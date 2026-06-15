<?php

/**
 * Init Search Widget
 */

class PH_Booking_Search {

	public $processed_products;

	/**
	 * Init Search Widget
	 */
	function __construct() {
		require_once plugin_dir_path( __FILE__ ) . 'class-ph-booking-search-widget.php';

		add_action( 'wp_enqueue_scripts', array( $this, 'ph_booking_scripts' ) );

		// Need to add the admin styles and scripts for the widget setting page only
		add_action( 'admin_print_styles-widgets.php', array( $this, 'ph_bookings_admin_style' ) );
		add_action( 'admin_print_scripts-widgets.php', array( $this, 'ph_admin_scripts' ) );

		$display_settings = get_option( 'ph_bookings_display_settigns' );

		if ( isset( $display_settings['ph_enable_search_widget'] ) && 'yes' === $display_settings['ph_enable_search_widget'] ) {
			add_action( 'widgets_init', array( $this, 'ph_load_widget' ) );
		}

		// Returns search params from a search session to autofill the calendar on product page.
		add_action( 'wp_ajax_ph_get_search_params', array( $this, 'ph_get_search_params' ) );
		add_action( 'wp_ajax_nopriv_ph_get_search_params', array( $this, 'ph_get_search_params' ) );

		add_action( 'wp_ajax_ph_handle_book_now_from_search', array( $this, 'ph_handle_book_now' ) );
		add_action( 'wp_ajax_nopriv_ph_handle_book_now_from_search', array( $this, 'ph_handle_book_now' ) );

		$this->processed_products = [];
		// Add search actions to search results page.
		// This will display book now button and view product button on search results page.
		add_filter( 'the_content', array( $this, 'ph_add_search_actions' ), 30, 1 );
		add_filter( 'wp_trim_excerpt', array( $this, 'ph_add_search_actions' ), 30, 1 );
	}

	// Handle book now click event from search results page.
	public function ph_handle_book_now() {

		$product_id = isset( $_POST['product_id'] ) ? $_POST['product_id'] : '';

		if ( WC()->cart && ! empty( $product_id ) ) {

			WC()->cart->add_to_cart( $product_id );

			wp_send_json_success(
				array(
					'cart_url' => wc_get_cart_url(),
				)
			);
		}

		die();
	}

	/**
	 * Display search actions.
	 *
	 * @param mixed $content
	 * @return mixed $content
	 */
	public function ph_add_search_actions( $content ) {

		// Do nothing if not on search page.
		if ( ! is_search() ) {
			return $content;
		}

		$product_id    = get_the_ID();
		$product_url   = get_permalink( $product_id ) . '?invoker=phbookingsearch';
		$search_params = array();
		
		// Check if this post has already been processed by this function
		if (in_array($product_id, $this->processed_products)) {

			return $content;
		}

		if ( WC()->session ) {
			$search_params = WC()->session->get( 'ph_bookings_search_params' );
		}

		// If no search parameters are provided, return the original content.
		if ( empty($search_params)) {

			return $content;
		}

		$product = wc_get_product( $product_id );

		if(!$product instanceof WC_Product_phive_booking)
		{
			return $content;
		}

		$booking_type		= $product->get_interval_type();
		$interval_period 	= $product->get_interval_period();
		$interval			= $product->get_interval();

		$book_from 	= $search_params['book_search_from'] ?? '';
		$book_to	= $search_params['book_search_to'] ?? '';

		// For fixed calendars other than month, building the to date manually.
		if ('fixed' === $booking_type && 'month' != $interval_period) {
			if (1 == $interval || 'day' != $interval_period) {
				$book_to = $book_from;
			} else {
				$modified_interval = 'day' === $interval_period ? $interval - 1 : $interval;

				$format = 'minute' === $interval_period ? 'Y-m-d H:i A' : 'Y-m-d';
				$book_to = date($format, strtotime("+$modified_interval $interval_period", strtotime($book_from)));
			}
		}

		// For fixed month calendar, building to date manually.
		if ('fixed' === $booking_type && 'month' === $interval_period) {
			
			$book_from	= date('Y-m', strtotime($book_from));

			if (1 == $interval) {
				$book_to = $book_from;
			} else {
				$modified_interval = $interval - 1;
				$book_to = date('Y-m', strtotime("+$modified_interval $interval_period", strtotime($book_from)));
			}
		}
		
		// For range calendars with time selection.
		if ($search_params['ph_book_search_filter_date_and_time'] && 'fixed' != $booking_type) { 
			$book_to = date('Y-m-d h:i A', strtotime("-$interval $interval_period", strtotime($book_to)));
			$book_from = date('Y-m-d h:i A', strtotime($book_from));
		}

		$booking_data = array(
			'book_from'       => str_replace('/', '-', $book_from),
			'book_to'         => str_replace('/', '-', $book_to),
			'persons_details' => $this->ph_build_participant_array(
				isset( $search_params['ph_book_search_participants'] ) ? $search_params['ph_book_search_participants'] : array(),
				$product_id
			),
		);

		$asset = null;

		// Check if asset is selected during search, else get auto assigned asset if enabled.
		if ( isset( $search_params['ph_search_asset_name'] ) && ! empty( $search_params['ph_search_asset_name'] ) ) {
			$asset = $search_params['ph_search_asset_name'];
		} else {
			$asset = $this->ph_check_get_auto_assigned_asset($product_id, $book_from, $book_to);
		}

		// Calculate the booking cost using the booking details to display on search results page.
		if ( ! empty( $asset ) ) {
			// Setting asset id for the product this will be used for calculation in the get_price functionality.
			$product->ph_set_asset_id( $asset );
		}

		$booking_cost         = $product->get_price(
			'view',
			array(
				$product_id => $booking_data,
			)
		);
		$widget_options       = get_option( 'widget_ph_booking_search_widget' );
		$display_settings     = get_option( 'ph_bookings_display_settigns' );
		$text_customisation   = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
		$book_now_button_text = isset( $text_customisation['book_now_button'] ) && ! empty( $text_customisation['book_now_button'] ) ? $text_customisation['book_now_button'] : __( 'Book Now', 'bookings-and-appointments-for-woocommerce' );

		// Check if any instances exist
		if ( ! is_array( $widget_options ) || empty( $widget_options ) ) {
			return $content; // No instances found
		}

		// Filter out non-instance elements (non-integer keys)
		$widget_instances = array_filter( $widget_options, 'is_array' );

		// Get the lastest instance
		$latest_instance = array_pop( $widget_instances );

		$search_action = '<div style="display:flex;flex-direction:column;gap:1rem;">';

		if ( ! empty( $booking_cost ) ) {
			$search_action .= '<b>' . wc_price( $booking_cost ) . '</b>';
		}

		$search_action .= '<div style="margin:10px 0px; display:flex;gap:1rem;">';

		if ( isset( $latest_instance['enable_view_product_button'] ) && $latest_instance['enable_view_product_button'] ) {
			$button_text    = isset( $latest_instance['view_product_button_text'] ) && ! empty( $latest_instance['view_product_button_text'] ) ? $latest_instance['view_product_button_text'] : __( 'View Product', 'bookings-and-appointments-for-woocommerce' );
			$search_action .= '<a href="' . esc_url( $product_url ) . '" class="button btn btn-lg btn-primary" target="_blank">' . $button_text . '</a>';
		}

		if ( isset( $latest_instance['enable_book_now_button'] ) && $latest_instance['enable_book_now_button'] ) {
			$search_action .= '<a href="" class="button btn btn-lg btn-primary ph_search_book_now" data-product-id="' . $product_id . '">' . $book_now_button_text . '</a>';
		}

		$search_action .= '</div>';
		$search_action .= '</div>';

		// Displaying the search actions befor the content.
		echo $search_action;

		// Mark this product ID as processed to prevent re-duplication if both hooks fire.
		$this->processed_products[] = $product_id;

		return $content;
	}

	/**
	 * Check and return asset if configured to auto assign.
	 *
	 * @param int $product_id
	 * @param mixed $from_date
	 * @param mixed $to_date
	 * @return string $chosen_asset
	 */
	private function ph_check_get_auto_assigned_asset($product_id, $from_date, $to_date) {

		$assets_enabled = get_post_meta( $product_id, '_phive_booking_assets_enable', 1 );
		$auto_assign	= get_post_meta( $product_id, '_phive_booking_assets_auto_assign', true);

		if ('yes' != $assets_enabled || !$auto_assign) {
			return;
		}

		if ( 'yes' == get_post_meta( $product_id, '_phive_book_charge_per_night', true ) ) {
			$to_date = strtotime( '-1 day', strtotime( $to_date ) );
		}

		$chosen_asset = Ph_Booking_Manage_Availability_Data::get_asset_id( 
			strtotime($from_date), 
			strtotime($to_date),
			$product_id,
			false
		);

		return $chosen_asset;
	}

	/**
	 * Build participant array for rate calculation.
	 *
	 * @param string $participants
	 * @return array $participants_array
	 */
	private function ph_build_participant_array( $participants, $product_id ) {

		$participants_array = array();
		$participants       = json_decode( stripslashes( $participants ), true );
		$participant_rules  = get_post_meta( $product_id, '_phive_booking_persons_pricing_rules', true );

		// Adjusting search params to form a proper structure.
		if ( ! empty( $participants ) && is_array( $participants ) ) {
			foreach ( $participant_rules as $index => $rule ) {
				foreach ( $participants as $participant ) {
					if ( $rule['ph_booking_persons_rule_type'] == $participant['rule'] ) {
						$participants_array[ $index ] = $participant['count'];
					}
				}
			}
		}

		return $participants_array;
	}

	/**
	 * Get search params from session.
	 */
	public function ph_get_search_params() {

		if ( WC()->session ) {

			// Enable session for non logged in users.
			if ( ! WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}

			wp_send_json_success(
				array( 'search_params' => WC()->session->get( 'ph_bookings_search_params' ) )
			);
		}
	}

	/**
	 * Linking Admin style Setting
	 */
	public function ph_bookings_admin_style() {
		wp_enqueue_style(
			'ph_bookings_wheeler_css',
			plugins_url( '/resources/css/wheelcolorpicker.css', PH_BOOKINGS_PLUGIN_FILE )
		);
	}

	/**
	 * Linking Jquery date and time picker Script  and style
	 */
	public function ph_booking_scripts() {

		wp_enqueue_script(
			'ph_booking_general_script2',
			plugins_url( '/resources/js/ph-booking-addon-general.js', __FILE__ ),
			array( 'jquery' )
		);

		wp_localize_script(
			'ph_booking_general_script2',
			'ph_booking_search_data',
			array(
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'home_url' => get_home_url(),
			)
		);

		wp_enqueue_style(
			'ph_booking_style2',
			plugins_url( '/resources/css/general-style.css', __FILE__ )
		);

		// Enqueue Moment.js as a dependency for Flatpickr
		wp_enqueue_script(
			'ph_moment_js', 
			'https://cdn.jsdelivr.net/npm/moment', 
			array(), 
			'2.29.4',
			true
		);

		// Enqueue Flatpickr CSS
		wp_enqueue_style(
			'ph_flatpickr_inbuild_css', 
			'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', 
			array(), 
			'4.6.13'
		);

		// Enqueue Flatpickr JS, with Moment.js as a dependency
		wp_enqueue_script(
			'ph_flatpickr_js', 
			'https://cdn.jsdelivr.net/npm/flatpickr', 
			array('ph_moment_js'), 
			'4.6.13',
			true
		);

		wp_enqueue_style(
			'ph_flatpickr_css',
			plugins_url( '/resources/css/flatpickr-calendar.css', __FILE__ )
		);
	}

	/**
	 * Linking admin Jquery date and time picker Script
	 */
	public function ph_admin_scripts() {
		wp_enqueue_script(
			'ph_booking_admin_widget_script',
			plugins_url( '/resources/js/ph-booking-addon-admin.js', __FILE__ ),
			array( 'jquery' )
		);

		wp_enqueue_script(
			'ph_bookings_wheeler_script',
			plugins_url( '/resources/js/jquery.wheelcolorpicker.js', PH_BOOKINGS_PLUGIN_FILE ),
			array( 'jquery' )
		);
		wp_localize_script(
			'ph_booking_admin_widget_script',
			'ph_booking_admin_widget_default',
			$this->ph_get_translated_string()
		);

		// Chosen Select.
		wp_enqueue_style(
			'ph_chosen_select_styles',
			'https://harvesthq.github.io/chosen/chosen.css',
		);

		wp_enqueue_script(
			'ph_chosen_select_js',
			'https://harvesthq.github.io/chosen/chosen.jquery.js',
			array( 'jquery' ),
		);
	}
	/**
	 *  WP Translator String
	 */
	public function ph_get_translated_string() {
		return array(
			'title_text'                         => __( 'Booking', 'bookings-and-appointments-for-woocommerce' ),
			'search_text'                        => __( 'Search', 'bookings-and-appointments-for-woocommerce' ),
			'clear_text'                         => __( 'Clear', 'bookings-and-appointments-for-woocommerce' ),
			'from_date_text'                     => __( 'From', 'bookings-and-appointments-for-woocommerce' ),
			'to_date_text'                       => __( 'To', 'bookings-and-appointments-for-woocommerce' ),
			'from_time_text'                     => __( 'Select a time', 'bookings-and-appointments-for-woocommerce' ),
			'to_time_text'                       => __( 'Select a time', 'bookings-and-appointments-for-woocommerce' ),
			'filter_asset_name_label'            => __( 'Select an asset', 'bookings-and-appointments-for-woocommerce' ),
			'filter_number_of_participant_label' => __( 'Participant Count', 'bookings-and-appointments-for-woocommerce' ),
			'reset_to_default_confirm_text'      => __( "This will reset all the settings to default. Click 'Ok' to proceed.", 'bookings-and-appointments-for-woocommerce' ),
		);
	}


	/**
	 * Register the Widget
	 */
	public function ph_load_widget() {
		register_widget( 'ph_booking_search_widget_addon' );
	}
}

new PH_Booking_Search();
