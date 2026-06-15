<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ph_Bookings_Calendar_Admin.
 */
class Ph_Bookings_Calendar_Admin {

	/**
	 * Stores Bookings.
	 *
	 * @var array
	 */
	private $bookings;

	// Assets Rules.
	public $assets_rules;

	/**
	 * WC Table
	 */
	private $woocommerce_table;

	/**
	 * Output the calendar view.
	 */
	public function output() {
		// wp_enqueue_script( 'wc-enhanced-select' );

		$product_filter = isset( $_REQUEST['filter_bookings'] ) ? absint( $_REQUEST['filter_bookings'] ) : '';
		$view           = isset( $_REQUEST['view'] ) && 'day' === $_REQUEST['view'] ? 'day' : 'month';
		$filter_bookings_by_status = isset( $_REQUEST['filter_bookings_by_status'] ) ? $_REQUEST['filter_bookings_by_status'] : '';

		$display_settings 	= get_option('ph_bookings_display_settigns');
		$ph_swap_month_year = isset($display_settings['ph_swap_month_year_in_calendar']) ? $display_settings['ph_swap_month_year_in_calendar'] : 'no';
		$year_format 		= apply_filters("ph_customize_year_format", 'Y');

		// Fetch Assets Rules.
		$asset_settings 			= get_option( 'ph_booking_settings_assets', 1 );
		$this->assets_rules 		= (isset($asset_settings['_phive_booking_assets']) && !empty($asset_settings['_phive_booking_assets'])) ? $asset_settings['_phive_booking_assets'] : array();

        // $view = 'month';
		if ( 'day' === $view ) {
			$day = isset( $_REQUEST['calendar_day'] ) ? wc_clean( $_REQUEST['calendar_day'] ) : date( 'Y-m-d' );
			$start_time = strtotime( 'midnight', strtotime( $day ) );
			$end_time = strtotime( 'midnight +1 day', strtotime( $day ) ) - 1;
			$this->bookings = $this->get_ph_bookings(date('Y-m-d', $start_time), date('Y-m-d', $end_time), $_REQUEST, $view='day');
		} else {
			$month = isset( $_REQUEST['calendar_month'] ) ? absint( $_REQUEST['calendar_month'] ) : date( 'n' );
			$year  = isset( $_REQUEST['calendar_year'] ) ? absint( $_REQUEST['calendar_year'] ) : date( 'Y' );

			if ( $year < ( date( 'Y' ) - 10 ) || $year > 2100 ) {
				$year = date( 'Y' );
			}

			if ( $month > 12 ) {
				$month = 1;
				$year ++;
			}

			if ( $month < 1 ) {
				$month = 12;
				$year --;
			}

			$start_of_week = absint( get_option( 'start_of_week', 1 ) );
			$last_day      = date( 't', strtotime( "$year-$month-01" ) );
			$start_date_w  = absint( date( 'w', strtotime( "$year-$month-01" ) ) );
			$end_date_w    = absint( date( 'w', strtotime( "$year-$month-$last_day" ) ) );

			// Calc day offset
			$day_offset = $start_date_w - $start_of_week;
			$day_offset = $day_offset >= 0 ? $day_offset : 7 - abs( $day_offset );

			// Cald end day offset
			$end_day_offset = 7 - ( $last_day % 7 ) - $day_offset;
			$end_day_offset = $end_day_offset >= 0 && $end_day_offset < 7 ? $end_day_offset : 7 - abs( $end_day_offset );

			// We want to get the last minute of the day, so we will go forward one day to midnight and subtract a min
			$end_day_offset = $end_day_offset + 1;

			$start_time = strtotime( "-{$day_offset} day", strtotime( "$year-$month-01" ) );
			$end_time   = strtotime( "+{$end_day_offset} day midnight", strtotime( "$year-$month-$last_day" ) );
			// error_log(print_r($_REQUEST,1));
            // error_log('start_time else : '.$start_time);
            // error_log('end_time else : '.$end_time);
			// Incorrect Date Issue
			//ticket 129031-not showing booking from this month to next month
			$this->bookings = $this->get_ph_bookings(date('Y-m-01',$start_time), date('Y-m-t',$end_time), $_REQUEST);

		}

		include( 'views/html-calendar-' . $view . '.php' );
	}

	/**
	 * List bookings for a day.
	 */
	public function list_bookings( $day, $month, $year ) {
		$date_start = strtotime( "$year-$month-$day midnight" ); // Midnight today.
		$date_end   = strtotime( "$year-$month-$day tomorrow" ); // Midnight next day.

		foreach ( $this->bookings as $booking ) 
		{	
			//173112 -Issue - For one night bookings, order is displaying on both days 
			$charge_per_night			= get_post_meta( $booking['product_id'], "_phive_book_charge_per_night", 1 );
			$charge_per_night_option	= get_post_meta( $booking['product_id'], "_phive_book_charge_per_night_option", 1 );
			if( isset($charge_per_night) && $charge_per_night== 'yes' && $date_start == strtotime($booking['end']) && $booking['start'] != $booking['end'] && isset($charge_per_night_option) && $charge_per_night_option == 'yes'){
				continue ;
			}
			$class = $booking['booking_status'];
			$interval_format = isset($booking['IntervalDetails']['interval_format']) ? $booking['IntervalDetails']['interval_format'] : '';
			if ( $interval_format == 'month' ) {
				$booking['end'] = date('Y-m-t',strtotime($booking['end']));
			}
            // error_log(print_r($booking,1));
			$is_ph_canceled = isset($booking['is_ph_canceled']) ? $booking['is_ph_canceled'] : '';
			$class = ($is_ph_canceled == 'yes') ? 'canceled' : $class;
			// error_log("is_ph_canceled : ".print_r($is_ph_canceled,1));
			if ( strtotime($booking['start']) < $date_end && strtotime($booking['end']) >= $date_start ) 
			{
				$product = wc_get_product($booking['product_id']);
                $order = wc_get_order($booking['order_id']);
				$product_name = '';
				if ( $product ) {
					$product_name = $product->get_title();
				}

				$booking_title =  $this->ph_generate_booking_title_for_admin_calendar( $booking, $product_name );
				
				// 120786 - Customisation Support
				$style = array(
					"li" => '',
					"a"  => ''
				);
				$style = apply_filters('ph_modify_all_bookings_calendar_item_style', $style, $product, $booking['order_id'] );
				echo '<li class="'.$class.'" title="'.$product_name.'" style="'.$style["li"].'"><a href="' . admin_url( 'post.php?post=' . $booking['order_id'] . '&action=edit' ) . '" target="_blank" style="'.$style["a"].'">';
				echo '<strong>'.$booking_title;
				echo '</strong>';
                echo '</a>';
				echo '</li>';
			}
		}
	}

	/**
	 * Generate Booking Title For Admin Calendar
	 */
	public function ph_generate_booking_title_for_admin_calendar( $booking, $product_name ){

		$display_settings = get_option('ph_bookings_display_settigns');
		
		$order = wc_get_order($booking['order_id']);

		$ph_booking_details_on_calendar = isset($display_settings['ph_booking_details_on_calendar']) ? $display_settings['ph_booking_details_on_calendar'] : array('order_id', 'product_name');
		
		$booking_title = '';

		if ( in_array('order_id', $ph_booking_details_on_calendar) ) {

			$booking_title .= '#'.$booking['order_id'];
		}

		if ( in_array('product_name', $ph_booking_details_on_calendar) ) {

			$booking_title .= ( !empty($booking_title) ? ' - ' : '' ).ucfirst($product_name);
		}

		if ( in_array('customer_name', $ph_booking_details_on_calendar) ) {

			$booking_title .= ( !empty($booking_title) ? ' - ' : '' ).$order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		}

		if ( in_array('participants', $ph_booking_details_on_calendar) && !empty($booking['no_of_persons']) ) {

			$booking_title .= ( !empty($booking_title) ? ' - ' : '' ).$booking['no_of_persons'];
		}

		// Adding Assets information.
		if ( in_array('assets', $ph_booking_details_on_calendar) && !empty($booking['Assets']) && isset($this->assets_rules[$booking['Assets']]) ) {

			$asset_name = $this->assets_rules[ $booking['Assets'] ]['ph_booking_asset_name'];
			
			$booking_title .= ( !empty($booking_title) ? ' - ' : '' ).$asset_name;
		}

		// Hook for modify Booking title
		return apply_filters('ph_modify_booking_title_for_admin_calendar', $booking_title, $booking, $booking['order_id'] );
	}

	/**
	 * List bookings on a day.
	 */
	public function list_bookings_for_day($format='time') 
	{
		// echo 'yes';
		// error_log(print_r($this->bookings,1));
		$bookings_by_time = array();
		$all_day_bookings = array();
		$unqiue_ids       = array();
		//173112 -Issue - For one night bookings, order is displaying on both days 
		$day = strtotime(isset( $_REQUEST['calendar_day'] ) ? wc_clean( $_REQUEST['calendar_day'] ) : date( 'Y-m-d' ));
		foreach ( $this->bookings as $booking ) {
			$interval_format = isset($booking['IntervalDetails']['interval_format']) ? $booking['IntervalDetails']['interval_format'] : '';
			$charge_per_night=get_post_meta( $booking['product_id'], "_phive_book_charge_per_night", 1 );
			if ( $interval_format == 'day' || $interval_format == 'month' ) {
				if( isset($charge_per_night) && $charge_per_night== 'yes' && $day == strtotime($booking['end'])){
					continue ;
				}
				$all_day_bookings[] = $booking;
			} else {
				// $start_time = $booking->get_start_date( '', 'Gi' );

				$start_time = date('Gi',strtotime($booking['start']));
				// error_log('start time day booking: : '.$start_time);

				if ( ! isset( $bookings_by_time[ $start_time ] ) ) {
					$bookings_by_time[ $start_time ] = array();
				}

				$bookings_by_time[ $start_time ][] = $booking;
			}
		}

		ksort( $bookings_by_time );

		$column = 0;

		if($format == 'day')
		{
			foreach ( $all_day_bookings as $booking ) 
			{
				$product = wc_get_product($booking['product_id']);
				$product_name = '';
				if ( $product ) {
					$product_name = $product->get_title();
				}

				// 120786 - Customisation Support
				$style = array(
					"li" => '',
					"a"  => ''
				);
				$style = apply_filters('ph_modify_all_bookings_calendar_item_style', $style, $product, $booking['order_id'] );
				
				$booking_title = $this->ph_generate_booking_title_for_admin_calendar( $booking, $product_name );
				
				echo '<li title="'.$product_name.'" class="'.$booking['booking_status'].'" style="'.$style["li"].'"><a href="' . admin_url( 'post.php?post=' . $booking['order_id'] . '&action=edit' ) . '" target="_blank" style="'.$style["a"].'">' . $booking_title. '</a></li>';
				
				$column++;
			}
		}
		else
		{
			$start_column = $column;
			$last_end     = 0;

			$day = isset( $_REQUEST['calendar_day'] ) ? wc_clean( $_REQUEST['calendar_day'] ) : date( 'Y-m-d' );
			$day_timestamp = strtotime( $day );
			$next_day_timestamp = strtotime( $day . '+1 days' );

			foreach ( $bookings_by_time as $bookings ) {
				foreach ( $bookings as $booking ) {

					$product = wc_get_product($booking['product_id']);
					$product_name = '';
					if ( $product ) {
						$product_name = $product->get_title();
					}
					
					// Adjust start_time if event starts before the calendar day
					if ( strtotime($booking['start']) >= $day_timestamp ) {
						$start_time = date('Hi', strtotime($booking['start']));
					} else {
						$start_time = '0000';
					}

					// Adjust end_time if event ends after the calendar day
					if ( strtotime($booking['end']) >= $next_day_timestamp ) 
					{
						$end_time = '2400';
					} else {
						$end_time  = date('Hi', strtotime($booking['end']));
					}

					$height = ( strtotime( $end_time ) - strtotime( $start_time ) ) / 60;

					if ( $height < 30 ) {
						$height = 30;
					}

					if ( $last_end > $start_time ) {
						$column++;
					} else {
						$column = $start_column;
					}

					$start_time_stamp   = strtotime( $start_time );
					$start_hour_in_mins = date( 'H', $start_time_stamp ) * 60;
					$start_minutes      = date( 'i', strtotime( $start_time ) );
					$from_top           = $start_hour_in_mins + $start_minutes;

					// 120786 - Customisation Support
					$style = array(
						"li" => '',
						"a"  => ''
					);

					$style = apply_filters('ph_modify_all_bookings_calendar_item_style', $style, $product, $booking['order_id'] );

					$booking_title = $this->ph_generate_booking_title_for_admin_calendar( $booking, $product_name );

					echo '<li title="'.$product_name.'" class="'.$booking['booking_status'].'" style="left:' . esc_attr( 100 * $column ) . 'px; top: ' . esc_attr( $from_top ) . 'px; height: ' . esc_attr( $height ) . 'px;'.$style["li"].'"><a href="' . admin_url( 'post.php?post=' . $booking['order_id'] . '&action=edit' ) . '" target="_blank" style="'.$style["a"].'">' . esc_html( $booking_title) . '</a></li>';

					if ( $end_time > $last_end ) {
						$last_end = $end_time;
					}
				}
			}
		}
		
	}

	/**
	 * Get a random colour.
	 */
	public function random_color() {
		return sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) );
	}


	/**
	 * Filters products for narrowing search.
	 */
	public function product_filters() {
		$filters  = array();
        $args = array(
            'limit'	=>	-1,
            'type' => 'phive_booking',
        );
        $products = wc_get_products( $args );

		foreach ( $products as $product ) {
			$filters[ $product->get_id() ] = $product->get_name();

			// $resources = $product->get_resources();

			// foreach ( $resources as $resource ) {
			// 	$filters[ $resource->get_id() ] = '&nbsp;&nbsp;&nbsp;' . $resource->get_name();
			// }
		}

		return $filters;
	}

	/**
	 * Filters resources for narrowing search.
	 */
	public function booking_status_filter() {
		$filters = [
			'paid'					=>	__('Paid','bookings-and-appointments-for-woocommerce'),
			'un-paid'				=>	__('Unpaid','bookings-and-appointments-for-woocommerce'),
			'canceled'				=>	__('Cancelled','bookings-and-appointments-for-woocommerce'),
			'requires-confirmation'	=>	__('Requires Confirmation','bookings-and-appointments-for-woocommerce'),
			'partially-paid'		=>	__('Partially Paid','bookings-and-appointments-for-woocommerce'),
		];
		return $filters;
	}

    public function get_ph_bookings($start_time, $end_time, $filters, $view='month')
    {
		global $wpdb;
		$this->woocommerce_table = PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled();

		// 195218 Need to display custom order status bookings
		$custom_order_status = apply_filters(('ph_all_bookings_for_custom_order_status'), []);
		$ph_custom_order_status = "";
		if (!empty($custom_order_status) && is_array($custom_order_status)) {

			$table_name = $this->woocommerce_table ? "{$wpdb->prefix}wc_orders.status" : "{$wpdb->prefix}posts.post_status";

			foreach ($custom_order_status as $order_status) {

				$ph_custom_order_status .= " OR $table_name = '$order_status'";
			}
		}
		
		// WC HPOS
		if ($this->woocommerce_table) {

			$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id, ometa.customer_name, ometa.customer_last_name, ometa.customer_billing_email, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails, imeta.no_of_persons, imeta.is_ph_canceled, imeta.Assets
			FROM {$wpdb->prefix}wc_orders
			INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}wc_orders.id
			INNER JOIN (
				SELECT
				order_item_id,
				MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
				MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
				MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
				MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
				MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value Else '' END) AS no_of_persons,
				MAX(CASE WHEN meta_key = 'Assets' THEN meta_value Else '' END) AS Assets,
				MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
				MAX(CASE WHEN meta_key = 'canceled' THEN meta_value ELSE '' END) AS is_ph_canceled
				FROM {$wpdb->prefix}woocommerce_order_itemmeta
				GROUP BY order_item_id
			) as imeta on  imeta.order_item_id = oitems.order_item_id
			LEFT JOIN (
					SELECT
					order_id,
					first_name AS customer_name,
					last_name AS customer_last_name,
					email AS customer_billing_email
					FROM {$wpdb->prefix}wc_order_addresses
					GROUP BY order_id
			) as ometa on ometa.order_id = {$wpdb->prefix}wc_orders.id
			INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
			WHERE {$wpdb->prefix}wc_orders.type IN ('shop_order', 'shop_order_refund')
			AND (
				{$wpdb->prefix}wc_orders.status = 'wc-pending'
				OR {$wpdb->prefix}wc_orders.status = 'wc-processing'
				OR {$wpdb->prefix}wc_orders.status = 'wc-on-hold'
				OR {$wpdb->prefix}wc_orders.status = 'wc-completed'
				OR {$wpdb->prefix}wc_orders.status = 'wc-cancelled'
				OR {$wpdb->prefix}wc_orders.status = 'wc-refunded'
				OR {$wpdb->prefix}wc_orders.status = 'wc-failed'
				OR {$wpdb->prefix}wc_orders.status = 'wc-partially-paid'
				OR {$wpdb->prefix}wc_orders.status = 'wc-partial-payment'
				OR {$wpdb->prefix}wc_orders.status = 'wc-ng-complete'
				$ph_custom_order_status
			)
			AND tt.taxonomy IN ('product_type')
			AND t.slug = 'phive_booking'";
			
		} else {

			$query = "SELECT oitems.order_id, oitems.order_item_id,tr.object_id product_id, ometa.customer_name, ometa.customer_last_name, ometa.customer_billing_email, imeta.BookingStatus, imeta.BookFrom, imeta.BookTo, imeta.IntervalDetails, imeta.no_of_persons, imeta.is_ph_canceled, imeta.Assets
			FROM {$wpdb->prefix}posts
			INNER JOIN {$wpdb->prefix}woocommerce_order_items oitems on oitems.order_id = {$wpdb->prefix}posts.ID
			INNER JOIN (
					SELECT
					order_item_id,
					MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
					MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
					MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
					MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
					MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value Else '' END) AS no_of_persons,
					MAX(CASE WHEN meta_key = 'Assets' THEN meta_value Else '' END) AS Assets,
					MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
					MAX(CASE WHEN meta_key = 'canceled' THEN meta_value ELSE '' END) AS is_ph_canceled
					FROM {$wpdb->prefix}woocommerce_order_itemmeta
					GROUP BY order_item_id
			) as imeta on  imeta.order_item_id = oitems.order_item_id
			INNER JOIN (
					SELECT
					post_id,
					MAX(CASE WHEN meta_key = '_billing_first_name' THEN meta_value ELSE '' END) AS customer_name,
					MAX(CASE WHEN meta_key = '_billing_last_name' THEN meta_value ELSE '' END) AS customer_last_name,
					MAX(CASE WHEN meta_key = '_billing_email' THEN meta_value ELSE '' END) AS customer_billing_email
					FROM {$wpdb->prefix}postmeta
					GROUP BY post_id
			) as ometa on ometa.post_id = {$wpdb->prefix}posts.ID
			INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = imeta.ProductId
			INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id
			WHERE {$wpdb->prefix}posts.post_type IN ('shop_order', 'shop_order_refund')
			AND (
				{$wpdb->prefix}posts.post_status = 'wc-pending'
				OR {$wpdb->prefix}posts.post_status = 'wc-processing'
				OR {$wpdb->prefix}posts.post_status = 'wc-on-hold'
				OR {$wpdb->prefix}posts.post_status = 'wc-completed'
				OR {$wpdb->prefix}posts.post_status = 'wc-cancelled'
				OR {$wpdb->prefix}posts.post_status = 'wc-refunded'
				OR {$wpdb->prefix}posts.post_status = 'wc-failed'
				OR {$wpdb->prefix}posts.post_status = 'wc-partially-paid'
				OR {$wpdb->prefix}posts.post_status = 'wc-partial-payment'
				OR {$wpdb->prefix}posts.post_status = 'wc-ng-complete'
				$ph_custom_order_status
			)
			AND tt.taxonomy IN ('product_type')
			AND t.slug = 'phive_booking'";
		}
		
		$sub_query = "
			IF( LENGTH( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[7-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) ) = 7,
				CONCAT( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:7:".'"'."', -1), '".'"'."', -2),'".'"'."',1),'-01' ),
			SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP ".'"[10-16]"'.":".'"'."', -1), '".'"'."', -2),'".'"'."',1) )";
		
		// 126219
		$query = apply_filters('ph_modify_admin_calendar_view_base_query', $query, $start_time, $end_time, $filters, $view);

		// filter by booking status
		if( !empty($filters['filter_bookings_by_status']) ){
			$len = strlen($filters['filter_bookings_by_status']);
			$query .= " AND (SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookingStatus, 'i:0;s:".$len.":".'"'."', -1), '".'"'."', 1) = '".$filters['filter_bookings_by_status']."' OR imeta.BookingStatus = '".$filters['filter_bookings_by_status']."' ) ";
		}

		// filter by booking product_id
		if( !empty($filters['filter_bookings']) ){
			$query .= " AND imeta.ProductId = '".$filters['filter_bookings']."'";
		}

		if($view != 'day')
		{
			// bookings between given dates
			// if( !empty($start_time) ){
			// 	$query .= " AND ( DATE(".$sub_query.")  >= '".$start_time."')";
			// }

			$query.= 'AND ( (';

			// Booking dates in middle of search start and end - Booked From >= Start Time && Booked End <= End Time
			if( !empty($start_time) ){
				$query .= " DATE(".$sub_query.")  >= '".$start_time."'";
			}

			if( !empty($end_time) ){
				$filter_end_to_with_time = $end_time; 
				$filter_end_to_with_time.= " 23:59"; 
				$sub_query_for_booking_end = <<<EOD
				AND "{$filter_end_to_with_time}" >= IF(
					( NOT ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
					(
						IF(
							(imeta.BookTo = imeta.BookFrom AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month'),
							IF (
								SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
								DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
								DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
							),
							IF(
								NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
								(
									IF(
										SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
										DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
										DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
									)
								),
								SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 )
							)
						)
					),
					(
						IF(
							( ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
							(
								IF(
									NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
									(
										IF (  
											SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
											DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
											DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
										)
									),
									(
										IF(
											SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) > 1 ,
											IF (
												SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day',
												DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) DAY),
												DATE_ADD(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), '-01'), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MONTH)
											),
											SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
										)
									)
								)
							),
							SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )

						)
					)
				)
EOD;
				$query.= $sub_query_for_booking_end;
			}

			$query .= ")";

			// Search Start Time in Middle of Booked Dates - Booked From <= Start Time <= Booked To
			if( !empty($start_time))
			{
				$query .= 'OR (';
				
				$query .= " DATE(".$sub_query.") <= '".$start_time."'";

				$filter_end_to_with_time = $start_time; 
				//ticket 129031-not showing booking from this month to next month
				//$filter_end_to_with_time.= " 23:59"; 
				$sub_query_for_booking_end = <<<EOD
				AND "{$filter_end_to_with_time}" <= IF(
					( NOT ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
					(
						IF(
							(imeta.BookTo = imeta.BookFrom AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month'),
							IF (
								SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
								DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
								DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
							),
							IF(
								NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
								(
									IF(
										SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
										DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
										DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
									)
								),
								IF(
									SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
									last_day(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ),"-27")),
									SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 )
								)
							)
						)
					),
					(
						IF(
							( ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
							(
								IF(
									NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
									(
										IF (  
											SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
											DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
											DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
										)
									),
									(
										IF(
											SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) > 1 ,
											IF (
												SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day',
												DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) DAY),
												DATE_ADD(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), '-01'), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MONTH)
											),
											SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
										)
									)
								)
							),
							SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )

						)
					)
				)
EOD;
				$query.= $sub_query_for_booking_end;

				$query .= ')';
			}

			// Search End Time in Middle of Booked Dates - Booked From <= End Time <= Booked To
			if( !empty($end_time))
			{
				$query .= "OR (";
				
				$query .= " DATE(".$sub_query.") <= '".$end_time."'";

				$filter_end_to_with_time = $end_time; 
				$filter_end_to_with_time.= " 23:59"; 
				$sub_query_for_booking_end = <<<EOD
				AND "{$filter_end_to_with_time}" <= IF(
					( NOT ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
					(
						IF(
							(imeta.BookTo = imeta.BookFrom AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month'),
							IF (
								SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
								DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
								DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
							),
							IF(
								NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
								(
									IF(
										SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
										DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
										DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
									)
								),
								SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 )
							)
						)
					),
					(
						IF(
							( ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
							(
								IF(
									NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
									(
										IF (  
											SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
											DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
											DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
										)
									),
									(
										IF(
											SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) > 1 ,
											IF (
												SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day',
												DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) DAY),
												DATE_ADD(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), '-01'), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MONTH)
											),
											SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
										)
									)
								)
							),
							SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )

						)
					)
				)
EOD;
				$query.= $sub_query_for_booking_end;

				$query	.= ')';
			}

			$query .= ")";

			// error_log('query : '.$query);

		}
		else
		{
			if( !empty($start_time) )
			{
				$sub_query_for_booking_end = <<<EOD
				AND "{$start_time}" <= IF(
				( NOT ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
				(
				IF(
					(imeta.BookTo = imeta.BookFrom AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month'),
					IF (
						SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
						DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
						DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
					),
					IF(
						NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
						(
							IF(
								SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
								DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
								DATE_ADD(REPLACE(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 ), '/', '-' ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
							)
						),
						SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookTo ,'";', 1), '"', -1 )
					)
				)
				),
				(
				IF(
					( ( ISNULL(imeta.BookTo) OR imeta.BookTo = '') AND NOT ( ISNULL(imeta.IntervalDetails) OR imeta.IntervalDetails = '') ),
					(
						IF(
							NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day' AND NOT SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'month',
							(
								IF (  
									SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'hour',
									DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) HOUR),
									DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MINUTE)        
								)
							),
							(
								IF(
									SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) > 1 ,
									IF (
										SUBSTRING_INDEX (SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -1), '"', 1 ) = 'day',
										DATE_ADD(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) DAY),
										DATE_ADD(CONCAT(SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 ), '-01'), INTERVAL SUBSTRING_INDEX(SUBSTRING_INDEX( imeta.IntervalDetails , ':"', -3), '"', 1) MONTH)        
									),
									SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
								)
							)
						)
					),
					SUBSTRING_INDEX( SUBSTRING_INDEX( imeta.BookFrom ,'";', 1), '"', -1 )
				)
				)
				)
EOD;
			 	$query.= $sub_query_for_booking_end;
			}

			if( !empty($end_time) )
			{
				$query .= " AND (DATE(".$sub_query.") <= '".$end_time."')";
			}	
		}
		$query .=" ORDER BY order_id DESC";

        $results = $wpdb->get_results( $query );

        // print_r($results);
        $bookings = array();
        $item_ids = array();
        foreach ($results as $key => $result) {
            $order_id = $result->order_id;
            $order = wc_get_order( $order_id );
            if(!($order instanceof WC_Order))
            {
                continue;
            }
            
            $order_item_id=$result->order_item_id;
            if (in_array($order_item_id,$item_ids))
            {
                continue;
            }
            $item_ids[]=$order_item_id;
            $IntervalDetails	= unserialize($result->IntervalDetails);
            $BookFrom 			= ph_maybe_unserialize($result->BookFrom);
            $BookTo 			= ph_maybe_unserialize($result->BookTo);
			$is_ph_canceled 	= ph_maybe_unserialize($result->is_ph_canceled);
			$assets 			= ph_maybe_unserialize($result->Assets);

            if( !empty($BookTo) && ! empty($IntervalDetails) ) {
                $BookTo				= ! empty($BookTo) ? $BookTo : $BookFrom;
                $interval 			= $IntervalDetails['interval'];
                $interval_format	= $IntervalDetails['interval_format'];
                if(strtotime($BookTo)==strtotime($BookFrom) && ($interval_format!='day' && $interval_format!='month') )
                {
                    $BookTo 			= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookTo) ) );
                }
                elseif($interval_format!='day' && $interval_format!='month' )
                {
                    $BookTo 	= str_replace('/', '-', $BookTo);
                    $BookTo 	= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookTo) ) ); // adding interval to last block
                }
            }
            elseif (empty($BookTo) && !empty($IntervalDetails) ) {
                
                $interval 			= $IntervalDetails['interval'];
                $interval_format	= $IntervalDetails['interval_format'];
                $BookTo = $BookFrom;
                if($interval_format!='day' && $interval_format!='month' )
                {
                    $BookTo 			= date( 'Y-m-d H:i', strtotime( "+$interval $interval_format",strtotime($BookFrom) ) );
                }
                elseif($interval>1)
                {
                    $BookTo 			= date( 'Y-m-d', strtotime( "+$interval $interval_format",strtotime($BookFrom) ) );	
                }
                
            }

            $customer_name = !empty($result->customer_name) ? $result->customer_name.' '.$result->customer_last_name : $result->customer_billing_email;

            $bookings[] = array(
                'ID' 			=> $result->order_item_id,
                'order_id' 		=> $result->order_id,
                'product_id' 	=> $result->product_id,
                'start' 		=> $BookFrom,
                'end' 			=> $BookTo,
                'bookedby' 		=> $customer_name,
                'booking_status'=> ph_maybe_unserialize($result->BookingStatus),
                'no_of_persons' => $result->no_of_persons,
				'IntervalDetails' => $IntervalDetails,
				'is_ph_canceled' => $is_ph_canceled,
				'Assets' => $assets,
            );
        }
        return $bookings;
    }
}
