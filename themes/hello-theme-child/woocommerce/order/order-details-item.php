<?php
/**
 * Order Item Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-item.php.
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

if (!defined('ABSPATH')) {
	exit;
}

if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
	return;
}
if($product->get_type() == 'phive_booking'){
	$cls = 'pr';
}else{
	$cls = '';
}
?>
<tr
	class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item ', $item, $order)); ?> <?php echo $product ? $product->get_type() : ''; ?>">

	<td class="woocommerce-table__product-name product-name index<?php echo $ind; ?>">
		<?php
		$is_visible = $product && $product->is_visible();
		$product_permalink = apply_filters('woocommerce_order_item_permalink', $is_visible ? $product->get_permalink($item) : '', $item, $order);

		echo wp_kses_post(apply_filters('woocommerce_order_item_name', $item->get_name(), $item, $is_visible));


		$qty = $item->get_quantity();
		$refunded_qty = $order->get_qty_refunded_for_item($item_id);

		if ($refunded_qty) {
			$qty_display = '<del>' . esc_html($qty) . '</del> <ins>' . esc_html($qty - ($refunded_qty * -1)) . '</ins>';
		} else {
			$qty_display = esc_html($qty);
		}

		echo apply_filters('woocommerce_order_item_quantity_html', ' <strong class="product-quantity ' . $product->get_type() . '">' . sprintf('&times;&nbsp;%s', $qty_display) . '</strong>', $item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		
		do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);

		wc_display_item_meta($item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		
		do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
		?>
	</td>

	<td class="woocommerce-table__product-total product-total index<?php echo $ind; ?>">
		<span class="<?php echo $cls; ?>"><?php echo $order->get_formatted_line_subtotal($item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</td>

</tr>
<?php
if ($product->get_type() === 'phive_booking') { ?>
<tr
	class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item ', $item, $order)); ?> addons">

	<td colspan="2" class="woocommerce-table__product-name product-name" style="border-bottom: 1px solid #dadada !important;">
		<div class="woocommerce-table__product-name product-name boldbrown upper">Add-ons</div>
	</td> 

</tr>
<?php }
?>

<?php if ($show_purchase_note && $purchase_note): ?>

	<tr class="woocommerce-table__product-purchase-note product-purchase-note">

		<td colspan="2">
			<?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</td>

	</tr>

<?php endif; ?>