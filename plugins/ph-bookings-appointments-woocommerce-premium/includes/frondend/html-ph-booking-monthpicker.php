<div class="month-picker-wraper">
    <?php
		$product_id = Ph_Bookings_General_Functions_Class::get_default_lang_product_id($default_product->get_id()); //wpml

		$default_product = wc_get_product( $product_id );
        $ph_calendar_color 			= get_option('ph_booking_settings_calendar_color') ;

		$ph_calendar_month_color	= isset($ph_calendar_color['ph_calendar_month_color']) && !empty($ph_calendar_color['ph_calendar_month_color']) ? $ph_calendar_color['ph_calendar_month_color']: '#539bbe';
		$booking_full_color 		= isset($ph_calendar_color['booking_full_color']) && !empty($ph_calendar_color['booking_full_color']) ? $ph_calendar_color['booking_full_color']: '#dadada';
		$selected_date_color 		= isset($ph_calendar_color['selected_date_color']) && !empty($ph_calendar_color['selected_date_color']) ? $ph_calendar_color['selected_date_color'] : '#6aa3f1';
		$booking_info_wraper_color 	= isset($ph_calendar_color['booking_info_wraper_color']) && !empty($ph_calendar_color['booking_info_wraper_color']) ? $ph_calendar_color['booking_info_wraper_color'] : '#539bbe';
		$ph_calendar_weekdays_color = isset($ph_calendar_color['ph_calendar_weekdays_color']) && !empty($ph_calendar_color['ph_calendar_weekdays_color']) ? $ph_calendar_color['ph_calendar_weekdays_color'] : '#ddd';
		$ph_calendar_days_color 	= isset($ph_calendar_color['ph_calendar_days_color']) && !empty($ph_calendar_color['ph_calendar_days_color']) ? $ph_calendar_color['ph_calendar_days_color'] : '#eee';
		
		$book_now_bg_color 	= !empty($ph_calendar_color['book_now_bg_color']) ? $ph_calendar_color['book_now_bg_color'] : '#1a1a1a';
		$book_now_text_color 	= !empty($ph_calendar_color['book_now_text_color']) ? $ph_calendar_color['book_now_text_color']: '#ffffff';
		$booking_info_wraper_text_color 	= !empty($ph_calendar_color['booking_info_wraper_text_color']) ? $ph_calendar_color['booking_info_wraper_text_color'] : '#ffffff';
		$price_box_text_color 		= (isset($ph_calendar_color['price_box_text_color']) && !empty($ph_calendar_color['price_box_text_color']))?$ph_calendar_color['price_box_text_color']:'#000000';
		$ph_calendar_days_text_color 	= !empty($ph_calendar_color['ph_calendar_days_text_color']) ? $ph_calendar_color['ph_calendar_days_text_color']: '#777';
		$ph_calendar_month_text_color 	= !empty($ph_calendar_color['ph_calendar_month_text_color']) ? $ph_calendar_color['ph_calendar_month_text_color']: '#ffffff';
		$ph_calendar_weekdays_text_color 	= !empty($ph_calendar_color['ph_calendar_weekdays_text_color']) ? $ph_calendar_color['ph_calendar_weekdays_text_color']: '#ffffff';

		$ph_calendar_design			= (isset($ph_calendar_color['ph_calendar_design']) && !empty($ph_calendar_color['ph_calendar_design']))?$ph_calendar_color['ph_calendar_design']:1; // default legacy design will display
		// new design colours 
		$primary_bg_color			= (isset($ph_calendar_color['primary_bg_color']) && !empty($ph_calendar_color['primary_bg_color']))?$ph_calendar_color['primary_bg_color']:'1791ce';
		$hover_bg_color			= (isset($ph_calendar_color['hover_bg_color']) && !empty($ph_calendar_color['hover_bg_color']))?$ph_calendar_color['hover_bg_color']:'fff';
		$price_box_bg_color			= (isset($ph_calendar_color['price_box_bg_color']) && !empty($ph_calendar_color['price_box_bg_color']))?$ph_calendar_color['price_box_bg_color']:'ffffff';
		$text_color			= (isset($ph_calendar_color['text_color']) && !empty($ph_calendar_color['text_color']))?$ph_calendar_color['text_color']:'fff';
		$booked_block_color			= (isset($ph_calendar_color['booked_block_color']) && !empty($ph_calendar_color['booked_block_color']))?$ph_calendar_color['booked_block_color']:'dadada';

		$book_now_bg_color_design_2 	= !empty($ph_calendar_color['book_now_bg_color_design_2']) ? $ph_calendar_color['book_now_bg_color_design_2'] : '#1a1a1a';
		$book_now_text_color_design_2 	= !empty($ph_calendar_color['book_now_text_color_design_2']) ? $ph_calendar_color['book_now_text_color_design_2']: '#ffffff';
		
		//design 3
		$font_color_box			= (isset($ph_calendar_color['font_color_box']) && !empty($ph_calendar_color['font_color_box']))?$ph_calendar_color['font_color_box']:'#1d3268';
		$booking_full_color_box			= (isset($ph_calendar_color['booking_full_color_box']) && !empty($ph_calendar_color['booking_full_color_box']))?$ph_calendar_color['booking_full_color_box']:'#dadada';
		$selected_date_color_box			= (isset($ph_calendar_color['selected_date_color_box']) && !empty($ph_calendar_color['selected_date_color_box']))?$ph_calendar_color['selected_date_color_box']:'#1d3268';
		$booking_wrapper_color_box			= (isset($ph_calendar_color['booking_wrapper_color_box']) && !empty($ph_calendar_color['booking_wrapper_color_box']))?$ph_calendar_color['booking_wrapper_color_box']:'#1d3268';
		$booking_wrapper_text_color_box			= (isset($ph_calendar_color['booking_wrapper_text_color_box']) && !empty($ph_calendar_color['booking_wrapper_text_color_box']))?$ph_calendar_color['booking_wrapper_text_color_box']:'#ffffff';
		$book_now_bg_color_box			= (isset($ph_calendar_color['book_now_bg_color_box']) && !empty($ph_calendar_color['book_now_bg_color_box']))?$ph_calendar_color['book_now_bg_color_box']:'#1d3268';
		$book_now_text_color_box			= (isset($ph_calendar_color['book_now_text_color_box']) && !empty($ph_calendar_color['book_now_text_color_box']))?$ph_calendar_color['book_now_text_color_box']:'#ffffff';
		$start_date_box			= (isset($ph_calendar_color['start_date_box']) && !empty($ph_calendar_color['start_date_box']))?$ph_calendar_color['start_date_box']:'#3f9dbc';
		
			// error_log("design".$ph_calendar_design);
			if($ph_calendar_design==2 && !(isset($_GET['page']) && $_GET['page']=='add-booking'))
			{
				?>
			        <style type="text/css">
						/* Compatibility with the Flatsome theme for the Quickview */
						.product-quick-view-container div.product form.cart,
			            .single-product div.product form.cart
						{
							background-color: <?php echo $primary_bg_color;?> !important;
						}
						.booking-info-wraper{
							background: <?php echo $price_box_bg_color;?> !important;  
						}
						.selected-date, .timepicker-selected-date, li.ph-calendar-date.mouse_hover, .time-picker-wraper #ph-calendar-time li.ph-calendar-date , li.ph-calendar-date.today:hover, .ph-calendar-date.today{
						    border: 0px solid transparent;
						}

						.time-picker-wraper #ph-calendar-time li.ph-calendar-date {
						    border: 1px solid #ffffff;
						}
						li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover{
						  background-color: <?php echo $hover_bg_color;?> !important;
						}
						.timepicker-selected-date,.selected-date {
						    background-color: <?php echo $hover_bg_color;?> !important;
						}
						.ph-next:hover, .ph-prev:hover{
						  color: #4d8e7a ;
						}
						li.ph-calendar-date.de-active.booking-full:hover, .ph-calendar-date.booking-full {
						  background-color: <?php echo $booked_block_color;?> !important;;
						  cursor: text;
						}
						.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover{
						  background-color: #1373a3 !important;
						    border: 1px #1373a3 !important;
						}
						.ph_bookings_book_now_button:before {
						  background: #2098D1;
						}
						li.ph-calendar-date.mouse_hover, li.ph-calendar-date.today:hover, li.ph-calendar-date:hover, .timepicker-selected-date, .selected-date, li.ph-calendar-date.today.timepicker-selected-date{
							background: #f4fafd;
				    		color: <?php echo $text_color;?> !important;
						}
						.ph-calendar-days li, .ph-calendar-weekdays li , .ph-calendar-month ul li, .extra-resources,.callender-msg,.ph_bookings_book_now_button,.ph_bookings_book_now_button:hover,.ph-calendar-date.today,label.label-person,.phive_asset_section label, .extra-resources label{
							color: <?php echo $text_color;?> !important;	
						}
						.please_pick_a_date_text
						{
							background: <?php echo $primary_bg_color ?> !important;
							margin-left: 0 !important; 
							padding: 10px 0 !important;
							margin-top: 1em !important;
						}
						.button.alt
						{
							background-color: <?php echo $book_now_bg_color_design_2 ?> !important;
							color: <?php echo $book_now_text_color_design_2 ?> !important;
						}
						.booking-info-wraper, .booking-info-wraper p 
						{
							color: <?php echo $price_box_text_color ?> !important;
						}
			        </style>
		<?php
				}
				else if($ph_calendar_design==3 && !(isset($_GET['page']) && $_GET['page']=='add-booking'))
				{ 
				?>
					<style type="text/css">
						/*new design*/

							.ph-calendar-box-container .inner-element input:focus{
							  background: <?php echo $start_date_box; ?> !important;
							}
							.ph-calendar-weekdays{
								background: white !important;
							}
							.ph-calendar-days{
								background: white !important;
							}
							li.ph-calendar-date.mouse_hover,li.ph-calendar-date:hover {
								background: <?php echo $selected_date_color_box; ?> !important;
							}
							li.ph-calendar-date.de-active:hover{
								background: none !important;
							}
							.button.alt
							{
								background-color: <?php echo $book_now_bg_color_box; ?> !important;
								color: <?php echo $book_now_text_color_box; ?> !important;
							}
							.booking-info-wraper
							{
								color: white !important;
							}
							.ph-calendar-days li,.time-picker-wraper #ph-calendar-time li.ph-calendar-date
							{
								color: <?php echo $font_color_box; ?>;
							}
							.ph-calendar-weekdays li
							{
								color: <?php echo $font_color_box; ?>;
							}
							.span-month, .span-year,.ph-prev,.ph-next
							{
								color: <?php echo $font_color_box; ?> !important;
							}
							.ph-calendar-month ul li{
								color: <?php echo $font_color_box; ?> !important;	
							}
							.time-picker{
								margin-top:1em !important;
							}
							.time-picker-wraper #ph-calendar-time li.ph-calendar-date
							{
								background: white;
							}
							.time-picker-wraper #ph-calendar-time li.ph-calendar-date:hover,.time-picker-wraper #ph-calendar-time li.ph-calendar-date.mouse_hover {
							    background: <?php echo $selected_date_color_box; ?>;;
							    color: white; 
							}

							.ph-calendar-container {
							   border: 1px solid #cccccc;
							   background: white;
							   margin-top: 5px;
							}

							.ph-calendar-month {
							    border-bottom: 1px solid #cccccc;
							}

							.ph-calendar-month {
							     background: #ffffff !important; 
							}

							ul.ph-calendar-weekdays {
							    border-bottom: 1px solid #cccccc;
							    background: #fff !important;
							}

							.ph-calendar-days {
							    background: #fff !important;
							}

							.time-picker {
							     margin-top: 0px !important; 
							    /*border-top: 1px solid #cccccc;*/
							}
							.booking-info-wraper {
								background: <?php echo $booking_wrapper_color_box; ?> !important;
								/*background: #3f9dbc !important;*/
							}
							li.ph-calendar-date.mouse_hover, li.ph-calendar-date:hover {
							    background: <?php echo $selected_date_color_box; ?> !important;
							    color: white;
							    /*background: #3f9dbc !important;*/
							}
							li.ph-calendar-date.booking-full.de-active:hover{
								background: <?php echo 	$booking_full_color_box ?> !important;
							}
							.booking-full{
								background: <?php echo $booking_full_color_box ?> !important;
							}
							.timepicker-selected-date, .selected-date{
								background: <?php echo $selected_date_color_box; ?> !important;
								border:  0px solid <?php echo $selected_date_color_box; ?> !important;
							}
							.booking-info-wraper, .booking-info-wraper p 
							{
								color: <?php echo $booking_wrapper_text_color_box;?> !important;
							}
							
				            .single-product div.product form.cart
							{
								border: 1px solid #cccccc;
								padding: 10px;
								border-radius: 5px;
							}
					</style>
				<?php }
				else
				{
        ?>
					<style type="text/css">
						.ph-calendar-month{
							background: <?php echo $ph_calendar_month_color ?> !important;
						}
						.booking-full{
							background: <?php echo $booking_full_color ?> !important;
						}
						.timepicker-selected-date, .selected-date{
							background: <?php echo $selected_date_color ?> !important;
							border:  0px solid <?php echo $selected_date_color ?> !important;
						}
						.booking-info-wraper{
							background: <?php echo $booking_info_wraper_color ?> !important;
						}
						.ph-calendar-weekdays{
							background: <?php echo $ph_calendar_weekdays_color ?> !important;
						}
						.ph-calendar-days{
							background: <?php echo $ph_calendar_days_color ?> !important;
						}
						li.ph-calendar-date.mouse_hover, li.ph-calendar-date:hover {
							background: <?php echo !empty($selected_date_color)?$selected_date_color:"#6aa3f1"; ?> !important;
						}
						li.ph-calendar-date.de-active:hover{
							background: none !important;
						}
						li.ph-calendar-date.booking-full.de-active:hover{
							background: <?php echo !empty($booking_full_color)?$booking_full_color:"#dadada"; ?> !important;
						}
						.please_pick_a_date_text
						{
							background: <?php echo $ph_calendar_days_color ?> !important;
							margin-left: 0 !important; 
							padding: 10px 0 !important;
							margin-top: 1em !important;
						}
						.button.alt
						{
							background-color: <?php echo $book_now_bg_color ?> !important;
							color: <?php echo $book_now_text_color ?> !important;
						}
						.booking-info-wraper, .booking-info-wraper p 
						{
							color: <?php echo $booking_info_wraper_text_color ?> !important;
						}
						.ph-calendar-days li,.time-picker-wraper #ph-calendar-time li.ph-calendar-date
						{
							color: <?php echo $ph_calendar_days_text_color ?>;
						}
						.ph-calendar-weekdays li
						{
							color: <?php echo $ph_calendar_weekdays_text_color ?>;
						}
						.span-month, .span-year
						{
							color: <?php echo $ph_calendar_month_text_color?>;
						}
		<?php
				}
		?>
					</style>
	<input type="hidden" id="calender_type" value="month">
	<input type="hidden" id="ph_display_booking_capacity" value="<?php echo $default_product->get_meta('_phive_display_bookings_capacity')?>">
	<input type="hidden" id="book_interval_type" value="<?php echo $default_product->get_interval_type()?>">
	<input type="hidden" id="book_interval" value="<?php echo $default_product->get_interval()?>">
	<input type="hidden" id="book_min_allowed_slot" value="<?php echo $default_product->get_min_allowed_booking() ?>">
	<input type="hidden" id="book_max_allowed_slot" value="<?php echo $default_product->get_max_allowed_booking() ?>">
	<input type="hidden" name="persons_as_booking" id="persons_as_booking" value="<?php echo $default_product->get_persons_as_booking() ?>">
	
	<?php
	
	if( $this->assets_enabled == 'yes' ){
		$this->phive_generate_assets_input_fields();
	}
		// $booking_text=apply_filters('ph_booking_pick_booking_period_text','Please pick a booking period',$default_product->get_id());
		$pick_a_month= __('Please Pick a Month', 'bookings-and-appointments-for-woocommerce');
		
		$booking_date_text=apply_filters('ph_booking_pick_booking_date_text',$pick_a_month,$default_product->get_id(),'month-picker');
	?>
	<?php 

	if($ph_calendar_design==3 && !(isset($_GET['page']) && $_GET['page']=='add-booking'))
	{
		if ($default_product->get_interval_type()=='fixed') {
			?>
			<div  class="ph-calendar-box-container month-calendar fixed-block" >
				<div class="left-element inner-element">
					<div class="element-container">
						<input class="element_from" type="text" name="" placeholder="<?php _e( 'Start Month', 'bookings-and-appointments-for-woocommerce' )?>" value=""  readonly="readonly"/>
					</div>
				</div>
			</div>
			
		<?php
		}
		elseif($default_product->get_interval_type()=='customer_choosen'){
			?>

			<div  class="ph-calendar-box-container month-calendar enable-range" >
				<div class="left-element inner-element">
					<div class="element-container">
						<input class="element_from" type="text" name="" placeholder="<?php _e( 'Start Month', 'bookings-and-appointments-for-woocommerce' )?>" value=""  readonly="readonly"/>
					</div>
				</div>
				<div class="center-element">
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</div>
				<div class="right-element inner-element">
					<div class="element-container">
						<input class="element_to" type="text" name="" placeholder="<?php _e( 'End Month', 'bookings-and-appointments-for-woocommerce' )?>"  readonly="readonly"/>
					</div>
				</div>
			</div>
		
		<?php
		}
	}?>

	<div class="ph-calendar-container">
	<!-- <div class="callender-msg"><php _e( $booking_text, 'bookings-and-appointments-for-woocommerce' )?></div> -->
		<div class="ph-calendar-month">	
			<ul>
				<li><?php _e('Pick Month(s)','bookings-and-appointments-for-woocommerce')?></li>
			</ul>
		</div>
		<ul class="ph-calendar-days ph-ul-date" id="ph-calendar-days" style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?> !important;">	<?php
			$start_date = date('Y-m');
			
			$asset_id = false;
			if( $this->assets_enabled =='yes'){
				if( $this->assets_auto_assign != 'yes' && !empty($this->assets_pricing_rules[0]) ){
					$asset_id 	= $this->assets_pricing_rules[0]['ph_booking_asset_id'];
				}else{
					$asset_id ='';
				}
			}
			echo $this->phive_generate_month_for_period($start_date, '', '', $asset_id);
			?>
		</ul>
	</div>
	
	<!-- <br> -->
	<!-- <ul class="please_pick_a_date_text" id="please_pick_a_date_text" style="position: relative;">
		
	</ul> -->

	<?php do_action('ph_bookings_additional_form_fields',$default_product->get_id());?>
	<?php

	if($this->person_enabled == 'yes' ){

		$this->phive_generate_persons_input_fields();

	}

	if($this->reources_enabled == 'yes' ){

		$this->phive_generate_resources_input_fields();
	}
	
	?>
</div>


<div class="booking-info-wraper">
	<p id="booking_info_text" style="text-align:center;"> 
				<?php _e($booking_date_text, "bookings-and-appointments-for-woocommerce")?>
	</p>
	<p id="booking_price_text"> </p>
</div>
<?php 
	if($this->additional_notes_enabled == 'yes'){
		$this->phive_generate_additional_notes_field();
	}
?>

