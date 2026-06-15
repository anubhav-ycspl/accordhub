<?php

if (! defined('ABSPATH')) {
	exit;
}

global $product;
?>
<script>
	// To Reload the page if customer is going to any product page by clicking back button
	window.addEventListener("pageshow", function(event) {
		var historyTraversal = event.persisted || (typeof window.performance != "undefined" && window.performance.navigation.type === 2);
		if (historyTraversal) {
			// Handle page restore.
			jQuery('.reset_action').trigger('change');
		}
	});
</script>
<?php
$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id($product->get_id());
$default_product = wc_get_product($product_id);
if (! $default_product->is_purchasable()) {
	return;
}
echo wc_get_stock_html($default_product);

if ($default_product->is_in_stock()) : ?>

	<?php
	do_action('woocommerce_before_add_to_cart_form');
	$this->interval					= get_post_meta($product_id, "_phive_book_interval", 1);
	$this->interval_period			= get_post_meta($product_id, "_phive_book_interval_period", 1);
	$this->shop_opening_time		= get_post_meta($product_id, "_phive_book_working_hour_start", 1);
	$this->shop_closing_time		= get_post_meta($product_id, "_phive_book_working_hour_end", 1);
	$across_the_day					= get_post_meta($product_id, '_phive_enable_across_the_day', 1);
	$auto_select_min_booking		= get_post_meta($product_id, '_phive_auto_select_min_booking', 1);
	$auto_select_min_booking 		= (empty($auto_select_min_booking) || $auto_select_min_booking == 'yes') ? 'yes' : 'no';
	$end_time_display				= get_post_meta($product_id, '_phive_enable_end_time_display', true);
	$end_time_display 				= (!empty($end_time_display) && $end_time_display == 'yes') ? 'yes' : 'no';


	$ph_calendar_color 			= get_option('ph_booking_settings_calendar_color');
	$ph_calendar_design			= (isset($ph_calendar_color['ph_calendar_design']) && !empty($ph_calendar_color['ph_calendar_design'])) ? $ph_calendar_color['ph_calendar_design'] : 1; // default legacy design will display

	// 125852 - timezone conversion status to be accessed later in js code
	$display_settings			= get_option('ph_bookings_display_settigns');
	$time_zone_conversion_enable = isset($display_settings['time_zone_conversion_enable']) ? $display_settings['time_zone_conversion_enable'] : 'no';
	$ph_search_product_view 	= isset($display_settings['ph_search_product_view']) ? $display_settings['ph_search_product_view'] : 'calendar';
	$ph_search_product_view 	= isset($display_settings['ph_search_product_view']) ? $display_settings['ph_search_product_view'] : 'calendar';

	?>
	<!-- 108065 - Wrong months in Firefox -->
	<form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', get_permalink())); ?>" method="post" enctype='multipart/form-data' autocomplete="off">
		<div class="booking-wraper">
			<div>
				<input type="hidden" class="phive_booked_price" name="phive_booked_price" id="phive_booked_price" value='' />
				<input type="hidden" id="phive_product_id" value='<?php echo $product_id; ?>' />

				<input type="hidden" name="phive_book_from_date" class="ph-date-from ph-datepicker" value="">
				<input type="hidden" name="phive_book_to_date" class="ph-datepicker ph-date-to" value="">
				<input type="hidden" name="phive_book_assets" class="phive_book_assets" value="">

				<input type="hidden" id="plugin_dir_url" value="<?php echo plugins_url('', dirname(dirname(__FILE__))); ?>">
				<!-- for addon -->
				<input type="hidden" class="display_time_to" name="phive_display_time_to" value="">
				<input type="hidden" class="display_time_from" name="phive_display_time_from" value="">
				<input type="hidden" class="time_offset" value="<?php echo get_option('gmt_offset') ?>">
				<input type="hidden" class="from_text" value="">
				<input type="hidden" class="to_text" value="">
				<input type="hidden" class="book_interval_period" value="<?php echo $this->interval_period ?>">
				<input type="hidden" class="book_interval" value="<?php echo $this->interval ?>">
				<input type="hidden" id="ph_booking_wp_date_format" value="<?php echo get_option('date_format') ?>">
				<input type="hidden" id="ph_booking_wp_time_format" value="<?php echo get_option('time_format') ?>">
				<input type="hidden" id="phive_booking_maximum_number_of_allowed_participant" value="<?php echo get_post_meta($default_product->get_id(), '_phive_booking_maximum_number_of_allowed_participant', 1); ?>">
				<input type="hidden" id="phive_booking_minimum_number_of_required_participant" value="<?php echo get_post_meta($default_product->get_id(), '_phive_booking_minimum_number_of_required_participant', 1); ?>">
				<input type="hidden" class="shop_opening_time" value="<?php echo $this->shop_opening_time ?>">
				<input type="hidden" class="shop_closing_time" value="<?php echo $this->shop_closing_time ?>">
				<input type="hidden" class="across_the_day_booking" value="<?php echo $across_the_day; ?>">
				<input type="hidden" name="ph_booking_addon_data" class="ph_booking_addon_data" value="">
				<input type="hidden" name="ph_booking_product_addon_data" class="ph_booking_product_addon_data" value="">
				<input type="hidden" id="auto_select_min_block" name="auto_select_min_block" class="auto_select_min_block" value="<?php echo $auto_select_min_booking; ?>">
				<input type="hidden" id="end_time_display" name="end_time_display" class="end_time_display" value="<?php echo $end_time_display; ?>">
				<input type="hidden" id="calendar_design" name="calendar_design" class="calendar_design" value="<?php echo $ph_calendar_design; ?>">
				<input type="hidden" class="ph_time_zone_conversion_active" value="<?php echo $time_zone_conversion_enable; ?>">
				<input type="hidden" id="reset_action" name="reset_action" class="reset_action" value="1">
				<input type="hidden" id="ph_prev_day_times" class="ph_prev_day_times" value="">
				<input type="hidden" id="ph_selected_blocks" class="ph_selected_blocks" value="">
				<input type="hidden" name="ph_search_product_view" id="ph_search_product_view" value="<?php echo $ph_search_product_view ?>">
				<!-- <p><?php //_e('Pick a booking period','ph-booking')?></p> -->
			</div>
			<div>

				<?php
				$invoker = isset($_GET['invoker']) ? $_GET['invoker'] : '';
				if ($invoker != "phbookingsearch") {
					
					include_once('class-ph-booking-calendar-strategy.php');
					$callender = new phive_booking_calendar_strategy($product_id);
					$callender->output_calender();
			
				} else {
					if (WC()->session->get('ph_bookings_search_params') && $ph_search_product_view === 'summary') {
						include_once('class-ph-booking-product-details.php');
					} else if ($invoker === "phbookingsearch") 
					{
						include_once('class-ph-booking-calendar-strategy.php');
						$callender = new phive_booking_calendar_strategy($product_id);
						$callender->output_calender();
					}
				}
				?>
			</div>
		</div>

		<?php
		do_action('woocommerce_before_add_to_cart_button');
		do_action('woocommerce_before_add_to_cart_quantity');
		/*woocommerce_quantity_input( array(
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $default_product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $default_product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $default_product->get_min_purchase_quantity(),
			) );*/

		/**
		 * @since 3.0.0.
		 */
		do_action('woocommerce_after_add_to_cart_quantity');
		?>
		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
		<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>" class="single_add_to_cart_button button alt ph_book_now_button <?php echo $ph_search_product_view === 'calendar' ? 'disabled' : ''; ?>">
			<?php echo esc_html($default_product->single_add_to_cart_text()); ?>
		</button>
		<?php
		/**
		 * @since 2.1.0.
		 */
		do_action('woocommerce_after_add_to_cart_button');
		?>
	</form>

	<?php do_action('woocommerce_after_add_to_cart_form'); ?>

<?php endif; ?>