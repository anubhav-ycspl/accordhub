jQuery(document).ready(function($){

    // Store initial form data
    let initialData = $('#pref_form').serialize();

    //alert($(window).width());

    /* ===============================
    GLOBAL STORAGE
    ================================= */

    let allSeatsData = JSON.parse(localStorage.getItem('all_seats_data')) || {};
    let currentSeat = null;


    /* ===============================
    HELPERS
    ================================= */

    function resetForm(){

        $('#pref_form')[0].reset();

        $('.rm_qty_field').val('');
        $('.total_item_price').html('');
        $('.rm_qty_box').hide();
        $('.rm_qty_add_btn').show();
        $('.rm_remark_field_box').hide();

        $('.other_dietary_preference_field').hide();
        $('.other_food_allergies_intolerances_field').hide();
        $('.tea_sugar_free_field').hide();

        $(".rm_remark_btn").removeClass("active");

        $(".special_instructions_count").text('50 chars left');
        $(".rm_remark_field_count, .other_food_allergies_intolerances_field_count, .other_dietary_preference_field_count, .other_tea_coffee_count").text('30 chars left');
    }

    function restoreSeatListUI(){

        let allSeatsData = JSON.parse(localStorage.getItem('all_seats_data')) || {};

        //if (Object.keys(allSeatsData).length > 0) {
        setTimeout(() => {
            if( $(".seat_data_item span").length > 0 ){
                $(".view_pref, .reset_seats").removeClass("disabled");
                $(".view_pref, .reset_seats").removeAttr("disabled");
            }
        }, 300);
        

        $.each(allSeatsData, function(seatNo, seatData){

            let fullName = seatData.preferences?.pr_full_name || '-';

            let seatEl = $('.seat_li[data-id="'+seatNo+'"]');

            if(seatEl.length){

                seatEl.find('.seat_data_full_name')
                    .html('Full Name: <span>' + fullName + '</span>');

                seatEl.find('.seat_data_status')
                    .html('Status: <span>Details Added </span>');

                seatEl.addClass('seat_saved'); // optional highlight class
            }

        });

    }

    function getFormData(form){

        let formArray = form.serializeArray();
        let data = {};

        $.each(formArray, function(_, field){

            if(data[field.name]){
                if(!Array.isArray(data[field.name])){
                    data[field.name] = [data[field.name]];
                }
                data[field.name].push(field.value);
            } else {
                data[field.name] = field.value;
            }

        });

        return data;
    }

    function getRefreshmentData(){

        let items = [];

        $('.rm_table_row').each(function(){

            let row = $(this);
            let qty = parseInt(row.find('.rm_qty_field').val()) || 0;

            if(qty > 0){

                let price = parseFloat(row.find('.rm_price').data('price')) || 0;

                items.push({
                    row_id: row.attr('id'),
                    item_name: row.find('.rm_item_title').text().trim(),
                    price: price,
                    quantity: qty,
                    total: qty * price,
                    remark: row.find('.rm_remark_field_box input').val() || ''
                });
            }
        });

        return items;
    }

    function populateSeat(seatNo){

        if(!allSeatsData[seatNo]) return;

        let data = allSeatsData[seatNo];

        // populate normal fields
        $.each(data.preferences, function(name, value){

            //console.log(name + " | " + value);

            if(name == 'rm_remark_field'){
                return;
            }

            let field = $('[name="'+name+'"]');

            //if()

            if(field.attr('type') === 'radio'){
                field.filter('[value="'+value+'"]').prop('checked', true);
            }
            else if(field.attr('type') === 'checkbox'){
                field.each(function(){
                    $(this).prop('checked', value.includes($(this).val()));
                });
            }
            else{
                field.val(value);
            }

            if( name == 'food_allergies_intolerances' && value.includes("Other") ){
                $(".other_food_allergies_intolerances_field").show();
            }

            if( name == 'tea_coffee' && (value == 'Tea' || value == 'Coffee') ){
                $(".tea_sugar_free_field").show();
            }

            if( name == 'dietary_preference' && value == 'Other' ){
                $(".other_dietary_preference_field").show();
            }

            if( name == 'other_food_allergies_intolerances' ){
                $('.other_dietary_preference_field_count').text((30 - value.length) + ' chars left');
            }

            if( name == 'other_tea_coffee' ){
                $('.other_tea_coffee_count').text((30 -  value.length) + ' chars left');
            }

            if( name == 'other_dietary_preference' ){
                $('.other_food_allergies_intolerances_field_count').text((30 -  value.length) + ' chars left');
            }

            if( name == 'special_instructions' ){
                $('.special_instructions_count').text((50 - value.length) + ' chars left');
            }

        });

        // populate refreshments
        data.refreshments.forEach(function(item){
            let row = $('#'+item.row_id);

            row.find('.rm_qty_add_btn').hide();
            row.find('.rm_qty_box').show();
            row.find('.rm_qty_field').val(item.quantity);
            row.find('.total_item_price').html('₹' + item.total);
            row.find('.rm_remark_btn').addClass("active");
            row.find('.rm_remark_field_box').show();
            row.find('.rm_remark_field_box input').val(item.remark);
            row.find('.rm_remark_field_box input').next('.rm_remark_field_count').text( (30 - item.remark.length) + ' chars left');
        });

    }

    function populateTeaSummary(allSeatsData){

        let teaSummary = {};

        $('#order_tea_coffee .order_tab_right tbody').html('<tr><th colspan="2">Order Summary</th></tr>');

        $.each(allSeatsData, function(seatNo, seatData){

            let tea = seatData.preferences?.tea_coffee || '--';

            if(!teaSummary[tea]){
                teaSummary[tea] = 0;
            }

            teaSummary[tea]++;

        });

        $.each(teaSummary, function(name, qty){
            if(name != '--' && name != 'None'){
                $('#order_tea_coffee .order_tab_right tbody').append(`
                    <tr>
                        <td>${name} x ${qty}</td>
                        <td>₹0</td>
                    </tr>
                `);
            }
        });

    }

    function populateMealSummary(allSeatsData){

        let mealSummary = {};

        $('#order_meal .order_tab_right tbody').html('<tr><th colspan="2">Order Summary</th></tr>');

        $.each(allSeatsData, function(seatNo, seatData){

            let bread = seatData.preferences?.bread_preference || '--';

            if(!mealSummary[bread]){
                mealSummary[bread] = 0;
            }

            mealSummary[bread]++;

        });

        $.each(mealSummary, function(name, qty){
            if(name != '--'){
                $('#order_meal .order_tab_right tbody').append(`
                    <tr>
                        <td>${name} x ${qty}</td>
                        <td>₹0</td>
                    </tr>
                `);
            }
        });

    }

    function populateOrderPopup(){

        let allSeatsData = JSON.parse(localStorage.getItem('all_seats_data')) || {};

        // Clear old data
        $('#order_tea_coffee tbody').html('');
        $('#order_meal tbody').html('');
        $('#order_refreshments .order_tab_left tbody').html('');
        //$('#order_refreshments .order_tab_right tbody').html('<tr><th colspan="2">Order Summary</th></tr>');
        $('.order_ref_charges tbody').html('');
        $('.order_ref_charges tbody').html('<tr><th colspan="2">Order Summary</th></tr>');

        let grandItems = {};
        let grandTotal = 0;

        $.each(allSeatsData, function(seatNo, seatData){

            let prefs = seatData.preferences || {};
            let refreshments = seatData.refreshments || [];

            /* ======================
            Beverage Selection
            ====================== */

            prefs.tea_coffee = prefs.tea_coffee;

            if( prefs.tea_sugar_free == "Without Sugar" && ( prefs.tea_coffee == 'Tea' || prefs.tea_coffee == 'Black Tea' || prefs.tea_coffee == 'Coffee' || prefs.tea_coffee == 'Black Coffee' ) ){
                prefs.tea_coffee = `${prefs.tea_coffee} (${prefs.tea_sugar_free})`;
            }
            
            if(prefs.tea_coffee || prefs.other_tea_coffee){
                $('#order_tea_coffee tbody').append(`
                    <tr>
                        <td>Seat #${seatNo}</td>
                        <td>${prefs.tea_coffee || '--'}</td>
                        <td>${prefs.other_tea_coffee || '--'}</td>
                    </tr>
                `);
            }

            /* ======================
            MEAL
            ====================== */

            //console.log(prefs.tea_sugar_free);

            let allergies = Array.isArray(prefs.food_allergies_intolerances)
                ? prefs.food_allergies_intolerances.join(', ')
                : (prefs.food_allergies_intolerances || '--');

            if(allergies.includes("Other")){
                if(prefs.other_food_allergies_intolerances){
                    allergies = `${allergies} (${prefs.other_food_allergies_intolerances})`;
                }else{
                    //allergies = allergies.replace(", Other", "");
                }
            }

            if(prefs.dietary_preference == "Other"){
                if(prefs.other_dietary_preference){
                    prefs.dietary_preference = `${prefs.dietary_preference} (${prefs.other_dietary_preference})`;
                }else{
                    //prefs.dietary_preference = "--";
                }
            }
            
            if( prefs.bread_preference || prefs.dietary_preference || prefs.special_instructions || (allergies != "--") ){
                $('#order_meal tbody').append(`
                    <tr>
                        <td>Seat #${seatNo}</td>
                        <td>${prefs.bread_preference || '--'}</td>
                        <td>${allergies}</td>
                        <td>${prefs.dietary_preference || '--'}</td>
                        <td>${prefs.special_instructions || '--'}</td>
                    </tr>
                `);
            }

            /* ======================
            REFRESHMENTS LEFT
            ====================== */

            if(refreshments.length){

                let orderText = [];
                let seatTotal = 0;

                refreshments.forEach(function(item){

                    orderText.push(`${item.item_name} x ${item.quantity}`);
                    seatTotal += item.total;

                    // accumulate for summary (UPDATED PART ONLY)
                    if(!grandItems[item.item_name]){
                        grandItems[item.item_name] = {
                            qty: 0,
                            total: 0
                        };
                    }

                    grandItems[item.item_name].qty += parseInt(item.quantity);
                    grandItems[item.item_name].total += parseFloat(item.total);

                });

                grandTotal += seatTotal;

                $('#order_refreshments .order_tab_left tbody').append(`
                    <tr>
                        <td>Seat #${seatNo}</td>
                        <td>${orderText.join(', ')}</td>
                        <td>₹${seatTotal}</td>
                    </tr>
                `);
            }

        });
        
        /* ======================
        Tea/Coffee SUMMARY (RIGHT)
        ====================== */
        //populateTeaSummary(allSeatsData);

        /* ======================
        Meal SUMMARY (RIGHT)
        ====================== */
        //populateMealSummary(allSeatsData);

        /* ======================
        REFRESHMENTS SUMMARY (RIGHT)
        ====================== */

        //console.log(grandItems);
        if ($.isEmptyObject(grandItems)) {
            //console.log('INSIDE EMPTY CHECK:', grandItems);
            //$('.order_ref_charges').html(`<div class="no_ref_added">No paid items added.</div>`);
            $('.order_ref_charges').addClass("empty_ref_charges");
            $('.order_ref_charges tbody').html(`
                                                <tr>
                                                    <td colspan="2" class="no_ref_added">No paid items added.</td>
                                                </tr>
                                            `);
            return false;
        }

        $('.order_ref_charges').removeClass("empty_ref_charges");

        $.each(grandItems, function(itemName, data){
            /*$('#order_refreshments .order_tab_right tbody').append(`
                <tr>
                    <td>${itemName} x ${data.qty}</td>
                    <td>₹${data.total}</td>
                </tr>
            `);*/

            $('.order_ref_charges tbody').append(`
                <tr>
                    <td>${itemName} x ${data.qty}</td>
                    <td>₹${data.total}</td>
                </tr>
            `);
        });

        let gst = (grandTotal * 0.09).toFixed(2);
        grandTotal = grandTotal.toFixed(2);
        let finalTotal = (parseFloat(grandTotal) + parseFloat(gst) + parseFloat(gst)).toFixed(2);

        $('.cgts_price').text('₹' + gst);
        $('.sgts_price').text('₹' + gst);
        $('.subtotal_price').text('₹' + grandTotal);
        $('.total_price').text('₹' + finalTotal);
    }

    // Restore saved data
    restoreSeatListUI();
    populateOrderPopup();

    /* ===============================
    SEAT CLICK
    ================================= */

    $(".seat_item, .seat_li").on('click', function(){

        let id = $(this).data("id");

        // deactivate
        if(currentSeat == id){
            $(".seat_item, .seat_li").removeClass("active");
            $(".pref_wr").slideUp();
            //$(".view_pref").hide();
            //$(".view_pref").addClass("disabled");
            //$(".view_pref").attr("disabled", "disabled");
            currentSeat = null;
            /*$('.seat_ul').animate({
                scrollTop: ($(`.seat_li[data-id="1"`).position().top + $('.seat_ul').scrollTop()) - 285
            }, 400);*/
            return;
        }

        currentSeat = id;

        $(".seat_item, .seat_li").removeClass("active");
        $(`.seat_li[data-id="${id}"], .seat_item[data-id="${id}"]`).addClass("active");

        $(".pr_seat_no").text(id);
        $("input[name=pr_seat_no]").val(id);

        $(".pref_wr").slideDown();
        // $(".view_pref").removeClass("disabled");
        // $(".view_pref").removeAttr("disabled");

        var seat_ul = $('.seat_ul');
        var activeSeat = $(`.seat_li.active`);
        //console.log(activeSeat.position().top + " | " + seat_ul.scrollTop());
        if( $(window).width() > 980 ){
            //console.log('1');
            seat_ul.animate({
                scrollTop: (activeSeat.position().top + seat_ul.scrollTop()) - 335
            }, 400);
        }else if( ($(window).width() <= 980) && ($(window).width() > 766) ){
            //console.log('2');
            seat_ul.animate({
                scrollTop: (activeSeat.position().top + seat_ul.scrollTop()) - 895
            }, 400);
        }else if( ($(window).width() <= 767) && ($(window).width() > 581) ){
            //console.log('3');
            seat_ul.animate({
                scrollTop: (activeSeat.position().top + seat_ul.scrollTop()) - 905
            }, 400);
        }else {
            //console.log('4');
            seat_ul.animate({
                scrollTop: (activeSeat.position().top + seat_ul.scrollTop()) - 787
            }, 400);
        }

        resetForm();

        // load old data if exists
        populateSeat(id);

        initialData = $('#pref_form').serialize();

    });


    /* ===============================
    SAVE FORM
    ================================= */

    $('#pref_form').on('submit', function(e){

        e.preventDefault();

        if(!currentSeat){
            //alert('Select seat first');
            return;
        }

        allSeatsData[currentSeat] = {
            seat_no: currentSeat,
            preferences: getFormData($(this)),
            refreshments: getRefreshmentData()
        };

        localStorage.setItem('all_seats_data', JSON.stringify(allSeatsData));

        // Update seat list UI
        let fullName = allSeatsData[currentSeat].preferences.pr_full_name || '-';

        //$(`.seat_li[data-id="${currentSeat}"]`).find('.seat_data_full_name').html('Full Name: <span>' + fullName + '</span>');

        $(`.seat_li[data-id="${currentSeat}"]`)
            .find('.seat_data_status')
            .html('Status: <span>Details Added</span>');

        populateOrderPopup();

        $(".view_pref, .reset_seats").removeClass("disabled");
        $(".view_pref, .reset_seats").removeAttr("disabled");

        $(".pref_right_message").html(``);
        $(".pref_right_message").html(`Preferences saved`);
        $(".pref_right_message").slideDown();
        $('.save_pref').prop('disabled', true); // Disable again if reverted
        initialData = $('#pref_form').serialize();

        setTimeout(() => {
            $(".pref_right_message").slideUp();
        }, 4000);

    });


    /* ===============================
    QTY SYSTEM
    ================================= */

    $(document).on('click', '.rm_qty_add_btn', function(){

        let box = $(this).next(".rm_qty_box");

        $(this).hide();
        box.show();
        box.find('.rm_qty_field').val(1).trigger('change');
        $submitBtn.prop('disabled', false); // Enable
    });

    $(document).on('click', '.rm_qty_plus', function(){

        let input = $(this).siblings('.rm_qty_field');
        input.val((parseInt(input.val())||0) + 1).trigger('change');
        $submitBtn.prop('disabled', false); // Enable
    });

    $(document).on('click', '.rm_qty_minus', function(){

        let input = $(this).siblings('.rm_qty_field');
        let val = parseInt(input.val())||0;

        if(val > 0){
            input.val(val - 1).trigger('change');
            $submitBtn.prop('disabled', false); // Enable
        }
    });

    $(document).on('change', '.rm_qty_field', function(){

        let row = $(this).closest('.rm_table_row');
        let qty = parseInt($(this).val())||0;
        let price = parseFloat(row.find('.rm_price').data('price'))||0;

        if(qty <= 0){
            row.find('.rm_qty_box').hide();
            row.find('.rm_qty_add_btn').show();
            row.find('.total_item_price').html('');
            return;
        }

        row.find('.total_item_price').html('₹' + (qty * price));
    });


    /* ===============================
    TEXT LIMIT
    ================================= */

    $('textarea[name="special_instructions"]').on('input', function(){

        if(this.value.length > 50){
            this.value = this.value.substring(0,50);
        }

        $('.special_instructions_count').text((50 - $(this).val().length) + ' chars left');

    });

    $(document).on('input', '.repeater_textarea', function () {

        let maxLength = 50;

        if (this.value.length > maxLength) {
            this.value = this.value.substring(0, maxLength);
        }

        let remaining = maxLength - $(this).val().length;

        $(this).siblings('.repeater_textarea_count').text(remaining + ' chars left');

    });

    $('input[name="rm_remark_field"]').on('input', function(){

        if(this.value.length > 30){
            this.value = this.value.substring(0,30);
        }

        $(this).next('.rm_remark_field_count').text((30 - $(this).val().length) + ' chars left');

    });

    $('input[name="other_food_allergies_intolerances"]').on('input', function(){

        if(this.value.length > 30){
            this.value = this.value.substring(0,30);
        }

        $(this).next('.other_food_allergies_intolerances_field_count').text((30 - $(this).val().length) + ' chars left');

    });

    $('input[name="other_dietary_preference"]').on('input', function(){

        if(this.value.length > 30){
            this.value = this.value.substring(0,30);
        }

        $(this).next('.other_dietary_preference_field_count').text((30 - $(this).val().length) + ' chars left');

    });

    $('input[name="other_tea_coffee"]').on('input', function(){

        if(this.value.length > 30){
            this.value = this.value.substring(0,30);
        }

        $(this).next('.other_tea_coffee_count').text((30 - $(this).val().length) + ' chars left');

    });


    /* ===============================
    Toggle next box
    ================================= */
    $(".toggle_head").click(function(){
        $(this).toggleClass("active");
        $(this).next(".toggle_content").stop().slideToggle();
    });

    $(".rm_remark_btn").click(function(){
        $(this).toggleClass("active");
        $(this).next(".rm_remark_field_box").stop().toggle();
    });

    $('input[name="dietary_preference"]').on('change', function(){

        if($(this).val() === 'Other'){
            $('.other_dietary_preference_field').slideDown();
        } else {
            $('.other_dietary_preference_field').slideUp();
        }

    });

    $('input[name="tea_coffee"]').on('change', function(){

        if( $(this).val() === 'Tea' || $(this).val() === 'Black Tea' || $(this).val() === 'Coffee' || $(this).val() === 'Black Coffee' ){
            $('.tea_sugar_free_field').slideDown();
        } else {
            $('.tea_sugar_free_field').slideUp();
        }

    });

    $('input[name="food_allergies_intolerances"]').on('change', function(){

        if ($('input[name="food_allergies_intolerances"][value="Other"]').is(':checked')) {
            $('.other_food_allergies_intolerances_field').slideDown();
        } else {
            $('.other_food_allergies_intolerances_field').slideUp();
        }

    });


    /* ===============================
    Tabs filter
    ================================= */

    $(".rm_tabs_li").click(function(){
        var filter = $(this).data("filter");
        $(".rm_tabs_li").removeClass("active");
        $(this).addClass("active");

        if(filter == 'all'){
            $(".rm_table_row").show();
        }else{
            $(".rm_table_row").hide();
            $(`.rm_table_row[data-filter=${filter}]`).show();
        }
    });

    $(".order_tabs_li").click(function(){
        var filter = $(this).data("filter");
        $(".order_tabs_li").removeClass("active");
        $(this).addClass("active");

        $(".order_tab_content").hide();
        $(`#${filter}`).show();
        
    });


    /* ===============================
    Reset form
    ================================= */

    $(".clear_fields").click(function(){
        resetForm();
    });


    /* ===============================
    Order Popup
    ================================= */

    $(".view_pref").click(function(){
        Fancybox.show(
            [{ 
                src: "#order_popup", 
                type: "inline",
                
            }],
            {
                closeButton: false,
                dragToClose: false,
            }
        );
    });
    

    /* ===============================
    Save order in CMS
    ================================= */

    $(document).on('click', '.save_webapp_order_btn', function(e){

        e.preventDefault();

        let allSeatsData = JSON.parse(localStorage.getItem('all_seats_data')) || {};

        if($.isEmptyObject(allSeatsData)){
            //alert('No order data found.');
            return;
        }

        // Optional: calculate totals again before sending
        let grandTotal = 0;

        $.each(allSeatsData, function(seatNo, seatData){
            let refreshments = seatData.refreshments || [];
            refreshments.forEach(function(item){
                grandTotal += parseFloat(item.total);
            });
        });

        let gst = (grandTotal * 0.05).toFixed(2);
        let finalTotal = (parseFloat(grandTotal) + parseFloat(gst)).toFixed(2);

        $.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'save_webapp_order',
                seats_data: allSeatsData,
                grand_total: grandTotal,
                gst: gst,
                final_total: finalTotal
            },
            beforeSend: function(){
                $('.save_webapp_order_btn').prop('disabled', true).text('Saving...');
            },
            success: function(response){
                console.log(response);
                //alert('Order saved successfully!');
            },
            error: function(xhr){
                console.log(xhr.responseText);
                //alert('Something went wrong.');
            },
            complete: function(){
                $('.save_webapp_order_btn').prop('disabled', false).text('Save Order');
            }
        });

    });

    // Fancybox scroll fixes
    document.querySelectorAll('.table_res').forEach(function(el) {
        el.addEventListener('touchmove', function(e){
            e.stopPropagation();
        }, { passive: true });
    });
    
    document.querySelector('.order_tabs_top').addEventListener('touchmove', function(e){
        e.stopPropagation();
    }, { passive: true });

    /* ===============================
    Reset Data
    ================================= */

    $(document).on('click', '.reset_seats', function(){

        Fancybox.show(
            [{ 
                src: "#reset_confirm_popup", 
                type: "inline",
                
            }],
            {
                closeButton: false,
                dragToClose: false,
            }
        );

    });

    // Cancel
    $(document).on('click', '.btn_cancel', function(){
        Fancybox.close();
    });

    // Confirm Reset
    $(document).on('click', '.btn_confirm_reset', function(){

        localStorage.removeItem('all_seats_data');

        Fancybox.close();

        // small delay for smooth UX
        setTimeout(function(){
            window.location.href = window.location.pathname + '?t=' + Date.now();
        }, 200);

    });

    /*$(document).on('click', '.reset_seats', function(){

        if (confirm('This will remove all selected seats and orders. Continue?')) {
            // Clear specific localStorage key
            localStorage.removeItem('all_seats_data');

            // Reload page
            //location.reload();
            window.location.href = window.location.pathname + '?t=' + Date.now();
        }

    });*/

/* ===============================
    SAVE FORM BUTTON CONDITION
================================= */

    let $form = $('#pref_form');
    let $submitBtn = $('.save_pref');

    // Initially disable button
    $submitBtn.prop('disabled', true);

    // Detect any change in form
    $form.on('input change', 'input, textarea, select', function () {

        let currentData = $form.serialize();

        if (currentData !== initialData) {
            $submitBtn.prop('disabled', false); // Enable
        } else {
            $submitBtn.prop('disabled', true); // Disable again if reverted
        }
    });

});