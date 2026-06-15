<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function my_enqueue_ajax_script()
{
    wp_enqueue_script(
        'my-frontend-ajax',
        get_stylesheet_directory_uri() . '/js/site.js',
        array('jquery'),
        time(), // use current timestamp as version
        true
    );

    wp_localize_script('my-frontend-ajax', 'my_ajax_obj', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'homeUrl' => get_bloginfo('url'),
    ));
}
add_action('wp_enqueue_scripts', 'my_enqueue_ajax_script');

// Ajax to show calendar popup
add_action('wp_ajax_load_booking_calendar', 'my_load_booking_calendar');
add_action('wp_ajax_nopriv_load_booking_calendar', 'my_load_booking_calendar');

function my_load_booking_calendar()
{
    if (empty($_POST['product_id'])) {
        wp_send_json_error('Missing product ID');
    }

    $product_id = intval($_POST['product_id']);
    $proId = $_POST["product_id"];

    // Render calendar via shortcode
    $calendar_html = do_shortcode('[ph_bookings_calendar id="' . $product_id . '"]');
    $calendar_html .= '<div class="popup_btm_btn"><button type="button" class="popup_addtocart elementor-button" data-href="' . get_permalink($proId) . '">Book Now</button></div>';

    echo $calendar_html;
    wp_die();
}

// Add Custom dicount if 2nd slots booked at a time

// add_action('woocommerce_before_calculate_totals', function ($cart) {
//     if (is_admin() && !defined('DOING_AJAX')) {
//         return;
//     }

//     foreach ($cart->get_cart() as $cart_item_key => $cart_item) {

//         // Ensure this is a booking product with the necessary time data
//         if (!isset($cart_item['phive_display_time_from']) || !isset($cart_item['phive_display_time_to'])) {
//             continue;
//         }

//         $from = strtotime($cart_item['phive_display_time_from']);
//         $to = strtotime($cart_item['phive_display_time_to']);

//         if ($from && $to) {
//             // Calculate total duration in hours
//             $diff_hours = ($to - $from) / 3600;

//             // Get base cost per slot from product meta
//             $cost_per_slot = (float) get_post_meta($cart_item['product_id'], '_phive_booking_pricing_cost_per_unit', true);
//             $product_title = get_the_title($cart_item['product_id']);

//             if ($cost_per_slot <= 0)
//                 continue;

//             $new_price = $cart_item['data']->get_price();
//             $discount_applied = false;
//             $message = "";

//             // CASE 1: 3 Slots (Full Day - 12 Hours) -> 15% Total Discount
//             if ($diff_hours > 9) {
//                 $total_before_discount = $cost_per_slot * 3;
//                 $new_price = $total_before_discount * 0.85; // Apply 15% off total
//                 $message = "🎁 Full Day Special: A 15% discount has been applied for booking 3 slots for {$product_title}!";
//                 $discount_applied = true;
//             }
//             // CASE 2: 2 Slots (Double Slot - 8 Hours) -> 10% Total Discount
//             elseif ($diff_hours > 5) {
//                 $total_before_discount = $cost_per_slot * 2;
//                 $new_price = $total_before_discount * 0.90; // Apply 10% off total
//                 $message = "🎁 Multi-Slot Discount: A 10% discount has been applied for booking 2 slots for {$product_title}.";
//                 $discount_applied = true;
//             }

//             if ($discount_applied) {
//                 $cart_item['data']->set_price($new_price);

//                 // Show the notice only once
//                 if (!wc_has_notice($message, 'success')) {
//                     //wc_add_notice($message, "success");
//                 }
//             }
//         }
//     }
// }, 20);


// Trigger Date/Slots Selection based on the URl
/*
add_action('wp_footer', function () {
    if (is_product()) { ?>
        <script>
            jQuery(document).ready(function ($) {
                function getParam(name) {
                    let url = new URL(window.location.href);
                    return url.searchParams.get(name);
                }

                let dateParam = getParam("date"); // YYYY-MM-DD
                let slotParam = getParam("slot"); // "09:00" or "09:00,14:00"

                function selectBooking(tries = 0, dateParam, slotParam) {
                    if (!dateParam || tries > 20) return; // Stop after 10 tries

                    let dateCl = $("input.callender-full-date[value='" + dateParam + "']");

                    if (!dateCl.length) {
                        // Date not visible → click next month
                        let nextBtn = $("li.ph-next");
                        if (nextBtn.length) {
                            nextBtn.trigger("click");
                        }

                        // Retry after short delay
                        setTimeout(function () {
                            selectBooking(tries + 1, dateParam, slotParam);
                        }, 2500);
                        return;
                    }

                    // Date exists → select it
                    let dateClLi = dateCl.closest("li.ph-calendar-date");
                    if (!dateClLi.hasClass("timepicker-selected-date")) {
                        dateClLi.trigger("click");
                    }

                    // Select slots if provided
                    if (slotParam) {
                        let slots_val = slotParam.split(",");
                        let allSelected_val = true;

                        slots_val.forEach(function (s) {
                            let slotValueData = dateParam + " " + s.trim();
                            let slotCl = $("input.callender-full-date[value='" + slotValueData + "']");
                            if (slotCl.length) {
                                let slotClLi = slotCl.closest("li.ph-calendar-date");
                                if (!slotClLi.hasClass("selected-date")) {
                                    slotClLi.trigger("click");
                                }
                                if (!slotClLi.hasClass("selected-date")) allSelected_val = false;
                            } else {
                                allSelected_val = false;
                            }
                        });

                        if (allSelected_val) {
                            $(".room_popup_box_loading").removeClass("room_popup_box_loading");
                            return;
                        }
                    }

                    // Retry if not fully selected
                    setTimeout(function () {
                        selectBooking(tries + 1, dateParam, slotParam);
                    }, 1000);
                }

                selectBooking(tries = 0, dateParam, slotParam);
            });
        </script>
    <?php }
});
*/
// Coupon code discount for Settlement Price
/*
add_filter( 'woocommerce_coupon_get_discount_amount', function( 
    $discount, 
    $discounting_amount, 
    $cart_item, 
    $single, 
    $coupon 
) {
    // Only run for our coupon
    if ( strtolower( $coupon->get_code() ) !== 'settlementdiscount' ) {
        return $discount;
    }

    $settlement_cost = 0;

    // if( is_cart() ){
    //     echo '<pre>';
    //         print_r($cart_item);
    //     echo '</pre>';
    // }

    // Check if resources exist
    if ( isset( $cart_item['phive_booked_resources'] ) ) {
        $selected = $cart_item['phive_booked_resources'];
        $resources_array = get_post_meta( $cart_item['product_id'], '_phive_booking_resources_pricing_rules', true );
        // if( is_cart() ){
        //     echo $selected;
        //     echo '<pre>';
        //         print_r($resources_array);
        //     echo '</pre>';
        // }

        foreach ( $resources_array as $rule ) {
            if ( $rule['ph_booking_resources_name'] === $selected ) {
                $settlement_cost = floatval( str_replace( ',', '', $rule['ph_booking_resources_cost'] ) );
                break;
            }
        }
    }

    if ( $settlement_cost <= 0 ) {
        return 0;
    }

    // % coupons → apply on settlement cost
    if ( $coupon->get_discount_type() === 'percent' ) {
        $discount = $settlement_cost * ( $coupon->get_amount() / 100 );
    }

    // Fixed coupons → cap by settlement cost
    if ( in_array( $coupon->get_discount_type(), [ 'fixed_cart', 'fixed_product' ] ) ) {
        $discount = min( $coupon->get_amount(), $settlement_cost );
    }

    return $discount;
}, 10, 5 );
*/

// Add tooltip for coupon description in checkout/cart
add_filter('woocommerce_cart_totals_coupon_label', function ($label, $coupon) {
    // Force uppercase coupon code
    $code = strtoupper($coupon->get_code());

    // Rebuild the label with span wrapper
    $label = sprintf(__('Coupon: <span class="coupon-code">%s</span>', 'woocommerce'), $code);

    // Get WooCommerce coupon object
    $coupon_obj = new WC_Coupon($coupon->get_code());

    // Get description (if any)
    $description = $coupon_obj->get_description();

    if ($description) {
        $label .= ' <span class="coupon-tooltip" title="' . esc_attr($description) . '" style="color:#1762b8 !important; cursor: help;">i</span>';
    }

    return $label;
}, 10, 2);


// Ajax to show calendar popup
add_action('wp_ajax_load_booking_calendar_CO', 'my_load_booking_calendar_CO');
add_action('wp_ajax_nopriv_load_booking_calendar', 'my_load_booking_calendar_CO');

function my_load_booking_calendar_CO()
{
    if (empty($_POST['product_id'])) {
        wp_send_json_error('Missing product ID');
    }

    $product_id = intval($_POST['product_id']);
    $proId = $_POST["product_id"];

    // Render calendar via shortcode
    $calendar_html = do_shortcode('[ph_bookings_calendar id="' . $product_id . '"]');

    echo $calendar_html;
    wp_die();
}

// Ajax to show calendar popup cart
add_action('wp_ajax_load_booking_calendar_cart', 'my_load_booking_calendar_cart');
add_action('wp_ajax_nopriv_load_booking_calendar_cart', 'my_load_booking_calendar_cart');

function my_load_booking_calendar_cart()
{
    if (empty($_POST['product_id'])) {
        wp_send_json_error('Missing product ID');
    }

    $product_id = intval($_POST['product_id']);
    $proId = $_POST["product_id"];
    $cartName = $_POST["cart_name"];

    // Render calendar via shortcode
    $calendar_html = do_shortcode('[ph_bookings_calendar id="' . $product_id . '"]');
    $calendar_html .= '<div class="popup_btm_btn"><button type="button" data-cart-name="' . $cartName . '" class="update_cart_from_popup elementor-button" data-href="' . get_permalink($proId) . '">Update</button></div>';

    echo $calendar_html;
    wp_die();
}


add_action('wp_ajax_save_booking_calendar_cart', 'save_booking_calendar_cart');
add_action('wp_ajax_nopriv_save_booking_calendar_cart', 'save_booking_calendar_cart');

function save_booking_calendar_cart()
{
    if (!is_user_logged_in())
        wp_send_json_error('Login required.');

    $user_id = get_current_user_id();
    $bookingData = $_POST['bookingData'] ?? [];
    $cart_name = sanitize_text_field($_POST['cartName'] ?? '');

    if (empty($bookingData['date']) || empty($bookingData['times'][0])) {
        wp_send_json_error('Invalid booking data.');
    }

    $saved_carts = get_user_meta($user_id, '_saved_carts', true);
    if (!is_array($saved_carts) || !isset($saved_carts[$cart_name])) {
        wp_send_json_error('Saved cart not found.');
    }

    $cart = $saved_carts[$cart_name];

    $date = $bookingData['date'];
    $slots = $bookingData['slots'];
    $newprice = $bookingData['price'];
    $slot1_start = $bookingData['times'][0]; // first slot start
    $slot2_start = $bookingData['times'][1] ?? null;
    $slot3_start = $bookingData['times'][2] ?? null;

    $morning_end = $date . ' 13:30'; // morning slot end
    $afternoon_start = $date . ' 14:00';
    $afternoon_end = $date . ' 18:00';
    $eve_start = $date . ' 18:30';
    $eve_end = $date . ' 22:30';

    // Determine the last slot selected to calculate the correct end time
    $last_slot_start = $slot3_start ?: ($slot2_start ?: $slot1_start);
    $last_time_check = date('H:i', strtotime($last_slot_start));

    if ($last_time_check === '09:30') {
        $actual_end_time = $morning_end;
    } elseif ($last_time_check === '14:00') {
        $actual_end_time = $afternoon_end;
    } else {
        $actual_end_time = $eve_end;
    }

    foreach ($cart['items'] as &$item) {
        $product = wc_get_product($item['product_id']);
        if ($product && $product->get_type() === 'phive_booking') {
            // Start of booking
            $item['phive_book_from_date'] = date('Y-m-d H:i', strtotime($slot1_start));
            $item['phive_display_time_from'] = date('F d, Y g:i a', strtotime($slot1_start));
            $item['ph_selected_blocks'] = $slots;
            $item['phive_booked_price'] = $newprice;

            // Dynamically set based on the last slot
            $item['phive_book_to_date'] = date('Y-m-d H:i', strtotime($last_slot_start));
            $item['phive_display_time_to'] = date('F d, Y g:i a', strtotime($actual_end_time));
        }
    }

    // Update cart name based on exact start and end times
    $main_product_name = wc_get_product($cart['items'][0]['product_id'])->get_name();
    $start_time = date('g:i a', strtotime($slot1_start));
    $end_time = date('g:i a', strtotime($actual_end_time));
    $new_cart_name = $main_product_name . ' - ' . date('F d, Y', strtotime($slot1_start)) . " ($start_time – $end_time)";

    if ($new_cart_name !== $cart_name && isset($saved_carts[$new_cart_name])) {
        wp_send_json_error(['message' => 'Item with same date & time slots is already added in the Cart.']);
    }

    unset($saved_carts[$cart_name]);
    $saved_carts[$new_cart_name] = $cart;

    update_user_meta($user_id, '_saved_carts', $saved_carts);

    wp_send_json_success([
        'message' => 'Booking updated successfully.',
        'new_cart_name' => $new_cart_name,
        'updated_cart' => $cart
    ]);
}


// Admin menu for orders

// 1️⃣ Add submenu under WooCommerce
add_action('admin_menu', 'custom_group_orders_submenu');
function custom_group_orders_submenu()
{
    add_submenu_page(
        'woocommerce',             // Parent slug
        'Grouped Bookings',           // Page title
        'Grouped Bookings',           // Menu title
        'manage_woocommerce',      // Capability
        'grouped-orders',           // Menu slug
        'display_grouped_orders'    // Callback function
    );
}

// 2️⃣ Include WP_List_Table if not loaded
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// 3️⃣ Custom WP_List_Table class
class Grouped_Orders_Table extends WP_List_Table
{

    function __construct()
    {
        parent::__construct(array(
            'singular' => 'grouped_order',
            'plural' => 'grouped_orders',
            'ajax' => false
        ));
    }

    // Columns
    function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'order_id' => 'Order ID',
            'total' => 'Total',
            'status' => 'Status',
            'date' => 'Date'
        );
    }

    // Sortable columns
    function get_sortable_columns()
    {
        return array(
            'order_id' => array('ID', true),
            'total' => array('total', false),
            'date' => array('date', false)
        );
    }

    // Prepare items
    function prepare_items()
    {
        $per_page = 10;
        $current_page = $this->get_pagenum();

        // Get all parent orders
        $parent_orders = wc_get_orders(array(
            'limit' => -1,
            'meta_key' => 'group_payment_mode',
            'meta_value' => 'group',
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        $data = array();

        foreach ($parent_orders as $parent) {
            $data[] = array(
                'order_id' => $parent->get_id() . '|' . $parent->get_billing_first_name() . ' ' . $parent->get_billing_last_name() . '|parent',
                'total' => wc_price($parent->get_total()),
                'status' => wc_get_order_status_name($parent->get_status()),
                'date' => $parent->get_date_created()->date('Y-m-d H:i')
            );

            // Get child orders
            $children = wc_get_orders(array(
                'limit' => -1,
                'meta_key' => 'group_parent_order',
                'meta_value' => $parent->get_id(),
                'orderby' => 'date',
                'order' => 'ASC'
            ));

            foreach ($children as $child) {
                $data[] = array(
                    'order_id' => $child->get_id() . '|' . $child->get_billing_first_name() . ' ' . $child->get_billing_last_name() . '|child',
                    'total' => wc_price($child->get_total()),
                    'status' => wc_get_order_status_name($child->get_status()),
                    'date' => $child->get_date_created()->date('Y-m-d H:i')
                );
            }
        }

        // Pagination
        $total_items = count($data);
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->_column_headers = array($this->get_columns(), array(), array());
        $this->items = $data;
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    // Checkbox column
    function column_cb($item)
    {
        $id = explode('|', $item['order_id'])[0];
        return sprintf('<input type="checkbox" name="order[]" value="%s" />', $id);
    }

    // Order ID column with parent/child styling
    function column_order_id($item)
    {
        list($id, $customer, $type) = explode('|', $item['order_id']);
        $edit_link = admin_url('post.php?post=' . $id . '&action=edit');

        if ($type === 'parent') {
            return '<strong><a href="' . esc_url($edit_link) . '">#' . $id . ' ' . esc_html($customer) . '</a></strong>';
        } else {
            return '<span style="padding-left:30px;"><a href="' . esc_url($edit_link) . '">#' . $id . ' ' . esc_html($customer) . '</a></span>';
        }
    }

    // Default column
    function column_default($item, $column_name)
    {
        if ($column_name == 'order_id') {
            return $this->column_order_id($item);
        }
        return $item[$column_name];
    }
}

// 4️⃣ Display table in submenu page
function display_grouped_orders()
{
    echo '<div class="wrap"><h1>Grouped Orders</h1>';

    $table = new Grouped_Orders_Table();
    $table->prepare_items();
    $table->display();

    echo '</div>';
}

function unique_orders_by_id($orders)
{
    $unique = [];
    $seen = [];

    foreach ($orders as $order) {
        if (!in_array($order['order_id'], $seen, true)) {
            $unique[] = $order;
            $seen[] = $order['order_id'];
        }
    }
    return $unique;
}


/* My Account Tabs */
// 3. Content for the "My Bookings" tab
add_action('woocommerce_account_my-bookings_endpoint', 'display_my_orders_with_booking_times');
function display_my_orders_with_booking_times()
{
    $customer_id = get_current_user_id();
    if (!$customer_id)
        return;

    $orders = wc_get_orders([
        'customer_id' => $customer_id,
        'limit' => -1,
    ]);

    $past_orders = [];
    $active_orders = [];
    $upcoming_orders = [];
    $past_orders_child = [];
    $active_orders_child = [];
    $upcoming_orders_child = [];

    $now = current_time('timestamp');

    foreach ($orders as $order) {
        if ($order->get_meta('cancelled_unpaid')) {
            continue;
        }
        $order_booking_from = null;
        $order_booking_to = null;

        // 👇 Loop through items to fetch booking meta from phive_booking
        foreach ($order->get_items() as $item) {
            if ($item->get_product()->get_type() === 'phive_booking') {
                if ($order->get_meta('group_parent_order')) {
                    $main = wc_get_order($order->get_meta('group_parent_order'));
                    foreach ($main->get_items() as $itm) {
                        if ($itm->get_product()->get_type() === 'phive_booking') {
                            $from_date = $itm->get_meta('Booked From');
                            $to_date = $itm->get_meta('Booked To');
                        }
                    }
                } else {
                    $from_date = $item->get_meta('Booked From');
                    $to_date = $item->get_meta('Booked To');
                }


                if ($from_date && $to_date) {
                    $from_ts = strtotime($from_date);
                    $to_ts = strtotime($to_date);

                    // Track earliest start and latest end for this order
                    if (!$order_booking_from || $from_ts < $order_booking_from) {
                        $order_booking_from = $from_ts;
                    }
                    if (!$order_booking_to || $to_ts > $order_booking_to) {
                        $order_booking_to = $to_ts;
                    }
                }
            }
        }

        // Skip orders without any booking items
        if (!$order_booking_from || !$order_booking_to) {
            continue;
        }

        $order_data = [
            'order_id' => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'order_status' => $order->get_status(),
            'order_url' => $order->get_view_order_url(),
            'total' => $order->get_formatted_order_total(),
            'from_date' => date('Y-m-d H:i', $order_booking_from),
            'to_date' => date('Y-m-d H:i', $order_booking_to),
            'actions' => wc_get_account_orders_actions($order),
        ];

        // Classify
        if ($now > $order_booking_to) {
            $past_orders[] = $order_data;
            $past_orders_child[] = $order_data;

        } elseif ($now < $order_booking_from) {
            $upcoming_orders[] = $order_data;
            $upcoming_orders_child[] = $order_data;
        } else {
            $active_orders[] = $order_data;
            $active_orders_child[] = $order_data;
        }
    }
    $past_orders_all = unique_orders_by_id(array_merge($past_orders, $past_orders_child));
    $active_orders_all = unique_orders_by_id(array_merge($active_orders, $active_orders_child));
    $upcoming_orders_all = unique_orders_by_id(array_merge($upcoming_orders, $upcoming_orders_child));

    // Render sections
    echo '<div class="booking-main-tabs">';
    echo '<ul class="main-tab-titles">';
    echo '<li class="active" data-tab="tab-all">All Bookings</li>';
    echo '<li data-tab="tab-you">Booked by Me</li>';
    echo '<li data-tab="tab-another">Booked by Another Party</li>';
    echo '</ul>';

    echo '<div class="main-tab-content">';

    echo '<div id="tab-all" class="tab active">';
    custom_render_orders_section_all(__('Active Bookings', 'space'), $active_orders_all);
    echo "<br>";
    echo "<span class='divider'></span>";
    custom_render_orders_section_all(__('Upcoming Bookings', 'space'), $upcoming_orders_all);
    echo "<br>";
    echo "<span class='divider'></span>";
    custom_render_orders_section_all(__('Past Bookings', 'space'), $past_orders_all);
    echo '</div>';

    // Tab 1: Booked by You
    echo '<div id="tab-you" class="tab">';
    custom_render_orders_section(__('Active Bookings', 'space'), $active_orders);
    echo "<br>";
    echo "<span class='divider'></span>";
    custom_render_orders_section(__('Upcoming Bookings', 'space'), $upcoming_orders);
    echo "<br>";
    echo "<span class='divider'></span>";
    custom_render_orders_section(__('Past Bookings', 'space'), $past_orders);
    echo '</div>';

    // Tab 2: Booked by Another Party
    echo '<div id="tab-another" class="tab">';
    custom_render_orders_section_child(__('Active Bookings', 'space'), $active_orders_child);
    echo "<br>";
    echo "<span class='divider'></span>";
    custom_render_orders_section_child(__('Upcoming Bookings', 'space'), $upcoming_orders_child);
    echo "<br>";
    echo "<span class='divider'></span>";
    custom_render_orders_section_child(__('Past Bookings', 'space'), $past_orders_child);
    echo '</div>';

    echo '</div>'; // main-tab-content
    echo '</div>'; // booking-main-tabs

    // ---------------- JS for Tabs ----------------
    ?>
    <script>
        jQuery(document).ready(function ($) {
            $('.main-tab-titles li').click(function () {
                var tab = $(this).data('tab');
                $(this).addClass('active').siblings().removeClass('active');
                $('.main-tab-content .tab').removeClass('active');
                $('#' + tab).addClass('active');
            });
        });
    </script>
    <?php
}

/**
 * Render a table for categorized orders
 */
function custom_render_orders_section($title, $orders)
{
    if (empty($orders)) {
        echo '<h4 class="order-heading">' . esc_html($title) . '</h4>';
        echo 'No Booking Found<br>';

        return;
    }

    $title_slug = strtolower($title);
    $title_slug = preg_replace('/[^a-z0-9]+/', '-', $title_slug);
    $title_slug = trim($title_slug, '-');

    $i = 0;

    echo '<h4 class="order-heading">' . esc_html($title) . '</h4>';
    echo '<table id="table_parent_' . $title_slug . '_' . $i . '" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">';
    echo '<thead>
            <tr>
                <th>' . __('Ref. No.', 'woocommerce') . '</th>
                <th>' . __('Details', 'space') . '</th>
                <!-- <th>' . __('Payment Type', 'space') . '</th> -->
                <th>' . __('Booking Status', 'woocommerce') . '</th>
                <th>' . __('Payment Status', 'woocommerce') . '</th>
                <th>' . __('Amount', 'woocommerce') . '</th>
                <th style="text-align:center;">' . __('Actions', 'woocommerce') . '</th>
            </tr>
        </thead>';
    echo '<tbody>';

    foreach ($orders as $order) {
        // echo '<pre>';
        //     print_r($order);
        // echo '</pre>';
        $order_id = $order['order_id'];
        $orderData = wc_get_order($order_id);
        //echo "parent<br>";
        if ($orderData->get_meta('group_parent_order')) {

            $parent_order_id = $orderData->get_meta('group_parent_order');
            $parentOrderData = wc_get_order($parent_order_id);
            continue;
        }
        $invoice_order_id = $order_id;

        $download_url = wp_nonce_url(
            add_query_arg('download_invoice', $invoice_order_id, wc_get_account_endpoint_url('view-order')),
            'download_invoice_' . $invoice_order_id
        );

        $i++;
        // echo '<pre>';
        //     print_r($parentOrderData);
        // echo '</pre>';

        $status_class = 'woocommerce-orders-table__row--status-' . esc_attr($order['order_status']);

        $from_ts = strtotime($order['from_date']);
        $to_ts = strtotime($order['to_date']);

        $payment_type = "Regular";
        // echo '<pre>';
        //     print_r($orderData);
        // echo '</pre>';
        if ($orderData->get_meta('group_payment_mode') == 'group') {
            $payment_type = "Split: Parent";
        } else if ($orderData->get_meta('group_parent_order')) {
            $payment_type = "Split: Child";
        }

        foreach ($orderData->get_items() as $item_id => $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            // Check product type
            if ($product->get_type() === 'phive_booking') {
                $phive_product_name = $product->get_name();
                break; // stop after first found
            }
        }
        $my_bookings = get_bookings_list_from_order($order['order_number']);
        // Format: September 30th (9AM to 6PM)
        $booking_display = date('F jS Y', $from_ts) . ' (' . date('gA', $from_ts) . ' to ' . date('gA', $to_ts) . ')';

        echo '<tr class="woocommerce-orders-table__row ' . $status_class . '">';
        echo '<td data-title="Order"><a href="' . esc_url($order['order_url']) . '">' . esc_html($order['order_number']) . '</a></td>';
        echo '<td data-title="Booking">' . $my_bookings . '</td>';
        //echo '<td data-title="Payment_Type">' . esc_html($payment_type) . '</td>';
        echo '<td data-title="Booking Status">' . get_booking_status($order['order_number']) . '</td>';
        echo '<td data-title="Booking Type">' . verify_razorpay_payment($order_id) . '</td>';

        if ($orderData->get_meta('group_parent_order')) {
            echo '<td data-title="Total">';
            echo "<div class='gp_div'>" . $order['total'] . '
                <span class="tooltip_box">
                    <span class="tooltip_i">i</span>
                    <span class="tooltip_box_hover" style="display:none;">
                        This amount is your share of the total booking cost of ' . wc_price($parentOrderData->get_meta("group_original_total")) . ', which is equally distributed among all parties.
                    </span>
                </span>
            </div>';
            echo '</td>';
            if ($orderData->has_status('pending') && !$orderData->is_paid()) {
                if ('yes' !== $orderData->get_meta('disable_customer_emails') && $orderData->get_meta('_wc_order_attribution_source_type') !== 'admin') {
                    echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
                } else {
                    echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
                    //echo '<td data-title="Actions" style="text-align:center;">-</td>';
                }
            } elseif (($orderData->has_status('completed') || $orderData->has_status('processing')) && $orderData->is_paid()) {
                echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($download_url) . '" class=""><img src="' . get_stylesheet_directory_uri() . '/images/invoice.png" class="inv-icon" title="Download Receipt"></a></td>';
            } else {
                echo '<td data-title="Actions" style="text-align:center;">-</td>';
            }

        } else if ($orderData->get_meta('group_payment_mode') == 'group') {
            echo '<td data-title="Total">';
            echo "<div class='gp_div'>" . $order['total'] . '
                <span class="tooltip_box">
                    <span class="tooltip_i">i</span>
                    <span class="tooltip_box_hover" style="display:none;"> 
                        This amount is your share of the total booking cost of ' . wc_price($orderData->get_meta("group_original_total")) . ', which is equally distributed among all parties.
                    </span>
                </span>
            </div>';
            echo '</td>';
            if ($orderData->has_status('pending') && !$orderData->is_paid()) {
                if ('yes' !== $orderData->get_meta('disable_customer_emails') && $orderData->get_meta('_wc_order_attribution_source_type') !== 'admin') {
                    echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
                } else {
                    //echo '<td data-title="Actions" style="text-align:center;">-</td>';
                    echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
                }

            } elseif (($orderData->has_status('completed') || $orderData->has_status('processing')) && $orderData->is_paid()) {
                echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($download_url) . '" class=""><img src="' . get_stylesheet_directory_uri() . '/images/invoice.png" class="inv-icon" title="Download Receipt"></a></td>';
            } else {
                echo '<td data-title="Actions" style="text-align:center;">-</td>';
            }
        } elseif (($orderData->has_status('completed') || $orderData->has_status('processing')) && $orderData->is_paid()) {
            echo '<td data-title="Total">' . $order['total'] . '</td>';
            echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($download_url) . '" class=""><img src="' . get_stylesheet_directory_uri() . '/images/invoice.png" class="inv-icon" title="Download Receipt"></a></td>';
        } else {
            if ($orderData->has_status('pending') && !$orderData->is_paid()) {
                echo '<td data-title="Total">' . $order['total'] . '</td>';
                echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
            } else {
                echo '<td data-title="Total">' . $order['total'] . '</td>';
                echo '<td data-title="Actions" style="text-align:center;">-</td>';
            }

        }


        echo '</tr>';
    }

    echo '</tbody></table>';

    if ($i == 0) {
        echo 'No Booking Found<br> 
            <style>
                #table_parent_' . $title_slug . '_0{display:none;}
            </style>
        ';
    }
    //echo $i;
}

/**
 * Render a table for categorized orders
 */
function custom_render_orders_section_child($title, $orders)
{
    if (empty($orders)) {
        echo '<h4 class="order-heading">' . esc_html($title) . '</h4>';
        echo 'No Booking Found<br>';

        return;
    }


    $i = 0;

    $title_slug = strtolower($title);
    $title_slug = preg_replace('/[^a-z0-9]+/', '-', $title_slug);
    $title_slug = trim($title_slug, '-');

    echo '<h4 class="order-heading">' . esc_html($title) . '</h4>';
    echo '<table id="table_child_' . $title_slug . '_' . $i . '" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">';
    echo '<thead>
            <tr>
                <th>' . __('Ref. No.', 'woocommerce') . '</th>
                <th>' . __('Details', 'space') . '</th>
                <!-- <th>' . __('Payment Type', 'space') . '</th> -->
                <th>' . __('Booking Status', 'woocommerce') . '</th>
                <th>' . __('Payment Status', 'woocommerce') . '</th>
                <th>' . __('Amount', 'woocommerce') . '</th>
                <th style="text-align:center;">' . __('Actions', 'woocommerce') . '</th>
            </tr>
        </thead>';
    echo '<tbody>';



    foreach ($orders as $order) {
        // echo '<pre>';
        //     print_r($order);
        // echo '</pre>';
        //echo 'Child<br>';
        $order_id = $order['order_id'];
        $orderData = wc_get_order($order_id);
        if ($orderData->get_meta('group_parent_order')) {
            $parent_order_id = $orderData->get_meta('group_parent_order');
            $parentOrderData = wc_get_order($parent_order_id);
            $i++;
        } else {
            continue;
        }
        $invoice_order_id = $order_id;

        $download_url = wp_nonce_url(
            add_query_arg('download_invoice', $invoice_order_id, wc_get_account_endpoint_url('view-order')),
            'download_invoice_' . $invoice_order_id
        );
        // echo '<pre>';
        //     print_r($parentOrderData);
        // echo '</pre>';

        $status_class = 'woocommerce-orders-table__row--status-' . esc_attr($order['order_status']);

        $from_ts = strtotime($order['from_date']);
        $to_ts = strtotime($order['to_date']);

        $payment_type = "Regular";
        // echo '<pre>';
        //     print_r($orderData);
        // echo '</pre>';
        if ($orderData->get_meta('group_payment_mode') == 'group') {
            $payment_type = "Split: Parent";
        } else if ($orderData->get_meta('group_parent_order')) {
            $payment_type = "Split: Child";
        }
        foreach ($orderData->get_items() as $item_id => $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            // Check product type
            if ($product->get_type() === 'phive_booking') {
                $phive_product_name = $product->get_name();
                break; // stop after first found
            }
        }
        $my_bookings = get_bookings_list_from_order($order['order_number']);
        // Format: September 30th (9AM to 6PM)
        $booking_display = date('F jS Y', $from_ts) . ' (' . date('gA', $from_ts) . ' to ' . date('gA', $to_ts) . ')';

        echo '<tr class="woocommerce-orders-table__row ' . $status_class . '">';
        echo '<td data-title="Order"><a href="' . esc_url($order['order_url']) . '">' . esc_html($order['order_number']) . '</a></td>';
        echo '<td data-title="Booking">' . $my_bookings . '</td>';
        //echo '<td data-title="Payment_Type">' . esc_html($payment_type) . '</td>';
        echo '<td data-title="Booking Status">' . get_booking_status($order['order_number']) . '</td>';
        echo '<td data-title="Booking Type">' . verify_razorpay_payment($order_id) . '</td>';

        if ($orderData->get_meta('group_parent_order')) {
            echo '<td data-title="Total">';
            echo "<div class='gp_div'>" . $order['total'] . '
                <span class="tooltip_box">
                    <span class="tooltip_i">i</span>
                    <span class="tooltip_box_hover" style="display:none;">
                        This amount is your share of the total booking cost of ' . wc_price($parentOrderData->get_meta("group_original_total")) . ', which is equally distributed among all parties.
                    </span>
                </span>
            </div>';
            echo '</td>';
            if ($orderData->has_status('pending') && !$orderData->is_paid()) {

                if ('yes' !== $orderData->get_meta('disable_customer_emails') && $orderData->get_meta('_wc_order_attribution_source_type') !== 'admin') {
                    echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
                } else {
                    //echo '<td data-title="Actions" style="text-align:center;">-</td>';
                    echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
                }
            } elseif (($orderData->has_status('completed') || $orderData->has_status('processing')) && $orderData->is_paid()) {
                echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($download_url) . '" class=""><img src="' . get_stylesheet_directory_uri() . '/images/invoice.png" class="inv-icon" title="Download Receipt"></a></td>';
            } else {
                echo '<td data-title="Actions" style="text-align:center;">-</td>';
            }

        } else if ($orderData->get_meta('group_payment_mode') == 'group') {
            echo '<td data-title="Total">';
            echo "<div class='gp_div'>" . $order['total'] . '
                <span class="tooltip_box">
                    <span class="tooltip_i">i</span>
                    <span class="tooltip_box_hover" style="display:none;"> 
                        This amount is your share of the total booking cost of ' . wc_price($orderData->get_meta("group_original_total")) . ', which is equally distributed among all parties.
                    </span>
                </span>
            </div>';
            echo '</td>';
            if ($orderData->has_status('pending') && !$orderData->is_paid()) {

                if ('yes' !== $orderData->get_meta('disable_customer_emails') && $orderData->get_meta('_wc_order_attribution_source_type') !== 'admin') {
                    echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
                } else {
                    //echo '<td data-title="Actions" style="text-align:center;">-</td>';
                    echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button ">Pay Now</a></td>';
                }
            } elseif (($orderData->has_status('completed') || $orderData->has_status('processing')) && $orderData->is_paid()) {
                echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($download_url) . '" class=""><img src="' . get_stylesheet_directory_uri() . '/images/invoice.png" class="inv-icon" title="Download Receipt"></a></td>';
            } else {
                echo '<td data-title="Actions" style="text-align:center;">-</td>';
            }
        } else {
            echo '<td data-title="Total">' . $order['total'] . '</td>';
            echo '<td data-title="Actions" style="text-align:center;">-</td>';
        }


        echo '</tr>';
    }

    echo '</tbody></table>';

    if ($i == 0) {
        echo 'No Booking Found<br> 
            <style>
                #table_child_' . $title_slug . '_0{display:none;}
            </style>
        ';
    }
    //echo $i;

}

function custom_render_orders_section_all($title, $orders)
{
    echo '<h4 class="order-heading">' . esc_html($title) . '</h4>';

    if (empty($orders)) {
        echo 'No Booking Found<br>';
        return;
    }

    $title_slug = strtolower($title);
    $title_slug = preg_replace('/[^a-z0-9]+/', '-', $title_slug);
    $title_slug = trim($title_slug, '-');

    echo '<table id="table_parent_' . $title_slug . '" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">';
    echo '<thead>
            <tr>
                <th>' . __('Ref. No.', 'woocommerce') . '</th>
                <th>' . __('Details', 'space') . '</th>
                <th>' . __('Booking Status', 'woocommerce') . '</th>
                <th>' . __('Payment Status', 'woocommerce') . '</th>
                <th>' . __('Amount', 'woocommerce') . '</th>
                <th style="text-align:center;">' . __('Actions', 'woocommerce') . '</th>
            </tr>
        </thead>';
    echo '<tbody>';

    foreach ($orders as $order) {
        $order_id = $order['order_id'];
        $orderData = wc_get_order($order_id);

        $parentOrderData = null;
        if ($orderData->get_meta('group_parent_order')) {
            $parent_order_id = $orderData->get_meta('group_parent_order');
            $parentOrderData = wc_get_order($parent_order_id);
        }

        $invoice_order_id = $order_id;
        $download_url = wp_nonce_url(
            add_query_arg('download_invoice', $invoice_order_id, wc_get_account_endpoint_url('view-order')),
            'download_invoice_' . $invoice_order_id
        );

        // Product name
        $phive_product_name = '-';
        foreach ($orderData->get_items() as $item) {
            $product = $item->get_product();
            if ($product && $product->get_type() === 'phive_booking') {
                $phive_product_name = $product->get_name();
                break;
            }
        }

        $my_bookings = get_bookings_list_from_order($order['order_number']);

        $from_ts = strtotime($order['from_date']);
        $to_ts = strtotime($order['to_date']);
        $booking_display = date('F jS Y', $from_ts) . ' (' . date('gA', $from_ts) . ' to ' . date('gA', $to_ts) . ')';

        // Payment type
        if ($orderData->get_meta('group_payment_mode') == 'group') {
            $payment_type = "Split: Parent";
        } elseif ($orderData->get_meta('group_parent_order')) {
            $payment_type = "Split: Child";
        } else {
            $payment_type = "Regular";
        }

        $status_class = 'woocommerce-orders-table__row--status-' . esc_attr($order['order_status']);
        echo '<tr class="' . $status_class . '">';
        echo '<td data-title="Order"><a href="' . esc_url($order['order_url']) . '">' . esc_html($order['order_number']) . '</a></td>';
        echo '<td data-title="Booking">' . $my_bookings . '</td>';
        echo '<td data-title="Booking Status">' . get_booking_status($order['order_number']) . '</td>';
        echo '<td data-title="Booking Type">' . verify_razorpay_payment($order_id) . '</td>';

        // Amount column with tooltip if group booking
        $total_display = $order['total'];
        if ($parentOrderData || $orderData->get_meta('group_payment_mode') == 'group') {
            $group_total = $parentOrderData ? $parentOrderData->get_meta("group_original_total") : $orderData->get_meta("group_original_total");
            $total_display = "<div class='gp_div'>{$order['total']}
                <span class='tooltip_box'>
                    <span class='tooltip_i'>i</span>
                    <span class='tooltip_box_hover' style='display:none;'>
                        This amount is your share of the total booking cost of " . wc_price($group_total) . ", which is equally distributed among all parties.
                    </span>
                </span>
            </div>";
        }
        echo '<td data-title="Total">' . $total_display . '</td>';

        // Actions column
        if ($orderData->has_status('pending') && !$orderData->is_paid()) {

            if ('yes' !== $orderData->get_meta('disable_customer_emails') && $orderData->get_meta('_wc_order_attribution_source_type') !== 'admin') {
                echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button">Pay Now</a></td>';
            } else {
                //echo '<td data-title="Actions" style="text-align:center;">-</td>';
                echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($orderData->get_checkout_payment_url()) . '" class="woocommerce-button button">Pay Now</a></td>';
            }
        } elseif (($orderData->has_status('completed') || $orderData->has_status('processing')) && $orderData->is_paid()) {
            echo '<td data-title="Actions" style="text-align:center;"><a href="' . esc_url($download_url) . '"><img src="' . get_stylesheet_directory_uri() . '/images/invoice.png" class="inv-icon" title="Download Receipt"></a></td>';
        } else {
            echo '<td data-title="Actions" style="text-align:center;">-</td>';
        }

        echo '</tr>';
    }

    echo '</tbody></table>';
}



// Shortcode: [search_rooms]
function shortcode_search_rooms()
{
    ob_start(); // Start output buffering

    $file = get_stylesheet_directory() . '/search-rooms-bar.php';
    if (file_exists($file)) {
        include $file;
    }

    return ob_get_clean(); // Return buffered output
}
add_shortcode('search_rooms', 'shortcode_search_rooms');


// Ajax to search rooms
add_action('wp_ajax_search_rooms', 'search_rooms');
add_action('wp_ajax_nopriv_search_rooms', 'search_rooms');

function search_rooms()
{
    global $wpdb;

    // 1. Sanitize Inputs
    $sf_date = sanitize_text_field($_POST["sf_date"]);
    $sf_slot = sanitize_text_field($_POST["sf_slot"]);
    $sf_participants = intval($_POST["sf_participants"]);

    if ($sf_participants > 15) {
        echo "<p class='extend_partic'>Sorry, we can host up to 15 participants for now. Bigger spaces are on the way!</p>";
        wp_die();
    }

    // 2. Prepare the "Block List" Subquery Logic
    $subquery_booked = "
        SELECT product_id 
        FROM {$wpdb->prefix}ph_bookings_availability_calculation_data
        WHERE booking_status != 'canceled'
          AND booking_type != 'cart'
          AND woocommerce_order_status != 'cancelled'
          AND (
              booked_date < %s 
              AND 
              booked_date_end > %s
          )
    ";

    // 3. Determine Which Slots to Check
    $params = [$sf_participants];
    $where_availability_logic = "";

    if (empty($sf_slot)) {
        // === SCENARIO: CHECK IF ANY OF THE THREE SLOTS IS FREE ===

        $s1_start = $sf_date . ' 09:30:00';
        $s1_end = $sf_date . ' 13:30:00';

        $s2_start = $sf_date . ' 14:00:00';
        $s2_end = $sf_date . ' 18:00:00';

        $s3_start = $sf_date . ' 18:30:00';
        $s3_end = $sf_date . ' 22:30:00';

        // UPDATED LOGIC: Add a third OR condition for Slot 3
        $where_availability_logic = "
            AND (
                p.ID NOT IN ($subquery_booked) -- Check Slot 1
                OR
                p.ID NOT IN ($subquery_booked) -- Check Slot 2
                OR
                p.ID NOT IN ($subquery_booked) -- Check Slot 3
            )
        ";

        // Correctly push parameters for 3 subqueries (Each needs: End, Start)
        array_push($params, $s1_end, $s1_start, $s2_end, $s2_start, $s3_end, $s3_start);

    } else {
        // === SCENARIO: SPECIFIC SLOT SELECTED ===
        $search_start = $sf_date . ' ' . $sf_slot . ':00';

        if ($sf_slot == '09:30') {
            $search_end = $sf_date . ' 13:30:00';
        } elseif ($sf_slot == '14:00') {
            $search_end = $sf_date . ' 18:00:00';
        } elseif ($sf_slot == '18:30') {
            $search_end = $sf_date . ' 22:30:00';
        } else {
            // Fallback for custom times
            $search_end = date('Y-m-d H:i:s', strtotime($search_start . ' +4 hours'));
        }

        $where_availability_logic = "AND p.ID NOT IN ($subquery_booked)";
        array_push($params, $search_end, $search_start);
    }

    // 4. Run the Main Query
    $query = $wpdb->prepare("
        SELECT p.ID, p.post_title, meta.meta_value as capacity
        FROM {$wpdb->prefix}posts AS p
        INNER JOIN {$wpdb->prefix}term_relationships AS tr ON (p.ID = tr.object_id)
        INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        INNER JOIN {$wpdb->prefix}terms AS t ON (tt.term_id = t.term_id)
        INNER JOIN {$wpdb->prefix}postmeta AS meta ON (p.ID = meta.post_id)
        WHERE p.post_type = 'product'
          AND p.post_status = 'publish'
          AND t.slug = 'rooms'
          AND tt.taxonomy = 'product_cat'
          AND meta.meta_key = '_guests'
          AND CAST(meta.meta_value AS UNSIGNED) >= %d
          $where_availability_logic
        GROUP BY p.ID
    ", $params);

    $available_products = $wpdb->get_results($query, ARRAY_A);

    if (empty($available_products)) {
        echo "<p class='extend_partic'>No rooms available for the selected date and time slot. Please search for another date and time slot.</p>";
        wp_die();
    }

    wp_send_json([
        'total_available' => count($available_products),
        'available_products' => $available_products
    ]);

    wp_die();
}

add_filter('phive_booking_calculated_price', function ($price, $product_id, $booking_data) {
    return $price;
}, 10, 3);



/* ---------------------------------------------------------
   ADMIN MEETING ADD-ONS: Meta-Based Storage
   --------------------------------------------------------- */

// 1. Add Meta Box
add_action('add_meta_boxes', 'add_meeting_addons_meta_box');
function add_meeting_addons_meta_box()
{
    $screens = ['shop_order', 'woocommerce_page_wc-orders'];
    foreach ($screens as $screen) {
        add_meta_box(
            'meeting_addons_box',
            'Meeting Add-ons',
            'render_meeting_addons_box',
            $screen,
            'normal',
            'high'
        );
    }
}

// 2. Render Meta Box HTML
function render_meeting_addons_box($post)
{
    if ($post instanceof WC_Order) {
        $order_id = $post->get_id();
    } else {
        $order_id = $post->ID;
    }
    $order = wc_get_order($order_id);

    // A. Fetch Products & Group by Category
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'addons',
            ),
        ),
    );
    $raw_products = get_posts($args);
    $grouped_products = [];

    foreach ($raw_products as $p_post) {
        $product = wc_get_product($p_post->ID);
        $terms = get_the_terms($p_post->ID, 'product_cat');
        $cat_name = 'Other';

        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if ($term->slug !== 'addons') {
                    $cat_name = $term->name;
                    break;
                }
            }
        }
        $grouped_products[$cat_name][] = $product;
    }
    ksort($grouped_products);
    $parent_cat_slug = 'addons';
    $parent_term = get_term_by('slug', $parent_cat_slug, 'product_cat');

    if (!$parent_term) {
        wp_send_json_error('Addons category not found');
    }
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

    $saved_coupon = $order->get_meta('_meeting_addon_coupon');
    $coupon_display = $saved_coupon ? $saved_coupon : 'No coupon selected';
    $btn_text = $saved_coupon ? 'Change Coupon' : 'Add Discount Coupon';
    $invoice_ids = $order->get_meta('meeting_addons_billed');
    ?>
    <style>
        /* (Styles preserved from your original code) */
        #meeting-addons-wrapper {
            background: #fff;
            padding: 0;
        }

        .addon-selection-area {
            border: 1px solid #c3c4c7;
            margin-bottom: 20px;
        }

        #addon-products-list {
            max-height: 250px;
            overflow-y: auto;
            background: #fff;
            border-bottom: 1px solid #c3c4c7;
        }

        .addon-cat-header {
            background: #f9f9f9;
            padding: 8px 12px;
            font-weight: 700;
            color: #2271b1;
            border-bottom: 1px solid #eee;
            border-top: 1px solid #eee;
        }

        .addon-item-row {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-bottom: 1px solid #f0f0f1;
        }

        .addon-item-row:hover {
            background: #f6f7f7;
        }

        .addon-col-check {
            width: 40px;
        }

        .addon-col-name {
            flex-grow: 1;
            font-weight: 600;
            color: #1d2327;
        }

        .addon-col-price {
            width: 80px;
            text-align: right;
            margin-right: 15px;
            color: #50575e;
        }

        .addon-col-qty {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
        }

        .addon-col-qty input {
            width: 50px !important;
            text-align: center;
        }

        .addon-actions-bar {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: end;
            padding: 10px 12px;
            background: #f0f0f1;
            gap: 10px;
        }

        label[for="adds_remark"] {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        table.addons-group-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 15px;
        }

        table.addons-group-table th {
            background: #f0f0f1;
            color: #1d2327;
            font-weight: 600;
            text-align: left;
            padding: 10px 15px;
            border-bottom: 1px solid #c3c4c7;
        }

        tr.addons-group-header td {
            background: #e5e5e5;
            color: #1d2327;
            padding: 8px 15px;
            border-bottom: 1px solid #dcdcde;
            font-size: 13px;
        }

        .batch-id {
            font-weight: 700;
            margin-right: 10px;
        }

        .batch-meta {
            font-size: 12px;
            color: #000;
            margin-right: 10px;
        }

        tr.addons-item-row td {
            background: #fff;
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f1;
            vertical-align: middle;
        }

        table.addons-group-table tfoot tr td {
            padding: 5px 15px;
        }

        table.addons-group-table tfoot tr:first-child td {
            padding-top: 20px;
        }

        table.addons-group-table tfoot tr:last-child td {
            padding-bottom: 20px;
        }

        tr.addons-item-row.billed-row td {
            background: #fcfcfc;
            color: #000;
        }

        select.addon-status-select {
            font-size: 11px;
            padding: 2px 20px 2px 8px;
            min-height: 25px;
            border-radius: 3px;
            border: 1px solid #8c8f94;
            background: #fff;
            color: #333;
            cursor: pointer;
            line-height: 1;
        }

        select.addon-status-select.status-order_placed {
            border-color: #2271b1;
            color: #2271b1;
            font-weight: 600;
        }

        select.addon-status-select.status-in_progress {
            border-color: #d63638;
            color: #d63638;
        }

        select.addon-status-select.status-completed {
            border-color: #00a32a;
            color: #00a32a;
            background: #edfaef;
        }

        .finalize-actions {
            margin-top: 20px;
            padding-top: 15px;
            text-align: right;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
        }
    </style>
    <input type="hidden" id="admin_total_member" value="<?php echo esc_attr($total_member); ?>">
    <input type="hidden" id="admin_order_id" value="<?php echo esc_attr($order_id); ?>">
    <div id="admin-member-input-overlay" style="display:none;">
        <div class="member-input-box">
            <h3>Total Participants?</h3>
            <p>Please enter the number of participants to use "Select for All".</p>
            <input type="number" id="manual_member_input" value="" min="1" step="1" placeholder="0">
            <div style="margin-top:15px;">
                <button type="button" id="set_member_count_action" class="button button-primary">Add Members</button>
                <button type="button" id="cancel_member_count" class="button">Cancel</button>
            </div>
        </div>
    </div>

    <div id="meeting-addons-table-container">
        <?php echo get_meeting_addons_table_html($order_id); ?>
    </div>
    <div id="meeting-addons-wrapper">
        <div class="addon-selection-area" style="display: none;">
            <?php if (!empty($grouped_products)): ?>
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
                                    $price = $product->get_regular_price();
                                    $qty = isset($existing_addons[$product_id]) ? $existing_addons[$product_id] : 0;

                                    $max_attr = ($product_id == 3015) ? 'max="1"' : 'max="100"';

                                    // If member count is 0, display 0, otherwise display count
                                    $max_display = ($product_id == 3015) ? '1' : ($total_member > 0 ? $total_member : 0);

                                    $class = ($product_id == 3015) ? 'operator' : 'dynamic-max';

                                    if ($product->get_name() != 'Printing') {
                                        echo '<tr class="service-row" data-price="' . esc_attr($price) . '">';

                                        echo '<td>' . esc_html($product->get_name());
                                        if ($product->get_description()) {
                                            echo '<div class="service_desc">' . $product->get_description() . '</div>';
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
                                    <div class="rem-box"><span class="rem-label">Add Remarks:</span>
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

                                // foreach ($saved_cart as $cart_item) {
                                //     $cart_product_id = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
                                //     if ($cart_product_id == $product_id) {
                                //         $existing_qty = $cart_item['quantity'];
                                //         break;
                                //     }
                                // }
                

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

                        <tr class="category-row">
                            <td colspan="5" style="font-weight:600;">Miscellaneous</td>
                        </tr>
                        <tr class="service-row misc-row">
                            <td><input type="text" class="misc_name" value="" name="misc_name" id="misc_name"
                                    placeholder="Enter Product Name">
                            </td>
                            <td>₹ <input type="number" name="misc_price" id="misc_price" value=""></td>
                            <td>
                                <input type="number" class="addon-qty" value="0" data-product_id="0" min="0" max="100"
                                    fdprocessedid="wcw4e">
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" class="addon-all">
                                    <span class="slider round dynamic-max"><?php echo $total_member; ?></span>
                                </label>
                            </td>
                            <td class="addon-total">₹0</td>
                        </tr>
                        <tr class="dup_misc">
                            <td colspan="7" style="text-align: right; font-size: 14px;"><a class="plus" href=""
                                    style="margin-left: 10px;"><span class="dashicons dashicons-insert"></span></a><a
                                    class="minus" href="" style="margin-left: 10px;"><span
                                        class="dashicons dashicons-remove"></span></a></td>
                        </tr>
                    </tbody>
                </table>

                <div class="addon-actions-bar">
                    <label for="adds_remark">Additional Remarks
                        <textarea name="adds_remark" id="adds_remark"></textarea>
                    </label>
                    <div style="float: right;">
                        <span class="spinner" id="addon-spinner" style="float:none; margin: 0 10px;"></span>
                        <button type="button" class="button button-primary" id="btn-add-admin-addons"
                            data-order-id="<?php echo esc_attr($order_id); ?>">
                            Add Items
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <p style="padding:15px; color:#d63638;">No products found in "addons" category.</p>
            <?php endif; ?>
        </div>


        <?php

        if (!empty($invoice_ids) && is_array($invoice_ids)) { ?>
            <div class="finalize-actions">
                <div class="coupon-section">
                    <span style="font-weight:600; margin-right:10px;">Discount Applied:</span>
                    <span id="active_coupon_display"
                        style="color: #2271b1; font-weight:bold; margin-right:15px;"><?php echo esc_html($coupon_display); ?></span>
                    <button type="button" class="button button-secondary" id="open_coupon_modal"
                        disabled><?php echo $btn_text; ?></button>
                </div>
                <button type="button" class="button button-large" id="btn-finalize-bill"
                    data-order-id="<?php echo esc_attr($order_id); ?>" disabled>
                    End Meeting & Send Invoices
                </button>
            </div>
        <?php } else { ?>
            <div class="finalize-actions">
                <div class="coupon-section">
                    <span style="font-weight:600; margin-right:10px;">Discount:</span>
                    <span id="active_coupon_display"
                        style="color: #2271b1; font-weight:bold; margin-right:15px;"><?php echo esc_html($coupon_display); ?></span>
                    <button type="button" class="button button-secondary"
                        id="open_coupon_modal"><?php echo $btn_text; ?></button>
                </div>
                <button type="button" class="button button-large" id="btn-finalize-bill"
                    data-order-id="<?php echo esc_attr($order_id); ?>">
                    End Meeting & Send Invoices
                </button>
            </div>
        <?php }
        ?>

        <div id="coupon_modal"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
            <div
                style="background:#fff; width:400px; padding:20px; border-radius:5px; box-shadow:0 0 10px rgba(0,0,0,0.3);">
                <h3 style="margin-top:0;">Select a Coupon</h3>
                <div id="coupon_list_container"
                    style="max-height:300px; overflow-y:auto; border:1px solid #eee; margin-bottom:15px;">
                    <p style="padding:10px;">Loading...</p>
                </div>
                <div style="text-align:right;">
                    <button type="button" class="button" id="close_coupon_modal">Cancel</button>
                    <button type="button" class="button button-primary" id="save_selected_coupon">Apply Coupon</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            // A. Toggle Qty
            $(document).on('change', '.addon-checkbox', function () {
                var row = $(this).closest('.addon-item-row');
                var qtyInput = row.find('.addon-qty-input');
                qtyInput.prop('disabled', !$(this).is(':checked'));
                if ($(this).is(':checked')) qtyInput.focus();
            });

            // B. Add Items
            $('#btn-add-admin-addons').on('click', function (e) {
                e.preventDefault();

                var btn = $(this);
                var orderId = btn.data('order-id');
                var adds_remark = $('#adds_remark').val();
                var items = [];

                // 1. Iterate through every service row
                $('.service-row').each(function () {
                    var row = $(this);
                    var qtyInput = row.find('.addon-qty');
                    var qty = parseInt(qtyInput.val()) || 0;

                    // Only collect if Quantity > 0
                    if (qty > 0) {
                        var productId = qtyInput.data('product_id');

                        // 2. Find the associated remark
                        // The remark is in the next '.remarks-row' following this service row
                        var remarkRow = row.nextAll('.remarks-row').first();
                        var remarkText = remarkRow.find('textarea.addon-remark').val();
                        var pName = row.find('input#misc_name').val() || '';
                        var pPrice = parseInt(row.find('input#misc_price').val()) || 0;

                        items.push({
                            product_id: productId,
                            product_name: pName,
                            product_price: pPrice,
                            qty: qty,
                            remark: remarkText // Pass the category-specific remark
                        });
                    }
                });

                // 3. Validation
                if (items.length === 0) {
                    alert('Please select at least one product (Qty > 0).');
                    return;
                }

                // 4. Send Request
                $('#addon-spinner').addClass('is-active');
                btn.prop('disabled', true);

                $.post(ajaxurl, {
                    action: 'add_admin_addon_items',
                    parent_order_id: orderId,
                    items: items,
                    adds_remark: adds_remark,
                    security: '<?php echo wp_create_nonce("admin_addon_action"); ?>'
                }, function (response) {
                    $('#addon-spinner').removeClass('is-active');
                    btn.prop('disabled', false);

                    if (response.success) {
                        // Refresh the table HTML
                        if (response.data.html) {
                            $('#meeting-addons-table-container').html(response.data.html);
                        }

                        // Reset inputs
                        $('.addon-qty').val(0);
                        $('.addon-all').prop('checked', false);
                        $('.addon-remark').val('');

                        $('.addon-selection-area').toggle();

                        var targetBox = $('div#meeting-addons-table-container');
                        if (targetBox.length) {
                            $('html, body').animate({
                                scrollTop: targetBox.offset().top - 50
                            }, 600);
                        }


                        // Optional: Show success message
                        // alert('Items added successfully');
                    } else {
                        alert(response.data.message || 'Error adding items');
                    }
                }).fail(function () {
                    $('#addon-spinner').removeClass('is-active');
                    btn.prop('disabled', false);
                    alert('Server error occurred.');
                });
            });

            // C. Update Item Status
            $(document).on('change', '.addon-status-select', function () {
                var select = $(this);
                var rowId = select.data('row-id'); // Unique ID in meta array
                var orderId = select.data('order-id');
                var newStatus = select.val();

                select.prop('disabled', true).css('opacity', '0.5');

                $.post(ajaxurl, {
                    action: 'update_addon_item_status',
                    order_id: orderId,
                    row_id: rowId,
                    status: newStatus,
                    security: '<?php echo wp_create_nonce("admin_addon_action"); ?>'
                }, function (response) {
                    select.prop('disabled', false).css('opacity', '1');
                    if (response.success) {
                        select.removeClass('status-order_placed status-in_progress status-completed')
                            .addClass('status-' + newStatus);
                    } else {
                        alert('Failed to update status.');
                    }
                });
            });

            // D. Finalize
            $('#btn-finalize-bill').on('click', function (e) {
                e.preventDefault();
                if (!confirm('Generate invoice for all unbilled items?')) return;

                var orderId = $(this).data('order-id');
                var btn = $(this);
                btn.prop('disabled', true).text('Processing...');

                $.post(ajaxurl, {
                    action: 'finalize_meeting_bill',
                    parent_order_id: orderId,
                    security: '<?php echo wp_create_nonce("admin_addon_action"); ?>'
                }, function (response) {
                    btn.prop('disabled', false).text('End Meeting & Send Invoices');
                    if (response.success) {
                        alert(response.data.message);
                        location.reload(); // Refresh to show items as "Billed"
                    } else {
                        alert(response.data.message);
                    }
                });
            });
        });
    </script>
    <?php
}

// 3. Helper: Generate Table (From Meta)
function get_meeting_addons_table_html($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order)
        return '';


    $invoice_ids = $order->get_meta('meeting_addons_billed');

    // 1. Retrieve & Sort Data
    $addons_data = $order->get_meta('_meeting_addons_data');
    if (!is_array($addons_data))
        $addons_data = [];

    usort($addons_data, function ($a, $b) {
        return $b['timestamp'] <=> $a['timestamp'];
    });

    // 2. Calculate Pending Totals (for the "To Bill" section)
    $grand_total_pending = 0;
    $grand_total_billed = 0;
    foreach ($addons_data as $row) {
        // Only sum if NOT billed and NOT cancelled
        if ((!isset($row['billed_status']) || $row['billed_status'] !== 'billed') && (!isset($row['status']) || $row['status'] !== 'cancelled')) {
            $grand_total_pending += floatval($row['line_total']);
        }
        if ((isset($row['billed_status']) && $row['billed_status'] === 'billed') && (!isset($row['status']) || $row['status'] !== 'cancelled')) {
            $grand_total_billed += floatval($row['line_total']);
        }
    }

    // 3. Group by Batch
    $batches = [];
    foreach ($addons_data as $row) {
        $batches[$row['batch_id']][] = $row;
    }

    ob_start();

    // --- SECTION A: ITEMS TABLE ---
    if (empty($addons_data)) {
        echo '<div style="padding:20px; background:#f0f0f1; border:1px solid #c3c4c7; text-align:center;">No add-ons added yet.</div>';
    } else {
        // echo "<pre>";
        // print_r($batches);
        // echo "<pre>";
        // return;
        ?>
        <h3 style="margin-bottom: 10px;">Items Ordered</h3>
        <table class="addons-group-table"
            style="width:100%; border-collapse:collapse; margin-bottom:20px; border:1px solid #c3c4c7;">
            <thead style="background:#f6f7f7;">
                <tr>
                    <th style="text-align:left; padding:10px;">Category</th>
                    <th style="text-align:left; padding:10px;">Item Name</th>
                    <th style="text-align:center; width:60px;">Quantity</th>
                    <th style="text-align:center; width:60px;">Rate/Item</th>
                    <th style="text-align:center; width:120px;">Status</th>
                    <th style="text-align:left; padding:10px;">Time</th>
                    <th style="text-align:left; padding:10px;">Source</th>
                    <th style="text-align:left; padding:10px;">Additional Remark</th>
                    <th style="text-align:right; padding:10px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batches as $batch_id => $rows):
                    // Helper vars for rowspans
                    $rowspans = [];
                    $prev_cat = null;
                    $start_index = 0;
                    foreach ($rows as $i => $row) {
                        $terms = get_the_terms($row['product_id'], 'product_cat');
                        $cat_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : '';
                        if ($cat_name !== $prev_cat) {
                            $rowspans[$i] = 1;
                            $start_index = $i;
                        } else {
                            $rowspans[$start_index]++;
                        }
                        $prev_cat = $cat_name;
                    }

                    // Render Rows
                    foreach ($rows as $index => $row):
                        $is_billed = (isset($row['billed_status']) && $row['billed_status'] === 'billed');
                        $row_class = $is_billed ? 'addons-item-row billed-row' : 'addons-item-row';
                        $status = isset($row['status']) ? $row['status'] : 'order_placed';

                        // Style: Billed rows look "disabled"
                        $style = $is_billed ? 'background-color:#fafafa; color:#888;' : 'background-color:#fff;';

                        $terms = get_the_terms($row['product_id'], 'product_cat');
                        $cat_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : 'Miscellaneous';
                        ?>
                        <tr class="<?php echo esc_attr($row_class); ?>" style="<?php echo $style; ?> border-bottom:1px solid #eee;">

                            <?php if (isset($rowspans[$index])): ?>
                                <td rowspan="<?php echo $rowspans[$index]; ?>"
                                    style="vertical-align:top; padding:10px; border-right:1px solid #eee;">
                                    <b><?php echo esc_html($cat_name); ?></b>
                                    <?php if (!empty($row['remark'])): ?>
                                        <br><small style="color:#999;">Remark:
                                            <?php echo esc_html($row['remark']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>

                            <td style="padding:10px;">

                                <?php
                                $product_id = $row['product_id'];
                                // Get brand terms
                                $brands = get_the_terms($product_id, 'product_brand');

                                if (!empty($brands) && !is_wp_error($brands)) {
                                    $brand = $brands[0];

                                    // Get brand image (term meta)
                                    $brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);

                                    if ($brand_image_id) {
                                        echo $brand_image = wp_get_attachment_image($brand_image_id, 'large', false, [
                                            'style' => 'width:35px;height:auto;margin-right:5px;vertical-align:middle;border-radius: 2px;'
                                        ]);
                                    }
                                }
                                ?>

                                <?php echo esc_html($row['product_name']); ?>
                            </td>

                            <td style="text-align:center;"><?php echo esc_html($row['qty']); ?></td>
                            <td style="text-align:center;"><?php echo wc_price($row['price']); ?></td>
                            <td style="text-align:center;">
                                <?php if ($is_billed): ?>
                                    <span
                                        style="background:#e5e5e5; color:#666; padding:3px 6px; border-radius:3px; font-size:10px;">BILLED</span>
                                <?php elseif ($status === 'xcancelled'): ?>
                                    <span
                                        style="background:#ffecec; color:#a00; padding:3px 6px; border-radius:3px; font-size:10px;">CANCELLED</span>
                                <?php else: ?>
                                    <select class="addon-status-select status-<?php echo esc_attr($status); ?>"
                                        data-row-id="<?php echo esc_attr($row['id']); ?>" data-order-id="<?php echo esc_attr($order_id); ?>"
                                        style="font-size:12px; height:24px; min-height:24px;">
                                        <option value="order_placed" <?php selected($status, 'order_placed'); ?>>Placed</option>
                                        <option value="in_progress" <?php selected($status, 'in_progress'); ?>>In Progress</option>
                                        <option value="completed" <?php selected($status, 'completed'); ?>>Completed</option>
                                        <option value="cancelled" <?php selected($status, 'cancelled'); ?>>Cancelled</option>
                                    </select>
                                <?php endif; ?>
                            </td>
                            <?php if ($index === 0): ?>
                                <td style="padding:10px; font-size:12px;" rowspan="<?php echo count($rows); ?>">
                                    <?php echo date('h:i A', $row['timestamp']); ?>
                                </td>
                                <td style="padding:10px; font-size:12px;" rowspan="<?php echo count($rows); ?>">
                                    <span style="color:#000;"><?php echo isset($row['added_by']) ? $row['added_by'] : ''; ?></span>
                                </td>
                                <td style="padding:10px; font-size:12px;" rowspan="<?php echo count($rows); ?>">
                                    <span style="color:#000;"><?php echo isset($row['adds_remark']) ? $row['adds_remark'] : ''; ?></span>
                                </td>
                            <?php endif; ?>
                            <td style="text-align:right; padding:10px;">
                                <?php echo wc_price($row['line_total']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>

            <?php if ($grand_total_pending > 0):
                $saved_coupon_code = $order->get_meta('_meeting_addon_coupon');
                $discount_amount = 0;
                if (!empty($saved_coupon_code)) {
                    $coupon = new WC_Coupon($saved_coupon_code);
                    if ($coupon->get_id()) {
                        $discount_amount = ($coupon->get_discount_type() === 'percent')
                            ? $grand_total_pending * ($coupon->get_amount() / 100)
                            : $coupon->get_amount();
                    }
                }
                if ($discount_amount > $grand_total_pending)
                    $discount_amount = $grand_total_pending;
                $taxable = $grand_total_pending - $discount_amount;
                $tax = $taxable * 0.18;
                $final = $taxable + $tax;
                ?>
                <tfoot style="background:#fdfdfd; border-top:2px solid #ddd;">
                    <tr>
                        <td colspan="8" style="text-align:right; padding:10px; font-weight:bold;">Total Add-Ons Price:</td>
                        <td style="text-align:right; padding:10px;font-weight:bold;"><?php echo wc_price($grand_total_pending); ?>
                        </td>
                    </tr>
                    <?php if ($discount_amount > 0): ?>
                        <tr>
                            <td colspan="8" style="text-align:right; padding:5px 10px;">Discount
                                (<?php echo esc_html($coupon->get_amount()); ?>%):</td>
                            <td style="text-align:right; padding:5px 10px;">-<?php echo wc_price($discount_amount); ?></td>
                        </tr>
                        <tr>
                            <td colspan="8" style="text-align:right; padding:5px 10px; font-weight:bold;">Total Amount:</td>
                            <td style="text-align:right; padding:5px 10px; font-weight:bold;">
                                <?php echo wc_price($grand_total_pending - $discount_amount); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="8" style="text-align:right; padding:5px 10px;">CGST @9%:</td>
                        <td style="text-align:right; padding:5px 10px;">+<?php echo wc_price($tax / 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="8" style="text-align:right; padding:5px 10px;">SGST @9%:</td>
                        <td style="text-align:right; padding:5px 10px;">+<?php echo wc_price($tax / 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="8" style="text-align:right; padding:10px; font-weight:700; color:#000;">Total to Bill:</td>
                        <td style="text-align:right; padding:10px; font-weight:700; color:#000;"><?php echo wc_price($final); ?>
                        </td>
                    </tr>
                </tfoot>
            <?php endif; ?>
            <?php if ($grand_total_billed > 0):
                $saved_coupon_code = $order->get_meta('_meeting_addon_coupon');
                $discount_amount = 0;
                if (!empty($saved_coupon_code)) {
                    $coupon = new WC_Coupon($saved_coupon_code);
                    if ($coupon->get_id()) {
                        $discount_amount = ($coupon->get_discount_type() === 'percent')
                            ? $grand_total_billed * ($coupon->get_amount() / 100)
                            : $coupon->get_amount();
                    }
                }
                if ($discount_amount > $grand_total_billed)
                    $discount_amount = $grand_total_billed;
                $taxable = $grand_total_billed - $discount_amount;
                $tax = $taxable * 0.18;
                $final = $taxable + $tax;
                ?>
                <tfoot style="background:#fdfdfd; border-top:2px solid #ddd;">
                    <tr>
                        <td colspan="8" style="text-align:right; padding:10px; font-weight:bold;">Total Add-Ons Price:</td>
                        <td style="text-align:right; padding:10px;font-weight:bold;">
                            <?php echo wc_price($grand_total_billed); ?>
                        </td>
                    </tr>
                    <?php if ($discount_amount > 0): ?>
                        <tr>
                            <td colspan="8" style="text-align:right; padding:5px 10px;">Discount
                                (
                                <?php echo esc_html($coupon->get_amount()); ?>%):
                            </td>
                            <td style="text-align:right; padding:5px 10px;">-
                                <?php echo wc_price($discount_amount); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" style="text-align:right; padding:5px 10px; font-weight:bold;">Total Amount:</td>
                            <td style="text-align:right; padding:5px 10px; font-weight:bold;">
                                <?php echo wc_price($grand_total_billed - $discount_amount); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="8" style="text-align:right; padding:5px 10px;">CGST @9%:</td>
                        <td style="text-align:right; padding:5px 10px;">+
                            <?php echo wc_price($tax / 2); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8" style="text-align:right; padding:5px 10px;">SGST @9%:</td>
                        <td style="text-align:right; padding:5px 10px;">+
                            <?php echo wc_price($tax / 2); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8" style="text-align:right; padding:10px; font-weight:700; color:#000;">Total Amount Billed:
                        </td>
                        <td style="text-align:right; padding:10px; font-weight:700; color:#000;">
                            <?php echo wc_price($final); ?>
                        </td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>

        <?php
    }
    if ((empty($invoice_ids) || !$invoice_ids) && !$order->has_status('cancelled')) { ?>
        <button id="toggle-addon" class="button button-primary button-large" style="margin: 10px 0;">Add Add-ons</button>
    <?php }
    // --- SECTION B: GENERATED INVOICES HISTORY (The new request) ---


    if (!empty($invoice_ids) && is_array($invoice_ids)) {
        ?>
        <div style="margin-top: 30px; border: 1px solid #dcdcde; background: #fff; ">
            <div class="flex"
                style="background: #f0f0f1;border-bottom: 1px solid #dcdcde;display: flex;align-items: center;justify-content: space-between;padding: 10px 15px;">
                <h3 style="font-size: 14px; margin: 0;">
                    Generated Add-ons Invoices (Billed History)
                </h3>
                <button type="button" class="button-primary" id="view-bill" style="box-shadow: none;">View Details</button>
            </div>
            <table class="wp-list-table widefat striped" style="border:none; box-shadow:none;display:none">
                <thead>
                    <tr>
                        <th style="font-weight:600; text-align:left;">Invoice #</th>
                        <th style="font-weight:600; text-align:left;">Billed To (User)</th>
                        <th style="font-weight:600; text-align:center;">Date</th>
                        <th style="font-weight:600; text-align:center;">Status</th>
                        <th style="font-weight:600; text-align:right;">Amount</th>
                        <th style="font-weight:600;text-align: center;">Actions</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    $has_invoices = false;
                    foreach ($invoice_ids as $inv_id) {
                        $inv = wc_get_order($inv_id);
                        if (!$inv)
                            continue;
                        $has_invoices = true;
                        $upload_dir = wp_upload_dir();
                        $inv_url = $upload_dir['baseurl'] . '/invoice_' . $order_id . '_' . $inv_id . '.pdf';
                        $rec_url = $upload_dir['baseurl'] . '/receipt_' . $order_id . '_' . $inv_id . '.pdf';

                        $fname = trim($inv->get_billing_first_name());
                        $lname = trim($inv->get_billing_last_name());

                        // Check if First Name already ends with Last Name (case-insensitive)
                        if (!empty($lname) && strripos($fname, $lname) === (strlen($fname) - strlen($lname))) {
                            $billed_name = $fname; // Use only First Name field since it holds the full name
                        } else {
                            $billed_name = $fname . ' ' . $lname; // Combine normally
                        }
                        $billed_email = $inv->get_billing_email();

                        // Get Status Color
                        $st = $inv->get_status();
                        $status_label = wc_get_order_status_name($st);
                        $color = '#777';
                        if ($st == 'completed')
                            $color = '#00a32a'; // Green
                        if ($st == 'pending')
                            $color = '#dba617'; // Orange
                        if ($st == 'processing')
                            $color = '#2271b1'; // Blue
                        if ($st == 'cancelled')
                            $color = '#d63638'; // Red
                        ?>
                        <tr>
                            <td>
                                <span style="font-weight:bold;">
                                    #<?php echo esc_html($inv_id); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo esc_html($billed_name); ?></strong><br>
                                <small style="color:#666;"><?php echo esc_html($billed_email); ?></small>
                            </td>
                            <td style="text-align:center;">
                                <?php echo $inv->get_date_created()->date('M d, Y'); ?>
                            </td>
                            <td style="text-align:center;">
                                <span class="phive-status <?php echo $st; ?>" style="">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                            </td>
                            <td style="text-align:right; font-weight:bold;">
                                <?php echo $inv->get_formatted_order_total(); ?>
                            </td>
                            <td style="text-align:right; font-weight:bold;text-align: center;">
                                <?php if ($st == 'completed') { ?>
                                    <div class="d-flex" style="display: flex;gap:10px;justify-content: center;">
                                        <a class="button button-secondary button-large " id=""
                                            data-order-id="<?php echo esc_html($inv_id); ?>" href="<?php echo $inv_url ?>"
                                            fdprocessedid="9avpfo" target="_blank">
                                            <span class="dashicons dashicons-download" style="margin-top:7px;"></span> Invoice
                                        </a>
                                        <a class="button button-primary button-large" id=""
                                            data-order-id="<?php echo esc_html($inv_id); ?>" href="<?php echo $rec_url ?>"
                                            fdprocessedid="9avpfo" target="_blank">
                                            <span class="dashicons dashicons-download" style="margin-top:7px;"></span> Receipt
                                        </a>
                                    </div>
                                <?php } else { ?>
                                    <a class="button button-secondary button-large " id=""
                                        data-order-id="<?php echo esc_html($inv_id); ?>" href="<?php echo $inv_url ?>"
                                        fdprocessedid="9avpfo" target="_blank">
                                        <span class="dashicons dashicons-download" style="margin-top:7px;"></span> Invoice
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                    if (!$has_invoices)
                        echo '<tr><td colspan="5" style="text-align:center;">No valid invoices found.</td></tr>';
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    return ob_get_clean();
}

/* ---------------------------------------------------------
   AJAX HANDLERS
   --------------------------------------------------------- */

// 4. AJAX: Add Items (To Meta)
add_action('wp_ajax_add_admin_addon_items', 'add_admin_addon_items');
function add_admin_addon_items()
{
    check_ajax_referer('admin_addon_action', 'security');

    $parent_id = intval($_POST['parent_order_id']);
    $items = $_POST['items'];
    $adds_remark = $_POST['adds_remark'];

    if (!$parent_id || empty($items))
        wp_send_json_error(['message' => 'Invalid data']);

    $order = wc_get_order($parent_id);
    if (!$order)
        wp_send_json_error(['message' => 'Order not found']);

    // Get existing data
    $addons_data = $order->get_meta('_meeting_addons_data');
    if (!is_array($addons_data))
        $addons_data = [];
    $current_wp_time = current_time('timestamp');
    $batch_id = $current_wp_time; // Group these items together

    foreach ($items as $item) {
        $product_id = intval($item['product_id']);
        $qty = intval($item['qty']);
        $remark = $item['remark'];
        $p_name = $item['product_name'];
        $p_price = $item['product_price'];
        $terms = get_the_terms($product_id, 'product_cat');
        if ($product_id && $qty > 0 && $product_id !== 0) {
            $product = wc_get_product($product_id);
            if ($product) {
                // Create a unique entry
                $new_row = [
                    'id' => uniqid('addon_'), // Unique ID for this specific line item
                    'batch_id' => $batch_id,
                    'product_id' => $product_id,
                    'product_category' => ($terms && !is_wp_error($terms)) ? $terms[0]->name : '',
                    'product_name' => $product->get_name(),
                    'qty' => $qty,
                    'price' => $product->get_price(),
                    'line_total' => $product->get_price() * $qty,
                    'status' => 'order_placed', // open, attended, served
                    'billed_status' => 'pending', // pending, billed
                    'timestamp' => $current_wp_time,
                    'added_by' => 'Admin',
                    'remark' => $remark,
                    'adds_remark' => $adds_remark,

                ];
                $addons_data[] = $new_row;
            }
        }
        if ($product_id === 0) {
            $new_row = [
                'id' => uniqid('addon_'), // Unique ID for this specific line item
                'batch_id' => $batch_id,
                'product_id' => $product_id,
                'product_name' => $p_name,
                'qty' => $qty,
                'price' => $p_price,
                'line_total' => $p_price * $qty,
                'status' => 'order_placed', // open, attended, served
                'billed_status' => 'pending', // pending, billed
                'timestamp' => $current_wp_time,
                'added_by' => 'Admin',
                'remark' => $remark,
                'adds_remark' => $adds_remark,

            ];
            $addons_data[] = $new_row;
        }
    }

    $order->update_meta_data('_meeting_addons_data', $addons_data);
    $order->save();

    wp_send_json_success(['html' => get_meeting_addons_table_html($parent_id)]);
}

// 5. AJAX: Update Status (In Meta)
add_action('wp_ajax_update_addon_item_status', 'update_addon_item_status');
function update_addon_item_status()
{
    check_ajax_referer('admin_addon_action', 'security');

    $order_id = intval($_POST['order_id']);
    $row_id = sanitize_text_field($_POST['row_id']);
    $status = sanitize_text_field($_POST['status']);

    $order = wc_get_order($order_id);
    if (!$order)
        wp_send_json_error();

    $addons_data = $order->get_meta('_meeting_addons_data');
    if (!is_array($addons_data))
        wp_send_json_error();

    $found = false;
    foreach ($addons_data as &$row) {
        if ($row['id'] === $row_id) {
            $row['status'] = $status;
            $found = true;
            break;
        }
    }

    if ($found) {
        $order->update_meta_data('_meeting_addons_data', $addons_data);
        $order->save();
        wp_send_json_success();
    }
    wp_send_json_error(['message' => 'Item not found']);
}

function custom_theme_log($message)
{
    if (is_array($message) || is_object($message)) {
        $message = print_r($message, true);
    }

    $log_file = get_stylesheet_directory() . '/debug.log';
    $timestamp = date("Y-m-d H:i:s");

    custom_log("[{$timestamp}] {$message}\n", 3, $log_file);
}


/* ---------------------------------------------------------
   1. CRON JOB: Run Daily at 10:00 AM
--------------------------------------------------------- */
add_action('init', 'phive_ensure_addon_cron_is_scheduled');

function phive_ensure_addon_cron_is_scheduled()
{
    $hook = 'phive_daily_addon_invoicing_event';

    if (!wp_next_scheduled($hook)) {
        $timezone = get_option('timezone_string') ?: 'Asia/Kolkata';

        // Use "tomorrow 10am" to ensure it starts fresh
        $date = new DateTime('tomorrow 10:00:00', new DateTimeZone($timezone));

        wp_schedule_event($date->getTimestamp(), 'daily', $hook);

        // Log the scheduling for debugging
        custom_theme_log("CRON LOG: Event '$hook' was successfully scheduled to start at: " . $date->format('Y-m-d H:i:s'));
    }
}

add_action('phive_daily_addon_invoicing_event', 'phive_cron_process_invoices');
function phive_cron_process_invoices()
{
    custom_theme_log("CRON LOG: Execution started at 10:00 AM.");

    // Use wc_get_orders for better compatibility
    $orders = wc_get_orders([
        'status' => ['processing', 'completed', 'partially-paid'],
        'limit' => -1,
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_meeting_addons_data',
                'compare' => 'EXISTS',
            ],
            [
                'key' => 'meeting_addons_billed',
                'compare' => 'NOT EXISTS',
            ]
        ],
    ]);

    if (empty($orders)) {
        custom_theme_log("CRON LOG: No orders found matching the criteria.");
        return;
    }

    $midnight = strtotime('today midnight');
    $count = 0;

    foreach ($orders as $order) {
        $result = phive_core_process_invoice($order->get_id(), $midnight);
        if ($result['success']) {
            $count++;
        }
    }

    custom_theme_log("CRON LOG: Processed $count orders successfully.");
}

/* ---------------------------------------------------------
   2. AJAX HANDLER: Admin Button Click
--------------------------------------------------------- */
add_action('wp_ajax_finalize_meeting_bill', 'phive_ajax_finalize_meeting_bill');

function phive_ajax_finalize_meeting_bill()
{
    check_ajax_referer('admin_addon_action', 'security');

    $order_id = intval($_POST['parent_order_id']);

    // Run shared logic (No date limit = Bill everything pending)
    $result = phive_core_process_invoice($order_id, null);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}

/* ---------------------------------------------------------
   3. SHARED LOGIC (The Core Function) - ACCURATE TAX RE-CALCULATION
--------------------------------------------------------- */
function phive_core_process_invoice($order_id, $date_limit = null)
{
    $order = wc_get_order($order_id);
    if (!$order)
        return ['success' => false, 'message' => 'Order not found'];

    $addons_data = $order->get_meta('_meeting_addons_data');
    if (empty($addons_data) || !is_array($addons_data))
        return ['success' => false, 'message' => 'No items.'];

    // --- A. CALCULATE BASE TOTALS (Before Discount) ---
    $items_to_bill_ids = [];
    $grouped_items = [];

    $base_net_total = 0;  // Subtotal without Tax
    $base_tax_total = 0;  // Total Tax
    $base_gross_total = 0; // Total with Tax

    $prices_include_tax = wc_prices_include_tax();

    foreach ($addons_data as $row) {
        // Validation Checks
        if (
            (isset($row['billed_status']) && $row['billed_status'] === 'billed') ||
            (isset($row['status']) && $row['status'] === 'cancelled')
        ) {
            continue;
        }
        if ($date_limit && isset($row['timestamp']) && $row['timestamp'] >= $date_limit) {
            continue;
        }

        $items_to_bill_ids[] = $row['id'];

        // Get Product Data
        $product_id = intval($row['product_id']);
        $qty = intval($row['qty']);
        $stored_price = floatval($row['price']);
        $product_name = $row['product_name'];
        $product = wc_get_product($product_id);

        // Calculate Line Totals
        if ($product) {
            $line_net = wc_get_price_excluding_tax($product, array('qty' => $qty, 'price' => $stored_price));
            $line_gross = wc_get_price_including_tax($product, array('qty' => $qty, 'price' => $stored_price));
            $line_tax = $line_gross - $line_net;
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

        // Prepare Display Data for PDF Table
        $display_row_total = $prices_include_tax ? $line_gross : $line_net;

        if (isset($grouped_items[$product_name])) {
            $grouped_items[$product_name]['qty'] += $qty;
            $grouped_items[$product_name]['price'] += $stored_price;
            $grouped_items[$product_name]['display_total'] += $display_row_total;
        } else {
            $grouped_items[$product_name] = [
                'name' => $product_name,
                'price' => $stored_price,
                'qty' => $qty,
                'display_total' => $display_row_total
            ];
        }
    }

    if (empty($items_to_bill_ids)) {
        custom_log('No pending items found to bill.');
        return ['success' => false, 'message' => 'No pending items found.'];
    }

    // --- B. APPLY DISCOUNT & RE-CALCULATE TAX ---
    $saved_coupon_code = $order->get_meta('_meeting_addon_coupon');
    $discount_amount = 0;
    $coupon = null;

    if (!empty($saved_coupon_code)) {
        $coupon = new WC_Coupon($saved_coupon_code);
        if ($coupon->get_id()) {
            // Calculate Discount Amount on the NET Subtotal
            $discount_amount = ($coupon->get_discount_type() === 'percent')
                ? $base_net_total * ($coupon->get_amount() / 100)
                : $coupon->get_amount();
        }
    }

    // Ensure discount doesn't exceed total
    if ($discount_amount > $base_net_total)
        $discount_amount = $base_net_total;

    $final_net_subtotal = $base_net_total - $discount_amount; // The new taxable value
    $final_tax_total = 0;

    // *** THE FIX: Recalculate tax per discounted line item instead of a global ratio ***
    if ($base_net_total > 0) {
        $discount_percentage = $discount_amount / $base_net_total;

        foreach ($addons_data as $row) {
            if (in_array($row['id'], $items_to_bill_ids)) {
                $product_id = intval($row['product_id']);
                $qty = intval($row['qty']);
                $stored_price = floatval($row['price']);
                $product = wc_get_product($product_id);

                // Apply proportional discount to the item's unit price
                $discounted_unit_price = $stored_price - ($stored_price * $discount_percentage);

                if ($product) {
                    $line_net = wc_get_price_excluding_tax($product, array('qty' => $qty, 'price' => $discounted_unit_price));
                    $line_gross = wc_get_price_including_tax($product, array('qty' => $qty, 'price' => $discounted_unit_price));
                    $final_tax_total += ($line_gross - $line_net);
                } else {
                    if ($prices_include_tax) {
                        $line_gross = $discounted_unit_price * $qty;
                        $line_net = $line_gross / 1.18;
                        $final_tax_total += ($line_gross - $line_net);
                    } else {
                        $line_net = $discounted_unit_price * $qty;
                        $line_tax = $line_net * 0.18;
                        $final_tax_total += $line_tax;
                    }
                }
            }
        }
    }

    $final_gross_total = $final_net_subtotal + $final_tax_total; // The total payable

    // --- C. PREPARE PAYERS ---
    $payment_mode = $order->get_meta('group_payment_mode');

    $payers = [];
    $payers[] = [
        'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'email' => $order->get_billing_email(),
        'phone' => $order->get_billing_phone(),
        'user_id' => $order->get_customer_id(),
    ];

    if ($payment_mode === 'group') {
        $additional_payers = $order->get_meta('group_additional_payers');
        if (!empty($additional_payers)) {
            foreach ($additional_payers as $p) {
                $user = get_user_by('email', $p['email']);
                $payers[] = ['name' => $p['name'], 'email' => $p['email'], 'phone' => $p['phone'], 'company' => $p['company'], 'user_id' => $user ? $user->ID : 0];
            }
        }
    }

    // Split the FINAL Gross Total
    $share_amount = $final_gross_total / count($payers);

    // --- D. GENERATE INVOICES ---
    $billed_items_details = array_values($grouped_items);
    $invoice_ids_created = [];

    if (!class_exists('Dompdf\Dompdf') && file_exists(get_stylesheet_directory() . '/libs/dompdf/autoload.inc.php')) {
        require_once get_stylesheet_directory() . '/libs/dompdf/autoload.inc.php';
    }
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);

    foreach ($payers as $payer) {

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


        // 1. Create Payer Order
        $invoice_order = wc_create_order();
        $invoice_order->set_customer_id($payer['user_id'] ?: 0);

        $item = new WC_Order_Item_Fee();
        $item->set_name('Meeting Add-ons Share');
        $item->set_amount($share_amount); // This is the Net+Tax combined share
        $item->set_total($share_amount);
        $item->set_tax_status('none');
        $invoice_order->add_item($item);

        if ($payer['user_id']) {
            $c = new WC_Customer($payer['user_id']);
            $invoice_order->set_address($c->get_billing() ?: $order->get_address('billing'), 'billing');
        } else {
            $invoice_order->set_address($order->get_address('billing'), 'billing');
        }
        $invoice_order->set_billing_first_name($payer['name']);
        $invoice_order->set_billing_email($payer['email']);
        $invoice_order->set_billing_phone($payer['phone']);
        $invoice_order->set_billing_company($payer['company']);
        $invoice_order->update_meta_data('addons_parent_order_id', $order_id);
        if (!$invoice_order->get_meta('_unique_apl_id')) {
            $invoice_order->update_meta_data('_unique_apl_id', $unique_apl_id);
            update_option('apl_sequence_counter', $new_sequence);
        }
        $invoice_order->calculate_totals();
        $invoice_order->save();
        $invoice_ids_created[] = $invoice_order->get_id();

        // 2. Generate PDF
        $template_args = [
            'parent_order' => $order,
            'billing_order' => $invoice_order,
            'items' => $billed_items_details,

            'prices_include_tax' => $prices_include_tax,

            // Raw Base Values
            'base_net_subtotal' => $base_net_total,
            'base_gross_total' => $base_gross_total,

            // Final Values (After Discount)
            'discount' => $discount_amount,
            'coupon_code' => ($coupon && $coupon->get_id()) ? $coupon->get_amount() : 0,

            'final_tax' => $final_tax_total,
            'final_total' => $final_gross_total,

            'share_amount' => $share_amount
        ];

        $template_file = ($payment_mode === 'group') ? 'emails/admin-addons-invoice-split.php' : 'emails/admin-addons-invoice.php';

        ob_start();
        wc_get_template($template_file, $template_args);
        $invoice_html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($invoice_html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->get_font("Inter", "normal");
        $canvas->page_text(200, 810, "We appreciate the opportunity to serve you.", $font, 10, array(0, 0, 0));
        $upload_dir = wp_upload_dir();
        $pdf_filename = 'invoice_' . $order_id . '_' . $invoice_order->get_id() . '.pdf';
        $pdf_path = $upload_dir['basedir'] . '/' . $pdf_filename;
        $pdf_file_url = $upload_dir['baseurl'] . '/' . $pdf_filename;

        file_put_contents($pdf_path, $dompdf->output());

        // 3. Send Email
        $pay_link = $invoice_order->get_checkout_payment_url();
        $url_parts = explode('/order-pay/', $pay_link);
        $button_suffix = end($url_parts);
        $subject = 'Invoice for Add-on Services - #' . $order_id;
        $msg = "Dear Customer,<br>Please find attached the invoice for the add-ons services requested during the meeting.<br><strong>Payable:</strong> " . wc_price($share_amount) . "<p class='btn_p'><a href='{$pay_link}'>Pay Now</a></p>";

        if (function_exists('send_woocommerce_custom_email')) {
            send_woocommerce_custom_email($payer['email'], $subject, $subject, $msg, $pdf_path);
        }

        $components = [
            // Header Component (The PDF Invoice)
            [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'document',
                        'document' => [
                            'link' => $pdf_file_url,
                            'filename' => $pdf_filename
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
                        'text' => $payer['name'] // Variable {{1}}
                    ]
                ]
            ],
            // Button Component (The Dynamic URL Suffix)
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => 0, // 0 refers to the first button in your template
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $button_suffix
                    ]
                ]
            ]
        ];
        if (strlen($payer['phone']) === 10) {
            $payer['phone'] = '91' . $payer['phone'];
        } elseif (strlen($payer['phone']) === 11 && substr($payer['phone'], 0, 1) === '0') {
            $payer['phone'] = '91' . substr($payer['phone'], 1);
        }

        // Call your common function
        //send_whatsapp_template_msg($payer['phone'], 'live_addons_invoice', $components);
    }

    // --- E. Update Parent Meta ---
    $fresh_data = $order->get_meta('_meeting_addons_data');
    foreach ($fresh_data as &$row) {
        if (in_array($row['id'], $items_to_bill_ids)) {
            $row['billed_status'] = 'billed';
        }
    }
    $order->update_meta_data('_meeting_addons_data', $fresh_data);
    $existing = $order->get_meta('meeting_addons_billed') ?: [];
    $order->update_meta_data('meeting_addons_billed', array_merge($existing, $invoice_ids_created));
    $order->save();

    return ['success' => true, 'message' => 'Generated ' . count($invoice_ids_created) . ' invoices.'];
}


/* ---------------------------------------------------------
   1. Disable Customer Emails for Admin Manually Created Orders
--------------------------------------------------------- */
add_filter('woocommerce_email_enabled_customer_processing_order', 'phive_disable_admin_created_emails_safe', 10, 2);
add_filter('woocommerce_email_enabled_customer_completed_order', 'phive_disable_admin_created_emails_safe', 10, 2);
add_filter('woocommerce_email_enabled_customer_on_hold_order', 'phive_disable_admin_created_emails_safe', 10, 2);
add_filter('woocommerce_email_enabled_new_order', 'phive_disable_admin_created_emails_safe', 10, 2);
add_filter('woocommerce_email_enabled_customer_invoice', 'phive_disable_admin_created_emails_safe', 10, 2);

function phive_disable_admin_created_emails_safe($enabled, $order)
{
    if (!$order instanceof WC_Order)
        return $enabled;

    // Check if our custom flag exists or if created via admin
    if ('yes' === $order->get_meta('disable_customer_emails') || $order->get_meta('_wc_order_attribution_source_type') === 'admin') {
        return false;
    }
    return $enabled;
}

// Automatically flag orders created via Admin to disable emails
add_action('woocommerce_new_order', 'phive_flag_admin_orders_no_email_safe', 10, 2);
function phive_flag_admin_orders_no_email_safe($order_id, $order)
{
    if (isset($_POST['wapf']) && is_array($_POST['wapf'])) {

        // 1. Check and save Case Title (Textarea)
        if (!empty($_POST['wapf']['field_68afdc5063a8e'])) {
            $case_title = sanitize_textarea_field(wp_unslash($_POST['wapf']['field_68afdc5063a8e']));
            $order->update_meta_data('Case Title', $case_title);
        }

        // 2. Check and save Case ID (Text Input)
        if (!empty($_POST['wapf']['field_68c7cf78747bb'])) {
            $case_id = sanitize_text_field(wp_unslash($_POST['wapf']['field_68c7cf78747bb']));
            $order->update_meta_data('Case ID', $case_id);
        }

    }
    if (is_admin() && !defined('DOING_AJAX')) {
        $order->update_meta_data('disable_customer_emails', 'yes');
        $order->update_meta_data('_phive_manual_payment_status', 'pending');
        $order->save();
    }
}



/* ---------------------------------------------------------
   2. Add "Manual Invoice" Meta Box (Button)
--------------------------------------------------------- */
add_action('add_meta_boxes', 'phive_add_invoice_meta_box_safe');
function phive_add_invoice_meta_box_safe()
{
    $screens = ['shop_order', 'woocommerce_page_wc-orders'];
    foreach ($screens as $screen) {
        add_meta_box(
            'phive_manual_invoice_box',      // ID
            'Booking Communications',                // Title
            'phive_render_invoice_meta_box_safe', // Callback
            $screen,                    // Screen
            'side',                          // Context
            'high'                           // Priority
        );
    }
}

function phive_render_invoice_meta_box_safe($post)
{
    $order = wc_get_order($post->ID);
    if (!$order)
        return;

    $status = $order->get_status();
    $order_id = $order->get_id();

    // Check if this is a group/split order
    $is_group = $order->get_meta('group_payment_mode') === 'group';

    if (get_current_status($order_id) === 'Past') {
        // echo "<p>Not Available for Completed or Expired Bookings.</p>";
        // return;
    }

    if ($status !== 'cancelled' && $status !== 'refund-processed') {

        if ($order) {
            // --- SPLIT PAYMENT BUTTONS (Triggers Custom Modal & Shows 'Resend') ---
            $global_actions = array(
                'send_manual_email' => 'Send Initiation Email',
                'send_manual_invoice_pdf_email' => 'Send Invoice & Email',
                'send_manual_confirmation' => 'Send Confirmation Only Email',
                'send_receipt' => 'Send Receipt Post Confirmation',
                'send_manual_booking_confirmation' => 'Send Confirmation & Receipt'
            );

            foreach ($global_actions as $action_key => $action_label) {
                // Check if this specific email was already sent
                $is_sent = $order->get_meta('_split_email_sent_' . $action_key) === 'yes';
                $btn_text = $is_sent ? str_replace('Send ', 'Send ', $action_label) : $action_label;

                echo '<button type="button" class="button button-primary button-large trigger-global-comm-modal" data-action="' . esc_attr($action_key) . '" data-label="' . esc_attr($action_label) . '" style="width:100%; text-align:center;margin-top:10px">' . esc_html($btn_text) . '</button>';
            }
            echo '<div id="phive_invoice_msg" style="margin-top:10px; color:green; font-weight:600;"></div>';

        } else {
            // --- SINGLE BOOKING BUTTONS (Original Behavior) ---
            echo '<button type="button" class="button button-primary button-large send_manual_invoice_btn no_pdf" style="width:100%; text-align:center;margin-top:10px">Send Initiation Email</button>';
            echo '<button type="button" class="button button-primary button-large send_manual_invoice_btn" style="width:100%; text-align:center;margin-top:10px">Send Invoice & Email</button>';
            echo '<button type="button" class="button button-primary button-large send_manual_confirmation no_pdf" style="width:100%; text-align:center;margin-top:10px">Send Confirmation Only Email</button>';
            echo '<button type="button" class="button button-primary button-large send_manual_confirmation no_pdf receipt" style="width:100%; text-align:center;margin-top:10px">Send Receipt Post Confirmation</button>';
            echo '<button type="button" class="button button-primary button-large send_manual_confirmation" style="width:100%; text-align:center;margin-top:10px">Send Confirmation & Receipt</button>';
            echo '<div id="phive_invoice_msg" style="margin-top:10px; color:green; font-weight:600;"></div>';
        }

    } else {
        echo "<p>Not Available for Cancelled or Refunded Bookings.</p>";
    }
}

/* ---------------------------------------------------------
   Add Custom Email Popup to Admin Footer
--------------------------------------------------------- */
add_action('admin_footer', 'phive_add_email_popup_html');

function phive_add_email_popup_html()
{
    // Only load on order edit pages
    $screen = get_current_screen();
    if (!$screen)
        return;
    $allowed_screens = ['shop_order', 'woocommerce_page_wc-orders'];
    if (!in_array($screen->id, $allowed_screens, true)) {
        return;
    }
    $order_id = $_GET['id'];
    if (!$order_id) {
        return;
    }
    $order = wc_get_order($order_id);
    $email = $order->get_billing_email();
    ?>
    <style>
        /* Popup Overlay */
        #phive_email_modal {
            display: none;
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }

        /* Popup Box */
        .phive-modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #888;
            width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .phive-modal-header {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .phive-modal-body textarea {
            width: 100%;
            height: 80px;
            margin-bottom: 15px;
        }

        .phive-modal-footer {
            text-align: right;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>

    <div id="phive_email_modal">
        <div class="phive-modal-content">
            <div class="phive-modal-header">Add Email id to send invoice</div>
            <div class="phive-modal-body">
                <p style="margin-top:0;">Enter recipient emails (comma separated):</p>
                <textarea id="phive_email_input"
                    placeholder="e.g. client@example.com, admin@example.com"><?php echo $email; ?></textarea>
            </div>
            <div class="phive-modal-footer">
                <button type="button" class="button" id="phive_cancel_email">Cancel</button>
                <button type="button" class="button button-primary" id="phive_send_email_confirm">Send Invoice</button>
            </div>
        </div>
    </div>
    <?php
}
function get_booking_details_admin($order)
{
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->get_type() === 'phive_booking') {
            $room_name = $item->get_name();
            $from = $item->get_meta('From')[0] ?? '';
            $to = $item->get_meta('To')[0] ?? '';
            $datetime = '';
            if ($from && $to) {
                $datetime = date('F j, Y', strtotime($from)) . ' (' . date('g:i a', strtotime($from)) . ' – ' . date('g:i a', strtotime($to . ' +4 hours')) . ')';
            }
            return ['room' => $room_name, 'datetime' => $datetime];
        }
    }
    return ['room' => '', 'datetime' => ''];
}


/* ---------------------------------------------------------
   4. AJAX: Send Manual Invoice PDF (Using Custom Mail Function)
--------------------------------------------------------- */
add_action('wp_ajax_send_manual_invoice_pdf_email', 'phive_ajax_send_manual_invoice_safe');

function phive_ajax_send_manual_invoice_safe()
{
    // Permission Check
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Unauthorized user.']);
    }
    $terms = home_url('terms-and-conditions');
    $policy = home_url('privacy-policy');
    $order_id = intval($_POST['order_id']);
    $recipients = sanitize_text_field($_POST['recipients']);
    $phones = isset($_POST['phones']) ? sanitize_text_field(wp_unslash($_POST['phones'])) : '';
    $type = isset($_POST['types']) ? sanitize_text_field(wp_unslash($_POST['types'])) : 'all'; // Default to 'all' if missing
    $order = wc_get_order($order_id);
    $pay_url = $order->get_checkout_payment_url();
    $billing_email = $order->get_billing_email();
    $user_id = $order->get_user_id();
    $billing_phone = $order->get_billing_phone();
    if ($billing_phone == '') {
        $billing_phone = get_user_meta($user_id, 'phone', true);
    }
    if (!$billing_phone || $billing_phone === '') {
        $billing_phone = 'NA';
        $login_html = "<p>Please login/regsiter with the OTP using below email id.</p>
            <p><strong>Login Email:</strong> {$billing_email}<br>";
    } else {
        $login_html = "<p>Please login/regsiter with the OTP using below email id or phone number.</p>
            <p><strong>Login Email:</strong> {$billing_email}<br>
            <strong>Phone No.:</strong> {$billing_phone}<br>";
    }
    if (!$order) {
        wp_send_json_error(['message' => 'Invalid Order ID.']);
    }

    // 1. Generate PDF (Using the helper function created earlier)
    $file_path = generate_admin_invoice_pdf($order);

    if (!$file_path || !file_exists($file_path)) {
        wp_send_json_error(['message' => 'Failed to generate PDF file.']);
    }

    if ($order->get_meta('group_parent_order')) {
        $parent_order = wc_get_order($order->get_meta('group_parent_order'));
        $booking_details = get_booking_details($parent_order);
    } else {
        $booking_details = get_booking_details($order);
    }
    $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;

    // 2. Prepare Email Content
    $subject = "Accordhub - Your Booking is Ready for Payment (Booking ID - {$booking_details['id']})";
    $email_heading = "Your Booking is Ready for Payment<br>(Booking ID - {$booking_details['id']})";

    // Message Body
    $message = "<p>Dear Customer,</p>";
    $message .= "<p>Your shared room booking is confirmed and ready for payment. Below are your booking details:</p>";
    $message .= "<p><strong>Booking ID:</strong> {$booking_details['id']}</p>
            <p><strong>Room:</strong> {$booking_details['room']}</p>
            <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>
            <p><strong>Requested by:</strong> {$booking_details['user']}</p>";
    $message .= "{$login_html}";
    $message .= "<p>Please click on the link below to complete your share of payment as per the attached invoice.</p>";
    $message .= "<p class='btn_p'><a href='{$pay_url}'>Pay Now</a></p>
    <p>Through this booking you agree to our <a href='{$terms}'>terms & conditions</a> and <a href='{$policy}'>privacy policy</a>.</p>
    ";

    // 3. Send WhatsApp Loop (Only if phones are provided)
    $wa_count = 0;
    if (!empty($phones)) {
        // Remove spaces and explode
        $phones_array = array_map('trim', explode(',', $phones));
        // Filter out empty entries just in case there were double commas
        $phones_array = array_filter($phones_array);

        if (!empty($phones_array) && ($type === 'all' || $type === 'wa')) {
            if ($order->get_meta('group_payment_mode') !== 'group' && empty($order->get_meta('group_parent_order'))) {
                $components = [
                    [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'document',
                                'document' => [
                                    'link' => 'https://staging.accordhub.in/wp-content/uploads/invoice-37698.pdf',
                                    'filename' => 'invoice-full.pdf'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => 'Anubhav'
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
                                'text' => 'invoice-split.pdf'
                            ]
                        ]
                    ]
                ];

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    send_whatsapp_template_msg($number, 'live_addons_invoice', $components);
                    $wa_count++;
                }
            } else {
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
                                'text' => 'Anubhav'
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

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    send_whatsapp_template_msg($number, 'live_addons_invoice', $components);
                    $wa_count++;
                }
            }
        }
    }

    // 3. Send Email Loop 
    $sent_count = 0;
    if (!empty($recipients)) {
        $recipient_array = array_map('trim', explode(',', $recipients));
        $recipient_array = array_filter($recipient_array);
        if (!empty($recipient_array) && ($type === 'all' || $type === 'email')) {
            foreach ($recipient_array as $to) {
                $to = sanitize_email(trim($to));
                if (is_email($to)) {

                    // Use your custom wrapper function
                    if (function_exists('send_woocommerce_custom_email')) {
                        send_woocommerce_custom_email(
                            $to,
                            $subject,
                            $email_heading,
                            $message,
                            file_exists($file_path) ? [$file_path] : []
                        );
                        $sent_count++;
                    } else {
                        // Fallback if function is missing (Safety)
                        wp_mail($to, $subject, $message, ['Content-Type: text/html; charset=UTF-8'], [$file_path]);
                        $sent_count++;
                    }
                }
            }
        }
    }


    if ($sent_count > 0 || $wa_count > 0) {

        $note = "Initiation communication sent.";
        if ($sent_count > 0)
            $note .= " Email(s) sent to: " . implode(', ', $recipient_array) . ".";
        if ($wa_count > 0)
            $note .= " WhatsApp(s) sent to: " . implode(', ', $phones_array) . ".";

        $order->add_order_note($note);
        $order->update_meta_data('init_mail', 1);
        $order->save();

        wp_send_json_success(['message' => "Invoice sent."]);
    } else {
        wp_send_json_error(['message' => 'Failed to send.']);
    }
}


/* ---------------------------------------------------------
   4. AJAX: Send Booking Confirmation (With PDF & Custom Mail)
--------------------------------------------------------- */
add_action('wp_ajax_send_manual_booking_confirmation', 'phive_ajax_send_manual_confirmation');

function phive_ajax_send_manual_confirmation()
{
    //error_reporting(E_ALL); // Report all PHP errors
    //ini_set('display_errors', 1);
    // 1. Permission Check
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Unauthorized user.']);
    }

    $order_id = intval($_POST['order_id']);
    $recipients_str = sanitize_text_field($_POST['recipients']);
    $phones = isset($_POST['phones']) ? sanitize_text_field(wp_unslash($_POST['phones'])) : '';
    $type = isset($_POST['types']) ? sanitize_text_field(wp_unslash($_POST['types'])) : 'all'; // Default to 'all' if missing
    $order = wc_get_order($order_id);
    $customer_name = $order->get_billing_first_name();
    if ($order->get_meta('group_parent_order')) {
        $parent_order = wc_get_order($order->get_meta('group_parent_order'));
        $booking_details = get_booking_details($parent_order);
    } else {
        $booking_details = get_booking_details($order);
    }
    $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;
    $user = get_user_by('id', $order->get_user_id());
    $email = $user ? $user->user_email : '';

    if (!$order) {
        wp_send_json_error(['message' => 'Invalid Order ID.']);
    }

    // 2. Generate Invoice PDF
    $pdf_path = generate_receipt_admin_pdf($order);



    $message = "
            <p>Dear Customer,</p>
            <p>Your payment has been successfully completed, and your room booking is confirmed.</p>
            <p>Please login with the OTP using below email id.</p>
            <p><strong>Login Email:</strong> {$email}</p>
            <p><strong>Booking ID:</strong> {$booking_details['id']}</p>
            <p><strong>Room:</strong> {$booking_details['room']}</p>
            <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>
            <p>Please find your payment receipt attached for reference.</p>
            <p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>
            ";

    // 4. Prepare Email Details
    $to = $order->get_billing_email();
    $subject = "Accordhub - Your Room Booking is Confirmed (Booking ID - {$booking_details['id']})";
    $email_heading = "Your Room Booking is Confirmed <br>(Booking ID - {$booking_details['id']})";

    // Prepare Attachments Array
    $attachments = ($pdf_path && file_exists($pdf_path)) ? [$pdf_path] : [];

    // 3. Send WhatsApp Loop (Only if phones are provided)
    $wa_count = 0;
    if (!empty($phones)) {
        // Remove spaces and explode
        $phones_array = array_map('trim', explode(',', $phones));
        $phones_array = array_filter($phones_array);

        if (!empty($phones_array) && ($type === 'all' || $type === 'wa')) {
            if ($order->get_meta('group_payment_mode') !== 'group' && empty($order->get_meta('group_parent_order'))) {
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
                                'text' => 'Anubhav'
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

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    send_whatsapp_template_msg($number, 'live_addons_invoice', $components);
                    $wa_count++;
                }
            } else {
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
                                'text' => 'Anubhav'
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

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    send_whatsapp_template_msg($number, 'live_addons_invoice', $components);
                    $wa_count++;
                }
            }

        }
    }

    $sent_count = 0;
    if (!empty($recipients_str)) {
        $recipient_array = array_map('trim', explode(',', $recipients_str));
        $recipient_array = array_filter($recipient_array);
        if (!empty($recipient_array) && ($type === 'all' || $type === 'email')) {
            foreach ($recipient_array as $to) {
                $to = sanitize_email(trim($to));


                if (is_email($to)) {
                    send_woocommerce_custom_email(
                        $to,
                        $subject,
                        $email_heading,
                        $message,
                        $attachments
                    );
                    $sent_count++;
                }
            }
        }
    }
    // 6. Cleanup: Remove the temporary PDF file
    if ($pdf_path && file_exists($pdf_path)) {
        //unlink($pdf_path);
    }

    if ($sent_count > 0 || $wa_count > 0) {
        $note = "Booking Confirmation and Receipt sent.";
        if ($sent_count > 0)
            $note .= " Email(s) sent to: " . implode(', ', $recipient_array) . ".";
        if ($wa_count > 0)
            $note .= " WhatsApp(s) sent to: " . implode(', ', $phones_array) . ".";

        $order->add_order_note($note);
        $order->update_meta_data('init_mail', 1);
        $order->save();
        wp_send_json_success(['message' => "Booking Confirmation sent."]);
    } else {
        wp_send_json_error(['message' => "Failed to send email. Check server logs."]);
    }
}




/* ---------------------------------------------------------
   4. AJAX: Send Manual Invoice PDF (Using Custom Mail Function)
--------------------------------------------------------- */
add_action('wp_ajax_send_manual_email', 'phive_ajax_send_manual_safe');

function phive_ajax_send_manual_safe()
{
    // Permission Check
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Unauthorized user.']);
    }

    $terms = home_url('terms-and-conditions');
    $policy = home_url('privacy-policy');

    // Safely retrieve $_POST variables with fallbacks
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $recipients = isset($_POST['recipients']) ? sanitize_text_field(wp_unslash($_POST['recipients'])) : '';
    $phones = isset($_POST['phones']) ? sanitize_text_field(wp_unslash($_POST['phones'])) : '';
    $type = isset($_POST['types']) ? sanitize_text_field(wp_unslash($_POST['types'])) : 'all'; // Default to 'all' if missing

    if (!$order_id) {
        wp_send_json_error(['message' => 'Invalid Order ID.']);
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error(['message' => 'Order not found.']);
    }

    $pay_url = $order->get_checkout_payment_url();
    $created_via = $order->get_created_via();

    if ($created_via === 'checkout') {
        $initiator = '';
    } else {
        $initiator = ' by Accordhub Admin';
    }

    // 1. Generate PDF (Using the helper function created earlier)
    $file_path = generate_admin_invoice_pdf($order);

    if (!$file_path || !file_exists($file_path)) {
        wp_send_json_error(['message' => 'Failed to generate PDF file.']);
    }

    if ($order->get_meta('group_parent_order')) {
        $parent_order = wc_get_order($order->get_meta('group_parent_order'));
        $booking_details = get_booking_details($parent_order);
    } else {
        $booking_details = get_booking_details($order);
    }

    $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;

    // 2. Prepare Email Content
    $subject = "Your Room Booking is Initiated (Booking ID - {$booking_details['id']})";
    $email_heading = "Your Room Booking is Initiated (Booking ID - {$booking_details['id']})";

    // Message Body
    $message = "<p>Dear Customer,</p>";
    $message .= "<p>As per the request, your booking has been initiated{$initiator}.</p>";
    $message .= "<p>Please find the booking details below.</p>";
    $message .= "<p><strong>Booking ID:</strong> {$booking_details['id']}</p>
            <p><strong>Room:</strong> {$booking_details['room']}</p>
            <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>
            <p>Through this booking you agree to our <a href='{$terms}'>terms & conditions</a> and <a href='{$policy}'>privacy policy</a>.</p>";

    // 3. Send WhatsApp Loop (Only if phones are provided)
    $wa_count = 0;
    if (!empty($phones)) {
        // Remove spaces and explode
        $phones_array = array_map('trim', explode(',', $phones));
        // Filter out empty entries just in case there were double commas
        $phones_array = array_filter($phones_array);

        if (!empty($phones_array) && ($type === 'all' || $type === 'wa')) {
            if ($order->get_meta('group_payment_mode') !== 'group' && empty($order->get_meta('group_parent_order'))) {
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
                                'text' => 'Anubhav'
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

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    //send_whatsapp_template_msg($number, 'live_addons_invoice', $components);
                    $wa_count++;
                }
            } else {
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
                                'text' => 'Anubhav'
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

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    //send_whatsapp_template_msg($number, 'live_addons_invoice', $components);
                    $wa_count++;
                }
            }

        }
    }

    // 4. Send Email Loop (Only if recipients are provided)
    $sent_count = 0;
    if (!empty($recipients)) {
        // Remove spaces and explode
        $recipient_array = array_map('trim', explode(',', $recipients));
        $recipient_array = array_filter($recipient_array);

        if (!empty($recipient_array) && ($type === 'all' || $type === 'email')) {
            foreach ($recipient_array as $to) {
                $to = sanitize_email($to);
                if (is_email($to)) {
                    if (function_exists('send_woocommerce_custom_email')) {
                        send_woocommerce_custom_email(
                            $to,
                            $subject,
                            $email_heading,
                            $message
                        );
                        $sent_count++;
                    } else {
                        wp_mail($to, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
                        $sent_count++;
                    }
                }
            }
        }
    }


    // 6. Return Response
    if ($sent_count > 0 || $wa_count > 0) {
        $note = "Initiation communication sent.";
        if ($sent_count > 0)
            $note .= " Email(s) sent to: " . implode(', ', $recipient_array) . ".";
        if ($wa_count > 0)
            $note .= " WhatsApp(s) sent to: " . implode(', ', $phones_array) . ".";

        $order->add_order_note($note);
        $order->update_meta_data('init_mail', 1);
        $order->save();

        wp_send_json_success(['message' => "Initiation communication sent."]);
    } else {
        wp_send_json_error(['message' => 'Failed to send. No valid email or phone number provided.']);
    }
}


/* ---------------------------------------------------------
   4. AJAX: Send Booking Confirmation (With PDF & Custom Mail)
--------------------------------------------------------- */
add_action('wp_ajax_send_manual_confirmation', 'phive_ajax_send_confirmation');

function phive_ajax_send_confirmation()
{
    //error_reporting(E_ALL); // Report all PHP errors
    //ini_set('display_errors', 1);
    // 1. Permission Check
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Unauthorized user.']);
    }

    $order_id = intval($_POST['order_id']);
    $recipients_str = sanitize_text_field($_POST['recipients']);
    $phones = isset($_POST['phones']) ? sanitize_text_field(wp_unslash($_POST['phones'])) : '';
    $type = isset($_POST['types']) ? sanitize_text_field(wp_unslash($_POST['types'])) : 'all'; // Default to 'all' if missing
    $order = wc_get_order($order_id);
    $customer_name = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
    $parent_order = null;
    if ($order->get_meta('group_parent_order')) {
        $parent_order = wc_get_order($order->get_meta('group_parent_order'));
        $booking_details = get_booking_details($parent_order);
    } else {
        $booking_details = get_booking_details($order);
    }
    $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;
    $user = get_user_by('id', $order->get_user_id());
    $email = $user ? $user->user_email : '';
    $phone = $order->get_billing_phone();
    if ($phone === "") {
        $phone = get_user_meta($order->get_user_id(), 'phone', true);
    }

    if (!$phone || $phone === '') {
        $phone === 'NA';
        $login_html = "<p>Please regsiter or login with the OTP using below email id.</p>
            <p><strong>Login Email:</strong> {$email}<br>";
    } else {
        $login_html = "<p>Please regsiter or login with the OTP using below email id or phone number.</p>
            <p><strong>Login Email:</strong> {$email}<br>
            <strong>Phone No.:</strong> {$phone}<br>";
    }

    if (!$order) {
        wp_send_json_error(['message' => 'Invalid Order ID.']);
    }

    // 2. Generate Invoice PDF
    $pdf_path = generate_receipt_admin_pdf($order);

    $message = "
            <p>Dear Customer,</p>
            <p>As per the request, your shared room booking is confirmed.</p>
            <p><strong>Booking ID:</strong> {$booking_details['id']}</p>
            <p><strong>Room:</strong> {$booking_details['room']}</p>
            <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>
            <p><strong>Requested by:</strong> {$booking_details['user']}</p> 

            {$login_html}
            
            <p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>
            ";

    // 4. Prepare Email Details
    $subject = "Accordhub - Your Room Booking is Confirmed (Booking ID - {$booking_details['id']})";
    $email_heading = "Your Room Booking is Confirmed <br>(Booking ID - {$booking_details['id']})";

    $to = $order->get_billing_email();
    // Prepare Attachments Array
    $attachments = ($pdf_path && file_exists($pdf_path)) ? [$pdf_path] : [];

    // 3. Send WhatsApp Loop (Only if phones are provided)
    $wa_count = 0;
    if (!empty($phones)) {
        // Remove spaces and explode
        $phones_array = array_map('trim', explode(',', $phones));
        // Filter out empty entries just in case there were double commas
        $phones_array = array_filter($phones_array);

        if (!empty($phones_array) && ($type === 'all' || $type === 'wa')) {
            if ($order->get_meta('group_payment_mode') !== 'group' && empty($order->get_meta('group_parent_order'))) {
                $components = [
                    [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'document',
                                'document' => [
                                    'link' => 'https://staging.accordhub.in/wp-content/uploads/invoice-37698.pdf',
                                    'filename' => 'full.pdf'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $customer_name],
                            ['type' => 'text', 'text' => $booking_details['id']],
                            ['type' => 'text', 'text' => $booking_details['room']],
                            ['type' => 'text', 'text' => $booking_details['datetime']]
                        ]
                    ]
                ];

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    send_whatsapp_template_msg($number, 'payment_completed_booking_confirmed', $components);
                    $wa_count++;
                }
            } else {
                $components = [
                    [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'document',
                                'document' => [
                                    'link' => 'https://staging.accordhub.in/wp-content/uploads/invoice-37698.pdf',
                                    'filename' => 'split.pdf'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $customer_name],
                            ['type' => 'text', 'text' => $booking_details['id']],
                            ['type' => 'text', 'text' => $booking_details['room']],
                            ['type' => 'text', 'text' => $booking_details['datetime']]
                        ]
                    ]
                ];

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    send_whatsapp_template_msg($number, 'payment_completed_booking_confirmed', $components);
                    $wa_count++;
                }
            }

        }
    }

    $sent_count = 0;
    if (!empty($recipients_str)) {
        $recipient_array = array_map('trim', explode(',', $recipients_str));
        $recipient_array = array_filter($recipient_array);
        if (!empty($recipient_array) && ($type === 'all' || $type === 'email')) {
            foreach ($recipient_array as $to) {
                $to = sanitize_email(trim($to));


                if (is_email($to)) {
                    send_woocommerce_custom_email(
                        $to,
                        $subject,
                        $email_heading,
                        $message
                    );
                    $sent_count++;
                }
            }
        }
    }

    if ($sent_count > 0 || $wa_count > 0) {
        $note = "Booking Confirmation sent.";
        if ($sent_count > 0)
            $note .= " Email(s) sent to: " . implode(', ', $recipient_array) . ".";
        if ($wa_count > 0)
            $note .= " WhatsApp(s) sent to: " . implode(', ', $phones_array) . ".";

        $order->add_order_note($note);
        $order->update_meta_data('init_mail', 1);
        $order->save();
        wp_send_json_success(['message' => "Booking Confirmation sent."]);
    } else {
        wp_send_json_error(['message' => "Failed to send email. Check server logs."]);
    }
}

/* ---------------------------------------------------------
   4. AJAX: Send Booking Confirmation (With PDF & Custom Mail)
--------------------------------------------------------- */
add_action('wp_ajax_send_receipt', 'phive_ajax_send_receipt');

function phive_ajax_send_receipt()
{
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Unauthorized user.']);
    }

    $order_id = intval($_POST['order_id']);
    $recipients_str = sanitize_text_field($_POST['recipients']);
    $phones = isset($_POST['phones']) ? sanitize_text_field(wp_unslash($_POST['phones'])) : '';
    $type = isset($_POST['types']) ? sanitize_text_field(wp_unslash($_POST['types'])) : 'all';
    $order = wc_get_order($order_id);
    $customer_name = $order->get_billing_first_name();
    if ($order->get_meta('group_parent_order')) {
        $parent_order = wc_get_order($order->get_meta('group_parent_order'));
        $booking_details = get_booking_details($parent_order);
    } else {
        $booking_details = get_booking_details($order);
    }
    $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;
    $user = get_user_by('id', $order->get_user_id());
    $email = $user ? $user->user_email : '';
    $phone = get_user_meta($order->get_user_id(), 'phone', true);

    if (!$phone || $phone === '') {
        $phone === 'NA';
        $login_html = "<p>Please login with the OTP using below email id.</p>
            <p><strong>Login Email:</strong> {$email}<br>";
    } else {
        $login_html = "<p>Please login with the OTP using below email id or phone number.</p>
            <p><strong>Login Email:</strong> {$email}<br>
            <strong>Phone No.:</strong> {$phone}<br>";
    }

    if (!$order) {
        wp_send_json_error(['message' => 'Invalid Order ID.']);
    }

    // 2. Generate Invoice PDF
    $pdf_path = generate_receipt_admin_pdf($order);



    $message = "
            <p>Dear Customer,</p>
            <p>Your payment has been successfully completed for your confirmed room booking.</p>
            {$login_html}
            <p><strong>Booking ID:</strong> {$booking_details['id']}</p>
            <p><strong>Room:</strong> {$booking_details['room']}</p>
            <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>
            <p>Please find your payment receipt attached for reference.</p>
            <p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>";

    // 4. Prepare Email Details
    $to = $order->get_billing_email();
    $subject = "Accordhub - Payment Received for Your Room Booking (Booking ID - {$booking_details['id']})";
    $email_heading = "Payment Received for Your Room Booking <br>(Booking ID - {$booking_details['id']})";

    // Prepare Attachments Array
    $attachments = ($pdf_path && file_exists($pdf_path)) ? [$pdf_path] : [];

    // 3. Send WhatsApp Loop (Only if phones are provided)
    $wa_count = 0;
    if (!empty($phones)) {
        // Remove spaces and explode
        $phones_array = array_map('trim', explode(',', $phones));
        // Filter out empty entries just in case there were double commas
        $phones_array = array_filter($phones_array);

        if (!empty($phones_array) && ($type === 'all' || $type === 'wa')) {
            if ($order->get_meta('group_payment_mode') !== 'group' && empty($order->get_meta('group_parent_order'))) {
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
                                'text' => 'Anubhav'
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

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    send_whatsapp_template_msg($number, 'live_addons_invoice', $components);
                    $wa_count++;
                }
            } else {
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
                                'text' => 'Anubhav'
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

                foreach ($phones_array as $number) {
                    if (strlen($number) === 10) {
                        $number = '91' . $number;
                    } elseif (strlen($number) === 11 && substr($number, 0, 1) === '0') {
                        $number = '91' . substr($number, 1);
                    }

                    send_whatsapp_template_msg($number, 'live_addons_invoice', $components);
                    $wa_count++;
                }
            }

        }
    }

    $sent_count = 0;
    if (!empty($recipients)) {
        $recipient_array = array_map('trim', explode(',', $recipients_str));
        $recipient_array = array_filter($recipient_array);
        if (!empty($recipient_array) && ($type === 'all' || $type === 'email')) {
            foreach ($recipient_array as $to) {
                $to = sanitize_email(trim($to));


                if (is_email($to)) {
                    send_woocommerce_custom_email(
                        $to,
                        $subject,
                        $email_heading,
                        $message,
                        $attachments
                    );
                    $sent_count++;
                }
            }
        }
    }

    if ($sent_count > 0 || $wa_count > 0) {
        $note = "Booking Receipt sent.";
        if ($sent_count > 0)
            $note .= " Email(s) sent to: " . implode(', ', $recipient_array) . ".";
        if ($wa_count > 0)
            $note .= " WhatsApp(s) sent to: " . implode(', ', $phones_array) . ".";

        $order->add_order_note($note);
        $order->update_meta_data('init_mail', 1);
        $order->save();
        wp_send_json_success(['message' => "Booking Receipt sent."]);
    } else {
        wp_send_json_error(['message' => "Failed to send email. Check server logs."]);
    }
}



/* ---------------------------------------------------------
   5. Custom Meta Box: Manage Coupons
--------------------------------------------------------- */
add_action('add_meta_boxes', 'phive_add_coupon_meta_box');

function phive_add_coupon_meta_box()
{
    $order_id = isset($_GET['id']) ? absint($_GET['id']) : (isset($_GET['post']) ? absint($_GET['post']) : 0);
    $order = wc_get_order($order_id);

    if ($order && $order->get_meta('init_mail')) {
        return;
    }

    add_meta_box(
        'phive_order_coupons',
        'Manage Coupons',
        'phive_render_coupon_box',
        'woocommerce_page_wc-orders',
        'side',
        'default'
    );
}

function phive_render_coupon_box($post_or_order_object)
{
    // Compatibility for HPOS vs Legacy Post
    $order = ($post_or_order_object instanceof WC_Order) ? $post_or_order_object : wc_get_order($post_or_order_object->ID);

    if (!$order)
        return;

    ?>
    <div class="phive-coupon-wrapper">
        <div style="display:flex; gap:5px; margin-bottom:10px;">
            <input type="text" id="phive_coupon_code" placeholder="Coupon Code" style="flex:1;">
            <button type="button" class="button button-secondary" id="phive_apply_coupon_btn"
                data-order-id="<?php echo $order->get_id(); ?>">Apply</button>
        </div>

        <div id="phive_coupon_msg" style="margin-bottom:10px; font-weight:600;"></div>

        <div class="coupon_box" style="padding:10px; border-radius:4px;">
            <strong>Applied Coupons:</strong>
            <?php
            $coupons = $order->get_coupons();
            if (empty($coupons)) {
                echo '<p style="margin:5px 0 0; color:#888;">No coupons applied.</p>';
            } else {
                echo '<ul style="margin:5px 0 0;">';
                foreach ($coupons as $item_id => $item) {
                    $code = $item->get_code();
                    // Clean amount display (optional)
                    $amount = wc_price($item->get_discount());
                    echo '<li style="margin-bottom:5px;">';
                    echo '<strong>' . esc_html($code) . '</strong> ';
                    echo '<a href="#" class="phive_remove_coupon" style="color:red; text-decoration:none;" data-code="' . esc_attr($code) . '" data-order-id="' . $order->get_id() . '"> X </a>';
                    echo '</li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
        <p class="description" style="margin-top:8px; font-size:11px;">Note: Page will reload to recalculate totals.</p>
    </div>
    <?php
}

/* ---------------------------------------------------------
   6. AJAX: Apply Coupon
--------------------------------------------------------- */
add_action('wp_ajax_phive_apply_order_coupon', 'phive_ajax_apply_coupon');

function phive_ajax_apply_coupon()
{
    if (!current_user_can('manage_woocommerce'))
        wp_send_json_error(['message' => 'Unauthorized']);

    $order_id = intval($_POST['order_id']);
    $code = sanitize_text_field($_POST['coupon_code']);
    $order = wc_get_order($order_id);

    if (!$order || empty($code))
        wp_send_json_error(['message' => 'Invalid data']);

    // Apply Coupon
    $result = $order->apply_coupon($code);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    // Recalculate Totals
    $order->calculate_totals();
    $order->save();

    wp_send_json_success(['message' => 'Coupon applied successfully!']);
}

/* ---------------------------------------------------------
   7. AJAX: Remove Coupon
--------------------------------------------------------- */
add_action('wp_ajax_phive_remove_order_coupon', 'phive_ajax_remove_coupon');

function phive_ajax_remove_coupon()
{
    if (!current_user_can('manage_woocommerce'))
        wp_send_json_error(['message' => 'Unauthorized']);

    $order_id = intval($_POST['order_id']);
    $code = sanitize_text_field($_POST['coupon_code']);
    $order = wc_get_order($order_id);

    if (!$order)
        wp_send_json_error(['message' => 'Invalid order']);

    // Remove Coupon
    $result = $order->remove_coupon($code);

    if ($result) {
        $order->calculate_totals();
        $order->save();
        wp_send_json_success(['message' => 'Coupon removed.']);
    } else {
        wp_send_json_error(['message' => 'Could not remove coupon.']);
    }
}



/* ---------------------------------------------------------
   ADMIN ADDONS: 1. Get Available Coupons for Popup
--------------------------------------------------------- */
add_action('wp_ajax_get_wc_coupons_list', 'phive_get_wc_coupons_list');
function phive_get_wc_coupons_list()
{
    if (!current_user_can('manage_woocommerce'))
        wp_send_json_error('Unauthorized');

    $args = array(
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'asc',
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
    );

    $coupons = get_posts($args);
    $list = [];

    foreach ($coupons as $coupon_post) {
        $coupon = new WC_Coupon($coupon_post->ID);
        $type = $coupon->get_discount_type();
        $amount = $coupon->get_amount();
        $desc = $coupon->get_description();

        // Format label
        if ($type === 'percent') {
            $label = $coupon->get_code() . ' (' . $amount . '% off)';
        } else {
            $label = $coupon->get_code() . ' (' . wc_price($amount) . ' off)';
        }

        $list[] = [
            'code' => $coupon->get_code(),
            'label' => $label,
            'desc' => $desc
        ];
    }

    wp_send_json_success($list);
}

/* ---------------------------------------------------------
   ADMIN ADDONS: 2. Save Coupon (Existing - ensure this is present)
--------------------------------------------------------- */
add_action('wp_ajax_save_addon_coupon', 'phive_save_addon_coupon');
function phive_save_addon_coupon()
{
    if (!current_user_can('manage_woocommerce'))
        wp_send_json_error('Unauthorized');

    $order_id = intval($_POST['order_id']);
    $code = sanitize_text_field($_POST['coupon_code']);
    $order = wc_get_order($order_id);

    if (empty($code)) {
        $order->delete_meta_data('_meeting_addon_coupon');
        $order->save();
        wp_send_json_success(['message' => 'Coupon removed.', 'code' => '']);
    }

    $coupon = new WC_Coupon($code);
    if (!$coupon->get_id()) {
        wp_send_json_error('Invalid coupon code.');
    }

    $order->update_meta_data('_meeting_addon_coupon', $code);
    $order->save();

    wp_send_json_success(['message' => 'Coupon saved!', 'code' => $code]);
}


// Check if booking is active or not based on the product id

function get_phive_product_current_booking($product_id)
{

    global $wpdb;

    $now = current_time('timestamp');

    // Get order items for this product
    $items = $wpdb->get_results($wpdb->prepare("
        SELECT order_item_id
        FROM {$wpdb->prefix}woocommerce_order_itemmeta
        WHERE meta_key = '_product_id'
        AND meta_value = %d
    ", $product_id));

    if (empty($items))
        return false;

    $bookings = [];

    foreach ($items as $row) {

        $item_id = $row->order_item_id;

        $from = wc_get_order_item_meta($item_id, 'Booked From', true);
        $to = wc_get_order_item_meta($item_id, 'Booked To', true);

        if (!$from || !$to)
            continue;

        $from_ts = strtotime($from);
        $to_ts = strtotime($to);

        // Get order id
        $order_id = $wpdb->get_var($wpdb->prepare("
            SELECT order_id
            FROM {$wpdb->prefix}woocommerce_order_items
            WHERE order_item_id = %d
        ", $item_id));

        // Determine status
        if ($now >= $from_ts && $now <= $to_ts) {
            $status = 'active';
        } elseif ($now < $from_ts) {
            $status = 'upcoming';
        } else {
            $status = 'past';
        }

        $bookings[] = [
            'status' => $status,
            'order_id' => (int) $order_id,
            'order_item_id' => (int) $item_id,
            'from' => $from,
            'to' => $to,
            'from_ts' => $from_ts,
            'to_ts' => $to_ts,
        ];
    }

    if (empty($bookings))
        return false;

    /*
     Priority:
     1. Active
     2. Nearest upcoming
    */

    usort($bookings, function ($a, $b) {
        return $a['from_ts'] <=> $b['from_ts'];
    });

    foreach ($bookings as $b) {
        if ($b['status'] === 'active')
            return $b;
    }

    foreach ($bookings as $b) {
        if ($b['status'] === 'upcoming')
            return $b;
    }

    return $bookings[0]; // fallback past
}

add_filter('the_content', function ($content) {

    if (is_page('kautilya')) {

        $product_id = 1944;
        $booking = get_phive_product_current_booking($product_id);

        if ($booking && $booking['status'] === 'active') {
            //$order = wc_get_order( $booking['order_id'] );
            $order_id = $booking['order_id'];
            $booking_html = "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    const el = document.querySelector('.webapp_btn_1 a');
                    if (el) {
                        el.href = '/kautilya/user/';
                    }
                    
                    const el2 = document.querySelector('.webapp_btn_2 a');
                    if (el2) {
                        el2.href = '/kautilya/service-manager/';
                    }
                });
            </script>";
            //$booking_html = '<pre>' . print_r($order, false) . '</pre>';
            return $content . $booking_html;
        } else {
            $booking_html = "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    const el = document.querySelector('.webapp_btn_1');
                    if (el) {
                         el.style.display = 'none';
                    }
                    
                    const el2 = document.querySelector('.webapp_btn_2');
                    if (el2) {
                        el2.style.display = 'none';
                        const p = document.createElement('p');
                        p.textContent = 'No active booking';
                        p.classList.add('no_booking_p');
                        el2.insertAdjacentElement('afterend', p);
                    }
                });
            </script>";
            return $content . $booking_html;
        }

    } else if (is_page('brihaspati')) {

        $product_id = 1694;
        $booking = get_phive_product_current_booking($product_id);

        if ($booking && $booking['status'] === 'active') {
            //$order = wc_get_order( $booking['order_id'] );
            $order_id = $booking['order_id'];
            $booking_html = "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    const el = document.querySelector('.webapp_btn_1 a');
                    if (el) {
                        el.href = '/brihaspati/user/';
                    }
                    
                    const el2 = document.querySelector('.webapp_btn_2 a');
                    if (el2) {
                        el2.href = '/brihaspati/service-manager/';
                    }
                });
            </script>";
            //$booking_html = '<pre>' . print_r($order, false) . '</pre>';
            return $content . $booking_html;
        } else {
            $booking_html = "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    const el = document.querySelector('.webapp_btn_1');
                    if (el) {
                         el.style.display = 'none';
                    }
                    
                    const el2 = document.querySelector('.webapp_btn_2');
                    if (el2) {
                        el2.style.display = 'none';
                        const p = document.createElement('p');
                        p.textContent = 'No active booking';
                        p.classList.add('no_booking_p');
                        el2.insertAdjacentElement('afterend', p);
                    }
                });
            </script>";
            return $content . $booking_html;
        }

    }

    return $content;

});

// User Addon Dashboard
function user_addon_dashboard_shortcode($atts)
{

    $atts = shortcode_atts(
        array(
            'room_id' => '',
        ),
        $atts
    );

    ob_start();

    $file = get_stylesheet_directory() . '/includes/user-addon-dashboard.php';

    if (file_exists($file)) {
        include $file;
    }

    return ob_get_clean();
}

add_shortcode('user_addon_dashboard', 'user_addon_dashboard_shortcode');

// Service Manager Dashboard
function sm_addon_dashboard_shortcode($atts)
{

    $atts = shortcode_atts(
        array(
            'room_id' => '',
        ),
        $atts
    );

    ob_start();

    $file = get_stylesheet_directory() . '/includes/sm-addon-dashboard.php';

    if (file_exists($file)) {
        include $file;
    }

    return ob_get_clean();
}

add_shortcode('sm_addon_dashboard', 'sm_addon_dashboard_shortcode');







// Update phive_display_time_from and phive_display_time_to in exact serialized array format
add_action('woocommerce_update_order', 'sync_phive_display_times_on_update', 50, 1);
add_action('woocommerce_ajax_save_order_items', 'sync_phive_display_times_on_update', 50, 1);

function sync_phive_display_times_on_update($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    foreach ($order->get_items() as $item_id => $item) {

        $display_settings = get_option('ph_bookings_display_settigns');
        $text_customisation = isset($display_settings['text_customisation']) ? $display_settings['text_customisation'] : array();

        $booked_from_key = isset($text_customisation['booked_from_text']) && !empty($text_customisation['booked_from_text']) ? $text_customisation['booked_from_text'] : "Booked From";
        $booked_to_key = isset($text_customisation['booked_to_text']) && !empty($text_customisation['booked_to_text']) ? $text_customisation['booked_to_text'] : "Booked To";

        $booked_from_val = $item->get_meta(__($booked_from_key, 'bookings-and-appointments-for-woocommerce'));
        if (empty($booked_from_val)) {
            $booked_from_val = $item->get_meta('Booked From');
        }

        $booked_to_val = $item->get_meta(__($booked_to_key, 'bookings-and-appointments-for-woocommerce'));
        if (empty($booked_to_val)) {
            $booked_to_val = $item->get_meta('Booked To');
        }

        if (!empty($booked_from_val) && !empty($booked_to_val)) {

            $booked_from_str = is_array($booked_from_val) ? $booked_from_val[0] : $booked_from_val;
            $booked_to_str = is_array($booked_to_val) ? $booked_to_val[0] : $booked_to_val;

            $item->update_meta_data('phive_display_time_from', array($booked_from_str));
            $item->update_meta_data('phive_display_time_to', array($booked_to_str));

            $item->save();
        }
    }
}

// ==============================================================================
// Add Custom Buttons Under WooCommerce "Order Actions" Meta Box & Modal
// ==============================================================================
add_action('woocommerce_order_actions_end', 'phive_add_custom_order_action_buttons');
function phive_add_custom_order_action_buttons($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order)
        return;

    if ($order->get_meta('group_parent_order'))
        return;

    $status = $order->get_status();
    $is_already_handled = in_array($status, ['cancelled', 'refund-processed']);
    $paid = false;
    if ($order->get_meta('group_additional_payers')) {
        $payers = $order->get_meta('group_additional_payers');
        foreach ($payers as $payer) {
            $child = wc_get_order($payer['child_order_id']);
            if (!$child) {
                continue;
            }
            if ($child->get_date_paid()) {
                $paid = true;
                break;
            }
        }
    } else {
        if ($order->get_date_paid()) {
            $paid = true;
        }
    }

    // echo "<pre>";
    // print_r($booking);
    // echo "<pre>";
    ?>
    <style>
        /* Custom Modal Styles */
        .phive-modal-overlay {
            display: none;
            position: fixed;
            z-index: 999999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
        }

        .phive-modal-box {
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            width: 380px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.4);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }

        .phive-modal-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1d2327;
            display: flex;
            align-items: center;
        }

        .phive-modal-body textarea {
            width: 100%;
            height: 80px;
            margin-bottom: 15px;
            padding: 8px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            resize: none;
        }

        .phive-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>

    <li class="wide custom-order-buttons" style="display:flex; flex-direction:column; gap:10px;">
        <?php if (!$is_already_handled && get_current_status($order_id) !== 'Past'): ?>
            <?php if (!$paid): ?>
                <button type="button" class="button" id="btn_custom_edit_booking"
                    style="width:100%; text-align:center; justify-content:center; display:flex; align-items:center;">
                    <span class="dashicons dashicons-edit" style="margin-right:6px;"></span> Edit Booking
                </button>
            <?php endif; ?>
            <button type="button" class="button" id="btn_custom_cancel_booking"
                style="width:100%; text-align:center; justify-content:center; display:flex; align-items:center; color:#d9534f; border-color:#d9534f;">
                <span class="dashicons dashicons-dismiss" style="margin-right:6px;"></span> Cancel Booking
            </button>
        <?php endif; ?>

        <button type="button" class="button button-primary" id="btn_custom_delete_record"
            style="width:100%; text-align:center; justify-content:center; display:flex; align-items:center; background:#fff; border-color:#d9534f; color:#d9534f;">
            <span class="dashicons dashicons-trash" style="margin-right:6px;"></span> Delete Record
        </button>

        <div id="phive_cancel_modal" class="phive-modal-overlay">
            <div class="phive-modal-box">
                <div class="phive-modal-header">
                    <span class="dashicons dashicons-warning" style="color:#d9534f; margin-right:8px;"></span> Cancel
                    Booking
                </div>
                <div class="phive-modal-body">
                    <p style="margin-top:0; color:#50575e; font-size:13px;">Please enter a reason for cancellation:</p>
                    <textarea id="phive_cancel_reason_input" placeholder="e.g. Customer requested cancellation..."
                        required></textarea>
                </div>
                <div class="phive-modal-footer">
                    <button type="button" class="button" id="phive_modal_close_btn">Go Back</button>
                    <button type="button" class="button button-primary" style="" id="phive_modal_confirm_btn">Confirm
                        Cancellation</button>
                </div>
            </div>
        </div>
    </li>

    <script>
        jQuery(document).ready(function ($) {

            // Move modal to body on load to prevent parent overflow clipping
            $('#phive_cancel_modal').appendTo('body');

            // Action 1: Edit Booking -> Ask Confirmation, then Scroll to the Custom Booking Date & Slots editor
            $('#btn_custom_edit_booking').on('click', function (e) {
                e.preventDefault();

                // Added Confirmation for Edit Booking 
                var targetBox = $('#phive_booking_datetime_editor');
                if (targetBox.length) {
                    $('html, body').animate({
                        scrollTop: targetBox.offset().top - 50
                    }, 600);

                    // Add a temporary glow effect so admin knows where to edit
                    targetBox.css({ 'box-shadow': '0 0 15px rgba(34, 113, 177, 0.6)', 'transition': 'box-shadow 0.3s ease' });
                    setTimeout(function () {
                        targetBox.css('box-shadow', 'none');
                    }, 2500);
                } else {
                    alert('Booking Editor not found on this page.');
                }
            });

            // Action 2: Cancel Booking -> Show Custom Modal Popup
            $('#btn_custom_cancel_booking').on('click', function (e) {
                e.preventDefault();
                if (confirm('Are you sure you want to Cancel this booking.')) {
                    $('#phive_cancel_modal').css('display', 'flex');
                };
            });

            // Close Modal
            $('#phive_modal_close_btn').on('click', function (e) {
                e.preventDefault();
                $('#phive_cancel_modal').css('display', 'none');
                $('#phive_cancel_reason_input').val(''); // Clear the input field
            });

            // Confirm Cancellation from Modal
            $('#phive_modal_confirm_btn').on('click', function (e) {
                e.preventDefault();


                var cancelReason = $('#phive_cancel_reason_input').val();

                if (cancelReason == '') {
                    alert("Please enter the reason for cancellation.");
                    return;
                }

                // Dynamic Form Selector: Compatible with both Classic WooCommerce and New HPOS
                var $form = $('#post').length ? $('#post') : $('.save_order').closest('form');

                // Inject a hidden field into the main form to pass the reason to PHP
                if ($('#phive_cancellation_reason').length === 0) {
                    $form.append('<input type="hidden" name="phive_cancellation_reason" id="phive_cancellation_reason" value="" />');
                }
                $('#phive_cancellation_reason').val(cancelReason);

                // Change WooCommerce status dropdown to 'cancelled'
                $('#order_status').val('wc-cancelled').trigger('change');

                // Hide modal and show loading state
                $('#phive_cancel_modal').css('display', 'none');
                $('#btn_custom_cancel_booking').html('<span class="dashicons dashicons-update" style="margin-right:6px;"></span> Cancelling...').css('opacity', '0.5');

                // Click the primary Update button to save
                $('form#order').attr('novalidate', 'novalidate');
                $('.save_order').prop('disabled', false).trigger('click');
            });

            // Action 3: Delete Record -> Trigger the native WooCommerce "Move to Trash" link safely
            //$('#btn_custom_delete_record').on('click', function (e) {
            // e.preventDefault();
            // if (confirm('Are you sure you want to DELETE this record? This action will move it to trash.')) {
            //     var trashLink = $('.submitdelete.deletion').attr('href');
            //     if (trashLink) {
            //         window.location.href = trashLink; // Follows the exact secure trash link
            //     } else {
            //         alert('Trash link not found. It may have already been deleted.');
            //     }
            // }
            //});

            $('#btn_custom_delete_record').on('click', function (e) {
                e.preventDefault();
                if (confirm('Are you sure you want to DELETE this record? This action will move it to trash.')) {
                    var trashLink = $('.submitdelete.deletion').attr('href');
                    if (trashLink) {
                        $('#custom_delete_modal').remove();
                        var modalHtml = '<div id="custom_delete_modal" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;"><div style="background:#fff;padding:20px;border-radius:4px;width:300px;box-shadow:0 2px 10px rgba(0,0,0,0.2);"><h3>Deletion Reason</h3><textarea id="custom_delete_reason" style="width:100%;height:80px;margin-bottom:15px;" placeholder="Enter reason here..."></textarea><div style="text-align:right;"><button type="button" id="custom_delete_cancel" class="button" style="margin-right:10px;">Cancel</button><button type="button" id="custom_delete_confirm" class="button button-primary">Delete</button></div></div></div>';
                        $('body').append(modalHtml);
                        $('#custom_delete_cancel').on('click', function () {
                            $('#custom_delete_modal').remove();
                        });
                        $('#custom_delete_confirm').on('click', function () {
                            var deleteReason = $('#custom_delete_reason').val();
                            if (deleteReason == '') {
                                alert("Please enter the reason for deletion .");
                                return;
                            }
                            $('#custom_delete_modal').remove();
                            var orderId = $('#post_ID').val() || new URLSearchParams(window.location.search).get('post') || new URLSearchParams(window.location.search).get('id');
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'custom_direct_delete_order',
                                    order_id: orderId,
                                    reason: deleteReason
                                },
                                success: function (response) {
                                    if (response.success) {
                                        window.location.href = window.location.href.includes('page=wc-orders') ? 'admin.php?page=wc-orders' : 'edit.php?post_type=shop_order'; // Follows the exact secure trash link
                                    } else {
                                        alert('Failed to delete the order.');
                                    }
                                },
                                error: function () {
                                    alert('AJAX request failed.');
                                }
                            });
                        });
                    } else {
                        alert('Trash link not found. It may have already been deleted.');
                    }
                }
            });

        });
    </script>
    <?php
}

add_action('wp_ajax_custom_direct_delete_order', 'custom_direct_delete_order_handler');
function custom_direct_delete_order_handler()
{
    // 1. Get POST data
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';

    if (!$order_id) {
        wp_send_json_error('Invalid order ID.');
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error('Order not found.');
    }

    $child_payers = $order->get_meta('group_additional_payers');
    $addons = $order->get_meta('meeting_addons_billed');


    // 2. Save the reason to order meta and add an order note
    if (!empty($reason)) {
        $order->update_meta_data('_deletion_reason', $reason);
        $order->add_order_note('Booking moved to trash. Reason: ' . $reason);
        $order->save();
    }

    $order->update_status('cancelled', sprintf('Cancelled before deletion. Reason: %s', $reason));
    if (is_array($child_payers)) {
        foreach ($child_payers as $payer) {
            if (!empty($payer['child_order_id'])) {
                $c_order = wc_get_order($payer['child_order_id']);
                if ($c_order) {
                    $c_order->update_status('cancelled', sprintf('Cancelled before deletion. Reason: %s', $reason));
                    $c_order->delete(false);
                }
            }
        }
    }

    if (is_array($addons)) {
        foreach ($addons as $bill) {
            $addons_order = wc_get_order($bill);
            if ($addons_order) {
                $addons_order->update_status('cancelled', sprintf('Cancelled before deletion. Reason: %s', $reason));
                $addons_order->delete(false);
            }
        }
    }
    // 3. Move order to trash (HPOS compatible, false prevents force deletion)
    $order->delete(false);

    // 4. Send success response back to JS
    wp_send_json_success();
}

// Intercept when an order goes specifically from 'trash' back to 'cancelled'
add_action('woocommerce_order_status_trash_to_cancelled', 'custom_restore_child_orders_on_trash_to_cancelled', 10, 2);
function custom_restore_child_orders_on_trash_to_cancelled($order_id, $order)
{
    $child_payers = $order->get_meta('group_additional_payers');
    $addons = $order->get_meta('meeting_addons_billed');

    // Restore child orders
    if (is_array($child_payers)) {
        foreach ($child_payers as $payer) {
            if (!empty($payer['child_order_id'])) {
                $c_order = wc_get_order($payer['child_order_id']);
                if ($c_order) {
                    // CPT fallback compatibility check for untrashing
                    if (get_post_type($c_order->get_id()) === 'shop_order') {
                        wp_untrash_post($c_order->get_id());
                    }
                    // Bring the child order status back to cancelled
                    $c_order->update_status('cancelled', 'Child order restored from trash alongside main order.');
                }
            }
        }
    }
    if (is_array($addons)) {
        foreach ($addons as $bill) {
            $addons_order = wc_get_order($bill);
            if ($addons_order) {
                $addons_order->update_status('cancelled', 'Addons order restored from trash alongside main order.');
            }
        }
    }
}

add_action('wp_ajax_frontend_cancel_booking_with_reason', 'handle_frontend_cancellation');

function handle_frontend_cancellation()
{

    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';

    $order = wc_get_order($order_id);

    if (!$order || $order->get_user_id() !== get_current_user_id()) {
        wp_send_json_error(['message' => 'Unauthorized request.']);
    }

    // Update status to cancelled
    $order->update_status('cancelled', sprintf('Cancelled by user from frontend. Reason: %s', $reason));

    // Save reason to meta (matches backend logic)
    $order->update_meta_data('_cancellation_reason', $reason);
    $order->update_meta_data('_cancelled_by_user_id', get_current_user_id());
    $order->save();

    wp_send_json_success(['message' => 'Booking cancelled successfully.']);
}


// ==============================================================================
// 1. Enqueue Scripts & Styles for Stylish Calendar (Flatpickr) in Admin
// ==============================================================================
add_action('admin_enqueue_scripts', 'phive_admin_booking_ui_scripts');
function phive_admin_booking_ui_scripts($hook)
{
    $screen = get_current_screen();
    // Load only on WooCommerce Order Edit pages
    if ($screen && ($screen->id === 'woocommerce_page_wc-orders' || $screen->id === 'shop_order')) {
        wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), null, true);
    }
}

// ==============================================================================
// 2. Add Custom Meta Box on Order Edit Screen (Right Sidebar)
// ==============================================================================
add_action('add_meta_boxes', 'phive_add_booking_edit_meta_box', 10, 2);
function phive_add_booking_edit_meta_box($post_type, $post_or_order_object)
{
    $screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') && wc_get_page_screen_id('shop-order') ? wc_get_page_screen_id('shop-order') : 'shop_order';

    add_meta_box(
        'phive_booking_datetime_editor',
        'Booking Date & Slots',
        'phive_render_booking_datetime_meta_box',
        $screen,
        'side',
        'high' // Show at the top right
    );
}

// ==============================================================================
// 3. Render the Stylish UI Form with Slot Selection
// ==============================================================================
function phive_render_booking_datetime_meta_box($post_or_order_object)
{
    $order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
    if (!$order)
        return;

    $order_id = $order->get_id();
    $bookable_item = null;
    $item_id_to_edit = 0;

    // Get the first booking item from the order to populate default values
    foreach ($order->get_items() as $item_id => $item) {
        if (($item->get_meta('Booked From') && $item->get_meta('Booked To')) || ($item->get_meta('From') && $item->get_meta('To'))) {
            $bookable_item = $item;
            $item_id_to_edit = $item_id;
            break;
        }
    }

    if (!$bookable_item) {
        echo '<p style="color:#d9534f;">No active bookings found in this order.</p>';
        return;
    }

    // Extract product and asset IDs for database availability check
    $product_id = $bookable_item->get_product_id();
    $assets_meta = $bookable_item->get_meta('Assets');
    $asset_id = is_array($assets_meta) && !empty($assets_meta) ? $assets_meta[0] : $assets_meta;

    // Extract current dates
    $current_from = $bookable_item->get_meta('Booked From');
    $current_from = is_array($current_from) ? $current_from[0] : $current_from;

    $current_to = $bookable_item->get_meta('Booked To');
    $current_to = is_array($current_to) ? $current_to[0] : $current_to;

    // Safety fallback
    if (empty($current_to)) {
        $current_to = $bookable_item->get_meta('To');
        $current_to = is_array($current_to) ? $current_to[0] : $current_to;
        $temp_to_time = date('H:i', strtotime($current_to));

        if ($temp_to_time === '09:30')
            $current_to = date('Y-m-d', strtotime($current_to)) . ' 13:30';
        if ($temp_to_time === '14:00')
            $current_to = date('Y-m-d', strtotime($current_to)) . ' 18:00';
        if ($temp_to_time === '18:30')
            $current_to = date('Y-m-d', strtotime($current_to)) . ' 22:30';
    }

    $current_date = date('Y-m-d', strtotime($current_from));
    $from_time = date('H:i', strtotime($current_from));
    $to_time = date('H:i', strtotime($current_to));

    $from_mins = strtotime($from_time);
    $to_mins = strtotime($to_time);

    // Define Allowed Slots
    $slots = array(
        array('start' => '09:30', 'end' => '13:30', 'label' => '9:30 AM – 1:30 PM'),
        array('start' => '14:00', 'end' => '18:00', 'label' => '2:00 PM – 6:00 PM'),
        array('start' => '18:30', 'end' => '22:30', 'label' => '6:30 PM – 10:30 PM')
    );

    // Calculate Original Slot Quantity explicitly in PHP
    $orig_count = 0;
    foreach ($slots as $slot) {
        $slot_start_mins = strtotime($slot['start']);
        $slot_end_mins = strtotime($slot['end']);
        if ($slot_start_mins >= $from_mins && $slot_end_mins <= $to_mins) {
            $orig_count++;
        }
    }
    if ($orig_count == 0)
        $orig_count = 1; // Fallback to 1

    ?>
    <style>
        .phive-slot-label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
            user-select: none;
        }

        .phive-slot-label input {
            display: none;
        }

        .phive-slot-label span.phive-slot-text {
            display: block;
            padding: 5px 10px;
            border: 2px solid #8c8f94;
            border-radius: 6px;
            text-align: center;
            background: #fff;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
            position: relative;
        }

        /* Hover effect */
        /* .phive-slot-label:hover span.phive-slot-text:not(.disabled-slot) {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        border-color: #2271b1;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        background: #f6f7f7;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    } */

        /* Selected State with Checkmark */
        .phive-slot-label input:checked+span.phive-slot-text {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }

        .phive-slot-label input:checked+span.phive-slot-text::before {
            content: '✓ ';
            font-weight: 900;
            margin-right: 4px;
        }

        /* Disabled State with Lock Icon */
        .phive-slot-label input:disabled+span.phive-slot-text {
            background: #f0f0f1;
            color: #a7aaad;
            border-color: #dcdcde;
            cursor: not-allowed;
            text-decoration: line-through;
        }

        .phive-slot-label input:disabled+span.phive-slot-text::before {
            content: '🔒 ';
            margin-right: 4px;
            text-decoration: none !important;
            display: inline-block;
        }

        /* Loading Skeleton Effect */
        #phive_slots_container.slots-loading {
            opacity: 0.4;
            pointer-events: none;
            filter: grayscale(100%);
        }

        button#phive_update_booking_btn {
            background-image: linear-gradient(90deg, #2271b1 45%, #2271b1 100%);
            border-radius: 38px 38px 38px 38px;
        }

        span.flatpickr-next-month,
        span.flatpickr-prev-month {
            position: relative !important;
        }

        .flatpickr-month {
            order: -1 !important;
            height: 45px !important;
        }

        .flatpickr-current-month {
            left: 10px !important;
            width: 100% !important;
            text-align: left;
            padding: 0;
        }

        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year {
            color: #0f2552 !important;
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        .flatpickr-current-month .flatpickr-monthDropdown-months {
            margin-right: 15px;
            padding-left: 0;
            height: 45px;
        }

        .flatpickr-innerContainer {
            border-top: 0.8px solid #E4E5E7;
        }

        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            height: 25px !important;
        }

        .flatpickr-months .flatpickr-prev-month svg,
        .flatpickr-months .flatpickr-next-month svg {
            vertical-align: middle;
        }

        select.flatpickr-monthDropdown-months:focus {
            box-shadow: none !important;
        }

        .flatpickr-months svg path {
            fill: none;
            stroke: #000;
            stroke-width: 1.5px;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .flatpickr-day.today,
        .flatpickr-day.inRange,
        .flatpickr-day.prevMonthDay.inRange,
        .flatpickr-day.nextMonthDay.inRange,
        .flatpickr-day.today.inRange,
        .flatpickr-day.prevMonthDay.today.inRange,
        .flatpickr-day.nextMonthDay.today.inRange,
        .flatpickr-day:not(.flatpickr-disabled):hover,
        .flatpickr-day.prevMonthDay:hover,
        .flatpickr-day.nextMonthDay:hover,
        .flatpickr-day:focus,
        .flatpickr-day.prevMonthDay:focus,
        .flatpickr-day.nextMonthDay:focus {
            background: #1763B9 !important;
            border: 0px solid #1763B9 !important;
            color: #fff;
        }

        input.flatpickr-input.form-control.input {
            width: 100%;
            text-align: center;
            font-weight: 700;
            padding: 3px;
        }
    </style>

    <?php
    $paid = false;
    if ($order->get_meta('group_additional_payers')) {
        $payers = $order->get_meta('group_additional_payers');
        if ($order->get_date_paid()) {
            $paid = true;
        }
        foreach ($payers as $payer) {
            $child = wc_get_order($payer['child_order_id']);
            if (!$child) {
                continue;
            }
            if ($child->get_date_paid()) {
                $paid = true;
                break;
            }
        }
    } else {
        if ($order->get_date_paid()) {
            $paid = true;
        }
    }
    if ($order->get_status() === 'cancelled' || $order->get_status() === 'refund-processed') {
        echo "This Booking is Cancelled.";
        return;
    }
    if (get_current_status($order_id) === 'Past' && $order->get_status() === 'completed') {
        echo "This Booking is completed.";
        return;
    }
    if ($paid) {
        echo "This booking cannot be edited because a payment has already been received from one or more parties.";
        return;
    }
    ?>

    <div class="phive-booking-edit-wrap" style="padding: 5px;">
        <p>
            <label style="font-weight:600; display:block; margin-bottom:5px;">Change Date:</label>
            <input type="text" id="phive_new_date" class="flatpickr-input" value="<?php echo esc_attr($current_date); ?>"
                placeholder="<?php echo esc_attr($current_date); ?>"
                style="width: 100%; border-radius: 4px; border: 1px solid #8c8f94; padding: 6px; box-shadow: 0 1px 2px rgba(0,0,0,.075);text-align: center;font-weight: 700;font-size: 16px;">
        </p>

        <div style="margin-top: 15px;">
            <label
                style="font-weight:600; display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                Change Time Slots:
                <span
                    style="font-size:11px; background:#e5f5fa; color:#005a9e; padding:3px 8px; border-radius:12px; font-weight:700;">
                    Required: <?php echo esc_html($orig_count); ?> Slot(s)
                </span>
            </label>
            <div id="phive_slots_container">
                <?php foreach ($slots as $slot):
                    $slot_start_mins = strtotime($slot['start']);
                    $slot_end_mins = strtotime($slot['end']);
                    // Auto-check slots that fall within the current booking's time range
                    $is_checked = ($slot_start_mins >= $from_mins && $slot_end_mins <= $to_mins) ? 'checked' : '';
                    ?>
                    <label class="phive-slot-label">
                        <input type="checkbox" class="phive-slot-cb" data-start="<?php echo esc_attr($slot['start']); ?>"
                            data-end="<?php echo esc_attr($slot['end']); ?>"
                            data-label="<?php echo esc_attr($slot['label']); ?>" <?php echo $is_checked; ?>>
                        <span class="phive-slot-text"><?php echo esc_html($slot['label']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <p style="margin-top: 20px;">
            <button type="button" id="phive_update_booking_btn" class="button button-primary button-large"
                style="width: 100%; text-align: center; justify-content:center; display:flex;font-size:15px">Update Booking
                Details</button>
            <span id="phive_update_msg" style="display:block; margin-top:10px; font-weight:600; text-align:center;"></span>
        </p>

        <input type="hidden" id="phive_hidden_from_time" value="<?php echo esc_attr($from_time); ?>">
        <input type="hidden" id="phive_hidden_to_time" value="<?php echo esc_attr($to_time); ?>">

        <input type="hidden" id="phive_original_date" value="<?php echo esc_attr($current_date); ?>">
        <input type="hidden" id="phive_original_from" value="<?php echo esc_attr($from_time); ?>">
        <input type="hidden" id="phive_original_to" value="<?php echo esc_attr($to_time); ?>">
        <input type="hidden" id="phive_original_slot_count" value="<?php echo esc_attr($orig_count); ?>">

        <input type="hidden" id="phive_edit_order_id" value="<?php echo $order_id; ?>">
        <input type="hidden" id="phive_edit_item_id" value="<?php echo $item_id_to_edit; ?>">
        <input type="hidden" id="phive_edit_product_id" value="<?php echo esc_attr($product_id); ?>">
        <input type="hidden" id="phive_edit_asset_id" value="<?php echo esc_attr($asset_id); ?>">
    </div>

    <script>
        jQuery(document).ready(function ($) {
            // Initialize Stylish Calendar
            if (typeof flatpickr !== 'undefined') {

                var disabledDates = [
                    "2026-01-01",
                    "2026-01-26", // Republic Day 
                    "2026-03-04", // Holi
                    "2026-03-20", // Eid  
                    "2026-08-15", // Independence Day 
                    "2026-08-28", // Rakhi 
                    "2026-10-02", // Gandhi Jayanti
                    "2026-10-20", // Dussehra
                    "2026-11-08", // Diwali
                    "2026-11-09", // Guru Nanak Jayanti
                    "2026-12-25"  // Christmas
                ];

                flatpickr("#phive_new_date", {
                    dateFormat: "Y-m-d", // Internal backend format kept as Y-m-d for correct db logic
                    altInput: true,      // Enables a separate visible input for frontend users
                    altFormat: "d-m-Y",  // Visible frontend format DD-MM-YYYY
                    disableMobile: "true",
                    minDate: "today", // Disables all past dates automatically
                    disable: disabledDates, // Disables the specific holiday list
                    onChange: function (selectedDates, dateStr, instance) {
                        // Reset all selected slots when a new date is selected
                        $('.phive-slot-cb').prop('checked', false);
                        phive_fetch_booked_slots(dateStr);
                    }
                });
            }

            // Fetch already booked slots from the database for the selected date
            function phive_fetch_booked_slots(selectedDate) {
                // Add loading state
                $('#phive_slots_container').addClass('slots-loading');
                $('.phive-slot-cb').prop('disabled', false).each(function () {
                    $(this).siblings('.phive-slot-text').text($(this).data('label')).removeClass('disabled-slot');
                });
                $('#phive_update_msg').text('Fetching availability...').css('color', '#888');
                $('#phive_update_booking_btn').prop('disabled', true).css('opacity', '0.5');

                var data = {
                    action: 'phive_ajax_get_booked_slots',
                    security: '<?php echo wp_create_nonce("phive_update_booking_nonce"); ?>',
                    product_id: $('#phive_edit_product_id').val(),
                    asset_id: $('#phive_edit_asset_id').val(),
                    order_id: $('#phive_edit_order_id').val(),
                    item_id: $('#phive_edit_item_id').val(),
                    date: selectedDate
                };

                $.post(ajaxurl, data, function (response) {
                    $('#phive_slots_container').removeClass('slots-loading');

                    if (response.success) {
                        var bookedSlots = response.data;
                        bookedSlots.forEach(function (slotStartTime) {
                            var checkbox = $('.phive-slot-cb[data-start="' + slotStartTime + '"]');
                            if (checkbox.length) {
                                checkbox.prop('disabled', true).prop('checked', false);
                                checkbox.siblings('.phive-slot-text').text(checkbox.data('label') + ' (Booked)').addClass('disabled-slot');
                            }
                        });

                        // Smart Slot Quantity Matching
                        var maxSlots = parseInt($('#phive_original_slot_count').val(), 10);
                        var originalDate = $('#phive_original_date').val();
                        var origFrom = $('#phive_original_from').val();
                        var origTo = $('#phive_original_to').val();

                        if (origFrom && origTo) {
                            var origStartMins = parseInt(origFrom.split(':')[0], 10) * 60 + parseInt(origFrom.split(':')[1], 10);
                            var origEndMins = parseInt(origTo.split(':')[0], 10) * 60 + parseInt(origTo.split(':')[1], 10);

                            var originalSlotStarts = [];

                            $('.phive-slot-cb').each(function () {
                                var slotStart = $(this).data('start');
                                var slotEnd = $(this).data('end');
                                var slotStartMins = parseInt(slotStart.split(':')[0], 10) * 60 + parseInt(slotStart.split(':')[1], 10);
                                var slotEndMins = parseInt(slotEnd.split(':')[0], 10) * 60 + parseInt(slotEnd.split(':')[1], 10);

                                if (slotStartMins >= origStartMins && slotEndMins <= origEndMins) {
                                    originalSlotStarts.push(slotStart);
                                }
                            });

                            if (selectedDate === originalDate) {
                                originalSlotStarts.forEach(function (st) {
                                    $('.phive-slot-cb[data-start="' + st + '"]').prop('checked', true);
                                });
                            } else {
                                var exactSlotsFree = true;
                                originalSlotStarts.forEach(function (st) {
                                    if ($('.phive-slot-cb[data-start="' + st + '"]').prop('disabled')) {
                                        exactSlotsFree = false;
                                    }
                                });

                                if (exactSlotsFree && maxSlots > 0) {
                                    originalSlotStarts.forEach(function (st) {
                                        $('.phive-slot-cb[data-start="' + st + '"]').prop('checked', true);
                                    });
                                } else if (maxSlots > 0) {
                                    var allCbs = $('.phive-slot-cb');
                                    var blockFound = false;

                                    for (var i = 0; i <= allCbs.length - maxSlots; i++) {
                                        var canSelect = true;
                                        for (var j = 0; j < maxSlots; j++) {
                                            if ($(allCbs[i + j]).prop('disabled')) {
                                                canSelect = false;
                                                break;
                                            }
                                        }
                                        if (canSelect) {
                                            for (var j = 0; j < maxSlots; j++) {
                                                $(allCbs[i + j]).prop('checked', true);
                                            }
                                            blockFound = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        $('#phive_update_msg').text('');
                        phive_validate_slots();
                    } else {
                        $('#phive_update_msg').text('');
                        phive_validate_slots();
                    }
                }).fail(function () {
                    $('#phive_slots_container').removeClass('slots-loading');
                    $('#phive_update_msg').text('');
                    phive_validate_slots();
                });
            }

            // Handle Exact Slot Selection & Validation
            function phive_validate_slots() {
                var checked = $('.phive-slot-cb:checked');
                var maxSlots = parseInt($('#phive_original_slot_count').val(), 10);

                if (checked.length !== maxSlots) {
                    $('#phive_update_msg').text('⚠️ Please select exactly ' + maxSlots + ' slot(s).').css('color', '#888');
                    $('#phive_update_booking_btn').prop('disabled', true).css('opacity', '0.5');
                    $('#phive_hidden_from_time').val('');
                    $('#phive_hidden_to_time').val('');
                    return;
                }

                var startTimes = [];
                var endTimes = [];
                var checkedIndexes = [];

                $('.phive-slot-cb').each(function (index) {
                    if ($(this).prop('checked')) {
                        checkedIndexes.push(index);
                        startTimes.push($(this).data('start'));
                        endTimes.push($(this).data('end'));
                    }
                });

                // Ensure continuous mapping (Strict exact sequence check)
                var isContiguous = true;
                for (var i = 1; i < checkedIndexes.length; i++) {
                    if (checkedIndexes[i] !== checkedIndexes[i - 1] + 1) {
                        isContiguous = false;
                        break;
                    }
                }

                if (!isContiguous) {
                    $('#phive_update_msg').text('❌ Selected slots must be continuous.').css('color', '#d9534f');
                    $('#phive_update_booking_btn').prop('disabled', true).css('opacity', '0.5');
                    $('#phive_hidden_from_time').val('');
                    $('#phive_hidden_to_time').val('');
                    return;
                }

                startTimes.sort();
                endTimes.sort();

                var finalStart = startTimes[0];
                var finalEnd = endTimes[endTimes.length - 1];

                // Update hidden values to send via AJAX
                $('#phive_hidden_from_time').val(finalStart);
                $('#phive_hidden_to_time').val(finalEnd);

                $('#phive_update_msg').text('');
                $('#phive_update_booking_btn').prop('disabled', false).css('opacity', '1');
            }

            // Trigger limit lock when checkboxes are clicked
            $(document).on('change', '.phive-slot-cb', function () {
                var maxSlots = parseInt($('#phive_original_slot_count').val(), 10);
                var isChecked = $(this).prop('checked');
                var slotStart = $(this).data('start');

                // Radio-button like behavior for 1-slot limit
                if (maxSlots === 1 && isChecked) {
                    $('.phive-slot-cb').not(this).prop('checked', false);
                } else {
                    // Normal limit check logic for more than 1 slots
                    var checkedCount = $('.phive-slot-cb:checked').length;

                    if (checkedCount > maxSlots) {
                        $(this).prop('checked', false);
                        return;
                    }

                    // Smart UX: If user needs 2 slots and clicks one, try to auto-select the next available one
                    if (isChecked && maxSlots > 1 && checkedCount < maxSlots) {
                        var $nextSlot = $(this).parent().next('.phive-slot-label').find('.phive-slot-cb');
                        if ($nextSlot.length && !$nextSlot.prop('disabled') && !$nextSlot.prop('checked')) {
                            $nextSlot.prop('checked', true);
                        } else {
                            var $prevSlot = $(this).parent().prev('.phive-slot-label').find('.phive-slot-cb');
                            if ($prevSlot.length && !$prevSlot.prop('disabled') && !$prevSlot.prop('checked')) {
                                $prevSlot.prop('checked', true);
                            }
                        }
                    }
                }

                // If user unselects the middle slot (14:00), automatically unselect the last slot (18:30)
                if (!isChecked && slotStart === '14:00') {
                    $('.phive-slot-cb[data-start="18:30"]').prop('checked', false);
                }

                phive_validate_slots();
            });

            // Fetch booked slots and validate on initial page load
            phive_fetch_booked_slots($('#phive_new_date').val());

            // Handle AJAX Update Submission
            $('#phive_update_booking_btn').on('click', function (e) {
                e.preventDefault();

                var finalFrom = $('#phive_hidden_from_time').val();
                var finalTo = $('#phive_hidden_to_time').val();

                if (!finalFrom || !finalTo) {
                    $('#phive_update_msg').text('❌ Please select a valid slot combination.').css('color', '#d9534f');
                    return;
                }

                var btn = $(this);
                btn.text('Updating...').prop('disabled', true).css('opacity', '0.5');
                $('#phive_update_msg').text('').css('color', 'black');

                var data = {
                    action: 'phive_ajax_update_booking_datetime',
                    security: '<?php echo wp_create_nonce("phive_update_booking_nonce"); ?>',
                    order_id: $('#phive_edit_order_id').val(),
                    item_id: $('#phive_edit_item_id').val(),
                    new_date: $('#phive_new_date').val(), // Always receives 'Y-m-d' from hidden actual input
                    new_from_time: finalFrom,
                    new_to_time: finalTo
                };

                $.post(ajaxurl, data, function (response) {
                    if (response.success) {
                        $('#phive_update_msg').text('✅ Successfully Updated! Reloading...').css('color', '#00a32a');
                        setTimeout(function () { location.reload(); }, 1200);
                    } else {
                        $('#phive_update_msg').text('❌ Error: ' + response.data).css('color', '#d9534f');
                        btn.text('Update Booking Details').prop('disabled', false).css('opacity', '1');
                    }
                });
            });
        });
    </script>
    <?php
}

// ==============================================================================
// 4. AJAX Handler to Process and Sync the Data Everywhere (Update Booking)
// ==============================================================================
add_action('wp_ajax_phive_ajax_update_booking_datetime', 'phive_ajax_update_booking_datetime_handler');
function phive_ajax_update_booking_datetime_handler()
{
    check_ajax_referer('phive_update_booking_nonce', 'security');

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $new_date = sanitize_text_field($_POST['new_date']);
    $new_from_time = sanitize_text_field($_POST['new_from_time']);
    $new_to_time = sanitize_text_field($_POST['new_to_time']);

    if (!$order_id || !$item_id || !$new_date || !$new_from_time || !$new_to_time) {
        wp_send_json_error('Missing required data.');
    }

    $order = wc_get_order($order_id);
    if (!$order)
        wp_send_json_error('Invalid order.');

    $item = $order->get_item($item_id);
    $order_status = $order->get_status();
    $status_label = wc_get_order_status_name($order_status);
    if (!$item)
        wp_send_json_error('Invalid item.');

    // Construct raw datetime strings for database
    $new_from_raw = $new_date . ' ' . $new_from_time;
    $new_to_raw = $new_date . ' ' . $new_to_time;

    $last_slot_start_time = $new_to_time; // Fallback
    if ($new_to_time === '13:30') {
        $last_slot_start_time = '09:30';
    } elseif ($new_to_time === '18:00') {
        $last_slot_start_time = '14:00';
    } elseif ($new_to_time === '22:30') {
        $last_slot_start_time = '18:30';
    }
    $new_to_raw_for_meta = $new_date . ' ' . $last_slot_start_time;

    // Construct formatted display strings (e.g., February 25, 2026 2:00 pm)
    $new_from_formatted = date('F j, Y g:i a', strtotime($new_from_raw));
    $new_to_formatted = date('F j, Y g:i a', strtotime($new_to_raw));

    // 1. Update Core 'From' and 'To' fields in array format
    $item->update_meta_data('From', array($new_from_raw));
    $item->update_meta_data('To', array($new_to_raw_for_meta));

    // 2. Dynamically fetch labels if renamed in plugin settings
    $display_settings = get_option('ph_bookings_display_settigns');
    $text_customisation = isset($display_settings['text_customisation']) ? $display_settings['text_customisation'] : array();

    $booked_from_key = !empty($text_customisation['booked_from_text']) ? $text_customisation['booked_from_text'] : "Booked From";
    $booked_to_key = !empty($text_customisation['booked_to_text']) ? $text_customisation['booked_to_text'] : "Booked To";

    $booked_from_label = __($booked_from_key, 'bookings-and-appointments-for-woocommerce');
    $booked_to_label = __($booked_to_key, 'bookings-and-appointments-for-woocommerce');

    // 3. Update 'Booked From' and 'Booked To' exact labels
    $item->update_meta_data($booked_from_label, $new_from_formatted);
    $item->update_meta_data($booked_to_label, $new_to_formatted);

    // Fallbacks
    if ($item->meta_exists('Booked From'))
        $item->update_meta_data('Booked From', $new_from_formatted);
    if ($item->meta_exists('Booked To'))
        $item->update_meta_data('Booked To', $new_to_formatted);

    // Save item changes
    $item->save();

    // 4. Trigger our background functions manually to sync EVERYTHING!

    // Sync 'phive_display_time_from' & 'phive_display_time_to'
    if (function_exists('sync_phive_display_times_on_update')) {
        sync_phive_display_times_on_update($order_id);
    }

    // Sync Buffer Freez Times, Time Slots Cache, and Google Calendar
    if (function_exists('phive_booking_admin_update_order_dates')) {
        phive_booking_admin_update_order_dates($order_id);
    }

    if ($order->get_meta('group_parent_order')) {
        $parent_order = wc_get_order($order->get_meta('group_parent_order'));
        $booking_details = get_booking_details($parent_order);
    } else {
        $booking_details = get_booking_details($order);
    }

    $booking_url = get_bloginfo('url') . '/my-account/view-order/' . $order_id;
    $customer_name = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
    $customer_phone = $order->get_billing_phone();

    $subject = "Accordhub – Your Booking Schedule is Updated (Booking ID - {$booking_details['id']})";
    $email_heading = "Your Room Booking Schedule is Updated as Requested.";

    // Message Body
    $message = "<p>Dear Customer,</p>";
    $message .= "<p>Your request for Room booking date/time slot change has been received and accepted.</p>";
    $message .= "<p>Please find the updated booking details below:</p>";
    $message .= "<p><strong>Booking ID:</strong> {$booking_details['id']}</p>
            <p><strong>Room:</strong> {$booking_details['room']}</p>
            <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>
            <p><strong>Status:</strong> {$status_label}</p>";
    if ($order->get_meta('group_parent_order') || $order->get_meta('group_additional_payers')) {
        $message .= "<p><strong>Requested by:</strong> {$booking_details['user']}</p>";
    }
    $message .= "<p class='btn_p'><a href='{$booking_url}'>View Booking Details</a></p>";


    $components = [
        [
            'type' => 'header',
            'parameters' => [
                [
                    'type' => 'document',
                    'document' => [
                        'link' => 'https://staging.accordhub.in/wp-content/uploads/invoice-37698.pdf',
                        'filename' => 'receipt-' . $order_id . '.pdf'
                    ]
                ]
            ]
        ],
        [
            'type' => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => $customer_name],
                ['type' => 'text', 'text' => $booking_details['id']],
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

    send_woocommerce_custom_email($order->get_billing_email(), $subject, $email_heading, $message);
    send_whatsapp_template_msg($order->get_billing_phone(), 'payment_completed_booking_confirmed', $components);

    if ($order->get_meta('group_additional_payers')) {
        $childs = $order->get_meta('group_additional_payers');
        $sent_count = 0;

        foreach ($childs as $to) {
            $booking_url_child = get_bloginfo('url') . '/my-account/view-order/' . $to['child_order_id'];

            $subject_child = "Accordhub – Your Booking Schedule is Updated (Booking ID - {$booking_details['id']})";
            $email_heading_child = "Your Room Booking Schedule is Updated as Requested.";

            // Message Body
            $message_child = "<p>Dear Customer,</p>";
            $message_child .= "<p>Your request for Room booking date/time slot change has been received and accepted.</p>";
            $message_child .= "<p>Please find the updated booking details below:</p>";
            $message_child .= "<p><strong>Booking ID:</strong> {$booking_details['id']}</p>
                                <p><strong>Room:</strong> {$booking_details['room']}</p>
                                <p><strong>Date & Time:</strong> {$booking_details['datetime']}</p>
                                <p><strong>Status:</strong> {$status_label}</p>";
            $message_child .= "<p><strong>Requested by:</strong> {$booking_details['user']}</p>";
            $message_child .= "<p class='btn_p'><a href='{$booking_url_child}'>View Booking Details</a></p>";
            //$to = sanitize_email(trim($to['email']));


            $components = [
                [
                    'type' => 'header',
                    'parameters' => [
                        [
                            'type' => 'document',
                            'document' => [
                                'link' => 'https://staging.accordhub.in/wp-content/uploads/invoice-37698.pdf',
                                'filename' => 'receipt-' . $to['child_order_id'] . '.pdf'
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $to['name']],
                        ['type' => 'text', 'text' => $booking_details['id']],
                        ['type' => 'text', 'text' => $booking_details['room']],
                        ['type' => 'text', 'text' => $booking_details['datetime']]
                    ]
                ]
            ];
            if (strlen($to['phone']) === 10) {
                $customer_phone = '91' . $to['phone'];
            } elseif (strlen($to['phone']) === 11 && substr($to['phone'], 0, 1) === '0') {
                $customer_phone = '91' . substr($to['phone'], 1);
            }

            send_whatsapp_template_msg($customer_phone, 'payment_completed_booking_confirmed', $components);


            if (is_email($to['email'])) {
                // Use your custom wrapper function
                if (function_exists('send_woocommerce_custom_email')) {
                    send_woocommerce_custom_email(
                        $to['email'],
                        $subject_child,
                        $email_heading_child,
                        $message_child
                    );
                    $sent_count++;
                } else {
                    // Fallback if function is missing (Safety)
                    wp_mail($to['email'], $subject_child, $message_child, ['Content-Type: text/html; charset=UTF-8']);
                    $sent_count++;
                }
            }
        }
    }
    wp_send_json_success('Updated successfully');
}

// ==============================================================================
// 5. AJAX Handler to Fetch Already Booked Slots from Database (Availability Table)
// ==============================================================================
add_action('wp_ajax_phive_ajax_get_booked_slots', 'phive_ajax_get_booked_slots_handler');
function phive_ajax_get_booked_slots_handler()
{
    check_ajax_referer('phive_update_booking_nonce', 'security');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $date = sanitize_text_field($_POST['date']);

    if (!$product_id || !$date) {
        wp_send_json_error('Missing data.');
    }

    global $wpdb;

    // We only want to search bookings matching the chosen date.
    $like_date = $wpdb->esc_like($date) . '%';

    // Construct SQL querying PluginHive's true availability table directly
    $sql = "
        SELECT booked_date, booked_date_end 
        FROM {$wpdb->prefix}ph_bookings_availability_calculation_data
        WHERE product_id = %d
          AND booking_status NOT IN ('canceled', 'cancelled', 'refunded', 'failed', 'trash')
          AND woocommerce_order_status NOT IN ('canceled', 'cancelled', 'refunded', 'failed', 'trash')
          AND booking_type != 'cart'
          AND booked_date LIKE %s
    ";

    $args = array($product_id, $like_date);

    // Exclude the current order being edited so its slots don't show up as 'booked'
    if ($order_id) {
        $sql .= " AND order_id != %d";
        $args[] = $order_id;
    }

    $prepared_sql = $wpdb->prepare($sql, $args);
    $results = $wpdb->get_results($prepared_sql);

    $booked_slots = array();

    if ($results) {
        foreach ($results as $row) {
            $start_time = strtotime($row->booked_date);
            $end_time = strtotime($row->booked_date_end);

            // Check which of our strict slots this booking overlaps with
            // Even if a booking spans across multiple slots, it will block all the correct checkboxes.
            if ($start_time <= strtotime($date . ' 09:30:00') && $end_time > strtotime($date . ' 09:30:00')) {
                $booked_slots[] = '09:30';
            }
            if ($start_time <= strtotime($date . ' 14:00:00') && $end_time > strtotime($date . ' 14:00:00')) {
                $booked_slots[] = '14:00';
            }
            if ($start_time <= strtotime($date . ' 18:30:00') && $end_time > strtotime($date . ' 18:30:00')) {
                $booked_slots[] = '18:30';
            }
        }
    }

    wp_send_json_success(array_unique($booked_slots));
}



function get_current_status($order_id)
{
    $order = wc_get_order($order_id);

    if (!$order) {
        return '-';
    }

    $order_booking_from = null;
    $order_booking_to = null;

    // Loop through items to fetch booking meta from phive_booking
    foreach ($order->get_items() as $item) {
        if ($item->get_product() && $item->get_product()->get_type() === 'phive_booking') {

            // Handle group/child orders by checking the parent order
            $parent_order_id = $order->get_meta('group_parent_order');
            $target_items = [];

            if ($parent_order_id) {
                $parent_order = wc_get_order($parent_order_id);
                if ($parent_order) {
                    $target_items = $parent_order->get_items();
                }
            } else {
                $target_items = [$item];
            }

            foreach ($target_items as $t_item) {
                if ($t_item->get_product() && $t_item->get_product()->get_type() === 'phive_booking') {
                    $from_date = $t_item->get_meta('Booked From');
                    $to_date = $t_item->get_meta('Booked To');

                    // Fallback to 'phive_display_time_from/to' if 'Booked From/To' is missing
                    if (empty($from_date)) {
                        $from_meta = $t_item->get_meta('phive_display_time_from');
                        $from_date = is_array($from_meta) ? ($from_meta[0] ?? '') : $from_meta;
                    }
                    if (empty($to_date)) {
                        $to_meta = $t_item->get_meta('phive_display_time_to');
                        $to_date = is_array($to_meta) ? ($to_meta[0] ?? '') : $to_meta;
                    }

                    if (!empty($from_date) && !empty($to_date)) {
                        $from_ts = strtotime($from_date);
                        $to_ts = strtotime($to_date);

                        // Track earliest start and latest end for this order
                        if (!$order_booking_from || $from_ts < $order_booking_from) {
                            $order_booking_from = $from_ts;
                        }
                        if (!$order_booking_to || $to_ts > $order_booking_to) {
                            $order_booking_to = $to_ts;
                        }
                    }
                }
            }
        }
    }

    if (!$order_booking_from || !$order_booking_to) {
        return '-';
    }

    $now = current_time('timestamp');

    // Classify
    if ($now > $order_booking_to) {
        return 'Past';
    } elseif ($now < $order_booking_from) {
        return 'Upcoming';
    } else {
        return 'Active';
    }
}


// 1. Add the "Schedule Status" column to the Orders table header (HPOS & Legacy)
// add_filter('manage_woocommerce_page_wc-orders_columns', 'phive_add_booking_status_column', 20);
// add_filter('manage_edit-shop_order_columns', 'phive_add_booking_status_column', 20);
function phive_add_booking_status_column($columns)
{
    if ($_GET['status'] !== 'trash') {


        $new_columns = array();
        $insert_index = 5;
        $current_index = 0;

        foreach ($columns as $key => $value) {
            // Insert our custom column when the counter matches
            if ($current_index === $insert_index) {
                $new_columns['booking_date_time'] = 'Booking Date & Time';
                $new_columns['booking_status'] = 'Schedule Status';
            }
            $new_columns[$key] = $value;
            $current_index++;
        }

        // Fallback just in case the table has fewer columns than the index
        if (!isset($new_columns['booking_status'])) {
            $new_columns['booking_date_time'] = 'Booking Date & Time';
            $new_columns['booking_status'] = 'Schedule Status';
        }

        return $new_columns;
    } else {
        return $columns;
    }
}

// 2. Populate the column for HPOS
//add_action('manage_woocommerce_page_wc-orders_custom_column', 'phive_populate_booking_status_column_hpos', 10, 2);
function phive_populate_booking_status_column_hpos($column_name, $order)
{
    if ($column_name === 'booking_status') {
        // HPOS passes the order object directly
        $order_id = $order->get_id();
        echo get_current_status($order_id);
    }
}


/**
 * 1. Add the custom column to the HPOS orders table
 */
//add_filter('manage_woocommerce_page_wc-orders_columns', 'add_custom_hpos_order_column');
function add_custom_hpos_order_column($columns)
{
    $new_columns = [];

    foreach ($columns as $key => $name) {
        $new_columns[$key] = $name;

        // Change 'order_status' to whatever column you want to insert your custom column AFTER.
        // Other common keys: 'order_number', 'order_date', 'shipping_address', 'order_total'
        if ('order_date' === $key) {
            $new_columns['payment_status'] = __('Payment Status', 'woocommerce');
        }
    }

    return $new_columns;
}

/**
 * 2. Populate the custom column with data
 */
//add_action('manage_woocommerce_page_wc-orders_custom_column', 'populate_custom_hpos_order_column', 10, 2);
function populate_custom_hpos_order_column($column_name, $order)
{

    // Check if we are rendering our specific column
    if ('payment_status' === $column_name) {
        if (verify_razorpay_payment($order->get_id()) == 'Failed') {
            echo track_razorpay_by_order_id($order->get_id());
        } else {
            echo verify_razorpay_payment($order->get_id());
        }

    }
}

// 3. Add the UI Dropdown Filter (HPOS & Legacy)
add_action('restrict_manage_posts', 'phive_add_booking_status_filter_dropdown');
add_action('woocommerce_order_list_table_restrict_manage_orders', 'phive_add_booking_status_filter_dropdown');
function phive_add_booking_status_filter_dropdown()
{
    global $typenow, $pagenow;

    // Ensure we only show this on the Legacy Orders or HPOS Orders page
    if ('shop_order' !== $typenow && ('admin.php' !== $pagenow || !isset($_GET['page']) || 'wc-orders' !== $_GET['page'])) {
        return;
    }

    $current_status = isset($_GET['filter_booking_status']) ? sanitize_text_field($_GET['filter_booking_status']) : '';

    // UPDATED: Now filtering for the specific dynamic statuses
    $statuses = array(
        'Past' => 'Past',
        'Upcoming' => 'Upcoming',
        'Active' => 'Active'
    );

    echo '<select name="filter_booking_status" id="filter_booking_status">';
    echo '<option value="">Booking Schedule</option>';
    foreach ($statuses as $value => $label) {
        printf(
            '<option value="%s" %s>%s</option>',
            esc_attr($value),
            selected($current_status, $value, false),
            esc_html($label)
        );
    }
    echo '</select>';
}

// Helper function to dynamically calculate matching order IDs 
function phive_get_order_ids_by_booking_status($target_status)
{
    $matched_ids = array();

    // Fetch all order IDs to run against the dynamic get_current_status function
    $all_order_ids = wc_get_orders(array(
        'limit' => -1,
        'return' => 'ids',
    ));

    foreach ($all_order_ids as $order_id) {
        if (get_current_status($order_id) === $target_status) {
            $matched_ids[] = $order_id;
        }
    }

    // Return array(0) if no matches are found so the query returns empty properly
    return empty($matched_ids) ? array(0) : $matched_ids;
}

// 4. Process the UI Filter for Legacy
add_action('pre_get_posts', 'phive_filter_orders_by_booking_status_legacy');
function phive_filter_orders_by_booking_status_legacy($query)
{
    global $typenow;
    if ('shop_order' === $typenow && is_admin() && $query->is_main_query() && isset($_GET['filter_booking_status']) && $_GET['filter_booking_status'] !== '') {
        $target_status = sanitize_text_field($_GET['filter_booking_status']);
        $matched_ids = phive_get_order_ids_by_booking_status($target_status);

        $query->set('post__in', $matched_ids);
    }
}

// 5. Process the UI Filter for HPOS
add_filter('woocommerce_order_list_table_prepare_items_query_args', 'phive_filter_orders_by_booking_status_hpos', 10, 1);
function phive_filter_orders_by_booking_status_hpos($query_args)
{
    if (isset($_GET['filter_booking_status']) && $_GET['filter_booking_status'] !== '') {
        $target_status = sanitize_text_field($_GET['filter_booking_status']);
        $matched_ids = phive_get_order_ids_by_booking_status($target_status);

        $query_args['post__in'] = $matched_ids;
    }
    return $query_args;
}

add_filter('gettext', 'change_woocommerce_created_via_labels', 20, 3);
function change_woocommerce_created_via_labels($translated_text, $text, $domain)
{
    // Only apply in the WordPress admin area and specifically to WooCommerce strings
    if (is_admin() && 'woocommerce' === $domain) {
        switch ($text) {
            case 'All sales channels':
                $translated_text = 'Select Source'; // Replace with your preferred text
                break;
            case 'Checkout':
                $translated_text = 'Website'; // Replace with your preferred text
                break;
            case 'Date created:':
                $translated_text = 'Created on:'; // Replace with your preferred text
                break;
            case 'Direct':
                $translated_text = 'Website'; // Replace with your preferred text
                break;
            case 'Pending payment':
                $translated_text = 'Pending'; // Replace with your preferred text
                break;
            case 'Orders':
                $translated_text = 'Bookings'; // Replace with your preferred text
                break;
            case 'Edit order':
                $translated_text = 'Edit booking'; // Replace with your preferred text
                break;
            case 'Add order':
                $translated_text = 'Add new booking'; // Replace with your preferred text
                break;
            case 'Total orders':
                $translated_text = 'Total bookings'; // Replace with your preferred text
                break;
            case 'Average order value':
                $translated_text = 'Average booking value'; // Replace with your preferred text
                break;
            case 'Order':
                $translated_text = 'Booking'; // Replace with your preferred text
                break;
            case 'Order ID':
                $translated_text = 'Booking ID'; // Replace with your preferred text
                break;
            case 'Search orders':
                $translated_text = 'Search bookings'; // Replace with your preferred text
                break;
            case "This is the Customer Lifetime Value, or the total amount you have earned from this customer's orders.":
                $translated_text = "This is the Customer Lifetime Value, or the total amount you have earned from this customer's bookings."; // Replace with your preferred text
                break;
            case "Total number of non-cancelled, non-failed orders for this customer, including the current one.":
                $translated_text = "Total number of non-cancelled, non-failed bookings for this customer, including the current one."; // Replace with your preferred text
                break;
            case 'Change status to completed':
                $translated_text = 'Change status to confirmed'; // Replace with your preferred text
                break;
            case 'Change status to processing':
                $translated_text = 'Change status to in-progress'; // Replace with your preferred text
                break;
            case ' View other orders →':
                $translated_text = ' View other bookings →'; // Replace with your preferred text
                break;
        }
    }
    return $translated_text;
}
add_filter('gettext', function ($translated, $text, $domain) {

    if ($domain === 'woocommerce') {

        // Replace exact text
        if (trim($text) === 'View other orders →') {
            return 'View other bookings →';
        }

        // Safe fallback
        if (strpos($text, 'View other orders') !== false) {
            return str_replace('orders', 'bookings', $text);
        }
    }

    return $translated;

}, 20, 3);

add_action('woocommerce_admin_order_data_after_order_details', 'add_custom_button_in_order_edit');

function add_custom_button_in_order_edit($order)
{
    $order_parent = $order->get_meta('group_parent_order');
    if (!$order_parent) {
        return;
    }
    $order_edit_url = admin_url('admin.php?page=wc-orders&action=edit&id=' . $order_parent);
    ?>
    <style>
        h2.woocommerce-order-data__heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
    <div class="parent-link">
        <a href="<?php echo $order_edit_url; ?>" class="button button-primary">
            VIew Primary Booking
        </a>
    </div>
    <script>
        jQuery(function ($) {
            jQuery(document).ready(function ($) {
                if ($('.woocommerce-order-data__heading').length && $('.parent-link').length) {
                    $('.woocommerce-order-data__heading').append($('.parent-link'));
                }
            });
        });
    </script>
    <?php
}

add_action('admin_init', 'custom_silent_redirect_bookings_to_orders');
function custom_silent_redirect_bookings_to_orders()
{
    // Check if we are on the specific bookings page
    if (isset($_GET['page']) && $_GET['page'] === 'bookings') {
        // Redirect seamlessly to the native WooCommerce orders page
        wp_safe_redirect(admin_url('admin.php?page=wc-orders'));
        exit; // Always exit immediately after a redirect
    }
}

add_action('admin_menu', function () {
    global $submenu;

    if (isset($submenu['woocommerce'])) {
        foreach ($submenu['woocommerce'] as $key => $menu_item) {
            if ($menu_item[0] === 'Orders') {
                $submenu['woocommerce'][$key][0] = 'Bookings';
            }
        }
    }
}, 999);


add_filter('woocommerce_order_fully_refunded_status', 'prevent_auto_refunded_status', 10, 3);
function prevent_auto_refunded_status($refunded_status, $order_id, $refund_id)
{
    $order = wc_get_order($order_id);

    if ($order) {
        return $order->get_status();
    }

    return $refunded_status;
}

/*
// Logout customer/admin based on visiting pages
function restrict_admin_from_myaccount()
{

    if (!is_user_logged_in())
        return;

    $user = wp_get_current_user();

    // ✅ SERVICE MANAGER → redirect (NO logout)
    if (in_array('service_manager', $user->roles)) {

        if (function_exists('is_account_page') && is_account_page()) {

            wp_redirect(home_url('/kautilya/service-manager/'));
            exit;
        }
    }

    // ✅ ADMIN → logout + redirect
    if (in_array('administrator', $user->roles)) {

        if (function_exists('is_account_page') && is_account_page()) {

            wp_logout();
            wp_redirect(home_url('/login'));
            exit;
        }
    }
}
add_action('template_redirect', 'restrict_admin_from_myaccount');


function restrict_customer_from_admin()
{

    if (!is_user_logged_in())
        return;

    // Only target wp-admin area
    if (!is_admin())
        return;

    // Prevent AJAX / CRON / REST issues
    if (defined('DOING_AJAX') && DOING_AJAX)
        return;
    if (defined('DOING_CRON') && DOING_CRON)
        return;
    if (defined('REST_REQUEST') && REST_REQUEST)
        return;

    $user = wp_get_current_user();

    // CUSTOMER → block wp-admin
    if (in_array('customer', $user->roles)) {

        wp_logout();
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('init', 'restrict_customer_from_admin');
*/

// Get all saved carts
function get_saved_carts_for_user()
{
    if (!is_user_logged_in())
        return '<p>Please login to see saved carts.</p>';

    $user_id = get_current_user_id();
    $saved_carts = get_user_meta($user_id, '_saved_carts', true);
    if (empty($saved_carts) || !is_array($saved_carts))
        return '<p style="text-align:center;">No saved carts found. Please add items to your cart.</p>';

    // --- NEW: Custom Sorting Logic ---

    // 1. Define the custom comparison function
    $sort_by_booking_date = function ($a, $b) {

        // Helper function to extract the booking timestamp from a cart array
        $get_booking_timestamp = function ($cart_data) {
            $cart_items = isset($cart_data['items']) ? $cart_data['items'] : $cart_data;
            foreach ($cart_items as $cart_item) {
                // Find the main booking product ('phive_booking')
                $product = wc_get_product($cart_item['product_id']);
                if ($product && $product->get_type() === 'phive_booking') {
                    $booked_from = $cart_item['phive_display_time_from'] ?? null;
                    if ($booked_from) {
                        return strtotime($booked_from);
                    }
                }
            }
            // Return 0 if no booking date is found (treat as oldest/unsortable)
            return 0;
        };

        $time_a = $get_booking_timestamp($a);
        $time_b = $get_booking_timestamp($b);

        // Sort ascending (closest date first)
        if ($time_a == $time_b) {
            return 0;
        }
        return ($time_a < $time_b) ? -1 : 1;
    };

    // 2. Apply the custom sorting function
    // We remove the array_reverse from the original code and replace it with usort
    uasort($saved_carts, $sort_by_booking_date);

    // --- END: Custom Sorting Logic ---


    //$saved_carts = array_reverse($saved_carts, true);

    // Initialize output variables for separation
    $non_expired_output = '';
    $expired_output = '';
    $has_expired_carts = false;

    // Output for modals (placed outside the loop)
    $output = '<div class="saved-carts-wrapper">';
    $output .= '<div id="update-saved-cart" style="display:none;">
                    <div class="modal-content">
                        <p>Do you want to change the Room Booking details?</p>
                        <button id="booking-yes">Yes</button>
                        <button id="booking-no">No</button>
                    </div>
                </div>';
    $output .= '<div id="delete-saved-cart" style="display:none;">
                    <div class="modal-content">
                        <p>Do you want to delete this item from Cart?</p>
                        <button id="delete-yes" class="">Yes</button>
                        <button id="delete-no" class="">No</button>
                    </div>
                </div>';
    $output .= '<div id="slot-warning-popup" style="display:none;">
                    <div class="modal-content">
                        <p id="slot-warning-message"></p>
                        <button id="slot-warning-ok" class="">Yes</button>
                        <button id="slot-warning-cancel" class="">No</button>
                    </div>
                </div>';
    $output .= '<div id="delete-all-expired-popup" style="display:none;">
                    <div class="modal-content">
                        <p>Are you sure you want to delete all expired items?</p>
                        <button id="expired-delete-yes" class="">Yes</button>
                        <button id="expired-delete-no" class="">No</button>
                    </div>
                </div>';
    // echo "<pre>";
    // print_r($saved_carts);
    foreach ($saved_carts as $cart_name => $cart_data) {

        $main_product = null;
        $addons = [];
        $remarks = [];

        // Support both old and new cart structures
        if (isset($cart_data['items'])) {
            $cart_items = $cart_data['items'];
            $remarks = $cart_data['remarks'] ?? [];
        } else {
            $cart_items = $cart_data;
        }

        // Separate main product (phive_booking) and addons
        foreach ($cart_items as $cart_item) {
            $product = wc_get_product($cart_item['product_id']);
            if (!$product)
                continue;

            if ($product->get_type() === 'phive_booking') {
                $main_product = $cart_item;
            } else {
                $addons[] = $cart_item;
            }
        }

        if (!$main_product)
            continue; // skip if no phive_booking



        $booking_price = $main_product['phive_booked_price'] ?? '';
        $main_product_obj = wc_get_product($main_product['product_id']);
        $booked_from = $main_product['phive_display_time_from'] ?? '';
        $booked_to = $main_product['phive_display_time_to'] ?? '';
        $from = $main_product['phive_book_from_date'] ?? '';
        $to = $main_product['phive_book_to_date'] ?? '';
        $product_url = $main_product_obj->get_permalink();
        $date = date('Y-m-d', strtotime($from));
        $slot = date('H:i', strtotime($from)) . ',' . date('H:i', strtotime($to));
        $total_slot = $main_product['ph_selected_blocks'] ?? 1;
        $members = $main_product['phive_booked_persons'][0] ?? 0;
        $case_title = '';
        $case_id = '';

        $date2 = date('F j, Y', strtotime($booked_from));
        $time_from = date('g:i a', strtotime($booked_from));
        //echo $booked_from;
        $time_to = date('g:i a', strtotime($booked_to));
        $booked_datetime = $date2 . ' (' . $time_from . ' – ' . $time_to . ')';

        if (!empty($main_product['wapf']) && is_array($main_product['wapf'])) {
            foreach ($main_product['wapf'] as $field) {
                if (isset($field['label']) && isset($field['value_cart'])) {
                    if ($field['label'] === 'Case Title')
                        $case_title = $field['value_cart'];
                    if ($field['label'] === 'Case ID')
                        $case_id = $field['value_cart'];
                }
            }
        }

        // --- EXPIRATION CHECK ---
        $is_expired = false;
        if (!empty($booked_from)) {
            // Check if the booking date/time is in the past
            $booking_timestamp = strtotime($booked_from);
            $current_timestamp = current_time('timestamp'); // Use WordPress function for correct time zone

            $booking_date_string = date('Y-m-d', strtotime($booked_from));
            $cutoff_timestamp = strtotime($booking_date_string . ' 14:00:00');

            if ($booking_timestamp < $current_timestamp) {
                $is_expired = true;
                $has_expired_carts = true;
            }
        }
        $item_class = $is_expired ? 'expired' : 'not-expired';
        // Start building the HTML for the current cart
        $cart_html = '<div class="saved-cart-item ' . $item_class . '">';

        // Cart Name on top
        //$cart_html .= '<h5 class="boldbrown">' . esc_html($cart_name) . '</h5>';

        $main_product_name = esc_html($main_product_obj->get_name());
        if ($total_slot == '2') {
            $main_product_price = floatval($booking_price - ($booking_price * 0.1));
        } elseif ($total_slot == '3') {
            $main_product_price = floatval($booking_price - ($booking_price * 0.15));
        } else {
            $main_product_price = floatval($booking_price);
        }


        $cart_html .= '<div class="middle-area"><div class="left-area"><img src="">' . $main_product_obj->get_image('medium') . '</div>';
        $edit_link_html = $is_expired ?
            '' :
            '<a id="" class="edit-svg boldbrown update-booking" href="' . $product_url . '?date=' . $date . '&slot=' . $slot . '&members=' . $members . '&case_id=' . $case_id . '&case_title=' . $case_title . '&product_id=' . $main_product['product_id'] . '" data-cart-name="' . $cart_name . '" style="vertical-align: text-top;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg></a>';
        $cart_html .= '<div class="right-area"><div class="main-title">
                            <span>' . $main_product_name . '</span> ' . $edit_link_html . '
                        </div><div class="main-heading boldonly"><span>Booking Details</span><span>Amount (INR)</span></div><div class="down">';

        // Main product row
        // Conditional link for expired items (they shouldn't be editable)


        $cart_html .= '<div class="boldbrown upper productname">
                            <span>Room Fee</span>
                            <span>₹' . esc_html($main_product_price) . '</span>
                        </div>';

        $calendar_edit_html = $is_expired ?
            '' :
            ' <a id="" class="saved_cart_calander edit-svg boldbrown" href="' . $product_url . '?date=' . $date . '&slot=' . $slot . '&members=' . $members . '&case_id=' . $case_id . '&case_title=' . $case_title . '&product_id=' . $main_product['product_id'] . '" data-cart-name="' . $cart_name . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg></a>';

        $cart_html .= '<div class="date-item">' . $booked_datetime . $calendar_edit_html . '</div>';
        $cart_html .= '<hr>';

        $addon_plus_html = $is_expired ?
            '' :
            ' <a id="open-extra-popup" class="edit-svg open-addon-popup boldbrown" href="" data-cart-name="' . $cart_name . '">+</a>';

        $cart_html .= '<div class="boldbrown upper heading"><span>Add-ons</span> ' . $addon_plus_html . '</div>';

        // Add-ons
        $addons_total = 0;
        if (!empty($addons)) {
            $cart_html .= '<div class="addon-box">';
            foreach ($addons as $addon) {
                $addon_product = wc_get_product($addon['product_id']);
                if (!$addon_product)
                    continue;

                $qty = $addon['quantity'] ?? 1;
                $price = floatval($addon_product->get_price());
                $total = $qty * $price;
                $addons_total += $total;

                // Get brand terms
                $brand_image = '';
                $product_id = $addon_product->get_id();
                ;
                $brands = get_the_terms($product_id, 'product_brand');
                if (!empty($brands) && !is_wp_error($brands)) {
                    $brand = $brands[0];

                    // Get brand image (term meta)
                    $brand_image_id = get_term_meta($brand->term_id, 'thumbnail_id', true);

                    if ($brand_image_id) {
                        $brand_image = wp_get_attachment_image($brand_image_id, 'large', false, [
                            'style' => 'width:35px;height:auto;margin-right:8px;vertical-align:middle;border-radius: 2px;'
                        ]);
                    }
                }

                $cart_html .= '<div class="items">
                                    <span>' . $brand_image . esc_html($addon_product->get_name()) . ' × ' . $qty . '</span>
                                    <span>₹' . esc_html($total) . '</span>
                                </div>';
            }
            $cart_html .= '</div>';
        }

        // Remarks
        if (!empty($remarks)) {
            $has_value = false;
            foreach ($remarks as $key => $value) {
                if (!empty($value)) {
                    $has_value = true;
                    break;
                }
            }

            if ($has_value) {
                $cart_html .= '<div class="remark-box">';
                $cart_html .= '<hr>';
                $cart_html .= '<div class="boldonly sub-heading">Remarks</div>';
                foreach ($remarks as $key => $remark) {
                    if (!empty($remark)) {
                        $term = get_term_by('slug', $key, 'product_cat');
                        $cat_name = $term ? $term->name : $key;
                        $cart_html .= '<div class="items"><span>' . esc_html($cat_name) . ':</span> ' . esc_html($remark) . '</div>';
                    }
                }
                $cart_html .= '</div>';
            }
        }

        // Total Add-Ons Price
        if ($addons_total > 0) {
            $cart_html .= '<hr>';
            $cart_html .= '<div class="boldbrown upper heading h2">
                                <span>Total Add-Ons Price</span>
                                <span>₹' . esc_html($addons_total) . '</span>
                            </div>';
        }

        // Grand Total (always includes main product)
        $grand_total = $main_product_price + $addons_total;
        $cart_html .= '<div class="boldbrown upper heading h3">
                            <span>Total Amount</span>
                            <span>₹' . esc_html($grand_total) . '</span>
                        </div>';

        // $cart_html .= '<div class=" upper heading h2">
        //                     <span>CGST @9%</span>
        //                     <span>+₹' . esc_html($grand_total * .09) . '</span>
        //                 </div>';

        // $cart_html .= '<div class=" upper heading h2">
        //                     <span>SGST @9%</span>
        //                     <span>+₹' . esc_html($grand_total * .09) . '</span>
        //                 </div>';
        // $cart_html .= '<div class="boldbrown upper heading h2">
        //                     <span>Your Total Payable</span>
        //                     <span>₹' . esc_html($grand_total + ($grand_total * .18)) . '</span>
        //                 </div>';

        $cart_html .= '</div>';

        // Conditional buttons
        if ($is_expired) {
            $cart_html .= '<div class="group-btns">
                                
                                <a class="delete-cart elementor-button" data-cart-name="' . $cart_name . '">Remove</a>
                            </div>';
        } else {
            $cart_html .= '<div class="group-btns">
            <a class="delete-cart elementor-button" data-cart-name="' . $cart_name . '">Remove</a>
                                <a class="restore-cart elementor-button" data-name="' . $cart_name . '" data-id="' . $main_product['product_id'] . '" data-from="' . $from . '" data-to="' . $to . '" href="">Proceed To Checkout</a>
                                
                            </div>';
        }


        $cart_html .= '</div></div></div>';

        // Append to the appropriate output variable
        if ($is_expired) {
            $expired_output .= $cart_html;
        } else {
            $non_expired_output .= $cart_html;
        }
    }

    // --- FINAL OUTPUT ASSEMBLY ---

    // Non-expired carts
    $output .= $non_expired_output;

    // Expired carts section
    if ($has_expired_carts) {
        $output .= '';
        $output .= '<hr class="exp-divider">';
        // Add button to delete all expired carts
        // NOTE: This requires new AJAX or form submission logic on the front-end/backend to handle the bulk deletion.
        $output .= '<div class="head-btn">
                        <h3>Expired Items</h3>
                        <a id="delete-all-expired-carts" class="delete-cart-bulk elementor-button" style="color:#ffffff;">Clear All</a>
                    </div>';
        $output .= '<p class="exp-text">Items in this list will be deleted 15 days after the selected date.</p>';

        $output .= $expired_output;
    }

    $output .= '</div>';
    return $output;
}



add_action('wp_ajax_create_new_cart', 'create_new_cart');
add_action('wp_ajax_nopriv_create_new_cart', 'create_new_cart');

function create_new_cart()
{
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Login required.']);
    }

    $user_id = get_current_user_id();
    $cartform = $_POST['formdata'] ?? '';
    $addons = $_POST['addons'] ?? [];
    $remarks = $_POST['remarks'] ?? [];

    if (empty($cartform)) {
        wp_send_json_error(['message' => 'Cart form data missing.']);
    }

    parse_str($cartform, $form_data);

    // ---- Get Main Product Info ----
    $main_product_id = intval($form_data['add-to-cart'] ?? 0);
    if (!$main_product_id) {
        wp_send_json_error(['message' => 'Main product missing in form data.']);
    }

    $quantity = intval($form_data['quantity'] ?? 1);
    $booked_from = $form_data['phive_display_time_from'] ?? '';
    $booked_to = $form_data['phive_display_time_to'] ?? '';

    // echo "<pre>";
    // print_r($form_data);

    $product = wc_get_product($main_product_id);
    if (!$product) {
        wp_send_json_error(['message' => 'Invalid main product ID.']);
    }

    $product_name = $product->get_name();
    $date = date('F j, Y', strtotime($booked_from));
    $time_from = date('g:i a', strtotime($booked_from));
    $time_to = date('g:i a', strtotime($booked_to));

    $cart_name = $product_name . ' - ' . $date . ' (' . $time_from . ' – ' . $time_to . ')';

    // ---- Prepare main item ----
    $main_item = [
        'product_id' => $main_product_id,
        'quantity' => $quantity,
    ];

    // Include booking form fields as meta
    $booking_fields = [
        'phive_display_time_from',
        'phive_display_time_to',
        'phive_booked_price',
        'phive_book_from_date',
        'phive_book_to_date',
        'phive_book_assets',
        'ph_selected_blocks',
        'ph_booking_addon_data',
        'ph_booking_product_addon_data',
        'auto_select_min_block',
        'end_time_display',
        'calendar_design',
        'reset_action',
        'ph_search_product_view',
        'persons_as_booking',
        'phive_book_resources',
        'admin-fee',
        'wapf_field_groups'
    ];

    // Add all booking-related fields dynamically
    foreach ($form_data as $key => $value) {
        if (in_array($key, $booking_fields) || str_starts_with($key, 'wapf')) {
            $main_item[$key] = $value;
        }
    }

    // ---- Build structured WAPF ----
    $form_wapf = $form_data['wapf'] ?? [];
    $field_map = [
        '68afdc5063a8e' => ['label' => 'Case Title', 'type' => 'textarea'],
        '68c7cf78747bb' => ['label' => 'Case ID', 'type' => 'text'],
    ];

    $wapf_items = [];
    $phive_book_persons = $form_data['phive_book_persons'];
    foreach ($form_wapf as $field_key => $field_value) {
        $field_id = str_replace('field_', '', $field_key);
        $wapf_items[] = [
            'id' => $field_id,
            'type' => $field_map[$field_id]['type'] ?? 'text',
            'raw' => $field_value,
            'value' => $field_value,
            'value_cart' => $field_value,
            'price' => [],
            'label' => $field_map[$field_id]['label'] ?? $field_id,
        ];
    }
    if (!empty($wapf_items)) {
        $main_item['wapf'] = $wapf_items;
        $main_item['phive_booked_persons'] = $phive_book_persons;
    }


    // ---- Addon Items ----
    $addon_items = [];
    if (is_array($addons)) {
        foreach ($addons as $addon) {
            $product_id = intval($addon['id'] ?? 0);
            $qty = intval($addon['qty'] ?? 0);
            if ($product_id && $qty > 0) {
                $addon_items[] = [
                    'product_id' => $product_id,
                    'quantity' => $qty,
                ];
            }
        }
    }

    // ---- Remarks ----
    $filtered_remarks = array_filter($remarks, fn($v) => !empty(trim($v)));

    // ---- Combine ----
    $cart_data = [
        'items' => array_merge([$main_item], $addon_items),
        'remarks' => $filtered_remarks,
    ];

    // ---- Save to user meta ----
    $saved_carts = get_user_meta($user_id, '_saved_carts', true);
    if (!is_array($saved_carts))
        $saved_carts = [];

    // 1. Check if EXACT same item with same add-ons already exists
    foreach ($saved_carts as $existing_name => $existing_cart_data) {
        if (json_encode($existing_name) === json_encode($cart_name)) {
            wp_send_json_error(['message' => 'Item is already added in the Cart.']);
        }
    }

    // 2. Prevent overwriting items with the same slots but different attributes  
    if (isset($saved_carts[$cart_name])) {
        $cart_name = $cart_name . ' (' . uniqid() . ')';
    }

    $saved_carts[$cart_name] = $cart_data;

    update_user_meta($user_id, '_saved_carts', $saved_carts);

    // echo "<pre>";
    // print_r($saved_carts[$cart_name]);

    // ---- Final JSON Response ----
    wp_send_json_success([
        'message' => 'Cart saved successfully.',
        'cart_name' => $cart_name,
        'cart_data' => $cart_data,
    ]);
}

add_action('woocommerce_order_after_calculate_totals', function ($and_taxes, $order) {
    $payment_mode = $order->get_meta('group_payment_mode');
    if ($payment_mode !== 'group') {
        return;
    }

    // SPECIFIC CHANGE: Removed the check for child orders being created so the total splits correctly even if they haven't been generated yet

    $total_payers = (int) $order->get_meta('group_total_payers') ?: 2;

    // WooCommerce just calculated the FULL total (items minus new coupons)
    $full_total = $order->get_total();

    if ($total_payers > 0) {
        $new_share_amount = $full_total / $total_payers;

        // Re-adjust the parent order total back down to its split share
        $order->set_total($new_share_amount);
        $order->update_meta_data('group_original_total', $full_total); // Record the new full total for future reference

        // SPECIFIC CHANGE: Wrapped child order logic in this check so it won't crash if additional_payers hasn't been set yet
        $additional_payers = $order->get_meta('group_additional_payers');
        if (!empty($additional_payers) && is_array($additional_payers)) {
            // Update all existing child orders with the new discounted share
            foreach ($additional_payers as $payer) {
                if (!empty($payer['child_order_id'])) {
                    $child_order = wc_get_order($payer['child_order_id']);
                    if ($child_order) {
                        $child_order->set_total($new_share_amount);
                        $child_order->save();
                    }
                }
            }
        }
    }
}, 10, 2);




// =========================================================================
// ADMIN SPLIT PAYMENT UI & LOGIC (MATCHING UI MOCKUPS)
// =========================================================================

/**
 * Register the Meta Box on the Admin Order Edit Page
 */
add_action('add_meta_boxes', 'phive_admin_split_payment_meta_box', 10, 2);
function phive_admin_split_payment_meta_box($screen_id, $order)
{
    $allowed_screens = array('shop_order', 'woocommerce_page_wc-orders');

    if (in_array($screen_id, $allowed_screens, true)) {
        if ($order->get_meta('group_parent_order')) {
            return;
        }
        if ($order->get_meta('group_payment_mode') == 'group') {
            $label = "Payment Mode / Party Details";
        } else {
            $label = "Party Details";
        }
        add_meta_box(
            'phive_admin_payment_mode_box',
            'Payment Mode / Party Details',
            'phive_render_admin_split_payment_ui',
            $screen_id,
            'normal',
            'high'
        );
    }
}

/**
 * Render the HTML/JS exactly matching the provided image requirements
 */
function phive_render_admin_split_payment_ui($post_or_order)
{
    $order = ($post_or_order instanceof WC_Order) ? $post_or_order : wc_get_order($post_or_order->ID);
    if (!$order)
        return;

    $is_group = $order->get_meta('group_payment_mode') === 'group';
    wp_nonce_field('phive_admin_split_save', 'phive_admin_split_nonce');

    // =====================================================================
    // READ-ONLY SUMMARY & COMMUNICATION MODAL (IF ALREADY SPLIT)
    // =====================================================================
    if ($is_group || !$is_group) {
        $total_payers = $order->get_meta('group_total_payers');
        $additional_payers = $order->get_meta('group_additional_payers');
        $saved_emails = $order->get_meta('_phive_split_comm_emails') ?: array();
        $ind = 1;

        // Compile Party Data for the Tables & Popups
        $parties_data = array();
        $parties_data[] = array(
            'label' => 'Party ' . $ind,
            'name' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'company' => $order->get_billing_company(),
            'gstin' => $order->get_meta('_billing_gstin'),
            'phone' => $order->get_billing_phone(),
            'email' => !empty($saved_emails[0]) ? $saved_emails[0] : $order->get_billing_email(),
            'saved_email' => !empty($saved_emails[0]) ? $saved_emails[0] : $order->get_billing_email(),
            'address_1' => $order->get_billing_address_1(),
            'city' => $order->get_billing_city(),
            'state' => $order->get_billing_state(),
            'postcode' => $order->get_billing_postcode(),
            'country' => $order->get_billing_country(),
            'order_id' => $order->get_id(),
            'type' => 'parent',
            'idx' => -1
        );

        if (!empty($additional_payers) && is_array($additional_payers)) {
            foreach ($additional_payers as $index => $payer) {
                $ind++;
                $p_idx = $index + 1;

                $c_addr_1 = $payer['address_1'] ?? '';
                $c_city = $payer['city'] ?? '';
                $c_state = $payer['state'] ?? '';
                $c_zip = $payer['postcode'] ?? '';
                $c_cntry = $payer['country'] ?? '';
                $c_gstin = $payer['gstin'] ?? '';

                $name_parts = explode(' ', $payer['name'], 2);
                $c_fname = $payer['first_name'] ?? $name_parts[0];
                $c_lname = $payer['last_name'] ?? ($name_parts[1] ?? '');

                if (!empty($payer['child_order_id'])) {
                    $c_order = wc_get_order($payer['child_order_id']);
                    if ($c_order) {
                        $c_fname = $c_order->get_billing_first_name();
                        $c_lname = $c_order->get_billing_last_name();
                        $c_addr_1 = $c_order->get_billing_address_1();
                        $c_city = $c_order->get_billing_city();
                        $c_state = $c_order->get_billing_state();
                        $c_zip = $c_order->get_billing_postcode();
                        $c_cntry = $c_order->get_billing_country();
                        $c_gstin = $c_order->get_meta('_billing_gstin');
                    }
                }

                $parties_data[] = array(
                    'label' => 'Party ' . ($ind),
                    'name' => trim($c_fname . ' ' . $c_lname),
                    'first_name' => $c_fname,
                    'last_name' => $c_lname,
                    'company' => $payer['company'] ?? '',
                    'gstin' => $c_gstin,
                    'phone' => $payer['phone'],
                    'email' => $payer['email'],
                    'saved_email' => !empty($saved_emails[$p_idx]) ? $saved_emails[$p_idx] : $payer['email'],
                    'address_1' => $c_addr_1,
                    'city' => $c_city,
                    'state' => $c_state,
                    'postcode' => $c_zip,
                    'country' => $c_cntry,
                    'order_id' => !empty($payer['child_order_id']) ? $payer['child_order_id'] : '',
                    'type' => 'child',
                    'idx' => $index
                );
            }
        }
        if (!$is_group && $order->get_meta('_wc_order_attribution_source_type') === 'admin' && $order->get_meta('init_mail') != 1 && $order->get_status() === 'pending') {
            ?>
            <div id="phive_admin_split_wrapper">
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 5px; color: #1d2327;">Payment Mode</label>
                        <div style="display: flex; gap: 15px; align-items: center; height: 30px;">
                            <label style="cursor: pointer;"><input type="radio" name="admin_payment_mode" value="full" checked> Full
                                Payment</label>
                            <label style="cursor: pointer;"><input type="radio" name="admin_payment_mode" value="split"> Split
                                Payment</label>
                        </div>
                    </div>
                    <div id="admin_members_wrap" style="display: none;">
                        <label for="admin_split_members"
                            style="font-weight: 600; display: block; margin-bottom: 5px; color: #1d2327;">Parties involved</label>
                        <input type="number" name="admin_split_members" id="admin_split_members" min="2" max="15" value="2"
                            style="width: 100px;">
                    </div>
                </div>

                <div id="admin_members_container"></div>
            </div>
            <?php
        }
        // --- Split Party Details Table ---
        echo '<div>';
        // echo '<div style="display:flex; justify-content:space-between; margin-bottom: 15px;">';
        // echo '<h3 style="margin: 0; color:#1d2327;">Split Party Details</h3>';
        // echo '<span style="font-weight:600; color:#1d2327; margin-top:2px;">Total Parties: ' . esc_html($total_payers) . '</span>';
        // echo '</div>';

        echo '<table class="wp-list-table widefat striped p_list" style="border: 1px solid #e2e4e7; border-collapse: collapse; text-align: left;">';
        echo '<thead><tr style="background:#f8f9fa;">';
        if ($order->get_meta('group_payment_mode') == 'group') {
            echo '<th style="padding:10px; border-bottom:1px solid #e2e4e7; font-weight:600; width:10%;">Party</th>';
        }
        echo '<th style="padding:10px; border-bottom:1px solid #e2e4e7; font-weight:600; width:15%;">Party Name</th>';
        echo '<th style="padding:10px; border-bottom:1px solid #e2e4e7; font-weight:600; width:15%;">Company Name</th>';
        echo '<th style="padding:10px; border-bottom:1px solid #e2e4e7; font-weight:600; width:15%;">WhatsApp</th>';
        echo '<th style="padding:10px; border-bottom:1px solid #e2e4e7; font-weight:600; width:20%;">Email Id</th>';
        echo '<th style="padding:10px; border-bottom:1px solid #e2e4e7; font-weight:600; width:10%;">Action</th>';
        echo '<th style="padding:10px; border-bottom:1px solid #e2e4e7; font-weight:600; width:15%;">Last Email Communication</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($parties_data as $index => $pd) {
            echo '<tr>';
            if ($order->get_meta('group_payment_mode') == 'group') {
                echo '<td style="padding:10px;">' . esc_html($pd['label']) . '</td>';
            }

            echo '<td style="padding:10px;">' . esc_html($pd['name']) . '</td>';
            echo '<td style="padding:10px;">' . esc_html($pd['company'] ?: '-') . '</td>';
            echo '<td style="padding:10px;">' . esc_html($pd['phone']) . '</td>';
            echo '<td style="padding:10px;">' . esc_html($pd['email']) . '</td>';
            if ($order->has_status('cancelled')) {
                // Action Column (Edit Button mapping all detailed fields)
                echo '<td style="padding:10px;"><div class="btn-grp">';
                echo '<button type="button" class="button trigger-view-party" data-type="' . esc_attr($pd['type']) . '" data-idx="' . esc_attr($pd['idx']) . '" data-order-id="' . esc_attr($pd['order_id']) . '" data-fname="' . esc_attr($pd['first_name']) . '" data-lname="' . esc_attr($pd['last_name']) . '" data-email="' . esc_attr($pd['email']) . '" data-phone="' . esc_attr($pd['phone']) . '" data-company="' . esc_attr($pd['company']) . '" data-gstin="' . esc_attr($pd['gstin']) . '" data-label="' . esc_attr($pd['label']) . '" data-address1="' . esc_attr($pd['address_1']) . '" data-city="' . esc_attr($pd['city']) . '" data-state="' . esc_attr($pd['state']) . '" data-postcode="' . esc_attr($pd['postcode']) . '" data-country="' . esc_attr($pd['country']) . '"></button>';
                echo '</div></td>';
                echo '</div></td>';
            } else {
                // Action Column (Edit Button mapping all detailed fields)
                echo '<td style="padding:10px;"><div class="btn-grp">';
                echo '<button type="button" class="button trigger-edit-party" data-type="' . esc_attr($pd['type']) . '" data-idx="' . esc_attr($pd['idx']) . '" data-order-id="' . esc_attr($pd['order_id']) . '" data-fname="' . esc_attr($pd['first_name']) . '" data-lname="' . esc_attr($pd['last_name']) . '" data-email="' . esc_attr($pd['email']) . '" data-phone="' . esc_attr($pd['phone']) . '" data-company="' . esc_attr($pd['company']) . '" data-gstin="' . esc_attr($pd['gstin']) . '" data-label="' . esc_attr($pd['label']) . '" data-address1="' . esc_attr($pd['address_1']) . '" data-city="' . esc_attr($pd['city']) . '" data-state="' . esc_attr($pd['state']) . '" data-postcode="' . esc_attr($pd['postcode']) . '" data-country="' . esc_attr($pd['country']) . '"></button>';
                echo '<button type="button" class="button trigger-view-party" data-type="' . esc_attr($pd['type']) . '" data-idx="' . esc_attr($pd['idx']) . '" data-order-id="' . esc_attr($pd['order_id']) . '" data-fname="' . esc_attr($pd['first_name']) . '" data-lname="' . esc_attr($pd['last_name']) . '" data-email="' . esc_attr($pd['email']) . '" data-phone="' . esc_attr($pd['phone']) . '" data-company="' . esc_attr($pd['company']) . '" data-gstin="' . esc_attr($pd['gstin']) . '" data-label="' . esc_attr($pd['label']) . '" data-address1="' . esc_attr($pd['address_1']) . '" data-city="' . esc_attr($pd['city']) . '" data-state="' . esc_attr($pd['state']) . '" data-postcode="' . esc_attr($pd['postcode']) . '" data-country="' . esc_attr($pd['country']) . '"></button>';
                echo '</div></td>';
            }


            // Last Email Communication Column 
            $last_comm = '-';
            // Try to fetch the party-specific meta we just created
            $party_comm = $order->get_meta('_last_email_comm_party_' . $pd['idx']);

            if ($party_comm) {
                $last_comm = $party_comm;
            } else {
                // Fallback for older orders where party-specific meta doesn't exist yet
                if ($pd['order_id']) {
                    $child_order = wc_get_order($pd['order_id']);
                    if ($child_order && $child_order->get_meta('_last_email_communication')) {
                        $last_comm = $child_order->get_meta('_last_email_communication');
                    }
                } elseif ($pd['type'] === 'parent' && $order->get_meta('_last_email_communication')) {
                    $last_comm = $order->get_meta('_last_email_communication');
                }
            }

            echo '<td style="padding:10px;">' . esc_html($last_comm) . '</td>';

            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';

        // =====================================================================
        // COMMUNICATION & EDIT POPUPS HTML
        // =====================================================================
        $party_sent_actions = array();
        foreach ($order->get_meta_data() as $meta) {
            if (preg_match('/^_split_email_sent_(.+)_party_(\d+)$/', $meta->key, $matches)) {
                if ($meta->value === 'yes') {
                    $party_sent_actions[$matches[2]][] = $matches[1];
                }
            }
        }
        ?>
        <div id="phive-global-comm-modal"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999999; align-items:center; justify-content:center;">
            <div
                style="background:#fff; padding:25px; border-radius:4px; width:90%; max-width:75vw; max-height:85vh; overflow-y:auto; box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px; color:#1d2327;">Send <span
                        id="global-comm-modal-title"></span></h3>
                <button class="modal-close modal-close-link dashicons dashicons-no-alt"><span
                        class="screen-reader-text">Close</span></button>
                <input type="hidden" id="global-comm-action-key" value="">
                <table class="wp-list-table widefat striped"
                    style="margin-top:15px; margin-bottom:20px; border: 1px solid #e2e4e7;">
                    <thead>
                        <tr style="background:#f8f9fa;">
                            <th style="padding:10px; font-weight:600; width:10%;">Party</th>
                            <th style="padding:10px; font-weight:600; width:20%;">Name</th>
                            <th style="padding:10px; font-weight:600; width:25%;">Email Id</th>
                            <th style="padding:10px; font-weight:600; width:25%;">Mobile Number</th>
                            <th style="padding:10px; font-weight:600; width:15%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parties_data as $index => $pd): ?>
                            <?php $p_actions = isset($party_sent_actions[$index]) ? implode(',', $party_sent_actions[$index]) : ''; ?>
                            <tr class="global-comm-party-row" data-index="<?php echo esc_attr($index); ?>"
                                data-order-id="<?php echo esc_attr($pd['order_id']); ?>"
                                data-sent-actions="<?php echo esc_attr($p_actions); ?>">
                                <td style="padding:10px;"><?php echo esc_html($pd['label']); ?></td>
                                <td style="padding:10px;"><?php echo esc_html($pd['name']); ?></td>
                                <td style="padding:10px; position:relative;">
                                    <input type="text" class="global-comm-email-input"
                                        value="<?php echo esc_attr($pd['saved_email']); ?>"
                                        style="width:-webkit-fill-available; border: 1px solid #8c8f94; border-radius: 4px; padding: 5px 5px;"
                                        readonly>
                                </td>
                                <td style="padding:10px; position:relative;">
                                    <input type="tel" class="global-comm-phone-input" value="<?php echo esc_attr($pd['phone']); ?>"
                                        style="width:100%; border: 1px solid #8c8f94; border-radius: 4px; padding: 5px 5px;"
                                        readonly>
                                </td>
                                <td style="padding:10px;">
                                    <select name="comm_type" class="comm_type">
                                        <option value="all" selected>Email & WhatsApp</option>
                                        <option value="email">Email only</option>
                                        <option value="wa">Whatsapp only</option>
                                    </select>
                                    <button type="button" class="button button-secondary global-comm-send-single">Send</button>
                                    <span class="single-send-response"
                                        style="display:block; margin-top:5px; font-size:11px; font-weight:600;"></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php $o_actions = isset($party_sent_actions[$index + 1]) ? implode(',', $party_sent_actions[$index + 1]) : ''; ?>
                        <tr class="global-comm-party-row" data-index="<?php echo esc_attr($index + 1); ?>"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>"
                            data-sent-actions="<?php echo esc_attr($o_actions); ?>">
                            <td style="padding:10px;">Others</td>
                            <td style="padding:10px;"><textarea class="global-comm-name-input"
                                    placeholder="Names (comma seperated)" value=""
                                    style="width:-webkit-fill-available; border: 1px solid #8c8f94; border-radius: 4px; padding: 5px 5px;"></textarea>
                            </td>
                            <td style="padding:10px; position:relative;">
                                <textarea class="global-comm-email-input ignore" placeholder="Email IDs (comma seperated)"
                                    value=""
                                    style="width:-webkit-fill-available; border: 1px solid #8c8f94; border-radius: 4px; padding: 5px 5px;"></textarea>
                            </td>
                            <td style="padding:10px; position:relative;">
                                <textarea class="global-comm-phone-input ignore"
                                    placeholder="WhatsApp Numbers (comma seperated)" value=""
                                    style="width:100%; border: 1px solid #8c8f94; border-radius: 4px; padding: 5px 5px;"></textarea>
                            </td>
                            <td style="padding:10px;">
                                <div class="btn-grp">
                                    <select name="comm_type" class="comm_type">
                                        <option value="all" selected>Email & WhatsApp</option>
                                        <option value="email">Email only</option>
                                        <option value="wa">Whatsapp only</option>
                                    </select>
                                    <button type="button"
                                        class="button button-secondary global-comm-send-single other">Send</button>
                                </div>
                                <span class="single-send-response"
                                    style="display:block; margin-top:5px; font-size:11px; font-weight:600;"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="display:flex; justify-content:center; gap:15px;">
                    <button type="button" id="global-comm-send-all" class="button button-primary button-large"
                        style="min-width: 120px;">Send to All</button>
                    <button type="button" id="global-comm-cancel" class="button button-large"
                        style="min-width: 120px;">Cancel</button>
                </div>
                <div id="global-comm-response" style="margin-top:15px; font-weight:600; font-size:14px; text-align:center;">
                </div>
            </div>
        </div>

        <div id="phive-edit-party-modal"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999999; align-items:center; justify-content:center;">
            <div
                style="background:#fff; padding:25px; border-radius:4px; width:700px; max-height:90vh; overflow-y:auto; box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px; color:#1d2327;">Edit <span
                        id="edit-party-label"></span> Details</h3>
                <button class="modal-close modal-close-link dashicons dashicons-no-alt"><span class="screen-reader-text">Close
                        modal</span></button>
                <input type="hidden" id="edit-party-type">
                <input type="hidden" id="edit-party-idx">
                <input type="hidden" id="edit-party-order-id">

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">First Name <span
                                style="color:#d63638">*</span></label>
                        <input type="text" id="edit-party-fname" style="width: 100%;" data-required="true">
                    </div>
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Last Name <span
                                style="color:#d63638">*</span></label>
                        <input type="text" id="edit-party-lname" style="width: 100%;" data-required="true">
                    </div>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Phone Number <span
                                style="color:#d63638">*</span></label>
                        <input type="tel" id="edit-party-phone" maxlength="10" style="width: 100%;" data-required="true">
                    </div>
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Email Address <span
                                style="color:#d63638">*</span></label>
                        <input type="email" id="edit-party-email" style="width: 100%;" data-required="true">
                    </div>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Company Name</label>
                        <input type="text" id="edit-party-company" style="width: 100%;">
                    </div>
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">GSTIN</label>
                        <input type="text" id="edit-party-gstin" style="width: 100%;">
                    </div>
                </div>

                <?php
                $countries_obj = new WC_Countries();
                $countries = $countries_obj->get_countries();
                $states = $countries_obj->get_states('IN'); // Assuming IN is default
        
                $country_opts = '<option value="">Select a country / region…</option>';
                foreach ($countries as $c_code => $c_name) {
                    $country_opts .= '<option value="' . esc_attr($c_code) . '">' . esc_html($c_name) . '</option>';
                }

                $state_opts = '<option value="">Select an option…</option>';
                if (!empty($states)) {
                    foreach ($states as $s_code => $s_name) {
                        $state_opts .= '<option value="' . esc_attr($s_code) . '">' . esc_html($s_name) . '</option>';
                    }
                }
                ?>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Country / Region</label>
                        <select id="edit-party-country" style="width: 100%;">
                            <?php echo $country_opts; ?>
                        </select>
                    </div>
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">State / County</label>
                        <select id="edit-party-state" style="width: 100%;">
                            <?php echo $state_opts; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 15px; position: relative;">
                    <label style="font-weight:600; display:block; margin-bottom:5px;">Address</label>
                    <input type="text" id="edit-party-address1" placeholder="House number and street name" style="width: 100%;">
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Town / City</label>
                        <input type="text" id="edit-party-city" style="width: 100%;">
                    </div>
                    <div style="flex: 1; position: relative;">
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Postcode / ZIP</label>
                        <input type="text" id="edit-party-postcode" style="width: 100%;">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" id="edit-party-save" class="button button-primary">Save</button>
                    <button type="button" id="edit-party-cancel" class="button">Cancel</button>
                </div>
            </div>
        </div>

        <div id="phive-view-party-modal"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999999; align-items:center; justify-content:center;">

            <div
                style="background:#fff; padding:25px; border-radius:4px; width:700px; max-height:90vh; overflow-y:auto; box-shadow:0 4px 15px rgba(0,0,0,0.2);">

                <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px; color:#1d2327;">
                    <span id="view-party-label"></span> Details
                </h3>
                <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                    <span class="screen-reader-text">Close modal panel</span>
                </button>
                <table style="width:100%; border-collapse:collapse;">
                    <tbody>

                        <tr>
                            <td style="padding:10px; font-weight:600; width:35%; border-bottom:1px solid #eee;">First Name</td>
                            <td id="view-party-fname" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">Last Name</td>
                            <td id="view-party-lname" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">Phone Number</td>
                            <td id="view-party-phone" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">Email Address</td>
                            <td id="view-party-email" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">Company Name</td>
                            <td id="view-party-company" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">GSTIN</td>
                            <td id="view-party-gstin" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">Country / Region</td>
                            <td id="view-party-country" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">State / County</td>
                            <td id="view-party-state" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">Address</td>
                            <td id="view-party-address1" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600; border-bottom:1px solid #eee;">Town / City</td>
                            <td id="view-party-city" style="padding:10px; border-bottom:1px solid #eee;"></td>
                        </tr>

                        <tr>
                            <td style="padding:10px; font-weight:600;">Postcode / ZIP</td>
                            <td id="view-party-postcode" style="padding:10px;"></td>
                        </tr>

                    </tbody>
                </table>

                <div class="btn-grp" style="display:flex; justify-content:flex-end; margin-top:20px;">
                    <button type="button" id="view-party-edit" data-order-id="" class="button button-primary">Edit</button>
                    <button type="button" id="view-party-cancel" class="button">Close</button>
                </div>

            </div>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                const phive_order_id = <?php echo $order->get_id(); ?>;

                function validateEmailList(emailStr) {
                    if (!emailStr) return false;
                    let emails = emailStr.split(',');
                    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    for (let i = 0; i < emails.length; i++) {
                        if (!emailRegex.test(emails[i].trim())) {
                            return false;
                        }
                    }
                    return true;
                }
                function validatePhoneList(phoneStr) {
                    if (!phoneStr) return false;
                    let phones = phoneStr.split(',');
                    let phoneRegex = /^\+?[\d\s\-()]{7,20}$/;
                    for (let i = 0; i < phones.length; i++) {
                        if (!phoneRegex.test(phones[i].trim())) {
                            return false;
                        }
                    }
                    return true;
                }
                // --- GLOBAL COMMUNICATION MODAL LOGIC ---
                $(document).on('click', '.trigger-global-comm-modal', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    let rawLabel = $(this).attr('data-label');
                    let baseBtnText = rawLabel.replace('Resend ', 'Send ');

                    let actionKey = $(this).attr('data-action');
                    $('#global-comm-action-key').val(actionKey);
                    $('#global-comm-modal-title').text(rawLabel.replace('Send ', '').replace('Resend ', ''));

                    $('.global-comm-party-row').each(function () {
                        let rowActions = $(this).attr('data-sent-actions') || '';
                        let actionsArray = rowActions.split(',').filter(Boolean); // Filters out empty strings
                        let btnText = actionsArray.includes(actionKey) ? 'Resend' : 'Send';
                        $(this).find('.global-comm-send-single:not(.other)').text(btnText).css('width', '').attr('data-orig-text', btnText);
                    });
                    $('#global-comm-response').text('');
                    $('.single-send-response').text('');
                    $('.global-comm-email-input').css('border-color', '').siblings('.inline-error').remove();

                    $('#phive-global-comm-modal').css('display', 'flex');
                });

                $('#global-comm-cancel, #phive-global-comm-modal .modal-close-link').on('click', function (e) {
                    e.preventDefault();
                    $('#phive-global-comm-modal').hide();
                });

                $(document).on('blur', '.global-comm-email-input', function () {
                    let $input = $(this);
                    let val = $input.val().trim();
                    $input.val(val);

                    let isValid = true;
                    let errorMsg = '';

                    if (val === '') {
                        if (!$input.hasClass('ignore')) {
                            isValid = false;
                            errorMsg = 'Email ID is required.';
                        }
                    } else if (!validateEmailList(val)) {
                        isValid = false;
                        errorMsg = 'Invalid email format.';
                    }

                    let $errorSpan = $input.siblings('.inline-error');
                    if (!isValid) {
                        if ($errorSpan.length === 0) {
                            $input.after('<span class="inline-error" style="color: #d63638; font-size: 11px; display: block; margin-top: 5px;"></span>');
                            $errorSpan = $input.siblings('.inline-error');
                        }
                        $errorSpan.text(errorMsg).show();
                        $input.css('border-color', '#d63638');
                    } else {
                        if ($errorSpan.length > 0) $errorSpan.hide();
                        $input.css('border-color', '');
                    }
                });
                $(document).on('blur', '.global-comm-phone-input', function () {
                    let $input = $(this);
                    let val = $input.val().trim();
                    $input.val(val);

                    let isValid = true;
                    let errorMsg = '';

                    if (val === '') {
                        if (!$input.hasClass('ignore')) {
                            isValid = false;
                            errorMsg = 'WhatsApp number. is required.';
                        }
                    } else if (!validatePhoneList(val)) {
                        isValid = false;
                        errorMsg = 'Invalid WhatsApp number.';
                    }

                    let $errorSpan = $input.siblings('.inline-error');
                    if (!isValid) {
                        if ($errorSpan.length === 0) {
                            $input.after('<span class="inline-error" style="color: #d63638; font-size: 11px; display: block; margin-top: 5px;"></span>');
                            $errorSpan = $input.siblings('.inline-error');
                        }
                        $errorSpan.text(errorMsg).show();
                        $input.css('border-color', '#d63638');
                    } else {
                        if ($errorSpan.length > 0) $errorSpan.hide();
                        $input.css('border-color', '');
                    }
                });
                $(document).on('click', '.global-comm-send-single', async function (e) {
                    e.preventDefault();
                    let btn = $(this);
                    let row = btn.closest('.global-comm-party-row');
                    let type = row.find('.comm_type');
                    let idx = row.data('index');
                    let ordId = row.data('order-id');
                    let emailInput = row.find('.global-comm-email-input');
                    let phoneInput = row.find('.global-comm-phone-input');
                    let types = type.val();
                    let emails = emailInput.val().trim();
                    let phones = phoneInput.val().trim();
                    let actionKey = $('#global-comm-action-key').val();
                    let responseSpan = row.find('.single-send-response');
                    let origText = btn.attr('data-orig-text');

                    let emailErrorSpan = emailInput.siblings('.inline-error');
                    if (emailErrorSpan.length === 0) {
                        emailInput.after('<span class="inline-error" style="color: #d63638; font-size: 11px; display: block; margin-top: 4px;"></span>');
                        emailErrorSpan = emailInput.siblings('.inline-error');
                    }
                    let phoneErrorSpan = phoneInput.siblings('.inline-error');
                    if (phoneErrorSpan.length === 0) {
                        phoneInput.after('<span class="inline-error" style="color: #d63638; font-size: 11px; display: block; margin-top: 4px;"></span>');
                        phoneErrorSpan = phoneInput.siblings('.inline-error');
                    }

                    emailErrorSpan.text('');
                    phoneErrorSpan.text('');
                    responseSpan.text('').css('color', '');

                    let hasError = false;

                    if (types === 'all') {
                        if (!emails) {
                            emailErrorSpan.text('Email ID is required.');
                            hasError = true;
                        } else if (!validateEmailList(emails)) {
                            emailErrorSpan.text('Invalid email format.');
                            hasError = true;
                        }
                        if (!phones) {
                            phoneErrorSpan.text('WhatsApp number is required.');
                            hasError = true;
                        } else if (!validatePhoneList(phones)) {
                            phoneErrorSpan.text('Invalid WhatsApp number.');
                            hasError = true;
                        }
                    } else if (types === 'email') {
                        if (!emails) {
                            emailErrorSpan.text('Email ID is required.');
                            hasError = true;
                        } else if (!validateEmailList(emails)) {
                            emailErrorSpan.text('Invalid email format.');
                            hasError = true;
                        }
                    } else if (types === 'wa') {
                        if (!phones) {
                            phoneErrorSpan.text('WhatsApp number is required.');
                            hasError = true;
                        } else if (!validatePhoneList(phones)) {
                            phoneErrorSpan.text('Invalid WhatsApp number.');
                            hasError = true;
                        }
                    }

                    if (hasError) return false;

                    emailInput.trigger('blur');
                    if (emailInput.css('border-color') === 'rgb(214, 54, 56)' || emailInput.css('border-color') === '#d63638') {
                        return;
                    }

                    let currentWidth = btn.outerWidth();
                    btn.css({ width: currentWidth + 'px', textAlign: 'center' });
                    btn.prop('disabled', true).text('Sending...');
                    responseSpan.text('').css('color', '');

                    try {
                        await $.ajax({
                            url: ajaxurl, type: 'POST',
                            data: {
                                action: 'phive_save_split_comm_emails',
                                order_id: phive_order_id,
                                security: $('#phive_admin_split_nonce').val(),
                                emails: [{ index: idx, emails: emails }],
                                phones: [{ index: idx, phones: phones }],
                                types: [{ index: idx, types: types }],
                                sent_action: actionKey
                            }
                        });

                        if (ordId) {
                            await $.ajax({
                                url: ajaxurl, type: 'POST',
                                data: { action: actionKey, order_id: ordId, recipients: emails, phones: phones, types: types }
                            });

                            let existingActions = row.attr('data-sent-actions') ? row.attr('data-sent-actions').split(',').filter(Boolean) : [];
                            if (!existingActions.includes(actionKey)) {
                                existingActions.push(actionKey);
                                row.attr('data-sent-actions', existingActions.join(','));
                            }

                            origText = 'Resend';
                            btn.attr('data-orig-text', 'Send');

                            btn.text('Sent!');
                            //responseSpan.text('Success').css('color', '#00a32a');
                        } else {
                            btn.text(origText);
                            responseSpan.text('Order Pending').css('color', '#d63638');
                        }
                    } catch (error) {
                        btn.text('Failed');
                        responseSpan.text('Error').css('color', '#d63638');
                    }

                    setTimeout(() => {
                        if (btn.hasClass('other')) {
                            origText = 'Send';
                            phoneInput.val('');
                            emailInput.val('');
                            btn.prop('disabled', false).text(origText);
                            responseSpan.text('');
                        } else {
                            btn.prop('disabled', false).text(origText);
                            responseSpan.text('');
                        }
                    }, 3000);
                });

                $('#global-comm-send-all').on('click', async function (e) {
                    e.preventDefault();
                    let btn = $(this);

                    $('.global-comm-email-input:not(.ignore)').trigger('blur');
                    $('.global-comm-phone-input:not(.ignore)').trigger('blur');
                    let hasError = false;
                    $('.global-comm-email-input:not(.ignore)').each(function () {
                        if ($(this).css('border-color') === 'rgb(214, 54, 56)' || $(this).css('border-color') === '#d63638' || $(this).val().trim() === '') {
                            hasError = true;
                        }
                    });

                    if (hasError) {
                        $('#global-comm-response').text('Please fix errors before sending.').css('color', '#d63638');
                        return;
                    }

                    let currentWidth = btn.outerWidth();
                    btn.css({ width: currentWidth + 'px', textAlign: 'center' });
                    btn.prop('disabled', true).text('Sending...');
                    $('#global-comm-response').text('Sending, please wait...').css('color', '#2271b1');

                    let actionKey = $('#global-comm-action-key').val();
                    let partyEmails = [];
                    let partyPhones = [];
                    let partytypes = [];
                    let sendPromises = [];

                    $('.global-comm-party-row').each(function () {
                        let row = $(this);
                        let idx = row.data('index');
                        let ordId = row.data('order-id');
                        let types = row.find('.comm_type').val();
                        let emails = row.find('.global-comm-email-input').val().trim();
                        let phones = row.find('.global-comm-phone-input').val().trim();


                        partyEmails.push({ index: idx, emails: emails });
                        partyPhones.push({ index: idx, phones: phones });
                        partytypes.push({ index: idx, types: types })


                        if (ordId && emails) {
                            let request = $.ajax({
                                url: ajaxurl, type: 'POST',
                                data: { action: actionKey, order_id: ordId, recipients: emails, types: types, phones: phones }
                            });
                            sendPromises.push(request);
                        }

                        let existingActions = row.attr('data-sent-actions') ? row.attr('data-sent-actions').split(',').filter(Boolean) : [];
                        if (!existingActions.includes(actionKey)) {
                            existingActions.push(actionKey);
                            row.attr('data-sent-actions', existingActions.join(','));
                        }
                        // Update both the hidden attribute AND the visible text
                        row.find('.global-comm-send-single:not(.other)').attr('data-orig-text', 'Resend').text('Resend');
                    });

                    await $.ajax({
                        url: ajaxurl, type: 'POST',
                        data: {
                            action: 'phive_save_split_comm_emails',
                            order_id: phive_order_id,
                            security: $('#phive_admin_split_nonce').val(),
                            emails: partyEmails,
                            sent_action: actionKey,
                            types: partytypes,
                            phones: partyPhones
                        }
                    });

                    await Promise.allSettled(sendPromises);

                    $('#global-comm-response').text('Sent successfully!').css('color', '#00a32a');
                    btn.text('Done!');

                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });

                // --- EDIT PARTY MODAL LOGIC ---
                $('.trigger-edit-party').on('click', function (e) {
                    e.preventDefault();
                    let btn = $(this);

                    $('#edit-party-label').text(btn.data('label'));
                    $('#edit-party-type').val(btn.data('type'));
                    $('#edit-party-idx').val(btn.data('idx'));
                    $('#edit-party-order-id').val(btn.data('order-id'));

                    $('#edit-party-fname').val(btn.data('fname'));
                    $('#edit-party-lname').val(btn.data('lname'));
                    $('#edit-party-email').val(btn.data('email'));
                    $('#edit-party-phone').val(btn.data('phone'));
                    $('#edit-party-company').val(btn.data('company'));
                    $('#edit-party-gstin').val(btn.data('gstin'));
                    $('#edit-party-country').val(btn.data('country') || 'IN');
                    $('#edit-party-state').val(btn.data('state'));
                    $('#edit-party-address1').val(btn.data('address1'));
                    $('#edit-party-city').val(btn.data('city'));
                    $('#edit-party-postcode').val(btn.data('postcode'));

                    $('#phive-edit-party-modal .inline-error').hide();
                    $('#phive-edit-party-modal input, #phive-edit-party-modal select').css('border-color', '');

                    $('#phive-edit-party-modal').css('display', 'flex');
                });

                $('#edit-party-cancel, #phive-edit-party-modal .modal-close-link').on('click', function (e) {
                    e.preventDefault();
                    $('#phive-edit-party-modal').hide();
                });

                $('.trigger-view-party').on('click', function (e) {
                    e.preventDefault();

                    let btn = $(this);

                    $('#view-party-label').text(btn.data('label'));

                    $('#view-party-fname').text(btn.data('fname'));
                    $('#view-party-lname').text(btn.data('lname'));
                    $('#view-party-email').text(btn.data('email'));
                    $('#view-party-phone').text(btn.data('phone'));
                    $('#view-party-company').text(btn.data('company'));
                    $('#view-party-gstin').text(btn.data('gstin'));
                    const countryNames = { 'IN': 'India' };
                    const stateNames = { 'AN': 'Andaman and Nicobar Islands', 'AP': 'Andhra Pradesh', 'AR': 'Arunachal Pradesh', 'AS': 'Assam', 'BR': 'Bihar', 'CH': 'Chandigarh', 'CG': 'Chhattisgarh', 'DN': 'Dadra and Nagar Haveli and Daman and Diu', 'DL': 'Delhi', 'GA': 'Goa', 'GJ': 'Gujarat', 'HR': 'Haryana', 'HP': 'Himachal Pradesh', 'JK': 'Jammu and Kashmir', 'JH': 'Jharkhand', 'KA': 'Karnataka', 'KL': 'Kerala', 'LA': 'Ladakh', 'LD': 'Lakshadweep', 'MP': 'Madhya Pradesh', 'MH': 'Maharashtra', 'MN': 'Manipur', 'ML': 'Meghalaya', 'MZ': 'Mizoram', 'NL': 'Nagaland', 'OD': 'Odisha', 'PY': 'Puducherry', 'PB': 'Punjab', 'RJ': 'Rajasthan', 'SK': 'Sikkim', 'TN': 'Tamil Nadu', 'TG': 'Telangana', 'TR': 'Tripura', 'UP': 'Uttar Pradesh', 'UK': 'Uttarakhand', 'WB': 'West Bengal' };
                    $('#view-party-country').text(countryNames[btn.data('country')] || btn.data('country'));
                    $('#view-party-state').text(stateNames[btn.data('state')] || btn.data('state'));
                    $('#view-party-address1').text(btn.data('address1'));
                    $('#view-party-city').text(btn.data('city'));
                    $('#view-party-postcode').text(btn.data('postcode'));
                    $('#view-party-edit').attr('data-order-id', btn.data('order-id'));

                    $('#phive-view-party-modal').css('display', 'flex');
                });

                $('#view-party-cancel').on('click', function () {
                    $('#phive-view-party-modal').hide();
                });
                $('#phive-view-party-modal .modal-close-link').on('click', function (e) {
                    e.preventDefault();
                    $('#phive-view-party-modal').hide();
                });
                $('#view-party-edit').on('click', function () {
                    let btn = $(this);
                    let orderId = btn.data('order-id');
                    $('#phive-view-party-modal').hide();
                    $('.trigger-edit-party[data-order-id="' + orderId + '"]').trigger('click');
                });

                // Inline validation for edit modal
                $('#edit-party-phone').on('input', function () { $(this).val($(this).val().replace(/\D/g, '')); });
                $('#edit-party-fname, #edit-party-lname').on('input', function () { $(this).val($(this).val().replace(/[0-9]/g, '')); });

                $('#phive-edit-party-modal input, #phive-edit-party-modal select').on('blur', function () {
                    let $input = $(this);
                    let val = $input.val().trim();
                    if ($input.is('input')) { $input.val(val); }

                    let isValid = true;
                    let errorMsg = '';

                    if ($input.data('required') === true && val === '') {
                        isValid = false;
                        errorMsg = 'This field is required.';
                    } else if (val !== '') {
                        if (($input.attr('id') === 'edit-party-fname' || $input.attr('id') === 'edit-party-lname') && !/^[A-Za-z\s]+$/.test(val)) {
                            isValid = false;
                            errorMsg = 'Name should not have numbers, alphabets only.';
                        } else if ($input.attr('id') === 'edit-party-email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                            isValid = false;
                            errorMsg = 'Email format is incorrect.';
                        } else if ($input.attr('id') === 'edit-party-phone' && !/^\d{10}$/.test(val)) {
                            isValid = false;
                            errorMsg = 'Phone number should be exactly 10 digits.';
                        }
                    }

                    let $errorSpan = $input.siblings('.inline-error');
                    if (!isValid) {
                        if ($errorSpan.length === 0) {
                            $input.after('<span class="inline-error" style="color: #d63638; font-size: 12px; display: block; margin-top: 5px;"></span>');
                            $errorSpan = $input.siblings('.inline-error');
                        }
                        $errorSpan.text(errorMsg).show();
                        $input.css('border-color', '#d63638');
                    } else {
                        if ($errorSpan.length > 0) $errorSpan.hide();
                        $input.css('border-color', '');
                    }
                });

                $('#edit-party-save').on('click', function (e) {
                    e.preventDefault();
                    let btn = $(this);

                    $('#phive-edit-party-modal input, #phive-edit-party-modal select').trigger('blur');
                    let hasError = false;
                    $('#phive-edit-party-modal input[data-required="true"], #phive-edit-party-modal select[data-required="true"]').each(function () {
                        if ($(this).val() === '' || $(this).css('border-color') === 'rgb(214, 54, 56)' || $(this).css('border-color') === '#d63638') {
                            hasError = true;
                        }
                    });

                    if (hasError) return;

                    let currentWidth = btn.outerWidth();
                    //btn.css({ width: currentWidth + 'px', textAlign: 'center' });
                    btn.prop('disabled', true).text('Saving...');

                    let fname = $('#edit-party-fname').val().trim();
                    let lname = $('#edit-party-lname').val().trim();
                    let email = $('#edit-party-email').val().trim();
                    let phone = $('#edit-party-phone').val().trim();
                    let company = $('#edit-party-company').val().trim();
                    let gstin = $('#edit-party-gstin').val().trim();

                    let address1 = $('#edit-party-address1').val().trim();
                    let city = $('#edit-party-city').val().trim();
                    let state = $('#edit-party-state').val().trim();
                    let postcode = $('#edit-party-postcode').val().trim();
                    let country = $('#edit-party-country').val().trim();

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'phive_edit_split_party',
                            security: $('#phive_admin_split_nonce').val(),
                            parent_order_id: phive_order_id,
                            party_type: $('#edit-party-type').val(),
                            party_idx: $('#edit-party-idx').val(),
                            child_order_id: $('#edit-party-order-id').val(),
                            first_name: fname,
                            last_name: lname,
                            email: email,
                            phone: phone,
                            company: company,
                            gstin: gstin,
                            address_1: address1,
                            city: city,
                            state: state,
                            postcode: postcode,
                            country: country
                        },
                        success: function (res) {
                            if (res.success) {
                                btn.text('Saved!');
                                setTimeout(() => { location.reload(); }, 800);
                            } else {
                                alert(res.data.message || 'Error saving details.');
                                btn.prop('disabled', false).text('Save Details');
                                btn.css('width', '');
                            }
                        },
                        error: function () {
                            alert('Server error occurred.');
                            btn.prop('disabled', false).text('Save Details');
                            btn.css('width', '');
                        }
                    });
                });

                $('#phive-edit-party-modal input, #phive-edit-party-modal select').on('input change', function () {
                    let $err = $(this).siblings('.inline-error');
                    if ($err.length > 0) $err.hide();
                    $(this).css('border-color', '');
                });
            });
        </script>
        <?php
        //return; // End rendering for already grouped orders
    }

    // =====================================================================
    // CREATION UI (IF NOT YET SPLIT)
    // =====================================================================

    // Fetch Draft Data for Page Reloads (HPOS Compatible)
    $draft_data = $order->get_meta('_phive_draft_split_data');
    if (empty($draft_data) || !is_array($draft_data)) {
        $draft_data = array('mode' => 'full', 'count' => 2, 'parties' => array());
    }

    wp_nonce_field('phive_admin_split_save', 'phive_admin_split_nonce');
    if (!$is_group && $order->get_meta('_wc_order_attribution_source_type') === 'admin' && $order->get_meta('init_mail') != 1) {


        $countries_obj = new WC_Countries();
        $countries = $countries_obj->get_countries();
        $states = $countries_obj->get_states('IN'); // Assuming IN is default

        $country_opts = '<option value="">Select a country / region…</option>';
        foreach ($countries as $c_code => $c_name) {
            $country_opts .= '<option value="' . esc_attr($c_code) . '">' . esc_html($c_name) . '</option>';
        }

        $state_opts = '<option value="">Select State…</option>';
        if (!empty($states)) {
            foreach ($states as $s_code => $s_name) {
                $state_opts .= '<option value="' . esc_attr($s_code) . '">' . esc_html($s_name) . '</option>';
            }
        }
        ?>

        <script>
            jQuery(document).ready(function ($) {
                const phive_draft_data = <?php echo json_encode($draft_data); ?>;
                const phive_order_id = <?php echo $order->get_id(); ?>;
                const countryOptionsHtml = <?php echo json_encode($country_opts); ?>;
                const stateOptionsHtml = <?php echo json_encode($state_opts); ?>;
                let isInitializing = true;

                const membersWrap = $('#admin_members_wrap');
                const membersInput = $('#admin_split_members');
                const container = $('#admin_members_container');

                container.after(`
            <div id="phive_split_actions_wrap" style="display:none; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <button type="button" id="add_new_party_btn" class="button">+ Add New Party</button>
                <button type="button" id="save_payment_mode_btn" class="button button-primary">Save Payment Mode</button>
            </div>
        `);
                const splitActionsWrap = $('#phive_split_actions_wrap');
                const addPartyBtn = $('#add_new_party_btn');
                const savePaymentModeBtn = $('#save_payment_mode_btn');

                $('body').append(`
            <div id="phive-custom-confirm-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999999; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:25px; border-radius:4px; max-width:400px; text-align:center; box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                    <p id="phive-custom-confirm-msg" style="font-size:15px; margin-bottom:20px; color:#1d2327; font-weight:600;"></p>
                    <div style="display:flex; justify-content:center; gap:10px;">
                        <button type="button" id="phive-custom-confirm-yes" class="button button-primary" style="min-width:80px;">Yes</button>
                        <button type="button" id="phive-custom-confirm-no" class="button" style="min-width:80px;">No</button>
                    </div>
                </div>
            </div>
        `);

                function customConfirm(message) {
                    return new Promise((resolve) => {
                        $('#phive-custom-confirm-msg').text(message);
                        $('#phive-custom-confirm-modal').css('display', 'flex');

                        $('#phive-custom-confirm-yes').off('click').on('click', function () {
                            $('#phive-custom-confirm-modal').hide();
                            resolve(true);
                        });

                        $('#phive-custom-confirm-no').off('click').on('click', function () {
                            $('#phive-custom-confirm-modal').hide();
                            resolve(false);
                        });
                    });
                }

                function escAttr(str) {
                    if (!str) return '';
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                }

                function buildSelect(optionsHtml, selectedValue) {
                    if (!selectedValue) return optionsHtml;
                    return optionsHtml.replace('value="' + selectedValue + '"', 'value="' + selectedValue + '" selected="selected"');
                }

                function createMemberBox(index, data = {}) {
                    let fname = escAttr(data.first_name || (data.name ? data.name.split(' ')[0] : ''));
                    let lname = escAttr(data.last_name || (data.name ? data.name.split(' ').slice(1).join(' ') : ''));
                    let email = escAttr(data.email || '');
                    let phone = escAttr(data.phone || '');
                    let company = escAttr(data.company || '');
                    let gstin = escAttr(data.gstin || '');
                    let address1 = escAttr(data.address_1 || '');
                    let address2 = escAttr(data.address_2 || '');
                    let city = escAttr(data.city || '');
                    let state = escAttr(data.state || '');
                    let postcode = escAttr(data.postcode || '');
                    let country = escAttr(data.country || 'IN');

                    return `
            <div class="member-box" data-index="${index}" style="background: #f8f9fa; border: 1px solid #e2e4e7; padding: 20px; margin-bottom: 15px; border-radius: 4px; position: relative;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; color: #1d2327; display: flex; justify-content: space-between;">
                    <span class="member-title">Party ${index} Details</span>
                    <button type="button" class="remove-party-btn" style="color: #d63638; background: none; border: none; cursor: pointer; text-decoration: underline; font-size: 13px;">Remove Party</button>
                </h4>
                
                <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <input type="text" name="split_member_first_name[]" class="validate-name" placeholder="First Name*" value="${fname}" style="width: 100%;" data-required="true">
                    </div>
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <input type="text" name="split_member_last_name[]" class="validate-name" placeholder="Last Name*" value="${lname}" style="width: 100%;" data-required="true">
                    </div>
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <input type="tel" name="split_member_phone[]" class="validate-phone" placeholder="Phone Number*" value="${phone}" maxlength="10" style="width: 100%;" data-required="true">
                    </div>
                </div> 

                <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <input type="email" name="split_member_email[]" class="validate-email" placeholder="Email Address*" value="${email}" style="width: 100%;" data-required="true">
                    </div>
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <input type="text" name="split_member_company[]" placeholder="Company Name" value="${company}" style="width: 100%;">
                    </div>
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <input type="text" name="split_member_gstin[]" placeholder="GSTIN" value="${gstin}" style="width: 100%;">
                    </div>
                </div> 

                <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <select name="split_member_country[]" style="width: 100%;">
                            ${buildSelect(countryOptionsHtml, country)}
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <select name="split_member_state[]" style="width: 100%;">
                            ${buildSelect(stateOptionsHtml, state)}
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 15px; position: relative;">
                    <input type="text" name="split_member_address_1[]" placeholder="Address" value="${address1}" style="width: 100%;" >
                </div> 

                <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <input type="text" name="split_member_city[]" placeholder="Town / City" value="${city}" style="width: 100%;" >
                    </div>
                    <div style="flex: 1; min-width: 200px; position: relative;">
                        <input type="text" name="split_member_postcode[]" placeholder="Postcode / ZIP" value="${postcode}" style="width: 100%;">
                    </div>
                </div>
            </div>`;
                }

                function checkAllFieldsValid() {
                    let isValidAll = true;
                    if ($('input[name="admin_payment_mode"]:checked').val() !== 'split') return true;

                    let emails = [];
                    let phones = [];

                    let primaryEmail = $('#_billing_email').val();
                    if (primaryEmail) emails.push(primaryEmail.trim().toLowerCase());

                    let primaryPhone = $('#_billing_phone').val();
                    if (primaryPhone) phones.push(primaryPhone.trim());

                    container.find('input[data-required="true"], select[data-required="true"]').each(function () {
                        let $input = $(this);
                        let val = $input.val() ? $input.val().trim() : '';
                        let isValid = true;
                        let errorMsg = '';

                        if (val === '') {
                            isValid = false;
                            errorMsg = 'This field is required.';
                        } else if ($input.is('input')) {
                            if ($input.hasClass('validate-name') && !/^[A-Za-z\s]+$/.test(val)) {
                                isValid = false;
                                errorMsg = 'Name should not have numbers, alphabets only.';
                            } else if ($input.hasClass('validate-email')) {
                                let emailVal = val.toLowerCase();
                                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                                    isValid = false;
                                    errorMsg = 'Email format is incorrect.';
                                } else if (emails.includes(emailVal)) {
                                    isValid = false;
                                    errorMsg = 'Email must be unique across all parties.';
                                } else {
                                    emails.push(emailVal);
                                }
                            } else if ($input.hasClass('validate-phone')) {
                                if (!/^\d{10}$/.test(val)) {
                                    isValid = false;
                                    errorMsg = 'Phone number should be exactly 10 digits.';
                                } else if (phones.includes(val)) {
                                    isValid = false;
                                    errorMsg = 'Phone must be unique across all parties.';
                                } else {
                                    phones.push(val);
                                }
                            }
                        }

                        let $errorSpan = $input.siblings('.inline-error');
                        if (!isValid) {
                            isValidAll = false;
                            if ($errorSpan.length === 0) {
                                $input.after('<span class="inline-error" style="color: #d63638; font-size: 12px; display: block; margin-top: 5px;"></span>');
                                $errorSpan = $input.siblings('.inline-error');
                            }
                            $errorSpan.text(errorMsg).show();
                            $input.css('border-color', '#d63638');
                        } else {
                            if ($errorSpan.length > 0) $errorSpan.hide();
                            $input.css('border-color', '');
                        }
                    });

                    return isValidAll;
                }

                let draftTimer;
                function saveDraftData() {
                    if (isInitializing) return;
                    clearTimeout(draftTimer);

                    draftTimer = setTimeout(function () {
                        let mode = $('input[name="admin_payment_mode"]:checked').val();
                        let count = parseInt(membersInput.val()) || 2;
                        let parties = [];

                        if (mode === 'split') {
                            let allValidAndFilled = true;
                            container.find('input[data-required="true"], select[data-required="true"]').each(function () {
                                let val = $(this).val() ? $(this).val().trim() : '';
                                if (val === '') allValidAndFilled = false;
                                if ($(this).hasClass('validate-name') && !/^[A-Za-z\s]+$/.test(val)) allValidAndFilled = false;
                                if ($(this).hasClass('validate-email') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) allValidAndFilled = false;
                                if ($(this).hasClass('validate-phone') && !/^\d{10}$/.test(val)) allValidAndFilled = false;
                            });

                            if (!allValidAndFilled) {
                                //return; // Abort auto-save until everything is perfect
                            }

                            container.children('.member-box').each(function () {
                                parties.push({
                                    first_name: $(this).find('input[name="split_member_first_name[]"]').val() || '',
                                    last_name: $(this).find('input[name="split_member_last_name[]"]').val() || '',
                                    email: $(this).find('input[name="split_member_email[]"]').val() || '',
                                    phone: $(this).find('input[name="split_member_phone[]"]').val() || '',
                                    company: $(this).find('input[name="split_member_company[]"]').val() || '',
                                    gstin: $(this).find('input[name="split_member_gstin[]"]').val() || '',
                                    address_1: $(this).find('input[name="split_member_address_1[]"]').val() || '',
                                    address_2: $(this).find('input[name="split_member_address_2[]"]').val() || '',
                                    city: $(this).find('input[name="split_member_city[]"]').val() || '',
                                    state: $(this).find('select[name="split_member_state[]"]').val() || '',
                                    postcode: $(this).find('input[name="split_member_postcode[]"]').val() || '',
                                    country: $(this).find('select[name="split_member_country[]"]').val() || ''
                                });
                            });
                        }

                        $.post(ajaxurl, {
                            action: 'phive_save_admin_split_draft',
                            order_id: phive_order_id,
                            security: $('#phive_admin_split_nonce').val(),
                            mode: mode,
                            count: count,
                            parties: parties
                        });
                    }, 800);
                }

                function reindexBoxes() {
                    const boxes = container.children('.member-box');
                    membersInput.val(boxes.length + 1);
                    boxes.each(function (i) {
                        $(this).find('.member-title').text('Party ' + (i + 2) + ' Details');
                        $(this).attr('data-index', i + 2);
                    });
                    saveDraftData();
                }

                function initSplitMode() {
                    if (phive_draft_data && phive_draft_data.mode === 'split') {
                        $('input[name="admin_payment_mode"][value="split"]').prop('checked', true);
                        membersWrap.show();
                        splitActionsWrap.css('display', 'flex');
                        container.show();

                        let count = parseInt(phive_draft_data.count) || 2;
                        if (count < 2) { count = 2; }
                        membersInput.val(count);

                        container.empty();
                        for (let i = 2; i <= count; i++) {
                            let partyData = (phive_draft_data.parties && phive_draft_data.parties[i - 2]) ? phive_draft_data.parties[i - 2] : {};
                            container.append(createMemberBox(i, partyData));
                        }
                    } else {
                        $('input[name="admin_payment_mode"][value="full"]').prop('checked', true);
                        membersWrap.hide();
                        splitActionsWrap.hide();
                        container.hide();
                    }

                    setTimeout(() => {
                        isInitializing = false;
                    }, 100);
                }

                $('input[name="admin_payment_mode"]').on('change', async function (e) {
                    if (isInitializing) return;
                    let newMode = $(this).val();

                    if (newMode === 'full') {
                        let hasData = false;
                        container.find('input').each(function () {
                            if ($(this).val().trim() !== '') hasData = true;
                        });

                        if (hasData) {
                            let userConfirmed = await customConfirm('Are you sure you want to change the payment type? All entered party details will be erased.');
                            if (!userConfirmed) {
                                $('input[name="admin_payment_mode"][value="split"]').prop('checked', true);
                                return;
                            } else {
                                container.empty();
                                membersInput.val(2);
                            }
                        } else {
                            container.empty();
                        }
                        membersWrap.hide();
                        splitActionsWrap.hide();
                        container.hide();
                    } else {
                        membersWrap.show();
                        splitActionsWrap.css('display', 'flex');
                        container.show();
                        let count = parseInt(membersInput.val()) || 2;
                        if (container.children('.member-box').length === 0) {
                            for (let i = 2; i <= count; i++) {
                                container.append(createMemberBox(i));
                            }
                        }
                    }
                    saveDraftData();
                });

                membersInput.on('input', function () {
                    $(this).val($(this).val().replace(/\D/g, ''));
                });

                let previousCount = parseInt(membersInput.val()) || 2;
                membersInput.on('change', async function () {
                    if (isInitializing) return;
                    let newCount = parseInt($(this).val());

                    if (isNaN(newCount) || newCount < 2) { newCount = 2; $(this).val(2); }
                    if (newCount > 15) { newCount = 15; $(this).val(15); }

                    let currentBoxes = container.children('.member-box').length;
                    let targetBoxes = newCount - 1;

                    if (targetBoxes > currentBoxes) {
                        for (let i = currentBoxes + 1; i <= targetBoxes; i++) {
                            container.append(createMemberBox(i + 1));
                        }
                    } else if (targetBoxes < currentBoxes) {
                        let userConfirmed = await customConfirm('Do you want to delete the last entry?');
                        if (userConfirmed) {
                            let diff = currentBoxes - targetBoxes;
                            for (let i = 0; i < diff; i++) {
                                container.children('.member-box').last().remove();
                            }
                        } else {
                            $(this).val(previousCount);
                            return;
                        }
                    }
                    previousCount = parseInt($(this).val());
                    saveDraftData();
                });

                addPartyBtn.on('click', function () {
                    let currentBoxes = container.children('.member-box').length;
                    if (currentBoxes >= 14) {
                        alert('Maximum 15 parties allowed.');
                        return;
                    }
                    container.append(createMemberBox(currentBoxes + 2));
                    reindexBoxes();
                    previousCount = parseInt(membersInput.val());
                });

                $('form#post, form.wc-hpos-form').on('submit', function (e) {
                    if ($('input[name="admin_payment_mode"]:checked').val() === 'split' && !checkAllFieldsValid()) {
                        e.preventDefault();
                        return false;
                    }
                });

                savePaymentModeBtn.on('click', async function (e) {
                    e.preventDefault();

                    if (!checkAllFieldsValid()) {
                        alert('Please fill out all required Party Details correctly before saving.');
                        return;
                    }

                    let userConfirmed = await customConfirm('Do you want to save the payment mode and generate child orders?');
                    if (!userConfirmed) {
                        return;
                    }

                    saveDraftData();
                    let currentWidth = $(this).outerWidth();
                    $('form#post').attr('novalidate', 'novalidate');
                    $(this).css({ width: currentWidth + 'px', textAlign: 'center' });
                    $(this).text('Saving...').css('pointer-events', 'none').css('opacity', '0.7');
                    setTimeout(() => {
                        $(this).closest('form').submit();
                    }, 500);
                });

                container.on('click', '.remove-party-btn', async function () {
                    let currentBoxes = container.children('.member-box').length;
                    if (currentBoxes <= 1) {
                        alert('At least one additional party is required for Split Payment.');
                        return;
                    }

                    let $box = $(this).closest('.member-box');

                    let userConfirmed = await customConfirm('Do you want to remove this party?');
                    if (!userConfirmed) {
                        return;
                    }

                    $box.remove();
                    reindexBoxes();
                    previousCount = parseInt(membersInput.val());
                });

                container.on('blur', 'input, select', function () {
                    let $input = $(this);
                    let val = $input.val() ? $input.val().trim() : '';

                    if ($input.is('input')) {
                        $input.val(val);
                    }

                    let isValid = true;
                    let errorMsg = '';

                    if ($input.data('required') === true && val === '') {
                        isValid = false;
                        errorMsg = 'This field is required.';
                    } else if (val !== '') {
                        if ($input.hasClass('validate-name')) {
                            if (!/^[A-Za-z\s]+$/.test(val)) {
                                isValid = false;
                                errorMsg = 'Name should not have numbers, alphabets only.';
                            }
                        } else if ($input.hasClass('validate-email')) {
                            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                                isValid = false;
                                errorMsg = 'Email format is incorrect.';
                            } else {
                                let count = 0;
                                let emailVal = val.toLowerCase();
                                container.find('.validate-email').each(function () {
                                    if ($(this).val().trim().toLowerCase() === emailVal) count++;
                                });
                                let primaryEmail = $('#_billing_email').val();
                                if (primaryEmail && primaryEmail.trim().toLowerCase() === emailVal) count++;

                                if (count > 1) {
                                    isValid = false;
                                    errorMsg = 'Email must be unique across all parties.';
                                }
                            }
                        } else if ($input.hasClass('validate-phone')) {
                            if (!/^\d{10}$/.test(val)) {
                                isValid = false;
                                errorMsg = 'Phone number should be exactly 10 digits.';
                            } else {
                                let count = 0;
                                container.find('.validate-phone').each(function () {
                                    if ($(this).val().trim() === val) count++;
                                });
                                let primaryPhone = $('#_billing_phone').val();
                                if (primaryPhone && primaryPhone.trim() === val) count++;

                                if (count > 1) {
                                    isValid = false;
                                    errorMsg = 'Phone must be unique across all parties.';
                                }
                            }
                        }
                    }

                    let $errorSpan = $input.siblings('.inline-error');
                    if (!isValid) {
                        if ($errorSpan.length === 0) {
                            $input.after('<span class="inline-error" style="color: #d63638; font-size: 12px; display: block; margin-top: 5px;"></span>');
                            $errorSpan = $input.siblings('.inline-error');
                        }
                        $errorSpan.text(errorMsg).show();
                        $input.css('border-color', '#d63638');
                    } else {
                        if ($errorSpan.length > 0) {
                            $errorSpan.hide();
                        }
                        $input.css('border-color', '');
                    }

                    saveDraftData();
                });

                container.on('input change', 'input, select', function () {
                    let $input = $(this);

                    if ($input.hasClass('validate-phone')) {
                        $input.val($input.val().replace(/\D/g, ''));
                    }
                    if ($input.hasClass('validate-name')) {
                        $input.val($input.val().replace(/[0-9]/g, ''));
                    }

                    let $errorSpan = $input.siblings('.inline-error');
                    if ($errorSpan.length > 0) {
                        $errorSpan.hide();
                    }
                    $input.css('border-color', '');
                });

                initSplitMode();
            });
        </script>
        <?php
    }
}

/**
 * Handle AJAX Request to Save Draft Data (HPOS Compatible)
 */
add_action('wp_ajax_phive_save_admin_split_draft', 'phive_save_admin_split_draft_callback');
function phive_save_admin_split_draft_callback()
{
    check_ajax_referer('phive_admin_split_save', 'security');

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    if (!$order_id)
        wp_send_json_error();

    $order = wc_get_order($order_id);
    if (!$order)
        wp_send_json_error();

    $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'full';
    $count = isset($_POST['count']) ? intval($_POST['count']) : 2;
    $parties = isset($_POST['parties']) && is_array($_POST['parties']) ? wp_unslash($_POST['parties']) : array();

    $sanitized_parties = array();
    foreach ($parties as $party) {
        $sanitized_parties[] = array(
            'first_name' => sanitize_text_field($party['first_name'] ?? ''),
            'last_name' => sanitize_text_field($party['last_name'] ?? ''),
            'email' => sanitize_email($party['email'] ?? ''),
            'phone' => sanitize_text_field($party['phone'] ?? ''),
            'company' => sanitize_text_field($party['company'] ?? ''),
            'gstin' => sanitize_text_field($party['gstin'] ?? ''),
            'address_1' => sanitize_text_field($party['address_1'] ?? ''),
            'address_2' => sanitize_text_field($party['address_2'] ?? ''),
            'city' => sanitize_text_field($party['city'] ?? ''),
            'state' => sanitize_text_field($party['state'] ?? ''),
            'postcode' => sanitize_text_field($party['postcode'] ?? ''),
            'country' => sanitize_text_field($party['country'] ?? '')
        );
    }

    $draft_data = array(
        'mode' => $mode,
        'count' => $count,
        'parties' => $sanitized_parties
    );

    $order->update_meta_data('_phive_draft_split_data', $draft_data);
    $order->save();

    wp_send_json_success();
}

/**
 * Handle AJAX Request to Save the Updated Comma Separated Emails
 */
add_action('wp_ajax_phive_save_split_comm_emails', 'phive_save_split_comm_emails_callback');
function phive_save_split_comm_emails_callback()
{
    check_ajax_referer('phive_admin_split_save', 'security');

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $emails_data = isset($_POST['emails']) && is_array($_POST['emails']) ? wp_unslash($_POST['emails']) : array();
    $sent_action = isset($_POST['sent_action']) ? sanitize_text_field($_POST['sent_action']) : '';

    $order = wc_get_order($order_id);
    if (!$order)
        wp_send_json_error();

    $saved_emails = array();
    foreach ($emails_data as $data) {
        $idx = intval($data['index']);
        $email_string = sanitize_text_field($data['emails']);
        $saved_emails[$idx] = $email_string;
    }

    $order->update_meta_data('_phive_split_comm_emails', $saved_emails);

    if (!empty($sent_action)) {
        $global_actions = array(
            'send_manual_email' => 'Initiation Email',
            'send_manual_invoice_pdf_email' => 'Invoice & Email',
            'send_manual_confirmation' => 'Confirmation Only Email',
            'send_receipt' => 'Receipt Post Confirmation',
            'send_manual_booking_confirmation' => 'Confirmation & Receipt'
        );
        $action_label = $global_actions[$sent_action] ?? 'Unknown Email';

        foreach ($emails_data as $data) {
            $idx = intval($data['index']);
            $order->update_meta_data('_split_email_sent_' . $sent_action . '_party_' . $idx, 'yes');
            // Store the communication label specifically for this party index
            $order->update_meta_data('_last_email_comm_party_' . $idx, $action_label);
        }

        // Keep global as a fallback
        $order->update_meta_data('_last_email_communication', $action_label);
    }

    $order->save();
    wp_send_json_success();
}

/**
 * Handle AJAX Edit Party Save
 */
add_action('wp_ajax_phive_edit_split_party', 'phive_edit_split_party_callback');
function phive_edit_split_party_callback()
{
    check_ajax_referer('phive_admin_split_save', 'security');

    $parent_order_id = intval($_POST['parent_order_id']);
    $child_order_id = intval($_POST['child_order_id']);
    $type = sanitize_text_field($_POST['party_type']);
    $idx = intval($_POST['party_idx']);

    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $company = sanitize_text_field($_POST['company']);
    $gstin = sanitize_text_field($_POST['gstin']);
    $address_1 = sanitize_text_field($_POST['address_1']);
    $city = sanitize_text_field($_POST['city']);
    $state = sanitize_text_field($_POST['state']);
    $postcode = sanitize_text_field($_POST['postcode']);
    $country = sanitize_text_field($_POST['country']);

    $name = trim($first_name . ' ' . $last_name);

    $parent_order = wc_get_order($parent_order_id);
    if (!$parent_order)
        wp_send_json_error(['message' => 'Parent order not found']);

    if ($type === 'parent') {
        $parent_order->set_billing_first_name($first_name);
        $parent_order->set_billing_last_name($last_name);
        $parent_order->set_billing_email($email);
        $parent_order->set_billing_phone($phone);
        $parent_order->set_billing_company($company);

        $parent_order->set_billing_address_1($address_1);
        $parent_order->set_billing_city($city);
        $parent_order->set_billing_state($state);
        $parent_order->set_billing_postcode($postcode);
        $parent_order->set_billing_country($country);

        $parent_order->update_meta_data('_billing_gstin', $gstin);

        $parent_order->save();
    } else {
        $additional_payers = $parent_order->get_meta('group_additional_payers');
        if (isset($additional_payers[$idx])) {
            $additional_payers[$idx]['name'] = $name;
            $additional_payers[$idx]['first_name'] = $first_name;
            $additional_payers[$idx]['last_name'] = $last_name;
            $additional_payers[$idx]['email'] = $email;
            $additional_payers[$idx]['phone'] = $phone;
            $additional_payers[$idx]['company'] = $company;
            $additional_payers[$idx]['gstin'] = $gstin;
            $additional_payers[$idx]['address_1'] = $address_1;
            $additional_payers[$idx]['city'] = $city;
            $additional_payers[$idx]['state'] = $state;
            $additional_payers[$idx]['postcode'] = $postcode;
            $additional_payers[$idx]['country'] = $country;

            $parent_order->update_meta_data('group_additional_payers', $additional_payers);
            $parent_order->save();
        }

        $child_order = wc_get_order($child_order_id);
        if ($child_order) {
            $child_order->set_billing_first_name($first_name);
            $child_order->set_billing_last_name($last_name);
            $child_order->set_billing_email($email);
            $child_order->set_billing_phone($phone);
            $child_order->set_billing_company($company);

            $child_order->set_billing_address_1($address_1);
            $child_order->set_billing_city($city);
            $child_order->set_billing_state($state);
            $child_order->set_billing_postcode($postcode);
            $child_order->set_billing_country($country);

            $child_order->update_meta_data('_billing_gstin', $gstin);

            $child_order->update_meta_data('group_payer_name', $name);
            $child_order->update_meta_data('group_payer_email', $email);
            $child_order->update_meta_data('group_payer_phone', $phone);
            $child_order->update_meta_data('group_payer_company', $company);
            $child_order->save();
        }
    }
    wp_send_json_success();
}

/**
 * Process the Data and Generate Child Orders on Save
 */
add_action('woocommerce_process_shop_order_meta', 'phive_process_admin_split_save', 20, 2);
function phive_process_admin_split_save($order_id, $post)
{
    if (!isset($_POST['phive_admin_split_nonce']) || !wp_verify_nonce($_POST['phive_admin_split_nonce'], 'phive_admin_split_save')) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order)
        return;

    if (!isset($_POST['admin_payment_mode']) || $_POST['admin_payment_mode'] !== 'split') {
        $order->delete_meta_data('_phive_draft_split_data');
        $order->save();
        return;
    }

    if ($order->get_meta('group_payment_mode') === 'group') {
        return;
    }

    $total_payers = isset($_POST['admin_split_members']) ? intval($_POST['admin_split_members']) : 2;
    $first_names = isset($_POST['split_member_first_name']) ? $_POST['split_member_first_name'] : array();
    $last_names = isset($_POST['split_member_last_name']) ? $_POST['split_member_last_name'] : array();
    $emails = isset($_POST['split_member_email']) ? $_POST['split_member_email'] : array();
    $phones = isset($_POST['split_member_phone']) ? $_POST['split_member_phone'] : array();
    $companies = isset($_POST['split_member_company']) ? $_POST['split_member_company'] : array();
    $gstins = isset($_POST['split_member_gstin']) ? $_POST['split_member_gstin'] : array();
    $addrs1 = isset($_POST['split_member_address_1']) ? $_POST['split_member_address_1'] : array();
    $addrs2 = isset($_POST['split_member_address_2']) ? $_POST['split_member_address_2'] : array();
    $cities = isset($_POST['split_member_city']) ? $_POST['split_member_city'] : array();
    $states = isset($_POST['split_member_state']) ? $_POST['split_member_state'] : array();
    $postcodes = isset($_POST['split_member_postcode']) ? $_POST['split_member_postcode'] : array();
    $countries = isset($_POST['split_member_country']) ? $_POST['split_member_country'] : array();

    $additional_payers = array();

    foreach ($first_names as $index => $fname) {
        if (!empty($fname)) {
            $lname = $last_names[$index] ?? '';
            $additional_payers[] = array(
                'name' => sanitize_text_field(trim($fname . ' ' . $lname)),
                'first_name' => sanitize_text_field($fname),
                'last_name' => sanitize_text_field($lname),
                'email' => sanitize_email($emails[$index] ?? ''),
                'phone' => sanitize_text_field($phones[$index] ?? ''),
                'company' => sanitize_text_field($companies[$index] ?? ''),
                'gstin' => sanitize_text_field($gstins[$index] ?? ''),
                'address_1' => sanitize_text_field($addrs1[$index] ?? ''),
                'address_2' => sanitize_text_field($addrs2[$index] ?? ''),
                'city' => sanitize_text_field($cities[$index] ?? ''),
                'state' => sanitize_text_field($states[$index] ?? ''),
                'postcode' => sanitize_text_field($postcodes[$index] ?? ''),
                'country' => sanitize_text_field($countries[$index] ?? '')
            );
        }
    }

    if (!empty($additional_payers)) {
        $full_total = $order->get_total();
        $share_amount = $full_total / $total_payers;

        $order->update_meta_data('group_payment_mode', 'group');
        $order->update_meta_data('group_total_payers', $total_payers);
        $order->update_meta_data('group_original_total', $full_total);
        $order->set_total($share_amount);

        $order->delete_meta_data('_phive_draft_split_data');
        $order->save();

        $updated_payers = $additional_payers;
        $gen_child = array();

        foreach ($updated_payers as $key => $payer) {
            $child_order = wc_create_order();
            if (is_wp_error($child_order))
                continue;

            foreach ($order->get_items() as $item) {
                $child_order->add_product($item->get_product(), $item->get_quantity());
            }

            $current_month = (int) date('n');
            $current_year = (int) date('Y');

            if ($current_month >= 4) {
                $year_part = $current_year . '-' . date('y', strtotime('+1 year'));
            } else {
                $year_part = ($current_year - 1) . '-' . date('y');
            }

            $last_sequence = (int) get_option('apl_sequence_counter', 0);
            $new_sequence = $last_sequence + 1;
            $padded_sequence = str_pad($new_sequence, 4, '0', STR_PAD_LEFT);

            $unique_apl_id = 'APL/' . $year_part . '/' . $padded_sequence;

            $found_user = false;
            $user_by_email = get_user_by('email', $payer['email']);

            if ($user_by_email) {
                $found_user = $user_by_email;
            } else {
                $users_by_phone = get_users(array(
                    'meta_key' => 'billing_phone',
                    'meta_value' => $payer['phone'],
                    'number' => 1,
                    'fields' => 'all'
                ));
                if (!empty($users_by_phone)) {
                    $found_user = $users_by_phone[0];
                }
            }

            if ($found_user) {
                $customer_id = $found_user->ID;
                $child_order->set_customer_id($customer_id);

                $b_first_name = get_user_meta($customer_id, 'billing_first_name', true);
                $b_last_name = get_user_meta($customer_id, 'billing_last_name', true);
                $b_email = get_user_meta($customer_id, 'billing_email', true) ?: $found_user->user_email;
                $b_phone = get_user_meta($customer_id, 'billing_phone', true);
                $b_company = get_user_meta($customer_id, 'billing_company', true);
                $b_address_1 = get_user_meta($customer_id, 'billing_address_1', true);
                $b_address_2 = get_user_meta($customer_id, 'billing_address_2', true);
                $b_city = get_user_meta($customer_id, 'billing_city', true);
                $b_state = get_user_meta($customer_id, 'billing_state', true);
                $b_postcode = get_user_meta($customer_id, 'billing_postcode', true);
                $b_country = get_user_meta($customer_id, 'billing_country', true);

                $child_order->set_billing_first_name($b_first_name ?: $payer['first_name']);
                if ($b_last_name || $payer['last_name'])
                    $child_order->set_billing_last_name($b_last_name ?: $payer['last_name']);
                $child_order->set_billing_email($b_email ?: $payer['email']);
                $child_order->set_billing_phone($b_phone ?: $payer['phone']);
                $child_order->set_billing_company($b_company ?: $payer['company']);

                if ($b_address_1 || $payer['address_1'])
                    $child_order->set_billing_address_1($b_address_1 ?: $payer['address_1']);
                if ($b_address_2 || $payer['address_2'])
                    $child_order->set_billing_address_2($b_address_2 ?: $payer['address_2']);
                if ($b_city || $payer['city'])
                    $child_order->set_billing_city($b_city ?: $payer['city']);
                if ($b_state || $payer['state'])
                    $child_order->set_billing_state($b_state ?: $payer['state']);
                if ($b_postcode || $payer['postcode'])
                    $child_order->set_billing_postcode($b_postcode ?: $payer['postcode']);
                if ($b_country || $payer['country'])
                    $child_order->set_billing_country($b_country ?: $payer['country']);

            } else {
                $child_order->set_billing_first_name($payer['first_name']);
                $child_order->set_billing_last_name($payer['last_name']);
                $child_order->set_billing_email($payer['email']);
                $child_order->set_billing_phone($payer['phone']);

                if (!empty($payer['company'])) {
                    $child_order->set_billing_company($payer['company']);
                }
                if (!empty($payer['address_1']))
                    $child_order->set_billing_address_1($payer['address_1']);
                if (!empty($payer['address_2']))
                    $child_order->set_billing_address_2($payer['address_2']);
                if (!empty($payer['city']))
                    $child_order->set_billing_city($payer['city']);
                if (!empty($payer['state']))
                    $child_order->set_billing_state($payer['state']);
                if (!empty($payer['postcode']))
                    $child_order->set_billing_postcode($payer['postcode']);
                if (!empty($payer['country']))
                    $child_order->set_billing_country($payer['country']);
            }

            if (!empty($payer['gstin'])) {
                $child_order->update_meta_data('_billing_gstin', sanitize_text_field($payer['gstin']));
            }

            $child_order->update_meta_data('group_payer_name', sanitize_text_field($payer['name']));
            $child_order->update_meta_data('group_payer_email', sanitize_email($payer['email']));
            $child_order->update_meta_data('group_payer_phone', sanitize_text_field($payer['phone'] ?? ''));
            $child_order->update_meta_data('group_payer_company', sanitize_text_field($payer['company'] ?? ''));

            $child_order->update_meta_data('group_parent_order', $order_id);
            $child_order->update_meta_data('is_group_child_order', 'yes');
            if (!$child_order->get_meta('_unique_apl_id')) {
                $child_order->update_meta_data('_unique_apl_id', $unique_apl_id);
                update_option('apl_sequence_counter', $new_sequence);
            }
            $child_order->set_total($share_amount);
            $child_order->save();
            $updated_payers[$key]['child_order_id'] = $child_order->get_id();
            $gen_child[] = $child_order->get_id();
        }

        $order->update_meta_data('group_additional_payers', $updated_payers);
        $order->save();
        generate_admin_invoice_pdf($order);
        foreach ($gen_child as $child) {
            $child_ord = wc_get_order($child);
            generate_admin_invoice_pdf($child_ord);
        }
    }
}


add_action('woocommerce_admin_order_items_after_shipping', 'add_child_refund_rows_to_table', 10, 1);
function add_child_refund_rows_to_table($order_id)
{
    $order = wc_get_order($order_id);
    $child_orders = $order->get_meta('group_additional_payers');
    $child_order_ids = array();
    foreach ($child_orders as $child) {
        $child_order_ids[] = $child['child_order_id'];
    }

    if (empty($child_order_ids)) {
        return;
    }

    foreach ($child_order_ids as $child_id) {
        $child_order = wc_get_order($child_id);
        if ($child_order) {
            $refunds = $child_order->get_refunds();
        }


        if ($refunds) {
            foreach ($refunds as $refund) {
                $who_refunded = get_userdata($refund->get_refunded_by());
                $refund_name = $who_refunded ? $who_refunded->display_name : 'System';
                ?>
                <tr class="refund " data-order_refund_id="<?php echo esc_attr($refund->get_id()); ?>">
                    <td class="thumb">
                        <div></div>
                    </td>

                    <td class="name">
                        <?php
                        printf(
                            'Refund #%d - %s by %s',
                            $refund->get_id(),
                            $refund->get_date_created()->date_i18n(get_option('date_format') . ', ' . get_option('time_format')),
                            '<abbr class="refund_by" title="ID: ' . $refund->get_refunded_by() . '">' . esc_html($refund_name) . '</abbr>'
                        );
                        ?>
                        <?php if ($reason = $refund->get_reason()): ?>
                            <p class="description"><?php echo esc_html($reason); ?></p>
                        <?php endif; ?>
                    </td>

                    <td class="item_cost" width="1%">&nbsp;</td>
                    <td class="quantity" width="1%">&nbsp;</td>

                    <td class="line_cost" width="1%">
                        <div class="view">
                            <span class="woocommerce-Price-amount amount">
                                <?php echo wc_price('-' . $refund->get_amount(), array('currency' => $refund->get_currency())); ?>
                            </span>
                        </div>
                    </td>

                    <td class="line_tax" width="1%"></td>

                    <td class="wc-order-edit-line-item">
                        <div class="wc-order-edit-line-item-actions">
                            <span class="tips" data-tip="Child Order Refund - View child order to manage">
                                <span class="dashicons dashicons-external"></span>
                            </span>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }
    }
}


/* ---------------------------------------------------------
   REALTIME CHECKOUT SPLIT DRAFT SAVER
   --------------------------------------------------------- */
add_action('wp_ajax_phive_save_checkout_split_draft', 'phive_save_checkout_split_draft_callback');
add_action('wp_ajax_nopriv_phive_save_checkout_split_draft', 'phive_save_checkout_split_draft_callback');

function phive_save_checkout_split_draft_callback()
{
    if (!isset(WC()->session)) {
        wp_send_json_error('No WooCommerce session');
    }

    $draft_data = array(
        'mode' => isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : '',
        'count' => isset($_POST['count']) ? intval($_POST['count']) : 2,
        'parties' => isset($_POST['parties']) ? wc_clean(wp_unslash($_POST['parties'])) : array(),
    );

    // Save to WooCommerce Session
    WC()->session->set('phive_checkout_split_draft', $draft_data);
    wp_send_json_success();
}

add_action('woocommerce_before_checkout_form', 'phive_load_checkout_split_draft');
function phive_load_checkout_split_draft()
{
    if (isset(WC()->session)) {
        $draft_data = WC()->session->get('phive_checkout_split_draft');
        if (!empty($draft_data)) {
            echo '<script>window.phive_checkout_draft = ' . wp_json_encode($draft_data) . ';</script>';
        }
    }
}


/**
 * Redirect and display notice when attempting to pay for a cancelled booking.
 */
add_action('template_redirect', 'accordhub_cancelled_payment_notice', 9);

function accordhub_cancelled_payment_notice()
{
    // Check if the user is on the WooCommerce "order-pay" endpoint
    if (is_wc_endpoint_url('order-pay')) {
        global $wp;

        $order_id = isset($wp->query_vars['order-pay']) ? absint($wp->query_vars['order-pay']) : 0;

        if ($order_id) {
            $order = wc_get_order($order_id);

            // Check if the order is valid and its status is "cancelled"
            if ($order && $order->has_status('cancelled')) {
                // Intercept and replace the default WooCommerce cancelled order notice to prevent duplicates
                add_filter('woocommerce_add_error', function ($message) {
                    if (strpos($message, 'it cannot be paid for') !== false) {
                        return 'This booking has been cancelled; therefore, payment is no longer accepted. Please contact the Admin for further information.';
                    }
                    return $message;
                });
            } elseif ($order && $order->has_status('processing')) {
                add_filter('woocommerce_add_error', function ($message) {
                    if (strpos($message, 'it cannot be paid for') !== false) {
                        return 'This booking has already been paid; therefore, payment is no longer accepted. Please contact the Admin for further information.';
                    }
                    return $message;
                });
            }
        }
    }
}


add_action('wp_ajax_ph_delete_freezed_slots', 'ph_delete_freezed_posts_ajax');
add_action('wp_ajax_ph_delete_freezed_slots', 'ph_delete_freezed_posts_ajax');
function ph_delete_freezed_posts_ajax()
{
    global $wpdb;
    global $wp_version;

    $query_post = "SELECT ID as freezed_id,post_date
    FROM {$wpdb->prefix}posts AS t1
    WHERE t1.post_type = 'booking_slot_freez'";

    $freezed_ids = $wpdb->get_results($query_post, ARRAY_A);
    $freezed_idss = array();

    if (!empty($freezed_ids)) {
        foreach ($freezed_ids as $key => $product) {

            $freezed_idss[] = $product['freezed_id'];

            $post_date = date('Y-m-d H:i:s', strtotime($product['post_date']));

            if (version_compare($wp_version, '5.3', '>=')) {

                $currentTime = current_datetime();
                $currentTime = $currentTime->format('Y-m-d H:i:s');
            } else {

                $currentTime = current_time('Y-m-d H:i:s');
            }

            // --- Changed to 10 minutes ---
            $before10mins = strtotime('-10 minutes', strtotime($currentTime));
            $before10mins = date('Y-m-d H:i:s', $before10mins);

            if (strtotime($post_date) < strtotime($before10mins)) {

                $asset_id = get_post_meta($product['freezed_id'], 'asset_id', 1);

                if ($asset_id != '') {

                    $ph_cache_obj = new phive_booking_cache_manager();
                    $ph_cache_obj->ph_unset_cache($asset_id);
                }

                // Clear the frozen slot from the custom availability table as well
                if (class_exists('Phive_Bookings_Database')) {
                    $obj = new Phive_Bookings_Database();
                    $obj->delete_data_availability_table($product['freezed_id'], 'order_id', 'cart');
                }

                wp_delete_post($product['freezed_id']);
            }
        }
    }
    wp_send_json_success();
}


// --- PASS WC SESSION TIMER DATA TO CHECKOUT JS ---
add_action('wp_head', 'inject_wc_session_timer_data');
function inject_wc_session_timer_data()
{
    if (is_checkout() && !is_wc_endpoint_url()) {
        if (is_null(WC()->session))
            return;
        $expiry = WC()->session->get('phive_checkout_expiry');
        $hash = WC()->session->get('phive_checkout_booking_hash');
        echo "<script>
            var wc_phive_expiry = " . ($expiry ? "'" . esc_js($expiry) . "'" : "null") . ";
            var wc_phive_hash = " . ($hash ? "'" . esc_js($hash) . "'" : "null") . ";
        </script>";
    }
}

// --- AJAX ACTION TO UPDATE WC SESSION TIMER DATA ---
add_action('wp_ajax_ph_update_timer_session', 'ph_update_timer_session');
add_action('wp_ajax_nopriv_ph_update_timer_session', 'ph_update_timer_session');
function ph_update_timer_session()
{
    if (is_null(WC()->session)) {
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
    }

    if (isset($_POST['expiry']) && $_POST['expiry'] !== '') {
        WC()->session->set('phive_checkout_expiry', sanitize_text_field($_POST['expiry']));
    } else {
        WC()->session->set('phive_checkout_expiry', null);
    }

    if (isset($_POST['hash']) && $_POST['hash'] !== '') {
        WC()->session->set('phive_checkout_booking_hash', sanitize_text_field($_POST['hash']));
    }

    wp_send_json_success();
}

// --- TRIGGER WOOCOMMERCE NEW ACCOUNT EMAIL ON USER REGISTER ---
add_action('user_register', 'phive_trigger_wc_email_on_custom_register', 20, 1);
function phive_trigger_wc_email_on_custom_register($user_id)
{
    if (!$user_id) {
        return;
    }
    if (!is_admin() || !current_user_can('create_users')) {
        return;
    }
    $user = get_userdata($user_id);
    $email = $user->user_email;

    $subject = "Welcome to Accordhub - Book Premium Rooms for Your Next ADR Hearing";
    $heading = "Welcome to a Better ADR Hearing Experience";

    $msg = "<p>Your account has been successfully created, and you can now experience high-quality ADR spaces designed for smooth, hassle-free hearings.</p>";
    $msg .= "<p>You can log in anytime at <a href=" . home_url() . ">accordhub.in</a> and start exploring.</p>";
    $msg .= '<p class="btn_p"><a href="' . home_url() . '/login/">Login to Get Started</a></p>';
    $msg .= "<p>With your Accordhub account, you can:</p>";
    $msg .= "<ul>
                <li>Browse and book hearing rooms based on the number of participants</li>
                <li>Manage all your ADR bookings in one place</li>
                <li>Receive timely updates and support</li>
                <li>Experience seamless tech and hospitality assistance during your sessions</li>
            </ul>";

    send_woocommerce_custom_email($email, $subject, $heading, $msg);
}

add_filter('send_email_change_email', '__return_false');


// =========================================================================
// BI-DIRECTIONAL PHONE SYNCHRONIZATION (BULLETPROOF SOLUTION)
// =========================================================================

// Syncs phone -> billing_phone AND billing_phone -> phone
add_action('updated_user_meta', 'accordhub_sync_phone_and_billing_phone', 10, 4);
add_action('added_user_meta', 'accordhub_sync_phone_and_billing_phone', 10, 4);

function accordhub_sync_phone_and_billing_phone($meta_id, $object_id, $meta_key, $_meta_value)
{
    // 1. Sync from Account Phone (phone) -> WooCommerce Billing Phone (billing_phone)
    if ('phone' === $meta_key) {
        $user_id = $object_id;
        $new_phone = sanitize_text_field($_meta_value);
        $current_billing_phone = get_user_meta($user_id, 'billing_phone', true);

        if ($current_billing_phone !== $new_phone) {
            // Temporarily unhook to ensure absolute loop protection
            remove_action('updated_user_meta', 'accordhub_sync_phone_and_billing_phone', 10);
            remove_action('added_user_meta', 'accordhub_sync_phone_and_billing_phone', 10);

            update_user_meta($user_id, 'billing_phone', $new_phone);

            // Re-hook safely
            add_action('updated_user_meta', 'accordhub_sync_phone_and_billing_phone', 10, 4);
            add_action('added_user_meta', 'accordhub_sync_phone_and_billing_phone', 10, 4);
        }
    }

    // 2. Sync from WooCommerce Billing Phone (billing_phone) -> Account Phone (phone)
    elseif ('billing_phone' === $meta_key) {
        $user_id = $object_id;
        $new_phone = sanitize_text_field($_meta_value);
        $current_phone = get_user_meta($user_id, 'phone', true);

        if ($current_phone !== $new_phone) {
            // Temporarily unhook to ensure absolute loop protection
            remove_action('updated_user_meta', 'accordhub_sync_phone_and_billing_phone', 10);
            remove_action('added_user_meta', 'accordhub_sync_phone_and_billing_phone', 10);

            update_user_meta($user_id, 'phone', $new_phone);

            // Re-hook safely
            add_action('updated_user_meta', 'accordhub_sync_phone_and_billing_phone', 10, 4);
            add_action('added_user_meta', 'accordhub_sync_phone_and_billing_phone', 10, 4);
        }
    }
}