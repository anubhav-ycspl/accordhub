<?php

class phive_booking_admin_order
{

	/**
	 * @var $assets_rules
	 */
	public $assets_rules;

	/**
	 * @var $blog_name
	 */
	public $blog_name;

	/**
	 * @var $email_bg_color
	 */
	public $email_bg_color;

	/**
	 * @var $email_body_bg_color
	 */
	public $email_body_bg_color;

	/**
	 * @var $email_base_color
	 */
	public $email_base_color;

	/**
	 * @var $email_text_color
	 */
	public $email_text_color;

	/**
	 * @var $wp_date_format
	 */
	public $wp_date_format;

	/**
	 * @var $billing_address
	 */
	public $billing_address;

	/**
	 * @var $interval
	 */
	public $interval;

	/**
	 * @var $interval_period
	 */
	public $interval_period;

	/**
	 * @var $shop_opening_time
	 */
	public $shop_opening_time;

	/**
	 * @var $shop_closing_time
	 */
	public $shop_closing_time;

	public function __construct()
	{
		if (isset($_REQUEST['ph_product_submit'])) {
			$this->ph_booking_form_validation();
		} else if (!empty($_REQUEST['ph_calendar_submit'])) {
			$this->ph_booking_process_form_data();
		} else {
			$this->ph_generate_booking_form(1);
		}

	}

	public function ph_generate_booking_form($step)
	{
		switch ($step) {
			case 1:
				include_once('views/html-ph-booking-admin-order.php');
				break;
			case 2:

				include_once('views/html-ph-booking-admin-order-calender.php');
				$scripts = new phive_booking_initialze_premium;

				// JS events were getting added twice
				// add_action( 'wp_enqueue_scripts', array( $this, 'phive_booking_scripts' ) );
				// add_filter( 'admin_enqueue_scripts', array( $this, 'phive_admin_scripts' ) );		
				// add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
				// add_action( 'plugins_loaded', array( $this,'register_booking_product_product_type' ) );
				// $scripts->phive_booking_scripts();
				// $scripts->phive_admin_scripts();

				// Loading required style sheets
				$scripts->phive_booking_styles_admin_booking_calendar();

				break;
			default:
				include_once('views/html-ph-booking-admin-order.php');
				break;
		}
	}
	public function ph_booking_form_validation()
	{

		$OrderIdError = "";
		$step = 1;
		$customer_id = isset($_REQUEST['ph_customer_id']) ? absint($_POST['ph_customer_id']) : 0;
		$product_id = absint($_REQUEST['ph_filter_product_ids']);
		$booking_order = wc_clean($_REQUEST['ph_booking_order']);
		$step = $_REQUEST['next_step'];
		if ($booking_order === 'existing') {

			$order_id = absint($_REQUEST['ph_booking_order_id']);

			$order = wc_get_order($order_id);

			if (!($order instanceof WC_Order)) {
				$step = 1;
				echo '<div class="notice ph-notice-error notice-error is-dismissible">
					<p>' . __('Invalid Order ID.', 'bookings-and-appointments-for-woocommerce') . '</p>
				</div>';
			} elseif ($order->get_user_id() != $customer_id) {
				$step = 1;
				echo '<div class="notice ph-notice-error notice-error is-dismissible">
					<p>' . __('User & Order ID combination not found. Please enter the correct details and try again.', 'bookings-and-appointments-for-woocommerce') . '</p>
				</div>';
			}
		}

		$this->ph_generate_booking_form($step);

	}

	private function phive_buffer_before_time($from, $buffer_period, $book_interval, $buffer_before, $buffer_after = '0')
	{


		switch ($buffer_period) {
			case 'day':
				$buffer_before_time = date('Y-m-d', (strtotime($from) - ($buffer_before * 3600 * 24)));
				break;
			case 'hour':
				$buffer_before_time = date('Y-m-d H:i', (strtotime($from) - ($buffer_before * 3600)));
				break;
			case 'minute':
				$buffer_after = isset($buffer_after) ? $buffer_after : '00';
				$buffer_before_time = date('Y-m-d H:i', (strtotime($from) - ($buffer_before * 60)));
				break;
		}
		return $buffer_before_time;

	}

	private function phive_buffer_after_time($from, $to = '', $buffer_period = '', $book_interval = '', $buffer_before = '0', $buffer_after = '', $product_id = '')
	{
		$interval = get_post_meta($product_id, "_phive_book_interval", 1);
		$interval_period = get_post_meta($product_id, '_phive_book_interval_period', 1);

		$to = !empty($to) ? $to : $from;
		switch ($buffer_period) {
			case 'day':
				$buffer_after_time = date('Y-m-d', (strtotime($to) + ($buffer_after * 3600 * 24)));
				break;
			case 'hour':
				$buffer_after_time = date('Y-m-d H:i', (strtotime($to) + ($buffer_after * 3600)));
				;
				break;
			case 'minute':
				$buffer_before = isset($buffer_before) ? $buffer_before : '00';
				$buffer_after_from = date("Y-m-d H:i", strtotime("+$interval $interval_period", strtotime($to)));
				$buffer_after_time = date('Y-m-d H:i', (strtotime($buffer_after_from) + ($buffer_after * 60)));
				;
				break;
		}
		return $buffer_after_time;
	}

	private function phive_save_booking_buffer_info($product_id, $buffer_before_time, $buffer_after_time, $person_as_booking = '', $number_of_booking = '', $is_buffer = '', $buffer_type = '', $asset_id = '')
	{
		$new_post = array(
			'ID' => '',
			'post_type' => 'booking_buffer_freez', // Custom Post Type Slug
			'post_status' => 'open',
			'post_title' => 'Booking buffer freezer-' . uniqid(),//171763 Issue: When activating our Bookings plugin, its taking a quite long time while checkout.
			'ping_status' => 'closed',
		);

		$buffer_id = wp_insert_post($new_post);
		if (!$buffer_id) {
			return false;
		}
		if ($is_buffer == 'yes' && $buffer_type == 'buffer-before') {
			$meta_values = array(
				'_product_id' => $product_id,
				'Buffer_before_From' => $buffer_before_time,
				'Buffer_before_To' => $buffer_after_time,
				'_booking_customer_id' => is_user_logged_in() ? get_current_user_id() : 0,
				'Number of persons' => $number_of_booking,
				'person_as_booking' => $person_as_booking,
				'ph_canceled' => '0',

			);
		} elseif ($is_buffer == 'yes' && $buffer_type == 'buffer-after') {
			$meta_values = array(
				'_product_id' => $product_id,
				'Buffer_after_From' => $buffer_before_time,
				'Buffer_after_To' => $buffer_after_time,
				'_booking_customer_id' => is_user_logged_in() ? get_current_user_id() : 0,
				'Number of persons' => $number_of_booking,
				'person_as_booking' => $person_as_booking,
				'ph_canceled' => '0',

			);
		}
		// buffer not getting applied for other products with same asset with backend booking
		if ($asset_id) {
			$meta_values['buffer_asset_id'] = $asset_id;
		}
		foreach ($meta_values as $meta_key => $value) {
			update_post_meta($buffer_id, $meta_key, $value);
		}

		return $buffer_id;
	}

	public function ph_booking_process_form_data()
	{

		$customer_id = absint($_REQUEST['phive_customer_id']);
		$product_id = absint($_REQUEST['phive_product_id']);
		$booking_order = wc_clean($_REQUEST['ph_booking_order']);
		$product = wc_get_product($product_id);
		$order_id = 0;
		$item_id = 0;
		$booking_cost = $_REQUEST['phive_booked_price'];
		$buffer_before = get_post_meta($product_id, "_phive_buffer_before", 1);
		$buffer_after = get_post_meta($product_id, "_phive_buffer_after", 1);
		$book_interval = get_post_meta($product_id, "_phive_book_interval", 1);
		$buffer_period = get_post_meta($product_id, "_phive_buffer_period", 1);
		$enable_buffer = get_post_meta($product_id, '_phive_enable_buffer', 1);
		$persons_as_booking = get_post_meta($product_id, "_phive_booking_persons_as_booking", 1);
		$interval = get_post_meta($product_id, "_phive_book_interval", 1);
		$interval_period = get_post_meta($product_id, '_phive_book_interval_period', 1);
		$guest_email_id = $_POST['ph_guest_email_id'] ?? '';
		$guest_first_name = $_POST['ph_guest_first_name'] ?? '';
		$guest_last_name = $_POST['ph_guest_last_name'] ?? '';


		if (empty($booking_cost)) {
			$booking_cost = 0;
		}

		if ($booking_order == 'new') {
			$order = new WC_Order();
			$order->set_customer_id($customer_id); 

			if (!empty($_REQUEST['group_payment_mode']) && $_REQUEST['group_payment_mode'] === 'group') {
				$order->update_meta_data('group_payment_mode', sanitize_text_field($_REQUEST['group_payment_mode']));
				$total_payers = intval($_REQUEST['group_total_payers'] ?? 1);
				

				$total = $order->get_total();
				$share_total = round($total / $total_payers, wc_get_price_decimals());

				$order->set_total($share_total);

				// Save total payers
				if (!empty($_REQUEST['group_total_payers'])) {
					$order->update_meta_data('group_total_payers', intval($_REQUEST['group_total_payers']));
				}

				// Save additional payer details (Payer 2+)
				if (!empty($_REQUEST['group_member_name'])) {
					$names = $_REQUEST['group_member_name'];
					$emails = $_REQUEST['group_member_email'] ?? [];
					$phones = $_REQUEST['group_member_phone'] ?? [];
					$companies = $_REQUEST['group_member_company'] ?? [];

					$additional_payers = [];

					foreach ($names as $index => $name) {
						if ($index < 2)
							continue; // Skip Payer 1 (main buyer)

						$additional_payers[$index] = [
							'name' => sanitize_text_field($name),
							'email' => sanitize_email($emails[$index] ?? ''),
							'phone' => sanitize_text_field($phones[$index] ?? ''),
							'company' => sanitize_text_field($companies[$index] ?? ''),
						];
					}

					if (!empty($additional_payers)) {
						$order->update_meta_data('group_additional_payers', $additional_payers);
					}
				}
			}


			

			$order->set_total($booking_cost);
			$order->update_meta_data('group_original_total', $order->get_total());
			$order_id = $order->save();

		} elseif ($booking_order == "existing") {
			$order_id = absint($_REQUEST['ph_bookable_product_id']);
			$order = new WC_Order($order_id);

			if (version_compare(WC_VERSION, '3.0', '<')) {
				PH_WC_Bookings_Storage_Handler::ph_add_and_save_meta_data($order_id, '_order_total', ($order->get_total() + $booking_cost));
			} else {
				$order->set_total($order->get_total('edit') + $booking_cost);
				$order->save();
			}
			$order->update_status('pending');

		}
		if ($order_id) {
			$item_id = wc_add_order_item($order_id, array(
				'order_item_name' => $product->get_title(),
				'order_item_type' => 'line_item',
			));

			// allow guest bookings
			if ((!empty($customer_id)) || ($customer_id == 0)) {

				$order = wc_get_order($order_id);
				$cron_manager = new phive_booking_availability_scheduler();
				$cart_item['phive_book_from_date'] = sanitize_text_field($_REQUEST['phive_book_from_date']);
				$cart_item['phive_book_to_date'] = sanitize_text_field($_REQUEST['phive_book_to_date']);
				$cart_item['phive_booked_price'] = sanitize_text_field($_REQUEST['phive_booked_price']);

				// 144100 - Translation function was applied on an array, giving fatal error in php8
				$cart_item['phive_booked_persons'] = isset($_REQUEST['phive_book_persons']) ? $_REQUEST['phive_book_persons'] : '';
				$cart_item['phive_booked_resources'] = isset($_REQUEST['phive_book_resources']) ? (!is_array($_REQUEST['phive_book_resources']) ? stripslashes($_REQUEST['phive_book_resources']) : $_REQUEST['phive_book_resources']) : '';

				$cart_item['phive_book_additional_notes_text'] = isset($_REQUEST['phive_book_additional_notes_text']) ? __($_REQUEST['phive_book_additional_notes_text'], 'bookings-and-appointments-for-woocommerce') : '';
				$cart_item['product_id'] = $product_id;
				$cart_item['phive_booked_assets'] = isset($_REQUEST['phive_book_assets']) ? $_REQUEST['phive_book_assets'] : '';

				if (($booking_order == 'new')) {
					$keys = array(
						'first_name',
						'last_name',
						'company',
						'address_1',
						'address_2',
						'city',
						'state',
						'postcode',
						'country',
						'phone'
					);

					$types = array('shipping', 'billing');

					foreach ($types as $type) {
						$address = array();

						foreach ($keys as $key) {
							$address[$key] = (string) get_user_meta($customer_id, $type . '_' . $key, true);

						}

						$address['email'] = $guest_email_id;

						if (!empty($guest_first_name)) {

							$address['first_name'] = $guest_first_name;
						}

						if (!empty($guest_last_name)) {

							$address['last_name'] = $guest_last_name;
						}

						$order->set_address($address, $type);
					}
				}
			}
			$values = $cart_item;

			$product_interval_details = array(
				'interval' => $interval,
				'interval_format' => $interval_period
			);
			wc_add_order_item_meta($item_id, '_phive_booking_product_interval_details', $product_interval_details);

			$participant_booking_data = array();
			if (array_key_exists('phive_booked_persons', $values)) {

				$persons_pricing_rules = get_post_meta($product_id, "_phive_booking_persons_pricing_rules", 1);
				$number_of_persons = 0;
				// Looping through the rule and assign the corresponding rule value given by customer
				foreach ($persons_pricing_rules as $key => $rule) {

					if (empty($rule)) {
						continue;
					}

					if (!empty($values['phive_booked_persons'][$key])) {
						$participant_booking_data[] = array(
							'participant_label' => $rule['ph_booking_persons_rule_type'],
							'participant_count' => $values['phive_booked_persons'][$key]
						);
						$number_of_persons += $values['phive_booked_persons'][$key];
						wc_add_order_item_meta($item_id, $rule['ph_booking_persons_rule_type'], $values['phive_booked_persons'][$key]);
					}

				}

				if (!empty($number_of_persons)) {

					wc_add_order_item_meta($item_id, 'Number of persons', $number_of_persons);

					// need to show as Total number of participants rather than Number of persons
					wc_add_order_item_meta($item_id, __('Total Number of Participants', 'bookings-and-appointments-for-woocommerce'), $number_of_persons);
				}

				// error_log('participant_booking_data : '.print_r($participant_booking_data,1));
				if (count($participant_booking_data) > 0) {
					wc_add_order_item_meta($item_id, 'ph_bookings_participant_booking_data', $participant_booking_data);
				}
			}
			// Display Additional Notes
			if (array_key_exists('phive_book_additional_notes_text', $values)) {

				$addition_notes_label = get_post_meta($product_id, '_phive_additional_notes_label', 1);
				// Looping through the rule and assign the corresponding rule value given by customer


				if (!empty($values['phive_book_additional_notes_text'])) {
					$additional_notes_text = $values['phive_book_additional_notes_text'];
					wc_add_order_item_meta($item_id, $addition_notes_label, $additional_notes_text);
					//172742 - Issue - When exporting the bookings, we are not able to see the Additional notes text for the admin (backend) bookings.
					wc_add_order_item_meta($item_id, 'ph_bookings_customer_additional_notes', (array) $additional_notes_text);
				}

			}

			//Disply resources details with items
			if (array_key_exists('phive_booked_resources', $values)) {
				$resources_booking_data = array();
				$resources_pricing_rules = get_post_meta($product_id, "_phive_booking_resources_pricing_rules", 1);
				$resources_type = get_post_meta($product_id, "_phive_booking_resources_type", 1);

				// Looping through the rule and assign the corresponding rule value given by customer
				foreach ($resources_pricing_rules as $key => $rule) {
					//107352 - Removed condition check which was skipping the execution when auto-assign enabled and resource type set to multiple
					if ($rule['ph_booking_resources_auto_assign'] == 'yes' && $resources_type != 'single') {
						// continue;
					}
					if ($resources_type == 'single') {
						if ($values['phive_booked_resources'] == $rule['ph_booking_resources_name']) {
							$resources_booking_data[] = array(
								'resource_label' => $rule['ph_booking_resources_name'],
								'resource_status' => 'yes'
							);
							wc_add_order_item_meta($item_id, $rule['ph_booking_resources_name'], 'yes');
						}
					} else {

						if (isset($values['phive_booked_resources'][$key])) {
							$resources_booking_data[] = array(
								'resource_label' => $rule['ph_booking_resources_name'],
								'resource_status' => $values['phive_booked_resources'][$key]
							);
							wc_add_order_item_meta($item_id, $rule['ph_booking_resources_name'], $values['phive_booked_resources'][$key]);
						}
					}

				}

				if (count($resources_booking_data) > 0) {
					wc_add_order_item_meta($item_id, 'ph_bookings_resources_booking_data', $resources_booking_data);
				}
			}

			//Disply Assets details with items
			if (array_key_exists('phive_booked_assets', $values) && !empty($values['phive_booked_assets'])) {

				$asset_settings = get_option('ph_booking_settings_assets', 1);
				$this->assets_rules = (isset($asset_settings['_phive_booking_assets']) && !empty($asset_settings['_phive_booking_assets'])) ? $asset_settings['_phive_booking_assets'] : array();
				$asset_label = get_post_meta($product_id, '_phive_booking_assets_label');
				$asset_label = empty($asset_label[0]) ? 'Asset' : $asset_label[0];
				$asset_name = $this->assets_rules[$values['phive_booked_assets']]['ph_booking_asset_name'];

				wc_add_order_item_meta($item_id, 'Assets', array($values['phive_booked_assets']));
				wc_add_order_item_meta($item_id, $asset_label, $asset_name);

				/*$resources_pricing_rules 							= get_post_meta( $product_id, "_phive_booking_assets_pricing_rules", 1 );

				// Looping through the rule and assign the corresponding rule value given by customer
				foreach ($resources_pricing_rules as $key => $rule) {

					//if( $rule['ph_booking_resources_auto_assign']=='yes' ){
					//	continue;
					//}

					if( isset($values['phive_booked_assets'][$key]) ){
						// wc_add_order_item_meta( $item_id,$rule['ph_booking_asset_id'],$values['phive_booked_assets'][$key] );


					}

				}*/
			}
			// 175970
			$display_settings = get_option('ph_bookings_display_settigns');
			$text_customisation = isset($display_settings['text_customisation']) ? $display_settings['text_customisation'] : array();
			if (array_key_exists('phive_book_from_date', $values)) {
				wc_add_order_item_meta($item_id, 'From', array($values['phive_book_from_date']));
				$booked_from_text = isset($text_customisation['booked_from_text']) && !empty($text_customisation['booked_from_text']) ? $text_customisation['booked_from_text'] : __("Booked From", 'bookings-and-appointments-for-woocommerce');
				wc_add_order_item_meta($item_id, __($booked_from_text, 'bookings-and-appointments-for-woocommerce'), Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format($values['phive_book_from_date']));
			}

			if (array_key_exists('phive_book_to_date', $values)) {
				wc_add_order_item_meta($item_id, 'To', array($values['phive_book_to_date']));
				$phive_booked_to_date = $values['phive_book_to_date'];
				if (($interval_period == 'minute') || ($interval_period == 'hour')) {
					$phive_booked_to_date = date('Y-m-d H:i', strtotime("+$interval $interval_period", strtotime($values['phive_book_to_date'])));
				} else {
					if ($interval_period == 'day') {
						$enable_per_night = get_post_meta($product_id, '_phive_book_charge_per_night', true);
						if ($enable_per_night == 'yes') {
							$book_to_date = date_create($values['phive_book_to_date']);
							$book_to_date->modify("-1 days");
							wc_update_order_item_meta($item_id, '_ph_book_to_date_with_night', array($book_to_date->format('Y-m-d')));
						}
					}
				}
				$booked_to_text = isset($text_customisation['booked_to_text']) && !empty($text_customisation['booked_to_text']) ? $text_customisation['booked_to_text'] : __("Booked To", 'bookings-and-appointments-for-woocommerce');
				wc_add_order_item_meta($item_id, __($booked_to_text, 'bookings-and-appointments-for-woocommerce'), Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format($phive_booked_to_date));
			}

			if (array_key_exists('phive_booked_price', $values)) {
				wc_add_order_item_meta($item_id, 'Cost', $values['phive_booked_price']);
			}
			if ($persons_as_booking == 'yes') {

				wc_add_order_item_meta($item_id, 'person_as_booking', array('yes'));

			}

			$required_confirmation = get_post_meta($cart_item['product_id'], '_phive_book_required_confirmation', 1);

			if ($required_confirmation === 'yes') {
				$is_confirm_required = 'true';
			} else {
				$is_confirm_required = 'false';
			}

			if ($required_confirmation == 'true') {
				$booking_status = 'requires-confirmation';
			} else {
				$booking_status = 'un-paid';
			}

			$booking_status_name = array(
				'paid' => __('Paid', 'bookings-and-appointments-for-woocommerce'),
				'un-paid' => __('Unpaid', 'bookings-and-appointments-for-woocommerce'),
				'canceled' => __('Cancelled', 'bookings-and-appointments-for-woocommerce'),
				'requires-confirmation' => __('Requires Confirmation', 'bookings-and-appointments-for-woocommerce')
			);

			if ($enable_buffer == 'yes') {
				$buffer_before_from = $this->phive_buffer_before_time($values['phive_book_from_date'], $buffer_period, $book_interval, $buffer_before, $buffer_after);

				$buffer_after_to = $this->phive_buffer_after_time($values['phive_book_from_date'], $values['phive_book_to_date'], $buffer_period, $book_interval, $buffer_before, $buffer_after, $product_id);
				switch ($interval_period) {
					case 'day':
						//Ticket-133368-buffer_time is not blocking correctly.
						$buffer_after_from = date("Y-m-d", strtotime("+1 day", strtotime($values['phive_book_to_date'])));

						$buffer_before_to = date("Y-m-d", strtotime("-1 day", strtotime($values['phive_book_from_date'])));
						break;
					case 'hour':
						$buffer_after_from = date("Y-m-d H:i", strtotime("+$interval $interval_period", strtotime($values['phive_book_to_date'])));

						$buffer_before_to = date("Y-m-d H:i", strtotime("-$interval $interval_period", strtotime($values['phive_book_from_date'])));
						break;
					case 'minute':
						$buffer_after_from = date("Y-m-d H:i", strtotime("+$interval $interval_period", strtotime($values['phive_book_to_date'])));

						$buffer_before_to = date("Y-m-d H:i", strtotime("-$interval $interval_period", strtotime($values['phive_book_from_date'])));
						break;
				}

				$asset_id = '';
				if ((isset($cart_item['phive_booked_assets'])) && (!empty($cart_item['phive_booked_assets']))) {
					$asset_id = $cart_item['phive_booked_assets'];
				}

				if ($buffer_before_from == '') {
					$buffer_before_to = '';
				}
				if ($buffer_after_to == '') {
					$buffer_after_from = '';
				}
				if ($persons_as_booking == 'yes') {
					$buffer_before_id = $this->phive_save_booking_buffer_info($product_id, $buffer_before_from, $buffer_before_to, array('yes'), $number_of_persons, 'yes', 'buffer-before', $asset_id);
					$buffer_after_id = $this->phive_save_booking_buffer_info($product_id, $buffer_after_from, $buffer_after_to, array('yes'), $number_of_persons, 'yes', 'buffer-after', $asset_id);
				} else {
					//Ticket-133376-buffer_time before booking is not blocking correctly.
					$buffer_before_id = $this->phive_save_booking_buffer_info($product_id, $buffer_before_from, $buffer_before_to, '', '', 'yes', 'buffer-before', $asset_id);
					$buffer_after_id = $this->phive_save_booking_buffer_info($product_id, $buffer_after_from, $buffer_after_to, '', '', 'yes', 'buffer-after', $asset_id);
				}

				$buffer_before_ids = array("$buffer_before_id");
				$buffer_after_ids = array("$buffer_after_id");

				wc_add_order_item_meta($item_id, 'buffer_before_id', $buffer_before_ids);
				wc_add_order_item_meta($item_id, 'buffer_after_id', $buffer_after_ids);

			}

			$taxable = 'taxable' === $product->get_tax_status();
			// Add line item meta
			$tax_item_id = '';
			if (wc_tax_enabled() && $taxable) {
				global $woocommerce;
				$countries = new WC_Countries();
				$base_country = WC()->countries->get_base_country();

				$order_taxes = $order->get_taxes();
				$tax_classes = WC_Tax::get_tax_classes();
				$classes_options = wc_get_product_tax_class_options();
				$show_tax_columns = count($order_taxes) === 1;

				$tax_slug = WC_Tax::get_tax_class_slugs();

				$incl_or_excl = get_option('woocommerce_prices_include_tax');

				$tax_based_on = get_option('woocommerce_tax_based_on');

				$arrayof = array();
				$arrayof['data'] = wc_get_product($product_id);
				$tax_class = $arrayof['data']->get_tax_class();
				$taxable = 'taxable' === $arrayof['data']->get_tax_status();
				$price_includes_tax = wc_prices_include_tax();
				$price = $booking_cost;
				$customer = $order->get_user();

				$tax_rates = $this->get_item_tax_rates($tax_class, $customer, $order);
				$label = '';
				$rate_id = '';
				$rate = '';
				foreach ($tax_rates as $key => $rate) {
					$rate_id = $key;
					$label = $tax_rates[$key]['label'];
					$rate = $tax_rates[$key]['rate'];
					break;
				}
				$total_taxes = WC_Tax::calc_tax($price, $tax_rates, $price_includes_tax);

				if ($price_includes_tax) {
					// Use unrounded taxes so we can re-calculate from the orders screen accurately later.
					$price = $price - array_sum($total_taxes);
					$booking_cost = $price;
					$price_includes_tax = 'yes';
				} else {
					$price_includes_tax = 'no';
				}

				if (!empty($label) && !empty($rate_id)) {
					$array_sum_of_total_tax = array_sum(array_values($total_taxes));
					$line_tax_data = array(
						'total' => array("$rate_id" => "$array_sum_of_total_tax"),
						'subtotal' => array("$rate_id" => "$array_sum_of_total_tax"),
					);

					if ($booking_order == "existing") {
						foreach ($order_taxes as $id => $tax_item) {
							$old_total_tax = wc_get_order_item_meta($id, 'tax_amount', 1);
							$new_total_tax = $old_total_tax + $array_sum_of_total_tax;
							wc_update_order_item_meta($id, 'tax_amount', $new_total_tax);

							if ($price_includes_tax == 'no') {
								$order_total = $order->get_total();
								$order->set_total($order_total + $array_sum_of_total_tax);
							}
						}
					} else {
						$tax_item_id = wc_add_order_item($order_id, array(
							'order_item_name' => $label,
							'order_item_type' => 'tax',
						));

						wc_add_order_item_meta($tax_item_id, 'label', $label);
						wc_add_order_item_meta($tax_item_id, 'rate_id', $rate_id);
						wc_add_order_item_meta($tax_item_id, 'rate_percent', $rate);
						wc_add_order_item_meta($tax_item_id, 'tax_amount', $array_sum_of_total_tax);
						wc_add_order_item_meta($tax_item_id, 'shipping_tax_amount', 0);
						wc_add_order_item_meta($tax_item_id, 'compound', '');
						$order->set_total($booking_cost + $array_sum_of_total_tax);

					}

					wc_add_order_item_meta($item_id, '_line_tax', $array_sum_of_total_tax);
					wc_add_order_item_meta($item_id, '_line_subtotal_tax', $array_sum_of_total_tax);
					wc_add_order_item_meta($item_id, '_line_tax_data', $line_tax_data);

					wc_update_order_item_meta($item_id, 'Cost', (array) $booking_cost);

					$order->set_prices_include_tax('yes' === get_option('woocommerce_prices_include_tax'));
				}

			}

			wc_add_order_item_meta($item_id, '_line_total', $booking_cost);
			wc_add_order_item_meta($item_id, '_line_subtotal', $booking_cost);
			wc_add_order_item_meta($item_id, '_qty', 1);
			wc_add_order_item_meta($item_id, '_tax_class', $product->get_tax_class());

			wc_add_order_item_meta($item_id, '_product_id', $product->get_id());
			wc_update_order_item_meta($item_id, 'booking_status', array($booking_status));
			wc_update_order_item_meta($item_id, __('Booking Status', 'bookings-and-appointments-for-woocommerce'), __($booking_status_name[$booking_status], 'bookings-and-appointments-for-woocommerce'));

			$order->save();

			do_action('ph_booking_google_calender_sync_for_admin_bookings', $order_id);
			// for product addon plugin
			do_action('ph_product_addon_add_to_order', $order_id, $product_id, $_REQUEST, $item_id);

			// for ph-deposits plugin to calculate deposit from admin booking
			do_action('ph_add_additional_order_item_meta_for_admin_bookings', $item_id, $order_id, $cart_item, $product->get_id(), $tax_item_id, $order);

			if ((isset($cart_item['phive_booked_assets'])) && (!empty($cart_item['phive_booked_assets']))) {
				// error_log("asset id : ".$cart_item['phive_booked_assets']);
				$asset_id = $cart_item['phive_booked_assets'];
				if (!empty($asset_id)) {
					$ph_cache_obj = new phive_booking_cache_manager();
					$ph_cache_obj->ph_unset_cache($asset_id);
				}
			}
		}

		$order = wc_get_order($order_id);

		$send_payment_email = isset($_REQUEST['send_payment_email']) ? $_REQUEST['send_payment_email'] : '';
		$this->send_payment_link_email($order_id, $order, $send_payment_email, $customer_id, $guest_email_id);
		wp_safe_redirect(admin_url('post.php?post=' . $order_id . '&action=edit'));
		exit;

	}


	protected function get_item_tax_rates($tax_class, $customer, $order)
	{
		if (!wc_tax_enabled()) {
			return array();
		}
		$item_tax_rates = $this->get_rates($tax_class, $customer, $order);

		return $item_tax_rates;
		// Allow plugins to filter item tax rates.
		// return apply_filters( 'woocommerce_cart_totals_get_item_tax_rates', $item_tax_rates, $item, $this->cart );
	}

	public static function get_rates($tax_class = '', $customer = null, $order = '')
	{
		$tax_class = sanitize_title($tax_class);
		$location = self::get_tax_location($tax_class, $customer, $order);
		$matched_tax_rates = array();
		if (count($location) === 4) {
			list($country, $state, $postcode, $city) = $location;

			$matched_tax_rates = WC_Tax::find_rates(
				array(
					'country' => $country,
					'state' => $state,
					'postcode' => $postcode,
					'city' => $city,
					'tax_class' => $tax_class,
				)
			);
		}

		// return apply_filters( 'woocommerce_matched_rates', $matched_tax_rates, $tax_class );
		return $matched_tax_rates;
	}

	public static function get_tax_location($tax_class = '', $customer = null, $order = '')
	{
		$location = array();
		if (is_null($customer) && WC()->customer) {
			$customer = WC()->customer;
		}
		$countries = new WC_Countries();

		$tax_based_on = get_option('woocommerce_tax_based_on');

		if (!empty($customer)) {
			// $location = $customer->get_taxable_address();
			// $location = self::get_taxable_address($customer, $countries);
			$tax_based_on = get_option('woocommerce_tax_based_on');

			if ('shipping' === $tax_based_on) {
				$address = $order->get_address('shipping');
				$country = $address['country'];
				if (!$country) {
					$tax_based_on = 'billing';
				}
			}

			if ('base' === $tax_based_on) {
				$country = WC()->countries->get_base_country();
				$state = WC()->countries->get_base_state();
				$postcode = WC()->countries->get_base_postcode();
				$city = WC()->countries->get_base_city();
			} elseif ('billing' === $tax_based_on) {
				$address = $order->get_address('billing');

				$country = $address['country'];
				$state = $address['state'];
				$postcode = $address['postcode'];
				$city = $address['city'];
			} else {
				$address = $order->get_address('shipping');

				$country = $address['country'];
				$state = $address['state'];
				$postcode = $address['postcode'];
				$city = $address['city'];
			}
			$location = array($country, $state, $postcode, $city);
		} else if (wc_prices_include_tax() || 'base' === get_option('woocommerce_default_customer_address') || 'base' === get_option('woocommerce_tax_based_on')) {
			$location = array(
				WC()->countries->get_base_country(),
				WC()->countries->get_base_state(),
				WC()->countries->get_base_postcode(),
				WC()->countries->get_base_city(),
			);
		}

		// return apply_filters( 'woocommerce_get_tax_location', $location, $tax_class, $customer );
		return $location;
	}

	public function get_taxable_address($customer, $countries)
	{
		$tax_based_on = get_option('woocommerce_tax_based_on');

		if ('base' === $tax_based_on) {
			$country = WC()->countries->get_base_country();
			$state = WC()->countries->get_base_state();
			$postcode = WC()->countries->get_base_postcode();
			$city = WC()->countries->get_base_city();
		} elseif ('billing' === $tax_based_on) {
			$country = $customer->get_billing_country();
			$state = $customer->get_billing_state();
			$postcode = $customer->get_billing_postcode();
			$city = $customer->get_billing_city();
		} else {
			$country = $customer->get_shipping_country();
			$state = $customer->get_shipping_state();
			$postcode = $customer->get_shipping_postcode();
			$city = $customer->get_shipping_city();
		}

		// return apply_filters( 'woocommerce_customer_taxable_address', array( $country, $state, $postcode, $city ) );
		return array($country, $state, $postcode, $city);
	}

	public function send_payment_link_email($order_id, $order, $send_email, $customer_id, $guest_email_id)
	{
		$from_email = empty($guest_email_id) ? get_user_meta($customer_id, 'billing_email', true) : $guest_email_id;

		if ($send_email && !empty($from_email)) {

			$status = 'pending_payment';
			global $current_lang;

			$current_lang = ph_wpml_language_switch_admin_email($order, '', 'order', '');

			if (!class_exists('Ph_WC_Email_Booking_Payment')) {
				$obj = include_once plugin_dir_path(PH_BOOKINGS_PLUGIN_FILE) . '/includes/emails/class-ph-booking-payment-for-customer.php';
				$obj->trigger($order_id, $from_email, $status, $order);
			} else {
				$obj = new Ph_WC_Email_Booking_Payment();
				$obj->trigger($order_id, $from_email, $status, $order);
			}
		}
	}
}
