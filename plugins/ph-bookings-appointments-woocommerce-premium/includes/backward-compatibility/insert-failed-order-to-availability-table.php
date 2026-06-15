<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action('admin_init', 'ph_bookings_insert_failed_order_data');

if (!function_exists('ph_bookings_insert_failed_order_data')) {

    /**
     * Add the data of the order that is first failed and processed succefully again
     * Issue: The data of the orders first failed and processed succefully again is not getting added to availability table
     * @since 3.1.9
     */
	function ph_bookings_insert_failed_order_data()
	{
        if(get_option('ph_bookings_insert_failed_order_data','yes') != 'yes'){
            return;
        }
		$current_date = strtotime(date("Y-m-d"));
		$compare_date = date("Y-m-d", strtotime("-3 day", $current_date));

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

		$data       = $wpdb->get_results($query, OBJECT);
		$order_ids  = array();
		$invalid_order_ids = array();
		foreach ($data as $key => $value) {
			$order = wc_get_order($value->order_id);
			if (!(is_object($order))) {
				// Migration Improvement - Deleted/Invalid orders should not be added for migration
				$invalid_order_ids[] = $value->order_id;
				continue;
			}
			$order_ids[] = $value->order_id;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ph_bookings_availability_calculation_data';
		$orders_ids_in_availability = $wpdb->get_results("SELECT order_item_id FROM $table_name ", ARRAY_A);
		$insert_order_item = [];
		foreach ($order_ids as $order_id) {
			$order = wc_get_order($order_id);
			if (!$order instanceof WC_Order) {
				continue;
			}
			foreach ($order->get_items() as $order_item_id => $item) {
				if (!in_array($order_item_id, array_column($orders_ids_in_availability, "order_item_id"))) {
					$insert_order_item[$order_id][] = $order_item_id;
				}
			}
		}
		foreach ($insert_order_item as $key => $order_item_ids) {
			$insert_order_item_ids = [];
			foreach (array_unique($order_item_ids) as $order_item_id) {
				$insert_order_item_ids[$order_item_id] = $key;
			}
			do_action('ph_booking_order_items_modified', $insert_order_item_ids, $key, '');
		}
		update_option('ph_bookings_insert_failed_order_data', "no");
	}
}