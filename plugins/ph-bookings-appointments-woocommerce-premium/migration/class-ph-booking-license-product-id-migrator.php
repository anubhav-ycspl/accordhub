<?php
/**
 * Handles migration of License Product ID for Bookings and Appointments For WooCommerce Premium.
 *
 * @package bookings-and-appointments-for-woocommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PH_Booking_License_Product_ID_Migrator
 *
 * Migrates old license product ID–based options to the new format.
 */
class PH_Booking_License_Product_ID_Migrator {

	/**
	 * Constructor.
	 *
	 * Triggers migration if not already performed and the constant is available.
	 */
	public function __construct() {

		$license_product_id_migrated = get_option( 'ph_booking_license_product_id_migrated', false );

		if ( ! $license_product_id_migrated ) {
			$this->ph_migrate_license_product_id();
		}
	}

	/**
	 * Performs the actual license product ID migration.
	 *
	 * Copies existing license-related options using the new product ID key structure.
	 *
	 * @return void
	 */
	private function ph_migrate_license_product_id() {

		$new_product_id = PH_Bookings_Config::PH_LICENSE_PRODUCT_ID;

		// Derive old product ID from plugin folder name.
		$plugin_dir     = dirname( untrailingslashit( plugin_basename( PH_BOOKINGS_PLUGIN_FILE ) ) );
		$old_product_id = strtolower( str_replace( array( ' ', '_', '&', '?', '-' ), '_', $plugin_dir ) );

		$new_data_key = 'wc_am_client_' . $new_product_id;
		$old_data_key = 'wc_am_client_' . $old_product_id;

		// Migrate main license data key — default to an empty array if old key not found.
		// Handle API key renaming for the main data key.
		$data_key_value = get_option( $old_data_key, array() );
		$old_api_key    = $old_data_key . '_api_key';
		$new_api_key    = $new_data_key . '_api_key';

		if ( ! empty( $data_key_value[ $old_api_key ] ) ) {
			$data_key_value[ $new_api_key ] = $data_key_value[ $old_api_key ];
			unset( $data_key_value[ $old_api_key ] );
			update_option( $new_data_key, $data_key_value );
			delete_option( $old_data_key );
		}

		// Migrate activation instance.
		self::ph_maybe_copy_option( $old_data_key . '_instance', $new_data_key . '_instance' );

		// Migrate activation status.
		self::ph_maybe_copy_option( $old_data_key . '_activated', $new_data_key . '_activated' );

		// Migrate deactivate checkbox state.
		self::ph_maybe_copy_option( $old_data_key . '_deactivate_checkbox', $new_data_key . '_deactivate_checkbox' );

		update_option( 'ph_booking_license_product_id_migrated', true );
	}

	/**
	 * Safely copies an option value if it exists, or uses a provided default.
	 *
	 * @param string $old_key  The old option key.
	 * @param string $new_key  The new option key.
	 * @return void
	 */
	private static function ph_maybe_copy_option( $old_key, $new_key ) {
		$value = get_option( $old_key, null );

		if ( null !== $value ) {
			update_option( $new_key, $value );
			delete_option( $old_key );
		}
	}
}

new PH_Booking_License_Product_ID_Migrator();
