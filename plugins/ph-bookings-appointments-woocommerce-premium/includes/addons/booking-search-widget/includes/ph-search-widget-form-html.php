<div class="ph_booking_search_widget_wrapper">
	<form action="" id="ph_booking_searchform" class="ph_booking_searchform" method="get" autocomplete="off">
		<input type="hidden" id="s" name="s" placeholder="Enter keywords" required>
		<input type="hidden" id="ph_book_search_filter_date_and_time" name="ph_book_search_filter_date_and_time" value="<?php echo $filter_date_and_time; ?>">
		<input type="hidden" id="ph_book_search_filter_asset_name" name="ph_book_search_filter_asset_name" value="<?php echo $filter_asset_name; ?>">
		<input type="hidden" id="ph_search_asset_name" name="ph_search_asset_name">
		<input type="hidden" id="ph_book_search_filter_number_of_participant" name="ph_book_search_filter_number_of_participant" value="<?php echo $filter_number_of_participant; ?>">
		<input type="hidden" id="ph_book_search_participants" name="ph_book_search_participants">
		<input type="hidden" id="ph_checkin_day_checked" name="ph_checkin_day_checked" value="<?php echo $restrict_checkin_checkbox; ?>">
		<input type="hidden" id="ph_checkout_day_checked" name="ph_checkout_day_checked" value="<?php echo $restrict_checkout_checkbox; ?>">
		<input type="hidden" id="ph_book_search_date_format" name="ph_book_search_date_format" value="<?php echo $filter_date_format; ?>">
		<input type="hidden" id="show_partially_unavailable" name="show_partially_unavailable" value="<?php echo $show_partially_unavailable; ?>">

		<?php
		if ( is_product() ) {
			echo "<input type='hidden' id='ph_booking_product_page' name='ph_booking_product_page'>";
		}
		?>


		<input type="hidden" id="ph_from_date_text" name="ph_from_date_text" value="<?php echo $from_date_text; ?>">
		<input type="hidden" id="ph_to_date_text" name="ph_to_date_text" value="<?php echo $to_date_text; ?>">

		<!-- Title  -->
		<input type="hidden" id="ph_display_title" name="ph_display_title" value="<?php echo $title; ?>">
		<!-- Fixed Date -->
		<input type="hidden" id="ph_fixed_date" name="ph_fixed_date" value="<?php echo $fixed_date; ?>">
		<input type="hidden" id="ph_asset_label" name="ph_asset_label" value="<?php echo $filter_asset_name_label; ?>">
		<input type="hidden" id="ph_participant_lable" name="ph_participant_lable" value="<?php echo $filter_number_of_participant_label; ?>">
	
		<!-- Border Style -->
		<input type="hidden" id="ph_border_style" name="ph_border_style" value="<?php echo $border_style; ?>">
		<input type="hidden" id="ph_border_width" name="ph_border_width" value="<?php echo $border_width; ?>">
		<input type="hidden" id="ph_border_color" name="ph_border_color" value="<?php echo $border_color; ?>">
		<input type="hidden" id="ph_border_radius" name="ph_border_radius" value="<?php echo $border_radius; ?>">	

		<input type="hidden" id="ph_product_view" name="ph_product_view" value="<?php echo $product_view; ?>">	


		<?php if ( $restrict_checkin_checkbox ) { ?>
			<input type="hidden" id="ph_checkin_day_related" name="ph_checkin_day_related" value="<?php
			if ( ! empty( $restrict_checkin ) ) {
				echo implode( ' ', $restrict_checkin );} ?>">
		<?php } ?>

		<?php if ( $restrict_checkout_checkbox ) { ?>
			<input type="hidden" id="ph_checkout_day_related" name="ph_checkout_day_related" value="<?php
			if ( ! empty( $restrict_checkout ) ) {
				echo implode( ' ', $restrict_checkout );}?>">
			<?php
		}
		if ( $filter_date_and_time ) {
			$display_settings            = get_option( 'ph_bookings_display_settigns' );
			$time_zone_conversion_enable = isset( $display_settings['time_zone_conversion_enable'] ) ? $display_settings['time_zone_conversion_enable'] : 'no';
			?>
			<input type="hidden" id="ph_book_search_time_format" name="ph_book_search_time_format" value="<?php echo get_option( 'time_format' ); ?>">
			<input type="hidden" id="ph_book_search_interval" name="ph_book_search_interval" value="<?php echo $filter_interval_time; ?>">
			<input type="hidden" id="ph_book_search_range_from" name="ph_book_search_range_from" value="<?php echo $filter_time_pick_from; ?>">
			<input type="hidden" id="ph_book_search_range_to" name="ph_book_search_range_to" value="<?php echo $filter_time_pick_to; ?>">

			<input type="hidden" id="ph_book_search_daily_range_from" name="ph_book_search_daily_range_from" value="<?php echo $filter_daily_range_from; ?>">
			<input type="hidden" id="ph_book_search_daily_range_to" name="ph_book_search_daily_range_to" value="<?php echo $filter_daily_range_to; ?>">
			<input type="hidden" id="ph_book_search_filter_time_format" name="ph_book_search_filter_time_format" value="<?php echo $filter_time_format; ?>">

			<input type="hidden" id="ph_book_search_time_zone_conversion_enable" name="ph_book_search_time_zone_conversion_enable" value="<?php echo $time_zone_conversion_enable; ?>">
			<?php
		}
		?>

		<!-- Widget Container -->
		<div class="ph_book_search_widget_container" id="ph_book_search_widget_container">

			<!-- From Date -->
			<div class="ph_book_search_date_container" id="ph_book_search_date_container">
				<input type="text" id="ph_book_search_from" class="search-field ph_book_search_from" placeholder="<?php echo __( $from_date_text, 'bookings-and-appointments-for-woocommerce' ); ?>" name="book_search_from" autocomplete="off" readonly>
				<?php if ( $filter_date_and_time ) { ?>
					<!-- <input type="text" maxlength="5" id="ph_book_search_time_from" class="search-field ph_book_search_time_from" placeholder="<?php echo __( $from_time_text, 'bookings-and-appointments-for-woocommerce' ); ?>" name="book_search_time_from"> -->
				<?php } ?>
			</div>
			<!-- To Date -->
			<div class="ph_book_search_date_container1" id="ph_book_search_date_container1">
				<input type="text" id="ph_book_search_to" class="search-field ph_book_search_to" placeholder="<?php echo __( $to_date_text, 'bookings-and-appointments-for-woocommerce' ); ?>" name="book_search_to" autocomplete="off"  readonly>
				<?php if ( $filter_date_and_time ) { ?>
					<!-- <input type="text" maxlength="5" id="ph_book_search_time_to" class="search-field ph_book_search_time_to" placeholder="<?php echo __( $to_time_text, 'bookings-and-appointments-for-woocommerce' ); ?>" name="book_search_time_to"> -->
				<?php } ?>
			</div>
			<!-- Asset -->
			<?php if ( $filter_asset_name ) { ?>
				<div class="ph_book_search_asset_name_container" id="ph_book_search_asset_name_container">
					<input type="text" id="ph_asset_name" value="<?php echo __( $filter_asset_name_label, 'bookings-and-appointments-for-woocommerce' ); ?> " autocomplete="off"/>
					<div class="ph_popup" id="ph_book_search_asset_list">
						<?php
						$asset_name = get_option( 'ph_booking_settings_assets' );
						echo '<div title="' . esc_attr( $filter_asset_name_label ) . '" class="ph_book_search_asset_item" style="color:darkgrey;" data-value="default">' . esc_html( $filter_asset_name_label ) . '</div>';
						foreach ( $asset_name['_phive_booking_assets'] as $key => $name ) {
							$key = esc_attr( $key );
							echo '<div title="' . $name['ph_booking_asset_name'] . '" class="ph_book_search_asset_item" data-value="' . $key . '">' . $name['ph_booking_asset_name'] . '</div>';
						}
						?>
					</div>
				</div>
				<!-- Participant -->
				<?php
			}
			if ( $filter_number_of_participant ) {
				?>
				<div class="ph_book_search_number_of_participants_container" id="ph_book_search_number_of_participants_container">
					<input type="text" class="ph_book_search_number_of_participants_buttons" value="<?php echo esc_html( $filter_number_of_participant_label ); ?>" autocomplete="off" />
					<span id="participant_count_display" style="margin-left: 85%!important;"></span>
					<div id="ph_book_search_number_of_participants_button" class="ph_popup">
						<div class="ph_content">
							<div class="participant-groups">
								<?php
								foreach ( $participant_rules as $rule ) {
									?>
									<div class="ph_participant-group" data-participant="<?php echo esc_attr( $rule ); ?>">
										<label for="<?php echo esc_attr( $rule ); ?>">
											<?php echo esc_html( $rule ); ?>:
										</label>
										<div class="ph_controls">
											<button type="button" class="ph-booking-participant-minus minus" data-target="<?php echo esc_attr( $rule ); ?>">-</button>
											<input type="number" id="<?php echo esc_attr( $rule ); ?>"  name="participants[<?php echo esc_attr( $rule ); ?>]" min="0" max="10" value="0" readonly style="width: 50%;"/>
											<button type="button" class="ph-booking-participant-plus" data-target="<?php echo esc_attr( $rule ); ?>">+</button>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
			<!-- Search and clear button container -->
			<div class="ph_book_search_button_container" id="ph_book_search_button_container">
				<!-- Search -->
				<button type="submit" class="woocommerce-Button button page-title-action search_icon ph_booking_searchsubmit <?php echo esc_attr( $search_class ); ?>" id="ph_booking_searchsubmit" alt="Search"
					style="<?php echo 'color:' . $clear_text_color . ' !important; background-color:' . $search_background_color . '!important;' . ( $disable_clear_button ? ';' : ';' ); ?>">
					<?php echo __( $search_text, 'bookings-and-appointments-for-woocommerce' ); ?>
				</button>&nbsp;&nbsp;
				<!-- Clear -->
				<?php if ( ! $disable_clear_button ) { ?>
					<button type="button" class="woocommerce-Button button page-title-action ph_booking_clear <?php echo esc_attr( $clear_class ); ?>" id="ph_booking_clear" alt="Clear"
						style="color:<?php echo $clear_text_color; ?> !important ;background-color: <?php echo $clear_background_color; ?>!important;">
						<?php echo __( $clear_text, 'bookings-and-appointments-for-woocommerce' ); ?>
					</button>
				<?php } ?>
			</div>
		</div>
	</form>
</div>
