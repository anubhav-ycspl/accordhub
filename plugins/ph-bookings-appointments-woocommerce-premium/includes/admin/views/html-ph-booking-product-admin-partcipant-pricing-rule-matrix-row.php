<tr class="rule ui-sortable-handle participant_rules">
	<td class="sort"></td>

	<td>
		<select name="ph_booking_participant_price_rule_type[]" class="ph_booking_participant_price_rule_type" style="width: 173px;">
			<option value="participant_based" <?php echo ( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based' ) ? 'selected="selected"' : '';?> ><?php _e('Participant Total Count','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="participant_based_custom_date" <?php echo ( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based_custom_date' ) ? 'selected="selected"' : '';?> ><?php _e('Participant Total Count With Custom Date','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="participant_based_week_day" <?php echo ( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based_week_day' ) ? 'selected="selected"' : '';?> ><?php _e('Participant Total Count With Week Days','bookings-and-appointments-for-woocommerce') ?></option>
			<option value="participant_based_block_count" <?php echo ( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based_block_count' ) ? 'selected="selected"' : '';?> ><?php _e('Participant Total Count With Block Count','bookings-and-appointments-for-woocommerce') ?></option>
			<?php
			foreach ($rules as $keys => $value) {
				?>
				<option value="<?php echo $keys ?>" <?php echo ( isset($rule['ph_booking_participant_rule_type'] ) && is_numeric($rule['ph_booking_participant_rule_type']) && $rule['ph_booking_participant_rule_type']== $keys ) ? 'selected="selected"' : '';?> ><?php echo isset($value['ph_booking_persons_rule_type']) ? $value['ph_booking_persons_rule_type'] : '' ?></option>
				<?php
				
			}
			?>
			<?php
			foreach ($rules as $keys => $value) {
				?>
				<option value="block_count_with_<?php echo $keys ?>" <?php echo ( isset($rule['ph_booking_participant_rule_type'] )  && $rule['ph_booking_participant_rule_type']== 'block_count_with_'.$keys ) ? 'selected="selected"' : '';?> ><?php echo isset($value['ph_booking_persons_rule_type']) ? $value['ph_booking_persons_rule_type'].' with block count' : '' ?></option>
				<?php	
			}
				
			?>

		</select>
	</td>

	<td>
		<div class="pricing_by_participant"  >
			<input type="number" min=0 name="pricing_from_participant[]" value="<?php echo isset($rule['pricing_from_participant']) ? $rule['pricing_from_participant'] : '';?>" style="width: 115px !important;" placeholder="<?php _e('Participant Count','bookings-and-appointments-for-woocommerce') ?>">
			<input type="text" class="pricing_from_participant_date_from datepicker_form" min=0 name="pricing_from_participant_date_from[]" value="<?php echo isset($rule['pricing_from_participant_date_from']) ? $rule['pricing_from_participant_date_from'] : '';?>" style="    width: 115px !important; <?php echo !( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based_custom_date' ) ? "display:none;":''; ?>"  placeholder="YYYY-MM-DD">
			<select name="pricing_from_participant_day_from[]" class="pricing_from_participant_day_from" style="width: 115px !important; <?php echo !( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based_week_day' ) ? "display:none;":''; ?>">
                <option value="1" <?php echo (isset($rule['pricing_from_participant_day_from']) && $rule['pricing_from_participant_day_from']=='1') ? 'selected="selected"' : '';?> ><?php _e('Sunday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="2" <?php echo (isset($rule['pricing_from_participant_day_from']) && $rule['pricing_from_participant_day_from']=='2') ? 'selected="selected"' : '';?> ><?php _e('Monday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="3" <?php echo (isset($rule['pricing_from_participant_day_from']) && $rule['pricing_from_participant_day_from']=='3') ? 'selected="selected"' : '';?> ><?php _e('Tuesday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="4" <?php echo (isset($rule['pricing_from_participant_day_from']) && $rule['pricing_from_participant_day_from']=='4') ? 'selected="selected"' : '';?> ><?php _e('Wednesday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="5" <?php echo (isset($rule['pricing_from_participant_day_from']) && $rule['pricing_from_participant_day_from']=='5') ? 'selected="selected"' : '';?> ><?php _e('Thursday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="6" <?php echo (isset($rule['pricing_from_participant_day_from']) && $rule['pricing_from_participant_day_from']=='6') ? 'selected="selected"' : '';?> ><?php _e('Friday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="7" <?php echo (isset($rule['pricing_from_participant_day_from']) && $rule['pricing_from_participant_day_from']=='7') ? 'selected="selected"' : '';?> ><?php _e('Saturday','bookings-and-appointments-for-woocommerce')?></option>
			</select>
			<input type="number" class="pricing_from_participant_block_count" min=0 name="pricing_from_participant_block_count[]" value="<?php echo isset($rule['pricing_from_participant_block_count']) ? $rule['pricing_from_participant_block_count'] : '';?>" style="    width: 115px !important; <?php echo !( isset($rule['ph_booking_participant_rule_type'] ) && ($rule['ph_booking_participant_rule_type']=='participant_based_block_count' || strpos($rule['ph_booking_participant_rule_type'], 'block_count_with_') !== false) ) ? "display:none;":''; ?>" placeholder="<?php _e('Block Count','bookings-and-appointments-for-woocommerce') ?>">
		</div>
	</td>

	<td>
		<div class="pricing_by_participant"  >
			<input type="number" min=0 name="pricing_to_participant[]" value="<?php echo isset($rule['pricing_to_participant']) ? $rule['pricing_to_participant'] : '';?>" style="width: 115px !important;" placeholder="<?php _e('Participant Count','bookings-and-appointments-for-woocommerce') ?>">
			<input type="text" class="pricing_from_participant_date_to datepicker_form" min=0 name="pricing_from_participant_date_to[]" value="<?php echo isset($rule['pricing_from_participant_date_to']) ? $rule['pricing_from_participant_date_to'] : '';?>" style="    width: 115px !important; <?php echo !( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based_custom_date' ) ? "display:none;":''; ?>"  placeholder="YYYY-MM-DD">
			<select name="pricing_from_participant_day_to[]" class="pricing_from_participant_day_to" style="width: 115px !important; <?php echo !( isset($rule['ph_booking_participant_rule_type'] ) && $rule['ph_booking_participant_rule_type']=='participant_based_week_day' ) ? "display:none;":''; ?>">
                <option value="1" <?php echo (isset($rule['pricing_from_participant_day_to']) && $rule['pricing_from_participant_day_to']=='1') ? 'selected="selected"' : '';?> ><?php _e('Sunday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="2" <?php echo (isset($rule['pricing_from_participant_day_to']) && $rule['pricing_from_participant_day_to']=='2') ? 'selected="selected"' : '';?> ><?php _e('Monday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="3" <?php echo (isset($rule['pricing_from_participant_day_to']) && $rule['pricing_from_participant_day_to']=='3') ? 'selected="selected"' : '';?> ><?php _e('Tuesday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="4" <?php echo (isset($rule['pricing_from_participant_day_to']) && $rule['pricing_from_participant_day_to']=='4') ? 'selected="selected"' : '';?> ><?php _e('Wednesday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="5" <?php echo (isset($rule['pricing_from_participant_day_to']) && $rule['pricing_from_participant_day_to']=='5') ? 'selected="selected"' : '';?> ><?php _e('Thursday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="6" <?php echo (isset($rule['pricing_from_participant_day_to']) && $rule['pricing_from_participant_day_to']=='6') ? 'selected="selected"' : '';?> ><?php _e('Friday','bookings-and-appointments-for-woocommerce')?></option>
                <option value="7" <?php echo (isset($rule['pricing_from_participant_day_to']) && $rule['pricing_from_participant_day_to']=='7') ? 'selected="selected"' : '';?> ><?php _e('Saturday','bookings-and-appointments-for-woocommerce')?></option>
			</select>
			<input type="number" class="pricing_to_participant_block_count" min=0 name="pricing_to_participant_block_count[]" value="<?php echo isset($rule['pricing_to_participant_block_count']) ? $rule['pricing_to_participant_block_count'] : '';?>" style="    width: 115px !important; <?php echo !( isset($rule['ph_booking_participant_rule_type'] ) && ( $rule['ph_booking_participant_rule_type']=='participant_based_block_count' || strpos($rule['ph_booking_participant_rule_type'], 'block_count_with_') !== false) ) ? "display:none;":''; ?>" placeholder="<?php _e('Block Count','bookings-and-appointments-for-woocommerce') ?>">
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