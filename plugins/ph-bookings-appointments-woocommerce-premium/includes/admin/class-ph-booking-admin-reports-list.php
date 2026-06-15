<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class phive_booking_all_list extends WP_List_Table {
	protected $max_items;
	public $woocommerce_table;

	/**
	 * iCalendar download icon URL.
	 *
	 * @var string
	 */
	private $icalendar_icon_url;

	/**
	 * iCalendar settings.
	 *
	 * @var array
	 */
	private $icalendar_settings;

	/**
	 * iCalendar status.
	 *
	 * @var bool
	 */
	private $is_ical_enabled;

	public function __construct() {
		
		$this->woocommerce_table = PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled();
		parent::__construct( array() );

		$this->icalendar_icon_url = plugins_url('ph-bookings-appointments-woocommerce-premium') . '/resources/icons/ics.svg';
		$this->icalendar_settings = get_option('ph_booking_settings_icalendar', array());
		$this->is_ical_enabled = (isset($this->icalendar_settings['enable_icalendar']) && 'yes' == $this->icalendar_settings['enable_icalendar']) ? true : false;
	}

	/**
	* Set bulk action options
	*/
	function get_bulk_actions() {
		$bulk_actions = array(
			'delete' 		=> __( 'Delete Booking(s)', 'bookings-and-appointments-for-woocommerce' ),
			'cancel'   		=> __( 'Cancel Booking(s)', 'bookings-and-appointments-for-woocommerce' ),
			'confirm'   	=> __( 'Confirm Booking(s)', 'bookings-and-appointments-for-woocommerce' ),
			'status-paid'	=> __( 'Mark as Paid', 'bookings-and-appointments-for-woocommerce'),
			'status-un-paid'=> __( 'Mark as Un-Paid', 'bookings-and-appointments-for-woocommerce'),
			're-sync-google-calender'=> __( 'Re-sync google calender', 'bookings-and-appointments-for-woocommerce'),
		);

		if($this->is_ical_enabled) {
			$bulk_actions['download-icalendar'] = __('Download iCal File', 'bookings-and-appointments-for-woocommerce');
		}

		$bulk_actions=apply_filters('ph_bookings_report_list_bulk_actions',$bulk_actions);
		return $bulk_actions;
	}

	/**
	* Set the colomn titles
	*/
	public function get_columns() {

		$columns = array(
			'cb'				=> 'cb',
			'order_id'			=> esc_html( __( 'Order', 'bookings-and-appointments-for-woocommerce' ) ),
			'product'			=> esc_html( __( 'Product', 'bookings-and-appointments-for-woocommerce' ) ),
			'booking_status'	=> esc_html( __( 'Booking Status', 'bookings-and-appointments-for-woocommerce' ) ),
			'start_date'		=> esc_html( __( 'From', 'bookings-and-appointments-for-woocommerce' ) ),
			'end_date'			=> esc_html( __( 'To', 'bookings-and-appointments-for-woocommerce' ) ),
			'no_of_persons'		=> esc_html( __( 'No of Participants', 'bookings-and-appointments-for-woocommerce' ) ),
			'bookedby'			=> esc_html( __( 'Booked by', 'bookings-and-appointments-for-woocommerce' ) ),
		);

		if($this->is_ical_enabled) {
			$columns['icalendar'] = esc_html(__( 'Download iCal File', 'bookings-and-appointments-for-woocommerce' )) ;
		}

		return $columns;
	}

	/**
	* Set sortable columns
	*/
	protected function get_sortable_columns() {

		$sortable_columns = array(
			'order_id'		=> array( 'order_id', true ),
			'product'		=> array( 'product', true ),
			'start_date'	=> array( 'start_date', false ),
			'end_date'		=> array( 'end_date', false ),
			'bookedby'		=> array( 'bookedby', false )
		);

		return $sortable_columns;
	}

	/**
	* Prapare the table content
	*/
	public function prepare_items() {
		
		$this->process_bulk_action();

		// Admin notices
		$this->ph_admin_notices();

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page		  = absint( $this->get_pagenum() );
		$per_page			  = 20;

		$this->get_items( $current_page, $per_page );

		/**
		 * Pagination
		 */
		$this->set_pagination_args( array(
			'total_items' 	=> $this->max_items,
			'per_page'		=> $per_page,
			'total_pages' 	=> ceil( $this->max_items / $per_page )
		) );
		
	}
	/**
	* Show the admin notices
	*/
	public function ph_admin_notices()
	{
		// Admin notice after bookings has been deleted
		if (isset($_REQUEST['deleted']) && $_REQUEST['deleted'] > 0) {

			echo '<div class="notice ph-notice-success notice-success is-dismissible">
						<p>' . sprintf(esc_html__('%d item(s) deleted', 'bookings-and-appointments-for-woocommerce'), $_REQUEST['deleted']) . '</p>
						<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Dismiss this notice', 'bookings-and-appointments-for-woocommerce') . '</span></button>
					</div>';
		}
	}

	public function process_bulk_action() { 
		$map_booking_status_to_name = array(
			'paid'					=>	'Paid',
			'un-paid'				=>	'Unpaid',
			'canceled'				=>	'Cancelled',
			'requires-confirmation'	=>	'Requires Confirmation',
			'refunded'				=>  'Refunded'
		);
		$action = $this->current_action();
		$user_id = get_current_user_id();
		if( isset($_REQUEST['ph_selected_bookings']) && is_array($_REQUEST['ph_selected_bookings']) ){
			switch ($action) {
				case 'cancel':
					$i=0;
					global $woocommerce;
					foreach ($_REQUEST['ph_selected_bookings'] as $key => $item_id) {
						$success = wc_update_order_item_meta( $item_id, 'canceled', 'yes' );
						$status_chage = wc_update_order_item_meta( $item_id, 'booking_status', array('canceled') );

						// 103410 - Switching to product language
						$order_id 	= wc_get_order_id_by_order_item_id($item_id);
						$order 		= wc_get_order($order_id);
						ph_wpml_language_switch_admin_email($order, '', 'order', '');
						wc_update_order_item_meta( $item_id, __('Booking Status','bookings-and-appointments-for-woocommerce'), __($map_booking_status_to_name['canceled'],'bookings-and-appointments-for-woocommerce') );

						// 103410 - Switching back to admin language
						ph_wpml_language_switch_admin_email('', $user_id, 'admin', '');

						if( $success ){
							// $order->update_status('cancelled');
							$buffer_before_id=wc_get_order_item_meta( $item_id, "buffer_before_id", 1 );
							$buffer_after_id=wc_get_order_item_meta( $item_id, "buffer_after_id", 1 );
							
							if(!empty($buffer_before_id)){
								update_post_meta( $buffer_before_id[0], 'ph_canceled', '1' );
								
							}
							if(!empty($buffer_after_id)){
								update_post_meta( $buffer_after_id[0], 'ph_canceled', '1' );
								
							}

							// Executed only when Zoom plugin is installed and a zoom meeting exists.
							do_action( 'ph_zoom_update_meeting_status_as_deleted', $item_id );

							do_action( 'ph_booking_status_changed', 'cancelled', $item_id, $order_id, $order  );
							
							$i++;
						}
					}
					echo '<div class="notice ph-notice-success notice-success is-dismissible">
						<p>'.sprintf( esc_html__( '%d item(s) canceled', 'bookings-and-appointments-for-woocommerce' ), $i ).'</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.','bookings-and-appointments-for-woocommerce').'</span></button></div>';
					
					break;
				
				case 'confirm':
					$i=0;
					global $woocommerce;
					foreach ($_REQUEST['ph_selected_bookings'] as $key => $item_id) {

						$line_item = new WC_Order_Item_Product($item_id);

						$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id($line_item->get_product_id() );		//WPML compatibilty
						$_product = wc_get_product( $product_id );
						
						$required_confirmation 	= get_post_meta( $_product->get_id(), "_phive_book_required_confirmation", 1 );
						
						if( $required_confirmation == 'yes' ){

							$success = wc_update_order_item_meta( $item_id, 'confirmed', 'yes' );
							$success_status_chage = wc_update_order_item_meta( $item_id, 'booking_status', array('un-paid') );

							// 103410 - Switching to product language
							$order_id	= wc_get_order_id_by_order_item_id($item_id);
							$order 		= wc_get_order($order_id);
							ph_wpml_language_switch_admin_email($order, '', 'order', '');	
							wc_update_order_item_meta( $item_id, __('Booking Status','bookings-and-appointments-for-woocommerce'), __($map_booking_status_to_name['un-paid'],'bookings-and-appointments-for-woocommerce'));

							// 140472
							$order_total = $order->get_total();

							// 143735
							$old_wc_order_status = $order->get_status();
							if (($order_total <= 0) || ($old_wc_order_status == 'processing' || $old_wc_order_status == 'completed'))
							{
								wc_update_order_item_meta( $item_id, 'booking_status', array('paid') );
								wc_update_order_item_meta( $item_id, __('Booking Status','bookings-and-appointments-for-woocommerce'), __($map_booking_status_to_name['paid'],'bookings-and-appointments-for-woocommerce'));
							}

							// 103410 - Switching back to current language
							ph_wpml_language_switch_admin_email('', $user_id, 'admin', '');

							if( $success ){
								$i++;
								$order_id 	= wc_get_order_id_by_order_item_id($item_id);
								do_action( 'ph_booking_status_changed', 'confirmed', $item_id, $order_id  );

							}

						}
							
						$order_id 	= wc_get_order_id_by_order_item_id($item_id);
						$order 		= wc_get_order($order_id);

						if( $required_confirmation == 'yes' && $this->is_confrimed_all_bookings_of_order($order) ){
							$order->update_status('pending');

							foreach( $order->get_items() as $item_id_2 => $item ){

								$line_item = new WC_Order_Item_Product($item_id_2);

								$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id($line_item->get_product_id() );		//WPML compatibilty
								$_product = wc_get_product( $product_id );

								
								$required_confirmation 	= get_post_meta( $_product->get_id(), "_phive_book_required_confirmation", 1 );

								if($required_confirmation == 'no'){
									// $success_status_chage = wc_update_order_item_meta( $item_id, 'booking_status', array('un-paid') );
									// wc_update_order_item_meta( $item_id, __('Booking Status','bookings-and-appointments-for-woocommerce'), 'un-paid' );

									$success = wc_update_order_item_meta( $item_id_2, 'booking_status',array('un-paid') );

									// 103410 - Switching to product language
									ph_wpml_language_switch_admin_email($order, '', 'order', '');	
									wc_update_order_item_meta( $item_id_2, __('Booking Status','bookings-and-appointments-for-woocommerce'), __($map_booking_status_to_name['un-paid'],'bookings-and-appointments-for-woocommerce') );

									// 103410 - Switching back to current language
									ph_wpml_language_switch_admin_email('', $user_id, 'admin', '');

									if( $success ){
										do_action( 'ph_booking_status_changed', 'un-paid', $item_id_2, $order_id  );
									}
								}
							}

							// 140472
							$order_total = $order->get_total();
							if ($order_total <= 0)
							{
								$order->update_status( 'processing' );
							}

							// 143735
							if($old_wc_order_status == 'processing' || $old_wc_order_status == 'completed')
							{
								$order->update_status($old_wc_order_status);
							}

							// $status_notice = sprintf( __("All the bookings for the order %d have been confirmed.  And the order status is marked as pending for payment", 'bookings-and-appointments-for-woocommerce'), $order_id );
						}/*else{
							$status_notice = sprintf( __('Some bookings for the order %d needs to be confirmed.', 'bookings-and-appointments-for-woocommerce'), $order_id );
						}*/

					}
					
					echo '<div class="notice ph-notice-success notice-success is-dismissible">';
					echo '<p>'.sprintf( esc_html__( '%d item(s) Booking Confirmed', 'bookings-and-appointments-for-woocommerce' ), $i ).'</p>';
					// echo '<p>'.$status_notice.'</p>';
					echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice','bookings-and-appointments-for-woocommerce').'</span></button>';
					echo '</div>';
					
					break;
				
				case 'delete':
					$i=0;
					foreach ($_REQUEST['ph_selected_bookings'] as $key => $item_id) {
						
						$order_id 	= wc_get_order_id_by_order_item_id($item_id);
						 if (  $item_id = absint( $item_id ) ) {
							 do_action( 'ph_booking_status_changed', 'deleted', $item_id, $order_id  );
							 wc_delete_order_item( $item_id );
							 $buffer_before_id=wc_get_order_item_meta( $item_id, "buffer_before_id", 1 );
							 $buffer_after_id=wc_get_order_item_meta( $item_id, "buffer_after_id", 1 );
							if(!empty($buffer_before_id)){
								wp_delete_post( $buffer_before_id[0]);
							
							}
							if(!empty($buffer_after_id)){
								wp_delete_post( $buffer_after_id[0]);
							
							}
							$i++;
							}
					}

					// Redirecting to all booking page
					$redirect_url = admin_url('admin.php?page=bookings');
					$redirect_url = add_query_arg(
						array(
							'deleted'     => $i,
							'paged'         => absint($this->get_pagenum()),
						),
						$redirect_url
					);
					wp_safe_redirect($redirect_url);
					exit;

					break;
				case 'status-paid':
					$i=0;
					global $woocommerce;
					foreach ($_REQUEST['ph_selected_bookings'] as $key => $item_id) {
						$order_id 	= wc_get_order_id_by_order_item_id($item_id);

						$success = wc_update_order_item_meta( $item_id, 'booking_status',array('paid') );

						// 103410 - Switching to product language
						$order 		= wc_get_order($order_id);
						$current_lang = ph_wpml_language_switch_admin_email($order, '', 'order', '');
						wc_update_order_item_meta( $item_id, __('Booking Status','bookings-and-appointments-for-woocommerce'), __($map_booking_status_to_name['paid'],'bookings-and-appointments-for-woocommerce') );

						// 103410 - Switching back to current language
						ph_wpml_language_switch_admin_email('', $user_id, 'admin', '');

						if( $success ){

							// Zoom.
							do_action( 'ph_zoom_create_meeting_for_paid_booking', $item_id, $order_id );

							do_action( 'ph_booking_status_changed', 'paid', $item_id, $order_id  );
							
							$i++;
						}
					}
					echo '<div class="notice ph-notice-success notice-success is-dismissible">
						<p>'.sprintf( esc_html__( '%d Booking staus(s) changed to Paid', 'bookings-and-appointments-for-woocommerce' ), $i ).'</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice','bookings-and-appointments-for-woocommerce').'</span></button></div>';
					
					break;
				case 'status-un-paid':
					$i=0;
					global $woocommerce;
					foreach ($_REQUEST['ph_selected_bookings'] as $key => $item_id) {
						$order_id 	= wc_get_order_id_by_order_item_id($item_id);

						$success = wc_update_order_item_meta( $item_id, 'booking_status',array('un-paid') );

						// 103410 - Switching to product language
						$order_id	= wc_get_order_id_by_order_item_id($item_id);
						$order 		= wc_get_order($order_id);
						$current_lang = ph_wpml_language_switch_admin_email($order, '', 'order', '');
						wc_update_order_item_meta( $item_id, __('Booking Status','bookings-and-appointments-for-woocommerce'), __($map_booking_status_to_name['un-paid'],'bookings-and-appointments-for-woocommerce') );

						// 103410 - Switching back to current language
						ph_wpml_language_switch_admin_email('', $user_id, 'admin', '');

						if( $success ){
							do_action( 'ph_booking_status_changed', 'un-paid', $item_id, $order_id  );
							
							$i++;
						}
					}
					echo '<div class="notice ph-notice-success notice-success is-dismissible">
						<p>'.sprintf( esc_html__( '%d Booking status(s) changed to Un-Paid', 'bookings-and-appointments-for-woocommerce' ), $i ).'</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice','bookings-and-appointments-for-woocommerce').'</span></button></div>';
					
					break;
				case 're-sync-google-calender':
					$i = 0;
					foreach ($_REQUEST['ph_selected_bookings'] as $key => $item_id) {
						$order_id 	= wc_get_order_id_by_order_item_id($item_id);
						 if (  $item_id = absint( $item_id ) ) {
							 do_action( 'ph_booking_item_calender_resynced', 're-sync-google-calender', $item_id, $order_id  );
							 $ph_recent_google_calendar_sync_response = get_option('ph_recent_google_calendar_sync_response',[]);

							 if( isset($ph_recent_google_calendar_sync_response['code']) && $ph_recent_google_calendar_sync_response['code'] != 200 ) {
								echo '<div class="notice ph-notice-error notice-error is-dismissible">
								<p>'.__(sprintf( 'Sync Failed ( Error Code: %s )',$ph_recent_google_calendar_sync_response['code']),'bookings-and-appointments-for-woocommerce').' - <a href="https://www.pluginhive.com/knowledge-base/woocommerce-bookings-and-appointments-plugin-faqs/?utm_source=bookings&utm_medium=plugin_settings#GoogleSync"  target="_blank">'.__('How to fix the error','bookings-and-appointments-for-woocommerce').'</a></p></div>';
								break 2;
							 }
						 }
						 $i++;
					}

					echo '<div class="notice ph-notice-success notice-success is-dismissible">
						<p>'.sprintf( esc_html__( '%d items(s) Re-synced with Google Calender', 'bookings-and-appointments-for-woocommerce' ), $i ).'</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice','bookings-and-appointments-for-woocommerce').'</span></button></div>';

					break;
				case 'download-icalendar':
					$i = 0;
					$order_ids = array();
					foreach($_REQUEST['ph_selected_bookings'] as $key => $item_id) {
						$order_id = wc_get_order_id_by_order_item_id($item_id);
						$order_ids[] = $item_id;
					}
					do_action('ph_bookings_bulk_icalendar_download', $order_ids);
					break;
				default:
					break;
			}
			do_action('ph_bookings_process_bulk_action',$_REQUEST['ph_selected_bookings'],$action);
		}
	}

	private function is_confrimed_all_bookings_of_order($order){

		$is_confrimed = true;

		$items 		= $order->get_items();

		foreach ($items as $order_item_id => $line_item) {

			$_product = wc_get_product( $line_item->get_product_id() );
			
			if( empty($_product) ){
				continue;
			}

			$required_confirmation 	= get_post_meta( $_product->get_id(), "_phive_book_required_confirmation", 1 );
			
			if( $required_confirmation == 'yes' && $line_item->get_meta('confirmed') != 'yes' ){
				$is_confrimed = false;
				break;
			}
		}
		return $is_confrimed;
	}

	function column_cb( $item ) {
		return  sprintf('<input id="cb-select-%s" type="checkbox" name="ph_selected_bookings[]" value="%s">',$item['ID'],$item['ID']);
	}

	/**
	 * iCalendar download icon.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_icalendar($item) {

		if (!$this->is_ical_enabled) {
			return;
		}

		return sprintf(
			'<img src="%s" class="ph-bookings-ics-download-btn" style="cursor:pointer;width:25px;" data-order-id="%s" data-item-id="%s" data-invoker="%s"/>',
			$this->icalendar_icon_url,
			$item['order_id'],
			$item['ID'],
			'all-bookings'
		);
    } 

	/**
	* Disply the table content
	*/
	public function get_items( $current_page, $per_page ) {
		
		$filters = array(
			'ph_booking_status' 		=> isset( $_GET['ph_booking_status'] ) ? $_GET['ph_booking_status'] : '',
			'ph_filter_product_ids' 	=> isset( $_GET['ph_filter_product_ids']) ? $_GET['ph_filter_product_ids'] : '',
			'ph_filter_from' 			=> isset( $_GET['ph_filter_from']) ? $_GET['ph_filter_from'] : '',
			'ph_filter_end_from' 			=> isset( $_GET['ph_filter_end_from']) ? $_GET['ph_filter_end_from'] : '',
			'ph_filter_to' 				=> isset( $_GET['ph_filter_to']) ? $_GET['ph_filter_to'] : '',
			'ph_filter_end_to' 				=> isset( $_GET['ph_filter_end_to']) ? $_GET['ph_filter_end_to'] : '',
		);

		// tickets 113580 -filter the bookings  based on date & time
		$filters = apply_filters('ph_filters_get_item_time',$filters);

		// error_log(print_r("get_items filters: ",1));
		// error_log(print_r($filters,1));
		$this->max_items 	= $this->ph_get_bookings_count( $filters, $current_page, $per_page );
		$this->items 		= $this->ph_get_bookings_for_current_page( $filters, $current_page, $per_page );
		
		return;
	}

	/**
	* Get count of all items
	*
	*/
	private function ph_get_bookings_count( $filters, $current_page, $per_page ){
		
		$filters = apply_filters('ph_booking_filters_for_bookings_list',$filters);
		global $wpdb;

		// 195218 Need to display custom order status bookings
		$custom_order_status = apply_filters(('ph_all_bookings_for_custom_order_status'), []);
		$ph_custom_order_status = "";
		if (!empty($custom_order_status) && is_array($custom_order_status)) {
			$table_name = $this->woocommerce_table ? "{$wpdb->prefix}wc_orders.status" : "{$wpdb->prefix}posts.post_status";
			foreach ($custom_order_status as $order_status) {

				$ph_custom_order_status .= " OR $table_name = '$order_status'";
			}
		}

		if($this->woocommerce_table) {
			$query = "SELECT count(distinct oitems.order_item_id)
			FROM {$wpdb->prefix}wc_orders
			INNER JOIN {$wpdb->prefix}wc_orders_meta ometa on ometa.order_id = {$wpdb->prefix}wc_orders.id
			INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}wc_orders.id
			INNER JOIN (
					SELECT 
					order_item_id,
					MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
					MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
					MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
					MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
					MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails
				FROM {$wpdb->prefix}woocommerce_order_itemmeta 
				GROUP BY order_item_id
			) as imeta on imeta.order_item_id = oitems.order_item_id
			INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId 
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
			INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
			WHERE {$wpdb->prefix}wc_orders.type IN ('shop_order', 'shop_order_refund')
			AND (
				{$wpdb->prefix}wc_orders.status = 'wc-pending'
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
			AND t.slug = 'phive_booking'";
		} else {
			$query = "SELECT count(distinct oitems.order_item_id)
			FROM {$wpdb->prefix}posts
			INNER JOIN {$wpdb->prefix}postmeta ometa on ometa.post_id = {$wpdb->prefix}posts.ID
			INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}posts.ID
			INNER JOIN (
					SELECT 
					order_item_id,
					MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
					MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
					MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
					MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
					MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails
				FROM {$wpdb->prefix}woocommerce_order_itemmeta 
				GROUP BY order_item_id
			) as imeta on imeta.order_item_id = oitems.order_item_id
			INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId 
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
			INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
			WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
			AND (
				{$wpdb->prefix}posts.post_status = 'wc-pending'
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
			AND ometa.meta_key in ('_billing_first_name','_billing_email')
			AND tt.taxonomy IN ('product_type')
			AND t.slug = 'phive_booking'";
		}
		$sub_query = "
		IF ( LENGTH( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[7-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) ) = 7,
			CONCAT( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:7:".'"'."', -1), '".'"'."', -2),'".'"'."',1),'-01'),
		SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[10-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) )";
		if( !empty($filters['ph_booking_status']) ){
			$booking_status_len = strlen($filters['ph_booking_status']);
			$query .= " AND (SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookingStatus, 'i:0;s:".$booking_status_len.":".'"'."', -1), '".'"'."', 1) = '".$filters['ph_booking_status']."' OR imeta.BookingStatus = '".$filters['ph_booking_status']."' ) ";
		}
		if( !empty($filters['ph_filter_product_ids']) ){
			$query .= "AND imeta.ProductId = '".$filters['ph_filter_product_ids']."'";
		}
		if( !empty($filters['ph_filter_from']) ){
			$query .= " AND (DATE(".$sub_query.") >= '".$filters['ph_filter_from']."'";
			$query .= " OR DATE(imeta.BookFrom) >= '".$filters['ph_filter_from']."')";
		}
		if( !empty($filters['ph_filter_to']) ){
			$query .= " AND (DATE(".$sub_query.") <= '".$filters['ph_filter_to']."'";
			$query .= " OR DATE(imeta.BookFrom) <= '".$filters['ph_filter_to']."')";
		}

		if( !empty($filters['ph_filter_end_from']) ){
			$sub_query_for_booking_end = <<<EOD
 AND "{$filters['ph_filter_end_from']}" <= IF(
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


		if( !empty($filters['ph_filter_end_to']) ){
			$filter_end_to_with_time = $filters['ph_filter_end_to']; 
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
				IF(
					SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
					last_day(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ),"-27")),
					SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 )
				)
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
			IF(
				SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
				last_day(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ),"-27")),
				SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
			)

		)
	)
)
EOD;
		$query.= $sub_query_for_booking_end;
		}



		$query = apply_filters('ph_booking_query_for_bookings_count',$query,$filters);
		$bookings_count = $wpdb->get_var( $query );
		return $bookings_count;
	}














	private function ph_get_bookings_for_current_page( $filters, $current_page, $per_page ){

		$filters = apply_filters('ph_booking_filters_for_bookings_list',$filters);
		global $wpdb;

		// 195218 Need to display custom order status bookings
		$custom_order_status = apply_filters(('ph_all_bookings_for_custom_order_status'), []);
		$ph_custom_order_status = "";
		if (!empty($custom_order_status) && is_array($custom_order_status)) {
			$table_name = $this->woocommerce_table ? "{$wpdb->prefix}wc_orders.status" : "{$wpdb->prefix}posts.post_status";
			foreach ($custom_order_status as $order_status) {

				$ph_custom_order_status .= " OR $table_name = '$order_status'";
			}
		}

		if ( $this->woocommerce_table ) {

			$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id, ometa.customer_name, ometa.customer_last_name, ometa.customer_billing_email, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails, imeta.no_of_persons
			FROM {$wpdb->prefix}wc_orders
			INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}wc_orders.id
			INNER JOIN (
					SELECT
					order_item_id,
					MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
					MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
					MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
					MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
					MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value Else '' END) AS no_of_persons,
					MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails
					FROM {$wpdb->prefix}woocommerce_order_itemmeta
					GROUP BY order_item_id
			) as imeta on  imeta.order_item_id = oitems.order_item_id
			LEFT JOIN (
					SELECT
					order_id,
					first_name AS customer_name,
					last_name AS customer_last_name,
					email AS customer_billing_email
					FROM {$wpdb->prefix}wc_order_addresses
					GROUP BY order_id
			) as ometa on ometa.order_id = {$wpdb->prefix}wc_orders.id
			INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
			WHERE {$wpdb->prefix}wc_orders.type IN ('shop_order', 'shop_order_refund')
			AND (
				{$wpdb->prefix}wc_orders.status = 'wc-pending'
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
			AND t.slug = 'phive_booking'";
		} else {
			$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id, ometa.customer_name, ometa.customer_last_name, ometa.customer_billing_email, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails, imeta.no_of_persons
			FROM {$wpdb->prefix}posts
			INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}posts.ID
			INNER JOIN (
					SELECT
					order_item_id,
					MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
					MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
					MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
					MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
					MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value Else '' END) AS no_of_persons,
					MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails
					FROM {$wpdb->prefix}woocommerce_order_itemmeta
					GROUP BY order_item_id
			) as imeta on  imeta.order_item_id = oitems.order_item_id
			INNER JOIN (
					SELECT
					post_id,
					MAX(CASE WHEN meta_key = '_billing_first_name' THEN meta_value ELSE '' END) AS customer_name,
					MAX(CASE WHEN meta_key = '_billing_last_name' THEN meta_value ELSE '' END) AS customer_last_name,
					MAX(CASE WHEN meta_key = '_billing_email' THEN meta_value ELSE '' END) AS customer_billing_email
					FROM {$wpdb->prefix}postmeta
					GROUP BY post_id
			) as ometa on ometa.post_id = {$wpdb->prefix}posts.ID
			INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
			WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
			AND (
				{$wpdb->prefix}posts.post_status = 'wc-pending'
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
			AND t.slug = 'phive_booking'";
		}
			
		// AND ometa.meta_key IN ('_billing_first_name','_billing_email')
		// AND CASE WHEN '_billing_first_name' THEN ometa.meta_key IN('_billing_first_name') ElSE ometa.meta_key IN ('_billing_email') END
		$sub_query = "
			IF( LENGTH( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[7-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) ) = 7,
				CONCAT( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:7:".'"'."', -1), '".'"'."', -2),'".'"'."',1),'-01' ),
			SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[10-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) )";
		
		if( !empty($filters['ph_booking_status']) ){
			$len = strlen($filters['ph_booking_status']);
			$query .= " AND (SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookingStatus, 'i:0;s:".$len.":".'"'."', -1), '".'"'."', 1) = '".$filters['ph_booking_status']."' OR imeta.BookingStatus = '".$filters['ph_booking_status']."' ) ";
		}
		if( !empty($filters['ph_filter_product_ids']) ){
			$query .= " AND imeta.ProductId = '".$filters['ph_filter_product_ids']."'";
		}
		if( !empty($filters['ph_filter_from']) ){
			$query .= " AND ( DATE(".$sub_query.")  >= '".$filters['ph_filter_from']."')";
			// $query .= " OR DATE(imeta.BookFrom) >= '".$filters['ph_filter_from']."')";
		}
		if( !empty($filters['ph_filter_to']) ){
			$query .= " AND (DATE(".$sub_query.") <= '".$filters['ph_filter_to']."')";
			// $query .= " OR DATE(imeta.BookFrom) <= '".$filters['ph_filter_to']."')";
		}

		if( !empty($filters['ph_filter_end_from']) ){
			$sub_query_for_booking_end = <<<EOD
 AND "{$filters['ph_filter_end_from']}" <= IF(
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


		if( !empty($filters['ph_filter_end_to']) ){
			$filter_end_to_with_time = $filters['ph_filter_end_to']; 
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
				IF(
					SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
					last_day(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ),"-27")),
					SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 )
				)
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
			IF(
				SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
				last_day(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ),"-27")),
				SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
			)

		)
	)
)
EOD;
		$query.= $sub_query_for_booking_end;

			// $query .= " AND (DATE(".$sub_query_for_booking_end.") <= '".$filter_end_to_with_time."'";
			// $query .= " AND (DATE(".$sub_query_for_booking_end.") <= '".$filter_end_to_with_time."'";
		}


		$sortby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'order_id';
		switch ( $sortby ) {
			case 'order_status':
				$orderby = 'imeta.BookingStatus';
				break;
			
			case 'product':
				$orderby = 'imeta.ProductId';
				break;
			
			case 'start_date':
				$orderby = 'order_id';
				// $orderby = "(imeta.BookFrom OR SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[10-16]"'.":".'"'."', -1), '".'"'."', 1)) ";
				
				break;
			
			case 'end_date':
				$orderby = 'order_id';
				// $orderby = "(imeta.BookTo OR SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookTo, 'i:0;s:REGEXP ".'"[10-16]"'.":".'"'."', -1), '".'"'."', 1) )";
				
				break;

			case 'bookedby':
				$orderby = 'ometa.customer_name';
				break;
			
			case 'order_id':
			default:
				$orderby = 'order_id';
				break;
		}

		$query = apply_filters('ph_booking_query_for_get_bookings_list',$query,$filters);

		$order = !empty($_GET['order']) ? $_GET['order'] : 'DESC';
		$query .=" ORDER BY $orderby ".$order;
		$start_limit 	= ($current_page-1) * $per_page;
		if($sortby!='start_date' && $sortby!='end_date')
			$query .=" LIMIT $start_limit, $per_page";
		
		$results = $wpdb->get_results( $query );
		$bookings = array();
		$item_ids=array();
		foreach ($results as $key => $result) {
			$order_id = $result->order_id;
			$order = wc_get_order( $order_id );
			if(!($order instanceof WC_Order))
			{
				continue;
			}
			
			$order_item_id=$result->order_item_id;
			if (in_array($order_item_id,$item_ids))
			{
				continue;
			}

			// 143735 - hide dokan parent orders
			if(function_exists('dokan_get_prop'))
			{
				$parent_order = $order;
				$sub_orders = get_children([
					'post_parent' => dokan_get_prop( $parent_order, 'id' ),
					'post_type'   => 'shop_order',
				]);

				if(is_array($sub_orders) && count($sub_orders) > 0)
				{
					continue;
				}
			}
			
			$item_ids[]=$order_item_id;
			$IntervalDetails	= unserialize($result->IntervalDetails);
			$BookFrom 			= ph_maybe_unserialize($result->BookFrom);
			$BookTo 			= ph_maybe_unserialize($result->BookTo);
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

			// Filter out the bookings based on END FILTER
			// if( !empty($filters['ph_filter_end_from']) ){
			// 	if( $BookTo < $filters['ph_filter_end_from'] ){
			// 		continue;
			// 	}
			// }
			// if( !empty($filters['ph_filter_end_to']) ){
			// 	$filter_end_to_with_time = $filters['ph_filter_end_to']; 
			// 	$filter_end_to_with_time.= " 23:59"; 
			// 	if( $BookTo > $filter_end_to_with_time ){
			// 			continue;
			// 		}
			// }
			$customer_name = !empty($result->customer_name) ? $result->customer_name.' '.$result->customer_last_name : $result->customer_billing_email;

			$bookings[] = array(
				'ID' 			=> $result->order_item_id,
				'order_id' 		=> $result->order_id,
				'product_id' 	=> $result->product_id,
				'start' 		=> $BookFrom,
				'end' 			=> $BookTo,
				'bookedby' 		=> $customer_name,
				'booking_status'=> __(ph_map_booking_status_to_name(ph_maybe_unserialize($result->BookingStatus)), 'bookings-and-appointments-for-woocommerce'),
				'no_of_persons' => $result->no_of_persons
			);
		}
		$sort_order = !empty($_GET['order']) ? $_GET['order'] : 'DESC';
		switch ( $sortby ) {
			
			case 'start_date':
				$bookings=$this->get_sorted_booking($bookings,'start',$sort_order);
				$bookings=array_slice($bookings,$start_limit,$per_page);
				break;
			
			case 'end_date':
				$bookings=$this->get_sorted_booking($bookings,'end',$sort_order);
				$bookings=array_slice($bookings,$start_limit,$per_page);
				
				break;

			default:
				break;
		}
		return $bookings;
	}
	public function get_sorted_booking($bookings,$orderby,$order)
	{
		$final_bookings=array();
		foreach ($bookings as $key => $value) {
			if($orderby=='start')
			{
				if(empty($value['start']))
					$bookings[$key]['sort_date']=strtotime(date('Y-m-d H:i'));
				else
					$bookings[$key]['sort_date']=strtotime(date('Y-m-d H:i',strtotime($value['start'])));
			}
			else
				$bookings[$key]['sort_date']=strtotime(date('Y-m-d H:i',strtotime($value['end'])));
			$final_bookings[]=$bookings[$key];
		}
    	$keys = array_column($final_bookings, 'sort_date');
    	if($order=='asc')
			array_multisort($keys, SORT_ASC, $final_bookings);
		else
			array_multisort($keys, SORT_DESC, $final_bookings);
		return $final_bookings;
	}
	/**
	* Display filter html and pagination html
	*
	*/
	protected function display_tablenav( $which ) {

		$ph_booking_status 		= isset( $_GET['ph_booking_status'] ) ? $_GET['ph_booking_status'] : '';
		$ph_filter_product_ids 	= isset( $_GET['ph_filter_product_ids']) ? $_GET['ph_filter_product_ids'] : '';
		$ph_filter_from 		= isset( $_GET['ph_filter_from']) ? $_GET['ph_filter_from'] : '';
		$ph_filter_end_from 		= isset( $_GET['ph_filter_end_from']) ? $_GET['ph_filter_end_from'] : '';
		$ph_filter_to 			= isset( $_GET['ph_filter_to']) ? $_GET['ph_filter_to'] : '';
		$ph_filter_end_to 			= isset( $_GET['ph_filter_end_to']) ? $_GET['ph_filter_end_to'] : '';

		if ( ! empty( $filter_id ) ) {
			$_product = wc_get_product( $filter_id );
		}

		include( 'views/html-ph-booking-admin-reports-list-filters.php' );
	}
	

	/**
	* Content of each columns.
	*/
	public function column_default( $item, $column_name ) {
		//ticket 112195
		$product_id = $item['product_id'];
		$interval_period = get_post_meta($product_id, "_phive_book_interval_period", 1);
		switch ( $column_name ) {

			case 'order_id' :

				if ( ! empty( $item['order_id'] ) ) {
					$order = wc_get_order( $item['order_id'] );
				} else {
					$order = false;
				}
				
				if ( $order ) {
					echo '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $order->get_order_number() ) . '</strong></a>';
				}
			break;

			case 'start_date' :
				//ticket 112195
				if($interval_period == 'month'){
					$item['end'] = $item['start']."-01";
				}
				
				//If booking time is there.
				if( strlen($item['start']) > 10 ){
					echo ph_wp_date( get_option( 'date_format' )." ".get_option( 'time_format' ), strtotime( $item['start'] ) );
				}else{
					echo ph_wp_date( get_option( 'date_format' ), strtotime( $item['start'] ) );
				}
			break;

			case 'end_date' :
				if ( !empty( $item['end'] ) ) {
					//ticket 112195
					if($interval_period == 'month'){
						$month = date("m",strtotime($item['end']));
						$year = date("Y",strtotime($item['end']));
						
						$days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
						$item['end'] = $item['end']."-".$days;
					}
					//If booking time is there.
					if( strlen($item['end']) > 10 ){
						echo ph_wp_date( get_option( 'date_format' )." ".get_option( 'time_format' ), strtotime( $item['end'] ) );
					}else{
						echo ph_wp_date( get_option( 'date_format' ), strtotime( $item['end'] ) );
					}
				}
			break;

			case 'product' :
				$product = wc_get_product( $item['product_id'] );
				if ( ! $product ) {
					return;
				}

				$product_name = $product->get_formatted_name();
				echo wp_kses_post( $product_name );
			break;

			default :
				echo isset( $item[$column_name] ) ? esc_html( __($item[$column_name],'bookings-and-appointments-for-woocommerce') ) : '';

			break;
		}
	}

}
