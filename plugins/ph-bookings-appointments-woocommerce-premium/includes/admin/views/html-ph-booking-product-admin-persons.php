<div id='booking_persons' class='panel woocommerce_options_panel'>
	<?php
	$person_enable 				= get_post_meta( $post->ID, '_phive_booking_person_enable', 1 );
	$take_persons_as_booking 	= get_post_meta( $post->ID, '_phive_booking_persons_as_booking', 1 );
	$multiply_all_cost		 	= get_post_meta( $post->ID, '_phive_booking_persons_multuply_all_cost', 1 );
	$enable_rules			 	= get_post_meta( $post->ID, '_phive_booking_persons_enable_rules', 1 );
	$rules 						= get_post_meta( $post->ID, '_phive_booking_persons_pricing_rules', 1 );
	$participant_pricing_rules 	= get_post_meta( $post->ID, '_phive_booking_participant_pricing_rules', 1 );
	$allowd_per_slot 			= get_post_meta( $post->ID, '_phive_book_allowed_per_slot', 1);
	$allowd_per_slot 			= !empty($allowd_per_slot)?$allowd_per_slot:'1';
	$maximum_number_of_allowed_participant			= get_post_meta( $post->ID, '_phive_booking_maximum_number_of_allowed_participant', 1);

	$minimum_number_of_required_participant	= get_post_meta( $post->ID, '_phive_booking_minimum_number_of_required_participant', 1);

	$minimum_number_of_required_participant = !empty($minimum_number_of_required_participant) ? $minimum_number_of_required_participant : 0;

	woocommerce_wp_checkbox( 
		array( 
			'id'            => '_phive_booking_person_enable', 
			'label'         => __('Enable Participants', 'bookings-and-appointments-for-woocommerce' ), 
			'value'			=> ($person_enable=='yes') ? 'yes' : 'no',
			'desc_tip'		=> true,
			'description'   => __( 'Enable this option if you want to allow the user to book this product with number of people or materials. For Eg : Adults/Children for hotel room, Chairs for party equipment etc.', 'bookings-and-appointments-for-woocommerce' ),
		)
	);
	?>
	<div class="persons-wraper"><?php
		
		woocommerce_wp_checkbox( 
			array( 
				'id'            => '_phive_booking_persons_multuply_all_cost', 
				'label'         => __('Multiply all costs by number of participants', 'bookings-and-appointments-for-woocommerce' ), 
				'value'		=> ($multiply_all_cost=='yes') ? 'yes' : 'no',
				'desc_tip'		=> true,
				'description'   => __( 'All the costs will be multiplied by the number of participants', 'bookings-and-appointments-for-woocommerce' ),
			)
		);

		woocommerce_wp_checkbox( 
			array( 
				'id'            => '_phive_booking_persons_as_booking', 
				'label'         => __('Consider each participant as separate booking', 'bookings-and-appointments-for-woocommerce' ), 
				'value'		=> ($take_persons_as_booking=='yes') ? 'yes' : 'no',
				'desc_tip'		=> true,
				'description'   => __( 'Enabling this option will create a separate booking order for each participant', 'bookings-and-appointments-for-woocommerce' ),
			)
		);
		woocommerce_wp_text_input( array(
			'id'			=> '_phive_booking_minimum_number_of_required_participant',
	        'data_type'=> 'decimal',
			'label'			=> __( 'Minimum number of participants required in a booking', 'bookings-and-appointments-for-woocommerce' ),
			'desc_tip'		=> 'true',
			'description'	=> __( 'Total number of Participants can not be lesser than this value.', 'bookings-and-appointments-for-woocommerce' ),
			'type' 			=> 'number',
			'value'			=> $minimum_number_of_required_participant,
			'placeholder'	=> 'Number',
			'custom_attributes'	=>	array(
				'min'	=>	0,
			),
		) );

		woocommerce_wp_text_input( array(
			'id'			=> '_phive_booking_maximum_number_of_allowed_participant',
	        'data_type'=> 'decimal',
			'label'			=> __( 'Maximum number of participants allowed in a booking', 'bookings-and-appointments-for-woocommerce' ),
			'desc_tip'		=> 'true',
			'description'	=> __( 'Total number of Participants will not exceed this value.', 'bookings-and-appointments-for-woocommerce' ),
			'type' 			=> 'number',
			'value'			=> $maximum_number_of_allowed_participant,
			'placeholder'	=> 'Number',
			'custom_attributes'	=>	array(
				'min'	=>	0,
			),
		) );

		?>
		<input type="hidden" name="allowd_per_slot" class="allowd_per_slot"  value="<?php echo $allowd_per_slot ?>">
		<div class="" id="persons_rule_wraper">
			<?php
			//Default values
			if( empty($rules) ){
				$rules = array( 0=>array('ph_booking_persons_rule_type'=>'Participant(s)','ph_booking_persons_rule_min'=>'','ph_booking_persons_rule_max'=>'','ph_booking_persons_rule_base_cost'=>'','ph_booking_persons_rule_cost_per_unit'=>'','ph_booking_persons_per_slot' =>'') );
			}?>
			<table class="ph_availability_table wc_input_table sortable" cellspacing="0" name="availability_rules_table">
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						<th><?php _e('Participant Label/Type','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Min','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Max','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Base cost','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Per Participant Cost','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Charge Per Block','bookings-and-appointments-for-woocommerce') ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody class="rules ui-sortable">
					<?php
					foreach ($rules as $key => $rule) {
						include("html-ph-booking-product-admin-persons-matrix-row.php");
					}
					$key = 1; //First row shouldnot have close button, I case of only one row key will be 0 alwas for JS
					?>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="10"><a href="#" class="add_rule button">+ <?php _e('Add','bookings-and-appointments-for-woocommerce') ?></a></th>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="" id="participant_pricing_rule_wrapper">
			<?php
			//Default values
			if( empty($participant_pricing_rules) ){
				$participant_pricing_rules = array( 0=>array('ph_booking_participant_rule_type'=>'','pricing_from_participant'=>'','pricing_to_participant'=>'','participantbasecost_operator'=>'+','ph_booking_participant_rule_base_cost'=>'','perparticipant_operator'=>'+','ph_booking_rule_cost_per_participant'=>'') );
			}?>
			<!-- <h4><?php // _e('Participant Rule','bookings-and-appointments-for-woocommerce') ?></h4> -->

			<h4 style="margin-bottom: -10px;"><b><?php _e('Provide Discounts or Special Prices for participants by creating participant rules.','bookings-and-appointments-for-woocommerce') ?></b></h4>
			<p>
				<i>
					<?php _e('Note :  Please save the participants to be able to create the rules for every participant.<br>You can create a common rule for all participants using “Participant Total Count” or you could create a separate rule for every participant.','bookings-and-appointments-for-woocommerce')
      				?>
      			</i>
      		</p>
			<table class="ph_availability_table wc_input_table sortable" cellspacing="0" name="participant_rules_table">
			<thead>
				<tr>
					<th class="sort">&nbsp;</th>
					<th><?php _e('Range Type','bookings-and-appointments-for-woocommerce') ?></th>
					<th><?php _e('From','bookings-and-appointments-for-woocommerce') ?></th>
					<th><?php _e('To','bookings-and-appointments-for-woocommerce') ?></th>
					<th colspan="2"><?php _e('Base Cost','bookings-and-appointments-for-woocommerce') ?></th>
					<th colspan="2"><?php _e('Cost per participant','bookings-and-appointments-for-woocommerce') ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody class="rules ui-sortable">
				<?php
				foreach ($participant_pricing_rules as $key => $rule) {
					if( !empty($rule) ){
						include("html-ph-booking-product-admin-partcipant-pricing-rule-matrix-row.php");
					}
				}?>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="10"><a href="#" class="add_rule button">+ <?php _e('Add','bookings-and-appointments-for-woocommerce') ?></a></th>
				</tr>
			</tfoot>
		</table>
		</div>
	</div>
</div>
<script type="text/javascript">
    jQuery(function() {
        jQuery( ".datepicker_form" ).datepicker({
            dateFormat : "yy-mm-dd"
        });
    });
jQuery(function($) {

	display_persons_options();
	
	jQuery(document).on('change', '#_phive_booking_person_enable', function(){
		display_persons_options();
	})

	function display_persons_options(){
		if( $("#_phive_booking_person_enable").is(":checked") ){
			$(".persons-wraper").show();
		}else{
			$(".persons-wraper").hide();
		}

	}
	$(document).on( 'change', '.ph_booking_participant_price_rule_type', function(){
		if($(this).val()=='participant_based_custom_date')
		{
			$(this).closest('.participant_rules').find('.datepicker_form').show();
			$(this).closest('.participant_rules').find('.datepicker_to').show();
		}
		else
		{
			$(this).closest('.participant_rules').find('.datepicker_form').hide();
			$(this).closest('.participant_rules').find('.datepicker_to').hide();	
		}
		
	});
	$(document).on( 'change', '.ph_booking_participant_price_rule_type', function(){
		if($(this).val()=='participant_based_week_day')
		{
			$(this).closest('.participant_rules').find('.pricing_from_participant_day_from').show();
			$(this).closest('.participant_rules').find('.pricing_from_participant_day_to').show();
		}
		else
		{
			$(this).closest('.participant_rules').find('.pricing_from_participant_day_to').hide();	
			$(this).closest('.participant_rules').find('.pricing_from_participant_day_from').hide();
		}
		
	});
	$(document).on( 'change', '.ph_booking_participant_price_rule_type', function(){
		if($(this).val()=='participant_based_block_count' || $(this).val().includes("block_count_with_"))
		{
			$(this).closest('.participant_rules').find('.pricing_from_participant_block_count').show();
			$(this).closest('.participant_rules').find('.pricing_to_participant_block_count').show();
		}
		else
		{
			$(this).closest('.participant_rules').find('.pricing_to_participant_block_count').hide();	
			$(this).closest('.participant_rules').find('.pricing_from_participant_block_count').hide();
		}
		
	});

	$('#persons_rule_wraper').on( 'click', '.remove', function(){
		$(this).closest('tr').remove();
	})

	$('#persons_rule_wraper').on( 'click', '.add_rule', function(){
		jQuery(`<?php $rule=""; include("html-ph-booking-product-admin-persons-matrix-row.php")?>`).appendTo('#persons_rule_wraper table tbody');
		return false;
	});
	$('#participant_pricing_rule_wrapper').on( 'click', '.remove', function(){
		$(this).closest('tr').remove();
	})

	$('#participant_pricing_rule_wrapper').on( 'click', '.add_rule', function(){
		jQuery(`<?php $rule=""; include("html-ph-booking-product-admin-partcipant-pricing-rule-matrix-row.php")?>`).appendTo('#participant_pricing_rule_wrapper table tbody');
		
        jQuery( ".datepicker_form" ).datepicker({
            dateFormat : "yy-mm-dd"
        });
		return false;
	});
});
</script>