<?php get_header(); ?>

<div class="bg-light py-5">
    <div class="container">
        <!-- Main Header -->
        <header class="mb-4">
            <h1 class="display-5 fw-bold text-primary mb-2">Investment Newsletters</h1>
            <p class="lead text-secondary">Access our premium market analysis and investment strategies.</p>
        </header>
        
        <div class="row">
            <!-- Main Content -->
            <main class="col-lg-8 mb-4 mb-lg-0">
                <?php if (have_posts()) : ?>
                    <div class="mb-4">
                        <?php while (have_posts()) : the_post(); ?>
                            <article class="card newsletter-card mb-4">
                                <!-- Newsletter Header Bar -->
                                <div class="newsletter-header card-header bg-primary text-white p-3">
                                    <h2 class="fs-4 mb-0 fw-semibold">
                                        <a href="<?php the_permalink(); ?>" class="text-white text-decoration-none"><?php the_title(); ?></a>
                                    </h2>
                                </div>
                                
                                <!-- Newsletter Content -->
                                <div class="card-body p-4">
                                    <div class="text-muted small mb-3">
                                        <i class="far fa-calendar-alt me-2"></i> 
                                        <?php echo get_the_date('F j, Y'); ?>
                                    </div>
                                    
                                    <div class="card-text mb-4 newsletter-excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    
                                    <div class="newsletter-actions">
                                        <?php if (function_exists('get_field') && get_field('newsletter_pdf')) : ?>
                                            <a href="<?php echo esc_url(get_field('newsletter_pdf')); ?>" class="btn btn-outline-secondary btn-newsletter me-2" download>
                                                <i class="fas fa-file-pdf"></i> Download PDF
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-newsletter">
                                            <i class="far fa-eye me-2"></i> Read Online
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-5 d-flex justify-content-center">
                        <?php 
                        // Use Bootstrap-compatible pagination
                        $pagination = paginate_links(array(
                            'prev_text' => '<i class="fas fa-chevron-left"></i>',
                            'next_text' => '<i class="fas fa-chevron-right"></i>',
                            'type'      => 'array',
                        )); 
                        
                        if ($pagination) {
                            echo '<nav aria-label="Page navigation"><ul class="pagination">';
                            foreach ($pagination as $page_link) {
                                // Add Bootstrap classes to pagination
                                $page_link = str_replace('page-numbers', 'page-link', $page_link);
                                
                                if (strpos($page_link, 'current') !== false) {
                                    echo '<li class="page-item active">' . $page_link . '</li>';
                                } else {
                                    echo '<li class="page-item">' . $page_link . '</li>';
                                }
                            }
                            echo '</ul></nav>';
                        }
                        ?>
                    </div>
                    
                <?php else : ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-5 text-center">
                            <h2 class="fs-4 fw-semibold mb-3">No Newsletters Found</h2>
                            <p class="text-muted mb-0">There are currently no newsletters available. Please check back soon.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
            
            <!-- Sidebar -->
            <aside class="col-lg-4">
                <div class="sticky-lg-top pt-lg-2" style="top:80px;">
                    <!-- Subscription Widget -->
                    <div class="card sidebar-widget mb-4">
                        <div class="card-header widget-header bg-primary text-white">
                            <h3 class="widget-title fs-5 mb-0"><i class="fas fa-id-card me-2"></i> Your Subscription</h3>
                        </div>
                        
                        <div class="card-body widget-content">
                            <?php 
                            // Get current user
                            $current_user = wp_get_current_user();
                            $user_id = $current_user->ID;
                            $is_admin = current_user_can('administrator');
                            
                            // Check subscription status more reliably
                            $has_subscription = false;
                            
                            // Try using PMS functions directly if available
                            if (function_exists('pms_is_member')) {
                                $has_subscription = pms_is_member($user_id);
                            }
                            
                            // Alternative check using the database
                            if (!$has_subscription) {
                                global $wpdb;
                                $subscription_count = $wpdb->get_var($wpdb->prepare(
                                    "SELECT COUNT(*) FROM {$wpdb->prefix}pms_member_subscriptions 
                                    WHERE user_id = %d AND status = 'active'",
                                    $user_id
                                ));
                                $has_subscription = !empty($subscription_count);
                            }
                            
                            if ($has_subscription || $is_admin) : ?>
                                <div class="d-flex align-items-start">
                                    <div class="subscription-active-icon bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-medium fs-5 text-dark mb-1">Active Subscription</h4>
                                        <p class="text-secondary mb-0">You have access to premium content.</p>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="d-flex align-items-start">
                                    <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-medium fs-5 text-dark mb-1">No Active Subscription</h4>
                                        <p class="text-secondary mb-1">Subscribe to access premium content.</p>
                                        <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary w-100 mt-3">
                                            <i class="fas fa-unlock-alt me-2"></i> Subscribe Now
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Links Widget -->
                    <div class="card sidebar-widget mb-4">
                        <div class="card-header widget-header bg-primary text-white">
                            <h3 class="widget-title fs-5 mb-0"><i class="fas fa-link me-2"></i> Quick Links</h3>
                        </div>
                        
                        <div class="card-body widget-content p-0">
                            <ul class="list-group list-group-flush quick-links-list">
                                <li class="list-group-item px-3 py-3 border-0">
                                    <a href="<?php echo esc_url(get_post_type_archive_link('newsletter')); ?>" class="quick-link text-decoration-none d-flex align-items-center text-dark">
                                        <i class="fas fa-newspaper text-primary me-3"></i>
                                        <span>Newsletters</span>
                                        <i class="fas fa-chevron-right ms-auto text-muted"></i>
                                    </a>
                                </li>
                                <li class="list-group-item px-3 py-3 border-0">
                                    <a href="<?php echo esc_url(get_post_type_archive_link('webinar')); ?>" class="quick-link text-decoration-none d-flex align-items-center text-dark">
                                        <i class="fas fa-video text-primary me-3"></i>
                                        <span>Webinars</span>
                                        <i class="fas fa-chevron-right ms-auto text-muted"></i>
                                    </a>
                                </li>
                                <li class="list-group-item px-3 py-3 border-0">
                                    <a href="<?php echo esc_url(site_url('/account/')); ?>" class="quick-link text-decoration-none d-flex align-items-center text-dark">
                                        <i class="fas fa-user-cog text-primary me-3"></i>
                                        <span>Account Settings</span>
                                        <i class="fas fa-chevron-right ms-auto text-muted"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <?php if (!$has_subscription && !$is_admin) : ?>
                    <!-- Premium Benefits Widget -->
                    <div class="card sidebar-widget">
                        <div class="card-header widget-header bg-primary text-white">
                            <h3 class="widget-title fs-5 mb-0"><i class="fas fa-star me-2"></i> Premium Benefits</h3>
                        </div>
                        
                        <div class="card-body widget-content bg-light">
                            <h4 class="fs-5 fw-medium mb-3 text-dark">Why Subscribe?</h4>
                            <ul class="list-unstyled mb-4">
                                <li class="d-flex align-items-center mb-3">
                                    <i class="fas fa-check text-primary me-3"></i>
                                    <span class="text-dark">Premium market analysis</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="fas fa-check text-primary me-3"></i>
                                    <span class="text-dark">Investment strategies</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="fas fa-check text-primary me-3"></i>
                                    <span class="text-dark">Exclusive webinars</span>
                                </li>
                                <li class="d-flex align-items-center">
                                    <i class="fas fa-check text-primary me-3"></i>
                                    <span class="text-dark">Portfolio recommendations</span>
                                </li>
                            </ul>
                            <a href="<?php echo esc_url(site_url('/plans/')); ?>" class="btn btn-primary w-100">
                                View Subscription Plans
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php get_footer(); ?>