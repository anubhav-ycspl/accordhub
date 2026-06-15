<?php
if (!defined('ABSPATH'))
	exit;

$order_number = $order->get_order_number();
$orig = $order;
$order_date = wc_format_datetime($orig->get_date_paid()); // Receipt usually shows paid date
$main_order_date = wc_format_datetime($orig->get_date_created());

if ($order->get_meta('group_parent_order')) {
	$inv_order = wc_get_order($order->get_meta('group_parent_order'));
	$order_number = $order->get_meta('group_parent_order');
	$main_order_date = wc_format_datetime($inv_order->get_date_created());
} else {
	$inv_order = $order;
}
$booking = get_booking_details($inv_order);

$discount_applied_25_per = $booking['discount_applied_25_per'];

$billing = $orig->get_address('billing');
$shipping = $orig->get_address('shipping');
$items = $inv_order->get_items();

$order_user_id = $orig->get_user_id();

$billing_first_name = $order->get_billing_first_name();
$billing_last_name = $order->get_billing_last_name();
$billing_address_1 = $order->get_billing_address_1();
$billing_address_2 = $order->get_billing_address_2();
$billing_city = $order->get_billing_city();
$billing_state = $order->get_billing_state();
$billing_postcode = $order->get_billing_postcode();
$billing_country = $order->get_billing_country();
$billing_email = $order->get_billing_email();
$billing_phone = $order->get_billing_phone();
$billing_company = $order->get_billing_company();
$billing_gstin = $order->get_meta( '_billing_gstin' );

if ($order_user_id) {
    if (empty($billing_first_name)) {
        $billing_first_name = get_user_meta($order_user_id, 'billing_first_name', true);
    }
    if (empty($billing_last_name)) {
        $billing_last_name = get_user_meta($order_user_id, 'billing_last_name', true);
    }
    if (empty($billing_address_1)) {
        $billing_address_1 = get_user_meta($order_user_id, 'billing_address_1', true);
    }
    if (empty($billing_address_2)) {
        $billing_address_2 = get_user_meta($order_user_id, 'billing_address_2', true);
    }
    if (empty($billing_city)) {
        $billing_city = get_user_meta($order_user_id, 'billing_city', true);
    }
    if (empty($billing_state)) {
        $billing_state = get_user_meta($order_user_id, 'billing_state', true);
    }
    if (empty($billing_postcode)) {
        $billing_postcode = get_user_meta($order_user_id, 'billing_postcode', true);
    }
    if (empty($billing_country)) {
        $billing_country = get_user_meta($order_user_id, 'billing_country', true);
    }
    if (empty($billing_email)) {
        $billing_email = get_user_meta($order_user_id, 'billing_email', true);
    }
    if (empty($billing_phone)) {
        $billing_phone = get_user_meta($order_user_id, 'billing_phone', true);
    }
    if (empty($billing_company)) {
        $billing_company = get_user_meta($order_user_id, 'billing_company_name', true);
    }
    if (empty($billing_gstin)) {
        $billing_gstin = get_user_meta($order_user_id, 'billing_gstin', true);
    }
}
$country_name = WC()->countries->countries[$billing_country] ?? $billing_country;
$states = WC()->countries->get_states($billing_country);
$state_name = $states[$billing_state] ?? $billing_state;

$phive_items = [];
$addon_items = [];
$addon_total = 0;

// Segregate items
foreach ($items as $item_id => $item) {
	$product = $item->get_product();
	if ($product && $product->get_type() === 'phive_booking') {
		$phive_items[$item_id] = $item;
	} else {
		$addon_items[$item_id] = $item;
		$addon_total += $item->get_total();
	}
}

$index = 1;
?>
<!DOCTYPE html>
<html>

<head>
	<title>Accordhub Payment Receipt</title>
	<link rel="icon" href="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/cropped-Ellipse-5-1-192x192.png"
		sizes="192x192">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style>
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

		table td {
			vertical-align: middle;
		}

		span.woocommerce-Price-amount,
		span.woocommerce-Price-amount * {
			all: unset;
		}
	</style>
</head>

<body style="font-family:Inter,sans-serif; font-size:9pt;margin:20px">

	<table style="width:100%; border-collapse:collapse; margin-bottom:5px;">
		<tr>
			<td style="width:60%; padding-right:15px; vertical-align:top;">
				<table style="width:100%; border-collapse:collapse;">
					<tr>
						<td>
							<div
								style="background-color: #1763B9; width:49px; text-align:center; line-height:0px; display:block; margin-right:10px;padding: 15px;box-sizing: border-box;border-radius: 50%;">
								<img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/10/AH.png" alt=""
									width="49px">
							</div>
						</td>
						<td></td>
					</tr>
				</table>
			</td>
			<td style="width:40%; vertical-align:top;">
				<div style="text-align:right; line-height:1.4; font-size:8pt;">
					<div style="font-size:16pt; font-weight:600; color: #1763B9;">Accordhub Pvt. Ltd.</div>
					<div><b>GSTIN: 08AALCS2627K2ZY</b></div>
					<div>501-510, 5th Floor,</div>
					<div>Kailash Tower, Tonk Rd, Lalkothi,</div>
					<div>Jaipur, Rajasthan 302015</div>
				</div>
			</td>
		</tr>
	</table>

	<table style="width:100%;margin-bottom:20px;">
		<tbody>
			<tr>
				<td style="width:27%;">
					<hr style="border: 1px solid #D4D7E3;vertical-align:middle;">
				</td>
				<td>
					<div
						style="font-size:16pt; font-weight:600; color: #1763B9;line-height:1; text-align:center; display:block; width:100%;">
						PAYMENT RECEIPT
					</div>
				</td>
				<td style="width:27%;">
					<hr style="border: 1px solid #D4D7E3;vertical-align:middle;">
				</td>
			</tr>
		</tbody>
	</table>

	<table style="width:100%; border-collapse:collapse;">
		<tr>
			<td style="width:55%; vertical-align:top; padding-right:20px;">
				<div style="width:100%; margin-top:0;text-align: left;">
					<div style="font-weight:600; margin-bottom:5px; font-size:9pt; color: #191919;">Bill To</div>
					<div style="font-weight:600; font-size:9pt; color: #1763B9;">
						<?php echo esc_html($billing_first_name . ' ' . $billing_last_name) . '<br>'; ?>
					</div>
					<div style="color: #2e2e2e; font-size:9pt;">
						<b>Business:</b> <?php echo esc_html($billing_company); ?><br>
						<b>GSTIN:</b> <?php echo esc_html($billing_gstin); ?><br>
						<?php
						$parts = [];
						$address = trim($billing_address_1 . ' ' . $billing_address_2);
						if (!empty($address))
							$parts[] = $address;

						$location_parts = [];
						if (!empty($billing_city))
							$location_parts[] = $billing_city;
						if (!empty($state_name))
							$location_parts[] = $state_name;
						if (!empty($billing_postcode))
							$location_parts[] = $billing_postcode;
						if (!empty($location_parts))
							$parts[] = implode(', ', $location_parts);

						if (!empty($country_name))
							$parts[] = $country_name;
						echo implode('<br>', $parts);

						if (!empty($billing_email))
							echo '<br>Email: ' . esc_html($billing_email);
						if (!empty($billing_phone))
							echo '<br>Phone: ' . esc_html($billing_phone);
						?>
					</div>
					<br>
					<div style="font-weight:600; margin-bottom:5px; font-size:9pt; color: #191919;">Booking Details
					</div>
					<div style="color: #2e2e2e; font-size:9pt;">
						Booking Ref. No.: <?php echo $order_number; ?><br>
						Booking Date: <?php echo $main_order_date; ?>
					</div>
				</div>
			</td>
			<td style="width:45%; vertical-align:top;">
				<div style="width:100%; display:inline-block;">
					<table style="width:100%; border-collapse:collapse; margin-top:5px;">
						<tr>
							<td
								style="padding:6px 10px; border:.5px solid #D4D7E3; background-color: #1763B9; color:white; font-weight:600; width:40%;line-height:1">
								Receipt No.</td>
							<td
								style="padding:6px 10px; border:.5px solid #D4D7E3; background-color: #F4FAFF; text-align:left; font-weight:600;line-height:1">
								<?php echo $orig->get_meta('_unique_apl_id'); ?></td>
						</tr>
						<tr>
							<td
								style="padding:6px 10px; border:.5px solid #D4D7E3; background-color: #1763B9; color:white; font-weight:600; width:40%;line-height:1">
								Receipt Date</td>
							<td
								style="padding:6px 10px; border:.5px solid #D4D7E3; background-color: #F4FAFF; text-align:left; font-weight:600;line-height:1">
								<?php echo $order_date; ?></td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>

	<table style="width:100%; border-collapse:collapse; margin-top:20px; font-size:10pt">
		<thead>
			<tr style="font-weight:700;">
				<th
					style="border:.5px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:5%;">
					#</th>
				<th
					style="border:.5px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:50%;">
					Item & Description</th>
				<th
					style="border:.5px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:17%;">
					Amount</th>
			</tr>
		</thead>
		<tbody>
			<tr style="background-color: #F4FAFF;">
				<td style="text-align:center; border:.5px solid #D4D7E3; padding:8px;">
					1
				</td>
				<td style="border:.5px solid #D4D7E3; padding:8px;">
					<span style="color: #191919;text-transform:uppercase;"><strong>
							<?php echo $booking['room']; ?>
						</strong></span><br>
					<span style="color: #2e2e2e;">
						<?php if ($booking['datetime']) {
							echo '<span style="font-weight:600;">Booking Date & Time: </span>' . $booking['datetime'];
						} ?>
					</span>
				</td>
				<td style="border:.5px solid #D4D7E3; padding:6px 20px 6px 8px; text-align:right;color: #2e2e2e;">
					<?php echo wc_price($booking['full_price']); ?>
				</td>
			</tr>
			<?php if ($booking['addon_count'] > 0) { ?>
				<tr style="background-color: #F4FAFF;">
					<td style="text-align:center; border:.5px solid #D4D7E3; padding:6px 8px;"></td>
					<td colspan="2"
						style="color: #191919;text-transform:uppercase;border:.5px solid #D4D7E3; padding:6px 8px;">
						<strong>Add-ons</strong>
					</td>
				</tr>
				<?php foreach ($inv_order->get_items() as $item):
					$product = $item->get_product();
					if ($product->get_type() == 'phive_booking') {
						continue;
					}
					$product_name = $item->get_name();
					$quantity = $item->get_quantity();
					$price = $product->get_price();
					$subtotal = $price * $quantity;

					// Get brand terms
					$brand_image = '';
					$product_id = $item->get_product_id();
					$brands = get_the_terms($product_id, 'product_brand');
					if (!empty($brands) && !is_wp_error($brands)) {
						$brand = $brands[0];

						// Get brand image (term meta)
						$brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);

						if ($brand_image_id) {
							/*$brand_image = wp_get_attachment_image($brand_image_id, 'large', false, [
								'style' => 'width:25px;height:auto;margin-top:8px;margin-right:8px;vertical-align:middle;border-radius: 2px;'
							]);*/
						}
					}

					?>
					<tr style="background-color: #F4FAFF;">
						<td style="text-align:center; border:.5px solid #D4D7E3; padding:6px 8px;color: #2e2e2e;">
							<?php echo $index + 1; ?>
						</td>
						<td style="border:.5px solid #D4D7E3; padding:6px 8px;">
							<span style="color: #2e2e2e;"><?php echo $brand_image; ?><?php echo $product_name; ?></span> x <?php echo $quantity; ?>
						</td>
						<td style="border:.5px solid #D4D7E3; padding:6px 20px 6px 8px; text-align:right;color: #2e2e2e;">
							<?php echo wc_price($subtotal); ?>
						</td>
					</tr>
					<?php $index++; ?>
				<?php endforeach; ?>
			<?php } ?>
		</tbody>
	</table>

	<?php
	if ($orig->get_meta('group_parent_order') || $orig->get_meta('group_payment_mode') == 'group') {
		$total_label = 'Amount Payable (Your Split Share)';
	} else {
		$total_label = 'Amount Payable';
	}

	// Discount Logic
	$discount_total = $inv_order->get_total_discount();
    foreach ($orig->get_items('coupon') as $coupon_item) {

        $coupon = new WC_Coupon($coupon_item->get_code());

        if ($coupon->get_discount_type() === 'percent') {
            $percentage = $coupon->get_amount(); // 👉 10
            $discount_type = $coupon->get_discount_type();
        }
    }
	
	?>

	<div style="width:100%; margin-top:10px;display:inline-block;height: auto;">
		<div style="width:60%; display:inline-block; vertical-align:top; margin-left:2%;float: right;color: #2e2e2e;">
			<table style="width:100%; border-collapse:collapse;">
				<?php if ($booking['slots'] > 1 && $booking['bulk_discount_amount'] > 0  && !$discount_applied_25_per ): ?>
				<!-- <tr>
					<td style="padding:8px 10px; text-align:right;font-size:10pt;font-weight:600;color: #2e2e2e;border-bottom:.5px solid #D4D7E3;">Total Amount</td>
					<td style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;font-weight:600;border-bottom:.5px solid #D4D7E3;">
						<?php echo wc_price($booking['full_price']); ?>
					</td>
				</tr> -->
				<tr>
					<td style="padding:8px 10px; text-align:right;font-size:10pt;color: #2e2e2e;border-bottom:.5px solid #D4D7E3;">Bulk Discount (<?php echo $booking['bulk_discount']; ?>)</td>
					<td style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;border-bottom:.5px solid #D4D7E3;">
						-<?php echo wc_price($booking['bulk_discount_amount']); ?>
					</td>
				</tr>
				<?php endif; ?>
				<?php if ($booking['discount'] > 0 && !$discount_applied_25_per ): ?>
					<tr>
						<td
							style="padding:8px 10px; text-align:right;font-size:10pt;color: #2e2e2e;border-bottom:.5px solid #D4D7E3;">
							Coupon Discount (<?php echo $booking['disc_percentage']; ?>%)</td>
						<td
							style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;color: #2e2e2e;border-bottom:.5px solid #D4D7E3;">
							-<?php echo wc_price($booking['discount']); ?>
						</td>
					</tr>
				<?php endif; ?>
				
				<?php if ( $discount_applied_25_per ): ?>
					<tr>
						<td
							style="padding:8px 10px; text-align:right;font-size:10pt;color: #2e2e2e;border-bottom:.5px solid #D4D7E3;">
							<?php echo $booking['discount_applied_25_text']; ?></td>
						<td
							style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;color: #2e2e2e;border-bottom:.5px solid #D4D7E3;">
							-<?php echo wc_price($booking['discount_applied_25_price']); ?>
						</td>
					</tr>
				<?php endif; ?>

				<tr>
					<td
						style="padding:8px 10px; text-align:right;font-size:10pt;font-weight:600;color: #2e2e2e; ">
						Booking Total</td>
					<td
						style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;font-weight:600;color: #2e2e2e; ">
						<?php echo wc_price($booking['total_after_dis']); ?>
					</td>
				</tr>
				<?php if ($booking['payers'] > 1): ?>
				<tr>
					<td
						style="padding:8px 10px; text-align:right;font-size:10pt;font-weight:600;color: #2e2e2e;border-top:.5px solid #D4D7E3;">
						Your Split Share</td>
					<td
						style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;font-weight:600;color: #2e2e2e;border-top:.5px solid #D4D7E3;">
						<?php echo wc_price($booking['share_total']); ?>
					</td>
				</tr>
				<?php endif; ?>
				<?php if ($booking['cgst'] > 0): ?>
					<tr>
						<td
							style="padding:8px 10px; text-align:right;font-size:10pt;color: #2e2e2e;border-top:.5px solid #D4D7E3;">
							CGST @9%</td>
						<td
							style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;border-top:.5px solid #D4D7E3;">
							+<?php echo wc_price($booking['cgst']); ?>
						</td>
					</tr>
					<tr>
						<td
							style="padding:8px 10px; text-align:right;font-size:10pt;color: #2e2e2e;border-top:.5px solid #D4D7E3;">
							SGST @9%</td>
						<td
							style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;border-top:.5px solid #D4D7E3;">
							+<?php echo wc_price($booking['sgst']); ?>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td
						style="padding:8px 10px; text-align:right;font-size:10pt;font-weight:600;color: #2e2e2e;border-top:.5px solid #D4D7E3;">
						Total Payable</td>
					<td
						style="padding:8px 20px 8px 10px; text-align:right;font-size:10pt;font-weight:600;color: #2e2e2e;border-top:.5px solid #D4D7E3;">
						<?php echo wc_price($inv_order->get_total()); ?>
					</td>
				</tr>
				<tr>
					<td
						style="padding:8px 10px; text-align:right; font-size:10pt;font-weight:600;background-color: #1763B9; color:white;border-top:.5px solid #D4D7E3;line-height:1">
						Total Amount Paid
					</td>
					<td
						style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt;font-weight:600;background-color: #1763B9; color:white;border-top:.5px solid #D4D7E3;line-height:1">
						<?php
						// Calculates (Subtotal - Discount + Tax)
						echo wc_price($inv_order->get_total());
						?>
					</td>
				</tr> 
			</table>
		</div>
	</div>
</body>

</html>