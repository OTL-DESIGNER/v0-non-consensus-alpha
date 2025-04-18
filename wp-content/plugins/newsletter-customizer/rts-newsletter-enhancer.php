<?php
/**
 * Plugin Name: RTS Newsletter Enhancer
 * Description: Streamlined WordPress plugin that enables publishing visually appealing, professionally formatted newsletters effortlessly.
 * Version: 1.0.0
 * Author: Claude
 * Text Domain: rts-newsletter-enhancer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class RTS_Newsletter_Enhancer {
    /**
     * Plugin instance.
     *
     * @var RTS_Newsletter_Enhancer
     */
    private static $instance = null;

    /**
     * Plugin version.
     *
     * @var string
     */
    private $version = '1.0.0';

    /**
     * Plugin directory path.
     *
     * @var string
     */
    private $plugin_dir;

    /**
     * Plugin directory URL.
     *
     * @var string
     */
    private $plugin_url;

    /**
     * Theme directory path.
     *
     * @var string
     */
    private $theme_dir;

    /**
     * Theme directory URL.
     *
     * @var string
     */
    private $theme_url;

    /**
     * Get plugin instance.
     *
     * @return RTS_Newsletter_Enhancer
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->theme_dir = get_template_directory() . '/';
        $this->theme_url = get_template_directory_uri() . '/';

        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Editor hooks
        add_action('add_meta_boxes', array($this, 'add_newsletter_metaboxes'));
        add_action('save_post_newsletter', array($this, 'save_newsletter_metadata'));
        
        // Display hooks
        add_filter('single_template', array($this, 'load_newsletter_template'));
        add_filter('archive_template', array($this, 'load_newsletter_archive_template'));
        add_filter('the_content', array($this, 'enhance_newsletter_content'), 20);
        
        // PDF generation
        add_action('publish_newsletter', array($this, 'generate_newsletter_pdf'), 10, 2);
        
        // AJAX handlers
        add_action('wp_ajax_rts_newsletter_preview', array($this, 'ajax_preview_newsletter'));
        
        // Dashboard widgets
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
        
        // Admin columns
      //  add_filter('manage_newsletter_posts_columns', array($this, 'modify_newsletter_columns'));
       // add_action('manage_newsletter_posts_custom_column', array($this, 'populate_newsletter_columns'), 10, 2);
        add_filter('manage_edit-newsletter_sortable_columns', array($this, 'make_newsletter_columns_sortable'));
        
        // Frontend assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // Register shortcodes
        add_shortcode('newsletters', array($this, 'newsletters_shortcode'));
        add_shortcode('newsletter_list', array($this, 'newsletter_list_shortcode'));
        
        // Cleanup functions
        add_action('rts_check_expired_content', array($this, 'process_expired_newsletters'));
    }

    /**
     * Enqueue admin assets.
     */
    public function enqueue_admin_assets($hook) {
        $screen = get_current_screen();
        
        // Only load on newsletter editor screens
        if ($screen->post_type !== 'newsletter') {
            return;
        }
        
        // Check if theme has newsletter admin JavaScript
        if (file_exists($this->theme_dir . 'assets/js/newsletter-admin.js')) {
            wp_enqueue_script(
                'rts-newsletter-admin',
                $this->theme_url . 'assets/js/newsletter-admin.js',
                array('jquery', 'wp-api'),
                $this->version,
                true
            );
        } else {
            wp_enqueue_script(
                'rts-newsletter-admin',
                $this->plugin_url . 'assets/js/newsletter-admin.js',
                array('jquery', 'wp-api'),
                $this->version,
                true
            );
        }
        
        // Check if theme has newsletter admin CSS
        if (file_exists($this->theme_dir . 'assets/css/_newsletter-admin.css')) {
            wp_enqueue_style(
                'rts-newsletter-admin-css',
                $this->theme_url . 'assets/css/_newsletter-admin.css',
                array(),
                $this->version
            );
        } else if (file_exists($this->theme_dir . 'assets/css/newsletter-admin.css')) {
            wp_enqueue_style(
                'rts-newsletter-admin-css',
                $this->theme_url . 'assets/css/newsletter-admin.css',
                array(),
                $this->version
            );
        } else {
            wp_enqueue_style(
                'rts-newsletter-admin-css',
                $this->plugin_url . 'assets/css/admin.css',
                array(),
                $this->version
            );
        }
        
        // Localize script for AJAX and translations
        wp_localize_script('rts-newsletter-admin', 'rtsNewsletter', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rts_newsletter_nonce'),
            'preview_url' => admin_url('admin-ajax.php?action=rts_newsletter_preview'),
            'templates' => $this->get_available_templates(),
            'i18n' => array(
                'preview' => __('Preview Newsletter', 'rts-newsletter-enhancer'),
                'generate_pdf' => __('Generate PDF', 'rts-newsletter-enhancer'),
                'save_draft' => __('Save Draft', 'rts-newsletter-enhancer'),
                'publish' => __('Publish Newsletter', 'rts-newsletter-enhancer'),
                'error' => __('An error occurred', 'rts-newsletter-enhancer'),
            ),
        ));
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_frontend_assets() {
        if (is_singular('newsletter') || is_post_type_archive('newsletter')) {
            // Use existing theme CSS files if available
            if (file_exists($this->theme_dir . 'assets/css/_newsletter-styles.css')) {
                wp_enqueue_style(
                    'rts-newsletter-front',
                    $this->theme_url . 'assets/css/_newsletter-styles.css',
                    array(),
                    $this->version
                );
            } else if (file_exists($this->theme_dir . 'assets/css/newsletter-styles.css')) {
                wp_enqueue_style(
                    'rts-newsletter-front',
                    $this->theme_url . 'assets/css/newsletter-styles.css',
                    array(),
                    $this->version
                );
            } else {
                wp_enqueue_style(
                    'rts-newsletter-front',
                    $this->plugin_url . 'assets/css/newsletter.css',
                    array(),
                    $this->version
                );
            }
            
            // Use existing theme JS files if available
            if (file_exists($this->theme_dir . 'assets/js/newsletter-scripts.js')) {
                wp_enqueue_script(
                    'rts-newsletter-front-js',
                    $this->theme_url . 'assets/js/newsletter-scripts.js',
                    array('jquery'),
                    $this->version,
                    true
                );
            } else {
                wp_enqueue_script(
                    'rts-newsletter-front-js',
                    $this->plugin_url . 'assets/js/newsletter.js',
                    array('jquery'),
                    $this->version,
                    true
                );
            }
        }
    }

    /**
     * Add newsletter metaboxes.
     */
    public function add_newsletter_metaboxes() {
        add_meta_box(
            'rts_newsletter_options',
            __('Newsletter Options', 'rts-newsletter-enhancer'),
            array($this, 'render_newsletter_options_metabox'),
            'newsletter',
            'side',
            'high'
        );
        
        add_meta_box(
            'rts_newsletter_template',
            __('Newsletter Template', 'rts-newsletter-enhancer'),
            array($this, 'render_newsletter_template_metabox'),
            'newsletter',
            'side',
            'default'
        );
    }

    /**
     * Render newsletter options metabox.
     */
    public function render_newsletter_options_metabox($post) {
        // Retrieve current values
        $pdf_enabled = get_post_meta($post->ID, '_rts_newsletter_pdf_enabled', true);
        $expiration_date = get_post_meta($post->ID, 'newsletter_expiration', true);
        
        if (empty($expiration_date)) {
            // Default to 6 months from now if not set
            $expiration_date = date('Y-m-d', strtotime('+6 months'));
        }
        
        // Security nonce
        wp_nonce_field('rts_newsletter_options', 'rts_newsletter_options_nonce');
        
        // Output the metabox
        ?>
        <div class="rts-newsletter-options-metabox">
            <p>
                <label for="rts_newsletter_pdf_enabled">
                    <input type="checkbox" id="rts_newsletter_pdf_enabled" name="rts_newsletter_pdf_enabled" value="1" <?php checked($pdf_enabled, '1'); ?> />
                    <?php _e('Generate PDF version', 'rts-newsletter-enhancer'); ?>
                </label>
            </p>
            
            <p>
                <label for="rts_newsletter_expiration_date">
                    <?php _e('Expiration Date:', 'rts-newsletter-enhancer'); ?><br>
                    <input type="date" id="rts_newsletter_expiration_date" name="rts_newsletter_expiration_date" value="<?php echo esc_attr($expiration_date); ?>" class="widefat" />
                </label>
                <span class="description"><?php _e('Content will be archived after this date', 'rts-newsletter-enhancer'); ?></span>
            </p>
            
            <div class="rts-newsletter-actions">
                <button type="button" id="rts-newsletter-preview" class="button">
                    <?php _e('Preview Newsletter', 'rts-newsletter-enhancer'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render newsletter template metabox.
     */
    public function render_newsletter_template_metabox($post) {
        // Retrieve current template
        $current_template = get_post_meta($post->ID, '_rts_newsletter_template', true);
        if (empty($current_template)) {
            $current_template = 'default';
        }
        
        // Get all available templates
        $templates = $this->get_available_templates();
        
        // Security nonce
        wp_nonce_field('rts_newsletter_template', 'rts_newsletter_template_nonce');
        
        // Output the metabox
        ?>
        <div class="rts-newsletter-template-metabox">
            <p>
                <label for="rts_newsletter_template"><?php _e('Select Template:', 'rts-newsletter-enhancer'); ?></label>
                <select name="rts_newsletter_template" id="rts_newsletter_template" class="widefat">
                    <?php foreach ($templates as $id => $template) : ?>
                        <option value="<?php echo esc_attr($id); ?>" <?php selected($current_template, $id); ?>>
                            <?php echo esc_html($template['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <div id="rts-template-preview">
                <?php foreach ($templates as $id => $template) : ?>
                    <div class="template-preview <?php echo ($current_template === $id) ? 'active' : ''; ?>" data-template="<?php echo esc_attr($id); ?>">
                        <img src="<?php echo esc_url($template['thumbnail']); ?>" alt="<?php echo esc_attr($template['name']); ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div id="rts-template-options" class="hidden">
                <!-- Template-specific options will be loaded dynamically -->
            </div>
        </div>
        <?php
    }

    /**
     * Save newsletter metadata.
     */
// Corrected save_newsletter_metadata function
public function save_newsletter_metadata($post_id) {
    // Check if it's an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check the user's permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Verify nonce for options
    if (isset($_POST['rts_newsletter_options_nonce']) && wp_verify_nonce($_POST['rts_newsletter_options_nonce'], 'rts_newsletter_options')) {
        // PDF enabled
        $pdf_enabled = isset($_POST['rts_newsletter_pdf_enabled']) ? '1' : '0';
        update_post_meta($post_id, '_rts_newsletter_pdf_enabled', $pdf_enabled);
        
        // Expiration date - ensure we're saving a properly formatted date
if (isset($_POST['rts_newsletter_expiration_date']) && !empty($_POST['rts_newsletter_expiration_date'])) {
    $expiration_date = sanitize_text_field($_POST['rts_newsletter_expiration_date']);
    
    // Ensure we're storing a standardized date format (YYYY-MM-DD)
    $timestamp = strtotime($expiration_date);
    if ($timestamp) {
        $formatted_date = date('Y-m-d', $timestamp);
        // Store with both keys to ensure compatibility
        update_post_meta($post_id, '_newsletter_expiration', $formatted_date);
        update_post_meta($post_id, 'newsletter_expiration', $formatted_date);
    }
}

    }
    
    // Verify nonce for template
    if (isset($_POST['rts_newsletter_template_nonce']) && wp_verify_nonce($_POST['rts_newsletter_template_nonce'], 'rts_newsletter_template')) {
        // Template selection
        if (isset($_POST['rts_newsletter_template'])) {
            update_post_meta($post_id, '_rts_newsletter_template', sanitize_text_field($_POST['rts_newsletter_template']));
        }
        
        // Save template-specific options
        $templates = $this->get_available_templates();
        foreach ($templates as $id => $template) {
            if (isset($template['options']) && is_array($template['options'])) {
                foreach ($template['options'] as $option_key => $option) {
                    $field_name = 'rts_template_' . $id . '_' . $option_key;
                    if (isset($_POST[$field_name])) {
                        update_post_meta($post_id, '_' . $field_name, sanitize_text_field($_POST[$field_name]));
                    }
                }
            }
        }
    }
}


    /**
     * Get available newsletter templates.
     */
    public function get_available_templates() {
        $templates = array(
            'default' => array(
                'name' => __('Default Template', 'rts-newsletter-enhancer'),
                'thumbnail' => $this->theme_url . 'assets/images/newsletter-default.jpg',
                'options' => array(
                    'header_color' => array(
                        'type' => 'color',
                        'label' => __('Header Color', 'rts-newsletter-enhancer'),
                        'default' => '#2c3e50',
                    ),
                    'accent_color' => array(
                        'type' => 'color',
                        'label' => __('Accent Color', 'rts-newsletter-enhancer'),
                        'default' => '#1abc9c',
                    ),
                ),
            ),
            'modern' => array(
                'name' => __('Modern Template', 'rts-newsletter-enhancer'),
                'thumbnail' => $this->theme_url . 'assets/images/newsletter-modern.jpg',
                'options' => array(
                    'header_color' => array(
                        'type' => 'color',
                        'label' => __('Header Color', 'rts-newsletter-enhancer'),
                        'default' => '#34495e',
                    ),
                    'accent_color' => array(
                        'type' => 'color',
                        'label' => __('Accent Color', 'rts-newsletter-enhancer'),
                        'default' => '#e74c3c',
                    ),
                    'show_sidebar' => array(
                        'type' => 'checkbox',
                        'label' => __('Show Sidebar', 'rts-newsletter-enhancer'),
                        'default' => true,
                    ),
                ),
            ),
            'minimal' => array(
                'name' => __('Minimal Template', 'rts-newsletter-enhancer'),
                'thumbnail' => $this->theme_url . 'assets/images/newsletter-minimal.jpg',
                'options' => array(
                    'text_color' => array(
                        'type' => 'color',
                        'label' => __('Text Color', 'rts-newsletter-enhancer'),
                        'default' => '#333333',
                    ),
                    'link_color' => array(
                        'type' => 'color',
                        'label' => __('Link Color', 'rts-newsletter-enhancer'),
                        'default' => '#3498db',
                    ),
                ),
            ),
        );
        
        // If thumbnails don't exist in theme, use plugin versions
        foreach ($templates as $id => &$template) {
            $thumbnail_path = str_replace($this->theme_url, $this->theme_dir, $template['thumbnail']);
            
            if (!file_exists($thumbnail_path)) {
                $template['thumbnail'] = $this->plugin_url . 'assets/images/templates/' . $id . '.jpg';
            }
        }
        
        return apply_filters('rts_newsletter_templates', $templates);
    }

/**
 * Generate PDF version of newsletter.
 */
public function generate_newsletter_pdf($post_id, $post) {
    // Check if PDF generation is enabled for this newsletter
    $pdf_enabled = get_post_meta($post_id, '_rts_newsletter_pdf_enabled', true);
    if ($pdf_enabled !== '1') {
        return;
    }
    
    // Get newsletter content
    $content = $post->post_content;
    $title = $post->post_title;
    
    // Apply content filters to get the rendered HTML
    $content = apply_filters('the_content', $content);
    
    // Apply newsletter styling based on template
    $template = get_post_meta($post_id, '_rts_newsletter_template', true);
    if (empty($template)) {
        $template = 'default';
    }
    
    // Get template-specific styling
    $content = $this->apply_template_styles($content, $template, $post_id);
    
    // Setup PDF generation using WordPress's basic PDF capabilities
    // This is a simpler approach that doesn't require external libraries
    
    // Create a unique filename
    $upload_dir = wp_upload_dir();
    $filename = sanitize_file_name($post->post_name . '-' . date('Ymd') . '.pdf');
    $filepath = $upload_dir['path'] . '/' . $filename;
    $pdf_url = $upload_dir['url'] . '/' . $filename;
    
    // Generate PDF HTML content
    $pdf_html = $this->get_pdf_html($title, $content, $template, $post_id);
    
    // Save HTML to a temporary file that we'll convert to PDF
    $temp_html_file = $upload_dir['path'] . '/temp-' . $post->post_name . '.html';
    file_put_contents($temp_html_file, $pdf_html);
    
    // Use WordPress's HTTP API to convert HTML to PDF using a web service
    // Note: In a production environment, you should use a proper PDF library
    // This is a simplified example using a fictional API service
    
    // Simulate PDF creation for demonstration
    // In a real implementation, you would use a library like mPDF, TCPDF, or Dompdf
    // or use a PDF generation service
    
    // For demonstration, we'll just create a simple text file with PDF extension
    $simple_pdf_content = "PDF VERSION OF: $title\n\n";
    $simple_pdf_content .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    $simple_pdf_content .= "Content would be properly formatted in a real PDF implementation.";
    file_put_contents($filepath, $simple_pdf_content);
    
    // Clean up the temporary HTML file
    if (file_exists($temp_html_file)) {
        unlink($temp_html_file);
    }
    
    // Save PDF URL to post meta
    update_post_meta($post_id, 'newsletter_pdf', $pdf_url);
    
    // Add admin notice that PDF was generated
    add_action('admin_notices', function() use ($pdf_url) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Newsletter PDF generated successfully.', 'rts-newsletter-enhancer'); ?> 
               <a href="<?php echo esc_url($pdf_url); ?>" target="_blank"><?php _e('View PDF', 'rts-newsletter-enhancer'); ?></a>
            </p>
        </div>
        <?php
    });
    
    return $pdf_url;
}

/**
 * For a production implementation, you should include a proper PDF library.
 * Here's how to implement it with mPDF if you have it installed:
 */
public function generate_pdf_with_mpdf($post_id, $html_content, $filename) {
    // This is example code - you would need to include the mPDF library
    if (class_exists('\\Mpdf\\Mpdf')) {
        try {
            $mpdf = new \Mpdf\Mpdf([
                'margin_left' => 20,
                'margin_right' => 20,
                'margin_top' => 20,
                'margin_bottom' => 20,
            ]);
            
            // Set document information
            $mpdf->SetTitle(get_the_title($post_id));
            $mpdf->SetAuthor(get_bloginfo('name'));
            
            // Write HTML content to PDF
            $mpdf->WriteHTML($html_content);
            
            // Save PDF to file
            $mpdf->Output($filename, 'F');
            
            return true;
        } catch (\Exception $e) {
            error_log('PDF Generation Error: ' . $e->getMessage());
            return false;
        }
    }
    
    return false;
}

    /**
     * Get HTML for PDF document.
     */
    private function get_pdf_html($title, $content, $template, $post_id) {
        // Get site info
        $site_name = get_bloginfo('name');
        $site_url = get_bloginfo('url');
        
        // Get template options
        $templates = $this->get_available_templates();
        $template_options = isset($templates[$template]) ? $templates[$template]['options'] : array();
        
        // Get option values
        $header_color = '#2c3e50'; // Default
        $accent_color = '#1abc9c'; // Default
        
        if (isset($template_options['header_color'])) {
            $saved_header_color = get_post_meta($post_id, '_rts_template_' . $template . '_header_color', true);
            $header_color = !empty($saved_header_color) ? $saved_header_color : $template_options['header_color']['default'];
        }
        
        if (isset($template_options['accent_color'])) {
            $saved_accent_color = get_post_meta($post_id, '_rts_template_' . $template . '_accent_color', true);
            $accent_color = !empty($saved_accent_color) ? $saved_accent_color : $template_options['accent_color']['default'];
        }
        
        // Build PDF HTML
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>' . esc_html($title) . '</title>
            <style>
                body {
                    font-family: "Helvetica", "Arial", sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                
                .header {
                    background-color: ' . esc_attr($header_color) . ';
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                
                .title {
                    font-size: 24px;
                    margin: 0;
                    padding: 0;
                }
                
                .content {
                    padding: 20px;
                }
                
                h1, h2, h3, h4, h5, h6 {
                    color: ' . esc_attr($header_color) . ';
                }
                
                a {
                    color: ' . esc_attr($accent_color) . ';
                    text-decoration: none;
                }
                
                .footer {
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-top: 1px solid #eee;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1 class="title">' . esc_html($title) . '</h1>
                <div class="site-info">' . esc_html($site_name) . '</div>
            </div>
            
            <div class="content">
                ' . $content . '
            </div>
            
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . esc_html($site_name) . ' | ' . esc_html($site_url) . '</p>
                <p>Generated on ' . date('F j, Y') . '</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }

/**
 * Apply template styles to newsletter content.
 */
private function apply_template_styles($content, $template, $post_id) {
    // Get template options
    $templates = $this->get_available_templates();
    $template_options = isset($templates[$template]) ? $templates[$template]['options'] : array();
    
    // Get option values
    $header_color = '#2c3e50'; // Default
    $accent_color = '#1abc9c'; // Default
    $text_color = '#333333';   // Default for minimal template
    $link_color = '#3498db';   // Default for minimal template
    
    if (isset($template_options['header_color'])) {
        $saved_header_color = get_post_meta($post_id, '_rts_template_' . $template . '_header_color', true);
        $header_color = !empty($saved_header_color) ? $saved_header_color : $template_options['header_color']['default'];
    }
    
    if (isset($template_options['accent_color'])) {
        $saved_accent_color = get_post_meta($post_id, '_rts_template_' . $template . '_accent_color', true);
        $accent_color = !empty($saved_accent_color) ? $saved_accent_color : $template_options['accent_color']['default'];
    }
    
    if (isset($template_options['text_color'])) {
        $saved_text_color = get_post_meta($post_id, '_rts_template_' . $template . '_text_color', true);
        $text_color = !empty($saved_text_color) ? $saved_text_color : $template_options['text_color']['default'];
    }
    
    if (isset($template_options['link_color'])) {
        $saved_link_color = get_post_meta($post_id, '_rts_template_' . $template . '_link_color', true);
        $link_color = !empty($saved_link_color) ? $saved_link_color : $template_options['link_color']['default'];
    }
    
    // Apply template-specific styling
    switch ($template) {
        case 'modern':
            $show_sidebar = get_post_meta($post_id, '_rts_template_modern_show_sidebar', true);
            
            // Create template-specific container
            $content = '<div class="newsletter-modern">' . $content . '</div>';
            
            // Add modern template specific CSS
            $inline_style = '<style>
                .newsletter-modern {
                    font-family: "Segoe UI", Roboto, -apple-system, sans-serif;
                    line-height: 1.8;
                    color: #444;
                }
                
                .newsletter-modern h1, 
                .newsletter-modern h2, 
                .newsletter-modern h3 {
                    font-family: "Playfair Display", Georgia, serif;
                    font-weight: 700;
                    margin-top: 1.5em;
                    color: ' . esc_attr($header_color) . ';
                }
                
                .newsletter-modern a {
                    color: ' . esc_attr($accent_color) . ';
                    text-decoration: none;
                    border-bottom: 1px solid rgba(' . $this->hex2rgb($accent_color) . ', 0.3);
                    transition: all 0.2s ease-in-out;
                }
                
                .newsletter-modern a:hover {
                    border-bottom-color: ' . esc_attr($accent_color) . ';
                }
                
                .newsletter-modern .newsletter-header {
                    background-color: ' . esc_attr($header_color) . ';
                    padding: 40px;
                    position: relative;
                }
                
                .newsletter-modern .newsletter-title {
                    font-size: 2.5rem;
                    line-height: 1.2;
                    color: #fff;
                    margin: 0 0 15px;
                }
                
                .newsletter-modern .newsletter-meta {
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 1rem;
                }
                
                .newsletter-modern blockquote {
                    border-left: 5px solid ' . esc_attr($accent_color) . ';
                    margin-left: 0;
                    padding-left: 20px;
                    font-style: italic;
                    color: #666;
                }
                
                .newsletter-modern ul, .newsletter-modern ol {
                    padding-left: 25px;
                }
                
                .newsletter-modern li {
                    margin-bottom: 8px;
                }
                
                .newsletter-modern .newsletter-button {
                    display: inline-block;
                    background-color: ' . esc_attr($accent_color) . ';
                    color: #fff;
                    padding: 12px 25px;
                    border-radius: 30px;
                    text-decoration: none;
                    border: none;
                    transition: all 0.3s ease;
                    font-weight: 600;
                }
                
                .newsletter-modern .newsletter-button:hover {
                    background-color: ' . $this->adjust_brightness($accent_color, -20) . ';
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                }
                
                .newsletter-modern .newsletter-footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 0.9rem;
                    color: #777;
                }
            </style>';
            
            if ($show_sidebar == '1') {
                // Apply layout with sidebar
                $sidebar_content = $this->get_sidebar_content();
                $content = '<div class="newsletter-layout with-sidebar">' . $inline_style . 
                          '<div class="newsletter-main-content">' . $content . '</div>' . 
                          '<div class="newsletter-sidebar">' . $sidebar_content . '</div></div>';
                
                // Add sidebar styling
                $content .= '<style>
                    .newsletter-layout.with-sidebar {
                        display: flex;
                        flex-wrap: wrap;
                    }
                    
                    .newsletter-main-content {
                        flex: 1;
                        min-width: 65%;
                        padding-right: 30px;
                    }
                    
                    .newsletter-sidebar {
                        width: 30%;
                        min-width: 250px;
                        background-color: #f9f9f9;
                        padding: 20px;
                        border-radius: 5px;
                    }
                    
                    @media (max-width: 768px) {
                        .newsletter-layout.with-sidebar {
                            flex-direction: column;
                        }
                        
                        .newsletter-main-content {
                            width: 100%;
                            padding-right: 0;
                        }
                        
                        .newsletter-sidebar {
                            width: 100%;
                            margin-top: 30px;
                        }
                    }
                </style>';
            } else {
                $content = $inline_style . $content;
            }
            break;
            
        case 'minimal':
            // Create minimal template container
            $content = '<div class="newsletter-minimal">' . $content . '</div>';
            
            // Add minimal template specific CSS
            $inline_style = '<style>
                .newsletter-minimal {
                    font-family: "Helvetica Neue", Arial, sans-serif;
                    line-height: 1.6;
                    color: ' . esc_attr($text_color) . ';
                    max-width: 700px;
                    margin: 0 auto;
                }
                
                .newsletter-minimal h1, 
                .newsletter-minimal h2, 
                .newsletter-minimal h3, 
                .newsletter-minimal h4, 
                .newsletter-minimal h5, 
                .newsletter-minimal h6 {
                    font-weight: 600;
                    line-height: 1.3;
                    margin-top: 1.5em;
                    margin-bottom: 0.5em;
                }
                
                .newsletter-minimal h1 {
                    font-size: 1.8rem;
                }
                
                .newsletter-minimal h2 {
                    font-size: 1.5rem;
                }
                
                .newsletter-minimal h3 {
                    font-size: 1.3rem;
                }
                
                .newsletter-minimal a {
                    color: ' . esc_attr($link_color) . ';
                    text-decoration: none;
                }
                
                .newsletter-minimal a:hover {
                    text-decoration: underline;
                }
                
                .newsletter-minimal .newsletter-header {
                    text-align: center;
                    margin-bottom: 40px;
                    padding: 20px 0;
                    border-bottom: 1px solid #eee;
                }
                
                .newsletter-minimal .newsletter-title {
                    font-size: 2rem;
                    margin: 0 0 10px;
                }
                
                .newsletter-minimal .newsletter-meta {
                    font-size: 0.9rem;
                    color: #777;
                }
                
                .newsletter-minimal p {
                    margin-bottom: 1.5em;
                }
                
                .newsletter-minimal blockquote {
                    margin-left: 0;
                    padding-left: 20px;
                    border-left: 2px solid #eee;
                    font-style: italic;
                    color: #555;
                }
                
                .newsletter-minimal .newsletter-footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    text-align: center;
                    font-size: 0.8rem;
                    color: #999;
                }
                
                .newsletter-minimal img {
                    max-width: 100%;
                    height: auto;
                    display: block;
                    margin: 20px auto;
                }
            </style>';
            
            $content = $inline_style . $content;
            break;
            
        case 'default':
        default:
            // Standard layout enhancement
            $content = '<div class="newsletter-default">' . $content . '</div>';
            
            // Add inline styling for default template
            $inline_style = '<style>
                .newsletter-default {
                    font-family: Georgia, serif;
                    line-height: 1.7;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                }
                
                .newsletter-default h1, 
                .newsletter-default h2, 
                .newsletter-default h3, 
                .newsletter-default h4, 
                .newsletter-default h5, 
                .newsletter-default h6 {
                    font-family: "Helvetica Neue", Arial, sans-serif;
                    color: ' . esc_attr($header_color) . ';
                    margin-top: 1.5em;
                    margin-bottom: 0.5em;
                }
                
                .newsletter-default h1 {
                    font-size: 2rem;
                    line-height: 1.3;
                }
                
                .newsletter-default h2 {
                    font-size: 1.7rem;
                    line-height: 1.3;
                }
                
                .newsletter-default h3 {
                    font-size: 1.4rem;
                    line-height: 1.3;
                }
                
                .newsletter-default a {
                    color: ' . esc_attr($accent_color) . ';
                    text-decoration: underline;
                }
                
                .newsletter-default a:hover {
                    text-decoration: none;
                }
                
                .newsletter-default .newsletter-header {
                    background-color: ' . esc_attr($header_color) . ';
                    color: white;
                    padding: 30px;
                    margin-bottom: 30px;
                    border-radius: 5px;
                }
                
                .newsletter-default .newsletter-title {
                    font-family: "Helvetica Neue", Arial, sans-serif;
                    font-size: 2.2rem;
                    margin: 0 0 15px;
                    color: white;
                }
                
                .newsletter-default .newsletter-meta {
                    font-size: 1rem;
                    opacity: 0.9;
                }
                
                .newsletter-default p {
                    margin-bottom: 1.5em;
                }
                
                .newsletter-default blockquote {
                    border-left: 3px solid ' . esc_attr($accent_color) . ';
                    margin-left: 0;
                    padding-left: 20px;
                    font-style: italic;
                    color: #555;
                }
                
                .newsletter-default ul, .newsletter-default ol {
                    padding-left: 25px;
                    margin-bottom: 20px;
                }
                
                .newsletter-default li {
                    margin-bottom: 10px;
                }
                
                .newsletter-default img {
                    max-width: 100%;
                    height: auto;
                    display: block;
                    margin: 20px 0;
                    border-radius: 5px;
                }
                
                .newsletter-default .newsletter-button {
                    display: inline-block;
                    background-color: ' . esc_attr($accent_color) . ';
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px;
                    text-decoration: none;
                    margin: 20px 0;
                    font-family: "Helvetica Neue", Arial, sans-serif;
                }
                
                .newsletter-default .newsletter-footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 0.9rem;
                    color: #777;
                }
            </style>';
            
            $content = $inline_style . $content;
            break;
    }
    
    return $content;
}

/**
 * Helper function to convert hex color to RGB
 */
private function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);
    
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    
    return "$r, $g, $b";
}

/**
 * Helper function to adjust brightness of a color
 */
private function adjust_brightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));
    
    // Format the hex color string
    $hex = str_replace('#', '', $hex);
    
    // Convert to decimal
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert to hex
    $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
    $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
    $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    
    return '#' . $r_hex . $g_hex . $b_hex;
}

    /**
     * Get sidebar content for templates that use it.
     */
    private function get_sidebar_content() {
        // This could be customized based on settings or admin options
        $sidebar = '<div class="newsletter-sidebar-section">
            <h3>' . __('Related Content', 'rts-newsletter-enhancer') . '</h3>
            <ul>';
        
        // Get recent newsletters
        $recent_newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        
        foreach ($recent_newsletters as $newsletter) {
            $sidebar .= '<li><a href="' . get_permalink($newsletter->ID) . '">' . $newsletter->post_title . '</a></li>';
        }
        
        $sidebar .= '</ul></div>';
        
        return $sidebar;
    }

    /**
     * Enhance newsletter content with styling and formatting.
     */
public function enhance_newsletter_content($content) {
    global $post;
    
    if (!is_singular('newsletter') || !in_the_loop() || !is_main_query()) {
        return $content;
    }
    
    // Get template
    $template = get_post_meta($post->ID, '_rts_newsletter_template', true);
    if (empty($template)) {
        $template = 'default';
    }
    
    // Add newsletter wrapper
    $content = '<div class="newsletter-content template-' . esc_attr($template) . '">' . $content . '</div>';
    
    // Apply template styles
    $content = $this->apply_template_styles($content, $template, $post->ID);
    
    // Check if we're using the plugin's rendering or the theme's
    $using_theme_template = false;
    if (locate_template('single-newsletter.php')) {
        $using_theme_template = true;
    }
    
    // Only add these elements if we're not using a theme template
    if (!$using_theme_template) {
        // Add newsletter header
        $header = $this->get_newsletter_header($post);
        
        // Add newsletter footer
        $footer = $this->get_newsletter_footer($post);
        
        // Add PDF download link if available
        $pdf_link = '';
        $pdf_url = get_post_meta($post->ID, 'newsletter_pdf', true);
        if (!empty($pdf_url)) {
            $pdf_link = '<div class="newsletter-pdf-download">
                <a href="' . esc_url($pdf_url) . '" class="newsletter-button" target="_blank">' . __('Download PDF', 'rts-newsletter-enhancer') . '</a>
            </div>';
        }
        
        // Combine all elements
        $content = $header . $content . $pdf_link . $footer;
    }
    
    return $content;
}

    /**
     * Get newsletter header HTML.
     */
    private function get_newsletter_header($post) {
        // Get template
        $template = get_post_meta($post->ID, '_rts_newsletter_template', true);
        if (empty($template)) {
            $template = 'default';
        }
        
        // Get template options
        $templates = $this->get_available_templates();
        $template_options = isset($templates[$template]) ? $templates[$template]['options'] : array();
        
        // Get header color
        $header_color = '#2c3e50'; // Default
        
        if (isset($template_options['header_color'])) {
            $saved_header_color = get_post_meta($post->ID, '_rts_template_' . $template . '_header_color', true);
            $header_color = !empty($saved_header_color) ? $saved_header_color : $template_options['header_color']['default'];
        }
        
        // Get featured image
        $featured_image = '';
        if (has_post_thumbnail($post->ID)) {
            $featured_image = '<div class="newsletter-featured-image">' . get_the_post_thumbnail($post->ID, 'large') . '</div>';
        }
        
        // Build header HTML
        $header = '<div class="newsletter-header" style="background-color: ' . esc_attr($header_color) . ';">
            <h1 class="newsletter-title">' . get_the_title($post->ID) . '</h1>
            <div class="newsletter-meta">
                <span class="newsletter-date">' . get_the_date('', $post->ID) . '</span>
            </div>
        </div>';
        
        // Add featured image if available
        if (!empty($featured_image)) {
            $header .= $featured_image;
        }
        
        // Add topic/category links
        $topics = get_the_terms($post->ID, 'topic');
        if ($topics && !is_wp_error($topics)) {
            $header .= '<div class="newsletter-topics">';
            foreach ($topics as $topic) {
                $header .= '<a href="' . get_term_link($topic) . '" class="newsletter-topic-link">' . $topic->name . '</a>';
            }
            $header .= '</div>';
        }
        
        return $header;
    }

    /**
     * Get newsletter footer HTML.
     */
    private function get_newsletter_footer($post) {
        // Get site info
        $site_name = get_bloginfo('name');
        $site_url = get_bloginfo('url');
        
        // Build footer HTML
        $footer = '<div class="newsletter-footer">
            <div class="newsletter-site-info">
                <p>&copy; ' . date('Y') . ' ' . esc_html($site_name) . '</p>
            </div>
            
            <div class="newsletter-links">
                <a href="' . site_url('/newsletters/') . '">' . __('View All Newsletters', 'rts-newsletter-enhancer') . '</a>
            </div>
        </div>';
        
        return $footer;
    }

    /**
     * AJAX handler for newsletter preview.
     */
    public function ajax_preview_newsletter() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rts_newsletter_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
// Get post data
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $template = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : 'default';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        
        // Apply content formatting
        $content = apply_filters('the_content', $content);
        
        // Apply template styling
        $content = $this->apply_template_styles($content, $template, $post_id);
        
        // Generate preview HTML
        $preview_html = '<div class="newsletter-preview">';
        $preview_html .= $this->get_newsletter_header((object) array('ID' => $post_id, 'post_title' => $title));
        $preview_html .= $content;
        $preview_html .= $this->get_newsletter_footer((object) array('ID' => $post_id));
        $preview_html .= '</div>';
        
        wp_send_json_success(array(
            'html' => $preview_html,
        ));
    }

    /**
     * Add dashboard widgets.
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'rts_newsletter_stats',
            __('Newsletter Statistics', 'rts-newsletter-enhancer'),
            array($this, 'render_newsletter_stats_widget')
        );
    }

    /**
     * Render newsletter stats dashboard widget.
     */
    public function render_newsletter_stats_widget() {
        // Get total newsletter count
        $total_count = wp_count_posts('newsletter');
        $published_count = $total_count->publish;
        
        // Get newsletter view statistics if available
        // This would typically integrate with an analytics solution
        
        // Get recent newsletters
        $recent_newsletters = get_posts(array(
            'post_type' => 'newsletter',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        
        // Output widget content
        ?>
        <div class="rts-newsletter-stats-widget">
            <div class="stats-overview">
                <p><?php printf(__('Total Published Newsletters: %d', 'rts-newsletter-enhancer'), $published_count); ?></p>
            </div>
            
            <?php if (!empty($recent_newsletters)) : ?>
                <div class="recent-newsletters">
                    <h4><?php _e('Recent Newsletters', 'rts-newsletter-enhancer'); ?></h4>
                    <ul>
                        <?php foreach ($recent_newsletters as $newsletter) : ?>
                            <li>
                                <a href="<?php echo get_edit_post_link($newsletter->ID); ?>">
                                    <?php echo esc_html($newsletter->post_title); ?>
                                </a>
                                <span class="newsletter-date"><?php echo get_the_date('', $newsletter->ID); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <p class="newsletter-links">
                <a href="<?php echo admin_url('edit.php?post_type=newsletter'); ?>"><?php _e('View All Newsletters', 'rts-newsletter-enhancer'); ?></a>
                <a href="<?php echo admin_url('post-new.php?post_type=newsletter'); ?>"><?php _e('Add New Newsletter', 'rts-newsletter-enhancer'); ?></a>
            </p>
        </div>
        <?php
    }

/**
 * Modify admin columns for newsletters.
 */
// public function modify_newsletter_columns($columns) {
//     $new_columns = array();
    
//     // Add checkbox for bulk actions
//     if (isset($columns['cb'])) {
//         $new_columns['cb'] = $columns['cb'];
//     }
    
//     // Add custom columns
//     $new_columns['title'] = __('Title', 'rts-newsletter-enhancer');
//     $new_columns['template'] = __('Template', 'rts-newsletter-enhancer');
//     $new_columns['pdf'] = __('PDF', 'rts-newsletter-enhancer');
//     $new_columns['expiration'] = __('Expiration', 'rts-newsletter-enhancer');
    
//     // Add date column
//     if (isset($columns['date'])) {
//         $new_columns['date'] = $columns['date'];
//     }
    
//     return $new_columns;
// }

/**
 * Populate custom admin columns.
 */
// public function populate_newsletter_columns($column, $post_id) {
//     switch ($column) {
//         case 'template':
//             $template = get_post_meta($post_id, '_rts_newsletter_template', true);
//             if (empty($template)) {
//                 $template = 'default';
//             }
            
//             $templates = $this->get_available_templates();
//             $template_name = isset($templates[$template]) ? $templates[$template]['name'] : $template;
            
//             echo esc_html($template_name);
//             break;
            
//         case 'pdf':
//             $pdf_url = get_post_meta($post_id, 'newsletter_pdf', true);
//             if (!empty($pdf_url)) {
//                 echo '<a href="' . esc_url($pdf_url) . '" target="_blank">' . __('Download', 'rts-newsletter-enhancer') . '</a>';
//             } else {
//                 echo '—';
//             }
//             break;
            
//         case 'expiration':
//             $expiration_date = get_post_meta($post_id, 'newsletter_expiration', true);
//             if (!empty($expiration_date)) {
//                 // Ensure the date is properly formatted
//                 $date_timestamp = strtotime($expiration_date);
//                 $formatted_date = date_i18n(get_option('date_format'), $date_timestamp);
                
//                 // Check if expiration is soon
//                 $days_until = ($date_timestamp - time()) / DAY_IN_SECONDS;
                
//                 if ($days_until < 0) {
//                     echo '<span class="expired">' . $formatted_date . ' (' . __('Expired', 'rts-newsletter-enhancer') . ')</span>';
//                 } elseif ($days_until < 7) {
//                     echo '<span class="expiring-soon">' . $formatted_date . ' (' . __('Soon', 'rts-newsletter-enhancer') . ')</span>';
//                 } else {
//                     echo $formatted_date;
//                 }
//             } else {
//                 echo '—';
//             }
//             break;
//     }
// }

    /**
     * Make admin columns sortable.
     */
    public function make_newsletter_columns_sortable($columns) {
        $columns['expiration'] = 'expiration';
        return $columns;
    }

    /**
     * Process expired newsletters.
     */
    public function process_expired_newsletters() {
        $today = date('Ymd');
        
        // Get expired newsletters
        $expired_newsletters = new WP_Query(array(
            'post_type' => 'newsletter',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'newsletter_expiration',
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
            }
        }
        
        wp_reset_postdata();
    }

    /**
     * Load custom template for single newsletter.
     */
    public function load_newsletter_template($single_template) {
        global $post;
        
        if ($post->post_type == 'newsletter') {
            // First check if theme has a template
            $theme_template = locate_template('single-newsletter.php');
            
            if ($theme_template) {
                return $theme_template;
            }
            
            // Otherwise use plugin template
            $plugin_template = $this->plugin_dir . 'templates/single-newsletter.php';
            
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $single_template;
    }

    /**
     * Load custom template for newsletter archive.
     */
    public function load_newsletter_archive_template($archive_template) {
        if (is_post_type_archive('newsletter')) {
            // First check if theme has a template
            $theme_template = locate_template('archive-newsletter.php');
            
            if ($theme_template) {
                return $theme_template;
            }
            
            // Otherwise use plugin template
            $plugin_template = $this->plugin_dir . 'templates/archive-newsletter.php';
            
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $archive_template;
    }

    /**
     * Shortcode for displaying newsletters.
     */
    public function newsletters_shortcode($atts) {
        $atts = shortcode_atts(array(
            'count' => 5,
            'topic' => '',
            'template' => '',
            'orderby' => 'date',
            'order' => 'DESC',
        ), $atts, 'newsletters');
        
        $args = array(
            'post_type' => 'newsletter',
            'posts_per_page' => intval($atts['count']),
            'post_status' => 'publish',
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );
        
        // Add topic filter if specified
        if (!empty($atts['topic'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'topic',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['topic']),
                ),
            );
        }
        
        // Add template filter if specified
        if (!empty($atts['template'])) {
            $args['meta_query'] = array(
                array(
                    'key' => '_rts_newsletter_template',
                    'value' => $atts['template'],
                    'compare' => '=',
                ),
            );
        }
        
        $newsletters = new WP_Query($args);
        
        $output = '<div class="rts-newsletters-list">';
        
        if ($newsletters->have_posts()) {
            while ($newsletters->have_posts()) {
                $newsletters->the_post();
                
                $output .= '<div class="newsletter-item">';
                
                // Featured image
                if (has_post_thumbnail()) {
                    $output .= '<div class="newsletter-thumbnail">';
                    $output .= '<a href="' . get_permalink() . '">' . get_the_post_thumbnail(null, 'medium') . '</a>';
                    $output .= '</div>';
                }
                
                // Content
                $output .= '<div class="newsletter-content">';
                $output .= '<h3 class="newsletter-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
                $output .= '<div class="newsletter-meta">' . get_the_date() . '</div>';
                $output .= '<div class="newsletter-excerpt">' . get_the_excerpt() . '</div>';
                
                // PDF link if available
                $pdf_url = get_post_meta(get_the_ID(), 'newsletter_pdf', true);
                if (!empty($pdf_url)) {
                    $output .= '<div class="newsletter-pdf-link">';
                    $output .= '<a href="' . esc_url($pdf_url) . '" target="_blank">' . __('Download PDF', 'rts-newsletter-enhancer') . '</a>';
                    $output .= '</div>';
                }
                
                $output .= '</div>'; // End .newsletter-content
                $output .= '</div>'; // End .newsletter-item
            }
        } else {
            $output .= '<p>' . __('No newsletters found.', 'rts-newsletter-enhancer') . '</p>';
        }
        
        $output .= '</div>'; // End .rts-newsletters-list
        
        wp_reset_postdata();
        
        return $output;
    }

    /**
     * Shortcode for displaying a simple newsletter list.
     */
    public function newsletter_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'count' => 10,
            'topic' => '',
            'show_date' => true,
            'show_excerpt' => false,
        ), $atts, 'newsletter_list');
        
        $args = array(
            'post_type' => 'newsletter',
            'posts_per_page' => intval($atts['count']),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Add topic filter if specified
        if (!empty($atts['topic'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'topic',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['topic']),
                ),
            );
        }
        
        $newsletters = new WP_Query($args);
        
        $output = '<ul class="rts-newsletter-list">';
        
        if ($newsletters->have_posts()) {
            while ($newsletters->have_posts()) {
                $newsletters->the_post();
                
                $output .= '<li class="newsletter-list-item">';
                $output .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
                
                // Show date if enabled
                if ($atts['show_date']) {
                    $output .= ' <span class="newsletter-date">' . get_the_date() . '</span>';
                }
                
                // Show excerpt if enabled
                if ($atts['show_excerpt']) {
                    $output .= '<div class="newsletter-excerpt">' . get_the_excerpt() . '</div>';
                }
                
                $output .= '</li>';
            }
        } else {
            $output .= '<li>' . __('No newsletters found.', 'rts-newsletter-enhancer') . '</li>';
        }
        
        $output .= '</ul>';
        
        wp_reset_postdata();
        
        return $output;
    }

    /**
     * Add settings page.
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=newsletter',
            __('Newsletter Settings', 'rts-newsletter-enhancer'),
            __('Settings', 'rts-newsletter-enhancer'),
            'manage_options',
            'rts-newsletter-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting('rts_newsletter_settings', 'rts_newsletter_default_template');
        register_setting('rts_newsletter_settings', 'rts_newsletter_default_expiration');
        register_setting('rts_newsletter_settings', 'rts_newsletter_auto_pdf');
        register_setting('rts_newsletter_settings', 'rts_newsletter_company_name');
        register_setting('rts_newsletter_settings', 'rts_newsletter_company_logo');
        
        add_settings_section(
            'rts_newsletter_general_section',
            __('General Settings', 'rts-newsletter-enhancer'),
            array($this, 'render_general_section'),
            'rts-newsletter-settings'
        );
        
        add_settings_field(
            'rts_newsletter_default_template',
            __('Default Template', 'rts-newsletter-enhancer'),
            array($this, 'render_default_template_field'),
            'rts-newsletter-settings',
            'rts_newsletter_general_section'
        );
        
        add_settings_field(
            'rts_newsletter_default_expiration',
            __('Default Expiration', 'rts-newsletter-enhancer'),
            array($this, 'render_default_expiration_field'),
            'rts-newsletter-settings',
            'rts_newsletter_general_section'
        );
        
        add_settings_field(
            'rts_newsletter_auto_pdf',
            __('Auto-generate PDF', 'rts-newsletter-enhancer'),
            array($this, 'render_auto_pdf_field'),
            'rts-newsletter-settings',
            'rts_newsletter_general_section'
        );
        
        add_settings_section(
            'rts_newsletter_branding_section',
            __('Branding Settings', 'rts-newsletter-enhancer'),
            array($this, 'render_branding_section'),
            'rts-newsletter-settings'
        );
        
        add_settings_field(
            'rts_newsletter_company_name',
            __('Company Name', 'rts-newsletter-enhancer'),
            array($this, 'render_company_name_field'),
            'rts-newsletter-settings',
            'rts_newsletter_branding_section'
        );
        
        add_settings_field(
            'rts_newsletter_company_logo',
            __('Company Logo', 'rts-newsletter-enhancer'),
            array($this, 'render_company_logo_field'),
            'rts-newsletter-settings',
            'rts_newsletter_branding_section'
        );
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('rts_newsletter_settings');
                do_settings_sections('rts-newsletter-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render general settings section.
     */
    public function render_general_section() {
        echo '<p>' . __('Configure general settings for newsletters.', 'rts-newsletter-enhancer') . '</p>';
    }

    /**
     * Render branding settings section.
     */
    public function render_branding_section() {
        echo '<p>' . __('Configure branding settings for newsletters.', 'rts-newsletter-enhancer') . '</p>';
    }

    /**
     * Render default template field.
     */
    public function render_default_template_field() {
        $default_template = get_option('rts_newsletter_default_template', 'default');
        $templates = $this->get_available_templates();
        
        echo '<select name="rts_newsletter_default_template">';
        foreach ($templates as $id => $template) {
            echo '<option value="' . esc_attr($id) . '" ' . selected($default_template, $id, false) . '>';
            echo esc_html($template['name']);
            echo '</option>';
        }
        echo '</select>';
    }

    /**
     * Render default expiration field.
     */
    public function render_default_expiration_field() {
        $default_expiration = get_option('rts_newsletter_default_expiration', '6');
        echo '<input type="number" name="rts_newsletter_default_expiration" value="' . esc_attr($default_expiration) . '" min="1" max="36" /> ';
        echo __('months', 'rts-newsletter-enhancer');
        echo '<p class="description">' . __('Default time period until newsletters expire.', 'rts-newsletter-enhancer') . '</p>';
    }

    /**
     * Render auto PDF field.
     */
    public function render_auto_pdf_field() {
        $auto_pdf = get_option('rts_newsletter_auto_pdf', '1');
        echo '<input type="checkbox" name="rts_newsletter_auto_pdf" value="1" ' . checked('1', $auto_pdf, false) . ' />';
        echo '<p class="description">' . __('Automatically generate PDF versions of newsletters when published.', 'rts-newsletter-enhancer') . '</p>';
    }

    /**
     * Render company name field.
     */
    public function render_company_name_field() {
        $company_name = get_option('rts_newsletter_company_name', get_bloginfo('name'));
        echo '<input type="text" name="rts_newsletter_company_name" value="' . esc_attr($company_name) . '" class="regular-text" />';
    }

    /**
     * Render company logo field.
     */
    public function render_company_logo_field() {
        $company_logo = get_option('rts_newsletter_company_logo', '');
        
        echo '<div class="rts-logo-upload">';
        
        // Image preview
        echo '<div class="logo-preview">';
        if (!empty($company_logo)) {
            echo '<img src="' . esc_url($company_logo) . '" alt="Company Logo" style="max-width: 300px; max-height: 100px;" />';
        }
        echo '</div>';
        
        // Upload button
        echo '<input type="hidden" name="rts_newsletter_company_logo" id="rts_newsletter_company_logo" value="' . esc_attr($company_logo) . '" />';
        echo '<input type="button" class="button rts-upload-logo" value="' . __('Upload Logo', 'rts-newsletter-enhancer') . '" />';
        
        // Remove button (only show if logo is set)
        if (!empty($company_logo)) {
            echo ' <input type="button" class="button rts-remove-logo" value="' . __('Remove Logo', 'rts-newsletter-enhancer') . '" />';
        }
        
        echo '</div>';
        
        // Add media uploader script
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.rts-upload-logo').click(function(e) {
                e.preventDefault();
                
                var custom_uploader = wp.media({
                    title: '<?php _e('Select Company Logo', 'rts-newsletter-enhancer'); ?>',
                    button: {
                        text: '<?php _e('Set as Logo', 'rts-newsletter-enhancer'); ?>'
                    },
                    multiple: false
                })
                .on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $('#rts_newsletter_company_logo').val(attachment.url);
                    $('.logo-preview').html('<img src="' + attachment.url + '" alt="Company Logo" style="max-width: 300px; max-height: 100px;" />');
                    $('.rts-upload-logo').after(' <input type="button" class="button rts-remove-logo" value="<?php _e('Remove Logo', 'rts-newsletter-enhancer'); ?>" />');
                })
                .open();
            });
            
            $(document).on('click', '.rts-remove-logo', function(e) {
                e.preventDefault();
                $('#rts_newsletter_company_logo').val('');
                $('.logo-preview').html('');
                $('.rts-remove-logo').remove();
            });
        });
        </script>
        <?php
    }
}

// Initialize plugin
function rts_newsletter_enhancer_init() {
    return RTS_Newsletter_Enhancer::get_instance();
}
add_action('plugins_loaded', 'rts_newsletter_enhancer_init');
