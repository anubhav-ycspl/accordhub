<?php
if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
}	



if( !class_exists('ph_bookings_time_zone_conversion') ) {
    class ph_bookings_time_zone_conversion {

        public function __construct(){
			add_action( 'wp_enqueue_scripts', array( $this, 'phive_time_zone_addon_scripts' ) );	
        }

		public function phive_time_zone_addon_scripts() {
			// 102180 - load scripts only on plugin specific pages
			global $post;
			$short_code_exists = 0;
			if(is_object($post))
			{
				$short_code_exists = ( ! empty( $post->post_content ) && strstr( $post->post_content, '[product_page' ) ) ? 1 : 0;
			}
			if( (function_exists('is_woocommerce') && is_woocommerce()) || 
				(function_exists('is_product') && is_product()) ||
				(function_exists('is_cart') && is_cart()) ||
				(function_exists('is_checkout') && is_checkout()) ||
				$short_code_exists
			)
			{
				$display_settings=get_option('ph_bookings_display_settigns');
				
				$booking_end_time_display=(isset($display_settings['booking_end_time_display']) && $display_settings['booking_end_time_display']=='no')?false:true;
				
				wp_enqueue_script( 'ph_addon_script', plugins_url( '/resources/js/ph-time-zone-conversion-addon.js', PH_BOOKINGS_PLUGIN_FILE ), array( 'jquery' ) );
				$localization_arr = array(
					'ajaxurl' 	=> admin_url( 'admin-ajax.php' ),
					'security' 	=> wp_create_nonce( 'phive_change_product_price' ),
					'display_end_time'		 =>  apply_filters('ph_bookings_display_booking_end_time',$booking_end_time_display)
				);
				wp_localize_script( 'ph_addon_script', 'ph_addon_script', $localization_arr);
			}
		}		
    }
new ph_bookings_time_zone_conversion();
    
}