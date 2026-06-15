<?php

defined('ABSPATH') || exit;

class PH_Bookings_Calendar_Color_Settings_Migration {

	/**
	 * @var string $migration_meta_key
	 */
	private static $migration_meta_key = 'ph_bookings_calendar_2_color_migration_status';

	/**
	 * @var string $calendar_color_meta
	 */
	private static $calendar_color_meta = 'ph_booking_settings_calendar_color';

	/**
	 * @var array $calendar settings
	 */
	private static $color_settings;

	/**
	 * @var array $color_keys
	 */
	private static $color_keys;

	/**
	 * PH_Bookings_Calendar_Color_Settings_Migration constructor
	 */
	public function __construct() {

		// Return if migration is not required
		if (get_option(self::$migration_meta_key, false)) {
			return;
		}

		if (empty(self::$color_settings)) {
			self::$color_settings = get_option(self::$calendar_color_meta, array());
		}

		self::$color_keys = array(
			'primary_bg_color',
			'price_box_bg_color',
			'price_box_text_color',
			'text_color',
			'booked_block_color',
			'hover_bg_color',
			'book_now_bg_color_design_2',
			'book_now_text_color_design_2'
		);

		$this->migrate_calendar_design_settings();
	}

	/**
	 * Migrate calendar design settings
	 */
	public function migrate_calendar_design_settings() {

		foreach (self::$color_keys as $key) {

			if (
				!$this->is_calendar_design2_settings($key)
				|| !$this->check_if_migration_required(self::$color_settings[$key])
			) {
				continue;
			}

			self::$color_settings[$key] = '#' . self::$color_settings[$key];
		}

		// Update settings once migrated
		update_option(self::$calendar_color_meta, self::$color_settings);
		update_option(self::$migration_meta_key, true);
	}

	/**
	 * Check if migration is required
	 *
	 * @param string $color_code
	 * @return bool
	 */
	private function check_if_migration_required($color_code) {
		return (strpos($color_code, '#') !== 0);
	}

	/**
	 * Check if current settings is of calendar design 2
	 *
	 * @param string $key
	 * @return bool
	 */
	private function is_calendar_design2_settings($key) {
		return array_key_exists($key, self::$color_settings);
	}
}
new PH_Bookings_Calendar_Color_Settings_Migration();
