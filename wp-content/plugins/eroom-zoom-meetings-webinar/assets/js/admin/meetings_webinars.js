(function ($) {
    'use strict';
    $(document).ready(function () {
        var zoom_type           = (typeof typenow != 'undefined' && typenow == 'stm-zoom-webinar') ? 'Webinars' : 'Meetings' ;

        $('.wrap > a').after('<a id="sync_meetings" class="page-title-action">Sync with Zoom ' + zoom_type + '</a>');

        $('#sync_meetings').on('click', function(e) {
            e.preventDefault();

            if ( confirm('Are you sure you want to synchronize Zoom ' + zoom_type + '?') ) {
                $.ajax({
                    url: stm_zoom_ajaxurl,
                    method: 'post',
                    type: 'json',
                    data: {
                        action: 'stm_zoom_sync_meetings_webinars',
                        zoom_type: typenow,
                        nonce: zoom_sync.nonce
                    },
                    beforeSend: function() {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-warning notice-alt updating-message"><p>Zoom ' + zoom_type + ' are synchronizing now...</p></div><hr class="wp-header-end extra">');
                    },
                    success: function(response) {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        if ( response == 'done' ) {
                            $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt updated-message notice-success"><p>Zoom ' + zoom_type + ' are synchronized successfully! Please <a href="javascript:window.location.href=window.location.href">reload</a> this page in order to see changes.</p></div><hr class="wp-header-end extra">');
                        } else {
                            $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt notice-error"><p>Zoom ' + zoom_type + ' Synchronization Failed. Please try once again!</p></div><hr class="wp-header-end extra">');
                        }
                    },
                    error: function (request, status, error) {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt notice-error"><p>Zoom ' + zoom_type + ' Synchronization Failed: ' + request.responseText + '</p></div><hr class="wp-header-end extra">');
                    }
                })
            }

        });
    })
})(jQuery);