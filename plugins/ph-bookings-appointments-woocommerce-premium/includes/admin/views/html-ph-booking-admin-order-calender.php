<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$product = wc_get_product($_REQUEST['ph_filter_product_ids']);
$id = $product->get_id();
global $post;
$post = get_post($id, OBJECT);
setup_postdata($post);

$this->interval = get_post_meta($id, "_phive_book_interval", 1);
$this->interval_period = get_post_meta($id, "_phive_book_interval_period", 1);
$this->shop_opening_time = get_post_meta($id, "_phive_book_working_hour_start", 1);
$this->shop_closing_time = get_post_meta($id, "_phive_book_working_hour_end", 1);
$across_the_day = get_post_meta($id, '_phive_enable_across_the_day', 1);
$auto_select_min_booking = get_post_meta($id, '_phive_auto_select_min_booking', 1);
$auto_select_min_booking = (empty($auto_select_min_booking) || $auto_select_min_booking == 'yes') ? 'yes' : 'no';
$end_time_display = get_post_meta($id, '_phive_enable_end_time_display', true);
$end_time_display = (!empty($end_time_display) && $end_time_display == 'yes') ? 'yes' : 'no';


?>
<div class="">
	<h2><?php _e('Add Booking', 'bookings-and-appointments-for-woocommerce'); ?></h2>

	<form method="POST" enctype="multipart/form-data">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" class="ph-add-booking-page-th-hide">
						<label><?php _e('Booking Data', 'bookings-and-appointments-for-woocommerce'); ?></label>
					</th>
					<td>
						<div class="wc-bookings-booking-form">

							<input type="hidden" name="phive_booked_price" id="phive_booked_price"
								class="phive_booked_price" value='' />
							<input type="hidden" id="phive_product_id" name="phive_product_id"
								value='<?php echo $product->get_id(); ?>' />
							<input type="hidden" id="phive_customer_id" name="phive_customer_id"
								value='<?php echo $_POST['ph_customer_id']; ?>' />

							<input type="hidden" name="phive_book_from_date" id="" class="ph-date-from ph-datepicker"
								value="">
							<input type="hidden" name="phive_book_to_date" id="" class="ph-datepicker ph-date-to"
								value="">

							<input type="hidden" id="plugin_dir_url"
								value="<?php echo plugins_url('', dirname(dirname(dirname(__FILE__)))); ?>">

							<!-- for addon -->
							<input type="hidden" class="display_time_to" name="phive_display_time_to" value="">
							<input type="hidden" class="display_time_from" name="phive_display_time_from" value="">
							<input type="hidden" class="time_offset" value="<?php echo get_option('gmt_offset') ?>">
							<input type="hidden" class="from_text" value="">
							<input type="hidden" class="to_text" value="">
							<input type="hidden" class="book_interval_period"
								value="<?php echo $this->interval_period ?>">
							<input type="hidden" class="book_interval" value="<?php echo $this->interval ?>">
							<input type="hidden" id="ph_booking_wp_date_format"
								value="<?php echo get_option('date_format') ?>">
							<input type="hidden" id="ph_booking_wp_time_format"
								value="<?php echo get_option('time_format') ?>">
							<input type="hidden" id="phive_booking_maximum_number_of_allowed_participant"
								value="<?php echo get_post_meta($id, '_phive_booking_maximum_number_of_allowed_participant', 1); ?>">
							<input type="hidden" id="phive_booking_minimum_number_of_required_participant"
								value="<?php echo get_post_meta($id, '_phive_booking_minimum_number_of_required_participant', 1); ?>">
							<input type="hidden" class="shop_opening_time"
								value="<?php echo $this->shop_opening_time ?>">
							<input type="hidden" class="shop_closing_time"
								value="<?php echo $this->shop_closing_time ?>">
							<input type="hidden" class="across_the_day_booking" value="<?php echo $across_the_day; ?>">
							<input type="hidden" name="ph_booking_addon_data" class="ph_booking_addon_data" value="">
							<input type="hidden" name="ph_booking_product_addon_data"
								class="ph_booking_product_addon_data" value="">
							<input type="hidden" id="auto_select_min_block" name="auto_select_min_block"
								class="auto_select_min_block" value="<?php echo $auto_select_min_booking; ?>">
							<input type="hidden" id="end_time_display" name="end_time_display" class="end_time_display"
								value="<?php echo $end_time_display; ?>">
							<input type="hidden" id="reset_action" name="reset_action" class="reset_action" value="1">
							<input type="hidden" id="ph_prev_day_times" class="ph_prev_day_times" value="">
							<input type="hidden" id="ph_selected_blocks" class="ph_selected_blocks" value="">
							<!-- for addon end -->

							<div style="width:80%">
								<?php
								$plugin_url = (plugin_dir_path(dirname(dirname(__FILE__))));

								include_once($plugin_url . '/frondend/class-ph-booking-calendar-strategy.php');
								include_once($plugin_url . '/class-ph-booking-ajax-interface.php');

								$booking_form = new phive_booking_calendar_strategy($product->get_id());
								$booking_form->output_calender($product);

								?>
							</div>
							<div class="wc-bookings-booking-cost" style="display:none">
							</div>
						</div>
						<div id="customer_details">
							<div id="group-payment-options">
								<h4>Would you like to split the payment between multiple Parties?</h4>
								<p style="margin-bottom:10px">You can choose to divide the total amount equally among parties</p>
								<section class="d-flex">
									<label>
										<input type="radio" name="group_payment_mode" value="full" checked="">
										No, Pay in Full
									</label>
									<label>
										<input type="radio" name="group_payment_mode" value="group">
										Split equally among the parties
									</label>
								</section>
								<div class="count-member" style="display: none;">
									<label for="group_total_payers">Parties involved</label>
									<p class="form-row form-row-wide update_totals_on_change thwcfd-required thwcfd-field-wrapper  validate-required"
										style="margin: 0;">
										<input type="number" id="group_total_payers" name="group_total_payers" min="2"
											max="10" value="2" class="input-text" fdprocessedid="52ilg">
									</p>
								</div>

							</div>
							<div id="group-payment-fields" style="margin-top: 15px; display: none;">
								<div id="group-members" style="margin-top:10px;"></div>
							</div>
						</div>
						<?php
						do_action('woocommerce_before_add_to_cart_button');
						do_action('woocommerce_before_add_to_cart_quantity');


						/**
						 * @since 3.0.0.
						 */
						do_action('woocommerce_after_add_to_cart_quantity');
						?>
					</td>
				</tr>
				<tr class="ph-add-booking-page-send-email-tr">
					<th class="ph-add-booking-page-th-hide"></th>
					<td><input type="checkbox" id="ph_add_booking_send_payment_email" name="send_payment_email"
							value="1"><label><?php _e('Send payment link email to customer.', 'bookings-and-appointments-for-woocommerce'); ?></label>
					</td>
				</tr>
				<?php if ($_POST['ph_customer_id'] == '0') { ?>
					<tr class="ph_guest_address_panel">
						<th class="ph-add-booking-page-th-hide"></th>
						<td>
							<label
								for="ph_guest_email_id"><?php _e('Please enter Email id:', 'bookings-and-appointments-for-woocommerce') ?></label>
							<input name="ph_guest_email_id" id='ph_guest_email_id' type="email" style="width:30%;">
						</td>
					</tr>
					<!-- Guest first name -->
					<tr class="ph_guest_address_panel">
						<th class="ph-add-booking-page-th-hide"></th>
						<td>
							<label
								for="ph_guest_first_name"><?php _e('First name:', 'bookings-and-appointments-for-woocommerce') ?></label>
							<input name="ph_guest_first_name" id='ph_guest_first_name' type="text" style="width:30%;">
						</td>
					</tr>
					<!-- Guest last name -->
					<tr class="ph_guest_address_panel">
						<th class="ph-add-booking-page-th-hide"></th>
						<td>
							<label
								for="ph_guest_last_name"><?php _e('Last name:', 'bookings-and-appointments-for-woocommerce') ?></label>
							<input name="ph_guest_last_name" id='ph_guest_last_name' type="text" style="width:30%;">
						</td>
					</tr>
				<?php } ?>
				<tr valign="top">
					<th scope="row" class="ph-add-booking-page-th-hide">&nbsp;</th>
					<td>
						<input type="submit" name="ph_calendar_submit" class="button-primary single_add_to_cart_button"
							value="<?php _e('Add Booking', 'bookings-and-appointments-for-woocommerce'); ?>" />
						<input type="hidden" name="ph_customer_id"
							value="<?php echo esc_attr($_REQUEST['ph_customer_id']); ?>" />
						<input type="hidden" name="ph_bookable_product_id"
							value="<?php echo esc_attr($_REQUEST['ph_booking_order_id']); ?>" />
						<input type="hidden" name="ph_add-to-cart"
							value="<?php echo esc_attr($_REQUEST['ph_filter_product_ids']); ?>" />
						<input type="hidden" name="ph_booking_order"
							value="<?php echo esc_attr($_REQUEST['ph_booking_order']); ?>" />
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<?php
wp_reset_postdata();
?>