<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter('ph_bookings_get_client_currency','ph_bookings_wpml_multi_currency_compatibility');

if(!function_exists('ph_bookings_wpml_multi_currency_compatibility'))
{
    /**
     * Return the client currency
     * @param $currency
     * @return client currency
     * @since 3.1.9
     */
    function ph_bookings_wpml_multi_currency_compatibility($curerency){

        return apply_filters('wcml_get_client_currency',$curerency);
    }
}