<div>
    <?php 
        $plugin_url = plugins_url('', dirname( dirname( dirname(__FILE__) )) );
        $product_addons = $plugin_url."/resources/images/PH-WooCommerce-Product-Addons-plugin.webp";
        $deposits_plugin = $plugin_url."/resources/images/WooCommerce-Deposits.webp";
    ?>
    <h2><?php _e('Integrations','bookings-and-appointments-for-woocommerce');?></h2>
    <!-- <hr> -->
    <div class="all_addon_container">
        <div>
            <a href="https://www.pluginhive.com/product/woocommerce-product-addons/?utm_source=bookings&utm_medium=plugin_settings" target="_blank">
                <img src="<?php echo $product_addons;?>" alt="">
                <p>PH WooCommerce Product Addons</p>
            </a>
        </div>
        <div>
            <a href="https://www.pluginhive.com/product/woocommerce-deposits/?utm_source=bookings&utm_medium=plugin_settings" target="_blank">
                <img src="<?php echo $deposits_plugin;?>" alt="">
                <p>PH WooCommerce Deposits</p>
            </a>
        </div>
    </div>
</div>
