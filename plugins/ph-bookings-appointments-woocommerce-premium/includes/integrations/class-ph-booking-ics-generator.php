<?php

/**
 * ICS file generator.
 */

defined('ABSPATH') || exit;

class PH_Bookings_ICalendar_Generator {

	private const DATE_FORMAT = 'Ymd\THis\Z';

	public static function ph_generate_ics_file($events) {

		$site_title		= get_bloginfo('name');
		$admin_email	= get_option('admin_email');

		// Event details
		$organizer = "mailto:{$admin_email}";

		// ICS file content
		$icsContent = "BEGIN:VCALENDAR\r\n";
		$icsContent .= "VERSION:2.0\r\n";
		$icsContent .= "PRODID:-//{$site_title}//Bookings//EN\r\n";
		$icsContent .= "CALSCALE:GREGORIAN\r\n";

		foreach ($events as $event) {

			$icsContent .= "BEGIN:VEVENT\r\n";
			$icsContent .= "UID:" . uniqid() . "\r\n";
			$icsContent .= "DTSTAMP:" . gmdate(self::DATE_FORMAT) . "\r\n";
			$icsContent .= "DTSTART:" . self::ph_format_timestamp($event['from']) . "\r\n";
			$icsContent .= "DTEND:" . self::ph_format_timestamp($event['to']) . "\r\n";
			$icsContent .= "SUMMARY:" . self::ph_escape_string($event['summary']) . "\r\n";
			$icsContent .= "DESCRIPTION:" . self::ph_escape_string($event['description']) . "\r\n";
			$icsContent .= "ORGANIZER:" . $organizer . "\r\n";
			$icsContent .= "END:VEVENT\r\n";
		}

		$icsContent .= "END:VCALENDAR\r\n";

		return $icsContent;
	}

	/**
	 * Format timestamp for ICS.
	 *
	 * @var mixed $timestamp
	 *
	 * @return DateTime
	 */
	private static function ph_format_timestamp($timestamp) {
		
		$timezone = get_option('timezone_string');

		if (empty($timezone)) {
			$time_offset = get_option('gmt_offset');
			$timezone = timezone_name_from_abbr('', $time_offset * 60 * 60, 0);
			$timezone = wp_timezone_string();
		}

		// Create a DateTime object with the given timestamp in IST
		$date_time = new DateTime($timestamp, new DateTimeZone($timezone));

		// Convert the DateTime object to UTC
		$date_time->setTimezone(new DateTimeZone('UTC'));

		// Format the date in the required .ics format
		return $date_time->format(self::DATE_FORMAT);
	}

	/**
	 * Escape the string before generating the ICS.
	 *
	 * @var mixed $data
	 *
	 * @return mixed
	 */
	private static function ph_escape_string($data) {

		$data = str_replace(["\r\n", "\n"], "\\n", $data);
		$data =  preg_replace('/([\,;])/', '\\\$1', $data);

		return $data;
	}
}
