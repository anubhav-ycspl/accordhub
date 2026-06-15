<div id='booking_assets' class='panel woocommerce_options_panel'>
	<?php
	$global_settings 	= get_option( 'ph_booking_settings_assets', 1 );
	//127287-Warning Message
	$assets 			= (isset($global_settings['_phive_booking_assets']) && !empty($global_settings['_phive_booking_assets'])) ? $global_settings['_phive_booking_assets'] : array();
	$rules 				= get_post_meta( $post->ID, '_phive_booking_assets_pricing_rules', 1 );
	$assets_enable		= get_post_meta( $post->ID, '_phive_booking_assets_enable', 1 );
	$hide_asset			= get_post_meta( $post->ID, '_phive_hide_assets', 1 );
	$assets_label		= get_post_meta( $post->ID, '_phive_booking_assets_label', 1 );

	woocommerce_wp_checkbox( 
		array( 
			'id'			=> '_phive_booking_assets_enable', 
			'label'		 	=> __('Enable Assets', 'bookings-and-appointments-for-woocommerce' ), 
			'value'			=> ($assets_enable=='yes') ? 'yes' : 'no',
			'desc_tip'		=> 'true',
			'description'	=> __( 'Check this option to eanable assets.', 'bookings-and-appointments-for-woocommerce' ),
		)
	);
	?>
	<div class="assets-wraper">
		<input type="hidden" name="phive_booking_assets" value="1">
		<?php 
		woocommerce_wp_select( array(
			'id'		=> '_phive_booking_assets_auto_assign',
			'label' 	=> __( 'Assign', 'bookings-and-appointments-for-woocommerce' ),
			'options'	=>  array(
					'no'	=> __('Let customer choose', 'bookings-and-appointments-for-woocommerce'),
					'yes'	=> __('Automatically assigned', 'bookings-and-appointments-for-woocommerce')
				)
		) );

		woocommerce_wp_checkbox( 
			array( 
				'id'			=> '_phive_hide_assets', 
				'label'		 	=> __('Hide Assets', 'bookings-and-appointments-for-woocommerce' ), 
				'value'			=> ($hide_asset == 'yes') ? 'yes' : 'no',
				'desc_tip'		=> 'true',
				'description'	=> __( 'Hide Asset Details from Cart/Checkout, Order Details and Emails', 'bookings-and-appointments-for-woocommerce' ),
			)
		);

		woocommerce_wp_text_input( array(
			'id'			=> '_phive_booking_assets_label',
			'label'			=> __( 'Label', 'bookings-and-appointments-for-woocommerce' ),
			'desc_tip'		=> 'true',
			'description'	=> __( 'The assets get displayed in this label ', 'bookings-and-appointments-for-woocommerce' ),
			'type' 			=> 'text',
			'value'			=> $assets_label,
			'placeholder'	=> __( 'Asset', 'bookings-and-appointments-for-woocommerce' ),
		) );
		?>
		<div class="" id="assets_rule_wraper">
			<?php
			if( empty($rules) ){
				$rules = array();
			}?>
			<table class="ph_rule_table wc_input_table sortable" cellspacing="0" name="availability_rules_table">
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						<th><?php _e('Assets','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Base Cost','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Block Cost','bookings-and-appointments-for-woocommerce') ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody class="rules ui-sortable">
					<?php
						foreach ($rules as $key => $rule) {
							//If valid asset (Asset is not removed).
							if( in_array( $rule['ph_booking_asset_id'], array_keys($assets) ) ){
								include("html-ph-booking-product-admin-assets-matrix-row.php");
							}
							else {
								// 106356 - unset rule if asset is deleted
								unset($rules[$key]);
							}
						}
						//106356 - updating asset rules after removing any deleted assest
						update_post_meta( $post->ID, "_phive_booking_assets_pricing_rules", $rules );
					?>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="7"><a href="#" class="add_rule button">+ <?php _e('Add','bookings-and-appointments-for-woocommerce') ?></a></th>
					</tr>
				</tfoot>
			</table>
		</div>
		<br>
		<div>
			<a href="<?php echo esc_url( admin_url('admin.php?page=bookings-settings&tab=assets') )?>" target="_blank"><?php _e('Create a new asset', 'bookings-and-appointments-for-woocommerce');?></a>
		</div>
		<span><?php _e( "Save the Product Settings once you create a New Asset in order to create an Asset Rule.", "bookings-and-appointments-for-woocommerce" ) ?></span>
	</div>
</div>
<script type="text/javascript">
jQuery(function($) {
	$('#assets_rule_wraper').on( 'click', '.add_rule', function(){
		jQuery(`<?php $rule=""; include("html-ph-booking-product-admin-assets-matrix-row.php")?>`).appendTo('#assets_rule_wraper table tbody');
		return false;
	});
});
</script>