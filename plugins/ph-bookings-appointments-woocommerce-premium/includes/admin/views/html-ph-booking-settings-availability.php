<?php
if ( isset( $_POST['ph_booking_settings_availability_sumitted'] ) ) {
	$settings = array(
		'_phive_restrict_start_day' 		=> !empty($_POST['_phive_restrict_start_day']) ? sanitize_text_field( $_POST['_phive_restrict_start_day'] ) : '',
		'_phive_booking_start_days' 		=> !empty($_POST['_phive_booking_start_days']) ? $_POST['_phive_booking_start_days'] : '',
		'_phive_un_availability' 			=> !empty($_POST['_phive_un_availability']) ? sanitize_text_field( $_POST['_phive_un_availability'] ) : '',
		'use_glb_and_prod_avail' 			=> !empty($_POST['use_glb_and_prod_avail']) ? sanitize_text_field( $_POST['use_glb_and_prod_avail'] ) : 'no',
		'_phive_booking_availability_rules'	=> validate_availability_rules(),
	);
	update_option( $this->id.'availability', $settings );

}
function validate_availability_rules(){
	if( empty($_POST['ph_booking_availability_type']) ){
		return array();
	}
	$rules = array();
	foreach ($_POST['ph_booking_availability_type'] as $key => $value) {
		$rules[] = array(
			'availability_type' => $_POST['ph_booking_availability_type'][$key],
			
			'from_date' 		=> $_POST['ph_booking_from_date'][$key],
			'from_date_for_date_range_and_time' 		=> $_POST['ph_booking_from_date_for_date_range_and_time'][$key],
			'from_week_day' 	=> $_POST['ph_booking_from_week_day'][$key],
			'from_month' 		=> $_POST['ph_booking_from_month'][$key],
			'from_time' 		=> $_POST['ph_booking_from_time'][$key],

			'to_week_day' 		=> $_POST['ph_booking_to_week_day'][$key],
			'to_month' 			=> $_POST['ph_booking_to_month'][$key],
			'to_date' 			=> $_POST['ph_booking_to_date'][$key],
			'to_date_for_date_range_and_time' 			=> $_POST['ph_booking_to_date_for_date_range_and_time'][$key],
			'to_time' 			=> $_POST['ph_booking_to_time'][$key],
			'is_bokable'		=> $_POST['ph_booking_is_bookable'][$key],
		);
	}
	return $rules;
}
$availability_settings 		= get_option( $this->id.'availability', 1 );
$rules 						= isset($availability_settings['_phive_booking_availability_rules']) ? $availability_settings['_phive_booking_availability_rules'] : array();
$un_availability			= isset($availability_settings['_phive_un_availability']) ? $availability_settings['_phive_un_availability'] : '';
$use_glb_and_prod_avail		= isset($availability_settings['use_glb_and_prod_avail']) ? $availability_settings['use_glb_and_prod_avail'] : '';
?>
<html>
<h2><?php _e('Global Availability','bookings-and-appointments-for-woocommerce')?></h2>
<p><?php _e('Define global availability rules here. These will reflect in all the bookable products and can be overridden at a product level.', 'bookings-and-appointments-for-woocommerce');
	echo "<br>";
	_e( "There are two ways you can set the availability rules", "bookings-and-appointments-for-woocommerce" );
						
?></p>

			<span style="">
				<i>
					<?php
						_e( "1. All dates are available by default. Create rules to set the time period when you are not available to take bookings.", "bookings-and-appointments-for-woocommerce" );
						echo "<br>OR<br>";
						_e( "2. Mark all the dates/blocks as unavailable and then set the time period when you are available for bookings.", "bookings-and-appointments-for-woocommerce" );
					?>
				</i>
			</span>
<form method="post" action="#">
<div id='booking_availability' class='panel woocommerce_options_panel'>
	<input type="hidden" name="ph_booking_settings_availability_sumitted" value="1" />
	<div class="ph-availability-section">
		<!-- <div class="ph-availability-section"> -->
			<p class="form-field _phive_un_availability_field ">
				<label for="_phive_un_availability">
					<input type="checkbox" class="checkbox" <?php echo $un_availability=='yes' ? 'checked' : '' ;?> name="_phive_un_availability" id="_phive_un_availability" value="yes">
					<?php _e('Make all dates/blocks unavailable by default', 'bookings-and-appointments-for-woocommerce')?>
				</label>
				<!-- <span class="woocommerce-help-tip" data-tip="<?php // _e('Enabling this option will disable all dates in the calendar. Using this option with availability rules, you can enable desired dates for bookings.', 'bookings-and-appointments-for-woocommerce')?>"></span> -->
			</p>
			<p class="form-field use_glb_and_prod_avail_field ">
				<label for="use_glb_and_prod_avail">
					<input type="checkbox" class="checkbox" <?php echo $use_glb_and_prod_avail=='yes' ? 'checked' : '' ;?> name="use_glb_and_prod_avail" id="use_glb_and_prod_avail" value="yes">
					<?php _e('Enable this to ensure product-specific availability settings take precedence. Global availability rules will only apply if no product-specific rule exists for a given time slot.', 'bookings-and-appointments-for-woocommerce')?>
				</label>
			</p>
		<!-- </div> -->
	</div>

	<div class="ph-availability-section" id="availability_wraper">
		<?php
		if( empty($rules) ){
			$rules = array( 0=>array('availability_type'=>'','from_date'=>'','from_week_day'=>'','from_month'=>'','from_time'=>'','to_week_day'=>'','to_month'=>'','to_date'=>'','to_time'=>'','is_bokable'=>'') );
		}?>
		<table class="ph_rule_table ph_availability_table wc_input_table sortable" cellspacing="0" name="availability_rules_table">
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
					<th colspan="7"><a href="#" class="add_rule button">+ <?php _e('Add','bookings-and-appointments-for-woocommerce')?></a></th>
				</tr>
			</tfoot>
		</table>
	</div>
	<p class="submit">
		<button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes"><?php _e('Save changes','bookings-and-appointments-for-woocommerce');?></button>
	</p>
</div>
</form>
</html>
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
		return false;
	});
});
</script>
