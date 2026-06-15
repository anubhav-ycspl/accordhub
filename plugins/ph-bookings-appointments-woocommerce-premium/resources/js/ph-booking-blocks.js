/**
 * PH Booking Gateway Blocks JS integration.
 */

const { registerPaymentMethod, registerPaymentMethodExtensionCallbacks } = window.wc.wcBlocksRegistry;
const { decodeEntities } = window.wp.htmlEntities;
const { createElement } = window.wp.element;
const { __ } = window.wp.i18n;

// Get settings data for the 'ph-booking-gateway' from global WooCommerce block settings.
const settings = window.wc.wcSettings?.allSettings?.paymentMethodData?.['ph-booking-gateway'] || {};

/**
 * Label component showing icon and payment method title.
 *
 * @param {Object} props - Component props.
 * @returns {WPElement} React element.
 */
const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components;

	return createElement( PaymentMethodLabel, {
		icon: settings.icon,
		text: decodeEntities( settings.title || __( 'Payment on Confirmation', 'bookings-and-appointments-for-woocommerce' ) ),
	} );
};

/**
 * Content component showing description text for the payment method.
 *
 * @returns {WPElement} React element.
 */
const Content = () => {
	return createElement(
		'div',
		{ className: 'wc-block-components-payment-method__subtitle' },
		decodeEntities( settings.description || __( 'Pay the full amount after your booking is confirmed.', 'bookings-and-appointments-for-woocommerce' ) )
	);
};

// Register the payment method with WooCommerce Blocks.
registerPaymentMethod( {
	name: 'ph-booking-gateway',
	ariaLabel: settings.title || __( 'Payment on Confirmation', 'bookings-and-appointments-for-woocommerce' ),
	label: createElement( Label ),
	content: createElement( Content ),
	edit: null,
	placeOrderButtonLabel: settings.order_button_label || __( 'Request Confirmation', 'bookings-and-appointments-for-woocommerce' ),
	canMakePayment: () => true,
	supports: {
		features: settings.supports || [ 'products', 'booking_confirmation_required' ],
	},
} );

// If the cart contains booking products, hide conflicting payment methods after registering ours.
if ( settings.has_booking_products ) {
	registerPaymentMethodExtensionCallbacks( 'ph-booking-filter', {
		stripe: () => false,
		stripe_cc: () => false,
		woocommerce_payments: () => false,
	} );
}
