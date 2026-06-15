jQuery(window).on('load', function () {

	let searchParams = new URLSearchParams(window.location.search)
	if (!(searchParams.has('invoker') && searchParams.get('invoker') === 'phbookingsearch')) {
		return;
	}

	const isSummaryPage = jQuery('#ph_search_product_view').val() === 'summary' ? true : false;

	const data = {
		action: 'ph_get_search_params'
	}

	let productId = jQuery('form.cart #phive_product_id').val();
	var producData= '';
	jQuery.post(ph_booking_autofill.ajaxurl, data, function (response) {

		const { search_params } = response?.data || {};

		if (!search_params) {
			return;
		}

		let participants = search_params['ph_book_search_participants'];
		let totalParticipants = 0;
		participants = participants.replace(/\\/g, "");
		participants = JSON.parse(participants);
		let productParticipant = [];
		// Iterate through each participant section
		jQuery('.participant_inner_section').each(function (index) {
		// Find the input field within the current participant section

			var inputField = jQuery(this).find('input.input-person');

			var participantType = inputField.data('name');

			participants.forEach(participant => {

				// Autofill the input field with the corresponding value
				if (participant.rule == participantType) {
					inputField.val(participant.count);
				}

			});
		});

		//Setting the product page from date in search bar
		var fromDate = search_params['book_search_from'];
		jQuery('#ph_book_search_from').val(fromDate);

		//Setting the product page to date in search bar
		var toDate = search_params['book_search_to'];
		jQuery('#ph_book_search_to').val(toDate);
		//Setting the product page participant count for each rule
		var participantNameCount = '';
		var hiddenInputsPersons = '';
		var hiddenInputsRules = '';
		var hiddenInputsbooking = '';


		jQuery('#ph_book_search_number_of_participants_button .ph_participant-group').each(function () {

			let participantRule = jQuery(this).data('participant');
			let inputField = jQuery(this).find('input[type="number"]');

			participants.forEach(participant => {
				if (participant.rule === participantRule) {
					inputField.val(participant.count);
					totalParticipants += participant.count;
					if(participant.count > 0) {
						participantNameCount += `${participant.rule} ${participant.count},  `;
					}
					productParticipant.push(participant.count);

					hiddenInputsPersons += `<input type="hidden" name="phive_book_persons[]" class="phive_book_persons" value="${participant.count}">`;
					hiddenInputsRules += `<input type="hidden" name="ph_person_rule[]" class="ph_person_rule" value="${participant.rule}">`;
					hiddenInputsbooking = `<input type="hidden" name="persons_as_booking" class="persons_as_booking" value="no">`;
				}
			});
		});

		jQuery('form.cart').append(hiddenInputsPersons);
		jQuery('form.cart').append(hiddenInputsRules);
		jQuery('form.cart').append(hiddenInputsbooking);

		hiddenInputsPersons = '';
		hiddenInputsRules = '';
		hiddenInputsbooking = '';
		participantNameCount = participantNameCount.trim().replace(/,$/, '');

		//Setting the product page total participant count in search bar
		jQuery('#participant_count_display').text(totalParticipants);

		//Setting the product page asset item in search bar
		let assetId = search_params['ph_search_asset_name'];
		let assetItem = jQuery('.ph_book_search_asset_item[data-value="' + assetId + '"]'); //#ph_book_search_asset_list 
		var assetName = '';
		if (assetItem.length > 0) {
			assetName = assetItem.attr('title');
			jQuery('#ph_asset_name').val(assetName);
			jQuery('#ph_asset_name').attr('data-ph-search-asset-id', assetId);
		}

		//product summary deatils
		jQuery(document).ready(function () {
			setTimeout(function () {
				if (search_params.book_search_from) {
					jQuery('#ph_product_from_date').text(search_params.book_search_from);
					jQuery('label[for="ph_product_from_date_label"]').text(search_params.ph_from_date_text ? search_params.ph_from_date_text + ':' : '').toggle(!!search_params.ph_from_date_text);
				} else {
					jQuery('#ph_product_from_date').closest('.ph_product_row').hide();
				}

				if (search_params.book_search_to) {
					jQuery('#ph_product_to_date').text(search_params.book_search_to);
					jQuery('label[for="ph_product_to_date_label"]').text(search_params.ph_to_date_text ? search_params.ph_to_date_text + ':' : '').toggle(!!search_params.ph_to_date_text);
				} else {
					jQuery('#ph_product_to_date').closest('.ph_product_row').hide();
				}

				if (assetName) {
					jQuery('#ph_product_asset_name').text(assetName);
					jQuery('label[for="ph_product_asset_name_label"]').text(search_params.ph_asset_label ? search_params.ph_asset_label + ':' : '').toggle(!!search_params.ph_asset_label);
				} else {
					jQuery('#ph_product_asset_name').closest('.ph_product_row').hide();
				}

				if (participantNameCount) {
					jQuery('#ph_product_participant_name_count').text(participantNameCount);
					jQuery('label[for="ph_product_participant_label"]').text(search_params.ph_participant_lable ? search_params.ph_participant_lable + ':' : '').toggle(!!search_params.ph_participant_lable);
				} else {
					jQuery('#ph_product_participant_name_count').closest('.ph_product_row').hide();
				}
				jQuery('label[for="ph_booking_cost_label"]').text('Booking Cost:');
				jQuery('.ph_outer_container').addClass('loaded');
			}, 1000);
		});

		if (search_params.ph_product_view) {
			jQuery('#ph_product_views').val(search_params.ph_product_view);
		} else {
			jQuery('#ph_product_views').closest('.ph_product_row').hide();
		}

		/**
		 * Updating hidden fields manually to handle add to cart when using summary page.
		 */

		// Formatting date for month calendar.
		if ('month' === jQuery('.book_interval_period').val()) {
			
			let dateStr = search_params.book_search_from;
			let yearAndMonth = moment(dateStr).format("YYYY-MM"); // Format to get "YYYY-MM"
			
			jQuery('.ph-date-from').val(yearAndMonth || '');
			jQuery('.ph-date-to').val(yearAndMonth || '');
		} 
		else {

			jQuery('.ph-date-from').val(search_params.book_search_from || '');

			const dateTo = getDatePart(search_params.book_search_to) + ' ' + getTimePart(new Date(search_params.book_search_to), 'to');

			// jQuery('.ph-date-to').val(search_params.book_search_to || '');
			jQuery('.ph-date-to').val(dateTo);
		}

		jQuery('.display_time_from').val(search_params.book_search_from);
    	jQuery('.display_time_to').val(search_params.book_search_to);
		
		jQuery('.phive_book_assets').val(assetName || '');

		// Autofill Asset.
		// When asset is empty considering first available asset.
		if (!search_params['ph_search_asset_name'] || jQuery('.phive_book_assets option[value="' + search_params['ph_search_asset_name'] + '"]').length == 0 ) {
			jQuery('.phive_book_assets').prop('selectedIndex', 0);
		} else {
			jQuery('.phive_book_assets option[value="' + search_params['ph_search_asset_name'] + '"]').prop('selected', 'true');
		}

		// Checks whether all dayes of the month aren't bookable.
		const totalDays 		= jQuery('.ph-calendar-days li').length;
		const unavailableDays 	= jQuery('.ph-calendar-days li.de-active').length;
		
		// Triggering month change to next month.
		if (totalDays == unavailableDays) {
			jQuery('.ph-next').trigger('click');
		}

		setTimeout(() => {
			checkAndSelect(search_params);
		}, 500);

		jQuery(document).on('change', '.ph-ul-time', function () {

			jQuery(".ph-ul-time li").each(function () {
				const date = jQuery(this).find(".callender-full-date").val();

				const time = date.split(' ')[1];

				if (jQuery('.selected-date').length == 0) {
					if (time == search_params['ph_book_search_range_from'] && !jQuery(this).hasClass('de-active') && !jQuery(this).hasClass('not-available') && !jQuery(this).hasClass('booking-full') && !jQuery(this).hasClass('non-working-time')) {
						jQuery(this).trigger('click');
					}
				}
			});
		});

		// Cost calculation to be triggered manually only for summary page.
		if (isSummaryPage) {

			var addon_data = jQuery('.addon').serialize();
			if (parseInt(jQuery(".wc-pao-addon-field").length) > 0) {
				addon_data = jQuery('.wc-pao-addon-field').serialize();
			}
	
			// Handling extra slot at the end for summary page cost calculation.
			const intervalPeriod = jQuery('.book_interval_period').val();

			if (search_params['ph_book_search_filter_date_and_time'] && ['hour', 'minute'].includes(intervalPeriod)) {
				const dateTime = new Date(toDate);
				toDate = getDatePart(toDate) + ' ' + getTimePart(dateTime, 'to');
			}
	
			var producData = {
				action: 'phive_get_booked_price',
				// security : phive_booking_ajax.security,
				product_id: productId,
				book_from: fromDate,
				book_to: toDate,
				person_details: productParticipant,
				asset: assetId,
				addon_data: addon_data,
				autofill: 'autofill-summary'
			};
	
			var result = '';
			jQuery.post(ph_booking_autofill.ajaxurl, producData, function (res) {
				result = jQuery.parseJSON(res);
				setTimeout(() => {
					jQuery('#ph_booking_cost').html(result.price_html);
				}, 1000);
			});
		}

	});

	function checkAndSelect(search_params) {

		let isDateFound = false;

		// For date part, we have to match pattern according to widget settings.
		let searchFromDateTime	= search_params['book_search_from'];
		let searchToDateTime	= search_params['book_search_to'];
	
		const fromDate	= getDatePart(searchFromDateTime);
		const toDate	= getDatePart(searchToDateTime);

		searchFromDateTime	= new Date(search_params['book_search_from']);
		searchToDateTime	= new Date(search_params['book_search_to']);

		const fromTime		= getTimePart(searchFromDateTime);
		const toTime		= getTimePart(searchToDateTime, 'to');
		const across_day	= jQuery('.across_the_day_booking').val();
		const interval_period = jQuery('.book_interval_period').val();

		// When searched for Range calendar.
		if (!search_params['ph_fixed_date']) {

			// For month calendar
			if ('month' == interval_period) {
				
				const monthFromDate	= new Date(fromDate);
				const monthFromYear	= monthFromDate.getFullYear();
				const monthFrom		= (monthFromDate.getMonth() + 1).toString().padStart(2, '0');

				// Combine year and month
				const fromYearMonth = `${monthFromYear}-${monthFrom}`;

				const monthToDate	= new Date(toDate);
				const monthToYear	= monthToDate.getFullYear();
				const monthTo		= (monthToDate.getMonth() + 1).toString().padStart(2, '0');

				// Combine year and month
				const toYearMonth = `${monthToYear}-${monthTo}`;

				jQuery(".ph-calendar-days li").each(function () {
					const date = jQuery(this).find(".callender-full-date").val();

					if (fromYearMonth == date && !jQuery(this).hasClass('de-active')) {
						jQuery(this).trigger('click');
		
						jQuery(".ph-calendar-days li").each(function () {
							const date = jQuery(this).find(".callender-full-date").val();
		
							if (fromYearMonth != toYearMonth && toYearMonth == date && !jQuery(this).hasClass('de-active')) {
								setTimeout(() => {
									jQuery(this).trigger('click');
								}, 1000);
							}
						});
					}
				});
			}

			jQuery(".ph-calendar-days li").each(function () {

				const date = jQuery(this).find(".callender-full-date").val();
				if (
					date == fromDate
					&& !jQuery(this).hasClass('temporary-unavailable')
					&& !jQuery(this).hasClass('ph-next-month-date')
				) {
					isDateFound = true;
					jQuery(this).trigger('click');

					// When across day booking is enabled and searched for time calendar.
					if ('yes' == across_day && search_params['ph_book_search_filter_date_and_time']) {
						
						// Select the from time.
						if (fromTime) {
							selectFromTime(fromDate, fromTime);
						}

						// Calculate the range difference and trigger next day click.
						const count = calculateRangeDifference(fromDate, toDate);

						function triggerClickWithDelay(index) {

							// Trigger next day click until the range is met.
							if (index < count) {
								setTimeout(function() {
									// Trigger the click
									jQuery('.ph-next-day-time').trigger('click');

									// Call the next iteration
									triggerClickWithDelay(index + 1);
								}, 1000);
							} else {
								// Else condition triggers when all the across day clicks are done, so now we can trigger time selection.
								if (toTime) {
									selectToTime(toDate, toTime, count);
								}
							}
						}

						// Start the loop from index 0
						setTimeout(() => {
							triggerClickWithDelay(0);
						}, 1000);
					}
				}

				if (date == toDate && (!fromTime || !search_params['ph_book_search_filter_date_and_time'])) {
					setTimeout(() => {
						jQuery(this).trigger('click');
					}, 1000);
				}
			});
		} 
		// When searched for Fixed calendar
		else {

			// For month calendar
			if ('month' == interval_period) {
				
				const date	= new Date(fromDate);
				const year	= date.getFullYear();
				const month = (date.getMonth() + 1).toString().padStart(2, '0');

				// Combine year and month
				const yearMonth = `${year}-${month}`;

				jQuery(".ph-calendar-days li").each(function () {
					const date = jQuery(this).find(".callender-full-date").val();
	
					if (yearMonth == date && !jQuery(this).hasClass('de-active')) {
						jQuery(this).trigger('click');
					}
				});
			} else {

				// For calendars other than month.
				jQuery(".ph-calendar-days li").each(function () {
					const date = jQuery(this).find(".callender-full-date").val();
	
					if (
						date == fromDate
						&& !jQuery(this).hasClass('temporary-unavailable')
						&& !jQuery(this).hasClass('ph-next-month-date')
					) {
						isDateFound = true;
						jQuery(this).trigger('click');
					}
				});
			}
		}

		// When from date isn't bookable, move to next month and do the search.
		if (!isDateFound && 'month' != interval_period) {
			jQuery('.ph-next').trigger('click');
			setTimeout(() => {
				checkAndSelect(search_params);
			}, 500);
		}

		if (across_day == 'no' || search_params['ph_fixed_date']) {
			
			// From time selection.
			if (fromTime) {
				selectFromTime(fromDate, fromTime);
			}

			// To time selection.
			if (toTime) {
				selectToTime(toDate, toTime);
			}
		}
	}

	/**
	 * Get time part from given dateTime.
	 */
	function getTimePart(dateTime, type = '') {

		// Handling extra slot at the end.
		if ('to' == type) {
			const interval = jQuery('.book_interval').val();
			const intervalPeriod = jQuery('.book_interval_period').val();
			const isFixedDateSearch = jQuery('#ph_fixed_date').val();
			
			// Extra slots get added only for range search.
			if ('minute' === intervalPeriod && !isFixedDateSearch) {
				dateTime.setMinutes(dateTime.getMinutes() - interval);
			}

			if ('hour' === intervalPeriod && !isFixedDateSearch) {
				dateTime.setHours(dateTime.getHours() - interval);
			}
		}

		// Extract the hours and minutes
		let hours = dateTime.getHours().toString().padStart(2, "0");
		let minutes = dateTime.getMinutes().toString().padStart(2, "0");

		// Format the time in "HH:MM" format
		let fromTime = (hours === 0 && minutes === "00") ? null : `${hours}:${minutes}`;

		return fromTime;
	}

	/**
	 * Get date part from given dateTime.
	 */
	function getDatePart(inputDate) {

		const patterns = [
			{ 
				pattern: /^(?<day>\d{2})-(?<month>\d{2})-(?<year>\d{4})(?: (?<hours>\d{2}):(?<minutes>\d{2}) (?<period>AM|PM))?$/, 
				format: 'dd-mm-yy' 
			},
			{ 
				pattern: /^(?<day>\d{2})\/(?<month>\d{2})\/(?<year>\d{4})(?: (?<hours>\d{2}):(?<minutes>\d{2}) (?<period>AM|PM))?$/, 
				format: 'dd/mm/yy' 
			},
			{ 
				pattern: /^(?<year>\d{4})-(?<month>\d{2})-(?<day>\d{2})(?: (?<hours>\d{2}):(?<minutes>\d{2}) (?<period>AM|PM))?$/, 
				format: 'yy-mm-dd' 
			},
			{ 
				pattern: /^(?<year>\d{4})\/(?<month>\d{2})\/(?<day>\d{2})(?: (?<hours>\d{2}):(?<minutes>\d{2}) (?<period>AM|PM))?$/, 
				format: 'yy/mm/dd' 
			}
		];
		

        for (const { pattern } of patterns) {
            const match = inputDate.match(pattern);
            if (match) {
                const { groups } = match;
                const year = groups.year;
                const month = groups.month;
                const day = groups.day;
                const hours = groups.hours;
                const minutes = groups.minutes;
                const time = hours && minutes ? ` ${hours}:${minutes}` : '';
                
                return `${year}-${month}-${day}`;
            }
        }

        return null;
	}

	/**
	 * Get date range difference.
	 */
	function calculateRangeDifference(fromDate, toDate) {

		let dateFrom = new Date(fromDate);
		let dateTo = new Date(toDate);

		// Calculate the difference in time.
		let timeDifference = dateTo - dateFrom;

		// Convert time difference to days
		let dayDifference = timeDifference / (1000 * 60 * 60 * 24);

		return dayDifference;
	}

	/**
	 * Select from time.
	 */
	function selectFromTime(fromDate, fromTime) {

		// Flag to ensure that click is triggered once.
		var isFromClicked = false;

		jQuery(document).on('change', '.ph-ul-time', function () {

			jQuery(".ph-ul-time li").each(function () {
				const date = jQuery(this).find(".callender-full-date").val();
				const nextDate = jQuery(this).next().find(".callender-full-date").val();

				const frmdate = date.split(' ')[0].trim();
				const time = date.split(' ')[1].trim();
				
				const nextTimeSlot = nextDate ? nextDate.split(' ')[1] : '';

				if (jQuery('.selected-date').length == 0) {
					if (fromDate == frmdate && time == fromTime && !jQuery(this).hasClass('de-active') && !jQuery(this).hasClass('not-available') && !jQuery(this).hasClass('booking-full') && !jQuery(this).hasClass('non-working-time')) {
						isFromClicked = true;
						jQuery(this).trigger('click');
					} else if ( fromTime >= time && fromTime < nextTimeSlot && !isFromClicked) { // When exact slot isn't available
						jQuery(this).trigger('click');
					}
				}
			});
		});
	}
	

	/**
	 * Select to time based on same day or range of days.
	 */
	function selectToTime(toDate, toTime, count) {

		// When count is zero, it means selection is for same day, hence for same day ph-ul-time trigger won't happen
		if (count === 0) {
			toTimeSelection(toTime);
		} else {
			jQuery(document).on('change', '.ph-ul-time', function () {
				toTimeSelection(toTime)
			});
		}
	}

	/**
	 * Select to time.
	 */
	function toTimeSelection(toTime) {

		// Flag to ensure that click is triggered once.
		var isToClicked = false;

		jQuery(".ph-ul-time li").each(function () {
	
			const date = jQuery(this).find(".callender-full-date").val();
			const nextDate = jQuery(this).next().find(".callender-full-date").val();

			const nextTimeSlot = nextDate ? nextDate.split(' ')[1] : '';

			const time = date.split(' ')[1].trim();
			
			if (time == toTime && !jQuery(this).hasClass('de-active') && !jQuery(this).hasClass('not-available') && !jQuery(this).hasClass('booking-full') && !jQuery(this).hasClass('non-working-time')) {
					isToClicked = true;
					setTimeout(() => {
						jQuery(this).trigger('click');
					}, 1000);
				} else if (toTime >= time && toTime < nextTimeSlot && !isToClicked) { // When exact slot isn't available
					jQuery(this).trigger('click');
				}
		});
	}
});