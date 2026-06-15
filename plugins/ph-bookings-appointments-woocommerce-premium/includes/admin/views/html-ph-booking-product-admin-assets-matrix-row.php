<tr class="rule ui-sortable-handle">
	<td class="sort" style="width:3%"></td>

	<td style="width:30%">
		<select name="ph_booking_asset[]" style="float: none;width:150px">
			<?php
			if(isset($assets))
			{
				foreach ($assets as $key => $asset) {
					?><option <?php if( isset($rule['ph_booking_asset_id']) ){selected( $rule['ph_booking_asset_id'], $key );}?> value="<?php echo $key?>"><?php echo $asset['ph_booking_asset_name'] ?></option><?php
				}
			}
			?>
		</select>
	</td>

	<td style="width:20%">
		<input type="text" name="ph_booking_assets_base_cost[]" value="<?php echo isset($rule['ph_booking_assets_base_cost']) ? $rule['ph_booking_assets_base_cost'] : '' ?>" >
	</td>
	
	<td style="width:20%">
		<input type="text" name="ph_booking_assets_cost_perblock[]" value="<?php echo isset($rule['ph_booking_assets_cost_perblock']) ? $rule['ph_booking_assets_cost_perblock'] : '' ?>" >
	</td>
	
	<td class="remove" style="width:3%">
		<a class="close">x</a>
	</td>
</tr>
