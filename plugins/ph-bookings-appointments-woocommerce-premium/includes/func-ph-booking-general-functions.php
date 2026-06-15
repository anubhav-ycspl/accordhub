<?php
/**
 * Get all booking status
 *
 * @return array
 */
function ph_get_booking_statuses() {
	$order_statuses = array(
		'paid'                  => _x( 'Paid', 'Booking status', 'bookings-and-appointments-for-woocommerce' ),
		'canceled'              => _x( 'Cancelled', 'Booking status', 'bookings-and-appointments-for-woocommerce' ),
		'un-paid'               => _x( 'Un-paid', 'Booking status', 'bookings-and-appointments-for-woocommerce' ),
		'requires-confirmation' => _x( 'Requires Confirmation', 'Booking status', 'bookings-and-appointments-for-woocommerce' ),
		'partially-paid'        => _x( 'Partially Paid', 'Booking status', 'bookings-and-appointments-for-woocommerce' ),
	);
	return $order_statuses;
}

/**
 * Check if given product is bookable
 *
 * @return bool
 */
function ph_is_bookable_product( $product ) {
	// 168526 - Booking Status getting added in woocoomerce subscription product when it was first saved as bookable product
	$product      = is_object( $product ) ? $product : wc_get_product( $product );
	$product_type = '';
	if ( is_object( $product ) ) {
		$product_type = $product->get_type();
	}
	if ( $product_type == 'phive_booking' ) {
		return 1;
	}
	return 0;
}

/**
 * Unserialze if serialized
 *
 * @return mixed value.
 */
function ph_maybe_unserialize( $value ) {
	$unserialized = maybe_unserialize( $value );
	return is_array( $unserialized ) ? $unserialized[0] : $unserialized;
}

/**
 * Convert to strtotime format only if not already in that formate.
 *
 * @return mixed formated string.
 */
function ph_strtotime( $date ) {
	if ( ph_is_valid_date( $date ) ) {
		return strtotime( $date );
	} else {
		return $date;
	}
}

function ph_wp_date( $format, $date = null ) {
	$zone = new DateTimeZone( 'UTC' );

	global $wp_version;

	if ( $format && $date ) {
		$return_date = date_i18n( $format, $date );
		if ( version_compare( $wp_version, '5.3', '>=' ) ) {
			$return_date = wp_date( $format, $date, $zone );
		}
		return $return_date;
	} elseif ( $format && ( ! $date || empty( $date ) ) ) {
		$return_date = date_i18n( $format );
		if ( version_compare( $wp_version, '5.3', '>=' ) ) {
			$return_date = wp_date( $format );
		}
		return $return_date;
	}
	return $date;
}
function ph_get_calendar_design() {
	$ph_calendar_color  = get_option( 'ph_booking_settings_calendar_color' );
	$ph_calendar_design = ( isset( $ph_calendar_color['ph_calendar_design'] ) && ! empty( $ph_calendar_color['ph_calendar_design'] ) ) ? $ph_calendar_color['ph_calendar_design'] : 1;
	return $ph_calendar_design;
}
function ph_get_date_using_date_time_object( $date, $format ) {
	global $wp_version;
	if ( version_compare( $wp_version, '5.3', '>=' ) ) {
		$timezone = wp_timezone();
	} else {
		$timezone = get_option( 'timezone_string' );
		if ( empty( $timezone ) ) {
			$time_offset = get_option( 'gmt_offset' );
			$timezone    = timezone_name_from_abbr( '', $time_offset * 60 * 60, 0 );
		}
		$timezone = new DateTimeZone( $timezone );
	}
	if ( ! empty( $date ) ) {
		$date = new DateTime( $date );
	} else {
		$date = new DateTime();
	}
	$date->setTimezone( $timezone );
	$date = $date->format( $format );
	return $date;
}

/**
 * Check if in strtotime formate
 *
 * @return bool
 */
function ph_is_valid_date( $date ) {
	return strpos( $date, '-' ) !== false;
}

function ph_wc_format_decimal( $cost = 0 ) {
	// removed the decimal rounding part while calculating prices
	$cost = wc_format_decimal( $cost );
	return $cost;
}

// will work for both WPML and Polylang
function ph_wpml_register_string_for_translation( $string_for = '', $value = '' ) {
	if ( $value != '' && $string_for != '' ) {
		$name    = 'Ph_' . $string_for;
		$context = 'bookings-and-appointments-for-woocommerce';
		do_action( 'wpml_register_single_string', $context, $name, $value );
	}
}

// will work for both WPML and Polylang
function ph_wpml_translate_single_string( $string_for = '', $original_value = '' ) {
	if ( $original_value != '' && $string_for != '' ) {
		$name           = 'Ph_' . $string_for;
		$domain         = 'bookings-and-appointments-for-woocommerce';
		$original_value = apply_filters( 'wpml_translate_single_string', $original_value, $domain, $name );
	}
	return $original_value;
}

function ph_display_setting_booked_to_order_and_emails() {
	$display_settings                           = get_option( 'ph_bookings_display_settigns' );
	$booking_end_time_display_cart_order_emails = ( isset( $display_settings['booking_end_time_display_cart_order_emails'] ) && $display_settings['booking_end_time_display_cart_order_emails'] == 'no' ) ? false : true;
	return $booking_end_time_display_cart_order_emails;
}

/**
 * Hides the Asset Details from Front-end
 *
 * @param int $product_id Product Id.
 */
function ph_hide_asset_details_from_frontend( $product_id ) {

	$asset_enabled     = get_post_meta( $product_id, '_phive_booking_assets_enable', 1 );
	$asset_auto_assign = get_post_meta( $product_id, '_phive_booking_assets_auto_assign', 1 );
	$hide_asset        = get_post_meta( $product_id, '_phive_hide_assets', 1 );

	if ( 'yes' == $asset_enabled && 'yes' === $asset_auto_assign && 'yes' === $hide_asset ) {

		return true;
	} else {

		return false;
	}
}

// 103401 - Admin Email Language Fix
function ph_wpml_language_switch_admin_email( $order = '', $user_id = '', $lang_basis = '', $lang = '' ) {
	global $sitepress_active_check;
	global $sitepress;
	$language_code          = '';
	$sitepress_active_check = class_exists( 'SitePress' );
	if ( ! $sitepress_active_check || ! is_object( $sitepress ) ) {
		return;
	}

	switch ( $lang_basis ) {
		case 'order':
			$language_code = ( ! empty( $order ) && is_object( $order ) ) ? $order->get_meta( 'wpml_language' ) : '';
			break;
		case 'admin':
			$admin_locale = get_user_meta( $user_id, 'locale', 1 );
			if ( ! empty( $admin_locale ) ) {
				$language_code = get_user_locale( $user_id );
				$language_code = substr( $language_code, 0, 2 );
			} else {
				$language_code = apply_filters( 'wpml_default_language', null );
			}
			break;
		case 'current':
			$language_code = $lang;
			break;
		case 'default':
		default:
			$language_code = apply_filters( 'wpml_default_language', null );
			break;
	}
	$current_language = apply_filters( 'wpml_current_language', null );
	if ( ! empty( $language_code ) && $current_language != $language_code ) {
		do_action( 'wpml_switch_language', $language_code );
	}
	return $current_language;
}

function ph_map_booking_status_to_name( $original_status = '' ) {
	$map_booking_status_to_name = array(
		'paid'                  => 'Paid',
		'un-paid'               => 'Unpaid',
		'canceled'              => 'Cancelled',
		'requires-confirmation' => 'Requires Confirmation',
		'refunded'              => 'Refunded',
		'partially-paid'        => 'Partially Paid',
		''                      => 'Unpaid',
	);
	return $map_booking_status_to_name[ $original_status ];
}

// woocommerce update - 6.1.0, bookings- 2.2.5
function ph_is_ajax() {
	if ( version_compare( WC_VERSION, '6.1.0', '<' ) ) {
		return is_ajax();
	} else {
		return wp_doing_ajax();
	}
}

if ( ! class_exists( 'Ph_Bookings_General_Functions_Class' ) ) {
	class Ph_Bookings_General_Functions_Class {

		private static $wp_date_format;

		private static $wp_time_format;

		/**
		 * Get WordPress Date Format.
		 */
		public static function get_wp_date_format() {
			! empty( self::$wp_date_format ) || self::$wp_date_format = get_option( 'date_format' );
			return self::$wp_date_format;
		}
		/**
		 * Get WordPress Time Format.
		 */
		public static function get_wp_time_format() {
			! empty( self::$wp_time_format ) || self::$wp_time_format = get_option( 'time_format' );
			return self::$wp_time_format;
		}

		// ticket 112195
		public static function phive_get_date_in_wp_format_month( $format ) {
			switch ( $format ) {
				case 'F j, Y':
					$output_format = 'F, Y';
					break;
				case 'm/d/Y':
				case 'd/m/Y':
					$output_format = 'm/Y';
					break;
				case 'Y/d/m':
				case 'Y/m/d':
						$output_format = 'Y/m';
						break;
				case 'j. F Y':
				case 'j F Y':
					$output_format = 'F Y';
					break;
				case 'm.d.Y':
				case 'd.m.Y':
					$output_format = 'm.Y';
					break;
				case 'm-d-Y':
				case 'd-m-Y':
					$output_format = 'm-Y';
					break;
				case 'Y-m-d':
				default:
					$output_format = 'Y-m';
			}
			return $output_format;
		}
		/**
		 * Format the date
		 */
		public static function phive_get_date_in_wp_format( $input_date, $input_format = '' ) {

			if ( empty( $input_date ) ) {
				return false;
			}

			if ( empty( $input_format ) ) {
				switch ( strlen( $input_date ) ) {

					case 7: // Month calendar
						$input_format = 'Y-m';
						// ticket 112195
						$output_format = self::phive_get_date_in_wp_format_month( self::get_wp_date_format() );
						break;

					case 10: // Day calendar
						$input_format  = 'Y-m-d';
						$output_format = self::get_wp_date_format();
						break;

					case 16: // Time picker
						$input_format  = 'Y-m-d H:i';
						$output_format = self::get_wp_date_format() . ' ' . self::get_wp_time_format();
						break;

					default:
						$input_format  = 'Y-m-d';
						$output_format = 'Y-m-d';
						break;

				}
			}
			$output_date = DateTime::createFromFormat( $input_format, esc_attr( $input_date ) );
			return is_a( $output_date, 'DateTime' ) ? ph_wp_date( $output_format, strtotime( $output_date->format( 'F j, Y H:i:s' ) ) ) : $input_date;
		}

		/**
		 * Get Product Id in default Language.
		 *
		 * @param int $product_id Product Id.
		 * @return int Product Id.
		 */
		public static function get_default_lang_product_id( $product_id ) {
			$wpml_default_lang = apply_filters( 'wpml_default_language', null );
			return apply_filters( 'wpml_object_id', $product_id, 'post', true, $wpml_default_lang );
		}

		/**
		 * Concatenate key value from the given array.
		 * Append new line after each element.
		 *
		 * @param array $data_set
		 * 
		 * @return string $concatenated_string
		 */
		public static function ph_prepare_concatenated_data($data_set) {

			$concatenated_data = '';
			$count = count($data_set);

			foreach ($data_set as $index => $data) {
				$key = key($data);
				$concatenated_data .= $key . ': ' . $data[$key];

				if ($index < $count - 1) {
					$concatenated_data .= "\\n";
				}
			}

			return $concatenated_data;
		}
	}
}
