<?php
	$ph_calendar_color = get_option( 'ph_booking_settings_calendar_color' );
	// $ph_calendar_month_color     = $ph_calendar_color['ph_calendar_month_color'] ;
	// $booking_full_color      = $ph_calendar_color['booking_full_color'];
	// $selected_date_color         = $ph_calendar_color['selected_date_color'];
	// $booking_info_wraper_color   = $ph_calendar_color['booking_info_wraper_color'];
	// $ph_calendar_weekdays_color = $ph_calendar_color['ph_calendar_weekdays_color'];
	// $ph_calendar_days_color  = $ph_calendar_color['ph_calendar_days_color'];

	$ph_calendar_month_color      = ! empty( $ph_calendar_color['ph_calendar_month_color'] ) ? $ph_calendar_color['ph_calendar_month_color'] : '#539bbe';
	$ph_calendar_month_text_color = ! empty( $ph_calendar_color['ph_calendar_month_text_color'] ) ? $ph_calendar_color['ph_calendar_month_text_color'] : '#ffffff';

	$booking_full_color  = ! empty( $ph_calendar_color['booking_full_color'] ) ? $ph_calendar_color['booking_full_color'] : '#dadada';
	$selected_date_color = ! empty( $ph_calendar_color['selected_date_color'] ) ? $ph_calendar_color['selected_date_color'] : '#6aa3f1';

	$booking_info_wraper_color      = ! empty( $ph_calendar_color['booking_info_wraper_color'] ) ? $ph_calendar_color['booking_info_wraper_color'] : '#539bbe';
	$booking_info_wraper_text_color = ! empty( $ph_calendar_color['booking_info_wraper_text_color'] ) ? $ph_calendar_color['booking_info_wraper_text_color'] : '#ffffff';

	$ph_calendar_weekdays_color      = ! empty( $ph_calendar_color['ph_calendar_weekdays_color'] ) ? $ph_calendar_color['ph_calendar_weekdays_color'] : '#ddd';
	$ph_calendar_weekdays_text_color = ! empty( $ph_calendar_color['ph_calendar_weekdays_text_color'] ) ? $ph_calendar_color['ph_calendar_weekdays_text_color'] : '#ffffff';

	$ph_calendar_days_color      = ! empty( $ph_calendar_color['ph_calendar_days_color'] ) ? $ph_calendar_color['ph_calendar_days_color'] : '#eee';
	$ph_calendar_days_text_color = ! empty( $ph_calendar_color['ph_calendar_days_text_color'] ) ? $ph_calendar_color['ph_calendar_days_text_color'] : '#777';

	$book_now_bg_color   = ! empty( $ph_calendar_color['book_now_bg_color'] ) ? $ph_calendar_color['book_now_bg_color'] : '#1a1a1a';
	$book_now_text_color = ! empty( $ph_calendar_color['book_now_text_color'] ) ? $ph_calendar_color['book_now_text_color'] : '#ffffff';



	$ph_calendar_design = ( isset( $ph_calendar_color['ph_calendar_design'] ) && ! empty( $ph_calendar_color['ph_calendar_design'] ) ) ? $ph_calendar_color['ph_calendar_design'] : 1; // default legacy design will display

	// new design colours
	$primary_bg_color   = ( isset( $ph_calendar_color['primary_bg_color'] ) && ! empty( $ph_calendar_color['primary_bg_color'] ) ) ? $ph_calendar_color['primary_bg_color'] : '#1791ce';
	$hover_bg_color     = ( isset( $ph_calendar_color['hover_bg_color'] ) && ! empty( $ph_calendar_color['hover_bg_color'] ) ) ? $ph_calendar_color['hover_bg_color'] : '#4baef1';
	$price_box_bg_color = ( isset( $ph_calendar_color['price_box_bg_color'] ) && ! empty( $ph_calendar_color['price_box_bg_color'] ) ) ? $ph_calendar_color['price_box_bg_color'] : '#ffffff';

	$price_box_text_color = ( isset( $ph_calendar_color['price_box_text_color'] ) && ! empty( $ph_calendar_color['price_box_text_color'] ) ) ? $ph_calendar_color['price_box_text_color'] : '#000000';

	$book_now_bg_color_design_2   = ! empty( $ph_calendar_color['book_now_bg_color_design_2'] ) ? $ph_calendar_color['book_now_bg_color_design_2'] : '#1a1a1a';
	$book_now_text_color_design_2 = ! empty( $ph_calendar_color['book_now_text_color_design_2'] ) ? $ph_calendar_color['book_now_text_color_design_2'] : '#ffffff';

	$text_color         = ( isset( $ph_calendar_color['text_color'] ) && ! empty( $ph_calendar_color['text_color'] ) ) ? $ph_calendar_color['text_color'] : '#fff';
	$booked_block_color = ( isset( $ph_calendar_color['booked_block_color'] ) && ! empty( $ph_calendar_color['booked_block_color'] ) ) ? $ph_calendar_color['booked_block_color'] : '#dadada';

	// design 3
	$start_date_box                 = ( isset( $ph_calendar_color['start_date_box'] ) && ! empty( $ph_calendar_color['start_date_box'] ) ) ? $ph_calendar_color['start_date_box'] : '#3f9dbc';
	$font_color_box                 = ( isset( $ph_calendar_color['font_color_box'] ) && ! empty( $ph_calendar_color['font_color_box'] ) ) ? $ph_calendar_color['font_color_box'] : '#1d3268';
	$booking_full_color_box         = ( isset( $ph_calendar_color['booking_full_color_box'] ) && ! empty( $ph_calendar_color['booking_full_color_box'] ) ) ? $ph_calendar_color['booking_full_color_box'] : '#dadada';
	$selected_date_color_box        = ( isset( $ph_calendar_color['selected_date_color_box'] ) && ! empty( $ph_calendar_color['selected_date_color_box'] ) ) ? $ph_calendar_color['selected_date_color_box'] : '#1d3268';
	$booking_wrapper_color_box      = ( isset( $ph_calendar_color['booking_wrapper_color_box'] ) && ! empty( $ph_calendar_color['booking_wrapper_color_box'] ) ) ? $ph_calendar_color['booking_wrapper_color_box'] : '#1d3268';
	$booking_wrapper_text_color_box = ( isset( $ph_calendar_color['booking_wrapper_text_color_box'] ) && ! empty( $ph_calendar_color['booking_wrapper_text_color_box'] ) ) ? $ph_calendar_color['booking_wrapper_text_color_box'] : '#ffffff';
	$book_now_bg_color_box          = ( isset( $ph_calendar_color['book_now_bg_color_box'] ) && ! empty( $ph_calendar_color['book_now_bg_color_box'] ) ) ? $ph_calendar_color['book_now_bg_color_box'] : '#1d3268';
	$book_now_text_color_box        = ( isset( $ph_calendar_color['book_now_text_color_box'] ) && ! empty( $ph_calendar_color['book_now_text_color_box'] ) ) ? $ph_calendar_color['book_now_text_color_box'] : '#ffffff';

if ( isset( $_POST['ph_booking_settings_calendar_sumitted'] ) ) {
	// error_log(print_r($_POST,1));
	$ph_calendar_month_color = empty( $_POST['month_color'] ) ? $ph_calendar_month_color : $_POST['month_color'];
	// $ph_calendar_month_color     = empty($_POST['ph-calendar-month-color'])   ? $ph_calendar_month_color     : $_POST['ph-calendar-month-color'];
	$booking_full_color         = empty( $_POST['booking-full-color'] ) ? $booking_full_color : $_POST['booking-full-color'];
	$selected_date_color        = empty( $_POST['selected-date-color'] ) ? $selected_date_color : $_POST['selected-date-color'];
	$booking_info_wraper_color  = empty( $_POST['booking-info-wraper-color'] ) ? $booking_info_wraper_color : $_POST['booking-info-wraper-color'];
	$ph_calendar_weekdays_color = empty( $_POST['ph-calendar-weekdays-color'] ) ? $ph_calendar_weekdays_color : $_POST['ph-calendar-weekdays-color'];
	$ph_calendar_days_color     = empty( $_POST['ph-calendar-days-color'] ) ? $ph_calendar_days_color : $_POST['ph-calendar-days-color'];
	$ph_calendar_design         = empty( $_POST['ph_calendar_design'] ) ? $ph_calendar_design : $_POST['ph_calendar_design'];

	$ph_calendar_month_text_color    = empty( $_POST['ph-calendar-month-text-color'] ) ? $ph_calendar_month_text_color : $_POST['ph-calendar-month-text-color'];
	$booking_info_wraper_text_color  = empty( $_POST['booking-info-wraper-text-color'] ) ? $booking_info_wraper_text_color : $_POST['booking-info-wraper-text-color'];
	$ph_calendar_weekdays_text_color = empty( $_POST['ph-calendar-weekdays-text-color'] ) ? $ph_calendar_weekdays_text_color : $_POST['ph-calendar-weekdays-text-color'];
	$ph_calendar_days_text_color     = empty( $_POST['ph-calendar-days-text-color'] ) ? $ph_calendar_days_text_color : $_POST['ph-calendar-days-text-color'];
	$book_now_bg_color               = empty( $_POST['ph-book-now-color'] ) ? $book_now_bg_color : $_POST['ph-book-now-color'];
	$book_now_text_color             = empty( $_POST['ph-book-now-text-color'] ) ? $book_now_text_color : $_POST['ph-book-now-text-color'];

	$primary_bg_color   = empty( $_POST['primary_bg_color'] ) ? $primary_bg_color : $_POST['primary_bg_color'];
	$hover_bg_color     = empty( $_POST['hover_bg_color'] ) ? $hover_bg_color : $_POST['hover_bg_color'];
	$price_box_bg_color = empty( $_POST['price_box_bg_color'] ) ? $price_box_bg_color : $_POST['price_box_bg_color'];
	$text_color         = empty( $_POST['text_color'] ) ? $text_color : $_POST['text_color'];
	$booked_block_color = empty( $_POST['booked_block_color'] ) ? $booked_block_color : $_POST['booked_block_color'];

	$price_box_text_color         = empty( $_POST['price_box_text_color'] ) ? $price_box_text_color : $_POST['price_box_text_color'];
	$book_now_bg_color_design_2   = empty( $_POST['book_now_bg_color_design_2'] ) ? $book_now_bg_color_design_2 : $_POST['book_now_bg_color_design_2'];
	$book_now_text_color_design_2 = empty( $_POST['book-now-text-color-design-2'] ) ? $book_now_text_color_design_2 : $_POST['book-now-text-color-design-2'];


	$start_date_box                 = empty( $_POST['start_date_box'] ) ? $start_date_box : $_POST['start_date_box'];
	$font_color_box                 = empty( $_POST['font_color_box'] ) ? $font_color_box : $_POST['font_color_box'];
	$booking_full_color_box         = empty( $_POST['booking_full_color_box'] ) ? $booking_full_color_box : $_POST['booking_full_color_box'];
	$selected_date_color_box        = empty( $_POST['selected_date_color_box'] ) ? $selected_date_color_box : $_POST['selected_date_color_box'];
	$booking_wrapper_color_box      = empty( $_POST['booking_wrapper_color_box'] ) ? $booking_wrapper_color_box : $_POST['booking_wrapper_color_box'];
	$booking_wrapper_text_color_box = empty( $_POST['booking_wrapper_text_color_box'] ) ? $booking_wrapper_text_color_box : $_POST['booking_wrapper_text_color_box'];
	$book_now_bg_color_box          = empty( $_POST['book_now_bg_color_box'] ) ? $book_now_bg_color_box : $_POST['book_now_bg_color_box'];
	$book_now_text_color_box        = empty( $_POST['book_now_text_color_box'] ) ? $book_now_text_color_box : $_POST['book_now_text_color_box'];
}

	$calendar_color = array(
		'ph_calendar_month_color'         => $ph_calendar_month_color,
		'booking_full_color'              => $booking_full_color,
		'selected_date_color'             => $selected_date_color,
		'booking_info_wraper_color'       => $booking_info_wraper_color,
		'ph_calendar_weekdays_color'      => $ph_calendar_weekdays_color,
		'ph_calendar_days_color'          => $ph_calendar_days_color,

		'ph_calendar_design'              => $ph_calendar_design,
		'primary_bg_color'                => $primary_bg_color,
		'hover_bg_color'                  => $hover_bg_color,
		'price_box_bg_color'              => $price_box_bg_color,
		'text_color'                      => $text_color,
		'booked_block_color'              => $booked_block_color,

		'price_box_text_color'            => $price_box_text_color,
		'book_now_bg_color_design_2'      => $book_now_bg_color_design_2,
		'book_now_text_color_design_2'    => $book_now_text_color_design_2,

		'book_now_text_color'             => $book_now_text_color,
		'book_now_bg_color'               => $book_now_bg_color,
		'booking_info_wraper_text_color'  => $booking_info_wraper_text_color,
		'ph_calendar_month_text_color'    => $ph_calendar_month_text_color,
		'ph_calendar_weekdays_text_color' => $ph_calendar_weekdays_text_color,
		'ph_calendar_days_text_color'     => $ph_calendar_days_text_color,


		'start_date_box'                  => $start_date_box,
		'font_color_box'                  => $font_color_box,
		'booking_full_color_box'          => $booking_full_color_box,
		'selected_date_color_box'         => $selected_date_color_box,
		'booking_wrapper_color_box'       => $booking_wrapper_color_box,
		'booking_wrapper_text_color_box'  => $booking_wrapper_text_color_box,
		'book_now_bg_color_box'           => $book_now_bg_color_box,
		'book_now_text_color_box'         => $book_now_text_color_box,
	);

	// error_log("calendar design");
	// error_log(print_r($calendar_color,1));

	update_option( 'ph_booking_settings_calendar_color', $calendar_color );

	?>
	<style type="text/css">
		.ph_calendar_custom6 .ph-calendar-month{
			background: <?php echo $ph_calendar_month_color; ?> !important;
			margin:0;
			padding:2% !important;
		}
		.ph_calendar_custom6 .booking-full{
			background: <?php echo $booking_full_color; ?> !important;
		}
		.ph_calendar_custom6 .timepicker-selected-date, .ph_calendar_custom6 .selected-date{
			background: <?php echo $selected_date_color; ?> !important;
		}
		.ph_calendar_custom6 .booking-info-wraper{
			background: <?php echo $booking_info_wraper_color; ?> !important;
		}
		.ph_calendar_custom6 .ph-calendar-weekdays{
			background: <?php echo $ph_calendar_weekdays_color; ?> !important;
			padding : 2% 0% !important;
		}
		.ph_calendar_custom6 .ph-calendar-days{
			background: <?php echo $ph_calendar_days_color; ?> !important;
			padding : 2% 0% !important;
		}
		.ph_calendar_custom6 .ph-calendar-days li{
			min-height: 0;
		}

		#ph_change_month_text_color
		{
			color: <?php echo $ph_calendar_month_text_color; ?> !important;
		}

		.ph_calendar_custom6 .ph-calendar-date.booking-full, .ph_calendar_custom3 .ph-calendar-date.booking-full{
			text-decoration: none;
		}

		.ph_calendar_design_new li.ph-calendar-date:hover{
			background: <?php echo $hover_bg_color; ?> !important;
		}

		.ph_calendar_custom6 li.ph-calendar-date:hover{
			background: <?php echo $selected_date_color; ?> !important;
		}

	</style>

	<style type="text/css">
		.ph_calendar_design,.ph_calendar_custom
		{

			border-radius: 5px ;
			padding: 2em !important;
			background-color: #1791ce ;
			margin: 2em;
			/* box-sizing: border-box !important; */
		}
		.ph_calendar_design
		{
			background-color: #1791ce ;
		}
		.ph_calendar_design .booking-full{
			background: #dadada  !important;
		}
		.ph_calendar_design .today{
			border: 0px solid #5ec1f3;
		}
		.ph_calendar_design.ph_calendar_design
		{
			width: 50%;
		}
		.booking-wraper-inner
		{
			border: 1px solid #e2e6ec;
			border-radius: 10px;
			box-shadow: 2px 2px 2px 1px rgba(0,0,0,.1);
			margin:5px;
		}
		.jQWCP-wWidget
		{
			margin-left: -220px !important;
		}

		.ph_calendar_design_new .ph-calendar-days li{
			min-height: 0;
			color: #fff;
		}

		.ph_calendar_custom6{
			background-color: transparent !important;
		}

		.booking-info-wraper{
			background: #ffffff !important;  
		}
	
		.ph_calendar_custom6 .today{
			border: none !important;
			border-radius: 0px;
			color: black !important;
		}

		.timepicker-selected-date, .selected-date,li.ph-calendar-date:hover{
			/* background: #f4fafd ; */
			color: #131515 !important;
			border: 0px solid transparent;
		}
		li.ph-calendar-date {
			/*height: 35px;*/
			padding-top: 4px;
			padding-bottom: 6px;
		}
		.booking-wraper{
			float: left;
		}
		.choose_design{
			margin: 2em;
		}
		.booking-wraper {
			color: black;
		}
		.ph_calendar_custom6 ul#ph-calendar-days {
			margin-left: 0px !important;
		}
		.ph_calendar_custom6 .booking-info-wraper {
				margin: 0em 0em; 
				margin-top: 1em; 
		}
		.ph_calendar_custom6  ul.ph-calendar-weekdays {
			padding: 13px 0px;
			margin-bottom: -13px;
		}
		.ph_calendar_custom6  div#month_color {
			padding: 12px 8px;
		}
		.ph-calendar-days,.ph-calendar-weekdays{
			margin-top: 0px !important;
		}
		.ph_calendar_custom6 .booking-info-wraper{
			border-radius: 0px;
		}
		.ph_calendar_custom6 .ph-calendar-month{
			margin-bottom: 0px !important;
		}
		.ph_calendar_custom.ph_calendar_custom6
		{
			width: 50%;
		}
		.ph_calendar_design_new .ph-calendar-days li.selected-date{
			background-color: #4baef1 !important;
		}
		

		
		.ph_calendar_custom6 .single_add_to_cart_button
		{
			border-radius:0 !important;
			margin: 0 !important;
			margin-top: 1em !important;
			color: <?php echo $book_now_text_color; ?> !important;
			background-color: <?php echo $book_now_bg_color; ?> !important;
		}

		.single_add_to_cart_button
		{
			font: inherit;
			/* background-color: #1a1a1a !important; */
			border-color: #1a1a1a !important;  
			/* color: #ffffff !important; */
			/* border-radius:0 !important; */
			cursor: not-allowed !important;
			padding: 0.5180469716em 1.31575em !important;
			margin-top: 1em !important;
			font-weight: 600 !important;
			display: inline-block !important;
			-webkit-appearance: none !important;
			font-size: 100%;
			line-height: 1.618;
			border:none !important;
		}

		.ph_calendar_design_new .single_add_to_cart_button
		{
			color: <?php echo $book_now_text_color_design_2; ?> !important;
			background-color: <?php echo $book_now_bg_color_design_2; ?> !important;
			margin: 0px 11px !important;
			margin-top: 1em !important;
			border-radius: 5px !important;
			float: none !important;
		}

		#booking_info_text
		{
			padding:2% !important;
		}

		.booking_info_text_design_2
		{
			color: <?php echo $price_box_text_color; ?> !important;
		}

		.booking_info_text_design_1
		{
			color: <?php echo $booking_info_wraper_text_color; ?> !important;
		}

		.ph_calendar_custom6 .ph-calendar-weekdays li
		{
			color: <?php echo $ph_calendar_weekdays_text_color; ?> !important;
		}

		.ph_calendar_custom6 .ph-calendar-days li
		{
			color: <?php echo $ph_calendar_days_text_color; ?> !important;
		}
		.ph_calendar_design2_form{
			margin-top: 22% !important;
		}
	</style>
	<style type="text/css">
		
		/*font color*/

		.ph_calendar_design_box .ph-calendar-weekdays li , .ph_calendar_design_box .span-month,.ph_calendar_design_box  .span-year,.ph_calendar_design_box .ph-prev,.ph-next , .ph_calendar_design_box .ph-calendar-days li,.ph_calendar_design_box .time-picker-wraper #ph-calendar-time li.ph-calendar-date , .ph_calendar_design_box .ph-calendar-date.today
		{
			color: <?php echo $font_color_box; ?> !important;
		}
		.ph_calendar_design_box .inner-element input::placeholder,.ph_calendar_design_box .inner-element input::-ms-input-placeholder{
			color: <?php echo $font_color_box; ?> !important;
		}
		.ph_calendar_design_box .booking-info-wraper p
		{
			color: <?php echo $booking_wrapper_text_color_box; ?> !important;
		}
		.ph_calendar_design_box li.ph-calendar-date.selected-date {
			color: white !important;
		}

		.ph_calendar_design_box .ph-calendar-box-container .inner-element input{
			text-align: center;
			color: <?php echo $font_color_box; ?> !important;
		}
		/*.ph_calendar_design_box .time-picker-wraper #ph-calendar-time li.ph-calendar-date:hover,.time-picker-wraper #ph-calendar-time li.ph-calendar-date.mouse_hover {
			background: #1e3368;
			color: white; 
		}*/
		.ph_calendar_design_box li.ph-calendar-date.mouse_hover, li.ph-calendar-date:hover {
			background: #1e3368 !important;
			color: white;
			/*background: #3f9dbc !important;*/
		}
		.ph_calendar_design_box button.single_add_to_cart_button.button.alt {
			color: <?php echo $book_now_text_color_box; ?>;
			background: <?php echo $book_now_bg_color_box; ?>;
		}
		.ph_calendar_design_box li.ph-calendar-date:hover {
			color: white !important;
		}


/*
		.ph_calendar_design_box .timepicker-selected-date, .selected-date{
			background: #1e3368 !important;
			border:  0px solid #1e3368 !important;
		}*/
		.ph_calendar_design_box li.ph-calendar-date.mouse_hover,li.ph-calendar-date:hover {
			background: <?php echo $selected_date_color_box; ?> !important;
		}
		.ph_calendar_design_box .booking-info-wraper {
			background: <?php echo $booking_wrapper_color_box; ?> !important;
			/*background: #3f9dbc !important;*/
		}
		.ph_calendar_design_box .selected-date {
			background: <?php echo $selected_date_color_box; ?> !important;
			/*background: #3f9dbc !important;*/
		}

		/*fixed*/
		.ph_calendar_design_box .ph-calendar-weekdays{
			background: white !important;
		}
		.ph_calendar_design_box .ph-calendar-days{
			background: white !important;
		}
		.ph_calendar_design_box li.ph-calendar-date.de-active:hover{
			background: none !important;
		}
		.ph_calendar_design_box .time-picker-wraper #ph-calendar-time li.ph-calendar-date
		{
			background: white;
		}
		.ph_calendar_design_box .ph-calendar-container {
			border: 1px solid #cccccc;
			background: white;
		}
		.ph_calendar_design_box ul.ph-calendar-weekdays {
			border-bottom: 1px solid #cccccc;
			background: #fff !important;
		}

		.ph_calendar_design_box .ph-calendar-days {
			background: #fff !important;
		}
		.ph_calendar_design_box .ph-calendar-month {
			background: #ffffff !important; 
		}

		.ph_calendar_design_box .booking-full {
			background: <?php echo $booking_full_color_box; ?> !important; 
		}
		.ph_calendar_design_box .ph-calendar-box-container .inner-element input:focus {
			background: <?php echo $start_date_box; ?> !important; 
		}
		.ph_calendar_design_box  .element_from_focused {
			background: <?php echo $start_date_box; ?> !important; 
		}



		.ph_calendar_design_box .button.alt
		{
			/*background-color: <?php // echo $book_now_bg_color ?> !important;*/
			/*color: <?php // echo $book_now_text_color ?> !important;*/
		}
		.ph_calendar_design_box .time-picker{
			margin-top:1em !important;
		}

		.ph_calendar_design_box .ph-calendar-month {
			border-bottom: 1px solid #cccccc;
		}


		.ph_calendar_design_box .time-picker {
			margin-top: 0px !important; 
			/*border-top: 1px solid #cccccc;*/
		}
		
		.ph_calendar_design_box .ph-calendar-container {
			display: block;
			position: inherit;
			margin: 5px 0 0 0;
		}
		.ph_calendar_design_box ul#ph-calendar-days {
			margin-left: 0px !important; 
			margin-top: 0px !important; 
		}
		.ph_calendar_design_box .inner-element input {
			line-height: 0 !important; 
		}
		.ph_calendar_design_box .booking-info-wraper {
			margin: 0 !important; 
			margin-top: 10px !important;
		}
		.ph-calendar-box-container .inner-element
		{
			width: 46.5% !important; 
		}
		.ph_calendar_design_box li.ph-calendar-date {
			min-height: auto;
		}
		.ph_calendar_design_box .inner-element input{
			border: 1px solid #cccccc;
		}
		.ph_calendar_design_box .ph-calendar-container {
			width: 100% !important;
		}
		.booking-wraper {
			border: 0px solid #cccccc; 
			padding: 0px;
			border-radius: 0px;
		}
	</style>
	<?php

	function phive_generate_calendar_for_colorpicker( $start_date ) {
		$end_date = strtotime( '+1 month', strtotime( $start_date ) );

		$day_order      = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		$callender_days = '<div id="ph-calendar-overlay" style="display:none"></div>';

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
		while ( $curr_date < $end_date ) {
			$css_classes = array( 'ph-calendar-date' );

			// if today.
			if ( $curr_date == strtotime( date( 'Y-m-d' ) ) ) {
				$css_classes[] = 'today';

			}

			if ( $curr_date == strtotime( '+1 day', strtotime( date( 'Y' ) . '-' . date( 'm' ) . '-01' ) ) ) {
				$css_classes[] = 'booking-full';
				$css_classes[] = 'de-active';

			}
			if ( $curr_date == strtotime( '+5 day', strtotime( date( 'Y' ) . '-' . date( 'm' ) . '-01' ) ) ) {
				$css_classes[] = 'selected-date';

			}
			$css_classes     = implode( ' ', array_unique( $css_classes ) );
			$callender_days .= '<li class="' . $css_classes . '"> ' . $html_input_bock_no . date( 'd', $curr_date ) . '</li>';

			$curr_date = strtotime( date( 'Y-m-d', strtotime( '+1 day', $curr_date ) ) );
			++$i;
		}
		return $callender_days;
	}
	?>
		<div class="calendar-designs-setttings" style="display:flex;flex-wrap: wrap;">
			<div class="booking-wraper ph_calendar_custom6" style="overflow:hidden;width:50%; display:flex;">
				<div class="booking-wraper-inner">
					<h4>
						<?php // echo _e('Click anywhere in the calendar to apply your desired colour.','bookings-and-appointments-for-woocommerce') ?>
					</h4>

					<div class="choose_design">
						<input id="calendar_design1" class="select_calendar_design" name="calendar_designs" type="radio" <?php echo ( $ph_calendar_design == 1 ) ? 'checked' : ''; ?> value="1" >
						<label for="calendar_design1"><i><?php echo __( 'Calendar Design 1', 'bookings-and-appointments-for-woocommerce' ); ?></i></label>
					</div>
					<div class='calender-division' style="display: flex;">
						<div class="ph_calendar_custom ph_calendar_custom6" style="margin:3%; margin-top: 0px;padding: 0em !important; height:100%; width:70%;">
							<div class="date-picker-wraper">
								<?php
									$start_date = date( 'Y' ) . '-' . date( 'm' ) . '-01';
									global $wp_version;
									$start_month_display = ph_wp_date( 'F', strtotime( $start_date ) );
								?>
								<input type="color" style="display:none" class="color-picker-input">
								<div class="ph-calendar-month"  id="month_color">	
									<ul>
										<li>
											<div class="month-year-wraper" id="ph_change_month_text_color">
												<!-- <span class="span-month"><php echo date_i18n( 'F', strtotime($start_date) );?></span> -->
												<span class="span-month"><?php echo $start_month_display; ?></span>
												<span class="span-year"><?php echo ph_wp_date( 'Y', strtotime( $start_date ) ); ?></span>

											</div>
										</li>
									</ul>
								</div>

								<ul class="ph-calendar-weekdays">
									<?php
										$week_days  = '<li>' . __( 'Mo', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Tu', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'We', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Th', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Fr', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Sa', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Su', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days  = apply_filters( 'ph_booking_calendar_weekdays_order', $week_days );
										echo $week_days;
									?>
								</ul>

								<ul class="ph-calendar-days" id="ph-calendar-days" style="position: relative;">
									<?php
										echo phive_generate_calendar_for_colorpicker( $start_date );
									?>
								</ul>
							</div>
							<div class="booking-info-wraper">
								<p id="booking_info_text" class="booking_info_text_design_1"><?php echo __( 'Booking Details', 'bookings-and-appointments-for-woocommerce' ); ?></p>
							</div>
							<button type="submit" name="book_now_button" class="single_add_to_cart_button button alt "><?php echo __( 'Book Now', 'bookings-and-appointments-for-woocommerce' ); ?></button>
						</div>
						<div class="">
							<div class='label-split' style="display: flex;">
								<label style="width: 100%;display: block;"><?php echo __( 'Change the background colors by clicking in the box', 'bookings-and-appointments-for-woocommerce' ); ?></label>
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Month', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $ph_calendar_month_color; ?>" class="month_color" name="month_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Month Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $ph_calendar_month_text_color; ?>" class="month_text_color" name="month_text_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Weekdays', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $ph_calendar_weekdays_color; ?>" class="week_days_color" name="week_days_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Weekdays Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $ph_calendar_weekdays_text_color; ?>" class="week_days_text_color" name="week_days_text_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Dates', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $ph_calendar_days_color; ?>" class="day_color" name="day_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Dates Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $ph_calendar_days_text_color; ?>" class="day_text_color" name="day_text_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Summary', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $booking_info_wraper_color; ?>" class="booking_wrapper_color" name="booking_wrapper_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Summary Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $booking_info_wraper_text_color; ?>" class="booking_wrapper_text_color" name="booking_wrapper_text_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Booked Dates', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $booking_full_color; ?>" class="booked_full_color" name="booked_full_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Selected Date', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $selected_date_color; ?>" class="selected_color" name="selected_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Book Now', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $book_now_bg_color; ?>" class="book_now_bg_color" name="book_now_bg_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Book Now Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $book_now_text_color; ?>" class="book_now_text_color" name="book_now_text_color" style="display: block;width: 30%;">
							</div>
							<br>
						</div>
					</div>
					<div style="width:100%; float:left; margin:2%;">
						<form method="POST">
							<input type="hidden" class="ph-calendar-month-color month_color" name="month_color">
							<!-- <input type="hidden" class="ph-calendar-month-color" name="ph-calendar-month-color"> -->
							<input type="hidden" class="calendar_design" name="ph_calendar_design" value="<?php echo $ph_calendar_design; ?>">
							<input type="hidden" class="booking-full-color" name="booking-full-color" >
							<input type="hidden" class="selected-date-color" name="selected-date-color" >
							<input type="hidden" class="booking-info-wraper-color" name="booking-info-wraper-color" >
							<input type="hidden" class="ph-calendar-weekdays-color" name="ph-calendar-weekdays-color" >
							<input type="hidden" class="ph-calendar-days-color" name="ph-calendar-days-color" >
							
							<input type="hidden" class="ph-calendar-month-text-color" name="ph-calendar-month-text-color" >
							<input type="hidden" class="ph-calendar-weekdays-text-color" name="ph-calendar-weekdays-text-color" >
							<input type="hidden" class="booking-info-wraper-text-color" name="booking-info-wraper-text-color" >
							<input type="hidden" class="ph-calendar-days-text-color" name="ph-calendar-days-text-color" >
							<input type="hidden" class="ph-book-now-color" name="ph-book-now-color">
							<input type="hidden" class="ph-book-now-text-color" name="ph-book-now-text-color">

							<input type="submit" class="button-primary woocommerce-save-button" name="ph_booking_settings_calendar_sumitted" value="<?php _e( 'Save Changes', 'bookings-and-appointments-for-woocommerce' ); ?>">
							<input type="button" class="button-primary woocommerce-save-button" name="ph_booking_settings_calendar_reset_color" id="ph_reset_color1" value="<?php _e( 'Reset to Default', 'bookings-and-appointments-for-woocommerce' ); ?>">



						</form>
					</div>
				</div>
			</div>

			<div class="booking-wraper " style="overflow:hidden;width:50%;display:flex;">
				<div class="booking-wraper-inner">		
					<div class="choose_design">
						<input id="calendar_design2" class="select_calendar_design" name="calendar_designs" type="radio" <?php echo ( $ph_calendar_design == 2 ) ? 'checked' : ''; ?> value="2" >
						<label for="calendar_design2"><i><?php echo __( 'Calendar Design 2', 'bookings-and-appointments-for-woocommerce' ); ?></i></label>
					</div>
					<div class='calender-division' style="display:flex;">
						<div class="ph_calendar_design_new ph_calendar_design" style="margin: 3%; margin-top: 0; width:70%;">
							<div class="date-picker-wraper">
								<?php
									$start_date = date( 'Y' ) . '-' . date( 'm' ) . '-01';
									global $wp_version;
									$start_month_display = ph_wp_date( 'F', strtotime( $start_date ) );
								?>
								<div class="ph-calendar-month"  id="month_color">	
									<ul>
										<li>
											<div class="month-year-wraper">
												<span class="span-month"><?php echo $start_month_display; ?></span>
												<span class="span-year"><?php echo ph_wp_date( 'Y', strtotime( $start_date ) ); ?></span>

											</div>
										</li>
									</ul>
								</div>

								<ul class="ph-calendar-weekdays">
									<?php
									echo '<li>' . __( 'Mo', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
									echo '<li>' . __( 'Tu', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
									echo '<li>' . __( 'We', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
									echo '<li>' . __( 'Th', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
									echo '<li>' . __( 'Fr', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
									echo '<li>' . __( 'Sa', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
									echo '<li>' . __( 'Su', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
									?>
								</ul>

								<ul class="ph-calendar-days" id="ph-calendar-days" style="position: relative;">
									<?php
										echo phive_generate_calendar_for_colorpicker( $start_date );
									?>
								</ul>
							</div>
							<div class="booking-info-wraper">
								<p id="booking_info_text" class="booking_info_text_design_2" ><?php echo __( 'Booking Details', 'bookings-and-appointments-for-woocommerce' ); ?></p>
							</div>
							<button type="submit" name="book_now_button" class="single_add_to_cart_button button alt "><?php echo __( 'Book Now', 'bookings-and-appointments-for-woocommerce' ); ?></button>
						</div>
						<div class="">
							<div class='label-split' style="display: flex;">
								<label style="width: 100%;display: block;"><?php echo __( 'Change the colors by clicking in the box', 'bookings-and-appointments-for-woocommerce' ); ?></label>
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Primary Background', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $primary_bg_color; ?>" class="primary_bg_color" name="primary_bg_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Summary Box', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $price_box_bg_color; ?>" class="price_box_bg_color" name="price_box_bg_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Summary Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $price_box_text_color; ?>" class="price_box_text_color" name="price_box_text_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $text_color; ?>" class="text_color" name="text_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Booked Dates', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $booked_block_color; ?>" class="booked_block_color" name="booked_block_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Selected Date', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $hover_bg_color; ?>" class="hover_bg_color" name="hover_bg_color" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Book Now Background', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $book_now_bg_color_design_2; ?>" class="book_now_bg_color_design_2" name="book_now_bg_color_design_2" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Book Now Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $book_now_text_color_design_2; ?>" class="book_now_text_color_design_2" name="book_now_text_color_design_2" style="display: block;width: 30%;">
							</div>
							<br>
						</div>
					</div>
					<div class="ph_calendar_design2_form" style="width:100%; float:left; margin:2%;">
						<form method="POST">
							
							<input type="hidden" class="calendar_design" name="ph_calendar_design" value="<?php echo $ph_calendar_design; ?>">
							

							<input type="hidden" class="primary_bg_color" name="primary_bg_color">
							<input type="hidden" class="hover_bg_color" name="hover_bg_color">
							<input type="hidden" class="price_box_bg_color" name="price_box_bg_color">
							<input type="hidden" class="text_color" name="text_color">
							<input type="hidden" class="booked_block_color" name="booked_block_color">
							
							<input type="hidden" class="price_box_text_color" name="price_box_text_color">
							<input type="hidden" class="book_now_bg_color_design_2" name="book_now_bg_color_design_2">
							<input type="hidden" class="book-now-text-color-design-2" name="book-now-text-color-design-2">
						
							<input type="submit" class="button-primary woocommerce-save-button" name="ph_booking_settings_calendar_sumitted" value="<?php _e( 'Save Changes', 'bookings-and-appointments-for-woocommerce' ); ?>">
							<input type="button" class="button-primary woocommerce-save-button" name="ph_booking_settings_calendar_reset_color" id="ph_reset_color2" value="<?php _e( 'Reset to Default', 'bookings-and-appointments-for-woocommerce' ); ?>">


						</form>
					</div>
				</div>
			</div>

			<div class="booking-wraper ph_calendar_custom3 ph_calendar_design_box" style="overflow:hidden;width:50%; display:flex;">
				<div class="booking-wraper-inner">
					

					<div class="choose_design">
						<input id="calendar_design3" class="select_calendar_design" name="calendar_designs" type="radio" <?php echo ( $ph_calendar_design == 3 ) ? 'checked' : ''; ?> value="3" >
						<label for="calendar_design3"><i><?php echo __( 'Calendar Design 3', 'bookings-and-appointments-for-woocommerce' ); ?></i></label>
					</div>

					<div class='calender-division' style="display: flex;">
						<div class="ph_calendar_custom ph_calendar_custom3" style="margin:3%; margin-top: 0px;padding: 0em !important; height:100%; width:70%;background: none;">

								<div  class="ph-calendar-box-container date-calendar enable-range" >
									<div class="left-element inner-element">
										<div class="element-container">
											<input class="element_from" type="text" name="" placeholder="Start Date" value="<?php echo __( 'Start Date', 'bookings-and-appointments-for-woocommerce' ); ?>"  readonly="readonly"/>
										</div>
									</div>
									<div class="center-element">
										<span class="dashicons dashicons-arrow-right-alt2"></span>
									</div>
									<div class="right-element inner-element">
										<div class="element-container">
											<input class="element_to" type="text" name="" placeholder="End Date" value="<?php echo __( 'End Date', 'bookings-and-appointments-for-woocommerce' ); ?>"  readonly="readonly"/>
										</div>
									</div>
								</div>
							<div class="booking-info-wraper">
								<p id="booking_info_text" class="booking_info_text_design_1"><?php echo __( 'Booking Details', 'bookings-and-appointments-for-woocommerce' ); ?></p>
							</div>
							<button type="submit" name="book_now_button" class="single_add_to_cart_button button alt "><?php echo __( 'Book Now', 'bookings-and-appointments-for-woocommerce' ); ?></button>
							<h4><?php echo __( 'Calendar on click', 'bookings-and-appointments-for-woocommerce' ); ?></h4>
								<div  class="ph-calendar-box-container date-calendar enable-range" >
									<div class="left-element inner-element">
										<div class="element-container">
											<input class="element_from element_from_focused" type="text" name="" placeholder="Start Date" value="<?php echo __( 'Start Date', 'bookings-and-appointments-for-woocommerce' ); ?>"  readonly="readonly"/>
										</div>
									</div>
									<div class="center-element">
										<span class="dashicons dashicons-arrow-right-alt2"></span>
									</div>
									<div class="right-element inner-element">
										<div class="element-container">
											<input class="element_to" type="text" name="" placeholder="End Date" value="<?php echo __( 'End Date', 'bookings-and-appointments-for-woocommerce' ); ?>"  readonly="readonly"/>
										</div>
									</div>
								</div>
							<div class="date-picker-wraper ph-calendar-container">
								<?php
									$start_date = date( 'Y' ) . '-' . date( 'm' ) . '-01';
									global $wp_version;
									$start_month_display = ph_wp_date( 'F', strtotime( $start_date ) );
								?>
								<input type="color" style="display:none" class="color-picker-input">
								<div class="ph-calendar-month"  id="month_color">	
									<ul>
										<li>
											<div class="month-year-wraper" id="ph_change_month_text_color">
												<!-- <span class="span-month"><php echo date_i18n( 'F', strtotime($start_date) );?></span> -->
												<span class="span-month"><?php echo $start_month_display; ?></span>
												<span class="span-year"><?php echo ph_wp_date( 'Y', strtotime( $start_date ) ); ?></span>

											</div>
										</li>
									</ul>
								</div>

								<ul class="ph-calendar-weekdays">
									<?php
										$week_days  = '<li>' . __( 'Mo', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Tu', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'We', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Th', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Fr', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Sa', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days .= '<li>' . __( 'Su', 'bookings-and-appointments-for-woocommerce' ) . '</li>';
										$week_days  = apply_filters( 'ph_booking_calendar_weekdays_order', $week_days );
										echo $week_days;
									?>
								</ul>

								<ul class="ph-calendar-days" id="ph-calendar-days" style="position: relative;">
									<?php
										echo phive_generate_calendar_for_colorpicker( $start_date );
									?>
								</ul>
							</div>
						</div>
						<div class="">
							<div class='label-split' style="display: flex;">
								<label style="width: 100%;display: block;"><?php echo __( 'Change colors by clicking in the box', 'bookings-and-appointments-for-woocommerce' ); ?></label>
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Font Color', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $font_color_box; ?>" class="font_color_box" name="font_color_box_name" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Summary', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $booking_wrapper_color_box; ?>" class="booking_wrapper_color_box" name="booking_wrapper_color_box_name" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Summary Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $booking_wrapper_text_color_box; ?>" class="booking_wrapper_text_color_box" name="booking_wrapper_text_color_box_name" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Book Now', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $book_now_bg_color_box; ?>" class="book_now_bg_color_box" name="book_now_bg_color_box_name" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Book Now Text', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $book_now_text_color_box; ?>" class="book_now_text_color_box" name="book_now_text_color_box_name" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="    display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Date Box on Click', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $start_date_box; ?>" class="start_date_box" name="start_date_box_name" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Booked Dates', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $booking_full_color_box; ?>" class="booking_full_color_box" name="booking_full_color_box_name" style="display: block;width: 30%;">
							</div>
							<hr>
							<div class='label-split' style="display: flex;">
								<label style="width: 65%;display: block;"><?php echo __( 'Selected Date', 'bookings-and-appointments-for-woocommerce' ); ?></label>
								<input type="text" value="<?php echo $selected_date_color_box; ?>" class="selected_date_color_box" name="selected_date_color_box_name" style="display: block;width: 30%;">
							</div>
							<hr>
							<br>
						</div>
					</div>

					<div style="width:100%; float:left; margin:2%;">
						<form method="POST">
							<input type="hidden" class="calendar_design" name="ph_calendar_design" value="<?php echo $ph_calendar_design; ?>">
							

							<input type="hidden" class="start_date_box_form" name="start_date_box">
							<input type="hidden" class="font_color_box_form" name="font_color_box">
							<input type="hidden" class="booking_full_color_box_form" name="booking_full_color_box">
							<input type="hidden" class="selected_date_color_box_form" name="selected_date_color_box">
							<input type="hidden" class="booking_wrapper_color_box_form" name="booking_wrapper_color_box">
							<input type="hidden" class="booking_wrapper_text_color_box_form" name="booking_wrapper_text_color_box">
							<input type="hidden" class="book_now_bg_color_box_form" name="book_now_bg_color_box">
							<input type="hidden" class="book_now_text_color_box_form" name="book_now_text_color_box">

							<input type="submit" class="button-primary woocommerce-save-button" name="ph_booking_settings_calendar_sumitted" value="<?php _e( 'Save Changes', 'bookings-and-appointments-for-woocommerce' ); ?>">
							<input type="button" class="button-primary woocommerce-save-button" name="ph_booking_settings_calendar_reset_color" id="ph_reset_color3" value="<?php _e( 'Reset to Default', 'bookings-and-appointments-for-woocommerce' ); ?>">


						</form>
					</div>
				</div>
			</div>
		<!-- </div> -->
			<!-- <div style="width:100%; float:left; margin:2%;">
				<form method="POST">
					<input type="hidden" class="ph-calendar-month-color month_color" name="month_color">
					<input type="hidden" class="calendar_design" name="ph_calendar_design" value="<?php // echo $ph_calendar_design; ?>">
					<input type="hidden" class="booking-full-color" name="booking-full-color" >
					<input type="hidden" class="selected-date-color" name="selected-date-color" >
					<input type="hidden" class="booking-info-wraper-color" name="booking-info-wraper-color" >
					<input type="hidden" class="ph-calendar-weekdays-color" name="ph-calendar-weekdays-color" >
					<input type="hidden" class="ph-calendar-days-color" name="ph-calendar-days-color" >
					
					<input type="hidden" class="ph-calendar-month-text-color" name="ph-calendar-month-text-color" >
					<input type="hidden" class="ph-calendar-weekdays-text-color" name="ph-calendar-weekdays-text-color" >
					<input type="hidden" class="booking-info-wraper-text-color" name="booking-info-wraper-text-color" >
					<input type="hidden" class="ph-calendar-days-text-color" name="ph-calendar-days-text-color" >
					<input type="hidden" class="ph-book-now-color" name="ph-book-now-color">
					<input type="hidden" class="ph-book-now-text-color" name="ph-book-now-text-color">


					<input type="hidden" class="primary_bg_color" name="primary_bg_color">
					<input type="hidden" class="hover_bg_color" name="hover_bg_color">
					<input type="hidden" class="price_box_bg_color" name="price_box_bg_color">
					<input type="hidden" class="text_color" name="text_color">
					<input type="hidden" class="booked_block_color" name="booked_block_color">
					
					<input type="hidden" class="price_box_text_color" name="price_box_text_color">
					<input type="hidden" class="book-now-bg-color-design-2" name="book-now-bg-color-design-2">
					<input type="hidden" class="book-now-text-color-design-2" name="book-now-text-color-design-2">
				
					<input type="submit" class="button-primary woocommerce-save-button" name="ph_booking_settings_calendar_sumitted" value="<?php // _e('Save Changes','bookings-and-appointments-for-woocommerce'); ?>">
					<input type="button" class="button-primary woocommerce-save-button" name="ph_booking_settings_calendar_reset_color" id="ph_reset_color" value="<?php // _e('Reset to Default','bookings-and-appointments-for-woocommerce'); ?>">


					<input type="hidden" class="start_date_box_form" name="start_date_box">
					<input type="hidden" class="font_color_box_form" name="font_color_box">
					<input type="hidden" class="booking_full_color_box_form" name="booking_full_color_box">
					<input type="hidden" class="selected_date_color_box_form" name="selected_date_color_box">
					<input type="hidden" class="booking_wrapper_color_box_form" name="booking_wrapper_color_box">
					<input type="hidden" class="booking_wrapper_text_color_box_form" name="booking_wrapper_text_color_box">
					<input type="hidden" class="book_now_bg_color_box_form" name="book_now_bg_color_box">
					<input type="hidden" class="book_now_text_color_box_form" name="book_now_text_color_box">

				</form>
			</div> -->
		</div>
	<script>
	jQuery(document).ready(function() {
		var className;

		jQuery('.month_color').wheelColorPicker();
		jQuery('.ph_calendar_custom6 .selected_color').wheelColorPicker();
		jQuery('.booking_wrapper_color').wheelColorPicker();
		jQuery('.booked_full_color').wheelColorPicker();
		jQuery('.week_days_color').wheelColorPicker();
		jQuery('.day_color').wheelColorPicker();

		jQuery('.month_text_color').wheelColorPicker();
		jQuery('.week_days_text_color').wheelColorPicker();
		jQuery('.day_text_color').wheelColorPicker();
		jQuery('.booking_wrapper_text_color').wheelColorPicker();
		jQuery('.ph_calendar_custom6 .book_now_bg_color').wheelColorPicker();
		jQuery('.ph_calendar_custom6 .book_now_text_color').wheelColorPicker();	

		
		jQuery('.day_text_color').on('colorchange', function(e)
		{
			selected_date_bg = jQuery('.selected_color').val();
			booking_full_bg = jQuery('.booked_full_color').val();
			if (jQuery('.selected-date-color').val()) 
			{
				selected_date_bg = jQuery('.selected-date-color').val();
			}
			if (jQuery('.booking-full-color').val()) 
			{
				booking_full_bg = jQuery('.booking-full-color').val();
			}

			jQuery('.ph_calendar_custom6 .ph-calendar-days li').css('cssText','color:#'+jQuery(this).val()+" !important;");
			jQuery('.ph_calendar_custom6 .selected-date').css('cssText','color:#'+jQuery(this).val()+" !important; background-color:"+selected_date_bg+"!important");
			jQuery('.ph_calendar_custom6 .booking-full').css('cssText','color:#'+jQuery(this).val()+" !important; background-color:"+booking_full_bg+"!important");

			jQuery('.ph-calendar-days-text-color').val('#'+jQuery(this).val());
		});

		jQuery('.month_text_color').on('colorchange', function(e){
			jQuery('#ph_change_month_text_color').css('cssText','color:#'+jQuery(this).val()+" !important;");
			jQuery('.ph-calendar-month-text-color').val('#'+jQuery(this).val());
			jQuery('.month_text_color').val('#'+jQuery(this).val());
		});

		jQuery('.booking_wrapper_text_color').on('colorchange', function(e){
			jQuery('.booking_info_text_design_1').css('cssText','color:#'+jQuery(this).val()+" !important;");
			jQuery('.booking-info-wraper-text-color').val('#'+jQuery(this).val());
		});

		jQuery('.ph_calendar_custom6 .book_now_bg_color').on('colorchange', function(e){
			color = jQuery('.ph-book-now-text-color').val();
			
			if (color != '') 
			{
				jQuery('.ph_calendar_custom6 .single_add_to_cart_button').css('cssText','background-color:#'+jQuery(this).val()+' !important; color:'+color+'!important;');
			}
			else
				jQuery('.ph_calendar_custom6 .single_add_to_cart_button').css('cssText','background-color:#'+jQuery(this).val()+" !important;");
			jQuery('.ph-book-now-color').val('#'+jQuery(this).val());
		});

		jQuery('.ph_calendar_custom6 .book_now_text_color').on('colorchange', function(e)
		{
			bg = jQuery('.ph-book-now-color').val();
			
			if (bg != '') 
			{
				jQuery('.ph_calendar_custom6 .single_add_to_cart_button').css('cssText','color:#'+jQuery(this).val()+' !important; background-color:'+bg+'!important');
			}
			else
				jQuery('.ph_calendar_custom6 .single_add_to_cart_button').css('cssText','color:#'+jQuery(this).val()+" !important;");
			jQuery('.ph-book-now-text-color').val('#'+jQuery(this).val());
		});

		jQuery('.month_color').on('colorchange', function(e){
			jQuery('.ph_calendar_custom6 .ph-calendar-month').css('cssText','background-color:#'+jQuery(this).val()+" !important;");
			jQuery('.ph-calendar-month-color').val('#'+jQuery(this).val());
		});

		jQuery('.ph_calendar_custom6 .selected_color').on('colorchange', function(e){
			day_text_color = jQuery('.day_text_color').val();
			if (jQuery('.ph-calendar-days-text-color').val()) 
			{
				day_text_color = jQuery('.ph-calendar-days-text-color').val();
			}
			jQuery('.ph_calendar_custom6 .selected-date').css('cssText','background-color:#'+jQuery(this).val()+" !important;min-height: 0px; color:"+day_text_color+"!important;");
			jQuery('.selected-date-color').val('#'+jQuery(this).val());
		});

		jQuery('.booking_wrapper_color').on('colorchange', function(e){
			jQuery('.ph_calendar_custom6 .booking-info-wraper').css('cssText','background-color:#'+jQuery(this).val()+" !important;");
			jQuery('.booking-info-wraper-color').val('#'+jQuery(this).val());
		});

		jQuery('.booked_full_color').on('colorchange', function(e)
		{
			day_text_color = jQuery('.day_text_color').val();
			if (jQuery('.ph-calendar-days-text-color').val()) 
			{
				day_text_color = jQuery('.ph-calendar-days-text-color').val();
			}
			jQuery('.ph_calendar_custom6 .booking-full').css('cssText','background-color:#'+jQuery(this).val()+" !important;min-height: 0px; color:"+day_text_color+"!important;");
			jQuery('.booking-full-color').val('#'+jQuery(this).val());
		});

		jQuery('.week_days_color').on('colorchange', function(e)
		{
			jQuery('.ph_calendar_custom6 .ph-calendar-weekdays').css('cssText','background-color:#'+jQuery(this).val()+" !important;");
			jQuery('.ph-calendar-weekdays-color').val('#'+jQuery(this).val());
		});

		jQuery('.week_days_text_color').on('colorchange', function(e)
		{
			bg = jQuery('.week_days_color').val();
			jQuery('.ph_calendar_custom6 .ph-calendar-weekdays li').css('cssText','color:#'+jQuery(this).val()+'!important;');
			jQuery('.ph-calendar-weekdays-text-color').val('#'+jQuery(this).val());
		});


		jQuery('.day_color').on('colorchange', function(e){
			jQuery('.ph_calendar_custom6 .ph-calendar-days').css('cssText','background-color:#'+jQuery(this).val()+" !important;");
			jQuery('.ph-calendar-days-color').val('#'+jQuery(this).val());
		});


		jQuery('#ph_reset_color1').on('click',function()
		{
			var default_color = 
			{
				'ph-calendar-month' : '#539bbe','booking-full' : '#dadada','selected-date': '#6aa3f1','booking-info-wraper' : '#539bbe','ph-calendar-weekdays' 	: '#ddd','ph-calendar-days' : '#eee'
			};
			// jQuery.each(default_color,function(key,value){
			// 	jQuery('.'+key+'-color').val(value);
			// 	jQuery('.'+key).attr('style','background-color:'+value+'!important');
				
			// })

			jQuery.each(default_color,function(key,value)
			{
				jQuery('.'+key+'-color').val(value);
				
				if(key == 'selected-date' || key == 'booking-full')
				{
					jQuery('.ph_calendar_custom6 .'+key).attr('style','background-color:'+value+'!important; min-height:0;');	
				}
				else
				{
					jQuery('.ph_calendar_custom6 .'+key).attr('style','background-color:'+value+'!important;');
				}
			});

			jQuery('.ph_calendar_custom6 .single_add_to_cart_button').css('cssText','background-color:#1a1a1a !important; color:#ffffff !important;');
			jQuery('.ph_calendar_custom6 .ph-calendar-weekdays li').css('cssText','color:#777 !important;');
			jQuery('.ph_calendar_custom6 .ph-calendar-days li').css('cssText','color:#777 !important;');
			jQuery('.ph_calendar_custom6 .selected-date ').css('cssText','color:#777 !important;background-color:#6aa3f1 !important;');
			jQuery('.ph_calendar_custom6 .booking-full').css('cssText','color:#777 !important;background-color:#dadada !important;');

			jQuery('.booking_info_text_design_1').attr('style','color:#ffffff !important;');
			jQuery('.booking-info-wraper-text-color').val('#ffffff');
			jQuery('.booking_wrapper_text_color').val('#ffffff');

			jQuery('.ph-book-now-color').val('#1a1a1a');
			jQuery('.ph-book-now-text-color').val('#ffffff');
			jQuery('.ph_book_now_color').val('#1a1a1a');
			jQuery('.ph_book_now_text_color').val('#ffffff');

			jQuery('#ph_change_month_text_color').css('cssText','color:#ffffff !important;');
			jQuery('.ph-calendar-month-text-color').val('#ffffff');
			jQuery('.month_text_color').val('#ffffff');

			jQuery('.week_days_text_color').val('#777');
			jQuery('.ph-calendar-weekdays-text-color').val('#777');

			jQuery('.ph-calendar-days-text-color').val('#777');
			jQuery('.day_text_color').val('#777');


		});

		jQuery('#ph_reset_color2').on('click',function()
		{
			

			var default_color_calendar_2 = 
			{
				'booking-full' : '#dadada', 'booking-info-wraper' : '#ffffff','selected-date' : '#4baef1'
			};

			jQuery.each(default_color_calendar_2,function(key,value)
			{
				
				jQuery('.ph_calendar_design_new .'+key).attr('style','background-color:'+value+'!important');
			});

			jQuery('.ph_calendar_design_new').attr('style','background-color:#1791ce; margin:3%; width:70%; margin-top: 0;');
			jQuery('.ph_calendar_design_new .ph-calendar-days li').attr('style','color:#fff !important; min-height:0;');
			jQuery('.ph_calendar_design_new .selected-date').attr('style','background-color:#4baef1 !important; min-height:0;');
			jQuery('.ph_calendar_design_new .ph-calendar-weekdays li').attr('style','color:#fff !important');
			jQuery('.ph_calendar_design_new .ph-calendar-month ul li').attr('style','color:#fff !important');

			jQuery('.primary_bg_color').val('#1791ce');
			jQuery('.hover_bg_color').val('#4baef1');
			jQuery('.price_box_bg_color').val('#ffffff');
			jQuery('.text_color').val('#ffffff');
			jQuery('.booked_block_color').val('#dadada');

			jQuery('.booking_info_text_design_2').attr('style','color:#000000 !important;');
			jQuery('.price_box_text_color').val('#000000');

			jQuery('.ph_calendar_design .single_add_to_cart_button').css('cssText','background-color:#1a1a1a !important; color:#ffffff !important;');
			jQuery('.book_now_bg_color_design_2').val('#1a1a1a');
			jQuery('.book-now-text-color-design-2').val('#ffffff');
			jQuery('.book_now_bg_color_design_2').val('#1a1a1a');
			jQuery('.book_now_text_color_design_2').val('#ffffff');



			


		});

		jQuery('#ph_reset_color3').on('click',function()
		{
			

			var default_color_calendar_3 = 
			{
				'start_date_box' : '#3f9dbc','font_color_box' : '#1d3268', 'booking_full_color_box' : '#dadada','selected_date_color_box' : '#1d3268','booking_wrapper_color_box' : '#1d3268','booking_wrapper_text_color_box' : '#ffffff','book_now_bg_color_box' : '#1d3268','book_now_text_color_box' : '#ffffff'
			};
			jQuery.each(default_color_calendar_3,function(key,value)
			{
				console.log('.ph_calendar_design_box .'+key+'_form');
				jQuery('.ph_calendar_design_box .'+key).val(value);
				jQuery('.'+key+'_form').val(value);
				// jQuery('.ph_calendar_design_box .'+key).attr('style','background-color:'+value+'!important');
			});

			jQuery('.ph_calendar_design_box .element_from_focused').attr('style','background-color:'+default_color_calendar_3.start_date_box+'!important');

			jQuery('.ph_calendar_design_box .inner-element input').attr('style','color:'+default_color_calendar_3.font_color_box+'!important');
			jQuery('.ph_calendar_design_box .ph-calendar-date').attr('style','color:'+default_color_calendar_3.font_color_box+'!important');
			jQuery('.ph_calendar_design_box .ph-calendar-weekdays li').attr('style','color:'+default_color_calendar_3.font_color_box+'!important');
			jQuery('.ph_calendar_design_box #ph_change_month_text_color span').attr('style','color:'+default_color_calendar_3.font_color_box+'!important');

			jQuery('.ph_calendar_design_box .booking-info-wraper').attr('style','background-color:'+default_color_calendar_3.booking_wrapper_color_box+'!important');
			jQuery('.ph_calendar_design_box .booking-info-wraper p').attr('style','color:'+default_color_calendar_3.booking_wrapper_text_color_box+'!important');

			jQuery('.ph_calendar_design_box .single_add_to_cart_button').attr('style','background-color:'+default_color_calendar_3.book_now_bg_color_box+'!important; color:'+default_color_calendar_3.book_now_text_color_box+'!important');
			// jQuery('.ph_calendar_design_box .single_add_to_cart_button').attr('style','color:'+default_color_calendar_3.book_now_text_color_box+'!important');

			jQuery('.ph_calendar_design_box .selected-date').attr('style','background-color:'+default_color_calendar_3.selected_date_color_box+'!important');

			jQuery('.ph_calendar_design_box .booking-full').attr('style','background-color:'+default_color_calendar_3.booking_full_color_box+'!important');

			jQuery('.ph_calendar_design_box .date-picker-wraper').attr('style','color:'+default_color_calendar_3.font_color_box+'!important');
		});



		jQuery('.primary_bg_color').wheelColorPicker();
		jQuery('.hover_bg_color').wheelColorPicker();
		jQuery('.price_box_bg_color').wheelColorPicker();
		jQuery('.text_color').wheelColorPicker();
		jQuery('.booked_block_color').wheelColorPicker();

		jQuery('.book_now_bg_color_design_2').wheelColorPicker();
		jQuery('.book_now_text_color_design_2').wheelColorPicker();
		
		jQuery('.price_box_text_color').wheelColorPicker();

		jQuery('.ph_calendar_design').css('cssText','margin:3%; width:70%; margin-top: 0;background-color:<?php echo $primary_bg_color; ?>  !important;');
		jQuery('.ph_calendar_design .booking-info-wraper').css('cssText','background-color:<?php echo $price_box_bg_color; ?>  !important;');

		jQuery('.ph_calendar_design .ph-calendar-date').css('cssText','color:<?php echo $text_color; ?>  !important;');
		jQuery('.ph_calendar_design .ph-calendar-weekdays li').css('cssText','color:<?php echo $text_color; ?>  !important;');
		jQuery('.ph_calendar_design .ph-calendar-month ul li').css('cssText','color:<?php echo $text_color; ?>  !important;');
		jQuery('.ph_calendar_design .selected-date').css('cssText','background-color:<?php echo $hover_bg_color; ?> !important; color:<?php echo $text_color;?>  !important;');
		jQuery('.ph_calendar_design .booking-full').css('cssText','background-color:<?php echo $booked_block_color; ?>  !important;');
		

		jQuery('.price_box_text_color').on('colorchange', function(e)
		{
			jQuery('.booking_info_text_design_2').css('cssText','color:#'+jQuery(this).val()+" !important;");
			jQuery('.price_box_text_color').val('#' + jQuery(this).val());
		});

		jQuery('.book_now_bg_color_design_2').on('colorchange', function(e){
			color = jQuery('.book-now-text-color-design-2').val();
			
			if (color != '') 
			{
				jQuery('.ph_calendar_design .single_add_to_cart_button').css('cssText','background-color:#'+jQuery(this).val()+' !important; color:#'+color+'!important;');
			}
			else
				jQuery('.ph_calendar_design .single_add_to_cart_button').css('cssText','background-color:#'+jQuery(this).val()+" !important;");
			jQuery('.book_now_bg_color_design_2').val('#' + jQuery(this).val());
		});

		jQuery('.book_now_text_color_design_2').on('colorchange', function(e)
		{
			bg = jQuery('.book_now_bg_color_design_2').val();
			
			if (bg != '') 
			{
				jQuery('.ph_calendar_design .single_add_to_cart_button').css('cssText','color:#'+jQuery(this).val()+' !important; background-color:'+bg+'!important');
			}
			else
				jQuery('.ph_calendar_design .single_add_to_cart_button').css('cssText','color:#'+jQuery(this).val()+" !important;");
			jQuery('.book-now-text-color-design-2').val('#' + jQuery(this).val());
		});



		jQuery('.primary_bg_color').on('colorchange', function(e){
			jQuery('.ph_calendar_design').css('cssText','margin:3%; width:70%; margin-top: 0;background-color:#'+jQuery(this).val()+" !important;");
			jQuery('.primary_bg_color').val('#' + jQuery(this).val());
		});
		jQuery('.hover_bg_color').on('colorchange', function(e){
			jQuery('.ph_calendar_design .selected-date').css('cssText','background-color:#'+jQuery(this).val()+' !important; min-height:0;');
			jQuery('.hover_bg_color').val('#' + jQuery(this).val());
		});
		jQuery('.price_box_bg_color').on('colorchange', function(e){
			jQuery('.ph_calendar_design .booking-info-wraper').css('cssText','background-color:#'+jQuery(this).val()+" !important;");
			jQuery('.price_box_bg_color').val('#' + jQuery(this).val());
		});
		jQuery('.text_color').on('colorchange', function(e){
			jQuery('.ph_calendar_design .ph-calendar-date').css('cssText','color:#'+jQuery(this).val()+" !important; min-height:0;");
			// jQuery('.ph_calendar_design .ph-calendar-date').css({"color":"#"+jQuery(this).val()+""});
			jQuery('.ph_calendar_design .ph-calendar-weekdays li').css('cssText','color:#'+jQuery(this).val()+" !important;");
			jQuery('.ph_calendar_design .ph-calendar-month ul li').css('cssText','color:#'+jQuery(this).val()+" !important;");
			jQuery('.text_color').val('#' + jQuery(this).val());
		});
		jQuery('.booked_block_color').on('colorchange', function(e){
			jQuery('.ph_calendar_design .booking-full').css('cssText','background-color:#'+jQuery(this).val()+" !important; min-height:0;");
			jQuery('.booked_block_color').val('#' + jQuery(this).val());
		});


		//design 3
		jQuery('.ph_calendar_design_box .font_color_box').wheelColorPicker();	
		jQuery('.ph_calendar_design_box .booking_full_color_box').wheelColorPicker();	
		jQuery('.ph_calendar_design_box .selected_date_color_box').wheelColorPicker();	
		jQuery('.ph_calendar_design_box .booking_wrapper_color_box').wheelColorPicker();	
		jQuery('.ph_calendar_design_box .booking_wrapper_text_color_box').wheelColorPicker();	
		jQuery('.ph_calendar_design_box .book_now_bg_color_box').wheelColorPicker();	
		jQuery('.ph_calendar_design_box .book_now_text_color_box').wheelColorPicker();		
		jQuery('.ph_calendar_design_box .start_date_box').wheelColorPicker();		

	


		jQuery('.start_date_box').on('colorchange', function(e){
			jQuery('.ph_calendar_design_box .element_from_focused').attr('style','background-color:#'+jQuery(this).val()+'!important');
			jQuery('.start_date_box_form').val('#'+jQuery(this).val());
			jQuery('.start_date_box').val('#'+jQuery(this).val());
		});

		jQuery('.font_color_box').on('colorchange', function(e){
			jQuery('.ph_calendar_design_box .inner-element input').attr('style','color:#'+jQuery(this).val()+'!important');
			jQuery('.ph_calendar_design_box .ph-calendar-date').attr('style','color:#'+jQuery(this).val()+'!important');
			jQuery('.ph_calendar_design_box .ph-calendar-weekdays li').attr('style','color:#'+jQuery(this).val()+'!important');
			jQuery('.ph_calendar_design_box #ph_change_month_text_color span').attr('style','color:#'+jQuery(this).val()+'!important');
			jQuery('.font_color_box_form').val('#'+jQuery(this).val());
			jQuery('.font_color_box').val('#'+jQuery(this).val());
		});
		jQuery('.booking_full_color_box').on('colorchange', function(e){
			jQuery('.ph_calendar_design_box .booking-full').attr('style','background-color:#'+jQuery(this).val()+'!important');
			jQuery('.booking_full_color_box_form').val('#'+jQuery(this).val());
			jQuery('.booking_full_color_box').val('#'+jQuery(this).val());
		});

		jQuery('.selected_date_color_box').on('colorchange', function(e){
			jQuery('.ph_calendar_design_box .selected-date').attr('style','background-color:#'+jQuery(this).val()+'!important');
			jQuery('.selected_date_color_box_form').val('#'+jQuery(this).val());
			jQuery('.selected_date_color_box').val('#'+jQuery(this).val());
		});
		jQuery('.booking_wrapper_color_box').on('colorchange', function(e){
			jQuery('.ph_calendar_design_box .booking-info-wraper').attr('style','background-color:#'+jQuery(this).val()+'!important');
			jQuery('.booking_wrapper_color_box_form').val('#'+jQuery(this).val());
			jQuery('.booking_wrapper_color_box').val('#'+jQuery(this).val());
		});
		jQuery('.booking_wrapper_text_color_box').on('colorchange', function(e){
			jQuery('.ph_calendar_design_box .booking-info-wraper p').attr('style','color:#'+jQuery(this).val()+'!important');
			jQuery('.booking_wrapper_text_color_box_form').val('#'+jQuery(this).val());
			jQuery('.booking_wrapper_text_color_box').val('#'+jQuery(this).val());
		});
		jQuery('.book_now_bg_color_box').on('colorchange', function(e){
			jQuery('.ph_calendar_design_box .single_add_to_cart_button').attr('style','background-color:#'+jQuery(this).val()+'!important');
			jQuery('.book_now_bg_color_box_form').val('#'+jQuery(this).val());
			jQuery('.book_now_bg_color_box').val('#'+jQuery(this).val());
		});
		jQuery('.book_now_text_color_box').on('colorchange', function(e){
			jQuery('.ph_calendar_design_box .single_add_to_cart_button').attr('style','color:#'+jQuery(this).val()+'!important');
			jQuery('.book_now_text_color_box_form').val('#'+jQuery(this).val());
			jQuery('.book_now_text_color_box').val('#'+jQuery(this).val());
		});

	});

	function rgb2hex(rgb){
	rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
	return (rgb && rgb.length === 4) ? "#" +
		("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
		("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
		("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
	}
	jQuery('.select_calendar_design').click(function(){
		jQuery('.calendar_design').val(jQuery(this).val());
	});

	</script>
	
