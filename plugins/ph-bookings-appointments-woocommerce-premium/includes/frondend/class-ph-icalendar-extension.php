<?php

/**
 * iCalendar.
 */

defined('ABSPATH') || exit;

class PH_Bookings_Icalendar_Extension {

	/**
	 * iCalendar (ICS) file generator.
	 *
	 * @var
	 */
	private $icaledar_generator;

	/**
	 * Event settings.
	 *
	 * @var array
	 */
	private $event_settings;

	/**
	 * ICS download filename.
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * iCalendar settings.
	 *
	 * @var array
	 */
	private $icalendar_settings;

	/**
	 * Download Button Text.
	 *
	 * @var string
	 */
	private $button_text;

	/**
	 * Set up iCalendar extension for thankyou page.
	 */
	public function __construct($icaledar_generator) {
		$this->icaledar_generator = $icaledar_generator;

		$this->filename = apply_filters('ph_bookings_icalendar_filename', 'ph_bookings_event_' . date('Y-m-d') . '.ics');

		$this->icalendar_settings = get_option('ph_booking_settings_icalendar', array());

		// Do nothing if iCalendar isn't enabled.
		if (!isset($this->icalendar_settings['enable_icalendar']) || 'no' === $this->icalendar_settings['enable_icalendar']) {
			return;
		}

		$this->button_text = !empty($this->icalendar_settings['icalendar_btn_text']) ? $this->icalendar_settings['icalendar_btn_text'] : __('Download .ics File', 'bookings-and-appointments-for-woocommerce');

		// Download button on thankyou page and my account page within order details table.
		if (isset($this->icalendar_settings['ics_download_for_customer']) && 'yes' === $this->icalendar_settings['ics_download_for_customer']) {
			
			add_action('woocommerce_order_details_after_order_table_items', array($this, 'ph_render_extended_view'), 10, 1);

			// Adding booking iCalendar
			add_action('woocommerce_email_order_details', array($this, 'ph_add_booking_ical_download_link_in_email'), 15, 4);
		}

		// Download button on edit order page under general column of order details.
		add_action('woocommerce_admin_order_data_after_order_details', array($this, 'ph_add_ics_download_button'));

		// Handle ICS download button click.
		add_action('wp_ajax_ph_bookings_download_ics_file', array($this, 'ph_download_ics_file'));

		// Bulk action.
		add_action('ph_bookings_bulk_icalendar_download', array($this, 'ph_bulk_download_ics'));

		$this->event_settings	= get_option('ph_booking_settings_icalendar', true);
	}

	/**
	 * Adds a download link for the iCalendar (ICS) file in the WooCommerce order email.
	 *
	 * This function checks if the order contains a bookable product, generates the iCalendar (ICS) file for the booking,
	 * and displays a download link in the WooCommerce email. It handles both plain text and HTML email formats.
	 *
	 * @param WC_Order $order           The WooCommerce order object.
	 * @param bool     $sent_to_admin   Whether the email is sent to the admin.
	 * @param bool     $plain_text      Whether the email is in plain text format.
	 * @param WC_Email $email           The WooCommerce email object.
	 */
	public function ph_add_booking_ical_download_link_in_email($order, $sent_to_admin, $plain_text, $email) {

		// Check if the order contains a bookable product
		if ($this->ph_order_contains_bookable_product($order)) {

			// Retrieve the iCalendar download link for the booking
			$ical_data = $this->ph_get_booking_ical_details($order);

			if (!$plain_text) {

				// Display the download link in HTML email format
				echo '<div>
						<p>' . __('Add bookings to iCalendar', 'bookings-and-appointments-for-woocommerce') . '</p>
						<p><a href="' . $ical_data . '" class="button" style="padding: 10px 15px; background-color: #0073aa; color: white; text-decoration: none; border-radius: 5px;">' . __($this->button_text,'bookings-and-appointments-for-woocommerce') . '</a></p>
					</div>';
			} else {

				// Display a plain text version of the download link
				echo __($this->button_text, 'bookings-and-appointments-for-woocommerce') . ': ' . $ical_data;
			}
		}
	}

	/**
	 * Generates the iCalendar (ICS) file for the bookings in the order and returns the URL to download the file.
	 *
	 * This function loops through the items in the order, generates booking events in iCalendar format (ICS),
	 * saves the ICS file in the WordPress uploads directory, and returns the URL for downloading the ICS file.
	 *
	 * @param WC_Order $order  The WooCommerce order object.
	 * @return string          The URL to download the ICS file.
	 */
	public function ph_get_booking_ical_details($order){

		$ical_data 			= '';

		$booking_events = array();
	
		// Loop through each order item and gather the ICS file data for each booking
		foreach ( $order->get_items() as $item_id => $item ) {
			$booking_events[] = $this->ph_download_ics_file( $item_id, 'bulk-action' );
		}
		
		// Flatten the nested array of booking events
		$flattened_booking_events = array_merge( ...$booking_events );
		
		// Generate the ICS file content based on the booking events
		$content = $this->icaledar_generator::ph_generate_ics_file( $flattened_booking_events );

		// Get the path and URL of the WordPress uploads directory
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['basedir'] . '/' . $this->filename;  // File path to save the ICS file
		$file_url = $upload_dir['baseurl'] . '/' . $this->filename;   // File URL to access the ICS file
		
		// Save the ICS content to the specified file path
		file_put_contents( $file_path, $content );

		// Check if the ICS file was successfully created
		if ( file_exists( $file_path ) ) {
			
			$ical_data = esc_url($file_url);
		}

		return $ical_data;
	}

	/**
	 * Download ICS in bulk.
	 *
	 * @param array $item_ids
	 */
	public function ph_bulk_download_ics($item_ids) {

		$item_ids 		= array_unique($item_ids);
		$booking_events = array();

		foreach ($item_ids as $item_id) {
			$booking_events[] = $this->ph_download_ics_file($item_id, 'bulk-action');
		}
		
		// To handle bulk, since order id's will repeat, nested array will be formed.
		$flattened_booking_events = array_merge(...$booking_events);
		$content = $this->icaledar_generator::ph_generate_ics_file($flattened_booking_events);

		ob_clean();
		
		// Set headers for proper download.
		header('Content-Type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $this->filename . '"');

		echo $content;
		exit();
	}

	/**
	 * Check if a "phive_booking" product exists in the WooCommerce order.
	 *
	 * This function loops through the items in a WooCommerce order to determine if
	 * any of the products are of the custom product type 'phive_booking'.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @return bool True if the order contains a 'phive_booking' product, false otherwise.
	 */
	function ph_order_contains_bookable_product( $order ) {
		
		// Ensure the input is a valid WC_Order object
		if ( $order instanceof WC_Order ) {
			
			// Retrieve the order items
			$order_items = $order->get_items();

			// Proceed only if there are items in the order
			if ( !empty( $order_items ) ) {

				// Loop through each item in the order
				foreach ( $order_items as $item ) {

					// Get the product ID from the order item
					$product_id = $item->get_product_id();

					// Get the product object using the product ID
					$product = wc_get_product( $product_id );

					// Check if the product object is a valid WooCommerce product
					if ( is_a( $product, 'WC_Product' ) ) {

						// Get the product type
						$product_type = $product->get_type();
						
						// Check if the product type is 'phive_booking'
						if ( 'phive_booking' === $product_type ) {

							return true;
						}
					}
				}
			}
		}

		// Return false if no bookable product is found in the order
		return false;
	}


	/**
	 * Render view.
	 *
	 * @param WC_Order
	 */
	public function ph_render_extended_view($order) {

		$is_booking_order = $this->ph_order_contains_bookable_product($order);

		if ($is_booking_order) {

			$order_id = $order->get_id();
			?>
			<tr>
				<td>
					<?php _e('Add bookings to iCalendar', 'bookings-and-appointments-for-woocommerce') ?>
				</td>
				<td>
					<?php echo sprintf(
						'<button id="ph-bookings-ics-download-btn" data-order-id="%s" data-item-id="" data-invoker="%s">%s</button><br/>',
						$order_id,
						'customer',
						__($this->button_text, 'ph-bookings-and-appointments-for-woocommerce')
					) ?>
				</td>

			</tr>
			<?php
		}
	}

	/**
	 * Download action button for All Bookings Page.
	 *
	 * @param int $item_id Item ID
	 */
	public function ph_add_ics_download_button($order) {

		$is_booking_order = $this->ph_order_contains_bookable_product($order);

		if ($is_booking_order) {
				
			$order_id = $order->get_id();

			echo "<div class='form-field'><h3>" . __('Add Bookings to iCalendar', 'bookings-and-appointments-for-woocommerce') . "</h3>";
			echo sprintf('<p class="form-field form-field-wide"><button id="ph-bookings-ics-download-btn" class="button button-primary" data-order-id="%s" data-item-id="" data-invoker="%s">%s</button></p>', $order_id, 'edit-order', __('Download iCal File', 'bookings-and-appointments-for-woocommerce'));
			echo "</div>";
		}
	}

	/**
	 * Handle download button click.
	 *
	 * @param int $item_id
	 * @param string $invoked_from
	 */
	public function ph_download_ics_file($item_id, $invoked_from = '') {

		$order_id		= (isset($_POST) && isset($_POST['orderId']) && !empty($_POST['orderId']) ? $_POST['orderId'] : '');
		$item_id		= !empty($item_id) ? $item_id : (isset($_POST) && isset($_POST['itemId']) && !empty($_POST['itemId']) ? $_POST['itemId'] : '');
		$invoker		= isset($_POST) && isset($_POST['invoker']) && !empty($_POST['invoker']) ? $_POST['invoker'] : '';

		// We don't have order_id when hooking into `woocommerce_after_order_itemmeta` hence retrieving from item_id.
		if (empty($order_id)) {
			$order_id = wc_get_order_id_by_order_item_id($item_id);
		}

		$booking_details	= array();
		$order				= wc_get_order($order_id);
		$order_items		= $order->get_items();
		
		// $invoked from will be passed from bulk action & $invoker is set in ajax request from the source of click event.
		$invoked_from = !empty($invoked_from) ? $invoked_from : $invoker;

		foreach ($order_items as $item) {

			// To avoid adding all the bookings in an order to the event when downloading from bulk action and all bookings page.
			if ($item_id != $item->get_id() && in_array($invoked_from, array('all-bookings', 'bulk-action'))) {
				continue;
			}

			$product = wc_get_product($item->get_product_id());

			if (!$product instanceof WC_Product_phive_booking) {
				continue;
			}

			$args = array(
				'product'	=> $product,
				'item'		=> $item
			);

			// Booking Status.
			$booking_status = PH_Bookings_Order_Item_Handler::ph_get_booking_status($args);

			// Booking Cost.
			$booking_cost	= __("Booking Cost: ", 'bookings-and-appointments-for-woocommerce') . PH_Bookings_Order_Item_Handler::ph_get_booking_cost($args);

			// Participants.
			$participants		= PH_Bookings_Order_Item_Handler::ph_get_participants($args);
			$participant_data 	= $this->ph_prepare_concatenated_data($participants);

			// Assets.
			$assets		= PH_Bookings_Order_Item_Handler::ph_get_assets($args);
			$asset_data	= $this->ph_prepare_concatenated_data($assets);

			// Resources.
			$resources		= PH_Bookings_Order_Item_Handler::ph_get_resources($args);
			$resource_data	= $this->ph_prepare_concatenated_data($resources);

			// Customer details.
			$customer_details = $this->ph_get_customer_details_from_order($order);

			$billing_addres = $this->ph_get_billing_address_from_order($order);

			$summary_args = array(
				'resource'			=> $resource_data,
				'participant'		=> $participant_data,
				'customer_name'		=> $customer_details['first_name'] . ' ' . $customer_details['last_name'],
				'customer_phone'	=> $customer_details['phone'],
				'customer_email'	=> $customer_details['email'],
				'product_name'		=> $product->get_name(),
				'booking_status' 	=> $booking_status,
				'asset'				=> $asset_data,
				'booking_cost' 		=> $booking_cost,
				'billing_address'	=> $billing_addres,
				'booking_notes'		=> PH_Bookings_Order_Item_Handler::ph_get_booking_notes($args),
				'location'			=> $item->get_meta('Location')
			);

			$event_summary = $this->ph_generate_event_summary($summary_args, $invoked_from);

			$event_description = $this->ph_generate_event_description($summary_args, $invoked_from);

			$from 	= ph_maybe_unserialize($item->get_meta('From'));
			$to		= ph_maybe_unserialize($item->get_meta('To'));

			// Setting ending, same as From, when To is empty.
			$to = empty($to) ? $from : $to;

			$interval_details 	= $item->get_meta('_phive_booking_product_interval_details', true);
			$interval_format	= is_array($interval_details) && isset($interval_details['interval_format']) ? $interval_details['interval_format'] : $product->get_interval_period();
			$interval      		= is_array($interval_details) && isset($interval_details['interval']) ? $interval_details['interval'] : $product->get_interval();

			if (!in_array($interval_format, array('day', 'month'))) {
				$to = date('Y-m-d H:i', strtotime("+$interval $interval_format", strtotime($to)));
			} else {
				$to = date('Y-m-d H:i', strtotime("+1 day", strtotime($to)));
			}

			$booking_details[] = array(
				'summary'		=> $event_summary,
				'description'	=> $event_description,
				'from'			=> $from,
				'to' 			=> $to,
			);
		}

		if ('bulk-action' === $invoked_from) {
			return apply_filters('ph_bookings_icalendar_event_details', $booking_details, $order_id, $item_id, $invoked_from);
		}

		$booking_details = apply_filters('ph_bookings_icalendar_event_details', $booking_details, $order_id, $item_id, $invoked_from);

		$content = $this->icaledar_generator::ph_generate_ics_file($booking_details);

		echo json_encode(
			array(
				'content' => $content,
				'filename'	=> $this->filename
			)
		);
		wp_die();
	}

	/**
	 * Generate event summary.
	 *
	 * @param array $args
	 * @param string $invoker
	 */
	private function ph_generate_event_summary($args, $invoker) {

		$event_summary	= isset($this->event_settings['icalendar_summary']) && !empty($this->event_settings['icalendar_summary'])
			? $this->event_settings['icalendar_summary']
			: '[PRODUCT_NAME]([BOOKING_STATUS])';

		// Map placeholders to their respective values
		$mapping = array(
			'[PRODUCT_NAME]'	=> isset($args['product_name']) ? $args['product_name'] : '',
			'[BOOKING_STATUS]' 	=> isset($args['booking_status']) ? $args['booking_status'] : '',
			'[PRODUCT_NAME]' 	=> isset($args['product_name']) ? $args['product_name'] : '',
			'[RESOURCE]' 		=> isset($args['resource']) ? $args['resource'] : '',
			'[PARTICIPANT]' 	=> isset($args['participant']) ? $args['participant'] : '',
			'[ASSET]' 			=> isset($args['asset']) ? $args['asset'] : '',
			'[CUSTOMER_NAME]'	=> isset($args['customer_name']) ? $args['customer_name'] : '',
		);

		if ('customer' === $invoker) {
			$event_summary = '[PRODUCT_NAME] ([BOOKING_STATUS])';
		}

		$event_summary = strip_tags(
			str_replace(
				array_keys($mapping),
				array_values($mapping),
				$event_summary
			)
		);

		return $event_summary;
	}

	/**
	 * Generate event description.
	 *
	 */
	private function ph_generate_event_description($args, $invoker) {

		$event_description_template 	= isset($this->event_settings['icalendar_description']) && !empty($this->event_settings['icalendar_description'])
			? $this->event_settings['icalendar_description']
			: "Customer Details\n[CUSTOMER_NAME]\n[CUSTOMER_PHONE]\n[CUSTOMER_EMAIL]\nBooking Details\n[BOOKING_COST]\n[PARTICIPANT]\n[ASSET]\n[RESOURCE]";

		// Map placeholders to their respective values
		$mapping = array(
			'[PRODUCT_NAME]'	=> isset($args['product_name']) ? $args['product_name'] : '',
			'[BOOKING_COST]'	=> isset($args['booking_cost']) ? $args['booking_cost'] : '',
			'[CUSTOMER_NAME]'	=> isset($args['customer_name']) ? $args['customer_name'] : '',
			'[BOOKING_STATUS]'	=> isset($args['booking_status']) ? $args['booking_status'] : '',
			'[PARTICIPANT]'		=> isset($args['participant']) ? $args['participant'] : '',
			'[ASSET]'			=> isset($args['asset']) ? $args['asset'] : '',
			'[RESOURCE]' 		=> isset($args['resource']) ? $args['resource'] : '',
			'[BILLING_ADDRESS]'	=> isset($args['billing_address']) ? $args['billing_address'] : '',
			'[BOOKING_NOTES]'	=> isset($args['booking_notes']) ? $args['booking_notes'] : '',
			'[CUSTOMER_PHONE]'	=> isset($args['customer_phone']) ? $args['customer_phone'] : '',
			'[CUSTOMER_EMAIL]'	=> isset($args['customer_email']) ? $args['customer_email'] : '',
		);

		if ('customer' === $invoker) {
			$event_description_template = array(
				'[BOOKING_COST]',
				'[PARTICIPANT]',
				'[ASSET]',
				'[RESOURCE]'
			);

			$event_description_parts = [];

			foreach ($event_description_template as $placeholder) {
				if (!empty($mapping[$placeholder])) {
					$event_description_parts[] = $mapping[$placeholder];
				}
			}

			$event_description = strip_tags(implode('\n', $event_description_parts));

			return $event_description;
		}

		$event_description = strip_tags(
			str_replace(
				array_keys($mapping),
				array_values($mapping),
				$event_description_template
			)
		);

		return $event_description;
	}

	/**
	 * Concatenate key value from the given array.
	 * Append new line after each element.
	 *
	 * @param array $data_set
	 * 
	 * @return string $concatenated_string
	 */
	private function ph_prepare_concatenated_data($data_set) {

		$concatenated_data = '';
		$count = count($data_set);

		foreach ($data_set as $index => $data) {
			$key = key($data);
			$concatenated_data .= $key . ': ' . $data[$key];

			if ($index < $count - 1) {
				$concatenated_data .= "\\n";
			}
		}

		return $concatenated_data;
	}

	/**
	 * Get billing address.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private function ph_get_billing_address_from_order($order) {
		
		$billing_addres_parts = array();
		$billing_addres = $order->get_address();

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

		return implode("\n", $billing_addres_parts);
	}

	/**
	 * Get customer details.
	 *
	 * @param WC_Order $order
	 *
	 * @return array $customer_details
	 */
	private function ph_get_customer_details_from_order($order) {

		$customer_details = array();

		$billing_addres = $order->get_address();

		$customer_details = array(
			'phone'			=> $billing_addres['phone'],
			'email'			=> $billing_addres['email'],
			'first_name'	=> $billing_addres['first_name'],
			'last_name'		=> $billing_addres['last_name']
		);

		return $customer_details;
	}
}
