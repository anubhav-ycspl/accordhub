<?php

/**
 * PH Customer booking reminder email Plain.
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

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html(wp_strip_all_tags($email_heading));
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo __('Hi', 'bookings-and-appointments-for-woocommerce') . ' ' . $customer_full_name;
printf(__("\nI request you to check the bookings details for your appointment with %s.\n", 'bookings-and-appointments-for-woocommerce'), $email->blog_name);

echo "\n----------------------------------------\n\n";

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

echo "\n----------------------------------------\n\n";


/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
	echo esc_html(wp_strip_all_tags(wptexturize($additional_content)));
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post(apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')));
