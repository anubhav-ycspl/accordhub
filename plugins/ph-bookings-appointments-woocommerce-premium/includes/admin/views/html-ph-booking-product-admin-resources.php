<div id='booking_resorces' class='panel woocommerce_options_panel'>
	<?php
	$resources_enable			= get_post_meta( $post->ID, '_phive_booking_resources_enable', 1 );
	$rules 						= get_post_meta( $post->ID, '_phive_booking_resources_pricing_rules', 1 );
	$resources_label 			= get_post_meta( $post->ID, '_phive_booking_resources_label', 1 );
	$resources_type 			= get_post_meta( $post->ID, '_phive_booking_resources_type', 1 );
	$resources_mandatory_enable			= get_post_meta( $post->ID, '_phive_booking_single_resources_mandatory_enable', 1 );
	
	woocommerce_wp_checkbox( 
		array( 
			'id'            => '_phive_booking_resources_enable', 
			'label'         => __('Enable Resources', 'bookings-and-appointments-for-woocommerce' ), 
			'value'			=> ($resources_enable=='yes') ? 'yes' : 'no',
			'desc_tip'		=> 'true',
			'description'	=> __( 'Enable this option if you wish to provide additional resources for the booking. For Eg: Breakfast in the case of hotels.', 'bookings-and-appointments-for-woocommerce' ),
		)
	);
	?>
	<div class="resources-wraper">
		<p class=" form-field _phive_booking_resources_type_field" >
			<label class="label" ><?php _e('Resource Type','bookings-and-appointments-for-woocommerce')?> </label>
			<select id="_phive_booking_resources_type" name="_phive_booking_resources_type" class="select" style="" >
				<option value="single" <?php if($resources_type=='single')echo'selected="selected"'; ?> ><?php _e('Single Choice (Dropdown)','bookings-and-appointments-for-woocommerce') ?></option>
				<option value="multiple" <?php if($resources_type=='multiple' || empty($resources_type))echo'selected="selected"'; ?>><?php _e('Multiple Choice (Check box)','bookings-and-appointments-for-woocommerce') ?></option>
			</select>
			
			<?php
			echo wc_help_tip( __("<h4><b>Single Resource</b> - Customers can choose One Resource from a Drop Down containing all the resources. </h4><h4><b>Multiple Resources</b> - Customers can select Multiple Resource while booking.</h4>", 'bookings-and-appointments-for-woocommerce') );?>
		</p>
		<?php
		woocommerce_wp_text_input( array(
			'id'			=> '_phive_booking_resources_label',
			'label'			=> __( 'Resource Label', 'bookings-and-appointments-for-woocommerce' ),
			'desc_tip'		=> 'true',
			'description'	=> __( 'This description will be displayed with the Resource selection.', 'bookings-and-appointments-for-woocommerce' ),
			'type' 			=> 'text',
			'value'			=> $resources_label,
			'placeholder'	=> 'Enter text here',
		) );

			woocommerce_wp_checkbox(
				array( 
					'id'            => '_phive_booking_single_resources_mandatory_enable', 
					'label'         => __('Resource Selection is Mandatory', 'bookings-and-appointments-for-woocommerce' ), 
					'value'			=> ($resources_mandatory_enable=='yes') ? 'yes' : 'no',
					'desc_tip'		=> 'true',
					'description'	=> __( 'Enable this option if you wish to make resource as a mandatory field for bookings.', 'bookings-and-appointments-for-woocommerce' ),
				)
			);
		?>

		<div class="" id="resources_rule_wraper">
			<?php
			if( empty($rules) ){
				$rules = array( 0=>array('ph_booking_resources_name'=>'','ph_booking_resources_cost'=>'','ph_booking_resources_auto_assign'=>'','ph_booking_resources_per_person'=>'', 'ph_booking_resources_per_slot'=>'') );
			}?>
			<table class="ph_availability_table wc_input_table sortable" cellspacing="0" name="availability_rules_table">
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						<th><?php _e('Resource Name/Label','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Resource Cost (Optional)','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Assign','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Charge Per Participant','bookings-and-appointments-for-woocommerce') ?></th>
						<th><?php _e('Charge Per Block','bookings-and-appointments-for-woocommerce') ?></th>
						<?php do_action('phive_add_extra_column_heading_for_resource_cost_settings', $post->ID); ?>
						<th></th>
					</tr>
				</thead>
				<tbody class="rules ui-sortable">
					<?php
					foreach ($rules as $key => $rule) {
						include("html-ph-booking-product-admin-resources-matrix-row.php");
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
jQuery(function($) {

	//107352 - function to change dropdown item based on resource type
	function change_dropdown_item(){

		if( $("#_phive_booking_resources_type").val()=='multiple'){
			$(".ph_assign_option").text('<?php _e('Automatically assigned', 'bookings-and-appointments-for-woocommerce');?>');
		}else{
			$(".ph_assign_option").text('<?php _e('Selected by default', 'bookings-and-appointments-for-woocommerce'); ?>');
		}
	}

	display_resources_options();
	display_single_resources_options();
	
	jQuery(document).on('change', '#_phive_booking_resources_enable', function(){
		display_resources_options();
	})

	function display_resources_options(){
		if( $("#_phive_booking_resources_enable").is(":checked") ){
			$(".resources-wraper").show();
		}else{
			$(".resources-wraper").hide();
		}

	}
	function display_single_resources_options(){
		//107352 - changing dropdown item based on resource type
		change_dropdown_item();
		if( $("#_phive_booking_resources_type").val()=='single' ){
			$("._phive_booking_single_resources_mandatory_enable_field").show();
		}else{
			$("._phive_booking_single_resources_mandatory_enable_field").hide();
		}

	}

	$('#_phive_booking_resources_type').on( 'change', function(){
		display_single_resources_options();
	});

	
	$('#resources_rule_wraper').on( 'click', '.remove', function(){
		$(this).closest('tr').remove();
	})

	$('#resources_rule_wraper').on( 'click', '.add_rule', function(){
		jQuery(`<?php $rule=""; include("html-ph-booking-product-admin-resources-matrix-row.php")?>`).appendTo('#resources_rule_wraper table tbody');
		//107352 - changing dropdown item based on resource type
		change_dropdown_item();
		return false;
	});
});
</script>