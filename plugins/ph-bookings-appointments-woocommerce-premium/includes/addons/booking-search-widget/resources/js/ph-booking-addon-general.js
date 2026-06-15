jQuery(document).ready(function ($) {
    /* --------------------------
       Helpers & initial setup
       -------------------------- */
    /**
     * Caches and positions an element relative to a parent on click.
     * @param {string} elementId The ID of the element to manage.
     * @param {string} parentId The ID of the parent element.
     */
    function detachElementAndPosition(elementId, parentId) {
        let $element = $(`#${elementId}`);
        let $parent = $(`#${parentId}`);

        $element.hide().appendTo('body');
        $parent.on('click', function () {
            let offset = $(this).offset();
            $element.css({
                top: offset.top + $(this).outerHeight(),
                left: offset.left
            }).show();
        });

        if ($('.ph_controls').length === 0) {
            $('#ph_book_search_number_of_participants_container').hide();
        }
    }

    detachElementAndPosition('ph_book_search_asset_list', 'ph_book_search_asset_name_container');
    detachElementAndPosition('ph_book_search_number_of_participants_button', 'ph_book_search_number_of_participants_container');

    // Read config from hidden inputs
    let date_format = $("#ph_book_search_date_format").val();
    let fixedDate = $('#ph_fixed_date').val() === '1';
    let timeEnabled = $('#ph_book_search_filter_date_and_time').val() === '1';
    let timeInterval = parseInt($('#ph_book_search_interval').val()) || 5;
    let assetLabel = $('#ph_asset_label').val();
    let borderRadius = $('#ph_border_radius').val();
    let borderStyle = $('#ph_border_style').val();
    let borderColor = $('#ph_border_color').val();
    let borderWidth = $('#ph_border_width').val();
    let daily_min_time = $('#ph_book_search_daily_range_from').val() || "00:00";
    let daily_max_time = $('#ph_book_search_daily_range_to').val() || "23:59";
    let time_format = $('#ph_book_search_filter_time_format').val();
    let time_format_txt = time_format === 'time_24hr' ? ' H:i' : ' h:i K';
    // For Moment formatting
    let momentTimeFormat = time_format === 'time_24hr' ? ' HH:mm' : ' hh:mm A';

    // available days (initial)
    let availableCheckInDays = $("#ph_checkin_day_related").val() ? $("#ph_checkin_day_related").val().split(' ').filter(Boolean) : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    let availableCheckOutDays = $("#ph_checkout_day_related").val() ? $("#ph_checkout_day_related").val().split(' ').filter(Boolean) : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    // Supported formats (same as your PHP array)
    let dateFormatOptions = [
        'd-m-Y',
        'Y-m-d',
        'd/m/Y',
        'Y/m/d',
        'd.m.Y',
        'D, d M Y',
        'M j, Y',
        'F j, Y',
        'M j Y'
    ];

    // Check if it's in the list, otherwise fallback
    if ($.inArray(date_format, dateFormatOptions) === -1) {
        date_format = 'd-m-Y';  // default
    }

    // URL param helper
    let getUrlParameter = (sParam) => {
        let sPageURL = window.location.search.substring(1);
        let sURLVariables = sPageURL.split('&');
        for (let i = 0; i < sURLVariables.length; i++) {
            let sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] === sParam) {
                // FIX 1: Ensure '+' is decoded as space, which is common in URL query strings
                return sParameterName[1] ? decodeURIComponent(sParameterName[1].replace(/\+/g, ' ')) : true; 
            }
        }
        return null;
    };

    // Convert flatpickr/PHP style date format to Moment.js format
    function convertToMomentFormat(flat) {
        const map = {
            'd-m-Y': 'DD-MM-YYYY',
            'Y-m-d': 'YYYY-MM-DD',
            'd/m/Y': 'DD/MM/YYYY',
            'Y/m/d': 'YYYY/MM/DD',
            'd.m.Y': 'DD.MM.YYYY',
            'D, d M Y': 'ddd, DD MMM YYYY',
            'M j, Y': 'MMM D, YYYY',
            'F j, Y': 'MMMM D, YYYY',
            'M j Y': 'MMM D YYYY'
        };
        return map[flat] || 'DD-MM-YYYY';
    }
    
    /**
     * Determines the initial selected date and time, respecting minTime and current time.
     * @returns {Date|null} The initial date object, or null if no URL parameter is set.
     */
    function getInitialSelectedDate(minTimeStr) {
        let resultFrom = getUrlParameter('book_search_from');
        
        if (resultFrom) {
            // FIX 2a: Set the input field directly to the decoded URL value for display
            $("#ph_book_search_from").val(resultFrom);
            
            // Use date from URL to initialize Flatpickr's date object
            const momentFormat = convertToMomentFormat(date_format) + (timeEnabled ? momentTimeFormat : '');
            let initialDate = moment(resultFrom, momentFormat).toDate();
            
            // Check if parsing failed (Moment.js date object is invalid)
            if (isNaN(initialDate.getTime())) {
                // If the URL date is truly invalid, clear the input and return null
                $("#ph_book_search_from").val('');
                return null; 
            }
            return initialDate;

        } else {
            // FIX 2b: If no URL parameter is present, DO NOT set an initial date/time.
            // The input field remains empty as intended for a fresh load.
            return null; 
        }
    }
    
    // Determine the initial date for the 'From' field. It will be null on fresh load.
    let initialFromDate = getInitialSelectedDate(daily_min_time);
    
    // Add this new block to handle the 'To' field.
    let resultTo = getUrlParameter('book_search_to');
    let initialToDate = null;
    if (resultTo) {
        // FIX 3a: Set the 'To' field value directly from the URL parameter for correct display
        $("#ph_book_search_to").val(resultTo);
        
        const momentFormat = convertToMomentFormat(date_format) + (timeEnabled ? momentTimeFormat : '');
        let parsedDate = moment(resultTo, momentFormat).toDate();
        
        // Check if parsing worked
        if (!isNaN(parsedDate.getTime())) {
            initialToDate = parsedDate;
        } else {
            // If invalid, clear input and use null
            $("#ph_book_search_to").val('');
        }
    }

    let flatpickrDateFormat = date_format;
    let flatpickrDateTimeFormat = date_format + time_format_txt;

    /**
     * Creates a Flatpickr disable function.
     * @param {string[]} availableDays - Array of available day names.
     * @param {Date|null} minDateForTo - The minimum date for the 'To' picker (optional).
     */
    function makeDisableFn(availableDays, minDateForTo) {

        return function (date) {
            let incomingDay = flatpickr.formatDate(date, "l");
            let isRestrictedDay = !availableDays.includes(incomingDay);
            return isRestrictedDay;
        };
    }

    /**
     * Updates the custom year select element's options and selected value.
     */
    function updateCustomYearSelect(instance, targetYear) {
        let yearSelect = instance.calendarContainer.querySelector(".flatpickr-year-select");
        if (yearSelect) {
            yearSelect.value = targetYear;
            instance.currentYear = targetYear;
            let yearInput = instance.calendarContainer.querySelector(".cur-year");
            if (yearInput) yearInput.value = targetYear;
        }
    }

    /**
     * Replaces Flatpickr time inputs with selects and controls year range.
     */
    function createTimeControls(instance) {
        // Use a timeout to ensure Flatpickr's structure is fully rendered
        setTimeout(function () {
            if (!instance || !instance.calendarContainer) return;

            let yearInput = instance.calendarContainer.querySelector(".cur-year");
            if (yearInput && yearInput.parentNode) {
                let yearWrapper = yearInput.parentNode;
                
                // Clean up any existing custom select before creating a new one
                let existingSelect = instance.calendarContainer.querySelector(".flatpickr-year-select");
                if (existingSelect) existingSelect.remove();
                
                let yearSelect = document.createElement("select");
                yearSelect.className = "flatpickr-year-select";

                // Determine the minimum selectable year from minDate config
                let minValidYear = instance.config.minDate && instance.config.minDate instanceof Date 
                                   ? instance.config.minDate.getFullYear() 
                                   : new Date().getFullYear();

                let cy = new Date().getFullYear();
                
                // Start the loop from the greater of the current system year or the minValidYear
                const startYear = Math.max(cy, minValidYear); 

                // Ensure the 'current year' for selection is at least the starting year
                let currentYearForSelection = instance.currentYear < startYear 
                                            ? startYear 
                                            : instance.currentYear;

                // Loop from the calculated minimum valid year up to the max year (+2)
                for (let y = startYear; y <= cy + 2; y++) {
                    let opt = document.createElement("option");
                    opt.value = y;
                    opt.textContent = y;
                    // Check against the adjusted current year for selection
                    if (y === currentYearForSelection) opt.selected = true;
                    yearSelect.appendChild(opt);
                }
                
                // Replace the old year element with the new select
                yearWrapper.parentNode.replaceChild(yearSelect, yearWrapper);
                yearSelect.addEventListener('change', function () {
                    instance.changeYear(parseInt(this.value, 10));
                });
            }
        }, 0);
    }
    
    /**
     * Generates a list of valid time options respecting min/max time and interval.
     */
    function generateTimeOptions(instance) {
        let options = [];
        let time24hr = instance.config.time_24hr;
        let timeMomentFormat = time_format === 'time_24hr' ? 'HH:mm' : 'hh:mm A';
        
        // Use the configured daily range
        let minTime = moment(daily_min_time, timeMomentFormat);
        let maxTime = moment(daily_max_time, timeMomentFormat);
        
        // Start from the minimum time (or 00:00 if minTime is invalid)
        let currentTime = moment().hour(minTime.hour()).minute(minTime.minute()).second(0).millisecond(0);
        
        // Ensure start time is correctly set to min time if it's 24-hour cycle
        if (!minTime.isValid()) {
            currentTime = moment().hour(0).minute(0).second(0).millisecond(0);
            minTime = currentTime.clone();
        }
        // Loop through all possible minutes until max time is reached
        while (currentTime.isSameOrBefore(maxTime)) {
            
            // Format time for display
            let h = currentTime.hour();
            let m = currentTime.minute();
            
            let displayHour = h;
            let ampm = '';
            let valueHour = h.toString().padStart(2, '0');
            let valueMinute = m.toString().padStart(2, '0');
            if (!time24hr) {
                ampm = h < 12 ? ' AM' : ' PM';
                displayHour = (h % 12) || 12; // Convert 0 and 12 to 12
            }
            let displayTime = `${displayHour.toString().padStart(2, '0')}:${valueMinute}${ampm}`;
            let valueTime = `${valueHour}:${valueMinute}`;
            
            options.push({ value: valueTime, display: displayTime });
            
            // Increment by interval
            currentTime.add(timeInterval, 'minutes');
        }
        return options;
    }
    
    /**
     * Helper to compare dates by year/month/day
     */
    function sameDay(a, b) {
        return a && b &&
            a.getFullYear() === b.getFullYear() &&
            a.getMonth() === b.getMonth() &&
            a.getDate() === b.getDate();
    }
    
    /**
     * Replaces Flatpickr time inputs with select dropdowns based on available intervals.
     */
    function createTimeDropdowns(instance) {
        if (!instance || !instance.calendarContainer || !timeEnabled) return;
        const timeContainer = instance.calendarContainer.querySelector(".flatpickr-time");
        if (!timeContainer) return;
        
        // Prevent duplicates
        if (timeContainer.querySelector(".ph-hour-select")) return;
        
        // Clear any existing time controls
        timeContainer.innerHTML = '';
        
        // Get full timeline options (these already respect daily_min_time / daily_max_time and interval)
        const timeOptions = generateTimeOptions(instance); // -> [{value: "HH:mm", display: "..."}]
        
        if (!timeOptions || !timeOptions.length) {
            const info = document.createElement('div');
            info.textContent = 'No available times';
            timeContainer.appendChild(info);
            return;
        }
        
        // Determine allowed min/max times (as moments on 'today')
        const valueFormat = 'HH:mm'; // generateTimeOptions uses 24-hour value strings ("HH:mm")
        let allowedMin = moment(daily_min_time, valueFormat);
        let allowedMax = moment(daily_max_time, valueFormat);
        
        // If instance has a config.minDate (may be set to a Date with time -- e.g., when you call set('minDate', sel))
        const configMinDate = instance.config && instance.config.minDate instanceof Date ? moment(instance.config.minDate) : null;
        
        // If the user has selected a date in this picker, use it to decide what minute/hour options to show
        const selectedDateObj = instance.selectedDates[0] || null;
        
        // If selected date equals today OR equals the config minDate, and now > allowedMin,
        // start from a rounded 'now' (rounded up to timeInterval) or from configMinDate (whichever is later).
        const now = moment();
        
        if (selectedDateObj) {
            const selectedIsToday = sameDay(selectedDateObj, new Date());
            const selectedIsConfigMin = configMinDate && sameDay(selectedDateObj, configMinDate.toDate());
            
            // Round 'now' up to next interval
            if (selectedIsToday) {
                if (now.isAfter(allowedMin)) {
                    const minutes = now.minute();
                    const rem = minutes % timeInterval;
                    const roundedMinutes = rem === 0 ? minutes : minutes + (timeInterval - rem);
                    const roundedNow = now.clone().minute(roundedMinutes).second(0).millisecond(0);
                    // allowedMin becomes the later of existing allowedMin and roundedNow
                    if (roundedNow.isAfter(allowedMin)) allowedMin = roundedNow;
                }
            }
            // If configMinDate has a specific time component and selected day equals its day,
            // make sure the configMinDate's time is also considered
            if (configMinDate && sameDay(selectedDateObj, configMinDate.toDate())) {
                if (configMinDate.isAfter(allowedMin)) allowedMin = configMinDate.clone();
            }
        }
        
        // Filter the timeOptions to only those inside [allowedMin, allowedMax]
        const filtered = timeOptions.filter(opt => {
            const [hh, mm] = opt.value.split(':').map(Number);
            // Create a moment for the option time on the selected date's day
            const optMoment = (selectedDateObj ? moment(selectedDateObj) : moment()).hour(hh).minute(mm).second(0).millisecond(0);
            
            // For comparison, normalize allowedMin/allowedMax to the selected date's day too
            let compareMin = allowedMin.clone().year(optMoment.year()).month(optMoment.month()).date(optMoment.date());
            let compareMax = allowedMax.clone().year(optMoment.year()).month(optMoment.month()).date(optMoment.date());
            
            return optMoment.isSameOrAfter(compareMin) && optMoment.isSameOrBefore(compareMax);
        });
        
        if (!filtered.length) {
            const info = document.createElement('div');
            info.textContent = 'No available times';
            timeContainer.appendChild(info);
            return;
        }
        
        // Build a map: hour (0..23) -> [minutes]
        const hourMap = {};
        filtered.forEach(opt => {
            const [hh, mm] = opt.value.split(':').map(Number);
            if (!hourMap[hh]) hourMap[hh] = [];
            if (hourMap[hh].indexOf(mm) === -1) hourMap[hh].push(mm);
        });
        
        // Sort hours
        const hours = Object.keys(hourMap).map(Number).sort((a, b) => a - b);
        
        // Create hour select (values are 0..23, display respects 12/24-hour format)
        const hourSelect = document.createElement('select');
        hourSelect.className = 'ph-hour-select';
        hours.forEach(h => {
            const opt = document.createElement('option');
            opt.value = h;
            if (instance.config.time_24hr) {
                opt.textContent = String(h).padStart(2, '0');
            } else {
                const displayHour = (h % 12) || 12;
                const ampm = h < 12 ? ' AM' : ' PM';
                opt.textContent = displayHour.toString() + ampm;
            }
            hourSelect.appendChild(opt);
        });
        
        // Determine initial hour to select
        let initialHour = selectedDateObj ? selectedDateObj.getHours() : hours[0];
        if (hours.indexOf(initialHour) === -1) {
            // pick nearest >= initialHour or first
            const next = hours.find(h => h >= initialHour);
            initialHour = typeof next !== 'undefined' ? next : hours[0];
        }
        hourSelect.value = initialHour;
        
        // Create minute select and populate based on current chosen hour
        const minuteSelect = document.createElement('select');
        minuteSelect.className = 'ph-minute-select';
        
        function populateMinutesForHour(h) {
            minuteSelect.innerHTML = '';
            const minutes = (hourMap[h] || []).slice().sort((a, b) => a - b);
            
            // Only add options if there are minutes available for this hour
            if (minutes.length === 0) return;
            
            minutes.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = String(m).padStart(2, '0');
                minuteSelect.appendChild(opt);
            });
            
            // choose the initial minute: prefer the selectedDate's minute if it exists and is available,
            // otherwise pick the first minute available
            let initMinute = selectedDateObj && selectedDateObj.getHours() === h ? selectedDateObj.getMinutes() : minutes[0];
            if (minutes.indexOf(initMinute) === -1) {
                const nextM = minutes.find(mm => mm >= initMinute);
                initMinute = typeof nextM !== 'undefined' ? nextM : minutes[0];
            }
            if (minutes.length) minuteSelect.value = initMinute;
        }
        
        populateMinutesForHour(Number(hourSelect.value));
        
        // Wrap and append controls
        const wrapper = document.createElement('div');
        wrapper.className = 'ph-time-select-wrapper';
        wrapper.style.display = 'flex';
        wrapper.style.gap = '6px';
        wrapper.style.justifyContent = 'center';
        wrapper.appendChild(hourSelect);
        wrapper.appendChild(document.createTextNode(':'));
        wrapper.appendChild(minuteSelect);
        timeContainer.appendChild(wrapper);
        
        // When hour changes, repopulate minutes for that hour and set date to first minute of that hour
        hourSelect.addEventListener('change', function () {
            const h = Number(this.value);
            populateMinutesForHour(h);
            // set selected date's time to this hour and selected minute (silent update)
            const m = minuteSelect.value ? Number(minuteSelect.value) : 0; // Default to 0 if minute select is empty
            const newDate = instance.selectedDates[0] ? new Date(instance.selectedDates[0]) : new Date();
            newDate.setHours(h, m, 0, 0);
            instance.setDate(newDate, false); // false to avoid re-triggering onChange loop
        });
        
        // When minute changes, update the date
        minuteSelect.addEventListener('change', function () {
            const h = Number(hourSelect.value);
            const m = Number(this.value);
            const newDate = instance.selectedDates[0] ? new Date(instance.selectedDates[0]) : new Date();
            newDate.setHours(h, m, 0, 0);
            instance.setDate(newDate, false);
        });
    }

    let baseFlatpickrConfig = {
        enableTime: timeEnabled,
        dateFormat: timeEnabled ? flatpickrDateTimeFormat : flatpickrDateFormat,
        time_24hr: time_format === 'time_24hr',
        minDate: "today",
        weekNumbers: false,
        closeOnSelect: false,
        onReady: function (selectedDates, dateStr, instance) {
            // Create year controls
            createTimeControls(instance); 
            
            // Create time dropdowns
            createTimeDropdowns(instance); 
            
            if (!instance.calendarContainer.querySelector('.ph_flatpickr_apply_button')) {
                let btnWrap = document.createElement('div');
                btnWrap.style.textAlign = 'center';
                btnWrap.style.padding = '10px';
                let applyBtn = document.createElement('button');
                applyBtn.className = 'ph_flatpickr_apply_button';
                applyBtn.textContent = 'Apply';
                applyBtn.addEventListener('click', function () {
                    if (instance.selectedDates.length) {
                        let sel = instance.selectedDates[0];
                        instance.close();
                        
                        // Sync the selected date/time to the hidden input
                        let formatted = moment(sel).format(convertToMomentFormat(date_format) + (timeEnabled ? momentTimeFormat : ''));

                        $(instance.input).val(formatted); 
                        
                        if (instance === fromFlatpickr && !fixedDate && toFlatpickr) {
                            
                            // 1. Set the minDate for the 'To' picker — allow the same day
                            toFlatpickr.set('minDate', sel);
                            toFlatpickr.set('minDate', moment(sel).subtract(1, 'days').toDate()); // temporarily subtract 1 day to allow same date
                            toFlatpickr.set('minDate', sel); // reapply real minDate (flatpickr internally allows equality)
                            
                            let fn = makeDisableFn(availableCheckOutDays, sel);
                            toFlatpickr.set('disable', [fn]);
                            
                            // Recreate the year select to reflect the new minDate/minYear
                            createTimeControls(toFlatpickr);
                            try { toFlatpickr.redraw(); } catch (e) {}
                            
                            // Check if 'To' date is now invalid (before 'From') and clear/reset if necessary
                            let currentToDate = toFlatpickr.selectedDates[0];
                            if (currentToDate && currentToDate.getTime() < sel.getTime()) {
                                toFlatpickr.clear();
                                // Set the 'To' field value to match 'From' if invalid/cleared
                                $('#ph_book_search_to').val(formatted);
                                toFlatpickr.setDate(sel, true); // Set selected date without closing
                            }
                            
                            // 2. Set the date in the picker, which updates the view to the correct month/year
                            toFlatpickr.setDate(toFlatpickr.selectedDates[0] || sel, true); // Use existing date or the new minDate
                            
                            // 3. Update the custom year select value to sync the custom dropdown
                            updateCustomYearSelect(toFlatpickr, toFlatpickr.selectedDates[0] ? toFlatpickr.selectedDates[0].getFullYear() : sel.getFullYear());
                            
                            // 4. Explicitly call changeYear to ensure the calendar view updates fully
                            toFlatpickr.changeYear(toFlatpickr.selectedDates[0] ? toFlatpickr.selectedDates[0].getFullYear() : sel.getFullYear()); 
                            
                            toFlatpickr.open();
                        }
                    }
                });
                btnWrap.appendChild(applyBtn);
                instance.calendarContainer.appendChild(btnWrap);
            }
        },
        // NEW: Add onChange to ensure the time dropdown is updated when date is changed
        onChange: function(selectedDates, dateStr, instance) {
            if(timeEnabled && selectedDates.length > 0) {
                let sel = selectedDates[0];
                let valueFormat = 'HH:mm';
                let minTimeMoment = moment(daily_min_time, valueFormat); // e.g., 16:00
                let selectedMoment = moment(sel); 
                
                // Compare only the time component
                let selTimeOnly = selectedMoment.clone().year(2000).month(0).date(1);
                let minTimeOnly = minTimeMoment.clone().year(2000).month(0).date(1);
                
                if (selTimeOnly.isBefore(minTimeOnly)) {
                    
                    // Candidate for the corrected time (16:00 on the selected day)
                    let newTimeCandidate = selectedMoment.clone()
                        .hour(minTimeMoment.hour())
                        .minute(minTimeMoment.minute());

                    // Respect Flatpickr's own minDate/time constraint (for "today")
                    let effectiveMinMoment = instance.config.minDate instanceof Date 
                        ? moment(instance.config.minDate)
                        : moment().startOf('day'); 
                    
                    if (newTimeCandidate.isBefore(effectiveMinMoment)) {
                        newTimeCandidate = effectiveMinMoment;
                    }
                    
                    sel = newTimeCandidate.toDate();
                    
                    // Force the corrected date back into the instance state (important for dropdown sync)
                    instance.setDate(sel, false); 
                    
                    // Update the input field immediately
                    let formatted = moment(sel).format(convertToMomentFormat(date_format) + momentTimeFormat);
                    $(instance.input).val(formatted);
                }
                
                // Force a rebuild/update of the dropdowns to reset selection and apply date-specific time constraints
                let timeContainer = instance.calendarContainer.querySelector(".flatpickr-time");
                if (timeContainer) timeContainer.innerHTML = '';
                createTimeDropdowns(instance);
            }
        },
        disable: [ makeDisableFn(availableCheckInDays, null) ]
    };

    let fromFlatpickr, toFlatpickr;
    if (fixedDate) {
        $('.ph_book_search_date_container1').hide();
        fromFlatpickr = flatpickr("#ph_book_search_from", {
            ...baseFlatpickrConfig,
            // FIX 4: Use initialFromDate (null if no URL param)
            defaultDate: initialFromDate, 
            disable: [ makeDisableFn(availableCheckInDays, null) ]
        });
    } else {
        fromFlatpickr = flatpickr("#ph_book_search_from", {
            ...baseFlatpickrConfig,
            mode: "single",
            // FIX 4: Use initialFromDate (null if no URL param)
            defaultDate: initialFromDate, 
            disable: [ makeDisableFn(availableCheckInDays, null) ]
        });
        
        // Configuration for the 'To' picker
        let toConfig = {
            ...baseFlatpickrConfig,
            mode: "single",
            // minDate logic should now correctly depend on initialFromDate if it exists, otherwise "today"
            minDate: initialFromDate || "today", 
            // FIX 5: Use initialToDate (null if no URL param)
            defaultDate: initialToDate, 
            disable: [ makeDisableFn(availableCheckOutDays, initialFromDate || null) ]
        };
        
        // If initialToDate is null, the defaultDate setting is omitted, leading to an empty input.
        
        toFlatpickr = flatpickr("#ph_book_search_to", toConfig);
    }

    /* --------------------------
       Dynamic Checkbox & Calendar Sync
       -------------------------- */
    function syncDayCheckboxesToHidden() {
        let checkedIn = $('.ph_checkin_day_checkbox:checked').map(function () { return $(this).val(); }).get();
        $('#ph_checkin_day_related').val(checkedIn.join(' '));
        availableCheckInDays = checkedIn.length ? checkedIn : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

        let checkedOut = $('.ph_checkout_day_checkbox:checked').map(function () { return $(this).val(); }).get();
        $('#ph_checkout_day_related').val(checkedOut.join(' '));
        availableCheckOutDays = checkedOut.length ? checkedOut : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

        if (fromFlatpickr) {
            fromFlatpickr.set('disable', [makeDisableFn(availableCheckInDays, null)]);
            try { fromFlatpickr.redraw(); } catch (e) { }
        }

        if (toFlatpickr) {
            let currentMin = toFlatpickr.config.minDate || initialFromDate || "today";
            toFlatpickr.set('disable', [makeDisableFn(availableCheckOutDays, currentMin)]);
            try { toFlatpickr.redraw(); } catch (e) { }
        }
    }

    $('.ph_checkin_day_checkbox, .ph_checkout_day_checkbox').on('change', syncDayCheckboxesToHidden);

    /* --------------------------
       UI Events & Form Submission
       -------------------------- */
    let participantCounts = [];
    
    /** Updates the total participant count display. */
    let updateTotalParticipants = () => {
        let total = $('.ph_participant-group input[type="number"]').get().reduce((sum, el) => sum + (parseInt(el.value) || 0), 0);
        $('#participant_count_display').text(total > 0 ? total : '');
    };

    $("#ph_booking_clear").click(function () {
        $('#ph_book_search_from').val('').attr('placeholder', $('#ph_from_date_text').val() || 'From');
        $('#ph_book_search_to').val('').attr('placeholder', $('#ph_to_date_text').val() || 'To');
        $('#ph_asset_name').val(assetLabel).attr('data-ph-search-asset-id', 'default');
        $("#participant_count_display").text('');
        $('.ph_participant-group input[type="number"]').val(0);
        participantCounts = [];
        if (fromFlatpickr) fromFlatpickr.clear();
        if (toFlatpickr) { 
            toFlatpickr.clear(); 
            // Also reset minDate for 'To' to 'today' (the default) when clearing
            toFlatpickr.set('minDate', "today"); 
        }
    });

    // Participant controls & asset selection
    $('.ph-booking-participant-minus, .ph-booking-participant-plus').on('click', function (event) {
        event.stopPropagation();

        // Find the input relative to this button
        let $input = $(this).siblings('input[type="number"]');
        let value = parseInt($input.val()) || 0;

        if ($(this).hasClass('minus')) {
            value = Math.max(parseInt($input.attr('min') || 0), value - 1);
        } else {
            value = Math.min(parseInt($input.attr('max') || 999), value + 1);
        }

        $input.val(value);
        updateTotalParticipants(); // your existing function
    });

    $('.ph_book_search_asset_item').on('click', function () {
        let selectedText = $(this).text();
        let assetId = $(this).attr('data-value');
        $('#ph_asset_name').val(selectedText).attr('data-ph-search-asset-id', assetId);
        $('#ph_book_search_asset_list').hide();
    });

    // Handle form submission logic
    $('#ph_booking_searchform').on('submit', function (e) {
        let assetId = $('#ph_asset_name').attr('data-ph-search-asset-id');
        $('#ph_search_asset_name').val(assetId === 'default' ? '' : assetId);
        
        participantCounts = $('.ph_participant-group').map(function () {
            let rule = $(this).data('participant').replace(/\+/g, ' ');
            let count = parseInt($(this).find('input[type="number"]').val(), 10) || 0;
            return { rule, count };
        }).get();
        $('#ph_book_search_participants').val(JSON.stringify(participantCounts));
    });

    // Initialize total participants on load
    updateTotalParticipants();

    // Handle book now on search results page.
	$('.ph_search_book_now').on('click', function (e) {

		e.preventDefault();

		const data = {
			action: 'ph_handle_book_now_from_search',
			product_id: $(this).attr('data-product-id')
		};

		$.post(ph_booking_search_data.ajaxurl, data, function (response) {

			if (response.success && response?.data?.cart_url) {
				window.location.href = response.data.cart_url;
			}
		});
	});
});