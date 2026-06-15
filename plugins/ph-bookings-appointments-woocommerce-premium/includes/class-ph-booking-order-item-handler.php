<?php

class PH_Bookings_Order_Item_Handler {

	/**
	 * Get resource details.
	 *
	 * @param array $args
	 *
	 * @return array $resources
	 */
	public static function ph_get_resources($args) {

		$resources = array();
		$resource_rules = $args['product']->get_meta('_phive_booking_resources_pricing_rules');

		foreach ($resource_rules as $rule) {

			$status = $args['item']->get_meta($rule['ph_booking_resources_name']);

			if (empty($status)) {
				continue;
			}

			$resources[] = array(
				$rule['ph_booking_resources_name'] => $status
			);
		}

		return $resources;
	}

	/**
	 * Get participant details.
	 *
	 * @param array $args
	 *
	 * @return array $participants
	 */
	public static function ph_get_participants($args) {

		$participants = array();
		$participant_rules = $args['product']->get_meta('_phive_booking_persons_pricing_rules');

		if (!is_array($participant_rules) || empty($participant_rules)) {
			return $participants;
		}

		foreach ($participant_rules as $rule) {
			$count = $args['item']->get_meta($rule['ph_booking_persons_rule_type']);

			if (empty($count)) {
				continue;
			}

			$participants[] = array(
				$rule['ph_booking_persons_rule_type'] => $count
			);
		}

		return $participants;
	}

	/**
	 * Get asset details.
	 *
	 * @param array $args
	 *
	 * @return array $assets
	 */
	public static function ph_get_assets($args) {

		$assets				= array();
		$selected_assets 	= $args['item']->get_meta('Assets');

		if (empty($selected_assets) || !is_array($selected_assets)) {
			return $assets;
		}

		$asset_label	= $args['product']->get_meta('_phive_booking_assets_label');
		$asset_label	= !empty($asset_label) ? $asset_label : __('Asset', 'bookings-and-appointments-for-woocommerce');
		$asset_settings	= get_option('ph_booking_settings_assets', array());

		if (!empty($asset_settings) && !empty($asset_settings['_phive_booking_assets'][current($selected_assets)])) {
			$assets[] = array(
				$asset_label => $asset_settings['_phive_booking_assets'][current($selected_assets)]['ph_booking_asset_name']
			);
		}

		return $assets;
	}

	/**
	 * Get booking status.
	 *
	 * @param array $args
	 *
	 * @return string $booking_status
	 */
	public static function ph_get_booking_status($args) {

		$booking_status = ph_maybe_unserialize($args['item']->get_meta('booking_status'));
		$booking_status = empty($booking_status)
			? __('Order Placed', 'bookings-and-appointments-for-woocommerce')
			: $booking_status;

		return $booking_status;
	}

	/**
	 * Get booking cost.
	 *
	 * @param array $args
	 *
	 * @return string mixed.
	 */
	public static function ph_get_booking_cost($args) {
		$booking_cost = ph_maybe_unserialize($args['item']->get_meta('Cost'));
		$booking_cost = !empty($booking_cost) ? $booking_cost : 0;

		return $booking_cost;
	}

	/**
	 * Get booking notes.
	 *
	 * @param array $args
	 *
	 * @return string $booking_notes
	 */
	public static function ph_get_booking_notes($args) {

		$booking_notes = '';

		$additional_notes_label = get_post_meta($args['product']->get_id(), '_phive_additional_notes_label', true);

		if (empty($additional_notes_label)) {
			return $booking_notes;
		}

		$booking_notes = $args['item']->get_meta($additional_notes_label, true);

		return $booking_notes;
	}

	/**
	 * Get booked dates.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function ph_get_booked_date($args) {

		$from 	= ph_maybe_unserialize($args['item']->get_meta('From'));
		$to		= ph_maybe_unserialize($args['item']->get_meta('To'));

		// Setting ending, same as From, when To is empty.
		$to = empty($to) ? $from : $to;

		$interval_details 	= $args['item']->get_meta('_phive_booking_product_interval_details', true);
		$interval_format	= is_array($interval_details) && isset($interval_details['interval_format'])
			? $interval_details['interval_format']
			: $args['product']->get_interval_period();

		$interval      		= is_array($interval_details) && isset($interval_details['interval'])
			? $interval_details['interval']
			: $args['product']->get_interval();

		if (!in_array($interval_format, array('day', 'month'))) {
			$to = date('Y-m-d H:i', strtotime("+$interval $interval_format", strtotime($to)));
		} else {
			$to = date('Y-m-d H:i', strtotime("+1 day", strtotime($to)));
		}

		return array(
			'from'	=> $from,
			'to'	=> $to
		);
	}

	/**
	 * Get item location.
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function ph_get_location($args) {
		return $args['item']->get_meta('Location');
	}
}
new PH_Bookings_Order_Item_Handler();
