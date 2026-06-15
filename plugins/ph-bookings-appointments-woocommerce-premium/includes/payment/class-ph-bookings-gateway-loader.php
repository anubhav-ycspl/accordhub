<?php
/**
 * Loader for Payment on Confirmation gateway and its integrations.
 *
 * @package BookingsAndAppointmentsForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PH_Bookings_Gateway_Loader
 *
 * Handles registration and integration of the custom Payment on Confirmation gateway.
 */
class PH_Bookings_Gateway_Loader {

	/**
	 * Gateway ID.
	 *
	 * @var string
	 */
	private $gateway_id = 'ph-booking-gateway';

	/**
	 * Constructor.
	 *
	 * Initializes the loader by including utilities and setting up hooks.
	 */
	public function __construct() {

		// Always include utilities up front.
		require_once plugin_dir_path( __FILE__ ) . 'class-ph-booking-gateway-utils.php';

		// Register gateway/classic for WooCommerce.
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );

		// Classic and Block: Hide all gateways except ours (if needed).
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_available_gateways' ), 999 );

		// Blocks registration.
		add_action( 'woocommerce_blocks_loaded', array( $this, 'register_block_gateway_integration' ) );
	}

	/**
	 * Register the Payment Gateway class.
	 *
	 * @param array $gateways Array of registered gateways.
	 *
	 * @return array Modified array of gateways including the custom gateway.
	 */
	public function register_gateway( $gateways ) {

		if ( ! class_exists( 'PH_Booking_Payment_Gateway' ) ) {

			require_once plugin_dir_path( __FILE__ ) . 'class-ph-booking-payment-gateway.php';
		}

		$gateways[] = 'PH_Booking_Payment_Gateway';

		return $gateways;
	}

	/**
	 * Filter available payment gateways to show only the custom gateway when conditions are met.
	 *
	 * @param array $available_gateways List of available payment gateway objects.
	 *
	 * @return array Filtered list of gateways.
	 */
	public function filter_available_gateways( $available_gateways ) {

		// Skip filtering in admin.
		if ( is_admin() ) {

			return $available_gateways;
		}

		$is_rest_request = defined( 'REST_REQUEST' ) && REST_REQUEST;
		$is_ajax         = defined( 'DOING_AJAX' ) && DOING_AJAX;

		// Only filter on frontend checkout/cart pages, REST API calls, or AJAX requests.
		if ( ! is_checkout() && ! is_cart() && ! $is_rest_request && ! $is_ajax ) {

			return $available_gateways;
		}

		if (
			isset( $available_gateways[ $this->gateway_id ] ) &&
			PH_Booking_Gateway_Utils::is_confirmation_booking_in_cart()
		) {
			// Remove all gateways except the custom gateway.
			foreach ( $available_gateways as $id => $gateway ) {

				if ( $id !== $this->gateway_id ) {
					unset( $available_gateways[ $id ] );
				}
			}
		}

		return $available_gateways;
	}

	/**
	 * Register block checkout integration only when needed.
	 *
	 * @return void
	 */
	public function register_block_gateway_integration() {

		if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {

			return;
		}

		if ( ! class_exists( 'PH_Booking_Gateway_Blocks_Support' ) ) {

			require_once plugin_dir_path( __FILE__ ) . 'class-ph-booking-gateway-blocks-support.php';
		}

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( $payment_method_registry ) {
				$payment_method_registry->register( new PH_Booking_Gateway_Blocks_Support() );
			}
		);
	}
}
