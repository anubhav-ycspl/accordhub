jQuery(document).ready(function ($) {
	jQuery('._tax_status_field').closest('.show_if_simple').addClass('show_if_phive_booking').show();

	//yith points and rewards settings on product general setting start
	jQuery('._ywpar_max_point_discount_field').addClass('show_if_phive_booking').show();
	jQuery('._ywpar_point_earned_field').addClass('show_if_phive_booking').show();
	jQuery('.ywpar_point_earned_dates_fields').addClass('show_if_phive_booking').show();
	jQuery('._ywpar_redemption_percentage_discount_field').addClass('show_if_phive_booking').show();
	//yith points and rewards settings on product general setting end

	/***********************************************************************
	**** ADMIN PRODUCT PAGE (html-ph-booking-product-admin-options.php) ****
	************************************************************************/
	toggle_interval_type();
	toggle_related_to_interval_period();
	toggle_related_to_cancel_interval_period();
	toggle_related_to_additional_notes();
	toggle_related_to_admin_order_submit();
	toggle_booking_start_day();
	toggle_related_to_enable_buffer();
	toggle_related_to_custom_date_range();
	toggle_charge_per_night_option();
	toggle_icalendar_settings();
	toggle_outlook_settings();

	// #105168 - allow customers to set maximum participant in a booking even if consider each participant as separate booking is active.
	// toggle_maxparticipant_box();
	ph_max_participant_warning();

	toggle_across_the_day_booking();
	toggle_end_time_display();

	toggle_enable_participant_box();
	$('#_phive_booking_person_enable').click(function () {
		toggle_enable_participant_box();
	});

	$("#_phive_book_interval_type").change(function () {
		toggle_interval_type();

	});

	$('#_phive_booking_persons_as_booking').click(function () {
		// toggle_maxparticipant_box();
		// #105168
		ph_max_participant_warning();
	});

	function toggle_enable_participant_box() {
		if (!$('#_phive_booking_person_enable').prop('checked')) {
			$('#_phive_booking_minimum_number_of_required_participant').val(0);
		}
	}


	toggle_booking_from_type();
	$("#_phive_fixed_availability_from").change(function () {
		toggle_booking_from_type();

	});
	function toggle_booking_from_type() {
		if ($('#_phive_fixed_availability_from').val() == '') {

			$('#_phive_first_booking_availability_type').attr('disabled', false);
		}
		else {
			$('#_phive_first_booking_availability_type').val('today');
			$('#_phive_first_booking_availability_type').attr('disabled', true);
		}
	}

	function toggle_maxparticipant_box() {
		if ($('#_phive_booking_persons_as_booking').prop('checked')) {
			$('#_phive_booking_maximum_number_of_allowed_participant').val('');
			$('._phive_booking_maximum_number_of_allowed_participant_field').hide();
			// $('#_phive_booking_minimum_number_of_required_participant').val(0);
			// $('._phive_booking_minimum_number_of_required_participant_field').hide();
		}
		else {
			$('._phive_booking_maximum_number_of_allowed_participant_field').show();
			// $('._phive_booking_minimum_number_of_required_participant_field').show();
		}
	}
	$("#_phive_book_interval_period").change(function () {
		toggle_related_to_interval_period();
		$('#_phive_enable_buffer').prop('checked', false);
		$('#_phive_buffer_before').val('0');
		$('#_phive_buffer_after').val('0');
		$('#ph_buffer_before').hide();
		$('#ph_buffer_after').hide();
		toggle_related_to_custom_date_range();
		toggle_across_the_day_booking();
		toggle_end_time_display();
		hide_time_range_availability_option();
	});

	// No need to show time range options for day and month interval in product availability
	hide_time_range_availability_option();
	function hide_time_range_availability_option() {
		if($('#_phive_book_interval_period').val() == 'day' || $('#_phive_book_interval_period').val() == 'month'){
			$(".ph_booking_availability_type optgroup").hide();
		}else{
			$(".ph_booking_availability_type optgroup").show();
		}

		// No need to show range of days option for month interval in product availability
		if($('#_phive_book_interval_period').val() == 'month'){
			$(".ph_booking_availability_type option[value=days").hide();
		}else{
			$(".ph_booking_availability_type option[value=days").show();
		}
	}

	// Hide Number of Seats/Places left based on maximum booking limit per block
	hide_number_of_seats_left();
	$('#_phive_book_allowed_per_slot').change(function () {
		hide_number_of_seats_left();
	});

	function hide_number_of_seats_left() {
		if ($("#_phive_book_allowed_per_slot").val() == "1" || $("#_phive_book_allowed_per_slot").val() == "") {
			$('#_phive_display_bookings_capacity').prop('checked', false);
			$('._phive_display_bookings_capacity_field').hide();
			$('._phive_remainng_bokkings_text_field').hide();
			$('#_ph_display_bookings_fully_booked_indicator').prop('checked', false);
			$('._ph_display_bookings_fully_booked_indicator_field').hide();
			$('._ph_booking_fully_booked_indicator_text_field').hide();
		}
		else {
			$('._phive_display_bookings_capacity_field').show();
			$('._ph_display_bookings_fully_booked_indicator_field').show();
			remaining_block_text();
			ph_toggle_fully_booked_indicator_text_field();
		}
	}
	$('#_phive_display_bookings_capacity').click(function () {
		hide_number_of_seats_left();
	});
	function remaining_block_text() {

		if ($('#_phive_display_bookings_capacity').is(":checked")) {
			$('._phive_remainng_bokkings_text_field').show();
		}
		else {
			$('._phive_remainng_bokkings_text_field').hide();
		}
	}

	$('#_ph_display_bookings_fully_booked_indicator').click(function () {
		ph_toggle_fully_booked_indicator_text_field();
	});

	function ph_toggle_fully_booked_indicator_text_field() {

		if ($('#_ph_display_bookings_fully_booked_indicator').is(":checked")) {
			$('._ph_booking_fully_booked_indicator_text_field').show();
		} else {
			$('._ph_booking_fully_booked_indicator_text_field').hide();
		}
	}

	hide_auto_select_min_block();
	$('#_phive_book_min_allowed_booking').change(function () {
		hide_auto_select_min_block();
	});

	function hide_auto_select_min_block() {
		if ($("#_phive_book_min_allowed_booking").val() == "1" || $("#_phive_book_min_allowed_booking").val() == "" || $("#_phive_book_interval_type").val() == 'fixed') {
			$('._phive_auto_select_min_booking_field').hide();
		}
		else {
			if ($('.calendar_design').val() == '3') {
				$('._phive_auto_select_min_booking_field').hide();
				$('#_phive_auto_select_min_booking').prop('checked', false);
			}
			else if ($("#_phive_book_interval_type").val() != 'fixed') {
				$('._phive_auto_select_min_booking_field').show();
			}
		}
	}

	$("#_phive_booking_persons_as_booking").change(function () {
		var persons_rule_min = $('.ph_booking_persons_rule_min').val();
		var persons_rule_max = $('.ph_booking_persons_rule_max').val();
		regex = $('#_phive_book_allowed_per_slot').val();
		error = 'exceed_booking';

		if (persons_rule_min > regex) {
			$('.ph_booking_persons_rule_min').val(regex);
		}
		if (persons_rule_max > regex) {
			$('.ph_booking_persons_rule_max').val(regex);
		}

	});
	$("#ph_booking_order_new").change(function () {
		toggle_related_to_admin_order_submit();
	});
	$("#ph_booking_order_existing").change(function () {
		toggle_related_to_admin_order_submit();
	});
	$("#ph_booking_order_id").focusout(function () {
		toggle_related_to_admin_order_submit();
	});

	//#107352
	$('#ph_booking_order_id').keyup(function(){
		toggle_related_to_admin_order_submit();
	});

	$("#_phive_additional_notes_label").attr('maxlength', '50');

	$('#_phive_enable_across_the_day').change(function(){
		toggle_phive_across_the_day_cost_calculation();
	});

	toggle_product_view();
	$('#ph_enable_search_widget').on('change', function () {
		toggle_product_view();
	});

	function toggle_product_view() {
		if($('#ph_enable_search_widget').is(':checked')) {
			$('#ph_search_product_view').closest('tr').show()
		} else {
			$('#ph_search_product_view').closest('tr').hide()
		}
	}

	function toggle_phive_across_the_day_cost_calculation(){
		if($('#_phive_enable_across_the_day').is(':not(:checked)')){
			$("._phive_across_the_day_cost_calculation").hide();
		}else{
			$("._phive_across_the_day_cost_calculation").show();
		}
	}

	function toggle_across_the_day_booking() {

		if ($("#_phive_book_interval_period").val() == 'minute' || $("#_phive_book_interval_period").val() == 'hour') {
			if ($("#_phive_book_interval_type").val() == 'customer_choosen') {
				$("._phive_enable_across_the_day").show();
				toggle_phive_across_the_day_cost_calculation();
			}
			else {
				$("._phive_enable_across_the_day").hide();
				$("._phive_across_the_day_cost_calculation").hide();
			}
		} else {
			$("._phive_enable_across_the_day").hide();
			$("._phive_across_the_day_cost_calculation").hide();
		}

	}
	function toggle_end_time_display() {

		if ($("#_phive_book_interval_period").val() == 'minute' || $("#_phive_book_interval_period").val() == 'hour') {

			if ($('.calendar_design').val() == '3') {
				$('._phive_enable_end_time_display').hide();
				$('#_phive_enable_end_time_display').prop('checked', false);
			}
			else {
				$("._phive_enable_end_time_display").show();
			}
		} else {
			$("._phive_enable_end_time_display").hide();
		}

	}
	function toggle_related_to_admin_order_submit() {
		var isNewBookingChecked = $('#ph_booking_order_new').prop('checked');
		var isExistingBookingChecked = $('#ph_booking_order_existing').prop('checked');
		var ExistingOrderId = $('#ph_booking_order_id').val();
		if (isNewBookingChecked || isExistingBookingChecked) {
			if (isExistingBookingChecked && ((ExistingOrderId).length == 0)) {
				$("#ph_create_booking").attr('disabled', 'disabled');

			} else if (isExistingBookingChecked && (ExistingOrderId).length > 0) {
				$("#ph_create_booking").removeAttr("disabled");

			} else {
				$("#ph_create_booking").removeAttr("disabled");
			}

		}
		else {

			$("#ph_create_booking").attr('disabled', 'disabled');
		}
	}

	function toggle_related_to_interval_period() {

		if ($("#_phive_book_interval_period").val() == 'minute' || $("#_phive_book_interval_period").val() == 'hour') {
			$("._phive_book_working_hour_start_field").show();
			$("._phive_book_working_hour_end_field").show();
			$(".ph_bookings_admin_time_start_end_time_settings").show();
			if ($("#_phive_book_interval_period").val() == 'minute') {
				$('._phive_buffer_period').html(phive_booking_locale.minute_text);
				$('#_phive_buffer_period').val('minute');
			}
			else {
				$('._phive_buffer_period').html(phive_booking_locale.hour_text);
				$('#_phive_buffer_period').val('hour');
			}
		} else {
			$("._phive_book_working_hour_start_field").hide();
			$("._phive_book_working_hour_end_field").hide();
			$(".ph_bookings_admin_time_start_end_time_settings").hide();
		}

		if ($("#_phive_book_interval_period").val() == 'day') {
			if ($("#_phive_book_interval_type").val() == 'customer_choosen') {
				$("._phive_book_charge_per_night_field").show();
			}

			/*$("._phive_book_checkin_field").show();
			$("._phive_book_checkout_field").show();*/
			$('._phive_buffer_period').html(phive_booking_locale.day_text);
			$('#_phive_buffer_period').val('day');
		} else {
			$("._phive_book_charge_per_night_field").hide();
			/*$("._phive_book_checkin_field").hide();
			$("._phive_book_checkout_field").hide();*/
		}

		if ($("#_phive_book_interval_period").val() == 'day') {
			$("#_phive_restrict_start_day").closest("div").show();
			$("#ph_start_day_related").show();
		} else {
			$("#_phive_restrict_start_day").closest("div").hide();
			$("#ph_start_day_related").hide();
		}

		toggle_related_to_enable_buffer();
	}

	$("#_phive_book_charge_per_night").change(function () {

		toggle_charge_per_night_option();
	});

	function toggle_charge_per_night_option() {

		if ($("#_phive_book_charge_per_night").prop('checked') == true) {

			$("._phive_book_charge_per_night_option_field").show();
		} else {
			$("._phive_book_charge_per_night_option_field").hide();
		}
	}
	function toggle_interval_type() {

		if ($("#_phive_book_interval_type").val() == 'customer_choosen') {
			$(".for-type-customer-choosen").closest(".form-field").show();
			$("#_phive_book_charge_per_night").closest(".form-field").show();
			if ($("#_phive_book_interval_period").val() == 'day' || $("#_phive_book_interval_period").val() == 'month') {
				$("._phive_enable_across_the_day").hide();
				$("._phive_enable_end_time_display").hide();
				$("._phive_across_the_day_cost_calculation").hide();
			}
			else {
				$("._phive_enable_across_the_day").show();
				$("._phive_enable_end_time_display").show();
				toggle_phive_across_the_day_cost_calculation();
			}
		} else {
			$("#_phive_book_charge_per_night").closest(".form-field").hide();
			//change the value to no when interval type is not customer choosen
			$('#_phive_book_charge_per_night').attr('checked', false);
			$(".for-type-customer-choosen").closest(".form-field").hide();
			if ($("#_phive_book_interval_period").val() == 'minute' || $("#_phive_book_interval_period").val() == 'hour') {
				$("._phive_enable_end_time_display").show();
			}
		}
	}

	/******* cancellation period **********/
	$("#_phive_book_allow_cancel").change(function () {
		toggle_related_to_cancel_interval_period();
	});

	function toggle_related_to_cancel_interval_period() {
		if ($('#_phive_book_allow_cancel').is(":checked")) {
			$('#_phive_cancellation_period').show();
		}
		else {
			$('#_phive_cancellation_period').hide();
		}
	}
	/******* Additional Notes ***********/
	$("#_phive_book_additional_notes").change(function () {
		toggle_related_to_additional_notes();
	});

	function toggle_related_to_additional_notes() {
		if ($('#_phive_book_additional_notes').is(":checked")) {
			$("._phive_additional_notes_fields").show();
		}
		else {
			$("._phive_additional_notes_fields").hide();
		}
	}

	/*****************  Buffer Enable    ****************/
	$("#_phive_enable_buffer").change(function () {
		toggle_related_to_enable_buffer();
	});
	function toggle_related_to_enable_buffer() {
		if ($("#_phive_book_interval_period").val() != 'month') {
			$("#_phive_enable_buffer_field").show();
			if ($('#_phive_enable_buffer').is(":checked")) {
				$("#ph_buffer_before").show();
				$("#ph_buffer_after").show();
			}
			else {
				$("#ph_buffer_before").hide();
				$("#ph_buffer_after").hide();
			}

		}
		else {
			$("#_phive_enable_buffer_field").hide();
			$("#ph_buffer_before").hide();
			$("#ph_buffer_after").hide();

		}

	}

	/****** html-ph-booking-product-admin-assets.php ********/
	toggle_auto_assign_related();
	display_assets_options();

	jQuery(document).on('change', '#_phive_booking_assets_enable', function () {
		display_assets_options();
	})

	jQuery(document).on('change', '#_phive_booking_assets_auto_assign', function () {
		toggle_auto_assign_related();
	})

	function toggle_auto_assign_related() {
		if ($("#_phive_booking_assets_auto_assign").val() == 'yes') {
			$("#_phive_hide_assets").closest(".form-field").show();
			// $("#_phive_booking_assets_label").closest(".form-field").hide();
		} else {
			$("#_phive_hide_assets").closest(".form-field").hide();
			// $("#_phive_booking_assets_label").closest(".form-field").show();
		}
	}

	function display_assets_options() {
		if ($("#_phive_booking_assets_enable").is(":checked")) {
			$(".assets-wraper").show();
		} else {
			$(".assets-wraper").hide();
		}
	}
	function toggle_related_to_custom_date_range() {
		if ($("#_phive_book_interval_period").length && $("#_phive_book_interval_period").val() != 'minute' && $("#_phive_book_interval_period").val() != 'hour') {
			$('.availability_time_picker_field').hide();
		}
		else {
			$('.availability_time_picker_field').show();
		}
	}

	/****** html-ph-booking-product-admin-availability.php ********/

	jQuery(document).on('change', '.ph_booking_from_date,.ph_booking_to_date,.ph_booking_from_timepicker,.ph_booking_to_timepicker', function () {
		var row = $(this).closest('tr');
		$(row).css('border', '1px solid #f00');
		var from_datepicker = $(row).find('.ph_booking_from_date').val();
		var from_timepicker = $(row).find('.ph_booking_from_timepicker').val();
		var from_date = from_datepicker + ' ' + from_timepicker;
		var to_datepicker = $(row).find('.ph_booking_to_date').val();
		var to_timepicker = $(row).find('.ph_booking_to_timepicker').val();
		var to_date = to_datepicker + ' ' + to_timepicker;
		$(row).find('.ph_booking_from_datetime').val(from_date);
		$(row).find('.ph_booking_to_datetime').val(to_date);
	})
	jQuery(document).on('change', '.ph_booking_from_date_for_date_range_and_time,.ph_booking_to_date_for_date_range_and_time,.ph_booking_from_timepicker_date_range_and_time,.ph_booking_to_timepicker_date_range_and_time', function () {
		var row = $(this).closest('tr');
		$(row).css('border', '1px solid #f00');
		var from_datepicker = $(row).find('.ph_booking_from_date_for_date_range_and_time').val();
		var from_timepicker = $(row).find('.ph_booking_from_timepicker_date_range_and_time').val();
		var from_date = from_datepicker + ' ' + from_timepicker;
		var to_datepicker = $(row).find('.ph_booking_to_date_for_date_range_and_time').val();
		var to_timepicker = $(row).find('.ph_booking_to_timepicker_date_range_and_time').val();
		var to_date = to_datepicker + ' ' + to_timepicker;
		$(row).find('.ph_booking_from_datetime_for_date_range_and_time').val(from_date);
		$(row).find('.ph_booking_to_datetime_for_date_range_and_time').val(to_date);
	})
	jQuery(document).on('change', '#_phive_restrict_start_day', function () {
		toggle_booking_start_day();
	})

	function toggle_booking_start_day() {
		if ($('#_phive_restrict_start_day').is(":checked")) {
			$(".ph_start_day_related").show();
		} else {
			$(".ph_start_day_related").hide();
		}
	}

	jQuery(document).on('change', '.ph_booking_availability_type', function () {
		var value = $(this).val();
		var row = $(this).closest('tr');

		$(row).css('border', '1px solid #f00');

		$(row).find('.availability_by_week_days, .availability_by_month, .availability_by_date, .availability_by_time, .availability_by_date_range_and_time').hide();

		if (value == 'custom') {
			$(row).find('.availability_by_date').show();
		}
		if (value == 'date-range-and-time') {
			$(row).find('.availability_by_date_range_and_time').show();
		}
		if (value == 'months') {
			$(row).find('.availability_by_month').show();
		}
		if (value == 'days') {
			$(row).find('.availability_by_week_days').show();
		}
		if (value.match("^time")) {
			$(row).find('.availability_by_time').show();
		}
	});

	jQuery(".availability_date_picker").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
		minDate: new Date(),
	});


	// To date must be greater than from date
	jQuery(".ph_booking_availability_type").each(function(){
		
		// Global Availability datepicker
		var defaultDate = new Date(jQuery(this).closest('tr').find('.ph_booking_from_date').val());
		jQuery(this).closest('tr').find('.ph_booking_to_date').datepicker('option', 'minDate', defaultDate);

		defaultDate = new Date(jQuery(this).closest('tr').find('.ph_booking_from_date_for_date_range_and_time').val());
		jQuery(this).closest('tr').find('.ph_booking_to_date_for_date_range_and_time').datepicker('option', 'minDate', defaultDate);

		// Asset availbility datepicker
		defaultDate = new Date(jQuery(this).closest('tr').find('.ph_booking_asset_from_date').val());
		jQuery(this).closest('tr').find('.ph_booking_asset_to_date').datepicker('option', 'minDate', defaultDate);
	});

	jQuery(".ph_availability_table").on('change','.ph_booking_from_date',function(){

		// Global Availability datepicker
		var defaultDate = new Date(jQuery(this).closest('tr').find('.ph_booking_from_date').val());
		jQuery(this).closest('tr').find('.ph_booking_to_date').datepicker('option', 'minDate', defaultDate);
	});

	// Date + Time Range Option
	jQuery(".ph_availability_table").on('change','.ph_booking_from_date_for_date_range_and_time',function(){

		var defaultDate = new Date(jQuery(this).closest('tr').find('.ph_booking_from_date_for_date_range_and_time').val());
		jQuery(this).closest('tr').find('.ph_booking_to_date_for_date_range_and_time').datepicker('option', 'minDate', defaultDate);
	});

	jQuery(".ph_availability_table").on('change','.ph_booking_asset_from_date',function(){

		// Asset availbility datepicker
		var defaultDate = new Date(jQuery(this).closest('tr').find('.ph_booking_asset_from_date').val());
		jQuery(this).closest('tr').find('.ph_booking_asset_to_date').datepicker('option', 'minDate', defaultDate);
	});

	jQuery('.ph_availability_table tbody').sortable({
		items: 'tr',
		cursor: 'move',
		axis: 'y',
		handle: '.sort',
		scrollSensitivity: 40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'wc-metabox-sortable-placeholder',
		start: function (event, ui) {
			ui.item.css('baclbsround-color', '#f6f6f6');
		},
		stop: function (event, ui) {
			ui.item.removeAttr('style');
		}
	});


	jQuery(document).on('click', "table[class^='ph_'] td.remove", function () {
		$(this).closest('tr').remove();
	});


	/****** html-ph-booking-admin-reports-list-filters.php ********/

	$(document).on("click", ".btn_filter", function () {
		admin_url = window.location.pathname + '?page=bookings';
		booking_status = $(this).closest(".tablenav").find(".ph_booking_status").val();
		filter_product_ids = $(this).closest(".tablenav").find(".ph_filter_product_ids").val();
		filter_from = $(this).closest(".tablenav").find(".ph_filter_from").val();
		filter_end_from = $(this).closest(".tablenav").find(".ph_filter_end_from").val();
		filter_to = $(this).closest(".tablenav").find(".ph_filter_to").val();
		filter_end_to = $(this).closest(".tablenav").find(".ph_filter_end_to").val();
		window.location = admin_url + "&ph_booking_status=" + booking_status + "&ph_filter_product_ids=" + filter_product_ids + "&ph_filter_from=" + filter_from + "&ph_filter_end_from=" + filter_end_from + "&ph_filter_to=" + filter_to + "&ph_filter_end_to=" + filter_end_to;
	});


	$(".ph_from-top").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});
	$(".ph_end_from-top").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});

	$(".ph_from-bottom").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});
	$(".ph_end_from-bottom").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});

	$(".ph_to-top").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});
	$(".ph_end_to-top").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});
	$(".ph_to-bottom").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});
	$(".ph_end_to-bottom").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});


	/***************** Custom error **********************/

	$(document).on('wc_add_error_tip', function (e, element, error_type, newvalue = '') {
		var offset = element.position();

		if (element.parent().find('.wc_error_tip').length === 0) {
			element.after('<div class="wc_error_tip ' + error_type + '">' + (phive_booking_locale.exceed_booking.replace(/%d/g, newvalue)) + '</div>');
			element.parent().find('.wc_error_tip')
				.css('left', offset.left + element.width() - (element.width() / 2) - ($('.wc_error_tip').width() / 2))
				.css('top', offset.top + element.height())
				.fadeIn('100');
		}
	})

	$(document).on('wc_remove_error_tip', function (e, element, error_type) {
		element.parent().find('.wc_error_tip.' + error_type).fadeOut('100', function () { $(this).remove(); });
	})

	$(document).on('click', function () {
		$('.wc_error_tip').fadeOut('100', function () { $(this).remove(); });
	})

	$(document).on('blur', '.ph_booking_persons_rule_min,ph_booking_persons_rule_max', function () {
		$('.wc_error_tip').fadeOut('100', function () { $(this).remove(); });
	});
	$(document).on('change', '.ph_booking_persons_rule_min,.ph_booking_persons_rule_max', function () {
		var regex;
		if ($('#_phive_booking_persons_as_booking').prop('checked')) {

			if ($(this).is('.ph_booking_persons_rule_min')) {
				regex = $('#_phive_book_allowed_per_slot').val();
				error = 'exceed_booking';
			}
			else if ($(this).is('.ph_booking_persons_rule_max')) {
				regex = $('#_phive_book_allowed_per_slot').val();
				error = 'exceed_booking';
			}


			var value = parseInt($(this).val());
			var newvalue = parseInt(regex);
			if (value > newvalue) {
				$(this).val(regex);
			}
		}

	})
	$(document).on('keyup', '.ph_booking_persons_rule_min ,.ph_booking_persons_rule_max', function () {
		var regex, error;
		if ($('#_phive_booking_persons_as_booking').prop('checked')) {

			if ($(this).is('.ph_booking_persons_rule_min') || $(this).is('.ph_booking_persons_rule_max')) {
				regex = $('#_phive_book_allowed_per_slot').val();
				error = 'exceed_booking';
			}
			var value = parseInt($(this).val());
			var newvalue = parseInt(regex);
			if (value > newvalue) {
				$(document).triggerHandler('wc_add_error_tip', [$(this), error, newvalue]);
			} else {
				$(document).triggerHandler('wc_remove_error_tip', [$(this), error]);
			}
		}
	})
	// console.log($('.modify_booking_from_meta_id'));
	var edit_order_item = $('#woocommerce-order-items'); 
	$(edit_order_item).on('click', '.edit-order-item', function () 
	{
		// console.log('clicked');
		$('.modify_booking_from_meta_id').each(function () 
		{
			date_value = $(this).val();
			meta_id = $(this).data('meta-id');
			item_id = $(this).data('item-id');
			meta = "meta_value[" + item_id + "][" + meta_id + "]";
			jQuery('#order_line_items').find('.edit').find('textarea[name="' + meta + '"]').html(date_value);
		});

		$('.modify_booking_to_meta_id').each(function () {
			date_value = $(this).val();
			meta_id = $(this).data('meta-id');
			item_id = $(this).data('item-id');
			meta = "meta_value[" + item_id + "][" + meta_id + "]";
			jQuery('#order_line_items').find('.edit').find('textarea[name="' + meta + '"]').html(date_value);
		});
	});
	
	// #105168
	$('#_phive_booking_maximum_number_of_allowed_participant').on('change', function (e) 
	{
		ph_max_participant_warning();
	});

	$('input[name=_phive_book_allowed_per_slot]').on('change', function () 
	{
		ph_max_participant_warning();
	});

	function ph_max_participant_warning()
	{
		max_booking = parseInt($('input[name=_phive_book_allowed_per_slot]').val());
		if ($('#_phive_booking_persons_as_booking').prop('checked')) 
		{
			if($('#_phive_booking_maximum_number_of_allowed_participant').val() > max_booking)
			{
				style = "display:block;margin:inherit;padding:10px 0px;color:red";
				elem = $('#_phive_booking_maximum_number_of_allowed_participant').parent('p._phive_booking_maximum_number_of_allowed_participant_field').find('span.ph-participant-more-than-max-bookings');
				if(elem.length) {
					elem.html(phive_booking_locale.max_participant_warning.replace('%d',max_booking));
				}else {
					$('#_phive_booking_maximum_number_of_allowed_participant').parent('p._phive_booking_maximum_number_of_allowed_participant_field').append('<span class="ph-participant-more-than-max-bookings" style="'+style+'">'+phive_booking_locale.max_participant_warning.replace('%d',max_booking)+'</span>');
				}
			}
			else
			{
				elem = $('#_phive_booking_maximum_number_of_allowed_participant').parent('p._phive_booking_maximum_number_of_allowed_participant_field').find('span.ph-participant-more-than-max-bookings');
				elem.remove();
			}
		}
		else
		{
			elem = $('#_phive_booking_maximum_number_of_allowed_participant').parent('p._phive_booking_maximum_number_of_allowed_participant_field').find('span.ph-participant-more-than-max-bookings');
			elem.remove();
		}
	}

	ph_min_participant_warning();

	$('#_phive_booking_persons_as_booking').on('click', function (e) 
	{
		ph_min_participant_warning();
	});

	$('#_phive_booking_minimum_number_of_required_participant').on('change', function (e) 
	{
		ph_min_participant_warning();
	});

	/**
	 * Check the min participant required when consider each participant as separate booking enabled
	 */
	function ph_min_participant_warning()
	{
		if ($('#_phive_booking_persons_as_booking').prop('checked')) 
		{
			if($('#_phive_booking_minimum_number_of_required_participant').val() < 1)
			{
				style = "display:block;margin:inherit;padding:10px 0px;color:red";
				elem = $('#_phive_booking_minimum_number_of_required_participant').parent('p._phive_booking_minimum_number_of_required_participant_field').find('span.ph-participant-more-than-max-bookings');

				$('#_phive_booking_minimum_number_of_required_participant').val(1);
				if(elem.length) {
					elem.html(phive_booking_locale.min_participant_warning);
				}else {
					$('#_phive_booking_minimum_number_of_required_participant').parent('p._phive_booking_minimum_number_of_required_participant_field').append('<span class="ph-participant-more-than-max-bookings" style="'+style+'">'+phive_booking_locale.min_participant_warning+'</span>');
				}
			}
			else
			{
				elem = $('#_phive_booking_minimum_number_of_required_participant').parent('p._phive_booking_minimum_number_of_required_participant_field').find('span.ph-participant-more-than-max-bookings');
				elem.remove();
			}
		}
		else
		{
			elem = $('#_phive_booking_minimum_number_of_required_participant').parent('p._phive_booking_minimum_number_of_required_participant_field').find('span.ph-participant-more-than-max-bookings');
			elem.remove();
		}
	}
	
	// notice-dismiss not working in all bookings page
	jQuery('.ph-notice-success').on('click', 'button.notice-dismiss', function () 
	{
		$(this).parent('div.ph-notice-success').hide();
	});
	// Ticket-133055- Give warning message (Allow to enter numeric or monetory decimal point only ) 
	$("#ph_booking_display_cost, #ph_booking_base_cost , #ph_booking_cost_per_unit").on("keyup", function() {
		if ( $( this ).is( '#ph_booking_display_cost' ) || $( this ).is( '#ph_booking_base_cost' ) || $( this ).is( '#ph_booking_cost_per_unit' ) ) {
			checkDecimalNumbers = true;
			regex = new RegExp( '[^\-0-9\%\\' + woocommerce_admin.mon_decimal_point + ']+', 'gi' );
			decimalRegex = new RegExp( '[^\\' + woocommerce_admin.mon_decimal_point + ']', 'gi' );
			error = 'i18n_mon_decimal_error';
		}
		var value    = $( this ).val();
		var newvalue = value.replace( regex, '' );

		if ( checkDecimalNumbers && 1 < newvalue.replace( decimalRegex, '' ).length ) {
			newvalue = newvalue.replace( decimalRegex, '' );
		}
		if($( this ).is( '#ph_booking_display_cost' )){
			$( '#ph_booking_display_cost' ).val( newvalue ) ;
		}
		else if($( this ).is( '#ph_booking_base_cost' )){
			$( '#ph_booking_base_cost' ).val( newvalue ) ;
		}
		else if($( this ).is( '#ph_booking_cost_per_unit' )){
			$( '#ph_booking_cost_per_unit' ).val( newvalue );
		}
		if ( value !== newvalue ) {
			$( document.body ).triggerHandler( 'wc_add_error_tip', [ $( this ), error ] );
		} else {
			$( document.body ).triggerHandler( 'wc_remove_error_tip', [ $( this ), error ] );
		}
		
	});
	
	// Maximum allowed duration should be greater than or equal to minimum allowed ticket
	$('#_phive_book_max_allowed_booking').attr( 'min',$('#_phive_book_min_allowed_booking').val());
	$('#_phive_book_min_allowed_booking').change(function(){
		$('#_phive_book_max_allowed_booking').attr( 'min',$('#_phive_book_min_allowed_booking').val());
	});
	
	// All booking page filter to dates must be greater than filter from dates
	$('#booking-list-table-form').find('.ph_filter_from').change(function(){
		defaultDate = new Date($(this).val());
		$('#booking-list-table-form').find('.ph_filter_to').datepicker('option', 'minDate', defaultDate);
		$('#booking-list-table-form').find('.ph_filter_end_from').datepicker('option', 'minDate', defaultDate);
		$('#booking-list-table-form').find('.ph_filter_end_to').datepicker('option', 'minDate', defaultDate);
	});
	
	$('#booking-list-table-form').find('.ph_filter_end_from').change(function(){
		
		if($(this).val() == ''){
			defaultDate = new Date($('#booking-list-table-form').find('.ph_filter_from').val());
		}else{
			defaultDate = new Date($(this).val());
		}
		$('#booking-list-table-form').find('.ph_filter_end_to').datepicker('option', 'minDate', defaultDate);
	});

	// Bookings open no later than should be greater than or equal to Bookings open no sooner than
	$('#_phive_fixed_availability_to').attr('min',$('#_phive_fixed_availability_from').val());
	$('#_phive_fixed_availability_from').change(function(){
		$('#_phive_fixed_availability_to').attr('min',$(this).val());
	});

	//Last booking starts at should be greater than or equal to First booking starts at 
	$('#_phive_book_working_hour_end').attr('min',$('#_phive_book_working_hour_start').val());
	$('#_phive_book_working_hour_start').change(function(){
		$('#_phive_book_working_hour_end').attr('min',$(this).val());
	});

	if (jQuery('.ph_booking_details_on_calendar').length > 0) {
		
		jQuery('.ph_booking_details_on_calendar').select2();
	}

	jQuery('.ph_booking_details_on_calendar').on('change', function(){

		let optionCount = jQuery('.ph_booking_details_on_calendar').val().length;

		if (optionCount >= 1 && optionCount <= 3) {

			jQuery('.woocommerce-save-button').prop('disabled', false);
			jQuery('#ph_booking_details_on_calendar_error').hide();
		} else {

			jQuery('.woocommerce-save-button').prop('disabled', true);
			jQuery('#ph_booking_details_on_calendar_error').show();
		}
	});

	jQuery('#ph_booking_details_on_calendar_error').hide();

	jQuery('#ph_bookings_followup_cron_interval').keyup(function() {
        
		let cron_interval = parseInt( jQuery('#ph_bookings_followup_cron_interval').val());

		let followup_time = parseInt( jQuery('#ph_bookings_followup_time').val());

		let input_element = jQuery('#ph_bookings_followup_cron_interval');

		let button_to_disable = '.ph_bookings_email_customiser_save';

		ph_toggle_cron_input_error( cron_interval, followup_time, input_element, button_to_disable);
	});

	jQuery('#woocommerce_ph_booking_followup_cron_interval').keyup(function() {
        
		let cron_interval = parseInt( jQuery('#woocommerce_ph_booking_followup_cron_interval').val());

		let followup_time = parseInt( jQuery('#woocommerce_ph_booking_followup_followup_time').val());

		let input_element = jQuery('#woocommerce_ph_booking_followup_cron_interval');

		let button_to_disable = '.woocommerce-save-button';

		ph_toggle_cron_input_error( cron_interval, followup_time, input_element, button_to_disable);
	});

	// Global Availability rules validation
	$('.woocommerce-save-button').on('click', function (e) {

		// Check if the current page and tab match the target values
		let url_params = new URLSearchParams(window.location.search); // Extract URL parameters
		let current_page = url_params.get('page'); // Get the current page parameter
		let current_tab = url_params.get('tab'); // Get the current tab parameter

		// Ensure the script only runs on the specific "bookings-settings" page and "availability" tab
		if (current_page === 'bookings-settings' && current_tab === 'availability') {

			// Initialize a flag to track if all validations pass
			let is_valid = true;

			// Iterate through each row in the availability rules table
			$('tbody.rules tr.rule').each(function (index) {
				let row = $(this); // Reference to the current row
				let ph_is_valid = ph_booking_avaibility_rules_validation(row, index); // Perform validation on the row

				// If any validation fails, set the is_valid flag to false
				if (!ph_is_valid) {
					is_valid = false;
				}
			});

			// If validation fails, prevent form submission and show error messages
			if (!is_valid) {
				e.preventDefault(); // Stop the default form submission

				// Scroll to the availability rules table for user convenience
				let table = $(".ph_availability_table");
				if (table.length) {
					$('html, body').animate({
						scrollTop: table.offset().top - 50 // Smooth scroll to the table (adjust offset if needed)
					}, 500); // Set scrolling animation duration
				}
			}
		}
	});

	// Product Availability rules validation
	$('#publish').on('click', function(e) {

		// Get the product type from the dropdown or input
		let product_type = $('#product-type').val();

		// Only validate if the product type is "phive_booking"
		if ("phive_booking" === product_type) {

			let is_valid = true; // Initialize validation flag

			// Iterate through each rule row in the table
			$(".rules .rule").each(function(index) {
				let row = $(this); // Reference to the current row
				let ph_is_valid = ph_booking_avaibility_rules_validation(row, index); // Validate the row

				// If validation fails for any row, set is_valid to false
				if (!ph_is_valid) {
					is_valid = false;
				}
			});

			// If validation fails, prevent form submission and navigate to the appropriate tab and table
			if (!is_valid) {

				// Activate the "Booking Availability" tab
				let tab = $(".phive_booking_availablity_options").find('a[href="#booking_availability"]');
				if (tab.length) {
					tab.trigger("click"); // Simulate a click to activate the tab
				}

				// Scroll to the "Booking Availability" table for user convenience
				let table = $(".ph_availability_table");
				if (table.length) {
					$('html, body').animate({
						scrollTop: table.offset().top - 50 // Smooth scroll to the table (adjust offset if needed)
					}, 500); // Set scrolling animation duration
				}

				// Prevent the form from being submitted
				e.preventDefault();
			}
		}
	});


	$('#enable_icalendar').on('change', function() {
		toggle_icalendar_settings();
	});

	$('#enable_outlook').on('change', function() {
		toggle_outlook_settings();
	});

	function toggle_icalendar_settings() {
		
		if ($('#enable_icalendar').is(":checked")) {
			$('.icalendar-wrapper').show();
		} else {
			$('.icalendar-wrapper').hide();
		}
	}

	function toggle_outlook_settings() {

		if ($('#enable_outlook').is(':checked')) {
			$('.outlook-wrapper').show();
		} else {
			$('.outlook-wrapper').hide();
		}
	}

	$('.ph-copy-outlook-redirect-url').on('click', function(){

		var url = $('.ph-outlook-redirect-url').val();

		if( url ) {
			$('.ph-copied-text').css("display","inline");
			$('.ph-copied-text').html('Copied').delay(2000).fadeOut(300);
		}

		// Copy the text inside the text field to clipboard.
		navigator.clipboard.writeText(url);
	})
})

// Toggle Cron input value error
function ph_toggle_cron_input_error( cron_interval, followup_time, input_element, button_to_disable){

	if ( cron_interval > followup_time ) {

		jQuery(button_to_disable).prop('disabled', true);

		if ( jQuery('.ph_followup_input_error').length < 1 ) {
			
			input_element.after('<span class="ph_followup_input_error" style="color:red;"> Cron interval must be less than or equal to the follow up delay time</span>');
		}
	} else {

		jQuery(button_to_disable).prop('disabled', false);

		jQuery('.ph_followup_input_error').remove();
	}
}

// Avaibility rule
function ph_booking_avaibility_rules_validation(row, index) {
	
	let is_valid = true;

	// Get the availability type
	let availability_type = row.find('.ph_booking_availability_type').val();

	// Custom Date Range validation
	if ('custom' === availability_type) {
		let from_date = row.find('.ph_booking_from_date').val().trim();
		let to_date = row.find('.ph_booking_to_date').val().trim();

		if (from_date === '' || to_date === '') {
			is_valid = false;
			row.find('.ph_booking_from_date, .ph_booking_to_date').css('border', '1px solid red'); // Highlight invalid fields
		} else {
			row.find('.ph_booking_from_date, .ph_booking_to_date').css('border', ''); // Remove highlight if valid
		}
	} else if ('date-range-and-time' === availability_type) {
		let from_date = row.find('.ph_booking_from_date_for_date_range_and_time').val().trim();
		let to_date = row.find('.ph_booking_to_date_for_date_range_and_time').val().trim();

		if (from_date === '' || to_date === '') {
			is_valid = false;
			row.find('.ph_booking_from_date_for_date_range_and_time, .ph_booking_to_date_for_date_range_and_time').css('border', '1px solid red'); // Highlight invalid fields
		} else {
			row.find('.ph_booking_from_date_for_date_range_and_time, .ph_booking_to_date_for_date_range_and_time').css('border', ''); // Remove highlight if valid
		}
	} else if (availability_type && availability_type.indexOf('time-') !== -1) {
		
		let from_time = row.find('input[name="ph_booking_from_time[]"]').val();
		let to_time = row.find('input[name="ph_booking_to_time[]"]').val();

		if (from_time === '' || to_time === '' || to_time <= from_time) {
			is_valid = false;
			row.find('.time-picker').css('border', '1px solid red'); // Highlight invalid fields
		} else {
			row.find('.time-picker').css('border', ''); // Remove highlight if valid
		}
	}

	return is_valid;  // Return true if validation passes
}