<tr class="rule ui-sortable-handle">
	<td class="sort"></td>
	<td>
		<select name="ph_booking_availability_type[]" class="ph_booking_availability_type">
			<option value="custom" <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='custom' ) ? 'selected="selected"' : '';?> ><?php _e('Custom date range','bookings-and-appointments-for-woocommerce')?></option>
			<option value="months"<?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='months') ? 'selected="selected"' : '';?>><?php _e('Range of months','bookings-and-appointments-for-woocommerce')?></option>
			<option value="days" <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='days') ? 'selected="selected"' : '';?>><?php _e('Range of days','bookings-and-appointments-for-woocommerce')?></option>
			<optgroup label="<?php _e('Time Ranges','bookings-and-appointments-for-woocommerce');?>">
				<option value="date-range-and-time" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='date-range-and-time') ? 'selected="selected"' : '';?>><?php _e('Date range and time','bookings-and-appointments-for-woocommerce')?></option>
				<option value="time-all" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='time-all') ? 'selected="selected"' : '';?>><?php _e('Time Range (all week)','bookings-and-appointments-for-woocommerce')?></option>
				<option value="time-mon" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='time-mon') ? 'selected="selected"' : '';?>><?php _e('Monday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="time-tue" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='time-tue') ? 'selected="selected"' : '';?>><?php _e('Tuesday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="time-wed" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='time-wed') ? 'selected="selected"' : '';?>><?php _e('Wednesday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="time-thu" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='time-thu') ? 'selected="selected"' : '';?>><?php _e('Thursday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="time-fri" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='time-fri') ? 'selected="selected"' : '';?>><?php _e('Friday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="time-sat" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='time-sat') ? 'selected="selected"' : '';?>><?php _e('Saturday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="time-sun" <?php echo (isset($rule['availability_type']) && $rule['availability_type']=='time-sun') ? 'selected="selected"' : '';?>><?php _e('Sunday','bookings-and-appointments-for-woocommerce')?></option>
			</optgroup>
		</select>
	</td>
	<td>


		<div class="availability_by_date" style="display: <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='custom' ) || empty($rule['availability_type']) ? 'block;' : 'none;';?>">
			<?php
			if(isset($rule['from_date'])){
				$from_date_rule = explode(" ",$rule['from_date']);
				$availability_from_date = $from_date_rule[0];
				$availability_from_time = !empty($from_date_rule[1])?$from_date_rule[1]:'';
			}
			 ?>
			<input type="text" class="availability_date_picker ph_booking_from_date"  value="<?php echo isset($rule['from_date']) ? $availability_from_date : '';?>" style="width: 100px;border: 1px solid #dddddd;background: white;" >
			<span class="availability_time_picker_field">
				<input type="time" class="ph_booking_from_timepicker" value="<?php echo isset($rule['from_date']) ? $availability_from_time : '';?>">
			</span>
			<input type="hidden" class="ph_booking_from_datetime" name="ph_booking_from_date[]" value="<?php echo isset($rule['from_date']) ? $rule['from_date'] : '';?>">
		</div>


		<div class="availability_by_date_range_and_time" style="display: <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='date-range-and-time' ) ? 'block;' : 'none;';?>">
			<?php
			if(isset($rule['from_date_for_date_range_and_time'])){
				$from_date_rule = explode(" ",$rule['from_date_for_date_range_and_time']);
				$availability_from_date = $from_date_rule[0];
				$availability_from_time = !empty($from_date_rule[1])?$from_date_rule[1]:'';
			}
			 ?>
			<input type="text" class="availability_date_picker ph_booking_from_date_for_date_range_and_time"  value="<?php echo isset($rule['from_date_for_date_range_and_time']) ? $availability_from_date : '';?>" style="width: 100px;border: 1px solid #dddddd;background: white;" >
			<span class="availability_time_picker_field">
				<input type="time" class="ph_booking_from_timepicker_date_range_and_time" value="<?php echo isset($rule['from_date_for_date_range_and_time']) ? $availability_from_time : '';?>">
			</span>
			<input type="hidden" class="ph_booking_from_datetime_for_date_range_and_time" name="ph_booking_from_date_for_date_range_and_time[]" value="<?php echo isset($rule['from_date_for_date_range_and_time']) ? $rule['from_date_for_date_range_and_time'] : '';?>">
		</div>



		<div class="select availability_by_month" style="display: <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='months' ) ? 'block;' : 'none;';?>">
			<select name="ph_booking_from_month[]">
				<option value="1" <?php echo (isset($rule['from_month']) && $rule['from_month']=='1') ? 'selected="selected"' : '';?> ><?php _e('January','bookings-and-appointments-for-woocommerce')?></option>
				<option value="2" <?php echo (isset($rule['from_month']) && $rule['from_month']=='2') ? 'selected="selected"' : '';?> ><?php _e('Febuary','bookings-and-appointments-for-woocommerce')?></option>
				<option value="3" <?php echo (isset($rule['from_month']) && $rule['from_month']=='3') ? 'selected="selected"' : '';?> ><?php _e('March','bookings-and-appointments-for-woocommerce')?></option>
				<option value="4" <?php echo (isset($rule['from_month']) && $rule['from_month']=='4') ? 'selected="selected"' : '';?> ><?php _e('April','bookings-and-appointments-for-woocommerce')?></option>
				<option value="5" <?php echo (isset($rule['from_month']) && $rule['from_month']=='5') ? 'selected="selected"' : '';?> ><?php _e('May','bookings-and-appointments-for-woocommerce')?></option>
				<option value="6" <?php echo (isset($rule['from_month']) && $rule['from_month']=='6') ? 'selected="selected"' : '';?> ><?php _e('June','bookings-and-appointments-for-woocommerce')?></option>
				<option value="7" <?php echo (isset($rule['from_month']) && $rule['from_month']=='7') ? 'selected="selected"' : '';?> ><?php _e('July','bookings-and-appointments-for-woocommerce')?></option>
				<option value="8" <?php echo (isset($rule['from_month']) && $rule['from_month']=='8') ? 'selected="selected"' : '';?> ><?php _e('August','bookings-and-appointments-for-woocommerce')?></option>
				<option value="9" <?php echo (isset($rule['from_month']) && $rule['from_month']=='9') ? 'selected="selected"' : '';?> ><?php _e('September','bookings-and-appointments-for-woocommerce')?></option>
				<option value="10" <?php echo (isset($rule['from_month']) && $rule['from_month']=='10') ? 'selected="selected"' : '';?> ><?php _e('October','bookings-and-appointments-for-woocommerce')?></option>
				<option value="11" <?php echo (isset($rule['from_month']) && $rule['from_month']=='11') ? 'selected="selected"' : '';?> ><?php _e('November','bookings-and-appointments-for-woocommerce')?></option>
				<option value="12" <?php echo (isset($rule['from_month']) && $rule['from_month']=='12') ? 'selected="selected"' : '';?> ><?php _e('December','bookings-and-appointments-for-woocommerce')?></option>
			</select>
		</div>
		<div class="select availability_by_week_days" style="display: <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='days' ) ? 'block;' : 'none;';?>">
			<select name="ph_booking_from_week_day[]">
				<option value="1" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='1') ? 'selected="selected"' : '';?> ><?php _e('Monday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="2" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='2') ? 'selected="selected"' : '';?> ><?php _e('Tuesday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="3" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='3') ? 'selected="selected"' : '';?> ><?php _e('Wednesday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="4" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='4') ? 'selected="selected"' : '';?> ><?php _e('Thursday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="5" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='5') ? 'selected="selected"' : '';?> ><?php _e('Friday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="6" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='6') ? 'selected="selected"' : '';?> ><?php _e('Saturday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="7" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='7') ? 'selected="selected"' : '';?> ><?php _e('Sunday','bookings-and-appointments-for-woocommerce')?></option>
			</select>
		</div>
		<div class="availability_by_time" style="display: <?php echo ( isset($rule['availability_type'] ) && strpos( $rule['availability_type'], "time" ) !== false && $rule['availability_type'] != 'date-range-and-time') ? 'block;' : 'none;';?>">
			<input type="time" class="time-picker" name="ph_booking_from_time[]" value="<?php echo isset($rule['from_time']) ? $rule['from_time'] : '' ?>" placeholder="HH:MM">
		</div>
	</td>
	<td>


		<div class="availability_by_date" style="display: <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='custom' ) || empty($rule['availability_type']) ? 'block;' : 'none;';?>">
			<?php
			if(isset($rule['to_date'])){
				$to_date_rule = explode(" ",$rule['to_date']);
				$availability_to_date = $to_date_rule[0];
				$availability_to_time = !empty($to_date_rule[1])?$to_date_rule[1]:'';
			}
			 ?>
			<input type="text"  class="availability_date_picker ph_booking_to_date"  value="<?php echo isset($rule['to_date']) ? $availability_to_date : '';?>" style="width: 100px;border: 1px solid #dddddd;background: white;" >
			<span class="availability_time_picker_field">
				<input type="time" class="ph_booking_to_timepicker" value="<?php echo isset($rule['to_date']) ? $availability_to_time : '';?>">
			</span>
			<input type="hidden" class="ph_booking_to_datetime" name="ph_booking_to_date[]" value="<?php echo isset($rule['to_date']) ? $rule['to_date'] : '';?>">
		</div>

		
		<div class="availability_by_date_range_and_time" style="display: <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='date-range-and-time' )  ? 'block;' : 'none;';?>">
			<?php
			if(isset($rule['to_date_for_date_range_and_time'])){
				$to_date_rule = explode(" ",$rule['to_date_for_date_range_and_time']);
				$availability_to_date = $to_date_rule[0];
				$availability_to_time = !empty($to_date_rule[1])?$to_date_rule[1]:'';
			}
			 ?>
			<input type="text"  class="availability_date_picker ph_booking_to_date_for_date_range_and_time"  value="<?php echo isset($rule['to_date_for_date_range_and_time']) ? $availability_to_date : '';?>" style="width: 100px;border: 1px solid #dddddd;background: white;" >
			<span class="availability_time_picker_field">
				<input type="time" class="ph_booking_to_timepicker_date_range_and_time" value="<?php echo isset($rule['to_date_for_date_range_and_time']) ? $availability_to_time : '';?>">
			</span>
			<input type="hidden" class="ph_booking_to_datetime_for_date_range_and_time" name="ph_booking_to_date_for_date_range_and_time[]" value="<?php echo isset($rule['to_date_for_date_range_and_time']) ? $rule['to_date_for_date_range_and_time'] : '';?>">
		</div>



		<div class="select availability_by_month" style="display: <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='months' ) ? 'block;' : 'none;';?>">
			<select name="ph_booking_to_month[]">
				<option value="1" <?php echo (isset($rule['to_month']) && $rule['to_month']=='1') ? 'selected="selected"' : '';?> ><?php _e('January','bookings-and-appointments-for-woocommerce')?></option>
				<option value="2" <?php echo (isset($rule['to_month']) && $rule['to_month']=='2') ? 'selected="selected"' : '';?> ><?php _e('Febuary','bookings-and-appointments-for-woocommerce')?></option>
				<option value="3" <?php echo (isset($rule['to_month']) && $rule['to_month']=='3') ? 'selected="selected"' : '';?> ><?php _e('March','bookings-and-appointments-for-woocommerce')?></option>
				<option value="4" <?php echo (isset($rule['to_month']) && $rule['to_month']=='4') ? 'selected="selected"' : '';?> ><?php _e('April','bookings-and-appointments-for-woocommerce')?></option>
				<option value="5" <?php echo (isset($rule['to_month']) && $rule['to_month']=='5') ? 'selected="selected"' : '';?> ><?php _e('May','bookings-and-appointments-for-woocommerce')?></option>
				<option value="6" <?php echo (isset($rule['to_month']) && $rule['to_month']=='6') ? 'selected="selected"' : '';?> ><?php _e('June','bookings-and-appointments-for-woocommerce')?></option>
				<option value="7" <?php echo (isset($rule['to_month']) && $rule['to_month']=='7') ? 'selected="selected"' : '';?> ><?php _e('July','bookings-and-appointments-for-woocommerce')?></option>
				<option value="8" <?php echo (isset($rule['to_month']) && $rule['to_month']=='8') ? 'selected="selected"' : '';?> ><?php _e('August','bookings-and-appointments-for-woocommerce')?></option>
				<option value="9" <?php echo (isset($rule['to_month']) && $rule['to_month']=='9') ? 'selected="selected"' : '';?> ><?php _e('September','bookings-and-appointments-for-woocommerce')?></option>
				<option value="10" <?php echo (isset($rule['to_month']) && $rule['to_month']=='10') ? 'selected="selected"' : '';?> ><?php _e('October','bookings-and-appointments-for-woocommerce')?></option>
				<option value="11" <?php echo (isset($rule['to_month']) && $rule['to_month']=='11') ? 'selected="selected"' : '';?> ><?php _e('November','bookings-and-appointments-for-woocommerce')?></option>
				<option value="12" <?php echo (isset($rule['to_month']) && $rule['to_month']=='12') ? 'selected="selected"' : '';?> ><?php _e('December','bookings-and-appointments-for-woocommerce')?></option>
			</select>
		</div>
		<div class="select availability_by_week_days" style="display: <?php echo ( isset($rule['availability_type'] ) && $rule['availability_type']=='days' ) ? 'block;' : 'none;';?>">
			<select name="ph_booking_to_week_day[]">
				<option value="1" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='1') ? 'selected="selected"' : '';?> ><?php _e('Monday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="2" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='2') ? 'selected="selected"' : '';?> ><?php _e('Tuesday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="3" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='3') ? 'selected="selected"' : '';?> ><?php _e('Wednesday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="4" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='4') ? 'selected="selected"' : '';?> ><?php _e('Thursday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="5" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='5') ? 'selected="selected"' : '';?> ><?php _e('Friday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="6" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='6') ? 'selected="selected"' : '';?> ><?php _e('Saturday','bookings-and-appointments-for-woocommerce')?></option>
				<option value="7" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='7') ? 'selected="selected"' : '';?> ><?php _e('Sunday','bookings-and-appointments-for-woocommerce')?></option>
			</select>
		</div>
		<div class="availability_by_time" style="display: <?php echo ( isset($rule['availability_type'] ) && strpos( $rule['availability_type'], "time" ) !== false  && $rule['availability_type'] != 'date-range-and-time') ? 'block;' : 'none;';?>">
			<input type="time" class="time-picker" name="ph_booking_to_time[]" value="<?php echo isset($rule['to_time']) ? $rule['to_time'] : '' ?>" placeholder="HH:MM">
		</div>
	</td>
	<td>
		<select name="ph_booking_is_bookable[]">
			<option value="no" <?php echo (isset($rule['is_bokable']) && $rule['is_bokable']=='no') ? 'selected="selected"' : '';?> ><?php _e('No','bookings-and-appointments-for-woocommerce')?></option>
			<option value="yes" <?php echo (isset($rule['is_bokable']) && $rule['is_bokable']=='yes') ? 'selected="selected"' : '';?> ><?php _e('Yes','bookings-and-appointments-for-woocommerce')?></option>
		</select>
	</td>
	<td class="remove">
		<a class="close">x</a>
	</td>
</tr>