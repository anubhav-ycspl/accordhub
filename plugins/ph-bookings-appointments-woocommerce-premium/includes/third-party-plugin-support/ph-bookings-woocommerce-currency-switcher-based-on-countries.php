<?php

if( ! function_exists('ph_bookings_get_currency_conversion_rate_based_on_country') ) {
	function ph_bookings_get_currency_conversion_rate_based_on_country($price) {
       $zones = (array) get_option( 'wc_price_based_country_regions', array() );
        if(function_exists('wcpbc_the_zone') && !empty($zones))
        {
            $zone=wcpbc_the_zone();
            if(!empty($zone))
            {
                // get_zone_id() changed to get_id since get_zone_id is deprecated
                $zone_id=$zone->get_id();
                if(!empty($zone_id) && isset($zones[$zone_id]))
                {
                    return $price*$zones[$zone_id]['exchange_rate'];
                    // return $price;
                }
            }
        }
        return $price;
	}

}
add_filter( 'ph_bookings_currency_conversion', 'ph_bookings_get_currency_conversion_rate_based_on_country' ,10,1);
add_filter( 'ph_bookings_currency_conversion_compact', 'ph_bookings_get_currency_conversion_rate_based_on_country' ,10,1);

