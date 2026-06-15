<?php
if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
}	



if( !class_exists('ph_bookings_calendar_weekdays_order') ) {
    class ph_bookings_calendar_weekdays_order {

		/**
		 * @var $start_of_week
		 */
		public $start_of_week;

        public function __construct(){

			$display_settings=get_option('ph_bookings_display_settigns');
			$this->start_of_week=isset($display_settings['start_of_week'])?$display_settings['start_of_week']:1;
            add_filter( 'ph_booking_calendar_weekdays_order', array($this, 'ph_booking_calendar_weekdays_order'), 10, 1);
            add_filter( 'ph_bookings_calendar_days_order', array($this, 'ph_bookings_calendar_days_order'), 10, 2 );
        }

        public function ph_booking_calendar_weekdays_order($weekdays ){
        	switch($this->start_of_week){
        		case 0:
			            $week_days= "<li>".__("Su", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Mo", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Tu", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("We", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Th", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Fr", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Sa", "bookings-and-appointments-for-woocommerce")."</li>";
        			break;
        		
        		case 1:
			            $week_days= "<li>".__("Mo", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Tu", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("We", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Th", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Fr", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Sa", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Su", "bookings-and-appointments-for-woocommerce")."</li>";
        			break;
        		case 2:
			            $week_days= "<li>".__("Tu", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("We", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Th", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Fr", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Sa", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Su", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Mo", "bookings-and-appointments-for-woocommerce")."</li>";
        			break;
        		case 3:
			            $week_days= "<li>".__("We", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Th", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Fr", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Sa", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Su", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Mo", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Tu", "bookings-and-appointments-for-woocommerce")."</li>";
        			break;
        		case 4:
			            $week_days= "<li>".__("Th", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Fr", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Sa", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Su", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Mo", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Tu", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("We", "bookings-and-appointments-for-woocommerce")."</li>";
        			break;
        		case 5:
			            $week_days= "<li>".__("Fr", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Sa", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Su", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Mo", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Tu", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("We", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Th", "bookings-and-appointments-for-woocommerce")."</li>";
        			break;
        		case 6:
			            $week_days= "<li>".__("Sa", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Su", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Mo", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Tu", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("We", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Th", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Fr", "bookings-and-appointments-for-woocommerce")."</li>";
        			break;
        		default:
			            $week_days= "<li>".__("Mo", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Tu", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("We", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Th", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Fr", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Sa", "bookings-and-appointments-for-woocommerce")."</li>";
			            $week_days.= "<li>".__("Su", "bookings-and-appointments-for-woocommerce")."</li>";
        			break;
        	}
            return $week_days;
        }
        function ph_bookings_calendar_days_order(){
            $day_order = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
            switch($this->start_of_week){
        		case 0:
        				$day_order = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
        			break;
        		
        		case 1:
        				$day_order = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
        			break;
        		case 2:
        				$day_order = array('tuesday','wednesday','thursday','friday','saturday','sunday','monday');
        			break;
        		case 3:
        				$day_order = array('wednesday','thursday','friday','saturday','sunday','monday','tuesday');
        			break;
        		case 4:
        				$day_order = array('thursday','friday','saturday','sunday','monday','tuesday','wednesday');
        			break;
        		case 5:
        				$day_order = array('friday','saturday','sunday','monday','tuesday','wednesday','thursday');
        			break;
        		case 6:
        				$day_order = array('saturday','sunday','monday','tuesday','wednesday','thursday','friday');
        			break;
        		default:
        				$day_order = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
        			break;
        	}
            return $day_order;
        }
    }
new ph_bookings_calendar_weekdays_order();
    
}