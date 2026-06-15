<div class="time-picker-wraper">
	<?php

	$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $default_product->get_id() ); // wpml

	$default_product  = wc_get_product( $product_id );
	$end_time_display = get_post_meta( $default_product->get_id(), '_phive_enable_end_time_display', true );

		$ph_calendar_color          = get_option( 'ph_booking_settings_calendar_color' );
		$ph_calendar_month_color    = ( ( ! empty( $ph_calendar_color ) ) && isset( $ph_calendar_color['ph_calendar_month_color'] ) ) ? $ph_calendar_color['ph_calendar_month_color'] : '#539bbe';
		$booking_full_color         = ( ( ! empty( $ph_calendar_color ) ) && isset( $ph_calendar_color['booking_full_color'] ) ) ? $ph_calendar_color['booking_full_color'] : '#dadada';
		$selected_date_color        = ( ( ! empty( $ph_calendar_color ) ) && isset( $ph_calendar_color['selected_date_color'] ) ) ? $ph_calendar_color['selected_date_color'] : '#6aa3f1';
		$booking_info_wraper_color  = ( ( ! empty( $ph_calendar_color ) ) && isset( $ph_calendar_color['booking_info_wraper_color'] ) ) ? $ph_calendar_color['booking_info_wraper_color'] : '#539bbe';
		$ph_calendar_weekdays_color = ( ( ! empty( $ph_calendar_color ) ) && isset( $ph_calendar_color['ph_calendar_weekdays_color'] ) ) ? $ph_calendar_color['ph_calendar_weekdays_color'] : '#ddd';
		$ph_calendar_days_color     = ( ( ! empty( $ph_calendar_color ) ) && isset( $ph_calendar_color['ph_calendar_days_color'] ) ) ? $ph_calendar_color['ph_calendar_days_color'] : '#eee';
		$display_booking_capacity   = get_post_meta( $default_product->get_id(), '_phive_display_bookings_capacity', true );

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
						.single-product div.product form.cart
						{
							background-color: <?php echo $primary_bg_color; ?> !important;
						}
						.booking-info-wraper{
							background: <?php echo $price_box_bg_color; ?> !important;  
						}
						.selected-date, .timepicker-selected-date, li.ph-calendar-date.mouse_hover, .time-picker-wraper #ph-calendar-time li.ph-calendar-date , li.ph-calendar-date.today:hover, .ph-calendar-date.today{
							border: 0px solid transparent;
						}

						.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
							border: 1px solid #ffffff;
						}
						li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover{
							background-color: <?php echo $hover_bg_color; ?> !important;
						}
						.timepicker-selected-date,.selected-date {
							background-color: <?php echo $hover_bg_color; ?> !important;
						}
						.ph-next:hover, .ph-prev:hover{
							color: #4d8e7a ;
						}
						li.ph-calendar-date.de-active.booking-full:hover, .ph-calendar-date.booking-full {
							background-color: <?php echo $booked_block_color; ?> !important;;
							cursor: text;
						}
						.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover{
							background-color: #1373a3 !important;
							border: 1px #1373a3 !important;
						}
						.ph_bookings_book_now_button:before {
							background: #2098D1;
						}
						li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover, .timepicker-selected-date, .selected-date, li.ph-calendar-date.today.timepicker-selected-date{
							background: #f4fafd;
							color: <?php echo $text_color; ?> !important;
						}
						.ph-calendar-days li, .ph-calendar-weekdays li , .ph-calendar-month ul li, .extra-resources,.callender-msg,.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover,.ph-calendar-date.today,label.label-person,.phive_asset_section label, .extra-resources label{
							color: <?php echo $text_color; ?> !important;	
						}

						.participant_section
						{
							margin-top: 2em !important;
						}
						/* book now background and text */
						.button.alt
						{
							background-color: <?php echo $book_now_bg_color_design_2; ?> !important;
							color: <?php echo $book_now_text_color_design_2; ?> !important;
						}
						.booking-info-wraper, .booking-info-wraper p 
						{
							color: <?php echo $price_box_text_color; ?> !important;
						}
						.time-picker{
							margin-top:1em !important;
						}

					</style>
				<?php
	} elseif ( $ph_calendar_design == 3 && ! ( isset( $_GET['page'] ) && $_GET['page'] == 'add-booking' ) ) {
		?>
				<style type="text/css">
					/*new design*/

						/*.ph-calendar-month{
							background: black !important;
						}*/
						
						.ph-calendar-box-container .inner-element input:focus{
							background: <?php echo $start_date_box; ?> !important;
						}
						.ph-calendar-weekdays{
							background: white !important;
						}
						.ph-calendar-days{
							background: white !important;
						}
						li.ph-calendar-date.mouse_hover,li.ph-calendar-date:hover {
							background: <?php echo $selected_date_color_box; ?> !important;
						}
						li.ph-calendar-date.de-active:hover{
							background: none !important;
						}
				<?php if ( $end_time_display == 'yes' ) { ?>
							.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
								border: 1px solid #777;
								color: #777;
								background-color: black;
								border-radius: 5px;
								margin-left: 6px !important;
								margin-right: 0px !important;
							}
							.time-picker li.ph-calendar-date {
								width: 42% !important;
							}
							.time-picker .ph-calendar-days li {
								font-size: 13px !important;
							}
							.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
								margin-left: 5% !important;
							}
						<?php } ?>
						.button.alt
						{
							background-color: <?php echo $book_now_bg_color_box; ?> !important;
							color: <?php echo $book_now_text_color_box; ?> !important;
						}
						.booking-info-wraper, .booking-info-wraper p 
						{
							color: <?php echo $booking_wrapper_text_color_box; ?> !important;
						}
						.ph-calendar-days li,.time-picker-wraper #ph-calendar-time li.ph-calendar-date
						{
							color: <?php echo $font_color_box; ?>;
						}
						.ph-calendar-weekdays li
						{
							color: <?php echo $font_color_box; ?>;
						}
						.span-month, .span-year,.ph-prev,.ph-next
						{
							color: <?php echo $font_color_box; ?> !important;
						}
						.time-picker{
							margin-top:1em !important;
						}
						.time-picker-wraper #ph-calendar-time li.ph-calendar-date
						{
							background: white;
						}
						.time-picker-wraper #ph-calendar-time li.ph-calendar-date:hover,.time-picker-wraper #ph-calendar-time li.ph-calendar-date.mouse_hover {
							background: <?php echo $selected_date_color_box; ?>;
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
						li.ph-calendar-date.mouse_hover, li.ph-calendar-date:hover {
							background: <?php echo $selected_date_color_box; ?> !important;
							color: white;
							/*background: #3f9dbc !important;*/
						}
						li.ph-calendar-date.booking-full.de-active:hover{
							background: <?php echo $booking_full_color_box; ?> !important;
						}
						.booking-full{
							background: <?php echo $booking_full_color_box; ?> !important;
						}
						.timepicker-selected-date, .selected-date{
							background: <?php echo $selected_date_color_box; ?> !important;
							border:  0px solid <?php echo $selected_date_color_box; ?> !important;
						}

						.single-product div.product form.cart
						{
							border: 1px solid #cccccc;
							padding: 10px;
							border-radius: 5px;
						}
				</style>
			<?php
	} else {
		?>
					<style type="text/css">
						.ph-calendar-month{
							background: <?php echo $ph_calendar_month_color; ?> !important;
						}
						.booking-full{
							background: <?php echo $booking_full_color; ?> !important;
						}
						.timepicker-selected-date, .selected-date{
							background: <?php echo $selected_date_color; ?> !important;
							border:  0px solid <?php echo $selected_date_color; ?> !important;
						}
						.booking-info-wraper{
							background: <?php echo $booking_info_wraper_color; ?> !important;
						}
						.ph-calendar-weekdays{
							background: <?php echo $ph_calendar_weekdays_color; ?> !important;
						}
						.ph-calendar-days{
							background: <?php echo $ph_calendar_days_color; ?> !important;
						}
						li.ph-calendar-date.mouse_hover,li.ph-calendar-date:hover {
							background: <?php echo ! empty( $selected_date_color ) ? $selected_date_color : '#6aa3f1'; ?> !important;
						}
						li.ph-calendar-date.de-active:hover{
							background: none !important;
						}
						li.ph-calendar-date.booking-full.de-active:hover{
							background: <?php echo ! empty( $booking_full_color ) ? $booking_full_color : '#dadada'; ?> !important;
						}
		<?php if ( $end_time_display == 'yes' ) { ?>
							.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
								border: 1px solid #777;
								color: #777;
								background-color: <?php echo $ph_calendar_days_color; ?>;
								border-radius: 5px;
								margin-left: 6px !important;
								margin-right: 0px !important;
							}
							.time-picker li.ph-calendar-date {
								width: 42% !important;
							}
							.time-picker .ph-calendar-days li {
								font-size: 13px !important;
							}
							.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
								margin-left: 5% !important;
							}
						<?php } ?>
						.button.alt
						{
							background-color: <?php echo $book_now_bg_color; ?> !important;
							color: <?php echo $book_now_text_color; ?> !important;
						}
						.booking-info-wraper, .booking-info-wraper p 
						{
							color: <?php echo $booking_info_wraper_text_color; ?> !important;
						}
						.ph-calendar-days li,.time-picker-wraper #ph-calendar-time li.ph-calendar-date
						{
							color: <?php echo $ph_calendar_days_text_color; ?>;
						}
						.ph-calendar-weekdays li
						{
							color: <?php echo $ph_calendar_weekdays_text_color; ?>;
						}
						.span-month, .span-year,.ph-prev,.ph-next
						{
							color: <?php echo $ph_calendar_month_text_color; ?> !important;
						}
						.time-picker{
							margin-top:1em !important;
						}
					</style>
		<?php
	}
	?>
	<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css" /> -->
	<input type="hidden" id="calender_type" value="time">
	<input type="hidden" id="ph_display_booking_capacity" value="<?php echo $default_product->get_meta( '_phive_display_bookings_capacity' ); ?>">
	<input type="hidden" id="book_interval_type" value="<?php echo $default_product->get_interval_type(); ?>">
	<input type="hidden" id="book_interval" value="1">
	<input type="hidden" id="book_min_allowed_slot" value="<?php echo $default_product->get_min_allowed_booking(); ?>">
	<input type="hidden" id="book_max_allowed_slot" value="<?php echo $default_product->get_max_allowed_booking(); ?>">
	<input type="hidden" name="persons_as_booking" id="persons_as_booking" value="<?php echo $default_product->get_persons_as_booking(); ?>">
	<?php
	$this->fixed_availability_from = get_post_meta( $default_product->get_id(), '_phive_fixed_availability_from', 1 );
	$this->fixed_availability_to   = get_post_meta( $default_product->get_id(), '_phive_fixed_availability_to', 1 );
	$this->first_availability      = get_post_meta( $default_product->get_id(), '_phive_first_availability', 1 );

	// customisation support
	$this->first_availability = apply_filters( 'ph_bookings_modify_first_availability', $this->first_availability, $default_product->get_id() );

	$this->last_availability                  = get_post_meta( $default_product->get_id(), '_phive_last_availability', 1 );
	$this->shop_opening_time                  = get_post_meta( $default_product->get_id(), '_phive_book_working_hour_start', 1 );
	$this->first_availability_interval_period = get_post_meta( $default_product->get_id(), '_phive_first_availability_interval_period', 1 );
	$this->last_availability_interval_period  = get_post_meta( $default_product->get_id(), '_phive_last_availability_interval_period', 1 );
	$timezone                                 = get_option( 'timezone_string' );
	if ( empty( $timezone ) ) {
		$time_offset = $this->zone;
		$timezone    = timezone_name_from_abbr( '', $time_offset * 60 * 60, 0 );
	}
	if ( ! empty( $timezone ) ) {
		date_default_timezone_set( $timezone );
	}

	$start_date = '';
	$end_date   = '';
	if ( ! empty( $this->fixed_availability_from ) ) {

		$start_date = ! empty( $this->fixed_availability_from ) ? date_create( date( 'Y-m-d', strtotime( $this->fixed_availability_from ) ) ) : '';
		$end_date   = ! empty( $this->fixed_availability_to ) ? date_create( date( 'Y-m-d', strtotime( $this->fixed_availability_to ) ) ) : '';

	} elseif ( ! empty( $this->first_availability ) ) { // If realative to today

		$start_date = ! empty( $this->first_availability ) ? date_create( date( 'Y-m-d', strtotime( '+' . $this->first_availability . " $this->first_availability_interval_period " ) ) ) : '';
		$end_date   = ! empty( $this->last_availability ) ? date_create( date( 'Y-m-d', strtotime( '+' . $this->last_availability . " $this->last_availability_interval_period " ) ) ) : '';
	}

		$current_date = strtotime( date( 'Y-m-1' ) );
	// Start date
	if ( ! empty( $this->fixed_availability_from ) && strtotime( $this->fixed_availability_from ) >= $current_date ) {
		$month_start_date = date( 'Y', strtotime( $this->fixed_availability_from ) ) . '-' . date( 'm', strtotime( $this->fixed_availability_from ) ) . '-01';
	} else {
		$month_start_date = date( 'Y' ) . '-' . date( 'm' ) . '-01';
	}



	$month_start_date = apply_filters( 'ph_booking_calendar_start_date', $month_start_date, $default_product->get_id(), 'time-picker' );
	$min_avail_date   = '';
	$max_avail_date   = '';
	if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
		$min_diff       = date_diff( date_create( '-1 day' ), $start_date ); // Difference from yesterday.
		$min_avail_date = $min_diff->format( '%R%a days' );

		$max_diff       = date_diff( date_create( '-1 day' ), $end_date );
		$max_avail_date = $max_diff->format( '%R%a days' );
	}
	$calendar_start_date = ! empty( $start_date ) ? $start_date->format( 'Y-m-d' ) : date( 'Y-m-d' );

	if ( $this->assets_enabled == 'yes' ) {
		$this->phive_generate_assets_input_fields();
	}
	// $booking_text=apply_filters('ph_booking_pick_booking_period_text','Please pick a booking period',$default_product->get_id());
	$display_settings   = get_option( 'ph_bookings_display_settigns' );
	$text_customisation = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
	$pick_a_date        = isset( $text_customisation['pick_a_date'] ) && ! empty( $text_customisation['pick_a_date'] ) ? $text_customisation['pick_a_date'] : 'Please Pick a Date';

	$booking_date_text = apply_filters( 'ph_booking_pick_booking_date_text', $pick_a_date, $default_product->get_id() );

	?>
	<input type="hidden" id="min_avail_date" value="<?php echo $min_avail_date; ?>">
	<input type="hidden" id="max_avail_date" value="<?php echo $max_avail_date; ?>">

	<!-- <div class="callender-msg"><php _e( $booking_text, 'bookings-and-appointments-for-woocommerce' )?></div> -->
	
	<?php
	$across_the_day = get_post_meta( $product_id, '_phive_enable_across_the_day', 1 );
	if ( $ph_calendar_design == 3 && ! ( isset( $_GET['page'] ) && $_GET['page'] == 'add-booking' ) ) {
		$display_settings   = get_option( 'ph_bookings_display_settigns' );
		$text_customisation = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
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
			<div  class="ph-calendar-box-container time-picker-box-container fixed-blocks" >
				
				<div class="left-element inner-element">
					<!-- <div class="element-container"> -->
							<input class="element_from_date" type="text" name="" placeholder="<?php echo $start_date_text; ?>" value="" readonly="readonly"/>
							<img class="date_image element_from_date_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/calendar.svg">
							<span class="to-arrow dashicons dashicons-arrow-right-alt2"></span>
							<input class="element_from_time" type="text" name="" placeholder="<?php echo $start_time_text; ?>" readonly="readonly"/>
							<img class="time_image element_from_time_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/clock.svg">
						
					<!-- </div> -->
				</div>
				<!-- <div class="right-element inner-element">
					<div class="element-container">
							<input class="element_from_time" type="text" name="" placeholder="Start Time"/>
					</div>
				</div> -->
			</div>
			
			<?php
		} elseif ( $default_product->get_interval_type() == 'customer_choosen' ) {
			if ( $across_the_day == 'no' ) {
				?>
				<div  class="ph-calendar-box-container time-picker-box-container enable-range" >
					<div class="left-element inner-element across-day-disabled">
						<input class="element_from_date" type="text" name="" placeholder="<?php echo $start_date_text; ?>" value="" readonly="readonly"/>
						<img class="date_image element_from_date_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/calendar.svg">
						
					</div>
					<div class="center-element ">
						<!-- <span class="dashicons dashicons-arrow-down-alt"></span> -->
					</div>
					<div class="right-element inner-element across-day-disabled">
						<input class="element_from_time" type="text" name="" placeholder="<?php echo $start_time_text; ?>" value="" readonly="readonly"/>
						<img class="time_image element_from_time_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/clock.svg">
						<span class="to-arrow dashicons dashicons-arrow-right-alt2"></span>
						<!-- <input class="element_to_date" type="text" name="" placeholder="<?php echo $end_date_text; ?>" readonly="readonly" style="display:none;" /> -->
						<input class="element_to_time" type="text" name="" placeholder="<?php echo $end_time_text; ?>" value="" readonly="readonly"/>
						<img class="time_image element_to_time_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/clock.svg">						
					</div>
				</div>
				<?php
			} else {
				?>

				<div  class="ph-calendar-box-container time-picker-box-container enable-range" >
					<div class="left-element inner-element">
						<input class="element_from_date" type="text" name="" placeholder="<?php echo $start_date_text; ?>" value="" readonly="readonly"/>
						<img class="date_image element_from_date_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/calendar.svg">
						<input class="element_from_time" type="text" name="" placeholder="<?php echo $start_time_text; ?>" value="" readonly="readonly"/>
						<img class="time_image element_from_time_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/clock.svg">
					</div>
					<div class="center-element ">
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="right-element inner-element">
						<input class="element_to_date" type="text" name="" placeholder="<?php echo $end_date_text; ?>" readonly="readonly"/>
						<img class="date_image element_to_date_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/calendar.svg">
						<input class="element_to_time" type="text" name="" placeholder="<?php echo $end_time_text; ?>" value="" readonly="readonly"/>
						<img class="time_image element_to_time_image" src="<?php echo plugins_url( '', dirname( __DIR__ ) ); ?>/resources/icons/clock.svg">
					</div>
				</div>
		
				<?php
			}
		}
	}

	?>
	<div  class="ph-calendar-container">
		<div class="time-calendar-date-section">
			<?php
			echo $this->phive_generate_date_calendar_for_timepicker( $month_start_date, $display_settings );
			?>
		</div>
		<!-- <br> -->
		<div class="time-picker" style="margin-top:1em !important;">
			<ul class="ph-calendar-days ph-ul-time <?php echo ( $display_booking_capacity != 'yes' ) ? 'ph_booking_no_place_left' : ''; ?>" id="ph-calendar-time" style="display:none;">
				
			</ul>
		</div>
		
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
</div>

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
