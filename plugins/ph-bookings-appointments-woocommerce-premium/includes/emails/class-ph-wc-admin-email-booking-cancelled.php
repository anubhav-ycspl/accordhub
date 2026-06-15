<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking is cancelled
 *
 * An email sent to the Admin when a booking is cancelled.
 *
 * @class   Ph_WC_Email_Booking_Cancelled_For_Admin
 * @extends WC_Email
 */
class Ph_WC_Email_Booking_Cancelled_For_Admin extends WC_Email {

    /**
	 * @var $blog_name
	 */
	public $blog_name;

	/**
	 * @var $booking_status
	 */
	public $booking_status;

	/**
	 * @var $item_id
	 */
	public $item_id;

	/**
	 * @var
	 */
	public $order_id, $order_number, $customer_first_name, $customer_last_name, $customer_full_name, $recipient_email, $object, $item;

    
	/**
	 * Constructor
	 */
	public function __construct() {
        
		$this->id             = 'ph_booking_cancelled_admin_email';
		$this->title          = __( 'PH Booking Cancelled For Admin', 'bookings-and-appointments-for-woocommerce' );
		$this->description    = __( 'Booking cancelled emails are sent when the status of a booking goes to cancelled.', 'bookings-and-appointments-for-woocommerce' );
		
        $this->customer_email = false;
		$this->template_html  = 'emails/ph-admin-booking-cancelled.php';
		$this->template_plain = 'emails/ph-admin-booking-cancelled-plain.php';

		// Call parent constructor
		parent::__construct();

		// Other settings
        $this->template_base = PH_BOOKINGS_TEMPLATE_PATH;

		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	public function trigger($order_id, $to = '', $order = '', $admin_user_id = '') {

		$order = wc_get_order( $order_id );
		
		if ( wc_get_order($order_id) ) {	
			
			$billing_email = wc_get_order( $order_id )->get_billing_email();
			$this->recipient = $billing_email;
		} else {

			$customer_id = $this->object->get_customer_id();
			$customer    = $customer_id ? get_user_by( 'id', $customer_id ) : false;

			if ( $customer_id && $customer ) {
				$this->recipient = $customer->user_email;
			}
		}

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object 				= $order;
			$this->order_id 			= $order_id;
			$this->order_number 		= $this->object->get_order_number();
			$this->customer_first_name 	= $this->object->get_billing_first_name();
			$this->customer_last_name  	= $this->object->get_billing_last_name();
			$this->recipient_email 	   	= $to;
			$this->item  				= $this->object->get_items();
			$this->customer_full_name 	= $this->object->get_formatted_billing_full_name();

			$this->placeholders['{order_date}']              = wc_format_datetime( $this->object->get_date_created() );
			$this->placeholders['{order_number}']            = $this->object->get_order_number();
			$this->placeholders['{order_billing_full_name}'] = $this->object->get_formatted_billing_full_name();
			$this->placeholders['{order_id}'] 				 = $this->order_id;
		}

		// return if no order ID is present
		if ( !$order_id || !$this->is_enabled() || empty($to) ) {

			return;
		}

		if (!empty($to)) {

			// send the email
			$this->send( $to, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
	}

	/**
	 * Get order's item meta
	 * 	 
	 * @access public
	 * @param  $order_item
	 * @return $content
	 */
	public function ph_get_order_item_meta_data( $order_item ) {
		$content = null;
		$meta_datas = $order_item->get_meta_data();
		$product = $order_item->get_product();

		//hide certain meta_keys from email 
		$hidden_order_itemmeta = apply_filters('ph_bookings_order_meta_key_filters', array('Number of persons','Total Number of Participants'), $order_item);
		
		foreach( $meta_datas as $meta_data ) {
			$meta_data = $meta_data->get_data();
			if( ! empty($meta_data['value']) && ! is_array($meta_data['value']) && !in_array($meta_data['key'], $hidden_order_itemmeta)) {			
				$meta_data['key'] = apply_filters( 'woocommerce_attribute_label', $meta_data['key'], $meta_data['key'], $product);			
				$content .= "<li style='margin: 0.5em 0 0; padding: 0;'><b>".__($meta_data['key'], 'bookings-and-appointments-for-woocommerce')."</b>: <br>".__($meta_data['value'], 'bookings-and-appointments-for-woocommerce')."</li>";
			}
		}

		if( ! empty($content) ) {
			$content = "<ul style='font-size: small; margin: 1em 0 0;padding: 0;list-style: none;'>".$content."</ul>";
		}
		return $content;
	}

	/**
	 * Return content from the additional_content field.
	 *
	 * Displayed above the footer.
	 *
	 * @since 3.7.0
	 * @return string
	 */
	public function get_additional_content() {
		$content = $this->get_option( 'additional_content', '' );

		return apply_filters( 'woocommerce_email_additional_content_' . $this->id, $this->format_string( $content ), $this->object, $this );
	}

	/**
	 * Default content to show below main email content.
	 *
	 * @since 3.7.0
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Thanks for reading.', 'bookings-and-appointments-for-woocommerce' );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template( $this->template_html, array(
			'order_id'         		=> $this->order_id,
			'order_number'			=> $this->order_number,
			'customer_first_name'	=> $this->customer_first_name,
			'customer_last_name'	=> $this->customer_last_name,
			'customer_full_name'	=> $this->customer_full_name,
			'recipient_email'       => $this->recipient_email,
			'order'         		=> $this->object,
			'item'					=> $this->item,
			'email_subject'			=> $this->get_subject(),
			'email_heading' 		=> $this->get_heading(),
			'additional_content' 	=> $this->get_additional_content(),
			'sent_to_admin' 		=> false,
			'plain_text'    		=> false,
			'email'         		=> $this,
			'email_base_color'		=> get_option( 'woocommerce_email_base_color' ),
			'email_text_color'		=> get_option( 'woocommerce_email_text_color' ),
			'wp_date_format'		=> get_option( 'date_format' )
		), 'ph-bookings-appointments-woocommerce/', $this->template_base );
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template( $this->template_plain, array(
			'order_id'         		=> $this->order_id,
			'order_number'			=> $this->order_number,
			'customer_first_name'	=> $this->customer_first_name,
			'customer_last_name'	=> $this->customer_last_name,
			'customer_full_name'	=> $this->customer_full_name,
			'recipient_email'       => $this->recipient_email,
			'order'         		=> $this->object,
			'item'					=> $this->item,
			'email_subject'			=> $this->get_subject(),
			'email_heading' 		=> $this->get_heading(),
			'additional_content' 	=> $this->get_additional_content(),
			'sent_to_admin' 		=> false,
			'plain_text'    		=> true,
			'email'         		=> $this,
			'email_base_color'		=> get_option( 'woocommerce_email_base_color' ),
			'email_text_color'		=> get_option( 'woocommerce_email_text_color' ),
			'wp_date_format'		=> get_option( 'date_format' )
		), 'ph-bookings-appointments-woocommerce/', $this->template_base );
		return ob_get_clean();
	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields() 
	{
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'bookings-and-appointments-for-woocommerce' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'bookings-and-appointments-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'bookings-and-appointments-for-woocommerce' ),
				'default' => 'yes',
			),
			'recipient'       => array(
				'title'       => __( 'Recipient(s)', 'bookings-and-appointments-for-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'bookings-and-appointments-for-woocommerce' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
			),
			'subject' => array(
				'title'       => __( 'Subject', 'bookings-and-appointments-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the email subject line. Leave blank to use the default subject.<br>Available placeholders: {site_title}, {site_address}, {site_url}, {order_date}, {order_id}, {order_number}, {order_billing_full_name}', 'bookings-and-appointments-for-woocommerce' ),
				'placeholder' => __( 'Subject', 'bookings-and-appointments-for-woocommerce' ),
				'default'     => __( 'Booking has been cancelled. The order details are shown below', 'bookings-and-appointments-for-woocommerce' ),
				'desc_tip'	  => true,
            ),
			'heading' => array(
				'title'       => __( 'Email heading', 'bookings-and-appointments-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading.', 'bookings-and-appointments-for-woocommerce' ),
				'placeholder' => __( 'Email heading', 'bookings-and-appointments-for-woocommerce' ),
				'default'     => __( 'Booking Cancellation', 'bookings-and-appointments-for-woocommerce' ),
				'desc_tip'      => true,
			),
			'additional_content' => array(
				'title'       => __( 'Additional content', 'bookings-and-appointments-for-woocommerce' ),
				'description' => __( 'Text to appear below the main email content.', 'bookings-and-appointments-for-woocommerce' ) . ' ' . $placeholder_text,
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => __( 'N/A', 'bookings-and-appointments-for-woocommerce' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_additional_content(),
				'desc_tip'    => true,
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'bookings-and-appointments-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'bookings-and-appointments-for-woocommerce' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}
}

return new Ph_WC_Email_Booking_Cancelled_For_Admin();
