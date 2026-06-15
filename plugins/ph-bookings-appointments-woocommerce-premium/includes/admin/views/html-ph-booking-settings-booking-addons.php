<div>
    <?php 
        $plugin_url = plugins_url('', dirname( dirname( dirname(__FILE__) )) );
        $multiple_non_adjacent_addon = $plugin_url."/resources/images/WooCommerce-Bookings-Multiple-Non-Adjacent-Bookings.webp";
        $recurring_bookings = $plugin_url."/resources/images/Recurring-bookings.webp";
        $dokan_integration = $plugin_url."/resources/images/dokan-woocommerce-bookings-integration.webp";
    ?>
    <h2><?php _e('Booking Add-Ons','bookings-and-appointments-for-woocommerce');?></h2>
    <!-- <hr> -->
    <div class="all_addon_container">
        <div>
            <a href="https://www.pluginhive.com/product/woocommerce-multiple-non-adjacent-bookings/?utm_source=bookings&utm_medium=plugin_settings" target="_blank">
                <img src="<?php echo $multiple_non_adjacent_addon;?>" alt="">
                <p>WooCommerce Multiple Non-Adjacent Bookings</p>
            </a>
        </div>
        <div>
            <a href="https://www.pluginhive.com/product/woocommerce-recurring-bookings-and-appointments/?utm_source=bookings&utm_medium=plugin_settings" target="_blank">
                <img src="<?php echo $recurring_bookings;?>" alt="">
                <p>WooCommerce Recurring Bookings and Appointments</p>
            </a>
        </div>
        <div>
            <a href="https://www.pluginhive.com/product/dokan-woocommerce-bookings-integration/?utm_source=bookings&utm_medium=plugin_settings" target="_blank">
                <img src="<?php echo $dokan_integration;?>" alt="">
                <p>Dokan WooCommerce Bookings Integration</p>
            </a>
        </div>
    </div>
</div>
