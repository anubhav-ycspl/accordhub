<?php

/**
 * Outlook Settings.
 *
 * @package bookings-and-appointments-for-woocommerce
 */

defined('ABSPATH') || exit;

?>

<?php 

$default_event_title 		= '[PRODUCT_NAME]([BOOKING_STATUS])';
$default_event_description 	= '<body>
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
</body>';

$settings 				= get_option('ph_booking_settings_outlook', array());
$settings 				= wp_parse_args($settings, PH_Bookings_Outlook::$default_settings);

if (isset($_POST) && isset($_POST['save_outlook_settings'])) {

	$settings = array(
		'enable_outlook'		=> isset($_POST['enable_outlook']) ? true : false,
		'debug'					=> isset($_POST['enable_debug']) ? true : false,
		'client_id'				=> isset($_POST['client_id']) ? $_POST['client_id'] : '',
		'client_secret'			=> isset($_POST['client_secret']) ? $_POST['client_secret'] : '',
		'event_title'			=> isset($_POST['event_title']) && !empty($_POST['event_title']) ? $_POST['event_title'] : $default_event_title,
		'event_description'		=> isset($_POST['event_description']) && !empty($_POST['event_description']) ? $_POST['event_description'] : $default_event_description,
		'event_attendee'		=> isset($_POST['event_attendee']) ? true : false,
		'outlook_calendar_id' 	=> isset($_POST['outlook_calendar_id']) ? $_POST['outlook_calendar_id'] : '',
		'outlook_calendars'		=> $settings['outlook_calendars'] ?? [],
		'outlook_event_filter'	=> $_POST['outlook_event_filter'] ?? [],
	);

	update_option($this->id . 'outlook', $settings);
}

$ph_booking_status = array(
	'paid'                  => __( 'Paid', 'bookings-and-appointments-for-woocommerce' ),
	'un-paid'               => __( 'Unpaid', 'bookings-and-appointments-for-woocommerce' ),
	'canceled'              => __( 'Cancelled', 'bookings-and-appointments-for-woocommerce' ),
	'requires-confirmation' => __( 'Requires Confirmation', 'bookings-and-appointments-for-woocommerce' ),
	'refunded'              => __( 'Refunded', 'bookings-and-appointments-for-woocommerce' ),
);

$ph_outlook_event_filter	= $settings['outlook_event_filter'] ?? [];
$ph_outlook_calendars		= $settings['outlook_calendars'] ?? [];
$ph_selected_calendar		= $settings['outlook_calendar_id'] ?? '';
$authorization_status 		= get_option('ph_bookings_outlook_authorization_status', false);
$readonly_status			= $authorization_status ? 'readonly' : '';

?>

<form method="post" action="" id="">
	<h2><?php _e('MS Outlook Settings', 'bookings-and-appointments-for-woocommerce') ?></h2>

	<table class="form-table">
		<tr>
			<th>
				<label for="enable_outlook"><?php _e('MS Outlook Event Sync', 'bookings-and-appointments-for-woocommerce'); ?></label>
			</th>
			<td>
				<input type="checkbox" name="enable_outlook" id="enable_outlook" <?php echo ($settings['enable_outlook'] ? 'checked' : '') ?>>
				<span><?php _e('Enabling this option will allow you to sync booking events to your MS Outlook calendar', 'bookings-and-appointments-for-woocommerce'); ?></span>
			</td>
		</tr>
		<tr class="outlook-wrapper">
			<th>
				<label for="client_id">
					<?php _e('Client ID', 'bookings-and-appointments-for-woocommerce') ?>
				</label>
			</th>
			<td>
				<input type="text" name="client_id" id="ph-outlook-client-id" value="<?php echo $settings['client_id'] ?>" <?php echo $readonly_status ?>>
			</td>
		</tr>
		<tr class="outlook-wrapper">
			<th>
				<label for="client_secret">
					<?php _e('Client Secret', 'bookings-and-appointments-for-woocommerce') ?>
				</label>
			</th>
			<td>
				<input type="password" name="client_secret" id="ph-outlook-client-secret" value="<?php echo $settings['client_secret'] ?>" <?php echo $readonly_status ?>>
			</td>
		</tr>
		<tr class="outlook-wrapper">
			<th>
				<label for="redirect_uri">
					<?php _e('Redirect URI', 'bookings-and-appointments-for-woocommerce') ?>
				</label>
			</th>
			<td>
				<div class="ph-outlook-redirect-uri-wrapper">
					<input type="text" name="redirect_uri" class="ph-outlook-redirect-url" value="<?php echo admin_url() ?>" readonly>
					<img width="20" height="20" src="https://img.icons8.com/ios-glyphs/30/copy.png" alt="copy" class="ph-copy-outlook-redirect-url"/>
					<b><span class="ph-copied-text"></span></b>
				</div>
			</td>
		</tr>
		<tr class="outlook-wrapper">
			<th>
				<label for="redirect_uri">
					<?php _e('Connection Status', 'bookings-and-appointments-for-woocommerce') ?>
				</label>
			</th>
			<td class="ph-outlook-form-element-wrapper">
				<?php
				if (!$authorization_status || empty($settings['client_id']) || empty($settings['client_secret'])) {
				?>
					<button class="button" id="signin_to_outlook">
						<?php _e('Sign in to MS Outlook', 'bookings-and-appointments-for-woocommerce') ?>
					</button>
				<?php
				} else {
				?>
					<button class="button" id="deactivate_outlook_authorization">
						<?php _e('Deactivate & Re-Authorize', 'bookings-and-appointments-for-woocommerce') ?>
					</button>
				<?php
				}
				?>
				</button>
				<?php 
					if ($authorization_status && !empty($settings['client_id']) && !empty($settings['client_secret'])) {
						echo '<span class="dashicons-before dashicons-yes-alt ph-outlook-auth-status" style="align-self:center;color:green">Active</span>';
					}
				?>
			</td>
		</tr>

		<?php if ($authorization_status && !empty($settings['client_id']) && !empty($settings['client_secret'])) { ?>

		<tr class="outlook-wrapper">
			<th>
				<label for="outlook_calendar_id">
					<?php _e( 'Outlook Calendar for Bookings', 'bookings-and-appointments-for-woocommerce' ); ?>
				</label>
			</th>
			<td>
				<select name="outlook_calendar_id" id="outlook_calendar_id">
					<option value=""><?php _e( 'Select Outlook Calendar', 'bookings-and-appointments-for-woocommerce' ); ?></option>
					
					<?php foreach($ph_outlook_calendars as $cal) {

						if (isset($cal['id']) && isset($cal['name'])) {

							echo '<option value="' . $cal['id'] . '"' . ($ph_selected_calendar == $cal['id'] ? 'selected' : '') . '>' . $cal['name'] . '</option>';
						}
					}?>
				</select>

				<button class="button" id="ph-fetch-outlook-calendars">
					<?php _e('Refresh Calendar List', 'bookings-and-appointments-for-woocommerce') ?>
				</button>
				<br>
				<p><i><?php
					echo __( 'Choose the Outlook calendar to which bookings should be synchronized.', 'bookings-and-appointments-for-woocommerce' );
				?></i></p>
				
			</td>
		</tr>

		<?php } ?>

		<tr class="outlook-wrapper">
			<th>
				<label for="outlook_event_filter">
					<?php _e( 'Booking Status Filter', 'bookings-and-appointments-for-woocommerce' ); ?>
				</label>
			</th>
			<td>
				<select multiple name="outlook_event_filter[]" id="outlook_event_filter">
					
					<?php foreach($ph_booking_status as $key => $value) {

						echo '<option value="' . $key . '"' . ( in_array($key, $ph_outlook_event_filter) ? 'selected' : '') . '>' . $value . '</option>';
					}?>
				</select>
				<br>
				<p><i><?php
					echo __( 'Select the booking statuses to sync with your Outlook calendar. Leave empty to synchronize all booking statuses.', 'bookings-and-appointments-for-woocommerce' );
				?></i></p>
			</td>
		</tr>

		<tr class="outlook-wrapper">
			<th>
				<label for="event_title">
					<?php _e('Customize Event Title', 'bookings-and-appointments-for-woocommerce'); ?>
				</label>
			</th>
			<td>
				<div style="float:left;">
					<b>Order: #[order_id], Order Item: #[order_item_id] </b>
				</div>
				<br>
				<div class="" style="float:left;width:50%;">
					<textarea
						type="textarea"
						name="event_title"
						id="event_title"
						style="width: 100%;height: 120px;"><?php echo $settings['event_title'] ?></textarea>
				</div>
				<div style="float:left;width:40%;font-size:12px;margin-left:10px;">
					<i>
						<?php _e('Use the following Tags to customize your MS Outlook Event Title.', 'bookings-and-appointments-for-woocommerce'); ?>
						<br>
						<span>
							<?php _e("For eg; if you want to mention customer’s name in the title then choose [CUSTOMER_NAME] tag.", "bookings-and-appointments-for-woocommerce") ?>
						</span>
					</i>
					<br>[RESOURCE]<br>[PARTICIPANT]<br>[CUSTOMER_NAME]<br>[PRODUCT_NAME]<br>[BOOKING_STATUS]<br>[ASSET]
				</div>
			</td>
		</tr>
		<tr class="outlook-wrapper">
			<th>
				<label for="event_description">
					<?php _e('Customize Event Details', 'bookings-and-appointments-for-woocommerce'); ?>
				</label>
			</th>
			<td class="forminp forminp-checkbox">
				<div class="" style="float:left;width:50%;">
					<textarea type="textarea" name="event_description" id="event_description" placeholder="<?php echo $default_event_description ?>" style="width: 100%;" rows="8"><?php echo $settings['event_description'] ?></textarea>
				</div>
				<div style="float:left;width:40%;font-size:12px;margin-left:10px;">
					<i>
						<?php _e('The tags/information mentioned in box will be displayed in your MS Outlook Calendar.
						You have an option to remove any tags that you do not wish to add to your iCalendar.
						Similarly, you could add any tags that are missing from the list given below.', 'bookings-and-appointments-for-woocommerce')
						?>
					</i>
					<br>
					[PARTICIPANT]
					<br>
					[ASSET]
					<br>
					[RESOURCE]
					<br>
					[CUSTOMER_NAME]
					<br>
					[CUSTOMER_PHONE]
					<br>
					[CUSTOMER_EMAIL]
					<br>
					[PRODUCT_NAME]
					<br>
					[BOOKING_STATUS]
					<br>
					[BOOKING_COST]
					<br>
					[BILLING_ADDRESS]
					<br>
					[BOOKING_NOTES]
				</div>
			</td>
		</tr>
		<tr class="outlook-wrapper">
			<th>
				<label for="event_attendee"><?php _e('Add Customer as Event Attendee', 'bookings-and-appointments-for-woocommerce'); ?></label>
			</th>
			<td>
				<input type="checkbox" name="event_attendee" id="event_attendee" <?php echo ($settings['event_attendee'] ? 'checked' : '') ?>>
				<span><?php _e('When enabled, the customer will be added as an attendee for the event in Outlook Calendar, and Outlook will send an email invitation to the customer.', 'bookings-and-appointments-for-woocommerce'); ?></span>
			</td>
		</tr>
		<tr class="outlook-wrapper">
			<th>
				<label for="enable_debug"><?php _e('Enable Debug Mode', 'bookings-and-appointments-for-woocommerce'); ?></label>
			</th>
			<td>
				<input type="checkbox" name="enable_debug" id="enable_debug" <?php echo ($settings['debug'] ? 'checked' : '') ?>>
				<span><?php _e('Enable', 'bookings-and-appointments-for-woocommerce'); ?></span>
			</td>
		</tr>
	</table>

	<br />
	<button name="save_outlook_settings" class="button-primary woocommerce-save-button">
		<?php _e('Save Changes', 'bookings-and-appointments-for-woocommerce') ?>
	</button>
</form>