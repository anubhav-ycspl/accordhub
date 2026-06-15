<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action('admin_init','ph_bookings_remove_duplicated_data_from_availability_table');

if (!function_exists('ph_bookings_remove_duplicated_data_from_availability_table')) {

	/**
	 * Remove the duplicated data from ph availability table
	 * Issue: with version: 3.0.7  thank you page reload will add the data of the order again to the availability table
	 * @since 3.1.9
	 */
	function ph_bookings_remove_duplicated_data_from_availability_table()
	{
        if(get_option('ph_bookings_remove_duplicated_data_from_availability_table','yes') != 'yes'){
            return;
        }
		global $wpdb;
		$table_name = $wpdb->prefix . 'ph_bookings_availability_calculation_data';
		$orders_data = $wpdb->get_results("SELECT * FROM $table_name ",ARRAY_A);
		$duplicate_order_item_ids = [];
		foreach ($orders_data as $order_data) {
			foreach ($orders_data as $order_data1) {
				if (
					$order_data['sno'] != $order_data1['sno'] &&
					$order_data['order_id'] == $order_data1['order_id'] &&
					$order_data['order_item_id'] == $order_data1['order_item_id'] &&
					$order_data['product_id'] == $order_data1['product_id'] &&
					$order_data['booked_date'] == $order_data1['booked_date'] &&
					$order_data['booked_date_end'] == $order_data1['booked_date_end'] &&
					$order_data['asset_id'] == $order_data1['asset_id'] &&
					$order_data['participant_count'] == $order_data1['participant_count'] &&
					$order_data['participant_as_booking'] == $order_data1['participant_as_booking'] &&
					$order_data['charge_per_night'] == $order_data1['charge_per_night'] &&
					$order_data['booking_interval_type'] == $order_data1['booking_interval_type'] &&
					$order_data['booking_interval'] == $order_data1['booking_interval'] &&
					$order_data['booked_date_type'] == $order_data1['booked_date_type'] &&
					$order_data['booking_type'] == $order_data1['booking_type'] &&
					$order_data['booking_status'] == $order_data1['booking_status'] &&
					$order_data['woocommerce_order_status'] == $order_data1['woocommerce_order_status'] &&
					$order_data['additional_data'] == $order_data1['additional_data'] &&
					$order_data['participant_detail'] == $order_data1['participant_detail'] &&
					$order_data['resource_detail'] == $order_data1['resource_detail']
				) {
					$duplicate_order_item_ids[$order_data1['order_id']][] = $order_data1['order_item_id'];
				}
			}
		}
		foreach ($duplicate_order_item_ids as $key => $order_item_ids) {
			$duplicate_item_ids = [];
			foreach (array_unique($order_item_ids) as $order_item_id) {
				$duplicate_item_ids[$order_item_id] = $key;
			}
			do_action('ph_booking_order_items_modified', $duplicate_item_ids, $key, '');
		}
		update_option('ph_bookings_remove_duplicated_data_from_availability_table', "no");
	}
}
