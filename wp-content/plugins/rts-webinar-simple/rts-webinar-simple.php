<?php
/**
 * Plugin Name: RTS Webinar Simple Integration
 * Description: Simple integration between eRoom meetings and webinar posts
 * Version: 1.0
 * Author: RTS Capital Management
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enable extra debugging
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/plugin-errors.log');
error_log('[RTS Debug] Plugin loaded at ' . date('Y-m-d H:i:s'));

// Hook into post save for eRoom meetings
add_action('save_post', 'rts_simple_save_meeting', 999, 3);

function rts_simple_save_meeting($post_id, $post, $update) {
    // Skip if not an eRoom meeting
    if (!isset($post->post_type) || $post->post_type !== 'stm-zoom') {
        return;
    }
    
    // Comprehensive debugging
    error_log('[RTS Debug] save_post triggered for post #' . $post_id);
    error_log('[RTS Debug] Post type: ' . $post->post_type);
    error_log('[RTS Debug] Post status: ' . $post->post_status);
    error_log('[RTS Debug] Post title: ' . $post->post_title);
    error_log('[RTS Debug] Is update: ' . ($update ? 'yes' : 'no'));
    
    // Debug all post meta
    $all_meta = get_post_meta($post_id);
    if (!empty($all_meta)) {
        error_log('[RTS Debug] Post meta keys: ' . implode(', ', array_keys($all_meta)));
    } else {
        error_log('[RTS Debug] No post meta found');
    }
    
    // Skip autosaves and revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        error_log('[RTS Debug] Skipping - doing autosave');
        return;
    }
    if (wp_is_post_revision($post_id)) {
        error_log('[RTS Debug] Skipping - is revision');
        return;
    }
    if ($post->post_status !== 'publish') {
        error_log('[RTS Debug] Skipping - post status is not publish: ' . $post->post_status);
        return;
    }
    
    error_log('[RTS Debug] Processing eRoom meeting #' . $post_id);
    
    // Create a unique ID for this meeting
    $meeting_id = 'eroom_' . $post_id;
    error_log('[RTS Debug] Generated meeting ID: ' . $meeting_id);
    
    // Check if webinar already exists by meeting ID
    $existing_posts = get_posts(array(
        'post_type' => 'webinar',
        'meta_key' => 'zoom_meeting_id',
        'meta_value' => $meeting_id,
        'posts_per_page' => 1
    ));
    
    if (!empty($existing_posts)) {
        $webinar_id = $existing_posts[0]->ID;
        error_log('[RTS Debug] Found existing webinar #' . $webinar_id . ' by meeting ID');
        
        // Update existing webinar
        wp_update_post(array(
            'ID' => $webinar_id,
            'post_title' => $post->post_title,
            'post_content' => $post->post_content,
            'post_status' => 'publish'
        ));
        
        error_log('[RTS Debug] Updated existing webinar #' . $webinar_id);
    } else {
        // Check if a webinar with the same title already exists (to prevent duplicates)
        $title_check = get_posts(array(
            'post_type' => 'webinar',
            's' => $post->post_title, // Using search instead of exact title match
            'posts_per_page' => 1
        ));
        
        error_log('[RTS Debug] Title search results: ' . (!empty($title_check) ? count($title_check) : '0'));
        
        if (!empty($title_check)) {
            $webinar_id = $title_check[0]->ID;
            error_log('[RTS Debug] Found existing webinar #' . $webinar_id . ' with matching title: ' . $title_check[0]->post_title);
            
            // Update the post to ensure it's current
            wp_update_post(array(
                'ID' => $webinar_id,
                'post_content' => $post->post_content,
                'post_status' => 'publish'
            ));
        } else {
            // Create new webinar
            $webinar_data = array(
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_status' => 'publish',
                'post_type' => 'webinar'
            );
            
            error_log('[RTS Debug] Creating new webinar post with data: ' . json_encode($webinar_data));
            $webinar_id = wp_insert_post($webinar_data);
            
            if (is_wp_error($webinar_id)) {
                error_log('[RTS Debug] Error creating webinar post: ' . $webinar_id->get_error_message());
                return;
            }
            
            error_log('[RTS Debug] Created new webinar #' . $webinar_id);
        }
    }
    
    // Update meta data
    error_log('[RTS Debug] Setting meta data for webinar #' . $webinar_id);
    update_post_meta($webinar_id, 'zoom_meeting_id', $meeting_id);
    update_post_meta($webinar_id, 'eroom_meeting_id', $post_id);
    update_post_meta($post_id, 'linked_webinar_post_id', $webinar_id);
    
    // Set meeting date if available
    $meeting_date = get_post_meta($post_id, 'stm_date', true);
    $meeting_time = get_post_meta($post_id, 'stm_time', true);
    
    if (!empty($meeting_date)) {
        $date_string = $meeting_date;
        if (!empty($meeting_time)) {
            $date_string .= ' ' . $meeting_time;
        }
        $formatted_date = date('Y-m-d H:i:s', strtotime($date_string));
        error_log('[RTS Debug] Setting webinar date: ' . $formatted_date . ' from date: ' . $meeting_date . ' and time: ' . $meeting_time);
        update_post_meta($webinar_id, 'webinar_date', $formatted_date);
    } else {
        $now = current_time('mysql');
        error_log('[RTS Debug] No meeting date found, using current time: ' . $now);
        update_post_meta($webinar_id, 'webinar_date', $now);
    }
    
    // Also copy any zoom meeting ID if present
    $zoom_id = get_post_meta($post_id, 'stm_zoom_meeting_id', true);
    if (!empty($zoom_id)) {
        error_log('[RTS Debug] Found Zoom meeting ID in post meta: ' . $zoom_id);
        update_post_meta($webinar_id, 'webinar_zoom_id', $zoom_id);
    }
    
    // Try to extract zoom ID from JSON data if present
    $zoom_data = get_post_meta($post_id, 'stm_zoom_data', true);
    if (!empty($zoom_data) && is_string($zoom_data)) {
        $decoded_data = json_decode($zoom_data, true);
        if (is_array($decoded_data) && isset($decoded_data['id'])) {
            $zoom_meeting_id = $decoded_data['id'];
            error_log('[RTS Debug] Found Zoom ID in stm_zoom_data JSON: ' . $zoom_meeting_id);
            update_post_meta($webinar_id, 'webinar_zoom_id', $zoom_meeting_id);
        }
    }
    
    // Set expiration date (for future use)
    $expiration_date = date('Y-m-d', strtotime('+2 months'));
    update_post_meta($webinar_id, 'webinar_expiration', $expiration_date);
    
    error_log('[RTS Debug] Successfully processed meeting #' . $post_id . ' to webinar #' . $webinar_id);
}

// Hook into webhook requests to update existing webinar posts
add_filter('rest_request_before_callbacks', 'rts_simple_webhook_filter', 9, 3);

function rts_simple_webhook_filter($response, $handler, $request) {
    // Only process requests to our webhook endpoint
    $route = $request->get_route();
    if ($route !== '/rts/v1/zapier-webhook' && $route !== '/rts/v1/zoom-webhook') {
        return $response;
    }
    
    error_log('[RTS Debug] Processing webhook request for route: ' . $route);
    
    // Get request data
    $body = $request->get_body();
    $data = json_decode($body, true);
    
    // Fallback to request params if JSON parsing failed
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        $data = $request->get_params();
        error_log('[RTS Debug] JSON parsing failed, using request params');
    }
    
    // Log a summary of the data
    $data_preview = print_r($data, true);
    error_log('[RTS Debug] Webhook data summary (truncated): ' . substr($data_preview, 0, 1000));
    
    // Extract meeting ID
    $meeting_id = '';
    if (isset($data['payload_object_id'])) {
        $meeting_id = $data['payload_object_id'];
        error_log('[RTS Debug] Found meeting ID in payload_object_id: ' . $meeting_id);
    } else if (isset($data['payload']) && isset($data['payload']['object']) && isset($data['payload']['object']['id'])) {
        $meeting_id = $data['payload']['object']['id'];
        error_log('[RTS Debug] Found meeting ID in payload.object.id: ' . $meeting_id);
    }
    
    if (empty($meeting_id)) {
        error_log('[RTS Debug] No meeting ID found in webhook data');
        return $response;
    }
    
    // Extract topic/title
    $topic = '';
    if (isset($data['payload_object_topic'])) {
        $topic = $data['payload_object_topic'];
        error_log('[RTS Debug] Found topic in payload_object_topic: ' . $topic);
    } else if (isset($data['payload']) && isset($data['payload']['object']) && isset($data['payload']['object']['topic'])) {
        $topic = $data['payload']['object']['topic'];
        error_log('[RTS Debug] Found topic in payload.object.topic: ' . $topic);
    }
    
    // Find webinar post matching this meeting ID - using multiple methods
    $webinar_id = null;
    
    // Attempt 1: Try direct match with the meeting ID
    $webinar_posts = get_posts(array(
        'post_type' => 'webinar',
        'meta_key' => 'zoom_meeting_id',
        'meta_value' => $meeting_id,
        'posts_per_page' => 1
    ));
    
    if (!empty($webinar_posts)) {
        $webinar_id = $webinar_posts[0]->ID;
        error_log('[RTS Debug] Found webinar #' . $webinar_id . ' by exact match on zoom_meeting_id');
    } else {
        // Attempt 2: Try with webinar_zoom_id
        $webinar_posts = get_posts(array(
            'post_type' => 'webinar',
            'meta_key' => 'webinar_zoom_id',
            'meta_value' => $meeting_id,
            'posts_per_page' => 1
        ));
        
        if (!empty($webinar_posts)) {
            $webinar_id = $webinar_posts[0]->ID;
            error_log('[RTS Debug] Found webinar #' . $webinar_id . ' by match on webinar_zoom_id');
        }
    }
    
    // Attempt 3: Try with eRoom prefix
    if (!$webinar_id && strpos($meeting_id, 'eroom_') !== 0) {
        $prefixed_id = 'eroom_' . $meeting_id;
        error_log('[RTS Debug] Trying with prefixed ID: ' . $prefixed_id);
        
        $webinar_posts = get_posts(array(
            'post_type' => 'webinar',
            'meta_key' => 'zoom_meeting_id',
            'meta_value' => $prefixed_id,
            'posts_per_page' => 1
        ));
        
        if (!empty($webinar_posts)) {
            $webinar_id = $webinar_posts[0]->ID;
            error_log('[RTS Debug] Found webinar #' . $webinar_id . ' by prefixed match');
        }
    }
    
    // Attempt 4: Try by meeting title if provided
    if (!$webinar_id && !empty($topic)) {
        error_log('[RTS Debug] Searching for webinar by title: ' . $topic);
        
        $webinar_posts = get_posts(array(
            'post_type' => 'webinar',
            's' => $topic,
            'posts_per_page' => 1
        ));
        
        if (!empty($webinar_posts)) {
            $webinar_id = $webinar_posts[0]->ID;
            error_log('[RTS Debug] Found webinar #' . $webinar_id . ' by title search');
        }
    }
    
    // Extract recording URL
    $recording_url = '';
    
    // Check in Zapier format
    if (isset($data['1. Share URL:']) || isset($data['1. Share URL'])) {
        $share_url_key = isset($data['1. Share URL:']) ? '1. Share URL:' : '1. Share URL';
        $recording_url = $data[$share_url_key];
        error_log('[RTS Debug] Found recording URL in field: ' . $share_url_key);
    } else if (isset($data['share_url'])) {
        $recording_url = $data['share_url'];
        error_log('[RTS Debug] Found recording URL in share_url field');
    } else if (isset($data['download_url'])) {
        $recording_url = $data['download_url'];
        error_log('[RTS Debug] Found recording URL in download_url field');
    } else if (isset($data['recording_url'])) {
        $recording_url = $data['recording_url'];
        error_log('[RTS Debug] Found recording URL in recording_url field');
    }
    
    // Try to find URL directly in array - for our test webhook
    if (empty($recording_url) && isset($data['download_url'])) {
        $recording_url = $data['download_url'];
        error_log('[RTS Debug] Found recording URL in download_url field (direct)');
    }
    
    // Try to find in recording_files array
    if (empty($recording_url) && isset($data['payload']) && isset($data['payload']['object']) && 
        isset($data['payload']['object']['recording_files']) && is_array($data['payload']['object']['recording_files'])) {
        
        foreach ($data['payload']['object']['recording_files'] as $file) {
            if (isset($file['file_type']) && $file['file_type'] === 'MP4' && isset($file['download_url'])) {
                $recording_url = $file['download_url'];
                error_log('[RTS Debug] Found recording URL in recording_files array');
                break;
            }
        }
    }
    
    // Extract password if available
    $password = '';
    $password_fields = array('Password', 'password', 'passcode', 'Passcode', 
                           'recording_password', 'webinar_password', 
                           'share_password', 'share_passcode');
    
    foreach ($password_fields as $field) {
        if (isset($data[$field]) && !empty($data[$field])) {
            $password = $data[$field];
            error_log('[RTS Debug] Found password in field: ' . $field);
            break;
        }
    }
    
    if (!empty($recording_url)) {
        // If we don't have a webinar post yet but have a topic, create one
        if (!$webinar_id && !empty($topic)) {
            error_log('[RTS Debug] Creating new webinar post for recording with topic: ' . $topic);
            
            $webinar_id = wp_insert_post(array(
                'post_title' => $topic,
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'webinar'
            ));
            
            error_log('[RTS Debug] Created new webinar #' . $webinar_id . ' for recording');
            
            // Store the meeting ID
            update_post_meta($webinar_id, 'zoom_meeting_id', $meeting_id);
        }
        
        if ($webinar_id) {
            error_log('[RTS Debug] Updating webinar #' . $webinar_id . ' with recording URL: ' . $recording_url);
            update_post_meta($webinar_id, 'webinar_recording', $recording_url);
            
            if (!empty($password)) {
                error_log('[RTS Debug] Also adding password: ' . $password);
                update_post_meta($webinar_id, 'webinar_password', $password);
            }
            
            // Set expiration date
            $expiration_date = date('Y-m-d', strtotime('+2 months'));
            update_post_meta($webinar_id, 'webinar_expiration', $expiration_date);
            
            error_log('[RTS Debug] Successfully updated webinar post with recording');
            
            // IMPORTANT CHANGE: This completely stops execution of the original webhook handler
            // by removing it from the rest API callbacks
            global $wp_filter;
            if (isset($wp_filter['rest_dispatch_request'])) {
                error_log('[RTS Debug] Removing original webhook handler to prevent duplicate posts');
                remove_all_filters('rest_dispatch_request');
            }
            
            // Completely remove other filters and actions that might create additional posts
            remove_all_actions('rest_api_init');
            if (function_exists('rts_handle_zapier_webhook')) {
                remove_all_filters('rest_request_before_callbacks');
                error_log('[RTS Debug] Removed original webhook handler functions');
            }
            
            // Disable any future save_post hooks to prevent more posts
            remove_all_actions('save_post');
            error_log('[RTS Debug] Removed all save_post actions to prevent duplicates');
            
            // Return success to prevent duplicate post creation
            return new WP_REST_Response(array(
                'status' => 'success',
                'message' => 'Updated existing webinar post with recording',
                'post_id' => $webinar_id
            ), 200);
        } else {
            error_log('[RTS Debug] No webinar post found to update with recording');
        }
    } else {
        error_log('[RTS Debug] No recording URL found in webhook data');
    }
    
    error_log('[RTS Debug] Letting original handler process the webhook');
    return $response;
}

// Debug function to log all notices about the save_post action
function rts_debug_all_save_actions($post_id) {
    error_log('[RTS Debug All] save_post action called for post #' . $post_id . ' (' . get_post_type($post_id) . ')');
}
add_action('save_post', 'rts_debug_all_save_actions', 1, 1);