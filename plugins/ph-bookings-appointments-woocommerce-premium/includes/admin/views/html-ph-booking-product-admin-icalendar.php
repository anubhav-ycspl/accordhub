<?php
$default_summary = "[PRODUCT_NAME]([BOOKING_STATUS])";
$default_calendar_details = "Customer Details\n[CUSTOMER_NAME]\n[CUSTOMER_PHONE]\n[CUSTOMER_EMAIL]\nBooking Details\n[BOOKING_COST]\n[PARTICIPANT]\n[ASSET]\n[RESOURCE]";
$default_btn_text =  __('Download .ics File', 'bookings-and-appointments-for-woocommerce');

if (!empty($_POST['ph_booking_settings_icalender_submitted'])) {

	$settings = array(
		'enable_icalendar' 			=> isset($_POST['enable_icalendar']) ? 'yes' : 'no',
		'icalendar_btn_text' 		=> isset($_POST['icalendar_btn_text']) ? $_POST['icalendar_btn_text'] : $default_btn_text,
		'ics_download_for_customer' => isset($_POST['ics_download_for_customer']) ? 'yes' : 'no',
		'icalendar_summary' 		=> isset($_POST['icalendar_summary']) ? $_POST['icalendar_summary'] : $default_summary,
		'icalendar_description' 	=> isset($_POST['icalendar_description']) ? $_POST['icalendar_description'] : $icalendar_description,
	);

	update_option($this->id . 'icalendar', $settings);
}

$icalendar_settings = get_option($this->id . 'icalendar', 1);
?>

<form method="post" action="" id="">
	<h2><?php _e('iCalendar Settings', 'bookings-and-appointments-for-woocommerce'); ?></h2>
	<ul style="list-style-type: disc;padding-left:1.3em">
		<!-- <li>
			<?php _e('Read more to know how to set up ', 'bookings-and-appointments-for-woocommerce'); ?><a href="https://www.pluginhive.com/knowledge-base/sync-woocommerce-bookings-with-your-google-calendar?utm_source=bookings&utm_medium=plugin_settings" target="_blank"><?php _e('Google Calendar Sync', 'bookings-and-appointments-for-woocommerce') ?></a>.
		</li>
		<li>
			<?php _e('Read more to know how to ', 'bookings-and-appointments-for-woocommerce'); ?><a href="https://www.pluginhive.com/knowledge-base/troubleshooting-google-calendar-sync-woocommerce-bookings-and-appointments-plugin/?utm_source=bookings&utm_medium=plugin_settings" target="_blank"><?php _e('Troubleshoot Google Calendar Sync', 'bookings-and-appointments-for-woocommerce') ?></a>.
		</li>
		<li>
			<?php _e('Read more to know the ', 'bookings-and-appointments-for-woocommerce'); ?><a href="https://www.pluginhive.com/knowledge-base/woocommerce-bookings-and-appointments-plugin-faqs/?utm_source=bookings&utm_medium=plugin_settings#GoogleSync" target="_blank"><?php _e('Frequently asked questions about Google Calendar Sync', 'bookings-and-appointments-for-woocommerce') ?></a>.
		</li> -->
	</ul>
	<input type="hidden" name="ph_booking_settings_icalender_submitted" value="1" />
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="enable_icalendar"><?php _e('iCalendar Event Export', 'bookings-and-appointments-for-woocommerce'); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="enable_icalendar" id="enable_icalendar" <?php echo isset($icalendar_settings['enable_icalendar']) && $icalendar_settings['enable_icalendar'] == 'yes' ? 'checked' : ''; ?>>
				<span><?php _e('Enabling this option will allow you to export the booking events to your iCalendar via .ics file', 'bookings-and-appointments-for-woocommerce'); ?></span>
			</td>
		</tr>
		<tr valign="top" class="icalendar-wrapper">
			<th scope="row" class="titledesc">
				<label for="ics_download_for_customer"><?php _e('Event Export for Customers', 'bookings-and-appointments-for-woocommerce'); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="ics_download_for_customer" id="ics_download_for_customer" <?php echo isset($icalendar_settings['ics_download_for_customer']) && $icalendar_settings['ics_download_for_customer'] == 'yes' ? 'checked' : ''; ?>>
				<span><?php _e('Enabling this option will allow your customers to export the booking events to your iCalendar via .ics file from their My Accounts page', 'bookings-and-appointments-for-woocommerce'); ?></span>
			</td>
		</tr>
		<tr valign="top" class="icalendar-wrapper">
			<th scope="row" class="titledesc">
				<label for="icalendar_btn_text"><?php _e('Export Event Button Text', 'bookings-and-appointments-for-woocommerce'); ?></label>
			</th>
			<td class="forminp forminp-text">
				<input name="icalendar_btn_text" id="icalendar_btn_text" type="text" value="<?php echo isset($icalendar_settings['icalendar_btn_text']) && !empty($icalendar_settings['icalendar_btn_text']) ? $icalendar_settings['icalendar_btn_text'] : $default_btn_text; ?>" placeholder="" autocomplete="off">
				<br/><span><?php _e("This text will be displayed on the .ics file download button on the customer's My Account Page", 'bookings-and-appointments-for-woocommerce') ?></span>
			</td>
		</tr>
		<tr valign="top" class="icalendar-wrapper">
			<th scope="row" class="titledesc">
				<label for="icalendar_summary"><?php _e('Customize iCalendar Event Title', 'bookings-and-appointments-for-woocommerce'); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<div class="" style="float:left;">
					<b>Order: #[order_id], Order Item: #[order_item_id] </b>
				</div>
				<br>
				<div class="" style="float:left;width:50%;">
					<textarea type="textarea" name="icalendar_summary" id="icalendar_summary" style="width: 100%;height: 120px;"><?php echo isset($icalendar_settings['icalendar_summary']) ? $icalendar_settings['icalendar_summary'] : $default_summary; ?></textarea>
				</div>
				<div style="float:left;width:40%;font-size:12px;margin-left:10px;"> <i><?php _e('Use the following Tags to customize your iCalendar Event Title.', 'bookings-and-appointments-for-woocommerce'); ?>
						<br><?php _e("For eg; if you want to mention customer’s name in the title then choose [CUSTOMER_NAME] tag.", "bookings-and-appointments-for-woocommerce") ?></i>
					<br>[RESOURCE]<br>[PARTICIPANT]<br>[CUSTOMER_NAME]<br>[PRODUCT_NAME]<br>[BOOKING_STATUS]<br>[ASSET]
				</div>
			</td>
		</tr>
		<tr valign="top" class="icalendar-wrapper">
			<th scope="row" class="titledesc">
				<label for="icalendar_summary"><?php _e('Customize iCalendar Event Details', 'bookings-and-appointments-for-woocommerce'); ?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<div class="" style="float:left;width:50%;">
					<textarea type="textarea" name="icalendar_description" id="icalendar_description" style="width: 100%;" rows="8"><?php echo (isset($icalendar_settings['icalendar_description']) && !empty($icalendar_settings['icalendar_description'])) ? $icalendar_settings['icalendar_description'] : $default_calendar_details; ?></textarea>
				</div>
				<div style="float:left;width:40%;font-size:12px;margin-left:10px;"> <i><?php _e("The tags/information mentioned in box will be displayed in your iCalendar. You have an option to remove any tags that you do not wish to add to your iCalendar. Similarly, you could add any tags that are missing from the list given below.", "bookings-and-appointments-for-woocommerce") ?></i>
					<br>[PARTICIPANT]<br>[ASSET]<br>[RESOURCE]<br>[CUSTOMER_NAME]<br>[CUSTOMER_PHONE]<br>[CUSTOMER_EMAIL]<br>[PRODUCT_NAME]<br>[BOOKING_STATUS]<br>[BOOKING_COST]<br>[BILLING_ADDRESS]<br>[BOOKING_NOTES]
				</div>
			</td>
		</tr>
	</table>
	<p class="submit">
		<button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes"><?php _e('Save changes', 'bookings-and-appointments-for-woocommerce'); ?></button>
	</p>

</form>