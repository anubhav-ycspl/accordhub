<?php

/**
 * Adds 'phive_booking' to the list of supported payment request types.
 *
 * This function appends 'phive_booking' to the supported types array used 
 * by the WooCommerce Stripe Payment Request feature.
 *
 * @param array $supported_types The existing array of supported types.
 * @return array The modified array of supported types including 'phive_booking'.
 */
function ph_add_phive_booking_supported_type( $supported_types ) {

	// Append 'phive_booking' to the supported types array
    $supported_types[] = 'phive_booking';

    // Return the updated array of supported types
    return $supported_types;
}

// Attach the function to the 'wc_stripe_payment_request_supported_types' filter
add_filter( 'wc_stripe_payment_request_supported_types', 'ph_add_phive_booking_supported_type' );