jQuery(document).ready(function ($) {
    const params = new URLSearchParams(window.location.search);

    if ($('.participant_section').length && $('.wapf').length) {
        $('.wapf').prependTo('.participant_section');
    }
    if (params.get('page') !== 'wc-orders') {
        return;
    }
    const $addBtn = $('a.page-title-action');
    if ($addBtn.length) {
        $addBtn.text('Add new booking').attr('href', adminData.add_booking_url);
    }
    if (!params.has('id') || params.get('action') !== 'edit') {
        return;
    }
    const $heading = $('.woocommerce-order-data__heading');

    const $mmbr = $('input.input-person.shipping-price-related')
    const buttonHtml = `
        <div class="custom-order-action " style="margin-top:12px;text-align:right">
            <button type="submit" class="button save_order button-primary" name="save" value="Update" disabled>Save Changes</button>
            <button class="button button-secondary" name="cancel" value="Cancel" disabled>Cancel</button>
        </div>
    `;



    if ($heading.length) {
        const text = $heading.text();
        const orderId = text.match(/\d+/);
        if (orderId) {
            $heading.text('Booking Ref no: ' + orderId[0]);
        }
    }


    if ($mmbr.length) {
        $mmbr.val('');
    }
    if ($('#order_data').length) {
        $('#order_data').append(buttonHtml);
    }
});
jQuery(function ($) {

    // Tabs Filter
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

    // When "Edit billing address" clicked
    $('#order_data').on('click', '.edit_address', function () {
        $('.order_data_column.billing').addClass('phive-hide-default-billing');
        $('#phive-checkout-billing-admin').slideDown();
    });

    // When Cancel clicked
    $('#order_data').on('click', '.cancel_edit_address', function () {
        $('.order_data_column.billing').removeClass('phive-hide-default-billing');
        $('#phive-checkout-billing-admin').slideUp();
    });
    // =======================================================
    // SHARED POPUP LOGIC (Invoice & Confirmation)
    // =======================================================

    // 1. OPEN POPUP (Handles BOTH buttons)
    $('body').on('click', '.send_manual_invoice_btn:not(.no_pdf), .send_manual_confirmation:not(.no_pdf)', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const isInvoice = $btn.hasClass('send_manual_invoice_btn');

        // Determine Settings based on button clicked
        const actionSlug = isInvoice ? 'send_manual_invoice_pdf_email' : 'send_manual_booking_confirmation';
        const modalTitle = isInvoice ? 'Add Email Id To Send Invoice' : 'Add Email Id To Send Booking Confirmation';
        const btnText = isInvoice ? 'Send Invoice' : 'Send Confirmation';

        // Get Order ID
        let orderId = $btn.data('order-id');
        if (!orderId) {
            const urlParams = new URLSearchParams(window.location.search);
            orderId = urlParams.get('post') || urlParams.get('id');
        }

        if (!orderId) {
            alert('Error: Could not retrieve Order ID.');
            return;
        }

        // Configure Modal
        $('#phive_email_modal .phive-modal-header').text(modalTitle);
        $('#phive_send_email_confirm').text(btnText);

        // Pass Data to the Submit Button
        $('#phive_send_email_confirm').data('order-id', orderId);
        $('#phive_send_email_confirm').data('action-slug', actionSlug); // Store which AJAX action to run

        // Show Modal
        //$('#phive_email_input').val('');
        $('#phive_email_modal').fadeIn(200);
        $('#phive_email_input').focus();
    });

    $('body').on('click', '.send_manual_invoice_btn.no_pdf, .send_manual_confirmation.no_pdf:not(.receipt)', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const isInvoice = $btn.hasClass('send_manual_invoice_btn');

        // Determine Settings based on button clicked
        const actionSlug = isInvoice ? 'send_manual_email' : 'send_manual_confirmation';
        const modalTitle = isInvoice ? 'Add Email Id To Send Notification' : 'Add Email Id To Send Booking Confirmation';
        const btnText = isInvoice ? 'Send Email' : 'Send Confirmation Email';

        // Get Order ID
        let orderId = $btn.data('order-id');
        if (!orderId) {
            const urlParams = new URLSearchParams(window.location.search);
            orderId = urlParams.get('post') || urlParams.get('id');
        }

        if (!orderId) {
            alert('Error: Could not retrieve Order ID.');
            return;
        }

        // Configure Modal
        $('#phive_email_modal .phive-modal-header').text(modalTitle);
        $('#phive_send_email_confirm').text(btnText);

        // Pass Data to the Submit Button
        $('#phive_send_email_confirm').data('order-id', orderId);
        $('#phive_send_email_confirm').data('action-slug', actionSlug); // Store which AJAX action to run

        // Show Modal
        //$('#phive_email_input').val('');
        $('#phive_email_modal').fadeIn(200);
        $('#phive_email_input').focus();
    });

    $('body').on('click', '.send_manual_confirmation.no_pdf.receipt', function (e) {
        e.preventDefault();

        const $btn = $(this);

        // Determine Settings based on button clicked
        const actionSlug = 'send_receipt';
        const modalTitle = 'Add Email Id To Send Receipt';
        const btnText = 'Send Receipt';

        // Get Order ID
        let orderId = $btn.data('order-id');
        if (!orderId) {
            const urlParams = new URLSearchParams(window.location.search);
            orderId = urlParams.get('post') || urlParams.get('id');
        }

        if (!orderId) {
            alert('Error: Could not retrieve Order ID.');
            return;
        }

        // Configure Modal
        $('#phive_email_modal .phive-modal-header').text(modalTitle);
        $('#phive_send_email_confirm').text(btnText);

        // Pass Data to the Submit Button
        $('#phive_send_email_confirm').data('order-id', orderId);
        $('#phive_send_email_confirm').data('action-slug', actionSlug); // Store which AJAX action to run

        // Show Modal
        //$('#phive_email_input').val('');
        $('#phive_email_modal').fadeIn(200);
        $('#phive_email_input').focus();
    });

    // 2. CLOSE POPUP
    $('body').on('click', '#phive_cancel_email', function () {
        $('#phive_email_modal').fadeOut(200);
    });

    $(window).on('click', function (e) {
        if ($(e.target).is('#phive_email_modal')) {
            $('#phive_email_modal').fadeOut(200);
        }
    });

    // 3. SUBMIT POPUP
    $('body').on('click', '#phive_send_email_confirm', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const orderId = $btn.data('order-id');
        const actionSlug = $btn.data('action-slug'); // Retrieve the stored action
        const inputEmails = $('#phive_email_input').val();
        const $msg = $('#phive_action_msg');

        if (!inputEmails || inputEmails.trim() === "") {
            alert("Please enter at least one email address.");
            return;
        }

        $btn.text('Sending...').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: actionSlug, // Dynamic Action
                order_id: orderId,
                recipients: inputEmails
            },
            success: function (response) {
                $('#phive_email_modal').fadeOut(200);

                if (response.success) {
                    if ($msg.length) $msg.css('color', 'green').text(response.data.message);
                    alert(response.data.message);
                    location.reload();
                } else {
                    if ($msg.length) $msg.css('color', 'red').text('Error: ' + response.data.message);
                    alert('Error: ' + response.data.message);
                }
            },
            error: function () {
                $('#phive_email_modal').fadeOut(200);
                alert('System error. Please try again.');
            },
            complete: function () {
                $btn.prop('disabled', false); // Text will be reset on next open
            }
        });
    });
    // =======================================================
    // COUPON MANAGER (Meta Box)
    // =======================================================

    // 1. APPLY COUPON
    $('body').on('click', '#phive_apply_coupon_btn', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const orderId = $btn.data('order-id');
        const code = $('#phive_coupon_code').val();
        const $msg = $('#phive_coupon_msg');

        if (!code) {
            alert('Please enter a coupon code.');
            return;
        }

        $btn.text('...').prop('disabled', true);
        $msg.text('');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'phive_apply_order_coupon',
                order_id: orderId,
                coupon_code: code
            },
            success: function (response) {
                if (response.success) {
                    $msg.css('color', 'green').text(response.data.message);
                    // Reload page to update totals and list
                    location.reload();
                } else {
                    $msg.css('color', 'red').text(response.data.message);
                    $btn.text('Apply').prop('disabled', false);
                }
            },
            error: function () {
                alert('System error.');
                $btn.text('Apply').prop('disabled', false);
            }
        });
    });

    // 2. REMOVE COUPON
    $('body').on('click', '.phive_remove_coupon', function (e) {
        e.preventDefault();

        if (!confirm("Remove this coupon?")) return;

        const $link = $(this);
        const orderId = $link.data('order-id');
        const code = $link.data('code');
        const $msg = $('#phive_coupon_msg');

        $link.text('...'); // Loading indicator

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'phive_remove_order_coupon',
                order_id: orderId,
                coupon_code: code
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    $link.text('[x]');
                }
            },
            error: function () {
                alert('System error.');
                $link.text('[x]');
            }
        });
    });


    var $emailRow = $('table tr').has('input#email');
    var $emailRow2 = $('table tr').has('input#billing_company');
    var $targetRow = $('tr.acf-field.acf-field-text[data-name="phone"]');
    var $targetRow2 = $('tr.acf-field.acf-field-text[data-name="billing_gstin"]');

    if ($emailRow.length && $targetRow.length) {
        $targetRow.insertAfter($emailRow);
    }
    if ($emailRow2.length && $targetRow2.length) {
        $targetRow2.insertAfter($emailRow2);
    }
});

jQuery(function ($) {
    const params = new URLSearchParams(window.location.search);
    if (params.get('page') !== 'add-booking') {
        return;
    }
    const btn = '<a href="' + adminData.new_user + '?redirect=' + adminData.add_booking_url + '" class="button button-primary" style="margin-left:10px;">Add New User</a>';

    $('td:has(select#customer_id)').first().append(btn);



    const userId = params.get('user_id');

    if (!userId) return;

    const customerSelect = document.getElementById('customer_id');
    if (!customerSelect) return;

    // Set value
    customerSelect.value = userId;

    // Native change event
    customerSelect.dispatchEvent(new Event('change', { bubbles: true }));

    // Select2 / WC safety (late init)
    setTimeout(function () {
        customerSelect.value = userId;
        customerSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }, 300);
});



jQuery(document).ready(function ($) {

    var $triggeringToggle = null; // Store which switch triggered the popup

    // 1. OPEN POPUP ON BUTTON CLICK (Existing Logic)
    $(document).on('click', '.add_addons', function (e) {
        e.preventDefault();
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('post') || urlParams.get('id');

        if (!orderId) { alert('Order ID not found.'); return; }
        const $btn = $('button.add_addons');
        $btn.text('Loading...');

        $.ajax({
            url: ajaxurl, type: 'POST',
            data: { action: 'load_admin_addon_popup', order_id: orderId },
            success: function (response) {
                $btn.text('Add Addons');
                if (response.success) {
                    $('body').append(response.data);
                    $('#admin-extra-services-popup').fadeIn();
                    updateAdminPopupState();
                } else { alert('Error: ' + response.data); }
            },
            error: function () { $btn.text('Add Addons'); alert('System error.'); }
        });
    });

    // 2. CLOSE POPUP
    $(document).on('click', '#close-admin-popup', function () {
        $('#admin-extra-services-popup').fadeOut(function () { $(this).remove(); });
    });

    // ----------------------------------------------------
    // MEMBER COUNT POPUP LOGIC
    // ----------------------------------------------------

    // Helper: Show Overlay
    function showMemberInputOverlay() {
        var current = $('#admin_total_member').val();
        if (current == "0") current = "";
        $('#manual_member_input').val(current).focus();
        $('#admin-member-input-overlay').fadeIn(200).css('display', 'flex');
    }

    // Manual Edit Link Click
    $(document).on('click', '#edit_member_count_link', function (e) {
        e.preventDefault();
        $triggeringToggle = null; // No auto-toggle needed
        showMemberInputOverlay();
    });

    // Cancel Button
    $(document).on('click', '#cancel_member_count', function (e) {
        e.preventDefault();
        $('#admin-member-input-overlay').fadeOut(200);
        // Reset triggering toggle if canceled
        if ($triggeringToggle) {
            $triggeringToggle.prop('checked', false);
            $triggeringToggle = null;
        }
    });

    // "Set Count" Action (UPDATED)
    $(document).on('click', '#set_member_count_action', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var newCount = parseInt($('#manual_member_input').val());
        var orderId = $('#admin_order_id').val();

        if (!newCount || newCount < 1) {
            alert("Please enter at least 1 participant.");
            return;
        }

        // Visual Feedback
        $btn.text('Saving...').prop('disabled', true);

        // AJAX Call to save immediately
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_admin_member_count',
                order_id: orderId,
                count: newCount
            },
            success: function (response) {
                $btn.text('Set Count').prop('disabled', false);

                if (response.success) {
                    // 1. Update Hidden Field & UI
                    $('#admin_total_member').val(newCount);
                    $('#disp_mem_count').text(newCount);

                    // 2. Update all "Select All" badges (non-operators)
                    $('.slider.dynamic-max').text(newCount);

                    // 3. Close Overlay
                    $('#admin-member-input-overlay').fadeOut(200);

                    // 4. Handle Triggering Checkbox (if opened via toggle)
                    if ($triggeringToggle) {
                        $triggeringToggle.prop('checked', true).trigger('change');
                        $triggeringToggle = null;
                    } else {
                        // Just recalculate totals
                        updateAdminPopupState();
                    }
                } else {
                    alert('Error saving count: ' + response.data);
                }
            },
            error: function () {
                $btn.text('Set Count').prop('disabled', false);
                alert('System error saving participant count.');
            }
        });
    });

    // ----------------------------------------------------
    // CALCULATION LOGIC
    // ----------------------------------------------------

    function updateAdminRowTotal($row) {
        var parsedDataPrice = parseFloat($row.data('price'));
        var price = !isNaN(parsedDataPrice) ? parsedDataPrice : (parseFloat($row.find('#misc_price').val()) || 0);
        var qty = parseInt($row.find('.addon-qty').val()) || 0;
        var total = price * qty;
        $row.find('.addon-total').text('₹' + total.toFixed(2));
    }

    function updateAdminPopupState() {
        const mem = parseInt($('#admin_total_member').val() || 0, 10);

        $('.service-row').each(function () {
            var $row = $(this);
            var $qtyInput = $row.find('.addon-qty');
            var $checkbox = $row.find('.addon-all');
            var qty = parseInt($qtyInput.val()) || 0;

            // Operator exception
            var isOperator = $row.find('.slider').hasClass('operator');
            var targetMax = isOperator ? 1 : mem;

            updateAdminRowTotal($row);

            // Sync Checkbox
            if (qty > 0 && qty === targetMax && targetMax > 0) {
                $checkbox.prop('checked', true);
                //$qtyInput.prop('disabled', true).addClass('input-disabled');
            } else {
                $checkbox.prop('checked', false);
                //$qtyInput.prop('disabled', false).removeClass('input-disabled');
            }
        });
    }
    $(document).on('input change', 'input#misc_price,input#misc_name', function () {
        $(this).attr('value', $(this).val());
    });
    // Event: Qty Change
    $(document).on('input change', '.addon-qty', function () {
        var $row = $(this).closest('.service-row');
        var max = parseInt($(this).attr('max'));

        // If dynamic max (based on member), check member count
        var isOperator = $row.find('.slider').hasClass('operator');
        if (!isOperator) {
            var mem = parseInt($('#admin_total_member').val() || 0, 10);
            if (mem > 0) max = mem; // Enforce dynamic max if set
        }

        var val = parseInt($(this).val());
        //if (max && val > max) $(this).val(max);

        updateAdminRowTotal($row);
        $row.find('.addon-all').prop('checked', false);
    });

    // Event: "Select for All" Toggle
    $(document).on('change', '.addon-all', function (e) {
        var $checkbox = $(this);
        var $row = $checkbox.closest('.service-row');
        var $qtyInput = $row.find('.addon-qty');

        // Check member count
        var mem = parseInt($('#admin_total_member').val() || 0, 10);
        var isOperator = $row.find('.slider').hasClass('operator');

        // INTERCEPT: If Member Count is 0 and this is NOT an operator
        if (mem === 0 && !isOperator && $checkbox.is(':checked')) {
            // Uncheck immediately
            $checkbox.prop('checked', false);
            // Remember who triggered it
            $triggeringToggle = $checkbox;
            // Show popup
            showMemberInputOverlay();
            return;
        }

        var targetQty = isOperator ? 1 : mem;

        if ($checkbox.is(':checked')) {
            //$qtyInput.val(targetQty).prop('disabled', true).addClass('input-disabled');
            $qtyInput.val(targetQty);
        } else {
            //$qtyInput.val(0).prop('disabled', false).removeClass('input-disabled');
            $qtyInput.val(0);
        }
        updateAdminRowTotal($row);
    });

    // Save Logic (Same as before)
    $(document).on('click', '#admin-save-addons', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $btn.text('Updating...').prop('disabled', true);
        var orderId = $('#admin_order_id').val();
        var addons = [];
        var remarks = {};

        $('#admin-extra-services-popup .addon-qty').each(function () {
            addons.push({ id: $(this).data('product_id'), qty: parseInt($(this).val()) || 0 });
        });

        console.log(addons);
        //return false;

        $('#admin-extra-services-popup .addon-remark').each(function () {
            remarks[$(this).data('category-slug')] = $(this).val().trim();
        });

        $.ajax({
            url: ajaxurl, type: 'POST',
            data: { action: 'save_admin_order_addons', order_id: orderId, addons: addons, remarks: remarks },
            success: function (response) {
                if (response.success) { alert('Order updated!'); location.reload(); }
                else { alert('Error: ' + response.data); $btn.text('Add & Proceed').prop('disabled', false); }
            },
            error: function () { alert('Error.'); $btn.text('Add & Proceed').prop('disabled', false); }
        });
    });
});

jQuery(function ($) {

    const excludeIds = [1944, 1694];

    function insertHeading() {
        $('.phive-addon-heading').remove();
        let added = false;

        $('.woocommerce_order_items tr.item').each(function () {

            const productId = parseInt($(this).find('input[name*="[product_id]"]').val());

            if (!excludeIds.includes(productId) && !added) {

                $('<tr class="phive-addon-heading">' +
                    '<td style="background:#f6f7f7;"></td><td colspan="5" style="background:#f6f7f7;font-weight:600;padding:10px 12px;">' +
                    '<h3 style="margin:0"><a href="" class="add_addons">Add-ons</a></h3>' +
                    '</td></tr>'
                ).insertAfter($(this));

                added = true;
            }
        });
    }

    insertHeading();
    $(document.body).on('wc_order_items_reloaded', insertHeading);
});
jQuery(function ($) {

    const excludeIds = [1944, 1694];

    function processAddons() {

        $('.woocommerce_order_items .item').each(function () {

            const $row = $(this);
            const productId = parseInt($row.find('input[name*="[product_id]"]').val());

            // Add-on = not in exclude list
            if (!excludeIds.includes(productId)) {

                // Remove clickable link, keep text
                const $link = $row.find('.name a');

                if ($link.length) {
                    //$link.replaceWith($('<span>').text($link.text()));
                }
            }
        });
    }

    processAddons();
    $(document.body).on('wc_order_items_reloaded', processAddons);
});


/* ---------------------------------------------------------
   ADMIN UI: Show Full Item Price (Source from Hidden Input)
   --------------------------------------------------------- */
jQuery(document).ready(function ($) {

    function phive_show_full_item_price() {
        // Iterate through each line item row
        $('#woocommerce-order-items tr.item').each(function () {
            var $row = $(this);
            var $lineCostCell = $row.find('td.line_cost');
            var $viewDiv = $lineCostCell.find('.view');
            var $discountLabel = $viewDiv.find('.wc-order-item-discount');

            // Only proceed if this item actually has a discount applied
            if ($discountLabel.length > 0) {

                // 1. Find the hidden input that holds the "Before Discount" Subtotal
                // It is located in the .edit div sibling
                var $subtotalInput = $lineCostCell.find('.edit input.line_subtotal');
                var fullPrice = $subtotalInput.val(); // e.g., "25"

                // 2. Get the currency symbol from the current display to reuse it
                // e.g., <span class="woocommerce-Price-currencySymbol">₹</span>
                var $currentSymbol = $viewDiv.find('.woocommerce-Price-currencySymbol').first();
                var symbolHtml = $currentSymbol.length ? $currentSymbol[0].outerHTML : '₹';

                if (fullPrice) {
                    // 3. Construct the new HTML structure
                    // We recreate the standard WooCommerce price HTML structure with the Full Price
                    var newHtml = '<span class="woocommerce-Price-amount amount"><bdi>' + symbolHtml + fullPrice + '</bdi></span>';

                    // 4. Replace the "View" content entirely
                    // This effectively removes the discounted price and the "5 discount" text, replacing it with just "₹25"
                    $viewDiv.html(newHtml);
                }
            }
        });
    }

    // 1. Run immediately on page load
    phive_show_full_item_price();

    // 2. Run whenever WooCommerce updates the item table (e.g., adding items, applying coupons)
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.data && typeof settings.data === 'string' && settings.data.indexOf('action=woocommerce_load_order_items') !== -1) {
            setTimeout(phive_show_full_item_price, 200);
        }
    });

});
document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('.addons-group-table');
    if (table) {
        const headers = table.querySelectorAll('thead th');
        const tbody = table.querySelector('tbody');

        headers.forEach((header, index) => {
            header.addEventListener('click', () => {
                sortColumn(index);
            });
        });
    }


    function sortColumn(index) {
        const rows = Array.from(tbody.querySelectorAll('tr')); // Get only tbody rows
        const isAscending = headers[index].getAttribute('data-order') === 'asc';

        // Toggle sort order
        headers[index].setAttribute('data-order', isAscending ? 'desc' : 'asc');

        rows.sort((rowA, rowB) => {
            const cellA = rowA.children[index];
            const cellB = rowB.children[index];

            let valA = getCellValue(cellA);
            let valB = getCellValue(cellB);

            // Numeric/Currency Sort
            if (!isNaN(parseFloat(valA)) && !isNaN(parseFloat(valB))) {
                return isAscending ? valA - valB : valB - valA;
            }

            // Text Sort
            return isAscending
                ? valA.toString().localeCompare(valB)
                : valB.toString().localeCompare(valA);
        });

        // Append sorted rows back to the table
        rows.forEach(row => tbody.appendChild(row));
    }

    function getCellValue(cell) {
        if (!cell) return "";

        // Handle the Dropdown (Status column)
        const select = cell.querySelector('select');
        if (select) {
            return select.options[select.selectedIndex].text.toLowerCase();
        }

        // Handle Currency (remove symbols)
        let text = cell.innerText || cell.textContent;

        // specific check for currency symbol '₹' or commas
        if (text.includes('₹') || text.match(/[0-9]/)) {
            // Remove non-numeric chars except dot and minus
            let numeric = text.replace(/[^0-9.-]+/g, "");
            if (numeric) return parseFloat(numeric);
        }

        return text.trim().toLowerCase();
    }
});
jQuery(document).ready(function ($) {

    // PLUS CLICK → add new misc row
    $(document).on('click', '.plus', function (e) {
        e.preventDefault();

        const $serviceRow = $(this).closest('tr').prev('.service-row');
        const $dupRow = $(this).closest('tr');

        // Clone rows
        const $newDupRow = $dupRow.clone();
        const $newServiceRow = $serviceRow.clone();


        // Reset inputs
        $newServiceRow.find('input').each(function () {
            if (this.type === 'checkbox') {
                this.checked = false;
            } else {
                $(this).val('');
            }
        });

        $newServiceRow.find('.addon-total').text('₹0');

        // Insert after current pair
        $dupRow.after($newDupRow).after($newServiceRow);
    });

    // MINUS CLICK → remove nearest misc row pair
    $(document).on('click', '.minus', function (e) {
        e.preventDefault();

        const $dupRow = $(this).closest('tr');
        const $serviceRow = $dupRow.prev('.service-row');

        // Prevent removing last remaining row
        if ($('.service-row.misc-row').length <= 1) {
            return;
        }

        $serviceRow.remove();
        $dupRow.remove();
    });


});
jQuery(document).ready(function ($) {

    // 1. OPEN MODAL & FETCH COUPONS
    $(document).on('click', '#open_coupon_modal', function (e) {
        e.preventDefault();
        $('#coupon_modal').css('display', 'flex');

        const $list = $('#coupon_list_container');
        $list.html('<p style="padding:10px;">Loading coupons...</p>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'get_wc_coupons_list' },
            success: function (res) {
                if (res.success) {
                    let html = '<ul style="list-style:none; margin:0; padding:0;">';
                    res.data.forEach(function (c) {
                        html += `
                        <li style="border-bottom:1px solid #eee; padding:10px;">
                            <label style="display:block; cursor:pointer;">
                                <input type="radio" name="selected_addon_coupon" value="${c.code}">
                                <strong>${c.label}</strong>
                                <br><small>${c.desc}</small>
                            </label>
                        </li>`;
                    });
                    html += '</ul>';
                    $list.html(html);
                } else {
                    $list.html('<p style="color:red; padding:10px;">Error loading coupons.</p>');
                }
            }
        });
    });

    // 2. CLOSE MODAL
    $(document).on('click', '#close_coupon_modal', function () {
        $('#coupon_modal').hide();
    });

    // 3. SAVE SELECTED COUPON
    $(document).on('click', '#save_selected_coupon', function (e) {
        e.preventDefault();

        const code = $('input[name="selected_addon_coupon"]:checked').val();
        if (!code) {
            alert("Please select a coupon.");
            return;
        }

        const order_id = $('#btn-finalize-bill').data('order-id');
        const $btn = $(this);
        $btn.text('Applying...').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_addon_coupon',
                order_id: order_id,
                coupon_code: code
            },
            success: function (res) {
                if (res.success) {
                    // Update UI
                    $('#active_coupon_display').text(code);
                    $('#open_coupon_modal').text('Change Coupon');
                    $('#coupon_modal').hide();
                    alert(res.data.message);
                    location.reload();
                } else {
                    alert(res.data);
                }
                $btn.text('Apply Coupon').prop('disabled', false);
            }
        });
    });

});



/* SPLIT PAYMENT JS*/

jQuery(function ($) {

    function renderMembers(count) {
        // Preserve existing values
        let existing = {};
        $('#group-members .group-member').each(function (index) {
            let i = index + 2; // Payer 2+
            existing[i] = {
                name: $(this).find('input[name^="group_member_name"]').val(),
                email: $(this).find('input[name^="group_member_email"]').val(),
                phone: $(this).find('input[name^="group_member_phone"]').val(),
                company: $(this).find('input[name^="group_member_company"]').val(),
            };
        });

        let html = '';
        for (let i = 2; i <= count; i++) {
            html += `<div class="group-member" style="margin-bottom:12px;display: inline-block;">
                    <span>Party ${i}</span><span class="required" aria-hidden="true">*</span><br>
                    <p class="form-row form-row-first update_totals_on_changes thwcfd-required thwcfd-field-wrapper thwcfd-field-country validate-required" ><input type="text" name="group_member_name[${i}]" class="input-text group-member-name" placeholder="Name"  value="${existing[i]?.name || ''}" aria-required="true"></p>

                    <p class="form-row form-row-last update_totals_on_changes thwcfd-required thwcfd-field-wrapper thwcfd-field-country validate-required" ><input type="text" name="group_member_company[${i}]" class="input-text" placeholder="Company Name" value="${existing[i]?.company || ''}" aria-required="true"></p>

                    <p class="form-row form-row-first update_totals_on_changes thwcfd-required thwcfd-field-wrapper thwcfd-field-country validate-required" ><input type="text" name="group_member_phone[${i}]" class="input-text" placeholder="Whatsapp Number"  value="${existing[i]?.phone || ''}" aria-required="true"></p>

                    <p class="form-row form-row-last update_totals_on_changes thwcfd-required thwcfd-field-wrapper thwcfd-field-country validate-required" ><input type="email" name="group_member_email[${i}]" class="input-text" placeholder="Email"  value="${existing[i]?.email || ''}" aria-required="true"></p>
                </div>`;
        }

        if (count > 1) {
            $('#group-members').html(html);
        } else {
            $('#group-members').empty();
        }
    }
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
    function initGroupPaymentUI() {
        // Restore radio selection from localStorage
        let savedMode = localStorage.getItem('group_payment_mode');
        if (savedMode) {
            //$('input[name="group_payment_mode"][value="' + savedMode + '"]').prop('checked', true);
            $('input[name="group_payment_mode"][value="full"]').prop('checked', true);
        }

        // Show/hide fields based on selection
        if ($('input[name="group_payment_mode"]:checked').val() === 'group') {
            $('#group-payment-fields').show();
            $('.count-member').show();
            renderMembers($('#group_total_payers').val());
        } else {
            $('#group-payment-fields').hide();
            $('.count-member').hide();
        }

        // Handle radio change
        $('input[name="group_payment_mode"]').off('change').on('change', function () {
            totalValue();
            localStorage.setItem('group_payment_mode', $(this).val()); // save
            if ($(this).val() === 'group') {
                $('#group-payment-fields').slideDown(200);
                $('.count-member').show();
                renderMembers($('#group_total_payers').val());
            } else {
                $('#group-payment-fields').slideUp(200);
                $('.count-member').hide();
                $('#group-members').empty();
            }
        });

        // Handle total payers change
        $(document).off('input change', '#group_total_payers').on('input change', '#group_total_payers', function () {
            updateSharedAmount();
            totalValue();
            let count = parseInt($(this).val());
            if (count >= 2 && count <= 10) {
                renderMembers(count);
            }
        });
        jQuery('input[type="email"]').each(function () {
            validateEmailField(this);
        });
    }

    function updateSharedAmount() {
        let totalText = $('.order-total .woocommerce-Price-amount bdi').text().replace(/[^\d.]/g, '');
        let total = parseFloat(totalText);
        let payers = parseInt($('#group_total_payers').val());

        if (isNaN(total) || isNaN(payers) || payers <= 0) {
            $('.shared-amount').text('');
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
        $('.shared-amount').html(`${formattedShare}`);
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
        let totalText = $('.order-total .woocommerce-Price-amount bdi').text().replace(/[^\d.]/g, '');
        let total = parseFloat(totalText);
        let payers = parseInt($('#group_total_payers').val());

        if (isNaN(total) || isNaN(payers) || payers <= 0) {
            $('.shared-amount').text('');
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

        $('.shared-amount').html(`${formattedShare}`);
        function getSelectedPaymentMode() {
            for (const radio of radios) {
                if (radio.checked) {
                    return radio.value;
                }
            }
            return null; // none selected (should not happen if one is checked by default)
        }
        if (getSelectedPaymentMode() == 'full') {
            $('button#place_order').html(`Confirm & Pay ${formattedTotal}`);
        } else {
            $('button#place_order').html(`Confirm & Pay ${formattedShare}`);
        }
    }
    totalValue();
    $(document.body).on('updated_checkout', function () {
        totalValue();
    });
    $(document).on('click', '.edit_date', function (e) {
        e.preventDefault();
        $('p.form-field.form-field-wide:has(input[name="order_date"]').toggleClass('show');
    });
    jQuery(".time-picker").after('<div class="discount-area"><ul class="disc-point"><li><b>10% off</b> on booking 2 slots.</li><li><b>15% off</b> on booking 3 slots.</li></ul></div>');

});

jQuery(document).ready(function ($) {

    // Sirf wahi specific fields jo aapne diye hain (baaki poore page ko ignore karega)
    var specificFieldsToTrack = [
        '#customer_user',                    // Customer dropdown
        'input[name="order_date"]',          // Created on Date
        'input[name="order_date_hour"]',     // Created on Hour
        'input[name="order_date_minute"]',   // Created on Minute
        '#phive-checkout-billing-admin input', // Billing details ke text/tel/email inputs
        '#phive-checkout-billing-admin select' // Billing details ke dropdowns (Country/State)
    ].join(', ');

    // 1. Page Load hote hi SIRF in specific fields ki "Initial Value" save kar lein
    $(specificFieldsToTrack).each(function () {
        var initialVal = $(this).val();
        if (initialVal == null) initialVal = '';
        $(this).data('initial-value', initialVal);
    });

    // 2. Check function jo SIRF in tracked fields me change dekhega
    function checkSpecificFieldsChanges() {
        var isChanged = false;

        $(specificFieldsToTrack).each(function () {
            var initialValue = $(this).data('initial-value');
            var currentValue = $(this).val();

            if (initialValue == null) initialValue = '';
            if (currentValue == null) currentValue = '';

            if (Array.isArray(initialValue)) initialValue = initialValue.join(',');
            if (Array.isArray(currentValue)) currentValue = currentValue.join(',');

            // Agar in selected fields me se ek bhi change hui hai toh isChanged = true ho jayega
            if (initialValue !== currentValue) {
                isChanged = true;
                return false; // loop break
            }
        });

        // Agar in specific fields me change hai toh buttons Enable karo
        if (isChanged) {
            $('.custom-order-action button').prop('disabled', false);
        } else {
            $('.custom-order-action button').prop('disabled', true);
        }
    }

    // 3. Track events on these specific fields
    $(document).on('change input', specificFieldsToTrack, function () {
        var $field = $(this);
        var initialValue = $field.data('initial-value');
        var currentValue = $field.val();

        if (initialValue == null) initialValue = '';
        if (currentValue == null) currentValue = '';
        if (Array.isArray(initialValue)) initialValue = initialValue.join(',');
        if (Array.isArray(currentValue)) currentValue = currentValue.join(',');

        // Optional: Changed field ko highlight karna (Red border)
        if (initialValue !== currentValue) {
            if ($field.is('select')) {
                $field.next('.select2-container').css('border', '1px solid #2271b1');
            } else {
                $field.css('border', '1px solid #2271b1');
            }
        } else {
            if ($field.is('select')) {
                $field.next('.select2-container').css('border', '');
            } else {
                $field.css('border', '');
            }
        }

        // Action: Buttons ka status check karo
        checkSpecificFieldsChanges();
    });

    // Select2 (Customer, Country, State) dropdowns ke liye special event
    $('body').on('select2:select', '#customer_user, #billing_country, #billing_state', function (e) {
        $(this).trigger('change');
    });

    // 4. Cancel Button Functionality (Reset ONLY these specific fields)
    $(document).on('click', '.custom-order-action button[name="cancel"]', function (e) {
        e.preventDefault(); // Page refresh roko

        // Sirf tracked fields ko unki original state me waapas le aao
        $(specificFieldsToTrack).each(function () {
            var initialVal = $(this).data('initial-value');
            $(this).val(initialVal);

            // Highlight (Red border) hatao
            if ($(this).is('select')) {
                $(this).trigger('change.select2'); // Select2 UI ko update karo
                $(this).next('.select2-container').css('border', '');
            } else {
                $(this).css('border', '');
            }
        });

        // Form wapas original ho gaya, isliye buttons disable kar do
        $('.custom-order-action button').prop('disabled', true);
    });

});

jQuery(document).ready(function ($) {

    // Status Dropdown aur Update Button ko select karein
    var $statusDropdown = $('#phive_custom_order_status');
    var $updateStatusBtn = $('#phive_update_status_btn');

    // 1. Page load hone par Dropdown ki initial value save karein
    if ($statusDropdown.length) {
        $statusDropdown.data('initial-value', $statusDropdown.val());
    }

    // 2. Dropdown me change hone par track karein
    $statusDropdown.on('change', function () {
        var initialValue = $(this).data('initial-value');
        var currentValue = $(this).val();

        // Agar value change hui hai
        if (initialValue !== currentValue) {
            // Button Enable karein aur optional red border lagayein
            $updateStatusBtn.prop('disabled', false);
            $(this).css('border', '1px solid #2271b1');
        } else {
            // Agar wapas purani value select kar li, toh Button Disable karein
            $updateStatusBtn.prop('disabled', true);
            $(this).css('border', '');
        }
    });

    // Optional: Agar button click hone par value save karni hai, toh use yahan update kar dein
    $updateStatusBtn.on('click', function () {
        // Nayi value ko ab "initial" set kar dein kyunki save ho chuka hai
        $statusDropdown.data('initial-value', $statusDropdown.val());

        // Button wapas disable aur border normal kar dein
        $(this).prop('disabled', true);
        $statusDropdown.css('border', '');

        // Yahan aap apna AJAX call ya jo bhi save logic hai wo add kar sakte hain
    });

});

jQuery(document).ready(function ($) {

    // 1. Payment Status Box elements ko select karein
    var $paymentBox = $('#phive_payment_status_box');
    var $paymentStatus = $paymentBox.find('select[name="phive_manual_payment_status"]');
    var $paymentModeRadios = $paymentBox.find('input[name="payment_mode"]');
    var $paymentUpdateBtn = $paymentBox.find('button.save_order[name="save"]'); // Sirf is box ka update button

    // 2. Page load par unki initial values store karein
    var initialPaymentStatus = $paymentStatus.val() || '';
    var initialPaymentMode = $paymentModeRadios.filter(':checked').val() || '';

    // Button ko load hone par directly disable karein (kyunki koi change nahi hua hai)
    $paymentUpdateBtn.prop('disabled', true);

    // 3. Function jo check karega ki status ya mode me koi change hua hai ya nahi
    function checkPaymentBoxChanges() {
        var currentStatus = $paymentStatus.val() || '';
        var currentMode = $paymentModeRadios.filter(':checked').val() || '';

        var isChanged = false;

        // Check Status Dropdown
        if (currentStatus !== initialPaymentStatus) {
            isChanged = true;
            $paymentStatus.css('border', '1px solid #2271b1'); // Red border
        } else {
            $paymentStatus.css('border', '');
        }

        // Check Radio Buttons
        if (currentMode !== initialPaymentMode) {
            isChanged = true;
            $paymentModeRadios.closest('p.mode').css('color', '#2271b1'); // Red text for radio options
        } else {
            $paymentModeRadios.closest('p.mode').css('color', '');
        }

        // Enable / Disable Update Button
        if (isChanged) {
            $paymentUpdateBtn.prop('disabled', false);
        } else {
            $paymentUpdateBtn.prop('disabled', true);
        }
    }

    // 4. Change events ko track karein
    $paymentStatus.on('change', function () {
        checkPaymentBoxChanges();
    });

    $paymentModeRadios.on('change', function () {
        checkPaymentBoxChanges();
    });

    // Optional: Agar button click default form submit rok kar AJAX se chalta hai
    // toh click hone par nayi values ko initial value bana dein
    $paymentUpdateBtn.on('click', function () {
        initialPaymentStatus = $paymentStatus.val() || '';
        initialPaymentMode = $paymentModeRadios.filter(':checked').val() || '';

        // Timeout taki form submit properly ho sake (agar wo normal submit hai)
        setTimeout(function () {
            checkPaymentBoxChanges(); // Isse button wapas disable aur border hat jayega
        }, 100);
    });
    //$('.role-service_manager #wpbody div#post-body button.edit-case-details').prop('disabled', true);
    //$('.role-service_manager div#post-body input').prop('disabled', true); 
    $('.role-service_manager #wpbody div#post-body select#phive_custom_order_status').prop('disabled', true);
    $('.role-service_manager #wpbody div#post-body div#woocommerce-order-data select').prop('disabled', true);
    jQuery('table.display_meta tr').filter(function () {
        return jQuery(this).text().includes('Booking Status:');
    }).remove();

    jQuery(document).on('click', '.case-edit-popup:not(:has(form)) #close-popup', function (e) {
        e.preventDefault();
        jQuery(".case-edit-popup").hide();
    });


    function formatBookingDate(dateString) {
        var date = new Date(dateString);

        var day = date.getDate();
        var month = date.toLocaleString('default', { month: 'long' });
        var year = date.getFullYear();

        var hours = date.getHours();
        var minutes = date.getMinutes().toString().padStart(2, '0');
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12;

        return {
            fullDate: day + ' ' + month + ', ' + year,
            time: hours + ':' + minutes + ' ' + ampm
        };
    }

    var bookedFromText = $('.display_meta th:contains("Booked From:")')
        .closest('tr')
        .find('td p')
        .text()
        .trim();

    var bookedToText = $('.display_meta th:contains("Booked To:")')
        .closest('tr')
        .find('td p')
        .text()
        .trim();

    if (bookedFromText && bookedToText) {

        var from = formatBookingDate(bookedFromText);
        var to = formatBookingDate(bookedToText);

        var bookingText = ' ( ' + from.fullDate + ' ' + from.time + ' to ' + to.time + ' )';

        $('.woocommerce-order-data__heading').append(
            ' <span> ' + bookingText + '</span>'
        );

        // var orderItemsElem = $('.role-service_manager div#woocommerce-order-items');
        // if (orderItemsElem.length) {
        //     var existingContent = window.getComputedStyle(orderItemsElem[0], '::before').getPropertyValue('content');
        //     var cleanContent = (existingContent && existingContent !== 'none') ? existingContent.replace(/['"]/g, '') : 'Booking Ref no: 35868';

        //     $('<style>')
        //         .prop('type', 'text/css')
        //         .html('.role-service_manager div#woocommerce-order-items::before { content: "' + cleanContent + ' ' + bookingText + '" !important; }')
        //         .appendTo('head');
        // }
    }

    $(document).on('click', 'button#toggle-addon', function (e) {
        e.preventDefault();
        $('.addon-selection-area').toggle();
    });

});
jQuery(document).ready(function ($) {
    // Find every table row that has the 'main-order' class
    $('tr.main-order').each(function () {
        var $row = $(this);

        // Extract the Order ID from the row's ID attribute (e.g., 'post-123' or 'order-123')
        var rowIdStr = $row.attr('id') || '';
        var orderId = rowIdStr.replace(/[^0-9]/g, '');

        // Fallback: if the row ID doesn't have a number, grab it from the checkbox
        if (!orderId) {
            orderId = $row.find('.check-column input[type="checkbox"]').val();
        }

        if (orderId) {
            // Duplicate the row
            var $clonedRow = $row.clone();

            // Add the requested custom class
            $clonedRow.find('.phive-toggle-children').remove(); // Remove the toggle button from the clone
            $clonedRow.find('.column-order_number').prepend('<span class="phive-tree-indicator" style="color:#a7aaad; margin-right:6px; font-size:16px; font-weight:bold;">↳</span>'); // Add [Reorder] before order number
            $clonedRow.find('.phive-parent-group').remove();
            $clonedRow.addClass('order-type-child');
            $clonedRow.addClass('split-order');
            $clonedRow.addClass('phive-reordered-child');
            $clonedRow.addClass('phive-child-of-' + orderId);
            $clonedRow.addClass('parent-' + orderId);

            // Optional but recommended: Remove 'main-order' from the clone 
            // so it isn't treated as a parent row itself
            $clonedRow.removeClass('main-order');
            $clonedRow.removeClass('order-type-parent');

            // Insert the duplicated row immediately after the original row
            $clonedRow.insertAfter($row);
        }
    });

    var addonGroups = {};

    $('tr.addon-order').each(function () {
        var $row = $(this);

        // Find the parent ID from the hidden div added via PHP
        var parentId = $row.find('.phive-is-addon').data('parent-id');

        if (parentId) {
            if (!addonGroups[parentId]) {
                addonGroups[parentId] = {
                    rows: [],
                    total: 0,
                    currencySymbol: '',
                    currencyPosition: 'left' // Default
                };
            }

            // Extract price from the order_total column
            var $amountElem = $row.find('.column-order_total .woocommerce-Price-amount bdi').last();
            if ($amountElem.length) {
                // Get currency symbol
                var symbol = $amountElem.find('.woocommerce-Price-currencySymbol').text();
                if (symbol && !addonGroups[parentId].currencySymbol) {
                    addonGroups[parentId].currencySymbol = symbol;
                    // Check if symbol is positioned on the left or right
                    if ($amountElem.text().trim().indexOf(symbol) === 0) {
                        addonGroups[parentId].currencyPosition = 'left';
                    } else {
                        addonGroups[parentId].currencyPosition = 'right';
                    }
                }

                // Extract the raw number
                var rawText = $amountElem.text().replace(symbol, '').trim();
                var cleanNumber = rawText.replace(/,/g, ''); // Remove comma separators for math
                var val = parseFloat(cleanNumber);

                if (!isNaN(val)) {
                    addonGroups[parentId].total += val;
                }
            }

            addonGroups[parentId].rows.push($row);

            // Hide the individual addon row
            $row.hide();
        }
    });

    // Create and insert the combined addon row
    $.each(addonGroups, function (parentId, group) {
        if (group.rows.length > 0) {
            // Use the first addon row as a template
            var $templateRow = group.rows[0].clone();

            // Clean up specific order IDs and classes
            $templateRow.attr('id', 'combined-addons-' + parentId);
            $templateRow.removeClass('addon-order type-shop_order status-completed status-processing');
            $templateRow.addClass('combined-addon-row phive-child-of-' + parentId);
            $templateRow.show(); // Ensure it is visible

            // Update Order Number Column text
            $templateRow.find('.column-order_number').html(
                '<span class="phive-tree-indicator" style="color:#a7aaad; margin-right:6px; font-size:16px; font-weight:bold;">↳</span>' +
                '<strong>Meeting Add-ons</strong>'
            );

            // Update Order Total Column with the combined sum
            var formattedTotal = group.total.toFixed(2);
            var priceHtml = '<span class="woocommerce-Price-amount amount"><bdi>';
            if (group.currencyPosition === 'left') {
                priceHtml += '<span class="woocommerce-Price-currencySymbol">' + group.currencySymbol + '</span>' + formattedTotal;
            } else {
                priceHtml += formattedTotal + '<span class="woocommerce-Price-currencySymbol">' + group.currencySymbol + '</span>';
            }
            priceHtml += '</bdi></span>';

            $templateRow.find('.column-order_total').html(priceHtml);

            // Clear out other columns (like status, date, etc.) to keep the UI looking clean
            $templateRow.find('td').not('.column-order_number, .column-order_total, .check-column').html('-');
            $templateRow.find('.check-column').html(''); // Remove checkbox

            // Determine where to insert it (after the parent, or after the duplicated main-order row)
            var $parentRow = $('#order-' + parentId + ', #post-' + parentId);
            if ($parentRow.length) {
                var $duplicateParent = $('.phive-child-of-' + parentId + ':not(.combined-addon-row)').last();
                if ($duplicateParent.length) {
                    $templateRow.insertAfter($duplicateParent);
                } else {
                    $templateRow.insertAfter($parentRow);
                }
            } else {
                // If parent isn't on the current page, place it where the last addon row was
                $templateRow.insertAfter(group.rows[group.rows.length - 1]);
            }

        }
    });
});
jQuery(document).ready(function () {
    jQuery('button#view-bill').on('click', function (e) {
        e.preventDefault();
        jQuery('#meeting-addons-table-container .wp-list-table').toggle();
        if (jQuery(this).text() == 'View Details') {
            jQuery(this).text('Hide Details');
        } else {
            jQuery(this).text('View Details');
        }
    });
    jQuery('#send_user_notification').attr('name', '').attr('id', '');
});




jQuery(document).ready(function ($) {
    if (new URLSearchParams(window.location.search).get('page') === 'add-booking') {
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



                    if (times.length == 2) {
                        var final = amount - tenpercent;
                        final = final.toLocaleString();

                        //$('<p class="full-day-msg" style="margin-top:0px;margin-bottom:10px;text-align:center">Booking for full day</p>').insertBefore($calendar);
                        html += `<p class="dis" style="">
                        10% bulk discount <span style="font-style:normal;">- ₹${tenpercents}</span>
                     </p>`;
                    }
                    if (times.length == 3) {
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


        // jQuery('.extra-resources.participant_section').appendTo('.wapf .wapf-wrapper');
        // jQuery('.extra-resources.resources_section').insertAfter('.wapf');
        // jQuery('.resources-wraper').append(`
        //         <div>
        //             <div class="persons-title">
        //                 <label class="label-resources">Admin Fee</label>
        //             </div>
        //             <div class="person-value" style="display: inline;">
        //                 <input type="text" value="" class="phive_book_resources admin-fee" name="admin-fee">
        //             </div>
        //         </div>
        //     `);
        jQuery('.extra-resources.resources_section').appendTo('.time-picker-wraper');
        jQuery('.label-person').each(function () {
            let html = jQuery(this).html();
            html = html.replace('*', '<abbr class="required" title="required">*</abbr>');
            jQuery(this).html(html);
        });

    }
});