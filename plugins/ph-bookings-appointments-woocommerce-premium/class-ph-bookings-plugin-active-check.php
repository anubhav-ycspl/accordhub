<?php

if( ! defined('ABSPATH') )	exit;

if( ! class_exists('Ph_Bookings_Plugin_Active_Check') ) {
	class Ph_Bookings_Plugin_Active_Check {

		private static $active_plugins;

		public static function init() {

			self::$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() )
				self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		/**
		 * Check whether plugin is active or not.
		 * @param string $plugin_name Plugin name.
		 * @return boolean
		 */
		public static function plugin_active_check( $plugin_name = null ) {

			if( empty($plugin_name) )	return false;
			if ( ! self::$active_plugins ) self::init();

			return in_array( $plugin_name, self::$active_plugins ) || array_key_exists( $plugin_name, self::$active_plugins );
		}

	}
}
new Ph_Bookings_Plugin_Active_Check();