<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ph_Bookings_Plugin_Language_Support' ) ) {
	class Ph_Bookings_Plugin_Language_Support {

		public function __construct() {
			$this->init();

			add_filter( 'ph_customize_year_format', array( $this, 'ph_customize_year_in_booking_calendar' ), 10, 1 );
		}

		public static function init() {
			$canceled          = __( 'canceled', 'bookings-and-appointments-for-woocommerce' );
			$yes               = __( 'yes', 'bookings-and-appointments-for-woocommerce' );
			$no                = __( 'no', 'bookings-and-appointments-for-woocommerce' );
			$Number_of_persons = __( 'Number of persons', 'bookings-and-appointments-for-woocommerce' );
			$Order             = __( 'Order', 'bookings-and-appointments-for-woocommerce' );
			$un_paid           = __( 'un-paid', 'bookings-and-appointments-for-woocommerce' );
			$Confirmed         = __( 'Confirmed', 'bookings-and-appointments-for-woocommerce' );
			$confirmed         = __( 'confirmed', 'bookings-and-appointments-for-woocommerce' );
			$cancelled         = __( 'cancelled', 'bookings-and-appointments-for-woocommerce' );
		}

		public function ph_customize_year_in_booking_calendar( $year_format ) {

			$jp_timezones = array( 'Asia/Tokyo', '+09:00' );

			if ( in_array( wp_timezone_string(), $jp_timezones ) ) {

				$year_format = 'Y年';
			}

			return apply_filters( 'ph_customized_year_in_booking_calendar', $year_format );
		}
	}
	new Ph_Bookings_Plugin_Language_Support();
}
