<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;
$addons_total = 0;


// echo '<pre>';
// 	print_r(WC()->cart->get_cart());
// echo '</pre>';

foreach (WC()->cart->get_cart() as $cart_item) {
	$product_id = $cart_item['product_id'];
	$terms = get_the_terms($product_id, 'product_cat');
	if ($terms && !is_wp_error($terms)) {
		foreach ($terms as $term) {
			if ($term->slug === 'addons' || term_is_ancestor_of(get_term_by('slug', 'addons', 'product_cat'), $term, 'product_cat')) {
				$addons_total += $cart_item['line_subtotal'];
				break;
			}
		}
	}
}
$subtotal = WC()->cart->get_subtotal();
$discount = WC()->cart->get_discount_total();
$discount_coupon = WC()->cart->get_discount_total();

// 25% discount applied
$total_fee_25 = 0;
$fees = WC()->cart->get_fees();
foreach ($fees as $fee) {
	if ($fee->name === 'Less Discount (30%)') {
		$total_fee_25 = $fee->amount;
	}
}



// print_r($discount);
$tx = WC()->cart->get_total_tax();

if (accordhub_is_discount_active() && $total_fee_25) {
	$net_subtotal = $subtotal + $total_fee_25;
} else {
	$net_subtotal = $subtotal - $discount;
}


$net_subtotal_html = wc_price($net_subtotal);
$net_with_gst = $net_subtotal + $tx;

/*
echo "tax: " . $tx;
echo "<br>";
echo 'discount: ' . $discount;
echo "<br>";
echo "net_subtotal: " . $net_subtotal;
echo "<br>";
echo "net_subtotal_html: " . $net_subtotal_html;
echo "<br>";
echo "net_with_gst: " . $net_with_gst;
echo "<br>";*/
?>
<table id="checkout_order_table" class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e('Booking Summary', 'woocommerce'); ?></th>
			<th class="product-total"><?php esc_html_e('Amount (INR)', 'woocommerce'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action('woocommerce_review_order_before_cart_contents');

		$unique_items = sizeof(WC()->cart->get_cart());

		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

			if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
				if ($_product->get_type() !== 'phive_booking') {
					$dsp = "display:none;";
				} else {
					$dsp = "";
				}
				?>
				<tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>"
					style="<?php echo $dsp; ?>">
					<td class="product-name p-name">

						<?php
						$product_id = $_product->get_id();
						$brand_image = "";
						$brands = get_the_terms($product_id, 'product_brand');
						$brand_image_id = '';
						if ( $brands && ! is_wp_error( $brands ) ) { 
						$brand_image_id = get_term_meta($brands[0]->term_id, 'thumbnail_id', true);
						}
						if ($brand_image_id) {
							$brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
						}
						?>

						<?php if (!empty($brand_image)) { ?>
							<img src="<?php echo esc_url($brand_image[0]); ?>" alt="<?php echo esc_attr($brand_title); ?>"
								width="35px" style="vertical-align: middle;margin-right: 3px;">
						<?php } ?>

						<?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;'; ?>
						<?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
					<td class="product-total">
						<?php
						$slots = get_booking_slots();
						$pr = (float) $_product->get_meta('_phive_booking_pricing_cost_per_unit');
						$full_price = $pr * $slots;
						if ($_product->get_type() !== 'phive_booking') {
							echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						} else {
							echo wc_price($full_price);
						}
						?>
					</td>
				</tr>
				<?php

				if ($_product->get_type() == 'phive_booking') {

					if (accordhub_is_discount_active() && $total_fee_25) { ?>
						<tr class="cart-discount light">
							<th><span>Less Discount (30%)</span></th>
							<td class="disc_price_td"><?php echo wc_price($total_fee_25); ?></td>
						</tr>
					<?php } else {
						$slots = get_booking_slots();
						$pr = (float) $_product->get_meta('_phive_booking_pricing_cost_per_unit');
						if ($slots == 2) {
							$dis_price = ($pr * $slots) * 0.1;
							?>
							<tr class="cart-discount light coupon-<?php //echo esc_attr(sanitize_title($code)); ?>">
								<th><span>Bulk Discount (10%)</span></th>
								<td class="disc_price_td">-<?php echo wc_price($dis_price); ?></td>
							</tr>
							<?php
						}
						if ($slots == 3) {
							$dis_price = ($pr * $slots) * 0.15;
							?>
							<tr class="cart-discount light coupon-<?php //echo esc_attr(sanitize_title($code)); ?>">
								<th><span>Bulk Discount (15%)</span></th>
								<td class="disc_price_td">-<?php echo wc_price($dis_price); ?></td>
							</tr>
							<?php
						}
					}

					foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
						<tr class="cart-discount light coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
							<th><span class="fw-600">Coupon: <span
										class="upper fw-600"><?php echo esc_attr(sanitize_title($code)); ?></span></span>
								<a href="<?php echo esc_url(add_query_arg('remove_coupon', urlencode($code), defined('WOOCOMMERCE_CHECKOUT') ? wc_get_checkout_url() : wc_get_cart_url())); ?>"
									class="woocommerce-remove-coupon" style="color: red;font-size:80%;text-transform:capitalize"
									data-coupon="<?php echo esc_attr($code); ?>"><?php _e('[Remove]', 'woocommerce'); ?></a><br><small
									style="text-transform:none; font-weight:normal;">
									<?php $c = new WC_Coupon($code);
									echo $c->get_discount_type() === 'percent' ? floatval($c->get_amount()) . '%' : wc_price($c->get_amount()); ?>
									Discount (Applied To Room Fee)
								</small>
							</th>
							<td class="disc_price_td"><?php wc_cart_totals_coupon_html($coupon); ?></td>
						</tr>
					<?php endforeach; ?>

					<?php
					if ($unique_items > 1) {
						$arrow = '<img width="15px"
									src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAABHElEQVR4nO3SMSiFYRSA4QcRiZSUUpSyKIvBYDKYLBYWk81iNNnvZLTYTBYWi8lgMhgsyqIUpZSUSES4Ur+SIpf/3vvdnKfOeOq8fR8hhBBCCCGEEHJXQDHxKfw0ZimBY4tfzHIpr1KHlQSOLn6aVdSX+sXeFtYSOL6YzQYa/NLb4noCEZto9EdN2KpixDaa5aQFO1WI2EWrnLVjr4IRe2hTJh3Yr0DEATqVWRcOyxhxhG4V0oPjMkScok+F9eIkx4gz9KuSAZznEHGBQVU2hMs/RFxhWCJGcPOLiOtsNymjuC0h4g5jEjWO+x9EPGBC4ibx+E3EE6bViKns4M8Rz5hRY2azw98jXjCnRs1/CFlQ4xazCSGEEEL4b14BnfLMQvEtlakAAAAASUVORK5CYII="
									alt="sort-down">';
					} else {
						$arrow = '';
					}
					echo '<tr class="cart_item addons-head cart-subtotal"><td colspan="2"><div class="addon-head"><div style="display: flex;align-items: flex-start;">Add-Ons<a id="open-extra-popup" class="edit-svg" href=""><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" role="img"><title>Plus</title><g fill="none"stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></g></svg></a></div><a href="#" class="toggle-addon">' . $arrow . '</a></div></td></tr>';

				}
			}
		}
		$remarks = WC()->session->get('addon_category_remarks');
		if (!empty($remarks) && !empty(array_filter($remarks))) {
			$output = [];

			// Heading row
			$output[] = '<tr class="cart_item cart_remark"><td class="product-name" colspan="2"><div><p class="r-title">Remarks</p>';

			foreach ($remarks as $category_slug => $remark) {
				if (!empty($remark)) {
					// ✅ Only show if products from category are in cart
					$in_cart = false;
					foreach (WC()->cart->get_cart() as $cart_item) {
						if (has_term($category_slug, 'product_cat', $cart_item['product_id'])) {
							$in_cart = true;
							break;
						}
					}

					if ($in_cart) {
						$category = get_term_by('slug', $category_slug, 'product_cat');
						$category_name = $category ? esc_html($category->name) : ucfirst($category_slug);

						$output[] = "<p><span>" . $category_name . ":</span> " . esc_html($remark) . "</p>";
					}
				}
			}
			$output[] = '</div></td></tr>';


			if (!empty($output)) {
				echo implode('', $output);
			}
		}
		if ($addons_total > 0) {
			?>
			<tr class=" cart-subtotal addon_total_tr">
				<th>
					<?php _e('Add-Ons Subtotal', 'textdomain'); ?>
				</th>
				<td class="product-total" data-title="<?php esc_attr_e('Add-Ons Subtotal', 'textdomain'); ?>">
					<?php echo wc_price($addons_total); ?>
				</td>
			</tr>
			<?php
		}
		do_action('woocommerce_review_order_after_cart_contents');
		?>
	</tbody>
	<tfoot class="main-checkout">

		<!-- <tr class="cart-subtotal">
			<th><?php //esc_html_e('Sub-total', 'woocommerce'); ?></th>
			<td><?php //wc_cart_totals_subtotal_html(); ?></td>
		</tr> -->

		<tr class="cart-subtotal blue">
			<th>Booking Total</th>
			<td class="order-total"><?php echo $net_subtotal_html; ?></td>
		</tr>
		<tr class="cart-subtotal">
			<td colspan="2">
				<div class="divide"><span class="line"></span><span class="text-title">Split Payment - Your
						Share</span><span class="line"></span></div>
			</td>
		</tr>
		<tr class="shared-total">
			<th><?php esc_html_e('Your Split Share', 'woocommerce'); ?><br>
				<div class="split-share-info" style="text-transform:none; font-weight:normal; font-size:12px;"></div>
			</th>
			<td class="shared-amount"></td>
		</tr>
		<?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()): ?>
			<?php if ('itemized' === get_option('woocommerce_tax_total_display')): ?>
				<?php foreach (WC()->cart->get_tax_totals() as $code => $tax): // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<?php if ($tax->label === 'GST'):
						//print_r($tax);
						?>
						<tr class="tax-rate light tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
							<th>CGST @9%</th>
							<td class="gst" data-org="<?php echo $tax->amount / 2; ?>">+₹<?php echo $tax->amount / 2; ?></td>
						</tr>
						<tr class="tax-rate light tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
							<th>SGST @9%</th>
							<td class="gst">+₹<?php echo $tax->amount / 2; ?></td>
						</tr>
					<?php else: ?>
						<tr class="tax-rate light tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
							<th><?php echo esc_html($tax->label); ?></th>
							<td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else: ?>
				<tr class="tax-total">
					<th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>
		<tr class="shared-total-final cart-subtotal">
			<td class="shared-amount-final" data-org="<?php echo $net_with_gst; ?>" colspan="2">
				<div class="last-row">
					<div style="text-align: left;">
						<small style="text-transform:none; font-weight:normal;">
							Total Payable
						</small>
						<br>
						<div class="final-price"><?php echo $net_with_gst; ?></div>
					</div>
					<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order"
						value="Pay Now" data-value="Pay Now">
						Pay Now
					</button>
				</div>
				<div id="checkout-timer"
					style="color:#ffffff; font-size:13px; font-weight:600; text-align:right; width:100%;"></div>
			</td>
			<!-- <td class="shared-amount-final" data-org="<?php //echo $net_with_gst; ?>">
				₹<?php //echo wp_kses_post($net_with_gst); ?></td> -->
			<!-- <td class="confirm_btn_td">

			</td> -->
		</tr>
		<?php do_action('woocommerce_review_order_before_order_total'); ?>


		<?php do_action('woocommerce_review_order_after_order_total'); ?>

	</tfoot>
</table>