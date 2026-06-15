<div id='booking_availability' class='panel woocommerce_options_panel'>
	
	<div class="">
		<?php
		$first_availability 				= get_post_meta( $post->ID, '_phive_first_availability', 1 );
		$last_availability 					= get_post_meta( $post->ID, '_phive_last_availability', 1 );
		$fixed_availability_from 			= get_post_meta( $post->ID, '_phive_fixed_availability_from', 1 );
		$fixed_availability_to 				= get_post_meta( $post->ID, '_phive_fixed_availability_to', 1 );
		$rules 								= get_post_meta( $post->ID, '_phive_booking_availability_rules', 1 );
		$unavailable						= get_post_meta( $post->ID, '_phive_un_availability', 1);
		$restrict_start_day					= get_post_meta( $post->ID, '_phive_restrict_start_day', 1);
		$booking_start_days					= get_post_meta( $post->ID, '_phive_booking_start_days', 1 );
		$first_availability_interval_period	= get_post_meta( $post->ID, '_phive_first_availability_interval_period', 1 );
		$last_availability_interval_period	= get_post_meta( $post->ID, '_phive_last_availability_interval_period', 1 );
		$first_booking_availability_type 	= get_post_meta( $post->ID, '_phive_first_booking_availability_type', 1 );
		if( empty($booking_start_days) ) {
			$booking_start_days = array();
		}

		?>
		<div class="ph-availability-section first-availablity-wraper">
			
			<div class="relative-contents" style="overflow: hidden;">
				
				<div class="fixed-contents" style="overflow: hidden;">
					<br/>
					<h2 style="padding-left:0.5%;"><?php _e( "Set a fixed booking window", "bookings-and-appointments-for-woocommerce"); ?> </h2>
					<div class="label" style="overflow: hidden;">
						<span style="float: left;margin: 5px; width: 33%;"><?php _e('Bookings open no sooner than','bookings-and-appointments-for-woocommerce')?> </span>
						<input type="date" class="short" style="width: 155px" name="_phive_fixed_availability_from" id="_phive_fixed_availability_from" value="<?php echo $fixed_availability_from;?>" placeholder="">
					</div>

					<br/>
					
					<div class="label" style="overflow: hidden;">
						<span style="float: left;margin: 5px; width: 33%;"><?php _e('Bookings open no later than','bookings-and-appointments-for-woocommerce')?> </span>
						<input type="date" class="short" style="width: 155px" name="_phive_fixed_availability_to" id="_phive_fixed_availability_to" value="<?php echo $fixed_availability_to;?>" placeholder="">
					</div>
				</div>
				<hr/>

				<br/>

				<div class="relative-from-wraper" style="overflow: hidden;">
					<h2 style="padding-left:0.5%;"><?php _e( "Set a relative booking window", "bookings-and-appointments-for-woocommerce"); ?> </h2>
					<div style="display:flex;">
						<span class="label" style="float: left;margin: 5px; width: 33%;"><?php _e('Bookings are open from','bookings-and-appointments-for-woocommerce')?> </span>
						<select id="_phive_first_booking_availability_type" name="_phive_first_booking_availability_type" class="select short input-item" style="width:155px;margin-left: 0px !important;" >
							<option value="today" <?php if($first_booking_availability_type=='today')echo'selected="selected"'; ?> ><?php _e('Today','bookings-and-appointments-for-woocommerce') ?></option>
							<option value="first_available_date" <?php if($first_booking_availability_type=='first_available_date')echo'selected="selected"'; ?>><?php _e('First Available Date','bookings-and-appointments-for-woocommerce') ?></option>
						</select>
					</div>
					
					<br>
					<!-- <h2 style="padding-left:0.5%;"><?php _e( "Set a booking window relative to today", "bookings-and-appointments-for-woocommerce"); ?> </h2> -->
					<div>
						<span class="label" style="float: left;margin: 5px; width: 33%;"><?php _e('Bookings are open for the next','bookings-and-appointments-for-woocommerce')?> </span>
						<input type="number" min="0" class="first_availability_input" style="width: 55px;" name="_phive_last_availability" id="_phive_last_availability" value="<?php echo $last_availability; ?>" placeholder="" autocomplete="off">
						<select id="_phive_last_availability_interval_period" name="_phive_last_availability_interval_period" class="select short input-item" style="width:95px;margin-left: 10px;" >
							<option value="minutes" <?php if($last_availability_interval_period=='minutes')echo'selected="selected"'; ?> ><?php _e('Minutes(s)','bookings-and-appointments-for-woocommerce') ?></option>
							<option value="hours" <?php if($last_availability_interval_period=='hours')echo'selected="selected"'; ?>><?php _e('Hour(s)','bookings-and-appointments-for-woocommerce') ?></option>
							<option value="days" <?php if($last_availability_interval_period=='days')echo'selected="selected"'; ?>><?php _e('Day(s)','bookings-and-appointments-for-woocommerce') ?></option>
						</select>
						
					</div>
					
					<?php
					echo wc_help_tip( __("Customers will be able to book till 30 days from today if this option is set to 30 days.", 'bookings-and-appointments-for-woocommerce') );?>
				</div>
				<hr/>
				
				<div class="relative-from-wraper" style="overflow: hidden;">
					<br/>
					<h2 style="padding-left:0.5%;"><?php _e( "Avoid last minute bookings", "bookings-and-appointments-for-woocommerce"); ?> </h2>
					<span class="label" style="float: left;margin: 5px; width: 33%;"><?php _e('Allow customers to book until','bookings-and-appointments-for-woocommerce')?> </span>
					<input type="number" min="0" class="first_availability_input" style="width: 55px;" name="_phive_first_availability" id="_phive_first_availability" value="<?php echo $first_availability; ?>" placeholder="">
					<span>  
					<select id="_phive_first_availability_interval_period" name="_phive_first_availability_interval_period" class="select short input-item" style="width:95px;margin-left: 10px;" >
						<option value="minutes" <?php if($first_availability_interval_period=='minutes')echo'selected="selected"'; ?> ><?php _e('Minutes(s)','bookings-and-appointments-for-woocommerce') ?></option>
						<option value="hours" <?php if($first_availability_interval_period=='hours')echo'selected="selected"'; ?>><?php _e('Hour(s)','bookings-and-appointments-for-woocommerce') ?></option>
						<option value="days" <?php if($first_availability_interval_period=='days')echo'selected="selected"'; ?>><?php _e('Day(s)','bookings-and-appointments-for-woocommerce') ?></option>
					</select>
					<?php _e(' before the booking starts','bookings-and-appointments-for-woocommerce')?></span>
					<?php
					echo wc_help_tip( __("Bookings will be closed before the specified time. E.g. - Set this option to 10 mins if you don't want any booking in the last 10 mins.", 'bookings-and-appointments-for-woocommerce') );?>
				</div>
				<?php
				// 129889-custom booking interval addon support
				do_action('ph_bookings_book_for_past_times',$post->ID);	
				?>
			</div>

		</div>
		<div class="ph-availability-section">
			<hr>
			<p>
				<span for="_phive_restrict_start_day" style="width:33%;float:left;margin-right: 5px;"><?php _e('Restrict bookings to start only on certain days of the week','bookings-and-appointments-for-woocommerce') ?></span>
					<input type="checkbox" class="short" style="width:10px;float:left;margin: 4px 0 !important;"  name="_phive_restrict_start_day"  id="_phive_restrict_start_day" <?php echo ($restrict_start_day=='yes')?'checked':'';?>>
					<span style="float:left"><?php
						echo wc_help_tip( __("Allow start booking only with selected days", 'bookings-and-appointments-for-woocommerce') );?>
				</span>
			</p>
			<div class="ph_start_day_related">
				<table style="width: 100%;border: 1px solid #dddddd;">
					<tr>
						<td> <span class="restrict-start-day-label"><?php _e( "Monday", "bookings-and-appointments-for-woocommerce"); ?></span> <input type="checkbox" class="checkbox" <?php echo !in_array(1, $booking_start_days)? '' : 'checked' ?> name="_phive_booking_start_days[]" value="1"/></td>
						<td> <span class="restrict-start-day-label"><?php _e( "Tuesday", "bookings-and-appointments-for-woocommerce"); ?></span> <input type="checkbox" class="checkbox" <?php echo !in_array(2, $booking_start_days)? '' : 'checked' ?> name="_phive_booking_start_days[]" value="2"/></td>
						<td> <span class="restrict-start-day-label"><?php _e( "Wednesday", "bookings-and-appointments-for-woocommerce"); ?></span> <input type="checkbox" class="checkbox" <?php echo !in_array(3, $booking_start_days)? '' : 'checked' ?> name="_phive_booking_start_days[]" value="3"/></td>
						<td> <span class="restrict-start-day-label"><?php _e( "Thursday", "bookings-and-appointments-for-woocommerce"); ?></span> <input type="checkbox" class="checkbox" <?php echo !in_array(4, $booking_start_days)? '' : 'checked' ?> name="_phive_booking_start_days[]" value="4"/></td>
						<td> <span class="restrict-start-day-label"><?php _e( "Friday", "bookings-and-appointments-for-woocommerce"); ?></span> <input type="checkbox" class="checkbox" <?php echo !in_array(5, $booking_start_days)? '' : 'checked' ?> name="_phive_booking_start_days[]" value="5"/></td>
						<td> <span class="restrict-start-day-label"><?php _e( "Saturday", "bookings-and-appointments-for-woocommerce"); ?></span> <input type="checkbox" class="checkbox" <?php echo !in_array(6, $booking_start_days)? '' : 'checked' ?> name="_phive_booking_start_days[]" value="6"/></td>
						<td> <span class="restrict-start-day-label"><?php _e( "Sunday", "bookings-and-appointments-for-woocommerce"); ?></span> <input type="checkbox" class="checkbox" <?php echo !in_array(7, $booking_start_days)? '' : 'checked' ?> name="_phive_booking_start_days[]" value="7"/></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="ph-availability-section">
			<hr/>
			<h2 style="padding-left:0.5%;"><?php _e( "Availability Rules :", "bookings-and-appointments-for-woocommerce"); ?> </h2>
			<span style="padding-left:0.5%;">
				<i>
					<?php
						_e( "There are two ways you can set the availability rules", "bookings-and-appointments-for-woocommerce" );
						echo "<br>";
						_e( "1. All dates are available by default. Create rules to set the time period when you are not available to take bookings.", "bookings-and-appointments-for-woocommerce" );
						echo "<br>";
						_e( "2. Mark all the dates/blocks as unavailable and then set the time period when you are available for bookings.", "bookings-and-appointments-for-woocommerce" );
					?>
				</i>
			</span>
			<p>
				<span for="_phive_un_availability" style="width:33%;float:left;margin-right: 5px;"><?php _e('Make all dates/blocks unavailable','bookings-and-appointments-for-woocommerce') ?></span>
					<input type="checkbox" class="short" style="width:10px;float:left;margin: 4px 0 !important;"  name="_phive_un_availability"  id="_phive_un_availability" <?php echo ($unavailable=='yes')?'checked':'';?>>
					<span style="float:left"><?php
						echo wc_help_tip( __("Enabling this option will disable all dates in the calendar. Using this option with availability rules, you can enable desired dates for bookings.", 'bookings-and-appointments-for-woocommerce') );?>
				</span>
			</p>
		</div>

	</div>

	<div class="ph-availability-section" id="availability_wraper">
		<?php
		if( empty($rules) ){
			$rules = array( 0=>array('availability_type'=>'','from_date'=>'','from_week_day'=>'','from_month'=>'','from_time'=>'','to_week_day'=>'','to_month'=>'','to_date'=>'','to_time'=>'','is_bokable'=>'') );
		}
		?>

		<table class="ph_availability_table wc_input_table sortable" cellspacing="0" name="availability_rules_table">
			<thead>
				<tr>
					<th class="sort">&nbsp;</th>
					<th><?php _e('Range Type','bookings-and-appointments-for-woocommerce')?></th>
					<th><?php _e('From','bookings-and-appointments-for-woocommerce')?></th>
					<th><?php _e('To','bookings-and-appointments-for-woocommerce')?></th>
					<th><?php _e('Bookable','bookings-and-appointments-for-woocommerce')?></th>
					<th></th>
				</tr>
			</thead>
			<tbody class="rules ui-sortable">
				<?php
				foreach ($rules as $key => $rule) {
					if( !empty($rule['availability_type']) ){
						include("html-ph-booking-product-admin-availability-matrix-row.php");
					}
				}?>
				</tr>
			</tbody>
			<tfoot>

				<tr>
					<th colspan="10">
						<i style="float: left;font-weight: 500;">
							<?php
							_e( "Note:  In case of conflicting rules, the top rule gets priority.", "bookings-and-appointments-for-woocommerce" );
							?>
						</i>
					</th>
				</tr>
				<tr>
					<th colspan="10"><a href="#" class="add_rule button">+ <?php _e('Add','bookings-and-appointments-for-woocommerce')?></a></th>
				</tr>
			</tfoot>
		</table>
	</div>
	
</div>
<script type="text/javascript">
jQuery(function($) {

	jQuery('#availability_wraper').on( 'click', 'a.add_rule', function(){
		
		$(".availability_date_picker").datepicker("destroy");
		jQuery(`<?php $rule=""; include("html-ph-booking-product-admin-availability-matrix-row.php")?>`).appendTo('#availability_wraper table tbody');
		jQuery( ".availability_date_picker" ).datepicker({
			showOtherMonths: true,
			selectOtherMonths: true,
			dateFormat: 'yy-mm-dd',
			minDate: new Date(),
		});

		// For day and month interval no need to show timerange option in product availability
		if( (jQuery("#_phive_book_interval_period").val() == 'minute') || (jQuery("#_phive_book_interval_period").val() == 'hour') ){

			jQuery('.availability_time_picker_field').show();
			jQuery(".ph_booking_availability_type optgroup").show();
		}
		else{
			jQuery('.availability_time_picker_field').hide();
			jQuery(".ph_booking_availability_type optgroup").hide();
		}

		// For Month interval no need to show range of days option in product availability
		if(jQuery('#_phive_book_interval_period').val() == 'month'){
			jQuery(".ph_booking_availability_type option[value=days").hide();
		}else{
			jQuery(".ph_booking_availability_type option[value=days").show();
		}
		return false;
	});
});
</script>
