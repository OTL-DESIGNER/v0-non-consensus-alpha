<?php
/**
 * Template for displaying single newsletter posts
 * Updated version that works with the Non-Consensus Alpha styling
 */
get_header();

// Get layout options
$color_scheme = get_post_meta(get_the_ID(), '_newsletter_color_scheme', true) ?: 'default';
$display_date = get_post_meta(get_the_ID(), '_newsletter_display_date', true) ?: 'yes';
$display_author = get_post_meta(get_the_ID(), '_newsletter_display_author', true) ?: 'yes';
$enable_sharing = get_post_meta(get_the_ID(), '_newsletter_enable_sharing', true) ?: 'yes';
$display_related = get_post_meta(get_the_ID(), '_newsletter_display_related', true) ?: 'yes';

// Get template settings from plugin
$template = get_post_meta(get_the_ID(), '_rts_newsletter_template', true) ?: 'default';
$header_color = get_post_meta(get_the_ID(), '_rts_template_' . $template . '_header_color', true) ?: '#333333';
$accent_color = get_post_meta(get_the_ID(), '_rts_template_' . $template . '_accent_color', true) ?: '#ff9800';
$show_sidebar = get_post_meta(get_the_ID(), '_rts_template_' . $template . '_show_sidebar', true) ?: '1';

// Apply color scheme class
$color_scheme_class = 'color-scheme-' . $color_scheme;
$template_class = 'template-' . $template;

// Let the plugin know we're using a theme template
set_query_var('using_theme_template', true);
?>

<div class="container single-newsletter-container my-4">
    <div class="row">
        <div class="<?php echo ($show_sidebar === '1') ? 'col-lg-8 mb-4' : 'col-12'; ?>">
            <?php
            while (have_posts()) :
                the_post();
                
                // Check if content is restricted for current user
                if (function_exists('pms_is_post_restricted') && pms_is_post_restricted(get_the_ID())) {
                    // User doesn't have access, show restricted template
                    get_template_part('template-parts/content', 'restricted-newsletter');
                } else {
                    // User has access, show full content
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('single-newsletter ' . $color_scheme_class . ' ' . $template_class); ?>>
                        <!-- Use newsletter-page-header to avoid conflicts with plugin header -->
                        <header class="newsletter-page-header">
                            <?php if ($display_date === 'yes') : ?>
                                <div class="newsletter-date">
                                    <?php echo get_the_date(); ?>
                                </div>
                            <?php endif; ?>
                            
                            <h1 class="newsletter-title"><?php the_title(); ?></h1>
                            
                            <?php if ($display_author === 'yes') : ?>
                                <div class="newsletter-author">
                                    <?php echo get_avatar(get_the_author_meta('ID'), 50); ?>
                                    <span class="newsletter-author-name"><?php the_author(); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php 
                            // Display categories/topics if available
                            $categories = get_the_term_list(get_the_ID(), 'topic', '', '', '');
                            if ($categories) : ?>
                                <div class="newsletter-categories">
                                    <?php echo $categories; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Add Non-Consensus badge -->
                            <div class="mt-3">
                                <span class="non-consensus-badge">Non-Consensus Alpha</span>
                            </div>
                        </header>
                        
                        <?php 
                        // Display highlights if available
                        $highlights = get_post_meta(get_the_ID(), '_newsletter_highlights', true);
                        if (!empty($highlights) && is_array($highlights)) {
                            $has_highlights = false;
                            foreach ($highlights as $highlight) {
                                if (!empty($highlight)) {
                                    $has_highlights = true;
                                    break;
                                }
                            }
                            
                            if ($has_highlights) : ?>
                                <div class="newsletter-highlights">
                                    <h3>Key Highlights</h3>
                                    <ul>
                                        <?php foreach ($highlights as $highlight) : 
                                            if (!empty($highlight)) : ?>
                                                <li><?php echo esc_html($highlight); ?></li>
                                            <?php endif; 
                                        endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif;
                        }
                        ?>
                        
                        <?php if (has_post_thumbnail() && get_post_meta(get_the_ID(), '_rts_newsletter_show_featured_image', true) !== '0') : ?>
                            <div class="newsletter-featured-image">
                                <?php the_post_thumbnail('large'); ?>
                                <?php if (function_exists('wp_get_attachment_caption') && wp_get_attachment_caption(get_post_thumbnail_id())) : ?>
                                    <div class="featured-caption">
                                        <?php echo wp_get_attachment_caption(get_post_thumbnail_id()); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="newsletter-content-wrapper">
                            <?php 
                            // Add a divider for visual structure
                            echo '<div class="nca-divider"></div>';
                            
                            // The content will be enhanced by the plugin's filter
                            the_content(); 
                            ?>
                            
                            <div class="newsletter-footer-actions">
                                <?php 
                                // Display PDF download if available (check both ACF and plugin meta)
                                $newsletter_pdf = '';
                                if (function_exists('get_field')) {
                                    $newsletter_pdf = get_field('newsletter_pdf');
                                }
                                if (empty($newsletter_pdf)) {
                                    $newsletter_pdf = get_post_meta(get_the_ID(), 'newsletter_pdf', true);
                                }
                                
                                if ($newsletter_pdf) : ?>
                                    <div class="newsletter-pdf-download">
                                        <a href="<?php echo esc_url($newsletter_pdf); ?>" target="_blank">
                                            <span class="newsletter-pdf-icon"><i class="fa fa-file-pdf"></i></span>
                                            <span>Download PDF Version</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($enable_sharing === 'yes') : ?>
                                    <div class="newsletter-sharing">
                                        <span class="newsletter-sharing-label">Share this newsletter:</span>
                                        <div class="newsletter-sharing-links">
                                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="newsletter-sharing-link" aria-label="Share on Twitter">
                                                <i class="fa fa-twitter"></i>
                                            </a>
                                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>" target="_blank" class="newsletter-sharing-link" aria-label="Share on LinkedIn">
                                                <i class="fa fa-linkedin"></i>
                                            </a>
                                            <a href="mailto:?subject=<?php echo urlencode(get_the_title()); ?>&body=<?php echo urlencode(get_the_title() . ' - ' . get_permalink()); ?>" class="newsletter-sharing-link" aria-label="Share via Email">
                                                <i class="fa fa-envelope"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                    
                    <?php if ($display_related === 'yes') : 
                        // Get topics of the current newsletter
                        $topics = wp_get_post_terms(get_the_ID(), 'topic', array('fields' => 'ids'));
                        
                        // Query related newsletters
                        if (!empty($topics)) {
                            $related_query = new WP_Query(array(
                                'post_type' => 'newsletter',
                                'posts_per_page' => 3,
                                'post__not_in' => array(get_the_ID()),
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'topic',
                                        'field' => 'id',
                                        'terms' => $topics,
                                    ),
                                ),
                            ));
                            
                            if ($related_query->have_posts()) : ?>
                                <div class="newsletter-related">
                                    <h3 class="newsletter-related-title">Related Newsletters</h3>
                                    <div class="newsletter-related-items">
                                        <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                            <div class="newsletter-related-item">
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <a href="<?php the_permalink(); ?>" class="related-image">
                                                        <?php the_post_thumbnail('thumbnail'); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <div class="newsletter-related-item-title">
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </div>
                                                <div class="newsletter-related-item-meta"><?php echo get_the_date(); ?></div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            <?php endif;
                            
                            wp_reset_postdata();
                        }
                    endif; ?>
                    
                    <div class="newsletter-subscription-section">
                        <h3 class="newsletter-subscription-title">Subscribe to Our Newsletter</h3>
                        <p class="newsletter-subscription-description">Get the latest insights and analysis delivered to your inbox.</p>
                        <form class="newsletter-subscription-form">
                            <input type="email" placeholder="Your email address" required>
                            <button type="submit">Subscribe</button>
                        </form>
                    </div>
                <?php
                }
            endwhile;
            ?>
        </div>
        
        <?php if ($show_sidebar === '1') : ?>
        <div class="col-lg-4">
               <div class="sticky-lg-top pt-lg-2" style="top:80px;">
                <div class="newsletter-sidebar">
                    <?php if (is_active_sidebar('newsletter-sidebar')) : ?>
                        <?php dynamic_sidebar('newsletter-sidebar'); ?>
                    <?php else : ?>
                        <!-- Default sidebar content if no widgets -->
                        <div class="sidebar-widget subscription-widget">
                            <div class="widget-header">
                                <h3 class="widget-title"><i class="fa fa-user-circle"></i> Your Subscription</h3>
                            </div>
                            
                            <div class="widget-content">
                                <?php 
                                // Get current user
                                $current_user = wp_get_current_user();
                                $has_subscription = false;
                                $is_admin = current_user_can('administrator');
                                
                                // Simple check for subscription
                                if (function_exists('pms_is_member')) {
                                    $has_subscription = pms_is_member($current_user->ID);
                                }
                                
                                if ($has_subscription || $is_admin) : ?>
                                    <div class="subscription-status subscription-active">
                                        <h4>Active Subscription</h4>
                                        <p>You have access to premium content.</p>
                                    </div>
                                <?php else : ?>
                                    <div class="subscription-status subscription-inactive">
                                        <h4>No Active Subscription</h4>
                                        <p>Subscribe to access premium investment insights.</p>
                                        <div class="subscription-actions mt-3">
                                            <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary btn-sm w-100">
                                                Subscribe Now
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="sidebar-widget quick-links-widget">
                            <div class="widget-header">
                                <h3 class="widget-title"><i class="fa fa-link"></i> Quick Links</h3>
                            </div>
                            
                            <div class="widget-content">
                                <ul class="quick-links-list">
                                    <li>
                                        <a href="<?php echo esc_url(get_post_type_archive_link('newsletter')); ?>">
                                            Newsletters
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo esc_url(get_post_type_archive_link('webinar')); ?>">
                                            Webinars
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo esc_url(site_url('/account/')); ?>">
                                            Account Settings
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="sidebar-widget">
                            <div class="widget-header">
                                <h3 class="widget-title"><i class="fa fa-clock"></i> Recent Newsletters</h3>
                            </div>
                            <div class="widget-content">
                                <ul class="recent-newsletters-list">
                                    <?php
                                    $recent_newsletters = new WP_Query(array(
                                        'post_type' => 'newsletter',
                                        'posts_per_page' => 5,
                                        'post__not_in' => array(get_the_ID()),
                                    ));
                                    
                                    if ($recent_newsletters->have_posts()) :
                                        while ($recent_newsletters->have_posts()) : $recent_newsletters->the_post();
                                            echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
                                        endwhile;
                                        wp_reset_postdata();
                                    else :
                                        echo '<li>No newsletters available</li>';
                                    endif;
                                    ?>
                                </ul>
                                <a href="<?php echo get_post_type_archive_link('newsletter'); ?>" class="btn btn-sm btn-outline-primary w-100 mt-3">View All Newsletters</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();