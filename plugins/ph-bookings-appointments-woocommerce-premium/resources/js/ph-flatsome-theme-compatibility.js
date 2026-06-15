jQuery(document).ready(function () {

    // This code updates the price in Flatsome's Quick View for booking products
    jQuery(".quick-view").on("click", function() {
        let product_id = jQuery(this).attr("data-prod"); // Get product ID from the data attribute

        let ph_check_quick_view_exist = setInterval(function () {
            // Check if the quick view modal is visible
            if (jQuery(".product-quick-view-container").is(":visible")) {
                ph_update_booking_price_on_quick_view_load(product_id);
                clearInterval(ph_check_quick_view_exist); // Stop checking once loaded
            }
        }, 300);
    });

    /**
     * Fetches and updates the booking price for the product in Flatsome's Quick View.
     * 
     * @param {number} product_id - The ID of the product.
     */
    function ph_update_booking_price_on_quick_view_load(product_id) {
        let priceElement = jQuery(".product-info").find(".woocommerce-Price-amount.amount bdi"); // Get the price element

        // Get the currency symbol from the price HTML
        let ph_price_html = priceElement.html();
        let ph_currency_html = ph_price_html.match(/<span class="woocommerce-Price-currencySymbol">.*?<\/span>/)[0];

        let data = {
            action: 'phive_get_booking_price',
            product_id: product_id
        };

        // Default booking price (will be updated after AJAX call)
        let ph_booking_price = 0;

        // Make an AJAX request to fetch the booking price
        jQuery.post(
            ph_booking_flatsome_locale.ajaxurl,
            data,
            function (res) {
                let result = jQuery.parseJSON(res); // Parse the JSON response

                if (result.success) {
                    ph_booking_price = result.booking_price; // Get the updated booking price

                    // Update the price in the quick view modal
                    priceElement.html(ph_currency_html + ph_booking_price);
                }
            }
        );
    }
});