<div id='booking_pricing' class='panel woocommerce_options_panel'>

	<div class="" id="pricing_wraper">
		<?php
		$rules         = get_post_meta( $post->ID, '_phive_booking_pricing_rules', 1 );
		$cost_per_unit = get_post_meta( $post->ID, '_phive_booking_pricing_cost_per_unit', 1 );
		$display_cost  = get_post_meta( $post->ID, '_phive_booking_pricing_display_cost', 1 );
		$base_cost     = get_post_meta( $post->ID, '_phive_booking_pricing_base_cost', 1 );

		$display_cost_suffix = get_post_meta( $post->ID, '_phive_booking_pricing_display_cost_suffix', 1 );

		woocommerce_wp_text_input(
			array(
				'type'        => 'text',
				'id'          => 'ph_booking_base_cost',
				'label'       => __( 'Base Cost', 'bookings-and-appointments-for-woocommerce' ),
				'value'       => $base_cost,
				'desc_tip'    => 'true',
				'description' => __( 'This is a one-off cost that is applied to the entire booking.', 'bookings-and-appointments-for-woocommerce' ),
				'style'       => 'width: 150px',
			)
		);
		woocommerce_wp_text_input(
			array(
				'type'        => 'text',
				'id'          => 'ph_booking_cost_per_unit',
				'label'       => __( 'Cost per block', 'bookings-and-appointments-for-woocommerce' ),
				'value'       => $cost_per_unit,
				'desc_tip'    => 'true',
				'description' => __( 'This is the cost applied to each block booked. for Eg : When booking period is 1 day and cost per block is $10, the total cost for booking 10 days is $100.', 'bookings-and-appointments-for-woocommerce' ),
				'style'       => 'width: 150px',
			)
		);
		woocommerce_wp_text_input(
			array(
				'type'          => 'text',
				'id'            => 'ph_booking_display_cost',
				'label'         => __( 'Display Cost', 'bookings-and-appointments-for-woocommerce' ),
				'value'         => $display_cost,
				'desc_tip'      => 'true',
				'description'   => __( 'Display cost will appear as the product price in the front end. This price is not considered for the dynamic booking cost calculations. If not set, the Base Cost  + the block cost will be the price displayed.', 'bookings-and-appointments-for-woocommerce' ),
				'style'         => 'width: 150px',
				'placeholder'   => 'Cost(Optional)',
				'wrapper_class' => 'display-field-width',
			)
		);
		?>

		<p class='form-field ph_booking_display_cost_suffix_field display-suffix-width' style='float:left;
		padding-left: 0px !important;'>
		<span class='woocommerce-help-tip' data-tip='<?php _e( 'Will be displayed as a suffix with Display Cost.', 'bookings-and-appointments-for-woocommerce' ); ?>'></span><input type='text' class='short' style='width: 190px;' name='ph_booking_display_cost_suffix' id='ph_booking_display_cost_suffix' value='<?php echo $display_cost_suffix; ?>' placeholder='Cost  Suffix text(Optional)'> </p>
	</div>

	<div class="" id="pricing_wraper" style="margin-top: 25px;">
		<?php
		if ( empty( $rules ) ) {
			$rules = array(
				0 => array(
					'pricing_type'  => '',
					'from_date'     => '',
					'from_week_day' => '',
					'from_month'    => '',
					'from_time'     => '',
					'to_week_day'   => '',
					'to_month'      => '',
					'to_date'       => '',
					'to_time'       => '',
					'base_cost'     => '',
					'cost_per_unit' => '',
				),
			);
		}
		?>
		<br>
		<br>
		<h2 style="margin-bottom:5px;"><b><?php echo __( 'Provide Discounts or Special Prices by creating cost rules.', 'bookings-and-appointments-for-woocommerce' ); ?></b></h2>
		<table class="ph_pricing_table ph_rule_table wc_input_table sortable" cellspacing="0" name="pricing_rules_table">
			<thead>
				<tr>
					<th class="sort">&nbsp;</th>
					<th><?php _e( 'Rule Type', 'bookings-and-appointments-for-woocommerce' ); ?></th>
					<th><?php _e( 'From', 'bookings-and-appointments-for-woocommerce' ); ?></th>
					<th><?php _e( 'To', 'bookings-and-appointments-for-woocommerce' ); ?></th>
					<th colspan="2"><?php _e( 'Base Cost', 'bookings-and-appointments-for-woocommerce' ); ?></th>
					<th colspan="2"><?php _e( 'Cost per block', 'bookings-and-appointments-for-woocommerce' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody class="rules ui-sortable">
				<?php
				foreach ( $rules as $key => $rule ) {
					if ( ! empty( $rule ) ) {
						include 'html-ph-booking-product-admin-pricing-matrix-row.php';
					}
				}
				?>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="10"><a href="#" class="add_rule button">+ <?php _e( 'Add', 'bookings-and-appointments-for-woocommerce' ); ?></a></th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
<script type="text/javascript">
jQuery(function($) {

	jQuery(document).on('change', '.ph_booking_pricing_rule_type', function(){	
		var value = $(this).val();
		var row   = $(this).closest('tr');

		$(row).css('border','1px solid #f00');

		$(row).find('.pricing_by_slot, .pricing_by_week_days, .pricing_by_month, .pricing_by_date, .pricing_by_time').hide();

		if ( value == 'slot_based' ) {
			$(row).find('.pricing_by_slot').show();
		}
		if ( value == 'custom' ) {
			$(row).find('.pricing_by_date').show();
		}
		if ( value == 'months' ) {
			$(row).find('.pricing_by_month').show();
		}
		if ( value == 'days' || value == 'strict_days') {
			$(row).find('.pricing_by_week_days').show();
		}
		if ( value.match( "^time" ) ) {
			$(row).find('.pricing_by_time').show();
		}
	});

	jQuery( ".pricing_date_picker" ).datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});

	jQuery('.ph_pricing_table').on( 'click', '.remove', function(){
		$(this).closest('tr').remove();
	})

	jQuery('.ph_pricing_table').on( 'click', '.add_rule', function(){
		$(".pricing_date_picker").datepicker("destroy");
		jQuery(`
		<?php
		$rule = '';
		require 'html-ph-booking-product-admin-pricing-matrix-row.php';
		?>
		`).appendTo('#pricing_wraper table tbody');
		jQuery( ".pricing_date_picker" ).datepicker({
			showOtherMonths: true,
			selectOtherMonths: true,
			dateFormat: 'yy-mm-dd',
		});
		return false;
	});

	$('.display-field-width').css('float', 'left');
	
});
</script>