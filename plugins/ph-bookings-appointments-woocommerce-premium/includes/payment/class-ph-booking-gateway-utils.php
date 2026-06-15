<?php
/**
 * Utilities for Booking Payment Gateway business logic.
 *
 * @package BookingsAndAppointmentsForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility methods for PH Booking Gateway plugin.
 */
class PH_Booking_Gateway_Utils {

	/**
	 * Determine if the cart has a product requiring confirmation.
	 *
	 * Checks cart items for any product flagged as requiring confirmation before payment.
	 *
	 * @return bool True if at least one product requires confirmation, false otherwise.
	 */
	public static function is_confirmation_booking_in_cart() {

		if ( ! function_exists( 'wc' ) || ! wc()->cart ) {
			return false;
		}

		foreach ( wc()->cart->get_cart_contents() as $cart_item ) {

			$product_id = method_exists( 'Ph_Bookings_General_Functions_Class', 'get_default_lang_product_id' )
				? Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $cart_item['product_id'] )
				: $cart_item['product_id'];

			if ( empty( $cart_item['phive_book_from_date'] ) ) {
				continue;
			}

			$required_confirmation = apply_filters(
				'ph_booking_require_confirmation',
				get_post_meta( $product_id, '_phive_book_required_confirmation', true ),
				$cart_item['phive_book_from_date']
			);

			if ( 'yes' === $required_confirmation ) {
				return true;
			}
		}

		return false;
	}
}
