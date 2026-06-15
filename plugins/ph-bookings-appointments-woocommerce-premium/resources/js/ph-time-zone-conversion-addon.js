jQuery(document).ready(function($){

	is_ph_timezone_conversion_required = true;	// used for recurring booking timezone conversion
	
	jQuery('.time-picker-wraper').on('change','#ph-calendar-time',function(){
		jQuery( ".time-picker ul li" ).each(function() {
		var d = jQuery(this).find('.callender-full-date').val();
		 if(typeof d !== typeof undefined){

			[date,time] = d.split(' ');
			d 		= date+'T'+time+timezoneOffset(d);
			 var date = new Date(d); // Or the date you'd like converted.
			time_format = ph_datetime_convert_to_client_timezone(date,'time','from');

			// 118207 - Time in End Time Popup not changing
			var interval_period 	= jQuery(".book_interval_period").val();	
			var book_interval 		= jQuery(".book_interval").val();	// hour, day etc
			if( interval_period == "hour" ) {
				book_interval = parseInt(book_interval) * 60;
				date.setMinutes( date.getMinutes() + book_interval );
			}
			else if( interval_period=='minute' ) {
				book_interval = parseInt(book_interval) ;
				date.setMinutes( date.getMinutes() + book_interval );
			}
			end_time_format = ph_datetime_convert_to_client_timezone(date,'time','from');

			// no need to display slot end time in calendar design three
			calendar_design = jQuery('#calendar_design').val();

			if((jQuery('.end_time_display').val()=='yes') && calendar_design != 3)
			{
				jQuery(this).find('.ph_calendar_time').text(time_format+" - "+end_time_format);
			}
			else
			{
				jQuery(this).find('.ph_calendar_time').text(time_format);	
			}
			// 118207 - Time in End Time Popup not changing
			jQuery(this).find('.ph_calendar_time_end').text(end_time_format);
	   }
	})
	});

	function convert_to_24_hours(date){
		if(!date)
			return date;
		let am_pm = date.slice(-2);
		let first_part = date.slice(0, -8);
		let hour = date.slice(-8, -6);
		let last_part = date.slice(-6);
		hour = am_pm === 'PM'  && parseInt(hour) < 12 ? parseInt(hour) +  12 : hour;
		date = (first_part + hour + last_part).slice(0, -3);
		return date;
	}

	function get_current_time(date){
		if(date){
			if( typeof(date) === 'string' && (date.length === 16 || date.length === 19)){
				if (date.length === 19) {
					date = convert_to_24_hours(date)
				}
				let str = date.replace(/\s/, 'T');
				return new Date(str);
			}
			return new Date(date);
		}
		return new Date();
	}

	function timezoneOffset(inputDate){
		// let date = inputDate ? new Date(inputDate.replace(/\s/, 'T')) : new Date();
		let date = get_current_time(inputDate);
				let timezoneOffsetValue = date.getTimezoneOffset(),
				hours = ('00' + Math.floor(Math.abs(timezoneOffsetValue/60))).slice(-2),
				minutes = ('00' + Math.abs(timezoneOffsetValue%60)).slice(-2),
				string = (timezoneOffsetValue >= 0 ? '-' : '+') + hours + ':' + minutes;
		return string;
}
	jQuery(document).on('change','.to_text',function(){
		var check_from 			= jQuery( ".ph-date-from" ).val();
		var from 				= jQuery( ".ph-date-from" ).val();
		var to 					= jQuery( ".ph-date-to" ).val();
		var interval_type 		= jQuery("#book_interval_type").val();
		var wp_date_format		= jQuery("#ph_booking_wp_date_format").val();
		var wp_time_format		= jQuery("#ph_booking_wp_time_format").val();
		var from_text 			= jQuery('.from_text').val();
		var to_text 			= jQuery('.to_text').val();
		
		if(from.length == '10'){
			datetime_for = 'date';

			// Passing default time to prevent timezone conversion for day calendar
			from 	= from+' 00:00';
			to 		= to+' 00:00';

			display_from_date = ph_convert_date_to_wp_date_format(wp_date_format,from , true);
			display_to_date = ph_convert_date_to_wp_date_format(wp_date_format,to , true);

			if(from != to && interval_type == 'customer_choosen' && ph_addon_script.display_end_time){

				date_html = "<b>"+from_text+":</b>&nbsp;"+display_from_date+"&nbsp;<b>"+to_text+"</b>&nbsp;"+display_to_date;
			}else{
				date_html = "<b>"+from_text+":</b>&nbsp;"+display_from_date;
			}
			if(jQuery('#booking_info_text').find('.not-available-msg').length == 0 ){

				jQuery('#booking_info_text').html(date_html);

			}

			jQuery('.display_time_from').val(display_from_date);
			jQuery('.display_time_to').val(display_to_date);
			return;
		}
		if(from.length == '7'){
			[from_year,from_month] 	= from.split('-');
			[to_year,to_month] 		= to.split('-');
			var months = ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];
			if(from != to && interval_type == 'customer_choosen' && ph_addon_script.display_end_time){
				//103800
				//112195
				display_from_date 	= ph_change_month_to_wp_format(from,wp_date_format,true);
				display_to_date 	= ph_change_month_to_wp_format(to,wp_date_format,true);
				date_html = "<b>"+from_text+":</b>&nbsp;"+display_from_date+"&nbsp;<b>"+to_text+"</b>&nbsp;"+display_to_date;	
			} else {

				display_from_date 	=  ph_change_month_to_wp_format(from,wp_date_format,true);
				display_to_date 	= display_from_date;
				date_html 			= "<b>"+from_text+":</b>&nbsp;"+display_from_date;
			}
			if(jQuery('#booking_info_text').find('.not-available-msg').length == 0 ){
			
				jQuery('#booking_info_text').html(date_html);
			}

			jQuery('.display_time_from').val(display_from_date);
			jQuery('.display_time_to').val(display_to_date);
			return;
		}
		if(from.length == '16'){
			datetime_for = 'display';
			[date,time] = from.split(' ');
			from 		= date+'T'+time+timezoneOffset(from);
			[date,time] = to.split(' ');
			to 		= date+'T'+time+timezoneOffset(to);
		}
		
		var from_date 	= get_current_time(from);
		var to_date 	= get_current_time(to);

		datetime_from 	= ph_datetime_convert_to_client_timezone(from_date,datetime_for,'from');
		datetime_to 	= ph_datetime_convert_to_client_timezone(to_date,datetime_for,'to');
		
		[date_from,time_from,from_am_pm] 	= datetime_from.split(' ');
		[date_to,time_to,to_am_pm] 		= datetime_to.split(' ');

		if( typeof from_am_pm == 'undefined' ) {
            if( hr < 12 )    from_am_pm = 'AM';
            else{
                if(hr != 12 ) hr = hr - 12;
                from_am_pm = 'PM';
            }
		}
		if( typeof to_am_pm == 'undefined' ) {
            if( hr < 12 )    to_am_pm = 'AM';
            else{
                if(hr != 12 ) hr = hr - 12;
                to_am_pm = 'PM';
            }
		}
		from_time 	= ph_convert_time_to_wp_time_format(wp_time_format,time_from,from_am_pm);
		to_time 	= ph_convert_time_to_wp_time_format(wp_time_format,time_to,to_am_pm);
		if(check_from.length == '10'){
			datetime_from 	= date_from;
			datetime_to 	= date_to;
			from_time 		= '';
			to_time 		= '';
			from_am_pm 		= '';
			to_am_pm		= '';
		}
		
		from_date 	= ph_convert_date_to_wp_date_format(wp_date_format,date_from+'T'+time_from+timezoneOffset(datetime_from));
		to_date 	= ph_convert_date_to_wp_date_format(wp_date_format,date_to+'T'+time_to+timezoneOffset(datetime_to));
		// 103800
		from_date 	= get_current_time(from_date);
		to_date 	= get_current_time(to_date);

		//103800 - adding translation to month name
		display_from_date = ph_convert_date_to_wp_date_format(wp_date_format,date_from+'T'+time_from+timezoneOffset(datetime_from), true)+ ' ' + from_time;
		display_to_date = ph_convert_date_to_wp_date_format(wp_date_format,date_to+'T'+time_to+timezoneOffset(datetime_to), true) + ' ' + to_time;

		if( ((from !== to || check_from.length == '16')) && ph_addon_script.display_end_time ){

			if($('#calender_type').val() != 'date' && $('#calender_type').val() != 'month'  && from_date.getMonth()==to_date.getMonth() && from_date.getFullYear()==to_date.getFullYear() && from_date.getDate()==to_date.getDate())
			{
				date_html = "<b>" + from_text + ":</b>&nbsp;" + display_from_date + "&nbsp;<b>" + to_text + "</b>&nbsp;"+to_time ;
			}
			else
			{
				date_html = "<b>"+from_text+":</b>&nbsp;"+display_from_date+"&nbsp;<b>"+to_text+"</b>&nbsp;"+display_to_date;
			}
		}else{
			date_html = "<b>"+from_text+":</b>&nbsp;"+display_from_date;
		}
		if(jQuery('#booking_info_text').find('.not-available-msg').length == 0 ){
			
		jQuery('#booking_info_text').html(date_html);

		}

		
		jQuery('.display_time_from').val(display_from_date);
		jQuery('.display_time_to').val(display_to_date);
		
	});

	// 118207 - Time in the field displaying according to WP Timezone instead of the customer's timezone
	var start_time_to_modify = '';
	var end_time_to_modify = '';
	jQuery(document).on("click", '.time-picker-wraper .ph-ul-time .ph-calendar-date', function ()
	{
		element_from = jQuery(this).find('.ph_calendar_time.ph_calendar_time_start');
		element_to = jQuery(this).find('.ph_calendar_time_end');
		if (jQuery(element_from).css('display') !== 'none') 
		{
			start_time_to_modify = element_from.text();
			jQuery('.element_from_time').val(start_time_to_modify);
		}
		else if(jQuery(element_to).css('display') !== 'none')
		{
			end_time_to_modify = element_to.text();
			jQuery('.element_to_time').val(end_time_to_modify);
		}
	});

	jQuery(document).on('change', '.ph-date-to', function()
	{
		// 125852 - when across day booking is not checked, value of start_time_to_modify is empty.
		if(jQuery('.across_the_day_booking').val() != 'no')
		{
			jQuery('.element_from_time').val(start_time_to_modify);
		}
		jQuery('.element_to_time').val(end_time_to_modify);
	});

});

function ph_change_month_to_wp_format(date, format, for_display = false) {
	
	var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

	if (for_display) {
		months = phive_booking_locale.months;
	}

	new_date = new Date(date);

	switch (format) {
		case 'F j, Y':
			output_date = months[new_date.getMonth()] + ', ' + new_date.getFullYear();
			break;
		case 'Y-m-d':
			output_date = new_date.getFullYear() + '-' + `${(new_date.getMonth() + 1)}`.padStart(2, "0");
			break;
		case 'm/d/Y':
		case 'd/m/Y':
			output_date = `${(new_date.getMonth() + 1)}`.padStart(2, "0") + '/' + new_date.getFullYear();
			break;
		case 'm.d.Y':
		case 'd.m.Y':
			output_date = `${(new_date.getMonth() + 1)}`.padStart(2, "0") + '.' + new_date.getFullYear();
			break;
		case 'j. F Y':
		case 'j F Y':
			output_date = months[new_date.getMonth()] + ' ' + new_date.getFullYear();
			break;
		default:
			output_date = new_date.getFullYear() + '-' + (new_date.getMonth() + 1);
			break;
	}
	return output_date;
}

function ph_change_date_to_client_timezone(){;
	jQuery( ".time-picker-wraper #ph-calendar-days li" ).each(function() {
		 d 		= jQuery(this).find('.callender-full-date').val();

		 if(typeof d !== typeof undefined){
		 	d 				= d+' 00:00';
			var date 		= new Date(d); // Or the date you'd like converted.
			date_format 	= ph_datetime_convert_to_client_timezone(date,'date','from');
			jQuery(this).find('.ph_calendar_day').text(date_format);
	   
		}
	})
}
function ph_datetime_convert_to_client_timezone(datetime,datetime_for,from_or_to){
	var server_offset 	= jQuery('.time_offset').val();
	var interval_period = jQuery(".book_interval_period").val();
	var interval 		= jQuery(".book_interval").val();
	//hour to minute
	interval 			= (interval_period=='hour')?(interval*60):interval;
	var interval_type 	= jQuery("#book_interval_type").val();
	var offset 			= new Date().getTimezoneOffset();
	offset 				= offset == 0 ? 0 : -offset;
	
	var utc_date 		= new Date(datetime.getTime() - ((server_offset*60) * 60000) );
	var new_date 		= new Date(utc_date.getTime() + ((offset) * 60000) );

	if(datetime_for == 'date'){
		return new_date.getDate();
	}
	if(datetime_for == 'display'){
		if(from_or_to == 'to' && (interval_period == 'hour' || interval_period =='minute')){
		 new_date = new Date(new_date.getTime() + ((interval) * 60000));
		}
		date_copy = new_date;
		
		date = ( "0" + date_copy.getDate() ).slice(-2);
		month = date_copy.getMonth();
		month = ( "0" + ( month+ 1 ) ).slice(-2);

		year = date_copy.getFullYear();
		hr = date_copy.getHours();
		min = date_copy.getMinutes();
		sec = date_copy.getSeconds();
		
		// if( typeof am_pm == 'undefined' ) {
            if( hr < 12 )    am_pm = 'AM';
            else{
                if(hr != 12 ) hr = hr - 12;
                am_pm = 'PM';
            }
		// }
		if (hr > 12 && hr != 12) 
		{
			hr = hr-12;
		}

		if(month.length<2){
			month = "0"+month;
		}
		if (hr < 10) 
		{
			hr = "0"+hr;	
		}
		if (min < 10) 
		{
			min = "0"+min;
		}
		if (sec < 10) 
		{
			sec = "0"+sec;
		}
		return format = year+'-'+month+'-'+date+' '+hr+':'+min+' '+am_pm;
			
	}
	else{
		
		date_copy = new_date;
		
		hr = date_copy.getHours();
		min = date_copy.getMinutes();
		// if( typeof am_pm == 'undefined' ) {
            if( hr < 12 )    am_pm = 'AM';
            else{
                if(hr != 12 ) hr = hr - 12;
                am_pm = 'PM';
            }
		// }

		hr = ( "0" + hr   ).slice(-2);

		// 118858 - Time slots : 9:05, 10:05, 11:05, etc displaying as 9:50, 10:50, 11:50
		// min = ( min + "0" ).slice(0,2);
		min = (min < 10) ? ("0"+min).slice(0,2) : min;
		
		wp_time_format = jQuery("#ph_booking_wp_time_format").val();
		format = hr+':'+min;
		
		time = ph_convert_time_to_wp_time_format(wp_time_format,format,am_pm);
		return time;
	}
	
}
function ph_convert_date_to_wp_date_format(wp_format, date, for_display = false) {

	months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

	if (for_display) {
		months = phive_booking_locale.months;
	}

	new_date = new Date(date);

	switch (wp_format) {
		case 'F j, Y': display_date = months[new_date.getMonth()] + ' ' + new_date.getDate() + ', ' + new_date.getFullYear();
			break;
		case 'Y-m-d': display_date = new_date.getFullYear() + '-' + (new_date.getMonth() + 1) + '-' + new_date.getDate();
			break;
		case 'm/d/Y': display_date = (new_date.getMonth() + 1) + '/' + new_date.getDate() + '/' + new_date.getFullYear();
			break;
		case 'd/m/Y': display_date = new_date.getDate() + '/' + (new_date.getMonth() + 1) + '/' + new_date.getFullYear();
			break;
		case 'd.m.Y': display_date = new_date.getDate() + '.' + (new_date.getMonth() + 1) + '.' + new_date.getFullYear();
			break;
		case 'm.d.Y': display_date = (new_date.getMonth() + 1) + '.' + new_date.getDate() + '.' + new_date.getFullYear();
			break;
		// 103800 - compatibility with j. F Y format
		// 110097 - space missing in between date and month
		case 'j. F Y': display_date = new_date.getDate() + '. ' + months[new_date.getMonth()] + ' ' + new_date.getFullYear();
			break;
		default: display_date = new_date.getFullYear() + '-' + (new_date.getMonth() + 1) + '-' + new_date.getDate();
			break;

	}
	return display_date;
}

function ph_convert_time_to_wp_time_format(wp_format,time,am_pm){
	switch(wp_format)
	{
		case 'g:i a': display_time = time+' '+am_pm.toLowerCase();
					break;
		case 'g:i A': display_time = time+' '+am_pm.toUpperCase();
					break;
		case 'H:i': [hr,min,sec] = time.split(':');
					if((am_pm == 'pm' || am_pm == 'PM') && hr!='12'){
						hr = parseInt(hr)+12;
					}
					display_time = hr+':'+min;
				break;
		case "G \\h i \\m\\i\\n": 
				[hr,min,sec] = time.split(':');
				if((am_pm == 'pm' || am_pm == 'PM') && !(parseInt(hr) >= 12) ){
					hr = parseInt(hr)+12;
				}
				display_time = hr+' h '+min+' min';
			  	break;
		case "G\\hi":     // 151305
				[hr,min,sec] = time.split(':');
				if((am_pm == 'pm' || am_pm == 'PM') && !(parseInt(hr) >= 12) ){
					hr = parseInt(hr)+12;
				}
				display_time = hr+'h'+min;
				break;
		default : display_time = time+' '+am_pm.toLowerCase();
			break;

	}
	return display_time;
}




