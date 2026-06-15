<?php

/**
 * Bookings config.
 *
 * @package bookings-and-appointments-for-woocommerce
 */

defined('ABSPATH') || exit;

class PH_Bookings_Config {

	public const PH_BOOKING_OUTLOOK_REDIRECT_URL 	= 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?';
	public const PH_BOOKING_OUTLOOK_TOKEN_URL		= 'https://login.microsoftonline.com/common/oauth2/v2.0/token'; 
	public const PH_BOOKING_OUTLOOK_EVENT_URL		= 'https://graph.microsoft.com/v1.0/me';

	public const PH_BOOKING_GOOGLE_OAUTH_URI		= 'https://accounts.google.com/o/oauth2/';
	public const PH_BOOKING_GOOGLE_TOKEN_URI		= 'https://oauth2.googleapis.com/';
	public const PH_BOOKING_GOOGLE_CAL_URI			= 'https://www.googleapis.com/calendar/v3';
	public const PH_BOOKING_GOOGLE_API_SCOPE		= 'https://www.googleapis.com/auth/calendar';

	public const PH_LICENSE_SERVER_URL				= 'https://www.pluginhive.com/';
	public const PH_LICENSE_PRODUCT_ID				= '1276';
}