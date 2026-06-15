<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( ! class_exists('Ph_Bookings_Send_Email_Notifications') ) {
	class Ph_Bookings_Send_Email_Notifications {

		/**
		 * @var $settings
		 */
		public $settings;

		/**
		 * @var $enabled
		 */
		public $enabled;

		/**
		 * @var $email_subject
		 */
		public $email_subject;

		/**
		 * @var $email_content
		 */
		public $email_content;

		/**
		 * @var $notification_time
		 */
		public $notification_time;

		/**
		 * @var $site_title;
		 */
		public $site_title;

		/**
		 * @var $bookings
		 */
		public $bookings;

		/**
		 * @var $email_instance
		 */
		public $email_instance;

		public function __construct() {
			$reminder_email_settings_id = 'ph_bookings_settings_notifications';
			add_action( 'ph_bookings_notification_cron', array( $this, 'ph_bookings_notification_cron_run') );
			add_filter( 'cron_schedules', array($this,'ph_bookings_notification_cron' ));
			$this->settings 		= get_option( $reminder_email_settings_id, array() );;
			$this->enabled			= ! empty($this->settings['reminder_email_enabled']) ? $this->settings['reminder_email_enabled'] : false;
			$this->email_subject	= ! empty($this->settings['reminder_email_subject']) ? $this->settings['reminder_email_subject'] : 'Bookings Reminders';
			$this->email_content	= ! empty($this->settings['reminder_email_content']) ? $this->settings['reminder_email_content'] : $this->get_default_email_contents();
			$this->notification_time			= ! empty($this->settings['reminder_email_notification_time']) ? $this->settings['reminder_email_notification_time'] : 60;
		}

		public function ph_bookings_notification_cron( $schedules ) {

			if (isset($this->settings['reminder_email_template']) && $this->settings['reminder_email_template'] == 'woocommerce' && class_exists('WC_Email')) {

				if( ! $this->email_instance instanceof Ph_WC_Email_Booking_reminder ) {

					$this->ph_set_reminder_email_instance();
				}
				$import_interval = $this->email_instance->get_option('cron_interval');
				
			} else {
				$import_interval = isset($this->settings ['reminder_email_cron_interval']) && !empty($this->settings ['reminder_email_cron_interval']) ? $this->settings ['reminder_email_cron_interval'] : 5;
			}
		    $schedules['booking_reminder_interval'] = array(
		            'interval'  => (int) $import_interval * 60 ,
		            'display'   => sprintf(__('Every %d minutes', 'bookings-and-appointments-for-woocommerce'), (int) $import_interval)
		    );
		    return $schedules;
		} 

		public function get_default_email_contents() {
			$email_content = "Hi [CUSTOMER_NAME],<br><br>I request you to check the bookings details for your appointment with [SITE_NAME].<br><br>[BOOKING_DETAILS]<br><br>We look forward to serving you.<br>Regards<br>Admin";
			return $email_content;
		}
		/**
		 * ph_bookings_notification_cron_run
		 */
		public function ph_bookings_notification_cron_run(){
			$reminder_email_settings 		= get_option( 'ph_bookings_settings_notifications', array() );;
			$reminder_email_enabled			= ! empty($reminder_email_settings['reminder_email_enabled']) ? $reminder_email_settings['reminder_email_enabled'] : false;	
			if($reminder_email_enabled)
			{
				if( isset($reminder_email_settings['reminder_email_template']) && $reminder_email_settings['reminder_email_template'] == 'woocommerce') {

					if( ! $this->email_instance instanceof Ph_WC_Email_Booking_reminder ) {

						$this->ph_set_reminder_email_instance();
					}
					
					$this->notification_time	= $this->email_instance->get_option('reminder_time');
				}

				$this->site_title = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );
				// Get Next Day Date according to site time
				$date_from = date_create();
				// date_timestamp_set( $date_from, current_time('timestamp') );
				global $wp_version;
				if ( version_compare( $wp_version, '5.3', '>=' ) ) 
				{
					$currentTime = current_datetime();
					$currentTime = $currentTime->format('U');
				}
				else
				{
					$currentTime = current_time('timestamp');
				}
				date_timestamp_set( $date_from, $currentTime );

				$date_to = date_create();
				// date_timestamp_set( $date_to, current_time('timestamp') );
				date_timestamp_set( $date_to, $currentTime );
				$date_to->modify("5 days");
				// error_log("date to before".print_r($date_to,1));
				if (!empty($this->notification_time)) 
				{
					$date_to->modify("$this->notification_time minutes");
					// error_log("date to after".print_r($date_to,1));
				}
				
				$filters = array(
					'ph_booking_status' => null,
					'ph_filter_from'	=> $date_from->format('Y-m-d'),
					'ph_filter_to'		=> $date_to->format('Y-m-d')
				);

				$this->bookings = $this->get_bookings( $filters );
				$this->bookings = $this->remove_previously_send_bookings( $this->bookings );
				$this->send_email( $this->bookings );
			}
		}

		/**
		 * Remove the Bookings which already send.
		 * @param array $bookings Bookings
		 * @param object $date_to_select
		 */
		public function remove_previously_send_bookings( $bookings ) {
			
			foreach( $bookings as $key => $booking ) {

				// Don't include Cancelled Bookings
				if(  !empty($booking['ReminderEmailStatus']) ) {
					$ReminderEmailStatus 			= unserialize($booking['ReminderEmailStatus']);
					if($ReminderEmailStatus)
					{		
						unset($bookings[$key]);
						continue;
					}
				}
			}
			return $bookings;
		}

		/**
		 * Send Email for the Bookings
		 * @param array $bookings Array of Bookings for which email need to be sent
		 */
		public function send_email( $bookings = array() ){
			$header = array(
				"Content-Type: text/html; charset=UTF-8"
			);
			$from_name		= get_option( 'woocommerce_email_from_name');
			$from_address	= get_option( 'woocommerce_email_from_address' );
			$header[]		= "From : ".wp_specialchars_decode( esc_html($from_name), ENT_QUOTES )." <$from_address>";
			// $subject		= $this->get_email_subject();
			foreach( $bookings as $booking ) {

				$order_id 	= $booking['order_id'];
				$product_id = $booking['product_id'];

				if (isset($this->settings['reminder_email_template']) && $this->settings['reminder_email_template'] == 'woocommerce') {

					if( ! $this->email_instance instanceof Ph_WC_Email_Booking_reminder ) {

						$this->ph_set_reminder_email_instance();
					}
					$email_status = $this->email_instance->trigger($order_id, $booking);
					
				} else {

					$order_language = PH_WC_Bookings_Storage_Handler::ph_get_meta_data($order_id, 'wpml_language');

					$subject = $this->get_email_subject();

					$subject = apply_filters('wpml_translate_single_string', $subject, 'bookings-and-appointments-for-woocommerce', 'reminder_email_subject_translation', $order_language);

					$email_status = wp_mail($booking['billing_email'], $subject, $this->get_email_content($booking, $order_language), $header);
				}

				if ($email_status) {
					$order_item_id = $booking['ID'];
					wc_add_order_item_meta($order_item_id, 'ReminderEmailStatus', array(true));
				}
			}
		}

		/**
		 * Get Email Subject.
		 * @return string Email Subject.
		 */
		private function get_email_subject() {
			$this->email_subject = __($this->email_subject, 'bookings-and-appointments-for-woocommerce');
			return $this->email_subject;
		}

		/**
		 * Get Email Content
		 * @param array $booking Booking
		 * @return string Email Content / Message.
		 */
		public function get_email_content( $booking, $order_language = null ) {
			$this->email_content	= str_replace( "\\","",$this->email_content);

			$this->email_content = __($this->email_content, 'bookings-and-appointments-for-woocommerce');
			$email_content = apply_filters('wpml_translate_single_string', $this->email_content, 'bookings-and-appointments-for-woocommerce', 'reminder_email_content_translation', $order_language);

			$email_content = str_replace( array( PHP_EOL, '[CUSTOMER_NAME]', '[SITE_NAME]'), array( "<br>", $booking['bookedby'], $this->site_title), $email_content );

			// $email_content="testing";
			$email_content =str_replace('[BOOKING_DETAILS]', $this->get_product_info_as_table($booking), $email_content);
			$email_content = apply_filters('ph_display_booking_code_reminder', $email_content, $booking);
			return $email_content;
		}

		public function get_bookings( $filters ) {
			return $this->ph_get_bookings($filters);
		}

		/**
		 * Get Bookings.
		 * @param array $filters Bookings Filters
		 */
		private function ph_get_bookings( $filters ){
			global $wpdb;

			if (PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled()) {

				$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id,ometa.customer_name, ometa.billing_email, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails,imeta.ReminderEmailStatus
				FROM {$wpdb->prefix}wc_orders
				INNER JOIN (
						SELECT
						order_id,
						email AS billing_email,
						first_name AS customer_name
						FROM {$wpdb->prefix}wc_order_addresses
						GROUP BY order_id
				) as ometa on ometa.order_id = {$wpdb->prefix}wc_orders.id
				INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}wc_orders.id
				INNER JOIN (
						SELECT
						order_item_id,
						MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
						MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
						MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
						MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
						MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
						MAX(CASE WHEN meta_key = 'ReminderEmailStatus' THEN meta_value ELSE '' END) AS ReminderEmailStatus
						FROM {$wpdb->prefix}woocommerce_order_itemmeta
						GROUP BY order_item_id
				) as imeta on  imeta.order_item_id = oitems.order_item_id
				INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
				INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
				WHERE {$wpdb->prefix}wc_orders.type IN ('shop_order', 'shop_order_refund')
				AND (
					{$wpdb->prefix}wc_orders.status = 'wc-pending'
					OR {$wpdb->prefix}wc_orders.status = 'wc-processing'
					OR {$wpdb->prefix}wc_orders.status = 'wc-on-hold'
					OR {$wpdb->prefix}wc_orders.status = 'wc-completed'
					OR {$wpdb->prefix}wc_orders.status = 'wc-partially-paid'
					OR {$wpdb->prefix}wc_orders.status = 'wc-partial-payment'
					OR {$wpdb->prefix}wc_orders.status = 'wc-ng-complete'
				)
				AND tt.taxonomy IN ('product_type')
				AND t.slug = 'phive_booking'";
			} else {

				$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id,ometa.customer_name, ometa.billing_email, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails,imeta.ReminderEmailStatus
				FROM {$wpdb->prefix}posts
				INNER JOIN (
						SELECT
						post_id,
						MAX(CASE WHEN meta_key = '_billing_email' THEN meta_value ELSE '' END) AS billing_email,
						MAX(CASE WHEN meta_key = '_billing_first_name' THEN meta_value ELSE '' END) AS customer_name
						FROM {$wpdb->prefix}postmeta
						GROUP BY post_id
				) as ometa on ometa.post_id = {$wpdb->prefix}posts.ID
				INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}posts.ID
				INNER JOIN (
						SELECT
						order_item_id,
						MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
						MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
						MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
						MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
						MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
						MAX(CASE WHEN meta_key = 'ReminderEmailStatus' THEN meta_value ELSE '' END) AS ReminderEmailStatus
						FROM {$wpdb->prefix}woocommerce_order_itemmeta
						GROUP BY order_item_id
				) as imeta on  imeta.order_item_id = oitems.order_item_id
				INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
				INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
				WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
				AND (
					{$wpdb->prefix}posts.post_status = 'wc-pending'
					OR {$wpdb->prefix}posts.post_status = 'wc-processing'
					OR {$wpdb->prefix}posts.post_status = 'wc-on-hold'
					OR {$wpdb->prefix}posts.post_status = 'wc-completed'
					OR {$wpdb->prefix}posts.post_status = 'wc-partially-paid'
					OR {$wpdb->prefix}posts.post_status = 'wc-partial-payment'
					OR {$wpdb->prefix}posts.post_status = 'wc-ng-complete'
				)
				AND tt.taxonomy IN ('product_type')
				AND t.slug = 'phive_booking'";
			}
			
			//126219 
			$query = apply_filters('ph_modify_reminder_email_base_query', $query);
			
			$sub_query = "
				IF( LENGTH( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[7-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) ) = 7,
					CONCAT( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:7:".'"'."', -1), '".'"'."', -2),'".'"'."',1),'-01' ),
				SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[10-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) )";
	
			if( !empty($filters['ph_booking_status']) ){
				$len = strlen($filters['ph_booking_status']);
				$query .= " AND (SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookingStatus, 'i:0;s:
				".$len.":".'"'."', -1), '".'"'."', 1) = '".$filters['ph_booking_status']."' OR imeta.BookingStatus = '".$filters['ph_booking_status']."' ) ";
			}
			if( !empty($filters['ph_filter_product_ids']) ){
				$query .= " AND imeta.ProductId = '".$filters['ph_filter_product_ids']."'";
			}
			if( !empty($filters['ph_filter_from']) ){
	
				$query .= " AND ( DATE(".$sub_query.")  >= '".$filters['ph_filter_from']."'";
				
				$query .= " OR DATE(imeta.BookFrom) >= '".$filters['ph_filter_from']."')";
				
			}
			if( !empty($filters['ph_filter_to']) ){
				$query .= " AND (DATE(".$sub_query.") <= '".$filters['ph_filter_to']."'";
				$query .= " OR DATE(imeta.BookFrom) <= '".$filters['ph_filter_to']."')";
				
			}
			
			
			$results = $wpdb->get_results( $query );
			$bookings = array();
			// $logger = wc_get_logger();
			// $context = array( 'source' => 'reminder email addon' );
			// $logger->debug( json_encode($results), $context );
			$date_from = date_create();
			// date_timestamp_set( $date_from, current_time('timestamp') );
			global $wp_version;
			if ( version_compare( $wp_version, '5.3', '>=' ) ) 
			{
				$currentTime = current_datetime();
				$currentTime = $currentTime->format('U');
				$userTimezone =new DateTimeZone(wp_timezone_string());
			}
			else
			{
				$currentTime = current_time('timestamp');
				$userTimezone =new DateTimeZone(get_option('timezone_string'));
			}

			// error_log("user timezone : ".print_r($userTimezone,1));
			$myDateTime = new DateTime();
			$myDateTime->setTimezone($userTimezone);
			// error_log("my date time : ".print_r($myDateTime,1));
			$myInterval=DateInterval::createFromDateString($this->notification_time . 'minutes');
			$myDateTime->add($myInterval);
			$result1 = $myDateTime->format('Y-m-d H:i');
			// error_log("result1 : ".print_r($result1,1));

			$myDateTime = new DateTime();
			$myDateTime->setTimezone($userTimezone);
			$myInterval=DateInterval::createFromDateString(($this->notification_time-5). 'minutes');
			$myDateTime->add($myInterval);
			$result2 = $myDateTime->format('Y-m-d H:i');
			// error_log("result2 : ".print_r($result2,1));
			
			date_timestamp_set( $date_from, $currentTime );

			$email_interval=$this->notification_time;
			$date1=date('Y-m-d H:i',strtotime("+$email_interval minutes",strtotime($date_from->format('Y-m-d H:i'))));
			$email_interval=$email_interval-5;
			$date2=date('Y-m-d H:i',strtotime("+$email_interval minutes",strtotime($date_from->format('Y-m-d H:i'))));
			$date1 = $result1;
			$date2 = $result2;
			foreach ($results as $key => $result) {

				// No need to send the reminder email to the orders which does not have billing email
				if(ph_maybe_unserialize($result->BookingStatus)=='canceled' || empty($result->billing_email))
				{
					continue;
				}

				$IntervalDetails	= maybe_unserialize($result->IntervalDetails);
				$BookFrom 			= maybe_unserialize($result->BookFrom);
				
				if(!is_array($BookFrom) && !$BookFrom )
				{
					$BookFrom=$result->BookFrom;
				}
				else
				{
					$BookFrom=$BookFrom[0];
				}

				//code to add end date start
				$BookTo 			= ph_maybe_unserialize($result->BookTo);
				if( !empty($BookTo) && !empty($IntervalDetails) ) {
					$BookTo                = ! empty($BookTo) ? $BookTo : $BookFrom;
					$interval             = $IntervalDetails['interval'];
					$interval_format    = $IntervalDetails['interval_format'];
					if(strtotime($BookTo)==strtotime($BookFrom))
					{
						$BookTo             = date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookTo) ) );
					}
					else if($interval_format!='day' && $interval_format!='month' )
					{
						$BookTo 	= str_replace('/', '-', $BookTo);
						$BookTo     = date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookTo) ) ); // adding interval to last block
					}
				}
				else if (empty($BookTo) && !empty($IntervalDetails) ) {
					
					$interval             = $IntervalDetails['interval'];
					$interval_format    = $IntervalDetails['interval_format'];
					
					if($interval_format!='day' && $interval_format!='month' )
					{
						$BookTo             = date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookFrom) ) );
						
					}
					else
					{
						$BookTo             = date( 'Y-m-d', strtotime( "+$interval $interval_format",strtotime($BookFrom) ) );    
						
					}
					
				}
				if(!is_array($BookTo))
				{
					$serialized_or_not = @unserialize($BookTo);
					if($serialized_or_not !== false)
					{
						$BookTo=$BookTo;
					}
					else
					{
						$BookTo= maybe_unserialize($BookTo);
					}
				}
				else
				{
					$BookTo=$BookTo[0];
				}
				//code to add end date end

				$booking_from=date('Y-m-d H:i',strtotime($BookFrom));
				
				// $logger->debug( json_encode($result->BookFrom), $context );
				// $logger->debug( json_encode($BookFrom), $context );
				// $logger->debug( json_encode($booking_from), $context );
				// $logger->debug( json_encode($date1), $context );
				// $logger->debug( json_encode($date2), $context );
				// $logger->debug( json_encode(strtotime($booking_from)), $context );
				// $logger->debug( json_encode(strtotime($date1)), $context );
				// $logger->debug( json_encode(strtotime($date2)), $context );
				// $logger->debug( json_encode(strtotime($booking_from) > strtotime($date1)), $context );
				// $logger->debug( json_encode(strtotime($booking_from) < strtotime($date2)), $context );
				if(strtotime($booking_from) > strtotime($date1) || strtotime($booking_from) < strtotime($date2)  )
				{
					$current_time = new DateTime();
					$current_time->setTimezone($userTimezone);
					$current_time = $current_time->format('Y-m-d H:i');
					if((strtotime($booking_from) < strtotime($date2)) && (strtotime($booking_from) < strtotime($current_time))) 
					{
						continue;
					}
					else if(strtotime($booking_from) > strtotime($date1))
					{	
						continue;
					}
				}
				$bookings[] = array(
					'ID' 			=> $result->order_item_id,
					'order_id' 		=> $result->order_id,
					'product_id' 	=> $result->product_id,
					'start' 		=> $BookFrom,
					'end' 			=> $BookTo,				//so that quantity can be calculated to display booking code in notification email
					'bookedby' 		=> $result->customer_name,
					'billing_email'	=> $result->billing_email,
					'booking_status'=> ph_maybe_unserialize($result->BookingStatus),
					'ReminderEmailStatus'=>$result->ReminderEmailStatus
				);
			}
			// $logger = wc_get_logger();
			// $context = array( 'source' => 'reminder email addon' );
			// $logger->debug( json_encode($bookings), $context );
			return $bookings;
		}

		/**
		 * Get bookings product info as table.
		 */
		private function get_product_info_as_table($booking) {
			$order_item_id=$booking['ID'];
			$order_id=$booking['order_id'];
			$content = null;
			$order = wc_get_order($order_id);
			if( $order instanceof WC_Order ) {
				$order_items = $order->get_items();
				$table_td_style = "style='border: 1px solid #dddddd; padding:10px;font-size:17px;'";
				$table_td_title_style = "style='border: 1px solid #dddddd; padding:10px;font-size:20px;'";
				if( ! empty($order_items) ) {
					$content .= "<table  style='border-collapse:collapse; width:100%;'>";
						$content .= "<tr>
										<td $table_td_title_style>".__( 'Product', 'bookings-and-appointments-for-woocommerce')."</th>
										<td $table_td_title_style>".__( 'Price', 'bookings-and-appointments-for-woocommerce')."</th>
									</tr>";
						foreach( $order_items as $order_item ) {
							$product 	= $order_item->get_product();
							$item_id 	= $order_item->get_id();
							if( empty($product) || $product->get_type() !='phive_booking' || $item_id!=$order_item_id){
								continue;
							}
							$content .= "<tr>
											<td $table_td_style>".$order_item->get_name().$this->get_order_item_meta_data($order_item)."</td>".
											"<td $table_td_style>".wp_kses_post( $order->get_formatted_line_subtotal( $order_item ))."</td>
										</tr>";
						}
					$content .= "</table>";
				}
			}
			return $content;
		}

		/**
		 * Get Meta data of Line Items that needs to be sent in email.
		 * @return string in the form of Unordered list
		 */
		private function get_order_item_meta_data( $order_item ) {
			$content = null;
			$meta_datas = $order_item->get_meta_data();
			$product = $order_item->get_product();

			$key_filter = array(
				__('Total Number of Participants', 'bookings-and-appointments-for-woocommerce'),
				__('Number of persons', 'bookings-and-appointments-for-woocommerce'),
				'Number of persons',
				'confirmed',
				'canceled',
				'FollowUpTime'
			);

			$key_filter=apply_filters('ph_bookings_order_meta_key_filters',$key_filter,$order_item);
			foreach( $meta_datas as $meta_data ) {
				$meta_data = $meta_data->get_data();
				if( ! empty($meta_data['value']) && ! is_array($meta_data['value']) ) {
					if( in_array($meta_data['key'], $key_filter) )	continue;
					$meta_data['key'] = apply_filters( 'woocommerce_attribute_label', $meta_data['key'], $meta_data['key'], $product);		
					$content .= "<li>".__($meta_data['key'], 'bookings-and-appointments-for-woocommerce').": ".__($meta_data['value'], 'bookings-and-appointments-for-woocommerce')."</li>";
				}
			}

			if( ! empty($content) ) {
				$content = "<ul style='padding:10px;font-size:12px;'>".$content."</ul>";
			}
			return $content;
		}

		/**
		 * Set the reminder email instance
		 */
		public function ph_set_reminder_email_instance()
		{
			if (!$this->email_instance instanceof Ph_WC_Email_Booking_reminder) {

				if (!class_exists('Ph_WC_Email_Booking_reminder')) {

					if ( function_exists( 'WC' ) ) {
				
						WC()->mailer(); // This triggers the mailer system and loads WC_Email internally
					}

					include_once plugin_dir_path(PH_BOOKINGS_PLUGIN_FILE) . '/includes/emails/class-ph-wc-email-booking-reminder.php';
				}

				$this->email_instance = new Ph_WC_Email_Booking_reminder();
			}
		}

	}
	new Ph_Bookings_Send_Email_Notifications();
}