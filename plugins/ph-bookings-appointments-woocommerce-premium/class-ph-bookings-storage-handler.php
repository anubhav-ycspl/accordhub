<?php

use Automattic\WooCommerce\Utilities\OrderUtil;

defined('ABSPATH') || exit;

class PH_WC_Bookings_Storage_Handler
{
    public $order;

    /**
     * Constructor
     *
     * @param object $order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Add meta data to order instance
     *
     * @param string $meta_key
     * @param mixed $meta_data
     */
    public function ph_add_meta_data($meta_key, $meta_data)
    {
        $this->order->update_meta_data($meta_key, $meta_data);
    }

    /**
     * Delete meta data from within the order instance
     *
     * @param string $meta_key
     */
    public function ph_delete_meta_data($meta_key)
    {
        $this->order->delete_meta_data($meta_key);
    }

    /**
     * Save meta data to the order instance
     */
    public function ph_save_meta_data()
    {
        $this->order->save();
    }

    /**
     * Check if WooCommerce HPOS enabled in the store
     *
     * @return bool Returns true if HPOS is enabled else returns false
     */
    public static function ph_check_if_hpo_enabled()
    {
        return OrderUtil::custom_orders_table_usage_is_enabled();
    }

    /**
     * Add meta data to order and save
     *
     * @param int $order_id
     * @param string $meta_key
     * @param mixed $meta_data
     */
    public static function ph_add_and_save_meta_data($order_id, $meta_key, $meta_data)
    {
        $order = wc_get_order($order_id);
        $order->update_meta_data($meta_key, $meta_data);
        $order->save();
    }

    /**
     * Get meta data
     *
     * @param int $order_id
     * @param string $meta_key
     * @param bool $single
     * @return mixed
     */
    public static function ph_get_meta_data($order_id, $meta_key, $single = true)
    {
        $order = wc_get_order($order_id);

        return $order->get_meta($meta_key, $single);
    }

    /**
     * Delete meta data
     */
    public static function ph_delete_and_save_meta_data($order_id, $meta_key)
    {
        $order = wc_get_order($order_id);
        $order->delete_meta_data($meta_key);
        $order->save();
    }
}