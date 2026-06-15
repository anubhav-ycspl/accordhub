jQuery(document).ready(function($){

    $(document).on('change', '.brand-status-toggle', function(){

        let checkbox = $(this);
        let term_id  = checkbox.data('term');
        let status   = checkbox.is(':checked');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'webapp_update_brand_status',
                term_id: term_id,
                status: status
            }
        });

    });

});