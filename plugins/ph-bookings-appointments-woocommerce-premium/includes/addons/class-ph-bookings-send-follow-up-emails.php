<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( ! class_exists('Ph_Bookings_Send_Follow_Up_Emails') ) {
	class Ph_Bookings_Send_Follow_Up_Emails {

		/**
		 * @var $email_content
		 */
		public $email_content;

		/**
		 * @var $enabled
		 */
		public $enabled;

		/**
		 * @var $email_subject
		 */
		public $email_subject;

		/**
		 * @var $followup_time
		 */
		public $followup_time;

		/**
		 * @var $site_title
		 */
		public $site_title;

		/**
		 * @var $bookings
		 */
		public $bookings;

		/**
		 * @var $followup_email_template
		 */
		public $followup_email_template;

		/**
		 * @var $email_instance
		 */
		public $email_instance;

		/**
		 * @var $followup_email_cron_interval
		 */
		public $followup_email_cron_interval;

		/**
		 * @var $woocommerce_table
		 */
		public $woocommerce_table;

		public function __construct() {
						
			$email_content = "Hi [CUSTOMER_NAME],<br><br>";
			$email_content .="Thank you for booking with [SITE_NAME].<br>";
			$email_content .="Hope you enjoyed our services. <br>";
			$email_content .="We look forward to serving you again.<br><br>";
			$email_content .="With Regards,<br>";
			$email_content .="Admin";
			$settings 		= get_option( 'ph_booking_follow_up_email', array() );
			$this->enabled			= ! empty($settings['followup_email_enabled']) ? $settings['followup_email_enabled'] : false;
			$this->email_subject	= ! empty($settings['followup_email_subject']) ? $settings['followup_email_subject'] : 'Thanks for Booking with Us..!';
			$this->email_content	= ! empty($settings['followup_email_content']) ? $settings['followup_email_content'] : $email_content;
			$this->followup_time	= ! empty($settings['followup_email_followup_time']) ? $settings['followup_email_followup_time'] : 24;
			$this->followup_email_template = isset($settings['followup_email_template']) && !empty($settings['followup_email_template']) ? $settings['followup_email_template'] : 'custom';
			$this->followup_email_cron_interval  = isset($settings['followup_email_cron_interval']) && !empty($settings['followup_email_cron_interval']) ? $settings['followup_email_cron_interval'] : 5;
			
			
			// add_filter( 'cron_schedules', array($this,'ph_bookings_follow_up_email_cron' ));
			add_action( 'ph_bookings_follow_up_email_cron', array( $this, 'ph_bookings_follow_up_email_cron_func_run') );
			add_filter( 'cron_schedules', array($this,'ph_bookings_follow_up_email_cron' ));

			// add_action( 'ph_bookings_follow_up_email_cron', array($this,'ph_bookings_follow_up_email_cron_func' ));
			$this->site_title = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );

			// Dynamic followup email cron hook
			add_action( 'ph_future_cron_schedule_for_followup_email', array($this,'ph_schedule_future_cron_for_followup_email' ));
		}

		/**
		 * Dynamic Followup email cron manage
		 */
		public function ph_schedule_future_cron_for_followup_email(){

			wp_clear_scheduled_hook('ph_future_cron_schedule_for_followup_email');
			
			$this->ph_bookings_follow_up_email_cron_func_run();
		}

		public function ph_bookings_follow_up_email_cron( $schedules ) {

			if ( $this->followup_email_template == 'woocommerce') {

				if( ! $this->email_instance instanceof Ph_WC_Email_Booking_followup ) {

					$this->ph_set_followup_email_instance();
				}
				$import_interval = $this->email_instance->get_option('cron_interval');
				
			} else {
				$import_interval = $this->followup_email_cron_interval;
			}
		    $schedules['booking_follow_up_interval'] = array(
		            'interval'  => (int) $import_interval * 60 ,
		            'display'   => sprintf(__('Every %d minutes', 'bookings-and-appointments-for-woocommerce'), (int) $import_interval)
		    );
		    return $schedules;
		}
		function ph_bookings_follow_up_email_cron_func_run() {
			$followup_email_settings 		= get_option( 'ph_booking_follow_up_email', array() );
			$followup_email_enabled			= ! empty($followup_email_settings['followup_email_enabled']) ? $followup_email_settings['followup_email_enabled'] : false;
			if($followup_email_enabled)
			{

				if( $this->followup_email_template  == 'woocommerce') {

					$this->ph_set_followup_email_instance();
					$this->followup_time	= $this->email_instance->get_option('followup_time');
				}

				$time_offset = get_option('gmt_offset');
				$current_time=date('Y-m-d H:i');
				$current_time=date('Y-m-d H:i:00',strtotime($current_time)+$time_offset*60*60);
				$this->bookings=$this->ph_get_bookings($current_time);
				$this->bookings = $this->remove_previously_send_bookings( $this->bookings);
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
				if( !empty($booking['FollowUpStatus'])) {
					$FollowUpStatus 			= ph_maybe_unserialize($booking['FollowUpStatus']);
					if($FollowUpStatus)
					{		
						unset($bookings[$key]);
						// error_log('status is true');
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
		public function send_email( $bookings = array() )
		{
			// error_log('follow up send email');
			$header = array(
				"Content-Type: text/html; charset=UTF-8"
			);
			$from_name		= get_option('woocommerce_email_from_name');
			$from_address	= get_option('woocommerce_email_from_address');
			$header[]		= "From : " . wp_specialchars_decode(esc_html($from_name), ENT_QUOTES) . " <$from_address>";
			// $subject		= $this->get_email_subject();
			foreach ($bookings as $booking) {

				$order_id 	= $booking['order_id'];
				$product_id = $booking['product_id'];

				if ($this->followup_email_template == 'woocommerce') {

					if (!$this->email_instance instanceof Ph_WC_Email_Booking_followup) {

						$this->ph_set_followup_email_instance();
					}
					$email_status = $this->email_instance->trigger($order_id, $booking);
				} else {

					$order_language = PH_WC_Bookings_Storage_Handler::ph_get_meta_data($order_id, 'wpml_language');

					$subject = $this->get_email_subject();
					$subject = apply_filters('wpml_translate_single_string', $subject, 'bookings-and-appointments-for-woocommerce', 'followup_email_subject_translation', $order_language);

					$email_status = wp_mail($booking['billing_email'], $subject, $this->get_email_content($booking, $order_language), $header);
				}

				if ($email_status) {
					$order_item_id = $booking['ID'];
					wc_add_order_item_meta($order_item_id, 'FollowUpStatus', array(true));
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
			// return "subject";
		}

		/**
		 * Get Email Content
		 * @param array $booking Booking
		 * @return string Email Content / Message.
		 */
		public function get_email_content( $booking, $order_language = null ) {
			$this->email_content	= str_replace( "\\","",$this->email_content);

			$this->email_content = __($this->email_content, 'bookings-and-appointments-for-woocommerce');
			$email_content = apply_filters('wpml_translate_single_string', $this->email_content, 'bookings-and-appointments-for-woocommerce', 'followup_email_content_translation', $order_language);
			
			$email_content = str_replace( array( PHP_EOL, '[CUSTOMER_NAME]', '[SITE_NAME]'), array( "<br>", $booking['bookedby'], $this->site_title), $email_content );
			$email_content = apply_filters('ph_display_booking_code_follow_up', $email_content, $booking);
			return $email_content;
		}

		/**
		 * Get Bookings.
		 * @param array $filters Bookings Filters
		 */
		private function ph_get_bookings( $follow_up_time ){

			$this->woocommerce_table = PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled();

			$follow_up_times=array("'".date('Y-m-d H:i:s',strtotime('-1 minutes',strtotime($follow_up_time)))."'",
									"'".$follow_up_time."'",
									"'".date('Y-m-d H:i:s',strtotime('+1 minutes',strtotime($follow_up_time)))."'"
							);
			global $wpdb;

			if ($this->woocommerce_table) {

				$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id,ometa.customer_name, ometa.billing_email, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails,imeta.FollowUpStatus
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
						-- MAX(CASE WHEN meta_key = 'FollowUpTime' THEN meta_value ELSE '' END) AS FollowUpTime,
						MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
						MAX(CASE WHEN meta_key = 'FollowUpStatus' THEN meta_value ELSE '' END) AS FollowUpStatus
						FROM {$wpdb->prefix}woocommerce_order_itemmeta
						GROUP BY order_item_id
				) as imeta on  imeta.order_item_id = oitems.order_item_id
				INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
				INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
				WHERE {$wpdb->prefix}wc_orders.type IN ('shop_order', 'shop_order_refund')
				AND (
					{$wpdb->prefix}wc_orders.status = 'wc-completed'
				)
				AND tt.taxonomy IN ('product_type')
				AND t.slug = 'phive_booking'";

			} else {

				$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id,ometa.customer_name, ometa.billing_email, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails,imeta.FollowUpStatus
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
						-- MAX(CASE WHEN meta_key = 'FollowUpTime' THEN meta_value ELSE '' END) AS FollowUpTime,
						MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
						MAX(CASE WHEN meta_key = 'FollowUpStatus' THEN meta_value ELSE '' END) AS FollowUpStatus
						FROM {$wpdb->prefix}woocommerce_order_itemmeta
						GROUP BY order_item_id
				) as imeta on  imeta.order_item_id = oitems.order_item_id
				INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
				INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
				WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
				AND (
					{$wpdb->prefix}posts.post_status = 'wc-completed'
				)
				AND tt.taxonomy IN ('product_type')
				AND t.slug = 'phive_booking'";
				
			}
			
			$results = $wpdb->get_results( $query );


			$bookings = array();
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

			$ph_future_cron_schedule_for = [];

			$myDateTime = new DateTime();
			$myDateTime->setTimezone($userTimezone);
			$current_time = $myDateTime->format('Y-m-d H:i');

			foreach ($results as $key => $result) {

				$IntervalDetails	= unserialize($result->IntervalDetails);
				$BookFrom 			= ph_maybe_unserialize($result->BookFrom);
				$BookTo 			= ph_maybe_unserialize($result->BookTo);
				$interval           = isset($IntervalDetails['interval']) ? $IntervalDetails['interval'] : 0;
				$interval_format    = isset($IntervalDetails['interval_format']) ? $IntervalDetails['interval_format'] : 'day';

				if( !empty($BookTo) && ! empty($IntervalDetails) ) {
					$BookTo                = ! empty($BookTo) ? $BookTo : $BookFrom;
					
					if(strtotime($BookTo)==strtotime($BookFrom))
					{
						$BookTo             = date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookTo) ) );
					}
					else if($interval_format!='day' && $interval_format!='month' )
					{
						$BookTo=str_replace('/', '-', $BookTo);
						$BookTo     = date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookTo) ) ); // adding interval to last block
					}
				}
				else if (empty($BookTo) && !empty($IntervalDetails) ) {
					
					
					if($interval_format!='day' && $interval_format!='month' )
					{
						$BookTo             = date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookFrom) ) );
						
					}
					else
					{
						$BookTo             = date( 'Y-m-d', strtotime( "+$interval $interval_format",strtotime($BookFrom) ) );    
						
					}
					
				}
	
				if(!is_array($BookFrom))
				{
					$serialized_or_not = @unserialize($BookFrom);
					if($serialized_or_not !== false)
					{
						$BookFrom=$result->BookFrom;
					}
					else
					{
						$BookFrom = maybe_unserialize($BookFrom);
						// $BookFrom = $BookFrom[0];
					}
				}
				else
				{
					$BookFrom=$BookFrom[0];
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

				if($interval_format =='day' || $interval_format =='month' )
				{
					$booking_to=date('Y-m-d 23:59',strtotime($BookTo));
					$booking_from=date('Y-m-d 23:59',strtotime($BookFrom));	
				}
				else
				{
					$booking_from=date('Y-m-d H:i',strtotime($BookFrom));
					$booking_to=date('Y-m-d H:i',strtotime($BookTo));
				}


				$ph_followup_email_time = date( 'Y-m-d H:i', strtotime( "+$this->followup_time minutes",strtotime($booking_to) ) );

				if ( $ph_followup_email_time == $current_time && !empty($result->billing_email) ) {

					$bookings[] = array(
						'ID' 			=> $result->order_item_id,
						'order_id' 		=> $result->order_id,
						'product_id' 	=> $result->product_id,
						'start' 		=> $BookFrom,
						'end' 			=> $BookTo,
						'bookedby' 		=> $result->customer_name,
						'billing_email'	=> $result->billing_email,
						'booking_status'=> ph_maybe_unserialize($result->BookingStatus),
						'FollowUpStatus'=>$result->FollowUpStatus
					);
				} else {

					$next_scheduled = wp_next_scheduled('ph_bookings_follow_up_email_cron');

					if ($next_scheduled !== false) {
						
						$myDateTime->setTimestamp($next_scheduled);

						$next_scheduled_time = $myDateTime->format('Y-m-d H:i');

						if ( $ph_followup_email_time < $next_scheduled_time && $ph_followup_email_time > $current_time ) {

							// Get All scheduled time
							$ph_future_cron_schedule_for[] = $ph_followup_email_time;
						}
					}
				}
			}

			if ( !empty($ph_future_cron_schedule_for) ) {
				
				sort($ph_future_cron_schedule_for);

				$next_scheduled_timestamp = strtotime( current( $ph_future_cron_schedule_for ) );

				$desired_datetime = date('Y-m-d H:i', $next_scheduled_timestamp);

				$next_scheduled = wp_next_scheduled('ph_future_cron_schedule_for_followup_email');

				$next_scheduled_time = false;
				
				if ($next_scheduled !== false) {
					
					$myDateTime->setTimestamp($next_scheduled);

					$next_scheduled_time = $myDateTime->format('Y-m-d H:i');
				}

				$dateTime = new DateTime($current_time);

				$scheduled_time = new DateTime(current( $ph_future_cron_schedule_for ));

				$interval = $dateTime->diff($scheduled_time);

				$dateTime = new DateTime();
				$dateTime->add($interval);
				$timestamp = $dateTime->getTimestamp();

				if ( $next_scheduled_time == false || $desired_datetime < $next_scheduled_time ) {

					wp_clear_scheduled_hook('ph_future_cron_schedule_for_followup_email');

					// Scheduled a Dynamic cron
					wp_schedule_single_event($timestamp, 'ph_future_cron_schedule_for_followup_email');
				}
			}

			return $bookings;
		}

		/**
		 * Get the followup email instance
		 */
		public function ph_set_followup_email_instance()
		{
			if (!$this->email_instance instanceof Ph_WC_Email_Booking_followup) {

				if (!class_exists('Ph_WC_Email_Booking_followup')) {

					if ( function_exists( 'WC' ) ) {

						WC()->mailer();
					}
					
					include_once plugin_dir_path(PH_BOOKINGS_PLUGIN_FILE) . '/includes/emails/class-ph-wc-email-booking-followup.php';
				}
				$this->email_instance = new Ph_WC_Email_Booking_followup();
			}
		}

	}
	new Ph_Bookings_Send_Follow_Up_Emails();
}