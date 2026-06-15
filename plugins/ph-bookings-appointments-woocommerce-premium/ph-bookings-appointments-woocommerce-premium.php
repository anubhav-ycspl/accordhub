<?php

/**
 * Plugin Name: Bookings and Appointments For WooCommerce Premium
 *
 * @package      bookings-and-appointments-for-woocommerce
 * @author       PluginHive
 * @copyright    2025 PluginHive
 * @license      GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:             Bookings and Appointments For WooCommerce Premium
 * Plugin URI:              https://www.pluginhive.com/product/woocommerce-booking-and-appointments/
 * Description:             Bookings and Appointments solution for all types of businesses.
 * Version:                 5.2.3
 * Author:                  PluginHive
 * Author URI:              https://www.pluginhive.com/
 * Text Domain:             bookings-and-appointments-for-woocommerce
 * Domain Path:             /i18n/
 * License:                 GPL v2 or later
 * License URI:             https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Requires Plugins:        woocommerce
 * WC requires at least:    3.0.0
 * WC tested up to:         10.3.3
 */

// Define PH_BOOKINGS_PLUGIN_FILE.
if ( ! defined( 'PH_BOOKINGS_PLUGIN_FILE' ) ) {
	define( 'PH_BOOKINGS_PLUGIN_FILE', __FILE__ );
}

// Define PH_BOOKINGS_PLUGIN_VERSION.
if ( ! defined( 'PH_BOOKINGS_PLUGIN_VERSION' ) ) {
	define( 'PH_BOOKINGS_PLUGIN_VERSION', '5.2.3' );
}

if ( ! class_exists( 'PH_Bookings_Config' ) ) {
	include_once 'ph-bookings-config.php';
}

if ( ! class_exists( 'PH_Bookings_API_Invoker' ) ) {
	include_once 'includes/api-handler/class-ph-bookings-api-invoker.php';
}

/**
 * Database version for the Bookings and Appointments plugin.
 *
 * @since 2.2.0
 *
 * ticket-96421
 */
define( 'PH_BOOKINGS_PLUGIN_DB_VERSION', '1.0.1' );

define( 'PH_BOOKINGS_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/includes/templates/' );

/**
 * Plugin activation check.
 */
if ( ! class_exists( 'Ph_Bookings_Plugin_Active_Check' ) ) {
	require_once 'class-ph-bookings-plugin-active-check.php';
}

register_activation_hook( __FILE__, 'phive_booking_pre_activation_check_premium' );

/**
 * Checks if the basic version of the plugin is active before activating the premium version.
 */
function phive_booking_pre_activation_check_premium() {

	// Check if the basic version is active.
	if ( Ph_Bookings_Plugin_Active_Check::plugin_active_check( 'bookings-and-appointments-for-woocommerce/ph-bookings-appointments-woocommerce.php' ) ) {

		deactivate_plugins( basename( __FILE__ ) );

		wp_die(
			esc_html__(
				'Oops! You tried installing the premium version without deactivating and deleting the basic version. Kindly deactivate and delete Bookings and Appointments Woocommerce Extension and then try again',
				'bookings-and-appointments-for-woocommerce'
			),
			'',
			array( 'back_link' => 1 )
		);
	}
}

add_action( 'wp_loaded', 'ph_check_cron_events_scheduled' );

/**
 * Add cron events.
 */
function ph_check_cron_events_scheduled() {

	// Unfreezing Hourly Event.
	if ( ! wp_next_scheduled( 'ph_bookings_unfreezing_hourly_event' ) ) {

		wp_schedule_event( time(), 'hourly', 'ph_bookings_unfreezing_hourly_event' );
	}

	// Booking reminder email.
	$reminder_email = get_option( 'ph_bookings_settings_notifications', array() );

	if ( isset( $reminder_email['reminder_email_enabled'] ) && $reminder_email['reminder_email_enabled'] ) {

		if ( ! wp_next_scheduled( 'ph_bookings_notification_cron' ) ) {

			wp_schedule_event( time(), 'booking_reminder_interval', 'ph_bookings_notification_cron' );
		}
	} else {
		wp_clear_scheduled_hook( 'ph_bookings_notification_cron' );
	}

	// Follow up email.
	$followup_email = get_option( 'ph_booking_follow_up_email', array() );

	if ( isset( $followup_email['followup_email_enabled'] ) && $followup_email['followup_email_enabled'] ) {

		if ( ! wp_next_scheduled( 'ph_bookings_follow_up_email_cron' ) ) {
			wp_schedule_event( time(), 'booking_follow_up_interval', 'ph_bookings_follow_up_email_cron' );
		}
	} else {
		wp_clear_scheduled_hook( 'ph_bookings_follow_up_email_cron' );
	}

	// Google Calendar Two Way Sync.
	$gcalendar_two_way_sync_settings = get_option( 'ph_booking_settings_google_calendar_two_way_sync', 1 );

	if ( isset( $gcalendar_two_way_sync_settings['ph_booking_google_calender_two_way_sync'] ) && '1' === $gcalendar_two_way_sync_settings['ph_booking_google_calender_two_way_sync'] ) {

		if ( ! wp_next_scheduled( 'ph_bookings_two_way_sync_cron' ) ) {
			wp_schedule_event( time(), 'booking_import_interval', 'ph_bookings_two_way_sync_cron' );
		}
	} else {
		wp_clear_scheduled_hook( 'ph_bookings_two_way_sync_cron' );
	}
}

register_deactivation_hook( __FILE__, 'ph_bookings_unfreezing_hourly_event_deactivation' );

/**
 * Clearing the cron events.
 */
function ph_bookings_unfreezing_hourly_event_deactivation() {

	wp_clear_scheduled_hook( 'ph_bookings_unfreezing_hourly_event' );
	wp_clear_scheduled_hook( 'ph_bookings_notification_cron' );
	wp_clear_scheduled_hook( 'ph_bookings_follow_up_email_cron' );
	wp_clear_scheduled_hook( 'ph_bookings_two_way_sync_cron' );
}

// WooCommerce HPOS Compatibility declaration.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

// Declare WooCommerce Blocks compatibility.
add_action( 'before_woocommerce_init', 'ph_booking_blocks_compatibility' );

/**
 * Declare compatibility with WooCommerce Cart & Checkout Blocks.
 *
 * @return void
 */
function ph_booking_blocks_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'cart_checkout_blocks',
			__FILE__,
			true // true = compatible, false = not compatible.
		);
	}
}

if ( Ph_Bookings_Plugin_Active_Check::plugin_active_check( 'woocommerce/woocommerce.php' ) && ! class_exists( 'phive_booking_initialze_premium' ) && ! Ph_Bookings_Plugin_Active_Check::plugin_active_check( 'bookings-and-appointments-for-woocommerce/ph-bookings-appointments-woocommerce.php' ) ) {

	/**
	 * Main class for initializing the PH Bookings and Appointments plugin.
	 */
	class phive_booking_initialze_premium {

		/**
		 * Array of active plugins.
		 *
		 * @var array $active_plugins
		 */
		public $active_plugins;

		/**
		 * Constructor.
		 *
		 * Initializes the plugin by setting up hooks and loading necessary files.
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'register_booking_product_product_type' ) );
			add_action( 'init', array( $this, 'ph_load_plugin_core_dependencies' ) );
			add_action( 'init', array( $this, 'init' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'phive_booking_scripts' ) );
			add_filter( 'admin_enqueue_scripts', array( $this, 'phive_admin_scripts' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'phive_booking_scripts_theme_porto' ), 1005 );

			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

			add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
			add_action( 'ph_bookings_unfreezing_hourly_event', array( $this, 'ph_bookings_unfreezing_hourly' ) );

			add_action( 'after_setup_theme', array( $this, 'ph_booking_search_widgets_files' ) );

			add_action( 'plugins_loaded', array( $this, 'ph_plugin_initialize_loader' ) );
		}

		/**
		 * Load and initialize the PH Bookings Gateway Loader.
		 *
		 * @return void
		 */
		public function ph_plugin_initialize_loader() {

			require_once plugin_dir_path( __FILE__ ) . 'includes/payment/class-ph-bookings-gateway-loader.php';

			new PH_Bookings_Gateway_Loader();
		}

		/**
		 * Includes the Booking Search Widget addon file.
		 *
		 * @return void
		 */
		public function ph_booking_search_widgets_files() {

			include_once 'includes/addons/booking-search-widget/booking-search-widget.php';
		}

		/**
		 * Initialize core dependencies and translations for the Bookings plugin.
		 */
		public function ph_load_plugin_core_dependencies() {
			
			load_plugin_textdomain( 'bookings-and-appointments-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n' );        // Load Plugin Text Translations

			if ( ! class_exists( 'PH_WC_Bookings_Storage_Handler' ) ) {
				include_once 'class-ph-bookings-storage-handler.php';
			}

			// License Product ID migration.
			if ( ! class_exists( 'PH_Booking_License_Product_ID_Migrator' ) ) {
				include_once plugin_dir_path( __FILE__ ) . 'migration/class-ph-booking-license-product-id-migrator.php';
			}

			// Include API Manager.
			if ( ! class_exists( 'PH_Bookings_API_Manager' ) ) {

				include_once 'ph-api-manager/ph_api_manager_bookings.php';
			}

			// License Activation.
			if ( class_exists( 'PH_Bookings_API_Manager' ) ) {

				$product_title = 'Bookings and Appointments For WooCommerce Premium';
				$product_id    = PH_Bookings_Config::PH_LICENSE_PRODUCT_ID;

				$license_menu = array(

					'menu_type'   => 'add_submenu_page',
					'parent_slug' => 'bookings',
					'page_title'  => 'Manage your License',
					'menu_title'  => 'License',
					'capability'  => 'manage_options',
					'position'    => 70,
				);

				$ph_bookings_api_obj = new PH_Bookings_API_Manager( PH_BOOKINGS_PLUGIN_FILE, $product_id, PH_BOOKINGS_PLUGIN_VERSION, 'plugin', PH_Bookings_Config::PH_LICENSE_SERVER_URL, $product_title, 'bookings-and-appointments-for-woocommerce', $license_menu );
			}

			// Manage Database.
			include_once 'includes/admin/class-ph-bookings-database.php';
			include_once 'includes/class-ph-booking-manage-availability-data.php';
			include_once 'includes/backward-compatibility/insert-failed-order-to-availability-table.php';
			include_once 'includes/backward-compatibility/remove-duplicate-data-from-availablity-table.php';
			include_once 'includes/backward-compatibility/ph-bookings-calendar-color-settings-migration.php';

			include_once 'includes/func-ph-booking-general-functions.php';
			include_once 'includes/class-ph-booking-availability-scheduler.php';
			include_once 'includes/class-ph-booking-cart-decorator.php';
			include_once 'includes/class-ph-booking-checkout-decorator.php';
			include_once 'includes/class-ph-booking-ajax-interface.php';
			include_once 'includes/class-ph-booking-addon-integration.php';
			include_once 'includes/class-ph-booking-assets.php';

			include_once 'includes/class-ph-booking-google-calendar.php';

			include_once 'includes/class-ph-booking-email-manager.php';

			include_once 'includes/admin/class-ph-booking-order-manager.php';
			include_once 'includes/admin/class-ph-booking-product-manager.php';
			include_once 'includes/class-ph-cache-manager.php';

			$this->third_party_plugin_support();

			if ( ! class_exists( 'Ph_Bookings_Plugin_Language_Support' ) ) {
				include_once 'class-ph-bookings-translation-support.php';
			}
			$this->init_load_addons();
		}

		/**
		 * Load enabled addons based on plugin display settings.
		 *
		 * @return void
		 */
		public function init_load_addons() {

			$display_settings            = get_option( 'ph_bookings_display_settigns' );
			$month_picker_enable         = isset( $display_settings['month_picker_enable'] ) ? $display_settings['month_picker_enable'] : 'no';
			$start_of_week               = isset( $display_settings['start_of_week'] ) ? $display_settings['start_of_week'] : 1;
			$time_zone_conversion_enable = isset( $display_settings['time_zone_conversion_enable'] ) ? $display_settings['time_zone_conversion_enable'] : 'no';

			// Month Picker Addon.
			if ( 'yes' === $month_picker_enable ) {
				include_once 'includes/addons/ph_bookings_month_picker.php';
			}

			// Start of the week addon.
			if ( '1' !== $start_of_week ) {
				include_once 'includes/addons/ph_bookings_start_of_the_week.php';
			}

			// Time Zone Conversion Addon.
			if ( 'yes' === $time_zone_conversion_enable ) {
				include_once 'includes/addons/ph_bookings_time_zone_conversion.php';
			}

			// Send Email Notifications.
			if ( ! class_exists( 'Ph_Bookings_Send_Email_Notifications' ) ) {
				include_once 'includes/addons/class-ph-bookings-send-email-notifications.php';
			}

			// Send Follow Up Emails.
			if ( ! class_exists( 'Ph_Bookings_Send_Follow_Up_Emails' ) ) {
				include_once 'includes/addons/class-ph-bookings-send-follow-up-emails.php';
			}
		}

		/**
		 * Unfreeze bookings older than 30 minutes by deleting expired booking slot posts.
		 *
		 * @return void
		 */
		public function ph_bookings_unfreezing_hourly() {

			global $wpdb;
			global $wp_version;

			$query_post = "SELECT ID as freezed_id,post_date
			FROM {$wpdb->prefix}posts AS t1
			WHERE t1.post_type = 'booking_slot_freez'";

			$freezed_ids  = $wpdb->get_results( $query_post, ARRAY_A );
			$freezed_idss = array();

			foreach ( $freezed_ids as $key => $product ) {

				$freezed_idss[] = $product['freezed_id'];

				$post_date = date( 'Y-m-d H:i:s', strtotime( $product['post_date'] ) );

				if ( version_compare( $wp_version, '5.3', '>=' ) ) {

					$currentTime = current_datetime();
					$currentTime = $currentTime->format( 'Y-m-d H:i:s' );
				} else {

					$currentTime = current_time( 'Y-m-d H:i:s' );
				}

				$before15mins = strtotime( '-30 minutes', strtotime( $currentTime ) );
				$before15mins = date( 'Y-m-d H:i:s', $before15mins );

				if ( strtotime( $post_date ) < strtotime( $before15mins ) ) {

					$asset_id = get_post_meta( $product['freezed_id'], 'asset_id', 1 );

					if ( $asset_id != '' ) {

						$ph_cache_obj = new phive_booking_cache_manager();
						$ph_cache_obj->ph_unset_cache( $asset_id );
					}

					wp_delete_post( $product['freezed_id'] );
				}
			}
		}

		/**
		 * Initialize plugin features, admin pages, shortcodes, and integrations.
		 *
		 * @return void
		 */
		public function init() {

			include_once 'includes/admin/class-ph-bookings-migration-3-0.php';

			if ( is_admin() ) {
				include_once 'includes/admin/class-ph-booking-admin-pages.php';
			}

			// Export Bookings.
			include_once 'includes/admin/class-ph-booking-export-bookings.php';

			// Bookings Calendar On Custom Page Shortcode.
			add_shortcode( 'ph_bookings_calendar', array( $this, 'ph_bookings_calendar' ) );

			$this->ph_load_icalendar_extensions();

			$this->ph_booking_theme_compatibility();
		}

		/**
		 * Checks if the active theme is 'Flatsome' and includes compatibility support.
		 */
		public function ph_booking_theme_compatibility() {

			// Get the active theme.
			$theme = wp_get_theme();

			// Check if the theme is a valid WP_Theme instance and whether it is 'Flatsome'.
			if ( ! $theme instanceof WP_Theme || 'Flatsome' !== $theme->get( 'Name' ) ) {
				return; // Exit if the active theme is not 'Flatsome'
			}

			// Include the Flatsome theme compatibility file if not already loaded.
			if ( ! class_exists( 'PH_Booking_FlatSome_Theme_Compatibility' ) ) {
				include_once 'includes/theme-support/ph-flatsome-support.php';
			}

			// Enqueue theme-specific scripts for Flatsome.
			add_action( 'wp_enqueue_scripts', array( $this, 'ph_booking_scripts_flatsome_theme' ) );
		}

		/**
		 * Enqueues the necessary JavaScript files for Flatsome theme compatibility.
		 */
		public function ph_booking_scripts_flatsome_theme() {

			// Enqueue the compatibility script for Flatsome.
			wp_enqueue_script(
				'ph_booking_flatsome_script',
				plugins_url( '/resources/js/ph-flatsome-theme-compatibility.js', __FILE__ ),
				array( 'jquery' ),
				PH_BOOKINGS_PLUGIN_VERSION
			);

			// Pass AJAX URL to the script for handling AJAX requests.
			wp_localize_script(
				'ph_booking_flatsome_script',
				'ph_booking_flatsome_locale',
				array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
			);
		}

		/**
		 * Shortcode handler to display the booking calendar for a given product.
		 *
		 * @param array $attr Shortcode attributes. Expects 'id' key with product ID.
		 * @return string Rendered booking calendar HTML if product exists, empty string otherwise.
		 */
		public function ph_bookings_calendar( $attr = array() ) {

			$product_id      = isset( $attr['id'] ) ? $attr['id'] : '';
			$booking_product = wc_get_product( $product_id );

			if ( is_object( $booking_product ) ) {

				global $product;

				$product = $booking_product;

				ob_start();
				include 'includes/frondend/ph-booking-calendar-for-shortcode.php';
				return ob_get_clean();
			}
		}

		/**
		 * Load compatibility files for third-party plugins detected as active.
		 *
		 * @return void
		 */
		public function third_party_plugin_support() {

			if ( empty( $this->active_plugins ) ) {

				$this->active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			}

			// For WooCommerce Currency Switcher.
			if ( in_array( 'woocommerce-multicurrency/woocommerce-multicurrency.php', $this->active_plugins ) ) {
				require_once 'includes/third-party-plugin-support/ph-bookings-woocommerce-currency-switcher.php';
			}

			// For WooCommerce Currency Switcher.
			// if(in_array( 'woo-multi-currency/woo-multi-currency.php', $this->active_plugins) || in_array( 'woo-multi-currency-pro/woo-multi-currency-pro.php', $this->active_plugins) ) {
			// require_once 'includes/third-party-plugin-support/ph-bookings-woocommerce-currency-switcher-woo.php';
			// }

			// For WooCommerce currency switcher based on countries
			if ( in_array( 'woocommerce-product-price-based-on-countries/woocommerce-product-price-based-on-countries.php', $this->active_plugins ) ) {
				require_once 'includes/third-party-plugin-support/ph-bookings-woocommerce-currency-switcher-based-on-countries.php';
			}

			// For WooCommerce Deposits.
			if ( in_array( 'woocommerce-deposits/woocommmerce-deposits.php', $this->active_plugins ) ) {
				require_once 'includes/third-party-plugin-support/ph-bookings-woocommerce-deposits-hide-meta-keys.php';
			}

			// Fox (WOOCS) WooCommerce Currency Switcher.
			if ( in_array( 'woocommerce-currency-switcher/index.php', $this->active_plugins ) ) {
				require_once 'includes/third-party-plugin-support/ph-bookings-woocs-woocommerce-currency-switcher.php';
			}

			// Dokan hide meta keys.
			if ( in_array( 'dokan-lite/dokan.php', $this->active_plugins ) ) {
				require_once 'includes/third-party-plugin-support/ph-bookings-dokan-hide-meta-keys.php';
			}

			// For WooCommerce Product Bundles.
			if ( in_array( 'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php', $this->active_plugins ) ) {
				require_once 'includes/third-party-plugin-support/ph-bookings-aelia-woocommerce-currency-switcher.php';
			}

			// Third party deposit compatiblility.
			if (
				in_array( 'deposits-partial-payments-for-woocommerce/start.php', $this->active_plugins ) ||
				in_array( 'woocommerce-deposits/woocommerce-deposits.php', $this->active_plugins )
			) {
				require_once 'includes/third-party-plugin-support/ph-bookings-deposit-compatiblility.php';
			}

			// WPML WooCommerce multicurrency compatibility.
			if ( in_array( 'woocommerce-multilingual/wpml-woocommerce.php', $this->active_plugins ) ) {
				require_once 'includes/third-party-plugin-support/ph-bookings-wpml-multi-currency.php';
			}

			// For WooCommerce Stripe Gateway.
			if ( in_array( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $this->active_plugins ) ) {

				require_once 'includes/third-party-plugin-support/ph-bookings-express-checkout-with-the-stripe.php';
			}
		}

		/**
		 * Add the Links to pages like Documentation.
		 *
		 * @param array  $links Array of links.
		 * @param string $file Plugin base file name.
		 * @return array
		 */
		public static function plugin_row_meta( $links, $file ) {

			if ( 'ph-bookings-appointments-woocommerce-premium/ph-bookings-appointments-woocommerce-premium.php' === $file ) {

				return array_merge(
					$links,
					array(
						'docs' => '<a href="https://www.pluginhive.com/knowledge-base/setup-guide-woocommerce-bookings-and-appointments-plugin/?utm_source=bookings&utm_medium=plugin_settings">' . __( 'Documentation', 'bookings-and-appointments-for-woocommerce' ) . '</a>',
					)
				);
			}

			return $links;
		}

		/**
		 * Register the custom product type
		 */
		public static function register_booking_product_product_type() {

			include_once 'includes/class-ph-booking-wc-product.php';
		}

		/**
		 * PH Booking Admin Scripts.
		 */
		public function phive_admin_scripts() {

			if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'bookings-settings' ) ) {

				wp_enqueue_style( 'ph_weeler_css', plugins_url( '/resources/css/wheelcolorpicker.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );

				wp_enqueue_script( 'ph_weeler', plugins_url( '/resources/js/jquery.wheelcolorpicker.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );

				wp_enqueue_script( 'ph_select2_dropdown_script', plugins_url( '/resources/js/select2.min.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );

				wp_enqueue_style( 'ph_select2_dropdown_style', plugins_url( '/resources/css/select2.min.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
			}

			wp_enqueue_script( 'jquery-ui-sortable' );

			// 152448 - sortable ui not working in small screens.
			wp_enqueue_script( 'jquery-touch-punch' );

			wp_enqueue_style( 'wc-common-style', plugins_url( '/resources/css/admin_style.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			wp_deregister_script( 'jqueryui' );
			if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'add-booking' || $_GET['page'] == 'bookings' || $_GET['page'] == 'ph-booking-calendar' ) ) {

				wp_enqueue_script( 'ph_booking_jquery_ui', plugins_url( '/resources/js/jquery-ui.min.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );
				// For new calendar design.
				wp_enqueue_style( 'ph_booking_calendar_style', plugins_url( '/resources/css/ph_calendar.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
				wp_enqueue_script( 'ph_booking_general_script', plugins_url( '/resources/js/ph-booking-general.js', __FILE__ ), array( 'jquery' ), time() );
				wp_localize_script( 'ph_booking_general_script', 'phive_booking_locale', $this->phive_get_string_translation_arr() );

				wp_enqueue_script( 'ph_select2_dropdown_script', plugins_url( '/resources/js/select2.min.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );

				wp_enqueue_style( 'ph_select2_dropdown_style', plugins_url( '/resources/css/select2.min.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
			} else {

				wp_enqueue_style( 'ph_booking_calendar_style_new', plugins_url( '/resources/css/ph_new_calendar.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
				wp_enqueue_style( 'ph_booking_box_calendar_style', plugins_url( '/resources/css/ph_box_calendar.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
			}

			// CSS issue with divi.
			if ( isset( $_GET['page'] ) && ( $_GET['page'] != 'et_divi_options' ) ) {

				wp_enqueue_style( 'jquery-ui-css', plugins_url( '/resources/css/jquery-ui.min.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
			}

			// #99899 - Admin Calendar.
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'ph-booking-calendar' ) {

				wp_enqueue_style( 'ph-admin-calendar-style', plugins_url( '/resources/css/ph_admin_calendar.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
			}

			wp_enqueue_script( 'ph_booking_admin_script', plugins_url( '/resources/js/ph-booking-admin.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );
			wp_localize_script( 'ph_booking_admin_script', 'phive_booking_locale', $this->phive_get_string_translation_arr() );

			// Need to enqueue it to header since for some theme enqueuing to footer will not load the js file.
			wp_enqueue_script( 'ph_booking_products', plugins_url( '/resources/js/ph-booking-ajax.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );

			$display_settings         = get_option( 'ph_bookings_display_settigns' );
			$text_customisation       = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
			$booking_end_time_display = ( isset( $display_settings['booking_end_time_display'] ) && $display_settings['booking_end_time_display'] == 'no' ) ? false : true;

			$max_participant = isset( $text_customisation['max_participant'] ) && ! empty( $text_customisation['max_participant'] ) ? $text_customisation['max_participant'] : __( 'Total participant (%total) exceeds maximum allowed participant (%max)', 'bookings-and-appointments-for-woocommerce' );
			$min_participant = isset( $text_customisation['min_participant'] ) && ! empty( $text_customisation['min_participant'] ) ? $text_customisation['min_participant'] : __( 'Minimum number of participants required for a booking is (%min)', 'bookings-and-appointments-for-woocommerce' );

			$maximum_participant_warning = apply_filters( 'phive_booking_get_maximum_allowed_participant_message', $max_participant );

			$minimum_participant_warning = apply_filters( 'phive_booking_get_minimum_required_participant_message', $min_participant );

			$maximum_participant_warning = ph_wpml_translate_single_string( 'text_customisation_max_participant', $maximum_participant_warning );
			$minimum_participant_warning = ph_wpml_translate_single_string( 'text_customisation_min_participant', $minimum_participant_warning );

			$localization_arr = array(
				'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
				'security'                    => wp_create_nonce( 'phive_change_product_price' ),
				'maximum_participant_warning' => __( $maximum_participant_warning, 'bookings-and-appointments-for-woocommerce' ),
				'minimum_participant_warning' => __( $minimum_participant_warning, 'bookings-and-appointments-for-woocommerce' ),
				'available_slot_message'      => __( 'There is a maximum of %available_slot place remaining', 'bookings-and-appointments-for-woocommerce' ),
				'display_end_time'            => apply_filters( 'ph_bookings_display_booking_end_time', $booking_end_time_display ),
				'am_pm_to_text'               => array(
					'am' => __( 'am', 'bookings-and-appointments-for-woocommerce' ),
					'pm' => __( 'pm', 'bookings-and-appointments-for-woocommerce' ),
				),
			);
			wp_localize_script( 'ph_booking_products', 'phive_booking_ajax', array_merge( $localization_arr, $this->phive_get_string_translation_arr() ) );

			wp_enqueue_script(
				'ph_booking_common',
				plugins_url( '/resources/js/ph-bookings-common.js', __FILE__ ),
				array( 'jquery' ),
				PH_BOOKINGS_PLUGIN_VERSION
			);

			wp_localize_script(
				'ph_booking_common',
				'phive_booking_common_ajax',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);
		}

		/**
		 * PH Booking Scripts.
		 */
		public function phive_booking_scripts() {

			// 168646 - backend add booking for vendors.
			global $wp;
			global $post;

			$dokan_add_bookings_page = 0;

			if ( ! is_admin() && isset( $wp->query_vars['phive_booking'] ) ) {
				if ( $wp->query_vars['phive_booking'] == 'add-booking' ) {
					$dokan_add_bookings_page = 1;
				}
			}

			wp_enqueue_script(
				'ph_booking_common',
				plugins_url( '/resources/js/ph-bookings-common.js', __FILE__ ),
				array( 'jquery' ),
				PH_BOOKINGS_PLUGIN_VERSION
			);

			wp_localize_script(
				'ph_booking_common',
				'phive_booking_common_ajax',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);

			// 102180 - load scripts only on plugin specific pages.
			// 123807 - load scripts on elementor product page templates.
			$template_type     = get_post_meta( get_the_ID(), '_elementor_template_type', 1 );
			$short_code_exists = 0;

			// To support Easy Real Estate Plugin's Properties.
			$ph_custom_post = false;

			if ( is_object( $post ) ) {
				$short_code_exists = ( ! empty( $post->post_content ) && strstr( $post->post_content, '[product_page' ) ) ? 1 : 0;
				$short_code_exists = $short_code_exists ? $short_code_exists : ( ( ! empty( $post->post_content ) && strstr( $post->post_content, '[ph_bookings_calendar' ) ) ? 1 : 0 );

				$ph_custom_post = ( ! empty( $post->post_type ) && $post->post_type == 'property' ) ? true : false;
			}

			if ( apply_filters( 'ph_load_booking_calendar_on_custom_pages', false ) ) {

				$ph_custom_post = true;
			}

			if ( ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) ||
				( function_exists( 'is_product' ) && is_product() ) ||
				( function_exists( 'is_cart' ) && is_cart() ) ||
				( function_exists( 'is_checkout' ) && is_checkout() ) ||
				$short_code_exists ||
				( ! empty( $template_type ) && $template_type == 'product' ) ||
				$dokan_add_bookings_page == 1 ||
				$ph_custom_post
			) {

				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_script( 'ph_booking_general_script', plugins_url( '/resources/js/ph-booking-general.js', __FILE__ ), array( 'jquery' ), time() );

				// Need to enqueue it to header since for some theme enqueuing to footer will not load the js file.
				wp_enqueue_script( 'ph_booking_product', plugins_url( '/resources/js/ph-booking-ajax.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );

				wp_localize_script( 'ph_booking_general_script', 'phive_booking_locale', $this->phive_get_string_translation_arr() );

				$display_settings   = get_option( 'ph_bookings_display_settigns' );
				$text_customisation = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
				$max_participant    = isset( $text_customisation['max_participant'] ) && ! empty( $text_customisation['max_participant'] ) ? $text_customisation['max_participant'] : __( 'Total participant (%total) exceeds maximum allowed participant (%max)', 'bookings-and-appointments-for-woocommerce' );
				$min_participant    = isset( $text_customisation['min_participant'] ) && ! empty( $text_customisation['min_participant'] ) ? $text_customisation['min_participant'] : __( 'Minimum number of participants required for a booking is (%min)', 'bookings-and-appointments-for-woocommerce' );

				$maximum_participant_warning = apply_filters( 'phive_booking_get_maximum_allowed_participant_message', $max_participant );
				$minimum_participant_warning = apply_filters( 'phive_booking_get_minimum_required_participant_message', $min_participant );

				$maximum_participant_warning = ph_wpml_translate_single_string( 'text_customisation_max_participant', $maximum_participant_warning );
				$minimum_participant_warning = ph_wpml_translate_single_string( 'text_customisation_min_participant', $minimum_participant_warning );

				$booking_end_time_display = ( isset( $display_settings['booking_end_time_display'] ) && $display_settings['booking_end_time_display'] == 'no' ) ? false : true;
				$localization_arr         = array(
					'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
					'security'                    => wp_create_nonce( 'phive_change_product_price' ),
					'maximum_participant_warning' => __( $maximum_participant_warning, 'bookings-and-appointments-for-woocommerce' ),
					'minimum_participant_warning' => __( $minimum_participant_warning, 'bookings-and-appointments-for-woocommerce' ),
					'available_slot_message'      => __( 'There is a maximum of %available_slot place remaining', 'bookings-and-appointments-for-woocommerce' ),
					'display_end_time'            => apply_filters( 'ph_bookings_display_booking_end_time', $booking_end_time_display ),
					'am_pm_to_text'               => array(
						'am' => __( 'am', 'bookings-and-appointments-for-woocommerce' ),
						'pm' => __( 'pm', 'bookings-and-appointments-for-woocommerce' ),
					),
				);

				wp_localize_script( 'ph_booking_product', 'phive_booking_ajax', array_merge( $localization_arr, $this->phive_get_string_translation_arr() ) );

				// Autofill for search widget.
				wp_enqueue_script( 'ph_booking_autofill_script', plugins_url( '/resources/js/ph-booking-autofill.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );

				wp_localize_script(
					'ph_booking_autofill_script',
					'ph_booking_autofill',
					array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
					)
				);

				wp_enqueue_style( 'ph_booking_style', plugins_url( '/resources/css/ph_booking.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
				wp_enqueue_style( 'jquery-ui-css', plugins_url( '/resources/css/jquery-ui.min.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );

				wp_enqueue_style( 'ph_booking_mobile_view_style', plugins_url( '/resources/css/ph_bookings_mobile_view.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );

				$ph_calendar_color  = get_option( 'ph_booking_settings_calendar_color' );
				$ph_calendar_design = ( isset( $ph_calendar_color['ph_calendar_design'] ) && ! empty( $ph_calendar_color['ph_calendar_design'] ) ) ? $ph_calendar_color['ph_calendar_design'] : 1; // default legacy design will display

				if ( $ph_calendar_design == 2 && ! ( isset( $_GET['page'] ) && $_GET['page'] == 'add-booking' ) ) {

					wp_enqueue_style( 'ph_booking_calendar_style', plugins_url( '/resources/css/ph_new_calendar.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
				}

				if ( $ph_calendar_design == 3 && ! ( isset( $_GET['page'] ) && $_GET['page'] == 'add-booking' ) ) {

					wp_enqueue_style( 'ph_booking_calendar_style', plugins_url( '/resources/css/ph_calendar.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
					wp_enqueue_style( 'ph_booking_mobile_view_style', plugins_url( '/resources/css/ph_bookings_mobile_view.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
					wp_enqueue_script( 'ph_booking_new_design', plugins_url( '/resources/js/ph-booking-box-design.js', __FILE__ ), array( 'jquery' ), PH_BOOKINGS_PLUGIN_VERSION );
					wp_enqueue_style( 'ph_booking_box_calendar_style', plugins_url( '/resources/css/ph_box_calendar.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
				} else {

					wp_enqueue_style( 'ph_booking_calendar_style', plugins_url( '/resources/css/ph_calendar.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
					wp_enqueue_style( 'ph_booking_mobile_view_style', plugins_url( '/resources/css/ph_bookings_mobile_view.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
				}
			} elseif ( is_active_widget( false, false, 'ph_booking_search_widget', false ) ) {

				wp_enqueue_style( 'jquery-ui-css', plugins_url( '/resources/css/jquery-ui.min.css', __FILE__ ) );
			}
		}

		/**
		 * Enqueue styles for admin booking calendar.
		 */
		public function phive_booking_styles_admin_booking_calendar() {

			wp_enqueue_style( 'ph_booking_style', plugins_url( '/resources/css/ph_booking.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
			wp_enqueue_style( 'ph_booking_mobile_view_style', plugins_url( '/resources/css/ph_bookings_mobile_view.css', __FILE__ ), array(), PH_BOOKINGS_PLUGIN_VERSION );
		}

		/**
		 * Enqueue scripts for Porto theme compatibility.
		 */
		public function phive_booking_scripts_theme_porto() {

			if ( function_exists( 'wp_get_theme' ) && ( 'Porto' === wp_get_theme() || 'Porto Child' === wp_get_theme() ) ) {

				wp_dequeue_script( 'ph_booking_general_script' );
				wp_dequeue_script( 'ph_booking_product' );
				wp_enqueue_script( 'ph_booking_general_script', plugins_url( '/resources/js/ph-booking-general.js', __FILE__ ), array( 'jquery', 'porto-theme' ), time(), true );
				wp_enqueue_script( 'ph_booking_product', plugins_url( '/resources/js/ph-booking-ajax.js', __FILE__ ), array( 'jquery', 'porto-theme' ), PH_BOOKINGS_PLUGIN_VERSION, true );
			}
		}

		/**
		 * Get the string translation array for various text customizations.
		 *
		 * @return array
		 */
		private function phive_get_string_translation_arr() {

			$display_settings   = get_option( 'ph_bookings_display_settigns' );
			$text_customisation = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();

			if ( ! empty( $text_customisation ) ) {
				foreach ( $text_customisation as $key => $value ) {
					$name                       = 'text_customisation_' . $key;
					$value                      = ph_wpml_translate_single_string( $name, $value );
					$text_customisation[ $key ] = $value;
				}
			}

			$pick_a_date = isset( $text_customisation['pick_a_date'] ) && ! empty( $text_customisation['pick_a_date'] ) ? $text_customisation['pick_a_date'] : 'Please Pick a Date';
			$max_block   = isset( $text_customisation['max_block'] ) && ! empty( $text_customisation['max_block'] ) ? $text_customisation['max_block'] : __( 'Max no of blocks available to book is %max_block', 'bookings-and-appointments-for-woocommerce' );

			// Translators: %d is the minimum number of blocks required for booking.
			$min_block_required = isset( $text_customisation['min_block_required'] ) && ! empty( $text_customisation['min_block_required'] ) ? $text_customisation['min_block_required'] : __( 'Please Select minimum %d blocks.', 'bookings-and-appointments-for-woocommerce' );

			$pick_an_end_date = isset( $text_customisation['pick_an_end_date'] ) && ! empty( $text_customisation['pick_an_end_date'] ) ? $text_customisation['pick_an_end_date'] : __( 'Please pick an end date', 'bookings-and-appointments-for-woocommerce' );
			// 137142
			// 169544 - Issue: "Please pick a time" label is not appearing in the Loco Translate plugin.
			$pick_a_time = isset( $text_customisation['pick_a_time'] ) && ! empty( $text_customisation['pick_a_time'] ) ? __( $text_customisation['pick_a_time'], 'bookings-and-appointments-for-woocommerce' ) : __( 'Please pick a Time', 'bookings-and-appointments-for-woocommerce' );

			$booking_info_booking_cost = isset( $text_customisation['booking_info_booking_cost'] ) && ! empty( $text_customisation['booking_info_booking_cost'] ) ? $text_customisation['booking_info_booking_cost'] : 'Booking cost';
			$booking_info_booking      = isset( $text_customisation['booking_info_booking'] ) && ! empty( $text_customisation['booking_info_booking'] ) ? $text_customisation['booking_info_booking'] : 'Booking';

			$check_in_text  = isset( $text_customisation['check_in_text'] ) && ! empty( $text_customisation['check_in_text'] ) ? $text_customisation['check_in_text'] : 'Check-in';
			$check_out_text = isset( $text_customisation['check_out_text'] ) && ! empty( $text_customisation['check_out_text'] ) ? $text_customisation['check_out_text'] : 'Check-out';

			$booking_date_text = apply_filters( 'ph_booking_pick_booking_date_text', $pick_a_date );

			// 142103 - Book Now button does not work with theme astra when "ajax add to cart" theme setting is enabled.
			$astra_ajax_add_to_cart = 0;
			if ( defined( 'ASTRA_THEME_SETTINGS' ) ) {
				$settings               = get_option( ASTRA_THEME_SETTINGS );
				$astra_ajax_add_to_cart = isset( $settings['single-product-add-to-cart-action'] ) ? 1 : ( isset( $settings['single-product-ajax-add-to-cart'] ) ? $settings['single-product-ajax-add-to-cart'] : 0 );
			}

			$calendar_selection_limit = apply_filters( 'ph_booking_calendar_selection_limit', 1500 );

			return array(
				'months'                         => array(
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
				'months_short'                   => array(
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
				'booking_cost'                   => __( $booking_info_booking_cost, 'bookings-and-appointments-for-woocommerce' ),
				'booking'                        => __( $booking_info_booking, 'bookings-and-appointments-for-woocommerce' ),
				'to'                             => __( 'to', 'bookings-and-appointments-for-woocommerce' ),
				'checkin'                        => __( $check_in_text, 'bookings-and-appointments-for-woocommerce' ),
				'checkout'                       => __( $check_out_text, 'bookings-and-appointments-for-woocommerce' ),
				'is_not_avail'                   => __( 'is not available.', 'bookings-and-appointments-for-woocommerce' ),
				'are_not_avail'                  => __( 'are not available.', 'bookings-and-appointments-for-woocommerce' ),
				'pick_later_date'                => __( 'Pick a later end date', 'bookings-and-appointments-for-woocommerce' ),
				'pick_later_time'                => __( 'Pick a later end time', 'bookings-and-appointments-for-woocommerce' ),
				'max_limit_text'                 => apply_filters( 'phive_booking_max_block_available_error_message', __( $max_block, 'bookings-and-appointments-for-woocommerce' ) ),
				'pick_booking'                   => __( 'Please pick a booking period', 'bookings-and-appointments-for-woocommerce' ),
				'exceed_booking'                 => __( "Since max bookings per block is %1\$d and you have enabled 'each participant as a booking' max participants allowed is %2\$d", 'bookings-and-appointments-for-woocommerce' ),
				'Please_Pick_a_Date'             => __( $booking_date_text, 'bookings-and-appointments-for-woocommerce' ),
				'pick_a_end_date'                => __( 'Please Pick a End Dates.', 'bookings-and-appointments-for-woocommerce' ),
				'pick_min_date'                  => apply_filters( 'phive_booking_pick_min_block_message', __( $min_block_required, 'bookings-and-appointments-for-woocommerce' ) ),
				'pick_an_end_date'               => apply_filters( 'phive_booking_pick_an_end_date_message', __( $pick_an_end_date, 'bookings-and-appointments-for-woocommerce' ) ),
				'pick_a_time'                    => apply_filters( 'phive_booking_pick_a_time_message', __( $pick_a_time, 'bookings-and-appointments-for-woocommerce' ) ),
				'pick_a_end_time'                => apply_filters( 'phive_booking_pick_a_end_time_message', __( 'Please pick the end time', 'bookings-and-appointments-for-woocommerce' ) ),
				'pick_a_end_month'               => apply_filters( 'phive_booking_pick_an_end_month_message', __( 'Please pick an end month', 'bookings-and-appointments-for-woocommerce' ) ),
				'pick_a_month'                   => apply_filters( 'phive_booking_pick_a_month_message', __( 'Please pick a month', 'bookings-and-appointments-for-woocommerce' ) ),
				'max_individual_participant'     => __( 'Number of %pname cannot exceed %pmax', 'bookings-and-appointments-for-woocommerce' ),
				'ajaxurl'                        => admin_url( 'admin-ajax.php' ),
				'single_min_participant_warning' => __( 'Minimum number of %pname required is %min', 'bookings-and-appointments-for-woocommerce' ),
				'astra_ajax_add_to_cart'         => $astra_ajax_add_to_cart,
				// translators: %d is the maximum number of bookings allowed per block.
				'max_participant_warning'        => __( 'Value must be less than or equal to Max Bookings per Block (%d)', 'bookings-and-appointments-for-woocommerce' ),
				'min_participant_warning'        => __( 'Value must be greater than or equal to 1', 'bookings-and-appointments-for-woocommerce' ),
				'and_text'                       => __( 'and', 'bookings-and-appointments-for-woocommerce' ),
				'calendar_selection_limit'       => $calendar_selection_limit,
				'minute_text'                    => __( 'minute', 'bookings-and-appointments-for-woocommerce' ),
				'hour_text'                      => __( 'hour', 'bookings-and-appointments-for-woocommerce' ),
				'day_text'                       => __( 'day', 'bookings-and-appointments-for-woocommerce' ),
			);
		}

		/**
		 * Add action links to the plugin row.
		 *
		 * @param array $links Array of links.
		 * @return array
		 */
		public function plugin_action_links( $links ) {

			$plugin_links = array(

				'<a href="http://pluginhive.com/support/?utm_source=bookings&utm_medium=plugin_settings" target="_blank">' . __( 'Support', 'bookings-and-appointments-for-woocommerce' ) . '</a>',
				'<a href="' . admin_url( 'admin.php?page=bookings-settings' ) . '" >' . __( 'Settings', 'bookings-and-appointments-for-woocommerce' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Load thankyou page extensions.
		 */
		public function ph_load_icalendar_extensions() {

			if ( ! class_exists( 'PH_Bookings_Icalendar_Extension' ) ) {
				include_once 'includes/frondend/class-ph-icalendar-extension.php';
			}

			if ( ! class_exists( 'PH_Bookings_ICalendar_Generator' ) ) {
				include_once 'includes/integrations/class-ph-booking-ics-generator.php';
			}

			if ( ! class_exists( 'PH_Bookings_Order_Item_Handler' ) ) {
				include_once 'includes/class-ph-booking-order-item-handler.php';
			}

			$icalendar_generator = new PH_Bookings_ICalendar_Generator();

			new PH_Bookings_Icalendar_Extension( $icalendar_generator );

			if ( ! class_exists( 'PH_Bookings_Outlook' ) ) {
				include_once 'includes/integrations/outlook/class-ph-bookings-outlook.php';
			}

			if ( ! class_exists( 'PH_Booking_Outlook_Event_Handler' ) ) {
				include_once 'includes/integrations/outlook/class-ph-booking-outlook-event-handler.php';
			}
		}
	}

	new phive_booking_initialze_premium();
}
