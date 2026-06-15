<tr class="rule ui-sortable-handle">
	<td class="sort"></td>
	<!-- <td> -->
		<input type="hidden" readonly name="ph_booking_asset_id[]" value="<?php echo isset($rule['ph_booking_asset_id']) ? $rule['ph_booking_asset_id'] : '' ?>">
	<!-- </td> -->
	<td>
		<input type="text" name="ph_booking_asset_name[]" value="<?php echo isset($rule['ph_booking_asset_name']) ? $rule['ph_booking_asset_name'] : '' ?>">
	</td>
	<td>
		<div class="">
			<input type="number" min=0 name="ph_booking_asset_quantity[]" value="<?php echo isset($rule['ph_booking_asset_quantity']) ? $rule['ph_booking_asset_quantity'] : '' ?>">
		</div>
	</td>

	<?php do_action('ph_zoom_add_alt_host_email_id_column_data', $rule ); ?>
	
	<td class="remove">
		<a class="close">x</a>
	</td>
</tr>