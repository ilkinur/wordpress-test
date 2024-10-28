jQuery(document).ready(function($) {
    $('.delete-log').on('click', function() {
        var logId = $(this).data('id');
        
        $.post(fs_project_ajax.ajax_url, {
            action: 'fs_project_delete_log',
            log_id: logId
        }, function(response) {
            if(response.success) {
                $('#log-' + logId).remove();
            }
        });
    });
});
