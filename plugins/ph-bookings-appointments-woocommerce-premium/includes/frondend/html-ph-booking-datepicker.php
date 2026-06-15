<div class="date-picker-wraper">
	<?php

	$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product->get_id() ); // wpml
	// error_log("product_id : ".$product_id);
	$default_product = wc_get_product( $product_id );

	$ph_calendar_color = get_option( 'ph_booking_settings_calendar_color' );

	$ph_calendar_month_color    = isset( $ph_calendar_color['ph_calendar_month_color'] ) && ! empty( $ph_calendar_color['ph_calendar_month_color'] ) ? $ph_calendar_color['ph_calendar_month_color'] : '#539bbe';
	$booking_full_color         = isset( $ph_calendar_color['booking_full_color'] ) && ! empty( $ph_calendar_color['booking_full_color'] ) ? $ph_calendar_color['booking_full_color'] : '#dadada';
	$selected_date_color        = isset( $ph_calendar_color['selected_date_color'] ) && ! empty( $ph_calendar_color['selected_date_color'] ) ? $ph_calendar_color['selected_date_color'] : '#6aa3f1';
	$booking_info_wraper_color  = isset( $ph_calendar_color['booking_info_wraper_color'] ) && ! empty( $ph_calendar_color['booking_info_wraper_color'] ) ? $ph_calendar_color['booking_info_wraper_color'] : '#539bbe';
	$ph_calendar_weekdays_color = isset( $ph_calendar_color['ph_calendar_weekdays_color'] ) && ! empty( $ph_calendar_color['ph_calendar_weekdays_color'] ) ? $ph_calendar_color['ph_calendar_weekdays_color'] : '#ddd';
	$ph_calendar_days_color     = isset( $ph_calendar_color['ph_calendar_days_color'] ) && ! empty( $ph_calendar_color['ph_calendar_days_color'] ) ? $ph_calendar_color['ph_calendar_days_color'] : '#eee';

	$display_booking_capacity = get_post_meta( $default_product->get_id(), '_phive_display_bookings_capacity', true );

	$book_now_bg_color               = ! empty( $ph_calendar_color['book_now_bg_color'] ) ? $ph_calendar_color['book_now_bg_color'] : '#1a1a1a';
	$book_now_text_color             = ! empty( $ph_calendar_color['book_now_text_color'] ) ? $ph_calendar_color['book_now_text_color'] : '#ffffff';
	$booking_info_wraper_text_color  = ! empty( $ph_calendar_color['booking_info_wraper_text_color'] ) ? $ph_calendar_color['booking_info_wraper_text_color'] : '#ffffff';
	$price_box_text_color            = ( isset( $ph_calendar_color['price_box_text_color'] ) && ! empty( $ph_calendar_color['price_box_text_color'] ) ) ? $ph_calendar_color['price_box_text_color'] : '#000000';
	$ph_calendar_days_text_color     = ! empty( $ph_calendar_color['ph_calendar_days_text_color'] ) ? $ph_calendar_color['ph_calendar_days_text_color'] : '#777';
	$ph_calendar_month_text_color    = ! empty( $ph_calendar_color['ph_calendar_month_text_color'] ) ? $ph_calendar_color['ph_calendar_month_text_color'] : '#ffffff';
	$ph_calendar_weekdays_text_color = ! empty( $ph_calendar_color['ph_calendar_weekdays_text_color'] ) ? $ph_calendar_color['ph_calendar_weekdays_text_color'] : '#ffffff';

	$ph_calendar_design = ( isset( $ph_calendar_color['ph_calendar_design'] ) && ! empty( $ph_calendar_color['ph_calendar_design'] ) ) ? $ph_calendar_color['ph_calendar_design'] : 1; // default legacy design will display
	// new design colours
	$primary_bg_color   = ( isset( $ph_calendar_color['primary_bg_color'] ) && ! empty( $ph_calendar_color['primary_bg_color'] ) ) ? $ph_calendar_color['primary_bg_color'] : '1791ce';
	$hover_bg_color     = ( isset( $ph_calendar_color['hover_bg_color'] ) && ! empty( $ph_calendar_color['hover_bg_color'] ) ) ? $ph_calendar_color['hover_bg_color'] : 'fff';
	$price_box_bg_color = ( isset( $ph_calendar_color['price_box_bg_color'] ) && ! empty( $ph_calendar_color['price_box_bg_color'] ) ) ? $ph_calendar_color['price_box_bg_color'] : 'ffffff';
	$text_color         = ( isset( $ph_calendar_color['text_color'] ) && ! empty( $ph_calendar_color['text_color'] ) ) ? $ph_calendar_color['text_color'] : 'fff';
	$booked_block_color = ( isset( $ph_calendar_color['booked_block_color'] ) && ! empty( $ph_calendar_color['booked_block_color'] ) ) ? $ph_calendar_color['booked_block_color'] : 'dadada';

	$book_now_bg_color_design_2   = ! empty( $ph_calendar_color['book_now_bg_color_design_2'] ) ? $ph_calendar_color['book_now_bg_color_design_2'] : '#1a1a1a';
	$book_now_text_color_design_2 = ! empty( $ph_calendar_color['book_now_text_color_design_2'] ) ? $ph_calendar_color['book_now_text_color_design_2'] : '#ffffff';

	// design 3
	$font_color_box                 = ( isset( $ph_calendar_color['font_color_box'] ) && ! empty( $ph_calendar_color['font_color_box'] ) ) ? $ph_calendar_color['font_color_box'] : '#1d3268';
	$booking_full_color_box         = ( isset( $ph_calendar_color['booking_full_color_box'] ) && ! empty( $ph_calendar_color['booking_full_color_box'] ) ) ? $ph_calendar_color['booking_full_color_box'] : '#dadada';
	$selected_date_color_box        = ( isset( $ph_calendar_color['selected_date_color_box'] ) && ! empty( $ph_calendar_color['selected_date_color_box'] ) ) ? $ph_calendar_color['selected_date_color_box'] : '#1d3268';
	$booking_wrapper_color_box      = ( isset( $ph_calendar_color['booking_wrapper_color_box'] ) && ! empty( $ph_calendar_color['booking_wrapper_color_box'] ) ) ? $ph_calendar_color['booking_wrapper_color_box'] : '#1d3268';
	$booking_wrapper_text_color_box = ( isset( $ph_calendar_color['booking_wrapper_text_color_box'] ) && ! empty( $ph_calendar_color['booking_wrapper_text_color_box'] ) ) ? $ph_calendar_color['booking_wrapper_text_color_box'] : '#ffffff';
	$book_now_bg_color_box          = ( isset( $ph_calendar_color['book_now_bg_color_box'] ) && ! empty( $ph_calendar_color['book_now_bg_color_box'] ) ) ? $ph_calendar_color['book_now_bg_color_box'] : '#1d3268';
	$book_now_text_color_box        = ( isset( $ph_calendar_color['book_now_text_color_box'] ) && ! empty( $ph_calendar_color['book_now_text_color_box'] ) ) ? $ph_calendar_color['book_now_text_color_box'] : '#ffffff';
	$start_date_box                 = ( isset( $ph_calendar_color['start_date_box'] ) && ! empty( $ph_calendar_color['start_date_box'] ) ) ? $ph_calendar_color['start_date_box'] : '#3f9dbc';
	// error_log("design".$ph_calendar_design);
	if ( $ph_calendar_design == 2 && ! ( isset( $_GET['page'] ) && $_GET['page'] == 'add-booking' ) ) {
		?>
		<style type="text/css">
			/* Compatibility with the Flatsome theme for the Quickview */
			.product-quick-view-container div.product form.cart,
			.single-product div.product form.cart {
				background-color: <?php echo $primary_bg_color; ?> !important;
			}

			.booking-info-wraper {
				background: <?php echo $price_box_bg_color; ?> !important;
			}

			.selected-date,
			.timepicker-selected-date,
			li.ph-calendar-date.mouse_hover,
			.time-picker-wraper #ph-calendar-time li.ph-calendar-date,
			li.ph-calendar-date.today:hover,
			.ph-calendar-date.today {
				border: 0px solid transparent;
			}

			.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
				border: 1px solid #ffffff;
			}

			.can-be-checkout-date {
				background: transparent !important;
			}

			li.ph-calendar-date.mouse_hover,
			li.ph-calendar-date.today:hover,
			li.ph-calendar-date:hover {
				background-color: <?php echo $hover_bg_color; ?> !important;
			}

			.timepicker-selected-date,
			.selected-date {
				background-color: <?php echo $hover_bg_color; ?> !important;
			}

			.ph-next:hover,
			.ph-prev:hover {
				color: #4d8e7a;
			}

			li.ph-calendar-date.de-active.booking-full:hover,
			.ph-calendar-date.booking-full {
				background-color: <?php echo $booked_block_color; ?> !important;
				;
				cursor: text;
			}

			.ph_bookings_book_now_button,
			.ph_bookings_book_now_button:hover {
				background-color: #1373a3 !important;
				border: 1px #1373a3 !important;
			}

			.ph_bookings_book_now_button:before {
				background: #2098D1;
			}

			li.ph-calendar-date.mouse_hover,
			li.ph-calendar-date.today:hover,
			li.ph-calendar-date:hover,
			.timepicker-selected-date,
			.selected-date,
			li.ph-calendar-date.today.timepicker-selected-date {
				background: #f4fafd;
				color: <?php echo $text_color; ?> !important;
			}

			.ph-calendar-days li,
			.ph-calendar-weekdays li,
			.ph-calendar-month ul li,
			.extra-resources,
			.callender-msg,
			.ph_bookings_book_now_button,
			.ph_bookings_book_now_button:hover,
			.ph-calendar-date.today,
			label.label-person,
			.phive_asset_section label,
			.extra-resources label {
				color: <?php echo $text_color; ?> !important;
			}

			.please_pick_a_date_text {
				background: <?php echo $primary_bg_color; ?> !important;
				margin-left: 0 !important;
				padding: 10px 0 !important;
				margin-top: 1em !important;
			}

			/* book now background and text */
			.button.alt {
				background-color: <?php echo $book_now_bg_color_design_2; ?> !important;
				color: <?php echo $book_now_text_color_design_2; ?> !important;
			}

			.booking-info-wraper,
			.booking-info-wraper p {
				color: <?php echo $price_box_text_color; ?> !important;
			}

			li.ph-calendar-date.de-active.mouse_hover:hover {
				background: <?php echo $hover_bg_color; ?> !important;
			}

			li.ph-calendar-date.booking-full.can-be-checkout-date,
			.ph-calendar-date.not-available.can-be-checkout-date {
				color: <?php echo $text_color; ?> !important;
				background: transparent !important;
				text-decoration: none;
			}

			li.ph-calendar-date.de-active.can-be-checkout-date:hover,
			.de-active.selected-date {
				background: <?php echo $hover_bg_color; ?> !important;
			}
		</style>
		<?php
	} elseif ( $ph_calendar_design == 3 && ! ( isset( $_GET['page'] ) && $_GET['page'] == 'add-booking' ) ) {
		?>
		<style type="text/css">
			/*new design*/

			.ph-calendar-box-container .inner-element input:focus {
				background: <?php echo $start_date_box; ?> !important;
			}

			.ph-calendar-weekdays {
				background: white !important;
			}

			.ph-calendar-days {
				background: white !important;
			}

			li.ph-calendar-date.mouse_hover,
			li.ph-calendar-date:hover {
				background: <?php echo $selected_date_color_box; ?> !important;
			}

			li.ph-calendar-date.de-active:hover {
				background: none !important;
			}

			.button.alt {
				background-color: <?php echo $book_now_bg_color_box; ?> !important;
				color: <?php echo $book_now_text_color_box; ?> !important;
			}

			.booking-info-wraper {
				color: white !important;
			}

			.ph-calendar-days li,
			.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
				color: <?php echo $font_color_box; ?>;
			}

			.ph-calendar-weekdays li {
				color: <?php echo $font_color_box; ?>;
			}

			.span-month,
			.span-year,
			.ph-prev,
			.ph-next {
				color: <?php echo $font_color_box; ?> !important;
			}

			.time-picker {
				margin-top: 1em !important;
			}

			.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
				background: white;
			}

			.time-picker-wraper #ph-calendar-time li.ph-calendar-date:hover,
			.time-picker-wraper #ph-calendar-time li.ph-calendar-date.mouse_hover {
				background: <?php echo $selected_date_color_box; ?>;
				;
				color: white;
			}

			.ph-calendar-container {
				border: 1px solid #cccccc;
				background: white;
			}

			.ph-calendar-month {
				border-bottom: 1px solid #cccccc;
			}

			.ph-calendar-month {
				background: #ffffff !important;
			}

			ul.ph-calendar-weekdays {
				border-bottom: 1px solid #cccccc;
				background: #fff !important;
			}

			.ph-calendar-days {
				background: #fff !important;
			}

			.time-picker {
				margin-top: 0px !important;
				/*border-top: 1px solid #cccccc;*/
			}

			.booking-info-wraper {
				background: <?php echo $booking_wrapper_color_box; ?> !important;
				/*background: #3f9dbc !important;*/
			}

			li.ph-calendar-date.mouse_hover,
			li.ph-calendar-date:hover {
				background: <?php echo $selected_date_color_box; ?> !important;
				color: white;
				/*background: #3f9dbc !important;*/
			}

			li.ph-calendar-date.booking-full.de-active:hover {
				background: <?php echo $booking_full_color_box; ?> !important;
			}

			.booking-full {
				background: <?php echo $booking_full_color_box; ?> !important;
			}

			.timepicker-selected-date,
			.selected-date {
				background: <?php echo $selected_date_color_box; ?> !important;
				border: 0px solid <?php echo $selected_date_color_box; ?> !important;
			}

			.booking-info-wraper,
			.booking-info-wraper p {
				color: <?php echo $booking_wrapper_text_color_box; ?> !important;
			}

			.single-product div.product form.cart {
				border: 1px solid #cccccc;
				padding: 10px;
				border-radius: 5px;
			}

			li.ph-calendar-date.de-active.mouse_hover:hover {
				background: <?php echo $selected_date_color_box; ?> !important;
			}

			li.ph-calendar-date.booking-full.can-be-checkout-date,
			.ph-calendar-date.not-available.can-be-checkout-date {
				color: <?php echo $font_color_box; ?> !important;
				background: transparent !important;
				/* text-decoration: none; */
			}

			li.ph-calendar-date.de-active.can-be-checkout-date:hover,
			.de-active.selected-date {
				background: <?php echo $selected_date_color_box; ?> !important;
			}
		</style>
		<?php
	} else {
		?>
		<style type="text/css">
			.ph-calendar-month {
				background: <?php echo $ph_calendar_month_color; ?> !important;
			}

			.booking-full {
				background: <?php echo $booking_full_color; ?> !important;
			}

			.can-be-checkout-date {
				background: transparent !important;
			}

			.timepicker-selected-date,
			.selected-date {
				background: <?php echo $selected_date_color; ?> !important;
				border: 0px solid <?php echo $selected_date_color; ?> !important;
			}

			.booking-info-wraper {
				background: <?php echo $booking_info_wraper_color; ?> !important;
			}

			.ph-calendar-weekdays {
				background: <?php echo $ph_calendar_weekdays_color; ?> !important;
			}

			.ph-calendar-days {
				background: <?php echo $ph_calendar_days_color; ?> !important;
			}

			li.ph-calendar-date.mouse_hover,
			li.ph-calendar-date:hover {
				background: <?php echo ! empty( $selected_date_color ) ? $selected_date_color : '#6aa3f1'; ?> !important;
			}

			li.ph-calendar-date.de-active:hover {
				background: none !important;
			}

			li.ph-calendar-date.booking-full.de-active:hover {
				background: <?php echo ! empty( $booking_full_color ) ? $booking_full_color : '#dadada'; ?> !important;
			}

			.please_pick_a_date_text {
				background: <?php echo $ph_calendar_days_color; ?> !important;
				margin-left: 0 !important;
				padding: 10px 0 !important;
				margin-top: 1em !important;
			}

			.button.alt {
				background-color: <?php echo $book_now_bg_color; ?> !important;
				color: <?php echo $book_now_text_color; ?> !important;
			}

			.booking-info-wraper,
			.booking-info-wraper p {
				color: <?php echo $booking_info_wraper_text_color; ?> !important;
			}

			.ph-calendar-days li,
			.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
				color: <?php echo $ph_calendar_days_text_color; ?>;
			}

			.ph-calendar-weekdays li {
				color: <?php echo $ph_calendar_weekdays_text_color; ?>;
			}

			.span-month,
			.span-year,
			.ph-prev,
			.ph-next {
				color: <?php echo $ph_calendar_month_text_color; ?> !important;
			}

			li.ph-calendar-date.de-active.mouse_hover:hover {
				background: <?php echo ! empty( $selected_date_color ) ? $selected_date_color : '#6aa3f1'; ?> !important;
			}

			li.ph-calendar-date.booking-full.can-be-checkout-date,
			.ph-calendar-date.not-available.can-be-checkout-date {
				color: <?php echo $ph_calendar_days_text_color; ?> !important;
			}

			li.ph-calendar-date.de-active.can-be-checkout-date:hover {
				background: <?php echo ! empty( $selected_date_color ) ? $selected_date_color : '#6aa3f1'; ?> !important;
			}
		</style>
		<?php
	}

	?>


	<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css" /> -->
	<input type="hidden" id="calender_type" value="date">
	<input type="hidden" id="ph_display_booking_capacity" value="<?php echo $default_product->get_meta( '_phive_display_bookings_capacity' ); ?>">
	<input type="hidden" id="book_interval_type" value="<?php echo $default_product->get_interval_type(); ?>">
	<input type="hidden" id="book_interval" value="<?php echo $default_product->get_interval(); ?>">
	<input type="hidden" id="book_min_allowed_slot" value="<?php echo $default_product->get_min_allowed_booking(); ?>">
	<input type="hidden" id="book_max_allowed_slot" value="<?php echo $default_product->get_max_allowed_booking(); ?>">
	<input type="hidden" id="charge_per_night" value="<?php echo $default_product->get_charge_per_night(); ?>">
	<input type="hidden" name="persons_as_booking" id="persons_as_booking" value="<?php echo $default_product->get_persons_as_booking(); ?>">

	<?php
	$this->fixed_availability_from = get_post_meta( $default_product->get_id(), '_phive_fixed_availability_from', 1 );
	$this->fixed_availability_to   = get_post_meta( $default_product->get_id(), '_phive_fixed_availability_to', 1 );

	$timezone = get_option( 'timezone_string' );
	if ( empty( $timezone ) ) {
		$time_offset = get_option( 'gmt_offset' );
		$timezone    = timezone_name_from_abbr( '', $time_offset * 60 * 60, 0 );
	}
	if ( ! empty( $timezone ) ) {
		date_default_timezone_set( $timezone );
	}
	$current_date = strtotime( date( 'Y-m-1' ) );


	// Start date
	if ( ! empty( $this->fixed_availability_from ) && strtotime( $this->fixed_availability_from ) >= $current_date ) {
		$start_date = date( 'Y', strtotime( $this->fixed_availability_from ) ) . '-' . date( 'm', strtotime( $this->fixed_availability_from ) ) . '-01';
	} else {
		$start_date = date( 'Y' ) . '-' . date( 'm' ) . '-01';
	}

	$start_date = apply_filters( 'ph_booking_calendar_start_date', $start_date, $default_product->get_id(), 'date-picker' );


	// If fixed availability, Month would not be changed
	$fixed_month = false;
	if (
		! empty( $this->fixed_availability_from ) && ! empty( $this->fixed_availability_to )
		&& date( 'm', strtotime( $this->fixed_availability_from ) ) == date( 'm', strtotime( $this->fixed_availability_to ) )
		&& date( 'Y', strtotime( $this->fixed_availability_from ) ) == date( 'Y', strtotime( $this->fixed_availability_to ) )
	) {
		$fixed_month = true;
	}

	if ( $this->assets_enabled == 'yes' ) {
		$this->phive_generate_assets_input_fields();
	}
	// $booking_text=apply_filters('ph_booking_pick_booking_period_text','Please pick a booking period',$default_product->get_id());
	$display_settings   = get_option( 'ph_bookings_display_settigns' );
	$text_customisation = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
	$ph_swap_month_year = isset( $display_settings['ph_swap_month_year_in_calendar'] ) ? $display_settings['ph_swap_month_year_in_calendar'] : 'no';
	$pick_a_date        = isset( $text_customisation['pick_a_date'] ) && ! empty( $text_customisation['pick_a_date'] ) ? $text_customisation['pick_a_date'] : 'Please Pick a Date';

	$booking_date_text = apply_filters( 'ph_booking_pick_booking_date_text', $pick_a_date, $default_product->get_id() );
	?>
	<!-- <div class="callender-msg"><php _e( $booking_text, 'bookings-and-appointments-for-woocommerce' )?></div> -->
	<?php

	if ( $ph_calendar_design == 3 && ! ( isset( $_GET['page'] ) && $_GET['page'] == 'add-booking' ) ) {
		$display_settings   = get_option( 'ph_bookings_display_settigns' );
		$text_customisation = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
		$ph_swap_month_year = isset( $display_settings['ph_swap_month_year_in_calendar'] ) ? $display_settings['ph_swap_month_year_in_calendar'] : 'no';
		$start_date_text    = isset( $text_customisation['start_date_text'] ) && ! empty( $text_customisation['start_date_text'] ) ? $text_customisation['start_date_text'] : 'Start Date';
		$end_date_text      = isset( $text_customisation['end_date_text'] ) && ! empty( $text_customisation['end_date_text'] ) ? $text_customisation['end_date_text'] : 'End Date';
		$start_time_text    = isset( $text_customisation['start_time_text'] ) && ! empty( $text_customisation['start_time_text'] ) ? $text_customisation['start_time_text'] : 'Start Time';
		$end_time_text      = isset( $text_customisation['end_time_text'] ) && ! empty( $text_customisation['end_time_text'] ) ? $text_customisation['end_time_text'] : 'End Time';

		// 144369 - allow strings to be translated
		$start_date_text = __( $start_date_text, 'bookings-and-appointments-for-woocommerce' );
		$end_date_text   = __( $end_date_text, 'bookings-and-appointments-for-woocommerce' );
		$start_time_text = __( $start_time_text, 'bookings-and-appointments-for-woocommerce' );
		$end_time_text   = __( $end_time_text, 'bookings-and-appointments-for-woocommerce' );

		if ( $default_product->get_interval_type() == 'fixed' ) {
			?>
			<div class="ph-calendar-box-container date-calendar fixed-block">
				<div class="left-element inner-element">
					<div class="element-container">
						<input class="element_from" id="element_from" type="text" name="" placeholder="<?php echo $start_date_text; ?>" value="" readonly="readonly" />
						<img for="element_from" class="date_image element_from_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/calendar.svg">

					</div>

				</div>
			</div>

			<?php
		} elseif ( $default_product->get_interval_type() == 'customer_choosen' ) {
			?>

			<div class="ph-calendar-box-container date-calendar enable-range">
				<div class="left-element inner-element">
					<div class="element-container">
						<input class="element_from" id="element_from" type="text" name="" placeholder="<?php echo $start_date_text; ?>" value="" readonly="readonly" />
						<img for="" class="date_image element_from_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/calendar.svg">

					</div>
				</div>
				<div class="center-element">
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</div>
				<div class="right-element inner-element">
					<div class="element-container">
						<input class="element_to" id="element_to" type="text" name="" placeholder="<?php echo $end_date_text; ?>" readonly="readonly" />
						<img for="" class="date_image element_to_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/calendar.svg">
					</div>
				</div>
			</div>

			<?php
		}
	}
	?>
	<div class="ph-calendar-container">
		<div class="ph-calendar-month">
			<ul>
				<?php if ( ! $fixed_month ) : ?>
					<li class="ph-prev" <?php echo ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == 'he' ) ? "style='float:right;'" : ''; ?>><?php echo is_rtl() ? '&#10095;' : '&#10094;'; ?></li>
					<li class="ph-next" <?php echo ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == 'he' ) ? "style='float:left;'" : ''; ?>><?php echo is_rtl() ? '&#10094;' : '&#10095;'; ?></li>
				<?php endif; ?>

				<?php
				$start_date_object   = new DateTime( $start_date, new DateTimeZone( 'UTC' ) );
				$start_month_display = $start_date_object->format( 'U' );
				$start_month_display = ph_wp_date( 'F', $start_month_display );
				$start_year_display  = $start_date_object->format( 'U' );
				$year_format         = apply_filters( 'ph_customize_year_format', 'Y' );
				$start_year_display  = ph_wp_date( $year_format, $start_year_display );
				?>

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

		<ul class="ph-calendar-weekdays">
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

		<ul class="ph-calendar-days ph-ul-date <?php echo ( $display_booking_capacity != 'yes' ) ? 'ph_booking_no_place_left' : ''; ?>" id="ph-calendar-days" style="position: relative; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?> !important;">
			<?php

			$asset_id = false;
			if ( $this->assets_enabled == 'yes' ) {
				if ( $this->assets_auto_assign != 'yes' && ! empty( $this->assets_pricing_rules[0] ) ) {
					$asset_id = $this->assets_pricing_rules[0]['ph_booking_asset_id'];
				} else {
					$asset_id = '';
				}
			}
			echo $this->phive_generate_days_for_period( $start_date, '', '', $asset_id, 'date-picker' );
			?>
		</ul>
	</div>
	<!-- <br> -->
	<!-- <ul class="please_pick_a_date_text" id="please_pick_a_date_text" style="position: relative;">
		<center>
			<?php _e( $booking_date_text, 'bookings-and-appointments-for-woocommerce' ); ?>
		</center>
	</ul> -->
</div>

<?php do_action( 'ph_bookings_additional_form_fields', $default_product->get_id() ); ?>
<?php

if ( $this->person_enabled == 'yes' ) {
	$this->phive_generate_persons_input_fields();
}

if ( $this->reources_enabled == 'yes' ) {
	$this->phive_generate_resources_input_fields();
}

?>

<div class="booking-info-wraper">
	<p id="booking_info_text" style="text-align:center;">
		<?php _e( $booking_date_text, 'bookings-and-appointments-for-woocommerce' ); ?>
	</p>
	<p id="booking_price_text"> </p>
</div>
<?php
if ( $this->additional_notes_enabled == 'yes' ) {
	$this->phive_generate_additional_notes_field();
}
