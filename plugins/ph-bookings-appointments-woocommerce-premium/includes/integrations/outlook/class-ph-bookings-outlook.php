<?php

/**
 * Outlook Integration.
 *
 * @package bookings-and-appointments-for-woocommerce
 */

defined('ABSPATH') || exit;

class PH_Bookings_Outlook {

	/**
	 * Outlook Settings.
	 *
	 * @var array
	 */
	private $outlook_settings;

	/**
	 * Redirect URL.
	 *
	 * @var string
	 */
	private $redirect_url;

	/**
	 * Authorization code.
	 *
	 * @var string
	 */
	private $authorization_code = '';

	/**
	 * User state, a unique id for each request.
	 *
	 * @var string
	 */
	private $state;

	/**
	 * Class instance.
	 *
	 * @var PH_Bookings_Outlook
	 */
	private static $instance;

	/**
	 * Defaul settings.
	 *
	 * @var array
	 */
	public static $default_settings;

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	private string $option_name = 'ph_booking_settings_outlook';

	/**
	 * PH_Booking_Outlook setup.
	 */
	public function __construct() {

		$this->redirect_url = admin_url();

		self::$default_settings = array(
			'enable_outlook'	=> false,
			'debug'				=> false,
			'client_id'			=> '',
			'client_secret'		=> '',
			'event_title'		=> '[PRODUCT_NAME]([BOOKING_STATUS])',
			'event_description' => "<body>
										<p><strong>Customer Details</strong>
											<br>[CUSTOMER_NAME]
											<br>[CUSTOMER_PHONE]
											<br>[CUSTOMER_EMAIL]
										</p>
										<p><strong>Booking Details</strong>
											<br>[BOOKING_COST]
											<br>[PARTICIPANT]
											<br>[ASSET]
											<br>[RESOURCE]
										</p>
									</body>",
			'event_attendee'	=> false
		);

		if (empty($this->outlook_settings)) {
			$this->outlook_settings = get_option($this->option_name, array());
		}

		$this->outlook_settings = wp_parse_args($this->outlook_settings, self::$default_settings);

		// Handle Sign in with Outlook.
		add_action('wp_ajax_ph_signin_with_outlook', array($this, 'ph_signin_with_outlook'));
		add_action('wp_ajax_ph_deactivate_outlook_authorization', array($this, 'ph_deactivate_outlook_authorization'));
		
		add_action('wp_ajax_ph_get_outlook_calendars', array($this, 'ph_get_and_update_outlook_calendars_to_db'));

		// Capture the redirect from Outlook. Checking for the key "page" since SMTP plugin uses same code which leads to invalid redirection.
		// For outlook redirection, there wont be a page attribute.
		if ($this->ph_verify_for_outlook_redirect()) {
			
			$this->ph_capture_outlook_redirect();
		}

		// Load Outlook Calendars after installation for existing customers 
		if (isset($this->outlook_settings['enable_outlook']) && $this->outlook_settings['enable_outlook'] && !isset($this->outlook_settings['outlook_calendars'])) {

			$this->ph_get_and_update_outlook_calendars_to_db();
		}
	}

	/**
	 * Verifies if the current request is a valid Outlook redirect.
	 *
	 * This function checks for the presence of necessary query parameters
	 * (`code`, `page`, and `state`) and validates the `state` against the
	 * transient `ph_bookings_outlook_app_state`. It ensures that the request
	 * corresponds to a valid Outlook authentication redirect.
	 *
	 * @return bool Returns true if the request is a valid Outlook redirect, false otherwise.
	 */
	public function ph_verify_for_outlook_redirect() {

		// Check if the required GET parameters are set and validate the app state.
		if (
			isset($_GET) &&                 // Ensure the $_GET superglobal is set.
			isset($_GET['code']) &&         // Check if the 'code' parameter exists.
			!isset($_GET['page']) &&         // Check if the 'page' parameter not exists.
			!empty($_GET['state']) &&        // Ensure the 'state' parameter is not empty.
			strpos($_GET['state'], 'PH-Bookings-Outlook') !== false // The 'PH-Bookings-Outlook' string is contained
		) {
			return true; // Valid Outlook redirect request.
		}

		return false; // Invalid request.
	}

	/**
	 * Class instance.
	 *
	 * @return PH_Bookings_Outlook
	 */
	public static function ph_get_instance() {

		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get outlook settings.
	 *
	 * @return array
	 */
	public static function ph_get_settings() {
		$instance = self::ph_get_instance();
		return $instance->outlook_settings;
	}

	/**
	 * Sign in with outlook by generating authorization URL and redirecting to Outlook.
	 */
	public function ph_signin_with_outlook() {

		$client_id		= isset($_POST['clientId']) ? $_POST['clientId'] : '';
		$client_secret	= isset($_POST['clientSecret']) ? $_POST['clientSecret'] : '';

		$this->outlook_settings['enable_outlook'] = true;
		$this->outlook_settings['client_id'] = $client_id;
		$this->outlook_settings['client_secret'] = $client_secret;

		// To handle scenario where settings aren't saved, instead directly went with Sign in.
		update_option($this->option_name, $this->outlook_settings);

		$app_state = 'PH-Bookings-Outlook-' . uniqid();

		// App state code will be maintained for 30 minutes.
		set_transient('ph_bookings_outlook_app_state', $app_state, 1800);

		$query_params = array(
			'client_id' 	=> $client_id,
			'response_type' => 'code',
			'redirect_uri' 	=> $this->redirect_url,
			'response_mode' => 'query',
			'scope' 		=> 'offline_access Calendars.ReadWrite',
			'state' 		=> $app_state
		);

		$query_params = urldecode(http_build_query($query_params));

		wp_send_json(
			array(
				'status'		=> 'success',
				'redirectUrl'	=> PH_Bookings_Config::PH_BOOKING_OUTLOOK_REDIRECT_URL . $query_params
			)
		);

    	// Fetch and update Outlook calendars immediately after sign-in.
		$this->ph_get_and_update_outlook_calendars_to_db();

		wp_die();
	}

	/**
	 * Fetch and Update Outlook Calendars in the Database.
	 *
	 * @param string $ph_type
	 */
	public function ph_get_and_update_outlook_calendars_to_db() {

		$authorization_status 	= get_option('ph_bookings_outlook_authorization_status', false);

		// If authorization status is unavailable or client_id and client_secret is not set, return an error response.
		if (!$authorization_status || empty($this->outlook_settings['client_id']) || empty($this->outlook_settings['client_secret'])) {

			if (!wp_doing_ajax() || !(isset($_REQUEST['action']) && 'ph_get_outlook_calendars' == $_REQUEST['action'])) {
				return;
			}

			wp_send_json(
				array(
					'status'	=> 'failed',
					'message'	=> __('Not Signed in, First sign in to MS Outlook', 'bookings-and-appointments-for-woocommerce')
				)
			);

			wp_die();
		}

		// Check for token validation
		$this->ph_check_and_refresh_token();

		$auth_token = get_transient('ph_bookings_outlook_auth_token');

		// Outlook Calendar API endpoint to fetch the user's calendar list.
		$ph_url = PH_Bookings_Config::PH_BOOKING_OUTLOOK_EVENT_URL  . '/calendars';

		// API request parameters.
		$params = array(
			'url'		=> $ph_url,
			'method'	=> 'GET',
			'headers'	=> array(
				'Content-Type'	=> 'application/json',
				'Authorization'	=> 'Bearer ' . $auth_token
			)
		);

		$response = PH_Bookings_API_Invoker::ph_make_request($params);

		$response_body = json_decode($response['body'], true); // Decode the JSON response body

		$ph_calendars = [];

		if (!empty($response_body)) {

			// If the response body contains calendar value, process each calendar.
			foreach($response_body['value'] as $key => $calendar) {

				if (!empty($calendar['id'])) {
				
					$ph_calendars[] = array(
						'id' 	=> $calendar['id'],
						'name' 	=> $calendar['name'],
					);
				}
			}

			// Update the settings with the fetched calendar list.
			$this->outlook_settings['outlook_calendars'] = $ph_calendars;

			update_option($this->option_name, $this->outlook_settings);		
		}

		if (!wp_doing_ajax() || !(isset($_REQUEST['action']) && 'ph_get_outlook_calendars' == $_REQUEST['action'])) {
			return;
		}

		// If no calendars were found, send a failure response.
		if (empty($ph_calendars)) {

			wp_send_json(
				array(
					'status'	=> 'failed',
					'responce'	=> $response_body
				)
			);
			
			wp_die();
		}

		// Send a success response with the retrieved calendar details.
		wp_send_json(
			array(
				'status'	=> 'success',
				'responce'	=> $ph_calendars
			)
		);

		wp_die();
	}

	/**
	 * Deactivate present authorization and provide option to Sign in.
	 */
	public function ph_deactivate_outlook_authorization() {
		
		// Update authorization status as false.
		update_option('ph_bookings_outlook_authorization_status', false);

		// Delete existing tokens.
		delete_transient('ph_bookings_outlook_auth_token');
		delete_option('ph_bookings_outlook_refresh_token');

		wp_send_json(
			array(
				'status' => 'success',
			)
		);

		wp_die();
	}

	/**
	 * Generate Auth token.
	 *
	 * @param array $query_param
	 */
	public function ph_generate_auth_token($params) {

		$response = PH_Bookings_API_Invoker::ph_make_request($params);

		if (is_wp_error($response)) {
			self::ph_debug('---------------- Auth Token Error ----------------', $this->outlook_settings['debug']);
			self::ph_debug($response->get_error_message(), $this->outlook_settings['debug']);
			return;
		}

		$response_code    = wp_remote_retrieve_response_code($response);
		$response_message = wp_remote_retrieve_response_message($response);

		if (200 != $response_code || 'OK' !== $response_message) {

			self::ph_debug('---------------- Auth Token Error ----------------', $this->outlook_settings['debug']);
			self::ph_debug(
				array(
					'response_code'		=> $response_code,
					'response_message'	=> $response_message
				),
				$this->outlook_settings['debug']
			);

			return;
		}

		$response_body = wp_remote_retrieve_body($response);
		$response_body = json_decode($response_body);

		if (!is_object($response_body)) {
			self::ph_debug('---------------- Auth Token Error ----------------', $this->outlook_settings['debug']);
			self::ph_debug($response_body->get_error_message(), $this->outlook_settings['debug']);
			return;
		}

		if (!empty($response_body->access_token)) {
			set_transient('ph_bookings_outlook_auth_token', $response_body->access_token, $response_body->expires_in);
			update_option('ph_bookings_outlook_authorization_status', true);
		}

		if (isset($response_body->refresh_token) && !empty($response_body->refresh_token)) {
			// Updating referesh token in db since it can be long lived.
			update_option('ph_bookings_outlook_refresh_token', $response_body->refresh_token);
		}
	}

	/**
	 * Check for auth token expiry and update if needed.
	 */
	public static function ph_check_and_refresh_token() {

		$auth_token = get_transient('ph_bookings_outlook_auth_token');

		if (!empty($auth_token)) {
			return;
		}

		$outlook_instance = self::ph_get_instance();

		$params = array(
			'url'			=> PH_Bookings_Config::PH_BOOKING_OUTLOOK_TOKEN_URL,
			'method'		=> 'POST',
			'headers'		=> array(
				'Content-Type'		=> 'application/x-www-form-urlencoded'
			),
			'body'			=> array(
				'client_id'		=> $outlook_instance->outlook_settings['client_id'],
				'scope'			=> 'Calendars.ReadWrite',
				'refresh_token'	=> get_option('ph_bookings_outlook_refresh_token'),
				'grant_type'	=> 'refresh_token',
				'client_secret'	=> $outlook_instance->outlook_settings['client_secret']
			)
		);

		$outlook_instance->ph_generate_auth_token($params);
	}

	/**
	 * Capture the redirect coming from Outlook
	 */
	public function ph_capture_outlook_redirect() {

		$this->state				= $_GET['state'];
		$this->authorization_code	= $_GET['code'];

		if (!$this->ph_verify_autorization_state()) {
			wp_safe_redirect($this->redirect_url . 'admin.php?page=bookings-settings&tab=outlook');
			exit();
		}

		$params = array(
			'url'		=> PH_Bookings_Config::PH_BOOKING_OUTLOOK_TOKEN_URL,
			'method'	=> 'POST',
			'headers'	=> array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
			'body'		=> array(
				'client_id' 	=> $this->outlook_settings['client_id'],
				'scope'			=> 'Calendars.ReadWrite',
				'code'			=> $this->authorization_code,
				'redirect_uri' 	=> $this->redirect_url,
				'grant_type' 	=> 'authorization_code',
				'client_secret' => $this->outlook_settings['client_secret'],
			)
		);

		$this->ph_generate_auth_token($params);

		// Redirect to Outlook settings page.
		wp_safe_redirect($this->redirect_url . 'admin.php?page=bookings-settings&tab=outlook');
		exit();
	}

	/**
	 * Verify the authorization state.
	 *
	 * @return bool
	 */
	private function ph_verify_autorization_state() {
		
		$app_state = get_transient('ph_bookings_outlook_app_state');

		if (empty($app_state) || $this->state != $app_state) {
			self::ph_debug('Failed to verify the authorization state. Please try to Sign In again.', $this->outlook_settings['debug']);
			return false;
		}

		return true;		
	}

	/**
	 * Debug Outlook details.
	 *
	 * @param mixed $data
	 */
	public static function ph_debug($data, $debug_status = false) {

		if (!$debug_status || !function_exists('wc_get_logger')) {
			return;
		}

		$logger = wc_get_logger();
		$logger->debug(print_r($data, 1), array('source' => 'ph-bookings-outlook-integration'));
	}
}
new PH_Bookings_Outlook();
