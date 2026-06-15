<?php

if( ! function_exists('ph_bookings_get_currency_conversion_rate') ) {
	function ph_bookings_get_currency_conversion_rate($price) {
        
        $store_admin_currency = get_option('woocommerce_currency');
        
        
        $wc_store_currency		= get_woocommerce_currency();

        $woocommerce_currency_conversion_rate=get_option('woocommerce_multicurrency_rates');

        
		if($store_admin_currency!=$wc_store_currency && !empty($woocommerce_currency_conversion_rate) && isset($woocommerce_currency_conversion_rate[$wc_store_currency]) && isset($woocommerce_currency_conversion_rate[$store_admin_currency]))
		{
            $store_admin_currency_rate = $woocommerce_currency_conversion_rate[$store_admin_currency];

            $wc_store_currency_rate = $woocommerce_currency_conversion_rate[$wc_store_currency];

            $conversion_rate = $wc_store_currency_rate / $store_admin_currency_rate;
            
            return $price * $conversion_rate;
        }
        return $price;
	}

}
add_filter( 'ph_bookings_currency_conversion', 'ph_bookings_get_currency_conversion_rate' ,10,1);
add_filter( 'ph_bookings_currency_conversion_compact', 'ph_bookings_get_currency_conversion_rate' ,10,1);

