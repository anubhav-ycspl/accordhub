jQuery(document).ready(function ($) {

    /*jQuery(document).on('change click keyup', '#ph-calendar-time li.ph-calendar-date', function () {

        var firstActiveValue = jQuery('#ph-calendar-time li.selected-date')
            .not('.de-active')
            .first()
            .find('.callender-full-date')
            .val();

        if(firstActiveValue){
            console.log(firstActiveValue);
            $(".ph-date-from").val(firstActiveValue);
        }
    });*/



    $("#vt_room_details button.take-tour").click(function () {
        $(this).parent(".image-container").addClass("hidden");
        $(this).parent(".image-container").next(".shortcode-box").addClass("active");
    })

    $('#contact_form #form-field-email').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    Fancybox.bind("[data-fancybox]", {});

    function selectBookingCo(tries = 0, dateData, slotData) {
        // 1. Safety break
        if (!dateData || tries > 20) {
            console.log("Max retries reached or invalid data.");
            return;
        }

        let dateCl = $("input.callender-full-date[value='" + dateData + "']");

        // 2. Handle Date Not Found (Navigation)
        if (!dateCl.length) {
            let nextBtn = $("li.ph-next");
            if (nextBtn.length) {
                nextBtn.trigger("click");
                // Increase timeout to 1000ms to allow AJAX load time
                setTimeout(function () {
                    selectBookingCo(tries + 1, dateData, slotData);
                }, 1000);
                return;
            }
            // If no next button and no date, simply retry
            setTimeout(function () {
                selectBookingCo(tries + 1, dateData, slotData);
            }, 1000);
            return;
        }

        // 3. Select Date
        let dateClLi = dateCl.closest("li.ph-calendar-date");
        if (!dateClLi.hasClass("timepicker-selected-date")) {
            dateClLi.trigger("click");
            // Return here to allow the UI to update (slots usually load AFTER date click)
            setTimeout(function () {
                selectBookingCo(tries + 1, dateData, slotData);
            }, 500);
            return;
        }

        // 4. Handle Slots (Fixing the "No Slot" Loophole)
        if (!slotData) {
            // If date is selected and no slots required, we are done.
            $(".room_popup_box_loading").removeClass("room_popup_box_loading");
            return;
        }

        let slots_val = slotData.split(",");
        let allSelected_val = true;

        // ... inside the slot loop ...

        slots_val.forEach(function (s) {
            let slotValueData = dateData + " " + s.trim();
            let slotCl = $("input.callender-full-date[value='" + slotValueData + "']");

            if (slotCl.length) {
                let slotClLi = slotCl.closest("li.ph-calendar-date");

                // FIX: Check for disabled/booked classes common in booking systems
                if (slotClLi.hasClass("de-active")) {
                    console.log("Slot is disabled/booked: " + s);
                    // OPTION 1: Abort immediately so we don't retry 20 times
                    tries = 999;
                    allSelected_val = true; // Stop the loop logic
                    return;
                }

                if (!slotClLi.hasClass("selected-date")) {
                    slotClLi.trigger("click");
                }

                // Check success
                if (!slotClLi.hasClass("selected-date")) {
                    allSelected_val = false;
                }
            } else {
                allSelected_val = false;
            }
        });

        if (allSelected_val) {
            $(".room_popup_box_loading").removeClass("room_popup_box_loading");
            return;
        }

        // Retry for slots
        setTimeout(function () {
            selectBookingCo(tries + 1, dateData, slotData);
        }, 1000);
    }


    function selectBookingCart(tries = 0, dateData, slotData) {

        var inputDate = new Date(dateData);
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        const observer = new MutationObserver(() => {
            let pbox = document.querySelector('.room_popup_box');
            if (pbox) {
                //pbox.classList.add("room_popup_box_loading");
                observer.disconnect();   // ⛔ stop observing after first success
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
        if (inputDate < today) {
            $(".room_popup_box_loading").removeClass("room_popup_box_loading");
            return;
        }
        if (!dateData || tries > 20) {
            $(".room_popup_box_loading").removeClass("room_popup_box_loading");
            return;
        }

        let dateCl = $("input.callender-full-date[value='" + dateData + "']");
        if (!dateCl.length) {
            // Date not visible → click next month
            let nextBtn = $("li.ph-next");
            if (nextBtn.length) {
                nextBtn.trigger("click");
            }

            // Retry after short delay
            setTimeout(function () {
                selectBookingCart(tries + 1, dateData, slotData);
            }, 500);
            return;
        }

        if (dateCl.length) {
            // Select the date
            let dateClLi = dateCl.closest("li.ph-calendar-date");
            // ✅ Only click if not already selected
            if (!dateClLi.hasClass("timepicker-selected-date")) {
                dateClLi.trigger("click");
                console.log('trigger');
            }

            if (slotData) {
                let slots_val = slotData.split(",");
                let allSelected_val = true;

                slots_val.forEach(function (s, index) {
                    let slotValueData = dateData + " " + s.trim();
                    let slotCl = $("input.callender-full-date[value='" + slotValueData + "']");
                    if (slotCl.length) {
                        let slotClLi = slotCl.closest("li.ph-calendar-date");

                        if (!slotClLi.hasClass("selected-date")) {
                            setTimeout(function () {
                                slotClLi.trigger("click"); // click if not already 
                            }, index * 500); // stagger clicks to allow UI to process multiple slots
                            allSelected_val = false; // ensure we wait for verification
                        }

                        // if still not selected, mark false
                        if (!slotClLi.hasClass("selected-date") && slotClLi.hasClass("de-active")) {
                            allSelected_val = false;
                            console.log('not selected');
                        }
                        if (slotClLi.hasClass("de-active")) {
                            allSelected_val = true;
                            console.log('deactive');
                        }
                    } else {
                        allSelected_val = false; // slot not found 
                    }
                });

                if (allSelected_val) {
                    $(".room_popup_box_loading").removeClass("room_popup_box_loading");
                    return; // ✅ success only if ALL slots are selected
                }
            }

        }

        // Retry after 1 sec if not ready
        setTimeout(function () {
            selectBookingCart(tries + 1, dateData, slotData);
        }, 1000);
    }

    // Calendar ajax trigger
    jQuery(document).on("click", ".get_avail_btn", function (e) {
        e.preventDefault();

        $(this).addClass("loading_btn");

        let product_id = jQuery(this).closest(".room_box").find(".curr_product_id span").text();

        let $btn = jQuery(this).find(".elementor-button-text");
        let oldText = $btn.text();

        // Update text
        $btn.text("Please wait...");

        // Start AJAX
        jQuery.post(my_ajax_obj.ajaxurl, {
            action: "load_booking_calendar",
            product_id: product_id
        }, function (response) {

            // Open Fancybox only after AJAX success
            Fancybox.show([
                {
                    html: '<div id="room_popup_box" class="room_popup_box">' + response + '</div>',
                }
            ]);

            // ➜ Trigger Hive Booking AJAX
            loadPhiveBlockedDates(product_id);

            // Restore button text
            $btn.text(oldText);
            $('.get_avail_btn').removeClass("loading_btn");

        }).fail(function () {
            console.log("Something went wrong. Please try again.");
            // Restore button text
            $btn.text(oldText);
            $('.get_avail_btn').removeClass("loading_btn");
        });
    });

    // Trigger Hive Plugin Blocked Dates AJAX
    /*function loadPhiveBlockedDates(product_id) {
        var today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        $.ajax({
            url: my_ajax_obj.ajax_url,
            type: "POST",
            data: {
                action: "phive_get_blocked_dates",
                product_id: product_id,
                start_date: today
            },
            success: function(res){
                console.log(res);
                if (!res.success) {
                    console.log("No blocked dates found");
                    return;
                }

                console.log("Hive Blocked Dates:", res.data);

                // ➜ Tell Hive booking calendar to disable these dates
                // (This JS function is used by the plugin)
                if (typeof window.phiveDisableDates === "function") {
                    window.phiveDisableDates(res.data);
                }
            }
        });
    }*/

    function loadPhiveBlockedDates(product_id) {
        var today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        //product_id = jQuery( "#phive_product_id" ).val();
        //start_date = jQuery( ".callender-full-date" ).first().val();
        var data = {
            action: 'phive_get_blocked_dates',
            product_id: product_id,
            start_date: today
        };
        jQuery(".ph-calendar-overlay").show();
        jQuery.post(
            phive_booking_ajax.ajaxurl,
            data,
            function (res) {
                // console.log('success');
                result = jQuery.parseJSON(res);
                jQuery.each(
                    result,
                    function (key, value) {
                        first_el = jQuery("input[value='" + value + "']").closest("li.ph-calendar-date");
                        first_el.addClass('de-active');
                        first_el.addClass('not-available');
                    }
                );
                jQuery(".ph-calendar-overlay").hide();
                //$(".room_popup_box_loading").removeClass("room_popup_box_loading");
            }
        ).fail(
            function () {
                // console.log('failed');
                jQuery(".ph-calendar-overlay").hide();
                $(".room_popup_box_loading").removeClass("room_popup_box_loading");
            }
        );
    }

    // Fetch popup date/slots
    $(document).on("click", ".popup_addtocart", function (e) {
        e.preventDefault();

        let data_href = $(this).attr("data-href");
        let selectedDate = null;
        let selectedSlots = [];

        // 1️⃣ Get selected date (YYYY-MM-DD)
        let dateInput = $("li.ph-calendar-date.timepicker-selected-date input.callender-full-date");
        if (dateInput.length) {
            selectedDate = dateInput.val(); // e.g. "2025-09-26"
        }

        // 2️⃣ Get selected slots (HH:MM from value OR full text from span)
        $("li.ph-calendar-date.selected-date input.callender-full-date").each(function () {
            let val = $(this).val().trim(); // e.g. "2025-09-26 09:00"
            let timePart = val.split(" ")[1]; // just HH:MM

            // fallback to readable text (09:00 am - 1:00 pm)
            let readable = $(this).siblings(".ph_calendar_time").text().trim();

            if (timePart) {
                selectedSlots.push(timePart);
            } else if (readable) {
                selectedSlots.push(readable);
            }
        });

        // 3️⃣ Validate
        if (!selectedDate) {
            alert("Please select a date first.");
            return;
        }

        // 4️⃣ Build redirect URL
        let params = new URLSearchParams();
        params.set("date", selectedDate);
        if (selectedSlots.length) {
            params.set("slot", selectedSlots.join(","));
        }

        // 5️⃣ Redirect
        //alert(data_href + "?" + params.toString());
        window.location.href = data_href + "?" + params.toString();
    });

    // Calendar ajax trigger
    jQuery(document).on('click', '#slots-yes', function (e) {
        e.preventDefault();
        let mbtn = jQuery(".calender-popup");
        $('#slots-yes').addClass("loading_btn");


        let urlData = mbtn.attr("href");
        let urlObj = new URL(urlData, window.location.origin);

        let productIdParam = urlObj.searchParams.get("product_id");
        let dateParam = urlObj.searchParams.get("date");
        let slotParam = urlObj.searchParams.get("slot");
        let amountParam = urlObj.searchParams.get("amount");
        let membersParam = urlObj.searchParams.get("members");
        let caseIdParam = urlObj.searchParams.get("case_id");
        let caseTitleParam = urlObj.searchParams.get("case_title");



        //console.log(productIdParam + " | " + dateParam + " | " + slotParam + " | " + amountParam + " | " + membersParam + " | " + caseIdParam + " | " + caseTitleParam);
        jQuery.post(my_ajax_obj.ajaxurl, {
            action: "ph_delete_freezed_posts"
        }, function (deleteResponse) {

            if (deleteResponse.success) {
                // Start AJAX
                jQuery.post(my_ajax_obj.ajaxurl, {
                    action: "load_booking_calendar_CO",
                    product_id: productIdParam
                }, function (response) {

                    jQuery("#update-booking-slots").fadeOut();

                    // Open Fancybox only after AJAX success
                    Fancybox.show([
                        {
                            html: '<div id="room_popup_box" class="room_popup_box room_popup_CO_box room_popup_box_loading">' + response + '</div>',
                        }
                    ]);

                    // ➜ Trigger Hive Booking AJAX
                    loadPhiveBlockedDates(productIdParam);


                    //selectBookingCo(tries = 0, dateParam, slotParam);
                    //setTimeout(function () {
                    dynamicCalendar(dateParam, slotParam, productIdParam);
                    //}, 5000);

                    setTimeout(function () {
                        console.log("trigger time func");
                        $(".input-person").val(membersParam);
                        //$(".phive_book_resources").val(amountParam);
                        $('input[name="wapf[field_68c7cf78747bb]"]').val(caseIdParam);
                        $('textarea[name="wapf[field_68afdc5063a8e]"]').text(caseTitleParam);
                        $(".time-picker").before('<div class="full-btn"><button id="auto-select-times" class="">Book For Full-day</button></div>');
                        $('.single_add_to_cart_button').before('<input type="hidden" name="final-cart" value="1">');

                    }, 500);


                    // Restore button text
                    //$btn.text(oldText);
                    $('#slots-yes').removeClass("loading_btn");
                    jQuery(".room_popup_box_loading").removeClass("room_popup_box_loading");

                }).fail(function () {
                    console.log("Something went wrong. Please try again.");
                    // Restore button text
                    //$btn.text(oldText);
                    $('#slots-yes').removeClass("loading_btn");
                });
            } else {
                console.log("Failed to clear freezed posts.");
                $('#slots-yes').removeClass("loading_btn");
            }

        }).fail(function () {
            console.log("Server error occurred while deleting freezed posts.");
            $('#slots-yes').removeClass("loading_btn");
        });


    });

    jQuery(document).on('click', '.case-edit-popup:not(:has(form)) #close-popup', function (e) {
        e.preventDefault();
        jQuery(".case-edit-popup").hide();
    });

    // Edit Cart Popup
    jQuery(document).on("click", ".saved_cart_calander", function (e) {
        e.preventDefault();

        $(this).addClass("loading_btn");

        let urlData = $(this).attr("href");
        let urlObj = new URL(urlData, window.location.origin);
        let cartName = $(this).data('cart-name');

        let productIdParam = urlObj.searchParams.get("product_id");
        let dateParam = urlObj.searchParams.get("date");
        let slotParam = urlObj.searchParams.get("slot");
        let amountParam = urlObj.searchParams.get("amount");
        let membersParam = urlObj.searchParams.get("members");
        let caseIdParam = urlObj.searchParams.get("case_id");
        let caseTitleParam = urlObj.searchParams.get("case_title");

        //console.log(productIdParam + " | " + dateParam + " | " + slotParam + " | " + amountParam + " | " + membersParam + " | " + caseIdParam + " | " + caseTitleParam);

        // Start AJAX
        jQuery.post(my_ajax_obj.ajaxurl, {
            action: "load_booking_calendar_cart",
            product_id: productIdParam,
            cart_name: cartName
        }, function (response) {

            // Open Fancybox only after AJAX success
            Fancybox.show([
                {
                    html: '<div id="room_popup_box" class="room_popup_box room_popup_CO_box room_popup_cart_box  saved_cart_fancybox">' + response + '</div>',
                }
            ]);

            // ➜ Trigger Hive Booking AJAX
            loadPhiveBlockedDates(productIdParam);

            const observer = new MutationObserver(() => {
                let pbox = document.querySelector('.room_popup_box');
                if (pbox) {
                    pbox.classList.add("room_popup_box_loading");
                    observer.disconnect();   // ⛔ stop observing after first success
                }
            });

            observer.observe(document.body, { childList: true, subtree: true });
            //selectBookingCart(tries = 0, dateParam, slotParam);
            dynamicCalendar(dateParam, slotParam, productIdParam);

            setTimeout(function () {
                console.log("trigger time func ajx");
                $(".input-person").val(membersParam);
                //$(".phive_book_resources").val(amountParam);
                $('input[name="wapf[field_68c7cf78747bb]"]').val(caseIdParam);
                $('textarea[name="wapf[field_68afdc5063a8e]"]').text(caseTitleParam);
                $(".time-picker").before('<div class="full-btn"><button id="auto-select-times" class="">Book For Full-day</button></div>');
            }, 500);


            // Restore button text
            //$btn.text(oldText);
            $('.saved_cart_calander').removeClass("loading_btn");

            $(".room_popup_box_loading").removeClass("room_popup_box_loading");

        }).fail(function () {
            console.log("Something went wrong. Please try again.");
            // Restore button text
            //$btn.text(oldText);
            $('.saved_cart_calander').removeClass("loading_btn");
            $(".room_popup_box_loading").removeClass("room_popup_box_loading");
        });

    });

    // Update cart data on cart page
    jQuery(document).on("click", ".update_cart_from_popup", function (e) {
        e.preventDefault();

        // Get selected date
        let selectedDate = jQuery("li.ph-calendar-date.timepicker-selected-date input.callender-full-date").val();

        // Get selected times
        let selectedTimes = [];
        jQuery("#ph-calendar-time .ph-calendar-date.selected-date .callender-full-date").each(function () {
            selectedTimes.push(jQuery(this).val());
        });

        let selectedSlots = jQuery('#ph_selected_blocks').val();
        let newPrice = jQuery('#phive_booked_price').val();

        // Create array of results
        let bookingData = {
            date: selectedDate,
            times: selectedTimes,
            slots: selectedSlots,
            price: newPrice,
        };

        console.log(bookingData);

        //return bookingData;

        // Start AJAX
        jQuery.post(my_ajax_obj.ajaxurl, {
            action: "save_booking_calendar_cart",
            bookingData: bookingData,
            cartName: jQuery(this).data('cart-name')
        }, function (response) {
            if (response.success) {
                // Create a popup div
                var $popup = jQuery('<div id="success-popup" style="position: fixed;top: 50vh;right: 50vw;padding:15px;background:#4CAF50;color:#fff;border-radius:5px;z-index:9999;transform: translate(50%, 50%);">' + response.data.message + '</div>');
                jQuery('body').append($popup);

                // Fade out after 2 seconds
                setTimeout(function () {
                    window.location.href = window.location.href;
                }, 1000);

                // Close your extra services popup
                jQuery('.fancybox__dialog').fadeOut(function () { jQuery(this).remove(); });
                jQuery('body').removeClass('hide-scrollbar');

            } else {
                var $popup = jQuery('<div id="error-popup" style="position: fixed;top: 50vh;right: 50vw;padding:15px;background:#fff;color:red;border:1px solid red;border-radius:5px;z-index:9999;transform: translate(50%, 50%);">' + response.data.message + '</div>');
                jQuery('body').append($popup);

                // Fade out after 4 seconds
                setTimeout(function () {
                    jQuery('#error-popup').fadeOut(function () { jQuery(this).remove(); });
                }, 4000);

                // Close your extra services popup
                jQuery('.fancybox__dialog').fadeOut(function () { jQuery(this).remove(); });
                jQuery('body').removeClass('hide-scrollbar');
            }
        }).fail(function () {
            console.log("Something went wrong. Please try again.");
        });

    });


});
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.adr-slider-component').forEach(sliderComponent => {

        const swiperContainer = sliderComponent.querySelector('.swiper');
        const paginationEl = sliderComponent.querySelector('.swiper-pagination');

        if (!swiperContainer || !paginationEl) {
            console.warn('Swiper container or pagination not found in component:', sliderComponent);
            return;
        }

        // 👉 Create Swiper instance
        const swiper = new Swiper(swiperContainer, {
            centeredSlides: true,
            slidesPerView: 'auto',
            loop: false,
            loopAdditionalSlides: 1,
            setWrapperSize: true,
            autoplay: {
                delay: 800000,
                disableOnInteraction: false
            },
            spaceBetween: 0,
            pagination: {
                el: paginationEl,
                clickable: true
            },
            // navigation: {
            //     nextEl: '.swiper-button-next',
            //     prevEl: '.swiper-button-prev',
            // }
        });

        // 👉 Lottie animations list
        const baseUploadsPath = `${my_ajax_obj.homeUrl}/wp-content/uploads/2025/11`;

        const animations2 = [
            { id: 'illustration6', path: `${baseUploadsPath}/Confidential-1-1.json` },
            { id: 'illustration7', path: `${baseUploadsPath}/Prime-Strategic-Venues.json` },
            { id: 'illustration8', path: `${baseUploadsPath}/Uninterrupted-Hearings.json` },
            { id: 'illustration9', path: `${baseUploadsPath}/One-click-booking.json` },
            { id: 'illustration10', path: `${baseUploadsPath}/Transparent-Pricing.json` },
        ];

        console.log(animations2);


        const lottieInstances2 = {};

        // 🎬 Load all Lottie animations (paused)
        animations2.forEach(anim => {
            const el = sliderComponent.querySelector(`#${anim.id}`);
            if (el) {
                lottieInstances2[anim.id] = lottie.loadAnimation({
                    container: el,
                    renderer: 'canvas',
                    loop: true,
                    autoplay: false,
                    path: anim.path
                });
            }
        });

        // ⏸ Pause all
        function pauseAllLotties() {
            Object.values(lottieInstances2).forEach(anim => anim.pause());
        }

        // ▶ Play only active slide animation
        function playActiveSlideLottie() {
            pauseAllLotties();

            const activeSlide = swiper.slides[swiper.activeIndex];
            if (!activeSlide) return;

            const lottieEl = activeSlide.querySelector('.lottie-anim');
            if (!lottieEl) return;

            const id = lottieEl.id;
            if (lottieInstances2[id]) {
                lottieInstances2[id].play();
            }
        }

        // When slider initializes
        swiper.on('init', () => {
            playActiveSlideLottie();
        });

        // When slide changes
        swiper.on('slideChange', () => {
            playActiveSlideLottie();
        });

        // Trigger manually (Swiper v8+)
        playActiveSlideLottie();

    });

});


window.addEventListener('load', () => {
    setTimeout(() => {
        // Find all lists you want to convert
        document.querySelectorAll('.vt-list').forEach((container) => {

            if (window.innerWidth > 1200) {
                return;
            }

            // Check if this list has already been turned into a slider
            if (container.classList.contains('swiper-initialized')) {
                return;
            }

            // Find all the cards inside THIS container
            const cards = container.querySelectorAll('.vt-card');
            if (cards.length === 0) {
                return; // No cards to slide, do nothing
            }

            // --- 2. RE-STRUCTURE DOM ---

            // Create the required <div class="swiper-wrapper">
            const swiperWrapper = document.createElement('div');
            swiperWrapper.classList.add('swiper-wrapper');

            // Loop through each .vt-card
            cards.forEach(card => {
                // Create a <div class="swiper-slide">
                const swiperSlide = document.createElement('div');
                swiperSlide.classList.add('swiper-slide');

                // Move the original card INSIDE the new slide
                swiperSlide.appendChild(card);

                // Add the new slide (with the card) to the wrapper
                swiperWrapper.appendChild(swiperSlide);
            });

            // Clear the original .vt-list and append the new .swiper-wrapper
            container.innerHTML = '';
            container.appendChild(swiperWrapper);

            // Add the 'swiper' class to the main .vt-list container
            container.classList.add('swiper');

            // Remove ARIA tab roles, as it's a slider now
            container.removeAttribute('role');
            container.removeAttribute('aria-orientation');

            // --- 3. CREATE CONTROLS ---

            // Create a new wrapper for controls
            const controlsWrapper = document.createElement('div');
            controlsWrapper.classList.add('vt-slider-controls');

            // Create elements for pagination and navigation
            const pagination = document.createElement('div');
            pagination.classList.add('swiper-pagination');

            const prevButton = document.createElement('div');
            prevButton.classList.add('swiper-button-prev');

            const nextButton = document.createElement('div');
            nextButton.classList.add('swiper-button-next');

            // Add controls to their wrapper
            controlsWrapper.appendChild(prevButton);
            controlsWrapper.appendChild(pagination);
            controlsWrapper.appendChild(nextButton);

            // Add the controls wrapper *after* the swiper container
            container.after(controlsWrapper);

            // --- 4. INITIALIZE SWIPER ---
            new Swiper(container, {
                // Mobile-first config (shows 1 and a bit of the next)
                slidesPerView: 1, // Use 'auto' to respect the CSS width
                spaceBetween: 15,
                centeredSlides: false,
                loop: true,
                grabCursor: true,
                speed: 800,
                autoplay: {
                    delay: 3500,    // 8 seconds
                    disableOnInteraction: false
                },
                // Link to our newly created controls
                pagination: {
                    el: pagination, // Use the JS variable, not a string
                    clickable: true,
                },
                navigation: {
                    nextEl: nextButton, // Use the JS variable
                    prevEl: prevButton, // Use the JS variable
                },
                breakpoints: {
                    981: {
                        slidesPerView: 2,
                    }
                }
            });

            // Mark as initialized to prevent running the script twice
            container.classList.add('swiper-initialized');
        });
    }, 100); // ⏳ WAIT 1 SECOND AFTER FULL PAGE LOAD
});

document.addEventListener("DOMContentLoaded", () => {
    const faqsContainer = document.querySelector(".faqs-div");
    const accordionWidgets = document.querySelectorAll(".faqs-div .elementskit-accordion");
    const allCards = document.querySelectorAll(".faqs-div .elementskit-card");
    const button = document.querySelector(".faq-btn a");

    if (!button || accordionWidgets.length === 0) return;

    if (window.innerWidth <= 768) {
        // Find all cards that are currently "active" (open)
        const activeCards = document.querySelectorAll(".faqs-div .elementskit-card.active");

        activeCards.forEach(card => {
            // 1. Remove active class from the card wrapper
            card.classList.remove("active");

            // 2. Find the content body and hide it
            const content = card.querySelector(".ekit-accordion--content").closest(".collapse");
            if (content) {
                content.classList.remove("show"); // Bootstrap class that shows content
            }

            // 3. Reset the button state (for accessibility and icons)
            const btn = card.querySelector(".ekit-accordion--toggler");
            if (btn) {
                btn.classList.add("collapsed");
                btn.setAttribute("aria-expanded", "false");
            }
        });
    }

    faqsContainer.addEventListener("click", (e) => {
        // Check if the clicked element is a toggle button
        const toggler = e.target.closest(".ekit-accordion--toggler");

        // Only run this logic on Mobile
        if (toggler && window.innerWidth <= 768) {
            const currentCard = toggler.closest(".elementskit-card");

            // Find ALL currently open cards in the entire section
            const allActiveCards = faqsContainer.querySelectorAll(".elementskit-card.active");

            allActiveCards.forEach(otherCard => {
                // If the open card is NOT the one we just clicked, close it
                if (otherCard !== currentCard) {
                    // 1. Remove active class from card
                    otherCard.classList.remove("active");

                    // 2. Hide the content body
                    const content = otherCard.querySelector(".ekit-accordion--content").closest(".collapse");
                    if (content) {
                        content.classList.remove("show");
                    }

                    // 3. Reset the button icon to (+)
                    const btn = otherCard.querySelector(".ekit-accordion--toggler");
                    if (btn) {
                        btn.classList.add("collapsed");
                        btn.setAttribute("aria-expanded", "false");
                    }
                }
            });
        }
    });

    let expanded = false;

    // Logic for the "Collapsed" state
    function applyCollapsedState() {
        const isMobile = window.innerWidth <= 768;

        accordionWidgets.forEach((widget, widgetIndex) => {
            const cards = widget.querySelectorAll(".elementskit-card");
            const half = Math.ceil(cards.length / 2);

            cards.forEach((card, cardIndex) => {
                if (isMobile) {
                    if (widgetIndex === 0) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }
                } else {
                    if (cardIndex >= half) {
                        card.style.display = "none";
                    } else {
                        card.style.display = "block";
                    }
                }
            });
        });

        button.textContent = "View All FAQs";
        expanded = false;
    }

    // Logic for the "Expanded" state (Show everything)
    function showAllFaqs() {
        allCards.forEach(card => (card.style.display = "block"));
        button.textContent = "View Less FAQs";
        expanded = true;
    }

    // Main function to decide layout based on screen size and current state
    function updateLayout() {
        // Always make sure button is visible (previously we hid it on mobile)
        button.style.display = "inline-block";

        if (expanded) {
            showAllFaqs();
        } else {
            applyCollapsedState();
        }
    }

    // Initialize
    updateLayout();

    // Listen for resize
    window.addEventListener("resize", updateLayout);

    // Click Event
    button.addEventListener("click", (e) => {
        e.preventDefault();
        if (expanded) {
            applyCollapsedState();
        } else {
            showAllFaqs();
        }
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const items = document.querySelectorAll(".timeline > div");

    if (window.innerWidth > 600) return;

    function setActiveByCenter() {
        const viewportCenter = window.innerHeight / 2;
        let closest = null;
        let closestDistance = Infinity;

        items.forEach(item => {
            const rect = item.getBoundingClientRect();
            const itemCenter = rect.top + rect.height / 2;

            const distance = Math.abs(itemCenter - viewportCenter);

            if (distance < closestDistance) {
                closestDistance = distance;
                closest = item;
            }
        });

        if (closest) {
            items.forEach(i => i.classList.remove("active"));
            closest.classList.add("active");
        }
    }

    // Run on scroll + initial load
    window.addEventListener("scroll", setActiveByCenter, { passive: true });
    setActiveByCenter();
});

// Use standard JS to access the Capture Phase
const eventsToBlock = ['change', 'input', 'keyup'];

eventsToBlock.forEach(function (eventType) {
    document.addEventListener(eventType, function (e) {
        // Check if the element triggering the event is our specific input
        if (e.target && e.target.matches('input.input-person.shipping-price-related')) {
            // Stop the event dead in its tracks during the capture phase
            e.stopPropagation();
            e.stopImmediatePropagation();

            // Add or select the error span next to the input
            let errorSpan = e.target.nextElementSibling;
            if (!errorSpan || !errorSpan.classList.contains('error')) {
                e.target.insertAdjacentHTML('afterend', '<span class="error" style="color: red; position: relative; margin-top: 2px; display: block;"></span>');
                errorSpan = e.target.nextElementSibling;
            }

            // Validate not to exceed max value
            if (e.target.hasAttribute('max') && parseFloat(e.target.value) > parseFloat(e.target.getAttribute('max'))) {
                e.target.value = e.target.getAttribute('max');
                errorSpan.textContent = 'The maximum number of members allowed is ' + e.target.getAttribute('max');

                // Clear the error message after 2 seconds
                clearTimeout(e.target.errorTimeout);
                e.target.errorTimeout = setTimeout(function () { errorSpan.textContent = ''; }, 3000);
            }

            // Validate not to drop below min value
            if (e.target.hasAttribute('min') && parseFloat(e.target.value) < parseFloat(e.target.getAttribute('min'))) {
                e.target.value = e.target.getAttribute('min');
                errorSpan.textContent = 'The minimum number of members allowed is ' + e.target.getAttribute('min');

                // Clear the error message after 2 seconds
                clearTimeout(e.target.errorTimeout);
                e.target.errorTimeout = setTimeout(function () { errorSpan.textContent = ''; }, 3000);
            }
        }
    }, true); // The 'true' argument is the magic here—it enables the Capture Phase
});

/* Dynamic Calendar date functions */
function getParam(name) {
    let url = new URL(window.location.href);
    return url.searchParams.get(name);
}

// ✅ NEW: Sequential slot selection with delay
function selectSlotsWithDelay(dateParam, slotParam) {
    if (!slotParam) return;

    let slots = slotParam.split(",");
    let index = 0;

    function clickNext() {

        if (index >= slots.length) {
            console.log("✅ All slots selected");
            return;
        }

        let slotValue = dateParam + " " + slots[index].trim();
        let slotInput = jQuery("input.callender-full-date[value='" + slotValue + "']");

        if (slotInput.length) {
            let slotLi = slotInput.closest("li.ph-calendar-date");

            if (!slotLi.hasClass("selected-date")) {
                slotLi.trigger("click");
                console.log("🟢 Clicked:", slotValue);
            }
        } else {
            console.log("❌ Slot not found:", slotValue);
        }

        index++;

        // ⏳ Delay between clicks (VERY IMPORTANT)
        setTimeout(clickNext, 200);
    }

    clickNext();
}

function selectSlotsRobust(dateParam, slotParam) {
    if (!slotParam) return;

    let slots = slotParam.split(",");
    let index = 0;

    function processNextSlot() {

        if (index >= slots.length) {
            console.log("✅ All slots processed");
            jQuery(".room_popup_box_loading").removeClass("room_popup_box_loading");
            return;
        }

        let slotTime = slots[index].trim();
        let slotValue = dateParam + " " + slotTime;

        let attempts = 0;
        let maxAttempts = 15;

        function trySelectCurrentSlot() {

            let slotInput = jQuery("input.callender-full-date[value='" + slotValue + "']");

            if (!slotInput.length) {
                console.log("❌ Slot not found:", slotValue);
                index++;
                setTimeout(processNextSlot, 500);
                return;
            }

            let slotLi = slotInput.closest("li.ph-calendar-date");

            // ✅ If already selected → move next
            if (slotLi.hasClass("selected-date")) {
                console.log("✅ Already selected:", slotValue);
                index++;
                setTimeout(processNextSlot, 500);
                return;
            }

            // 🔁 Try clicking
            slotLi.trigger("click");
            attempts++;

            console.log("🔄 Attempt", attempts, "for", slotValue);

            // ⏳ Wait and verify
            setTimeout(function () {

                if (slotLi.hasClass("selected-date")) {
                    console.log("✅ Selected:", slotValue);
                    index++;
                    setTimeout(processNextSlot, 500); // wait before next slot
                } else if (attempts < maxAttempts) {
                    trySelectCurrentSlot(); // retry same slot
                } else {
                    console.log("❌ Failed after retries:", slotValue);
                    index++;
                    setTimeout(processNextSlot, 500);
                }

            }, 500); // wait for phive_get_booked_price ajax

        }

        trySelectCurrentSlot();
    }

    processNextSlot();
}

// Function to select date in calendar
function dynamicCalendar(dateParam, slotParam, product_id) {

    console.log("dynamicCalendar() Triggered.");

    if (!product_id) {
        console.log("Product ID not found");
        jQuery(".room_popup_box_loading").removeClass("room_popup_box_loading");
        return;
    }

    var dateObj = new Date(dateParam);

    dateObj.setMonth(dateObj.getMonth() - 1);
    var month = dateObj.toLocaleString('default', { month: 'long' });
    var year = dateObj.getFullYear();

    var data = {
        action: 'phive_get_callender_next_month',
        product_id: product_id,
        month: month,
        year: year,
        calendar_for: "time-picker",
    };

    jQuery(".ph-calendar-overlay").show();

    jQuery.post(phive_spinner.ajaxurl, data, function (res) {

        jQuery(".ph-calendar-overlay").hide();

        if (calender_type == 'time') {
            jQuery(".ph-ul-time").html('').hide();

            jQuery(".booking-info-wraper").html(
                '<p style="text-align:center;">' + phive_booking_locale.Please_Pick_a_Date + '</p>'
            );
        }

        result = jQuery.parseJSON(res);

        jQuery(".ph-ul-date").html(result.days);

        jQuery(".callender-month").val(result.month).change();
        jQuery(".callender-year").val(result.year);

        if ((result.month == ph_current_month) && (result.year == ph_current_year)) {
            jQuery(".ph-prev").hide();
        } else {
            jQuery(".ph-prev").show();
        }

        jQuery(".span-month").html(result.display_month || phive_booking_locale.months[full_month.indexOf(result.month)]);
        jQuery(".span-year").html(result.display_year);

        if (!jQuery(".ph-date-from").val()) {
            jQuery(".single_add_to_cart_button").addClass("disabled");
        }

        block_unavailable_dates();

        let dateCl = jQuery("input.callender-full-date[value='" + dateParam + "']");

        if (dateCl.length) {

            let dateClLi = dateCl.closest("li.ph-calendar-date");

            if (!dateClLi.hasClass("timepicker-selected-date")) {

                dateClLi.addClass("timepicker-selected-date");

                let loading_ico_url = my_ajax_obj.homeUrl + "/wp-content/plugins/ph-bookings-appointments-woocommerce-premium/resources/css/images/loading2.gif";

                jQuery(".ph-ul-time").show().html(
                    '<img class="loading-ico" align="middle" src="' + loading_ico_url + '">'
                );

                var data = {
                    action: 'phive_get_booked_datas_of_date',
                    product_id: product_id,
                    date: dateParam,
                    type: 'time-picker',
                    custom_time_period: ""
                };

                jQuery(".ph-calendar-date").prop('disabled', true);

                jQuery.post(phive_spinner.ajaxurl, data, function (res) {

                    jQuery(".ph-calendar-overlay").hide();
                    jQuery(".ph-calendar-date").prop('disabled', false);

                    if (jQuery('#calendar_design').val() == '3') {
                        jQuery('.time-calendar-date-section').hide();
                        jQuery('.ph-calendar-container .time-picker').show();
                    }

                    // ✅ Inject slots
                    jQuery(".ph-ul-time").html(res).show();

                    if (jQuery('#calendar_design').val() == '3' && $is_booking_end_date_calendar_open) {
                        jQuery('.ph_calendar_time_start').hide();
                        jQuery('.ph_calendar_time_end').show();
                    }

                    // ✅ Let plugin initialize
                    setTimeout(function () {

                        jQuery('#ph-calendar-time').trigger('change');

                        // 🔥 SELECT SLOTS (WITH DELAY)
                        //selectSlotsWithDelay(dateParam, slotParam);
                        selectSlotsRobust(dateParam, slotParam);

                    }, 100);

                    jQuery(".booking-info-wraper").html(
                        '<p style="text-align:center;">' + phive_booking_ajax.pick_a_time + '</p>'
                    );

                    $is_booking_end_date_calendar_open = false;
                });
            }
        }
    });
}