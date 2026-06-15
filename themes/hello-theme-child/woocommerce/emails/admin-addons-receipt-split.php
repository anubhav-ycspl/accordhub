<?php
if (!defined('ABSPATH'))
    exit;

// Source Order for Address
$source_order = isset($billing_order) && $billing_order ? $billing_order : $parent_order;
$booking = get_booking_details($parent_order);
// Address Variables
$billing_first_name = $source_order->get_billing_first_name();
$billing_last_name = $source_order->get_billing_last_name();
$billing_address_1 = $source_order->get_billing_address_1();
$billing_address_2 = $source_order->get_billing_address_2();
$billing_city = $source_order->get_billing_city();
$billing_state = $source_order->get_billing_state();
$billing_postcode = $source_order->get_billing_postcode();
$billing_country = $source_order->get_billing_country();
$billing_email = $source_order->get_billing_email();
$billing_phone = $source_order->get_billing_phone();
$billing_company = $source_order->get_billing_company();
$billing_gstin = $source_order->get_meta('_billing_gstin');
$order_user_id = $source_order->get_user_id();
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
$invoice_date = date('F j, Y');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Accordhub Add-ons Invoice</title>
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
                <td style="width:20%;">
                    <hr style="border: 1px solid #D4D7E3;vertical-align:middle;">
                </td>
                <td>
                    <div
                        style="font-size:16pt; font-weight:600; color: #1763B9;line-height:1; text-align:center; display:block; width:100%;">
                        PAYMENT RECEIPT</div>
                </td>
                <td style="width:20%;">
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
                        <?php if (!empty($billing_company)): ?>
                            <b>Business:</b> <?php echo esc_html($billing_company); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($billing_gstin)): ?>
                            <b>GSTIN:</b> <?php echo esc_html($billing_gstin); ?><br>
                        <?php endif; ?>
                        <?php
                        $parts = [];
                        $address = trim($billing_address_1 . ' ' . $billing_address_2);
                        if (!empty($address))
                            $parts[] = $address;
                        $location_parts = array_filter([$billing_city, $state_name, $billing_postcode]);
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
                                <?php echo $source_order->get_meta('_unique_apl_id'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td
                                style="padding:6px 10px; border:.5px solid #D4D7E3; background-color: #1763B9; color:white; font-weight:600; width:40%;line-height:1">
                                Receipt Date</td>
                            <td
                                style="padding:6px 10px; border:.5px solid #D4D7E3; background-color: #F4FAFF; text-align:left; font-weight:600;line-height:1">
                                <?php echo $invoice_date; ?>
                            </td>
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
                    style="border:.5px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:65%;">
                    Item & Description</th>
                <th
                    style="border:.5px solid #D4D7E3; padding:8px; background-color: #1763B9; color:white; font-weight:600; text-align:center; width:30%;">
                    Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 1;
            foreach ($items as $item):
                $product_name = $item['name'];
                $qty = $item['qty'];
                // display_total is already processed in functions.php based on Tax setting
                $formatted_total = wc_price($item['display_total']);

                // Get brand terms
                $brand_image = '';
                $product_id = $item['product_id'];
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
                    <td style="text-align:center; border:.5px solid #D4D7E3; padding:8px; color: #2e2e2e;">
                        <?php echo $index; ?>
                    </td>
                    <td style="border:.5px solid #D4D7E3; padding:8px;">
                        <span
                            style="color: #191919; text-transform:uppercase;"><strong><?php echo $brand_image; ?><?php echo esc_html($product_name); ?></strong></span>
                        <span style="color: #2e2e2e;"> &times; <?php echo esc_html($qty); ?></span>
                    </td>
                    <td style="border:.5px solid #D4D7E3; padding:6px 20px 6px 8px; text-align:right; color: #2e2e2e;">
                        <?php echo $formatted_total; ?>
                    </td>
                </tr>
                <?php $index++; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="width:100%; margin-top:10px; display:inline-block; height: auto;">
        <div style="width:60%; display:inline-block; vertical-align:top; margin-left:2%; float: right; color: #2e2e2e;">
            <table style="width:100%; border-collapse:collapse;">

                <!-- <?php if ($prices_include_tax): ?>
                    <tr>
                        <td style="padding:8px 10px; text-align:right; font-size:10pt;font-weight:600; color: #2e2e2e;">
                            Sub-total:</td>
                        <td style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt;font-weight:600;">
                            <?php //echo wc_price($base_gross_total); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td style="padding:8px 10px; text-align:right; font-size:10pt;font-weight:600; color: #2e2e2e;">
                            Sub-total:</td>
                        <td style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt;font-weight:600;">
                            <?php //echo wc_price($base_net_subtotal); ?>
                        </td>
                    </tr>
                <?php endif; ?> -->
                <?php $sub = $base_net_subtotal; ?>
                <?php if (!empty($discount) && $discount > 0): ?>
                    <?php $sub = $base_net_subtotal - $discount; ?>
                    <tr>
                        <td
                            style="padding:8px 10px; text-align:right; font-size:10pt;color: #2e2e2e;border-bottom:.5px solid #D4D7E3;">
                            Discount (<?php echo esc_html($coupon_code); ?>%)
                        </td>
                        <td
                            style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt;color: #2e2e2e;border-bottom:.5px solid #D4D7E3;">
                            -<?php echo wc_price($discount); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td
                        style="padding:8px 10px; text-align:right; font-size:10pt;font-weight:600; color: #2e2e2e; ">
                        Add-Ons Total</td>
                    <td
                        style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt;font-weight:600; ">
                        <?php echo wc_price($sub); ?>
                    </td>
                </tr> 
                <tr>
                    <td
                        style="padding:8px 10px; text-align:right; font-size:10pt;font-weight:600; color: #2e2e2e; border-top:.5px solid #D4D7E3;">
                        Your Split Share</td>
                    <td
                        style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt;font-weight:600; border-top:.5px solid #D4D7E3;">
                        <?php echo wc_price($sub / $booking['payers']); ?>
                    </td>
                </tr>
                <?php $gst = ($sub / $booking['payers']) * 0.18; ?>
                <?php $final = ($sub / $booking['payers']) + $gst; ?>
                <tr>
                    <td
                        style="padding:8px 10px; text-align:right; font-size:10pt; color: #2e2e2e; border-top:.5px solid #D4D7E3;">
                        CGST @9%
                    </td>
                    <td
                        style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt; border-top:.5px solid #D4D7E3;">
                        +<?php echo wc_price($gst / 2); ?>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding:8px 10px; text-align:right; font-size:10pt; color: #2e2e2e; border-top:.5px solid #D4D7E3;">
                        SGST @9%
                    </td>
                    <td
                        style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt; border-top:.5px solid #D4D7E3;">
                        +<?php echo wc_price($gst / 2); ?>
                    </td>
                </tr>

                <tr>
                    <td
                        style="padding:8px 10px 8px 10px; text-align:right; font-size:10pt; font-weight:600; color:#2e2e2e; border-top:.5px solid #D4D7E3;">
                        Total Payable:
                    </td>
                    <td
                        style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt; font-weight:600; color:#2e2e2e; border-top:.5px solid #D4D7E3;">
                        <?php echo wc_price($final); ?>
                    </td>
                </tr> 
                <?php if (!empty($share_amount)): ?>
                    <tr>
                        <td
                            style="padding:8px 10px 8px 10px; text-align:right; font-size:10pt; font-weight:600; background-color: #1763B9; color:white; border-top:0px solid #0f4c90;">
                            Amount Paid</td>
                        <td
                            style="padding:8px 20px 8px 10px; text-align:right; font-size:10pt; font-weight:600; background-color: #1763B9; color:white; border-top:0px solid #0f4c90;">
                            <?php echo wc_price($share_amount); ?>
                        </td>
                    </tr>
                <?php endif; ?>

            </table>
        </div>
    </div>
</body>

</html>