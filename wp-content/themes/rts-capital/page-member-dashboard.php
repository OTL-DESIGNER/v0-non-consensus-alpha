<?php
/**
 * Template Name: Member Dashboard
 */
// Redirect non-logged in users to login page
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
} else {
    // User is logged in, continue
    $current_user = wp_get_current_user();
    error_log('User ' . $current_user->user_login . ' accessed dashboard');
}
// Set error handling to catch and report issues
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display PHP errors but log them
ini_set('log_errors', 1); // Ensure errors are logged
// Start output buffering to prevent partial HTML output
ob_start();
try {
    get_header();
    
    // Get current user info
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    
    // Define subscription plan IDs
    $complete_access_id = 12;
    $newsletter_access_id = 10;
    $webinar_access_id = 11;
    
    // Function to check if a user has a specific subscription
    function user_has_subscription($user_id, $plan_id) {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pms_member_subscriptions 
            WHERE user_id = %d AND subscription_plan_id = %d AND status = 'active'",
            $user_id, $plan_id
        ));
        return !empty($result);
    }
    
    // Check user's access levels
    $has_complete_access = user_has_subscription($user_id, $complete_access_id);
    $has_newsletter_access = user_has_subscription($user_id, $newsletter_access_id) || $has_complete_access;
    $has_webinar_access = user_has_subscription($user_id, $webinar_access_id) || $has_complete_access;
    
    // Check content permissions directly from the PMS capability system
    if (!$has_newsletter_access && function_exists('pms_is_member')) {
        $has_newsletter_access = pms_is_member($user_id, array($newsletter_access_id, $complete_access_id));
    }
    
    if (!$has_webinar_access && function_exists('pms_is_member')) {
        $has_webinar_access = pms_is_member($user_id, array($webinar_access_id, $complete_access_id));
    }
    
    // Also check if user has administrator role (admins typically have access to everything)
    $is_admin = in_array('administrator', $current_user->roles);
    if ($is_admin) {
        $has_newsletter_access = true;
        $has_webinar_access = true;
    }
    
    // Get all user subscriptions
    $member_subscriptions = array();
    if (class_exists('PMS_Member_Subscriptions')) {
        $member_subscriptions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pms_member_subscriptions 
            WHERE user_id = %d",
            $user_id
        ));
    }
?>
<div class="bg-light py-5">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm dashboard-header">
                    <div class="card-body text-center p-5">
                        <h1 class="display-5 fw-bold text-primary mb-3">Member Dashboard</h1>
                        <p class="lead mb-0">Welcome back, <strong><?php echo esc_html($current_user->display_name); ?></strong>!</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Main Dashboard Content -->
            <div class="col-lg-8">
                <!-- Subscriptions Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-4 border-0">
                        <h2 class="card-title mb-0 fs-4"><i class="fas fa-id-card me-2 text-primary"></i>My Subscriptions</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($member_subscriptions) || $has_newsletter_access || $has_webinar_access) : ?>
                            <?php if (!empty($member_subscriptions)) : ?>
                                <?php foreach ($member_subscriptions as $subscription) : 
                                    // Get subscription plan details directly from the database
                                    $plan = $wpdb->get_row($wpdb->prepare(
                                        "SELECT * FROM {$wpdb->prefix}pms_subscription_plans WHERE id = %d",
                                        $subscription->subscription_plan_id
                                    ));
                                    
                                    $plan_name = $plan ? $plan->name : 'Subscription #' . $subscription->subscription_plan_id;
                                    $status_class = ($subscription->status == 'active') ? 'success' : 'secondary';
                                ?>
                                <div class="card subscription-item mb-3 border-<?php echo $status_class; ?>">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="fw-bold"><?php echo esc_html($plan_name); ?></h5>
                                                
                                                <?php if ($subscription->status == 'active') : ?>
                                                    <?php if (!empty($subscription->expiration_date) && $subscription->expiration_date != '0000-00-00 00:00:00') : ?>
                                                        <p class="text-muted mb-0">
                                                            <span class="badge bg-<?php echo $status_class; ?> me-2">Active</span>
                                                            Expires: <?php echo date_i18n(get_option('date_format'), strtotime($subscription->expiration_date)); ?>
                                                        </p>
                                                    <?php else : ?>
                                                        <p class="text-muted mb-0">
                                                            <span class="badge bg-<?php echo $status_class; ?>">Active</span>
                                                        </p>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <p class="text-muted mb-0">
                                                        <span class="badge bg-<?php echo $status_class; ?>">
                                                            <?php echo ucfirst($subscription->status); ?>
                                                        </span>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                <a href="<?php echo esc_url(site_url('/account/')); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-cog me-1"></i> Manage
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <!-- User has access but no formal subscription records -->
                                <div class="card subscription-item mb-3 border-success">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="fw-bold">Content Access</h5>
                                                <p class="text-muted mb-0">
                                                    <span class="badge bg-success me-2">Active</span>
                                                    <?php if ($has_newsletter_access && $has_webinar_access) : ?>
                                                        You have access to all premium content
                                                    <?php elseif ($has_newsletter_access) : ?>
                                                        You have access to newsletter content
                                                    <?php elseif ($has_webinar_access) : ?>
                                                        You have access to webinar content
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                <a href="<?php echo esc_url(site_url('/account/')); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-cog me-1"></i> Manage
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <div class="alert alert-info">
                                <p class="mb-0"><i class="fas fa-info-circle me-2"></i> You don't have any active subscriptions.</p>
                            </div>
                            <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-1"></i> View Subscription Options
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($has_newsletter_access) : 
                    // Query recent newsletters
                    $recent_newsletters = new WP_Query(array(
                        'post_type' => 'newsletter',
                        'posts_per_page' => 5,
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'post_status' => 'publish'
                    ));
                    
                    if ($recent_newsletters->have_posts()) : 
                ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-4 border-0">
                        <h2 class="card-title mb-0 fs-4"><i class="fas fa-newspaper me-2 text-primary"></i>Recent Newsletters</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="list-group list-group-flush">
                            <?php while ($recent_newsletters->have_posts()) : $recent_newsletters->the_post(); ?>
                                <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col-md-9">
                                            <h5 class="mb-1"><a href="<?php the_permalink(); ?>" class="text-decoration-none"><?php the_title(); ?></a></h5>
                                            <p class="text-muted mb-2 small">
                                                <i class="far fa-calendar-alt me-1"></i> <?php echo get_the_date(); ?>
                                            </p>
											<div class="text-muted mb-md-0 mb-3">
                                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-md-end">
                                            <?php if (function_exists('get_field') && get_field('newsletter_pdf')) : ?>
                                                <a href="<?php echo esc_url(get_field('newsletter_pdf')); ?>" class="btn btn-sm btn-outline-secondary mb-2 mb-md-0 me-md-2" target="_blank" download>
                                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-book-open me-1"></i> Read
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center border-0 pt-0 pb-4">
                        <a href="<?php echo esc_url(get_post_type_archive_link('newsletter')); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list me-1"></i> View All Newsletters
                        </a>
                    </div>
                </div>
                <?php 
                    endif;
                endif; 
                ?>
                
                <?php if ($has_webinar_access) : 
                    // Upcoming Webinars
                    $upcoming_webinars = new WP_Query(array(
                        'post_type' => 'webinar',
                        'posts_per_page' => 3,
                        'meta_key' => 'webinar_date',
                        'meta_value' => date('Y-m-d H:i:s'),
                        'meta_compare' => '>',
                        'meta_type' => 'DATETIME',
                        'orderby' => 'meta_value',
                        'order' => 'ASC',
                        'post_status' => 'publish'
                    ));
                    
                    if ($upcoming_webinars->have_posts()) : 
                ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-4 border-0">
                        <h2 class="card-title mb-0 fs-4"><i class="fas fa-video me-2 text-primary"></i>Upcoming Webinars</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="list-group list-group-flush">
                            <?php while ($upcoming_webinars->have_posts()) : $upcoming_webinars->the_post(); 
                                $webinar_date = '';
                                if (function_exists('get_field')) {
                                    $webinar_date = get_field('webinar_date');
                                }
                                
                                // Only continue if we have a date
                                if (!empty($webinar_date)) :
                                    $webinar_timestamp = strtotime($webinar_date);
                                    $days_until = floor(($webinar_timestamp - time()) / (60 * 60 * 24));
                            ?>
                                <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                    <div class="row">
                                        <div class="col-md-3 mb-3 mb-md-0">
                                            <div class="d-flex flex-column align-items-center justify-content-center bg-light rounded p-3 h-100">
                                                <div class="fs-3 fw-bold text-primary"><?php echo date('d', $webinar_timestamp); ?></div>
                                                <div class="text-uppercase"><?php echo date('M', $webinar_timestamp); ?></div>
                                                <div class="badge bg-primary mt-1"><?php echo date('g:i A', $webinar_timestamp); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <h5 class="mb-1"><a href="<?php the_permalink(); ?>" class="text-decoration-none"><?php the_title(); ?></a></h5>
                                            
                                            <?php if ($days_until <= 1) : ?>
                                                <div class="badge bg-danger mb-2">Starts in <?php echo ($days_until == 0) ? 'less than 24 hours' : '1 day'; ?></div>
                                            <?php elseif ($days_until <= 7) : ?>
                                                <div class="badge bg-warning mb-2">Starts in <?php echo $days_until; ?> days</div>
                                            <?php endif; ?>
                                            
                                            <div class="text-muted mb-3">
                                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                            </div>
                                            
                                            <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-info-circle me-1"></i> Webinar Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endwhile; wp_reset_postdata(); 
                            ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center border-0 pt-0 pb-4">
                        <a href="<?php echo esc_url(get_post_type_archive_link('webinar')); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list me-1"></i> View All Webinars
                        </a>
                    </div>
                </div>
                <?php 
                    endif;
                endif; 
                ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Account Management Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-4 border-0">
                        <h2 class="card-title mb-0 fs-4"><i class="fas fa-user-circle me-2 text-primary"></i>My Account</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-grid gap-2">
                            <a href="<?php echo esc_url(site_url('/account/')); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-user-edit me-2"></i> Edit Profile
                            </a>
                            <a href="<?php echo esc_url(site_url('/account/subscriptions/')); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-credit-card me-2"></i> Manage Subscriptions
                            </a>
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Log Out
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (!$has_newsletter_access || !$has_webinar_access) : 
                    // Get available subscription plans directly from the database
                    $subscription_plans = $wpdb->get_results(
                        "SELECT * FROM {$wpdb->prefix}pms_subscription_plans 
                        WHERE id IN (10, 11, 12) 
                        ORDER BY price ASC"
                    );
                    
                    // If no results, use fallback data
                    if (empty($subscription_plans)) {
                        // Fallback data
                        $subscription_plans = array(
                            (object) array(
                                'id' => 10,
                                'name' => 'Newsletter Access',
                                'price' => 39.00,
                                'description' => 'Access all our newsletter content.',
                                'duration' => 1,
                                'duration_unit' => 'month'
                            ),
                            (object) array(
                                'id' => 11,
                                'name' => 'Webinar Access',
                                'price' => 49.00,
                                'description' => 'Access all our webinar content.',
                                'duration' => 1, 
                                'duration_unit' => 'month'
                            ),
                            (object) array(
                                'id' => 12,
                                'name' => 'Complete Access Bundle',
                                'price' => 69.00,
                                'description' => 'Access all newsletters and webinars at a discounted rate.',
                                'duration' => 1,
                                'duration_unit' => 'month'
                            )
                        );
                    }
                ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-4 border-0">
                        <h2 class="card-title mb-0 fs-4"><i class="fas fa-rocket me-2 text-primary"></i>Upgrade Your Membership</h2>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Enhance your experience with RTS Capital by upgrading your membership to access premium content.</p>
                        
                        <?php foreach ($subscription_plans as $plan) : 
                            // Skip plans the user already has
                            if (($plan->id == $newsletter_access_id && $has_newsletter_access) || 
                                ($plan->id == $webinar_access_id && $has_webinar_access) ||
                                ($plan->id == $complete_access_id)) {
                                continue;
                            }
                        ?>
                            <div class="card mb-3 border-primary">
                                <div class="card-body p-3">
                                    <h5 class="card-title fw-bold"><?php echo esc_html($plan->name); ?></h5>
                                    <div class="fs-4 fw-bold text-primary mb-2">
                                        $<?php echo number_format((float)$plan->price, 2); ?>
                                        <?php if (isset($plan->duration) && $plan->duration > 0) : ?>
                                            <span class="fs-6 text-muted fw-normal">
                                                / <?php echo $plan->duration; ?> 
                                                <?php echo $plan->duration > 1 ? $plan->duration_unit . 's' : $plan->duration_unit; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="card-text small mb-3">
                                        <?php echo isset($plan->description) ? esc_html($plan->description) : ''; ?>
                                    </p>
                                    <a href="<?php echo esc_url(site_url('/register/?subscription_plan=' . $plan->id)); ?>" class="btn btn-primary btn-sm d-block">
                                        <i class="fas fa-arrow-circle-up me-1"></i> Upgrade Now
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Resources or Help Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white p-4 border-0">
                        <h2 class="card-title mb-0 fs-4"><i class="fas fa-question-circle me-2 text-primary"></i>Need Help?</h2>
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-3">Have questions about your membership or need assistance?</p>
                        <div class="d-grid gap-2">
                            <a href="<?php echo esc_url(site_url('/contact/')); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i> Contact Support
                            </a>
                            <a href="<?php echo esc_url(site_url('/faq/')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-book me-2"></i> View FAQs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    get_footer();
} catch (Exception $e) {
    // Log the error
    error_log('Member dashboard error: ' . $e->getMessage());
    
    // Complete any partially rendered HTML
    ?>
    </div></div></div>
    <div class="container py-5">
        <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
            <h4>Sorry, there was an error loading the dashboard</h4>
            <p>Please try again later or contact support if the problem persists.</p>
            <a href="<?php echo esc_url(home_url()); ?>" class="btn btn-primary mt-3">Return to Homepage</a>
        </div>
    </div>
    <?php
    get_footer();
}
// End output buffering
ob_end_flush();