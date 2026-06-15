<?php

defined('ABSPATH') || exit;

if (!class_exists('Phive_Bookings_Migrate_Data')) {

    class Phive_Bookings_Migrate_Data
    {
        /**
         * @var $option_name
         */
        private $option_name;

        /**
         * @var $migration
         */
        private $migration;

        /**
         * @var $logger
         */
        private $logger;

        /**
         * @var $context
         */
        private $context;

        /**
         * @var $migration limit
         */
        private $migration_limit;

        /**
         * @var $booking_data
         */
        private $booking_data;

        /**
         * Phive_Bookings_Migrate_Data contructor
         */
        public function __construct()
        {
            $this->migration = [
                'started'               => 'no',
                'fetched_data'          => 'no',
                'table_prepared'        => 'no',
                'completed'             => 'no',
                'partial'               => 'no',
                'order_ids'             => [],
                'order_ids_left'        => [],
                'order_ids_migrated'    => []
            ];

            $this->migration_limit = apply_filters('ph_wc_bookings_3_0_migration_limit', 30);

            $this->option_name  = 'ph_migrate_availability_data_v3_0_0';

            $this->migration = get_option($this->option_name, $this->migration);

            $this->context  = [
                'source' => 'ph_bookings_availability_data_migration_v3_0_0'
            ];

            add_action('ph_bookings_3_0_migration', [$this, 'ph_run_migration']);

            // Check if migration required
            if ($this->ph_is_migration_required()) {
                $this->ph_start_migration_process();
            }
        }

        /**
         * Start migration process
         */
        public function ph_start_migration_process()
        {
            $this->logger   = wc_get_logger();

            // Schedule migration if order_ids left
            if (!empty($this->migration['order_ids_left']) && !wp_next_scheduled('ph_bookings_3_0_migration')) {

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('MIGRATION_PROCESS', $this->context);
                $this->logger->debug(print_r($this->migration,1), $this->context);

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('ORDER_IDS_LEFT_TO_MIGRATE', $this->context);
                $this->logger->debug(print_r($this->migration['order_ids_left'],1), $this->context);

                $this->ph_schedule_migration_event();

                return;
            }

            // Fetch booking orders from DB
            if ($this->migration['fetched_data'] != 'yes') {
                $this->ph_get_booking_data();
            }

            // Return if no booking data found
            if (empty($this->booking_data)) {

                $this->migration['started'] = $this->migration['completed'] = 'yes';
                update_option($this->option_name, $this->migration);

                return;
            }

            $this->ph_filter_orders();

            if (empty($this->migration['order_ids'])) {
                return;
            }

            // Return if no data is fetched
            if ($this->migration['fetched_data'] != 'yes') {
                return;
            }

            // Clear DB before initiating migration process
            $this->ph_empty_table_data();

            if (!wp_next_scheduled('ph_bookings_3_0_migration')) {

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('MIGRATION_PROCESS', $this->context);
                $this->logger->debug(print_r($this->migration,1), $this->context);

                $this->ph_schedule_migration_event();
            }
        }

        /**
         * Check if migration is required
         *
         * @return bool
         */
        private function ph_is_migration_required()
        {
            $is_new_site = get_option('ph_migration_new_site_v3_0_0', '');

            $is_new_site =  (empty($is_new_site)) ? $this->ph_is_new_site() : $is_new_site;

            if ($this->migration['completed'] != 'yes' || $this->migration['partial'] == 'yes' || !$is_new_site) {
                return true;
            }

	        $display_settings = get_option('ph_bookings_display_settigns',[]);

            if (is_array($display_settings)) {

                $display_settings['calculate_availability_using_availability_table'] = 'yes';
	            update_option('ph_bookings_display_settigns',$display_settings);
            }

            return false;
        }

        /**
         * Get booking details from DB
         */
        private function ph_get_booking_data()
        {
            global $wpdb;

            $this->logger->debug('____________________________________________', $this->context);
            $this->logger->debug('FETCHING_BOOKING_DATA', $this->context);

            try {

                $current_date = strtotime(date("Y-m-d"));
                $compare_date = date("Y-m-d", strtotime("-3 day", $current_date));

                
                if (PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled()) {

                    $query = "SELECT 
                    oitems.order_item_id, 
                    oitems.order_id, 
                    oimeta.book_from,
                    oimeta.book_to, 
                    oimeta.product_id, 
                    oimeta.booking_status, 
                    oimeta.participant_count,
                    oimeta.asset_id, 
                    oimeta.person_as_booking,
                    oimeta.interval_format, 
                    oimeta.interval_number, 
                    oimeta.buffer_before_id, 
                    oimeta.buffer_after_id,
                    wposts.id,
                    wposts.buffer_after_to, 
                    wposts.buffer_after_from, 
                    wposts.buffer_asset_id
                    FROM {$wpdb->prefix}woocommerce_order_items AS oitems
                    INNER JOIN
                    (
                        SELECT order_item_id,
                        MAX(CASE WHEN meta_key = 'From' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"', 2), '\"', -1) ELSE '' END) AS book_from,
                        MAX(CASE WHEN meta_key = 'To' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"', 2), '\"', -1) ELSE '' END) AS book_to,
                        MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS product_id,
                        MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS booking_status,
                        MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value ELSE 0 END) AS participant_count,
                        MAX(CASE WHEN meta_key = 'Assets' THEN meta_value ELSE '' END) AS asset_id,
                        MAX(CASE WHEN meta_key = 'person_as_booking' THEN meta_value ELSE '' END) AS person_as_booking,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN (SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\"', 1)) ELSE '' END) AS interval_format,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN (SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -3), '\"', 1)) ELSE '' END) AS interval_number,
                        MAX(CASE WHEN meta_key = 'buffer_before_id' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\";', 1) ELSE '' END) AS buffer_before_id,
                        MAX(CASE WHEN meta_key = 'buffer_after_id' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\";', 1) Else '' END) AS buffer_after_id
                        FROM {$wpdb->prefix}woocommerce_order_itemmeta
                        GROUP BY order_item_id
                    ) AS oimeta ON oimeta.order_item_id = oitems.order_item_id
                    INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = oimeta.product_id
                    INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
                    LEFT JOIN
                    (
                        SELECT {$wpdb->prefix}wc_orders.id,
                        MAX(CASE WHEN {$wpdb->prefix}wc_orders_meta.meta_key = 'Buffer_after_To' THEN {$wpdb->prefix}wc_orders_meta.meta_value ELSE '' END) AS buffer_after_to,
                        MAX(CASE WHEN {$wpdb->prefix}wc_orders_meta.meta_key = 'Buffer_after_From' THEN {$wpdb->prefix}wc_orders_meta.meta_value ELSE '' END) AS buffer_after_from,
                        MAX(CASE WHEN {$wpdb->prefix}wc_orders_meta.meta_key = 'buffer_asset_id' THEN {$wpdb->prefix}wc_orders_meta.meta_value ELSE '' END) AS buffer_asset_id
                        FROM {$wpdb->prefix}wc_orders
                        INNER JOIN {$wpdb->prefix}wc_orders_meta ON {$wpdb->prefix}wc_orders.id = {$wpdb->prefix}wc_orders_meta.order_id
                        GROUP BY {$wpdb->prefix}wc_orders.id
                    ) AS wposts ON wposts.id = oimeta.buffer_after_id
                    WHERE t.slug = 'phive_booking' and tt.taxonomy = 'product_type' AND 
                    DATE(
                        IF(
                            NOT (ISNULL(wposts.buffer_after_to) OR wposts.buffer_after_to = ''),
                            wposts.buffer_after_to,
                            IF (
                                NOT (ISNULL(wposts.buffer_after_from) OR wposts.buffer_after_from = ''),
                                wposts.buffer_after_from,
                                IF
                                (
                                    (
                                        NOT (ISNULL(oimeta.book_to) OR oimeta.book_to = '') AND (NOT (ISNULL(oimeta.interval_format) OR oimeta.interval_format = '') )
                                    ),
                                    IF(
                                        (
                                            (NOT oimeta.interval_format = 'day') AND (NOT oimeta.interval_format = 'month')
                                        ),
                                        IF(
                                            oimeta.interval_format = 'hour',
                                            DATE_ADD(oimeta.book_to, INTERVAL oimeta.interval_number HOUR),
                                            DATE_ADD(oimeta.book_to, INTERVAL oimeta.interval_number MINUTE)
                                        ),
                                        IF(
                                            oimeta.interval_format = 'month',
                                            LAST_DAY(CONCAT(oimeta.book_to, '-01')),
                                            oimeta.book_to
                                        )
                                    ),
                                    IF(
                                        ( 
                                            ( ISNULL(oimeta.book_to) OR oimeta.book_to = '' ) AND 
                                            ( NOT (ISNULL(oimeta.interval_format) OR oimeta.interval_format = '') )
                                        ),
                                        IF(
                                            (
                                                (oimeta.interval_format = 'day' || oimeta.interval_format = 'month')
                                            ),
                                            (
                                                IF(
                                                    oimeta.interval_number > 1,
                                                    (
                                                        IF(
                                                            oimeta.interval_format = 'day',
                                                            DATE_ADD(oimeta.book_from, INTERVAL (oimeta.interval_number-1) Day),
                                                            DATE_ADD(LAST_DAY(CONCAT(oimeta.book_from, '-01')), INTERVAL (oimeta.interval_number-1) Month)
                                                        )
                                                    ),
                                                    IF(
                                                        oimeta.interval_format = 'month',
                                                        LAST_DAY(CONCAT(oimeta.book_from, '-01')),
                                                        oimeta.book_from
                                                    )
                                                )
                                            ),
                                            (
                                                IF(
                                                    oimeta.interval_format = 'hour',
                                                    DATE_ADD(oimeta.book_from, INTERVAL oimeta.interval_number HOUR),
                                                    DATE_ADD(oimeta.book_from, INTERVAL oimeta.interval_number MINUTE)
                                                )
                                            )
                                        ),
                                        IF(
                                            LENGTH(oimeta.book_from) = 7,
                                            LAST_DAY(CONCAT(oimeta.book_from, '-01')),
                                            oimeta.book_from
                                        )
                                    )
                                )
                            )
                        )
                    ) >= '$compare_date'
                    ORDER BY `oimeta`.`book_to` DESC";
                } else {

                    $query = "SELECT 
                    oitems.order_item_id, 
                    oitems.order_id, 
                    oimeta.book_from,
                    oimeta.book_to, 
                    oimeta.product_id, 
                    oimeta.booking_status, 
                    oimeta.participant_count,
                    oimeta.asset_id, 
                    oimeta.person_as_booking,
                    oimeta.interval_format, 
                    oimeta.interval_number, 
                    oimeta.buffer_before_id, 
                    oimeta.buffer_after_id,
                    wposts.ID, 
                    wposts.buffer_after_to, 
                    wposts.buffer_after_from, 
                    wposts.buffer_asset_id
                    FROM {$wpdb->prefix}woocommerce_order_items AS oitems
                    INNER JOIN
                    (
                        SELECT order_item_id,
                        MAX(CASE WHEN meta_key = 'From' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"', 2), '\"', -1) ELSE '' END) AS book_from,
                        MAX(CASE WHEN meta_key = 'To' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"', 2), '\"', -1) ELSE '' END) AS book_to,
                        MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS product_id,
                        MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS booking_status,
                        MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value ELSE 0 END) AS participant_count,
                        MAX(CASE WHEN meta_key = 'Assets' THEN meta_value ELSE '' END) AS asset_id,
                        MAX(CASE WHEN meta_key = 'person_as_booking' THEN meta_value ELSE '' END) AS person_as_booking,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN (SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\"', 1)) ELSE '' END) AS interval_format,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN (SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -3), '\"', 1)) ELSE '' END) AS interval_number,
                        MAX(CASE WHEN meta_key = 'buffer_before_id' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\";', 1) ELSE '' END) AS buffer_before_id,
                        MAX(CASE WHEN meta_key = 'buffer_after_id' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\";', 1) Else '' END) AS buffer_after_id
                        FROM {$wpdb->prefix}woocommerce_order_itemmeta
                        GROUP BY order_item_id
                    ) AS oimeta ON oimeta.order_item_id = oitems.order_item_id
                    INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = oimeta.product_id
                    INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
                    LEFT JOIN
                    (
                        SELECT {$wpdb->prefix}posts.ID,
                        MAX(CASE WHEN {$wpdb->prefix}postmeta.meta_key = 'Buffer_after_To' THEN {$wpdb->prefix}postmeta.meta_value ELSE '' END) AS buffer_after_to,
                        MAX(CASE WHEN {$wpdb->prefix}postmeta.meta_key = 'Buffer_after_From' THEN {$wpdb->prefix}postmeta.meta_value ELSE '' END) AS buffer_after_from,
                        MAX(CASE WHEN {$wpdb->prefix}postmeta.meta_key = 'buffer_asset_id' THEN {$wpdb->prefix}postmeta.meta_value ELSE '' END) AS buffer_asset_id
                        FROM {$wpdb->prefix}posts
                        INNER JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
                        GROUP BY {$wpdb->prefix}posts.ID
                    ) AS wposts ON wposts.ID = oimeta.buffer_after_id
                    WHERE t.slug = 'phive_booking' and tt.taxonomy = 'product_type' AND 
                    DATE(
                        IF(
                            NOT (ISNULL(wposts.buffer_after_to) OR wposts.buffer_after_to = ''),
                            wposts.buffer_after_to,
                            IF (
                                NOT (ISNULL(wposts.buffer_after_from) OR wposts.buffer_after_from = ''),
                                wposts.buffer_after_from,
                                IF
                                (
                                    (
                                        NOT (ISNULL(oimeta.book_to) OR oimeta.book_to = '') AND (NOT (ISNULL(oimeta.interval_format) OR oimeta.interval_format = '') )
                                    ),
                                    IF(
                                        (
                                            (NOT oimeta.interval_format = 'day') AND (NOT oimeta.interval_format = 'month')
                                        ),
                                        IF(
                                            oimeta.interval_format = 'hour',
                                            DATE_ADD(oimeta.book_to, INTERVAL oimeta.interval_number HOUR),
                                            DATE_ADD(oimeta.book_to, INTERVAL oimeta.interval_number MINUTE)
                                        ),
                                        IF(
                                            oimeta.interval_format = 'month',
                                            LAST_DAY(CONCAT(oimeta.book_to, '-01')),
                                            oimeta.book_to
                                        )
                                    ),
                                    IF(
                                        ( 
                                            ( ISNULL(oimeta.book_to) OR oimeta.book_to = '' ) AND 
                                            ( NOT (ISNULL(oimeta.interval_format) OR oimeta.interval_format = '') )
                                        ),
                                        IF(
                                            (
                                                (oimeta.interval_format = 'day' || oimeta.interval_format = 'month')
                                            ),
                                            (
                                                IF(
                                                    oimeta.interval_number > 1,
                                                    (
                                                        IF(
                                                            oimeta.interval_format = 'day',
                                                            DATE_ADD(oimeta.book_from, INTERVAL (oimeta.interval_number-1) Day),
                                                            DATE_ADD(LAST_DAY(CONCAT(oimeta.book_from, '-01')), INTERVAL (oimeta.interval_number-1) Month)
                                                        )
                                                    ),
                                                    IF(
                                                        oimeta.interval_format = 'month',
                                                        LAST_DAY(CONCAT(oimeta.book_from, '-01')),
                                                        oimeta.book_from
                                                    )
                                                )
                                            ),
                                            (
                                                IF(
                                                    oimeta.interval_format = 'hour',
                                                    DATE_ADD(oimeta.book_from, INTERVAL oimeta.interval_number HOUR),
                                                    DATE_ADD(oimeta.book_from, INTERVAL oimeta.interval_number MINUTE)
                                                )
                                            )
                                        ),
                                        IF(
                                            LENGTH(oimeta.book_from) = 7,
                                            LAST_DAY(CONCAT(oimeta.book_from, '-01')),
                                            oimeta.book_from
                                        )
                                    )
                                )
                            )
                        )
                    ) >= '$compare_date'
                    ORDER BY `oimeta`.`book_to` DESC";
                }

                $this->booking_data = $wpdb->get_results($query, OBJECT);
            } catch (Exception $e) {

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('fetch_data', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    $this->context
                );
            } catch (Exception $e) {
                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('fetch_data', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    $this->context
                );
            }
        }

        /**
         * Run DB migration
         */
        public function ph_run_migration()
        {
            $this->logger   = wc_get_logger();

            try {

                $this->migration['started'] = 'yes';

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('RUNNING_MIGRATION', $this->context);
                $this->logger->debug(print_r($this->migration,1), $this->context);

                $order_ids = $this->migration['order_ids'];

                $order_ids_in_availability_table    = $this->ph_get_order_ids_from_availability_table();
                $order_ids_migrated                 = array_values(array_intersect($order_ids, $order_ids_in_availability_table));
                $order_ids_to_migrate               = $order_ids_left = array_values(array_diff($order_ids, $order_ids_in_availability_table));

                if (count($order_ids_migrated) > 0) {

                    $this->migration['partial']             = 'yes';
                    $this->migration['order_ids_migrated']  = array_values(array_unique(array_merge($this->migration['order_ids_migrated'], $order_ids_migrated)));
                    $this->migration['order_ids_left']      = $order_ids_left;

                    $this->logger->debug('____________________________________________', $this->context);
                    $this->logger->debug('ORDER_IDS_MIGRATED', $this->context);
                    $this->logger->debug(print_r($this->migration,1), $this->context);

                    update_option($this->option_name, $this->migration);
                }

                if (count($order_ids_to_migrate) == 0) {

                    $this->migration['partial']             = 'no';
                    $this->migration['completed']           = 'yes';
                    $this->migration['order_ids_migrated']  = array_values(array_unique(array_merge($this->migration['order_ids_migrated'], $order_ids_migrated)));
                    $this->migration['order_ids_left']      = [];

                    update_option($this->option_name, $this->migration);
                }

                foreach ($order_ids_to_migrate as $key => $order_id) {
                    
                    if ($key == $this->migration_limit) {

                        $this->logger->debug('____________________________________________', $this->context);
                        $this->logger->debug('MIGRATION_LIMIT', $this->context);
                        
                        break;
                    }

                    if (!empty($order_id)) {
                        $order = wc_get_order($order_id);
                        $this->ph_build_and_insert_data_to_availability_table($order);
                        $order_ids_migrated[] = $order_id;
                    }
                }

                if ((count($order_ids_migrated) > 0) && !(array_diff($order_ids, $order_ids_migrated))) {

                    $this->migration['partial']             = 'no';
                    $this->migration['completed']           = 'yes';
                    $this->migration['order_ids_migrated']  = array_values(array_unique(array_merge($this->migration['order_ids_migrated'], $order_ids_migrated)));
                    $this->migration['order_ids_left']      = [];
                    update_option($this->option_name, $this->migration);
                }

                if (array_diff($order_ids, $order_ids_migrated)) {

                    $this->migration['order_ids_left']      = array_diff($order_ids, $order_ids_migrated);
                    $this->migration['order_ids_migrated']  = array_values(array_unique(array_merge($this->migration['order_ids_migrated'], $order_ids_migrated)));
                    $this->migration['partial']             = 'yes';
                    $this->migration['completed']           = 'yes';
                }

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('MIGRATION_END', $this->context);
                $this->logger->debug(print_r($this->migration,1), $this->context);

                update_option($this->option_name, $this->migration);
            } catch (Throwable $e) {
                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('migrate_data', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    $this->context
                );
            } catch (Exception $e) {
                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('migrate_data', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    $this->context
                );
            }
        }

        /**
         * Empty avaialbility table before running migration
         */
        private function ph_empty_table_data()
        {
            $this->migration = get_option($this->option_name, $this->migration);

            if ($this->migration['table_prepared'] == 'yes') {
                return;
            }

            try {

                $booking_db = new Phive_Bookings_Database();
                $booking_db->empty_availability_table();
                $this->migration['table_prepared'] = 'yes';

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('EMPTYING_TABLE_DATA', $this->context);
                $this->logger->debug(print_r($this->migration,1), $this->context);

                update_option($this->option_name, $this->migration);
            } catch (Throwable $e) {
                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('empty_availability_table_before_migration_started', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    $this->context
                );
            } catch (Exception $e) {
                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('empty_availability_table_before_migration_started', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    $this->context
                );
            }
        }

        /**
         * Schedule migration
         */
        private function ph_schedule_migration_event()
        {
            try {

                $start_time_stamp = strtotime("now + 5 minutes");
                wp_schedule_single_event($start_time_stamp, 'ph_bookings_3_0_migration');

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('SCHEDULING_MIGRATION_CRON', $this->context);

            } catch (Throwable $e) {
                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('scheduling_migration_event', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        array(
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ),
                        1
                    ),
                    $this->context
                );
            } catch (Exception $e) {
                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('scheduling_migration_event', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        array(
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ),
                        1
                    ),
                    $this->context
                );
            }
        }

        /**
         * Filter invalid orders
         */
        private function ph_filter_orders()
        {
            $order_ids          = [];
            $invalid_order_ids  = [];

            foreach ($this->booking_data as $data) {

                $order_id = $data->order_id;

                $order = wc_get_order($order_id);

                if (!$order instanceof WC_Order) {

                    $invalid_order_ids[] = $order_id;
                    continue;
                }

                $order_ids[] = $order_id;
            }

            $this->logger->debug('____________________________________________', $this->context);
            $this->logger->debug('INVALID_ORDER_IDS', $this->context);
            $this->logger->debug(print_r($invalid_order_ids,1), $this->context);

            update_option('ph_migration_invalid_order_ids_not_migrated_v3_0_0', $invalid_order_ids);

            if (empty($order_ids)) {

                $this->migration['order_ids'] = $this->migration['order_ids_left'] = [];
                $this->migration['fetched_data'] = $this->migration['started'] = $this->migration['completed'] = 'yes';
            } else {

                $this->migration['order_ids'] = $this->migration['order_ids_left'] = $order_ids;
                $this->migration['fetched_data'] = 'yes';
            }

            update_option($this->option_name, $this->migration);
        }

        /**
         * Get order ids from availability table
         *
         * @param array $order_ids
         */
        private function ph_get_order_ids_from_availability_table()
        {
            $order_ids = [];

            try {

                $obj        = new Phive_Bookings_Database();
                $order_ids  = $obj->get_order_ids_from_availability_table();
            } catch (Throwable $e) {

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('get_order_ids_from_availability_table', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    $this->context
                );
            } catch (Exception $e) {

                $this->logger->debug('____________________________________________', $this->context);
                $this->logger->debug('get_order_ids_from_availability_table', $this->context);
                $this->logger->debug('-------------------Errors-------------------', $this->context);
                $this->logger->debug(
                    print_r(
                        [
                            'code'      => $e->getCode(),
                            'message'   => $e->getMessage(),
                            'file'      => $e->getFile(),
                            'line'      => $e->getLine(),
                            'time'      => date('Y-m-d H:i:s'),
                        ],
                        1
                    ),
                    $this->context
                );
            }

            return $order_ids;
        }

        /**
         * Insert data to availability table
         *
         * @param object $order
         * @param string $order_item_id
         */
        public function ph_build_and_insert_data_to_availability_table($order, $order_item_id = '', $ph_booking_order = '')
        {
            $data['order_id']                   = $order->get_id();
            $data['woocommerce_order_status']   = $order->get_status();
            $order_items                        = $order->get_items();

            foreach ($order_items as $item_id => $item) {
                // When new order item added/modified to existing booking
                if ($ph_booking_order == 'existing') {
                    if ($order_item_id != $item_id) {
                        continue;
                    }
                }

                $data['product_id'] = $item->get_product_id();
                $product            = wc_get_product($data['product_id']);

                // Skip the product if it is deleted
                if (!$product instanceof WC_Product) {
                    continue;
                }

                if ($product->get_type() != 'phive_booking') {
                    continue;
                }
                $data['order_item_id']  = $item_id;
                $data['booking_type']   = 'booked';

                // Product Settings
                $settings = Ph_Booking_Manage_Availability_Data::ph_get_product_settings($data['product_id']);
                // interval details
                $data['interval']           = $settings['interval'];
                $data['interval_format']    = $settings['interval_period'];
                $data['charge_per_night']   = $settings['charge_per_night'];

                // Booking Dates
                $booked_from_date       = ph_maybe_unserialize(wc_get_order_item_meta($item_id, 'From', 1));
                $booked_to_date         = ph_maybe_unserialize(wc_get_order_item_meta($item_id, 'To', 1));
                $booked_to_date         = $booked_to_date ? $booked_to_date : $booked_from_date;
                $data['booking_status'] = ph_maybe_unserialize(wc_get_order_item_meta($item_id, 'booking_status', 1));

                // Participants 
                $data['number_of_persons']  = wc_get_order_item_meta($item_id, 'Number of persons', 1);
                $data['number_of_persons']    = $data['number_of_persons'] ? $data['number_of_persons'] : 0;
                $data['person_as_booking']  = ph_maybe_unserialize(wc_get_order_item_meta($item_id, 'person_as_booking', 1));
                $data['person_as_booking']    = $data['person_as_booking'] ? $data['person_as_booking'] : 'no';

                // Participant Group Addon
                $phive_booked_persons = maybe_unserialize(wc_get_order_item_meta($item_id, 'ph_bookings_participant_booking_data', 1));
                if (is_array($phive_booked_persons) && !empty($phive_booked_persons)) {
                    $participant_booking_data     = '';
                    // Looping through the rule and assign the corresponding rule value given by customer
                    foreach ($phive_booked_persons as $phive_booked_person) {
                        // "Participant(s)":"1";"Family":"2";
                        $participant_booking_data .= '"' . $phive_booked_person['participant_label'] . '":"' . $phive_booked_person['participant_count'] . '";';
                    }
                    if ($participant_booking_data) {
                        $data['participant_detail'] = $participant_booking_data;
                    }
                } else {
                    $persons_pricing_rules = get_post_meta($data['product_id'], "_phive_booking_persons_pricing_rules", 1);
                    if ($persons_pricing_rules) {
                        $participant_booking_data = '';
                        $participant_names = array();
                        foreach ($persons_pricing_rules as $key => $rule) {
                            $rule['ph_booking_persons_rule_type'] = apply_filters(
                                'wpml_translate_single_string',
                                $rule['ph_booking_persons_rule_type'],
                                'bookings-and-appointments-for-woocommerce',
                                'participant_name_' . $rule['ph_booking_persons_rule_type']
                            );

                            $participant_names[] = $rule['ph_booking_persons_rule_type'];
                        }

                        if ($participant_names) {
                            foreach ($participant_names as $participant_name) {
                                $participant_count = wc_get_order_item_meta($item_id, $participant_name, 1);
                                if (is_numeric($participant_count)) {
                                    $participant_booking_data .= '"' . $participant_name . '":"' . $participant_count . '";';
                                }
                            }

                            if ($participant_booking_data) {
                                $data['participant_detail'] = $participant_booking_data;
                            }
                        }
                    }
                }


                // Resources - Resource quantity addon.
                $phive_booked_resources = maybe_unserialize(wc_get_order_item_meta($item_id, 'ph_bookings_resources_booking_data', 1));
                if (is_array($phive_booked_resources) && !empty($phive_booked_resources)) {
                    $resources_booking_data     = '';
                    // Looping through the rule and assign the corresponding rule value given by customer
                    foreach ($phive_booked_resources as $phive_booked_resource) {
                        $resource_count         = apply_filters('ph_modify_resource_count_after_order_placed', 1, $item_id, $order, $settings, $phive_booked_resource['resource_label']);
                        $resources_booking_data .= '"' . $phive_booked_resource['resource_label'] . '":"' . $resource_count . '";';
                    }
                    if ($resources_booking_data) {
                        $data['resource_detail'] = $resources_booking_data;
                    }
                } else {
                    $resources_pricing_rules = get_post_meta($data['product_id'], "_phive_booking_resources_pricing_rules", 1);

                    if ($resources_pricing_rules) {
                        $resources_booking_data = '';
                        $resource_names = array();
                        foreach ($resources_pricing_rules as $key => $rule) {
                            $rule['ph_booking_resources_name'] = apply_filters(
                                'wpml_translate_single_string',
                                $rule['ph_booking_resources_name'],
                                'bookings-and-appointments-for-woocommerce',
                                'resource_name_' . $rule['ph_booking_resources_name']
                            );

                            $resource_names[] = $rule['ph_booking_resources_name'];
                        }

                        if ($resource_names) {
                            foreach ($resource_names as $resource_name) {
                                $resource = wc_get_order_item_meta($item_id, $resource_name, 1);
                                if ($resource == 'yes') {
                                    $resource_count         = apply_filters('ph_modify_resource_count_after_order_placed', 1, $item_id, $order, $settings, $resource_name);
                                    $resources_booking_data .= '"' . $resource_name . '":"' . $resource_count . '";';
                                }
                            }

                            if ($resources_booking_data) {
                                $data['resource_detail'] = $resources_booking_data;
                            }
                        }
                    }
                }

                // Asset 
                $data['asset_id']   = ph_maybe_unserialize(wc_get_order_item_meta($item_id, 'Assets', 1));
                $data['asset_id']   = $data['asset_id'] ? $data['asset_id'] : NULL;

                // Buffer
                $buffer_before_id   = ph_maybe_unserialize(wc_get_order_item_meta($item_id, 'buffer_before_id', 1));
                $buffer_after_id    = ph_maybe_unserialize(wc_get_order_item_meta($item_id, 'buffer_after_id', 1));
                $buffer_from_date   = $buffer_before_id ? get_post_meta($buffer_before_id, 'Buffer_before_From', 1) : $booked_from_date;
                $buffer_from_date   = $buffer_from_date ? $buffer_from_date : $booked_from_date;
                $buffer_to_date     = $buffer_after_id ? get_post_meta($buffer_after_id, 'Buffer_after_To', 1) : $booked_to_date;
                $buffer_to_date     = $buffer_to_date ? $buffer_to_date : $booked_to_date;

                Ph_Booking_Manage_Availability_Data::ph_bookings_insert_data_in_availability_table($data, $buffer_from_date, $buffer_to_date, $settings);
            }
        }

        /**
         * Check if its a new site
         *
         * @return bool
         */
        private function ph_is_new_site()
        {
            global $wpdb;

            if (PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled()) {

                $query = "SELECT 
                    oitems.order_item_id, 
                    oitems.order_id, 
                    oimeta.book_from,
                    oimeta.book_to, 
                    oimeta.product_id, 
                    oimeta.booking_status, 
                    oimeta.participant_count, 
                    oimeta.asset_id, 
                    oimeta.person_as_booking,
                    oimeta.interval_format, 
                    oimeta.interval_number, 
                    oimeta.buffer_before_id, 
                    oimeta.buffer_after_id,
                    wposts.ID, 
                    wposts.buffer_after_to, 
                    wposts.buffer_after_from, 
                    wposts.buffer_asset_id
                    FROM {$wpdb->prefix}woocommerce_order_items AS oitems
                    INNER JOIN
                    (
                        SELECT order_item_id,
                        MAX(CASE WHEN meta_key = 'From' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"', 2), '\"', -1) ELSE '' END) AS book_from,
                        MAX(CASE WHEN meta_key = 'To' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"', 2), '\"', -1) ELSE '' END) AS book_to,
                        MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS product_id,
                        MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS booking_status,
                        MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value ELSE 0 END) AS participant_count,
                        MAX(CASE WHEN meta_key = 'Assets' THEN meta_value ELSE '' END) AS asset_id,
                        MAX(CASE WHEN meta_key = 'person_as_booking' THEN meta_value ELSE '' END) AS person_as_booking,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN (SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\"', 1)) ELSE '' END) AS interval_format,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN (SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -3), '\"', 1)) ELSE '' END) AS interval_number,
                        MAX(CASE WHEN meta_key = 'buffer_before_id' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\";', 1) ELSE '' END) AS buffer_before_id,
                        MAX(CASE WHEN meta_key = 'buffer_after_id' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\";', 1) Else '' END) AS buffer_after_id
                        FROM {$wpdb->prefix}woocommerce_order_itemmeta
                        GROUP BY order_item_id
                    ) AS oimeta ON oimeta.order_item_id = oitems.order_item_id
                    INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = oimeta.product_id
                    INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
                    LEFT JOIN
                    (
                        SELECT {$wpdb->prefix}wc_orders.id,
                        MAX(CASE WHEN {$wpdb->prefix}wc_orders_meta.meta_key = 'Buffer_after_To' THEN {$wpdb->prefix}wc_orders_meta.meta_value ELSE '' END) AS buffer_after_to,
                        MAX(CASE WHEN {$wpdb->prefix}wc_orders_meta.meta_key = 'Buffer_after_From' THEN {$wpdb->prefix}wc_orders_meta.meta_value ELSE '' END) AS buffer_after_from,
                        MAX(CASE WHEN {$wpdb->prefix}wc_orders_meta.meta_key = 'buffer_asset_id' THEN {$wpdb->prefix}wc_orders_meta.meta_value ELSE '' END) AS buffer_asset_id
                        FROM {$wpdb->prefix}wc_orders
                        INNER JOIN {$wpdb->prefix}wc_orders_meta ON {$wpdb->prefix}wc_orders.id = {$wpdb->prefix}wc_orders_meta.order_id
                        GROUP BY {$wpdb->prefix}wc_orders.id
                    ) AS wposts ON wposts.ID = oimeta.buffer_after_id
                    WHERE t.slug = 'phive_booking' and tt.taxonomy = 'product_type'
                    ORDER BY `oimeta`.`book_to` DESC limit 10";
            } else {

                $query = "SELECT 
                    oitems.order_item_id, 
                    oitems.order_id, 
                    oimeta.book_from,
                    oimeta.book_to, 
                    oimeta.product_id, 
                    oimeta.booking_status, 
                    oimeta.participant_count, 
                    oimeta.asset_id, 
                    oimeta.person_as_booking,
                    oimeta.interval_format, 
                    oimeta.interval_number, 
                    oimeta.buffer_before_id, 
                    oimeta.buffer_after_id,
                    wposts.ID, 
                    wposts.buffer_after_to, 
                    wposts.buffer_after_from, 
                    wposts.buffer_asset_id
                    FROM {$wpdb->prefix}woocommerce_order_items AS oitems
                    INNER JOIN
                    (
                        SELECT order_item_id,
                        MAX(CASE WHEN meta_key = 'From' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"', 2), '\"', -1) ELSE '' END) AS book_from,
                        MAX(CASE WHEN meta_key = 'To' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"', 2), '\"', -1) ELSE '' END) AS book_to,
                        MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS product_id,
                        MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS booking_status,
                        MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value ELSE 0 END) AS participant_count,
                        MAX(CASE WHEN meta_key = 'Assets' THEN meta_value ELSE '' END) AS asset_id,
                        MAX(CASE WHEN meta_key = 'person_as_booking' THEN meta_value ELSE '' END) AS person_as_booking,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN (SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\"', 1)) ELSE '' END) AS interval_format,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN (SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -3), '\"', 1)) ELSE '' END) AS interval_number,
                        MAX(CASE WHEN meta_key = 'buffer_before_id' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\";', 1) ELSE '' END) AS buffer_before_id,
                        MAX(CASE WHEN meta_key = 'buffer_after_id' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ':\"', -1), '\";', 1) Else '' END) AS buffer_after_id
                        FROM {$wpdb->prefix}woocommerce_order_itemmeta
                        GROUP BY order_item_id
                    ) AS oimeta ON oimeta.order_item_id = oitems.order_item_id
                    INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = oimeta.product_id
                    INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
                    LEFT JOIN
                    (
                        SELECT {$wpdb->prefix}posts.ID,
                        MAX(CASE WHEN {$wpdb->prefix}postmeta.meta_key = 'Buffer_after_To' THEN {$wpdb->prefix}postmeta.meta_value ELSE '' END) AS buffer_after_to,
                        MAX(CASE WHEN {$wpdb->prefix}postmeta.meta_key = 'Buffer_after_From' THEN {$wpdb->prefix}postmeta.meta_value ELSE '' END) AS buffer_after_from,
                        MAX(CASE WHEN {$wpdb->prefix}postmeta.meta_key = 'buffer_asset_id' THEN {$wpdb->prefix}postmeta.meta_value ELSE '' END) AS buffer_asset_id
                        FROM {$wpdb->prefix}posts
                        INNER JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
                        GROUP BY {$wpdb->prefix}posts.ID
                    ) AS wposts ON wposts.ID = oimeta.buffer_after_id
                    WHERE t.slug = 'phive_booking' and tt.taxonomy = 'product_type'
                    ORDER BY `oimeta`.`book_to` DESC limit 10";
            }

            $data = $wpdb->get_results($query, OBJECT);

            if (empty($data)) {
                $this->migration['order_ids'] = $this->migration['order_ids_left'] = array();
                $this->migration['fetched_data'] = $this->migration['started'] = $this->migration['completed'] = 'yes';
                update_option($this->option_name, $this->migration);
                update_option('ph_migration_plugins_updated_v3_0_0', 'yes');
                update_option('ph_migration_env_set_v3_0_0', 'yes');
                update_option('ph_migration_final_consent_v3_0_0', 'yes');
                update_option('ph_migration_new_site_v3_0_0', 'yes');

                if (function_exists('wc_get_logger')) {

                    $this->logger = wc_get_logger();
                    $this->logger->debug('____________________________________________', $this->context);
                    $this->logger->debug('NEW_SITE_CHECK', $this->context);
                    $this->logger->debug(print_r($this->migration,1), $this->context);
                }


                return true;
            }

            update_option('ph_migration_new_site_v3_0_0', 'no');

            return false;
        }
    }
    new Phive_Bookings_Migrate_Data();
}
