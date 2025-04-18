<div class="sidebar">
    <div class="sidebar-widget">
        <h3>Your Subscription</h3>
        <?php 
        // Get current user
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $is_admin = current_user_can('administrator');
        
        // Check subscription status more reliably
        $has_subscription = false;
        
        // Try using PMS functions directly if available
        if (function_exists('pms_is_member')) {
            $has_subscription = pms_is_member($user_id);
        }
        
        // Alternative check using the database
        if (!$has_subscription) {
            global $wpdb;
            $subscription_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pms_member_subscriptions 
                WHERE user_id = %d AND status = 'active'",
                $user_id
            ));
            $has_subscription = !empty($subscription_count);
        }
        
        if ($has_subscription || $is_admin) : ?>
            <div class="subscription-status active">
                <?php if ($is_admin && !$has_subscription) : ?>
                    <p><strong>Admin Access</strong></p>
                    <p>You have access via administrator privileges.</p>
                <?php else : ?>
                    <p><strong>Active Subscription</strong></p>
                    <p>You have access to premium content.</p>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div class="subscription-status inactive">
                <p>No active subscription</p>
                <a href="<?php echo esc_url(site_url('/register/')); ?>" class="button">Subscribe Now</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="sidebar-widget">
        <h3>Quick Links</h3>
        <ul>
            <li><a href="<?php echo esc_url(get_post_type_archive_link('newsletter')); ?>">Newsletters</a></li>
            <li><a href="<?php echo esc_url(get_post_type_archive_link('webinar')); ?>">Webinars</a></li>
            <li><a href="<?php echo esc_url(site_url('/account/')); ?>">Account Settings</a></li>
        </ul>
    </div>
</div>