jQuery(document).ready(function($) {
    $('.export-excel').on('click', function(e) {
        e.preventDefault();
        
        var type = $(this).data('type');
        var data = {
            action: 'petshop_export_excel',
            report_type: type,
            year: $('#report-year').val(),
            nonce: petshop_admin.nonce
        };
        
        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                window.location.href = response.data.file_url;
            }
        });
    });
});