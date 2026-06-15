<!-- Display Settings -->
<div>
	<p id="<?php echo esc_attr( $this->get_field_id( 'display_settings' ) ); ?>">
		<?php _e( 'Display Settings', 'bookings-and-appointments-for-woocommerce' ); ?><span class="dashicons dashicons-arrow-down-alt2" style="float: right;"></span>
	</p>
	<div id="<?php echo $this->get_field_id( 'display_settings_panel' ); ?>" style="display:none;">
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Search Widget Title:', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				placeholder="<?php _e( 'Booking Search', 'bookings-and-appointments-for-woocommerce' ); ?>"
				value="<?php echo $title; ?>" />
		</p>

		<!-- Resrtict widget on certain pages -->
		<p>
			<input name="<?php echo esc_attr( $this->get_field_name( 'restrict_widget_checkbox' ) ); ?>" id="<?php echo $this->get_field_id( 'restrict_widget_checkbox' ); ?>" type="checkbox" value="1" <?php checked( '1', $restrict_widget_checkbox ); ?> />
			<label for="<?php echo $this->get_field_id( 'restrict_widget_checkbox' ); ?>"><?php _e( 'Display Search Widget Visibility', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			<div style="overflow-x: auto;">
				<table class="form-table" style="border: 1px solid #dddddd;" id="<?php echo $this->get_field_id( 'restrict_widget_option' ); ?>">
					<tr>
						<?php
						$pages = array(
							'home'        => __( 'Home Page', 'bookings-and-appointments-for-woocommerce' ),
							'custom_home' => __( 'Custom Home Page', 'bookings-and-appointments-for-woocommerce' ),
							'shop'        => __( 'Shop Page', 'bookings-and-appointments-for-woocommerce' ),
							'product_cat' => __( 'Product Category Page', 'bookings-and-appointments-for-woocommerce' ),
						);

																$restrict_widget = empty( $restrict_widget ) ? array() : $restrict_widget;
						foreach ( $pages as $key => $value ) {
							?>
							<td style="padding:1em 1.1em;">
								<label for="<?php echo esc_attr( $this->get_field_id( 'restrict_widget' ) ); ?>"><?php echo $value; ?></label>
								<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'restrict_widget' ) ); ?>[]" type="checkbox" value="<?php echo $key; ?>" <?php checked( '1', in_array( $key, $restrict_widget ) ); ?> />
							</td>
							<?php
						}
						?>
					</tr>
				</table>
			</div>
		</p>
		<!-- Clear Button -->
		<p>
			<input type="checkbox" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'disable_clear_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'disable_clear_button' ) ); ?>" value="1" <?php checked( '1', $disable_clear_button ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'disable_clear_button' ) ); ?>"><?php _e( 'Hide Clear Search button', 'bookings-and-appointments-for-woocommerce' ); ?></label>
		</p>
		<!-- Ticket #174377 customer choosen date format -->
		<p>
			<?php
			$date_format_option = array(
				"d-m-Y",     
				"Y-m-d",     
				"d/m/Y",     
				"Y/m/d",     
				"d.m.Y",     
				"D, d M Y",
				"M j, Y",    
				"F j, Y",    
				"M j Y", 
			);

			?>
			<label for="<?php echo esc_attr( $this->get_field_id( 'filter_date_format' ) ); ?>"><?php _e( 'Date Format', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter_date_format' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_date_format' ) ); ?>">

				<?php
				foreach ( $date_format_option as $option ) {
					if ( $option == $filter_date_format ) {
						echo '<option value="' . $option . '" selected >' . $option . '</option>';
					} else {
						echo '<option value="' . $option . '" >' . $option . '</option>';
					}
				}
				?>
			</select>
		</p>
		<p>
			<input type="checkbox" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'enable_book_now_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'enable_book_now_button' ) ); ?>" value="1" <?php checked( '1', $enable_book_now_button ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'enable_book_now_button' ) ); ?>"><?php _e( 'Display Book Now button', 'bookings-and-appointments-for-woocommerce' ); ?></label>
		</p>
		<p>
			<input type="checkbox" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'enable_view_product_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'enable_view_product_button' ) ); ?>" value="1" <?php checked( '1', $enable_view_product_button ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'enable_view_product_button' ) ); ?>"><?php _e( 'Display View Product button', 'bookings-and-appointments-for-woocommerce' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'view_product_button_text' ); ?>"><?php _e( 'Button Title:', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'view_product_button_text' ); ?>" name="<?php echo $this->get_field_name( 'view_product_button_text' ); ?>" type="text"
				placeholder="<?php _e( 'View Product', 'bookings-and-appointments-for-woocommerce' ); ?>"
				value="<?php echo $view_product_button_text; ?>" />
		</p>
	</div>
</div>


<!-- Search Filters -->
<div>
	<p id="<?php echo esc_attr( $this->get_field_id( 'search_filters' ) ); ?>">
		<?php _e( 'Search Filters', 'bookings-and-appointments-for-woocommerce' ); ?><span class="dashicons dashicons-arrow-down-alt2" style="float: right;"></span>
	</p>
	<div id="<?php echo $this->get_field_id( 'search_filters_panel' ); ?>" style="display:none;">

		<p>
			<input type="checkbox" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'disable_clear_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fixed_date' ) ); ?>" value="1" <?php checked( '1', $fixed_date ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'fixed_date' ) ); ?>"><?php _e( 'Filter by Fixed Date', 'bookings-and-appointments-for-woocommerce' ); ?></label>
		</p>

		<!-- Restrict checkin -->
		<p>
			<input name="<?php echo esc_attr( $this->get_field_name( 'restrict_checkin_checkbox' ) ); ?>" id="<?php echo $this->get_field_id( 'restrict_checkin_checkbox' ); ?>" type="checkbox" value="1" <?php checked( '1', $restrict_checkin_checkbox ); ?> />
			<label for="<?php echo $this->get_field_id( 'restrict_checkin_checkbox' ); ?>"><?php _e( 'Filter by Day (For Checkin)', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			<div style="overflow-x: auto;">
				<table class="form-table" style="border: 1px solid #dddddd;" id="<?php echo $this->get_field_id( 'restrict_checkin_option' ); ?>">
					<tr>
						<?php
						$days                                      = array(
							__( 'Monday', 'bookings-and-appointments-for-woocommerce' ),
							__( 'Tuesday', 'bookings-and-appointments-for-woocommerce' ),
							__( 'Wednesday', 'bookings-and-appointments-for-woocommerce' ),
							__( 'Thursday', 'bookings-and-appointments-for-woocommerce' ),
							__( 'Friday', 'bookings-and-appointments-for-woocommerce' ),
							__( 'Saturday', 'bookings-and-appointments-for-woocommerce' ),
							__( 'Sunday', 'bookings-and-appointments-for-woocommerce' ),
						);
																$j = 1;
																$restrict_checkin = empty( $restrict_checkin ) ? array() : $restrict_checkin;
						foreach ( $days as $value ) {
							?>
							<td style="padding:1em 1.1em;">
								<label for="<?php echo esc_attr( $this->get_field_id( 'restrict_checkin' ) ); ?>"><?php echo $value; ?></label>
								<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'restrict_checkin' ) ); ?>[]" type="checkbox" value="<?php echo $value; ?>" <?php checked( '1', in_array( $value, $restrict_checkin ) ); ?> />
							</td>
							<?php
																	++$j;
						}
						?>
					</tr>
				</table>
			</div>
		</p>
		<!-- Restrict checkout -->
		<p>
			<input name="<?php echo esc_attr( $this->get_field_name( 'restrict_checkout_checkbox' ) ); ?>" id="<?php echo $this->get_field_id( 'restrict_checkout_checkbox' ); ?>" type="checkbox" value="1" <?php checked( '1', $restrict_checkout_checkbox ); ?> />
			<label for="<?php echo $this->get_field_id( 'restrict_checkout_checkbox' ); ?>"><?php _e( 'Filter by Day (For Checkout)', 'bookings-and-appointments-for-woocommerce' ); ?></label>
			<div style="overflow-x: auto;">
				<table class="form-table" style="border: 1px solid #dddddd;" id="<?php echo $this->get_field_id( 'restrict_checkout_option' ); ?>">
					<tr>
						<?php
						$i                 = 1;
						$restrict_checkout = empty( $restrict_checkout ) ? array() : $restrict_checkout;
						foreach ( $days as $value ) {
							?>
							<td style="padding:1em 1.1em;">
								<label for="<?php echo esc_attr( $this->get_field_id( 'restrict_checkout' ) ); ?>"><?php echo $value; ?></label>
								<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'restrict_checkout' ) ); ?>[]" type="checkbox" value="<?php echo $value; ?>" <?php checked( '1', in_array( $value, $restrict_checkout ) ); ?> />

							</td>
							<?php
							++$i;
						}
						?>
					</tr>
				</table>
			</div>
		</p>
		<!-- Range of date+time -->
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'filter_date_and_time' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_date_and_time' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $filter_date_and_time ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'filter_date_and_time' ) ); ?>"><?php _e( 'Filter by Time', 'bookings-and-appointments-for-woocommerce' ); ?></label>
		</p>
		<p id="hint">
			<span style='float:left;color:red;'><?php _e( '*Time range can not be used in booking search when timezone conversion is enabled.', 'bookings-and-appointments-for-woocommerce' ); ?></span></br><br>
		</p>
		<div id="<?php echo esc_attr( $this->get_field_id( 'filter_date_and_time_panel' ) ); ?>" style="border:solid darkgrey 0.1em; padding:0 0.6em;">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'filter_interval_time' ) ); ?>"><?php _e( 'Time Interval in Minutes', 'bookings-and-appointments-for-woocommerce' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter_interval_time' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_interval_time' ) ); ?>" type="number" value="<?php echo esc_attr( $filter_interval_time ); ?>" style="padding: 1.1em;" min="5" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'filter_daily_range_from' ) ); ?>"><?php _e( 'Start Time', 'bookings-and-appointments-for-woocommerce' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter_daily_range_from' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_daily_range_from' ) ); ?>" type="time" value="<?php echo esc_attr( $filter_daily_range_from ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'filter_daily_range_to' ) ); ?>"><?php _e( 'End Time', 'bookings-and-appointments-for-woocommerce' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter_daily_range_to' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_daily_range_to' ) ); ?>" type="time" value="<?php echo esc_attr( $filter_daily_range_to ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'filter_time_format' ) ); ?>"><?php _e( 'Time Format', 'bookings-and-appointments-for-woocommerce' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter_time_format' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_time_format' ) ); ?>">
					<option value="time_24hr" <?php selected( 'time_24hr', $filter_time_format ); ?>><?php _e( '24-Hour Format', 'bookings-and-appointments-for-woocommerce' ); ?></option>
					<option value="time_12hr" <?php selected( 'time_12hr', $filter_time_format ); ?>><?php _e( '12-Hour Format', 'bookings-and-appointments-for-woocommerce' ); ?></option>
				</select>
			</p>
		</div>

		<!-- FIlter by assets -->
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'filter_asset_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_asset_name' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $filter_asset_name ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'filter_asset_name' ) ); ?>"><?php _e( 'Filter by Asset Name', 'bookings-and-appointments-for-woocommerce' ); ?></label>
		</p>
		<!-- Filter by number of participants -->
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'filter_number_of_participant' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'filter_number_of_participant' ) ); ?>"
				type="checkbox"
				value="1"
				<?php checked( '1', $filter_number_of_participant ); ?>
				class="toggle-participant-rules" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'filter_number_of_participant' ) ); ?>">
				<?php _e( 'Filter by Participants', 'bookings-and-appointments-for-woocommerce' ); ?>
			</label>
		</p>

		<div id="ph_participant_dropdown" style="display: flex; flex-direction: column; align-items: stretch;">
			<select data-placeholder="<?php _e( 'Select Participants', 'bookings-and-appointments-for-woocommerce' ); ?>" class="ph_multi_select_participant" name="<?php echo esc_attr( $this->get_field_name( 'participant_rules' ) ); ?>[]"
				id="<?php echo esc_attr( $this->get_field_id( 'participant_rules' ) ); ?>"
				multiple="multiple">
				<?php foreach ( $rule_types as $rule_type ) : ?>
					<option value="<?php echo esc_attr( $rule_type ); ?>"
						<?php echo in_array( $rule_type, (array) $participant_rules ) ? 'selected' : ''; ?>>
						<?php echo esc_html( $rule_type ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<!-- Show the Partially unavailable bookings -->
		<!-- <p>
			<input type="checkbox" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_partially_unavailable' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_partially_unavailable' ) ); ?>" value="1" <?php checked( '1', $show_partially_unavailable ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_partially_unavailable' ) ); ?>"><?php _e( 'Show Products Partially Unavailable Between The Search Interval', 'bookings-and-appointments-for-woocommerce' ); ?></label>
		</p> -->
	</div>
</div>

<!-- Customize Buttons -->
<div>
	<p id="<?php echo esc_attr( $this->get_field_id( 'customize_buttons' ) ); ?>">
		<?php _e( 'Customize Style', 'bookings-and-appointments-for-woocommerce' ); ?><span class="dashicons dashicons-arrow-down-alt2" style="float: right;"></span>
	</p>
	<div style="overflow-x: auto;">
		<table id="<?php echo esc_attr( $this->get_field_id( 'customize_buttons_panel' ) ); ?>" style="display:none;">
			<thead>
				<th>
					<?php _e( 'Buttons', 'bookings-and-appointments-for-woocommerce' ); ?>
				</th>
				<th>
					<?php _e( 'Class Name', 'bookings-and-appointments-for-woocommerce' ); ?>
				</th>
				<th>
					<?php _e( 'Display Text', 'bookings-and-appointments-for-woocommerce' ); ?>
				</th>
				<th>
					<?php _e( 'Display Text Colour', 'bookings-and-appointments-for-woocommerce' ); ?>
				</th>
				<th>
					<?php _e( 'Button Colour', 'bookings-and-appointments-for-woocommerce' ); ?>
				</th>
			</thead>
			<tr>
				<td>
					<?php _e( 'Search', 'bookings-and-appointments-for-woocommerce' ); ?>
				</td>
				<td>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'search_class' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_class' ) ); ?>" placeholder="<?php _e( 'Class', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $search_class ); ?>">
				</td>
				<td>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'search_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_text' ) ); ?>" placeholder="<?php _e( 'Search', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $search_text ); ?>" minlength="3" maxlength="10">
				</td>
				<td>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'search_text_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_text_color' ) ); ?>" placeholder="white" value="<?php echo esc_attr( $search_text_color ); ?>">
				</td>
				<td>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'search_background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_background_color' ) ); ?>" placeholder="#2271b1" value="<?php echo esc_attr( $search_background_color ); ?>">
				</td>
			</tr>
			<tr>
				<td>
					<?php _e( 'Clear', 'bookings-and-appointments-for-woocommerce' ); ?>
				</td>
				<td>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'clear_class' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'clear_class' ) ); ?>" placeholder="<?php _e( 'Class', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $clear_class ); ?>">
				</td>
				<td>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'clear_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'clear_text' ) ); ?>" placeholder="<?php _e( 'Clear', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $clear_text ); ?>" minlength="3" maxlength="10">
				</td>
				<td>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'clear_text_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'clear_text_color' ) ); ?>" placeholder="white" value="<?php echo esc_attr( $clear_text_color ); ?>">
				</td>
				<td>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'clear_background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'clear_background_color' ) ); ?>" placeholder="#2271b1" value="<?php echo esc_attr( $clear_background_color ); ?>">
				</td>
			</tr>
			<tfoot>
				<tr>
					<thead>
						<th>
							<?php _e( 'Border', 'bookings-and-appointments-for-woocommerce' ); ?>
						</th>
						<th>
							<?php _e( 'Border Width(px)', 'bookings-and-appointments-for-woocommerce' ); ?>
						</th>
						<th>
							<?php _e( 'Border Colour', 'bookings-and-appointments-for-woocommerce' ); ?>
						</th>
						<th>
							<?php _e( 'Border Style', 'bookings-and-appointments-for-woocommerce' ); ?>
						</th>
						<th>
							<?php _e( 'Border Radius(px)', 'bookings-and-appointments-for-woocommerce' ); ?>
						</th>
					</thead>
				</tr>
				<tr>
					<td>
						<?php _e( 'Search Bar', 'bookings-and-appointments-for-woocommerce' ); ?>
					</td>
					<td>
						<input class="widefat" type="number" id="<?php echo esc_attr( $this->get_field_id( 'border_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_width' ) ); ?>" placeholder="<?php _e( 'Border Width', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $border_width ); ?>">
					</td>
					<td>
						<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'border_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_color' ) ); ?>" placeholder="<?php _e( 'Border Color', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $border_color ); ?>">
					</td>
					<td>
						<!-- <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'border_style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_style' ) ); ?>" placeholder="<?php _e( 'Border Style', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $border_style ); ?>"> -->
						<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'border_style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_style' ) ); ?>">
							<option value="none" <?php selected( $border_style, 'none' ); ?>>None</option>
							<option value="solid" <?php selected( $border_style, 'solid' ); ?>>Solid</option>
							<option value="dashed" <?php selected( $border_style, 'dashed' ); ?>>Dashed</option>
							<option value="dotted" <?php selected( $border_style, 'dotted' ); ?>>Dotted</option>
							<option value="double" <?php selected( $border_style, 'double' ); ?>>Double</option>
							<option value="groove" <?php selected( $border_style, 'groove' ); ?>>Groove</option>
							<option value="ridge" <?php selected( $border_style, 'ridge' ); ?>>Ridge</option>
							<option value="inset" <?php selected( $border_style, 'inset' ); ?>>Inset</option>
							<option value="outset" <?php selected( $border_style, 'outset' ); ?>>Outset</option>
							<option value="hidden" <?php selected( $border_style, 'hidden' ); ?>>Hidden</option>
							<option value="inherit" <?php selected( $border_style, 'inherit' ); ?>>Inherit</option>
							<option value="initial" <?php selected( $border_style, 'initial' ); ?>>Initial</option>
							<option value="revert" <?php selected( $border_style, 'revert' ); ?>>Revert</option>
						</select>
					</td>
					<td>
						<input class="widefat" type="number" id="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_radius' ) ); ?>" placeholder="<?php _e( 'Border Radius', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $border_radius ); ?>">
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
<!-- Customize labels -->
<div>
	<p id="<?php echo esc_attr( $this->get_field_id( 'customize_labels' ) ); ?>">
		<?php _e( 'Customize Filter Labels', 'bookings-and-appointments-for-woocommerce' ); ?><span class="dashicons dashicons-arrow-down-alt2" style="float: right;"></span>
	</p>
	<table id="<?php echo esc_attr( $this->get_field_id( 'customize_labels_panel' ) ); ?>" style="display:none;">
		<thead>
			<th>
				<?php _e( 'Filters', 'bookings-and-appointments-for-woocommerce' ); ?>
			</th>
			<th>
				<?php _e( 'Filter Display Text', 'bookings-and-appointments-for-woocommerce' ); ?>
			</th>
		</thead>
		<tr>
			<td>
				<?php _e( 'From Date', 'bookings-and-appointments-for-woocommerce' ); ?>
			</td>
			<td>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'from_date_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'from_date_text' ) ); ?>" type="text" placeholder="<?php _e( 'From', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $from_date_text ); ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e( 'To Date', 'bookings-and-appointments-for-woocommerce' ); ?>
			</td>
			<td>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'to_date_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'to_date_text' ) ); ?>" type="text" placeholder="<?php _e( 'To', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $to_date_text ); ?>" />
			</td>
		</tr>
		<!-- <tr>
			<td>
				<?php _e( 'From Time', 'bookings-and-appointments-for-woocommerce' ); ?>
			</td>
			<td>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'from_time_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'from_time_text' ) ); ?>" type="text" placeholder="<?php _e( 'From Time', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $from_time_text ); ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e( 'To Time', 'bookings-and-appointments-for-woocommerce' ); ?>
			</td>
			<td>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'to_time_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'to_time_text' ) ); ?>" type="text" placeholder="<?php _e( 'To Time', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $to_time_text ); ?>" />
			</td>
		</tr> -->
		<tr>
			<td>
				<?php _e( 'Asset Name', 'bookings-and-appointments-for-woocommerce' ); ?>
			</td>
			<td>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter_asset_name_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_asset_name_label' ) ); ?>" placeholder="<?php _e( 'Select an asset', 'bookings-and-appointments-for-woocommerce' ); ?>" type="text" value="<?php echo esc_attr( $filter_asset_name_label ); ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e( 'Number of Participants', 'bookings-and-appointments-for-woocommerce' ); ?>
			</td>
			<td>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter_number_of_participant_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter_number_of_participant_label' ) ); ?>" type="text" placeholder="<?php _e( 'Participant Count', 'bookings-and-appointments-for-woocommerce' ); ?>" value="<?php echo esc_attr( $filter_number_of_participant_label ); ?>" />
			</td>
		</tr>
	</table>
</div>
<br>
<div class="button-primary woocommerce-save-button" id="<?php echo esc_attr( $this->get_field_id( 'reset_to_default' ) ); ?>"><?php _e( 'Reset to default', 'bookings-and-appointments-for-woocommerce' ); ?></div>