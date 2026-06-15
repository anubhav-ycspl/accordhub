<?php

if (! defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

/**
 * Handles all AJAX calls related to Flatsome theme compatibility for booking functionality.
 */
class PH_Booking_FlatSome_Theme_Compatibility
{

    /**
     * Constructor function to initialize AJAX hooks.
     */
    public function __construct()
    {
        // AJAX action for logged-in users
        add_action('wp_ajax_phive_get_booking_price', array( $this, 'ph_get_booking_price_for_quick_view' ));
        
        // AJAX action for guest users
        add_action('wp_ajax_nopriv_phive_get_booking_price', array( $this, 'ph_get_booking_price_for_quick_view' ));
    }

    /**
     * Handles AJAX request to fetch the booking price for quick view.
     *
     * Retrieves the product's booking price using the product ID and returns the data in JSON format.
     */
    public function ph_get_booking_price_for_quick_view()
    {
        // Retrieve product ID from the AJAX request
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if (!$product_id) {
            echo json_encode(
                array(
                'success' => 0,
                'message' => 'Invalid product ID.'
                )
            );
            exit();
        }

        // Get the booking price based on the product ID
        $product_price = $this->ph_get_booking_price_by_id($product_id);

        // Return response in JSON format
        echo json_encode(
            array(
            'success'       => 1,
            'booking_price' => $product_price,
            )
        );
        exit();
    }

    /**
     * Retrieves the booking price of a product by its ID.
     *
     * @param  int $product_id The ID of the product.
     * @return mixed The calculated booking price, including suffix if available.
     */
    public function ph_get_booking_price_by_id($product_id)
    {
        $product_price = 0;

        // Check if a display cost is set for the product
        $display_cost = get_post_meta($product_id, '_phive_booking_pricing_display_cost', true);

        if (!empty($display_cost)) {
            // Retrieve cost suffix if available
            $display_cost_suffix = get_post_meta($product_id, '_phive_booking_pricing_display_cost_suffix', true);

            // Append suffix if it exists
            $product_price = !empty($display_cost_suffix) ? $display_cost . ' ' . $display_cost_suffix : $display_cost;
        } else {
            // Retrieve base cost and cost per unit
            $base_cost     = get_post_meta($product_id, '_phive_booking_pricing_base_cost', true);
            $cost_per_unit = get_post_meta($product_id, '_phive_booking_pricing_cost_per_unit', true);

            // Calculate total product price
            $product_price = (float) $base_cost + (float) $cost_per_unit;
        }

        return $product_price;
    }
}

// Initialize the class
new PH_Booking_FlatSome_Theme_Compatibility();