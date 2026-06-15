document.documentElement.style.setProperty(
    '--home-url',
    phive_spinner.home_link
);

const wrappers = document.querySelectorAll('.timepicker-ui');

wrappers.forEach(wrapper => {
    const picker = new window.tui.TimepickerUI(wrapper, {
        mobile: false,
        theme: 'light',
        enableSwitchIcon: true
    });
    picker.create();
});

let otpTimerInterval = null;

function startOtpCountdown($form, duration = 180) {
    let remaining = duration;

    // Clear existing timer
    if (otpTimerInterval) {
        clearInterval(otpTimerInterval);
        $form.find('a.resend').addClass('disabled')
    }

    otpTimerInterval = setInterval(function () {
        let minutes = Math.floor(remaining / 60);
        let seconds = remaining % 60;

        seconds = seconds < 10 ? '0' + seconds : seconds;

        $form.find('.otp-timer').text(
            `OTP expires in ${minutes}:${seconds}`
        );

        if (remaining <= 0) {
            clearInterval(otpTimerInterval);
            $form.find('.otp-timer').text('OTP expired.');
            if ($form.find('a.resend').hasClass('disabled')) {
                $form.find('a.resend').removeClass('disabled');
            }
            //jQuery('#otp_sent').val("0");
            //$form.find('.otp-field').hide();
        }

        remaining--;
    }, 1000);
}


jQuery(document).ready(function ($) {
    $temp = jQuery('.woocommerce-info:contains("temporary password")');
    if ($temp.length) {
        $temp.hide();
    }
});
jQuery(document).ready(function ($) {

    $(document).on("click", ".category-row .rm_tabs_li", function () {
        var filter = $(this).data("filter");

        $(".rm_tabs_li").removeClass("active");
        $(this).addClass("active");

        if (filter == 'all') {
            $(".rm_table_row").show();
        } else {
            $(".rm_table_row").hide();
            $(`.rm_table_row[data-filter="${filter}"]`).show();
        }
    });

    jQuery('link#wcgs-fancybox-css').remove();
    if (jQuery('body').hasClass('single-product')) {
        $('.phive_book_resources.shipping-price-related option').each(function () {
            var text = $(this).text().replace(/\s*\([^)]*\)/g, '');
            $(this).text(text.trim());
        });
        $('.phive_book_resources.shipping-price-related option').each(function () {
            var text = $(this).text().replace('Select any', 'Select');
            $(this).text(text.trim());
        });
        if (jQuery('.resources_section').length && jQuery('.participant_section').length) {
            jQuery('.resources_section').insertBefore('.participant_section');
        }
        jQuery('.wapf').appendTo('.booking-wraper');
        jQuery('.booking-info-wraper').insertAfter('.booking-wraper');
        if (jQuery('#addon-price').length && jQuery('.booking-info-wraper').length) {
            jQuery('.booking-info-wraper').insertAfter('#addon-price');
        }
        jQuery('.extra-resources.resources_section').appendTo('.time-picker-wraper');
        var addToCartElements = jQuery('input[name="add-to-cart"], .single_add_to_cart_button');

        // Move them inside #custom-price-breakdown
        jQuery('#btns').append(addToCartElements);
        //jQuery('.participant_inner_section').first().after('<div class="extra-charge-info">(Per person additional charge is INR 7500)</div>');


        function fetchBookingPrice() {

            // Get values from hidden inputs
            var product_id = jQuery('#phive_product_id').val();
            var book_from = jQuery('input[name="phive_book_from_date"]').val();
            var book_to = jQuery('input[name="phive_book_to_date"]').val();
            var addon_data = jQuery('input[name="ph_booking_addon_data"]').val();
            var product_addon_data = jQuery('input[name="ph_booking_product_addon_data"]').val();
            var selected_blocks = jQuery('#ph_selected_blocks').val();
            var limit = parseInt(jQuery('#cap h6').text().trim(), 10) || 0;
            var max = parseInt(jQuery('input[name="phive_book_persons[]"]').attr('max'), 10) || 0;

            // ✅ Get person count dynamically
            var persons = parseInt(jQuery('input[name="phive_book_persons[]"]').val()) || 0;

            // ✅ Calculate extra charge if persons > 5
            var extraPersons = persons > limit ? (persons - limit) : 0;
            extraPersons = Math.min(extraPersons, (max - limit)); // Max 3 extra persons

            var extraCharge = extraPersons * 7500;

            var basePrice = 0;
            var totalPrice = basePrice + extraCharge;

            // ✅ Update custom breakdown safely
            // jQuery('#adds-price').html(
            //     `<p><b>Additional Charge Per Person (${extraPersons} x 7500):</b> ₹${extraCharge}</p>`
            // );

            jQuery('.phive_book_resources').off('change').on('change', function () {
                var selectedText = jQuery(this).find('option:selected').val(); // e.g., "Upto 50 Lakhs (+₹16,667)"
                var match = selectedText.match(/\+₹([\d,]+)/); // Extract the charge (with comma)
                var settlementCharge = match ? parseInt(match[1].replace(/,/g, '')) : 0;
                var settlementCharges = match ? parseInt(match[1].replace(/,/g, '')) : '';
                jQuery('input.admin-fee').val(settlementCharges);


                // Append or update settlement charge paragraph
                jQuery('#settlement-charge').remove(); // Remove old one if exists

                jQuery('#stl-price').append(`
                            <p id="settlement-charge"><span>Admin Fee </span> ${settlementCharge}</p>
                        `);

            });
            var basePrice = parseInt(jQuery('.wapf-product-totals').data('product-price'), 10) || 0;
            var selectedDates = {};
            jQuery('.ph-calendar-date.selected-date').each(function () {

                var fullDate = jQuery(this).find('.callender-full-date').val(); // e.g., "2025-09-04 09:00"
                var displayTime = jQuery(this).find('.ph_calendar_time').text(); // e.g., "9:00 am - 1:00 pm"

                // ✅ Extract only date part (YYYY-MM-DD)
                var datePart = fullDate.split(' ')[0];

                // ✅ Convert to readable format (September 4, 2025)
                var dateObj = new Date(datePart);
                var options = { year: 'numeric', month: 'long', day: 'numeric' };
                var formattedDate = dateObj.toLocaleDateString('en-US', options);

                // ✅ Group times by date
                if (!selectedDates[formattedDate]) {
                    selectedDates[formattedDate] = [];
                }
                selectedDates[formattedDate].push(displayTime);
            });

            var $calendar = $('#ph-calendar-time');
            html = '';

            if (Object.keys(selectedDates).length > 0) {
                $('.full-day-msg').remove();

                Object.keys(selectedDates).forEach(function (date, index) {
                    var times = selectedDates[date];

                    const formatteddate = new Date(date).toLocaleDateString('en-US', {
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric'
                    });

                    var firstSlot = times[0].split('-')[0].trim();
                    var lastSlot = times[times.length - 1].split('-')[1].trim();
                    var timeRange = firstSlot + ' - ' + lastSlot;

                    var totalPrice = basePrice * times.length;
                    var formatted = totalPrice.toLocaleString('en-IN');

                    html += `<p><b>Room Details</b></p>
                            <p class="slt${index + 1}">
                                <span>${formatteddate} (${timeRange})</span> ₹${formatted}
                            </p>`;
                    var amount = parseInt(totalPrice) || 0;
                    var tenpercent = amount * .1;
                    var tenpercents = tenpercent.toLocaleString();
                    var fifteenpercent = amount * .15;
                    var fifteenpercents = fifteenpercent.toLocaleString();
                    var final = amount;
                    final = final.toLocaleString();



                    if (times.length == 2 && $(".dis_25").length == 0) {
                        var final = amount - tenpercent;
                        final = final.toLocaleString();

                        //$('<p class="full-day-msg" style="margin-top:0px;margin-bottom:10px;text-align:center">Booking for full day</p>').insertBefore($calendar);
                        html += `<p class="dis" style="">
                        10% bulk discount <span style="font-style:normal;">- ₹${tenpercents}</span>
                     </p>`;
                    }
                    if (times.length == 3 && $(".dis_25").length == 0) {
                        var final = amount - fifteenpercent;
                        final = final.toLocaleString();

                        //$('<p class="full-day-msg" style="margin-top:0px;margin-bottom:10px;text-align:center">Booking for full day</p>').insertBefore($calendar);
                        html += `<p class="dis" style="">
                        15% bulk discount <span style="font-style:normal;">- ₹${fifteenpercents}</span>
                     </p>`;
                    }
                    if (times.length > 1) {
                        jQuery(document).ajaxSuccess(function (event, xhr, settings) {
                            $('p#booking_price_text span.woocommerce-Price-amount.amount').text('₹' + final);
                            // Unbind after running to prevent infinite loops or double triggers
                            jQuery(document).off('ajaxSuccess');
                        });

                    }

                });

            }



            jQuery('#slt-time-1').html(html);



            function recalcAddons() {
                var person1Qty = parseInt(jQuery('input[rule-key="persons-1"]').val()) || 0;
                var person2Qty = parseInt(jQuery('input[rule-key="persons-2"]').val()) || 0;
                var person3Qty = parseInt(jQuery('input[rule-key="persons-3"]').val()) || 0;
                var person4Qty = parseInt(jQuery('input[rule-key="persons-4"]').val()) || 0;
                var person5Qty = parseInt(jQuery('input[rule-key="persons-5"]').val()) || 0;

                var person1Charge = person1Qty * 200;
                var person2Charge = person2Qty * 100;
                var person3Charge = person3Qty * 200;
                var person4Charge = person4Qty * 100;
                var person5Charge = person5Qty * 20;

                var html = '<p><b>Add On Services:</b></p>';

                // Meals
                html += '<p class="addon-label"><b>Meals</b></p>';
                html += '<p class="ad-charge"><span>Veg Meal (₹200 x ' + person1Qty + '):</span> ₹' + person1Charge + '</p>';

                // Refreshments
                html += '<p class="addon-label"><b>Refreshments</b></p>';
                html += '<p class="ad-charge"><span>Tea (₹100 x ' + person2Qty + '):</span> ₹' + person2Charge + '</p>';
                html += '<p class="ad-charge"><span>Coffee (₹200 x ' + person3Qty + '):</span> ₹' + person3Charge + '</p>';

                // Stationary
                html += '<p class="addon-label"><b>Stationary</b></p>';
                html += '<p class="ad-charge"><span>Notepads (₹100 x ' + person4Qty + '):</span> ₹' + person4Charge + '</p>';
                html += '<p class="ad-charge"><span>Pens (₹20 x ' + person5Qty + '):</span> ₹' + person5Charge + '</p>';


                if (person1Qty > 0 || person2Qty > 0 || person3Qty > 0 || person4Qty > 0 || person5Qty > 0) {
                    jQuery('#addon-price').show();
                } else {
                    jQuery('#addon-price').hide();
                }

                jQuery('#addon-price').html(html);
            }

        }

        jQuery('#extra-services-container .participant_inner_section').each(function () {
            var $section = jQuery(this);
            var input = $section.find('.input-person');
            var ruleKey = input.attr('rule-key');

            // Extract price from label text (e.g., "Tea (+₹100 )")
            var labelText = $section.find('.label-person').text();
            var priceMatch = labelText.match(/\+₹(\d+)/);
            var price = priceMatch ? parseInt(priceMatch[1]) : 0;
            $section.find('.label-person').attr('price', price);
            // Add Price div next to .persons-title
            if ($section.find('.price').length === 0) {
                $section.find('.persons-title').after('<div class="price">₹' + price + '</div>');
            }

            // Add Total div next to .person-value
            if ($section.find('.total-price').length === 0) {
                $section.find('.person-value').after('<div class="total-price">₹0</div>');
            }

            // On quantity change, update total
            $section.find('.input-person').on('input change', function () {
                // 				if (jQuery(this).is(':disabled')) {
                // 						 return; 
                // 				} 
                var qty = parseInt(jQuery(this).val()) || 0;
                var total = qty * price;
                $section.find('.total-price').text('₹' + total);
            });

        });
        // jQuery('#extra-services-container .label-person').each(function () {
        //     var text = jQuery(this).text();
        //     var cleanText = text.replace(/\s*\(.*?\)\s*/g, ''); // Remove anything inside ( )
        //     jQuery(this).text(cleanText);
        // });

        // ✅ Trigger on page load and when participant or calendar date changes

        fetchBookingPrice();

        jQuery(document).on('change click keyup', 'li.ph-calendar-date,input[rule-key="persons-1"],input[rule-key="persons-2"],.input-person-minus,.input-person-plus', function () {
            fetchBookingPrice();
        });

        jQuery(document).on('input', 'input[rule-key="persons-0"]', function () {
            fetchBookingPrice();
            jQuery('input[rule-key="persons-0"]').trigger('change');
        });
        // jQuery('textarea[name="wapf[field_68afdc5063a8e]"]').on('input', function () {
        //     jQuery('input[rule-key="persons-0"]').trigger('change');
        // });

        // });
        jQuery('input.input-person.shipping-price-related[rule-key="persons-0"]').val('');
        jQuery('#booking_info_text').text('');


        jQuery('.extra-resources.participant_section').appendTo('.wapf .wapf-wrapper');
        jQuery('.extra-resources.resources_section').insertAfter('.wapf');
        jQuery('.resources-wraper').append(`
            <div>
                <div class="persons-title">
                    <label class="label-resources">Admin Fee</label>
                </div>
                <div class="person-value" style="display: inline;">
                    <input type="text" value="" class="phive_book_resources admin-fee" name="admin-fee">
                </div>
            </div>
        `);
        jQuery('.extra-resources.resources_section').appendTo('.time-picker-wraper');
        jQuery('.label-person').each(function () {
            let html = jQuery(this).html();
            html = html.replace('*', '<abbr class="required" title="required">*</abbr>');
            jQuery(this).html(html);
        });

    }



    // Open popup and disable background scroll
    jQuery(document).on('click', '#open-extra-popup', function (e) {
        e.preventDefault();
        updateAddonAllCheckbox();
        jQuery('#extra-services-popup').fadeIn();
        jQuery('body').css('overflow', 'hidden'); // disable scrolling
    });


    function toggleBookingInfo() {
        var infoText = $.trim(jQuery('#booking_info_text').text());
        var priceText = $.trim(jQuery('#booking_price_text').text());

        if (infoText === '' && priceText === '') {
            jQuery('.booking-info-wraper').hide();
        } else {
            jQuery('.booking-info-wraper').show();
        }
    }

    // Run on page load
    toggleBookingInfo();

    // Run again whenever content changes
    const target = document.querySelector('.booking-info-wraper');
    if (target) {
        const observer = new MutationObserver(toggleBookingInfo);
        observer.observe(target, { childList: true, subtree: true, characterData: true });
    }

    jQuery('li.ph-calendar-date.today').trigger("click");

    jQuery(".time-picker").before('<div class="full-btn"><button id="auto-select-times" class="">Book For Full-day</button></div>');

    if (jQuery(".dis_25").length == 0) {
        jQuery(".time-picker").after('<div class="discount-area"><ul class="disc-point"><li><b>10% off</b> on booking 2 slots.</li><li><b>15% off</b> on booking 3 slots.</li></ul></div>');
    }

    jQuery(document).on("click", "#auto-select-times", function (e) {
        e.preventDefault();

        const wrap = jQuery('ul#ph-calendar-time');
        const items = jQuery("ul#ph-calendar-time li.ph-calendar-date").not(".non-working-time,.selected-date");
        if (items.length > 0) {
            wrap.addClass('selecting');
            items.each(function (index, li) {
                setTimeout(() => {
                    jQuery(li).trigger("click");

                    // When the last item is clicked, remove selecting class
                    if (index === items.length - 1) {
                        wrap.removeClass('selecting');
                    }
                }, index * 1500);
            });

        }
    });



});


jQuery(document).ready(function ($) {

    function showExtraServicesPopup() {
        if ($('#extra-services-popup').length) {
            $('#extra-services-popup').fadeIn();
            $('body').css('overflow', 'hidden');
        }
    }

    // Show popup only once per session
    if ($('body').hasClass('woocommerce-checkout')) {
        if (!sessionStorage.getItem('extraServicesPopupShown')) {
            setTimeout(function () {
                showExtraServicesPopup();
                sessionStorage.setItem('extraServicesPopupShown', '1');
            }, 1000);
        }
    }
    $(document).on('click', '.ph_book_now_button:not(.open-addon-popup)', function (e) {
        sessionStorage.removeItem('extraServicesPopupShown');
    });
    $(document).on('click', '.gw-google-login a', function (e) {
        sessionStorage.removeItem('extraServicesPopupShown');
    });
    $(document).on('click', 'button#otp-submit', function (e) {
        sessionStorage.removeItem('extraServicesPopupShown');
        var email_field = $('#otp_phone').val().trim();

        // remove old error
        $('.otp-error').remove();
        $('#otp_phone').css('border-color', '');

        if (email_field === "") {
            e.preventDefault();
            e.stopImmediatePropagation();

            // red border
            $('#reg_first_name').css('border-color', '#d4d7e3').focus();

            // show error message exactly below input field
            $('<div class="otp-error" style="color:red; font-size:14px; margin-top:5px;">Enter Email</div>')
                .insertAfter('#otp_phone');

            return false;
        }
    });
    // if (jQuery('body').hasClass('woocommerce-checkout')) {
    //     setTimeout(function () {
    //         if (jQuery('#extra-services-popup').length) {
    //             jQuery('#extra-services-popup').fadeIn();
    //             jQuery('body').css('overflow', 'hidden');
    //         }
    //     }, 1000);
    // }



    // Try to fetch participants from DOM, fallback to 0
    var participants = parseInt(jQuery('.variation-TotalMembers p').text()) || 0;


    function updateRowTotal($row) {
        var price = parseFloat($row.data('price')) || 0;
        var qty = parseInt($row.find('.addon-qty').val()) || 0;

        // calculate total
        var total = price * qty;

        // store total in a data attribute and update display
        $row.data('total', total);
        $row.find('.addon-total').text('₹' + total.toFixed(2));
    }

    // On quantity input change
    jQuery(document).on('input change', '.addon-qty', function () {
        updateRowTotal(jQuery(this).closest('.service-row'));
    });
    jQuery(document).on('click', 'a.member-popup', function (e) {
        e.preventDefault();
        jQuery('#update-member-modal').fadeIn();
    });

    // On "Select for All" checkbox change
    jQuery(document).on('change', '.addon-all:not([name="addon_all[3015]"])', function () {
        var $row = jQuery(this).closest('.service-row');
        var $qtyInput = $row.find('.addon-qty');

        if (jQuery(this).is(':checked')) {
            let participants = 0;

            let total_input = jQuery('input#total_member');
            if (total_input.length) {
                participants = parseInt(total_input.val()) || 0;
            }

            let mm_val = parseInt(jQuery('input[name="mm"]').val()) || 0;
            if (participants === 0 && mm_val !== 0) {
                participants = mm_val;
            }

            if (participants === 0) {
                jQuery('#update-member-modal').fadeIn();
                //jQuery(this).prop('checked', false);
                localStorage.setItem('savedElement', jQuery(this).attr('name'));
                return; // stop further execution
            }

            // Initial calculated quantity
            let qty = participants > 0 ? participants : 0;

            // --- NEW LOGIC STARTS HERE ---
            // Get the max attribute value
            let maxVal = parseInt($qtyInput.attr('max'));

            // Check if maxVal is a valid number and if qty exceeds it
            if (!isNaN(maxVal) && qty > maxVal) {
                qty = maxVal;
            }
            // --- NEW LOGIC ENDS HERE ---

            //$qtyInput.val(qty).addClass('input-disabled').prop('disabled', true);
            $qtyInput.val(qty);

        } else {
            //$qtyInput.val(0).removeClass('input-disabled').prop('disabled', false);
            $qtyInput.val(0);
        }

        // Recalculate row total after checkbox change
        updateRowTotal($row);
    });
    jQuery(document).on('change', '.addon-all[name="addon_all[3015]"]', function () {
        var $row = jQuery(this).closest('.service-row');
        var $qtyInput = $row.find('.addon-qty');

        if (jQuery(this).is(':checked')) {
            let participants = 0;

            let total_input = jQuery('input#total_member');
            if (total_input.length) {
                participants = 1;
            }

            // Initial calculated quantity
            let qty = participants > 0 ? participants : 0;

            // --- NEW LOGIC STARTS HERE ---
            // Get the max attribute value
            let maxVal = parseInt($qtyInput.attr('max'));

            // Check if maxVal is a valid number and if qty exceeds it
            if (!isNaN(maxVal) && qty > maxVal) {
                qty = maxVal;
            }
            // --- NEW LOGIC ENDS HERE ---

            //$qtyInput.val(qty).addClass('input-disabled').prop('disabled', true);
            $qtyInput.val(qty);

        } else {
            //$qtyInput.val(0).removeClass('input-disabled').prop('disabled', false);
            $qtyInput.val(0);
        }

        // Recalculate row total after checkbox change
        updateRowTotal($row);
    });
    jQuery(document).on('input', '.addon-qty', function () {
        let max = parseInt(jQuery(this).attr('max'));
        let val = parseInt(jQuery(this).val());

        if (val > max) {
            jQuery(this).val(max);
        }
    });
    // Proceed button
    jQuery(document).on('click', 'body:not(.single-product) #proceed-btn', function (e) {
        e.preventDefault();
        var $resourceSelect = jQuery('select.phive_book_resources.shipping-price-related');
        $resourceSelect.trigger('change').trigger('input').trigger('blur');
        jQuery('#extra-services-popup').fadeOut();
        jQuery('body').css('overflow', 'auto');
    });

    jQuery(document).on('click', '#confirm-no', function (e) {
        e.preventDefault();
        jQuery('#extra-services-popup').fadeOut();
        jQuery('#confirm-modal').fadeOut();
        jQuery('body').css('overflow', 'auto');
    });
    jQuery(document).on('click', '#member-no', function (e) {
        e.preventDefault();
        const saved = localStorage.getItem('savedElement');
        if (saved) {
            const savedname = $(`[name="${saved}"]`);
            savedname.prop('checked', false);
            localStorage.removeItem('savedElement');
        }
        jQuery('#update-member-modal').fadeOut();
    });
    jQuery(document).on('click', '.woocommerce-checkout #member-yes', function (e) {
        e.preventDefault();
        var oldCount = jQuery('.count').text();
        var newCount = jQuery('input#member-count').val();

        jQuery(this).addClass('loading');

        jQuery.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'update_members',
                number: newCount
            },
            success: function (response) {
                if (response.success) {
                    // location.reload();
                    if (oldCount !== newCount) {
                        jQuery('input.addon-all').prop('checked', false);
                        jQuery('input.addon-qty').prop('disabled', false).removeClass('input-disabled');
                    }
                    jQuery('.slider.round:not(.operator)').each(function () {
                        jQuery(this).text(newCount);
                    });
                    const saved = localStorage.getItem('savedElement');
                    let mm_val = jQuery('input[name="mm"]');
                    let total_input = jQuery('input#total_member');
                    let span = jQuery('.count');
                    if (saved) {
                        const savedname = $(`[name="${saved}"]`);
                        savedname.closest('.service-row').find('.addon-qty').val(newCount);
                        const pr = newCount * parseFloat(savedname.closest('.service-row').data('price')).toFixed(2);
                        savedname.closest('.service-row').find('.addon-total').html('₹' + pr);
                        localStorage.removeItem('savedElement');
                    }
                    if (mm_val.length) {
                        mm_val.val(newCount);
                    }
                    if (total_input.length) {
                        total_input.val(newCount);
                    }
                    if (span.length) {
                        span.text(newCount);
                    }
                    total_input.val(newCount);
                    jQuery('.woocommerce-checkout #member-yes').removeClass('loading');
                    jQuery('div#update-member-modal').fadeOut();
                } else {
                    alert(response.data || 'Something went wrong.');
                }
            },
            error: function (xhr, status, err) {
                alert('AJAX error: ' + err);
            }
        });

    });

    jQuery(document).on('click', '.woocommerce-cart #member-yes', function (e) {
        e.preventDefault();
        var oldCount = jQuery('.count').text();
        var newCount = jQuery('input#member-count').val();
        var cartName = jQuery('button#proceed-btn').data('cart-name');
        jQuery(this).addClass('loading');
        jQuery.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'update_cart_members',
                number: newCount,
                cart: cartName
            },
            success: function (response) {
                if (response.success) {
                    if (oldCount !== newCount) {
                        jQuery('input.addon-all').prop('checked', false);
                        jQuery('input.addon-qty').prop('disabled', false).removeClass('input-disabled');
                    }
                    jQuery('.slider.round:not(.operator)').each(function () {
                        jQuery(this).text(newCount);
                    });
                    const saved = localStorage.getItem('savedElement');
                    let mm_val = jQuery('input[name="mm"]');
                    let total_input = jQuery('input#total_member');
                    let span = jQuery('.count');
                    if (saved) {
                        const savedname = $(`[name="${saved}"]`);
                        savedname.closest('.service-row').find('.addon-qty').val(newCount);
                        const pr = newCount * parseFloat(savedname.closest('.service-row').data('price')).toFixed(2);
                        savedname.closest('.service-row').find('.addon-total').html('₹' + pr);
                        localStorage.removeItem('savedElement');
                    }
                    if (mm_val.length) {
                        mm_val.val(newCount);
                    }
                    if (total_input.length) {
                        total_input.val(newCount);
                    }
                    if (span.length) {
                        span.text(newCount);
                    }
                    total_input.val(newCount);
                    jQuery('.woocommerce-checkout #member-yes').removeClass('loading');
                    jQuery('div#update-member-modal').fadeOut();
                } else {
                    alert(response.data || 'Something went wrong.');
                }
            },
            error: function (xhr, status, err) {
                alert('AJAX error: ' + err);
            }
        });

    });

    jQuery(document).on('click', '.woocommerce-view-order #member-yes', function (e) {
        e.preventDefault();
        var oldCount = jQuery('.count').text();
        var newCount = jQuery('input#member-count').val();
        var order = jQuery('input[name="order_id"]').val();
        jQuery(this).addClass('loading');
        jQuery.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'update_order_members',
                number: newCount,
                order_id: order
            },
            success: function (response) {
                if (response.success) {
                    if (oldCount !== newCount) {
                        jQuery('input.addon-all').prop('checked', false);
                        jQuery('input.addon-qty').prop('disabled', false).removeClass('input-disabled');
                    }
                    jQuery('.slider.round:not(.operator)').each(function () {
                        jQuery(this).text(newCount);
                    });
                    const saved = localStorage.getItem('savedElement');
                    let mm_val = jQuery('input[name="mm"]');
                    let total_input = jQuery('input#total_member');
                    let span = jQuery('.count');
                    if (saved) {
                        const savedname = $(`[name="${saved}"]`);
                        savedname.closest('.service-row').find('.addon-qty').val(newCount);
                        const pr = newCount * parseFloat(savedname.closest('.service-row').data('price')).toFixed(2);
                        savedname.closest('.service-row').find('.addon-total').html('₹' + pr);
                        localStorage.removeItem('savedElement');
                    }
                    if (mm_val.length) {
                        mm_val.val(newCount);
                    }
                    if (total_input.length) {
                        total_input.val(newCount);
                    }
                    if (span.length) {
                        span.text(newCount);
                    }
                    total_input.val(newCount);
                    jQuery('.woocommerce-view-order #member-yes').removeClass('loading');
                    jQuery('div#update-member-modal').fadeOut();
                } else {
                    alert(response.data || 'Something went wrong.');
                }
            },
            error: function (xhr, status, err) {
                alert('AJAX error: ' + err);
            }
        });

    });

    jQuery(document).on('click', '.single-product #member-yes', function (e) {
        e.preventDefault();
        var oldCount = jQuery('.count').text();
        var newCount = jQuery('input#member-count').val();
        if (oldCount !== newCount) {
            jQuery('input.addon-all').prop('checked', false);
            jQuery('input.addon-qty').prop('disabled', false).removeClass('input-disabled');
        }
        jQuery('input[data-name="Total Participants"]').val(newCount);
        jQuery('span.count').text(newCount);
        jQuery('input#total_member').val(newCount);
        jQuery('.slider.round:not(.operator)').each(function () {
            jQuery(this).text(newCount);
        });
        const saved = localStorage.getItem('savedElement');
        if (saved) {
            const savedname = $(`[name="${saved}"]`);
            savedname.closest('.service-row').find('.addon-qty').val(newCount);
            const pr = newCount * parseFloat(savedname.closest('.service-row').data('price')).toFixed(2);
            savedname.closest('.service-row').find('.addon-total').html('₹' + pr);
            localStorage.removeItem('savedElement');
        }
        jQuery('div#update-member-modal').fadeOut();
    });

    jQuery(document).on("input", "textarea.auto-height", function () {
        this.style.height = "auto";   // reset first
        this.style.height = (this.scrollHeight) + "px"; // set to scroll height
    });
    jQuery(document).on("input", "textarea.wapf-input", function () {
        this.style.height = "38.4px";   // reset first
        this.style.height = (this.scrollHeight) + "px"; // set to scroll height
    });

    function addAddonsHeading() {
        // Remove existing heading first (avoid duplicates)
        //jQuery('tr.addons-head').remove();

        jQuery('tr.cart_item').each(function () {
            if (jQuery(this).find('.phive_booking').length) {
                //jQuery(this).after('<tr class="cart_item addons-head"><td colspan="2"><div class="addon-head"><div>Add-ons</div><a id="open-extra-popup" class="edit-svg" href=""><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" role="img"><title>Plus</title><g fill="none"stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></g></svg></a></div></td></tr>');
            }
        });
        var cartRows = jQuery('tr.cart_item');
        cartRows.each(function () {
            var row = jQuery(this);
            if (row.find('.phive_booking').length > 0) {
                // Move booking row to top
                row.prependTo(row.parent());

                var addons = row.nextAll('tr.cart_item.addons-head').first();
                if (addons.length) {
                    addons.insertAfter(row);
                }
            }
        });

        var $tt = jQuery('tr.cart_item span.tooltip_box');

        var $target = jQuery("tr.cart_item td.product-total");

        if ($tt.length) {
            $target.append($tt); // ✅ Correct direction
        }

        // jQuery('#update-booking').remove();
        // var $updateBooking = jQuery(`
        //     <a id="update-booking" class="edit-svg" href="">
        //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" 
        //             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        //             <path d="M12 20h9"></path>
        //             <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
        //         </svg>
        //     </a>
        // `);

        // var $target = jQuery("table.shop_table.woocommerce-checkout-review-order-table th.product-name");

        // if ($target.length) {
        //     $target.append($updateBooking); // ✅ Correct direction
        // }


    }

    // Run on load
    //addAddonsHeading();

    // Run again when WooCommerce updates checkout fragments
    //jQuery(document.body).on('updated_checkout', addAddonsHeading);
    jQuery(document.body).on('updated_checkout', function () {
        jQuery('label').each(function () {
            let clean = jQuery(this).html().replace(/&nbsp;/g, '').replace(/\s*:/g, '');
            jQuery(this).html(clean);
        });
    });

});
function updateAddonAllCheckbox($context = jQuery(document)) {
    const mem = parseInt(jQuery('#total_member').val() || 0, 10);

    $context.find('.addon-qty').each(function () {
        const $this = jQuery(this);
        const currentVal = parseInt($this.val() || 0, 10);
        const originalVal = parseInt($this.data('original') || 0, 10);
        const $checkbox = $this.closest('.service-row').find('.addon-all');

        if (currentVal !== 0) {
            const isAll = currentVal === mem;
            $checkbox.prop('checked', isAll);
            //$this.prop('disabled', isAll);

            // Toggle class based on disabled state
            if (isAll) {
                //$this.addClass('input-disabled');
            } else {
                //$this.removeClass('input-disabled');
            }
        } else {
            // If quantity is 0, uncheck checkbox and enable input
            $checkbox.prop('checked', false);
            $this.prop('disabled', false).removeClass('input-disabled');
        }
    });
}


// Example usage:
jQuery(document).ready(function ($) {
    updateAddonAllCheckbox();
    // $(document).on('input change', '.addon-qty', function() {
    //     updateAddonAllCheckbox($(this).closest('.service-row'));
    // });
});


function syncOriginalsFromAttrs() {
    jQuery('.addon-qty').each(function () {
        var $el = jQuery(this);
        var origAttr = $el.attr('data-original');

        // ensure stored as number
        var origVal = (typeof origAttr !== 'undefined') ? parseInt(origAttr, 10) || 0 : (parseInt($el.val(), 10) || 0);
        $el.data('original', origVal);
        $el.attr('data-original', origVal); // keep attribute consistent
    });

    jQuery('.remarks-row textarea').each(function () {
        var $el = jQuery(this);
        var origAttr = $el.attr('data-original');
        var origVal = (typeof origAttr !== 'undefined') ? String(origAttr) : $el.val().trim();
        $el.data('original', origVal);
        $el.attr('data-original', origVal);
    });

}

// Initialize on DOM ready
syncOriginalsFromAttrs();

// Re-sync when WooCommerce updates checkout fragments (in case popup HTML is re-rendered)
jQuery(document.body).on('updated_checkout', function () {
    // small timeout to ensure fragments inserted
    setTimeout(syncOriginalsFromAttrs, 50);
});

// 2) Close popup: compare current vs stored original in jQuery data
jQuery(document).on('click', 'body:not(.single-product) #close-popup', function (e) {
    e.preventDefault();

    var qtyChanged = false;
    var remarkChanged = false;

    jQuery('.addon-qty').each(function () {
        var $el = jQuery(this);
        var current = parseInt($el.val(), 10) || 0;
        var original = parseInt($el.data('original'), 10) || 0;

        if (current !== original) {
            qtyChanged = true;
            return false;
        }
    });

    if (!qtyChanged) {
        jQuery('.remarks-row textarea').each(function () {
            var $el = jQuery(this);
            var current = $el.val().trim();
            var original = $el.data('original') || '';

            if (current !== original) {
                remarkChanged = true;
                return false;
            }
        });
    }

    if (qtyChanged || remarkChanged) {
        jQuery('#confirm-modal').fadeIn();
    } else {
        jQuery('#extra-services-popup').fadeOut();
        jQuery('body').css('overflow', 'auto');
        // Scroll to top
        jQuery('html, body').animate({ scrollTop: 0 }, 200);
    }
});

// 3) Proceed / Confirm yes — ajax send (your existing code) + update both attr & jQuery data BEFORE closing
jQuery(document).on('click', 'form.checkout #proceed-btn,form.checkout #confirm-yes', function (e) {
    e.preventDefault();
    jQuery('body').css('overflow', 'auto');
    jQuery('#confirm-modal').fadeOut();

    var addons = [];
    var remarks = {};

    jQuery('.addon-qty').each(function () {
        var $el = jQuery(this);
        var qty = parseInt($el.val(), 10) || 0;
        var product_id = $el.data('product_id');
        addons.push({ id: product_id, qty: qty });
    });

    jQuery('.remarks-row textarea').each(function () {
        var $el = jQuery(this);
        var match = $el.attr('name').match(/\[(.*?)\]/);
        if (!match) return;
        var category_slug = match[1];
        var remark_val = $el.val();
        remarks[category_slug] = remark_val.trim();
    });

    var anyQty = addons.some(function (a) { return a.qty > 0; });
    var anyRemark = Object.keys(remarks).length > 0 && Object.values(remarks).some(function (r) { return r.trim() !== ''; });

    if (!anyQty && !anyRemark) {
        if (!confirm('You have set all quantities to 0. This will remove all addon products. Continue?')) {
            return;
        }
    }

    // AJAX
    jQuery.ajax({
        url: wc_add_to_cart_params.ajax_url,
        type: 'POST',
        data: {
            action: 'add_addon_products_to_cart',
            addons: addons,
            remarks: remarks
        },
        success: function (response) {
            if (response.success) {

                // 3a) Update originals FIRST (both attr and jQuery data)
                jQuery('.addon-qty').each(function () {
                    var $el = jQuery(this);
                    var val = parseInt($el.val(), 10) || 0;
                    $el.attr('data-original', val);
                    $el.data('original', val);
                });
                jQuery('.remarks-row textarea').each(function () {
                    var $el = jQuery(this);
                    var val = $el.val().trim();
                    $el.attr('data-original', val);
                    $el.data('original', val);
                });

                // 3b) hide popup and refresh checkout
                jQuery('#extra-services-popup').fadeOut();
                jQuery('body').css('overflow', 'auto');

                setTimeout(() => {
                    // Scroll to top
                    jQuery('html, body').animate({ scrollTop: 0 }, 200);
                }, 200);
                
                
                jQuery('body').trigger('update_checkout');

            } else {
                alert(response.data || 'Something went wrong.');
            }
        },
        error: function (xhr, status, err) {
            alert('AJAX error: ' + err);
        }
    });
});

/* JS for Update Booking Popup */

jQuery(document).on('click', 'form.checkout #update-booking', function (e) {
    e.preventDefault();
    jQuery('#update-booking-modal').fadeIn();
});
jQuery(document).on('click', 'form.checkout .calender-popup', function (e) {
    e.preventDefault();
    jQuery('#update-booking-slots').fadeIn();
});

jQuery(document).on('click', '#slots-no', function (e) {
    e.preventDefault();
    jQuery('#update-booking-slots').fadeOut();
})

jQuery(document).on('click', '.saved-carts-wrapper .update-booking', function (e) {
    e.preventDefault();
    jQuery('#update-saved-cart').fadeIn();

    // Store href for Yes button
    var queryPar = jQuery(this).attr('href');
    jQuery('#update-saved-cart').find('.modal-content button#booking-yes').attr('href', queryPar);
});

// No → close modal
jQuery(document).on('click', 'form.checkout #booking-no', function (e) {
    e.preventDefault();
    jQuery('#update-booking-modal').fadeOut();
});

jQuery(document).on('click', '.saved-carts-wrapper #booking-no', function (e) {
    e.preventDefault();
    jQuery('#update-saved-cart').fadeOut();
});

// Yes → redirect
jQuery(document).on('click', '#booking-yes', function (e) {
    e.preventDefault();

    var redirectUrl = jQuery(this).data('href');
    if (redirectUrl) {
        window.location.href = redirectUrl;
    }

    var $bookingRow = jQuery('tr.cart_item:has(.phive_booking)');
    var productURL = $bookingRow.find('.phive_booking').data('url');

    // Get Case ID and Case Title
    var caseID = $bookingRow.find('dd.variation-CaseID p').text().trim();
    var caseTitle = $bookingRow.find('dd.variation-CaseTitle p').text().trim();

    // Get Booked From / Booked To text
    var bookedFrom = $bookingRow.find('dd.variation-BookedFrom p').text().trim();
    var bookedTo = $bookingRow.find('dd.variation-BookedTo p').text().trim();

    // Convert to Date objects
    var fromDate = new Date(bookedFrom);
    var toDate = new Date(bookedTo);

    // Format YYYY-MM-DD
    var yyyy = fromDate.getFullYear();
    var mm = ('0' + (fromDate.getMonth() + 1)).slice(-2);
    var dd = ('0' + fromDate.getDate()).slice(-2);
    var date = yyyy + "-" + mm + "-" + dd;

    // Format HH:MM (24h)
    function formatTime(d) {
        return ('0' + d.getHours()).slice(-2) + ":" + ('0' + d.getMinutes()).slice(-2);
    }
    var fromTime = formatTime(fromDate);
    var toTime = formatTime(toDate);
    var before4h = new Date(toDate.getTime() - (4 * 60 * 60 * 1000));
    var toSlot = formatTime(before4h);

    // Settlement Amount
    var settlement = $bookingRow.find('dd p').filter(function () {
        return /\+\s*₹\d+/.test(jQuery(this).text());
    }).first().text().trim();

    // Total Participants
    var members = $bookingRow.find('dd.variation-TotalMembers p').text().trim();

    var queryUrl = jQuery(this).attr('href');

    var query = "?date=" + date +
        "&slot=" + fromTime + "," + toSlot +
        "&amount=" + encodeURIComponent(settlement) +
        "&members=" + encodeURIComponent(members) +
        "&case_id=" + encodeURIComponent(caseID) +
        "&cart=" + encodeURIComponent('1') +
        "&case_title=" + encodeURIComponent(caseTitle);



    jQuery.post(typeof my_ajax_obj !== 'undefined' ? my_ajax_obj.ajaxurl : ajaxurl, {
        action: "ph_delete_freezed_posts"
    }).always(function () {

        if (redirectUrl) {
            window.location.href = redirectUrl;
        }

        // Redirect
        if (queryUrl) {
            window.location.href = queryUrl;
        } else {
            if (productURL) {
                window.location.href = productURL + query;
            } else {
                return;
            }
        }

    });

});


jQuery(document).ready(function () {


    var $bookingRow = jQuery('tr.cart_item:has(.phive_booking)');
    var productURL = $bookingRow.find('.phive_booking').data('url');
    var productID = $bookingRow.find('.phive_booking').data('product_id');

    // Get Case ID and Case Title
    var caseID = $bookingRow.find('dd.variation-CaseID p').text().trim();
    var caseTitle = $bookingRow.find('dd.variation-CaseTitle p').text().trim();

    // Get Booked From / Booked To text
    var bookedFrom = $bookingRow.find('dd.variation-BookedFrom p').text().trim();
    var bookedTo = $bookingRow.find('dd.variation-BookedTo p').text().trim();

    // Convert to Date objects
    var fromDate = new Date(bookedFrom);
    var toDate = new Date(bookedTo);

    // Format YYYY-MM-DD
    var yyyy = fromDate.getFullYear();
    var mm = ('0' + (fromDate.getMonth() + 1)).slice(-2);
    var dd = ('0' + fromDate.getDate()).slice(-2);
    var date = yyyy + "-" + mm + "-" + dd;

    // Format HH:MM (24h)
    function formatTime(d) {
        return ('0' + d.getHours()).slice(-2) + ":" + ('0' + d.getMinutes()).slice(-2);
    }
    var fromTime = formatTime(fromDate);
    var toTime = formatTime(toDate);
    var before4h = new Date(toDate.getTime() - (4 * 60 * 60 * 1000));
    var toSlot = formatTime(before4h);

    // Settlement Amount
    var settlement = $bookingRow.find('dd p').filter(function () {
        return /\+\s*₹\d+/.test(jQuery(this).text());
    }).first().text().trim();

    // Total Participants
    var members = $bookingRow.find('dd.variation-TotalMembers p').text().trim();

    // Build query string
    var query = "?date=" + date +
        "&slot=" + fromTime + "," + toSlot +
        "&amount=" + encodeURIComponent(settlement) +
        "&members=" + encodeURIComponent(members) +
        "&case_id=" + encodeURIComponent(caseID) +
        "&case_title=" + encodeURIComponent(caseTitle) +
        "&cart=" + encodeURIComponent('1') +
        "&product_id=" + encodeURIComponent(productID);

    function passQuery() {
        if (jQuery('a.calender-popup').length) {
            jQuery('a.calender-popup').attr('href', productURL + query);
        }
    }

    passQuery();

    jQuery(document.body).on('updated_checkout', passQuery);
});


jQuery(document).ready(function () {


    function getQueryParam(name) {
        var params = new URLSearchParams(window.location.search);
        return params.get(name);
    }
    var params = new URLSearchParams(window.location.search);
    if (params.has('amount') || params.has('members') || params.has('case_id')) {
        // Change the button text
        jQuery('button.ph_book_now_button').text('Update Booking');
    }

    // if (params.has('cart')) {
    if (window.location.href.includes('product') && !params.has('members')) {
        jQuery('button.ph_book_now_button').before('<input type="hidden" name="fresh-cart" value="1">');
        jQuery('button.ph_book_now_button').after('<button type="submit" class="single_add_to_cart_button button alt ph_book_now_button open-addon-popup disabled" name="woocommerce_checkout_place_order" id="place_order2" value="Pay Now" data-cart-name="no_cart" data-value="Add to Cart" >Add to Cart</button>');
    }
    jQuery('button.ph_book_now_button').before('<input type="hidden" name="final-cart" value="1">');

    // }



    var caseID = getQueryParam('case_id');
    if (caseID !== null) {
        var $caseInput = jQuery('input.wapf-input[data-field-id="68c7cf78747bb"]');
        if ($caseInput.length) {
            $caseInput.val(caseID).trigger('input').trigger('change');
        }
    }

    // Auto-fill Case Title
    var caseTitle = getQueryParam('case_title');
    if (caseTitle !== null) {
        var $caseTitleInput = jQuery('textarea.wapf-input[data-field-id="68afdc5063a8e"]'); // replace with actual data-field-id of Case Title
        if ($caseTitleInput.length) {
            $caseTitleInput.text(caseTitle).trigger('input').trigger('change');
        }
    }

    // Optional: auto-fill participants
    var members = getQueryParam('members');
    if (members !== null) {
        var $input = jQuery('input.input-person.shipping-price-related');
        var max = parseInt($input.attr('max')) || 0;
        var min = parseInt($input.attr('min')) || 0;
        var val = parseInt(members);

        if (val > max) val = max;
        if (val < min) val = min;

        $input.val(val).attr('last-val', val);
    }

    // Optional: auto-fill settlement select
    var amount = getQueryParam('amount');
    if (amount !== null) {
        var $select = jQuery('select.phive_book_resources');
        $select.val(amount).trigger('change');
    }

});


// document.addEventListener('DOMContentLoaded', function() {
//     const input = document.querySelector('.input-person.shipping-price-related');
//     const popup = document.getElementById('participant-popup');
//     const bookBtn = document.getElementById('book-aravalli');
//     const adminBtn = document.getElementById('contact-admin');
//     const cancelBtn = document.getElementById('cancel-popup');

//     if (input && popup) {
//         input.addEventListener('input', function() {
//             const max = parseInt(input.getAttribute('max'), 10);
//             const value = parseInt(input.value, 10);

//             if (value > max) {
//                 popup.style.display = 'flex';
//             }
//         });
//     }

//     if (bookBtn) {
//         bookBtn.addEventListener('click', function(e) {
//             e.preventDefault();
//             window.location.href = '/product/aravalli';
//         });
//     }

//     if (adminBtn) {
//         adminBtn.addEventListener('click', function(e) {
//             e.preventDefault();
//             window.location.href = '/contact-us';
//         });
//     }

//     if (cancelBtn && popup && input) {
//         cancelBtn.addEventListener('click', function(e) {
//             e.preventDefault();
//             popup.style.display = 'none';
//             const max = parseInt(input.getAttribute('max'), 10);
//             input.value = max;
//         });
//     }
// });


// document.addEventListener('DOMContentLoaded', function () {
//     const fromInput = document.querySelector('input[name="phive_book_from_date"]');
//     const toInput = document.querySelector('input[name="phive_book_to_date"]');
//     const roomCharge = document.getElementById('room-charge');

//     if (!fromInput || !toInput || !roomCharge) {
//         console.warn("Required elements not found");
//         return;
//     }

//     // Get base price dynamically from roomCharge text
//     const basePriceMatch = roomCharge.textContent.match(/₹\s*([\d,]+)/);
//     const basePrice = basePriceMatch ? parseInt(basePriceMatch[1].replace(/,/g, '')) : 0;

//     function updateRoomCharge() {
//         const fromVal = fromInput.value;
//         const toVal = toInput.value;

//         if (!fromVal || !toVal) return; // do nothing if empty

//         const fromDate = new Date(fromVal);
//         const toDate = new Date(toVal);

//         if (isNaN(fromDate) || isNaN(toDate)) return; // invalid date

//         const diffHours = (toDate - fromDate) / (1000 * 60 * 60);

//         let price = basePrice;
//         if (diffHours > 4) {
//             price = basePrice * 2; // double if more than 4 hours
//         }

//         roomCharge.innerHTML = `<b>Room Charge</b> ₹${price.toLocaleString()}`;
//     }

//     // Run once on page load
//     updateRoomCharge();

//     // Watch for programmatic value changes in hidden inputs
//     const observer = new MutationObserver(updateRoomCharge);

//     observer.observe(fromInput, { attributes: true, attributeFilter: ['value'] });
//     observer.observe(toInput, { attributes: true, attributeFilter: ['value'] });
// });


jQuery(function ($) {
    $(document).on('click', '#place_order', function (e) {
        let error = false;
        let error2 = false;
        let errorMessage = "";
        let $btn = $(this);
        //$btn.prop("disabled", true);

        $('form.checkout input[aria-required="true"], form.checkout select[aria-required="true"],form#order_review input[aria-required="true"], form#order_review select[aria-required="true"]').each(function () {
            if ($(this).val() === '' || $(this).val() === null) {
                error2 = true;
                var fieldLabel = $('label[for="' + $(this).attr('id') + '"]').text().replace('*', '').trim();
                var ph = $(this).attr('placeholder');
                // Add WooCommerce error class for styling
                $(this).addClass('woocommerce-invalid').removeClass('woocommerce-validated');

                $(this).next('.error').remove();

                // Add error message if not exists
                if (!$(this).next('.error').length) {
                    if (fieldLabel) {
                        $(this).after('<span class="error" style="color:red;display:block;position: absolute;" role="alert">This field is required.</span>');
                    } else {
                        $(this).after('<span class="error" style="color:red;display:block;position: absolute;" role="alert">This field is required.</span>');
                    }


                }
            } else if ($(this).val() !== '' && $(this).val().length < 10 && this.name && (this.name.startsWith('group_member_phone'))) {

                error2 = true;
                $(this).addClass('woocommerce-invalid').removeClass('woocommerce-validated');

                $(this).next('.error').remove();

                $(this).after('<span class="error" style="color:red;display:block;position: absolute;" role="alert">Enter a valid phone no.</span>');

            } else {
                $(this).removeClass('woocommerce-invalid').addClass('woocommerce-validated');
                $(this).next('.error').remove();
            }
        });
        jQuery('input[type="email"]').each(function () {
            validateEmailField(this);
        });

        // --- NEW: Uniqueness Check ---
        let emails = [];
        let phones = [];

        let primaryEmail = $('#billing_email').val();
        if (primaryEmail) emails.push(primaryEmail.trim().toLowerCase());

        let primaryPhone = $('#billing_phone').val();
        if (!primaryPhone) primaryPhone = $('#billing_mobile').val();
        if (primaryPhone) phones.push(primaryPhone.trim());

        $('input[name^="group_member_email"]').each(function () {
            let val = $(this).val().trim().toLowerCase();
            if (val) {
                if (emails.includes(val)) {
                    error2 = true;
                    $(this).addClass('woocommerce-invalid').removeClass('woocommerce-validated');
                    $(this).next('.error').remove();
                    $(this).after('<span class="error" style="color:red;display:block;position: absolute;" role="alert">Email must be unique.</span>');
                } else {
                    emails.push(val);
                }
            }
        });

        $('input[name^="group_member_phone"]').each(function () {
            let val = $(this).val().trim();
            if (val) {
                if (phones.includes(val)) {
                    error2 = true;
                    $(this).addClass('woocommerce-invalid').removeClass('woocommerce-validated');
                    $(this).next('.error').remove();
                    $(this).after('<span class="error" style="color:red;display:block;position: absolute;" role="alert">Phone must be unique.</span>');
                } else {
                    phones.push(val);
                }
            }
        });
        // --- END NEW ---

        if (error2) {
            return false;
        }

        let fromDate = $('#phive_book_from_date').val();
        let toDate = $('#phive_book_to_date').val();
        let productID = $('#product_id').val();

        //$btn.addClass('has_spinner');

        if (fromDate) {
            let from = new Date(fromDate);
            let now = new Date();
            if (from < now) {
                error = true;
                errorMessage = "You cannot book a room for Past date/slot. Please select a new date/slot.";
            }
        }

        if (!error && fromDate && toDate) {
            $.ajax({
                url: phive_spinner.ajaxurl,
                type: "POST",
                async: false, // keep synchronous here, since it's validation
                data: {
                    action: "phive_check_slot",
                    from: fromDate,
                    to: toDate,
                    id: productID
                },
                success: function (response) {
                    if (response.booked) {
                        error = true;
                        errorMessage = "This slot is booked. Please select another date or time slot.";
                    }
                }
            });
        }

        if (error) {
            e.preventDefault(); // stop checkout 
            if (confirm(errorMessage)) {
                $('.calender-popup').trigger('click');
            }
            return false;
        }

    });
});


// function gw_open_google_popup() {
//     var w = 620, h = 680;
//     var left = (screen.width - w) / 2;
//     var top = (screen.height - h) / 2;
//     var returnTo = encodeURIComponent(window.location.href);
//     var url = "<?php echo $entry_url; ?>?popup=1&return_to=" + returnTo;
//     var popup = window.open(url, 'GoogleLogin', 'width=' + w + ',height=' + h + ',top=' + top + ',left=' + left);

//     if (!popup) {
//         alert('Popup blocked. Please allow popups for this site and try again.');
//         return;
//     }

//     // Listen for message from popup
//     function gwMessageHandler(e) {
//         if (e.origin !== window.location.origin) return;

//         var data = e.data || {};
//         if (data.type === 'google_login_success') {
//             window.removeEventListener('message', gwMessageHandler);
//             window.location.href = data.redirect || "<?php echo $myaccount_js; ?>";
//         } else if (data.type === 'google_login_error') {
//             window.removeEventListener('message', gwMessageHandler);
//             alert('Google login failed: ' + (data.message || 'Unknown error'));
//         }
//     }

//     window.addEventListener('message', gwMessageHandler, false);
// }



jQuery(document).ready(function ($) {





    jQuery('#ajax-otp-form').on('submit', function (e) {
        e.preventDefault();
        // var $email = jQuery('#otp_phone').val();
        // if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($email)) {
        //     return;
        // }
        var $form = jQuery(this);
        var $btn = $form.find('#otp-submit');
        var $btn1 = $form.find('#otp-submit1');
        $form.find('.status_x').html("");

        //$btn.addClass('loading').prop('disabled', true);

        var otpSent = jQuery('#otp_sent').val();

        // Case: If OTP already sent AND phone has not changed → verify
        if (otpSent === "1" && $form.find('.otp-field').is(':visible') && !$form.data('phone-changed')) {
            $btn1.addClass('loading').prop('disabled', true);

            jQuery.post(phive_spinner.ajaxurl, {
                action: 'ajax_verify_otp',
                otp_code: jQuery('#otp_code').val(),
                phone: jQuery('#otp_phone').val(),
                security_verify: jQuery('#security_verify').val()
            }, function (response) {
                $form.find('.status_x').html(response.data.message);

                if (response.success) {
                    //window.location.href = phive_spinner.redirecturl;
                    //location.reload();
                    var redirectTo = getUrlParameter('redirect_to');

                    var redirectUrl = window.location.origin + window.location.pathname;

                    if (redirectTo) {
                        //redirectUrl += '?redirect_to=' + encodeURIComponent(redirectTo);
                        redirectUrl = redirectTo;
                    } else {
                        redirectUrl = window.location.href;
                    }

                    // Add timestamp to prevent caching
                    var separator = redirectUrl.includes('?') ? '&' : '?';
                    var finalUrl = decodeURIComponent(redirectUrl) + separator + 'ts=' + new Date().getTime();

                    fetch(decodeURIComponent(redirectUrl), { cache: 'reload' }).finally(() => {
                        //window.location.href = decodeURIComponent(redirectUrl);
                        window.location.href = finalUrl;
                    });

                }

                $btn1.removeClass('loading').prop('disabled', false);
            }, 'json');
        }
        // Else → Send OTP (new number OR first time)
        else {

            $btn.addClass('loading').prop('disabled', true);
            jQuery.post(phive_spinner.ajaxurl, {
                action: 'ajax_send_otp',
                phone: jQuery('#otp_phone').val(),
                security: jQuery('#security').val()
            }, function (response) {
                if (response.success) {
                    $form.find('.otp-field').append(
                        $form.find('.status_x').html('<div class="otp-timer"></div>')
                    );
                    startOtpCountdown($form, 180);
                    $form.find('#otp_code').val('');
                    $form.find('.otp-field, .otp-resend').show();
                    //$btn.text('Verify OTP');
                    jQuery('#otp_sent').val("1"); // mark OTP sent
                    $form.data('phone-changed', false); // reset
                } else {
                    $form.find('.status_x').html(response.data.message);
                }
                $btn.removeClass('loading').prop('disabled', false);
            }, 'json');
        }

    });




    // Track phone number changes
    jQuery('#otp_phone').on('input', function () {
        jQuery('#ajax-otp-form').data('phone-changed', true);
        jQuery('#otp_sent').val("0"); // reset OTP flag when number changes
    });



    // Resend OTP
    // Resend OTP
    jQuery(document).on('click', 'a.resend', function (e) {
        e.preventDefault();
        var $link = jQuery(this);
        var $form = jQuery('#ajax-otp-form');

        if ($link.hasClass('disabled')) return;
        $link.addClass('disabled').text('Resending...');

        // 🚀 Always send OTP again
        jQuery.post(phive_spinner.ajaxurl, {
            action: 'ajax_send_otp',
            phone: jQuery('#otp_phone').val(),
            security: jQuery('#security').val()
        }, function (response) {
            if (response.success) {
                $form.find('.status_x').html('<div class="otp-timer"></div>');
                startOtpCountdown($form, 180);
            } else {
                $form.find('.status_x').html(response.data.message);
            }

            $link.text('OTP sent');
            setTimeout(function () {
                $link.removeClass('disabled').text('Resend OTP');
            }, 3000);
        }, 'json');
    });



    jQuery(document).on('click', '.gw-pass-login', function (e) {
        jQuery('.login-buttons.login').fadeOut();
        jQuery('form#password-form').fadeIn();
    });

    jQuery(document).on('click', '.gw-otp-login', function (e) {
        jQuery('.login-buttons.login').fadeOut();
        jQuery('#ajax-otp-form').fadeIn();
    });

    jQuery(document).on('click', '.gw-register', function (e) {
        jQuery('.login-buttons.login').fadeOut();
        jQuery('#ajax-otp-form').fadeOut();
        jQuery('form#password-form').fadeOut();
        jQuery('form.woocommerce-form-register.register').addClass('show');
    });

    jQuery(document).on('click', '.method', function (e) {
        e.preventDefault();
        jQuery('form#password-form').fadeOut();
        jQuery('#ajax-otp-form').fadeOut();
        jQuery('form.woocommerce-form-register.register').removeClass('show');
        jQuery('.login-buttons.login').fadeIn();
    });


});

jQuery(function ($) {
    // target common registration form selectors used by WooCommerce
    var $regForm = $('#register, form.woocommerce-form-register, form.register');

    $regForm.on('submit', function (e) {
        // find the password and confirm fields inside this form
        var $form = $(this);
        var p = $form.find('input[name="password"], input[name="reg_password"], #reg_password').val() || '';
        var p2 = $form.find('input[name="password2"], input[name="reg_password2"], #reg_password2').val() || '';

        // remove old errors
        $form.find('.woocommerce-error').remove();

        if (p !== p2) {
            e.preventDefault();
            var $err = $('<ul class="woocommerce-error" role="alert"><li>Passwords do not match.</li></ul>');
            $form.prepend($err);
            $form.find('input[name="password2"]').focus();
            return false;
        }
    });
});
jQuery(document).ready(function ($) {
    $('body').on('updated_checkout', function () {
        var notices_wrapper = $('div[data-widget_type="woocommerce-notices.default"]');

        if (notices_wrapper.length > 0) {
            $('.e-coupon-box').append(notices_wrapper);
        }
        jQuery('input#coupon_code').val('');
    });
});
jQuery(document).ready(function ($) {
    var notices_wrapper = $('div[data-widget_type="woocommerce-notices.default"]');
    jQuery('html, body').stop();
    if (notices_wrapper.length > 0) {
        $('.woocommerce-case-form').prepend(notices_wrapper);
    }
});
jQuery(document.body).on('checkout_error', function () {
    jQuery('html, body').stop();
});
jQuery(document).ajaxComplete(function () {
    if (jQuery('body').hasClass('woocommerce-checkout') || jQuery('body').hasClass('woocommerce-cart')) {
        jQuery('html, body').stop();
    }
});
// jQuery(document).on('click', '.close-popup', function (e) {
//     e.preventDefault();
//     jQuery(".case-edit-popup").hide();
// });





jQuery(document).ready(function ($) {
    // Send OTP
    jQuery('#send_otp_btn').on('click', function (e) {
        e.preventDefault();
        var phone_email = $('#reg_phone_email').val();
        var first_name = $('#reg_first_name').val();


        // if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(phone_email)) {
        //     validateEmailField(phone_email);
        //     return;
        // }
        jQuery(this).addClass('loading');




        jQuery.post(phive_spinner.ajaxurl, {
            action: 'ajax_send_reg_otp',
            security: phive_spinner.nonce,
            phone_email: phone_email,
            first_name: first_name,
        }, function (response) {
            jQuery('.otp-message').remove();
            if (response.success) {
                jQuery('input#reg_phone_email').after('<div class="otp-message" style="color:green; margin-top:5px;">' + response.data.message + '</div>');
                jQuery('.ott').hide();
                jQuery('.otf').show();
                jQuery('#send_otp_btn').removeClass('loading');
                jQuery('img.back-arrow').show();
                jQuery('#register_form p.status_x').html('<div class="otp-timer"></div>');
                startOtpCountdown(jQuery('#register_form'), 180);
            } else {
                jQuery('input#reg_phone_email').after('<div class="otp-message" style="color:red; margin-top:5px;">' + response.data.message + '</div>');
                jQuery('#send_otp_btn').removeClass('loading');
            }

        });
    });

    jQuery('img.back-arrow').on('click', function (e) {
        jQuery('img.back-arrow').hide();
        jQuery('.ott').show();
        jQuery('.otf').hide();
        jQuery('.otp-message').remove();
        jQuery('#email_mobile_otp').val('');
    });

    jQuery(document).ready(function ($) {

        $('#send_otp_btn').on('click', function (e) {

            var firstName = $('#reg_first_name').val().trim();
            var email_field = $('#reg_phone_email').val().trim();

            // remove old error
            $('span.error').remove();
            $('.first-name-error').remove();
            $('#reg_first_name').css('border-color', '');
            $('.email-error').remove();
            $('#reg_phone_email').css('border-color', '');
            if (firstName === "" || email_field === "") {
                e.preventDefault();
                e.stopImmediatePropagation();

                if (firstName === "") {


                    // red border
                    $('#reg_first_name').css('border-color', '#d4d7e3').focus();

                    // show error message exactly below input field
                    $('<div class="first-name-error" style="color:red; font-size:14px; margin-top:5px;">Enter first Name</div>')
                        .insertAfter('#reg_first_name');


                }
                if (email_field === "") {

                    // red border
                    $('#reg_phone_email').css('border-color', '#d4d7e3').focus();

                    // show error message exactly below input field
                    $('<div class="email-error" style="color:red; font-size:14px; margin-top:5px;">Enter Email</div>')
                        .insertAfter('#reg_phone_email');


                }
                return false;
            }
        });

    });










    // Verify OTP + Register
    $('#register_form').on('submit', function (e) {
        e.preventDefault();
        $('#register_form button[type="submit"]').addClass('loading');
        var data = {
            action: 'ajax_verify_reg_otp',
            security_verify: phive_spinner.nonce_verify,
            otp_code: $('#email_mobile_otp').val(),
            phone_email: $('#reg_phone_email').val(),
            first_name: $('#reg_first_name').val(),
            last_name: $('#reg_last_name').val(),
            username: $('#reg_username').val()
        };

        $.post(phive_spinner.ajaxurl, data, function (response) {

            $('.otp-message').remove(); // remove old msg

            let msg = '<div class="otp-message" style="color:red;margin-top:6px;">'
                + response.data.message +
                '</div>';


            $('.otf').after(msg);

            $('#register_form button[type="submit"]').removeClass('loading');

            // If registration is successful, redirect to the My Account page with all parameters
            if (response.success) {
                // Default redirect URL (in case redirect_to is not available)
                var redirectTo = getUrlParameter('redirect_to');
                var redirectUrl = window.location.origin + window.location.pathname;

                // If redirect_to exists, append it to the redirect URL
                if (redirectTo) {
                    //redirectUrl += '?redirect_to=' + encodeURIComponent(redirectTo);
                    redirectUrl = redirectTo;
                } else {
                    redirectUrl = phive_spinner.redirecturl;
                }

                console.log(redirectUrl)
                // Redirect user to the My Account page
                window.location.href = decodeURIComponent(redirectUrl);
            }

        });


    });

});

jQuery(function ($) {
    function showPopup(message, onOk) {
        $('#slot-warning-message').text(message);
        $('#slot-warning-popup').fadeIn();

        $('#slot-warning-ok').off('click').on('click', function () {
            $('#slot-warning-popup').fadeOut();
            if (typeof onOk === 'function') onOk();
        });

        $('#slot-warning-cancel').off('click').on('click', function () {
            $('#slot-warning-popup').fadeOut();
        });
    }
    $('.restore-cart').on('click', function (e) {
        e.preventDefault();
        let $btn = $(this);
        let cartName = $btn.data('name');
        let productID = $btn.data('id');
        let fromDate = $btn.data('from');
        let toDate = $btn.data('to');
        var $target = $btn.closest('.saved-cart-item').find('.saved_cart_calander');

        $btn.addClass('loading');

        // --- Past date validation ---
        let from = new Date(fromDate);
        let now = new Date();
        if (from < now) {
            $btn.removeClass('loading');
            showPopup('The selected date or time slot has already passed. Please select another date or time slot.', function () {
                if ($target.length) {
                    $target.click();
                }
            });
            return;
        }

        // --- Slot availability check ---
        $.post(phive_spinner.ajaxurl, {
            action: "phive_check_slot",
            from: fromDate,
            to: toDate,
            id: productID
        }, function (response) {
            if (response.booked) {
                $btn.removeClass('loading');
                showPopup('This slot is already booked. Please select another date or time slot.', function () {
                    if ($target.length) {
                        $target.click();
                    }
                });
            } else {
                // --- Restore the cart ---
                $.post(phive_spinner.ajaxurl, {
                    action: 'restore_cart',
                    cart_name: cartName
                }, function (res) {
                    $btn.removeClass('loading');
                    if (res.success) {
                        jQuery.post(phive_spinner.ajaxurl, {
                            action: 'delete_saved_cart',
                            cart_name: cartName
                        }, function (response) {
                            if (response.success) {
                                window.location.href = '/checkout';
                            }
                        });

                    } else {
                        alert(res.data || 'Failed to restore cart.');
                    }
                });
            }
        });
    });
});


jQuery(document).on('click', '.delete-cart', function (e) {
    e.preventDefault();

    const cartName = jQuery(this).data('cart-name');
    var btn = jQuery(this);
    btn.addClass('loading');

    jQuery('#delete-saved-cart').fadeIn();

    // Remove previous handlers to avoid multiple triggers
    jQuery('#delete-yes').off('click').on('click', function () {
        var yesBtn = jQuery(this);
        yesBtn.addClass('loading');

        jQuery.post(phive_spinner.ajaxurl, {
            action: 'delete_saved_cart',
            cart_name: cartName
        }, function (response) {
            if (response.success) {
                location.reload(); // refresh to update cart list
            } else {
                alert(response.data);
                yesBtn.removeClass('loading');
                btn.removeClass('loading');
            }
        });
    });

    jQuery('#delete-no').off('click').on('click', function () {
        jQuery('#delete-saved-cart').fadeOut();
        btn.removeClass('loading');
    });
});



jQuery(document).on('click', 'body:not(.single-product) .open-addon-popup', function (e) {
    e.preventDefault();

    // Remove any existing modals/popups
    jQuery('#extra-services-popup, #update-booking-modal, #update-member-modal').remove();

    var cart_name = jQuery(this).data('cart-name');
    let btn = jQuery(this); // store clicked button
    let page = '';
    if (window.location.href.includes('product')) {
        page = 'details';
    } else {
        console.log(window.location.href);
    }
    btn.addClass('loading');

    jQuery.ajax({
        url: phive_spinner.ajaxurl,
        type: 'POST',
        data: {
            action: 'load_addon_popup',
            cart_name: cart_name,
            page: page
        },
        success: function (response) {
            if (response.success) {
                jQuery('body').append(response.data);

                // ✅ Use stored btn reference here
                var container = btn.closest('.right-area');
                if (container.length) {
                    var updateBookingHref = container.find('a.update-booking').attr('href');
                    jQuery('#member-yes').attr('href', updateBookingHref);
                }
                updateAddonAllCheckbox();
                jQuery('#extra-services-popup').fadeIn();
                jQuery('body').css('overflow', 'hidden'); // prevent background scroll
                btn.removeClass('loading');
            } else {
                alert(response.data);
                btn.removeClass('loading');
            }
        }
    });
});


jQuery(document).on('click', '.woocommerce-cart #proceed-btn,.woocommerce-cart #confirm-yes', function (e) {
    e.preventDefault();
    var $btn = jQuery(this);
    var cart_name = $btn.data('cart-name');

    var addons = [];
    jQuery('#extra-services-popup .addon-qty').each(function () {
        addons.push({
            id: jQuery(this).data('product_id'),
            qty: parseInt(jQuery(this).val()) || 0
        });
    });

    var remarks = {};
    jQuery('#extra-services-popup textarea[name^="addon_remarks"]').each(function () {
        var key = jQuery(this).attr('name').replace('addon_remarks[', '').replace(']', '');
        remarks[key] = jQuery(this).val();
    });

    jQuery.ajax({
        url: phive_spinner.ajaxurl,
        type: 'POST',
        data: {
            action: 'add_addon_products_to_saved_cart',
            cart_name: cart_name,
            addons: addons,
            remarks: remarks
        },
        success: function (response) {
            if (response.success) {
                location.reload();
                jQuery('#extra-services-popup').fadeOut(function () { jQuery(this).remove(); });
            } else {
                alert(response.data);
            }
        }
    });
});

jQuery(document).on('click', '#addition-button', function (e) {
    e.preventDefault();

    let btn = jQuery(this); // ✅ this is the button itself
    let mem = jQuery('input[name="mm"]').val();
    btn.addClass('loading');

    jQuery.ajax({
        url: phive_spinner.ajaxurl,
        type: 'POST',
        data: {
            action: 'load_addon_order',
            cart_name: 'no_cart', // pass your cart/order info here if needed

        },
        success: function (response) {
            if (response.success) {
                jQuery('body').append(response.data);
                jQuery('span.slider.round:not(.operator)').text(mem);
                jQuery('span.count').text(mem);
                jQuery('input#member-count').val(mem);
                jQuery('#extra-services-popup').fadeIn();
                jQuery('body').css('overflow', 'hidden'); // prevent background scroll
            } else {
                alert(response.data);
            }
            btn.removeClass('loading'); // ✅ always remove loading at end
        },
        error: function () {
            alert('Something went wrong. Please try again.');
            btn.removeClass('loading');
        }
    });
});


jQuery(document).on('click', '.woocommerce-view-order #proceed-btn', function (e) {
    e.preventDefault();
    var $btn = jQuery(this);
    var ord_id = jQuery('th.order-id').text();

    var addons = [];
    jQuery('#extra-services-popup .addon-qty').each(function () {
        addons.push({
            id: jQuery(this).data('product_id'),
            qty: parseInt(jQuery(this).val()) || 0
        });
    });

    var remarks = {};
    jQuery('#extra-services-popup textarea[name^="addon_remarks"]').each(function () {
        var key = jQuery(this).attr('name').replace('addon_remarks[', '').replace(']', '');
        remarks[key] = jQuery(this).val();
    });

    redirectUrl = phive_spinner.checkout;

    jQuery.ajax({
        url: phive_spinner.ajaxurl,
        type: 'POST',
        data: {
            action: 'create_new_order',
            addons: addons,
            remarks: remarks,
            ord_id: ord_id
        },
        success: function (response) {
            if (response.success) {
                //jQuery('#extra-services-popup').fadeOut(function () { jQuery(this).remove(); });
                jQuery('#extra-services-popup table.service-table').hide();
                // jQuery('#extra-services-popup').append(
                //     '<p id="addons-success-msg" style="color:green" class="addons-success">Add-ons order placed successfully.</p>'
                // );
                jQuery('#extra-services-popup .popup-content').append('<p id="addons-success-msg" style="color:green" class="addons-success">Add-ons order placed successfully.</p>');

                setTimeout(() => {
                    location.reload();
                }, 1500);

            } else {
                alert(response.data);
            }
        }
    });
});

jQuery(document).ready(function ($) {
    $(document).on('click', '.order-actions-button.cancel', function (e) {
        e.preventDefault();

        // Get original cancel URL
        let cancelUrl = $(this).attr('href');
        const url = new URL(cancelUrl);
        const orderId = url.searchParams.get('order_id');

        if (orderId) {
            // Build the order details page URL
            const orderDetailsUrl = `/my-account/view-order/${orderId}/`;
            // Replace the redirect param
            url.searchParams.set('redirect', orderDetailsUrl);
            cancelUrl = url.toString();
        }

        const bookedFromTs = parseInt($(this).data('booked-from')); // timestamp (seconds)
        if (!bookedFromTs) {
            alert('Unable to determine booking start time.');
            return;
        }

        const bookingTime = new Date(bookedFromTs * 1000);
        const now = new Date();
        const diffHours = (bookingTime - now) / (1000 * 60 * 60);

        let message = '';
        if (diffHours > 72) {
            message = WCCancelOptions.popup_before_72;
        } else {
            message = WCCancelOptions.popup_within_72;
        }

        $('#cancel-confirmation-popup').remove();

        const popup = `
            <div id="cancel-confirmation-popup" class="cancel-popup-overlay">
                <div class="modal-content"> 
                    <h5>Cancel Room Booking</h5>
                    <p>${message}</p>
                    <div class="popup-buttons">
                        <button id="confirm-cancel" class="button danger">Yes</button>
                        <button id="cancel-close" class="button secondary">No</button>
                    </div>
                </div>
            </div>
        `;

        $('body').append(popup);
        $('#cancel-confirmation-popup').fadeIn();

        // --- Button Actions ---
        $('#confirm-cancel').on('click', function () {
            window.location.href = cancelUrl; // now correctly points to order details page
        });

        $('#cancel-close').on('click', function () {
            $('#cancel-confirmation-popup').fadeOut(function () {
                $(this).remove();
            });
        });
    });
    $(document).on('input change keyup paste', 'textarea[name="wapf[field_68afdc5063a8e]"]', function () {
        var $textarea = $(this);
        var $buttons = $('.ph_book_now_button');

        var val = $textarea.val() || '';
        var isEmpty = val.trim() === '';

        if (isEmpty) {
            //$buttons.addClass('disabled').prop('disabled', true);
        } else {
            $buttons.removeClass('disabled').prop('disabled', false);
        }

        console.log("Textarea Value:", val);
    });

    //$textarea.on('input change keyup paste', toggleButtons);
    //setInterval(toggleButtons, 500);
});

jQuery(document).on('click', '#place_order2', function (e) {
    e.preventDefault();
    e.stopPropagation();
    return false;
});


jQuery(document).on('click', '.elementor-page-2169 #proceed-btn', function (e) {
    e.preventDefault();
    var $btn = jQuery(this);
    $btn.addClass('loading');
    jQuery('#ph_selected_blocks').attr('name', 'ph_selected_blocks');
    var $form = jQuery('form.cart');
    var formData = $form.serialize();

    var addons = [];
    jQuery('#extra-services-popup .addon-qty').each(function () {
        addons.push({
            id: jQuery(this).data('product_id'),
            qty: parseInt(jQuery(this).val()) || 0
        });
    });

    var remarks = {};
    jQuery('#extra-services-popup textarea[name^="addon_remarks"]').each(function () {
        var key = jQuery(this).attr('name').replace('addon_remarks[', '').replace(']', '');
        remarks[key] = jQuery(this).val();
    });

    redirectUrl = phive_spinner.rooms;

    jQuery.ajax({
        url: phive_spinner.ajaxurl,
        type: 'POST',
        data: {
            action: 'create_new_cart',
            addons: addons,
            remarks: remarks,
            formdata: formData
        },
        success: function (response) {
            if (response.success) {
                // Create a popup div
                var $popup = jQuery('<div id="success-popup" style="position: fixed;top: 50vh;right: 50vw;padding:15px;background:#4CAF50;color:#fff;border-radius:5px;z-index:9999;transform: translate(50%, 50%);">' + response.data.message + '</div>');
                jQuery('body').append($popup);

                // Fade out after 2 seconds
                setTimeout(function () {
                    window.location.href = window.location.href;
                }, 1000);

                // Close your extra services popup
                jQuery('#extra-services-popup').fadeOut(function () { jQuery(this).remove(); });

            } else {
                var $popup = jQuery('<div id="error-popup" style="position: fixed;top: 50vh;right: 50vw;padding:15px;background:#fff;color:red;border:1px solid red;border-radius:5px;z-index:9999;transform: translate(50%, 50%);">' + response.data.message + '</div>');
                jQuery('body').append($popup);

                // Fade out after 2 seconds
                setTimeout(function () {
                    jQuery('#error-popup').fadeOut(function () { jQuery(this).remove(); });
                    jQuery('.single_add_to_cart_button').removeClass('disabled');
                }, 3000);

                // Close your extra services popup
                jQuery('#extra-services-popup').fadeOut(function () { jQuery(this).remove(); });
                jQuery('body').css('overflow', 'auto');
            }
        }
    });
});

// For add to cart ajax run again after logged in 
jQuery(document).ready(function () {

    const url = new URL(window.location.href);
    if (url.searchParams.get("retry_ajax") == "1") {

        let cart_name = url.searchParams.get("cart_name");
        let page = url.searchParams.get("page");
        let participant = url.searchParams.get("participant");

        // ✔ Run the same AJAX again automatically
        jQuery.ajax({
            url: phive_spinner.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_addon_popup2',
                cart_name: cart_name,
                page: page,
                participant: participant
            },
            success: function (response) {
                window.history.replaceState({}, document.title, window.location.pathname);
                if (response.success) {
                    jQuery('body').append(response.data);

                    var updateBookingHref = jQuery('.right-area').find('a.update-booking').attr('href');
                    jQuery('#member-yes').attr('href', updateBookingHref);

                    updateAddonAllCheckbox();
                    jQuery('.single-product #btnss #close-popup').text('Proceed without Add-ons');
                    jQuery('#extra-services-popup').fadeIn();
                    jQuery('body').css('overflow', 'hidden');
                } else {
                    alert(response.data);
                    //btn.removeClass('loading');
                }
            }
        });
    }

});


jQuery(document).on('click', '.single-product .open-addon-popup', function (e) {
    e.preventDefault();
    window.history.replaceState({}, document.title, window.location.pathname);
    // Remove any existing modals/popups
    jQuery('#extra-services-popup, #update-booking-modal, #update-member-modal').remove();

    var cart_name = jQuery(this).data('cart-name');
    let btn = jQuery(this); // store clicked button
    var participant = jQuery('input[data-name="Total Participants"]').val();
    let page = '';
    if (window.location.href.includes('product')) {
        page = 'details';
    } else {
        console.log(window.location.href);
    }
    btn.addClass('loading');

    jQuery.ajax({
        url: phive_spinner.ajaxurl,
        type: 'POST',
        data: {
            action: 'load_addon_popup2',
            cart_name: cart_name,
            page: page,
            participant: participant
        },
        success: function (response) {
            if (response.success) {
                jQuery('body').append(response.data);

                // ✅ Use stored btn reference here
                var container = btn.closest('.right-area');
                if (container.length) {
                    var updateBookingHref = container.find('a.update-booking').attr('href');
                    jQuery('#member-yes').attr('href', updateBookingHref);
                }
                updateAddonAllCheckbox();
                jQuery('.single-product #btnss #close-popup').text('Proceed without Add-ons');
                jQuery('#extra-services-popup').fadeIn();
                jQuery('body').css('overflow', 'hidden'); // prevent background scroll
                btn.removeClass('loading');
            } else {
                if (response.data === "Login required.") {
                    var redirectURL = window.location.origin + window.location.pathname;
                    console.log(redirectURL);
                    let selectedDate = null;
                    let selectedSlots = [];

                    // 1️⃣ Get selected date (YYYY-MM-DD)
                    let dateInput = jQuery("li.ph-calendar-date.timepicker-selected-date input.callender-full-date");
                    if (dateInput.length) {
                        selectedDate = dateInput.val(); // e.g. "2025-09-26"
                    }

                    // 2️⃣ Get selected slots (HH:MM from value OR full text from span)
                    jQuery("li.ph-calendar-date.selected-date input.callender-full-date").each(function () {
                        let val = jQuery(this).val().trim(); // e.g. "2025-09-26 09:00"
                        let timePart = val.split(" ")[1]; // just HH:MM

                        // fallback to readable text (09:00 am - 1:00 pm)
                        let readable = jQuery(this).siblings(".ph_calendar_time").text().trim();

                        if (timePart) {
                            selectedSlots.push(timePart);
                        } else if (readable) {
                            selectedSlots.push(readable);
                        }
                    });

                    // 3️⃣ Validate
                    if (!selectedDate) {
                        //alert("Please select a date first.");
                        //return;
                    }

                    // 4️⃣ Build redirect URL
                    //let params = new URLSearchParams();
                    //params.set("date", selectedDate);
                    var slots = selectedSlots;
                    if (selectedSlots.length) {
                        //params.set("slot", selectedSlots.join(","));
                        slots = selectedSlots.join(",");
                    }

                    var params = new URLSearchParams({
                        cart_name: cart_name,
                        page: page,
                        participant: participant,
                        date: selectedDate,
                        slot: slots,
                        retry_ajax: 1 // flag to auto run AJAX after login
                    });

                    window.location.href = phive_spinner.home_link + '/my-account/' + '?redirect_to=' + encodeURIComponent(redirectURL + "?" + params.toString());
                    return;
                } else {
                    alert(response.data);
                    btn.removeClass('loading');
                }

            }
        }
    });
});

jQuery(document).on('click', '.single-product #close-popup', function (e) {
    e.preventDefault();
    var $btn = jQuery(this);
    var $form = jQuery('form.cart');
    jQuery('#ph_selected_blocks').attr('name', 'ph_selected_blocks');
    var formData = $form.serialize();
    $btn.addClass('loading');

    var addons = [];
    // jQuery('#extra-services-popup .addon-qty').each(function () {
    //     addons.push({
    //         id: jQuery(this).data('product_id'),
    //         qty: parseInt(jQuery(this).val()) || 0
    //     });
    // });

    var remarks = {};
    // jQuery('#extra-services-popup textarea[name^="addon_remarks"]').each(function () {
    //     var key = jQuery(this).attr('name').replace('addon_remarks[', '').replace(']', '');
    //     remarks[key] = jQuery(this).val();
    // });

    redirectUrl = phive_spinner.rooms;

    jQuery.ajax({
        url: phive_spinner.ajaxurl,
        type: 'POST',
        data: {
            action: 'create_new_cart',
            addons: addons,
            remarks: remarks,
            formdata: formData
        },
        success: function (response) {
            if (response.success) {
                // Create a popup div
                var $popup = jQuery('<div id="success-popup" style="position: fixed;top: 50vh;right: 50vw;padding:15px;background:#4CAF50;color:#fff;border-radius:5px;z-index:9999;transform: translate(50%, 50%);">' + response.data.message + '</div>');
                jQuery('body').append($popup);

                // Fade out after 2 seconds
                setTimeout(function () {
                    //window.location.href = window.location.href;

                    window.location.href = window.location.href;
                }, 1000);

                // Close your extra services popup
                jQuery('#extra-services-popup').fadeOut(function () { jQuery(this).remove(); });
                jQuery('body').css('overflow', 'auto');

            } else {
                var $popup = jQuery('<div id="error-popup" style="position: fixed;top: 50vh;right: 50vw;padding:15px;background:#fff;color:red;border:1px solid red;border-radius:5px;z-index:9999;transform: translate(50%, 50%);">' + response.data.message + '</div>');
                jQuery('body').append($popup);

                // Fade out after 2 seconds
                setTimeout(function () {
                    jQuery('#error-popup').fadeOut(function () { jQuery(this).remove(); });
                    jQuery('.single_add_to_cart_button').removeClass('disabled');
                }, 3000);

                // Close your extra services popup
                jQuery('#extra-services-popup').fadeOut(function () { jQuery(this).remove(); });
                jQuery('body').css('overflow', 'auto');
            }
        }
    });
});

jQuery(document).on('click', '.single-product div#close-popup', function (e) {
    jQuery('#extra-services-popup').fadeOut(function () {
        jQuery('#update-booking-modal').remove();
        jQuery('#update-member-modal').remove();
        jQuery('body').css('overflow', 'auto');
        jQuery(this).remove();
    });

});


jQuery(function ($) {

    function showCustomConfirm(message, onYes, onNo) {
        $('#custom-confirm-popup').remove();
        const popup = `
            <div id="custom-confirm-popup" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:flex; justify-content:center; align-items:center; z-index:999999;">
                <div style="background:#fff; padding:30px; border-radius:8px; max-width:400px; width:90%; text-align:center; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"> 
                    <p style="font-size: 16px; margin-bottom: 25px; color:#333;">${message}</p>
                    <div style="display:flex; justify-content:center; gap:15px;">
                        <button id="custom-confirm-yes" class="button alt" style=" color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Yes</button>
                        <button id="custom-confirm-no" class="button" style="background:#e0e0e0; color:#333; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">No</button>
                    </div>
                </div>
            </div>
        `;
        $('body').append(popup);

        $('#custom-confirm-yes').off('click').on('click', function (e) {
            e.preventDefault();
            $('#custom-confirm-popup').fadeOut(200, function () { $(this).remove(); });
            if (typeof onYes === 'function') onYes();
        });

        $('#custom-confirm-no').off('click').on('click', function (e) {
            e.preventDefault();
            $('#custom-confirm-popup').fadeOut(200, function () { $(this).remove(); });
            if (typeof onNo === 'function') onNo();
        });
    }

    $(document).on('click', '.remove-party', function (e) {
        e.preventDefault();
        let $btn = $(this);
        showCustomConfirm(
            'Do you want to delete this party?',
            function () {
                $btn.closest('.group-member').remove();

                let $input = $('#group_total_payers');
                let currentCount = parseInt($input.val()) || 2;

                if (currentCount <= 2) {
                    // Switching back to full payment automatically clears remaining and hides UI
                    $('input[name="group_payment_mode"][value="full"]').prop('checked', true).trigger('change');
                } else {
                    let newCount = currentCount - 1;
                    $input.val(newCount);
                    $input.data('prev-val', newCount);

                    // Re-index remaining boxes to keep Party 2, Party 3 contiguous
                    $('#group-members .group-member').each(function (index) {
                        let newIndex = index + 2;
                        // Update header text
                        $(this).find('.remove-box span').first().html(`Party ${newIndex} <span class="required" aria-hidden="true">*</span>`);
                        // Update input names
                        $(this).find('input.group-member-name').attr('name', `group_member_name[${newIndex}]`);
                        $(this).find('input[placeholder="Company Name"]').attr('name', `group_member_company[${newIndex}]`);
                        $(this).find('input[placeholder="Whatsapp Number*"]').attr('name', `group_member_phone[${newIndex}]`);
                        $(this).find('input[placeholder="Email*"]').attr('name', `group_member_email[${newIndex}]`);
                    });

                    if (typeof updateSharedAmount === 'function') updateSharedAmount();
                    if (typeof totalValue === 'function') totalValue();
                    if (typeof saveCheckoutDraftData === 'function') saveCheckoutDraftData();
                }
            },
            function () {
                // Do nothing if "No" is clicked
            }
        );
    });

    function renderMembers(count) {
        let currentBoxes = $('#group-members .group-member').length;
        let targetBoxes = count - 1; // Since counting starts at Party 2

        // Restore from WooCommerce Session Draft if DOM is empty on first load
        let draftParties = {};
        if (currentBoxes === 0 && typeof window.phive_checkout_draft !== 'undefined' && window.phive_checkout_draft.parties) {
            draftParties = window.phive_checkout_draft.parties;
        }

        // 1. ADD: Append new boxes to the bottom if count increased
        if (targetBoxes > currentBoxes) {
            for (let i = currentBoxes + 2; i <= count; i++) {
                let pData = draftParties[i] || {};
                let html = `<div class="group-member" style="margin-bottom:12px;display: inline-block;">
                    <div class="remove-box" style="display:flex; justify-content:space-between;">
                        <span>Party ${i} </span><a class="remove-party" href="" style="color:red; font-size:12px; text-decoration:underline;">Remove Party</a>
                    </div>
                    <p class="form-row form-row-first update_totals_on_changes thwcfd-required thwcfd-field-wrapper thwcfd-field-country validate-required" ><input type="text" name="group_member_name[${i}]" class="input-text group-member-name" placeholder="Name*"  value="${pData.name || ''}" aria-required="true"></p>

                    <p class="form-row form-row-last update_totals_on_changes thwcfd-required thwcfd-field-wrapper thwcfd-field-country " ><input type="text" name="group_member_company[${i}]" class="input-text" placeholder="Company Name" value="${pData.company || ''}"></p>

                    <p class="form-row form-row-first update_totals_on_changes thwcfd-required thwcfd-field-wrapper thwcfd-field-country validate-required" ><input type="text" name="group_member_phone[${i}]" class="input-text" placeholder="Whatsapp Number*"  value="${pData.phone || ''}" aria-required="true"></p>

                    <p class="form-row form-row-last update_totals_on_changes thwcfd-required thwcfd-field-wrapper thwcfd-field-country validate-required" ><input type="email" name="group_member_email[${i}]" class="input-text" placeholder="Email*"  value="${pData.email || ''}" aria-required="true"></p>
                </div>`;
                $('#group-members').append(html);
            }
        }
        // 2. REMOVE: Delete boxes from the bottom if count decreased
        else if (targetBoxes < currentBoxes) {
            let diff = currentBoxes - targetBoxes;
            for (let i = 0; i < diff; i++) {
                $('#group-members .group-member').last().remove();
            }
        }
    }

    function initGroupPaymentUI() {
        // Restore radio selection from localStorage
        let savedMode = localStorage.getItem('group_payment_mode');
        let savedCount = $('#group_total_payers').val();

        // NEW: Check window.phive_checkout_draft to restore from session if available
        if (typeof window.phive_checkout_draft !== 'undefined') {
            if (window.phive_checkout_draft.mode) {
                savedMode = window.phive_checkout_draft.mode;
            }
            if (window.phive_checkout_draft.count) {
                savedCount = window.phive_checkout_draft.count;
                $('#group_total_payers').val(savedCount);
            }
        }

        if (savedMode) {
            $('input[name="group_payment_mode"][value="' + savedMode + '"]').prop('checked', true);
            //$('input[name="group_payment_mode"][value="full"]').prop('checked', true);
        }

        // Show/hide fields based on selection
        if ($('input[name="group_payment_mode"]:checked').val() === 'group') {
            $('#group-payment-fields').show();
            $('.count-member').show();
            $('.consent-line').show();
            renderMembers(savedCount);
        } else {
            $('#group-payment-fields').hide();
            $('.count-member').hide();
            $('.consent-line').hide();
            $('#group-members').empty();
        }

        // Handle radio change
        $(document).off('change', 'input[name="group_payment_mode"]').on('change', 'input[name="group_payment_mode"]', function () {
            let newMode = $(this).val();

            function applyMode(mode) {
                totalValue();
                localStorage.setItem('group_payment_mode', mode);
                if (mode === 'group') {
                    $('#group-payment-fields').slideDown(200);
                    $('.count-member').show();
                    $('.consent-line').show();
                    let cnt = parseInt($('#group_total_payers').val()) || 2;
                    if ($('#group-members .group-member').length === 0) {
                        renderMembers(cnt);
                    }
                } else {
                    $('#group-payment-fields').slideUp(200);
                    $('.count-member').hide();
                    $('.consent-line').hide();
                    $('#group-members').empty();
                }
                updateSharedAmount();
                if (typeof saveCheckoutDraftData === 'function') saveCheckoutDraftData();
            }

            if (newMode !== 'group') {
                let hasData = false;
                $('#group-members input').each(function () {
                    if ($(this).val().trim() !== '') hasData = true;
                });

                if (hasData) {
                    showCustomConfirm(
                        'Are you sure you want to change the payment type? All entered party details will be erased.',
                        function () { applyMode(newMode); },
                        function () { $('input[name="group_payment_mode"][value="group"]').prop('checked', true); }
                    );
                    return;
                }
            }
            applyMode(newMode);
        });

        // Handle total payers change
        $(document).on('focusin', '#group_total_payers', function () {
            $(this).data('prev-val', $(this).val());
        });

        $(document).off('change', '#group_total_payers').on('change', '#group_total_payers', function () {
            let $input = $(this);
            let newCount = parseInt($input.val()) || 2;
            let prevCount = parseInt($input.data('prev-val')) || 2;

            function applyCount(count) {
                $input.data('prev-val', count);
                updateSharedAmount();
                totalValue();
                if (count >= 2 && count <= 15) {
                    renderMembers(count);
                }
                if (typeof saveCheckoutDraftData === 'function') saveCheckoutDraftData();
            }

            if (newCount < prevCount) {
                showCustomConfirm(
                    'Do you want to delete the last entry?',
                    function () { applyCount(newCount); },
                    function () { $input.val(prevCount); } // Revert input if No
                );
            } else if (newCount > prevCount) {
                applyCount(newCount);
            }
        });

        jQuery('input[type="email"]').each(function () {
            validateEmailField(this);
        });
    }

    function updateSharedAmount() {
        let totalText = $('.order-total .woocommerce-Price-amount bdi').text().replace(/[^\d.]/g, '');
        let org = $('.tax-rate:first td.gst').data('org');
        let totalTex = $('.tax-rate:first td.gst').data('org');

        let total = parseFloat(totalText);
        let totalT = parseFloat(totalTex);
        let payers = parseInt($('#group_total_payers').val());

        if (isNaN(total) || isNaN(payers) || payers <= 0) {
            $('.shared-amount').text('');
            return;
        }

        let percentage = (100 / payers).toFixed(0);
        $('.split-share-info').html(`1 of ${payers} <span style="vertical-align: text-bottom;font-weight: bold;">.</span> ${percentage}%`);

        //console.log(percentage);

        if (isNaN(totalT) || isNaN(payers) || payers <= 0 || $('input[type="radio"][value="full"]').is(':checked')) {
            $('.tax-rate td.gst').each(function () {
                $(this).html(`+₹${org}`);
            });
            return;
        }
        // Calculate per-payer share
        let share = total / payers;
        let tax = totalT / payers;

        // Format as currency
        let formattedShare = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
        }).format(share);
        $('.shared-amount').html(`${formattedShare}`);

        let formattedTax = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
        }).format(tax);

        $('.tax-rate td.gst').each(function () {
            $(this).html(`+${formattedTax}`);
        });
    }

    // Init on page load
    initGroupPaymentUI();

    // Re-init after checkout AJAX update
    $(document.body).on('updated_checkout', function () {
        initGroupPaymentUI();
        updateSharedAmount();
    });

    updateSharedAmount();
    const minput = document.getElementById('group_total_payers');
    if (minput) {
        minput.addEventListener('input', function () {
            const min = parseInt(this.min, 10);
            const max = parseInt(this.max, 10);
            let value = parseInt(this.value, 10);

            if (isNaN(value)) {
                this.value = min; // reset if not a number
                return;
            }

            if (value < min) this.value = min;
            if (value > max) this.value = max;
        });
    }


    function totalValue() {
        const radios = document.querySelectorAll('input[name="group_payment_mode"]');
        let totalText = $('.shared-amount-final').data('org');
        let total = parseFloat(totalText);
        let payers = parseInt($('#group_total_payers').val());

        if (isNaN(total) || isNaN(payers) || payers <= 0) {
            //$('.shared-amount-final').text('');
            return;
        }

        // Calculate per-payer share
        let share = total / payers;

        // Format as currency
        let formattedShare = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
        }).format(share);

        let formattedTotal = new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
        }).format(total);

        //$('.shared-amount-final').html(`${formattedShare}`);
        function getSelectedPaymentMode() {
            for (const radio of radios) {
                if (radio.checked) {
                    return radio.value;
                }
            }
            return null; // none selected (should not happen if one is checked by default)
        }
        if (getSelectedPaymentMode() == 'full') {
            //$('.shared-amount-final').html(`${formattedTotal}`);
            $('.shared-total-final th.upper').html(`Your Total Payable`);
            $('#place_order').html(`Pay Now`);
            $('.final-price').html(`${formattedTotal}`);
        } else {
            //$('.shared-amount-final').html(`${formattedShare}`);
            $('.shared-total-final th.upper').html(`Your Total Payable`);
            $('#place_order').html(`Pay Now`);
            $('.final-price').html(`${formattedShare}`);
        }
    }
    totalValue();
    $(document.body).on('updated_checkout', function () {
        totalValue();
    });

    // --- NEW: Realtime Checkout Data Saver ---
    let checkoutDraftTimer;
    function saveCheckoutDraftData() {
        clearTimeout(checkoutDraftTimer);
        checkoutDraftTimer = setTimeout(function () {
            let mode = $('input[name="group_payment_mode"]:checked').val();
            let count = parseInt($('#group_total_payers').val()) || 2;
            let parties = {};

            if (mode === 'group') {
                for (let i = 2; i <= count; i++) {
                    parties[i] = {
                        name: $(`input[name="group_member_name[${i}]"]`).val() || '',
                        company: $(`input[name="group_member_company[${i}]"]`).val() || '',
                        phone: $(`input[name="group_member_phone[${i}]"]`).val() || '',
                        email: $(`input[name="group_member_email[${i}]"]`).val() || ''
                    };
                }
            }

            $.ajax({
                url: (typeof phive_spinner !== 'undefined' ? phive_spinner.ajaxurl : '/wp-admin/admin-ajax.php'),
                type: 'POST',
                data: {
                    action: 'phive_save_checkout_split_draft',
                    mode: mode,
                    count: count,
                    parties: parties
                }
            });
        }, 1000); // 1-second debounce
    }

    // Trigger save on any input change inside group options or member fields
    $(document).on('input change', 'input[name="group_payment_mode"], #group_total_payers, #group-members input', function () {
        saveCheckoutDraftData();
    });

});


document.addEventListener("DOMContentLoaded", function () {
    const otp = document.getElementById("otp_code");
    if (!otp) return;

    otp.addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "");
    });
});

(function () {
    document.addEventListener("DOMContentLoaded", function () {
        const el = document.getElementById("email_mobile_otp");
        if (!el) return;

        el.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, "");
        });
    });
})();



jQuery(function ($) {

    // Run only on page-id-1392
    if (!$('body').hasClass('page-id-1392')) {
        return; // stop code on all other pages
    }

    // selector: adapt if your carousel element uses a different class
    var $owl = $('.login-img-slider.owl-carousel');
    if (!$owl.length) {
        // fallback: maybe class order is different
        $owl = $('.login-img-slider');
    }

    // If Owl was already initialized, destroy it first to avoid conflicts
    if ($owl.length && $owl.hasClass('owl-loaded')) {
        try {
            $owl.trigger('destroy.owl.carousel');
            $owl.removeClass('owl-loaded');
            $owl.find('.owl-stage-outer').children().unwrap(); // optional cleanup
        } catch (e) {
            // ignore if not initialized properly
        }
    }

    // Initialize
    $owl.owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 4000,   // change speed (ms)
        autoplayHoverPause: false,
        smartSpeed: 700,
        nav: false,
        dots: true,
        margin: 20,
        responsive: {
            0: { items: 1 },
            768: { items: 1 },
            1200: { items: 1 }
        }
    });

});





jQuery(document).ready(function () {
    setTimeout(function () {
        jQuery(".lgn-slider-sec-top").addClass('xint'); // show smoothly
    }, 500); // 2000ms = 2 seconds
});








function validateEmailField(input) {
    if (!input) return;

    let isValid = true;

    if (input.value !== "") {
        const strictPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!strictPattern.test(input.value)) {
            isValid = false;
        }
    } else {
        if (input.ariaRequired === "true" || input.hasAttribute("required")) {
            input.insertAdjacentHTML(
                'afterend',
                '<span class="error" style="color:red;display:block;" role="alert">This field is required.</span>'
            );
        }

    }

    const nextSibling = input.nextElementSibling;
    const isErrorSpan = nextSibling && nextSibling.classList.contains('error');

    if (!isValid) {
        if (!isErrorSpan) {
            jQuery('.otp-error').remove();
            jQuery('.email-error').remove();
            input.insertAdjacentHTML(
                'afterend',
                '<span class="error" style="color:red;display:block;" role="alert">Invalid Email.</span>'
            );
        }
    } else {
        if (isErrorSpan) {
            nextSibling.remove();
        }
    }
}

function uniqueCheck(){
            // --- NEW: Uniqueness Check ---
        let emails = [];
        let phones = [];

        let primaryEmail = jQuery('#billing_email').val();
        if (primaryEmail) emails.push(primaryEmail.trim().toLowerCase());

        let primaryPhone = jQuery('#billing_phone').val();
        if (!primaryPhone) primaryPhone = jQuery('#billing_mobile').val();
        if (primaryPhone) phones.push(primaryPhone.trim());

        jQuery('input[name^="group_member_email"]').each(function () {
            let val = jQuery(this).val().trim().toLowerCase();
            if (val) {
                if (emails.includes(val)) {
                    error2 = true;
                    jQuery(this).addClass('woocommerce-invalid').removeClass('woocommerce-validated');
                    jQuery(this).next('.error').remove();
                    jQuery(this).after('<span class="error" style="color:red;display:block;position: absolute;" role="alert">Email must be unique.</span>');
                } else {
                    emails.push(val);
                }
            }
        });

        jQuery('input[name^="group_member_phone"]').each(function () {
            let val = jQuery(this).val().trim();
            if (val) {
                if (phones.includes(val)) {
                    error2 = true;
                    jQuery(this).addClass('woocommerce-invalid').removeClass('woocommerce-validated');
                    jQuery(this).next('.error').remove();
                    jQuery(this).after('<span class="error" style="color:red;display:block;position: absolute;" role="alert">Phone must be unique.</span>');
                } else {
                    phones.push(val);
                }
            }
        });
        // --- END NEW ---
}

document.addEventListener('focusout', function (e) {
    if (e.target && e.target.matches('input[type="email"]')) {
        validateEmailField(e.target);
    }
    uniqueCheck();
});
jQuery(document.body).on('updated_checkout', function () {
    jQuery('input[type="email"]').each(function () {
        validateEmailField(this);
    });
});
document.addEventListener('input change keyup blur', function (e) {
    if (e.target && e.target.matches('input[type="email"]')) {
        const input = e.target;
        const nextSibling = input.nextElementSibling;
        if (nextSibling && nextSibling.classList.contains('error')) {
            nextSibling.remove();
        }
    }
});



document.addEventListener('input', function (e) {
    const input = e.target;

    if (input.id === 'billing_postcode') {

        // Allow ONLY numbers
        input.value = input.value.replace(/\D/g, '');

        // Limit to 6 digits max
        if (input.value.length > 6) {
            input.value = input.value.slice(0, 6);
        }
    }
    if (
        input.id === 'billing_first_name' ||
        input.id === 'billing_last_name' ||
        input.id === 'billing_city' ||
        input.id === 'account_first_name' ||
        input.id === 'account_last_name' ||
        input.id === 'reg_first_name' ||
        input.classList.contains('group-member-name')
    ) {
        // Remove ALL digits 0-9
        input.value = input.value.replace(/[0-9]/g, '');
    }

    if (input.id === 'billing_phone' || input.id === 'billing_mobile') {
        input.value = input.value.replace(/\D/g, ''); // keep only digits
        if (input.value.length > 10) {
            input.value = input.value.slice(0, 10);
        }
    }

    if (input.matches(`input[placeholder="Participant's Name"]`) || input.matches(`input[placeholder="Party Name"]`) || input.matches(`input[placeholder="Arbitrator's Name"]`)) {
        input.value = input.value.replace(/[0-9]/g, '');
    }


    // WhatsApp number (placeholder match)
    if (input.matches('input[placeholder="Whatsapp Number*"]')) {
        input.value = input.value.replace(/\D/g, ''); // keep only digits
        if (input.value.length > 10) {
            input.value = input.value.slice(0, 10);
        }
    }
    const inputtm = document.querySelector('#member-count');

    if (inputtm) {
        // Prevent negative values on input (typing, paste, scroll)
        inputtm.addEventListener('input', () => {
            if (Number(inputtm.value) < 0) {
                inputtm.value = 0;
            }
        });

        // Prevent typing "-" key
        inputtm.addEventListener('keydown', (e) => {
            if (e.key === '-' || e.key === 'Minus') {
                e.preventDefault();
            }
        });

        // Optional: prevent mouse wheel changing to negative
        inputtm.addEventListener('wheel', (e) => {
            if (inputtm.value <= 0 && e.deltaY > 0) {
                e.preventDefault();
            }
        });
    }

});



// Function to get URL parameters by name
function getUrlParameter(name) {
    var url = new URL(window.location.href);
    return url.searchParams.get(name);
}

// Check if the URL contains "redirect_to" parameter
var redirectTo = getUrlParameter('redirect_to');
if (redirectTo) {
    // Add the 'redirect_to' parameter to '.to-register' link
    var registerLink = document.querySelector('.to-register');
    if (registerLink) {
        registerLink.href = registerLink.href + (registerLink.href.includes('?') ? '&' : '?') + 'redirect_to=' + encodeURIComponent(redirectTo);
    }

    // Add the 'redirect_to' parameter to '.to-register' link
    var loginLink = document.querySelector('.to-login');
    if (loginLink) {
        loginLink.href = loginLink.href + (loginLink.href.includes('?') ? '&' : '?') + 'redirect_to=' + encodeURIComponent(redirectTo);
    }

    // Add the 'redirect_to' parameter to '.header-account .elementor-button-link'
    var accountLink = document.querySelector('.header-account .elementor-button-link');
    if (accountLink) {
        accountLink.href = accountLink.href + (accountLink.href.includes('?') ? '&' : '?') + 'redirect_to=' + encodeURIComponent(redirectTo);
    }

    // Add the 'redirect_to' parameter to '.login-link'
    var mobLoginLink = document.querySelector('.login-link a');
    if (mobLoginLink) {
        mobLoginLink.href = mobLoginLink.href + (mobLoginLink.href.includes('?') ? '&' : '?') + 'redirect_to=' + encodeURIComponent(redirectTo);
    }

}

jQuery(document).ready(function ($) {

    var $deleteButton = $('#delete-all-expired-carts');
    var $modal = $('#delete-all-expired-popup');
    var $yesButton = $('#expired-delete-yes');
    var $noButton = $('#expired-delete-no');

    // 1. Show the custom confirmation modal on button click
    $deleteButton.on('click', function (e) {
        e.preventDefault();

        $modal.fadeIn(300).css('display', 'flex'); // Use flex to center the content
    });

    // 2. Handle the 'No' (Cancel) action
    $noButton.on('click', function (e) {
        e.preventDefault();
        $modal.fadeOut(300);
    });

    // 3. Handle the 'Yes' (Confirm) action - RUN THE AJAX
    $yesButton.on('click', function (e) {
        e.preventDefault();

        // Hide the modal immediately
        //$modal.fadeOut(300);

        var originalText = $deleteButton.text();

        // Disable button and show loading text
        $deleteButton.text('Deleting...').prop('disabled', true).addClass('loading');

        // Prepare data for the AJAX request
        var data = {
            action: 'phive_delete_all_expired_carts'     // The security token
        };

        $.post(wc_add_to_cart_params.ajax_url, data, function (response) {

            // Re-enable button
            $deleteButton.text(originalText).prop('disabled', false).removeClass('loading');

            if (response.success) {
                //alert(response.data.message);

                // Reload the page to show the updated list
                location.reload();
            } else {
                alert('Error: ' + response.data.message);
            }
        }).fail(function () {
            // Handle server or network failure
            alert('An unexpected error occurred. Please try again.');
            $deleteButton.text(originalText).prop('disabled', false).removeClass('loading');
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const loginLinks = document.querySelectorAll("li.login-link.menu-item a");
    if (!loginLinks.length) return;

    const excludedPages = ["login", "register"];

    const path = window.location.pathname.replace(/^\/|\/$/g, "");
    const isExcluded = excludedPages.includes(path);
    const isLoggedIn = document.body.classList.contains("logged-in");

    if (!isExcluded && !isLoggedIn) {
        const currentUrl = window.location.href;

        loginLinks.forEach(link => {
            let url = link.href;

            // If URL already has ?, use & — else use ?
            const connector = url.includes("?") ? "&" : "?";

            link.href = url + connector + "redirect_to=" + encodeURIComponent(currentUrl);
        });
    }
});


document.addEventListener("DOMContentLoaded", function () {
    // Check if we are on checkout page
    if (window.location.pathname.includes('/checkout')) {
        const registerLink = document.querySelector('a.to-register');

        if (registerLink) {
            const redirectUrl = encodeURIComponent(window.location.href);
            const url = new URL(registerLink.href);

            // Add / overwrite redirect_to param
            url.searchParams.set('redirect_to', redirectUrl);

            registerLink.href = url.toString();
        }
    }
});

document.addEventListener("DOMContentLoaded", function () {
    // Get the cancel button and the popup element
    const cancelBtn = document.querySelector('a.cancel_btn_child');
    const popup = document.getElementById('cancel-confirmation-popup');
    const cancelCloseBtn = document.getElementById('cancel-close');
    if (cancelBtn) {
        // Show the popup when the cancel button is clicked
        cancelBtn.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default anchor link behavior
            popup.style.display = 'block'; // Show the popup
        });

        // Close the popup when the "No" button is clicked
        cancelCloseBtn.addEventListener('click', function () {
            popup.style.display = 'none'; // Hide the popup
        });
    }
    jQuery(document).ajaxComplete(function () {
        jQuery('p#booking_price_text b').text('Total Room Fee:');
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const fieldName = 'wapf[field_68afdc5063a8e]';
    const inputField = document.querySelector(`textarea[name="${fieldName}"]`);
    if (!inputField) {
        //console.log('Textarea not found');
        return;
    }
    const form = inputField.closest('form');

    // 1. Create the error message element
    const errorDisplay = document.createElement('span');
    errorDisplay.className = 'error';
    errorDisplay.innerHTML = 'This field is required';
    errorDisplay.style.cssText = 'color:red; display:block; position: relative; margin-top: 2px; display:none;';

    inputField.parentNode.appendChild(errorDisplay);
    form.setAttribute('novalidate', true);

    // 2. Handle Form Submission
    form.addEventListener('submit', function (e) {
        if (!inputField.value.trim()) {
            e.preventDefault();

            errorDisplay.style.display = 'block';
            // Apply both border and outline
            inputField.style.borderColor = '#d9534f';
            inputField.style.outline = '1px solid red';

            // Smooth Scroll to field
            inputField.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Focus the field without the browser's default jump
            setTimeout(() => {
                inputField.focus({ preventScroll: true });
            }, 500);
        }
    });

    // 3. Hide error and reset styles while user is typing
    inputField.addEventListener('input', function () {
        if (inputField.value.trim().length > 0) {
            errorDisplay.style.display = 'none';
            inputField.style.borderColor = '';
            inputField.style.outline = ''; // Reset outline
        }
    });
});

jQuery(document).ready(function ($) {
    // Open Modal
    $('#frontend_cancel_booking_trigger').on('click', function (e) {
        e.preventDefault();
        $('#phive_cancel_modal').css('display', 'flex');
    });

    // Close Modal
    $('#phive_modal_close_btn').on('click', function () {
        $('#phive_cancel_modal').hide();
        $('#phive_cancel_reason_input').val('');
    });

    // Confirm Cancellation via AJAX
    $('#phive_modal_confirm_btn').on('click', function () {
        const reason = $('#phive_cancel_reason_input').val();
        const orderId = $('.order-id').text().trim(); // Ensure this matches your HTML ID for Order Number
        const $btn = $(this);

        if (reason == '') {
            $('#phive_cancel_reason_input').after("<span class='error-msg' style='color:red'>Please enter the reason for cancellation.</span>");
            return;
        }

        $btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: my_ajax_obj.ajaxurl, // Uses localized ajaxurl
            type: 'POST',
            data: {
                action: 'frontend_cancel_booking_with_reason',
                order_id: orderId,
                reason: reason,
                security: my_ajax_obj.nonce // You should add a nonce to my_ajax_obj in custom_functions.php
            },
            success: function (response) {
                if (response.success) {
                    $('.phive-modal-footer').after("<span class='success-msg' style='color:green;margin-top: 10px;display: block;text-align: center;'>Booking Cancelled.</span>");
                    setTimeout(() => {
                        location.reload();
                    }, 1500);

                } else {
                    alert(response.data.message || 'Error cancelling booking.');
                    $btn.prop('disabled', false).text('Confirm Cancellation');
                }
            }
        });
    });
});
jQuery(document).ready(function ($) {
    /**
     * Updates all coupon-related buttons based on currently applied coupons.
     */
    function syncCouponButtons() {
        if (window.location.href.includes('/order-pay')) {
            return;
        }
        let appliedCoupons = [];

        // 1. Look for native remove links (Standard Checkout)
        $('.woocommerce-remove-coupon').each(function () {
            let code = $(this).data('coupon');
            if (code) appliedCoupons.push(code.toString().toLowerCase());
        });

        // 2. Look for Coupon Labels in the table (Order-Pay / form-pay.php)
        // This looks for "Coupon: code" text in the table headers/rows
        $('.cart-discount th, .checkout-order-review tr th').each(function () {
            let text = $(this).text().toLowerCase();
            if (text.includes('coupon:')) {
                let code = text.split('coupon:')[1].trim().split(' ')[0];
                if (code) appliedCoupons.push(code);
            }
        });

        // 3. Update all buttons with [data-code]
        $('[data-code]').each(function () {
            let $btn = $(this);
            let btnCode = $btn.data('code').toString().toLowerCase();

            if (appliedCoupons.includes(btnCode)) {
                $btn.text('Remove')
                    .addClass('remove-coupon-style')
                    .addClass('is-applied')
                    .attr('data-action', 'remove');
            } else {
                $btn.text('Apply')
                    .removeClass('remove-coupon-style')
                    .removeClass('is-applied')
                    .attr('data-action', 'apply');
            }
        });
    }

    // Trigger on checkout updates
    $(document.body).on('updated_checkout', function () {
        syncCouponButtons();
    });

    // Run on initial load for both Checkout and form-pay.php
    syncCouponButtons();

    /**
     * Unified Click Handler
     */
    $(document).on('click', 'form.checkout .apply-quick-coupon, form.checkout .coupon-popup-btn', function (e) {
        e.preventDefault();
        let $btn = $(this);
        let code = $btn.data('code');
        let action = $btn.attr('data-action');

        if (action === 'remove') {
            // Find the [Remove] link in the table and click it
            let $removeLink = $(`.woocommerce-remove-coupon[data-coupon="${code}"]`);
            if ($removeLink.length) {
                $removeLink.trigger('click');
            } else {
                // Fallback for form-pay.php: redirect to the remove URL if button click fails
                let removeUrl = $(`.cart-discount.coupon-${code.toLowerCase()} a.woocommerce-remove-coupon`).attr('href');
                if (removeUrl) window.location.href = removeUrl;
            }
        } else {
            // Standard Apply logic
            $('#coupon_code').val(code);
            $('button[name="apply_coupon"]').trigger('click');
        }
    });
    $(document).on('click', '.woocommerce-remove-coupon', function () {
        $('.coupon-notice-wrapper').hide().empty();
        $('.my-custom-coupon-alert').hide().empty();
    });
    $(document).on('input', 'input#coupon_code', function () {
        $('.coupon-notice-wrapper').hide().empty();
        $('.my-custom-coupon-alert').hide().empty();
    });
});
function showError(input, message) {
    var errorSpan = input.nextElementSibling;

    // If the error span doesn't exist yet, generate it
    if (!errorSpan || !errorSpan.classList.contains('error')) {
        errorSpan = document.createElement('span');
        errorSpan.className = 'error';
        errorSpan.style.color = 'red';
        errorSpan.style.display = 'block';
        errorSpan.style.position = 'absolute';
        errorSpan.setAttribute('role', 'alert');
        input.parentNode.insertBefore(errorSpan, input.nextSibling);
    }

    // Update the message
    errorSpan.innerText = message;
}

// Helper function to completely remove the error container
function clearError(input) {
    var errorSpan = input.nextElementSibling;
    if (errorSpan && errorSpan.classList.contains('error')) {
        errorSpan.remove(); // Completely deletes the element from the DOM
    }
}

// 1. Setup the input validation rules when the user clicks in
document.addEventListener('focusin', function (e) {
    if (e.target.tagName === 'INPUT' && e.target.name && e.target.name.startsWith('group_member_phone')) {
        if (!e.target.dataset.initialized) {
            e.target.setAttribute('minlength', '10');
            e.target.setAttribute('pattern', '\\d{10,}');
            e.target.dataset.initialized = 'true';
        }
    }
});

// 2. Generate error when focus is removed and it's invalid
document.addEventListener('focusout', function (e) {
    if (e.target.tagName === 'INPUT' && e.target.name && e.target.name.startsWith('group_member_phone')) {
        if (e.target.value.trim() === '') {
            showError(e.target, 'This field is required.');
        } else if (!e.target.validity.valid) {
            showError(e.target, 'Enter a valid phone no.');
        } else {
            clearError(e.target);
        }
    }
});

// 3. Remove the error container dynamically while they are typing to correct it
document.addEventListener('input', function (e) {
    if (e.target.tagName === 'INPUT' && e.target.name && e.target.name.startsWith('group_member_phone')) {
        e.target.setCustomValidity('');
        clearError(e.target);
    }
});

jQuery(document).ready(function ($) {
    if ($('body').hasClass('single-product')) {
        //console.log("Product page loaded. Running auto-cleanup for freezed posts...");
        $.post(typeof my_ajax_obj !== 'undefined' ? my_ajax_obj.ajaxurl : ajaxurl, {
            action: "ph_delete_freezed_posts"
        }).done(function (response) {
            //console.log("Auto-cleanup finished.");
        }).fail(function () {
            console.log("Failed to clear freezed posts on page load.");
        });
    }
});

// 1. Generic focusout (blur) listener for ALL required fields
document.addEventListener('focusout', function (e) {
    if (e.target && e.target.matches('input[aria-required="true"], select[aria-required="true"], textarea[aria-required="true"], input[required], select[required], textarea[required]')) {

        let val = e.target.value.trim();

        // Check 1: Is it empty?
        if (val === '') {
            showError(e.target, 'This field is required.');
            e.target.classList.add('woocommerce-invalid');
            e.target.classList.remove('woocommerce-validated');
            uniqueCheck();
        }
        // Check 2: Is it a group member phone and less than 10 digits?
        else if (e.target.name && e.target.name.startsWith('group_member_phone') && val.length < 10) {
            showError(e.target, 'Enter a valid phone no.');
            e.target.classList.add('woocommerce-invalid');
            e.target.classList.remove('woocommerce-validated');
            uniqueCheck();
        }
        // Check 3: Is it an email field and has an invalid format?
        else if (e.target.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            showError(e.target, 'Invalid Email.');
            e.target.classList.add('woocommerce-invalid');
            e.target.classList.remove('woocommerce-validated');
            uniqueCheck();
        }
        // Check 4: If all checks pass, mark as valid
        else {
            clearError(e.target);
            e.target.classList.remove('woocommerce-invalid');
            e.target.classList.add('woocommerce-validated');
            uniqueCheck();
        }
    }
});

// 2. Automatically clear the error message as soon as the user starts typing again
document.addEventListener('input', function (e) {
    if (e.target && e.target.matches('input[aria-required="true"], select[aria-required="true"], textarea[aria-required="true"], input[required], select[required], textarea[required]')) {
        clearError(e.target);
        e.target.classList.remove('woocommerce-invalid');
    }
});
jQuery(document).ready(function ($) {
    $(document).on('click', '#add-party', function () {
        let $input = $('#group_total_payers');
        $input.val(parseInt($input.val()) + 1).trigger('change');
    });
});

jQuery(function ($) {
    // Hook into WooCommerce's checkout initialization
    $(document.body).on('init_checkout', function () {
        var $form = $('form.checkout');

        // 1. Unbind WooCommerce's default AJAX triggers for address fields
        $form.off('change', '.address-field select');
        $form.off('change', '.address-field input.input-text, .update_totals_on_change input.input-text');
        $form.off('keydown', '.address-field input.input-text, .update_totals_on_change input.input-text');

        // 2. Create a custom trigger that specifically ignores billing fields
        var custom_trigger = function (e) {
            if ($(this).closest('.woocommerce-billing-fields').length > 0) {
                return; // Halt the update if the field is in the billing section
            }
            $(document.body).trigger('update_checkout');
        };

        // 3. Rebind the 'change' events with our custom trigger
        $form.on('change', '.address-field select', custom_trigger);
        $form.on('change', '.address-field input.input-text, .update_totals_on_change input.input-text', custom_trigger);

        // 4. Rebind the 'keydown' events with the standard 1-second delay for typing
        var updateTimer;
        $form.on('keydown', '.address-field input.input-text, .update_totals_on_change input.input-text', function (e) {
            var code = e.keyCode || e.which || 0;
            if (code === 9) { // Allow standard Tab navigation to work
                return true;
            }
            if ($(this).closest('.woocommerce-billing-fields').length > 0) {
                return; // Halt the update for billing fields
            }

            clearTimeout(updateTimer);
            updateTimer = setTimeout(function () {
                $(document.body).trigger('update_checkout');
            }, 1000);
        });
    });
});


// --- NEW CHECKOUT TIMER LOGIC ---
jQuery(document).ready(function ($) {
    let checkoutTimerInterval = null;
    
    // Initialize from WooCommerce session variables (injected via PHP)
    let currentExpiry = typeof wc_phive_expiry !== 'undefined' && wc_phive_expiry ? wc_phive_expiry : null;
    let currentHash = typeof wc_phive_hash !== 'undefined' && wc_phive_hash ? wc_phive_hash : null;

    // Helper to push updates to WC Session
    function updateServerSession(expiry, hash) {
        currentExpiry = expiry;
        currentHash = hash;
        $.post(typeof phive_spinner !== 'undefined' ? phive_spinner.ajaxurl : (typeof my_ajax_obj !== 'undefined' ? my_ajax_obj.ajaxurl : ajaxurl), {
            action: "ph_update_timer_session",
            expiry: expiry,
            hash: hash
        });
    }

    function startCheckoutTimer() {
        if ($('#checkout-timer').length === 0) return;
        if (checkoutTimerInterval) return; // Already running

        let expiryTime = currentExpiry;
        let now = new Date().getTime();

        if (expiryTime === 'expired') {
            return;
        }

        if (expiryTime && (parseInt(expiryTime) - now <= 0)) {
            return;
        }
        // If no expiry time or it's already past (from an old session), start a fresh 10 mins
        if (!expiryTime) {
            expiryTime = now + (10 * 60 * 1000); // 10 mins
            updateServerSession(expiryTime, currentHash);
        }

        checkoutTimerInterval = setInterval(function () {
            let currentTime = new Date().getTime();
            let distance = parseInt(expiryTime) - currentTime;

            if (distance <= 0) {
                clearInterval(checkoutTimerInterval);
                $('#checkout-timer').text('Time expired. Releasing slots...');
                updateServerSession('expired', currentHash);

                $.post(typeof phive_spinner !== 'undefined' ? phive_spinner.ajaxurl : (typeof my_ajax_obj !== 'undefined' ? my_ajax_obj.ajaxurl : ajaxurl), {
                    action: "ph_delete_freezed_slots"
                }).always(function () {
                    $('#checkout-timer').text('Slots Released!');
                    setTimeout(function () {
                        $('#checkout-timer').text('').hide();
                    }, 1500)
                });
            } else {
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);
                seconds = seconds < 10 ? '0' + seconds : seconds;
                $('#checkout-timer').text('Time Left: ' + minutes + ':' + seconds + ' mins');
            }
        }, 1000);
    }

    $(document.body).on('updated_checkout', function () {
        // Grab current booking dates from the hidden inputs
        let currentFrom = $('#phive_book_from_date').val() || '';
        let currentTo = $('#phive_book_to_date').val() || '';
        let currentBookingHash = currentFrom + '|' + currentTo;
        let savedBookingHash = currentHash;

        // Only reset the timer if the booking date or slot actually changed
        if (currentBookingHash !== '|' && savedBookingHash !== currentBookingHash) {
            if (checkoutTimerInterval) {
                clearInterval(checkoutTimerInterval);
                checkoutTimerInterval = null;
            }
            // Passing empty string for expiry clears it on the server
            updateServerSession('', currentBookingHash);
        }

        if (currentExpiry !== 'expired') {
            startCheckoutTimer();
        }
    });
});
// --- END NEW CHECKOUT TIMER LOGIC ---