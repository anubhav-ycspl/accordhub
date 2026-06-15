<?php
$display_settings = get_option( 'ph_bookings_display_settigns' );

$month_picker_enable         = isset( $display_settings['month_picker_enable'] ) ? $display_settings['month_picker_enable'] : 'no';
$start_of_week               = isset( $display_settings['start_of_week'] ) ? $display_settings['start_of_week'] : 1;
$time_zone_conversion_enable = isset( $display_settings['time_zone_conversion_enable'] ) ? $display_settings['time_zone_conversion_enable'] : 'no';
$booking_end_time_display    = ( isset( $display_settings['booking_end_time_display'] ) && $display_settings['booking_end_time_display'] == 'no' ) ? 'no' : 'yes';
// #91928
$booking_end_time_display_cart_order_emails = ( isset( $display_settings['booking_end_time_display_cart_order_emails'] ) && $display_settings['booking_end_time_display_cart_order_emails'] == 'no' ) ? 'no' : 'yes';
$ph_swap_month_year_in_calendar             = ( isset( $display_settings['ph_swap_month_year_in_calendar'] ) && $display_settings['ph_swap_month_year_in_calendar'] == 'yes' ) ? 'yes' : 'no';
// 96421
$calculate_availability_using_availability_table = ( isset( $display_settings['calculate_availability_using_availability_table'] ) && $display_settings['calculate_availability_using_availability_table'] == 'yes' ) ? 'yes' : 'no';

$ph_booking_details_on_calendar = isset( $display_settings['ph_booking_details_on_calendar'] ) ? $display_settings['ph_booking_details_on_calendar'] : array( 'order_id', 'product_name' );
$ph_enable_search_widget 		= (isset($display_settings['ph_enable_search_widget']) && 'yes' == $display_settings['ph_enable_search_widget']) ? 'yes' : 'no';
$ph_search_product_view			= isset($display_settings['ph_search_product_view']) && !empty($display_settings['ph_search_product_view']) ? $display_settings['ph_search_product_view'] : 'calendar';

// text customisation
$text_customisation = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();

$max_participant = isset( $text_customisation['max_participant'] ) && ! empty( $text_customisation['max_participant'] ) ? $text_customisation['max_participant'] : 'Total participant (%total) exceeds maximum allowed participant (%max)';
$min_participant = isset( $text_customisation['min_participant'] ) && ! empty( $text_customisation['min_participant'] ) ? $text_customisation['min_participant'] : 'Minimum number of participants required for a booking is (%min)';
$max_block       = isset( $text_customisation['max_block'] ) && ! empty( $text_customisation['max_block'] ) ? $text_customisation['max_block'] : 'Max no of blocks available to book is %max_block';

$min_block_required = isset( $text_customisation['min_block_required'] ) && ! empty( $text_customisation['min_block_required'] ) ? $text_customisation['min_block_required'] : 'Please Select minimum %d blocks.';

$pick_a_date      = isset( $text_customisation['pick_a_date'] ) && ! empty( $text_customisation['pick_a_date'] ) ? $text_customisation['pick_a_date'] : 'Please Pick a Date';
$book_now_button  = isset( $text_customisation['book_now_button'] ) && ! empty( $text_customisation['book_now_button'] ) ? $text_customisation['book_now_button'] : 'Book Now';
$pick_an_end_date = isset( $text_customisation['pick_an_end_date'] ) && ! empty( $text_customisation['pick_an_end_date'] ) ? $text_customisation['pick_an_end_date'] : 'Please pick an end date';

// 137142
$pick_a_time = isset( $text_customisation['pick_a_time'] ) && ! empty( $text_customisation['pick_a_time'] ) ? $text_customisation['pick_a_time'] : 'Please pick a Time';

// $pick_a_date=isset($text_customisation['pick_a_date']) && !empty($text_customisation['pick_a_date'])?$text_customisation['pick_a_date']:'Please pick the end time';
// $pick_a_date=isset($text_customisation['pick_a_date']) && !empty($text_customisation['pick_a_date'])?$text_customisation['pick_a_date']:'Please pick an end month';
$booking_info_booking_cost = isset( $text_customisation['booking_info_booking_cost'] ) && ! empty( $text_customisation['booking_info_booking_cost'] ) ? $text_customisation['booking_info_booking_cost'] : 'Booking cost';
$booking_info_booking      = isset( $text_customisation['booking_info_booking'] ) && ! empty( $text_customisation['booking_info_booking'] ) ? $text_customisation['booking_info_booking'] : 'Booking';

$start_date_text = isset( $text_customisation['start_date_text'] ) && ! empty( $text_customisation['start_date_text'] ) ? $text_customisation['start_date_text'] : 'Start Date';
$end_date_text   = isset( $text_customisation['end_date_text'] ) && ! empty( $text_customisation['end_date_text'] ) ? $text_customisation['end_date_text'] : 'End Date';
$start_time_text = isset( $text_customisation['start_time_text'] ) && ! empty( $text_customisation['start_time_text'] ) ? $text_customisation['start_time_text'] : 'Start Time';
$end_time_text   = isset( $text_customisation['end_time_text'] ) && ! empty( $text_customisation['end_time_text'] ) ? $text_customisation['end_time_text'] : 'End Time';

$check_in_text  = isset( $text_customisation['check_in_text'] ) && ! empty( $text_customisation['check_in_text'] ) ? $text_customisation['check_in_text'] : 'Check-in';
$check_out_text = isset( $text_customisation['check_out_text'] ) && ! empty( $text_customisation['check_out_text'] ) ? $text_customisation['check_out_text'] : 'Check-out';
// 175970
$booked_from_text = isset( $text_customisation['booked_from_text'] ) && ! empty( $text_customisation['booked_from_text'] ) ? $text_customisation['booked_from_text'] : 'Booked From';
$booked_to_text   = isset( $text_customisation['booked_to_text'] ) && ! empty( $text_customisation['booked_to_text'] ) ? $text_customisation['booked_to_text'] : 'Booked To';
$booked_text      = isset( $text_customisation['booked_text'] ) && ! empty( $text_customisation['booked_text'] ) ? $text_customisation['booked_text'] : 'Booked';
if ( ! empty( $_POST['ph_bookings_save_display_settigns'] ) ) {
	$month_picker_enable         = isset( $_POST['month_picker_enable'] ) ? $_POST['month_picker_enable'] : 'no';
	$start_of_week               = isset( $_POST['start_of_week'] ) ? $_POST['start_of_week'] : 1;
	$time_zone_conversion_enable = isset( $_POST['time_zone_conversion_enable'] ) ? $_POST['time_zone_conversion_enable'] : 'no';
	$booking_end_time_display    = isset( $_POST['booking_end_time_display'] ) ? $_POST['booking_end_time_display'] : 'no';
	// #91928
	$booking_end_time_display_cart_order_emails = isset( $_POST['booking_end_time_display_cart_order_emails'] ) ? $_POST['booking_end_time_display_cart_order_emails'] : 'no';
	$ph_swap_month_year_in_calendar             = isset( $_POST['ph_swap_month_year_in_calendar'] ) ? $_POST['ph_swap_month_year_in_calendar'] : 'no';
	$ph_enable_search_widget					= isset($_POST['ph_enable_search_widget']) ? $_POST['ph_enable_search_widget'] : 'no';
	$ph_search_product_view						= isset($_POST['ph_search_product_view']) ? $_POST['ph_search_product_view'] : 'calendar';

	// 96421
	$calculate_availability_using_availability_table = isset( $_POST['calculate_availability_using_availability_table'] ) ? $_POST['calculate_availability_using_availability_table'] : 'no';
	$max_participant                                 = isset( $_POST['max_participant'] ) ? $_POST['max_participant'] : 'Total participant (%total) exceeds maximum allowed participant (%max)';
	$min_participant                                 = isset( $_POST['min_participant'] ) ? $_POST['min_participant'] : 'Minimum number of participants required for a booking is (%min)';
	$max_block                                       = isset( $_POST['max_block'] ) ? $_POST['max_block'] : 'Max no of blocks available to book is %max_block';

	$min_block_required = isset( $_POST['min_block_required'] ) ? $_POST['min_block_required'] : 'Please Select minimum %d blocks.';

	$pick_a_date      = isset( $_POST['pick_a_date'] ) ? $_POST['pick_a_date'] : 'Please Pick a Date';
	$book_now_button  = isset( $_POST['book_now_button'] ) ? $_POST['book_now_button'] : 'Book Now';
	$pick_an_end_date = isset( $_POST['pick_an_end_date'] ) ? $_POST['pick_an_end_date'] : 'Please pick an end date';
	$pick_a_time      = isset( $_POST['pick_a_time'] ) ? $_POST['pick_a_time'] : 'Please pick a Time';

	$booking_info_booking_cost = isset( $_POST['booking_info_booking_cost'] ) ? $_POST['booking_info_booking_cost'] : 'Booking cost';
	$booking_info_booking      = isset( $_POST['booking_info_booking'] ) ? $_POST['booking_info_booking'] : 'Booking';

	$start_date_text = isset( $_POST['start_date_text'] ) ? $_POST['start_date_text'] : 'Start Date';
	$end_date_text   = isset( $_POST['end_date_text'] ) ? $_POST['end_date_text'] : 'End Date';
	$start_time_text = isset( $_POST['start_time_text'] ) ? $_POST['start_time_text'] : 'Start Time';
	$end_time_text   = isset( $_POST['end_time_text'] ) ? $_POST['end_time_text'] : 'End Time';

	$check_in_text    = isset( $_POST['check_in_text'] ) ? $_POST['check_in_text'] : 'Check-in';
	$check_out_text   = isset( $_POST['check_out_text'] ) ? $_POST['check_out_text'] : 'Check-out';
	$booked_from_text = isset( $_POST['booked_from_text'] ) ? $_POST['booked_from_text'] : 'Booked From';
	$booked_to_text   = isset( $_POST['booked_to_text'] ) ? $_POST['booked_to_text'] : 'Booked To';
	$booked_text      = isset( $_POST['booked_text'] ) ? $_POST['booked_text'] : 'Booked';

	$ph_booking_details_on_calendar = isset( $_POST['ph_booking_details_on_calendar'] ) ? $_POST['ph_booking_details_on_calendar'] : array();

	$text_customisation = array(
		'max_participant'           => $max_participant,
		'min_participant'           => $min_participant,
		'max_block'                 => $max_block,
		'pick_a_date'               => $pick_a_date,
		'book_now_button'           => $book_now_button,
		'pick_an_end_date'          => $pick_an_end_date,
		'booking_info_booking_cost' => $booking_info_booking_cost,
		'booking_info_booking'      => $booking_info_booking,
		'start_date_text'           => $start_date_text,
		'end_date_text'             => $end_date_text,
		'start_time_text'           => $start_time_text,
		'end_time_text'             => $end_time_text,
		'min_block_required'        => $min_block_required,
		'check_in_text'             => $check_in_text,
		'check_out_text'            => $check_out_text,
		'pick_a_time'               => $pick_a_time,
		'booked_from_text'          => $booked_from_text,
		'booked_to_text'            => $booked_to_text,
		'booked_text'               => $booked_text,
	);

	$Settings = array(
		'month_picker_enable'                             => $month_picker_enable,
		'start_of_week'                                   => $start_of_week,
		'time_zone_conversion_enable'                     => $time_zone_conversion_enable,
		'text_customisation'                              => $text_customisation,
		'booking_end_time_display'                        => $booking_end_time_display,
		'booking_end_time_display_cart_order_emails'      => $booking_end_time_display_cart_order_emails,
		'ph_swap_month_year_in_calendar'                  => $ph_swap_month_year_in_calendar,
		'calculate_availability_using_availability_table' => $calculate_availability_using_availability_table,
		'ph_booking_details_on_calendar'                  => $ph_booking_details_on_calendar,
		'ph_enable_search_widget'						  => $ph_enable_search_widget,
		'ph_search_product_view'						  => $ph_search_product_view,
	);

	update_option( 'ph_bookings_display_settigns', $Settings );

	foreach ( $text_customisation as $key => $value ) {
		$name = 'text_customisation_' . $key;
		ph_wpml_register_string_for_translation( $name, $value );
	}
}

$search_widget_notice = '';
if (Ph_Bookings_Plugin_Active_Check::plugin_active_check('booking-search-widget-addon/booking-search-widget-addon.php')) {
	$search_widget_notice = __('Please deactivate the <strong>Availability Search Widget Add-on</strong> to access the features in beta version.', 'bookings-and-appointments-for-woocommerce');
}

?>

<form method="post" action="" id="">
	<h2><?php _e( 'Customize Labels', 'bookings-and-appointments-for-woocommerce' ); ?></h2>
	<p>
	<?php
	_e( 'The text given below is displayed by the plugin by default. You can change the text messages as per your preference.', 'bookings-and-appointments-for-woocommerce' );
	echo '<br>';
	_e( 'Please retain the text in parenthesis() and used with % sign as is.', 'bookings-and-appointments-for-woocommerce' );
	?>
	</p>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="book_now_button"><?php _e( 'Book Now Button', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="book_now_button" id="book_now_button" value="<?php echo $book_now_button; ?>" placeholder="Book Now">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="pick_a_date"><?php _e( 'Please Pick a Date', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="pick_a_date" id="pick_a_date" value="<?php echo $pick_a_date; ?>" placeholder="Please Pick a Date"  style="    width: 60%;">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="pick_an_end_date"><?php _e( 'Please Pick an End Date', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="pick_an_end_date" id="pick_an_end_date" value="<?php echo $pick_an_end_date; ?>" placeholder="Please pick an end date"  style="    width: 60%;">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="pick_a_time"><?php _e( 'Please Pick a Time', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="pick_a_time" id="pick_a_time" value="<?php echo $pick_a_time; ?>" placeholder="Please pick a Time"  style="width: 60%;">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="max_block"><?php _e( 'Max no of blocks available', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="max_block" id="max_block" value="<?php echo $max_block; ?>" placeholder="Max no of blocks available to book is %max_block" style="    width: 60%;">
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="max_block"><?php _e( 'Minimum no of blocks required', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="min_block_required" id="min_block_required" value="<?php echo $min_block_required; ?>" placeholder="Please Select minimum %d blocks." style="    width: 60%;">
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="min_participant"><?php _e( 'Minimum participant', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="min_participant" id="min_participant" value="<?php echo $min_participant; ?>"  placeholder="Minimum number of participants required for a booking is (%min)" style="    width: 60%;">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="max_participant"><?php _e( 'Total participant', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="max_participant" id="max_participant" value="<?php echo $max_participant; ?>"  placeholder="Total participant (%total) exceeds maximum allowed participant (%max)" style="    width: 60%;">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="booking_info_booking"><?php _e( 'Booking', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="booking_info_booking" id="booking_info_booking" value="<?php echo $booking_info_booking; ?>"  placeholder="Booking" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="booking_info_booking_cost"><?php _e( 'Booking cost', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="booking_info_booking_cost" id="booking_info_booking_cost" value="<?php echo $booking_info_booking_cost; ?>"  placeholder="Booking cost" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="start_date_text"><?php _e( 'Start Date', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="start_date_text" id="start_date_text" value="<?php echo $start_date_text; ?>"  placeholder="Start Date" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="end_date_text"><?php _e( 'End Date', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="end_date_text" id="end_date_text" value="<?php echo $end_date_text; ?>"  placeholder="End Date" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="start_time_text"><?php _e( 'Start Time', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="start_time_text" id="start_time_text" value="<?php echo $start_time_text; ?>"  placeholder="Start Time" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="end_time_text"><?php _e( 'End Time', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="end_time_text" id="end_time_text" value="<?php echo $end_time_text; ?>"  placeholder="End Time" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="check_in_text"><?php _e( 'Check-in', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="check_in_text" id="check_in_text" value="<?php echo $check_in_text; ?>"  placeholder="Check-in" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="check_out_text"><?php _e( 'Check-out', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="check_out_text" id="check_out_text" value="<?php echo $check_out_text; ?>"  placeholder="Check-out" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<!-- 175970 -->
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="booked_from_text"><?php _e( 'Booked From', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="booked_from_text" id="booked_from_text" value="<?php echo $booked_from_text; ?>" placeholder="Booked From" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="booked_to_text"><?php _e( 'Booked To', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="booked_to_text" id="booked_to_text" value="<?php echo $booked_to_text; ?>" placeholder="Booked To" style="width: 60%;" maxlength="40">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="booked_text"><?php _e( 'Booked', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="text" name="booked_text" id="booked_text" value="<?php echo $booked_text; ?>" placeholder="Booked" style="width: 60%;" maxlength="40">
			</td>
		</tr>
	</table> 
	<hr>
	<h2><?php _e( 'Calendar Display Settings', 'bookings-and-appointments-for-woocommerce' ); ?></h2>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="start_of_week"><?php _e( 'Week Starts On', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<select name="start_of_week" id="start_of_week">
					<option value="0" <?php echo $start_of_week == 0 ? 'selected' : ''; ?>><?php _e( 'Sunday', 'bookings-and-appointments-for-woocommerce' ); ?></option>
					<option value="1" <?php echo ( $start_of_week == 1 || $start_of_week == '' ) ? 'selected' : ''; ?>><?php _e( 'Monday', 'bookings-and-appointments-for-woocommerce' ); ?></option>
					<!-- <option value="2" <?php echo $start_of_week == 2 ? 'selected' : ''; ?>>Tuesday</option>
					<option value="3" <?php echo $start_of_week == 3 ? 'selected' : ''; ?>>Wednesday</option>
					<option value="4" <?php echo $start_of_week == 4 ? 'selected' : ''; ?>>Thursday</option>
					<option value="5" <?php echo $start_of_week == 5 ? 'selected' : ''; ?>>Friday</option>
					<option value="6" <?php echo $start_of_week == 6 ? 'selected' : ''; ?>>Saturday</option> -->
				</select>

				<i>
				<?php
					echo __( 'This will be the first weekday in the calendar.', 'bookings-and-appointments-for-woocommerce' );
				?>
				</i>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="month_picker_enable"><?php _e( 'Month Picker', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="month_picker_enable" id="month_picker_enable"  value="yes" <?php echo $month_picker_enable == 'yes' ? 'checked' : ''; ?>>
				<!-- <span><?php _e( 'Enable', 'bookings-and-appointments-for-woocommerce' ); ?></span> -->				
				<i>
				<?php
					echo __( 'Allow customers to choose a month from a drop down to navigate to that month directly.', 'bookings-and-appointments-for-woocommerce' );
				?>
				</i>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="time_zone_conversion_enable"><?php _e( 'Time Zone Conversion', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="time_zone_conversion_enable" id="time_zone_conversion_enable"  value="yes" <?php echo $time_zone_conversion_enable == 'yes' ? 'checked' : ''; ?>>
				<i>
				<?php
					echo __( 'Enabling this option will allow clients to see the calendar in their own time zone.', 'bookings-and-appointments-for-woocommerce' );
				?>
				</i>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="booking_end_time_display"><?php _e( 'Booking Summary text includes End Date and Time', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="booking_end_time_display" id="booking_end_time_display"  value="yes" <?php echo $booking_end_time_display == 'yes' ? 'checked' : ''; ?>>
				<i><?php _e( 'By default the summary of Booking details is displayed as “Booking: December 18, 2019 9:00 AM to December 19, 2019 1:00 PM”.<br>&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Disabling this option will omit the Booking end details and display as “Booking: December 18, 2019 9:00 AM”.', 'bookings-and-appointments-for-woocommerce' ); ?></i>
			</td>
		</tr>
		<!-- #91928-->
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="booking_end_time_display_cart_order_emails"><?php _e( 'Include End Date and Time in Cart, Order Details and Emails', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="booking_end_time_display_cart_order_emails" id="booking_end_time_display_cart_order_emails"  value="yes" <?php echo $booking_end_time_display_cart_order_emails == 'yes' ? 'checked' : ''; ?>>
				<i><?php _e( 'By default details in Cart, Order Details and Emails will contain “Booked To”.<br>&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Disabling this option will omit the Booking end details and “Booked To” will not be displayed.', 'bookings-and-appointments-for-woocommerce' ); ?></i>
			</td>
		</tr>

		<?php

			$migration = get_option( 'ph_migrate_availability_data_v3_0_0' );

		if ( isset( $migration['completed'] ) && $migration['completed'] == 'yes' ) {
			?>
					<input type="hidden" name="calculate_availability_using_availability_table" id="calculate_availability_using_availability_table" value="yes">
			<?php
		}
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="ph_booking_details_on_calendar"><?php _e( 'Booking Personalization for Admin Calendar', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp">
				<select name="ph_booking_details_on_calendar[]" multiple="multiple" class="ph_booking_details_on_calendar" style="width: 300px;">
					<?php

						$ph_details_on_calendar = array(
							'order_id'      => __( 'Order ID', 'bookings-and-appointments-for-woocommerce' ),
							'product_name'  => __( 'Product Name', 'bookings-and-appointments-for-woocommerce' ),
							'customer_name' => __( 'Customer Name', 'bookings-and-appointments-for-woocommerce' ),
							'participants'  => __( 'No of Participants', 'bookings-and-appointments-for-woocommerce' ),
							'assets'        => __( 'Assets', 'bookings-and-appointments-for-woocommerce' ),
						);

						foreach ( $ph_details_on_calendar as $key => $value ) {

							$is_selected = in_array( $key, $ph_booking_details_on_calendar ) ? 'selected' : '';

							?>
								<option value= 
								<?php
								echo '"' . $key . '"';
								echo $is_selected;
								?>
								><?php echo $value; ?></option>
							<?php
						}
						?>
				</select>
				<br><i><?php _e( "You'll have the option to personalize your admin calendar, choosing to display essential details like order ID, product name, customer name, and the number of participants.", 'bookings-and-appointments-for-woocommerce' ); ?></i>
				<br><span style="color:red" id="ph_booking_details_on_calendar_error"><?php _e( 'Kindly choose at least one option and a maximum of three to save the settings.', 'bookings-and-appointments-for-woocommerce' ); ?></span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="ph_swap_month_year_in_calendar"><?php _e( 'Swap Month & Year Format in Calendar Title', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="ph_swap_month_year_in_calendar" id="ph_swap_month_year_in_calendar"  value="yes" <?php echo $ph_swap_month_year_in_calendar == 'yes' ? 'checked' : ''; ?>>
				<i><?php _e( 'By default Calendar Title have Month followed by Year. Enable this option to display Year first.', 'bookings-and-appointments-for-woocommerce' ); ?></i>
			</td>
		</tr>

	</table>
	<hr>
	<h2><?php _e( 'Experimental Feature: Search Availability Widget', 'bookings-and-appointments-for-woocommerce' ); ?></h2>
	<p><?php _e( 'This feature is currently in development and may not function as expected in all scenarios. Please report any issues you encounter. We are actively working to improve the functionality and reliability of this feature. Please note that this feature is currently not tested with our add-ons.', 'bookings-and-appointments-for-woocommerce' ); ?></p>
	<table class="form-table">
		<tr>
			<th>
				<label for="ph_enable_search_widget"><?php _e('Display Search Availability Widget', 'bookings-and-appointments-for-woocommerce') ?></label>
			</th>
			<td>
				<input
					type="checkbox"
					name="ph_enable_search_widget"
					id="ph_enable_search_widget"
					value="yes" <?php echo (('yes' == $ph_enable_search_widget && empty($search_widget_notice)) ? 'checked' : '' )?>
					<?php echo (!empty($search_widget_notice) ? 'disabled' : ''); ?>
				>
				<i><?php _e( 'Locate the Search Availability Widget in your theme\'s widget section and add it to a desired widget area to enable the feature.', 'bookings-and-appointments-for-woocommerce' ); ?></i>
				<br>
				<br>
				<?php
					if (!empty($search_widget_notice)) {

						$settings['ph_enable_search_widget'] = 'no';
					
						update_option( 'ph_bookings_display_settigns', $settings );

						?>
						<span class="notice notice-warning" style="padding: 5px;"><i><?php echo $search_widget_notice ?></i></span>
						<?php
					}
				?>
			</td>
		</tr>
		<?php 
			if (empty($search_widget_notice)) {
				?>
				<tr>
					<th>
						<label for="ph_search_product_view"><?php _e('Booking Availability View', 'bookings-and-appointments-for-woocommerce') ?></label>
					</th>
					<td>
						<select name="ph_search_product_view" id="ph_search_product_view">
							<option value="calendar" <?php echo ('calendar' === $ph_search_product_view ? 'selected' : '') ?>><?php _e( 'Flexible Booking', 'bookings-and-appointments-for-woocommerce' ); ?></option>
							<option value="summary" <?php echo ('summary' === $ph_search_product_view ? 'selected' : '') ?>><?php _e( 'Direct Booking', 'bookings-and-appointments-for-woocommerce' ); ?></option>
						</select>
						<br/>
						<br/>
						<i><?php _e( '<strong>Flexible Booking:</strong> Display booking details along with a calendar for editing and modifying.', 'bookings-and-appointments-for-woocommerce' ); ?></i>
						<br/>
						<i><?php _e( '<strong>Direct Booking:</strong> Display booking details directly without a calendar for modifications.', 'bookings-and-appointments-for-woocommerce' ); ?></i>
					</td>
				</tr>
		<?php
			}
		?>
	</table>

	<p class="submit">
		<button name="ph_bookings_save_display_settigns" class="button-primary woocommerce-save-button" type="submit" value="Save Settings"><?php _e( 'Save Settings', 'bookings-and-appointments-for-woocommerce' ); ?></button>
	</p>
</form>
