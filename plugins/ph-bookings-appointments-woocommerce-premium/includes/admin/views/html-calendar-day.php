<div class="wrap woocommerce">
	<h2><?php _e( 'Calendar', 'bookings-and-appointments-for-woocommerce' ); ?><a href="admin.php?page=add-booking" class="page-title-action"><?php _e( 'Add Booking', 'bookings-and-appointments-for-woocommerce' ); ?></a></h2>

	<form method="get" id="mainform" enctype="multipart/form-data" class="ph_bookings_calendar_form">
		<input type="hidden" name="page" value="ph-booking-calendar" />
		<input type="hidden" name="view" value="<?php echo esc_attr( $view ); ?>" />
		<input type="hidden" name="tab" value="calendar" />
		<div class="tablenav">
			<div class="text-center admin-calendar-views">
				<div class="admin-calendar-btn" style="background-color:#f1f1f1;">
					<a class="month" style="color: #555;" href="<?php echo esc_url( add_query_arg( 'view', 'month' ) ); ?>"><?php _e( 'Month', 'bookings-and-appointments-for-woocommerce' ); ?></a>
				</div>
				<div class="admin-calendar-btn">
					<a class="day" href="<?php echo esc_url( add_query_arg( 'view', 'day' ) ); ?>"><?php _e( 'Day', 'bookings-and-appointments-for-woocommerce' ); ?></a>
				</div>
			</div>

			<div class="ph-bottom-filters">
				<div class="date_selector">
					<a class="prev dashicons dashicons-arrow-left-alt2" href="<?php echo esc_url( add_query_arg( 'calendar_day', date_i18n( 'Y-m-d', strtotime( '-1 day', strtotime( $day ) ) ) ) ); ?>"></a>
					<div>
						<input type="text" name="calendar_day" class="calendar_day" placeholder="yyyy-mm-dd" value="<?php echo esc_attr( $day ); ?>" />
					</div>
					<a class="next next dashicons dashicons-arrow-right-alt2" href="<?php echo esc_url( add_query_arg( 'calendar_day', date_i18n( 'Y-m-d', strtotime( '+1 day', strtotime( $day ) ) ) ) ); ?>"></a>
				</div>
				<div class="filters">
					<select id="ph-calendar-bookings-filter" name="filter_bookings" class="wc-enhanced-select" style="width:200px">
						<option value=""><?php _e( 'Choose Product', 'bookings-and-appointments-for-woocommerce' ); ?></option>
						<?php
						$product_filters = $this->product_filters();
						if ( $product_filters ) :
						?>
							<?php foreach ( $product_filters as $filter_id => $filter_name ) : ?>
								<option value="<?php echo $filter_id; ?>" <?php selected( $product_filter, $filter_id ); ?>><?php echo $filter_name; ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
					<select id="ph-calendar-bookings-filter-status" name="filter_bookings_by_status" class="wc-enhanced-select" style="width:200px">
						<option value=""><?php _e( 'All Status', 'bookings-and-appointments-for-woocommerce' ); ?></option>
						<?php
						$booking_status_filter = $this->booking_status_filter();
						if ( $booking_status_filter ) :
						?>
							<?php foreach ( $booking_status_filter as $filter_id => $filter_name ) : ?>
								<option value="<?php echo $filter_id; ?>" <?php selected( $filter_bookings_by_status, $filter_id ); ?>><?php echo $filter_name; ?></option>
							<?php endforeach; ?>
						<?php 
						endif; 
						?>
					</select>
					<span>
						<button class="submit-button button ph-admin-calendar-submit-btn" type="submit" value="Filter"><?php echo __('Filter', 'bookings-and-appointments-for-woocommerce')?></button>
					</span>
				</div>
           </div>

			<script type="text/javascript">
				jQuery(function() {
					jQuery(".tablenav input[name=calendar_day]").change(function() {
						jQuery("#mainform").submit();
					});
					jQuery( '.calendar_day' ).datepicker({
						dateFormat: 'yy-mm-dd',
						firstDay: <?php echo get_option( 'start_of_week' ); ?>,
						numberOfMonths: 1,
					});
                    jQuery(document).ready(function() 
                    {
                        jQuery('#ph-calendar-bookings-filter').select2();
                        jQuery('#ph-calendar-bookings-filter-status').select2();
                    });
				});
			</script>
		</div>
        
        <div class="calendar_days_all_day">
            <ul>
                <li><?php _e( 'All Day', 'bookings-and-appointments-for-woocommerce' ); ?></li>
            </ul>
            <ul class="bookings">
                <?php 
                    $this->list_bookings_for_day('day'); 
                ?>
            </ul>
        </div>
		<div class="calendar_days">
            
			<ul class="hours">
				<?php for ( $i = 0; $i < 24; $i ++ ) : ?>
					<li><label>
					<?php
					if ( 0 != $i && 24 != $i ) {
						echo date_i18n( wc_time_format(), strtotime( "midnight +{$i} hour" ) );
					}
					?>
					</label></li>
				<?php endfor; ?>
			</ul>
			<ul class="bookings">
				<?php 
                    $this->list_bookings_for_day('time'); 
                ?>
			</ul>
		</div>
	</form>
</div>
