<?php
/**
 * Register custom post types
 */
function rts_register_post_types() {
    // Newsletter Post Type
    register_post_type('newsletter', array(
        'labels' => array(
            'name' => 'Newsletters',
            'singular_name' => 'Newsletter',
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-media-document',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite' => array('slug' => 'newsletters'),
    ));
    
    // Webinar Post Type
    register_post_type('webinar', array(
        'labels' => array(
            'name' => 'Webinars',
            'singular_name' => 'Webinar',
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-video-alt2',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite' => array('slug' => 'webinars'),
    ));
}
add_action('init', 'rts_register_post_types');

/**
 * Register custom taxonomies
 */
function rts_register_taxonomies() {
    // Topics for organizing content
    register_taxonomy('topic', array('newsletter', 'webinar'), array(
        'labels' => array(
            'name' => 'Topics',
            'singular_name' => 'Topic',
        ),
        'hierarchical' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'topic'),
    ));
}
add_action('init', 'rts_register_taxonomies');

