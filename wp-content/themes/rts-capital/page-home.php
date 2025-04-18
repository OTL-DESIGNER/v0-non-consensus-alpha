<?php
/**
 * Template Name: Home Page
 */

get_header();
?>

<div class="loading-bar" id="loadingBar"></div>

<main id="primary" class="site-main">
    <!-- Hero Section -->
    <section class="hero-section position-relative overflow-hidden">
        <div class="container">
            <div class="row align-items-center py-5">
                <div class="col-lg-6 py-5 fade-in-up" data-delay="200">
                    <h1 class="display-3 fw-bold text-white mb-3">Discover Alpha Where <span class="text-primary">Others See Noise</span></h1>
                    <p class="lead text-white-75 mb-4">Join sophisticated investors who profit from opportunities overlooked by the consensus narrative. Get actionable investment insights delivered directly to you.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary btn-lg px-4 py-3">
                            <i class="fas fa-rocket me-2"></i>Join Now
                        </a>
                        <a href="<?php echo esc_url(site_url('/about/')); ?>" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                    <div class="mt-4 d-flex align-items-center">
                        <div class="avatar-group me-3">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/member-1.jpg" class="rounded-circle border border-2 border-dark" width="40" height="40" alt="Member">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/member-2.jpg" class="rounded-circle border border-2 border-dark" width="40" height="40" alt="Member">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/member-3.jpg" class="rounded-circle border border-2 border-dark" width="40" height="40" alt="Member">
                        </div>
                        <p class="text-white-75 mb-0">Join 2,500+ investors already profiting from our insights</p>
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-center justify-content-lg-end fade-in-up" data-delay="400">
                    <div class="position-relative dashboard-preview">
                        <div class="card shadow-lg border-0">
                            <div class="card-body p-0">
                                <img src="https://staging.nonconsesus.com/wp-content/uploads/2025/04/nca-hero-right.png" class="img-fluid rounded" alt="Performance Chart">
                            </div>
                        </div>
                        <div class="position-absolute top-0 end-0 translate-middle-y">
                            <div class="badge bg-success p-3 rounded-pill">
                                <i class="fas fa-chart-line me-2"></i>153% YTD Returns
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-bg-overlay"></div>
    </section>

    <!-- Featured Stats Section - NEW -->
    <section class="stats-section py-4 bg-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-3 mb-md-0 fade-in-up" data-delay="300">
                    <div class="py-3">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-user-check text-primary fa-2x me-2"></i>
                            <h2 class="display-6 fw-bold mb-0">2,500+</h2>
                        </div>
                        <p class="text-muted mb-0">Active Members</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3 mb-md-0 fade-in-up" data-delay="400">
                    <div class="py-3">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-chart-line text-primary fa-2x me-2"></i>
                            <h2 class="display-6 fw-bold mb-0">153%</h2>
                        </div>
                        <p class="text-muted mb-0">YTD Returns</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 fade-in-up" data-delay="500">
                    <div class="py-3">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-newspaper text-primary fa-2x me-2"></i>
                            <h2 class="display-6 fw-bold mb-0">24</h2>
                        </div>
                        <p class="text-muted mb-0">Reports Monthly</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 fade-in-up" data-delay="600">
                    <div class="py-3">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-trophy text-primary fa-2x me-2"></i>
                            <h2 class="display-6 fw-bold mb-0">87%</h2>
                        </div>
                        <p class="text-muted mb-0">Success Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services/Offerings Section -->
    <section class="services-section py-5 bg-light">
        <div class="container py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center fade-in-up" data-delay="200">
                    <span class="badge bg-primary text-white px-3 py-2 mb-3">OUR PREMIUM OFFERINGS</span>
                    <h2 class="display-5 fw-bold mb-3">How We Help You Beat The Market</h2>
                    <p class="lead text-muted">Our subscription-based services provide deep research and valuable insights that institutional investors pay thousands for.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 fade-in-up" data-delay="300">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4 p-lg-5">
                            <div class="icon-box bg-primary text-white rounded-4 mb-4">
                                <i class="fas fa-newspaper fa-2x"></i>
                            </div>
                            <h3 class="card-title fs-2 fw-bold">Premium Newsletters</h3>
                            <p class="card-text text-muted">Deep-dive analysis and unconventional investment ideas delivered to your inbox twice monthly.</p>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Contrarian market analysis</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Hidden value opportunities</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Risk-adjusted position suggestions</li>
                            </ul>
                            <a href="<?php echo esc_url(get_post_type_archive_link('newsletter')); ?>" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 fade-in-up" data-delay="400">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4 p-lg-5">
                            <div class="icon-box bg-primary text-white rounded-4 mb-4">
                                <i class="fas fa-video fa-2x"></i>
                            </div>
                            <h3 class="card-title fs-2 fw-bold">Live Webinars</h3>
                            <p class="card-text text-muted">Interactive sessions with our analysts breaking down market developments and answering your questions.</p>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Real-time market commentary</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Q&A with senior analysts</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Access to webinar recordings</li>
                            </ul>
                            <a href="<?php echo esc_url(get_post_type_archive_link('webinar')); ?>" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 fade-in-up" data-delay="500">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4 p-lg-5">
                            <div class="icon-box bg-primary text-white rounded-4 mb-4">
                                <i class="fas fa-rocket fa-2x"></i>
                            </div>
                            <h3 class="card-title fs-2 fw-bold">Complete Access</h3>
                            <p class="card-text text-muted">Our all-inclusive package combining newsletters, webinars, and additional premium benefits.</p>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> All newsletters and webinars</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Early access to research</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Priority analyst support</li>
                            </ul>
                            <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary">Subscribe Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Content Section -->
    <section class="featured-content py-5 bg-white">
        <div class="container py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center fade-in-up" data-delay="200">
                    <span class="badge bg-primary text-white px-3 py-2 mb-3">FEATURED INSIGHTS</span>
                    <h2 class="display-5 fw-bold mb-3">Latest from Our Analysts</h2>
                    <p class="lead text-muted">Preview our latest research and analysis. Subscribe for full access.</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <?php
                    // Get latest newsletter
                    $latest_newsletter = new WP_Query(array(
                        'post_type' => 'newsletter',
                        'posts_per_page' => 1,
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'post_status' => 'publish'
                    ));
                    
                    if ($latest_newsletter->have_posts()) :
                        while ($latest_newsletter->have_posts()) : $latest_newsletter->the_post();
                    ?>
                    <div class="card border-0 shadow-sm mb-4 hover-lift fade-in-up" data-delay="300">
                        <div class="card-body p-4">
                            <div class="ribbon position-absolute">
                                <span class="bg-primary text-white px-3 py-1 small">Latest</span>
                            </div>
                            <h3 class="card-title h4 fw-bold mb-3">
                                <i class="fas fa-newspaper text-primary me-2"></i>
                                Latest Newsletter: <?php the_title(); ?>
                            </h3>
                            <p class="text-muted mb-2 small">
                                <i class="far fa-calendar-alt me-1"></i> <?php echo get_the_date(); ?>
                                <span class="ms-3"><i class="far fa-clock me-1"></i> <?php echo get_the_time(); ?></span>
                            </p>
                            <div class="card-text mb-3">
                                <?php echo wp_trim_words(get_the_excerpt(), 40); ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary">
                                    <i class="fas fa-lock me-1"></i> Subscribe to Access
                                </a>
                                <span class="text-muted small">
                                    <i class="far fa-file-alt me-1"></i> Premium Content
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    
                    <?php
                    // Get upcoming webinar
                    $upcoming_webinar = new WP_Query(array(
                        'post_type' => 'webinar',
                        'posts_per_page' => 1,
                        'meta_key' => 'webinar_date',
                        'meta_value' => date('Y-m-d H:i:s'),
                        'meta_compare' => '>',
                        'meta_type' => 'DATETIME',
                        'orderby' => 'meta_value',
                        'order' => 'ASC',
                        'post_status' => 'publish'
                    ));
                    
                    if ($upcoming_webinar->have_posts()) :
                        while ($upcoming_webinar->have_posts()) : $upcoming_webinar->the_post();
                            $webinar_date = '';
                            if (function_exists('get_field')) {
                                $webinar_date = get_field('webinar_date');
                            }
                            
                            if (!empty($webinar_date)) :
                                $webinar_timestamp = strtotime($webinar_date);
                    ?>
                    <div class="card border-0 shadow-sm hover-lift fade-in-up" data-delay="400">
                        <div class="card-body p-4">
                            <div class="ribbon position-absolute end-0">
                                <span class="bg-danger text-white px-3 py-1 small">Upcoming</span>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center bg-light rounded py-4">
                                        <div class="fs-3 fw-bold text-primary"><?php echo date('d', $webinar_timestamp); ?></div>
                                        <div class="text-uppercase"><?php echo date('M', $webinar_timestamp); ?></div>
                                        <div class="badge bg-primary mt-1"><?php echo date('g:i A', $webinar_timestamp); ?></div>
                                        <div class="countdown-timer small text-muted mt-2" data-time="<?php echo esc_attr($webinar_date); ?>">
                                            <span class="days">00</span>d <span class="hours">00</span>h <span class="minutes">00</span>m
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <h3 class="card-title h4 fw-bold mb-3">
                                        <i class="fas fa-video text-primary me-2"></i>
                                        Upcoming Webinar: <?php the_title(); ?>
                                    </h3>
                                    <div class="card-text mb-3">
                                        <?php echo wp_trim_words(get_the_excerpt(), 30); ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary">
                                            <i class="fas fa-lock me-1"></i> Register to Join
                                        </a>
                                        <span class="text-muted small">
                                            <i class="far fa-clock me-1"></i> <?php echo date('F j, Y', $webinar_timestamp); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                            endif;
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 bg-primary text-white shadow hover-lift fade-in-up" data-delay="500">
                        <div class="card-body p-4">
                            <h3 class="card-title h4 fw-bold mb-3">Member Dashboard</h3>
                            <p class="card-text mb-4">Already a subscriber? Access your premium content from your member dashboard.</p>
                            <div class="d-grid gap-2">
                                <a href="<?php echo esc_url(site_url('/member-dashboard/')); ?>" class="btn btn-light text-primary fw-semibold">
                                    <i class="fas fa-user-circle me-2"></i>Go to Dashboard
                                </a>
                                <a href="<?php echo esc_url(wp_login_url(site_url('/member-dashboard/'))); ?>" class="btn btn-outline-light">
                                    <i class="fas fa-sign-in-alt me-2"></i>Log In
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mt-4 hover-lift fade-in-up" data-delay="600">
                        <div class="card-body p-4">
                            <h3 class="card-title h4 fw-bold mb-3">Join Our Newsletter</h3>
                            <p class="card-text text-muted mb-3">Get a free weekly market summary and exclusive content previews.</p>
                            <form action="#" method="post" class="newsletter-form">
                                <div class="mb-3">
                                    <input type="email" class="form-control form-control-lg" placeholder="Your email address" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Subscribe</button>
                                </div>
                                <p class="form-text text-muted small mt-2">
                                    <i class="fas fa-shield-alt me-1"></i> We respect your privacy. Unsubscribe anytime.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section py-5 bg-light">
        <div class="container py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center fade-in-up" data-delay="200">
                    <span class="badge bg-primary text-white px-3 py-2 mb-3">MEMBER TESTIMONIALS</span>
                    <h2 class="display-5 fw-bold mb-3">What Our Subscribers Say</h2>
                    <p class="lead text-muted">Don't just take our word for it - see what our members have to say about Non-Consensus Alpha.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 fade-in-up" data-delay="300">
                    <div class="card h-100 border-0 shadow-sm testimonial-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="testimonial-avatar me-3">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/testimonial-1.jpg" alt="Testimonial" class="rounded-circle" width="60" height="60">
                                </div>
                                <div>
                                    <h5 class="mb-1">Michael R.</h5>
                                    <p class="text-muted mb-0 small">Investment Advisor</p>
                                </div>
                            </div>
                            <div class="testimonial-stars text-warning mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="mb-0">"The insights from Non-Consensus Alpha have transformed my approach to portfolio management. Their newsletter highlighted three opportunities that outperformed my best picks last quarter."</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 fade-in-up" data-delay="400">
                    <div class="card h-100 border-0 shadow-sm testimonial-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="testimonial-avatar me-3">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/testimonial-2.jpg" alt="Testimonial" class="rounded-circle" width="60" height="60">
                                </div>
                                <div>
                                    <h5 class="mb-1">Jennifer K.</h5>
                                    <p class="text-muted mb-0 small">Individual Investor</p>
                                </div>
                            </div>
                            <div class="testimonial-stars text-warning mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="mb-0">"The webinars alone are worth the subscription price. Being able to ask questions directly to the analysts about market trends has given me confidence in my investment decisions."</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 fade-in-up" data-delay="500">
                    <div class="card h-100 border-0 shadow-sm testimonial-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="testimonial-avatar me-3">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/testimonial-3.jpg" alt="Testimonial" class="rounded-circle" width="60" height="60">
                                </div>
                                <div>
                                    <h5 class="mb-1">David T.</h5>
                                    <p class="text-muted mb-0 small">Retired Executive</p>
                                </div>
                            </div>
                            <div class="testimonial-stars text-warning mb-3">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <p class="mb-0">"After trying several investment newsletters, Non-Consensus Alpha is the only one I've renewed. Their contrarian approach helped me identify opportunities everyone else missed during the market volatility."</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Performance Metrics Section -->
    <section class="performance-section py-5 bg-white">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-5 mb-5 mb-lg-0 fade-in-up" data-delay="200">
                    <span class="badge bg-primary text-white px-3 py-2 mb-3">OUR TRACK RECORD</span>
                    <h2 class="display-5 fw-bold mb-4">Beating The Market Consistently</h2>
                    <p class="lead mb-4">Our recommendations have outperformed market benchmarks across multiple timeframes and market conditions.</p>
                    <ul class="list-unstyled performance-list">
                        <li class="mb-4 d-flex">
                            <div class="icon-box bg-primary text-white rounded-circle me-3">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold">Top Stock Picks</h5>
                                <p class="text-muted mb-0">Our top 10 stock picks have averaged 153% returns over the past year compared to the S&P 500's 19%.</p>
                            </div>
                        </li>
                        <li class="mb-4 d-flex">
                            <div class="icon-box bg-primary text-white rounded-circle me-3">
                                <i class="fas fa-search-dollar"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold">Market-Beating Research</h5>
                                <p class="text-muted mb-0">87% of our research insights have identified trends before they became mainstream news.</p>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="icon-box bg-primary text-white rounded-circle me-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold">Risk Management</h5>
                                <p class="text-muted mb-0">Our risk management strategies have helped subscribers avoid major drawdowns in volatile markets.</p>
                            </div>
                        </li>
                    </ul>
                    <div class="mt-4">
                        <a href="<?php echo esc_url(site_url('/performance/')); ?>" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-chart-bar me-2"></i>View Full Performance
                        </a>
                    </div>
                </div>
                <div class="col-lg-7 fade-in-up" data-delay="400">
                    <div class="card border-0 shadow performance-chart">
                        <div class="card-body p-4">
                            <h3 class="card-title h5 fw-bold mb-4">Non-Consensus Alpha vs. Market Benchmark (2024 YTD)</h3>
                            <div class="position-relative" style="height: 350px;">
                                <img src="https://staging.nonconsesus.com/wp-content/uploads/2025/04/nca-hero-right.png" class="img-fluid rounded" alt="Performance Chart">
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                <div class="d-flex align-items-center me-4">
                                    <div class="chart-legend-box bg-primary me-2"></div>
                                    <span>Non-Consensus Alpha Portfolio</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="chart-legend-box bg-secondary me-2"></div>
                                    <span>S&P 500</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- FAQs Section - NEW -->
    <section class="faq-section py-5 bg-light">
        <div class="container py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-7 text-center fade-in-up" data-delay="200">
                    <span class="badge bg-primary text-white px-3 py-2 mb-3">COMMON QUESTIONS</span>
                    <h2 class="display-5 fw-bold mb-3">Frequently Asked Questions</h2>
                    <p class="lead text-muted">Everything you need to know about our investment services and how they can help you.</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-10 fade-in-up" data-delay="300">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h3 class="accordion-header" id="headingOne">
                                <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    How is Non-Consensus Alpha different from other investment newsletters?
                                </button>
                            </h3>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Our approach focuses on identifying opportunities overlooked by the broader market narrative. While most newsletters follow mainstream thinking, we specifically seek investments that are undervalued due to consensus blindspots, providing our subscribers with unique insights they won't find elsewhere.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h3 class="accordion-header" id="headingTwo">
                                <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    What is included in the Complete Access subscription?
                                </button>
                            </h3>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>The Complete Access subscription includes all our premium newsletters, full access to our live webinars and their recordings, early access to new research reports, priority analyst support, and access to our exclusive member community. It's the most comprehensive way to benefit from our investment insights.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3 border-0 shadow-sm">
                            <h3 class="accordion-header" id="headingThree">
                                <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    How often will I receive new investment recommendations?
                                </button>
                            </h3>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>We publish our premium newsletter twice monthly, each containing 2-3 new investment recommendations plus updates on existing positions. Additionally, we host weekly webinars where our analysts may provide timely recommendations based on market conditions. In total, subscribers typically receive 6-8 new investment ideas each month.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0 shadow-sm">
                            <h3 class="accordion-header" id="headingFour">
                                <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Is there a money-back guarantee?
                                </button>
                            </h3>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Yes, we offer a 30-day money-back guarantee on all our subscription plans. If you're not completely satisfied with our service, simply contact our support team within 30 days of your subscription date, and we'll process a full refund, no questions asked.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section py-5 bg-primary text-white">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center fade-in-up" data-delay="200">
                    <h2 class="display-4 fw-bold mb-4">Ready to Profit Beyond Consensus?</h2>
                    <p class="lead mb-4">Join Non-Consensus Alpha today and gain access to premium market insights, exclusive webinars, and a community of sophisticated investors.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-light btn-lg text-primary px-4 py-3">
                            <i class="fas fa-rocket me-2"></i>Subscribe Now
                        </a>
                        <a href="<?php echo esc_url(site_url('/about/')); ?>" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Back to Top Button -->
    <div class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </div>
</main>

<!-- Add JavaScript for animation and interactive elements -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Loading bar animation
    const loadingBar = document.getElementById('loadingBar');
    loadingBar.style.width = '100%';
    setTimeout(() => {
        loadingBar.style.opacity = '0';
    }, 1000);
    
    // Fade in elements on scroll
    const fadeElements = document.querySelectorAll('.fade-in-up');
    
    const fadeCallback = (entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add delay if specified
                const delay = entry.target.getAttribute('data-delay') || 0;
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, delay);
            }
        });
    };
    
    const fadeObserver = new IntersectionObserver(fadeCallback, {
        threshold: 0.1
    });
    
    fadeElements.forEach(element => {
        fadeObserver.observe(element);
    });
    
    // Sticky header
    const header = document.querySelector('.site-header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    
    // Countdown timer for webinars
    const countdownElements = document.querySelectorAll('.countdown-timer');
    if (countdownElements.length > 0) {
        countdownElements.forEach(element => {
            const targetDate = new Date(element.getAttribute('data-time')).getTime();
            
            const updateCountdown = () => {
                const now = new Date().getTime();
                const distance = targetDate - now;
                
                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    
                    element.querySelector('.days').textContent = days.toString().padStart(2, '0');
                    element.querySelector('.hours').textContent = hours.toString().padStart(2, '0');
                    element.querySelector('.minutes').textContent = minutes.toString().padStart(2, '0');
                }
            };
            
            updateCountdown();
            setInterval(updateCountdown, 60000);
        });
    }
    
    // Scroll to top button
    const scrollTopBtn = document.getElementById('scrollTop');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollTopBtn.classList.add('visible');
        } else {
            scrollTopBtn.classList.remove('visible');
        }
    });
    
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

<?php
get_footer();