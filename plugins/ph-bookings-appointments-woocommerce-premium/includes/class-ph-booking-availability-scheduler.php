<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* This class handle all the Cron jobs.
*/
class phive_booking_availability_scheduler{
	public function __construct() {
		add_action( 'ph-unfreez-booking-slot', array( $this, 'phive_clear_scheduled_unfreez' ) );
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'before_cart_item_quantity_zero' ), 10, 1 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'cart_item_removed' ), 20 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'cart_item_restored' ), 20 );
		$this->booking_slot_freez_setup_post_type();
		
		add_action( 'woocommerce_thankyou', array($this, 'phive_order_placed'), 10, 1);
	}

	/**
	* Register custom post type. The post is used for storing the booking details on the slot take to cart.
	* @return null
	*/
	public function booking_slot_freez_setup_post_type() {
		$args = array(
			'public'	=> false,
			'map_meta_cap' => true,
		);
		register_post_type( 'booking_slot_freez', $args );
	}

	/**
	* Delete the crone after the order is placed.
	* @param  Order_id
	* @return false 
	*/
	function phive_order_placed( $order_id ){
		$freezer_ids = WC()->session != null ? WC()->session->get( 'ph_crone_ids' ) : '';
		WC()->session->__unset( 'ph_crone_ids' );
		if( !empty($freezer_ids) ){
			foreach ($freezer_ids as $freezer_id) {
				$this->phive_clear_scheduled_unfreez( $freezer_id );
			}
		}
	}

	/**
	* schedule the unfreez if restored (Undo on removed item) the cart item.
	* @return null
	*/
	public function cart_item_restored( $cart_item_key ) {
		$cart	  = WC()->cart->get_cart();
		$cart_item = $cart[ $cart_item_key ];
		if ( isset( $cart_item['phive_booking_freezer_id'] ) ) 
		{
			if(isset($cart_item['phive_booking_buffer_to_freezer_id']))
			{
				$this->schedule_unfreez( $cart_item['phive_booking_buffer_to_freezer_id'] );
			}
			if(isset($cart_item['phive_booking_buffer_from_freezer_id'])){
				$this->schedule_unfreez( $cart_item['phive_booking_buffer_from_freezer_id'] );
			}
			$this->schedule_unfreez( $cart_item['phive_booking_freezer_id'] );
			if (isset($cart_item['phive_booked_assets'] ) && !empty($cart_item['phive_booked_assets'])) 
			{
				$asset_id = $cart_item['phive_booked_assets'];
				$ph_cache_obj = new phive_booking_cache_manager();
				$ph_cache_obj->ph_unset_cache($asset_id);
			}
		}
	}

	/**
	* Clear the schedule on item removed from cart.
	* @return null
	*/
	public function cart_item_removed( $cart_item_key ) {
		$cart_item = WC()->cart->removed_cart_contents[ $cart_item_key ];
		if ( isset( $cart_item['phive_booking_freezer_id'] ) ) {
			$post_id = $cart_item['phive_booking_freezer_id'];
			$this->phive_clear_scheduled_unfreez( $post_id );
			if(isset($cart_item['phive_booking_buffer_to_freezer_id'])){
				$buffer_to_id = $cart_item['phive_booking_buffer_to_freezer_id'];
				$this->phive_clear_scheduled_unfreez( $buffer_to_id );
			}
			if(isset($cart_item['phive_booking_buffer_from_freezer_id'])){
				$buffer_from_id = $cart_item['phive_booking_buffer_from_freezer_id'];
				$this->phive_clear_scheduled_unfreez( $buffer_from_id );
			}
			if (isset($cart_item['phive_booked_assets'] ) && !empty($cart_item['phive_booked_assets'])) 
			{
				$asset_id = $cart_item['phive_booked_assets'];
				$ph_cache_obj = new phive_booking_cache_manager();
				$ph_cache_obj->ph_unset_cache($asset_id);
			}
		}
	}
	
	public function before_cart_item_quantity_zero( $cart_item_key ) {
		$cart		= WC()->cart->get_cart();
		$cart_item 	= $cart[ $cart_item_key ];

		if ( isset($cart_item['phive_booking_freezer_id']) ) {
			$post_id = $cart_item['phive_booking_freezer_id'];
			$this->phive_clear_scheduled_unfreez( $post_id );
			if(isset($cart_item['phive_booking_buffer_to_freezer_id'])){
				$buffer_to_id = $cart_item['phive_booking_buffer_to_freezer_id'];
				$this->phive_clear_scheduled_unfreez( $buffer_to_id );
			}
			if(isset($cart_item['phive_booking_buffer_from_freezer_id'])){
				$buffer_from_id = $cart_item['phive_booking_buffer_from_freezer_id'];
				$this->phive_clear_scheduled_unfreez( $buffer_from_id );
			}
			if (isset($cart_item['phive_booked_assets'] ) && !empty($cart_item['phive_booked_assets'])) 
			{
				$asset_id = $cart_item['phive_booked_assets'];
				$ph_cache_obj = new phive_booking_cache_manager();
				$ph_cache_obj->ph_unset_cache($asset_id);
			}
		}
	}

	/**
	* Freez the booking slot by inserting a post wtih post type 'booking_slot_freez'. and schedule the unfreez (remove the post) after 15 minuts.
	* @return int
	*/
	public function freeze_booking_slot( $product_id, $from, $to, $asset_id='', $person_as_booking='', $number_of_booking='', $is_buffer='', $buffer_type='', $book_to_date_with_night = array() ,$phive_book_persons=array()){
		global $wpdb;

		// Since PHP 8.1, strlen throws warning when NULL is provided as a parameter
		$from = !empty($from) ? $from : '';
		$to = !empty($to) ? $to : '';

		if( strlen($to) == 16 ){
			$date_format = 'Y-m-d H:i';
		}elseif ( strlen($to) == 10 ) {
			$date_format 	= "Y-m-d";
		}else{
			$date_format 	= "Y-m";
		}


		$from_str 	= strtotime($from);
		$to_str 	= strtotime($to);
		/*$interval_type 			= get_post_meta( $product_id, "_phive_book_interval_period", 1 );
		$charge_per_night 		= get_post_meta( $product_id, '_phive_book_charge_per_night', 1 );

		//If charge_per_night, remove last slot
		if( $charge_per_night == 'yes' && $interval_type=='day' && $to_str > $from_str ){
			$to = date ( $date_format, strtotime( "-1 $interval_type", $to_str ) ) ;
		}*/

		$new_post = array(
			'ID' => '',
			'post_type' => 'booking_slot_freez', // Custom Post Type Slug
			'post_status' => 'open',
			'post_title' => 'Booking slot freezer-'.uniqid(),//171763 Issue: When activating our Bookings plugin, its taking a quite long time while checkout.
			'ping_status' => 'closed',
		);

		$freezer_id = wp_insert_post($new_post);
		if( !$freezer_id ){
			return false;
		}
		if($is_buffer == 'yes' && $buffer_type == 'buffer-before'){
			$meta_values = array(
			'_product_id' 			=> $product_id,
			'Buffer_before_From'	=> $from,
			'Buffer_before_To'		=> $to,
			'_booking_customer_id'	=> is_user_logged_in() ? get_current_user_id() : 0,
			'Number of persons' 	=> $number_of_booking,
			'person_as_booking' 	=> $person_as_booking,
			'participants'	=> $phive_book_persons
			
		);
		}elseif($is_buffer == 'yes' && $buffer_type == 'buffer-after'){
			$meta_values = array(
			'_product_id' 			=> $product_id,
			'Buffer_after_From'	=> $from,
			'Buffer_after_To'		=> $to,
			'_booking_customer_id'	=> is_user_logged_in() ? get_current_user_id() : 0,
			'Number of persons' 	=> $number_of_booking,
			'person_as_booking' 	=> $person_as_booking,
			'participants'	=> $phive_book_persons
			
		);
		}else{
			$meta_values = array(
			'_product_id' 			=> $product_id,
			'From'					=> $from,
			'To'					=> $to,
			'_booking_customer_id'	=> is_user_logged_in() ? get_current_user_id() : 0,
			'Number of persons' 	=> $number_of_booking,
			'person_as_booking' 	=> $person_as_booking,
			'participants'	=> $phive_book_persons
			
			);	
		}
		
		// Handle Bookings with per night
		if( ! empty($book_to_date_with_night) ) $meta_values['_ph_book_to_date_with_night'] = $book_to_date_with_night;
		
		if($asset_id && $is_buffer!='yes'){
			$meta_values['asset_id']	= $asset_id;
		}
		else if($asset_id)
		{
			$meta_values['buffer_asset_id']	= $asset_id;	
		}

		foreach ( $meta_values as $meta_key => $value ) {
			update_post_meta( $freezer_id, $meta_key, $value );
		}
		$this->schedule_unfreez( $freezer_id );

		return $freezer_id;
	}


	private function schedule_unfreez( $post_id ){
		$schedule_unfreez_time_in_minutes = apply_filters('ph_schedule_unfreez_time_in_minutes_for_bookings_in_cart', 15, $post_id);
		
		$schedule_unfreez_time_in_minutes = (int) $schedule_unfreez_time_in_minutes;

		wp_schedule_single_event( time() + ( 60 * $schedule_unfreez_time_in_minutes ) , "ph-unfreez-booking-slot", array( $post_id ) );
	}

	/**
	* Remove the post and clear the schedule
	*/
	public function phive_clear_scheduled_unfreez( $post_id ){
		$asset_id = get_post_meta( $post_id, 'asset_id', 1 );
		// error_log("asset id".$asset_id);
		if ($asset_id != '') 
		{
			$ph_cache_obj = new phive_booking_cache_manager();
			$ph_cache_obj->ph_unset_cache($asset_id);
		}
		wp_delete_post( $post_id );
		wp_clear_scheduled_hook( 'ph-unfreez-booking-slot', array( $post_id ) );
	}
}
new phive_booking_availability_scheduler();