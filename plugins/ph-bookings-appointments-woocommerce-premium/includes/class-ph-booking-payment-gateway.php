<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Bookings_Gateway class.
 */
class phive_booking_payment_gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                = 'ph-booking-gateway';
		$this->icon              = '';
		$this->has_fields        = false;
		$this->method_title      = __( 'Payment on Confirmation', 'bookings-and-appointments-for-woocommerce' );
		$this->title             = $this->method_title;
		$this->order_button_text = __( 'Request Confirmation', 'bookings-and-appointments-for-woocommerce' );
		$this->description 		 = __( 'Pay the full amount after your booking is confirmed. You\'ll receive a confirmation email with payment details.', 'bookings-and-appointments-for-woocommerce' );

		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'phive_thankyou_page' ) );
	}


	public function phive_thankyou_page( $order_id ) {
		
		$order = new WC_Order( $order_id );

		if( 'completed' == $order->get_status() ){
			echo '<div>' . __( 'Your booking has been approved, Thank you.', 'bookings-and-appointments-for-woocommerce' ) . '</div>';
		}else{
			echo '<div>' . __( 'Your booking is waiting for approval', 'bookings-and-appointments-for-woocommerce' ) . '</div>';
		}
	}

	public function admin_options() {
		$title = ( ! empty( $this->method_title ) ) ? $this->method_title : __( 'Settings', 'bookings-and-appointments-for-woocommerce' ) ;

		echo '<h3>' . $title . '</h3>
				<p>' . __( 'This payment method is used those orders has booking products and pay later (Need confirmation)', 'bookings-and-appointments-for-woocommerce' ) . '</p>
				<p>' . __( 'This payment gateway does not need to config', 'bookings-and-appointments-for-woocommerce' ) . '</p>';

		// hide save button by css
		echo'
		<style>
			input[type=submit]{
				display: none!important;
			}
		</style>';
		
	}

	/**
	 * Process the payment
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		// Add custom order note.
		$order->add_order_note( __( 'The order is waiting for approval from admin', 'bookings-and-appointments-for-woocommerce' ) );

		// Remove cart
		WC()->cart->empty_cart();

		do_action( 'ph_booking_payment_processed', $order_id, $order  );

		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);
	}

}
