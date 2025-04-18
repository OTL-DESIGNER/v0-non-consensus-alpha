<?php
/*
Plugin Name: RTS Auto Content Restriction
Description: Automatically sets content restrictions based on post type
Version: 1.0
Author: Your Name
*/

// Add JavaScript to auto-check the appropriate subscription boxes
function rts_auto_content_restriction_script() {
    // Only run on add/edit post screens
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, array('post', 'post-new'))) {
        return;
    }
    
    // Only for our custom post types
    if (!in_array($screen->post_type, array('newsletter', 'webinar'))) {
        return;
    }
    
    // The exact IDs of your subscription plans
    $newsletter_plan_id = 10;
    $webinar_plan_id = 11;
    $bundle_plan_id = 12;
    
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Function to check the appropriate boxes based on post type
        function setContentRestriction() {
            // Always check the Complete Access Bundle
            $('#pms-content-restrict-subscription-plan-<?php echo $bundle_plan_id; ?>').prop('checked', true);
            
            // For newsletter posts
            if ('<?php echo $screen->post_type; ?>' === 'newsletter') {
                $('#pms-content-restrict-subscription-plan-<?php echo $newsletter_plan_id; ?>').prop('checked', true);
            }
            
            // For webinar posts
            if ('<?php echo $screen->post_type; ?>' === 'webinar') {
                $('#pms-content-restrict-subscription-plan-<?php echo $webinar_plan_id; ?>').prop('checked', true);
            }
        }
        
        // Set initial values when page loads
        setContentRestriction();
        
        // Also set values when the content restriction box is toggled/opened
        $(document).on('click', '#pms_post_content_restriction .handlediv', function() {
            setTimeout(setContentRestriction, 100);
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'rts_auto_content_restriction_script');
