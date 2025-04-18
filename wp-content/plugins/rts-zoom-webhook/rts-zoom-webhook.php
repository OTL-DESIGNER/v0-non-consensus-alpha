<?php
/*
Plugin Name: RTS Zoom Webhook
Plugin URI: https://nonconsesus.com
Description: Automatically creates webinar posts from Zoom recordings
Version: 1.1
Author: RTS Capital Management
Text Domain: rts-zoom-webhook
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enable error logging for debugging
if (defined('WP_DEBUG') && WP_DEBUG === true) {
    ini_set('log_errors', 1);
    error_log('[RTS Zoom] Plugin loaded');
}

// ========================
// eRoom INTEGRATION (NEW)
// ========================

// Hook into eRoom Zoom meeting creation/update
add_action('save_post', 'rts_handle_eroom_meeting_save', 999, 2);

function rts_handle_eroom_meeting_save($post_id, $post) {
    // Skip if not an eRoom meeting
    if (!isset($post->post_type) || $post->post_type !== 'stm-zoom') {
        return;
    }
    
    // Skip autosaves and revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if ($post->post_status !== 'publish') return;
    
    error_log('[RTS Zoom] Processing eRoom meeting #' . $post_id);
    
    // Create a unique ID for this meeting
    $meeting_id = 'eroom_' . $post_id;
    
    // Check if webinar already exists
    $existing_posts = get_posts(array(
        'post_type' => 'webinar',
        'meta_key' => 'zoom_meeting_id',
        'meta_value' => $meeting_id,
        'posts_per_page' => 1
    ));
    
    // Also check for an existing post with the same title to prevent duplicates
    if (empty($existing_posts)) {
        $title_matches = get_posts(array(
            'post_type' => 'webinar',
            's' => $post->post_title,
            'posts_per_page' => 1
        ));
        
        if (!empty($title_matches)) {
            $existing_posts = $title_matches;
            error_log('[RTS Zoom] Found existing webinar with matching title: ' . $title_matches[0]->ID);
        }
    }
    
    if (!empty($existing_posts)) {
        // Update existing webinar
        $webinar_id = $existing_posts[0]->ID;
        
        wp_update_post(array(
            'ID' => $webinar_id,
            'post_title' => $post->post_title,
            'post_content' => $post->post_content,
            'post_status' => 'publish'
        ));
        
        error_log('[RTS Zoom] Updated existing webinar #' . $webinar_id);
    } else {
        // Create new webinar
        $webinar_id = wp_insert_post(array(
            'post_title' => $post->post_title,
            'post_content' => $post->post_content,
            'post_status' => 'publish',
            'post_type' => 'webinar'
        ));
        
        error_log('[RTS Zoom] Created new webinar #' . $webinar_id);
    }
    
    // Update meta data
    update_post_meta($webinar_id, 'zoom_meeting_id', $meeting_id);
    update_post_meta($webinar_id, 'eroom_meeting_id', $post_id);
    update_post_meta($post_id, 'linked_webinar_post_id', $webinar_id);


// Set meeting date if available
$meeting_date = get_post_meta($post_id, 'stm_date', true);
$meeting_time = get_post_meta($post_id, 'stm_time', true);

error_log("[RTS Zoom] Raw date from eRoom: '$meeting_date', time: '$meeting_time'");

// Ensure we have a date to work with
if (!empty($meeting_date)) {
    // Check if it's a timestamp in milliseconds (common from Zoom/eRoom)
    if (is_numeric($meeting_date) && strlen($meeting_date) > 10) {
        // Convert milliseconds to seconds for PHP timestamp
        $timestamp = intval($meeting_date) / 1000;
        
        // Create DateTime object in UTC
        $date_obj = new DateTime('@' . $timestamp);
        
        // Convert to site timezone
        $site_timezone = new DateTimeZone(wp_timezone_string());
        $date_obj->setTimezone($site_timezone);
        
        // If we have time information, use it - IMPORTANT: keep time in the same timezone
        if (!empty($meeting_time)) {
            // Extract hours and minutes from the time string
            $time_parts = explode(':', $meeting_time);
            $hours = isset($time_parts[0]) ? intval($time_parts[0]) : 0;
            $minutes = isset($time_parts[1]) ? intval($time_parts[1]) : 0;
            
            // Set the time on our DateTime object (keeping the date the same)
            $current_date = $date_obj->format('Y-m-d');
            $new_datetime = new DateTime($current_date . ' ' . sprintf('%02d:%02d:00', $hours, $minutes), $site_timezone);
            
            // Use this new datetime object
            $date_obj = $new_datetime;
            
            error_log("[RTS Zoom] Set meeting time to: " . $hours . ":" . $minutes . " in " . $site_timezone->getName());
        }
        
        // Format as MySQL datetime
        $formatted_date = $date_obj->format('Y-m-d H:i:s');
        error_log("[RTS Zoom] Final formatted date with timezone adjustment: $formatted_date");
        
        // Store this datetime in both meta fields
        update_post_meta($webinar_id, 'webinar_date', $formatted_date);
        
        // Also update the ACF field if it exists
        if (function_exists('update_field')) {
            $acf_date = $date_obj->format('Ymd');
            update_field('webinar_date', $acf_date, $webinar_id);
            error_log("[RTS Zoom] Updated ACF date field: $acf_date");
        }
        
        // Store Unix timestamp for precise calculations
        update_post_meta($webinar_id, 'webinar_timestamp', $date_obj->getTimestamp());
    } else {
        // Non-timestamp date format - handle it accordingly
        // [code for handling other date formats - similar to your existing code]
    }
} else {
    error_log("[RTS Zoom] No date from eRoom, using current time");
    $date_obj = new DateTime('now', new DateTimeZone(wp_timezone_string()));
    $formatted_date = $date_obj->format('Y-m-d H:i:s');
    update_post_meta($webinar_id, 'webinar_date', $formatted_date);
    
    if (function_exists('update_field')) {
        $acf_date = $date_obj->format('Ymd');
        update_field('webinar_date', $acf_date, $webinar_id);
    }
    
    update_post_meta($webinar_id, 'webinar_timestamp', $date_obj->getTimestamp());
}
    
    // Auto-restrict to appropriate subscription plans if needed
    $webinar_plan_id = 11;    // Webinar Access plan ID
    $bundle_plan_id = 12;     // Complete Bundle plan ID
    update_post_meta($webinar_id, 'pms-content-restrict-subscription-plan', array($webinar_plan_id, $bundle_plan_id));

    do_action('rts_handle_eroom_meeting_save_fields', $webinar_id, $post_id);    

    error_log('[RTS Zoom] Successfully processed meeting #' . $post_id . ' to webinar #' . $webinar_id);
}

// ========================
// Map Presenter ID to Names
// ========================
function rts_get_presenter_name($presenter_id) {
    // First check if this is a user ID or email
    if (is_numeric($presenter_id)) {
        $user = get_user_by('id', $presenter_id);
        if ($user) {
            return $user->display_name;
        }
    } elseif (is_email($presenter_id)) {
        return $presenter_id; // Use email if it's an email
    }
    
    // For Zoom IDs, try to map from known users
    $presenter_map = array(
        'oDWSVHdGQEWI3RcYlHSKlA' => 'RTS Capital Team', // Update with actual name
        // Add more mappings as needed
    );
    
    if (isset($presenter_map[$presenter_id])) {
        return $presenter_map[$presenter_id];
    }
    
    // If we can't map it, use a placeholder
    return 'RTS Presenter';
}
// ========================
// DEBUG FUNCTION
// ========================
// Add this debug function to your plugin
function rts_debug_webinar_time($webinar_id) {
    $eroom_id = get_post_meta($webinar_id, 'eroom_meeting_id', true);
    if (!$eroom_id) return;
    
    // Get eRoom data
    $eroom_timestamp = get_post_meta($eroom_id, 'stm_date', true);
    $eroom_time = get_post_meta($eroom_id, 'stm_time', true);
    
    // Get webinar data 
    $webinar_date = get_post_meta($webinar_id, 'webinar_date', true);
    $webinar_timestamp = get_post_meta($webinar_id, 'webinar_timestamp', true);
    $acf_date = get_field('webinar_date', $webinar_id);
    
    // Site timezone
    $site_tz = wp_timezone_string();
    
    error_log("=== TIME DEBUG COMPARISON ===");
    error_log("Site timezone: $site_tz");
    error_log("eRoom timestamp: $eroom_timestamp (" . date('Y-m-d H:i:s', intval($eroom_timestamp)/1000) . " UTC)");
    error_log("eRoom time: $eroom_time");
    error_log("Webinar date in DB: $webinar_date");
    error_log("Webinar timestamp: $webinar_timestamp (" . date('Y-m-d H:i:s', intval($webinar_timestamp)) . ")");
    error_log("ACF date format: $acf_date");
    
    // Calculate difference in hours
    $eroom_dt = new DateTime('@' . (intval($eroom_timestamp)/1000));
    
    // Use the timestamp if available, otherwise try to parse the date
    if (!empty($webinar_timestamp)) {
        $webinar_dt = new DateTime('@' . $webinar_timestamp);
    } else {
        // Try to parse based on format
        if (preg_match('/^\d{8}$/', $webinar_date)) {
            // ACF format
            $year = substr($webinar_date, 0, 4);
            $month = substr($webinar_date, 4, 2);
            $day = substr($webinar_date, 6, 2);
            $webinar_dt = new DateTime("$year-$month-$day 00:00:00");
        } else {
            // Assume it's in a format DateTime can parse
            try {
                $webinar_dt = new DateTime($webinar_date);
            } catch (Exception $e) {
                error_log("Error parsing webinar date: " . $e->getMessage());
                return;
            }
        }
    }
    
    $diff_seconds = $webinar_dt->getTimestamp() - $eroom_dt->getTimestamp();
    $diff_hours = $diff_seconds / 3600;
    
    error_log("Time difference: $diff_hours hours ($diff_seconds seconds)");
    error_log("===========================");
}

// Call this after creating/updating a webinar
add_action('rts_handle_eroom_meeting_save_fields', 'rts_debug_webinar_time', 999, 1);
// ========================
// Save webinar post data
// ========================
add_action('save_post_webinar', 'rts_preserve_webinar_meta', 10, 3);

function rts_preserve_webinar_meta($post_id, $post, $update) {
    // Skip if not an update, or if it's an autosave or revision
    if (!$update || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_revision($post_id)) {
        return;
    }
    
    // Log that we're handling a webinar update
    error_log("[RTS Zoom] Handling webinar post update for #" . $post_id);
    
    // Get existing meta values before they might be overwritten
    $existing_recording = get_post_meta($post_id, 'webinar_recording', true);
    $existing_password = get_post_meta($post_id, 'webinar_password', true);
    $existing_meeting_id = get_post_meta($post_id, 'zoom_meeting_id', true);
    
    // Preserve them only if they exist and would be lost
    if (!empty($existing_recording)) {
        update_post_meta($post_id, 'webinar_recording', $existing_recording);
        error_log("[RTS Zoom] Preserved recording URL: " . $existing_recording);
    }
    
    if (!empty($existing_password)) {
        update_post_meta($post_id, 'webinar_password', $existing_password);
    }
    
    if (!empty($existing_meeting_id)) {
        update_post_meta($post_id, 'zoom_meeting_id', $existing_meeting_id);
    }
}

// ========================
// Ensure webinar date is preserved and properly formatted
// ========================

add_action('save_post_webinar', 'rts_ensure_webinar_date_format', 20, 3);

function rts_ensure_webinar_date_format($post_id, $post, $update) {
    // Skip if not an update, or if it's an autosave or revision
    if (!$update || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_revision($post_id)) {
        return;
    }
    
    // Get the current webinar_date
    $webinar_date = get_post_meta($post_id, 'webinar_date', true);
    
    if (!empty($webinar_date)) {
        // Ensure it's in the correct MySQL datetime format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $webinar_date)) {
            // It's just a date without time, append a default time
            $webinar_date .= ' 00:00:00';
            update_post_meta($post_id, 'webinar_date', $webinar_date);
            error_log("[RTS Zoom] Fixed webinar_date format for post #$post_id: $webinar_date");
        } else if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $webinar_date)) {
            // Already in the correct format, no need to change
            error_log("[RTS Zoom] webinar_date already in correct format for post #$post_id: $webinar_date");
        } else {
            // Try to convert to the correct format
            $timestamp = strtotime($webinar_date);
            if ($timestamp !== false) {
                $formatted_date = date('Y-m-d H:i:s', $timestamp);
                update_post_meta($post_id, 'webinar_date', $formatted_date);
                error_log("[RTS Zoom] Converted webinar_date format for post #$post_id from '$webinar_date' to '$formatted_date'");
            } else {
                error_log("[RTS Zoom] WARNING: Could not parse webinar_date for post #$post_id: $webinar_date");
            }
        }
        
        // Ensure ACF field is also updated
        if (function_exists('update_field')) {
            $acf_date = get_field('webinar_date', $post_id);
            // Only update if ACF field is different or empty
            if (empty($acf_date) || $acf_date != $webinar_date) {
                // Convert to ACF date format if needed
                $date_obj = new DateTime($webinar_date);
                $acf_date = $date_obj->format('Ymd'); // ACF date format
                update_field('webinar_date', $acf_date, $post_id);
                error_log("[RTS Zoom] Updated ACF webinar_date field with date: $acf_date");
            }
        }
    }
}

// ========================
// Ensure recording and date are properly synchronized
// ========================

add_filter('pre_wp_update_post_data', 'rts_prevent_recording_loss', 10, 2);

function rts_prevent_recording_loss($data, $postarr) {
    // Only run for webinar post type
    if (!isset($postarr['post_type']) || $postarr['post_type'] !== 'webinar' || !isset($postarr['ID'])) {
        return $data;
    }
    
    $post_id = $postarr['ID'];
    
    // Get current recording URL before update
    $recording_url = get_post_meta($post_id, 'webinar_recording', true);
    
    // If we have a recording URL, store it in a transient to be restored after update
    if (!empty($recording_url)) {
        set_transient('rts_webinar_' . $post_id . '_recording', $recording_url, 60); // Store for 1 minute
        error_log("[RTS Zoom] Preserved recording URL for post #$post_id during update: $recording_url");
    }
    
    // Do the same for webinar_date
    $webinar_date = get_post_meta($post_id, 'webinar_date', true);
    if (!empty($webinar_date)) {
        set_transient('rts_webinar_' . $post_id . '_date', $webinar_date, 60);
        error_log("[RTS Zoom] Preserved webinar_date for post #$post_id during update: $webinar_date");
    }
    
    return $data;
}

// Restore recording and date after post update
add_action('wp_after_insert_post', 'rts_restore_webinar_meta', 10, 4);

function rts_restore_webinar_meta($post_id, $post, $update, $post_before) {
    // Only run for webinar post type and updates
    if (!$update || $post->post_type !== 'webinar') {
        return;
    }
    
    // Restore recording URL if it exists in transient
    $recording_url = get_transient('rts_webinar_' . $post_id . '_recording');
    if ($recording_url !== false) {
        update_post_meta($post_id, 'webinar_recording', $recording_url);
        delete_transient('rts_webinar_' . $post_id . '_recording');
        error_log("[RTS Zoom] Restored recording URL after update for post #$post_id: $recording_url");
        
        // Also update ACF field if it exists
        if (function_exists('update_field')) {
            update_field('webinar_recording', $recording_url, $post_id);
        }
    }
    
    // Restore webinar_date if it exists in transient
    $webinar_date = get_transient('rts_webinar_' . $post_id . '_date');
    if ($webinar_date !== false) {
        update_post_meta($post_id, 'webinar_date', $webinar_date);
        delete_transient('rts_webinar_' . $post_id . '_date');
        error_log("[RTS Zoom] Restored webinar_date after update for post #$post_id: $webinar_date");
        
        // Also update ACF field if it exists
        if (function_exists('update_field') && function_exists('get_field')) {
            $date_obj = new DateTime($webinar_date);
            $acf_date = $date_obj->format('Ymd'); // ACF date format
            update_field('webinar_date', $acf_date, $post_id);
        }
    }
}

// ========================
// ACF Field Integration
// ========================

// Add this function to properly map all standard meta fields to ACF fields
add_action('acf/save_post', 'rts_sync_webinar_meta_with_acf', 20);

function rts_sync_webinar_meta_with_acf($post_id) {
    // Only run for webinar post type
    if (get_post_type($post_id) !== 'webinar') {
        return;
    }
    
    // Get all the relevant meta values
    $fields_to_sync = array(
        // Standard field => ACF field
        'webinar_recording' => 'webinar_recording',
        'webinar_preview' => 'webinar_preview',
        'webinar_date' => 'webinar_date',
        'webinar_duration' => 'webinar_duration',
        'webinar_presenter' => 'webinar_presenter',
        'webinar_password' => 'webinar_password',
        'zoom_meeting_id' => 'zoom_meeting_id',
        'webinar_expiration' => 'webinar_expiration'
    );
    
    error_log('[RTS Zoom] ACF Sync - Beginning field sync for post #' . $post_id);
    
    foreach ($fields_to_sync as $meta_key => $acf_key) {
        // Get the raw meta value
        $meta_value = get_post_meta($post_id, $meta_key, true);
        
        // Skip if empty
        if (empty($meta_value)) {
            continue;
        }
        
        // Get the current ACF value
        $acf_value = get_field($acf_key, $post_id);
        
        // If ACF value is empty or different, update it
        if (empty($acf_value) || $acf_value != $meta_value) {
            // Special handling for file fields
            if ($meta_key === 'webinar_recording' || $meta_key === 'webinar_preview') {
                // Check if the ACF field is configured as a file field
                if (is_array($acf_value)) {
                    // Try to get attachment ID from URL
                    $attachment_id = attachment_url_to_postid($meta_value);
                    if ($attachment_id) {
                        update_field($acf_key, $attachment_id, $post_id);
                        error_log("[RTS Zoom] Updated ACF $acf_key field with attachment ID: $attachment_id");
                    } else {
                        // If we can't find an attachment, still update with URL
                        update_field($acf_key, $meta_value, $post_id);
                        error_log("[RTS Zoom] Could not find attachment for $meta_value, updated with URL");
                    }
                } else {
                    // It's a text/URL field
                    update_field($acf_key, $meta_value, $post_id);
                    error_log("[RTS Zoom] Updated ACF $acf_key field with URL: $meta_value");
                }
            } 
            // Special handling for date fields
            else if ($meta_key === 'webinar_date' || $meta_key === 'webinar_expiration') {
                // Convert to ACF date format if needed
                $date_obj = new DateTime($meta_value);
                $acf_date = $date_obj->format('Ymd'); // ACF date format
                update_field($acf_key, $acf_date, $post_id);
                error_log("[RTS Zoom] Updated ACF $acf_key field with date: $acf_date");
            }
            // Default handling for all other fields
            else {
                update_field($acf_key, $meta_value, $post_id);
                error_log("[RTS Zoom] Updated ACF $acf_key field with value: $meta_value");
            }
        }
    }
}

// Also sync the reverse - from ACF to standard meta
add_action('acf/save_post', 'rts_sync_acf_to_webinar_meta', 15); // Run before the other function

function rts_sync_acf_to_webinar_meta($post_id) {
    // Only run for webinar post type
    if (get_post_type($post_id) !== 'webinar') {
        return;
    }
    
    // Fields to sync from ACF to standard meta
    $fields_to_sync = array(
        // ACF field => standard meta key
        'webinar_recording' => 'webinar_recording',
        'webinar_preview' => 'webinar_preview',
        'webinar_date' => 'webinar_date',
        'webinar_duration' => 'webinar_duration',
        'webinar_presenter' => 'webinar_presenter',
        'webinar_password' => 'webinar_password',
        'zoom_meeting_id' => 'zoom_meeting_id',
        'webinar_expiration' => 'webinar_expiration'
    );
    
    error_log('[RTS Zoom] ACF to Meta - Beginning field sync for post #' . $post_id);
    
    foreach ($fields_to_sync as $acf_key => $meta_key) {
        // Get ACF value
        $acf_value = get_field($acf_key, $post_id);
        
        // Skip if not set
        if (empty($acf_value) && $acf_value !== 0) {
            continue;
        }
        
        // Handle file fields
        if ($acf_key === 'webinar_recording' || $acf_key === 'webinar_preview') {
            if (is_array($acf_value) && isset($acf_value['url'])) {
                // It's a file field, get the URL
                update_post_meta($post_id, $meta_key, $acf_value['url']);
                error_log("[RTS Zoom] Updated meta $meta_key with file URL: " . $acf_value['url']);
            } else if (is_string($acf_value)) {
                // It's already a URL
                update_post_meta($post_id, $meta_key, $acf_value);
                error_log("[RTS Zoom] Updated meta $meta_key with string value: $acf_value");
            }
        }
        // Handle date fields
else if ($acf_key === 'webinar_date' || $acf_key === 'webinar_expiration') {
    if (is_string($acf_value)) {
        // Try to parse as ACF date format (Ymd)
        if (preg_match('/^\d{8}$/', $acf_value)) {
            $year = substr($acf_value, 0, 4);
            $month = substr($acf_value, 4, 2);
            $day = substr($acf_value, 6, 2);
            
            // For webinar_date, preserve time if it exists in the current value
            if ($acf_key === 'webinar_date') {
                $current_date = get_post_meta($post_id, $meta_key, true);
                if (strlen($current_date) > 10 && strpos($current_date, ':') !== false) {
                    // Extract time part from current date
                    $time_part = substr($current_date, 11);
                    $formatted_date = "$year-$month-$day $time_part";
                } else {
                    // Use default time if no time exists
                    $formatted_date = "$year-$month-$day 00:00:00";
                }
            } else {
                // For other date fields, just use the date portion
                $formatted_date = "$year-$month-$day";
            }
            
            update_post_meta($post_id, $meta_key, $formatted_date);
            error_log("[RTS Zoom] Updated meta $meta_key with formatted date: $formatted_date");
        } else {
            // Not in ACF format, convert to MySQL format
            $timestamp = strtotime($acf_value);
            if ($timestamp !== false) {
                // For webinar_date, include time
                if ($acf_key === 'webinar_date') {
                    $formatted_date = date('Y-m-d H:i:s', $timestamp);
                } else {
                    $formatted_date = date('Y-m-d', $timestamp);
                }
                update_post_meta($post_id, $meta_key, $formatted_date);
                error_log("[RTS Zoom] Converted meta $meta_key from '$acf_value' to MySQL format: $formatted_date");
            } else {
                // If conversion fails, add time to ensure correct format
                if ($acf_key === 'webinar_date' && strpos($acf_value, ':') === false) {
                    $acf_value .= ' 00:00:00';
                }
                update_post_meta($post_id, $meta_key, $acf_value);
                error_log("[RTS Zoom] Used original value for $meta_key: $acf_value");
            }
        }
    }
}
        // All other fields
        else {
            update_post_meta($post_id, $meta_key, $acf_value);
            error_log("[RTS Zoom] Updated meta $meta_key with value: $acf_value");
        }
    }
}

// Enhance the eRoom meeting transfer to include more fields
add_filter('rts_handle_eroom_meeting_save_fields', 'rts_enhance_eroom_to_webinar_fields', 10, 2);

function rts_enhance_eroom_to_webinar_fields($webinar_id, $eroom_post_id) {
    // Get additional fields from eRoom post
    
    // Get presenter information
$presenter = get_post_meta($post_id, 'stm_zoom_host', true);
if (empty($presenter)) {
    // Try alternative field names
    $presenter = get_post_meta($post_id, 'zoom_host', true);
    if (empty($presenter)) {
        $presenter = get_post_meta($post_id, 'stm_host', true);
    }
}

// Update webinar post with presenter data
if (!empty($presenter)) {
    $presenter_name = rts_get_presenter_name($presenter);
    update_post_meta($webinar_id, 'webinar_presenter', $presenter);
    update_post_meta($webinar_id, 'webinar_presenter_name', $presenter_name);
    error_log("[RTS Zoom] Set presenter from eRoom: $presenter ($presenter_name)");
}
    
    // Try to get duration
    $duration = get_post_meta($eroom_post_id, 'stm_duration', true);
    if (empty($duration)) {
        // Try alternative field names
        $duration = get_post_meta($eroom_post_id, 'zoom_duration', true);
    }
    
    // Update webinar post with this data
    if (!empty($presenter)) {
        update_post_meta($webinar_id, 'webinar_presenter', $presenter);
        error_log("[RTS Zoom] Set presenter from eRoom: $presenter");
    }
    
    if (!empty($duration)) {
        update_post_meta($webinar_id, 'webinar_duration', $duration);
        error_log("[RTS Zoom] Set duration from eRoom: $duration");
    }
    
    return $webinar_id;
}

// Add this to your plugin - focused on preserving recording URL when preview is updated
add_action('acf/save_post', 'rts_preserve_recording_on_preview_update', 20);

function rts_preserve_recording_on_preview_update($post_id) {
    // Only run for webinar post type
    if (get_post_type($post_id) !== 'webinar') {
        return;
    }
    
    // Get the original recording URL (stored in regular meta)
    $recording_url = get_post_meta($post_id, 'webinar_recording', true);
    
    // If we don't have a recording URL stored, nothing to preserve
    if (empty($recording_url)) {
        return;
    }
    
    // Get the ACF recording field
    $acf_recording = get_field('webinar_recording', $post_id);
    
    // If ACF field is empty but we have a URL, we need to convert
    if (empty($acf_recording)) {
        error_log("[RTS Zoom] Recording URL exists but ACF field is empty - converting URL to attachment");
        
        // Check if it's a valid URL
        if (filter_var($recording_url, FILTER_VALIDATE_URL)) {
            // First try to find if this URL is already an attachment
            $attachment_id = attachment_url_to_postid($recording_url);
            
            if ($attachment_id) {
                // Update the ACF field with the attachment ID
                update_field('webinar_recording', $attachment_id, $post_id);
                error_log("[RTS Zoom] Updated ACF recording field with attachment ID: $attachment_id");
            } else {
                // For external URLs, just use the URL string directly
                update_field('webinar_recording', $recording_url, $post_id);
                error_log("[RTS Zoom] Updated ACF recording field with URL string");
            }
        }
    } 
    // If the ACF field is an attachment, make sure the URL is stored in meta
    else if (is_array($acf_recording) && !empty($acf_recording['url'])) {
        $acf_url = $acf_recording['url'];
        
        // Only update if different
        if ($acf_url !== $recording_url) {
            update_post_meta($post_id, 'webinar_recording', $acf_url);
            error_log("[RTS Zoom] Updated meta recording URL from ACF: $acf_url");
        }
    }
}

// =====================
// WEBHOOK API ENDPOINTS
// =====================

// Register the Zapier webhook endpoint
add_action('rest_api_init', function () {
    register_rest_route('rts/v1', '/zapier-webhook', array(
        'methods' => 'POST',
        'callback' => 'rts_handle_zapier_webhook',
        'permission_callback' => function() {
            // You might want to add authentication here later
            return true;
        }
    ));
    
    // Add a test endpoint for debugging
    register_rest_route('rts/v1', '/test', array(
        'methods' => 'GET',
        'callback' => function() {
            return array('status' => 'API is working!');
        },
        'permission_callback' => '__return_true'
    ));

    // Direct Zoom webhook endpoint (optional alternative to Zapier)
    register_rest_route('rts/v1', '/zoom-webhook', array(
        'methods' => 'POST',
        'callback' => 'rts_handle_zoom_webhook',
        'permission_callback' => '__return_true'
    ));
});

// Handle the webhook from Zapier
function rts_handle_zapier_webhook($request) {
    // Enhanced debugging - log everything
    error_log('======= ZAPIER WEBHOOK DEBUG START =======');
    error_log('REQUEST METHOD: ' . $_SERVER['REQUEST_METHOD']);
    error_log('REQUEST HEADERS: ' . print_r(getallheaders(), true));
    error_log('RAW REQUEST BODY: ' . file_get_contents('php://input'));
    
    // Get the raw request body and parse it as JSON
    $body = $request->get_body();
    $data = json_decode($body, true);
    
    // Fallback to request params if JSON parsing failed
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        $data = $request->get_params();
    }
    
    error_log('PROCESSED DATA: ' . print_r($data, true));
    
    // Process the webhook data
    $meeting_id = '';
    $topic = 'Untitled Webinar';
    $video_url = '';
    $password = '';
    
    // Extract meeting ID from payload_object_id (as shown in your Zapier config)
    if (isset($data['payload_object_id'])) {
        $meeting_id = $data['payload_object_id'];
        error_log("Found meeting ID: $meeting_id");
    }
    
    // Extract topic from payload_object_topic (as shown in your Zapier config)
    if (isset($data['payload_object_topic'])) {
        $topic = $data['payload_object_topic'];
        error_log("Found topic: $topic");
    }
    
    // Extract password - look for various possible field names
    $password_field_options = array(
        'Password', 'password', 'passcode', 'Passcode', 
        'recording_password', 'webinar_password', 
        'share_password', 'share_passcode'
    );
    
    foreach ($password_field_options as $field) {
        if (isset($data[$field]) && !empty($data[$field])) {
            $password = $data[$field];
            error_log("Found recording password: $password");
            break;
        }
    }
    
    // Look for Share URL (as shown in your Zapier config)
    if (isset($data['1. Share URL:']) || isset($data['1. Share URL'])) {
        // Handle potential variation in key name with or without colon
        $share_url_key = isset($data['1. Share URL:']) ? '1. Share URL:' : '1. Share URL';
        $video_url = $data[$share_url_key];
        error_log("SUCCESS: Found Share URL: $video_url");
    }
    
    // Fallback: Try other common fields for the URL
    if (empty($video_url)) {
        $possible_keys = array(
            'share_url',
            'download_url',
            '1. Share URL',
            'Share URL'
        );
        
        foreach ($possible_keys as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $video_url = $data[$key];
                error_log("SUCCESS: Found URL in field '$key': $video_url");
                break;
            }
        }
    }
    
    // =============================
    // FIND EXISTING WEBINAR (DEBUG ENHANCED)
    // =============================
    $existing_webinar_id = null;
    
    // Log all webinar posts for debugging
    $all_webinars = get_posts(array(
        'post_type' => 'webinar',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    error_log("FOUND " . count($all_webinars) . " TOTAL WEBINAR POSTS");
    foreach ($all_webinars as $webinar) {
        $webinar_zoom_id = get_post_meta($webinar->ID, 'zoom_meeting_id', true);
        error_log("WEBINAR #" . $webinar->ID . ": " . $webinar->post_title . " - Zoom ID: " . $webinar_zoom_id);
    }
    
    // First try exact match by meeting ID
    if (!empty($meeting_id)) {
        $exact_match_query = new WP_Query(array(
            'post_type' => 'webinar',
            'meta_key' => 'zoom_meeting_id',
            'meta_value' => $meeting_id,
            'posts_per_page' => 1
        ));
        error_log("EXACT MATCH QUERY FOUND: " . $exact_match_query->post_count . " POSTS");
        
        if ($exact_match_query->have_posts()) {
            $exact_match_query->the_post();
            $existing_webinar_id = get_the_ID();
            error_log("Found existing webinar #$existing_webinar_id by exact meeting ID match");
            wp_reset_postdata();
        } else {
            // Next try prefixed ID (eroom_XXX)
            $prefixed_id = 'eroom_' . $meeting_id;
            $prefixed_match_query = new WP_Query(array(
                'post_type' => 'webinar',
                'meta_key' => 'zoom_meeting_id',
                'meta_value' => $prefixed_id,
                'posts_per_page' => 1
            ));
            error_log("PREFIXED MATCH QUERY FOUND: " . $prefixed_match_query->post_count . " POSTS");
            
            if ($prefixed_match_query->have_posts()) {
                $prefixed_match_query->the_post();
                $existing_webinar_id = get_the_ID();
                error_log("Found existing webinar #$existing_webinar_id by prefixed ID match");
                wp_reset_postdata();
            }
        }
    }
    
    // If still no match, try by title
    if (!$existing_webinar_id && !empty($topic)) {
        $title_match_query = new WP_Query(array(
            'post_type' => 'webinar',
            'title' => $topic,
            'posts_per_page' => 1
        ));
        error_log("TITLE MATCH QUERY FOUND: " . $title_match_query->post_count . " POSTS");
        
        if ($title_match_query->have_posts()) {
            $title_match_query->the_post();
            $existing_webinar_id = get_the_ID();
            error_log("Found existing webinar #$existing_webinar_id by title match");
            wp_reset_postdata();
        }
    }
    
    // Try a more direct title search if still no match
    if (!$existing_webinar_id && !empty($topic)) {
        global $wpdb;
        $like_title = '%' . $wpdb->esc_like($topic) . '%';
        $title_search_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts} 
                WHERE post_type = 'webinar' 
                AND post_title LIKE %s
                LIMIT 1",
                $like_title
            )
        );
        
        error_log("DIRECT TITLE SEARCH FOUND: " . count($title_search_results) . " POSTS");
        if (!empty($title_search_results)) {
            $existing_webinar_id = $title_search_results[0]->ID;
            error_log("Found existing webinar #$existing_webinar_id by direct title search: " . $title_search_results[0]->post_title);
        }
    }
    
    // If we have a video URL, update existing or create new webinar post
    if (!empty($video_url)) {
        if ($existing_webinar_id) {
            // Update existing webinar with recording
            error_log("UPDATING EXISTING WEBINAR #$existing_webinar_id WITH RECORDING");
            update_post_meta($existing_webinar_id, 'webinar_recording', $video_url);
            update_post_meta($existing_webinar_id, 'webinar_date', current_time('mysql'));
            
            if (!empty($password)) {
                update_post_meta($existing_webinar_id, 'webinar_password', $password);
            }
            
            // Set expiration date (2 months from now)
            $expiration_date = date('Y-m-d', strtotime('+2 months'));
            update_post_meta($existing_webinar_id, 'webinar_expiration', $expiration_date);
            
            error_log("SUCCESS! Updated existing webinar post #$existing_webinar_id with recording URL: $video_url");
            
            // IMPORTANT: Return here to prevent creating a duplicate
            error_log('======= ZAPIER WEBHOOK DEBUG END =======');
            return new WP_REST_Response(array(
                'status' => 'success',
                'message' => 'Webhook received and updated existing post',
                'post_id' => $existing_webinar_id
            ), 200);
        } else {
            // No existing webinar found, create a new one
            error_log("NO EXISTING WEBINAR FOUND - CREATING NEW ONE");
            $post_id = wp_insert_post(array(
                'post_title' => $topic,
                'post_type' => 'webinar',
                'post_status' => 'publish',
                'post_content' => 'This webinar recording was automatically created from a Zoom webinar.'
            ));
            
            if (!is_wp_error($post_id)) {
                // Update post metadata
                update_post_meta($post_id, 'zoom_meeting_id', $meeting_id);
                update_post_meta($post_id, 'webinar_recording', $video_url);
                update_post_meta($post_id, 'webinar_date', current_time('mysql'));
                
                // Save the password if we found one
                if (!empty($password)) {
                    update_post_meta($post_id, 'webinar_password', $password);
                    error_log("Saved recording password: $password");
                }
                
                // Set expiration date (2 months from now)
                $expiration_date = date('Y-m-d', strtotime('+2 months'));
                update_post_meta($post_id, 'webinar_expiration', $expiration_date);
                
                error_log('SUCCESS! Created webinar post ID: ' . $post_id . ' with video URL: ' . $video_url);
            } else {
                error_log('ERROR: Failed to create webinar post: ' . $post_id->get_error_message());
            }
        }
    } else {
        error_log('ERROR: No video URL found in webhook data');
    }
    
    error_log('======= ZAPIER WEBHOOK DEBUG END =======');
    
    // Always return success to Zapier
    return new WP_REST_Response(array(
        'status' => 'success',
        'message' => 'Webhook received'
    ), 200);
}

// Handle direct webhook from Zoom (alternative to Zapier)
function rts_handle_zoom_webhook($request) {
    error_log('======= ZOOM WEBHOOK DEBUG START =======');
    error_log('REQUEST BODY: ' . $request->get_body());
    error_log('REQUEST PARAMS: ' . print_r($request->get_params(), true));
    error_log('======= ZOOM WEBHOOK DEBUG END =======');
    
    $data = json_decode($request->get_body(), true);
    
    // Zoom sends a verification challenge when setting up the webhook
    if (isset($data['event']) && $data['event'] === 'endpoint.url_validation') {
        return new WP_REST_Response(array(
            'plainToken' => $data['payload']['plainToken']
        ), 200);
    }
    
    // Process recording completed event
    if (isset($data['event']) && $data['event'] === 'recording.completed') {
        rts_process_recording($data);
        error_log('Recording completed event processed directly from Zoom');
    } else {
        error_log('Unhandled Zoom event type or missing event data');
    }
    
    return new WP_REST_Response(array('status' => 'success'), 200);
}

// Process the recording data and create a webinar post
function rts_process_recording($data) {
    // Extract recording information
    $meeting_id = isset($data['payload']['object']['id']) ? $data['payload']['object']['id'] : '';
    $topic = isset($data['payload']['object']['topic']) ? $data['payload']['object']['topic'] : 'Untitled Webinar';
    $recording_files = isset($data['payload']['object']['recording_files']) ? $data['payload']['object']['recording_files'] : array();
    
    // Log data for debugging
    error_log('Processing recording for meeting ID: ' . $meeting_id);
    error_log('Topic: ' . $topic);
    error_log('Recording files: ' . json_encode($recording_files));
    
    if (empty($meeting_id)) {
        error_log('Missing meeting ID, generating a random one');
        $meeting_id = 'auto_' . uniqid();
    }
    
    if (empty($recording_files)) {
        error_log('No recording files found in the data');
        
        // Look for MP4 file URL in the raw data
        if (isset($data['download_url']) && !empty($data['download_url'])) {
            error_log('Found direct download_url in data');
            $recording_files = array(
                array(
                    'file_type' => 'MP4',
                    'download_url' => $data['download_url']
                )
            );
        } else {
            // Try to find any URL in the data that might be a recording
            foreach ($data as $key => $value) {
                if (is_string($value) && 
                    stripos($value, 'http') === 0 && 
                    (stripos($value, 'zoom.us') !== false || stripos($value, 'recording') !== false || stripos($value, 'video') !== false)) {
                    error_log('Found potential recording URL in field: ' . $key);
                    $recording_files = array(
                        array(
                            'file_type' => 'MP4',
                            'download_url' => $value
                        )
                    );
                    break;
                }
            }
        }
    }
    
    if (empty($recording_files)) {
        error_log('Could not find any recording files or URLs in the data');
        return;
    }
    
    // Prepare recording URLs
    $recording_url = '';
    $mp4_found = false;
    
    // First pass: try to find MP4 file
    foreach ($recording_files as $file) {
        if (isset($file['file_type']) && $file['file_type'] === 'MP4' && isset($file['download_url'])) {
            $recording_url = $file['download_url'];
            $mp4_found = true;
            break;
        }
    }
    
    // Second pass: if no MP4, use any available recording
    if (!$mp4_found) {
        foreach ($recording_files as $file) {
            if (isset($file['download_url'])) {
                $recording_url = $file['download_url'];
                break;
            }
        }
    }
    
    if (empty($recording_url)) {
        error_log('No recording URL found in any of the files');
        return;
    }
    
    // ==============================
    // FIND EXISTING WEBINAR (NEW!)
    // ==============================
    
    // Try multiple ways to find existing webinar post
    $existing_webinar_id = null;
    
    // 1. Direct match
    $existing_posts = get_posts(array(
        'post_type' => 'webinar',
        'meta_key' => 'zoom_meeting_id',
        'meta_value' => $meeting_id,
        'posts_per_page' => 1
    ));
    
    if (!empty($existing_posts)) {
        $existing_webinar_id = $existing_posts[0]->ID;
        error_log("Found existing webinar #$existing_webinar_id by meeting ID");
    } else {
        // 2. eRoom prefixed match
        $existing_posts = get_posts(array(
            'post_type' => 'webinar',
            'meta_key' => 'zoom_meeting_id',
            'meta_value' => 'eroom_' . $meeting_id,
            'posts_per_page' => 1
        ));
        
        if (!empty($existing_posts)) {
            $existing_webinar_id = $existing_posts[0]->ID;
            error_log("Found existing webinar #$existing_webinar_id by prefixed ID");
        } else {
            // 3. Title match
            $title_matches = get_posts(array(
                'post_type' => 'webinar',
                's' => $topic,
                'posts_per_page' => 1
            ));
            
            if (!empty($title_matches)) {
                $existing_webinar_id = $title_matches[0]->ID;
                error_log("Found existing webinar #$existing_webinar_id by title");
            }
        }
    }
    
    // Update existing or create new webinar post
    if ($existing_webinar_id) {
        // Update existing post
        error_log("Updating existing webinar post: $existing_webinar_id");
        
        // Update the post metadata
        update_post_meta($existing_webinar_id, 'zoom_meeting_id', $meeting_id);
        update_post_meta($existing_webinar_id, 'webinar_recording', $recording_url);
        
        // Set expiration date (2 months from now)
        $expiration_date = date('Y-m-d', strtotime('+2 months'));
        update_post_meta($existing_webinar_id, 'webinar_expiration', $expiration_date);
        
        // Ensure post is public
        wp_update_post(array(
            'ID' => $existing_webinar_id,
            'post_status' => 'publish'
        ));
        
        error_log("WEBINAR POST UPDATED: Post ID $existing_webinar_id with recording URL: $recording_url");
        $post_id = $existing_webinar_id;
    } else {
        // Create new post
        $post_id = wp_insert_post(array(
            'post_title' => $topic,
            'post_type' => 'webinar',
            'post_status' => 'publish',
            'post_content' => 'This webinar recording was automatically created from a Zoom webinar.'
        ));
        error_log("Created new webinar post: $post_id");
        
        if (!$post_id || is_wp_error($post_id)) {
            error_log('Failed to create webinar post: ' . (is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error'));
            return;
        }
        
        // Update the post metadata
        update_post_meta($post_id, 'zoom_meeting_id', $meeting_id);
        update_post_meta($post_id, 'webinar_recording', $recording_url);
        update_post_meta($post_id, 'webinar_date', current_time('mysql'));
        
        // Set expiration date (2 months from now)
        $expiration_date = date('Y-m-d', strtotime('+2 months'));
        update_post_meta($post_id, 'webinar_expiration', $expiration_date);
        
        error_log("WEBINAR POST CREATED: Post ID $post_id with recording URL: $recording_url");
    }
    
    // Auto-restrict to appropriate subscription plans (adjust IDs as needed)
    $webinar_plan_id = 11;    // Webinar Access plan ID
    $bundle_plan_id = 12;     // Complete Bundle plan ID
    update_post_meta($post_id, 'pms-content-restrict-subscription-plan', array($webinar_plan_id, $bundle_plan_id));

    // Attempt to send notification to Zapier about successful processing
    $zapier_catch_url = get_option('rts_zapier_webhook_url', '');
    if (!empty($zapier_catch_url)) {
        wp_remote_post($zapier_catch_url, array(
            'body' => json_encode(array(
                'event' => 'webinar_post_created',
                'post_id' => $post_id,
                'meeting_id' => $meeting_id,
                'topic' => $topic,
                'recording_url' => $recording_url
            )),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 15
        ));
    }
}

// Register settings
add_action('admin_init', 'rts_zoom_webhook_register_settings');

function rts_zoom_webhook_register_settings() {
    register_setting('rts_zoom_webhook_options', 'rts_zapier_webhook_url');
}

// Add an admin menu item
add_action('admin_menu', 'rts_zoom_webhook_menu');

function rts_zoom_webhook_menu() {
    add_options_page(
        'RTS Zoom-Zapier Webhook',
        'RTS Zoom-Zapier Webhook',
        'manage_options',
        'rts-zoom-webhook',
        'rts_zoom_webhook_page'
    );
}

// Admin page
function rts_zoom_webhook_page() {
    // Save URL if submitted
    if (isset($_POST['rts_zapier_webhook_url'])) {
        update_option('rts_zapier_webhook_url', sanitize_text_field($_POST['rts_zapier_webhook_url']));
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
    }
    
    // Get currently saved URL
    $webhook_url = get_option('rts_zapier_webhook_url', '');
    ?>
    <div class="wrap">
        <h1>RTS Zoom-Zapier Webhook</h1>
        
        <form method="post" action="">
            <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px;">
                <h2>Zapier Webhook URL</h2>
                <p>Enter your Zapier Catch Hook URL below:</p>
                <input type="text" name="rts_zapier_webhook_url" value="<?php echo esc_attr($webhook_url); ?>" style="width: 100%;" placeholder="https://hooks.zapier.com/hooks/catch/XXXXX/XXXXX/" />
                <p><input type="submit" class="button button-primary" value="Save Settings" /></p>
                
                <?php if (empty($webhook_url)) : ?>
                    <div class="notice notice-warning" style="margin: 10px 0;">
                        <p><strong>Important:</strong> You need to configure your Zapier webhook URL above for automatic webinar creation to work.</p>
                    </div>
                <?php else : ?>
                    <div class="notice notice-info" style="margin: 10px 0;">
                        <p><strong>Current Webhook URL:</strong> <?php echo esc_html($webhook_url); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if (!empty($webhook_url)) : ?>
        <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px;">
            <h2>Test Connection</h2>
            <p>Click the button below to send a test message to Zapier:</p>
            <button id="test-zapier" class="button button-secondary">Send Test to Zapier</button>
            <div id="test-result" style="margin-top: 10px; padding: 10px; display: none;"></div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#test-zapier').on('click', function() {
                    $(this).prop('disabled', true).text('Sending...');
                    $('#test-result').hide();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'rts_test_zapier_connection',
                            nonce: '<?php echo wp_create_nonce('rts_test_zapier'); ?>'
                        },
                         success: function(response) {
                            $('#test-zapier').prop('disabled', false).text('Send Test to Zapier');
                            if (response.success) {
                                $('#test-result').html(response.data).show().addClass('notice notice-success').removeClass('notice-error');
                            } else {
                                $('#test-result').html('Error: ' + response.data).show().addClass('notice notice-error').removeClass('notice-success');
                            }
                        },
                        error: function() {
                        $('#test-zapier').prop('disabled', false).text('Send Test to Zapier');
                            $('#test-result').html('Error: Could not send test. Please check your settings.').show().addClass('notice notice-error').removeClass('notice-success');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php endif; ?>
        
        <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px;">
            <h2>WordPress REST API Endpoints</h2>
            <p>Use these URLs in your webhook configurations:</p>
            
            <h4>Option 1: For Zapier</h4>
            <p>Use this URL in your Zapier action step to send data to WordPress:</p>
            <code style="display: block; padding: 10px; background: #f5f5f5; margin: 15px 0;"><?php echo site_url('/wp-json/rts/v1/zapier-webhook'); ?></code>
            
            <h4>Option 2: Direct Zoom Webhook (Alternative)</h4>
            <p>For direct integration with Zoom (bypassing Zapier):</p>
            <code style="display: block; padding: 10px; background: #f5f5f5; margin: 15px 0;"><?php echo site_url('/wp-json/rts/v1/zoom-webhook'); ?></code>
        </div>
        
        <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px;">
            <h2>Setup Instructions</h2>
            
            <div style="margin-bottom: 30px;">
                <h3>Option 1: Using Zapier (Recommended)</h3>
                <h4>Step 1: Configure Zoom Webhook in Zapier</h4>
                <ol>
                    <li>Go to <a href="https://zapier.com" target="_blank">Zapier.com</a> and create an account if you don't have one</li>
                    <li>Create a new Zap</li>
                    <li>For the Trigger app, select "Zoom"</li>
                    <li>Choose "Recording Completed" as the trigger event</li>
                    <li>Connect your Zoom account and configure the trigger</li>
                    <li>Test the trigger to ensure it's working</li>
                </ol>
                
                <h4>Step 2: Configure Zapier to send data to WordPress</h4>
                <ol>
                    <li>Add an Action step to your Zap</li>
                    <li>Select "Webhooks by Zapier" as the Action app</li>
                    <li>Choose "POST" as the Action event</li>
                    <li>In the URL field, enter: <code><?php echo site_url('/wp-json/rts/v1/zapier-webhook'); ?></code></li>
                    <li>Set Payload Type to "JSON"</li>
                    <li>Configure the data payload:</li>
                    <pre style="background: #f5f5f5; padding: 10px; margin: 5px 0; overflow: auto;">
{
  "event": "recording.completed",
  "payload": {
    "object": {
      "id": "{{id}}",
      "topic": "{{topic}}",
      "recording_files": {{recording_files}}
    }
  }
}</pre>
                    <li>For Headers, add: <code>Content-Type: application/json</code></li>
                    <li>Test the Zap and turn it on if successful</li>
                </ol>
            </div>
            
            <div>
                <h3>Option 2: Direct Zoom Integration (Advanced)</h3>
                <ol>
                    <li>Go to <a href="https://marketplace.zoom.us" target="_blank">Zoom Marketplace</a> and log in</li>
                    <li>Create a new app of type "Webhook Only"</li>
                    <li>In "Feature" → "Event Subscriptions", add endpoint URL:
                        <code><?php echo site_url('/wp-json/rts/v1/zoom-webhook'); ?></code>
                    </li>
                    <li>Subscribe to the "recording.completed" event</li>
                    <li>Save your changes and activate the app</li>
                </ol>
            </div>
        </div>
        
        <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px;">
            <h2>Debugging</h2>
            <p>If you're having issues with the webhook integration, check the following:</p>
            <ol>
                <li>Ensure your Zapier webhook URL is correctly configured above</li>
                <li>Verify your Zoom account is properly connected to Zapier</li>
                <li>Check WordPress error logs for any issues during webhook processing</li>
                <li>Test the API with this URL: <a href="<?php echo site_url('/wp-json/rts/v1/test'); ?>" target="_blank"><?php echo site_url('/wp-json/rts/v1/test'); ?></a></li>
            </ol>
            
            <p><strong>Recent logs:</strong></p>
            <div style="background: #f5f5f5; padding: 10px; max-height: 200px; overflow: auto;">
                <?php
                $log_file = WP_CONTENT_DIR . '/debug.log';
                if (file_exists($log_file) && is_readable($log_file)) {
                    $logs = file_get_contents($log_file, false, null, -10000); // Get last 10KB of log
                    $logs = array_filter(explode("\n", $logs), function($line) {
                        return strpos($line, 'ZAPIER WEBHOOK') !== false || strpos($line, 'ZOOM WEBHOOK') !== false || strpos($line, '[RTS Zoom]') !== false;
                    });
                    
                    if (!empty($logs)) {
                        echo '<pre>' . esc_html(implode("\n", $logs)) . '</pre>';
                    } else {
                        echo '<p>No relevant log entries found.</p>';
                    }
                } else {
                    echo '<p>Debug log not accessible. Make sure WP_DEBUG and WP_DEBUG_LOG are enabled in wp-config.php.</p>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

// AJAX handler for testing Zapier connection
add_action('wp_ajax_rts_test_zapier_connection', 'rts_test_zapier_connection');

function rts_test_zapier_connection() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rts_test_zapier')) {
        wp_send_json_error('Security check failed');
    }
    
    $webhook_url = get_option('rts_zapier_webhook_url', '');
    
    if (empty($webhook_url)) {
        wp_send_json_error('Webhook URL not configured');
    }
    
    // Send test data to Zapier
    $response = wp_remote_post($webhook_url, array(
        'body' => json_encode(array(
            'test' => true,
            'message' => 'This is a test from RTS Zoom Webhook plugin',
            'timestamp' => current_time('mysql')
        )),
        'headers' => array('Content-Type' => 'application/json'),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    } else {
        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 200 && $code < 300) {
            wp_send_json_success('Test sent successfully! Check your Zapier account to see if it was received.');
        } else {
            wp_send_json_error('Received unexpected response code: ' . $code);
        }
    }
}

// Fix webinar dates that are in incorrect format
add_action('init', 'rts_fix_webinar_dates', 999);

function rts_fix_webinar_dates() {
    // Only run this once per hour to avoid performance issues
    if (get_transient('rts_fixed_webinar_dates')) {
        return;
    }
    
    // Only run on admin pages or when specifically requested
    if (!is_admin() && !isset($_GET['fix_webinar_dates'])) {
        return;
    }
    
    global $wpdb;
    
    // Get all webinar_date meta values that are not in MySQL datetime format
    $results = $wpdb->get_results(
        "SELECT post_id, meta_value FROM {$wpdb->postmeta} 
        WHERE meta_key = 'webinar_date' 
        AND meta_value NOT LIKE '____-__-__ __:__:__'"
    );
    
    if (!empty($results)) {
        error_log("[RTS Zoom] Found " . count($results) . " webinar dates in incorrect format");
        
        foreach ($results as $row) {
            $post_id = $row->post_id;
            $current_date = $row->meta_value;
            
            // Convert to proper MySQL format
            $timestamp = strtotime($current_date);
            if ($timestamp !== false) {
                $formatted_date = date('Y-m-d H:i:s', $timestamp);
                update_post_meta($post_id, 'webinar_date', $formatted_date);
                error_log("[RTS Zoom] Fixed webinar_date format for post #$post_id: from '$current_date' to '$formatted_date'");
            }
        }
    }
    
    // Set a transient to prevent running this too often
    set_transient('rts_fixed_webinar_dates', true, HOUR_IN_SECONDS);
}
    // ==============================
     // Add this function to fix all webinar posts
    // ==============================

function rts_fix_all_webinar_dates() {
    // Only run for admin users who explicitly request it
    if (!current_user_can('manage_options') || !isset($_GET['fix_all_webinars'])) {
        return;
    }
    
    $webinars = get_posts(array(
        'post_type' => 'webinar',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    $fixed_count = 0;
    foreach ($webinars as $webinar) {
        $webinar_id = $webinar->ID;
        
        // CRITICAL: First save ALL eRoom-related fields to preserve them
        $eroom_meeting_id = get_post_meta($webinar_id, 'eroom_meeting_id', true);
        
        // Save all possible eRoom related fields
        $eroom_fields = array(
            'eroom_meeting_id',
            'stm_date',
            'stm_time',
            'stm_zoom_id',
            'stm_zoom_host',
            'stm_zoom_password',
            'zoom_meeting_id',
            'zoom_password',
            'zoom_join_url',
            'zoom_start_url',
            'zoom_host_key'
        );
        
        $preserved_values = array();
        foreach ($eroom_fields as $field) {
            $value = get_post_meta($webinar_id, $field, true);
            if (!empty($value)) {
                $preserved_values[$field] = $value;
            }
        }
        
        // Now process the date
        $webinar_date = get_post_meta($webinar_id, 'webinar_date', true);
        
        if (!empty($webinar_date)) {
            $timestamp = 0;
            
            // Try to parse the date based on format
            if (preg_match('/^\d{8}$/', $webinar_date)) {
                // ACF format (YYYYMMDD)
                $year = substr($webinar_date, 0, 4);
                $month = substr($webinar_date, 4, 2);
                $day = substr($webinar_date, 6, 2);
                $timestamp = strtotime("$year-$month-$day 00:00:00");
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $webinar_date)) {
                // MySQL datetime format
                $timestamp = strtotime($webinar_date);
            } else {
                // Try generic parsing
                $timestamp = strtotime($webinar_date);
            }
            
            if ($timestamp) {
                // Store the timestamp without disturbing other fields
                update_post_meta($webinar_id, 'webinar_timestamp', $timestamp);
                
                // CRITICAL: Now restore all eRoom fields
                foreach ($preserved_values as $field => $value) {
                    update_post_meta($webinar_id, $field, $value);
                }
                
                $fixed_count++;
            }
        }
    }
    
    // Add a notice about the number of fixed posts
    add_action('admin_notices', function() use ($fixed_count) {
        echo '<div class="notice notice-success"><p>Fixed timestamps for ' . $fixed_count . ' webinar posts. All eRoom integration data has been preserved.</p></div>';
    });
}
add_action('admin_init', 'rts_fix_all_webinar_dates');