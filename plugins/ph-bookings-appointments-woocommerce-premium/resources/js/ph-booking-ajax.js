
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

jQuery(document).ready(function ($) {
  $is_booking_end_date_clicked = false;
  $is_booking_end_date_calendar_open = false;
  $ph_date_from_previous_value = '';
  
  function generate_booking_info_text(from, to, cost, custom_messages, result) {
    $('.display_time_from').val(from);
    $('.display_time_to').val(to);
    if ($('#calender_type').val() == 'date' && $('#charge_per_night').length && $('#charge_per_night').val() == 'yes') {
      from_text = phive_booking_ajax.checkin;
      to_text = phive_booking_ajax.checkout;
    } else {
      from_text = phive_booking_ajax.booking;
      to_text = phive_booking_ajax.to;
    }
    if (from !== to && phive_booking_ajax.display_end_time) 
    {
      // ticket 104504  safari and IE not support all date format.
      var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));
      var isIE = /*@cc_on!@*/false || !!document.documentMode;
      if(isSafari || isIE)
      {
        date_and_time_from = result.org_from_date.split(" ");
        date_form = date_and_time_from[0].split("-");
        new_date_from = date_form[1] + "/" + date_form[2] + "/" +date_form[0] + " " + date_and_time_from[1];
        
        date_and_time_to = result.org_to_date.split(" ");
        date_to = date_and_time_to[0].split("-");
        new_date_to = date_to[1] + "/" + date_to[2] + "/" +date_to[0]+ " "+ date_and_time_to[1];
  
        from_date = new Date(new_date_from);
        to_date = new Date(new_date_to);
      }
      else
      {
        from_date = new Date(result.org_from_date);
        to_date = new Date(result.org_to_date);
      }
      if ($('#calender_type').val() != 'date' && $('#calender_type').val() != 'month' && from_date.getMonth() == to_date.getMonth() && from_date.getFullYear() == to_date.getFullYear() && from_date.getDate() == to_date.getDate()) {
        var wp_date_format = jQuery("#ph_booking_wp_time_format").val();
        var hours = to_date.getHours();
        var ampm = hours >= 12 ? phive_booking_ajax.am_pm_to_text['pm']: phive_booking_ajax.am_pm_to_text['am'];
        to_time = ph_convert_time_to_wp_time_format(wp_date_format, to_date, ampm);
        date_html = "<b>" + from_text + ":</b>&nbsp;" + from + "&nbsp;<b>" + to_text + "</b>&nbsp;" + to_time;
      }
      else {
        date_html = "<b>" + from_text + ":</b>&nbsp;" + from + "&nbsp;<b>" + to_text + "</b>&nbsp;" + to;
      }

    } else {
      date_html = "<b>" + phive_booking_ajax.booking + ":</b>&nbsp;" + from;
    } //for Addon

    jQuery('.from_text').val(from_text);
    jQuery('.to_text').val(to_text).trigger('change');
    if (custom_messages == '')
      return '<p id="booking_info_text">' + date_html + '</p> <p id="booking_price_text"> <b>' + phive_booking_ajax.booking_cost + ':&nbsp;</b>' + cost + '</p>';
    else
      return '<p id="booking_info_text">' + date_html + '</p> <p id="booking_price_text"> <b>' + phive_booking_ajax.booking_cost + ':&nbsp;</b>' + cost + '</p> <p id="booking_custom_text"> ' + custom_messages + '</p>';
  
    }
  function timezoneOffset(date_string) {
    // passing default parameter value doesn't work in safari
    if (date_string === undefined || !date_string) {
      date_string = '';
    }
    let date = new Date(date_string),
      timezoneOffset = date.getTimezoneOffset(),
      hours = ('00' + Math.floor(Math.abs(timezoneOffset / 60))).slice(-2),
      minutes = ('00' + Math.abs(timezoneOffset % 60)).slice(-2),
      string = (timezoneOffset >= 0 ? '-' : '+') + hours + ':' + minutes;
    return string;
  }

  function ph_convert_date_to_wp_date_format(wp_format, date) {

    months    = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    new_date  = new Date(date + 'T' + '00:00' + timezoneOffset(date));

    month = ("0" + (new_date.getMonth() + 1)).slice(-2);
    date  = ("0" + new_date.getDate()).slice(-2);

    switch (wp_format) {
      case 'j F Y': display_date = date + ' ' + phive_booking_ajax.months[new_date.getMonth()] + ' ' + new_date.getFullYear();
        break;
      case 'F j, Y': display_date = phive_booking_ajax.months[new_date.getMonth()] + ' ' + date + ', ' + new_date.getFullYear();
        break;
      case 'Y-m-d': display_date = new_date.getFullYear() + '-' + month + '-' + date;
        break;
      case 'm/d/Y': display_date = month + '/' + date + '/' + new_date.getFullYear();
        break;
      case 'd/m/Y': display_date = date + '/' + month + '/' + new_date.getFullYear();
        break;
      case 'd.m.Y': display_date = date + '.' + month + '.' + new_date.getFullYear();
        break;
      case 'm.d.Y': display_date = month + '.' + date + '.' + new_date.getFullYear();
        break;
      default: display_date = new_date.getFullYear() + '-' + month + '-' + date;
        break;

    }
    return display_date;
  }

  function ph_convert_time_to_wp_time_format(wp_format, date, am_pm) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    wp_format = wp_format + "";
    switch (wp_format) {
      case "g:i a":
      case "g:ia":
      case "g:i A":
      case "g:iA":
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;

        // Determine if format is lowercase or uppercase
        let isUpperCase = wp_format === "g:i A" || wp_format === "g:iA";
        let space = (wp_format === "g:i a" || wp_format === "g:i A") ? ' ' : '';
        
        display_time = hours + ':' + minutes + space + (isUpperCase ? am_pm.toUpperCase() : am_pm.toLowerCase());
        break;
      case "H:i":
        hours = hours.toString().padStart(2, 0);
        minutes = minutes.toString().padStart(2, 0);
        display_time = hours + ':' + minutes;
        break;
      case "G \\h i \\m\\i\\n":
        display_time = hours + ' h ' + minutes + ' min';
        break;
      case "G\\hi":     // 151305
        minutes = minutes.toString().padStart(2, 0);
        display_time = hours + 'h' + minutes;
        break;
      case "G:i":
        minutes = minutes < 10 ? '0' + minutes : minutes;
        display_time = hours + ':' + minutes;
        break;
      case "G.i":
        minutes = minutes < 10 ? '0' + minutes : minutes;
        display_time = hours + '.' + minutes;
        break;
      default:
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;
        display_time = hours + ':' + minutes + ' ' + am_pm.toUpperCase();
        break;

    }
    return display_time;
  }
  function resetSelection(fullReset) {
    fullReset = fullReset || 'yes';
    date_from = '';
    date_to = '';
    click = 0;
    $(".single_add_to_cart_button").addClass("disabled");
    $(".selected-date").each(function () {
      $(this).removeClass("selected-date");
    });


    $(".ph-date-from").val("");
    $(".ph-date-to").val("");
    $('.element_from').val("");
    $('.element_to').val("");
    $('.element_from_date').val("");
    $('.element_from_time').val("");
    $('.element_to_date').val("");
    $('.element_to_time').val("");
    $('#ph_selected_blocks').val('');

    if (fullReset == 'yes') {
      $(".booking-info-wraper").html("");
    }

    if ($(".not-startable").length) {
      $(".not-startable").removeClass("hide-not-startable");
    }
  }
  /*function formate_date( input_date ){
    var date = new Date( input_date.replace(/-/g, "/") ); //Safari bowser will accept only with seprator '/'
    
    var month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][date.getMonth()];
    var strDate = date.getDate() + '-' + month + '-' + date.getFullYear();
      if( $("#calender_type").val() =='time' ){
      strDate += " ";
      strDate += date.getHours()<10 ? "0"+date.getHours() : date.getHours();
      strDate += ":"
      strDate += date.getMinutes()<10 ? "0"+date.getMinutes() : date.getMinutes();
    }
    return strDate;
  }*/
  // Calculate the booking price.


  jQuery(document).on('change', ".ph-date-to", function () {
    calculate_booking_price();
  });

  // #104395 - For input type number, price should re-calculate on change
  jQuery(document).on('change', ".addon,.wc-pao-addon-field,.ph-addons:not(.phive-addon-textbox):not(.phive-addon-textarea), input.ph-addons.phive-addon-textbox[type=number]", function () 
  {
    from = jQuery(".ph-date-from").val();

    if (from != '') {
      calculate_booking_price();
    }
  });

  //update price on image select - wc product addons
  jQuery(document).on('click', ".wc-pao-addon-image-swatch", function () {
    from = jQuery(".ph-date-from").val();
    if (from != '') {
      calculate_booking_price();
    }
  });

  // update price on keyup - product addons
  jQuery(document).on('keyup', ".ph-addons.phive-addon-textbox, .ph-addons.phive-addon-textarea", function () {
    from = jQuery(".ph-date-from").val();
    if(jQuery(this).attr('data-price'))
    {
      data_price = parseInt(jQuery(this).attr('data-price'));
      if(data_price == 0)
      {
        return true;
      }
    }

    if (from != '') {
      calculate_booking_price();
    }
  });

  function calculate_booking_price() {
    if ($('.input-disabled').length || !jQuery(".ph-date-from").length) {
      return;
    }

    $(".shipping-price-related").addClass('input-disabled');
    $(".shipping-price-related").attr("disabled", true);

    //171254 Issue: Customer is using WooCommerce Product Add-ons plugin along with our Bookings plugin. When selecting the add-ons quickly the cost is not getting added.
    $(".ph-addon-container").css({'pointer-events':'none','cursor':'not-allowed','opacity':'0.4'});
    $(".wc-pao-addon-container").css({'pointer-events':'none','cursor':'not-allowed','opacity':'0.4'});
    
    var loding_ico_url = $("#plugin_dir_url").val() + "/resources/icons/loading2.gif";

    $(".booking-info-wraper").html('<img class="loading-ico" align="middle" src="' + loding_ico_url + '">');
    $(".single_add_to_cart_button").addClass("disabled");
    var from = jQuery(".ph-date-from").val();
    var to = jQuery(".ph-date-to").val();
    var person_details = [];
    $('.input-person').each(function () {
      if ($(this).val() != '') person_details.push(parseInt($(this).val())); else person_details.push(0);
    }); // for range values select min available slot

    var available_slots = $('.selected-date').map(function () {
      var attr = $(this).attr('data-title');

      if (_typeof(attr) !== (typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) && attr !== false) {
        return parseInt($(this).attr('data-title'));
      } else {
        if ($(this).text() == '' || ($(".selected-date").last().find('.callender-full-date').val() == $(this).find('.callender-full-date').val() && $('#charge_per_night').val() == 'yes')) return; // If try to book between months or per night booking and Consider each participant as booking is marked 
        return 0;
      }
    }).get();
    var available_slot = Math.min.apply(Math, available_slots); // for range values select min available slot

    // 150854 - unable to choose the same day (check-in day for other bookings) as check-out day for other bookings when assigning participants.
    if($('#charge_per_night').val() == 'yes' && $('.book_interval_period').val() == 'day')
    {
      available_slots = $('.selected-date:not(:last)').map(function () {
        attr = $(this).attr('data-title');
  
        if (_typeof(attr) !== (typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) && attr !== false) {
          return parseInt($(this).attr('data-title'));
        } else {
          if ($(this).text() == '' || ($(".selected-date").last().find('.callender-full-date').val() == $(this).find('.callender-full-date').val() && $('#charge_per_night').val() == 'yes')) return; // If try to book between months or per night booking and Consider each participant as booking is marked 
          return 0;
        }
      }).get();
      available_slot = Math.min.apply(Math, available_slots);
    }

    var available_slots_to_displays = $('.selected-date').map(function () {
      var attr = $(this).attr('data-max');

      if (_typeof(attr) !== (typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) && attr !== false) {
        return parseInt($(this).attr('data-max'));
      } else {
        if ($(this).text() == '') return; // If try to book between months and Consider each participant as booking is marked

        return 0;
      }
    }).get();
    var available_slots_to_display = Math.min.apply(Math, available_slots_to_displays);
    var persons_as_booking = $('#persons_as_booking').val();

    if (persons_as_booking == 'yes') {
      person_details_count = person_details.reduce(function (a, b) {
        return a + b;
      }, 0);

      if (person_details_count > available_slot) {
        var message = phive_booking_ajax.available_slot_message;
        message = message.replace('%available_slot', available_slots_to_display);
        $('.booking-info-wraper').html('<p id="booking_info_text"><span class="not-available-msg">' + message + '</span></p>');
        $(".shipping-price-related").removeClass('input-disabled');
        $(".shipping-price-related").removeAttr("disabled");
        // /171254
        $(".ph-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
        $(".wc-pao-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
        return false;
      }

      // 144645 - Issue with Max Bookings per Block & Participant as booking when choosing multiple dates.
      selected_dates_mna = $('.select-random-dates-html-div .select_random_dates_from');
      if (selected_dates_mna.length) 
      {
          var available_slots_mna = $(selected_dates_mna).map(function () {
            var attr = $(this).attr('data-title');
            if (_typeof(attr) !== (typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) && attr !== false) {
              return parseInt($(this).attr('data-title'));
            } else {
              return 0;
            }
          }).get();
          var available_slot_mna = Math.min.apply(Math, available_slots_mna); // for range values select min available slot
          
          var available_slots_to_displays_mna = $(selected_dates_mna).map(function () {
            var attr = $(this).attr('data-max');
      
            if (_typeof(attr) !== (typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) && attr !== false) {
              return parseInt($(this).attr('data-max'));
            } else {
              if ($(this).text() == '') return; // If try to book between months and Consider each participant as booking is marked
      
              return 0;
            }
          }).get();
          var available_slots_to_display_mna = Math.min.apply(Math, available_slots_to_displays_mna);
    
          if (person_details_count > available_slot_mna) 
          {
            var message = phive_booking_ajax.available_slot_message;
            message = message.replace('%available_slot', available_slots_to_display_mna);
            $('.booking-info-wraper').html('<p id="booking_info_text"><span class="not-available-msg">' + message + '</span></p>');
            $(".shipping-price-related").removeClass('input-disabled');
            $(".shipping-price-related").removeAttr("disabled");
            // /171254
            $(".ph-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
            $(".wc-pao-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
            return false;
          }
      }
      // 144645 END
    }

    var total_numer_persons = 0;
    $('.input-person').each(function () {
      // total_numer_persons += parseInt($(this).val());
      Number.isInteger = Number.isInteger || function (value) {
        return typeof value === 'number' &&
          isFinite(value) &&
          Math.floor(value) === value;
      };
      if (Number.isInteger(parseInt($(this).val()))) {
        total_numer_persons += parseInt($(this).val());
      }
    });

    // #105168 - maximum participant should work even if consider each participant as separate booking is active.
    if (total_numer_persons > parseInt($('#phive_booking_maximum_number_of_allowed_participant').val()) && parseInt($('#phive_booking_maximum_number_of_allowed_participant').val()) != 'NaN') {
      var message = phive_booking_ajax.maximum_participant_warning;
      message = message.replace('%total', total_numer_persons);
      message = message.replace('%max', parseInt($('#phive_booking_maximum_number_of_allowed_participant').val()));
      $('.booking-info-wraper').html('<p id="booking_info_text"><span class="not-available-msg">' + message + '</span></p>');
      $(".shipping-price-related").removeClass('input-disabled');
      $(".shipping-price-related").removeAttr("disabled");
      // /171254
      $(".ph-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
      $(".wc-pao-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
      if ($('.ph_booking_info_wrapper_error').length) {
        $('.ph_booking_info_wrapper_error').trigger('click');
      }      return false;
    }

    // if (persons_as_booking != 'yes' && total_numer_persons < parseInt($
    if (total_numer_persons < parseInt($('#phive_booking_minimum_number_of_required_participant').val()) && parseInt($('#phive_booking_minimum_number_of_required_participant').val()) != 'NaN') {
      var message = phive_booking_ajax.minimum_participant_warning;
      // message = message.replace('%total', total_numer_persons);
      message = message.replace('%min', parseInt($('#phive_booking_minimum_number_of_required_participant').val()));
      $('.booking-info-wraper').html('<p id="booking_info_text"><span class="not-available-msg">' + message + '</span></p>');
      $(".shipping-price-related").removeClass('input-disabled');
      $(".shipping-price-related").removeAttr("disabled");
      // /171254
      $(".ph-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
      $(".wc-pao-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});

      if ($('.ph_booking_info_wrapper_error').length) {
        $('.ph_booking_info_wrapper_error').trigger('click');
      }
      return false;
    }

    resources_type = $('.resources_type').val();
    if (resources_type == 'single') {
      resources_details = [];
      selected_resources = $('.phive_book_resources').val();
      $(".phive_book_resources option").each(function (i) {
        if ($(this).val() == '')
          return true;
        val = 'no';
        if (selected_resources == $(this).val()) {
          val = 'yes';
        }

        // Checking id to support hide Resource
        let id = $(this).attr('id');

        let resourceId = id.replace('phive_book_resources_', '');

        resources_details[resourceId] = val;
      });
    }
    else {
      resources_details = [];
      $('.input-resources').each(function () {
        val = $(this).is(":checked") ? 'yes' : 'no';

        // Checking id to support hide Resource
        let id = $(this).attr('id');

        let resourceId = id.replace('phive_book_resources_', '');

        resources_details[resourceId] = val;
      });
    }

    product_id = jQuery("#phive_product_id").val();

    current_product_id = product_id;

    // WPML Compatibility 
    if(jQuery("#product-addons-total"))
    {
        current_product_id = jQuery("#product-addons-total").attr('data-product-id');
    }

    if (from.length === 0 || to.length === 0) {
      return;
    }

    addon_data = jQuery('.addon').serialize();
    if (parseInt($(".wc-pao-addon-field").length) > 0) {
      addon_data = jQuery('.wc-pao-addon-field').serialize();
    }

    product_addon_data = '';
    if (parseInt($(".phive-addon-field").length) > 0) {
      product_addon_data = jQuery('.phive-addon-field').serialize();
    }
    custom_time_period = '';
    if ($(".cutome_time_period").length > 0) {
      custom_time_period = $(".cutome_time_period").find(':selected').val();
    }

    //PH Product Addons - File upload cost not getting added
    if(jQuery('input[name^=addon-fileupload-phive_addons_]').length)
    {
      product_addon_files = jQuery('input[name^=addon-fileupload-phive_addons_]')
      if(product_addon_files.length)
      {
        for (let index = 0; index < product_addon_files.length; index++)
        {
          input_name = $(product_addon_files[index]).attr('name');
          files = product_addon_files[index].files;
          if(files.length)
          {
            if(product_addon_data)
            {
              product_addon_data += '&'+input_name+'='+files[0].name;
            }
            else
            {
              product_addon_data += input_name+'='+files[0].name;
            }
          }
        }
      }
    }

    //WC Product Addons - File upload cost not getting added
    if(jQuery('input.wc-pao-addon-file-upload').length)
    {
      wc_product_addon_files = jQuery('input.wc-pao-addon-file-upload');
      if(wc_product_addon_files.length)
      {
        for (let index = 0; index < wc_product_addon_files.length; index++)
        {
          input_name = $(wc_product_addon_files[index]).attr('name');
          files = wc_product_addon_files[index].files;
          if(files.length)
          {
            if(addon_data)
            {
              addon_data += '&'+input_name+'='+files[0].name;
            }
            else
            {
              addon_data += input_name+'='+files[0].name;
            }
          }
        }
      }
    }

    let json_data = $(".ph_custom_fields").val();

    // Convert the JSON string back to an object
    let ph_custom_fields_data = json_data ? JSON.parse(json_data) : {};

    var data = {
      action: 'phive_get_booked_price',
      // security : phive_booking_ajax.security,
      product_id: product_id,
      book_from: from,
      book_to: to,
      person_details: person_details,
      resources_details: resources_details,
      addon_data: addon_data,
      product_addon_data: product_addon_data,
      asset: $(".input-assets").val(),
      custom_fields: $(".custom_fields").val(),
      ph_custom_fields: ph_custom_fields_data,
      custom_time_period: custom_time_period,
      current_product_id: current_product_id
    };
    jQuery(".ph-calendar-date").prop('disabled', true);
    $.post(phive_booking_ajax.ajaxurl, data, function (res) {
      result = jQuery.parseJSON(res);
      jQuery(".ph-calendar-date").prop('disabled', false); // Confirm the response of last request.
      // The price should updated only for last request incase another request send immediately

      $(".shipping-price-related").removeClass('input-disabled');
      $(".shipping-price-related").removeAttr("disabled");
      // /171254
      $(".ph-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
      $(".wc-pao-addon-container").css({'pointer-events':'initial','cursor':'initial','opacity':'initial'});
      $(".single_add_to_cart_button").removeClass("disabled");
      $(".callender-msg").html('');

      if(result.selected_blocks) {

          $('#ph_selected_blocks').val(result.selected_blocks);
      } else {

        $('#ph_selected_blocks').val('');
      }

      if (result.error_msg) {
        if ($('#calendar_design').val() != '3') {
          resetSelection();
        }
        $('.booking-info-wraper').html(result.error_msg);
        // $("#phive_booked_price").val('');
        $(".phive_booked_price").val('');
        // $(".phive_book_assets").val(asset);
        $(".single_add_to_cart_button").addClass("disabled");
      } else {
        if (to != '') {
          var to_date_obj = new Date(to);
          var from_date_obj = new Date(from);

          //support for customise booking interval addon
          if(custom_time_period && result.org_to_date_customise_booking_interval)
          {
              if(result.org_to_date_customise_booking_interval != '')
              {
                $('.ph-date-to').val(result.org_to_date_customise_booking_interval);
              }
          }
    
          if (from_date_obj.getTime() > to_date_obj.getTime()) {
            resetSelection();
            $('.booking-info-wraper').html('<p id="booking_info_text"><span class="not-available-msg">Booking start date cannot be higher than end date.</span></p>');
            // $("#phive_booked_price").val('');
            $(".phive_booked_price").val('');
            $(".phive_book_assets").val('');
          } else {
            // $(".price").html( result.price_html ); //to change the main product price
            // $("#phive_booked_price").val(result.price);
            $(".phive_booked_price").val(result.price);
            $(".phive_book_assets").val(result.asset_id); //in the case of monthpicker, take last date of 'TO' month
            var custom_messages = '';
            if (result.custom_messages) {
              custom_messages = result.custom_messages;
            }

            if (result.product_addon_data) {
              $(".ph_booking_product_addon_data").val(result.product_addon_data);
            }else{
              $(".ph_booking_product_addon_data").val('');
            }

            if (result.addon_data) {
              $(".ph_booking_addon_data").val(result.addon_data);

              let json_data = JSON.stringify(result.ph_addon_data);

              $(".ph_addon_data").val(json_data);
            }
            /*if( (to.match(new RegExp("-", "g")) || []).length < 2 ){
              var date = new Date( to.replace(/-/g, "/") ); //Safari bowser will accept only with separator '/'
              var LastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
              to = to+"-"+LastDay.getDate();
            }*/

            booking_info_text = generate_booking_info_text(result.from_date, result.to_date, result.price_html, custom_messages, result);
            $('.booking-info-wraper').html(booking_info_text).trigger('change');

            // 213065 When the resource is mandatory and not selected any option book now button should be disabled
            if (resources_type == 'single' && $('.phive_book_resources_required').length && $('.phive_book_resources').val() == '') {

              $(".single_add_to_cart_button").addClass("disabled");
            }
          }
        }
      }
    });
  }

  //   $('.time-picker-wraper #ph-calendar-days').on("click", ".ph-calendar-date", function () {
  $(document).on("click", ".time-picker-wraper .ph-ul-date .ph-calendar-date", function () {

    if ($(this).hasClass("booking-disabled") || 
        $(this).hasClass("de-active") || 
        $(this).hasClass("not-available")) {
        // Do nothing and stop the event if the slot is not available
        return false; 
    }

    var from = jQuery(".ph-date-from").val();
    var element_from_date = $('.element_from_date').val()
    var to = jQuery(".ph-date-to").val();

    var loding_ico_url = $("#plugin_dir_url").val() + "/resources/icons/loading2.gif";
    $(".ph-ul-time").show();
    //     $("#ph-calendar-time").html('<img class="loading-ico" align="middle" src="' + loding_ico_url + '">');
    $(".ph-ul-time").html('<img class="loading-ico" align="middle" src="' + loding_ico_url + '">');
    var product_id = jQuery("#phive_product_id").val();

    if ($is_booking_end_date_calendar_open && from != '') {
      // When user clicks a To Date in Design 3 with all fields filled
      $(".ph-calendar-overlay").show();
      from_date = convert_date_to_wp_format($('.timepicker-selected-date .callender-full-date').val());
      $('.element_to_date').val(from_date);
      $is_booking_end_date_clicked = true;
      $('.element_to_time').val('');
    }
    else if ($is_booking_end_date_calendar_open && from == '') {
      // When user clicks a To Date in Design 3 and the previous selection was invalid
      $(".ph-date-from").val($ph_date_from_previous_value);
      $(".ph-calendar-overlay").show();
      $is_booking_end_date_clicked = true;
      $('.element_to_date').val('');
      $('.element_to_time').val('');
      var current_date = convert_date_to_wp_format($('.timepicker-selected-date .callender-full-date').val());
      $(".element_to_date").val(current_date);
      $('.element_to_time').trigger('click');
      $('.element_to_time').focus();
    }
    else if (from == '' || (from != '' && to != '')) {
      $(".ph-calendar-overlay").show();
      from_date = convert_date_to_wp_format($('.timepicker-selected-date .callender-full-date').val());
      $('.element_from_date').val(from_date).change();
      $('.element_from_time').val('');
      $('.element_from_time').scrollTop();
      $('.element_from_time').trigger('click');
      $('.element_from_time').focus();
      $('.element_to_date').val('');
      $('.element_to_time').val('');
    } else {
      $(".ph-calendar-overlay").show();
      to_date = convert_date_to_wp_format($('.timepicker-selected-date .callender-full-date').val());
      $('.element_to_date').val(to_date);
      $('.element_to_time').val('');
      $('.element_to_time').scrollTop();
      $('.element_to_time').trigger('click');
      $('.element_to_time').focus();
    }

    // custom booking interval addon supprt
    custom_time_period = '';
    if ($(".cutome_time_period").length > 0) {
      custom_time_period = $(".cutome_time_period").find(':selected').val();
    }

    var data = {
      action: 'phive_get_booked_datas_of_date',
      product_id: product_id,
      date: $('.timepicker-selected-date .callender-full-date').val(),
      type: 'time-picker',
      asset: $(".input-assets").val(),
      custom_time_period:custom_time_period
    };
    // $is_booking_end_date_calendar_open = false;

    jQuery(".ph-calendar-date").prop('disabled', true);
    $.post(phive_booking_ajax.ajaxurl, data, function (res) {

      $(".ph-calendar-overlay").hide();
      jQuery(".ph-calendar-date").prop('disabled', false);
      // $("#ph-calendar-time").html(res).trigger('change');

      if ($('#calendar_design').val() == '3') {
        $('.time-calendar-date-section').hide();
        $('.ph-calendar-container .time-picker').show();
      }
      $(".ph-ul-time").html(res);
      // change slot time as end time while showing time calendar for end time
      if ($('#calendar_design').val() == '3') {
        // if (from != '' && to == '') {
        // This was inside IF earlier
        if ($is_booking_end_date_calendar_open) {
          $('.ph_calendar_time_start').hide();
          $('.ph_calendar_time_end').show();
        }
      }
      $('#ph-calendar-time').trigger('change');
      $(this).addClass("timepicker-selected-date");

      // 137142 - Point 2 - When using "Auto Select Available Time of the Date" add-on, its not showing "Minimum number of participants required for a booking is (1)" pop-up.
      if(($(document).find('.ph_auto_select_available_time_of_date_status').length > 0) && 
      ($(document).find('.ph_auto_select_available_time_of_date_status').val() == 'yes') && 
      ($(document).find('.ph-ul-time .ph-calendar-date.selected-date').length > 0))
      {
          return;
      }

      // 137142 - Point 1 - For Time calendar, as of now there is no pop-up like "Please pick a time" after selecting the Date.
      $(".booking-info-wraper").html('<p id="booking_info_text" style="text-align:center;">'+phive_booking_ajax.pick_a_time+'</p><p id="booking_price_text"> </p>');
      $is_booking_end_date_calendar_open = false;
    });

  });

  function convert_date_to_wp_format(date) {
    var wp_date_format = jQuery("#ph_booking_wp_date_format").val();
    date = ph_convert_date_to_wp_date_format(wp_date_format, date);
    return date;
  }

  $('.outlook-wrapper').on('click', '#signin_to_outlook', function (e) {
    e.preventDefault();

    const clientId = $('#ph-outlook-client-id').val();
    const clientSecret  = $('#ph-outlook-client-secret').val();
    
    const data = {
      action: 'ph_signin_with_outlook',
      clientId,
      clientSecret
    };

    $.post(phive_booking_ajax.ajaxurl, data, function (response) {

      if (response?.status === 'success') {
        const { redirectUrl } = response;
        window.location.replace(redirectUrl);
      }
    })
  });

  // On click of deactivation, make the fields editable to configure new outlook credentials.
	$('#deactivate_outlook_authorization').on('click', function(e) {
		e.preventDefault();

    const data = {
      action: 'ph_deactivate_outlook_authorization'
    };

    $.post(phive_booking_ajax.ajaxurl, data, function (response) {

      if (response?.status === 'success') {
        location.reload();
      }
    })
	});

  if (jQuery('#outlook_event_filter').length > 0) {

    jQuery('#outlook_event_filter').select2();
  }
   
  if (jQuery('#google_cal_event_filter').length > 0) {
		
    jQuery('#google_cal_event_filter').select2();
  }
  
  // Fetch Outlook Calendars
  $('#ph-fetch-outlook-calendars').on('click', function (e) {

    e.preventDefault();

    let data = {
      action: 'ph_get_outlook_calendars',
    };

    $.post(phive_booking_ajax.ajaxurl, data, function (response) {

      if (response?.status === 'success') {

        location.reload();
      } else {

        alert(response?.message || 'An error occurred. Please try again.');
      }
    })
  });

  // Fetch Google Calendars
  $('#ph-fetch-google-calendars').on('click', function (e) {

    e.preventDefault();

    let data = {
      action: 'ph_get_google_calendars',
    };

    $.post(phive_booking_ajax.ajaxurl, data, function (response) {

      if (response?.status === 'success') {

        location.reload();
      } else {

        alert(response?.message || 'An error occurred. Please try again.');
      }
    })
  });
});