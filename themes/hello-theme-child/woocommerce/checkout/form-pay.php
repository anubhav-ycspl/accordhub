<?php
/**
 * Pay for order form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.2.0
 */

defined('ABSPATH') || exit;

$totals = $order->get_order_item_totals(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$order_id = $order->get_id();
$order_button_text = "Pay Now";
?>

<form id="order_review" method="post">

	<?php
	if ($order->get_meta('group_parent_order')) {

		$p_order = wc_get_order($order->get_meta('group_parent_order'));

		// echo '<pre style="height:400px;overflow:auto;">';
		// 	print_r($p_order);
		// echo '</pre>';

		

		$p_totals = $p_order->get_order_item_totals();
		$meta_data = $p_order->get_meta_data(); // WC_Order object method
		$orig = $p_order->get_meta('group_original_total');
		$first_name = $p_order->get_billing_first_name();
		$last_name = $p_order->get_billing_last_name();
		$billing_full_name = $first_name . ' ' . $last_name;
		// echo "<pre>";
		// print_r($meta);
		?>
		<style>
			button#place_order {
				float: none !important;
				margin: auto;
				display: block;
			}

			.woocommerce-billing-fields {
				max-width: 60%;
				padding: 20px;
				border: 1px solid #dadada;
			}

			.saved-cart-item {
				width: 40%;
				margin-bottom: 0;
			}

			#payment {
				background-color: transparent !important;
			}

			.woocommerce #payment #place_order {
				border-radius: 38px;
				padding: 13px 18px;
			}

			.child-pay {
				display: flex;
				gap: 0px;
				margin-top: 40px;
			}

			.saved-cart-item .main-heading {
				font-size: 16px;
				color: #191919;
				margin-bottom: 20px;
			}

			.saved-cart-item .heading {
				color: #191919;
			}

			.woocommerce-checkout #payment div.form-row {
				padding: 1em 0 0;
				margin: 0;
			}

			.addon-box,
			.remark-box {
				color: #2e2e2e;
				font-size: 14px;
			}

			.child-pay .woocommerce-billing-fields {
				padding: 0 30px;
				border: none;
			}

			.child-pay .woocommerce-billing-fields .form-wrap {
				background-color: #ffffff;
				border-radius: 12px;
				height: max-content;
				padding: 30px;
				display: inline-block;
				box-shadow: 0px 0px 10px #0000000f;
			}



			.saved-cart-item img {
				border-radius: 15px;
				aspect-ratio: 10 / 5;
				object-fit: cover;
			}

			div#payment {
				padding: 0px !important;
			}

			span.dis_applied {
				font-size: 90% !important;
			}

			@media only screen and (max-width:767px) {
				span.dis_applied {
					font-size: 14px !important;
				}
			}
		</style>
		<?php 
			$product_details = get_booking_details($p_order); 
			$discount_applied_25_per = $p_order->get_meta('_accordhub_discount_applied');
		?>
		
		<?php 
			// echo '<pre>';
			// 	print_r($product_details); 
			// echo '</pre>';
		?>

		<div class="child-pay">
			<div class="woocommerce-billing-fields" id="customer_details">
				<div class="form-wrap">
					<h3><?php esc_html_e('Billing details', 'woocommerce'); ?></h3>
					<?php
					$checkout = WC()->checkout();
					foreach ($checkout->get_checkout_fields('billing') as $key => $field) {
						woocommerce_form_field($key, $field, $checkout->get_value($key));
					}
					?>
				</div>
				<div id="cancel_details" class="" style="margin-top: 20px;">
					<h3>Cancellation Policy</h3>
					<ul class="cp_ul">
						<li>Since this split payment was initiated by <?php echo $billing_full_name; ?>, please contact them
							for cancellation.</li>
						<li>Cancellations made more than 72 hours before the hearing receive a 50% refund.</li>
						<li>Cancellations within 72 hours and no-shows are fully chargeable and non-refundable.</li>
						<li>Split-payment bookings must be completed by all parties within 24 hours; failing which the
							booking is
							cancelled and all paid amounts are refunded.</li>
						<li>Add-ons already used are non-refundable.</li>
						<li>Eligible refunds are processed within 7 business days.</li>
					</ul>
					<p><a href="/cancellation-and-refund-policy/" target="_blank">Read full policy here</a></p>
				</div>
			</div>
			<div class="saved-cart-item">
				<?php if (count($p_order->get_items()) > 0): ?>

					<?php foreach ($p_order->get_items() as $item_id => $item): ?>
						<?php $product = $item->get_product(); ?>
						<?php $type = $product->get_type(); ?>
						<?php if ($type == 'phive_booking') {
							$image_url = $product_details['image'];
							$name = $product_details['room'];
							$case_id = $item->get_meta('Case ID');
							$case_name = $item->get_meta('Case Title');
							$booked_from = $item->get_meta('phive_display_time_from')[0];
							$booked_to = $item->get_meta('phive_display_time_to')[0];
							$date2 = date('F j, Y', strtotime($booked_from));
							$time_from = date('g:i a', strtotime($booked_from));
							$time_to = date('g:i a', strtotime($booked_to));
							$booked_datetime = $date2 . ' (' . $time_from . ' – ' . $time_to . ')';
							?>
							<!-- <img src="<?php //echo $image_url; ?>" alt=""> -->
						<?php } ?>
					<?php endforeach; ?>
				<?php endif; ?>
				<div class="inner_div secondary">
					<div class="productname" style="color: #2e2e2e;margin-bottom:15px"><span class="boldonly">Booking
							Reference No.</span><span
							class="boldonly"><?php echo $order->get_meta('group_parent_order'); ?></span></div>
					<div class="order_review_child">
						<div class="main-heading boldonly head"><span>Booking Summary</span><span>Amount (INR)</span>
						</div>
						<?php if (count($p_order->get_items()) > 0): ?>

							<div class="boldbrown productname">
								<span>Room Fee - <?php echo $product_details['room']; ?></span>
								<span><?php echo wc_price($product_details['full_price']); ?></span>
							</div>
							<div class="date-item"><?php echo $product_details['datetime']; ?></div>
						<?php endif; ?>
						<?php if ($product_details['slots'] > 1 && !$discount_applied_25_per): ?>
							<hr>
							<div class="heading">
								<span> Bulk Discount (<?php echo $product_details['bulk_discount']; ?>) </span>
								<span style="    color: green !important; font-weight: 600 !important;">-
									<?php echo wc_price($product_details['bulk_discount_amount']); ?>
								</span>
							</div>
						<?php endif; ?>

						<?php if( $discount_applied_25_per ){ ?>
							<hr>
							<div class="heading">
								<span><?php echo $product_details['discount_applied_25_text'] ?></span>
								<span style="    color: green !important; font-weight: 600 !important;">-
									<?php echo wc_price($product_details['discount_applied_25_price']); ?>
								</span>
							</div>
						<?php } ?>

						<hr>
						<?php foreach ($p_order->get_coupons() as $item_id => $item): ?>
							<?php
							$coupon_code = $item->get_code();
							// FIX: Added 'is_custom_ajax' => 1 to the remove URL
							$remove_url = add_query_arg(array(
								'remove_coupon_order_pay' => $coupon_code,
								'_wpnonce' => wp_create_nonce('remove-coupon-' . $coupon_code),
								'is_custom_ajax' => 1
							), $order->get_checkout_payment_url());
							$c = new WC_Coupon($coupon_code);
							$discount_text = $c->get_discount_type() === 'percent' ? floatval($c->get_amount()) . '%' : wc_price($c->get_amount());
							?>
							<div class="heading">
								<span class="fw-600">
									Coupon:
									<span class="upper fw-600"><?php echo esc_html($coupon_code); ?></span>
									<br><span class="dis_applied" style="text-transform:none; font-weight:normal;font-size:90%">
										<?php echo $discount_text; ?>
										discount (Applied To Room Fee)
									</span>
								</span>
								<span style="    color: green !important; font-weight: 600 !important;">-
									<?php echo wc_price($item->get_discount()); ?>
								</span>
							</div>
							<hr>
						<?php endforeach; ?>
						<?php $total_addon_price = 0; ?>
						<?php if (count($p_order->get_items()) > 1): ?>
							<div class="boldbrown heading"><span>Add-Ons</span> <a href="#" class="toggle-addon"><img
										width="15px"
										src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAABHElEQVR4nO3SMSiFYRSA4QcRiZSUUpSyKIvBYDKYLBYWk81iNNnvZLTYTBYWi8lgMhgsyqIUpZSUSES4Ur+SIpf/3vvdnKfOeOq8fR8hhBBCCCGEEHJXQDHxKfw0ZimBY4tfzHIpr1KHlQSOLn6aVdSX+sXeFtYSOL6YzQYa/NLb4noCEZto9EdN2KpixDaa5aQFO1WI2EWrnLVjr4IRe2hTJh3Yr0DEATqVWRcOyxhxhG4V0oPjMkScok+F9eIkx4gz9KuSAZznEHGBQVU2hMs/RFxhWCJGcPOLiOtsNymjuC0h4g5jEjWO+x9EPGBC4ibx+E3EE6bViKns4M8Rz5hRY2azw98jXjCnRs1/CFlQ4xazCSGEEEL4b14BnfLMQvEtlakAAAAASUVORK5CYII="
										alt="sort-down"></a></div>

							<div class="addon-box" style="display: none;">
								<?php foreach ($p_order->get_items() as $item_id => $item): ?>
									<?php $product = $item->get_product(); ?>
									<?php $type = $product->get_type(); ?>
									<?php $quantity = $item->get_quantity();
									$product_id = $product->get_id();
									$brand_image = "";
									$brands = get_the_terms($product_id, 'product_brand');
									$brand_image_id = get_term_meta($brands[0]->term_id, 'thumbnail_id', true);
									if ($brand_image_id) {
										$brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
									} ?>
									<?php if ($type !== 'phive_booking') { ?>
										<?php $price = $product->get_price(); ?>
										<?php $total_addon_price += $price * $quantity; ?>
										<div class="items">
											<span>
												<?php if (!empty($brand_image)) { ?>
													<img src="<?php echo esc_url($brand_image[0]); ?>"
														alt="<?php echo esc_attr($brand_title); ?>" width="35px"
														style="vertical-align: middle;margin-right: 3px;border-radius: 0;">
												<?php } ?>
												<?php echo $product->get_name(); ?> × <?php echo $quantity; ?></span>
											<span><?php echo wc_price($price * $quantity); ?></span>
										</div>
									<?php } ?>
								<?php endforeach; ?>
							</div>
							<div class="remark-box" style="display: none;">
								<hr>
								<div class="boldonly sub-heading">Remarks</div>
								<?php foreach ($meta_data as $meta) {
									if (strpos($meta->key, 'Remarks for ') === 0 && !empty($meta->value)) {
										$category_name = str_replace('Remarks for ', '', $meta->key);
										?>
										<div class="items">
											<span><?php echo esc_html($category_name); ?>:</span>
											<?php echo esc_html($meta->value); ?>
										</div>
										<?php
									}
								} ?>
							</div>
							<hr>
							<div class="heading">
								<span>Add-Ons Subtotal</span>
								<span><?php echo wc_price($total_addon_price); ?></span>
							</div>
							<hr>
						<?php endif; ?>
						<!-- <div class="boldbrown upper heading">
						<span>Sub-total</span>
						<span><?php //echo wc_price($total_addon_price + $product_details['price']); ?></span>
					</div> -->
						<!-- <?php //if ($product_details['discount'] > 0): ?>
							<hr>
							<div class=" upper heading">
								<span>Discount</span>
								<span>-<?php //echo wc_price($product_details['discount']); ?></span>
							</div>
						<?php //endif; ?> -->
						<!-- <hr> -->
						<?php $total_amount = $total_addon_price + $product_details['total_after_dis']; ?>
						<div class="boldbrown heading blue" style="">
							<span>Booking Amount</span>
							<span><?php echo wc_price($total_amount); ?></span>
						</div>
						<hr>
						<div class="boldbrown divider">
							<div class="divide"><span class="line"></span><span class="text-title">Split Payment - Your
									Share</span><span class="line"></span></div>
						</div>
						<?php $share = $total_amount / $product_details['payers'];
						$percent = 100 / $product_details['payers'];
						?>
						<div class="boldbrown heading">
							<span>Your Split Share<br>
								<div class="split-share-info"
									style="text-transform:none; font-weight:normal; font-size:12px;">1 of
									<?php echo $product_details['payers']; ?> <span
										style="vertical-align: text-bottom;font-weight: bold;">.</span>
									<?php echo $percent; ?>%
								</div>
							</span>
							<span><?php echo wc_price($total_amount / $product_details['payers']); ?></span>
						</div>
						<hr>
						<?php $gst = $share * .09; ?>
						<div class=" heading">
							<span>CGST @9%</span>
							<span>+<?php echo wc_price($gst); ?></span>
						</div>
						<hr>
						<div class=" heading">
							<span>SGST @9%</span>
							<span>+<?php echo wc_price($gst); ?></span>
						</div>
						<hr>
						<div class="boldbrown heading foot">
							<span><small style="text-transform:none; font-weight:normal;">Total Payable</small><br>
								<div class="final-price"><?php echo wc_price($p_order->get_total()); ?></div>
							</span>
							<span><button type="submit" class="button alt" id="place_order" value="Pay Now"
									data-value="Pay Now">Pay Now</button></span>
						</div>
					</div>
					<div id="payment">
						<?php if ($order->needs_payment()): ?>
							<ul class="wc_payment_methods payment_methods methods">
								<?php
								if (!empty($available_gateways)) {
									foreach ($available_gateways as $gateway) {
										wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
									}
								} else {
									echo '<li>';
									wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', esc_html__('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')), 'notice'); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
									echo '</li>';
								}
								?>
							</ul>
						<?php endif; ?>
						<div class="form-row">
							<input type="hidden" name="woocommerce_pay" value="1" />

							<?php wc_get_template('checkout/terms.php'); ?>

							<?php do_action('woocommerce_pay_order_before_submit'); ?>

							<?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') . '" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // @codingStandardsIgnoreLine ?>

							<?php do_action('woocommerce_pay_order_after_submit'); ?>

							<?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
						</div>
					</div>
				</div>

			</div>
		</div>
	<?php } elseif ($order->get_meta('addons_parent_order_id')) {
		$p_order = $order;
		$p_totals = $p_order->get_total();
		$meta_data = $p_order->get_meta_data(); // WC_Order object method
		$orig = $p_order->get_meta('group_original_total');
		$b_ref = $p_order->get_meta('addons_parent_order_id') ?? $order_id;

		// echo "<pre>";
		// print_r($meta);
	
		$addons_data_order = wc_get_order($b_ref);
		$booking = get_booking_details($addons_data_order);
		$addons_data = $addons_data_order->get_meta('_meeting_addons_data');
		$items_to_bill_ids = [];
		$grouped_items = [];
		$base_net_total = 0;  // Subtotal without Tax
		$base_tax_total = 0;  // Total Tax
		$base_gross_total = 0; // Total with Tax
	
		$prices_include_tax = wc_prices_include_tax();
		foreach ($addons_data as $row) {
			// Validation Checks
			if (
				(isset($row['status']) && $row['status'] === 'cancelled')
			) {
				continue;
			}
			if ($date_limit && isset($row['timestamp']) && $row['timestamp'] >= $date_limit) {
				continue;
			}

			$items_to_bill_ids[] = $row['id'];

			// Get Product Data
			$product_id = intval($row['product_id']);
			$qty = intval($row['qty']);
			$stored_price = floatval($row['price']);
			$product_name = $row['product_name'];
			$product = wc_get_product($product_id);

			// Calculate Line Totals
			if ($product) {
				$line_net = wc_get_price_excluding_tax($product, array('qty' => $qty, 'price' => $stored_price));
				$line_gross = wc_get_price_including_tax($product, array('qty' => $qty, 'price' => $stored_price));
				$line_tax = $line_gross - $line_net;
			} else {
				if ($prices_include_tax) {
					$line_gross = $stored_price * $qty;
					$line_net = $line_gross / 1.18;
					$line_tax = $line_gross - $line_net;
				} else {
					$line_net = $stored_price * $qty;
					$line_tax = $line_net * 0.18;
					$line_gross = $line_net + $line_tax;
				}
			}

			$base_net_total += $line_net;
			$base_tax_total += $line_tax;
			$base_gross_total += $line_gross;

			// Prepare Display Data for PDF Table
			$display_row_total = $prices_include_tax ? $line_gross : $line_net;

			if (isset($grouped_items[$product_name])) {
				$grouped_items[$product_name]['qty'] += $qty;
				$grouped_items[$product_name]['display_total'] += $display_row_total;
			} else {
				$grouped_items[$product_name] = [
					'name' => $product_name,
					'qty' => $qty,
					'display_total' => $display_row_total
				];
			}
		}

		$billed_items_details = array_values($grouped_items);
		$saved_coupon_code = $addons_data_order->get_meta('_meeting_addon_coupon');
		$payment_type = $addons_data_order->get_meta('group_payment_mode');
		$discount_amount = 0;
		$coupon = null;

		if (!empty($saved_coupon_code)) {
			$coupon = new WC_Coupon($saved_coupon_code);
			if ($coupon->get_id()) {
				// Calculate Discount Amount on the NET Subtotal
				$discount_amount = ($coupon->get_discount_type() === 'percent')
					? $base_net_total * ($coupon->get_amount() / 100)
					: $coupon->get_amount();
			}
		}

		// Ensure discount doesn't exceed total
		if ($discount_amount > $base_net_total) {
			$discount_amount = $base_net_total;
		}

		$add_total = $base_net_total;

		$after_discount = $add_total - $discount_amount;

		$gst = 0;
		if ($base_net_total > 0) {
			$discount_percentage = $discount_amount / $base_net_total;
			foreach ($addons_data as $row) {
				if (in_array($row['id'], $items_to_bill_ids)) {
					$product_id = intval($row['product_id']);
					$qty = intval($row['qty']);
					$stored_price = floatval($row['price']);
					$product = wc_get_product($product_id);

					$discounted_unit_price = $stored_price - ($stored_price * $discount_percentage);

					if ($product) {
						$line_net = wc_get_price_excluding_tax($product, array('qty' => $qty, 'price' => $discounted_unit_price));
						$line_gross = wc_get_price_including_tax($product, array('qty' => $qty, 'price' => $discounted_unit_price));
						$gst += ($line_gross - $line_net);
					} else {
						if ($prices_include_tax) {
							$line_gross = $discounted_unit_price * $qty;
							$line_net = $line_gross / 1.18;
							$gst += ($line_gross - $line_net);
						} else {
							$line_net = $discounted_unit_price * $qty;
							$line_tax = $line_net * 0.18;
							$gst += $line_tax;
						}
					}
				}
			}
		}

		// print_r($billed_items_details);
		// echo "<pre>";
	
		?>
		<style>
			button#place_order {
				float: none !important;
				margin: auto;
				display: block;
			}

			.woocommerce-billing-fields {
				max-width: 60%;
				padding: 20px;
				border: 1px solid #dadada;
			}

			.saved-cart-item {
				width: 40%;
				margin-bottom: 0;
			}

			#payment {
				background-color: transparent !important;
			}

			.woocommerce #payment #place_order {
				border-radius: 38px;
				padding: 13px 18px;
			}

			/* button#place_order {
																					color: transparent;
																					position: relative;
																				}

																				button#place_order:after {
																					content: "Pay Now ₹<?php //echo $p_order->get_total(); ?>";
																					position: absolute;
																					left: 50%;
																					top: 50%;
																					color: #ffffff;
																					transform: translate(-50%, -50%)
																				} */

			.child-pay {
				display: flex;
				gap: 0px;
				margin-top: 40px;
			}

			.saved-cart-item .main-heading {
				font-size: 16px;
				color: #191919;
				margin-bottom: 20px;
			}

			.saved-cart-item .heading {
				color: #191919;
			}

			.woocommerce-checkout #payment div.form-row {
				padding: 1em 0 0;
				margin: 0;
			}

			.addon-box,
			.remark-box {
				color: #2e2e2e;
				font-size: 14px;
			}

			.child-pay .woocommerce-billing-fields {
				padding: 0 30px;
				border: none;
			}

			.child-pay .woocommerce-billing-fields .form-wrap {
				background-color: #ffffff;
				border-radius: 12px;
				height: max-content;
				padding: 30px;
				display: inline-block;
				box-shadow: 0px 0px 10px #0000000f;
			}



			.saved-cart-item img {
				border-radius: 15px;
				aspect-ratio: 10 / 5;
				object-fit: cover;
			}

			div#payment {
				padding: 0px !important;
			}

			.inner_div {}
		</style>
		<div class="child-pay">
			<div class="woocommerce-billing-fields" id="customer_details">
				<div class="form-wrap">
					<h3><?php esc_html_e('Billing details', 'woocommerce'); ?></h3>
					<?php
					$checkout = WC()->checkout();
					foreach ($checkout->get_checkout_fields('billing') as $key => $field) {
						woocommerce_form_field($key, $field, $checkout->get_value($key));
					}
					?>
				</div>
			</div>
			<div class="saved-cart-item">
				<!-- <img src="<?php //echo $booking['image']; ?>" alt="Thumbnail"> -->
				<div class="inner_div secondary">
					<div class="  productname boldbrown"
						style="color: #2e2e2e;margin-bottom: 15px;margin-top: 0px !important;"><span
							class="boldonly">Booking
							Reference No.</span><span class="boldonly"><?php echo $b_ref; ?></span></div>
					<div class="order_review_child">
						<div class="main-heading  boldonly head" style="margin-bottom: 0px;"><span>Add-Ons
								Summary</span><span>Amount (INR)</span></div>
						<hr>
						<div class="addon-box">
							<?php foreach ($billed_items_details as $item): ?>
								<?php
								$product_name = $item['name'];
								$formatted_total = wc_price($item['display_total']);
								$qty = $item['qty'];
								$product_id = $product->get_id();
								$brand_image = "";
								$brands = get_the_terms($product_id, 'product_brand');
								$brand_image_id = get_term_meta($brands[0]->term_id, 'thumbnail_id', true);
								if ($brand_image_id) {
									$brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
								} ?>
								<div class="items">
									<span>
										<?php if (!empty($brand_image)) { ?>
											<img src="<?php echo esc_url($brand_image[0]); ?>"
												alt="<?php echo esc_attr($brand_title); ?>" width="35px"
												style="vertical-align: middle;margin-right: 3px;border-radius: 0;">
										<?php } ?>
										<?php echo $product_name; ?> × <?php echo $qty; ?>
									</span>
									<span><?php echo $formatted_total; ?></span>
								</div>

							<?php endforeach; ?>
						</div>

						<hr>
						<div class=" heading">
							<span>Add-Ons Subtotal</span>
							<span>
								<?php echo wc_price($add_total); ?>
							</span>
						</div>
						<?php if ($discount_amount > 0): ?>
							<hr>
							<div class=" heading">
								<span>Discount <!-- (<? php// echo $coupon ? $coupon->get_amount() : '0'; ?>%) --></span>
								<span style="color: green !important;font-weight: 600 !important;">
									-<?php echo wc_price($discount_amount); ?> </span>
							</div>
						<?php endif; ?>
						<hr>
						<div class="boldbrown heading blue">
							<span>Add-Ons Amount</span>
							<span>
								<?php echo wc_price($after_discount); ?>
							</span>
						</div>

						<?php if ($payment_type == 'group'):
							$percent = 100 / $booking['payers'];
							?>
							<div class="boldbrown divider">
								<div class="divide"><span class="line"></span><span class="text-title">Split Payment - Your
										Share</span><span class="line"></span></div>
							</div>

							<div class="boldbrown heading">
								<span>Your Split Share<br>
									<div class="split-share-info"
										style="text-transform:none; font-weight:normal; font-size:12px;">1 of
										<?php echo $booking['payers']; ?> <span
											style="vertical-align: text-bottom;font-weight: bold;">.</span>
										<?php echo $percent; ?>%
									</div>
								</span>
								<span>
									<?php echo wc_price($after_discount / $booking['payers']); ?>
								</span>
							</div>
						<?php endif; ?>
						<?php if ($gst > 0): ?>
							<hr>
							<div class=" heading">
								<span>CGST @9%</span>
								<span> +<?php echo wc_price($gst / (2 * $booking['payers'])); ?> </span>
							</div>
							<hr>
							<div class=" heading">
								<span>SGST @9%</span>
								<span> +<?php echo wc_price($gst / (2 * $booking['payers'])); ?> </span>
							</div>
						<?php endif; ?>
						<hr>
						<div class="boldbrown heading foot">
							<span><small style="text-transform:none; font-weight:normal;">Total Payable</small><br>
								<div class="final-price"><?php echo wc_price($p_order->get_total()); ?></div>
							</span>
							<span><button type="submit" class="button alt" id="place_order" value="Pay Now"
									data-value="Pay Now">Pay Now</button></span>
						</div>
					</div>
					<div id="payment">
						<?php if ($order->needs_payment()): ?>
							<ul class="wc_payment_methods payment_methods methods">
								<?php
								if (!empty($available_gateways)) {
									foreach ($available_gateways as $gateway) {
										wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
									}
								} else {
									echo '<li>';
									wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', esc_html__('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')), 'notice'); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
									echo '</li>';
								}
								?>
							</ul>
						<?php endif; ?>
						<div class="form-row">
							<input type="hidden" name="woocommerce_pay" value="1" />

							<?php wc_get_template('checkout/terms.php'); ?>

							<?php do_action('woocommerce_pay_order_before_submit'); ?>

							<?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') . '" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // @codingStandardsIgnoreLine ?>

							<?php do_action('woocommerce_pay_order_after_submit'); ?>

							<?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
						</div>
					</div>
				</div>
			</div>

			<?php
	} else {

		// --- PROCESS COUPON ACTIONS DIRECTLY ---
		// FIX: Explicitly check for our custom AJAX flag instead of relying on server headers
		$is_ajax_request = isset($_REQUEST['is_custom_ajax']) && $_REQUEST['is_custom_ajax'] == '1';
		$redirect_needed = false;

		// Handle Apply Coupon
		if (isset($_POST['apply_coupon_order_pay'], $_POST['coupon_code'], $_POST['security-coupon-order-pay']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security-coupon-order-pay'])), 'apply-coupon-order-pay')) {
			$code = wc_format_coupon_code(wp_unslash($_POST['coupon_code']));
			if (!empty($code)) {
				// Store and remove old coupons to ensure only one applies
				$applied_coupons = $order->get_coupons();
				$old_coupons = array();
				foreach ($applied_coupons as $item_id => $item) {
					$old_coupons[] = $item->get_code();
					$order->remove_coupon($item->get_code());
				}

				$result = $order->apply_coupon($code);
				if (is_wp_error($result)) {
					wc_add_notice($result->get_error_message(), 'error');
					// Restore old coupons if the new one is invalid
					foreach ($old_coupons as $old_code) {
						$order->apply_coupon($old_code);
					}
					$order->calculate_totals();
				} else {
					$order->calculate_totals();
					wc_add_notice(__('Coupon code applied successfully.', 'woocommerce'), 'success');
				}
				if (!$is_ajax_request) {
					$redirect_needed = true;
				}
			} else {
				wc_add_notice(__('Please enter a coupon code.', 'woocommerce'), 'error');
			}
		}

		// Handle Remove Coupon
		if (isset($_GET['remove_coupon_order_pay'], $_GET['_wpnonce'])) {
			$coupon_code = wc_clean(wp_unslash($_GET['remove_coupon_order_pay']));
			if (wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'remove-coupon-' . $coupon_code)) {
				$order->remove_coupon($coupon_code);
				$order->calculate_totals();
				wc_add_notice(__('Coupon has been removed.', 'woocommerce'), 'success');
				if (!$is_ajax_request) {
					$redirect_needed = true;
				}
			}
		}

		// Clean JS redirect for non-AJAX requests
		if ($redirect_needed) {
			echo '<script>window.location.href="' . esc_url_raw($order->get_checkout_payment_url()) . '";</script>';
			exit;
		}
		// --- END PROCESS COUPON ACTIONS ---
	
		$p_order = $order;
		$p_totals = $p_order->get_order_item_totals();
		$meta_data = $p_order->get_meta_data(); // WC_Order object method
		$orig = $p_order->get_meta('group_original_total');
		?>
			<style>
				button#place_order {
					float: none !important;
					margin: auto;
					display: block;
				}

				.woocommerce-billing-fields {
					max-width: 60%;
					padding: 20px;
					border: 1px solid #dadada;
				}

				.saved-cart-item {
					width: 40%;
					margin-bottom: 0;
				}

				#payment {
					background-color: transparent !important;
				}

				.woocommerce #payment #place_order {
					border-radius: 38px;
					padding: 13px 18px;
				}

				/* button#place_order {
																																																		color: transparent;
																																																		position: relative;
																																																	}

																																																	button#place_order:after {
																																																		content: "Confirm & Pay ₹<?php //echo $p_order->get_total(); ?>";
																																																		position: absolute;
																																																		left: 50%;
																																																		top: 50%;
																																																		color: #ffffff;
																																																		transform: translate(-50%, -50%)
																																																	} */

				.child-pay {
					display: flex;
					gap: 0px;
					margin-top: 40px;
				}

				.saved-cart-item .main-heading {
					font-size: 16px;
					color: #191919;
					margin-bottom: 20px;
				}

				.saved-cart-item .heading {
					color: #191919;
				}

				.woocommerce-checkout #payment div.form-row {
					padding: 1em 0 0;
					margin: 0;
				}

				.addon-box,
				.remark-box {
					color: #2e2e2e;
					font-size: 14px;
				}

				.child-pay .woocommerce-billing-fields {
					padding: 0 30px;
					border: none;
				}

				.child-pay .woocommerce-billing-fields .form-wrap {
					background-color: #ffffff;
					border-radius: 12px;
					height: max-content;
					padding: 30px;
					display: inline-block;
					box-shadow: 0px 0px 10px #0000000f;
				}



				.saved-cart-item img {
					border-radius: 15px;
					aspect-ratio: 10 / 5;
					object-fit: cover;
				}

				div#payment {
					padding: 0px !important;
				}

				.e-woocommerce-notices-wrapper:has(.woocommerce-notices-wrapper:empty) {
					margin-top: 0 !important;
				}

				.e-coupon-box ul.woocommerce-error,
				.e-coupon-box .e-woocommerce-notices-wrapper {
					margin-bottom: 0 !important;
				}



				span.dis_applied {
					font-size: 90% !important;
				}

				@media only screen and (max-width:767px) {
					span.dis_applied {
						font-size: 14px !important;
					}
				}
			</style>

			<div class="child-pay">
				<div class="woocommerce-billing-fields" id="customer_details">
					<div class="form-wrap">
						<h3><?php esc_html_e('Billing details', 'woocommerce'); ?></h3>
						<?php
						$checkout = WC()->checkout();
						foreach ($checkout->get_checkout_fields('billing') as $key => $field) {
							woocommerce_form_field($key, $field, $checkout->get_value($key));
						}
						?>
					</div>
					<div id="cancel_details" class="" style="margin-top: 20px;">
						<h3>Cancellation Policy</h3>
						<ul class="cp_ul">
							<?php if ($p_order->get_meta('group_payment_mode') == 'group'): ?>
								<li>This booking is initiated by you, therefore, any further cancelation or
									changes can be managed by only you.</li>
							<?php endif; ?>
							<li>Cancellations made more than 72 hours before the hearing receive a 50% refund.</li>
							<li>Cancellations within 72 hours and no-shows are fully chargeable and non-refundable.</li>
							<li>Split-payment bookings must be completed by all parties within 24 hours; failing which the
								booking is
								cancelled and all paid amounts are refunded.</li>
							<li>Add-ons already used are non-refundable.</li>
							<li>Eligible refunds are processed within 7 business days.</li>
						</ul>
						<p><a href="/cancellation-and-refund-policy/" target="_blank">Read full policy here</a></p>
					</div>
				</div>
				<div class="saved-cart-item">
					<?php if (count($p_order->get_items()) > 0): ?>
						<?php 
							$product_details = get_booking_details($p_order);
							$discount_applied_25_per = $p_order->get_meta('_accordhub_discount_applied');

							// echo '<pre>';
							// 	print_r($product_details); 
							// echo '</pre>';
						
							foreach ($p_order->get_items() as $item_id => $item): ?>
							<?php $product = $item->get_product(); ?>
							<?php $type = $product->get_type(); ?>
							<?php if ($type == 'phive_booking') {
								$image_url = $product_details['image'];
								$name = $product_details['room'];
								$case_id = $item->get_meta('Case ID');
								$case_name = $item->get_meta('Case Title');
								$booked_from = $item->get_meta('phive_display_time_from')[0];
								$booked_to = $item->get_meta('phive_display_time_to')[0];
								$date2 = date('F j, Y', strtotime($booked_from));
								$time_from = date('g:i a', strtotime($booked_from));
								$time_to = date('g:i a', strtotime($booked_to));
								$booked_datetime = $date2 . ' (' . $time_from . ' – ' . $time_to . ')';
								?>
							<?php } ?>
						<?php endforeach; ?>
					<?php endif; ?>
					<div class="inner_div main">
						<div class=" productname" style="color: #2e2e2e;margin-bottom: 15px;"><span class="boldonly">Booking
								Reference No.</span><span class="boldonly"><?php echo $order->get_ID(); ?></span></div>
						<div class="order_review_child">
							<div class="main-heading boldonly head"><span>Booking Summary</span><span>Amount (INR)</span>
							</div>
							<?php if (count($p_order->get_items()) > 0): ?>

								<div class="boldbrown  productname">
									<span>Room Fee - <?php echo $product_details['room']; ?></span>
									<span><?php echo wc_price($product_details['full_price']); ?></span>
								</div>
								<div class="date-item"><?php echo $product_details['datetime']; ?></div>
							<?php endif; ?>

							<?php if ($product_details['slots'] > 1 && !$discount_applied_25_per ): ?>
								<hr>
								<div class="heading">
									<span> Bulk Discount (<?php echo $product_details['bulk_discount']; ?>) </span>
									<span style="    color: green !important; font-weight: 600 !important;">-
										<?php echo wc_price($product_details['bulk_discount_amount']); ?>
									</span>
								</div>
							<?php endif; ?>

							<?php if( $discount_applied_25_per ){ ?>
								<hr>
								<div class="heading">
									<span><?php echo $product_details['discount_applied_25_text'] ?></span>
									<span style="color: green !important; font-weight: 600 !important;">-
										<?php echo wc_price($product_details['discount_applied_25_price']); ?>
									</span>
								</div>
							<?php } ?>

							<?php foreach ($order->get_coupons() as $item_id => $item): ?>
								<?php
								$coupon_code = $item->get_code();
								// FIX: Added 'is_custom_ajax' => 1 to the remove URL
								$remove_url = add_query_arg(array(
									'remove_coupon_order_pay' => $coupon_code,
									'_wpnonce' => wp_create_nonce('remove-coupon-' . $coupon_code),
									'is_custom_ajax' => 1
								), $order->get_checkout_payment_url());
								$c = new WC_Coupon($coupon_code);
								$discount_text = $c->get_discount_type() === 'percent' ? floatval($c->get_amount()) . '%' : wc_price($c->get_amount());
								?>
								<hr>
								<div class=" heading">
									<span class="fw-600">
										Coupon: <span class="upper fw-600"><?php echo esc_html($coupon_code); ?></span>
										<?php if ($p_order->get_meta('_wc_order_attribution_source_type') !== 'admin'): ?>
											<a href="<?php echo esc_url($remove_url); ?>" class="woocommerce-remove-coupon"
												style="color:red; font-size:80%; text-decoration:none;text-transform:capitalize"><?php _e('[Remove]', 'woocommerce'); ?></a>
										<?php endif; ?>
										<br><span class="dis_applied"
											style="text-transform:none; font-weight:normal;"><?php echo $discount_text; ?>
											discount (Applied To Room Fee)</span>
									</span>
									<span
										style="color: green !important; font-weight: 600 !important;">-<?php echo wc_price($item->get_discount()); ?></span>
								</div>
							<?php endforeach; ?>
							<hr>
							<?php $total_addon_price = 0; ?>

							<?php if (count($p_order->get_items()) > 1): ?>
								<div class="boldbrown heading"><span>Add-Ons</span> <a href="#" class="toggle-addon"><img
											width="15px"
											src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAABHElEQVR4nO3SMSiFYRSA4QcRiZSUUpSyKIvBYDKYLBYWk81iNNnvZLTYTBYWi8lgMhgsyqIUpZSUSES4Ur+SIpf/3vvdnKfOeOq8fR8hhBBCCCGEEHJXQDHxKfw0ZimBY4tfzHIpr1KHlQSOLn6aVdSX+sXeFtYSOL6YzQYa/NLb4noCEZto9EdN2KpixDaa5aQFO1WI2EWrnLVjr4IRe2hTJh3Yr0DEATqVWRcOyxhxhG4V0oPjMkScok+F9eIkx4gz9KuSAZznEHGBQVU2hMs/RFxhWCJGcPOLiOtsNymjuC0h4g5jEjWO+x9EPGBC4ibx+E3EE6bViKns4M8Rz5hRY2azw98jXjCnRs1/CFlQ4xazCSGEEEL4b14BnfLMQvEtlakAAAAASUVORK5CYII="
											alt="sort-down"></a></div>

								<div class="addon-box" style="display:none">
									<?php foreach ($p_order->get_items() as $item_id => $item): ?>
										<?php $product = $item->get_product(); ?>
										<?php $product_id = $product->get_id();
										$brand_image = "";
										$brands = get_the_terms($product_id, 'product_brand');
										$brand_image_id = get_term_meta($brands[0]->term_id, 'thumbnail_id', true);
										if ($brand_image_id) {
											$brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
										} ?>
										<?php $type = $product->get_type(); ?>
										<?php $quantity = $item->get_quantity(); ?>
										<?php if ($type !== 'phive_booking') { ?>
											<?php $price = $product->get_price(); ?>
											<?php $total_addon_price += $price * $quantity; ?>
											<div class="items">
												<span><?php if (!empty($brand_image)) { ?>
														<img src="<?php echo esc_url($brand_image[0]); ?>"
															alt="<?php echo esc_attr($brand_title); ?>" width="35px"
															style="vertical-align: middle;margin-right: 3px;width: 35px;border-radius: 0;">

													<?php } ?>
													<?php echo $product->get_name(); ?> × <?php echo $quantity; ?></span>
												<span><?php echo wc_price($price * $quantity); ?></span>
											</div>
										<?php } ?>
									<?php endforeach; ?>
								</div>
								<div class="remark-box" style="display:none">
									<hr>
									<div class="boldonly sub-heading">Remarks</div>
									<?php foreach ($meta_data as $meta) {
										if (strpos($meta->key, 'Remarks for ') === 0 && !empty($meta->value)) {
											$category_name = str_replace('Remarks for ', '', $meta->key);
											?>
											<div class="items">
												<span><?php echo esc_html($category_name); ?>:</span>
												<?php echo esc_html($meta->value); ?>
											</div>
											<?php
										}
									} ?>
								</div>
								<hr>
								<div class=" heading">
									<span>Add-Ons Subtotal</span>
									<span><?php echo wc_price($total_addon_price); ?></span>
								</div>
								<hr>
							<?php endif; ?>


							<?php $total_amount = $product_details['total_after_dis']; ?>
							<div class="boldbrown heading blue">
								<span>Booking Amount</span>
								<span><?php echo wc_price($total_amount); ?></span>
							</div>
							<hr>
							<?php $share = $total_amount / $product_details['payers']; ?>
							<?php if ($product_details['payers'] > 1):
								$percent = 100 / $product_details['payers'];
								?>
								<div class="boldbrown divider">
									<div class="divide"><span class="line"></span><span class="text-title">Split Payment - Your
											Share</span><span class="line"></span></div>
								</div>
								<div class="boldbrown heading">
									<span>Your Split Share<br>
										<div class="split-share-info"
											style="text-transform:none; font-weight:normal; font-size:12px;">1 of
											<?php echo $product_details['payers']; ?> <span
												style="vertical-align: text-bottom;font-weight: bold;">.</span>
											<?php echo $percent; ?>%
										</div>
									</span>
									<span><?php echo wc_price($total_amount / $product_details['payers']); ?></span>
								</div>
								<hr>
							<?php endif; ?>
							<?php $gst = $share * .09; ?>
							<div class=" heading">
								<span>CGST @9%</span>
								<span>+<?php echo wc_price($gst); ?></span>
							</div>
							<hr>
							<div class=" heading">
								<span>SGST @9%</span>
								<span>+<?php echo wc_price($gst); ?></span>
							</div>
							<?php if ($product_details['payers'] > 1): ?>
								<hr>
								<div class="boldbrown heading foot">
									<span><small style="text-transform:none; font-weight:normal;">Total Payable</small><br>
										<div class="final-price"><?php echo wc_price($p_order->get_total()); ?></div>
									</span>
									<span><button type="submit" class="button alt" id="place_order" value="Pay Now"
											data-value="Pay Now">Pay Now</button></span>
								</div>
							<?php else: ?>
								<hr>
								<div class="boldbrown heading foot">
									<span><small style="text-transform:none; font-weight:normal;">Total Payable</small><br>
										<div class="final-price"><?php echo wc_price($p_order->get_total()); ?></div>
									</span>
									<span><button type="submit" class="button alt" id="place_order" value="Pay Now"
											data-value="Pay Now">Pay Now</button></span>
								</div>
							<?php endif; ?>
						</div>
						<div id="payment">
							<?php if ($order->needs_payment()): ?>
								<ul class="wc_payment_methods payment_methods methods">
									<?php
									if (!empty($available_gateways)) {
										foreach ($available_gateways as $gateway) {
											wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
										}
									} else {
										echo '<li>';
										wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', esc_html__('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')), 'notice'); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
										echo '</li>';
									}
									?>
								</ul>
							<?php endif; ?>
							<?php if ($p_order->get_meta('_wc_order_attribution_source_type') !== 'admin'): ?>
								<ul class="available-offers-list 11" style="list-style: none; padding: 0;">
									<li class="offer-item"
										style=" margin: 25px 0 0px 0; justify-content: space-between; align-items: center; padding: 15px; border: 1px dashed #ccc; border-radius: 10px;">
										<div class="coupon-item"><img decoding="async" class="coupon-img"
												src="<?php echo get_stylesheet_directory_uri(); ?>/images/marketing.png"
												alt="Coupon Icon">
											<div class="popup-trigger"><span class="" style="font-size: 14px; width: 100%;">Use
													Code
													<strong>ACCRD20</strong> to get 20% off room charges.</span><a href="#"
													class="toggle-coupon-btn" style="">View All Coupons</a></div><button
												type="button" class="button apply-quick-coupon" data-code="accrd20"
												style="width: auto;" fdprocessedid="fuceee">Apply</button>
										</div>
										<div class="coupon-notice-wrapper"
											style="display:none; width: 100%; flex-basis: 100%; margin-top: 10px;"></div>
									</li>
								</ul>
								<div class="e-coupon-box" style="margin-bottom: 2em;">
									<p class="e-woocommerce-coupon-nudge e-checkout-secondary-title">Have a
										coupon? <a href="#" class="custom-show-coupon-form">Click here to enter your coupon
											code</a>
									</p>
									<div class="custom-coupon-anchor" style="display: none;">
										<label class="e-coupon-anchor-description">If you have a coupon code, please apply it
											below.</label>
										<div class="form-row">
											<div class="coupon-container-grid">
												<div class="col coupon-col-1 ">
													<input type="text" class="input-text" placeholder="Coupon code"
														id="custom_coupon_code" value="">
												</div>
												<div class="col coupon-col-2">
													<button class="woocommerce-button button e-apply-coupon" type="button"
														id="apply_custom_coupon_btn">Apply</button>
												</div>
											</div>
										</div>
										<div class="clear"></div>
									</div>

									<div class="e-woocommerce-notices-wrapper" style="margin-top:1em;">
										<div class="woocommerce-notices-wrapper"><?php wc_print_notices(); ?></div>
									</div>
									<?php wp_nonce_field('apply-coupon-order-pay', 'security-coupon-order-pay'); ?>
								</div>
							<?php endif; ?>
							<div class="form-row">
								<input type="hidden" name="woocommerce_pay" value="1" />

								<?php wc_get_template('checkout/terms.php'); ?>

								<?php do_action('woocommerce_pay_order_before_submit'); ?>

								<?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') . '" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // @codingStandardsIgnoreLine ?>

								<?php do_action('woocommerce_pay_order_after_submit'); ?>

								<?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
							</div>

							<?php
							// ========================================================================
							// INJECT AVAILABLE OFFERS
							// ========================================================================
							$args = array(
								'post_type' => 'shop_coupon',
								'post_status' => 'publish',
								'posts_per_page' => -1,
								'meta_query' => array(
									array(
										'key' => '_visible_on_checkout',
										'value' => 'yes',
									)
								)
							);
							$visible_coupons = get_posts($args);

							if (!empty($visible_coupons)) {
								echo '<div class="coupon-model" style="display:none">';
								echo '<div class="available-offers-container" style="margin-bottom: 20px;">';
								echo '<div class="scroll-content">';
								echo '<div class="icon-heading">';
								echo '<h3 style="margin-bottom: 10px;">Coupons</h3>';
								echo '<a href="#" class="toggle-coupon-btn"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 72 72">
<path d="M 19 15 C 17.977 15 16.951875 15.390875 16.171875 16.171875 C 14.609875 17.733875 14.609875 20.266125 16.171875 21.828125 L 30.34375 36 L 16.171875 50.171875 C 14.609875 51.733875 14.609875 54.266125 16.171875 55.828125 C 16.951875 56.608125 17.977 57 19 57 C 20.023 57 21.048125 56.609125 21.828125 55.828125 L 36 41.65625 L 50.171875 55.828125 C 51.731875 57.390125 54.267125 57.390125 55.828125 55.828125 C 57.391125 54.265125 57.391125 51.734875 55.828125 50.171875 L 41.65625 36 L 55.828125 21.828125 C 57.390125 20.266125 57.390125 17.733875 55.828125 16.171875 C 54.268125 14.610875 51.731875 14.609875 50.171875 16.171875 L 36 30.34375 L 21.828125 16.171875 C 21.048125 15.391875 20.023 15 19 15 z"></path>
</svg></a>';
								echo '</div>';
								echo '<ul class="available-offers-list 3" style="list-style: none; padding: 0;">';


								$c_count = 0;
								foreach ($visible_coupons as $coupon_post) {
									$coupon_obj = new WC_Coupon($coupon_post->ID);
									$c_code = $coupon_obj->get_code();

									$amount = $coupon_obj->get_amount();
									$discount_type = $coupon_obj->get_discount_type();
									$discount_text = ($discount_type === 'percent') ? $amount . '%' : wc_price($amount);

									$c_count++;
									$display_style = ($c_count > 3) ? '' : '';
									$item_class = ($c_count > 3) ? 'offer-item' : 'offer-item';

									// SAFE CHECK FOR APPLIED COUPON
									$is_applied = false;
									$applied_coupons = $order->get_coupons();
									// echo "<pre>";
									// print_r($applied_coupons);
									foreach ($applied_coupons as $applied_coupon) {
										if (strcasecmp($applied_coupon->get_code(), $c_code) === 0) {
											$is_applied = true;
											break;
										}
									}

									if ($is_applied == true) {
										$btn_text = 'Remove';
										$btn_class = 'button apply-quick-coupon is-applied';
									} else {
										$btn_text = 'Apply';
										$btn_class = 'button apply-quick-coupon';
									}

									echo '<li class="' . esc_attr($item_class) . '" style=" margin-bottom: 10px;  justify-content: space-between;' . esc_attr($display_style) . ' align-items: center; padding: 15px; border: 1px dashed #ccc; border-radius: 10px;">';
									echo '<div class="coupon-item">';
									echo '<img class="coupon-img" src="' . get_stylesheet_directory_uri() . '/images/marketing.png" alt="Coupon Icon">';
									echo '<span class="" style="font-size: 14px; width: 70%;">Use Code <strong>' . esc_html(strtoupper($c_code)) . '</strong> to get ' . esc_html($discount_text) . ' off room charges.</span>';
									echo '<button type="button" class="' . esc_attr($btn_class) . '" data-code="' . esc_attr($c_code) . '" style="width: auto;">' . esc_html($btn_text) . '</button>';
									echo '</div>';
									echo '<div class="coupon-notice-wrapper" style="display:none; width: 100%; flex-basis: 100%; margin-top: 10px;"></div>';
									echo '</li>';
								}

								echo '</ul>';

								if ($c_count > 3) {
									//echo '<a href="#" id="toggle-offers-btn" style="">View All</a>';
								}
								echo '</div>';
								echo '</div>';
								echo '</div>';
								echo '</div>';
							}
							// ========================================================================
							?>



						</div>
					</div>
				</div>
			</div>

			<script>
				if (typeof window.wooCustomCouponAjaxBound === 'undefined') {
					window.wooCustomCouponAjaxBound = true;
					jQuery(function ($) {
						var $body = $('body');

						// 1. Show/Hide Coupon Field (using safe non-conflicting classes)
						$(document).off('click', '.custom-show-coupon-form').on('click', '.custom-show-coupon-form', function (e) {
							e.preventDefault();
							$('.custom-coupon-anchor').slideToggle(400, function () {
								$('#custom_coupon_code').focus();
							});
						});

						// 2. Apply Coupon via custom AJAX call
						$(document).off('click', '#apply_custom_coupon_btn').on('click', '#apply_custom_coupon_btn', function (e) {
							e.preventDefault();
							$('.coupon-notice-wrapper').hide().empty();
							$('.my-custom-coupon-alert').hide().empty();
							var coupon_code = $('#custom_coupon_code').val();
							var nonce = $('#security-coupon-order-pay').val();

							if (!coupon_code) return;

							var $container = $('.order_review_child');
							// CHECK: Was the popup open?
							var isPopupOpen = $('.coupon-model').is(':visible');

							$container.block({
								message: null,
								overlayCSS: { background: '#fff', opacity: 0.6 }
							});

							$.ajax({
								type: 'POST',
								url: window.location.href,
								data: {
									apply_coupon_order_pay: 1,
									coupon_code: coupon_code,
									'security-coupon-order-pay': nonce,
									is_custom_ajax: 1 // FIX: Ensure PHP knows it's an AJAX request
								},
								success: function (response) {
									var newContent = $(response).find('.order_review_child').html();

									if (newContent) {
										$container.html(newContent);
										var notices = $(response).find('.woocommerce-error, .woocommerce-message, .woocommerce-info');
										notices.each(function () {
											var notice = $(this);
											var textLowerCase = notice.text().toLowerCase();
											var rawText = notice.text().trim(); // Get the actual text to put in our custom element

											// Check if the notice contains the word "coupon"
											if (textLowerCase.indexOf('coupon') !== -1) {
												var elementorCouponBox = $('.e-coupon-box');

												if (elementorCouponBox.length) {
													// Determine if it's an error or success message to style differently
													var isError = notice.hasClass('woocommerce-error');
													var borderColor = isError ? '#e2401c' : '#0f834d';
													var bgColor = isError ? '#ffe9e9' : '#e5f9e7';
													var textColor = isError ? '#e2401c' : '#0f834d';

													// 1. Remove any existing custom messages to prevent duplicates
													elementorCouponBox.find('.my-custom-coupon-alert').remove();

													// 2. Build your completely custom HTML element here
													var customElement = '<div class="my-custom-coupon-alert" style="margin-top: 15px; padding: 12px 15px; background-color: ' + bgColor + '; color: ' + textColor + ';  font-size: 13px; border-radius: 8px;">' + rawText + '</div>';

													// 3. Append your custom element below the Elementor form
													elementorCouponBox.append(customElement);

													// 4. Hide the original WooCommerce notice at the top of the page
													notice.hide();
													$('input#custom_coupon_code').val('');
												}
											}
										});
									} else {
										window.location.reload();
									}
								},
								error: function () {
									window.location.reload();
								},
								complete: function () {
									$container.unblock();
									$('.custom-coupon-anchor').slideToggle(400, function () { });
								}
							});
						});

						// 3. Remove Coupon via custom AJAX call
						$(document).off('click', '.woocommerce-remove-coupon').on('click', '.woocommerce-remove-coupon', function (e) {
							e.preventDefault();
							$('.coupon-notice-wrapper').hide().empty();
							$('.my-custom-coupon-alert').hide().empty();
							var remove_url = $(this).attr('href');

							var $container = $('.order_review_child');
							// CHECK: Was the popup open?
							var isPopupOpen = $('.coupon-model').is(':visible');

							$container.block({
								message: null,
								overlayCSS: { background: '#fff', opacity: 0.6 }
							});

							$.ajax({
								type: 'GET',
								url: remove_url,
								success: function (response) {
									var newContent = $(response).find('.order_review_child').html();
									if (newContent) {
										$container.html(newContent);
										syncQuickCouponButtons();
										// RE-SHOW POPUP if it was open
										if (isPopupOpen) {
											$('.coupon-model').show();
											//jQuery('body').toggleClass('no-scroll');
										}
									} else {
										window.location.reload();
									}
								},
								error: function () {
									window.location.reload();
								},
								complete: function () {
									$container.unblock();
								}
							});
						});

						// 4. Handle Quick Coupon Apply/Remove
						$(document).off('click', '.apply-quick-coupon').on('click', '.apply-quick-coupon', function (e) {
							$('.coupon-notice-wrapper').hide().empty();
							$('.my-custom-coupon-alert').hide().empty();
							e.preventDefault();
							var $button = $(this);
							var couponCode = $button.data('code');
							$button.addClass('processing');

							// Select ALL buttons on the page that correspond to this exact coupon code
							var $allButtons = $('.apply-quick-coupon[data-code="' + couponCode + '"]');

							if ($button.hasClass('is-applied')) {
								// Find the corresponding remove link generated by PHP
								var $removeLink = $('.woocommerce-remove-coupon').filter(function () {
									return $(this).attr('href').indexOf('remove_coupon_order_pay=' + couponCode) !== -1;
								});

								if ($removeLink.length) {
									// Update text and disable ALL matching buttons
									$allButtons.text('Removing').prop('disabled', true);
									var remove_url = $removeLink.attr('href');

									var $container = $('.order_review_child');
									// QUICK COUPON usually means popup IS open
									var isPopupOpen = $('.coupon-model').is(':visible');

									$container.block({
										message: null,
										overlayCSS: { background: '#fff', opacity: 0.6 }
									});

									$.ajax({
										type: 'GET',
										url: remove_url,
										success: function (response) {
											var newContent = $(response).find('.order_review_child').html();
											if (newContent) {
												$container.html(newContent);
												var mess = $(response).find('.woocommerce-message').html();
												var htmlMsg = '<div class="custom-coupon-remove-msg" style="margin-bottom: 0; padding: 10px 15px; font-size: 13px; background-color: #fff3cd; color: #664d03;  border-radius: 8px;">' + mess + '</div>';
												$button.closest('li').find('.coupon-notice-wrapper').html(htmlMsg).show();
												if (isPopupOpen) $('.coupon-model').show();
											} else {
												window.location.reload();
											}
										},
										error: function () {
											window.location.reload();
										},
										complete: function () {
											$container.unblock();
											$button.removeClass('processing');

										}
									});
								}
							} else {
								var currentUrl = new URL(window.location.href);

								// Correctly add or update the coupon parameter
								currentUrl.searchParams.set('coupon', couponCode);
								$allButtons.text('Applying').prop('disabled', true);
								var apply_url = currentUrl.toString();

								var $container = $('.order_review_child');
								var $allButtons = $('.apply-coupon-button'); // Adjust selector as needed
								var isPopupOpen = $('.coupon-model').is(':visible');



								$container.block({
									message: null,
									overlayCSS: { background: '#fff', opacity: 0.6 }
								});

								$.ajax({
									type: 'GET',
									url: apply_url,
									success: function (response) {
										// We look for the updated order table AND the notices
										var $html = $(response);
										var newContent = $html.find('.order_review_child').html();
										var notice = $html.find('.woocommerce-error, .woocommerce-message').first().html();

										if (newContent) {
											$container.html(newContent);
											var mess = $(response).find('.woocommerce-message').html();
											var htmlMsg = '<div class="custom-coupon-remove-msg" style="margin-bottom: 0; padding: 10px 15px; font-size: 13px; background-color: #e5f9e7; color: #1e5631; border-radius: 8px;">' + mess + '</div>';
											$button.closest('li').find('.coupon-notice-wrapper').html(htmlMsg).show();
											if (isPopupOpen) $('.coupon-model').show();
										} else {
											window.location.reload();
										}
									},
									error: function () {
										window.location.reload();
									},
									complete: function () {
										$container.unblock();
										$allButtons.text('Apply').prop('disabled', false);
										$button.removeClass('processing');
									}
								});
							}
						});
						// 5. Update the "Pay for order" button text dynamically based on the active cart total
						function updateDynamicPlaceOrderText() {
							var currentAmount = $('span.tot').last().text().trim() || '';
							var dynamicText = 'Pay Now ' + currentAmount;
							$('#place_order').text(dynamicText).val(dynamicText).attr('data-value', dynamicText);
						}

						// 6. Sync Quick Coupon Buttons State
						function syncQuickCouponButtons() {
							$('.apply-quick-coupon').each(function () {
								var $btn = $(this);
								var code = $btn.data('code');

								// Check if a remove link exists for this specific coupon in the updated DOM
								var isApplied = $('.woocommerce-remove-coupon').filter(function () {
									var href = $(this).attr('href');
									return href && href.indexOf('remove_coupon_order_pay=' + code) !== -1;
								}).length > 0;

								// Re-enable the button and update text/class based on whether it is currently applied
								if (isApplied) {
									$btn.addClass('is-applied').text('Remove').prop('disabled', false);
								} else {
									$btn.removeClass('is-applied').text('Apply').prop('disabled', false);
								}
							});
						}

						// Run immediately on load
						updateDynamicPlaceOrderText();
						syncQuickCouponButtons();

						// Re-run whenever WooCommerce or custom AJAX completes (e.g., after applying/removing coupons)
						$(document).ajaxComplete(function () {
							updateDynamicPlaceOrderText();
							syncQuickCouponButtons();
						});

						$(document).ajaxComplete(function () {
							updateDynamicPlaceOrderText();
							syncQuickCouponButtons();
							// const couponBox = document.querySelector('.e-coupon-box');
							// const offersContainer = document.querySelector('.available-offers-container .scroll-content');
							// if (couponBox && offersContainer) {
							// 	offersContainer.appendChild(couponBox);
							// }
						});
					});
				}
				// document.addEventListener("DOMContentLoaded", function () {
				// 	const couponBox = document.querySelector('.e-coupon-box');
				// 	const offersContainer = document.querySelector('.available-offers-container .scroll-content');

				// 	if (couponBox && offersContainer) {
				// 		offersContainer.appendChild(couponBox);
				// 	}
				// });
			</script>
			<?php
	}
	/**
	 * Triggered from within the checkout/form-pay.php template, immediately before the payment section.
	 *
	 * @since 8.2.0
	 */
	do_action('woocommerce_pay_order_before_payment');
	if ($order->get_meta('group_parent_orderxyz')) {
		?>

			<div id="payment">
				<?php if ($order->needs_payment()): ?>
					<ul class="wc_payment_methods payment_methods methods">
						<?php
						if (!empty($available_gateways)) {
							foreach ($available_gateways as $gateway) {
								wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
							}
						} else {
							echo '<li>';
							wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', esc_html__('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')), 'notice'); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
							echo '</li>';
						}
						?>
					</ul>
				<?php endif; ?>
				<div class="form-row">
					<input type="hidden" name="woocommerce_pay" value="1" />

					<?php wc_get_template('checkout/terms.php'); ?>

					<?php do_action('woocommerce_pay_order_before_submit'); ?>

					<?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') . '" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // @codingStandardsIgnoreLine ?>

					<?php do_action('woocommerce_pay_order_after_submit'); ?>

					<?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
				</div>
			</div>
		<?php } ?>
</form>
</div>
</div>