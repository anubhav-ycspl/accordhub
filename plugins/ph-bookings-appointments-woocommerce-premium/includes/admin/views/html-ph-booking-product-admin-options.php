<style>
.input-item{
	margin: 0px 5px!important;
}


</style>
<div id='booking_options' class='panel woocommerce_options_panel'>
	<?php
	$interval_period 		= get_post_meta( $post->ID, '_phive_book_interval_period', 1);
	$interval_type 			= get_post_meta( $post->ID, '_phive_book_interval_type', 1);
	$interval 				= get_post_meta( $post->ID, '_phive_book_interval', 1);
	$cancel_interval		= get_post_meta( $post->ID, '_phive_cancel_interval', 1);
	$price 					= get_post_meta( $post->ID, '_phive_book_price', 1);
	$allowd_per_slot 		= get_post_meta( $post->ID, '_phive_book_allowed_per_slot', 1);
	$opening_time			= get_post_meta( $post->ID, '_phive_book_working_hour_start', 1);
	$closing_tme			= get_post_meta( $post->ID, '_phive_book_working_hour_end', 1);
	$min_allowd_booking		= get_post_meta( $post->ID, '_phive_book_min_allowed_booking', 1);
	$auto_select_min_booking		= get_post_meta( $post->ID, '_phive_auto_select_min_booking', 1);
	$max_allowd_booking		= get_post_meta( $post->ID, '_phive_book_max_allowed_booking', 1);
	$cancel_interval_period = get_post_meta( $post->ID, '_phive_cancel_interval_period', 1);
	$cancellable			= get_post_meta( $post->ID, '_phive_book_allow_cancel', 1);
	/*$ph_checkin				= get_post_meta( $post->ID, '_phive_book_checkin', 1);
	$ph_checkout			= get_post_meta( $post->ID, '_phive_book_checkout', 1);*/
	$additional_notes_label	= get_post_meta( $post->ID, '_phive_additional_notes_label', 1);
	$additional_notes		= get_post_meta( $post->ID, '_phive_book_additional_notes', 1);
	$is_add_notes_mandatory = get_post_meta( $post->ID, '_phive_add_notes_is_mandatory', 1);
	$enable_buffer			= get_post_meta( $post->ID, '_phive_enable_buffer', 1);
	$buffer_before 			= get_post_meta( $post->ID, '_phive_buffer_before', 1 );
	$buffer_after			= get_post_meta( $post->ID, '_phive_buffer_after', 1 );
	$buffer_period			= get_post_meta( $post->ID, '_phive_buffer_period', 1 );
	$across_the_day			= get_post_meta( $post->ID, '_phive_enable_across_the_day', 1);
	$end_time_display			= get_post_meta( $post->ID, '_phive_enable_end_time_display', 1);
	$across_the_day_cost_calculation	= get_post_meta( $post->ID, '_phive_across_the_day_cost_calculation', 1);

	$ph_remaining_booking_text			= get_post_meta( $post->ID, '_phive_remainng_bokkings_text', 1);
	$ph_remaining_booking_text= !empty($ph_remaining_booking_text)?$ph_remaining_booking_text:'left';

	$ph_booking_fully_booked_indicator			= get_post_meta( $post->ID, '_ph_booking_fully_booked_indicator_text', 1);
	$ph_booking_fully_booked_indicator			= !empty($ph_booking_fully_booked_indicator) ? $ph_booking_fully_booked_indicator : __('Sold Out', 'bookings-and-appointments-for-woocommerce');

	//Set default values
	if( empty($buffer_before) ) 		$buffer_before = 0;
	if( empty($buffer_after) ) 			$buffer_after = 0;
	if( empty($allowd_per_slot) ) 		$allowd_per_slot = 1;
	if( empty($interval) ) 				$interval = 1;
	if( empty($opening_time) ) 			$opening_time = '';
	if( empty($closing_tme) ) 			$closing_tme = '';
	if( empty($additional_notes_label))	$additional_notes_label='Additional Notes';

	$ph_calendar_color 			= get_option('ph_booking_settings_calendar_color') ;
		$ph_calendar_design			= (isset($ph_calendar_color['ph_calendar_design']) && !empty($ph_calendar_color['ph_calendar_design']))?$ph_calendar_color['ph_calendar_design']:1; // default legacy design will display
		
	?>
	<input type="hidden" id="calendar_design" name="calendar_design" class="calendar_design" value="<?php echo $ph_calendar_design;?>">
	<p class="form-field">
		<label for="_phive_book_price" ><?php _e('Booking Period','bookings-and-appointments-for-woocommerce') ?></label>
		<select id="_phive_book_interval_type" name="_phive_book_interval_type" class="short">
			<option value="fixed"<?php if($interval_type=='fixed')echo'selected="selected"'; ?> ><?php _e('Fixed Blocks of','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="customer_choosen" <?php if($interval_type=='customer_choosen')echo'selected="selected"'; ?> ><?php _e('Calendar Range with Blocks of','bookings-and-appointments-for-woocommerce') ?></option>
			<?php
				do_action('ph_bookings_interval_type_dropdown',$interval_type);	
			?>
		</select>
		
		<input type="number" class="short " style="width:70px;margin-left:5px;" onKeyPress="if(this.value.length==3) return false;" name="_phive_book_interval" id="_phive_book_interval" min="1" value="<?php echo $interval;?>" placeholder="1">
		<select id="_phive_book_interval_period" name="_phive_book_interval_period" class="select short input-item" style="width:85px;margin-left: 10px;" >
			<option value="minute" <?php if($interval_period=='minute')echo'selected="selected"'; ?> ><?php _e('Minutes(s)','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="hour" <?php if($interval_period=='hour')echo'selected="selected"'; ?>><?php _e('Hour(s)','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="day" <?php if( $interval_period=='day' || empty($interval_period) )echo'selected="selected"'; ?>><?php _e('Day(s)','bookings-and-appointments-for-woocommerce') ?></option>
			<!-- <option value="week" <?php //if($interval_period=='week')echo'selected="selected"'; ?>><?php //_e('Week(s)','bookings-and-appointments-for-woocommerce') ?></option> -->
			<option value="month" <?php if($interval_period=='month')echo'selected="selected"'; ?>><?php _e('Month(s)','bookings-and-appointments-for-woocommerce') ?></option>
		</select><?php
			_e( "<br><span style='float:left;'><i>Fixed Blocks : Customers can book one block at a time.<br>Enable Range : Customers can book multiple blocks by choosing a start and an end.</i></span>", 'bookings-and-appointments-for-woocommerce');
		?>
	</p>
	<p class="form-field" >
		<label for="_phive_book_allowed_per_slot"><?php _e('Max Bookings per Block','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="number" class="short " style="width:70px" name="_phive_book_allowed_per_slot" id="_phive_book_allowed_per_slot" min="1" value="<?php echo $allowd_per_slot;?>" placeholder="1">
		<?php
			_e( "<span style='padding-left:2%;'><i>Allow multiple bookings for the same block until the specified number is reached.</i></span>", 'bookings-and-appointments-for-woocommerce' );
		?>
	</p>
	<?php
		woocommerce_wp_checkbox( array(
			'id' 			=> '_phive_display_bookings_capacity',
			'label' 		=> __( 'Remaining Bookings', 'bookings-and-appointments-for-woocommerce' ),
			'description' 	=> __( "<span style='padding-left:2%;'><i>Enabling this will show the number of places left for bookings per block.</i><span>", 'bookings-and-appointments-for-woocommerce' ),
		) );

		woocommerce_wp_text_input( array(
			'id'			=> '_phive_remainng_bokkings_text',
			'label'			=> __( 'Remaining Bookings Text', 'bookings-and-appointments-for-woocommerce' ),
			'desc_tip'		=> 'true',
			'value'			=> $ph_remaining_booking_text,
			'type' 			=> 'text',
			'style'			=> "width: 120px;",
			'custom_attributes' => array(
						            'maxlength' => 6
						        ),
			'description'	=> __('This text is displayed below each date or time block For Eg: 10 Left','bookings-and-appointments-for-woocommerce' )

		) );

		// Fully Booked Indicator
		woocommerce_wp_checkbox( array(
			'id' 			=> '_ph_display_bookings_fully_booked_indicator',
			'label' 		=> __('Fully Booked Indicator', 'bookings-and-appointments-for-woocommerce'),
			'description' 	=> __("<span style='padding-left:2%;'><i>Enabling this will show the Fully Booked Indicator for bookings slots which is fully booked.</i><span>", 'bookings-and-appointments-for-woocommerce'),
		) );

		// Fully Booked Indicator Text
		woocommerce_wp_text_input( array(
			'id'				=> '_ph_booking_fully_booked_indicator_text',
			'label'				=> __('Fully Booked Message', 'bookings-and-appointments-for-woocommerce'),
			'desc_tip'			=> 'true',
			'value'				=> $ph_booking_fully_booked_indicator,
			'type' 				=> 'text',
			'style'				=> "width: 120px;",
			'custom_attributes' => array(
						            	'maxlength' => 10
						        	),
			'description'		=> __('This text is displayed below fully booked date or time slot.','bookings-and-appointments-for-woocommerce')

		) );
	?>

	<p class="form-field" >
		<label for="_phive_book_min_allowed_booking" ><?php _e('Minimum Duration','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="number" min="1" class="short for-type-customer-choosen" style="width:70px" name="_phive_book_min_allowed_booking" id="_phive_book_min_allowed_booking" value="<?php echo $min_allowd_booking;?>" placeholder="1"><?php
		_e( "<span style='padding-left:2%;'><i>Set the minimum booking period the customer can select.</i></span>", 'bookings-and-appointments-for-woocommerce' );
		?>
	</p>
	<p class="form-field _phive_auto_select_min_booking_field" >
		<label for="_phive_auto_select_min_booking" ><?php _e('','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="checkbox" class=" for-type-customer-choosen" style="" name="_phive_auto_select_min_booking" id="_phive_auto_select_min_booking" value="yes" <?php echo (empty($auto_select_min_booking) || $auto_select_min_booking=='yes' )?"checked":'';?>  placeholder="1"><?php
		_e( "<span style='padding-left:2%;'><i>Auto-Select Minimum Booking Slots.</i></span>", 'bookings-and-appointments-for-woocommerce' );
		?>
		<span style=""><?php
			echo wc_help_tip( __("Enabling this option will select the Minimum Booking Slots automatically as soon as the customer selects the first slot.", 'bookings-and-appointments-for-woocommerce') );?>
		</span>
	</p>
	<p class="form-field" >
		<label for="_phive_book_max_allowed_booking" ><?php _e('Maximum Duration','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="number" class="short  for-type-customer-choosen" style="width:70px" name="_phive_book_max_allowed_booking" id="_phive_book_max_allowed_booking" value="<?php echo $max_allowd_booking;?>" placeholder=""><?php
		_e( "<span style='padding-left:2%;'><i>Set the maximum period the customer can input. Leave blank for unlimited.</i></span>", 'bookings-and-appointments-for-woocommerce' );?>
	</p>


	<?php
	/*woocommerce_wp_text_input( array(
		'id'			=> '_phive_book_checkin',
		'label'			=> __( 'Check-in Time', 'bookings-and-appointments-for-woocommerce' ),
		'desc_tip'		=> 'true',
		'value'			=> $ph_checkin,
		'description'	=> __( 'Checkin Time', 'bookings-and-appointments-for-woocommerce' ),
		'type' 			=> 'time',
		'style'			=> "width: 120px;",

	) );
	woocommerce_wp_text_input( array(
		'id'			=> '_phive_book_checkout',
		'label'			=> __( 'Check-out Time', 'bookings-and-appointments-for-woocommerce' ),
		'desc_tip'		=> 'true',
		'value'			=> $ph_checkout,
		'description'	=> __( 'Check out time', 'bookings-and-appointments-for-woocommerce' ),
		'type' 			=> 'time',
		'style'			=> "width: 120px;",

	) );*/

	
	?>
	<p >
		<span >
			<span for="_phive_book_allow_cancel" style="width:150px;float:left;margin-left: 0.5%;"><?php _e('Allow Cancellation','bookings-and-appointments-for-woocommerce') ?></span>
			<input type="checkbox" class="short" style="width:10px;float:left;margin: 4px 0 !important;"  name="_phive_book_allow_cancel"  id="_phive_book_allow_cancel" <?php echo ($cancellable=='yes')?'checked':'';?>>
		</span>
		<span   id="_phive_cancellation_period" >
			<span for="_phive_book_allow_cancel" style="float:left;margin:0 4px;"><?php _e('Until','bookings-and-appointments-for-woocommerce') ?></span>
			<input type="number" min="1" class="short" style="width:50px;" name="_phive_cancel_interval" id="_phive_cancel_interval" value="<?php echo $cancel_interval;?>" placeholder="30">
			<select id="_phive_cancel_interval_period" name="_phive_cancel_interval_period" class="select short input-item" style="width:85px;margin-left: 10px;" >
				<option value="minute" <?php if($cancel_interval_period=='minute')echo'selected="selected"'; ?> ><?php _e('Minutes(s)','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="hour" <?php if($cancel_interval_period=='hour')echo'selected="selected"'; ?>><?php _e('Hour(s)','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="day" <?php if($cancel_interval_period=='day')echo'selected="selected"'; ?>><?php _e('Day(s)','bookings-and-appointments-for-woocommerce') ?></option>
				<!-- <option value="month" <?php //if($cancel_interval_period=='month')echo'selected="selected"'; ?>><?php //_e('Month(s)','bookings-and-appointments-for-woocommerce') ?></option> --> 
			</select>
			<span><?php _e(' before the booking starts','bookings-and-appointments-for-woocommerce')?></span>
		</span>
	</p>
	<?php

	woocommerce_wp_checkbox( array(
		'id' 			=> '_phive_book_required_confirmation',
		'label' 		=> __( 'Requires Confirmation', 'bookings-and-appointments-for-woocommerce' ),
		'description' 	=> __( "<span style='padding-left:2%; font-size:12px;'><i>Check this box if the booking requires admin approval/confirmation.</i></span>", 'bookings-and-appointments-for-woocommerce' ),
		// 'desc_tip'		=> 'true',
	) );
	woocommerce_wp_checkbox( array(
		'id' 			=> '_phive_book_charge_per_night',
		'label' 		=> __( 'Bookings Per Night', 'bookings-and-appointments-for-woocommerce' ),
		'description' 	=> __( "<span style='padding-left:2%;'><i>Enable this option to offer bookings on a per night basis. For Eg : Accommodations. The customer can choose a check in and check out date.</i></span>", 'bookings-and-appointments-for-woocommerce' ),
		// 'desc_tip'		=> 'true',
	) );

	woocommerce_wp_checkbox( array(
		'id' 			=> '_phive_book_charge_per_night_option',
		'label' 		=> __( '', 'bookings-and-appointments-for-woocommerce' ),
		'description' 	=> __( "<span style='padding-left:2%;'><i>Hide checkout date on admin calendar and google calendar for per night bookings.</i></span>", 'bookings-and-appointments-for-woocommerce' ),
		// 'desc_tip'		=> 'true',
	) );
	
	?>
	<p >
		<span >
			<span for="_phive_book_additional_notes" style="width:150px;float:left;margin-left: 0.5%;"><?php _e('Enable Booking Notes','bookings-and-appointments-for-woocommerce') ?></span>
			<input type="checkbox" class="short" style="width:10px;float:left;margin: 4px 0 !important;"  name="_phive_book_additional_notes"  id="_phive_book_additional_notes" <?php echo ($additional_notes=='yes')?'checked':'';?>>
			<span style="float:left"><?php
				echo wc_help_tip( __("Enable this option if you need a text area to appear along with the calendar for clients to enter additional information.", 'bookings-and-appointments-for-woocommerce') );?>
			</span>

		</span>
	</p>
	<p class="form-field _phive_additional_notes_fields">
		<label><?php _e('Booking Notes Label','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="text" class="short" style="width:40%;"  name="_phive_additional_notes_label" id="_phive_additional_notes_label" value="<?php echo $additional_notes_label;?>" placeholder="Additional Notes">
		
		<?php
		echo wc_help_tip( __("Modify the title that appears with the Booking notes text area.") );?>
	</p>
	<p class="form-field _phive_additional_notes_fields">
		<label ><?php _e('Make "Notes" Mandatory','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="checkbox" class="short" style="width:10px;float:left;margin: 4px 0 !important;"  name="_phive_add_notes_is_mandatory"  id="_phive_add_notes_is_mandatory" <?php echo ($is_add_notes_mandatory=='yes')?'checked':'';?>>
	</p>
	<div id="_phive_enable_buffer_field">
		<?php
		woocommerce_wp_checkbox( array(
			'id' 			=> '_phive_enable_buffer',
			'label' 		=> __( 'Buffer Time', 'bookings-and-appointments-for-woocommerce' ),
		) );
		?>
	

		<p class="form-field" id="ph_buffer_before">
			<label ><?php _e('Before Booking','bookings-and-appointments-for-woocommerce') ?></label>
			<input type="number" class="short" style="width:70px;margin-right: 5px"  name="_phive_buffer_before" id="_phive_buffer_before" value="<?php echo $buffer_before;?>" min = 0>
			<span class="_phive_buffer_period"></span><span><?php _e("(s)", "bookings-and-appointments-for-woocommerce") ?></span>
		</p>

		<p class="form-field" id="ph_buffer_after">
			<label ><?php _e('After Booking','bookings-and-appointments-for-woocommerce') ?></label>
			<input type="number" class="short" style="width:70px;margin-right: 5px"  name="_phive_buffer_after" id="_phive_buffer_after" value="<?php echo $buffer_after;?>" min = 0><span class="_phive_buffer_period"></span><span><?php _e("(s)", "bookings-and-appointments-for-woocommerce") ?></span>
			<input type="hidden" name="_phive_buffer_period" id="_phive_buffer_period" value="<?php echo $buffer_period ?>">
		</p>
	</div>

	<?php

	echo "<h2 class='ph_bookings_admin_time_start_end_time_settings'><hr/>".__( "Daily Booking Times:", "bookings-and-appointments-for-woocommerce")."</h2>";
	woocommerce_wp_text_input( array(
		'id'			=> '_phive_book_working_hour_start',
		'label'			=> __( 'First booking starts at :', 'bookings-and-appointments-for-woocommerce' ),
		'value'			=> $opening_time,
		'type' 			=> 'time',
		'style'			=> "width: 120px;float:left",
	) );
	woocommerce_wp_text_input( array(
		'id'			=> '_phive_book_working_hour_end',
		'label'			=> __( 'Last booking starts at :', 'bookings-and-appointments-for-woocommerce' ),
		'value'			=> $closing_tme,
		'type' 			=> 'time',
		'style'			=> "width: 120px;float:left",
	) );

	echo "<span class='ph_bookings_admin_time_start_end_time_settings' style='padding-left:1.5%'><i>".__( "Note : To override these timings for specific days, you can set the Availability Rules under <b>Booking Availability</b>.", "bookings-and-appointments-for-woocommerce")."</i></span>";
?>
	<h2><hr/></h2>
	<p class="form-field _phive_enable_across_the_day" >
		<label for="_phive_enable_across_the_day"  style="" ><?php _e('Book Across Days','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="checkbox" class="for-type-customer-choosen" name="_phive_enable_across_the_day" id="_phive_enable_across_the_day" value="yes" placeholder="" <?php echo ($across_the_day=='yes' || empty($across_the_day))?"checked":'';?>><?php
		_e( "<span style='padding-left:2%;'><i>Enabling this option will allow customers to book timeslots across days. For Eg : 9.00 Am , 1st May to 6.00 Pm 2nd May.</i></span>", 'bookings-and-appointments-for-woocommerce' );
		?>
	</p>
	<!-- Option to skip or include invisible slot between across days for cost calculation -->
	<p class="form-field _phive_across_the_day_cost_calculation" >
		<label for="_phive_across_the_day_cost_calculation"  style="" ><?php _e('Across Days Cost Calculation','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="checkbox" class="for-type-customer-choosen" name="_phive_across_the_day_cost_calculation" id="_phive_across_the_day_cost_calculation" value="exclude" <?php  echo $across_the_day_cost_calculation	==	'exclude' ? "checked" : '' ; ?>>
		<span style='padding-left:2%;'><i><?php
			_e( "Enabling the option to exclude cost calculation for unavailable slots between consecutive days.", 'bookings-and-appointments-for-woocommerce' );
		?></i></span>
	</p>
	<p class="form-field _phive_enable_end_time_display" >
		<label for="_phive_enable_end_time_display"  style="" ><?php _e('Display Slot End Time','bookings-and-appointments-for-woocommerce') ?></label>
		<input type="checkbox" class="for-type-customer-choosen" name="_phive_enable_end_time_display" id="_phive_enable_end_time_display" value="yes" placeholder="" <?php echo ($end_time_display=='yes')?"checked":'';?>><?php
		_e( "<span style='padding-left:2%;'><i>Enabling this option will display both the Booking Slot Start Time as well as End Time on the calendar.</i></span>", 'bookings-and-appointments-for-woocommerce' );
		?>
	</p>
</div>