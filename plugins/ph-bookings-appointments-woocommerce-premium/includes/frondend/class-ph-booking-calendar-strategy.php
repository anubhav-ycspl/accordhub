<?php
class phive_booking_calendar_strategy {

	/**
	 * @var $first_availability
	 */
	private $first_availability;

	/**
	 * @var $last_availability
	 */
	private $last_availability;

	/**
	 * @var $availability_rules
	 */
	private $availability_rules;

	/**
	 * @var $asset_obj
	 */
	public $asset_obj;

	/**
	 * @var $asset_id
	 */
	public $asset_id;

	/**
	 * @var $wp_time_format
	 */
	public $wp_time_format;

	/**
	 * @var $display_booking_capacity
	 */
	public $display_booking_capacity;

	/**
	 * @var $booking_pId
	 */
	public $booking_pId;

	/**
	 * @var $booked_dates_set
	 */
	public $booked_dates_set;

	/**
	 * @var $called_when_clicked_to_date
	 */
	public $called_when_clicked_to_date;

	/**
	 * @var $product
	 */
	public $product;

	/**
	 * @var $interval_period
	 */
	public $interval_period;

	/**
	 * @var $assets_enabled
	 */
	public $assets_enabled;

	/**
	 * @var $interval
	 */
	public $interval;

	/**
	 * @var $unavailable_array
	 */
	public $unavailable_array;

	/**
	 * @var $interval_type
	 */
	public $interval_type;

	/**
	 * @var $assets_auto_assign
	 */
	public $assets_auto_assign;

	/**
	 * @var @zone
	 */
	public $zone;

	/**
	 * @var $shop_opening_time
	 */
	public $shop_opening_time;

	/**
	 * @var $shop_closing_time
	 */
	public $shop_closing_time;

	/**
	 * @var $persons_pricing_rules
	 */
	public $persons_pricing_rules;

	/**
	 * @var $resources_pricing_rules
	 */
	public $resources_pricing_rules;

	/**
	 * @var $additional_notes_label
	 */
	public $additional_notes_label;

	/**
	 * @var $assets_label
	 */
	public $assets_label;

	/**
	 * @var $assets_pricing_rules
	 */
	public $assets_pricing_rules;

	/**
	 * @var $assets_rules
	 */
	public $assets_rules;

	/**
	 * @var $phive_enable_buffer
	 */
	public $phive_enable_buffer;

	/**
	 * @var $buffer_period
	 */
	public $buffer_period;

	/**
	 * @var $buffer_before_time
	 */
	public $buffer_before_time;

	/**
	 * @var $buffer_after_time
	 */
	public $buffer_after_time;

	/**
	 * @var $fixed_availability_to
	 */
	public $fixed_availability_to;

	/**
	 * @var $fixed_availability_from
	 */
	public $fixed_availability_from;

	/**
	 * @var $first_availability_interval_period
	 */
	public $first_availability_interval_period;

	/**
	 * @var $last_availability_interval_period
	 */
	public $last_availability_interval_period;

	/**
	 * @var $unavailable_default
	 */
	public $unavailable_default;

	/**
	 * @var $booked_dates
	 */
	public $booked_dates;

	/**
	 * @var $allowd_per_slot
	 */
	public $allowd_per_slot;

	/**
	 * @var $restrict_start_day
	 */
	public $restrict_start_day;

	/**
	 * @var $booking_start_days
	 */
	public $booking_start_days;

	/**
	 * @var $first_availability_type
	 */
	public $first_availability_type;

	/**
	 * @var $cancel_interval
	 */
	public $cancel_interval;

	/**
	 * @var $cancel_interval_period
	 */
	public $cancel_interval_period;

	/**
	 * @var $person_enabled
	 */
	public $person_enabled;

	/**
	 * @var $reources_enabled
	 */
	public $reources_enabled;

	/**
	 * @var $additional_notes_enabled
	 */
	public $additional_notes_enabled, $is_add_notes_mandatory;

	public function __construct( $product_id = '' ) {
		// In case of class object is created from Ajax, need to rest all product properties.
		if ( ! empty( $product_id ) ) {
			$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product_id );        // WPML Compatibility
			$this->phive_set_product_properties( $product_id );
		}

		$this->asset_id                 = '';
		$this->wp_time_format           = get_option( 'time_format' );
		$this->display_booking_capacity = get_post_meta( $product_id, '_phive_display_bookings_capacity', true );
		$this->booking_pId              = $product_id;
		// for timezone addon
		do_action( 'ph_booking_set_calendar_timezone' );
		add_filter( 'ph_booking_calendar_start_date', array( $this, 'get_first_available_date' ), 10, 3 );

		$this->booked_dates_set            = 0;
		$this->called_when_clicked_to_date = 0;
	}

	/**
	 * Render whole calendar html
	 */
	public function output_calender( $product = '' ) {

		if ( empty( $product ) ) {
			global $product;
		} else {
			$this->product = $product;
			global $product;
			$product = $this->product;
		}
		$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product->get_id() );

		$default_product = wc_get_product( $product_id );
		$interval_period = $default_product->get_interval_period();
		switch ( $interval_period ) {
			case 'hour':
			case 'minute':
				include 'html-ph-booking-timepicker.php';
				break;

			case 'month':
				include 'html-ph-booking-monthpicker.php';
				break;

			default:
				include 'html-ph-booking-datepicker.php';
				break;
		}
	}

	/**
	 * Reload the calendar (Probably while changing the asset)
	 */
	public function ph_reload_calendar( $start_date, $end_date = '', $product_id = '', $asset_id = false ) {
		switch ( $this->interval_period ) {
			case 'hour':
			case 'minute':
				// $available_dates_array = $this->get_available_date_for_time($start_date, $end_date, $product_id, $asset_id);

				$return = $this->phive_generate_days_for_period( $start_date, $end_date, $product_id, $asset_id, 'time-picker' );
				break;

			case 'month':
				$return = $this->phive_generate_month_for_period( $start_date, $end_date, $product_id, $asset_id );
				break;

			case 'day':
			default:
				$return = $this->phive_generate_days_for_period( $start_date, $end_date, $product_id, $asset_id, $calendar_for = 'date-picker' );
				break;
		}
		return $return;
	}

	/**
	 * Output the days for days calender
	 *
	 * @param $start_date  string
	 * @param $end_date  string
	 * @param $product_id  string
	 * @return string
	 */
	public function phive_generate_days_for_period( $start_date, $end_date = '', $product_id = '', $asset_id = false, $calendar_for = '' ) {
		$end_date = ( empty( $end_date ) ) ? strtotime( '+1 month', strtotime( $start_date ) ) : strtotime( $end_date );
		if ( $this->assets_enabled == 'yes' ) {
			$this->asset_obj = $this->get_asset_obj( $asset_id, $start_date );
		}

		if ( empty( $product_id ) ) {
			global $product;
			$product_id = $product->get_id();
			$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product->get_id() );
		}
		$display_booking_capacity = get_post_meta( $product_id, '_phive_display_bookings_capacity', true );
		$remainng_bokkings_text   = get_post_meta( $product_id, '_phive_remainng_bokkings_text', true );
		$remainng_bokkings_text   = ph_wpml_translate_single_string( 'Remaining_Bookings_Text', $remainng_bokkings_text );
		$remainng_bokkings_text   = ! empty( $remainng_bokkings_text ) ? '%s ' . $remainng_bokkings_text : '(%s left)';
		$auto_select_min_booking  = get_post_meta( $product_id, '_phive_auto_select_min_booking', 1 );

		$fully_booked_indicator			= get_post_meta( $product_id, '_ph_display_bookings_fully_booked_indicator', true );
		$fully_booked_indicator_text	= get_post_meta( $product_id, '_ph_booking_fully_booked_indicator_text', true );

		$min_allowed_booking = get_post_meta( $product_id, '_phive_book_min_allowed_booking', 1 );
		$min_allowed_booking = empty( $min_allowed_booking ) ? 1 : $min_allowed_booking;
		if ( empty( $this->interval ) ) {
			$this->interval = 1; // Defaulting interval to 1
		}

		// if fixed interval, in case of booking last days of month, want to display days of next month to complete a period
		if ( $calendar_for != 'time-picker' ) {
			// Support Customise Duration Addon
			$min_allowed_booking = apply_filters( 'ph_booking_min_allowed_booking_customise', $min_allowed_booking, $product_id, $start_date );
			
			if ( $this->interval > 1 || $min_allowed_booking > 1 ) {
				$end_date            = strtotime( date( 'Y-m-d', strtotime( '+' . ( ( $min_allowed_booking * $this->interval ) - 1 ) . ' day', $end_date ) ) );
			}
			$end_date = apply_filters( 'ph_booking_end_date_for_day_booking', $end_date, $product_id, $start_date, $this->interval, $min_allowed_booking );
		}
		$day_order      = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		$day_order      = apply_filters( 'ph_bookings_calendar_days_order', $day_order, $product_id );
		$callender_days = '<div class="ph-calendar-overlay" id="ph-calendar-overlay" style="display:none"></div>';

		// Align date to print under corresponding week day name
		foreach ( $day_order as $day ) {
			if ( $day == strtolower( date( 'l', strtotime( $start_date ) ) ) ) {
				break;
			}
			$callender_days .= '<li class="ph-calendar-date"></li>';
		}

		$curr_date          = strtotime( $start_date );
		$i                  = 1;
		$block_num          = 1;
		$html_input_bock_no = '';
		$booking_capacity   = null;
		while ( $curr_date < $end_date ) {
			$css_classes = array( 'ph-calendar-date' );

			// 40175
			$css_classes = apply_filters( 'ph_add_classes_to_callendar_dates', $css_classes, $curr_date, $product_id );

			$available_slot = $this->get_number_of_available_slot( $curr_date, $product_id, $asset_id );
			// if today.
			if ( $curr_date == strtotime( current_time( 'Y-m-d' ) ) ) {
				$css_classes[] = 'today';
			}
			// if Past date.
			if ( $curr_date < strtotime( current_time( 'Y-m-d' ) ) ) {
				$css_classes[] = 'booking-disabled';
				$css_classes[] = 'de-active';
			}

			// 129889-allow past booking
			$css_classes = apply_filters( 'ph_bookings_book_for_past_days', $css_classes, $product_id, $curr_date, $this->interval_period, $this->shop_opening_time, $this->shop_closing_time );

			// if set rule not available in availability table
			if ( ! $this->is_available( $curr_date, '', $product_id, $calendar_for ) || ! $this->is_asset_available( $curr_date, $calendar_for ) ) {
				$css_classes[] = 'de-active';
				$css_classes[] = 'not-available';
			}
			if ( $calendar_for == 'time-picker' && isset( $this->unavailable_array ) ) {
				if ( in_array( date( 'Y-m-d', $curr_date ), $this->unavailable_array ) ) {
					$css_classes[] = 'de-active';
					$css_classes[] = 'not-available';
				}
			}
			if ( $calendar_for != 'time-picker' ) {
				// if booking slot is full.
				// if( $this->is_booked_date( $curr_date, $product_id ) || ($available_slot=='0') ){
				// $css_classes[] = 'booking-full';
				// $css_classes[] = 'de-active';
				// }

				// is_booked_date and $available_slot working the same way, it will take extra loading time if both are applied.
				if ( ( $available_slot == '0' ) ) {
					$css_classes[] = 'booking-full';
					$css_classes[] = 'de-active';
				}

				// if there is no slot for book whole period (Case of Min booking set).
				if ( ! $this->is_bookable( $curr_date, '1 day', $product_id, $calendar_for ) ) {
					$css_classes[] = 'non-bookable-slot';
				}

				// if date of next month (Case of Fixed Interval).
				if ( ( date( 'm', $curr_date ) != date( 'm', strtotime( $start_date ) ) ) ) {
					$css_classes[] = 'booking-disabled';
					$css_classes[] = 'ph-next-month-date';
					if ( $auto_select_min_booking != 'yes' && $this->interval_type == 'customer_choosen' ) {
						$css_classes[] = 'ph-no-auto-select';
					}
				}

				// if non-startable days
				if ( ! in_array( date( 'N', $curr_date ), $this->get_booking_start_days( $product_id ) ) ) {
					$css_classes[] = 'not-startable';
				}
				// If interval is set, segregate whole dates as blocks of intervals
				if ( $this->interval > 0 ) {
					$html_input_bock_no = '<input type="hidden" class="callender-date" value="' . $block_num . '"/>';
					if ( $i % $this->interval == 0 ) {
						++$block_num;
					}
				}

				$css_classes_in_text = implode( ' ', array_unique( $css_classes ) );

				$non_title_classes = array( 'booking-disabled', 'booking-full', 'not-available' );
				if ( array_diff( $non_title_classes, $css_classes ) != $non_title_classes ) {

					$fully_booked_display_text = '';
					
					if ( $fully_booked_indicator == 'yes' && in_array('booking-full', $css_classes)) {
						$fully_booked_display_text = '<span class="ph_bookings_capacity" style="font-size:12px;"><br>' . sprintf( __( $fully_booked_indicator_text, 'bookings-and-appointments-for-woocommerce' ) ) . '</span>';
					}

					$callender_days .= '<li class="' . $css_classes_in_text . '" data-max="' . $available_slot . '" data-title="' . $available_slot . '"> ' . $html_input_bock_no . ' <input type="hidden" class="callender-full-date"  value="' . date( 'Y-m-d', $curr_date ) . '"><span class="ph_calendar_day">' . date( 'd', $curr_date ) . '</span>' . $fully_booked_display_text . '</li>';
				} else {
					if ( $display_booking_capacity == 'yes' ) {
						$booking_capacity = '<span class="ph_bookings_capacity" style="font-size:12px;"><br>' . sprintf( __( $remainng_bokkings_text, 'bookings-and-appointments-for-woocommerce' ), $available_slot ) . '</span>';
					}
					$callender_days .= '<li class="' . $css_classes_in_text . '" data-max="' . $available_slot . '" data-title="' . $available_slot . '" title="' . apply_filters( 'ph_booking_availability_title', '', $available_slot ) . '"> ' . $html_input_bock_no . ' <input type="hidden" class="callender-full-date"  value="' . date( 'Y-m-d', $curr_date ) . '"><span class="ph_calendar_day">' . date( 'd', $curr_date ) . '</span>' . $booking_capacity . '</li>';
				}
			} else {
				// 129992-restrict startday for timepicker
				$css_classes     = apply_filters( 'ph_bookings_restrict_start_days_for_timepicker', $css_classes, $product_id, $curr_date );
				$css_classes     = implode( ' ', array_unique( $css_classes ) );
				$callender_days .= '<li class="' . $css_classes . '"> ' . $html_input_bock_no . ' <input type="hidden" class="callender-full-date"  value="' . date( 'Y-m-d', $curr_date ) . '"><span class="ph_calendar_day">' . date( 'd', $curr_date ) . '</span></li>';
			}
			$curr_date = strtotime( date( 'Y-m-d', strtotime( '+1 day', $curr_date ) ) );
			++$i;
		}
		return $callender_days;
	}

	/**
	 * Output the months for month calender
	 *
	 * @param $start_date  string
	 * @param $end_date  string
	 * @param $product_id  string
	 * @return string
	 */
	public function phive_generate_month_for_period( $start_date, $end_date = '', $product_id = '', $asset_id = false ) {
		if ( empty( $product_id ) ) {
			global $product;
			$product_id = $product->get_id();
			$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product->get_id() );
		}

		$calendar_for = get_post_meta( $product_id, '_phive_book_interval_period', 1 );

		$display_booking_capacity = get_post_meta( $product_id, '_phive_display_bookings_capacity', true );
		$remainng_bokkings_text   = get_post_meta( $product_id, '_phive_remainng_bokkings_text', true );
		$remainng_bokkings_text   = ph_wpml_translate_single_string( 'Remaining_Bookings_Text', $remainng_bokkings_text );
		$remainng_bokkings_text   = ! empty( $remainng_bokkings_text ) ? '%s ' . $remainng_bokkings_text : '(%s left)';

		$fully_booked_indicator			= get_post_meta( $product_id, '_ph_display_bookings_fully_booked_indicator', true );
		$fully_booked_indicator_text	= get_post_meta( $product_id, '_ph_booking_fully_booked_indicator_text', true );

		$end_date         = ( empty( $end_date ) ) ? strtotime( '+3 year', strtotime( $start_date ) ) : strtotime( $end_date );
		$booking_capacity = null;

		if ( $this->assets_enabled == 'yes' ) {
			$this->asset_obj = $this->get_asset_obj( $asset_id, $start_date );
		}

		$callender_days = '<div id="ph-calendar-overlay" style="display:none"></div>';

		$curr_date = strtotime( $start_date );
		while ( $curr_date < $end_date ) {
			$css_classes    = array( 'ph-calendar-date' );
			$available_slot = $this->get_number_of_available_slot( $curr_date, $product_id, $asset_id );

			// if( $this->is_booked_date( $curr_date, $product_id ) || ($available_slot==0) ){
			// $css_classes[] = 'booking-full';
			// $css_classes[] = 'de-active';
			// }

			// is_booked_date and $available_slot working the same way, it will take extra loading time if both are applied.
			if ( ( $available_slot == '0' ) ) {
				$css_classes[] = 'booking-full';
				$css_classes[] = 'de-active';
			}

			if ( ! $this->is_available( $curr_date, '', $product_id, $calendar_for ) || ! $this->is_asset_available( $curr_date ) ) { // if set rule not available in availability table
				$css_classes[] = 'de-active';
				$css_classes[] = 'not-available';
			}
			if ( ! $this->is_bookable( $curr_date, '1 month', $product_id ) ) { // if there is no slot for book whole period.
				$css_classes[] = 'non-bookable-slot';
			}

			// The ph_wp_date function default zone is UTC, so it has been converted accordingly.
			$start_date_object   = new DateTime( date("Y-m-d",$curr_date), new DateTimeZone( 'UTC' ) );

			$utc_curr_date = $start_date_object->getTimestamp();

			$css_classes_in_text = implode( ' ', array_unique( $css_classes ) );
			$non_title_classes   = array( 'booking-disabled', 'booking-full', 'not-available' );
			if ( array_diff( $non_title_classes, $css_classes ) != $non_title_classes ) {
				
				$fully_booked_display_text = '';
					
				if ($fully_booked_indicator == 'yes' && in_array('booking-full', $css_classes)) {

					$fully_booked_display_text = '<span class="ph_bookings_capacity" style="font-size:12px;"><br>' . sprintf( __( $fully_booked_indicator_text, 'bookings-and-appointments-for-woocommerce' ) ) . '</span>';
				}

				$callender_days .= '<li class="' . $css_classes_in_text . '"> <input type="hidden" class="callender-full-date" value="' . date( 'Y-m', $curr_date ) . '">' . ph_wp_date( 'M', $utc_curr_date ) . '<br/>' . ph_wp_date( 'Y', $utc_curr_date ) . $fully_booked_display_text . '</li>';
			} else {
				if ( $display_booking_capacity == 'yes' ) {
					$booking_capacity = '<span class="ph_bookings_capacity" style="font-size:12px;"><br>' . sprintf( __( $remainng_bokkings_text, 'bookings-and-appointments-for-woocommerce' ), $available_slot ) . '</span>';
				}
				$callender_days .= '<li class="' . $css_classes_in_text . '" data-max="' . $available_slot . '" data-title="' . $available_slot . '" title="' . apply_filters( 'ph_booking_availability_title', '', $available_slot ) . '"> <input type="hidden" class="callender-full-date" value="' . date( 'Y-m', $curr_date ) . '">' . ph_wp_date( 'M', $utc_curr_date ) . '<br/>' . ph_wp_date( 'Y', $utc_curr_date ) . $booking_capacity . '</li>';
			}
			$curr_date = strtotime( date( 'Y-m-d', strtotime( '+1 month', $curr_date ) ) );
		}
		return $callender_days;
	}

	/**
	 * Output the time for time callender
	 *
	 * @param $start_time  string
	 * @param $end_time  string
	 * @param $product_id  string
	 * @return string
	 */
	public function phive_generate_time_for_period( $start_time, $end_time = '', $product_id = '', $asset_id = false, $calendar_for = '', $post = array() ) {
		// custom booking interval addon support
		$custom_booking_interval = isset( $post['custom_time_period'] ) ? $post['custom_time_period'] : '';

		if ( ! $product_id ) {
			global $product;
			$product_id = $product->get_id();
			$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product->get_id() );
		}
		if ( $this->assets_enabled == 'yes' ) {
			$this->asset_obj = $this->get_asset_obj( $asset_id, $start_time );
		}
		$buffer_period            = get_post_meta( $product_id, '_phive_buffer_period', 1 );
		$across_the_day           = get_post_meta( $product_id, '_phive_enable_across_the_day', 1 );
		$phive_enable_buffer      = get_post_meta( $product_id, '_phive_enable_buffer', 1 );
		$display_booking_capacity = get_post_meta( $product_id, '_phive_display_bookings_capacity', true );
		$end_time_display         = get_post_meta( $product_id, '_phive_enable_end_time_display', true );
		$remainng_bokkings_text   = get_post_meta( $product_id, '_phive_remainng_bokkings_text', true );
		$remainng_bokkings_text   = ph_wpml_translate_single_string( 'Remaining_Bookings_Text', $remainng_bokkings_text );
		$remainng_bokkings_text   = ! empty( $remainng_bokkings_text ) ? '%s ' . $remainng_bokkings_text : __( '(%s left)', 'bookings-and-appointments-for-woocommerce' );
		$loop_breaker             = 200;
		$callender_days           = '<div class="ph-calendar-overlay" id="ph-calendar-overlay" style="display:none"></div>';

		$fully_booked_indicator			= get_post_meta( $product_id, '_ph_display_bookings_fully_booked_indicator', true );
		$fully_booked_indicator_text	= get_post_meta( $product_id, '_ph_booking_fully_booked_indicator_text', true );

		$end_time         = ( empty( $end_time ) ) ? strtotime( '+1 day', strtotime( date( 'Y-m-d', ( strtotime( $start_time ) ) ) ) ) : strtotime( $end_time );
		$booking_capacity = null;
		$interval         = 0;

		$ph_timezome = new DateTimeZone(date_default_timezone_get());

		while ( strtotime( $start_time ) < $end_time && $loop_breaker > 0 ) {

			if ( ( $phive_enable_buffer == 'yes' ) && ( $this->interval_period == $buffer_period ) ) {
				$interval = $this->get_buffer_added_interval();
			} else {
				$interval = $this->interval;
			}

			$start_time          = strtotime( $start_time );
			$next_available_time = $start_time;
			$prev_available_time = '';

			$start_time_day = date( 'D', $start_time );
			do {
				// Check if matching asset is there for each slot and create the asset object for that asset id
				if ( $this->assets_enabled == 'yes' && $this->assets_auto_assign == 'yes' ) {
					$asset_id                     = $this->get_most_matching_asset_for_slots( $next_available_time, '', $product_id );
					$this->asset_obj              = $this->get_asset_obj( $asset_id, $next_available_time );
					$asset_matched_available_time = $next_available_time;
				}

				// This loop finds the next available time slot starting time
				// ONLY check the interval time slot, as buffer time is not needed to be available.
				$next_available_time_for_product = $this->get_next_available_time( $next_available_time, $this->interval, $product_id );
				// 168526 - Issue: When setting up both Global availability and asset availability rules, availability is getting blocked out partially!
				$asset_next_available_time = $this->get_asset_next_available_time( $this->get_farthest_time( $next_available_time_for_product, $next_available_time ), $this->interval );
				$prev_available_time       = $next_available_time;
				$next_available_time       = $this->get_farthest_time( $next_available_time_for_product, $asset_next_available_time );

				// 194516 The slots showing as available when asset is not available
				if ( ! empty( $next_available_time_for_product ) && empty( $asset_next_available_time ) ) {
					$next_available_time = $next_available_time_for_product;
					break;
				}
				// 158203
				$conflict = 0;
				if ( $this->assets_enabled == 'yes' && $this->assets_auto_assign == 'yes' && $next_available_time != '' ) {
					if ( ( $next_available_time_for_product == $next_available_time && $asset_next_available_time < $next_available_time ) || ( $asset_matched_available_time != $next_available_time ) ) {
						$conflict = 1;
					}
				}

				// In case of default - non available, $next_available_time jumps to next day indicating no available time remaining for current day
				if ( $next_available_time != '' && $start_time_day != date( 'D', $next_available_time ) ) {
					$next_available_time = '';
					break;
				}
				// In case of default - available, !$next_available_time indicates that no available time for current day
				if ( ! $next_available_time || $this->is_available( $next_available_time, $this->interval, $product_id, 'time' ) ) {
					break;
				} elseif ( $prev_available_time == $next_available_time ) {
					// If no rule was matched, get the next time based on booking duration.
					// else, check availability for the new time slot which was calculated based on rules.
					$next_available_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $next_available_time ) );
					$next_available_time = strtotime( $next_available_time );
				}
			} while ( $next_available_time );

			if ( ! $next_available_time ) {
				// no time slot available for today
				break;
			}
			$start_time = $next_available_time;

			$available_slot = $this->get_number_of_available_slot( $start_time, $product_id, $asset_id );

			// 158203
			if ( $conflict == 1 && $available_slot == 0 ) {
				$old_asset_id       = $asset_id;
				$old_asset_obj      = $this->asset_obj;
				$old_available_slot = $available_slot;
				$asset_id           = $this->get_most_matching_asset_for_slots( $start_time, '', $product_id );
				$this->asset_obj    = $this->get_asset_obj( $asset_id, $start_time );
				$available_slot     = $this->get_number_of_available_slot( $start_time, $product_id, $asset_id );
				if ( $available_slot < 1 ) {
					$asset_id        = $old_asset_id;
					$this->asset_obj = $old_asset_obj;
					$available_slot  = $old_available_slot;
				}
			}

			if ( $this->is_working_time( $start_time, $product_id ) || $across_the_day == 'yes' ) {

				$css_classes = array( 'ph-calendar-date' );
				// if( $this->is_booked_date( $start_time, $product_id ) || ($available_slot==0) ){
				// $css_classes[] = 'booking-full';
				// $css_classes[] = 'de-active';
				// }

				// is_booked_date and $available_slot working the same way, it will take extra loading time if both are applied.
				if ( ( $available_slot == '0' ) || ( empty( $asset_next_available_time ) && $this->assets_enabled == 'yes' && $this->assets_auto_assign == 'yes' ) ) {
					$css_classes[] = 'booking-full';
					$css_classes[] = 'de-active';
				}

				$date_now = $this->get_current_time_in_wp_tz();
				// $date_now = strtotime(date("Y-m-d H:i") );
				if ( $this->zone == -3 ) {
					$date_now = strtotime( gmdate( 'Y-m-d H:i', time() + 3600 * ( $this->zone ) ) );
				}

				if ( $start_time < $date_now ) { // if Past date.
					$css_classes[] = 'booking-disabled';
				}

				if ( ! $this->is_available( $start_time, '', $product_id, 'time' ) || ! $this->is_asset_available( $start_time ) || ( empty( $asset_next_available_time ) && $this->assets_enabled == 'yes' && $this->assets_auto_assign != 'yes' ) ) { // if set rule not available in availability table
					$css_classes[] = 'de-active';
					$css_classes[] = 'not-available';
				}
				if ( ! $this->is_working_time( $start_time, $product_id ) && $across_the_day == 'yes' ) {
					$css_classes[] = 'non-working-time';
				}

				// custom booking interval addon support
				$css_classes = apply_filters( 'ph_disable_slot_beyond_daily_booking_times', $css_classes, $product_id, $custom_booking_interval, $start_time, $this->interval_period, $this->shop_opening_time, $this->shop_closing_time, $asset_id );

				$css_classes_in_text = implode( ' ', array_unique( $css_classes ) );
				$non_title_classes   = array( 'booking-disabled', 'booking-full', 'not-available' );
				// if( ($phive_enable_buffer == 'yes') && ($this->interval_period == $buffer_period)  ){
				// $interval = $this->get_buffer_added_interval();

				// }else{
				// $interval = $this->interval;

				// }

				$zone = get_option( 'timezone_string' );

				if ( empty( $zone ) ) {
					$time_offset = $this->zone;
					$zone        = timezone_name_from_abbr( '', $time_offset * 60 * 60, 0 );
				}

				if ( ! empty( $zone ) ) {
					date_default_timezone_set( $zone );
				}

				$fully_booked_display_text = '';
						
				if ( $fully_booked_indicator == 'yes' && in_array('booking-full', $css_classes)) {
					$fully_booked_display_text = '<span class="ph_bookings_capacity" style="font-size:12px;"><br>' . sprintf( __( $fully_booked_indicator_text, 'bookings-and-appointments-for-woocommerce' ) ) . '</span>';
				}

				if ( ph_get_calendar_design() == 3 ) {
					if ( array_diff( $non_title_classes, $css_classes ) != $non_title_classes ) {

						$callender_days .= '<li class="' . $css_classes_in_text . '"> <input type="hidden" class="callender-full-date" value="' . wp_date( 'Y-m-d H:i', $start_time, $ph_timezome ) . '"><span class="ph_calendar_time ph_calendar_time_start">' . wp_date( $this->wp_time_format, $start_time, $ph_timezome ) . '</span><span class="ph_calendar_time_end">' . wp_date( $this->wp_time_format, strtotime( "+$this->interval $this->interval_period", $start_time ), $ph_timezome ) . '</span>' . $fully_booked_display_text . '</li>';
					} else {
						if ( $display_booking_capacity == 'yes' ) {
							$booking_capacity = '<span class="ph_bookings_capacity" style="font-size:12px;"><br>' . sprintf( __( $remainng_bokkings_text, 'bookings-and-appointments-for-woocommerce' ), $available_slot ) . '</span>';
						}
						$callender_days .= '<li class="' . $css_classes_in_text . '"  data-max="' . $available_slot . '" data-title="' . $available_slot . '" title="' . apply_filters( 'ph_booking_availability_title', '', $available_slot ) . '"> <input type="hidden" class="callender-full-date" value="' . wp_date( 'Y-m-d H:i', $start_time, $ph_timezome ) . '"><span class="ph_calendar_time ph_calendar_time_start">' . wp_date( $this->wp_time_format, $start_time, $ph_timezome ) . '</span><span class="ph_calendar_time_end">' . wp_date( $this->wp_time_format, strtotime( "+$this->interval $this->interval_period", $start_time ), $ph_timezome ) . '</span>' . $booking_capacity . '</li>';
					}
				} elseif ( $end_time_display == 'yes' ) {
					if ( array_diff( $non_title_classes, $css_classes ) != $non_title_classes ) {

						$callender_days .= '<li class="' . $css_classes_in_text . '"> <input type="hidden" class="callender-full-date" value="' . wp_date( 'Y-m-d H:i', $start_time, $ph_timezome ) . '"><span class="ph_calendar_time">' . wp_date( $this->wp_time_format, $start_time, $ph_timezome ) . ' - ' . wp_date( $this->wp_time_format, strtotime( "+$this->interval $this->interval_period", $start_time ), $ph_timezome ) . '</span>' . $fully_booked_display_text . '</li>';
					} else {
						if ( $display_booking_capacity == 'yes' ) {
							$booking_capacity = '<span class="ph_bookings_capacity" style="font-size:12px;"><br>' . sprintf( __( $remainng_bokkings_text, 'bookings-and-appointments-for-woocommerce' ), $available_slot ) . '</span>';
						}

						$callender_days .= '<li class="' . $css_classes_in_text . '"  data-max="' . $available_slot . '" data-title="' . $available_slot . '" title="' . apply_filters( 'ph_booking_availability_title', '', $available_slot ) . '"> <input type="hidden" class="callender-full-date" value="' . wp_date( 'Y-m-d H:i', $start_time, $ph_timezome) . '"><span class="ph_calendar_time">' . wp_date( $this->wp_time_format, $start_time, $ph_timezome ) . ' - ' . wp_date( $this->wp_time_format, strtotime( "+$this->interval $this->interval_period", $start_time ), $ph_timezome ) . '</span>' . $booking_capacity . '</li>';
					}
				} elseif ( array_diff( $non_title_classes, $css_classes ) != $non_title_classes ) {

					$callender_days .= '<li class="' . $css_classes_in_text . '"> <input type="hidden" class="callender-full-date" value="' . wp_date( 'Y-m-d H:i', $start_time, $ph_timezome ) . '"><span class="ph_calendar_time">' . apply_filters( 'ph_bookings_calendar_time_slot_value', wp_date( $this->wp_time_format, $start_time, $ph_timezome ), $product_id, $interval, $start_time ) . '</span>' . $fully_booked_display_text . '</li>';
				} else {
					if ( $display_booking_capacity == 'yes' ) {
						$booking_capacity = '<span class="ph_bookings_capacity" style="font-size:12px;"><br>' . sprintf( __( $remainng_bokkings_text, 'bookings-and-appointments-for-woocommerce' ), $available_slot ) . '</span>';
					}
					$callender_days .= '<li class="' . $css_classes_in_text . '"  data-max="' . $available_slot . '" data-title="' . $available_slot . '" title="' . apply_filters( 'ph_booking_availability_title', '', $available_slot ) . '"> <input type="hidden" class="callender-full-date" value="' . wp_date( 'Y-m-d H:i', $start_time, $ph_timezome ) . '"><span class="ph_calendar_time">' . apply_filters( 'ph_bookings_calendar_time_slot_value', wp_date( $this->wp_time_format, $start_time, $ph_timezome ), $product_id, $interval, $start_time ) . '</span>' . $booking_capacity . '</li>';
				}
			}

			// // if( $interval_type == 'minute' ){
			$start_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );

			/*
			}else{
				$start_time = date( 'Y-m-d H:i', strtotime( "+1 hour", $start_time ) );
			}*/
			--$loop_breaker;
		}

		if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product_id );
		}

		// Add time navigator in case of range
		$booking_type = $product->get_meta( '_phive_book_interval_type' );
		if ( $booking_type == 'customer_choosen' ) {
			$require_next_day_navigation = self::allow_prev_next_day_time_nav( $product );        // Navigation of time is not required if start time or end time is set
			// 106293
			if ( ( ( $require_next_day_navigation && ( $across_the_day == 'yes' || empty( $across_the_day ) ) ) || ( $across_the_day == 'yes' ) ) ) {
				$time_of_date = ! empty( $_POST['date'] ) ? $_POST['date'] : null;
				return '<span class="ph-prev-day-time" >&#10094;</span><input type="hidden" id="ph-booking-time-for-the-date" value="' . $time_of_date . '" /><span class="ph-next-day-time" >&#10095;</span><br class="ph-h-0-for-calendar-3">' . $callender_days;
			}
			/*
			$time_of_date = ! empty($_POST['date']) ? $_POST['date'] : null;
			return '<span class="ph-prev-day-time" >&#10094;</span><input type="hidden" id="ph-booking-time-for-the-date" value="'.$time_of_date.'" /><span class="ph-next-day-time" >&#10095;</span><br>'.$callender_days;*/
		}
		if ( ! empty( $asset_id ) ) {
			$ph_cache_obj = new phive_booking_cache_manager();
			$ph_cache_obj->ph_unset_cache( $asset_id );
		}
		return $callender_days;
	}

	/**
	 * Check whether navigation to next/prev day time is required or not.
	 *
	 * @param Object $product WC_Product_phive_booking
	 * @return boolean
	 */
	public static function allow_prev_next_day_time_nav( $product ) {
		$status     = true;
		$start_time = $product->get_meta( '_phive_book_working_hour_start' );
		$end_time   = $product->get_meta( '_phive_book_working_hour_end' );
		if ( ! empty( $start_time ) || ! empty( $end_time ) ) {
			$status = false;
		}
		return $status;
	}

	/**
	 * Date calendar for time picker
	 */
	public function phive_generate_date_calendar_for_timepicker( $month_start_date, $display_settings = array() ) {

		$ph_swap_month_year  = isset( $display_settings['ph_swap_month_year_in_calendar'] ) ? $display_settings['ph_swap_month_year_in_calendar'] : 'no';
		$start_date_object   = new DateTime( $month_start_date, new DateTimeZone( 'UTC' ) );
		$start_year_display  = $start_date_object->format( 'U' );
		$start_month_display = $start_date_object->format( 'U' );
		$start_month_display = ph_wp_date( 'F', $start_month_display );
		$year_format         = apply_filters( 'ph_customize_year_format', 'Y' );
		$start_year_display  = ph_wp_date( $year_format, $start_year_display );
		?>
		<div class="ph-calendar-month">
			<ul>

				<li class="ph-prev" <?php echo ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == 'he' ) ? "style='float:right'" : ''; ?>><?php echo is_rtl() ? '&#10095;' : '&#10094;'; ?></li>
				<li class="ph-next" <?php echo ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == 'he' ) ? "style='float:left'" : ''; ?>><?php echo is_rtl() ? '&#10094;' : '&#10095;'; ?></li>


				<li class="ph-month">
					<div class="month-year-wraper">

						<?php if ( 'no' == $ph_swap_month_year ) { ?>

							<span class="span-month"><?php echo $start_month_display; ?></span>
							<span class="span-year"><?php echo $start_year_display; ?></span>
						<?php } else { ?>

							<span class="span-year"><?php echo $start_year_display; ?></span>
							<span class="span-month"><?php echo $start_month_display; ?></span>
						<?php } ?>

						<input type="text" readonly size="12" class="callender-month" value="<?php echo $start_date_object->format( 'F' ); ?>" style="opacity: 0 !important; filter: alpha(opacity=0)!important;">
						<input type="text" readonly size="5" class="callender-year" value="<?php echo $start_date_object->format( 'Y' ); ?>" style="opacity: 0 !important; filter: alpha(opacity=0)!important;">
					</div>
				</li>
			</ul>
		</div>

		<ul class="ph-calendar-weekdays ">
			<?php
			$week_days  = '<li>' . __( 'Mon', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
			$week_days .= '<li>' . __( 'Tue', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
			$week_days .= '<li>' . __( 'Wed', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
			$week_days .= '<li>' . __( 'Thu', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
			$week_days .= '<li>' . __( 'Fri', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
			$week_days .= '<li>' . __( 'Sat', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
			$week_days .= '<li>' . __( 'Sun', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
			$week_days  = apply_filters( 'ph_booking_calendar_weekdays_order', $week_days );
			echo $week_days;
			?>
		</ul>
		<ul class="ph-calendar-days ph-ul-date ph_booking_no_place_left" id="ph-calendar-days" style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?> !important;">
			<?php
			$asset_id = false;
			if ( $this->assets_enabled == 'yes' ) {
				if ( $this->assets_auto_assign != 'yes' && ! empty( $this->assets_pricing_rules[0] ) ) {
					$asset_id = $this->assets_pricing_rules[0]['ph_booking_asset_id'];
				} else {
					$asset_id = '';
				}
			}
			// $available_dates_array = $this->get_available_date_for_time($month_start_date, '', '', $asset_id);
			echo $this->phive_generate_days_for_period( $month_start_date, '', '', $asset_id, 'time-picker' );
			?>
		</ul>
		<?php
	}

	/**
	 * Render the HTML for inputting persons
	 */
	public function phive_generate_persons_input_fields() {
		?>
		<div class="extra-resources participant_section">
			<?php

			foreach ( $this->persons_pricing_rules as $key => $rule ) {
				if ( ! empty( $rule['ph_booking_persons_rule_type'] ) ) {
					$rule['ph_booking_persons_rule_type'] = apply_filters( 'wpml_translate_single_string', $rule['ph_booking_persons_rule_type'], 'bookings-and-appointments-for-woocommerce', 'participant_name_' . $rule['ph_booking_persons_rule_type'] );
					$number_of_persons                    = ( ! empty( $rule['ph_booking_persons_rule_min'] ) ) ? $rule['ph_booking_persons_rule_min'] : 0;
					?>
					<div class="participant_inner_section">
						<div class="persons-title">
							<label class="label-person"><?php _e( $rule['ph_booking_persons_rule_type'], 'bookings-and-appointments-for-woocommerce' ); ?></label>
						</div>
						<div class="person-value button-group-container">
							<a href="#" class="input-person-minus">&minus;</a>
							<input type="number" name="phive_book_persons[]" class="input-person shipping-price-related" rule-key="persons-<?php echo $key; ?>" value="<?php echo $number_of_persons; ?>" min="<?php echo $rule['ph_booking_persons_rule_min']; ?>" max="<?php echo $rule['ph_booking_persons_rule_max']; ?>" last-val="<?php echo $rule['ph_booking_persons_rule_min']; ?>" <?php echo ( $rule['ph_booking_persons_rule_min'] > 0 ) ? 'required' : ''; ?> data-name="<?php echo $rule['ph_booking_persons_rule_type']; ?>">						
							<a href="#" class="input-person-plus">&plus;</a>
						</div>
					</div>
					<div class="participant_count_error participant_count_error_persons-<?php echo $key; ?>"></div>
					<?php
				}
			}
			?>

		</div>
		<?php
	}

	/**
	 * Render the HTML for inputting resources
	 */
	public function phive_generate_resources_input_fields() {
		global $product;
		$product_id = $product->get_id();
		$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product_id );        // WPML Compatibility
		?>
		<div class="extra-resources resources_section">
			<?php
			if ( ! empty( $product ) && get_post_meta( $product_id, '_phive_booking_resources_type', 1 ) == 'single' ) {
				$resources_type		= get_post_meta( $product_id, '_phive_booking_resources_type', 1 );
				$resources_label	= get_post_meta( $product_id, '_phive_booking_resources_label', 1 );
				$resources_label	= ph_wpml_translate_single_string( 'Resource_Main_Label', $resources_label );
				$resources_label 	= !empty($resources_label) ? $resources_label : __('Resources', 'bookings-and-appointments-for-woocommerce');

				$single_resources_mandatory_option = get_post_meta( $product->get_id(), '_phive_booking_single_resources_mandatory_enable', 1 );
				?>
				<div style="overflow:hidden">
					<div class="resources-wraper">
						<div>
							<input type="hidden" value="single" class="resources_type">
							<div class="persons-title">
								<label class="label-resources"><?php _e( $resources_label, 'bookings-and-appointments-for-woocommerce' ); ?></label>

								<?php if ( $single_resources_mandatory_option == 'yes' ) { ?>
										<span style="color: red;" class="phive_book_resources_required">*</span>
								<?php } ?>
							</div>
							<div class="person-value" style="display: inline;">
								<select class="phive_book_resources shipping-price-related" name="phive_book_resources">
									<option value=""><?php echo __( 'Select any', 'bookings-and-appointments-for-woocommerce' ); ?></option>
									<?php
									foreach ( $this->resources_pricing_rules as $key => $rule ) {
										if ( empty( $rule['ph_booking_resources_name'] ) ) {
											continue;
										}

										// Auto assign resources.
										if ( $rule['ph_booking_resources_auto_assign'] == 'yes' ) {
											?>
											<option selected value="<?php echo $rule['ph_booking_resources_name']; ?>" id="phive_book_resources_<?php echo $key; ?>" >
												<?php
												$rule['ph_booking_resources_name'] = apply_filters( 'wpml_translate_single_string', $rule['ph_booking_resources_name'], 'bookings-and-appointments-for-woocommerce', 'resource_name_' . $rule['ph_booking_resources_name'] );
												_e( $rule['ph_booking_resources_name'], 'bookings-and-appointments-for-woocommerce' );
												?>
											</option>
											<?php
										} else {
											?>
											<option value="<?php echo $rule['ph_booking_resources_name']; ?>" id="phive_book_resources_<?php echo $key; ?>" >
												<?php
												$rule['ph_booking_resources_name'] = apply_filters( 'wpml_translate_single_string', $rule['ph_booking_resources_name'], 'bookings-and-appointments-for-woocommerce', 'resource_name_' . $rule['ph_booking_resources_name'] );
												_e( $rule['ph_booking_resources_name'], 'bookings-and-appointments-for-woocommerce' );
												?>
											</option>
											<?php
										}
									}
									?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<?php

			} else {
				$resources_label = get_post_meta( $product_id, '_phive_booking_resources_label', 1 );
				$resources_label = ph_wpml_translate_single_string( 'Resource_Main_Label', $resources_label );
				?>

				<div class="">
					<label class="label-resources"><?php _e( $resources_label, 'bookings-and-appointments-for-woocommerce' ); ?></label>
				</div>
				<div class="">
					<?php
					foreach ( $this->resources_pricing_rules as $key => $rule ) {
						if ( empty( $rule['ph_booking_resources_name'] ) ) {
							continue;
						}

						?>
						<div style="overflow:hidden">
							<div class="resources-wraper">
								<input type="hidden" value="multiple" class="resources_type">
								<?php
								// Auto assign resources.
								$rule['ph_booking_resources_name'] = apply_filters( 'wpml_translate_single_string', $rule['ph_booking_resources_name'], 'bookings-and-appointments-for-woocommerce', 'resource_name_' . $rule['ph_booking_resources_name'] );
								if ( $rule['ph_booking_resources_auto_assign'] == 'yes' ) {
									?>
									<!-- <span style="display: inline;width: 15px;">&nbsp;&nbsp;&nbsp;&nbsp;</span> -->
									<input type="hidden" id="phive_book_resources_<?php echo $key; ?>" class="phive_book_resources input-resources" value="yes" name="phive_book_resources[]" checked>
									<label class="label-person auto_assigned_resource" style="display: inline;"><?php _e( $rule['ph_booking_resources_name'], 'bookings-and-appointments-for-woocommerce' ); ?></label>
									<?php
								} else {
									?>
									<input type="hidden" class="phive_book_resources" value="no" name="phive_book_resources[]">
									<input type="checkbox" id="phive_book_resources_<?php echo $key; ?>" class="resources_check input-resources shipping-price-related" />
									<label class="label-person" style="display: inline !important;" for="phive_book_resources_<?php echo $key; ?>"><?php _e( $rule['ph_booking_resources_name'], 'bookings-and-appointments-for-woocommerce' ); ?></label>
									<?php
								}

								?>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the HTML for inputting additional notes
	 */
	public function phive_generate_additional_notes_field() {
		?>
		<p class="additional-notes">
			<label class="label-person"><?php _e( $this->additional_notes_label, 'bookings-and-appointments-for-woocommerce' ); ?><?php echo $this->is_add_notes_mandatory == 'yes' ? '<span style="color:red">*</span>' : '' ?></label>
			<textarea placeholder="" class="phive_book_additional_notes_text" maxlength="80" name="phive_book_additional_notes_text" <?php echo $this->is_add_notes_mandatory == 'yes' ? 'required' : '' ?>></textarea>
		</p>
		<?php
	}

	/**
	 * Render the HTML for inputting Assets
	 */
	public function phive_generate_assets_input_fields() {

		// 106356 - retrieving global assets
		$global_settings = get_option( 'ph_booking_settings_assets', 1 );
		$assets          = ( isset( $global_settings['_phive_booking_assets'] ) && ! empty( $global_settings['_phive_booking_assets'] ) ) ? $global_settings['_phive_booking_assets'] : array();

		if ( $this->assets_auto_assign == 'yes' ) {
			?>
			<input type="hidden" class='phive_book_assets' name="phive_book_assets" value="">
			<?php
			return;
		}
		?>

		<div class="extra-resources asset-section">
			<label><?php echo ! empty( $this->assets_label ) ? _e( $this->assets_label, 'bookings-and-appointments-for-woocommerce' ) : _e( 'Asset', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			<select class="input-assets phive_book_assets" name="phive_book_assets">
				<?php
				foreach ( $this->assets_pricing_rules as $key => $rule ) {
					// 106356 - checking if global asset is present
					if ( ( empty( $rule['ph_booking_asset_id'] ) ) || ( ! in_array( $rule['ph_booking_asset_id'], array_keys( $assets ) ) ) ) {
						continue;
					}
					$asset_name = $this->assets_rules[ $rule['ph_booking_asset_id'] ]['ph_booking_asset_name'];
					$asset_name = apply_filters( 'wpml_translate_single_string', $asset_name, 'ph_booking_plugins', $rule['ph_booking_asset_id'] );
					?>
					<option value="<?php echo $rule['ph_booking_asset_id']; ?>"><?php _e( $asset_name, 'bookings-and-appointments-for-woocommerce' ); ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<?php
	}

	/**
	 * Get the shop opening time
	 */
	public function get_shop_opening_time( $product_id, $date = '', $selected_date = '' ) {
		$zone = get_option( 'timezone_string' );

		if ( empty( $zone ) ) {
			$time_offset = $this->zone;
			$zone        = timezone_name_from_abbr( '', $time_offset * 60 * 60, 0 );
		}
		if ( ! empty( $zone ) ) {
			date_default_timezone_set( $zone );
		}

		$start_date   = date( 'Y-m-d', strtotime( $date ) );
		$selected_day = date( 'Y-m-d', strtotime( $selected_date ) );
		// ticket - 47769
		// if(!empty($selected_date) && strtotime($selected_day) != strtotime($start_date))
		// {
		// $start_time=date('Y-m-d H:i:s',strtotime($selected_date));
		// $next_day=date('Y-m-d 00:00:00',strtotime($date));

		// while(strtotime($start_time)<strtotime($next_day))
		// {
		// $start_time = date( 'Y-m-d H:i', strtotime( "+$this->interval $this->interval_period", strtotime($start_time) ) );
		// }
		// return date( 'H:i',strtotime($start_time) );
		// }
		$shop_opening_time = ! empty( $this->shop_opening_time ) ? date( 'H:i', strtotime( $this->shop_opening_time ) ) : '00:00';
		$shop_opening_time = apply_filters( 'ph_modify_shop_opening_time', $shop_opening_time, $product_id, strtotime( $date ) );

		$buffer_before_time = get_post_meta( $product_id, '_phive_buffer_before', 1 );

		if ( ! empty( $buffer_before_time ) && $this->phive_enable_buffer == 'yes' ) {
			if ( $this->buffer_period == 'hour' ) {
				$buffer_before_time = $buffer_before_time . ':00';
			} elseif ( $this->buffer_period == 'minute' ) {
				// $buffer_before_time="00:".$buffer_before_time;
				$buffer_before_time = floor( $buffer_before_time / 60 ) . ':' . $buffer_before_time % 60;
			}
			$secs              = strtotime( $buffer_before_time ) - strtotime( '00:00:00' );
			$shop_opening_time = date( 'H:i', strtotime( $shop_opening_time ) + $secs );
		}

		return $shop_opening_time;
	}


	private function get_buffer_added_interval() {

		$buffer_before_time = empty( $this->buffer_before_time ) ? '0' : $this->buffer_before_time;
		$buffer_after_time  = empty( $this->buffer_after_time ) ? '0' : $this->buffer_after_time;
		$interval           = $this->interval;
		if ( ( ( $buffer_before_time % $this->interval ) != 0 ) || ( ( $buffer_after_time % $this->interval ) != 0 ) ) {
			$interval += ( $buffer_before_time + $buffer_after_time );
		}
		return $interval;
	}

	private function is_date_in_between( $date, $from, $to, $calendar_for = '' ) {

		$from_string = $from;
		$to_string   = $to;
		$from        = ! empty( $from ) ? strtotime( $from ) : '';
		$to          = ! empty( $to ) ? strtotime( $to ) : '';

		if ( $calendar_for == 'time-picker' || $calendar_for == 'time' ) {
			$from = ! empty( $from_string ) ? strtotime( $from_string . ' 00:00' ) : '';
			$to   = ! empty( $to_string ) ? strtotime( $to_string . ' 23:59' ) : '';
		}
		if ( ( ! empty( $from ) && $date >= $from ) && ( ! empty( $to ) && $date <= $to ) ) {
			return true;
		} elseif ( ! empty( $from ) && $date < $from || ( ! empty( $to ) && $date > $to ) ) {
			return false;
		} elseif ( $calendar_for == 'time-picker' || $calendar_for == 'time' ) {
			$from = ! empty( $from_string ) ? strtotime( $from_string . ' 00:00' ) : '';
			$to   = ! empty( $to_string ) ? strtotime( $to_string . ' 23:59' ) : '';
			if ( ! empty( $this->fixed_availability_to ) && $date > strtotime( $this->fixed_availability_to ) ) {
				return false;
			}
			if ( ! empty( $this->fixed_availability_from ) && $date < strtotime( $this->fixed_availability_from ) ) {
				return false;
			}
			if ( empty( $this->last_availability ) && ! empty( $from ) && $from <= $date ) {
				return true;
			} elseif ( empty( $this->first_availability ) && ! empty( $to ) && $to >= $date ) {
				return true;
			} elseif ( ! empty( $from ) && $from <= $date && ! empty( $to ) && $to >= $date ) {
				return true;
			}
		} elseif ( ! empty( $from ) && ! empty( $to ) && $from > $to ) {
			if ( $date < $from ) {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}
	public function is_date_in_between_relative( $date, $from, $to, $calendar_for = '' ) {
		$from_string = $from;
		$to_string   = $to;
		$from        = ! empty( $from ) ? strtotime( $from ) : '';
		$to          = ! empty( $to ) ? strtotime( $to ) : '';
		if ( ( ! empty( $from ) && $date >= $from ) && ( ! empty( $to ) && $date <= $to ) ) {
			return true;
		} elseif ( $calendar_for == 'time-picker' || $calendar_for == 'time' ) {
			if ( isset( $this->first_availability_interval_period ) && $this->first_availability_interval_period != 'days' && $calendar_for != 'time' ) {
				$from = ! empty( $from_string ) ? strtotime( $from_string . ' 00:00' ) : '';
			}
			if ( isset( $this->last_availability_interval_period ) && $this->last_availability_interval_period != 'days' && $calendar_for != 'time' ) {
				$to = ! empty( $to_string ) ? strtotime( $to_string . ' 23:59' ) : '';
			}
			// $from=strtotime($from_string." 00:00");
			// $to=strtotime($to_string." 23:59");

			if ( empty( $this->last_availability ) && ! empty( $from ) && $from <= $date ) {
				return true;
			} elseif ( empty( $this->first_availability ) && ! empty( $to ) && $to >= $date ) {
				return true;
			} elseif ( ! empty( $from ) && $from <= $date && ! empty( $to ) && $to >= $date ) {
				return true;
			}
		} elseif ( $calendar_for == 'date-picker' ) {
			if ( empty( $this->last_availability ) && ! empty( $from ) && $from <= $date ) {
				return true;
			} elseif ( empty( $this->first_availability ) && ! empty( $to ) && $to >= $date ) {
				return true;
			} elseif ( ! empty( $from ) && $from <= $date && ! empty( $to ) && $to >= $date ) {
				return true;
			}
		} elseif ( ! empty( $from ) && ! empty( $to ) && $from > $to ) {
			if ( $date < $from ) {
				return false;
			}
			return true;
		} else {
			// if last availability is not set
			if ( ( ! empty( $from ) && $date >= $from ) && ( empty( $to ) ) ) {
				
				return true;
			} else if ( empty( $from ) && $calendar_for == 'month' && $date <= $to && $date > time() ) {

				return true;
			}

			return false;
		}
	}

	/**
	 * Check if Asset is available against Assets availability rules
	 *
	 * @param $calendar_for optional to check the unavailable by default for time calendars
	 * @return bool
	 */
	private function is_asset_available( $start_time, $calendar_for = '' ) {
		if ( $this->assets_enabled == 'yes' ) {

			if ( is_object( $this->asset_obj ) ) {

				return $this->asset_obj->is_available( $start_time, '', $calendar_for, $this->assets_auto_assign );
			}
		}
		return true;
	}

	/**
	 * Check availability from availability table
	 *
	 * @return Bool
	 */
	public function is_available( $start_time, $interval = '', $product_id = '', $calendar_for = '' ) {

		// Filter to allow skip availability rules for admin(support Disable Avoid Last Minute Booking For Admin Bookings Addon)
		$ignore_availability_rules = apply_filters( 'ph_bookings_skip_availability_rules_for_admin', false, 'booking' );
		
		// If the filter returns true, check for availability rules are ignored.
		if ( $ignore_availability_rules ) {

			return true;
		}

		// if first availability set
		if ( ( ! empty( $this->fixed_availability_from ) || ! empty( $this->fixed_availability_to ) ) ) {

			if ( ! $this->is_date_in_between( $start_time, $this->fixed_availability_from, $this->fixed_availability_to, $calendar_for ) ) {
				return false;
			}
		}
		if ( ( ! empty( $this->first_availability ) || ! empty( $this->last_availability ) ) ) { // If relative today

			$first_availability_date_format = ( $this->first_availability_interval_period == 'days' || $calendar_for == 'time-picker' ) ? 'Y-m-d' : 'Y-m-d H:i';
			$last_availability_date_format  = ( $this->last_availability_interval_period == 'days' || $calendar_for == 'time-picker' ) ? 'Y-m-d' : 'Y-m-d H:i';
			$this->first_availability       = empty( $this->first_availability ) ? '' : $this->first_availability;
			$this->last_availability        = empty( $this->last_availability ) ? '' : $this->last_availability;
			$from                           = ! empty( $this->first_availability ) ? date( $first_availability_date_format, strtotime( '+' . $this->first_availability . ' ' . $this->first_availability_interval_period ) ) : '';
			$to                             = ! empty( $this->last_availability ) ? date( $last_availability_date_format, strtotime( '+' . $this->last_availability . ' ' . $this->last_availability_interval_period ) ) : '';
			if ( ! $this->is_date_in_between_relative( $start_time, $from, $to, $calendar_for ) ) {
				return false;
			}
		}
		if ( $calendar_for == 'time-picker' && $this->called_when_clicked_to_date == 1 ) {
			$calendar_for = 'time';
		}
		// $end_time = strtotime( "+$interval", $start_time );
		if ( ! empty( $interval ) ) {
			$end_time = strtotime( "+$interval $this->interval_period", $start_time );
		} else {
			$end_time = $start_time;
		}

		// Don't need to create time slot of next day in current day.
		if ( date( 'Y-m-d', $start_time ) != date( 'Y-m-d', $end_time ) && date( 'H:i', $end_time ) != '00:00' && $calendar_for == 'time' ) {
			return false;
		}

		foreach ( $this->availability_rules as $key => $rule ) {
			if ( $rule['availability_type'] == 'custom' ) {
				if ( $calendar_for == 'time-picker' || $calendar_for == 'date-picker' ) {
					$date_from = explode( ' ', $rule['from_date'] );
					$date_from = $date_from[0];
					$date_to   = explode( ' ', $rule['to_date'] );
					$date_to   = $date_to[0];
				} else {
					$date_from = $rule['from_date'];
					$date_to   = $rule['to_date'];
				}

				if (
					! empty( $date_from ) && ! empty( $date_to )
					&& $start_time >= strtotime( $date_from )
					&& $start_time <= strtotime( $date_to )
				) {
					if ( $calendar_for == 'time-picker' && ( $date_from == $date_to || $start_time == strtotime( $date_to ) || $start_time == strtotime( $date_from ) ) ) {
						return true;
					} elseif ( $start_time <= strtotime( $date_to ) ) {
						// availability rule TO depends on the duration type
						// for days, it is included, for min/hours, it is excluded
						if ( $calendar_for == 'date-picker' ) {
							if ( $rule['is_bokable'] === 'yes' ) {
								if ( $start_time <= strtotime( $date_to ) ) {
									return ( $end_time <= strtotime( $date_to ) );
								}
							} elseif ( $start_time <= strtotime( $date_to ) ) {
								return false;
							}
						} elseif ( $rule['is_bokable'] === 'yes' ) {
							if ( $start_time < strtotime( $date_to ) ) {
								return ( $end_time <= strtotime( $date_to ) );
							}
						} elseif ( $start_time < strtotime( $date_to ) ) {
							return false;
						}
					}
				}
				// if( !empty($date_from) && !empty($date_to  )
				// && $start_time >= strtotime($date_from)
				// && $start_time <= strtotime($date_to ) )
				// {
				// if($calendar_for == 'time-picker' && ($date_from == $date_to || $start_time == strtotime($date_to)  || $start_time == strtotime($date_from)  )){
				// return true;
				// }
				// else{
				// error_log('time-picker 3');
				// return $rule['is_bokable'] === 'yes';
				// }

				// }
			} elseif ( $rule['availability_type'] == 'months' && ! empty( $rule['from_month'] ) ) {

				$range_arr = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );

				if (
					$this->is_in_range( $range_arr, date( 'n', $start_time ), $rule['from_month'], $rule['to_month'] )
					&& $this->is_in_range( $range_arr, date( 'n', $end_time ), $rule['from_month'], $rule['to_month'] )
				) {
					return $rule['is_bokable'] === 'yes';
				}
			} elseif ( $rule['availability_type'] == 'days' && ! empty( $rule['from_week_day'] ) ) {
				$range_arr = array( 1, 2, 3, 4, 5, 6, 7 );

				if (
					$this->is_in_range( $range_arr, date( 'N', $start_time ), $rule['from_week_day'], $rule['to_week_day'] )
					&& $this->is_in_range( $range_arr, date( 'N', $end_time ), $rule['from_week_day'], $rule['to_week_day'] )
				) {
					return $rule['is_bokable'] === 'yes';
				}
			} elseif ( $rule['availability_type'] == 'time-all' && ! empty( $rule['from_time'] ) ) {
				if ( $calendar_for == 'time-picker' ) {
					return true;
				}

				// When the end time is 00:00 and need to check availability with the 23:59
				if ( date( 'H:i', $end_time ) == '00:00' && $start_time != $end_time ) {

					$end_time = strtotime( '-1 minute', $end_time );
				}

				if ( $rule['is_bokable'] === 'yes' ) {
					if (
						strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $start_time ) ) <= strtotime( $rule['to_time'] )
						&& strtotime( date( 'H:i', $end_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['to_time'] )
					) {
						return true;
					}
				}
				// If the time slots touches non bookable rule, return not available
				if ( $rule['is_bokable'] === 'no' ) {
					if (
						! ( strtotime( date( 'H:i', $start_time ) ) < strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['from_time'] ) )
						&& ! ( strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['to_time'] ) )
					) {
						return false;
					}
				}
				// if ( strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $start_time ) ) <= strtotime( $rule['to_time'] )
				// && strtotime( date( 'H:i', $end_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $end_time ) ) < strtotime( $rule['to_time'] ) ){
				// return $rule['is_bokable'] === 'yes';
				// }
			} elseif ( strpos( $rule['availability_type'], 'time-' ) !== false && ! empty( $rule['from_time'] ) ) {
				$day = explode( '-', $rule['availability_type'] );
				$day = $day[1];
				if ( $calendar_for == 'time-picker' && strtolower( date( 'D', $start_time ) ) == $day ) {
					return true;
				}

				// When the end time is 00:00 and need to check availability with the 23:59
				if ( date( 'H:i', $end_time ) == '00:00' && $start_time != $end_time ) {

					$end_time = strtotime( '-1 minute', $end_time );
				}

				if ( strtolower( date( 'D', $start_time ) ) == $day ) {
					if (
						$rule['is_bokable'] === 'yes'
						&& strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] )
						&& strtotime( date( 'H:i', $start_time ) ) <= strtotime( date( 'H:i', $end_time ) )
						&& strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['to_time'] )
					) {
						return true;
					}
					// If the time slots touches non bookable rule, return not available
					if ( $rule['is_bokable'] === 'no' ) {
						if (
							! ( strtotime( date( 'H:i', $start_time ) ) < strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['from_time'] ) )
							&& ! ( strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['to_time'] ) )
						) {
							return false;
						}
					}
				}
				// if( strtolower( date( 'D', $start_time) ) == $day
				// && strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] )
				// && strtotime( date( 'H:i', $end_time ) ) < strtotime( $rule['to_time'] ) ){
				// return $rule['is_bokable'] === 'yes';
				// }
			} elseif ( $rule['availability_type'] == 'date-range-and-time' ) {
				if ( $calendar_for == 'time-picker' || $calendar_for == 'time' ) {
					$date_from      = $rule['from_date_for_date_range_and_time'];
					$date_to        = $rule['to_date_for_date_range_and_time'];
					$date_from_hour = explode( ' ', $rule['from_date_for_date_range_and_time'] )[1];
					$date_to_hour   = explode( ' ', $rule['to_date_for_date_range_and_time'] )[1];

					if ( ! empty( $date_from ) && ! empty( $date_to ) ) {

						// When the end time is 00:00 and need to check availability with the 23:59
						if ( date( 'H:i', $end_time ) == '00:00' && $start_time != $end_time ) {

							$end_time = strtotime( '-1 minute', $end_time );
						}

						if ( $calendar_for == 'time-picker' ) {
							$from_date = explode( ' ', $rule['from_date_for_date_range_and_time'] )[0];
							$from_date = $from_date . ' 00:00';
							$to_date   = explode( ' ', $rule['to_date_for_date_range_and_time'] )[0];
							$to_date   = $to_date . ' 00:00';
							if ( $start_time >= strtotime( $from_date ) && $start_time <= strtotime( $to_date ) ) {
								return true;
							}
						} elseif ( ( $start_time >= strtotime( $date_from ) && $start_time <= strtotime( $date_to ) )
							|| ( $end_time >= strtotime( $date_from ) && $end_time <= strtotime( $date_to ) )
						) {

							$start_time_date           = explode( ' ', date( 'Y-m-d H:i', $start_time ) )[0];
							$start_time_date_with_rule = $start_time_date . ' ' . $date_from_hour;
							$end_time_date             = explode( ' ', date( 'Y-m-d H:i', $end_time ) )[0];
							$end_time_date_with_rule   = $end_time_date . ' ' . $date_to_hour;

							if ( $rule['is_bokable'] === 'yes' ) {
								// Issue: Extra Non-Available Slot Displayed - Rule end date should not be next date so, taking date part from start_time
								$end_time_date           = explode( ' ', date( 'Y-m-d H:i', $start_time ) )[0];
								$end_time_date_with_rule = $end_time_date . ' ' . $date_to_hour;

								if ( ( $start_time >= strtotime( $start_time_date_with_rule ) && $start_time < strtotime( $end_time_date_with_rule ) )
									&& ( $end_time >= strtotime( $start_time_date_with_rule ) && $end_time <= strtotime( $end_time_date_with_rule ) )
								) {
									// The slot is valid only if it falls perfectly between the rule timings
									return true;
								}
							} elseif ( ( $start_time > strtotime( $start_time_date_with_rule ) && $start_time < strtotime( $end_time_date_with_rule ) )
								|| ( $end_time > strtotime( $start_time_date_with_rule ) && $end_time < strtotime( $end_time_date_with_rule ) )
							) {

								// The slot is invalid only if it falls even partially between the rule timings
								return false;
							}
						}
					}
				}
			}
		}

		if ( $this->unavailable_default == 'yes' ) {

			return false;
		} else {
			return true;
		}
	}

	public function get_farthest_time( $time_1, $time_2 ) {
		if ( ! $time_1 || ! $time_2 ) {
			return '';
		}
		return $time_1 > $time_2 ? $time_1 : $time_2;
	}

	/**
	 * Check if Asset is available against Assets availability rules
	 *
	 * @return bool
	 */
	private function get_asset_next_available_time( $start_time, $interval = '' ) {

		// Filter to allow skip availability rules for admin(support Disable Avoid Last Minute Booking For Admin Bookings Addon)
		$ignore_asset_availability_rules = apply_filters( 'ph_bookings_skip_availability_rules_for_admin', false, 'asset' );

		// If the filter returns true, check for availability rules are ignored.
		if ( $ignore_asset_availability_rules ) {

			return $start_time;
		}

		// Ticket #179905
		if ( $this->assets_enabled == 'yes' && ! empty( $start_time ) ) {

			if ( is_object( $this->asset_obj ) ) {

				return $this->asset_obj->get_next_available_time( $start_time, $interval, $this->interval_period );
			}
		}
		return $start_time;
	}

	/**
	 * Get the Next Available Time based on the NON BOOKABLE availability rule
	 * If the next available time is for the next day, return '' indicating that no next time is available for the current day
	 *
	 * @return String
	 */
	public function get_next_available_time_based_on_non_bookable_availability_rule( $start_time, $end_time, $from_time, $to_time ) {
		$date_to_return = strtotime( $to_time );
		if ( $this->phive_enable_buffer == 'yes' && $this->buffer_before_time ) {
			$date_to_return = strtotime( "+$this->buffer_before_time $this->interval_period", $date_to_return );
		}

		if ( ( strtotime( date( 'Y-m-d', $start_time ) ) != strtotime( date( 'Y-m-d', $date_to_return ) ) ) || ! $this->is_working_time( $date_to_return, $this->booking_pId ) ) {
			// If the next time available is for the next day,
			// return '' indicating that no next time is available for the current day
			$date_to_return = '';
		}
		// check return date is start time date, else return ''
		return $date_to_return;
	}

	/**
	 * Get Next Available Time based on BOOKABLE Availability Rule
	 *
	 * @return String
	 */
	public function get_next_available_time_based_on_bookable_availability_rule( $start_time, $end_time, $from_time, $to_time, $product_id, $interval ) {
		// If current time slot is already available, no enhancement is required
		if ( $this->is_available( $start_time, $interval, $product_id, 'time' ) ) {
			return $start_time;
		}

		// Time Slot starts before Rule
		if ( strtotime( date( 'Y-m-d H:i', $start_time ) ) < strtotime( $from_time ) ) {
			if ( strtotime( $end_time ) <= strtotime( $to_time ) ) {
				// Time Slot ends during the Rule
				if ( $this->phive_enable_buffer == 'yes' && $this->buffer_before_time ) {
					return strtotime( "+$this->buffer_before_time $this->interval_period", strtotime( $from_time ) );
				}
				return strtotime( $from_time );
			}
			// Time Slot ends after the Rule
			return strtotime( $end_time );
		}
		if ( strtotime( $end_time ) <= strtotime( $to_time ) ) {
			// Time Slot ends during the Rule
			return $start_time;
		}
		// Time Slot ends after the Rule
		return strtotime( $end_time );
	}

	/**
	 * Check whether the Time Slot falls under any Availability Rule,
	 * If so, enhance the Start Time of the Time Slot if required.
	 * Buffer before slot is also added if start time is enhanced.
	 * The first rule is given highest priority
	 *
	 * @return String
	 */
	public function get_next_available_time( $start_time, $interval = '', $product_id = '' ) {

		// Filter to allow skip availability rules for admin(support Disable Avoid Last Minute Booking For Admin Bookings Addon)
		$ignore_booking_availability_rules = apply_filters( 'ph_bookings_skip_availability_rules_for_admin', false, 'booking' );
		
		// If the filter returns true, check for availability rules are ignored.
		if ( $ignore_booking_availability_rules ) {

			return $start_time;
		}

		$end_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );
		foreach ( $this->availability_rules as $key => $rule ) {

			if ( $rule['availability_type'] == 'custom' ) {
				$date_from = $rule['from_date'];
				$date_to   = $rule['to_date'];
				if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
					if (
						! ( $start_time < strtotime( $date_from ) && strtotime( $end_time ) <= strtotime( $date_from ) )
						&& ! ( $start_time >= strtotime( $date_to ) )
					) {
						// If current time slot falls in current rule, return the next available slot corresponding to the current rule.
						if ( $rule['is_bokable'] == 'no' ) {
							return $this->get_next_available_time_based_on_non_bookable_availability_rule( $start_time, $end_time, $date_from, $date_to, $interval );
						}
						return $this->get_next_available_time_based_on_bookable_availability_rule( $start_time, $end_time, $date_from, $date_to, $product_id, $interval );
					}
				}
			} elseif ( $rule['availability_type'] == 'time-all' && ! empty( $rule['from_time'] ) ) {
				$from_time_current_day = date( 'Y-m-d ' . $rule['from_time'], $start_time );
				$to_time_current_day   = date( 'Y-m-d ' . $rule['to_time'], $start_time );
				if (
					! ( strtotime( date( 'H:i', $start_time ) ) < strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', strtotime( $end_time ) ) ) <= strtotime( $rule['from_time'] ) )
					&& ! ( strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['to_time'] ) )
				) {
					// If current time slot falls in current rule, return the next available slot corresponding to the current rule.
					if ( $rule['is_bokable'] == 'no' ) {
						return $this->get_next_available_time_based_on_non_bookable_availability_rule( $start_time, $end_time, $from_time_current_day, $to_time_current_day, $interval );
					}
					return $this->get_next_available_time_based_on_bookable_availability_rule( $start_time, $end_time, $from_time_current_day, $to_time_current_day, $product_id, $interval );
				}
			} elseif ( strpos( $rule['availability_type'], 'time-' ) !== false && ! empty( $rule['from_time'] ) ) {
				$day = explode( '-', $rule['availability_type'] );
				$day = $day[1];
				if ( strtolower( date( 'D', $start_time ) ) == $day ) {
					if (
						! ( strtotime( date( 'H:i', $start_time ) ) < strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', strtotime( $end_time ) ) ) <= strtotime( $rule['from_time'] ) )
						&& ! ( strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['to_time'] ) )
					) {
						// If current time slot falls in current rule, return the next available slot corresponding to the current rule.
						$from_time_current_day = date( 'Y-m-d ' . $rule['from_time'], $start_time );
						$to_time_current_day   = date( 'Y-m-d ' . $rule['to_time'], $start_time );
						if ( $rule['is_bokable'] == 'no' ) {
							return $this->get_next_available_time_based_on_non_bookable_availability_rule( $start_time, $end_time, $from_time_current_day, $to_time_current_day, $interval );
						}
						return $this->get_next_available_time_based_on_bookable_availability_rule( $start_time, $end_time, $from_time_current_day, $to_time_current_day, $product_id, $interval );
					}
				}
			} elseif ( $rule['availability_type'] == 'months' && ! empty( $rule['from_month'] ) ) {
				$range_arr = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );
				if (
					$this->is_in_range( $range_arr, date( 'n', $start_time ), $rule['from_month'], $rule['to_month'] )
					&& $this->is_in_range( $range_arr, date( 'n', strtotime( $end_time ) ), $rule['from_month'], $rule['to_month'] )
				) {
					// If current time slot falls in current rule, return the next available slot corresponding to the current rule.
					if ( $rule['is_bokable'] == 'no' ) {
						return '';
					}
					return $start_time;
				}
			} elseif ( $rule['availability_type'] == 'days' && ! empty( $rule['from_week_day'] ) ) {
				$range_arr = array( 1, 2, 3, 4, 5, 6, 7 );
				if (
					$this->is_in_range( $range_arr, date( 'N', $start_time ), $rule['from_week_day'], $rule['to_week_day'] )
					&& $this->is_in_range( $range_arr, date( 'N', strtotime( $end_time ) ), $rule['from_week_day'], $rule['to_week_day'] )
				) {
					// If current time slot falls in current rule, return the next available slot corresponding to the current rule.
					if ( $rule['is_bokable'] == 'no' ) {
						return '';
					}
					return $start_time;
				}
			} elseif ( $rule['availability_type'] == 'date-range-and-time' ) {
				$date_from = $rule['from_date_for_date_range_and_time'];
				$date_to   = $rule['to_date_for_date_range_and_time'];
				if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
					if ( ( $start_time >= strtotime( $date_from ) && $start_time <= strtotime( $date_to ) )
						|| ( strtotime( $end_time ) >= strtotime( $date_from ) && strtotime( $end_time ) <= strtotime( $date_to ) )
					) {
						$date_from_hour            = explode( ' ', $rule['from_date_for_date_range_and_time'] )[1];
						$date_to_hour              = explode( ' ', $rule['to_date_for_date_range_and_time'] )[1];
						$start_time_date           = explode( ' ', date( 'Y-m-d H:i', $start_time ) )[0];
						$start_time_date_with_rule = $start_time_date . ' ' . $date_from_hour;
						$end_time_date             = explode( ' ', $end_time )[0];
						$end_time_date_with_rule   = $end_time_date . ' ' . $date_to_hour;

						if (
							! ( $start_time < strtotime( $start_time_date_with_rule ) && strtotime( $end_time ) <= strtotime( $start_time_date_with_rule ) )
							&& ! ( $start_time >= strtotime( $end_time_date_with_rule ) )
						) {
							// If current time slot falls in current rule, return the next available slot corresponding to the current rule.
							if ( $rule['is_bokable'] == 'no' ) {
								return $this->get_next_available_time_based_on_non_bookable_availability_rule( $start_time, $end_time, $start_time_date_with_rule, $end_time_date_with_rule, $interval );
							}
							return $this->get_next_available_time_based_on_bookable_availability_rule( $start_time, $end_time, $start_time_date_with_rule, $end_time_date_with_rule, $product_id, $interval );
						}
					}
				}
			}
		}

		return $start_time;
	}


	/**
	 * check if the given value is in given range of the array (Eg: Check if Sunday is in between Monday to Saturday)
	 *
	 * @param $full_ranges the range array with integer values
	 * @param $check_me the value to check if in range
	 * @param $lower_range Lower range
	 * @param $uppper_range Upper range
	 * @return bool
	 */
	private function is_in_range( $full_ranges, $check_me, $lower_range, $uppper_range ) {
		if ( $lower_range <= $uppper_range ) {
			return $check_me >= $lower_range && $check_me <= $uppper_range;
		} else {
			$count           = count( $full_ranges );
			$new_range_limit = $count - $lower_range + $count;
			$uppper_range   += $count;
			if ( $check_me < $lower_range ) {
				$check_me += $count;
			}

			$available_array = array();
			for ( $i = $lower_range; $i <= $uppper_range; $i++ ) {
				$available_array[] = $i;
			}

			return in_array( $check_me, $available_array );
		}
	}

	/**
	 * Check if already booked.
	 *
	 * @return bool
	 */
	public function is_booked_date( $date, $product_id ) {
		if ( empty( $this->booked_dates ) && $this->booked_dates_set == 0 ) {
			$this->booked_dates     = $this->get_all_bookings_for_product( $product_id );
			$this->booked_dates_set = 1;  // since, if no order is placed $this->booked_dates will always be empty
		}
		$found            = 0;
		$charge_per_night = get_post_meta( $product_id, '_phive_book_charge_per_night', 1 );
		foreach ( $this->booked_dates as $order_item_id => $booked_detail ) {
			if ( isset( $booked_detail['to'] ) && ( $charge_per_night == 'yes' ) && ( $this->interval_period == 'day' ) && $this->interval_type != 'fixed' ) {
				$booked_detail['to'] = date( 'Y-m-d', strtotime( '-1 day', strtotime( $booked_detail['to'] ) ) );
			}
			// if date in between booked from and to
			if ( ( isset( $booked_detail['from'] ) && isset( $booked_detail['to'] )
				&& $date >= strtotime( $booked_detail['from'] ) && $date <= strtotime( $booked_detail['to'] ) ) || ( isset( $booked_detail['Buffer_before_From'] ) && isset( $booked_detail['Buffer_before_To'] )
				&& $date >= strtotime( $booked_detail['Buffer_before_From'] ) && $date <= strtotime( $booked_detail['Buffer_before_To'] ) ) || ( isset( $booked_detail['Buffer_after_From'] ) && isset( $booked_detail['Buffer_after_To'] )
				&& $date >= strtotime( $booked_detail['Buffer_after_From'] ) && $date <= strtotime( $booked_detail['Buffer_after_To'] ) ) ) {
				if ( ! empty( $booked_detail['person_as_booking'] ) ) {
					$person_as_booking = maybe_unserialize( $booked_detail['person_as_booking'] );
					if ( ! empty( $person_as_booking[0] ) && ( $person_as_booking[0] == 'yes' ) && isset( $booked_detail['Number of persons'] ) && is_numeric( $booked_detail['Number of persons'] ) ) {
						$found += $booked_detail['Number of persons'];
					} else {
						++$found;
					}
				} else {
					++$found;
				}
				// if reached maximum allowed booking.
				if ( $found >= $this->allowd_per_slot ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * check if there is slot for book whole period (if the min booking is set).
	 *
	 * @param $date: date to check bookable
	 * @param $intravl_period: Day/Month
	 * @param $product_id
	 * @return Bool
	 */
	private function is_bookable( $date, $intravl_period, $product_id, $calendar_for = '' ) {

		if ( $this->interval_type !== 'fixed' ) {
			return true;
		}

		if ( ! empty( $this->interval ) && $this->interval > 1 ) {
			for ( $i = 1; $i < $this->interval; $i++ ) {
				$date = strtotime( date( 'Y-m-d', strtotime( '+' . $intravl_period, $date ) ) );

				// if already taken or not avialable (Set as not available in available table)
				if ( $this->is_booked_date( $date, $product_id ) || ! $this->is_available( $date, '', $product_id, $calendar_for ) ) {
					return false;
				}

				// Asset Availability Checking
				if ( $this->assets_enabled == 'yes' && $this->assets_auto_assign == 'yes') {

					$asset_id  = $this->get_most_matching_asset_for_slots( $date, '', $product_id );
					$asset_obj = $this->get_asset_obj( $asset_id, $date );

					if ( ! empty( $asset_obj ) ) {

						$asset_availability = $this->get_asset_availability( $asset_id, $date, 'false', $product_id );
						if ( $asset_availability < 1 ) {
							return false;
						}
					}
				}
			}
		}
		return true;
	}

	public function get_number_of_available_slot( $date, $product_id, $asset_id = false, $ignore_freezed = false, $auto_asset_id_available = 0, $ignore_asset = false ) {
		// 96421
		$display_settings       = get_option( 'ph_bookings_display_settigns' );
		$use_availability_table = ( isset( $display_settings['calculate_availability_using_availability_table'] ) && $display_settings['calculate_availability_using_availability_table'] == 'yes' ) ? true : false;
		$is_new_site            = get_option( 'ph_migration_new_site_v3_0_0', 'no' );
		if ( ( $use_availability_table || $is_new_site == 'yes' ) && Phive_Bookings_Database::ph_availability_table_exists() ) {
			$remaining_count = Ph_Booking_Manage_Availability_Data::ph_get_number_of_available_slot( $date, $product_id, $asset_id, $ignore_freezed, '', $ignore_asset );
			return $remaining_count;
		}
		if ( empty( $this->booked_dates ) && $this->booked_dates_set == 0 ) {
			$this->booked_dates     = $this->get_all_bookings_for_product( $product_id, $ignore_freezed );
			$this->booked_dates_set = 1;
		}
		$charge_per_night = get_post_meta( $product_id, '_phive_book_charge_per_night', 1 );
		$found            = 0;
		// TODO: Optimize the if else ladder.
		foreach ( $this->booked_dates as $order_item_id => $booked_detail ) {

			if ( isset( $booked_detail['to'] ) && ( $charge_per_night == 'yes' ) && ( $this->interval_period == 'day' ) && $this->interval_type != 'fixed' ) {
				$booked_detail['to'] = date( 'Y-m-d', strtotime( '-1 day', strtotime( $booked_detail['to'] ) ) );
			}

			if ( isset( $booked_detail['to'] ) ) {
				// 138547
				$booked_detail['to'] = apply_filters( 'ph_modify_to_date_booked_for_product_availability_calculation', $booked_detail['to'], $booked_detail['from'], $product_id, '', '', $booked_detail );
			}

			if (
				isset( $booked_detail['from'] ) && isset( $booked_detail['to'] )
				&& $date >= strtotime( $booked_detail['from'] ) && $date <= strtotime( $booked_detail['to'] )
			) {
				if ( isset( $booked_detail['person_as_booking'] ) ) {
					$person_as_booking = maybe_unserialize( $booked_detail['person_as_booking'] );
					if ( ! empty( $person_as_booking[0] ) && ( $person_as_booking[0] == 'yes' ) && isset( $booked_detail['Number of persons'] ) && is_numeric( $booked_detail['Number of persons'] ) ) {
						$found += apply_filters( 'ph_bookings_check_participant_adjustment', $booked_detail['Number of persons'], $booked_detail, $product_id );
					} else {
						++$found;
					}
				} else {
					++$found;
				}
			} elseif (
				isset( $booked_detail['Buffer_before_From'] ) && isset( $booked_detail['Buffer_before_To'] )
				&& $date >= strtotime( $booked_detail['Buffer_before_From'] ) && $date <= strtotime( $booked_detail['Buffer_before_To'] )
			) {
				if ( isset( $booked_detail['person_as_booking'] ) ) {
					$person_as_booking = maybe_unserialize( $booked_detail['person_as_booking'] );
					if ( ! empty( $person_as_booking[0] ) && ( $person_as_booking[0] == 'yes' ) && isset( $booked_detail['Number of persons'] ) && is_numeric( $booked_detail['Number of persons'] ) ) {
						$found += apply_filters( 'ph_bookings_check_participant_adjustment', $booked_detail['Number of persons'], $booked_detail, $product_id );
					} else {
						++$found;
					}
				} else {
					++$found;
				}
			} elseif ( isset( $booked_detail['Buffer_after_From'] ) && isset( $booked_detail['Buffer_after_To'] ) ) {
				if ( $charge_per_night == 'yes' && ( $this->interval_period == 'day' ) ) {
					$buffer_after_to   = date( 'Y-m-d', strtotime( '-1 day', strtotime( $booked_detail['Buffer_after_To'] ) ) );
					$buffer_after_from = date( 'Y-m-d', strtotime( '-1 day', strtotime( $booked_detail['Buffer_after_From'] ) ) );
				} else {
					$buffer_after_to   = $booked_detail['Buffer_after_To'];
					$buffer_after_from = $booked_detail['Buffer_after_From'];
				}
				if ( $date >= strtotime( $buffer_after_from ) && $date <= strtotime( $buffer_after_to ) && $this->interval_period != 'minute' ) {
					if ( isset( $booked_detail['person_as_booking'] ) ) {
						$person_as_booking = maybe_unserialize( $booked_detail['person_as_booking'] );
						if ( ! empty( $person_as_booking[0] ) && ( $person_as_booking[0] == 'yes' ) && isset( $booked_detail['Number of persons'] ) && is_numeric( $booked_detail['Number of persons'] ) ) {

							$found += apply_filters( 'ph_bookings_check_participant_adjustment', $booked_detail['Number of persons'], $booked_detail, $product_id );
						} else {
							++$found;
						}
					} else {
						++$found;
					}
				} elseif ( $date >= strtotime( $buffer_after_from ) && $date < strtotime( $buffer_after_to ) && $this->interval_period == 'minute' ) {
					if ( isset( $booked_detail['person_as_booking'] ) ) {
						$person_as_booking = maybe_unserialize( $booked_detail['person_as_booking'] );
						if ( ! empty( $person_as_booking[0] ) && ( $person_as_booking[0] == 'yes' ) && isset( $booked_detail['Number of persons'] ) && is_numeric( $booked_detail['Number of persons'] ) ) {

							$found += apply_filters( 'ph_bookings_check_participant_adjustment', $booked_detail['Number of persons'], $booked_detail, $product_id );
						} else {
							++$found;
						}
					} else {
						++$found;
					}
				}
			}
			$found = apply_filters( 'ph_bookings_total_bookings_count', $found, $product_id, $booked_detail, $date, $this->allowd_per_slot );
		}

		$availability = $this->allowd_per_slot - $found;
		if ( $availability <= 0 ) {
			$asset_id = isset( $asset_id ) ? $asset_id : '';
			return apply_filters( 'ph_bookings_total_booking_availability', max( 0, $availability ), $product_id, $date, $this->booked_dates, $asset_id );
		}
		if ( $this->assets_enabled == 'yes' && $ignore_asset == false ) {
			if ( $this->assets_auto_assign == 'yes' ) {
				if ( $auto_asset_id_available == 1 && $asset_id != '' ) {
					$this->asset_obj = $this->get_asset_obj( $asset_id, $date );
				} else {
					$asset_id        = $this->get_most_matching_asset_for_slots( $date, '', $product_id, $ignore_freezed, $auto_asset_id_available );
					$this->asset_obj = $this->get_asset_obj( $asset_id, $date );
				}
			}

			if ( ! empty( $this->asset_obj ) ) {
				$asset_availability = $this->get_asset_availability( $asset_id, $date, $ignore_freezed, $product_id );
				$availability       = min( $asset_availability, $availability );
			} else {
				$availability = 0;
			}
		}
		$asset_id = isset( $asset_id ) ? $asset_id : '';
		return apply_filters( 'ph_bookings_total_booking_availability', max( 0, $availability ), $product_id, $date, $this->booked_dates, $asset_id );
	}

	private function get_most_matching_asset_for_slots( $from, $to = '', $product_id = '', $ignore_freezed = false, $auto_asset_id_available = 0 ) {

		$display_settings       = get_option( 'ph_bookings_display_settigns' );
		$use_availability_table = ( isset( $display_settings['calculate_availability_using_availability_table'] ) && $display_settings['calculate_availability_using_availability_table'] == 'yes' ) ? true : false;
		$is_new_site            = get_option( 'ph_migration_new_site_v3_0_0', 'no' );

		if ( ( $use_availability_table || $is_new_site == 'yes' ) && Phive_Bookings_Database::ph_availability_table_exists() ) {

			return Ph_Booking_Manage_Availability_Data::get_asset_id( $from, '', $product_id, false, '' );
		}
		$interval_string = "$this->interval $this->interval_period";
		// $asset_fount = '';
		$asset_fount = false;
		// Loop through booked slots, find asset which available for all slot.
		foreach ( $this->assets_pricing_rules as $key => $rule ) {
			if ( empty( $rule['ph_booking_asset_id'] ) ) {
				continue;
			}

			$current_time = ph_strtotime( $from );
			$book_to      = empty( $to ) ? $current_time : ph_strtotime( $to );
			$loop_breaker = 500;
			while ( ! empty( $current_time ) && $current_time <= $book_to && $loop_breaker > 0 ) {
				$asset_availability = $this->get_asset_availability( $rule['ph_booking_asset_id'], $current_time, $ignore_freezed, $product_id );
				$this->asset_obj    = $this->get_asset_obj( $rule['ph_booking_asset_id'], $from );
				if ( $asset_availability == 0 || ! $this->asset_obj->is_available( $current_time ) ) {
					$asset_fount = false;
					continue 2;
				}
				$asset_fount  = $rule['ph_booking_asset_id'];
				$current_time = strtotime( "+$interval_string", $current_time );

				--$loop_breaker;
			}
			if ( ! empty( $asset_fount ) ) {
				return $asset_fount;
			}
		}
		// return false;
		if ( ( count( $this->assets_pricing_rules ) > 0 ) && ( $auto_asset_id_available == 1 ) ) {
			$asset_fount = $this->assets_pricing_rules[0]['ph_booking_asset_id'];
			// error_log('asset_fount : '.$asset_fount);
		}
		return $asset_fount;
	}

	private function get_asset_availability( $asset_id, $date, $ignore_freezed = false, $product_id = '' ) {

		$this->asset_obj = new phive_booking_assets( $asset_id );

		switch ( $this->interval_period ) {
			case 'day':
				$interval_string = '+1 day';
				$format          = 'Y-m-d';
				break;

			case 'hour':
			case 'minute':
				$interval_string = '+' . $this->interval . ' ' . $this->interval_period;
				$format          = 'Y-m-d H:i';
				break;

			case 'month':
				$interval_string = '+1 month';
				$format          = 'Y-m-d';
				break;
		}
		$from = date( $format, $date );
		$to   = date( $format, strtotime( $interval_string, $date ) );

		$asset_availability = $this->asset_obj->get_availability( $from, $to, $ignore_freezed, $this->interval_period, $product_id );
		return $asset_availability;
	}


	private function get_booking_start_days( $product_id ) {
		if ( empty( $this->restrict_start_day ) ) {
			$this->restrict_start_day = get_post_meta( $product_id, '_phive_restrict_start_day', 1 );
		}
		if ( $this->restrict_start_day === 'yes' ) {
			if ( empty( $this->booking_start_days ) ) {
				$this->booking_start_days = get_post_meta( $product_id, '_phive_booking_start_days', 1 );
			}
		} else {
			$this->booking_start_days = array( 1, 2, 3, 4, 5, 6, 7 );
		}
		return $this->booking_start_days;
	}

	private function is_working_time( $date, $product_id ) {
		$time = strtotime( date( 'H:i', $date ) );
		// if time falls in working hours
		$this->shop_opening_time = apply_filters( 'ph_modify_shop_opening_time', $this->shop_opening_time, $product_id, $date );
		$this->shop_closing_time = apply_filters( 'ph_modify_shop_closing_time', $this->shop_closing_time, $product_id, $date );

		if ( ( empty( $this->shop_opening_time ) && empty( $this->shop_closing_time ) )
			|| ! empty( $this->shop_opening_time ) && ! empty( $this->shop_closing_time ) && $time >= strtotime( $this->shop_opening_time ) && $time <= strtotime( $this->shop_closing_time )
			|| empty( $this->shop_opening_time ) && ! empty( $this->shop_closing_time ) && $time <= strtotime( $this->shop_closing_time )
			|| ! empty( $this->shop_opening_time ) && empty( $this->shop_closing_time ) && $time >= strtotime( $this->shop_opening_time )
		) {
			return true;
		}
		return false;
	}

	private function phive_set_product_properties( $product_id ) {
		$this->first_availability_type = get_post_meta( $product_id, '_phive_first_availability_type', 1 );
		$this->shop_opening_time       = get_post_meta( $product_id, '_phive_book_working_hour_start', 1 );
		$this->shop_closing_time       = get_post_meta( $product_id, '_phive_book_working_hour_end', 1 );
		$this->fixed_availability_from = get_post_meta( $product_id, '_phive_fixed_availability_from', 1 );
		$this->fixed_availability_to   = get_post_meta( $product_id, '_phive_fixed_availability_to', 1 );

		$this->interval_type          = get_post_meta( $product_id, '_phive_book_interval_type', 1 );
		$this->interval               = get_post_meta( $product_id, '_phive_book_interval', 1 );
		$this->interval_period        = get_post_meta( $product_id, '_phive_book_interval_period', 1 );
		$this->cancel_interval        = get_post_meta( $product_id, '_phive_cancel_interval', 1 );
		$this->cancel_interval_period = get_post_meta( $product_id, '_phive_cancel_interval_period', 1 );
		// $this->additinal_notes_label = get_post_meta( $product_id, "_phive_additional_notes_label", 1 );
		/*
		$this->ph_checkin             = get_post_meta( $product_id, "_phive_book_checkin", 1 );
		$this->ph_checkout              = get_post_meta( $product_id, "_phive_book_checkout", 1 );*/

		$this->first_availability = get_post_meta( $product_id, '_phive_first_availability', 1 );

		// customisation support
		$this->first_availability = apply_filters( 'ph_bookings_modify_first_availability', $this->first_availability, $product_id );

		$this->last_availability                  = get_post_meta( $product_id, '_phive_last_availability', 1 );
		$this->first_availability_interval_period = get_post_meta( $product_id, '_phive_first_availability_interval_period', 1 );
		$this->last_availability_interval_period  = get_post_meta( $product_id, '_phive_last_availability_interval_period', 1 );
		$this->allowd_per_slot                    = get_post_meta( $product_id, '_phive_book_allowed_per_slot', 1 );

		$this->person_enabled           = get_post_meta( $product_id, '_phive_booking_person_enable', 1 );
		$this->persons_pricing_rules    = get_post_meta( $product_id, '_phive_booking_persons_pricing_rules', 1 );
		$this->reources_enabled         = get_post_meta( $product_id, '_phive_booking_resources_enable', 1 );
		$this->resources_pricing_rules  = get_post_meta( $product_id, '_phive_booking_resources_pricing_rules', 1 );
		$this->additional_notes_enabled = get_post_meta( $product_id, '_phive_book_additional_notes', 1 );
		$this->additional_notes_label   = get_post_meta( $product_id, '_phive_additional_notes_label', 1 );
		$this->is_add_notes_mandatory 	= get_post_meta( $product_id, '_phive_add_notes_is_mandatory', 1 );
		$this->buffer_before_time       = get_post_meta( $product_id, '_phive_buffer_before', 1 );
		$this->buffer_after_time        = get_post_meta( $product_id, '_phive_buffer_after', 1 );
		$this->buffer_period            = get_post_meta( $product_id, '_phive_buffer_period', 1 );
		$this->phive_enable_buffer      = get_post_meta( $product_id, '_phive_enable_buffer', 1 );
		$this->assets_enabled           = get_post_meta( $product_id, '_phive_booking_assets_enable', 1 );
		$this->assets_label             = get_post_meta( $product_id, '_phive_booking_assets_label', 1 );
		$this->assets_label             = ph_wpml_translate_single_string( 'Assets_Main_Label', $this->assets_label );
		$this->assets_pricing_rules     = get_post_meta( $product_id, '_phive_booking_assets_pricing_rules', 1 );
		$this->assets_auto_assign       = get_post_meta( $product_id, '_phive_booking_assets_auto_assign', 1 );
		$asset_settings                 = get_option( 'ph_booking_settings_assets', 1 );
		$this->assets_rules             = ( ( ! empty( $asset_settings ) ) && ( isset( $asset_settings['_phive_booking_assets'] ) ) ) ? $asset_settings['_phive_booking_assets'] : array();
		$this->zone                     = get_option( 'gmt_offset' );
		$this->set_availability_rule( $product_id );

		$this->additional_notes_label = ph_wpml_translate_single_string( 'Additional_Notes_Label', $this->additional_notes_label );

		if ( empty( $this->assets_pricing_rules ) ) {
			$this->assets_pricing_rules = array();
		}
		if ( empty( $this->allowd_per_slot ) ) {
			$this->allowd_per_slot = 1;
		}
		if ( empty( $this->first_availability ) ) {
			$this->first_availability = 0;
		}
		if ( empty( $this->last_availability ) ) {
			$this->last_availability = 0;
		}
	}

	private function set_availability_rule( $product_id ) {
		
		$availability_rules  		= get_post_meta( $product_id, '_phive_booking_availability_rules', 1 );
		$availability_rules 		= !empty($availability_rules) ? $availability_rules : array();
		
		$glb_avail_settings 		= get_option( 'ph_booking_settings_availability', 1 );
		
		// Dokan Integration - Vendor global availability
		$glb_avail_settings 		= apply_filters( 'ph_modify_bookings_global_availability_settings', $glb_avail_settings, $product_id );
		
		$global_availability_rules  	= !empty($glb_avail_settings) && isset($glb_avail_settings['_phive_booking_availability_rules']) ? $glb_avail_settings['_phive_booking_availability_rules'] : array();
		
		$this->unavailable_default 	= get_post_meta( $product_id, '_phive_un_availability', 1 );
		$availability_level 		= 'product-level';

		if (!empty($availability_rules) && !empty($global_availability_rules) && isset($glb_avail_settings['use_glb_and_prod_avail']) && 'yes' == $glb_avail_settings['use_glb_and_prod_avail']) {
			
			$availability_rules = array_merge($availability_rules, $global_availability_rules);
			$availability_level = 'product-and-global-level';
		} else if (empty($availability_rules) && !empty($global_availability_rules)) {

			$availability_rules 		= $global_availability_rules;
			$this->unavailable_default 	= !empty($glb_avail_settings) && isset($glb_avail_settings['_phive_un_availability']) ? $glb_avail_settings['_phive_un_availability'] : $this->unavailable_default;
			$availability_level = 'global-level';
		}
		
		$availability_rules        = apply_filters( 'ph_modify_final_availability_rules_for_a_product', $availability_rules, $product_id, $availability_level );
		$this->unavailable_default = apply_filters( 'ph_modify_final_unavailable_default_for_a_product', $this->unavailable_default, $product_id, $availability_level );

		$this->availability_rules = ! empty( $availability_rules ) ? $availability_rules : array();
	}

	private function get_asset_obj( $asset_id, $date ) {
		return new phive_booking_assets( $asset_id );
	}

	private function old_get_all_bookings_for_product( $product_id = '', $ignore_freezed = false ) {
		global $wpdb;

		$logger  = wc_get_logger();
		$context = array( 'source' => 'PH-BOOKINGS' );
		$logger->debug( '=====================================', $context );
		$logger->debug( '=====================================', $context );
		$logger->debug( 'OLD query', $context );

		if ( ! $product_id ) {
			$product_id = $product->get_id();
		}
		$allowd_per_slot = get_post_meta( $product_id, '_phive_book_allowed_per_slot', 1 );

		$ph_booking_placed_limit = apply_filters( 'ph_get_all_bookings_booked_dates_query_limit', 1000, $product_id, $ignore_freezed );

		$ph_booking_in_cart_limit = apply_filters( 'ph_get_all_bookings_from_cart_query_limit', 300, $product_id, $ignore_freezed );

		$start_time       = microtime( true );
		$start_time_total = microtime( true );

		// Query for getting booked dates
		$query  = "SELECT meta_key , meta_value , order_item_id
			FROM {$wpdb->prefix}woocommerce_order_itemmeta AS t1
			WHERE t1.order_item_id
			IN (
				SELECT order_item_id
				FROM {$wpdb->prefix}woocommerce_order_itemmeta
				WHERE	(meta_key = '_ph_booking_dlang_product_id' OR meta_key = '_product_id')
				AND	meta_value = $product_id
			)AND ( 
				meta_key = 'From'
				OR meta_key = 'To'
				OR meta_key = 'canceled'
				OR meta_key = 'person_as_booking'
				OR meta_key = 'Number of persons'
				OR meta_key = 'Buffer_before_From'
				OR meta_key = 'Buffer_before_To'
				OR meta_key = 'Buffer_after_From'
				OR meta_key = 'Buffer_after_To'  
			)
			
			ORDER BY	t1.order_item_id DESC LIMIT 0,$ph_booking_placed_limit";
		$query  = apply_filters( 'ph_bookings_all_bookings_query', $query );
		$booked = $wpdb->get_results( $query, OBJECT );

		$end_time       = microtime( true );
		$execution_time = ( $end_time - $start_time );
		$logger->debug( 'Database execution time', $context );
		$logger->debug( $execution_time, $context );
		$start_time = microtime( true );

		$freezed = array();
		if ( ! $ignore_freezed ) {
			// Query for getting freezed dates
			$query_post = "SELECT meta_key, meta_value, post_id as order_item_id
				FROM {$wpdb->prefix}postmeta AS t1
				WHERE t1.post_id
				IN (
					SELECT post_id
					FROM  {$wpdb->prefix}postmeta
					WHERE meta_key = '_product_id'
					AND meta_value = $product_id
				)AND ( 
					meta_key = 'From'
					OR meta_key = 'To'
					OR meta_key = 'person_as_booking'
					OR meta_key = 'Number of persons'
					OR meta_key = 'Buffer_before_From'
					OR meta_key = 'Buffer_before_To'
					OR meta_key = 'Buffer_after_From'
					OR meta_key = 'Buffer_after_To'
					OR meta_key = 'participants'
				)
				AND t1.post_id 
				NOT IN (select post_id
					from {$wpdb->prefix}postmeta
					where meta_key='ph_canceled'
					AND meta_value = '1')
				ORDER BY  t1.post_id DESC LIMIT 0,$ph_booking_in_cart_limit";
			$freezed    = $wpdb->get_results( $query_post, OBJECT );
		}

		$end_time       = microtime( true );
		$execution_time = ( $end_time - $start_time );
		$logger->debug( 'Freeze query execution time', $context );
		$logger->debug( $execution_time, $context );
		$start_time = microtime( true );

		$booked_array     = array_merge( $booked, $freezed );
		$processed        = array();
		$booked_date_time = array();
		$canceled         = array();
		foreach ( $booked as $key => $value ) {
			$order_item = new WC_Order_Item_Product( $value->order_item_id );
			// The order ID
			$order_id = $order_item->get_order_id();
			$order    = wc_get_order( $order_id );
			if ( $order === false ) {
				continue;
			}
			if ( $order->get_status() == 'refunded' ) {
				$canceled[ $value->order_item_id ] = '';
			}
		}
		$end_time       = microtime( true );
		$execution_time = ( $end_time - $start_time );
		$logger->debug( 'WC_ORDER LOOP time', $context );
		$logger->debug( $execution_time, $context );
		$start_time = microtime( true );

		foreach ( $booked_array as $key => $value ) {
			if ( $value->meta_key == 'From' ) {
				$from_date                                  = substr( $value->meta_value, 0, 10 );
				$processed[ $value->order_item_id ]['from'] = ph_maybe_unserialize( $value->meta_value );
			}
			if ( $value->meta_key == 'Number of persons' ) {
				$number_of_person                                        = substr( $value->meta_value, 0, 10 );
				$processed[ $value->order_item_id ]['Number of persons'] = $value->meta_value;
			}
			if ( $value->meta_key == 'person_as_booking' ) {
				$person_as_booking                                       = substr( $value->meta_value, 0, 10 );
				$processed[ $value->order_item_id ]['person_as_booking'] = $value->meta_value;
			}
			if ( $value->meta_key == 'To' ) {
				$processed[ $value->order_item_id ]['to'] = ph_maybe_unserialize( $value->meta_value );
			}
			if ( $value->meta_key == 'Buffer_before_From' ) {
				$processed[ $value->order_item_id ]['Buffer_before_From'] = $value->meta_value;
			}
			if ( $value->meta_key == 'Buffer_before_To' ) {
				$processed[ $value->order_item_id ]['Buffer_before_To'] = $value->meta_value;
			}
			if ( $value->meta_key == 'Buffer_after_From' ) {
				$processed[ $value->order_item_id ]['Buffer_after_From'] = $value->meta_value;
			}
			if ( $value->meta_key == 'Buffer_after_To' ) {
				$processed[ $value->order_item_id ]['Buffer_after_To'] = $value->meta_value;
			}
			$processed = apply_filters( 'ph_bookings_extra_key_value', $processed, $value->order_item_id, $value );
			if ( $value->meta_key == 'canceled' && $value->meta_value == 'yes' ) {
				$canceled[ $value->order_item_id ] = '';
			}
		}

		$end_time       = microtime( true );
		$execution_time = ( $end_time - $start_time );
		$logger->debug( 'boked_array loop execution time', $context );
		$logger->debug( $execution_time, $context );
		$start_time           = microtime( true );
		$eliminated_cancelled = array_diff_key( $processed, $canceled );

		// if TO is missing, concider FROM as TO
		foreach ( $eliminated_cancelled as $key => &$value ) {
			if ( empty( $value['to'] ) && ! empty( $value['from'] ) ) { // in the case of buffer, index 'from' wil be empty
				$value['to'] = $value['from'];
			}
		}

		$end_time       = microtime( true );
		$execution_time = ( $end_time - $start_time );
		$logger->debug( 'eliminated cancelled loop execution time', $context );
		$logger->debug( $execution_time, $context );
		$logger->debug( 'ph_booking_placed_limit', $context );
		$logger->debug( $ph_booking_placed_limit, $context );
		$logger->debug( 'booked count', $context );
		$logger->debug( count( $booked ), $context );
		$logger->debug( 'eliminated_cancelled', $context );
		$logger->debug( count( $eliminated_cancelled ), $context );
		$end_time       = microtime( true );
		$execution_time = ( $end_time - $start_time_total );
		$logger->debug( 'TOTAL: execution time', $context );
		$logger->debug( $execution_time, $context );
		return $eliminated_cancelled;
	}

	private function get_all_bookings_for_product( $product_id = '', $ignore_freezed = false ) {
		global $wpdb;
		// $this->old_get_all_bookings_for_product($product_id, $ignore_freezed);

		if ( ! $product_id ) {
			$product_id = $product->get_id();
		}
		$allowd_per_slot = get_post_meta( $product_id, '_phive_book_allowed_per_slot', 1 );

		$ph_booking_placed_limit  = apply_filters( 'ph_get_all_bookings_booked_dates_query_limit', 1000, $product_id, $ignore_freezed );
		$ph_booking_in_cart_limit = apply_filters( 'ph_get_all_bookings_from_cart_query_limit', 300, $product_id, $ignore_freezed );

		$woocommerce_table = PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled();

		// Query for getting booked dates
		if ( $woocommerce_table ) {

			$query = "SELECT oitems.order_id, oitems.order_item_id, tr.object_id product_id, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails, imeta.no_of_persons, imeta.person_as_booking, imeta.buffer_before_id, imeta.buffer_after_id
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
					MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails
					FROM {$wpdb->prefix}woocommerce_order_itemmeta
					GROUP BY order_item_id
			) as imeta on  imeta.order_item_id = oitems.order_item_id
			INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
			WHERE {$wpdb->prefix}wc_orders.type IN ('shop_order', 'shop_order_refund')
			AND (
				{$wpdb->prefix}wc_orders.status Like 'wc-pending'
				OR {$wpdb->prefix}wc_orders.status = 'wc-processing'
				OR {$wpdb->prefix}wc_orders.status = 'wc-on-hold'
				OR {$wpdb->prefix}wc_orders.status = 'wc-completed'
				OR {$wpdb->prefix}wc_orders.status = 'wc-partially-paid'
				OR {$wpdb->prefix}wc_orders.status = 'wc-partial-payment'
				OR {$wpdb->prefix}wc_orders.status = 'wc-ng-complete'
			)
			AND tt.taxonomy IN ('product_type')
			AND t.slug = 'phive_booking' 
			AND (imeta.ProductId = '$product_id' OR imeta.DefaultLangProductId = '$product_id')
			AND imeta.BookingStatus != 'a:1:{i:0;s:8:\"canceled\";}'
			AND imeta.BookingStatus != 'canceled'
			ORDER BY oitems.order_item_id  DESC LIMIT 0, $ph_booking_placed_limit";
		} else {

			$query = "SELECT oitems.order_id, oitems.order_item_id, tr.object_id product_id, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails, imeta.no_of_persons, imeta.person_as_booking, imeta.buffer_before_id, imeta.buffer_after_id
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
					MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails
					FROM {$wpdb->prefix}woocommerce_order_itemmeta
					GROUP BY order_item_id
			) as imeta on  imeta.order_item_id = oitems.order_item_id
			INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
			WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
			AND (
				{$wpdb->prefix}posts.post_status Like 'wc-pending'
				OR {$wpdb->prefix}posts.post_status = 'wc-processing'
				OR {$wpdb->prefix}posts.post_status = 'wc-on-hold'
				OR {$wpdb->prefix}posts.post_status = 'wc-completed'
				OR {$wpdb->prefix}posts.post_status = 'wc-partially-paid'
				OR {$wpdb->prefix}posts.post_status = 'wc-partial-payment'
				OR {$wpdb->prefix}posts.post_status = 'wc-ng-complete'
			)
			AND tt.taxonomy IN ('product_type')
			AND t.slug = 'phive_booking' 
			AND (imeta.ProductId = '$product_id' OR imeta.DefaultLangProductId = '$product_id')
			AND imeta.BookingStatus != 'a:1:{i:0;s:8:\"canceled\";}'
			AND imeta.BookingStatus != 'canceled'
			ORDER BY oitems.order_item_id  DESC LIMIT 0, $ph_booking_placed_limit";
		}

		$query  = apply_filters( 'ph_bookings_all_bookings_query', $query, $product_id, $ph_booking_placed_limit );
		$booked = $wpdb->get_results( $query, OBJECT );

		$freezed = array();

		$query_post = '';

		if ( ! $ignore_freezed ) {
			// Query for getting freezed dates
			if ( $woocommerce_table ) {

				$query_post = "SELECT meta_key, meta_value, order_id as order_item_id
				FROM {$wpdb->prefix}wc_orders_meta AS t1
				WHERE t1.order_id
				IN (
					SELECT order_id
					FROM  {$wpdb->prefix}wc_orders_meta
					WHERE meta_key = '_product_id'
					AND meta_value = $product_id
				)AND ( 
					meta_key = 'From'
					OR meta_key = 'To'
					OR meta_key = 'person_as_booking'
					OR meta_key = 'Number of persons'
					OR meta_key = 'Buffer_before_From'
					OR meta_key = 'Buffer_before_To'
					OR meta_key = 'Buffer_after_From'
					OR meta_key = 'Buffer_after_To'
					OR meta_key = 'participants'
				)
				AND t1.order_id 
				NOT IN (select order_id
					from {$wpdb->prefix}wc_orders_meta
					where meta_key='ph_canceled'
					AND meta_value = '1')
				ORDER BY  t1.order_id DESC LIMIT 0,$ph_booking_in_cart_limit";
			} else {

				$query_post = "SELECT meta_key, meta_value, post_id as order_item_id
					FROM {$wpdb->prefix}postmeta AS t1
					WHERE t1.post_id
					IN (
						SELECT post_id
						FROM  {$wpdb->prefix}postmeta
						WHERE meta_key = '_product_id'
						AND meta_value = $product_id
					)AND ( 
						meta_key = 'From'
						OR meta_key = 'To'
						OR meta_key = 'person_as_booking'
						OR meta_key = 'Number of persons'
						OR meta_key = 'Buffer_before_From'
						OR meta_key = 'Buffer_before_To'
						OR meta_key = 'Buffer_after_From'
						OR meta_key = 'Buffer_after_To'
						OR meta_key = 'participants'
					)
					AND t1.post_id 
					NOT IN (select post_id
						from {$wpdb->prefix}postmeta
						where meta_key='ph_canceled'
						AND meta_value = '1')
					ORDER BY  t1.post_id DESC LIMIT 0,$ph_booking_in_cart_limit";
			}

			$freezed = $wpdb->get_results( $query_post, OBJECT );
		}

		$processed        = array();
		$booked_date_time = array();
		$canceled         = array();

		foreach ( $booked as $key => $value ) {
			$order_item_id = $value->order_item_id;
			// $processed[$order_item_id]

			if ( isset( $value->BookFrom ) && ! empty( $value->BookFrom ) ) {
				$processed[ $order_item_id ]['from'] = ph_maybe_unserialize( $value->BookFrom );
			}

			if ( isset( $value->BookTo ) && ! empty( $value->BookTo ) ) {
				$processed[ $order_item_id ]['to'] = ph_maybe_unserialize( $value->BookTo );
			} elseif ( isset( $value->BookFrom ) && ! empty( $value->BookFrom ) ) {
				$processed[ $order_item_id ]['to'] = ph_maybe_unserialize( $value->BookFrom );
			}

			if ( isset( $value->person_as_booking ) && ! empty( $value->person_as_booking ) ) {
				$person_as_booking                                = substr( $value->person_as_booking, 0, 10 );
				$processed[ $order_item_id ]['person_as_booking'] = $value->person_as_booking;
			}

			if ( isset( $value->no_of_persons ) && ! empty( $value->no_of_persons ) ) {
				$number_of_person                                 = substr( $value->no_of_persons, 0, 10 );
				$processed[ $order_item_id ]['Number of persons'] = $value->no_of_persons;
			}

			$processed = apply_filters( 'ph_bookings_extra_key_value_new_query', $processed, $order_item_id, $value );
		}

		foreach ( $freezed as $key => $value ) {
			if ( $value->meta_key == 'From' ) {
				$from_date                                  = substr( $value->meta_value, 0, 10 );
				$processed[ $value->order_item_id ]['from'] = ph_maybe_unserialize( $value->meta_value );
			}
			if ( $value->meta_key == 'Number of persons' ) {
				$number_of_person                                        = substr( $value->meta_value, 0, 10 );
				$processed[ $value->order_item_id ]['Number of persons'] = $value->meta_value;
			}
			if ( $value->meta_key == 'person_as_booking' ) {
				$person_as_booking                                       = substr( $value->meta_value, 0, 10 );
				$processed[ $value->order_item_id ]['person_as_booking'] = $value->meta_value;
			}
			if ( $value->meta_key == 'To' ) {
				$processed[ $value->order_item_id ]['to'] = ph_maybe_unserialize( $value->meta_value );
			}
			if ( $value->meta_key == 'Buffer_before_From' ) {
				$processed[ $value->order_item_id ]['Buffer_before_From'] = $value->meta_value;
			}
			if ( $value->meta_key == 'Buffer_before_To' ) {
				$processed[ $value->order_item_id ]['Buffer_before_To'] = $value->meta_value;
			}
			if ( $value->meta_key == 'Buffer_after_From' ) {
				$processed[ $value->order_item_id ]['Buffer_after_From'] = $value->meta_value;
			}
			if ( $value->meta_key == 'Buffer_after_To' ) {
				$processed[ $value->order_item_id ]['Buffer_after_To'] = $value->meta_value;
			}
			$processed = apply_filters( 'ph_bookings_extra_key_value', $processed, $value->order_item_id, $value );
			if ( $value->meta_key == 'canceled' && $value->meta_value == 'yes' ) {
				$canceled[ $value->order_item_id ] = '';
			}
		}

		$eliminated_cancelled = array_diff_key( $processed, $canceled );

		$eliminated_cancelled = $processed;

		// if TO is missing, concider FROM as TO
		foreach ( $eliminated_cancelled as $key => &$value ) {
			if ( empty( $value['to'] ) && ! empty( $value['from'] ) ) { // in the case of buffer, index 'from' wil be empty
				$value['to'] = $value['from'];
			}
		}

		return $eliminated_cancelled;
	}

	/**
	 * Get the first available date
	 *
	 * @param string  $start_date
	 * @param int     $product_id
	 * @param string  $calendar_for
	 * @param boolean $return_matched_date
	 * @return string $curr_date
	 */
	public function get_first_available_date( $start_date, $product_id, $calendar_for = 'time-picker', $return_matched_date = false, $asset_obj = '' ) {

		$fixed_availability_from         = get_post_meta( $product_id, '_phive_fixed_availability_from', 1 );
		$fixed_availability_to           = get_post_meta( $product_id, '_phive_fixed_availability_to', 1 );
		$first_booking_availability_type = get_post_meta( $product_id, '_phive_first_booking_availability_type', 1 );
		$first_booking_availability_type = ! empty( $first_booking_availability_type ) ? $first_booking_availability_type : 'today';
		if ( ( ! empty( $fixed_availability_from ) || $first_booking_availability_type != 'first_available_date' ) && ! $return_matched_date ) {
			return $start_date;
		}
		$asset_id = false;
		if ( $this->assets_enabled == 'yes' ) {
			if ( $this->assets_auto_assign != 'yes' && ! empty( $this->assets_pricing_rules[0] ) ) {
				$asset_id        = $this->assets_pricing_rules[0]['ph_booking_asset_id'];
				$this->asset_obj = $this->get_asset_obj( $asset_id, $start_date );
			} else {
				$asset_id = '';
			}
		}

		// Assign if asset object is passed
		if ( is_object( $asset_obj ) && $asset_obj instanceof phive_booking_assets ) {

			$asset_id        = $asset_obj->id;
			$this->asset_obj = $asset_obj;
		}

		$curr_date = strtotime( date( 'Y-m-d' ) );
		$curr_date = strtotime( $start_date );
		// $calendar_for='time-picker';
		$this->shop_opening_time  = get_post_meta( $product_id, '_phive_book_working_hour_start', 1 );
		$this->shop_closing_time  = get_post_meta( $product_id, '_phive_book_working_hour_end', 1 );
		$this->interval           = get_post_meta( $product_id, '_phive_book_interval', 1 );
		$this->interval_period    = get_post_meta( $product_id, '_phive_book_interval_period', 1 );
		$this->zone               = get_option( 'gmt_offset' );
		$this->buffer_before_time = get_post_meta( $product_id, '_phive_buffer_before', 1 );
		$this->buffer_after_time  = get_post_meta( $product_id, '_phive_buffer_after', 1 );

		$buffer_period       = get_post_meta( $product_id, '_phive_buffer_period', 1 );
		$phive_enable_buffer = get_post_meta( $product_id, '_phive_enable_buffer', 1 );

		$end_date               = strtotime( date( 'Y-m-d', strtotime( '+3 year', $curr_date ) ) );
		$count                  = 0;
		$display_settings       = get_option( 'ph_bookings_display_settigns' );
		$use_availability_table = ( isset( $display_settings['calculate_availability_using_availability_table'] ) && $display_settings['calculate_availability_using_availability_table'] == 'yes' ) ? true : false;
		$is_new_site            = get_option( 'ph_migration_new_site_v3_0_0', 'no' );
		$settings               = Ph_Booking_Manage_Availability_Data::ph_get_product_settings( $product_id );

		while ( $curr_date <= $end_date && $count < 1100 ) {

			$date_booking_status = true;

			if ( ! empty( $asset_id ) ) {
				$availability = false;
				if ( ! empty( $this->asset_obj ) ) {

					if ( ( $use_availability_table || $is_new_site == 'yes' ) && Phive_Bookings_Database::ph_availability_table_exists() ) {
						$asset_availability = Ph_Booking_Manage_Availability_Data::get_asset_availability( $asset_id, $curr_date, false, $product_id, $settings );
					} else {
						$asset_availability = $this->get_asset_availability( $asset_id, $curr_date, false, $product_id );
					}
					$availability = $asset_availability;
					$availability = $this->is_asset_available( $curr_date );
				} else {
					$availability = false;
				}
				if ( ! $availability ) {
					$curr_date = strtotime( date( 'Y-m-d', strtotime( '+1 day', $curr_date ) ) );
					continue;
				}
			}

			if ( $calendar_for == 'date-picker' ) {

				$available_slot = $this->get_number_of_available_slot( $curr_date, $product_id, $asset_id );

				$interval_type = get_post_meta( $product_id, '_phive_book_interval_type', 1 );

				if ( ( ( $interval_type == 'fixed' && $this->interval > 1 ) || $interval_type == 'customer_choosen' ) && $available_slot > 0 ) {

					$start_date = date( 'Y-m-d', $curr_date );

					$interval_day = ( $interval_type == 'fixed' ) ? $this->interval : get_post_meta( $product_id, '_phive_book_min_allowed_booking', 1 );

					while ( $interval_day-- > 0 ) {

						$available_slot_curr_date = $this->get_number_of_available_slot( strtotime( $start_date ), $product_id, $asset_id );

						$is_available = $this->is_available( strtotime( $start_date ), '', $product_id, $calendar_for );

						if ( $available_slot_curr_date < 1 || ! $is_available ) {

							$date_booking_status = false;
							break;
						}

						$start_date = date( 'Y-m-d', strtotime( '+1 day ' . $start_date ) );
					}

					if ( ! $date_booking_status ) {

						$curr_date = strtotime( '+1 day ' . $start_date );
						continue;
					}
				}

				// if Past date.
				if ( $curr_date < strtotime( date( 'Y-m-d' ) ) ) {
					$date_booking_status = false;
				}

				// if set rule not available in availability table
				if ( ! $this->is_available( $curr_date, '', $product_id, $calendar_for ) ) {
					$date_booking_status = false;
				}
				// if booking slot is full.
				// if( $this->is_booked_date( $curr_date, $product_id ) || ($available_slot=='0') ){
				// $date_booking_status=false;
				// }

				// is_booked_date and $available_slot working the same way, it will take extra loading time if both are applied.

				if ( ( $available_slot == '0' ) ) {
					$date_booking_status = false;
				}

				// if there is no slot for book whole period (Case of Min booking set).
				// if( !$this->is_bookable( $curr_date, "1 day", $product_id ) ){
				// $date_booking_status=false;
				// }

				// if non-startable days
				if ( ! in_array( date( 'N', $curr_date ), $this->get_booking_start_days( $product_id ) ) ) {
					$date_booking_status = false;
				}
				if ( $return_matched_date && $date_booking_status ) {

					return date( 'Y-m-d', $curr_date );
				} elseif ( $date_booking_status ) {

					return date( 'Y-m-1', $curr_date );
				}
			} elseif ( $calendar_for == 'time-picker' ) {

				$date       = date( 'Y-m-d', $curr_date );
				$start_time = $date . ' ' . $this->get_shop_opening_time( $product_id, date( 'Y-m-d', $curr_date ) );

				$end_time     = strtotime( '+1 day', strtotime( date( 'Y-m-d 00:00', ( strtotime( $start_time ) ) ) ) );
				$interval     = 0;
				$loop_breaker = 200;
				while ( strtotime( $start_time ) < $end_time && $loop_breaker > 0 ) {
					$flag       = false;
					$start_time = strtotime( $start_time );
					if ( ( $phive_enable_buffer == 'yes' ) && ( $this->interval_period == $buffer_period ) ) {
						$interval = $this->get_buffer_added_interval();
					} else {
						$interval = $this->interval;
					}
					$date_now = $this->get_current_time_in_wp_tz();
					// $date_now = strtotime(date("Y-m-d H:i") );
					if ( $this->zone == -3 ) {
						$date_now = strtotime( gmdate( 'Y-m-d H:i', time() + 3600 * ( $this->zone ) ) );
					}
					if ( $start_time < $date_now ) { // if Past date.
						$date_booking_status = false;
						$start_time          = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );
						--$loop_breaker;
						continue;
					}
					if ( $this->is_working_time( $start_time, $product_id ) ) {
						// Dynamic slot creation
						$next_available_time = $start_time;
						$prev_available_time = '';

						$start_time_day = date( 'D', $start_time );
						do {

							// Check if matching asset is there for each slot and create the asset object for that asset id
							if ( $this->assets_enabled == 'yes' && $this->assets_auto_assign == 'yes' ) {
								$asset_id                     = $this->get_most_matching_asset_for_slots( $next_available_time, '', $product_id );
								$this->asset_obj              = $this->get_asset_obj( $asset_id, $next_available_time );
								$asset_matched_available_time = $next_available_time;
							}

							// This loop finds the next available time slot starting time
							// ONLY check the interval time slot, as buffer time is not needed to be available.
							$next_available_time_for_product = $this->get_next_available_time( $next_available_time, $this->interval, $product_id );
							$asset_next_available_time       = $this->get_asset_next_available_time( $next_available_time, $this->interval );
							$prev_available_time             = $next_available_time;
							$next_available_time             = $this->get_farthest_time( $next_available_time_for_product, $asset_next_available_time );

							// 158203
							$conflict = 0;
							if ( $this->assets_enabled == 'yes' && $this->assets_auto_assign == 'yes' && $next_available_time != '' ) {
								if ( ( $next_available_time_for_product == $next_available_time && $asset_next_available_time < $next_available_time ) || ( $asset_matched_available_time != $next_available_time ) ) {
									$conflict = 1;
								}
							}

							// 158203
							if ( $next_available_time == '' ) {
								break;
							}

							// Break the loop if next available time exceeds shop closing time and take prev_available_time as next_available_time
							if ( isset( $this->shop_closing_time ) && ! empty( $this->shop_closing_time ) && strtotime( date( 'H:i', $next_available_time ) ) >= strtotime( "+$interval $this->interval_period", strtotime( $this->shop_closing_time ) ) ) {
								$next_available_time = $prev_available_time;
								break;
							}

							// In case of default - non available, $next_available_time jumps to next day indicating no available time remaining for current day
							if ( $next_available_time != '' && $start_time_day != date( 'D', $next_available_time ) ) {
								$next_available_time = '';
								break;
							}
							// In case of default - available, !$next_available_time indicates that no available time for current day
							if ( ! $next_available_time || ( $this->is_available( $next_available_time, $this->interval, $product_id, 'time' ) && $this->is_asset_available( $next_available_time ) ) ) {
								break;
							} elseif ( $prev_available_time == $next_available_time ) {
								// If no rule was matched, get the next time based on booking duration.
								// else, check availability for the new time slot which was calculated based on rules.
								$next_available_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $next_available_time ) );
								$next_available_time = strtotime( $next_available_time );
							}
						} while ( $next_available_time );

						if ( ! $next_available_time ) {
							// no time slot available for today
							$date_booking_status = false;
							break;
						}

						$start_time = $next_available_time;
						// Dynamic slot creation END

						if ( $start_time < $date_now ) { // if Past date.
							$flag = false;

							// Ticket 132863 -Allow Past booking
							$flag = apply_filters( 'ph_bookings_book_for_past_days_of_time_flag', $flag, $product_id, $start_time );
							if ( $flag == false ) {
								$start_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );
								--$loop_breaker;
								continue;
							} else {
								break;
							}
						}

						$available_slot = $this->get_number_of_available_slot( $start_time, $product_id, $asset_id, false, 1 );

						// 158203
						if ( $conflict == 1 && $available_slot == 0 ) {
							$old_asset_id       = $asset_id;
							$old_asset_obj      = $this->asset_obj;
							$old_available_slot = $available_slot;
							$asset_id           = $this->get_most_matching_asset_for_slots( $start_time, '', $product_id );
							$this->asset_obj    = $this->get_asset_obj( $asset_id, $start_time );
							$available_slot     = $this->get_number_of_available_slot( $start_time, $product_id, $asset_id, false, 1 );
							if ( $available_slot < 1 ) {
								$asset_id        = $old_asset_id;
								$this->asset_obj = $old_asset_obj;
								$available_slot  = $old_available_slot;
							}
						}

						$date_now = $this->get_current_time_in_wp_tz();
						// $date_now = strtotime(date("Y-m-d H:i") );
						if ( $this->zone == -3 ) {
							$date_now = strtotime( gmdate( 'Y-m-d H:i', time() + 3600 * ( $this->zone ) ) );
						}
						// if( $this->is_booked_date( $start_time, $product_id ) || ($available_slot==0) ){
						// $flag = '1';
						// $date_booking_status=false;
						// }

						// is_booked_date and $available_slot working the same way, it will take extra loading time if both are applied.
						if ( ( $available_slot == 0 ) ) {
							$flag                = '1';
							$date_booking_status = false;
						} elseif ( $start_time < $date_now ) { // if Past date.
							$flag                = '2';
							$date_booking_status = false;
						} elseif ( $this->is_available( $start_time, '', $product_id, 'time' ) && strtotime( date( 'Y-m-d H:i' ) ) < $start_time ) { // if set rule not available in availability table
							$date_booking_status = true;
							break;
						}
						if ( ! $this->is_available( $start_time, '', $product_id ) ) {
							$flag                = '5';
							$date_booking_status = false;
						}
					} else {
						$flag                = '4';
						$date_booking_status = false;
					}
					$start_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );
					--$loop_breaker;
				}
				if ( $return_matched_date && $date_booking_status ) {

					return date( 'Y-m-d H:i', $start_time );
				} elseif ( $date_booking_status === true ) {

					return date( 'Y-m-1', $curr_date );
				}
			} else {
				return $start_date;
			}
			$curr_date = strtotime( date( 'Y-m-d', strtotime( '+1 day', $curr_date ) ) );
		}
		return $start_date;
	}

	public function get_available_date_for_time( $start_date = '', $end_date = '', $product_id = '', $asset_id = '' ) {
		$available_array   = array();
		$unavailable_array = array();
		$end_date          = ( empty( $end_date ) ) ? strtotime( '+1 month', strtotime( $start_date ) ) : strtotime( $end_date );
		if ( $this->assets_enabled == 'yes' ) {
			$this->asset_obj = $this->get_asset_obj( $asset_id, $start_date );
		}
		if ( empty( $product_id ) ) {
			global $product;
			$product_id = $product->get_id();
			$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product->get_id() );
		}

		$min_allowed_booking = get_post_meta( $product_id, '_phive_book_min_allowed_booking', 1 );
		$min_allowed_booking = empty( $min_allowed_booking ) ? 1 : $min_allowed_booking;
		if ( empty( $this->interval ) ) {
			$this->interval = 1; // Defaulting interval to 1
		}

		global $wp_version;

		if ( version_compare( $wp_version, '5.3', '>=' ) ) {
			$timezone = wp_timezone();
		} else {
			$timezone = get_option( 'timezone_string' );
			if ( empty( $timezone ) ) {
				$time_offset = get_option( 'gmt_offset' );
				$timezone    = timezone_name_from_abbr( '', $time_offset * 60 * 60, 0 );
			}
			$timezone = new DateTimeZone( $timezone );
		}
		$today = new DateTime( 'now', $timezone );
		$today = $today->format( 'Y-m-d' );

		if ( strtotime( $start_date ) < strtotime( $today ) ) {
			$start_date = $today;
		}

		$fixed_availability_from = get_post_meta( $product_id, '_phive_fixed_availability_from', 1 );
		$fixed_availability_to   = get_post_meta( $product_id, '_phive_fixed_availability_to', 1 );

		if ( ! empty( $fixed_availability_from ) && strtotime( $start_date ) < strtotime( $fixed_availability_from ) ) {
			$start_date = $fixed_availability_from;
		}

		if ( ! empty( $fixed_availability_to ) && strtotime( $end_date ) > strtotime( $fixed_availability_to ) ) {
			$end_date = $fixed_availability_to;
			$end_date = strtotime( $end_date );
		}

		$availability_rules = get_post_meta( $product_id, '_phive_booking_availability_rules', 1 );

		$curr_date = strtotime( $start_date );

		$calendar_for = 'time-picker';

		$buffer_period       = get_post_meta( $product_id, '_phive_buffer_period', 1 );
		$phive_enable_buffer = get_post_meta( $product_id, '_phive_enable_buffer', 1 );

		$count = 0;
		while ( $curr_date < $end_date && $count < 50 ) {
			++$count;
			$date       = date( 'Y-m-d', $curr_date );
			$start_time = $date . ' ' . $this->get_shop_opening_time( $product_id, date( 'Y-m-d', $curr_date ) );

			// If shop closing time is given consider it as end_time
			if ( isset( $this->shop_closing_time ) && ! empty( $this->shop_closing_time ) ) {
				$shopClosingTime = date( 'H:i', strtotime( $this->shop_closing_time ) );
				$end_time        = $date . ' ' . $shopClosingTime;
				$end_time        = strtotime( $end_time );
				$end_time        = strtotime( "+$this->interval $this->interval_period", $end_time );
			} else {
				$end_time = strtotime( '+1 day', strtotime( date( 'Y-m-d 00:00', ( strtotime( $start_time ) ) ) ) );
			}

			$interval     = 0;
			$loop_breaker = 200;

			while ( strtotime( $start_time ) < $end_time && $loop_breaker > 0 ) {
				$flag       = false;
				$start_time = strtotime( $start_time );
				$date_now   = $this->get_current_time_in_wp_tz();
				// $date_now = strtotime(date("Y-m-d H:i") );
				if ( $this->zone == -3 ) {
					$date_now = strtotime( gmdate( 'Y-m-d H:i', time() + 3600 * ( $this->zone ) ) );
				}
				if ( $this->assets_enabled == 'yes' ) {
					$this->asset_obj = $this->get_asset_obj( $asset_id, $start_time );
				}
				if ( ( $phive_enable_buffer == 'yes' ) && ( $this->interval_period == $buffer_period ) ) {
					$interval = $this->get_buffer_added_interval();
				} else {
					$interval = $this->interval;
				}
				// if( $start_time <  $date_now)
				// { // if Past date.
				// $flag = false;
				// $start_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );
				// $loop_breaker--;
				// continue;
				// }
				if ( $this->is_working_time( $start_time, $product_id ) ) {
					// Dynamic slot creation
					$next_available_time = $start_time;
					$prev_available_time = '';

					$start_time_day = date( 'D', $start_time );
					do {

						// Check if matching asset is there for each slot and create the asset object for that asset id
						if ( $this->assets_enabled == 'yes' && $this->assets_auto_assign == 'yes' ) {
							$asset_id                     = $this->get_most_matching_asset_for_slots( $next_available_time, '', $product_id );
							$this->asset_obj              = $this->get_asset_obj( $asset_id, $next_available_time );
							$asset_matched_available_time = $next_available_time;
						}

						// This loop finds the next available time slot starting time
						// ONLY check the interval time slot, as buffer time is not needed to be available.
						$next_available_time_for_product = $this->get_next_available_time( $next_available_time, $this->interval, $product_id );
						$asset_next_available_time       = $this->get_asset_next_available_time( $next_available_time, $this->interval );
						$prev_available_time             = $next_available_time;
						$next_available_time             = $this->get_farthest_time( $next_available_time_for_product, $asset_next_available_time );

						// 158203
						$conflict = 0;
						if ( $this->assets_enabled == 'yes' && $this->assets_auto_assign == 'yes' && $next_available_time != '' ) {
							if ( ( $next_available_time_for_product == $next_available_time && $asset_next_available_time < $next_available_time ) || ( $asset_matched_available_time != $next_available_time ) ) {
								$conflict = 1;
							}
						}

						// 158203
						if ( $next_available_time == '' ) {
							break;
						}

						// Break the loop if next available time exceeds shop closing time and take prev_available_time as next_available_time
						if ( isset( $this->shop_closing_time ) && ! empty( $this->shop_closing_time ) && strtotime( date( 'H:i', $next_available_time ) ) >= strtotime( "+$interval $this->interval_period", strtotime( $this->shop_closing_time ) ) ) {
							$next_available_time = $prev_available_time;
							break;
						}

						// In case of default - non available, $next_available_time jumps to next day indicating no available time remaining for current day
						if ( $next_available_time != '' && $start_time_day != date( 'D', $next_available_time ) ) {
							$next_available_time = '';
							break 2;
						}
						// In case of default - available, !$next_available_time indicates that no available time for current day
						if ( ! $next_available_time || ( $this->is_available( $next_available_time, $this->interval, $product_id, 'time' ) && $this->is_asset_available( $next_available_time ) ) ) {
							break;
						} elseif ( $prev_available_time == $next_available_time ) {
							// If no rule was matched, get the next time based on booking duration.
							// else, check availability for the new time slot which was calculated based on rules.
							$next_available_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $next_available_time ) );
							$next_available_time = strtotime( $next_available_time );
						}
					} while ( $next_available_time );

					if ( ! $next_available_time ) {
						// no time slot available for today
						break;
					}

					$start_time = $next_available_time;
					// Dynamic slot creation END

					if ( $start_time < $date_now ) { // if Past date.
						$flag = false;

						// Ticket 132863 -Allow Past booking
						$flag = apply_filters( 'ph_bookings_book_for_past_days_of_time_flag', $flag, $product_id, $start_time );
						if ( $flag == false ) {
							$start_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );
							--$loop_breaker;
							continue;
						} else {
							break;
						}
					}

					$available_slot = $this->get_number_of_available_slot( $start_time, $product_id, $asset_id, false, 1 );

					// 158203
					if ( $conflict == 1 && $available_slot == 0 ) {
						$old_asset_id       = $asset_id;
						$old_asset_obj      = $this->asset_obj;
						$old_available_slot = $available_slot;
						$asset_id           = $this->get_most_matching_asset_for_slots( $start_time, '', $product_id );
						$this->asset_obj    = $this->get_asset_obj( $asset_id, $start_time );
						$available_slot     = $this->get_number_of_available_slot( $start_time, $product_id, $asset_id, false, 1 );
						if ( $available_slot < 1 ) {
							$asset_id        = $old_asset_id;
							$this->asset_obj = $old_asset_obj;
							$available_slot  = $old_available_slot;
						}
					}

					$date_now = $this->get_current_time_in_wp_tz();
					// $date_now = strtotime(date("Y-m-d H:i") );
					if ( $this->zone == -3 ) {
						$date_now = strtotime( gmdate( 'Y-m-d H:i', time() + 3600 * ( $this->zone ) ) );
					}
					// if( $this->is_booked_date( $start_time, $product_id ) || ($available_slot==0) ){
					if ( $available_slot == 0 ) {
						$flag = false;
					} elseif ( $start_time < $date_now ) { // if Past date.
						$flag = false;
					} elseif ( $this->assets_enabled == 'yes' && ! $this->is_asset_available( $start_time ) ) {
						$flag = false;
					} elseif ( $this->is_available( $start_time, '', $product_id, 'time' ) && strtotime( current_time( 'Y-m-d H:i' ) ) < $start_time ) { // if set rule not available in availability table
						$flag = true;
						break;
					}
				} else {
					// $flag = false;
				}
				$start_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );
				--$loop_breaker;
			}
			if ( $flag === true ) {
				$available_array[] = date( 'Y-m-d', $curr_date );
			} else {
				$unavailable_array[] = date( 'Y-m-d', $curr_date );
			}
			$curr_date = strtotime( date( 'Y-m-d', strtotime( '+1 day', $curr_date ) ) );
		}
		$this->unavailable_array = array_unique( $unavailable_array );
		return $available_array;
	}

	public function get_current_time_in_wp_tz() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.3', '>=' ) ) {
			$timezone = wp_timezone();
		} else {
			$timezone = get_option( 'timezone_string' );
			if ( empty( $timezone ) ) {
				$time_offset = get_option( 'gmt_offset' );
				$timezone    = timezone_name_from_abbr( '', $time_offset * 60 * 60, 0 );
			}
			$timezone = new DateTimeZone( $timezone );
		}
		$current_time = new DateTime( 'now', $timezone );
		$current_time = $current_time->format( 'Y-m-d H:i' );
		return strtotime( $current_time );
	}

	public function is_all_slots_available( $start_time = '', $end_time = '', $product_id = '', $asset_id = '' ) {

		$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product_id );

		if ( $this->assets_enabled == 'yes' ) {
			$this->asset_obj = $this->get_asset_obj( $asset_id, $start_time );
		}
		$buffer_period            = get_post_meta( $product_id, '_phive_buffer_period', 1 );
		$across_the_day           = get_post_meta( $product_id, '_phive_enable_across_the_day', 1 );
		$phive_enable_buffer      = get_post_meta( $product_id, '_phive_enable_buffer', 1 );
		$display_booking_capacity = get_post_meta( $product_id, '_phive_display_bookings_capacity', true );
		$end_time_display         = get_post_meta( $product_id, '_phive_enable_end_time_display', true );
		$remainng_bokkings_text   = get_post_meta( $product_id, '_phive_remainng_bokkings_text', true );
		$remainng_bokkings_text   = ph_wpml_translate_single_string( 'Remaining_Bookings_Text', $remainng_bokkings_text );
		$remainng_bokkings_text   = ! empty( $remainng_bokkings_text ) ? '%s ' . $remainng_bokkings_text : '(%s left)';
		$loop_breaker             = 200;
		$unavailable_slot         = '';
		$shop_opening_time 		  = get_post_meta( $product_id, '_phive_book_working_hour_start', 1 );

		$end_time         = ( empty( $end_time ) ) ? strtotime( '+1 day', strtotime( date( 'Y-m-d', ( strtotime( $start_time ) ) ) ) ) : strtotime( $end_time );
		$booking_capacity = null;
		$interval         = 0;
		$ph_pre_slot_date = '';

		while ( strtotime( $start_time ) < $end_time && $loop_breaker > 0 ) {

			// Create DateTime object 
			$date_time = new DateTime($start_time);
			$ph_day = $date_time->format('d');
			
			// Check if the current slot's day is different from the previous slot's day
			// If yes, it indicates a new day has started (across-day booking scenario)
			if (!empty($ph_pre_slot_date) && $ph_pre_slot_date != $ph_day) {

				// Update the start time to the new day's start time
				// Use first booking start time if defined, otherwise default to '00:00'
				$start_time = date( 'Y-m-d', strtotime( $start_time )) . ' ' . (!empty($shop_opening_time) ? $shop_opening_time : '00:00');
			}

			// Save the current slot's day for comparison in the next iteration
			$ph_pre_slot_date = $ph_day;

			$flag           = true;
			$start_time     = strtotime( $start_time );
			$available_slot = $this->get_number_of_available_slot( $start_time, $product_id, $asset_id );

			if ( $this->is_working_time( $start_time, $product_id ) || $across_the_day == 'yes' ) {

				$css_classes = array( 'ph-calendar-date' );
				// if( $this->is_booked_date( $start_time, $product_id ) || ($available_slot==0) ){
				// $css_classes[] = 'booking-full';
				// $css_classes[] = 'de-active';
				// }

				// is_booked_date and $available_slot working the same way, it will take extra loading time if both are applied.
				if ( ( $available_slot == '0' ) ) {
					$css_classes[] = 'booking-full';
					$css_classes[] = 'de-active';
					$flag          = false;
				}

				$date_now = $this->get_current_time_in_wp_tz();
				// $date_now = strtotime(date("Y-m-d H:i") );
				if ( $this->zone == -3 ) {
					$date_now = strtotime( gmdate( 'Y-m-d H:i', time() + 3600 * ( $this->zone ) ) );
				}

				if ( $start_time < $date_now ) { // if Past date.
					$css_classes[] = 'booking-disabled';
					$flag          = false;
					// 129889-allow past booking
					$flag = apply_filters( 'ph_bookings_book_for_past_days_of_time_flag', $flag, $product_id, $start_time, $css_classes );
				}
				$this->called_when_clicked_to_date = 1;
				if ( ! $this->is_available( $start_time, $this->interval, $product_id, 'time-picker' ) || ! $this->is_asset_available( $start_time ) ) { // if set rule not available in availability table
					$css_classes[] = 'de-active';
					$css_classes[] = 'not-available';
					$flag          = false;
				}
				$this->called_when_clicked_to_date = 0;
				if ( ! $this->is_working_time( $start_time, $product_id ) && $across_the_day == 'yes' ) {
					$css_classes[] = 'non-working-time';
					$flag          = true;
				}
				$css_classes_in_text = implode( ' ', array_unique( $css_classes ) );
				$non_title_classes   = array( 'booking-disabled', 'booking-full', 'not-available' );
				if ( ( $phive_enable_buffer == 'yes' ) && ( $this->interval_period == $buffer_period ) ) {
					$interval = $this->get_buffer_added_interval();
				} else {
					$interval = $this->interval;
				}

				if ( $flag == false ) {
					$unavailable_slot = date( 'Y-m-d H:i', $start_time );
					return $unavailable_slot;
					// return false;
				}
			}

			$start_time = date( 'Y-m-d H:i', strtotime( "+$interval $this->interval_period", $start_time ) );

			--$loop_breaker;
		}
		// return true;
		return $unavailable_slot;
	}
}
