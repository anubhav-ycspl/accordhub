<?php
if ( isset( $_POST['ph_booking_settings_assets_sumitted'] ) ) {
	$settings = array(
		'_phive_booking_assets'					=> validate_assets_rules(),
		'_phive_booking_assets_availability'	=> validate_assets_availability_rules(),
		'_phive_asset_un_availability'			=> !empty($_POST['_phive_asset_un_availability']) ? sanitize_text_field( $_POST['_phive_asset_un_availability'] ) : '',
	);
	update_option( $this->id.'assets', $settings );

	// resetting the transient value to no
	foreach ($settings['_phive_booking_assets'] as $key => $value) 
	{
		$asset_id = $value['ph_booking_asset_id'];
		if (!empty($asset_id)) 
		{
			$ph_cache_obj = new phive_booking_cache_manager();
			$ph_cache_obj->ph_unset_cache($asset_id);
		}
	}
}

function validate_assets_rules(){
	if( empty($_POST['ph_booking_asset_name']) ){
		return array();
	}
	$assets = array();
	foreach ($_POST['ph_booking_asset_name'] as $key => $value) {
		$asset_id = !empty($_POST['ph_booking_asset_id'][$key]) ? $_POST['ph_booking_asset_id'][$key] : uniqid( substr(sanitize_title_with_dashes($_POST['ph_booking_asset_name'][$key]),0,7) ); //Generate unique id

		$assets[ $asset_id ] = array(
			'ph_booking_asset_id' 		=> $asset_id,
			'ph_booking_asset_name' 	=> trim($_POST['ph_booking_asset_name'][$key]),
			'ph_booking_asset_quantity'	=> $_POST['ph_booking_asset_quantity'][$key],
		);

		// Validate and set the alternate host email ID for a booking asset from Zoom plugin.
		$assets = apply_filters( 'ph_zoom_validate_alt_host_email_id_column_data', $assets, $asset_id, $key );
		
		do_action( 'wpml_register_single_string', 'ph_booking_plugins', $asset_id, $_POST['ph_booking_asset_name'][$key] );		// WPML support
	}
	return $assets;
}

function validate_assets_availability_rules(){
	if( empty($_POST['ph_booking_asset_availability_type']) ){
		return array();
	}
	$rules = array();
	foreach ($_POST['ph_booking_asset_availability_type'] as $key => $value) {
		$rules[] = array(
			'availability_asset_id' => $_POST['ph_booking_asset_availability_asset'][$key],
			'availability_type' 	=> $_POST['ph_booking_asset_availability_type'][$key],
			
			'from_date' 			=> $_POST['ph_booking_asset_from_date'][$key],
			'from_date_for_date_range_and_time' 		=> $_POST['ph_booking_from_date_for_date_range_and_time'][$key],

			'from_week_day' 		=> $_POST['ph_booking_asset_from_week_day'][$key],
			'from_month' 			=> $_POST['ph_booking_asset_from_month'][$key],
			'from_time' 			=> $_POST['ph_booking_asset_from_time'][$key],

			'to_week_day' 			=> $_POST['ph_booking_asset_to_week_day'][$key],
			'to_month' 				=> $_POST['ph_booking_asset_to_month'][$key],
			'to_date' 				=> $_POST['ph_booking_asset_to_date'][$key],
			'to_date_for_date_range_and_time' 			=> $_POST['ph_booking_to_date_for_date_range_and_time'][$key],
			'to_time' 				=> $_POST['ph_booking_asset_to_time'][$key],
			'is_bokable'			=> $_POST['ph_booking_asset_is_bookable'][$key],
		);
	}
	return $rules;
}

$settings 				= get_option( $this->id.'assets', 1 );
$assets 				= isset($settings['_phive_booking_assets']) ? $settings['_phive_booking_assets'] : array();
$assets_availability 	= isset($settings['_phive_booking_assets_availability']) ? $settings['_phive_booking_assets_availability'] : array();
$un_availability		= isset($settings['_phive_asset_un_availability']) ? $settings['_phive_asset_un_availability'] : '';
?>
<html>
<form method="post" action="#">
	<div id='ph_booking_assets' class='panel woocommerce_options_panel'>
		<div style="margin-left:10px;">
			<h2>
				<?php _e('Asset', 'bookings-and-appointments-for-woocommerce')?>
			</h2>
			<p>
				<?php _e('Assets are global resources which can be attached either to a single product or to multiple products. Assets have a quantity and availability attached.', 'bookings-and-appointments-for-woocommerce' )?>
			</p>
			<div>
				<strong><?php _e('Use assets in following cases:', 'bookings-and-appointments-for-woocommerce')?></strong>
				<ol>
					<li><?php _e('When Product A is booked, you need product B to be automatically booked for the same time.', 'bookings-and-appointments-for-woocommerce' );?></li>
					<li><?php _e('When you have a staff that handles multiple services. When Service A is booked, other services from the same staff are unavailable to book for the same time.', 'bookings-and-appointments-for-woocommerce' );?></li>
					<li><?php _e('When you have multiple staff with varying availability or price.', 'bookings-and-appointments-for-woocommerce' );?></li>
					<li><?php _e('When you have multiple resource types with different quantities. For Eg : Types of Kayaks to be chosen by the user to book.', 'bookings-and-appointments-for-woocommerce' );?></li>
				</ol>
				<p>
					<?php 
						$documentation_link = '<a href="https://www.pluginhive.com/knowledge-base/how-to-set-booking-assets-using-woocommerce-bookings-and-appointments-plugin/?utm_source=bookings&utm_medium=plugin_settings" target="_blank">Documentation</a>';
						$documentation = sprintf(__('Refer %s for further details.', 'bookings-and-appointments-for-woocommerce'),$documentation_link);
						echo __($documentation,'bookings-and-appointments-for-woocommerce');
					?>
				</p>
			</div>
		</div>
		
		<input type="hidden" name="ph_booking_settings_assets_sumitted" value="1" />
		<div class="" id="ph_assets_wraper">
			<center>
			<table class="ph_asset_rule_table  wc_input_table sortable" cellspacing="0" name="">
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						 <!-- <th><?php // _e('Assets ID','bookings-and-appointments-for-woocommerce')?></th> -->
						<th><?php _e('Assets Name','bookings-and-appointments-for-woocommerce')?></th>
						<th><?php _e('Quantity','bookings-and-appointments-for-woocommerce')?></th>
						<?php do_action('ph_zoom_add_alt_host_email_id_column_header'); ?>
						<th></th>
					</tr>
				</thead>
				<tbody class="rules ui-sortable">
					<?php
					foreach ($assets as $key => $rule) {
						if( !empty($rule['ph_booking_asset_name']) ){
							include("html-ph-booking-settings-assets-row.php");
						}
					}?>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="7" style="text-align: left;"><a href="#" class="add_rule button">+ <?php _e('Add','bookings-and-appointments-for-woocommerce')?></a></th>
					</tr>
				</tfoot>
			</table>
			</center>
		</div>
	</div>

	<div id='booking_asset_availability' class='panel woocommerce_options_panel'>
		<h2><?php _e('Asset Availability', 'bookings-and-appointments-for-woocommerce')?></h2>
		<p><?php _e('Define availability for each asset.', 'bookings-and-appointments-for-woocommerce')?></p>

		<!-- Make all the assets unavailable by default -->
		<div class="ph-availability-section">
			<p class="form-field _phive_asset_un_availability_field ">
				<label for="_phive_asset_un_availability">
					<input type="checkbox" class="checkbox" <?php echo $un_availability=='yes' ? 'checked' : '' ;?> name="_phive_asset_un_availability" id="_phive_asset_un_availability" value="yes">
					<?php _e('Make all Assets unavailable by default', 'bookings-and-appointments-for-woocommerce')?>
				</label>
			</p>
		</div>
		<div class="ph-availability-section" id="availability_wraper">
			<?php
			if( empty($assets_availability) ){
				$assets_availability = array( 0=>array('availability_type'=>'','from_date'=>'','from_week_day'=>'','from_month'=>'','from_time'=>'','to_week_day'=>'','to_month'=>'','to_date'=>'','to_time'=>'','is_bokable'=>'') );
			}?>
			<table class="ph_availability_table wc_input_table sortable" cellspacing="0" name="availability_rules_table">
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						<th><?php _e('Asset','bookings-and-appointments-for-woocommerce')?></th>
						<th><?php _e('Range Type','bookings-and-appointments-for-woocommerce')?></th>
						<th><?php _e('From','bookings-and-appointments-for-woocommerce')?></th>
						<th><?php _e('To','bookings-and-appointments-for-woocommerce')?></th>
						<th><?php _e('Bookable','bookings-and-appointments-for-woocommerce')?></th>
						<th></th>
					</tr>
				</thead>
				<tbody class="rules ui-sortable">
					<?php
					foreach ($assets_availability as $key => $rule) {
						if( !empty($rule['availability_type']) ){
							include("html-ph-booking-settings-assets-availability-matrix-row.php");
						}
					}?>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="10" style="text-align: left;"><a href="#" class="add_rule button">+ <?php _e('Add','bookings-and-appointments-for-woocommerce')?></a></th>
					</tr>
				</tfoot>
			</table>
		</div>	
	</div>
	<p class="submit">
		<button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes"><?php _e('Save changes','bookings-and-appointments-for-woocommerce');?></button>
	</p>
</form>
<script type="text/javascript">
jQuery(function($) {

	jQuery('#availability_wraper').on( 'click', 'a.add_rule', function(){
		$(".availability_date_picker").datepicker("destroy");
		jQuery(`<?php $rule=""; include("html-ph-booking-settings-assets-availability-matrix-row.php")?>`).appendTo('#availability_wraper table tbody');
		jQuery( ".availability_date_picker" ).datepicker({
			showOtherMonths: true,
			selectOtherMonths: true,
			dateFormat: 'yy-mm-dd',
			minDate: new Date(),
		});
		return false;
	});
});
</script>

</html>

<script type="text/javascript">
jQuery(function($) {

	jQuery('#ph_assets_wraper').on( 'click', 'a.add_rule', function(){
		jQuery(`<?php $rule=""; include("html-ph-booking-settings-assets-row.php")?>`).appendTo('#ph_assets_wraper table tbody');
		return false;
	});
});
</script>
