<?php
/**
 * Template part for displaying restricted webinar content
 */

// Get current post data
$post_id = get_the_ID();
$title = get_the_title();
$excerpt = get_the_excerpt();
$date = get_the_date();
$featured_image = get_the_post_thumbnail_url($post_id, 'large') ?: get_template_directory_uri() . '/assets/images/default-webinar.jpg';
$categories = get_the_term_list($post_id, 'topic', '', ', ', '');

// Get webinar custom fields
$webinar_date = get_field('webinar_date', $post_id);
$webinar_presenter = get_field('webinar_presenter', $post_id) ?: '';
$webinar_duration = get_field('webinar_duration', $post_id) ?: '60 minutes';
$webinar_preview = get_field('webinar_preview', $post_id); // Add this custom field for preview video

// Format date for display if it exists
$display_date = '';
if ($webinar_date) {
    $display_date = date('F j, Y \a\t g:i a', strtotime($webinar_date));
}

// Get main webinar topics/highlights
$webinar_highlights = get_field('webinar_highlights', $post_id);
if (!$webinar_highlights) {
    // If no highlights are explicitly set, extract from content
    $content = get_the_content();
    $content_without_tags = strip_tags($content);
    $content_sentences = preg_split('/(?<=[.!?])\s+/', $content_without_tags, -1, PREG_SPLIT_NO_EMPTY);
    $webinar_highlights = array_slice($content_sentences, 0, 3);
}

// Get subscription plan details
// These can be populated dynamically based on your membership plugin
$webinar_price = '49';
$webinar_id = 'webinar-access';
$complete_price = '69';
$complete_id = 'complete-access';

// Registration URL (modify based on your membership plugin)
$register_url = site_url('/membership/');
?>

<article id="post-<?php echo $post_id; ?>" <?php post_class('restricted-webinar'); ?>>
    <div class="webinar-restricted-container">
        <!-- Main content preview section -->
        <div class="webinar-preview-section">
            <div class="webinar-header">
                <h1 class="webinar-title"><?php echo $title; ?></h1>
                
                <div class="webinar-meta">
                    <?php if ($display_date) : ?>
                        <div class="meta-item webinar-date">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?php echo $display_date; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($webinar_duration) : ?>
                        <div class="meta-item webinar-duration">
                            <i class="fas fa-clock"></i>
                            <span><?php echo $webinar_duration; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($webinar_presenter) : ?>
                        <div class="meta-item webinar-presenter">
                            <i class="fas fa-user"></i>
                            <span>Presented by: <?php echo $webinar_presenter; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($categories) : ?>
                    <div class="webinar-topics">
                        <i class="fas fa-tags"></i>
                        <span>Topics: <?php echo strip_tags($categories); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="webinar-content-preview">
                <div class="webinar-image">
                    <img src="<?php echo $featured_image; ?>" alt="<?php echo $title; ?>">
                    
                    <?php if ($webinar_preview) : ?>
                    <div class="video-preview-overlay">
                        <a href="#preview-modal" class="preview-button" data-toggle="modal">
                            <i class="fas fa-play-circle"></i>
                            <span>Watch 2-minute preview</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="webinar-excerpt">
                    <?php 
                    // Display first paragraph of content
                    $content = get_the_content();
                    $first_paragraph = substr($content, 0, strpos($content, '</p>') + 4);
                    echo $first_paragraph;
                    ?>
                    
                    <div class="content-blur">
                        <div class="blur-overlay"></div>
                        <div class="lock-message">
                            <i class="fas fa-lock"></i>
                            <h3>This webinar is exclusively for our subscribers</h3>
                            <p>Gain access to this webinar and our entire archive of expert insights with a subscription.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Value proposition section -->
        <div class="webinar-value-section">
            <div class="what-you-learn">
                <h2>What You'll Learn</h2>
                
                <ul class="learning-points">
                    <?php if (is_array($webinar_highlights)) : ?>
                        <?php foreach ($webinar_highlights as $point) : ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo $point; ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="social-proof">
                <h3>What Others Are Saying</h3>
                
                <div class="testimonial">
                    <blockquote>"The RTS Capital webinars provide actionable insights I can't find anywhere else. The presenters are top-notch and the Q&A segments are invaluable."</blockquote>
                    <cite>— Sarah M., Portfolio Manager</cite>
                </div>
                
                <p class="subscriber-count"><strong>Join 800+ professionals</strong> attending our exclusive webinars</p>
            </div>
        </div>
        
        <!-- Subscription options section -->
        <div class="subscription-options">
            <h2>Subscribe to Access This Webinar</h2>
            
            <div class="plan-cards">
                <div class="plan-card">
                    <h3>Webinar Access</h3>
                    <div class="plan-price">$<?php echo $webinar_price; ?><span>/month</span></div>
                    
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Access to all live webinars</li>
                        <li><i class="fas fa-check"></i> Complete webinar archive</li>
                        <li><i class="fas fa-check"></i> Downloadable presentation slides</li>
                        <li><i class="fas fa-check"></i> Priority Q&A submission</li>
                    </ul>
                    
                    <a href="<?php echo add_query_arg('plan', $webinar_id, $register_url); ?>" class="subscribe-button">Subscribe Now</a>
                </div>
                
                <div class="plan-card featured-plan">
                    <span class="best-value">Best Value</span>
                    <h3>Complete Access</h3>
                    <div class="plan-price">$<?php echo $complete_price; ?><span>/month</span></div>
                    
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> <strong>All webinar benefits</strong></li>
                        <li><i class="fas fa-check"></i> Full newsletter access</li>
                        <li><i class="fas fa-check"></i> Newsletter archive</li>
                        <li><i class="fas fa-check"></i> Market analysis reports</li>
                        <li><i class="fas fa-check"></i> Monthly investment outlook</li>
                    </ul>
                    
                    <a href="<?php echo add_query_arg('plan', $complete_id, $register_url); ?>" class="subscribe-button primary-button">Get Complete Access</a>
                </div>
            </div>
            
            <div class="guarantee">
                <i class="fas fa-shield-alt"></i>
                <p><strong>30-Day Money Back Guarantee</strong> - If you're not completely satisfied, we'll refund your subscription, no questions asked.</p>
            </div>
        </div>
        
        <!-- Upcoming webinars -->
        <div class="related-webinars">
            <h3>Upcoming Webinars</h3>
            
            <?php
            // Get upcoming webinars
            $today = date('Y-m-d');
            $upcoming_webinars = new WP_Query(array(
                'post_type' => 'webinar',
                'posts_per_page' => 3,
                'post__not_in' => array($post_id),
                'meta_key' => 'webinar_date',
                'meta_value' => $today,
                'meta_compare' => '>=',
                'orderby' => 'meta_value',
                'order' => 'ASC'
            ));
            
            if ($upcoming_webinars->have_posts()) : ?>
                <div class="upcoming-webinars-grid">
                    <?php while ($upcoming_webinars->have_posts()) : $upcoming_webinars->the_post(); 
                        $upcomingDate = get_field('webinar_date', get_the_ID());
                        $displayDate = $upcomingDate ? date('M j', strtotime($upcomingDate)) : '';
                        $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: get_template_directory_uri() . '/assets/images/default-webinar-thumb.jpg';
                    ?>
                        <div class="webinar-card">
                            <div class="webinar-card-image">
                                <img src="<?php echo $thumbnail; ?>" alt="<?php the_title(); ?>">
                                <?php if ($displayDate) : ?>
                                    <span class="webinar-date-badge"><?php echo $displayDate; ?></span>
                                <?php endif; ?>
                            </div>
                            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <p class="view-all">
                    <a href="<?php echo get_post_type_archive_link('webinar'); ?>">
                        View all upcoming webinars <i class="fas fa-arrow-right"></i>
                    </a>
                </p>
            <?php else : ?>
                <p>No upcoming webinars scheduled at this time.</p>
            <?php endif; 
            wp_reset_postdata(); 
            ?>
        </div>
    </div>
    
    <!-- Video Preview Modal -->
    <?php if ($webinar_preview) : ?>
    <div id="preview-modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $title; ?> - Preview</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="video-container">
                        <video controls>
                            <source src="<?php echo $webinar_preview; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
                <div class="modal-footer">
                    <p>Want to see the full webinar? <a href="<?php echo add_query_arg('plan', $webinar_id, $register_url); ?>">Subscribe now</a></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</article>