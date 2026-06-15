<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class phive_booking_order_manager{

	/**
	 * @var $booked_from_text
	 */
	public $booked_from_text;

	/**
	 * @var $booked_to_text
	 */
	public $booked_to_text;

	/**
	 * @var $older_booking_values
	 */
	public $older_booking_values;

	/**
	 * @var $older_participant_values
	 */
	public $older_participant_values;

	/**
	 * @var $older_order_address
	 */
	public $older_order_address;

	/**
	 * Construct
	 */
	public function __construct() {

		// 175970
		$display_settings = get_option('ph_bookings_display_settigns');
		$text_customisation = isset($display_settings['text_customisation']) ? $display_settings['text_customisation'] : array();
		$this->booked_from_text =	isset($text_customisation['booked_from_text']) && !empty($text_customisation['booked_from_text']) ? __($text_customisation['booked_from_text'], 'bookings-and-appointments-for-woocommerce') : __("Booked From", 'bookings-and-appointments-for-woocommerce');
		$this->booked_to_text =	isset($text_customisation['booked_to_text']) && !empty($text_customisation['booked_to_text']) ? __($text_customisation['booked_to_text'], 'bookings-and-appointments-for-woocommerce') : __("Booked To", 'bookings-and-appointments-for-woocommerce');
		
		add_action( 'woocommerce_order_item_meta_end', array( $this, 'phive_disply_cancel_button' ), 10, 4 );
		// add_filter( 'woocommerce_display_item_meta', array( $this, 'phive_disply_cancel_buttons' ), 10, 4 );
		add_action( 'woocommerce_admin_html_order_item_class', array($this, 'phive_return_product_css_class'), 5, 3 );
		
		add_action( 'woocommerce_after_order_itemmeta', array($this, 'phive_after_order_itemmeta_contents'), 5, 3 );		// Need to remove after few version Added in - 1.1.6, It's getting handled
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'phive_hide_order_itemmeta' ), 10 );
		
		add_action( 'woocommerce_order_status_cancelled', array($this, 'phive_cancel_all_bookings_of_order'), 5, 1 );

		// Woo Order delete directly
		add_action( 'wp_trash_post', array($this, 'phive_cancel_all_bookings_of_order_on_trash'), 5, 1 );
		add_action( 'woocommerce_trash_order', array($this, 'phive_cancel_all_bookings_of_order_on_trash'), 5, 1 );
		add_action( 'woocommerce_delete_order', array($this, 'phive_cancel_all_bookings_of_order_on_trash'), 5, 1 );

		if( isset($_GET['phive_cancel_lineitem'])){

			$this->phive_cancel_lineitem();
		}

		// 41940 if order item is cancelled also then no need to modify order total and subtotal
		// add_filter('woocommerce_get_formatted_order_total',array($this,'ph_booking_remove_cancel_booking_cost_in_order_total'),10,2);
		// add_filter('woocommerce_order_subtotal_to_display',array($this,'ph_booking_remove_cancel_booking_cost_in_order_total_my_account_page'),10,3);
		// add_filter('woocommerce_order_get_total',array($this,'ph_booking_calculate_order_total_for_repay'),10,2);

		add_filter('wc_order_is_editable', array($this, 'ph_always_show_edit_icons_to_admin'), 10, 2);
		add_action( 'woocommerce_saved_order_items', array( $this, 'ph_change_from_and_to_from_order_edit' ), 10, 2 );
		
		add_action( 'woocommerce_before_save_order_items', array( $this, 'ph_change_from_and_to_from_order_edit_before' ), 10, 2 );

		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'ph_booking_modify_booking_hidden_fields' ), 10, 3 );

		add_action( 'ph_booking_status_changed', array( $this, 'ph_reset_asset_cache' ), 10, 4 );

		// 150878 - Issue : Not showing the Booked From and Booked To times as per wp timezone under order edit page.
		add_filter('woocommerce_order_item_display_meta_value', array($this, 'woocommerce_order_item_display_default_format_dates'), 10, 3);

		add_action('woocommerce_update_order', array($this, 'ph_woocommerce_update_order'), 10, 2);

		$this->older_booking_values = array();
		$this->older_order_address	= [];
	}

	public function ph_reset_asset_cache($status, $item_id, $order_id, $order='' )
	{
		if (!empty($item_id)) 
		{
			$asset_id = wc_get_order_item_meta( $item_id, 'Assets', 1);
			$asset_id = !empty($asset_id) ? maybe_unserialize($asset_id) : '';
			if (!empty($asset_id) && is_array($asset_id)) 
			{
				$asset_id = $asset_id[0];
				$ph_cache_obj = new phive_booking_cache_manager();
				$ph_cache_obj->ph_unset_cache($asset_id);
			}
		}
	}

	public function ph_booking_modify_booking_hidden_fields($item_id='', $item='', $product = '')
	{
		// error_log('action');
		// error_log($_REQUEST['action']);
		// if (isset($_REQUEST['action']) && $_REQUEST['action'] != 'woocommerce_save_order_items') 
		// {
		// 	// error_log('return');
		// 	return;
		// }
		$language_ignore_array= array('en_US','en_UK','en_CA');
		$current_lang=get_locale();

		if ( (!empty($current_lang) && !in_array($current_lang, $language_ignore_array )) && $meta_data = $item->get_formatted_meta_data( '' ) ) : 
				foreach ( $meta_data as $meta_id => $meta ) :
					if($meta->key==__($this->booked_from_text, 'bookings-and-appointments-for-woocommerce'))
					{
						$value= $item->get_meta('From');
						$value = isset($value[0])?$value[0]:$value;
					?>
						<input type="hidden" class="modify_booking_from_meta_id" data-item-id="<?php echo $item_id;?>" data-meta-id="<?php echo $meta_id?>" value="<?php echo $value;?>"/>
					<?php
					}
					if($meta->key==__($this->booked_to_text, 'bookings-and-appointments-for-woocommerce'))
					{

						$from= $item->get_meta('From');
						$from = isset($from[0])?$from[0]:$from;

						$value= $item->get_meta('To');
						$value = isset($value[0])?$value[0]:$from;
						$interval_details = $item->get_meta('_phive_booking_product_interval_details');
						$interval_format = $interval_details['interval_format'] ?? '';

						// For time Interval booked to date is not showing currect date
						if ($interval_format == 'hour' || $interval_format == 'minute') 
						{
							$interval = (int) $interval_details['interval'];
							$value = date('Y-m-d H:i', strtotime("+$interval $interval_format", strtotime($value)));
						}
					?>
						<input type="hidden" class="modify_booking_to_meta_id" data-item-id="<?php echo $item_id;?>" data-meta-id="<?php echo $meta_id?>" value="<?php echo $value;?>"/>
					<?php
					}
			endforeach; 
		endif;
	}

	public function ph_booking_calculate_order_total_for_repay($total='',$order='')
	{
		$subtotal=$total;
		if((isset($_GET['pay_for_order']) && $_GET['pay_for_order']) )
			{
				$tax_display =get_option( 'woocommerce_tax_display_cart' );
				$price=0;
				$tax=0;
				if($order instanceof WC_Order)
				{
					// $subtotal=$order->get_subtotal();
					$items = $order->get_items();
					foreach ($items as $order_item_id => $line_item) {
						$product = $line_item->get_product();
						// Trigger only for Bookings Products
						if( is_a( $product, 'WC_Product_phive_booking' ) ) {
							$canceled = $line_item->get_meta('canceled');
							if( $canceled == 'yes' ){
								$price+=$line_item->get_meta('Cost')[0];
							}
							if ( 'incl' === $tax_display ) {
								// $tax += $line_item->get_subtotal_tax();
							}
						}
					}
					return $subtotal+$tax-$price;
				}
			}
			return $subtotal;

	}

	public function ph_booking_remove_cancel_booking_cost_in_order_total($price_html,$order)
	{
		if(is_account_page() || (isset($_GET['pay_for_order']) && $_GET['pay_for_order']) )
		{
			$tax_display =get_option( 'woocommerce_tax_display_cart' );
			$price=0;
			$tax=0;
			if($order instanceof WC_Order)
			{
				$subtotal=$order->get_total();
				$items 						= $order->get_items();
				foreach ($items as $order_item_id => $line_item) {
					$product = $line_item->get_product();
					// Trigger only for Bookings Products
					if( is_a( $product, 'WC_Product_phive_booking' ) ) {
						$canceled = $line_item->get_meta('canceled');
						if( $canceled == 'yes' )
						{
							if (isset($line_item->get_meta('Cost')[0]) && is_numeric($line_item->get_meta('Cost')[0])) 
							{
								$price+=$line_item->get_meta('Cost')[0];
							}
							else
							{
								$price+=0;
							}
						}
						if ( 'incl' === $tax_display ) {
							// $tax += $line_item->get_subtotal_tax();
						}
					}
				}
				return wc_price($subtotal+$tax-$price);
			}
		}
		return $price_html;
	}

	public function ph_booking_remove_cancel_booking_cost_in_order_total_my_account_page($subtotal,$compound,$order)
	{

		if(is_account_page() || (isset($_GET['pay_for_order']) && $_GET['pay_for_order']))
		{
			$tax_display =get_option( 'woocommerce_tax_display_cart' );
			$price=0;
			$tax=0;
			if($order instanceof WC_Order)
			{
				$subtotal=$order->get_subtotal();
				$items 						= $order->get_items();
				foreach ($items as $order_item_id => $line_item) {
					$product = $line_item->get_product();
					// Trigger only for Bookings Products
					if( is_a( $product, 'WC_Product_phive_booking' ) ) {
						$canceled = $line_item->get_meta('canceled');
						if( $canceled == 'yes' ){
							if (isset($line_item->get_meta('Cost')[0]) && is_numeric($line_item->get_meta('Cost')[0])) 
							{
								$price+=$line_item->get_meta('Cost')[0];
							}
							else
							{
								$price+=0;
							}
						}
						if ( 'incl' === $tax_display ) {
							$tax += $line_item->get_subtotal_tax();
						}
					}
				}
				return wc_price($subtotal+$tax-$price);
			}
		}
		return $subtotal;
	}

	public function phive_cancel_all_bookings_of_order_on_trash($post_id)
	{
		$order = wc_get_order($post_id);
		if( $order instanceof WC_Order ) {
			// 143612 - Issue: When moving woocommerce order to trash (order status is completed), bookings in the order getting cancelled and customers are getting cancelled emails.
			$order_status = $order->get_status();
			if($order_status != 'completed')
			{
				$this->phive_cancel_all_bookings_of_order($post_id);
			}
		}
	}

	public function phive_cancel_all_bookings_of_order( $order_id ){
		$order 				= new WC_Order($order_id);
		$items 						= $order->get_items();
		foreach ($items as $order_item_id => $line_item) {
			$product = $line_item->get_product();
			// Trigger only for Bookings Products
			if( is_a( $product, 'WC_Product_phive_booking' ) ) {
				$canceled = $line_item->get_meta('canceled');
				if( $canceled != 'yes' ){
					$this->phive_cancel_lineitem( $order_item_id, $order_id );
				}
			}
		}
	}

	public function phive_hide_order_itemmeta($hidden_metas)
	{
		// When manually adding Product in order $_GET is empty 
		if ( !isset($_GET['id']) && !isset($_GET['post']) ) {

			return $hidden_metas;
		}

		$hidden_metas[] = 'booking_status';

		$order_id = PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled() ? $_GET['id'] : $_GET['post'];

		// No need to show the Number of persons meta to user
		$order = !empty($order_id) ? wc_get_order($order_id): new stdClass();

		if (!$order instanceof WC_Order) {
			return  $hidden_metas;
		}
		$order_items	=	$order->get_items();

		foreach ($order_items as $item_id => $item) {

			if (!empty(wc_get_order_item_meta($item_id, __('Total Number of Participants', 'bookings-and-appointments-for-woocommerce'), 1))) {

				$hidden_metas[] = 'Number of persons';
				return $hidden_metas;
			}
		}
		return $hidden_metas;
	}

	/**
	 * Need to remove after few version Added in - 1.1.6, It's getting handled
	 * 
	 * @param int $item_id
	 * @param object $item
	 * @param object $product
	 */
	public function phive_after_order_itemmeta_contents($item_id, $item, $product)
	{
		// Return in Case of Shipping Line Item and others, empty in case of deleted products
		if (!($item instanceof WC_Order_Item_Product) || empty($product))	return;

		$product_type = $product->get_type();

		// Return if Product is not a booking Product.
		if ($product_type != 'phive_booking')	return;

		$booking_status = $item->get_meta(__('Booking Status', 'bookings-and-appointments-for-woocommerce'));

		if (!empty($booking_status))	return;

		$booking_status = ph_maybe_unserialize($item->get_meta('booking_status'));
	}

	/**
	 * Add the class for cancelled Booking Line Items.
	 * @param string $class
	 * @param object $item WC_Order_Item_Product
	 * @param object $order WC_Order.
	 * @return string
	 */
	public function phive_return_product_css_class( $class, $item, $order='' ){
		$product	= $item->get_product();
		if( is_a( $product, 'WC_Product_phive_booking' ) ) {
			$canceled	= $item->get_meta('canceled');
			return $canceled == "yes" ? $class.' retuned' : $class;
		}
		return $class;
	}

	public function phive_disply_cancel_buttons($html, $item, $args){
		if( strpos($html, 'canceled:') ){
			$html = str_replace('canceled:', '<span style="color:red">'.__('The order is canceled','bookings-and-appointments-for-woocommerce').'</span>', $html);
			$html = str_replace('yes', '', $html);
		}
		return $html;
	}

	/**
	 * Cancel the booking item
	 * @param int $line_item_id (order_item_id)
	 * @param int $order_id
	 */
	public function phive_cancel_lineitem( $line_item_id='', $order_id='' ){
		$order_id 			= isset($_GET['phive_cancel_order_id']) ? $_GET['phive_cancel_order_id'] : $order_id;
		$order 				= new WC_Order($order_id);
		if (empty($order)) {
			return;
		}
		$item_id 					= isset($_GET['phive_cancel_lineitem']) ? $_GET['phive_cancel_lineitem'] : $line_item_id;
		$buffer_before_id 			= wc_get_order_item_meta( $item_id, "buffer_before_id", 1 );
		$buffer_before_id			= isset($buffer_before_id[0])?$buffer_before_id[0]:'';
		$buffer_after_id 			= wc_get_order_item_meta( $item_id, "buffer_after_id", 1 );
		$buffer_after_id			= isset($buffer_after_id[0])?$buffer_after_id[0]:'';
		update_post_meta( $buffer_before_id, 'ph_canceled', '1' );
		update_post_meta( $buffer_after_id, 'ph_canceled', '1' );
		
		global $wpdb;
		$wpdb->insert( $wpdb->prefix."woocommerce_order_itemmeta", array( 'meta_key'=>'canceled', 'meta_value'=>'yes', 'order_item_id'=>$item_id,  ) );
		$status_chage = wc_update_order_item_meta( $item_id, 'booking_status', array('canceled') );
		// wc_update_order_item_meta( $item_id, 'Booking Status', 'cancelled' );

		// 103410 - Switching to product language
		$current_lang = ph_wpml_language_switch_admin_email($order, '', 'order', '');
		wc_update_order_item_meta( $item_id, __('Booking Status','bookings-and-appointments-for-woocommerce'), __('cancelled','bookings-and-appointments-for-woocommerce') );

		// 103410 - Switching back to current language
		ph_wpml_language_switch_admin_email('', '', 'current', $current_lang);

		if( $this->is_all_order_item_cancelled($order) ){
			$order->update_status( 'wc-cancelled' );
		}

		// Executed only when Zoom plugin is installed and a zoom meeting exists.
		do_action( 'ph_zoom_update_meeting_status_as_deleted', $item_id );
		
		do_action( 'ph_booking_status_changed', 'cancelled', $item_id, $order_id, $order  );

		// 113447 - woocommerce_cancel_unpaid_orders cron was not working because of this redirection.
		// 195063 - Payment cancel is not cancelling all booking in the order because of this redirection
		if (!is_admin() && !wp_doing_cron() && isset($_GET['phive_cancel_lineitem'])) {

			$my_account	=	wc_get_account_endpoint_url('view-order');
			wp_redirect("$my_account/$order_id/");
			exit;
		}
	}

	private function is_all_order_item_cancelled( $order ){
		if( empty($order) ){
			return false;
		}
		
		$items 						= $order->get_items();
		foreach ($items as $order_item_id => $line_item) {
			$canceled = $line_item->get_meta('canceled');
			if( $canceled != 'yes' ){
				return false;
			}
		}
		return true;
	}
	
	public function phive_disply_cancel_button($line_item_id, $line_item, $order){
		
		$zone	= get_option('timezone_string');
		if(empty($zone)){
			$time_offset = get_option('gmt_offset');
			$zone = timezone_name_from_abbr( "", $time_offset*60*60, 0 );
		}
		// date_default_timezone_set($zone);
		global $wp_version;
		if ( version_compare( $wp_version, '5.3', '>=' ) ) 
		{
			$timezone = wp_timezone();
		}
		else
		{
			$timezone = new DateTimeZone($zone);
		}
		if( is_order_received_page() || is_view_order_page() )
		{
			$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id($line_item->get_product_id() );		//WPML compatibilty
			$product = wc_get_product( $product_id );
			if ($product) 
			{
				// 168526 - giving fatal error for woocommerce subscription product order
				if($product->get_type() != 'phive_booking'){
					return;
				}
				
				$cancelable	= get_post_meta( $product->get_id(), '_phive_book_allow_cancel', 1);
				if( $cancelable != 'yes' )
				{
					return;
				}

				$ph_cancel_interval			= get_post_meta( $product->get_id(), '_phive_cancel_interval', 1);
				$ph_cancel_interval_period	= get_post_meta( $product->get_id(), '_phive_cancel_interval_period', 1);
				/* $ph_checkin					= get_post_meta( $product->get_id(), '_phive_book_checkin', 1);*/
				
				$from = ph_maybe_unserialize( $line_item->get_meta('From') );
				$interval_period = $product->get_interval_period();
				switch($interval_period)
				{
					case 'minute':
							$modified_from=$from.':00';
							break;
					case 'hour':
							$modified_from=$from.':00';
							break;
					case 'day':
							/*if(!empty($ph_checkin)){
								$to=$ph_checkin.':00';
							}
							else{
								*/
								$to='00:00:00';
							// }
							$newtimestamp = strtotime($from.' '.$to);
							$modified_from=date('Y-m-d H:i:s', $newtimestamp);
							break;
					case 'month':
							$to='00:00:00';
							$newtimestamp = strtotime($from.' '.$to);
							$modified_from=date('Y-m-d H:i:s', $newtimestamp);
							break;
				
				}
			
				switch($ph_cancel_interval_period)
				{
					case 'minute':
						$ph_cancel_interval = $ph_cancel_interval*60;
						break;
					case 'hour':
						$ph_cancel_interval = $ph_cancel_interval*3600;
						break;
					case 'day':
						$ph_cancel_interval = $ph_cancel_interval*3600*24;
						break;
				}
				$current_date = new DateTime();
				$current_date->setTimeZone($timezone);
				$current_date = $current_date->format('Y-m-d H:i:s');
				
				$diff 		= ( strtotime( $modified_from ) - strtotime($current_date));
				$canceled 	= $line_item->get_meta('canceled');
				if( $product->get_type() == 'phive_booking' )
				{
					$booking_status = $line_item->get_meta( 'booking_status' );

					if( ($canceled != 'yes' && ( is_array($booking_status) && 'refunded' != current($booking_status))) && $diff > ($ph_cancel_interval)):

						$cancel_confirmation = __("Are you sure to cancel booking?",'bookings-and-appointments-for-woocommerce');
						?>
						<form method="get" action="#">
							<input type="hidden" name="phive_cancel_lineitem" value="<?php echo $line_item_id?>">
							<input type="hidden" name="phive_cancel_order_id" value="<?php echo $order->get_id()?>">
							<div><input type="submit" value="<?php echo __('Cancel','bookings-and-appointments-for-woocommerce');?>" onclick = "if (! confirm(`<?php echo $cancel_confirmation;?>`)) { return false; }" ></div>
						</form><?php
					else:?>
						<!-- <span style="color:red">The order is canceled</span>-->
						<?php 
					endif;
				
				}
			}
		}
	}

    public function ph_always_show_edit_icons_to_admin($status, $order_object='')
    {
		if($order_object instanceof WC_Order)
		{
	        $meta_datas = $order_object->get_items();
	        // empty or not
	        if (!empty($meta_datas)) 
	        {
	            foreach( $meta_datas as $meta_data ) 
	            {
					$product = $meta_data->get_product();
					if ($product) 
					{
						$product_type = $product->get_type();
						if ($product_type == 'phive_booking') 
						{
							$status = true;
							// return $status;
							break;
						}
					}
	            }
	        }
	    }
        return $status;
    }

	public function ph_change_from_and_to_from_order_edit_before($order_id, $items)
	{

		// When all items deleted order_item_id not present in items
		if(!isset($items['order_item_id'])){
			return;
		}

		$order = wc_get_order($order_id);
		$this->older_order_address = $order->get_address();
		$booked_from_text = __($this->booked_from_text, 'bookings-and-appointments-for-woocommerce');
		$booked_to_text = __($this->booked_to_text, 'bookings-and-appointments-for-woocommerce');

		foreach ( $items['order_item_id'] as $item_id ) 
        {
			$item = WC_Order_Factory::get_order_item( absint( $item_id ) );
			
			$this->older_booking_values[$item_id] = array(
				$booked_from_text => wc_get_order_item_meta( $item_id, $booked_from_text, 1),
				$booked_to_text => wc_get_order_item_meta( $item_id, $booked_to_text, 1),
				'item_id'	=> $item_id
			);
			// #116815
			$this->older_participant_values[$item_id] = array(
				'Number of persons' => wc_get_order_item_meta( $item_id, 'Number of persons', 1),
				'item_id'	=> $item_id
			);
		}
	}

    public function ph_change_from_and_to_from_order_edit($order_id, $items)
    {
		// When all items deleted order_item_id not present in items
		if(!isset($items['order_item_id'])){
			return;
		}
		$booked_from_text = __($this->booked_from_text,'bookings-and-appointments-for-woocommerce');
		$booked_to_text = __($this->booked_to_text,'bookings-and-appointments-for-woocommerce');
		
		$language_ignore_array= array('en_US','en_UK','en_Ca');
		$current_lang=get_locale();

        $meta_value_from = ''; 
        $meta_value_to = '';
        $item_id_to_modify = '';
        $edit_items = array();
		$participant_modified = [];
        foreach ( $items['order_item_id'] as $item_id ) 
        {
			// Need to update changes of Total Number of participants to Number of persons
			$total_number_participants =	wc_get_order_item_meta($item_id, __('Total Number of Participants', 'bookings-and-appointments-for-woocommerce'), 1);
			if(!empty($total_number_participants)){
				wc_update_order_item_meta($item_id, "Number of persons",$total_number_participants );
			}
			$item = WC_Order_Factory::get_order_item(absint($item_id));

			$modified_booking_values = array(
				$booked_from_text => wc_get_order_item_meta( $item_id, $booked_from_text, 1),
				$booked_to_text => wc_get_order_item_meta( $item_id, $booked_to_text, 1),
				'item_id'	=> $item_id
			);

			$modified_participant_values = array(
				'Number of persons' => wc_get_order_item_meta( $item_id, 'Number of persons', 1),
				'item_id'	=> $item_id
			);
			
			$check_date_format = substr(trim($modified_booking_values[$booked_from_text]),0,5);

			if ( !empty($current_lang) && !in_array($current_lang, $language_ignore_array ) 
			&& ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'woocommerce_save_order_items') || preg_match("/^[0-9]{4}-$/",$check_date_format))) 
			{
				$modified_booking_values = array(
					$booked_from_text => $this->ph_calc_from_and_to_for_order_edit( $item_id, $modified_booking_values[$booked_from_text], $modified_booking_values[$booked_to_text], 'from'),
					$booked_to_text => $this->ph_calc_from_and_to_for_order_edit( $item_id, $modified_booking_values[$booked_from_text], $modified_booking_values[$booked_to_text], 'to'),
					'item_id'	=> $item_id
				);
			}

			$participant_modified[$item_id] = 1;
			$bookings_modified[$item_id] = 1;
			if (isset($this->older_participant_values[$item_id]) && $modified_participant_values == $this->older_participant_values[$item_id])
			{
				$participant_modified[$item_id] = 0;
			}

			if (isset($this->older_booking_values[$item_id]) && $modified_booking_values == $this->older_booking_values[$item_id]) 
			{
				$bookings_modified[$item_id] = 0;
				if($participant_modified[$item_id] == 0) 
				{
					continue;
				}
			}
			
			$asset_id = wc_get_order_item_meta( $item_id, 'Assets', 1);
			$asset_id = !empty($asset_id) ? maybe_unserialize($asset_id) : '';
			if (!empty($asset_id) && is_array($asset_id)) 
			{
				$asset_id = $asset_id[0];
				$ph_cache_obj = new phive_booking_cache_manager();
				$ph_cache_obj->ph_unset_cache($asset_id);
				// error_log("asset id : ".$asset_id);
				// error_log("cache set : ". $ph_cache_obj->ph_is_cache_set($asset_id));
			}

            if ( isset( $items['meta_key'][ $item_id ], $items['meta_value'][ $item_id ] ) ) 
            {
                foreach ( $items['meta_key'][ $item_id ] as $meta_id => $meta_key ) 
                {
                    $meta_key   = substr( wp_unslash( $meta_key ), 0, 255 );
                    if ($bookings_modified[$item_id] && ($meta_key == $booked_from_text || $meta_key == $booked_to_text) ) 
                    {
                        $meta_value = isset( $items['meta_value'][ $item_id ][ $meta_id ] ) ? wp_unslash( $items['meta_value'][ $item_id ][ $meta_id ] ) : '';

                        if ($meta_key == $booked_from_text ) 
                        {
                            $meta_value_from = $meta_value;
                            $item_id_to_modify = $item_id; 
                            $edit_items[$item_id_to_modify][$meta_key] = $meta_value_from;
                        }
                        else if ($meta_key == $booked_to_text ) 
                        {
                            $meta_value_to = $meta_value; 
                            $item_id_to_modify = $item_id; 
                            $edit_items[$item_id_to_modify][$meta_key] = $meta_value_to;
                        }
                    } 

					if($participant_modified[$item_id] && $meta_key == 'Total Number of Participants')
					{
						$meta_value_participant = isset( $items['meta_value'][ $item_id ][ $meta_id ] ) ? wp_unslash( $items['meta_value'][ $item_id ][ $meta_id ] ) : '';
						$item_id_to_modify = $item_id; 
						$edit_items[$item_id_to_modify][$meta_key] = $meta_value_participant;
					}
                }
            }
        }

        if (!empty($edit_items)) 
        {
            foreach ($edit_items as $item_id_to_modify => $edit_key_values) 
            {

                $meta_value_from = '';
                $meta_value_to = '';
                $interval = '';
                $interval_format = '';
				$number_of_persons = '';
                if(isset($edit_key_values[$booked_from_text])) 
                {
					$meta_value_from = $edit_key_values[$booked_from_text];
                }
                if (isset($edit_key_values[$booked_to_text]))
                {
                    $meta_value_to = $edit_key_values[$booked_to_text];
                }
				if (isset($edit_key_values['Number of persons']))
                {
                    $number_of_persons = $edit_key_values['Number of persons'];
                }
                $item = WC_Order_Factory::get_order_item( absint( $item_id_to_modify ) );

				if ( !($item instanceof WC_Order_Item_Product) ) {

					continue;
				}

                $meta_datas = $item->get_meta_data();
                // booking_status

				$keys = array();

                foreach( $meta_datas as $meta_data ) 
                {
					$meta_data = $meta_data->get_data();
					$keys[] = $meta_data['key'];
				}

                foreach( $meta_datas as $meta_data ) 
                {
                    $meta_data = $meta_data->get_data();
                    if ($meta_data['key'] == '_phive_booking_product_interval_details') 
                    {
                        $interval = $meta_data['value']['interval'];
                        $interval_format = $meta_data['value']['interval_format'];
                    }
                    if ($meta_data['key'] == 'buffer_before_id' && $meta_data['value'] != '') 
                    {
                        $buffer_before_id = maybe_unserialize($meta_data['value']);
                        $buffer_before_id = $buffer_before_id[0];

                        // $buffer_before_from = get_post_meta($buffer_before_id, 'Buffer_before_From', 1);
                        // $buffer_before_to = get_post_meta($buffer_before_id, 'Buffer_before_To', 1);

						if(isset($bookings_modified[$item_id_to_modify]) && $bookings_modified[$item_id_to_modify])
						{
							$buffer_before_from = '';
							$buffer_before_to = '';

							$product_id = get_post_meta($buffer_before_id, '_product_id', 1);
							$enable_buffer = get_post_meta($product_id, '_phive_enable_buffer', 1);
							$buffer_before = get_post_meta($product_id, '_phive_buffer_before', 1);
							$buffer_after = get_post_meta($product_id, '_phive_buffer_after', 1);
							$buffer_period = get_post_meta($product_id, '_phive_buffer_period', 1);

							$interval = ($interval != '') ? $interval : get_post_meta($product_id, '_phive_book_interval', 1);
							$interval_format = ($interval_format != '') ? $interval_format : get_post_meta($product_id, '_phive_book_interval_period', 1);

							$value_date_format = get_option( 'date_format' );

							$from = $meta_value_from;
							if($value_date_format == 'd/m/Y')
							{
								$from = strtotime(str_replace('/', '-', $meta_value_from));
							}
							else {
								$from = strtotime($meta_value_from);
							}

							switch ($interval_format) 
							{
								case 'day':
										$buffer_before_from		= date ( "Y-m-d", strtotime( "-$buffer_before $buffer_period", $from ) );
										$buffer_before_to 		= date ( "Y-m-d", strtotime( "-1 day", $from ) );
										break;
								case 'hour':
								case 'minute':
										$buffer_before_from		= date ( "Y-m-d H:i", strtotime( "-$buffer_before $buffer_period", $from ) );
										$buffer_before_to 		= date ( "Y-m-d H:i", strtotime( "-$interval $interval_format", $from ) );
										break;
							}

							if ($buffer_before_from != '') 
							{
								update_post_meta($buffer_before_id, 'Buffer_before_From', $buffer_before_from);                                
							}
							if ($buffer_before_to != '') 
							{
								update_post_meta($buffer_before_id, 'Buffer_before_To', $buffer_before_to);
							}
						}
						if(isset($participant_modified[$item_id_to_modify]) && $participant_modified[$item_id_to_modify])
						{
							update_post_meta($buffer_before_id, 'Number of persons', $number_of_persons);
						}
					}
                    if ($meta_data['key'] == 'buffer_after_id' && $meta_data['value'] != '') 
                    {
                        $buffer_after_id = maybe_unserialize($meta_data['value']);
                        $buffer_after_id = $buffer_after_id[0];
                       
                        // $buffer_after_from = get_post_meta($buffer_after_id, 'Buffer_after_From', 1);
                        // $buffer_after_to = get_post_meta($buffer_after_id, 'Buffer_after_To', 1);
						if(isset($bookings_modified[$item_id_to_modify]) && $bookings_modified[$item_id_to_modify])
						{
							$buffer_after_from = '';
							$buffer_after_to = '';

							$product_id = get_post_meta($buffer_after_id, '_product_id', 1);
							$enable_buffer = get_post_meta($product_id, '_phive_enable_buffer', 1);
							$buffer_before = get_post_meta($product_id, '_phive_buffer_before', 1);
							$buffer_after = get_post_meta($product_id, '_phive_buffer_after', 1);
							$buffer_period = get_post_meta($product_id, '_phive_buffer_period', 1);
							
							$value_date_format = get_option( 'date_format' );
							$to = $meta_value_to;
							if($value_date_format == 'd/m/Y')
							{
								if ($meta_value_to != '') 
								{
									$to = strtotime(str_replace('/', '-', $meta_value_to));
								}
								else 
								{
									$from = strtotime(str_replace('/', '-', $meta_value_from));
								}
							}
							else {
								$to = strtotime($meta_value_to);
								$from = strtotime($meta_value_from);
							}
							switch ($interval_format) 
							{
								case 'day':
										if ($to != '') 
										{
											$buffer_after_from 	= date ( "Y-m-d", strtotime( "+1 day", $to ) );
											$buffer_after_to 		= date ( "Y-m-d", strtotime( "+ $buffer_after $buffer_period", $to ) );
										}
										else 
										{
											$buffer_after_from 	= date ( "Y-m-d", strtotime( "+1 day", $from ) );
											$buffer_after_to 		= date ( "Y-m-d", strtotime( "+ $buffer_after $buffer_period", $from ) );
										}
										break;
								case 'hour':
								case 'minute':
										if ($to != '') 
										{
											$buffer_after_from		= date ( "Y-m-d H:i", $to );
											$to = date("Y-m-d H:i", strtotime("-$interval $interval_format", $to));
										}
										else 
										{
											$buffer_after_from		= date ( "Y-m-d H:i", strtotime( "+$interval $interval_format", $from ) );
											$to = date("Y-m-d H:i", $from);
										}

										// 116815 - When rescheduling a booking which has before and after booking buffer, after booking buffer is not applying to the new block/ timeslot.
										if($interval_format == 'minute')
										{
											$buffer_after_and_interval = $buffer_after + $interval;
											$buffer_after_to = date ( "Y-m-d H:i", strtotime( "+$buffer_after_and_interval $buffer_period", strtotime($to)));
										}
										else
										{
											$buffer_after_to = date ( "Y-m-d H:i", strtotime( "+$buffer_after $buffer_period", strtotime($to)));
										}
										break;
							}

							if ($buffer_after_from != '') 
							{
								update_post_meta($buffer_after_id, 'Buffer_after_From', $buffer_after_from);
							}
							if ($buffer_after_to != '') 
							{
								update_post_meta($buffer_after_id, 'Buffer_after_To', $buffer_after_to);
							}
						}
						if(isset($participant_modified[$item_id_to_modify]) && $participant_modified[$item_id_to_modify])
						{
							update_post_meta($buffer_after_id, 'Number of persons', $number_of_persons);
						}
                    }
                    if(isset($bookings_modified[$item_id_to_modify]) && $bookings_modified[$item_id_to_modify]) 
					{
                        // if( $meta_data['key'] != 'From' && $meta_data['key'] != 'To')	continue;
                        if ($meta_data['key'] == 'From' && $meta_value_from != '' && $interval_format != '') 
                        {
                            $value_date_format = get_option( 'date_format' );
                            if($value_date_format == 'd/m/Y')
                            {
                                $from = strtotime(str_replace('/', '-', $meta_value_from));
                            }
                            else {
                                $from = strtotime($meta_value_from);
                            }

                            if ($interval_format == 'hour' || $interval_format == 'minute') 
                            {
                                $from = date('Y-m-d H:i', $from);
                            }
                            else if ($interval_format == 'day') 
                            {
                                $from = date('Y-m-d', $from);
                            }
                            elseif ($interval_format == 'month') {
                                $from = date('Y-m', $from);
							}

							if (!in_array('To', $keys) && $meta_value_to != '' && $interval != '' && $interval_format != '') 
							{
								$value_date_format = get_option( 'date_format' );
								if($value_date_format == 'd/m/Y')
								{
									$to = strtotime(str_replace('/', '-', $meta_value_to));
								}
								else {
									$to = strtotime($meta_value_to);
								}

								if ($interval_format == 'hour' || $interval_format == 'minute') 
								{
									// $to = strtotime(date('Y-m-d H:i', $to));
									$interval = (int) $interval;
									$to = date('Y-m-d H:i', strtotime("-$interval $interval_format", $to));
								}
								else if ($interval_format == 'day') 
								{
									$to = date('Y-m-d', $to);
								}
								elseif ($interval_format == 'month') 
								{
									$to = date('Y-m', $to);
								}
								if($from != $to)
								{
									$to = (array) $to;
									$item->add_meta_data('To', $to, $meta_data['id']);
								}
							}

                            $from = (array) $from;
                            $item->update_meta_data($meta_data['key'], $from, $meta_data['id']);
                        }
                        if ($meta_data['key'] == 'To' && $meta_value_to != '' && $interval != '' && $interval_format != '') 
                        {
                            $value_date_format = get_option( 'date_format' );
                            if($value_date_format == 'd/m/Y')
                            {
                                $to = strtotime(str_replace('/', '-', $meta_value_to));
                            }
                            else {
                                $to = strtotime($meta_value_to);
                            }


                            if ($interval_format == 'hour' || $interval_format == 'minute') 
                            {
                                // $to = strtotime(date('Y-m-d H:i', $to));
                                $interval = (int) $interval;
                                $to = date('Y-m-d H:i', strtotime("-$interval $interval_format", $to));
                            }
                            else if ($interval_format == 'day') 
                            {
                                $to = date('Y-m-d', $to);
                            }
                            elseif ($interval_format == 'month') 
                            {
                                $to = date('Y-m', $to);
                            }
                            $to = (array) $to;

                            $item->update_meta_data($meta_data['key'], $to, $meta_data['id']);
                        }
                        if ($meta_data['key'] == $booked_from_text && $meta_value_from != '' && $interval != '' && $interval_format != '' && (!empty($current_lang) && !in_array($current_lang, $language_ignore_array )) ) 
                        {
                        	$from = strtotime($meta_value_from);
                        	if ($interval_format == 'hour' || $interval_format == 'minute') 
                            {
                                $from = date('Y-m-d H:i', $from);
                            }
                            else if ($interval_format == 'day') 
                            {
                                $from = date('Y-m-d', $from);
                            }
                            elseif ($interval_format == 'month') 
                            {
                                $from = date('Y-m', $from);
                            }
                            $from= Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format($from);
                            $item->update_meta_data($booked_from_text, $from, $meta_data['id']);
                        }

                        if ($meta_data['key'] == $booked_to_text && $meta_value_to != '' && $interval != '' && $interval_format != '' && (!empty($current_lang) && !in_array($current_lang, $language_ignore_array )) ) 
                        {
                        	$to=strtotime($meta_value_to);
                        	 if ($interval_format == 'hour' || $interval_format == 'minute') 
                            {
                                $to = date('Y-m-d H:i', $to);
                            }
                            else if ($interval_format == 'day') 
                            {
                                $to = date('Y-m-d', $to);
                            }
                            elseif ($interval_format == 'month') 
                            {
                                $to = date('Y-m', $to);
                            }
                            $to= Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format($to);
                            $item->update_meta_data($booked_to_text, $to, $meta_data['id']);
                        }
                    }
                }

                $item->save();
				do_action('ph_booking_item_calender_resynced', 're-sync-google-calender', $item_id_to_modify, $order_id);
            }
			// 96421 
			do_action('ph_booking_order_items_modified', $edit_items, $order_id, $_REQUEST);
        }
	}
	
	public function ph_calc_from_and_to_for_order_edit($item_id, $from, $to, $return_date)
	{
		$item = WC_Order_Factory::get_order_item( absint( $item_id ) );
		//176792 not able save in edit order page when fee item exist
		if( ! $item instanceof WC_Order_Factory){
			return;
		}
		$product_id = $item->get_product_id();
		$meta_datas = $item->get_meta_data();
		$interval = '';
		$interval_format = '';
		foreach( $meta_datas as $meta_data ) 
		{
			$meta_data = $meta_data->get_data();
			if ($meta_data['key'] == '_phive_booking_product_interval_details') 
			{
				$interval = $meta_data['value']['interval'];
				$interval_format = $meta_data['value']['interval_format'];
			}
		}

		$interval = ($interval != '') ? $interval : get_post_meta($product_id, '_phive_book_interval', 1);
		$interval_format = ($interval_format != '') ? $interval_format : get_post_meta($product_id, '_phive_book_interval_period', 1);

		if($return_date == 'from')
		{
			// calc from date
			$from = Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format($from);
			wc_update_order_item_meta( $item_id, __($this->booked_from_text,'bookings-and-appointments-for-woocommerce'), $from);
			return $from;
		}
		else if($return_date == 'to' && !empty($to))
		{
			// calc to date
			$to=strtotime($to);
			if ($interval_format == 'hour' || $interval_format == 'minute') 
			{
				$interval = (int) $interval;
				$to = date('Y-m-d H:i', strtotime("+$interval $interval_format", $to));
			}
			else if ($interval_format == 'day') 
			{
				$to = date('Y-m-d', $to);
			}
			elseif ($interval_format == 'month') 
			{
				$to = date('Y-m', $to);
			}
			$to= Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format($to);
			wc_update_order_item_meta( $item_id, __($this->booked_to_text,'bookings-and-appointments-for-woocommerce'), $to);
			return $to;
		}		
	}

	public function woocommerce_order_item_display_default_format_dates($display_value='', $meta='', $order_item='')
	{
		if($display_value && is_object($meta) && is_object($order_item) && is_admin())
		{
			$display_settings		= get_option('ph_bookings_display_settigns');
			$time_zone_conversion 	= isset($display_settings['time_zone_conversion_enable']) ? $display_settings['time_zone_conversion_enable'] : 'no';

			if($time_zone_conversion != 'yes')
			{
				return $display_value;
			}

			$sitepress_active_check = class_exists('SitePress');
			$current_language = '';
			if($sitepress_active_check)
			{
				$order = $order_item->get_order();
			
				// WPML Support - Switch to order language
				$current_language 	= ph_wpml_language_switch_admin_email($order, '', $lang_basis='order');
				$wpml_lang 			= $order->get_meta('wpml_language');

				$booked_from_key 	= __($this->booked_from_text,'bookings-and-appointments-for-woocommerce');
				$booked_to_key 		= __($this->booked_to_text,'bookings-and-appointments-for-woocommerce');

				// WPML Support - Switch back to current langauge
				ph_wpml_language_switch_admin_email($order, '', $lang_basis='current', $current_language);
			}
			else
			{
				$booked_from_key 	= __($this->booked_from_text,'bookings-and-appointments-for-woocommerce');
				$booked_to_key 		= __($this->booked_to_text,'bookings-and-appointments-for-woocommerce');
			}

			if(isset($meta->key) && $meta->key == $booked_from_key)
			{
				$display_value = Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format(ph_maybe_unserialize($order_item->get_meta('From')));
			}
			else if(isset($meta->key) && $meta->key == $booked_to_key)
			{
				$to = ph_maybe_unserialize($order_item->get_meta('To'));
				$interval_details	= maybe_unserialize($order_item->get_meta('_phive_booking_product_interval_details'));
				if(!empty($interval_details))
				{
					if(empty($to))
					{
						$to 				= ph_maybe_unserialize($order_item->get_meta('From'));
						$interval 			= $interval_details['interval'];
						$interval_format	= $interval_details['interval_format'];
						if($interval_format != 'day' && $interval_format != 'month' )
						{
							$to = date('Y-m-d H:i', strtotime("+$interval $interval_format", strtotime($to)));
						}
						elseif($interval > 1)
						{
							$to = date('Y-m-d', strtotime( "+$interval $interval_format", strtotime($to)));	
						}
					}
					else
					{
						$interval 			= $interval_details['interval'];
						$interval_format	= $interval_details['interval_format'];
						if($interval_format != 'day' && $interval_format != 'month' )
						{
							$to	= str_replace('/', '-', $to);
							$to = date('Y-m-d H:i', strtotime("+$interval $interval_format", strtotime($to))); // adding interval to last block
						}
					}
				}
				else
				{
					$from 	= ph_maybe_unserialize($order_item->get_meta('From'));
					$to		= !empty($to) ? $to : $from;
				}
				$display_value = Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format($to);
			}
		}
		return $display_value;
	}


	/**
	 * Check if the customer Details in the order modified and resync google calendar
	 * 
	 * @param int $order_id
	 * @param object $order
	 */
	public function ph_woocommerce_update_order($order_id, $order)
	{

		if ($order instanceof WC_Order && !empty(array_diff($this->older_order_address, $order->get_address()))) {

			$items	= $order->get_items();

			if (empty($items)) {
				return;
			}

			foreach ($items as $item_id => $item) {

				$product 				 = wc_get_product($item->get_product_id());

				if ($product instanceof WC_Product_phive_booking ) {

					do_action('ph_booking_item_calender_resynced', 're-sync-google-calender', $item_id, $order_id);
				}
			}
		}
	}

}
new phive_booking_order_manager;
