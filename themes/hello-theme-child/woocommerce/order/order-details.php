<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

// phpcs:disable WooCommerce.Commenting.CommentHooks.MissingHookComment

defined('ABSPATH') || exit;
// error_reporting(E_ALL); // Report all PHP errors
// ini_set('display_errors', 1);
$order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if (!$order) {
	return;
}
$orignal_order_id = $order_id;
$org_order = $order;
$manual_payment = $order->get_meta('_phive_manual_payment_status');
$child = false;
$split = false;
if ($order->get_meta('group_parent_order')) {

	$order = wc_get_order($order->get_meta('group_parent_order'));
	$order_id = $order->get_id();
	$child = true;
	$tt_label = 'Your Split Share';
	$ss_label = 'Total Amount';
}
if ($order->get_meta('group_payment_mode') == 'group') {
	$split = true;
}
$booking = get_booking_details($order);
$current_user_id = get_current_user_id();

$discount_applied_25_per = $booking['discount_applied_25_per'];

// echo "<pre>";
// print_r($booking);
// echo "</pre>";



$order_booking_to = '';
$order_booking_from = '';
$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_purchase_note = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
$downloads = $order->get_downloadable_items();
$actions = array_filter(
	wc_get_account_orders_actions($order),
	function ($key) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);

$options = get_option('wc_booking_cancel_options', []);
$info = $options['info_message'] . ' <a style="color:#fff;text-decoration:underline" href="' . home_url("cancellation-and-refund-policy") . '" target="_blank" rel="noopener noreferrer">View Cancellation Policy</a>' ?? '';
$btn_label = $options['cancel_label'] ?? '';

$now = current_time('timestamp');

foreach ($order_items as $item_id => $item) {
	$product = $item->get_product(); // WC_Product object
	$product_name = $item->get_name();
	if ($product && $product->get_type() === 'phive_booking') {
		$total_participants = $item->get_meta('Total Participants', true);
		if ($total_participants == '') {
			$total_participants = $order->get_meta('Total Participants', true);
		}
		$from_date = $item->get_meta('Booked From'); // meta key from plugin
		$to_date = $item->get_meta('Booked To');   // meta key from plugin
		if ($from_date && $to_date) {
			$from_ts = strtotime($from_date);
			$to_ts = strtotime($to_date);

			// Track earliest start and latest end for this order
			if (!$order_booking_from || $from_ts < $order_booking_from) {
				$order_booking_from = $from_ts;
			}
			if (!$order_booking_to || $to_ts > $order_booking_to) {
				$order_booking_to = $to_ts;
			}
		}
	}
}

if (!empty($order_booking_from)) {
	$hours_distance = ($order_booking_from - $now) / 3600;
	if ($hours_distance >= 72) {
		$msg = 'If you cancel this booking, 50% cancelation charges will apply.';
		$msg2 = 'Please note that a 50% cancellation charge applies to this booking, meaning only half of your total payment is refundable.';
	} else {
		$msg = 'If you cancel this booking, 100% cancelation charges will apply therefor you will not receive a refund.';
		$msg2 = 'If you cancel this booking, a 100% cancellation fee applies, meaning no refund will be issued.';
	}
}

// We make sure the order belongs to the user. This will also be true if the user is a guest, and the order belongs to a guest (userID === 0).
$show_customer_details = $order->get_user_id() === get_current_user_id();

if ($show_downloads) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads' => $downloads,
			'show_title' => true,
		)
	);
}
$addons_total = 0;

foreach ($order->get_items() as $cart_item) {
	$product_id = $cart_item['product_id'];

	// Get all categories for this product
	$terms = get_the_terms($product_id, 'product_cat');

	if ($terms && !is_wp_error($terms)) {
		foreach ($terms as $term) {
			// Check if this term is "addons" or a child of "addons"
			$addons_term = get_term_by('slug', 'addons', 'product_cat');
			if ($term->slug === 'addons' || term_is_ancestor_of($addons_term, $term, 'product_cat')) {
				$addons_total += $cart_item['line_subtotal']; // safer than price*qty
				break; // no need to check more categories for this product
			}
		}
	}
}
$addons_total_formatted = wc_price($addons_total);
$first_name = $order->get_billing_first_name();
$last_name = $order->get_billing_last_name();

$customer_name = $first_name . ' ' . $last_name;

?>
<style>
</style>
<section class="woocommerce-order-details">
	<?php do_action('woocommerce_order_details_before_order_table', $order); ?>
	<div class="d-flex">
		<h2 class="woocommerce-order-details__title"><?php esc_html_e('Booking Details', 'woocommerce'); ?>
		</h2>
	</div>
	<?php if ('yes' === $order->get_meta('disable_customer_emails') && $order->get_meta('_wc_order_attribution_source_type') === 'admin') {
		echo '<p class="admin-booking-note" style="margin-bottom:15px"><strong>Note:</strong> This booking is initiated by Accordhub Admin on your behalf. Please contact Admin to manage the booking.</p>';
	} ?>
	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name">
					<?php esc_html_e('Booking Reference No', 'woocommerce'); ?>
				</th>
				<th class="woocommerce-table__product-table product-total order-id"><?php echo $order_id; ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="woocommerce-table__product-name product-name" scope="row">Booking Confirmation Date & Time
				</td>
				<td class="woocommerce-table__product-table product-total">
					<?php echo wc_format_datetime($order->get_date_created(), get_option('date_format') . ' ' . get_option('time_format')); ?>
				</td>
			</tr>
			<tr>
				<td class="woocommerce-table__product-name product-name" scope="row">Booking Status</td>
				<td class="woocommerce-table__product-table product-total">
					<?php echo get_booking_status($orignal_order_id); ?>
				</td>
			</tr>
			<?php if (get_booking_status($orignal_order_id) == 'Cancelled' && $order->get_meta('_cancelled_by_user_name') !== '' && $order->get_meta('_cancelled_by_user_name') === 'Admin') { ?>
				<tr>
					<td class="woocommerce-table__product-name product-name" scope="row">Cancelled by</td>
					<td class="woocommerce-table__product-table product-total">
						<?php echo $order->get_meta('_cancelled_by_user_name'); ?>
					</td>
				</tr>
			<?php } ?>
			<?php if (get_booking_status($orignal_order_id) == 'Cancelled' && $order->get_meta('phive_cancellation_requested_at') !== '') { ?>
				<tr>
					<td class="woocommerce-table__product-name product-name" scope="row">Date of cancellation</td>
					<td class="woocommerce-table__product-table product-total">
						<?php echo date('F j, Y g:i a', $order->get_meta('phive_cancellation_requested_at')); ?>
					</td>
				</tr>
			<?php } ?>
			<?php if (get_booking_status($orignal_order_id) == 'Cancelled' && $order->get_meta('_cancellation_reason') !== '' && $order->get_meta('_cancelled_by_user_name') === 'Admin') { ?>
				<tr>
					<td class="woocommerce-table__product-name product-name" scope="row">Reason of Cancellation</td>
					<td class="woocommerce-table__product-table product-total">
						<?php echo $order->get_meta('_cancellation_reason'); ?>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="woocommerce-table__product-name product-name" scope="row">Payment Type</td>
				<td class="woocommerce-table__product-table product-total">
					<?php
					if ($booking['payers'] > 1) {
						echo "Split Payment";
					} else {
						echo "Full Payment";
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="woocommerce-table__product-name product-name" scope="row">Payment Status</td>
				<td class="woocommerce-table__product-table product-total">
					<?php echo verify_razorpay_payment($orignal_order_id); ?>

					<?php if ($manual_payment === 'refunded') { ?>
						<span class="tooltip_box">
							<span class="tooltip_i">i</span>
							<span class="tooltip_box_hover" style="display:none;">
								Refunded as per the Accordhub Cancellation & Refund Policy. <a
									style="color:#fff;text-decoration:underline"
									href="<?php echo home_url('cancellation-and-refund-policy') ?>" target="_blank"
									rel="noopener noreferrer">View Policy</a>.
							</span>
						</span>
						<br><span style="color:#69727d;font-weight: 400;">Your refund is initiated and shall be refunded to
							you in 14 days.</span>
					<?php } ?>
				</td>
			</tr>
			<?php if (get_payment_method_name($order_id) !== '') { ?>
				<!-- <tr>
					<td class="woocommerce-table__product-name product-name" scope="row">Payment Method</td>
					<td class="woocommerce-table__product-table product-total" style="text-transform: capitalize;">
						<?php //echo get_payment_method_name($order_id); ?>
					</td>
				</tr> -->
			<?php } ?>

		</tbody>
		<?php
		if (!empty($actions) || ($now < $order_booking_to) && $order->get_status() !== 'cancelled' && $order->get_status() !== 'refund-processed'):
			//echo $child;
			if (!$child) {

				?>
				<tfoot>
					<tr>
						<!-- <th class="order-actions--heading"><?php // esc_html_e('Actions', 'woocommerce'); ?></th> -->
						<td colspan="2"><?php
						$wp_button_class = wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

						if ($now < $order_booking_to && $now > $order_booking_from) {
							if (!$order->get_meta('meeting_addons_billed') && $order->is_paid() && get_booking_status($orignal_order_id) == 'Confirmed') {
								echo '<button id="addition-button" class="button alt order-actions-button">Select Add-Ons</button>';
							}

						} elseif ($now < $order_booking_to && $now < $order_booking_from) {
							if (!$order->get_meta('meeting_addons_billed') && $order->is_paid() && get_booking_status($orignal_order_id) == 'Confirmed') {
								echo '<button id="addition-button" class="button alt order-actions-button">Select Add-Ons</button>';
							}
							foreach ($actions as $key => $action) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								if (empty($action['aria-label'])) {
									// Generate the aria-label based on the action name.
									/* translators: %1$s Action name, %2$s Order number. */
									$action_aria_label = sprintf(__('%1$s order number %2$s', 'woocommerce'), $action['name'], $order->get_order_number());
								} else {
									$action_aria_label = $action['aria-label'];
								}
								$action_name = str_replace('Cancel', 'Cancel Booking', $action['name']);
								$action_name = str_replace('Pay', 'Pay Now', $action_name); // use $action_name here
				
								if ($action_name == 'Cancel Booking' && $info !== '') {
									echo '<a href="' . esc_url($action['url']) . '" title_hover="' . $msg2 . '" class="woocommerce-button' . esc_attr($wp_button_class) . ' button   order-actions-button cancel_btn" id="frontend_cancel_booking_trigger" aria-label="' . esc_attr($action_aria_label) . '" data-booked-from="' . esc_attr($order_booking_from) . '">' . esc_html($action_name) . '</a>';
									echo '<span class="tooltip_box">
												<span class="tooltip_i">i</span>
												<span class="tooltip_box_hover" style="display:none;">' . $info . ' </span>
											</span>';
									echo '<div id="phive_cancel_modal" class="phive-modal-overlay" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
											<div class="phive-modal-box" style="background:#fff; padding:20px; border-radius:4px; width:380px;">
												<div class="phive-modal-header" style="font-size:18px; font-weight:600; margin-bottom:15px;">
													Are you sure you want to cancel this booking?
												</div>
												<div class="phive-modal-body">
													<p style="color:#50575e;margin-bottom:10px;">' . esc_html($msg) . '</p>
													<p style="color:#50575e;margin-bottom:10px;">Please enter a reason for cancellation:</p>
													<textarea id="phive_cancel_reason_input" style="width:100%; height:80px;  resize:none;background-color: #02010100;border-color: #D4D7E3;border-width: 1px 1px 1px 1px;border-radius: 12px 12px 12px 12px;" required></textarea>
												</div>
												<div class="phive-modal-footer" style="display:flex; justify-content:flex-end; gap:10px;margin-top:15px;">
													<button type="button" class="button" id="phive_modal_close_btn">Go Back</button>
													<button type="button" class="button button-primary" id="phive_modal_confirm_btn">Confirm</button>
												</div>
											</div>
										</div>';
								} else {
									echo '<a href="' . esc_url($action['url']) . '" class="woocommerce-button' . esc_attr($wp_button_class) . ' button ' . sanitize_html_class($key) . ' order-actions-button " aria-label="' . esc_attr($action_aria_label) . '" data-booked-from="' . esc_attr($order_booking_from) . '">' . esc_html($action_name) . '</a>';
								}

								// if ($action_name == 'Cancel Booking' && $info !== '') {
								// 	echo '<span class="coupon-tooltip" title="' . $info . '">i</span>';
								// }
								unset($action_aria_label);
							}
						} elseif ($now > $order_booking_to) {
							echo "";
						} else {
							foreach ($actions as $key => $action) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								if (empty($action['aria-label'])) {
									$action_aria_label = sprintf(__('%1$s order number %2$s', 'woocommerce'), $action['name'], $order->get_order_number());
								} else {
									$action_aria_label = $action['aria-label'];
								}
								$action_name = str_replace('Cancel', $btn_label, $action['name']);
								$action_name = str_replace('Pay', 'Pay Now', $action_name); // use $action_name here
				
								echo '<a href="' . esc_url($action['url']) . '" class="woocommerce-button' . esc_attr($wp_button_class) . ' button  order-actions-button "  id="frontend_cancel_booking_trigger" aria-label="' . esc_attr($action_aria_label) . '">' . esc_html($action_name) . '</a>';
								echo '<div id="phive_cancel_modal" class="phive-modal-overlay" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
											<div class="phive-modal-box" style="background:#fff; padding:20px; border-radius:4px; width:380px;">
												<div class="phive-modal-header" style="font-size:18px; font-weight:600; margin-bottom:15px;">
													Cancel Booking
												</div>
												<div class="phive-modal-body">
													<p style="color:#50575e;margin-bottom:10px;">Please enter a reason for cancellation:</p>
													<textarea id="phive_cancel_reason_input" style="width:100%; height:80px; resize:none;background-color: #02010100;border-color: #D4D7E3;border-width: 1px 1px 1px 1px;border-radius: 12px 12px 12px 12px;" required></textarea>
												</div>
												<div class="phive-modal-footer" style="display:flex; justify-content:flex-end; gap:10px;margin-top:15px;">
													<button type="button" class="button" id="phive_modal_close_btn">Go Back</button>
													<button type="button" class="button button-primary" id="phive_modal_confirm_btn">Confirm</button>
												</div>
											</div>
										</div>';
								if ($action_name == 'Cancel Booking' && $info !== '') {
									echo '<span class="coupon-tooltip" title="' . $info . '">i</span>';
								}
								unset($action_aria_label);
							}
						}
						?></td>
					</tr>
				</tfoot>

				<?php

			} else { ?>
				<tfoot>
					<tr>
						<!-- <th class="order-actions--heading"><?php // esc_html_e('Actions', 'woocommerce'); ?></th> -->
						<td colspan="2">
							<?php if ($now < $order_booking_from) {
								// echo $org_order->get_customer_id();
								// echo $current_user_id;
								?>
								<?php if ($org_order->get_status() == 'pending' && $org_order->get_customer_id() == $current_user_id) { ?>
									<a href="<?php echo esc_url($org_order->get_checkout_payment_url()); ?>"
										class="woocommerce-button button order-actions-button">Pay
										Now</a>
								<?php } ?>
								<a href="" class="woocommerce-button button order-actions-button cancel_btn_child">Cancel
									Booking</a>
							<?php } ?>
						</td>
					</tr>
				</tfoot>
			<?php }
		endif ?>
	</table>
	<?php if ($child) {

		?>
		<div id="cancel-confirmation-popup" class="cancel-popup-overlay" style="display: none;">
			<div class="modal-content">
				<h5>Cancel Room Booking</h5>
				<p style="margin-bottom: 10px;">This booking was not intiatied by you. Please contact
					<?php echo $customer_name; ?> to cancel the booking.
				</p>
				<div class="popup-buttons">
					<button id="cancel-close" class="button secondary">OK</button>
				</div>
			</div>
		</div>
	<?php } ?>
	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details main">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name boldbrown">
					<?php esc_html_e('Particulars', 'woocommerce'); ?>
				</th>
				<th class="woocommerce-table__product-table product-total boldbrown">
					<span class="pr"><?php esc_html_e('Total', 'woocommerce'); ?></span>
				</th>
			</tr>
		</thead>

		<tbody>
			<tr class="woocommerce-table__line-item order_item  phive_booking">

				<td class="woocommerce-table__product-name product-name index1 boldbrown">
					Room Fee - <?php echo $booking['room']; ?>
					<ul class="wc-item-meta">
						<li style="text-transform: none;"><?php echo $booking['datetime']; ?></li>
					</ul>
				</td>
				<td class="woocommerce-table__product-total product-total index1 boldbrown">
					<span class="pr"><?php echo wc_price($booking['full_price']); ?></span>
				</td>

			</tr>
			<?php if ($booking['addon_count'] > 0): ?>
				<tr class="woocommerce-table__line-item order_item  addons">
					<td colspan="2" class="woocommerce-table__product-name product-name"
						style="border-bottom: 1px solid #dadada !important;">
						<div class="woocommerce-table__product-name product-name boldbrown">Add-Ons</div>
					</td>
				</tr>
				<?php foreach ($order->get_items() as $item):
					$prod = $item->get_product();
					$prod_name = $prod->get_name();
					$quantity = $item->get_quantity();
					$price = $prod->get_price();
					$tot = $price * $quantity;
					if ($prod->get_type() == 'phive_booking') {
						continue;
					}


					$product_id = $prod->get_id();
					$brand_image = "";
					$brands = get_the_terms($product_id, 'product_brand');
					$brand_image_id = get_term_meta($brands[0]->term_id, 'thumbnail_id', true);
					if ($brand_image_id) {
						$brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
					}
					?>
					<tr class="woocommerce-table__line-item order_item  simple">
						<td class="woocommerce-table__product-name product-name index2">

							<?php if (!empty($brand_image)) { ?>
								<img src="<?php echo esc_url($brand_image[0]); ?>" alt="<?php echo esc_attr($brand_title); ?>"
									width="35px" style="vertical-align: middle;margin-right: 3px;">
							<?php } ?>

							<?php echo $prod_name; ?> <strong
								class="product-quantity simple">×&nbsp;<?php echo $quantity; ?></strong>
						</td>
						<td class="woocommerce-table__product-total product-total index2">
							<span class=""><?php echo wc_price($tot); ?></span>
						</td>
					</tr>
				<?php endforeach;
				$output = [];
				foreach ($order->get_meta_data() as $meta) {
					if (strpos($meta->key, 'Remarks for ') === 0 && !empty($meta->value)) {
						// Get category name by removing the prefix
						$category_name = str_replace('Remarks for ', '', $meta->key);
						$term = get_term_by('name', $category_name, 'product_cat');

						if ($term && order_has_category($order, $term->slug)) {
							$output[] = '<tr class="woocommerce-table__line-item order_item remarks">
											<td class="woocommerce-table__product-name product-name" colspan="2">' . esc_html($category_name) . ': ' . esc_html($meta->value) . '</td>
										</tr>';
						}
					}
				}

				if (!empty($output)) {
					echo '<tr class="woocommerce-table__line-item order_item remarks"><td class="woocommerce-table__product-name product-name rem-head" colspan="2" style="border-top: 1px solid #dadada !important;"><div class="boldonly"> Remarks</div></td></tr>';
					echo implode('', $output);
				}
			endif; ?>
		</tbody>
		<tfoot>
			<?php if ($booking['addon_count'] > 0): ?>
				<tr>
					<th scope="row" class="">Add-Ons Subtotal</th>
					<td class=""><span class="pr"><?php echo wc_price($booking['addons_price']); ?></span></td>
				</tr>
			<?php endif; ?>
			<?php if ($booking['slots'] > 1 && $booking['bulk_discount_amount'] > 0 && !$discount_applied_25_per): ?>
				<tr>
					<th scope="row" class="">Bulk Discount (<?php echo $booking['bulk_discount']; ?>)</th>
					<td class=""><span class="pr">-<?php echo wc_price($booking['bulk_discount_amount']); ?></span></td>
				</tr>
			<?php endif; ?>
			<?php if ($booking['discount'] > 0 && !$discount_applied_25_per): ?>
				<tr>
					<th scope="row" class="">Coupon Discount (<?php echo $booking['disc_percentage']; ?>%)</th>
					<td class=""><span class="pr">-<?php echo wc_price($booking['discount']); ?></span></td>
				</tr>
			<?php endif; ?>

			<?php if ($discount_applied_25_per): ?>
				<tr>
					<th scope="row" class=""><?php echo $booking['discount_applied_25_text']; ?></th>
					<td class=""><span class="pr">-<?php echo wc_price($booking['discount_applied_25_price']); ?></span>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th scope="row" class="boldbrown">Booking Total</th>
				<td class="boldbrown"><span class="pr"><?php echo wc_price($booking['total_after_dis']); ?></span></td>
			</tr>
			<?php if ($booking['payers'] > 1):
				$percent = 100 / $booking['payers'];
				?>
				<tr>
					<th scope="row" class="boldbrown">Your Split Share<br>
						<div class="split-share-info" style="text-transform:none; font-weight:normal; font-size:12px;">1 of
							<?php echo $booking['payers']; ?> <span
								style="vertical-align: text-bottom;font-weight: bold;">.</span> <?php echo $percent; ?>%
						</div>
					</th>
					<td class="boldbrown"><span class="pr"><?php echo wc_price($booking['share_total']); ?></span></td>
				</tr>
			<?php endif; ?>
			<tr>
				<th scope="row" class=" upper">CGST @9%</th>
				<td class=""><span class="pr">+<?php echo wc_price($booking['cgst']); ?></span></td>
			</tr>
			</tr>
			<tr>
				<th scope="row" class=" upper">SGST @9%</th>
				<td class=""><span class="pr">+<?php echo wc_price($booking['sgst']); ?></span></td>
			</tr>
			<tr>
				<th scope="row" class="boldbrown">Total Payable</th>
				<td class="boldbrown"><span class="pr"><?php echo wc_price($order->get_total()); ?></span></td>
			</tr>
			<?php
			/*
			foreach ($order->get_order_item_totals() as $key => $total) {
				if ($total['label'] == 'Payment method:') {
					continue;
				}
				if ($total['label'] == 'Total:') {
					$class = 'boldbrown';
				} elseif ($total['label'] == 'Subtotal:') {
					$class = 'boldbrown plus2';
				} else {
					$class = '';
				}
				$totallabel = str_replace(':', '', $total['label']);
				// if ($child) {
				// 	$totallabel = str_replace('Total', 'Your Split Share', $totallabel);
				// 	$totallabel = str_replace('Subtotal', 'Total Amount', $totallabel);
				// }
				$policy_line = '';
				if ($totallabel === 'Refund') {
					$totallabel = str_replace('Refund', 'Refunded', $totallabel);
					$policy_line = '<span class="tooltip_box">
							<span class="tooltip_i">i</span>
							<span class="tooltip_box_hover" style="display:none;text-transform:none">
								Refunded as per the Accordhub Cancellation & Refund Policy. <a
									style="color:#fff;text-decoration:underline"
									href="' . home_url('cancellation-and-refund-policy') . '" target="_blank"
									rel="noopener noreferrer">View Policy</a>.
							</span>
						</span>';
				}
				if ($totallabel == 'GST') {
					$tax_total = $order->get_total_tax();
					$half = $tax_total / 2;
					?>

					<tr>
						<th scope="row" class="<?php echo $class; ?>  upper">CGST @9%</th>
						<td class="<?php echo $class; ?>  upper"><span class="pr">+<?php echo wc_price($half); ?>
								<?php echo $policy_line; ?></span></td>
					</tr>
					<tr>
						<th scope="row" class="<?php echo $class; ?>  upper">SGST @9%</th>
						<td class="<?php echo $class; ?>  upper"><span class="pr">+<?php echo wc_price($half); ?>
								<?php echo $policy_line; ?></span></td>
					</tr>
				<?php } elseif ($totallabel == 'Total' && ($split || $child)) {
					$orignal_amount = $order->get_meta('group_original_total');
					?>
					<tr>
						<th scope="row" class="<?php echo $class; ?>  upper">Total</th>
						<td class="<?php echo $class; ?>  upper"><span class="pr"><?php echo wc_price($orignal_amount); ?>
								<?php echo $policy_line; ?></span></td>
					</tr>
					<tr>
						<th scope="row" class="<?php echo $class; ?>  upper">Your Split Share</th>
						<td class="<?php echo $class; ?>  upper"><span class="pr"><?php echo wp_kses_post($total['value']); ?>
								<?php echo $policy_line; ?></span></td>
					</tr>
				<?php } else { ?>
					<tr>
						<th scope="row" class="<?php echo $class; ?> upper"><?php echo esc_html($totallabel); ?></th>
						<td class="<?php echo $class; ?>  upper"><span class="pr"><?php echo wp_kses_post($total['value']); ?>
								<?php echo $policy_line; ?></span></td>
					</tr>
				<?php }
				?>

				<?php
			}
				*/
			?>

			<?php if ($order->get_customer_note()): ?>
				<tr>
					<th><?php esc_html_e('Note:', 'woocommerce'); ?></th>
					<td>
						<?php
						$customer_note = wc_wptexturize_order_note($order->get_customer_note());
						echo wp_kses(nl2br($customer_note), array('br' => array()));
						?>
					</td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>



	<!-- <div class="refund-card">
		<h3><?php echo verify_razorpay_payment($orignal_order_id); ?></h3>
		<p class="subtitle">Check the status below</p>

		<div class="timelines">
			<div class="timeline-line"></div>

			<div class="step left">
				<div class="icon-wrap">
					<svg viewBox="0 0 24 24" width="24" height="24">
						<circle cx="12" cy="12" r="11" fill="#1763B9" />
						<path d="M10 15.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.9-5.9 1.4 1.4z" fill="#ffffff" />
					</svg>
				</div>
				<div class="step-text">
					<span class="step-title">Booking Cancelled</span>
					<span class="step-date">Sep 27</span>
				</div>
			</div>

			<div class="step center">
				<div class="icon-wrap">
					<svg viewBox="0 0 24 24" width="24" height="24">
						<circle cx="12" cy="12" r="11" fill="#1763B9" />
						<path d="M10 15.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.9-5.9 1.4 1.4z" fill="#ffffff" />
					</svg>
				</div>
				<div class="step-text">
					<span class="step-title">Refund Initiated</span>
					<span class="step-date">Wed Oct 01</span>
				</div>
			</div>

			<div class="step right">
				<div class="icon-wrap">
					<svg viewBox="0 0 24 24" width="24" height="24">
						<circle cx="12" cy="12" r="11" fill="#1763B9" />
						<path d="M10 15.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.9-5.9 1.4 1.4z" fill="#ffffff" />
					</svg>
				</div>
				<div class="step-text">
					<span class="step-title">Refund Completed</span>
					<span class="step-date">Oct 07</span>
				</div>
			</div>
		</div>

		<div class="total-title">Total refund - ₹8679</div>

		<div class="processed-box">
			<div class="bank-details">
				<svg class="bank-icon" viewBox="0 0 24 24" fill="currentColor">
					<path
						d="M12 3L1 9h22L12 3zm0 2.67l7.15 4.33H4.85L12 5.67zM2 21h20v-2H2v2zm3-4h2v-6H5v6zm4 0h2v-6H9v6zm4 0h2v-6h-2v6zm4 0h2v-6h-2v6z" />
				</svg>
				<span>₹8679</span>
				<svg class="chevron-icon" viewBox="0 0 24 24" fill="currentColor">
					<path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z" />
				</svg>
			</div>
			<div class="status-badge">Processed</div>
		</div>

	</div> -->

	<?php
	// --- START CUSTOM REFUND UI ---
	
	// 1. Check if the order is actually cancelled
	if ($org_order->has_status('cancelled') || $org_order->has_status('refunded')) {

		// 2. Fetch cancellation & refund data
		$cancel_time_raw = $org_order->get_meta('phive_cancellation_requested_at');
		$is_refunded = $org_order->get_meta('_phive_manual_payment_status') === 'refunded';
		$is_paid = $org_order->get_date_paid();

		// Use standard date format: May 28, 2026 at 10:30 AM
		$date_format = 'F j, Y \a\t g:i a';

		if ($cancel_time_raw) {
			// If raw is timestamp, create WC_DateTime object
			$cancel_time = wc_format_datetime(new WC_DateTime(is_numeric($cancel_time_raw) ? "@{$cancel_time_raw}" : $cancel_time_raw), $date_format);
		} else {
			// Fallback to order modified date
			$cancel_time = wc_format_datetime($order->get_date_modified(), $date_format);
		}

		$refund_date = ($org_order->get_date_modified()) ? wc_format_datetime($org_order->get_date_modified(), $date_format) : 'Pending';

		// --- SYNC POLICY LOGIC WITH FUNCTIONS.PHP ---
		$booking_start_ts = $order_booking_from;
		if (!$booking_start_ts && $order->get_meta('group_parent_order')) {
			$p_ord = wc_get_order($order->get_meta('group_parent_order'));
			if ($p_ord) {
				foreach ($p_ord->get_items() as $item) {
					if ($item->get_product() && $item->get_product()->get_type() === 'phive_booking') {
						$from_date = $item->get_meta('phive_display_time_from');
						if (!empty($from_date)) {
							$val = is_array($from_date) ? $from_date[0] : $from_date;
							$booking_start_ts = strtotime($val);
							break;
						}
					}
				}
			}
		}

		$now = current_time('timestamp');
		$cancel_at = $cancel_time_raw ? strtotime($cancel_time_raw) : $now;
		if ($booking_start_ts) {
			$seconds_until = $booking_start_ts - $cancel_at;
			$hours_until = round($seconds_until / 3600, 2);
		} else {
			$hours_until = 0;
		}

		if ($hours_until < 72) {
			$policy_percent = 0;
		} else {
			$policy_percent = 50;
		}

		$total_paid = floatval($org_order->get_total());
		$real_refunded = floatval($org_order->get_total_refunded());

		if ($real_refunded > 0 || $org_order->has_status('refunded')) {
			$amount_val = $real_refunded;
		} else {
			$amount_val = $total_paid * ($policy_percent / 100);
		}

		// 3. Determine Stage
		if (!$is_refunded) {
			$stage = 1;
		} else {
			//$stage = 2;
			$rzp_status = strtolower(verify_razorpay_payment($orignal_order_id));
			if ($is_refunded || strpos($rzp_status, 'processed') !== false || strpos($rzp_status, 'Refunded') !== false) {
				$stage = 3;
			}
		}

		// 4. Dynamic Messages & Titles
		if ($stage === 1) {
			$status_title = 'Booking Cancelled';
			if ($org_order->get_total() > 0 && $amount_val == 0 && $is_paid) {
				$status_msg = 'Your booking is cancelled successfully. No refund is applicable due to the 100% cancellation fee policy (< 72 hours).';
			} elseif (!$is_paid) {
				$status_msg = 'Your booking is cancelled successfully. No refund is applicable as no amount has been paid by you.';
			} else {
				$status_msg = 'Your booking is cancelled successfully. We will initiate your refund as per the policies, shortly.';
			}
			$tip = 'Amount will be refunded as per the Accordhub Cancellation &amp; Refund Policy.';
			$css = '.timeline-line{background-image: linear-gradient(90deg, #1763B9 0%, #d3d3d3 0%);}';
		} elseif ($stage === 2) {
			$status_title = 'Refund Initiated';
			$status_msg = 'Your refund has been initiated. You shall receive the refunded amount within 14 business days.';
			$tip = 'Amount will be refunded as per the Accordhub Cancellation &amp; Refund Policy.';
			$css = '.timeline-line{background-image: linear-gradient(90deg, #1763B9 50%, #d3d3d3 0%);}';
		} else {
			$status_title = 'Refund Completed';
			$payment_title = $org_order->get_payment_method_title();
			$rzp_method = $org_order->get_meta('razorpay_payment_method') ?: ($org_order->get_meta('_razorpay_method') ?: $payment_title);
			$display_mode = !empty($rzp_method) ? ucwords(str_replace('_', ' ', $rzp_method)) : 'your original payment method';

			// Get refund transaction ID safely
			$refund_txn_id = '';
			if (!empty($org_order->get_refunds())) {
				$refund_txn_id = $org_order->get_refunds()[0]->id;
			}
			$refund_txn_text = $refund_txn_id ? ' (Refund ID: ' . $refund_txn_id . ')' : '';

			$status_msg = 'The refund process is completed. Your amount should have been credited to your account via ' . esc_html($display_mode) . esc_html($refund_txn_text) . '. If you do not receive the amount within 7 business days, please contact your bank.';
			$tip = 'Refunded as per the Accordhub Cancellation &amp; Refund Policy.';
			$css = '.timeline-line{background-image: linear-gradient(90deg, #1763B9 100%, #d3d3d3 0%);}';
			// echo "<pre>";
			// print_r($org_order->get_refunds()[0]->get_meta_data());
			// echo "<pre>";
		}

		$color_done = '#1763B9';
		$color_pending = '#d3d3d3'; ?>
		<style>
			<?php echo $css; ?>
		</style>
		<div class="refund-card">
			<h3><?php echo esc_html($status_title); ?></h3>
			<p class="subtitle"><?php echo esc_html($status_msg); ?></p>

			<div class="timelines">
				<div class="timeline-line"></div>

				<div class="step left">
					<div class="icon-wrap">
						<svg viewBox="0 0 24 24" width="24" height="24">
							<circle cx="12" cy="12" r="11"
								fill="<?php echo $stage >= 1 ? $color_done : $color_pending; ?>" />
							<?php if ($stage >= 1): ?>
								<path d="M10 15.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.9-5.9 1.4 1.4z" fill="#ffffff" /><?php endif; ?>
						</svg>
					</div>
					<div class="step-text">
						<span class="step-title" style="<?php echo $stage >= 1 ? '' : 'color:#999;'; ?>">Booking
							Cancelled</span>
						<span class="step-date"><?php echo esc_html($cancel_time); ?></span>
					</div>
				</div>

				<div class="step center">
					<div class="icon-wrap">
						<svg viewBox="0 0 24 24" width="24" height="24">
							<circle cx="12" cy="12" r="11"
								fill="<?php echo $stage >= 3 ? $color_done : $color_pending; ?>" />
							<?php if ($stage >= 3): ?>
								<path d="M10 15.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.9-5.9 1.4 1.4z" fill="#ffffff" /><?php endif; ?>
						</svg>
					</div>
					<div class="step-text">
						<span class="step-title" style="<?php echo $stage >= 3 ? '' : 'color:#999;'; ?>">Refund
							Initiated</span>
						<span class="step-date"><?php echo $stage >= 3 ? esc_html($refund_date) : '--'; ?></span>
					</div>
				</div>

				<div class="step right">
					<div class="icon-wrap">
						<svg viewBox="0 0 24 24" width="24" height="24">
							<circle cx="12" cy="12" r="11"
								fill="<?php echo $stage >= 3 ? $color_done : $color_pending; ?>" />
							<?php if ($stage >= 3): ?>
								<path d="M10 15.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.9-5.9 1.4 1.4z" fill="#ffffff" /><?php endif; ?>
						</svg>
					</div>
					<div class="step-text">
						<span class="step-title" style="<?php echo $stage >= 3 ? '' : 'color:#999;'; ?>">Refund
							Completed</span>
						<span class="step-date"><?php echo $stage >= 3 ? esc_html($refund_date) : '--'; ?></span>
					</div>
				</div>
			</div>

			<?php $amount = wc_price($amount_val);
			if ($is_paid) { ?>
				<div class="total-title"><?php echo ($stage >= 3) ? 'Total refund' : 'Total to be refunded'; ?>:
					<?php echo wp_kses_post($amount); ?>
					<span class="tooltip_box">
						<span class="tooltip_i">i</span>
						<span class="tooltip_box_hover" style="display:none;">
							<?php echo $tip; ?> <a style="color:#fff;text-decoration:underline"
								href="<?php echo home_url(); ?>/cancellation-and-refund-policy" target="_blank"
								rel="noopener noreferrer">View Policy</a>.
						</span>
					</span>
				</div>
			<?php } ?>

			<?php if ($stage >= 2 && $amount_val > 0): ?>
				<div class="processed-box">
					<div class="bank-details">
						<svg class="bank-icon" viewBox="0 0 24 24" fill="currentColor">
							<path
								d="M12 3L1 9h22L12 3zm0 2.67l7.15 4.33H4.85L12 5.67zM2 21h20v-2H2v2zm3-4h2v-6H5v6zm4 0h2v-6H9v6zm4 0h2v-6h-2v6zm4 0h2v-6h-2v6z" />
						</svg>
						<span><?php echo wp_kses_post($amount); ?></span>
						<!-- <svg class="chevron-icon" viewBox="0 0 24 24" fill="currentColor">
							<path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z" />
						</svg> -->
					</div>
					<div class="status-badge"><?php echo verify_razorpay_payment($orignal_order_id); ?></div>
				</div>
			<?php endif; ?>
			<p style="margin-top: 18px; font-size: 14px;">
				<a href="<?php echo home_url(); ?>/cancellation-and-refund-policy" target="_blank"
					style="text-decoration:underline; color:#1763B9; font-weight: 500;">View cancellation policy for
					details</a>
			</p>
		</div>
	<?php }
	// --- END CUSTOM REFUND UI ---
	
	$child_Split_orders = wc_get_orders([
		'limit' => -1,
		'meta_key' => 'group_parent_order',
		'meta_value' => $order_id,
		'orderby' => 'ID',
		'order' => 'ASC'
	]);
	if ($child_Split_orders && !is_checkout()) {

		// Include the Parent Order (Organizer) along with the Child Orders (Participants)
		$all_split_orders = [wc_get_order($order_id)];
		foreach ($child_Split_orders as $c_post) {
			$c_order = wc_get_order($c_post->ID);
			if ($c_order) {
				$all_split_orders[] = $c_order;
			}
		}

		echo '<section class="woocommerce-linked-orders ">';
		echo '<h3 class="woocommerce-order-section__title">Split Payment Details</h3>';
		echo '<div class="force-scroll" style="overflow-x: auto; max-width: 100%; -webkit-overflow-scrolling: touch;">';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details cdf new">';
		echo '<thead><tr>';
		echo '<th class="text-left">Name / Email</th>';
		// echo '<th class="text-left">WhatsApp No.</th>';
		// echo '<th class="text-left">Company</th>';
		echo '<th class="text-left">Status</th>';
		echo '<th class="text-right">Amount</th>';
		echo '<th class="text-right">Refunded</th>';
		//echo '<th class="text-right">Net Revenue</th>';
		echo '<th class="text-center">Actions</th>';
		echo '</tr></thead><tbody>';

		$total_paid = 0;
		$total_refunded = 0;
		$total_net = 0;


		foreach ($all_split_orders as $sp_order) {
			$sp_id = $sp_order->get_id();
			$role = ($sp_id == $order_id) ? 'Organizer' : 'Participant';

			$fname = trim($sp_order->get_billing_first_name());
			$lname = trim($sp_order->get_billing_last_name());
			$wa = $sp_order->get_billing_phone();
			$name = !empty($lname) && strpos($fname, $lname) === false ? $fname . ' ' . $lname : $fname;
			$email = $sp_order->get_billing_email();
			$company = $sp_order->get_billing_company();

			$status = $sp_order->get_status();
			$status_name = wc_get_order_status_name($status);



			$paid = (float) $sp_order->get_total();
			$refunded = (float) $sp_order->get_total_refunded();
			$net = $paid - $refunded;

			$total_paid += $paid;
			$total_refunded += $refunded;
			$total_net += $net;

			$upload_dir = wp_upload_dir();
			$inv_url = $upload_dir['baseurl'] . '/invoice-' . $sp_id . '.pdf';
			$rec_url = $upload_dir['baseurl'] . '/receipt-' . $sp_id . '.pdf';

			echo '<tr>';
			echo '<td><strong>' . esc_html($name) . '</strong><br><small class="split-meta">' . esc_html($email) . '</small></td>';
			// echo '<td>' . esc_html($wa) . '</td>';
			// echo '<td>' . esc_html($company) . '</td>';
			echo '<td>';
			echo verify_razorpay_payment($sp_id);
			echo '</td>';

			echo '<td class="text-right split-money">' . wc_price($paid) . '</td>';
			echo '<td class="text-right split-money">' . ($refunded > 0 ? '-' . wc_price($refunded) : '-') . '</td>';
			//echo '<td class="text-right split-money"><strong>' . wc_price($net) . '</strong></td>';
	
			echo '<td class="text-center"><div class="split-actions" style="text-align:left;">';

			// Pay Now Button (Only if pending & belongs to logged-in user)
			if ($status == 'pending' && $sp_order->get_customer_id() == $current_user_id) {
				echo '<a href="' . esc_url($sp_order->get_checkout_payment_url()) . '" class="action-btn">Pay Now</a>';
			}

			echo '<a href="' . esc_url($inv_url) . '" target="_blank" class="action-btn">Invoice</a>';

			if ($sp_order->is_paid()) {
				echo '<a href="' . esc_url($rec_url) . '" target="_blank" class="action-btn">Receipt</a>';
			}

			echo '</div></td>';
			echo '</tr>';
		}

		echo '</tbody>';

		// FOOTER ROW FOR TOTALS
		echo '<tfoot><tr class="split-footer">';
		echo '<td colspan="3" class="text-right footer-label"><strong>Total Amount:</strong></td>';
		echo '<td class="text-right split-money">' . wc_price($total_paid) . '</td>';
		echo '<td class="text-right split-money refunded-val">' . ($total_refunded > 0 ? '-' . wc_price($total_refunded) : '-') . '</td>';
		echo '<td class="text-right split-money net-val">' . wc_price($total_net) . '</td>';
		echo '<td></td>';
		echo '</tr></tfoot>';

		echo '</table>';
		echo '</div>';
		echo '</section>';
	}

	echo '<div class="case-box">';
	echo '<div class="d-flex">';
	echo '<h3>Case Details</h3>';
	if (!$ord_meta) {
		echo '<button type="button" class="button woocommerce-button edit-case-details" data-order-id="' . esc_attr($order_id) . '">Update</button>';
	}
	echo '</div>';
	if (!is_wc_endpoint_url('order-received')) {
		// Get custom order meta values
		$case_name = $order->get_meta('Case Title');
		$case_id = $order->get_meta('Case ID');
		$case_desc = $order->get_meta('Case Description');
		$total = $order->get_meta('Total Participants');
		$total_parties = $order->get_meta('Total Parties Involved');
		$parties = $order->get_meta('Parties Involved'); // structured array
		$arbitrators = $order->get_meta('Arbitrators');  // array
		$other_rem = $order->get_meta('Other Remarks');

		// Try to get from current order items if missing
		if (empty($case_name) || empty($case_id) || empty($case_desc) || empty($total) || empty($total_parties) || empty($parties) || empty($arbitrators)) {
			foreach ($order->get_items() as $item_id => $item) {
				$product = $item->get_product();
				if ($product && $product->get_type() === 'phive_booking') {
					$case_name = $case_name ?: $item->get_meta('Case Title');
					$case_id = $case_id ?: $item->get_meta('Case ID');
					$case_desc = $case_desc ?: $item->get_meta('Case Description');
					$total = $total ?: $item->get_meta('Total Participants');
					$total_parties = $total_parties ?: $item->get_meta('Total Parties Involved');
					$parties = $parties ?: $item->get_meta('Parties Involved');
					$arbitrators = $arbitrators ?: $item->get_meta('Arbitrators');
					$other_rem = $other_rem ?: $item->get_meta('Other Remarks');
					break;
				}
			}
		}

		// Try to get from parent order items if still missing
		if (empty($case_name) || empty($case_id) || empty($case_desc) || empty($total) || empty($total_parties) || empty($parties) || empty($arbitrators)) {
			$parent_id = $order->get_meta('group_parent_order');
			if ($parent_id) {
				$p_ord = wc_get_order($parent_id);
				if ($p_ord) {
					foreach ($p_ord->get_items() as $item_id => $item) {
						$product = $item->get_product();
						if ($product && $product->get_type() === 'phive_booking') {
							$case_name = $case_name ?: $item->get_meta('Case Title');
							$case_id = $case_id ?: $item->get_meta('Case ID');
							$case_desc = $case_desc ?: $item->get_meta('Case Description');
							$total = $total ?: $item->get_meta('Total Participants');
							$total_parties = $total_parties ?: $item->get_meta('Total Parties Involved');
							$parties = $parties ?: $item->get_meta('Parties Involved');
							$arbitrators = $arbitrators ?: $item->get_meta('Arbitrators');
							$other_rem = $other_rem ?: $item->get_meta('Other Remarks');
							break;
						}
					}
				}
			}
		}


		if (is_admin()) { ?>
			<style>
				#order_data .order_data_column {
					width: 100%;
				}

				#order_data table.shop_table.my_account_orders.wc-case-details {
					width: 100%;
					text-align: left;
				}

				div#order_data button.button.woocommerce-button.edit-case-details {
					display: none;
				}

				#order_data h3 {
					display: inline-block;
				}
			</style>
		<?php }


		ob_start();

		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details cdf4">';

		// 1. Case Title (Only if set)
		if (!empty($case_name)) {
			echo '<thead><tr><th class="woocommerce-table__product-name product-name">Case Title</th><th class="woocommerce-table__product-table product-total" data-title="Case Title">' . esc_html($case_name) . '</th></tr></thead>';
		}

		echo '<tbody>';

		// 2. Case ID
		if (!empty($case_id)) {
			echo '<tr><td class="woocommerce-table__product-name product-name">Case ID</td><td class="woocommerce-table__product-table product-total" data-title="Case ID">' . esc_html($case_id) . '</td></tr>';
		}

		// 3. Case Description
		if (!empty($case_desc)) {
			echo '<tr><td class="woocommerce-table__product-name product-name">Case Description</td><td class="woocommerce-table__product-table product-total" data-title="Case Description">' . nl2br(esc_html($case_desc)) . '</td></tr>';
		}

		// 4. Arbitrators (Filter empty values)
		$filtered_arbitrators = !empty($arbitrators) && is_array($arbitrators) ? array_filter($arbitrators) : [];
		if (!empty($filtered_arbitrators)) {
			echo '<tr><td class="woocommerce-table__product-name product-name">Arbitrators</td><td class="woocommerce-table__product-table product-total" data-title="Arbitrators">' . esc_html(implode(', ', $filtered_arbitrators)) . '</td></tr>';
		}

		// 5. Total Members
		if (!empty($total)) {
			echo '<tr><td class="woocommerce-table__product-name product-name">Total Members</td><td class="woocommerce-table__product-table product-total" data-title="Total Members">' . esc_html($total) . '</td></tr>';
		}

		// 6. No of Parties
		if (!empty($total_parties)) {
			echo '<tr><td class="woocommerce-table__product-name product-name">No of Parties</td><td class="woocommerce-table__product-table product-total" data-title="No of Parties">' . esc_html($total_parties) . '</td></tr>';
		}

		// 7. Parties Details (The logic fix for your screenshot)
		if (!empty($parties) && is_array($parties)) {
			// Buffer the parties content first to see if we have anything to show
			$parties_html = '';

			foreach ($parties as $index => $party) {
				// --- LOGIC CHECK START ---
				// Check if Party Name exists
				// echo '<pre>';
				//   print_r($party);
				// echo '</pre>';
				$has_name = !empty($party['name']);

				// Check if any Members exist (filter out empty names)
				$valid_members = [];
				if (!empty($party['members']) && is_array($party['members'])) {
					foreach ($party['members'] as $member) {
						if (!empty($member['name'])) {
							$valid_members[] = $member;
						}
					}
				}
				$has_members = !empty($valid_members);

				// If NO name and NO members, skip this party entirely (fixes the empty "Party 1" issue)
				if (!$has_name && !$has_members) {
					continue;
				}
				// --- LOGIC CHECK END ---
	
				// If we are here, there is data to show. Add to buffer.
				//$parties_html .= '<strong>Party ' . ($index + 1) . '</strong><br>';
	
				if ($has_name) {
					$parties_html .= 'Party Name: ' . esc_html($party['name']) . '<br>';
				}

				if ($has_members) {
					$legal_counsels = [];
					$company_reps = [];

					foreach ($valid_members as $member) {
						if ($member['role'] === 'legal_counsel') {
							$legal_counsels[] = esc_html($member['name']);
						} else {
							$company_reps[] = esc_html($member['name']);
						}

						if ($member['name']) {
							$parties_html .= '<span style="margin-top:5px;display: inline-block;">Name: ' . esc_html($member['name']) . '</span><br>';
						}

						if ($member['wa']) {
							$parties_html .= 'Whatsapp Number: ' . esc_html($member['wa']) . '<br>';
						}

						if ($member['role'] === 'legal_counsel') {
							$parties_html .= 'Role: Legal Counsel<br>';
						} else {
							$parties_html .= 'Role: Company Representatives<br>';
						}


					}



					/*if (!empty($legal_counsels)) {
					  $parties_html .= 'Legal Counsel: ' . implode(', ', $legal_counsels) . '<br>';
					}
					if (!empty($company_reps)) {
					  $parties_html .= 'Company Representatives: ' . implode(', ', $company_reps) . '<br>';
					}*/
				}
				$parties_html .= '<br>'; // spacing between parties
			}

			// Only echo the row if we actually created HTML for at least one party
			if (!empty($parties_html)) {
				echo '<tr><td class="woocommerce-table__product-name product-name">Parties Details</td><td class="woocommerce-table__product-table product-total" data-title="Parties Involved">' . $parties_html . '</td></tr>';
			}
		}

		// 8. Other Remarks
		if (!empty($other_rem)) {
			echo '<tr><td class="woocommerce-table__product-name product-name">Other Remarks</td><td class="woocommerce-table__product-table product-total" data-title="Other Remarks">' . esc_html($other_rem) . '</td></tr>';
		}

		echo '</tbody>';
		echo '</table>';

		$generated_table_html = ob_get_clean();

		if ($generated_table_html === '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details cdf4"><tbody></tbody></table>') {
			echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details cdf4"><tbody><tr><td style="padding: 0; border: none;">No case details added</td></tr></tbody></table>';
		} else {
			echo $generated_table_html;
		}

		// Add Edit button
	


		// Popup container
		echo '<div id="case-edit-popup-' . esc_attr($order_id) . '" class="case-edit-popup" style="display:none;">';
		show_case_details_form_on_thankyou($order_id);
		echo '</div>';


		?>

		<style>
			.case-edit-popup {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background: rgba(0, 0, 0, 0.6);
				z-index: 9999;
				display: flex;
				align-items: center;
				justify-content: center;
			}

			.case-edit-popup .woocommerce-case-form {
				background: #fff;
				padding: 20px;
				max-height: 90vh;
				overflow-y: auto;
				border-radius: 8px;
				width: 100%;
				position: relative;
			}
		</style>

		<script>
			document.addEventListener("click", function (e) {
				if (e.target.classList.contains("edit-case-details")) {
					const orderId = e.target.dataset.orderId;
					document.getElementById("case-edit-popup-" + orderId).style.display = "flex";
				}
			});
		</script>
		<?php
	}
	echo '</div>';



	$child_Split_orders = wc_get_orders([
		'limit' => -1,
		'meta_key' => 'group_parent_order',
		'meta_value' => $order_id,
		'orderby' => 'ID',
		'order' => 'ASC'
	]);
	if ($child_Split_orders && !is_checkout()) {

		// Include the Parent Order (Organizer) along with the Child Orders (Participants)
		$all_split_orders = [wc_get_order($order_id)];
		foreach ($child_Split_orders as $c_post) {
			$c_order = wc_get_order($c_post->ID);
			if ($c_order) {
				$all_split_orders[] = $c_order;
			}
		}

		echo '<section class="woocommerce-linked-orders ">';
		echo '<h3 class="woocommerce-order-section__title">Split Party Details</h3>';
		echo '<div class="force-scroll" style="overflow-x: auto; max-width: 100%; -webkit-overflow-scrolling: touch;">';
		echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details cdf new">';
		echo '<thead><tr>';
		echo '<th class="text-left">Other Party Details</th>';
		echo '<th class="text-left">Full Name</th>';
		echo '<th class="text-left">Company Name</th>';
		echo '<th class="text-left">Email Id</th>';
		echo '<th class="text-left">Mobile Number</th>';
		echo '<th class="text-left">Payment Status</th>';
		echo '</tr></thead><tbody>';

		$total_paid = 0;
		$total_refunded = 0;
		$total_net = 0;
		$index = 1;


		foreach ($all_split_orders as $sp_order) {
			$sp_id = $sp_order->get_id();
			$role = ($sp_id == $order_id) ? 'Organizer' : 'Participant';

			$fname = trim($sp_order->get_billing_first_name());
			$lname = trim($sp_order->get_billing_last_name());
			$wa = $sp_order->get_billing_phone();
			$name = !empty($lname) && strpos($fname, $lname) === false ? $fname . ' ' . $lname : $fname;
			$email = $sp_order->get_billing_email();
			$company = $sp_order->get_billing_company();

			$status = $sp_order->get_status();
			$status_name = wc_get_order_status_name($status);



			$paid = (float) $sp_order->get_total();
			$refunded = (float) $sp_order->get_total_refunded();
			$net = $paid - $refunded;

			$total_paid += $paid;
			$total_refunded += $refunded;
			$total_net += $net;

			$upload_dir = wp_upload_dir();
			$inv_url = $upload_dir['baseurl'] . '/invoice-' . $sp_id . '.pdf';
			$rec_url = $upload_dir['baseurl'] . '/receipt-' . $sp_id . '.pdf';
			if ($orignal_order_id !== $sp_id) {
				echo '<tr>';
				echo '<td>Party ' . $index . '</td>';
				echo '<td>' . esc_html($name) . '</td>';
				echo '<td>' . esc_html($company) . '</td>';
				echo '<td>' . esc_html($email) . '</td>';
				echo '<td>' . esc_html($wa) . '</td>';
				echo '<td>';
				echo verify_razorpay_payment($sp_id);
				echo '</td>';
				echo '</tr>';
			}
			$index++;
		}

		echo '</tbody>';

		// FOOTER ROW FOR TOTALS
		echo '<tfoot><tr class="split-footer">';
		echo '<td colspan="3" class="text-right footer-label"><strong>Total Amount:</strong></td>';
		echo '<td class="text-right split-money">' . wc_price($total_paid) . '</td>';
		echo '<td class="text-right split-money refunded-val">' . ($total_refunded > 0 ? '-' . wc_price($total_refunded) : '-') . '</td>';
		echo '<td class="text-right split-money net-val">' . wc_price($total_net) . '</td>';
		echo '<td></td>';
		echo '</tr></tfoot>';

		echo '</table>';
		echo '</div>';
		echo '</section>';
	}


	$meta_addons = $order->get_meta('_meeting_addons_data');

	if (!empty($meta_addons) && is_array($meta_addons)) {
		// 1. Sort by timestamp (Newest First)
		usort($meta_addons, function ($a, $b) {
			return $b['timestamp'] <=> $a['timestamp'];
		});

		// --- NEW: Merge Identical Products Across All Batches ---
		$merged_addons = [];
		foreach ($meta_addons as $item) {
			$product_key = !empty($item['product_id']) ? $item['product_id'] : $item['product_name'];

			// Resolve category early so we can sort properly for rowspans
			$temp_cat = !empty($item['product_category']) ? $item['product_category'] : '';
			if (empty($temp_cat) && !empty($item['product_id'])) {
				$terms = get_the_terms($item['product_id'], 'product_cat');
				$temp_cat = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : 'Miscellaneous';
			}
			if (empty($temp_cat)) {
				$temp_cat = 'Miscellaneous';
			}
			$item['sort_cat'] = $temp_cat;

			if (isset($merged_addons[$product_key])) {
				$merged_addons[$product_key]['qty'] += (float) $item['qty'];
				$merged_addons[$product_key]['line_total'] += (float) $item['line_total'];
				if (!empty($item['remark'])) {
					if (empty($merged_addons[$product_key]['remark'])) {
						$merged_addons[$product_key]['remark'] = $item['remark'];
					} elseif (strpos($merged_addons[$product_key]['remark'], $item['remark']) === false) {
						$merged_addons[$product_key]['remark'] .= ', ' . $item['remark'];
					}
				}
			} else {
				$merged_addons[$product_key] = $item;
			}
		}

		// Sort by category to guarantee rowspans group perfectly together
		usort($merged_addons, function ($a, $b) {
			return strcmp($a['sort_cat'], $b['sort_cat']);
		});

		// Reassign back to $meta_addons and force a single batch ID 
		// so the table renders as one continuous, clean list
		$meta_addons = [];
		foreach ($merged_addons as $m_item) {
			$m_item['batch_id'] = 'merged';
			$meta_addons[] = $m_item;
		}
		// --- END MERGE LOGIC ---
	
		// 2. Group by Batch ID
		$meta_batches = [];
		foreach ($meta_addons as $item) {
			$batch_id = $item['batch_id'];
			$meta_batches[$batch_id][] = $item;
		}

		// 3. Calculate Pending and Billed Totals
		$grand_total_pending = 0;
		$grand_total_billed = 0;
		foreach ($meta_addons as $row) {
			if ((!isset($row['billed_status']) || $row['billed_status'] !== 'billed') && (!isset($row['status']) || $row['status'] !== 'cancelled')) {
				$grand_total_pending += floatval($row['line_total']);
			}
			if ((isset($row['billed_status']) && $row['billed_status'] === 'billed') && (!isset($row['status']) || $row['status'] !== 'cancelled')) {
				$grand_total_billed += floatval($row['line_total']);
			}
		}

		echo '<section class="woocommerce-linked-orders" style="margin-top:40px;">';
		echo '<h3 class="woocommerce-order-section__title">Add-Ons Ordered</h3>';

		echo '<div class="force-scroll" style="overflow-x: auto; max-width: 100%; -webkit-overflow-scrolling: touch;">';
		echo '<table class="woocommerce-table shop_table order_details new">';
		echo '<thead style="background:#f6f7f7;">';
		echo '<tr>';
		echo '<th style="">Category</th>';
		echo '<th style="">Item Name</th>';
		echo '<th style="">Quantity</th>';
		echo '<th style="">Rate/Item</th>';
		echo '<th style="">Total</th>';
		echo '</tr>';
		echo '</thead>';

		echo '<tbody>';

		foreach ($meta_batches as $batch_id => $rows) {
			// Helper vars for rowspans
			$rowspans = [];
			$prev_cat = null;
			$start_index = 0;

			foreach ($rows as $i => $row) {
				$cat_name = !empty($row['product_category']) ? $row['product_category'] : '';
				if (empty($cat_name) && !empty($row['product_id'])) {
					$terms = get_the_terms($row['product_id'], 'product_cat');
					$cat_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : 'Miscellaneous';
				}
				if (empty($cat_name))
					$cat_name = 'Miscellaneous';

				if ($cat_name !== $prev_cat) {
					$rowspans[$i] = 1;
					$start_index = $i;
				} else {
					$rowspans[$start_index]++;
				}
				$prev_cat = $cat_name;
			}

			// Render Rows
			foreach ($rows as $index => $row) {
				$is_billed = (isset($row['billed_status']) && $row['billed_status'] === 'billed');
				$status = isset($row['status']) ? $row['status'] : 'order_placed';

				// Style: Billed rows look slightly faded
				$style = $is_billed ? 'background-color:#fafafa; color:#888;' : 'background-color:#fff;';

				$cat_name = !empty($row['product_category']) ? $row['product_category'] : '';
				if (empty($cat_name) && !empty($row['product_id'])) {
					$terms = get_the_terms($row['product_id'], 'product_cat');
					$cat_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : 'Miscellaneous';
				}
				if (empty($cat_name))
					$cat_name = 'Miscellaneous';

				echo '<tr>';

				// 1. Category (with rowspan)
				if (isset($rowspans[$index])) {
					echo '<td rowspan="' . $rowspans[$index] . '">';
					echo '<strong>' . esc_html($cat_name) . '</strong>';
					if (!empty($row['remark'])) {
						echo '<br><small>Remark: ' . esc_html($row['remark']) . '</small>';
					}
					echo '</td>';
				}

				// 2. Item Name
				$product_id = $row['product_id'];
				// Get brand terms
				$brands = get_the_terms($product_id, 'product_brand');

				echo '<td>';
				if (!empty($brands) && !is_wp_error($brands)) {
					$brand = $brands[0];

					// Get brand image (term meta)
					$brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);

					if ($brand_image_id) {
						echo $brand_image = wp_get_attachment_image($brand_image_id, 'large', false, [
							'style' => 'width:35px;height:auto;margin-right:8px;vertical-align:middle;border-radius: 2px;'
						]);
					}
				}
				echo esc_html($row['product_name']);
				echo '</td>';

				// 3. Qty
				echo '<td class="qty">' . esc_html($row['qty']) . '</td>';
				echo '<td class="qty">' . wc_price($row['price']) . '</td>';

				// 8. Total
				echo '<td>' . wc_price($row['line_total']) . '</td>';

				echo '</tr>';
			}
		}

		echo '</tbody>';

		// Get Coupon Data for Totals
		$saved_coupon_code = $order->get_meta('_meeting_addon_coupon');
		$coupon = !empty($saved_coupon_code) ? new WC_Coupon($saved_coupon_code) : null;

		// --- TFOOT: PENDING TOTALS ---
		if ($grand_total_pending > 0) {
			$discount_amount = 0;
			if ($coupon && $coupon->get_id()) {
				$discount_amount = ($coupon->get_discount_type() === 'percent')
					? $grand_total_pending * ($coupon->get_amount() / 100)
					: $coupon->get_amount();
			}
			if ($discount_amount > $grand_total_pending)
				$discount_amount = $grand_total_pending;

			$taxable = $grand_total_pending - $discount_amount;
			$tax = $taxable * 0.18;
			$final = $taxable + $tax;

			echo '<tfoot>';
			echo '<tr class="bold"><td colspan="4">Total Add-Ons Price</td><td>' . wc_price($grand_total_pending) . '</td></tr>';

			if ($discount_amount > 0) {
				echo '<tr><td colspan="4">Discount</td><td>-' . wc_price($discount_amount) . '</td></tr>';
			}

			echo '<tr class="bold"><td colspan="4">Total Amount</td><td>' . wc_price($grand_total_pending - $discount_amount) . '</td></tr>';

			// FIX 2: Prevent Division by Zero
			$payers = (!empty($booking['payers']) && $booking['payers'] > 0) ? (int) $booking['payers'] : 1;

			if ($payers > 1):
				echo '<tr class="bold"><td colspan="4">Your Split Share</td><td>' . wc_price(($grand_total_pending - $discount_amount) / $payers) . '</td></tr>';
			endif;

			echo '<tr><td colspan="4">CGST @9%</td><td>+' . wc_price(($tax / 2) / $payers) . '</td></tr>';
			echo '<tr><td colspan="4">SGST @9%</td><td>+' . wc_price(($tax / 2) / $payers) . '</td></tr>';
			echo '<tr class="bold"><td colspan="4">Your Total Payable</td><td>' . wc_price($final / $payers) . '</td></tr>';
			echo '</tfoot>';
		}

		// --- TFOOT: BILLED TOTALS ---
		if ($grand_total_billed > 0) {
			$discount_amount = 0;
			if ($coupon && $coupon->get_id()) {
				$discount_amount = ($coupon->get_discount_type() === 'percent')
					? $grand_total_billed * ($coupon->get_amount() / 100)
					: $coupon->get_amount();
			}
			if ($discount_amount > $grand_total_billed)
				$discount_amount = $grand_total_billed;

			$taxable = $grand_total_billed - $discount_amount;
			$tax = $taxable * 0.18;
			$final = $taxable + $tax;

			echo '<tfoot>';
			echo '<tr class=""><td colspan="4">Total Add-Ons Price</td><td>' . wc_price($grand_total_billed) . '</td></tr>';

			if ($discount_amount > 0) {
				echo '<tr><td colspan="4">Discount</td><td>-' . wc_price($discount_amount) . '</td></tr>';
			}
			echo '<tr class="bold"><td colspan="4">Total Amount</td><td>' . wc_price($grand_total_billed - $discount_amount) . '</td></tr>';

			$payers = (!empty($booking['payers']) && $booking['payers'] > 0) ? (int) $booking['payers'] : 1;

			if ($payers > 1):
				echo '<tr class="bold"><td colspan="4">Your Split Share</td><td>' . wc_price(($grand_total_pending - $discount_amount) / $payers) . '</td></tr>';
			endif;

			echo '<tr><td colspan="4">CGST @9%</td><td>+' . wc_price(($tax / 2) / $booking['payers']) . '</td></tr>';
			echo '<tr><td colspan="4">SGST @9%</td><td>+' . wc_price(($tax / 2) / $booking['payers']) . '</td></tr>';
			echo '<tr class="bold"><td colspan="4">Your Total Payable</td><td>' . wc_price($final / $booking['payers']) . '</td></tr>';
			echo '</tfoot>';
		}

		echo '</table>';
		echo '</div>';
		echo '</section>';
	}
	// ==========================================
	// DISPLAY GENERATED INVOICES (BILLED HISTORY)
	// ==========================================
	$invoice_ids = $order->get_meta('meeting_addons_billed');

	if (!empty($invoice_ids) && is_array($invoice_ids)) {
		echo '<section class="woocommerce-linked-orders" style="">';
		echo '<h3 class="woocommerce-order-section__title">Add-Ons Invoices</h3>';
		echo '<div class="force-scroll" style="overflow-x: auto; max-width: 100%; -webkit-overflow-scrolling: touch;">';
		echo '<table class="woocommerce-table shop_table order_details new">';
		echo '<thead>';
		echo '<tr>';
		//echo '<th>Invoice #</th>';
		echo '<th>Billed To</th>';
		echo '<th>Date</th>';
		echo '<th>Status</th>';
		echo '<th>Amount</th>';
		echo '<th>Actions</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		$has_invoices = false;
		$current_user_id = get_current_user_id();

		foreach ($invoice_ids as $inv_id) {
			$inv = wc_get_order($inv_id);
			if (!$inv)
				continue;
			$has_invoices = true;

			$upload_dir = wp_upload_dir();
			$inv_url = $upload_dir['baseurl'] . '/invoice_' . $order_id . '_' . $inv_id . '.pdf';
			$rec_url = $upload_dir['baseurl'] . '/receipt_' . $order_id . '_' . $inv_id . '.pdf';

			$fname = trim($inv->get_billing_first_name());
			$lname = trim($inv->get_billing_last_name());

			if (!empty($lname) && strripos($fname, $lname) === (strlen($fname) - strlen($lname))) {
				$billed_name = $fname;
			} else {
				$billed_name = $fname . ' ' . $lname;
			}
			$billed_email = $inv->get_billing_email();

			$st = $inv->get_status();
			$status_label = wc_get_order_status_name($st);

			echo '<tr>';

			// 1. Invoice Number (Link to order if it belongs to current user)
			// echo '<td>';
			// if ($inv->get_customer_id() == $current_user_id) {
			// 	echo '<a href="' . esc_url($inv->get_view_order_url()) . '" >#' . esc_html($inv_id) . '</a>';
			// } else {
			// 	echo '<span>#' . esc_html($inv_id) . '</span>';
			// }
			// echo '</td>';
	
			// 2. Billed To
			echo '<td><strong>' . esc_html($billed_name) . '</strong><br><small style="color:#666;">' . esc_html($billed_email) . '</small></td>';

			// 3. Date
			echo '<td>' . $inv->get_date_created()->date('M d, Y') . '</td>';

			// 4. Status Badges (Reusing CSS from the items table)
			echo '<td>';
			echo verify_razorpay_payment($inv_id);
			echo '</td>';

			// 5. Amount
			echo '<td>' . $inv->get_formatted_order_total() . '</td>';

			// 6. Actions (Buttons)
			echo '<td>';
			echo '<div style="text-align:center;">';

			// Only show "Pay Now" if pending AND the invoice belongs to the logged-in user
			if ($inv->has_status('pending') && $inv->get_customer_id() == $current_user_id) {
				echo '<a href="' . esc_url($inv->get_checkout_payment_url()) . '" class="action-btn">Pay Now</a>';
			}

			echo '<a href="' . esc_url($inv_url) . '" target="_blank" class="action-btn" title="Download Invoice">Invoice</a>';

			if ($st == 'completed') {
				echo '<a href="' . esc_url($rec_url) . '" target="_blank" class="action-btn" title="Download Receipt">Receipt</a>';
			}

			echo '</div>';
			echo '</td>';
			echo '</tr>';
		}

		if (!$has_invoices) {
			echo '<tr><td colspan="6">No invoices generated yet.</td></tr>';
		}

		echo '</tbody></table></div></section>';
	}

	do_action('woocommerce_order_details_after_order_table', $order); ?>
</section>

<input type="hidden" name="mm" value="<?php echo $total_participants; ?>">
<?php



if ($orignal_order_id) {
	$order = wc_get_order($orignal_order_id);
	$order_id = $orignal_order_id;
}

/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action('woocommerce_after_order_details', $order);

if ($show_customer_details) {
	wc_get_template('order/order-details-customer.php', array('order' => $order));
}

