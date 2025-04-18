jQuery(document).ready(function($) {
    $('#rts-sync-webinar').on('click', function() {
        var button = $(this);
        var meetingId = button.data('meeting-id');
        var resultDiv = $('#rts-sync-result');
        
        button.prop('disabled', true).text('Processing...');
        resultDiv.html('');
        
        $.ajax({
            url: rtsWebinarAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rts_sync_meeting_to_webinar',
                meeting_id: meetingId,
                nonce: rtsWebinarAdmin.nonce
            },
            success: function(response) {
                button.prop('disabled', false).text('Create Webinar Post');
                
                if (response.success) {
                    resultDiv.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                    
                    // Reload the page after short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    resultDiv.html('<div class="notice notice-error inline"><p>Error: ' + response.data + '</p></div>');
                }
            },
            error: function() {
                button.prop('disabled', false).text('Create Webinar Post');
                resultDiv.html('<div class="notice notice-error inline"><p>Connection error. Please try again.</p></div>');
            }
        });
    });
});
