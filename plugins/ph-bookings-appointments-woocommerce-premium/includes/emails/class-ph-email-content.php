<?php

if( ! defined('ABSPATH') )	return;

if( ! class_exists('Ph_Bookings_Email_Content') ) {
	class Ph_Bookings_Email_Content {

		/**
		 * @var $order
		 */
		public $order;

		/**
		 * @var $sent_to_admin
		 */
		public $sent_to_admin;

		/**
		 * @var $booking_status
		 */
		public $booking_status;

		/**
		 * @var $blog_name
		 */
		public $blog_name;

		/**
		 * @var $email_bg_color
		 */
		public $email_bg_color;

		/**
		 * @var $email_body_bg_color
		 */
		public $email_body_bg_color;

		/**
		 * @var $email_base_color
		 */
		public $email_base_color;

		/**
		 * @var $email_text_color
		 */
		public $email_text_color;

		/**
		 * @var $wp_date_format
		 */
		public $wp_date_format;

		/**
		 * @var $email_base_text
		 */
		public $email_base_text;

		/**
		 * @var $email_bg_color_darker_10
		 */
		public $email_bg_color_darker_10;

		/**
		 * @var $email_body_bg_color_darker_10
		 */
		public $email_body_bg_color_darker_10;

		/**
		 * @var $email_base_color_lighter_20
		 */
		public $email_base_color_lighter_20;

		/**
		 * @var $email_base_color_lighter_40
		 */
		public $email_base_color_lighter_40;

		/**
		 * @var $email_base_text_lighter_20
		 */
		public $email_base_text_lighter_20;

		/**
		 * @var $email_base_text_lighter_40
		 */
		public $email_base_text_lighter_40;

		/**
		 * @var $billing_address
		 */
		public $billing_address;

		/**
		 * Initialize
		 */
		public function init( $order, $sent_to_admin= false, $booking_status='' ) {
			$this->order 			= $order;
			$this->sent_to_admin 	= $sent_to_admin;
			$this->booking_status	= $booking_status;
			$this->blog_name		= get_option('blogname');

			// Load colors.
			$this->email_bg_color				= get_option( 'woocommerce_email_background_color' );
			$this->email_body_bg_color			= get_option( 'woocommerce_email_body_background_color' );
			$this->email_base_color				= get_option( 'woocommerce_email_base_color' );
			$this->email_text_color				= get_option( 'woocommerce_email_text_color' );
			$this->wp_date_format				= get_option( 'date_format' );
			
			$this->email_base_text 				= wc_light_or_dark( $this->email_base_color, '#202020', '#ffffff' );
			
			$this->email_bg_color_darker_10    = wc_hex_darker( $this->email_bg_color, 10 );
			$this->email_body_bg_color_darker_10  = wc_hex_darker( $this->email_body_bg_color, 10 );
			$this->email_base_color_lighter_20 = wc_hex_lighter( $this->email_base_color, 20 );
			$this->email_base_color_lighter_40 = wc_hex_lighter( $this->email_base_color, 40 );
			$this->email_base_text_lighter_20 = wc_hex_lighter( $this->email_base_text, 20 );
			$this->email_base_text_lighter_40 = wc_hex_lighter( $this->email_base_text, 40 );
			
		}

		/**
		 * Get Email Subject.
		 * @return string
		 */
		public function get_email_subject() {
			$subject = null;
			if( $this->booking_status == 'confirmed' || $this->booking_status=='cancelled' ) {
				$subject = sprintf( __( 'Your Booking at %s is %s', 'bookings-and-appointments-for-woocommerce' ), $this->get_blogname(),  __($this->booking_status,'bookings-and-appointments-for-woocommerce') );
			}
			elseif( $this->booking_status == 'pending_payment' ) {
				$subject = sprintf( __( 'Your Booking request at %s is awaiting approval', 'bookings-and-appointments-for-woocommerce' ), $this->get_blogname() );
			}
			return $subject;
		}

		/**
		 * Get blog name formatted for emails.
		 * @return string
		 */
		private function get_blogname() {
			return wp_specialchars_decode( $this->blog_name, ENT_QUOTES );
		}

		public function get_email_contents($item_id='') {
			$content = null;
			if( is_a( $this->order, 'WC_Order') ) {
				// Style
				$content .= "<body style='background-color:$this->email_bg_color;'>";
				$content .= "<div style='padding:9% 9%; padding-bottom:0;'>";
				$content .= "<div style ='box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1) !important;border: 1px solid $this->email_bg_color_darker_10; border-radius: 3px !important;'>";
						
						// Heading
						$content.="<div id='template_header_image'>";
								if ( $img = get_option( 'woocommerce_email_header_image' ) ) {
									$content.='<p style="margin-top:0;"><img src="' . esc_url( $img ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" /></p>';
								}
						$content.="</div>";
						$content .= "<div style='background-color:$this->email_base_color;color:$this->email_base_text;padding:20px 30px;font-size:30px;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;'>";
							if( $this->booking_status == 'pending_payment' )	$content .= __( 'We have received your booking', 'bookings-and-appointments-for-woocommerce');
							elseif( $this->booking_status == 'confirmed' )	$content .= __( 'Thank you for booking with us', 'bookings-and-appointments-for-woocommerce' );
							elseif( $this->booking_status == 'cancelled' )	$content .= sprintf( __( 'Your booking at %s is cancelled', 'bookings-and-appointments-for-woocommerce' ), $this->get_blogname() );
							else	$content .= __( "Thanks for shopping with us", 'bookings-and-appointments-for-woocommerce');
						$content .= "</div>";

						$content .= "<div style='color:$this->email_text_color;background-color:$this->email_body_bg_color;padding:20px;font-size:17px;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;'>";
							$content .= $this->get_content_before_product_info();
							if( $this->booking_status == 'confirmed' ) {
								$content .= "<br><br>".__( "Please click below link to proceed with the payment ",'bookings-and-appointments-for-woocommerce')."</br><small><a href='".$this->order->get_checkout_payment_url()."' target='_blank' >".$this->order->get_checkout_payment_url().'</a></small>';
							}
							$content .= "<br><br><span style='color:$this->email_base_color;font-size:20px;'>".sprintf( '['.__( 'Order','bookings-and-appointments-for-woocommerce').' #%s] (%s)', $this->order->get_order_number(), ph_wp_date($this->wp_date_format) )."</span><br><br>";
							// Product details
							$content .= $this->get_product_info_as_table($item_id);
							// Address
							$content .= $this->get_address();
						$content .= "</div>";
					$content .= "</div>";
						//footer
						$footer = "";
						$footer = '<table id="" width="100%"><tr><td align="center" valign="top">';
						$footer .= '<table border="0" cellpadding="10" cellspacing="0" width="100%" id="template_footer"><tr><td valign="top"><table border="0" cellpadding="10" cellspacing="0" width="100%"><tr><td colspan="2" valign="middle" id="credit" align="center"><small style="text-align:center; color:#6c757d!important;">';
// 						$footer .= wp_kses_post( wpautop( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) ); 
						
						$footer_modified_string = $this->replace_placeholders( get_option( 'woocommerce_email_footer_text' ) ) ;
				
						$footer .= wp_kses_post( wpautop( wptexturize($footer_modified_string) ) );
 						
						$footer .= "</small></td></tr></table></td></tr></table>";
						$footer .= '</td></tr></table>';
						$content .= $footer;
				$content .= "</body>";
			}
			return $content;
		}
		
		public function replace_placeholders( $string ) 
		{
			
			$domain = wp_parse_url( home_url(), PHP_URL_HOST );

			return str_replace(
				array(
					'{site_title}',
					'{site_address}',
					'{site_url}',
					'{woocommerce}',
					'{WooCommerce}',
				),
				array(
					$this->get_blogname(),
					$domain,
					$domain,
					'<a href="https://woocommerce.com">WooCommerce</a>',
					'<a href="https://woocommerce.com">WooCommerce</a>',
				),
				$string
			);
		}

		/**
		 * Get Content Before Order Line item Table.
		 * @return string
		 */
		private function get_content_before_product_info() {
			$content = null;
			$this->billing_address = $this->order->get_address();
			$content .= __( 'Hi', 'bookings-and-appointments-for-woocommerce' ).' '. $this->billing_address['first_name'].',<br><br>';
			if( $this->booking_status == 'pending_payment' ) {
				$content .= sprintf( __( 'Your Booking request at %s is awaiting approval.', 'bookings-and-appointments-for-woocommerce' ), $this->get_blogname());
			}
			elseif( $this->booking_status == 'confirmed' ) {	
				$content .= sprintf( __( 'Your booking at %s is approved.', 'bookings-and-appointments-for-woocommerce' ), $this->get_blogname() );
			}
			else{
				$content .= sprintf( __( 'Your booking at %s has been marked as %s.', 'bookings-and-appointments-for-woocommerce'), $this->blog_name, __($this->booking_status,'bookings-and-appointments-for-woocommerce') );
			}
			return $content;
		}

		/**
		 * Get bookings product info as table.
		 */
		private function get_product_info_as_table($item_id='') {
			$order_items = $this->order->get_items();
			$content = null;
			$table_td_style = "style='border: 1px solid #dddddd; padding:10px;font-size:17px;'";
			$table_td_title_style = "style='border: 1px solid #dddddd; padding:10px;font-size:20px;'";
			if( ! empty($order_items) ) {
				$content .= "<table  style='border-collapse:collapse; width:100%;color:$this->email_text_color;'>";
					$content .= "<tr>
									<td $table_td_title_style>".__( 'Product', 'bookings-and-appointments-for-woocommerce')."</th>
									<td $table_td_title_style>".__( 'Price', 'bookings-and-appointments-for-woocommerce')."</th>
								</tr>";
					foreach( $order_items as $order_item_id => $order_item ) {
						$product 	= $order_item->get_product();
						if( empty($product) || $product->get_type() !='phive_booking' || (!empty($item_id) && $item_id!=$order_item_id) ){
							continue;
						}
						$content .= "<tr>
										<td $table_td_style>".$order_item->get_name().$this->get_order_item_meta_data($order_item)."</td>".
										"<td $table_td_style>".wp_kses_post( $this->order->get_formatted_line_subtotal( $order_item ))."</td>
									</tr>";
					}
				$content .= "</table>";
			}
			return $content;
		}

		/**
		 * Get Meta data of Line Items that needs to be sent in email.
		 * @return string in the form of Unordered list
		 */
		private function get_order_item_meta_data( $order_item ) {
			$content = null;
			$meta_datas = $order_item->get_meta_data();
			$product = $order_item->get_product();
			$key_filter=apply_filters('ph_bookings_order_meta_key_filters_for_admin',array('confirmed','canceled','FollowUpTime'), $order_item);
			foreach( $meta_datas as $meta_data ) {
				$meta_data = $meta_data->get_data();
				if( ! empty($meta_data['value']) && ! is_array($meta_data['value']) ) {
					if( in_array($meta_data['key'], $key_filter) )	continue;
					$meta_data['key'] = apply_filters( 'woocommerce_attribute_label', $meta_data['key'], $meta_data['key'], $product);
					$content .= "<li>".__($meta_data['key'], 'bookings-and-appointments-for-woocommerce').": ".__($meta_data['value'], 'bookings-and-appointments-for-woocommerce')."</li>";
				}
			}

			if( ! empty($content) ) {
				$content = "<ul style='padding:10px;font-size:12px;'>".$content."</ul>";
			}
			return $content;
		}

		/**
		 * Get Address as html
		 */
		private function get_address() {
			$billing_address  = $this->order->get_address();
			$shipping_address = $this->order->get_address('shipping');
			$content = "<br><table style='width:100%;border-collapse:collapse;'>
							<tr>
								<td style='color:$this->email_base_color; font-size:20px; padding:10px 0px;'>".
									__( 'Billing address', 'bookings-and-appointments-for-woocommerce')."
								</td>";
			if( ! empty($shipping_address) ) {
				$content .= "<td style='color:$this->email_base_color; font-size:20px; padding:10px 0px;'>".__( 'Shipping address', 'bookings-and-appointments-for-woocommerce' )."</td>";
			}

			$content .= "</tr><tr>
							<td style='color:$this->email_text_color;border: 1px solid #dddddd; padding:10px;font-size:15px;'>".
								$billing_address['company']."<br>".
								$billing_address['first_name']." ".$billing_address['last_name']."<br>".
								$billing_address['address_1']."<br>".
								$billing_address['address_2']."<br>".
								$billing_address['city']." ".$billing_address['state']." ".$billing_address['postcode']."<br>".
								$billing_address['country']."<br>".
								$billing_address['phone']."<br>".
								$billing_address['email'].
							"</td>";
			if( ! empty($shipping_address) ) {
				$content .= "<td style='color:$this->email_text_color;border: 1px solid #dddddd; padding:10px;font-size:15px;'>".
									$shipping_address['company']."<br>".
									$shipping_address['first_name']." ".$shipping_address['last_name']."<br>".
									$shipping_address['address_1']."<br>".
									$shipping_address['address_2']."<br>".
									$shipping_address['city']." ".$shipping_address['state']." ".$shipping_address['postcode']."<br>".
									$shipping_address['country']."<br>"."
							</td>";
			}
			$content .= "</tr></table><br>";
			return $content;
		}

	}
}