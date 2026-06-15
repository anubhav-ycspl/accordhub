<?php

/**
 * @since 2.2.0
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Phive_Bookings_Database' ) ) 
{
	class Phive_Bookings_Database
    {
        /**
         *@var  $availability_tablename
         */
        public $availability_tablename;

		public function __construct() 
        {
            $this->availability_tablename = 'ph_bookings_availability_calculation_data';
            $this->ph_bookings_update_db_check();
            add_action('ph_bookings_clean_up_availability_table_daily_event', array($this,'clean_up_availability_table'));
            add_action('ph_bookings_clean_up_cart_data_availability_table_twicedaily_event', array($this,'clean_up_cart_data_availability_table'));

            // Clear the deleted order items from availability table
            add_action( 'wp_loaded', array($this, 'ph_booking_clear_deleted_edit_order_items') );
		}

        /**
         * this function will clear the data that there in availability table even though those bookings has been deleted
         */
        public function ph_booking_clear_deleted_edit_order_items()
        {
            if(get_option("ph_booking_clear_deleted_edit_order_items",'no') == 'yes'){
                return;
            }
            global $wpdb;
            $sql = "SELECT order_id, order_item_id FROM {$wpdb->prefix}ph_bookings_availability_calculation_data";
            $result = $wpdb->get_results($sql);
            $order_list =  json_decode(json_encode($result), true);
            $deleted_orders = [];
            $item_id_list = [];

            foreach (array_column($order_list, 'order_id') as $order_id) {

                $order = wc_get_order($order_id);
                if (!$order instanceof WC_Order) {
                    $deleted_orders[] = $order_id;
                    continue;
                }
                foreach ($order->get_items() as $item_id => $item) {
                    if (!in_array($item_id, $item_id_list)) {
                        $item_id_list[]  = $item_id;
                    }
                }
            }
            $deleted_item_ids = array_diff(array_column($order_list, 'order_item_id'), $item_id_list);
            $obj    = new Phive_Bookings_Database();

            // Removing deleted order item data
            foreach (array_unique($deleted_item_ids) as $item_id) {

                $obj->delete_data_availability_table($item_id, 'order_item_id', 'booked');
            }

            // Removing deleted order data
            foreach (array_unique($deleted_orders) as $order_id) {
                $obj->delete_data_availability_table($order_id, 'order_id', 'booked');
            }
            update_option('ph_booking_clear_deleted_edit_order_items', 'yes');
        }
        
        public function ph_bookings_update_db_check()
        {
            $intalled_version = get_option('ph_bookings_appointments_db_version');
			$version_compare  = version_compare(PH_BOOKINGS_PLUGIN_DB_VERSION, $intalled_version);
            if(empty($intalled_version))
			{
				$this->create_availability_table();
			}
			else if($version_compare > 0 && version_compare(PH_BOOKINGS_PLUGIN_DB_VERSION, '1.0.1', '==')) 
            {
                $this->create_availability_table();
			}
            else
            {

                // clean up availability table for past dates
                if (!wp_next_scheduled ( 'ph_bookings_clean_up_availability_table_daily_event' )) {
                    wp_schedule_event(time(), 'daily', 'ph_bookings_clean_up_availability_table_daily_event');
                }

                // clean up availability table for unnecessary rows from unfreezed cart bookings
                if (!wp_next_scheduled ( 'ph_bookings_clean_up_cart_data_availability_table_twicedaily_event' )) {
                    wp_schedule_event(time(), 'twicedaily', 'ph_bookings_clean_up_cart_data_availability_table_twicedaily_event');
                }
            }
        }


		public function create_availability_table()
		{
            /* 
                # Fields are not restricted using enum for flexibility in future
                - booking_type can be either 'booked' or 'cart'
                - participant_as_booking can be either 'yes' or 'no'
                - booked_date_type can be in ('from', 'middle', 'to')
            */
            global $wpdb;
            $tablename = $wpdb->prefix.$this->availability_tablename;
            $sql = "CREATE TABLE $tablename
                    (
                        sno bigint(20) UNSIGNED AUTO_INCREMENT,
                        order_id bigint(20) UNSIGNED NOT NULL,
                        order_item_id bigint(20) UNSIGNED NOT NULL,
                        product_id bigint(20) UNSIGNED NOT NULL,
                        booked_date timestamp default '0000-00-00 00:00:00',
                        booked_date_end timestamp default '0000-00-00 00:00:00',
                        asset_id text default NULL,
                        participant_count int(11) UNSIGNED default NULL,
                        participant_as_booking varchar(191) default 'no',
                        charge_per_night varchar(191) default 'no',
                        booking_interval_type varchar(191) default NULL,
                        booking_interval bigint(20) UNSIGNED default NULL,
                        booked_date_type varchar(191) default NULL,
                        booking_type varchar(191) default 'booked',
                        booking_status varchar(191) default NULL,
                        woocommerce_order_status varchar(191) default NULL,
                        participant_detail text default NULL,
                        resource_detail text default NULL,
                        additional_data text default NULL,
                        created_at timestamp default CURRENT_TIMESTAMP,
                        updated_at timestamp default '0000-00-00 00:00:00',
                        PRIMARY KEY  (sno)
                    );";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
			update_option( 'ph_bookings_appointments_db_version', PH_BOOKINGS_PLUGIN_DB_VERSION );
		}

        public function alter_availability_table()
        {
            # code...
        }

        public function get_result_availability_table()
        {
            global $wpdb;
            $tablename = $wpdb->prefix.$this->availability_tablename;
            $results = $wpdb->get_results( "SELECT * FROM $tablename where booking_status='paid'" );
            // error_log('results : '.print_r($results,1));
        }

        public function insert_data_availability_table($data='')
        {
            if(!$this->ph_availability_table_exists()){
                return false;
            }
            global $wpdb;
            $tablename          = $wpdb->prefix.$this->availability_tablename;

            if(empty($data) && !is_array($data))
            {
                return false;
            }
            $booked_date        = $data['booked_date'];
            $booked_date_end    = $data['booked_date_end'];
            $booked_date_type   = $data['booked_date_type'];
            $booking_type       = $data['booking_type'];
            $additional_data    = isset($data['additional_data']) ? $data['additional_data'] : NULL;
            $participant_detail = isset($data['participant_detail']) ? $data['participant_detail'] : NULL;
            $resource_detail    = isset($data['resource_detail']) ? $data['resource_detail'] : NULL;

            $status = $wpdb->insert( 
				$tablename, 
				array( 
					'order_id' 					=> $data['order_id'], 
					'order_item_id' 			=> $data['order_item_id'], 
					'product_id' 				=> $data['product_id'], 
					'booked_date' 				=> "$booked_date",
					'booked_date_end'			=> "$booked_date_end",
                    'booked_date_type'          => "$booked_date_type",
                    'booking_type'              => "$booking_type",
					'booking_status' 			=> $data['booking_status'],
					'participant_count' 		=> $data['number_of_persons'],
					'participant_as_booking' 	=> $data['person_as_booking'],
                    'charge_per_night'          => $data['charge_per_night'],
					'booking_interval_type'		=> $data['interval_format'],
					'booking_interval'			=> $data['interval'],
					'asset_id'					=> $data['asset_id'],
                    'woocommerce_order_status'  => $data['woocommerce_order_status'],
                    'participant_detail'        => $participant_detail,
                    'resource_detail'           => $resource_detail,
                    'additional_data'           => $additional_data
				)
			);

            return $status;
        }

		public function update_data_availability_table()
		{
			# code...
		}

        public function clean_up_availability_table()
        {
            if(!$this->ph_availability_table_exists()){
                return ;
            }
            global $wpdb;
            $tablename      = $wpdb->prefix.$this->availability_tablename;

            // delete older rows
            $timeZone       = $this->get_site_timezone();
            $timeZone       = new DateTimeZone($timeZone);
            $current_date   = new DateTime('NOW');
            $current_date->setTimezone($timeZone);
            $current_date   = $current_date->format('Y-m-d H:i:s');
            $current_date   = date('Y-m-d H:i:s', strtotime('-2 day', strtotime($current_date)));
            
            $prepare_values = array( $current_date );
            $query 			= $wpdb->prepare( "DELETE FROM $tablename WHERE booked_date_end < %s", $prepare_values);
            $status = $wpdb->query($query);
            if($status)
            {
                $this->reset_auto_increment_availability_table();
            }
        }

        /*
            id_type can be      - product_id, order_id, order_item_id
            booking_type can be - booked, cart
        */
        public function delete_data_availability_table($id, $id_type='', $booking_type='')
        {
            if(!$this->ph_availability_table_exists()){
                return false;
            }
            global $wpdb;
            $tablename          = $wpdb->prefix.$this->availability_tablename;   
            if($booking_type != '')         
            {    
                $prepare_values = array( $id, $booking_type );
                $query 			= $wpdb->prepare( "DELETE FROM $tablename WHERE $id_type = %d AND booking_type = %s", $prepare_values);
            }
            else
            {
                $prepare_values = array( $id );
                $query 			= $wpdb->prepare( "DELETE FROM $tablename WHERE $id_type = %d", $prepare_values);
            }
            $status             = $wpdb->query($query);

            $this->reset_auto_increment_availability_table();
            return $status;
        }

        /*
            id_type can be      - product_id, order_id, order_item_id
            booking_type can be - booked, cart
            status_type can be  - booking_status, woocommerce_order_status
        */
        public function update_status_availability_table($id, $id_type='', $booking_type='', $status='', $status_type='')
        {
            if(!$this->ph_availability_table_exists()){
                return false;
            }
            global $wpdb;
            $tablename      = $wpdb->prefix.$this->availability_tablename;            
            $prepare_values = array( $status, $id, $booking_type );
            $query 			= $wpdb->prepare( "UPDATE $tablename SET $status_type = '%s' WHERE $id_type = %d AND booking_type = %s", $prepare_values);
            $status         = $wpdb->query($query);
            return $status;
        }

        public function clean_up_cart_data_availability_table()
        {
            if(!$this->ph_availability_table_exists()){
                return;
            }
            global $wpdb;
            $tablename      = $wpdb->prefix.$this->availability_tablename;

            // delete older rows
            $timeZone       = $this->get_site_timezone();
            $timeZone       = new DateTimeZone($timeZone);
            $current_date   = new DateTime('NOW');
            $current_date->setTimezone($timeZone);
            $current_date   = $current_date->format('Y-m-d');
            
            $prepare_values = array( $current_date );
            $query 			= $wpdb->prepare( "DELETE FROM $tablename WHERE DATE(created_at) < %s AND booking_type = 'cart' AND booking_status = 'canceled'", $prepare_values);
            $status = $wpdb->query($query);

            if($status)
            {
                $this->reset_auto_increment_availability_table();
            }
        }

        public function reset_auto_increment_availability_table()
        {
            global $wpdb;
            $tablename      = $wpdb->prefix.$this->availability_tablename;
            $row            = $wpdb->get_row( "SELECT Max(sno) as max_sno FROM $tablename" );
            
            $auto_increment = (int) $row->max_sno + 1;
            // error_log('auto_increment : '.print_r($auto_increment,1));
            $prepare_values = array( $auto_increment );
            $query 			= $wpdb->prepare( "ALTER TABLE $tablename AUTO_INCREMENT = %d", $prepare_values);
            $status         = $wpdb->query($query);
        }

        public function get_site_timezone()
        {
            global $wp_version;
            $timezone 		    = get_option('timezone_string');
            if(empty($timezone)) 
            {
                $time_offset    = get_option('gmt_offset');
                // Considered daylight saving off
                $timezone       = timezone_name_from_abbr("", $time_offset * 60 * 60, 0 );
                if ( version_compare( $wp_version, '5.3', '>=' ) ) 
                {
                    $timezone   = wp_timezone_string();
                }
            }
            return $timezone ? $timezone : 'UTC';
        }

        public function update_charge_per_night_availability_table($charge_per_night, $product_id)
        {
            if(!$this->ph_availability_table_exists()){
                return false;
            }
            global $wpdb;
            $tablename      = $wpdb->prefix.$this->availability_tablename;            
            $prepare_values = array( $charge_per_night, $product_id );
            $query 			= $wpdb->prepare( "UPDATE $tablename SET charge_per_night = '%s' WHERE product_id = %d", $prepare_values);
            $status         = $wpdb->query($query);
            return $status;
        }

        public function get_order_ids_from_availability_table()
        {
            global $wpdb;
            $tablename      = $wpdb->prefix.$this->availability_tablename;            
            $order_ids      = $wpdb->get_col( "SELECT DISTINCT order_id FROM $tablename" );
            return $order_ids;
        }

        public function empty_availability_table()
        {
            global $wpdb;
            $tablename      = $wpdb->prefix.$this->availability_tablename;            
            $query 			= "DELETE FROM $tablename";
            $wpdb->query($query);

            $this->reset_auto_increment_availability_table();
        }

        public static function ph_availability_table_exists(){
            global $wpdb;
            $table_name = $wpdb->prefix.'ph_bookings_availability_calculation_data';
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                return true;
            }
            return false;
        }
	}
	new Phive_Bookings_Database();
}