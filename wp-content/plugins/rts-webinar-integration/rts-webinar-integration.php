<?php
/**
 * Plugin Name: RTS eRoom-Webinar Integration
 * Description: Automatically creates webinar posts when meetings are scheduled in eRoom and integrates with the Zoom webhook
 * Version: 1.0
 * Requires at least: 5.6
 * Author: RTS Capital Management
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class RTS_Webinar_Integration {
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Log plugin initialization
        error_log('[RTS Webinar Integration] Plugin initialized');
        
        // Hook into eRoom Zoom meeting creation/update
        add_action('save_post_stm-zoom', array($this, 'handle_eroom_meeting_save'), 10, 3);
        
        // Add filter to modify the Zoom webhook plugin's behavior
        add_filter('rest_request_before_callbacks', array($this, 'filter_webhook_request'), 9, 3);
        
        // Filter for webinar post links to point to the Zoom meeting page if not recorded yet
        /*add_filter('post_type_link', array($this, 'modify_webinar_permalink'), 99, 2);*/
        
        // Add admin notice if the required plugins aren't active
        add_action('admin_notices', array($this, 'check_dependencies'));
        
        // Add meta box to Zoom meetings to show linked webinar post
        add_action('add_meta_boxes', array($this, 'add_linked_webinar_meta_box'));
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add AJAX handler for manual sync
        add_action('wp_ajax_rts_sync_meeting_to_webinar', array($this, 'ajax_sync_meeting_to_webinar'));

            // Debug all actions on stm-zoom posts
    add_action('save_post', function($post_id, $post, $update) {
        if (is_object($post) && $post->post_type === 'stm-zoom') {
            error_log('[RTS Debug] save_post action fired for stm-zoom post #' . $post_id);
        }
    }, 10, 3);
    }
    
    /**
     * Check if required plugins are active
     */
    public function check_dependencies() {
        error_log('[RTS Webinar Integration] Checking dependencies');
        if (!class_exists('StmZoom') || !function_exists('rts_handle_zapier_webhook')) {
            error_log('[RTS Webinar Integration] Dependencies missing: StmZoom class exists: ' . (class_exists('StmZoom') ? 'yes' : 'no') . ', rts_handle_zapier_webhook function exists: ' . (function_exists('rts_handle_zapier_webhook') ? 'yes' : 'no'));
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>RTS eRoom-Webinar Integration:</strong> This plugin requires both the eRoom Zoom Meetings plugin and the RTS Zoom Webhook plugin to be active.</p>';
            echo '</div>';
        } else {
            error_log('[RTS Webinar Integration] All dependencies present');
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('post.php' !== $hook) {
            return;
        }
        
        $screen = get_current_screen();
        error_log('[RTS Webinar Integration] Enqueue scripts - Screen: ' . (is_object($screen) ? $screen->id : 'null'));
        
        if (!($screen && $screen->post_type === 'stm-zoom')) {
            return;
        }
        
        error_log('[RTS Webinar Integration] Enqueuing admin script for eRoom meeting edit page');
        wp_enqueue_script(
            'rts-webinar-admin',
            plugin_dir_url(__FILE__) . 'js/admin.js',
            array('jquery'),
            '1.0',
            true
        );
        
        wp_localize_script('rts-webinar-admin', 'rtsWebinarAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rts_webinar_nonce')
        ));
    }
    
    /**
     * Handle when an eRoom Zoom meeting is created or updated
     */
    public function handle_eroom_meeting_save($post_id, $post, $update) {
        error_log('[RTS Webinar Integration] handle_eroom_meeting_save called - Post ID: ' . $post_id . ', Update: ' . ($update ? 'yes' : 'no'));
        
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            error_log('[RTS Webinar Integration] Skipping - doing autosave');
            return;
        }
        if (wp_is_post_revision($post_id)) {
            error_log('[RTS Webinar Integration] Skipping - is revision');
            return;
        }
        if ($post->post_status !== 'publish') {
            error_log('[RTS Webinar Integration] Skipping - post status is not publish: ' . $post->post_status);
            return;
        }
        
        // Get Zoom meeting data from eRoom
 // Try different meta fields to find the Zoom meeting ID
$zoom_meeting_id = '';

// First check for encoded Zoom data
$zoom_data = get_post_meta($post_id, 'stm_zoom_data', true);
if (!empty($zoom_data) && is_string($zoom_data)) {
    $decoded_data = json_decode($zoom_data, true);
    if (is_array($decoded_data) && isset($decoded_data['id'])) {
        $zoom_meeting_id = $decoded_data['id'];
        error_log('[RTS Webinar Integration] Found Zoom ID in stm_zoom_data JSON: ' . $zoom_meeting_id);
    }
}

// If still empty, try to use the post ID as a fallback
if (empty($zoom_meeting_id)) {
    $zoom_meeting_id = 'eroom_' . $post_id;
    error_log('[RTS Webinar Integration] Using post ID as Zoom ID fallback: ' . $zoom_meeting_id);
}
 
        error_log('[RTS Webinar Integration] Zoom meeting ID: ' . ($zoom_meeting_id ? $zoom_meeting_id : 'not found'));
        
        if (empty($zoom_meeting_id)) {
            error_log('[RTS Webinar Integration] No Zoom meeting ID found, skipping');
            return;
        }
        
        // Check if this is a recurring meeting and handle appropriately
        $recurring = get_post_meta($post_id, 'stm_zoom_meeting_recurring', true);
        error_log('[RTS Webinar Integration] Recurring meeting: ' . ($recurring ? 'yes' : 'no'));
        
        // Get all post meta to see what's available
        $all_meta = get_post_meta($post_id);
        error_log('[RTS Webinar Integration] Available meta keys: ' . implode(', ', array_keys($all_meta)));
        
        // Get other meeting details
        $title = get_the_title($post_id);
        $meeting_time = get_post_meta($post_id, 'stm_zoom_meeting_start_time', true);
        $meeting_duration = get_post_meta($post_id, 'stm_zoom_meeting_duration', true);
        $meeting_timezone = get_post_meta($post_id, 'stm_zoom_meeting_timezone', true);
        $meeting_host = get_post_meta($post_id, 'stm_zoom_meeting_host', true); 
        
        error_log('[RTS Webinar Integration] Meeting details - Title: ' . $title . 
                  ', Time: ' . ($meeting_time ? $meeting_time : 'not set') . 
                  ', Duration: ' . ($meeting_duration ? $meeting_duration : 'not set') . 
                  ', Timezone: ' . ($meeting_timezone ? $meeting_timezone : 'not set') . 
                  ', Host: ' . ($meeting_host ? $meeting_host : 'not set'));
        
        // Get the meeting description
        $content = $post->post_content;
        
        // Convert start time to correct format if needed
        if (!empty($meeting_time)) {
            error_log('[RTS Webinar Integration] Processing meeting time: ' . $meeting_time);
            // Parse the time according to timezone if available
            if (!empty($meeting_timezone)) {
                try {
                    $date = new DateTime($meeting_time, new DateTimeZone($meeting_timezone));
                    // Convert to site timezone
                    $wp_timezone = wp_timezone();
                    $date->setTimezone($wp_timezone);
                    $formatted_date = $date->format('Y-m-d H:i:s');
                    error_log('[RTS Webinar Integration] Converted time with timezone: ' . $formatted_date);
                } catch (Exception $e) {
                    error_log('[RTS Webinar Integration] Error converting date with timezone: ' . $e->getMessage());
                    $formatted_date = date('Y-m-d H:i:s', strtotime($meeting_time));
                }
            } else {
                $formatted_date = date('Y-m-d H:i:s', strtotime($meeting_time));
                error_log('[RTS Webinar Integration] Converted time without timezone: ' . $formatted_date);
            }
        } else {
            $formatted_date = current_time('mysql');
            error_log('[RTS Webinar Integration] Using current time: ' . $formatted_date);
        }
        
        // Check if we already have a webinar post for this meeting
        $existing_webinar = $this->get_webinar_by_zoom_id($zoom_meeting_id);
        
        if ($existing_webinar) {
            error_log('[RTS Webinar Integration] Found existing webinar post: ' . $existing_webinar->ID);
            // Update existing webinar post
            $webinar_id = $existing_webinar->ID;
            
            $webinar_data = array(
                'ID'           => $webinar_id,
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish'
            );
            
            error_log('[RTS Webinar Integration] Updating existing webinar post with data: ' . json_encode($webinar_data));
            $update_result = wp_update_post($webinar_data);
            
            if (is_wp_error($update_result)) {
                error_log('[RTS Webinar Integration] Error updating webinar post: ' . $update_result->get_error_message());
                return;
            }
            
            error_log('[RTS Webinar Integration] Updated existing webinar post #' . $webinar_id . ' for meeting #' . $zoom_meeting_id);
        } else {
            error_log('[RTS Webinar Integration] No existing webinar post found, creating new one');
            // Create new webinar post
            $webinar_data = array(
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'webinar'
            );
            
            error_log('[RTS Webinar Integration] Creating new webinar post with data: ' . json_encode($webinar_data));
            $webinar_id = wp_insert_post($webinar_data);
            
            if (is_wp_error($webinar_id)) {
                error_log('[RTS Webinar Integration] Error creating webinar post: ' . $webinar_id->get_error_message());
                return;
            }
            
            error_log('[RTS Webinar Integration] Created new webinar post #' . $webinar_id . ' for meeting #' . $zoom_meeting_id);
        }
        
        // Update webinar metadata
        error_log('[RTS Webinar Integration] Setting meta data for webinar #' . $webinar_id);
        update_post_meta($webinar_id, 'webinar_zoom_id', $zoom_meeting_id);
        update_post_meta($webinar_id, 'zoom_meeting_id', $zoom_meeting_id);  // For compatibility with webhook plugin
        update_post_meta($webinar_id, 'webinar_date', $formatted_date);
        update_post_meta($webinar_id, 'webinar_duration', $meeting_duration . ' minutes');
        update_post_meta($webinar_id, 'webinar_presenter', $meeting_host);
        update_post_meta($webinar_id, 'eroom_meeting_id', $post_id);  // Store reference to eRoom meeting
        
        // Store the webinar ID in the eRoom meeting post meta
        update_post_meta($post_id, 'linked_webinar_post_id', $webinar_id);
        error_log('[RTS Webinar Integration] Linked meeting #' . $post_id . ' to webinar #' . $webinar_id);
        
        // Set subscription restriction if Paid Member Subscriptions is active
        if (function_exists('pms_is_plugin_active')) {
            $webinar_plan_id = 11;    // Webinar Access plan ID - change to match your setup
            $bundle_plan_id = 12;     // Complete Bundle plan ID - change to match your setup
            update_post_meta($webinar_id, 'pms-content-restrict-subscription-plan', array($webinar_plan_id, $bundle_plan_id));
            error_log('[RTS Webinar Integration] Set subscription restrictions for webinar #' . $webinar_id);
        }
        
        // Set any taxonomies if needed
        if (!empty($_POST['tax_input']) && !empty($_POST['tax_input']['topic'])) {
            wp_set_object_terms($webinar_id, $_POST['tax_input']['topic'], 'topic');
            error_log('[RTS Webinar Integration] Set topics for webinar #' . $webinar_id);
        }
        
        error_log('[RTS Webinar Integration] Webinar post creation/update completed successfully');
    }
    
    /**
     * Get webinar post by Zoom meeting ID
     */
    public function get_webinar_by_zoom_id($zoom_meeting_id) {
        if (empty($zoom_meeting_id)) {
            error_log('[RTS Webinar Integration] get_webinar_by_zoom_id called with empty ID');
            return false;
        }
        
        error_log('[RTS Webinar Integration] Looking for webinar with Zoom ID: ' . $zoom_meeting_id);
        
        $args = array(
            'post_type' => 'webinar',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'webinar_zoom_id',
                    'value' => $zoom_meeting_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'zoom_meeting_id',
                    'value' => $zoom_meeting_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        );
        
        $webinars = get_posts($args);
        
        if (!empty($webinars)) {
            error_log('[RTS Webinar Integration] Found webinar post #' . $webinars[0]->ID);
            return $webinars[0];
        } else {
            error_log('[RTS Webinar Integration] No webinar post found for Zoom ID: ' . $zoom_meeting_id);
            return false;
        }
    }
    
    /**
     * Filter the webhook processing to update existing posts
     */
    public function filter_webhook_request($response, $handler, $request) {
        // Only process requests to our webhook endpoint
        $route = $request->get_route();
        error_log('[RTS Webinar Integration] filter_webhook_request - Route: ' . $route);
        
        if ($route === '/rts/v1/zapier-webhook' || $route === '/rts/v1/zoom-webhook') {
            error_log('[RTS Webinar Integration] Processing webhook request');
            
            // Get the request body and parse it
            $body = $request->get_body();
            $data = json_decode($body, true);
            
            // Fallback to request params if JSON parsing failed
            if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
                error_log('[RTS Webinar Integration] JSON parsing failed, using request params');
                $data = $request->get_params();
            }
            
            error_log('[RTS Webinar Integration] Webhook data: ' . substr(print_r($data, true), 0, 1000) . '...');
            
            // Extract meeting ID from payload
            $meeting_id = '';
            if (isset($data['payload_object_id'])) {
                $meeting_id = $data['payload_object_id'];
            } else if (isset($data['payload']) && isset($data['payload']['object']) && isset($data['payload']['object']['id'])) {
                $meeting_id = $data['payload']['object']['id'];
            }
            
            error_log('[RTS Webinar Integration] Extracted meeting ID: ' . ($meeting_id ? $meeting_id : 'not found'));
            
            // Check if we have a webinar post with this meeting ID
            if (!empty($meeting_id)) {
                $existing_webinar = $this->get_webinar_by_zoom_id($meeting_id);
                
                if ($existing_webinar) {
                    error_log('[RTS Webinar Integration] Found existing webinar post #' . $existing_webinar->ID . ' for meeting #' . $meeting_id . ' in webhook');
                    
                    // Extract recording URL
                    $recording_url = $this->extract_recording_url_from_data($data);
                    error_log('[RTS Webinar Integration] Extracted recording URL: ' . ($recording_url ? $recording_url : 'not found'));
                    
                    // Extract password
                    $password = $this->extract_password_from_data($data);
                    error_log('[RTS Webinar Integration] Extracted password: ' . ($password ? 'yes (found)' : 'not found'));
                    
                    // Update the existing webinar post with recording info
                    if (!empty($recording_url)) {
                        update_post_meta($existing_webinar->ID, 'webinar_recording', $recording_url);
                        
                        if (!empty($password)) {
                            update_post_meta($existing_webinar->ID, 'webinar_password', $password);
                        }
                        
                        // Set expiration date (2 months from now)
                        $expiration_date = date('Y-m-d', strtotime('+2 months'));
                        update_post_meta($existing_webinar->ID, 'webinar_expiration', $expiration_date);
                        
                        error_log('[RTS Webinar Integration] Updated existing webinar post #' . $existing_webinar->ID . ' with recording URL: ' . $recording_url);
                        
                        // Return a success response to prevent creating a duplicate post
                        error_log('[RTS Webinar Integration] Returning success response to webhook');
                        return new WP_REST_Response(array(
                            'status' => 'success',
                            'message' => 'Updated existing webinar post with recording',
                            'post_id' => $existing_webinar->ID
                        ), 200);
                    } else {
                        error_log('[RTS Webinar Integration] No recording URL found in webhook data');
                    }
                } else {
                    error_log('[RTS Webinar Integration] No existing webinar post found for meeting ID: ' . $meeting_id);
                }
            } else {
                error_log('[RTS Webinar Integration] Could not extract meeting ID from webhook data');
            }
        }
        
        // Continue with normal processing if no existing post found or update failed
        error_log('[RTS Webinar Integration] Continuing with normal webhook processing');
        return $response;
    }
    
    /**
     * Extract recording URL from webhook data
     */
    private function extract_recording_url_from_data($data) {
        error_log('[RTS Webinar Integration] Extracting recording URL from data');
        $video_url = '';
        
        // Look for Share URL based on Zapier config structure
        if (isset($data['1. Share URL:']) || isset($data['1. Share URL'])) {
            $share_url_key = isset($data['1. Share URL:']) ? '1. Share URL:' : '1. Share URL';
            $video_url = $data[$share_url_key];
            error_log('[RTS Webinar Integration] Found URL in primary field: ' . $share_url_key);
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
                    error_log('[RTS Webinar Integration] Found URL in fallback field: ' . $key);
                    break;
                }
            }
        }
        
        // Try to find URL in recording_files array from direct Zoom webhook
        if (empty($video_url) && isset($data['payload']) && isset($data['payload']['object']) && 
            isset($data['payload']['object']['recording_files']) && is_array($data['payload']['object']['recording_files'])) {
            
            $recording_files = $data['payload']['object']['recording_files'];
            error_log('[RTS Webinar Integration] Found recording_files array, length: ' . count($recording_files));
            
            // First try to find MP4
            foreach ($recording_files as $file) {
                if (isset($file['file_type']) && $file['file_type'] === 'MP4' && isset($file['download_url'])) {
                    $video_url = $file['download_url'];
                    error_log('[RTS Webinar Integration] Found MP4 download URL');
                    break;
                }
            }
            
            // If no MP4, use any available
            if (empty($video_url)) {
                foreach ($recording_files as $file) {
                    if (isset($file['download_url'])) {
                        $video_url = $file['download_url'];
                        error_log('[RTS Webinar Integration] Found non-MP4 download URL');
                        break;
                    }
                }
            }
        }
        
        error_log('[RTS Webinar Integration] Final extracted URL: ' . ($video_url ? $video_url : 'none found'));
        return $video_url;
    }
    
    /**
     * Extract password from webhook data
     */
    private function extract_password_from_data($data) {
        error_log('[RTS Webinar Integration] Extracting password from data');
        $password = '';
        $password_field_options = array(
            'Password', 'password', 'passcode', 'Passcode', 
            'recording_password', 'webinar_password', 
            'share_password', 'share_passcode'
        );
        
        foreach ($password_field_options as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $password = $data[$field];
                error_log('[RTS Webinar Integration] Found password in field: ' . $field);
                break;
            }
        }
        
        return $password;
    }
    
    /**
     * Modify the permalink for webinar posts to point to the Zoom meeting page
     * if no recording is available yet
     */
/*
public function modify_webinar_permalink($permalink, $post) {
    // Only modify permalinks for our webinar post type, never for stm-zoom posts
    if ($post->post_type !== 'webinar') {
        return $permalink;
    }
    
    // If there's no recording yet, and we have an eRoom meeting ID,
    // link to the eRoom meeting page instead
    $recording_url = get_post_meta($post->ID, 'webinar_recording', true);
    
    if (empty($recording_url)) {
        $eroom_meeting_id = get_post_meta($post->ID, 'eroom_meeting_id', true);
        
        if (!empty($eroom_meeting_id)) {
            $eroom_permalink = get_permalink($eroom_meeting_id);
            
            if ($eroom_permalink) {
                error_log('[RTS Webinar Integration] Modifying permalink for webinar #' . $post->ID . ' to eRoom meeting permalink: ' . $eroom_permalink);
                return $eroom_permalink;
            }
        }
    }
    
    return $permalink;
}
    */
    /**
     * Add meta box to show linked webinar post
     */
    public function add_linked_webinar_meta_box() {
        error_log('[RTS Webinar Integration] Adding linked webinar meta box');
        add_meta_box(
            'rts_linked_webinar',
            'Linked Webinar Post',
            array($this, 'render_linked_webinar_meta_box'),
            'stm-zoom',
            'side',
            'default'
        );
    }
    
    /**
     * Render meta box content
     */
    public function render_linked_webinar_meta_box($post) {
        $post_id = $post->ID;
        error_log('[RTS Webinar Integration] Rendering meta box for meeting #' . $post_id);
        
        $webinar_id = get_post_meta($post_id, 'linked_webinar_post_id', true);
        error_log('[RTS Webinar Integration] Linked webinar ID: ' . ($webinar_id ? $webinar_id : 'none'));
        
        if (!empty($webinar_id) && get_post($webinar_id)) {
            $webinar = get_post($webinar_id);
            $edit_link = get_edit_post_link($webinar_id);
            $view_link = get_permalink($webinar_id);
            
            error_log('[RTS Webinar Integration] Found linked webinar: ' . $webinar->post_title);
            echo '<p><strong>Linked webinar:</strong> ' . esc_html($webinar->post_title) . '</p>';
            echo '<p>';
            echo '<a href="' . esc_url($edit_link) . '" class="button button-small">Edit Webinar</a> ';
            echo '<a href="' . esc_url($view_link) . '" class="button button-small" target="_blank">View Webinar</a>';
            echo '</p>';
        } else {
            error_log('[RTS Webinar Integration] No linked webinar found, showing create button');
            echo '<p>No webinar post linked to this meeting.</p>';
            
            // Add button to manually sync
            echo '<p><button type="button" id="rts-sync-webinar" class="button" data-meeting-id="' . esc_attr($post_id) . '">Create Webinar Post</button></p>';
            echo '<div id="rts-sync-result"></div>';
        }
    }
    
    /**
     * AJAX handler for manual sync
     */
    public function ajax_sync_meeting_to_webinar() {
        error_log('[RTS Webinar Integration] AJAX sync_meeting_to_webinar called');
        
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rts_webinar_nonce')) {
            error_log('[RTS Webinar Integration] Security check failed');
            wp_send_json_error('Security check failed');
        }
        
        // Get meeting ID from request
        $meeting_id = isset($_POST['meeting_id']) ? intval($_POST['meeting_id']) : 0;
        error_log('[RTS Webinar Integration] Meeting ID from request: ' . $meeting_id);
        
        if (!$meeting_id) {
            error_log('[RTS Webinar Integration] Invalid meeting ID');
            wp_send_json_error('Invalid meeting ID');
        }
        
        // Get meeting post
        $meeting_post = get_post($meeting_id);
        error_log('[RTS Webinar Integration] Meeting post exists: ' . ($meeting_post ? 'yes' : 'no'));
        
        if (!$meeting_post) {
            error_log('[RTS Webinar Integration] Meeting post not found');
            wp_send_json_error('Meeting not found');
        }
        
        error_log('[RTS Webinar Integration] Meeting post type: ' . $meeting_post->post_type);
        if ($meeting_post->post_type !== 'stm-zoom') {
            error_log('[RTS Webinar Integration] Wrong post type: ' . $meeting_post->post_type);
            wp_send_json_error('Meeting not found');
        }
        
        // Get all meta data for debugging
        $meta = get_post_meta($meeting_id);
        error_log('[RTS Webinar Integration] Meeting meta fields: ' . print_r(array_keys($meta), true));
        
        // Check if the Zoom ID exists
        // Try different meta fields to find the Zoom meeting ID
$zoom_meeting_id = '';

// First check for encoded Zoom data
$zoom_data = get_post_meta($post_id, 'stm_zoom_data', true);
if (!empty($zoom_data) && is_string($zoom_data)) {
    $decoded_data = json_decode($zoom_data, true);
    if (is_array($decoded_data) && isset($decoded_data['id'])) {
        $zoom_meeting_id = $decoded_data['id'];
        error_log('[RTS Webinar Integration] Found Zoom ID in stm_zoom_data JSON: ' . $zoom_meeting_id);
    }
}

// If still empty, try to use the post ID as a fallback
if (empty($zoom_meeting_id)) {
    $zoom_meeting_id = 'eroom_' . $post_id;
    error_log('[RTS Webinar Integration] Using post ID as Zoom ID fallback: ' . $zoom_meeting_id);
}
        error_log('[RTS Webinar Integration] Zoom meeting ID: ' . ($zoom_id ? $zoom_id : 'not found'));
        
        // Call our save function to create/update the webinar post
        error_log('[RTS Webinar Integration] Calling handle_eroom_meeting_save to create webinar');
        $this->handle_eroom_meeting_save($meeting_id, $meeting_post, false);
        
        // Get the newly created webinar ID
        $webinar_id = get_post_meta($meeting_id, 'linked_webinar_post_id', true);
        error_log('[RTS Webinar Integration] Result - Linked webinar ID: ' . ($webinar_id ? $webinar_id : 'none'));
        
        if ($webinar_id) {
            $webinar_post = get_post($webinar_id);
            if ($webinar_post) {
                error_log('[RTS Webinar Integration] Webinar creation successful: ' . $webinar_post->post_title);
                $edit_link = get_edit_post_link($webinar_id);
                wp_send_json_success(array(
                    'message' => 'Webinar post created successfully!',
                    'webinar_id' => $webinar_id,
                    'edit_link' => $edit_link
                ));
            } else {
                error_log('[RTS Webinar Integration] Webinar post not found despite having ID');
                wp_send_json_error('Webinar post created but could not be retrieved');
            }
        } else {
            error_log('[RTS Webinar Integration] Failed to create webinar post');
            wp_send_json_error('Failed to create webinar post');
        }
    }
    
    /**
     * Log messages for debugging
     */
    private function log_message($message) {
        if (WP_DEBUG === true) {
            error_log('[RTS Webinar Integration] ' . $message);
        }
    }
}

// Initialize the plugin
function rts_webinar_integration_init() {
    error_log('[RTS Webinar Integration] Initializing plugin');
    new RTS_Webinar_Integration();
}
add_action('plugins_loaded', 'rts_webinar_integration_init');

/**
 * Create needed directories and files on plugin activation
 */
function rts_webinar_integration_activate() {
    error_log('[RTS Webinar Integration] Plugin activation');
    // Create JS directory if it doesn't exist
    $js_dir = plugin_dir_path(__FILE__) . 'js';
    if (!file_exists($js_dir)) {
        error_log('[RTS Webinar Integration] Creating JS directory');
        mkdir($js_dir, 0755, true);
    }
    
    // Create admin.js file
    $js_file = $js_dir . '/admin.js';
    if (!file_exists($js_file)) {
        error_log('[RTS Webinar Integration] Creating admin.js file');
        $js_content = <<<EOT
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
EOT;
        file_put_contents($js_file, $js_content);
    }
}
register_activation_hook(__FILE__, 'rts_webinar_integration_activate');
