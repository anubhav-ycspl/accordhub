<tr class="rule ui-sortable-handle">
	<td class="sort"></td>

	<td>
		<select name="ph_booking_participant_rule_type[]" class="ph_booking_participant_rule_type">
			<option value="participant_based" <?php echo ( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based' ) ? 'selected="selected"' : '';?> ><?php _e('Participant Count','bookings-and-appointments-for-woocommerce') ?></option>
		</select>
	</td>

	<td>
		<div class="pricing_by_participant" style="display: <?php echo ( isset($rule['ph_booking_participant_rule_type']) && $rule['ph_booking_participant_rule_type']=='participant_based' || empty($rule['ph_booking_participant_rule_type']) ) ? 'block;' : 'none;';?>" >
			<input type="number" min=0 name="pricing_from_participant[]" value="<?php echo isset($rule['pricing_from_participant']) ? $rule['pricing_from_participant'] : '';?>" style="width: 100px;" >
		</div>
	</td>

	<td>
		<div class="pricing_by_participant" style="display: <?php echo ( isset($rule['ph_booking_participant_rule_type']) && $rule['ph_booking_participant_rule_type']=='participant_based' || empty($rule['ph_booking_participant_rule_type']) ) ? 'block;' : 'none;';?>" >
			<input type="number" min=0 name="pricing_to_participant[]" value="<?php echo isset($rule['pricing_to_participant']) ? $rule['pricing_to_participant'] : '';?>" style="width: 100px;" >
		</div>
	</td>

	<td>
		<select name="participantbasecost_operator[]">
			<option value="add" <?php isset($rule['participantbasecost_operator']) ? selected( $rule['participantbasecost_operator'], "add" ) : '' ?> >+</option>
			<option value="sub" <?php isset($rule['participantbasecost_operator']) ? selected( $rule['participantbasecost_operator'], "sub" ) : '' ?> >-</option>
			<option value="mul" <?php isset($rule['participantbasecost_operator']) ? selected( $rule['participantbasecost_operator'], "mul" ) : '' ?> >x</option>
			<option value="div" <?php isset($rule['participantbasecost_operator']) ? selected( $rule['participantbasecost_operator'], "div" ) : '' ?> >÷</option>
		</select>
	</td>
	
	<td>
		<input type="text" class="ph_base_cost" name="ph_booking_participant_rule_base_cost[]" value="<?php echo !empty($rule['ph_booking_participant_rule_base_cost']) ? $rule['ph_booking_participant_rule_base_cost'] : '';?>">
	</td>

	<td>
		<select name="perparticipant_operator[]">
			<option value="add" <?php isset($rule['perparticipant_operator']) ? selected( $rule['perparticipant_operator'], "add" ) : '' ?> >+</option>
			<option value="sub" <?php isset($rule['perparticipant_operator']) ? selected( $rule['perparticipant_operator'], "sub" ) : '' ?> >-</option>
			<option value="mul" <?php isset($rule['perparticipant_operator']) ? selected( $rule['perparticipant_operator'], "mul" ) : '' ?> >x</option>
			<option value="div" <?php isset($rule['perparticipant_operator']) ? selected( $rule['perparticipant_operator'], "div" ) : '' ?> >÷</option>
		</select>
	</td>

	<td>
		<input type="text" class="ph_cost_per_participant" name="ph_booking_rule_cost_per_participant[]" value="<?php echo !empty($rule['ph_booking_rule_cost_per_participant']) ? $rule['ph_booking_rule_cost_per_participant'] : '';?>">
	</td>
	<td class="remove">
		<a class="close">x</a>
	</td>
</tr>