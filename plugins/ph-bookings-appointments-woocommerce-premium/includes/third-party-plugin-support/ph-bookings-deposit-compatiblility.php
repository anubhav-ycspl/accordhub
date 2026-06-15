<?php
if (!function_exists('deposits_thankyou_order')) {
  function deposits_thankyou_order($order)
  {
    // 	Deposits & Partial Payments for WooCommerce by Acowebs
    if ($order->get_type() == 'awcdp_payment') {
      $order_id = $order->get_parent_id();
      try {
        $order = wc_get_order($order_id, 1);
      } catch (Exception $e) {
      }
    }
    // 	WooCommerce Deposits  Webtomizer by Acowebs
    if ($order->get_type() === 'wcdp_payment') {
      $order_id = $order->get_parent_id();
      try {
        $order = wc_get_order($order_id, 1);
      } catch (Exception $e) {
      }
    }
    do_action('ph_deposit_thankyou_compatibility', $order);
  }
}
add_action('awcdp_deposits_thankyou','deposits_thankyou_order');

add_action('wc_deposits_thankyou','deposits_thankyou_order');