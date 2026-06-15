<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WC_Product_phive_booking_addon_integration {


	public function __construct(){

		//add_filter( 'woocommerce_product_addons_show_grand_total', array( $this, 'phive_addon_hide_total' ), 20, 2 );
		// add_filter('ph_bookings_currency_conversion',array($this,'phive_apply_addon_price_display'),10,2);
		add_filter('phive_booking_cost',array($this,'phive_apply_addon_price'),11,4);
		// add_filter( 'woocommerce_product_addon_cart_item_data', array( $this, 'woocommerce_product_addon_cart_item_data' ), 20, 4 );
		add_filter( 'woocommerce_product_addons_adjust_price', array( $this, 'ph_booking_addon_price_in_cart_page' ),9,2 );
	}
	public function ph_booking_addon_price_in_cart_page( $true_false,$cart_item_data ) {
		if(isset($cart_item_data['phive_book_from_date']))
		{		
			return false;
		}
		return $true_false;
	}



	public function woocommerce_product_addon_cart_item_data($data, $addon, $product_id, $post_data){
		if(ph_is_bookable_product( $product_id) && !isset($post_data['addon_data']) )
		{
			$data=array();
		}

		return $data;
	}
	public function phive_addon_hide_total($show_total, $product){
		if ( $product->is_type( 'phive_booking' ) ) {
			$show_total = false;
		}
		return $show_total;
	}

	public function phive_apply_addon_price($booking_cost, $id, $customer_choosen_values=null, $booking_data=null)
	{
		if (  in_array( 'woocommerce-product-addons/woocommerce-product-addons.php', 
		    apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array('woocommerce-product-addons-master/woocommerce-product-addons.php',apply_filters( 'active_plugins', get_option( 'active_plugins' ) ))) {
			if(!defined('WC_PRODUCT_ADDONS_VERSION'))
			{
				$version=(int)str_replace('.', '', WC_PRODUCT_ADDONS_VERSION);
				if(!isset($_POST['addon_data']) || empty($_POST['addon_data']))
				{
					return $booking_cost;
				}
				parse_str($_POST['addon_data'],$addon_data);
		
	   			// $_POST=array_merge($addon_data,$_POST);
				$addons       = $GLOBALS['Product_Addon_Cart']->add_cart_item_data( array(), $id, $addon_data, true );
				$addon_costs  = 0;
				$participant=1;
				if(isset($_POST['person_details']) && !empty($_POST['person_details']))
				{
					$participant=array_sum($_POST['person_details']);
				}
				$resources_pricing_rules = get_post_meta( $id, "_phive_booking_resources_pricing_rules", 1 );
				$ph_booking_resources_per_person=false;
				if(!empty($resources_pricing_rules))
				{
					foreach ($resources_pricing_rules as $key => $rule) {
						if(isset($rule['ph_booking_resources_per_person']) && $rule['ph_booking_resources_per_person']=='yes')
						{
							$ph_booking_resources_per_person=true;
							break;
						}
					}
				}
				if ( ! empty( $addons['addons'] ) ) {
					foreach ( $addons['addons'] as $addon ) {
						
						$addon['price'] = ( ! empty( $addon['price'] ) ) ? $addon['price'] : 0;

						if($ph_booking_resources_per_person)
							$addon_costs += floatval( $addon['price'] )*$participant ;
						else
							$addon_costs += floatval( $addon['price'] ) ;
					}
				}
			 	$booking_cost= ($booking_cost == '')? 0 : $booking_cost;
				$total = $booking_cost + $addon_costs;
				return $total;
			} // 110387 - prices not working because of incorrect version compare
			else if(defined('WC_PRODUCT_ADDONS_VERSION') && version_compare(WC_PRODUCT_ADDONS_VERSION, '3.0.1', '>='))
			{
				$wc_addon_data 		= isset($_POST['addon_data']) ? $_POST['addon_data'] : '';
				if(empty($wc_addon_data) && isset($booking_data[$id]['wc_addon_data'])){
					$wc_addon_data 	= $booking_data[$id]['wc_addon_data'];
				}
				
				$version = (int)str_replace('.', '', WC_PRODUCT_ADDONS_VERSION);

				if(empty($wc_addon_data))
				{
					return $booking_cost;
				}

				parse_str($wc_addon_data, $addon_data);

				$_POST 				= array_merge($addon_data, $_POST);

				// WPML Compatibility // price not working
				$current_product_id = isset($_POST['current_product_id']) ? $_POST['current_product_id'] : $id;

				// WPML Compatibility // price not working - recalculation from cart
				if(isset($booking_data[$id]['wc_addon_data']) && !empty($booking_data[$id]['wc_addon_data']))
				{
					$current_language = apply_filters( 'wpml_current_language', NULL );
					$old_product_id     = $current_product_id;
					$current_product_id = apply_filters('wpml_object_id', $old_product_id, 'product', false, $current_language );
					if(empty($current_product_id)){
						$current_product_id = $old_product_id;
					}
				}
				// WPML Compatibility END

				// $addons       	= $GLOBALS['Product_Addon_Cart']->add_cart_item_data( array(), $current_product_id, $addon_data, true );
				$addons       		= $this->add_cart_item_data( array(), $current_product_id, $addon_data, true );
				$quantity 			= 1;
				if(isset($_POST['person_details']) && !empty($_POST['person_details']))
				{
					$quantity	= array_sum($_POST['person_details']);
				}
				else if(isset($booking_data[$id]['persons_details']) && !empty($booking_data[$id]['persons_details']))
				{
					$quantity	= array_sum($booking_data[$id]['persons_details']);
				}

			 	$booking_cost 	= ($booking_cost == '') ? 0 : $booking_cost;
				$total 			= $this->get_addon_applied_cost($addons, $quantity, $booking_cost);
				return $total;
			}
			else
			{
				return $booking_cost;
			}
		}
		else{
			return $booking_cost;
		}
	}

	public function phive_apply_addon_price_display($booking_cost,$id) {
		if (  in_array( 'woocommerce-product-addons/woocommerce-product-addons.php', 
		    apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || in_array('woocommerce-product-addons-master/woocommerce-product-addons.php',apply_filters( 'active_plugins', get_option( 'active_plugins' ) ))) {
			// 110387
			if(defined('WC_PRODUCT_ADDONS_VERSION') && version_compare(WC_PRODUCT_ADDONS_VERSION, '3.0.1', '>='))
			{
				$version=(int)str_replace('.', '', WC_PRODUCT_ADDONS_VERSION);
				if(!isset($_POST['addon_data']) || empty($_POST['addon_data']))
				{
					return $booking_cost;
				}
				parse_str($_POST['addon_data'],$addon_data);
		
	   			$_POST=array_merge($addon_data,$_POST);
				$addons       = $GLOBALS['Product_Addon_Cart']->add_cart_item_data( array(), $id, $addon_data, true );
				$addon_costs  = 0;
				$participant=1;
				if(isset($_POST['person_details']) && !empty($_POST['person_details']))
				{
					$participant=array_sum($_POST['person_details']);
				}
				$resources_pricing_rules = get_post_meta( $id, "_phive_booking_resources_pricing_rules", 1 );
				$ph_booking_resources_per_person=false;
				if(!empty($resources_pricing_rules))
				{
					foreach ($resources_pricing_rules as $key => $rule) {
						if(isset($rule['ph_booking_resources_per_person']) && $rule['ph_booking_resources_per_person']=='yes')
						{
							$ph_booking_resources_per_person=true;
							break;
						}
					}
				}
				if ( ! empty( $addons['addons'] ) ) {
					foreach ( $addons['addons'] as $addon ) {
						
						$addon['price'] = ( ! empty( $addon['price'] ) ) ? $addon['price'] : 0;

						if($ph_booking_resources_per_person && isset($addon['price_type']) && $addon['price_type']=='quantity_based')
							$addon_costs += floatval( $addon['price'] )*$participant ;
						else
							$addon_costs += floatval( $addon['price'] ) ;
					}
				}
			 	$booking_cost= ($booking_cost == '')? 0 : $booking_cost;
				$total = $booking_cost + $addon_costs;
				return $total;
			}
			else
			{
				return $booking_cost;
			}
		}
		else{
			return $booking_cost;
		}
		
	}

	public function add_cart_item_data( $cart_item_data, $product_id, $addon_data )
	{
		if ( isset( $_POST ) && ! empty( $product_id ) ) {
			$post_data = $_POST;
		} else {
			return;
		}

		$product_addons = WC_Product_Addons_Helper::get_product_addons( $product_id );

		if ( empty( $cart_item_data['addons'] ) ) {
			$cart_item_data['addons'] = array();
		}

		if ( is_array( $product_addons ) && ! empty( $product_addons ) ) {
			include_once dirname( WC_PRODUCT_ADDONS_MAIN_FILE ) . '/includes/fields/abstract-wc-product-addons-field.php';

			foreach ( $product_addons as $addon ) {
				// If type is heading, skip.
				if ( 'heading' === $addon['type'] ) {
					continue;
				}

				$value = wp_unslash( isset( $post_data[ 'addon-' . $addon['field_name'] ] ) ? $post_data[ 'addon-' . $addon['field_name'] ] : '' );

				switch ( $addon['type'] ) {
					case 'checkbox':
						include_once dirname( WC_PRODUCT_ADDONS_MAIN_FILE ) . '/includes/fields/class-wc-product-addons-field-list.php';
						$field = new WC_Product_Addons_Field_List( $addon, $value );
						$data = $field->get_cart_item_data();
						break;
					case 'multiple_choice':
						switch ( $addon['display'] ) {
							case 'radiobutton':
								include_once dirname( WC_PRODUCT_ADDONS_MAIN_FILE ) . '/includes/fields/class-wc-product-addons-field-list.php';
								$field = new WC_Product_Addons_Field_List( $addon, $value );
								$data = $field->get_cart_item_data();
								break;
							case 'images':
							case 'select':
								include_once dirname( WC_PRODUCT_ADDONS_MAIN_FILE ) . '/includes/fields/class-wc-product-addons-field-select.php';
								$field = new WC_Product_Addons_Field_Select( $addon, $value );
								$data = $field->get_cart_item_data();
								break;
						}
						break;
					case 'custom_text':
					case 'custom_textarea':
					case 'custom_price':
					case 'input_multiplier':
						include_once dirname( WC_PRODUCT_ADDONS_MAIN_FILE ) . '/includes/fields/class-wc-product-addons-field-custom.php';
						$field = new WC_Product_Addons_Field_Custom( $addon, $value );
						$data = $field->get_cart_item_data();
						break;
					case 'file_upload':
						include_once dirname( WC_PRODUCT_ADDONS_MAIN_FILE ) . '/includes/fields/class-wc-product-addons-field-file-upload.php';
						$field = new WC_Product_Addons_Field_File_Upload( $addon, $value );
						$validate = $field->validate();
						if($validate == 1)
						{
							$adjust_price   = $addon['adjust_price'];
							$field_name 	= $field->get_field_name();
							if((isset($_FILES[$field_name]['name']) && !empty($_FILES[$field_name]['name'])) || (isset($addon_data[$field_name]) && !empty($addon_data[$field_name])))
							{
								$data = array(
									array
									(
										'name'    		=> sanitize_text_field( $addon['name'] ),
										'value'	 		=> '',		// just to use for calculation
										'display'		=> '',		// just to use for calculation
										'price'   		=> '1' != $adjust_price ? 0 : floatval( sanitize_text_field( $addon['price'] ) ),
										'field_name' 	=> $addon['field_name'],
										'field_type' 	=> $addon['type'],
										'price_type' 	=> $addon['price_type'],
									)
								);
							}
							else{
								continue 2;
							}
						}
						break;
				}
				if ( $data ) {
					$cart_item_data['addons'] = array_merge( $cart_item_data['addons'], apply_filters( 'woocommerce_product_addon_cart_item_data', $data, $addon, $product_id, $post_data ) );
				}
			}
		}

		return $cart_item_data;
	}

	public function get_addon_applied_cost( $addons, $quantity, $booking_cost )
	{
		if ( !empty( $addons['addons'] ) )
		{
			$price = $booking_cost;

			foreach ( $addons['addons'] as $addon )
			{
				$price_type  = $addon['price_type'];
				$addon_price = !empty($addon['price']) ? $addon['price'] : 0;

				switch ( $price_type ) {
					case 'percentage_based':
						$price         += (float) ( $booking_cost * ( $addon_price / 100 ) );
						break;
					case 'quantity_based':
						$price         += (float) ( $addon_price * $quantity );
						break;
					case 'flat_fee':
					default:
						$price         += (float) $addon_price;
						break;
				}
			}
			return $price;
		}

		return $booking_cost;
	}

}
new WC_Product_phive_booking_addon_integration();