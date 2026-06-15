(function ($) {

    function toggleParticipantSelect() {

        const $dropdown = $('#ph_participant_dropdown');

        if ($('.toggle-participant-rules').is(':checked')) {
            $dropdown.show();
        } else {
            $dropdown.hide();
        }
    }

    $(document).on('widget-added, widget-added widget-updated', function ($control, widget) {

        $('.ph_multi_select_participant').chosen({
            search_contains: true,
            no_results_text: "Nothing found for: ",
            width: "100%",
            allowClear: true
        });

        toggleParticipantSelect();
        // Toggle visibility of the dropdown based on the checkbox
        $('.toggle-participant-rules').on('change', function () {
            toggleParticipantSelect();
        });

        var id = $(widget).find('.widget_number').val();
        var timezone_enabled = $('#timezone_enabled').val();

        if (id != "__i__" && id != "") {

            if (timezone_enabled == 'yes') {

                $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time").attr("disabled", true);
                $('label[for="widget-ph_booking_search_widget-' + id + '-filter_date_and_time"]').attr("disabled", true);
                $('label[for="widget-ph_booking_search_widget-' + id + '-filter_date_and_time"]').css('opacity', '0.5');
                $("#widget-ph_booking_search_widget-" + id + "-from_time_text").closest('tr').hide();
                $("#widget-ph_booking_search_widget-" + id + "-to_time_text").closest('tr').hide();
                $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time_panel").hide();
                $('#hint').show();
            }
            else {
                $('#hint').hide();
            }

            // Title Checkbox
            $('#widget-ph_booking_search_widget-' + id + '-ph_display_title').click(function () {
                $("#widget-ph_booking_search_widget-" + id + "-title").val('');

            });

            if ($('#widget-ph_booking_search_widget-' + id + '-filter_date_and_time').is(':not(:checked)')) {
                $("#widget-ph_booking_search_widget-" + id + "-from_time_text").closest('tr').hide();
                $("#widget-ph_booking_search_widget-" + id + "-to_time_text").closest('tr').hide();
                $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time_panel").hide();
            }
            else {
                if (!($("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time").is('[disabled]'))) {
                    $("#widget-ph_booking_search_widget-" + id + "-from_time_text").closest('tr').show();
                    $("#widget-ph_booking_search_widget-" + id + "-to_time_text").closest('tr').show();
                    $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time_panel").show();
                }
            }

            if ($("#widget-ph_booking_search_widget-" + id + "-filter_asset_name").is(':not(:checked)')) {
                $("#widget-ph_booking_search_widget-" + id + "-filter_asset_name_label").closest('tr').hide();
            }
            else {
                $("#widget-ph_booking_search_widget-" + id + "-filter_asset_name_label").closest('tr').show();
            }

            if ($("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant").is(':not(:checked)')) {
                $("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant_label").closest('tr').hide();

            }
            else {
                $("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant_label").closest('tr').show();
            }

            $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time").click(function () {

                if ($(this).is(':not(:checked)')) {
                    $("#widget-ph_booking_search_widget-" + id + "-from_time_text").closest('tr').hide();
                    $("#widget-ph_booking_search_widget-" + id + "-to_time_text").closest('tr').hide();
                    $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time_panel").hide();
                }
                else {
                    if (!($("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time").is('[disabled]'))) {
                        $("#widget-ph_booking_search_widget-" + id + "-from_time_text").closest('tr').show();
                        $("#widget-ph_booking_search_widget-" + id + "-to_time_text").closest('tr').show();
                        $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time_panel").show();
                    }
                }
            });

            $("#widget-ph_booking_search_widget-" + id + "-filter_asset_name").click(function () {
                if ($(this).is(':not(:checked)')) {
                    $("#widget-ph_booking_search_widget-" + id + "-filter_asset_name_label").closest('tr').hide();
                }
                else {
                    $("#widget-ph_booking_search_widget-" + id + "-filter_asset_name_label").closest('tr').show();
                }
            });

            $("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant").click(function () {
                if ($(this).is(':not(:checked)')) {
                    $("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant_label").closest('tr').hide();
                }
                else {
                    $("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant_label").closest('tr').show();
                }
            });

            // Restrict checkin
            if ($('#widget-ph_booking_search_widget-' + id + '-restrict_checkin_checkbox').is(':not(:checked)')) {

                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkin_option").hide();
            } else {
                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkin_option").show();
            }
            $("#widget-ph_booking_search_widget-" + id + "-restrict_checkin_checkbox").on('click', function () {
                if ($(this).is(':not(:checked)')) {
                    $("#widget-ph_booking_search_widget-" + id + "-restrict_checkin_option").hide();
                }
                else {
                    $("#widget-ph_booking_search_widget-" + id + "-restrict_checkin_option").show();
                }
            });

            // Restrict checkout
            if ($('#widget-ph_booking_search_widget-' + id + '-restrict_checkout_checkbox').is(':not(:checked)')) {

                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkout_option").hide();
            } else {
                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkout_option").show();
            }
            $('#widget-ph_booking_search_widget-' + id + '-restrict_checkout_checkbox').click(function () {
                if ($(this).is(':not(:checked)')) {

                    $("#widget-ph_booking_search_widget-" + id + "-restrict_checkout_option").hide();
                } else {
                    $("#widget-ph_booking_search_widget-" + id + "-restrict_checkout_option").show();
                }
            })

            // Restrict widget
            if ($('#widget-ph_booking_search_widget-' + id + '-restrict_widget_checkbox').is(':not(:checked)')) {

                $("#widget-ph_booking_search_widget-" + id + "-restrict_widget_option").hide();
            } else {
                $("#widget-ph_booking_search_widget-" + id + "-restrict_widget_option").show();
            }
            $('#widget-ph_booking_search_widget-' + id + '-restrict_widget_checkbox').click(function () {
                if ($(this).is(':not(:checked)')) {

                    $("#widget-ph_booking_search_widget-" + id + "-restrict_widget_option").hide();
                } else {
                    $("#widget-ph_booking_search_widget-" + id + "-restrict_widget_option").show();
                }
            })

            // Hide clear button
            if ($('#widget-ph_booking_search_widget-' + id + '-disable_clear_button').is(':not(:checked)')) {

                $("#widget-ph_booking_search_widget-" + id + "-clear_text").closest('tr').show();
            } else {
                $("#widget-ph_booking_search_widget-" + id + "-clear_text").closest('tr').hide();
            }

            $('#widget-ph_booking_search_widget-' + id + '-disable_clear_button').click(function () {
                if ($(this).is(':not(:checked)')) {

                    $("#widget-ph_booking_search_widget-" + id + "-clear_text").closest('tr').show();
                } else {
                    $("#widget-ph_booking_search_widget-" + id + "-clear_text").closest('tr').hide();
                }
            })

            // Sho4w/Hide View Product Button Title.
            if($('#widget-ph_booking_search_widget-' + id + '-enable_view_product_button').is(':not(:checked)')) {
                $("#widget-ph_booking_search_widget-" + id + "-view_product_button_text").closest('p').hide();

            } else {
                $("#widget-ph_booking_search_widget-" + id + "-view_product_button_text").closest('p').show();
            }

            $('#widget-ph_booking_search_widget-' + id + '-enable_view_product_button').click(function() {
                if($(this).is(':not(:checked)')) {
                    $("#widget-ph_booking_search_widget-" + id + "-view_product_button_text").closest('p').hide();
                } else {
                    $("#widget-ph_booking_search_widget-" + id + "-view_product_button_text").closest('p').show();
                }
            });

            $("#widget-ph_booking_search_widget-" + id + "-clear_background_color").wheelColorPicker('format', 'css');
            $("#widget-ph_booking_search_widget-" + id + "-search_background_color").wheelColorPicker('format', 'css');
            $("#widget-ph_booking_search_widget-" + id + "-clear_text_color").wheelColorPicker('format', 'css');
            $("#widget-ph_booking_search_widget-" + id + "-search_text_color").wheelColorPicker('format', 'css');
            $("#widget-ph_booking_search_widget-" + id + "-border_color").wheelColorPicker('format', 'css');

            // Customize Buttons
            $('#widget-ph_booking_search_widget-' + id + '-customize_buttons').click(function () {
                $("#widget-ph_booking_search_widget-" + id + "-customize_buttons_panel").toggle();
            })
            // Customize Labels
            $('#widget-ph_booking_search_widget-' + id + '-customize_labels').click(function () {
                $("#widget-ph_booking_search_widget-" + id + "-customize_labels_panel").toggle();
            })

            // Display Settings
            $('#widget-ph_booking_search_widget-' + id + '-display_settings').click(function () {
                $("#widget-ph_booking_search_widget-" + id + "-display_settings_panel").toggle();
            })

            // Search Filters
            $('#widget-ph_booking_search_widget-' + id + '-search_filters').click(function () {
                $("#widget-ph_booking_search_widget-" + id + "-search_filters_panel").toggle();
            })

            // Reset To Defaults
            $("#widget-ph_booking_search_widget-" + id + "-reset_to_default").click(function (e) {

                if (!window.confirm(ph_booking_admin_widget_default.reset_to_default_confirm_text)) {
                    return false;
                }

                // Reset Title Text
                $("#widget-ph_booking_search_widget-" + id + "-title").val(ph_booking_admin_widget_default.title_text);

                // Reset restrict Checkin
                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkin_checkbox").prop('checked', false);
                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkin_option tr").each(function () {
                    $(this).find('input').prop('checked', false);
                })
                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkin_option").hide();

                // Reset restrict checkout
                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkout_checkbox").prop('checked', false);
                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkout_option tr").each(function () {
                    $(this).find('input').prop('checked', false);
                })
                $("#widget-ph_booking_search_widget-" + id + "-restrict_checkout_option").hide();

                // Reset restrict widget
                $("#widget-ph_booking_search_widget-" + id + "-restrict_widget_checkbox").prop('checked', false);
                $("#widget-ph_booking_search_widget-" + id + "-restrict_widget_option tr").each(function () {
                    $(this).find('input').prop('checked', false);
                })
                $("#widget-ph_booking_search_widget-" + id + "-restrict_widget_option").hide();

                // Reset range of date+time
                $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time").prop('checked', false);
                $("#widget-ph_booking_search_widget-" + id + "-filter_interval_time").val('');
                $("#widget-ph_booking_search_widget-" + id + "-filter_time_pick_from").val('');
                $("#widget-ph_booking_search_widget-" + id + "-filter_time_pick_to").val('');

                // Daily range of time and time format.
                $("#widget-ph_booking_search_widget-" + id + "-filter_daily_range_from").val(''); //filter_daily_range_from
                $("#widget-ph_booking_search_widget-" + id + "-filter_daily_range_to").val(''); //filter_daily_range_to
                $("#widget-ph_booking_search_widget-" + id + "-filter_time_format").val('time_24hr'); //filter_time_format

                $("#widget-ph_booking_search_widget-" + id + "-filter_date_and_time_panel").hide();

                $("#widget-ph_booking_search_widget-" + id + "-filter_asset_name").prop('checked', false);
                $("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant").prop('checked', false);
                $("#widget-ph_booking_search_widget-" + id + "-show_partially_unavailable").prop('checked', false);
                $("#widget-ph_booking_search_widget-" + id + "-disable_clear_button").prop('checked', false);
                $("#widget-ph_booking_search_widget-" + id + "-filter_date_format").val('yy-mm-dd');

                // Reset Customize Buttons
                $("#widget-ph_booking_search_widget-" + id + "-search_text").val(ph_booking_admin_widget_default.search_text);
                $("#widget-ph_booking_search_widget-" + id + "-search_class").val('');
                $("#widget-ph_booking_search_widget-" + id + "-search_background_color").val('#2271b1');
                $("#widget-ph_booking_search_widget-" + id + "-search_text_color").val('#ffffff');
                $("#widget-ph_booking_search_widget-" + id + "-clear_text").val(ph_booking_admin_widget_default.clear_text);
                $("#widget-ph_booking_search_widget-" + id + "-clear_class").val('');
                $("#widget-ph_booking_search_widget-" + id + "-clear_background_color").val('#2271b1');

                $("#widget-ph_booking_search_widget-" + id + "-border_width").val('1');
                $("#widget-ph_booking_search_widget-" + id + "-border_radius").val('1');
                $("#widget-ph_booking_search_widget-" + id + "-border_color").val('#8f8f8f');
                $("#widget-ph_booking_search_widget-" + id + "-border_style").val('solid');
             

                $("#widget-ph_booking_search_widget-" + id + "-clear_text_color").val('#ffffff');
                $("#widget-ph_booking_search_widget-" + id + "-clear_text").closest('tr').show();
                $("#widget-ph_booking_search_widget-" + id + "-customize_buttons_panel").hide();

                // Reset Customize Labels
                $("#widget-ph_booking_search_widget-" + id + "-from_date_text").val(ph_booking_admin_widget_default.from_date_text);
                $("#widget-ph_booking_search_widget-" + id + "-to_date_text").val(ph_booking_admin_widget_default.to_date_text);
                $("#widget-ph_booking_search_widget-" + id + "-from_time_text").val(ph_booking_admin_widget_default.from_time_text);
                $("#widget-ph_booking_search_widget-" + id + "-to_time_text").val(ph_booking_admin_widget_default.to_time_text);
                $("#widget-ph_booking_search_widget-" + id + "-filter_asset_name_label").val(ph_booking_admin_widget_default.filter_asset_name_label);
                $("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant_label").val(ph_booking_admin_widget_default.filter_number_of_participant_label);
                $("#widget-ph_booking_search_widget-" + id + "-from_time_text").closest('tr').hide();
                $("#widget-ph_booking_search_widget-" + id + "-to_time_text").closest('tr').hide();
                $("#widget-ph_booking_search_widget-" + id + "-filter_asset_name_label").closest('tr').hide();
                $("#widget-ph_booking_search_widget-" + id + "-filter_number_of_participant_label").closest('tr').hide();
                $("#widget-ph_booking_search_widget-" + id + "-customize_labels_panel").hide();

                $("#widget-ph_booking_search_widget-" + id + "-display_settings_panel").hide();
                $("#widget-ph_booking_search_widget-" + id + "-search_filters_panel").hide();

                $(this).trigger('change');
            })
            // Admin Styles
            $("#widget-ph_booking_search_widget-" + id + "-customize_buttons_panel th").css("padding", "0.4em 1.1em");
            $("#widget-ph_booking_search_widget-" + id + "-customize_buttons_panel td").css("padding", "0.4em 1.1em");
            $("#widget-ph_booking_search_widget-" + id + "-customize_buttons_panel").css({ "font-size": "0.8em", 'border-top': 'solid darkgrey 0.1em' });
            $("#widget-ph_booking_search_widget-" + id + "-customize_buttons").parent().css({ 'border': 'solid darkgrey 0.1em', 'border-radius': '0.3em' });
            $("#widget-ph_booking_search_widget-" + id + "-customize_buttons").css({ 'cursor': 'pointer', 'padding': '0.4em', 'margin': '0' });
            $("#widget-ph_booking_search_widget-" + id + "-customize_labels").parent().css({ 'border': 'solid darkgrey 0.1em', 'border-radius': '0.3em', 'margin-top': '0.6em' });
            $("#widget-ph_booking_search_widget-" + id + "-customize_labels").css({ 'cursor': 'pointer', 'padding': '0.4em', 'margin': '0' });
            $("#widget-ph_booking_search_widget-" + id + "-customize_labels_panel").css({ "font-size": "0.8em", 'border-top': 'solid darkgrey 0.1em', "width": "100%", "overflow-x": "scroll" });
            $("#widget-ph_booking_search_widget-" + id + "-customize_labels_panel th").css("padding", "0.4em 1.1em");
            $("#widget-ph_booking_search_widget-" + id + "-customize_labels_panel td").css("padding", "0.4em 1.1em");
            $("#widget-ph_booking_search_widget-" + id + "-display_settings").parent().css({ 'border': 'solid darkgrey 0.1em', 'border-radius': '0.3em', 'margin': '0.6em 0' });
            $("#widget-ph_booking_search_widget-" + id + "-display_settings").css({ 'cursor': 'pointer', 'padding': '0.4em', 'margin': '0' });
            $("#widget-ph_booking_search_widget-" + id + "-display_settings_panel").css({ 'border-top': 'solid darkgrey 0.1em', 'padding': '0.6em' });
            $("#widget-ph_booking_search_widget-" + id + "-search_filters").parent().css({ 'border': 'solid darkgrey 0.1em', 'border-radius': '0.3em', 'margin': '0.6em 0' });
            $("#widget-ph_booking_search_widget-" + id + "-search_filters").css({ 'cursor': 'pointer', 'padding': '0.4em', 'margin': '0' });
            $("#widget-ph_booking_search_widget-" + id + "-search_filters_panel").css({ 'border-top': 'solid darkgrey 0.1em', 'padding': '0.6em' }); 

            // Toggle visibility of the dropdown based on the checkbox
            $('.toggle-participant-rules').on('change', function () {
                var $dropdown = $('#ph_participant_dropdown');
                if ($(this).is(':checked')) {
                    $dropdown.show();
                } else {
                    $dropdown.hide();
                }
            });
        }
    });

})(jQuery);