<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'Ph_Export_Bookings' ) ) {
    class Ph_Export_Bookings 
    {
        /**
         * WC Table
         */
        private $woocommerce_table;
		
        public function __construct() 
        {
            add_action(	'admin_menu', array( $this, 'ph_booking_admin_menu' ), 10 );
            
            if(isset($_POST['ph-booking-export-submit']))
            {
                $this->woocommerce_table = PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled();

                $this->ph_export_bookings_data();
            }
		}

		/**
		* Add administration menus
		*
		* @since 0.1
		**/
        public function ph_booking_admin_menu() 
        {
            // 127029 - "PH Export Bookings" feature for the "Shop Manager" user role.
            add_submenu_page(
                "bookings",
                "Export Bookings",
                __("Export Bookings",'bookings-and-appointments-for-woocommerce'),
                "manage_woocommerce",
				"ph-export-bookings", 
				array( $this, "ph_bookings_main_screen" ),
                60
            );
		}

		/**
		* Main plugin screen 
		*/
		public function ph_bookings_main_screen() {

            $ph_booking_export_settings = get_option('ph_booking_export_settings');
            
            $ph_number_of_participants_checkbox = isset( $ph_booking_export_settings['ph_booking_export_number_of_participants_checkbox'])?$ph_booking_export_settings['ph_booking_export_number_of_participants_checkbox']:'';
            $ph_booked_by_checkbox = isset( $ph_booking_export_settings['ph_booking_export_booked_by_checkbox'] ) ? $ph_booking_export_settings['ph_booking_export_booked_by_checkbox'] : '';
            $ph_customer_email_checkbox = isset( $ph_booking_export_settings['ph_booking_export_customer_email_checkbox'] ) ? $ph_booking_export_settings['ph_booking_export_customer_email_checkbox']: '';
            $ph_customer_phone_checkbox = isset( $ph_booking_export_settings['ph_booking_export_customer_phone_checkbox'] ) ? $ph_booking_export_settings['ph_booking_export_customer_phone_checkbox'] : '' ;
            $ph_additional_notes_checkbox = isset( $ph_booking_export_settings['ph_booking_export_additional_notes_checkbox'] ) ? $ph_booking_export_settings['ph_booking_export_additional_notes_checkbox'] : '';
            $ph_asset_checkbox = isset( $ph_booking_export_settings['ph_booking_export_asset_checkbox'] ) ? $ph_booking_export_settings['ph_booking_export_asset_checkbox'] : '' ;
            $ph_booking_cost_checkbox = isset( $ph_booking_export_settings['ph_booking_export_booking_cost_checkbox'] ) ? $ph_booking_export_settings['ph_booking_export_booking_cost_checkbox'] : '' ;
            $ph_booking_participant_details_checkbox = isset( $ph_booking_export_settings['ph_booking_export_participant_details_checkbox'] ) ? $ph_booking_export_settings['ph_booking_export_participant_details_checkbox'] : '' ;
            $ph_booking_resources_details_checkbox = isset( $ph_booking_export_settings['ph_booking_export_resources_details_checkbox'] ) ? $ph_booking_export_settings['ph_booking_export_resources_details_checkbox'] : '' ;

            $args = array(
				'limit'	=>	-1,
			    'type' => 'phive_booking',
			);
			$products = wc_get_products( $args );

            $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
            
            // Multisite case
            if ( is_multisite() ) {

                $active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
            }

            // PH WooCommerce Product Addons activation check
            $is_activate_product_addon  = in_array('ph-woocommerce-product-addon-premium/ph-woocommerce-product-addon.php',$active_plugins);
            
            // PH Order Export for WooCommerce activation check
            $is_active_order_export     = in_array('ph-export-order-details/ph-export-order-details.php',$active_plugins);

			// Query all products for display them in the select in the backoffice
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Export Bookings' , 'bookings-and-appointments-for-woocommerce' ); ?></h1>
				<div class="ph-bookings-export-box postbox" style="padding:3.5rem;">
					<form method="post" name="ph_csv_exporter_form" action="#" enctype="multipart/form-data">
						<h2><?php esc_html_e( 'Export Bookings To CSV :', 'bookings-and-appointments-for-woocommerce' ); ?></h2>
						<div>
                            <label for="ph-bookings-export-product"><?php esc_html_e( 'Product : ', 'bookings-and-appointments-for-woocommerce' ); ?></label>
                            <select name="ph-bookings-export-product" id="ph-bookings-export-product">
                                <option value=""><?php esc_html_e( 'Select a product', 'bookings-and-appointments-for-woocommerce' ); ?></option>
                                <?php
                                	foreach ( $products as $key => $product ) {
                                        if( !empty($product) ){
                                            echo '<option value="'.$product->get_id().'">' . $product->get_name() .'</option>';
                                        }
                                    }?> 
                                ?>
                            </select>
                        </div>
                        <br>
						<div class="ph-bookings-export-dates">
							<div class="ph-bookings-date-picker">
								<label for="ph_booking_export_start_date"><?php esc_html_e( 'Bookings start between', 'bookings-and-appointments-for-woocommerce' ); ?>:</label>
                                <input type="date" id="ph_booking_export_start_date" name="ph_booking_export_start_date" value="" />
                                <input type="date" id="ph_booking_export_end_date" name="ph_booking_export_end_date" value="" />
                            </div>
                            <br>
                            <div class="ph-bookings-date-picker">
								<label for="ph_booking_export_end_from_date"><?php esc_html_e( 'Bookings end between', 'bookings-and-appointments-for-woocommerce' ); ?>: &nbsp;</label>
                                <input type="date" id="ph_booking_export_end_from_date" name="ph_booking_export_end_from_date" value="" />
							
                            	<!-- <label for="ph_booking_export_end_date"><php esc_html_e( 'End', 'bookings-and-appointments-for-woocommerce' ); ?> :</label> -->
                                <input type="date" id="ph_booking_export_end_to_date" name="ph_booking_export_end_to_date" value="" />
							</div>
						</div>
                        <br><br><br>
                        <div>
                            <div>
                                <?php esc_html_e( 'Fields To Include', 'bookings-and-appointments-for-woocommerce' ); ?>:<br><br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_number_of_participants_checkbox" name="ph_booking_export_number_of_participants_checkbox" <?php echo ($ph_number_of_participants_checkbox == 'yes') ? 'checked' : '';?>  /><?php _e('No of Participants', 'bookings-and-appointments-for-woocommerce'); ?><br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_participant_details_checkbox" name="ph_booking_export_participant_details_checkbox" <?php echo ($ph_booking_participant_details_checkbox == 'yes')  ? 'checked' : '';?>  /><?php _e('Booked Participants', 'bookings-and-appointments-for-woocommerce'); ?> (<?php _e('Applicable for bookings made after updating to plugin version 2.3.0', 'bookings-and-appointments-for-woocommerce'); ?>)<br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_resources_details_checkbox" name="ph_booking_export_resources_details_checkbox" <?php echo ($ph_booking_resources_details_checkbox == 'yes')  ? 'checked' : '';?>  /><?php _e('Booked Resources', 'bookings-and-appointments-for-woocommerce'); ?> (<?php _e('Applicable for bookings made after updating to plugin version 2.3.0', 'bookings-and-appointments-for-woocommerce'); ?>)<br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_booked_by_checkbox" name="ph_booking_export_booked_by_checkbox" <?php echo ($ph_booked_by_checkbox == 'yes')  ? 'checked' : '';?>  /><?php _e('Booked by', 'bookings-and-appointments-for-woocommerce'); ?><br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_customer_email_checkbox" name="ph_booking_export_customer_email_checkbox" <?php echo ($ph_customer_email_checkbox == 'yes')  ? 'checked' : '';?>  /><?php _e('Customer Email', 'bookings-and-appointments-for-woocommerce'); ?><br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_customer_phone_checkbox" name="ph_booking_export_customer_phone_checkbox" <?php echo ($ph_customer_phone_checkbox =='yes' )  ? 'checked' : '';?>  /><?php _e('Customer Phone', 'bookings-and-appointments-for-woocommerce'); ?><br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_additional_notes_checkbox" name="ph_booking_export_additional_notes_checkbox" <?php echo ($ph_additional_notes_checkbox == 'yes')  ? 'checked' : '';?>  /><?php _e('Additional Notes', 'bookings-and-appointments-for-woocommerce'); ?><br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_asset_checkbox" name="ph_booking_export_asset_checkbox" <?php echo ($ph_asset_checkbox == 'yes')  ? 'checked' : '';?>  /><?php _e('Asset', 'bookings-and-appointments-for-woocommerce'); ?><br><br>
                            </div>
                            <div>
                                <input type="checkbox" id="ph_booking_export_booking_cost_checkbox" name="ph_booking_export_booking_cost_checkbox" <?php echo ($ph_booking_cost_checkbox == 'yes')  ? 'checked' : '';?>  /><?php _e('Booking Cost', 'bookings-and-appointments-for-woocommerce'); ?><br><br>
                            </div>

                            <?php if ($is_activate_product_addon) { ?> 
                            <div>
                                <input type="checkbox" <?php echo $is_active_order_export ? '' : 'disabled' ?> id="include_produce_addon_details" name="include_produce_addon_details" value="yes"><i><b><?php echo __('Export Additional Booking Data (using PH Product Add-ons plugin).' . ($is_active_order_export ? '' : ' This feature is not currently enabled. <a href="http://pluginhive.com/support/?utm_source=bookings&utm_medium=plugin_settings" target="_blank">Contact support</a> to activate exporting add-ons, extra options, and other additional booking data.') , 'bookings-and-appointments-for-woocommerce'); ?></b></i><br><br>
                            </div>
                            <?php } ?> 
                        </div>
                        <br>
                        <div class="ph-bookings-export-download">
                            <input type="submit" name="ph-booking-export-submit" id="ph-booking-export-submit" class="button button-primary" value="<?php esc_html_e( 'Export and Download', 'bookings-and-appointments-for-woocommerce' ); ?>" />
						</div>
					</form>
				</div>
			</div>
			<?php 
        }

        public function ph_export_bookings_data()
        {
            global $wpdb;
            $booked = array();
            $ph_booking_export_settings['ph_booking_export_number_of_participants_checkbox'] = isset( $_POST['ph_booking_export_number_of_participants_checkbox'] ) ? 'yes' : 'no' ;
            $ph_booking_export_settings['ph_booking_export_booked_by_checkbox'] = isset( $_POST['ph_booking_export_booked_by_checkbox'] ) ? 'yes' : 'no' ;
            $ph_booking_export_settings['ph_booking_export_customer_email_checkbox'] = isset( $_POST['ph_booking_export_customer_email_checkbox'] ) ? 'yes' : 'no' ;
            $ph_booking_export_settings['ph_booking_export_customer_phone_checkbox'] = isset( $_POST['ph_booking_export_customer_phone_checkbox'] ) ? 'yes' : 'no' ;
            $ph_booking_export_settings['ph_booking_export_additional_notes_checkbox'] = isset( $_POST['ph_booking_export_additional_notes_checkbox'] ) ? 'yes' : 'no' ;
            $ph_booking_export_settings['ph_booking_export_asset_checkbox'] = isset( $_POST['ph_booking_export_asset_checkbox'] ) ? 'yes' : 'no' ;
            $ph_booking_export_settings['ph_booking_export_booking_cost_checkbox'] = isset( $_POST['ph_booking_export_booking_cost_checkbox'] ) ? 'yes' : 'no' ;
            $ph_booking_export_settings['ph_booking_export_participant_details_checkbox'] = isset( $_POST['ph_booking_export_participant_details_checkbox'] ) ? 'yes' : 'no' ;
            $ph_booking_export_settings['ph_booking_export_resources_details_checkbox'] = isset( $_POST['ph_booking_export_resources_details_checkbox'] ) ? 'yes' : 'no' ;

            update_option( 'ph_booking_export_settings', $ph_booking_export_settings );
            
            // Support for custom order status
            $custom_order_status = apply_filters('ph_all_bookings_for_custom_order_status', []);
            $ph_custom_order_status = "";
            if (!empty($custom_order_status) && is_array($custom_order_status)) {

			    $table_name = $this->woocommerce_table ? "{$wpdb->prefix}wc_orders.status" : "{$wpdb->prefix}posts.post_status";
    
                foreach ($custom_order_status as $order_status) {
    
                    $ph_custom_order_status .= " OR $table_name = '$order_status'";
                }
            }

            //Query for getting booked dates
            if ($this->woocommerce_table) {

                $query = "SELECT oitems.order_id, oitems.order_item_id, tr.object_id product_id, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails, imeta.no_of_persons, imeta.person_as_booking, imeta.buffer_before_id, imeta.buffer_after_id, product_name, ometa.customer_name, ometa.customer_last_name, ometa.customer_billing_email, ometa.customer_billing_phone, imeta.AdditionalNotes, imeta.BookingAssetId, imeta.ParticipantData, imeta.ResourcesData, imeta.AdditionalNotesDynamicKey
                FROM {$wpdb->prefix}wc_orders
                INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}wc_orders.id
                INNER JOIN (
                        SELECT
                        order_item_id,
                        MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
                        MAX(CASE WHEN meta_key = '_ph_booking_dlang_product_id' THEN meta_value ELSE '' END) AS DefaultLangProductId,
                        MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
                        MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
                        MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
                        MAX(CASE WHEN meta_key = 'buffer_before_id' THEN meta_value ELSE '' END) AS buffer_before_id,
                        MAX(CASE WHEN meta_key = 'buffer_after_id' THEN meta_value Else '' END) AS buffer_after_id,
                        MAX(CASE WHEN meta_key = 'person_as_booking' THEN meta_value ELSE '' END) AS person_as_booking,
                        MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value Else '' END) AS no_of_persons,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
                        MAX(CASE WHEN meta_key = 'Additional Notes' THEN meta_value ELSE '' END) AS AdditionalNotes,
                        MAX(CASE WHEN meta_key = 'Assets' THEN meta_value ELSE '' END) AS BookingAssetId,
                        MAX(CASE WHEN meta_key = 'ph_bookings_participant_booking_data' THEN meta_value ELSE '' END) AS ParticipantData,
                        MAX(CASE WHEN meta_key = 'ph_bookings_resources_booking_data' THEN meta_value ELSE '' END) AS ResourcesData,
                        MAX(CASE WHEN meta_key = 'ph_bookings_customer_additional_notes' THEN meta_value ELSE '' END) AS AdditionalNotesDynamicKey
                        FROM {$wpdb->prefix}woocommerce_order_itemmeta
                        GROUP BY order_item_id
                ) as imeta on  imeta.order_item_id = oitems.order_item_id
                INNER JOIN (
                    SELECT ID, post_title as product_name
                    from {$wpdb->prefix}posts
                ) as p on imeta.ProductId = p.ID
                INNER JOIN (
                    SELECT
                    order_id,
					first_name AS customer_name,
					last_name AS customer_last_name,
					email AS customer_billing_email,
					phone AS customer_billing_phone
					FROM {$wpdb->prefix}wc_order_addresses
					GROUP BY order_id
                ) as ometa on ometa.order_id = {$wpdb->prefix}wc_orders.id
                INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
                INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
                WHERE {$wpdb->prefix}wc_orders.type IN ('shop_order', 'shop_order_refund')
                AND (
                    {$wpdb->prefix}wc_orders.status Like 'wc-pending'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-processing'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-on-hold'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-completed'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-cancelled'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-refunded'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-failed'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-partially-paid'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-partial-payment'
                    OR {$wpdb->prefix}wc_orders.status = 'wc-ng-complete'
                    $ph_custom_order_status
                )
                AND tt.taxonomy IN ('product_type')
                AND t.slug = 'phive_booking' ";

            } else {

                $query = "SELECT oitems.order_id, oitems.order_item_id, tr.object_id product_id, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails, imeta.no_of_persons, imeta.person_as_booking, imeta.buffer_before_id, imeta.buffer_after_id, product_name, ometa.customer_name, ometa.customer_last_name, ometa.customer_billing_email, ometa.customer_billing_phone, imeta.AdditionalNotes, imeta.BookingAssetId, imeta.ParticipantData, imeta.ResourcesData, imeta.AdditionalNotesDynamicKey
                FROM {$wpdb->prefix}posts
                INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}posts.ID
                INNER JOIN (
                        SELECT
                        order_item_id,
                        MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
                        MAX(CASE WHEN meta_key = '_ph_booking_dlang_product_id' THEN meta_value ELSE '' END) AS DefaultLangProductId,
                        MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
                        MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
                        MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
                        MAX(CASE WHEN meta_key = 'buffer_before_id' THEN meta_value ELSE '' END) AS buffer_before_id,
                        MAX(CASE WHEN meta_key = 'buffer_after_id' THEN meta_value Else '' END) AS buffer_after_id,
                        MAX(CASE WHEN meta_key = 'person_as_booking' THEN meta_value ELSE '' END) AS person_as_booking,
                        MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value Else '' END) AS no_of_persons,
                        MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
                        MAX(CASE WHEN meta_key = 'Additional Notes' THEN meta_value ELSE '' END) AS AdditionalNotes,
                        MAX(CASE WHEN meta_key = 'Assets' THEN meta_value ELSE '' END) AS BookingAssetId,
                        MAX(CASE WHEN meta_key = 'ph_bookings_participant_booking_data' THEN meta_value ELSE '' END) AS ParticipantData,
                        MAX(CASE WHEN meta_key = 'ph_bookings_resources_booking_data' THEN meta_value ELSE '' END) AS ResourcesData,
                        MAX(CASE WHEN meta_key = 'ph_bookings_customer_additional_notes' THEN meta_value ELSE '' END) AS AdditionalNotesDynamicKey
                        FROM {$wpdb->prefix}woocommerce_order_itemmeta
                        GROUP BY order_item_id
                ) as imeta on  imeta.order_item_id = oitems.order_item_id
                INNER JOIN (
                    SELECT ID, post_title as product_name
                    from {$wpdb->prefix}posts
                ) as p on imeta.ProductId = p.ID
                INNER JOIN (
                    SELECT
                    post_id,
                    MAX(CASE WHEN meta_key = '_billing_first_name' THEN meta_value ELSE '' END) AS customer_name,
                    MAX(CASE WHEN meta_key = '_billing_last_name' THEN meta_value ELSE '' END) AS customer_last_name,
                    MAX(CASE WHEN meta_key = '_billing_email' THEN meta_value ELSE '' END) AS customer_billing_email,
                    MAX(CASE WHEN meta_key = '_billing_phone' THEN meta_value ELSE '' END) AS customer_billing_phone
                    FROM {$wpdb->prefix}postmeta
                    GROUP BY post_id
                ) as ometa on ometa.post_id = {$wpdb->prefix}posts.ID
                INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
                INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
                WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
                AND (
                    {$wpdb->prefix}posts.post_status Like 'wc-pending'
                    OR {$wpdb->prefix}posts.post_status = 'wc-processing'
                    OR {$wpdb->prefix}posts.post_status = 'wc-on-hold'
                    OR {$wpdb->prefix}posts.post_status = 'wc-completed'
                    OR {$wpdb->prefix}posts.post_status = 'wc-cancelled'
                    OR {$wpdb->prefix}posts.post_status = 'wc-refunded'
                    OR {$wpdb->prefix}posts.post_status = 'wc-failed'
                    OR {$wpdb->prefix}posts.post_status = 'wc-partially-paid'
                    OR {$wpdb->prefix}posts.post_status = 'wc-partial-payment'
                    OR {$wpdb->prefix}posts.post_status = 'wc-ng-complete'
                    $ph_custom_order_status
                )
                AND tt.taxonomy IN ('product_type')
                AND t.slug = 'phive_booking' ";
            }

            $sub_query = "
			IF( LENGTH( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[7-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) ) = 7,
				CONCAT( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:7:".'"'."', -1), '".'"'."', -2),'".'"'."',1),'-01' ),
			SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[10-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) )";

            if( isset($_POST['ph-bookings-export-product']) && !empty($_POST['ph-bookings-export-product']) ){
                $product_id = $_POST['ph-bookings-export-product'];
                $query .= "AND (imeta.ProductId = '$product_id' OR imeta.DefaultLangProductId = '$product_id')";
            }

            if( isset($_POST['ph_booking_export_start_date']) && !empty($_POST['ph_booking_export_start_date']) )
            {
                $query .= " AND ( DATE(".$sub_query.")  >= '".$_POST['ph_booking_export_start_date']."')";
                // $query .= " OR DATE(imeta.BookFrom) >= '".$_POST['ph_booking_export_start_date']."')";
            }

            if( isset($_POST['ph_booking_export_end_date']) && !empty($_POST['ph_booking_export_end_date']) ){
                $query .= " AND (DATE(".$sub_query.") <= '".$_POST['ph_booking_export_end_date']."')";
                // $query .= " OR DATE(imeta.BookFrom) <= '".$_POST['ph_booking_export_end_date']."')";
            }

            if( isset($_POST['ph_booking_export_end_from_date']) && !empty($_POST['ph_booking_export_end_from_date']) )
            {
                $sub_query_for_booking_end = <<<EOD
                AND "{$_POST['ph_booking_export_end_from_date']}" <= IF(
                ( NOT ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
                (
                IF(
                    (imeta.BookTo = imeta.BookFrom AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month'),
                    IF (
                        SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
                        DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
                        DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
                    ),
                    IF(
                        NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
                        (
                            IF(
                                SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
                                DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
                                DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
                            )
                        ),
                        SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 )
                    )
                )
                ),
                (
                IF(
                    ( ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
                    (
                        IF(
                            NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
                            (
                                IF (  
                                    SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
                                    DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
                                    DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
                                )
                            ),
                            (
                                IF(
                                    SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) > 1 ,
                                    IF (
                                        SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day',
                                        DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) DAY),
                                        DATE_ADD(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), '-01'), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MONTH)        
                                    ),
                                    SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
                                )
                            )
                        )
                    ),
                    SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
                )
                )
                )
EOD;
                    $query.= $sub_query_for_booking_end;
            }

            if( isset($_POST['ph_booking_export_end_to_date']) && !empty($_POST['ph_booking_export_end_to_date']) )
            {
                $filter_end_to_with_time = $_POST['ph_booking_export_end_to_date']; 
                $filter_end_to_with_time.= " 23:59"; 
                $sub_query_for_booking_end = <<<EOD
                AND "{$filter_end_to_with_time}" >= IF(
                    ( NOT ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
                    (
                        IF(
                            (imeta.BookTo = imeta.BookFrom AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month'),
                            IF (
                                SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
                                DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
                                DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
                            ),
                            IF(
                                NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
                                (
                                    IF(
                                        SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
                                        DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
                                        DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
                                    )
                                ),
                                SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 )
                            )
                        )
                    ),
                    (
                        IF(
                            ( ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
                            (
                                IF(
                                    NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
                                    (
                                        IF (  
                                            SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
                                            DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
                                            DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
                                        )
                                    ),
                                    (
                                        IF(
                                            SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) > 1 ,
                                            IF (
                                                SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day',
                                                DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) DAY),
                                                DATE_ADD(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), '-01'), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MONTH)
                                            ),
                                            SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
                                        )
                                    )
                                )
                            ),
                            SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
                
                        )
                    )
                )
EOD;
                $query.= $sub_query_for_booking_end;
            }
    

            $query .= " ORDER BY oitems.order_item_id  DESC";
            $booked = $wpdb->get_results( $query, OBJECT );

            $processed = $this->ph_get_formatted_data($booked);
            
            // if TO is missing, concider FROM as TO
            foreach ($processed as $key => &$value) {
                if( empty($value['to']) && !empty($value['from'])){ // in the case of buffer, index 'from' wil be empty
                    $value['to'] = $value['from'];
                }
            }

            // Filter to add PH WooCommerce Product Addons Details
            $processed = apply_filters('ph_export_product_addon_details_with_bookings', $processed, $_POST);

            if(empty($processed))
            {
                // echo "<script>alert('No Bookings Found');</script>";
                $class = 'notice notice-error is-dismissible';
                $message = __( 'No Bookings Found.', 'bookings-and-appointments-for-woocommerce' );  
                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
            }
            else
            {
                $this->ph_download_send_headers("ph_booking_data_export_" . time() . ".csv");
                echo $this->ph_generate_csv($processed);
                die();
            }
        }

        public function ph_get_formatted_data($booked)
        {
            $processed = array();
            if(empty($booked))
            {
                return $processed;
            }

            foreach($booked as $key => $value)
            {
                $order_item_id = $value->order_item_id;

                // error_log("value  : ".print_r($value,1));
                if(isset($value->BookFrom) && !empty($value->BookFrom))
                {
                    $IntervalDetails	= unserialize($value->IntervalDetails);
                    $BookFrom 			= ph_maybe_unserialize($value->BookFrom);
                    $BookTo 			= ph_maybe_unserialize($value->BookTo);
                    // 130669 - Assets in export
                    $BookingAssetId     = ph_maybe_unserialize($value->BookingAssetId);
                    if(!empty($BookingAssetId)){
                        $Asset_settings 			= get_option( 'ph_booking_settings_assets', 1 );
                        foreach ($Asset_settings as $key => $rule) {
                            if (!empty($rule[$BookingAssetId])) {
                                $asset_name = $rule[$BookingAssetId]['ph_booking_asset_name'];
                            }
                        }
                    }
                    if( !empty($BookTo) && ! empty($IntervalDetails) ) {
                        $BookTo				= ! empty($BookTo) ? $BookTo : $BookFrom;
                        $interval 			= $IntervalDetails['interval'];
                        $interval_format	= $IntervalDetails['interval_format'];
                        if(strtotime($BookTo)==strtotime($BookFrom) && ($interval_format!='day' && $interval_format!='month') )
                        {
                            $BookTo 			= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookTo) ) );
                        }
                        elseif($interval_format!='day' && $interval_format!='month' )
                        {
                            $BookTo=str_replace('/', '-', $BookTo);
                            $BookTo 	= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookTo) ) ); // adding interval to last block
                        }
                    }
                    elseif (empty($BookTo) && !empty($IntervalDetails) ) {
                        
                        $interval 			= $IntervalDetails['interval'];
                        $interval_format	= $IntervalDetails['interval_format'];
                        $BookTo = $BookFrom;
                        if($interval_format!='day' && $interval_format!='month' )
                        {
                            $BookTo 			= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookFrom) ) );
                        }
                        elseif($interval>1)
                        {
                            $BookTo 			= date( 'Y-m-d', strtotime( "+$interval $interval_format",strtotime($BookFrom) ) );	
                        }
                        
                    }

                    // convert to wp format
                    if( strlen($BookFrom) > 10 ){
                        $BookFrom = ph_wp_date( get_option( 'date_format' )." ".get_option( 'time_format' ), strtotime( $BookFrom ) );
                    }else{
                        $BookFrom = ph_wp_date( get_option( 'date_format' ), strtotime( $BookFrom ) );
                    }

                    if( strlen($BookTo) > 10 ){
                        $BookTo = ph_wp_date( get_option( 'date_format' )." ".get_option( 'time_format' ), strtotime( $BookTo ) );
                    }else{
                        $BookTo = ph_wp_date( get_option( 'date_format' ), strtotime( $BookTo ) );
                    }

                    if(isset($value->order_id))
                    {
                        $processed[$order_item_id][__('Order', 'bookings-and-appointments-for-woocommerce')] = $value->order_id;
                    }

                    if(isset($value->product_name))
                    {
                        $processed[$order_item_id][__('Product', 'bookings-and-appointments-for-woocommerce')] = $value->product_name;
                    }

                    if(isset($value->BookingStatus))
                    {
                        $processed[$order_item_id][__('Booking Status', 'bookings-and-appointments-for-woocommerce')] = __( ph_map_booking_status_to_name(ph_maybe_unserialize($value->BookingStatus)),'bookings-and-appointments-for-woocommerce' );
                    }

                    $processed[$order_item_id][__('From', 'bookings-and-appointments-for-woocommerce')] = $BookFrom;
                    $processed[$order_item_id][__('To', 'bookings-and-appointments-for-woocommerce')] = $BookTo;
                
                    if(isset($_POST['ph_booking_export_asset_checkbox']) ){
                        $processed[$order_item_id][__('Asset', 'bookings-and-appointments-for-woocommerce')] = isset($asset_name) ? $asset_name : '';
                    }
                    if(isset($value->no_of_persons))
                    {
                        $number_of_person = '';
                        if(!empty($value->no_of_persons))
                        {
                            $number_of_person = substr($value->no_of_persons, 0, 10);
                        }
                        if(isset($_POST['ph_booking_export_number_of_participants_checkbox'])){
                            $processed[$order_item_id][__('No of Participants', 'bookings-and-appointments-for-woocommerce')] = $number_of_person;
                        }
                    }
                    if (isset($value->ParticipantData) && isset($_POST['ph_booking_export_participant_details_checkbox']))
                    {
                        $participant_export = '';
                        $value->ParticipantData = maybe_unserialize($value->ParticipantData);
                        if(is_array($value->ParticipantData) && !empty($value->ParticipantData))
                        {
                            $participant_count = 0;
                            foreach ($value->ParticipantData as $participant) 
                            {
                                $participant_export .= implode(":",$participant);
                                if(++$participant_count < count($value->ParticipantData))
                                {
                                    $participant_export .= ', ';
                                }
                            }
                        }
                        $processed[$order_item_id][__('Booked Participants', 'bookings-and-appointments-for-woocommerce')] = $participant_export;
                    }
                    if (isset($value->ResourcesData) && isset($_POST['ph_booking_export_resources_details_checkbox']))
                    {
                        $resources_export = '';
                        $value->ResourcesData = maybe_unserialize($value->ResourcesData);
                        if(is_array($value->ResourcesData) && !empty($value->ResourcesData))
                        {
                            $resources_export = implode(',', array_column($value->ResourcesData, 'resource_label'));
                        }
                        $processed[$order_item_id][__('Booked Resources', 'bookings-and-appointments-for-woocommerce')] = $resources_export;
                    }

                    $customer_name = !empty($value->customer_name) ? $value->customer_name.' '.$value->customer_last_name : $value->customer_billing_email;

                    if(isset($_POST['ph_booking_export_booked_by_checkbox'])){
                        $processed[$order_item_id][__('Booked By', 'bookings-and-appointments-for-woocommerce')] = $customer_name;
                    }
                    if(isset($_POST['ph_booking_export_customer_email_checkbox'])){
                        $processed[$order_item_id][__('Customer Email','bookings-and-appointments-for-woocommerce')] = $value->customer_billing_email;
                    }
                    if(isset($_POST['ph_booking_export_customer_phone_checkbox'])){
                        $processed[$order_item_id][__('Customer Phone', 'bookings-and-appointments-for-woocommerce')] = $value->customer_billing_phone;
                    }
                    if(isset($_POST['ph_booking_export_additional_notes_checkbox'])){
                        $processed[$order_item_id][__('Additional Notes', 'bookings-and-appointments-for-woocommerce')] = (isset($value->AdditionalNotes) && !empty($value->AdditionalNotes)) ? $value->AdditionalNotes : ((isset($value->AdditionalNotesDynamicKey) && !empty($value->AdditionalNotesDynamicKey)) ? ph_maybe_unserialize($value->AdditionalNotesDynamicKey) : '');
                    }
                    if(isset($_POST['ph_booking_export_booking_cost_checkbox']))
                    {
                        // 146094
                        if(isset($value->order_item_id))
                        {
                            // error_log($value->order_item_id);
                            if(class_exists('WC_Order_Factory'))
                            {
                                $order_item = WC_Order_Factory::get_order_item($value->order_item_id);
                                if(is_object($order_item))
                                {
                                    $total = $order_item->get_total() + $order_item->get_total_tax();
                                    $processed[$order_item_id][__('Booking Cost', 'bookings-and-appointments-for-woocommerce')] = html_entity_decode(strip_tags(wc_price($total)));
                                }
                            }
                        }

                    }
                }
                
            }

            return $processed;
        }

        public function ph_generate_csv(array &$array)
        {
            if (count($array) == 0) {
                return null;
            }

            ob_start();
            
            $fp = fopen("php://output", 'w');
        
            // Collect all unique keys from the array
            $all_keys = [];
            foreach ($array as $row) {
                $all_keys = array_merge($all_keys, array_keys($row));
            }
            $all_keys = array_unique($all_keys);  // Remove duplicates
        
            // Write header with all keys
            fputcsv($fp, $all_keys);
        
            // Write data rows, ensuring all columns align
            foreach ($array as $row) {
                $line = [];
                foreach ($all_keys as $key) {
                    $line[] = isset($row[$key]) ? $row[$key] : '';  // Fill missing columns with empty strings
                }
                fputcsv($fp, $line);
            }
            fclose($fp);
            return ob_get_clean();
        }

        public function ph_download_send_headers($filename) {
            // disable caching
            $now = gmdate("D, d M Y H:i:s");
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            header("Last-Modified: {$now} GMT");
    
            // force download  
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
    
            // disposition / encoding on response body
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");
        }

	} new Ph_Export_Bookings();
}