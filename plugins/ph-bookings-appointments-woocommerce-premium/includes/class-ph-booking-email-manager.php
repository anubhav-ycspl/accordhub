<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* This class manage all emails related jobs
*/
class ph_booking_email_manager {

	/**
	 * @var $id
	 */
	public $id;

	/**
	 * @var $blog_name
	 */
	public $blog_name;

	public function __construct() {
		// 103410
		global $current_lang;
		$this->id             = 'ph_booking';
		if( ! class_exists('Ph_Bookings_Email_Content') )
			include_once 'emails/class-ph-email-content.php';
		add_action( 'ph_booking_status_changed', array( $this, 'email_customer_booking_status_changed' ), 10, 4 );
		add_action( 'ph_booking_status_changed', array( $this, 'email_admin_booking_status_changed' ), 10, 4 );
		// add_action( 'ph_booking_payment_processed', array( $this, 'email_customer_pending_payment' ), 10, 2 );
		add_action( 'ph_booking_payment_processed', array( $this, 'email_admin_pending_payment' ), 10, 2 );
		$this->blog_name = get_option('blogname');

		add_filter( 'woocommerce_email_classes', array( $this, 'init_emails' ) );

		add_action( 'ph_booking_status_changed', array( $this, 'email_customer_booking_status_changed_wc_email' ), 10, 4 );

		add_action( 'ph_booking_payment_processed', array( $this, 'email_customer_pending_payment_wc_email' ), 10, 2 );

		#102692 - template overriding
		add_filter('woocommerce_template_directory', array($this, 'ph_custom_woocommerce_template_directory'), 10, 2);

		// 214344 && 214273 Modify the Booking details for the emails
		add_filter('woocommerce_email_order_items_args', array($this, 'ph_woocommerce_email_order_items_args'));

		// 103410 - checking if sitepress plugin is active
		global $sitepress_active_check;
		$sitepress_active_check = class_exists('SitePress');
	}

	public function email_customer_booking_status_changed_wc_email( $status, $item_id, $order_id, $order='')
	{
		if($status == 'cancelled')
		{
			// 103410 - Switching to product language
			global $current_lang;
			$current_lang = ph_wpml_language_switch_admin_email($order, '', 'order', '');

			if ( ! class_exists('Ph_WC_Email_Booking_Cancelled') ) 
			{
				$obj = include_once( 'emails/class-ph-wc-email-booking-cancelled.php' );
				$obj->trigger($order_id, $status, $item_id, $order);
			}
			else
			{
				$obj = new Ph_WC_Email_Booking_Cancelled();

				$obj->trigger($order_id, $status, $item_id, $order);
			}
		}
		// error_log('inside');
	}

	public function email_customer_pending_payment_wc_email( $order_id, $order )
	{	
		// 103410 - Switching to product language
		global $current_lang;
		$current_lang = ph_wpml_language_switch_admin_email($order, '', 'order', '');

		$status = 'pending_payment';
		if ( ! class_exists('Ph_WC_Email_Booking_Requires_Confirmation') ) 
		{
			$obj = include_once( 'emails/class-ph-wc-email-booking-requires-confirmation.php' );
			$obj->trigger($order_id, $status, '', $order);
		}
		else
		{
			$obj = new Ph_WC_Email_Booking_Requires_Confirmation();
			$obj->trigger($order_id, $status, '', $order);
		}
	}

	/**
	 * Init the Registerd email classes
	 * 
	 * @param $email_classes
	 * @return $email_classes
	 */
	public function init_emails($email_classes)
	{
		if (!isset($email_classes['Ph_WC_Email_Booking_Cancelled'])) {

			if (class_exists('Ph_WC_Email_Booking_Cancelled')) {

				$email_classes['Ph_WC_Email_Booking_Cancelled'] = new Ph_WC_Email_Booking_Cancelled();
			} else {

				$email_classes['Ph_WC_Email_Booking_Cancelled'] = include('emails/class-ph-wc-email-booking-cancelled.php');
			}
		}

		if (!isset($email_classes['Ph_WC_Email_Booking_Confirmation'])) {

			if (class_exists('Ph_WC_Email_Booking_Confirmation')) {

				$email_classes['Ph_WC_Email_Booking_Confirmation'] = new Ph_WC_Email_Booking_Confirmation();
			} else {

				$email_classes['Ph_WC_Email_Booking_Confirmation'] = include('emails/class-ph-wc-email-booking-confirmation.php');
			}
		}

		if (!isset($email_classes['Ph_WC_Email_Booking_Payment'])) {

			if (class_exists('Ph_WC_Email_Booking_Payment')) {

				$email_classes['Ph_WC_Email_Booking_Payment'] = new Ph_WC_Email_Booking_Payment();
			} else {

				$email_classes['Ph_WC_Email_Booking_Payment'] = include('emails/class-ph-booking-payment-for-customer.php');
			}
		}

		if (!isset($email_classes['Ph_WC_Email_Booking_Requires_Confirmation'])) {

			if (class_exists('Ph_WC_Email_Booking_Requires_Confirmation')) {

				$email_classes['Ph_WC_Email_Booking_Requires_Confirmation'] = new Ph_WC_Email_Booking_Requires_Confirmation();
			} else {

				$email_classes['Ph_WC_Email_Booking_Requires_Confirmation'] = include('emails/class-ph-wc-email-booking-requires-confirmation.php');
			}
		}

		$reminder_email = get_option('ph_bookings_settings_notifications', []);

		if (
			!isset($email_classes['Ph_WC_Email_Booking_reminder']) &&
			isset($reminder_email['reminder_email_enabled']) && $reminder_email['reminder_email_enabled'] &&
			isset($reminder_email['reminder_email_template']) && $reminder_email['reminder_email_template'] == 'woocommerce'
			) {

			if (!class_exists('Ph_WC_Email_Booking_reminder')) {
					
				include_once 'emails/class-ph-wc-email-booking-reminder.php';
			}

			$email_classes['Ph_WC_Email_Booking_reminder'] = new Ph_WC_Email_Booking_reminder();
		}

		$followup_email = get_option('ph_booking_follow_up_email', []);

		if (
			!isset($email_classes['Ph_WC_Email_Booking_followup']) &&
			isset($followup_email['followup_email_enabled']) && $followup_email['followup_email_enabled'] &&
			isset($followup_email['followup_email_template']) && $followup_email['followup_email_template'] == 'woocommerce'
			) {
				
			if (!class_exists('Ph_WC_Email_Booking_followup')) {
				include_once 'emails/class-ph-wc-email-booking-followup.php';
			}

			$email_classes['Ph_WC_Email_Booking_followup'] = new Ph_WC_Email_Booking_followup();
		}

		if (!isset($email_classes['Ph_WC_Email_Booking_Waiting_For_Approval'])) {

			if (class_exists('Ph_WC_Email_Booking_Waiting_For_Approval')) {
				
				$email_classes['Ph_WC_Email_Booking_Waiting_For_Approval'] = new Ph_WC_Email_Booking_Waiting_For_Approval();
			} else {
				
				$email_classes['Ph_WC_Email_Booking_Waiting_For_Approval'] = include_once('emails/class-ph-wc-admin-email-booking-waiting-for-approval.php');
			}
		}

		if (!isset($email_classes['Ph_WC_Email_Booking_Cancelled_For_Admin'])) {

			if (class_exists('Ph_WC_Email_Booking_Cancelled_For_Admin')) {
				
				$email_classes['Ph_WC_Email_Booking_Cancelled_For_Admin'] = new Ph_WC_Email_Booking_Cancelled_For_Admin();
			} else {
				
				$email_classes['Ph_WC_Email_Booking_Cancelled_For_Admin'] = include_once('emails/class-ph-wc-admin-email-booking-cancelled.php');
			}
		}

		return $email_classes;
	}

	public function email_admin_pending_payment( $order_id, $order ){

		$admin_emails = wc()->mailer()->emails;
		$new_order_admin_email = $admin_emails['Ph_WC_Email_Booking_Waiting_For_Approval']->get_recipient();
		$to 		= $new_order_admin_email;

		$admin_user = get_user_by( 'email', $new_order_admin_email );
		$admin_user_id = $admin_user->ID;

		//103401 - Admin Email Language Fix
		global $sitepress_active_check;
		$current_language = '';
		if($sitepress_active_check)
		{
			$admin_locale = get_user_meta($admin_user_id,'locale',1);
			$admin_locale = !empty($admin_locale) ? $admin_locale : apply_filters('wpml_default_language', NULL ) ;
			if(!empty($admin_locale))
			{
				// WPML Support - Switch to Admin Language For All Email Content and Store Current Language Before Changing to Admin Language
				$current_language = ph_wpml_language_switch_admin_email($order, $admin_user_id, $lang_basis='admin');
			}
		}

		if ( !class_exists('Ph_WC_Email_Booking_Waiting_For_Approval') ) {
			
			$obj = include_once( 'emails/class-ph-wc-admin-email-booking-waiting-for-approval.php' );
		} else {

			$obj = new Ph_WC_Email_Booking_Waiting_For_Approval();
		}

		$obj->trigger($order_id, $to, $order, $admin_user_id);

		if(!empty($current_language))
		{
			// WPML Support - Switch back to current language after sending email
			ph_wpml_language_switch_admin_email($order, $admin_user_id, $lang_basis='current', $current_language);
		}
	}
	public function email_customer_pending_payment( $order_id, $order ){
		$obj = new Ph_Bookings_Email_Content();
		$status = 'pending_payment';
		$obj->init( $order, false, $status );
		$subject 	= $obj->get_email_subject();
		$content 	= $obj->get_email_contents();
		$to 		= $order->get_billing_email();
		if( !empty($to) ){
			$this->send( $to, $subject, $content );
		}
	}

	public function email_admin_booking_status_changed( $status, $item_id, $order_id, $order='' ){

		$return = false;
        $return = apply_filters('ph_filter_do_not_send_cancellation_email', false, $status, 'admin-email');
        if ($return)
        {
            return;
        }

		if( empty($order) ){
			$order = wc_get_order($order_id);
		}

		$admin_emails = wc()->mailer()->emails;
		$new_order_admin_email = $admin_emails['Ph_WC_Email_Booking_Cancelled_For_Admin']->get_recipient();
		$to 		= $new_order_admin_email;

		//103401 - Admin Email Language Fix
		$admin_user = get_user_by( 'email', $new_order_admin_email );
		$admin_user_id = $admin_user->ID;

		global $sitepress_active_check;
		$current_language = '';
		if($sitepress_active_check)
		{
			// WPML Support - Changing to admin language to send email
			$admin_locale = get_user_meta($admin_user_id,'locale',1);
			$admin_locale = !empty($admin_locale) ? $admin_locale : apply_filters('wpml_default_language', NULL ) ;
			if(!empty($admin_locale))
			{
				$current_language = ph_wpml_language_switch_admin_email($order, $admin_user_id, $lang_basis='admin');
			}
		}

		if ( $status=='cancelled' ) { 

			if ( ! class_exists('Ph_WC_Email_Booking_Cancelled_For_Admin') ) 
			{
				$obj = include_once( 'emails/class-ph-wc-admin-email-booking-cancelled.php' );
			} else {

				$obj = new Ph_WC_Email_Booking_Cancelled_For_Admin();
			}

			$obj->trigger($order_id, $to, $order, $admin_user_id);
		}

		if(!empty($current_language))
		{
			// WPML Support - Switch back to current language after sending email
			ph_wpml_language_switch_admin_email($order, $admin_user_id, $lang_basis='current', $current_language);
		}
	}

	public function email_customer_booking_status_changed( $status, $item_id,  $order_id, $order='' ){
		// Customer emails will use new templates
		if($status == 'cancelled' || $status == 'confirmed')
		{
			return;
		}

		$return = false;
        $return = apply_filters('ph_filter_do_not_send_cancellation_email', false, $status, 'customer-email');
        if ($return)
        {
            return;
        }
        
		if( ! is_a($order, 'WC_Order') ){
			$order = wc_get_order($order_id);
		}

		$obj = new Ph_Bookings_Email_Content();
		$obj->init( $order, false, $status );
		$subject = $obj->get_email_subject();
		$content = $obj->get_email_contents($item_id);

		$to = $order->get_billing_email();
		
		if( !empty($to) && !empty($subject) && !empty($content) ){
			$this->send( $to, $subject, $content );
		}
	}

	private function send( $to, $subject, $message ){

		$header = array(
			"Content-Type: text/html; charset=UTF-8"
		);
		$from_address	= get_option( 'woocommerce_email_from_address' );
		$from_name		= get_option( 'woocommerce_email_from_name');
		$header[]		= "From : ".wp_specialchars_decode( esc_html($from_name), ENT_QUOTES )." <$from_address>";
		$return  = wp_mail( $to, $subject, $message, $header, '' );
	}

	/**
	 * Override the template
	 * 
	 * @param string $woocommerce
	 * @param string $template
	 * @return string $woocommerce or ph-bookings-appointments-woocommerce
	 */
	public function ph_custom_woocommerce_template_directory( $woocommerce, $template ){ 
		// error_log($template);
		$ph_templates = array(
			'emails/ph-customer-booking-cancelled.php',
			'emails/ph-customer-booking-confirmed.php',
			'emails/ph-customer-booking-requires-confirmation.php',
			'emails/ph-customer-booking-reminder.php',
			'emails/ph-customer-booking-followup.php',
			'emails/ph-admin-booking-cancelled.php',
			'emails/ph-admin-booking-waiting-for-approval.php',
		);
		if(in_array($template, $ph_templates))
		{
			return 'ph-bookings-appointments-woocommerce';
		}
		return $woocommerce;
	}

	/**
	 * Modify the Booked from and Booked to dates according to the wordpress timezone for the admin email
	 * 
	 * @param $order_details
	 * @return $order_details
	 */
	public function ph_woocommerce_email_order_items_args($order_details)
	{

		$display_settings 	= get_option('ph_bookings_display_settigns');

		if ($order_details['sent_to_admin'] && isset($display_settings['time_zone_conversion_enable']) && $display_settings['time_zone_conversion_enable'] == 'yes') {

			$booked_from_text 	= isset($display_settings['text_customisation']['booked_from_text']) && !empty($display_settings['text_customisation']['booked_from_text']) ? __($display_settings['text_customisation']['booked_from_text'], 'bookings-and-appointments-for-woocommerce') : __("Booked From", 'bookings-and-appointments-for-woocommerce');
			$booked_to_text 	= isset($display_settings['text_customisation']['booked_to_text']) && !empty($display_settings['text_customisation']['booked_to_text']) ? __($display_settings['text_customisation']['booked_to_text'], 'bookings-and-appointments-for-woocommerce') : __("Booked To", 'bookings-and-appointments-for-woocommerce');

			foreach ($order_details['items'] as $item) {

				$product 				 = wc_get_product($item->get_product_id());
				if ($product instanceof WC_Product_phive_booking) {

					// Skip the item if interval period of is either day or month
					$interval_details	= $item->get_meta('_phive_booking_product_interval_details');

					if ($interval_details['interval_format'] == 'day' || $interval_details['interval_format'] == "month") {
						continue;
					}
					// Loop through all the items and modify the Booked From and Booked To dates
					foreach ($item->get_meta_data() as $meta) {

						if ($meta->key == $booked_from_text) {

							$from 			= $item->get_meta('From');
							$meta->value	= Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format(current($from));
						} else if ($meta->key == $booked_to_text) {

							// Add one interval for to dates since to contains start time
							$interval			= $interval_details['interval'];
							$interval_format	= $interval_details['interval_format'];
							$from 				= $item->get_meta('From');
							$to 				= $item->get_meta('To');
							$to 				= empty($to) ? date('Y-m-d H:i', strtotime("+$interval $interval_format", strtotime(current($from)))) : date('Y-m-d H:i', strtotime("+$interval $interval_format", strtotime(current($to))));

							$meta->value		= Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format($to);
						}
					}
				}
			}
		}

		return $order_details;
	}
	
}
new ph_booking_email_manager();