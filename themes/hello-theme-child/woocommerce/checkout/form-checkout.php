<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>
	
	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
	
	<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
	
	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>
<style>.e-checkout__order_review-2 {
    order: 2;
}</style>
<ul class="available-offers-list 1" style="list-style: none; padding: 0;">
        <li class="offer-item"
            style=" margin: 25px 0 0px 0; justify-content: space-between; align-items: center; padding: 15px; border: 1px dashed #ccc; border-radius: 10px;">
            <div class="coupon-item"><img decoding="async" class="coupon-img"
                    src="<?php echo get_stylesheet_directory_uri(); ?>/images/marketing.png"
                    alt="Coupon Icon">
                <div class="popup-trigger"><span class="" style="font-size: 14px; width: 100%;">Use Code
                        <strong>ACCRD20</strong> to get 20% off room charges.</span><a href="#" class="toggle-coupon-btn"
                        style="">View All Coupons</a></div><button type="button" class="button apply-quick-coupon"
                    data-code="accrd20" style="width: auto;" fdprocessedid="fuceee">Apply</button>
            </div>
			<div class="coupon-notice-wrapper" style="display:none; width: 100%; flex-basis: 100%; margin-top: 10px;"></div>
        </li>
    </ul>
</form>
	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>


<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
