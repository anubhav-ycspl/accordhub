<?php

/**
 *
 * Creating the widget
 */
class PH_Booking_Search_Widget_Addon extends WP_Widget {

	function __construct() {
		parent::__construct(
			// Base ID of the widget
			'ph_booking_search_widget',
			// Widget name will appear in UI
			__( 'Booking Search Availability', 'bookings-and-appointments-for-woocommerce' ),
			// Widget description
			array( 'description' => __( 'Seach here the available Bookings', 'bookings-and-appointments-for-woocommerce' ) )
		);

		add_filter( 'posts_join', array( $this, 'ph_modify_search_join' ) );
		add_filter( 'posts_where', array( $this, 'ph_modify_search_where' ) );
		// Ticket #176787
		add_action( 'wp', array( $this, 'ph_sidebar_widget_hide' ) );
		add_filter( 'posts_clauses_request', array( $this, 'ph_bookings_posts_clauses_request' ), 9, 1 );
	}

	/**
	 *
	 * Hiding the SideBar widget
	 */
	function ph_sidebar_widget_hide() {
		// Ticket #176787
		add_filter( 'sidebars_widgets', array( $this, 'ph_maybe_unset_widgets_by_context' ), 9 );
		add_filter( 'woocommerce_page_title', array( $this, 'ph_woocommerce_page_title' ) );
	}

	/**
	 * @since 1.2.4
	 * This function changes the woocommerce search title
	 * @param $search_title contains the title for search results
	 */
	function ph_woocommerce_page_title( $search_title ) {

		$search_from = isset( $_GET['book_search_from'] ) ? $_GET['book_search_from'] : '';
		$search_to   = isset( $_GET['book_search_to'] ) ? $_GET['book_search_to'] : '';

		if ( is_search() && ! empty( $search_from ) && ! empty( $search_to ) ) {

			$search_title = sprintf( __( 'Search results for: &ldquo;%1$s to %2$s&rdquo;', 'bookings-and-appointments-for-woocommerce' ), $search_from, $search_to );
		}
		return $search_title;
	}
	/**
	 * Booking From
	 *
	 * @param $rules contains book from for search results
	 */
	public function ph_bookings_posts_clauses_request( $rules ) {
		// 144118 - Issue: When activating this add-on, dokan geolocation search filter is throwing incorrect search results.
		if ( ! is_search() || ! isset( $_GET['s'] ) || ! isset( $_GET['book_search_from'] ) ) {
			return $rules;
		}

		global $wpdb;
		if ( isset( $rules['groupby'] ) ) {
			$rules['groupby'] = "{$wpdb->posts}.post_name";
		}
		return $rules;
	}
	/**
	 * Unset the SideBar widget
	 *
	 * @param $sidebars_widgets contains widget layout
	 */
	function ph_maybe_unset_widgets_by_context( $sidebars_widgets ) {
		$invoker = isset( $_GET['invoker'] ) ? $_GET['invoker'] : '';

		if (
			! is_shop() && ! is_home() && ! is_search() && ! is_admin()
			&& ! is_product_category() && ! is_front_page()
			&& ! ( is_product() && ( $invoker == 'phbookingsearch' ) )
		) {

			foreach ( $sidebars_widgets as $widget_area => $widget_list ) {

				if ( $widget_area == 'wp_inactive_widgets' || empty( $widget_list ) ) {
					continue;
				}

				foreach ( $widget_list as $pos => $widget_id ) {

					$widget_id_array = explode( '-', $widget_id );

					if ( ! empty( $widget_id_array[0] ) && $widget_id_array[0] == 'ph_booking_search_widget' ) {
						unset( $sidebars_widgets[ $widget_area ][ $pos ] );
					}
				}
			}
		}

		return $sidebars_widgets;
	}

	public function ph_modify_search_join( $join ) {
		if ( ! is_search() || ! isset( $_GET['s'] ) || ! isset( $_GET['book_search_from'] ) || 'product' == get_post_type() ) {
			return $join;
		}

		// Set search params in session to autofill the booking calendar on product page.
		if ( WC()->session ) {

			$search_params = $_GET;

			$from = str_replace( '/', '-', $search_params['book_search_from'] );
			$to = str_replace( '/', '-', $search_params['book_search_to'] );

			$format = $search_params['ph_book_search_filter_date_and_time'] ? 'Y-m-d h:i A' : 'Y-m-d';

			$search_params['book_search_from'] = date($format, strtotime($from));
			$search_params['book_search_to'] = !empty($search_params['book_search_to']) ? date($format, strtotime($to)) : date($format, strtotime($from));

			WC()->session->set( 'ph_bookings_search_params', $search_params );
		}

		global $wpdb;

		$join = "INNER JOIN {$wpdb->prefix}term_relationships AS tr ON tr.object_id = {$wpdb->prefix}posts.ID
						INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
						INNER JOIN {$wpdb->prefix}terms AS t ON t.term_id = tt.term_id";

		return ( $join );
	}


	public function ph_modify_search_where( $where ) {
		if ( ! is_search() || ! isset( $_GET['s'] ) || ! isset( $_GET['book_search_from'] ) || 'product' == get_post_time() ) {
			return $where;
		}
		global $wpdb;

		$filter_from = isset( $_GET['book_search_from'] ) ? $_GET['book_search_from'] : '';
		$filter_to   = isset( $_GET['book_search_to'] ) ? $_GET['book_search_to'] : '';

		// Consider from date as to when searched for fixed date.
		if (empty($filter_to)) {
			$filter_to = $filter_from;
		}

		// changing  date format to defualt
		$filter_from = str_replace( '/', '-', $filter_from );
		$filter_to   = str_replace( '/', '-', $filter_to );
		$filter_from = date( 'Y-m-d', strtotime( $filter_from ) );
		$filter_to   = date( 'Y-m-d', strtotime( $filter_to ) );

		$is_time_search = isset($_GET['ph_book_search_filter_date_and_time']) ? $_GET['ph_book_search_filter_date_and_time'] : false;
		$is_fixed_date = isset($_GET['ph_fixed_date']) ? $_GET['ph_fixed_date'] : false;

		// data to filter product
		$filter_time_from              = isset( $_GET['book_search_time_from'] ) ? $_GET['book_search_time_from'] : '';
		$filter_time_to                = isset( $_GET['book_search_time_to'] ) ? $_GET['book_search_time_to'] : '';
		$filter_asset_name             = (isset( $_GET['ph_search_asset_name'] ) && $_GET['ph_search_asset_name'] != 'select') ? $_GET['ph_search_asset_name'] : '';
		$filter_number_of_participants = isset( $_GET['book_search_number_of_participants'] ) ? $_GET['book_search_number_of_participants'] : '';
		$participantsJson              = isset( $_GET['ph_book_search_participants'] ) ? stripslashes( $_GET['ph_book_search_participants'] ) : '';
		$participantsArray             = json_decode( $participantsJson, true );
		$participants                  = array();
		if ( ! empty( $participantsArray ) ) {
			foreach ( $participantsArray as $participant ) {
				if ( isset( $participant['rule'] ) && isset( $participant['count'] ) ) {
					$participants[ $participant['rule'] ] = $participant['count'];
				}
			}
		}
		$show_partially_unavailable = isset( $_GET['show_partially_unavailable'] ) ? $_GET['show_partially_unavailable'] : false;

		$sub_where    = '';
		$filter_query = "
					IF( LENGTH( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP " . '"[7-16]"' . ':' . '"' . "', -1), '" . '"' . "', -2),'" . '"' . "',1) ) = 7,
					CONCAT( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:7:" . '"' . "', -1), '" . '"' . "', -2),'" . '"' . "',1),'-01' ),
					SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookFrom, 'i:0;s:REGEXP " . '"[10-16]"' . ':' . '"' . "', -1), '" . '"' . "', -2),'" . '"' . "',1) )";
		// ticket 120047--Showing a product if the Book to date is in between filter from and filter to dates
		$filter_query_to = "
					IF( LENGTH( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookTo, 'i:0;s:REGEXP " . '"[7-16]"' . ':' . '"' . "', -1), '" . '"' . "', -2),'" . '"' . "',1) ) = 7,
					last_day(CONCAT( SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookTo, 'i:0;s:7:" . '"' . "', -1), '" . '"' . "', -2),'" . '"' . "',1),'-27' )),
					SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookTo, 'i:0;s:REGEXP " . '"[10-16]"' . ':' . '"' . "', -1), '" . '"' . "', -2),'" . '"' . "',1) )";
		$sub_where      .= ' AND ( (';

		if ( ! empty( $filter_from ) ) {

			if ($is_time_search) {

				$search_from = str_replace('/', '-', $_GET['book_search_from']);

				$filter_time_from = date('h:i a', strtotime($search_from));
			}

			// combine from time with from date
			if ( ! empty( $filter_time_from ) ) {
				// Mysql support only H:i format
				$filter_time_from = date( 'H:i', strtotime( $filter_time_from ) );
				$filter_from     .= ' ' . $filter_time_from;
			}
			// $sub_where .= " AND (" ;
			// $sub_where .= "  DATE(".$filter_query.")  >= '".$filter_from."'" ;
			// $sub_where .= "  OR DATE(imeta.BookFrom) >= '".$filter_from."'" ;
			$sub_where .= ' 	TIMESTAMP(' . $filter_query . ")  <= '" . $filter_from . "'";
			// $sub_where .= " )" ;
		}
		// if( !empty($filter_to) ){
		// $sub_where .= " AND (" ;
		// $sub_where .= "     DATE(".$filter_query.") <= '".$filter_to."'" ;
		// $sub_where .= "  OR DATE(imeta.BookFrom) <= '".$filter_to."'" ;
		// $sub_where .= " )";
		// }

		if ( ! empty( $filter_to ) ) {
			// combine to time with to date
			if ( ! empty( $filter_time_to ) ) {

				if ($is_time_search) {

					$search_to = str_replace('/', '-', $_GET['book_search_to']);
	
					$filter_time_to = date('h:i a', strtotime($search_to));
				}

				// Mysql support only H:i format
				$filter_time_to = date( 'H:i', strtotime( $filter_time_to ) );
				$filter_to     .= ' ' . $filter_time_to;
			}
			$filter_to_with_time = $filter_to;
			// $filter_to_with_time .= " 23:59";
			$sub_query_for_booking_end = <<<EOD
                AND "{$filter_to_with_time}" <= IF(
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
			$sub_where                .= $sub_query_for_booking_end;
		}
		$sub_where .= ' )';

		if ( ! empty( $filter_from ) ) {

			$sub_where .= ' OR (';
			$sub_where .= ' 	TIMESTAMP(' . $filter_query . ")  >= '" . $filter_from . "'";
			if ( ! empty( $filter_to ) ) {
				$sub_where .= ' AND (';
				$sub_where .= ' 	TIMESTAMP(' . $filter_query . ") <= '" . $filter_to . "'";
				$sub_where .= ' )';
			}
			$sub_where .= ' )';
			// ticket 120047--Showing a product if the Book to date is in between filter from and filter to dates
			$sub_where .= ' OR (';
			$sub_where .= ' 	TIMESTAMP(' . $filter_query_to . ") >= '" . $filter_from . "'";
			if ( ! empty( $filter_to ) ) {
				$sub_where .= ' AND (';
				$sub_where .= ' 	TIMESTAMP(' . $filter_query_to . ") <= '" . $filter_to . "'";
				$sub_where .= ' )';
			}
			$sub_where .= ' )';
		}

		$sub_where .= ' )';

		$sub_query1 = " SELECT order_item_id, ProductId, NumberofPersons, BookingStatus, BookFrom, BookTo, IntervalDetails, personsAsBooking, buffer_after_id, buffer_before_id FROM 
			(
				SELECT order_item_id,
				MAX(CASE WHEN meta_key = '_product_id' THEN meta_value ELSE '' END) AS ProductId,
				MAX(CASE WHEN meta_key = 'booking_status' THEN meta_value ELSE '' END) AS BookingStatus,
				MAX(CASE WHEN meta_key = 'From' THEN meta_value ELSE '' END) AS BookFrom,
				MAX(CASE WHEN meta_key = 'To' THEN meta_value ELSE '' END) AS BookTo,
				MAX(CASE WHEN meta_key = 'Number of persons' THEN meta_value ELSE '' END) AS NumberofPersons,
				MAX(CASE WHEN meta_key = '_phive_booking_product_interval_details' THEN meta_value ELSE '' END) AS IntervalDetails,
				MAX(CASE WHEN meta_key = 'person_as_booking' THEN meta_value ELSE '' END) AS personsAsBooking,
				MAX(CASE WHEN meta_key = 'buffer_after_id' THEN meta_value ELSE '' END) AS buffer_after_id,
				MAX(CASE WHEN meta_key = 'buffer_before_id' THEN meta_value ELSE '' END) AS buffer_before_id
				FROM {$wpdb->prefix}woocommerce_order_itemmeta imeta
				WHERE 1=1 
				GROUP BY order_item_id
			) AS imeta 
			
			WHERE 1=1
			$sub_where
			AND (SUBSTRING_INDEX(SUBSTRING_INDEX(imeta.BookingStatus, 'i:0;s:8:" . '"' . "', -1), '" . '"' . "', 1) != 'canceled' and imeta.BookingStatus != 'canceled' ) ";

		$results        = $wpdb->get_results( $sub_query1, ARRAY_A );
		$final_products = array();

		// Checking all booking count of the products
		foreach ( $results as $key => $booking ) {

			$product_id       = $booking['ProductId'];

			if ( empty($product_id ) ) {
				continue;
			}
			$from             = ph_strtotime( ph_maybe_unserialize( $booking['BookFrom'] ) );
			$to               = ! empty( $booking['BookTo'] ) ? ph_strtotime( ph_maybe_unserialize( $booking['BookTo'] ) ) : $from;
			$personsAsBooking = ph_maybe_unserialize( $booking['personsAsBooking'] );
			$booking_count    = 0;

			if ( ! empty( $booking['buffer_before_id'] ) ) {

				$buffer_before = get_post_meta( ph_maybe_unserialize( $booking['buffer_before_id'] ), 'Buffer_before_From', 1 );
				$from          = ! empty( $buffer_before ) ? ph_strtotime( ph_maybe_unserialize( $buffer_before ) ) : $from;
			}

			// When bookings having buffer
			if ( ! empty( $booking['buffer_after_id'] ) ) {

				$buffer_after = get_post_meta( ph_maybe_unserialize( $booking['buffer_after_id'] ), 'Buffer_after_To', 1 );
				$to           = ! empty( $buffer_after ) ? ph_strtotime( ph_maybe_unserialize( $buffer_after ) ) : $to;
			}
			if ( $personsAsBooking == 'yes' ) {
				$booking_count = ! empty( $booking['NumberofPersons'] ) ? $booking['NumberofPersons'] : 0;
			} else {
				$booking_count = 1;
			}

			// When the product charge per night end date sould not calculted for booking count
			$charge_per_night = get_post_meta( $product_id, '_phive_book_charge_per_night', 1 );
			$interval_period  = get_post_meta( $product_id, '_phive_book_interval_period', 1 );

			if ( $charge_per_night == 'yes' && $interval_period == 'day' ) {

				// Check if $to is already a timestamp (integer)
				$to_timestamp = is_numeric($to) ? (int) $to : strtotime($to);

				if ( false !== $to_timestamp ) {
					$to = strtotime( '-1 day', $to_timestamp );
				} 
			}
			if ( isset( $final_products[ $product_id ] ) ) {
				$final_products[ $product_id ][] = array(
					'from'          => $from,
					'to'            => $to,
					'booking_count' => $booking_count,
				);
			} else {
				$final_products[ $product_id ][] = array(
					'from'          => $from,
					'to'            => $to,
					'booking_count' => $booking_count,
				);
			}
		}

		// CHecking the product blocked or not
		$blocked_products = array();
		foreach ( $final_products as $fproduct_id => $bookings ) {

			$interval            = get_post_meta( $fproduct_id, '_phive_book_interval', 1 );
			$interval_period     = get_post_meta( $fproduct_id, '_phive_book_interval_period', 1 );
			$buffer_period       = get_post_meta( $fproduct_id, '_phive_buffer_period', 1 );
			$phive_enable_buffer = get_post_meta( $fproduct_id, '_phive_enable_buffer', 1 );
			$buffer_before_time  = get_post_meta( $fproduct_id, '_phive_buffer_before', 1 );
			$buffer_after_time   = get_post_meta( $fproduct_id, '_phive_buffer_after', 1 );
			$shop_opening_time   = get_post_meta( $fproduct_id, '_phive_book_working_hour_start', 1 );
			$shop_closing_time   = get_post_meta( $fproduct_id, '_phive_book_working_hour_end', 1 );
			$allowd_per_slot     = get_post_meta( $fproduct_id, '_phive_book_allowed_per_slot', 1 );
			$enable_per_night 		= 	get_post_meta($fproduct_id, '_phive_book_charge_per_night', true);

			if ( ( $phive_enable_buffer == 'yes' ) && ( $interval_period == 'hour' || $interval_period == 'minutes' ) ) {
				$buffer_before_time = empty( $buffer_before_time ) ? '0' : $buffer_before_time;
				$buffer_after_time  = empty( $buffer_after_time ) ? '0' : $buffer_after_time;
				$interval           = $interval;
				if ( ( ( $buffer_before_time % $interval ) != 0 ) || ( ( $buffer_after_time % $interval ) != 0 ) ) {
					$interval += ( $buffer_before_time + $buffer_after_time );
				}
			}
			$current_date                  = $interval_period == 'minute' && $interval_period == 'hour' ? ph_strtotime( $filter_from ) : strtotime( date( 'Y-m-d', ph_strtotime( $filter_from ) ) );
			$curr_date_day                 = date( 'd', $current_date );
			$product_id_partially_availble = false;

			// Looping through interval
			while ( $current_date < ph_strtotime($filter_to) || ($interval_period != 'hour' && $interval_period != 'minute' && ( ($enable_per_night != 'yes' && $current_date <= ph_strtotime($filter_to)) || ($filter_from == $filter_to && $current_date == ph_strtotime($filter_to))) ) ) {

				if ( ( $interval_period == 'hour' || $interval_period == 'minute' ) && ! $this->is_working_time( $current_date, $shop_opening_time, $shop_closing_time ) ) {

					$curr_date_day = date( 'd', $current_date );
					$current_date  = strtotime( "+$interval $interval_period", $current_date );

					if ( date( 'd', $current_date ) != $curr_date_day ) {

						if ( ! empty( $shop_opening_time ) ) {

							$current_date = strtotime( date( "Y-m-d $shop_opening_time", $current_date ) );
						} else {
							$current_date = strtotime( date( 'Y-m-d 00:00', $current_date ) );
						}
					}
					continue;
				}
				$number_of_slots_booked = $this->number_of_slots_booked( $current_date, strtotime( "+$interval $interval_period", $current_date ), $bookings, $fproduct_id );
				if ( $number_of_slots_booked >= $allowd_per_slot && ! $show_partially_unavailable ) {
					$blocked_products[] = $fproduct_id;
					break;
				}
				if ( $show_partially_unavailable && $number_of_slots_booked < $allowd_per_slot ) {
					$product_id_partially_availble = true;
					break;
				}

				$curr_date_day = date( 'd', $current_date );
				$current_date  = strtotime( "+$interval $interval_period", $current_date );
				if ( ( $interval_period == 'hour' || $interval_period == 'minute' ) && date( 'd', $current_date ) != $curr_date_day ) {

					if ( ! empty( $shop_opening_time ) ) {

						$current_date = strtotime( date( "Y-m-d $shop_opening_time", $current_date ) );
					} else {
						$current_date = strtotime( date( 'Y-m-d 00:00', $current_date ) );
					}
				}
			}

			// If product is not available for all the slots between the search
			if ( $show_partially_unavailable && ! $product_id_partially_availble ) {

				$blocked_products[] = $fproduct_id;
			}
		}

		if ( ! empty( $blocked_products ) ) {
			$blocked_products_string = implode( ',', $blocked_products );

			$query_unblocked = "SELECT Distinct ID as product_id from {$wpdb->prefix}posts
					inner join {$wpdb->prefix}term_relationships AS term_r ON term_r.object_id = {$wpdb->prefix}posts.ID
					INNER JOIN {$wpdb->prefix}term_taxonomy AS term_t ON term_r.term_taxonomy_id = term_t.term_taxonomy_id 
					INNER JOIN {$wpdb->prefix}terms AS term ON term.term_id = term_t.term_id
					where {$wpdb->prefix}posts.post_type = 'product' and {$wpdb->prefix}posts.post_status = 'publish' and {$wpdb->prefix}posts.ID Not In($blocked_products_string)";

			$query_unblocked_where = "AND term_t.taxonomy IN ('product_type') and term.slug = 'phive_booking'";
		} else {
			$query_unblocked = "SELECT Distinct ID as product_id from {$wpdb->prefix}posts
					inner join {$wpdb->prefix}term_relationships AS term_r ON term_r.object_id = {$wpdb->prefix}posts.ID
					INNER JOIN {$wpdb->prefix}term_taxonomy AS term_t ON term_r.term_taxonomy_id = term_t.term_taxonomy_id 
					INNER JOIN {$wpdb->prefix}terms AS term ON term.term_id = term_t.term_id
					where {$wpdb->prefix}posts.post_type = 'product' and {$wpdb->prefix}posts.post_status = 'publish'";

			$query_unblocked_where = "AND term_t.taxonomy IN ('product_type') and term.slug = 'phive_booking'";
		}

		if ( is_product_category() ) {
			$current_term_id = get_queried_object()->term_id;
			// Ticket #176787
			$current_term          = get_queried_object()->slug;
			$query_unblocked_where = "AND term_t.taxonomy IN ('product_cat') and term.slug = '" . $current_term . "' AND term_r.term_taxonomy_id = $current_term_id";
		}

		$query_unblocked      .= $query_unblocked_where;
		$product_ids_unblocked = $wpdb->get_results( $query_unblocked, ARRAY_A );

		$availability_rules = '';
		if ( ! class_exists( 'phive_booking_calendar_strategy' ) ) {

			include_once dirname( PH_BOOKINGS_PLUGIN_FILE ) . '/includes/frondend/class-ph-booking-calendar-strategy.php';
		}

		foreach ( $product_ids_unblocked as $key => $product ) {
			$product_id = $product['product_id'];

			$product = wc_get_product($product_id);

			// If not a bookable product.
			if ( !ph_is_bookable_product( $product )) {

				$blocked_products[] = $product_id;
				continue;
			}

			$interval_period 	= $product->get_interval_period();
			$interval_type		= $product->get_interval_type();

			// Block hour/minute products when time is not enabled.
			if ( ( !$is_time_search && in_array( $interval_period, array( 'minute', 'hour') ) )
				|| ( $is_time_search && in_array( $interval_period, array( 'day', 'month' ) ) )
			) {
				$blocked_products[] = $product_id;
				continue;
			}

			// Only show fixed calendar products.
			if ( ( $is_fixed_date  && 'customer_choosen' == $interval_type )
				|| ( !$is_fixed_date  && 'fixed' == $interval_type ) )
			{				
				$blocked_products[] = $product_id;
				continue;
			}

			// asset search
			if ( ! empty( ( $filter_asset_name ) && ( $filter_asset_name != 'select' ) ) ) {
				$asset_enable           = get_post_meta( $product_id, '_phive_booking_assets_enable', 1 );
				$filter_asset_name_base = explode( '-', $filter_asset_name )[0];
				// check asset enabled/disabled
				if ( $asset_enable == 'yes' ) {
					$prod_asset = get_post_meta( $product_id, '_phive_booking_assets_pricing_rules', 1 );

					if ( is_iterable( $prod_asset ) ) {
						$asset_temp = 0;
						foreach ( $prod_asset as $key => $name ) {
							if ( $name['ph_booking_asset_id'] == $filter_asset_name ) {
								$asset_temp = 1;
								break;
							}
						}
					}
					if ( $asset_temp == 0 ) {
						$blocked_products[] = $product_id;
					}
				} else {
					$blocked_products[] = $product_id;
				}
			}

			// Participant filter.
			$is_participant_enabled = get_post_meta( $product_id, '_phive_booking_person_enable', true );

			if ( ! empty( $participants ) && is_array( $participants ) && 'no' === $is_participant_enabled ) {
				$blocked_products[] = $product_id;
			}

			if ( 'yes' === $is_participant_enabled && is_array( $participants ) ) {

				$persons_rules = get_post_meta( $product_id, '_phive_booking_persons_pricing_rules', true );

				$max_participants = get_post_meta( $product_id, '_phive_booking_maximum_number_of_allowed_participant', true );
				$min_participants = get_post_meta( $product_id, '_phive_booking_minimum_number_of_required_participant', true );


				if ( (! empty( $max_participants ) && ( array_sum( $participants ) > $max_participants ) )
					|| ( ! empty( $min_participants ) && ( array_sum( $participants ) < $min_participants ) ) ) {
					$blocked_products[] = $product_id;
				}

				foreach ( $participants as $participant => $count ) {

					$ruleFound = false;

					// Case when participant count coming as zero and the participant is not found within the rules, skip the check.
					if (!in_array($participant, $persons_rules) && empty($count)) {
						continue;
					}

					foreach ( $persons_rules as $rule ) {

						$min = $rule['ph_booking_persons_rule_min'];

						if ( !empty( $min ) && !array_key_exists( $rule['ph_booking_persons_rule_type'], $participants)) {
							$blocked_products[] = $product_id;
							continue;
						} 

						if ( $rule['ph_booking_persons_rule_type'] === $participant ) {
							$ruleFound = true;
							$max       = $rule['ph_booking_persons_rule_max'];

							// If max is empty or not numeric, assume no limit
							if ( ( ! empty( $max ) && ( $count > $max ) ) || (! empty( $min ) && ( $count < $min || empty( $count ) ) ) ) {
								$blocked_products[] = $product_id;
								break;
							}
						}
					}

					if ( ! $ruleFound ) {
						$blocked_products[] = $product_id;
					}
				}
			}

			// ticket-118190--show product having specific days for booking
			$blocked_products = apply_filters( 'ph_search_widget_blocked', $blocked_products, $product_id, $filter_from, $filter_to );

			// ticket-118190--restrict start day
			$restrict_start_day = get_post_meta( $product_id, '_phive_restrict_start_day', 1 );
			$product_interval_type      = get_post_meta( $product_id, '_phive_book_interval', 1 );

			if ( 'yes' === $restrict_start_day && 'day' === $product_interval_type) {
				
				$booking_start_days = get_post_meta( $product_id, '_phive_booking_start_days', 1 );
				$not_blocking       = 0;
				foreach ( $booking_start_days as $value ) {
					if ( date( 'N', strtotime( $filter_from ) ) == $value ) {
						$not_blocking = 1;
					}
				}
				if ( $not_blocking == 0 ) {
					$blocked_products[] = $product_id;
				}
			}

			// ticket49137
			if ( class_exists( 'Polylang' ) ) {
				if ( pll_current_language() != pll_get_post_language( $product_id ) ) {
					$blocked_products[] = $product_id;
				}
			}
			// Ticket #174377
			$default_product_id = $product_id;
			// 168031
			// WPML compatibility - Show only products in current language or site default language in case product not created for current language.
			if ( function_exists( 'icl_object_id' ) ) {
				$wpml_current_lang  = apply_filters( 'wpml_current_language', null );
				$wpml_default_lang  = apply_filters( 'wpml_default_language', null );
				$default_product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product_id );

				// Product ID
				$actualProductId = $product_id;

				// WPML product ID
				$product_id = apply_filters( 'wpml_object_id', $product_id, 'post', true, $wpml_current_lang );

				// Adding the main product_id to blocked products, if it has a different WPML product_id
				if ( $actualProductId != $product_id ) {
					$blocked_products[] = $actualProductId;
				}

				// Product language details
				$ph_product_language_details = apply_filters( 'wpml_post_language_details', null, $product_id );
				$post_language               = $wpml_default_lang;
				if ( ! empty( $ph_product_language_details ) ) {
					$post_language = isset( $ph_product_language_details['language_code'] ) ? $ph_product_language_details['language_code'] : $wpml_default_lang;
				}

				// Show products either from current language or default language.
				if ( $wpml_current_lang != $post_language && $wpml_default_lang != $post_language ) {
					$blocked_products[] = $product_id;
				}
				// Ticket #174377 Issue: When searching the date range using search widget, its showing the products which are not available. (in the secondary language)
				if ( array_search( $default_product_id, array_column( $product_ids_unblocked, 'product_id' ) ) === false ) {
					$blocked_products[] = $product_id;
				}
			}

			if ( strtotime( $filter_from ) < strtotime( date('Y-m-d') ) ) {
				$blocked_products[] = $product_id;
			} else {

				$availability_rules = get_post_meta( $default_product_id, '_phive_booking_availability_rules', 1 );

				$interval          = get_post_meta( $default_product_id, '_phive_book_interval', 1 );
				$interval_period   = get_post_meta( $default_product_id, '_phive_book_interval_period', 1 );
				$shop_opening_time = get_post_meta( $default_product_id, '_phive_book_working_hour_start', 1 );
				$shop_closing_time = get_post_meta( $default_product_id, '_phive_book_working_hour_end', 1 );

				$start              = strtotime( $filter_from );
				$end                = strtotime( $filter_to );
				$available_interval = '';
				$calendar_for       = '';
				if ( $interval_period == 'hour' || $interval_period == 'minute' ) {
					$calendar_for       = 'time-picker';
					$available_interval = $interval;
				} else if ('day' == $interval_period) {

					$calendar_for = 'date-picker';
				}

				$asset_availability_check      = 1;
				$flag                          = 0;
				$loop_count                    = 0;
				$product_id_partially_availble = false;
				$curr_date_day                 = date( 'd', $start );
				$calendar_strategy             = new phive_booking_calendar_strategy( $product_id );

				/**
				 * In case of searching with time, if the searched time slot doesn't match with the product slots, the product will be blocked.
				 * Previously we didn't have a strict check, however with the inroduction of summary page view, since the searched date and time is direcly read from the search bar to display,
				 * it will cause error while trying to book for scenarios were searched slot is available but fall's within a slot
				 * For example - searched for 10:00 to 11:00, but let's say according to the product configuration the slots are like - 9:00 to 12:00 3 hours
				 */
				if ($is_time_search) {
					// Get the first day of the month
					$date 		= new DateTime($search_from);
					$first_date = $date->modify('first day of this month')->format('Y-m-d 00:00');
					$end_date 	= $date->modify('last day of this month')->format('Y-m-d 00:00');

					$first_available_date	= $calendar_strategy->get_first_available_date($first_date, $product_id);
					$next_available_time 	= $first_available_date;

					$is_matching_slot_found = false;

					$start_date = strtotime($first_date);
					$end_date 	= strtotime($end_date);

					if (!empty($shop_opening_time)) {

						$start_date = strtotime( date( "Y-m-d $shop_opening_time", $start_date ) );
					}

					/**
					 * Loop from first day until the month end to figure out all the available slots for the product.
					 * If the searched from time doesn't strictly match with any of the slots, then the product will be blocked.
					 */
					while( $start_date < $end_date ) {

						$next_available_date = $calendar_strategy->get_next_available_time($start_date, $interval, $product_id);

						if (!empty($next_available_date)) {
							$next_available_time = date( 'Y-m-d H:i', $next_available_date );
						}

						// Compare next available time with searched from time, if match found update the flag.
						if (!empty($next_available_time) && strtotime($filter_from) == strtotime($next_available_time)) {
							$is_matching_slot_found = true;
							break;
						} else if (!empty($next_available_time) && strtotime($next_available_time) > strtotime($filter_from)) {
							// If next available time crossed the searched from time, no point in continuing the loop.
							break;
						}

						// Increment the start date based on interval settings.
						if ( !empty($shop_opening_time) ) {

							// Calculate today's shop schedule
							$today_date = date('Y-m-d', $start_date);
							$closing_timestamp = strtotime("$today_date $shop_closing_time");
							$opening_timestamp = strtotime("$today_date $shop_opening_time");

							// Increment time
							$start_date = strtotime("+$interval $interval_period", $start_date);

							// If crossed today's closing time, move to next day's opening
							if ( $start_date > $closing_timestamp ) {
								$next_day = strtotime('+1 day', $closing_timestamp);
								$start_date = strtotime(date('Y-m-d', $next_day) . ' ' . $shop_opening_time);
							}
						} else {

							// Non-fixed: just increment
							$start_date = strtotime("+$interval $interval_period", $start_date);
						}
					}

					// When slot's aren't matched block the product.
					if (!$is_matching_slot_found) {
						$blocked_products[] = $product_id;
					}

				}

				while ( ( $start < $end || ( $interval_period != 'hour' && $interval_period != 'minute' && $start <= $end ) ) && $loop_count <= 60 ) {

					if ( ( $interval_period == 'hour' || $interval_period == 'minute' ) && ! $this->is_working_time( $start, $shop_opening_time, $shop_closing_time ) ) {

						$curr_date_day = date( 'd', $start );
						$start         = strtotime( "+$interval $interval_period", $start );

						if ( date( 'd', $start ) != $curr_date_day ) {

							if ( ! empty( $shop_opening_time ) ) {

								$start = strtotime( date( "Y-m-d $shop_opening_time", $start ) );
							} else {
								$start = strtotime( date( 'Y-m-d 00:00', $start ) );
							}
						}
						continue;
					}

					$product_availability = $calendar_strategy->is_available( $start, '', $default_product_id, $calendar_for);
					if ( $product_availability === false && ! $show_partially_unavailable ) {
						$blocked_products[] = $product_id;
						break;
					} else {
						$assets_enabled = get_post_meta( $default_product_id, '_phive_booking_assets_enable', 1 );
						if ( $assets_enabled == 'yes' ) {

							// ticket 118190--The product should be displayed when the night option is enabled and Asset quantity is one. Search to date is the checkin date for another booking of the product.
							$charge_per_night     = get_post_meta( $default_product_id, '_phive_book_charge_per_night', 1 );
							$product_availability = $this->is_asset_available( $start, $default_product_id );
							// check the asset is available for each block.
							if ( ! empty( $filter_asset_name ) ) {
								$asset_availability_check = $this->get_asset_availability( $default_product_id, $filter_asset_name, $start );
							}

							// Ticket 134820 -- Increment the availability for start and end date if booking per night option enabled.
							if ( $start == $end ) {
								$flag = 0;
							}
							if ( $charge_per_night == 'yes' && $flag == 0 ) {
								$product_availability     += 1;
								$asset_availability_check += 1;
								$flag                      = 1;
							}

							if ( ( $product_availability == 0 || ! $asset_availability_check ) && ! $show_partially_unavailable ) {
								$blocked_products[] = $product_id;
								break;
							} elseif ( $show_partially_unavailable && ( $product_availability != 0 && $asset_availability_check ) ) {
								$product_id_partially_availble = true;
								break;
							}
						} elseif ( $show_partially_unavailable && $product_availability ) {
							$product_id_partially_availble = true;
							break;
						}
					}
					$curr_date_day = date( 'd', $start );
					$start         = strtotime( "+$interval $interval_period", $start );
					if ( ( $interval_period == 'hour' || $interval_period == 'minute' ) && date( 'd', $start ) != $curr_date_day ) {

						if ( ! empty( $shop_opening_time ) ) {

							$start = strtotime( date( "Y-m-d $shop_opening_time", $start ) );
						} else {
							$start = strtotime( date( 'Y-m-d 00:00', $start ) );
						}
					}
					++$loop_count;
				}
				if ( $show_partially_unavailable && ! $product_id_partially_availble ) {

					$blocked_products[] = $product_id;
				}
			}
		}

		$where  = '';
		$where .= " AND {$wpdb->prefix}posts.post_type IN ( 'product' ) AND ({$wpdb->prefix}posts.post_status IN ('publish') )";
		if ( ! empty( $blocked_products ) ) {
			$blocked_products = implode( ',', $blocked_products );
			$where           .= " AND ( {$wpdb->prefix}posts.ID NOT IN ( $blocked_products ) )";
		}

		if ( is_product_category() ) {
			$current_term_id = get_queried_object()->term_id;
			// Ticket #176787
			$current_term = get_queried_object()->slug;
			$where       .= "AND tt.taxonomy IN ('product_cat') AND t.slug = '" . $current_term . "' AND tr.term_taxonomy_id = $current_term_id";
		} else {
			$where .= " AND tt.taxonomy IN ('product_type')";
			$where .= " AND t.slug = 'phive_booking' ";
		}
		return $where;
	}

	/**
	 * Product Details
	 */
	public function get_all_products() {
		$args     = array(
			'limit' => -1,
		);
		$products = wc_get_products( $args );
		return $products;
	}

	/**
	 * Creating widget front-end
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );

		$filter_date_and_time               = isset( $instance['filter_date_and_time'] ) ? $instance['filter_date_and_time'] : false;
		$filter_asset_name                  = isset( $instance['filter_asset_name'] ) ? $instance['filter_asset_name'] : false;
		$filter_number_of_participant       = isset( $instance['filter_number_of_participant'] ) ? $instance['filter_number_of_participant'] : false;
		$filter_asset_name_label            = isset( $instance['filter_asset_name_label'] ) ? $instance['filter_asset_name_label'] : __( ' ', 'bookings-and-appointments-for-woocommerce' );
		$filter_number_of_participant_label = isset( $instance['filter_number_of_participant_label'] ) ? $instance['filter_number_of_participant_label'] : __( 'Participant Count', 'bookings-and-appointments-for-woocommerce' );
		$filter_interval_time               = isset( $instance['filter_interval_time'] ) ? $instance['filter_interval_time'] : '';
		$filter_time_pick_from              = isset( $instance['filter_time_pick_from'] ) ? $instance['filter_time_pick_from'] : '';
		$filter_time_pick_to                = isset( $instance['filter_time_pick_to'] ) ? $instance['filter_time_pick_to'] : '';

		// Daily range filter options.
		$filter_daily_range_from            = isset( $instance['filter_daily_range_from'] ) ? $instance['filter_daily_range_from'] : '';
		$filter_daily_range_to              = isset( $instance['filter_daily_range_to'] ) ? $instance['filter_daily_range_to'] : '';
		$filter_time_format					= isset( $instance['filter_time_format'] ) ? $instance['filter_time_format'] : 'time_24hr';

		$restrict_checkin                   = isset( $instance['restrict_checkin'] ) ? $instance['restrict_checkin'] : '';
		$restrict_checkout                  = isset( $instance['restrict_checkout'] ) ? $instance['restrict_checkout'] : '';
		$restrict_checkin_checkbox          = isset( $instance['restrict_checkin_checkbox'] ) ? $instance['restrict_checkin_checkbox'] : '';
		$restrict_checkout_checkbox         = isset( $instance['restrict_checkout_checkbox'] ) ? $instance['restrict_checkout_checkbox'] : '';
		$filter_date_format                 = isset( $instance['filter_date_format'] ) ? $instance['filter_date_format'] : 'yy-mm-dd';
		$show_partially_unavailable         = isset( $instance['show_partially_unavailable'] ) ? $instance['show_partially_unavailable'] : '';
		$restrict_widget_checkbox           = isset( $instance['restrict_widget_checkbox'] ) ? $instance['restrict_widget_checkbox'] : false;
		$restrict_widget                    = isset( $instance['restrict_widget'] ) && ! empty( $instance['restrict_widget'] ) ? $instance['restrict_widget'] : array();
		$disable_clear_button               = isset( $instance['disable_clear_button'] ) ? $instance['disable_clear_button'] : '';
		$enable_book_now_button             = isset( $instance['enable_book_now_button'] ) ? $instance['enable_book_now_button'] : '';
		$enable_view_product_button         = isset( $instance['enable_view_product_button'] ) ? $instance['enable_view_product_button'] : '';
		$view_product_button_text           = isset( $instance['view_product_button_text'] ) ? $instance['view_product_button_text'] : '';
		$search_text                        = isset( $instance['search_text'] ) ? $instance['search_text'] : __( 'Search', 'bookings-and-appointments-for-woocommerce' );
		$search_class                       = isset( $instance['search_class'] ) ? $instance['search_class'] : '';
		$search_text_color                  = isset( $instance['search_text_color'] ) ? $instance['search_text_color'] : '#2271b1';
		$search_background_color            = isset( $instance['search_background_color'] ) ? $instance['search_background_color'] : '#ffffff';
		$clear_text                         = isset( $instance['clear_text'] ) ? $instance['clear_text'] : __( 'Clear', 'bookings-and-appointments-for-woocommerce' );
		$clear_class                        = isset( $instance['clear_class'] ) ? $instance['clear_class'] : '';
		$clear_text_color                   = isset( $instance['clear_text_color'] ) ? $instance['clear_text_color'] : '#ffffff';
		$clear_background_color             = isset( $instance['clear_background_color'] ) ? $instance['clear_background_color'] : '#2271b1';
		$from_date_text                     = isset( $instance['from_date_text'] ) ? $instance['from_date_text'] : __( 'From', 'bookings-and-appointments-for-woocommerce' );
		$to_date_text                       = isset( $instance['to_date_text'] ) ? $instance['to_date_text'] : __( 'To', 'bookings-and-appointments-for-woocommerce' );
		$from_time_text                     = isset( $instance['from_time_text'] ) ? $instance['from_time_text'] : __( 'Select a time', 'bookings-and-appointments-for-woocommerce' );
		$to_time_text                       = isset( $instance['to_time_text'] ) ? $instance['to_time_text'] : __( 'Select a time', 'bookings-and-appointments-for-woocommerce' );
		$fixed_date                         = isset( $instance['fixed_date'] ) ? $instance['fixed_date'] : false;
		$participant_rules                  = isset( $instance['participant_rules'] ) ? $instance['participant_rules'] : array();
		$product_view                       = isset( $instance['product_view'] ) ? $instance['product_view'] : 'summary';
		$border_width                       = isset( $instance['border_width'] ) ? $instance['border_width'] : '1';
		$border_style                       = isset( $instance['border_style'] ) ? $instance['border_style'] : 'solid';
		$border_radius                      = isset( $instance['border_radius'] ) ? $instance['border_radius'] : '1';
		$border_color                       = isset( $instance['border_color'] ) ? $instance['border_color'] : '#8f8f8f';

		$is_widget_restricted = false;
		if ( $restrict_widget_checkbox ) {
			if ( ! in_array( 'home', $restrict_widget ) && is_home() ) {
				$is_widget_restricted = true;
			} elseif ( ! in_array( 'custom_home', $restrict_widget ) && is_front_page() && !is_home() ) {
				$is_widget_restricted = true;
			} elseif ( ! in_array( 'shop', $restrict_widget ) && is_shop() ) {
				$is_widget_restricted = true;
			} elseif ( ! in_array( 'product_cat', $restrict_widget ) && is_product_category() ) {
				$is_widget_restricted = true;
			}
		}

		if ( $is_widget_restricted ) {

			return;
		}
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];

		if ( ! empty( $title ) ) {

			echo $args['before_title'] . $title . $args['after_title'];
		}
		// This is where you run the code and display the output
		include 'includes/ph-search-widget-form-html.php';
		echo $args['after_widget'];
	}

	/**
	 * Widget Backend
	 */
	public function form( $instance ) {

		$defaults = array(
			'title'                              => '',
			'filter_date_and_time'               => '',
			'filter_asset_name'                  => '',
			'filter_number_of_participant'       => '',
			'filter_asset_name_label'            => __( 'Select an Asset', 'bookings-and-appointments-for-woocommerce' ),
			'filter_number_of_participant_label' => __( 'Participant Count', 'bookings-and-appointments-for-woocommerce' ),
			'filter_interval_time'               => '',
			'filter_time_pick_from'              => '',
			'filter_time_pick_to'                => '',
			'filter_daily_range_from'            => '',
			'filter_daily_range_to'              => '',
			'filter_time_format'                 => 'time_24hr',
			'restrict_checkin_checkbox'          => '',
			'restrict_checkout_checkbox'         => '',
			'restrict_checkin'                   => array(),
			'restrict_checkout'                  => array(),
			'filter_date_format'                 => 'yy-mm-dd',
			'show_partially_unavailable'         => '',
			'restrict_widget_checkbox'           => '',
			'restrict_widget'                    => array(),
			'disable_clear_button'               => '',
			'enable_book_now_button'             => '',
			'enable_view_product_button'         => '',
			'view_product_button_text'           => __( 'View Product', 'bookings-and-appointments-for-woocommerce' ),
			'search_text'                        => __( 'Search', 'bookings-and-appointments-for-woocommerce' ),
			'search_class'                       => '',
			'search_text_color'                  => '#ffffff',
			'search_background_color'            => '#2271b1',
			'clear_text'                         => __( 'Clear', 'bookings-and-appointments-for-woocommerce' ),
			'clear_class'                        => '',
			'clear_text_color'                   => '#ffffff',
			'clear_background_color'             => '#2271b1',
			'border_width'                       => '1',
			'border_color'                       => '#8f8f8f',
			'border_radius'                      => '1',
			'border_style'                       => 'solid',
			'from_date_text'                     => __( 'From', 'bookings-and-appointments-for-woocommerce' ),
			'to_date_text'                       => __( 'To', 'bookings-and-appointments-for-woocommerce' ),
			'from_time_text'                     => __( 'Select a time', 'bookings-and-appointments-for-woocommerce' ),
			'to_time_text'                       => __( 'Select a time', 'bookings-and-appointments-for-woocommerce' ),
			'fixed_date'                         => false,
			'participant_rules'                  => array(),
			'product_view'                       => isset( $instance['product_view'] ) ? $instance['product_view'] : 'summary',

		);
		// Parse current settings with defaults
		extract( wp_parse_args( (array) $instance, $defaults ) );

		$display_settings            = get_option( 'ph_bookings_display_settigns' );
		$time_zone_conversion_enable = isset( $display_settings['time_zone_conversion_enable'] ) ? $display_settings['time_zone_conversion_enable'] : 'no';

		?>
		<input type="hidden" id='timezone_enabled' value="<?php echo $time_zone_conversion_enable; ?>">
		<?php
		$title      = isset( $instance['title'] ) ? $instance['title'] : __( 'Booking Search', 'bookings-and-appointments-for-woocommerce' );
		$products   = $this->get_all_products();
		$rule_types = array(); // Array to store all ph_booking_persons_rule_type values

		foreach ( $products as $product ) {
			$product_id   = $product->get_id();
			$product_type = $product->get_type();

			if ( $product_type == 'phive_booking' ) {
				$participant_pricing_rules = get_post_meta( $product_id, '_phive_booking_persons_pricing_rules', 1 );

				if ( is_array( $participant_pricing_rules ) ) {
					foreach ( $participant_pricing_rules as $rule ) {
						if ( isset( $rule['ph_booking_persons_rule_type'] ) ) {
							$rule_types[] = $rule['ph_booking_persons_rule_type'];
						}
					}
				}
			}
		}
		$rule_types = array_unique( $rule_types );
		// Widget admin form
		include 'includes/ph-search-widget-admin-form-html.php';
	}

	/**
	 * Updating widget replacing old instances with new
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                                       = array();
		$instance['title']                              = isset( $new_instance['title'] ) && ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['filter_date_and_time']               = isset( $new_instance['filter_date_and_time'] ) && ! empty( $new_instance['filter_date_and_time'] ) ? 1 : false;
		$instance['filter_asset_name']                  = isset( $new_instance['filter_asset_name'] ) && ! empty( $new_instance['filter_asset_name'] ) ? 1 : false;
		$instance['filter_number_of_participant']       = isset( $new_instance['filter_number_of_participant'] ) && ! empty( $new_instance['filter_number_of_participant'] ) ? 1 : false;
		$instance['filter_asset_name_label']            = isset( $new_instance['filter_asset_name_label'] ) && ! empty( $new_instance['filter_asset_name_label'] ) ? wp_strip_all_tags( $new_instance['filter_asset_name_label'] ) : __( 'Select an Asset', 'bookings-and-appointments-for-woocommerce' );
		$instance['filter_number_of_participant_label'] = isset( $new_instance['filter_number_of_participant_label'] ) && ! empty( $new_instance['filter_number_of_participant_label'] ) ? wp_strip_all_tags( $new_instance['filter_number_of_participant_label'] ) : __( 'Participant Count', 'bookings-and-appointments-for-woocommerce' );
		$instance['filter_interval_time']               = isset( $new_instance['filter_interval_time'] ) && ! empty( $new_instance['filter_interval_time'] ) ? wp_strip_all_tags( $new_instance['filter_interval_time'] ) : '';
		$instance['filter_time_pick_from']              = isset( $new_instance['filter_time_pick_from'] ) && ! empty( $new_instance['filter_time_pick_from'] ) ? wp_strip_all_tags( $new_instance['filter_time_pick_from'] ) : '';
		$instance['filter_time_pick_to']                = isset( $new_instance['filter_time_pick_to'] ) && ! empty( $new_instance['filter_time_pick_to'] ) ? wp_strip_all_tags( $new_instance['filter_time_pick_to'] ) : '';
		$instance['filter_daily_range_from']              = isset( $new_instance['filter_daily_range_from'] ) && ! empty( $new_instance['filter_daily_range_from'] ) ? wp_strip_all_tags( $new_instance['filter_daily_range_from'] ) : '';
		$instance['filter_daily_range_to']                = isset( $new_instance['filter_daily_range_to'] ) && ! empty( $new_instance['filter_daily_range_to'] ) ? wp_strip_all_tags( $new_instance['filter_daily_range_to'] ) : '';
		$instance['filter_time_format']                   = isset( $new_instance['filter_time_format'] ) && ! empty( $new_instance['filter_time_format'] ) ? wp_strip_all_tags( $new_instance['filter_time_format'] ) : 'time_24hr';
		$instance['restrict_checkin_checkbox']          = isset( $new_instance['restrict_checkin_checkbox'] ) && ! empty( $new_instance['restrict_checkin_checkbox'] ) ? 1 : false;
		$instance['restrict_checkout_checkbox']         = isset( $new_instance['restrict_checkout_checkbox'] ) && ! empty( $new_instance['restrict_checkout_checkbox'] ) ? 1 : false;
		$instance['restrict_checkin']                   = isset( $new_instance['restrict_checkin'] ) && ! empty( $new_instance['restrict_checkin'] ) ? array_map( 'strip_tags', $new_instance['restrict_checkin'] ) : '';
		$instance['restrict_checkout']                  = isset( $new_instance['restrict_checkout'] ) && ! empty( $new_instance['restrict_checkout'] ) ? array_map( 'strip_tags', $new_instance['restrict_checkout'] ) : '';
		$instance['filter_date_format']                 = isset( $new_instance['filter_date_format'] )  && ! empty( $new_instance['filter_date_format'] ) ? $new_instance['filter_date_format'] : 'yy-mm-dd';
		$instance['show_partially_unavailable']         = isset( $new_instance['show_partially_unavailable'] ) && ! empty( $new_instance['show_partially_unavailable'] ) ? $new_instance['show_partially_unavailable'] : false;
		$instance['restrict_widget_checkbox']           = isset( $new_instance['restrict_widget_checkbox'] ) && ! empty( $new_instance['restrict_widget_checkbox'] ) ? 1 : false;
		$instance['restrict_widget']                    = isset( $new_instance['restrict_widget'] ) && ! empty( $new_instance['restrict_widget'] ) ? array_map( 'strip_tags', $new_instance['restrict_widget'] ) : '';
		$instance['disable_clear_button']               = isset( $new_instance['disable_clear_button'] ) && ! empty( $new_instance['disable_clear_button'] ) ? 1 : false;
		$instance['enable_book_now_button']             = isset( $new_instance['enable_book_now_button'] ) && ! empty( $new_instance['enable_book_now_button'] ) ? 1 : false;
		$instance['enable_view_product_button']         = isset( $new_instance['enable_view_product_button'] ) && ! empty( $new_instance['enable_view_product_button'] ) ? 1 : false;
		$instance['view_product_button_text']           = isset( $new_instance['view_product_button_text'] ) && ! empty( $new_instance['view_product_button_text'] ) ? wp_strip_all_tags( $new_instance['view_product_button_text'] ) : __( 'View Product', 'bookings-and-appointments-for-woocommerce' );
		$instance['search_text']                        = isset( $new_instance['search_text'] ) && ! empty( $new_instance['search_text'] ) ? wp_strip_all_tags( $new_instance['search_text'] ) : __( 'Search', 'bookings-and-appointments-for-woocommerce' );
		$instance['search_class']                       = isset( $new_instance['search_class'] ) && ! empty( $new_instance['search_class'] ) ? wp_strip_all_tags( $new_instance['search_class'] ) : '';
		$instance['search_text_color']                  = isset( $new_instance['search_text_color'] ) && ! empty( $new_instance['search_text_color'] ) ? wp_strip_all_tags( $new_instance['search_text_color'] ) : '#ffffff';
		$instance['search_background_color']            = isset( $new_instance['search_background_color'] ) && ! empty( $new_instance['search_background_color'] ) ? wp_strip_all_tags( $new_instance['search_background_color'] ) : '#2271b1';
		$instance['clear_text']                         = isset( $new_instance['clear_text'] ) && ! empty( $new_instance['clear_text'] ) ? wp_strip_all_tags( $new_instance['clear_text'] ) : __( 'Clear', 'bookings-and-appointments-for-woocommerce' );
		$instance['clear_class']                        = isset( $new_instance['clear_class'] ) && ! empty( $new_instance['clear_class'] ) ? wp_strip_all_tags( $new_instance['clear_class'] ) : '';
		$instance['clear_text_color']                   = isset( $new_instance['clear_text_color'] ) && ! empty( $new_instance['clear_text_color'] ) ? wp_strip_all_tags( $new_instance['clear_text_color'] ) : '#ffffff';
		$instance['clear_background_color']             = isset( $new_instance['clear_background_color'] ) && ! empty( $new_instance['clear_background_color'] ) ? wp_strip_all_tags( $new_instance['clear_background_color'] ) : '#2271b1';
		$instance['from_date_text']                     = isset( $new_instance['from_date_text'] ) && ! empty( $new_instance['from_date_text'] ) ? wp_strip_all_tags( $new_instance['from_date_text'] ) : __( 'From', 'bookings-and-appointments-for-woocommerce' );
		$instance['to_date_text']                       = isset( $new_instance['to_date_text'] ) && ! empty( $new_instance['to_date_text'] ) ? wp_strip_all_tags( $new_instance['to_date_text'] ) : __( 'To', 'bookings-and-appointments-for-woocommerce' );
		$instance['from_time_text']                     = isset( $new_instance['from_time_text'] ) && ! empty( $new_instance['from_time_text'] ) ? wp_strip_all_tags( $new_instance['from_time_text'] ) : __( 'Select a time', 'bookings-and-appointments-for-woocommerce' );
		$instance['to_time_text']                       = isset( $new_instance['to_time_text'] ) && ! empty( $new_instance['to_time_text'] ) ? wp_strip_all_tags( $new_instance['to_time_text'] ) : __( 'Select a time', 'bookings-and-appointments-for-woocommerce' );
		$instance['fixed_date']                         = isset( $new_instance['fixed_date'] ) && $new_instance['fixed_date'] ? 1 : false;
		$instance['participant_rules']                  = isset( $new_instance['participant_rules'] ) ? array_map( 'sanitize_text_field', (array) $new_instance['participant_rules'] ) : array();
		$instance['product_view']                       = isset( $new_instance['product_view'] ) ? sanitize_text_field( $new_instance['product_view'] ) : 'summary';
		$instance['border_width']                       = isset( $new_instance['border_width'] ) && ! empty( $new_instance['border_width'] ) ? wp_strip_all_tags( $new_instance['border_width'] ) : '1';
		$instance['border_color']                       = isset( $new_instance['border_color'] ) && ! empty( $new_instance['border_color'] ) ? wp_strip_all_tags( $new_instance['border_color'] ) : '#8f8f8f';
		$instance['border_style']                       = isset( $new_instance['border_style'] ) && ! empty( $new_instance['border_style'] ) ? wp_strip_all_tags( $new_instance['border_style'] ) : 'solid';
		$instance['border_radius']                      = isset( $new_instance['border_radius'] ) && ! empty( $new_instance['border_radius'] ) ? wp_strip_all_tags( $new_instance['border_radius'] ) : '1';
		return $instance;
	}


	public function is_available( $start_time, $product_id, $availability_rules, $calendar_for = '' ) {

		$fixed_availability_from            = get_post_meta( $product_id, '_phive_fixed_availability_from', 1 );
		$fixed_availability_to              = get_post_meta( $product_id, '_phive_fixed_availability_to', 1 );
		$first_availability                 = get_post_meta( $product_id, '_phive_first_availability', 1 );
		$last_availability                  = get_post_meta( $product_id, '_phive_last_availability', 1 );
		$first_availability_interval_period = get_post_meta( $product_id, '_phive_first_availability_interval_period', 1 );
		$last_availability_interval_period  = get_post_meta( $product_id, '_phive_last_availability_interval_period', 1 );
		$unavailable_default                = get_post_meta( $product_id, '_phive_un_availability', 1 );
		$interval                           = get_post_meta( $product_id, '_phive_book_interval', 1 );
		$interval_period                    = get_post_meta( $product_id, '_phive_book_interval_period', 1 );

		// if first availability set
		if ( ( ! empty( $fixed_availability_from ) || ! empty( $fixed_availability_to ) ) ) {

			if ( ! $this->is_date_in_between( $start_time, $fixed_availability_from, $fixed_availability_to ) ) {
				return false;
			}
		}
		if ( ( ! empty( $first_availability ) || ! empty( $last_availability ) ) ) {             // If relative today

			$first_availability_date_format = ( $first_availability_interval_period == 'days' || $calendar_for == 'time-picker' ) ? 'Y-m-d' : 'Y-m-d H:i';
			$last_availability_date_format  = ( $last_availability_interval_period == 'days' || $calendar_for == 'time-picker' ) ? 'Y-m-d' : 'Y-m-d H:i';

			$from = date( $first_availability_date_format, strtotime( '+' . $first_availability . ' ' . $first_availability_interval_period ) );
			$to   = date( $last_availability_date_format, strtotime( '+' . $last_availability . ' ' . $last_availability_interval_period ) );

			if ( ! $this->is_date_in_between( $start_time, $from, $to, $calendar_for ) ) {
				return false;
			}
		}

		$end_time = $start_time;

		if ( $interval_period == 'hour' || $interval_period == 'minute' ) {
			$end_time = strtotime( "+$interval $interval_period", $start_time );
		}
		if ( ! empty( $availability_rules ) ) {
			foreach ( $availability_rules as $key => $rule ) {
				if ( $rule['availability_type'] == 'custom' ) {
					if ( $calendar_for == 'time-picker' || $calendar_for == 'date-picker' ) {
						$date_from      = explode( ' ', $rule['from_date'] );
						$date_from_hour = isset( $date_from[1] ) ? $date_from[1] : '';
						$date_from      = $date_from[0] . ' ' . $date_from_hour;
						$date_to        = explode( ' ', $rule['to_date'] );
						$date_to_hour   = isset( $date_to[1] ) ? $date_to[1] : '';
						$date_to        = $date_to[0] . ' ' . $date_to_hour;
					} else {
						$date_from = $rule['from_date'];
						$date_to   = $rule['to_date'];
					}
					if (
						! empty( $date_from ) && ! empty( $date_to )
						&& $start_time >= strtotime( $date_from )
						&& $start_time <= strtotime( $date_to )
					) {
						if ( $calendar_for == 'time-picker' && ( $date_from == $date_to || $start_time == strtotime( $date_to ) || $start_time == strtotime( $date_from ) ) ) {

							$interval        = get_post_meta( $product_id, '_phive_book_interval', 1 );
							$interval_period = get_post_meta( $product_id, '_phive_book_interval_period', 1 );

							$rule_time_from = $rule['from_date'];
							$rule_time_to   = $rule['to_date'];

							$shop_opening_time = get_post_meta( $product_id, '_phive_book_working_hour_start', 1 );
							$shop_opening_time = ! empty( $shop_opening_time ) ? date( 'H:i', strtotime( $shop_opening_time ) ) : '00:00';

							$shop_closing_time = get_post_meta( $product_id, '_phive_book_working_hour_end', 1 );
							$shop_closing_time = ! empty( $shop_closing_time ) ? date( 'H:i', strtotime( $shop_closing_time ) ) : '23:59';

							$date_from_start_time = date( 'Y-m-d', $start_time );
							$date_from_start_time = $date_from_start_time . ' ' . $shop_opening_time;
							$date_from_end_time   = date( 'Y-m-d', $start_time );
							$date_from_end_time   = $date_from_end_time . ' ' . $shop_closing_time;

							$found           = 0;
							$loop_count_time = 0;
							while ( strtotime( $date_from_start_time ) <= strtotime( $date_from_end_time ) && $loop_count_time <= 70 ) {
								if ( strtotime( $date_from_start_time ) >= strtotime( $rule_time_from ) && strtotime( $date_from_start_time ) <= strtotime( $rule_time_to ) ) {
									++$found;
								}
								$date_from_start_time = date( 'Y-m-d H:i', strtotime( $date_from_start_time . "+$interval $interval_period" ) );
								++$loop_count_time;
							}
							if ( $loop_count_time == $found ) {
								return $rule['is_bokable'] === 'yes';
							} else {
								return true;
							}
							// }
						} else {
							return $rule['is_bokable'] === 'yes';
						}
					}
				} elseif ( $rule['availability_type'] == 'months' && ! empty( $rule['from_month'] ) ) {

					$range_arr = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );

					if (
						$this->is_in_range( $range_arr, date( 'n', $start_time ), $rule['from_month'], $rule['to_month'] )
						&& $this->is_in_range( $range_arr, date( 'n', $end_time ), $rule['from_month'], $rule['to_month'] )
					) {
						return $rule['is_bokable'] === 'yes';
					}
				} elseif ( $rule['availability_type'] == 'days' && ! empty( $rule['from_week_day'] ) ) {
					$range_arr = array( 1, 2, 3, 4, 5, 6, 7 );

					if (
						$this->is_in_range( $range_arr, date( 'N', $start_time ), $rule['from_week_day'], $rule['to_week_day'] )
						&& $this->is_in_range( $range_arr, date( 'N', $end_time ), $rule['from_week_day'], $rule['to_week_day'] )
					) {
						return $rule['is_bokable'] === 'yes';
					}
				} elseif ( $rule['availability_type'] == 'time-all' && ! empty( $rule['from_time'] ) ) {
					if ( $calendar_for == 'time-picker' && empty( $rule['from_time'] ) && empty( $rule['to_time'] ) ) {
						return true;
					}
					if (
						strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $start_time ) ) <= strtotime( $rule['to_time'] )
						&& strtotime( date( 'H:i', $end_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['to_time'] )
					) {
						return $rule['is_bokable'] === 'yes';
					}
				} elseif ( strpos( $rule['availability_type'], 'time-' ) !== false && ! empty( $rule['from_time'] ) ) {
					$day = explode( '-', $rule['availability_type'] );
					$day = $day[1];
					if ( $calendar_for == 'time-picker' && strtolower( date( 'D', $start_time ) ) == $day ) {
						return true;
					}
					if (
						strtolower( date( 'D', $start_time ) ) == $day
						&& strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] )
						&& strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['to_time'] )
					) {
						return $rule['is_bokable'] === 'yes';
					}
				}
			}
		}
		if ( $unavailable_default == 'yes' ) {

			return false;
		} else {
			return true;
		}
	}


	private function is_asset_available( $start_time, $product_id ) {
		$assets_pricing_rules = get_post_meta( $product_id, '_phive_booking_assets_pricing_rules', 1 );
		$asset_count          = ( ! empty( $assets_pricing_rules ) && is_array( $assets_pricing_rules ) ) ? count( $assets_pricing_rules ) : 0;
		if ( $asset_count >= 1 ) {
			$asset_availability = array();
			foreach ( $assets_pricing_rules as $assets_pricing_rule ) {
				$asset_id = $assets_pricing_rule['ph_booking_asset_id'];
				if ( ! class_exists( 'phive_booking_assets' ) ) {
					include_once ABSPATH . '/wp-content/plugins/ph-bookings-appointments-woocommerce-premium/includes/class-ph-booking-assets.php';
				}
				$asset_obj = new phive_booking_assets( $asset_id );
				if ( ! empty( $asset_obj ) && ! empty( $asset_id ) ) {
					$asset_availability[] = $this->get_asset_availability( $product_id, $asset_id, $start_time );
				}
			}
			if ( array_sum( $asset_availability ) <= 0 ) {
				return 0;
			}
		}

		return true;
	}

	private function get_asset_availability( $product_id, $asset_id, $date, $ignore_freezed = false ) {

		$asset_obj       = new phive_booking_assets( $asset_id );
		$interval_period = get_post_meta( $product_id, '_phive_book_interval_period', 1 );
		$interval        = get_post_meta( $product_id, '_phive_book_interval', 1 );

		switch ( $interval_period ) {
			case 'day':
				$interval_string = '+1 day';
				$format          = 'Y-m-d';
				break;

			case 'hour':
			case 'minute':
				$interval_string = '+' . $interval . ' ' . $interval_period;
				$format          = 'Y-m-d H:i';
				break;

			case 'month':
				$interval_string = '+1 month';
				$format          = 'Y-m-d';
				break;
		}
		$from = date( $format, $date );
		$to   = date( $format, strtotime( $interval_string, $date ) );

		$display_settings       = get_option( 'ph_bookings_display_settigns' );
		$use_availability_table = ( isset( $display_settings['calculate_availability_using_availability_table'] ) && $display_settings['calculate_availability_using_availability_table'] == 'yes' ) ? true : false;
		$is_new_site            = get_option( 'ph_migration_new_site_v3_0_0', 'no' );
		$settings               = Ph_Booking_Manage_Availability_Data::ph_get_product_settings( $product_id );

		// Checking asset booking count
		if ( ( $use_availability_table || $is_new_site == 'yes' ) && Phive_Bookings_Database::ph_availability_table_exists() ) {
			$asset_booking_count = Ph_Booking_Manage_Availability_Data::get_asset_availability( $asset_id, strtotime( $from ), $ignore_freezed, $product_id, $settings );
		} else {
			$asset_booking_count = $asset_obj->get_availability( $from, $to, $ignore_freezed, $interval_period );
		}

		// Checking the asset availbility
		$asset_availability = $asset_obj->is_available( strtotime( $from ) );

		if ( ! $asset_availability ) {
			return 0;
		}
		return $asset_booking_count;
	}

	private function is_date_in_between( $date, $from, $to, $calendar_for = '' ) {

		$from = strtotime( $from );
		$to   = strtotime( $to );

		// If the date is between fixed window
		if ( ( ! empty( $from ) && $date < $from ) || ( ! empty( $to ) && $date > $to ) ) {
			return false;
		}

		if ( ( ! empty( $from ) && $date >= $from ) && ( ! empty( $to ) && $date <= $to ) ) {
			return true;
		} elseif ( $calendar_for == 'time-picker' ) {
			if ( empty( $this->last_availability ) && $from <= $date ) {
				return true;
			} elseif ( empty( $this->first_availability ) && $to >= $date ) {
				return true;
			} elseif ( $from <= $date && $to >= $date ) {
				return true;
			}
		} elseif ( $from > $to ) {
			if ( $date < $from ) {
				return false;
			}
			return true;
		} else {
			// Set a relative booking window option in Booking availability tab.
			if ( $date > $to ) {
				return false;
			}
			return true;
		}
	}

	private function is_in_range( $full_ranges, $check_me, $lower_range, $uppper_range ) {
		if ( $lower_range <= $uppper_range ) {
			return $check_me >= $lower_range && $check_me <= $uppper_range;
		} else {
			$count           = count( $full_ranges );
			$new_range_limit = $count - $lower_range + $count;
			$uppper_range   += $count;
			if ( $check_me < $lower_range ) {
				$check_me += $count;
			}

			$available_array = array();
			for ( $i = $lower_range; $i <= $uppper_range; $i++ ) {
				$available_array[] = $i;
			}

			return in_array( $check_me, $available_array );
		}
	}

	/**
	 * This function will check the time is working time or not of the shop
	 *
	 * @since 1.2.5
	 * @param string $date time to checking working time or not
	 */
	private function is_working_time( $date, $shop_opening_time, $shop_closing_time ) {
		$time = strtotime( date( 'H:i', $date ) );

		// if time falls in working hours
		if ( ( empty( $shop_opening_time ) && empty( $shop_closing_time ) )
			|| ! empty( $shop_opening_time ) && ! empty( $shop_closing_time ) && $time >= strtotime( $shop_opening_time ) && $time <= strtotime( $shop_closing_time )
			|| empty( $shop_opening_time ) && ! empty( $shop_closing_time ) && $time <= strtotime( $shop_closing_time )
			|| ! empty( $shop_opening_time ) && empty( $shop_closing_time ) && $time >= strtotime( $shop_opening_time )
		) {
			return true;
		}
		return false;
	}

	/**
	 * This function will check for the particular slot is blocked or not
	 *
	 * @since 1.2.5
	 * @return integer if product slot is available return true or false
	 */
	public function number_of_slots_booked( $current_date, $current_to, $bookings, $product_id ) {
		$booking_count = 0;
		foreach ( $bookings  as $booking ) {

			// Convert date to timestamp if it is not numeric
			$from = is_numeric( $booking['from'] ) ? (int) $booking['from'] : strtotime( $booking['from'] );
			$to   = is_numeric( $booking['to'] ) ? (int) $booking['to'] : strtotime( $booking['to'] );

			if ( ( $current_date >= $from && $current_date <= $to ) ||
				( $current_to >= $from && $current_to <= $to )
			) {
				$booking_count += $booking['booking_count'];
			}
		}
		return $booking_count;
	}
}
new PH_Booking_Search_Widget_Addon();
