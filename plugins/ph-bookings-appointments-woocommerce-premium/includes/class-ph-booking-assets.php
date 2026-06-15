<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* The Asset class.
*/
class phive_booking_assets{

	/**
	 * @var $id
	 */
	public $id;

	/**
	 * @var $serialized_asset_id
	 */
	public $serialized_asset_id;

	/**
	 * @var $settings
	 */
	public $settings;

	/**
	 * @var $booked_dates_set
	 */
	public $booked_dates_set;

	/**
	 * @var $booked_dates
	 */
	public $booked_dates;

	/**
	 * @var $max_quantity
	 */
	public $max_quantity;

	/**
	 * @var $unavailable_default
	 */
	public $unavailable_default;

	/**
	 * @var $availabiliy_rules
	 */
	public $availabiliy_rules;

	public function __construct( $asset_id = '') {
		$this->id 					= $asset_id;
		$this->serialized_asset_id 	= serialize(array($this->id) );
		$this->settings 			= get_option( 'ph_booking_settings_assets', 1 );
		$this->set_asset_max_quantity();
		$this->set_asset_availability_rules();
		$this->booked_dates_set = 0;
		$this->booked_dates = array();
	}

	private function get_all_bookings($ignore_freezed=false){
		$ph_get_asset_bookings_limit = apply_filters('ph_get_all_bookings_booked_dates_for_asset_query_limit', 1000, $this->id, $ignore_freezed);

		global $wpdb;
		$woocommerce_table = PH_WC_Bookings_Storage_Handler::ph_check_if_hpo_enabled();

		// Query for getting booked dates
		$query = "SELECT meta_key , meta_value , order_item_id, 'from_order_item_meta' as meta_type
			FROM {$wpdb->prefix}woocommerce_order_itemmeta AS t1
			WHERE t1.order_item_id
			IN (
				SELECT order_item_id
				FROM {$wpdb->prefix}woocommerce_order_itemmeta
				WHERE	meta_key = 'Assets'
				AND	(meta_value = '$this->serialized_asset_id' OR meta_value = '$this->id')
			)AND ( 
				meta_key = 'From'
				OR meta_key = 'To'
				OR meta_key = 'canceled'
				OR meta_key = 'person_as_booking'
				OR meta_key = 'Number of persons'
				OR meta_key = '_ph_book_to_date_with_night'
			)
			ORDER BY	t1.order_item_id DESC LIMIT 0,$ph_get_asset_bookings_limit";
		$booked = $wpdb->get_results( $query, OBJECT );
		$freezed=array();
		if(!$ignore_freezed)
		{
			//Query for getting freezed dates

			if ($woocommerce_table) {

				$query_post = "SELECT meta_key, meta_value, order_id as order_item_id, 'from_post_meta' as meta_type
				FROM {$wpdb->prefix}wc_orders_meta AS t1
				WHERE t1.order_id
				IN (
					SELECT order_id
					FROM  {$wpdb->prefix}wc_orders_meta
					WHERE (meta_key = 'asset_id' or meta_key = 'buffer_asset_id')
					AND meta_value = '$this->id'
				)AND ( 
					meta_key = 'From'
					OR meta_key = 'To'
					OR meta_key = 'person_as_booking'
					OR meta_key = 'Number of persons'
					OR meta_key = '_ph_book_to_date_with_night'
					Or meta_key = 'Buffer_before_From'
					Or meta_key = 'Buffer_before_To'
					Or meta_key = 'Buffer_after_From'
					Or meta_key = 'Buffer_after_To'
				)
				AND t1.order_id 
				NOT IN (select order_id
					from {$wpdb->prefix}wc_orders_meta
					where meta_key='ph_canceled'
					AND meta_value = '1')
				
				ORDER BY  t1.order_id DESC LIMIT 0,$ph_get_asset_bookings_limit";

			} else {

				$query_post = "SELECT meta_key, meta_value, post_id as order_item_id, 'from_post_meta' as meta_type
					FROM {$wpdb->prefix}postmeta AS t1
					WHERE t1.post_id
					IN (
						SELECT post_id
						FROM  {$wpdb->prefix}postmeta
						WHERE (meta_key = 'asset_id' or meta_key = 'buffer_asset_id')
						AND meta_value = '$this->id'
					)AND ( 
						meta_key = 'From'
						OR meta_key = 'To'
						OR meta_key = 'person_as_booking'
						OR meta_key = 'Number of persons'
						OR meta_key = '_ph_book_to_date_with_night'
						Or meta_key = 'Buffer_before_From'
						Or meta_key = 'Buffer_before_To'
						Or meta_key = 'Buffer_after_From'
						Or meta_key = 'Buffer_after_To'
					)
					AND t1.post_id 
					NOT IN (select post_id
						from {$wpdb->prefix}postmeta
						where meta_key='ph_canceled'
						AND meta_value = '1')
					
					ORDER BY  t1.post_id DESC LIMIT 0,$ph_get_asset_bookings_limit";
			}

			$freezed = $wpdb->get_results( $query_post, OBJECT );
		}
		$booked_array = array_merge( $booked, $freezed );
		$processed = array();
		$canceled = array();
		
		// error_log(print_r($booked_array, 1));
		foreach ($booked_array as $key => $value) {
			if( $value->meta_key == 'From' && ! empty($value->meta_value) ){
				$from_date = substr($value->meta_value, 0, 10);
				$processed[ $value->order_item_id ]['from'] = ph_maybe_unserialize($value->meta_value);
				$processed[ $value->order_item_id ]['meta_type'] = $value->meta_type;
			}

			// Handle Per Night Booking
			if( $value->meta_key == '_ph_book_to_date_with_night' && ! empty($value->meta_value) ) {
				$processed[ $value->order_item_id ]['to'] = ph_maybe_unserialize($value->meta_value);
				$processed[ $value->order_item_id ]['meta_type'] = $value->meta_type;
			}

			// Can be set by Per Night Booking
			if( $value->meta_key=='To' && empty($processed[ $value->order_item_id ]['to']) ){
				$processed[ $value->order_item_id ]['to'] = ph_maybe_unserialize($value->meta_value);
			}

			if( $value->meta_key == 'Number of persons' ){
				$number_of_person = substr($value->meta_value, 0, 10);
				$processed[ $value->order_item_id ]['Number of persons'] = $value->meta_value;
				$processed[ $value->order_item_id ]['meta_type'] = $value->meta_type;
			}
			if( $value->meta_key == 'person_as_booking' ){
				$person_as_booking = substr($value->meta_value, 0, 10);
				$processed[ $value->order_item_id ]['person_as_booking'] = $value->meta_value;
				$processed[ $value->order_item_id ]['meta_type'] = $value->meta_type;
			}
			if( $value->meta_key == 'canceled' && $value->meta_value == 'yes' ){
				$canceled[$value->order_item_id] = '';
			}

			if( $value->meta_key == 'Buffer_before_From' && ! empty($value->meta_value) ){
				$processed[ $value->order_item_id ]['Buffer_before_From'] = ph_maybe_unserialize($value->meta_value);
				$processed[ $value->order_item_id ]['meta_type'] = $value->meta_type;
			}
			if( $value->meta_key == 'Buffer_before_To' && ! empty($value->meta_value) ){
				$processed[ $value->order_item_id ]['Buffer_before_To'] = ph_maybe_unserialize($value->meta_value);
			}
			if( $value->meta_key == 'Buffer_after_From' && ! empty($value->meta_value) ){
				$processed[ $value->order_item_id ]['Buffer_after_From'] = ph_maybe_unserialize($value->meta_value);
				$processed[ $value->order_item_id ]['meta_type'] = $value->meta_type;
			}
			if( $value->meta_key == 'Buffer_after_To' && ! empty($value->meta_value) ){
				$processed[ $value->order_item_id ]['Buffer_after_To'] = ph_maybe_unserialize($value->meta_value);
			}
		}
		$eliminated_cancelled =  array_diff_key($processed, $canceled);

		//if TO is missing, concider FROM as TO
		foreach ($eliminated_cancelled as $key => &$value) {
			if( empty($value['to']) && !empty($value['from'])){ // in the case of buffer, index 'from' wil be empty
				$value['to'] = $value['from'];
			}
		}
		
		// error_log(print_r($eliminated_cancelled, 1));
		
		return $eliminated_cancelled;
	}

	/**
	* Get the availability of the given asset for the given date interval
	* @param $from: Start date in format Y-m-d (2018-08-03)
	* @param $to: End date in format Y-m-d (2018-08-03)
	* @return interger
	*/
	public function get_availability($from, $to,$ignore_freezed=false, $interval_period = '', $product_id = ''){
		$bookings_count = $this->ph_get_bookings_count( $from, $to,$ignore_freezed, $interval_period, $product_id);
		$availability 	= $this->max_quantity - $bookings_count;
		$availability	= apply_filters( 'ph_bookings_get_assets_availabibility', $availability, $this->id, $bookings_count, $this->max_quantity, $from, $to );
		return max( 0, $availability );
	}








	/**
     * Get the Next Available Time based on the NON BOOKABLE availability rule
     * If the next available time is for the next day, return '' indicating that no next time is available for the current day
     * @return String
     */
    public function get_next_available_time_based_on_non_bookable_availability_rule($start_time, $end_time, $from_time, $to_time)
    {
        $date_to_return = strtotime($to_time);
        if (strtotime(date('Y-m-d', $start_time)) != strtotime(date('Y-m-d', $date_to_return))) {
            // If the next time available is for the next day,
            // return '' indicating that no next time is available for the current day
            $date_to_return = '';
        }
        // check return date is start time date, else return ''
        return $date_to_return;
    }

    /**
     * Get Next Available Time based on BOOKABLE Availability Rule
     * @return String
     */
    public function get_next_available_time_based_on_bookable_availability_rule($start_time, $end_time, $from_time, $to_time, $interval, $interval_period)
    {
		// If current time slot is already available, no enhancement is required
		if ($this->is_available($start_time, $interval, "", "no", $interval_period)) {
			return $start_time;
		}
        // Time Slot starts before Rule
        if (strtotime(date('Y-m-d H:i', $start_time)) < strtotime($from_time)) {
            if (strtotime($end_time) <= strtotime($to_time)) {
                // Time Slot ends during the Rule
                return strtotime($from_time);
            }
            // Time Slot ends after the Rule
            return strtotime($end_time);
        }
        if (strtotime($end_time) <= strtotime($to_time)) {
            // Time Slot ends during the Rule
            return $start_time;
        }

		// 194516 The slots showing as available when asset is not available
		if ($this->unavailable_default == 'yes') {
			return "";
		}
        // Time Slot ends after the Rule
        return strtotime($end_time);
    }

    /**
     * Check whether the Time Slot falls under any Availability Rule,
     * If so, enhance the Start Time of the Time Slot if required.
     * The first rule is given highest priority
     * @return String
     */
    public function get_next_available_time($start_time, $interval = '', $interval_period='')
    {

        $end_time = date('Y-m-d H:i', strtotime("+$interval $interval_period", $start_time));
        foreach ($this->availabiliy_rules as $key => $rule) {

            if ($rule['availability_type'] == 'custom') {
								$date_from = $rule['from_date'].' 00:00';
								$date_to = $rule['to_date'].' 23:59';
                if (!empty($date_from) && !empty($date_to)) {
                    if (!($start_time < strtotime($date_from) && strtotime($end_time) <= strtotime($date_from))
                        && !($start_time >= strtotime($date_to))) {
                        // If current time slot falls in current rule, return the next available slot corresponding to the current rule.
                        if ($rule['is_bokable'] == 'no') {
                            return $this->get_next_available_time_based_on_non_bookable_availability_rule($start_time, $end_time, $date_from, $date_to, $interval);
                        }
                        return $this->get_next_available_time_based_on_bookable_availability_rule($start_time, $end_time, $date_from, $date_to, $interval, $interval_period);
                    }
                }
            } elseif ($rule['availability_type'] == 'time-all' && !empty($rule['from_time'])) {
                $from_time_current_day = date("Y-m-d " . $rule['from_time'], $start_time);
                $to_time_current_day = date("Y-m-d " . $rule['to_time'], $start_time);
                if (!(strtotime(date('H:i', $start_time)) < strtotime($rule['from_time']) && strtotime(date('H:i', strtotime($end_time))) <= strtotime($rule['from_time']))
                    && !(strtotime(date('H:i', $start_time)) >= strtotime($rule['to_time']))) {
                    // If current time slot falls in current rule, return the next available slot corresponding to the current rule.
                    if ($rule['is_bokable'] == 'no') {
                        return $this->get_next_available_time_based_on_non_bookable_availability_rule($start_time, $end_time, $from_time_current_day, $to_time_current_day, $interval);
                    }
                    return $this->get_next_available_time_based_on_bookable_availability_rule($start_time, $end_time, $from_time_current_day, $to_time_current_day, $interval, $interval_period);
                }

            } elseif (strpos($rule['availability_type'], "time-") !== false && !empty($rule['from_time'])) {
                $day = explode('-', $rule['availability_type']);
                $day = $day[1];
                if (strtolower(date('D', $start_time)) == $day) {
                    if (!(strtotime(date('H:i', $start_time)) < strtotime($rule['from_time']) && strtotime(date('H:i', strtotime($end_time))) <= strtotime($rule['from_time']))
                        && !(strtotime(date('H:i', $start_time)) >= strtotime($rule['to_time']))) {
                        // If current time slot falls in current rule, return the next available slot corresponding to the current rule.
                        $from_time_current_day = date("Y-m-d " . $rule['from_time'], $start_time);
                        $to_time_current_day = date("Y-m-d " . $rule['to_time'], $start_time);
                        if ($rule['is_bokable'] == 'no') {
                            return $this->get_next_available_time_based_on_non_bookable_availability_rule($start_time, $end_time, $from_time_current_day, $to_time_current_day, $interval);
                        }
                        return $this->get_next_available_time_based_on_bookable_availability_rule($start_time, $end_time, $from_time_current_day, $to_time_current_day, $interval, $interval_period);
                    }
                }
            } elseif ($rule['availability_type'] == 'months' && !empty($rule['from_month'])) {
                $range_arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
                if ($this->is_in_range($range_arr, date('n', $start_time), $rule['from_month'], $rule['to_month'])
                    && $this->is_in_range($range_arr, date('n', strtotime($end_time)), $rule['from_month'], $rule['to_month'])) {
                    // If current time slot falls in current rule, return the next available slot corresponding to the current rule.
                    if ($rule['is_bokable'] == 'no') {
                        return '';
                    }
                    return $start_time;
                }
            } elseif ($rule['availability_type'] == 'days' && !empty($rule['from_week_day'])) {
                $range_arr = array(1, 2, 3, 4, 5, 6, 7);
                if ($this->is_in_range($range_arr, date('N', $start_time), $rule['from_week_day'], $rule['to_week_day'])
                    && $this->is_in_range($range_arr, date('N', strtotime($end_time)), $rule['from_week_day'], $rule['to_week_day'])) {
                    // If current time slot falls in current rule, return the next available slot corresponding to the current rule.
                    if ($rule['is_bokable'] == 'no') {
                        return '';
                    }
                    return $start_time;
                }
            } elseif ($rule['availability_type'] == 'date-range-and-time') {
							$date_from  = $rule['from_date_for_date_range_and_time'];
							$date_to  = $rule['to_date_for_date_range_and_time'];
							if (!empty($date_from) && !empty($date_to)) {
								if(($start_time >= strtotime($date_from) && $start_time <= strtotime($date_to))
								|| (strtotime($end_time) >= strtotime($date_from) && strtotime($end_time) <= strtotime($date_to))
								){
									$date_from_hour = explode(" ",$rule['from_date_for_date_range_and_time'])[1];
									$date_to_hour = explode(" ",$rule['to_date_for_date_range_and_time'])[1];
									$start_time_date = explode(" ",date("Y-m-d H:i", $start_time))[0];
									$start_time_date_with_rule = $start_time_date." ".$date_from_hour;
									$end_time_date = explode(" ",$end_time)[0];
                  $end_time_date_with_rule = $end_time_date." ".$date_to_hour;
                  
									if (!($start_time < strtotime($start_time_date_with_rule) && strtotime($end_time) <= strtotime($start_time_date_with_rule))
									&& !($start_time >= strtotime($end_time_date_with_rule))) {
										// If current time slot falls in current rule, return the next available slot corresponding to the current rule.
										if ($rule['is_bokable'] == 'no') {
											return $this->get_next_available_time_based_on_non_bookable_availability_rule($start_time, $end_time, $start_time_date_with_rule, $end_time_date_with_rule, $interval);
										}
										return $this->get_next_available_time_based_on_bookable_availability_rule($start_time, $end_time, $start_time_date_with_rule, $end_time_date_with_rule, $interval, $interval_period);
									}
								}
							}
						}
        }

        return $start_time;
    }

	/**
	* Check availability from availability table
	* @param $calendar_for to check the unavailable by default for time calendars
	* @return Bool
	*/
	public function is_available($start_time, $interval = '', $calendar_for = "", $assets_auto_assign = "no", $interval_period = "")
	{
		// Filter to allow skip availability rules for admin(support Disable Avoid Last Minute Booking For Admin Bookings Addon)
		$ignore_asset_availability_rules = apply_filters( 'ph_bookings_skip_availability_rules_for_admin', false, 'asset' );

		// If the filter returns true, check for availability rules are ignored.
		if ( $ignore_asset_availability_rules ) {

			return true;
		}

		if (!empty($interval) && !empty($interval_period)) {

			$end_time = strtotime("+$interval $interval_period", $start_time);
		} else {
			$end_time = $start_time;
		}
		$default_return = true;
		foreach ($this->availabiliy_rules as $key => $rule) {
			// if($rule['is_bokable'] === 'yes'){
			// 	$default_return = false;
			// }
			if( $rule['availability_type']=='custom' ){
				if( !empty($rule['from_date']) && !empty($rule['to_date']  )
					&& $start_time >= strtotime($rule['from_date'].' 00:00' )
					&& $start_time <= strtotime($rule['to_date'].' 23:59' ) ){
					return $rule['is_bokable'] === 'yes';
				}
			}
			elseif ( $rule['availability_type']=='months' && !empty($rule['from_month']) ) {
				
				$range_arr = array(1,2,3,4,5,6,7,8,9,10,11,12);
				
				if( $this->is_in_range( $range_arr, date( 'n', $start_time), $rule['from_month'], $rule['to_month'] )
					&& $this->is_in_range( $range_arr, date( 'n', $end_time), $rule['from_month'], $rule['to_month']) ){
					return $rule['is_bokable'] === 'yes';
				}
			}
			elseif ( $rule['availability_type']=='days' && !empty($rule['from_week_day']) ) {
				$range_arr = array(1,2,3,4,5,6,7);
				
				if( $this->is_in_range( $range_arr, date( 'N', $start_time), $rule['from_week_day'], $rule['to_week_day'] )
					&& $this->is_in_range( $range_arr, date( 'N', $end_time), $rule['from_week_day'], $rule['to_week_day']) ){
					return $rule['is_bokable'] === 'yes';
				}
			}
 			elseif ( $rule['availability_type']=='time-all' && !empty($rule['from_time'])  ) {

				if ($calendar_for == 'time-picker') {
					return true;
				}
 				if ( strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $start_time ) ) <= strtotime( $rule['to_time'] )
 					&& strtotime( date( 'H:i', $end_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $end_time ) ) < strtotime( $rule['to_time'] ) ){
 					return $rule['is_bokable'] === 'yes';
 				}
 			}
 			elseif ( strpos($rule['availability_type'],"time-") !== false && !empty($rule['from_time'])  ) {
 				$day = explode('-', $rule['availability_type']);
 				$day = $day[1];

				if ($calendar_for == 'time-picker' &&  strtolower(date('D', $start_time)) == $day) {
					return true;
				}
 				if( strtolower( date( 'D', $start_time) ) == $day
 					&& strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] )
 					&& strtotime( date( 'H:i', $end_time ) ) < strtotime( $rule['to_time'] ) ){	
 					return $rule['is_bokable'] === 'yes';
 				}
 			} elseif( $rule['availability_type']=='date-range-and-time' ){

				if ($calendar_for == 'time-picker') {
					return true;
				}
					$date_from  = $rule['from_date_for_date_range_and_time'];
					$date_to  = $rule['to_date_for_date_range_and_time'];
					$date_from_hour = explode(" ",$rule['from_date_for_date_range_and_time'])[1];
					$date_to_hour = explode(" ",$rule['to_date_for_date_range_and_time'])[1];

					if( !empty($date_from) && !empty($date_to) ){
						if( ($start_time >= strtotime($date_from) && $start_time <= strtotime($date_to))
						|| ($end_time >= strtotime($date_from) && $end_time <= strtotime($date_to))
						){

							$start_time_date = explode(" ",date("Y-m-d H:i", $start_time))[0];
							$start_time_date_with_rule = $start_time_date." ".$date_from_hour;
							$end_time_date = explode(" ",date("Y-m-d H:i", $end_time))[0];
							$end_time_date_with_rule = $end_time_date." ".$date_to_hour;

							if($rule['is_bokable'] === 'yes'){
								if( ($start_time >= strtotime($start_time_date_with_rule) && $start_time < strtotime($end_time_date_with_rule))
								&& ($end_time >= strtotime($start_time_date_with_rule) && $end_time < strtotime($end_time_date_with_rule))
								){
									// The slot is valid only if it falls perfectly between the rule timings
									return true;
								}
							}else{
								if( ($start_time >= strtotime($start_time_date_with_rule) && $start_time < strtotime($end_time_date_with_rule))
								|| ($end_time > strtotime($start_time_date_with_rule) && $end_time < strtotime($end_time_date_with_rule))
								){
									// The slot is invalid only if it falls even partially between the rule timings
									return false;
								}
							}
						}


				}

			}
		}
		// Checking if Unavailable by default
		if($this->unavailable_default == 'yes' && $assets_auto_assign == 'no'){
			return false;
		}
		return true;
	}

	/**
	* check if the given value is in given range of the array (Eg: Check if Sunday is in between Monday to Saturday)
	* @param $full_ranges the range array with integer values
	* @param $check_me the value to check if in range
	* @param $lower_range Lower range
	* @param $uppper_range Upper range
	* @return bool
	*/
	private function is_in_range( $full_ranges, $check_me, $lower_range, $uppper_range ){
		if( $lower_range <= $uppper_range ){
			return $check_me >= $lower_range && $check_me <= $uppper_range;
		}else{
			$count = count($full_ranges);
			$new_range_limit = $count - $lower_range + $count;
			$uppper_range += $count;
			if( $check_me < $lower_range ){
				$check_me += $count;
			}

			$available_array = array();
			for( $i=$lower_range; $i<=$uppper_range; $i++ ){
				$available_array[] = $i;
			}

			return in_array($check_me, $available_array);
		}
	}

	private function ph_get_bookings_count( $date, $date_to ,$ignore_freezed=false, $interval_period = '', $product_id=''){

		static $called_count = 0;
		$called_count++;
		$current_interval_period = $interval_period;
		$current_product_id = $product_id;
		if( empty($this->booked_dates) && $this->booked_dates_set == 0)
		{
			if(($interval_period == 'hour' || $interval_period == 'minute'))
			{
				$ph_cache_obj = new phive_booking_cache_manager();
				if(($ph_cache_obj->ph_is_cache_set($this->id) != 'Yes') || (($called_count < 2) && ($ignore_freezed == true)))
				{
					$this->booked_dates = $this->get_all_bookings($ignore_freezed);
					$this->booked_dates_set = 1;
					$ph_cache_obj->ph_set_cache($this->id, $this->booked_dates);
				}
				else
				{
					$this->booked_dates = $ph_cache_obj->ph_get_cache($this->id);

					$this->booked_dates = ( ($this->booked_dates != false) && (!empty($this->booked_dates)) ) ? $this->booked_dates : array();  
				}		
			}
			else
			{
				// error_log('for day or month calendar');
				$this->booked_dates = $this->get_all_bookings($ignore_freezed);
				$this->booked_dates_set = 1;
			}
		}
		
		$found = 0;
		foreach ( $this->booked_dates as $order_item_id => $booked_detail ) {
			//for buffer
			$product_id 		= get_post_meta($order_item_id,'_product_id',1);
			
			if(isset($booked_detail['meta_type']) && $booked_detail['meta_type'] == 'from_order_item_meta') 
			{
				$product_id 		= wc_get_order_item_meta($order_item_id,'_product_id',1);
			}
			$interval_period	= get_post_meta( $product_id, "_phive_book_interval_period", 1 );
			$charge_per_night	= get_post_meta( $product_id, "_phive_book_charge_per_night", 1 );
			$from='';
			$to='';
			
			if (isset($booked_detail['from'])) 
			{
				switch ( strlen($date) ) {
				case 7:
					$from 	= substr($booked_detail['from'], 0, 7);
					$to 	= isset($booked_detail['to']) ? substr($booked_detail['to'], 0, 7) : substr( $this->generate_order_item_booked_to($order_item_id, $from, $booked_detail['meta_type']), 0, 7);
					break;
				
				case 10:
					$from 	= substr($booked_detail['from'], 0, 10);
					$to 	= isset($booked_detail['to']) ? substr($booked_detail['to'], 0, 10) : substr( $this->generate_order_item_booked_to($order_item_id, $from, $booked_detail['meta_type']), 0, 10 );
					break;
				
				default:
					$from 	= $booked_detail['from'];
					$to 	= (isset($booked_detail['to']) && $booked_detail['to']!=$booked_detail['from']) ? $booked_detail['to'] : $this->generate_order_item_booked_to($order_item_id, $from, $booked_detail['meta_type']);

					// 109945 - Fixed - few blocks of minute product are not blocked since, asset was booked for hour product
					if($interval_period == 'hour' && $current_interval_period == 'minute')
					{
						$booked_product_interval = get_post_meta( $product_id, "_phive_book_interval", 1 );
						if(!empty($current_product_id) && !empty($to))
						{
							$from_obj				= date_create($from);
							$to_obj					= date_create($to);
							$booked_dates_diff		= date_diff($from_obj,$to_obj);
							$booked_dates_diff		= $booked_dates_diff->format("%h");
							if($booked_dates_diff >= $booked_product_interval)
							{
								$current_product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id($current_product_id);
								$current_interval	= get_post_meta( $current_product_id, "_phive_book_interval", 1 );
								$to = date( 'Y-m-d H:i', strtotime( "+$booked_product_interval $interval_period", strtotime($to) ) );
								$to = date( 'Y-m-d H:i', strtotime( "-$current_interval $current_interval_period", strtotime($to) ) );
							}
						}
					}
					break;
				}
			}
			$Buffer_before_From='';
			$Buffer_before_To='';
			if (isset($booked_detail['Buffer_before_From']) && isset($booked_detail['Buffer_before_To'])) 
			{
				switch ( strlen($date) ) {
				case 7:
					$Buffer_before_From 	= substr($booked_detail['Buffer_before_From'], 0, 7);
					$Buffer_before_To 	= substr($booked_detail['Buffer_before_To'], 0, 7);
					break;
				
				case 10:
					$Buffer_before_From 	= substr($booked_detail['Buffer_before_From'], 0, 10);
					$Buffer_before_To 	= substr($booked_detail['Buffer_before_To'], 0, 10);
					break;
				
				default:
					$Buffer_before_From 	= $booked_detail['Buffer_before_From'];
					$Buffer_before_To 	= $booked_detail['Buffer_before_To'];
					break;
				}
			}


			$Buffer_after_From='';
			$Buffer_after_To='';
			if (isset($booked_detail['Buffer_after_From']) && isset($booked_detail['Buffer_after_To'])) 
			{
				switch ( strlen($date) ) {
				case 7:
					$Buffer_after_From 	= substr($booked_detail['Buffer_after_From'], 0, 7);
					$Buffer_after_To 	= substr($booked_detail['Buffer_after_To'], 0, 7);
					break;
				
				case 10:
					$Buffer_after_From 	= substr($booked_detail['Buffer_after_From'], 0, 10);
					$Buffer_after_To 	= substr($booked_detail['Buffer_after_To'], 0, 10);
					break;
				
				default:
					$Buffer_after_From 	= $booked_detail['Buffer_after_From'];
					$Buffer_after_To 	= $booked_detail['Buffer_after_To'];
					break;
				}
				if($charge_per_night == 'yes' && ($interval_period == 'day')){
					$Buffer_after_From = date ( "Y-m-d", strtotime( "-1 day", strtotime($Buffer_after_From) ) );
					$Buffer_after_To = date ( "Y-m-d", strtotime( "-1 day", strtotime($Buffer_after_To) ) );
				}
			}
			
			if ($interval_period == 'day' && strlen($date) != '7') 
			{
				$from = (!empty($from) && strlen($from) == 10) ? $from." 00:00" : $from;
				$to = (!empty($to) && strlen($to) == 10) ? $to." 23:59" : $to;
			}

			// 138547
			$to = apply_filters('ph_modify_to_date_booked_for_asset_availability_calculation', $to, $from, $product_id, $this->id, $current_product_id, $booked_detail);

			if( (!empty($from) && !empty($to) && strtotime($date) >= strtotime($from) && strtotime($date) <= strtotime($to) ) || (strtotime($date) <= strtotime($from) && strtotime($from) < strtotime($date_to) ) || (strtotime($date) <= strtotime($to) && strtotime($to) < strtotime($date_to) ) )
			{
				$person_as_booking = !empty($booked_detail['person_as_booking']) ? ph_maybe_unserialize( $booked_detail['person_as_booking'] ) : '';
				// 110259 - Fixed - Showing warning message in customer's site in case Number of persons is empty
				if( $person_as_booking == 'yes' && isset($booked_detail['Number of persons']) && is_numeric($booked_detail['Number of persons'])){
					$found += $booked_detail['Number of persons'];
				}else{
					$found++;

				}
			}// buffer should get subtracted from asset's value
			if(!empty($Buffer_before_From) && !empty($Buffer_before_To) &&  strtotime($date) >= strtotime($Buffer_before_From) && strtotime($date) <= strtotime($Buffer_before_To)){
				if(isset($booked_detail['person_as_booking'])){
					$person_as_booking = maybe_unserialize($booked_detail['person_as_booking']);
					if( !empty($person_as_booking[0]) && ($person_as_booking[0] == 'yes') && isset($booked_detail['Number of persons']) && is_numeric($booked_detail['Number of persons'])){
						$found += apply_filters('ph_bookings_check_participant_adjustment',$booked_detail['Number of persons'],$booked_detail,$product_id);
					}
					else{
						$found++;
					}
				}else{
					$found++;
				}
			}
			elseif(strtotime($date) >= strtotime($Buffer_after_From) && strtotime($date) <= strtotime($Buffer_after_To) && $interval_period != 'minute')
				{
					if(isset($booked_detail['person_as_booking'])){
						$person_as_booking = maybe_unserialize($booked_detail['person_as_booking']);
						if( !empty($person_as_booking[0]) && ($person_as_booking[0] == 'yes') && isset($booked_detail['Number of persons']) && is_numeric($booked_detail['Number of persons'])){
							
								$found += apply_filters('ph_bookings_check_participant_adjustment',$booked_detail['Number of persons'],$booked_detail,$product_id);
						}	
						else{
							$found++;
						}
					}else{
						$found++;
					}
			}
			elseif(strtotime($date) >= strtotime($Buffer_after_From) && strtotime($date) < strtotime($Buffer_after_To) && $interval_period == 'minute')
				{
					if(isset($booked_detail['person_as_booking'])){
						$person_as_booking = maybe_unserialize($booked_detail['person_as_booking']);
						if( !empty($person_as_booking[0]) && ($person_as_booking[0] == 'yes') && isset($booked_detail['Number of persons']) && is_numeric($booked_detail['Number of persons'])){
							
								$found += apply_filters('ph_bookings_check_participant_adjustment',$booked_detail['Number of persons'],$booked_detail,$product_id);
						}	
						else{
							$found++;
						}
					}else{
						$found++;
					}
			}
		}
		return $found;
		
	}

	private function generate_order_item_booked_to($order_item_id, $from, $meta_type){
		// $line_item 			= new WC_Order_Item_Product($order_item_id);
		$product_id = '';
		if ($meta_type == 'from_post_meta') 
		{
			$product_id 		= get_post_meta($order_item_id,'_product_id',1);
		}
		elseif ($meta_type == 'from_order_item_meta') 
		{
			$product_id 		= wc_get_order_item_meta($order_item_id,'_product_id',1);
		}
		$interval_period	= get_post_meta( $product_id, "_phive_book_interval_period", 1 );
		$interval			= get_post_meta( $product_id, "_phive_book_interval", 1 );

		return date( 'Y-m-d H:i', strtotime( "+$interval $interval_period", strtotime($from) ) -1 ); //Substracting 1 second for not matching with starting time of next slot
	}

	private function set_asset_max_quantity(){
		$rules 				= ( isset( $this->settings['_phive_booking_assets']) && !empty($this->settings['_phive_booking_assets']) )? $this->settings['_phive_booking_assets']: array();
		$this->max_quantity = isset($rules[$this->id]['ph_booking_asset_quantity']) ? (float) $rules[$this->id]['ph_booking_asset_quantity'] : null;
	}

	private function set_asset_availability_rules(){
		$this->availabiliy_rules 	= isset($this->settings['_phive_booking_assets_availability']) ? $this->settings['_phive_booking_assets_availability'] : array();
		$this->unavailable_default		= isset($this->settings['_phive_asset_un_availability']) ? $this->settings['_phive_asset_un_availability'] : 'no';
		foreach ($this->availabiliy_rules as $key => $rule) {
			if( $rule['availability_asset_id'] != $this->id ){
				unset($this->availabiliy_rules[$key]);
			}
		}
	}
}