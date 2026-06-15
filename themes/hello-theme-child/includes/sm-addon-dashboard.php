<?php if ( ! defined( 'ABSPATH' ) ) exit;
$room_id = isset( $atts['room_id'] ) ? esc_attr( $atts['room_id'] ) : ''; ?>

<div id="ao_dashboard" class="sm_addon_dashboard">
    <!-- <h3 style="display:none;">Room ID: <?php echo $room_id; ?></h3> -->
    <?php 
    /*$booking = get_phive_product_current_booking( $room_id );
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
        echo '</div>';*/
    ?>
    
    <div id="seat_box_wr" class="seat_box_wr">
        <h1>Food & Drinks for This Meeting</h1>
        <div class="pref_right pref_top">
            <div class="pref_right_inner">

                <div class="pref_right_top">
                    <div class="pr_seat_info">
                        <div class="pr_seat_room"><span>Room Name:</span> <label>Kautilya</label></div>
                        <div class="pr_seat_br"><span>Booking Ref:</span> <label>35268</label></div>
                        <div class="pr_primary_user"><span>Primary User:</span> <label>Aditya Jain</label></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <p>Neque porro quisquam est qui dolorem ipsum quia dolor sit amet</p> -->

        <div class="seat_box_outer">
            <div class="seat_box_left">
                <h2>Interactive Seat Selection</h2>
                <!-- <p>Neque porro quisquam est qui dolorem ipsum quia dolor sit amet</p> -->
                <div class="seat_box_support">
                    <div class="seat_box">
                        <div class="seat_dia">
                            <div class="seat_table_computer">
                                <span>COMPUTER <br>TABLE</span>
                            </div>
                            <div class="seat_table_top"></div>
                            <div class="seat_table_bottom"></div>
                        </div>
                        <div class="seat_items">
                            <div class="seat_item seat_1" data-id="1">1</div>
                            <div class="seat_item seat_2" data-id="2">2</div>
                            <div class="seat_item seat_3" data-id="3">3</div>
                            <div class="seat_item seat_4" data-id="4">4</div>
                            <div class="seat_item seat_5" data-id="5">5</div>
                            <div class="seat_item seat_6" data-id="6">6</div>
                            <div class="seat_item seat_7" data-id="7">7</div>
                            <div class="seat_item seat_8" data-id="8">8</div>
                            <div class="seat_item seat_9" data-id="9">9</div>
                            <div class="seat_item seat_10" data-id="10">10</div>
                            <div class="seat_item seat_11" data-id="11">11</div>
                            <div class="seat_item seat_12" data-id="12">12</div>
                            <div class="seat_item seat_13" data-id="13">13</div>
                            <div class="seat_item seat_14" data-id="14">14</div>
                            <div class="seat_item seat_15" data-id="15">15</div>
                            <div class="seat_item seat_16" data-id="16">16</div>
                            <div class="seat_item seat_17" data-id="17">17</div>
                            <div class="seat_item seat_18" data-id="18">18</div>
                            <div class="seat_item seat_19" data-id="19">19</div>
                            <div class="seat_item seat_20" data-id="20">20</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="seat_box_right">
                <div class="seat_box_right_inner">
                    <div class="side_btn_wr">
                        <div class="view_pref_btn_box">
                            <button type="button" class="view_pref disabled" disabled="disabled">Review Selections & Orders</button>
                            <div class="view_pref_notice">Please add at least one preference or add-ons order</div>
                        </div>
                        <div class="reset_btn_wr">
                            <button type="button" class="reset_seats disabled" disabled="disabled">Reset All Selection</button>
                        </div>
                    </div>
                    <h2>Seat Listing</h2>
                    <ul class="seat_ul">
                        <li class="seat_li" data-id="1">
                            <div class="seat_li_inner">
                                <div class="seat_num">1</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="2">
                            <div class="seat_li_inner">
                                <div class="seat_num">2</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="3">
                            <div class="seat_li_inner">
                                <div class="seat_num">3</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="4">
                            <div class="seat_li_inner">
                                <div class="seat_num">4</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="5">
                            <div class="seat_li_inner">
                                <div class="seat_num">5</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="6">
                            <div class="seat_li_inner">
                                <div class="seat_num">6</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="7">
                            <div class="seat_li_inner">
                                <div class="seat_num">7</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="8">
                            <div class="seat_li_inner">
                                <div class="seat_num">8</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="9">
                            <div class="seat_li_inner">
                                <div class="seat_num">9</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="10">
                            <div class="seat_li_inner">
                                <div class="seat_num">10</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="11">
                            <div class="seat_li_inner">
                                <div class="seat_num">11</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="12">
                            <div class="seat_li_inner">
                                <div class="seat_num">12</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="13">
                            <div class="seat_li_inner">
                                <div class="seat_num">13</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="14">
                            <div class="seat_li_inner">
                                <div class="seat_num">14</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="15">
                            <div class="seat_li_inner">
                                <div class="seat_num">15</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="16">
                            <div class="seat_li_inner">
                                <div class="seat_num">16</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="17">
                            <div class="seat_li_inner">
                                <div class="seat_num">17</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="18">
                            <div class="seat_li_inner">
                                <div class="seat_num">18</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="19">
                            <div class="seat_li_inner">
                                <div class="seat_num">19</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>

                        <li class="seat_li" data-id="20">
                            <div class="seat_li_inner">
                                <div class="seat_num">20</div>
                                <div class="seat_data">
                                    <!-- <div class="seat_data_item seat_data_full_name">Full Name:</div> -->
                                    <div class="seat_data_item seat_data_status">Status:</div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="divider_line"></div>

        <form id="pref_form">
            <div id="pref_wr" class="pref_wr" style="display:none;">

                <div class="pref_left">
                    <h2 class="pref_head_btn">
                        <span>Set Your Choices</span>
                        <div class="save_pref_box">
                            <div class="pr_seat_no_box"><span>Seat No:</span> <label class="pr_seat_no">1</label></div>
                            <!-- <button type="button" class="reset_seats">Reset All</button> -->
                            <button type="submit" class="save_pref">Save</button>
                            <input type="hidden" name="pr_seat_no" value="1">
                            <div class="pref_right_message" style="display:none;"></div>
                        </div>
                    </h2>

                    <div class="toggle_outer_box">
                        <div class="toggle_box">
                            <h3 class="toggle_head">
                                Beverage Selection
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="12" transform="matrix(-1 8.74228e-08 8.74228e-08 1 24 0)" fill="#1763B9"/><path d="M16.5 10L12.5707 13.9293C12.5317 13.9683 12.4683 13.9683 12.4293 13.9293L8.5 10" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                            </h3>
                            <div class="toggle_content" style="display:none;">
                                <div class="toggle_content_box">

                                    <?php if( have_rows('_teacoffee_preference', 35905) ): $i = 1; ?>                                    
                                        <div class="form_field_box">
                                            <?php 
                                                $bf_object = get_field_object("_teacoffee_preference", 35905);
                                                //print_r($bf_object);
                                            ?>
                                            <!-- <h4><?php //echo $bf_object['label']; ?>Beverage Selection</h4> -->
                                            <div class="form_field form_field_radio">
                                                <?php while( have_rows('_teacoffee_preference', 35905) ): the_row(); 
                                                        $item = get_sub_field('_item'); 
                                                ?>
                                                    <div>
                                                        <input id="tea_coffee_<?php echo $i; ?>" type="radio" value="<?php echo $item; ?>" name="tea_coffee">
                                                        <label for="tea_coffee_<?php echo $i; ?>"><?php echo $item; ?></label>
                                                    </div>
                                                <?php $i++; endwhile; ?>
                                            </div>
                                            <div class="form_field form_field_checkbox tea_sugar_free_field" style="display:none">
                                                <div>
                                                    <input id="tea_sugar_free" type="checkbox" value="Without Sugar" name="tea_sugar_free">
                                                    <label for="tea_sugar_free">Without Sugar</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form_field_box">
                                            <h4>Remark</h4>
                                            <div class="form_field form_field_fw">
                                                <input type="text" name="other_tea_coffee" placeholder="Type Remark">
                                                <div class="other_tea_coffee_count">30 chars left</div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php /*if( have_rows('_add_product_section', 35905) ): $i = 1; ?>
                                        <?php while( have_rows('_add_product_section', 35905) ): the_row(); ?> 
                                            <?php $label = get_sub_field("_title"); ?>                                    
                                            <div class="form_field_box">
                                                <?php 
                                                    $type = get_sub_field("_type");
                                                    $label = get_sub_field("_title");
                                                    $label_slug  = sanitize_title($label);
                                                    $label_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $label_slug)));
                                                    //print_r($bf_object);
                                                ?>
                                                <h4><?php echo $label; ?></h4>
                                                <div class="form_field form_field_<?php echo $type; ?>">

                                                    <?php if( $type == 'radio' ){ ?>
                                                        <?php if( have_rows('_products') ): $j = 1; ?>
                                                            <?php while( have_rows('_products') ): the_row(); 
                                                                    $item = get_sub_field('_title');
                                                                    $price = get_sub_field('_price');
                                                                    $price_text = ($price) ? "₹".$price : "₹0";
                                                            ?>
                                                                <div>
                                                                    <input id="<?php echo $label_slug .'_'. $j; ?>" type="radio" value="<?php echo $item; ?>" name="<?php echo $label_slug; ?>" data-price="<?php echo $price; ?>">
                                                                    <label for="<?php echo $label_slug .'_'. $j; ?>"><?php echo $item; ?> (<?php echo $price_text; ?>)</label>
                                                                </div>
                                                            <?php $j++; endwhile; ?>
                                                        <?php endif; ?>
                                                    <?php }else if($type == 'checkbox'){ ?>
                                                        <?php if( have_rows('_products') ): $j = 1; ?>
                                                            <?php while( have_rows('_products') ): the_row(); 
                                                                    $item = get_sub_field('_title'); 
                                                                    $price = get_sub_field('_price');
                                                                    $price_text = ($price) ? "₹".$price : "₹0";
                                                            ?>
                                                                <div>
                                                                    <input id="<?php echo $label_slug .'_'. $j; ?>" type="checkbox" value="<?php echo $item; ?>" name="<?php echo $label_slug; ?>" data-price="<?php echo $price; ?>">
                                                                    <label for="<?php echo $label_slug .'_'. $j; ?>"><?php echo $item; ?> (<?php echo $price_text; ?>)</label>
                                                                </div>
                                                            <?php $j++; endwhile; ?>
                                                        <?php endif; ?>
                                                    <?php }else if($type == 'textarea'){ ?>
                                                        <textarea class="repeater_textarea" name="<?php echo $label_slug; ?>"></textarea>
                                                        <div class="repeater_textarea_count <?php echo $label_slug; ?>_count">50 chars left</div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <?php $i++; endwhile; ?>
                                    <?php endif;*/ ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="toggle_outer_box">
                        <div class="toggle_box">
                            <h3 class="toggle_head">
                                Meal Preferences
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="12" transform="matrix(-1 8.74228e-08 8.74228e-08 1 24 0)" fill="#1763B9"/><path d="M16.5 10L12.5707 13.9293C12.5317 13.9683 12.4683 13.9683 12.4293 13.9293L8.5 10" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                            </h3>
                            <div class="toggle_content" style="display:none;">
                                <!-- <h3 class="toggle_content_head">Meal & Dietary Preference</h3> -->
                                <div class="toggle_content_box">

                                    <?php if( have_rows('_bread_preference', 2580) ): $i = 1; ?>                                    
                                        <div class="form_field_box">
                                            <?php 
                                                $bf_object = get_field_object("_bread_preference", 2580);
                                                //print_r($bf_object);
                                            ?>
                                            <h4><?php //echo $bf_object['label']; ?>Choice of Bread</h4>
                                            <div class="form_field form_field_radio">
                                                <?php while( have_rows('_bread_preference', 2580) ): the_row(); 
                                                        $item = get_sub_field('_item'); 
                                                ?>
                                                    <div>
                                                        <input id="bread_preference_<?php echo $i; ?>" type="radio" value="<?php echo $item; ?>" name="bread_preference">
                                                        <label for="bread_preference_<?php echo $i; ?>"><?php echo $item; ?></label>
                                                    </div>
                                                <?php $i++; endwhile; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if( have_rows('_food_allergies__intolerances', 2580) ): $i = 1; ?>                                    
                                        <div class="form_field_box">
                                            <?php 
                                                $bf_object = get_field_object("_food_allergies__intolerances", 2580);
                                                //print_r($bf_object);
                                            ?>
                                            <h4><?php //echo $bf_object['label']; ?>Food Allergies / Intolerances</h4>
                                            <div class="form_field form_field_checkbox">
                                                <?php while( have_rows('_food_allergies__intolerances', 2580) ): the_row(); 
                                                        $item = get_sub_field('_item'); 
                                                ?>
                                                    <div>
                                                        <input id="food_allergies_intolerances_<?php echo $i; ?>" type="checkbox" value="<?php echo $item; ?>" name="food_allergies_intolerances">
                                                        <label for="food_allergies_intolerances_<?php echo $i; ?>"><?php echo $item; ?></label>
                                                    </div>
                                                <?php $i++; endwhile; ?>
                                            </div>
                                            <div class="form_field form_field_fw other_food_allergies_intolerances_field" style="display:none">
                                                <input type="text" name="other_food_allergies_intolerances" placeholder="Type Other Food Allergies / Intolerances">
                                                <div class="other_food_allergies_intolerances_field_count">30 chars left</div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- <div class="form_field_box">
                                        <h4>Dietary Requirements</h4>
                                        <div class="form_field form_field_radio">
                                            <div>
                                                <input id="dietary_preference_1" type="radio" value="Regular" name="dietary_preference">
                                                <label for="dietary_preference_1">Regular</label>
                                            </div>
                                            <div>
                                                <input id="dietary_preference_2" type="radio" value="Jain" name="dietary_preference">
                                                <label for="dietary_preference_2">Jain</label>
                                            </div>
                                            <div>
                                                <input id="dietary_preference_3" type="radio" value="Less Spicy" name="dietary_preference">
                                                <label for="dietary_preference_3">Less Spicy</label>
                                            </div>
                                            <div>
                                                <input id="dietary_preference_4" type="radio" value="Vegan" name="dietary_preference">
                                                <label for="dietary_preference_4">Vegan</label>
                                            </div>
                                            <div>
                                                <input id="dietary_preference_5" type="radio" value="Other" name="dietary_preference">
                                                <label for="dietary_preference_5">Other</label>
                                            </div>
                                        </div>
                                        <div class="form_field form_field_fw other_dietary_preference_field" style="display:none">
                                            <input type="text" name="other_dietary_preference" placeholder="Type Other Dietary Preference">
                                            <div class="other_dietary_preference_field_count">30 chars left</div>
                                        </div>
                                    </div> -->

                                    <?php if( have_rows('_dietary_preference', 2580) ): $i = 1; ?>                                    
                                        <div class="form_field_box">
                                            <?php 
                                                $bf_object = get_field_object("_dietary_preference", 2580);
                                                //print_r($bf_object);
                                            ?>
                                            <h4><?php //echo $bf_object['label']; ?>Dietary Requirements</h4>
                                            <div class="form_field form_field_radio">
                                                <?php while( have_rows('_dietary_preference', 2580) ): the_row(); 
                                                        $item = get_sub_field('_item'); 
                                                ?>
                                                    <div>
                                                        <input id="dietary_preference_<?php echo $i; ?>" type="radio" value="<?php echo $item; ?>" name="dietary_preference">
                                                        <label for="dietary_preference_<?php echo $i; ?>"><?php echo $item; ?></label>
                                                    </div>
                                                <?php $i++; endwhile; ?>
                                            </div>
                                            <div class="form_field form_field_fw other_dietary_preference_field" style="display:none">
                                                <input type="text" name="other_dietary_preference" placeholder="Type Other Dietary Preference">
                                                <div class="other_dietary_preference_field_count">30 chars left</div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form_field_box">
                                        <h4>Add a note</h4>
                                        <div class="form_field form_field_fw">
                                            <textarea name="special_instructions"></textarea>
                                            <div class="special_instructions_count">50 chars left</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php 
                        $brands = get_terms(array(
                            'taxonomy'   => 'product_brand',
                            'hide_empty' => true,
                            'meta_query' => array(
                                array(
                                    'key'     => '_enabledisable',
                                    'value'   => 1,
                                    'compare' => '='
                                )
                            )
                        ));
                    if ( ! empty($brands) && ! is_wp_error($brands) ) : ?>
                    <div class="toggle_outer_box">
                        <div class="toggle_box">
                            <h3 class="toggle_head">
                                Snacks & Light Bites
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="12" transform="matrix(-1 8.74228e-08 8.74228e-08 1 24 0)" fill="#1763B9"/><path d="M16.5 10L12.5707 13.9293C12.5317 13.9683 12.4683 13.9683 12.4293 13.9293L8.5 10" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                            </h3>
                            <div class="toggle_content" style="display:none;">
                                <!-- <h3 class="toggle_content_head">Refreshments</h3> -->
                                <div class="toggle_content_box">

                                    <div class="rm_tabs">
                                        <?php //if ( ! empty($brands) && ! is_wp_error($brands) ) : ?>
                                            
                                            <ul class="rm_tabs_ul">
                                                
                                                <!-- View All -->
                                                <li class="rm_tabs_li active" data-filter="all">
                                                    <div class="rm_tabs_inner">
                                                        <span>View All</span>
                                                    </div>
                                                </li>

                                                <?php foreach ( $brands as $brand ) : 
                                                    // If you are using term meta for image
                                                    $brand_image = "";
                                                    $brand_image_id = get_term_meta( $brand->term_id, 'thumbnail_id', true );
                                                    if($brand_image_id){
                                                        $brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
                                                    }
                                                    //print_r(get_term_meta( $brand->term_id ));
                                                    
                                                    // Fallback image (optional)
                                                    if ( empty($brand_image) ) {
                                                        //$brand_image = get_stylesheet_directory_uri() . '/images/default-brand.svg';
                                                    }
                                                ?>
                                                
                                                    <li class="rm_tabs_li" data-filter="<?php echo esc_attr( $brand->slug ); ?>">
                                                        <div class="rm_tabs_inner">
                                                            
                                                            <?php if ( ! empty($brand_image) ) : ?>
                                                                <img src="<?php echo esc_url( $brand_image[0] ); ?>" alt="<?php echo esc_attr( $brand->name ); ?>">
                                                            <?php endif; ?>
                                                            
                                                            <span><?php echo esc_html( $brand->name ); ?></span>
                                                        </div>
                                                    </li>

                                                <?php endforeach; ?>

                                            </ul>

                                        <?php //endif; ?>
                                    </div>

                                    <div class="rm_data">
                                        <table class="rm_data_table">
                                            <thead>
                                                <tr>
                                                    <th class="rm_service_th">Service</th>
                                                    <th class="rm_restaurant_th">Restaurant</th>
                                                    <th class="rm_price_th">Price</th>
                                                    <th class="rm_qty_th">Quantity</th>
                                                    <th class="rm_tprice_th">Total Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    // Step 1: Get enabled brands
                                                    $enabled_brands = get_terms(array(
                                                        'taxonomy'   => 'product_brand',
                                                        'hide_empty' => true,
                                                        'meta_query' => array(
                                                            array(
                                                                'key'   => '_enabledisable',
                                                                'value' => '1',
                                                                'compare' => '='
                                                            )
                                                        ),
                                                        'fields' => 'ids'
                                                    ));

                                                    // Step 2: Query products with those brands
                                                    $args = array(
                                                        'post_type'      => 'product',
                                                        'posts_per_page' => -1,
                                                        'post_status'    => 'publish',
                                                        'tax_query'      => array(
                                                            array(
                                                                'taxonomy' => 'product_brand',
                                                                'field'    => 'term_id',
                                                                'terms'    => $enabled_brands
                                                            ),
                                                        ),
                                                    );

                                                    $query = new WP_Query($args);

                                                    if ( $query->have_posts() ) :

                                                        $row_count = 1;

                                                        while ( $query->have_posts() ) : $query->the_post();
                                                            $product = wc_get_product( get_the_ID() );

                                                            $brands = get_the_terms( get_the_ID(), 'product_brand' );
                                                            //print_r($brands);
                                                            $brand_slug = !empty($brands) && !is_wp_error($brands) ? $brands[0]->slug : '';
                                                            $brand_title = !empty($brands) && !is_wp_error($brands) ? $brands[0]->name : '-';

                                                            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
                                                            if ( ! $image_url ) {
                                                                $image_url = get_stylesheet_directory_uri() . '/images/hot-pan.svg';
                                                            }

                                                            // If you are using term meta for image
                                                            $brand_image = "";
                                                            $brand_image_id = get_term_meta( $brands[0]->term_id, 'thumbnail_id', true );
                                                            if($brand_image_id){
                                                                $brand_image = wp_get_attachment_image_src($brand_image_id, 'large');
                                                            }
                                                            //print_r(get_term_meta( $brand->term_id ));
                                                            
                                                            // Fallback image (optional)
                                                            if ( empty($brand_image) ) {
                                                                //$brand_image = get_stylesheet_directory_uri() . '/images/default-brand.svg';
                                                            }

                                                            $price      = $product ? $product->get_price() : 0;
                                                            $price_html = $product ? $product->get_price_html() : '';
                                                    ?>

                                                    <tr id="rm_table_row_<?php echo esc_attr($row_count); ?>" 
                                                        class="rm_table_row" 
                                                        data-filter="<?php echo esc_attr($brand_slug); ?>">

                                                        <td>
                                                            <!-- <label class="mobile_label" style="display:none;">Service</label> -->
                                                            <div class="rm_item_meta">
                                                                <div class="rm_item_image">
                                                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>">
                                                                </div>
                                                                <h3 class="rm_item_title"><?php the_title(); ?></h3>
                                                            </div>

                                                            <div class="rm_item_remark" style="display:none;">
                                                                <button class="rm_remark_btn" type="button">Add Remark</button>
                                                                <div class="form_field form_field_fw rm_remark_field_box" style="display:none;">
                                                                    <input type="text" name="rm_remark_field" placeholder="Type Remark">
                                                                    <div class="rm_remark_field_count">30 chars left</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        
                                                        <td>
                                                            <!-- <label class="mobile_label" style="display:none;">Restaurant</label> -->
                                                            <div class="rm_restaurant">
                                                                <?php if ( ! empty($brand_image) ) { ?>
                                                                    <img src="<?php echo esc_url( $brand_image[0] ); ?>" alt="<?php echo esc_attr( $brand_title ); ?>">
                                                                <?php }else{ ?>-<?php } ?>
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <label class="mobile_label" style="display:none;">Price</label>
                                                            <div class="rm_price" data-price="<?php echo esc_attr($price); ?>">
                                                                <?php echo $price_html; ?>
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <label class="mobile_label" style="display:none;">Quantity</label>
                                                            <div class="rm_qty">
                                                                <button type="button" class="rm_qty_add_btn">
                                                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M12.5 6.25C12.5 6.44891 12.421 6.63968 12.2803 6.78033C12.1397 6.92098 11.9489 7 11.75 7H7V11.75C7 11.9489 6.92098 12.1397 6.78033 12.2803C6.63968 12.421 6.44891 12.5 6.25 12.5C6.05109 12.5 5.86032 12.421 5.71967 12.2803C5.57902 12.1397 5.5 11.9489 5.5 11.75V7H0.75C0.551088 7 0.360322 6.92098 0.21967 6.78033C0.0790177 6.63968 0 6.44891 0 6.25C0 6.05109 0.0790177 5.86032 0.21967 5.71967C0.360322 5.57902 0.551088 5.5 0.75 5.5H5.5V0.75C5.5 0.551088 5.57902 0.360322 5.71967 0.21967C5.86032 0.0790177 6.05109 0 6.25 0C6.44891 0 6.63968 0.0790177 6.78033 0.21967C6.92098 0.360322 7 0.551088 7 0.75V5.5H11.75C11.9489 5.5 12.1397 5.57902 12.2803 5.71967C12.421 5.86032 12.5 6.05109 12.5 6.25Z" fill="white"/>
                                                                    </svg> ADD
                                                                </button>

                                                                <div class="rm_qty_box" style="display:none;">
                                                                    <button type="button" class="rm_qty_btn rm_qty_minus">
                                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                            <path d="M14.0303 8.53033C14.171 8.38968 14.25 8.19891 14.25 8C14.25 7.80109 14.171 7.61032 14.0303 7.46967C13.8897 7.32902 13.6989 7.25 13.5 7.25L7.25 7.25205L2.5 7.25C2.30109 7.25 2.11032 7.32902 1.96967 7.46967C1.82902 7.61032 1.75 7.80109 1.75 8C1.75 8.19891 1.82902 8.38968 1.96967 8.53033C2.11032 8.67098 2.30109 8.75 2.5 8.75H7.25H13.5C13.6989 8.75 13.8897 8.67098 14.0303 8.53033Z" fill="#373636"/>
                                                                        </svg>
                                                                    </button>

                                                                    <input type="text" step="1" min="0" class="rm_qty_field">

                                                                    <button type="button" class="rm_qty_btn rm_qty_plus">
                                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                            <path d="M14.25 8C14.25 8.19891 14.171 8.38968 14.0303 8.53033C13.8897 8.67098 13.6989 8.75 13.5 8.75H8.75V13.5C8.75 13.6989 8.67098 13.8897 8.53033 14.0303C8.38968 14.171 8.19891 14.25 8 14.25C7.80109 14.25 7.61032 14.171 7.46967 14.0303C7.32902 13.8897 7.25 13.6989 7.25 13.5V8.75H2.5C2.30109 8.75 2.11032 8.67098 1.96967 8.53033C1.82902 8.38968 1.75 8.19891 1.75 8C1.75 7.80109 1.82902 7.61032 1.96967 7.46967C2.11032 7.32902 2.30109 7.25 2.5 7.25H7.25V2.5C7.25 2.30109 7.32902 2.11032 7.46967 1.96967C7.61032 1.82902 7.80109 1.75 8 1.75C8.19891 1.75 8.38968 1.82902 8.53033 1.96967C8.67098 2.11032 8.75 2.30109 8.75 2.5V7.25H13.5C13.6989 7.25 13.8897 7.32902 14.0303 7.46967C14.171 7.61032 14.25 7.80109 14.25 8Z" fill="#343330"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <label class="mobile_label" style="display:none;">Total Price</label>
                                                            <div class="total_item_price"></div>
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

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </form>

    </div>

    <div id="order_popup" class="order_popup" style="display:none;">
        <div class="order_popup_box">
            <div class="order_popup_inner">
                <div class="order_tabs_top">
                    <ul class="order_tabs_ul">
                        <li class="order_tabs_li active" data-filter="order_tea_coffee">Beverage Selection</li>
                        <li class="order_tabs_li" data-filter="order_meal">Meal Preferences</li>
                        <li class="order_tabs_li" data-filter="order_refreshments">Snacks & Light Bites</li>
                    </ul>
                </div>
                <div class="order_tabs_content_bottom">

                    <div id="order_tea_coffee" class="order_tab_content">
                        <div class="order_tab_left">
                            <div class="table_res">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Seat Name</th>
                                            <th>Beverage Selection</th>
                                            <th>Additional Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="order_meal" class="order_tab_content" style="display:none;">
                        <div class="order_tab_left">
                            <div class="table_res">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Seat Name</th>
                                            <th>Choice of Bread</th>
                                            <th>Food Allergies</th>
                                            <th>Dietary Requirements</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="order_refreshments" class="order_tab_content" style="display:none;">
                        <div class="order_tab_left">
                            <div class="table_res">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Seat Name</th>
                                            <th>Orders</th>
                                            <th>Item Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                
            </div>
            <div class="order_popup_inner_right">
                <h2>Paid Items (Snacks / Light Bites)</h2>
                <div class="order_tab_right order_ref_charges">
                    <table>
                        <thead>
                            <tr>
                                <th>Particulars</th>
                                <th>Charges (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th colspan="2">Order Summary</th>
                            </tr>
                            
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Subtotal</th>
                                <th class="subtotal_price"></th>
                            </tr>
                            <tr>
                                <th>CGST (9%)</th>
                                <th class="cgts_price"></th>
                            </tr>
                            <tr>
                                <th>SGST (9%)</th>
                                <th class="sgts_price"></th>
                            </tr>
                            <!-- <tr>
                                <th>GST</th>
                                <th class="gts_price"></th>
                            </tr> -->
                            <tr>
                                <th>Final Total:</th>
                                <th class="total_price"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <span class="order_popup_close" data-fancybox-close="">
                <svg width="37" height="37" viewBox="0 0 37 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_4908_1753)">
                <path d="M18.3327 35.6654C27.9053 35.6654 35.6654 27.9053 35.6654 18.3327C35.6654 8.76011 27.9053 1 18.3327 1C8.76011 1 1 8.76011 1 18.3327C1 27.9053 8.76011 35.6654 18.3327 35.6654Z" stroke="#1763B9" stroke-width="2"/>
                <path d="M22.6659 14L13.9995 22.6663M13.9995 14L22.6659 22.6663" stroke="#1763B9" stroke-width="2" stroke-linecap="round"/>
                </g>
                <defs>
                <clipPath id="clip0_4908_1753">
                <rect width="37" height="37" fill="white"/>
                </clipPath>
                </defs>
                </svg>
            </span>
        </div>
    </div>

    <div id="reset_confirm_popup" style="display:none; max-width:400px;">
        <h3>Confirm Reset</h3>
        <p>This will remove all selected seats and orders. Continue?</p>

        <div style="margin-top:20px; text-align:right;">
            <button type="button" class="btn_cancel">Cancel</button>
            <button type="button" class="btn_confirm_reset">Yes, Reset</button>
        </div>
        <span class="order_popup_close" data-fancybox-close="">
            <svg width="37" height="37" viewBox="0 0 37 37" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0_4908_1753)">
            <path d="M18.3327 35.6654C27.9053 35.6654 35.6654 27.9053 35.6654 18.3327C35.6654 8.76011 27.9053 1 18.3327 1C8.76011 1 1 8.76011 1 18.3327C1 27.9053 8.76011 35.6654 18.3327 35.6654Z" stroke="#1763B9" stroke-width="2"></path>
            <path d="M22.6659 14L13.9995 22.6663M13.9995 14L22.6659 22.6663" stroke="#1763B9" stroke-width="2" stroke-linecap="round"></path>
            </g>
            <defs>
            <clipPath id="clip0_4908_1753">
            <rect width="37" height="37" fill="white"></rect>
            </clipPath>
            </defs>
            </svg>
        </span>
    </div>

    <?php
    /*}else{
        return;
    }*/
        
    ?>
</div>

<style>

.fancybox__backdrop {
	background-color: #E3E3E34F;
}

.fancybox__viewport.is-draggable {
	cursor: auto;
}

.fancybox__viewport .fancybox__slide {
	transform: none !important;
}

</style>

<script src="<?php echo get_stylesheet_directory_uri(); ?>/includes/webapp.js?v=<?php echo time(); ?>"></script>