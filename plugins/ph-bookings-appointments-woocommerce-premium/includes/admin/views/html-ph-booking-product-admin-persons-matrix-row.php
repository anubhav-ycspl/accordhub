<tr class="rule ui-sortable-handle">
	<td class="sort"></td>
	<td style="width:30%">
		<input type="text" name="ph_booking_persons_rule_type[]" class="ph_booking_persons_rule_type"  value="<?php echo isset($rule['ph_booking_persons_rule_type']) ? $rule['ph_booking_persons_rule_type'] : '' ?>" >
	</td>
	<td>
		<input type="number" min='0' name="ph_booking_persons_rule_min[]" class="ph_booking_persons_rule_min"  value="<?php echo isset($rule['ph_booking_persons_rule_min']) ? $rule['ph_booking_persons_rule_min'] : '' ?>" >
	</td>
	<td>
		<input type="number" min='0' name="ph_booking_persons_rule_max[]"  class="ph_booking_persons_rule_max" value="<?php echo isset($rule['ph_booking_persons_rule_max']) ? $rule['ph_booking_persons_rule_max'] : '' ?>" >
	</td>
	<td>
		<input type="text" name="ph_booking_persons_rule_base_cost[]" value="<?php echo isset($rule['ph_booking_persons_rule_base_cost']) ? $rule['ph_booking_persons_rule_base_cost'] : '' ?>" >
	</td>
	<td>
		<input type="text" name="ph_booking_persons_rule_cost_per_unit[]" value="<?php echo isset($rule['ph_booking_persons_rule_cost_per_unit']) ? $rule['ph_booking_persons_rule_cost_per_unit'] : '' ?>" >
	</td>
	<td>
		<select name="ph_booking_persons_per_slot[]" style="float: none;">
			<option value="no" <?php echo (isset($rule['ph_booking_persons_per_slot']) && $rule['ph_booking_persons_per_slot']=='no') ? 'selected="selected"' : '';?> ><?php _e('No','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="yes" <?php echo (isset($rule['ph_booking_persons_per_slot']) && $rule['ph_booking_persons_per_slot']=='yes') ? 'selected="selected"' : '';?> ><?php _e('Yes','bookings-and-appointments-for-woocommerce') ?></option>
		</select>
	</td>
	<td <?php echo $key !== 0 ? 'class="remove"' : ''?> >
		<?php 
		// First row should not be removed.
		if( $key !== 0 ):?>
			<a class="close">x</a> 
		<?php endif;?>
	</td>
</tr>
