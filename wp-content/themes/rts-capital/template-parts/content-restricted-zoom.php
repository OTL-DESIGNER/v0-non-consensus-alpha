<?php
/**
 * Simple content restriction for STM Zoom plugin posts
 * Restricts access to members with plan IDs 11 or 12
 */

/**
 * Check if user has access to webinar content
 */
function rts_user_has_webinar_access() {
    if (!is_user_logged_in()) {
        return false;
    }

    if (function_exists('pms_is_member_of_plan')) {
        $user_id = get_current_user_id();
        if (pms_is_member_of_plan($user_id, 11) || pms_is_member_of_plan($user_id, 12)) {
            return true;
        }
    }

    if (current_user_can('administrator')) {
        return true;
    }

    return false;
}

/**
 * Hook into the content filter to restrict zoom meeting posts
 */
function rts_restrict_zoom_meetings($content) {
    if (!is_singular('stm-zoom')) {
        return $content;
    }

    if (rts_user_has_webinar_access()) {
        return $content;
    }

    return rts_get_zoom_restricted_content();
}
add_filter('the_content', 'rts_restrict_zoom_meetings', 999);

/**
 * Generate the restricted content template for Zoom meetings
 */
function rts_get_zoom_restricted_content() {
    $post_id = get_the_ID();
    $title = get_the_title();
    $excerpt = get_the_excerpt();
    $featured_image = get_the_post_thumbnail_url($post_id, 'large') ?: get_template_directory_uri() . '/assets/images/default-webinar.jpg';
    $zoom_data = get_post_meta($post_id, 'stm_zoom_data', true) ?: array();

    $meeting_date = '';
    if (!empty($zoom_data['start_date']) && !empty($zoom_data['start_time'])) {
        $meeting_timestamp = strtotime($zoom_data['start_date'] . ' ' . $zoom_data['start_time']);
        $meeting_date = date('F j, Y \a\t g:i a', $meeting_timestamp);
    }

    $webinar_price = '49';
    $complete_price = '69';
    $register_url = site_url('/membership/');

    $output = '<div class="zoom-meeting-restricted">';
    $output .= '<div class="meeting-header">';
    $output .= '<h1 class="meeting-title">' . esc_html($title) . '</h1>';

    if ($meeting_date) {
        $output .= '<div class="meeting-meta"><div class="meta-item meeting-date">';
        $output .= '<i class="fas fa-calendar-alt"></i><span>' . esc_html($meeting_date) . '</span>';
        $output .= '</div></div>';
    }
    $output .= '</div>';

    $output .= '<div class="meeting-content-preview"><div class="meeting-image">';
    $output .= '<img src="' . esc_url($featured_image) . '" alt="' . esc_attr($title) . '"></div>';

    $output .= '<div class="meeting-excerpt"><p>' . esc_html($excerpt) . '</p>';
    $output .= '<div class="content-blur"><div class="blur-overlay"></div><div class="lock-message">';
    $output .= '<i class="fas fa-lock"></i>';
    $output .= '<h3>This meeting is exclusively for our subscribers</h3>';
    $output .= '<p>Gain access to this meeting and our entire archive of expert insights with a subscription.</p>';
    $output .= '</div></div></div></div>';

    $output .= '<div class="subscription-options"><h2>Subscribe to Access This Meeting</h2><div class="plan-cards">';

    $output .= '<div class="plan-card"><h3>Webinar Access</h3>';
    $output .= '<div class="plan-price">$' . esc_html($webinar_price) . '<span>/month</span></div>';
    $output .= '<ul class="plan-features">';
    $output .= '<li><i class="fas fa-check"></i> Access to all live webinars</li>';
    $output .= '<li><i class="fas fa-check"></i> Complete webinar archive</li>';
    $output .= '<li><i class="fas fa-check"></i> Downloadable presentation slides</li>';
    $output .= '<li><i class="fas fa-check"></i> Priority Q&A submission</li>';
    $output .= '</ul>';
    $output .= '<a href="' . esc_url(add_query_arg('subscription_plan', 11, $register_url)) . '" class="subscribe-button">Subscribe Now</a>';
    $output .= '</div>';

    $output .= '<div class="plan-card featured-plan"><span class="best-value">Best Value</span><h3>Complete Access</h3>';
    $output .= '<div class="plan-price">$' . esc_html($complete_price) . '<span>/month</span></div>';
    $output .= '<ul class="plan-features">';
    $output .= '<li><i class="fas fa-check"></i> <strong>All webinar benefits</strong></li>';
    $output .= '<li><i class="fas fa-check"></i> Full newsletter access</li>';
    $output .= '<li><i class="fas fa-check"></i> Newsletter archive</li>';
    $output .= '<li><i class="fas fa-check"></i> Market analysis reports</li>';
    $output .= '<li><i class="fas fa-check"></i> Monthly investment outlook</li>';
    $output .= '</ul>';
    $output .= '<a href="' . esc_url(add_query_arg('subscription_plan', 12, $register_url)) . '" class="subscribe-button primary-button">Get Complete Access</a>';
    $output .= '</div></div>';

    $output .= '<div class="guarantee"><i class="fas fa-shield-alt"></i>';
    $output .= '<p><strong>30-Day Money Back Guarantee</strong> - If you\'re not completely satisfied, we\'ll refund your subscription, no questions asked.</p></div></div>';

    $output .= '<div class="related-meetings"><h3>Upcoming Meetings</h3>';

    // Get upcoming meetings
    $today = date('Y-m-d');
    $today_ts = strtotime($today);

    $all_meetings = new WP_Query(array(
        'post_type' => 'stm-zoom',
        'posts_per_page' => -1,
        'post__not_in' => array($post_id),
    ));

    $upcoming_meeting_posts = [];

    if ($all_meetings->have_posts()) {
        while ($all_meetings->have_posts()) {
            $all_meetings->the_post();
            $meta = get_post_meta(get_the_ID(), 'stm_zoom_data', true);
            if (!empty($meta['start_date'])) {
                $start_ts = strtotime($meta['start_date']);
                if ($start_ts >= $today_ts) {
                    $upcoming_meeting_posts[] = array(
                        'ID' => get_the_ID(),
                        'title' => get_the_title(),
                        'permalink' => get_permalink(),
                        'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: get_template_directory_uri() . '/assets/images/default-webinar-thumb.jpg',
                        'start_date' => $meta['start_date']
                    );
                }
            }
        }
        wp_reset_postdata();

        usort($upcoming_meeting_posts, function ($a, $b) {
            return strtotime($a['start_date']) - strtotime($b['start_date']);
        });

        $upcoming_meeting_posts = array_slice($upcoming_meeting_posts, 0, 3);
    }

    if (!empty($upcoming_meeting_posts)) {
        $output .= '<div class="upcoming-meetings-grid">';
        foreach ($upcoming_meeting_posts as $meeting) {
            $display_date = date('M j', strtotime($meeting['start_date']));
            $output .= '<div class="meeting-card"><div class="meeting-card-image">';
            $output .= '<img src="' . esc_url($meeting['thumbnail']) . '" alt="' . esc_attr($meeting['title']) . '">';
            $output .= '<span class="meeting-date-badge">' . esc_html($display_date) . '</span>';
            $output .= '</div>';
            $output .= '<h4><a href="' . esc_url($meeting['permalink']) . '">' . esc_html($meeting['title']) . '</a></h4>';
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '<p class="view-all"><a href="' . get_post_type_archive_link('stm-zoom') . '">';
        $output .= 'View all upcoming meetings <i class="fas fa-arrow-right"></i></a></p>';
    } else {
        $output .= '<p>No upcoming meetings scheduled at this time.</p>';
    }

    $output .= '</div></div>';

    return $output;
}

/**
 * Make Zoom meeting archives show only meetings for logged in subscribers 
 */
function rts_restrict_zoom_archive($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('stm-zoom')) {
        if (!rts_user_has_webinar_access()) {
            add_action('stm_zoom_after_archive_title', 'rts_show_login_required_message');
            $query->set('post__in', array(0));
        }
    }
    return $query;
}
add_action('pre_get_posts', 'rts_restrict_zoom_archive');

/**
 * Show login required message on zoom archive
 */
function rts_show_login_required_message() {
    ?>
    <div class="zoom-login-required">
        <i class="fas fa-lock"></i>
        <h3>Access to Zoom meetings requires a subscription</h3>
        <p>Please log in or subscribe to view our upcoming Zoom meetings and webinars.</p>
        <div class="zoom-auth-buttons">
            <a href="<?php echo wp_login_url(get_post_type_archive_link('stm-zoom')); ?>" class="zoom-login-button">Log In</a>
            <a href="<?php echo site_url('/membership/'); ?>" class="zoom-subscribe-button">Subscribe Now</a>
        </div>
    </div>
    <style>
        .zoom-login-required {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .zoom-login-required i {
            font-size: 48px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .zoom-login-required h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .zoom-auth-buttons {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        
        .zoom-login-button, .zoom-subscribe-button {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        
        .zoom-login-button {
            background-color: #2c3e50;
            color: white;
        }
        
        .zoom-login-button:hover {
            background-color: #1e2b38;
            color: white;
        }
        
        .zoom-subscribe-button {
            background-color: #1abc9c;
            color: white;
        }
        
        .zoom-subscribe-button:hover {
            background-color: #16a085;
            color: white;
        }
    </style>
    <?php
}

/**
 * Change the layout/styling of the Zoom meeting countdown
 */
function rts_customize_zoom_countdown() {
    if (is_singular('stm-zoom')) {
        ?>
        <style>
            /* Override the default Zoom meeting countdown styles */
            .meeting-custom-fields {
                max-width: 1100px !important;
                margin: 0 auto !important;
                padding: 0 20px !important;
            }
            
            .stm-countdown {
                background-color: #f8f9fa !important;
                border-radius: 10px !important;
                padding: 30px !important;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
                margin-bottom: 40px !important;
            }
            
            .stm-countdown h3 {
                font-size: 24px !important;
                margin-bottom: 20px !important;
                text-align: center !important;
                color: #2c3e50 !important;
            }
            
            .stm-countdown-unit {
                background-color: #2c3e50 !important;
                color: white !important;
                padding: 15px !important;
                border-radius: 8px !important;
                margin: 0 5px !important;
            }
            
            .stm-countdown-number {
                font-size: 36px !important;
                font-weight: 700 !important;
            }
            
            .stm-countdown-label {
                font-size: 14px !important;
                text-transform: uppercase !important;
                letter-spacing: 1px !important;
            }
            
            .join-buttons {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 15px !important;
                justify-content: center !important;
                margin-top: 30px !important;
            }
            
            .zoom-join-btn, .zoom-join-via-app-btn {
                padding: 12px 24px !important;
                border-radius: 6px !important;
                text-decoration: none !important;
                font-weight: 600 !important;
                transition: all 0.3s ease !important;
                font-size: 16px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-width: 200px !important;
            }
            
            .zoom-join-btn {
                background-color: #1abc9c !important;
                color: white !important;
            }
            
            .zoom-join-btn:hover {
                background-color: #16a085 !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
            }
            
            .zoom-join-via-app-btn {
                background-color: #2c3e50 !important;
                color: white !important;
            }
            
            .zoom-join-via-app-btn:hover {
                background-color: #1e2b38 !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
            }
            
            .stm-meetings-sidebar {
                background-color: white !important;
                border-radius: 10px !important;
                padding: 30px !important;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'rts_customize_zoom_countdown');