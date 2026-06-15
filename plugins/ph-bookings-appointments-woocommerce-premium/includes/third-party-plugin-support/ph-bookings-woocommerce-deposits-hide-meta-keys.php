<?php

if( ! function_exists('ph_bookings_hide_woocommerce_deposits_meta_keys') ) 
    {
        function ph_bookings_hide_woocommerce_deposits_meta_keys($meta_keys=array()) 
        {
            $meta_keys[] = '_is_deposit';
            $meta_keys[] = '_remaining_balance_order_id';
            $meta_keys[] = '_remaining_balance_paid';
            $meta_keys[] = '_original_order_id';
            $meta_keys[] = '_payment_plan_scheduled';
            $meta_keys[] = '_payment_plan';
            $meta_keys[] = '_deposit_full_amount';
            $meta_keys[] = '_deposit_full_amount_ex_tax';
            $meta_keys[] = '_deposit_deposit_amount_ex_tax';
            return $meta_keys;
        }
    }

    add_filter( 'ph_bookings_order_meta_key_filters', 'ph_bookings_hide_woocommerce_deposits_meta_keys' ,10,1);
    
    add_filter( 'ph_bookings_order_meta_key_filters_for_admin', 'ph_bookings_hide_woocommerce_deposits_meta_keys' ,10,1);

    