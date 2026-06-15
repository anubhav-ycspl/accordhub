<?php
if ( ! class_exists( 'Ph_Bookings_Month_Picker_Addon' ) ) {
	class Ph_Bookings_Month_Picker_Addon {
		public function __construct() {
			add_action( 'wp_footer', array( $this, 'phive_booking_scripts_month_picker' ) );
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'addDiv_beforeCart' ) );
			add_filter( 'admin_enqueue_scripts', array( $this, 'phive_booking_scripts_month_picker' ) );
		}

		function phive_booking_scripts_month_picker() {
			// 102180 - load scripts only on plugin specific pages
			global $post;
			$short_code_exists = 0;
			if ( is_object( $post ) ) {
				$short_code_exists = ( ! empty( $post->post_content ) && strstr( $post->post_content, '[product_page' ) ) ? 1 : 0;
			}
			if ( ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) ||
				( function_exists( 'is_product' ) && is_product() ) ||
				( function_exists( 'is_cart' ) && is_cart() ) ||
				( function_exists( 'is_checkout' ) && is_checkout() ) ||
				( isset( $_GET['page'] ) && ( $_GET['page'] == 'add-booking' ) ) ||
				$short_code_exists
			) {
				wp_enqueue_script( 'ph_booking_month_picker', plugins_url( 'resources/js/ph-booking-month-picker.js', PH_BOOKINGS_PLUGIN_FILE ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );
				wp_localize_script( 'ph_booking_month_picker', 'ph_booking_month_picker_date', $this->phive_get_string_translation_arr() );
			}
		}
		public function addDiv_beforeCart() {
				$ph_calendar_color       = get_option( 'ph_booking_settings_calendar_color' );
				$ph_calendar_month_color = isset( $ph_calendar_color['ph_calendar_month_color'] ) && ! empty( $ph_calendar_color['ph_calendar_month_color'] ) ? $ph_calendar_color['ph_calendar_month_color'] : '#539bbe';
				$ph_calendar_design      = ( isset( $ph_calendar_color['ph_calendar_design'] ) && ! empty( $ph_calendar_color['ph_calendar_design'] ) ) ? $ph_calendar_color['ph_calendar_design'] : 1; // default legacy design will display
			if ( $ph_calendar_design == 2 ) {
				$primary_bg_color = ( isset( $ph_calendar_color['primary_bg_color'] ) && ! empty( $ph_calendar_color['primary_bg_color'] ) ) ? $ph_calendar_color['primary_bg_color'] : '1791ce';
				$text_color       = ( isset( $ph_calendar_color['text_color'] ) && ! empty( $ph_calendar_color['text_color'] ) ) ? $ph_calendar_color['text_color'] : 'fff';
				?>
					<style type="text/css">
						.booking_month
						{
							width: max-content !important;
							background-color: #<?php echo "$primary_bg_color"; ?> !important;
							color: #<?php echo $text_color; ?> !important;
							border: none !important;
							margin-left: auto;
							margin-right: auto;
							-webkit-appearance: menulist !important;
						}
						.callender-month,.callender-year{
							width: 0px !important;
						}
					</style>
				<?php
			} else {

				?>

					<style type="text/css">
						.booking_month
						{
							width: max-content !important;
						<?php
							$ph_calendar_month_color  = ( isset( $ph_calendar_month_color ) && ! empty( $ph_calendar_month_color ) ) ? $ph_calendar_month_color : '#539bbe';
						$ph_calendar_month_text_color = ! empty( $ph_calendar_color['ph_calendar_month_text_color'] ) ? $ph_calendar_color['ph_calendar_month_text_color'] : '#ffffff';
						?>
							background: <?php echo "$ph_calendar_month_color"; ?> !important;
							color: <?php echo $ph_calendar_month_text_color; ?> !important;
							border: none !important;
							margin-left: auto;
							margin-right: auto;
							-webkit-appearance: menulist !important;
						}
						.callender-month,.callender-year{
							width: 0px !important;
						}
						
					</style>
				
				<?php
			}
		}
		private function phive_get_string_translation_arr() {
			$booking_date_text = apply_filters( 'ph_booking_pick_booking_date_text', 'Please Pick a Date' );
			$month_texts       = array(
				'months'             => array(
					__( 'January', 'bookings-and-appointments-for-woocommerce' ),
					__( 'February', 'bookings-and-appointments-for-woocommerce' ),
					__( 'March', 'bookings-and-appointments-for-woocommerce' ),
					__( 'April', 'bookings-and-appointments-for-woocommerce' ),
					__( 'May', 'bookings-and-appointments-for-woocommerce' ),
					__( 'June', 'bookings-and-appointments-for-woocommerce' ),
					__( 'July', 'bookings-and-appointments-for-woocommerce' ),
					__( 'August', 'bookings-and-appointments-for-woocommerce' ),
					__( 'September', 'bookings-and-appointments-for-woocommerce' ),
					__( 'October', 'bookings-and-appointments-for-woocommerce' ),
					__( 'November', 'bookings-and-appointments-for-woocommerce' ),
					__( 'December', 'bookings-and-appointments-for-woocommerce' ),
				),
				'months_short'       => array(
					__( 'Jan', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Feb', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Mar', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Apr', 'bookings-and-appointments-for-woocommerce' ),
					__( 'May', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Jun', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Jul', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Aug', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Sep', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Oct', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Nov', 'bookings-and-appointments-for-woocommerce' ),
					__( 'Dec', 'bookings-and-appointments-for-woocommerce' ),
				),
				'Please_Pick_a_Date' => __( $booking_date_text, 'bookings-and-appointments-for-woocommerce' ),
				'ajaxurl'            => admin_url( 'admin-ajax.php' ),
			);
			foreach ( $month_texts['months'] as $key => $value ) {

				$name = 'ph_booking_month_picker_date_' . $value;
				ph_wpml_register_string_for_translation( $name, $value );
				$month_texts['months'][ $key ] = ph_wpml_translate_single_string( $name, $value );
			}
			return $month_texts;
		}
	}
}
new Ph_Bookings_Month_Picker_Addon();