<?php
//  error_log('hide dokan meta keys');
if( ! function_exists('ph_bookings_hide_dokan_lite_meta_keys') ) 
{
    function ph_bookings_hide_dokan_lite_meta_keys($key_filter=array()) 
    {
        // error_log('hide dokan meta keys inside');
        $hide_key = array('_dokan_commission_rate', '_dokan_commission_type', '_dokan_additional_fee');
		$key_filter = array_merge($key_filter, $hide_key);
		// error_log(print_r($key_filter,1));
		return $key_filter;
    }
}

add_filter('ph_bookings_order_meta_key_filters_for_admin', 'ph_bookings_hide_dokan_lite_meta_keys',10, 1);

add_filter('ph_bookings_order_meta_key_filters','ph_bookings_hide_dokan_lite_meta_keys',10, 1);

    