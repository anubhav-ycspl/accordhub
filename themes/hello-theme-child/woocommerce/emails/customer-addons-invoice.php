<?php
if (!defined('ABSPATH'))
	exit;

$order_number = $order->get_order_number();
$orig = $order;
$order_date = wc_format_datetime($orig->get_date_paid());
$main_order_date = wc_format_datetime($orig->get_date_created());


// Start with parent order items
$items = $order->get_items();

// Fetch all child orders linked to this parent order
$child_orders = wc_get_orders([
	'type'       => 'shop_order',
	'limit'      => -1,
	'status'     => array_keys(wc_get_order_statuses()),
	'meta_key'   => '_parent_order_id',
	'meta_value' => $order->get_id(),
]);

// Merge all child order items into the same array
if (!empty($child_orders)) {
	foreach ($child_orders as $child_order) {
		foreach ($child_order->get_items() as $child_item_id => $child_item) {
			$items[$child_item_id] = $child_item; // add to combined items
		}
	}
}


$billing = $orig->get_address('billing');
$shipping = $orig->get_address('shipping'); 

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

foreach ($items as $item_id => $item) {
	$product = $item->get_product();
	if ($product && $product->get_type() === 'phive_booking') {
		$phive_items[$item_id] = $item;
		$booked_from = $item->get_meta('phive_display_time_from')[0];
		$booked_to = $item->get_meta('phive_display_time_to')[0];
		$date2 = date('F j, Y', strtotime($booked_from));
		$time_from = date('g:i a', strtotime($booked_from));
		$time_to = date('g:i a', strtotime($booked_to));
		$booked_datetime = $date2 . ' (' . $time_from . ' – ' . $time_to . ')';
	} else {
		$addon_items[$item_id] = $item;
		$addon_total += $item->get_total(); // sum of all addon products
	}
}
// Merge phive_booking items first
$ordered_items = array_merge($phive_items, $addon_items);
$index = 1;
?>
<!DOCTYPE html>
<html>

<head>
	<title>Payment Receipt</title>
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

<body style="font-family:Inter,sans-serif; font-size:8pt;margin:20px">

	<table style="width:100%; border-collapse:collapse; margin-bottom:5px;">
		<tr>
			<td style="width:60%; padding-right:15px; vertical-align:top;">
				<table style="width:100%; border-collapse:collapse;">
					<tr>
						<td>
							<div
								style="background-color: #1763B9; width:70px; text-align:center; line-height:0px; display:block; margin-right:10px;padding: 15px;box-sizing: border-box;border-radius: 50%;">
								<img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/10/AH.png" alt=""
									width="70px">
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
						PAYMENT RECEIPT</div>
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
					<div style="font-weight:600; margin-bottom:5px; font-size:10pt; color: #191919;">Bill To</div>
					<div style="font-weight:600; font-size:9pt; color: #1763B9;">
						<?php echo esc_html($billing_first_name . ' ' . $billing_last_name) . '<br>'; ?>
					</div>
					<div style="color: #2e2e2e; font-size:8pt;">
						<b>Business:</b> <?php echo esc_html($billing_company); ?><br>
						<b>GSTIN:</b> <?php echo esc_html($billing_gstin); ?><br>
						<?php
						echo $billing_address_1 . ' ' . $billing_address_2 . '<br>';
						echo $billing_city . ', ' . $state_name . ' - ' . $billing_postcode . ', ';
						echo $country_name . '<br>';
						echo 'Email: ' . esc_html($billing_email) . '<br>';
						echo 'Phone: ' . esc_html($billing_phone);
						?>
					</div>
					<br>
					<div style="font-weight:600; margin-bottom:5px; font-size:10pt; color: #191919;">Booking Details
					</div>
					<div style="color: #2e2e2e; font-size:8pt;">
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
								style="padding:6px 10px; border:1px solid #D4D7E3; background-color: #1763B9; color:white; font-weight:600; width:40%;line-height:1">
								Receipt #</td>
							<td
								style="padding:6px 10px; border:1px solid #D4D7E3; background-color: #F4FAFF; text-align:left; font-weight:600;line-height:1">
								<?php echo $orig->get_order_number(); ?>
							</td>
						</tr>
						<tr>
							<td
								style="padding:6px 10px; border:1px solid #D4D7E3; background-color: #1763B9; color:white; font-weight:600; width:40%;line-height:1">
								Receipt Date</td>
							<td
								style="padding:6px 10px; border:1px solid #D4D7E3; background-color: #F4FAFF; text-align:left; font-weight:600;line-height:1">
								<?php echo $order_date; ?>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>

	<table style="width:100%; border-collapse:collapse; margin-top:20px;">
		<thead>
			<tr>
				<th
					style="border:1px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:5%;">
					#</th>
				<th
					style="border:1px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:50%;">
					Item & Description</th>
				<!-- <th
					style="border:1px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:10%;">
					Qty</th>
				<th
					style="border:1px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:10%;">
					Rate</th> -->
				<th
					style="border:1px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:30%;">
					Amount</th>
			</tr>
		</thead>
		<tbody>

			<?php
			foreach ($items as $item_id => $item):
				$product_name = $item->get_name();
				$quantity = $item->get_quantity();
				$subtotal = $inv_order->get_formatted_line_subtotal($item);
				$price = wc_price($item->get_total() / $quantity);

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
							'style' => 'width:25px;height:auto;margin-right:8px;margin-top:8px;vertical-align:middle;border-radius: 2px;'
						]);*/
					}
				}  
				?>
				<tr style="background-color: #F4FAFF;">
					<td style="text-align:center; border:1px solid #D4D7E3; padding:8px;"><?php echo $index; ?></td>
					<td style="border:1px solid #D4D7E3; padding:8px;">
						<span style="font-weight:600;"><?php echo $brand_image; ?><?php echo $product_name; ?></span> x <?php echo $quantity; ?>
					</td>
					<!-- <td style="border:1px solid #D4D7E3; padding:8px; text-align:right;"><?php //echo $quantity; ?></td>
					<td style="border:1px solid #D4D7E3; padding:8px; text-align:right;"><?php //echo $price; ?></td> -->
					<td style="border:1px solid #D4D7E3; padding:8px; text-align:right;"><?php echo $subtotal; ?></td>
				</tr>
				<?php $index++; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	if ($orig->get_meta('group_parent_order') || $orig->get_meta('group_payment_mode') == 'group') {
		$total_label = 'Your Share';
	} else {
		$total_label = 'Total Booking Charges';
	}
	?>
	<div style="width:100%; margin-top:20px;">
		<div style="width:45%; display:inline-block; vertical-align:top; margin-left:2%;float: right;">
			<table style="width:100%; border-collapse:collapse;">
				<tr>
					<td style="padding:8px 10px; text-align:right;font-size:9pt;">Total</td>
					<td style="padding:8px 10px; text-align:right;font-size:9pt;">
						<?php echo wc_price($inv_order->get_subtotal()); ?>
					</td>
				</tr>
				<tr>
					<td
						style="padding:8px 10px; text-align:right; font-size:10pt;font-weight:600;border-top:1px solid #D4D7E3;line-height:1">
						<?php echo $total_label; ?>
					</td>
					<td
						style="padding:8px 10px; text-align:right; font-size:10pt;font-weight:600;border-top:1px solid #D4D7E3;line-height:1">
						<?php echo wc_price($inv_order->get_total()); ?>
					</td>
				</tr>
			</table>

			<table style="width:100%; border-collapse:collapse; margin-top:10px;">
				<tr>
					<td
						style="background-color: #1763B9; color:white; font-size:10pt; padding:10px;font-weight:600; text-align:right;line-height:1">
						Amount Paid
					</td>
					<td
						style="background-color: #1763B9; color:white; font-size:10pt; padding:10px;font-weight:600; text-align:right;line-height:1">
						<?php echo wc_price($inv_order->get_total()); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div style="width: 100%;margin-top: 20px;display: inline-block;">
		<table style="width:100%;">
			<tr>
				<td>
					<div style="width:100%; display: inline-table; vertical-align:bottom;margin-top:70px">
						<div style="font-size:8pt; color: #2e2e2e; padding-top:10px;text-align:left">We appreciate the
							opportunity to serve you.</div>
					</div>
				</td>
			</tr>
		</table>
	</div>
</body>

</html>