<?php

/**
 * API Invoker.
 *
 * @package bookings-and-appointments-for-woocommerce
 */

defined('ABSPATH') || exit;

class PH_Bookings_API_Invoker {

	/**
	 * Makes API call using wp_remote_request.
	 *
	 * @param array $params The parameters for the API call.
	 *
	 * @return array|WP_Error The response or Wp_Error on failure.
	 */
	public static function ph_make_request($params = array()) {

		$defaults = array(
			'url'     => '',
			'method'  => 'GET',
			'headers' => array(),
			'body'    => array(),
			'options' => array(),
		);

		// Merge default parameters with provided parameters
		$args = wp_parse_args($params, $defaults);

		// Handle form data encoding for x-www-form-urlencoded
		if (isset($args['headers']['Content-Type']) && 'application/x-www-form-urlencoded' === $args['headers']['Content-Type']) {
			$args['body'] = http_build_query($args['body']);
		}

		// Make the API call
		$response = wp_remote_request($args['url'], array(
			'method'  => $args['method'],
			'headers' => $args['headers'],
			'body'    => $args['body'],
		) + $args['options']);

		return $response;
	}
}
