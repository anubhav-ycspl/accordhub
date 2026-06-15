<?php if ( ! defined( 'ABSPATH' ) ) exit;
$room_id = isset( $atts['room_id'] ) ? esc_attr( $atts['room_id'] ) : ''; ?>

<div class="user-addon-dashboard">
    <h3>Room ID: <?php echo $room_id; ?></h3>
    <?php 
        
        $booking = get_phive_product_current_booking( $room_id );
        if($booking && $booking['status'] === 'active'){
            $order_id = $booking['order_id'];
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }

            $first_name = $order->get_billing_first_name();
            $last_name  = $order->get_billing_last_name();

            $full_name = trim( $first_name . ' ' . $last_name );

            echo '<div>';
                esc_html_e('Details', 'woocommerce');
                echo '<br>';
                echo get_the_title($room_id);
                echo '<br>';
                esc_html_e('Booking Reference No: ', 'woocommerce');
                echo $order_id;
                echo '<br>';
                esc_html_e('User: ', 'woocommerce');
                echo $full_name;
            echo '</div>';

        }else{
            return;
        }
        
        
    ?>
</div>
