<?php
/**
 * WooCommerce Blocks integration for PH Booking Payment Gateway.
 *
 * @package BookingsAndAppointmentsForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-ph-booking-gateway-utils.php';

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Class PH_Booking_Gateway_Blocks_Support
 *
 * Implements WooCommerce Blocks payment method integration for the PH Booking Payment Gateway.
 */
class PH_Booking_Gateway_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * Payment gateway name ID.
	 *
	 * @var string
	 */
	protected $name = 'ph-booking-gateway';

	/**
	 * Cache for whether booking products exist in cart.
	 *
	 * @var bool|null
	 */
	private $has_booking_products = null;

	/**
	 * Initialize the payment method integration.
	 *
	 * Registers booking payment requirements if supported.
	 *
	 * @return void
	 */
	public function initialize() {

		if ( function_exists( 'woocommerce_store_api_register_payment_requirements' ) ) {

			add_action( 'woocommerce_blocks_loaded', array( $this, 'register_booking_requirements' ) );
		}
	}

	/**
	 * Register booking payment requirements with the store API.
	 *
	 * @return void
	 */
	public function register_booking_requirements() {

		woocommerce_store_api_register_payment_requirements(
			array(
				'data_callback' => array( $this, 'get_booking_payment_requirements' ),
			)
		);
	}

	/**
	 * Check if booking products are in the cart and cache the result.
	 *
	 * @return bool True if booking products requiring confirmation are in the cart, false otherwise.
	 */
	private function has_booking_products() {

		if ( null === $this->has_booking_products ) {

			$this->has_booking_products = PH_Booking_Gateway_Utils::is_confirmation_booking_in_cart();
		}
		return $this->has_booking_products;
	}

	/**
	 * Get booking payment requirements for the Blocks API.
	 *
	 * @return array List of requirement strings.
	 */
	public function get_booking_payment_requirements() {

		return $this->has_booking_products() ? array( 'booking_confirmation_required' ) : array();
	}

	/**
	 * Determine if this payment method is active.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public function is_active() {

		return $this->has_booking_products();
	}

	/**
	 * Register and return script handles required for this payment method.
	 *
	 * @return string[] List of script handles.
	 */
	public function get_payment_method_script_handles() {

		wp_register_script(
			'ph-booking-gateway-blocks',
			plugin_dir_url( __DIR__ ) . '../resources/js/ph-booking-blocks.js',
			array( 'wc-blocks-registry', 'wp-element', 'wp-html-entities', 'wp-i18n' ),
			'1.0.1',
			true
		);

		wp_set_script_translations(
			'ph-booking-gateway-blocks',
			'bookings-and-appointments-for-woocommerce',
			plugin_dir_path( __DIR__ ) . '../i18n'
		);

		return array( 'ph-booking-gateway-blocks' );
	}

	/**
	 * Get payment method data for Blocks to render.
	 *
	 * Retrieves gateway title, description, order button label, support flags, and booking presence.
	 *
	 * @return array Payment method data array.
	 */
	public function get_payment_method_data() {

		$gateway = WC()->payment_gateways()->payment_gateways()[ $this->name ] ?? null;

		$options = get_option( 'woocommerce_' . $this->name . '_settings', array() );

		$title = $gateway
			? $gateway->get_title()
			: ( $options['title'] ?? __( 'Payment on Confirmation', 'bookings-and-appointments-for-woocommerce' ) );

		$description = $gateway
			? $gateway->get_description()
			: ( $options['description'] ?? __( 'Pay after your booking is confirmed.', 'bookings-and-appointments-for-woocommerce' ) );

		$order_button_label = $options['order_button_label'] ?? __( 'Request Confirmation', 'bookings-and-appointments-for-woocommerce' );

		return array(
			'title'                => $title,
			'description'          => $description,
			'order_button_label'   => $order_button_label,
			'icon'                 => '',
			'supports'             => array( 'products', 'booking_confirmation_required' ),
			'has_booking_products' => $this->has_booking_products(),
		);
	}
}
