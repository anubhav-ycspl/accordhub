<div class="wrap woocommerce">
	<h2><?php _e( 'Calendar', 'bookings-and-appointments-for-woocommerce' ); ?><a href="admin.php?page=add-booking" class="page-title-action"><?php _e( 'Add Booking', 'bookings-and-appointments-for-woocommerce' ); ?></a></h2>

	<form method="get" id="mainform" enctype="multipart/form-data" class="ph_bookings_calendar_form">
		<input type="hidden" name="page" value="ph-booking-calendar" />
		<input type="hidden" name="calendar_month" value="<?php echo absint( $month ); ?>" />
		<input type="hidden" name="view" value="<?php echo esc_attr( $view ); ?>" />
		<input type="hidden" name="tab" value="calendar" />
		<div class="tablenav">
			<div class="text-center admin-calendar-views">
				<div class="admin-calendar-btn">
					<a class="month" href="<?php echo esc_url( add_query_arg( 'view', 'month' ) ); ?>"><?php _e( 'Month', 'bookings-and-appointments-for-woocommerce' ); ?></a>
				</div>
				<div class="admin-calendar-btn" style="background-color:#f1f1f1;">
					<a class="day" style="color: #555;" href="<?php echo esc_url( add_query_arg( 'view', 'day' ) ); ?>"><?php _e( 'Day', 'bookings-and-appointments-for-woocommerce' ); ?></a>
				</div>
			</div>

			<div class="ph-bottom-filters">
				<div class="date_selector">
					<a class="prev dashicons dashicons-arrow-left-alt2" href="
					<?php
						echo esc_url(
							add_query_arg(
								array(
									'calendar_year'  => $year,
									'calendar_month' => $month - 1,
								)
							)
						);
						?>
					"></a>
					<?php if ( 'no' == $ph_swap_month_year ) { ?>
						<div>
							<select name="calendar_month">
								<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
									<option value="<?php echo $i; ?>" <?php selected( $month, $i ); ?>><?php echo ucfirst( date_i18n( 'F', strtotime( '2021-' . $i . '-01' ) ) ); ?></option>
								<?php endfor; ?>
							</select>
						</div>
						<div>
							<select name="calendar_year">
								<?php for ( $i = ( date( 'Y' ) - 11 ); $i <= ( date( 'Y' ) + 9 ); $i++ ) : ?>
									<option value="<?php echo $i; ?>" <?php selected( $year, $i ); ?>><?php echo date_i18n( $year_format, strtotime( "01/01/$i" ) ); ?></option>
									<?php endfor; ?>
								</select>
							</div>
					<?php } else { ?>
							
							<div>
								<select name="calendar_year">
									<?php for ( $i = ( date( 'Y' ) - 11 ); $i <= ( date( 'Y' ) + 9 ); $i++ ) : ?>
										<option value="<?php echo $i; ?>" <?php selected( $year, $i ); ?>><?php echo date_i18n( $year_format, strtotime( "01/01/$i" ) ); ?></option>
									<?php endfor; ?>
								</select>
							</div>
							<div>
								<select name="calendar_month">
									<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
										<option value="<?php echo $i; ?>" <?php selected( $month, $i ); ?>><?php echo ucfirst( date_i18n( 'F', strtotime( '2021-' . $i . '-01' ) ) ); ?></option>
								<?php endfor; ?>
							</select>
						</div>
					<?php } ?>
					<a class="next dashicons dashicons-arrow-right-alt2" href="
					<?php
						echo esc_url(
							add_query_arg(
								array(
									'calendar_year'  => $year,
									'calendar_month' => $month + 1,
								)
							)
						);
						?>
					"></a>
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
						<button class="submit-button button ph-admin-calendar-submit-btn" type="submit" value="Filter"><?php echo __( 'Filter', 'bookings-and-appointments-for-woocommerce' ); ?></button>
					</span>
				</div>
			</div>
		
			<script type="text/javascript">
				jQuery(".tablenav .date_selector select").change(function() {
					jQuery("#mainform").submit();
				});
				jQuery(document).ready(function() 
				{
					jQuery('#ph-calendar-bookings-filter').select2();
					jQuery('#ph-calendar-bookings-filter-status').select2();
				});
			</script>
		</div>

		<table class="ph_bookings_calendar widefat">
			<thead>
				<tr>
					<?php for ( $ii = get_option( 'start_of_week', 1 ); $ii < get_option( 'start_of_week', 1 ) + 7; $ii++ ) : ?>
						<th><?php echo date_i18n( _x( 'l', 'date format', 'bookings-and-appointments-for-woocommerce' ), strtotime( "next sunday +{$ii} day" ) ); ?></th>
					<?php endfor; ?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<?php
					$timestamp        = $start_time;
					$index            = 0;
					$compare_end_time = strtotime( '-1 day', $end_time );
					while ( $timestamp <= $compare_end_time ) :
						?>
							<td width="14.285%" >
								<div class="ph-admin-calendar-date-wrapper 
								<?php
								if ( date( 'n', $timestamp ) != absint( $month ) ) {
									echo 'calendar-diff-month';
								}
								?>
								">
									<a class='ph-admin-calendar-date' href="<?php echo admin_url( 'admin.php?page=ph-booking-calendar&view=day&tab=calendar&calendar_day=' . date( 'Y-m-d', $timestamp ) ); ?>">
										<?php
										if ( absint( $month ) == date( 'm', $timestamp ) ) {
											echo date( 'd', $timestamp );
										}
										?>
									</a>
									<div class="bookings">
										<ul>
											<?php
											if ( absint( $month ) == date( 'm', $timestamp ) ) {
												$this->list_bookings(
													date( 'd', $timestamp ),
													date( 'm', $timestamp ),
													date( 'Y', $timestamp )
												);
											}
											?>
										</ul>
									</div>
								</div>
							</td>
							<?php
							$timestamp = strtotime( '+1 day', $timestamp );
							++$index;

							if ( 0 === $index % 7 ) {
								echo '</tr><tr>';
							}
						endwhile;
					?>
				</tr>
			</tbody>
		</table>
	</form>
</div>
