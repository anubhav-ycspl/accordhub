jQuery(function ($) {
	
	jQuery('#ph-bookings-ics-download-btn, .ph-bookings-ics-download-btn').on('click', function (e) {
		e.preventDefault();

		const orderId = jQuery(this).data('order-id');
		const itemId = jQuery(this).data('item-id');
		const invoker = jQuery(this).data('invoker');

		const data = {
			action: 'ph_bookings_download_ics_file',
			orderId,
			itemId,
			invoker
		};

		jQuery.post(phive_booking_common_ajax.ajaxurl, data, function (response) {

			response = JSON.parse(response);

			if (response?.content) {

				const { content, filename } = response;

				const blob = new Blob([content], { type: 'text/calendar;charset=utf-8;' });
				const link = document.createElement('a');
				link.href = URL.createObjectURL(blob);
				link.download = filename;
				link.click();
			}
		});
	})
});