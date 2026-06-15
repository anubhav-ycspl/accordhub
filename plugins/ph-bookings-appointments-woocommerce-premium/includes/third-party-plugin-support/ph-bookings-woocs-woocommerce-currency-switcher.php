<?php

if( ! function_exists('ph_bookings_get_currency_conversion_rate_from_woocs') ) {
	function ph_bookings_get_currency_conversion_rate_from_woocs($price) {
        
        $store_admin_currency = get_option('woocommerce_currency');
        
        $wc_store_currency		= get_woocommerce_currency();
        $woocs = get_option('woocs', 1);
		if($store_admin_currency != $wc_store_currency && !empty($woocs))
		{
            $rate = $woocs[$wc_store_currency]['rate'];
            $rate_plus = $woocs[$wc_store_currency]['rate_plus'];
            if(empty($rate_plus))
            {
                $rate_plus = 0;
            }
            $rate = $rate + $rate_plus;
            $price =  $price * $rate;
            return $price; 
        }

        return $price;
	}

}

if( ! function_exists('ph_modify_cart_subtotal_after_woocs') ) {
	function ph_modify_cart_subtotal_after_woocs($cart_subtotal, $compound, $obj) {
        
        $cart = WC()->session->get( 'cart' );
        // error_log(print_r($cart, 1));
        $price = 0;
        if (isset($cart) && !empty($cart))
        {
            foreach ( $cart as $value ) 
            {
                if(isset($value['phive_booked_price']))
                {
                    $value['line_total'] = (isset($value['phive_booked_price']) && !empty($value['phive_booked_price'])) ? $value['phive_booked_price'] : 0;
                    $line_total = ph_bookings_get_currency_conversion_rate_from_woocs($value['line_total']);
                    // error_log($line_total);
                    $unformatted_price = $line_total;
                    $negative          = $line_total < 0;
                    $line_total        = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $line_total * -1 : $line_total ) );
	                $line_total        = apply_filters( 'formatted_woocommerce_price', $line_total, 1, '.', ',' );
                    $value['line_total'] = $line_total;
                }
                $price = $price +  $value['line_total'];
            }
            $currency = ! empty(WC()->session->client_currency) ? WC()->session->client_currency : get_woocommerce_currency();
            $price_html = wc_price($price,array( 'currency' => $currency ));

            // 124423 - not showing correct subtotal if tax is there.
            $product = isset($value['product_id']) ? wc_get_product($value['product_id']) : '';
            if (is_object($product) && '0' !== $product->get_tax_class() && 'taxable' === $product->get_tax_status() && wc_tax_enabled()) 
            {
                if ( is_object($obj) && $obj->display_prices_including_tax() ) 
                {
                    // $value['quantity'] -> showing incorrect result if this variable is used. so, using quantity 1 for calculation.
                    // error_log(get_option( 'woocommerce_prices_include_tax' ));
                    if(get_option( 'woocommerce_prices_include_tax' ) != 'yes')
                    {
                        $price = wc_get_price_including_tax($product, array('qty' => 1, 'price' => $price));
                        $price_html = wc_price( $price, array( 'currency' => $currency ) );
                    }
                    else
                    {
                        if(isset($value['phive_booked_price']))
                        {
                            $price = wc_get_price_including_tax($product, array('qty' => 1, 'price' => $price));
                        }
                        else
                        {
                            $price = $price + $value['line_tax'];
                        }
                        $price_html = wc_price( $price, array( 'currency' => $currency ) );
                    }
                }
                else if(is_object($obj) && !$obj->display_prices_including_tax() && isset($value['phive_booked_price']))
                {
                    if(get_option( 'woocommerce_prices_include_tax' ) == 'yes')
                    {
                        $price = wc_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price));
                        $price_html = wc_price( $price, array( 'currency' => $currency ) );
                    }
                }
            }
            return $price_html;
        }
        return $cart_subtotal;

	}

}

add_filter( 'ph_bookings_currency_conversion', 'ph_bookings_get_currency_conversion_rate_from_woocs' ,10,1);
add_filter( 'ph_bookings_currency_conversion_compact', 'ph_bookings_get_currency_conversion_rate_from_woocs' ,10,1);

add_filter('woocommerce_cart_subtotal','ph_modify_cart_subtotal_after_woocs',99999,3);

// add_filter( 'woocommerce_get_price_html', 'ph_modify_display_price',10,2); 
