<div class="tablenav nav-<?php echo esc_attr( $which ); ?>" >

	<div class="ph-list-bulkaction-wraper">
		<?php $this->bulk_actions( $which );?>
	</div>

	<div class="ph-list-filter-wraper">



		<div class="ph-filter-item">
			<select class="ph_booking_status" id="ph_booking_status">
				<option value=""><?php esc_html_e( 'All', 'bookings-and-appointments-for-woocommerce' ); ?></option>
				<?php 
					$all_status = ph_get_booking_statuses();
					foreach ($all_status as $status_key => $value) {
						echo '<option '.selected( $ph_booking_status, $status_key ).' value="'.$status_key.'">'.$value.'</option>';
					}
				?>
			</select>
		</div>

		<div class="ph-filter-item">
			<?php
			$args = array(
				'limit'	=>	-1,
			    'type' => 'phive_booking',
			);
			$products = wc_get_products( $args );
			?>
			<select name="ph_filter_product_ids" class="wc-enhanced-select ph_filter_product_ids"><?php
				echo '<option value="">'.__( 'Choose product', 'bookings-and-appointments-for-woocommerce' ).'</option>';
				foreach ( $products as $key => $product ) {
					if( !empty($product) ){
						echo '<option ' . selected( $ph_filter_product_ids, $product->get_id() ) . ' value="' . $product->get_id() .'">' . $product->get_name() .'</option>';
					}
				}?>
			</select>

		</div>


		<div class="ph-filter-item ph_filter_name">
			<?php _e('Bookings start between','bookings-and-appointments-for-woocommerce');?>:
		</div>
		<div class="ph-filter-item">
			<input type="text" class="ph_filter_from ph_from-<?php echo esc_attr( $which ); ?>" placeholder="<?php _e('From', 'bookings-and-appointments-for-woocommerce')?>" value="<?php echo $ph_filter_from;?>">
		</div>

		<!--#113580 -filter the bookings based on date & time-->
		<?php do_action('ph_action_start_from_time',$which);?>

		<div class="ph-filter-item">
			<input type="text" class="ph_filter_to ph_to-<?php echo esc_attr( $which ); ?>" placeholder="<?php _e('To', 'bookings-and-appointments-for-woocommerce')?>" value="<?php echo $ph_filter_to;?>">
		</div>

		<!--#113580 -filter the bookings based on date & time-->
		<?php do_action('ph_action_start_to_time',$which);?>

		<div class="ph-filter-item ph_filter_name" id="ph_filter_booking_end_between-<?php echo esc_attr( $which ); ?>">
			<?php _e('Bookings end between','bookings-and-appointments-for-woocommerce');?>:
		</div>
		<div class="ph-filter-item" id="ph_additional_filters_from_date-<?php echo esc_attr( $which ); ?>">
			<input type="text" class="ph_filter_end_from ph_end_from-<?php echo esc_attr( $which ); ?>" placeholder="<?php _e('From', 'bookings-and-appointments-for-woocommerce')?>" value="<?php echo $ph_filter_end_from;?>">
		</div>

		<!--#113580 -filter the bookings  based on date & time-->
		<?php do_action('ph_action_end_from_time',$which);?>

		<div class="ph-filter-item" id="ph_additional_filters_to_date-<?php echo esc_attr( $which ); ?>">
			<input type="text" class="ph_filter_end_to ph_end_to-<?php echo esc_attr( $which ); ?>" placeholder="<?php _e('To', 'bookings-and-appointments-for-woocommerce')?>" value="<?php echo $ph_filter_end_to;?>">
		</div>

		<!--#113580 -filter the bookings  based on date & time-->
		<?php do_action('ph_action_end_to_time',$which);?>


		<div class="ph-filter-item">
			<input type="button" class="button btn_filter" value="<?php _e('Filter','bookings-and-appointments-for-woocommerce');?>">
		</div>





		<br class="clear">

	</div>

	<div class="ph-list-pagination-wraper">
		<?php $this->pagination( $which );?>
	</div>

</div>

<script>
	jQuery(document).ready(function() 
	{
		jQuery('.ph_filter_product_ids').select2();
	});
</script>