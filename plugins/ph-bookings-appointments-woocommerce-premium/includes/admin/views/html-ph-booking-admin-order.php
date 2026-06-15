<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wrap woocommerce">
	<h2><?php _e( 'Add Booking', 'bookings-and-appointments-for-woocommerce' ); ?></h2>

	<form method="POST" >
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label><?php _e( 'User', 'bookings-and-appointments-for-woocommerce' ); ?></label>
					</th>
					<td>
						<select name="ph_customer_id" id="customer_id" class="wc-enhanced-select ph_filter_product_ids" style="width:300px;">
							<!-- allow guest bookings -->
							<option value="0"><?php echo __('Guest','bookings-and-appointments-for-woocommerce');?></option>
						<?php
														
						$blogusers = get_users( array( 'fields' => array( 'display_name','ID', 'user_email' ) ));
						foreach ( $blogusers as $user ) {
						    echo '<option value="' . esc_html( $user->ID ) . '">'.esc_html( $user->display_name ) .' ( '.$user->user_email.' )</option>';
						}
								
								?>
						</select>	
						
					</td>

				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="ph_filter_product_ids"><?php _e( 'Bookable Product', 'bookings-and-appointments-for-woocommerce' ); ?></label>
					</th>
					<td>
						<div class="ph-filter-item">
							<?php
							$args = array(
								'limit'	=>	-1,
								'type' => 'phive_booking',
								'orderby' => 'name',
								'order' => 'ASC'
							);
							$products = wc_get_products( $args );
							?>
							<select name="ph_filter_product_ids" class="wc-enhanced-select ph_filter_product_ids" style="width:300px;">
							<?php
								foreach ( $products as $key => $product ) {
									if( $product instanceof WC_Product_phive_booking ){
										echo '<option ' . $product->get_id() . ' value="' . $product->get_id() .'">' . $product->get_name() .'</option>';
									}
								}?>
							</select>

						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="create_order"><?php _e( 'Create Order', 'bookings-and-appointments-for-woocommerce' ); ?></label>
					</th>
					<td>
						<p>
							<label>
								<input type="radio" name="ph_booking_order" id="ph_booking_order_new" value="new" class="checkbox" />
								<?php _e( 'Create a new  order.', 'bookings-and-appointments-for-woocommerce' ); ?>
							</label>
						</p>
						<p>
							<label>
								<input type="radio" name="ph_booking_order" id="ph_booking_order_existing" value="existing" class="checkbox" />
								<?php _e( 'Assign this booking to an existing order with this ID:', 'bookings-and-appointments-for-woocommerce' ); ?>
								
									<input type="number" name="ph_booking_order_id" id="ph_booking_order_id" value="" class="text" size="10" />

							</label>
						</p>
						
					</td>
				</tr>
				<?php do_action( 'woocommerce_bookings_after_create_booking_page' ); ?>
				<tr valign="top">
					<th scope="row">&nbsp;</th>
					<td>
						<input type="hidden" name="next_step" value="2">
						<input type="submit" name="ph_product_submit" id="ph_create_booking" class="button-primary" value="<?php _e( 'Next', 'bookings-and-appointments-for-woocommerce' ); ?>" />
						
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<?php
?>

<script>
	jQuery(document).ready(function() 
	{
		jQuery('.ph_filter_product_ids').select2();
	});
</script>
