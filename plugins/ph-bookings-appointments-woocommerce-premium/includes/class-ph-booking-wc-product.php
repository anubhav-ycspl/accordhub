<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WC_Product_phive_booking extends WC_Product {

	/**
	 * @vaar $booked_price
	 */
	private $booked_price = false;

	/**
	 * @var $product_type
	 */
	public $product_type;

	/**
	 * var $persons_count;
	 */
	public $persons_count;

	/**
	 * @var $product_properties
	 */
	public $product_properties;

	/**
	 * @var $product_price;
	 */
	public $product_price;

	/**
	 * @var $day_indicator
	 */
	public $day_indicator;

	/**
	 * @var $interval_type
	 */
	public $interval_type;

	/**
	 * @var $interval_period
	 */
	public $interval_period;

	/**
	 * @var $interval
	 */
	public $interval;

	/**
	 * @var $cancel_interval_period
	 */
	public $cancel_interval_period;

	/**
	 * @var $additional_notes_label
	 */
	public $additional_notes_label;

	/**
	 * @var $cancel_interval
	 */
	public $cancel_interval;

	/**
	 * @var $min_allowed_booking
	 */
	public $min_allowed_booking;

	/**
	 * @var $persons_as_booking
	 */
	public $persons_as_booking;

	/**
	 * @var $allowd_per_slot
	 */
	public $allowd_per_slot;

	/**
	 * @var $max_allowed_booking
	 */
	public $max_allowed_booking;

	/**
	 * @var $charge_per_night
	 */
	public $charge_per_night;

	/**
	 * @var $assets_id
	 */
	public $assets_id;

	/**
	 * @var $book_from
	 */
	public $book_from;

	/**
	 * @var
	 */
	public $book_to;

	/**
	 * @var $number_of_slots_selected
	 */
	public $number_of_slots_selected;

	/**
	 * @var $time_related_pricing_rules
	 */
	public $time_related_pricing_rules;

	/**
	 * @var $non_time_related_pricing_rules
	 */
	public $non_time_related_pricing_rules;

	/**
	 * @var $cost_per_unit
	 */
	public $cost_per_unit;

	/**
	 * @var $base_cost
	 */
	public $base_cost;

	/**
	 * @var $base_cost_added
	 */
	public $base_cost_added;

	/**
	 * @var $pricing_rules
	 */
	public $pricing_rules;

	/**
	 * @var $display_cost
	 */
	public $display_cost;

	/**
	 * @var $prod_interval
	 */
	public $prod_interval;

	/**
	 * @var $prod_cancel_interval
	 */
	public $prod_cancel_interval;

	/**
	 * @var $person_enabled
	 */
	public $person_enabled;

	/**
	 * @var $persons_multuply_all_cost
	 */
	public $persons_multuply_all_cost;

	/**
	 * @var $persons_pricing_rules
	 */
	public $persons_pricing_rules;

	/**
	 * @var $participant_pricing_rules
	 */
	public $participant_pricing_rules;

	/**
	 * @var $resources_enabled;
	 */
	public $resources_enabled;

	/**
	 * @var $resources_pricing_rules
	 */
	public $resources_pricing_rules;

	/**
	 * @var $resources_type
	 */
	public $resources_type;

	/**
	 * @var $asset_enabled
	 */
	public $asset_enabled;

	/**
	 * @var $assets_pricing_rules
	 */
	public $assets_pricing_rules;

	/**
	 * @var $assets_auto_assign
	 */
	public $assets_auto_assign;

	/**
	 * @var $buffer_before_time
	 */
	public $buffer_before_time;

	/**
	 * @var $buffer_after_time
	 */
	public $buffer_after_time;

	/**
	 * @var $buffer_period
	 */
	public $buffer_period;

	/**
	 * @var $phive_enable_buffer
	 */
	public $phive_enable_buffer;

	/**
	 * @var $shop_opening_time
	 */
	public $shop_opening_time;

	/**
	 * @var $shop_closing_time
	 */
	public $shop_closing_time;

	/**
	 * @var $buffer_added_interval
	 */
	public $buffer_added_interval;

	/**
	 * @var $interval_string
	 */
	public $interval_string;

	/**
	 * @var $across_the_day_cost_calculation
	 */
	public $across_the_day_cost_calculation;

	/**
	 * @var $interval_string_without_buffer
	 */
	public $interval_string_without_buffer;

	public function __construct( $product ) {
		$this->product_type  = 'phive_booking';
		$this->persons_count = 0;
		// WPML Compatibility
		$this->id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $this->id );
		parent::__construct( $product );

		$this->product_properties = get_post_meta( Ph_Bookings_General_Functions_Class::get_default_lang_product_id( $this->id ) );

		if ( ! empty( $this->product_properties['_booking_price'][0] ) ) {
			$this->product_price = $this->product_properties['_booking_price'][0];
		}

		$this->day_indicator = array(
			'1' => 'Sun',
			'2' => 'Mon',
			'3' => 'Tue',
			'4' => 'Wed',
			'5' => 'Thu',
			'6' => 'Fri',
			'7' => 'Sat',
		);
		
		add_filter( 'woocommerce_is_sold_individually', array( $this, 'hide_quantity_field' ), 10, 2 );

		// for display cost suffix
		// 110387 - Display cost suffix appearing along with WooCommerce Product Add-on Price
		add_filter( 'woocommerce_get_price_suffix', array( $this, 'ph_add_display_cost_suffix' ), 10, 2 );
	}

	public function ph_add_display_cost_suffix( $return = '', $product = '' ) {

		if ( ! empty( $product ) && is_a( $product, 'WC_Product_phive_booking' ) && ! is_cart() && ! is_checkout() && ! ph_is_ajax() ) {

			$display_cost = get_post_meta( $product->get_id(), '_phive_booking_pricing_display_cost', 1 );

			$display_cost_suffix = get_post_meta( $product->get_id(), '_phive_booking_pricing_display_cost_suffix', 1 );
			$display_cost_suffix = isset( $display_cost_suffix ) ? $display_cost_suffix : '';
			if ( ( ! empty( $display_cost ) || $display_cost == 0 ) && ! empty( $display_cost_suffix ) && ! strpos( $return, $display_cost_suffix ) ) {
				$display_cost_suffix = '<span class = "ph-bookings-display-cost-suffix">' . $display_cost_suffix . '</span>';
				$html                = explode( '<small class="woocommerce-price-suffix">', $return );
				if ( isset( $html[0] ) && isset( $html[1] ) ) {

					$html[0] = substr_replace( '<small class="woocommerce-price-suffix">', '&nbsp;' . $display_cost_suffix . '&nbsp;' . '<small class="woocommerce-price-suffix">', 0 );
					$return  = $html[0] . ' ' . $html[1];
				} else {
					$return = '&nbsp;' . $display_cost_suffix;
				}
				return $return;
			}
		} elseif ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'prdctfltr_respond_550' && isset( $product ) && is_object( $product ) ) {
			$display_cost = get_post_meta( $product->get_id(), '_phive_booking_pricing_display_cost', 1 );

			$display_cost_suffix = get_post_meta( $product->get_id(), '_phive_booking_pricing_display_cost_suffix', 1 );
			$display_cost_suffix = isset( $display_cost_suffix ) ? $display_cost_suffix : '';

			if ( ( ! empty( $display_cost ) || $display_cost == 0 ) && ! empty( $display_cost_suffix ) && ! strpos( $return, $display_cost_suffix ) ) {
				$display_cost_suffix = '<span class = "ph-bookings-display-cost-suffix">' . $display_cost_suffix . '</span>';
				$html                = explode( '<small class="woocommerce-price-suffix">', $return );
				if ( isset( $html[0] ) && isset( $html[1] ) ) {

					$html[0] = substr_replace( '<small class="woocommerce-price-suffix">', '&nbsp;' . $display_cost_suffix . '&nbsp;' . '<small class="woocommerce-price-suffix">', 0 );
					$return  = $html[0] . ' ' . $html[1];
				} else {
					$return = '&nbsp;' . $display_cost_suffix;
				}
				return $return;
			}
		}
		return $return;
	}

	/**
	 * Hide quantity field for booking products by marking them as sold individually.
	 *
	 * @param bool       $return  Current "sold individually" status.
	 * @param WC_Product $product Product object being checked.
	 * @return bool True to hide quantity field for booking products.
	 */
	public function hide_quantity_field($return, $product) {

		// Check if the product is a booking product.
		if ('phive_booking' === $product->get_type()) {

			return true;
		}

		return $return;
	}

	public function get_interval_type() {
		if ( empty( $this->interval_type ) ) {
			$this->interval_type = get_post_meta( $this->id, '_phive_book_interval_type', 1 );
		}
		return $this->interval_type;
	}

	public function get_interval_period() {
		if ( empty( $this->interval_period ) ) {
			$this->interval_period = get_post_meta( $this->id, '_phive_book_interval_period', 1 );
		}
		return $this->interval_period;
	}

	public function get_interval() {
		if ( empty( $this->interval ) ) {
			$this->interval = get_post_meta( $this->id, '_phive_book_interval', 1 );
		}
		return $this->interval;
	}

	public function get_cancel_interval_period() {
		if ( empty( $this->cancel_interval_period ) ) {
			$this->cancel_interval_period = get_post_meta( $this->id, '_phive_cancel_interval_period', 1 );
		}
		return $this->cancel_interval_period;
	}

	public function get_additional_notes_label() {
		if ( empty( $this->additional_notes_label ) ) {
			$this->additional_notes_label = get_post_meta( $this->id, '_phive_additional_notes_label', 1 );
		}
		return $this->additional_notes_label;
	}

	public function get_cancel_interval() {
		if ( empty( $this->cancel_interval ) ) {
			$this->cancel_interval = get_post_meta( $this->id, '_phive_cancel_interval', 1 );
		}
		return $this->cancel_interval;
	}

	public function get_min_allowed_booking() {
		if ( empty( $this->min_allowed_booking ) ) {
			$this->min_allowed_booking = get_post_meta( $this->id, '_phive_book_min_allowed_booking', 1 );
		}
		return $this->min_allowed_booking;
	}

	public function get_persons_as_booking() {
		if ( empty( $this->persons_as_booking ) ) {
			$this->persons_as_booking = ! empty( $this->product_properties['_phive_booking_persons_as_booking'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_persons_as_booking'][0] ) : '';
		}
		return $this->persons_as_booking;
	}

	public function get_allowd_per_slot() {
		if ( empty( $this->allowd_per_slot ) ) {
			$this->allowd_per_slot = ! empty( $this->product_properties['_phive_book_allowed_per_slot'][0] ) ? maybe_unserialize( $this->product_properties['_phive_book_allowed_per_slot'][0] ) : array();
		}
		return $this->allowd_per_slot;
	}

	public function get_max_allowed_booking() {
		if ( empty( $this->max_allowed_booking ) ) {
			$this->max_allowed_booking = get_post_meta( $this->id, '_phive_book_max_allowed_booking', 1 );
		}
		return $this->max_allowed_booking;
	}

	public function get_charge_per_night() {
		if ( empty( $this->charge_per_night ) ) {
			$this->charge_per_night = get_post_meta( $this->id, '_phive_book_charge_per_night', 1 );
		}
		return $this->charge_per_night;
	}

	public function phive_set_booked_price( $id, $price ) {
		$this->booked_price = $price;
	}

	public function set_id( $id ) {
		$this->id = $id;
		// $this->id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id($id);
	}

	public function ph_set_asset_id( $assets_id ) {
		$this->assets_id = $assets_id;
	}

	/**
	 * Return the booking price
	 *
	 * @param $context optional
	 * @param $booking_data optional
	 * @return booking_price
	 */
	public function get_price( $context = 'view', $booking_data = array() ) {

		global $woocommerce;

		// If already booked (called from cart/checkout page)
		if ( $this->booked_price > 0 ) {

			if ( is_cart() || is_checkout() ) {

				return apply_filters( 'ph_bookings_currency_conversion_compact', $this->booked_price, $this->id );
			} else {
				return $this->booked_price;
			}
		}

		// WPML Compatibilty
		$currency = ! empty( $woocommerce->session->client_currency ) ? $woocommerce->session->client_currency : get_woocommerce_currency();

		// 194389 - issue:- currency not converting to wpml currency in product page
		$currency = apply_filters( 'ph_bookings_get_client_currency', $currency );

		$this->set_product_pricing_properties();

		$customer_choosen_values = ( ph_is_ajax() ) ? ( ( isset( WC()->session ) && ! empty( WC()->session ) ) ? WC()->session->get( 'phive_booking_details' ) : array() ) : array();

		// 147163
		if ( isset( $booking_data[ $this->id ] ) && ! empty( $booking_data[ $this->id ] ) ) {
			$customer_choosen_values[ $this->id ] = $booking_data[ $this->id ];
		}
		// 147163 END

		$this->book_from = ! empty( $customer_choosen_values[ $this->id ]['book_from'] ) ? strtotime( $customer_choosen_values[ $this->id ]['book_from'] ) : '';
		$this->book_to   = ! empty( $customer_choosen_values[ $this->id ]['book_to'] ) ? strtotime( $customer_choosen_values[ $this->id ]['book_to'] ) : '';

		// If charge_per_night, remove last slot
		if ( $this->charge_per_night == 'yes' && $this->interval_period == 'day' && $this->book_to > $this->book_from ) {
			$this->book_to -= $this->get_interval_in_seconds();
		}
		if ( empty( $customer_choosen_values[ $this->id ] ) ) {
			// The funciton get_price() will get called from cart only if the Booked Price is zero.
			if ( is_cart() || is_checkout() || ph_is_ajax() ) {
				if ( is_cart() ) {
					if ( function_exists( 'WC' ) ) {
						$cart_contents = WC()->cart->get_cart();
						$cart_products = array();
						foreach ( $cart_contents as $cart_item => $values ) {
							$cart_products[] = $values['data']->get_id();
						}
					}
					$current_product_id = $this->get_id();
					if ( ! in_array( $current_product_id, $cart_products ) ) {
						return apply_filters( 'wcml_raw_price_amount', $this->display_cost, $currency );
					}
				}

				// Product Filter for WooCommerce Compatibility
				if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'prdctfltr_respond_550' ) {
					return apply_filters( 'wcml_raw_price_amount', $this->display_cost, $currency );
				}

				if ( $this->booked_price == false ) {
					// 129535 - Issue - Quick view option for bookable products with the theme "Atomion" is not working and its showing as 0 where they have set the Booking cost.
					if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'atomion_qv_get_product' ) {
						return apply_filters( 'wcml_raw_price_amount', $this->display_cost, $currency );
					}

					// ticket 113419 -Display cost is not working when activating the "WooCommerce One Page Checkout" plugin.
					if ( class_exists( 'PP_One_Page_Checkout' ) ) {
						if ( is_cart() ) {
							return apply_filters( 'wcml_raw_price_amount', 0, $currency );
						}
						return apply_filters( 'wcml_raw_price_amount', $this->display_cost, $currency );
					}
					return apply_filters( 'wcml_raw_price_amount', 0, $currency );        // WPML Currency Compatibility
				}
				return apply_filters( 'wcml_raw_price_amount', $this->booked_price, $currency );      // WPML Currency Compatibility
			} else {
				return apply_filters( 'wcml_raw_price_amount', $this->display_cost, $currency );      // WPML Currency Compatibility
			}
		}
		$this->number_of_slots_selected = $this->get_number_of_slots_selected();
		$bookings_price                 = $this->phive_calculate_rule_price();
		$persons_applied_price          = $this->phive_apply_persons_price( $bookings_price, $customer_choosen_values );
		$resources_applied_price        = $this->phive_apply_resources_price( $persons_applied_price, $customer_choosen_values );
		$asset_applied_price            = $this->phive_apply_asset_price( $resources_applied_price, $customer_choosen_values );
		$final_price                    = apply_filters( 'wcml_raw_price_amount', apply_filters( 'phive_booking_cost', $asset_applied_price, $this->id, $customer_choosen_values[ $this->id ], $booking_data ), $currency );        // WPML Currency Compatibility
		$this->set_price( $final_price );

		// 147163
		if ( isset( $booking_data[ $this->id ] ) && ! empty( $booking_data[ $this->id ] ) ) {
			return $final_price;
		}
		// 147163 END
	}

	public function set_price( $price ) {
		// $currency_converted_price    = apply_filters( 'ph_bookings_currency_conversion', $price, $this->id);

		// $this->set_prop( 'price', wc_format_decimal( $currency_converted_price ) );
		$this->set_prop( 'price', $price );
		$this->booked_price = $price;
		// $this->booked_price = $currency_converted_price;
	}

	public function set_regular_price( $price ) {
		$this->set_prop( 'regular_price', wc_format_decimal( 0 ) );
	}

	/**
	 * Return booking price in html format
	 *
	 * @param $currency optional
	 * @return booking_price
	 */
	public function get_price_html( $currency = '' ) {

		// 194389 - issue:- currency not converting to wpml currency in product page
		$currency = apply_filters( 'ph_bookings_get_client_currency', $currency );
		if ( '' === $this->get_price() ) {
			$price = apply_filters( 'woocommerce_empty_price_html', '', $this );
		} else {
			$price = wc_get_price_to_display( $this );
			$price = apply_filters( 'ph_bookings_currency_conversion', $price, $this->id );
			$price = wc_price( $price, array( 'currency' => $currency ) ) . $this->get_price_suffix();
		}
		return apply_filters( 'woocommerce_get_price_html', $price, $this );
	}



	/**
	 * Get the add to cart button text
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_text() {
		$display_settings   = get_option( 'ph_bookings_display_settigns' );
		$text_customisation = isset( $display_settings['text_customisation'] ) ? $display_settings['text_customisation'] : array();
		$book_now_button    = isset( $text_customisation['book_now_button'] ) && ! empty( $text_customisation['book_now_button'] ) ? $text_customisation['book_now_button'] : 'Book Now';
		$book_now_button    = ph_wpml_translate_single_string( 'text_customisation_book_now_button', $book_now_button );

		$text = __( $book_now_button, 'bookings-and-appointments-for-woocommerce' );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Get the add to cart button text for the single page
	 *
	 * @access public
	 * @return string
	 */
	public function single_add_to_cart_text() {
		return apply_filters( 'woocommerce_product_single_add_to_cart_text', self::add_to_cart_text(), $this );
	}

	public function is_on_sale( $context = 'view' ) {
		return false;
	}


	/**
	 * Classify all the pricing rule in to two group, time related and non-time related.
	 *
	 * @param null
	 * @return null
	 */
	private function segregate_pricing_rules() {

		$time_ralated     = array( 'custom', 'months', 'days', 'strict_days', 'time-all', 'time-mon', 'time-tue', 'time-wed', 'time-thu', 'time-fri', 'time-sat', 'time-sun' );
		$non_time_related = array( 'slot_based' );

		$this->time_related_pricing_rules     = array();
		$this->non_time_related_pricing_rules = array();
		foreach ( $this->pricing_rules as $key => $rule ) {

			if ( in_array( $rule['pricing_type'], $time_ralated ) ) {
				$this->time_related_pricing_rules[] = $rule;
			} else {
				$this->non_time_related_pricing_rules[] = $rule;
			}
		}
	}

	private function get_number_of_slots_selected() {

		// $divider = $this->get_interval_in_seconds();
		// $datediff = (int)$this->book_to - (int)$this->book_from;
		// $diff = round( ($datediff / $divider) + 1);
		// $interval_period =   $this->get_interval_period();
		// $interval = $this->get_interval();
		// if ($interval_period == 'day')
		// {
		// 42926 cost per block need to be applied to the block and not the days
		// $diff = round($diff/$interval);
		// }

		// The Number of slots selected calculation
		$slot_found    = 0;
		$book_from     = $this->book_from;
		$day_crossed   = 'no';
		$book_from_day = date( 'd', $book_from );
		while ( ! empty( $book_from ) && $book_from <= $this->book_to ) {

			// Cost calculation for across day booking based on user settings
			if ( ( $this->interval_period == 'hour' || $this->interval_period == 'minute' ) ) {

				// This code helps to skip invisible slot between across days for cost calculation
				if ( $this->across_the_day_cost_calculation != 'include' && ! $this->is_working_time( $book_from ) ) {

					$book_from_day = date( 'd', $book_from );
					$book_from     = strtotime( "+$this->interval_string", $book_from );

					if ( date( 'd', $book_from ) != $book_from_day ) {

						if ( ! empty( $this->shop_opening_time ) ) {

							$book_from = strtotime( date( "Y-m-d $this->shop_opening_time", $book_from ) );
						} else {
							$book_from = strtotime( date( 'Y-m-d 00:00', $book_from ) );
						}
					}
					continue;
				} elseif (
					$this->across_the_day_cost_calculation == 'include' &&
					date( 'd', $book_from ) != date( 'd', strtotime( "+$this->interval_string_without_buffer", $book_from ) ) &&
					'00:00' != date( 'H:i', strtotime( "+$this->interval_string", $book_from ) )
				) {
					$book_from   = strtotime( "+$this->interval_string", $book_from );
					$book_from   = strtotime( date( 'Y-m-d 00:00', $book_from ) );
					$day_crossed = 'yes';
					continue;
				}
				if (
					$day_crossed == 'yes' && ! empty( $this->shop_opening_time ) &&
					$this->shop_opening_time <= date( 'H:i', strtotime( "+$this->interval_string", $book_from ) )
				) {
					// when shop opening time less than next slot time assign shop opening time
					if ( $this->shop_opening_time < date( 'H:i', strtotime( "+$this->interval_string", $book_from ) ) ) {
						$book_from = strtotime( date( "Y-m-d $this->shop_opening_time", $book_from ) );
					}
					$day_crossed = 'no';
				}
			}
			$book_from_day  = date( 'd', $book_from );
			$prev_book_from = $book_from;
			$book_from      = strtotime( "+$this->interval_string", $book_from );

			if ( date( 'd', $book_from ) != $book_from_day ) {

				if ( date( 'd', $prev_book_from ) == date( 'd', strtotime( "+$this->interval_string_without_buffer", $prev_book_from ) ) ) {

					$book_from = strtotime( "+$this->interval_string_without_buffer", $prev_book_from );
				} else {
					$day_crossed = 'yes';
				}
			}

			// This code helps to skip invisible slot between across days for cost calculation
			if ( ( $this->interval_period == 'hour' || $this->interval_period == 'minute' ) && date( 'd', $book_from ) != $book_from_day && $this->across_the_day_cost_calculation != 'include' ) {

				if ( ! empty( $this->shop_opening_time ) ) {

					$book_from = strtotime( date( "Y-m-d $this->shop_opening_time", $book_from ) );
				} else {
					$book_from = strtotime( date( 'Y-m-d 00:00', $book_from ) );
				}
			}
			++$slot_found;
		}
		// 121279 - JOB3 = Ability to create a custom holiday and allow the customers to book across this custom holiday.
		$slot_found = apply_filters( 'ph_bookings_modify_number_of_slots_selected_count', $slot_found, $this->id, $this->book_from, $this->book_to );
		return $slot_found;
	}

	private function get_interval_in_seconds() {
		$interval = 0;
		switch ( $this->interval_period ) {
			case 'minute':
				$interval = 60 * $this->buffer_added_interval;
				break;

			case 'hour':
				$interval = 60 * 60 * $this->buffer_added_interval;
				break;

			case 'day':
				$interval = 60 * 60 * 24;
				break;

			case 'month':
				$interval = 60 * 60 * 24 * 30;
				break;
		}
		return $interval;
	}

	private function calculate_rule_price_of_the_block( $start_time, $interval, $book_from, $book_to ) {

		/*
		if( !empty($interval) )
			$end_time = strtotime( "+$interval", $start_time );
		else*/
		$end_time = $start_time;

		if ( ! empty( $this->time_related_pricing_rules ) && ! empty( $start_time ) ) {

			foreach ( $this->time_related_pricing_rules as $key => $rule ) {

				// ticket 111845 Decimal separator is "," in WooCommerce settings. Unable to use the same separator for Discount or Special Price in booking costs tab
				$rule['cost_per_unit'] = ph_wc_format_decimal( $rule['cost_per_unit'] );
				$rule['base_cost']     = ph_wc_format_decimal( $rule['base_cost'] );

				$base_cost = false;
				$unit_cost = 0;
				if ( $rule['pricing_type'] == 'custom' ) {

					// for time calendar rule should apply till 23:59
					if ( $this->interval_period == 'hour' || $this->interval_period == 'minute' ) {
						$rule['to_date'] = date( 'Y-m-d 23:59', strtotime( $rule['to_date'] ) );
					}
					if (
						! empty( $rule['from_date'] ) && ! empty( $rule['to_date'] )
						&& $start_time >= strtotime( $rule['from_date'] )             // Start day should be begining of the day i.e 12:00 midnight
						&& $start_time <= strtotime( $rule['to_date'] )
					) {

						// Find the unit cost
						if ( ! empty( $rule['cost_per_unit'] ) && is_numeric( $rule['cost_per_unit'] ) ) {
							$unit_cost = $this->perform_cost_rule_operation( $this->cost_per_unit, $rule['cost_per_unit'], $rule['perunit_operator'] );
						} else {
							$unit_cost = $this->cost_per_unit;
						}

						// Find the base cost
						if ( ! empty( $rule['base_cost'] ) && is_numeric( $rule['base_cost'] ) ) {
							$base_cost = $this->perform_cost_rule_operation( $this->base_cost, $rule['base_cost'], $rule['basecost_operator'] );
						}

						return array(
							'base_cost'     => $base_cost,
							'cost_per_unit' => $unit_cost,
						);
					}
				} elseif ( $rule['pricing_type'] == 'months' && ! empty( $rule['from_month'] ) ) {

					$range_arr = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );
					if (
						$this->is_in_range( $range_arr, date( 'n', $start_time ), $rule['from_month'], $rule['to_month'] )
						&& $this->is_in_range( $range_arr, date( 'n', $end_time ), $rule['from_month'], $rule['to_month'] )
					) {

						// Find the unit cost
						if ( ! empty( $rule['cost_per_unit'] ) && is_numeric( $rule['cost_per_unit'] ) ) {
							$unit_cost = $this->perform_cost_rule_operation( $this->cost_per_unit, $rule['cost_per_unit'], $rule['perunit_operator'] );
						} else {
							$unit_cost = $this->cost_per_unit;
						}

						// Find the base cost
						if ( ! empty( $rule['base_cost'] ) && is_numeric( $rule['base_cost'] ) ) {
							$base_cost = $this->perform_cost_rule_operation( $this->base_cost, $rule['base_cost'], $rule['basecost_operator'] );
						}

						return array(
							'base_cost'     => $base_cost,
							'cost_per_unit' => $unit_cost,
						);
					}
				} elseif ( $rule['pricing_type'] == 'days' && ! empty( $rule['from_week_day'] ) ) {

					$range_arr = array( 1, 2, 3, 4, 5, 6, 7 );
					if (
						$this->is_in_range( $range_arr, date( 'N', $start_time ), $rule['from_week_day'], $rule['to_week_day'] )
						&& $this->is_in_range( $range_arr, date( 'N', $end_time ), $rule['from_week_day'], $rule['to_week_day'] )
					) {
						$rule = apply_filters( 'ph_bookings_booking_cost_based_on_days', $rule, $start_time, $interval, $book_from, $book_to, $this->id );
						// Find the unit cost
						if ( ! empty( $rule['cost_per_unit'] ) && is_numeric( $rule['cost_per_unit'] ) ) {
							$unit_cost = $this->perform_cost_rule_operation( $this->cost_per_unit, $rule['cost_per_unit'], $rule['perunit_operator'] );
						} else {
							$unit_cost = $this->cost_per_unit;
						}

						// Find the base cost
						if ( ! empty( $rule['base_cost'] ) && is_numeric( $rule['base_cost'] ) ) {
							$base_cost = $this->perform_cost_rule_operation( $this->base_cost, $rule['base_cost'], $rule['basecost_operator'] );
						}

						return array(
							'base_cost'     => $base_cost,
							'cost_per_unit' => $unit_cost,
						);
					}
				} elseif ( $rule['pricing_type'] == 'strict_days' && ! empty( $rule['from_week_day'] ) ) {

					if ( date( 'N', $book_from ) == $rule['from_week_day'] && date( 'N', $book_to ) == $rule['to_week_day'] && ( date( 'd', $book_from ) + 7 ) >= date( 'd', $book_to ) ) {
						// Find the unit cost

						if ( ! empty( $rule['cost_per_unit'] ) && is_numeric( $rule['cost_per_unit'] ) ) {
							$unit_cost = $this->perform_cost_rule_operation( $this->cost_per_unit, $rule['cost_per_unit'], $rule['perunit_operator'] );
						} else {
							$unit_cost = $this->cost_per_unit;
						}

						// Find the base cost
						if ( ! empty( $rule['base_cost'] ) && is_numeric( $rule['base_cost'] ) ) {
							$base_cost = $this->perform_cost_rule_operation( $this->base_cost, $rule['base_cost'], $rule['basecost_operator'] );
						}

						return array(
							'base_cost'     => $base_cost,
							'cost_per_unit' => $unit_cost,
						);
					}
				} elseif ( $rule['pricing_type'] == 'time-all' && ! empty( $rule['from_time'] ) ) {

					if (
						strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $start_time ) ) <= strtotime( $rule['to_time'] )
						&& strtotime( date( 'H:i', $end_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['to_time'] )
					) {

						// Find the unit cost
						if ( ! empty( $rule['cost_per_unit'] ) && is_numeric( $rule['cost_per_unit'] ) ) {
							$unit_cost = $this->perform_cost_rule_operation( $this->cost_per_unit, $rule['cost_per_unit'], $rule['perunit_operator'] );
						} else {
							$unit_cost = $this->cost_per_unit;
						}

						// Find the base cost
						if ( ! empty( $rule['base_cost'] ) && is_numeric( $rule['base_cost'] ) ) {
							$base_cost = $this->perform_cost_rule_operation( $this->base_cost, $rule['base_cost'], $rule['basecost_operator'] );
						}

						return array(
							'base_cost'     => $base_cost,
							'cost_per_unit' => $unit_cost,
						);
					}
				} elseif ( strpos( $rule['pricing_type'], 'time-' ) !== false ) {

					$day = explode( '-', $rule['pricing_type'] );
					$day = $day[1];
					if (
						strtolower( date( 'D', $start_time ) ) == $day
						&& strtotime( date( 'H:i', $start_time ) ) >= strtotime( $rule['from_time'] )
						&& ( strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['to_time'] ) || empty( $rule['to_time'] ) )
					) {

						// Find the unit cost
						if ( ! empty( $rule['cost_per_unit'] ) && is_numeric( $rule['cost_per_unit'] ) ) {
							$unit_cost = $this->perform_cost_rule_operation( $this->cost_per_unit, $rule['cost_per_unit'], $rule['perunit_operator'] );
						} else {
							$unit_cost = $this->cost_per_unit;
						}

						// Find the base cost
						if ( ! empty( $rule['base_cost'] ) && is_numeric( $rule['base_cost'] ) ) {
							$base_cost = $this->perform_cost_rule_operation( $this->base_cost, $rule['base_cost'], $rule['basecost_operator'] );
						}

						return array(
							'base_cost'     => $base_cost,
							'cost_per_unit' => $unit_cost,
						);
					}
				}
			}
		}

		return array(
			'base_cost'     => false,
			'cost_per_unit' => $this->cost_per_unit,
		);
	}

	private function perform_cost_rule_operation( $cost1 = '', $cost2 = '', $operation = '' ) {
		$cost1 = ! empty( $cost1 ) ? ph_wc_format_decimal( $cost1 ) : '';
		$cost2 = ! empty( $cost2 ) ? ph_wc_format_decimal( $cost2 ) : '';

		switch ( $operation ) {

			case 'add':
				$cost1  = empty( $cost1 ) ? 0 : $cost1;
				$cost2  = empty( $cost2 ) ? 0 : $cost2;
				$result = $cost1 + $cost2;
				break;

			case 'sub':
				$cost1  = empty( $cost1 ) ? 0 : $cost1;
				$cost2  = empty( $cost2 ) ? 0 : $cost2;
				$result = $cost1 - $cost2;
				break;

			case 'mul':
				$cost1  = empty( $cost1 ) ? 1 : $cost1;
				$cost2  = empty( $cost2 ) ? 1 : $cost2;
				$result = $cost1 * $cost2;
				break;

			case 'div':
				if ( empty( $cost1 ) || empty( $cost2 ) ) {
					$result = 0;
				} else {
					$result = $cost1 / $cost2;
				}
				break;

			default:
				$cost1  = empty( $cost1 ) ? 0 : $cost1;
				$cost2  = empty( $cost2 ) ? 0 : $cost2;
				$result = $cost1 + $cost2;
				break;
		}
		return $result;
	}


	/**
	 * Cacluate the price for participants
	 *
	 * @param float $price
	 * @param array $customer_choosen_values
	 * @return float $price
	 */
	private function phive_apply_persons_price( $price, $customer_choosen_values = '' ) {
		$person_details  = apply_filters( 'phive_booking_total_person_details', ! empty( $customer_choosen_values[ $this->id ]['persons_details'] ) ? $customer_choosen_values[ $this->id ]['persons_details'] : array(), $this->id );
		$booking_details = ! empty( $customer_choosen_values[ $this->id ] ) ? $customer_choosen_values[ $this->id ] : array();
		$person_count    = array_sum( $person_details );
		if ( empty( $person_details ) ) {
			return $price;
		}
		if ( $this->person_enabled != 'yes' ) {
			return $price;
		}

		$number_of_slots = $this->number_of_slots_selected;
		$number_of_slots = apply_filters( 'ph_change_number_of_slot_before_person_price_calculation', $number_of_slots, $this->id );

		$persons_count = 0;

		if ( ! empty( $this->persons_pricing_rules ) ) {
			$persons_price              = 0;
			$total_base_cost            = 0;
			$total_cost_per_participant = 0;

			$total_base_cost_date_rule            = 0;
			$total_cost_per_participant_date_rule = 0;

			$total_base_cost_block_count_rule            = 0;
			$total_cost_per_participant_block_count_rule = 0;

			$total_participant_rules_for_date          = array();
			$total_participant_rules_block_count       = array();
			$found_total_participant_rules             = 0;
			$found_total_participant_rules_date        = 0;
			$found_total_participant_rules_block_count = 0;
			if ( ! empty( $this->participant_pricing_rules ) ) {
				foreach ( $this->participant_pricing_rules as $keys => $rules ) {
					// participant rule for total count of participant
					// checking empty to make previous version compatibility
					if ( ( $rules['pricing_from_participant'] <= $person_count ) && ( $rules['pricing_to_participant'] >= $person_count ) && ( $rules['ph_booking_participant_rule_type'] == 'participant_based' || ( empty( $rules['ph_booking_participant_rule_type'] ) ) && $rules['ph_booking_participant_rule_type'] != 0 ) ) {
						++$found_total_participant_rules;
						// participant rule for base cost
						$total_participant_rules = $rules;
						break;
					}
				}
				// participant rules for custom date range and week days
				if ( ! empty( $booking_details['book_from'] ) && isset( $booking_details['book_to'] ) && ! empty( $booking_details['book_to'] ) ) {
					$book_from = date( 'Y-m-d', strtotime( str_replace( '/', '-', $booking_details['book_from'] ) ) );
					$book_to   = date( 'Y-m-d', strtotime( str_replace( '/', '-', $booking_details['book_to'] ) ) );
					if ( $this->charge_per_night == 'yes' && $this->interval_period == 'day' ) {
						$book_to = date( 'Y-m-d', strtotime( '-1 day', strtotime( $book_to ) ) );
					}
					$choosen_date_ranges               = array();
					$choosen_date_ranges[ $book_from ] = $book_from;
					$choosen_day_ranges                = array();
					$choosen_day_ranges[ $book_from ]  = date( 'D', strtotime( $book_from ) );
					while ( $book_from < $book_to ) {
						$book_from                         = date( 'Y-m-d', strtotime( '+1 day', strtotime( $book_from ) ) );
						$choosen_date_ranges[ $book_from ] = $book_from;
						$choosen_day_ranges[ $book_from ]  = date( 'D', strtotime( $book_from ) );
					}
					$matched_dates                   = array();
					$matched_slots                   = 0;
					$matched_slots_for_time          = ( $this->interval_period == 'hour' || $this->interval_period == 'minute' ) ? true : false;
					$total_participant_rules_applied = '';
					foreach ( $this->participant_pricing_rules as $keys => $rules ) {
						if ( ( $rules['pricing_from_participant'] <= $person_count ) && ( $rules['pricing_to_participant'] >= $person_count ) && ( ! empty( $rules['pricing_from_participant_date_from'] ) || ! empty( $rules['pricing_from_participant_day_from'] ) ) && ( ! empty( $rules['pricing_from_participant_date_to'] ) || ! empty( $rules['pricing_from_participant_day_to'] ) ) ) {
							$rule_book_from     = date( 'Y-m-d', strtotime( str_replace( '/', '-', $rules['pricing_from_participant_date_from'] ) ) );
							$rule_book_to       = date( 'Y-m-d', strtotime( str_replace( '/', '-', $rules['pricing_from_participant_date_to'] ) ) );
							$rule_date_ranges   = array();
							$rule_date_ranges[] = $rule_book_from;
							while ( $rule_book_from < $rule_book_to ) {
								$rule_book_from     = date( 'Y-m-d', strtotime( '+1 day', strtotime( $rule_book_from ) ) );
								$rule_date_ranges[] = $rule_book_from;
							}

							$from       = $rules['pricing_from_participant_day_from'];
							$to         = $rules['pricing_from_participant_day_to'];
							$day_ranges = array();
							if ( $from > $to ) {
								while ( $from <= 7 ) {
									$day_ranges[] = $this->day_indicator[ $from ];
									$from++;
								}
								$from = 1;
								while ( $from <= $to ) {
									$day_ranges[] = $this->day_indicator[ $from ];
									$from++;
								}
							} else {
								while ( $from <= $to ) {
									$day_ranges[] = $this->day_indicator[ $from ];
									$from++;
								}
							}
							if ( ( $rules['ph_booking_participant_rule_type'] == 'participant_based_custom_date' ) && $this->get_matched_dates_from_choosen_dates( $choosen_date_ranges, $rule_date_ranges, $matched_dates, true ) > 0 ) {

								$new_matched_dates = $this->get_matched_dates_from_choosen_dates( $choosen_date_ranges, $rule_date_ranges, $matched_dates );

								if ( $matched_slots_for_time ) {

									$matched_slots = $this->get_number_of_slots_in_matched_days( $new_matched_dates, $booking_details['book_from'], $booking_details['book_to'] );
								}
								++$found_total_participant_rules_date;
								$total_participant_rules_applied    = 'participant_based_custom_date';
								$rules['matched_slots']             = $matched_slots > 0 ? $matched_slots : $this->get_matched_dates_from_choosen_dates( $choosen_date_ranges, $rule_date_ranges, $matched_dates, true );
								$total_participant_rules_for_date[] = $rules;
								$matched_dates                      = array_merge( $matched_dates, $new_matched_dates );
							} elseif ( ( $rules['ph_booking_participant_rule_type'] == 'participant_based_week_day' ) && $this->get_matched_dates_from_choosen_dates( $choosen_day_ranges, $day_ranges, $matched_dates, true ) > 0 ) {

								$new_matched_dates = $this->get_matched_dates_from_choosen_dates( $choosen_day_ranges, $day_ranges, $matched_dates );

								if ( $matched_slots_for_time ) {
									$matched_slots = $this->get_number_of_slots_in_matched_days( $new_matched_dates, $booking_details['book_from'], $booking_details['book_to'] );
								}
								++$found_total_participant_rules_date;
								$total_participant_rules_applied    = 'participant_based_week_day';
								$rules['matched_slots']             = $matched_slots > 0 ? $matched_slots : $this->get_matched_dates_from_choosen_dates( $choosen_day_ranges, $day_ranges, $matched_dates, true );
								$total_participant_rules_for_date[] = $rules;
								$matched_dates                      = array_merge( $matched_dates, $new_matched_dates );
							} elseif ( $rules['ph_booking_participant_rule_type'] == 'participant_based' && $found_total_participant_rules != 0 ) {
								break;
							}
						}
					}
					if ( $matched_slots > 0 ) {
						$number_of_slots -= $matched_slots;
					} else {
						$number_of_slots -= count( $matched_dates );
					}
				}
				foreach ( $this->participant_pricing_rules as $keys => $rules ) {
					// participant rule for total count of participant
					// checking empty to make previous version compatibility
					if ( ( $rules['pricing_from_participant'] <= $person_count ) && ( $rules['pricing_to_participant'] >= $person_count ) && ( $rules['ph_booking_participant_rule_type'] == 'participant_based_block_count' ) && isset( $rules['pricing_from_participant_block_count'] ) && $number_of_slots >= $rules['pricing_from_participant_block_count'] && isset( $rules['pricing_to_participant_block_count'] ) && $number_of_slots <= $rules['pricing_to_participant_block_count'] ) {
						++$found_total_participant_rules_block_count;
						// participant rule for base cost
						$total_participant_rules_block_count = $rules;
						break;
					}
				}
			}

			foreach ( $this->persons_pricing_rules as $key => $rule ) {
				if ( empty( $person_details[ $key ] ) ) {
					continue;
				}

				$persons_count += $person_details[ $key ];
				if ( $found_total_participant_rules != 0 ) {
					$total_base_cost                += ! empty( $rule['ph_booking_persons_rule_base_cost'] ) ? ph_wc_format_decimal( $rule['ph_booking_persons_rule_base_cost'] ) : 0;
					$individual_cost_per_participant = ! empty( $rule['ph_booking_persons_rule_cost_per_unit'] ) ? ph_wc_format_decimal( $rule['ph_booking_persons_rule_cost_per_unit'] ) : 0;
					$individual_cost_per_participant = $this->perform_cost_rule_operation( $individual_cost_per_participant, $total_participant_rules['ph_booking_rule_cost_per_participant'], $total_participant_rules['perparticipant_operator'] );
					$total_cost_per_participant     += $this->calculate_participant_cost( 0, $individual_cost_per_participant, $number_of_slots, $person_details[ $key ], $rule['ph_booking_persons_per_slot'] );
				}
				if ( $found_total_participant_rules_date != 0 && ! empty( $total_participant_rules_for_date ) ) {
					$total_base_cost_date_rule += ! empty( $rule['ph_booking_persons_rule_base_cost'] ) ? ph_wc_format_decimal( $rule['ph_booking_persons_rule_base_cost'] ) : 0;

					foreach ( $total_participant_rules_for_date as $rule_key => $date_rule ) {

						$total_base_cost_date_rule = ! empty( $date_rule['ph_booking_participant_rule_base_cost'] ) ? $this->perform_cost_rule_operation( $total_base_cost_date_rule, $date_rule['ph_booking_participant_rule_base_cost'], $date_rule['participantbasecost_operator'] ) : $total_base_cost_date_rule;

						$individual_cost_per_participant_date_rule = ! empty( $rule['ph_booking_persons_rule_cost_per_unit'] ) ? ph_wc_format_decimal( $rule['ph_booking_persons_rule_cost_per_unit'] ) : 0;
						$individual_cost_per_participant_date_rule = $this->perform_cost_rule_operation( $individual_cost_per_participant_date_rule, $date_rule['ph_booking_rule_cost_per_participant'], $date_rule['perparticipant_operator'] );
						$total_cost_per_participant_date_rule     += $this->calculate_participant_cost( 0, $individual_cost_per_participant_date_rule, $date_rule['matched_slots'], $person_details[ $key ], $rule['ph_booking_persons_per_slot'] );
					}
				}

				if ( $found_total_participant_rules_block_count != 0 ) {
					$total_base_cost_block_count_rule            += ! empty( $rule['ph_booking_persons_rule_base_cost'] ) ? ph_wc_format_decimal( $rule['ph_booking_persons_rule_base_cost'] ) : 0;
					$individual_cost_per_participant              = ! empty( $rule['ph_booking_persons_rule_cost_per_unit'] ) ? ph_wc_format_decimal( $rule['ph_booking_persons_rule_cost_per_unit'] ) : 0;
					$individual_cost_per_participant              = $this->perform_cost_rule_operation( $individual_cost_per_participant, $total_participant_rules_block_count['ph_booking_rule_cost_per_participant'], $total_participant_rules_block_count['perparticipant_operator'] );
					$total_cost_per_participant_block_count_rule += $this->calculate_participant_cost( 0, $individual_cost_per_participant, $number_of_slots, $person_details[ $key ], $rule['ph_booking_persons_per_slot'] );
					// error_log($total_cost_per_participant_block_count_rule);
				}

				$base_cost    = ! empty( $rule['ph_booking_persons_rule_base_cost'] ) ? ph_wc_format_decimal( $rule['ph_booking_persons_rule_base_cost'] ) : 0;
				$cos_per_unit = ! empty( $rule['ph_booking_persons_rule_cost_per_unit'] ) ? ph_wc_format_decimal( $rule['ph_booking_persons_rule_cost_per_unit'] ) : 0;
				if ( ! empty( $this->participant_pricing_rules ) ) {
					$is_participant_rule_applied = 0;
					foreach ( $this->participant_pricing_rules as $keys => $rules ) {

						if ( is_numeric( $rules['ph_booking_participant_rule_type'] ) && $rules['ph_booking_participant_rule_type'] == $key ) {

							if ( ( $rules['pricing_from_participant'] <= $person_details[ $key ] ) && ( $rules['pricing_to_participant'] >= $person_details[ $key ] ) ) {
								++$is_participant_rule_applied;
								// participant rule for base cost
								$base_cost_participant_rule = $this->perform_cost_rule_operation( $base_cost, $rules['ph_booking_participant_rule_base_cost'], $rules['participantbasecost_operator'] );
								// participant rule for cost per participant
								$cos_per_unit_participant_rule = ! empty( $rules['ph_booking_rule_cost_per_participant'] ) ? $this->perform_cost_rule_operation( $cos_per_unit, $rules['ph_booking_rule_cost_per_participant'], $rules['perparticipant_operator'] ) : $cos_per_unit;
								$persons_price                += $this->calculate_participant_cost( $base_cost_participant_rule, $cos_per_unit_participant_rule, $number_of_slots, $person_details[ $key ], $rule['ph_booking_persons_per_slot'] );
							}
						} elseif ( $rules['ph_booking_participant_rule_type'] == 'block_count_with_' . $key ) {

							if ( ( $rules['pricing_from_participant'] <= $person_details[ $key ] ) && ( $rules['pricing_to_participant'] >= $person_details[ $key ] ) && isset( $rules['pricing_from_participant_block_count'] ) && $number_of_slots >= $rules['pricing_from_participant_block_count'] && isset( $rules['pricing_to_participant_block_count'] ) && $number_of_slots <= $rules['pricing_to_participant_block_count'] ) {
								++$is_participant_rule_applied;
								// participant rule for base cost
								$base_cost_participant_rule = $this->perform_cost_rule_operation( $base_cost, $rules['ph_booking_participant_rule_base_cost'], $rules['participantbasecost_operator'] );
								// participant rule for cost per participant
								$cos_per_unit_participant_rule = ! empty( $rules['ph_booking_rule_cost_per_participant'] ) ? $this->perform_cost_rule_operation( $cos_per_unit, $rules['ph_booking_rule_cost_per_participant'], $rules['perparticipant_operator'] ) : $cos_per_unit;
								$persons_price                += $this->calculate_participant_cost( $base_cost_participant_rule, $cos_per_unit_participant_rule, $number_of_slots, $person_details[ $key ], $rule['ph_booking_persons_per_slot'] );
							}
						} elseif ( ( $rules['ph_booking_participant_rule_type'] == 'participant_based_custom_date' && $total_participant_rules_applied == 'participant_based_custom_date' ) || ( $rules['ph_booking_participant_rule_type'] == 'participant_based_week_day' && $total_participant_rules_applied == 'participant_based_week_day' ) ) {
							break;
						} elseif ( $rules['ph_booking_participant_rule_type'] == 'participant_based_block_count' && $found_total_participant_rules_block_count != 0 ) {
							break;
						} elseif ( $rules['ph_booking_participant_rule_type'] == 'participant_based' && $found_total_participant_rules != 0 ) {
							break;
						}
					}
					if ( $is_participant_rule_applied == 0 && ( $found_total_participant_rules == 0 && $found_total_participant_rules_date == 0 && $found_total_participant_rules_block_count == 0 ) ) {
						$cos_per_unit_participant_rule = $cos_per_unit;
						$base_cost_participant_rule    = $base_cost;
						$persons_price                += $this->calculate_participant_cost( $base_cost_participant_rule, $cos_per_unit_participant_rule, $number_of_slots, $person_details[ $key ], $rule['ph_booking_persons_per_slot'] );
					} elseif ( $is_participant_rule_applied == 0 && ( $found_total_participant_rules == 0 && $found_total_participant_rules_block_count == 0 && $found_total_participant_rules_date != 0 ) && $number_of_slots > 0 ) {
						$cos_per_unit_participant_rule = $cos_per_unit;
						$persons_price                += $this->calculate_participant_cost( 0, $cos_per_unit_participant_rule, $number_of_slots, $person_details[ $key ], $rule['ph_booking_persons_per_slot'] );
					}
				}
				// if participant rule not set
				else {
					$persons_price += $this->calculate_participant_cost( $base_cost, $cos_per_unit, $number_of_slots, $person_details[ $key ], $rule['ph_booking_persons_per_slot'] );
				}
			}
			if ( $found_total_participant_rules != 0 && $is_participant_rule_applied == 0 ) {
				// participant rule for base cost
				$total_base_cost = ! empty( $total_participant_rules['ph_booking_participant_rule_base_cost'] ) ? $this->perform_cost_rule_operation( $total_base_cost, $total_participant_rules['ph_booking_participant_rule_base_cost'], $total_participant_rules['participantbasecost_operator'] ) : $total_base_cost;
				$persons_price  += $total_base_cost + $total_cost_per_participant;
			}

			if ( $found_total_participant_rules_date != 0 && $is_participant_rule_applied == 0 ) {
				// participant rule for base cost
				$persons_price += $total_base_cost_date_rule + $total_cost_per_participant_date_rule;
			}
			if ( $found_total_participant_rules_block_count != 0 && $is_participant_rule_applied == 0 ) {
				// participant rule for base cost
				$total_base_cost_block_count_rule = ! empty( $total_participant_rules_block_count['ph_booking_participant_rule_base_cost'] ) ? $this->perform_cost_rule_operation( $total_base_cost_block_count_rule, $total_participant_rules_block_count['ph_booking_participant_rule_base_cost'], $total_participant_rules_block_count['participantbasecost_operator'] ) : $total_base_cost_block_count_rule;
				$persons_price                   += $total_base_cost_block_count_rule + $total_cost_per_participant_block_count_rule;
			}
		}
		// Sum of all customer provided value for rules.
		// Made this global, Because this value is required for resources calculation
		$this->persons_count = $persons_count;
		if ( $this->persons_multuply_all_cost == 'yes' ) { // if multiply by person
			if ( $persons_count > 0 ) {
				$price *= $this->persons_count;
			}
		}

		$persons_price = apply_filters( 'ph_bookings_participant_applied_cost', $persons_price, $this->id, $this->base_cost, $this->cost_per_unit, $person_details, $customer_choosen_values, $this );

		$price += $persons_price;

		return $price;
	}

	private function calculate_participant_cost( $base_cost, $cos_per_unit, $number_of_slots, $person_details_key, $ph_booking_persons_per_slot ) {
		$persons_price = 0;

		if ( $this->persons_multuply_all_cost == 'yes' ) {
			if ( $ph_booking_persons_per_slot == 'yes' && is_numeric( $number_of_slots ) && $person_details_key > 0 ) {
				$persons_price += ( $base_cost + ( $cos_per_unit * $number_of_slots ) ) * $person_details_key;
			} else {
				$persons_price += ( $base_cost + $cos_per_unit ) * $person_details_key;
			}
		} elseif ( $ph_booking_persons_per_slot == 'yes' && is_numeric( $number_of_slots ) && $person_details_key > 0 ) {
				$persons_price += $base_cost + ( $cos_per_unit * $person_details_key * $number_of_slots );
		} else {
			$persons_price += $base_cost + ( $cos_per_unit * $person_details_key );
		}
		return $persons_price;
	}

	private function phive_apply_resources_price( $price, $customer_choosen_values = '' ) {
		if ( empty( $customer_choosen_values ) ) {
			return $price;
		}

		if ( $this->resources_enabled !== 'yes' ) {
			return $price;
		}

		$initial_price = $price;

		// in the case of persons count is zero and per_person is enabled, the resorces cost might be added once
		$persons_count = $this->persons_count > 0 ? $this->persons_count : 1;

		$resources_details = isset($customer_choosen_values[ $this->id ]['resources_details']) ? $customer_choosen_values[ $this->id ]['resources_details'] : array();

		$resources_details = apply_filters( 'ph_change_resources_details_for_price_calculation', $resources_details, $this->id, $customer_choosen_values );
		
		$number_of_slots   = $this->number_of_slots_selected;

		// Filter to skip cost calculation for auto assign resource
		$skip_price_calculation = apply_filters( 'ph_skip_price_calculation_for_hide_resource', $resources_details, $this->id, $customer_choosen_values );

		foreach ( $this->resources_pricing_rules as $key => $rule ) {

			// Skip cost calculation for auto assign resource hide by hide resource addon 
			if ( isset($skip_price_calculation[$key]) && $skip_price_calculation[$key] == 'hide' ) {
				
				continue;
			}

			$cost = ! empty( $rule['ph_booking_resources_cost'] ) ? ph_wc_format_decimal( $rule['ph_booking_resources_cost'] ) : 0;

			if ( ( isset( $resources_details[ $key ] ) && $resources_details[ $key ] == 'yes' ) || ( $rule['ph_booking_resources_auto_assign'] === 'yes' && $this->resources_type != 'single' ) ) {
				if ( $rule['ph_booking_resources_per_person'] == 'yes' && $rule['ph_booking_resources_per_slot'] == 'yes' ) {
					$price += ( $persons_count * $number_of_slots * $cost );
				} elseif ( $rule['ph_booking_resources_per_person'] == 'yes' && is_numeric( $this->persons_count ) ) {

					$price += ( $persons_count * $cost );
				} elseif ( $rule['ph_booking_resources_per_slot'] == 'yes' && is_numeric( $number_of_slots ) ) {
					$price += ( $number_of_slots * $cost );
				} else {

					$price += $cost;
				}
			}
			// customisation support - calculate resource base cost
			$price = apply_filters( 'ph_bookings_adding_resource_costs', $price, $this->id, $key, $rule, $resources_details, $persons_count, $number_of_slots );
		}

		$price = apply_filters( 'ph_bookings_resource_applied_cost', $price, $initial_price, $this->id, $this->base_cost, $this->cost_per_unit );

		return $price;
	}

	private function phive_apply_asset_price( $price, $customer_choosen_values = '' ) {
		if ( empty( $customer_choosen_values ) ) {
			return $price;
		}
		if ( $this->asset_enabled != 'yes' || empty( $this->assets_id ) ) {
			return $price;
		}

		$initial_price = $price;

		$number_of_slots = $this->number_of_slots_selected;

		// 169244  - Issue: When enabling "Multiply all costs by number of participants" option, cost is not calculating accurately!
		$asset_cost = 0;

		foreach ( $this->assets_pricing_rules as $key => $rule ) {

			if ( $rule['ph_booking_asset_id'] != $this->assets_id ) {
				continue;
			}

			$base_cost    = ! empty( $rule['ph_booking_assets_base_cost'] ) ? ph_wc_format_decimal( $rule['ph_booking_assets_base_cost'] ) : 0;
			$cos_per_unit = ! empty( $rule['ph_booking_assets_cost_perblock'] ) ? ph_wc_format_decimal( $rule['ph_booking_assets_cost_perblock'] ) : 0;
			$asset_cost  += $base_cost;
			if ( $cos_per_unit > 0 ) {
				$asset_cost += ( $cos_per_unit * $number_of_slots );
			}
			break;
		}
		if ( $this->persons_multuply_all_cost == 'yes' && $this->persons_count > 0 ) { // if multiply by person

			$asset_cost *= $this->persons_count;
		}
		$price += $asset_cost;
		$price  = apply_filters( 'ph_bookings_asset_applied_cost', $price, $initial_price, $this->id, $this->base_cost, $this->cost_per_unit );

		return $price;
	}

	/**
	 * check if the given value is in given range of the array
	 *
	 * @param $full_ranges the range array with integer values
	 * @param $check_me the value to check if in range
	 * @param $lower_range Lower range
	 * @param $uppper_range Upper range
	 * @return bool
	 */
	private function is_in_range( $full_ranges, $check_me, $lower_range, $uppper_range ) {
		if ( $lower_range <= $uppper_range ) {
			return $check_me >= $lower_range && $check_me <= $uppper_range;
		} else {
			$count           = count( $full_ranges );
			$new_range_limit = $count - $lower_range + $count;
			$uppper_range   += $count;
			if ( $check_me < $lower_range ) {
				$check_me += $count;
			}

			$available_array = array();
			for ( $i = $lower_range; $i <= $uppper_range; $i++ ) {
				$available_array[] = $i;
			}

			return in_array( $check_me, $available_array );
		}
	}

	private function phive_calculate_rule_price() {

		$this->segregate_pricing_rules();

		$this->set_base_cost_and_unit_cost_by_number_of_slot();         // Set the price based on Number of Slot Booked.
		$booking_cost = $this->apply_time_pricing_rule_cost();

		// return apply_filters('ph_bookings_final_rule_based_booking_cost',$booking_cost,$this->book_from, $this->book_to,$this->id);

		return apply_filters( 'ph_bookings_final_rule_based_booking_cost', $booking_cost, $this->book_from, $this->book_to, $this->id, $this->base_cost, $this->cost_per_unit );
	}

	private function get_buffer_added_interval() {

		$buffer_before_time = empty( $this->buffer_before_time ) ? '0' : $this->buffer_before_time;
		$buffer_after_time  = empty( $this->buffer_after_time ) ? '0' : $this->buffer_after_time;
		$interval           = $this->prod_interval;
		if ( ( ( $buffer_before_time % $this->prod_interval ) != 0 ) || ( ( $buffer_after_time % $this->prod_interval ) != 0 ) ) {
			$interval += ( $buffer_before_time + $buffer_after_time );
		}
		return $interval;
	}

	private function set_base_cost_and_unit_cost_by_number_of_slot( $calulated_price = 0 ) {

		$number_of_slots = $this->number_of_slots_selected;

		foreach ( $this->non_time_related_pricing_rules as $key => $rule ) {

			if ( $rule['pricing_type'] == 'slot_based' ) {
				// ticket 111845 Decimal separator is "," in WooCommerce settings. Unable to use the same separator for Discount or Special Price in booking costs tab
				$rule['cost_per_unit'] = ph_wc_format_decimal( $rule['cost_per_unit'] );
				$rule['base_cost']     = ph_wc_format_decimal( $rule['base_cost'] );

				$rule = apply_filters( 'ph_booking_rule_for_setting_base_cost_and_unit_cost', $rule, $this->book_from, $this->book_to, $number_of_slots );

				// for multiple non adjacent booking rule calculation
				$number_of_slots = apply_filters( 'ph_change_number_of_slot_before_slot_based_rule_calculation', $number_of_slots, $rule, $this->book_from, $this->book_to, $this->base_cost, $this->cost_per_unit, $this->id );

				if ( ( ( ! empty( $rule['from_slot'] ) && $number_of_slots >= $rule['from_slot'] ) || empty( $rule['from_slot'] ) )
					&& ( ! empty( $rule['to_slot'] ) && $number_of_slots <= $rule['to_slot'] ) || empty( $rule['to_slot'] )
				) {

					// Find the unit cost
					if ( ! empty( $rule['cost_per_unit'] ) && is_numeric( $rule['cost_per_unit'] ) ) {
						$this->cost_per_unit = $this->perform_cost_rule_operation( $this->cost_per_unit, $rule['cost_per_unit'], $rule['perunit_operator'] );
					}

					// Find the base cost
					if ( ! empty( $rule['base_cost'] ) && is_numeric( $rule['base_cost'] ) ) {
						$this->base_cost = $this->perform_cost_rule_operation( $this->base_cost, $rule['base_cost'], $rule['basecost_operator'] );
					}
					return;
				}
			}
		}
	}

	private function apply_time_pricing_rule_cost() {

		$calulated_price       = 0;
		$this->base_cost_added = false;
		$loop_breaker          = 1500;
		$current_time          = $this->book_from;
		$current_time_day      = date( 'd', $current_time );
		// Find rate for each slot one by one.

		$day_crossed = 'no';
		while ( ! empty( $current_time ) && $current_time <= $this->book_to && $loop_breaker > 0 ) {

			// For allow acros day wrong cost calculation #185474
			// Cost calculation for across day booking based on user settings
			if ( ( $this->interval_period == 'hour' || $this->interval_period == 'minute' ) ) {

				// This code helps to skip invisible slot between across days for cost calculation
				if ( $this->across_the_day_cost_calculation != 'include' && ! $this->is_working_time( $current_time ) ) {

					$current_time_day = date( 'd', $current_time );
					$current_time     = strtotime( "+$this->interval_string", $current_time );

					if ( date( 'd', $current_time ) != $current_time_day ) {

						if ( ! empty( $this->shop_opening_time ) ) {

							$current_time = strtotime( date( "Y-m-d $this->shop_opening_time", $current_time ) );
						} else {
							$current_time = strtotime( date( 'Y-m-d 00:00', $current_time ) );
						}
					}
					--$loop_breaker;
					continue;
				} elseif (
					$this->across_the_day_cost_calculation == 'include' &&
					date( 'd', $current_time ) != date( 'd', strtotime( "+$this->interval_string_without_buffer", $current_time ) ) &&
					'00:00' != date( 'H:i', strtotime( "+$this->interval_string", $current_time ) )
				) {
					$current_time = strtotime( "+$this->interval_string", $current_time );
					$current_time = strtotime( date( 'Y-m-d 00:00', $current_time ) );
					--$loop_breaker;
					$day_crossed = 'yes';
					continue;
				}
				if (
					$day_crossed == 'yes' && ! empty( $this->shop_opening_time ) &&
					$this->shop_opening_time <= date( 'H:i', strtotime( "+$this->interval_string", $current_time ) )
				) {
					// when shop opening time less than next slot time assign shop opening time
					if ( $this->shop_opening_time < date( 'H:i', strtotime( "+$this->interval_string", $current_time ) ) ) {
						$current_time = strtotime( date( "Y-m-d $this->shop_opening_time", $current_time ) );
					}
					$day_crossed = 'no';
				}
			}

			$rule_price = $this->calculate_rule_price_of_the_block( $current_time, $this->interval_string, $this->book_from, $this->book_to );
			$rule_price = apply_filters( 'ph_change_cost_per_unit_based_on_bookings', $rule_price, $current_time, $this->book_from, $this->book_to, $this->id );
			// In the case of multiple rule satisfying, it will take the base cost of first rule.
			if ( $this->base_cost_added === false && is_numeric( $rule_price['base_cost'] ) ) {

				$calulated_price      += ( ph_wc_format_decimal( $rule_price['base_cost'] ) + ph_wc_format_decimal( $rule_price['cost_per_unit'] ) );
				$this->base_cost_added = true;
			} else {
				$calulated_price += ph_wc_format_decimal( $rule_price['cost_per_unit'] );
				// 121279 - JOB3 = Ability to create a custom holiday and allow the customers to book across this custom holiday.
				$calulated_price = apply_filters( 'ph_modify_apply_time_pricing_rule_cost', $calulated_price, $this->id, $rule_price['cost_per_unit'], $current_time, $this->book_from, $this->book_to );
			}

			$current_time_day  = date( 'd', $current_time );
			$prev_current_time = $current_time;
			$current_time      = strtotime( "+$this->interval_string", $current_time );

			if ( date( 'd', $current_time ) != $current_time_day ) {

				if ( date( 'd', $prev_current_time ) == date( 'd', strtotime( "+$this->interval_string_without_buffer", $prev_current_time ) ) ) {

					$current_time = strtotime( "+$this->interval_string_without_buffer", $prev_current_time );
				} else {
					$day_crossed = 'yes';
				}
			}

			// This code helps to skip invisible slot between across days for cost calculation
			if ( ( $this->interval_period == 'hour' || $this->interval_period == 'minute' ) && date( 'd', $current_time ) != $current_time_day && $this->across_the_day_cost_calculation != 'include' ) {

				if ( ! empty( $this->shop_opening_time ) ) {

					$current_time = strtotime( date( "Y-m-d $this->shop_opening_time", $current_time ) );
				} else {
					$current_time = strtotime( date( 'Y-m-d 00:00', $current_time ) );
				}
			}
			--$loop_breaker;
		}
		// If no rule with base cost
		if ( $this->base_cost_added === false ) {
			$calulated_price += $this->base_cost;
		}

		return $calulated_price;
	}

	private function set_product_pricing_properties() {
		// Case of AJAX

		if ( empty( $this->product_properties ) ) {
			$this->product_properties = get_post_meta( $this->id );
		}
		$this->pricing_rules          = ! empty( $this->product_properties['_phive_booking_pricing_rules'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_pricing_rules'][0] ) : array();
		$this->base_cost              = ! empty( $this->product_properties['_phive_booking_pricing_base_cost'][0] ) ? ph_wc_format_decimal( maybe_unserialize( $this->product_properties['_phive_booking_pricing_base_cost'][0] ) ) : 0;
		$this->cost_per_unit          = ! empty( $this->product_properties['_phive_booking_pricing_cost_per_unit'][0] ) ? ph_wc_format_decimal( maybe_unserialize( $this->product_properties['_phive_booking_pricing_cost_per_unit'][0] ) ) : 0;
		$this->display_cost           = ! empty( $this->product_properties['_phive_booking_pricing_display_cost'][0] ) ? ph_wc_format_decimal( maybe_unserialize( $this->product_properties['_phive_booking_pricing_display_cost'][0] ) ) : ( $this->cost_per_unit + $this->base_cost );
		$this->charge_per_night       = ! empty( $this->product_properties['_phive_book_charge_per_night'][0] ) ? maybe_unserialize( $this->product_properties['_phive_book_charge_per_night'][0] ) : '';
		$this->interval_period        = ! empty( $this->product_properties['_phive_book_interval_period'][0] ) ? maybe_unserialize( $this->product_properties['_phive_book_interval_period'][0] ) : '';
		$this->prod_interval          = ! empty( $this->product_properties['_phive_book_interval'][0] ) ? maybe_unserialize( $this->product_properties['_phive_book_interval'][0] ) : '';
		$this->cancel_interval_period = ! empty( $this->product_properties['_phive_cancel_interval_period'][0] ) ? maybe_unserialize( $this->product_properties['_phive_cancel_interval_period'][0] ) : '';
		$this->prod_cancel_interval   = ! empty( $this->product_properties['_phive_cancel_interval'][0] ) ? maybe_unserialize( $this->product_properties['_phive_cancel_interval'][0] ) : '';
		/*
		$this->ph_checkin                 = !empty($this->product_properties['_phive_book_checkin'][0])               ? maybe_unserialize($this->product_properties['_phive_book_checkin'][0]) : '';
		$this->ph_checkout              = !empty($this->product_properties['_phive_book_checkin'][0])               ? maybe_unserialize($this->product_properties['_phive_book_checkin'][0]) : '';*/
		$this->person_enabled            = ! empty( $this->product_properties['_phive_booking_person_enable'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_person_enable'][0] ) : '';
		$this->persons_multuply_all_cost = ! empty( $this->product_properties['_phive_booking_persons_multuply_all_cost'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_persons_multuply_all_cost'][0] ) : '';
		$this->persons_pricing_rules     = ! empty( $this->product_properties['_phive_booking_persons_pricing_rules'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_persons_pricing_rules'][0] ) : array();
		$this->participant_pricing_rules = ! empty( $this->product_properties['_phive_booking_participant_pricing_rules'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_participant_pricing_rules'][0] ) : array();
		$this->resources_enabled         = ! empty( $this->product_properties['_phive_booking_resources_enable'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_resources_enable'][0] ) : '';
		$this->resources_pricing_rules   = ! empty( $this->product_properties['_phive_booking_resources_pricing_rules'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_resources_pricing_rules'][0] ) : array();
		$this->resources_type            = ( isset( $this->product_properties['_phive_booking_resources_type'][0] ) && ! empty( $this->product_properties['_phive_booking_resources_type'][0] ) ) ? $this->product_properties['_phive_booking_resources_type'][0] : '';
		$this->asset_enabled             = ! empty( $this->product_properties['_phive_booking_assets_enable'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_assets_enable'][0] ) : '';
		$this->assets_pricing_rules      = ! empty( $this->product_properties['_phive_booking_assets_pricing_rules'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_assets_pricing_rules'][0] ) : '';
		$this->assets_auto_assign        = ! empty( $this->product_properties['_phive_booking_assets_auto_assign'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_assets_auto_assign'][0] ) : '';

		$this->allowd_per_slot                 = ! empty( $this->product_properties['_phive_book_allowed_per_slot'][0] ) ? maybe_unserialize( $this->product_properties['_phive_book_allowed_per_slot'][0] ) : array();
		$this->persons_as_booking              = ! empty( $this->product_properties['_phive_booking_persons_as_booking'][0] ) ? maybe_unserialize( $this->product_properties['_phive_booking_persons_as_booking'][0] ) : array();
		$this->buffer_before_time              = ! empty( $this->product_properties['_phive_buffer_before'][0] ) ? maybe_unserialize( $this->product_properties['_phive_buffer_before'][0] ) : array();
		$this->buffer_after_time               = ! empty( $this->product_properties['_phive_buffer_after'][0] ) ? maybe_unserialize( $this->product_properties['_phive_buffer_after'][0] ) : array();
		$this->buffer_period                   = ! empty( $this->product_properties['_phive_buffer_period'][0] ) ? maybe_unserialize( $this->product_properties['_phive_buffer_period'][0] ) : array();
		$this->phive_enable_buffer             = ! empty( $this->product_properties['_phive_enable_buffer'][0] ) ? maybe_unserialize( $this->product_properties['_phive_enable_buffer'][0] ) : array();
		$this->shop_opening_time               = ! empty( $this->product_properties['_phive_book_working_hour_start'][0] ) ? maybe_unserialize( $this->product_properties['_phive_book_working_hour_start'][0] ) : '';
		$this->shop_closing_time               = ! empty( $this->product_properties['_phive_book_working_hour_end'][0] ) ? maybe_unserialize( $this->product_properties['_phive_book_working_hour_end'][0] ) : '';
		$this->across_the_day_cost_calculation = ! empty( $this->product_properties['_phive_across_the_day_cost_calculation'][0] ) ? maybe_unserialize( $this->product_properties['_phive_across_the_day_cost_calculation'][0] ) : 'include';

		// Default interval 1
		if ( empty( $this->prod_interval ) ) {

			$this->prod_interval = 1;
		}
		if ( ( $this->phive_enable_buffer == 'yes' ) && ( $this->interval_period == $this->buffer_period ) && ( $this->interval_period == 'minute' || $this->interval_period == 'hour' ) ) {
			$interval = $this->get_buffer_added_interval();
		} else {
			$interval = $this->prod_interval;
		}
		$this->buffer_added_interval          = $interval;
		$this->interval_string                = "$interval $this->interval_period";
		$this->interval_string_without_buffer = "$this->prod_interval $this->interval_period";
	}

	/**
	 * This function will check the time is working time or not of the shop
	 *
	 * @since 3.1.2
	 * @param string $date time to checking working time or not
	 */
	private function is_working_time( $date ) {
		$time = strtotime( date( 'H:i', $date ) );

		// if time falls in working hours
		if ( ( empty( $this->shop_opening_time ) && empty( $this->shop_closing_time ) )
			|| ! empty( $this->shop_opening_time ) && ! empty( $this->shop_closing_time ) && $time >= strtotime( $this->shop_opening_time ) && $time <= strtotime( $this->shop_closing_time )
			|| empty( $this->shop_opening_time ) && ! empty( $this->shop_closing_time ) && $time <= strtotime( $this->shop_closing_time )
			|| ! empty( $this->shop_opening_time ) && empty( $this->shop_closing_time ) && $time >= strtotime( $this->shop_opening_time )
		) {
			return true;
		}
		return false;
	}

	/**
	 * This function will calculate the number of slots selected in one day
	 *
	 * @since 3.1.2
	 * @param array $matched_dates dates that are macthed with the rules
	 */
	private function get_number_of_slots_in_matched_days( $matched_dates, $from_time, $to_time ) {
		$slot_found = 0;
		$from_time  = strtotime( str_replace( '/', '-', $from_time ) );
		$to_time    = strtotime( str_replace( '/', '-', $to_time ) );
		foreach ( $matched_dates as $key => $value ) {

			if ( $key == date( 'Y-m-d', $from_time ) ) {
				$matched_from = $from_time;
			} elseif ( ! empty( $this->shop_opening_time ) ) {
				$matched_from = strtotime( date( "Y-m-d $this->shop_opening_time", strtotime( $key ) ) );
			} else {
				$matched_from = strtotime( date( 'Y-m-d 00:00', strtotime( $key ) ) );
			}
			$from_time_day = date( 'd', $matched_from );

			// Calculating selected slots for each matched dates
			while ( date( 'd', $matched_from ) == $from_time_day && $matched_from <= $to_time ) {

				// This code helps to skip the slots between acorss day for cost calculation
				if ( ( $this->interval_period == 'hour' || $this->interval_period == 'minute' ) && ! $this->is_working_time( $matched_from ) && $this->across_the_day_cost_calculation != 'include' ) {
					break;
				}
				$matched_from = strtotime( "+$this->interval_string", $matched_from );
				++$slot_found;
			}
		}
		return $slot_found;
	}

	/**
	 * Return the macthed dates for the cost calculation if it is not matched previously
	 *
	 * @since 3.2.6
	 * @param array   $choosen_date_ranges contains either the dates choosen or days choosen
	 * @param array   $rule_date_ranges contains either the rule date ranges or rule day range
	 * @param array   $matched_dates contains all matched dates
	 * @param boolean $return_count
	 * @return mixed returns either the matched dates or count of matche dates
	 */
	public function get_matched_dates_from_choosen_dates( $choosen_date_ranges, $rule_date_ranges, $matched_dates, $return_count = false ) {
		$matched_dates = array_intersect( array_diff_key( $choosen_date_ranges, $matched_dates ), $rule_date_ranges );

		if ( $return_count ) {

			return count( $matched_dates );
		} else {

			return $matched_dates;
		}
	}
}
