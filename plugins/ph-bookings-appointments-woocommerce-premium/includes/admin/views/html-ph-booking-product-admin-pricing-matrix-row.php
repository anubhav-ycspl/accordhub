<tr class="rule ui-sortable-handle">
	<td class="sort"></td>

	<td>
		<select name="ph_booking_pricing_rule_type[]" class="ph_booking_pricing_rule_type">
			<option value="slot_based" <?php echo ( isset($rule['pricing_type'] ) && $rule['pricing_type']=='slot_based' ) ? 'selected="selected"' : '';?> ><?php _e('Block Count','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="custom" <?php echo ( isset($rule['pricing_type'] ) && $rule['pricing_type']=='custom' ) ? 'selected="selected"' : '';?> ><?php _e('Custom date range','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="months"<?php echo ( isset($rule['pricing_type'] ) && $rule['pricing_type']=='months') ? 'selected="selected"' : '';?>><?php _e('Range of months','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="days" <?php echo ( isset($rule['pricing_type'] ) && $rule['pricing_type']=='days') ? 'selected="selected"' : '';?>><?php _e('Range of days','bookings-and-appointments-for-woocommerce') ?></option>

			<option value="strict_days" <?php echo ( isset($rule['pricing_type'] ) && $rule['pricing_type']=='strict_days') ? 'selected="selected"' : '';?>><?php _e('Exact Match (days)','bookings-and-appointments-for-woocommerce') ?></option>

			<optgroup label="<?php _e('Time Ranges','bookings-and-appointments-for-woocommerce'); ?>">
				<option value="time-all" <?php echo (isset($rule['pricing_type']) && $rule['pricing_type']=='time-all') ? 'selected="selected"' : '';?>><?php _e('Time Range (all week)','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="time-mon" <?php echo (isset($rule['pricing_type']) && $rule['pricing_type']=='time-mon') ? 'selected="selected"' : '';?>><?php _e('Monday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="time-tue" <?php echo (isset($rule['pricing_type']) && $rule['pricing_type']=='time-tue') ? 'selected="selected"' : '';?>><?php _e('Tuesday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="time-wed" <?php echo (isset($rule['pricing_type']) && $rule['pricing_type']=='time-wed') ? 'selected="selected"' : '';?>><?php _e('Wednesday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="time-thu" <?php echo (isset($rule['pricing_type']) && $rule['pricing_type']=='time-thu') ? 'selected="selected"' : '';?>><?php _e('Thursday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="time-fri" <?php echo (isset($rule['pricing_type']) && $rule['pricing_type']=='time-fri') ? 'selected="selected"' : '';?>><?php _e('Friday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="time-sat" <?php echo (isset($rule['pricing_type']) && $rule['pricing_type']=='time-sat') ? 'selected="selected"' : '';?>><?php _e('Saturday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="time-sun" <?php echo (isset($rule['pricing_type']) && $rule['pricing_type']=='time-sun') ? 'selected="selected"' : '';?>><?php _e('Sunday','bookings-and-appointments-for-woocommerce') ?></option>
			</optgroup>
		</select>
	</td>

	<td>
		<div class="pricing_by_slot" style="display: <?php echo ( isset($rule['pricing_type']) && $rule['pricing_type']=='slot_based' || empty($rule['pricing_type']) ) ? 'block;' : 'none;';?>" >
			<input type="number" min=0 name="pricing_from_slot[]" value="<?php echo isset($rule['from_slot']) ? $rule['from_slot'] : '';?>" style="width: 100px;" >
		</div>
		<div class="pricing_by_date" style="display: <?php echo ( isset($rule['pricing_type'] ) && $rule['pricing_type']=='custom' ) ? 'block;' : 'none;';?>">
			<input type="text" class="pricing_date_picker" name="pricing_from_date[]" value="<?php echo isset($rule['from_date']) ? $rule['from_date'] : '';?>" style="width: 100px;" >
		</div>
		<div class="select pricing_by_month" style="display: <?php echo ( isset($rule['pricing_type'] ) && $rule['pricing_type']=='months' ) ? 'block;' : 'none;';?>">
			<select name="pricing_from_month[]">
				<option value="1" <?php echo (isset($rule['from_month']) && $rule['from_month']=='1') ? 'selected="selected"' : '';?> ><?php _e('January','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="2" <?php echo (isset($rule['from_month']) && $rule['from_month']=='2') ? 'selected="selected"' : '';?> ><?php _e('Febuary','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="3" <?php echo (isset($rule['from_month']) && $rule['from_month']=='3') ? 'selected="selected"' : '';?> ><?php _e('March','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="4" <?php echo (isset($rule['from_month']) && $rule['from_month']=='4') ? 'selected="selected"' : '';?> ><?php _e('April','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="5" <?php echo (isset($rule['from_month']) && $rule['from_month']=='5') ? 'selected="selected"' : '';?> ><?php _e('May','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="6" <?php echo (isset($rule['from_month']) && $rule['from_month']=='6') ? 'selected="selected"' : '';?> ><?php _e('June','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="7" <?php echo (isset($rule['from_month']) && $rule['from_month']=='7') ? 'selected="selected"' : '';?> ><?php _e('July','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="8" <?php echo (isset($rule['from_month']) && $rule['from_month']=='8') ? 'selected="selected"' : '';?> ><?php _e('August','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="9" <?php echo (isset($rule['from_month']) && $rule['from_month']=='9') ? 'selected="selected"' : '';?> ><?php _e('September','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="10" <?php echo (isset($rule['from_month']) && $rule['from_month']=='10') ? 'selected="selected"' : '';?> ><?php _e('October','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="11" <?php echo (isset($rule['from_month']) && $rule['from_month']=='11') ? 'selected="selected"' : '';?> ><?php _e('November','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="12" <?php echo (isset($rule['from_month']) && $rule['from_month']=='12') ? 'selected="selected"' : '';?> ><?php _e('December','bookings-and-appointments-for-woocommerce') ?></option>
			</select>
		</div>
		<div class="select pricing_by_week_days" style="display: <?php echo ( isset($rule['pricing_type'] ) && ( $rule['pricing_type']=='days' || $rule['pricing_type']=='strict_days' ) ) ? 'block;' : 'none;';?>">
			<select name="pricing_from_week_day[]">
				<option value="1" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='1') ? 'selected="selected"' : '';?> ><?php _e('Monday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="2" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='2') ? 'selected="selected"' : '';?> ><?php _e('Tuesday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="3" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='3') ? 'selected="selected"' : '';?> ><?php _e('Wednesday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="4" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='4') ? 'selected="selected"' : '';?> ><?php _e('Thursday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="5" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='5') ? 'selected="selected"' : '';?> ><?php _e('Friday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="6" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='6') ? 'selected="selected"' : '';?> ><?php _e('Saturday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="7" <?php echo (isset($rule['from_week_day']) && $rule['from_week_day']=='7') ? 'selected="selected"' : '';?> ><?php _e('Sunday','bookings-and-appointments-for-woocommerce') ?></option>
			</select>
		</div>
		<div class="pricing_by_time" style="display: <?php echo ( isset($rule['pricing_type'] ) && strpos( $rule['pricing_type'], "time" ) !== false ) ? 'block;' : 'none;';?>">
			<input type="time" class="time-picker" name="pricing_from_time[]" value="<?php echo isset($rule['from_time']) ? $rule['from_time'] : '' ?>" placeholder="HH:MM">
		</div>
	</td>

	<td>
		<div class="pricing_by_slot" style="display: <?php echo ( isset($rule['pricing_type']) && $rule['pricing_type']=='slot_based' || empty($rule['pricing_type']) ) ? 'block;' : 'none;';?>" >
			<input type="number" min=0 name="pricing_to_slot[]" value="<?php echo isset($rule['to_slot']) ? $rule['to_slot'] : '';?>" style="width: 100px;" >
		</div>
		<div class="pricing_by_date" style="display: <?php echo ( isset($rule['pricing_type']) && $rule['pricing_type']=='custom' )  ? 'block;' : 'none;';?>">
			<input type="text" class="pricing_date_picker" name="pricing_to_date[]" value="<?php echo isset($rule['to_date']) ? $rule['to_date'] : '';?>" style="width: 100px;" >
		</div>
		<div class="select pricing_by_month" style="display: <?php echo ( isset($rule['pricing_type'] ) && $rule['pricing_type']=='months' ) ? 'block;' : 'none;';?>">
			<select name="pricing_to_month[]">
				<option value="1" <?php echo (isset($rule['to_month']) && $rule['to_month']=='1') ? 'selected="selected"' : '';?> ><?php _e('January','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="2" <?php echo (isset($rule['to_month']) && $rule['to_month']=='2') ? 'selected="selected"' : '';?> ><?php _e('Febuary','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="3" <?php echo (isset($rule['to_month']) && $rule['to_month']=='3') ? 'selected="selected"' : '';?> ><?php _e('March','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="4" <?php echo (isset($rule['to_month']) && $rule['to_month']=='4') ? 'selected="selected"' : '';?> ><?php _e('April','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="5" <?php echo (isset($rule['to_month']) && $rule['to_month']=='5') ? 'selected="selected"' : '';?> ><?php _e('May','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="6" <?php echo (isset($rule['to_month']) && $rule['to_month']=='6') ? 'selected="selected"' : '';?> ><?php _e('June','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="7" <?php echo (isset($rule['to_month']) && $rule['to_month']=='7') ? 'selected="selected"' : '';?> ><?php _e('July','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="8" <?php echo (isset($rule['to_month']) && $rule['to_month']=='8') ? 'selected="selected"' : '';?> ><?php _e('August','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="9" <?php echo (isset($rule['to_month']) && $rule['to_month']=='9') ? 'selected="selected"' : '';?> ><?php _e('September','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="10" <?php echo (isset($rule['to_month']) && $rule['to_month']=='10') ? 'selected="selected"' : '';?> ><?php _e('October','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="11" <?php echo (isset($rule['to_month']) && $rule['to_month']=='11') ? 'selected="selected"' : '';?> ><?php _e('November','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="12" <?php echo (isset($rule['to_month']) && $rule['to_month']=='12') ? 'selected="selected"' : '';?> ><?php _e('December','bookings-and-appointments-for-woocommerce') ?></option>
			</select>
		</div>
		<div class="select pricing_by_week_days" style="display: <?php echo ( isset($rule['pricing_type'] ) && ($rule['pricing_type']=='days' || $rule['pricing_type']=='strict_days' )) ? 'block;' : 'none;';?>">
			<select name="pricing_to_week_day[]">
				<option value="1" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='1') ? 'selected="selected"' : '';?> ><?php _e('Monday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="2" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='2') ? 'selected="selected"' : '';?> ><?php _e('Tuesday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="3" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='3') ? 'selected="selected"' : '';?> ><?php _e('Wednesday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="4" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='4') ? 'selected="selected"' : '';?> ><?php _e('Thursday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="5" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='5') ? 'selected="selected"' : '';?> ><?php _e('Friday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="6" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='6') ? 'selected="selected"' : '';?> ><?php _e('Saturday','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="7" <?php echo (isset($rule['to_week_day']) && $rule['to_week_day']=='7') ? 'selected="selected"' : '';?> ><?php _e('Sunday','bookings-and-appointments-for-woocommerce') ?></option>
			</select>
		</div>
		<div class="pricing_by_time" style="display: <?php echo ( isset($rule['pricing_type'] ) && strpos( $rule['pricing_type'], "time" ) !== false ) ? 'block;' : 'none;';?>">
			<input type="time" class="time-picker" name="pricing_to_time[]" value="<?php echo isset($rule['to_time']) ? $rule['to_time'] : '' ?>" placeholder="HH:MM">
		</div>
	</td>

	<td>
		<select name="basecost_operator[]">
			<option value="add" <?php isset($rule['basecost_operator']) ? selected( $rule['basecost_operator'], "add" ) : '' ?> >+</option>
			<option value="sub" <?php isset($rule['basecost_operator']) ? selected( $rule['basecost_operator'], "sub" ) : '' ?> >-</option>
			<option value="mul" <?php isset($rule['basecost_operator']) ? selected( $rule['basecost_operator'], "mul" ) : '' ?> >x</option>
			<option value="div" <?php isset($rule['basecost_operator']) ? selected( $rule['basecost_operator'], "div" ) : '' ?> >÷</option>
		</select>
	</td>
	
	<td>
		<input type="text" class="ph_base_cost" name="ph_booking_rule_base_cost[]" value="<?php echo !empty($rule['base_cost']) ? $rule['base_cost'] : '';?>">
	</td>

	<td>
		<select name="perunit_operator[]">
			<option value="add" <?php isset($rule['perunit_operator']) ? selected( $rule['perunit_operator'], "add" ) : '' ?> >+</option>
			<option value="sub" <?php isset($rule['perunit_operator']) ? selected( $rule['perunit_operator'], "sub" ) : '' ?> >-</option>
			<option value="mul" <?php isset($rule['perunit_operator']) ? selected( $rule['perunit_operator'], "mul" ) : '' ?> >x</option>
			<option value="div" <?php isset($rule['perunit_operator']) ? selected( $rule['perunit_operator'], "div" ) : '' ?> >÷</option>
		</select>
	</td>

	<td>
		<input type="text" class="ph_cost_per_block" name="ph_booking_rule_cost_per_unit[]" value="<?php echo !empty($rule['cost_per_unit']) ? $rule['cost_per_unit'] : '';?>">
	</td>
	<td class="remove">
		<a class="close">x</a>
	</td>
</tr>