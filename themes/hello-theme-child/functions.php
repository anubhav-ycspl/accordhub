<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
// Load custom functions
require_once get_stylesheet_directory() . '/custom_functions.php';
require_once get_stylesheet_directory() . '/includes/webapp-functions.php';
require_once get_stylesheet_directory() . '/libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

define('HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0');

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles()
{

    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css?' . time(),
        [
            'hello-elementor-theme-style',
        ],
        HELLO_ELEMENTOR_CHILD_VERSION
    );
    wp_enqueue_style(
        'hello-elementor-custom-style',
        get_stylesheet_directory_uri() . '/custom.css?' . time(),
        array(),
        null
    );
    wp_enqueue_style(
        'hello-elementor-site-style',
        get_stylesheet_directory_uri() . '/site.css?' . time(),
        array(),
        null
    );

}
add_action('wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20);

add_action('admin_enqueue_scripts', 'load_admin_global_assets');
function load_admin_global_assets()
{

    // CSS
    wp_enqueue_style(
        'custom-admin-style',
        get_stylesheet_directory_uri() . '/admin.css',
        [],
        time()
    );

    // JS
    wp_enqueue_script(
        'custom-admin-script',
        get_stylesheet_directory_uri() . '/js/admin.js',
        ['jquery'], // dependency (optional)
        time(),
        true // load in footer
    );

    wp_localize_script('custom-admin-script', 'adminData', [
        'add_booking_url' => admin_url('admin.php?page=add-booking'),
        'nonce' => wp_create_nonce('phive_admin_email_nonce'),
        'new_user' => admin_url('user-new.php')
    ]);
}

function enqueue_owl_carousel_links()
{
    if (is_front_page()) {
        return;
    }
    // Owl Carousel CSS files
    wp_enqueue_style('owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', array(), '2.3.4');
    wp_enqueue_style('owl-carousel-theme', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', array(), '2.3.4');

    // Owl Carousel JS file
    wp_enqueue_script('owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array('jquery'), '2.3.4', true);
}
add_action('wp_enqueue_scripts', 'enqueue_owl_carousel_links');

// Enqueue custom.js in child theme
function enqueue_child_theme_scripts()
{
    // Make sure jQuery is loaded
    wp_enqueue_script('jquery');
    $cancel_options = get_option('wc_booking_cancel_options', []);


    wp_enqueue_script(
        'custom-js',
        get_stylesheet_directory_uri() . '/js/custom.js',
        array('jquery'), // dependencies
        filemtime(get_stylesheet_directory() . '/js/custom.js'), // version = last modified
        true // load in footer
    );

    wp_localize_script('custom-js', 'phive_spinner', [
        'spinner_url' => get_stylesheet_directory_uri() . '/images/spinner.gif',
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-reg-nonce'),
        'nonce_verify' => wp_create_nonce('ajax-reg-verify-nonce'),
        'redirecturl' => home_url(),
        'loadingmessage' => 'Please wait...',
        'checkout' => wc_get_page_permalink('checkout'),
        'rooms' => wc_get_page_permalink('shop'),
        'home_link' => get_bloginfo('url')
    ]);

    wp_localize_script('custom-js', 'WCCancelOptions', [
        'before_72_charge' => $cancel_options['before_72_charge'] ?? '',
        'within_72_charge' => $cancel_options['within_72_charge'] ?? '',
        'popup_before_72' => $cancel_options['popup_before_72'] ?? '',
        'popup_within_72' => $cancel_options['popup_within_72'] ?? '',
        'info_message' => $cancel_options['info_message'] ?? '',
        'cancel_label' => $cancel_options['cancel_label'] ?? 'Cancel Booking',
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_child_theme_scripts');



//add_action( 'woocommerce_cart_actions', 'add_custom_button_cart_page' );
function add_custom_button_cart_page()
{
    echo '<a href="' . esc_url(home_url('/custom-page')) . '" class="checkout-button button alt wc-forward">GO TO CAFE</a>';
}

//add_action('woocommerce_proceed_to_checkout', 'add_custom_button_inside_checkout_div', 20);
function add_custom_button_inside_checkout_div()
{
    echo '<a href="' . esc_url(home_url('/custom-page')) . '" class="checkout-button button alt wc-forward" style="margin-top:10px;">GO TO CAFE</a>';
}

//add_action('woocommerce_review_order_after_submit', 'add_custom_button_checkout_page');
function add_custom_button_checkout_page()
{
    echo '<a href="" class="button checkout-button cafe-btn" id="place_order" style="margin-top:10px;display:block;text-align:center;">GO TO CAFE</a>';
}




// 1. Add new "My Bookings" tab in My Account menu
//add_filter('woocommerce_account_menu_items', 'add_my_bookings_tab', 40);
function add_my_bookings_tab($menu_links)
{
    // Insert after Orders or where you want
    $new = array('my-bookings' => __('My Bookings', 'your-textdomain'));

    // Add after "Orders"
    $menu_links = array_slice($menu_links, 0, 1, true)
        + $new
        + array_slice($menu_links, 1, NULL, true);

    return $menu_links;
}

// 2. Register the endpoint
//add_action('init', 'register_my_bookings_endpoint');
function register_my_bookings_endpoint()
{
    add_rewrite_endpoint('my-bookings', EP_PAGES);
}



function custom_booking_details_view($booking_id)
{

    $booking_item_id = $booking_id;

    // Get the order ID from the order item
    $order_id = wc_get_order_id_by_order_item_id($booking_item_id);

    if (!$order_id) {
        echo '<p>Invalid booking ID.</p>';
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order || $order->get_user_id() != get_current_user_id()) {
        echo '<p>You are not allowed to view this booking.</p>';
        return;
    }

    // Get the order item
    $order_item = $order->get_item($booking_item_id);
    if (!$order_item) {
        echo '<p>Booking not found.</p>';
        return;
    }

    // ✅ Display booking details styled like WooCommerce
    echo '<div class="woocommerce">';
    echo '<h2>' . __('Booking Details', 'textdomain') . '</h2>';
    echo '<table class="shop_table shop_table_responsive bookings-table">';
    echo '<tbody>';

    echo '<tr><th>' . __('Booking ID', 'textdomain') . '</th><td data-title="Booking ID">' . $booking_item_id . '</td></tr>';
    echo '<tr><th>' . __('Product', 'textdomain') . '</th><td data-title="Product">' . $order_item->get_name() . '</td></tr>';
    echo '<tr><th>' . __('Quantity', 'textdomain') . '</th><td data-title="Quantity">' . $order_item->get_quantity() . '</td></tr>';
    echo '<tr><th>' . __('Booking Cost', 'textdomain') . '</th><td data-title="Booking Cost">' . wc_price($order_item->get_total()) . '</td></tr>';

    // If there are custom meta fields for the booking
    foreach ($order_item->get_formatted_meta_data() as $meta) {
        if (preg_match('/\d+[L|C|c]r/i', $meta->display_key)) {
            echo '<tr><th>Settlement Amount</th><td data-title="Settlement Amount">' . $meta->display_key . '</td></tr>';
        } else {
            echo '<tr><th>' . esc_html($meta->display_key) . '</th><td data-title="' . esc_html($meta->display_key) . '">' . $meta->display_value . '</td></tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';

    echo '<p><a href="' . esc_url(wc_get_account_endpoint_url('dashboard')) . '" class="woocommerce-button button view-booking wc-backward">' . __('Back to Bookings', 'textdomain') . '</a></p>';

    echo '</div>';
}

add_action('wp_footer', 'change_settlement_label_js');
function change_settlement_label_js()
{
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('dt[class^="variation-"]').forEach(function (dt) {
                let dd = dt.nextElementSibling;
                if (dd && dd.querySelector('p')) {
                    let originalLabel = dt.textContent.replace(':', '').trim(); // e.g., "5cr-10cr"

                    // Check if the label matches the pattern like "50L-1cr", "5cr-10cr", etc.
                    if (/^\d+.*(L|l|c|C)r.*$/.test(originalLabel)) {
                        dt.textContent = 'Settlement Amount:';  // Change label
                        dd.querySelector('p').textContent = originalLabel; // Replace value
                    }
                }
            });
        });
        jQuery(document).ready(function ($) {
            function modifyVariations() {
                $('dt').each(function () {
                    let dt = $(this);
                    let dd = dt.next('dd');

                    if (dd.length && dd.find('p').length) {
                        let originalLabel = dt.text().replace(':', '').trim(); // e.g., "5cr-10cr"

                        // Check if label matches patterns like "50L-1cr", "5cr-10cr"
                        if (/^\d+.*(L|l|c|C)r.*$/.test(originalLabel)) {
                            dt.text('Settlement Amount:');  // Change label
                            dd.find('p').text(originalLabel); // Set original value
                        }
                    }
                });
            }

            // Run initially
            modifyVariations();

            // Run after WooCommerce checkout refresh (AJAX)
            $(document.body).on('updated_checkout', function () {
                modifyVariations();
            });

            // Also run after cart updates or fragments refresh
            $(document.body).on('updated_cart_totals wc_fragments_refreshed', function () {
                modifyVariations();
            });
        });

        jQuery(document).ready(function ($) {
            function updateSettlementLabels() {
                $('.wc-item-meta li').each(function () {
                    let $label = $(this).find('strong');
                    let $value = $(this).find('p');
                    let labelText = $label.text().replace(':', '').trim();

                    // Check if the label looks like a range (Lakhs, L, or cr)
                    if (/(\d+.*(L|l|c|C)r|\bLakhs\b)/.test(labelText)) {
                        $label.text('Settlement Amount:'); // Change label to "Settlement Amount:"
                        $value.text(labelText); // Replace value with original range (e.g., "50L-1cr")
                    }
                });
            }

            // Run initially
            updateSettlementLabels();

            // Run after WooCommerce AJAX updates (cart/checkout refresh)
            $(document.body).on('updated_wc_div updated_cart_totals updated_checkout', function () {
                updateSettlementLabels();
            });
        });



    </script>
    <?php
}


function get_associated_post_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID() // default: current post
    ), $atts);

    $selected_post_id = get_field('select_tour', $atts['post_id']);

    if (!empty($selected_post_id)) {
        return '[wpvr id="' . $selected_post_id[0] . '"]';
    } else {
        return '[ipano id="1"]';
    }

    return '[ipano id="1"]';
}
add_shortcode('associated_tour_id', 'get_associated_post_shortcode');

add_action('woocommerce_before_add_to_cart_button', 'custom_price_breakdown_html');
function custom_price_breakdown_html()
{

    $product_id = get_the_ID(); // Current product ID
    if ($product_id == 1694 || $product_id == 1944) {
        $field_value = get_post_meta($product_id);


        ?>
        <div id="custom-price-breakdown" style="">
            <div id="fee-title">
                Room Fee
            </div>
            <div id="slt-price">
                <p id="room-charge"><span>Particulars</span> <span>Charges (₹)</span></p>
            </div>
            <div id="slt-time-1"></div>
            <div id="slt-time-2"></div>
            <div id="slt-discount"></div>
            <div id="stl-price">
                <p id="settlement-charge"><span>Admin Fee</span> 0</p>
            </div>
            <div id="adds-price"></div>
            <div id="addon-price"></div>


            <div class="dsc_line" style="text-align: center;margin-top: 15px;margin-bottom: 20px;font-style: italic;">
                <?php
                if (is_user_logged_in()) {

                    $user_id = get_current_user_id();

                    $eligible = get_user_meta($user_id, 'accordhub_discount_eligible', true);
                    $used = get_user_meta($user_id, 'accordhub_discount_used', true);

                    if ($eligible === 'yes' && $used !== 'yes') {
                        echo 'Applicable discount will be enabled on checkout.';
                        echo '<input type="hidden" class="dis_25" name="dis_25" value="1">';
                    } else {
                        echo 'Apply coupon on checkout page to avail discount';
                    }

                } else {
                    echo 'Apply coupon on checkout page to avail discount';
                }
                ?>
            </div>
            <div id="btns"></div>

        </div>
        <?php if ($product_id == 1694) { ?>
            <!-- <div id="participant-popup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
            background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
            <div style="background:#fff; padding:20px; border-radius:8px; max-width:400px; text-align:center;">
                <p>Please book Arvalli Room to add more participants.</p>
                <button id="book-aravalli" style="margin:5px;">Book Aravalli</button>
                <button id="cancel-popup" style="margin:5px;">Cancel</button>
            </div>
        </div> -->
        <?php } else { ?>
            <!-- <div id="participant-popup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
            background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
            <div style="background:#fff; padding:20px; border-radius:8px; max-width:400px; text-align:center;">
                <p>Sorry, the room capacity is maximum 15 particiapnt. Please contact Admin to accommodate larger participants.</p>
                <button id="contact-admin" style="margin:5px;">Contact Admin</button>
                <button id="cancel-popup" style="margin:5px;">Cancel</button>
            </div>
        </div> -->
        <?php } ?>
    <?php
    }
}



add_filter('ph_load_booking_calendar_on_custom_pages', '__return_true');

add_filter('woocommerce_coupons_enabled', 'bbloomer_disable_coupons_cart_page');

function bbloomer_disable_coupons_cart_page()
{
    if (is_checkout() && WC()->session->get('parent_order')) {
        return false;
    } else {
        return true;
    }
}

add_action('woocommerce_checkout_before_order_review', 'custom_checkout_buttons');
function custom_checkout_buttons()
{
    // Get parent order ID from WooCommerce session
    $parent_id = WC()->session->get('parent_order');

    $phive_image_url = '';
    $name = '';
    $booked_datetime = '';

    // ✅ If a parent order exists, load its details
    if ($parent_id) {
        $parent_order = wc_get_order($parent_id);

        if ($parent_order) {
            foreach ($parent_order->get_items() as $item) {
                $product = $item->get_product();

                if ($product && $product->get_type() === 'phive_booking') {
                    $name = $product->get_name();

                    // Safely fetch meta (ensure it’s not an array)
                    $booked_from = $item->get_meta('phive_display_time_from')[0];
                    $booked_to = $item->get_meta('phive_display_time_to')[0];

                    if ($booked_from && $booked_to) {
                        $date2 = date('M j, Y', strtotime($booked_from));
                        $time_from = date('g:i a', strtotime($booked_from));
                        $time_to = date('g:i a', strtotime($booked_to));
                        $booked_datetime = $date2 . ' (' . $time_from . ' – ' . $time_to . ')';
                    }

                    $image_id = $product->get_image_id();
                    $phive_image_url = wp_get_attachment_image_url($image_id, 'full');
                    break; // Stop after first booking item
                }
            }
        }
    } else {
        // ✅ No parent order? Fallback to current cart item image
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if ($product && $product->get_type() === 'phive_booking') {
                $image_id = $product->get_image_id();
                $phive_image_url = wp_get_attachment_image_url($image_id, 'full');
                break;
            }
        }
    }

    // ✅ Now safely output your checkout details
    ?>
    <!-- <div class="custom-checkout-summary">
        <?php if ($phive_image_url): ?>
            <div class="room-img">
                <img src="<?php //echo esc_url($phive_image_url); ?>" alt="">
            </div>
        <?php endif; ?>

        <?php if ($parent_id): ?>
            <div class="productname">
                <span>Booking Reference No:</span>
                <span><?php //echo esc_html($parent_id); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($name): ?>
            <div class="productname">
                <span>Room Name:</span>
                <span><?php //echo esc_html($name); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($booked_datetime): ?>
            <div class=" productname">
                <span>Booking Date:</span>
                <span><?php //echo esc_html($booked_datetime); ?></span>
            </div>
        <?php endif; ?>
    </div> -->

    <?php
}


// add_filter('woocommerce_add_to_cart_redirect', function () {
//     return wc_get_checkout_url();
// });



add_action('woocommerce_after_checkout_billing_form', 'custom_checkout_addon_services');
function custom_checkout_addon_services($saved_cart = null, $cart_name = '') // Pass current cart items if needed
{
    // echo "<pre>";
    // print_r($saved_cart);
    $parent_cat_slug = 'addons';
    $parent_term = get_term_by('slug', $parent_cat_slug, 'product_cat');

    if (!$parent_term) {
        return;
    }

    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => $parent_term->term_id,
    ]);

    if (empty($categories) || is_wp_error($categories)) {
        return;
    }

    $saved_remarks = [];
    $total_member = 0;
    // First try to load from saved cart (new structure)
    if ($saved_cart !== null && is_array($saved_cart)) {
        $saved_remarks = $saved_cart['remarks'] ?? [];
        $saved_cart = $saved_cart['items'] ?? [];
        $total_member = $saved_cart[0]['phive_booked_persons'][0] ?? 0;
    } else {
        $saved_cart = WC()->cart ? WC()->cart->get_cart() : [];
        $saved_remarks = WC()->session->get('addon_category_remarks', []);

        foreach ($saved_cart as $cart_item) {
            if (isset($cart_item['phive_booked_persons'])) {
                $total_member = $cart_item['phive_booked_persons'][0] ?? 0;
                break; // exit the loop since we found it
            }
        }
    }


    if ($cart_name == 'details') {
        $saved_cart = [];
    }
    if ($cart_name === null) {
        $cart_name = '';
    }



    ?>
    <div id="extra-services-popup" style="display:none;">
        <div class="popup-content popup-content-checkout">

            <input type="hidden" name="total_member" id="total_member" value="<?php echo $total_member; ?>">
            <div class="close-icn" id="close-popup"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            <div class="addon-section">
                <h2>Add-ons for Hearing Day</h2>
                <p style="text-align: left;"><b>Total Members:</b> <span
                        class="count"><?php echo !empty($total_member) ? $total_member : 0; ?></span>
                    <a class="member-popup" href=""><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                            viewBox="0 0 24 17" fill="none" stroke="#1763B9" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                        </svg></a>
                </p>
                <table class="service-table" border="1" cellspacing="0" cellpadding="8">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Select Count</th>
                            <th>Select for All</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $placeholders = [
                            'support-services' => 'Eg: Add support service details (e.g., operator at 10:00 am)',
                            'meals' => 'Your meal can be customized later. ',
                            'refreshments' => 'Eg: Provide cookies/veg puff at 3:00 pm',
                            'stationery' => 'Eg: Place notepads and pens on each table',
                        ];
                        foreach ($categories as $category) {
                            echo '<tr class="category-row"><td colspan="5" style="font-weight:600;">' . esc_html($category->name) . '</td></tr>';

                            $products = wc_get_products([
                                'status' => 'publish',
                                'limit' => -1,
                                'category' => [$category->slug],
                            ]);

                            if (!empty($products)) {
                                foreach ($products as $product) {
                                    $product_id = $product->get_id();
                                    $description = get_field('_description', $product_id);
                                    $price = $product->get_regular_price();

                                    // ✅ find quantity in cart
                                    $existing_qty = 0;
                                    foreach ($saved_cart as $cart_item) {
                                        $cart_product_id = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
                                        if ($cart_product_id == $product_id) {
                                            $existing_qty = $cart_item['quantity'];
                                            break;
                                        }
                                    }

                                    // ✅ NEW: Set max="1" ONLY for Product ID 3015
                                    $max_attr = ($product_id == 3015) ? 'max="1"' : 'max="100"';
                                    $max_count = ($product_id == 3015) ? '1' : $total_member;
                                    $class = ($product_id == 3015) ? 'operator' : '';


                                    echo '<tr class="service-row" data-price="' . esc_attr($price) . '">';
                                    echo '<td>' . esc_html($product->get_name());
                                    if ($description) {
                                        echo '<div class="service_desc">' . $description . '</div>';
                                    }
                                    echo '</td>';
                                    if ($product_id == 3015) {
                                        echo '<td data-title="Price">Free</td>';
                                    } else {
                                        echo '<td data-title="Price">₹' . esc_html($price) . '</td>';
                                    }


                                    echo '<td data-title="Select Count">
                                            <input type="number" 
                                                name="addon_qty[' . esc_attr($product_id) . ']" 
                                                class="addon-qty" 
                                                value="' . esc_attr($existing_qty) . '" 
                                                data-original="' . esc_attr($existing_qty) . '"
                                                min="0" ' . $max_attr . ' data-product_id="' . esc_attr($product_id) . '">
                                        </td>';

                                    echo '<td data-title="Select for All">
                                            <label class="switch">
                                                <input type="checkbox" 
                                                    name="addon_all[' . esc_attr($product_id) . ']" 
                                                    class="addon-all a">
                                                <span class="slider round ' . $class . '">' . $max_count . '</span>
                                            </label>
                                        </td>';

                                    $line_total = $price * $existing_qty;
                                    echo '<td data-title="Total Price" class="addon-total">₹' . esc_html($line_total) . '</td>';
                                    echo '</tr>';

                                }
                            }

                            // ✅ Prefill remarks
                            $remark_val = isset($saved_remarks[$category->slug]) ? $saved_remarks[$category->slug] : '';
                            $placeholder = $placeholders[$category->slug];
                            echo '<tr class="remarks-row">
                            <td colspan="5"><div class="rem-box"><span class="rem-label">Add Remark:</span>
                                <textarea name="addon_remarks[' . esc_attr($category->slug) . ']" id="remark' . esc_attr($category->slug) . '" class="auto-height" 
                                        rows="1" 
                                        style="width:100%;" 
                                        data-original="' . esc_textarea($remark_val) . '"
                                        placeholder="' . $placeholder . '">'
                                . esc_textarea($remark_val) . '</textarea>
                            </div></td></tr>';
                        }
                        ?>

                        <?php
                        $brands = get_terms(array(
                            'taxonomy' => 'product_brand',
                            'hide_empty' => true,
                            'meta_query' => array(
                                array(
                                    'key' => '_enabledisable',
                                    'value' => 1,
                                    'compare' => '='
                                )
                            )
                        ));
                        if (!empty($brands) && !is_wp_error($brands)): ?>
                            <tr class="category-row">
                                <td colspan="5" style="font-weight:600;">Snacks & Light Bites</td>
                            </tr>
                            <tr class="category-row">
                                <td colspan="5" style="font-weight:600;">
                                    <div class="rm_tabs">

                                        <ul class="rm_tabs_ul">

                                            <!-- View All -->
                                            <li class="rm_tabs_li active" data-filter="all">
                                                <div class="rm_tabs_inner">
                                                    <span>View All</span>
                                                </div>
                                            </li>

                                            <?php foreach ($brands as $brand):
                                                // If you are using term meta for image
                                                $brand_image = "";
                                                $brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
                                                if ($brand_image_id) {
                                                    $brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
                                                }
                                                //print_r(get_term_meta( $brand->term_id ));
                                    
                                                // Fallback image (optional)
                                                if (empty($brand_image)) {
                                                    //$brand_image = get_stylesheet_directory_uri() . '/images/default-brand.svg';
                                                }
                                                ?>

                                                <li class="rm_tabs_li" data-filter="<?php echo esc_attr($brand->slug); ?>">
                                                    <div class="rm_tabs_inner">

                                                        <?php if (!empty($brand_image)): ?>
                                                            <img src="<?php echo esc_url($brand_image[0]); ?>"
                                                                alt="<?php echo esc_attr($brand->name); ?>">
                                                        <?php endif; ?>

                                                        <span><?php echo esc_html($brand->name); ?></span>
                                                    </div>
                                                </li>

                                            <?php endforeach; ?>

                                        </ul>

                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php
                        // Step 1: Get enabled brands
                        $enabled_brands = get_terms(array(
                            'taxonomy' => 'product_brand',
                            'hide_empty' => true,
                            'meta_query' => array(
                                array(
                                    'key' => '_enabledisable',
                                    'value' => '1',
                                    'compare' => '='
                                )
                            ),
                            'fields' => 'ids'
                        ));

                        // Step 2: Query products with those brands
                        $args = array(
                            'post_type' => 'product',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_brand',
                                    'field' => 'term_id',
                                    'terms' => $enabled_brands
                                ),
                            ),
                        );

                        $query = new WP_Query($args);

                        if ($query->have_posts()):
                            $row_count = 1;

                            while ($query->have_posts()):
                                $query->the_post();
                                $product = wc_get_product(get_the_ID());
                                $product_id = get_the_ID();
                                $existing_qty = 0;

                                foreach ($saved_cart as $cart_item) {
                                    $cart_product_id = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
                                    if ($cart_product_id == $product_id) {
                                        $existing_qty = $cart_item['quantity'];
                                        break;
                                    }
                                }

                                // ✅ NEW: Set max="1" ONLY for Product ID 3015
                                $max_attr = ($product_id == 3015) ? 'max="1"' : 'max="100"';
                                $max_count = ($product_id == 3015) ? '1' : $total_member;
                                $class = ($product_id == 3015) ? 'operator' : '';

                                $brands = get_the_terms(get_the_ID(), 'product_brand');
                                //print_r($brands);
                                $brand_slug = !empty($brands) && !is_wp_error($brands) ? $brands[0]->slug : '';
                                $brand_title = !empty($brands) && !is_wp_error($brands) ? $brands[0]->name : '-';

                                $image_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                                if (!$image_url) {
                                    $image_url = get_stylesheet_directory_uri() . '/images/hot-pan.svg';
                                }

                                // If you are using term meta for image
                                $brand_image = "";
                                $brand_image_id = get_term_meta($brands[0]->term_id, 'thumbnail_id', true);
                                if ($brand_image_id) {
                                    $brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
                                }
                                //print_r(get_term_meta( $brand->term_id ));
                    
                                // Fallback image (optional)
                                if (empty($brand_image)) {
                                    //$brand_image[0] = get_stylesheet_directory_uri() . '/images/hot-pan.svg';
                                }

                                $price = $product ? $product->get_price() : 0;
                                $price_html = $product ? $product->get_price_html() : '';
                                ?>

                                <tr id="rm_table_row_<?php echo esc_attr($row_count); ?>" class="service-row rm_table_row"
                                    data-filter="<?php echo esc_attr($brand_slug); ?>" data-price="<?php echo esc_attr($price); ?>">

                                    <td>
                                        <div class="rm_item_meta">
                                            <?php if (!empty($brand_image)) { ?>
                                                <div class="rm_item_image">
                                                    <img src="<?php echo esc_url($brand_image[0]); ?>"
                                                        alt="<?php echo esc_attr($brand_title); ?>">
                                                </div>
                                            <?php } ?>
                                            <?php the_title(); ?>
                                        </div>
                                    </td>

                                    <td data-title="Price"><?php echo $price_html; ?></td>

                                    <td data-title="Select Count">
                                        <?php
                                        echo '<input type="number" 
                                    name="addon_qty[' . esc_attr($product_id) . ']" 
                                    class="addon-qty" 
                                    value="' . esc_attr($existing_qty) . '" 
                                    data-original="' . esc_attr($existing_qty) . '"
                                    min="0" ' . $max_attr . ' data-product_id="' . esc_attr($product_id) . '">';
                                        ?>
                                    </td>

                                    <td data-title="Select for All">
                                        <?php
                                        echo '<label class="switch">
                                        <input type="checkbox" 
                                            name="addon_all[' . esc_attr($product_id) . ']" 
                                            class="addon-all a">
                                        <span class="slider round ' . $class . '">' . $max_count . '</span>
                                    </label>';
                                        ?>
                                    </td>

                                    <?php $line_total = $price * $existing_qty; ?>
                                    <td data-title="Total Price" class="addon-total">
                                        ₹ <?php echo esc_html($line_total); ?>
                                    </td>

                                </tr>

                                <?php
                                $row_count++;
                            endwhile;

                            wp_reset_postdata();

                        endif;
                        ?>

                    </tbody>
                </table>
                <p style="text-align:left;margin-bottom:0;color:#313131" class="boldonly">Note: Add-ons can be ordered
                    during the meetings as well. For meals- kindly let us know at the start of session or 1 hour in advance.
                </p>
            </div>
            <div id="btnss">
                <button data-cart-name="<?php echo $cart_name; ?>" id="proceed-btn">Add & Proceed</button>
                <button id="close-popup">Close</button>
            </div>
        </div>
        <div id="confirm-modal" style="display:none;">
            <div class="popup-contents">
                <p>Do you want to save the changes?</p>
                <div class="modal-actions">
                    <button data-cart-name="<?php echo $cart_name; ?>" id="confirm-yes" class="button">Yes</button>
                    <button id="confirm-no" class="button">No</button>
                </div>
            </div>
        </div>
    </div>
    <div id="update-booking-modal" style="display:none;">
        <div class="modal-content 2">
            <p>Do you want to change the room booking details? <span class="release-note">Please note that your currently
                    held time slots will be released for others to book.</span></p>
            <button id="booking-yes">Yes</button>
            <button id="booking-no">No</button>
        </div>
    </div>
    <div id="update-booking-slots" style="display:none;">
        <div class="modal-content">
            <p>Do you want to change the booking date or slots? <span class="release-note">Please note that your currently
                    held time slots will be released for others to book.</span></p>
            <button id="slots-yes">Yes</button>
            <button id="slots-no">No</button>
        </div>
    </div>
    <div id="update-member-modal" style="display:none;">
        <div class="modal-content">
            <p>Please add or update the participants for this booking.</p>
            <label for="member-count">Total Members
                <input type="number" name="member-count" id="member-count"
                    value="<?php echo !empty($total_member) ? $total_member : 0; ?>" min="1" step="1">
            </label>
            <button id="member-yes">Save</button>
            <button id="member-no">Cancel</button>
        </div>
    </div>
    <?php
}


add_action('wp_ajax_add_addon_products_to_cart', 'add_addon_products_to_cart');
add_action('wp_ajax_nopriv_add_addon_products_to_cart', 'add_addon_products_to_cart');

function add_addon_products_to_cart()
{
    $addons = isset($_POST['addons']) ? $_POST['addons'] : [];
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : [];

    // Remove all addon products if all qty = 0 and no remarks
    $all_qty_zero = true;
    foreach ($addons as $addon) {
        if (intval($addon['qty']) > 0) {
            $all_qty_zero = false;
            break;
        }
    }

    if ($all_qty_zero && empty(array_filter($remarks))) {
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $terms = get_the_terms($product_id, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $addons_term = get_term_by('slug', 'addons', 'product_cat');
                    if ($term->slug === 'addons' || ($addons_term && $term->parent === $addons_term->term_id)) {
                        WC()->cart->remove_cart_item($cart_item_key);
                        break;
                    }
                }
            }
        }

        WC()->session->__unset('addon_category_remarks'); // clear remarks
        wp_send_json_success('All addon products and remarks removed from cart.');
    }

    // Store remarks if provided
    if (!empty($remarks)) {
        WC()->session->set('addon_category_remarks', $remarks);
    }

    // Add or update addon products
    foreach ($addons as $addon) {
        $product_id = intval($addon['id']);
        $new_qty = intval($addon['qty']);

        if (!$product_id)
            continue;

        $found_key = false;

        // Loop through cart manually to find product
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] === $product_id) {
                $found_key = $cart_item_key;
                break;
            }
        }

        if ($new_qty > 0) {
            if ($found_key) {
                // Update quantity
                WC()->cart->set_quantity($found_key, $new_qty, true);
            } else {
                // Add new item
                WC()->cart->add_to_cart($product_id, $new_qty);
            }
        } else {
            // Remove if qty = 0
            if ($found_key) {
                WC()->cart->remove_cart_item($found_key);
            }
        }
    }

    wp_send_json_success('Add-ons updated successfully.');
}




// --------------------
// Save to order meta (no change)
// --------------------
add_action('woocommerce_checkout_create_order', function ($order) {
    $remarks = WC()->session->get('addon_category_remarks');
    $parent_order = WC()->session->get('parent_order');
    if ($parent_order) {
        $order->update_meta_data('_parent_order_id', $parent_order);
    }
    if (!empty($remarks)) {
        foreach ($remarks as $category_slug => $remark) {
            $category = get_term_by('slug', $category_slug, 'product_cat');
            if ($category && !empty($remark)) {
                $order->update_meta_data(
                    'Remarks for ' . $category->name,
                    sanitize_text_field($remark)
                );
            }
        }
        WC()->session->__unset('addon_category_remarks');
        WC()->session->__unset('parent_order');
    }
});


// --------------------
// Helper: Check if category exists in order
// --------------------
function order_has_category($order, $category_slug)
{
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        if (has_term($category_slug, 'product_cat', $product_id)) {
            return true;
        }
    }
    return false;
}

/*
add_action('woocommerce_review_order_after_cart_contents', function () {
    $remarks = WC()->session->get('addon_category_remarks');
    if (!empty($remarks) && !empty(array_filter($remarks))) {
        $output = [];

        // Heading row
        $output[] = '<tr class="cart_item cart_remark"><td class="product-name" colspan="2"><div><p class="r-title">Remarks</p>';

        foreach ($remarks as $category_slug => $remark) {
            if (!empty($remark)) {
                // ✅ Only show if products from category are in cart
                $in_cart = false;
                foreach (WC()->cart->get_cart() as $cart_item) {
                    if (has_term($category_slug, 'product_cat', $cart_item['product_id'])) {
                        $in_cart = true;
                        break;
                    }
                }

                if ($in_cart) {
                    $category = get_term_by('slug', $category_slug, 'product_cat');
                    $category_name = $category ? esc_html($category->name) : ucfirst($category_slug);

                    $output[] = "<p><span>" . $category_name . ":</span> " . esc_html($remark) . "</p>";
                }
            }
        }
        $output[] = '</div></td></tr>';


        if (!empty($output)) {
            echo implode('', $output);
        }
    }
});
*/



// --------------------
// Show remarks in Admin Order Page
// --------------------
// add_action('woocommerce_admin_order_items_after_line_items', function ($order) {
//     $order = wc_get_order($_REQUEST['id']);
//     if ( ! $order instanceof WC_Order ) {
//         echo "Exit";
//         return;
//     }
//     $output = [];
//     foreach ($order->get_meta_data() as $meta) {
//         if (strpos($meta->key, 'Remarks for ') === 0 && !empty($meta->value)) {
//             $category_name = str_replace('Remarks for ', '', $meta->key);
//             $term = get_term_by('name', $category_name, 'product_cat');
//             if ($term && order_has_category($order, $term->slug)) {
//                 $output[] = '<li><strong>' . esc_html($meta->key) . ':</strong> ' . esc_html($meta->value) . '</li>';
//             }
//         }
//     }
//     if (!empty($output)) {
//         echo '<div class="order_data_column" style="margin-top:30px;margin-bottom:30px"><h4>Service Remarks</h4><ul>' . implode('', $output) . '</ul></div>';
//     }
// });


// --------------------
// Show remarks in Emails
// --------------------
// add_action('woocommerce_email_after_order_table', function ($order, $sent_to_admin, $plain_text, $email) {
//     $output = [];
//     foreach ($order->get_meta_data() as $meta) {
//         if (strpos($meta->key, 'Remarks for ') === 0 && !empty($meta->value)) {
//             $category_name = str_replace('Remarks for ', '', $meta->key);
//             $term = get_term_by('name', $category_name, 'product_cat');
//             if ($term && order_has_category($order, $term->slug)) {
//                 $output[] = '<li><strong>' . esc_html($meta->key) . ':</strong> ' . esc_html($meta->value) . '</li>';
//             }
//         }
//     }
//     if (!empty($output)) {
//         echo '<div class="order_data_column" style="margin-top:30px;margin-bottom:30px"><h3>Service Remarks</h3><ul>' . implode('', $output) . '</ul></div>';
//     }
// }, 20, 4);

// add_action('template_redirect', function () {
//     if (is_cart() && WC()->cart->is_empty()) {
//         wp_safe_redirect(wc_get_page_permalink('rooms'));
//         exit;
//     }
// });



// Add product type as a data attribute in checkout/order review
add_filter('woocommerce_checkout_cart_item_quantity', function ($product_name, $cart_item, $cart_item_key) {
    $product = $cart_item['data']; // WC_Product object
    $product_id = $product->get_id();
    $product_type = $product->get_type(); // simple, variable, etc.
    $product_url = get_permalink($product_id);

    if ($product_type == 'phive_booking') {
        $from = $cart_item['phive_display_time_from'];
        $to = $cart_item['phive_display_time_to'];

        $from_date = date('M j, Y', strtotime($from));
        $to_date = date('M j, Y', strtotime($to));

        if ($from_date === $to_date) {
            $display = $from_date . ' (' . date('g:i a', strtotime($from)) . ' - ' . date('g:i a', strtotime($to)) . ')';
        } else {
            $display = $from . ' - ' . $to;
        }

        $text = $display;
        $word1 = '(9:30 am - 10:30 pm)';
        $word2 = '(9:30 am - 6:00 pm)';
        $word3 = '(2:00 am - 10:30 pm)';

        if (str_contains($text, $word1)) {
            $room_dis = '<span class="tooltip_box" style="text-transform: none !important;">
                            <span class="tooltip_i">i</span>
                            <span class="tooltip_box_hover" style="display:none;">
                                15% discount applied.
                            </span>
                        </span>';
        } elseif (str_contains($text, $word2)) {
            $room_dis = '<span class="tooltip_box" style="text-transform: none !important;">
                            <span class="tooltip_i">i</span>
                            <span class="tooltip_box_hover" style="display:none;">
                                10% discount applied.
                            </span>
                        </span>';
        } elseif (str_contains($text, $word3)) {
            $room_dis = '<span class="tooltip_box" style="text-transform: none !important;">
                            <span class="tooltip_i">i</span>
                            <span class="tooltip_box_hover" style="display:none;">
                                10% discount applied.
                            </span>
                        </span>';
        } else {
            $room_dis = '';
        }
        $room_dis = '';



        return '<a id="update-booking" class="edit-svg" href=""><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg></a><span class="' . esc_attr($product_type) . '" data-url="' . $product_url . '" data-product_id="' . $product_id . '">' . $product_name . '</span><dl class="variation new"><dt>' . $display . '</dt><a class="calender-popup" href=""><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg></a></dl>' . $room_dis;
    } else {
        return '<span class="' . esc_attr($product_type) . '" data-url="' . $product_url . '">' . $product_name . '</span>';
    }

}, 10, 3);

// Show total for products under "addons" parent category (including its child categories)
/*
add_action('woocommerce_review_order_after_cart_contents', function () {
    $addons_total = 0;
 
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id']; 
        $terms = get_the_terms($product_id, 'product_cat'); 
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) { 
                if ($term->slug === 'addons' || term_is_ancestor_of(get_term_by('slug', 'addons', 'product_cat'), $term, 'product_cat')) { 
                    $addons_total += $cart_item['line_subtotal']; 
                    break; 
                }
            }
        }
    }

    if ($addons_total > 0) {
        ?>
        <tr class="cart-addons-total">
            <th style="text-transform:uppercase;">
                <?php _e('Add-ons Total', 'textdomain'); ?>
            </th>
            <td class="product-total" data-title="<?php esc_attr_e('Add-ons Total', 'textdomain'); ?>"
                style="text-transform:uppercase;">
                <?php echo wc_price($addons_total); ?>
            </td>
        </tr>
        <?php
    }
});

*/


add_filter('woocommerce_add_to_cart_validation', 'replace_phive_booking_in_cart', 10, 3);
function replace_phive_booking_in_cart($passed, $product_id, $quantity)
{
    $fresh = isset($_POST['fresh-cart']) && $_POST['fresh-cart'] == '1';
    if ($fresh) {
        WC()->cart->empty_cart();
        WC()->session->set('addon_category_remarks', []);
    } else {
        $product = wc_get_product($product_id);

        if ($product && $product->get_type() === 'phive_booking') {

            // Loop through cart items
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $cart_product = $cart_item['data'];

                if ($cart_product && $cart_product->get_type() === 'phive_booking') {
                    // Remove previous phive_booking product
                    WC()->cart->remove_cart_item($cart_item_key);
                }
            }
        }
    }
    return $passed;
}

add_action('woocommerce_before_calculate_totals', 'keep_only_latest_phive_booking', 20, 1);
function keep_only_latest_phive_booking($cart)
{
    if (is_admin() && !defined('DOING_AJAX'))
        return; // avoid admin
    if (empty($cart->get_cart()))
        return;

    $booking_items = [];

    // Collect all phive_booking products in the cart
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        if ($product && $product->get_type() === 'phive_booking') {
            $booking_items[$cart_item_key] = $cart_item;
        }
    }

    // If more than 1 booking exists, remove all except the last added
    if (count($booking_items) > 1) {
        $last_key = array_key_last($booking_items); // last added booking
        foreach ($booking_items as $cart_item_key => $cart_item) {
            if ($cart_item_key !== $last_key) {
                $cart->remove_cart_item($cart_item_key);
            }
        }
    }
}



add_action('wp_ajax_phive_check_slot', 'phive_check_slot_callback');
add_action('wp_ajax_nopriv_phive_check_slot', 'phive_check_slot_callback');

function phive_check_slot_callback()
{
    global $wpdb;

    // 1. Sanitize Inputs
    $raw_from = sanitize_text_field($_POST['from']);
    $raw_to = sanitize_text_field($_POST['to']);
    $id = intval($_POST['id']);

    // 2. Parse Hours for Logic
    $ts_from = strtotime($raw_from);
    $ts_to = strtotime($raw_to);

    $start_hour = date('H', $ts_from); // Will be 09, 14, or 18
    $end_hour = date('H', $ts_to);     // Will be 09, 14, or 18

    $check_from = date('Y-m-d H:i:s', $ts_from);
    $date_part = date('Y-m-d', $ts_from);

    // 3. APPLY THREE-SLOT LOGIC

    // CASE 1: FULL DAY (Morning Start to Evening End)
    // 09:30 to 18:30 start hours -> Covers 09:30 to 22:30
    if ($start_hour == '09' && $end_hour == '18') {
        $check_to = $date_part . ' 22:30:00';
    }

    // CASE 2: MORNING + AFTERNOON (Double Slot)
    // 09:30 to 14:00 start hours -> Covers 09:30 to 18:00
    elseif ($start_hour == '09' && $end_hour == '14') {
        $check_to = $date_part . ' 18:00:00';
    }

    // CASE 3: AFTERNOON + EVENING (Double Slot)
    // 14:00 to 18:30 start hours -> Covers 14:00 to 22:30
    elseif ($start_hour == '14' && $end_hour == '18') {
        $check_to = $date_part . ' 22:30:00';
    }

    // CASE 4: SINGLE MORNING SLOT
    // 09:30 to 09:30 start hours -> Covers 09:30 to 13:30
    elseif ($start_hour == '09' && $end_hour == '09') {
        $check_to = $date_part . ' 13:30:00';
    }

    // CASE 5: SINGLE AFTERNOON SLOT
    // 14:00 to 14:00 start hours -> Covers 14:00 to 18:00
    elseif ($start_hour == '14' && $end_hour == '14') {
        $check_to = $date_part . ' 18:00:00';
    }

    // CASE 6: SINGLE EVENING SLOT
    // 18:30 to 18:30 start hours -> Covers 18:30 to 22:30
    elseif ($start_hour == '18' && $end_hour == '18') {
        $check_to = $date_part . ' 22:30:00';
    }

    // Fallback for custom ranges
    else {
        if ($ts_to > $ts_from) {
            $check_to = date('Y-m-d H:i:s', $ts_to);
        } else {
            $check_to = date('Y-m-d H:i:s', strtotime($check_from . ' +4 hours'));
        }
    }

    // 4. Run the Overlap Check
    $query = $wpdb->prepare("
        SELECT *
        FROM {$wpdb->prefix}ph_bookings_availability_calculation_data
        WHERE product_id = %d
          AND booking_status != 'canceled'
          AND booking_type != 'cart'
          AND woocommerce_order_status != 'cancelled'
          AND (
                booked_date < %s
            AND booked_date_end > %s
          )
    ", $id, $check_to, $check_from);

    $results = $wpdb->get_results($query, ARRAY_A);

    // 5. Send Response
    wp_send_json(array(
        'logic_used' => "Checking overlap from $check_from to $check_to",
        'booked' => !empty($results),
        'results' => $results
    ));

    wp_die();
}




add_action('woocommerce_review_order_before_submit', 'phive_add_hidden_booking_fields');
function phive_add_hidden_booking_fields()
{
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (isset($cart_item['phive_book_from_date'], $cart_item['phive_book_to_date'])) {
            echo '<input type="hidden" id="phive_book_from_date" value="' . esc_attr($cart_item['phive_book_from_date']) . '">';
            echo '<input type="hidden" id="phive_book_to_date" value="' . esc_attr($cart_item['phive_book_to_date']) . '">';
            echo '<input type="hidden" id="product_id" value="' . esc_attr($cart_item['product_id']) . '">';
            // echo '<input type="hidden" id="phive_book_from_date" value="2025-09-08 09:00">';
            // echo '<input type="hidden" id="phive_book_to_date" value="2025-09-08 14:00">';
            break; // only 1 booking per checkout? remove if multiple
        }
    }
}




//add_action( 'woocommerce_review_order_before_shipping', 'custom_discount_row_before_shipping' );
function custom_discount_row_before_shipping()
{
    $discount_amount = 10000; // flat 10k discount
    ?>
    <tr class="cart-subtotal">
        <th><?php _e('Discount', 'woocommerce'); ?></th>
        <td class="product-total">- <?php echo wc_price($discount_amount); ?></td>
    </tr>
    <?php
}
//add_action( 'woocommerce_cart_calculate_fees', 'apply_custom_discount' );
function apply_custom_discount($cart)
{
    if (is_admin() && !defined('DOING_AJAX'))
        return;

    $discount_amount = 10000; // flat 10k discount
    $cart->add_fee(__('Discount', 'woocommerce'), -$discount_amount);
}
//add_filter('woocommerce_order_button_text', 'custom_checkout_button_text');
function custom_checkout_button_text($button_text)
{
    return __('Confirm & Pay', 'woocommerce');
}

add_filter('woocommerce_cart_item_name', 'custom_booking_product_name', 10, 3);
function custom_booking_product_name($product_name, $cart_item, $cart_item_key)
{

    if (!is_checkout()) {
        return $product_name;
    }

    // Get product object
    $product = $cart_item['data'];

    // Check if product exists and is of type "phive_booking"
    if ($product && $product->get_type() === 'phive_booking') {
        $original_name = $product->get_name();
        $product_name = 'Room Fee - ' . $original_name . '';
    }

    return $product_name;
}


// Add alert container just after coupon form
//add_action('woocommerce_review_order_after_payment', 'custom_coupon_alert_container');
function custom_coupon_alert_container()
{
    echo '<div id="custom-coupon-alert" style="color:red; margin-top:10px;"></div>';
}

//add_filter('woocommerce_notice_types', 'filter_wc_notices', 99);
function filter_wc_notices($notices)
{
    foreach ($notices as $notice_type => $notice_array) {
        foreach ($notice_array as $key => $notice) {
            if (strpos($notice, 'Your order was cancelled.') !== false) {
                unset($notices[$notice_type][$key]);
            }
        }
    }
    return $notices;
}




add_filter('woocommerce_add_message', function ($message) {
    if (stripos($message, 'added to your cart') !== false) {
        return '';
    }
    return $message;
}, 10);

//add_action('wp_footer', 'custom_coupon_alert_script');
function custom_coupon_alert_script()
{
    if (is_checkout()): ?>
        <script type="text/javascript">
            jQuery(function ($) {
                function showAlert(msg) {
                    $("#custom-coupon-alert").text(msg).show();
                }
                function hideAlert() {
                    $("#custom-coupon-alert").hide().text("");
                }

                // Move alert box once
                if ($("#custom-coupon-alert").length && $(".e-coupon-anchor").length) {
                    $("#custom-coupon-alert").appendTo(".e-coupon-anchor");
                }



                // Hijack apply coupon click
                $(document).on('click', 'button.e-apply-coupon', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    let couponCode = $('#coupon_code').val().trim().toLowerCase();
                    if (!couponCode) {
                        showAlert("⚠️ Please enter coupon code.");
                        return;
                    }

                    // get available coupons from hidden div
                    let availableCoupons = $('#available-coupons').text().split(',').map(c => c.trim().toLowerCase());

                    // check if coupon exists
                    if (!availableCoupons.includes(couponCode)) {
                        showAlert("⚠️ Invalid coupon code.");
                        return false;
                    }

                    // check duplicate
                    let appliedCoupons = [];
                    $('.woocommerce-remove-coupon').each(function () {
                        appliedCoupons.push($(this).data('coupon').toLowerCase());
                    });

                    if (appliedCoupons.includes(couponCode)) {
                        showAlert("⚠️ This coupon code is already applied.");
                        return false;
                    }

                    hideAlert();

                    // TODO: perform your AJAX call here once validation passes
                    // applyCouponAjax(couponCode);

                    return false;
                });

            });
        </script>
    <?php endif;
}

// Add hidden coupon list on checkout page
add_action('woocommerce_before_checkout_form', 'show_all_coupons_hidden');

function show_all_coupons_hidden()
{
    // Get all published coupons
    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
    );

    $coupons = get_posts($args);

    if ($coupons) {
        $coupon_codes = array();

        foreach ($coupons as $coupon) {
            $coupon_codes[] = $coupon->post_title; // coupon code is the post title
        }

        echo '<div id="available-coupons" style="display:none;">';
        echo implode(', ', $coupon_codes);
        echo '</div>';
    }
}

add_filter('woocommerce_form_field', function ($field, $key, $args, $value) {
    // Remove &nbsp; from field labels
    $field = str_replace('&nbsp;', '', $field);
    return $field;
}, 10, 4);


// ===== Google Popup OAuth for WooCommerce (no composer) =====
// Replace these with your Google credentials:
define('GW_GOOGLE_CLIENT_ID', 'GW_GOOGLE_CLIENT_ID');
define('GW_GOOGLE_CLIENT_SECRET', 'GW_GOOGLE_CLIENT_SECRET');

/**
 * Render the "Continue with Google" button on both login & register forms
 */
add_action('woocommerce_login_form_end', 'gw_google_login_button');
add_action('woocommerce_register_form_end', 'gw_google_login_button');

function gw_google_login_button()
{
    // Base URL for the OAuth entry
    $entry_url = esc_js(site_url('/google-login'));
    $myaccount_js = esc_js(home_url());

    // Output only the button (no script here)
    ?>
    <div class="gw-google-login" style="">
        <a href="javascript:void(0);" onclick="gw_open_google_popup()">
            <img src="https://developers.google.com/static/site-assets/logo-google-g.svg" alt="Google"
                style="width:18px;height:18px;border-radius: 50%;">
            Login Via Google
        </a>
    </div>
    <?php

    // Print the script once at the footer
    add_action('wp_footer', function () use ($entry_url, $myaccount_js) {
        ?>
        <script>
            function gw_open_google_popup() {
                var w = 620, h = 680;
                var left = (screen.width - w) / 2;
                var top = (screen.height - h) / 2;



                // We pass the current href, but we will also handle the redirect logic explicitly in the listener below
                var returnTo = encodeURIComponent(window.location.href);
                var url = "<?php echo $entry_url; ?>?popup=1&return_to=" + returnTo;


                var popup = window.open(url, 'GoogleLogin', 'width=' + w + ',height=' + h + ',top=' + top + ',left=' + left);

                if (!popup) {
                    alert('Popup blocked. Please allow popups for this site and try again.');
                    return;
                }

                // Listen for message from popup
                function gwMessageHandler(e) {
                    if (e.origin !== window.location.origin) return;

                    var data = e.data || {};
                    if (data.type === 'google_login_success') {
                        jQuery('body').addClass('load');
                        window.removeEventListener('message', gwMessageHandler);

                        // 1. Get URL parameters from the current window
                        var urlParams = new URLSearchParams(window.location.search);
                        var customRedirect = urlParams.get('redirect_to');

                        // 2. Check if 'redirect_to' exists, otherwise fall back to data.redirect or Account Page
                        if (customRedirect) {

                            // Add timestamp to prevent caching
                            var separator = customRedirect.includes('?') ? '&' : '?';
                            var finalUrl = decodeURIComponent(customRedirect) + separator + 'ts=' + new Date().getTime();

                            // Decode it in case it's URL encoded
                            fetch(decodeURIComponent(customRedirect), { cache: 'reload' }).finally(() => {
                                window.location.href = finalUrl;
                            });
                        } else {
                            //location.reload();
                            window.location.href = new URL(window.location.href).toString() + (window.location.search ? '&' : '?') + 'ts=' + Date.now();
                        }

                    } else if (data.type === 'google_login_error') {
                        window.removeEventListener('message', gwMessageHandler);
                        alert('Google login failed: ' + (data.message || 'Unknown error'));
                    }

                }

                window.addEventListener('message', gwMessageHandler, false);
            }

        </script>
        <?php
    }, 99);
}


/**
 * Handle /google-login endpoint (entry + callback)
 */
add_action('init', 'gw_handle_google_login');
function gw_handle_google_login()
{
    // We expect a page (or rewrite) at /google-login — detect the request.
    $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $target_path = parse_url(site_url('/google-login'), PHP_URL_PATH);

    // If not our endpoint, return early
    if (substr($request_path, -strlen($target_path)) !== $target_path) {
        return;
    }

    $client_id = GW_GOOGLE_CLIENT_ID;
    $client_secret = GW_GOOGLE_CLIENT_SECRET;
    $redirect_uri = site_url('/google-login'); // must match Google Console

    // If no code yet, start auth and set a state (to preserve popup + return_to)
    if (empty($_GET['code'])) {
        $state = array(
            'nonce' => wp_create_nonce('gw_google_state'),
            'popup' => isset($_GET['popup']) ? 1 : 0,
            'return_to' => isset($_GET['return_to']) ? $_GET['return_to'] : ''
        );
        $state_enc = base64_encode(wp_json_encode($state));

        $auth_url = add_query_arg(array(
            'response_type' => 'code',
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'scope' => 'openid email profile',
            'state' => $state_enc,
            'prompt' => 'select_account' // optional
        ), 'https://accounts.google.com/o/oauth2/v2/auth');

        wp_redirect($auth_url);
        exit;
    }

    // We have a code — handle callback
    $state_raw = isset($_GET['state']) ? $_GET['state'] : '';
    $state_data = @json_decode(base64_decode($state_raw), true);
    $is_popup = !empty($state_data['popup']);
    $return_to = isset($state_data['return_to']) ? $state_data['return_to'] : '';

    // verify nonce if present
    if (empty($state_data['nonce']) || !wp_verify_nonce($state_data['nonce'], 'gw_google_state')) {
        // nonce invalid — continue but clear return_to for safety
        $return_to = '';
    }

    // Exchange code for tokens
    $token_resp = wp_remote_post('https://oauth2.googleapis.com/token', array(
        'body' => array(
            'code' => $_GET['code'],
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ),
        'timeout' => 20,
    ));

    if (is_wp_error($token_resp)) {
        return gw_send_popup_message_error($is_popup, 'token_request_failed: ' . $token_resp->get_error_message());
    }

    $token_body = json_decode(wp_remote_retrieve_body($token_resp), true);
    $access_token = !empty($token_body['access_token']) ? $token_body['access_token'] : '';

    if (empty($access_token)) {
        $error_msg = isset($token_body['error']) ? (string) $token_body['error'] : 'no_access_token';
        return gw_send_popup_message_error($is_popup, 'No access token: ' . $error_msg);
    }

    // Fetch userinfo
    $user_resp = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', array(
        'headers' => array('Authorization' => 'Bearer ' . $access_token),
        'timeout' => 20,
    ));

    if (is_wp_error($user_resp)) {
        return gw_send_popup_message_error($is_popup, 'userinfo_request_failed: ' . $user_resp->get_error_message());
    }

    $google_user = json_decode(wp_remote_retrieve_body($user_resp), true);
    if (empty($google_user['email'])) {
        return gw_send_popup_message_error($is_popup, 'Google did not return an email address.');
    }

    // sanitize
    $email = sanitize_email($google_user['email']);
    $name = sanitize_text_field($google_user['name'] ?? '');

    // Log in or create WP user
    if (email_exists($email)) {
        $user = get_user_by('email', $email);
        wp_set_auth_cookie($user->ID, true);
    } else {
        $username = sanitize_user(current(explode('@', $email)), true);
        if (username_exists($username)) {
            $username .= '_' . wp_generate_password(4, false);
        }
        $password = wp_generate_password();
        $user_id = wc_create_new_customer($email, $username, $password);

        if (!is_wp_error($user_id)) {
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $name,
                'first_name' => $google_user['given_name'] ?? '',
                'last_name' => $google_user['family_name'] ?? '',
            ));
        }

        wp_set_auth_cookie($user_id, true);


        // Fetch IP
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

        // Use a geolocation API, e.g., ipinfo.io (you can replace with another provider)
        $geo = json_decode(wp_remote_retrieve_body(
            wp_remote_get("https://ipinfo.io/{$user_ip}/json")
        ), true);

        $country = 'IN';
        $region = $geo['region'] ?? '';

        $region_code = '';
        if ($country && $region) {
            $states = WC()->countries->get_states($country);
            $region_code = array_search($region, $states);
        }

        // Update WooCommerce billing fields
        update_user_meta($user_id, 'billing_first_name', $google_user['given_name']);
        update_user_meta($user_id, 'billing_last_name', $google_user['family_name']);
        update_user_meta($user_id, 'billing_country', $country);
        update_user_meta($user_id, 'billing_state', $region_code ?: $region);

        $user_email = $email;

        // Set up the email parameters
        $admin_email = 'admin@accordhub.in';
        $sales_email = 'sales@accordhub.in';
        $subject = 'New User Registration';
        $heading = 'New User Registration';

        // Construct the email body
        $msg = 'A new user has successfully registered on your site.<br><br>';
        $msg .= '<strong>Name:</strong> ' . $google_user['given_name'] . ' ' . $google_user['family_name'] . '<br>';
        if (strpos($user_email, 'example.com') === false) {
            $msg .= '<strong>Email:</strong> ' . $user_email . '<br>';
        }

        // Execute your custom email function
        if (function_exists('send_woocommerce_custom_email')) {
            send_woocommerce_custom_email_to_admin($admin_email, $subject, $heading, $msg);
            send_woocommerce_custom_email_to_admin($sales_email, $subject, $heading, $msg);
            send_woocommerce_custom_email_to_admin('pratiksha@ycspl.in', $subject, $heading, $msg);
        }
    }

    // Build redirect URL: prefer return_to, else My Account
    $redirect_url = !empty($return_to) ? esc_url_raw($return_to) : wc_get_page_permalink('myaccount');

    // If popup, send postMessage to opener and close. Otherwise redirect normally.
    if ($is_popup) {
        $payload = array(
            'type' => 'google_login_success',
            'redirect' => $redirect_url,
        );
        // Output a small JS snippet that posts message then closes the popup
        echo "<!doctype html><html><head><meta charset='utf-8'><title>Google Login</title></head><body>";
        echo "<script>
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage(" . wp_json_encode($payload) . ", window.location.origin);
                }
            } catch(e) {}
            // close after a short delay to ensure message dispatch
            setTimeout(function(){ window.close(); }, 300);
        </script>";
        echo "</body></html>";
        exit;
    } else {
        wp_redirect($redirect_url);
        exit;
    }
}

/**
 * Helper to send an error message back to opener (popup) or show a WP error
 */
function gw_send_popup_message_error($is_popup, $msg)
{
    if ($is_popup) {
        $payload = array('type' => 'google_login_error', 'message' => $msg);
        echo "<!doctype html><html><head><meta charset='utf-8'><title>Google Login Error</title></head><body>";
        echo "<script>
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage(" . wp_json_encode($payload) . ", window.location.origin);
                }
            } catch(e) {}
            // also display the message inside popup for debugging
            document.write('Google login error: ' + " . json_encode($msg) . ");
            setTimeout(function(){}, 1000);
        </script>";
        echo "</body></html>";
        exit;
    } else {
        wp_die('Google login error: ' . esc_html($msg));
    }
}

add_filter('woocommerce_locate_template', 'my_override_woocommerce_template', 10, 3);
function my_override_woocommerce_template($template, $template_name, $template_path)
{
    if ($template_name === 'myaccount/form-login.php') {
        $custom = get_stylesheet_directory() . '/woocommerce/myaccount/form-login.php';
        if (file_exists($custom))
            return $custom;
    }
    return $template;
}


function mask_phone($phone)
{
    if (strlen($phone) < 7)
        return $phone; // too short, don't mask
    return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 6) . substr($phone, -3);
}

function mask_email($email)
{
    $parts = explode('@', $email);
    if (count($parts) !== 2)
        return $email;

    $name = $parts[0];
    $domain = $parts[1];

    $masked_name = substr($name, 0, 3) . str_repeat('*', max(strlen($name) - 4, 3)) . substr($name, -1);

    return $masked_name . '@' . $domain;
}

function send_accordhub_otp($mobile_number, $otp, $type = 'register')
{

    $auth_key = '483917AU35y54yv69467900P1';
    $sender_id = 'ACCRDH';
    $route = '4';
    $api_url = 'https://sms.ssdweb.in/api/sendhttp.php';

    $templates = array(
        'register' => array(
            'dlt_id' => '1007867182765301664',
            'message' => "Your Accordhub registration verification OTP is {$otp}.",
        ),
        'login' => array(
            'dlt_id' => '1007906494143655449',
            'message' => "Your Accordhub login verification OTP is {$otp}.",
        ),
    );

    if (!isset($templates[$type])) {
        custom_log("SMS Error: Invalid OTP type '$type' requested.");
        return false;
    }

    $selected_template = $templates[$type];

    $params = array(
        'authkey' => $auth_key,
        'mobiles' => $mobile_number,
        'message' => $selected_template['message'],
        'sender' => $sender_id,
        'route' => $route,
        'DLT_TE_ID' => $selected_template['dlt_id'],
    );

    $request_url = add_query_arg($params, $api_url);
    $response = wp_remote_get($request_url, array('timeout' => 15));

    if (is_wp_error($response)) {
        custom_log('SMS API Connection Error: ' . $response->get_error_message());
        return false;
    }

    return wp_remote_retrieve_body($response);
}

add_action('wp_ajax_nopriv_ajax_send_otp', 'ajax_send_otp');
add_action('wp_ajax_ajax_send_otp', 'ajax_send_otp');
function ajax_send_otp()
{
    check_ajax_referer('ajax-login-nonce', 'security');
    $input = sanitize_text_field($_POST['phone']); // can be email OR phone

    if (empty($input)) {
        wp_send_json_error(['message' => '<span style="color:red;">Enter phone number or email id.</span>']);
    }

    // Find user by email OR phone
    if (is_email($input)) {
        $user = get_user_by('email', $input);
    } else {
        $users = get_users([
            'meta_key' => 'phone',
            'meta_value' => $input,
            'number' => 1
        ]);
        $user = !empty($users) ? $users[0] : null;
    }

    if (!$user) {
        wp_send_json_error(['message' => '<span style="color:red;">No user found with this email id / phone number.</span>']);
    }
    $first_name = get_user_meta($user->ID, 'first_name', true);
    $email = $user->user_email;
    $phone = get_user_meta($user->ID, 'phone', true);
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (strlen($phone) === 10) {
        $phone = '91' . $phone;
    } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
        $phone = '91' . substr($phone, 1);
    }

    $otp = rand(100000, 999999); // static for testing (use rand(100000,999999) later)
    //$otp = 111111;
    // Always use same key for both email/phone → tied to user ID
    $key = 'otp_code_' . $user->ID;
    set_transient($key, (string) $otp, 3 * MINUTE_IN_SECONDS);
    $sub = "Accordhub - OTP for Account Login";
    $e_heading = "Here is your OTP for Login";

    $msg = "<p>Dear Customer,</p>";
    $msg .= "<p>Your one time password for Accordhub login is</p>";
    $msg .= "<p class='otp_p'>" . $otp . "</p>";
    $msg .= "<p>Log in and start exploring.</p>";
    $msg .= "<p>If you have not requested the OTP, please ignore this email.</p>";

    // send mail/sms here
    if (!empty($email)) {
        //wp_mail($email, 'Your OTP Code', 'Your OTP is: ' . $otp);
        send_woocommerce_custom_email($email, $sub, $e_heading, $msg);
    }
    if (!empty($phone)) {
        send_accordhub_otp($phone, $otp, 'login');
        $components = [
            [
                'type' => 'body',
                'parameters' => [['type' => 'text', 'text' => $otp]] // The OTP
            ],
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => [['type' => 'text', 'text' => $otp]] // Copy code button
            ]
        ];
        send_whatsapp_template_msg($phone, 'login_registration_otp', $components);
    }

    wp_send_json_success([
        'message' => 'OTP sent to your registered ' .
            (!empty($email) ? 'email <b>' . mask_email($email) . '</b>' : '') .
            (!empty($phone) ? ' and phone <b>' . mask_phone($phone) . '</b>' : '') . '.'
    ]);
}

// Verify OTP
add_action('wp_ajax_nopriv_ajax_verify_otp', 'ajax_verify_otp');
add_action('wp_ajax_ajax_verify_otp', 'ajax_verify_otp');
function ajax_verify_otp()
{
    check_ajax_referer('ajax-otp-verify-nonce', 'security_verify');

    $otp_entered = sanitize_text_field($_POST['otp_code']);
    $input = sanitize_text_field($_POST['phone']); // can be email OR phone

    // Find user first
    if (is_email($input)) {
        $user = get_user_by('email', $input);
    } else {
        $users = get_users([
            'meta_key' => 'phone',
            'meta_value' => $input,
            'number' => 1
        ]);
        $user = !empty($users) ? $users[0] : null;
    }

    if (!$user) {
        wp_send_json_error(['message' => '<span style="color:red;">User not found.</span>']);
    }

    $key = 'otp_code_' . $user->ID;
    $stored = get_transient($key);

    custom_log("VERIFY Entered: $otp_entered | Stored: $stored | Key: $key");

    if ($stored && $stored === (string) $otp_entered) {
        wp_set_auth_cookie($user->ID);
        delete_transient($key);

        wp_send_json_success([
            'message' => '<span style="color:green;">OTP verified. Logging in...</span>'
        ]);
    }

    wp_send_json_error([
        'message' => '<span style="color:red;">Invalid OTP.</span>'
    ]);
}




// Save extra registration fields
add_action('woocommerce_created_customer', function ($customer_id) {
    $fields_map = [
        'first_name' => 'billing_first_name',
        'last_name' => 'billing_last_name',
        'phone' => 'billing_phone',
        'country' => 'billing_country',
        'state' => 'billing_state',
        'city' => 'billing_city',
        'address_1' => 'billing_address_1',
        'postcode' => 'billing_postcode',
    ];

    foreach ($fields_map as $post_key => $billing_key) {
        if (isset($_POST[$post_key]) && !empty($_POST[$post_key])) {
            $value = sanitize_text_field($_POST[$post_key]);

            // Save to user meta (profile fields)
            update_user_meta($customer_id, $post_key, $value);

            // Save to WooCommerce billing meta
            update_user_meta($customer_id, $billing_key, $value);
        }
    }
});


add_action('user_register', function ($user_id) {
    $first_name = get_user_meta($user_id, 'first_name', true);
    if ($first_name) {
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $first_name
        ]);
    }
});

// Default country = India
add_filter('default_checkout_billing_country', 'my_default_checkout_country');
function my_default_checkout_country()
{
    return 'IN'; // India
}

// Default state = Rajasthan
add_filter('default_checkout_billing_state', 'my_default_checkout_state');
function my_default_checkout_state()
{
    return 'RJ'; // Rajasthan
}
//add_filter('woocommerce_countries_allowed_countries', 'restrict_countries_to_india');
function restrict_countries_to_india($countries)
{
    return array(
        'IN' => $countries['IN'],
        '' => $countries['Select']
    );
}



// Validate confirm password on registration
//add_action('woocommerce_register_post', 'my_validate_confirm_password', 10, 3);
function my_validate_confirm_password($username, $email, $validation_errors)
{
    // Only validate when user is sending a password (some stores auto-generate passwords)
    if (isset($_POST['password'])) {
        $password = isset($_POST['password']) ? wc_clean(wp_unslash($_POST['password'])) : '';
        $password2 = isset($_POST['password2']) ? wc_clean(wp_unslash($_POST['password2'])) : '';

        if (empty($password)) {
            $validation_errors->add('password_empty', __('Please enter a password.', 'woocommerce'));
        }

        if ($password !== $password2) {
            $validation_errors->add('password_mismatch', __('Passwords do not match.', 'woocommerce'));
        }
    }

    return $validation_errors;
}


// Show Case Details Form on Thank You Page
add_action('woocommerce_thankyou', 'show_case_details_form_on_thankyou', 30);
function show_case_details_form_on_thankyou($order_id)
{
    if (!$order_id)
        return;

    $order = wc_get_order($order_id);
    if (!$order)
        return;



    if ($order->has_status(array('cancelled'))) {
        echo '<div class="woocommerce-case-form"><div class="close-icn close-popup" id="close-popup"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>';

        echo "You can't update case details for cancelled Bookings.";
        echo '</div>';
        return;
    }

    // Initialize variables
    $case_id = '';
    $case_name = '';
    $parties = array();
    $case_description = '';
    $arbitrator_details = '';
    $legal_representative_details = '';
    $parent_order_id = null;

    foreach ($order->get_meta_data() as $meta) {
        if ($meta->get_data()['key'] === '_parent_order_id') {
            $parent_order_id = $meta->get_data()['value'];
            break;
        }
    }


    $is_elementor_edit_mode = false;
    if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
        $is_elementor_edit_mode = true;
    }

    if (!$is_elementor_edit_mode) {

        if ($parent_order_id) {
            $parent_order = wc_get_order($parent_order_id);
            if ($parent_order) {

                // Add success notice for parent order
                $notice = sprintf(
                    'Add-ons order successful for your current booking #%d.',
                    $order_id
                );
                wc_add_notice($notice, 'success');

                // Persist notice in session
                WC()->session->set('wc_notices', WC()->session->get('wc_notices'));

                // Redirect to parent order
                wp_safe_redirect($parent_order->get_view_order_url());
                exit;
            }
        }

    }



    // Get first phive_booking item meta
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if ($product && $product->get_type() === 'phive_booking') {
            $product_name = $item->get_name();
            $case_id = $item->get_meta('Case ID');
            $case_name = $item->get_meta('Case Title');
            $total = $item->get_meta('Total Participants');
            $parties = $item->get_meta('Parties Involved') ?: array();
            $case_description = $item->get_meta('Case Description');
            //$arbitrator_details = $item->get_meta('Arbitrator Details');
            $booked_from = $item->get_meta('phive_display_time_from')[0];
            if (!is_array($item->get_meta('phive_display_time_from'))) {
                $booked_from = $item->get_meta('From')[0];
            }
            $booked_to = $item->get_meta('phive_display_time_to')[0];
            if (!is_array($item->get_meta('phive_display_time_to'))) {
                $booked_to = $item->get_meta('To')[0];
            }
            $legal_representative_details = $item->get_meta('Legal Representative Details');
            $other_rem = $item->get_meta('Other Remarks');
            break; // only first matching item
        }
    }

    if ($order->get_meta('group_parent_order')) {
        $parent_order_id = $order->get_meta('group_parent_order');
        $p_ord = wc_get_order($parent_order_id);

        if ($p_ord) {
            foreach ($p_ord->get_items() as $item_id => $item) {
                $product = $item->get_product();
                if ($product && $product->get_type() === 'phive_booking') {
                    $booked_from = $item->get_meta('phive_display_time_from')[0];
                    $booked_to = $item->get_meta('phive_display_time_to')[0];
                    $product_name = $item->get_name();
                }
            }
        }
    } else {
        $parent_order_id = $order_id;
    }

    $date2 = date('F j, Y', strtotime($booked_from));
    $time_from = date('g:i a', strtotime($booked_from));
    $time_to = date('g:i a', strtotime($booked_to));
    if ($time_to === $time_from || $time_to === date('g:i a', strtotime($booked_from . ' + 5 hours'))) {
        $time_to = date('g:i a', strtotime($booked_to . ' + 4 hours'));
    }
    $booked_datetime = $date2 . ' (' . $time_from . ' – ' . $time_to . ')';

    if (empty($case_id)) {
        $case_id = $order->get_meta('Case ID');
    }
    if (empty($case_name)) {
        $case_name = $order->get_meta('Case Title');
    }
    if (empty($total)) {
        $total = $order->get_meta('Total Participants');
    }
    $parties_total = $order->get_meta('Total Parties Involved') ?: 1;

    if (empty($parties)) {
        $parties = $order->get_meta('Parties Involved') ?: [];
    }
    if (empty($case_description)) {
        $case_description = $order->get_meta('Case Description');
    }
    $arbitrator_details = $order->get_meta('Arbitrators');

    if (empty($legal_representative_details)) {
        $legal_representative_details = $order->get_meta('Legal Representative Details');
    }
    if (empty($other_rem)) {
        $other_rem = $order->get_meta('Other Remarks');
    }



    // Nonce field for security
    wp_nonce_field('save_case_details', 'case_details_nonce');

    if ($order->get_meta('addons_parent_order_id')) {

        $parent_order_id = $order->get_meta('addons_parent_order_id');
        $parent_order = wc_get_order($parent_order_id);

        if ($parent_order) {

            // Add success notice
            wc_add_notice(
                sprintf(
                    'Add-ons Payment successful for your booking #%d.',
                    $parent_order_id
                ),
                'success'
            );
            wc_print_notices();

            return;
        }
    }

    //print_r($parties); ?>
    <div class="woocommerce-case-form">

        <?php
        if (!is_wc_endpoint_url('order-received')) { ?>
            <div class="close-icn close-popup" id="close-popup"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            <h2 style="margin-bottom: 15px;margin-top: 10px;">Update Case Details</h2>
            <p style="text-align: center;margin-bottom: 15px;">Please update the case details to help us track your case for
                future bookings</p>
        <?php } else {

            if ($order && $order->is_paid()) {
                if ($order->get_meta('group_payment_mode') == 'group' || $order->get_meta('group_parent_order')) {
                    $p_type = 'Split';
                    if ($p_ord) {
                        $order_status = $p_ord->get_status();
                    } else {
                        $order_status = $order->get_status();
                    }
                    if ($order_status == 'processing') {
                        wc_add_notice('Thank you! Your payment was successful. Your Booking is under process and will be confirmed only if the other parties complete the payment process within the next 24 hours. <button type="button" class="button woocommerce-button"><a style="color:#fff" href="' . $order->get_view_order_url() . '">View Details</a></button>', 'success');
                    } else {
                        wc_add_notice('Thank you! Your payment was successful.  <button type="button" class="button woocommerce-button"><a style="color:#fff" href="' . $order->get_view_order_url() . '">View Details</a></button>', 'success');
                    }
                } else {
                    wc_add_notice('Thank you! Your payment was successful. <button type="button" class="button woocommerce-button"><a style="color:#fff" href="' . $order->get_view_order_url() . '">View Details</a></button>', 'success');
                    $p_type = 'Full';
                }
                wc_print_notices();
            }

            // if ($order->get_meta('group_parent_order')) {
            //     $p_type = 'Split';
            // }
            ?>
            <table style="margin:20px auto 45px auto;">
                <tbody>
                    <tr>
                        <td class="woocommerce-table__product-name product-name">
                            Booking Reference No </td>
                        <td class="woocommerce-table__product-table product-total order-id"><?php echo $parent_order_id; ?></td>
                    </tr>
                    <tr>
                        <td class="woocommerce-table__product-name product-name" scope="row">Room Name </td>
                        <td class="woocommerce-table__product-table product-total">
                            <?php echo $product_name; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="woocommerce-table__product-name product-name" scope="row">Booking Date & Slots </td>
                        <td class="woocommerce-table__product-table product-total">
                            <?php echo $booked_datetime; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="woocommerce-table__product-name product-name" scope="row">Payment Type</td>
                        <td class="woocommerce-table__product-table product-total">
                            <?php echo $p_type; ?> payment
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
            if ($order->get_meta('group_parent_order')) {
                return;
            }
            ?>
            <h2>Submit Case Details</h2>
            <p style="text-align: center;">This section is totally optional and is solely for the purpose of tracking/managing
                your case hearings with Accordhub. Please close this window if you don't want to share case details as your
                booking
                process has already been started or is confirmed.</p>
            <div id="update-order-meta" style="display:none;">
                <div class="modal-content">
                    <p>You have previously submitted details for this Case. Do you want to copy previous details into this
                        Booking?</p>
                    <button id="order-yes" class="button woocommerce-button">Yes</button>
                    <button id="order-no" class="button woocommerce-button">No</button>
                </div>
            </div>
        <?php } ?>

        <!-- Custom Unsaved Changes Modal -->
        <div id="custom-unsaved-modal"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999999;">
            <div class="modal-content"
                style="background:#fff; margin:20vh auto; padding:30px; max-width:400px; text-align:center; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                <p style="margin-bottom:25px; font-size:16px;">Do you want save the case details?</p>
                <button id="custom-unsaved-yes" class="button woocommerce-button">Yes</button>
                <button id="custom-unsaved-no" class="button woocommerce-button">No</button>
            </div>
        </div>

        <form method="post" id="case-details-form">
            <?php wp_nonce_field('save_case_details', 'case_details_nonce'); ?>
            <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">

            <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                <label>Case Title</label>
                <textarea name="case_title" class="input-text"><?php echo esc_textarea($case_name); ?></textarea>
            </p>

            <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                <label>Case ID</label>
                <input type="text" name="case_id" class="input-text" value="<?php echo esc_attr($case_id); ?>">
            </p>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label>Case Description</label>
                <textarea name="case_description"
                    class="input-text"><?php echo esc_textarea($case_description); ?></textarea>
            </p>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label>No of Parties</label>
                <input type="number" id="num-parties" class="input-text" name="num_parties" min="2" value="<?php if ($parties_total > 1) {
                    echo $parties_total;
                    $repeat = $parties_total;
                } else {
                    echo '2';
                    $repeat = 2;
                } ?>">
            </p>

            <div id="parties-wrapper" class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <?php
                for ($i = 0; $i < intval($repeat); $i++):
                    $p = isset($parties[$i]) ? $parties[$i] : [];
                    ?>
                    <div class="party-section">
                        <h4>Party <?php echo ($i + 1); ?> Details</h4>
                        <div>
                            <div class="woocommerce-form-row form-row form-row-wide">
                                <input type="text" name="parties[<?php echo $i; ?>][name]" class="input-text"
                                    placeholder="Party Name"
                                    value="<?php echo isset($p['name']) ? esc_attr($p['name']) : ''; ?>">
                            </div>
                            <div class="woocommerce-form-row form-row form-row-wide members-wrapper"
                                data-party-index="<?php echo $i; ?>">
                                <?php
                                $member_wa = '';
                                if (!empty($p['members']) && is_array($p['members'])):
                                    foreach ($p['members'] as $j => $member):
                                        $member_name = is_array($member) ? ($member['name'] ?? '') : $member;
                                        $member_wa = is_array($member) ? ($member['wa'] ?? '') : $member;
                                        $member_role = is_array($member) ? ($member['role'] ?? 'company_rep') : 'company_rep';
                                        ?>
                                        <div class="member-input member-wa-row">
                                            <div class="member-wa-box">
                                                <input type="text" name="parties[<?php echo $i; ?>][members][<?php echo $j; ?>][name]"
                                                    class="input-text" placeholder="Participant's Name"
                                                    value="<?php echo esc_attr($member_name); ?>">

                                                <input type="text" name="parties[<?php echo $i; ?>][members][<?php echo $j; ?>][wa]"
                                                    class="input-text" placeholder="Whatsapp Number"
                                                    value="<?php echo esc_attr($member_wa); ?>" pattern="[0-9]{10}" minlength="10"
                                                    maxlength="10" inputmode="numeric">
                                            </div>
                                            <label>
                                                <input type="radio" name="parties[<?php echo $i; ?>][members][<?php echo $j; ?>][role]"
                                                    value="company_rep" <?php checked($member_role, 'company_rep'); ?>>
                                                Company Representative
                                            </label>
                                            <label>
                                                <input type="radio" name="parties[<?php echo $i; ?>][members][<?php echo $j; ?>][role]"
                                                    value="legal_counsel" <?php checked($member_role, 'legal_counsel'); ?>>
                                                Legal Counsel
                                            </label>
                                            <div style="display: flex;gap:5px;">
                                                <button type="button" class="add-member">+</button>
                                                <button type="button" class="remove-member">-</button>
                                            </div>
                                        </div>
                                        <?php
                                    endforeach;
                                else: ?>
                                    <div class="member-input member-wa-row">
                                        <div class="member-wa-box">
                                            <input type="text" name="parties[<?php echo $i; ?>][members][0][name]"
                                                class="input-text" placeholder="Participant's Name">
                                            <input type="text" name="parties[<?php echo $i; ?>][members][0][wa]" class="input-text"
                                                placeholder="Whatsapp Number" value="<?php echo esc_attr($member_wa); ?>"
                                                pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric">
                                        </div>
                                        <label>
                                            <input type="radio" name="parties[<?php echo $i; ?>][members][0][role]"
                                                value="company_rep" checked>
                                            Company Representative
                                        </label>
                                        <label>
                                            <input type="radio" name="parties[<?php echo $i; ?>][members][0][role]"
                                                value="legal_counsel">
                                            Legal Counsel
                                        </label>

                                        <div style="display: flex;gap:5px;">
                                            <button type="button" class="add-member">+</button>
                                            <button type="button" class="remove-member">-</button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>


                        </div>
                    </div>
                <?php endfor; ?>
            </div>


            <div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide arbitrators-wrapper">
                <label>Arbitrators</label>

                <?php if (!empty($arbitrator_details) && is_array($arbitrator_details)):
                    $t = 0;
                    ?>
                    <?php foreach ($arbitrator_details as $arb): ?>
                        <div class="arbitrator-input" data-count="<?php echo $t; ?>">
                            <input type="text" name="arbitrators[]" class="input-text" placeholder="Arbitrator's Name"
                                value="<?php echo esc_attr($arb); ?>">
                            <button type="button" class="add-arbitrator">+</button>
                            <button type="button" class="remove-arbitrator">-</button>
                        </div>
                        <?php
                        $t++;
                    endforeach; ?>
                <?php else: ?>
                    <!-- Show at least one empty input if no data -->
                    <div class="arbitrator-input" data-count="0">
                        <input type="text" name="arbitrators[]" class="input-text" placeholder="Arbitrator's Name" value="">
                        <button type="button" class="add-arbitrator">+</button>
                        <button type="button" class="remove-arbitrator">-</button>
                    </div>
                <?php endif; ?>
            </div>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label>Total Members </label>
                <input type="number" id="num-members" class="input-text" name="num_members" min="1"
                    value="<?php echo esc_attr($total); ?>">
            </p>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label>Other Remarks </label>
                <textarea id="other_remarks" name="other_remarks"
                    class="input-text"><?php echo esc_textarea($other_rem); ?></textarea>
            <div id="remarks_count">0 / 50</div>
            </p>

            <p class="woocommerce-form-row woocommerce-form-row--full form-row form-row-wide btn-row">

                <?php if (!is_wc_endpoint_url('order-received')) { ?>
                    <button type="submit" name="save_case_details" class="button woocommerce-button">Update</button>
                    <button type="button" class="button woocommerce-button close-popup">Cancel</button>
                <?php } else { ?>
                    <button type="submit" name="save_case_details" class="button woocommerce-button">Submit</button>
                <?php } ?>
            </p>
        </form>
        <div id="case-form-message"></div>
    </div>

    <script>

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('add-arbitrator')) {
                const wrapper = e.target.closest('.arbitrators-wrapper');

                // Count existing arbitrator-input elements
                const currentCount = wrapper.querySelectorAll('.arbitrator-input').length;
                const newCount = currentCount;

                // Create new input div
                const div = document.createElement('div');
                div.classList.add('arbitrator-input');
                div.setAttribute('data-count', newCount); // add data-count attr

                div.innerHTML = `
        <input type="text" name="arbitrators[]" class="input-text" placeholder="Arbitrator Name">
        <button type="button" class="add-arbitrator">+</button>
        <button type="button" class="remove-arbitrator">-</button>
    `;

                wrapper.appendChild(div);
            }

            if (e.target.classList.contains('remove-arbitrator')) {
                const wrapper = e.target.closest('.arbitrators-wrapper');
                const inputs = wrapper.querySelectorAll('.arbitrator-input');
                if (inputs.length > 1) {
                    e.target.closest('.arbitrator-input').remove();
                }
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById('case-details-form');
            const messageContainer = document.getElementById('case-form-message');
            const wrapper = document.getElementById('parties-wrapper');
            const numPartiesInput = document.getElementById('num-parties');
            let cachedParties = {}; // store data by index (0,1,2...)
            let formIsDirty = false;

            if (form) {
                form.addEventListener('input', function () { formIsDirty = true; });
                form.addEventListener('change', function () { formIsDirty = true; });
            }

            // Handle standard close/refresh browser alerts (must remain native)
            window.addEventListener('beforeunload', function (e) {
                if (formIsDirty) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Do you want to leave?';
                }
            });

            // Show custom modal on link click
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (link && formIsDirty && !link.hasAttribute('data-nocatch') && !link.href.includes('javascript:')) {
                    e.preventDefault();
                    window.pendingRedirect = link.href;
                    window.pendingAction = 'redirect';
                    document.getElementById('custom-unsaved-modal').style.display = 'block';
                }
            });

            // Show custom modal on popup close button
            jQuery(document).on('click', '.close-popup', function (e) {
                e.preventDefault();
                if (formIsDirty) {
                    window.pendingAction = 'close';
                    document.getElementById('custom-unsaved-modal').style.display = 'block';
                } else {
                    jQuery(".case-edit-popup").hide();
                }
            });

            // Handle Custom Modal Yes Button
            document.getElementById('custom-unsaved-yes').addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('custom-unsaved-modal').style.display = 'none';
                jQuery('#case-details-form button[type="submit"]').trigger('click');
            });

            // Handle Custom Modal No Button
            document.getElementById('custom-unsaved-no').addEventListener('click', function () {
                document.getElementById('custom-unsaved-modal').style.display = 'none';
                formIsDirty = false;

                if (window.pendingAction === 'redirect' && window.pendingRedirect) {
                    window.location.href = window.pendingRedirect;
                } else if (window.pendingAction === 'close') {
                    jQuery(".case-edit-popup").hide();
                    //window.location.href = window.pendingRedirect;
                }
            });

            function adjustPartyFields(count) {
                wrapper.innerHTML = ''; // clear existing
                for (let i = 0; i < count; i++) {
                    const partyData = cachedParties[i] || { name: '', members: [{ name: '', role: 'company_rep' }] };
                    const partyDiv = document.createElement('div');
                    partyDiv.classList.add('party-section');

                    let membersHTML = '';
                    partyData.members.forEach((member, mIdx) => {
                        membersHTML += `
                <div class="member-input member-wa-row">
                    <div class="member-wa-box">
                        <input type="text" 
                            name="parties[${i}][members][${mIdx}][name]" 
                            class="input-text" 
                            placeholder="Participant's Name" 
                            value="${member.name}">
                        <input type="text" name="parties[${i}][members][${mIdx}][wa]" 
                            class="input-text" placeholder="Whatsapp Number"
                            value="${member.wa}"
                            pattern="[0-9]{10}" 
                            minlength="10" 
                            maxlength="10"
                            inputmode="numeric">
                    </div>
                    <label>
                        <input type="radio" 
                               name="parties[${i}][members][${mIdx}][role]" 
                               value="company_rep"
                               ${member.role === 'company_rep' ? 'checked' : ''}>
                        Company Representative
                    </label>
                    <label>
                        <input type="radio" 
                               name="parties[${i}][members][${mIdx}][role]" 
                               value="legal_counsel"
                               ${member.role === 'legal_counsel' ? 'checked' : ''}>
                        Legal Counsel
                    </label>
                                                        <div style="display: flex;gap:5px;">
                                            <button type="button" class="add-member">+</button>
                                            <button type="button" class="remove-member">-</button>
                                            </div>
                </div>
            `;
                    });

                    partyDiv.innerHTML = `
            <h4>Party ${i + 1} Details</h4>
            <div>
                <div class="woocommerce-form-row form-row form-row-wide">
                    <input type="text" 
                           name="parties[${i}][name]" 
                           class="input-text" 
                           placeholder="Party Name" 
                           value="${partyData.name}">
                </div>
                <div class="woocommerce-form-row form-row form-row-wide members-wrapper" data-party-index="${i}">
                    ${membersHTML}
                </div>
            </div>
        `;

                    wrapper.appendChild(partyDiv);
                }
            }



            // Member add/remove logic stays the same
            wrapper.addEventListener('click', function (e) {
                if (e.target.classList.contains('add-member')) {
                    const membersWrapper = e.target.closest('.members-wrapper');
                    const idx = membersWrapper.dataset.partyIndex;
                    const memberCount = membersWrapper.querySelectorAll('.member-input').length;

                    const div = document.createElement('div');
                    div.classList.add('member-input');
                    div.classList.add('member-wa-row');

                    div.innerHTML = `
            <div class="member-wa-box">
                <input type="text" 
                    name="parties[${idx}][members][${memberCount}][name]" 
                    class="input-text" 
                    placeholder="Participant's Name">
                <input type="text" name="parties[${idx}][members][${memberCount}][wa]" 
                    class="input-text" placeholder="Whatsapp Number"
                    pattern="[0-9]{10}" 
                    minlength="10" 
                    maxlength="10"
                    inputmode="numeric">
            </div>
            <label>
                <input type="radio" 
                       name="parties[${idx}][members][${memberCount}][role]" 
                       value="company_rep" 
                       checked>
                Company Representative
            </label>
            <label>
                <input type="radio" 
                       name="parties[${idx}][members][${memberCount}][role]" 
                       value="legal_counsel">
                Legal Counsel
            </label>

                                                        <div style="display: flex;gap:5px;">
                                            <button type="button" class="add-member">+</button>
                                            <button type="button" class="remove-member">-</button>
                                            </div>
        `;

                    membersWrapper.appendChild(div);
                }

                if (e.target.classList.contains('remove-member')) {
                    const membersWrapper = e.target.closest('.members-wrapper');
                    const memberInputs = membersWrapper.querySelectorAll('.member-input');
                    if (memberInputs.length > 1) {
                        e.target.closest('.member-input').remove();
                    }
                }
            });


            // Initialize Dynamic Party Logic without Resetting
            numPartiesInput.addEventListener('input', function () {
                let count = parseInt(this.value) || 1;
                const currentPartyNodes = wrapper.querySelectorAll('.party-section');
                let currentCount = currentPartyNodes.length;

                if (count > currentCount) {
                    // Add new blank parties
                    for (let i = currentCount; i < count; i++) {
                        const partyDiv = document.createElement('div');
                        partyDiv.classList.add('party-section');
                        partyDiv.innerHTML = `
                            <h4>Party ${i + 1} Details</h4>
                            <div>
                                <div class="woocommerce-form-row form-row form-row-wide">
                                    <input type="text" 
                                           name="parties[${i}][name]" 
                                           class="input-text" 
                                           placeholder="Party Name" 
                                           value="">
                                </div>
                                <div class="woocommerce-form-row form-row form-row-wide members-wrapper" data-party-index="${i}">
                                    <div class="member-input member-wa-row">
                                        <div class="member-wa-box">
                                            <input type="text" 
                                                name="parties[${i}][members][0][name]" 
                                                class="input-text" 
                                                placeholder="Participant's Name" 
                                                value="">
                                            <input type="text" name="parties[${i}][members][0][wa]" 
                                                class="input-text" placeholder="Whatsapp Number"
                                                value=""
                                                pattern="[0-9]{10}" 
                                                minlength="10" 
                                                maxlength="10"
                                                inputmode="numeric">
                                        </div>
                                        <label>
                                            <input type="radio" 
                                                   name="parties[${i}][members][0][role]" 
                                                   value="company_rep" checked>
                                            Company Representative
                                        </label>
                                        <label>
                                            <input type="radio" 
                                                   name="parties[${i}][members][0][role]" 
                                                   value="legal_counsel">
                                            Legal Counsel
                                        </label>
                                        <div style="display: flex;gap:5px;">
                                            <button type="button" class="add-member">+</button>
                                            <button type="button" class="remove-member">-</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        wrapper.appendChild(partyDiv);
                    }
                } else if (count < currentCount) {
                    // Remove parties from the end
                    for (let i = currentCount - 1; i >= count; i--) {
                        currentPartyNodes[i].remove();
                    }
                }
            });

            // Initial render
            //adjustPartyFields(parseInt(numPartiesInput.value) || 1);



            // AJAX form submit
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                jQuery('#case-details-form button[type="submit"]').addClass('loading');
                const formData = new FormData(form);
                formData.append('action', 'save_case_details_ajax'); // WordPress AJAX action

                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            formIsDirty = false; // Reset warning flag
                            jQuery('#case-details-form button[type="submit"]').prop('disabled', false).removeClass('loading');
                            messageContainer.innerHTML = '<div class="woocommerce-message" style="display:block">' + data.data.message + '</div>';
                            form.reset();
                            adjustPartyFields(1);
                            const orderUrl = '<?php echo esc_url($order->get_view_order_url()); ?>';

                            // Check for custom redirect from Yes/No warning intercept
                            if (window.pendingAction === 'redirect' && window.pendingRedirect) {
                                window.location.href = window.pendingRedirect;
                            } else if (window.pendingAction === 'close') {
                                window.location.reload();
                            } else if (window.location.href.includes("wp-admin/admin.php?page=wc-orders")) {
                                window.location.reload();
                            } else {
                                window.location.href = orderUrl;
                            }

                        } else {
                            messageContainer.innerHTML = '<div class="woocommerce-error"  style="display:block">' + data.data.message + '</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageContainer.innerHTML = '<div class="woocommerce-error"  style="display:block">Something went wrong. Please try again.</div>';
                    });
            });

            jQuery(function ($) {
                $('#other_remarks').on('input', function () {
                    let max = 50;
                    let val = $(this).val();

                    if (val.length > max) {
                        $(this).val(val.substring(0, max));
                    }

                    $('#remarks_count').text($(this).val().length + " / " + max);
                });
            });



            //const form = document.getElementById('case-details-form');
            let caseIdInput, orderID;

            if (form) {
                caseIdInput = form.querySelector('input[name="case_id"]');
                orderID = form.querySelector('input[name="order_id"]').value;
            }

            const page = jQuery('body.woocommerce-view-order');
            if (page.length) return;

            // Now these are safe to check
            if (!caseIdInput || !form) return;

            const caseId = caseIdInput.value;
            if (!caseId) return;


            // AJAX request to check previous case
            const data = new FormData();
            data.append('action', 'get_user_case_by_id');
            data.append('case_id', caseId);
            data.append('order_id', orderID);

            fetch(wc_add_to_cart_params.ajax_url, { method: 'POST', body: data })
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data.exists) {
                        setTimeout(() => {
                            jQuery("#update-order-meta").show();
                        }, 1500);
                    }
                });

            jQuery(document).on('click', '#order-yes', function (e) {
                e.preventDefault();
                jQuery(this).addClass('loading');

                jQuery.post(phive_spinner.ajaxurl, {
                    action: 'copy_case_details',
                    case_id: caseId,
                    current_order_id: orderID
                }, function (response) {
                    if (!response.success) {
                        alert(response.data);
                        return;
                    }

                    // After receiving AJAX response
                    const data = response.data.form_data;
                    const form = document.getElementById('case-details-form');

                    // 1️⃣ Fill simple fields
                    form.querySelector('textarea[name="case_title"]').value = data.case_title || '';
                    form.querySelector('textarea[name="case_description"]').value = data.case_description || '';
                    form.querySelector('input[name="num_parties"]').value = data.total_parties || 1;
                    form.querySelector('input[name="num_members"]').value = data.total_participants || 1;

                    // 2️⃣ Build cachedParties
                    // After AJAX response
                    cachedParties = {};
                    if (Array.isArray(data.parties)) {
                        data.parties.forEach((party, i) => {
                            cachedParties[i] = {
                                name: party.name || '',
                                members: Array.isArray(party.members) && party.members.length > 0
                                    ? party.members.map(m => ({
                                        name: m.name || '',
                                        role: m.role || 'company_rep'
                                    }))
                                    : [{ name: '', role: 'company_rep' }] // default one member
                            };
                        });
                    }


                    // 3️⃣ Rebuild all party sections


                    // 4️⃣ After rebuild, ensure party names are set
                    Object.keys(cachedParties).forEach(i => {
                        const partyInput = wrapper.querySelector(`input[name="parties[${i}][name]"]`);
                        if (partyInput) partyInput.value = cachedParties[i].name;

                        cachedParties[i].members.forEach((member, mIdx) => {
                            const memberInput = wrapper.querySelector(`input[name="parties[${i}][members][${mIdx}][name]"]`);
                            if (memberInput) memberInput.value = member.name;

                            const roleInput = wrapper.querySelector(`input[name="parties[${i}][members][${mIdx}][role]"][value="${member.role}"]`);
                            if (roleInput) roleInput.checked = true;
                        });
                    });

                    adjustPartyFields(parseInt(data.total_parties) || 1);

                    // 4️⃣ Fill arbitrators correctly
                    const arbWrapper = document.querySelector('.arbitrators-wrapper');
                    if (arbWrapper && Array.isArray(data.arbitrators)) {
                        // Clear existing except first
                        const existing = arbWrapper.querySelectorAll('.arbitrator-input');
                        existing.forEach((el, idx) => { if (idx > 0) el.remove(); });

                        // Fill first input
                        if (existing[0]) existing[0].querySelector('input').value = data.arbitrators[0] || '';

                        // Add remaining arbitrators
                        data.arbitrators.slice(1).forEach(arb => {
                            // simulate add button click
                            const addBtn = arbWrapper.querySelector('.add-arbitrator');
                            if (addBtn) addBtn.click();
                            const inputs = arbWrapper.querySelectorAll('.arbitrator-input input');
                            inputs[inputs.length - 1].value = arb || '';
                        });
                    }
                    //adjustPartyFields(parseInt(data.total_parties) || 1);

                    // 5️⃣ Hide modal
                    jQuery('#update-order-meta').hide();

                });
            });

            jQuery(document).on('click', '#order-no', function (e) {
                e.preventDefault();
                jQuery("#update-order-meta").hide();
            });

        });
    </script>


    <?php
}

// Save case details via AJAX
add_action('wp_ajax_save_case_details_ajax', 'save_case_details_ajax');
add_action('wp_ajax_nopriv_save_case_details_ajax', 'save_case_details_ajax');

function save_case_details_ajax()
{
    if (!isset($_POST['case_details_nonce']) || !wp_verify_nonce($_POST['case_details_nonce'], 'save_case_details')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    $order_id = intval($_POST['order_id']);
    $order = wc_get_order($order_id);

    if (!$order) {
        wp_send_json_error(['message' => 'Invalid order.']);
    }

    // Sanitize inputs
    $case_title = sanitize_text_field($_POST['case_title'] ?? '');
    $case_id_field = sanitize_text_field($_POST['case_id'] ?? '');
    $case_description = sanitize_textarea_field($_POST['case_description'] ?? '');
    $other_rem = sanitize_textarea_field($_POST['other_remarks'] ?? '');
    $num_parties = $_POST['num_parties'] ?? 0;
    $total = $_POST['num_members'] ?? '';

    // Arbitrators (multiple)
    $arbitrators = [];
    if (!empty($_POST['arbitrators']) && is_array($_POST['arbitrators'])) {
        foreach ($_POST['arbitrators'] as $arb) {
            $arbitrators[] = sanitize_text_field($arb);
        }
    }

    // Parties (nested structure)
    $parties = [];
    if (!empty($_POST['parties']) && is_array($_POST['parties'])) {
        foreach ($_POST['parties'] as $party) {
            $clean_members = [];

            if (!empty($party['members']) && is_array($party['members'])) {
                foreach ($party['members'] as $member) {
                    $clean_members[] = [
                        'name' => sanitize_text_field($member['name'] ?? ''),
                        'wa' => sanitize_text_field($member['wa'] ?? ''),
                        'role' => (isset($member['role']) && in_array($member['role'], ['company_rep', 'legal_counsel'], true))
                            ? $member['role']
                            : 'company_rep',
                    ];
                }
            }

            $parties[] = [
                'name' => sanitize_text_field($party['name'] ?? ''),
                'members' => $clean_members,
            ];
        }
    }


    $has_phive_booking = false;

    // Save as order meta
    $order->update_meta_data('Case Title', $case_title);
    $order->update_meta_data('Case ID', $case_id_field);
    $order->update_meta_data('Case Description', $case_description);
    $order->update_meta_data('Total Parties Involved', $num_parties);
    $order->update_meta_data('Parties Involved', $parties); // structured array
    $order->update_meta_data('Arbitrators', $arbitrators); // array of arbitrators
    $order->update_meta_data('Total Participants', $total); // array of arbitrators 
    $order->update_meta_data('Other Remarks', $other_rem);

    // Loop through order items
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if ($product && $product->get_type() === 'phive_booking') {
            $item->update_meta_data('Case Title', $case_title);
            $item->update_meta_data('Case ID', $case_id_field);
            $item->update_meta_data('Total Participants', $total);
            $item->save();
        }
    }
    $order->save();

    wp_send_json_success(['message' => 'Case details submitted successfully!']);
}


// Show Case Details before billing details on Order Details page
//add_action('woocommerce_order_details_after_order_table', 'show_case_details_before_billing');
//add_action('woocommerce_admin_order_data_after_order_details', 'show_case_details_before_billing');
function show_case_details_before_billing($order)
{

    global $wp;

    // Check if we're on the "view-order" endpoint


    if (!$order instanceof WC_Order)
        return;

    if (isset($wp->query_vars['view-order'])) {
        $order_id = absint($wp->query_vars['view-order']);
        $orig_order = wc_get_order($order_id);
    }
    $order_id = $order->get_id();
    if ($orig_order) {
        $ord_meta = $orig_order->get_meta('group_parent_order');
    }

    echo '<div class="case-box">';
    echo '<div class="d-flex">';
    echo '<h3>Case Details</h3>';
    if (!$ord_meta) {
        echo '<button type="button" class="button woocommerce-button edit-case-details" data-order-id="' . esc_attr($order_id) . '">Update</button>';
    }
    echo '</div>';
    if (!is_wc_endpoint_url('order-received')) {
        // Get custom order meta values
        $case_name = $order->get_meta('Case Title');
        $case_id = $order->get_meta('Case ID');
        $case_desc = $order->get_meta('Case Description');
        $total = $order->get_meta('Total Participants');
        $total_parties = $order->get_meta('Total Parties Involved');
        $parties = $order->get_meta('Parties Involved'); // structured array
        $arbitrators = $order->get_meta('Arbitrators');  // array
        $other_rem = $order->get_meta('Other Remarks');

        // Try to get from current order items if missing
        if (empty($case_name) || empty($case_id) || empty($case_desc) || empty($total) || empty($total_parties) || empty($parties) || empty($arbitrators)) {
            foreach ($order->get_items() as $item_id => $item) {
                $product = $item->get_product();
                if ($product && $product->get_type() === 'phive_booking') {
                    $case_name = $case_name ?: $item->get_meta('Case Title');
                    $case_id = $case_id ?: $item->get_meta('Case ID');
                    $case_desc = $case_desc ?: $item->get_meta('Case Description');
                    $total = $total ?: $item->get_meta('Total Participants');
                    $total_parties = $total_parties ?: $item->get_meta('Total Parties Involved');
                    $parties = $parties ?: $item->get_meta('Parties Involved');
                    $arbitrators = $arbitrators ?: $item->get_meta('Arbitrators');
                    $other_rem = $other_rem ?: $item->get_meta('Other Remarks');
                    break;
                }
            }
        }

        // Try to get from parent order items if still missing
        if (empty($case_name) || empty($case_id) || empty($case_desc) || empty($total) || empty($total_parties) || empty($parties) || empty($arbitrators)) {
            $parent_id = $order->get_meta('group_parent_order');
            if ($parent_id) {
                $p_ord = wc_get_order($parent_id);
                if ($p_ord) {
                    foreach ($p_ord->get_items() as $item_id => $item) {
                        $product = $item->get_product();
                        if ($product && $product->get_type() === 'phive_booking') {
                            $case_name = $case_name ?: $item->get_meta('Case Title');
                            $case_id = $case_id ?: $item->get_meta('Case ID');
                            $case_desc = $case_desc ?: $item->get_meta('Case Description');
                            $total = $total ?: $item->get_meta('Total Participants');
                            $total_parties = $total_parties ?: $item->get_meta('Total Parties Involved');
                            $parties = $parties ?: $item->get_meta('Parties Involved');
                            $arbitrators = $arbitrators ?: $item->get_meta('Arbitrators');
                            $other_rem = $other_rem ?: $item->get_meta('Other Remarks');
                            break;
                        }
                    }
                }
            }
        }


        if (is_admin()) { ?>
            <style>
                #order_data .order_data_column {
                    width: 100%;
                }

                #order_data table.shop_table.my_account_orders.wc-case-details {
                    width: 100%;
                    text-align: left;
                }

                div#order_data button.button.woocommerce-button.edit-case-details {
                    display: none;
                }

                #order_data h3 {
                    display: inline-block;
                }
            </style>
        <?php }


        echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details cdf4">';

        // 1. Case Title (Only if set)
        if (!empty($case_name)) {
            echo '<thead><tr><th class="woocommerce-table__product-name product-name">Case Title</th><th class="woocommerce-table__product-table product-total" data-title="Case Title">' . esc_html($case_name) . '</th></tr></thead>';
        }

        echo '<tbody>';

        // 2. Case ID
        if (!empty($case_id)) {
            echo '<tr><td class="woocommerce-table__product-name product-name">Case ID</td><td class="woocommerce-table__product-table product-total" data-title="Case ID">' . esc_html($case_id) . '</td></tr>';
        }

        // 3. Case Description
        if (!empty($case_desc)) {
            echo '<tr><td class="woocommerce-table__product-name product-name">Case Description</td><td class="woocommerce-table__product-table product-total" data-title="Case Description">' . nl2br(esc_html($case_desc)) . '</td></tr>';
        }

        // 4. Arbitrators (Filter empty values)
        $filtered_arbitrators = !empty($arbitrators) && is_array($arbitrators) ? array_filter($arbitrators) : [];
        if (!empty($filtered_arbitrators)) {
            echo '<tr><td class="woocommerce-table__product-name product-name">Arbitrators</td><td class="woocommerce-table__product-table product-total" data-title="Arbitrators">' . esc_html(implode(', ', $filtered_arbitrators)) . '</td></tr>';
        }

        // 5. Total Members
        if (!empty($total)) {
            echo '<tr><td class="woocommerce-table__product-name product-name">Total Members</td><td class="woocommerce-table__product-table product-total" data-title="Total Members">' . esc_html($total) . '</td></tr>';
        }

        // 6. No of Parties
        if (!empty($total_parties)) {
            echo '<tr><td class="woocommerce-table__product-name product-name">No of Parties</td><td class="woocommerce-table__product-table product-total" data-title="No of Parties">' . esc_html($total_parties) . '</td></tr>';
        }

        // 7. Parties Details (The logic fix for your screenshot)
        if (!empty($parties) && is_array($parties)) {
            // Buffer the parties content first to see if we have anything to show
            $parties_html = '';

            foreach ($parties as $index => $party) {
                // --- LOGIC CHECK START ---
                // Check if Party Name exists
                // echo '<pre>';
                //     print_r($party);
                // echo '</pre>';
                $has_name = !empty($party['name']);

                // Check if any Members exist (filter out empty names)
                $valid_members = [];
                if (!empty($party['members']) && is_array($party['members'])) {
                    foreach ($party['members'] as $member) {
                        if (!empty($member['name'])) {
                            $valid_members[] = $member;
                        }
                    }
                }
                $has_members = !empty($valid_members);

                // If NO name and NO members, skip this party entirely (fixes the empty "Party 1" issue)
                if (!$has_name && !$has_members) {
                    continue;
                }
                // --- LOGIC CHECK END ---

                // If we are here, there is data to show. Add to buffer.
                //$parties_html .= '<strong>Party ' . ($index + 1) . '</strong><br>';

                if ($has_name) {
                    $parties_html .= 'Party Name: ' . esc_html($party['name']) . '<br>';
                }

                if ($has_members) {
                    $legal_counsels = [];
                    $company_reps = [];

                    foreach ($valid_members as $member) {
                        if ($member['role'] === 'legal_counsel') {
                            $legal_counsels[] = esc_html($member['name']);
                        } else {
                            $company_reps[] = esc_html($member['name']);
                        }

                        if ($member['name']) {
                            $parties_html .= '<span style="margin-top:5px;display: inline-block;">Name: ' . esc_html($member['name']) . '</span><br>';
                        }

                        if ($member['wa']) {
                            $parties_html .= 'Whatsapp Number: ' . esc_html($member['wa']) . '<br>';
                        }

                        if ($member['role'] === 'legal_counsel') {
                            $parties_html .= 'Role: Legal Counsel<br>';
                        } else {
                            $parties_html .= 'Role: Company Representatives<br>';
                        }


                    }



                    /*if (!empty($legal_counsels)) {
                        $parties_html .= 'Legal Counsel: ' . implode(', ', $legal_counsels) . '<br>';
                    }
                    if (!empty($company_reps)) {
                        $parties_html .= 'Company Representatives: ' . implode(', ', $company_reps) . '<br>';
                    }*/
                }
                $parties_html .= '<br>'; // spacing between parties
            }

            // Only echo the row if we actually created HTML for at least one party
            if (!empty($parties_html)) {
                echo '<tr><td class="woocommerce-table__product-name product-name">Parties Details</td><td class="woocommerce-table__product-table product-total" data-title="Parties Involved">' . $parties_html . '</td></tr>';
            }
        }

        // 8. Other Remarks
        if (!empty($other_rem)) {
            echo '<tr><td class="woocommerce-table__product-name product-name">Other Remarks</td><td class="woocommerce-table__product-table product-total" data-title="Other Remarks">' . esc_html($other_rem) . '</td></tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Add Edit button



        // Popup container
        echo '<div id="case-edit-popup-' . esc_attr($order_id) . '" class="case-edit-popup" style="display:none;">';
        show_case_details_form_on_thankyou($order_id);
        echo '</div>';


        ?>

        <style>
            .case-edit-popup {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .case-edit-popup .woocommerce-case-form {
                background: #fff;
                padding: 20px;
                max-height: 90vh;
                overflow-y: auto;
                border-radius: 8px;
                width: 100%;
                position: relative;
            }
        </style>

        <script>
            document.addEventListener("click", function (e) {
                if (e.target.classList.contains("edit-case-details")) {
                    const orderId = e.target.dataset.orderId;
                    document.getElementById("case-edit-popup-" + orderId).style.display = "flex";
                }
            });
        </script>
        <?php
    }
    echo '</div>';
}



// Shortcode: [header_account_dropdown]
function my_header_account_dropdown()
{
    ob_start(); ?>
    <style>
        @media only screen and (min-width:768px) {
            span.nav-cart-icon.nav-sprite {
                background-size: 300px;
                background-position: -5px -275px;
                width: 40px;
                height: 40px;
            }

            span#nav-cart-count {
                font-size: 15px;
                left: 15px;
                top: 7px;
            }
        }
    </style>
    <div class="head-group">
        <?php if (is_user_logged_in()):
            $current_user = wp_get_current_user();
            $first_name = $current_user->user_firstname;
            $user_id = $current_user->ID;
            $saved_carts = get_user_meta($user_id, '_saved_carts', true);
            if (empty($saved_carts) || !is_array($saved_carts)) {
                $saved_carts = array();
            }
            $total_cart = count($saved_carts);
            ?>
            <style>
                .cart-header .elementor-widget-image:before {
                    content: "<?php echo $total_cart; ?>";
                }
            </style>
            <a class="header-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                <div id="nav-cart-count-container">
                    <span id="nav-cart-count" aria-hidden="true"
                        class="nav-cart-count nav-cart-0 nav-progressive-attribute nav-progressive-content"><?php echo $total_cart; ?></span>
                    <span class="nav-cart-icon nav-sprite"></span>
                </div>
            </a>
        <?php endif; ?>
        <div class="header-account elementor-hidden-mobile  elementor-hidden-tablet">
            <!-- Always show icon -->
            <?php
            if (!is_page('login') && !is_page('register') && !is_user_logged_in()) {
                global $wp;
                $current_url = add_query_arg($_GET, home_url($wp->request));
                $redirect_to = "/?redirect_to=" . urlencode($current_url);
            } else {
                $redirect_to = "";
            } ?>
            <?php if (is_user_logged_in()): ?>
                <span class="account-icon  elementor-hidden-tablet">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/user.png" alt="" width="40px" height="40px">
                </span>
                <span class="username   elementor-hidden-tablet">
                    <?php echo $first_name; ?></span>

            <?php else: ?>
                <a class="elementor-button elementor-button-link elementor-size-sm"
                    href="<?php echo esc_url(home_url('login')); ?><?php echo $redirect_to; ?>">Login or Register</a>
            <?php endif; ?>


            <ul class="account-dropdown">
                <?php if (is_user_logged_in()): ?>
                    <!-- <li><a href="<? php// echo esc_url(wc_get_cart_url()); ?>">
                            <div id="nav-cart-count-container">
                                <span id="nav-cart-count" aria-hidden="true"
                                    class="nav-cart-count nav-cart-0 nav-progressive-attribute nav-progressive-content"><?php //echo $total_cart; ?></span>
                                <span class="nav-cart-icon nav-sprite"></span>
                            </div> Cart
                        </a></li> -->
                    <li class="check-btn"><a href="<?php echo esc_url(wc_get_checkout_url()); ?>">
                            <!-- <i class="dashicons dashicons-cart"></i> --><svg xmlns="http://www.w3.org/2000/svg" width="28"
                                height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="11" rx="2" ry="2" />
                                <line x1="2" y1="9" x2="18" y2="9" />
                                <line x1="6" y1="13" x2="6.01" y2="13" />
                                <line x1="9" y1="13" x2="13" y2="13" />
                            </svg>
                            Checkout
                        </a></li>
                    <li><a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>">
                            <i class="dashicons dashicons-admin-users"></i> My Bookings
                        </a></li>
                    <li><a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>">
                            <i class="dashicons dashicons-location-alt"></i> Addresses
                        </a></li>
                    <li><a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>">
                            <i class="dashicons dashicons-admin-generic"></i> Account Details
                        </a></li>
                    <!-- <li><a href="<?php //echo esc_url(wc_get_account_endpoint_url('orders')); ?>">
                        <i class="dashicons dashicons-list-view"></i> Orders
                    </a></li> -->





                    <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                            <i class="dashicons dashicons-migrate"></i> Logout
                        </a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('header_account_dropdown', 'my_header_account_dropdown');

function my_load_dashicons_frontend()
{
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'my_load_dashicons_frontend');

//add_filter( 'woocommerce_checkout_fields', 'my_reorder_checkout_fields' );
function my_reorder_checkout_fields($fields)
{

    // Change priorities (lower number = appears first)
    $fields['billing']['billing_first_name']['priority'] = 10;
    $fields['billing']['billing_last_name']['priority'] = 20;
    //$fields['billing']['billing_company']['priority']    = 30;
    $fields['billing']['billing_phone']['priority'] = 40;
    $fields['billing']['billing_email']['priority'] = 50;
    //$fields['billing']['billing_address_2']['priority']  = 60;
    $fields['billing']['billing_country']['priority'] = 70;
    $fields['billing']['billing_state']['priority'] = 80;
    $fields['billing']['billing_address_1']['priority'] = 90;
    $fields['billing']['billing_city']['priority'] = 100;
    $fields['billing']['billing_postcode']['priority'] = 110;



    return $fields;
}

add_action('wp_head', 'phive_custom_checkout_button_css');
function phive_custom_checkout_button_css()
{
    // Bail if WooCommerce not active
    if (!function_exists('WC')) {
        return;
    }

    $has_phive_booking = false;

    // Loop through cart items
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if ($product && $product->get_type() === 'phive_booking') {
            $has_phive_booking = true;
            break;
        }
    }

    // If no phive_booking product, hide the button
    if (!$has_phive_booking) {
        ?>
        <style>
            .check-btn {
                display: none !important;
            }
        </style>
        <?php
    }
}

add_filter('woocommerce_account_menu_items', function ($items) {
    // Optional: remove the duplicate "my-bookings" endpoint tab if you already created one
    if (array_key_exists('my-bookings', $items)) {
        unset($items['my-bookings']);
    }
    if (array_key_exists('orders', $items)) {
        unset($items['orders']);
    }
    if (array_key_exists('customer-logout', $items)) {
        unset($items['customer-logout']);
    }
    if (array_key_exists('downloads', $items)) {
        unset($items['downloads']);
    }
    return $items;
}, 99); // run later so it overrides



add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain === 'bookings-and-appointments-for-woocommerce') {
        if ($text === 'Unpaid') {
            return 'Pending';
        }
        if ($text === 'Total Participants') {
            return 'Total Members';
        }
    }
    if ($text === 'Your order was cancelled.') {
        return 'Your booking cancellation request has been received. We will process your request soon.';
    }
    return $translated;
}, 10, 3);

add_action('wp_ajax_get_user_case_by_id', 'get_user_case_by_id');

function get_user_case_by_id()
{
    // Ensure logged-in user
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'User not logged in']);
        wp_die();
    }

    $user_id = get_current_user_id();
    $case_id = $_POST['case_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    if (!$case_id) {
        wp_send_json_error(['message' => 'No Case ID provided']);
        wp_die();
    }

    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'meta_key' => 'Case ID',
        'meta_value' => $case_id,
        'post_type' => 'shop_order',
        'status' => array('wc-processing', 'wc-completed')
    ]);


    $matched_order = null;

    foreach ($orders as $order) {
        // Exclude the current order
        if ($order->get_id() == $order_id) {
            continue;
        }

        // Check items for Case ID
        foreach ($order->get_items() as $item) {
            if ($item->get_meta('Case ID') === $case_id) {
                $matched_order = $order;
                break 2; // stop search once found
            }
        }
    }

    if ($matched_order) {
        $case_data = [
            'case_name' => $matched_order->get_meta('Case Title'),
            'case_description' => $matched_order->get_meta('Case Description'),
            'total' => $matched_order->get_meta('Total Participants'),
            'num_parties' => $matched_order->get_meta('Total Parties Involved'),
            'parties' => $matched_order->get_meta('Parties Involved'),
            'arbitrators' => $matched_order->get_meta('Arbitrators'),
        ];

        wp_send_json_success([
            'exists' => true,
            'data' => $case_data,
        ]);
        wp_die();
    }

    wp_send_json_success(['exists' => false]);
    wp_die();
}

add_action('wp_ajax_copy_case_details', 'copy_case_details');
function copy_case_details()
{
    if (!is_user_logged_in())
        wp_send_json_error('Not logged in');

    $user_id = get_current_user_id();
    $case_id = sanitize_text_field($_POST['case_id'] ?? '');
    $current_order_id = absint($_POST['current_order_id'] ?? 0);

    if (!$case_id || !$current_order_id)
        wp_send_json_error('Invalid parameters');

    $prev_orders = wc_get_orders([
        'limit' => 1,
        'customer_id' => $user_id,
        'meta_key' => 'Case ID',
        'meta_value' => $case_id,
        'status' => ['wc-processing', 'wc-completed'],
        'exclude' => [$current_order_id]
    ]);

    if (empty($prev_orders))
        wp_send_json_error('No previous order found.');

    $prev_order = $prev_orders[0];

    $form_data = [
        'case_title' => $prev_order->get_meta('Case Title'),
        'case_description' => $prev_order->get_meta('Case Description'),
        'total_parties' => $prev_order->get_meta('Total Parties Involved') ?: 1,
        'total_participants' => $prev_order->get_meta('Total Participants'),
        'parties' => $prev_order->get_meta('Parties Involved') ?: [],
        'arbitrators' => $prev_order->get_meta('Arbitrators') ?: [],
    ];


    wp_send_json_success(['form_data' => $form_data]);
}



add_action('wp_ajax_nopriv_ajax_send_reg_otp', 'ajax_send_reg_otp');
add_action('wp_ajax_ajax_send_reg_otp', 'ajax_send_reg_otp');

function ajax_send_reg_otp()
{
    check_ajax_referer('ajax-reg-nonce', 'security');

    $phone_email = sanitize_text_field($_POST['phone_email'] ?? '');
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');

    if (empty($phone_email)) {
        wp_send_json_error([
            'message' => '<span style="color:red;">Enter email id or phone number.</span>'
        ]);
    }

    /* ----------------------------------------
     * 1. CHECK IF USER ALREADY EXISTS
     * ---------------------------------------- */

    // Case 1: Email check
    if (is_email($phone_email)) {

        if (email_exists($phone_email)) {
            wp_send_json_error([
                'message' => '<span style="color:red;">Account already exists with this email. Please login to continue.</span>'
            ]);
        }

    }
    // Case 2: Phone check (assuming phone stored in user meta)
    elseif (preg_match('/^[0-9]{8,15}$/', $phone_email)) {

        $user_query = new WP_User_Query([
            'meta_key' => 'phone', // 🔁 change if your meta key is different
            'meta_value' => $phone_email,
            'number' => 1,
            'fields' => 'ID',
        ]);

        if (!empty($user_query->get_results())) {
            wp_send_json_error([
                'message' => '<span style="color:red;">Account already exists with this phone number. Please login to continue.</span>'
            ]);
        }

    } else {
        wp_send_json_error([
            'message' => '<span style="color:red;">Invalid email id or phone number </span>'
        ]);
    }

    /* ----------------------------------------
     * 2. GENERATE & STORE OTP
     * ---------------------------------------- */

    $otp = rand(100000, 999999); // production
    //$otp = 111111; // testing

    set_transient(
        'otp_code_' . md5($phone_email),
        (string) $otp,
        3 * MINUTE_IN_SECONDS
    );

    $sub = "Accordhub - OTP for Account Registration";
    $e_heading = "New Account Registration";

    $msg = "<p>Dear Customer,</p>";
    $msg .= "<p>Your one time password for new account registration at Accordhub is</p>";
    $msg .= "<p class='otp_p'>" . esc_html($otp) . "</p>";
    $msg .= "<p>If you have not requested the OTP, please ignore this email.</p>";

    /* ----------------------------------------
     * 3. SEND OTP
     * ---------------------------------------- */

    if (is_email($phone_email)) {

        send_woocommerce_custom_email($phone_email, $sub, $e_heading, $msg);

        wp_send_json_success([
            'message' => 'OTP sent to email <b>' . esc_html($phone_email) . '</b>'
        ]);

    } else {
        $phone = preg_replace('/[^0-9]/', '', $phone_email);

        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            $phone = '91' . substr($phone, 1);
        }

        send_accordhub_otp($phone, $otp, 'register');
        $components = [
            [
                'type' => 'body',
                'parameters' => [['type' => 'text', 'text' => $otp]] // The OTP
            ],
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => [['type' => 'text', 'text' => $otp]] // Copy code button
            ]
        ];
        send_whatsapp_template_msg($phone, 'login_registration_otp', $components);
        wp_send_json_success([
            'message' => 'OTP sent to phone <b>' . esc_html($phone_email) . '</b>'
        ]);
    }
}



// Verify OTP + Create Customer
add_action('wp_ajax_nopriv_ajax_verify_reg_otp', 'ajax_verify_reg_otp');
add_action('wp_ajax_ajax_verify_reg_otp', 'ajax_verify_reg_otp');
function ajax_verify_reg_otp()
{
    check_ajax_referer('ajax-reg-verify-nonce', 'security_verify');

    $otp_entered = sanitize_text_field($_POST['otp_code'] ?? '');
    $phone_email = sanitize_text_field($_POST['phone_email'] ?? '');
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $username = sanitize_user($_POST['username'] ?? '');

    $key = 'otp_code_' . md5($phone_email);
    $stored = get_transient($key);

    custom_log("REG VERIFY Entered: $otp_entered | Stored: $stored | Key: $key");

    if (!$stored || $otp_entered !== $stored) {
        wp_send_json_error(['message' => '<span style="color:red;">Invalid OTP.</span>']);
    }

    // Delete OTP after use
    delete_transient($key);

    // If email, register with that
    if (is_email($phone_email)) {

        $email = $phone_email;
        $number = "";

        if (empty($username)) {
            // Take part before @
            $base_username = strtolower(current(explode('@', $email)));

            // Make it WordPress-safe
            $base_username = preg_replace('/[^a-z0-9_]/', '', $base_username);

            // Fallback
            if (empty($base_username)) {
                $base_username = 'user';
            }

            // Ensure unique username
            $username = $base_username;
            $i = 1;
            while (username_exists($username)) {
                $username = $base_username . $i;
                $i++;
            }
        }

    } else {
        $number = $phone_email;
        // If phone → create fake email (required by WP)
        $email = $phone_email . '@example.com';

        if (empty($username)) {
            $base_username = 'user_' . preg_replace('/[^0-9]/', '', $phone_email);

            // Ensure unique username
            $username = $base_username;
            $i = 1;
            while (username_exists($username)) {
                $username = $base_username . $i;
                $i++;
            }
        }
    }


    // Create new customer
    $password = wp_generate_password();
    $customer_id = wc_create_new_customer($email, $username, $password);

    if (is_wp_error($customer_id)) {
        wp_send_json_error(['message' => '<span style="color:red;">' . $customer_id->get_error_message() . '</span>']);
    }

    // Update user meta
    wp_update_user([
        'ID' => $customer_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
    ]);

    update_user_meta($customer_id, 'phone', $number);
    update_user_meta($customer_id, 'billing_first_name', $first_name);
    update_user_meta($customer_id, 'billing_last_name', $last_name);
    update_user_meta($customer_id, 'billing_phone', $number);


    $user_email = $email;
    $user_phone = $number;

    // Set up the email parameters
    $admin_email = 'admin@accordhub.in';
    $sales_email = 'sales@accordhub.in';
    $subject = 'New User Registration';
    $heading = 'New User Registration';

    // Construct the email body
    $msg = 'A new user has successfully registered on your site.<br><br>';
    $msg .= '<strong>Name:</strong> ' . $first_name . ' ' . $last_name . '<br>';
    if (strpos($user_email, 'example.com') === false) {
        $msg .= '<strong>Email:</strong> ' . $user_email . '<br>';
    }
    if ($user_phone !== '') {
        $msg .= '<strong>Phone No.:</strong> ' . $user_phone . '<br>';
    }

    // Execute your custom email function
    if (function_exists('send_woocommerce_custom_email')) {
        send_woocommerce_custom_email_to_admin($admin_email, $subject, $heading, $msg);
        send_woocommerce_custom_email_to_admin($sales_email, $subject, $heading, $msg);
        send_woocommerce_custom_email_to_admin('pratiksha@ycspl.in', $subject, $heading, $msg);
    }

    // Auto login new customer
    wp_set_auth_cookie($customer_id);

    wp_send_json_success(['message' => '<span style="color:green;">Registration successful. Logging in...</span>']);

}

add_filter('woocommerce_display_item_meta', function ($html, $item, $args) {
    // Meta keys to skip
    $skip_keys = ['Case Title', 'Case ID', 'Booking Status', 'Total Members'];

    $strings = [];

    foreach ($item->get_formatted_meta_data() as $meta_id => $meta) {
        if (in_array($meta->display_key, $skip_keys, true)) {
            continue; // skip these keys
        }

        $value = $args['autop']
            ? wp_kses_post($meta->display_value)
            : wp_kses_post(make_clickable(trim($meta->display_value)));

        $strings[] = $args['label_before'] . esc_html($meta->display_key) . $args['label_after'] . $value;
    }

    if ($strings) {
        $html = $args['before'] . implode($args['separator'], $strings) . $args['after'];
    } else {
        $html = '';
    }

    return $html;
}, 10, 3);

add_filter('woocommerce_valid_order_statuses_for_cancel', function ($statuses, $order) {
    $statuses[] = 'processing';
    $statuses[] = 'completed';
    return $statuses;
}, 10, 2);

add_filter('woocommerce_add_cart_item_data', 'add_final_cart_flag', 10, 3);
function add_final_cart_flag($cart_item_data, $product_id, $variation_id)
{
    if (isset($_POST['final-cart']) && $_POST['final-cart'] == '1') {
        $cart_item_data['final_cart'] = 1;
    }
    if (isset($_POST['fresh-cart']) && $_POST['fresh-cart'] == '1') {
        $cart_item_data['fresh_cart'] = 1;
    }
    return $cart_item_data;
}

add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout_after_add_to_cart');

function redirect_to_checkout_after_add_to_cart($url)
{
    // Only redirect if "final cart" is set in POST
    if (isset($_POST['final-cart']) && $_POST['final-cart'] == '1') {
        return wc_get_checkout_url(); // redirect to checkout page
    }
    return $url; // default behavior for other cases
}

add_action('woocommerce_add_to_cart', 'auto_save_each_phive_booking_cart', 20, 6);
function auto_save_each_phive_booking_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    if (!is_user_logged_in())
        return;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if ($product && $product->get_type() === 'phive_booking') {
            // If any product in the cart is of type 'phive_booking', remove session key
            if (WC()->session->get('parent_order')) {
                WC()->session->__unset('parent_order');
                // Optional debug log
                // custom_log('parent_order removed because phive_booking product added to cart');
            }
            break; // No need to check further
        }
    }

    // Check for final cart flag
    if (!empty($cart_item_data['final_cart']))
        return; // skip saving

    $user_id = get_current_user_id();
    $cart_items = WC()->cart->get_cart();
    if (empty($cart_items))
        return;

    // Get existing saved carts
    $saved_carts = get_user_meta($user_id, '_saved_carts', true);
    if (!is_array($saved_carts))
        $saved_carts = [];

    foreach ($cart_items as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        $booked_from = $cart_item['phive_display_time_from'] ?? '';
        $booked_to = $cart_item['phive_display_time_to'] ?? '';

        if ($product && $product->get_type() === 'phive_booking') {
            $date2 = date('F j, Y', strtotime($booked_from));
            $time_from = date('g:i a', strtotime($booked_from));
            $time_to = date('g:i a', strtotime($booked_to));
            $booked_datetime = $date2 . ' (' . $time_from . ' – ' . $time_to . ')';
            $name = $product->get_name();
            $cart_name = $name . ' - ' . $booked_datetime;

            // Only create new cart if it does not exist
            if (!isset($saved_carts[$cart_name])) {
                $saved_carts[$cart_name] = [
                    'items' => [$cart_item], // Save main phive_booking as first item
                    'remarks' => [],           // Empty remarks for now
                ];
            } else {
                // Cart exists → merge items
                $existing_items = $saved_carts[$cart_name]['items'];
                $new_items = [];

                // Keep only one phive_booking: use the latest one (current $cart_item)
                foreach ($existing_items as $item) {
                    $prod = wc_get_product($item['product_id']);
                    if ($prod && $prod->get_type() !== 'phive_booking') {
                        $new_items[] = $item; // Keep add-ons
                    }
                }

                // Add the latest phive_booking (current one)
                $new_items[] = $cart_item;

                // Update saved cart
                $saved_carts[$cart_name]['items'] = $new_items;
            }
            // Otherwise, do nothing — do NOT overwrite or merge
        }
    }

    //update_user_meta($user_id, '_saved_carts', $saved_carts);
}


// <a class="open-addon-popup button wc-forward" data-cart-name="' . $cart_name . '">Add-ons</a>
// <a class="saved_cart_calander button wc-forward" href="' . $product_url . '?date=' . $date . '&slot=' . $slot . '&members=' . $members . '&case_id=' . $case_id . '&case_title=' . $case_title . '&product_id=' . $main_product['product_id'] . '" data-cart-name="' . $cart_name . '">Date & Time</a>

// Shortcode to list saved carts
add_shortcode('my_saved_carts', function () {
    $saved_carts = get_saved_carts_for_user();
    return $saved_carts;
});

// Save current cart
add_action('wp_ajax_save_cart', 'my_save_cart_ajax');
add_action('wp_ajax_nopriv_save_cart', 'my_save_cart_ajax');

function my_save_cart_ajax()
{
    if (!is_user_logged_in())
        wp_send_json_error('Login required.');

    $user_id = get_current_user_id();
    $cart_name = sanitize_text_field($_POST['cart_name'] ?? '');
    $cart_items = WC()->cart->get_cart();

    if (empty($cart_items))
        wp_send_json_error('Cart is empty.');

    $saved_carts = get_user_meta($user_id, '_saved_carts', true);
    if (!is_array($saved_carts))
        $saved_carts = [];

    if (empty($cart_name)) {
        $cart_name = 'Cart ' . date('Y-m-d H:i:s');
    }

    $saved_carts[$cart_name] = $cart_items;
    update_user_meta($user_id, '_saved_carts', $saved_carts);

    wp_send_json_success('Cart saved as: ' . $cart_name);
}

add_action('wp_ajax_restore_cart', 'restore_full_saved_cart');
add_action('wp_ajax_nopriv_restore_cart', 'restore_full_saved_cart');

function restore_full_saved_cart()
{
    if (!is_user_logged_in())
        wp_send_json_error('Login required.');

    $user_id = get_current_user_id();
    $cart_name = sanitize_text_field($_POST['cart_name'] ?? '');
    $saved_carts = get_user_meta($user_id, '_saved_carts', true);

    if (empty($saved_carts) || !isset($saved_carts[$cart_name])) {
        wp_send_json_error('Cart not found.');
    }

    $saved_cart = $saved_carts[$cart_name];
    $cart_items = $saved_cart['items'] ?? [];   // ✅ only items
    $remarks = $saved_cart['remarks'] ?? []; // ✅ extract remarks too

    // Empty current cart fully
    WC()->cart->empty_cart();
    WC()->cart->set_session();

    // Restore each saved item
    foreach ($cart_items as $key => $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'] ?? 1;
        $variation_id = $item['variation_id'] ?? 0;
        $variation = $item['variation'] ?? [];
        $cart_item_data = $item;

        // Remove WC internal object
        if (isset($cart_item_data['data'])) {
            unset($cart_item_data['data']);
        }

        $cart_item_data['final_cart'] = 1;

        WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);
    }

    // ✅ Restore remarks into session for checkout addon UI
    WC()->session->set('addon_category_remarks', $remarks);

    WC()->cart->set_session();

    wp_send_json_success('Cart restored: ' . $cart_name);
}



// AJAX hook for deleting a saved cart
add_action('wp_ajax_delete_saved_cart', 'delete_saved_cart_ajax');
//add_action('wp_ajax_nopriv_delete_saved_cart', 'delete_saved_cart_ajax');

function delete_saved_cart_ajax()
{
    if (!is_user_logged_in())
        wp_send_json_error('Login required.');

    $user_id = get_current_user_id();
    $cart_name = sanitize_text_field($_POST['cart_name'] ?? '');

    $saved_carts = get_user_meta($user_id, '_saved_carts', true);
    $saved_carts = is_array($saved_carts) ? $saved_carts : [];
    if (empty($saved_carts) || !isset($saved_carts[$cart_name])) {
        wp_send_json_error('Cart not found.');
    }

    // Remove the cart
    unset($saved_carts[$cart_name]);

    // Update user meta
    update_user_meta($user_id, '_saved_carts', $saved_carts);

    wp_send_json_success('Cart deleted: ' . $cart_name);
}

add_action('wp_ajax_load_addon_popup', 'ajax_load_addon_popup');
add_action('wp_ajax_nopriv_load_addon_popup', 'ajax_load_addon_popup');

function ajax_load_addon_popup()
{
    // error_reporting(E_ALL); // Report all PHP errors
    // ini_set('display_errors', 1);
    if (!is_user_logged_in()) {
        wp_send_json_error('Login required.');
    }

    $cart_name = sanitize_text_field($_POST['cart_name'] ?? '');
    if (empty($cart_name)) {
        wp_send_json_error('Cart not specified.');
    }

    $user_id = get_current_user_id();
    $saved_carts = get_user_meta($user_id, '_saved_carts', true);

    if (empty($saved_carts[$cart_name]) && $cart_name !== 'no_cart') {
        wp_send_json_error('Saved cart not found.');
    }

    $saved_cart = $saved_carts[$cart_name];

    // Call your existing popup generation function and pass $saved_cart
    ob_start();
    custom_checkout_addon_services($saved_cart, $cart_name);
    $popup_html = ob_get_clean();

    wp_send_json_success($popup_html);
}

add_action('wp_ajax_load_addon_popup2', 'ajax_load_addon_popup2');
add_action('wp_ajax_nopriv_load_addon_popup2', 'ajax_load_addon_popup2');

function ajax_load_addon_popup2()
{
    // error_reporting(E_ALL); // Report all PHP errors
    // ini_set('display_errors', 1);
    if (!is_user_logged_in()) {
        wp_send_json_error('Login required.');
    }

    $cart_name = sanitize_text_field($_POST['cart_name'] ?? '');
    $member = sanitize_text_field($_POST['participant'] ?? '0');
    if (empty($cart_name)) {
        wp_send_json_error('Cart not specified.');
    }

    $user_id = get_current_user_id();
    $saved_carts = get_user_meta($user_id, '_saved_carts', true);
    if (!is_array($saved_carts)) {
        $saved_carts = [];
    }
    if (empty($saved_carts[$cart_name]) && $cart_name !== 'no_cart') {
        wp_send_json_error('Saved cart not found.');
    }



    $saved_cart = $saved_carts[$cart_name];

    // Call your existing popup generation function and pass $saved_cart
    ob_start();
    custom_checkout_addon_services2($member);
    $popup_html = ob_get_clean();

    wp_send_json_success($popup_html);
}

add_action('wp_ajax_add_addon_products_to_saved_cart', 'add_addon_products_to_saved_cart');
add_action('wp_ajax_nopriv_add_addon_products_to_saved_cart', 'add_addon_products_to_saved_cart');

function add_addon_products_to_saved_cart()
{
    if (!is_user_logged_in())
        wp_send_json_error('Login required.');

    $user_id = get_current_user_id();
    $cart_name = sanitize_text_field($_POST['cart_name'] ?? '');
    $addons = $_POST['addons'] ?? [];
    $remarks = $_POST['remarks'] ?? [];

    if (empty($cart_name))
        wp_send_json_error('Cart name missing.');

    $saved_carts = get_user_meta($user_id, '_saved_carts', true);
    if (empty($saved_carts[$cart_name]))
        wp_send_json_error('Saved cart not found.');

    $cart_data = $saved_carts[$cart_name];

    // ✅ Extract items and remarks separately
    $cart_items = $cart_data['items'] ?? $cart_data; // backward compatible
    $cart_remarks = $cart_data['remarks'] ?? [];

    // ✅ If all addons qty = 0 AND no remarks → remove addon items
    $all_qty_zero = true;
    foreach ($addons as $addon) {
        if (intval($addon['qty']) > 0) {
            $all_qty_zero = false;
            break;
        }
    }

    if ($all_qty_zero && empty(array_filter($remarks))) {
        $cart_items = array_filter($cart_items, function ($item) {
            $product_id = $item['product_id'];
            $terms = get_the_terms($product_id, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    if ($term->slug === 'addons' || $term->parent === get_term_by('slug', 'addons', 'product_cat')->term_id) {
                        return false; // remove addon
                    }
                }
            }
            return true; // keep non-addon
        });

        $remarks = []; // clear remarks if nothing left
    }

    // ✅ Update remarks into saved cart
    if (!empty($remarks)) {
        $cart_remarks = $remarks;
    }

    // ✅ Add/update addon products in saved cart
    foreach ($addons as $addon) {
        $product_id = intval($addon['id']);
        $new_qty = intval($addon['qty']);
        if (!$product_id)
            continue;

        $found = false;
        foreach ($cart_items as &$item) {
            $item_product_id = $item['product_id'];
            if ($item_product_id == $product_id) {
                $found = true;
                if ($new_qty > 0) {
                    $item['quantity'] = $new_qty;
                } else {
                    $item = null; // mark for removal
                }
                break;
            }
        }

        if (!$found && $new_qty > 0) {
            // Add new addon item
            $cart_items[] = [
                'product_id' => $product_id,
                'quantity' => $new_qty
            ];
        }
    }

    // Remove null items
    $cart_items = array_filter($cart_items);

    // ✅ Save updated cart (items + remarks together)
    $saved_carts[$cart_name] = [
        'items' => $cart_items,
        'remarks' => $cart_remarks
    ];

    update_user_meta($user_id, '_saved_carts', $saved_carts);

    wp_send_json_success('Add-ons and remarks updated successfully in the cart.');
}

add_action('wp_ajax_load_addon_order', 'ajax_load_addon_order');
add_action('wp_ajax_nopriv_load_addon_order', 'ajax_load_addon_order');

function ajax_load_addon_order()
{
    //     error_reporting(E_ALL); // Report all PHP errors
// ini_set('display_errors', 1);
    if (!is_user_logged_in()) {
        wp_send_json_error('Login required.');
    }

    $cart_name = sanitize_text_field($_POST['cart_name'] ?? '');
    if (empty($cart_name)) {
        wp_send_json_error('Cart not specified.');
    }

    $user_id = get_current_user_id();

    if ($cart_name == 'no_cart') {
        $saved_cart = [];
    }

    if (isset($_POST['cart_name']) && $_POST['cart_name'] == 'details') {
        $cart_name = 'details';
    }

    // Call your existing popup generation function and pass $saved_cart
    ob_start();
    custom_checkout_addon_services($saved_cart, $cart_name);
    $popup_html = ob_get_clean();

    wp_send_json_success($popup_html);
}


add_action('wp_ajax_create_new_order', 'create_new_order_with_parent');
add_action('wp_ajax_nopriv_create_new_order', 'create_new_order_with_parent');

function create_new_order_with_parent()
{
    // 1. Basic Security & Validation
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Login required.']);
    }

    $user_id = get_current_user_id();

    // Get submitted data
    $parent_order_id = intval($_POST['ord_id'] ?? 0);
    $cart_items = $_POST['addons'] ?? [];
    $remarks = $_POST['remarks'] ?? [];

    if (!$parent_order_id) {
        wp_send_json_error(['message' => 'Order ID missing.']);
    }

    $order = wc_get_order($parent_order_id);

    if (!$order) {
        wp_send_json_error(['message' => 'Order not found.']);
    }

    // 2. CRITICAL: Ensure the logged-in user owns this order
    // We allow admins to pass this check, but customers must match the ID.
    if ($order->get_customer_id() != $user_id && !current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Unauthorized access to this booking.']);
    }

    if (empty($cart_items)) {
        wp_send_json_error(['message' => 'No products selected.']);
    }

    // 3. Fetch Existing Addon Data from Meta
    $addons_data = $order->get_meta('_meeting_addons_data');
    if (!is_array($addons_data)) {
        $addons_data = [];
    }

    $current_wp_time = current_time('timestamp');
    $batch_id = $current_wp_time; // Group this submission together
    $items_added_count = 0;

    foreach ($cart_items as $item) {
        $product_id = intval($item['id']);
        $qty = intval($item['qty']);

        if ($product_id && $qty > 0) {
            $product = wc_get_product($product_id);
            if ($product) {
                $price = (float) $product->get_price();
                $terms = get_the_terms($product_id, 'product_cat');

                // 1. MUST Reset to empty string so it doesn't copy the previous item's remark
                $remark = '';

                // 2. Safely check if terms exist
                if ($terms && !is_wp_error($terms) && isset($terms[0])) {
                    $cat_slug = $terms[0]->slug;

                    // 3. Safely check if a remark was actually typed for this category
                    if (isset($remarks[$cat_slug]) && !empty($remarks[$cat_slug])) {
                        $remark = sanitize_text_field($remarks[$cat_slug]);
                    }
                }

                $addons_data[] = [
                    'id' => uniqid('addon_'), // Unique ID for this specific line
                    'batch_id' => $batch_id,
                    'product_id' => $product_id,
                    'product_category' => ($terms && !is_wp_error($terms)) ? $terms[0]->name : '',
                    'product_name' => $product->get_name(),
                    'qty' => $qty,
                    'price' => $price,
                    'line_total' => $price * $qty,
                    'status' => 'order_placed',       // Default status
                    'billed_status' => 'pending',    // Needs to be billed by admin
                    'timestamp' => $current_wp_time,
                    'remark' => $remark,
                    'added_by' => 'Website',    // Flag to know who added it
                ];
                $items_added_count++;
            }
        }
    }

    if ($items_added_count === 0) {
        wp_send_json_error(['message' => 'No valid items added.']);
    }

    // 5. Update Remarks (Directly to Order Meta)
    // Note: We overwrite remarks for the category to keep the key unique per category.
    if (!empty($remarks) && is_array($remarks)) {
        foreach ($remarks as $slug => $text) {
            $term = get_term_by('slug', $slug, 'product_cat');
            if ($term) {
                $key = 'Remarks for ' . $term->name;
                // Only update if not empty, otherwise we might delete previous instructions?
                // Usually, for a new batch, we might want to append or just save. 
                // Here we save/overwrite the specific category remark.
                if (!empty($text)) {
                    $order->update_meta_data($key, sanitize_text_field($text));
                }
            }
        }
    }

    // 6. Save Data to Order
    $order->update_meta_data('_meeting_addons_data', $addons_data);
    $order->save();

    // 7. Success Response
    // 'redirect' => false tells your JS NOT to go to the checkout page
    wp_send_json_success([
        'message' => 'Items added to your booking successfully!',
        'redirect' => false
    ]);
}




add_action('woocommerce_order_status_changed', 'cancel_child_orders_with_parent', 10, 4);
function cancel_child_orders_with_parent($order_id, $old_status, $new_status, $order)
{
    if ($old_status === 'trash' || $old_status === 'pending') {
        return;
    }
    // Only trigger when parent order is cancelled
    if ($new_status !== 'cancelled') {
        return;
    }

    $order = wc_get_order($order_id);
    if ($order->get_meta('cancelled_unpaid') == 'yes') {
        return;
    }
    $order->update_meta_data('_phive_manual_payment_status', 'cancelled');
    $order->save();

    // Get child orders
    $child_orders = wc_get_orders([
        'limit' => -1,
        'status' => array_keys(wc_get_order_statuses()), // All statuses
        'meta_key' => '_parent_order_id',
        'meta_value' => $order_id,
    ]);

    // Cancel each child order
    foreach ($child_orders as $child_order) {
        if ($child_order && $child_order->get_status() !== 'cancelled') {
            $child_order->update_meta_data('_phive_manual_payment_status', 'cancelled');
            $child_order->save();
        }
    }
}

// Hook into order status change
add_action('woocommerce_order_status_changed', 'phive_handle_group_cancellation_and_emails', 10, 4);

function phive_handle_group_cancellation_and_emails($order_id, $old_status, $new_status, $order)
{

    if ($old_status === 'trash') {
        return;
    }
    // 1. Only run if status is 'cancelled' OR 'refunded'
    if (!in_array($new_status, ['cancelled', 'refund-processed'])) {
        return;
    }

    if (!in_array($new_status, ['failed'])) {
        $order->update_meta_data('_phive_manual_payment_status', 'cancelled');
    }

    if (!$order->get_meta('phive_cancellation_requested_at') && $order->has_status(['cancelled'])) {
        $order->update_meta_data('phive_cancellation_requested_at', current_time('timestamp'));
        $order->save();
    }
    // 2. CHECK FOR SUPPRESS FLAG
    // If this order was cancelled automatically by the Parent process below, 
    // it will have this flag. We stop here to avoid sending a duplicate email.
    if ($order->get_meta('_phive_suppress_cancellation_email') === 'yes') {
        // Optional: Clean up the flag so it doesn't persist forever
        $order->delete_meta_data('_phive_suppress_cancellation_email');
        $order->save();
        return;
    }

    // 3. RETRIEVE GROUP DATA
    // We do NOT stop if it's not a group. We simply handle it as a single order.
    $additional_payers = $order->get_meta('group_additional_payers');
    $parent_cancel_time = $order->get_meta('phive_cancellation_requested_at');

    // 4. IF GROUP PARENT: UPDATE CHILDREN
    if (!empty($additional_payers) && is_array($additional_payers)) {
        foreach ($additional_payers as $payer) {
            $child_order_id = $payer['child_order_id'] ?? 0;

            if ($child_order_id) {
                $child_order = wc_get_order($child_order_id);

                // Update child if it's not already in the target state
                if ($child_order && !$child_order->has_status(['cancelled', 'refund-processed'])) {

                    // A. SET SUPPRESS FLAG
                    // This prevents this hook from running again for the child and sending a second email.
                    $child_order->update_meta_data('_phive_suppress_cancellation_email', 'yes');
                    if ($parent_cancel_time) {
                        $child_order->update_meta_data('phive_cancellation_requested_at', $parent_cancel_time);
                    }
                    $child_order->save();

                    // B. UPDATE STATUS
                    $note = "Auto-updated because the Main Booking (Parent Booking #{$order_id}) was marked as {$new_status}.";

                    if ($new_status === 'refund-processed') {
                        //$child_order->update_status('refund-processed', $note);
                    } else {
                        if ($order->get_meta('_cancellation_24') === 'yes') {
                            $child_order->update_meta_data('_cancellation_24', 'yes');
                            $child_order->save();
                        }
                        $child_order->update_status('cancelled', $note);
                    }
                }
            }
        }
    }

    if ($order->get_status() == 'cancelled') {
        $cancel_reason = '';
        if (isset($_POST['phive_cancellation_reason'])) {
            $cancel_reason = sanitize_textarea_field($_POST['phive_cancellation_reason']);
        }

        // Check if a user is logged in and performing this action
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();

            // Determine the display name based on user role
            if (in_array('administrator', $current_user->roles)) {
                $cancelled_by_name = 'Admin';
            } else {
                $first_name = get_user_meta($current_user->ID, 'first_name', true);
                $last_name = get_user_meta($current_user->ID, 'last_name', true);

                $cancelled_by_name = trim($first_name . ' ' . $last_name);
            }

            // 1. Add Note for Admin Panel Visibility
            $note = sprintf('❌ Booking cancelled by: <strong>%s</strong>', esc_html($cancelled_by_name));
            if (!empty($cancel_reason)) {
                $note .= sprintf('<br/><strong>Reason:</strong> %s', esc_html($cancel_reason));
            }
            $order->add_order_note($note, false);

            // 2. Save Data in Order Meta for easy fetching later
            $order->update_meta_data('_cancelled_by_user_id', $current_user->ID);
            $order->update_meta_data('_cancelled_by_user_name', $cancelled_by_name);
            if ($order->get_meta('cancelled_unpaid') !== 'yes') {
                $order->update_meta_data('_phive_manual_payment_status', 'cancelled');
            }

            if (!empty($cancel_reason)) {
                $order->update_meta_data('_cancellation_reason', $cancel_reason);
            }
            // Save the meta data to the database
            $order->save();
        } else {
            // Fallback if cancelled by system/guest asynchronously
            $note = '❌ Booking cancelled by: <strong>System/Cron</strong>';
            if (!empty($cancel_reason)) {
                $note .= sprintf('<br/><strong>Reason:</strong> %s', esc_html($cancel_reason));
            }
            $order->add_order_note($note, false);
            $order->update_meta_data('_cancelled_by_user_name', 'Admin');
            if ($order->get_meta('cancelled_unpaid') !== 'yes') {
                $order->update_meta_data('_phive_manual_payment_status', 'cancelled');
            }
            if (!empty($cancel_reason)) {
                $order->update_meta_data('_cancellation_reason', $cancel_reason);
            }

            $order->save();
        }
    }

    // 5. SEND EMAILS (Unified Logic)
    // This now works for:
    // - Group Parent (sends to Parent + All Children)
    // - Single Order (sends to Customer only, as $additional_payers is empty)
    phive_send_group_cancellation_emails($order, $additional_payers);
}

/**
 * Helper function to send emails to Parent + All Children
 */
/**
 * Helper function to send UNIFIED emails to Parent + All Children
 */
/**
 * Helper function to send Cancellation OR Refund emails
 * Updated to match specific templates for Cancelled vs Refunded (Split/Full)
 */
function phive_send_group_cancellation_emails($parent_order, $additional_payers)
{
    $parent_id = $parent_order->get_id();
    $order_status = $parent_order->get_status(); // 'cancelled' or 'refunded'
    $homeurl = get_bloginfo('url');


    // 1. EXTRACT BOOKING DETAILS
    // --------------------------
    $room_name = 'Room';
    $date_str = '';
    $time_range = '';
    $is_late_cancellation = false;

    foreach ($parent_order->get_items() as $item) {
        $product = $item->get_product();
        if ($product) {
            $room_name = $item->get_name();

            // Date & Time
            $start_raw = $item->get_meta('phive_display_time_from');
            $end_raw = $item->get_meta('phive_display_time_to');

            $start_val = is_array($start_raw) ? $start_raw[0] : $start_raw;
            $end_val = is_array($end_raw) ? $end_raw[0] : $end_raw;

            if ($start_val) {
                $ts_start = strtotime($start_val);
                if ($ts_start) {
                    $date_str = date('F j, Y', $ts_start);
                    $time_range = date('g:i a', $ts_start);
                    $now = current_time('timestamp');
                    $cancel_at = $parent_order->get_meta('phive_cancellation_requested_at');
                    $hours_until = ($ts_start - $cancel_at) / 3600;

                    // If booking is in the past OR less than 72 hours away
                    if ($hours_until < 72) {
                        $is_late_cancellation = true;
                    }
                } else {
                    $date_str = $start_val;
                }
            }
            if ($end_val) {
                $ts_end = strtotime($end_val);
                if ($ts_end) {
                    $time_range .= ' – ' . date('g:i a', $ts_end);
                }
            }
            break;
        }
    }

    $status_label = wc_get_order_status_name($order_status);
    $is_split = (!empty($additional_payers) && is_array($additional_payers));

    // 2. CALCULATE TOTAL GROUP REFUND (For Split Email)
    // -------------------------------------------------
    $total_group_refund = 0;

    // Parent Refund
    $total_group_refund += floatval($parent_order->get_total_refunded());

    // Children Refunds
    if ($is_split) {
        foreach ($additional_payers as $payer) {
            if (!empty($payer['child_order_id'])) {
                $c_order = wc_get_order($payer['child_order_id']);
                if ($c_order) {
                    $total_group_refund += floatval($c_order->get_total_refunded());
                }
            }
        }
    }
    $formatted_total_refund = wc_price($total_group_refund);

    // 3. DEFINE RECIPIENTS
    // --------------------
    $recipients = [];
    $total_children = 0;
    $unpaid_children = 0;
    $paid_children = 0;

    if (!$parent_order->get_date_paid()) {
        $unpaid_children++;
    } else {
        $paid_children++;
    }


    // Parent
    $p_email = $parent_order->get_billing_email();
    $p_phone = $parent_order->get_billing_phone();
    $booked_by = $parent_order->get_billing_first_name() . ' ' . $parent_order->get_billing_last_name();
    if ($parent_order->get_meta('_cancelled_by_user_name')) {
        $cancel_by = $parent_order->get_meta('_cancelled_by_user_name');
    } else {
        $cancel_by = $booked_by;
    }

    $cancel_reason = $parent_order->get_meta('_cancellation_reason');

    if ('yes' === $parent_order->get_meta('disable_customer_emails') || $parent_order->get_meta('_wc_order_attribution_source_type') === 'admin') {
        //return;
    }

    $parent_name = $parent_order->get_billing_first_name() . " " . $parent_order->get_billing_last_name();

    if ($p_email) {
        $recipients[] = [
            'type' => 'parent',
            'order' => $parent_order,
            'name' => $parent_order->get_billing_first_name(),
            'email' => $p_email,
            'phone' => $p_phone,
            'amount' => wc_price($parent_order->get_total_refunded() ?: $parent_order->get_total())
        ];
    }

    // Children (if split)
    if ($is_split) {
        $total_children = count($additional_payers) + 1;
        foreach ($additional_payers as $payer) {
            if (!empty($payer['email'])) {
                $child_amount = 'N/A';
                $child_order_obj = null;

                if (!empty($payer['child_order_id'])) {
                    $c_order = wc_get_order($payer['child_order_id']);
                    if ($c_order) {
                        $child_order_obj = $c_order;
                        $child_amount = wc_price($c_order->get_total_refunded() ?: $c_order->get_total());
                        if (!$c_order->get_date_paid()) {
                            $unpaid_children++;
                        } else {
                            $paid_children++;
                        }
                    } else {
                        $unpaid_children++;
                    }
                } else {
                    $unpaid_children++;
                }



                $recipients[] = [
                    'type' => 'child',
                    'order' => $child_order_obj,
                    'name' => $payer['name'] ?? 'Participant',
                    'email' => $payer['email'],
                    'phone' => $payer['phone'],
                    'amount' => $child_amount
                ];
            }
        }
    }

    // 4. SEND EMAILS (Based on Status)
    // --------------------------------
    foreach ($recipients as $person) {
        $p_name = esc_html($person['name']);
        $p_room = esc_html($room_name);
        $p_date = esc_html($date_str);
        $p_time = esc_html($time_range);
        $p_amt = $person['amount']; // "Amount Refund to You" / "Amount Refunded"
        $datetime = $p_date . ' (' . $p_time . ')';

        $order_url = '#';
        if ($person['order']) {
            $order_url = $person['order']->get_view_order_url();
        } else {
            continue;
        }

        // --- SCENARIO 1: CANCELLED (Initiated / Under Review) ---
        // "Booking cancelation is initiated by the Party (full or split both cases)"
        if ($order_status === 'cancelled' && !$person['order']->get_meta('_parent_order_id')) {
            if ($person['order']->get_meta('cancelled_unpaid') == 'yes') {
                $subject = "Your Room Booking has been Cancelled due to Incomplete Payment.";
                $heading = "Your Room Booking has been Cancelled due to Incomplete Payment.";

                $msg = "<p>Dear Customer,</p>";
                $msg .= "<p>Your room booking has been automatically cancelled because the payment was not received within the required 5-minute time limit.</p>";
            } elseif ($person['order']->get_meta('_cancellation_24') == 'yes') {
                $subject = "Accordhub – Your Booking has been Canceled (Booking ID: {$parent_id})";
                $heading = "Your Room Booking has been Cancelled due to Non-payment.";

                $msg = "<p>Dear Customer,</p>";
                $msg .= "<p>Your room booking has been canceled due to non-payment of the booking amount by {$unpaid_children} of the {$total_children} parties within the stipulated time-period.</p>";
                $msg .= "<p>Please find the booking cancellation details below:</p>";

                $msg .= "<p><strong>Booking ID:</strong> {$parent_id}</p>";
                $msg .= "<p><strong>Room:</strong> {$p_room}</p>";
                $msg .= "<p><strong>Date & Time:</strong> {$p_date} ({$p_time})</p>";
                $msg .= "<p><strong>Status:</strong> {$status_label}</p>";
                if (!empty($cancel_reason)) {
                    $msg .= "<p><strong>Reason of Cancellation:</strong> Non-payment by {$unpaid_children} of {$total_children} parties</p>";
                }

                $msg .= "<p>Please review our <a href='{$homeurl}/cancellation-and-refund-policy/'>cancellation policy</a> for more details.</p>";
                $msg .= "<p class='btn_p'><a href='{$order_url}'>View Booking Details</a></p>";

            } else {
                $subject = "Accordhub – Confirmation of Your Booking Cancellation (Booking ID: {$parent_id})";
                $heading = "Your Room Booking is Cancelled as Requested.";

                $msg = "<p>Dear Customer,</p>";
                $msg .= "<p>Your cancellation request for Room booking has been received. Therefore, we have cancelled your room booking.</p>";
                $msg .= "<p>Please find the booking details for the cancellation below:</p>";

                $msg .= "<p><strong>Booking ID:</strong> {$parent_id}</p>";
                $msg .= "<p><strong>Room:</strong> {$p_room}</p>";
                $msg .= "<p><strong>Date & Time:</strong> {$p_date} ({$p_time})</p>";
                $msg .= "<p><strong>Status:</strong> {$status_label}</p>";
                if ($person['order']->get_meta('group_parent_order') || $person['order']->get_meta('group_additional_payers')) {
                    $msg .= "<p><strong>Requested by:</strong> {$parent_name}</p>";
                }

                if (!empty($cancel_reason)) {
                    $msg .= "<p><strong>Reason of Cancellation:</strong> " . esc_html($cancel_reason) . "</p>";
                }


                $msg .= "<p>Please review our <a href='{$homeurl}/cancellation-and-refund-policy/'>cancellation policy</a> for more details.</p>";

                $msg .= "<p class='btn_p'><a href='{$order_url}'>View Booking Details</a></p>";
            }



            send_woocommerce_custom_email($person['email'], $subject, $heading, $msg);




            if (strlen($person['phone']) === 10) {
                $person['phone'] = '91' . $person['phone'];
            } elseif (strlen($person['phone']) === 11 && substr($person['phone'], 0, 1) === '0') {
                $person['phone'] = '91' . substr($person['phone'], 1);
            }
            // Send using your common function
            if ($person['order']->get_meta('cancelled_unpaid') !== 'yes' && $person['order']->get_meta('_cancellation_24') !== 'yes') {
                $components = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $p_name // {{1}} Name
                            ],
                            [
                                'type' => 'text',
                                'text' => (string) $parent_id // {{2}} Order ID
                            ],
                            [
                                'type' => 'text',
                                'text' => $p_room // {{3}} Room Name
                            ],
                            [
                                'type' => 'text',
                                'text' => $datetime // {{4}} Date & Time
                            ],
                            [
                                'type' => 'text',
                                'text' => 'Cancelled' // {{5}} Status
                            ],
                            [
                                'type' => 'text',
                                'text' => $cancel_by // {{5}} Status
                            ]
                        ]
                    ]
                ];
                send_whatsapp_template_msg($person['phone'], 'booking_cancellation_by_the_party', $components);
            }

            if ($person['order']->get_meta('cancelled_unpaid') !== 'yes' && $person['order']->get_meta('_cancellation_24') === 'yes') {
                $components = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $p_name // {{1}} Name
                            ],
                            [
                                'type' => 'text',
                                'text' => (string) $parent_id // {{2}} Order ID
                            ],
                            [
                                'type' => 'text',
                                'text' => $p_room // {{3}} Room Name
                            ],
                            [
                                'type' => 'text',
                                'text' => $datetime // {{4}} Date & Time
                            ],
                            [
                                'type' => 'text',
                                'text' => 'Cancelled' // {{5}} Status
                            ],
                            [
                                'type' => 'text',
                                'text' => $cancel_by // {{5}} Status
                            ]
                        ]
                    ]
                ];
                send_whatsapp_template_msg($person['phone'], 'booking_cancellation_by_the_party', $components);
            }

        }

        // --- SCENARIO 2: REFUNDED (Approved) ---
        elseif ($order_status === 'refund-processed') {

            $subject = "Accordhub - Room Booking Cancellation Request Approved (Booking ID - {$parent_id})";
            $heading = "Room Booking Cancellation Request Approved";

            // A. SPLIT PAYMENT (Group)
            if ($is_split) {
                $msg = "<p>Dear Customer,</p>";
                $msg .= "<p>Your cancellation request for Room booking has been approved. The amount will be refunded to you in 14 business days.</p>";
                $msg .= "<p>Please find the booking details for the cancellation below:</p>";

                $msg .= "<p><strong>Booking ID:</strong> {$parent_id}</p>";
                if (is_array($recipients) && count($recipients) > 1 && $person['order']->get_meta('group_parent_order')) {
                    $msg .= "<p><strong>Canceled By:</strong> {$cancel_by}</p>";
                }
                $msg .= "<p><strong>Room:</strong> {$p_room}</p>";
                $msg .= "<p><strong>Date & Time:</strong> {$p_date} ({$p_time})</p>";
                $msg .= "<p><strong>Status:</strong> {$status_label}</p>";
                $msg .= "<p><strong>Total Refund Application:</strong> {$formatted_total_refund}</p>";
                $msg .= "<p><strong>Amount Refund to You:</strong> {$p_amt}</p>";
            }
            // B. FULL PAYMENT (Single/Non-Group)
            else {
                $msg = "<p>Dear Customer,</p>";
                $msg .= "<p>Your cancellation request for Room booking has been approved. The amount will be refunded to you in 14 business days.</p>";
                $msg .= "<p>Please find the booking details for the cancellation below:</p>";

                $msg .= "<p><strong>Booking ID:</strong> {$parent_id}</p>";
                $msg .= "<p><strong>Room:</strong> {$p_room}</p>";
                $msg .= "<p><strong>Date & Time:</strong> {$p_date} ({$p_time})</p>";
                $msg .= "<p><strong>Status:</strong> {$status_label}</p>";
                $msg .= "<p><strong>Amount Refunded:</strong> {$p_amt}</p>";
            }

            // Common Footer for Refund
            $msg .= "<p>Please review our <a href='{$homeurl}/cancellation-and-refund-policy/'>cancellation policy</a> for more details.</p>";
            $msg .= "<p class='btn_p'><a href='{$order_url}'>View Booking Details</a></p>";

            send_woocommerce_custom_email($person['email'], $subject, $heading, $msg);
        }
    }
}

add_action('admin_init', function () {
    register_setting('wc_booking_cancel_group', 'wc_booking_cancel_options');
});


add_action('admin_menu', 'my_wc_add_options_page');
function my_wc_add_options_page()
{
    add_submenu_page(
        'woocommerce',                 // Parent slug
        'Cancelation Options',              // Page title
        'Cancelation Options',              // Menu title
        'manage_woocommerce',          // Capability
        'wc-cancel-options',           // Menu slug
        'my_wc_options_page_callback'  // Callback
    );
}

function my_wc_options_page_callback()
{
    $template_path = get_stylesheet_directory() . '/woocommerce/options.php';

    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo '<div class="error"><p>Template not found: ' . esc_html($template_path) . '</p></div>';
    }
}


function custom_checkout_addon_services2($member) // Pass current cart items if needed
{
    // echo "<pre>";
    // print_r($saved_cart);
    $parent_cat_slug = 'addons';
    $parent_term = get_term_by('slug', $parent_cat_slug, 'product_cat');

    if (!$parent_term) {
        return;
    }

    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => $parent_term->term_id,
    ]);

    if (empty($categories) || is_wp_error($categories)) {
        return;
    }

    $saved_remarks = [];
    $total_member = $member;
    $saved_cart = [];
    $cart_name = [];

    ?>
    <div id="extra-services-popup" style="display:none;">
        <div class="popup-content popup-content-room">
            <input type="hidden" name="total_member" id="total_member" value="<?php echo $total_member; ?>">
            <div class="close-icn" id="close-popup"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            <div class="addon-section">
                <h2>Add-ons for Hearing Day</h2>
                <p style="text-align: left;"><b>Total Members:</b> <span
                        class="count"><?php echo !empty($total_member) ? $total_member : 0; ?></span>
                    <a class="member-popup" href=""><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                            viewBox="0 0 24 17" fill="none" stroke="#1763B9" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                        </svg></a>
                </p>
                <table class="service-table" border="1" cellspacing="0" cellpadding="8">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Select Count</th>
                            <th>Select for All</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $placeholders = [
                            'support-services' => 'Eg: Add support service details (e.g., operator at 10:00 am)',
                            'meals' => 'Your meal can be customized later. ',
                            'refreshments' => 'Eg: Provide cookies/veg puff at 3:00 pm',
                            'stationery' => 'Eg: Place notepads and pens on each table',
                        ];
                        foreach ($categories as $category) {
                            echo '<tr class="category-row"><td colspan="5" style="font-weight:600;">' . esc_html($category->name) . '</td></tr>';

                            $products = wc_get_products([
                                'status' => 'publish',
                                'limit' => -1,
                                'category' => [$category->slug],
                            ]);

                            if (!empty($products)) {
                                foreach ($products as $product) {
                                    $product_id = $product->get_id();
                                    $price = $product->get_regular_price();
                                    $description = get_field('_description', $product_id);

                                    // ✅ find quantity in cart
                                    $existing_qty = 0;
                                    foreach ($saved_cart as $cart_item) {
                                        $cart_product_id = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
                                        if ($cart_product_id == $product_id) {
                                            $existing_qty = $cart_item['quantity'];
                                            break;
                                        }
                                    }

                                    // ✅ NEW: Set max="1" ONLY for Product ID 3015
                                    $max_attr = ($product_id == 3015) ? 'max="1"' : 'max="100"';
                                    $max_count = ($product_id == 3015) ? '1' : $total_member;
                                    $class = ($product_id == 3015) ? 'operator' : '';

                                    if ($product->get_name() != 'Printing') {
                                        echo '<tr class="service-row" data-price="' . esc_attr($price) . '">';
                                        echo '<td>' . esc_html($product->get_name());
                                        if ($description) {
                                            echo '<div class="service_desc">' . $description . '</div>';
                                        }
                                        echo '</td>';
                                        if ($product_id == 3015) {
                                            echo '<td data-title="Price">Free</td>';
                                        } else {
                                            echo '<td data-title="Price">₹' . esc_html($price) . '</td>';
                                        }

                                        echo '<td data-title="Select Count">
                    <input type="number" 
                        name="addon_qty[' . esc_attr($product_id) . ']" 
                        class="addon-qty" 
                        value="' . esc_attr($existing_qty) . '" 
                        data-original="' . esc_attr($existing_qty) . '"
                        min="0" ' . $max_attr . ' data-product_id="' . esc_attr($product_id) . '">
                  </td>';

                                        echo '<td data-title="Select for All">
                    <label class="switch">
                        <input type="checkbox" 
                            name="addon_all[' . esc_attr($product_id) . ']" 
                            class="addon-all b">
                        <span class="slider round ' . $class . '">' . $max_count . '</span>
                    </label>
                  </td>';

                                        $line_total = $price * $existing_qty;
                                        echo '<td data-title="Total Price" class="addon-total">₹' . esc_html($line_total) . '</td>';
                                        echo '</tr>';
                                    }
                                }
                            }

                            // ✅ Prefill remarks
                            $remark_val = isset($saved_remarks[$category->slug]) ? $saved_remarks[$category->slug] : '';
                            $placeholder = $placeholders[$category->slug];
                            echo '<tr class="remarks-row">
                            <td colspan="5"><div class="rem-box"><span class="rem-label">Add Remark:</span>
                                <textarea name="addon_remarks[' . esc_attr($category->slug) . ']" id="remark' . esc_attr($category->slug) . '" class="auto-height" 
                                        rows="1" 
                                        style="width:100%;" 
                                        data-original="' . esc_textarea($remark_val) . '"
                                        placeholder="' . $placeholder . '">'
                                . esc_textarea($remark_val) . '</textarea>
                            </div></td></tr>';
                        }
                        ?>

                        <?php
                        $brands = get_terms(array(
                            'taxonomy' => 'product_brand',
                            'hide_empty' => true,
                            'meta_query' => array(
                                array(
                                    'key' => '_enabledisable',
                                    'value' => 1,
                                    'compare' => '='
                                )
                            )
                        ));
                        if (!empty($brands) && !is_wp_error($brands)): ?>
                            <tr class="category-row">
                                <td colspan="5" style="font-weight:600;">Snacks & Light Bites</td>
                            </tr>
                            <tr class="category-row">
                                <td colspan="5" style="font-weight:600;">
                                    <div class="rm_tabs">

                                        <ul class="rm_tabs_ul">

                                            <!-- View All -->
                                            <li class="rm_tabs_li active" data-filter="all">
                                                <div class="rm_tabs_inner">
                                                    <span>View All</span>
                                                </div>
                                            </li>

                                            <?php foreach ($brands as $brand):
                                                // If you are using term meta for image
                                                $brand_image = "";
                                                $brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
                                                if ($brand_image_id) {
                                                    $brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
                                                }
                                                //print_r(get_term_meta( $brand->term_id ));
                                    
                                                // Fallback image (optional)
                                                if (empty($brand_image)) {
                                                    //$brand_image = get_stylesheet_directory_uri() . '/images/default-brand.svg';
                                                }
                                                ?>

                                                <li class="rm_tabs_li" data-filter="<?php echo esc_attr($brand->slug); ?>">
                                                    <div class="rm_tabs_inner">

                                                        <?php if (!empty($brand_image)): ?>
                                                            <img src="<?php echo esc_url($brand_image[0]); ?>"
                                                                alt="<?php echo esc_attr($brand->name); ?>">
                                                        <?php endif; ?>

                                                        <span><?php echo esc_html($brand->name); ?></span>
                                                    </div>
                                                </li>

                                            <?php endforeach; ?>

                                        </ul>

                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php
                        // Step 1: Get enabled brands
                        $enabled_brands = get_terms(array(
                            'taxonomy' => 'product_brand',
                            'hide_empty' => true,
                            'meta_query' => array(
                                array(
                                    'key' => '_enabledisable',
                                    'value' => '1',
                                    'compare' => '='
                                )
                            ),
                            'fields' => 'ids'
                        ));

                        // Step 2: Query products with those brands
                        $args = array(
                            'post_type' => 'product',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_brand',
                                    'field' => 'term_id',
                                    'terms' => $enabled_brands
                                ),
                            ),
                        );

                        $query = new WP_Query($args);

                        if ($query->have_posts()):
                            $row_count = 1;

                            while ($query->have_posts()):
                                $query->the_post();
                                $product = wc_get_product(get_the_ID());
                                $product_id = get_the_ID();
                                $existing_qty = 0;

                                foreach ($saved_cart as $cart_item) {
                                    $cart_product_id = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
                                    if ($cart_product_id == $product_id) {
                                        $existing_qty = $cart_item['quantity'];
                                        break;
                                    }
                                }

                                // ✅ NEW: Set max="1" ONLY for Product ID 3015
                                $max_attr = ($product_id == 3015) ? 'max="1"' : 'max="100"';
                                $max_count = ($product_id == 3015) ? '1' : $total_member;
                                $class = ($product_id == 3015) ? 'operator' : '';

                                $brands = get_the_terms(get_the_ID(), 'product_brand');
                                //print_r($brands);
                                $brand_slug = !empty($brands) && !is_wp_error($brands) ? $brands[0]->slug : '';
                                $brand_title = !empty($brands) && !is_wp_error($brands) ? $brands[0]->name : '-';

                                $image_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                                if (!$image_url) {
                                    $image_url = get_stylesheet_directory_uri() . '/images/hot-pan.svg';
                                }

                                // If you are using term meta for image
                                $brand_image = "";
                                $brand_image_id = get_term_meta($brands[0]->term_id, 'thumbnail_id', true);
                                if ($brand_image_id) {
                                    $brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
                                }
                                //print_r(get_term_meta( $brand->term_id ));
                    
                                // Fallback image (optional)
                                if (empty($brand_image)) {
                                    //$brand_image[0] = get_stylesheet_directory_uri() . '/images/hot-pan.svg';
                                }

                                $price = $product ? $product->get_price() : 0;
                                $price_html = $product ? $product->get_price_html() : '';
                                ?>

                                <tr id="rm_table_row_<?php echo esc_attr($row_count); ?>" class="service-row rm_table_row"
                                    data-filter="<?php echo esc_attr($brand_slug); ?>" data-price="<?php echo esc_attr($price); ?>">

                                    <td>
                                        <div class="rm_item_meta">
                                            <?php if (!empty($brand_image)) { ?>
                                                <div class="rm_item_image">
                                                    <img src="<?php echo esc_url($brand_image[0]); ?>"
                                                        alt="<?php echo esc_attr($brand_title); ?>">
                                                </div>
                                            <?php } ?>
                                            <?php the_title(); ?>
                                        </div>
                                    </td>

                                    <td data-title="Price"><?php echo $price_html; ?></td>

                                    <td data-title="Select Count">
                                        <?php
                                        echo '<input type="number" 
                                    name="addon_qty[' . esc_attr($product_id) . ']" 
                                    class="addon-qty" 
                                    value="' . esc_attr($existing_qty) . '" 
                                    data-original="' . esc_attr($existing_qty) . '"
                                    min="0" ' . $max_attr . ' data-product_id="' . esc_attr($product_id) . '">';
                                        ?>
                                    </td>

                                    <td data-title="Select for All">
                                        <?php
                                        echo '<label class="switch">
                                        <input type="checkbox" 
                                            name="addon_all[' . esc_attr($product_id) . ']" 
                                            class="addon-all a">
                                        <span class="slider round ' . $class . '">' . $max_count . '</span>
                                    </label>';
                                        ?>
                                    </td>

                                    <?php $line_total = $price * $existing_qty; ?>
                                    <td data-title="Total Price" class="addon-total">
                                        ₹ <?php echo esc_html($line_total); ?>
                                    </td>

                                </tr>

                                <?php
                                $row_count++;
                            endwhile;

                            wp_reset_postdata();

                        endif;
                        ?>

                    </tbody>
                </table>
                <p style="text-align:left;margin-bottom:0;color:#313131" class="boldonly">Note: Add-ons can be ordered
                    during the meetings as well. For meals- kindly let us know at the start of session or 1 hour in advance.
                </p>
            </div>
            <div id="btnss">
                <button data-cart-name="<?php echo $cart_name; ?>" id="proceed-btn">Add & Proceed</button>
                <button id="close-popup">Close</button>
            </div>
        </div>
        <div id="confirm-modal" style="display:none;">
            <div class="popup-contents">
                <p>Do you want to save the changes?</p>
                <div class="modal-actions">
                    <button data-cart-name="<?php echo $cart_name; ?>" id="confirm-yes" class="button">Yes</button>
                    <button id="confirm-no" class="button">No</button>
                </div>
            </div>
        </div>
    </div>
    <div id="update-booking-modal" style="display:none;">
        <div class="modal-content 1">
            <p>Do you want to change the Room Booking details?</p>
            <button id="booking-yes">Yes</button>
            <button id="booking-no">No</button>
        </div>
    </div>
    <div id="update-member-modal" style="display:none;">
        <div class="modal-content">
            <p>You have not added the participants for this booking.</p>
            <label for="member-count">Total Members
                <input type="number" name="member-count" id="member-count" min="1" step="1">
            </label>
            <button id="member-yes">Add Participants</button>
            <button id="member-no">Cancel</button>
        </div>
    </div>
    <?php
}
add_action('woocommerce_checkout_before_customer_details', function () {
    $parent_id = WC()->session->get('parent_order');
    if (!$parent_id) {
        ?>

        <div id="customer_details">
            <div id="group-payment-options">
                <h4>Would you like to split the payment between multiple Parties?</h4>
                <p>You can choose to divide the total amount equally among parties</p>
                <section class="d-flex">
                    <label>
                        <input type="radio" name="group_payment_mode" value="full" checked>
                        No, Pay in Full
                    </label>
                    <label>
                        <input type="radio" name="group_payment_mode" value="group">
                        Split equally among the parties
                    </label>
                </section>
                <p class="consent-line">By providing these details, you confirm that you have the consent of the involved
                    parties to share their information for this transaction.</p>
                <div class="count-member">
                    <label for="group_total_payers">Parties involved:</label>
                    <p class="form-row form-row-wide  thwcfd-required thwcfd-field-wrapper  validate-required"
                        style="margin: 0;">
                        <input type="number" id="group_total_payers" name="group_total_payers" min="2" max="15" value="2"
                            class="input-text">
                    </p>
                </div>

            </div>
            <div id="group-payment-fields" style="margin-top:15px;">
                <div id="group-members" style="margin-top:10px;"></div>
                <button type="button" id="add-party">Add Party</button>
            </div>
        </div>
        <?php
    }
});

add_action('woocommerce_checkout_after_customer_details', function () {
    ?>

    <div id="cancel_details" class="elementor-hidden-mobile elementor-hidden-tablet">
        <h3>Cancellation Policy</h3>
        <ul class="cp_ul">
            <li class="book-by">This booking is initiated by you, therefore, any further cancelation or changes can be
                managed by only you.</li>
            <li>Cancellations made more than 72 hours before the hearing receive a 50% refund.</li>
            <li>Cancellations within 72 hours and no-shows are fully chargeable and non-refundable.</li>
            <li>Split-payment bookings must be completed by all parties within 24 hours; failing which the booking is
                cancelled and all paid amounts are refunded.</li>
            <li>Add-ons already used are non-refundable.</li>
            <li>Eligible refunds are processed within 7 business days.</li>
        </ul>
        <p><a href="/cancellation-and-refund-policy/" target="_blank">Read full policy here</a></p>
    </div>
    <?php
});

add_action('woocommerce_after_checkout_form', function () {
    ?>

    <div id="cancel_details" class="elementor-hidden-desktop elementor-hidden-laptop">
        <h3>Cancellation Policy</h3>
        <ul class="cp_ul">
            <li class="book-by">This booking is initiated by you, therefore, any further cancelation or changes can be
                managed by only you.</li>
            <li>Cancellations made more than 72 hours before the hearing receive a 50% refund.</li>
            <li>Cancellations within 72 hours and no-shows are fully chargeable and non-refundable.</li>
            <li>Split-payment bookings must be completed by all parties within 24 hours; failing which the booking is
                cancelled and all paid amounts are refunded.</li>
            <li>Add-ons already used are non-refundable.</li>
            <li>Eligible refunds are processed within 7 business days.</li>
        </ul>
        <p><a href="/cancellation-and-refund-policy/" target="_blank">Read full policy here</a></p>
    </div>
    <?php
});

// Save group payment data when order is created
add_action('woocommerce_checkout_create_order', function ($order, $data) {

    // Save payment mode
    if (!empty($_POST['group_payment_mode'])) {
        $order->update_meta_data('group_payment_mode', sanitize_text_field($_POST['group_payment_mode']));
    }

    if ($_POST['group_payment_mode'] === 'group') {

        $total_payers = intval($_POST['group_total_payers'] ?? 1);
        $order->update_meta_data('group_original_total', $order->get_total());

        $total = $order->get_total();
        $share_total = $total / $total_payers;

        $order->set_total($share_total);

        // Save total payers
        if (!empty($_POST['group_total_payers'])) {
            $order->update_meta_data('group_total_payers', intval($_POST['group_total_payers']));
        }

        // Save additional payer details (Payer 2+)
        if (!empty($_POST['group_member_name'])) {
            $names = $_POST['group_member_name'];
            $emails = $_POST['group_member_email'] ?? [];
            $phones = $_POST['group_member_phone'] ?? [];
            $companies = $_POST['group_member_company'] ?? [];

            $additional_payers = [];

            foreach ($names as $index => $name) {
                if ($index < 2)
                    continue; // Skip Payer 1 (main buyer)

                $additional_payers[$index] = [
                    'name' => sanitize_text_field($name),
                    'email' => sanitize_email($emails[$index] ?? ''),
                    'phone' => sanitize_text_field($phones[$index] ?? ''),
                    'company' => sanitize_text_field($companies[$index] ?? ''),
                ];
            }

            if (!empty($additional_payers)) {
                $order->update_meta_data('group_additional_payers', $additional_payers);
            }
        }
    }

}, 10, 2);





add_action('woocommerce_thankyou', function ($parent_order_id) {
    $parent_order = wc_get_order($parent_order_id);
    if (!$parent_order)
        return;

    $now = time();
    $created_dt = $parent_order->get_date_created();
    $created_ts = $created_dt->getTimestamp();
    $deadline_24h = $created_ts + (24 * 60 * 60);
    $booking_start_ts = null;

    foreach ($parent_order->get_items() as $item) {
        $product = $item->get_product();

        if ($product && $product->get_type() === 'phive_booking') {
            $from_date = $item->get_meta('phive_display_time_from');

            if (!empty($from_date)) {
                $val = is_array($from_date) ? $from_date[0] : $from_date;
                $booking_start_ts = strtotime($val);
                break;
            }
        }
    }

    $effective_deadline = $deadline_24h;

    if ($booking_start_ts) {
        $b_date_str = wp_date('Y-m-d 00:00:00', $booking_start_ts);
        $booking_midnight = strtotime($b_date_str);

        $tz = new DateTimeZone(wp_timezone_string());

        // Convert booking start to site timezone
        $booking_dt = new DateTime('@' . $booking_start_ts);
        $booking_dt->setTimezone($tz);

        // Set to midnight (00:00:00) of that day
        $booking_dt->setTime(0, 0, 0);

        // Convert back to timestamp (UTC internally)
        $booking_midnight = $booking_dt->getTimestamp();

        if ($deadline_24h > $booking_midnight) {
            $effective_deadline = $booking_midnight;
        }
    }

    $cancel_time = ($effective_deadline === $deadline_24h)
        ? 'Twentyfour'
        : 'Midnight';

    $payment_mode = $parent_order->get_meta('group_payment_mode');
    if ($payment_mode !== 'group')
        return;

    $additional_payers = $parent_order->get_meta('group_additional_payers');
    if (empty($additional_payers) || !is_array($additional_payers))
        return;

    // Prevent duplicate child orders creation
    if ($parent_order->get_meta('_group_child_orders_created'))
        return;

    $total_original = $parent_order->get_meta('group_original_total');
    if (!$total_original)
        $total_original = $parent_order->get_total();

    $total_payers = $parent_order->get_meta('group_total_payers') ?: 2;

    // Calculate share per payer
    $share_amount = $total_original / $total_payers;
    $current_month = (int) date('n');
    $current_year = (int) date('Y');

    if ($current_month >= 4) {
        // April to Dec (e.g., 2025-26)
        $year_part = $current_year . '-' . date('y', strtotime('+1 year'));
    } else {
        // Jan to March (e.g., 2024-25)
        $year_part = ($current_year - 1) . '-' . date('y');
    }

    foreach ($additional_payers as $index => $payer) {
        // Skip if child order already exists
        if (!empty($payer['child_order_id']))
            continue;

        $child_order = wc_create_order();
        if (!$child_order)
            continue;

        // Assign customer if exists
        $user = get_user_by('email', $payer['email']);
        $child_order->set_customer_id($user ? $user->ID : 0);

        // Copy products from parent
        foreach ($parent_order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $variation_id = $item->get_variation_id();
            $quantity = $item->get_quantity();

            if ($variation_id > 0) {
                $child_order->add_product(wc_get_product($variation_id), $quantity);
            } else {
                $child_order->add_product(wc_get_product($product_id), $quantity);
            }
        }

        // Set billing and total
        $child_order->set_total($share_amount);
        $child_order->set_billing_first_name(sanitize_text_field($payer['name']));
        $child_order->set_billing_email(sanitize_email($payer['email']));
        $child_order->set_billing_phone(sanitize_text_field($payer['phone'] ?? ''));
        $child_order->set_billing_company(sanitize_text_field($payer['company'] ?? ''));
        $child_order->save();

        // Sequence Counter Logic
        $last_sequence = (int) get_option('apl_sequence_counter', 0);
        $new_sequence = $last_sequence + 1;
        $padded_sequence = str_pad($new_sequence, 4, '0', STR_PAD_LEFT);

        $unique_apl_id = 'APL/' . $year_part . '/' . $padded_sequence;

        if (!$child_order->meta_exists('_unique_apl_id')) {
            $child_order->update_meta_data('_unique_apl_id', $unique_apl_id);
            update_option('apl_sequence_counter', $new_sequence);
        }

        // Group meta
        $child_order->update_meta_data('group_parent_order', $parent_order->get_id());
        $child_order->update_meta_data('group_payer_index', $index);
        $child_order->update_meta_data('group_payer_name', sanitize_text_field($payer['name']));
        $child_order->update_meta_data('group_payer_email', sanitize_email($payer['email']));
        $child_order->update_meta_data('group_payer_phone', sanitize_text_field($payer['phone'] ?? ''));
        $child_order->update_meta_data('group_payer_company', sanitize_text_field($payer['company'] ?? ''));
        $child_order->update_meta_data('_phive_manual_payment_status', 'pending');
        $child_order->save();

        $invoice = generate_admin_invoice_pdf($child_order);

        // Save child order ID in parent meta
        $additional_payers[$index]['child_order_id'] = $child_order->get_id();

        // Send checkout email
        $pay_url = $child_order->get_checkout_payment_url();
        $subject = "Accordhub - Your Room Booking Payment Share is Pending";
        $email_heading = "Complete Your Share of Room Payment to Confirm the Room Booking";
        $message = "<p>Dear Customer,</p>";
        $message .= "<p>You've been added as a participant in a split payment for an upcoming room booking at Accordhub. Please complete payment for your share of <strong>" . wc_price($share_amount) . "</strong>.</p>";
        $message .= "<p class='btn_p'><a href='{$pay_url}'>Click here to pay</a></p>";
        $message .= "<p>To avoid cancellation, please make the payment within 24 hours of booking. However, if your booking is scheduled for tomorrow, kindly ensure payment is completed by 11:59 PM today.</p>";
        //wp_mail($payer['email'], $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        send_woocommerce_custom_email($payer['email'], $subject, $email_heading, $message);

        $url_parts = explode('/order-pay/', $pay_url);
        $button_suffix = end($url_parts);

        // $components = [
        //     // Body Component: 2 Variables (Name and Share Amount)
        //     [
        //         'type' => 'body',
        //         'parameters' => [
        //             [
        //                 'type' => 'text',
        //                 'text' => $payer['name'] 
        //             ],
        //             [
        //                 'type' => 'text',
        //                 'text' => $share_amount  
        //             ]
        //         ]
        //     ], 
        //     [
        //         'type' => 'button',
        //         'sub_type' => 'url',
        //         'index' => 0,
        //         'parameters' => [
        //             [
        //                 'type' => 'text',
        //                 'text' => $button_suffix 
        //             ]
        //         ]
        //     ]
        // ];

        $components = [
            [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'document',
                        'document' => [
                            'link' => 'https://staging.accordhub.in/wp-content/uploads/invoice-37698.pdf',
                            'filename' => 'invoice-37698.pdf'
                        ]
                    ]
                ]
            ],
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $cancel_time
                    ]
                ]
            ],
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => 0,
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => 'invoice-37698.pdf'
                    ]
                ]
            ]
        ];

        if (strlen($payer['phone']) === 10) {
            $number = '91' . $payer['phone'];
        } elseif (strlen($payer['phone']) === 11 && substr($payer['phone'], 0, 1) === '0') {
            $number = '91' . substr($payer['phone'], 1);
        }

        //send_whatsapp_template_msg($number, 'payment_other_parties_request_completion', $components);
        send_whatsapp_template_msg($number, 'live_addons_invoice', $components);

    }

    // Save updated additional payers and mark child orders as created
    $parent_order->update_meta_data('group_additional_payers', $additional_payers);
    $parent_order->update_meta_data('_group_child_orders_created', 1);
    $parent_order->save();
    $parent_invoice = generate_admin_invoice_pdf($parent_order);
}, 10, 1);


add_action('user_register', function ($user_id) {
    $user = get_userdata($user_id);
    if (!$user)
        return;

    $email = $user->user_email;

    // Find any guest child orders with this email
    $args = [
        'limit' => -1,
        'status' => ['pending', 'on-hold'],
        'meta_key' => 'group_payer_email',
        'meta_value' => $email,
    ];
    $orders = wc_get_orders($args);

    foreach ($orders as $order) {
        if ($order->get_customer_id() == 0) {
            $order->set_customer_id($user_id);
            $order->save();
        }
    }
});



add_action('woocommerce_payment_complete', function ($order_id) {

    $order = wc_get_order($order_id);
    generate_invoice_pdf($order);
    $order->update_meta_data('_phive_manual_payment_status', 'completed');
    $order->update_meta_data('fake_status', '');
    $order->save();
    $parent_id = $order->get_meta('group_parent_order');
    $p_type = $order->get_meta('group_payment_mode');
    if (!$parent_id) {
        if (!$p_type && $order) {
            $order->update_status('completed', 'Payments completed.');
        }
        return; // stop execution for non-child orders
    }
    $order->update_status('completed', 'Payment completed.');

    $parent_order = wc_get_order($parent_id);
    if (!$parent_order)
        return;

    $all_paid = true;

    if (!in_array($parent_order->get_status(), ['processing', 'completed'])) {
        $all_paid = false;
    }

    $child_payers = $parent_order->get_meta('group_additional_payers');

    if ($child_payers) {
        foreach ($child_payers as $payer) {
            if (empty($payer['child_order_id']))
                continue;
            $c_order = wc_get_order($payer['child_order_id']);

            if (!$c_order || !in_array($c_order->get_status(), ['processing', 'completed'])) {
                $all_paid = false;
                break;
            }
        }
    }

    if ($all_paid) {
        $parent_order->update_status('completed', 'All group payments completed.');
    }
}, 10, 1);



add_action('woocommerce_before_pay_action', function ($order) {
    if (!isset($_POST))
        return;
    if (isset($_POST['woocommerce-pay-nonce']))
        return;
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'billing_') === 0) {
            $order->update_meta_data($key, sanitize_text_field($value));
        }
    }
    $order->save();
});
add_action('woocommerce_before_pay_action', 'phive_update_order_billing_dynamic');

function phive_update_order_billing_dynamic($order)
{
    if (!isset($_POST['woocommerce-pay-nonce']))
        return;

    if (isset($_POST['billing_gstin'])) {
        $order->update_meta_data('_billing_gstin', sanitize_text_field(wp_unslash($_POST['billing_gstin'])));
    }

    if (isset($_POST['billing_company_name'])) {
        $order->update_meta_data('_billing_company_name', sanitize_text_field(wp_unslash($_POST['billing_company_name'])));
    }

    foreach ($_POST as $key => $value) {
        // 1. Only look at billing fields
        if (strpos($key, 'billing_') === 0) {

            // 2. Clean the value
            $clean_value = sanitize_text_field($value);

            // 3. Handle specific mismatch in your HTML form
            if ($key === 'billing_company_name') {
                $order->set_billing_company($clean_value);
                continue;
            }

            // 4. Construct the setter function name (e.g., set_billing_first_name)
            $setter = 'set_' . $key;

            // 5. Check if WooCommerce has a method for this and call it
            if (is_callable(array($order, $setter))) {
                $order->{$setter}($clean_value);
            }
        }
    }

    // 6. Save the actual order properties
    $order->save();
}
function get_booking_status($order_id)
{
    if (empty($order_id)) {
        return '';
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return '';
    }

    if ($order->get_meta('fake_status') == 'completed') {
        return 'Confirmed';
    }

    if ($order->has_status('cancelled')) {
        return 'Cancelled';
    }
    // Check if this order has a parent (group order)
    $parent_order_id = $order->get_meta('group_parent_order');
    if ($parent_order_id) {
        $order = wc_get_order($parent_order_id);
        // If this child order is paid
        $child_payers = $order->get_meta('group_additional_payers', true);
        $total_children = 0;
        $unpaid_children = 0;
        $paid_children = 0;

        if (!empty($child_payers) && is_array($child_payers)) {
            $total_children = count($child_payers) + 1;

            // Check parent payment status first (the +1)
            if (!$order->is_paid()) {
                $unpaid_children++;
            } else {
                $paid_children++;
            }

            foreach ($child_payers as $payer) {
                if (empty($payer['child_order_id'])) {
                    $unpaid_children++; // Order not even created yet is still unpaid
                    continue;
                }

                $child_order = wc_get_order($payer['child_order_id']);

                // Count only valid child orders
                if (!$child_order) {
                    $unpaid_children++;
                    continue;
                }

                if (!$child_order->is_paid()) {
                    $unpaid_children++;
                }
                if ($child_order->is_paid()) {
                    $paid_children++;
                }
            }
        }

        // Return results based on payment progress
        if ($total_children > 0) {
            if ($unpaid_children === 0 && $paid_children > 0) {
                return 'Confirmed';
            } else {
                $title_text = esc_attr("{$unpaid_children} of {$total_children} parties payment is pending");

                return 'In Progress <span class="tooltip_box">
                            <span class="tooltip_i">i</span>
                            <span class="tooltip_box_hover" style="display:none;">' . $title_text . '</span>
                        </span>';
            }
        }
    }

    // Check if this order is a group payment type
    $group_payment_type = $order->get_meta('group_payment_mode', true);

    if ($group_payment_type && $group_payment_type === 'group') {

        $child_payers = $order->get_meta('group_additional_payers', true);
        $total_children = 0;
        $unpaid_children = 0;
        $paid_children = 0;
        if (!empty($child_payers) && is_array($child_payers)) {
            $total_children = count($child_payers) + 1;

            // Check parent payment status first (the +1)
            if (!$order->is_paid()) {
                $unpaid_children++;
            } else {
                $paid_children++;
            }

            foreach ($child_payers as $payer) {
                if (empty($payer['child_order_id'])) {
                    $unpaid_children++;
                    continue;
                }

                $child_order = wc_get_order($payer['child_order_id']);

                // Count only valid child orders
                if (!$child_order) {
                    $unpaid_children++;
                    continue;
                }

                if (!$child_order->is_paid()) {
                    $unpaid_children++;
                }
                if ($child_order->is_paid()) {
                    $paid_children++;
                }
            }
        }

        // Return results based on payment progress
        if ($total_children > 0) {
            if ($unpaid_children === 0 && $paid_children > 0) {
                return 'Confirmed';
            } else {
                $title_text = esc_attr("{$unpaid_children} of {$total_children} parties payment is pending");
                return 'In Progress <span class="tooltip_box">
                            <span class="tooltip_i">i</span>
                            <span class="tooltip_box_hover" style="display:none;">' . $title_text . '</span>
                        </span>';
            }
        }
    }


    if ($order->is_paid()) {
        return 'Confirmed';
    } else {
        return 'Pending';
    }
}
function verify_razorpay_payment($order_id)
{
    if (empty($order_id)) {
        return ['status' => 'error', 'message' => 'Invalid order ID'];
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return ['status' => 'error', 'message' => 'Order not found'];
    }

    $manual_payment = $order->get_meta('_phive_manual_payment_status');
    if ($manual_payment && $manual_payment !== 'pending' && $manual_payment !== '') {

        if ($order->get_date_paid()) {
            $labels = [
                'pending' => 'Pending',
                'completed' => 'Completed ',
                'cancelled' => 'Refund Pending',
                'refunded' => 'Refunded'
            ];
        } else {
            $labels = [
                'pending' => 'Pending',
                'completed' => 'Completed ',
                'cancelled' => 'Payment Cancelled',
                'refunded' => 'Refunded'
            ];
        }

        return isset($labels[$manual_payment]) ? $labels[$manual_payment] : ucwords(str_replace('_', ' ', $manual_payment));
    }
    // Your custom meta key format
    $payment_meta_key = 'razorpay_order_id' . $order_id;
    $razorpay_order_id = $order->get_meta($payment_meta_key);

    $rzp_settings = get_option('woocommerce_razorpay_settings');
    $api_key = $rzp_settings['key_id'];
    $api_secret = $rzp_settings['key_secret'];

    $razorpay_payment_id = $order->get_transaction_id();

    // Step 1: If payment ID not found, fetch it from Razorpay using order ID
    if (empty($razorpay_payment_id) && !empty($razorpay_order_id)) {
        $response = wp_remote_get(
            "https://api.razorpay.com/v1/orders/$razorpay_order_id/payments",
            [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret)
                ]
            ]
        );

        if (is_wp_error($response)) {
            return 'Pending';
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['items'])) {
            foreach ($body['items'] as $payment) {
                if ($payment['status'] === 'captured') {
                    $razorpay_payment_id = $payment['id'];
                    break;
                }
            }

            if (empty($razorpay_payment_id)) {
                $last_payment = end($body['items']);
                $razorpay_payment_id = $last_payment['id'];
            }
        }
    }

    if (empty($razorpay_payment_id)) {
        return 'Pending';
    }

    // Step 2: Fetch payment details
    $payment_response = wp_remote_get(
        "https://api.razorpay.com/v1/payments/$razorpay_payment_id",
        [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret)
            ]
        ]
    );

    if (is_wp_error($payment_response)) {
        return ['status' => 'error', 'message' => 'Payment API error'];
    }

    $payment_data = json_decode(wp_remote_retrieve_body($payment_response), true);

    $status_map = [
        'captured' => 'Completed',
        'failed' => 'Pending',
        'refunded' => 'Refunded',
        'created' => 'Canceled',
        'authorized' => 'In Process'
    ];

    $final_status = $status_map[$payment_data['status']] ?? 'Pending';

    return $final_status;
}

function track_razorpay_by_order_id($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order)
        return 'Order Not Found';

    // 1. Aapka custom meta key format
    $payment_meta_key = 'razorpay_order_id' . $order_id;
    $razorpay_order_id = $order->get_meta($payment_meta_key);

    if (empty($razorpay_order_id)) {
        return 'No Razorpay Order Found';
    }

    $rzp_settings = get_option('woocommerce_razorpay_settings');
    $api_key = $rzp_settings['key_id'];
    $api_secret = $rzp_settings['key_secret'];

    // 2. Seedha Razorpay Order API ko hit karein (Payments ki list ke bajaye)
    $response = wp_remote_get(
        "https://api.razorpay.com/v1/orders/$razorpay_order_id",
        [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret)
            ]
        ]
    );

    if (is_wp_error($response))
        return 'API Error';

    $order_data = json_decode(wp_remote_retrieve_body($response), true);

    /**
     * Razorpay Order Status Logic:
     * 'created'  => User ne checkout khola par abhi kuch nahi kiya (Not Attempted)
     * 'attempted' => User ne payment try kiya (OTP page tak gaya ya fail hua)
     * 'paid'     => Payment success ho gayi
     */

    $status = $order_data['status'];
    $attempts = isset($order_data['attempts']) ? (int) $order_data['attempts'] : 0;

    if ($status === 'paid') {
        return 'Completed';
    } elseif ($status === 'attempted' || $attempts > 0) {
        return 'Payment Failed';
    } elseif ($status === 'created' && $attempts === 0) {
        return 'Payment Canceled';
    }

    return ucwords($status);
}
function get_payment_method_name($order_id)
{
    // 1. Safety Checks (Return a string, not an array)
    if (empty($order_id)) {
        return '';
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return '';
    }

    // 2. Check Custom Manual Payment Status
    $manual_payment = $order->get_meta('_phive_manual_payment_status');

    // 3. Fallback to Standard WooCommerce Title
    if ($order->is_paid()) {
        return 'Paid via Razorpay';
    } elseif ($manual_payment === 'completed') {
        return 'Paid Manually';
    } else {
        return '';
    }
}

function get_booking_type($order_id)
{
    if (!$order_id) {
        return '';
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return '';
    }

    $booking_count = 0;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        if (!$product) {
            continue;
        }

        // Check product type
        if ($product->is_type('phive_booking')) {
            $booking_count += $item->get_quantity(); // count qty if needed
        }
    }

    if ($booking_count === 1) {
        return 'Booking';
    }

    if ($booking_count > 1) {
        return 'Group (' . $booking_count . ')';
    }

    return '';
}


add_action('template_redirect', 'handle_invoice_download_pdf_simple');

function handle_invoice_download_pdf_simple()
{
    if (isset($_GET['download_invoice']) && isset($_GET['_wpnonce'])) {
        $order_id = intval($_GET['download_invoice']);

        if (!wp_verify_nonce($_GET['_wpnonce'], 'download_invoice_' . $order_id)) {
            wp_die('Invalid request.');
        }

        $order = wc_get_order($order_id);
        if (!$order || !is_user_logged_in() || $order->get_user_id() !== get_current_user_id()) {
            wp_die('You are not allowed to access this receipt.');
        }

        // Get the WooCommerce email instance
        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();
        $email = $emails['WC_Email_Customer_Invoice'];

        // Generate invoice HTML from template
        ob_start();
        wc_get_template(
            'emails/customer-receipt-admin.php',
            array(
                'order' => $order,
                'sent_to_admin' => false,
                'plain_text' => false,
                'email' => $email,
                'email_heading' => 'Receipt'
            )
        );
        $invoice_html = ob_get_clean();

        // Generate PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true); // allow images
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($invoice_html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->get_font("Inter", "normal");
        $canvas->page_text(200, 810, "We appreciate the opportunity to serve you.", $font, 10, array(0, 0, 0));

        // Send PDF to browser
        $filename = 'receipt-' . $order->get_order_number() . '.pdf';
        $dompdf->stream($filename, array('Attachment' => 1));
        exit;
    }
}





function show_case_details_invoice($order)
{

    global $wp;

    // Check if we're on the "view-order" endpoint


    if (!$order instanceof WC_Order)
        return;

    if (isset($wp->query_vars['view-order'])) {
        $order_id = absint($wp->query_vars['view-order']);
        $orig_order = wc_get_order($order_id);
    }
    $order_id = $order->get_id();
    if ($orig_order) {
        $ord_meta = $orig_order->get_meta('group_parent_order');
    }

    echo '<div class="case-box">';
    echo '<div class="d-flex">';
    echo '<h2>Case Details</h2>';
    echo '</div>';
    if (!is_wc_endpoint_url('order-received')) {
        // Get custom order meta values
        $case_name = $order->get_meta('Case Title');
        $case_id = $order->get_meta('Case ID');
        $case_desc = $order->get_meta('Case Description');
        $total = $order->get_meta('Total Participants');
        $total_parties = $order->get_meta('Total Parties Involved');
        $parties = $order->get_meta('Parties Involved'); // structured array
        $arbitrators = $order->get_meta('Arbitrators');  // array

        // Try to get from current order items if missing
        if (empty($case_name) || empty($case_id) || empty($case_desc) || empty($total) || empty($total_parties) || empty($parties) || empty($arbitrators)) {
            foreach ($order->get_items() as $item_id => $item) {
                $product = $item->get_product();
                if ($product && $product->get_type() === 'phive_booking') {
                    $case_name = $case_name ?: $item->get_meta('Case Title');
                    $case_id = $case_id ?: $item->get_meta('Case ID');
                    $case_desc = $case_desc ?: $item->get_meta('Case Description');
                    $total = $total ?: $item->get_meta('Total Participants');
                    $total_parties = $total_parties ?: $item->get_meta('Total Parties Involved');
                    $parties = $parties ?: $item->get_meta('Parties Involved');
                    $arbitrators = $arbitrators ?: $item->get_meta('Arbitrators');
                    break;
                }
            }
        }

        // Try to get from parent order items if still missing
        if (empty($case_name) || empty($case_id) || empty($case_desc) || empty($total) || empty($total_parties) || empty($parties) || empty($arbitrators)) {
            $parent_id = $order->get_meta('group_parent_order');
            if ($parent_id) {
                $p_ord = wc_get_order($parent_id);
                if ($p_ord) {
                    foreach ($p_ord->get_items() as $item_id => $item) {
                        $product = $item->get_product();
                        if ($product && $product->get_type() === 'phive_booking') {
                            $case_name = $case_name ?: $item->get_meta('Case Title');
                            $case_id = $case_id ?: $item->get_meta('Case ID');
                            $case_desc = $case_desc ?: $item->get_meta('Case Description');
                            $total = $total ?: $item->get_meta('Total Participants');
                            $total_parties = $total_parties ?: $item->get_meta('Total Parties Involved');
                            $parties = $parties ?: $item->get_meta('Parties Involved');
                            $arbitrators = $arbitrators ?: $item->get_meta('Arbitrators');
                            break;
                        }
                    }
                }
            }
        }


        if (is_admin()) { ?>
            <style>
                #order_data .order_data_column {
                    width: 100%;
                }

                #order_data table.shop_table.my_account_orders.wc-case-details {
                    width: 100%;
                    text-align: left;
                }

                div#order_data button.button.woocommerce-button.edit-case-details {
                    display: none;
                }

                #order_data h3 {
                    display: inline-block;
                }
            </style>
        <?php }


        echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">';
        echo '<tbody>';
        if ($case_name)
            echo '<tr><td class="woocommerce-table__product-name product-name">Case Title</td><td class="woocommerce-table__product-table product-total" data-title="Case Title">' . esc_html($case_name) . '</th></td>';



        if ($case_id)
            echo '<tr><td class="woocommerce-table__product-name product-name">Case ID</td><td class="woocommerce-table__product-table product-total" data-title="Case ID">' . esc_html($case_id) . '</td></tr>';
        if ($case_desc)
            echo '<tr><td class="woocommerce-table__product-name product-name">Case Description</td><td class="woocommerce-table__product-table product-total" data-title="Case Description">' . nl2br(esc_html($case_desc)) . '</td></tr>';
        if ($case_desc)
            echo '<tr><td class="woocommerce-table__product-name product-name">No of Parties</td><td class="woocommerce-table__product-table product-total" data-title="No of Parties">' . nl2br(esc_html($total_parties)) . '</td></tr>';

        // Parties
        if (!empty($parties) && is_array($parties)) {
            echo '<tr><td class="woocommerce-table__product-name product-name">Parties Details</td><td class="woocommerce-table__product-table product-total" data-title="Parties Involved">';
            foreach ($parties as $index => $party) {
                echo '<strong>Party ' . ($index + 1) . '</strong><br>';

                if (!empty($party['name'])) {
                    echo 'Party Name: ' . esc_html($party['name']) . '<br>';
                }

                if (!empty($party['members']) && is_array($party['members'])) {
                    $legal_counsels = [];
                    $company_reps = [];

                    foreach ($party['members'] as $member) {
                        if (!empty($member['name'])) {
                            if ($member['role'] === 'legal_counsel') {
                                $legal_counsels[] = esc_html($member['name']);
                            } else {
                                $company_reps[] = esc_html($member['name']);
                            }
                        }
                    }

                    if (!empty($legal_counsels)) {
                        echo 'Legal Counsel: ' . implode(', ', $legal_counsels) . '<br>';
                    }
                    if (!empty($company_reps)) {
                        echo 'Company Representatives: ' . implode(', ', $company_reps) . '<br>';
                    }
                }

                echo '<br>'; // spacing between parties
            }
            echo '</td></tr>';
        }



        // Arbitrators
        if (!empty($arbitrators) && is_array($arbitrators)) {
            echo '<tr><td class="woocommerce-table__product-name product-name">Arbitrators</td><td class="woocommerce-table__product-table product-total" data-title="Arbitrators">' . esc_html(implode(', ', array_filter($arbitrators))) . '</td></tr>';
        }

        if ($total)
            echo '<tr><td class="woocommerce-table__product-name product-name">Total Members</td><td class="woocommerce-table__product-table product-total" data-title="Total Members">' . esc_html($total) . '</td></tr>';

        echo '</tbody>';
        echo '</table>';
        ?>

        <?php
    }
    echo '</div>';
}

function generate_invoice_pdf($order)
{
    $options = new Options();
    $options->set('isRemoteEnabled', true); // Allow remote images
    $dompdf = new Dompdf($options);

    $mailer = WC()->mailer();
    $emails = $mailer->get_emails();
    $email = $emails['WC_Email_Customer_Invoice'];

    // Generate invoice HTML from template
    ob_start();
    wc_get_template(
        'emails/customer-receipt-admin.php',
        array(
            'order' => $order,
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $email,
            'email_heading' => 'Receipt'
        )
    );
    $invoice_html = ob_get_clean();

    // Render PDF
    $dompdf->loadHtml($invoice_html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->get_font("Inter", "normal");
    $canvas->page_text(200, 810, "We appreciate the opportunity to serve you.", $font, 10, array(0, 0, 0));
    // Save PDF to uploads folder
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/receipt-' . $order->get_id() . '.pdf';
    file_put_contents($file_path, $dompdf->output());

    return $file_path;
}

function generate_receipt_admin_pdf($order)
{
    $options = new Options();
    $options->set('isRemoteEnabled', true); // Allow remote images
    $dompdf = new Dompdf($options);

    $mailer = WC()->mailer();
    $emails = $mailer->get_emails();
    $email = $emails['WC_Email_Customer_Invoice'];

    // Generate invoice HTML from template
    ob_start();
    wc_get_template(
        'emails/customer-receipt-admin.php',
        array(
            'order' => $order,
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $email,
            'email_heading' => 'Receipt'
        )
    );
    $invoice_html = ob_get_clean();

    // Render PDF
    $dompdf->loadHtml($invoice_html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->get_font("Inter", "normal");
    $canvas->page_text(200, 810, "We appreciate the opportunity to serve you.", $font, 10, array(0, 0, 0));
    // Save PDF to uploads folder
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/receipt-' . $order->get_id() . '.pdf';
    file_put_contents($file_path, $dompdf->output());

    return $file_path;
}

add_action('woocommerce_payment_complete', 'send_booking_payment_emails', 10, 1);



function get_booking_details($order)
{
    $id = $order->get_ID();
    $applied_coupons = $order->get_coupon_codes();
    $discount_total = $order->get_discount_total();
    $percentage = '';
    foreach ($order->get_items('coupon') as $coupon_item) {

        $coupon = new WC_Coupon($coupon_item->get_code());

        if ($coupon->get_discount_type() === 'percent') {
            $percentage = $coupon->get_amount();
        }
    }
    $parties = $order->get_meta('group_additional_payers');
    $prime_user = $order->get_billing_first_name() . " " . $order->get_billing_last_name();

    $discount_applied_25_price = '';
    $discount_applied_25_per = "";
    $discount_applied_25_per = $order->get_meta('_accordhub_discount_applied');
    $addon_count = 0;
    $addons_price = 0;


    // Initialize variables to prevent undefined warnings
    $bulk_discount = '';
    $bulk_discount_amount = 0;
    $room_name = '';
    $datetime = '';
    $price = 0;
    $slots = 1;
    $image_url = '';

    if (is_array($parties)) {
        $payers = count($parties) + 1;
    } else {
        $payers = 1;
    }

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->get_type() === 'phive_booking') {

            $room_name = $item->get_name();
            $price = (float) $product->get_meta('_phive_booking_pricing_cost_per_unit');
            $full_price = $price;
            $image_id = $product->get_image_id();
            $image_url = wp_get_attachment_image_url($image_id, 'full');

            $from_meta = $item->get_meta('phive_display_time_from');
            $from = is_array($from_meta) ? ($from_meta[0] ?? '') : $from_meta;
            if (empty($from)) {
                $fallback_from = $item->get_meta('From');
                $from = is_array($fallback_from) ? ($fallback_from[0] ?? '') : $fallback_from;
            }

            $to_meta = $item->get_meta('phive_display_time_to');
            $to = is_array($to_meta) ? ($to_meta[0] ?? '') : $to_meta;
            if (empty($to)) {
                $fallback_to = $item->get_meta('To');
                $to = is_array($fallback_to) ? ($fallback_to[0] ?? '') : $fallback_to;
            }

            if ($to === $from || $to === date('g:i a', strtotime($from . ' + 5 hours'))) {
                $to = date('g:i a', strtotime($to . ' + 4 hours'));
            }
            $datetime = '';
            $slots = 1;
            if ($from && $to) {
                $datetime = date('M j, Y', strtotime($from)) . ' (' . date('g:i a', strtotime($from)) . ' – ' . date('g:i a', strtotime($to)) . ')';

                // Calculate hours and apply slot discounts
                $diff_hours = (strtotime($to) - strtotime($from)) / 3600;
                if ($diff_hours > 9) {
                    $slots = 3;
                    $full_price = $price * $slots;
                    if (!$discount_applied_25_per) {
                        $price = ($price * $slots) * 0.85; // 15% discount
                        $bulk_discount = '15%';
                        $bulk_discount_amount = $full_price - $price;
                    } else {
                        $price = $price * $slots;
                    }
                } elseif ($diff_hours > 5) {
                    $slots = 2;
                    $full_price = $price * $slots;
                    if (!$discount_applied_25_per) {
                        $price = ($price * $slots) * 0.90; // 10% discount
                        $bulk_discount = '10%';
                        $bulk_discount_amount = $full_price - $price;
                    } else {
                        $price = $price * $slots;
                    }
                } else {
                    $slots = 1;
                    $full_price = $price * $slots;
                    $price = $price * $slots;
                    $bulk_discount = '';
                    $bulk_discount_amount = 0;
                }
            }

        } else {
            $addon_unit_price = $product ? $product->get_price() : 0;
            $addons_price += ($addon_unit_price * $item->get_quantity());
            $addon_count++;
        }
    }



    $total = $price + $addons_price;

    // If Link discount added 25% discount
    if ($discount_applied_25_per) {
        $discount_applied_25_price = $price * 0.30;
        $total_after_discount = $total - $discount_applied_25_price;
    } else {
        $total_after_discount = $total - $discount_total;
    }

    $share_total = $total_after_discount / $payers;
    $gst = $share_total * 0.18;
    $cgst = $gst / 2;
    $sgst = $gst / 2;

    $coupon_code = !empty($applied_coupons) ? $applied_coupons[0] : '';

    return ['id' => $id, 'user' => $prime_user, 'room' => $room_name, 'datetime' => $datetime, 'price' => $price, 'slots' => $slots, 'image' => $image_url, 'coupon' => $coupon_code, 'discount' => $discount_total, 'disc_percentage' => $percentage, 'payers' => $payers, 'addon_count' => $addon_count, 'addons_price' => $addons_price, 'total' => $total, 'total_after_dis' => $total_after_discount, 'share_total' => $share_total, 'cgst' => $cgst, 'sgst' => $sgst, 'full_price' => $full_price, 'bulk_discount' => $bulk_discount, 'bulk_discount_amount' => $bulk_discount_amount, 'discount_applied_25_per' => $discount_applied_25_per, 'discount_applied_25_price' => $discount_applied_25_price, 'discount_applied_25_text' => 'Less Discount (30%)'];
}
function send_booking_payment_emails($order_id)
{
    if (!$order_id)
        return;

    $order = wc_get_order($order_id);


    if (!$order)
        return;
    if ('yes' === $order->get_meta('disable_customer_emails') || $order->get_meta('_wc_order_attribution_source_type') === 'admin') {
        //return;
    }
    $terms = home_url('terms-and-conditions');
    $policy = home_url('privacy-policy');
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $customer_phone = $order->get_billing_phone();
    if (empty($customer_phone)) {
        $user_id = $order->get_user_id();

        if ($user_id) {
            $customer_phone = get_user_meta($user_id, 'phone', true);
        }
    }
    $booked_by = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $customer_email = $order->get_billing_email();
    $upload_dir = wp_upload_dir();
    $invoice_file = $upload_dir['basedir'] . '/receipt-' . $order_id . '.pdf';
    $invoice_file_url = $upload_dir['baseurl'] . '/receipt-' . $order_id . '.pdf';
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // -----------------------------
    // 1️⃣ Check if order has at least one phive_booking product
    // -----------------------------
    $has_booking = false;
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->get_type() === 'phive_booking') {
            $has_booking = true;
            break;
        }
    }
    if ($order->get_meta('addons_parent_order_id'))
        return; // Stop if not a booking order

    // -----------------------------
    // 2️⃣ Identify order type
    // -----------------------------
    $parent_order_id = $order->get_meta('group_parent_order');
    if (!$parent_order_id && !empty($order->get_meta('group_additional_payers', true))) {
        $parent_order_id = $order->get_id();
    }
    $by = $order->get_meta('_wc_order_attribution_source_type');
    $parent_order = $parent_order_id ? wc_get_order($parent_order_id) : null;

    $is_child = !empty($parent_order_id);
    $is_parent = !$is_child && !empty($order->get_meta('group_additional_payers', true));
    $is_normal = !$is_child && !$is_parent;



    if ($is_child && $parent_order instanceof WC_Order) {
        $parent_phone = $parent_order->get_billing_phone();
        $booked_by = $parent_order->get_billing_first_name() . ' ' . $parent_order->get_billing_last_name();
    } else {
        $parent_phone = $order->get_billing_phone(); // fallback
    }


    // -----------------------------
    // 3️⃣ Get booking details (room, datetime)
    // -----------------------------


    // For child orders, get booking details from parent
    $booking_details = $is_child ? get_booking_details($parent_order) : get_booking_details($order);

    $total_children = 0;
    $unpaid_children = 0;
    $paid_children = 0;

    if ($parent_order) {
        if (!$parent_order->get_date_paid()) {
            $unpaid_children++;
        } else {
            $paid_children++;
        }

        $add_orders = $parent_order->get_meta('group_additional_payers');
        $is_split = (!empty($add_orders) && is_array($add_orders));


        if ($is_split) {
            $total_children = count($add_orders) + 1;
            foreach ($add_orders as $payer) {
                if (!empty($payer['email'])) {
                    if (!empty($payer['child_order_id'])) {
                        $c_order = wc_get_order($payer['child_order_id']);
                        if ($c_order) {
                            if (!$c_order->get_date_paid()) {
                                $unpaid_children++;
                            } else {
                                $paid_children++;
                            }
                        } else {
                            $unpaid_children++;
                        }
                    } else {
                        $unpaid_children++;
                    }
                }
            }
        }

    }

    // -----------------------------
    // 4️⃣ Send email based on order type
    // -----------------------------
    if ($is_normal) {
        // Normal single booking
        $subject = "Your Payment is Received and Booking is Confirmed (Booking ID - {$order_id})";
        $email_heading = "Your Room Booking Payment is Completed <br>(Booking ID - {$order_id})";
        $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;
        $message = "
            <p>Dear Customer,</p>
            <p>Your payment has been successfully completed, and your room booking is confirmed.</p>
            <p><strong>Booking ID:</strong> {$order_id}<br>
            <strong>Room:</strong> {$booking_details['room']}<br>
            <strong>Date & Time:</strong> {$booking_details['datetime']}</p>
            <p>Please view the payment receipt attached for your reference.</p>
            <p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>
            <p>Through this booking you agree to our <a href='{$terms}'>terms & conditions</a> and <a href='{$policy}'>privacy policy</a>.</p>
            ";
        //wp_mail($customer_email, $subject, $message, $headers, file_exists($invoice_file) ? [$invoice_file] : []);
        send_woocommerce_custom_email(
            $customer_email,
            $subject,
            $email_heading,
            $message,
            file_exists($invoice_file) ? [$invoice_file] : []
        );

        $components = [
            [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'document',
                        'document' => [
                            'link' => $invoice_file_url,
                            'filename' => 'receipt-full.pdf'
                        ]
                    ]
                ]
            ],
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customer_name],
                    ['type' => 'text', 'text' => $order_id],
                    ['type' => 'text', 'text' => $booking_details['room']],
                    ['type' => 'text', 'text' => $booking_details['datetime']]
                ]
            ]
        ];
        if (strlen($customer_phone) === 10) {
            $customer_phone = '91' . $customer_phone;
        } elseif (strlen($customer_phone) === 11 && substr($customer_phone, 0, 1) === '0') {
            $customer_phone = '91' . substr($customer_phone, 1);
        }

        send_whatsapp_template_msg($customer_phone, 'payment_completed_booking_confirmed', $components);

    } elseif ($is_parent) {
        // Parent booking
        $subject = "Accordhub - Payment Received for Your Room Booking (Booking ID - {$order_id})";
        $email_heading = "Your Room Booking Payment is Received (Booking ID - {$order_id})";
        $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;
        $message = "
            <p>Dear Customer,</p>
            <p>Thank you for your payment for room booking (Booking ID - {$order_id}).</p>
            <p>This booking includes multiple parties. We have successfully received payment for {$paid_children} of the {$total_children} parties.</p>
            <p><strong>Room:</strong> {$booking_details['room']}</p>
            <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>  
            <p>Please find the receipt for your payment attached.</p>
            <p class='btn_p'><a href='{$booking_url}'>View Booking Status</a></p>
            <p>Through this booking you agree to our <a href='{$terms}'>terms & conditions</a> and <a href='{$policy}'>privacy policy</a>.</p>
            ";
        //wp_mail($customer_email, $subject, $message, $headers, file_exists($invoice_file) ? [$invoice_file] : []);
        send_woocommerce_custom_email(
            $customer_email,
            $subject,
            $email_heading,
            $message,
            file_exists($invoice_file) ? [$invoice_file] : []
        );

        $components = [
            [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'document',
                        'document' => [
                            'link' => $invoice_file_url,
                            'filename' => 'receipt-main-split.pdf'
                        ]
                    ]
                ]
            ],
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customer_name],
                    ['type' => 'text', 'text' => $order_id],
                    ['type' => 'text', 'text' => $booking_details['room']],
                    ['type' => 'text', 'text' => $booking_details['datetime']]
                ]
            ]
        ];
        if (strlen($customer_phone) === 10) {
            $customer_phone = '91' . $customer_phone;
        } elseif (strlen($customer_phone) === 11 && substr($customer_phone, 0, 1) === '0') {
            $customer_phone = '91' . substr($customer_phone, 1);
        }

        send_whatsapp_template_msg($customer_phone, 'payment_split_mode', $components);

    } elseif ($is_child) {

        $msg = "<p><strong>Booked By:</strong> {$booked_by}</p>";

        // Child payment
        $ref_id = $parent_order_id;
        $subject = "Accordhub - Payment Received for Your Room Booking (Booking ID - {$booking_details['id']})";
        $email_heading = "Your Room Booking Payment is Received (Booking ID - {$booking_details['id']})";
        $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;
        $message = "
            <p>Dear Customer,</p>
            <p>Thank you for your payment for room booking (Booking ID - {$booking_details['id']}).</p>
            <p>This booking includes multiple parties. We have successfully received payment for {$paid_children} of the {$total_children} parties.</p>
            <p><strong>Room:</strong> {$booking_details['room']}</p>
            <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>  
            <p>Please find the receipt for your payment attached.</p>
            <p class='btn_p'><a href='{$booking_url}'>View Booking Status</a></p>
            <p>Through this booking you agree to our <a href='{$terms}'>terms & conditions</a> and <a href='{$policy}'>privacy policy</a>.</p>
            ";

        //wp_mail($customer_email, $subject, $message, $headers, file_exists($invoice_file) ? [$invoice_file] : []);
        send_woocommerce_custom_email(
            $customer_email,
            $subject,
            $email_heading,
            $message,
            file_exists($invoice_file) ? [$invoice_file] : []
        );

        $components = [
            ['type' => 'header', 'parameters' => [['type' => 'document', 'document' => ['link' => $invoice_file_url, 'filename' => 'receipt-child-split.pdf']]]],
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $customer_name],
                    ['type' => 'text', 'text' => $ref_id],
                    ['type' => 'text', 'text' => $booking_details['room']],
                    ['type' => 'text', 'text' => $booking_details['datetime']]
                ]
            ]
        ];
        if (strlen($customer_phone) === 10) {
            $customer_phone = '91' . $customer_phone;
        } elseif (strlen($customer_phone) === 11 && substr($customer_phone, 0, 1) === '0') {
            $customer_phone = '91' . substr($customer_phone, 1);
        }
        send_whatsapp_template_msg($customer_phone, 'payment_secondary_party_split_payment_completed', $components);

        // Optional: Notify parent about this payment
        if ($parent_order) {
            $parent_name = $parent_order->get_billing_first_name() . ' ' . $parent_order->get_billing_last_name();
            $parent_email = $parent_order->get_billing_email();

            // Check child payments
            // $child_payers = $parent_order->get_meta('group_additional_payers', true);
            // $unpaid_children = 0;
            // $total_children = is_array($child_payers) ? count($child_payers) + 1 : 1;

            // if (!empty($child_payers)) {
            //     foreach ($child_payers as $payer) {
            //         if (!empty($payer['child_order_id'])) {
            //             $child = wc_get_order($payer['child_order_id']);
            //             if ($child && !$child->is_paid()) {
            //                 $unpaid_children++;
            //             }
            //         }
            //     }
            // }

            // Notify parent of partial payment
            /*if ($unpaid_children > 0) {
                $parent_subject = "Accordhub - Payment Received by {$customer_name} for your Room Booking (Booking ID - {$parent_order_id})";
                $email_heading = "Payment Received by {$customer_name} for your Room Booking";
                $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $parent_order_id;
                $parent_message = "
                    <p>Dear Customer,</p>
                    <p>{$customer_name} has completed their payment for their share. Please find the booking details below.</p>
                    <p><strong>Booking ID:</strong> {$parent_order_id}</p>
                    <p><strong>Room:</strong> {$booking_details['room']}</p>
                    <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>
                    <p><strong>Booking Status:</strong> Pending (Payment from " . ($total_children - $unpaid_children) . "/{$total_children} parties is completed)</p>
                    <p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>
                    ";
                //wp_mail($parent_email, $parent_subject, $parent_message, $headers);
                send_woocommerce_custom_email(
                    $parent_email,
                    $parent_subject,
                    $email_heading,
                    $parent_message
                );
                $paid_child = $total_children - $unpaid_children;
                $components = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $parent_name // {{1}} Name of the main person
                            ],
                            [
                                'type' => 'text',
                                'text' => (string) $parent_order_id // {{2}} Main Booking ID
                            ],
                            [
                                'type' => 'text',
                                'text' => $booking_details['room'] // {{3}} Room Name
                            ],
                            [
                                'type' => 'text',
                                'text' => $booking_details['datetime'] // {{4}} Date & Time
                            ],
                            [
                                'type' => 'text',
                                'text' => "Pending (Payment from {$paid_child}/{$total_children} parties is completed)" // {{5}} Status string
                            ],
                            [
                                'type' => 'text',
                                'text' => $customer_name // {{6}} Name of person who just paid
                            ]
                        ]
                    ]
                ];
                $parent_phone = preg_replace('/[^0-9]/', '', $parent_order->get_billing_phone());
                if (strlen($parent_phone) === 10)
                    $parent_phone = '91' . $parent_phone;
                // Send to the Main Party's phone
                send_whatsapp_template_msg($parent_phone, 'payment_secondary_party_split_payment_completed_to_main', $components);
            }*/

            // If all payments done, confirm booking to parent
            if ($unpaid_children === 0) {

                // Parent order notification
                $parent_invoice_file = $upload_dir['basedir'] . '/receipt-' . $parent_order_id . '.pdf';
                $parent_subject = "Accordhub - Payment Completed by all the Parties (Booking ID -{$parent_order_id})";
                $email_heading = "Your Room Booking Payment is Completed by All the Parties (Booking ID -{$parent_order_id})";
                $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $parent_order_id;
                $parent_message = "
                    <p>Dear Customer,</p>
                    <p>The payment has been successfully completed by all the parties added to the split share for the room booking at Accordhub.</p> 
                    <p><strong>Booking ID:</strong> {$parent_order_id}</p>
                    <p><strong>Room:</strong> {$booking_details['room']}</p>
                    <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p> 
                    <p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>
                    <p>Through this booking you agree to our <a href='{$terms}'>terms & conditions</a> and <a href='{$policy}'>privacy policy</p>
                    ";
                send_woocommerce_custom_email(
                    $parent_email,
                    $parent_subject,
                    $email_heading,
                    $parent_message,
                    //file_exists($parent_invoice_file) ? [$parent_invoice_file] : []
                );
                $final_components = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $parent_name], // Variable {{1}}
                            ['type' => 'text', 'text' => (string) $parent_order_id],
                            ['type' => 'text', 'text' => $booking_details['room']],
                            ['type' => 'text', 'text' => $booking_details['datetime']]
                        ]
                    ]
                ];

                // Send to Parent
                $parent_phone = preg_replace('/[^0-9]/', '', $parent_order->get_billing_phone());
                if (strlen($parent_phone) === 10)
                    $parent_phone = '91' . $parent_phone;
                send_whatsapp_template_msg($parent_phone, 'booking_confirmation_all_parties_done', $final_components);

                // Child order notification

                $child_subject = "Accordhub - Payment Completed by all the Parties (Booking ID - {$parent_order_id})";
                $email_heading = "Your Room Booking Payment is Completed by All the Parties";
                $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;
                $child_message = "
                    <p>Dear Customer,</p>
                    <p>The payment has been successfully completed by all the parties added to the split share for the room booking at Accordhub.</p> 
                    <p><strong>Booking ID:</strong> {$parent_order_id}</p>
                    <p><strong>Room:</strong> {$booking_details['room']}</p>
                    <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p> 
                    <p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>
                    <p>Through this booking you agree to our <a href='{$terms}'>terms & conditions</a> and <a href='{$policy}'>privacy policy</p>";

                $additional_payers = $parent_order->get_meta('group_additional_payers');
                foreach ($additional_payers as $index => $payer) {
                    send_woocommerce_custom_email(
                        $payer['email'],
                        $child_subject,
                        $email_heading,
                        $child_message,
                    );
                    $final_component = [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $payer['name']], // Variable {{1}}
                                ['type' => 'text', 'text' => (string) $parent_order_id],
                                ['type' => 'text', 'text' => $booking_details['room']],
                                ['type' => 'text', 'text' => $booking_details['datetime']]
                            ]
                        ]
                    ];

                    // Send to Parent
                    $payer_phone = preg_replace('/[^0-9]/', '', $payer['phone']);
                    if (strlen($payer_phone) === 10)
                        $payer_phone = '91' . $payer_phone;
                    send_whatsapp_template_msg($payer_phone, 'booking_confirmation_all_parties_done', $final_component);
                }

            }

        }
    }
}



// Handle AJAX request
add_action('wp_ajax_update_members', 'handle_update_members'); // logged-in users
add_action('wp_ajax_nopriv_update_members', 'handle_update_members'); // guests if needed

function handle_update_members()
{
    $numbr = isset($_POST['number']) ? sanitize_text_field($_POST['number']) : '';

    // Loop through the cart and update the meta
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['phive_booked_persons'])) {
            WC()->cart->cart_contents[$cart_item_key]['phive_booked_persons'] = array($numbr);
        }
    }

    // Save cart to session
    WC()->cart->set_session();

    // Return response
    wp_send_json_success([
        'message' => 'Updated successfully',
        'updated_value' => $numbr,
        'cart' => WC()->cart->get_cart()
    ]);
}


// Handle AJAX request
add_action('wp_ajax_update_cart_members', 'handle_update_cart_members'); // logged-in users
add_action('wp_ajax_nopriv_update_cart_members', 'handle_update_cart_members'); // guests if needed

function handle_update_cart_members()
{
    // Sanitize inputs
    $numbr = isset($_POST['number']) ? (int) sanitize_text_field($_POST['number']) : 0;
    $cartname = isset($_POST['cart']) ? sanitize_text_field($_POST['cart']) : '';
    $user_id = get_current_user_id();

    // Fetch saved carts
    $saved_carts = get_user_meta($user_id, '_saved_carts', true);

    if (is_array($saved_carts) && isset($saved_carts[$cartname])) {

        foreach ($saved_carts[$cartname]['items'] as &$item) {

            // 🎯 Only update if phive_booked_persons exists
            if (isset($item['phive_booked_persons'])) {

                // ✅ Set correct array structure
                $item['phive_booked_persons'] = [$numbr];

                // ✅ Update persons_count inside product object
                if (isset($item['data']) && is_object($item['data'])) {
                    $item['data']->persons_count = $numbr;
                }
            }
        }

        // ✅ Save back to user meta (correct key)
        update_user_meta($user_id, '_saved_carts', $saved_carts);
    }

    // ✅ Return success response
    wp_send_json_success([
        'message' => 'Members updated successfully',
        'updated_value' => $numbr,
        'cart' => $cartname,
        'data' => $saved_carts[$cartname]['items'] ?? []
    ]);
}


add_action('wp_ajax_update_order_members', 'handle_update_order_members');
add_action('wp_ajax_nopriv_update_order_members', 'handle_update_order_members');

function handle_update_order_members()
{
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $numbr = isset($_POST['number']) ? sanitize_text_field($_POST['number']) : '';

    if (!$order_id || empty($numbr)) {
        wp_send_json_error(['message' => 'Missing order ID or number']);
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error(['message' => 'Order not found']);
    }

    // Loop through order items and update the meta
    foreach ($order->get_items() as $item_id => $item) {
        if ($item->get_meta('Total Participants')) {
            wc_update_order_item_meta($item_id, 'Total Participants', $numbr);
        }
    }

    // Optionally update at order level too
    $order->update_meta_data('Total Participants', $numbr);
    $order->save();

    wp_send_json_success([
        'message' => 'Booking updated successfully',
        'updated_value' => $numbr
    ]);
}




add_filter('woocommerce_available_payment_gateways', 'custom_cod_for_addon_orders', 100);
function custom_cod_for_addon_orders($available_gateways)
{
    // Do not interfere in admin
    if (is_admin() && !defined('DOING_AJAX')) {
        return $available_gateways;
    }

    if (!function_exists('WC') || !WC()->session) {
        return $available_gateways;
    }

    // ✅ Check parent order ID from session
    $parent_order_id = WC()->session->get('parent_order_id');

    // ❌ No parent order → remove COD
    if (empty($parent_order_id)) {
        unset($available_gateways['cod']);
        return $available_gateways;
    }

    // ✅ Parent order exists → allow ONLY COD
    if (isset($available_gateways['cod'])) {
        return array(
            'cod' => $available_gateways['cod']
        );
    }

    return $available_gateways;
}

// add_action('init', function () {
//     // Check if user is logged in and trying to access wp-admin
//     if (is_user_logged_in() && is_admin()) {
//         $user = wp_get_current_user();

//         // Allow admins and editors to stay
//         if (in_array('administrator', (array) $user->roles) || in_array('editor', (array) $user->roles)) {
//             return;
//         }

//         // If not admin/editor, logout and redirect to wp-login.php
//         wp_logout();
//         wp_redirect(admin_url());
//         exit;
//     }
// });

function custom_tab_section_shortcode()
{
    ob_start(); ?>
    <script src="https://unpkg.com/lottie-web@latest/build/player/lottie.min.js"></script>

    <style>
        /* Loader */
        .custom-tab #loader {
            position: absolute;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: -1;
            top: 50%;
            transform: translateY(-50%);
            height: 100%;
        }

        .custom-tab .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #e0e0e0;
            border-top: 6px solid #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }


        .custom-tab {
            position: relative;
            min-height: 507px;
        }


        .custom-tab #adr-section {
            transition: opacity 1s ease-in-out;
            opacity: 1;
            height: auto;
        }

        div.illustration svg {
            max-height: 507px;
        }

        div#illustration4 svg {
            scale: 1.5;
            transform-origin: center;
        }

        .right-content {
            height: calc(100% - 63.59px);
        }

        .custom-tab #adr-section.hidden-2 {
            opacity: 0;
        }

        .custom-tab .container {
            max-width: 1397px;
            margin: auto;
            text-align: center;
            padding: 0 20px;
        }

        .custom-tab h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .custom-tab .intro {
            font-size: 15px;
            color: #555;
            max-width: 800px;
            margin: 0 auto 50px;
            line-height: 1.6;
        }

        .custom-tab .content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .custom-tab .left-tabs {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 22.2px;
        }

        .custom-tab .content h3 {
            font-family: Inter;
            font-weight: 500;
            font-size: 28px;
            line-height: 120%;
            text-align: center;
            vertical-align: middle;
            text-transform: capitalize;
            color: #191919;
            margin-bottom: 30px;
            margin-top: 0;
            letter-spacing: .28px;
        }

        .left-part {
            flex: 1;
            max-width: 682px;
            padding: 0 32px;
        }

        .custom-tab .tab {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border-radius: 12px;
            padding: 29.8px 22.2px;
            cursor: pointer;
            transition: background 1s ease, transform 1s ease, border 1s ease;
            border: 1px solid rgba(229, 231, 235, 0.8);
        }

        .custom-tab .tab .icon {
            line-height: 0;
        }

        .custom-tab .tab:hover {
            transform: translateX(4px);
        }

        .custom-tab .tab::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #1763B9 39.9%, #2F87ED 100%);
            opacity: 0;
            transition: opacity 1s ease;
            /* ✅ fades smoothly */
            z-index: 0;
            border-radius: 10px;
        }

        .custom-tab .tab.active::before {
            opacity: 1;
        }

        .custom-tab .tab>* {
            position: relative;
            z-index: 1;
        }


        .custom-tab .tab.active * {
            color: white;
        }

        .custom-tab .tab h4 {
            margin-bottom: 7.4px;
            font-family: Inter;
            font-weight: 500;
            font-size: 22px;
            line-height: 120%;
            vertical-align: middle;
            text-transform: capitalize;
            margin-top: 0;
            text-align: left;
        }

        .custom-tab .tab p {
            font-family: Inter;
            font-weight: 400;
            font-size: 16px;
            line-height: 140%;
            vertical-align: middle;
            margin: 0;
            text-align: left;
            color: #272727;
        }

        .custom-tab .tab .icon {
            max-width: 59.21px;
            min-width: 59.21px;
        }

        .custom-tab .tab .icon img {
            width: 59.21px;
        }

        .custom-tab .tab.active .icon img {
            filter: brightness(0) invert(1);
        }

        .custom-tab .right-content {

            border-radius: 12px;
            padding: 28px 34px;
            background: #fff;
            border: 1px solid rgba(229, 231, 235, 0.8);
            display: block;
            position: relative;

            text-align: left;
            overflow: hidden;
            transition: all 1.5s ease;
        }

        .right-part {
            height: -webkit-fill-available;
            flex: 1.1;
            max-width: 714px;
            padding: 0 48px;
        }

        /*.custom-tab .content-box {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            display: none;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            opacity: 0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             transform: translateY(15px); 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            transition: all 1s ease;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        }

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        .custom-tab .content-box.active {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            display: block;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            opacity: 1;*/
        /* transform: translateY(0); */
        /* transition: all 1s ease;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        } */
        .content-box {
            opacity: 0;
            /* transform: translateY(10px); */
            transition: opacity 1.5s ease;
            max-width: 548px;
            display: none;
        }

        .content-box.fade-in {
            opacity: 1;
            display: flex;
            height: -webkit-fill-available;
            flex-direction: column;
            justify-content: space-between;
        }

        .content-box:not(.fade-in) {
            position: absolute;
        }

        .content-box.fade-out {
            opacity: 0;
        }

        .right-content:has(.content-box.fade-out) {
            opacity: 0;
        }

        .custom-tab .content-box h3 {
            margin-bottom: 6.2px;
            font-family: Inter;
            font-weight: 500;
            font-size: 22px;
            line-height: 120%;
            vertical-align: middle;
            text-transform: capitalize;
            color: #191919;
        }

        .custom-tab .content-box p {
            font-family: Inter;
            font-weight: 400;
            font-size: 16px;
            line-height: 21px;
            vertical-align: middle;
            color: #272727;
            margin-bottom: 22px;
        }

        .custom-tab .content-box ul {
            list-style: none;
            margin-bottom: 22.56px;
            padding: 0;
        }

        .custom-tab .content-box ul li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 10px;
            font-family: Inter;
            font-weight: 400;
            font-size: 16px;
            line-height: 21px;
            vertical-align: middle;
            color: #272727;
        }

        .custom-tab .content-box ul li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 3px;
            width: 20px;
            height: 20px;
            background: url('data:image/svg+xml;utf8,<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 10C0 4.49219 4.45312 0 10 0C15.5078 0 20 4.49219 20 10C20 15.5469 15.5078 20 10 20C4.45312 20 0 15.5469 0 10ZM14.4922 8.28125C14.9219 7.85156 14.9219 7.1875 14.4922 6.75781C14.0625 6.32812 13.3984 6.32812 12.9688 6.75781L8.75 10.9766L6.99219 9.25781C6.5625 8.82812 5.89844 8.82812 5.46875 9.25781C5.03906 9.6875 5.03906 10.3516 5.46875 10.7812L7.96875 13.2812C8.39844 13.7109 9.0625 13.7109 9.49219 13.2812L14.4922 8.28125Z" fill="%235D92CE"/></svg>') no-repeat center center;
            background-size: contain;
            top: 0;
        }

        .custom-tab .content-box .illustration img {
            width: 100%;
            max-width: 100%;
            display: block;
            margin: 0 auto;
            max-height: 515px;
        }

        .custom-tab .adr-logo-wrapper {
            width: 70%;
            height: 70%;
            border-radius: 50%;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            cursor: pointer;
            /* transition: all 0.4s ease; */
            margin: auto;
            /* animation: pulseScale 2s ease-in-out infinite; */
            transform-origin: center;
            margin-top: 55px;
            border: none !important;
        }

        .flx img {
            width: 59.21px;
        }

        @keyframes pulseScale {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .custom-tab .adr-logo {
            width: 70px;
            transition: transform 0.3s ease;
        }

        .adr-logo-wrapper {
            width: 252px;
            height: 252px;
            border-width: 1.5px;
        }

        .adr-logo {
            width: 52.5px;
        }

        /* #base-A,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        #base-arch,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        #white-cut {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            fill: #000;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        }

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        #white-cut {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            fill: #fff;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        } */

        /* #arch-animated {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            fill: #000;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            opacity: 0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            transform-box: fill-box;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            transform-origin: bottom left;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        }



                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        #logo-svg #arch-animated {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            animation: reveal 1.2s ease forwards;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        } */

        /* @keyframes reveal {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            from {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                opacity: 0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                clip-path: inset(0 100% 0 0);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            to {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                opacity: 1;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                clip-path: inset(0 0 0 0);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        }

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        :root #logo-svg {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            width: 80%;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            height: auto;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            display: block;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            margin: auto;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        } */

        div#logo-button {
            width: inherit;
            max-width: 740px;
        }

        .flx {
            display: flex;
            align-items: flex-start;
            gap: 12.95px;
        }

        .inf * {
            text-align: left !important;
            margin: 0;
        }

        div.illustration {
            background-color: #e4effa;
            border-radius: 10px;
            height: -webkit-fill-available;
        }

        #loader {
            /* display: none; */
            opacity: 1;
            transition: opacity 1s ease;
        }
    </style>
    <div class="custom-tab">
        <!-- Loader -->
        <div id="loader">
            <div class="adr-logo-wrapper">
                <style>
                    /* Circular button */
                    .logo-btn {
                        width: var(--size);
                        height: var(--size);
                        border-radius: 50%;
                        display: grid;
                        place-items: center;
                        box-shadow:
                            0 0 0 0 rgba(0, 0, 0, .08),
                            inset 0 0 0 var(--ring) rgba(0, 0, 0, .10);
                        /* transition: box-shadow .2s ease, transform .12s ease; */
                        cursor: pointer;
                        margin: 32px auto;
                        position: relative;
                        z-index: 1;
                        margin-top: 0;
                    }

                    .tab.active {
                        border: 0.93px solid #2B7DDA;
                        ;
                    }

                    /* ✨ Outer glow ring (outlined, not filled) */
                    .logo-btn::after {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        border-radius: 50%;
                        border: 2px solid rgba(0, 0, 0, 0.15);
                        /* glow thickness & color */
                        transform: scale(1);
                        opacity: 0;
                        z-index: -1;
                        /* animation: pulse-ring 4s ease-out infinite; */
                        /* ⏳ 3 sec cycle */
                        pointer-events: none;
                    }

                    /* On hover */
                    .logo-btn:hover {}

                    /* Focus styles */
                    .logo-btn:focus-visible {
                        outline: none;
                    }

                    /* Pulse ring animation */
                    @keyframes pulse-ring {
                        0% {
                            transform: scale(1);
                            opacity: 0.8;
                        }

                        70% {
                            transform: scale(1.8);
                            opacity: 0;
                        }

                        100% {
                            opacity: 0;
                        }
                    }

                    /* Stop glow when clicked */
                    .logo-btn.no-glow::after {
                        animation: none;
                        opacity: 0;
                    }

                    @media (prefers-reduced-motion: reduce) {
                        .logo-btn::after {
                            animation: none;
                        }

                        #logo-svg.play #arch-animated {
                            animation: none !important;
                            opacity: 1;
                        }
                    }
                </style>
                <style>
                    #base-A,
                    #base-arch,
                    #white-cut {
                        fill: #1763b9;
                    }

                    #white-cut {
                        fill: #fff;
                    }

                    #arch-animated {
                        fill: #1763b9;
                        opacity: 0;
                        transform-box: fill-box;
                        transform-origin: bottom left;
                    }

                    #logo-svg.play #arch-animated {
                        animation: reveal 1.2s ease forwards;
                    }

                    @keyframes reveal {
                        from {
                            opacity: 0;
                            clip-path: inset(0 100% 0 0);
                        }

                        to {
                            opacity: 1;
                            clip-path: inset(0 0 0 0);
                        }
                    }

                    :root #logo-svg {
                        width: 78%;
                        height: auto;
                        display: block;
                    }
                </style>
                <!-- ====== Circular Button Wrapper ====== -->
                <div class="logo-btn" id="logo-button" role="button" aria-label="Play Accordhub logo animation"
                    tabindex="0">

                    <!-- ====== Inline SVG Logo ====== -->
                    <svg xmlns="http://www.w3.org/2000/svg" id="logo-svg" viewBox="100 120 1050 720" role="img"
                        aria-label="Accordhub Logo">


                        <!-- Static logo paths -->
                        <path id="base-arch"
                            d="M740.27,830.76v-318.14c-37-6.15-75.44,4.24-105.46,26.16-64.51,47.11-89.53,114.8-93.2,195.4-1.45,31.9.69,64.73.67,96.58h197.99Z" />
                        <path id="base-A"
                            d="M671.77,136.32l-146.89.5L138.92,831.58l116.82-.68.17-1.02,93.18-215.24.41-.41.43-.43-.08.08.12-.2,8.01-9.76-8,9.83-.04.04,3.37-3.38,5.28-7.1,179.49-177.4-180.12,177.49,180.08-177.41,198.47-72.17.24-.08v.26s0-152.95,0-152.95c0-35.85-29.12-64.87-64.97-64.75ZM736.73,354.02l-186.72,67.15-11.92,4.75.04.04,198.59-71.95h0Z" />
                        <path id="white-cut" class="st0" d="M538.13,425.97v-167.47l-188.12,355.05,188.12-187.58Z" />
                        <path id="arch-animated" class="st1"
                            d="M538.08,426.11s146.19-67.82,212.8-74.1c27.71-2.61,55.71,1.82,82.96,7.43,27.08,5.58,53.57,14.04,78.96,24.98,25.54,11.01,50.01,24.55,72.86,40.41,22.61,15.7,43.65,33.71,62.39,53.88,18.38,19.78,34.54,41.68,47.54,65.36,12.88,23.48,22.6,48.7,28.32,74.88,14.95,68.44,20.13,140.6,16.77,209.81l-218.88,2.13c1.92-62.31,4.96-129.72-1.35-191.51-13.69-134.2-147.39-213.49-266.37-174.59-25.63,8.39-49.04,22.39-69.36,40.11l-328.97,325.99,19.36-131.37,1.7-16.51,73.11-69.67,3.2-3.05,5.83-5.56,1.7-1.7,111.46-111.14,65.98-65.79,5.05,4.65-188.99,188.07-4.06-5.2,188-187.52" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Section -->
        <section id="adr-section" class="hidden-2">
            <div class="container">
                <div class="content elementor-hidden-mobile elementor-hidden-tablet">
                    <div class="left-part">
                        <h3>Why dedicated ADR spaces matter?</h3>
                        <!-- Tabs -->
                        <div class="left-tabs">
                            <div class="tab active" data-index="0">
                                <div class="icon"><img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/1.png"
                                        alt="">
                                </div>
                                <div>
                                    <h4>Confidentiality Risks</h4>
                                    <p>Sensitive case material can leak in non-soundproof venues with external
                                        disturbances
                                    </p>
                                </div>
                            </div>

                            <div class="tab" data-index="1">
                                <div class="icon"><img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/2.png"
                                        alt="">
                                </div>
                                <div>
                                    <h4>Inconvenient Locations</h4>
                                    <p>Venues far from courts and financial hubs add delays, travel costs, and
                                        inefficiency
                                    </p>
                                </div>
                            </div>

                            <div class="tab" data-index="2">
                                <div class="icon"><img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/3.png"
                                        alt="">
                                </div>
                                <div>
                                    <h4>Tech Infrastructure Gaps</h4>
                                    <p>Poor internet, unreliable VC/AV, and no backups disrupt arbitration proceedings
                                    </p>
                                </div>
                            </div>

                            <div class="tab" data-index="3">
                                <div class="icon"><img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/4.png"
                                        alt="">
                                </div>
                                <div>
                                    <h4>Cumbersome Booking & Coordination</h4>
                                    <p>Hotels and clubs require multiple calls, emails, and negotiations, with no
                                        structured
                                        ADR
                                        booking system</p>
                                </div>
                            </div>

                            <div class="tab" data-index="4">
                                <div class="icon"><img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/5.png"
                                        alt="">
                                </div>
                                <div>
                                    <h4>Opaque Costing</h4>
                                    <p>Charges for venue, AV, and admin support are inconsistent and unpredictable</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="right-part">
                        <h3>how Accordhub bridges the gap</h3>
                        <!-- Right Content -->
                        <div class="right-content">
                            <div class="content-box active" data-index="0">
                                <div class="flx">
                                    <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-1.png" alt="">
                                    <div class="inf">
                                        <h3>Secure Spaces</h3>
                                        <p>Confidential, soundproof hearing rooms with access control.</p>
                                        <ul>
                                            <li>Soundproof venues that ensure complete privacy</li>
                                            <li>Neutral, professional spaces for fair hearings</li>
                                            <li>Secure, tech-enabled rooms for confidential meets</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="illustration" id="illustration1">

                                </div>
                            </div>

                            <div class="content-box" data-index="1">
                                <div class="flx">
                                    <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-2.png" alt="">
                                    <div class="inf">
                                        <h3>Prime & Strategic Venues</h3>
                                        <p>Strategically located centres in Jaipur & Jodhpur, accessible to NCR & Rajasthan
                                            parties.</p>
                                        <ul>
                                            <li>Centrally located in Jaipur for easy citywide access</li>
                                            <li>Close to Rajasthan High court - Jaipur & other legal zones</li>
                                            <li>Reduces travel time, costs, and scheduling gaps</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="illustration" id="illustration2">

                                </div>
                            </div>

                            <div class="content-box" data-index="2">
                                <div class="flx">
                                    <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-3.png" alt="">
                                    <div class="inf">
                                        <h3>Uninterrupted Hearings</h3>
                                        <p>Integrated full VC and AV setup with reliable backups.</p>
                                        <ul>
                                            <li>High-speed internet ensures seamless sessions</li>
                                            <li>Advanced AV setup for clear, hybrid hearings</li>
                                            <li>Backup power and network for zero downtime</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="illustration" id="illustration3">

                                </div>
                            </div>

                            <div class="content-box" data-index="3">
                                <div class="flx">
                                    <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-4.png" alt="">
                                    <div class="inf">
                                        <h3>One-Click Venue Booking</h3>
                                        <p>Smart case-linked booking in just a few clicks - simplifying scheduling and
                                            coordination.</p>
                                        <ul>
                                            <li>Instant booking with real-time availability</li>
                                            <li>Eliminate manual calls, emails, and follow-ups</li>
                                            <li>Automated scheduling and coordination tools</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="illustration" id="illustration4">

                                </div>
                            </div>

                            <div class="content-box" data-index="4">
                                <div class="flx">
                                    <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-5.png" alt="">
                                    <div class="inf">
                                        <h3>Transparent & Predictable Pricing</h3>
                                        <p>Transparent, pay-per-use pricing - no hidden charges.</p>
                                        <ul>
                                            <li>Upfront packages with all costs included</li>
                                            <li>No hidden charges or surprise add-ons</li>
                                            <li>Consistent pricing across all venues</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="illustration" id="illustration5">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="adr-slider-component elementor-hidden-desktop">
                    <h2>Why Dedicated ADR Spaces Matter?</h2>

                    <div class="swiper">
                        <div class="swiper-wrapper">

                            <div class="swiper-slide">
                                <div class="card">
                                    <div class="card-header">
                                        <img decoding="async"
                                            src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/1.png" alt="">
                                        <h4>Confidentiality Risks</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-promise">
                                            <div class="promise-icon">
                                                <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Ellipse-5-1.png"
                                                    alt="">
                                            </div>
                                            <h3>The Accordhub Promise</h3>
                                        </div>
                                        <div class="card-content-wrapper">
                                            <div class="feature-box">
                                                <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-1.png"
                                                    alt="">
                                                <p>Secure Spaces</p>
                                            </div>
                                            <ul class="features-list ticklist">
                                                <li> <span>Soundproof venues that ensure complete privacy</span></li>
                                                <li> <span>Neutral, professional spaces for fair hearings</span></li>
                                                <li> <span>Secure, tech-enabled rooms for confidential meets</span></li>
                                            </ul>
                                        </div>
                                        <div class="card-image lottie-anim" id="illustration6">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="card">
                                    <div class="card-header">
                                        <img decoding="async"
                                            src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/2.png" alt="">
                                        <h4>Inconvenient Locations</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-promise">
                                            <div class="promise-icon">
                                                <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Ellipse-5-1.png"
                                                    alt="">
                                            </div>
                                            <h3>The Accordhub Promise</h3>
                                        </div>
                                        <div class="card-content-wrapper">
                                            <div class="feature-box">
                                                <img decoding="async"
                                                    src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-2.png"
                                                    alt="">
                                                <p>Prime & Strategic Venues</p>
                                            </div>
                                            <ul class="features-list ticklist">
                                                <li> <span>Centrally located in Jaipur for easy citywide access</span>
                                                </li>
                                                <li> <span>Close to Rajasthan High court - Jaipur & other legal zones</span>
                                                </li>
                                                <li> <span>Reduces travel time, costs, and scheduling gaps</span></li>
                                            </ul>
                                        </div>
                                        <div class="card-image lottie-anim" id="illustration7">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="card">
                                    <div class="card-header">
                                        <img decoding="async"
                                            src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/3.png" alt="">
                                        <h4>Tech Infrastructure Gaps</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-promise">
                                            <div class="promise-icon">
                                                <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Ellipse-5-1.png"
                                                    alt="">
                                            </div>
                                            <h3>The Accordhub Promise</h3>
                                        </div>
                                        <div class="card-content-wrapper">
                                            <div class="feature-box">
                                                <img decoding="async"
                                                    src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-3.png"
                                                    alt="">
                                                <p>Uninterrupted Hearings</p>
                                            </div>
                                            <ul class="features-list ticklist">
                                                <li> <span>High-speed internet ensures seamless sessions</span>
                                                </li>
                                                <li> <span>Advanced AV setup for clear, hybrid hearings</span></li>
                                                <li> <span>Backup power and network for zero downtime</span></li>
                                            </ul>
                                        </div>
                                        <div class="card-image lottie-anim" id="illustration8">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="card">
                                    <div class="card-header">
                                        <img decoding="async"
                                            src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/4.png" alt="">
                                        <h4>Cumbersome Booking & Coordination</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-promise">
                                            <div class="promise-icon">
                                                <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Ellipse-5-1.png"
                                                    alt="">
                                            </div>
                                            <h3>The Accordhub Promise</h3>
                                        </div>
                                        <div class="card-content-wrapper">
                                            <div class="feature-box">
                                                <img decoding="async"
                                                    src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-4.png"
                                                    alt="">
                                                <p>One-Click Venue Booking</p>
                                            </div>
                                            <ul class="features-list ticklist">
                                                <li> <span>Instant booking with real-time availability</span>
                                                </li>
                                                <li> <span>Eliminate manual calls, emails, and follow-ups</span></li>
                                                <li> <span>Automated scheduling and coordination tools</span></li>
                                            </ul>
                                        </div>
                                        <div class="card-image lottie-anim" id="illustration9">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="card">
                                    <div class="card-header">
                                        <img decoding="async"
                                            src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/5.png" alt="">
                                        <h4>Opaque Costing</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-promise">
                                            <div class="promise-icon">
                                                <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Ellipse-5-1.png"
                                                    alt="">
                                            </div>
                                            <h3>The Accordhub Promise</h3>
                                        </div>
                                        <div class="card-content-wrapper">
                                            <div class="feature-box">
                                                <img decoding="async"
                                                    src="<?php bloginfo('url') ?>/wp-content/uploads/2025/11/Mask-group-5.png"
                                                    alt="">
                                                <p>Transparent & Predictable Pricing</p>
                                            </div>
                                            <ul class="features-list ticklist">
                                                <li> <span>Upfront packages with all costs included</span>
                                                </li>
                                                <li> <span>No hidden charges or surprise add-ons</span></li>
                                                <li> <span>Consistent pricing across all venues</span></li>
                                            </ul>
                                        </div>
                                        <div class="card-image lottie-anim" id="illustration10">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>

                    <div class="swiper-pagination"></div>

                </div>
            </div>
        </section>
    </div>
    <script>
        const shell = document.getElementById('logo-button');
        const svg = document.getElementById('logo-svg');

        function replay() {
            // Stop glow effect after click
            shell.classList.add('no-glow');

            // Restart SVG animation
            svg.classList.remove('play');
            void svg.offsetWidth; // force reflow
            svg.classList.add('play');
        }

        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.content-box[data-index]');
        let currentIndex = 0;
        let intervalId;

        function playLottieAnimation(index) {
            // Get the ID of the illustration container for the current tab index
            const illustrationId = 'illustration' + (index + 1); // Assuming illustration IDs are illustration1, illustration2, etc.

            // Pause all Lottie instances first to ensure only the new one plays
            Object.values(lottieInstances).forEach(anim => anim.pause());

            // Play the specific animation
            const animToPlay = lottieInstances[illustrationId];
            if (animToPlay) {
                // Use goToAndPlay(0, true) to ensure it restarts from the beginning
                animToPlay.goToAndPlay(0, true);
            }
        }

        // Show a tab with fade animation
        let animationTimeout = null;
        let isTransitioning = false;

        function showTab(index) {
            if (isTransitioning) return; // ⛔ Prevent rapid clicks during animation
            isTransitioning = true;

            tabs.forEach(t => t.classList.remove('active'));
            tabs[index].classList.add('active');

            const newContent = document.querySelector(`.content-box[data-index="${index}"]`);
            const activeContent = document.querySelector('.content-box.fade-in');

            // Clear any previous pending timeout
            if (animationTimeout) {
                clearTimeout(animationTimeout);
                animationTimeout = null;
            }

            if (activeContent && activeContent !== newContent) {
                activeContent.classList.remove('fade-in');
                activeContent.classList.add('fade-out');

                animationTimeout = setTimeout(() => {
                    activeContent.classList.remove('fade-out');
                    newContent.classList.add('fade-in');

                    playLottieAnimation(index);

                    isTransitioning = false; // 🔓 Unlock clicks
                }, 300); // Match your CSS transition time
            } else {
                newContent.classList.add('fade-in');
                playLottieAnimation(index);
                isTransitioning = false; // 🔓 Unlock immediately
            }
        }



        function startTabCycle(startFrom = 0) {
            currentIndex = startFrom;
            showTab(currentIndex);
            clearInterval(intervalId);
            intervalId = setInterval(() => {
                currentIndex = (currentIndex + 1) % tabs.length;
                showTab(currentIndex);
            }, 8000);
        }

        function pauseTabCycle() {
            clearInterval(intervalId);
        }

        // Manual tab click
        tabs.forEach((tab, i) => {
            tab.addEventListener('click', () => {
                startTabCycle(i);
            });
        });

        window.addEventListener('load', () => {
            const loader = document.getElementById('loader');
            const section = document.getElementById('adr-section');
            const tabSection = document.querySelector('.custom-tab');
            let hasStarted = false;

            // First observer – triggers loader and tab cycle
            const initialObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !hasStarted) {
                        hasStarted = true;

                        // Show loader
                        loader.style.display = 'flex';
                        replay();
                        setTimeout(() => loader.style.opacity = '1', 50);

                        // Hide section initially
                        section.classList.remove('visible-2');

                        // After 2s → hide loader + show section
                        setTimeout(() => {
                            loader.style.opacity = '0';
                            section.classList.add('visible-2');
                            section.classList.remove('hidden-2');

                            // optional: hide loader fully after fade
                            setTimeout(() => loader.style.display = 'flex', 500);

                            startTabCycle(); // start tab rotation
                        }, 1000);

                        initialObserver.unobserve(tabSection);
                    }
                });
            }, { threshold: 0.5 });

            if (tabSection) initialObserver.observe(tabSection);

            // Second observer – pauses/resumes tab auto-rotation
            const sectionObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        startTabCycle(currentIndex);
                    } else {
                        pauseTabCycle();
                    }
                });
            }, { threshold: 0.3 });

            if (section) sectionObserver.observe(section);
        });
    </script>
    <script>
        const animations = [
            { id: 'illustration1', path: "<?php bloginfo('url') ?>" + '/wp-content/uploads/2025/11/Confidential-1-1.json' },
            { id: 'illustration2', path: "<?php bloginfo('url') ?>" + '/wp-content/uploads/2025/11/Prime-Strategic-Venues.json' },
            { id: 'illustration3', path: "<?php bloginfo('url') ?>" + '/wp-content/uploads/2025/11/Uninterrupted-Hearings.json' },
            { id: 'illustration4', path: "<?php bloginfo('url') ?>" + '/wp-content/uploads/2025/11/One-click-booking.json' },
            { id: 'illustration5', path: "<?php bloginfo('url') ?>" + '/wp-content/uploads/2025/11/Transparent-Pricing.json' },
        ];

        const lottieInstances = {};
        let anyvisible = false;

        // Load animations (autoplay disabled)
        animations.forEach(item => {
            const container = document.getElementById(item.id);

            if (container) {
                const anim = lottie.loadAnimation({
                    container: container,
                    renderer: 'svg',
                    loop: true,
                    autoplay: false,
                    path: item.path
                });

                lottieInstances[item.id] = anim;
            }
        });

        // Intersection Observer
        const observer = new IntersectionObserver((entries) => {
            // Check if any of the observed elements are visible-2
            anyvisible = entries.some(entry => entry.isIntersecting);

            // Play or pause all animations together
            if (anyvisible) {
                Object.values(lottieInstances).forEach(anim => anim.play(0, true));
            } else {
                Object.values(lottieInstances).forEach(anim => anim.pause());
            }
        }, {
            threshold: .3 // Trigger when 30% visible-2
        });

        // Observe all containers
        animations.forEach(item => {
            const container = document.getElementById(item.id);
            if (container) observer.observe(container);
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('custom_tab_section', 'custom_tab_section_shortcode');

add_filter('woocommerce_price_trim_zeros', '__return_true');




/**
 * Register [virtual_tour_tabs] shortcode (image version)
 */
add_action('init', function () {
    add_shortcode('virtual_tour_tabs', 'vt_render_virtual_tour_tabs_image');
});

function vt_render_virtual_tour_tabs_image()
{
    $uid = 'vt-' . uniqid();

    $rooms = [
        [
            'slug' => 'overview',
            'title' => 'Overview',
            'desc' => 'Welcome to Accordhub - begin your virtual journey from our central reception lounge.',
            'tab_desc' => 'Start here to explore the Accordhub workspace',
            'thumb' => get_bloginfo('url') . '/wp-content/uploads/2025/12/1eb2a938-7a3c-4b18-bd0a-ebfc90fbb4d4-scaled.jpg',
            'capacity' => ''
        ],
        [
            'slug' => 'brihaspati',
            'title' => 'Brihaspati',
            'desc' => 'Compact & private with full VC setup. Perfect for mediation, caucuses, and smaller hearings.',
            'tab_desc' => 'Our compact room designed for smaller hearings',
            'thumb' => get_bloginfo('url') . '/wp-content/uploads/2025/12/compressed_SUR_6912.webp',
            'capacity' => '10 Max'
        ],
        [
            'slug' => 'kautilya',
            'title' => 'Kautilya',
            'desc' => 'Spacious tribunal room with full VC setup. Ideal for high-stake arbitrations or 2+ party disputes.',
            'tab_desc' => 'Discover our premium tribunal hearing room',
            'thumb' => get_bloginfo('url') . '/wp-content/uploads/2025/12/compressed_IMG_0533.webp',
            'capacity' => '15 Max'
        ],
        [
            'slug' => 'breakout',
            'title' => 'Breakout Lounge',
            'desc' => 'Designed for comfort - relax, recharge, or hold informal discussions between hearings.',
            'tab_desc' => 'Our relaxing lounge for informal meets between sessions',
            'thumb' => get_bloginfo('url') . '/wp-content/uploads/2025/12/compressed_SUR_6921-1.jpg',
            'capacity' => '20 Max'
        ],
    ];

    $main = $rooms[0];
    ob_start();
    ?>
    <section class="virtual-tour" aria-label="Virtual Tour">
        <style>
            :root {
                --bg: #f1f9ff;
                --card: #ffffff;
                --accent: #1e66f5;
                --accent-600: #144dcc;
                --muted: #6b7280;
                --pill: #eaf2ff;
                --radius: 18px;
            }

            /* base */
            * {
                box-sizing: border-box
            }

            html,
            body {
                margin: 0;
                padding: 0
            }

            .virtual-tour {}

            .vt-wrap {
                margin: 0 auto;
                display: flex;
                gap: 36px;
                align-items: flex-start;
            }

            /* MAIN */
            .vt-main {
                width: 1080px;
                min-width: 1080px
            }

            .vt-panel {
                background: transparent;
                border-radius: 14px;
                overflow: hidden;
                opacity: 0;
                max-height: 0;
                transform: translateY(8px);
                transition: opacity 1s ease-in-out;
            }

            .vt-panel.active {
                opacity: 1;
                max-height: 2000px;
                /* large enough to fit content */
                transform: translateY(0);
            }

            .vt-media {
                border-radius: 60px;
                overflow: hidden;
                position: relative;
            }

            /* image container that will be hidden when tour opens */
            .image-container {
                transition: opacity 1s ease-in-out, height 1s ease-in-out, transform 1s ease-in-out, padding 1s ease-in-out;
            }

            .image-container img {
                display: block;
                width: 100%;
                height: 601px;
                object-fit: cover;
                object-position: left;
            }

            .image-container.hidden {
                opacity: 0;
                height: 0;
                padding: 0;
                overflow: hidden;
                transform: scale(.995);
                pointer-events: none;
            }

            a.vt-book {
                width: 250px;
                height: 58px;
                font-family: Inter !important;
                font-weight: 500 !important;
                font-size: 18px !important;
                line-height: 100% !important;
                letter-spacing: 1px !important;
                text-align: center !important;
                vertical-align: middle !important;
                text-transform: capitalize !important;
                color: #ffffff !important;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Take tour centered button */
            .take-tour {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                display: inline-flex;
                gap: 12px;
                align-items: center;
                padding: 10px 16px;
                background: linear-gradient(90deg, var(--accent), #2b86ff);
                color: #fff;
                border-radius: 999px;
                border: 0;
                cursor: pointer;
                z-index: 4;
                font-weight: 700;
            }

            .take-tour .circle {
                border-radius: 50%;
                background: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                left: -14px;
                width: 69px;
                height: 69px;
                box-shadow: 10px 4px 14px 0px #00000021;

            }

            button.take-tour {
                width: 172px;
                height: 52px;
                justify-content: end;
                font-family: Inter;
                font-weight: 400;
                font-size: 16px;
                line-height: 100%;
                letter-spacing: normal;
            }

            span.label {
                width: 100px;
            }

            .pnlm-controls-container {
                position: absolute;
                top: unset;
                left: unset;
                z-index: 1;
                right: 24px;
                bottom: 24px;
            }

            .pnlm-fullscreen-toggle-button.pnlm-sprite.pnlm-fullscreen-toggle-button-inactive.pnlm-controls.pnlm-control {
                background-size: 24px;
                border: 1px solid #fff;
                border-radius: 50%;
                background-position: center;
                background-repeat: no-repeat;
                filter: none;
                background-color: #0A0A0A66;
                background-image: url(<?php bloginfo('url') ?>/wp-content/uploads/2025/11/garden_original-size-stroke-16.png);
                width: 44px;
                height: 44px;
            }

            .pnlm-zoom-controls.pnlm-controls {
                display: none !important;
            }

            .take-tour .circle svg {
                width: 44px;
                height: 44px;
            }

            /* shortcode box (hidden until active) */
            .shortcode-box {
                opacity: 0;
                max-height: 0;
                overflow: hidden;
                transition: opacity .1s ease-in-out;
            }

            .vt-meta {
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            .shortcode-box.active {
                opacity: 1;
                max-height: 1200px;
            }

            .shortcode-inner {
                background: #eef6ff;
                border-radius: 12px;
            }

            /* info row below media */
            .vt-info {
                padding: 15px 13px 0px 13px;
                display: flex;
                justify-content: space-between;
                gap: 9px;
                align-items: end;
            }

            .vt-info h2 {
                margin: 0;
                color: #191919;
                font-family: Inter;
                font-weight: 600;
                font-size: 32px;
                line-height: 38px;
                vertical-align: middle;
                text-transform: capitalize;
            }

            .vt-info p {
                margin: 10px 0 0;
                color: #484848;
                font-family: Inter;
                font-weight: 400;
                font-size: 16px;
                line-height: 22px;
                vertical-align: middle;
            }


            .vt-book {
                color: #fff;
                border: 0;
                padding: 10px 18px;
                border-radius: 999px;
                font-weight: 700;
                cursor: pointer
            }

            /* SIDE */
            .vt-side {
                width: 539px;
                min-width: 539px
            }

            .vt-side .heading {
                margin: 0 0 14px;
                font-size: 18px;
                color: #191919;
                font-family: Inter;
                font-weight: 600;
                font-size: 28px;
                line-height: 120%;
                letter-spacing: 0.28px;
                vertical-align: middle;
                text-transform: capitalize;
            }

            .vt-side p.lead {
                color: #2C2C2C;
                font-family: Inter;
                font-weight: 400;
                font-size: 16px;
                line-height: 1.4;
                vertical-align: middle;
                margin-bottom: 28px;
                padding-right: 1px;
            }

            .vt-list {
                display: flex;
                flex-direction: column;
                gap: 43px
            }

            .vt-card {
                display: flex;
                gap: 10px;
                align-items: start;
                cursor: pointer;
                transition: transform .18s, box-shadow .18s, border-color .18s
            }

            .vt-card.active {
                border-color: rgba(30, 102, 245, 0.18);
                display: none;
            }

            .vt-thumb {
                width: 264px;
                height: 152px;
                border-radius: 30px;
                overflow: hidden;
                background: #f2f8ff;
                position: relative;
                flex-shrink: 0
            }

            .vt-thumb img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block
            }

            .vt-thumb .play {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 69px;
                height: 69px;
                border-radius: 50%;
                background: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 10px 4px 14px 0px #00000021;

            }

            .vt-meta h4 {
                margin: 0;
                font-family: Inter;
                font-weight: 600;
                font-size: 22px;
                line-height: 1.2;
                vertical-align: middle;
                text-transform: capitalize;
                color: #191919;
            }

            .vt-meta p {
                margin: 0;
                color: #2C2C2C;
                font-family: Inter;
                font-weight: 400;
                font-size: 16px;
                line-height: 1.4;
                vertical-align: middle;
            }

            .pax {
                display: flex;
                gap: 8px;
                align-items: center;
                padding: 8px 14px;
                border-radius: 999px;
                background: #CFEBFF80;
                color: #191919;
                font-weight: 500;
                font-size: 16px;
                line-height: 1.4;
                width: fit-content;
            }

            section.virtual-tour .ipnrm-1 {
                height: 601px;
            }

            .ipnrm-tr-bar {
                top: 24px !important;
                right: 24px !important;
                gap: 24px !important;
                padding: 0 !important;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-btn {
                width: 44px !important;
                height: 44px !important;
                margin: 0 !important;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-next-scene:after {
                background-image: url("data:image/svg+xml;utf8,<svg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M4.5 12L19.5 12M19.5 12L13.875 6M19.5 12L13.875 18' stroke='white' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/></svg>");
                background-size: 24px;
                border: 1px solid #fff;
                border-radius: 50%;
                background-position: center;
                background-repeat: no-repeat;
                filter: none;
                background-color: #0A0A0A66;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-btn:before {
                display: none;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-prev-scene {
                display: block !important;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-prev-scene:after {
                background-image: url("data:image/svg+xml;utf8,<svg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M19.5 12L4.5 12M4.5 12L10.125 6M4.5 12L10.125 18' stroke='white' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/></svg>");
                background-size: 24px;
                border: 1px solid #fff;
                border-radius: 50%;
                background-position: center;
                background-repeat: no-repeat;
                filter: none;
                background-color: #0A0A0A66;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-btn:before {
                display: none;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-br-bar {
                bottom: 24px;
                right: 24px;
                gap: 24px;
                padding: 0;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-thumbs:after {
                background-size: 24px;
                border: 1px solid #fff;
                border-radius: 50%;
                background-position: center;
                background-repeat: no-repeat;
                filter: none;
                background-color: #0A0A0A66;
                background-image: url(<?php bloginfo('url') ?>/wp-content/uploads/2025/11/radix-icons_dashboard.png);
            }


            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-fullscreen:after {
                background-size: 24px;
                border: 1px solid #fff;
                border-radius: 50%;
                background-position: center;
                background-repeat: no-repeat;
                filter: none;
                background-color: #0A0A0A66;
                background-image: url(<?php bloginfo('url') ?>/wp-content/uploads/2025/11/garden_original-size-stroke-16.png);
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-bl-bar {
                display: none;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-thumbs-wrap .ipnrm-thumbs-close {
                position: absolute;
                top: 24px;
                left: 24px;
                width: 44px;
                height: 44px;
                cursor: pointer;
                border: 1px solid #fff;
                border-radius: 50%;
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-thumbs-wrap .ipnrm-thumbs-close:before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 24px;
                height: 24px;
                transform: translate(-50%, -50%);
            }

            .ipnrm-theme-light .ipnrm-markers .ipnrm-marker:not(.ipnrm-custom) .ipnrm-body {
                width: 44px;
                height: 44px;
                background: #0A0A0A66;
                border: 1px solid #fff;
                border-radius: 100%;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
            }

            .ipnrm-theme-light .ipnrm-markers .ipnrm-marker:not(.ipnrm-custom).ipnrm-link-scene .ipnrm-body:before {
                width: 12px;
                height: 12px;
            }

            /* responsive */
            @media (max-width:900px) {
                .vt-wrap {
                    gap: 18px;
                    padding: 0 12px
                }
            }

            @media (max-width:700px) {
                .vt-wrap {
                    flex-direction: column
                }

                .vt-side {
                    order: 2
                }

                .vt-main {
                    order: 1
                }
            }

            .ipnrm.ipnrm-scene-active.ipnrm-widget-modern .ipnrm-next-scene {
                display: block !important;
            }

            section.virtual-tour div#master-container.wpvr-cardboard {
                height: 601px !important;
            }
        </style>

        <div class="vt-wrap">
            <!-- MAIN COLUMN -->
            <div class="vt-main">
                <!-- Overview panel -->
                <div class="vt-panel active" id="vt-overview" role="tabpanel" aria-hidden="false">
                    <div class="vt-media">
                        <div class="image-container" data-img>
                            <!-- <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/12/1eb2a938-7a3c-4b18-bd0a-ebfc90fbb4d4-scaled.jpg"
                                alt="Overview" /> -->
                            <?php
                            echo wp_get_attachment_image(36983, 'large', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'class' => 'desktop-img'
                            ]);
                            echo wp_get_attachment_image(36983, 'medium', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'class' => 'mobile-img',
                                'sizes' => '(max-width: 768px) 100vw, 315px'
                            ]);
                            ?>
                            <button class="take-tour" data-action="open-tour" aria-label="Start Tour">
                                <span class="circle" aria-hidden="true">
                                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                                            fill="#1763B9" />
                                    </svg>

                                </span>
                                <span class="label">Start Tour</span>
                            </button>
                        </div>

                        <div class="shortcode-box" data-shortcode>
                            <div class="shortcode-inner">
                                <!-- Replace with your iframe or server-rendered shortcode -->
                                [ipano id="1"]
                                <!-- <iframe width="100%" height="527" src="https://www.youtube.com/embed/CmzKQ3PSrow" title="History of Lorem Ipsum and What It Means" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> -->
                            </div>
                        </div>
                    </div>

                    <div class="vt-info">
                        <div class="vt-flex">
                            <div class="title-btn">
                                <h2>Full Office Tour</h2>
                                <div class="vt-cta"><a href="<?php bloginfo('url') ?>/hearing-rooms-arbitration-adr"
                                        class="vt-book elementor-button">View
                                        <span class="elementor-hidden-mobile">Room</span> Details</a></div>
                            </div>
                            <p>Welcome to Accordhub - begin your virtual journey from our central reception lounge</p>
                        </div>
                        <div class="vt-cta"><a href="<?php bloginfo('url') ?>/hearing-rooms-arbitration-adr"
                                class="vt-book elementor-button">View
                                Room Details</a></div>
                    </div>
                </div>

                <!-- Brihaspati panel -->
                <div class="vt-panel" id="vt-brihaspati" role="tabpanel" aria-hidden="true">
                    <div class="vt-media">
                        <div class="image-container" data-img>
                            <!-- <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/12/compressed_SUR_6909.webp"
                                alt="Brihaspati" /> -->
                            <?php
                            echo wp_get_attachment_image(36986, 'large', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'class' => 'desktop-img'
                            ]);
                            echo wp_get_attachment_image(36986, 'medium', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'class' => 'mobile-img',
                                'sizes' => '(max-width: 768px) 100vw, 315px'
                            ]);
                            ?>
                            <button class="take-tour" data-action="open-tour" aria-label="View 360°">
                                <span class="circle" aria-hidden="true">
                                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                                            fill="#1763B9" />
                                    </svg>

                                </span>
                                <span class="label">View 360°</span>
                            </button>
                        </div>

                        <div class="shortcode-box" data-shortcode>
                            <div class="shortcode-inner">
                                [wpvr id="249"]
                            </div>
                        </div>
                    </div>

                    <div class="vt-info">
                        <div class="vt-flex">
                            <div class="title-btn">
                                <h2>Brihaspati</h2>
                                <div class="vt-cta"><a href="<?php bloginfo('url') ?>/product/brihaspati/"
                                        class="vt-book elementor-button">Book Now</a></div>
                            </div>

                            <p>Compact & private with full VC setup. Perfect for mediation, caucuses, and smaller hearings
                            </p>
                        </div>
                        <div class="vt-cta"><a href="<?php bloginfo('url') ?>/product/brihaspati/"
                                class="vt-book elementor-button">Book Now</a></div>
                    </div>
                </div>

                <!-- Kautilya panel -->
                <div class="vt-panel" id="vt-kautilya" role="tabpanel" aria-hidden="true">
                    <div class="vt-media">
                        <div class="image-container" data-img>
                            <!-- <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/12/compressed_IMG_0533.webp"
                                alt="Kautilya" /> -->
                            <?php
                            echo wp_get_attachment_image(33408, 'large', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'class' => 'desktop-img'
                            ]);
                            echo wp_get_attachment_image(33408, 'medium', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'class' => 'mobile-img',
                                'sizes' => '(max-width: 768px) 100vw, 315px'
                            ]);
                            ?>
                            <button class="take-tour" data-action="open-tour" aria-label="View 360°">
                                <span class="circle" aria-hidden="true">
                                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                                            fill="#1763B9" />
                                    </svg>

                                </span>
                                <span class="label">View 360°</span>
                            </button>
                        </div>

                        <div class="shortcode-box" data-shortcode>
                            <div class="shortcode-inner">
                                [wpvr id="2086"]
                            </div>
                        </div>
                    </div>

                    <div class="vt-info">
                        <div class="vt-flex">
                            <div class="title-btn">
                                <h2>Kautilya</h2>
                                <div class="vt-cta"><a href="<?php bloginfo('url') ?>/product/kautilya/"
                                        class="vt-book elementor-button">Book Now</a></div>
                            </div>

                            <p>Spacious tribunal room with full VC setup. Ideal for high-stake arbitrations or 2+ party
                                disputes</p>
                        </div>
                        <div class="vt-cta"><a href="<?php bloginfo('url') ?>/product/kautilya/"
                                class="vt-book elementor-button">Book Now</a></div>
                    </div>
                </div>

                <!-- Breakout panel -->
                <div class="vt-panel" id="vt-lounge" role="tabpanel" aria-hidden="true">
                    <div class="vt-media">
                        <div class="image-container" data-img>
                            <!-- <img src="<?php bloginfo('url') ?>/wp-content/uploads/2025/12/compressed_SUR_6923.jpg"
                                alt="Breakout Lounge" /> -->
                            <?php
                            echo wp_get_attachment_image(36987, 'large', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'class' => 'desktop-img'
                            ]);
                            echo wp_get_attachment_image(36987, 'medium', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'class' => 'mobile-img',
                                'sizes' => '(max-width: 768px) 100vw, 315px'
                            ]);
                            ?>
                            <button class="take-tour" data-action="open-tour" aria-label="View 360°">
                                <span class="circle" aria-hidden="true">
                                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                                            fill="#1763B9" />
                                    </svg>

                                </span>
                                <span class="label">View 360°</span>
                            </button>
                        </div>

                        <div class="shortcode-box" data-shortcode>
                            <div class="shortcode-inner">
                                [wpvr id="31385"]
                            </div>
                        </div>
                    </div>

                    <div class="vt-info">
                        <div class="vt-flex">
                            <div class="title-btn">
                                <h2>Breakout Lounge</h2>
                                <div class="vt-cta"><a href="<?php bloginfo('url') ?>/hearing-rooms-arbitration-adr/"
                                        class="vt-book elementor-button">View
                                        <span class="elementor-hidden-mobile">Room</span> Details</a></div>
                            </div>

                            <p>Designed for comfort - relax, recharge, or hold informal discussions between hearings</p>
                        </div>
                        <div class="vt-cta"><a href="<?php bloginfo('url') ?>/hearing-rooms-arbitration-adr/"
                                class="vt-book elementor-button">View
                                Room Details</a></div>
                    </div>
                </div>
            </div>

            <!-- SIDE COLUMN -->
            <aside class="vt-side" aria-label="Rooms list">
                <h3 class="heading">Virtual room gallery</h3>
                <p class="lead elementor-hidden-mobile elementor-hidden-tablet">Take a 360° virtual tour of our arbitration
                    and meeting rooms to
                    explore size, setup, and
                    ambience - so you know exactly what to expect before booking your next session</p>

                <div class="vt-list" role="tablist" aria-orientation="vertical">
                    <div class="vt-card active" data-target="vt-overview" role="tab" tabindex="0" aria-selected="true">
                        <div class="vt-thumb">
                            <!-- <img
                                src="<?php bloginfo('url') ?>/wp-content/uploads/2025/12/1eb2a938-7a3c-4b18-bd0a-ebfc90fbb4d4-scaled.jpg"
                                alt=""> -->
                            <?php
                            echo wp_get_attachment_image(36983, 'medium', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'sizes' => '(max-width: 650px) 100vw, 300px'
                            ]);
                            ?>
                            <span class="play"><svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                                        fill="#1763B9" />
                                </svg>
                            </span>
                        </div>
                        <div class="vt-meta">
                            <h4>Full Office Tour</h4>
                            <p>Start here to explore the Accordhub workspace</p>
                            <div class="pax"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 3H21M21 3V9M21 3L14.5 9.5M9 3H3V9M15 21H21V15M9 21H3M3 21V15M3 21L9.5 14.5"
                                        stroke="#191919" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                2500 Sq. ft.</div>
                        </div>
                    </div>

                    <div class="vt-card" data-target="vt-brihaspati" role="tab" tabindex="0" aria-selected="false">
                        <div class="vt-thumb">
                            <!-- <img
                                src="<?php bloginfo('url') ?>/wp-content/uploads/2025/12/compressed_SUR_6912.webp"
                                alt=""> -->
                            <?php
                            echo wp_get_attachment_image(33535, 'medium', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'sizes' => '(max-width: 650px) 100vw, 300px'
                            ]);
                            ?>
                            <span class="play"><svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                                        fill="#1763B9" />
                                </svg>
                            </span>
                        </div>
                        <div class="vt-meta">
                            <h4>Brihaspati</h4>
                            <p>Our compact room designed for smaller hearings</p>
                            <div class="pax"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.28557 3.85725C6.77409 3.85725 6.28355 4.06044 5.92187 4.42211C5.56019 4.78379 5.357 5.27433 5.357 5.78582C5.357 6.29731 5.56019 6.78785 5.92187 7.14952C6.28355 7.5112 6.77409 7.71439 7.28557 7.71439C7.79706 7.71439 8.2876 7.5112 8.64928 7.14952C9.01096 6.78785 9.21415 6.29731 9.21415 5.78582C9.21415 5.27433 9.01096 4.78379 8.64928 4.42211C8.2876 4.06044 7.79706 3.85725 7.28557 3.85725ZM4.07129 5.78582C4.07129 4.93334 4.40994 4.11577 5.01273 3.51298C5.61553 2.91018 6.43309 2.57153 7.28557 2.57153C8.13806 2.57153 8.95562 2.91018 9.55842 3.51298C10.1612 4.11577 10.4999 4.93334 10.4999 5.78582C10.4999 6.6383 10.1612 7.45587 9.55842 8.05866C8.95562 8.66146 8.13806 9.0001 7.28557 9.0001C6.43309 9.0001 5.61553 8.66146 5.01273 8.05866C4.40994 7.45587 4.07129 6.6383 4.07129 5.78582ZM2.57129 12.2144C2.57129 11.7029 2.77448 11.2124 3.13615 10.8507C3.49783 10.489 3.98837 10.2858 4.49986 10.2858H10.0713C10.5828 10.2858 11.0733 10.489 11.435 10.8507C11.7967 11.2124 11.9999 11.7029 11.9999 12.2144V16.7144C11.9999 17.9647 11.5032 19.1638 10.6191 20.0479C9.73498 20.932 8.53588 21.4287 7.28557 21.4287C6.03527 21.4287 4.83617 20.932 3.95207 20.0479C3.06797 19.1638 2.57129 17.9647 2.57129 16.7144V12.2144ZM4.49986 11.5715C4.32936 11.5715 4.16585 11.6393 4.04529 11.7598C3.92473 11.8804 3.857 12.0439 3.857 12.2144V16.7144C3.857 17.6237 4.21823 18.4958 4.86121 19.1388C5.50419 19.7817 6.37626 20.143 7.28557 20.143C8.19489 20.143 9.06696 19.7817 9.70994 19.1388C10.3529 18.4958 10.7141 17.6237 10.7141 16.7144V12.2144C10.7141 12.0439 10.6464 11.8804 10.5259 11.7598C10.4053 11.6393 10.2418 11.5715 10.0713 11.5715H4.49986ZM12.0007 9.0001C11.4015 9.0013 10.814 8.83409 10.3053 8.51753C10.5933 8.20039 10.8316 7.8361 11.0081 7.43925C11.3008 7.61479 11.6348 7.70954 11.976 7.71381C12.3173 7.71808 12.6535 7.63173 12.9505 7.46358C13.2475 7.29542 13.4945 7.05147 13.6663 6.75664C13.8382 6.46181 13.9288 6.12666 13.9288 5.78539C13.9288 5.44412 13.8382 5.10897 13.6663 4.81414C13.4945 4.51931 13.2475 4.27537 12.9505 4.10721C12.6535 3.93905 12.3173 3.8527 11.976 3.85697C11.6348 3.86124 11.3008 3.95599 11.0081 4.13153C10.8318 3.73776 10.5946 3.37417 10.3053 3.0541C10.7279 2.79206 11.2055 2.63163 11.7006 2.58544C12.1957 2.53924 12.6947 2.60854 13.1585 2.78787C13.6223 2.96721 14.0381 3.2517 14.3733 3.61896C14.7085 3.98623 14.9539 4.42626 15.0903 4.90444C15.2266 5.38261 15.2502 5.88591 15.1591 6.37473C15.068 6.86355 14.8647 7.32457 14.5653 7.72153C14.2658 8.11849 13.8784 8.44056 13.4334 8.66242C12.9884 8.88428 12.498 8.99987 12.0007 9.0001ZM12.0007 21.4287C11.518 21.4291 11.0382 21.3554 10.5779 21.2101C10.997 20.9034 11.3712 20.5396 11.6896 20.1292C11.7924 20.1384 11.8961 20.143 12.0007 20.143C12.91 20.143 13.7821 19.7817 14.4251 19.1388C15.0681 18.4958 15.4293 17.6237 15.4293 16.7144V12.2144C15.4293 12.0439 15.3616 11.8804 15.241 11.7598C15.1204 11.6393 14.9569 11.5715 14.7864 11.5715H12.7833C12.6688 11.0879 12.4267 10.6439 12.0821 10.2858H14.7864C15.2979 10.2858 15.7885 10.489 16.1501 10.8507C16.5118 11.2124 16.715 11.7029 16.715 12.2144V16.7144C16.715 17.3335 16.5931 17.9465 16.3562 18.5185C16.1192 19.0904 15.772 19.6101 15.3342 20.0479C14.8965 20.4857 14.3768 20.8329 13.8048 21.0698C13.2328 21.3067 12.6198 21.4287 12.0007 21.4287ZM15.0196 8.51668C15.5063 8.81886 16.065 8.98544 16.6378 8.99918C17.2106 9.01292 17.7766 8.87331 18.2773 8.59481C18.778 8.3163 19.1952 7.90903 19.4856 7.41516C19.776 6.92128 19.9292 6.35876 19.9292 5.78582C19.9292 5.21288 19.776 4.65035 19.4856 4.15648C19.1952 3.6626 18.778 3.25533 18.2773 2.97683C17.7766 2.69833 17.2106 2.55872 16.6378 2.57246C16.065 2.58619 15.5063 2.75278 15.0196 3.05496C15.3076 3.3721 15.5459 3.73553 15.7224 4.13239C16.0151 3.95668 16.3491 3.86179 16.6904 3.85741C17.0317 3.85302 17.3681 3.9393 17.6651 4.10743C17.9622 4.27556 18.2093 4.51952 18.3812 4.81439C18.5532 5.10926 18.6438 5.44448 18.6438 5.78582C18.6438 6.12716 18.5532 6.46238 18.3812 6.75725C18.2093 7.05212 17.9622 7.29608 17.6651 7.46421C17.3681 7.63234 17.0317 7.71862 16.6904 7.71423C16.3491 7.70985 16.0151 7.61495 15.7224 7.43925C15.5461 7.83302 15.3089 8.19661 15.0196 8.51668ZM16.403 20.1292C16.5059 20.1378 16.6101 20.1424 16.7159 20.143C17.6252 20.143 18.4972 19.7817 19.1402 19.1388C19.7832 18.4958 20.1444 17.6237 20.1444 16.7144V12.2144C20.1444 12.0439 20.0767 11.8804 19.9561 11.7598C19.8356 11.6393 19.6721 11.5715 19.5016 11.5715H17.4976C17.3831 11.0879 17.141 10.6439 16.7964 10.2858H19.5016C20.0131 10.2858 20.5036 10.489 20.8653 10.8507C21.227 11.2124 21.4301 11.7029 21.4301 12.2144V16.7144C21.4304 17.4555 21.2559 18.1863 20.9208 18.8474C20.5857 19.5085 20.0994 20.0812 19.5015 20.5192C18.9036 20.9571 18.2108 21.2479 17.4794 21.368C16.7481 21.488 15.9987 21.4339 15.2921 21.2101C15.711 20.9033 16.0849 20.5395 16.403 20.1292Z"
                                        fill="#191919" />
                                </svg>8 Max</div>
                        </div>
                    </div>

                    <div class="vt-card" data-target="vt-kautilya" role="tab" tabindex="0" aria-selected="false">
                        <div class="vt-thumb">
                            <!-- <img
                                src="<?php bloginfo('url') ?>/wp-content/uploads/2025/12/compressed_IMG_0533.webp"
                                alt=""> -->
                            <?php
                            echo wp_get_attachment_image(33408, 'medium', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'sizes' => '(max-width: 650px) 100vw, 300px'
                            ]);
                            ?>
                            <span class="play"><svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                                        fill="#1763B9" />
                                </svg>
                            </span>
                        </div>
                        <div class="vt-meta">
                            <h4>Kautilya</h4>
                            <p>Discover our premium tribunal hearing room</p>
                            <div class="pax"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.28557 3.85725C6.77409 3.85725 6.28355 4.06044 5.92187 4.42211C5.56019 4.78379 5.357 5.27433 5.357 5.78582C5.357 6.29731 5.56019 6.78785 5.92187 7.14952C6.28355 7.5112 6.77409 7.71439 7.28557 7.71439C7.79706 7.71439 8.2876 7.5112 8.64928 7.14952C9.01096 6.78785 9.21415 6.29731 9.21415 5.78582C9.21415 5.27433 9.01096 4.78379 8.64928 4.42211C8.2876 4.06044 7.79706 3.85725 7.28557 3.85725ZM4.07129 5.78582C4.07129 4.93334 4.40994 4.11577 5.01273 3.51298C5.61553 2.91018 6.43309 2.57153 7.28557 2.57153C8.13806 2.57153 8.95562 2.91018 9.55842 3.51298C10.1612 4.11577 10.4999 4.93334 10.4999 5.78582C10.4999 6.6383 10.1612 7.45587 9.55842 8.05866C8.95562 8.66146 8.13806 9.0001 7.28557 9.0001C6.43309 9.0001 5.61553 8.66146 5.01273 8.05866C4.40994 7.45587 4.07129 6.6383 4.07129 5.78582ZM2.57129 12.2144C2.57129 11.7029 2.77448 11.2124 3.13615 10.8507C3.49783 10.489 3.98837 10.2858 4.49986 10.2858H10.0713C10.5828 10.2858 11.0733 10.489 11.435 10.8507C11.7967 11.2124 11.9999 11.7029 11.9999 12.2144V16.7144C11.9999 17.9647 11.5032 19.1638 10.6191 20.0479C9.73498 20.932 8.53588 21.4287 7.28557 21.4287C6.03527 21.4287 4.83617 20.932 3.95207 20.0479C3.06797 19.1638 2.57129 17.9647 2.57129 16.7144V12.2144ZM4.49986 11.5715C4.32936 11.5715 4.16585 11.6393 4.04529 11.7598C3.92473 11.8804 3.857 12.0439 3.857 12.2144V16.7144C3.857 17.6237 4.21823 18.4958 4.86121 19.1388C5.50419 19.7817 6.37626 20.143 7.28557 20.143C8.19489 20.143 9.06696 19.7817 9.70994 19.1388C10.3529 18.4958 10.7141 17.6237 10.7141 16.7144V12.2144C10.7141 12.0439 10.6464 11.8804 10.5259 11.7598C10.4053 11.6393 10.2418 11.5715 10.0713 11.5715H4.49986ZM12.0007 9.0001C11.4015 9.0013 10.814 8.83409 10.3053 8.51753C10.5933 8.20039 10.8316 7.8361 11.0081 7.43925C11.3008 7.61479 11.6348 7.70954 11.976 7.71381C12.3173 7.71808 12.6535 7.63173 12.9505 7.46358C13.2475 7.29542 13.4945 7.05147 13.6663 6.75664C13.8382 6.46181 13.9288 6.12666 13.9288 5.78539C13.9288 5.44412 13.8382 5.10897 13.6663 4.81414C13.4945 4.51931 13.2475 4.27537 12.9505 4.10721C12.6535 3.93905 12.3173 3.8527 11.976 3.85697C11.6348 3.86124 11.3008 3.95599 11.0081 4.13153C10.8318 3.73776 10.5946 3.37417 10.3053 3.0541C10.7279 2.79206 11.2055 2.63163 11.7006 2.58544C12.1957 2.53924 12.6947 2.60854 13.1585 2.78787C13.6223 2.96721 14.0381 3.2517 14.3733 3.61896C14.7085 3.98623 14.9539 4.42626 15.0903 4.90444C15.2266 5.38261 15.2502 5.88591 15.1591 6.37473C15.068 6.86355 14.8647 7.32457 14.5653 7.72153C14.2658 8.11849 13.8784 8.44056 13.4334 8.66242C12.9884 8.88428 12.498 8.99987 12.0007 9.0001ZM12.0007 21.4287C11.518 21.4291 11.0382 21.3554 10.5779 21.2101C10.997 20.9034 11.3712 20.5396 11.6896 20.1292C11.7924 20.1384 11.8961 20.143 12.0007 20.143C12.91 20.143 13.7821 19.7817 14.4251 19.1388C15.0681 18.4958 15.4293 17.6237 15.4293 16.7144V12.2144C15.4293 12.0439 15.3616 11.8804 15.241 11.7598C15.1204 11.6393 14.9569 11.5715 14.7864 11.5715H12.7833C12.6688 11.0879 12.4267 10.6439 12.0821 10.2858H14.7864C15.2979 10.2858 15.7885 10.489 16.1501 10.8507C16.5118 11.2124 16.715 11.7029 16.715 12.2144V16.7144C16.715 17.3335 16.5931 17.9465 16.3562 18.5185C16.1192 19.0904 15.772 19.6101 15.3342 20.0479C14.8965 20.4857 14.3768 20.8329 13.8048 21.0698C13.2328 21.3067 12.6198 21.4287 12.0007 21.4287ZM15.0196 8.51668C15.5063 8.81886 16.065 8.98544 16.6378 8.99918C17.2106 9.01292 17.7766 8.87331 18.2773 8.59481C18.778 8.3163 19.1952 7.90903 19.4856 7.41516C19.776 6.92128 19.9292 6.35876 19.9292 5.78582C19.9292 5.21288 19.776 4.65035 19.4856 4.15648C19.1952 3.6626 18.778 3.25533 18.2773 2.97683C17.7766 2.69833 17.2106 2.55872 16.6378 2.57246C16.065 2.58619 15.5063 2.75278 15.0196 3.05496C15.3076 3.3721 15.5459 3.73553 15.7224 4.13239C16.0151 3.95668 16.3491 3.86179 16.6904 3.85741C17.0317 3.85302 17.3681 3.9393 17.6651 4.10743C17.9622 4.27556 18.2093 4.51952 18.3812 4.81439C18.5532 5.10926 18.6438 5.44448 18.6438 5.78582C18.6438 6.12716 18.5532 6.46238 18.3812 6.75725C18.2093 7.05212 17.9622 7.29608 17.6651 7.46421C17.3681 7.63234 17.0317 7.71862 16.6904 7.71423C16.3491 7.70985 16.0151 7.61495 15.7224 7.43925C15.5461 7.83302 15.3089 8.19661 15.0196 8.51668ZM16.403 20.1292C16.5059 20.1378 16.6101 20.1424 16.7159 20.143C17.6252 20.143 18.4972 19.7817 19.1402 19.1388C19.7832 18.4958 20.1444 17.6237 20.1444 16.7144V12.2144C20.1444 12.0439 20.0767 11.8804 19.9561 11.7598C19.8356 11.6393 19.6721 11.5715 19.5016 11.5715H17.4976C17.3831 11.0879 17.141 10.6439 16.7964 10.2858H19.5016C20.0131 10.2858 20.5036 10.489 20.8653 10.8507C21.227 11.2124 21.4301 11.7029 21.4301 12.2144V16.7144C21.4304 17.4555 21.2559 18.1863 20.9208 18.8474C20.5857 19.5085 20.0994 20.0812 19.5015 20.5192C18.9036 20.9571 18.2108 21.2479 17.4794 21.368C16.7481 21.488 15.9987 21.4339 15.2921 21.2101C15.711 20.9033 16.0849 20.5395 16.403 20.1292Z"
                                        fill="#191919" />
                                </svg>15 Max</div>
                        </div>
                    </div>

                    <div class="vt-card" data-target="vt-lounge" role="tab" tabindex="0" aria-selected="false">
                        <div class="vt-thumb">
                            <!-- <img
                                src="<?php bloginfo('url') ?>/wp-content/uploads/2025/12/compressed_SUR_6921-1.jpg"
                                alt=""> -->
                            <?php
                            echo wp_get_attachment_image(36988, 'medium', false, [
                                'alt' => 'Overview',
                                'loading' => 'lazy',
                                'sizes' => '(max-width: 650px) 100vw, 300px'
                            ]);

                            ?>
                            <span class="play"><svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                                        fill="#1763B9" />
                                </svg>
                            </span>
                        </div>
                        <div class="vt-meta">
                            <h4>Breakout Lounge</h4>
                            <p>Our relaxing lounge for informal meets between sessions</p>
                            <div class="pax"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.28557 3.85725C6.77409 3.85725 6.28355 4.06044 5.92187 4.42211C5.56019 4.78379 5.357 5.27433 5.357 5.78582C5.357 6.29731 5.56019 6.78785 5.92187 7.14952C6.28355 7.5112 6.77409 7.71439 7.28557 7.71439C7.79706 7.71439 8.2876 7.5112 8.64928 7.14952C9.01096 6.78785 9.21415 6.29731 9.21415 5.78582C9.21415 5.27433 9.01096 4.78379 8.64928 4.42211C8.2876 4.06044 7.79706 3.85725 7.28557 3.85725ZM4.07129 5.78582C4.07129 4.93334 4.40994 4.11577 5.01273 3.51298C5.61553 2.91018 6.43309 2.57153 7.28557 2.57153C8.13806 2.57153 8.95562 2.91018 9.55842 3.51298C10.1612 4.11577 10.4999 4.93334 10.4999 5.78582C10.4999 6.6383 10.1612 7.45587 9.55842 8.05866C8.95562 8.66146 8.13806 9.0001 7.28557 9.0001C6.43309 9.0001 5.61553 8.66146 5.01273 8.05866C4.40994 7.45587 4.07129 6.6383 4.07129 5.78582ZM2.57129 12.2144C2.57129 11.7029 2.77448 11.2124 3.13615 10.8507C3.49783 10.489 3.98837 10.2858 4.49986 10.2858H10.0713C10.5828 10.2858 11.0733 10.489 11.435 10.8507C11.7967 11.2124 11.9999 11.7029 11.9999 12.2144V16.7144C11.9999 17.9647 11.5032 19.1638 10.6191 20.0479C9.73498 20.932 8.53588 21.4287 7.28557 21.4287C6.03527 21.4287 4.83617 20.932 3.95207 20.0479C3.06797 19.1638 2.57129 17.9647 2.57129 16.7144V12.2144ZM4.49986 11.5715C4.32936 11.5715 4.16585 11.6393 4.04529 11.7598C3.92473 11.8804 3.857 12.0439 3.857 12.2144V16.7144C3.857 17.6237 4.21823 18.4958 4.86121 19.1388C5.50419 19.7817 6.37626 20.143 7.28557 20.143C8.19489 20.143 9.06696 19.7817 9.70994 19.1388C10.3529 18.4958 10.7141 17.6237 10.7141 16.7144V12.2144C10.7141 12.0439 10.6464 11.8804 10.5259 11.7598C10.4053 11.6393 10.2418 11.5715 10.0713 11.5715H4.49986ZM12.0007 9.0001C11.4015 9.0013 10.814 8.83409 10.3053 8.51753C10.5933 8.20039 10.8316 7.8361 11.0081 7.43925C11.3008 7.61479 11.6348 7.70954 11.976 7.71381C12.3173 7.71808 12.6535 7.63173 12.9505 7.46358C13.2475 7.29542 13.4945 7.05147 13.6663 6.75664C13.8382 6.46181 13.9288 6.12666 13.9288 5.78539C13.9288 5.44412 13.8382 5.10897 13.6663 4.81414C13.4945 4.51931 13.2475 4.27537 12.9505 4.10721C12.6535 3.93905 12.3173 3.8527 11.976 3.85697C11.6348 3.86124 11.3008 3.95599 11.0081 4.13153C10.8318 3.73776 10.5946 3.37417 10.3053 3.0541C10.7279 2.79206 11.2055 2.63163 11.7006 2.58544C12.1957 2.53924 12.6947 2.60854 13.1585 2.78787C13.6223 2.96721 14.0381 3.2517 14.3733 3.61896C14.7085 3.98623 14.9539 4.42626 15.0903 4.90444C15.2266 5.38261 15.2502 5.88591 15.1591 6.37473C15.068 6.86355 14.8647 7.32457 14.5653 7.72153C14.2658 8.11849 13.8784 8.44056 13.4334 8.66242C12.9884 8.88428 12.498 8.99987 12.0007 9.0001ZM12.0007 21.4287C11.518 21.4291 11.0382 21.3554 10.5779 21.2101C10.997 20.9034 11.3712 20.5396 11.6896 20.1292C11.7924 20.1384 11.8961 20.143 12.0007 20.143C12.91 20.143 13.7821 19.7817 14.4251 19.1388C15.0681 18.4958 15.4293 17.6237 15.4293 16.7144V12.2144C15.4293 12.0439 15.3616 11.8804 15.241 11.7598C15.1204 11.6393 14.9569 11.5715 14.7864 11.5715H12.7833C12.6688 11.0879 12.4267 10.6439 12.0821 10.2858H14.7864C15.2979 10.2858 15.7885 10.489 16.1501 10.8507C16.5118 11.2124 16.715 11.7029 16.715 12.2144V16.7144C16.715 17.3335 16.5931 17.9465 16.3562 18.5185C16.1192 19.0904 15.772 19.6101 15.3342 20.0479C14.8965 20.4857 14.3768 20.8329 13.8048 21.0698C13.2328 21.3067 12.6198 21.4287 12.0007 21.4287ZM15.0196 8.51668C15.5063 8.81886 16.065 8.98544 16.6378 8.99918C17.2106 9.01292 17.7766 8.87331 18.2773 8.59481C18.778 8.3163 19.1952 7.90903 19.4856 7.41516C19.776 6.92128 19.9292 6.35876 19.9292 5.78582C19.9292 5.21288 19.776 4.65035 19.4856 4.15648C19.1952 3.6626 18.778 3.25533 18.2773 2.97683C17.7766 2.69833 17.2106 2.55872 16.6378 2.57246C16.065 2.58619 15.5063 2.75278 15.0196 3.05496C15.3076 3.3721 15.5459 3.73553 15.7224 4.13239C16.0151 3.95668 16.3491 3.86179 16.6904 3.85741C17.0317 3.85302 17.3681 3.9393 17.6651 4.10743C17.9622 4.27556 18.2093 4.51952 18.3812 4.81439C18.5532 5.10926 18.6438 5.44448 18.6438 5.78582C18.6438 6.12716 18.5532 6.46238 18.3812 6.75725C18.2093 7.05212 17.9622 7.29608 17.6651 7.46421C17.3681 7.63234 17.0317 7.71862 16.6904 7.71423C16.3491 7.70985 16.0151 7.61495 15.7224 7.43925C15.5461 7.83302 15.3089 8.19661 15.0196 8.51668ZM16.403 20.1292C16.5059 20.1378 16.6101 20.1424 16.7159 20.143C17.6252 20.143 18.4972 19.7817 19.1402 19.1388C19.7832 18.4958 20.1444 17.6237 20.1444 16.7144V12.2144C20.1444 12.0439 20.0767 11.8804 19.9561 11.7598C19.8356 11.6393 19.6721 11.5715 19.5016 11.5715H17.4976C17.3831 11.0879 17.141 10.6439 16.7964 10.2858H19.5016C20.0131 10.2858 20.5036 10.489 20.8653 10.8507C21.227 11.2124 21.4301 11.7029 21.4301 12.2144V16.7144C21.4304 17.4555 21.2559 18.1863 20.9208 18.8474C20.5857 19.5085 20.0994 20.0812 19.5015 20.5192C18.9036 20.9571 18.2108 21.2479 17.4794 21.368C16.7481 21.488 15.9987 21.4339 15.2921 21.2101C15.711 20.9033 16.0849 20.5395 16.403 20.1292Z"
                                        fill="#191919" />
                                </svg>
                                20 Max</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <script>
            (function () {
                const cards = Array.from(document.querySelectorAll('.vt-card'));
                const panels = Array.from(document.querySelectorAll('.vt-panel'));

                // init: only first panel active; ensure image visible-2, shortcode hidden
                panels.forEach((p, i) => {
                    const img = p.querySelector('[data-img]');
                    const sc = p.querySelector('[data-shortcode]');
                    img && img.classList.remove('hidden');
                    sc && sc.classList.remove('active');
                    if (i === 0) {
                        p.classList.add('active');
                        p.setAttribute('aria-hidden', 'false');
                    } else {
                        p.classList.remove('active');
                        p.setAttribute('aria-hidden', 'true');
                    }
                });

                // activate panel
                function activatePanel(targetId) {
                    const current = document.querySelector('.vt-panel.active');
                    if (current && current.id === targetId) return;

                    // reset panels: show image & hide shortcode
                    panels.forEach(p => {
                        const img = p.querySelector('[data-img]');
                        const sc = p.querySelector('[data-shortcode]');
                        img && img.classList.remove('hidden');
                        sc && sc.classList.remove('active');
                        p.classList.remove('active');
                        p.setAttribute('aria-hidden', 'true');
                    });

                    // mark cards active/inactive
                    cards.forEach(c => c.classList.remove('active'));
                    const clickedCard = document.querySelector(`.vt-card[data-target="${targetId}"]`);
                    clickedCard && clickedCard.classList.add('active');

                    // show new panel (small delay helps transition)
                    const newPanel = document.getElementById(targetId);
                    if (newPanel) {
                        setTimeout(() => {
                            newPanel.classList.add('active');
                            newPanel.setAttribute('aria-hidden', 'false');
                        }, 30);
                    }
                }

                // card clicks
                cards.forEach(card => {
                    card.addEventListener('click', () => {
                        const target = card.dataset.target;
                        activatePanel(target);
                    });
                    card.addEventListener('keydown', e => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            card.click();
                        }
                    });
                });

                // Take tour open
                document.querySelectorAll('[data-action="open-tour"]').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const panel = e.currentTarget.closest('.vt-panel');
                        if (!panel) return;
                        const img = panel.querySelector('[data-img]');
                        const sc = panel.querySelector('[data-shortcode]');
                        // hide image container
                        img && img.classList.add('hidden');
                        sc && sc.classList.add('active');
                        // show shortcode after small delay for smoothness
                        // setTimeout(() => {
                        //     sc && sc.classList.add('active');
                        // }, 380);

                        // optionally: lazy-load iframe only when opening (optional)
                        const iframe = sc && sc.querySelector('iframe[data-src]');
                        if (iframe && !iframe.src) {
                            iframe.src = iframe.dataset.src;
                        }
                    });
                });

                // Close tour (Back to image) buttons inside shortcode boxes
                document.addEventListener('click', function (e) {
                    if (e.target.matches('.vt-close-tour')) {
                        const panel = e.target.closest('.vt-panel');
                        if (!panel) return;
                        const img = panel.querySelector('[data-img]');
                        const sc = panel.querySelector('[data-shortcode]');
                        sc && sc.classList.remove('active');
                        // restore image after small delay
                        setTimeout(() => img && img.classList.remove('hidden'), 220);
                    }
                });

                // When switching tabs via card, reset is handled by activatePanel
            })();
        </script>
    </section>
    <?php
    return ob_get_clean();
}


// Room Tour Dynamic VT
add_shortcode('room_tour', 'room_tour_func');

function room_tour_func()
{
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID() // default: current post
    ), $atts);

    $o = '';
    $shortcode = '[ipano id="1"]';

    $selected_post_id = get_field('select_tour', $atts['post_id']);

    if (!empty($selected_post_id)) {
        $shortcode = '[wpvr id="' . $selected_post_id[0] . '"]';
    }
    global $product;

    $img_url = '';

    // 1️⃣ Get gallery images
    $gallery_ids = $product->get_gallery_image_ids();

    if (!empty($gallery_ids)) {
        // Get LAST gallery image
        $last_image_id = end($gallery_ids);
        $img_url = wp_get_attachment_url($last_image_id);
    }

    // 2️⃣ Fallback to featured image
    if (empty($img_url)) {
        $featured_id = $product->get_image_id();
        $img_url = wp_get_attachment_url($featured_id);
    }


    $o .= '<div id="vt_room_details" class="vt-media">
        <div class="image-container" data-img>
            <img src="' . $img_url . '"
                alt="Brihaspati" />
            <button class="take-tour" data-action="open-tour" aria-label="View 360°">
                <span class="circle" aria-hidden="true">
                    <svg width="44" height="44" viewBox="0 0 44 44" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M29.5859 19.3763L17.6091 13.3878C15.4149 12.2907 12.8333 13.8863 12.8333 16.3394V27.6604C12.8333 30.1136 15.4149 31.7091 17.6091 30.612L29.5859 24.6236C31.748 23.5426 31.748 20.4573 29.5859 19.3763Z"
                            fill="#1763B9" />
                    </svg>

                </span>
                <span class="label">View 360°</span>
            </button>
        </div>

        <div class="shortcode-box" data-shortcode>
            <div class="shortcode-inner">
                ' . $shortcode . '
            </div>
        </div>
    </div>';
    return $o;
}

// Function to use woocommerce email template 
function send_woocommerce_custom_email($to, $subject, $email_heading = '', $message_content = '', $attachments = array())
{

    // Load WooCommerce email header template
    $header = wc_get_template_html(
        'emails/email-header.php',
        array('email_heading' => $email_heading)
    );
    $mailer = WC()->mailer();
    $from_name = $mailer->get_from_name();
    $from_email = $mailer->get_from_address();

    // Replace {site_title} placeholder
    $header = str_replace('{site_title}', get_bloginfo('name'), $header);

    // Load WooCommerce email footer template
    $footer = wc_get_template_html('emails/email-footer.php');

    // Load WooCommerce email styles
    ob_start();
    wc_get_template('emails/email-styles.php');
    $styles = ob_get_clean();

    // Combine everything
    $message = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
    $message .= '<style>' . $styles . '</style>';
    $message .= '</head><body>';
    $message .= $header;
    $message .= '<div style="padding: 20px 20px 5px 20px;">' . $message_content . '</div>';
    $message .= $footer;
    $message .= '</body></html>';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . sprintf('%s <%s>', $from_name, $from_email),
    );

    // Send email
    wp_mail(
        $to,
        $subject,
        $message,
        $headers,
        $attachments
    );
}
function send_woocommerce_custom_email_to_admin($to, $subject, $email_heading = '', $message_content = '', $attachments = array())
{

    // Load WooCommerce email header template
    $header = wc_get_template_html(
        'emails/email-header.php',
        array('email_heading' => $email_heading)
    );
    $mailer = WC()->mailer();
    $from_name = $mailer->get_from_name();
    $from_email = $mailer->get_from_address();

    // Replace {site_title} placeholder
    $header = str_replace('{site_title}', get_bloginfo('name'), $header);

    // Load WooCommerce email footer template
    $footer = wc_get_template_html('emails/email-footer-admin.php');

    // Load WooCommerce email styles
    ob_start();
    wc_get_template('emails/email-styles.php');
    $styles = ob_get_clean();

    // Combine everything
    $message = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
    $message .= '<style>' . $styles . '</style>';
    $message .= '</head><body>';
    $message .= $header;
    $message .= '<div style="padding: 20px;">' . $message_content . '</div>';
    $message .= $footer;
    $message .= '</body></html>';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . sprintf('%s <%s>', $from_name, $from_email),
    );

    // Send email
    wp_mail(
        $to,
        $subject,
        $message,
        $headers,
        $attachments
    );
}

add_action('wp_head', function () {
    if (function_exists('WC') && WC()->cart->is_empty()) {
        echo '<style>
            .checkout-link {
                display: none !important;
            }
        </style>';
    }
});
add_action('template_redirect', 'redirect_login_to_account_if_logged_in');
function redirect_login_to_account_if_logged_in()
{

    // Change the slug if your login page is different
    if ((is_page('login') || is_page('register')) && is_user_logged_in()) {

        if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
            wp_redirect(esc_url_raw($_GET['redirect_to']));
            exit;
        }

        // WooCommerce My Account page
        $my_account_url = wc_get_page_permalink('myaccount');

        wp_redirect($my_account_url);
        exit;
    }
}


/**
 * AJAX handler to delete all expired saved carts for the current user.
 */
function phive_delete_all_expired_carts_ajax_handler()
{
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'User not logged in.'));
    }

    $user_id = get_current_user_id();
    $saved_carts = get_user_meta($user_id, '_saved_carts', true);

    if (empty($saved_carts) || !is_array($saved_carts)) {
        wp_send_json_success(array('message' => 'No saved carts found to process.', 'deleted_count' => 0));
    }

    $updated_carts = $saved_carts;
    $deleted_count = 0;
    $current_timestamp = current_time('timestamp');

    foreach ($saved_carts as $cart_name => $cart_data) {

        $cart_items = isset($cart_data['items']) ? $cart_data['items'] : $cart_data;
        $main_product = null;

        // Find the main booking product
        foreach ($cart_items as $cart_item) {
            $product = wc_get_product($cart_item['product_id']);
            if ($product && $product->get_type() === 'phive_booking') {
                $main_product = $cart_item;
                break;
            }
        }

        if ($main_product) {
            $booked_from = $main_product['phive_display_time_from'] ?? '';

            if (!empty($booked_from)) {
                $booking_timestamp = strtotime($booked_from);

                // Check for expiration
                if ($booking_timestamp < $current_timestamp) {
                    // This cart is expired, remove it from the updated array
                    unset($updated_carts[$cart_name]);
                    $deleted_count++;
                }
            }
        }
    }

    // Update the user meta only if carts were deleted
    if ($deleted_count > 0) {
        update_user_meta($user_id, '_saved_carts', $updated_carts);
        wp_send_json_success(array(
            'message' => $deleted_count . ' expired carts have been deleted.',
            'deleted_count' => $deleted_count
        ));
    } else {
        wp_send_json_success(array(
            'message' => 'No expired carts were found to delete.',
            'deleted_count' => 0
        ));
    }
}

// Hook the function for both logged-in and non-logged-in users (though logged-in check is inside)
add_action('wp_ajax_phive_delete_all_expired_carts', 'phive_delete_all_expired_carts_ajax_handler');

// Schedule daily cron event on theme/plugin activation
function phive_schedule_daily_cleanup()
{
    if (!wp_next_scheduled('phive_daily_expired_cart_cleanup')) {
        $midnight = strtotime('tomorrow midnight');
        wp_schedule_event($midnight, 'daily', 'phive_daily_expired_cart_cleanup');
    }
}
add_action('init', 'phive_schedule_daily_cleanup');

add_action('phive_daily_expired_cart_cleanup', 'phive_cleanup_all_users_saved_carts');

function phive_cleanup_all_users_saved_carts()
{

    // Get all users who MAY have saved carts
    $users = get_users(array(
        'meta_key' => '_saved_carts',
        'meta_compare' => 'EXISTS',
        'fields' => array('ID')
    ));

    if (empty($users)) {
        return;
    }

    foreach ($users as $user) {
        phive_delete_expired_saved_carts($user->ID); // clean each user
    }
}

function phive_delete_expired_saved_carts($user_id)
{

    $saved_carts = get_user_meta($user_id, '_saved_carts', true);

    if (empty($saved_carts) || !is_array($saved_carts)) {
        return 0;
    }

    $updated_carts = [];
    $deleted_count = 0;
    $current_time = current_time('timestamp');
    $days_15 = 15 * DAY_IN_SECONDS;

    foreach ($saved_carts as $cart_name => $cart_data) {

        $cart_items = isset($cart_data['items']) ? $cart_data['items'] : $cart_data;
        $booking_timestamp = 0;

        foreach ($cart_items as $item) {
            if (isset($item['phive_display_time_from'])) {
                $booking_timestamp = strtotime($item['phive_display_time_from']);
                break;
            }
        }

        if (!$booking_timestamp) {
            $updated_carts[$cart_name] = $cart_data;
            continue;
        }

        if (($current_time - $booking_timestamp) > $days_15) {
            $deleted_count++;
        } else {
            $updated_carts[$cart_name] = $cart_data;
        }
    }

    update_user_meta($user_id, '_saved_carts', $updated_carts);

    return $deleted_count;
}

add_filter('wp_nav_menu_items', function ($items, $args) {

    // Replace placeholder URL
    $logout_url = wp_logout_url(home_url());

    // If user is logged in → show logout
    if (is_user_logged_in()) {
        $items = str_replace('#logout', $logout_url, $items);
    } else {
        // Hide item if user not logged in
        $items = preg_replace('/<li.*?#logout.*?<\/li>/', '', $items);
    }

    return $items;
}, 10, 2);

add_action('template_redirect', function () {

    // Only do this for logged-in users
    if (!is_user_logged_in()) {
        return;
    }

    // Check if redirect_to exists
    if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {

        // Sanitize URL
        $redirect_url = esc_url_raw($_GET['redirect_to']);

        // Prevent endless loops
        if ($redirect_url !== home_url(add_query_arg([], $_SERVER['REQUEST_URI']))) {
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
});


function custom_checkout_legal_links_replacement()
{

    // --- 1. Define your actual URLs here ---
    $terms_of_use_url = esc_url('/terms-and-conditions/');
    $privacy_policy_url = esc_url(get_privacy_policy_url()); // Uses WooCommerce/WP setting for Privacy Policy
    $legal_terms_url = esc_url('/legal-terms/');
    if (empty($privacy_policy_url)) {
        $privacy_policy_url = '#';
    }
    $custom_text = sprintf(
        __('By completing this purchase, you agree to our <a href="%1$s" target="_blank">terms & conditions</a>, <a href="%2$s" target="_blank">privacy policy</a>, and all applicable <a href="%3$s" target="_blank">Legal Terms</a>.', 'your-text-domain'),
        $terms_of_use_url, // %1$s
        $privacy_policy_url, // %2$s
        $legal_terms_url // %3$s
    );
    echo '';
    // echo '<div class="woocommerce-privacy-policy-text book-by">';
    // echo 'This booking is initiated by you, therefore, any further cancelation or changes can be managed by only you.';
    // echo '</div>';
    echo '<div class="woocommerce-privacy-policy-text">';
    echo wp_kses_post(wpautop($custom_text));
    echo '</div>';
}

// Remove the default WooCommerce terms output
remove_action('woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20);
remove_action('woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30);
add_action('woocommerce_checkout_terms_and_conditions', 'custom_checkout_legal_links_replacement', 10);

// Remove Pay Now button when payment succesfull
add_action('wp_footer', function () { ?>
    <script>
        jQuery(function ($) {
            if (window.location.href.includes("/checkout/order-pay/")) {

                const interval = setInterval(() => {
                    let msg = $(".woocommerce-info:contains('Please wait while we are processing your payment.')");

                    if (msg.length && msg.is(":visible")) {

                        // Add custom class to body
                        $("body").addClass("payment_success");

                        clearInterval(interval);
                    }
                }, 300);

            }
        });
    </script>
<?php });

//add_filter('woocommerce_add_error', 'replace_order_pay_error_only_for_wrong_user', 10, 1);
function replace_order_pay_error_only_for_wrong_user($message)
{

    if (!is_wc_endpoint_url('order-pay') || !is_user_logged_in()) {
        return $message;
    }

    global $wp;

    if (empty($wp->query_vars['order-pay'])) {
        return $message;
    }

    $order_id = absint($wp->query_vars['order-pay']);
    $order = wc_get_order($order_id);

    if (!$order) {
        return $message;
    }

    // ✅ Only when logged-in user does NOT own the order
    if (
        (int) $order->get_user_id() > 0 &&
        (int) get_current_user_id() !== (int) $order->get_user_id() &&
        strpos($message, 'This order cannot be paid for.') !== false
    ) {
        return __(
            'Please login with the email Id / Mobile number added under split payment to complete the payment.',
            'woocommerce'
        );
    }

    return $message;
}
add_action('woocommerce_checkout_update_order_meta', 'sync_billing_phone_to_user_meta', 10, 1);
function sync_billing_phone_to_user_meta($order_id)
{

    // Only for logged-in users
    if (!is_user_logged_in()) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $user_id = $order->get_user_id();
    if (!$user_id) {
        return;
    }

    // Get existing user phone
    $user_phone = get_user_meta($user_id, 'phone', true);

    // If already exists, do NOT overwrite
    if (!empty($user_phone)) {
        return;
    }

    // Get billing phone from order
    $billing_phone = $order->get_billing_phone();

    if (!empty($billing_phone)) {
        update_user_meta(
            $user_id,
            'phone',
            sanitize_text_field($billing_phone)
        );
    }
}


add_filter('wpseo_opengraph_image', function ($image) {
    // If WooCommerce product single page, return default product image instead
    if (function_exists('is_product') && is_product()) {
        return $image; // keep Yoast/WooCommerce product image
    }
    // Otherwise, apply global image sitewide
    return get_bloginfo('url') . '/wp-content/uploads/2025/12/Accorhub-Preview1.jpg';
});


/* ==========================================================================
   GROUP ORDER REFUND MANAGER (Manual + Policy Calculation)
   ========================================================================== */

/**
 * 1. Add "Group Refund Manager" Button to Admin Order Panel
 */
add_action('woocommerce_order_item_add_action_buttons', 'phive_add_manual_group_refund_button', 10, 1);

function phive_add_manual_group_refund_button($order)
{
    $status = $order->get_status();
    $initiated = $order->get_meta('init_mail');
    if ($status === 'pending' && empty($initiated)) {
        echo '<button type="button" class="button button-primary add_addons" style="text-align:center;float: left;">Select Add-ons</button>';
    } else {
        echo '<button type="button" class="button button-primary add_addons" style="text-align:center;float: left;" disabled>Select Add-ons</button>';
    }
    if ($status !== 'cancelled') {
        //echo '<button type="button" class="button button-primary send_manual_invoice_btn" style="text-align:center;float: right;">Send Invoice & Email</button>';
        //echo '<button type="button" class="button button-primary send_manual_invoice_btn no_pdf" style="text-align:center;float: right;margin-right:10px">Send Initiation Email</button>';
    }
    // Only for Group Parents
    if ($order->get_meta('_parent_order_id') || $order->get_meta('billed_for'))
        return;

    if ($order->get_meta('_parent_order_id') || $order->get_meta('billed_for'))

        // Hide if already fully refunded or cancelled (optional, but keeps UI clean)
        if (!$order->has_status(['cancelled'])) {
            return; // Uncomment if you want to hide button on cancelled orders
        }
    if ($order->get_meta('cancelled_unpaid') !== 'yes') {
        if ($order->get_date_paid()) {
            $paid = true;
        } else {
            $paid = false;
        }
        $order_total = $paid ? (float) $order->get_total() : 0.0;
        $total_refunded = (float) $order->get_total_refunded();
        $child_orders = $order->get_meta('group_additional_payers');
        if ($child_orders) {
            foreach ($child_orders as $child_order) {
                if (!$child_order['child_order_id']) {
                    continue;
                }
                $ord = wc_get_order($child_order['child_order_id']);
                if ($ord->get_date_paid()) {
                    $paid = true;
                } else {
                    $paid = false;
                }

                $order_total += $paid ? (float) $ord->get_total() : 0.0;
                $total_refunded += (float) $ord->get_total_refunded();
            }

        }
        $refund_percent = ($order_total > 0) ? ($total_refunded / $order_total) * 100 : 0;

        if ($refund_percent >= 50 && $refund_percent < 100 && $total_refunded > 0) {
            echo '<button type="button" class="button button-primary" id="phive_open_refund_modal" style="float: right;margin-right:10px">Refund Split Payments</button>';
            echo '<span style="float: right; margin-right: 10px; padding-top: 4px;"><strong>Note:</strong> Payment has been refunded.</span>';
        } elseif ($refund_percent > 0 && $refund_percent < 50 && $total_refunded > 0) {
            echo '<button type="button" class="button button-primary" id="phive_open_refund_modal" style="float: right;margin-right:10px">Refund Split Payments</button>';
            echo '<span style="float: right; margin-right: 10px; padding-top: 4px;"><strong>Note:</strong> Payment has been partially refunded.</span>';
        } elseif ($refund_percent == 100 && $total_refunded > 0) {
            echo '<button type="button" class="button button-primary" id="phive_open_refund_modal" style="float: right;margin-right:10px" disabled>Refund Split Payments</button>';
            echo '<span style="float: right; margin-right: 10px; padding-top: 4px;"><strong>Note:</strong> Payment has been fully refunded.</span>';
        }
    }

}


add_action('woocommerce_admin_order_items_after_line_items', 'phive_add_manual_group_details', 1, 1);

function phive_add_manual_group_details($order_id)
{
    // Convert order ID to WC_Order object
    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    // Only for Group Parents
    if ($order->get_meta('_parent_order_id') || $order->get_meta('billed_for')) {
        return;
    }

    // Optional: hide on cancelled / fully refunded orders
    if ($order->has_status(['cancelled']) || $order->get_remaining_refund_amount() <= 0) {
        // return;
    }

    // --- DYNAMIC CALCULATIONS ---
    $order->calculate_taxes();
    $total_amount = $order->get_subtotal();          // e.g., 12800
    $total_tax = $order->get_total_tax();      // e.g., 2800
    $refunded_amount = $order->get_total_refunded();
    $discount_total = $order->get_discount_total();
    $has_discount = $discount_total > 0 && !empty($order->get_coupon_codes());
    $room_charge = $total_amount;   // e.g., 10000
    $sum = $total_amount + $total_tax - $discount_total;
    $booking = get_booking_details($order);

    // echo '<pre>';
    //     print_r($booking);
    // echo '</pre>';

    $discount_applied_25_per = $booking['discount_applied_25_per'];

    if ($discount_applied_25_per) {
        $sum = $booking['total_after_dis'] + $total_tax;
    }

    $output = [];
    foreach ($order->get_meta_data() as $meta) {
        if (strpos($meta->key, 'Remarks for ') === 0 && !empty($meta->value)) {
            $category_name = str_replace('Remarks for ', '', $meta->key);
            $term = get_term_by('name', $category_name, 'product_cat');
            if ($term && order_has_category($order, $term->slug)) {
                $output[] = '<li><strong>' . esc_html($meta->key) . ':</strong> ' . esc_html($meta->value) . '</li>';
            }
        }
    }
    if (!empty($output)) {
        echo '<tr><td></td><td colspan="5"><div class="order_data_column" style=""><h3 style="margin-top:0;margin-bottom:10px;font-size: 16px;">Service Remarks</h3><ul>' . implode('', $output) . '</ul></div></td><td></td></tr>';
    }

    foreach ($order->get_items('coupon') as $coupon_item) {

        $coupon = new WC_Coupon($coupon_item->get_code());

        if ($coupon->get_discount_type() === 'percent') {
            $percentage = $coupon->get_amount(); // 👉 10
            $discount_type = $coupon->get_discount_type();
        }
    }
    ?>
    <style>
        .woocommerce_order_items_wrapper.wc-order-items-editable {
            display: flex;
            flex-direction: column;
            align-items: end;
        }

        .phive-group-wrapper {
            display: flex;
            justify-content: end;
            padding-right: 20px;
        }
    </style>



    <tr class="phive-group-details-row">
        <td colspan="99" style="padding: 0; border: none;">
            <div class="phive-group-wrapper">
                <table cellpadding="0" cellspacing="0" class="" style="width: 300px;" id="gp_table">
                    <tbody>
                        <!-- <tr>
                            <td class="label" style="text-align:right;">Booking Amount:</td>
                            <td width="1%"></td>
                            <td class="total" style="text-align:right;">
                                <strong><?php echo wc_price($booking['full_price']); ?></strong>
                            </td>
                        </tr> -->
                        <?php if ($booking['slots'] > 1 && $booking['bulk_discount_amount'] > 0 && !$discount_applied_25_per): ?>
                            <tr>
                                <td class="label" style="text-align:right;">Bulk Discount
                                    (<?php echo $booking['bulk_discount']; ?>):</td>
                                <td width="1%"></td>
                                <td class="total" style="text-align:right;">
                                    -<?php echo wc_price($booking['bulk_discount_amount']); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($has_discount && !$discount_applied_25_per): ?>
                            <tr>
                                <td class="label" style="text-align:right;">Coupon Discount
                                    (<?php echo $booking['disc_percentage']; ?>%):<br><small>Applicable on room fee</small>
                                </td>
                                <td width="1%"></td>
                                <td class="total" style="text-align:right;">

                                    -<?php echo wc_price($booking['discount']); ?>

                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($discount_applied_25_per): ?>
                            <tr>
                                <td class="label" style="text-align:right;"><?php echo $booking['discount_applied_25_text']; ?>
                                </td>
                                <td width="1%"></td>
                                <td class="total" style="text-align:right;">

                                    -<?php echo wc_price($booking['discount_applied_25_price']); ?>

                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($booking['addons_price'] > 0): ?>
                            <tr>
                                <td class="label" style="text-align:right;">Add-Ons Subtotal:
                                </td>
                                <td width="1%"></td>
                                <td class="total" style="text-align:right;">

                                    <?php echo wc_price($booking['addons_price']); ?>

                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <td class="label" style="text-align:right;font-weight:700">Booking Total:</td>
                            <td width="1%"></td>
                            <td class="total" style="text-align:right;font-weight:700">
                                <strong>
                                    <?php echo wc_price($booking['total_after_dis']); ?>
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <?php $half = $total_tax / 2; ?>
                            <td class="label" style="text-align:right;">CGST @9%:</td>
                            <td width="1%"></td>
                            <td class="total" style="text-align:right;">
                                +<?php echo wc_price($half, ['currency' => $order->get_currency()]); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label" style="text-align:right;">SGST @9%:</td>
                            <td width="1%"></td>
                            <td class="total" style="text-align:right;">
                                +<?php echo wc_price($half, ['currency' => $order->get_currency()]); ?>
                            </td>
                        </tr>
                        <tr style="font-size: 16px;font-weight:700">
                            <td class="label" style="text-align:right; border-bottom: 0px solid #eee;">Total Payable:
                            </td>
                            <td width="1%" style="border-bottom: 0px solid #eee;"></td>
                            <td class="total" style="text-align:right; border-bottom: 0px solid #eee;">
                                <strong><?php echo wc_price($sum, ['currency' => $order->get_currency()]); ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
    <?php
}

/**
 * 2. Add Modal HTML, CSS, and JS to Admin Footer
 */
add_action('admin_footer', 'phive_group_refund_modal_scripts');

function phive_group_refund_modal_scripts()
{
    // 1. Robust Screen Detection
    $screen = get_current_screen();
    $is_legacy_order = ($screen && $screen->id === 'shop_order');
    $is_hpos_order = ($screen && $screen->id === 'woocommerce_page_wc-orders' && isset($_GET['action']) && $_GET['action'] === 'edit');

    if (!$is_legacy_order && !$is_hpos_order)
        return;

    // 2. Get Order ID
    $current_order_id = 0;
    if (isset($_GET['id'])) {
        $current_order_id = intval($_GET['id']);
    } else {
        global $post;
        if (isset($post->ID))
            $current_order_id = $post->ID;
    }

    if (!$current_order_id)
        return;

    $c_order = wc_get_order($current_order_id);
    if ($c_order->get_meta('_parent_order_id') || $c_order->get_meta('billed_for')) {
        return;
    }
    $is_group = $c_order->get_meta('group_payment_mode') === 'group';

    ?>

    <style>
        #phive-refund-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100000;
        }

        #phive-refund-modal {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            width: 700px;
            max-width: 95%;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        #phive-refund-modal h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .phive-refund-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 13px;
        }

        .phive-refund-table th {
            text-align: left;
            background: #f9f9f9;
            padding: 10px;
            border-bottom: 2px solid #eee;
        }

        .phive-refund-table td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .phive-refund-table input {
            width: 90px;
            padding: 5px;
        }

        .phive-policy-alert {
            background: #e5f6fd;
            color: #000;
            padding: 15px;
            border-left: 4px solid #00a0d2;
            margin-bottom: 15px;
            font-size: 13px;
            line-height: 1.5;
        }

        .phive-modal-actions {
            text-align: right;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .phive-prev-refund {
            color: #a00;
            font-weight: 500;
        }

        body:has(#gp_table) button.button.button-primary.calculate-action,
        body:has(#gp_table) p.form-field.form-field-wide.wc-order-status,
        body:has(#gp_table) button.button.refund-items,
        body:has(#gp_table) button.button.add-line-item,
        body:has(#gp_table) button.button.add-coupon,
        .wc-order-data-row.wc-order-totals-items.wc-order-items-editable {
            display: none !important;
        }

        tr.phive-group-details-row td {
            padding: 5px !important;
        }

        body:has(input#original_order_status[value="refund-processed"]) button#phive_open_refund_modal {
            pointer-events: none;
            opacity: .6;
        }

        body:has(input#original_order_status[value="refund-processed"]) button#phive_open_refund_modal {
            font-size: 0;
        }

        body:has(input#original_order_status[value="refund-processed"]) button#phive_open_refund_modal:before {
            content: "Booking Amount Refunded";
            font-size: 12px;
        }
    </style>

    <div id="phive-refund-modal-overlay">
        <div id="phive-refund-modal">
            <h3><?php echo esc_js($is_group ? __('Process Group Refund', 'woocommerce') : __('Process Refund', 'woocommerce')); ?>
            </h3>
            <div id="phive-loading-msg" style="padding:20px; text-align:center;">
                <span class="spinner is-active" style="float:none; margin:0 10px 0 0;"></span> Loading details...
            </div>

            <div id="phive-modal-content" style="display:none;">
                <div class="phive-policy-alert" id="phive-policy-text"></div>

                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="phive-refund-table">
                        <thead>
                            <tr>
                                <th>Payer</th>
                                <th>Total Paid</th>
                                <th>Already Refunded</th>
                                <th>Refund Now</th>
                            </tr>
                        </thead>
                        <tbody id="phive-refund-rows">
                        </tbody>
                    </table>
                </div>

                <div class="phive-modal-actions">
                    <button type="button" class="button" id="phive-close-modal">Cancel</button>
                    <button type="button" class="button button-primary" id="phive-process-refund-btn">Confirm & Process
                        Refunds</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var orderId = <?php echo $current_order_id; ?>;

            // Open Modal
            $(document).on('click', '#phive_open_refund_modal', function (e) {
                e.preventDefault();
                $('#phive-refund-modal-overlay').fadeIn(200);
                $('#phive-loading-msg').show();
                $('#phive-modal-content').hide();

                $.post(ajaxurl, {
                    action: 'phive_get_group_refund_details',
                    order_id: orderId,
                    security: '<?php echo wp_create_nonce("phive_refund_view"); ?>'
                }, function (response) {
                    if (response.success) {
                        renderModal(response.data);
                    } else {
                        alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
                        $('#phive-refund-modal-overlay').fadeOut();
                    }
                });
            });

            // Close Modal
            $(document).on('click', '#phive-close-modal, #phive-refund-modal-overlay', function (e) {
                if (e.target === this || e.target.id === 'phive-close-modal') {
                    $('#phive-refund-modal-overlay').fadeOut(200);
                }
            });

            // Render Data
            function renderModal(data) {
                $('#phive-loading-msg').hide();
                $('#phive-modal-content').fadeIn();

                var policyHtml = '<strong>Booking Status:</strong> ' + data.time_status + '<br/>';
                var is_valid = false;
                policyHtml += '<strong>System Policy:</strong> ' + data.policy_desc;
                $('#phive-policy-text').html(policyHtml);

                var html = '';
                if (data.payers && data.payers.length > 0) {
                    data.payers.forEach(function (payer) {
                        html += '<tr>';
                        // Column 1: Payer
                        html += '<td><strong>' + payer.name + '</strong><br><span style="color:#777; font-size:11px;">' + payer.role + ' #' + payer.id + '</span></td>';

                        // Column 2: Total Paid
                        html += '<td>' + payer.formatted_total + '</td>';

                        // Column 3: Already Refunded (NEW)
                        html += '<td>';
                        if (payer.has_previous_refund) {
                            html += '<span class="phive-prev-refund">-' + payer.formatted_already_refunded + '</span>';
                        } else {
                            html += '-';
                        }
                        html += '</td>';

                        // Column 4: Refund Now
                        html += '<td>';
                        if (payer.is_refundable) {
                            if (payer.is_paid) {
                                html += '<input type="number" step="1" class="phive-refund-input" ';
                                html += 'data-order-id="' + payer.id + '" ';
                                html += 'max="' + payer.max_refund + '" ';
                                html += 'value="' + payer.suggested_refund + '">';
                                html += '<div style="font-size:10px; color:#666; margin-top:2px;">Max left: ' + payer.max_refund + '</div>';
                                is_valid = true;
                            } else {
                                html += '-';
                            }

                        } else {
                            html += '<span style="color:#a00; font-size:12px;">' + payer.status_msg + '</span>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="4">No payers found.</td></tr>';
                }
                $('#phive-refund-rows').html(html);
                if (!is_valid) {
                    $('#phive-process-refund-btn').prop('disabled', true);
                }

            }

            // Process Refunds Button
            $(document).on('click', '#phive-process-refund-btn', function (e) {
                e.preventDefault();
                var refunds = [];
                var totalRefundAmount = 0;

                $('.phive-refund-input').each(function () {
                    var amount = parseFloat($(this).val());
                    var max = parseFloat($(this).attr('max'));
                    if (amount > max) {
                        alert('Error: You cannot refund more than the refundable amount for Booking #' + $(this).data('order-id'));
                        refunds = [];
                        return false;
                    }
                    if (amount > 0) {
                        refunds.push({
                            order_id: $(this).data('order-id'),
                            amount: amount
                        });
                        totalRefundAmount += amount;
                    }
                });

                if (refunds.length === 0) return;
                var msg = (totalRefundAmount === 0)
                    ? "Refund amount is 0.00. This will cancel the orders without sending money back. Continue?"
                    : "Refund a total of " + totalRefundAmount.toFixed(2) + " across " + refunds.length + " orders?";
                if (totalRefundAmount === 0) {
                    alert('Refund amount should be more then 0.');
                    return;
                }
                if (!confirm(msg)) return;

                var $btn = $(this);
                $btn.prop('disabled', true).text('Processing...');
                $('.phive-refund-input').prop('disabled', true);

                $.post(ajaxurl, {
                    action: 'phive_process_manual_refunds',
                    refunds: refunds,
                    parent_id: orderId,
                    security: '<?php echo wp_create_nonce("phive_refund_process"); ?>'
                }, function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                        $btn.prop('disabled', false).text('Confirm & Process Refunds');
                        $('.phive-refund-input').prop('disabled', false);
                    }
                });
            });
        });
    </script>
    <?php
}

/**
 * 3. AJAX: Fetch Data for Modal (Updated to show Manual Refunds)
 */
add_action('wp_ajax_phive_get_group_refund_details', 'phive_get_group_refund_details');

function phive_get_group_refund_details()
{
    // error_reporting(E_ALL); // Report all PHP errors
// ini_set('display_errors', 1);
    check_ajax_referer('phive_refund_view', 'security');

    $parent_id = intval($_POST['order_id']);
    $parent_order = wc_get_order($parent_id);
    if (!$parent_order)
        wp_send_json_error(['message' => 'Order not found']);

    // --- A. Policy Logic ---
    $booking_start_ts = null;
    foreach ($parent_order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->get_type() === 'phive_booking') {
            $from_date = $item->get_meta('phive_display_time_from');
            if (empty($from_date)) {
                $from_date = $item->get_meta('From');
            }
            if (!empty($from_date)) {
                $val = is_array($from_date) ? $from_date[0] : $from_date;
                $booking_start_ts = strtotime($val);
                break;
            }
        }
    }

    $hours_until = 0;
    $policy_percent = 0;
    $time_status = "Unknown Booking Date";
    $policy_desc = "Manual Override";

    if ($booking_start_ts) {

        $now = current_time('timestamp');



        $cancel_at_raw = $parent_order->get_meta('phive_cancellation_requested_at');

        $cancel_at = $cancel_at_raw ? $cancel_at_raw : $now;

        // Safety fallback
        if (!$cancel_at) {
            $cancel_at = $now;
        }

        $seconds_until = $booking_start_ts - $cancel_at;
        $hours_until = round($seconds_until / 3600, 2);

        // Calculate Days and remaining Hours
        $days = floor($seconds_until / 86400);
        $remaining_hours = floor(($seconds_until % 86400) / 3600);
        $time_string = "{$days} days and {$remaining_hours} hours";

        // Booking already started
        if ($hours_until <= 0) {
            $policy_percent = 0;
            $time_status = "Booking has already started.";
            $policy_desc = "100% Cancellation Fee (0% Refund).";

        } elseif ($hours_until < 72) {
            $policy_percent = 0;
            $time_status = "Booking cancelled before {$time_string} (< 72h).";
            $policy_desc = "100% Cancellation Fee (0% Refund).";

        } else {
            $policy_percent = 50;
            $time_status = "Booking cancelled before {$time_string} (> 72h).";
            $policy_desc = "50% Cancellation Fee (50% Refund).";
        }
    }


    // --- B. Build Payers List ---
    $payers = [];

    // 1. Parent
    $payers[] = phive_prepare_payer_data($parent_order, 'Organizer (Parent)', $policy_percent);

    // 2. Children
    $child_payers = $parent_order->get_meta('group_additional_payers');
    if (!empty($child_payers) && is_array($child_payers)) {
        foreach ($child_payers as $p) {
            if (!empty($p['child_order_id'])) {
                $c_order = wc_get_order($p['child_order_id']);
                if ($c_order) {
                    $payers[] = phive_prepare_payer_data($c_order, 'Participant', $policy_percent);
                }
            }
        }
    }

    wp_send_json_success([
        'time_status' => $time_status,
        'policy_desc' => $policy_desc,
        'payers' => $payers
    ]);
}

// Helper to format data with "Already Refunded" logic
function phive_prepare_payer_data($order, $role, $policy_percent)
{
    if ($order->get_date_paid()) {
        $total_paid = $order->get_total();
    } else {
        $total_paid = 0;
    }
    $already_refunded = $order->get_total_refunded(); // Previous manual refunds
    if ($order->get_meta('group_refunded_amount')) {
        //$already_refunded = $order->get_meta('group_refunded_amount');
    }

    $max_refundable = $order->get_remaining_refund_amount();
    if ($order->get_meta('group_remaining_amount')) {
        //$max_refundable = $order->get_meta('group_remaining_amount');
    }
    // Calculate Policy Amount based on ORIGINAL Total
    $target_refund_amount = ($total_paid * $policy_percent) / 100;

    // Smart Suggestion: 
    // If policy says refund $50, but we already refunded $20 manually, suggest $30.
    // If we already refunded $50 (or more), suggest $0.
    $suggested = $target_refund_amount - $already_refunded;

    // Sanity Checks
    if ($suggested < 0)
        $suggested = 0;
    if ($suggested > $max_refundable)
        $suggested = $max_refundable;

    return [
        'id' => $order->get_id(),
        'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'role' => $role,
        'formatted_total' => wc_price($total_paid),
        'formatted_already_refunded' => wc_price($already_refunded), // Display string
        'has_previous_refund' => ($already_refunded > 0),
        'max_refund' => $max_refundable,
        'suggested_refund' => number_format($suggested, 2, '.', ''),
        'is_refundable' => ($max_refundable > 0),
        'status_msg' => ($max_refundable <= 0) ? 'Fully Refunded' : '',
        'is_paid' => ($total_paid > 0)
    ];
}

/**
 * 4. AJAX: Process the Refunds (Robust Version)
 */
add_action('wp_ajax_phive_process_manual_refunds', 'phive_process_manual_refunds');

function phive_process_manual_refunds()
{
    // 1. Security Check
    check_ajax_referer('phive_refund_process', 'security');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Unauthorized user.']);
    }

    $refunds = isset($_POST['refunds']) ? $_POST['refunds'] : [];
    $parent_id = intval($_POST['parent_id']);

    $errors = [];
    $success_count = 0;

    // 2. Process Monetary Refunds
    if (!empty($refunds) && is_array($refunds)) {
        foreach ($refunds as $r) {
            $order_id = intval($r['order_id']);
            $amount = floatval($r['amount']);
            $order = wc_get_order($order_id);
            $customer_id = $order->get_user_id();
            $customer_email = $order->get_billing_email();
            $first_name = get_user_meta($customer_id, 'first_name', true);
            $last_name = get_user_meta($customer_id, 'last_name', true);
            $full_name = trim($first_name . ' ' . $last_name);
            $to = 'Refunded to ' . $full_name . ' (' . $customer_email . ')';

            if ($order && $amount > 0) {
                // Attempt Refund
                $refund = wc_create_refund([
                    'amount' => $amount,
                    'reason' => $to,
                    'order_id' => $order_id,
                    'refund_payment' => true // This triggers the Gateway (Stripe/PayPal)
                ]);

                if (is_wp_error($refund)) {
                    // Capture the Gateway Error
                    $errors[] = "Order #{$order_id}: " . $refund->get_error_message();
                } else {
                    $success_count++;
                    // --- START: Save Refunded & Remaining Meta ---

                    // 1. Re-fetch the order to ensure we get the updated refund totals from the DB
                    // (The old $order variable doesn't know about the refund we just created)
                    $fresh_order = wc_get_order($order_id);

                    // 2. Get accurate totals
                    $total_refunded = $fresh_order->get_total_refunded();
                    if ($order->get_meta('group_refunded_amount')) {
                        $total_refunded = $order->get_meta('group_refunded_amount');
                    }
                    $current_total = $fresh_order->get_total();

                    // 3. Calculate Remaining
                    // Use wc_format_decimal to ensure 2 decimal places (e.g., 50.00)
                    $remaining = wc_format_decimal($current_total - $total_refunded);

                    // 4. Update Meta Data
                    $fresh_order->update_meta_data('group_refunded_amount', $total_refunded);
                    $fresh_order->update_meta_data('group_remaining_amount', $remaining);
                    $fresh_order->update_meta_data('_phive_manual_payment_status', 'refunded');


                    // 5. Save the changes
                    $fresh_order->save();
                }
            }
        }
    }

    // 3. Status Updates
    // Only update status to 'refunded' if there were no critical errors, 
    // or if it was a $0 cancellation (no refunds requested).
    if (empty($errors) && $success_count > 0) {
        wp_send_json_success(['message' => 'Refunds processed successfully and orders marked as Refunded.']);
    } else {
        // Return the specific errors to the user
        $error_msg = "We encountered issues processing the following refunds:\n" . implode("\n", $errors) . "\n\nNo changes have been made to the booking status. Please resolve these errors and try again.";
        wp_send_json_error(['message' => $error_msg]);
    }
}

/* ==========================================================================
   GROUP ORDER BREAKDOWN (Meta Box)
   ========================================================================== */

/**
 * 1. Register the Meta Box
 */
add_action('add_meta_boxes', 'phive_add_group_breakdown_meta_box');

function phive_add_group_breakdown_meta_box()
{
    // Add to 'shop_order' (Legacy) and 'woocommerce_page_wc-orders' (HPOS)
    $screens = ['shop_order', 'woocommerce_page_wc-orders'];

    foreach ($screens as $screen) {
        add_meta_box(
            'phive_group_breakdown_box',
            __('Split Payment Details', 'woocommerce'),
            'phive_render_group_breakdown',
            $screen,
            'normal', // Context: 'normal' (main column) or 'side' (sidebar)
            'high'    // Priority
        );
    }
}

/**
 * 2. Render the Content (The Table)
 */
function phive_render_group_breakdown($post_or_order_object)
{
    // Compatibility: Get Order Object
    $order = ($post_or_order_object instanceof WC_Order) ? $post_or_order_object : wc_get_order($post_or_order_object->ID);
    if (!$order)
        return;

    // Check if Group Parent
    $payment_mode = $order->get_meta('group_payment_mode');
    if ($payment_mode !== 'group') {
        //echo '<p style="color:#777; margin:10px 0;">This is not a Group Parent order.</p>';
        //return;
    }
    $is_group = $order->get_meta('group_payment_mode') === 'group';

    $additional_payers = $order->get_meta('group_additional_payers');

    // Prepare Rows
    $rows = [];

    // 1. Parent
    $rows[] = [
        'type' => 'Organizer',
        'obj' => $order,
        'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'email' => $order->get_billing_email(),
    ];

    // 2. Children
    if (!empty($additional_payers) && is_array($additional_payers)) {
        foreach ($additional_payers as $p) {
            $child_order = (!empty($p['child_order_id'])) ? wc_get_order($p['child_order_id']) : null;
            $rows[] = [
                'type' => 'Participant',
                'obj' => $child_order,
                'name' => $p['name'] ?? 'N/A',
                'email' => $p['email'] ?? 'N/A',
            ];
        }
    }

    ?>
    <style>
        .phive-group-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid #e5e5e5;
        }

        .phive-group-table th {
            background: #f8f8f8;
            padding: 12px;
            border-bottom: 2px solid #ddd;
            font-weight: 600;
            color: #333;
        }

        .phive-group-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .phive-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1;
        }

        .phive-status.completed,
        .phive-status.processing {
            background: #c6e1c6;
            color: #5b841b;
        }

        .phive-status.pending {
            background: #f8dda7;
            color: #94660c;
        }

        .phive-status.cancelled,
        .phive-status.failed {
            background: #e5e5e5;
            color: #777;
        }

        .phive-status.refund-processed {
            background: #e5e5e5;
            color: #a00;
        }

        .phive-money {
            font-size: 13px;
        }

        .phive-strike {
            text-decoration: line-through;
            color: #aaa;
        }
    </style>

    <table class="phive-group-table">
        <thead>
            <tr>
                <th>Role</th>
                <th>Name / Email</th>
                <th>Status</th>
                <th>Paid Amount</th>
                <th>Refunded</th>
                <th>Net Revenue</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Initialize Accumulators
            $sum_total_paid = 0;
            $sum_total_refunded = 0;
            $sum_net_revenue = 0;

            foreach ($rows as $row) {

                $o = $row['obj'];
                $upload_dir = wp_upload_dir();
                if ($o) {
                    if ($row['type'] === 'Organizer') {
                        $inv_url = $upload_dir['baseurl'] . '/invoice-' . $o->get_id() . '.pdf';
                        $rec_url = $upload_dir['baseurl'] . '/receipt-' . $o->get_id() . '.pdf';
                    } else {
                        $inv_url = $upload_dir['baseurl'] . '/invoice-' . $o->get_id() . '.pdf';
                        $rec_url = $upload_dir['baseurl'] . '/receipt-' . $o->get_id() . '.pdf';
                    }
                }


                // Defaults
                $status_slug = 'pending';
                $status_label = 'Not Created';
                $link = '#';

                $raw_total = 0;
                $raw_refund = 0;

                // Display Vars
                $display_total = wc_price(0);
                $display_refund = '-';
                $display_net = wc_price(0);

                // Calc Vars
                $row_calc_total = 0;
                $row_calc_refund = 0;
                $row_calc_net = 0;

                if ($o) {
                    $status_slug = $o->get_meta('_phive_manual_payment_status');
                    $status_label = ucfirst($status_slug);
                    $link = $o->get_edit_order_url();

                    $raw_total = floatval($o->get_total());
                    $raw_refund = floatval($o->get_total_refunded());

                    if ($order->get_meta('group_refunded_amount')) {
                        //$raw_refund = floatval($o->get_meta('group_refunded_amount'));
                    }

                    // LOGIC: Exclude ONLY if truly 0.00 (Unpaid)
                    // If order is Cancelled but $raw_total is > 0 (and not fully refunded yet), we count it.
                    // Actually, if an order is cancelled, WooCommerce usually doesn't change the 'total', 
                    // but we only want to count *money received*.
        
                    // In WooCommerce:
                    // - 'on-hold', 'pending', 'failed', 'cancelled' usually mean money NOT captured yet (unless manually marked paid).
                    // - 'processing', 'completed', 'refunded' mean money WAS captured.
        
                    // However, you specifically asked: "Include cancelled ones if they are paid".
                    // The best check for "Paid" in WC is transaction_id OR date_paid.
                    $is_paid_status = $o->get_date_paid() || !empty($o->get_transaction_id()) || in_array($status_slug, ['processing', 'completed', 'refunded']);

                    // STRICT LOGIC:
                    // If it was NEVER paid, exclude it.
                    if (!$is_paid_status && $raw_refund == 0) {
                        // Visuals: Strike through
                        $display_total = '<span class="phive-strike">' . wc_price($raw_total) . '</span>';
                        $display_refund = '-';
                        $display_net = '<span class="phive-strike">' . wc_price(0) . '</span>';

                        // Math: Zero
                        $row_calc_total = 0;
                        $row_calc_refund = 0;
                        $row_calc_net = 0;
                    } else {
                        // It WAS paid (even if now cancelled/refunded), so we count the money.
                        $display_total = wc_price($raw_total);
                        $display_refund = ($raw_refund > 0) ? '<span style="color:#a00;">-' . wc_price($raw_refund) . '</span>' : '-';
                        $display_net = '<strong>' . wc_price($raw_total - $raw_refund) . '</strong>';

                        // Math: Add Actuals
                        $row_calc_total = $raw_total;
                        $row_calc_refund = $raw_refund;
                        $row_calc_net = $raw_total - $raw_refund;
                    }
                } else {
                    $status_label = 'Pending Creation';
                    $status_slug = 'cancelled';
                }

                // Accumulate
                $sum_total_paid += $row_calc_total;
                $sum_total_refunded += $row_calc_refund;
                $sum_net_revenue += $row_calc_net;
                ?>
                <tr>
                    <td><strong><?php echo esc_html($row['type']); ?></strong></td>
                    <td>
                        <?php echo esc_html($row['name']); ?><br>
                        <small style="color:#888;"><?php echo esc_html($row['email']); ?></small>
                    </td>
                    <td>
                        <span class="phive-status <?php echo esc_attr($status_slug); ?>">
                            <?php echo esc_html($status_label); ?>
                        </span>
                        <?php if ($o): ?>
                            <div style="font-size:10px; margin-top:3px; color:#999;">#<?php echo $o->get_id(); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="phive-money"><?php echo $display_total; ?></td>
                    <td class="phive-money"><?php echo $display_refund; ?></td>
                    <td class="phive-money"><?php echo $display_net; ?></td>
                    <td class="phive-money" style="text-align: center;">
                        <?php if ($status_slug == 'completed' || !empty($is_paid_status)) { ?>
                            <div class="d-flex" style="display: flex;gap:10px;justify-content: center;">
                                <a class="button button-secondary button-large addons-invoice" data-order-id="<?php if ($o) {
                                    echo $o->get_id();
                                } ?>" href="<?php echo $inv_url ?>" fdprocessedid="9avpfo" target="_blank">
                                    <span class="dashicons dashicons-download" style="margin-top:7px;"></span> Invoice
                                </a>
                                <a class="button button-primary button-large addons-receipt" data-order-id="<?php if ($o) {
                                    echo $o->get_id();
                                } ?>" href="<?php echo $rec_url ?>" fdprocessedid="9avpfo" target="_blank">
                                    <span class="dashicons dashicons-download" style="margin-top:7px;"></span> Receipt
                                </a>
                            </div>
                        <?php } else { ?>
                            <a class="button button-secondary button-large addons-invoice" data-order-id="<?php if ($o) {
                                echo $o->get_id();
                            } ?>" href="<?php echo $inv_url ?>" fdprocessedid="9avpfo" target="_blank">
                                <span class="dashicons dashicons-download" style="margin-top:7px;"></span> Invoice
                            </a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr style="background:#fcfcfc; border-top:2px solid #ccc;">
                <td colspan="3"
                    style="text-align:right; text-transform:uppercase; font-size:11px; letter-spacing:0.5px; color:#555; padding-right:15px;">
                    <strong>Total Amount:</strong>
                </td>

                <td class="phive-money" style="font-weight:bold; border-top:2px solid #ccc;">
                    <?php echo wc_price($sum_total_paid); ?>
                </td>

                <td class="phive-money" style="font-weight:bold; color:#a00; border-top:2px solid #ccc;">
                    <?php echo ($sum_total_refunded > 0) ? '-' . wc_price($sum_total_refunded) : '-'; ?>
                </td>

                <td class="phive-money"
                    style="font-weight:bold; color:#000; border-top:2px solid #ccc; background:#efefef;">
                    <?php echo wc_price($sum_net_revenue); ?>
                </td>
            </tr>
        </tfoot>
    </table>
    <script>
        jQuery(function ($) {
            $('#phive_group_breakdown_box h2.hndle').text('<?php echo esc_js($is_group ? __('Split Payment Details', 'woocommerce') : __('Payment Details', 'woocommerce')); ?>');
            $('button#phive_open_refund_modal').text('<?php echo esc_js($is_group ? __('Refund Split Payments', 'woocommerce') : __('Refund Payment', 'woocommerce')); ?>');
        });
    </script>
    <?php
}

/* ---------------------------------------------------------
   ADD CUSTOM STATUS MANAGER META BOX (HPOS COMPATIBLE)
------------------------------------------------------------ */

// 1. Add Meta Box (Supports both Legacy and HPOS)
add_action('add_meta_boxes', 'phive_add_custom_status_box');

function phive_add_custom_status_box()
{
    // A. For Legacy WooCommerce (Post Type)
    $screen_id = 'shop_order';

    // B. For New WooCommerce (HPOS)
    if (function_exists('wc_get_page_screen_id')) {
        $screen_id = wc_get_page_screen_id('shop_order');
    }

    add_meta_box(
        'phive_custom_status_box',        // Unique ID
        'Booking Status',           // Title
        'phive_render_custom_status_box', // Callback function
        $screen_id,                       // Screen ID (Auto-detected)
        'side',                           // Context (Side column)
        'high'                            // Priority
    );
}

// 2. Render the Dropdown and Button
function phive_render_custom_status_box($post_or_order_object)
{
    // Handle both Post object (Legacy) and Order object (HPOS)
    if ($post_or_order_object instanceof WP_Post) {
        $order = wc_get_order($post_or_order_object->ID);
    } else {
        $order = $post_or_order_object;
    }

    if (!$order)
        return;

    $order_id = $order->get_ID();

    $current_status = 'wc-' . $order->get_status();
    //$statuses = wc_get_order_statuses();
    // $order_statuses = [
    //     'wc-pending'           => 'Pending payment',
    //     'wc-processing'        => 'Processing',
    //     'wc-on-hold'           => 'On hold',
    //     'wc-completed'         => 'Completed',
    //     'wc-cancelled'         => 'Cancelled',
    //     'wc-refund-processed'  => 'Refund Processed',
    //     'wc-refunded'          => 'Refunded',
    //     'wc-failed'            => 'Failed',
    //     'wc-checkout-draft'    => 'Draft',
    // ];  
    $cancelled = 'Cancelled';
    if (track_razorpay_by_order_id($order_id) == 'Payment Canceled' || track_razorpay_by_order_id($order_id) == 'Payment Failed') {
        $cancelled = 'NA';
    }
    $statuses = [
        'wc-pending' => 'Pending',
        'wc-processing' => 'In progress',
        'wc-completed' => 'Confirmed',
        'wc-cancelled' => $cancelled,
    ];
    ?>
    <div style="padding: 0;">
        <p style="margin-bottom: 8px;"><strong>Status:</strong></p>
        <?php if ($order->get_meta('fake_status') !== 'completed') { ?>
            <select id="phive_custom_order_status" style="width:100%; margin-bottom: 10px;">
                <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $current_status); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php } else { ?>
            <select id="phive_custom_order_status" style="width:100%; margin-bottom: 10px;">
                <option value="wc-completed" selected>Confirmed</option>
            </select>
        <?php } ?>

        <button type="button" id="phive_update_status_btn" class="button button-primary button-large" style="width:100%;"
            disabled>
            Update Status
        </button>

        <div id="phive_status_message" style="margin-top: 10px; font-weight: bold;"></div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#phive_update_status_btn').on('click', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var newStatus = $('#phive_custom_order_status').val();
                var orderId = <?php echo $order->get_id(); ?>;
                var $msg = $('#phive_status_message');

                $btn.addClass('disabled').text('Updating...');
                $msg.text('').css('color', '#000');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'phive_custom_update_status',
                        order_id: orderId,
                        status: newStatus,
                        security: '<?php echo wp_create_nonce("phive_status_nonce"); ?>'
                    },
                    success: function (response) {
                        if (response.success) {
                            $msg.text('✓ Status Updated! Reloading...').css('color', 'green');
                            location.reload();
                        } else {
                            $btn.removeClass('disabled').text('Update Status Only');
                            $msg.text('Error: ' + (response.data || 'Unknown error')).css('color', 'red');
                        }
                    },
                    error: function () {
                        $btn.removeClass('disabled').text('Update Status Only');
                        $msg.text('Server Error').css('color', 'red');
                    }
                });
            });
        });
    </script>
    <?php
}

// 3. Handle the AJAX Request (Server Side)
add_action('wp_ajax_phive_custom_update_status', 'phive_process_custom_status_update');

function phive_process_custom_status_update()
{
    // error_reporting(E_ALL); // Report all PHP errors
// ini_set('display_errors', 1);
    check_ajax_referer('phive_status_nonce', 'security');

    if (empty($_POST['order_id']) || empty($_POST['status'])) {
        wp_send_json_error('Missing data');
    }

    $order_id = intval($_POST['order_id']);
    $status = sanitize_text_field($_POST['status']);

    // Remove 'wc-' prefix
    $clean_status = str_replace('wc-', '', $status);

    $order = wc_get_order($order_id);

    if (!$order) {
        wp_send_json_error('Invalid Order ID');
    }

    try {

        if ($status === 'wc-completed') {
            $order->update_meta_data('fake_status', 'completed');
            $order->save();
        } else {
            $order->update_status($clean_status, 'Status changed via Admin', true);
            //$order->update_meta_data('_phive_manual_payment_status', 'completed');
            //$order->save();
        }
        wp_send_json_success(['status' => $status]);
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}


//add_filter('manage_woocommerce_page_wc-orders_columns', 'phive_add_refund_column_hpos', 20);

function phive_add_refund_column_hpos($columns)
{
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        // Add after Total column
        if ($key === 'order_total') {
            $new_columns['phive_refund_details'] = __('Refund', 'woocommerce');
        }
    }

    return $new_columns;
}

//add_action('manage_woocommerce_page_wc-orders_custom_column', 'phive_render_refund_column_hpos', 20, 2);

function phive_render_refund_column_hpos($column, $order)
{
    if ($column !== 'phive_refund_details') {
        return;
    }

    if (!$order instanceof WC_Order) {
        return;
    }

    $refunded = $order->get_meta('group_refunded_amount');
    $remaining = $order->get_meta('group_remaining_amount');

    if (!is_numeric($refunded) && !is_numeric($remaining)) {
        echo '—';
        return;
    }

    echo '<div style="line-height:1.4;">';

    if ($refunded > 0) {
        echo '<div style="color:#d63638;">Refunded: ' . wc_price($refunded, ['currency' => $order->get_currency()]) . '</div>';
    }

    if (is_numeric($remaining)) {
        //echo '<div style="color:#666;">Net: ' . wc_price($remaining, ['currency' => $order->get_currency()]) . '</div>';
    }

    echo '</div>';
}

// 1. Add the column header 
add_filter('manage_woocommerce_page_wc-orders_columns', 'phive_add_booking_column_header');

function phive_add_booking_column_header($columns)
{

    $new_columns = array();
    $insert_index = 4;
    $current_index = 0;

    foreach ($columns as $key => $value) {
        // Insert our custom column when the counter matches
        if ($current_index === $insert_index) {

        }
        $new_columns[$key] = $value;
        $current_index++;
    }

    return $new_columns;
}

// 2. Output the column data 
add_action('manage_woocommerce_page_wc-orders_custom_column', 'phive_admin_order_grouping', 10, 2);

function phive_admin_order_grouping($column, $post_or_order)
{
    $order = (is_numeric($post_or_order)) ? wc_get_order($post_or_order) : $post_or_order;
    if (!$order)
        return;

    if ($column === 'booking_date_time') {
        $details = get_booking_details($order);
        if (isset($details['datetime'])) {
            echo $details['datetime'];
        }
    }
}

add_filter('post_class', 'phive_add_order_row_classes', 10, 3);

function phive_add_order_row_classes($classes, $class, $post_id)
{
    // 1. Check if we are in the Admin Order List
    if (!is_admin() || get_post_type($post_id) !== 'shop_order') {
        return $classes;
    }

    $order = wc_get_order($post_id);
    if (!$order)
        return $classes;

    // 2. Retrieve Meta Data to determine type
    $additional_payers = $order->get_meta('group_additional_payers');
    $parent_id = $order->get_meta('group_parent_order');

    // 3. Logic to assign classes

    // A. PARENT ORDER (Has "additional payers" list)
    if (!empty($additional_payers) && is_array($additional_payers)) {
        $classes[] = 'order-type-parent';
    }
    // B. CHILD ORDER (Has a "parent ID" pointing to another order)
    elseif (!empty($parent_id)) {
        $classes[] = 'order-type-child parent-' . $parent_id;
    }
    // C. FULL / STANDARD ORDER (Neither parent nor child)
    else {
        $classes[] = 'order-type-full';
    }

    return $classes;
}
add_filter('woocommerce_shop_order_list_table_order_css_classes', 'phive_hpos_order_classes', 10, 2);

function phive_hpos_order_classes($classes, $order)
{
    // 2. Retrieve Meta Data
    $additional_payers = $order->get_meta('group_additional_payers');
    $parent_id = $order->get_meta('group_parent_order');

    // 3. Logic
    if (!empty($additional_payers) && is_array($additional_payers)) {
        $classes[] = 'order-type-parent';
    } elseif (!empty($parent_id)) {
        $classes[] = 'order-type-child parent-' . $parent_id;
    } else {
        $classes[] = 'order-type-full';
    }

    return $classes;
}

//add_action('init', 'register_refund_processed_order_status');

function register_refund_processed_order_status()
{
    register_post_status('wc-refund-processed', [
        'label' => 'Refund Processed',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop(
            'Refund Processed <span class="count">(%s)</span>',
            'Refund Processed <span class="count">(%s)</span>'
        ),
    ]);
}

//add_filter('wc_order_statuses', 'add_refund_processed_to_order_statuses');
// ==============================================================================
// Rename "Pending payment" status to "Pending"
// ==============================================================================
add_filter('wc_order_statuses', 'phive_rename_pending_payment_status');
function phive_rename_pending_payment_status($order_statuses)
{

    // Check if the 'wc-pending' status exists in the array
    if (isset($order_statuses['wc-pending'])) {
        $order_statuses['wc-pending'] = _x('Pending', 'Order status', 'woocommerce');
    }
    if (isset($order_statuses['wc-processing'])) {
        $order_statuses['wc-processing'] = _x('In progress ', 'Order status', 'woocommerce');
    }
    return $order_statuses;
}
function add_refund_processed_to_order_statuses($order_statuses)
{
    $new_statuses = [];

    foreach ($order_statuses as $key => $label) {
        $new_statuses[$key] = $label;

        // Insert after Cancelled (change if needed)
        if ($key === 'wc-cancelled') {
            $new_statuses['wc-refund-processed'] = 'Refund Processed';
        }
    }

    return $new_statuses;
}



/* ---------------------------------------------------------
   ADMIN ORDER LIST: Add "Order Type" Column
   --------------------------------------------------------- */

// 1. Add Column Header (Supports Legacy & HPOS)
//add_filter('manage_edit-shop_order_columns', 'add_order_type_column_header', 20);
//add_filter('manage_woocommerce_page_wc-orders_columns', 'add_order_type_column_header', 20);

function add_order_type_column_header($columns)
{
    $new_columns = array();

    // Loop to insert the new column right after "Order" (first column)
    foreach ($columns as $key => $column) {
        $new_columns[$key] = $column;
        if ('order_number' === $key || 'order_title' === $key) { // 'order_title' is sometimes used in plugins
            $new_columns['order_type'] = 'Type';
        }
    }

    // Fallback: If logic above missed, append to end
    if (!isset($new_columns['order_type'])) {
        $new_columns['order_type'] = 'Type';
    }

    return $new_columns;
}

// 2. Populate Column Content (Legacy Mode)
add_action('manage_shop_order_posts_custom_column', 'fill_order_type_column_content_legacy', 10, 2);
function fill_order_type_column_content_legacy($column, $post_id)
{
    if ('order_type' === $column) {
        $order = wc_get_order($post_id);
        render_order_type_label($order);
    }
}

// 3. Populate Column Content (HPOS Mode)
add_action('manage_woocommerce_page_wc-orders_custom_column', 'fill_order_type_column_content_hpos', 10, 2);
function fill_order_type_column_content_hpos($column, $order)
{
    if ('order_type' === $column) {
        render_order_type_label($order);
    }
}

// 4. Common Rendering Logic
function render_order_type_label($order)
{
    if (!$order)
        return;

    // A. Check for Addons Order 
    // Logic: Has '_parent_order_id' (This covers Admin Batches and Addon Invoices)
    if ($order->get_meta('_parent_order_id')) {
        echo '<span style="display:inline-block; background:#e0f7fa; color:#006064; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700; border:1px solid #b2ebf2;">Addons</span>';
        return;
    }

    // B. Check for Child Booking Order (Split Payment)
    // Logic: Has 'group_parent_order' (Standard from your existing setup)
    if ($order->get_meta('group_parent_order')) {
        echo '<span style="display:inline-block; background:#fce4ec; color:#880e4f; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700; border:1px solid #f8bbd0;">Split Payment</span>';
        return;
    }

    if ($order->get_meta('addons_parent_order_id')) {
        echo '<span style="display:inline-block; background:#fce4ec; color:#880e4f; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700; border:1px solid #f8bbd0;">Add-ons Bill</span>';
        return;
    }

    if ($order->get_meta('group_payment_mode') === 'group' || $order->get_meta('meeting_addons_billed')) {
        echo '<span style="display:inline-block; background:#fce4ec; color:#880e4f; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700; border:1px solid #f8bbd0;">Split Payment</span>';
        return;
    }
    // C. Default: Booking Order
    // Logic: Everything else (Main Parents or Standard Orders)
    echo '<span style="display:inline-block; background:#e8f5e9; color:#1b5e20; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700; border:1px solid #c8e6c9;">' . get_booking_type($order->ID) . '</span>';
}

/* ---------------------------------------------------------
   ADMIN: MANUAL INVOICE EMAIL SENDER
   (Uses send_woocommerce_custom_email)
   --------------------------------------------------------- */

// 1. Add Meta Box to Order Edit Screen
add_action('add_meta_boxes', 'add_invoice_email_meta_box');
function add_invoice_email_meta_box()
{
    add_meta_box(
        'invoice_email_actions',
        'Invoice Actions',
        'render_invoice_email_meta_box',
        ['shop_order', 'woocommerce_page_wc-orders'],
        'side',
        'high'
    );
}

// 2. Render the Button
function render_invoice_email_meta_box($post)
{
    if ($post instanceof WC_Order) {
        $order_id = $post->get_id();
    } else {
        $order_id = $post->ID;
    }
    ?>
    <div style="text-align:center; padding:10px;">

        <div id="invoice-email-feedback" style="margin-top:10px; font-weight:600;"></div>
    </div>

    <script>
        jQuery(document).ready(function ($) {


            // --- 2. DOWNLOAD LOGIC ---
            $('#btn-download-invoice,.addons-invoice').on('click', function (e) {
                e.preventDefault();

                var btn = $(this);
                var orderId = btn.data('order-id');
                var originalText = btn.html();

                btn.prop('disabled', true).text('Generating...');

                $.post(ajaxurl, {
                    action: 'admin_download_invoice_pdf',
                    order_id: orderId,
                    security: '<?php echo wp_create_nonce("admin_invoice_action"); ?>'
                }, function (response) {
                    btn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        // Open PDF in new tab
                        window.open(response.data.url, '_blank');
                    } else {
                        alert(response.data.message);
                    }
                }).fail(function () {
                    btn.prop('disabled', false).html(originalText);
                    alert('Server error occurred.');
                });
            });
            $('#btn-download-receipt').on('click', function (e) {
                e.preventDefault();

                var btn = $(this);
                var orderId = btn.data('order-id');
                var originalText = btn.html();

                btn.prop('disabled', true).text('Generating...');

                $.post(ajaxurl, {
                    action: 'admin_download_receipt_pdf',
                    order_id: orderId,
                    security: '<?php echo wp_create_nonce("admin_invoice_action"); ?>'
                }, function (response) {
                    btn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        // Open PDF in new tab
                        window.open(response.data.url, '_blank');
                    } else {
                        alert(response.data.message);
                    }
                }).fail(function () {
                    btn.prop('disabled', false).html(originalText);
                    alert('Server error occurred.');
                });
            });
        });
    </script>
    <?php
}


function generate_admin_invoice_pdf($order)
{

    $options = new Options();
    $options->set('isRemoteEnabled', true); // Allow remote images
    $dompdf = new Dompdf($options);

    $mailer = WC()->mailer();
    $emails = $mailer->get_emails();
    $email = $emails['WC_Email_Customer_Invoice'];

    // Generate invoice HTML from template
    ob_start();
    wc_get_template(
        'emails/customer-invoice-admin.php',
        array(
            'order' => $order,
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $email,
            'email_heading' => 'Receipt'
        )
    );
    $invoice_html = ob_get_clean();

    // Render PDF
    $dompdf->loadHtml($invoice_html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->get_font("Inter", "normal");
    $canvas->page_text(200, 810, "We appreciate the opportunity to serve you.", $font, 10, array(0, 0, 0));
    // Save PDF to uploads folder
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/invoice-' . $order->get_id() . '.pdf';
    file_put_contents($file_path, $dompdf->output());

    return $file_path;
}


// 4. AJAX Handler: Download PDF (NEW)
add_action('wp_ajax_admin_download_invoice_pdf', 'admin_download_invoice_pdf');
function admin_download_invoice_pdf()
{
    check_ajax_referer('admin_invoice_action', 'security');
    if (empty($_POST['order_id']))
        wp_send_json_error(['message' => 'Missing Order ID']);

    $order_id = intval($_POST['order_id']);
    $order = wc_get_order($order_id);

    if (!$order)
        wp_send_json_error(['message' => 'Order not found']);

    // Generate File
    $file_path = generate_admin_invoice_pdf($order);

    if ($file_path && file_exists($file_path)) {
        // Convert File Path to URL
        $upload_dir = wp_upload_dir();
        $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);

        wp_send_json_success(['url' => $file_url]);
    } else {
        wp_send_json_error(['message' => 'Could not generate PDF file.']);
    }
}
// 4. AJAX Handler: Download PDF (NEW)
add_action('wp_ajax_admin_download_receipt_pdf', 'admin_download_receipt_pdf');
function admin_download_receipt_pdf()
{
    check_ajax_referer('admin_invoice_action', 'security');
    if (empty($_POST['order_id']))
        wp_send_json_error(['message' => 'Missing Order ID']);

    $order_id = intval($_POST['order_id']);
    $order = wc_get_order($order_id);

    if (!$order)
        wp_send_json_error(['message' => 'Order not found']);

    // Generate File
    $file_path = generate_receipt_admin_pdf($order);

    if ($file_path && file_exists($file_path)) {
        // Convert File Path to URL
        $upload_dir = wp_upload_dir();
        $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);

        wp_send_json_success(['url' => $file_url]);
    } else {
        wp_send_json_error(['message' => 'Could not generate PDF file.']);
    }
}

add_action(
    'woocommerce_admin_order_data_after_billing_address',
    'phive_render_checkout_billing_form_hidden'
);

function phive_render_checkout_billing_form_hidden($order)
{
    if (!$order) {
        return;
    }

    $checkout = WC()->checkout();
    $billing_fields = $checkout->get_checkout_fields('billing');

    echo '<div id="phive-checkout-billing-admin" style="display:none; margin-top:15px;">';
    echo '<h4>Billing Details</h4>';

    foreach ($billing_fields as $key => $field) {

        // Remove "billing_" prefix for getter
        $prop = str_replace('billing_', '', $key);
        $getter = "get_billing_{$prop}";

        $value = '';

        // 1️⃣ Preferred: Order getter
        if (is_callable([$order, $getter])) {
            $value = $order->$getter();
        }

        // 2️⃣ Fallback: meta
        if (empty($value)) {
            $value = $order->get_meta("_{$key}");
        }

        // 3️⃣ Fallback: user meta (if order user exists)
        if (empty($value) && $order->get_user_id()) {
            $value = get_user_meta(
                $order->get_user_id(),
                $key,
                true
            );
        }
        if ($key === 'billing_country' && empty($value)) {
            $value = 'IN';
        }
        woocommerce_form_field($key, $field, $value);
    }

    echo '</div>';
}
add_action(
    'woocommerce_process_shop_order_meta',
    'phive_force_save_admin_billing_fields',
    99
);

function phive_force_save_admin_billing_fields($order_id)
{
    // Check if the billing form is actually present in the request
    if (empty($_POST['billing_first_name'])) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    // 1. Handle GSTIN (Order Meta & User Meta)
    if (isset($_POST['billing_gstin'])) {
        $gstin_value = sanitize_text_field($_POST['billing_gstin']);

        // Save to Order
        $order->update_meta_data('billing_gstin', $gstin_value);

        // Save to User Profile
        $user_id = $order->get_customer_id();
        if ($user_id && $user_id > 0) {
            update_user_meta($user_id, 'billing_gstin', $gstin_value);
        }
    }

    // 2. Map fields based on your provided HTML name attributes
    $map = [
        'billing_first_name' => 'set_billing_first_name',
        'billing_last_name' => 'set_billing_last_name',
        'billing_company_name' => 'set_billing_company', // Maps HTML 'billing_company_name' to WC Company
        'billing_phone' => 'set_billing_phone',
        'billing_email' => 'set_billing_email',
        'billing_address_1' => 'set_billing_address_1',
        // 'billing_address_2' is not in your HTML, skipping
        'billing_city' => 'set_billing_city',
        'billing_state' => 'set_billing_state',
        'billing_postcode' => 'set_billing_postcode',
        'billing_country' => 'set_billing_country',
    ];

    foreach ($map as $field_name => $setter_method) {
        if (isset($_POST[$field_name]) && is_callable([$order, $setter_method])) {
            $value = wc_clean($_POST[$field_name]);
            $order->$setter_method($value);

            // Also update User Meta for standard fields if customer exists
            $user_id = $order->get_customer_id();
            if ($user_id && $user_id > 0) {
                update_user_meta($user_id, $field_name, $value);
            }
        }
    }

    // 3. Final save for Order Object
    $order->save();
}


/**
 * Retrieve a list of booking names and times from a single order.
 *
 * @param int $order_id The Order ID.
 * @return array List of bookings array( 'name', 'date_display' ).
 */
function get_bookings_list_from_order($order_id)
{
    $order = wc_get_order($order_id);
    if ($order->get_meta('group_parent_order')) {
        $order = wc_get_order($order->get_meta('group_parent_order'));
    }
    if (!$order)
        return [];

    $booking_items = [];

    // Loop through all items in this single order
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        // Check if product is a booking type (or if specific meta exists)
        if ($product && $product->get_type() === 'phive_booking') {

            // 1. Get Meta Data (Handling potential array vs string)
            $booked_from_raw = $item->get_meta('Booked From');
            $booked_to_raw = $item->get_meta('Booked To');

            $booked_from = is_array($booked_from_raw) ? $booked_from_raw[0] : $booked_from_raw;
            $booked_to = is_array($booked_to_raw) ? $booked_to_raw[0] : $booked_to_raw;

            // 2. Format the Date String
            $date_display = '';

            if (!empty($booked_from) && !empty($booked_to)) {
                $ts_from = strtotime($booked_from);
                $ts_to = strtotime($booked_to);

                if ($ts_from && $ts_to) {
                    // Check if Start and End are on the same day
                    if (date('Ymd', $ts_from) === date('Ymd', $ts_to)) {
                        // Format: "October 20, 2025 (9:00 am – 11:00 am)"
                        $date_display = date('M j, Y', $ts_from) . ' (' . date('g:i a', $ts_from) . ' – ' . date('g:i a', $ts_to) . ')';
                    } else {
                        // Multi-day Format: "Oct 20, 9am – Oct 21, 5pm"
                        $date_display = date('M j, g:i a', $ts_from) . ' – ' . date('M j, g:i a', $ts_to);
                    }
                } else {
                    // Fallback to raw string if parsing fails
                    $date_display = $booked_from . ' – ' . $booked_to;
                }
            }

            // 3. Add to List
            $booking_items[] = [
                'name' => $item->get_name(),     // Product Name
                'date' => $date_display,         // Formatted Date Time
            ];

            $html = '<ul class="phive-booking-list">';
            foreach ($booking_items as $booking) {
                $html .= '<li>';
                $html .= '<strong>' . esc_html($booking['name']) . '</strong>';
                if (!empty($booking['date'])) {
                    $html .= '<span style="color:#666;">' . esc_html($booking['date']) . '</span>';
                }
                $html .= '</li>';
                break;
            }
            $html .= '</ul>';
        }
    }

    return $html;
}

add_action('init', 'auto_generate_username_from_email');

function auto_generate_username_from_email()
{

    // 1. Check if the user is submitting a form with an Email but NO Username
    // Standard WP uses 'user_email', but we check 'email' too just in case.
    $email = '';
    if (!empty($_POST['user_email'])) {
        $email = $_POST['user_email'];
    } elseif (!empty($_POST['email'])) {
        $email = $_POST['email'];
    }

    // Only run if we found an email AND the username is missing
    if ($email && empty($_POST['user_login'])) {

        // 2. Extract part before '@'
        $parts = explode('@', $email);
        $email_prefix = $parts[0];

        // 3. Strict Sanitization (Fixes "Illegal Characters" error)
        // 'true' removes accents and special chars that WP rejects
        $new_username = sanitize_user($email_prefix, true);

        // Fallback: If strict sanitization stripped everything (e.g. email was "???@gmail.com")
        if (empty($new_username)) {
            $new_username = 'user';
        }

        // 4. Ensure Uniqueness (Fixes potential duplicates)
        $base_username = $new_username;
        while (username_exists($new_username)) {
            $new_username = $base_username . wp_rand(100, 9999);
        }

        // 5. THE FIX: Inject the generated name back into $_POST
        // This tricks WordPress into thinking the user typed this name manually.
        $_POST['user_login'] = $new_username;
    }
}


add_action('admin_init', function () {

    $user_id = isset($_GET['id']) ? absint($_GET['id']) : null;
    if (!$user_id) {
        return;
    }

    $redirect = admin_url('admin.php?page=add-booking');

    // Append user_id to redirect URL
    $redirect = add_query_arg('user_id', $user_id, $redirect);
    if (
        is_admin() &&
        isset($GLOBALS['pagenow']) &&
        $GLOBALS['pagenow'] === 'users.php' && isset($_GET['id'])
    ) {
        wp_safe_redirect($redirect);
        exit;
    }

});



/* ---------------------------------------------------------
   9. Admin: Manual Payment Status Meta Box
--------------------------------------------------------- */

// 1. Add Meta Box
add_action('add_meta_boxes', 'phive_add_payment_status_box');
function phive_add_payment_status_box()
{
    $screens = ['shop_order', 'woocommerce_page_wc-orders'];
    foreach ($screens as $screen) {
        add_meta_box(
            'phive_payment_status_box',  // Box ID
            'Payment Status',     // Box Title
            'phive_render_payment_box',  // Callback Function
            $screen,
            'side',                      // Location (Side column)
            'default'
        );
    }
}

// 2. Render Box Content
function phive_render_payment_box($post_or_order_object)
{
    // Get Order Object (Compatible with HPOS and Legacy)
    $order = ($post_or_order_object instanceof WC_Order) ? $post_or_order_object : wc_get_order($post_or_order_object->ID);
    if (!$order)
        return;

    // Retrieve saved status
    $default_status = $order->get_status();
    // print_r($default_status);
    // echo "<pre>";
    $current_status = $order->get_meta('_phive_manual_payment_status');
    $mode = $order->get_meta('_payment_mode');

    // Define options
    // $options = [
    //     '' => 'Select Status...',
    //     'pending' => 'Pending',
    //     'fully_paid_cash' => 'Cash Payment',
    //     'fully_paid_online' => 'Online Payment',
    //     'refund-processed-manual' => 'Refunded'
    // ];
    $options = [
        '' => 'Select Status...',
        'pending' => 'Pending',
        'failed' => 'Payment Failed',
        'completed' => 'Completed ',
        'cancelled' => 'Refund Pending',
        'refunded' => 'Refunded'
    ];
    // Output Field
    echo '<select name="phive_manual_payment_status" style="width:100%; margin-bottom:10px;">';
    foreach ($options as $key => $label) {
        $selected = selected($current_status, $key, false);
        echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';

    if ($mode === 'cash') {
        echo '<p class="mode" style="margin-top:0"><input type="radio" value="online" name="payment_mode" >Online</input>';
        echo '<input type="radio"  name="payment_mode" value="cash" style="margin-left:10px" checked>Cash</input></p>';
    } else {
        echo '<p class="mode" style="margin-top:0"><input type="radio" value="online" name="payment_mode" checked>Online</input>';
        echo '<input type="radio"  name="payment_mode" value="cash" style="margin-left:10px">Cash</input></p>';
    }



    echo '<button type="submit" class="button save_order button-primary" name="save" value="Update">Update</button>';

}

// 3. Save Data
add_action('woocommerce_process_shop_order_meta', 'phive_save_manual_payment_status', 45, 2);
function phive_save_manual_payment_status($order_id, $post)
{
    $order = wc_get_order($order_id);
    if (!$order)
        return;
    // Prevent conflict: Skip manual status update if JS triggered a cancellation
    if (isset($_POST['phive_cancellation_reason']) && !empty($_POST['phive_cancellation_reason'])) {
        return;
    }
    // Save Status
    if (isset($_POST['phive_manual_payment_status'])) {
        $order->update_meta_data('_phive_manual_payment_status', sanitize_text_field($_POST['phive_manual_payment_status']));
        if ($_POST['phive_manual_payment_status'] == 'completed') {
            $order->update_meta_data('_payment_mode', sanitize_text_field($_POST['payment_mode']));
            $order->update_meta_data('fake_status', '');
            $order->update_status('completed', 'Payment status changed to completed by admin Manually.');
            generate_receipt_admin_pdf($order);
        }
        if ($_POST['phive_manual_payment_status'] == 'pending') {
            $order->update_meta_data('_payment_mode', '');
            $order->update_status('pending', 'Payment status changed to pending by admin Manually.');
        }
        if ($_POST['phive_manual_payment_status'] == 'refunded' || $_POST['phive_manual_payment_status'] == 'cancelled') {
            $order->update_meta_data('_payment_mode', '');
            $order->update_status('cancelled', 'Payment status changed to cancelled by admin Manually.');
        }
    }

    $order->save();
}

function create_master_invoice($child_order_ids)
{
    if (empty($child_order_ids)) {
        return;
    }

    // 1. Fetch all Order Objects
    $orders = [];
    foreach ($child_order_ids as $id) {
        $order = wc_get_order($id);
        if ($order) {
            $orders[] = $order;
        }
    }

    if (empty($orders)) {
        return;
    }

    // 2. Use the first order as the "Main" order for Billing Details
    $main_order = $orders[0];

    // 3. Generate HTML using a custom 'master-invoice' template
    ob_start();
    wc_get_template(
        'emails/admin-addons-invoice.php', // You must create this file (see Part 2)
        array(
            'orders' => $orders,
            'main_order' => $main_order,
        )
    );
    $invoice_html = ob_get_clean();

    // 4. Generate PDF (Standard Dompdf Logic)
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($invoice_html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Add Footer Text
    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->get_font("Inter", "normal");
    $canvas->page_text(200, 810, "We appreciate the opportunity to serve you.", $font, 10, array(0, 0, 0));

    // 5. Stream the PDF
    // We create a combined ID for the filename (e.g., "receipt-123-124-125.pdf")
    $file_ids = implode('-', $child_order_ids);
    $filename = 'combined-receipt-' . $file_ids . '.pdf';
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file_path, $dompdf->output());

    return $file_path;
}



/* ---------------------------------------------------------
   ADMIN ADDONS: 1. AJAX to Load Popup HTML
--------------------------------------------------------- */
add_action('wp_ajax_load_admin_addon_popup', 'phive_load_admin_addon_popup');
function phive_load_admin_addon_popup()
{
    // Security check
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Unauthorized');
    }

    $order_id = intval($_POST['order_id']);
    $order = wc_get_order($order_id);

    if (!$order) {
        wp_send_json_error('Order not found');
    }

    // 1. Get Total Members from Order Meta
    $total_member = (int) $order->get_meta('Total Participants');
    if (!$total_member) {
        // Fallback: Try to find it in line items
        foreach ($order->get_items() as $item) {
            if ($item->get_product() && $item->get_product()->get_type() === 'phive_booking') {
                $total_member = (int) $item->get_meta('Total Participants');
                break;
            }
        }
    }
    // Default to 0 so we can trigger the popup
    if (!$total_member)
        $total_member = 0;

    // 2. Get Existing Addons in this Order
    $existing_addons = [];
    foreach ($order->get_items() as $item_id => $item) {
        $prod_id = $item->get_product_id();
        $existing_addons[$prod_id] = $item->get_quantity();
    }

    // 3. Get Saved Remarks
    $saved_remarks = [];
    foreach ($order->get_meta_data() as $meta) {
        if (strpos($meta->key, 'Remarks for ') === 0) {
            $cat_name = str_replace('Remarks for ', '', $meta->key);
            $term = get_term_by('name', $cat_name, 'product_cat');
            if ($term) {
                $saved_remarks[$term->slug] = $meta->value;
            }
        }
    }

    // 4. Fetch Categories & Products
    $parent_cat_slug = 'addons';
    $parent_term = get_term_by('slug', $parent_cat_slug, 'product_cat');

    if (!$parent_term) {
        wp_send_json_error('Addons category not found');
    }

    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => $parent_term->term_id,
    ]);

    $placeholders = [
        'support-services' => 'Eg: Add support service details (e.g., operator at 10:00 am)',
        'meals' => 'Your meal can be customized later. ',
        'refreshments' => 'Eg: Provide cookies/veg puff at 3:00 pm',
        'stationery' => 'Eg: Place notepads and pens on each table',
    ];

    ob_start();
    ?>

    <div id="admin-extra-services-popup" class="admin-popup-overlay">
        <div class="popup-content popup-content-admin">
            <input type="hidden" id="admin_total_member" value="<?php echo esc_attr($total_member); ?>">
            <input type="hidden" id="admin_order_id" value="<?php echo esc_attr($order_id); ?>">

            <div class="close-icn" id="close-admin-popup">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#333"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>



            <div class="addon-section">
                <h2>Add-ons for Booking #<?php echo $order_id; ?></h2>
                <div style="margin-bottom:10px; font-size:13px; color:#666;">
                    <strong>Total Participants: </strong>
                    <span id="disp_mem_count"><?php echo ($total_member > 0) ? $total_member : 'Not set'; ?></span>
                    <a href="#" id="edit_member_count_link" style="margin-left:5px; font-size:11px;">[Edit]</a>
                </div>

                <table class="service-table" border="1" cellspacing="0" cellpadding="8">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Select Count</th>
                            <th>Select for All</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category) {
                            echo '<tr class="category-row"><td colspan="5" style="font-weight:600;">' . esc_html($category->name) . '</td></tr>';

                            $products = wc_get_products([
                                'status' => 'publish',
                                'limit' => -1,
                                'category' => [$category->slug],
                            ]);

                            if (!empty($products)) {
                                foreach ($products as $product) {
                                    $product_id = $product->get_id();
                                    $description = get_field('_description', $product_id);
                                    $price = $product->get_regular_price();
                                    $qty = isset($existing_addons[$product_id]) ? $existing_addons[$product_id] : 0;

                                    $max_attr = ($product_id == 3015) ? 'max="1"' : 'max="100"';

                                    // If member count is 0, display 0, otherwise display count
                                    $max_display = ($product_id == 3015) ? '1' : ($total_member > 0 ? $total_member : 0);

                                    $class = ($product_id == 3015) ? 'operator' : 'dynamic-max';

                                    if ($product->get_name() != 'Printing') {
                                        echo '<tr class="service-row" data-price="' . esc_attr($price) . '">';

                                        echo '<td>' . esc_html($product->get_name());
                                        if ($description) {
                                            echo '<div class="service_desc">' . $description . '</div>';
                                        }
                                        echo '</td>';

                                        echo '<td>₹' . esc_html($price) . '</td>';

                                        echo '<td>
                                            <input type="number" 
                                                class="addon-qty" 
                                                value="' . esc_attr($qty) . '" 
                                                data-product_id="' . esc_attr($product_id) . '"
                                                min="0" ' . $max_attr . '>
                                        </td>';

                                        echo '<td>
                                            <label class="switch">
                                                <input type="checkbox" class="addon-all">
                                                <span class="slider round ' . $class . '">' . $max_display . '</span>
                                            </label>
                                        </td>';

                                        $line_total = $price * $qty;
                                        echo '<td class="addon-total">₹' . esc_html($line_total) . '</td>';
                                        echo '</tr>';
                                    }
                                }
                            }

                            $remark_val = isset($saved_remarks[$category->slug]) ? $saved_remarks[$category->slug] : '';
                            $placeholder = isset($placeholders[$category->slug]) ? $placeholders[$category->slug] : '';

                            echo '<tr class="remarks-row">
                                <td colspan="5">
                                    <div class="rem-box"><span class="rem-label">Add Remark:</span>
                                    <textarea class="addon-remark auto-height" 
                                        rows="2" 
                                        style="width:100%;" 
                                        data-category-slug="' . esc_attr($category->slug) . '"
                                        placeholder="' . $placeholder . '">' . esc_textarea($remark_val) . '</textarea>
                                    </div>
                                </td>
                            </tr>';
                        } ?>

                        <?php
                        $brands = get_terms(array(
                            'taxonomy' => 'product_brand',
                            'hide_empty' => true,
                            'meta_query' => array(
                                array(
                                    'key' => '_enabledisable',
                                    'value' => 1,
                                    'compare' => '='
                                )
                            )
                        ));
                        if (!empty($brands) && !is_wp_error($brands)): ?>
                            <tr class="category-row">
                                <td colspan="5" style="font-weight:600;">Snacks & Light Bites</td>
                            </tr>
                            <tr class="category-row">
                                <td colspan="5" style="font-weight:600;">
                                    <div class="rm_tabs">

                                        <ul class="rm_tabs_ul">

                                            <!-- View All -->
                                            <li class="rm_tabs_li active" data-filter="all">
                                                <div class="rm_tabs_inner">
                                                    <span>View All</span>
                                                </div>
                                            </li>

                                            <?php foreach ($brands as $brand):
                                                // If you are using term meta for image
                                                $brand_image = "";
                                                $brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
                                                if ($brand_image_id) {
                                                    $brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
                                                }
                                                //print_r(get_term_meta( $brand->term_id ));
                                    
                                                // Fallback image (optional)
                                                if (empty($brand_image)) {
                                                    //$brand_image = get_stylesheet_directory_uri() . '/images/default-brand.svg';
                                                }
                                                ?>

                                                <li class="rm_tabs_li" data-filter="<?php echo esc_attr($brand->slug); ?>">
                                                    <div class="rm_tabs_inner">

                                                        <?php if (!empty($brand_image)): ?>
                                                            <img src="<?php echo esc_url($brand_image[0]); ?>"
                                                                alt="<?php echo esc_attr($brand->name); ?>">
                                                        <?php endif; ?>

                                                        <span><?php echo esc_html($brand->name); ?></span>
                                                    </div>
                                                </li>

                                            <?php endforeach; ?>

                                        </ul>

                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php
                        // Step 1: Get enabled brands
                        $enabled_brands = get_terms(array(
                            'taxonomy' => 'product_brand',
                            'hide_empty' => true,
                            'meta_query' => array(
                                array(
                                    'key' => '_enabledisable',
                                    'value' => '1',
                                    'compare' => '='
                                )
                            ),
                            'fields' => 'ids'
                        ));

                        // Step 2: Query products with those brands
                        $args = array(
                            'post_type' => 'product',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_brand',
                                    'field' => 'term_id',
                                    'terms' => $enabled_brands
                                ),
                            ),
                        );

                        $query = new WP_Query($args);

                        if ($query->have_posts()):
                            $row_count = 1;

                            while ($query->have_posts()):
                                $query->the_post();
                                $product = wc_get_product(get_the_ID());
                                $product_id = get_the_ID();

                                $qty = isset($existing_addons[$product_id]) ? $existing_addons[$product_id] : 0;

                                $max_attr = ($product_id == 3015) ? 'max="1"' : 'max="100"';

                                // If member count is 0, display 0, otherwise display count
                                $max_display = ($product_id == 3015) ? '1' : ($total_member > 0 ? $total_member : 0);

                                $class = ($product_id == 3015) ? 'operator' : 'dynamic-max';

                                foreach ($saved_cart as $cart_item) {
                                    $cart_product_id = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
                                    if ($cart_product_id == $product_id) {
                                        $existing_qty = $cart_item['quantity'];
                                        break;
                                    }
                                }


                                $brands = get_the_terms(get_the_ID(), 'product_brand');
                                //print_r($brands);
                                $brand_slug = !empty($brands) && !is_wp_error($brands) ? $brands[0]->slug : '';
                                $brand_title = !empty($brands) && !is_wp_error($brands) ? $brands[0]->name : '-';

                                $image_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                                if (!$image_url) {
                                    $image_url = get_stylesheet_directory_uri() . '/images/hot-pan.svg';
                                }

                                // If you are using term meta for image
                                $brand_image = "";
                                $brand_image_id = get_term_meta($brands[0]->term_id, 'thumbnail_id', true);
                                if ($brand_image_id) {
                                    $brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
                                }
                                //print_r(get_term_meta( $brand->term_id ));
                    
                                // Fallback image (optional)
                                if (empty($brand_image)) {
                                    //$brand_image[0] = get_stylesheet_directory_uri() . '/images/hot-pan.svg';
                                }

                                $price_html = $product ? $product->get_price_html() : '';
                                $price = $product->get_regular_price();
                                ?>

                                <tr id="rm_table_row_<?php echo esc_attr($row_count); ?>" class="service-row rm_table_row"
                                    data-filter="<?php echo esc_attr($brand_slug); ?>" data-price="<?php echo esc_attr($price); ?>">

                                    <td>
                                        <div class="rm_item_meta">
                                            <?php if (!empty($brand_image)) { ?>
                                                <div class="rm_item_image">
                                                    <img src="<?php echo esc_url($brand_image[0]); ?>"
                                                        alt="<?php echo esc_attr($brand_title); ?>">
                                                </div>
                                            <?php } ?>
                                            <?php the_title(); ?>
                                        </div>
                                    </td>

                                    <td>₹<?php echo esc_html($price); ?></td>

                                    <td>
                                        <?php
                                        echo '<input type="number" 
                                        class="addon-qty" 
                                        value="' . esc_attr($qty) . '" 
                                        min="0" ' . $max_attr . ' 
                                        data-product_id="' . esc_attr($product_id) . '">';
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo '<label class="switch">
                                            <input type="checkbox" class="addon-all">
                                            <span class="slider round ' . $class . '">' . $max_display . '</span>
                                        </label>';
                                        ?>
                                    </td>

                                    <?php $line_total = $price * $qty; ?>
                                    <td class="addon-total">
                                        ₹<?php echo esc_html($line_total); ?>
                                    </td>

                                </tr>

                                <?php
                                $row_count++;
                            endwhile;

                            wp_reset_postdata();

                        endif;
                        ?>

                    </tbody>
                </table>
            </div>

            <div id="btnss" style="margin-top:20px; text-align:right;">
                <button type="button" id="admin-save-addons" class="button button-primary button-large">Add &
                    Proceed</button>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success($html);
}
/* ---------------------------------------------------------
   ADMIN ADDONS: 2. AJAX to Save Addons to Order
--------------------------------------------------------- */
add_action('wp_ajax_save_admin_order_addons', 'phive_save_admin_order_addons');
function phive_save_admin_order_addons()
{
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Unauthorized');
    }

    $order_id = intval($_POST['order_id']);
    $addons = isset($_POST['addons']) ? $_POST['addons'] : [];
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : [];

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error('Order not found');
    }

    // 1. Process Products (Add / Update / Remove)
    foreach ($addons as $addon) {
        $product_id = intval($addon['id']);
        $new_qty = intval($addon['qty']);

        if (!$product_id)
            continue;

        // Check if item already exists in order
        $found_item_id = false;
        foreach ($order->get_items() as $item_id => $item) {
            if ($item->get_product_id() == $product_id) {
                $found_item_id = $item_id;
                break;
            }
        }

        if ($found_item_id) {
            if ($new_qty > 0) {
                // Update quantity
                $item = $order->get_item($found_item_id);
                $item->set_quantity($new_qty);

                $product = $item->get_product();
                if ($product) {
                    $item->set_subtotal($product->get_price() * $new_qty);
                    $item->set_total($product->get_price() * $new_qty);
                }

                $item->calculate_taxes();
                $item->save();
            } else {
                // Remove item if qty is 0
                $order->remove_item($found_item_id);
            }
        } else {
            if ($new_qty > 0) {
                // Add new item
                $product = wc_get_product($product_id);
                $order->add_product($product, $new_qty);
            }
        }
    }

    // 2. Process Remarks
    foreach ($remarks as $slug => $text) {
        $category = get_term_by('slug', $slug, 'product_cat');
        if ($category) {
            $key = 'Remarks for ' . $category->name;
            if (!empty($text)) {
                $order->update_meta_data($key, sanitize_text_field($text));
            } else {
                // Remove meta if empty
                $order->delete_meta_data($key);
            }
        }
    }

    // 3. Recalculate Totals
    $order->calculate_totals();
    $order->save();

    wp_send_json_success('Order updated successfully.');
}

/* ---------------------------------------------------------
   ADMIN ADDONS: 3. AJAX to Update Member Count Immediately
--------------------------------------------------------- */
add_action('wp_ajax_update_admin_member_count', 'phive_update_admin_member_count');
function phive_update_admin_member_count()
{
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Unauthorized');
    }

    $order_id = intval($_POST['order_id']);
    $count = intval($_POST['count']);

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error('Order not found');
    }

    // 1. Update Order Meta
    $order->update_meta_data('Total Participants', $count);

    // 2. Optional: Sync with Line Items (if your booking logic requires it)
    foreach ($order->get_items() as $item) {
        if ($item->get_product() && $item->get_product()->get_type() === 'phive_booking') {
            $item->update_meta_data('Total Participants', $count);
            $item->save();
        }
    }

    $order->save();
    wp_send_json_success('Count updated');
}


// Code for update Email/Phone with OTP in profle update form

add_action('woocommerce_save_account_details', 'accordhub_save_phone_field');

function accordhub_save_phone_field($user_id)
{

    if (isset($_POST['phone'])) {

        update_user_meta(
            $user_id,
            'phone',
            sanitize_text_field($_POST['phone'])
        );
    }
}


add_action('woocommerce_save_account_details_errors', 'accordhub_gate_profile_update', 10, 2);

function accordhub_gate_profile_update($errors, $user)
{

    $new_email = sanitize_email($_POST['account_email'] ?? '');
    $new_phone = sanitize_text_field($_POST['phone'] ?? '');

    $old_email = $user->user_email;
    $old_phone = get_user_meta($user->ID, 'phone', true);

    if ($new_email !== $old_email && empty($_POST['email_verified'])) {
        $errors->add('email', 'Verify Email OTP');
    }

    if ($new_phone !== $old_phone && empty($_POST['phone_verified'])) {
        $errors->add('phone', 'Verify Phone OTP');
    }

}

add_action('wp_ajax_send_profile_update_otp', 'send_profile_update_otp');

function send_profile_update_otp()
{

    $user = wp_get_current_user();

    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $mode = sanitize_text_field($_POST['mode'] ?? '');

    $otp = rand(100000, 999999);

    $first_name = get_user_meta($user->ID, 'first_name', true);
    $sub = "Accordhub - OTP for Account Update";
    $e_heading = "Here is your OTP for Account Update";

    $msg = "<p>Dear Customer,</p>";
    $msg .= "<p>Your one time password for email address update at Accordhub is</p>";
    $msg .= "<p class='otp_p'>" . $otp . "</p>";
    $msg .= "<p>If you have not requested the OTP, please ignore this email.</p>";

    $components = [
        [
            'type' => 'body',
            'parameters' => [['type' => 'text', 'text' => $otp]] // The OTP
        ],
        [
            'type' => 'button',
            'sub_type' => 'url',
            'index' => '0',
            'parameters' => [['type' => 'text', 'text' => $otp]] // Copy code button
        ]
    ];


    if ($mode == 'combined') {
        set_transient('combined_otp_' . $user->ID, $otp, 3 * MINUTE_IN_SECONDS);
        send_woocommerce_custom_email($email, $sub, $e_heading, $msg);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            $phone = '91' . substr($phone, 1);
        }
        send_accordhub_otp($phone, $otp, 'login');
        send_whatsapp_template_msg($phone, 'login_registration_otp', $components);
    } else {
        if ($email) {
            set_transient('email_otp_' . $user->ID, $otp, 3 * MINUTE_IN_SECONDS);
            send_woocommerce_custom_email($email, $sub, $e_heading, $msg);
        }

        if ($phone) {
            if (strlen($phone) === 10) {
                $phone = '91' . $phone;
            } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
                $phone = '91' . substr($phone, 1);
            }
            set_transient('phone_otp_' . $user->ID, $otp, 3 * MINUTE_IN_SECONDS);
            send_accordhub_otp($phone, $otp, 'login');
            send_whatsapp_template_msg($phone, 'login_registration_otp', $components);
        }
    }

    wp_send_json_success();
}

add_action('wp_ajax_verify_email_otp', 'verify_email_otp');
function verify_email_otp()
{

    $user = wp_get_current_user();
    $otp = sanitize_text_field($_POST['otp']);

    if (get_transient('email_otp_' . $user->ID) == $otp) {
        delete_transient('email_otp_' . $user->ID);
        wp_send_json_success();
    }

    wp_send_json_error();
}

add_action('wp_ajax_verify_phone_otp', 'verify_phone_otp');
function verify_phone_otp()
{

    $user = wp_get_current_user();
    $otp = sanitize_text_field($_POST['otp']);

    if (get_transient('phone_otp_' . $user->ID) == $otp) {
        delete_transient('phone_otp_' . $user->ID);
        wp_send_json_success();
    }

    wp_send_json_error();
}

add_action('wp_ajax_verify_combined_otp', 'verify_combined_otp');
function verify_combined_otp()
{

    $user = wp_get_current_user();
    $otp = sanitize_text_field($_POST['otp']);

    if (get_transient('combined_otp_' . $user->ID) == $otp) {
        delete_transient('combined_otp_' . $user->ID);
        wp_send_json_success();
    }

    wp_send_json_error();
}



/**
 * Shared Logic to generate receipts for Add-ons using the same financial logic as invoices.
 */
function phive_core_process_receipt($billing_order_id)
{
    $invoice_order = wc_get_order($billing_order_id);
    if (!$invoice_order) {
        return ['success' => false, 'message' => 'Billing order not found'];
    }

    $parent_order_id = $invoice_order->get_meta('addons_parent_order_id');
    $parent_order = wc_get_order($parent_order_id);
    if (!$parent_order) {
        return ['success' => false, 'message' => 'Parent booking order not found'];
    }

    // --- A. RE-CALCULATE BASE TOTALS (Mirroring Invoice Logic) ---
    $addons_data = $parent_order->get_meta('_meeting_addons_data');
    $grouped_items = [];
    $base_net_total = 0;
    $base_tax_total = 0;
    $base_gross_total = 0;
    $prices_include_tax = wc_prices_include_tax();

    foreach ($addons_data as $row) {
        // We only include items that were part of this billing cycle (marked as billed)
        if (!isset($row['billed_status']) || $row['billed_status'] !== 'billed') {
            continue;
        }

        $product_id = intval($row['product_id']);
        $qty = intval($row['qty']);
        $stored_price = floatval($row['price']);
        $product_name = $row['product_name'];
        $product = wc_get_product($product_id);

        if ($product) {
            if ($prices_include_tax) {
                $line_gross = $stored_price * $qty;
                $line_net = $line_gross / 1.18;
                $line_tax = $line_gross - $line_net;
            } else {
                $line_net = $stored_price * $qty;
                $line_tax = $line_net * 0.18;
                $line_gross = $line_net + $line_tax;
            }
        } else {
            if ($prices_include_tax) {
                $line_gross = $stored_price * $qty;
                $line_net = $line_gross / 1.18;
                $line_tax = $line_gross - $line_net;
            } else {
                $line_net = $stored_price * $qty;
                $line_tax = $line_net * 0.18;
                $line_gross = $line_net + $line_tax;
            }
        }

        $base_net_total += $line_net;
        $base_tax_total += $line_tax;
        $base_gross_total += $line_gross;

        $display_row_total = $prices_include_tax ? $line_gross : $line_net;

        if (isset($grouped_items[$product_name])) {
            $grouped_items[$product_name]['qty'] += $qty;
            $grouped_items[$product_name]['display_total'] += $display_row_total;
        } else {
            $grouped_items[$product_name] = [
                'name' => $product_name,
                'qty' => $qty,
                'display_total' => $display_row_total
            ];
        }
    }

    // --- B. APPLY DISCOUNT & SCALING (Mirroring Invoice Logic) ---
    $saved_coupon_code = $parent_order->get_meta('_meeting_addon_coupon');
    $discount_amount = 0;
    $coupon = !empty($saved_coupon_code) ? new WC_Coupon($saved_coupon_code) : null;

    if ($coupon && $coupon->get_id()) {
        $discount_amount = ($coupon->get_discount_type() === 'percent')
            ? $base_net_total * ($coupon->get_amount() / 100)
            : $coupon->get_amount();
    }

    if ($discount_amount > $base_net_total)
        $discount_amount = $base_net_total;

    $ratio = ($base_net_total > 0) ? ($base_net_total - $discount_amount) / $base_net_total : 1;
    $final_net_subtotal = $base_net_total - $discount_amount;
    $final_tax_total = $base_tax_total * $ratio;
    $final_gross_total = $final_net_subtotal + $final_tax_total;

    // --- C. PREPARE TEMPLATE DATA ---
    $payment_mode = $parent_order->get_meta('group_payment_mode');
    $share_amount = $invoice_order->get_total(); // The amount this specific person paid

    $template_args = [
        'parent_order' => $parent_order,
        'billing_order' => $invoice_order,
        'items' => array_values($grouped_items),
        'prices_include_tax' => $prices_include_tax,
        'base_net_subtotal' => $base_net_total,
        'base_gross_total' => $base_gross_total,
        'discount' => $discount_amount,
        'coupon_code' => ($coupon && $coupon->get_id()) ? $coupon->get_amount() : 0,
        'final_tax' => $final_tax_total,
        'final_total' => $final_gross_total,
        'share_amount' => $share_amount
    ];

    $template_file = ($payment_mode === 'group') ? 'emails/admin-addons-receipt-split.php' : 'emails/admin-addons-receipt.php';

    // --- D. GENERATE PDF ---
    if (!class_exists('Dompdf\Dompdf') && file_exists(get_stylesheet_directory() . '/libs/dompdf/autoload.inc.php')) {
        require_once get_stylesheet_directory() . '/libs/dompdf/autoload.inc.php';
    }

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);

    ob_start();
    wc_get_template($template_file, $template_args);
    $receipt_html = ob_get_clean();

    $dompdf->loadHtml($receipt_html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Add custom footer text as seen in invoice logic
    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->get_font("Inter", "normal");
    $canvas->page_text(200, 810, "Thank you for your payment.", $font, 10, array(0, 0, 0));

    $upload_dir = wp_upload_dir();
    $pdf_path = $upload_dir['basedir'] . '/receipt_' . $parent_order_id . '_' . $billing_order_id . '.pdf';
    $pdf_path_url = $upload_dir['baseurl'] . '/receipt_' . $parent_order_id . '_' . $billing_order_id . '.pdf';
    file_put_contents($pdf_path, $dompdf->output());

    // --- E. SEND EMAIL ---
    $subject = 'Payment Receipt for Add-on Services - #' . $parent_order_id;
    //$msg = "Hello " . $invoice_order->get_billing_first_name() . ",<br>Thank you for your payment. Please find your official receipt attached for the add-on services.";
    $msg = "Dear Customer,<br>Thank you for your payment. Please find the receipt attached for the add-on services requested during the meeting.";

    if (function_exists('send_woocommerce_custom_email')) {
        send_woocommerce_custom_email($invoice_order->get_billing_email(), $subject, $subject, $msg, $pdf_path);
    }

    $customer_name = $invoice_order->get_billing_first_name() . ' ' . $invoice_order->get_billing_last_name();
    $customer_phone = $invoice_order->get_billing_phone();
    $components = [
        // Header Component (The PDF Receipt)
        [
            'type' => 'header',
            'parameters' => [
                [
                    'type' => 'document',
                    'document' => [
                        // Ensure you use the public URL as discussed earlier
                        'link' => $pdf_path_url,
                        'filename' => 'receipt_' . $parent_order_id . '_' . $billing_order_id . '.pdf'
                    ]
                ]
            ]
        ],
        // Body Component (The Customer Name)
        [
            'type' => 'body',
            'parameters' => [
                [
                    'type' => 'text',
                    'text' => $customer_name // Variable {{1}} in template
                ]
            ]
        ]
    ];
    if (strlen($customer_phone) === 10) {
        $customer_phone = '91' . $customer_phone;
    } elseif (strlen($customer_phone) === 11 && substr($customer_phone, 0, 1) === '0') {
        $customer_phone = '91' . substr($customer_phone, 1);
    }
    // Call your common function
    send_whatsapp_template_msg($customer_phone, 'live_addons_receipt_payment_completed', $components);
    return ['success' => true, 'message' => 'Receipt generated and sent.'];
}

/**
 * Hook into WooCommerce payment completion to trigger the receipt
 */
add_action('woocommerce_payment_complete', 'phive_trigger_addon_receipt_on_payment');
function phive_trigger_addon_receipt_on_payment($order_id)
{
    $order = wc_get_order($order_id);
    if ($order->get_meta('addons_parent_order_id')) {
        phive_core_process_receipt($order_id);
    }
}



/*add_action('woocommerce_new_order', function ($parent_order_id) {
    $parent_order = wc_get_order($parent_order_id);
    if (!$parent_order)
        return;

    $payment_mode = $parent_order->get_meta('group_payment_mode');
    if ($payment_mode !== 'group')
        return;

    $additional_payers = $parent_order->get_meta('group_additional_payers');
    if (empty($additional_payers) || !is_array($additional_payers))
        return;

    // Prevent duplicate child orders creation
    if ($parent_order->get_meta('_group_child_orders_created'))
        return;

    $total_original = $parent_order->get_meta('group_original_total');
    if (!$total_original)
        $total_original = $parent_order->get_total();

    $total_payers = $parent_order->get_meta('group_total_payers') ?: 2;

    // Calculate share per payer
    $share_amount = round($total_original / $total_payers, wc_get_price_decimals());

    foreach ($additional_payers as $index => $payer) {
        // Skip if child order already exists
        if (!empty($payer['child_order_id']))
            continue;

        $child_order = wc_create_order();
        if (!$child_order)
            continue;

        // Assign customer if exists
        $user = get_user_by('email', $payer['email']);
        $child_order->set_customer_id($user ? $user->ID : 0);

        // Copy products from parent
        foreach ($parent_order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $variation_id = $item->get_variation_id();
            $quantity = $item->get_quantity();

            if ($variation_id > 0) {
                $child_order->add_product(wc_get_product($variation_id), $quantity);
            } else {
                $child_order->add_product(wc_get_product($product_id), $quantity);
            }
        }

        // Set billing and total
        $child_order->set_total($share_amount);
        $child_order->set_billing_first_name(sanitize_text_field($payer['name']));
        $child_order->set_billing_email(sanitize_email($payer['email']));
        $child_order->set_billing_phone(sanitize_text_field($payer['phone'] ?? ''));
        $child_order->set_billing_company(sanitize_text_field($payer['company'] ?? ''));
        $child_order->save();

        // Group meta
        $child_order->update_meta_data('group_parent_order', $parent_order->get_id());
        $child_order->update_meta_data('group_payer_index', $index);
        $child_order->update_meta_data('group_payer_name', sanitize_text_field($payer['name']));
        $child_order->update_meta_data('group_payer_email', sanitize_email($payer['email']));
        $child_order->update_meta_data('group_payer_phone', sanitize_text_field($payer['phone'] ?? ''));
        $child_order->update_meta_data('group_payer_company', sanitize_text_field($payer['company'] ?? ''));
        $child_order->save();

        // Save child order ID in parent meta
        $additional_payers[$index]['child_order_id'] = $child_order->get_id();

        // Send checkout email
        $pay_url = $child_order->get_checkout_payment_url();
        $subject = "Accordhub - Your Room Booking Payment Share is Pending";
        $email_heading = "Complete Your Share of Room Payment to Confirm the Room Booking";
        $message = "<p>Dear " . sanitize_text_field($payer['name']) . ",</p>";
        $message .= "<p>You’ve been added as a participant in a split payment for an upcoming room booking at Accordhub. Please complete payment for your share of <strong>" . wc_price($share_amount) . "</strong>.</p>";
        $message .= "<p class='btn_p'><a href='{$pay_url}'>Click here to pay</a></p>";
        $message .= "<p>Kindly make the payment as early as possible to avoid booking cancellation.</p>";
        //wp_mail($payer['email'], $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
        send_woocommerce_custom_email($payer['email'], $subject, $email_heading, $message);
        $url_parts = explode('/order-pay/', $pay_url);
        $button_suffix = end($url_parts);

        $components = [
            // Body Component: 2 Variables (Name and Share Amount)
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $payer['name'] // {{1}} Name
                    ],
                    [
                        'type' => 'text',
                        'text' => $share_amount // {{2}} Amount to pay
                    ]
                ]
            ],
            // Button Component: Dynamic URL Suffix
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => 0,
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $button_suffix // e.g., 34735/?pay_for_order=true&key=...
                    ]
                ]
            ]
        ];

        // Send to the secondary party
        send_whatsapp_template_msg($payer['phone'], 'payment_other_parties_request_completion', $components);
    }

    // Save updated additional payers and mark child orders as created
    $parent_order->update_meta_data('group_additional_payers', $additional_payers);
    $parent_order->update_meta_data('_group_child_orders_created', 1);
    $parent_order->set_total($share_amount);
    $parent_order->save();

}, 10, 1);*/

add_filter('woocommerce_coupon_message', 'modify_woocommerce_coupon_message', 10, 3);
function modify_woocommerce_coupon_message($msg, $msg_code, $coupon)
{

    // Get coupon code safely
    $coupon_code = is_a($coupon, 'WC_Coupon')
        ? $coupon->get_code()
        : $coupon;

    $coupon_code_upper = strtoupper($coupon_code);

    // Success message code = 200
    if ($msg_code == 200) {
        $msg = sprintf(
            __('Coupon code "%s" applied successfully.', 'woocommerce'),
            $coupon_code_upper
        );
    }

    // Replace lowercase coupon in message
    if (strpos($msg, $coupon_code) !== false) {
        $msg = str_replace($coupon_code, $coupon_code_upper, $msg);
    }

    return $msg;
}
add_filter('woocommerce_coupon_error', 'modify_woocommerce_coupon_error', 10, 3);
function modify_woocommerce_coupon_error($msg, $msg_code, $coupon)
{

    // Get coupon code safely
    $coupon_code = is_a($coupon, 'WC_Coupon')
        ? $coupon->get_code()
        : $coupon;

    $coupon_code_upper = strtoupper($coupon_code);

    // Success message code = 200
    if ($msg_code == 200) {
        $msg = sprintf(
            __('Coupon code "%s" applied successfully.', 'woocommerce'),
            $coupon_code_upper
        );
    }

    // Replace lowercase coupon in message
    if (strpos($msg, $coupon_code) !== false) {
        $msg = str_replace($coupon_code, $coupon_code_upper, $msg);
    }

    return $msg;
}

// 1. Add the Code and Percentage to the Left Label
add_filter('woocommerce_cart_totals_coupon_label', 'custom_coupon_label_with_percentage', 10, 2);
function custom_coupon_label_with_percentage($label, $coupon)
{
    $code = strtoupper($coupon->get_code());
    $percentage = $coupon->is_type('percent') ? ' ' . $coupon->get_amount() . '% discount' : '';

    // This creates the "Coupon: ACCRD20 (20%)" block
    return 'Coupon: ' . $code . ' <br> ' . $percentage;
}

// 2. Clean up the Right Side (Remove the redundant lowercase code)
add_filter('woocommerce_cart_totals_coupon_html', 'custom_coupon_html_clean_value', 10, 3);
function custom_coupon_html_clean_value($coupon_html, $coupon, $discount_amount_html)
{
    // We return ONLY the discount amount (the price), removing the text prefix
    return $discount_amount_html;
}



/**
 * Send a WhatsApp Message using a Template
 *
 * @param string $to The recipient phone number (e.g., '918197595073')
 * @param string $template_name The name of the WhatsApp template
 * @param array  $components The components (body/header parameters) for the template
 * @return array|WP_Error Response from the API
 */
function send_whatsapp_template_msg($to, $template_name, $components = [])
{
    $access_token = 'EAAKM9OBeuZAQBQ6TrNxZAxAIzPQC4iP8wrZCLTLjdPtnXQ3DpDUNt2KT2tSEre2bEeCLgNoOyIi59TVRYMrLZB8GVZB1emNhMv3AJ0lm0CNdLS14p7ZBisZBOIDkZAtWxR6vuDyZA0FxIijEvteK3e66cGET8Qz3IZBIUpUMNn1dZCR1SguOmYwrLcLqNTZAxmBEvgZDZD';
    $phone_number_id = '1043203775534194'; // From your Postman collection
    $api_url = "https://graph.facebook.com/v24.0/{$phone_number_id}/messages";

    $payload = [
        'messaging_product' => 'whatsapp',
        'to' => $to,
        'type' => 'template',
        'template' => [
            'name' => $template_name,
            'language' => ['code' => 'en'],
            'components' => $components
        ]
    ];

    $response = wp_remote_post($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode($payload),
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) {
        custom_log('WhatsApp API Error: ' . $response->get_error_message());
    }

    return $response;
}


add_action('template_redirect', 'restrict_pay_for_order_access');

function restrict_pay_for_order_access()
{
    if (is_checkout() && !empty(is_wc_endpoint_url('order-pay'))) {

        $order_id = absint(get_query_var('order-pay'));
        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            $current_user_id = get_current_user_id();
            $order_customer_id = $order->get_customer_id();

            // 1. If user is logged in, check if they own the order
            if ($order_customer_id > 0 || $order_customer_id === 0) {
                if ($current_user_id !== $order_customer_id) {
                    wc_clear_notices();
                    wc_add_notice('It looks like you’re not logged in with the account used to create this booking. Please log in using the registered mobile number or email address to proceed with the payment.', 'error');
                    add_filter('woocommerce_add_error', '__return_false', 9999);
                    //wp_safe_redirect(wc_get_page_permalink('myaccount'));
                    //exit;
                }
            }
            // 2. If guest, you can optionally verify by session or billing email
            // Most gateways handle this, but for extra security:
            elseif (!is_user_logged_in()) {
                // Optional: Force login or redirect if you want to ban guest payments entirely
            }
        }
    }
}


// 1. Add Meta Box
add_action('add_meta_boxes', 'add_case_details_meta_box');
function add_case_details_meta_box()
{
    $screens = ['shop_order', 'woocommerce_page_wc-orders'];
    foreach ($screens as $screen) {
        add_meta_box(
            'case_details_box',
            'Case Details',
            'render_case_details_box',
            $screen,
            'normal',
            'high'
        );
    }
}

// 2. Render Meta Box HTML
function render_case_details_box($post)
{
    if ($post instanceof WC_Order) {
        $order_id = $post->get_id();
    } else {
        $order_id = $post->ID;
    }
    $order = wc_get_order($order_id);
    $orig_order = wc_get_order($order_id);

    $ord_meta = '';
    if ($orig_order && is_object($orig_order)) {
        if ($orig_order->get_meta('group_parent_order')) {
            $ord_meta = $orig_order->get_meta('group_parent_order');
        }
    }

    echo '<div class="case-box">';
    echo '<div class="d-flex">';
    echo '<h3>Case Details</h3>';
    if (!$ord_meta) {
        echo '<button type="button" class="button woocommerce-button edit-case-details button-primary" data-order-id="' . esc_attr($order_id) . '">Update</button>';
    }
    echo '</div>';
    // Get custom order meta values
    $case_name = $order->get_meta('Case Title');
    $case_id = $order->get_meta('Case ID');
    $case_desc = $order->get_meta('Case Description');
    $total = $order->get_meta('Total Participants');
    $total_parties = $order->get_meta('Total Parties Involved');
    $parties = $order->get_meta('Parties Involved'); // structured array
    $arbitrators = $order->get_meta('Arbitrators');  // array
    $other_rem = $order->get_meta('Other Remarks');

    // Try to get from current order items if missing
    if (empty($case_name) || empty($case_id) || empty($case_desc) || empty($total) || empty($total_parties) || empty($parties) || empty($arbitrators)) {
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            if ($product && $product->get_type() === 'phive_booking') {
                $case_name = $case_name ?: $item->get_meta('Case Title');
                $case_id = $case_id ?: $item->get_meta('Case ID');
                $case_desc = $case_desc ?: $item->get_meta('Case Description');
                $total = $total ?: $item->get_meta('Total Participants');
                $total_parties = $total_parties ?: $item->get_meta('Total Parties Involved');
                $parties = $parties ?: $item->get_meta('Parties Involved');
                $arbitrators = $arbitrators ?: $item->get_meta('Arbitrators');
                $other_rem = $other_rem ?: $item->get_meta('Other Remarks');
                break;
            }
        }
    }

    // Try to get from parent order items if still missing
    if (empty($case_name) || empty($case_id) || empty($case_desc) || empty($total) || empty($total_parties) || empty($parties) || empty($arbitrators)) {
        $parent_id = $order->get_meta('group_parent_order');
        if ($parent_id) {
            $p_ord = wc_get_order($parent_id);
            if ($p_ord) {
                foreach ($p_ord->get_items() as $item_id => $item) {
                    $product = $item->get_product();
                    if ($product && $product->get_type() === 'phive_booking') {
                        $case_name = $case_name ?: $item->get_meta('Case Title');
                        $case_id = $case_id ?: $item->get_meta('Case ID');
                        $case_desc = $case_desc ?: $item->get_meta('Case Description');
                        $total = $total ?: $item->get_meta('Total Participants');
                        $total_parties = $total_parties ?: $item->get_meta('Total Parties Involved');
                        $parties = $parties ?: $item->get_meta('Parties Involved');
                        $arbitrators = $arbitrators ?: $item->get_meta('Arbitrators');
                        $other_rem = $other_rem ?: $item->get_meta('Other Remarks');
                        break;
                    }
                }
            }
        }
    }


    if (is_admin()) { ?>
        <style>
            #order_data .order_data_column {
                width: 100%;
            }

            #order_data table.shop_table.my_account_orders.wc-case-details {
                width: 100%;
                text-align: left;
            }

            div#order_data button.button.woocommerce-button.edit-case-details {
                display: none;
            }

            #order_data h3 {
                display: inline-block;
            }
        </style>
    <?php }


    echo '<div class="cd_box">';

    // 1. Case Title (Only if set)
    if (!empty($case_name)) {
        echo '<div class="cd_box_itm">
                <label>Case Title: </label>
                <div>' . esc_html($case_name) . '</div>
            </div>';
    } else {
        // echo '<div class="cd_box_itm">
        //         <label>Case Title: </label>
        //         <div>-</div>
        //     </div>';
    }

    // 2. Case ID
    if (!empty($case_id)) {
        echo '<div class="cd_box_itm">
                <label>Case ID: </label>
                <div>' . esc_html($case_id) . '</div>
            </div>';
    } else {
        // echo '<div class="cd_box_itm">
        //         <label>Case ID: </label>
        //         <div>-</div>
        //     </div>';
    }

    // 5. Total Members
    if (!empty($total)) {
        echo '<div class="cd_box_itm">
                <label>Total Members: </label>
                <div>' . esc_html($total) . '</div>
            </div>';
    } else {
        // echo '<div class="cd_box_itm">
        //         <label>Total Members: </label>
        //         <div>-</div>
        //     </div>';
    }

    // 6. No of Parties
    /*if (!empty($total_parties)) {
        echo '<div class="cd_box_itm">
                    <div><label>No of Parties: </label>' . esc_html($total_parties) . '</div>
                </div>';
    }*/

    // 3. Case Description
    if (!empty($case_desc)) {
        echo '<div class="cd_box_itm cd_box_itm_fw">
                    <label>Case Description: </label>
                    <div>' . nl2br(esc_html($case_desc)) . '</div>
                </div>';
    } else {
        // echo '<div class="cd_box_itm cd_box_itm_fw">
        //             <label>Case Description: </label>
        //             <div>-</div>
        //         </div>';
    }

    // 7. Other Remarks
    if (!empty($other_rem)) {
        echo '<div class="cd_box_itm cd_box_itm_fw">
                    <label>Other Remarks: </label>
                    <div>' . esc_html($other_rem) . '</div>
                </div>';
    } else {
        // echo '<div class="cd_box_itm cd_box_itm_fw">
        //             <label>Other Remarks: </label>
        //             <div>-</div>
        //         </div>';
    }


    echo '</div>';

    // 8. Parties Details (The logic fix for your screenshot)
    if (!empty($parties) && is_array($parties)) {
        echo '<h3>Parties Details</h3>';

        // 4. Arbitrators (Filter empty values)
        $filtered_arbitrators = !empty($arbitrators) && is_array($arbitrators) ? array_filter($arbitrators) : [];
        if (!empty($filtered_arbitrators)) {
            echo '<div class="cd_box_itm">
                        <div><label>Arbitrators: </label>' . esc_html(implode(', ', $filtered_arbitrators)) . '</div>
                    </div>';
        } else {
            echo '<div class="cd_box_itm">
                        <div><label>Arbitrators: </label>-</div>
                    </div>';
        }

        echo '<table class="wp-list-table fixed table-view-list widefat">';
        echo '<tr>
                    <th>Party Name</th>
                    <th>Name</th>
                    <th>Whatsapp No</th>
                    <th>Role</th>
                </tr>';
        // Buffer the parties content first to see if we have anything to show
        $parties_html = '';



        foreach ($parties as $index => $party) {

            $has_name = !empty($party['name']);

            // Check if any Members exist (filter out empty names)
            $valid_members = [];
            if (!empty($party['members']) && is_array($party['members'])) {
                foreach ($party['members'] as $member) {
                    if (!empty($member['name'])) {
                        $valid_members[] = $member;
                    }
                }
            }
            $has_members = !empty($valid_members);

            // If NO name and NO members, skip this party entirely (fixes the empty "Party 1" issue)
            if (!$has_name && !$has_members) {
                continue;
            }
            // --- LOGIC CHECK END ---

            // If we are here, there is data to show. Add to buffer.
            //$parties_html .= '<strong>Party ' . ($index + 1) . '</strong><br>';




            if ($has_members) {
                $legal_counsels = [];
                $company_reps = [];

                //print_r($valid_members);
                $i = 0;
                foreach ($valid_members as $member) {
                    $parties_html .= '<tr>';

                    $parties_html .= '<td>';
                    if ($has_name && $i != 1) {
                        $parties_html .= esc_html($party['name']);
                    } else {
                        $parties_html .= '';
                    }
                    $parties_html .= '</td>';

                    $parties_html .= '<td>';
                    if ($member['name']) {
                        $parties_html .= esc_html($member['name']);
                    }
                    $parties_html .= '</td>';

                    $parties_html .= '<td>';
                    if ($member['wa']) {
                        $parties_html .= esc_html($member['wa']);
                    }
                    $parties_html .= '</td>';

                    $parties_html .= '<td>';
                    if ($member['role'] === 'legal_counsel') {
                        $parties_html .= 'Legal Counsel';
                    } else {
                        $parties_html .= 'Company Representatives';
                    }
                    $parties_html .= '</td>';

                    $parties_html .= '</tr>';
                    $i++;
                }

            }

        }

        // Only echo the row if we actually created HTML for at least one party
        if (!empty($parties_html)) {
            echo $parties_html;
        }

        echo '</table>';
    }



    // Popup container
    // echo '<div id="case-edit-popup-' . esc_attr($order_id) . '" class="case-edit-popup" style="display:none;">';
    //     show_case_details_form_on_thankyou($order_id);
    // echo '</div>';


    ?>

    <style>
        .cd_box_itm div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cd_box_itm.cd_box_itm_fw {
            width: 50%;
        }

        .cd_box+h3 {
            margin-bottom: 0;
        }

        #case_details_box .wp-list-table {
            border: none;
            border-collapse: collapse;
        }

        #case_details_box .wp-list-table th,
        #case_details_box .wp-list-table td {
            padding: 10px;
            line-height: 1.2;
            border: 1px solid #eaeaea;
            cursor: auto;
        }

        .cd_box {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -5px;
        }

        .cd_box_itm {
            width: 33.333%;
            padding: 5px;
            box-sizing: border-box;
        }

        .cd_box_itm label,
        #case_details_box th {
            font-weight: 600;
        }

        .case-box .d-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0 5px;
        }

        .case-box .d-flex h3 {
            margin: 0;
        }

        body .woocommerce-case-form {
            max-width: 712.80px;
        }

        .case-edit-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .case-edit-popup .woocommerce-case-form {
            background: #fff;
            padding: 20px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 8px;
            width: 100%;
            position: relative;
        }

        .close-icn {
            background-image: linear-gradient(90deg, #1763B9 45%, #2F87ED 100%) !important;
        }

        .close-icn {
            position: absolute;
            right: 15px;
            top: 15px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 50%;
        }

        body .close-icn {
            right: 11.88px;
            top: 11.88px;
            width: 26.93px;
            height: 26.93px;
        }

        body .close-icn svg {
            width: 15.84px;
            height: 15.84px;
        }

        .case-edit-popup h2 {
            font-family: "Inter", Sans-serif;
            font-size: 2rem;
            font-weight: 600;
            text-transform: capitalize;
            line-height: 120%;
            letter-spacing: 0px;
            color: #191919;
            margin-bottom: 5px !important;
            text-align: center;
        }

        .case-edit-popup .woocommerce-case-form>p {
            font-family: "Inter", Sans-serif;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.6em;
            letter-spacing: 0.29px;
            color: #2E2E2E;
            margin-block-end: .9rem;
            margin-block-start: 0;
        }

        #case-details-form {
            padding: 15.84px;
            border-radius: 12px;
            border: 1px solid #D4D7E3;
        }

        #case-details-form .form-row {

            padding: 2.38px 0;
            margin-block-end: .9rem;
            margin-block-start: 0;
        }

        #case-details-form .form-row.form-row-first {
            float: left;
            width: 49%;
            overflow: visible;
        }

        #case-details-form .form-row.form-row-last {
            float: right;
            width: 49%;
            overflow: visible;
        }

        #case-details-form .form-row::after,
        #case-details-form .form-row::after {
            clear: both;
        }

        #case-details-form .form-row::after,
        #case-details-form .form-row::before,
        #case-details-form .form-row::after,
        #case-details-form .form-row::before {
            content: " ";
            display: table;
        }

        #case-details-form .form-row.form-row-wide {
            clear: both;
        }

        #case-details-form p label {
            font-family: "Inter", Sans-serif;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 2%;
            color: #2e2e2e;
            line-height: 2;
            display: block;
        }

        form#case-details-form input:not([type="radio"]),
        form#case-details-form textarea {
            height: 48px;
            background-color: #02010100;
            border-color: #D4D7E3;
            border-width: 1px 1px 1px 1px;
            border-radius: 12px 12px 12px 12px;
            padding: 8px 14px;
            font-family: "Inter", Sans-serif;
            font-size: 14px;
            font-weight: 400;
            height: 38.02px;
            border-radius: 9.50px 9.50px 9.50px 9.50px;
            padding: 6.34px 11.09px;
        }

        body .case-edit-popup form#case-details-form textarea[name="case_description"],
        body .case-edit-popup form#case-details-form textarea[name="other_remarks"] {
            height: 85.54px;
            padding: 11.09px;
        }

        .case-edit-popup form#case-details-form textarea[name="case_title"] {
            resize: none;
            border-radius: 12px !important;
            border: 1px solid #D4D7E3 !important;
            background-color: transparent !important;
            height: 38.02px;
            padding: 8px 11.09px 6.34px;
            font-size: 14px;
            font-family: "Inter", Sans-serif;
            font-weight: 400;
            overflow: hidden;
        }

        #case-details-form #parties-wrapper {
            padding: 15.84px;
            margin: 11.88px 0 7.92px;
            border: 1px solid #D4D7E3;
            border-radius: 12px;
            font-size: 14px;
            color: #69727d;
        }

        body .party-section:not(:last-child) {
            margin-bottom: 7.92px;
        }

        body .case-edit-popup form#case-details-form #parties-wrapper h4 {
            font-family: "Inter", Sans-serif;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 2%;
            color: #2e2e2e;
            line-height: 2;
            font-style: normal;
            margin-bottom: 0;
            margin-top: 0;
        }

        .party-section>div {
            display: grid;
            grid-template-columns: 1fr;
            justify-content: space-between;
            gap: 4px;
        }

        .party-section>div>div {
            flex: 1;
            margin-bottom: 0 !important;
        }

        body .case-edit-popup form#case-details-form #parties-wrapper .member-input {
            margin-top: 7.92px;
        }

        .party-section .member-input:not(:last-child) {
            margin-bottom: 10px;
        }

        body .party-section .member-input {
            gap: 3.96px;
        }

        .member-input.member-wa-row .member-wa-box {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 2%;
            justify-content: space-between;
        }

        .case-edit-popup form#case-details-form #parties-wrapper .member-input label {
            line-height: 1.1;
            flex-direction: row;
            width: 118.80px;
            text-align: left;
            justify-content: start;
            font-size: 14px;
            min-width: max-content;
            max-width: max-content;
        }

        .party-section .member-input {
            display: flex;
            gap: 3.96px;
            justify-content: space-between;
        }

        .member-input.member-wa-row {
            flex-wrap: wrap;
        }

        .party-section .member-input button,
        .arbitrator-input button {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 300;
            padding: 7px;
            font-size: 20px;
            min-width: 30px;
            line-height: 0;
        }

        body .party-section .member-input button,
        body .arbitrator-input button {
            padding: 5.54px;
            font-size: 15.84px;
            min-width: 23.76px;
        }

        .party-section .member-input:not(:last-child) button.add-member,
        .party-section .arbitrator-input:not(:last-child) button.add-arbitrator {
            opacity: .5;
            pointer-events: none;
        }

        .case-edit-popup form#case-details-form #parties-wrapper .party-section .member-input button,
        .case-edit-popup form#case-details-form .arbitrator-input button {
            width: 30px;
            height: 30px;
            border: none;
            color: #242424;
            transition: none;
            background-color: transparent;
            background-image: linear-gradient(180deg, #F2F8FF 0%, #F2F8FF 100%);
            padding: 0;
            justify-content: center;
            align-items: center;
            line-height: 1px;
            text-align: center;
            margin-top: 8px;
            letter-spacing: -.2px;
            border-radius: 38px 38px 38px 38px;
        }

        body .case-edit-popup form#case-details-form #parties-wrapper .party-section .member-input button,
        body .case-edit-popup form#case-details-form .arbitrator-input button {
            width: 23.76px;
            height: 23.76px;
            margin-top: 6.34px;
        }

        .case-edit-popup form#case-details-form #parties-wrapper .party-section .member-input button:hover,
        .case-edit-popup form#case-details-form .arbitrator-input button:hover {
            background-image: linear-gradient(180deg, #2F87ED 0%, #2F87ED 100%);
            color: #fff;
        }

        .case-edit-popup form#case-details-form label {
            font-family: "Inter", Sans-serif;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 2%;
            color: #2e2e2e;
            line-height: 2;
        }

        body .case-edit-popup form#case-details-form input[type="radio"] {
            width: 10.296px;
        }

        .party-section .member-input>label {
            width: max-content;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-left: 5px;
            margin-right: 5px;
        }

        .arbitrator-input {
            display: flex;
            gap: 5px;
            margin-bottom: 12px;
        }

        body .arbitrator-input {
            display: flex;
            gap: 3.96px;
            margin-bottom: 9.50px;
        }

        #case-details-form .button,
        .case-box form#case-details-form button.woocommerce-button {
            color: #242424 !important;
            transition: none;
            height: 48px;
            background-color: transparent;
            background-image: linear-gradient(180deg, #F2F8FF 0%, #F2F8FF 100%);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.2px;
            text-transform: none;
            padding: 10px 20px;
            width: 162px;
            max-width: 100%;
            font-family: "Inter", Sans-serif;
            border: none;
            outline: 0;
        }

        #case-details-form .button:hover,
        .case-box form#case-details-form button.woocommerce-button:hover {
            background-image: linear-gradient(180deg, #2F87ED 0%, #2F87ED 100%);
            color: #fff !important;
        }

        .form-row.form-row-wide.btn-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
    </style>

    <script>
        document.addEventListener("click", function (e) {
            if (e.target.classList.contains("edit-case-details")) {
                const orderId = e.target.dataset.orderId;
                document.getElementById("case-edit-popup-" + orderId).style.display = "flex";
            }
        });

        /*jQuery(document).ready(function ($) {
            $("#close-popup, .close-popup").click(function () {
                $(".case-edit-popup").hide();
            });
        });*/

    </script>
    <?php
    echo '</div>';

}


add_action('admin_footer', 'add_case_popup_in_admin_footer');

function add_case_popup_in_admin_footer()
{

    global $pagenow;

    // Only run on order edit page
    if ($pagenow !== 'post.php' && $pagenow !== 'admin.php') {
        return;
    }

    $screen = get_current_screen();

    if (
        !$screen ||
        ($screen->id !== 'shop_order' &&
            $screen->id !== 'woocommerce_page_wc-orders')
    ) {
        return;
    }

    // Get order ID safely
    $order_id = 0;

    if (isset($_GET['post'])) {
        $order_id = absint($_GET['post']);
    }

    if (isset($_GET['id'])) { // HPOS
        $order_id = absint($_GET['id']);
    }

    if (!$order_id) {
        return;
    }

    echo '<div id="case-edit-popup-' . esc_attr($order_id) . '" class="case-edit-popup" style="display:none;">';
    show_case_details_form_on_thankyou($order_id);
    echo '</div>';

}
add_filter('manage_woocommerce_page_wc-orders_columns', function ($columns) {

    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        if ($key === 'order_status') {
            $new_columns['order_source'] = 'Source';
        }

        if ($key === 'order_date') {
            $new_columns['order_date'] = 'Created On';
        }

        if ($key === 'order_status') {
            $new_columns['order_status'] = 'Booking Status';
        }
    }

    return $new_columns;
});
add_action('manage_woocommerce_page_wc-orders_custom_column', function ($column, $order) {

    if ($column === 'order_source') {

        $created_via = $order->get_created_via();

        if ($created_via === '') {
            echo 'Admin';
        } elseif ($created_via === 'checkout') {
            echo 'Website';
        } else {
            echo ucfirst($created_via ?: 'Unknown');
        }
    }

}, 10, 2);


add_filter('woocommerce_payment_complete_order_status', 'phive_custom_payment_complete_status', 10, 3);
function phive_custom_payment_complete_status($status, $order_id, $order)
{
    $parent_id = $order->get_meta('group_parent_order');
    $additional_payers = $order->get_meta('group_additional_payers');

    if (!$parent_id && empty($additional_payers)) {
        return 'completed';
    }

    return $status;
}

add_action('woocommerce_admin_order_data_after_order_details', 'phive_display_cancellation_meta_in_order_admin');

function phive_display_cancellation_meta_in_order_admin($order)
{
    // 1. Fetch the meta data from the order 
    $cancelled_by = $order->get_meta('_cancelled_by_user_name');
    $cancellation_reason = $order->get_meta('_cancellation_reason');
    $created_via = $order->get_created_via();
    $created_on = $order->get_date_created();
    $cancel_time = $order->get_meta('phive_cancellation_requested_at');
    $payment = $order->get_meta('group_payment_mode');
    if ($payment === 'group') {
        $payment_mode = 'Split Payment';
    } else {
        $payment_mode = 'Full Payment';
    }
    echo $created_via;

    if ($created_via === '') {
        $source = 'Admin';
    } elseif ($created_via === 'checkout') {
        $source = 'Website';
    } else {
        $source = ucfirst($created_via ?: 'Unknown');
    }

    ?>
    <?php

    if (!empty($created_on)) {
        echo '<p style="color:#000000;font-size: 14px;order:2" class="form-field form-field-wide">';
        echo '<strong>Created on:</strong> ' . esc_html($created_on->date('d-m-Y')) . '<a class="edit_date" href="#"></a>';
        echo '</p>';
    }
    // 2. Output the data if it exists
    if (!empty($cancelled_by) || !empty($cancellation_reason) || !empty($source) || !empty($cancel_time) || !empty($payment_mode)) {

        if (!empty($source)) {
            echo '<p style="color:#000000;font-size: 14px;order:4;" class="form-field form-field-wide">';
            echo '<strong>Creation Source:</strong> ' . esc_html($source);
            echo '</p>';
        }

        if (!empty($cancel_time)) {
            echo '<p style="color:#000000;font-size: 14px;order:5;" class="form-field form-field-wide">';
            echo '<strong>Cancelled on:</strong> ' . date('d-m-Y H:i', $cancel_time);
            echo '</p>';
        }

        if (!empty($cancelled_by)) {
            echo '<p style="color:#000000;font-size: 14px;order:6;" class="form-field form-field-wide">';
            echo '<strong>Cancelled By:</strong> ' . esc_html($cancelled_by);
            echo '</p>';
        }
        if (!empty($payment_mode)) {
            echo '<p style="color:#000000;font-size: 14px;order:7;" class="form-field form-field-wide">';
            echo '<strong>Payment Type:</strong> ' . esc_html($payment_mode);
            echo '</p>';
        }
        if (!empty($cancellation_reason)) {
            echo '<p style="color:#000000;font-size: 14px;order:7;" class="form-field form-field-wide">';
            echo '<strong>Cancellation Reason:</strong> <br>' . esc_html($cancellation_reason);
            echo '</p>';
        }

    }
}


// 1. Replace "Status" column with "Deletion Reason" only in HPOS Trash view
add_filter('manage_woocommerce_page_wc-orders_columns', 'phive_replace_status_with_deletion_reason_in_trash', 20);
function phive_replace_status_with_deletion_reason_in_trash($columns)
{
    // Only apply this on the HPOS orders page AND when the status filter is set to 'trash'
    if (isset($_GET['page']) && $_GET['page'] === 'wc-orders' && isset($_GET['status']) && $_GET['status'] === 'trash') {

        $new_columns = array();

        foreach ($columns as $key => $value) {
            // When we hit the default 'status' column, replace it with ours
            if ($key === 'order_status') {
                $new_columns['deletion_reason'] = 'Remark';
                continue; // Skip adding the original status column
            }
            $new_columns[$key] = $value;
        }

        return $new_columns;
    }

    return $columns;
}

// 2. Populate the "Deletion Reason" column with the meta value
add_action('manage_woocommerce_page_wc-orders_custom_column', 'phive_populate_deletion_reason_column_hpos', 10, 2);
function phive_populate_deletion_reason_column_hpos($column_name, $order)
{
    if ($column_name === 'deletion_reason') {
        // Fetch the custom meta value
        $deletion_reason = $order->get_meta('_deletion_reason');

        // Output the reason, or a fallback dash if it's empty
        if (!empty($deletion_reason)) {
            echo esc_html($deletion_reason);
        } else {
            echo '-';
        }
    }
}

add_filter('woocommerce_shop_order_list_table_order_css_classes', 'add_custom_order_row_class_hpos', 10, 2);
function add_custom_order_row_class_hpos($classes, $order)
{

    $order_id = $order->get_id();
    $secondary_ids = array();
    $addon_ids = array();

    $payers_meta = $order->get_meta('group_additional_payers');
    $payers_meta = maybe_unserialize($payers_meta);
    if (is_array($payers_meta) || is_object($payers_meta)) {
        foreach ($payers_meta as $payer) {
            if (is_array($payer) && !empty($payer['child_order_id'])) {
                $secondary_ids[] = absint($payer['child_order_id']);
            }
        }
    }

    $addon_orders = wc_get_orders(array(
        'meta_key' => 'addons_parent_order_id',
        'meta_value' => $order_id,
        'return' => 'ids',
        'limit' => -1,
        'status' => array('any', 'trash') // Ensure we find child orders even in trash
    ));
    if (!empty($addon_orders)) {
        $addon_ids = array_merge($addon_ids, $addon_orders);
    }

    if (!empty($secondary_ids) || !empty($addon_ids)) {
        $classes[] = 'main-order';
    }

    $parent_split = $order->get_meta('group_parent_order');
    if (!empty($parent_split)) {
        $classes[] = 'split-order phive-child-of-' . $parent_split;
    }

    $parent_addon = $order->get_meta('addons_parent_order_id');
    if (!empty($parent_addon)) {
        $classes[] = 'addon-order phive-child-of-' . $parent_addon;
    }

    return $classes;
}


add_action('manage_shop_order_posts_custom_column', 'phive_admin_order_grouping_data', 10, 2);
add_action('manage_woocommerce_page_wc-orders_custom_column', 'phive_admin_order_grouping_data', 10, 2);

function phive_admin_order_grouping_data($column, $post_or_order)
{
    $order = (is_numeric($post_or_order)) ? wc_get_order($post_or_order) : $post_or_order;
    if (!$order)
        return;

    $order_id = $order->get_id();
    $secondary_ids = array();
    $addon_ids = array();

    $payers_meta = $order->get_meta('group_additional_payers');
    $payers_meta = maybe_unserialize($payers_meta);
    if (is_array($payers_meta) || is_object($payers_meta)) {
        foreach ($payers_meta as $payer) {
            if (is_array($payer) && !empty($payer['child_order_id'])) {
                $secondary_ids[] = absint($payer['child_order_id']);
            }
        }
    }

    $addon_orders = wc_get_orders(array(
        'meta_key' => 'addons_parent_order_id',
        'meta_value' => $order_id,
        'return' => 'ids',
        'limit' => -1,
        'status' => array('any', 'trash') // Ensure we find child orders even in trash
    ));
    if (!empty($addon_orders)) {
        $addon_ids = array_merge($addon_ids, $addon_orders);
    }
    if ($column === 'order_number') {
        if (!empty($secondary_ids) || !empty($addon_ids)) {
            echo '<div class="phive-parent-group" data-parent="' . esc_attr($order_id) . '" data-secondary="' . esc_attr(implode(',', $secondary_ids)) . '" data-addon="' . esc_attr(implode(',', $addon_ids)) . '"></div>';
            echo '<span class="dashicons dashicons-arrow-down-alt2 phive-toggle-children phive-collapsed" style="cursor:pointer; background:#e5f5fa; color:#005a9e; border-radius:50%; transition: transform 0.3s ease; position:absolute; z-index:99;right:50px;padding:5px;transform: rotate(-180deg);"></span>';
        }

        $parent_addon = $order->get_meta('addons_parent_order_id');
        if (!empty($parent_addon)) {
            echo '<div class="phive-is-addon" data-parent-id="' . esc_attr($parent_addon) . '"></div>';
        }
    } elseif ($column === 'order_total') {
        $combined_total = (float) $order->get_total();

        if (!empty($secondary_ids)) {
            foreach ($secondary_ids as $sec_id) {
                $sec_order = wc_get_order($sec_id);
                if ($sec_order) {
                    $combined_total += (float) $sec_order->get_total();
                }
            }
        }

        if (!empty($addon_ids)) {
            foreach ($addon_ids as $addon_id) {
                $addon_order = wc_get_order($addon_id);
                if ($addon_order) {
                    $combined_total += (float) $addon_order->get_total();
                }
            }
        }

        echo "<span class='combined_total' style='display:none;'>" . wc_price($combined_total, array('currency' => $order->get_currency())) . "</span>";
    } else {
        return;
    }
}

add_action('admin_footer', 'phive_admin_orders_grouping_script');
function phive_admin_orders_grouping_script()
{
    $screen = get_current_screen();
    if (!$screen || ($screen->id !== 'edit-shop_order' && $screen->id !== 'woocommerce_page_wc-orders' && $screen->id !== 'edit-shop_order_trash')) {
        return;
    }
    ?>
    <style>
        tr.phive-reordered-child {
            background-color: #fafbfc !important;
        }

        tr.phive-reordered-child:hover {
            background-color: #f0f0f1 !important;
        }

        tr.phive-reordered-child td.column-order_number,
        tr.phive-reordered-child td.column-order_title {
            padding-left: 40px;
        }

        .phive-reordered-child time,
        .phive-reordered-child .booking_status,
        .phive-reordered-child .order_source {
            opacity: 0;
        }

        .phive-toggle-children.phive-collapsed {
            transform: rotate(-0deg) !important;
        }

        td.order_number.column-order_number.has-row-actions.column-primary,
        td.order_title.column-order_title.has-row-actions.column-primary {
            position: relative;
        }

        .phive-reordered-child {
            display: none;
        }
    </style>

    <script>
        jQuery(document).ready(function ($) {

            $('.phive-parent-group').each(function () {
                var $parentData = $(this);
                var parentId = $parentData.data('parent');

                var secondaryIds = $parentData.data('secondary') ? $parentData.data('secondary').toString().split(',') : [];
                var addonIds = $parentData.data('addon') ? $parentData.data('addon').toString().split(',') : [];

                var $parentRow = $parentData.closest('tr');
                var $currentRowInsertion = $parentRow;

                var allChildren = secondaryIds.concat(addonIds);

                allChildren.forEach(function (childId) {
                    if (!childId) return;

                    var $childRow = $('tr').filter(function () {
                        // Extended search for trash rows which sometimes use different ID formats
                        return $(this).attr('id') === 'post-' + childId ||
                            $(this).attr('id') === 'order-' + childId ||
                            $(this).hasClass('post-' + childId) ||
                            $(this).hasClass('order-' + childId) ||
                            $(this).find('input[name="post[]"]').val() == childId;
                    });

                    if ($childRow.length) {
                        $childRow.insertAfter($currentRowInsertion);
                        $childRow.addClass('phive-reordered-child phive-child-of-' + parentId);

                        $childRow.hide();

                        if ($childRow.find('.phive-tree-indicator').length === 0) {
                            $childRow.find('.order_number a, .column-order_title a, .column-order_number strong, .column-order_title strong').first().before('<span class="phive-tree-indicator" style="color:#a7aaad; margin-right:6px; font-size:16px; font-weight:bold;">↳</span>');
                        }

                        $currentRowInsertion = $childRow;
                    }
                });
            });

            $(document).on('click', '.phive-toggle-children', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $btn = $(this);
                var $parentRow = $btn.closest('tr');
                var parentId = $parentRow.find('.phive-parent-group').attr('data-parent');

                var $childrenRows = $('.phive-child-of-' + parentId);

                if ($childrenRows.length) {
                    if ($btn.hasClass('phive-collapsed')) {
                        $childrenRows.show();
                        $btn.removeClass('phive-collapsed');
                    } else {
                        $childrenRows.hide();
                        $btn.addClass('phive-collapsed');
                    }
                }

                return false;
            });

        });
    </script>


    <?php
}

add_filter('admin_body_class', 'add_user_role_to_admin_body');

function add_user_role_to_admin_body($classes)
{
    $user = wp_get_current_user();

    if (!empty($user->roles)) {
        foreach ($user->roles as $role) {
            $classes .= ' role-' . esc_attr($role);
        }
    }

    return $classes;
}
add_filter('manage_woocommerce_page_wc-orders_columns', 'phive_move_status_column_last', 999);

function phive_move_status_column_last($columns)
{
    // Store status column
    if (isset($columns['order_source'])) {
        $status_column = $columns['order_source'];
        unset($columns['order_source']);

        // Add it at the end
        $columns['order_source'] = $status_column;
    }

    return $columns;
}

add_action('admin_menu', 'phive_restrict_service_manager_menu', 999);
function phive_restrict_service_manager_menu()
{
    if (current_user_can('service_manager') && !current_user_can('administrator')) {
        // List of all items to remove
        remove_menu_page('index.php');                  // Dashboard
        remove_menu_page('edit.php');                   // Posts
        remove_menu_page('upload.php');                 // Media
        remove_menu_page('edit.php?post_type=page');    // Pages
        remove_menu_page('edit-comments.php');          // Comments
        remove_menu_page('themes.php');                 // Appearance
        remove_menu_page('plugins.php');                // Plugins
        remove_menu_page('users.php');                  // Users
        remove_menu_page('tools.php');                  // Tools
        remove_menu_page('options-general.php');        // Settings
        remove_menu_page('edit.php?post_type=product'); // Products
        remove_menu_page('woocommerce-marketing');
        remove_menu_page('admin.php?page=wc-settings&tab=checkout&from=PAYMENTS_MENU_ITEM');
        remove_menu_page('post-new.php?post_type=product');
        remove_menu_page('post-new.php?post_type=shop_order');


        // Remove specific WooCommerce submenus except Orders
        remove_submenu_page('woocommerce', 'wc-reports');
        remove_submenu_page('woocommerce', 'wc-settings');
        remove_submenu_page('woocommerce', 'wc-status');
        remove_submenu_page('woocommerce', 'wc-addons');
        remove_submenu_page('woocommerce', 'wc-admin');
        remove_submenu_page('woocommerce', 'wc-cancel-options');
        remove_submenu_page('woocommerce', 'checkout_form_designer');
        remove_submenu_page('woocommerce', 'add-booking');
        remove_submenu_page('woocommerce', 'bookings-settings');
        remove_submenu_page('woocommerce', 'ph-booking-calendar');
        remove_submenu_page('woocommerce', 'ph-export-bookings');
        remove_submenu_page('woocommerce', 'grouped-orders');
        remove_submenu_page('woocommerce', 'add_product');
        remove_submenu_page('edit.php?post_type=product', 'post-new.php?post_type=product'); // Add New Product
        remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=product_cat&post_type=product'); // Categories
        remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=product_tag&post_type=product'); // Tags
        remove_submenu_page('edit.php?post_type=product', 'edit-tags.php?taxonomy=product_shipping_class&post_type=product'); // Shipping Classes
        remove_submenu_page('edit.php?post_type=product', 'attributes');
        remove_submenu_page('bookings', 'add_product');
        remove_submenu_page('bookings', 'add-booking');
        remove_submenu_page('bookings', 'bookings-settings');
        remove_submenu_page('bookings', 'ph-booking-calendar');
        remove_submenu_page('bookings', 'ph-export-bookings');

    }
}


add_action('woocommerce_new_order', function ($order_id) {
    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }
    // Financial Year Logic (Starts April 1st)
    $current_month = (int) date('n');
    $current_year = (int) date('Y');

    if ($current_month >= 4) {
        // April to Dec (e.g., 2025-26)
        $year_part = $current_year . '-' . date('y', strtotime('+1 year'));
    } else {
        // Jan to March (e.g., 2024-25)
        $year_part = ($current_year - 1) . '-' . date('y');
    }

    // Sequence Counter Logic
    $last_sequence = (int) get_option('apl_sequence_counter', 0);
    $new_sequence = $last_sequence + 1;
    $padded_sequence = str_pad($new_sequence, 4, '0', STR_PAD_LEFT);

    $unique_apl_id = 'APL/' . $year_part . '/' . $padded_sequence;

    // Meta update aur save 
    if (!$order->meta_exists('_unique_apl_id')) {
        $order->update_meta_data('_unique_apl_id', $unique_apl_id);
        update_option('apl_sequence_counter', $new_sequence);
    }
    $order->save();
}, 10, 2);



add_action('wp_footer', 'accordhub_custom_js_error_swap', 99);
function accordhub_custom_js_error_swap()
{
    ?>
    <script type="text/javascript">
        (function ($) {
            $(document).ready(function () {
                // Function to find and replace the specific text
                function swapBookingErrorText() {
                    $('.woocommerce-error li').each(function () {
                        var originalText = "This order’s status is “Completed”—it cannot be paid for. Please contact us if you need assistance.";
                        var newText = "You have already made payment for this booking. Please contact Admin for further queries.";

                        if ($(this).text().trim().includes("This order’s status is “Completed”")) {
                            $(this).text(newText);
                        }
                        if ($(this).text().trim().includes("It looks like you’re not logged in with the account used to create this booking. Please log in using the registered mobile number or email address to proceed with the payment.")) {
                            $(this).closest('.woocommerce-error').siblings('.woocommerce-error').remove();
                        }
                        if ($(this).text().trim() === "") {
                            $(this).closest('.woocommerce-error').remove();
                            //$(this).text() = "";
                        }
                    });
                }

                // Run once on load
                swapBookingErrorText();

                // Run again if WooCommerce updates the checkout/notices via AJAX
                $(document.body).on('updated_checkout updated_shipping_method updated_cart_totals', function () {
                    swapBookingErrorText();
                });

                // Watch for any changes in the notice wrapper (MutationObserver)
                var target = document.querySelector('.e-woocommerce-notices-wrapper');
                if (target) {
                    var observer = new MutationObserver(function (mutations) {
                        swapBookingErrorText();
                    });
                    observer.observe(target, { childList: true, subtree: true });
                }
            });
        })(jQuery);
    </script>
    <?php
}

/**
 * Split GST Tax into CGST and SGST on Checkout
 */
//add_filter('woocommerce_cart_tax_totals', 'split_gst_tax_totals_checkout', 10, 1);

function split_gst_tax_totals_checkout($tax_totals)
{
    // Only apply this on the frontend (Cart/Checkout)
    if (is_admin() && !defined('DOING_AJAX')) {
        return $tax_totals;
    }

    foreach ($tax_totals as $code => $tax) {
        // Match the label "GST" (case-sensitive)
        if (trim($tax->label) === 'GST') {
            $half_amount = $tax->amount / 2;

            // Create CGST Object
            $cgst = new stdClass();
            $cgst->label = 'CGST @9%';
            $cgst->amount = $half_amount;
            $cgst->formatted_amount = wc_price($half_amount);
            $cgst->is_compound = $tax->is_compound;

            // Create SGST Object
            $sgst = new stdClass();
            $sgst->label = 'SGST @9%';
            $sgst->amount = $half_amount;
            $sgst->formatted_amount = wc_price($half_amount);
            $sgst->is_compound = $tax->is_compound;

            // Replace the original GST with our new split rows
            unset($tax_totals[$code]);
            $tax_totals['cgst'] = $cgst;
            $tax_totals['sgst'] = $sgst;
        }
    }

    return $tax_totals;
}

add_action('admin_print_scripts', 'patch_elementor_pointer_crash', 999);
function patch_elementor_pointer_crash()
{
    // Only run on the WooCommerce HPOS Orders page
    if (isset($_GET['page']) && 'wc-orders' === $_GET['page']) {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.pointer === 'undefined') {
                    // Create a dummy pointer function to prevent the Uncaught TypeError
                    jQuery.fn.pointer = function () {
                        return {
                            pointer: function () { return this; }
                        };
                    };
                }
            });
        </script>
        <?php
    }
}



// 1. Add the Registration Time column to the Users table
add_filter('manage_users_columns', function ($columns) {
    $columns['registration_time'] = 'Registration Time';
    return $columns;
});

// 2. Populate the Registration Time column with data
add_filter('manage_users_custom_column', function ($val, $column_name, $user_id) {
    if ($column_name === 'registration_time') {
        $user_info = get_userdata($user_id);
        // Formats the output based on your WordPress General Settings for Date & Time
        $val = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($user_info->user_registered));
    }
    return $val;
}, 10, 3);

// 3. Make the Registration Time column sortable
add_filter('manage_users_sortable_columns', function ($columns) {
    $columns['registration_time'] = 'user_registered';
    return $columns;
});

// 4. Sort the Users list by Registration Time (Newest First) by default
add_action('pre_get_users', function ($query) {
    if (!is_admin()) {
        return;
    }

    // Check if the user is already trying to sort by a specific column; if not, apply default
    if (!isset($_REQUEST['orderby'])) {
        $query->set('orderby', 'user_registered');
        $query->set('order', 'DESC');
    }
});

function custom_log($message)
{
    $log_file = WP_CONTENT_DIR . '/debug.log';

    if (is_array($message) || is_object($message)) {
        $message = print_r($message, true);
    }

    $time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$time] " . $message . PHP_EOL, FILE_APPEND);
}

//add_action( 'template_redirect', 'restrict_frontend_for_admin_and_service_manager' );

function restrict_frontend_for_admin_and_service_manager()
{
    // Check if the user is currently logged in
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;

        // Check if the user has either the 'administrator' or 'service_manager' role
        if (in_array('administrator', $roles) || in_array('service_manager', $roles)) {
            // Redirect them to the WordPress admin dashboard
            wp_safe_redirect(admin_url());
            exit;
        }
    }
}

add_filter('manage_users_columns', 'custom_move_login_as_user_column_to_end', 9999);

function custom_move_login_as_user_column_to_end($columns)
{

    $target_column = 'loginasuser_col';

    if (array_key_exists($target_column, $columns)) {
        $column_label = $columns[$target_column];
        unset($columns[$target_column]);
        $columns[$target_column] = $column_label;
    }

    return $columns;
}

// 1. Remove 'username', move 'name' to Position 1, add 'phone', and keep the rest
add_filter('manage_users_columns', 'custom_replace_username_with_name_column', 9999);

function custom_replace_username_with_name_column($columns)
{
    $new_columns = array();

    // Keep the Checkbox ('cb') first
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
    }

    // Put 'name' immediately next and optionally change its header label
    if (isset($columns['name'])) {
        $new_columns['name'] = 'Full Name';
    }

    // Add the new custom 'phone' column
    $new_columns['phone'] = 'Phone';

    // Loop through the rest of the columns
    foreach ($columns as $key => $title) {
        // Skip 'cb' and 'name' (already placed) and completely exclude 'username'
        if ($key !== 'cb' && $key !== 'name' && $key !== 'username') {
            $new_columns[$key] = $title;
        }
    }

    return $new_columns;
}

// 2. Tell WordPress to attach the "Edit | Delete | View" actions to the 'name' column
add_filter('list_table_primary_column', 'custom_set_name_as_primary_column', 10, 2);

function custom_set_name_as_primary_column($default, $screen_id)
{
    // Only apply this to the Users list table
    if ('users' === $screen_id) {
        return 'name';
    }
    return $default;
}

// 3. Populate the 'phone' column with the user meta data
add_filter('manage_users_custom_column', 'custom_populate_phone_column', 10, 3);

function custom_populate_phone_column($val, $column_name, $user_id)
{
    // Check if we are rendering our custom 'phone' column
    if ('phone' === $column_name) {
        // Retrieve the user meta value
        $phone_number = get_user_meta($user_id, 'phone', true);

        // Return the phone number, or a dash if it's empty
        return $phone_number ? esc_html($phone_number) : '—';
    }

    // Return the default value for all other columns
    return $val;
}

add_filter('woocommerce_shop_order_list_table_columns', 'custom_wc_hpos_colspan', 20);
function custom_wc_hpos_colspan($columns)
{
    // This filter handles the columns themselves. 
    // WooCommerce calculates the 'No items' colspan based on the count of these columns.
    // If you are trying to force a colspan of 7 specifically for the empty row:
    add_filter('woocommerce_admin_order_list_colspan', function () {
        return 7;
    }, 20);

    return $columns;
}

// Add Restaurant image in the admin order list
add_filter('woocommerce_order_item_name', 'add_brand_image_before_order_item_name', 10, 2);

function add_brand_image_before_order_item_name($item_name, $item)
{

    $product = $item->get_product();
    if (!$product)
        return $item_name;

    $product_id = $product->get_id();

    // Get brand terms
    $brands = get_the_terms($product_id, 'product_brand');

    if (!empty($brands) && !is_wp_error($brands)) {

        $brand = $brands[0];

        // Get brand image (term meta)
        $brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);

        if ($brand_image_id) {
            $brand_image = wp_get_attachment_image($brand_image_id, 'large', false, [
                'style' => 'width:35px;height:auto;margin-right:8px;vertical-align:middle;border-radius: 2px;'
            ]);

            // Prepend image before title
            $item_name = $brand_image . $item_name;
        }
    }

    return $item_name;
}


// Add Checkbox to Coupon General Options
add_action('woocommerce_coupon_options', 'add_custom_coupon_visibility_checkbox', 10, 2);
function add_custom_coupon_visibility_checkbox($coupon_id, $coupon)
{
    // Get current value, default to 'yes' if it's a new coupon
    $is_visible = $coupon->get_meta('_visible_on_checkout');
    if (empty($is_visible)) {
        $is_visible = 'yes';
    }

    woocommerce_wp_checkbox(array(
        'id' => '_visible_on_checkout',
        'label' => __('Visible on Checkout', 'woocommerce'),
        'description' => __('Show this coupon in the available offers list on the checkout page.', 'woocommerce'),
        'desc_tip' => true,
        'value' => $is_visible,
    ));
}

// Save the Checkbox Value
add_action('woocommerce_coupon_options_save', 'save_custom_coupon_visibility_checkbox', 10, 2);
function save_custom_coupon_visibility_checkbox($post_id, $coupon)
{
    $is_visible = isset($_POST['_visible_on_checkout']) ? 'yes' : 'no';
    $coupon->update_meta_data('_visible_on_checkout', $is_visible);
    $coupon->save();
}

// Add Custom Column Header to Coupons List
add_filter('manage_edit-shop_coupon_columns', 'add_custom_coupon_visibility_column');
function add_custom_coupon_visibility_column($columns)
{
    $columns['visible_on_checkout'] = __('Visible on Checkout', 'woocommerce');
    return $columns;
}

// Populate Custom Column Content in Coupons List
add_action('manage_shop_coupon_posts_custom_column', 'populate_custom_coupon_visibility_column', 10, 2);
function populate_custom_coupon_visibility_column($column, $post_id)
{
    if ($column === 'visible_on_checkout') {
        $coupon = new WC_Coupon($post_id);
        $is_visible = $coupon->get_meta('_visible_on_checkout');

        if (empty($is_visible)) {
            $is_visible = 'yes';
        }

        if ($is_visible === 'yes') {
            echo '<input type="checkbox" name="_visible_on_checkout" class="toggle-coupon-visibility" data-post-id="' . $post_id . '" checked="checked" title="Visible" />';
        } else {
            echo '<input type="checkbox" name="_visible_on_checkout" class="toggle-coupon-visibility" data-post-id="' . $post_id . '" title="Hidden" />';
        }
    }
}

// Add AJAX Script to Admin Footer
add_action('admin_footer', 'add_coupon_visibility_ajax_script');
function add_coupon_visibility_ajax_script()
{
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-shop_coupon') {
        return;
    }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.toggle-coupon-visibility').on('change', function () {
                var checkbox = $(this);
                var isChecked = checkbox.is(':checked') ? 'yes' : 'no';
                var postId = checkbox.data('post-id');

                checkbox.prop('disabled', true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_coupon_visibility_ajax',
                        post_id: postId,
                        visibility: isChecked,
                        security: '<?php echo wp_create_nonce("update_coupon_visibility_nonce"); ?>'
                    },
                    success: function (response) {
                        checkbox.prop('disabled', false);
                        if (!response.success) {
                            alert('Error saving visibility.');
                            checkbox.prop('checked', !checkbox.prop('checked'));
                        }
                    },
                    error: function () {
                        checkbox.prop('disabled', false);
                        alert('AJAX error occurred.');
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    }
                });
            });
        });
    </script>
    <?php
}

// Handle the AJAX Request
add_action('wp_ajax_update_coupon_visibility_ajax', 'ajax_handle_update_coupon_visibility');
function ajax_handle_update_coupon_visibility()
{
    // Verify nonce for security
    check_ajax_referer('update_coupon_visibility_nonce', 'security');

    // Ensure user has permissions
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Permission denied');
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $visibility = isset($_POST['visibility']) ? sanitize_text_field($_POST['visibility']) : 'no';

    if ($post_id > 0) {
        $coupon = new WC_Coupon($post_id);
        $coupon->update_meta_data('_visible_on_checkout', $visibility);
        $coupon->save();
        wp_send_json_success('Saved successfully');
    }

    wp_send_json_error('Invalid Post ID');
}


add_action('woocommerce_review_order_after_payment', 'display_visible_coupons_on_checkout', 15);
function display_visible_coupons_on_checkout()
{
    // Query for published coupons that have our custom meta set to 'yes'
    $args = array(
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_visible_on_checkout',
                'value' => 'yes',
            )
        )
    );

    $coupons = get_posts($args);

    if (empty($coupons)) {
        return;
    }
    echo '<div class="coupon-model" style="display:none">';
    echo '<div class="available-offers-container" style="margin-bottom: 20px; ">';
    echo '<div class="scroll-content">';
    echo '<div class="icon-heading">';
    echo '<h3 style="margin-bottom: 10px;">Coupons</h3>';
    echo '<a href="#" class="toggle-coupon-btn"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 72 72"><path d="M 19 15 C 17.977 15 16.951875 15.390875 16.171875 16.171875 C 14.609875 17.733875 14.609875 20.266125 16.171875 21.828125 L 30.34375 36 L 16.171875 50.171875 C 14.609875 51.733875 14.609875 54.266125 16.171875 55.828125 C 16.951875 56.608125 17.977 57 19 57 C 20.023 57 21.048125 56.609125 21.828125 55.828125 L 36 41.65625 L 50.171875 55.828125 C 51.731875 57.390125 54.267125 57.390125 55.828125 55.828125 C 57.391125 54.265125 57.391125 51.734875 55.828125 50.171875 L 41.65625 36 L 55.828125 21.828125 C 57.390125 20.266125 57.390125 17.733875 55.828125 16.171875 C 54.268125 14.610875 51.731875 14.609875 50.171875 16.171875 L 36 30.34375 L 21.828125 16.171875 C 21.048125 15.391875 20.023 15 19 15 z"></path></svg></a>';
    echo '</div>';
    echo '<ul class="available-offers-list 2" style="list-style: none; padding: 0;">';

    $count = 0;
    foreach ($coupons as $coupon_post) {
        $coupon = new WC_Coupon($coupon_post->ID);
        $code = $coupon->get_code();

        // Handle different discount types (percentage vs fixed cart)
        $amount = $coupon->get_amount();
        $discount_type = $coupon->get_discount_type();
        $discount_text = ($discount_type === 'percent') ? $amount . '%' : wc_price($amount);

        $count++;
        // Hide items beyond the first 3 by default
        $display_style = ($count > 3) ? '' : '';
        $item_class = ($count > 3) ? 'offer-item' : 'offer-item';

        // Check if this specific coupon is already applied to show "Remove" instead of "Apply"
        $is_applied = false;
        global $wp;
        $is_order_pay = isset($wp->query_vars['order-pay']) ? true : false;
        $order_id = $is_order_pay ? absint($wp->query_vars['order-pay']) : 0;

        if ($is_order_pay && $order_id > 0) {
            $order = wc_get_order($order_id);
            if ($order && $order->has_coupon($code)) {
                $is_applied = true;
            }
        } else {
            if (WC()->cart && WC()->cart->has_discount($code)) {
                $is_applied = true;
            }
        }

        $btn_text = $is_applied ? 'Remove' : 'Apply';
        $btn_class = $is_applied ? 'button apply-quick-coupon is-applied' : 'button apply-quick-coupon';

        echo '<li class="' . esc_attr($item_class) . '" style="' . esc_attr($display_style) . ' margin-bottom: 15px; justify-content: space-between; align-items: center; flex-wrap: wrap; padding: 15px; border: 1px dashed #ccc; border-radius: 10px;">';
        echo '<div class="coupon-item">';
        echo '<img class="coupon-img" src="' . get_stylesheet_directory_uri() . '/images/marketing.png" alt="Coupon Icon">';
        echo '<span>Use Code <strong>' . esc_html(strtoupper($code)) . '</strong> to get ' . esc_html($discount_text) . ' off room charges.</span>';
        echo '<button type="button" class="' . esc_attr($btn_class) . '" data-code="' . esc_attr($code) . '">' . esc_html($btn_text) . '</button>';
        echo '</div>';
        echo '<div class="coupon-notice-wrapper" style="display:none; width: 100%; flex-basis: 100%; margin-top: 10px;"></div>';
        echo '</li>';
    }

    echo '</ul>';

    // Show View All button if more than 3 coupons
    if ($count > 3) {
        //echo '<a href="#" id="toggle-offers-btn" style="">View All</a>';
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';
}

// ========================================================================
// NEW AJAX CODE: Handle JS click events on Checkout and Order Pay Pages
// ========================================================================

add_action('wp_footer', 'custom_checkout_coupon_ajax_script');
function custom_checkout_coupon_ajax_script()
{
    // Only run on checkout or order-pay pages
    if (!is_checkout()) {
        return;
    }

    global $wp;
    // Check if we are specifically on the "order-pay" endpoint and get the Order ID
    $is_order_pay = isset($wp->query_vars['order-pay']) ? 'yes' : 'no';
    $order_id = $is_order_pay === 'yes' ? absint($wp->query_vars['order-pay']) : 0;
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $(document).on('click', 'form.checkout.woocommerce-checkout .apply-quick-coupon', function (e) {
                e.preventDefault();

                var $button = $(this);
                $button.addClass('processing');
                var couponCode = $button.data('code');
                var originalText = $button.text();
                var isRemoving = $button.hasClass('is-applied');
                var actionType = isRemoving ? 'remove' : 'apply';

                // SPECIFIC CHANGE: Select ALL buttons on the page with this specific coupon code
                var $allButtons = $('.apply-quick-coupon[data-code="' + couponCode + '"]');

                // SPECIFIC CHANGE: Update text and disable ALL matching buttons during the AJAX request
                $allButtons.text(isRemoving ? 'Removing' : 'Applying').prop('disabled', true);

                // FIX: Hide empty wrappers without animations
                $('.coupon-notice-wrapper').hide().empty();

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    data: {
                        action: 'apply_custom_discount_code_ajax',
                        coupon_code: couponCode,
                        coupon_action: actionType,
                        is_order_pay: '<?php echo $is_order_pay; ?>',
                        order_id: '<?php echo $order_id; ?>',
                        security: '<?php echo wp_create_nonce('apply_custom_coupon_nonce'); ?>'
                    },
                    success: function (response) {
                        if (response.success) {
                            var successHtml = '';

                            // SPECIFIC CHANGE: Switch color based on apply vs remove
                            if (actionType === 'remove') {
                                successHtml = '<div class="custom-coupon-remove-msg" style="margin-bottom: 0; padding: 10px 15px; font-size: 13px; background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5; border-radius: 8px;">' + response.data + '</div>';
                            } else {
                                successHtml = '<div class="custom-coupon-success-msg" style="margin-bottom: 0; padding: 10px 15px; font-size: 13px; background-color: #e5f9e7; color: #1e5631; border: 1px solid #c1eac5; border-radius: 8px;">' + response.data + '</div>';
                            }

                            // FIX: Using .show() instead of slideDown() to guarantee visibility in flex containers
                            $button.closest('li').find('.coupon-notice-wrapper').html(successHtml).show();

                            // Change button states immediately
                            if (actionType === 'apply') {
                                $('.apply-quick-coupon').removeClass('is-applied').text('Apply');
                                $allButtons.addClass('is-applied').text('Remove');
                            } else {
                                $allButtons.removeClass('is-applied').text('Apply');
                            }
                            $allButtons.prop('disabled', false);

                            // Delay the checkout update so the user has time to actually read the message 
                            setTimeout(function () {
                                if ('<?php echo $is_order_pay; ?>' === 'yes') {
                                    window.location.reload();
                                } else {
                                    $('body').trigger('update_checkout');
                                    $button.removeClass('processing');
                                }
                            }, 1500);

                        } else {
                            // FIX: Using custom class 'custom-coupon-error-msg' to prevent Theme JS from stealing the element
                            var errorHtml = '<div class="custom-coupon-error-msg" style="margin-bottom: 0; padding: 10px 15px; font-size: 13px; background-color: #f9e5e5; color: #842029; border: 1px solid #f5c2c7; border-radius: 5px;">' + response.data + '</div>';
                            $button.closest('li').find('.coupon-notice-wrapper').html(errorHtml).show();
                            $allButtons.text(originalText).prop('disabled', false);
                            $button.removeClass('processing');
                        }
                    },
                    error: function () {
                        alert('An error occurred while applying the coupon.');
                        $allButtons.text(originalText).prop('disabled', false);
                        $button.removeClass('processing');
                    }
                });
            });
            $(document).off('click', 'a.toggle-coupon-btn').on('click', 'a.toggle-coupon-btn', function (e) {
                e.preventDefault();
                $('.coupon-model').fadeToggle();
                jQuery('body').toggleClass('no-scroll');
                $('.coupon-notice-wrapper').hide().empty();
                $('.my-custom-coupon-alert').hide().empty();
            });
            $(document).off('click', 'a.toggle-addon').on('click', 'a.toggle-addon', function (e) {
                e.preventDefault();
                $('.addon-box').slideToggle();
                $('.remark-box').slideToggle();
                $('tr.cart_item:has(span.simple)').toggle();
                $(this).toggleClass('rotate-active');

            });
            $(document).on('click', '.e-show-coupon-form, .custom-show-coupon-form', function () {
                //e.preventDefault();
                $('.coupon-notice-wrapper').hide().empty();
                $('.my-custom-coupon-alert').hide().empty();
            });
        });
        // document.addEventListener("DOMContentLoaded", function () {
        //     const couponBox = document.querySelector('.e-coupon-box');
        //     const offersContainer = document.querySelector('.available-offers-container .scroll-content');

        //     if (couponBox && offersContainer) {
        //         offersContainer.appendChild(couponBox);
        //     }
        // });

    </script>
    <?php
}

// ========================================================================
// NEW AJAX CODE: Backend PHP Handler for applying the coupon
// ========================================================================

add_action('wp_ajax_apply_custom_discount_code_ajax', 'ajax_handle_apply_custom_discount_code');
add_action('wp_ajax_nopriv_apply_custom_discount_code_ajax', 'ajax_handle_apply_custom_discount_code');

function ajax_handle_apply_custom_discount_code()
{
    check_ajax_referer('apply_custom_coupon_nonce', 'security');

    $code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
    $action_type = isset($_POST['coupon_action']) ? sanitize_text_field($_POST['coupon_action']) : 'apply';
    $is_order_pay = isset($_POST['is_order_pay']) && $_POST['is_order_pay'] === 'yes';
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

    if (empty($code)) {
        wp_send_json_error(__('Please enter a coupon code.', 'woocommerce'));
    }

    if ($is_order_pay && $order_id > 0) {
        // --- LOGIC FOR ORDER-PAY PAGE ---
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(__('Invalid order.', 'woocommerce'));
        }

        if ($action_type === 'remove') {
            $order->remove_coupon($code);
            $order->calculate_totals();
            $order->save();
            wp_send_json_success(__('Coupon removed successfully.', 'woocommerce'));
        } else {
            // Apply coupon directly to the existing order
            $result = $order->apply_coupon($code);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            } else {
                // Recalculate order totals and save
                $order->calculate_totals();
                $order->save();
                wp_send_json_success(__('Coupon applied successfully.', 'woocommerce'));
            }
        }

    } else {
        // --- LOGIC FOR STANDARD CHECKOUT PAGE ---
        if ($action_type === 'remove') {
            WC()->cart->remove_coupon($code);
            wp_send_json_success(__('Coupon removed successfully.', 'woocommerce'));
        } else {
            if (WC()->cart->has_discount($code)) {
                wp_send_json_error(__('Coupon code already applied!', 'woocommerce'));
            }

            $result = WC()->cart->add_discount($code);

            if ($result) {
                wp_send_json_success(__('Coupon applied successfully.', 'woocommerce'));
            } else {
                // WooCommerce usually handles its own error notices for invalid cart coupons, 
                // but we'll send a generic one just in case the add_discount fails silently.
                wp_send_json_error(__('Invalid coupon code or coupon does not apply to these items.', 'woocommerce'));
            }
        }
    }
}

/**
 * Create a custom message element for WooCommerce coupon notices in Elementor.
 */
add_action('wp_footer', 'custom_elementor_coupon_message_element');
function custom_elementor_coupon_message_element()
{
    // Only load this on Cart and Checkout pages
    if (is_cart() || is_checkout()) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {

                function handleCustomCouponNotices() {
                    // Target standard WooCommerce notice wrappers
                    var notices = $('.woocommerce-error, .woocommerce-message, .woocommerce-info');

                    notices.each(function () {
                        var notice = $(this);
                        var textLowerCase = notice.text().toLowerCase();
                        var rawText = notice.text().trim(); // Get the actual text to put in our custom element

                        if (rawText.includes('cannot be applied because it does not exist.')) {
                            rawText = 'The discount code is invalid or already applied.'
                        }

                        // Check if the notice contains the word "coupon"
                        if (textLowerCase.indexOf('coupon') !== -1) {
                            var elementorCouponBox = $('.e-coupon-box');

                            if (elementorCouponBox.length) {
                                // Determine if it's an error or success message to style differently
                                var isError = notice.hasClass('woocommerce-error');
                                var borderColor = isError ? '#e2401c' : '#0f834d';
                                var bgColor = isError ? '#ffe9e9' : '#e5f9e7';
                                var textColor = isError ? '#e2401c' : '#1e5631';

                                // 1. Remove any existing custom messages to prevent duplicates
                                elementorCouponBox.find('.my-custom-coupon-alert').remove();

                                // 2. Build your completely custom HTML element here
                                var customElement = '<div class="my-custom-coupon-alert" style="margin-top: 15px; padding: 12px 15px; background-color: ' + bgColor + '; color: ' + textColor + ';  font-size: 13px; border-radius: 8px;">' + rawText + '</div>';

                                // 3. Append your custom element below the Elementor form
                                elementorCouponBox.append(customElement);

                                // 4. Hide the original WooCommerce notice at the top of the page
                                notice.hide();
                            }
                        }
                    });
                }

                // 1. Run on initial page load (catches standard reloads)
                handleCustomCouponNotices();

                // 2. Run after standard WooCommerce Cart/Checkout update events
                $(document.body).on('updated_checkout updated_wc_div applied_coupon_in_checkout removed_coupon_in_checkout', function () {
                    handleCustomCouponNotices();
                });

                // 3. Fallback for specific AJAX calls
                $(document).ajaxComplete(function (event, xhr, settings) {
                    if (settings.url && (settings.url.indexOf('wc-ajax=apply_coupon') !== -1 || settings.url.indexOf('wc-ajax=remove_coupon') !== -1 || settings.url.indexOf('wc-ajax=update_order_review') !== -1 || settings.url.indexOf('coupon=') !== -1)) {
                        // Short delay ensures WooCommerce/Elementor finished generating the hidden notice
                        setTimeout(handleCustomCouponNotices, 250);
                    }
                });
            });
        </script>
        <?php
    }
}

add_action('template_redirect', 'apply_coupon_to_pay_order_page');
function apply_coupon_to_pay_order_page()
{
    // 1. Check if the required URL parameters exist
    if (!isset($_GET['pay_for_order']) || empty($_GET['coupon']) || !isset($_GET['key'])) {
        return;
    }

    // 2. Get the Order ID from the URL path (now guaranteed to be available)
    global $wp;
    $order_id = isset($wp->query_vars['order-pay']) ? absint($wp->query_vars['order-pay']) : 0;

    if (!$order_id)
        return;

    $order = wc_get_order($order_id);
    $coupon_code = sanitize_text_field($_GET['coupon']);

    // 3. Security & Status Check: Verify key AND ensure the order actually needs payment
    if (!$order || $_GET['key'] !== $order->get_order_key() || !$order->needs_payment()) {
        return;
    }

    // 4. Apply the coupon if not already applied
    $applied_coupons = $order->get_coupon_codes();

    // Check if coupon is already in the array of applied coupons
    if (!in_array(strtolower($coupon_code), array_map('strtolower', $applied_coupons))) {
        wc_clear_notices();

        // Remove existing coupons so only 1 is applied at a time
        foreach ($applied_coupons as $existing_coupon) {
            $order->remove_coupon($existing_coupon);
        }

        $result = $order->apply_coupon($coupon_code);

        if (is_wp_error($result)) {
            // Coupon failed (expired, invalid, etc.)
            wc_add_notice($result->get_error_message(), 'error');
        } else {
            // Coupon success
            $order->calculate_totals();
            $order->save();
            wc_add_notice(__('Coupon applied successfully.', 'woocommerce'), 'success');

            wp_safe_redirect($order->get_checkout_payment_url());
            exit;
        }
    }
}


function custom_coupon_shortcode($atts)
{
    $button_text = '20% OFF';
    $code = 'ACCRD20';

    if (class_exists('WooCommerce')) {
        $product_id = get_the_ID();
        $product = wc_get_product($product_id);

        if ($product) {
            $price = $product->get_price();
            $coupon_meta = get_post_meta($product_id, 'discount_coupon', true);

            if (!empty($coupon_meta)) {
                $coupon = new WC_Coupon($coupon_meta);
                $discount_amount = $coupon->get_amount();
                $code = $coupon->get_code();
                $disc_price = $price - ($price * ($discount_amount / 100));
                $button_text = round($discount_amount) . '% OFF';
            }
        }
    }

    $atts = shortcode_atts(
        array(
            'text' => $button_text,
            'code' => $code,
            'main_color' => '#1762B8',
            'code_color' => '#3F87D9',
        ),
        $atts,
        'coupon_button'
    );

    ob_start();

    ?>

    <div class="my-coupon-button-container" style="background-color: <?php echo esc_attr($atts['main_color']); ?>;">

        <div class="my-coupon-content">
            <div class="my-coupon-icon">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/icon-discoun.png" alt="Discount Icon">
            </div>
            <div class="my-coupon-text"><?php echo esc_html($atts['text']); ?></div>
        </div>

        <div class="my-coupon-code-box" style="background-color: <?php echo esc_attr($atts['code_color']); ?>;">
            <?php echo esc_html($atts['code']); ?>
        </div>

    </div>
    <?php

    return ob_get_clean();
}

add_shortcode('coupon_button', 'custom_coupon_shortcode');

add_shortcode('loop_product_price', 'custom_loop_product_price_shortcode');

function custom_loop_product_price_shortcode()
{

    if (!class_exists('WooCommerce')) {
        return 'WooCommerce is not active.';
    }

    $product_id = get_the_ID();

    if (!$product_id || get_post_type($product_id) !== 'product') {
        return '';
    }

    $product = wc_get_product($product_id);
    $price = $product->get_price();
    $disc_price = $price * .8;

    $coupon_meta = get_post_meta($product_id, 'discount_coupon', true);

    if (!empty($coupon_meta)) {
        $coupon = new WC_Coupon($coupon_meta);
        if ($coupon->get_id() > 0) {
            $discount_amount = $coupon->get_amount();

            if ($coupon->get_discount_type() === 'percent') {
                $disc_price = $price - ($price * ($discount_amount / 100));
            } else {
                $disc_price = $price - $discount_amount;
            }
        }
    }

    if ($product) {
        return '<div class="custom-loop-price"><span class="actual">' . wc_price($price) . '</span> <span class="discounted">' . wc_price($disc_price) . '</span> Per Session</div>';
    }

    return '';
}

//add_action('init', 'execute_cancel_unpaid_orders_after_5_mins_sql');

function execute_cancel_unpaid_orders_after_5_mins_sql()
{
    global $wpdb;

    // 1. Universal Time (GMT/UTC) ke hisaab se 5 minute pehle ka waqt
    // current_time('mysql', 1) seedha 'Y-m-d H:i:s' format mein GMT time deta hai
    $two_mins_ago_utc = date('Y-m-d H:i:s', strtotime('-5 minutes', current_time('timestamp', 1)));

    // 2. SQL Query: Sirf Pending orders ki IDs nikalna jo 5 min se purane hain
    // Hum wc_orders table (HPOS) ka use kar rahe hain
    $table_name = $wpdb->prefix . 'wc_orders';

    $order_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM $table_name 
         WHERE status = 'wc-pending' 
         AND date_created_gmt < %s",
        $two_mins_ago_utc
    ));

    // 3. Loop through results
    if (!empty($order_ids)) {
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);

            if (!$order)
                continue;

            // Safety check: Jo aapne pehle logic mein diya tha
            if ($order->get_meta('group_parent_order') || $order->get_meta('addons_parent_order_id') || $order->get_meta('_wc_order_attribution_source_type') === 'admin') {
                continue;
            }

            // Meta update aur status change
            $order->update_meta_data('cancelled_unpaid', 'yes');
            $order->update_meta_data('_phive_manual_payment_status', 'failed');
            $order->save_meta_data();
            $order->update_status('cancelled', __('Order cancelled after 5 minutes of no payment.', 'woocommerce'));

        }
    }
}

add_action('wp_footer', 'check_order_status_on_pay_page');
function check_order_status_on_pay_page()
{
    // Sirf 'Order Pay' page par hi chale
    if (!is_checkout() || !isset($_GET['key'])) {
        return;
    }

    global $wp_query;
    $order_id = isset($wp_query->query_vars['order-pay']) ? absint($wp_query->query_vars['order-pay']) : 0;

    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    // Agar order pehle se hi cancelled hai toh script chalane ki zaroorat nahi
    if (!$order || $order->get_status() === 'cancelled') {
        return;
    }

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var orderId = <?php echo $order_id; ?>;
            var checkInterval = setInterval(function () {
                $.ajax({
                    url: wc_checkout_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'check_order_status_poll',
                        order_id: orderId
                    },
                    success: function (response) {
                        if (response.success && response.data.status === 'cancelled') {
                            clearInterval(checkInterval); // Polling rok dein

                            alert("Payment Timeout: Your booking session has expired because payment was not received within the time limit.");

                            window.location.href = '<?php echo wc_get_page_permalink('checkout'); ?>'; // Page refresh karein
                        }
                    }
                });
            }, 5000); // Har 5 second mein check karega
        });
    </script>
    <?php
}

// AJAX Handler jo status check karega
add_action('wp_ajax_check_order_status_poll', 'check_order_status_poll_handler');
add_action('wp_ajax_nopriv_check_order_status_poll', 'check_order_status_poll_handler');

function check_order_status_poll_handler()
{
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;

    if ($order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            wp_send_json_success(array('status' => $order->get_status()));
        }
    }
    wp_send_json_error();
}


add_action('manage_woocommerce_page_wc-orders_custom_column', 'populate_phive_custom_status_column', 10, 2);
function populate_phive_custom_status_column($column_name, $order)
{
    if ($column_name === 'order_status') {
        // Order object make sure karein
        if (!is_a($order, 'WC_Order')) {
            $order = wc_get_order($order);

        }
        $order_id = $order->get_ID();
        $current_status = 'wc-' . $order->get_status();
        $statuses = [
            'wc-pending' => 'Pending',
            'wc-processing' => 'In progress',
            'wc-completed' => 'Confirmed',
            'wc-cancelled' => 'Cancelled',
        ];

        // Meta value nikalein (Underscore check karein agar error aaye)
        $custom_status = $statuses[$current_status];


        if (!empty($custom_status)) {
            if ($custom_status == 'Cancelled' && (track_razorpay_by_order_id($order_id) == 'Payment Canceled' || track_razorpay_by_order_id($order_id) == 'Payment Failed')) {
                echo '<mark class="order-status status-' . $order->get_status() . ' tips"><span>NA</span></mark>';
            } else {
                if ($order->get_meta('fake_status') == 'completed') {
                    echo '<mark class="order-status status-completed tips"><span>Confirmed</span></mark>';
                } else {
                    echo '<mark class="order-status status-' . $order->get_status() . ' tips"><span>' . esc_html(ucwords(str_replace('_', ' ', $custom_status))) . '</span></mark>';
                }
            }

        }
    }
}


// Hook the function to WordPress AJAX for both logged-in and guest users
add_action('wp_ajax_ph_delete_freezed_posts', 'ph_delete_all_freezed_posts_manual');
add_action('wp_ajax_nopriv_ph_delete_freezed_posts', 'ph_delete_all_freezed_posts_manual');

function ph_delete_all_freezed_posts_manual()
{
    //wp_send_json_success();
    $user_id = get_current_user_id();

    global $wpdb;

    $query_post = "SELECT ID as freezed_id
  FROM {$wpdb->prefix}posts AS t1
  WHERE t1.post_type = 'booking_slot_freez'
  AND post_author = {$user_id}
  ";

    $freezed_ids = $wpdb->get_results($query_post, ARRAY_A);

    foreach ($freezed_ids as $key => $product) {

        $freezed_id = $product['freezed_id'];

        // 1. Clear the frontend calendar cache for this asset
        $asset_id = get_post_meta($freezed_id, 'asset_id', 1);
        if ($asset_id != '') {
            $ph_cache_obj = new phive_booking_cache_manager();
            $ph_cache_obj->ph_unset_cache($asset_id);
        }

        // 2. Delete the blocked dates from the plugin's custom availability table
        if (class_exists('Phive_Bookings_Database')) {
            $db_obj = new Phive_Bookings_Database();
            $db_obj->delete_data_availability_table($freezed_id, 'order_id', 'cart');
        }

        // 3. Delete the post meta
        $wpdb->delete($wpdb->postmeta, array('post_id' => $freezed_id));

        // 4. Delete the temporary freezed post
        wp_delete_post($freezed_id);
    }

    if (wp_doing_ajax()) {
        wp_send_json_success();
        wp_die();
    }
}

/* Remove unused CSS/JS for Optimization */
function remove_wpvr_fontawesome()
{
    wp_dequeue_style('wpvrfontawesome');
    wp_deregister_style('wpvrfontawesome');
}
add_action('wp_enqueue_scripts', 'remove_wpvr_fontawesome', 100);

function remove_wpvr_owl_assets_frontpage()
{

    // Run only on frontend + only front page
    if (!is_front_page()) {
        return;
    }

    // Remove Owl JS
    wp_dequeue_script('owl-js');
    wp_deregister_script('owl-js');

    // Remove Owl CSS
    wp_dequeue_style('owl-css');
    wp_deregister_style('owl-css');

}
add_action('wp_enqueue_scripts', 'remove_wpvr_owl_assets_frontpage', 100);


function preload_lcp_image()
{
    if (is_front_page()) {
        echo '<link rel="preload" as="image" href="https://accordhub.in/wp-content/uploads/2026/02/compressed_DSC02330.webp" fetchpriority="high"><link rel="preload" as="image" href="https://accordhub.in/wp-content/uploads/2026/02/compressed_1200_600-1.webp" fetchpriority="high">';
    }
}
add_action('wp_head', 'preload_lcp_image');

add_action('wp_body_open', 'preload_lcp_image_data');
function preload_lcp_image_data()
{
    if (is_front_page()) {
        echo '<img src="https://accordhub.in/wp-content/uploads/2026/02/compressed_DSC02330.webp" fetchpriority="high" style="display: none;" alt="Accordhub"><img src="https://accordhub.in/wp-content/uploads/2026/02/compressed_1200_600-1.webp" fetchpriority="high" style="display: none;" alt="Accordhub">';
    }
}

// Remove and add "flatpickr.min.css" from CDN to local

function remove_flatpickr_cdn_css()
{
    wp_dequeue_style('ph_flatpickr_inbuild_css');
    wp_deregister_style('ph_flatpickr_inbuild_css');
}
add_action('wp_enqueue_scripts', 'remove_flatpickr_cdn_css', 100);

function add_local_flatpickr_css()
{
    wp_enqueue_style(
        'flatpickr-local',
        get_stylesheet_directory_uri() . '/css/flatpickr.min.css',
        array(),
        '4.6.13'
    );
}
add_action('wp_enqueue_scripts', 'add_local_flatpickr_css');

// Removing woocommerce scripts from homepage
function remove_woocommerce_scripts_home()
{

    if (is_front_page()) {

        // JS
        wp_dequeue_script('wc-add-to-cart');
        wp_dequeue_script('wc-js-cookie');
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('sourcebuster-js');
        wp_dequeue_script('wc-order-attribution');
        wp_dequeue_script('wc-jquery-blockui');

        wp_deregister_script('wc-add-to-cart');
        wp_deregister_script('wc-js-cookie');
        wp_deregister_script('woocommerce');
        wp_deregister_script('sourcebuster-js');
        wp_deregister_script('wc-order-attribution');
        wp_deregister_script('wc-jquery-blockui');
    }
}
add_action('wp_enqueue_scripts', 'remove_woocommerce_scripts_home', 100);

function remove_woocommerce_css_home()
{

    if (is_front_page()) {

        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('brands-styles');

        wp_deregister_style('woocommerce-layout');
        wp_deregister_style('woocommerce-smallscreen');
        wp_deregister_style('woocommerce-general');
        wp_deregister_style('brands-styles');
    }
}
add_action('wp_enqueue_scripts', 'remove_woocommerce_css_home', 100);


// Flatpicker JS added via local file
function remove_flatpickr_cdn_js()
{
    wp_dequeue_script('ph_flatpickr_js');
    wp_deregister_script('ph_flatpickr_js');
}
add_action('wp_enqueue_scripts', 'remove_flatpickr_cdn_js', 100);

function add_local_flatpickr_js()
{
    wp_enqueue_script(
        'flatpickr-local',
        get_stylesheet_directory_uri() . '/js/flatpickr.min.js',
        array(),
        '4.6.13',
        true
    );
}
add_action('wp_enqueue_scripts', 'add_local_flatpickr_js', 110);

/**
 * Visually replace item_cost and line_cost on the admin screen without affecting backend math.
 */
add_action('woocommerce_admin_order_item_values', 'custom_visual_phive_booking_price', 10, 3);

function custom_visual_phive_booking_price($product, $item, $item_id)
{
    // 1. Ensure it's a valid product and the Phive Booking type
    if (!$product || !is_a($product, 'WC_Product') || 'phive_booking' !== $product->get_type()) {
        return;
    }

    // 2. Safely get the order and check if the custom function exists
    $order = $item->get_order();
    if (!$order || !function_exists('get_booking_details')) {
        return;
    }

    try {
        $booking = get_booking_details($order);

        // 3. If price exists, inject a tiny JS script to overwrite the HTML visually
        if (is_array($booking) && isset($booking['full_price']) && '' !== $booking['full_price']) {

            // Format the custom price with the WooCommerce currency symbol
            $custom_price_html = wc_price($booking['full_price'], array('currency' => $order->get_currency()));

            // Output a script that runs immediately to change the text in the browser
            ?>
            <script type="text/javascript">
                setTimeout(function () {
                    // Target the exact row using the WooCommerce item ID
                    var rowId = "<?php echo esc_js($item_id); ?>";
                    var customPrice = '<?php echo wp_kses_post($custom_price_html); ?>';

                    var row = document.querySelector('tr.item[data-order_item_id="' + rowId + '"]');
                    if (row) {
                        var itemCostView = row.querySelector('td.item_cost .view');
                        var lineCostView = row.querySelector('td.line_cost .view');

                        // Overwrite the HTML on the screen
                        if (itemCostView) itemCostView.innerHTML = customPrice;
                        if (lineCostView) lineCostView.innerHTML = customPrice;
                    }
                }, 100);
            </script>
            <?php
        }
    } catch (Exception $e) {
        // Fails silently if there's an error
        return;
    }
}

/**
 * Prevent Razorpay from marking cancelled orders as failed by overriding the cancel button behavior.
 */
add_action('wp_footer', 'custom_override_razorpay_cancel_button');

function custom_override_razorpay_cancel_button()
{
    // Only run this script on the WooCommerce "Pay for Order" screen
    if (!is_wc_endpoint_url('order-pay')) {
        return;
    }
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            // Find the specific Razorpay cancel button from your screenshot
            var rzpCancelBtn = document.getElementById('btn-razorpay-cancel');

            if (rzpCancelBtn) {
                // 1. Remove Razorpay's default form submission action
                rzpCancelBtn.removeAttribute('onclick');

                // 2. Add our own click event to redirect safely
                rzpCancelBtn.addEventListener('click', function (e) {
                    e.preventDefault(); // Stop any form submission

                    // Redirect back to the checkout page (or change to cart URL if you prefer)
                    window.location.href = '<?php echo wc_get_page_permalink('myaccount'); ?>';
                });
            }
        });
    </script>
    <?php
}

function custom_remove_wc_orders_bulk_actions($actions)
{
    return array();
}

// Hook for WooCommerce High-Performance Order Storage (HPOS)
//add_filter('bulk_actions-woocommerce_page_wc-orders', 'custom_remove_wc_orders_bulk_actions');

/**
 * 1. Add Dropdown Filter to Orders Screen (HPOS & Legacy)
 */
add_action('restrict_manage_posts', 'phive_custom_admin_orders_filter_dropdown');
add_action('woocommerce_order_list_table_restrict_manage_orders', 'phive_custom_admin_orders_filter_dropdown', 10, 1);

function phive_custom_admin_orders_filter_dropdown($post_type = '')
{
    global $typenow;
    $current_type = $post_type ? $post_type : $typenow;

    // Sirf WooCommerce Orders page par show karein
    if ('shop_order' !== $current_type) {
        return;
    }

    $selected = isset($_GET['phive_ui_filter']) ? wc_clean(wp_unslash($_GET['phive_ui_filter'])) : '';
    ?>
    <select name="phive_ui_filter" id="phive_ui_filter">
        <option value="">Booking Status</option>
        <option value="in_progress" <?php selected($selected, 'in_progress'); ?>>In progress</option>
        <option value="confirmed" <?php selected($selected, 'confirmed'); ?>>Confirmed</option>
        <option value="cancelled_na" <?php selected($selected, 'cancelled_na'); ?>>NA</option>
        <option value="cancelled_only" <?php selected($selected, 'cancelled_only'); ?>>Cancelled</option>
    </select>
    <?php
}

/**
 * 2. Apply Filter Logic for Legacy WooCommerce (WP_Query)
 */
add_filter('request', 'phive_apply_legacy_custom_orders_filter');
function phive_apply_legacy_custom_orders_filter($vars)
{
    global $typenow;
    if (is_admin() && 'shop_order' === $typenow && !empty($_GET['phive_ui_filter'])) {
        $filter_val = wc_clean(wp_unslash($_GET['phive_ui_filter']));

        if ('in_progress' === $filter_val) {
            $vars['post_status'] = 'wc-processing';
        } elseif ('confirmed' === $filter_val) {
            $vars['post_status'] = 'wc-completed';
        } elseif ('cancelled_na' === $filter_val || 'cancelled_only' === $filter_val) {
            $vars['post_status'] = 'wc-cancelled';

            // Live check via Razorpay API
            $cancelled_orders = wc_get_orders(array(
                'status' => 'cancelled',
                'limit' => -1,
                'return' => 'ids',
            ));

            $matched_ids = array();
            foreach ($cancelled_orders as $cid) {
                $rzp_status = track_razorpay_by_order_id($cid);
                $is_na = ($rzp_status == 'Payment Canceled' || $rzp_status == 'Payment Failed');

                if ('cancelled_na' === $filter_val && $is_na) {
                    $matched_ids[] = $cid;
                } elseif ('cancelled_only' === $filter_val && !$is_na) {
                    $matched_ids[] = $cid;
                }
            }

            if (empty($matched_ids)) {
                $matched_ids = array(0); // Force no match
            }
            $vars['post__in'] = $matched_ids;
        }
    }
    return $vars;
}

/**
 * 3. Apply Filter Logic for HPOS (High-Performance Order Storage)
 */
add_filter('woocommerce_order_list_table_prepare_items_query_args', 'phive_apply_hpos_custom_orders_filter', 10, 1);
function phive_apply_hpos_custom_orders_filter($query_args)
{
    if (is_admin() && !empty($_GET['phive_ui_filter'])) {
        $filter_val = wc_clean(wp_unslash($_GET['phive_ui_filter']));

        if ('in_progress' === $filter_val) {
            $query_args['status'] = 'wc-processing';
        } elseif ('confirmed' === $filter_val) {
            $query_args['status'] = 'wc-completed';
        } elseif ('cancelled_na' === $filter_val || 'cancelled_only' === $filter_val) {
            $query_args['status'] = 'wc-cancelled';

            // Live check via Razorpay API
            $cancelled_orders = wc_get_orders(array(
                'status' => 'cancelled',
                'limit' => -1,
                'return' => 'ids',
            ));

            $matched_ids = array();
            foreach ($cancelled_orders as $cid) {
                $rzp_status = track_razorpay_by_order_id($cid);
                $is_na = ($rzp_status == 'Payment Canceled' || $rzp_status == 'Payment Failed');

                if ('cancelled_na' === $filter_val && $is_na) {
                    $matched_ids[] = $cid;
                } elseif ('cancelled_only' === $filter_val && !$is_na) {
                    $matched_ids[] = $cid;
                }
            }

            if (empty($matched_ids)) {
                $matched_ids = array(0); // Force no match
            }
            $query_args['post__in'] = $matched_ids;
        }
    }
    return $query_args;
}

/**
 * Get total booked slots directly from the cart on checkout page.
 */
function get_booking_slots()
{

    if (!WC()->cart || WC()->cart->is_empty()) {
        return 0;
    }
    // error_reporting(E_ALL); // Report all PHP errors
    // ini_set('display_errors', 1);
    $slots = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];

        // Sirf phive_booking product type ko check karein
        if ($product && $product->get_type() === 'phive_booking') {

            // Cart session se time fetch karein
            $from = isset($cart_item['phive_display_time_from']) ? $cart_item['phive_display_time_from'] : '';
            $to = isset($cart_item['phive_display_time_to']) ? $cart_item['phive_display_time_to'] : '';

            // Array handling jaisa aapke get_booking_details mein hai
            if (is_array($from)) {
                $from = $from[0] ?? '';
            }
            if (is_array($to)) {
                $to = $to[0] ?? '';
            }

            if (!empty($from) && !empty($to)) {

                // Same time correction logic from your previous functions
                if ($to === $from || $to === date('g:i a', strtotime($from . ' + 5 hours'))) {
                    $to = date('g:i a', strtotime($to . ' + 4 hours'));
                }

                // Hours difference calculate karein
                $diff_hours = (strtotime($to) - strtotime($from)) / 3600;

                // Slot logic apply karein
                if ($diff_hours > 9) {
                    $slots = 3;
                } elseif ($diff_hours > 5) {
                    $slots = 2;
                } else {
                    $slots = 1;
                }
            }

            // Kyunki cart mein ek hi booking hoti hai, loop break kar dein
            break;
        }
    }

    return $slots;
}

// Check if email has @example.com
function is_placeholder_email($email)
{
    return (strpos($email, '@example.com') !== false);
}

add_action('wp_footer', function () {
    if (is_account_page() || is_checkout()) {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                function clearFakeEmail() {
                    const emailField = document.querySelector('#billing_email');

                    if (emailField && emailField.value.includes('@example.com')) {
                        emailField.value = '';
                    }
                }

                // Run on load
                clearFakeEmail();

                // Run again for WooCommerce AJAX updates (checkout refresh)
                document.body.addEventListener('updated_checkout', clearFakeEmail);

            });
        </script>
        <?php
    }
});

// Replace email if user has example.com email on Address update page
add_action('woocommerce_customer_save_address', function ($user_id, $load_address) {

    if ($load_address !== 'billing')
        return;

    if (empty($_POST['billing_email']))
        return;

    $new_email = sanitize_email($_POST['billing_email']);
    $user = get_userdata($user_id);

    if (!$user)
        return;

    $current_email = $user->user_email;

    // Check conditions:
    // 1. Current email is placeholder
    // 2. New email is NOT placeholder
    if (
        strpos($current_email, '@example.com') !== false &&
        strpos($new_email, '@example.com') === false
    ) {

        // Prevent duplicate email issue
        if (!email_exists($new_email)) {

            wp_update_user([
                'ID' => $user_id,
                'user_email' => $new_email
            ]);

            update_user_meta($user_id, 'billing_email', $new_email);
        }
    }

}, 10, 2);

// Replace email if user has example.com email on Checkout page
add_action('woocommerce_checkout_update_user_meta', function ($user_id, $data) {

    if (empty($data['billing_email']))
        return;

    $new_email = sanitize_email($data['billing_email']);
    $user = get_userdata($user_id);

    if (!$user)
        return;

    $current_email = $user->user_email;

    // Only replace placeholder → real email
    if (
        strpos($current_email, '@example.com') !== false &&
        strpos($new_email, '@example.com') === false
    ) {

        // Prevent duplicate email
        if (email_exists($new_email) && email_exists($new_email) != $user_id) {
            return;
        }

        // ✅ This now sticks
        wp_update_user([
            'ID' => $user_id,
            'user_email' => $new_email
        ]);

        // Keep billing in sync
        update_user_meta($user_id, 'billing_email', $new_email);
    }

}, 999, 2);

// Email and phone no. on billing address should be not editable. Show a hover msg "Please update the phone number/email id in My account page."
add_filter('woocommerce_form_field', function ($field_html, $key, $args, $value) {

    // echo '<pre>';
    //     echo $key;
    // echo '</pre>';

    $custom_span = '';

    $user_id = get_current_user_id();
    if (!$user_id)
        return $field_html;

    $phone = get_user_meta($user_id, 'billing_phone', true);
    $email = get_user_meta($user_id, 'billing_email', true);

    if (!$email) {
        $user = get_userdata($user_id);
        $email = $user ? $user->user_email : '';
    }

    // 🔹 For Phone Field
    if ($key === 'billing_phone' && !empty($phone)) {

        $custom_span = '<span class="cf_tooltip">
            <span class="cf_tooltip_box">
                Please update the phone number in My account page.
            </span>
        </span>';
    }

    // 🔹 For Email Field
    if (
        $key === 'billing_email' &&
        !empty($email) &&
        strpos($email, '@example.com') === false
    ) {
        $custom_span = '<span class="cf_tooltip">
            <span class="cf_tooltip_box">
                Please update the email id in My account page.
            </span>
        </span>';
    }

    // Inject BEFORE closing </p> of field wrapper
    if (!empty($custom_span)) {
        $field_html = str_replace('</p>', $custom_span . '</p>', $field_html);
    }

    return $field_html;

}, 10, 4);



/*
// ==========================================
// 1. Cron Schedule Setup (Daily at Midnight)
// ==========================================

// 1. Cleanup: Remove the OLD 10-minute cron if it exists
// (This runs once on page load to clean up your DB)
add_action('init', 'phive_cleanup_old_cron');
function phive_cleanup_old_cron()
{
    $old_hook = 'phive_group_payment_check_cron'; // Your old hook name
    if (wp_next_scheduled($old_hook)) {
        wp_clear_scheduled_hook($old_hook);
    }
}

// 2. Schedule the NEW Daily Midnight Event
add_action('init', 'phive_schedule_midnight_cron');
function phive_schedule_midnight_cron()
{
    $hook_name = 'phive_daily_payment_check'; // New unique hook name

    if (!wp_next_scheduled($hook_name)) {

        // Calculate "Next Midnight" in your Site's Timezone
        $timezone_string = get_option('timezone_string');
        if (!$timezone_string) {
            $timezone_string = 'Asia/Kolkata'; // Fallback to your likely timezone
        }

        // Create DateTime for "Tomorrow 00:00:00" in Site Time
        $date = new DateTime('now', new DateTimeZone($timezone_string));
        $date->modify('+1 hour');
        $date->setTime((int) $date->format('H'), 0, 0);

        // Schedule it
        wp_schedule_event($date->getTimestamp(), 'hourly', $hook_name);
    }
}

// 3. Connect the Hook to your Logic Function
add_action('phive_daily_payment_check', 'check_group_payment_status');
// ==========================================
// 2. Main Check Logic (Cancel + Reminders)
// ==========================================

function check_group_payment_status()
{
    // Filter for Parent Group Orders that are pending/processing
    $args = [
        'limit' => -1,
        'status' => ['pending', 'on-hold', 'processing'],
        'type' => 'shop_order',
        'meta_query' => [
            ['key' => 'group_payment_mode', 'value' => 'group'],
            ['key' => 'group_additional_payers', 'compare' => 'EXISTS']
        ]
    ];

    $parent_orders = wc_get_orders($args);

    // USE UTC TIMESTAMP for accurate comparisons
    $now = time();

    // Get Site Timezone (e.g., 'Asia/Kolkata') to calculate "9 AM" correctly
    $site_timezone = new DateTimeZone(wp_timezone_string());

    foreach ($parent_orders as $parent_order) {
        $parent_id = $parent_order->get_id();
        $child_payers = $parent_order->get_meta('group_additional_payers');

        if (empty($child_payers) || !is_array($child_payers))
            continue;

        // 1. Check if ALL children have paid
        $all_paid = true;
        $unpaid_children_ids = [];

        foreach ($child_payers as $payer) {
            if (!empty($payer['child_order_id'])) {
                $c_order = wc_get_order($payer['child_order_id']);
                if ($c_order && !$c_order->is_paid()) {
                    $all_paid = false;
                    $unpaid_children_ids[] = $payer['child_order_id'];
                }
            } else {
                $all_paid = false;
            }
        }

        if ($all_paid)
            continue; // Everyone paid, skip.

        // 2. Determine Deadlines (Timestamps)

        // A: Standard 24 Hour Policy
        $created_dt = $parent_order->get_date_created(); // UTC
        $created_ts = $created_dt->getTimestamp();       // UTC Timestamp
        $deadline_24h = $created_ts + (24 * 60 * 60);

        // B: Booking Date Policy
        $booking_start_ts = null;
        foreach ($parent_order->get_items() as $item) {
            $product = $item->get_product();
            if ($product && $product->get_type() === 'phive_booking') {
                $from_date = $item->get_meta('phive_display_time_from');
                if (!empty($from_date)) {
                    $val = is_array($from_date) ? $from_date[0] : $from_date;
                    // strtotime uses WP timezone settings, so this returns a correct UTC timestamp
                    $booking_start_ts = strtotime($val);
                    break;
                }
            }
        }

        $effective_deadline = $deadline_24h;

        if ($booking_start_ts) {
            // Get Midnight (00:00:00) of the booking day
            // We format the booking timestamp to Y-m-d 00:00:00 using site timezone, then convert back
            $b_date_str = wp_date('Y-m-d 00:00:00', $booking_start_ts);
            $booking_midnight = strtotime($b_date_str);

            if ($deadline_24h > $booking_midnight) {
                $effective_deadline = $booking_midnight;
            }
        }

        // 3. AUTO CANCELLATION
        if ($now > $effective_deadline) {
            $reason = ($effective_deadline === $deadline_24h)
                ? 'System: 24-hour payment window expired.'
                : 'System: Booking day arrived (Midnight cutoff). Unpaid shares remaining.';

            $parent_order->update_status('cancelled', $reason);
            $parent_order->update_meta_data('_cancellation_reason', $reason);
            $parent_order->save();
            continue;
        }

        // 4. REMINDER LOGIC (9 AM Only)
        // -----------------------------
        $already_reminded = $parent_order->get_meta('_group_payment_reminder_sent');

        if (!$already_reminded) {

            // Calculate the "Next 9 AM" relative to the Order Creation Time

            // 1. Create a DateTime object from the creation timestamp
            $created_local = new DateTime('@' . $created_ts); // @ indicates Unix timestamp (UTC)
            $created_local->setTimezone($site_timezone);      // Convert to Site Time (e.g. India)

            // 2. Create a target for 9:00 AM on the same day
            $target_9am = clone $created_local;
            $target_9am->setTime(9, 0, 0);

            // 3. If order was created AFTER 9:00 AM, the next reminder slot is Tomorrow 9:00 AM
            if ($created_local >= $target_9am) {
                $target_9am->modify('+1 day');
            }

            $reminder_threshold = $target_9am->getTimestamp(); // Convert back to UTC for comparison

            // Send if current time passed the threshold AND we haven't hit the cancellation deadline
            if ($now > $reminder_threshold && $now < $effective_deadline) {

                foreach ($unpaid_children_ids as $cid) {
                    $child_order = wc_get_order($cid);
                    if (!$child_order)
                        continue;

                    $payer_email = $child_order->get_billing_email();
                    $payer_name = $child_order->get_billing_first_name();
                    $recipient_phone = $child_order->get_billing_phone();
                    $pay_url = $child_order->get_checkout_payment_url();

                    $subject = "Reminder: Complete Your Payment";
                    $heading = "Payment Reminder";
                    $msg = "<p>Dear " . esc_html($payer_name) . ",</p>";
                    $msg .= "<p>Your payment is pending for the room booking.</p>";
                    $msg .= "<p>Please pay before midnight of the booking date, or within 24 hours of booking (whichever is sooner) to avoid cancellation.</p>";
                    $msg .= "<p>Thank You</p>";
                    $msg .= "<p class='btn_p'><a href='{$pay_url}'>Click here to Pay Now</a></p>";

                    if (function_exists('send_woocommerce_custom_email')) {
                        send_woocommerce_custom_email($payer_email, $subject, $heading, $msg);
                    } else {
                        wp_mail($payer_email, $subject, $msg, ['Content-Type: text/html; charset=UTF-8']);
                    }
                    $recipient_phone = preg_replace('/[^0-9]/', '', $recipient_phone);
                    if (strlen($recipient_phone) == 10) {
                        $recipient_phone = '91' . $recipient_phone;
                    }

                    $components = [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $payer_name
                                ]
                            ]
                        ]
                    ];

                    //send_whatsapp_template_msg($recipient_phone, 'payment_reminder_room_booking', $components);
                }

                // Mark as sent
                $parent_order->update_meta_data('_group_payment_reminder_sent', 1);
                $parent_order->save();
            }
        }
    }
}
*/


// ==========================================
// 1. CRON SETUP (Every 5 Minutes)
// ==========================================

// Add custom interval
add_filter('cron_schedules', function ($schedules) {
    $schedules['every_5_min'] = [
        'interval' => 300,
        'display' => 'Every 5 Minutes'
    ];
    return $schedules;
});

// Schedule event
add_action('init', function () {
    if (!wp_next_scheduled('phive_payment_check')) {
        wp_schedule_event(time(), 'every_5_min', 'phive_payment_check');
    }
});

// Hook
add_action('phive_payment_check', 'check_group_payment_status');


// ==========================================
// 2. MAIN FUNCTION (Optimized)
// ==========================================

function check_group_payment_status()
{
    $page = 1;
    $limit = 10;
    $now = time();

    $site_timezone = new DateTimeZone(wp_timezone_string());

    do {

        $args = [
            'limit' => $limit,
            'paged' => $page,
            'status' => ['pending', 'on-hold', 'processing'],
            'type' => 'shop_order',
            'return' => 'ids',

            // Only recent orders (last 48 hours)
            'date_created' => '>' . (time() - 48 * 60 * 60),

            'meta_query' => [
                [
                    'key' => 'group_payment_mode',
                    'value' => 'group',
                ],
                [
                    'key' => 'group_additional_payers',
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => '_wc_order_attribution_source_type',
                    'value' => 'admin',
                    'compare' => '!=',
                ]
            ]
        ];

        $order_ids = wc_get_orders($args);

        if (empty($order_ids)) {
            break;
        }

        foreach ($order_ids as $order_id) {

            $parent_order = wc_get_order($order_id);
            if (!$parent_order)
                continue;

            if ($parent_order->get_meta('_wc_order_attribution_source_type') == 'admin')
                continue;

            $child_payers = $parent_order->get_meta('group_additional_payers');
            if (empty($child_payers) || !is_array($child_payers))
                continue;

            // ----------------------------------
            // 1. CHECK PAYMENT STATUS
            // ----------------------------------

            $all_paid = true;
            $unpaid_children_ids = [];
            $child_cache = [];

            foreach ($child_payers as $payer) {

                if (empty($payer['child_order_id'])) {
                    $all_paid = false;
                    continue;
                }

                $cid = $payer['child_order_id'];

                if (!isset($child_cache[$cid])) {
                    $child_cache[$cid] = wc_get_order($cid);
                }

                $c_order = $child_cache[$cid];

                if ($c_order && !$c_order->is_paid()) {
                    $all_paid = false;
                    $unpaid_children_ids[] = $cid;
                }
            }

            if ($all_paid)
                continue;

            // ----------------------------------
            // 2. CALCULATE DEADLINES
            // ----------------------------------

            $created_dt = $parent_order->get_date_created();
            if (!$created_dt)
                continue;

            $created_ts = $created_dt->getTimestamp();
            $deadline_24h = $created_ts + (24 * 60 * 60);

            $booking_start_ts = null;

            foreach ($parent_order->get_items() as $item) {
                $product = $item->get_product();

                if ($product && $product->get_type() === 'phive_booking') {
                    $from_date = $item->get_meta('phive_display_time_from');

                    if (!empty($from_date)) {
                        $val = is_array($from_date) ? $from_date[0] : $from_date;
                        $booking_start_ts = strtotime($val);
                        break;
                    }
                }
            }

            $effective_deadline = $deadline_24h;

            if ($booking_start_ts) {
                $b_date_str = wp_date('Y-m-d 00:00:00', $booking_start_ts);
                $booking_midnight = strtotime($b_date_str);

                $tz = new DateTimeZone(wp_timezone_string());

                // Convert booking start to site timezone
                $booking_dt = new DateTime('@' . $booking_start_ts);
                $booking_dt->setTimezone($tz);

                // Set to midnight (00:00:00) of that day
                $booking_dt->setTime(0, 0, 0);

                // Convert back to timestamp (UTC internally)
                $booking_midnight = $booking_dt->getTimestamp();

                if ($deadline_24h > $booking_midnight) {
                    $effective_deadline = $booking_midnight;
                }
            }

            // ----------------------------------
            // 3. AUTO CANCEL
            // ----------------------------------

            if ($now > $effective_deadline) {

                $reason = ($effective_deadline === $deadline_24h)
                    ? '24-hour payment window expired.'
                    : 'Booking day arrived (Midnight cutoff). Unpaid shares remaining.';
                $parent_order->update_meta_data('_cancellation_24', 'yes');
                $parent_order->update_meta_data('_cancellation_reason', $reason);
                $parent_order->save();
                $parent_order->update_status('cancelled', $reason);
                continue;
            }

            // ----------------------------------
            // 4. REMINDER LOGIC (9 AM)
            // ----------------------------------

            $already_reminded = $parent_order->get_meta('_group_payment_reminder_sent');
            if ($already_reminded)
                continue;

            $created_local = new DateTime('@' . $created_ts);
            $created_local->setTimezone($site_timezone);

            $target_9am = clone $created_local;
            $target_9am->setTime(9, 0, 0);

            if ($created_local >= $target_9am) {
                $target_9am->modify('+1 day');
            }

            $reminder_threshold = $target_9am->getTimestamp();

            if ($now > $reminder_threshold && $now < $effective_deadline) {

                foreach ($unpaid_children_ids as $cid) {

                    $child_order = $child_cache[$cid] ?? wc_get_order($cid);
                    if (!$child_order)
                        continue;

                    $payer_email = $child_order->get_billing_email();
                    $payer_name = $child_order->get_billing_first_name();
                    $recipient_phone = $child_order->get_billing_phone();
                    $pay_url = $child_order->get_checkout_payment_url();

                    $subject = "Reminder: Complete Your Payment";
                    $heading = "Payment Reminder";

                    $msg = "<p>Dear Customer,</p>";
                    $msg .= "<p>Your payment is pending for the room booking.</p>";
                    $msg .= "<p>Please pay before midnight of the booking date, or within 24 hours of booking (whichever is sooner) to avoid cancellation.</p>";
                    $msg .= "<p>Thank You</p>";
                    $msg .= "<p class='btn_p'><a href='{$pay_url}'>Click here to Pay Now</a></p>";

                    if (function_exists('send_woocommerce_custom_email')) {
                        send_woocommerce_custom_email($payer_email, $subject, $heading, $msg);
                    } else {
                        wp_mail($payer_email, $subject, $msg, ['Content-Type: text/html; charset=UTF-8']);
                    }

                    // Normalize phone
                    $recipient_phone = preg_replace('/[^0-9]/', '', $recipient_phone);
                    if (strlen($recipient_phone) == 10) {
                        $recipient_phone = '91' . $recipient_phone;
                    }
                    if (strlen($recipient_phone) === 10) {
                        $recipient_phone = '91' . $recipient_phone;
                    } elseif (strlen($recipient_phone) === 11 && substr($recipient_phone, 0, 1) === '0') {
                        $recipient_phone = '91' . substr($recipient_phone, 1);
                    }

                    // WhatsApp (optional)

                    $components = [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $payer_name
                                ]
                            ]
                        ]
                    ];
                    send_whatsapp_template_msg($recipient_phone, 'payment_reminder_room_booking', $components);

                }

                // Mark reminder sent
                $parent_order->update_meta_data('_group_payment_reminder_sent', 1);
                $parent_order->save();
            }
        }

        $page++;

    } while (count($order_ids) === $limit);
}


function check_group_payment_status_testing()
{
    $page = 1;
    $limit = 10;
    $now = time();

    echo '<h1>Current Time:' . $now . '</h1>';

    $formatted_date = wp_date('Y-m-d H:i:s', $now);
    echo '<h1>Formatted Date/Time:' . $formatted_date . '</h1>';

    $now = '1778178612';

    echo '<h1>Manual Time:' . $now . '</h1>';
    $formatted_date = wp_date('Y-m-d H:i:s', $now);
    echo '<h1>Formatted Date/Time:' . $formatted_date . '</h1>';

    $site_timezone = new DateTimeZone(wp_timezone_string());

    do {

        $args = [
            'limit' => $limit,
            'paged' => $page,
            'status' => ['pending', 'on-hold', 'processing'],
            'type' => 'shop_order',
            'return' => 'ids',

            // Only recent orders (last 48 hours)
            'date_created' => '>' . (time() - 48 * 60 * 60),

            'meta_query' => [
                [
                    'key' => 'group_payment_mode',
                    'value' => 'group',
                ],
                [
                    'key' => 'group_additional_payers',
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => '_wc_order_attribution_source_type',
                    'value' => 'admin',
                    'compare' => '!=',
                ]
            ]
        ];

        $order_ids = wc_get_orders($args);

        if (empty($order_ids)) {
            break;
        }

        // echo '<pre>';
        // print_r($order_ids);
        // echo '</pre>';

        foreach ($order_ids as $order_id) {

            $parent_order = wc_get_order($order_id);
            if (!$parent_order)
                continue;

            if ($parent_order->get_meta('_wc_order_attribution_source_type') == 'admin')
                continue;

            $child_payers = $parent_order->get_meta('group_additional_payers');
            if (empty($child_payers) || !is_array($child_payers))
                continue;

            // ----------------------------------
            // 1. CHECK PAYMENT STATUS
            // ----------------------------------

            $all_paid = true;
            $unpaid_children_ids = [];
            $child_cache = [];

            foreach ($child_payers as $payer) {

                if (empty($payer['child_order_id'])) {
                    $all_paid = false;
                    continue;
                }

                $cid = $payer['child_order_id'];

                if (!isset($child_cache[$cid])) {
                    $child_cache[$cid] = wc_get_order($cid);
                }

                $c_order = $child_cache[$cid];

                if ($c_order && !$c_order->is_paid()) {
                    $all_paid = false;
                    $unpaid_children_ids[] = $cid;
                }
            }

            if ($all_paid)
                continue;

            // ----------------------------------
            // 2. CALCULATE DEADLINES
            // ----------------------------------

            $created_dt = $parent_order->get_date_created();
            if (!$created_dt)
                continue;

            $created_ts = $created_dt->getTimestamp();
            $deadline_24h = $created_ts + (24 * 60 * 60);

            $booking_start_ts = null;

            foreach ($parent_order->get_items() as $item) {
                $product = $item->get_product();

                if ($product && $product->get_type() === 'phive_booking') {
                    $from_date = $item->get_meta('phive_display_time_from');

                    if (!empty($from_date)) {
                        $val = is_array($from_date) ? $from_date[0] : $from_date;
                        $booking_start_ts = strtotime($val);
                        break;
                    }
                }
            }

            $effective_deadline = $deadline_24h;

            if ($booking_start_ts) {
                $b_date_str = wp_date('Y-m-d 00:00:00', $booking_start_ts);
                $booking_midnight = strtotime($b_date_str);

                $tz = new DateTimeZone(wp_timezone_string());

                // Convert booking start to site timezone
                $booking_dt = new DateTime('@' . $booking_start_ts);
                $booking_dt->setTimezone($tz);

                // Set to midnight (00:00:00) of that day
                $booking_dt->setTime(0, 0, 0);

                // Convert back to timestamp (UTC internally)
                $booking_midnight = $booking_dt->getTimestamp();

                if ($deadline_24h > $booking_midnight) {
                    $effective_deadline = $booking_midnight;
                }
            }

            // ----------------------------------
            // 3. AUTO CANCEL
            // ----------------------------------

            echo '<pre>';
            echo 'Now-1: ' . $now . '<br>';
            echo 'Deadline-1: ' . $effective_deadline;
            echo '</pre>';


            if ($now > $effective_deadline) {

                echo '<pre>';
                echo 'Now-2: ' . $now . '<br>';
                echo 'Deadline 24h-2: ' . $deadline_24h . '<br>';
                echo 'Deadline-2: ' . $effective_deadline;
                echo '</pre>';

                $reason = ($effective_deadline === $deadline_24h)
                    ? 'System: 24-hour payment window expired.'
                    : 'System: Booking day arrived (Midnight cutoff). Unpaid shares remaining.';

                echo 'Reason: ' . $reason . '<br>';
                $formatted_date = wp_date('Y-m-d H:i:s', $effective_deadline);
                echo 'Formatted Date/Time: ' . $formatted_date . '<br>';

                //$parent_order->update_status('cancelled', $reason);
                //$parent_order->update_meta_data('_cancellation_reason', $reason);
                //$parent_order->save();

                continue;
            }



            // ----------------------------------
            // 4. REMINDER LOGIC (9 AM)
            // ----------------------------------

            $already_reminded = $parent_order->get_meta('_group_payment_reminder_sent');
            if ($already_reminded)
                continue;

            $created_local = new DateTime('@' . $created_ts);
            $created_local->setTimezone($site_timezone);

            $target_9am = clone $created_local;
            $target_9am->setTime(9, 0, 0);

            if ($created_local >= $target_9am) {
                $target_9am->modify('+1 day');
            }

            $reminder_threshold = $target_9am->getTimestamp();

            echo '<pre>';
            echo 'Now-3: ' . $now . '<br>';
            echo 'Reminder-3: ' . $reminder_threshold . '<br>';
            echo 'Deadline-3: ' . $effective_deadline;
            echo '</pre>';

            if ($now > $reminder_threshold && $now < $effective_deadline) {

                foreach ($unpaid_children_ids as $cid) {

                    $child_order = $child_cache[$cid] ?? wc_get_order($cid);
                    if (!$child_order)
                        continue;

                    $payer_email = $child_order->get_billing_email();
                    $payer_name = $child_order->get_billing_first_name();
                    $recipient_phone = $child_order->get_billing_phone();
                    $pay_url = $child_order->get_checkout_payment_url();

                    $subject = "Reminder: Complete Your Payment";
                    $heading = "Payment Reminder";

                    $msg = "<p>Dear Customer,</p>";
                    $msg .= "<p>Your payment is pending for the room booking.</p>";
                    $msg .= "<p>Please pay before midnight of the booking date, or within 24 hours of booking (whichever is sooner) to avoid cancellation.</p>";
                    $msg .= "<p>Thank You</p>";
                    $msg .= "<p class='btn_p'><a href='{$pay_url}'>Click here to Pay Now</a></p>";

                    if (function_exists('send_woocommerce_custom_email')) {
                        //send_woocommerce_custom_email($payer_email, $subject, $heading, $msg);
                    } else {
                        //wp_mail($payer_email, $subject, $msg, ['Content-Type: text/html; charset=UTF-8']);
                    }

                    // Normalize phone
                    $recipient_phone = preg_replace('/[^0-9]/', '', $recipient_phone);
                    if (strlen($recipient_phone) == 10) {
                        $recipient_phone = '91' . $recipient_phone;
                    }

                    // WhatsApp (optional)
                    /*
                    $components = [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $payer_name
                                ]
                            ]
                        ]
                    ];
                    send_whatsapp_template_msg($recipient_phone, 'payment_reminder_room_booking', $components);
                    */
                }

                echo '<pre>';
                echo 'Now-4: ' . $now . '<br>';
                echo 'Reminder-4: ' . $reminder_threshold . '<br>';
                echo 'Deadline-4: ' . $effective_deadline;
                echo '</pre>';

                // Mark reminder sent
                //$parent_order->update_meta_data('_group_payment_reminder_sent', 1);
                //$parent_order->save();
            }
        }

        $page++;

    } while (count($order_ids) === $limit);
}

add_action('init', function () {

    if (isset($_GET['cgp'])) {
        check_group_payment_status_testing();
    }

});


// Trigger Date/Slots Selection based on the URl
add_action('wp_footer', function () {
    if (is_product()) { ?>
        <script>
            var product_id = "<?php echo get_the_ID(); ?>";

            jQuery(document).ready(function ($) {

                let dateParam = getParam("date"); // YYYY-MM-DD
                let slotParam = getParam("slot"); // "09:30,14:00"
                if (dateParam) {
                    console.log("Data: " + $('.time-calendar-date-section'));
                    $('.ph-calendar-container').addClass("room_popup_box_loading");

                    dynamicCalendar(dateParam, slotParam, product_id);

                    //$('.time-calendar-date-section').removeClass("room_popup_box_loading");
                }
            });
        </script>
    <?php }
});


// To add html custom fields in blog details page
add_filter('the_content', function ($content) {

    if (!is_singular('post')) {
        return $content;
    }

    $acf_content = get_field('_html_content');

    if (!empty($acf_content)) {
        return do_shortcode($acf_content);
    }

    return $content;

}, 20);

// To add class for html custom fields in blog details page
add_filter('body_class', function ($classes) {

    if (is_singular('post')) {

        $acf_content = get_field('_html_content');

        if (!empty($acf_content)) {
            $classes[] = 'post_type_html';
        }
    }

    return $classes;

});



// Step 1: Promo Link Handling 
// Coupon code link, https://accordhub.in/?promo=exclusiveaccord
add_action('init', function () {

    if (!isset($_GET['promo']) || $_GET['promo'] !== 'exclusiveaccord')
        return;

    // If user already logged in
    if (is_user_logged_in()) {

        $user_id = get_current_user_id();

        // If already used
        if (get_user_meta($user_id, 'accordhub_discount_used', true) === 'yes') {
            set_transient('accordhub_popup', 'used', 30);
            return;
        }

        // If already eligible
        if (get_user_meta($user_id, 'accordhub_discount_eligible', true) === 'yes') {
            set_transient('accordhub_popup', 'already', 30);
            return;
        }

        // New user eligibility
        update_user_meta($user_id, 'accordhub_discount_eligible', 'yes');
        set_transient('accordhub_popup', 'new', 30);

    } else {
        // Store for after login/register
        if (function_exists('WC') && WC()->session) {
            WC()->session->set('accordhub_pending_discount', 'yes');
        }

        $promo_redirect_url = add_query_arg(
            'redirect_to',
            urlencode(home_url('?promo=exclusiveaccord')),
            home_url('/register/')
        );

        wp_safe_redirect($promo_redirect_url);
        exit;
    }
});

// Step 2: After Login / Register
add_action('wp_login', function ($user_login, $user) {
    accordhub_apply_pending_discount($user->ID);
}, 10, 2);

add_action('user_register', function ($user_id) {
    accordhub_apply_pending_discount($user_id);
});

function accordhub_apply_pending_discount($user_id)
{

    if (function_exists('WC') && WC()->session && WC()->session->get('accordhub_pending_discount') === 'yes') {

        update_user_meta($user_id, 'accordhub_discount_eligible', 'yes');
        WC()->session->__unset('accordhub_pending_discount');

        set_transient('accordhub_popup', 'new', 30);
    }
}

// Step 3: Popup Logic (Frontend)
add_action('wp_footer', function () {

    $popup = get_transient('accordhub_popup');
    if (!$popup)
        return;

    delete_transient('accordhub_popup');
    ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            let message = "";
            let showButtons = true;

            switch ("<?php echo $popup; ?>") {

                case "new":
                    message = "<h3>Congratulations!</h3><p>You've unlocked an exclusive 30% discount on your first booking with Accordhub. Your discount will be automatically applied at checkout. </p>";
                    break;

                case "already":
                    message = "<h3>Welcome Back!</h3> <p>The discount is already applied, please book a room to avail the discount.</p>";
                    break;

                case "used":
                    message = "<p>Looks like the discount has already been availed. Although, you can apply the available discounts at Checkout page.</p>";
                    showButtons = false;
                    break;
            }

            if (message) {
                //if (showButtons) {
                Fancybox.show([
                    {
                        html: `
                                <div id="discount_popup_box" class="discount_popup_box">
                                    <div class="discount_popup_box_inner">
                                        <div class="dp_text">${message}</div>
                                        <div class="dp_btns">
                                            <a class="elementor-button elementor-button-link elementor-size-sm" href="/hearing-rooms-arbitration-adr/">Book Now</a>
                                            <button class="elementor-button elementor-button-link elementor-size-sm" title="Close" data-fancybox-close="">I'll book later</button>
                                        </div>
                                    </div> 
                                </div>
                            `,
                    }
                ], {
                    dragToClose: false,   // ❌ disable drag to close
                    backdropClick: false,
                    placeFocusBack: false, // optional (avoid focus jump)
                    hideScrollbar: false   // optional (better UX)
                });
                /*} else {
                    //alert(message);
                }*/
            }
        });
    </script>

    <?php
});

add_action('woocommerce_cart_calculate_fees', function ($cart) {

    if (is_admin() && !defined('DOING_AJAX'))
        return;

    if (!is_user_logged_in())
        return;

    $user_id = get_current_user_id();

    $eligible = get_user_meta($user_id, 'accordhub_discount_eligible', true);
    $used = get_user_meta($user_id, 'accordhub_discount_used', true);

    if ($eligible === 'yes' && $used !== 'yes') {

        $booking_total = 0;

        foreach ($cart->get_cart() as $cart_item) {

            if (empty($cart_item['data']))
                continue;

            $product = $cart_item['data'];

            // ✅ Only booking product
            if ($product->get_type() !== 'phive_booking')
                continue;

            // ✅ Use line total (already includes slots logic)
            $booking_total += $cart_item['line_total'];
        }

        // ✅ Apply discount only on booking total
        $discount = $booking_total * 0.30;

        if ($discount > 0) {
            $cart->add_fee('Less Discount (30%)', -$discount);
        }
    }
});

// Step 5: Block Other Coupons
add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {

    if (!is_user_logged_in())
        return $valid;

    $user_id = get_current_user_id();

    $eligible = get_user_meta($user_id, 'accordhub_discount_eligible', true);
    $used = get_user_meta($user_id, 'accordhub_discount_used', true);

    if ($eligible === 'yes' && $used !== 'yes') {
        wc_add_notice('The current offer cannot be merged with other offers or coupons', 'error');
        return false;
    }

    return $valid;
}, 10, 2);

// Step 6: Mark Discount as Used
add_action('woocommerce_checkout_create_order', function ($order, $data) {

    foreach ($order->get_fees() as $fee) {
        if (strpos($fee->get_name(), 'Less Discount (30%)') !== false) {
            $order->update_meta_data('_accordhub_discount_applied', 'yes');
        }
    }

}, 10, 2);

// STEP 7: Mark Discount as Used (ONLY after order success)
add_action('woocommerce_order_status_completed', function ($order_id) {

    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();

    if (!$user_id)
        return;

    if ($order->get_meta('_accordhub_discount_applied') === 'yes') {
        update_user_meta($user_id, 'accordhub_discount_used', 'yes');
    }
});

// STEP 8: Show Message on Room Details Page
add_action('woocommerce_before_add_to_cart_form', function () {

    if (!is_user_logged_in())
        return;

    $user_id = get_current_user_id();

    $eligible = get_user_meta($user_id, 'accordhub_discount_eligible', true);
    $used = get_user_meta($user_id, 'accordhub_discount_used', true);

    if ($eligible === 'yes' && $used !== 'yes') {
        //echo '<p style="color:green;">Applicable discount will be enabled on checkout.</p>';
    }
});

// STEP 9: Detect Promo Active
function accordhub_is_discount_active()
{
    if (!is_user_logged_in())
        return false;

    $user_id = get_current_user_id();

    return (
        get_user_meta($user_id, 'accordhub_discount_eligible', true) === 'yes' &&
        get_user_meta($user_id, 'accordhub_discount_used', true) !== 'yes'
    );
}

// add_filter('phive_booking_cost', function($price, $product_id){
//     // Get original base price
//     $base_cost = get_post_meta($product_id, '_phive_booking_pricing_base_cost', true);

//     return $base_cost; // ignore all rules (including discount)
// }, 10, 2);

add_action('woocommerce_before_calculate_totals', function ($cart) {

    if (is_admin() && !defined('DOING_AJAX'))
        return;

    if (!accordhub_is_discount_active())
        return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {

        if (empty($cart_item['data']))
            continue;

        $product = $cart_item['data'];
        $product_id = $product->get_id();

        // ✅ Skip products from specific categories
        if (has_term(['refreshments-2', 'addons', 'stationery', 'meals', 'support-services'], 'product_cat', $product_id)) {
            continue;
        }

        // ✅ Get cost per unit
        $cost_per_unit = (float) get_post_meta($product_id, '_phive_booking_pricing_cost_per_unit', true);

        // ✅ Calculate slots from time
        $from = isset($cart_item['phive_book_from_date']) ? strtotime($cart_item['phive_book_from_date']) : 0;
        $to = isset($cart_item['phive_book_to_date']) ? strtotime($cart_item['phive_book_to_date']) : 0;

        $interval = (int) get_post_meta($product_id, '_phive_book_interval', true);

        $slots = 1;

        if ($from && $to && $interval) {
            $duration = ($to - $from) / 60;
            $slots = max(1, ceil($duration / $interval));
        }

        $new_price = $cost_per_unit * $slots;

        // ✅ OVERRIDE FINAL PRICE
        $product->set_price($new_price);
    }

}, 9999);


/**
 * Send automatic emails and WhatsApp messages for admin-generated split payment bookings.
 * Hooks into 'woocommerce_process_shop_order_meta' with a late priority (99) 
 * to ensure child orders have already been created by your existing function.
 */
add_action('woocommerce_process_shop_order_meta', 'phive_admin_send_split_payment_emails', 99, 1);

function phive_admin_send_split_payment_emails($parent_order_id)
{
    $parent_order = wc_get_order($parent_order_id);
    if (!$parent_order)
        return;

    // Only proceed if it is a group/split payment
    $payment_mode = $parent_order->get_meta('group_payment_mode');
    if ($payment_mode !== 'group')
        return;

    // Ensure this is only processed for admin-created orders
    $source_type = $parent_order->get_meta('_wc_order_attribution_source_type');
    $created_via = $parent_order->get_created_via();
    if ($source_type !== 'admin') {
        return;
    }

    // Prevent duplicate emails
    if ($parent_order->get_meta('_admin_split_emails_sent'))
        return;

    $additional_payers = $parent_order->get_meta('group_additional_payers');
    if (empty($additional_payers) || !is_array($additional_payers))
        return;

    $booking = get_booking_details($parent_order);

    $all_child_emails_sent = true;

    // Loop through additional payers to send emails
    foreach ($additional_payers as $index => $payer) {
        $child_order_id = isset($payer['child_order_id']) ? $payer['child_order_id'] : '';

        // If child order doesn't exist yet, we can't send the email.
        if (!$child_order_id) {
            $all_child_emails_sent = false;
            continue;
        }

        $child_order = wc_get_order($child_order_id);
        if (!$child_order) {
            $all_child_emails_sent = false;
            continue;
        }

        // Skip if email already sent for this specific child
        if ($child_order->get_meta('_admin_child_email_sent'))
            continue;

        $name = $payer['name'];
        $email = $payer['email'];
        $phone = $payer['phone'];

        $share_amount = $child_order->get_total();
        $pay_url = $child_order->get_checkout_payment_url();
        $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $payer['child_order_id'];

        $c_subject = "Accordhub - Your Room Booking is Confirmed (Booking ID - " . $parent_order_id . ")";
        $c_heading = "Your Room Booking is Confirmed (Booking ID - " . $parent_order_id . ")";

        $message = "<p>Dear Customer,</p>";
        $message .= "<p>As per the request, your room booking is confirmed.</p>";
        $message .= "<p><strong>Booking ID:</strong> {$parent_order_id}</p>";
        $message .= "<p><strong>Room:</strong> {$booking['room']}</p>";
        $message .= "<p><strong>Date & Time:</strong> {$booking['datetime']}</p>";
        $message .= "<p><strong>Requested by:</strong> {$name}</p>";

        $message .= "<p>Please regsiter or login with the OTP using below Email Id or Phone Number.</p>";

        $message .= "<p><strong>Login Email:</strong> {$email}</p>";
        $message .= "<p><strong>Phone No.:</strong> {$phone}</p>";

        $message .= "<p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>";

        if (function_exists('send_woocommerce_custom_email')) {
            //send_woocommerce_custom_email($payer['email'], $c_subject, $c_heading, $message);
        }

        $url_parts = explode('/order-pay/', $pay_url);
        $button_suffix = end($url_parts);

        // $components = [
        //     [
        //         'type' => 'body',
        //         'parameters' => [
        //             ['type' => 'text', 'text' => $payer['name'] ?? 'Customer'],
        //             ['type' => 'text', 'text' => $share_amount]
        //         ]
        //     ],
        //     [
        //         'type' => 'button',
        //         'sub_type' => 'url',
        //         'index' => 0,
        //         'parameters' => [
        //             ['type' => 'text', 'text' => $button_suffix]
        //         ]
        //     ]
        // ];

        $components = [
            [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'document',
                        'document' => [
                            'link' => 'https://staging.accordhub.in/wp-content/uploads/invoice-37698.pdf',
                            'filename' => 'invoice-37698.pdf'
                        ]
                    ]
                ]
            ],
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => 'Admin Split'
                    ]
                ]
            ],
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => 0,
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => 'invoice-37698.pdf'
                    ]
                ]
            ]
        ];

        if (function_exists('send_whatsapp_template_msg') && !empty($payer['phone'])) {
            //send_whatsapp_template_msg($payer['phone'], 'payment_other_parties_request_completion', $components);
            send_whatsapp_template_msg($payer['phone'], 'live_addons_invoice', $components);
        }

        $child_order->update_meta_data('_admin_child_email_sent', 1);
        $child_order->save();
    }

    // Send email/WhatsApp to the Main Buyer (Party 1) if not sent yet
    $main_buyer_email = $parent_order->get_billing_email();
    $main_buyer_phone = $parent_order->get_billing_phone();
    $main_buyer_name = $parent_order->get_billing_first_name() . " " . $parent_order->get_billing_last_name() ?: 'Customer';
    $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $parent_order_id;

    if ($main_buyer_email && !$parent_order->get_meta('_admin_main_buyer_email_sent')) {
        $parent_pay_url = $parent_order->get_checkout_payment_url();
        $p_subject = "Accordhub - Your Room Booking is Confirmed (Booking ID - " . $parent_order_id . ")";
        $p_heading = "Your Room Booking is Confirmed (Booking ID - " . $parent_order_id . ")";

        $share_amount_parent = $parent_order->get_total();

        $p_message = "<p>Dear Customer,</p>";
        $p_message .= "<p>As per the request, your room booking is confirmed.</p>";
        $p_message .= "<p><strong>Booking ID:</strong> {$parent_order_id}</p>";
        $p_message .= "<p><strong>Room:</strong> {$booking['room']}</p>";
        $p_message .= "<p><strong>Date & Time:</strong> {$booking['datetime']}</p>";
        $p_message .= "<p><strong>Requested by:</strong> {$main_buyer_name}</p>";

        $p_message .= "<p>Please regsiter or login with the OTP using below Email Id or Phone Number.</p>";

        $p_message .= "<p><strong>Login Email:</strong> {$main_buyer_email}</p>";
        $p_message .= "<p><strong>Phone No.:</strong> {$main_buyer_phone}</p>";

        $p_message .= "<p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>";

        if (function_exists('send_woocommerce_custom_email')) {
            //send_woocommerce_custom_email($main_buyer_email, $p_subject, $p_heading, $p_message);
        }

        $p_url_parts = explode('/order-pay/', $parent_pay_url);
        $p_button_suffix = end($p_url_parts);

        // $p_components = [
        //     [
        //         'type' => 'body',
        //         'parameters' => [
        //             ['type' => 'text', 'text' => $main_buyer_name],
        //             ['type' => 'text', 'text' => $share_amount_parent]
        //         ]
        //     ],
        //     [
        //         'type' => 'button',
        //         'sub_type' => 'url',
        //         'index' => 0,
        //         'parameters' => [
        //             ['type' => 'text', 'text' => $p_button_suffix]
        //         ]
        //     ]
        // ];
        $components = [
            [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'document',
                        'document' => [
                            'link' => 'https://staging.accordhub.in/wp-content/uploads/invoice-37698.pdf',
                            'filename' => 'invoice-37698.pdf'
                        ]
                    ]
                ]
            ],
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => 'Admin Main'
                    ]
                ]
            ],
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => 0,
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => 'invoice-37698.pdf'
                    ]
                ]
            ]
        ];

        if (function_exists('send_whatsapp_template_msg') && !empty($main_buyer_phone)) {
            //send_whatsapp_template_msg($main_buyer_phone, 'payment_other_parties_request_completion', $p_components);
            //send_whatsapp_template_msg($main_buyer_phone, 'live_addons_invoice', $components);
        }

        $parent_order->update_meta_data('_admin_main_buyer_email_sent', 1);
    }

    // If all child orders were processed successfully, mark the parent to prevent re-running
    if ($all_child_emails_sent) {
        $parent_order->update_meta_data('_admin_split_emails_sent', 1);
    }

    $parent_order->save();
}