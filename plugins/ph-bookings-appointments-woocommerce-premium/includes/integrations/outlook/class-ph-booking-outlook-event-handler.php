<?php

/**
 * Outlook event handler.
 *
 * @package bookings-and-appointments-for-woocommerce
 */

defined('ABSPATH') || exit;

class PH_Booking_Outlook_Event_Handler {

	/**
	 * Order ID.
	 *
	 * @var int
	 */
	private $order_id;

	/**
	 * WC Order.
	 *
	 * @var WC_Order
	 */
	private $order;

	/**
	 * Timezone
	 *
	 * @var string
	 */
	private $timezone;

	/**
	 * Outlook settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Date format for Outlook.
	 *
	 * @var string
	 */
	private const DATE_FORMAT = 'Y-m-d\TH:i:s';

	/**
	 * Outlook class instance.
	 *
	 * @var PH_Bookings_Outlook
	 */
	private $outlook_instance;

	/**
	 * Outlook event handler.
	 */
	public function __construct() {

		if (null == $this->outlook_instance) {
			$this->outlook_instance = PH_Bookings_Outlook::ph_get_instance();
		}

		$this->settings = $this->outlook_instance::ph_get_settings();

		if ($this->settings['enable_outlook']) {
			// Add Outlook event on successful order placement.
			add_action('woocommerce_thankyou', array($this, 'ph_add_event'));
			add_action('ph_booking_google_calender_sync_for_admin_bookings', array($this, 'ph_add_event'));

			// Hook to add events on booking status changes.
			add_action( 'ph_booking_status_changed', array( $this, 'ph_add_outlook_event_on_booking_status_change'), 10, 4);
		}
	}

	/**
	 * Add Outlook event on booking status change.
	 *
	 * @param string $status   Booking status.
	 * @param int    $item_id  Booking item ID.
	 * @param int    $order_id Order ID.
	 * @param WC_Order $order  WooCommerce order instance.
	 */
	public function ph_add_outlook_event_on_booking_status_change($status, $item_id, $order_id, $order=''){

		// Delete Outlook event for deleted booking
		if ('deleted' == $status) {

			$order_event_ids = PH_WC_Bookings_Storage_Handler::ph_get_meta_data($order_id, 'ph_bookings_outlook_event_ids');

			// Check for event exists.
			if (!empty($order_event_ids[$item_id]) ) {
				
				$this->outlook_instance::ph_check_and_refresh_token();

				$this->ph_delete_outlook_calendar_event($order_event_ids[$item_id], $order_id);
			}
		} else {

			$this->ph_add_event($order_id);
		}
	}

	/**
	 * Delete an event from Outlook Calendar.
	 * 
	 * @param string $event_id
	 * @param int    $order_id 
	 *
	 * @return void
	 */
	public function ph_delete_outlook_calendar_event($event_id, $order_id){

		// Retrieve the list of event IDs associated with the given order.
		$order_event_ids = PH_WC_Bookings_Storage_Handler::ph_get_meta_data($order_id, 'ph_bookings_outlook_event_ids');

		if (in_array($event_id, $order_event_ids)) {

			// Construct the DELETE API endpoint URL.
			$ph_url = PH_Bookings_Config::PH_BOOKING_OUTLOOK_EVENT_URL . '/events/' . $event_id;
			
			// If a specific calendar ID is configured in the settings, update the URL to target the specified calendar.
			if ( isset($this->settings['outlook_calendar_id']) && !empty($this->settings['outlook_calendar_id'])) {
				
				$ph_url = PH_Bookings_Config::PH_BOOKING_OUTLOOK_EVENT_URL . '/calendars/' . $this->settings['outlook_calendar_id'] . '/events/' . $event_id;
			}

			// Set up the request parameters.
			$params = array(
				'url'     => $ph_url,
				'method'  => 'DELETE',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . get_transient('ph_bookings_outlook_auth_token'),
				),
			);
		
			// Make the request using the API invoker.
			$response = PH_Bookings_API_Invoker::ph_make_request($params);

			if (is_wp_error($response)) {
				$this->outlook_instance->ph_debug($response->get_error_message(), $this->settings['debug']);
				return;
			}

			// Check for a successful response.
			if (!empty($response['response']['code']) && $response['response']['code'] === 204) {

				$ph_item_id = '';

				// Remove the event ID from the list of associated events.
				foreach($order_event_ids as $key => $value) {

					if ( $value === $event_id) {

						$ph_item_id = $key;

						unset($order_event_ids[$key]);
					}
				}

				// Log the successful deletion details.
				$ph_log_details = array(
					'message' 	=> __('Successfully Deleted the Event.', ''),
					'event_id' 	=> $event_id,
					'item_id'	=> $ph_item_id,
					'order_id'	=> $order_id,
					'response' 	=> $response,
				);
				
				$this->outlook_instance->ph_debug($ph_log_details, $this->settings['debug']);

				// Update the order metadata after deleting the event.
				if (empty($order_event_ids)) {

					PH_WC_Bookings_Storage_Handler::ph_delete_and_save_meta_data($order_id, 'ph_bookings_outlook_event_ids');
				} else {
					PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data($order_id, 'ph_bookings_outlook_event_ids', $order_event_ids);
				}
			}
		}
	}

	/**
	 * Add event to Outlook calendar.
	 *
	 * @param int $order_id 
	 */
	public function ph_add_event($order_id) {

		if (empty($this->settings['client_id']) || empty($this->settings['client_secret'])) {
			$this->outlook_instance::ph_debug(
				'Missing Outlook Application ID or Client Secret',
				$this->settings['debug']
			);

			return;
		}

		$this->order_id = $order_id;
		$this->order	= wc_get_order($this->order_id);

		$order_event_ids = PH_WC_Bookings_Storage_Handler::ph_get_meta_data($order_id, 'ph_bookings_outlook_event_ids');

		/* Do nothing when the order is failed or when the events are already created for the order
		 * (Scenario where Thankyou Page is reloaded to prevent multiple entries).
		 */
		if ($this->ph_is_failed_order()) {
			return;
		}

		$this->outlook_instance::ph_check_and_refresh_token();

		$order_items = $this->order->get_items();

		$booking_details 	= array();
		$event_ids			= array();

		foreach ($order_items as $item_id => $item) {

			$product = wc_get_product($item->get_product_id());

			if (!$product instanceof WC_Product_phive_booking) {
				continue;
			}

			// Check if an event filter is configured and the current booking status of the item is not in the allowed filter.
			// If the condition is met, skip processing this item.
			if (!empty($this->settings['outlook_event_filter']) && !in_array(current($item->get_meta('booking_status')),$this->settings['outlook_event_filter'])) {

				continue;
			}

			// Retrieve the event ID associated with the current order item, if it exists.
			$event_id 	= !empty($order_event_ids[$item_id]) ? $order_event_ids[$item_id] : '';

			// Get the current booking status of the item.
			$status = current($item->get_meta('booking_status'));

			// Check if the event ID exists and if the booking status is either 'deleted' or 'canceled'.
			if (!empty($event_id) && ($status == 'deleted' ||  $status == 'canceled')){

				// Call the function to delete the event from Outlook Calendar.
				$this->ph_delete_outlook_calendar_event($event_id, $order_id);

				continue;
			}

			$booking_details 	= $this->ph_get_booking_data_for_item($item);
			$event_body 		= $this->ph_build_event_body($booking_details, $item_id, $order_id);
			$ph_url 			= PH_Bookings_Config::PH_BOOKING_OUTLOOK_EVENT_URL . '/events'; 	// Set the default API endpoint URL for creating events in Outlook.
			$ph_method 			= 'POST'; 															// Default the HTTP method to POST for creating a new event.

			// If a specific calendar ID is configured in the settings, update the URL to target the specified calendar.
			if ( isset($this->settings['outlook_calendar_id']) && !empty($this->settings['outlook_calendar_id'])) {

				$ph_url = PH_Bookings_Config::PH_BOOKING_OUTLOOK_EVENT_URL . '/calendars/' . $this->settings['outlook_calendar_id'] . '/events';
			}

			// Check if an event ID already exists for this item (indicating the event was previously created).
			// If so, update the URL to target the existing event and switch the HTTP method to PATCH for updating the event.
			if (!empty($order_event_ids[$item_id])) {

				$ph_url 	.= '/' . $order_event_ids[$item_id];
				$ph_method 	 = 'PATCH';
			}

			$params = array(
				'url'		=> $ph_url,
				'method'	=> $ph_method,
				'body'		=> json_encode($event_body),
				'headers'	=> array(
					'Content-Type'	=> 'application/json',
					'Authorization'	=> 'Bearer ' . get_transient('ph_bookings_outlook_auth_token')
				)
			);

			$response = PH_Bookings_API_Invoker::ph_make_request($params);

			if (is_wp_error($response)) {
				$this->outlook_instance->ph_debug($response->get_error_message(), $this->settings['debug']);
				continue;
			}

			$response_code 		= wp_remote_retrieve_response_code($response);
			$response_message	= wp_remote_retrieve_response_message($response);
			$response_body 		= wp_remote_retrieve_body($response);

			// Check if the API response indicates a successful creation or update of the event.
			if ((201 === $response_code || 200 === $response_code) && ('Created' === $response_message || 'OK' === $response_message) && !empty($response_body)) {

				$response_body = json_decode($response_body);
				$event_ids[$item_id] = isset($response_body->id) ? $response_body->id : '';
			}

			$this->outlook_instance::ph_debug(
				array(
					'order_id'			=> $this->order_id,
					'item_id'			=> $item_id,
					'response_code'		=> $response_code,
					'response_message'	=> $response_message
				),
				$this->settings['debug']
			);
		}

		if (!empty($event_ids)) {
			PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data($order_id, 'ph_bookings_outlook_event_ids', $event_ids);
		}
	}

	/**
	 * Generate booking data for the given order item.
	 *
	 * @param WC_Order_Item_Product $item
	 */
	private function ph_get_booking_data_for_item($item) {

		$booking_data 	= array();
		$product		= wc_get_product($item->get_product_id());

		if (!$this->ph_is_valid_product($product)) {
			return $booking_data;
		}

		$args = array(
			'product'	=> $product,
			'item'		=> $item
		);

		$participants	= PH_Bookings_Order_Item_Handler::ph_get_participants($args);
		$resources		= PH_Bookings_Order_Item_Handler::ph_get_resources($args);
		$assets			= PH_Bookings_Order_Item_Handler::ph_get_assets($args);
		$customer_details = $this->ph_get_customer_details_from_order();
		$billing_addres	= $this->ph_get_billing_address_from_order();

		$booking_data = array(
			'product_name'		=> $product->get_name(),
			'booking_status'	=> PH_Bookings_Order_Item_Handler::ph_get_booking_status($args),
			'booking_cost'		=> PH_Bookings_Order_Item_Handler::ph_get_booking_cost($args),
			'participant'		=> Ph_Bookings_General_Functions_Class::ph_prepare_concatenated_data($participants),
			'resource'			=> Ph_Bookings_General_Functions_Class::ph_prepare_concatenated_data($resources),
			'asset'				=> Ph_Bookings_General_Functions_Class::ph_prepare_concatenated_data($assets),
			'customer_name'		=> $customer_details['first_name'] . ' ' . $customer_details['last_name'],
			'customer_phone'	=> $customer_details['phone'],
			'customer_email'	=> $customer_details['email'],
			'billing_address'	=> $billing_addres,
			'booking_notes'		=> PH_Bookings_Order_Item_Handler::ph_get_booking_notes($args),
		);

		$booked_date = PH_Bookings_Order_Item_Handler::ph_get_booked_date($args);

		$booking_data['from'] 	= $this->ph_format_timestamp($booked_date['from']);
		$booking_data['to']		= $this->ph_format_timestamp($booked_date['to']);

		return $booking_data;
	}

	/**
	 * Check if the order status is failed.
	 *
	 * @return bool
	 */
	private function ph_is_failed_order() {
		return 'failed' === $this->order->get_status();
	}

	/**
	 * Check if the given product is valid.
	 *
	 * @param WC_Product_phive_booking $product
	 *
	 * @return bool
	 */
	private function ph_is_valid_product($product) {
		return (!empty($product) && 'phive_booking' == $product->get_type());
	}

	/**
	 * Get customer details.
	 *
	 * @param WC_Order $order
	 *
	 * @return array $customer_details
	 */
	private function ph_get_customer_details_from_order() {

		$customer_details = array();

		$billing_addres = $this->order->get_address();

		$customer_details = array(
			'phone'			=> $billing_addres['phone'],
			'email'			=> $billing_addres['email'],
			'first_name'	=> $billing_addres['first_name'],
			'last_name'		=> $billing_addres['last_name']
		);

		return $customer_details;
	}

	/**
	 * Get billing address.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private function ph_get_billing_address_from_order() {
		
		$billing_addres_parts = array();
		$billing_addres = $this->order->get_address();

		if (!empty($billing_addres['company'])) {
			$billing_addres_parts[] = $billing_addres['company'];
		}

		if (!empty($billing_addres['address_1'])) {
			$billing_addres_parts[] = $billing_addres['address_1'];
		}

		if (!empty($billing_addres['address_2'])) {
			$billing_addres_parts[] = $billing_addres['address_2'];
		}

		if (!empty($billing_addres['city'])) {
			$billing_addres_parts[] = $billing_addres['city'];
		}

		if (!empty($billing_addres['postcode'])) {
			$billing_addres_parts[] = $billing_addres['postcode'];
		}

		if (!empty($billing_addres['state'])) {
			$billing_addres_parts[] = $billing_addres['state'];
		}

		if (!empty($billing_addres['country'])) {
			$billing_addres_parts[] = $billing_addres['country'];
		}

		return implode("<br/>", $billing_addres_parts);
	}

	/**
	 * Outlook event body.
	 *
	 * @param array $booking_details
	 */
	private function ph_build_event_body($booking_details, $item_id, $order_id) {

		$body = array(
			'subject'	=> $this->ph_get_event_subject($booking_details),
			'body'		=> array(
				'contentType'	=> 'HTML',
				'content'		=> $this->ph_get_event_description($booking_details, $item_id, $order_id),
			),
			'start' => array(
				'dateTime' => $booking_details['from'],
				'timeZone' => $this->timezone
			),
			'end' => array(
				'dateTime' => $booking_details['to'],
				'timeZone' => $this->timezone
			)
		);

		if ($this->settings['event_attendee']) {

			$attendees = array(
				'attendees' => array(
					array(
						'emailAddress'	=> array(
							'address'	=> $booking_details['customer_email'],
							'name'		=> $booking_details['customer_name']
						),
						'type' => 'required'
					)
				)
			);

			$body = array_merge($body, $attendees);
		}

		return apply_filters( 'ph_booking_outlook_calender_event_data', $body, $booking_details, $order_id, $item_id );
	}

	/**
	 * Format timestamp for Outlook.
	 *
	 * @var mixed $timestamp
	 *
	 * @return DateTime
	 */
	private function ph_format_timestamp($timestamp) {

		$timezone = get_option('timezone_string');

		$this->timezone = $timezone;

		if (empty($timezone)) {
			$time_offset = get_option('gmt_offset');
			$timezone = timezone_name_from_abbr('', $time_offset * 60 * 60, 0);
			$this->timezone = $timezone;
			$timezone = wp_timezone_string();
		}

		// Create a DateTime object with the given timestamp
		$date_time = new DateTime($timestamp, new DateTimeZone($timezone));

		// Format the date in the required .ics format
		return $date_time->format(self::DATE_FORMAT);
	}

	/**
	 * Get event subject.
	 *
	 * @param array $booking_details
	 *
	 * @return string $event_subject
	 */
	private function ph_get_event_subject($booking_details) {

		$event_subject = $this->settings['event_title'];

		$mapping = array(
			'[PRODUCT_NAME]'	=> isset($booking_details['product_name']) ? $booking_details['product_name'] : '',
			'[BOOKING_STATUS]' 	=> isset($booking_details['booking_status']) ? $booking_details['booking_status'] : '',
			'[PRODUCT_NAME]' 	=> isset($booking_details['product_name']) ? $booking_details['product_name'] : '',
			'[RESOURCE]' 		=> isset($booking_details['resource']) ? $booking_details['resource'] : '',
			'[PARTICIPANT]' 	=> isset($booking_details['participant']) ? $booking_details['participant'] : '',
			'[ASSET]' 			=> isset($booking_details['asset']) ? $booking_details['asset'] : '',
			'[CUSTOMER_NAME]'	=> isset($booking_details['customer_name']) ? $booking_details['customer_name'] : '',
		);

		$event_subject = str_replace(
			array_keys($mapping),
			array_values($mapping),
			$event_subject
		);

		return $event_subject;
	}

	/**
	 * Get event description.
	 */
	private function ph_get_event_description($booking_details, $item_id, $order_id) {

		$event_description = $this->settings['event_description'];

		$mapping = array(
			'[PRODUCT_NAME]'	=> isset($booking_details['product_name']) ? $booking_details['product_name'] : '',
			'[BOOKING_COST]'	=> isset($booking_details['booking_cost']) ? $booking_details['booking_cost'] : '',
			'[CUSTOMER_NAME]'	=> isset($booking_details['customer_name']) ? $booking_details['customer_name'] : '',
			'[BOOKING_STATUS]'	=> isset($booking_details['booking_status']) ? $booking_details['booking_status'] : '',
			'[PARTICIPANT]'		=> isset($booking_details['participant']) ? $booking_details['participant'] : '',
			'[ASSET]'			=> isset($booking_details['asset']) ? $booking_details['asset'] : '',
			'[RESOURCE]' 		=> isset($booking_details['resource']) ? $booking_details['resource'] : '',
			'[BILLING_ADDRESS]'	=> isset($booking_details['billing_address']) ? $booking_details['billing_address'] : '',
			'[BOOKING_NOTES]'	=> isset($booking_details['booking_notes']) ? $booking_details['booking_notes'] : '',
			'[CUSTOMER_PHONE]'	=> isset($booking_details['customer_phone']) ? $booking_details['customer_phone'] : '',
			'[CUSTOMER_EMAIL]'	=> isset($booking_details['customer_email']) ? $booking_details['customer_email'] : '',
		);

		$event_description = str_replace(
			array_keys($mapping),
			array_values($mapping),
			$event_description
		);

		return apply_filters( 'ph_booking_outlook_calender_event_description', $event_description, $booking_details, $order_id, $item_id );
	}
}

new PH_Booking_Outlook_Event_Handler();
