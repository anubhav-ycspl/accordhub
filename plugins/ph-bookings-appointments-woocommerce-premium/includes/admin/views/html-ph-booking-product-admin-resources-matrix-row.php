<tr class="rule ui-sortable-handle">
	<td class="sort"></td>

	<td style="width:30%">
		<input type="text" name="ph_booking_resources_name[]" value="<?php echo isset($rule['ph_booking_resources_name']) ? $rule['ph_booking_resources_name'] : '' ?>" >
	</td>

	<td>
		<input type="text" name="ph_booking_resources_cost[]" value="<?php echo isset($rule['ph_booking_resources_cost']) ? $rule['ph_booking_resources_cost'] : '' ?>" >
	</td>

	<td>
		<select name="ph_booking_resources_auto_assign[]" style="float: none;width:150px">
			<option value="no" <?php echo (isset($rule['ph_booking_resources_auto_assign']) && $rule['ph_booking_resources_auto_assign']=='no') ? 'selected="selected"' : '';?> ><?php _e('Let customer choose','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="yes" <?php echo (isset($rule['ph_booking_resources_auto_assign']) && $rule['ph_booking_resources_auto_assign']=='yes') ? 'selected="selected"' : '';?> class="ph_assign_option"><?php _e('Automatically assigned','bookings-and-appointments-for-woocommerce') ?></option>
		</select>
	</td>

	<td>
		<select name="ph_booking_resources_per_person[]" style="float: none;">
			<option value="no" <?php echo (isset($rule['ph_booking_resources_per_person']) && $rule['ph_booking_resources_per_person']=='no') ? 'selected="selected"' : '';?> ><?php _e('No','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="yes" <?php echo (isset($rule['ph_booking_resources_per_person']) && $rule['ph_booking_resources_per_person']=='yes') ? 'selected="selected"' : '';?> ><?php _e('Yes','bookings-and-appointments-for-woocommerce') ?></option>
		</select>
	</td>
	
	<td>
		<select name="ph_booking_resources_per_slot[]" style="float: none;">
			<option value="no" <?php echo (isset($rule['ph_booking_resources_per_slot']) && $rule['ph_booking_resources_per_slot']=='no') ? 'selected="selected"' : '';?> ><?php _e('No','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="yes" <?php echo (isset($rule['ph_booking_resources_per_slot']) && $rule['ph_booking_resources_per_slot']=='yes') ? 'selected="selected"' : '';?> ><?php _e('Yes','bookings-and-appointments-for-woocommerce') ?></option>
		</select>
	</td>

	<?php do_action('phive_add_extra_column_value_for_resource_cost_settings', $post->ID, $rule); ?>

	<td class="remove">
		<a class="close">x</a>
	</td>
</tr>
