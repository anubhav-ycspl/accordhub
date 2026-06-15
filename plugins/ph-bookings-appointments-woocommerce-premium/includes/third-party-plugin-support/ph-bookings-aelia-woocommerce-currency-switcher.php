<?php

if( ! function_exists('ph_bookings_aelia_currency_price') ) 
{	
    function ph_bookings_aelia_currency_price($price)
    {
        $price = apply_filters('wc_aelia_cs_convert', $price, get_option('woocommerce_currency'), get_woocommerce_currency());
        return $price;
    }
}

if( ! function_exists('ph_bookings_hide_aelia_currency_switcher_meta_keys') ) 
{
    function ph_bookings_hide_aelia_currency_switcher_meta_keys($key_filter=array()) 
    {
        $hide_key = array(
            '_line_subtotal_base_currency',
			'_line_subtotal_tax_base_currency',
			'_line_tax_base_currency',
			'_line_total_base_currency',
			'tax_amount_base_currency',
			'shipping_tax_amount_base_currency',
			'discount_amount_base_currency',
			'discount_amount_tax_base_currency',
        );

		$key_filter = array_merge($key_filter, $hide_key);
		return $key_filter;
    }
}

if( ! function_exists('ph_woocommerce_add_cart_item_aelia_compatibility') ) 
{
    function ph_woocommerce_add_cart_item_aelia_compatibility($cart_item)
    {
        if( isset($cart_item['phive_booked_price']) )
        {
            $price = apply_filters('wc_aelia_cs_convert', $cart_item['phive_booked_price'], get_option('woocommerce_currency'), get_woocommerce_currency());
			$cart_item['data']->set_price($price);
		}
		return $cart_item;
    }

}

if( ! function_exists('woocommerce_get_cart_item_from_session_aelia_compatibility') ) 
{
    function woocommerce_get_cart_item_from_session_aelia_compatibility($cart_item)
    {
        if( isset($cart_item['phive_booked_price']) ){
			$cart_item['data']->set_price($cart_item['phive_booked_price']);
        }
		return $cart_item;
    }
}

add_filter( 'ph_bookings_currency_conversion', 'ph_bookings_aelia_currency_price' ,10,1);
add_filter( 'ph_bookings_currency_conversion_compact', 'ph_bookings_aelia_currency_price' ,10,1);

add_filter('ph_bookings_order_meta_key_filters_for_admin', 'ph_bookings_hide_aelia_currency_switcher_meta_keys',10, 1);
add_filter('ph_bookings_order_meta_key_filters','ph_bookings_hide_aelia_currency_switcher_meta_keys',10, 1);

add_action( 'woocommerce_get_cart_item_from_session','woocommerce_get_cart_item_from_session_aelia_compatibility', 20, 3 );

add_filter( 'woocommerce_add_cart_item','ph_woocommerce_add_cart_item_aelia_compatibility', 20, 1 );