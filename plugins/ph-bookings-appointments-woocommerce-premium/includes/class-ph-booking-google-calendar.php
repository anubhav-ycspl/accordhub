<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class phive_booking_google_calendar {

	/**
	 * @var $ph_bookable_products
	 */
	public $ph_bookable_products;

	/**
	 * @var $google_calendar_enable
	 */
	public $google_calendar_enable;
	
	/**
	 * @var $google_calendar_id
	 */
	public $google_calendar_id;

	/**
	 * @var $google_client_id
	 */
	public $google_client_id;

	/**
	 * @var $google_client_secret
	 */
	public $google_client_secret;

	/**
	 * @var $google_calendar_frondend
	 */
	public $google_calendar_frondend;

	/**
	 * @var $google_calendar_debug
	 */
	public $google_calendar_debug;

	/**
	 * @var $google_redirect_uri
	 */
	public $google_redirect_uri;

	/**
	 * @var $asset_settings
	 */
	public $asset_settings;

	/**
	 * @var $logger
	 */
	public $logger;

	/**
	 * @var $google_cal_event_filter
	 */
	public $google_cal_event_filter;

	/**
	* Init and hook in the integration.
	*/
	public function __construct() {
		global $woocommerce;
		
		$setting_id 	= 'ph_booking_settings_';
		$settings 		= get_option( $setting_id.'google_calendar', 1 );
		$this->ph_bookable_products = [];

		$this->google_calendar_enable 	= isset($settings['google_calendar_enable']) && 'yes' === $settings['google_calendar_enable'] ? true : false;
		$this->google_calendar_id 		= isset($settings['google_calendar_id']) ? $settings['google_calendar_id'] : '';
		$this->google_client_id 		= isset($settings['google_client_id']) ? $settings['google_client_id'] : '';
		$this->google_client_secret 	= isset($settings['google_client_secret']) ? $settings['google_client_secret'] : '';
		$this->google_calendar_frondend = isset($settings['google_calendar_frondend']) ? $settings['google_calendar_frondend'] : '';
		$this->google_calendar_debug 	= isset($settings['google_calendar_debug']) ? $settings['google_calendar_debug'] : '';
		$this->google_cal_event_filter 	= isset($settings['google_cal_event_filter']) ? $settings['google_cal_event_filter'] : [];

		$this->google_redirect_uri	= WC()->api_request_url( 'phive_booking_google_calendar' );
	
		$this->init_debug();

		add_action( 'woocommerce_api_phive_booking_google_calendar' , array( $this, 'phive_oauth_callback_redirect' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'sync_order') );
		// third party deposit compatiblity
		add_action('ph_deposit_thankyou_compatibility',array($this,'ph_deposit_thankyou_compatibility'));
		add_action('woocommerce_checkout_order_created',array($this, 'ph_deposit_thankyou_compatibility'));

		add_action( 'ph_booking_status_changed', array( $this, 'sync_order_item' ), 10, 4 );
		add_action( 'ph_booking_item_calender_resynced', array( $this, 'sync_order_item' ), 10, 3 );
		add_action( 'woocommerce_order_item_meta_end', array( $this, 'phive_google_calender_link_with_order_item'), 10, 4 );

		// Before Order item deleted 
		add_action('woocommerce_before_delete_order_item', array($this, 'ph_woocommerce_before_delete_order_item'));

		
		if ( is_admin() ) {
			add_action( 'ph_booking_google_calender_sync_for_admin_bookings', array($this, 'sync_order') );		// It will get triggered on front end also but on front end we will do it from thank you page
			add_action( 'admin_notices', array( $this, 'ph_admin_notices' ) );
		}
		
		if (isset($_GET['ph_clear_google_oauth'])) {
			$this->ph_clear_google_oauth();
		}

		if (isset($_POST['ph_bookings_manually_sync'])) {
			$this->ph_bookings_manually_sync();
		}

		if (isset($_POST['ph_bookings_start_stop_two_way_sync'])) {
			$this->ph_bookings_start_two_way_sync();
		}

		add_filter( 'cron_schedules', array($this,'ph_bookings_two_way_sync_cron' ));
		add_action( 'ph_bookings_two_way_sync_cron', array($this,'ph_bookings_two_way_sync_cron_func' ));

		//Validate the credentials
		if (isset($_POST['ph_booking_gcalendar_validate_credentials'])) {

			$this->ph_booking_gcalendar_validate_credentials();
		}

		if ( $this->google_calendar_enable ) {

			if ($this->is_google_calendars_synced() && (!isset($settings['google_calendars']) || empty($settings['google_calendars']))) {

				$this->ph_get_and_update_google_calendars_to_db();
			}

			add_action('wp_ajax_ph_get_google_calendars', array($this, 'ph_get_and_update_google_calendars_to_db'));
		}
	}

	/**
	 * Verify if Google Calendars are connected and synced.
	 *
	 * @return bool True if Google Calendars are synced, otherwise false.
	 */
	public function is_google_calendars_synced() {

		// Get the access token required for Google Calendar integration.
		$access_token = $this->phive_get_access_token();

		// If the access token is not available, return false to indicate syncing is not enabled.
		if (!$access_token) {
			return false;
		}

		// Return true.
		return true;
	}

	/**
	 * Fetch and Update google Calendars in the Database.
	 *
	 * @param string $ph_type
	 */
	public function ph_get_and_update_google_calendars_to_db($ph_type='') {

		$settings = get_option( 'ph_booking_settings_google_calendar', 1 );

		// Get the access token for Google Calendar API.
		$access_token 	= $this->phive_get_access_token();

		// If access token is unavailable and $ph_type is not set, return an error response.
		if (!$access_token && wp_doing_ajax()) {

			wp_send_json(
				array(
					'status'	=> 'failed',
					'message'	=> __('Not Synced, First sync with Google Calendar', 'bookings-and-appointments-for-woocommerce')
				)
			);

			wp_die();
		}

		// Google Calendar API endpoint to fetch the user's calendar list.
		$ph_url = PH_Bookings_Config::PH_BOOKING_GOOGLE_CAL_URI . '/users/me/calendarList';

		// API request parameters.
		$params = array(
			'url'		=> $ph_url,
			'method' => 'GET',
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $access_token
			)
		);

		$response = PH_Bookings_API_Invoker::ph_make_request($params);

		$response_body = json_decode($response['body'], true); // Decode the JSON response body

		$ph_calendars = [];

		if (!empty($response_body)) {

			// If the response body contains calendar items, process each calendar.
			foreach($response_body['items'] as $key => $cal) {

				if (!empty($cal['id'])) {
				
					$ph_calendars[] = array(
						'id' 	=> $cal['id'],
						'name' 	=> $cal['summary'],
					);
				}
			}

			// Update the settings with the fetched calendar list.
			$settings['google_calendars'] = $ph_calendars;

			update_option('ph_booking_settings_google_calendar', $settings);
		}

		if (!wp_doing_ajax()) {
			return;
		}

		// If no calendars were found, send a failure response.
		if (empty($ph_calendars)) {

			wp_send_json(
				array(
					'status'	=> 'failed',
					'responce'	=> $response_body
				)
			);

			wp_die();
		}

		// Send a success response with the retrieved calendar details.
		wp_send_json(
			array(
				'status'	=> 'success',
				'responce'	=> $ph_calendars
			)
		);

		wp_die();
	}
	
	public function ph_deposit_thankyou_compatibility($order) {
		$order_id = $order->get_id();
		$this->sync_order($order_id);
	}

	function ph_bookings_manually_sync() {
		$status=$this->ph_get_calendar_events();
		wp_redirect( admin_url( 'admin.php?page=bookings-settings&tab=google-calendar&two_way_sync_status='.$status ) );

	}
	function ph_bookings_two_way_sync_cron_func() {
	    // do something
	    $this->ph_get_calendar_events();
	}
	public function ph_bookings_two_way_sync_cron( $schedules ) {
		$gcalendar_two_way_sync_settings = get_option( 'ph_booking_settings_google_calendar_two_way_sync', 1 );
		$import_interval=isset($gcalendar_two_way_sync_settings['ph_booking_two_way_sync_interval']) && !empty($gcalendar_two_way_sync_settings['ph_booking_two_way_sync_interval']) ? $gcalendar_two_way_sync_settings['ph_booking_two_way_sync_interval'] : 60;
	    $schedules['booking_import_interval'] = array(
	            'interval'  => (int) $import_interval ,
	            'display'   => sprintf(__('Every %d seconds', 'bookings-and-appointments-for-woocommerce'), (int) $import_interval)
	    );
	    return $schedules;
	}
	public function ph_bookings_start_two_way_sync(  ){
		if($_POST['ph_bookings_start_stop_two_way_sync']==1)
		{
				// Schedule an action if it's not already scheduled
			if ( ! wp_next_scheduled( 'ph_bookings_two_way_sync_cron' ) ) {
			    wp_schedule_event( time(), 'booking_import_interval', 'ph_bookings_two_way_sync_cron' );
			}
		}
		else
		{
			if( wp_next_scheduled( 'ph_bookings_two_way_sync_cron' ) ){
			    wp_clear_scheduled_hook( 'ph_bookings_two_way_sync_cron' );
			}
		}
		wp_redirect( admin_url( 'admin.php?page=bookings-settings&tab=google-calendar&two_way_sync_status=success' ) );
	}

	/**
	 * Get the events from the google calendar and create order if product id or product name passed
	 */
	public function ph_get_calendar_events(  ){

		// Check if the function is already running to avoid conflict between manual sync and cron
		$gcalendar_two_way_sync_status = get_transient( 'ph_booking_google_calendar_two_way_sync_status' );
		
		if(!$gcalendar_two_way_sync_status)
		{
			set_transient('ph_booking_google_calendar_two_way_sync_status', 1, 90);
			$access_token 	= $this->phive_get_access_token();

			if (empty($access_token)) {

				$this->debug("\n\n**** Two way google calendar sync ****");
				$this->debug("Empty Access Token.");
				return 'failed';
			}

			$api_url		= PH_Bookings_Config::PH_BOOKING_GOOGLE_CAL_URI . '/calendars/' . $this->google_calendar_id . '/events';
			$current_time   = new DateTime();
			$current_time=$current_time->format('Y-m-d\TH:i:s');
			$params = array(
				'method' => 'GET',
				'body'		=> array('timeMin'=>$current_time.'Z'),
				'sslverify' => '',
				'timeout' => 60,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $access_token
				)
			);


			$response = wp_remote_post( $api_url, $params );
			$this->ph_recent_google_calendar_sync_response($response);
			
			$display_settings = get_option('ph_bookings_display_settigns');
			$text_customisation = isset($display_settings['text_customisation']) ? $display_settings['text_customisation'] : array();
			$booked_from_text	= isset($text_customisation['booked_from_text']) && !empty($text_customisation['booked_from_text']) ? $text_customisation['booked_from_text'] : __("Booked From", 'bookings-and-appointments-for-woocommerce');
			$booked_to_text		= isset($text_customisation['booked_to_text']) && !empty($text_customisation['booked_to_text']) ? $text_customisation['booked_to_text'] : __("Booked To", 'bookings-and-appointments-for-woocommerce');

			$this->debug("\n\n**** Two way google calendar sync ****");
			$this->debug("Api Url: ".print_r($api_url,1));
			$this->debug("Params: ".print_r($params,1));

			if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 && $response['response']['message'] == 'OK' ) {
				
				// process the google calendar events when the response os OK
				$response_data = json_decode( $response['body'], true );
				if (isset($response_data['items']) && !empty($response_data['items'])) {

					$found=0;
					$event_not_created	=	false;

					// Loop trough every events and check for the bookable product id or product name
					foreach ($response_data['items'] as $item_key => $item_details) {

						if(isset($item_details['summary']) && !strstr($item_details['summary'], 'Order:'))
						{

							$item_details['summary'] = str_replace('(Attention: Time is required for this event)','',$item_details['summary']);
							$item_details['summary'] = str_replace(__('(Attention: Time is required for this event)', 'bookings-and-appointments-for-woocommerce'),'',$item_details['summary']);
							$product=trim($item_details['summary']);

							if(is_numeric($product))
							{
								$product=$this->ph_booking_find_product($product,$from='product_id');
							}
							else{
								$product=$this->ph_booking_find_product($product,$from='product_name');
							}

							if(ph_is_bookable_product($product))
							{
								// If time not passed for the time range products no need to create order
								$product_id = $product->get_id();
								$interval_period = get_post_meta($product_id, '_phive_book_interval_period', 1);

								if (($interval_period == "hour" || $interval_period == "minute") && !isset($item_details['start']['dateTime'])) {

									$event_not_created	= $this->debug("Product Id/Name: " . print_r(trim($item_details['summary'])." Error: Unable to create order, Time is required",1));

									// Update the google calendar event
									$event_details = $item_details;
									$event_details['summary'] .= __(' (Attention: Time is required for this event)', 'bookings-and-appointments-for-woocommerce');
									$status = $this->update_google_calendar_event($event_details,$item_details['id']);
									continue;
								}
								$found++;
								$this->ph_bookings_create_new_order_from_google_calendar($product,$item_details);
							}
						}

						/**
						 * Handle bookings modification.
						 * The text "Modify Booking Status:" is deprecated, customer's should move to "Modify:"
						 */
						if(isset($item_details['summary']) && (stristr($item_details['summary'], 'Modify:') || stristr($item_details['summary'], 'Modify Booking Status:')))
						{
							$summary = explode(',',$item_details['summary']);

							$modify_status = '';
							$order_id = '';
							$order_item_id = '';

							foreach($summary as $key => $data)
							{
								if(strstr($data, 'Order:') || strstr($data, __('Order:','bookings-and-appointments-for-woocommerce')))
								{
									$order_id = explode(":", $data);
									$order_id = explode("#", $order_id[1]);
									$order_id = $order_id[1];
								}
								if(strstr($data, 'Order Item:') || strstr($data, __('Order Item:','bookings-and-appointments-for-woocommerce')))
								{
									$order_item_id = explode(":", $data);
									$order_item_id = explode("#", $order_item_id[1]);
									$order_item_id = $order_item_id[1];
								}	
								if(stristr($data, 'Modify Booking Status:') || stristr($data, 'Modify:'))
								{
									$data = trim($data);
									$modify_status = explode(":", $data);
									$modify_status = strtolower(trim($modify_status[1]));
								}	
							}

							if('cancel' == $modify_status && $order_item_id && $order_id)
							{
								$success = wc_update_order_item_meta( $order_item_id, 'canceled', 'yes' );
								$status_chage = wc_update_order_item_meta( $order_item_id, 'booking_status', array('canceled') );
								wc_update_order_item_meta( $order_item_id, __('Booking Status','bookings-and-appointments-for-woocommerce'), 'canceled' );
								// if( $success )
								// {
									$order 		= wc_get_order($order_id);
									// $order->update_status('cancelled');
									$buffer_before_id = wc_get_order_item_meta( $order_item_id, "buffer_before_id", 1 );
									$buffer_after_id=wc_get_order_item_meta( $order_item_id, "buffer_after_id", 1 );
									
									if(!empty($buffer_before_id)){

										update_post_meta( $buffer_before_id[0], 'ph_canceled', '1' );
									}
									if(!empty($buffer_after_id)){

										update_post_meta( $buffer_after_id[0], 'ph_canceled', '1' );
										
									}
									wc_update_order_item_meta( $order_item_id, '_line_subtotal', 0 );
									wc_update_order_item_meta( $order_item_id, '_line_total', 0 );
									wc_update_order_item_meta( $order_item_id, 'Cost', array(0) );
									$order->calculate_totals();

									// Executed only when Zoom plugin is installed and a zoom meeting exists.
									do_action( 'ph_zoom_update_meeting_status_as_deleted', $order_item_id );

									do_action( 'ph_booking_status_changed', 'cancelled', $order_item_id, $order_id, $order  );
									do_action( 'ph_booking_item_calender_resynced', 'cancelled', $order_item_id, $order_id  );
								// }
							}

							// Update booking details from google calendar
							if ('update' == $modify_status && $order_item_id && $order_id) {

								$from_date_time		= isset($item_details['start']['dateTime']) ? $item_details['start']['dateTime'] : '';
								$to_date_time		= isset($item_details['end']['dateTime']) ? $item_details['end']['dateTime'] : '';
								$from_date			= isset($item_details['start']['date']) ? $item_details['start']['date'] : '';
								$to_date			= isset($item_details['end']['date']) ? $item_details['end']['date'] : '';
								$order_item			= new WC_Order_Item_Product($order_item_id);
								$product_id			= $order_item->get_product_id();
								$interval_period	= get_post_meta($product_id, '_phive_book_interval_period', true);

								if (!empty($from_date_time) && ('hour' === $interval_period || 'minute' === $interval_period)) {

									$timezone = get_option('timezone_string');

									if (empty($timezone)) {
										$time_offset = get_option('gmt_offset');
										// Considered daylight saving off
										$timezone = timezone_name_from_abbr("", $time_offset * 60 * 60, 0);
										global $wp_version;
										if (version_compare($wp_version, '5.3', '>=')) {
											$timezone = wp_timezone_string();
										}
									}

									$datetime = new DateTime();
									$datetime->setTimezone(new DateTimeZone($timezone));

									$from_time = $datetime->setTimestamp(strtotime($from_date_time));
									$from_time = $from_time->format(get_option('date_format') . ' ' . get_option('time_format'));

									//to properly block booked times
									$from = $datetime->setTimestamp(strtotime($from_date_time));
									$from = $from->format('Y-m-d H:i');
									wc_update_order_item_meta($order_item_id, 'From', array($from));
									wc_update_order_item_meta($order_item_id, __($booked_from_text, 'bookings-and-appointments-for-woocommerce'), $from_time);

									if ($from_date_time != $to_date_time) {

										$end		= date('Y-m-d H:i:s', strtotime($to_date_time));
										$interval	= get_post_meta($product_id, "_phive_book_interval", 1);

										$end = date('Y-m-d H:i:s', strtotime("-$interval $interval_period", strtotime($end)));

										$end_time = $datetime->setTimestamp(strtotime($end));
										$end_time = $end_time->format(get_option('date_format') . ' ' . get_option('time_format'));

										//to properly block booked times
										$end = $datetime->setTimestamp(strtotime($end));
										$end = $end->format('Y-m-d H:i');
										wc_update_order_item_meta($order_item_id, 'To', array($end));

										$end_time = $datetime->setTimestamp(strtotime($to_date_time));
										$end_time = $end_time->format(get_option('date_format') . ' ' . get_option('time_format'));
										wc_update_order_item_meta($order_item_id, __($booked_to_text, 'bookings-and-appointments-for-woocommerce'), $end_time);
									}
								} else if (!empty($from_date_time)) {

									// If time entered for day calendar products ignore time
									$from = date('Y-m-d', strtotime($from_date_time));
									wc_update_order_item_meta($order_item_id, 'From', array($from));
									wc_update_order_item_meta($order_item_id, __($booked_from_text, 'bookings-and-appointments-for-woocommerce'), $from);

									if ($from_date_time != $to_date_time) {

										$end = date('Y-m-d', strtotime($to_date_time));
										wc_update_order_item_meta($order_item_id, 'To', array($end));
										wc_update_order_item_meta($order_item_id, __($booked_to_text, 'bookings-and-appointments-for-woocommerce'), $end);
									}
								} else if (!empty($from_date) && !empty($to_date)) {

									$from = date('Y-m-d', strtotime($from_date));
									wc_update_order_item_meta($order_item_id, 'From', array($from));
									wc_update_order_item_meta($order_item_id, __($booked_from_text, 'bookings-and-appointments-for-woocommerce'), $from);
									if ($from_date != $to_date) {
										$end = date('Y-m-d', strtotime('-1 day', strtotime($to_date)));
										wc_update_order_item_meta($order_item_id, 'To', array($end));
										wc_update_order_item_meta($order_item_id, __($booked_to_text, 'bookings-and-appointments-for-woocommerce'), $end);
									}
								}

								// TO sync back to Google calendar, which will remove the keyword in summary.
								do_action('ph_booking_item_calender_resynced', 're-sync-google-calender', $order_item_id, $order_id);
							}
						}
					}
					if(!$event_not_created || $found > 0){
						$this->debug("Event: ".print_r($found." bookings on ".date('Y-m-d H:i:s'),1));
					}
				} else {
					$this->debug("No Events Available in your Calendar ".print_r(date('Y-m-d H:i:s'),1));
				}
				delete_transient('ph_booking_google_calendar_two_way_sync_status');
				// wp_redirect( admin_url( 'admin.php?page=bookings-settings&tab=google-calendar&two_way_sync_status=success' ) );
				return 'success';
			} else {
				delete_transient('ph_booking_google_calendar_two_way_sync_status');
				$this->debug("Two way sync failed, response: ".print_r($response,1));
				// wp_redirect( admin_url( 'admin.php?page=bookings-settings&tab=google-calendar&two_way_sync_status=failed' ) );
				return 'failed';
			}
		}
	}

	/**
	 * Find the product from product_id or product name
	 * 
	 * @param string $product_id_or_name contains either product id or product name
	 * @param string $from tells what $product parameter contain
	 * @return object $ph_product object of the product or the default string passed
	 */
	public function ph_booking_find_product($product_id_or_name, $find_produuct_based_on)
	{
		$ph_product = $product_id_or_name;

		if ($find_produuct_based_on == 'product_id') {

			$ph_product = wc_get_product($product_id_or_name);
		} else {

			if(empty($this->ph_bookable_products)) {

				$args = array(
					'limit'	=>	-1,
					'type' => 'phive_booking',
					'orderby' => 'name',
					'order' => 'ASC'
				);
				$this->ph_bookable_products = wc_get_products($args);
			}

			// Loop through all the bookable product assign the corresponding product given by customer
			foreach ($this->ph_bookable_products as $product) {

				if ($product_id_or_name == $product->get_name()) {

					$ph_product = $product;
					break;
				}
			}
		}

		return $ph_product;
	}

	/**
	 * Create the booking order
	 * @param object $product
	 * @param array $order_details
	 */
	public function ph_bookings_create_new_order_from_google_calendar($product,$order_details)
	{		
		$product_id=$product->get_id();
		$order = new WC_Order();
		$order->set_total( 0  );
		$order_id = $order->save();
		
		$asset_found = 0;
		$assets_enabled 				= get_post_meta( $product_id, "_phive_booking_assets_enable", 1 );
		$assets_auto_assign 			= get_post_meta( $product_id, "_phive_booking_assets_auto_assign", 1 );
		$assets_pricing_rules			= get_post_meta( $product_id, "_phive_booking_assets_pricing_rules", 1 );
		$asset_settings 				= get_option( 'ph_booking_settings_assets', 1 );
		$assets_rules 					= (isset($asset_settings['_phive_booking_assets']) && !empty($asset_settings['_phive_booking_assets'])) ? $asset_settings['_phive_booking_assets'] : array();
		$person_enable					= get_post_meta($product_id, "_phive_booking_person_enable", 1);
		
		// BUFFER
		$buffer_before 				= get_post_meta( $product_id, "_phive_buffer_before", 1 );
		$buffer_after 				= get_post_meta( $product_id, "_phive_buffer_after", 1 );
		$buffer_period 				= get_post_meta( $product_id, "_phive_buffer_period", 1 );
		$enable_buffer				= get_post_meta( $product_id, '_phive_enable_buffer', 1);
		$book_interval 				= get_post_meta( $product_id, "_phive_book_interval", 1 );
		$interval 			 		= get_post_meta( $product_id, "_phive_book_interval", 1 );
		$interval_period			= get_post_meta( $product_id, '_phive_book_interval_period', 1 );

		if ( $order_id ) 
		{
			$order->update_status( 'pending' );
			$item_id  = wc_add_order_item( $order_id, array(
				'order_item_name' => $product->get_title(),
				'order_item_type' => 'line_item',
			) );
				
			// 187196- google calendar bookings not considering for number of items in all bookings page
			$address  = array(
				'first_name'=>'',
				'last_name'=>'',
				'company'=>'',
				'address_1'=>'',
				'address_2'=>'',
				'city'=>'',
				'state'=>'',
				'postcode'=>'',
				'country'=>'',
				'phone'=>''
			);
			$order->set_address( $address, "billing" );
			$order->set_address( $address, 'shipping' );

			// Add Booking Interval and Booking Interval For or Period
			$product_interval_details = array(
				'interval'			=>	$interval,
				'interval_format'	=>	$interval_period
			);
			wc_add_order_item_meta( $item_id, '_phive_booking_product_interval_details', $product_interval_details );


			if (!empty($order_details['description'])) {

				$descriptions = explode("\n", $order_details['description']);

				if (!empty($descriptions[0])) {

					$persons_pricing_rules		= get_post_meta($product->get_id(), "_phive_booking_persons_pricing_rules", 1);
					$number_of_persons = 0;
					$participant_booking_data = array();

					foreach ($descriptions as $count => $description_line) {

						$description = explode(":", $description_line);

						if (!isset($description[1])){
							continue;
						}

						$description_name	= trim($description[0]);
						$description_value	= trim($description[1]);

						if (strtolower($description_name) == 'asset' && $assets_enabled == 'yes' && $asset_found == 0) {

							$asset_name = $description_value;

							// Looping through the rule and assign the corresponding rule value given by customer
							foreach ($assets_pricing_rules as $key => $rule) {

								if (empty($rule['ph_booking_asset_id'])){
									continue;
								}

								if ($assets_rules[$rule['ph_booking_asset_id']]['ph_booking_asset_name'] == $asset_name) {

									$asset_id = $rule['ph_booking_asset_id'];
									$ph_cache_obj = new phive_booking_cache_manager();
									$ph_cache_obj->ph_unset_cache($asset_id);
									$asset_found = 1;
									$asset_label = get_post_meta($product_id, "_phive_booking_assets_label", 1);

									if (empty($asset_label)) {

										$asset_label = __('Asset', 'bookings-and-appointments-for-woocommerce');
									}
									wc_add_order_item_meta($item_id, 'Assets', array($asset_id));
									wc_add_order_item_meta($item_id, $asset_label, $asset_name);
									break;
								}
							}
						} else if (strtolower($description_name) == 'customer name') {

							$customer_name = $description_value;
							wc_add_order_item_meta($item_id, 'Customer Name', $customer_name);
						} else if (strtolower($description_name) == 'location') {

							$location = $description_value;
							wc_add_order_item_meta($item_id, 'Location', $location);
						} else if($person_enable == 'yes') {

							if (!is_numeric($description_value)) {
								continue;
							}

							// Looping through the rule and assign the corresponding rule value given by customer
							foreach ($persons_pricing_rules as $key => $rule) {

								if (empty($rule)) {
									continue;
								}
								if ($rule['ph_booking_persons_rule_type'] == $description_name) {
									$participant_booking_data[] = array(
										'participant_label' => $description_name,
										'participant_count' => $description_value
									);
									$number_of_persons += $description_value;
									wc_add_order_item_meta($item_id, $rule['ph_booking_persons_rule_type'], $description_value);
								}
							}
						}
					}
					if (!empty($number_of_persons)) {

						wc_add_order_item_meta($item_id, 'Number of persons', $number_of_persons);

						// Need to show as Total number of participants rather than Number of persons
						wc_add_order_item_meta($item_id, __('Total Number of Participants', 'bookings-and-appointments-for-woocommerce'), $number_of_persons);
					}
					// error_log('participant_booking_data : '.print_r($participant_booking_data,1));
					if (count($participant_booking_data) > 0) {
						wc_add_order_item_meta($item_id, 'ph_bookings_participant_booking_data', $participant_booking_data);
					}
				}
			}
			// 175970
			$display_settings = get_option('ph_bookings_display_settigns');
			$text_customisation = isset($display_settings['text_customisation']) ? $display_settings['text_customisation'] : array();
			$booked_from_text =	isset($text_customisation['booked_from_text']) && !empty($text_customisation['booked_from_text']) ? $text_customisation['booked_from_text'] : __("Booked From", 'bookings-and-appointments-for-woocommerce');
			$booked_to_text =	isset($text_customisation['booked_to_text']) && !empty($text_customisation['booked_to_text']) ? $text_customisation['booked_to_text'] : __("Booked To", 'bookings-and-appointments-for-woocommerce');

			if(isset($order_details['start']['dateTime']) && ($interval_period == 'hour' || $interval_period == 'minute'))
			{
				$timezone 			= get_option('timezone_string');
				if( empty($timezone) ) {
					$time_offset = get_option('gmt_offset');
					// Considered daylight saving off
					$timezone = timezone_name_from_abbr( "", $time_offset*60*60, 0 );
					global $wp_version;
					if ( version_compare( $wp_version, '5.3', '>=' ) ) 
					{
						$timezone = wp_timezone_string();
					}
				}
				$datetime = new DateTime();
				$datetime->setTimezone(new DateTimeZone($timezone));

				// $from=date('Y-m-d H:i:s',strtotime($order_details['start']['dateTime']));
				$from_time=$datetime->setTimestamp(strtotime($order_details['start']['dateTime']));
				$from_time=$from_time->format(get_option( 'date_format' ).' '.get_option( 'time_format' ));

				//to properly block booked times
				$from = $datetime->setTimestamp(strtotime($order_details['start']['dateTime']));
				$from = $from->format('Y-m-d H:i');			
				wc_add_order_item_meta( $item_id,'From',array($from));

				// wc_add_order_item_meta( $item_id,'From',array($from_time));
				wc_add_order_item_meta( $item_id,__($booked_from_text,'bookings-and-appointments-for-woocommerce'), $from_time);
				if($order_details['start']['dateTime']!=$order_details['end']['dateTime'])
				{

					$end=date('Y-m-d H:i:s',strtotime($order_details['end']['dateTime']));
					$interval 			 		= get_post_meta( $product_id, "_phive_book_interval", 1 );
					$interval_period			= get_post_meta( $product_id, '_phive_book_interval_period', 1 );
					if( ($interval_period == 'minute') || ($interval_period == 'hour')){
						$end=date('Y-m-d H:i:s',strtotime("-$interval $interval_period",strtotime($end)));
					}
					$end_time=$datetime->setTimestamp(strtotime($end));
					$end_time=$end_time->format(get_option( 'date_format' ).' '.get_option( 'time_format' ));

					//to properly block booked times
					$end=$datetime->setTimestamp(strtotime($end));
					$end=$end->format('Y-m-d H:i');	
					wc_add_order_item_meta( $item_id,'To',array($end));

					// wc_add_order_item_meta( $item_id,'To',array($end_time));
					$end_time=$datetime->setTimestamp(strtotime($order_details['end']['dateTime']));
					$end_time=$end_time->format(get_option( 'date_format' ).' '.get_option( 'time_format' ));
					wc_add_order_item_meta( $item_id,__($booked_to_text,'bookings-and-appointments-for-woocommerce'), $end_time);
				}				
			} else if (isset($order_details['start']['dateTime'])) {

				// If time entered for day calendar products ignore time
				$from = date('Y-m-d', strtotime($order_details['start']['dateTime']));
				wc_add_order_item_meta($item_id, 'From', array($from));
				wc_add_order_item_meta($item_id, __($booked_from_text, 'bookings-and-appointments-for-woocommerce'), $from);

				if ($order_details['start']['dateTime'] != $order_details['end']['dateTime']) {

					$end = date('Y-m-d', strtotime($order_details['end']['dateTime']));
					wc_add_order_item_meta($item_id, 'To', array($end));
					wc_add_order_item_meta($item_id, __($booked_to_text, 'bookings-and-appointments-for-woocommerce'), $end);
				}
			}
			else if(isset($order_details['start']['date']) && isset($order_details['end']['date']))
			{
				$from=date('Y-m-d',strtotime($order_details['start']['date']));
				wc_add_order_item_meta( $item_id,'From',array($from));
				wc_add_order_item_meta( $item_id,__($booked_from_text,'bookings-and-appointments-for-woocommerce'), $from);
				if($order_details['start']['date']!=$order_details['end']['date'])
				{
					$end=date('Y-m-d',strtotime('-1 day',strtotime($order_details['end']['date'])));
					wc_add_order_item_meta( $item_id,'To',array($end));
					wc_add_order_item_meta( $item_id,__($booked_to_text,'bookings-and-appointments-for-woocommerce'), $end);
				}
			}
			
			$persons_as_booking 		= get_post_meta( $product_id, "_phive_booking_persons_as_booking", 1 );
			if($persons_as_booking == 'yes'){
					wc_add_order_item_meta( $item_id,'person_as_booking',array('yes') );
			}
			
			if( $assets_enabled=='yes' && $asset_found == 0 )
			{
				$assets_choosen = '';
				if( $assets_auto_assign == 'yes' )
				{
					$context = array( 'source' => 'add-order-from-google-calendar-with-asset-name');
					
					if($from != '' && $end != '')
					{
						$assets_choosen		= $this->get_most_matching_asset_for_slots( $from, $end, $product_id );
					}
					else if($from_time != '' && $end_time != '')
					{
						$assets_choosen		= $this->get_most_matching_asset_for_slots( $from_time, $end_time, $product_id );	
					} 
					$asset_found = 1;
				}
				if($assets_choosen != '')
				{
					$asset_id = $assets_choosen;

					$ph_cache_obj = new phive_booking_cache_manager();
					$ph_cache_obj->ph_unset_cache($asset_id);
					
					foreach ($assets_pricing_rules as $key => $rule) 
					{
						if( empty($rule['ph_booking_asset_id']) )
							continue;
						if($rule['ph_booking_asset_id'] == $asset_id)
						{
							$asset_name = $assets_rules[$rule['ph_booking_asset_id'] ]['ph_booking_asset_name'];
							// $logger->debug( 'auto_asset_id', $context );
							// $logger->debug( $asset_id, $context );
							
							$asset_found = 1;
							
							$asset_label = get_post_meta( $product_id, "_phive_booking_assets_label", 1 );

							if (!empty($asset_label) && is_array($asset_label)) 
							{
								$asset_label = empty($asset_label[0]) ? __('Asset', 'bookings-and-appointments-for-woocommerce') : $asset_label[0];
							}
							else
							{
								$asset_label = __('Asset', 'bookings-and-appointments-for-woocommerce');
							}
							wc_add_order_item_meta($item_id, 'Assets', array($asset_id));
							wc_add_order_item_meta( $item_id, $asset_label, $asset_name);
							break;
						}

					} 
				}
				
			}

			// Adding BUFFER for GC Bookings
			if($enable_buffer=='yes' && (isset($from) && !empty($from)))
			{	
				if( (!isset($end)) || ( isset($end) && empty($end)) )
				{
					$end = $from;
				}
				// error_log('from : '.$from);
				// error_log('end : '.$end);
				$asset_id = isset($asset_id) ? $asset_id : '';

				$buffer_before_from 		= $this->phive_buffer_before_time($from,$buffer_period,$book_interval,$buffer_before,$buffer_after);
	
				$buffer_after_to 			= $this->phive_buffer_after_time($from, $end, $buffer_period,$book_interval,$buffer_before,$buffer_after);
				switch($interval_period)
				{
					case 'day':
							$buffer_after_from 	= date ( "Y-m-d", strtotime( "+1 day", strtotime($end) ) );	
							$buffer_before_to 		= date ( "Y-m-d", strtotime( "-1 day", strtotime($from) ) );
							break;
					case 'hour':
							$buffer_after_from 	= date ( "Y-m-d H:i", strtotime( "+$interval $interval_period", strtotime($end) ) );
							$buffer_before_to 		= date ( "Y-m-d H:i", strtotime( "-$interval $interval_period", strtotime($from) ) );
							break;
					case 'minute':
							$buffer_after_from 	= date ( "Y-m-d H:i", strtotime( "+$interval $interval_period", strtotime($end) ) );
							$buffer_before_to 		= date ( "Y-m-d H:i", strtotime( "-$interval $interval_period", strtotime($from) ) );
							break;
				}
					
				
				if($buffer_before_from == ''){
					$buffer_before_to ='';
				}
				if($buffer_after_to == ''){
					$buffer_after_from = '';
				}	
				if($persons_as_booking == 'yes'){
					$buffer_before_id 					= $this->phive_save_booking_buffer_info($product_id,$buffer_before_from,$buffer_before_to,array('yes'),$number_of_persons,'yes','buffer-before',$asset_id);
					$buffer_after_id 					= $this->phive_save_booking_buffer_info($product_id,$buffer_after_from,$buffer_after_to,array('yes'),$number_of_persons,'yes','buffer-after',$asset_id);
				}
				else{
					$buffer_before_id 					= $this->phive_save_booking_buffer_info($product_id,$buffer_before_from,$buffer_before_to,'','','yes','buffer-before',$asset_id);
					$buffer_after_id 					= $this->phive_save_booking_buffer_info($product_id,$buffer_after_from,$buffer_after_to,'','','yes','buffer-after',$asset_id);
				}
				
				$buffer_before_ids = array("$buffer_before_id");
				$buffer_after_ids = array("$buffer_after_id");
				
				wc_add_order_item_meta( $item_id, 'buffer_before_id',$buffer_before_ids );
				wc_add_order_item_meta( $item_id, 'buffer_after_id',$buffer_after_ids );
	
			}

			// Add line item meta
			wc_add_order_item_meta( $item_id, '_line_total', 0 );
			wc_add_order_item_meta( $item_id, '_qty', 1 );
			wc_add_order_item_meta( $item_id, '_tax_class', $product->get_tax_class() );
			wc_add_order_item_meta( $item_id, '_product_id', $product->get_id() );
			wc_add_order_item_meta( $item_id, 'booking_status',array('un-paid') );
			wc_add_order_item_meta( $item_id,__('Booking Status','bookings-and-appointments-for-woocommerce'), __('Unpaid','bookings-and-appointments-for-woocommerce') );

			// Update the booking event for the event which passed product id or name
			$event_id[$item_id]	=	$order_details['id'];
			PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data($order_id, 'phive_google_calendar_event_ids', $event_id);
			$this->sync_order($order_id);

			// 96421
			do_action('ph_bookings_order_created_from_google_calendar', $item_id, $order_id, $product_id);
		}
		$order = wc_get_order( $order_id );
		return $order;
	}
	
	private function get_most_matching_asset_for_slots($from='', $to='', $product_id='')
	{	
		$interval_period 		= get_post_meta( $product_id, "_phive_book_interval_period", 1 );
		$interval 				= get_post_meta( $product_id, "_phive_book_interval", 1 );
		
		$interval_string 		= "$interval $interval_period";
		$asset_fount = '';
		
		$assets_pricing_rules	= get_post_meta( $product_id, "_phive_booking_assets_pricing_rules", 1 );
		$asset_settings 		= get_option( 'ph_booking_settings_assets', 1 );
		$assets_rules 			= (isset($asset_settings['_phive_booking_assets']) && !empty($asset_settings['_phive_booking_assets'])) ? $asset_settings['_phive_booking_assets'] : array();
		// Loop through booked slots, find asset which available for all slot.
		foreach ($assets_pricing_rules as $key => $rule) 
		{
			if( empty($rule['ph_booking_asset_id']) )
				continue;
			$current_time 		= strtotime($from);
			$book_to 			= empty($to) ? $current_time : strtotime($to);

			$loop_breaker = 300;
			while ( !empty($current_time) && $current_time <= $book_to && $loop_breaker > 0 ) {
				$asset_availability = $this->get_asset_availability( $rule['ph_booking_asset_id'], $current_time, $interval_period, $interval );
				if( $asset_availability == 0 ){
					$asset_fount  = false;
					continue 2;
				}
				$asset_fount  = $rule['ph_booking_asset_id'];
				$current_time = strtotime( "+$interval_string", $current_time );;
				$loop_breaker--;
			}
			if( !empty($asset_fount) ){
				return $asset_fount;
			}
		}
		return false;
	}

	private function get_asset_availability( $asset_id='', $date='', $interval_period='', $interval='' )
	{	
		$asset_manager = new phive_booking_assets($asset_id);

		switch( $interval_period ){
			case 'day':
				$interval_string = '+1 day';
				$format = "Y-m-d";
				break;
			
			case 'hour':
			case 'minute':
				$interval_string = "+".$interval." ".$interval_period;
				$format = "Y-m-d H:i";
				break;

			case 'month':
				$interval_string = "+1 month";
				$format = "Y-m-d";
				break;
		}
		$from 	= date ( $format, $date );
		$to 	= date ( $format, strtotime( $interval_string, $date ) );
		
		$asset_availability = $asset_manager->get_availability( $from, $to );

		return $asset_availability;
	}
	
	private function phive_buffer_before_time($from,$buffer_period,$book_interval,$buffer_before,$buffer_after='0'){
		if($buffer_before=='0'){
			return ;
		}
		else{		
			switch($buffer_period){
				case 'day':
					$buffer_before_time=date('Y-m-d', (strtotime($from) - ($buffer_before*3600*24)));
					break;
				case 'hour':
					$buffer_before_time=date('Y-m-d H:i', (strtotime($from) -( $buffer_before*3600)));
					break;
				case 'minute':
					$buffer_after=isset($buffer_after)?$buffer_after:'00';
					$buffer_before_time=date('Y-m-d H:i', (strtotime($from) -( $buffer_before*60)));
					break;
			}
			return $buffer_before_time;
		}

	}

	private function phive_buffer_after_time($from, $to='',$buffer_period='', $book_interval='', $buffer_before='0', $buffer_after=''){
		$to=!empty($to)?$to:$from;
		if($buffer_after=='0'){
			return ;
		}
		else{
		switch($buffer_period){
				case 'day':
					$buffer_after_time=date('Y-m-d', (strtotime($to) + ($buffer_after*3600*24)));
					break;
				case 'hour':
					$buffer_after_time=date('Y-m-d H:i', (strtotime($to) +($buffer_after*3600 )));
					break;
				case 'minute':
					$buffer_before=isset($buffer_before)?$buffer_before:'00';
					$buffer_after_time=date('Y-m-d H:i', (strtotime($to) +($buffer_after*60 )));
					break;
			}
			return $buffer_after_time;
		}
	}

	private function phive_save_booking_buffer_info($product_id,$buffer_before_time,$buffer_after_time,$person_as_booking='',$number_of_booking='',$is_buffer='',$buffer_type='',$asset_id=''){
		$new_post = array(
			'ID' => '',
			'post_type' => 'booking_buffer_freez', // Custom Post Type Slug
			'post_status' => 'open',
			'post_title' => 'Booking buffer freezer-'.uniqid(),//171763 Issue: When activating our Bookings plugin, its taking a quite long time while checkout.
			'ping_status' => 'closed',
		);

		$buffer_id = wp_insert_post($new_post);
		if( !$buffer_id ){
			return false;
		}
		if($is_buffer == 'yes' && $buffer_type == 'buffer-before'){
			$meta_values = array(
			'_product_id' 			=> $product_id,
			'Buffer_before_From'	=> $buffer_before_time,
			'Buffer_before_To'		=> $buffer_after_time,
			'_booking_customer_id'	=> 0,
			'Number of persons' 	=> $number_of_booking,
			'person_as_booking' 	=> $person_as_booking,
			'ph_canceled' 	=> '0',
			
		);
		}elseif($is_buffer == 'yes' && $buffer_type == 'buffer-after'){
			$meta_values = array(
			'_product_id' 			=> $product_id,
			'Buffer_after_From'		=> $buffer_before_time,
			'Buffer_after_To'		=> $buffer_after_time,
			'_booking_customer_id'	=> 0,
			'Number of persons' 	=> $number_of_booking,
			'person_as_booking' 	=> $person_as_booking,
			'ph_canceled' 	=> '0',
			
		);
		}
		if($asset_id)
		{
			$meta_values['buffer_asset_id']	= $asset_id;	
		}
		foreach ( $meta_values as $meta_key => $value ) {
			update_post_meta( $buffer_id, $meta_key, $value );
		}
		
		return $buffer_id;
	}

	public function phive_google_calender_link_with_order_item( $item_id, $item, $order ){
		if( $this->google_calendar_frondend != 'yes' ){
			return;
		}
		
		global $wp;
		//ticket 107893
		$phive_display_time_from 	= ph_maybe_unserialize( $item->get_meta('From') );
		$phive_display_time_to		= ph_maybe_unserialize( $item->get_meta('To') );
		$from 						= empty($phive_display_time_from)?ph_maybe_unserialize( $item->get_meta('From')):$phive_display_time_from;	
		$to 						= empty($phive_display_time_to)?ph_maybe_unserialize( $item->get_meta('To') ):$phive_display_time_to;	

		// 53947
		$value_date_format = get_option( 'date_format' );
		if($value_date_format == 'd/m/Y')
		{
			$from = str_replace('/','-',$from);
			if (!empty($to)) 
			{
				$to = str_replace('/','-',$to);
			}
		}

		$product					= wc_get_product( $item->get_product_id() );
		$canceled 					=  $item->get_meta('canceled');		
		if ($product) 
		{
			if( $product->get_type() != 'phive_booking' || $canceled == 'yes'){
				return;
			}
			// Interval Details
			$interval_details	= $item->get_meta('_phive_booking_product_interval_details',true);
			$interval_format 	= ( is_array($interval_details) && isset($interval_details['interval_format']) ) ? $interval_details['interval_format'] : $product->get_interval_period();
			$interval			= ( is_array($interval_details) && isset($interval_details['interval']) ) ? $interval_details['interval'] : $product->get_interval();

			$to 				= empty($to) ? $from : $to;
			// $to 				= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($to) ) );	//Google calendar is not concider the last date
			if($interval_format!='day' && $interval_format!='month')
			{
					$to 				= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($to) ) );	//Google calendar is not concider the last date
					$dates		=date( 'Ymd\\THi00', strtotime($from) ).'/'.date( 'Ymd\\THi00', strtotime($to) );
			}
			else
			{
				$to 				= date( 'Y-m-d', strtotime( "+1 day",strtotime($to) ) );	//Google calendar is not concider the last date
				$dates		=date( 'Ymd', strtotime($from) ).'/'.date( 'Ymd', strtotime($to) );
			}

			$args = array(
				'action'	=> 'TEMPLATE',
				'text'		=> $item->get_name(),
				'details'	=> __('For more info please visit here: ','bookings-and-appointments-for-woocommerce').home_url( $wp->request ),
				'dates'		=> $dates,
			);

			$href = 'https://www.google.com/calendar/render?'.http_build_query($args);
		
			?><a target="_blank" href="<?php echo $href?>"><?php _e('Add as an event in google calendar','bookings-and-appointments-for-woocommerce');?></a><?php
		}
		
	}

	/**
	* Process the call back of oauth
	* @return void
	*/
	public function phive_oauth_callback_redirect() {
		
		if( !current_user_can('manage_woocommerce') ){
			wp_die('Permission denied');
		}

		if( isset($_GET['code']) ){

			$is_access_tocken_generated = $this->phive_generate_access_token( $_GET['code'] );
			
			if( !$is_access_tocken_generated ){	
				wp_redirect( admin_url( 'admin.php?page=bookings-settings&tab=google-calendar&ph_google_oauth=failed' ) );
			}else{
				wp_redirect( admin_url( 'admin.php?page=bookings-settings&tab=google-calendar&ph_google_oauth=success' ) );
			}
		
		}elseif( $_GET['error'] ){
		
			wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=integration&section=google_calendar&ph_google_oauth=failed' ) );
		
		}else{
		
			wp_die("Invalid request");
		
		}
	}

	public function ph_admin_notices(){

		if( isset($_GET['ph_google_oauth']) ){
		
			if( $_GET['ph_google_oauth'] == 'success'){

				echo '<div class="updated fade"><p>'.__( 'Successfully authenticated to google API', 'bookings-and-appointments-for-woocommerce').'</p></div>';

			}elseif( $_GET['ph_google_oauth'] == 'failed' ){
				
				echo '<div class="error fade"><p>'.__( 'Google API authentication Failed ', 'bookings-and-appointments-for-woocommerce').'</p></div>';

			}
		}
		if( isset( $_GET['ph_google_oauth_cleared']) ){
			echo '<div class="updated fade"><p>'.__( 'Cleared all google API authentication details', 'bookings-and-appointments-for-woocommerce').'</p></div>';
		}

	}

	/**
	* 
	*/
	public function generate_validate_google_caledar_credentials_html() {
		
		$access_token 	= $this->phive_get_access_token();
		$ph_recent_google_calendar_sync_response = get_option('ph_recent_google_calendar_sync_response',[]);
		$sync_failed_text = isset($ph_recent_google_calendar_sync_response['code']) && $ph_recent_google_calendar_sync_response['code'] != 200 ? __(sprintf( 'Sync Failed ( Error Code: %s )',$ph_recent_google_calendar_sync_response['code']),'bookings-and-appointments-for-woocommerce'):  '';
		ob_start();?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e('Connect Google Calendar', 'bookings-and-appointments-for-woocommerce'); ?></th>
			<td>
				<?php
				if( !$access_token ){
					
					$google_client_id		= isset( $_POST[ 'google_client_id' ] ) ? sanitize_text_field( $_POST[ 'google_client_id' ] ) : $this->google_client_id;
					$google_client_secret	= isset( $_POST[ 'google_client_secret' ] ) ? sanitize_text_field( $_POST[ 'google_client_secret' ] ) : $this->google_client_secret;
					$google_calendar_id		= isset( $_POST[ 'google_calendar_id' ] ) ? sanitize_text_field( $_POST[ 'google_calendar_id' ] ) : $this->google_calendar_id;
					

					if( $google_client_id && $google_client_secret && $google_client_id ){
						?>
						<button class="button tips" name="ph_booking_gcalendar_validate_credentials" data-tip="<?php _e('Validate the credentials with google API', 'bookings-and-appointments-for-woocommerce'); ?>" ><?php _e('Connect', 'bookings-and-appointments-for-woocommerce'); ?></button>
						<?php 
					}else{?>
						<p class="phive-google-calender-validation-result"> <?php _e('Please enter the credentials','bookings-and-appointments-for-woocommerce')?></p><?php
					}
				}else{
					$clear_url = admin_url('admin.php?page=bookings-settings&tab=google-calendar&ph_clear_google_oauth=tue');?>
					<a class="button tips" href="<?php echo esc_url( $clear_url )?>" data-tip="<?php _e('Clear the authentication details', 'bookings-and-appointments-for-woocommerce'); ?>"><?php _e('Disconnect', 'bookings-and-appointments-for-woocommerce'); ?></a>
					<?php
				}?>
			</td>
		</tr>
		<?php if( $access_token )  {?>
			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e('Status', 'bookings-and-appointments-for-woocommerce'); ?></th>
				<td>
					<p class="phive-google-calender-validation-result" style="color: #51c551;"><span class="dashicons dashicons-yes"></span><?php _e('Connection Successfull','bookings-and-appointments-for-woocommerce')?></p>
					<?php if( $sync_failed_text != '' ) { ?>
						<p class="phive-google-calender-validation-result"><span style="color:red" class="dashicons dashicons-no"></span><?php echo $sync_failed_text; ?> - <a href="https://www.pluginhive.com/knowledge-base/woocommerce-bookings-and-appointments-plugin-faqs/?utm_source=bookings&utm_medium=plugin_settings#GoogleSync"  target="_blank"><?php _e('How to fix the error','bookings-and-appointments-for-woocommerce')?></a></p>
					<?php } ?>
				</td>
			</tr>
			<?php
		}
		echo ob_get_clean();
	}

	public function ph_clear_google_oauth(){
		//Clear auth informations
		delete_option('phive_booking_google_refresh_tocken');
		delete_transient( 'phive_booking_google_access_tocken');
		wp_redirect( admin_url( 'admin.php?page=bookings-settings&tab=google-calendar&ph_google_oauth_cleared=success' ) );
	}

	/**
	* Create an access tocken by API call
	* API article: https://developers.google.com/identity/protocols/OAuth2ServiceAccount
	* @param $code:  Refresh Tocken / Code got from redirect uri (for new tocken)
	* @param $code:  code type (new_token/refresh_token)
	* @return bool
	*/
	private function phive_generate_access_token( $code, $type='new_token' ){
		
		$this->debug("Called to Gnerate Refresh/Access Token");

		if( empty($code) ){

			$this->debug("Refresh Token is empty, aborting token generation");
			return false;
		}
		
		if (get_transient( 'ph_booking_generating_google_access_token')) {

			$this->debug("Access/Refresh Token generation is in-progress, aborting token generation");
			return false;
		}

		set_transient( 'ph_booking_generating_google_access_token', true, 500 );

		$args = array(
			'client_id'		=> $this->google_client_id,
			'client_secret' => $this->google_client_secret,
			'redirect_uri'	=> $this->google_redirect_uri,
		);

		//create a new Tocken
		if( $type == 'new_token' ){
		
			$args['code'] 			= $code;
			$args['grant_type'] 	= 'authorization_code';
			$args['access_type'] 	= 'offline';
		}elseif ( $type=='refresh_token' ) { //Update from existing refresh Tocken

			$args['refresh_token']	= $code;
			$args['grant_type']		= 'refresh_token';
		}


		$params = array(
			'body'		=> http_build_query( $args ),
			'sslverify' => false,
			'timeout'	=> 60,
			'headers'	=> array(
				'Content-Type' => 'application/x-www-form-urlencoded'
			)
		);

		$response = wp_remote_post( PH_Bookings_Config::PH_BOOKING_GOOGLE_TOKEN_URI . 'token', $params );

		$this->debug("\n\n**** Generating the Access Token ****");
		$this->debug("Token Request: ".print_r($params,1));
		$this->debug("Token Response: ".print_r($response,1));

		delete_transient( 'ph_booking_generating_google_access_token');

		if ( is_wp_error( $response ) ) {
			
			$this->debug("Error Response: ".print_r($response,1));

			return false;
		}else{

			$response_data 	= json_decode( $response['body'] );
			if( isset($response_data->error) ){

				$this->debug("Error Response: ".print_r($response,1));
				return false;	
			}

			$this->debug("Success Response: ".print_r($response_data,1));

			$access_token	= sanitize_text_field( $response_data->access_token );

			$expires_in = isset($response_data->expires_in) ? intval($response_data->expires_in) : 3600;
			
			//Update auth informations
			if (!empty($response_data->refresh_token)) {
				$refresh_token 	= sanitize_text_field($response_data->refresh_token);
				update_option( 'phive_booking_google_refresh_tocken', $refresh_token);
			}

			set_transient( 'phive_booking_google_access_tocken', $access_token, $expires_in - 120 ); //Te tocken will expire from google server after 3600 seconds 

			// Save access token's expiry time in option, because some time is not deleting the transient
			update_option('ph_booking_google_access_token_expire_time', time() + $expires_in - 100);

			return true;
		}
	}

	private function phive_get_access_token( $code = '' ) {

		$access_token = get_transient( 'phive_booking_google_access_tocken' );
		$refresh_token = get_option( 'phive_booking_google_refresh_tocken' );

		$this->debug("Called to Get Access Token");
		$this->debug("Access Token  : ".print_r($access_token,1));
		$this->debug("Refresh Token : ".print_r($refresh_token,1));

		// If token exists, validate against saved expiry in option
		if ( !empty($access_token) ) {
			
			$ph_expire_time = get_option('ph_booking_google_access_token_expire_time');
			
			// If expired, remove transient
			if ( empty($ph_expire_time) || time() > $ph_expire_time ) {
				
				delete_transient('phive_booking_google_access_tocken');

				$access_token = '';

				$this->debug("Manually deleted the Access Token, transient not working properly.");
			}
		}

		if( empty($access_token) && empty($refresh_token) ){

			$this->debug("Both Access and Refresh Token empty, aborting token generation");
			return false;
		}

		if( !empty($access_token) ){
			
			$this->debug("Returned Access Token");
			return $access_token;
		}

		$this->phive_generate_access_token( $refresh_token, 'refresh_token' );
		return get_transient( 'phive_booking_google_access_tocken' );

	}

	/**
	* Add all the bookings info of the order into google calendar
	* @param $order_id:  order id
	* @return null
	*/
	public function sync_order( $order_id ){
		
		if ( !$this->google_calendar_enable ) {
			return;
		}

		$order 		= wc_get_order($order_id);
		$items 		= $order->get_items();

		// Failed order bookings creating events
		if($order->get_status() == 'failed'){
			return;
		}
		$event_ids = PH_WC_Bookings_Storage_Handler::ph_get_meta_data($order_id, 'phive_google_calendar_event_ids');
		$updated_event_ids = array();

		foreach ($items as $order_item_id => $line_item) {

			$product_id = $line_item->get_product_id();

			// Checking for booking product
			if (!ph_is_bookable_product($product_id)) {

				continue;
			}

			// Verify the configured event filters and If the booking status is not in the filter list, continue the looping.
			if (!empty($this->google_cal_event_filter) && is_array($this->google_cal_event_filter) && !in_array(current($line_item->get_meta('booking_status')), $this->google_cal_event_filter)) {

				continue;
			}

			$event_details 	= $this->prepare_event_data_by_order_item($line_item, $order_id);
			$event_id 		= !empty( $event_ids[$order_item_id] ) ? $event_ids[$order_item_id] : '';
			$updated_event_ids[$order_item_id] = $this->update_google_calendar_event( $event_details, $event_id );			
		}

		// Store Event ID with Post.
		if( !empty($updated_event_ids) ) {
			PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data( $order_id, 'phive_google_calendar_event_ids', $updated_event_ids );
		}
	}

	public function sync_order_item( $status, $item_id, $order_id, $order=''  ){

		// Get a order item for the given item ID.
		$line_item	= WC_Order_Factory::get_order_item( absint( $item_id ) );

		if ( !$this->google_calendar_enable ) {
			return;
		}

    	// Verify the configured event filters and If the booking status is not in the filter list, exit the function.
		if (!empty($this->google_cal_event_filter) && is_array($this->google_cal_event_filter) && !in_array(current($line_item->get_meta('booking_status')), $this->google_cal_event_filter)) {

			return;
		}

		$event_ids 	= PH_WC_Bookings_Storage_Handler::ph_get_meta_data( $order_id, 'phive_google_calendar_event_ids');

		$order = wc_get_order( $order_id );
		if(!($order instanceof WC_Order))
		{
			return;
		}
		$order_status  = $order->get_status();
		
		$event_id 	= !empty($event_ids[$item_id]) ? $event_ids[$item_id] : '';
		$item_canceled_status = wc_get_order_item_meta( $item_id, 'canceled', 1 );
		if( $status == 'deleted' ||  $status == 'cancelled' || ( ( $order_status == 'cancelled' || $item_canceled_status=='yes') && $status == 're-sync-google-calender' )){
			
			if ( !empty($event_id) )  {
				
				$this->delete_google_calendar_event($event_id);
				// delete_post_meta( $order_id, 'phive_google_calendar_event_ids' );
				$event_ids_stored = PH_WC_Bookings_Storage_Handler::ph_get_meta_data( $order_id, 'phive_google_calendar_event_ids');
				
				if(!empty($event_ids_stored) && (count($event_ids_stored) >= 1))
				{
					unset($event_ids_stored[$item_id]);
				}

				if(!empty($event_ids_stored) && is_array($event_ids_stored) &&(count($event_ids_stored) >= 1))
				{
					PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data( $order_id, 'phive_google_calendar_event_ids', $event_ids_stored );
				}
				else
				{
					PH_WC_Bookings_Storage_Handler::ph_delete_and_save_meta_data($order_id, 'phive_google_calendar_event_ids');
				}
			}
		}
		elseif ( $status == 're-sync-google-calender' ) {
			
			$event_details = $this->prepare_event_data_by_order_item($line_item, $order_id);	

			if( !empty($event_details) && is_array($event_ids) ) {
				$event_ids[ $item_id ] = $this->update_google_calendar_event( $event_details, $event_id );
				PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data( $order_id, 'phive_google_calendar_event_ids', $event_ids );
			}
			elseif ( !empty($event_details) ) {
				$event = $this->update_google_calendar_event( $event_details, $event_id );
				$array_event = array($item_id => $event); 
				PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data( $order_id, 'phive_google_calendar_event_ids', $array_event);	
			}
		}
		else{
		
			$event_details = $this->prepare_event_data_by_order_item($line_item, $order_id);
			if( !empty($event_details) ){
				$event_ids = empty($event_ids)?array():$event_ids;
				$event_ids[ $item_id ] = $this->update_google_calendar_event( $event_details, $event_id );
				PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data( $order_id, 'phive_google_calendar_event_ids', $event_ids );
			}
		}
	}

	/**
	 * Returns the event details to send to the google calendar
	 * @param object $line_item
	 * @param int $order_id
	 * @return array $event_details
	 */
	private function prepare_event_data_by_order_item($line_item, $order_id)
	{
		$timezone = get_option('timezone_string');
		if( empty($timezone) ) {
			$time_offset 	= get_option('gmt_offset');
			// Considered daylight saving off
			$timezone 		= timezone_name_from_abbr( "", $time_offset*60*60, 0 );
			global $wp_version;
			if ( version_compare( $wp_version, '5.3', '>=' ) ) {
				$timezone 	= wp_timezone_string();
			}
		}

		$product = wc_get_product($line_item->get_product_id());
		
		if( empty($product) ) //43325 get_type() on boolean
		{
			return;
		}
		if( $product->get_type() != 'phive_booking' ){
			return;
		}
		// Interval Details
		$interval_details	= $line_item->get_meta('_phive_booking_product_interval_details',true);
		$interval_format 	= ( is_array($interval_details) && isset($interval_details['interval_format']) ) ? $interval_details['interval_format'] : $product->get_interval_period();
		$interval			= ( is_array($interval_details) && isset($interval_details['interval']) ) ? $interval_details['interval'] : $product->get_interval();

		$product_name 		= $product->get_title();
		$booking_status		= ph_maybe_unserialize($line_item->get_meta('booking_status'));
		$booking_status 	= empty($booking_status) ? __('Order Placed', 'bookings-and-appointments-for-woocommerce') : $booking_status;
		$start 				= ph_maybe_unserialize($line_item->get_meta('From'));
		$end 				= ph_maybe_unserialize($line_item->get_meta('To'));
		$end 				= empty($end) ? $start : $end;
		$booking_notes 		= '';
		$additional_notes_label	= get_post_meta($product->get_id(), '_phive_additional_notes_label', 1);

		$name_from_gc 		= $line_item->get_meta('Customer Name');
		if(isset($name_from_gc) && !empty($name_from_gc))
		{
			$name_from_gc 	= __("Customer Name: ", 'bookings-and-appointments-for-woocommerce') . $name_from_gc;
		}

		$location = $line_item->get_meta('Location');
		if(isset($location) && !empty($location))
		{
			$location = __("Location: ", 'bookings-and-appointments-for-woocommerce') . $location;
		}

		if (!empty($additional_notes_label)) 
		{
			$additional_notes_value = $line_item->get_meta($additional_notes_label,true);
			$booking_notes 			= $additional_notes_label." : ".$additional_notes_value;
		}

		if($interval_format != 'day' && $interval_format != 'month')
		{
			$end	= str_replace('/', '-', $end);
			$start	= str_replace('/', '-', $start);
			$end 	= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($end) ) ); // adding interval to last block
		}
		else if($interval_format == 'month')  // 166951 - Month booking to be displayed on whole month instead of just the first date.
		{
			$end 	= date('Y-m-t', strtotime($end)); // getting last date of the month
			$end    = $end.' 23:59';
		}

		$order			= wc_get_order($order_id);
		$description	= null;
		
		if(!is_a($order, 'WC_order'))
		{
			return;
		}

		// Google Calendar Description Customization 
		$prefix 					= 'ph_booking_settings_';
		$gcalendar_settings 		= get_option( $prefix.'google_calendar', 1 );
		$default_calendar_details 	= "<br><br><strong>Customer Details</strong><br>[CUSTOMER_NAME]<br>[CUSTOMER_PHONE]<br>[CUSTOMER_EMAIL]<br><br><strong>Booking Details</strong><br>[BOOKING_COST]<br>[PARTICIPANT]<br>[ASSET]<br>[RESOURCE]<br><br>[ORDER_PAGE_LINK]";
		$description 				= (isset($gcalendar_settings['google_calendar_details']) && !empty($gcalendar_settings['google_calendar_details'])) ? $gcalendar_settings['google_calendar_details'] : $default_calendar_details;

		$billing_address			= $order->get_address();
		$customer_name  			= __("Booked by: ", 'bookings-and-appointments-for-woocommerce') . $billing_address['first_name'] . ' ' . $billing_address['last_name'];
		$customer_phone 			= __("Phone: ", 'bookings-and-appointments-for-woocommerce') . $billing_address['phone'];
		$customer_email 			= __("Email Id: ", 'bookings-and-appointments-for-woocommerce') . $billing_address['email'];

		$from_string 				= array('[CUSTOMER_NAME]', '[CUSTOMER_PHONE]', '[CUSTOMER_EMAIL]');
		$to_string 					= array($customer_name, $customer_phone, $customer_email);
		$description 				= str_replace($from_string, $to_string, $description);

		$booking_cost 				= __("Booking cost: ", 'bookings-and-appointments-for-woocommerce') . ph_maybe_unserialize($line_item->get_meta('Cost'));
		$booking_cost 				= !empty($booking_cost) ? $booking_cost : 0;
		
		// Add Participant
		$no_of_person 				= $line_item->get_meta('Number of persons');
		$no_of_person 				= !empty($no_of_person) ? $no_of_person : 0;
		$participant_data 			= __('Total Number of Participants', 'bookings-and-appointments-for-woocommerce').':' . $no_of_person;
		$participant_rules 			= $product->get_meta('_phive_booking_persons_pricing_rules');

		if (is_array($participant_rules) && !empty($participant_rules)) {

			foreach ($participant_rules as $participant_rule) {

				$participant_count = $line_item->get_meta($participant_rule['ph_booking_persons_rule_type']);
				$participant_count = !empty($participant_count) ? $participant_count : 0;
				$participant_data .= "<br>" . $participant_rule['ph_booking_persons_rule_type'] . ": " . $participant_count;
			}
		}
		
		// Add Assets
		$asset_data 				= '';
		$selected_assets_detail		= $line_item->get_meta('Assets');
		if (!empty($selected_assets_detail) && is_array($selected_assets_detail)) {

			$asset_rules 			= $product->get_meta('_phive_booking_assets_pricing_rules');
			$asset_label			= $product->get_meta('_phive_booking_assets_label');

			if (empty($asset_label)) {

				$asset_label = __('Asset', 'bookings-and-appointments-for-woocommerce');
			}

			if (empty($this->asset_settings)) {

				$this->asset_settings = get_option('ph_booking_settings_assets', array());
			}

			if (!empty($this->asset_settings) && !empty($this->asset_settings['_phive_booking_assets'][current($selected_assets_detail)])) {

				$asset_data .= $asset_label . ": " . $this->asset_settings['_phive_booking_assets'][current($selected_assets_detail)]['ph_booking_asset_name'];
			}
		}

		// Add Resources
		$resource_data 		= '';
		$resources_rules 	= $product->get_meta('_phive_booking_resources_pricing_rules');

		if (!empty($resources_rules) && is_array($resources_rules)) {

			$resource_loop_count = 0;
			foreach ($resources_rules as $resources_rule) {
				$resource_loop_count++;
				$resource_status = $line_item->get_meta($resources_rule['ph_booking_resources_name']);

				if (!empty($resource_status)) {

					if ($resource_loop_count == 1) {

						$resource_data .= $resources_rule['ph_booking_resources_name'] . ": " . $resource_status;
					} else {
						$resource_data .= "<br>" . $resources_rule['ph_booking_resources_name'] . ": " . $resource_status;
					}
				}
			}
		}

		// Add billing address
		$billing_address_area =	"<b>".apply_filters("ph_booking_google_calender_billing_address_title","Billing Address").'</b> <br/>';
		if( !empty($billing_address['company'])) {
			$billing_address_area.= $billing_address['company']."<br/>";
		}
		if( !empty($billing_address['address_1'])) {
			$billing_address_area.= $billing_address['address_1']."<br/>";
		}
		if( !empty($billing_address['address_2'])) {
			$billing_address_area.= $billing_address['address_2']."<br/>";
		}
		if( !empty($billing_address['city'])) {
			$billing_address_area.= $billing_address['city']." ";
		}
		if( !empty($billing_address['postcode'])) {
			$billing_address_area.= $billing_address['postcode']."<br/>";
		}
		if( !empty($billing_address['state'])) {
			$billing_address_area.= $billing_address['state'].", ";
		}
		if( !empty($billing_address['country'])) {
			$billing_address_area.= $billing_address['country'];
		}
		
		$order_page_link = '<a href="'. admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' ) .'" >Go to Order Page</a>';
		
		$from_string = array('[PRODUCT_NAME]','[BOOKING_COST]','[BOOKING_STATUS]', '[PARTICIPANT]', '[ASSET]','[RESOURCE]', '[BILLING_ADDRESS]', '[ORDER_PAGE_LINK]', '[BOOKING_NOTES]', '[NAME]', '[LOCATION]');
		$to_string = array($product_name, $booking_cost, $booking_status, $participant_data, $asset_data, $resource_data, $billing_address_area, $order_page_link, $booking_notes, $name_from_gc, $location);
		$description = str_replace($from_string, $to_string, $description);
		
		if($interval_format == 'day' || $interval_format == 'month')
		{
			// when passed as date google calendar consider as all day booking and reduced one day for calendar range bookings
			$end = date("Y-m-d", strtotime("+1 day", strtotime($end)));
			$charge_per_night 			= get_post_meta( $line_item->get_product_id(), "_phive_book_charge_per_night", 1);
			$charge_per_night_option	= get_post_meta( $line_item->get_product_id(), "_phive_book_charge_per_night_option", 1 );
			if (isset($charge_per_night) && $charge_per_night == 'yes' && isset($charge_per_night_option) && $charge_per_night_option == 'yes') {
				$end = date("Y-m-d", strtotime("-1 day", strtotime($end)));
			}

			// for day calendar time should not show
			$event_details = array(
				'summary' => $this->ph_booking_get_calendar_event_summary($order_id, $product, $line_item, $booking_status),
				'description' => $description,
				'end'	=>	array(
					'date' => date('Y-m-d', strtotime($end)),
					'timeZone' => $timezone,
				),
				'start'	=>	array(
					'date' => date('Y-m-d', strtotime($start)),
					'timeZone' => $timezone,
				),
			);
		} else {

			$event_details = array(
				'summary' => $this->ph_booking_get_calendar_event_summary($order_id, $product, $line_item, $booking_status),
				'description' => $description,
				'end'	=>	array(
					'dateTime' => date('Y-m-d\TH:i:s', strtotime($end)),
					'timeZone' => $timezone,
				),
				'start'	=>	array(
					'dateTime' => date('Y-m-d\TH:i:s', strtotime($start)),
					'timeZone' => $timezone,
				),
			);
		}
		return apply_filters( 'ph_booking_google_calender_event_data', $event_details, $line_item, $order_id );
	}

	/**
	 * Returns the summery data to event details for google calendar
	 * @param int $order_id
	 * @param objbect $product
	 * @param object $line_item
	 * @param string $booking_status
	 * @return string $summery
	 */
	public function ph_booking_get_calendar_event_summary($order_id,$product,$line_item,$booking_status)
	{
		$id = 'ph_booking_settings_'; // The prefix of the key for google calendar settings.
		$default_summary="[PRODUCT_NAME]([BOOKING_STATUS])";
		$gcalendar_settings = get_option( $id.'google_calendar', 1 );
		$resource=array();
		$resources_rules 	= $product->get_meta('_phive_booking_resources_pricing_rules');

		if (!empty($resources_rules) && is_array($resources_rules)) {

			foreach ($resources_rules as $resources_rule) {

				$resource_status = $line_item->get_meta($resources_rule['ph_booking_resources_name']);

				if (!empty($resource_status)) {

					$resource[] = $resources_rule['ph_booking_resources_name'] . ": " . $resource_status;
				}
			}
		}

		// Add Participant
		$participant=array();
		$participant_rules = $product->get_meta('_phive_booking_persons_pricing_rules');

		if (is_array($participant_rules) && !empty($participant_rules)) {

			foreach ($participant_rules as $participant_rule) {

				$participant_count = $line_item->get_meta($participant_rule['ph_booking_persons_rule_type']);
				$participant[] = $participant_rule['ph_booking_persons_rule_type'] . ": " . $participant_count;
			}
		}

		$order				= wc_get_order($order_id);
		$customer_name='';

		if (is_a($order, 'WC_order')) {

			$billing_addres	= $order->get_address();
			$customer_name = $billing_addres['first_name'] . ' ' . $billing_addres['last_name'];
		}

		// Add Assets
		$selected_assets_detail	= $line_item->get_meta('Assets');
		$asset = array();

		if (!empty($selected_assets_detail) && is_array($selected_assets_detail)) {

			$asset_rules 			= $product->get_meta('_phive_booking_assets_pricing_rules');
			$asset_label			= $product->get_meta('_phive_booking_assets_label');

			if (empty($asset_label)) {

				$asset_label = __('Asset', 'bookings-and-appointments-for-woocommerce');
			}
			if (empty($this->asset_settings)) {

				$this->asset_settings	= get_option('ph_booking_settings_assets', array());
			}
			if (!empty($this->asset_settings) && !empty($this->asset_settings['_phive_booking_assets'][current($selected_assets_detail)])) {

				$asset[] = $asset_label . ": " . $this->asset_settings['_phive_booking_assets'][current($selected_assets_detail)]['ph_booking_asset_name'];
			}
		}

		$resource=implode(',', $resource);
		$participant=implode(',', $participant);
		$customer_name=$customer_name;
		$product_name=$product->get_title();
		$asset=implode(',', $asset);

		$summary=isset($gcalendar_settings['google_calendar_summary']) ? $gcalendar_settings['google_calendar_summary'] : $default_summary;
		$from_string=array('[RESOURCE]','[PARTICIPANT]','[CUSTOMER_NAME]','[PRODUCT_NAME]','[BOOKING_STATUS]','[ASSET]');
		$to_string=array($resource,$participant,$customer_name,$product_name,$booking_status,$asset);
		$summary=str_replace($from_string, $to_string, $summary);

		// so that cancelling the order can be done for each line item separately.
		$item_id = $line_item->get_id();

		$summary = __("Order: #", 'bookings-and-appointments-for-woocommerce') . $order_id . ', ' . __("Order Item: #", 'bookings-and-appointments-for-woocommerce') . $item_id . ", " . $summary;
		
		return apply_filters('ph_booking_calendar_event_summary',$summary,$product,$line_item);
	}

	/**
	* Update an event in google calendar by an API call
	* Create new event if not exist
	* API article: https://developers.google.com/calendar/v3/reference/events/insert
	* @return event id got from API.
	*/
	private function update_google_calendar_event( $event_details, $event_id='' ){

		$access_token 	= $this->phive_get_access_token();
		$api_url		= PH_Bookings_Config::PH_BOOKING_GOOGLE_CAL_URI . '/calendars/' . $this->google_calendar_id . '/events';

		$params = array(
			'method' => 'POST',
			'body'		=> json_encode( $event_details ),
			'sslverify' => '',
			'timeout' => 60,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $access_token
			)
		);

		if ( !empty($event_id) ) {
			$api_url .= '/' . $event_id;
			$params['method'] = 'PUT';
		}

		$response = wp_remote_post( $api_url, $params );
		$this->ph_recent_google_calendar_sync_response($response);
		
		$this->debug("\n\n**** Adding Calendar event ****");
		$this->debug("Request: ".print_r($params,1));
		$this->debug("Response: ".print_r($response,1));
		
		if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 && $response['response']['message'] == 'OK' ) {
			
			$response_data = json_decode( $response['body'], true );
			return $response_data['id'];

		}else{
			// Failed case
			return false;
		}
	}
	
	/**
	* Delete an event in google calendar by an API call
	* API article: https://developers.google.com/calendar/v3/reference/events/delete
	* @return NULL.
	*/
	private function delete_google_calendar_event( $event_id ){

		$access_token 	= $this->phive_get_access_token();
		$api_url		= PH_Bookings_Config::PH_BOOKING_GOOGLE_CAL_URI . '/calendars/' . $this->google_calendar_id . '/events/'.$event_id;

		$params = array(
			'method' => 'DELETE',
			'body'		=> '',
			'sslverify' => '',
			'timeout' => 60,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $access_token
			)
		);

		$response = wp_remote_post( $api_url, $params );
		$this->ph_recent_google_calendar_sync_response($response);
		
		$this->debug("\n\n**** Deleting Calendar event ****");
		$this->debug("Event id: ".print_r($event_id,1));
		$this->debug("Response: ".print_r($response,1));
		
	}

	private function init_debug(){
		if( $this->google_calendar_debug !== 'yes' ){
			return;
		}
		$this->logger = new WC_Logger();
	}

	private function debug( $log_data ){
		if( $this->google_calendar_debug !== 'yes' ){
			return false;
		}

		$this->logger->add("ph_google_calendar_log", print_r($log_data,1)."\n");
		return true;
	}

	/**
	 * Validate the google calendar credentials
	 * 
	 * @since 3.2.3
	 */
	public function ph_booking_gcalendar_validate_credentials()
	{
		$google_client_id		= isset($_POST['google_client_id']) ? sanitize_text_field($_POST['google_client_id']) : $this->google_client_id;
		$google_client_secret	= isset($_POST['google_client_secret']) ? sanitize_text_field($_POST['google_client_secret']) : $this->google_client_secret;
		$google_calendar_id		= isset($_POST['google_calendar_id']) ? sanitize_text_field($_POST['google_calendar_id']) : $this->google_calendar_id;

		if ($google_client_id && $google_client_secret && $google_client_id) {
			$url = PH_Bookings_Config::PH_BOOKING_GOOGLE_OAUTH_URI . 'auth';
			$auth_link = add_query_arg(
				array(
					'scope'			=> PH_Bookings_Config::PH_BOOKING_GOOGLE_API_SCOPE,
					'redirect_uri'	=> $this->google_redirect_uri,
					'response_type'	=> 'code',
					'client_id'		=> $google_client_id,
					'approval_prompt' => 'force',
					'access_type'	 => 'offline',
				),
				$url
			);
			update_option('ph_recent_google_calendar_sync_response', []);
			wp_redirect($auth_link);
		}
	}

	/**
	 * Save the last google calendar sync response
	 * 
	 * @param mixed $response
	 */
	public function ph_recent_google_calendar_sync_response($response)
	{
		if (is_wp_error($response)) {

			$response_data = array(
				'code'		=> $response->get_error_code(),
				'message'	=> $response->get_error_message()
			);
		} else {
			$response_data = array(
				'code'		=> wp_remote_retrieve_response_code($response),
				'message'	=> wp_remote_retrieve_response_message($response)
			);
		}

		update_option('ph_recent_google_calendar_sync_response', $response_data);
	}

	/**
	 * Remove the google calendar event
	 * 
	 * @param $item_id
	 */
	public function ph_woocommerce_before_delete_order_item($item_id)
	{
		$order_id = wc_get_order_id_by_order_item_id($item_id);
		$this->sync_order_item('deleted', $item_id, $order_id);
	}
}
new phive_booking_google_calendar();