<?php
$gcalendar_settings = get_option( $this->id.'google_calendar', 1 );
$default_summary="[PRODUCT_NAME]([BOOKING_STATUS])";
$default_calendar_details = "<br><br><strong>Customer Details</strong><br>[CUSTOMER_NAME]<br>[CUSTOMER_PHONE]<br>[CUSTOMER_EMAIL]<br><br><strong>Booking Details</strong><br>[BOOKING_COST]<br>[PARTICIPANT]<br>[ASSET]<br>[RESOURCE]<br><br>[ORDER_PAGE_LINK]";

if ( ! empty( $_POST['ph_booking_settings_google_calender_sumitted'] ) ) {
	$settings = array(
		'google_calendar_enable' 	=> isset($_POST['google_calendar_enable']) ? 'yes' : 'no',
		'google_calendar_id' 		=> isset($_POST['google_calendar_id']) ? $_POST['google_calendar_id'] : '',
		'google_client_id' 			=> isset($_POST['google_client_id']) ? $_POST['google_client_id'] : '',
		'google_client_secret' 		=> isset($_POST['google_client_secret']) ? $_POST['google_client_secret'] : '',
		'google_calendar_frondend' 	=> isset($_POST['google_calendar_frondend']) ? 'yes' : 'no',
		'google_calendar_debug' 	=> isset($_POST['google_calendar_debug']) ? 'yes' : 'no',
		'google_calendar_summary' 	=> isset($_POST['google_calendar_summary']) ? $_POST['google_calendar_summary'] : $default_summary,
		'google_calendar_details' 	=> isset($_POST['google_calendar_details']) ? $_POST['google_calendar_details'] : $google_calendar_details,
		'google_cal_event_filter'	=> $_POST['google_cal_event_filter'] ?? [],
		'google_calendars'			=> $gcalendar_settings['google_calendars'] ?? [],
	);
	update_option( $this->id.'google_calendar', $settings );

}

if ( isset( $_POST['ph_bookings_start_stop_two_way_sync'] )  ) {
	$settings = array(
		'ph_booking_google_calender_two_way_sync' 	=> isset($_POST['ph_bookings_start_stop_two_way_sync'])?$_POST['ph_bookings_start_stop_two_way_sync']:0
	);
	update_option( $this->id.'google_calendar_two_way_sync', $settings );

}
if ( isset( $_POST['ph_bookings_two_way_sync_save'] )  ) {
	$settings = array(
		'ph_booking_google_calender_two_way_sync' 	=> isset($_POST['ph_booking_google_calender_two_way_sync_status'])?$_POST['ph_booking_google_calender_two_way_sync_status']:0,
		'ph_booking_two_way_sync_interval' => isset($_POST['ph_booking_two_way_sync_interval'])?$_POST['ph_booking_two_way_sync_interval']:60
	);
	update_option( $this->id.'google_calendar_two_way_sync', $settings );

}

$gcalendar_settings = get_option( $this->id.'google_calendar', 1 );
$gcalendar_two_way_sync_settings = get_option( $this->id.'google_calendar_two_way_sync', 1 );
$gcalendar_obj = new phive_booking_google_calendar();

$ph_google_calendars		= $gcalendar_settings['google_calendars'] ?? [];
$ph_google_cal_event_filter = $gcalendar_settings['google_cal_event_filter'] ?? [];
$ph_synced_google_cal 		= $gcalendar_obj->is_google_calendars_synced();

$ph_booking_status = array(
	'paid'                  => __( 'Paid', 'bookings-and-appointments-for-woocommerce' ),
	'un-paid'               => __( 'Unpaid', 'bookings-and-appointments-for-woocommerce' ),
	'canceled'              => __( 'Cancelled', 'bookings-and-appointments-for-woocommerce' ),
	'requires-confirmation' => __( 'Requires Confirmation', 'bookings-and-appointments-for-woocommerce' ),
	'refunded'              => __( 'Refunded', 'bookings-and-appointments-for-woocommerce' ),
);

?>

<form method="post" action="" id="">
	<h2><?php _e('Google Calendar Settings','bookings-and-appointments-for-woocommerce');?></h2>
	<ul style="list-style-type: disc;padding-left:1.3em">
		<li>
			<?php _e('Read more to know how to set up ','bookings-and-appointments-for-woocommerce');?><a href="https://www.pluginhive.com/knowledge-base/sync-woocommerce-bookings-with-your-google-calendar?utm_source=bookings&utm_medium=plugin_settings"  target="_blank"><?php _e('Google Calendar Sync','bookings-and-appointments-for-woocommerce')?></a>.
		</li>
		<li>
			<?php _e('Read more to know how to ','bookings-and-appointments-for-woocommerce');?><a href="https://www.pluginhive.com/knowledge-base/troubleshooting-google-calendar-sync-woocommerce-bookings-and-appointments-plugin/?utm_source=bookings&utm_medium=plugin_settings"  target="_blank"><?php _e('Troubleshoot Google Calendar Sync','bookings-and-appointments-for-woocommerce')?></a>.
		</li>
		<li>
			<?php _e('Read more to know the ','bookings-and-appointments-for-woocommerce');?><a href="https://www.pluginhive.com/knowledge-base/woocommerce-bookings-and-appointments-plugin-faqs/?utm_source=bookings&utm_medium=plugin_settings#GoogleSync"  target="_blank"><?php _e('Frequently asked questions about Google Calendar Sync','bookings-and-appointments-for-woocommerce')?></a>.
		</li>
	</ul>
	<input type="hidden" name="ph_booking_settings_google_calender_sumitted" value="1" />
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="google_calendar_enable"><?php _e('Enable Google Calendar Sync','bookings-and-appointments-for-woocommerce');?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="google_calendar_enable" id="google_calendar_enable" <?php echo isset($gcalendar_settings['google_calendar_enable']) && $gcalendar_settings['google_calendar_enable']=='yes' ? 'checked' : '';?> >
				<span><?php _e('Enable','bookings-and-appointments-for-woocommerce');?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="google_client_id"><?php _e('Client ID','bookings-and-appointments-for-woocommerce');?>
					<?php echo wc_help_tip( __( 'Enter with your Client id. You can find this under Credentials.', 'bookings-and-appointments-for-woocommerce' ) )?>
				</label>
			</th>
			<td class="forminp forminp-text">
				<input name="google_client_id" id="google_client_id" type="text" value="<?php echo isset($gcalendar_settings['google_client_id']) ? $gcalendar_settings['google_client_id'] : '';?>" placeholder="" autocomplete="off">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="google_client_secret"><?php _e('Client Secret','bookings-and-appointments-for-woocommerce');?>
					<?php echo wc_help_tip( __( 'Enter with your Client Secret. You can find this under Credentials.', 'bookings-and-appointments-for-woocommerce' ) )?>
				</label>
			</th>
			<td class="forminp forminp-text">
				<input name="google_client_secret" id="google_client_secret" type="password" value="<?php echo isset($gcalendar_settings['google_client_secret']) ? $gcalendar_settings['google_client_secret'] : '';?>" placeholder="" autocomplete="off">
			</td>
		</tr>
			<?php $gcalendar_obj->generate_validate_google_caledar_credentials_html()?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="google_calendar_frondend"><?php _e('Google calendar sync for customers','bookings-and-appointments-for-woocommerce');?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="google_calendar_frondend" id="google_calendar_frondend" <?php echo isset($gcalendar_settings['google_calendar_frondend']) && $gcalendar_settings['google_calendar_frondend']=='yes' ? 'checked' : '';?> >
				<span><?php _e('Enabling this option will allow your customers to add the booking as an event to their google calendar','bookings-and-appointments-for-woocommerce');?></span>
			</td>
		</tr>

		<?php if ($ph_synced_google_cal) { ?>

		<tr valign="top">
			<th>
				<label for="google_calendar_id">
					<?php _e( 'Google Calendar for Bookings', 'bookings-and-appointments-for-woocommerce' ); ?>
				</label>
			</th>
			<td>
				<select name="google_calendar_id" id="google_calendar_id">
					<option value=""><?php _e( 'Select Google Calendar', 'bookings-and-appointments-for-woocommerce' ); ?></option>
					
					<?php foreach($ph_google_calendars as $cal) {

						if (isset($cal['id']) && isset($cal['name'])) {

							echo '<option value="' . $cal['id'] . '"' . ($gcalendar_settings['google_calendar_id'] == $cal['id'] ? 'selected' : '') . '>' . $cal['name'] . '</option>';
						}
					}?>
				</select>
				<button class="button" id="ph-fetch-google-calendars">
					<?php _e('Refresh Calendar List', 'bookings-and-appointments-for-woocommerce') ?>
				</button>
				<br>

				<p><i><?php
					echo __( 'Choose the Google calendar to which bookings should be synchronized.', 'bookings-and-appointments-for-woocommerce' );
				?></i></p>
			</td>
		</tr>

		<tr valign="top">
			<th>
				<label for="google_cal_event_filter">
					<?php _e( 'Booking Status Filter', 'bookings-and-appointments-for-woocommerce' ); ?>
				</label>
			</th>
			<td>
				<select multiple name="google_cal_event_filter[]" id="google_cal_event_filter">
					
					<?php foreach($ph_booking_status as $key => $value) {

						echo '<option value="' . $key . '"' . ( in_array($key, $ph_google_cal_event_filter) ? 'selected' : '') . '>' . $value . '</option>';
					}?>
				</select>
				<br>
				<p><i><?php
					echo __( 'Select the booking statuses to sync with your Google calendar. Leave empty to synchronize all booking statuses.', 'bookings-and-appointments-for-woocommerce' );
				?></i></p>
			</td>
		</tr>

		<?php } ?>



		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="google_calendar_summary"><?php _e('Customize Calendar Event Title','bookings-and-appointments-for-woocommerce');?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<div class="" style="float:left;">
					<b>Order: #[order_id], Order Item: #[order_item_id] </b>
				</div>
				<br>
				<div class="" style="float:left;width:50%;">
					<textarea type="textarea" name="google_calendar_summary" id="google_calendar_summary"  style="width: 100%;height: 120px;"><?php echo isset($gcalendar_settings['google_calendar_summary']) ? $gcalendar_settings['google_calendar_summary'] : $default_summary;?></textarea>
				</div>
				<div style="float:left;width:40%;font-size:12px;margin-left:10px;"> <i><?php _e('Use the following Tags to customize your Google Calendar Event Title.','bookings-and-appointments-for-woocommerce');?> 
				<br><?php _e("For eg; if you want to mention customer’s name in the title then choose [CUSTOMER_NAME] tag.", "bookings-and-appointments-for-woocommerce") ?></i>
				<br>[RESOURCE]<br>[PARTICIPANT]<br>[CUSTOMER_NAME]<br>[PRODUCT_NAME]<br>[BOOKING_STATUS]<br>[ASSET]
				</div>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="google_calendar_summary"><?php _e('Customize Calendar Event Details','bookings-and-appointments-for-woocommerce');?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<div class="" style="float:left;width:50%;">
					<textarea type="textarea" name="google_calendar_details" id="google_calendar_details"  style="width: 100%;" rows="8"><?php echo (isset($gcalendar_settings['google_calendar_details']) && !empty($gcalendar_settings['google_calendar_details'])) ? $gcalendar_settings['google_calendar_details'] : $default_calendar_details;?></textarea>
				</div>
				<div style="float:left;width:40%;font-size:12px;margin-left:10px;"> <i><?php _e("The tags/information mentioned in box will be synced and displayed in your calendar. You have an option to remove any tags that you do not wish to sync with your calendar. Similarly, you could add any tags that are missing from the list given below.  Please ensure you maintain the html tags for each detail so that the formatting is maintained.", "bookings-and-appointments-for-woocommerce") ?></i>
				<br>[PARTICIPANT]<br>[ASSET]<br>[RESOURCE]<br>[CUSTOMER_NAME]<br>[CUSTOMER_PHONE]<br>[CUSTOMER_EMAIL]<br>[PRODUCT_NAME]<br>[BOOKING_STATUS]<br>[BOOKING_COST]<br>[ORDER_PAGE_LINK]<br>[BILLING_ADDRESS]<br>[BOOKING_NOTES]<br>[LOCATION]<br>[NAME]
				</div>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="google_calendar_debug"><?php _e('Debug log','bookings-and-appointments-for-woocommerce');?></label>
			</th>
			<td class="forminp forminp-checkbox">
				<input type="checkbox" name="google_calendar_debug" id="google_calendar_debug" <?php echo isset($gcalendar_settings['google_calendar_debug']) && $gcalendar_settings['google_calendar_debug']=='yes' ? 'checked' : '';?> >
				<span><?php _e('Enable this option only to troubleshoot issues in authentication or calendar syncing','bookings-and-appointments-for-woocommerce');?></span>
			</td>
		</tr>
	</table>
	<p class="submit">
		<button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes"><?php _e('Save changes','bookings-and-appointments-for-woocommerce');?></button>
	</p>

</form>

<hr>
<form method="post" action="" id="">
	<h2><?php _e('Google calendar Two Way Sync','bookings-and-appointments-for-woocommerce');?></h2>
	<input type="hidden" name="ph_booking_google_calender_two_way_sync_status" value="<?php echo isset($gcalendar_two_way_sync_settings['ph_booking_google_calender_two_way_sync']) ? $gcalendar_two_way_sync_settings['ph_booking_google_calender_two_way_sync'] : 0;?>" />
	
	<?php
		if(isset($gcalendar_two_way_sync_settings['ph_booking_google_calender_two_way_sync']) && $gcalendar_two_way_sync_settings['ph_booking_google_calender_two_way_sync']==1 )
		{?>
			<button name="ph_bookings_start_stop_two_way_sync" class="button woocommerce-save-button" type="submit" value="0" style="background-color:red;background:red;border:red;color:white;"><?php _e('Stop Two Way Sync','bookings-and-appointments-for-woocommerce');?></button>
			<table class="form-table">
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="google_calendar_enable"><?php  _e('Time Interval','bookings-and-appointments-for-woocommerce');?></label>
					</th>
					<td class="forminp forminp-checkbox">
						<input type="text" name="ph_booking_two_way_sync_interval" id="ph_booking_two_way_sync_interval" value="<?php echo isset($gcalendar_two_way_sync_settings['ph_booking_two_way_sync_interval']) && !empty($gcalendar_two_way_sync_settings['ph_booking_two_way_sync_interval']) ? $gcalendar_two_way_sync_settings['ph_booking_two_way_sync_interval'] : 60;?>" >
						<span><?php  _e('interval in seconds','bookings-and-appointments-for-woocommerce');?></span>
					</td>
				</tr>
				<tr>

				</tr>
				 <!-- <tr valign="top">
					<th scope="row" class="titledesc">
						<label for="google_calendar_debug"></label>
					</th>
					<td class="forminp forminp-checkbox">
						<input type="checkbox" name="google_calendar_debug" id="google_calendar_debug"  >
						<span></span>
					</td>
				</tr>  -->
			</table> 
			<p class="submit">
				<button name="ph_bookings_two_way_sync_save" class="button-primary woocommerce-save-button" type="submit" value="Save Interval"><?php _e('Save Interval','bookings-and-appointments-for-woocommerce');?></button>
			</p>
			<button name="ph_bookings_manually_sync" class="button woocommerce-save-button" type="submit" value="1" style=""><?php _e('Manually Sync','bookings-and-appointments-for-woocommerce');?></button>
			<br>

		<?php	
				if ($scheduled_timestamp = wp_next_scheduled('ph_bookings_two_way_sync_cron')) {
				    $scheduled_desc = sprintf(__('The next import is scheduled on <code>%s</code>', 'bookings-and-appointments-for-woocommerce'), get_date_from_gmt(date('Y-m-d H:i:s', $scheduled_timestamp), wc_date_format() . ' ' . wc_time_format()));
				} else {
				    $scheduled_desc = __('There is no import scheduled.', 'bookings-and-appointments-for-woocommerce');
				}
				echo '<p>'.$scheduled_desc.'</p>';
			}
		else{?>
			<button name="ph_bookings_start_stop_two_way_sync" class="button-primary woocommerce-save-button" type="Submit" value="1"><?php _e('Start Two Way Sync','bookings-and-appointments-for-woocommerce');?></button>
		<?php }
		?>
		<br>
	
</form>

