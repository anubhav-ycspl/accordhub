<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class phive_booking_checkout_decorator {

	/**
	 * @var $map_booking_status_with_order_status
	 */
	public $map_booking_status_with_order_status;

	/**
	 * @var $map_booking_status_to_name
	 */
	public $map_booking_status_to_name;

	/**
	 * @var $booked_from_text
	 */
	public $booked_from_text;

	/**
	 * @var $booked_to_text
	 */
	public $booked_to_text;

	/**
	 * @var $assets_rules
	 */
	public $assets_rules;

	public function __construct() {
		$this->init();
		
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'phive_add_booking_info_order_line_item_meta' ), 10, 4 ); // Add Custom Details with Order Line Items while checking out
		add_action( 'woocommerce_my_account_my_orders_actions', array( $this, 'phive_myaccount_order_payment_button' ), 10, 4 ); // Remove the pay button for non-confirmed orders

		add_filter( 'woocommerce_display_item_meta', array( $this, 'phive_booking_translate_order_meta_data' ), 11, 3 ); // translate order meta content

		add_action( 'woocommerce_order_status_pending', array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );
		// add_action( 'woocommerce_order_status_cancelled',    array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );
		add_action( 'woocommerce_order_status_failed', array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );

		// failed order booking status not cancelling
		add_action( 'ph_woocommerce_order_status_failed', array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'phive_change_booking_status_based_on_order_details' ), 5 );
		add_filter( 'woocommerce_email_order_items_args', array( $this, 'phive_order_email_order_items_args' ), 5 );
		add_filter( 'woocommerce_email_order_item_quantity', array( $this, 'phive_order_email_order_items_quantity' ), 5, 2 );

		// #91928 - hiding 'Booked To' in order details and emails
		add_filter( 'ph_bookings_order_meta_key_filters_for_admin', array( $this, 'phive_hide_details_from_booking_emails' ), 10, 2 );
		add_filter( 'ph_bookings_order_meta_key_filters', array( $this, 'phive_hide_details_from_booking_emails' ), 10, 2 );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'phive_hide_details_from_checkout_and_wc_emails' ), 10, 2 );

		// 140472
		add_filter( 'woocommerce_cart_needs_payment', array( $this, 'woocommerce_cart_needs_payment_requires_confirmation' ), 99, 2 );
	}

	// update quantity in order email with '-'
	public function phive_order_email_order_items_quantity( $quantity, $item = array() ) {
		if ( ! empty( $item ) && ph_is_bookable_product( $item->get_product_id() ) ) {
			return '-';
		}
		return $quantity;
	}

	// update item values with new
	public function phive_order_email_order_items_args( $order ) {
		$orders = wc_get_order( $order['order']->get_order_number() );
		if ( $orders instanceof WC_Order ) {
			$items          = $orders->get_items();
			$order['items'] = $items;
		}
		return $order;
	}

	private function init() {
		$this->map_booking_status_with_order_status = array(
			'pending'    => 'un-paid',
			'processing' => 'paid',
			'on-hold'    => 'un-paid',
			'completed'  => 'paid',
			'cancelled'  => 'canceled',
			'failed'     => 'canceled',
			'refunded'   => 'refunded',
		);
		$this->map_booking_status_to_name           = array(
			'paid'                  => __( 'Paid', 'bookings-and-appointments-for-woocommerce' ),
			'un-paid'               => __( 'Unpaid', 'bookings-and-appointments-for-woocommerce' ),
			'canceled'              => __( 'Cancelled', 'bookings-and-appointments-for-woocommerce' ),
			'requires-confirmation' => __( 'Requires Confirmation', 'bookings-and-appointments-for-woocommerce' ),
			'refunded'              => __( 'Refunded', 'bookings-and-appointments-for-woocommerce' ),
		);
		// 175970
		$display_settings       = get_option( 'ph_bookings_display_settigns' );
		$text_customisation     = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
		$this->booked_from_text = isset( $text_customisation['booked_from_text'] ) && ! empty( $text_customisation['booked_from_text'] ) ? $text_customisation['booked_from_text'] : __( 'Booked From', 'bookings-and-appointments-for-woocommerce' );
		$this->booked_to_text   = isset( $text_customisation['booked_to_text'] ) && ! empty( $text_customisation['booked_to_text'] ) ? $text_customisation['booked_to_text'] : __( 'Booked To', 'bookings-and-appointments-for-woocommerce' );
	}

	/**
	 * Change Booking Status of Order Bookings Products.
	 */
	public function phive_change_booking_status_based_on_order_details( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order instanceof WC_Order ) {
			$map_booking_status_with_order_status = $this->map_booking_status_with_order_status;
			$booking_status_name                  = $this->map_booking_status_to_name;

			$order_status = $order->get_status();
			// if( $order_status == 'refunded' )    return;
			$booking_status = ! empty( $map_booking_status_with_order_status[ $order_status ] ) ? $map_booking_status_with_order_status[ $order_status ] : null;

			// Zoom.
			do_action( 'ph_zoom_create_meeting_for_paid_order', $order_id, $order, $booking_status );

			$payment_method = $order->get_payment_method();
			// For COD always Unpaid
			if ( $payment_method == 'cod' ) {
				// 118250 - booking to be marked as paid if order status is set to completed
				if ( $order_status != 'completed' ) {
					$booking_status = 'un-paid';
				}
			}

			// 143735 - dokan integration sub orders & requires-confirmation support
			if ( $order_status == 'processing' && function_exists( 'dokan_is_sub_order' ) && dokan_is_sub_order( $order_id ) ) {
				if ( function_exists( 'dokan_get_prop' ) ) {
					$all_processing = 0;
					$parent_order   = wc_get_order( $order->get_parent_id() );
					$sub_orders     = get_children(
						array(
							'post_parent' => dokan_get_prop( $parent_order, 'id' ),
							'post_type'   => 'shop_order',
						)
					);

					if ( is_array( $sub_orders ) && count( $sub_orders ) > 0 ) {
						foreach ( $sub_orders as $sub_order_id => $sub_order ) {
							$sub_order = wc_get_order( $sub_order_id );
							if ( $sub_order->get_status() == 'processing' ) {
								++$all_processing;
							}
						}

						if ( $all_processing == count( $sub_orders ) ) {
							$parent_order_items = $parent_order->get_items();
							foreach ( $parent_order_items as $parent_order_item_id => $parent_order_item ) {
								wc_update_order_item_meta( $parent_order_item_id, 'confirmed', 'yes' );
								wc_update_order_item_meta( $parent_order_item_id, 'booking_status', array( 'paid' ) );
								wc_update_order_item_meta( $parent_order_item_id, __( 'Booking Status', 'bookings-and-appointments-for-woocommerce' ), __( $booking_status_name['paid'], 'bookings-and-appointments-for-woocommerce' ) );
							}
							$parent_order->update_status( 'processing' );
						}
					}
				}
			}

			if ( ! empty( $booking_status ) ) {
				$items = $order->get_items();
				foreach ( $items as $order_item_id => $line_item ) {
					if ( ph_is_bookable_product( $line_item->get_product_id() ) ) {
						$old_booking_status = $line_item->get_meta( 'booking_status' );

						// 143735
						if ( ph_maybe_unserialize( $old_booking_status ) == 'requires-confirmation' && ! in_array( $booking_status, array( 'canceled', 'refunded' ) ) ) {
							continue;
						}

						// status stays canceled even if wc status changes to any other
						wc_update_order_item_meta( $order_item_id, 'booking_status', array( $booking_status ) );

						// 103410 - Switching to product language
						$current_lang = ph_wpml_language_switch_admin_email( $order, '', 'order', '' );
						wc_update_order_item_meta( $order_item_id, __( 'Booking Status', 'bookings-and-appointments-for-woocommerce' ), __( $booking_status_name[ $booking_status ], 'bookings-and-appointments-for-woocommerce' ) );

						// 103410 - Switching back to current language
						ph_wpml_language_switch_admin_email( '', '', 'current', $current_lang );

						if ( $booking_status == 'canceled' ) {
							wc_update_order_item_meta( $order_item_id, 'canceled', 'yes' );
						} else {
							wc_delete_order_item_meta( $order_item_id, 'canceled', 'yes' );
						}

						// Sync google calender for all status
						do_action( 'ph_booking_item_calender_resynced', 're-sync-google-calender', $order_item_id, $order_id );
					}
				}
			}
		}
	}

	/**
	 * Remove the payment button if bookings are not confirmed by shopowner
	 */
	public function phive_myaccount_order_payment_button( $actions, $order ) {

		if ( ! $this->is_confrimed_all_bookings_of_order( $order ) ) {
			unset( $actions['pay'] );
		}
		return $actions;
	}

	private function is_confrimed_all_bookings_of_order( $order ) {

		// Check if current order is a balance payment order created via deposits plugin
		if ( ! empty( $order->get_created_via() ) && $order->get_created_via() == 'ph_deposits' ) {

			// If balance payment order then check confirmation on parent order
			$order = ! empty( $order->get_parent_id() ) ? wc_get_order( $order->get_parent_id() ) : $order;
		}

		$is_confrimed = true;

		$items = $order->get_items();

		foreach ( $items as $order_item_id => $line_item ) {

			$_product = wc_get_product( $line_item->get_product_id() );

			if ( empty( $_product ) ) {
				continue;
			}

			$product_id            = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $_product->get_id() );        // WPML compatibilty
			$required_confirmation = get_post_meta( $product_id, '_phive_book_required_confirmation', 1 );

			if ( $required_confirmation == 'yes' && $line_item->get_meta( 'confirmed' ) != 'yes' ) {
				$is_confrimed = false;
				break;
			}
		}
		return $is_confrimed;
	}

	private function phive_buffer_before_time( $from, $buffer_period, $book_interval, $buffer_before, $buffer_after = '0' ) {
		if ( $buffer_before == '0' ) {
			return;
		} else {
			switch ( $buffer_period ) {
				case 'day':
					$buffer_before_time = date( 'Y-m-d', ( strtotime( $from ) - ( $buffer_before * 3600 * 24 ) ) );
					break;
				case 'hour':
					$buffer_before_time = date( 'Y-m-d H:i', ( strtotime( $from ) - ( $buffer_before * 3600 ) ) );
					break;
				case 'minute':
					$buffer_after       = isset( $buffer_after ) ? $buffer_after : '00';
					$buffer_before_time = date( 'Y-m-d H:i', ( strtotime( $from ) - ( $buffer_before * 60 ) ) );
					break;
			}
			return $buffer_before_time;
		}
	}

	private function phive_buffer_after_time( $from, $to = '', $buffer_period = '', $book_interval = '', $buffer_before = '0', $buffer_after = '', $product_id = '' ) {
		$interval        = get_post_meta( $product_id, '_phive_book_interval', 1 );
		$interval_period = get_post_meta( $product_id, '_phive_book_interval_period', 1 );

		$to = ! empty( $to ) ? $to : $from;
		if ( $buffer_after == '0' ) {
			return;
		} else {
			switch ( $buffer_period ) {
				case 'day':
					$buffer_after_time = date( 'Y-m-d', ( strtotime( $to ) + ( $buffer_after * 3600 * 24 ) ) );
					break;
				case 'hour':
					$buffer_after_time = date( 'Y-m-d H:i', ( strtotime( $to ) + ( $buffer_after * 3600 ) ) );
					break;
				case 'minute':
					$buffer_before     = isset( $buffer_before ) ? $buffer_before : '00';
					$buffer_after_from = date( 'Y-m-d H:i', strtotime( "+$interval $interval_period", strtotime( $to ) ) );
					$buffer_after_time = date( 'Y-m-d H:i', ( strtotime( $buffer_after_from ) + ( $buffer_after * 60 ) ) );
					break;
			}
			return $buffer_after_time;
		}
	}

	private function phive_save_booking_buffer_info( $product_id, $buffer_before_time, $buffer_after_time, $person_as_booking = '', $number_of_booking = '', $is_buffer = '', $buffer_type = '', $asset_id = '' ) {
		$new_post = array(
			'ID'          => '',
			'post_type'   => 'booking_buffer_freez', // Custom Post Type Slug
			'post_status' => 'open',
			'post_title'  => 'Booking buffer freezer-' . uniqid(), // 171763 Issue: When activating our Bookings plugin, its taking a quite long time while checkout.
			'ping_status' => 'closed',
		);

		$buffer_id = wp_insert_post( $new_post );
		if ( ! $buffer_id ) {
			return false;
		}
		if ( $is_buffer == 'yes' && $buffer_type == 'buffer-before' ) {
			$meta_values = array(
				'_product_id'          => $product_id,
				'Buffer_before_From'   => $buffer_before_time,
				'Buffer_before_To'     => $buffer_after_time,
				'_booking_customer_id' => is_user_logged_in() ? get_current_user_id() : 0,
				'Number of persons'    => $number_of_booking,
				'person_as_booking'    => $person_as_booking,
				'ph_canceled'          => '0',

			);
		} elseif ( $is_buffer == 'yes' && $buffer_type == 'buffer-after' ) {
			$meta_values = array(
				'_product_id'          => $product_id,
				'Buffer_after_From'    => $buffer_before_time,
				'Buffer_after_To'      => $buffer_after_time,
				'_booking_customer_id' => is_user_logged_in() ? get_current_user_id() : 0,
				'Number of persons'    => $number_of_booking,
				'person_as_booking'    => $person_as_booking,
				'ph_canceled'          => '0',

			);
		}
		if ( $asset_id ) {
			$meta_values['buffer_asset_id'] = $asset_id;
		}
		foreach ( $meta_values as $meta_key => $value ) {
			update_post_meta( $buffer_id, $meta_key, $value );
		}

		return $buffer_id;
	}

	/**
	 * Add Bookings details with order line items.
	 */
	public function phive_add_booking_info_order_line_item_meta( $item, $cart_item_key, $values, $order ) {

		if ( ! ( $item instanceof WC_Order_Item_Product ) ) {
			return;
		}

		$product = $item->get_product();
		if ( empty( $product ) ) {
			return;         // If Product is not found
		}

		$product_type = $product->get_type();
		if ( $product_type != 'phive_booking' ) {
			return;     // Return If Product Type is not booking
		}

		$product_id          = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $values['product_id'] );
		$buffer_before       = get_post_meta( $product_id, '_phive_buffer_before', 1 );
		$buffer_after        = get_post_meta( $product_id, '_phive_buffer_after', 1 );
		$book_interval       = get_post_meta( $product_id, '_phive_book_interval', 1 );
		$buffer_period       = get_post_meta( $product_id, '_phive_buffer_period', 1 );
		$enable_buffer       = get_post_meta( $product_id, '_phive_enable_buffer', 1 );
		$_persons_as_booking = get_post_meta( $product_id, '_phive_booking_persons_as_booking', 1 );
		$interval            = get_post_meta( $product_id, '_phive_book_interval', 1 );
		$interval_period     = get_post_meta( $product_id, '_phive_book_interval_period', 1 );
		$asset_settings      = get_option( 'ph_booking_settings_assets', 1 );
		$this->assets_rules  = ( isset( $asset_settings['_phive_booking_assets'] ) && ! empty( $asset_settings['_phive_booking_assets'] ) ) ? $asset_settings['_phive_booking_assets'] : array();
		$asset_label         = get_post_meta( $product_id, '_phive_booking_assets_label' );
		$asset_label         = empty( $asset_label[0] ) ? 'Asset' : $asset_label[0];

		$_persons_as_booking = apply_filters( 'ph_is_persons_as_booking', ( $_persons_as_booking == 'yes' ), $product_id );
		$persons_as_booking  = $_persons_as_booking ? 'yes' : 'no';

		// converting to wp date & time format
		$values['phive_display_time_from'] = Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format( $values['phive_display_time_from'] );
		$values['phive_display_time_to']   = Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format( $values['phive_display_time_to'] );

		// Add Booking Interval and Booking Interval Forma or Period
		$product_interval_details = array(
			'interval'        => $interval,
			'interval_format' => $interval_period,
		);
		$item->add_meta_data( '_phive_booking_product_interval_details', $product_interval_details );

		$participant_booking_data = array();
		if ( array_key_exists( 'phive_booked_persons', $values ) ) {

			$persons_pricing_rules = get_post_meta( $product_id, '_phive_booking_persons_pricing_rules', 1 );
			$number_of_persons     = 0;
			// Looping through the rule and assign the corresponding rule value given by customer
			foreach ( $persons_pricing_rules as $key => $rule ) {

				if ( empty( $rule ) ) {
					continue;
				}

				if ( ! empty( $values['phive_booked_persons'][ $key ] ) ) {
					$participant_booking_data[] = array(
						'participant_label' => $rule['ph_booking_persons_rule_type'],
						'participant_count' => $values['phive_booked_persons'][ $key ],
					);
					$number_of_persons         += $values['phive_booked_persons'][ $key ];
					$item->add_meta_data( $rule['ph_booking_persons_rule_type'], $values['phive_booked_persons'][ $key ] );
				}
			}

			if ( ! empty( $number_of_persons ) ) {

				$item->add_meta_data( 'Number of persons', $number_of_persons );

				// need to show as Total number of participants rather than Number of persons
				$item->add_meta_data( __( 'Total Number of Participants', 'bookings-and-appointments-for-woocommerce' ), $number_of_persons );
			}

			// error_log('participant_booking_data : '.print_r($participant_booking_data,1));
			if ( count( $participant_booking_data ) > 0 ) {
				$item->add_meta_data( 'ph_bookings_participant_booking_data', $participant_booking_data );
			}
		}

		// Display Additional Notes
		if ( array_key_exists( 'phive_book_additional_notes_text', $values ) ) {

			$addition_notes_label = get_post_meta( $product_id, '_phive_additional_notes_label', 1 );
			
			if ( ! empty( $values['phive_book_additional_notes_text'] ) ) {

				$additional_notes_text = wp_unslash( $values['phive_book_additional_notes_text'] );

				$item->add_meta_data( $addition_notes_label, $additional_notes_text );

				// export additional notes with dynamic labels given
				$item->add_meta_data( 'ph_bookings_customer_additional_notes', (array) $additional_notes_text );
			}
		}

		// Disply resources details with items
		if ( array_key_exists( 'phive_booked_resources', $values ) ) {
			$resources_booking_data  = array();
			$resources_pricing_rules = get_post_meta( $product_id, '_phive_booking_resources_pricing_rules', 1 );
			$resources_type          = get_post_meta( $product_id, '_phive_booking_resources_type', 1 );

			// Looping through the rule and assign the corresponding rule value given by customer
			foreach ( $resources_pricing_rules as $key => $rule ) {

				if ( $rule['ph_booking_resources_auto_assign'] == 'yes' && $resources_type != 'single' ) {
					// continue;
				}
				if ( $resources_type == 'single' ) {
					if ( $values['phive_booked_resources'] == $rule['ph_booking_resources_name'] ) {
						$resources_booking_data[] = array(
							'resource_label'  => $rule['ph_booking_resources_name'],
							'resource_status' => 'yes',
						);
						$item->add_meta_data( $rule['ph_booking_resources_name'], __( 'yes', 'bookings-and-appointments-for-woocommerce' ) );
					}
				} elseif ( ( isset( $values['phive_booked_resources'][ $key ] ) ) && ( $values['phive_booked_resources'][ $key ] == 'yes' ) ) {

						$resources_booking_data[] = array(
							'resource_label'  => $rule['ph_booking_resources_name'],
							'resource_status' => $values['phive_booked_resources'][ $key ],
						);
						$item->add_meta_data( $rule['ph_booking_resources_name'], __( $values['phive_booked_resources'][ $key ], 'bookings-and-appointments-for-woocommerce' ) );
				}
			}

			// error_log('resources_booking_data : '.print_r($resources_booking_data,1));
			if ( count( $resources_booking_data ) > 0 ) {
				$item->add_meta_data( 'ph_bookings_resources_booking_data', $resources_booking_data );
			}
		}

		// Disply Assets details with items
		if ( ! empty( $values['phive_booked_assets'] ) ) {

			$asset_name = $this->assets_rules[ $values['phive_booked_assets'] ]['ph_booking_asset_name'];

			$item->add_meta_data( 'Assets', array( $values['phive_booked_assets'] ) );
			$item->add_meta_data( $asset_label, $asset_name );

			// update transient set value to no
			$asset_id     = $values['phive_booked_assets'];
			$ph_cache_obj = new phive_booking_cache_manager();
			$ph_cache_obj->ph_unset_cache( $asset_id );
		}

		// If case of fixed block of time, show only from date
		if ( array_key_exists( 'phive_book_from_date', $values ) && $values['phive_book_from_date'] == $values['phive_book_to_date'] ) {

			$item->add_meta_data( 'From', array( $values['phive_book_from_date'] ) );
			/**
			 * When the Book From & To Date are the same, we will not save the Book To Date in Order Item Meta.
			 * Case: In some cases, the Book To Date is saved in an unserialized format by default, as it is present in the Line Item.
			 * Affect: This causes an issue where the bookings do not show up in the admin calendar.
			 * Fix: Manually update the Booked To Date in serialized format in order to avoid any conflict.
			 */
			$item->add_meta_data( 'To', array( $values['phive_book_to_date'] ) );

			$item->add_meta_data( __( $this->booked_from_text, 'bookings-and-appointments-for-woocommerce' ), empty( $values['phive_display_time_from'] ) ? Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format( $values['phive_book_from_date'] ) : $values['phive_display_time_from'] );
			$item->add_meta_data( 'phive_display_time_from', array( $values['phive_display_time_from'] ) );
			if ( array_key_exists( 'phive_display_time_to', $values ) && ! empty( $values['phive_display_time_to'] ) ) {
				$item->add_meta_data( __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' ), $values['phive_display_time_to'] );
				$item->add_meta_data( 'phive_display_time_to', array( $values['phive_display_time_to'] ) );
			}
		} else {
			if ( array_key_exists( 'phive_book_from_date', $values ) ) {
				$item->add_meta_data( __( $this->booked_from_text, 'bookings-and-appointments-for-woocommerce' ), empty( $values['phive_display_time_from'] ) ? Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format( $values['phive_book_from_date'] ) : $values['phive_display_time_from'] );
				$item->add_meta_data( 'From', array( $values['phive_book_from_date'] ) );
				$item->add_meta_data( 'phive_display_time_from', array( $values['phive_display_time_from'] ) );
			}

			if ( array_key_exists( 'phive_book_to_date', $values ) ) {
				if ( ( $interval_period == 'minute' ) || ( $interval_period == 'hour' ) ) {
					$values['phive_booked_to_date'] = date( 'Y-m-d H:i', strtotime( "+$interval $interval_period", strtotime( $values['phive_book_to_date'] ) ) );
					$item->add_meta_data( __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' ), empty( $values['phive_display_time_to'] ) ? Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format( $values['phive_booked_to_date'] ) : $values['phive_display_time_to'] );
					$item->add_meta_data( 'To', array( $values['phive_book_to_date'] ) );
					$item->add_meta_data( 'phive_display_time_to', array( $values['phive_display_time_to'] ) );
				} else {
					// Handle Booking Per Night
					if ( $interval_period == 'day' ) {
						$enable_per_night = get_post_meta( $product_id, '_phive_book_charge_per_night', true );
						if ( $enable_per_night == 'yes' ) {
							$book_to_date = date_create( $values['phive_book_to_date'] );
							$book_to_date->modify( '-1 days' );
							$item->add_meta_data( '_ph_book_to_date_with_night', array( $book_to_date->format( 'Y-m-d' ) ) );
						}
					}

					$item->add_meta_data( 'To', array( $values['phive_book_to_date'] ) );
					$item->add_meta_data( __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' ), empty( $values['phive_display_time_to'] ) ? Ph_Bookings_General_Functions_Class::phive_get_date_in_wp_format( $values['phive_book_to_date'] ) : $values['phive_display_time_to'] );
					$item->add_meta_data( 'phive_display_time_to', array( $values['phive_display_time_to'] ) );
				}
			}
		}

		if ( $persons_as_booking == 'yes' ) {
			// serialising because no need to display in frontend
			$item->add_meta_data( 'person_as_booking', array( 'yes' ) );
		}
		if ( array_key_exists( 'phive_booked_price', $values ) ) {
			$item->add_meta_data( 'Cost', array( $values['phive_booked_price'] ) );
			// WPML Compatibilty
			// $currency = ! empty($woocommerce->session->client_currency) ? $woocommerce->session->client_currency : get_woocommerce_currency();
			// $item->add_meta_data(__('Booking Cost','bookings-and-appointments-for-woocommerce'),get_woocommerce_currency_symbol($currency).$values['phive_booked_price']);
		}

		if ( ! class_exists( 'PH_Booking_Gateway_Utils' ) ) {
			
			require_once plugin_dir_path( __FILE__ ) . 'payment/class-ph-booking-gateway-utils.php';
		}

		$required_confirmation = PH_Booking_Gateway_Utils::is_confirmation_booking_in_cart();

		if ( $required_confirmation ) {
			$booking_status = 'requires-confirmation';
		} else {
			$payment_method = $order->get_payment_method();
			$order_status   = $order->get_status();

			// ticket103401
			// ticket 127333-booking status is un-paid if order status is getting Failed.
			$booking_status = ( ( $payment_method == 'cheque' || $payment_method == 'cod' || $payment_method == 'bacs' || $order_status == 'on-hold' || $order_status == 'pending' || $order_status == 'failed' ) ) ? 'un-paid' : 'paid';
		}

		// wpml compatibility
		if ( $values['product_id'] != $product_id ) {
			$item->add_meta_data( '_ph_booking_dlang_product_id', $product_id );
		}

		$item->add_meta_data( 'booking_status', array( $booking_status ) );
		$item->add_meta_data( __( 'Booking Status', 'bookings-and-appointments-for-woocommerce' ), __( $this->map_booking_status_to_name[ $booking_status ], 'bookings-and-appointments-for-woocommerce' ) );
		if ( $enable_buffer == 'yes' ) {
			$buffer_before_from = $this->phive_buffer_before_time( $values['phive_book_from_date'], $buffer_period, $book_interval, $buffer_before, $buffer_after );

			$buffer_after_to = $this->phive_buffer_after_time( $values['phive_book_from_date'], $values['phive_book_to_date'], $buffer_period, $book_interval, $buffer_before, $buffer_after, $product_id );
			switch ( $interval_period ) {
				case 'day':
					$buffer_after_from = date( 'Y-m-d', strtotime( '+1 day', strtotime( $values['phive_book_to_date'] ) ) );

					$buffer_before_to = date( 'Y-m-d', strtotime( '-1 day', strtotime( $values['phive_book_from_date'] ) ) );
					break;
				case 'hour':
					$buffer_after_from = date( 'Y-m-d H:i', strtotime( "+$interval $interval_period", strtotime( $values['phive_book_to_date'] ) ) );

					$buffer_before_to = date( 'Y-m-d H:i', strtotime( "-$interval $interval_period", strtotime( $values['phive_book_from_date'] ) ) );
					break;
				case 'minute':
					$buffer_after_from = date( 'Y-m-d H:i', strtotime( "+$interval $interval_period", strtotime( $values['phive_book_to_date'] ) ) );

					$buffer_before_to = date( 'Y-m-d H:i', strtotime( "-$interval $interval_period", strtotime( $values['phive_book_from_date'] ) ) );
					break;
			}

			if ( $buffer_before_from == '' ) {
				$buffer_before_to = '';
			}
			if ( $buffer_after_to == '' ) {
				$buffer_after_from = '';
			}
			$asset_id = ! empty( $values['phive_booked_assets'] ) ? $values['phive_booked_assets'] : '';
			if ( $persons_as_booking == 'yes' ) {
				$buffer_before_id = $this->phive_save_booking_buffer_info( $product_id, $buffer_before_from, $buffer_before_to, array( 'yes' ), $number_of_persons, 'yes', 'buffer-before', $asset_id );
				$buffer_after_id  = $this->phive_save_booking_buffer_info( $product_id, $buffer_after_from, $buffer_after_to, array( 'yes' ), $number_of_persons, 'yes', 'buffer-after', $asset_id );
			} else {
				// $buffer_before_id                    = $this->phive_save_booking_buffer_info($product_id,$buffer_before_from,$buffer_before_to,'','','yes','buffer-after',$asset_id);
				$buffer_before_id = $this->phive_save_booking_buffer_info( $product_id, $buffer_before_from, $buffer_before_to, '', '', 'yes', 'buffer-before', $asset_id );
				$buffer_after_id  = $this->phive_save_booking_buffer_info( $product_id, $buffer_after_from, $buffer_after_to, '', '', 'yes', 'buffer-after', $asset_id );
			}

			$buffer_before_ids = array( "$buffer_before_id" );
			$buffer_after_ids  = array( "$buffer_after_id" );

			$item->add_meta_data( 'buffer_before_id', $buffer_before_ids );
			$item->add_meta_data( 'buffer_after_id', $buffer_after_ids );
		}
	}

	public function phive_booking_translate_order_meta_data( $html, $item, $args ) {

		$product    = $item->get_product();
		$resources  = array();
		$product_id = null;

		// Trigger only for Bookings Products
		if ( is_a( $product, 'WC_Product_phive_booking' ) ) {

			$product_id              = $product->get_id();
			$product_id              = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product_id );        // WPML compatibilty
			$resources_pricing_rules = get_post_meta( $product_id, '_phive_booking_resources_pricing_rules', 1 );

			// Looping through the rule and assign the corresponding rule value given by customer
			foreach ( $resources_pricing_rules as $key => $rule ) {
				$resources[] = $rule['ph_booking_resources_name'];
			}
		} else {

			return $html;
		}

		if ( ! empty( $args['before'] ) && ! empty( $args['after'] ) && ! empty( $args['separator'] ) ) {
			$list_html = explode( $args['before'], $html );
			if ( isset( $list_html[1] ) && ! empty( $list_html[1] ) ) {
				$list_html   = $list_html[1];
				$ul_elements = explode( $args['after'], $list_html );
				$ul_elements = $ul_elements[0];
				if ( ! empty( $list_html ) && ! empty( $ul_elements ) ) {
					$li_elements = explode( $args['separator'], $ul_elements );
					$strings     = array();
					foreach ( $li_elements as $li_key => $li_value ) {
						$value_without_html = strip_tags( $li_value );
						$key_value_pair     = explode( ':', $value_without_html, 2 );

						// Temporarily skipping the total number of participants in emails for the German language (WPML-related issue)
						// This scenario will be properly handled in the next release
						if ('Gesamtzahl der Teilnehmer' === $key_value_pair[0]) {
							
							continue;
						}

						// No need to show the number of persons meta in emails
						if ( ( in_array( $key_value_pair[0], $resources ) && trim( $key_value_pair[1] ) == 'no' ) ||
							( $key_value_pair[0] == 'Number of persons' || $key_value_pair[0] == __( 'Number of persons', 'bookings-and-appointments-for-woocommerce' ) ) ||
							$key_value_pair[0] == __( 'Total Number of Participants', 'bookings-and-appointments-for-woocommerce' )
						) {
							continue;
						}

						// 154930 - "End Date and Time in Cart, Order Details and Emails" setting to apply for all emails.
						if ( ph_display_setting_booked_to_order_and_emails() == false ) {
							if ( $key_value_pair[0] == $this->booked_to_text || $key_value_pair[0] == __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' ) ) {
								continue;
							}
						}

						if ( ph_hide_asset_details_from_frontend( $product_id ) ) {

							$asset_label            = get_post_meta( $product_id, '_phive_booking_assets_label', 1 );
							$asset_label_translated = ph_wpml_translate_single_string( 'Assets_Main_Label', $asset_label );
							$asset_label_translated = ! empty( $asset_label ) ? __( $asset_label, 'bookings-and-appointments-for-woocommerce' ) : __( 'Asset', 'bookings-and-appointments-for-woocommerce' );

							if ( $key_value_pair[0] === $asset_label || $key_value_pair[0] === $asset_label_translated ) {
								continue;
							}
						}

						$meta_key   = __( $key_value_pair[0], 'bookings-and-appointments-for-woocommerce' );
						$meta_value = __( trim( $key_value_pair[1] ), 'bookings-and-appointments-for-woocommerce' );

						if ( strpos($li_value, '<a href=') !== false ) {

							// If any link is present in the meta value, simply update the translated label without rebuilding the entire tag.
							$strings[] = str_replace($key_value_pair[0], wp_kses_post($meta_key), $li_value);
						} else {

							// Building meta tag
							$strings[]  = $args['label_before'] . wp_kses_post( $meta_key ) . $args['label_after'] .'<p>' . $meta_value . '</p>';
						}
					}
					if ( $strings ) {
						$html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
					}
				}
			}
		}
		return $html;
	}

	public function phive_hide_details_from_booking_emails( $hidden_metas, $order_item = '' ) {

		$product_id = null;

		if ( ! empty( $order_item ) ) {

			$product    = $order_item->get_product();
			$product_id = $product->get_id();
			$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product_id );
		}
		// 118793 - End date/time is appearing in the admin email for translated products when using WPML Translation plugin.
		$sitepress_active_check = class_exists( 'SitePress' );

		if ( ! empty( $order_item ) && $sitepress_active_check ) {
			// Get Order Object
			$order = $order_item->get_order();

			// Switch To Order Language
			$current_language = ph_wpml_language_switch_admin_email( $order, '', $lang_basis = 'order' );

			$booked_to_key = __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' );

			// Switch Back To Current Language
			ph_wpml_language_switch_admin_email( $order, '', $lang_basis = 'current', $current_language );

			if ( ph_display_setting_booked_to_order_and_emails() == false ) {
				$hidden_metas[] = $booked_to_key;
			}
		}

		if ( ph_display_setting_booked_to_order_and_emails() == false ) {

			$hidden_metas[] = __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' );
		}

		if ( ! empty( $product_id ) && ph_hide_asset_details_from_frontend( $product_id ) ) {

			$asset_label            = get_post_meta( $product_id, '_phive_booking_assets_label', 1 );
			$asset_label_translated = ph_wpml_translate_single_string( 'Assets_Main_Label', $asset_label );
			$asset_label_translated = ! empty( $asset_label ) ? __( $asset_label, 'bookings-and-appointments-for-woocommerce' ) : __( 'Asset', 'bookings-and-appointments-for-woocommerce' );

			$hidden_metas[] = $asset_label_translated;

			if ( $asset_label !== $asset_label_translated ) {

				$hidden_metas[] = $asset_label;
			}
		}

		$hidden_metas[] = '_ph_booking_dlang_product_id';

		return apply_filters( 'ph_zoom_hide_order_item_hidden_metas', $hidden_metas );
	}

	/**
	 * Return the translation meta
	 *
	 * @param array  $meta
	 * @param object $order_item
	 * @return array $meta
	 */
	public function phive_hide_details_from_checkout_and_wc_emails( $meta, $order_item = '' ) {
		global $sitepress_active_check;

		if ( ! is_admin() && ! ph_display_setting_booked_to_order_and_emails() ) {

			$criteria = array( 'key' => __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' ) );
			$meta     = wp_list_filter( $meta, $criteria, 'NOT' );
		}

		// hide extra info
		$criteria = array( 'key' => '_ph_booking_dlang_product_id' );
		$meta     = wp_list_filter( $meta, $criteria, 'NOT' );

		// 103401 - Admin Email Issue
		if ( is_object( $order_item ) ) {

			$order   = $order_item->get_order();
			$product = is_callable( array( $order_item, 'get_product' ) ) ? $order_item->get_product() : false;

			$sitepress_active_check = class_exists( 'SitePress' );

			if ( ! empty( $order ) && ph_is_bookable_product( $product ) ) {

				$product_id = $product->get_id();

				$booked_dates = array(
					__( $this->booked_from_text, 'bookings-and-appointments-for-woocommerce' ),
					__( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' ),
				);

				if ( ! is_admin() && ph_hide_asset_details_from_frontend( $product_id ) ) {

					$asset_label = get_post_meta( $product_id, '_phive_booking_assets_label', 1 );
					$asset_label = ph_wpml_translate_single_string( 'Assets_Main_Label', $asset_label );
					$asset_label = ! empty( $asset_label ) ? __( $asset_label, 'bookings-and-appointments-for-woocommerce' ) : __( 'Asset', 'bookings-and-appointments-for-woocommerce' );
					$criteria    = array( 'key' => $asset_label );

					$meta = wp_list_filter( $meta, $criteria, 'NOT' );
				}

				$modified_meta_key_value = array();

				if ( $sitepress_active_check ) {

					$current_language = apply_filters( 'wpml_current_language', null );

					$wpml_lang = $order->get_meta( 'wpml_language' );

					if ( $current_language != $wpml_lang ) {

						// Get keys in order language to modify to admin language in email
						ph_wpml_language_switch_admin_email( $order, '', $lang_basis = 'order' );

						$booked_from_key    = __( $this->booked_from_text, 'bookings-and-appointments-for-woocommerce' );
						$booked_to_key      = __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' );
						$booking_status_key = __( 'Booking Status', 'bookings-and-appointments-for-woocommerce' );
						$months             = array(
							'January'   => __( 'January', 'bookings-and-appointments-for-woocommerce' ),
							'February'  => __( 'February', 'bookings-and-appointments-for-woocommerce' ),
							'March'     => __( 'March', 'bookings-and-appointments-for-woocommerce' ),
							'April'     => __( 'April', 'bookings-and-appointments-for-woocommerce' ),
							'May'       => __( 'May', 'bookings-and-appointments-for-woocommerce' ),
							'June'      => __( 'June', 'bookings-and-appointments-for-woocommerce' ),
							'July'      => __( 'July', 'bookings-and-appointments-for-woocommerce' ),
							'August'    => __( 'August', 'bookings-and-appointments-for-woocommerce' ),
							'September' => __( 'September', 'bookings-and-appointments-for-woocommerce' ),
							'October'   => __( 'October', 'bookings-and-appointments-for-woocommerce' ),
							'November'  => __( 'November', 'bookings-and-appointments-for-woocommerce' ),
							'December'  => __( 'December', 'bookings-and-appointments-for-woocommerce' ),
							'Jan'       => __( 'Jan', 'bookings-and-appointments-for-woocommerce' ),
							'Feb'       => __( 'Feb', 'bookings-and-appointments-for-woocommerce' ),
							'Mar'       => __( 'Mar', 'bookings-and-appointments-for-woocommerce' ),
							'Apr'       => __( 'Apr', 'bookings-and-appointments-for-woocommerce' ),
							'Jun'       => __( 'Jun', 'bookings-and-appointments-for-woocommerce' ),
							'Jul'       => __( 'Jul', 'bookings-and-appointments-for-woocommerce' ),
							'Aug'       => __( 'Aug', 'bookings-and-appointments-for-woocommerce' ),
							'Sep'       => __( 'Sep', 'bookings-and-appointments-for-woocommerce' ),
							'Oct'       => __( 'Oct', 'bookings-and-appointments-for-woocommerce' ),
							'Nov'       => __( 'Nov', 'bookings-and-appointments-for-woocommerce' ),
							'Dec'       => __( 'Dec', 'bookings-and-appointments-for-woocommerce' ),
						);

						// Switch Back To Current Language
						ph_wpml_language_switch_admin_email( $order, '', $lang_basis = 'current', $current_language );
					}
				}

				foreach ( $meta as $id => $meta_array ) {

					if ( $sitepress_active_check && $current_language != $wpml_lang ) {

						if ( $booked_from_key === $meta_array->key || $booked_to_key === $meta_array->key ) {

							if ( in_array( $meta_array->display_key, $booked_dates ) ) {
								// $meta_array->display_value = 'test';
								foreach ( $months as $key => $value ) {

									if ( strripos( $meta_array->display_value, $value ) ) {

										$key                       = __( $key, 'bookings-and-appointments-for-woocommerce' );
										$meta_array->display_value = str_ireplace( $value, $key, $meta_array->display_value );
										$modified_meta_key_value[] = $meta_array->display_value;
										break;
									}
								}
							}
						}
						if ( $booking_status_key == $meta_array->key ) {

							if ( $meta_array->display_key == __( 'Booking Status', 'bookings-and-appointments-for-woocommerce' ) ) {

								$item_id        = $order_item->get_id();
								$booking_status = wc_get_order_item_meta( $item_id, 'booking_status', 1 );

								if ( is_array( $booking_status ) && isset( $booking_status[0] ) ) {

									$booking_status            = $booking_status[0];
									$meta_array->display_value = ph_map_booking_status_to_name( $booking_status );
									$modified_meta_key_value[] = $meta_array->display_value;
								}
							}
						}

						if ( $meta_array->display_key == $booking_status_key ) {
							$meta_array->display_key   = __( 'Booking Status', 'bookings-and-appointments-for-woocommerce' );
							$modified_meta_key_value[] = $meta_array->display_key;
						}
						if ( $meta_array->display_key == $booked_from_key ) {
							$meta_array->display_key   = __( $this->booked_from_text, 'bookings-and-appointments-for-woocommerce' );
							$modified_meta_key_value[] = $meta_array->display_key;
						}
						if ( $meta_array->display_key == $booked_to_key ) {
							$meta_array->display_key   = __( $this->booked_to_text, 'bookings-and-appointments-for-woocommerce' );
							$modified_meta_key_value[] = $meta_array->display_key;
						}
					}
					if ( ! in_array( $meta_array->display_key, $modified_meta_key_value ) ) {
						$meta_array->display_key = __( $meta_array->display_key, 'bookings-and-appointments-for-woocommerce' );
					}

					if ( ! in_array( $meta_array->display_value, $modified_meta_key_value ) ) {
						$meta_array->display_value = __( $meta_array->display_value, 'bookings-and-appointments-for-woocommerce' );
					}
				}
			}
		}

		return $meta;
	}

	// 140472
	public function woocommerce_cart_needs_payment_requires_confirmation( $needs_payment, $cart ) {
		foreach ( $cart->get_cart() as $cart_item ) {
			$product_id = $cart_item['data']->get_id();
			$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $product_id );

			// For normal product it don't have phive_book_from_date
			if ( empty( $cart_item['phive_book_from_date'] ) ) {
				continue;
			}
			$required_confirmation = apply_filters( 'ph_booking_require_confirmation', get_post_meta( $product_id, '_phive_book_required_confirmation', 1 ), $cart_item['phive_book_from_date'] );
			if ( $required_confirmation == 'yes' ) {
				if ( is_cart() || is_checkout() ) {
					return true;
				}
			}
		}
		return $needs_payment;
	}
}
new phive_booking_checkout_decorator();
