jQuery(function ($) {
    $('#scannedData').DataTable({
        pageLength: 50
    });

    $(document).on('click','.root-dir-scan-action-btn',function(e){
        e.preventDefault(); 
        var $default_text = $(this).text();
        var $scan_btn_nonce = $(this).attr("data-scan-btn-nonce");
        $(this).text('Scaning...');
        $('.loaderWrap').removeClass('wprds-hide');
        $.ajax({
            method: "POST",
            dataType: "json",
            url: wprds_ajax_object.ajax_url,
            data: { action: "root_dir_scan", scanner_btn_nonce: $scan_btn_nonce },
            success: function(data) {
                if( data.success == true ){
                }
            }
        })
        .done(function( data ) {
            if( data.success == true ){
            }
            //$('.loaderWrap').addClass('wprds-hide');
            $('.root-dir-scan-action-btn').text($default_text);
            window.location.reload();
        });
    });
});