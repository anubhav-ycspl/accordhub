<?php

if( ! function_exists('ph_bookings_woo_currency_conversion') ) {
    function ph_bookings_woo_currency_conversion($price) {
        $woocommerce_currency=get_option('woocommerce_currency');
        $wc_store_currency      = get_woocommerce_currency();
        $currency_params        = get_option( 'woo_multi_currency_params', array() );
        if(!isset($currency_params['currency_default']))
        {
            return $price;
        }
        $wc_default_currency    = $currency_params['currency_default'];
        $currency_conversion=array();
        if( $woocommerce_currency!=$wc_store_currency && isset($currency_params['currency']) )
        {
            foreach ($currency_params['currency'] as $key => $currency) {
                $currency_conversion[$currency] =  $currency_params['currency_rate'][$key];
            }
        }

        if( $wc_store_currency!=$wc_default_currency && !empty($currency_conversion[$wc_store_currency]) && !empty($currency_conversion[$woocommerce_currency]) )
        {
            $conversion_rate = round( ($currency_conversion[$woocommerce_currency] * $currency_conversion[$wc_store_currency]), 2);
            $price=$price*$conversion_rate;
        }
        
        return $price;
    }

}
// add_filter( 'ph_bookings_currency_conversion', 'ph_bookings_woo_currency_conversion' ,10,1);
