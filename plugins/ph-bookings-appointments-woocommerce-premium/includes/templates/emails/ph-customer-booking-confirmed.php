<?php
 /**
 * PH Customer booking confirmed email HTML.
 * 
 * You can choose to display the following details within the email 
 *  
 * $order_id         	- used to include Order ID
 * $order_number     	- used to include Order Number
 * $customer_first_name - used to include Customer First Name
 * $customer_last_name  - used to include Customer Last Name
 * $customer_full_name	- used to include Customer Full Name
 * $recipient_email     - used to include Recipient Email Id
 * $order     			- used to include Order Object
 * $item     			- used to include Order Item
 * $email_subject       - used to include Email Subject
 * $email_heading    	- used to include Email Header
 * $additional_content  - used to include Additional Content
 * $email_base_color	- used to include Email Base Color
 * $email_text_color	- used to include Text Color
 * $wp_date_format		- used to include Date Format
 * $email            	- used to include email object
 * $sent_to_admin 		- need to send email to admin
 * $plain_text    		- email type
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * @hooked WC_Emails::email_header() Output the email header
*/
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %1$s: Order number, %2$s: Customer full name.  */ ?>
<p><?php 
// printf( esc_html__( 'Notification to let you know &mdash; order #%1$s belonging to %2$s has been cancelled:', 'woocommerce' ), esc_html( $order->get_order_number() ), esc_html( $order->get_formatted_billing_full_name() ) ); 
?></p>

<p>
	<?php
		echo __( 'Hi', 'bookings-and-appointments-for-woocommerce' ).' '.$customer_full_name.',<br><br>';
		printf( __( 'Your booking at %s is approved.', 'bookings-and-appointments-for-woocommerce' ), $email->blog_name );

		// 140472
		if (is_object($order)) 
		{
			$order_total = $order->get_total();

			//  143735
			$wc_order_status = $order->get_status();
			if ($order_total > 0 && $wc_order_status != 'processing' && $wc_order_status != 'completed')
			{
				echo "<br><br>".__( "Please click below link to proceed with the payment ",'bookings-and-appointments-for-woocommerce')."<br><a href='".$order->get_checkout_payment_url()."' target='_blank' >".$order->get_checkout_payment_url().'</a>';
			}
		}
	?>
</p>

<?php
/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
