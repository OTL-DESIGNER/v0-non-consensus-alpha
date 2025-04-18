<?php
/**
 * Template part for displaying restricted newsletter content
 */
// Get current post data
$post_id = get_the_ID();
$title = get_the_title();
$excerpt = get_the_excerpt();
$date = get_the_date();
$featured_image = get_the_post_thumbnail_url($post_id, 'large');
$categories = get_the_term_list($post_id, 'topic', '', ', ', '');

// Get newsletter custom fields if using ACF
$newsletter_pdf = get_field('newsletter_pdf', $post_id);
?>

<article id="post-<?php echo $post_id; ?>" <?php post_class('restricted-content newsletter-restricted'); ?>>
    
    <div class="restriction-container">
        <div class="restriction-content-preview">
            <header class="entry-header">
                <h1 class="entry-title"><?php echo $title; ?></h1>
                <div class="entry-meta">
                    <span class="posted-on"><?php echo $date; ?></span>
                    <?php if ($categories) : ?>
                        <span class="topics">Topics: <?php echo $categories; ?></span>
                    <?php endif; ?>
                </div>
            </header>

            <?php if ($featured_image) : ?>
                <div class="featured-image">
                    <img src="<?php echo $featured_image; ?>" alt="<?php echo $title; ?>">
                </div>
            <?php endif; ?>

            <div class="entry-content-teaser">
                <?php 
                // Display first paragraph of content
                $content = get_the_content();
                $first_paragraph = substr($content, 0, strpos($content, '</p>') + 4);
                echo $first_paragraph;
                ?>
                
                <div class="content-blur-overlay">
                    <div class="blurred-content">
                        <?php 
                        // Get remaining paragraphs for blurred background effect
                        $remaining_content = substr($content, strpos($content, '</p>') + 4);
                        echo $remaining_content;
                        ?>
                    </div>
                    <div class="blur-message">
                        <i class="fa fa-lock"></i>
                        <h3>This premium content is exclusively for our subscribers</h3>
                        <p>Unlock this complete newsletter and our entire archive of market insights with a subscription.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="restriction-subscribe-panel">
            <div class="value-panel">
                <h2>Inside This Newsletter</h2>
                <ul class="newsletter-highlights">
                    <?php
                    // Extract key points from the content
                    $content_without_tags = strip_tags($content);
                    $content_sentences = preg_split('/(?<=[.!?])\s+/', $content_without_tags, -1, PREG_SPLIT_NO_EMPTY);
                    $highlight_sentences = array_slice($content_sentences, 0, 3);
                    
                    foreach ($highlight_sentences as $sentence) {
                        echo '<li><i class="fa fa-check-circle"></i> ' . $sentence . '</li>';
                    }
                    ?>
                </ul>
            </div>

            <div class="social-proof">
                <h3>Trusted by Leading Investors</h3>
                <div class="testimonial">
                    <blockquote>"RTS Capital's analysis has been instrumental in helping me navigate volatile markets and identify opportunities others miss."</blockquote>
                    <cite>— John D., Portfolio Manager</cite>
                </div>
                <p class="subscriber-count"><strong>Join 1,200+ investors</strong> receiving our premium market insights</p>
            </div>

            <div class="subscription-options">
                <h2>Subscribe to Continue Reading</h2>
                
                <?php
                // Get subscription plan details
                // These functions are for Paid Member Subscriptions plugin
                $newsletter_price = '49'; // Default price if can't get from plugin
                $newsletter_id = '';
                $complete_price = '99'; // Default price if can't get from plugin
                $complete_id = '';
                
                // Try to get the actual subscription plans if the functions exist
                if (function_exists('pms_get_subscription_plans')) {
                    $subscription_plans = pms_get_subscription_plans();
                    foreach ($subscription_plans as $plan) {
                        if (strpos(strtolower($plan->name), 'newsletter') !== false) {
                            $newsletter_price = $plan->price;
                            $newsletter_id = $plan->id;
                        } elseif (strpos(strtolower($plan->name), 'complete') !== false || 
                                  strpos(strtolower($plan->name), 'bundle') !== false) {
                            $complete_price = $plan->price;
                            $complete_id = $plan->id;
                        }
                    }
                }
                
                // Get register page URL
                $register_page_id = function_exists('pms_get_page_id') ? pms_get_page_id('register') : 0;
                $register_url = $register_page_id ? get_permalink($register_page_id) : site_url('/register/');
                ?>
                
                <div class="subscription-cards">
                    <div class="subscription-card">
                        <h3>Newsletter Access</h3>
                        <div class="price">$<?php echo $newsletter_price; ?><span>/month</span></div>
                        <ul class="features">
                            <li><i class="fa fa-check"></i> Full access to all newsletters</li>
                            <li><i class="fa fa-check"></i> Complete newsletter archive</li>
                            <li><i class="fa fa-check"></i> Downloadable PDF versions</li>
                            <li><i class="fa fa-check"></i> Email notifications for new issues</li>
                        </ul>
                        <a href="<?php echo add_query_arg('subscription_plan', $newsletter_id, $register_url); ?>" class="subscribe-button">Subscribe Now</a>
                    </div>
                    
                    <div class="subscription-card featured">
                        <div class="best-value-badge">Best Value</div>
                        <h3>Complete Access</h3>
                        <div class="price">$<?php echo $complete_price; ?><span>/month</span></div>
                        <ul class="features">
                            <li><i class="fa fa-check"></i> <strong>All newsletter benefits</strong></li>
                            <li><i class="fa fa-check"></i> <strong>Live webinar access</strong></li>
                            <li><i class="fa fa-check"></i> <strong>Webinar recording archive</strong></li>
                            <li><i class="fa fa-check"></i> <strong>Priority Q&A responses</strong></li>
                            <li><i class="fa fa-check"></i> <strong>Monthly market overview calls</strong></li>
                        </ul>
                        <a href="<?php echo add_query_arg('subscription_plan', $complete_id, $register_url); ?>" class="subscribe-button primary">Get Complete Access</a>
                    </div>
                </div>
                
                <div class="guarantee">
                    <i class="fa fa-shield-alt"></i>
                    <p><strong>30-Day Money Back Guarantee</strong> - Try risk-free and cancel anytime if you're not completely satisfied.</p>
                </div>
            </div>
            
            <div class="sample-offer">
                <h3>Want a sample before subscribing?</h3>
                <p>Contact us for a free sample newsletter.</p>
                <a href="<?php echo site_url('/contact/'); ?>" class="subscribe-button">Request Sample</a>
            </div>
        </div>
    </div>
</article>
