<?php
/**
 * PH Booking Payment Gateway - Classic WooCommerce gateway class.
 *
 * @package BookingsAndAppointmentsForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-ph-booking-gateway-utils.php';

/**
 * PH_Booking_Payment_Gateway Class.
 *
 * Implements a payment gateway for payment after booking confirmation.
 */
class PH_Booking_Payment_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor.
	 *
	 * Sets up gateway ID, title, description, button text and hooks.
	 */
	public function __construct() {

		$this->id           = 'ph-booking-gateway';
		$this->method_title = __( 'Payment on Confirmation', 'bookings-and-appointments-for-woocommerce' );
		$this->icon         = '';
		$this->has_fields   = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->title             = $this->get_option( 'title', __( 'Payment on Confirmation', 'bookings-and-appointments-for-woocommerce' ) );
		$this->description       = $this->get_option( 'description', __( 'Pay after your booking is confirmed.', 'bookings-and-appointments-for-woocommerce' ) );
		$this->order_button_text = $this->get_option( 'order_button_label', __( 'Request Confirmation', 'bookings-and-appointments-for-woocommerce' ) );

		// Admin save settings handler.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Thank you page message.
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'phive_thankyou_page' ) );
	}

	/**
	 * Initialize settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'banner'             => array(
				'type' => 'ph_booking_banner',
			),
			'title'              => array(
				'title'       => __( 'Title', 'bookings-and-appointments-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Payment method title shown to customers on checkout page.', 'bookings-and-appointments-for-woocommerce' ),
				'default'     => __( 'Payment on Confirmation', 'bookings-and-appointments-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'description'        => array(
				'title'       => __( 'Description', 'bookings-and-appointments-for-woocommerce' ),
				'type'        => 'textarea',
				'css'         => 'width: 400px;',
				'description' => __( 'Description shown on checkout page.', 'bookings-and-appointments-for-woocommerce' ),
				'default'     => __( 'Pay after your booking is confirmed.', 'bookings-and-appointments-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'order_button_label' => array(
				'title'       => __( 'Place Order Button Text', 'bookings-and-appointments-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Text for the Place Order button on checkout page.', 'bookings-and-appointments-for-woocommerce' ),
				'default'     => __( 'Request Confirmation', 'bookings-and-appointments-for-woocommerce' ),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Render the custom banner above standard settings.
	 *
	 * @param string $key  Field key.
	 * @param array  $data Field definition array.
	 *
	 * @return void
	 */
	public function generate_ph_booking_banner_html( $key, $data ) {

		?>
		<tr valign="top">
			<th colspan="2" class="ph-bookings-rc-banner-cell">
				<div class="ph-bookings-rc-banner">
					<div class="ph-bookings-rc-icon">
						<img
							src="/wp-content/plugins/ph-bookings-appointments-woocommerce-premium/resources/icons/pluginhive.png"
							alt="PluginHive Icon"
							width="50"
							height="50"
							style="display: block;" />
					</div>
					<div class="ph-bookings-rc-content">
						<div class="ph-bookings-rc-title-row">
							<span class="ph-bookings-rc-title">
								<?php esc_html_e( 'Booking Confirmation Gateway by PluginHive', 'bookings-and-appointments-for-woocommerce' ); ?>
							</span>
							<span class="ph-bookings-rc-subtitle">
								<?php esc_html_e( '– specifically designed for bookable products, becoming visible only when the bookings require confirmation.', 'bookings-and-appointments-for-woocommerce' ); ?>
							</span>
						</div>
						<div class="ph-bookings-rc-paragraph">
							<?php esc_html_e( "Orders placed via this method will be set to 'Pending Payment' and require your manual approval before payment is taken.", 'bookings-and-appointments-for-woocommerce' ); ?>
						</div>
						<div class="ph-bookings-rc-note">
							<?php
							printf(
								wp_kses(
									/* Translators: %1$s is the URL to the WooCommerce Bookings Confirmation & Payment on Approval documentation. */
									__( 'Refer to official <a href="%1$s" target="_blank" rel="noopener noreferrer">WooCommerce Bookings Confirmation & Payment on Approval</a> documentation for advanced setup.', 'bookings-and-appointments-for-woocommerce' ),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
											'rel'    => array(),
										),
									)
								),
								esc_url( 'https://www.pluginhive.com/knowledge-base/woocommerce-bookings-confirmation-payment-on-approval/?utm_source=bookings&utm_medium=plugin_settings' )
							);
							?>
						</div>
					</div>
				</div>
			</th>
		</tr>
		<?php
	}

	/**
	 * Check if payment gateway is available on the current cart.
	 *
	 * @return bool True if available, false otherwise.
	 */
	public function is_available() {

		return PH_Booking_Gateway_Utils::is_confirmation_booking_in_cart();
	}

	/**
	 * Display message on the thank you page after order is placed.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function phive_thankyou_page( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( 'completed' === $order->get_status() ) {
			echo '<div>' . esc_html__( 'Your booking has been approved, Thank you.', 'bookings-and-appointments-for-woocommerce' ) . '</div>';
		} else {
			echo '<div>' . esc_html__( 'Your booking is waiting for approval.', 'bookings-and-appointments-for-woocommerce' ) . '</div>';
		}
	}

	/**
	 * Render the admin options page.
	 *
	 * Hides the save button since no configuration changes are needed.
	 *
	 * @return void
	 */
	public function admin_options() {

		parent::admin_options();

		// Hide WooCommerce save button for this gateway to enforce always enabled.
		echo '<style>input[type=submit]{display:none!important;}</style>';
	}

	/**
	 * Process payment and set the order to "on-hold" or similar.
	 *
	 * @param int $order_id Order ID.
	 * @return array Payment result and redirect URL.
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}

		$order->add_order_note( __( 'The order is waiting for approval from admin.', 'bookings-and-appointments-for-woocommerce' ) );

		// Empty the cart.
		WC()->cart->empty_cart();

		// Trigger payment processed action hook.
		do_action( 'ph_booking_payment_processed', $order_id, $order );

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}
