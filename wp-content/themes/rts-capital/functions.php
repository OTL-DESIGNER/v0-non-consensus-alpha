<?php
function rts_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'rts'),
        'footer' => __('Footer Menu', 'rts'),
    ));
}
add_action('after_setup_theme', 'rts_theme_setup');

// Enqueue scripts and styles with Bootstrap
function rts_scripts() {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/bootstrap/css/bootstrap.min.css', array(), '5.3.3');
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
    
    // Theme main style
    wp_enqueue_style('rts-style', get_stylesheet_uri());
    
    // Custom styles that override Bootstrap
    wp_enqueue_style('rts-custom', get_template_directory_uri() . '/assets/css/rts-custom.css', array('bootstrap'), '1.0.0');
    
    // Bootstrap Bundle JS (includes Popper)
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/bootstrap/js/bootstrap.bundle.min.js', array('jquery'), '5.3.3', true);
    
    // Main theme JS
    wp_enqueue_script('rts-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery', 'bootstrap'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'rts_scripts');

// Include custom post types
require get_template_directory() . '/inc/custom-post-types.php';
// Include Zoom meeting restriction functionality
require_once get_template_directory() . '/template-parts/content-restricted-zoom.php';

/**
 * Enqueue styles and scripts specifically for the homepage
 */
function nca_home_page_assets() {
    if (is_page_template('page-home-template.php')) {
        // Enqueue Slick Carousel (if not already loaded)
        wp_enqueue_style('slick-carousel', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), '1.8.1');
        wp_enqueue_style('slick-carousel-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array(), '1.8.1');
        wp_enqueue_script('slick-carousel', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
        
        // Home page specific styles - add this CSS to your existing stylesheet or create a new one
        wp_enqueue_style('nca-home-styles', get_template_directory_uri() . '/assets/css/home-styles.css', array(), '1.0.0');
    }
}
add_action('wp_enqueue_scripts', 'nca_home_page_assets', 15); // Higher priority than your main enqueue function
/**
 * Enqueue page-specific styles and scripts
 */
function rts_enqueue_page_assets() {
    // Global pages that need Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');

    // Webinar pages
    if (is_singular('webinar') || is_post_type_archive('webinar')) {
        wp_enqueue_style('rts-webinar', get_template_directory_uri() . '/assets/css/webinar-styles.css', array(), '1.0.0');
        
        // Only load countdown JS on webinar single page
        if (is_singular('webinar')) {
            wp_enqueue_script('rts-countdown', get_template_directory_uri() . '/assets/js/countdown.js', array('jquery'), '1.0.0', true);
        }
    }

    // Newsletter archive & sidebar styles
    if (is_post_type_archive('newsletter') || is_singular('newsletter')) {
        wp_enqueue_style('rts-newsletter', get_template_directory_uri() . '/assets/css/archive-newsletter.css', array(), '1.0.0');
    }

    // Restricted content styles - load on both webinar and newsletter pages for non-members
    if ((is_singular('webinar') || is_singular('newsletter')) &&
        !rts_user_has_access(get_current_user_id(), get_post_type())) {
        wp_enqueue_style('rts-restricted-content', get_template_directory_uri() . '/assets/css/restricted-content.css', array(), '1.0.0');
    }

    // Member dashboard page
    if (is_page_template('page-member-dashboard.php')) {
        wp_enqueue_style('rts-dashboard', get_template_directory_uri() . '/assets/css/dashboard.css', array(), '1.0.0');
        wp_enqueue_script('rts-dashboard-script', get_template_directory_uri() . '/assets/js/dashboard.js', array('jquery'), '1.0.0', true);
    }
if (is_page('register')) {
        wp_enqueue_style(
            'rts-register-custom',
            get_stylesheet_directory_uri() . '/assets/css/register-page.css',
            [],
            '1.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'rts_enqueue_page_assets');



// Define subscription plan constants for easier reference
if (!defined('RTS_NEWSLETTER_PLAN_ID')) {
    define('RTS_NEWSLETTER_PLAN_ID', 10);
}

if (!defined('RTS_WEBINAR_PLAN_ID')) {
    define('RTS_WEBINAR_PLAN_ID', 11);
}

if (!defined('RTS_BUNDLE_PLAN_ID')) {
    define('RTS_BUNDLE_PLAN_ID', 12);
}

/**
 * Check if a user has access to specific content
 * 
 * @param int $user_id The user ID to check
 * @param string $content_type Either 'newsletter' or 'webinar'
 * @return bool Whether the user has access
 */
function rts_user_has_access($user_id, $content_type) {
    if (!function_exists('pms_is_member_of_plan')) {
        return false;
    }
    
    // Check for complete access first (has access to everything)
    if (pms_is_member_of_plan($user_id, RTS_BUNDLE_PLAN_ID)) {
        return true;
    }
    
    // Check for specific content type access
    if ($content_type == 'newsletter') {
        return pms_is_member_of_plan($user_id, RTS_NEWSLETTER_PLAN_ID);
    } else if ($content_type == 'webinar') {
        return pms_is_member_of_plan($user_id, RTS_WEBINAR_PLAN_ID);
    }
    
    return false;
}

/**
 * Get subscription name by ID
 * 
 * @param int $plan_id The subscription plan ID
 * @return string The subscription plan name
 */
function rts_get_subscription_name($plan_id) {
    if (!function_exists('pms_get_subscription_plan')) {
        return '';
    }
    
    $plan = pms_get_subscription_plan($plan_id);
    if ($plan) {
        return $plan->name;
    }
    
    return '';
}

/**
 * Format webinar date in a readable format
 * 
 * @param string $date_string The date string from ACF
 * @param bool $include_time Whether to include the time
 * @return string Formatted date
 */
function rts_format_webinar_date($date_string, $include_time = true) {
    if (empty($date_string)) {
        return '';
    }
    
    $timestamp = strtotime($date_string);
    
    if ($include_time) {
        return date_i18n(get_option('date_format') . ' @ ' . get_option('time_format'), $timestamp);
    } else {
        return date_i18n(get_option('date_format'), $timestamp);
    }
}

/**
 * Get Zoom webinar registration link
 * Helper function for webinar templates
 * 
 * @param string $webinar_id The Zoom webinar ID
 * @return string The registration URL or empty string
 */
function rts_get_webinar_registration_link($webinar_id) {
    if (empty($webinar_id)) {
        return '';
    }
    
    // For now, just return a direct link to the webinar detail page
    // This can be expanded to integrate with Zoom API if needed
    return get_permalink();
}

/**
 * Add custom body class for member dashboard
 */
function rts_body_classes($classes) {
    if (is_page_template('page-member-dashboard.php')) {
        $classes[] = 'member-dashboard-page';
    }
    
    return $classes;
}
add_filter('body_class', 'rts_body_classes');

// Auto-restrict content based on post type
function rts_auto_restrict_content($post_id) {
    // Skip if this is an autosave or revision
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    
    $post_type = get_post_type($post_id);
    
    // Your actual subscription plan IDs - verify these match your actual plan IDs
    $newsletter_plan_id = 10; // Newsletter Access plan ID
    $webinar_plan_id = 11;    // Webinar Access plan ID
    $bundle_plan_id = 12;     // Complete Bundle plan ID
    
    // Restrict based on post type
    if ($post_type === 'newsletter') {
        // Restrict newsletters to Newsletter plan and Bundle plan
        update_post_meta($post_id, 'pms-content-restrict-type', 'template');
        update_post_meta($post_id, 'pms-content-restrict-subscription-plan', array($newsletter_plan_id, $bundle_plan_id));
        update_post_meta($post_id, 'pms-content-restrict-user-status', 'loggedout');
    } elseif ($post_type === 'webinar') {
        // Restrict webinars to Webinar plan and Bundle plan
        update_post_meta($post_id, 'pms-content-restrict-type', 'template');
        update_post_meta($post_id, 'pms-content-restrict-subscription-plan', array($webinar_plan_id, $bundle_plan_id));
        update_post_meta($post_id, 'pms-content-restrict-user-status', 'loggedout');
    }
}
add_action('save_post', 'rts_auto_restrict_content');


// Include navigation walkers
require get_template_directory() . '/inc/bootstrap-5-nav-walker.php';
require get_template_directory() . '/inc/bootstrap-5-mobile-nav-walker.php';

// Enqueue styles and scripts
function nca_enqueue_nav_scripts() {
    // Enqueue CSS
    wp_enqueue_style(
        'nca-nav-styles',
        get_template_directory_uri() . '/assets/css/nav-styles.css',
        array(),
        '1.0.0'
    );
    
    // Enqueue JS
    wp_enqueue_script(
        'nca-nav-scripts',
        get_template_directory_uri() . '/assets/js/nav-scripts.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'nca_enqueue_nav_scripts');
/**
 * Content Expiration System
 * Unified version that works with the RTS Newsletter Enhancer plugin
 */

// Schedule daily check for expired content
function rts_schedule_expiration_check() {
    if (!wp_next_scheduled('rts_check_expired_content')) {
        wp_schedule_event(time(), 'daily', 'rts_check_expired_content');
    }
}
add_action('wp', 'rts_schedule_expiration_check');

/**
 * Function to check and process expired content
 * Now handles both meta keys for expiration dates
 */
function rts_process_expired_content() {
    $today = date('Y-m-d');
    $results = array(
        'newsletters' => 0,
        'webinars' => 0
    );
    
    // Check newsletters with both possible meta keys
    $expired_newsletters = new WP_Query(array(
        'post_type' => 'newsletter',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'newsletter_expiration',
                'value' => $today,
                'compare' => '<',
                'type' => 'DATE'
            ),
            array(
                'key' => '_rts_newsletter_expiration',
                'value' => $today,
                'compare' => '<',
                'type' => 'DATE'
            )
        ),
        'post_status' => 'publish'
    ));
    
    if ($expired_newsletters->have_posts()) {
        while ($expired_newsletters->have_posts()) {
            $expired_newsletters->the_post();
            // Change status to private instead of deleting
            wp_update_post(array(
                'ID' => get_the_ID(),
                'post_status' => 'private'
            ));
            
            // Log expiration
            error_log('Newsletter expired: ' . get_the_title() . ' (ID: ' . get_the_ID() . ')');
            $results['newsletters']++;
        }
    }
    wp_reset_postdata();
    
    // Check webinars with both possible meta keys
    $expired_webinars = new WP_Query(array(
        'post_type' => 'webinar',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'webinar_expiration',
                'value' => $today,
                'compare' => '<',
                'type' => 'DATE'
            ),
            array(
                'key' => '_rts_webinar_expiration',
                'value' => $today,
                'compare' => '<',
                'type' => 'DATE'
            )
        ),
        'post_status' => 'publish'
    ));
    
    if ($expired_webinars->have_posts()) {
        while ($expired_webinars->have_posts()) {
            $expired_webinars->the_post();
            // Change status to private instead of deleting
            wp_update_post(array(
                'ID' => get_the_ID(),
                'post_status' => 'private'
            ));
            
            // Log expiration
            error_log('Webinar expired: ' . get_the_title() . ' (ID: ' . get_the_ID() . ')');
            $results['webinars']++;
        }
    }
    wp_reset_postdata();
    
    return $results;
}
add_action('rts_check_expired_content', 'rts_process_expired_content');

/**
 * Send notification for content expiring soon (7 days before)
 * Now checks both meta keys for expiration dates
 */
function rts_notify_expiring_content() {
    $seven_days_future = date('Y-m-d', strtotime('+7 days'));
    $results = array(
        'newsletters' => 0,
        'webinars' => 0
    );
    
    // Check newsletters
    $expiring_newsletters = new WP_Query(array(
        'post_type' => 'newsletter',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'newsletter_expiration',
                'value' => $seven_days_future,
                'compare' => '=',
                'type' => 'DATE'
            ),
            array(
                'key' => '_rts_newsletter_expiration',
                'value' => $seven_days_future,
                'compare' => '=',
                'type' => 'DATE'
            )
        ),
        'post_status' => 'publish'
    ));
    
    if ($expiring_newsletters->have_posts()) {
        $message = "The following newsletters will expire in 7 days:\n\n";
        
        while ($expiring_newsletters->have_posts()) {
            $expiring_newsletters->the_post();
            $message .= "- " . get_the_title() . " (ID: " . get_the_ID() . ")\n";
            $results['newsletters']++;
        }
        
        // Send email notification
        wp_mail(
            get_option('admin_email'),
            'Newsletters Expiring Soon',
            $message
        );
    }
    wp_reset_postdata();
    
    // Check webinars
    $expiring_webinars = new WP_Query(array(
        'post_type' => 'webinar',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'webinar_expiration',
                'value' => $seven_days_future,
                'compare' => '=',
                'type' => 'DATE'
            ),
            array(
                'key' => '_rts_webinar_expiration',
                'value' => $seven_days_future,
                'compare' => '=',
                'type' => 'DATE'
            )
        ),
        'post_status' => 'publish'
    ));
    
    if ($expiring_webinars->have_posts()) {
        $message = "The following webinars will expire in 7 days:\n\n";
        
        while ($expiring_webinars->have_posts()) {
            $expiring_webinars->the_post();
            $message .= "- " . get_the_title() . " (ID: " . get_the_ID() . ")\n";
            $results['webinars']++;
        }
        
        // Send email notification
        wp_mail(
            get_option('admin_email'),
            'Webinars Expiring Soon',
            $message
        );
    }
    wp_reset_postdata();
    
    return $results;
}
add_action('rts_check_expired_content', 'rts_notify_expiring_content');

/**
 * Create a dedicated admin page for expiration management
 */
function rts_add_expiration_tools_page() {
    add_management_page(
        'Content Expiration Tools', 
        'Content Expiration', 
        'manage_options', 
        'rts-content-expiration', 
        'rts_expiration_tools_page'
    );
}
add_action('admin_menu', 'rts_add_expiration_tools_page');

/**
 * Render the expiration tools admin page
 */
function rts_expiration_tools_page() {
    // Process form submission
    $processed_results = array();
    $notification_results = array();
    
    // Add sync functionality
    if (isset($_POST['rts_sync_expiration_dates']) && current_user_can('manage_options')) {
        check_admin_referer('rts_expiration_sync', 'rts_expiration_sync_nonce');
        
        // Run the sync function
        rts_sync_all_expiration_dates();
        
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>All expiration dates have been synchronized successfully!</p>';
        echo '</div>';
    }
    
    if (isset($_POST['rts_run_expiration_check']) && current_user_can('manage_options')) {
        check_admin_referer('rts_expiration_check', 'rts_expiration_nonce');
        
        // Process expired content
        $processed_results = rts_process_expired_content();
        
        // Send notifications for soon-to-expire content
        $notification_results = rts_notify_expiring_content();
    }
    
    // Get upcoming expirations for display (checking both meta keys)
    $upcoming_webinars = new WP_Query(array(
        'post_type' => 'webinar',
        'posts_per_page' => 10,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'webinar_expiration',
                'value' => array(date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ),
            array(
                'key' => '_rts_webinar_expiration',
                'value' => array(date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            )
        ),
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'post_status' => 'publish'
    ));
    
    $upcoming_newsletters = new WP_Query(array(
        'post_type' => 'newsletter',
        'posts_per_page' => 10,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'newsletter_expiration',
                'value' => array(date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ),
            array(
                'key' => '_rts_newsletter_expiration',
                'value' => array(date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            )
        ),
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'post_status' => 'publish'
    ));
    
    // Check for recently expired content
    $recent_expired_webinars = new WP_Query(array(
        'post_type' => 'webinar',
        'posts_per_page' => 10,
        'post_status' => 'private',
        'orderby' => 'modified',
        'order' => 'DESC',
    ));
    
    $recent_expired_newsletters = new WP_Query(array(
        'post_type' => 'newsletter',
        'posts_per_page' => 10,
        'post_status' => 'private',
        'orderby' => 'modified',
        'order' => 'DESC',
    ));
    
    // Display the admin page
    ?>
    <div class="wrap">
        <h1>Content Expiration Management</h1>
        
        <?php if (!empty($processed_results) || !empty($notification_results)): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Expiration check completed:</strong></p>
                <ul>
                    <?php if (!empty($processed_results['newsletters'])): ?>
                        <li><?php echo esc_html($processed_results['newsletters']); ?> newsletter(s) marked as private</li>
                    <?php endif; ?>
                    
                    <?php if (!empty($processed_results['webinars'])): ?>
                        <li><?php echo esc_html($processed_results['webinars']); ?> webinar(s) marked as private</li>
                    <?php endif; ?>
                    
                    <?php if (!empty($notification_results['newsletters'])): ?>
                        <li>Sent notification for <?php echo esc_html($notification_results['newsletters']); ?> newsletter(s) expiring soon</li>
                    <?php endif; ?>
                    
                    <?php if (!empty($notification_results['webinars'])): ?>
                        <li>Sent notification for <?php echo esc_html($notification_results['webinars']); ?> webinar(s) expiring soon</li>
                    <?php endif; ?>
                    
                    <?php if (
                        empty($processed_results['newsletters']) && 
                        empty($processed_results['webinars']) && 
                        empty($notification_results['newsletters']) && 
                        empty($notification_results['webinars'])
                    ): ?>
                        <li>No content needed processing at this time</li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Manual check form -->
        <div class="card">
            <h2 class="title">Run Manual Expiration Check</h2>
            <div class="inside">
                <p>Click the button below to manually process expired content and send notifications for content expiring soon.</p>
                <form method="post" action="">
                    <?php wp_nonce_field('rts_expiration_check', 'rts_expiration_nonce'); ?>
                    <p class="submit">
                        <input type="submit" name="rts_run_expiration_check" class="button button-primary" value="Run Expiration Check Now">
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Sync expiration dates -->
        <div class="card">
            <h2 class="title">Synchronize Expiration Dates</h2>
            <div class="inside">
                <p>Click the button below to synchronize expiration dates between plugin and theme meta fields. Use this if you see duplicate or inconsistent expiration dates.</p>
                <form method="post" action="">
                    <?php wp_nonce_field('rts_expiration_sync', 'rts_expiration_sync_nonce'); ?>
                    <p class="submit">
                        <input type="submit" name="rts_sync_expiration_dates" class="button button-secondary" value="Sync All Expiration Dates">
                    </p>
                </form>
            </div>
        </div>
        
        <div class="card">
            <h2 class="title">Next Scheduled Check</h2>
            <div class="inside">
                <?php
                $next_scheduled = wp_next_scheduled('rts_check_expired_content');
                if ($next_scheduled) {
                    echo '<p>Next automatic check: <strong>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_scheduled) . '</strong></p>';
                } else {
                    echo '<p>No automatic check scheduled. Please try deactivating and reactivating this plugin.</p>';
                }
                ?>
            </div>
        </div>
        
        <!-- Content expiring soon -->
        <h2>Content Expiring Soon (Next 30 Days)</h2>
        
        <?php if ($upcoming_newsletters->have_posts() || $upcoming_webinars->have_posts()): ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Expiration Date</th>
                        <th>Days Left</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // List upcoming webinar expirations
                    if ($upcoming_webinars->have_posts()) {
                        while ($upcoming_webinars->have_posts()) {
                            $upcoming_webinars->the_post();
                            
                            // Check both meta fields
                            $expiration_date = get_post_meta(get_the_ID(), 'webinar_expiration', true);
                            if (empty($expiration_date)) {
                                $expiration_date = get_post_meta(get_the_ID(), '_rts_webinar_expiration', true);
                            }
                            
                            if (empty($expiration_date)) {
                                continue; // Skip this item if no expiration date found
                            }
                            
                            $days_left = floor((strtotime($expiration_date) - time()) / (60 * 60 * 24));
                            
                            echo '<tr>';
                            echo '<td><a href="' . esc_url(get_edit_post_link()) . '">' . esc_html(get_the_title()) . '</a></td>';
                            echo '<td>Webinar</td>';
                            echo '<td>' . esc_html($expiration_date) . '</td>';
                            echo '<td>' . ($days_left <= 7 ? '<span style="color:red;">' . esc_html($days_left) . '</span>' : esc_html($days_left)) . '</td>';
                            echo '<td><a href="' . esc_url(get_edit_post_link()) . '" class="button button-small">Edit</a></td>';
                            echo '</tr>';
                        }
                    }
                    
                    // List upcoming newsletter expirations
                    if ($upcoming_newsletters->have_posts()) {
                        while ($upcoming_newsletters->have_posts()) {
                            $upcoming_newsletters->the_post();
                            
                            // Check both meta fields
                            $expiration_date = get_post_meta(get_the_ID(), 'newsletter_expiration', true);
                            if (empty($expiration_date)) {
                                $expiration_date = get_post_meta(get_the_ID(), '_rts_newsletter_expiration', true);
                            }
                            
                            if (empty($expiration_date)) {
                                continue; // Skip this item if no expiration date found
                            }
                            
                            $days_left = floor((strtotime($expiration_date) - time()) / (60 * 60 * 24));
                            
                            echo '<tr>';
                            echo '<td><a href="' . esc_url(get_edit_post_link()) . '">' . esc_html(get_the_title()) . '</a></td>';
                            echo '<td>Newsletter</td>';
                            echo '<td>' . esc_html($expiration_date) . '</td>';
                            echo '<td>' . ($days_left <= 7 ? '<span style="color:red;">' . esc_html($days_left) . '</span>' : esc_html($days_left)) . '</td>';
                            echo '<td><a href="' . esc_url(get_edit_post_link()) . '" class="button button-small">Edit</a></td>';
                            echo '</tr>';
                        }
                    }
                    wp_reset_postdata();
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No content is expiring in the next 30 days.</p>
        <?php endif; ?>
        
        <!-- Recently expired content -->
        <h2>Recently Expired Content</h2>
        
        <?php if ($recent_expired_newsletters->have_posts() || $recent_expired_webinars->have_posts()): ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Expiration Date</th>
                        <th>Expired On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // List recently expired webinars
                    if ($recent_expired_webinars->have_posts()) {
                        while ($recent_expired_webinars->have_posts()) {
                            $recent_expired_webinars->the_post();
                            
                            // Check both meta fields
                            $expiration_date = get_post_meta(get_the_ID(), 'webinar_expiration', true);
                            if (empty($expiration_date)) {
                                $expiration_date = get_post_meta(get_the_ID(), '_rts_webinar_expiration', true);
                            }
                            
                            echo '<tr>';
                            echo '<td><a href="' . esc_url(get_edit_post_link()) . '">' . esc_html(get_the_title()) . '</a></td>';
                            echo '<td>Webinar</td>';
                            echo '<td>' . esc_html($expiration_date) . '</td>';
                            echo '<td>' . esc_html(get_the_modified_date()) . '</td>';
                            echo '<td>';
                            echo '<a href="' . esc_url(get_edit_post_link()) . '" class="button button-small">Edit</a> ';
                            echo '<a href="' . esc_url(add_query_arg(array(
                                'rts_restore_content' => get_the_ID(),
                                '_wpnonce' => wp_create_nonce('rts_restore_content_' . get_the_ID())
                            ))) . '" class="button button-small">Restore</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    
                    // List recently expired newsletters
                    if ($recent_expired_newsletters->have_posts()) {
                        while ($recent_expired_newsletters->have_posts()) {
                            $recent_expired_newsletters->the_post();
                            
                            // Check both meta fields
                            $expiration_date = get_post_meta(get_the_ID(), 'newsletter_expiration', true);
                            if (empty($expiration_date)) {
                                $expiration_date = get_post_meta(get_the_ID(), '_rts_newsletter_expiration', true);
                            }
                            
                            echo '<tr>';
                            echo '<td><a href="' . esc_url(get_edit_post_link()) . '">' . esc_html(get_the_title()) . '</a></td>';
                            echo '<td>Newsletter</td>';
                            echo '<td>' . esc_html($expiration_date) . '</td>';
                            echo '<td>' . esc_html(get_the_modified_date()) . '</td>';
                            echo '<td>';
                            echo '<a href="' . esc_url(get_edit_post_link()) . '" class="button button-small">Edit</a> ';
                            echo '<a href="' . esc_url(add_query_arg(array(
                                'rts_restore_content' => get_the_ID(),
                                '_wpnonce' => wp_create_nonce('rts_restore_content_' . get_the_ID())
                            ))) . '" class="button button-small">Restore</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    wp_reset_postdata();
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No content has been recently expired.</p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Handle restoring expired content
 */
function rts_handle_content_restore() {
    if (isset($_GET['rts_restore_content']) && isset($_GET['_wpnonce']) && current_user_can('manage_options')) {
        $post_id = intval($_GET['rts_restore_content']);
        
        // Verify nonce
        if (wp_verify_nonce($_GET['_wpnonce'], 'rts_restore_content_' . $post_id)) {
            // Change post status back to published
            wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'publish'
            ));
            
            // Get post type to determine expiration field
            $post_type = get_post_type($post_id);
            $expiration_field = $post_type . '_expiration';
            
            // Set new expiration date (2 months from now)
            $new_expiration = date('Y-m-d', strtotime('+2 months'));
            
            // Update both meta fields for consistency
            update_post_meta($post_id, $expiration_field, $new_expiration);
            update_post_meta($post_id, '_rts_' . $expiration_field, $new_expiration);
            
            // Add admin notice
            add_action('admin_notices', function() use ($post_id) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>Content restored and expiration date extended. <a href="' . esc_url(get_edit_post_link($post_id)) . '">Edit content</a></p>';
                echo '</div>';
            });
        }
        
        // Redirect back to expiration page
        wp_redirect(admin_url('tools.php?page=rts-content-expiration'));
        exit;
    }
}
add_action('admin_init', 'rts_handle_content_restore');

/**
 * Sync all expiration dates between standard and plugin formats
 */
function rts_sync_all_expiration_dates() {
    $posts = get_posts(array(
        'post_type' => array('newsletter', 'webinar'),
        'posts_per_page' => -1,
        'post_status' => array('publish', 'private')
    ));
    
    $synced_count = 0;
    
    foreach ($posts as $post) {
        $post_type = $post->post_type;
        $post_id = $post->ID;
        $standard_key = $post_type . '_expiration';
        $plugin_key = '_rts_' . $standard_key;
        
        // Get values from both keys
        $standard_value = get_post_meta($post_id, $standard_key, true);
        $plugin_value = get_post_meta($post_id, $plugin_key, true);
        
        // If standard exists but plugin doesn't, update plugin
        if (!empty($standard_value) && empty($plugin_value)) {
            update_post_meta($post_id, $plugin_key, $standard_value);
            $synced_count++;
        }
        
        // If plugin exists but standard doesn't, update standard
        elseif (empty($standard_value) && !empty($plugin_value)) {
            update_post_meta($post_id, $standard_key, $plugin_value);
            $synced_count++;
        }
        
        // If both exist but are different, make them match (prefer standard)
        elseif (!empty($standard_value) && !empty($plugin_value) && $standard_value !== $plugin_value) {
            update_post_meta($post_id, $plugin_key, $standard_value);
            $synced_count++;
        }
    }
    
    return $synced_count;
}

/**
 * Sync meta values for the expiration date when one is updated
 * This ensures both systems work together
 */
function rts_sync_expiration_meta($meta_id, $post_id, $meta_key, $meta_value) {
    // Check if we're updating an expiration date
    if ($meta_key === 'newsletter_expiration' || $meta_key === 'webinar_expiration') {
        // Update the plugin's version of the meta key
        update_post_meta($post_id, '_rts_' . $meta_key, $meta_value);
    } 
    elseif (strpos($meta_key, '_rts_') === 0 && (strpos($meta_key, '_expiration') !== false)) {
        // This is the plugin's version of the expiration meta
        // Extract the standard key by removing the '_rts_' prefix
        $standard_key = substr($meta_key, 5);
        
        // Update the standard meta key
        update_post_meta($post_id, $standard_key, $meta_value);
    }
}
add_action('updated_post_meta', 'rts_sync_expiration_meta', 10, 4);
add_action('added_post_meta', 'rts_sync_expiration_meta', 10, 4);

// Add an expiration status column to admin list tables
function rts_add_expiration_column($columns) {
    $columns['expiration'] = 'Expiration';
    return $columns;
}
add_filter('manage_newsletter_posts_columns', 'rts_add_expiration_column');
add_filter('manage_webinar_posts_columns', 'rts_add_expiration_column');

// Fill the expiration column - check both meta keys
function rts_expiration_column_content($column, $post_id) {
    if ($column !== 'expiration') {
        return;
    }
    
    $post_type = get_post_type($post_id);
    $expiration_field = $post_type . '_expiration';
    
    // Check both meta keys - first standard, then plugin version
    $expiration_date = get_post_meta($post_id, $expiration_field, true);
    if (empty($expiration_date)) {
        $expiration_date = get_post_meta($post_id, '_rts_' . $expiration_field, true);
    }
    
    if (!$expiration_date) {
        echo 'No expiration set';
        return;
    }
    
    $expiration_timestamp = strtotime($expiration_date);
    $today = strtotime('today');
    
    if ($expiration_timestamp < $today) {
        echo '<span style="color: red;">Expired: ' . date_i18n(get_option('date_format'), $expiration_timestamp) . '</span>';
    } else {
        $days_until = floor(($expiration_timestamp - $today) / (60 * 60 * 24));
        if ($days_until <= 7) {
            echo '<span style="color: orange;">Expiring soon: ' . date_i18n(get_option('date_format'), $expiration_timestamp) . ' (' . $days_until . ' days)</span>';
        } else {
            echo date_i18n(get_option('date_format'), $expiration_timestamp);
        }
    }
}
add_action('manage_newsletter_posts_custom_column', 'rts_expiration_column_content', 10, 2);
add_action('manage_webinar_posts_custom_column', 'rts_expiration_column_content', 10, 2);

// Make the expiration column sortable
function rts_expiration_column_sortable($columns) {
    $columns['expiration'] = 'expiration';
    return $columns;
}
add_filter('manage_edit-newsletter_sortable_columns', 'rts_expiration_column_sortable');
add_filter('manage_edit-webinar_sortable_columns', 'rts_expiration_column_sortable');

// Handle sorting by expiration date with both meta keys
function rts_expiration_orderby($query) {
    if (!is_admin()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('expiration' === $orderby) {
        $post_type = $query->get('post_type');
        
        // Use more complex meta query to handle both meta key formats
        $query->set('meta_query', array(
            'relation' => 'OR',
            'standard_expiration' => array(
                'key' => $post_type . '_expiration',
                'compare' => 'EXISTS',
            ),
            'plugin_expiration' => array(
                'key' => '_rts_' . $post_type . '_expiration',
                'compare' => 'EXISTS',
            )
        ));
        
        // Order by the meta query clauses with standard taking precedence
        $query->set('orderby', array(
            'standard_expiration' => $query->get('order'),
            'plugin_expiration' => $query->get('order')
        ));
    }
}
add_action('pre_get_posts', 'rts_expiration_orderby');

// Add a link to the expiration tools page in the admin bar
function rts_admin_bar_expiration_link($admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $admin_bar->add_menu(array(
        'id'    => 'rts-expiration-check',
        'title' => 'Content Expiration',
        'href'  => admin_url('tools.php?page=rts-content-expiration'),
        'meta'  => array(
            'title' => 'Content Expiration Management',
        ),
    ));
}
add_action('admin_bar_menu', 'rts_admin_bar_expiration_link', 100);

/**
 * Preserve webinar recording and other custom fields when updating posts
 */
function rts_preserve_webinar_fields($post_id, $post, $update) {
    // Only run on webinar post type updates
    if ($post->post_type !== 'webinar' || !$update) {
        return;
    }
    
    // Check if this is an update from the editor (not our expiration system)
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Preserve webinar recording URL if it exists
    $recording_url = get_field('webinar_recording', $post_id);
    if ($recording_url) {
        // Make sure the field is preserved by updating it again
        update_field('webinar_recording', $recording_url, $post_id);
    }
    
    // You can add other fields that need to be preserved here
    $zoom_id = get_field('zoom_meeting_id', $post_id);
    if ($zoom_id) {
        update_field('zoom_meeting_id', $zoom_id, $post_id);
    }
}
add_action('save_post', 'rts_preserve_webinar_fields', 20, 3);

// Add this line anywhere in your functions.php (preferably near other similar option settings)
update_option('webinar_question_form_id', 122);

