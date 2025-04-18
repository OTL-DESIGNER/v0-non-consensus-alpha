<?php
/**
 * Plugin Name: RTS Scheduled Webinars Extension
 * Description: Extends RTS Zoom Webhook to handle scheduled webinars before recordings are available
 * Version: 1.0
 * Requires at least: 5.6
 * Author: RTS Capital Management
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin page for creating scheduled webinars
 */
function rts_scheduled_webinars_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=webinar',
        'Schedule Webinar',
        'Schedule Webinar',
        'edit_posts',
        'schedule-webinar',
        'rts_scheduled_webinar_page'
    );
}
add_action('admin_menu', 'rts_scheduled_webinars_admin_menu');

/**
 * Admin page for scheduling webinars
 */
function rts_scheduled_webinar_page() {
    $message = '';
    $message_type = '';
    
    // Handle form submission
    if (isset($_POST['rts_schedule_webinar_nonce']) && wp_verify_nonce($_POST['rts_schedule_webinar_nonce'], 'rts_schedule_webinar')) {
        // Get form data
        $title = sanitize_text_field($_POST['webinar_title']);
        $date = sanitize_text_field($_POST['webinar_date']);
        $time = sanitize_text_field($_POST['webinar_time']);
        $presenter = sanitize_text_field($_POST['webinar_presenter']);
        $zoom_id = sanitize_text_field($_POST['webinar_zoom_id']);
        $duration = sanitize_text_field($_POST['webinar_duration']);
        $topics = isset($_POST['webinar_topics']) ? array_map('intval', $_POST['webinar_topics']) : array();
        $content = wp_kses_post($_POST['webinar_content']);
        
        // Create webinar post
        $post_data = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_status'   => 'publish',
            'post_type'     => 'webinar',
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (!is_wp_error($post_id)) {
            // Format datetime
            $datetime = $date . ' ' . $time . ':00';
            
            // Save meta data
            update_post_meta($post_id, 'webinar_date', $datetime);
            update_post_meta($post_id, 'webinar_presenter', $presenter);
            update_post_meta($post_id, 'webinar_zoom_id', $zoom_id);
            update_post_meta($post_id, 'webinar_duration', $duration);
            update_post_meta($post_id, 'zoom_meeting_id', $zoom_id); // For compatibility with webhook plugin
            
            // Set topics/categories
            if (!empty($topics)) {
                wp_set_object_terms($post_id, $topics, 'topic');
            }
            
            // Set subscription restriction if Paid Member Subscriptions is active
            if (function_exists('pms_is_plugin_active')) {
                $webinar_plan_id = 11;    // Webinar Access plan ID
                $bundle_plan_id = 12;     // Complete Bundle plan ID
                update_post_meta($post_id, 'pms-content-restrict-subscription-plan', array($webinar_plan_id, $bundle_plan_id));
            }
            
            $message = 'Webinar scheduled successfully!';
            $message_type = 'success';
            
            // Redirect to edit page
            wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
            exit;
        } else {
            $message = 'Error creating webinar: ' . $post_id->get_error_message();
            $message_type = 'error';
        }
    }
    
    // Get available topics
    $topics = get_terms(array(
        'taxonomy' => 'topic',
        'hide_empty' => false,
    ));
    
    // Default time (1 hour)
    $default_duration = '60 minutes';
    
    // Future date (next week)
    $default_date = date('Y-m-d', strtotime('+1 week'));
    $default_time = '10:00';
    
    ?>
    <div class="wrap">
        <h1>Schedule a Webinar</h1>
        
        <?php if ($message): ?>
            <div class="notice notice-<?php echo $message_type; ?> is-dismissible">
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
        
        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
            <form method="post" action="">
                <?php wp_nonce_field('rts_schedule_webinar', 'rts_schedule_webinar_nonce'); ?>
                
                <div style="margin-bottom: 20px;">
                    <label for="webinar_title" style="display: block; font-weight: bold; margin-bottom: 5px;">Webinar Title:</label>
                    <input type="text" name="webinar_title" id="webinar_title" class="regular-text" required style="width: 100%;" placeholder="Enter webinar title...">
                </div>
                
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label for="webinar_date" style="display: block; font-weight: bold; margin-bottom: 5px;">Date:</label>
                        <input type="date" name="webinar_date" id="webinar_date" value="<?php echo $default_date; ?>" required>
                    </div>
                    
                    <div style="flex: 1;">
                        <label for="webinar_time" style="display: block; font-weight: bold; margin-bottom: 5px;">Time:</label>
                        <input type="time" name="webinar_time" id="webinar_time" value="<?php echo $default_time; ?>" required>
                    </div>
                    
                    <div style="flex: 1;">
                        <label for="webinar_duration" style="display: block; font-weight: bold; margin-bottom: 5px;">Duration:</label>
                        <input type="text" name="webinar_duration" id="webinar_duration" value="<?php echo $default_duration; ?>" required placeholder="e.g., 60 minutes">
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="webinar_presenter" style="display: block; font-weight: bold; margin-bottom: 5px;">Presenter:</label>
                    <input type="text" name="webinar_presenter" id="webinar_presenter" class="regular-text" style="width: 100%;" placeholder="Enter presenter name...">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="webinar_zoom_id" style="display: block; font-weight: bold; margin-bottom: 5px;">Zoom Webinar ID:</label>
                    <input type="text" name="webinar_zoom_id" id="webinar_zoom_id" class="regular-text" required style="width: 100%;" placeholder="Enter Zoom Meeting/Webinar ID...">
                    <p class="description">Enter the Zoom Meeting/Webinar ID (required for linking with recordings later)</p>
                </div>
                
                <?php if (!empty($topics) && !is_wp_error($topics)): ?>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Topics:</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 5px;">
                        <?php foreach ($topics as $topic): ?>
                            <label style="margin-right: 15px; display: inline-flex; align-items: center;">
                                <input type="checkbox" name="webinar_topics[]" value="<?php echo $topic->term_id; ?>">
                                <span style="margin-left: 5px;"><?php echo $topic->name; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div style="margin-bottom: 20px;">
                    <label for="webinar_content" style="display: block; font-weight: bold; margin-bottom: 5px;">Webinar Description:</label>
                    <?php
                    wp_editor('', 'webinar_content', array(
                        'textarea_name' => 'webinar_content',
                        'textarea_rows' => 10,
                        'media_buttons' => true,
                        'teeny' => false,
                    ));
                    ?>
                </div>
                
                <div style="margin-top: 20px;">
                    <input type="submit" class="button button-primary" value="Schedule Webinar">
                </div>
            </form>
        </div>
        
        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
            <h2>How It Works</h2>
            <ol>
                <li>Use this form to schedule upcoming webinars in advance.</li>
                <li>Enter the Zoom Meeting/Webinar ID that will be used for the meeting.</li>
                <li>The webinar will appear in the upcoming webinars list on your site.</li>
                <li>After the webinar is completed and recorded, the recording will automatically be added by the Zoom webhook.</li>
            </ol>
            <p><strong>Important:</strong> Make sure to use the exact same Zoom Meeting/Webinar ID that will be used for the actual webinar so the recording can be properly linked.</p>
        </div>
    </div>
    <?php
}

/**
 * Modify webhook plugin behavior to update existing posts if they exist
 */
function rts_check_existing_webinar_post($meeting_id) {
    if (empty($meeting_id)) {
        return false;
    }
    
    $existing_posts = get_posts(array(
        'post_type' => 'webinar',
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'webinar_zoom_id',
                'value' => $meeting_id,
                'compare' => '='
            ),
            array(
                'key' => 'zoom_meeting_id',
                'value' => $meeting_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    ));
    
    return !empty($existing_posts) ? $existing_posts[0]->ID : false;
}

/**
 * Filter the webhook processing to update existing posts
 * This hooks into the existing webhook plugin's functionality
 */
function rts_process_zapier_webhook_filter($request) {
    // Get the raw request body and parse it as JSON
    $body = $request->get_body();
    $data = json_decode($body, true);
    
    // Fallback to request params if JSON parsing failed
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        $data = $request->get_params();
    }
    
    // Extract meeting ID from payload_object_id (as shown in Zapier config)
    $meeting_id = '';
    if (isset($data['payload_object_id'])) {
        $meeting_id = $data['payload_object_id'];
    }
    
    // Check if we have a post already with this meeting ID
    if (!empty($meeting_id)) {
        $existing_post_id = rts_check_existing_webinar_post($meeting_id);
        
        if ($existing_post_id) {
            error_log("Found existing webinar post ID $existing_post_id for meeting ID $meeting_id");
            
            // Extract video URL
            $video_url = '';
            if (isset($data['1. Share URL:']) || isset($data['1. Share URL'])) {
                $share_url_key = isset($data['1. Share URL:']) ? '1. Share URL:' : '1. Share URL';
                $video_url = $data[$share_url_key];
            }
            
            // Fallback: Try other common fields for the URL
            if (empty($video_url)) {
                $possible_keys = array(
                    'share_url',
                    'download_url',
                    'Share URL'
                );
                
                foreach ($possible_keys as $key) {
                    if (isset($data[$key]) && !empty($data[$key])) {
                        $video_url = $data[$key];
                        break;
                    }
                }
            }
            
            // Extract password
            $password = '';
            $password_field_options = array(
                'Password', 'password', 'passcode', 'Passcode', 
                'recording_password', 'webinar_password', 
                'share_password', 'share_passcode'
            );
            
            foreach ($password_field_options as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    $password = $data[$field];
                    break;
                }
            }
            
            // Update the existing post with recording info
            if (!empty($video_url)) {
                update_post_meta($existing_post_id, 'webinar_recording', $video_url);
                
                if (!empty($password)) {
                    update_post_meta($existing_post_id, 'webinar_password', $password);
                }
                
                // Set expiration date (2 months from now)
                $expiration_date = date('Y-m-d', strtotime('+2 months'));
                update_post_meta($existing_post_id, 'webinar_expiration', $expiration_date);
                
                error_log("Updated existing webinar post ID $existing_post_id with recording URL: $video_url");
                
                // Return early to prevent creating a duplicate post
                return new WP_REST_Response(array(
                    'status' => 'success',
                    'message' => 'Updated existing webinar post with recording',
                    'post_id' => $existing_post_id
                ), 200);
            }
        }
    }
    
    // If no existing post found or update failed, continue with the original webhook handler
    return $request;
}

// Try to hook into RTS Zoom Webhook plugin's processing
// This should run before the main plugin processes the webhook
add_filter('rest_request_before_callbacks', 'rts_filter_webhook_request', 10, 3);

function rts_filter_webhook_request($response, $handler, $request) {
    // Only process requests to our webhook endpoint
  $route = $request->get_route();
if ($route !== null && $route === '/rts/v1/zapier-webhook') {
        $result = rts_process_zapier_webhook_filter($request);
        
        // If our function processed the request and returned a response, return it
        if ($result instanceof WP_REST_Response) {
            return $result;
        }
    }
    
    // Otherwise, return the original response/request to continue normal processing
    return $response;
}

/**
 * Add a dashboard widget to show upcoming webinars
 */
function rts_upcoming_webinars_dashboard_widget() {
    wp_add_dashboard_widget(
        'rts_upcoming_webinars_widget',
        'Upcoming Webinars',
        'rts_display_upcoming_webinars_widget'
    );
}
add_action('wp_dashboard_setup', 'rts_upcoming_webinars_dashboard_widget');

/**
 * Display the upcoming webinars widget
 */
function rts_display_upcoming_webinars_widget() {
    $args = array(
        'post_type'      => 'webinar',
        'posts_per_page' => 5,
        'meta_key'       => 'webinar_date',
        'meta_value'     => date('Y-m-d H:i:s'),
        'meta_compare'   => '>=',
        'meta_type'      => 'DATETIME',
        'orderby'        => 'meta_value',
        'order'          => 'ASC'
    );
    
    $upcoming_webinars = new WP_Query($args);
    
    if ($upcoming_webinars->have_posts()) {
        echo '<table class="widefat">';
        echo '<thead><tr><th>Webinar</th><th>Date</th></tr></thead>';
        echo '<tbody>';
        
        while ($upcoming_webinars->have_posts()) {
            $upcoming_webinars->the_post();
            $webinar_date = get_post_meta(get_the_ID(), 'webinar_date', true);
            $formatted_date = date('M j, Y g:i a', strtotime($webinar_date));
            
            echo '<tr>';
            echo '<td><a href="' . get_edit_post_link() . '">' . get_the_title() . '</a></td>';
            echo '<td>' . $formatted_date . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<p>No upcoming webinars scheduled.</p>';
    }
    
    wp_reset_postdata();
    
    echo '<p><a href="' . admin_url('edit.php?post_type=webinar&page=schedule-webinar') . '" class="button button-primary">Schedule New Webinar</a></p>';
}

/**
 * Add custom meta box for webinar details to the webinar post edit screen
 */
function rts_add_webinar_details_meta_box() {
    add_meta_box(
        'rts_webinar_details',
        'Webinar Details',
        'rts_webinar_details_meta_box_callback',
        'webinar',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'rts_add_webinar_details_meta_box');

/**
 * Callback function for the webinar details meta box
 */
function rts_webinar_details_meta_box_callback($post) {
    wp_nonce_field('rts_save_webinar_details', 'rts_webinar_details_nonce');
    
    // Get saved values
    $webinar_date = get_post_meta($post->ID, 'webinar_date', true);
    $webinar_presenter = get_post_meta($post->ID, 'webinar_presenter', true);
    $webinar_zoom_id = get_post_meta($post->ID, 'webinar_zoom_id', true);
    $webinar_duration = get_post_meta($post->ID, 'webinar_duration', true);
    $webinar_recording = get_post_meta($post->ID, 'webinar_recording', true);
    $webinar_password = get_post_meta($post->ID, 'webinar_password', true);
    
    // Format date and time for inputs
    $date_value = '';
    $time_value = '';
    if (!empty($webinar_date)) {
        $date_time = new DateTime($webinar_date);
        $date_value = $date_time->format('Y-m-d');
        $time_value = $date_time->format('H:i');
    }
    
    ?>
    <style>
    .rts-meta-field {
        margin-bottom: 15px;
    }
    .rts-meta-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .rts-meta-field input[type="text"],
    .rts-meta-field input[type="url"] {
        width: 100%;
    }
    .rts-date-time-group {
        display: flex;
        gap: 10px;
    }
    .rts-date-time-group > div {
        flex: 1;
    }
    </style>
    
    <div class="rts-meta-field rts-date-time-group">
        <div>
            <label for="webinar_date">Date:</label>
            <input type="date" id="webinar_date" name="webinar_date" value="<?php echo esc_attr($date_value); ?>">
        </div>
        <div>
            <label for="webinar_time">Time:</label>
            <input type="time" id="webinar_time" name="webinar_time" value="<?php echo esc_attr($time_value); ?>">
        </div>
    </div>
    
    <div class="rts-meta-field">
        <label for="webinar_duration">Duration:</label>
        <input type="text" id="webinar_duration" name="webinar_duration" value="<?php echo esc_attr($webinar_duration); ?>" placeholder="e.g., 60 minutes">
    </div>
    
    <div class="rts-meta-field">
        <label for="webinar_presenter">Presenter:</label>
        <input type="text" id="webinar_presenter" name="webinar_presenter" value="<?php echo esc_attr($webinar_presenter); ?>" placeholder="Enter presenter name">
    </div>
    
    <div class="rts-meta-field">
        <label for="webinar_zoom_id">Zoom Meeting/Webinar ID:</label>
        <input type="text" id="webinar_zoom_id" name="webinar_zoom_id" value="<?php echo esc_attr($webinar_zoom_id); ?>" placeholder="Enter Zoom ID">
        <p class="description">This ID is used to match the recording with this webinar post.</p>
    </div>
    
    <div class="rts-meta-field">
        <label for="webinar_recording">Recording URL:</label>
        <input type="url" id="webinar_recording" name="webinar_recording" value="<?php echo esc_url($webinar_recording); ?>" placeholder="URL will be added automatically after the webinar">
        <p class="description">This will be added automatically by the webhook when the recording is available.</p>
    </div>
    
    <div class="rts-meta-field">
        <label for="webinar_password">Recording Password:</label>
        <input type="text" id="webinar_password" name="webinar_password" value="<?php echo esc_attr($webinar_password); ?>" placeholder="Password will be added with the recording">
    </div>
    
    <?php
}

/**
 * Save the webinar details when the post is saved
 */
function rts_save_webinar_details($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['rts_webinar_details_nonce'])) {
        return;
    }
    
    // Verify that the nonce is valid
    if (!wp_verify_nonce($_POST['rts_webinar_details_nonce'], 'rts_save_webinar_details')) {
        return;
    }
    
    // If this is an autosave, we don't want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check the user's permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save date and time
    if (isset($_POST['webinar_date']) && !empty($_POST['webinar_date'])) {
        $date = sanitize_text_field($_POST['webinar_date']);
        $time = isset($_POST['webinar_time']) ? sanitize_text_field($_POST['webinar_time']) : '00:00';
        $datetime = $date . ' ' . $time . ':00';
        update_post_meta($post_id, 'webinar_date', $datetime);
    }
    
    // Save presenter
    if (isset($_POST['webinar_presenter'])) {
        update_post_meta($post_id, 'webinar_presenter', sanitize_text_field($_POST['webinar_presenter']));
    }
    
    // Save Zoom ID (in both meta fields for compatibility)
    if (isset($_POST['webinar_zoom_id'])) {
        $zoom_id = sanitize_text_field($_POST['webinar_zoom_id']);
        update_post_meta($post_id, 'webinar_zoom_id', $zoom_id);
        update_post_meta($post_id, 'zoom_meeting_id', $zoom_id);
    }
    
    // Save duration
    if (isset($_POST['webinar_duration'])) {
        update_post_meta($post_id, 'webinar_duration', sanitize_text_field($_POST['webinar_duration']));
    }
    
    // Save recording URL
    if (isset($_POST['webinar_recording'])) {
        update_post_meta($post_id, 'webinar_recording', esc_url_raw($_POST['webinar_recording']));
    }
    
    // Save password
    if (isset($_POST['webinar_password'])) {
        update_post_meta($post_id, 'webinar_password', sanitize_text_field($_POST['webinar_password']));
    }
}
add_action('save_post_webinar', 'rts_save_webinar_details');

/**
 * Add custom columns to the webinar post list
 */
function rts_add_webinar_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        if ($key == 'title') {
            $new_columns[$key] = $value;
            $new_columns['webinar_date'] = 'Date & Time';
            $new_columns['webinar_zoom_id'] = 'Zoom ID';
            $new_columns['webinar_status'] = 'Status';
        } else if ($key != 'date') {
            $new_columns[$key] = $value;
        }
    }
    
    return $new_columns;
}
add_filter('manage_webinar_posts_columns', 'rts_add_webinar_columns');

/**
 * Fill custom columns with content
 */
function rts_webinar_custom_column($column, $post_id) {
    switch ($column) {
        case 'webinar_date':
            $webinar_date = get_post_meta($post_id, 'webinar_date', true);
            if (!empty($webinar_date)) {
                echo date('M j, Y g:i a', strtotime($webinar_date));
            } else {
                echo '—';
            }
            break;
            
        case 'webinar_zoom_id':
            $webinar_zoom_id = get_post_meta($post_id, 'webinar_zoom_id', true);
            if (empty($webinar_zoom_id)) {
                $webinar_zoom_id = get_post_meta($post_id, 'zoom_meeting_id', true);
            }
            echo !empty($webinar_zoom_id) ? esc_html($webinar_zoom_id) : '—';
            break;
            
        case 'webinar_status':
            $webinar_date = get_post_meta($post_id, 'webinar_date', true);
            $webinar_recording = get_post_meta($post_id, 'webinar_recording', true);
            
            if (empty($webinar_date)) {
                echo '<span class="dashicons dashicons-warning" style="color:#999;" title="Date not set"></span>';
            } else if (strtotime($webinar_date) > current_time('timestamp')) {
                echo '<span class="dashicons dashicons-calendar-alt" style="color:#0073aa;" title="Upcoming"></span>';
            } else if (!empty($webinar_recording)) {
                echo '<span class="dashicons dashicons-video-alt3" style="color:#46b450;" title="Recorded"></span>';
            } else {
                echo '<span class="dashicons dashicons-backup" style="color:#ffb900;" title="Awaiting Recording"></span>';
            }
            break;
    }
}
add_action('manage_webinar_posts_custom_column', 'rts_webinar_custom_column', 10, 2);

/**
 * Make custom columns sortable
 */
function rts_webinar_sortable_columns($columns) {
    $columns['webinar_date'] = 'webinar_date';
    return $columns;
}
add_filter('manage_edit-webinar_sortable_columns', 'rts_webinar_sortable_columns');

/**
 * Handle sorting
 */
function rts_webinar_custom_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ($query->get('post_type') != 'webinar') {
        return;
    }
    
    if ($query->get('orderby') == 'webinar_date') {
        $query->set('meta_key', 'webinar_date');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'rts_webinar_custom_orderby');

/**
 * Add a filter dropdown for webinar status
 */
function rts_add_webinar_status_filter() {
    global $typenow;
    
    if ($typenow != 'webinar') {
        return;
    }
    
    $current_status = isset($_GET['webinar_status']) ? $_GET['webinar_status'] : '';
    
    ?>
    <select name="webinar_status" id="filter-by-webinar-status">
        <option value=""<?php selected($current_status, ''); ?>>All Statuses</option>
        <option value="upcoming"<?php selected($current_status, 'upcoming'); ?>>Upcoming</option>
        <option value="recorded"<?php selected($current_status, 'recorded'); ?>>Recorded</option>
        <option value="awaiting"<?php selected($current_status, 'awaiting'); ?>>Awaiting Recording</option>
        <option value="nodate"<?php selected($current_status, 'nodate'); ?>>No Date Set</option>
    </select>
    <?php
}
add_action('restrict_manage_posts', 'rts_add_webinar_status_filter');

/**
 * Modify query for status filter
 */
function rts_filter_webinars_by_status($query) {
    global $pagenow, $typenow;
    
    if ($pagenow !== 'edit.php' || $typenow !== 'webinar' || !isset($_GET['webinar_status'])) {
        return;
    }
    
    $status = $_GET['webinar_status'];
    $now = current_time('mysql');
    
    switch ($status) {
        case 'upcoming':
            $query->query_vars['meta_query'] = array(
                array(
                    'key'     => 'webinar_date',
                    'value'   => $now,
                    'compare' => '>',
                    'type'    => 'DATETIME'
                )
            );
            $query->query_vars['orderby'] = 'meta_value';
            $query->query_vars['meta_key'] = 'webinar_date';
            $query->query_vars['order'] = 'ASC';
            break;
            
        case 'recorded':
            $query->query_vars['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'key'     => 'webinar_recording',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key'     => 'webinar_recording',
                    'value'   => '',
                    'compare' => '!='
                )
            );
            break;
            
        case 'awaiting':
            $query->query_vars['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'key'     => 'webinar_date',
                    'value'   => $now,
                    'compare' => '<',
                    'type'    => 'DATETIME'
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'webinar_recording',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'     => 'webinar_recording',
                        'value'   => '',
                        'compare' => '='
                    )
                )
            );
            break;
            
        case 'nodate':
            $query->query_vars['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key'     => 'webinar_date',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key'     => 'webinar_date',
                    'value'   => '',
                    'compare' => '='
                )
            );
            break;
    }
}
add_action('pre_get_posts', 'rts_filter_webinars_by_status');

/**
 * Add a shortcode to display upcoming webinars
 */
function rts_upcoming_webinars_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count' => 3,
        'show_date' => 'yes',
        'show_presenter' => 'yes',
        'show_description' => 'yes',
        'button_text' => 'View Details',
        'class' => ''
    ), $atts, 'upcoming_webinars');
    
    $args = array(
        'post_type'      => 'webinar',
        'posts_per_page' => intval($atts['count']),
        'meta_key'       => 'webinar_date',
        'meta_value'     => date('Y-m-d H:i:s'),
        'meta_compare'   => '>=',
        'meta_type'      => 'DATETIME',
        'orderby'        => 'meta_value',
        'order'          => 'ASC'
    );
    
    $upcoming_webinars = new WP_Query($args);
    
    ob_start();
    
    if ($upcoming_webinars->have_posts()) {
        echo '<div class="upcoming-webinars-list ' . esc_attr($atts['class']) . '">';
        
        while ($upcoming_webinars->have_posts()) {
            $upcoming_webinars->the_post();
            
            // Get meta data
            $webinar_date = get_post_meta(get_the_ID(), 'webinar_date', true);
            $webinar_presenter = get_post_meta(get_the_ID(), 'webinar_presenter', true);
            
            // Format date
            $formatted_date = '';
            if (!empty($webinar_date)) {
                $formatted_date = date('F j, Y g:i a', strtotime($webinar_date));
            }
            
            echo '<div class="webinar-item">';
            
            echo '<h3 class="webinar-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
            
            if ($atts['show_date'] == 'yes' && !empty($formatted_date)) {
                echo '<div class="webinar-date"><i class="fas fa-calendar-alt"></i> ' . $formatted_date . '</div>';
            }
            
            if ($atts['show_presenter'] == 'yes' && !empty($webinar_presenter)) {
                echo '<div class="webinar-presenter"><i class="fas fa-user"></i> Presented by: ' . esc_html($webinar_presenter) . '</div>';
            }
            
            if ($atts['show_description'] == 'yes') {
                echo '<div class="webinar-excerpt">' . get_the_excerpt() . '</div>';
            }
            
            echo '<a href="' . get_permalink() . '" class="webinar-button">' . esc_html($atts['button_text']) . '</a>';
            
            echo '</div>';
        }
        
        echo '</div>';
    } else {
        echo '<p>No upcoming webinars scheduled.</p>';
    }
    
    wp_reset_postdata();
    
    return ob_get_clean();
}
add_shortcode('upcoming_webinars', 'rts_upcoming_webinars_shortcode');

/**
 * Add a shortcode to display past webinar recordings
 */
function rts_past_webinars_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count' => 3,
        'show_date' => 'yes',
        'show_presenter' => 'yes',
        'show_description' => 'yes',
        'button_text' => 'Watch Recording',
        'class' => ''
    ), $atts, 'past_webinars');
    
    $args = array(
        'post_type'      => 'webinar',
        'posts_per_page' => intval($atts['count']),
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'webinar_recording',
                'compare' => 'EXISTS'
            ),
            array(
                'key'     => 'webinar_recording',
                'value'   => '',
                'compare' => '!='
            )
        ),
        'meta_key'       => 'webinar_date',
        'orderby'        => 'meta_value',
        'order'          => 'DESC'
    );
    
    $past_webinars = new WP_Query($args);
    
    ob_start();
    
    if ($past_webinars->have_posts()) {
        echo '<div class="past-webinars-list ' . esc_attr($atts['class']) . '">';
        
        while ($past_webinars->have_posts()) {
            $past_webinars->the_post();
            
            // Get meta data
            $webinar_date = get_post_meta(get_the_ID(), 'webinar_date', true);
            $webinar_presenter = get_post_meta(get_the_ID(), 'webinar_presenter', true);
            
            // Format date
            $formatted_date = '';
            if (!empty($webinar_date)) {
                $formatted_date = date('F j, Y', strtotime($webinar_date));
            }
            
            echo '<div class="webinar-item">';
            
            if (has_post_thumbnail()) {
                echo '<div class="webinar-thumbnail">';
                echo '<a href="' . get_permalink() . '">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</a>';
                echo '</div>';
            }
            
            echo '<h3 class="webinar-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
            
            if ($atts['show_date'] == 'yes' && !empty($formatted_date)) {
                echo '<div class="webinar-date"><i class="fas fa-calendar-alt"></i> ' . $formatted_date . '</div>';
            }
            
            if ($atts['show_presenter'] == 'yes' && !empty($webinar_presenter)) {
                echo '<div class="webinar-presenter"><i class="fas fa-user"></i> Presented by: ' . esc_html($webinar_presenter) . '</div>';
            }
            
            if ($atts['show_description'] == 'yes') {
                echo '<div class="webinar-excerpt">' . get_the_excerpt() . '</div>';
            }
            
            echo '<a href="' . get_permalink() . '" class="webinar-button">' . esc_html($atts['button_text']) . '</a>';
            
            echo '</div>';
        }
        
        echo '</div>';
    } else {
        echo '<p>No recorded webinars available.</p>';
    }
    
    wp_reset_postdata();
    
    return ob_get_clean();
}
add_shortcode('past_webinars', 'rts_past_webinars_shortcode');

/**
 * Add CSS for the webinar shortcodes
 */
function rts_webinar_shortcode_styles() {
    ?>
    <style type="text/css">
        .upcoming-webinars-list,
        .past-webinars-list {
            margin: 20px 0;
        }
        
        .webinar-item {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .webinar-title {
            margin-bottom: 10px;
        }
        
        .webinar-date,
        .webinar-presenter {
            margin-bottom: 5px;
            color: #666;
        }
        
        .webinar-excerpt {
            margin: 10px 0;
        }
        
        .webinar-button {
            display: inline-block;
            padding: 8px 16px;
            background-color: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            margin-top: 10px;
        }
        
        .webinar-button:hover {
            background-color: #005a87;
            color: #fff;
        }
        
        .webinar-thumbnail {
            margin-bottom: 15px;
        }
        
        .webinar-thumbnail img {
            max-width: 100%;
            height: auto;
        }
    </style>
    <?php
}
add_action('wp_head', 'rts_webinar_shortcode_styles');

/**
 * Add widget for upcoming webinars
 */
class RTS_Upcoming_Webinars_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'rts_upcoming_webinars_widget',
            'Upcoming Webinars',
            array('description' => 'Display a list of upcoming webinars')
        );
    }
    
    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Upcoming Webinars';
        $count = !empty($instance['count']) ? intval($instance['count']) : 3;
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        }
        
        echo do_shortcode('[upcoming_webinars count="' . $count . '" show_description="no"]');
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Upcoming Webinars';
        $count = !empty($instance['count']) ? intval($instance['count']) : 3;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('count'); ?>">Number of webinars to show:</label>
            <input type="number" class="tiny-text" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" value="<?php echo esc_attr($count); ?>" min="1" max="10" step="1">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['count'] = (!empty($new_instance['count'])) ? intval($new_instance['count']) : 3;
        
        return $instance;
    }
}

/**
 * Register the widgets
 */
function rts_register_webinar_widgets() {
    register_widget('RTS_Upcoming_Webinars_Widget');
}
add_action('widgets_init', 'rts_register_webinar_widgets');

/**
 * Automatically send email notification when a recording is added
 */
function rts_webinar_recording_notification($meta_id, $post_id, $meta_key, $meta_value) {
    // Only proceed if we're adding a recording URL
    if ($meta_key !== 'webinar_recording' || empty($meta_value)) {
        return;
    }
    
    // Get post information
    $post = get_post($post_id);
    
    if (!$post || $post->post_type !== 'webinar') {
        return;
    }
    
    // Don't send notification if this is a new post being created
    if (get_post_meta($post_id, 'rts_recording_notification_sent', true)) {
        return;
    }
    
    // Send notification to admin
    $admin_email = get_option('admin_email');
    $subject = 'Webinar Recording Added: ' . $post->post_title;
    
    $message = "Hello,\n\n";
    $message .= "A recording has been added to the webinar: " . $post->post_title . "\n\n";
    $message .= "View the webinar: " . get_permalink($post_id) . "\n\n";
    $message .= "Edit the webinar: " . admin_url('post.php?post=' . $post_id . '&action=edit') . "\n\n";
    $message .= "Recording URL: " . $meta_value . "\n\n";
    $message .= "This is an automated notification from your WordPress site.";
    
    wp_mail($admin_email, $subject, $message);
    
    // Mark notification as sent
    update_post_meta($post_id, 'rts_recording_notification_sent', true);
}
add_action('added_post_meta', 'rts_webinar_recording_notification', 10, 4);
add_action('updated_post_meta', 'rts_webinar_recording_notification', 10, 4);
