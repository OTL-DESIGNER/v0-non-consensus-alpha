<?php
/**
 * Template for displaying single webinar posts
 */

get_header();
?>

<div class="bg-light py-5">
    <div class="container">
        <?php
        while (have_posts()) :
            the_post();
            
            // Check if content is restricted for current user
            if (function_exists('pms_is_post_restricted') && pms_is_post_restricted(get_the_ID())) {
                // User doesn't have access, show restricted template
                get_template_part('template-parts/content', 'restricted-webinar');
            } else {
                // User has access, show full content
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('webinar-single'); ?>>
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Webinar Header -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <header class="entry-header mb-4">
                                        <h1 class="display-5 fw-bold text-primary"><?php the_title(); ?></h1>
                                        
                                        <?php 
                                        // Display webinar details
                                        $webinar_date = get_post_meta(get_the_ID(), 'webinar_date', true);
                                        if (empty($webinar_date) && function_exists('get_field')) {
                                            $webinar_date = get_field('webinar_date');
                                        }

                                        $webinar_time = get_post_meta(get_the_ID(), 'webinar_time', true);
                                        if (empty($webinar_time) && function_exists('get_field')) {
                                            $webinar_time = get_field('webinar_time');
                                        }

                                        $webinar_presenter = get_post_meta(get_the_ID(), 'webinar_presenter', true);
                                        if (empty($webinar_presenter) && function_exists('get_field')) {
                                            $webinar_presenter = get_field('webinar_presenter');
                                        }

                                        $webinar_duration = get_post_meta(get_the_ID(), 'webinar_duration', true);
                                        if (empty($webinar_duration) && function_exists('get_field')) {
                                            $webinar_duration = get_field('webinar_duration'); 
                                        }

                                        $webinar_recording = get_post_meta(get_the_ID(), 'webinar_recording', true);
                                        if (empty($webinar_recording) && function_exists('get_field')) {
                                            $webinar_recording = get_field('webinar_recording');
                                            if (is_array($webinar_recording) && isset($webinar_recording['url'])) {
                                                $webinar_recording = $webinar_recording['url'];
                                            }
                                        }

                                        // Format date for display
                                        $display_date = '';
                                        if ($webinar_date) {
                                            // Try to parse the date in various formats
                                            $timestamp = strtotime($webinar_date);
                                            if ($timestamp !== false) {
                                                $display_date = date_i18n('F j, Y', $timestamp);
                                                if ($webinar_time) {
                                                    $display_date .= ' at ' . $webinar_time;
                                                }
                                            }
                                        }

                                        // Check if webinar is in the future
                                        $is_upcoming = false;
                                        if ($webinar_date) {
                                            $webinar_datetime = $webinar_date;
                                            if ($webinar_time && strpos($webinar_date, ':') === false) {
                                                $webinar_datetime .= ' ' . $webinar_time;
                                            }
                                            $is_upcoming = strtotime($webinar_datetime) > current_time('timestamp');
                                        }
                                        
                                        // Clean up presenter name if it's a Zoom ID
                                        if ($webinar_presenter && preg_match('/^[a-zA-Z0-9]{20,}$/', $webinar_presenter)) {
                                            // It's likely a Zoom ID, set a default name
                                            $webinar_presenter = "Investment Expert";
                                        }
                                        ?>
                                        
                                        <div class="entry-meta d-flex flex-wrap gap-3 text-muted fs-6 mb-3">
                                            <?php if ($display_date) : ?>
                                                <div class="webinar-date">
                                                    <i class="fa fa-calendar-alt me-2 text-primary"></i> <?php echo $display_date; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($webinar_duration) : ?>
                                                <div class="webinar-duration">
                                                    <i class="fa fa-clock me-2 text-primary"></i> <?php echo $webinar_duration; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($webinar_presenter) : ?>
                                                <div class="webinar-presenter">
                                                    <i class="fa fa-user me-2 text-primary"></i> Presented by: <?php echo $webinar_presenter; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php 
                                        $categories = get_the_term_list(get_the_ID(), 'topic', '', ', ', '');
                                        if ($categories) {
                                            echo '<div class="webinar-topics mb-3"><i class="fa fa-tags me-2 text-primary"></i> ' . $categories . '</div>';
                                        }
                                        ?>
                                    </header>

                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="featured-image mb-4">
                                            <?php the_post_thumbnail('large', ['class' => 'img-fluid rounded']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="entry-content mb-4">
                                        <?php the_content(); ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($is_upcoming) : ?>
    <!-- Upcoming Webinar Card -->
    <div class="card border-0 shadow-sm mb-4 webinar-countdown-container">
        <div class="card-body p-4 text-center">
    <div class="mb-3">
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
            <i class="fas fa-broadcast-tower me-2"></i> Live Webinar
        </span>
    </div>
    <h3 class="fs-4 fw-bold text-dark mb-2">Join the Webinar</h3>
    <p class="text-muted mb-4">The session is about to begin. You can join right here below:</p>

    <div class="webinar-join mx-auto" style="max-width: 800px;">
        <?php 
        $eroom_meeting_id = get_post_meta(get_the_ID(), 'eroom_meeting_id', true);
        if (empty($eroom_meeting_id) && function_exists('get_field')) {
            $eroom_meeting_id = get_field('eroom_meeting_id');
        }

        if (!empty($eroom_meeting_id)) {
            echo do_shortcode('[stm_zoom_conference post_id="' . intval($eroom_meeting_id) . '"]');
        } else {
            echo '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i> Webinar meeting information will be available soon.</div>';
        }
        ?>
    </div>
</div>

                                </div>
                            <?php elseif (!$is_upcoming && $webinar_recording) : ?>
                                <!-- Recording Card -->
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <h3 class="fs-4 fw-bold text-primary mb-3">Webinar Recording</h3>
                                        
                                        <?php 
                                        // Check if we have a password
                                        $webinar_password = get_post_meta(get_the_ID(), 'webinar_password', true);
                                        if (empty($webinar_password) && function_exists('get_field')) {
                                            $webinar_password = get_field('webinar_password');
                                        }
                                        ?>
                                        
                                        <div class="video-container ratio ratio-16x9 mb-3">
                                            <iframe src="<?php echo esc_url($webinar_recording); ?>" 
                                                    class="rounded" 
                                                    frameborder="0" 
                                                    allow="autoplay; fullscreen" 
                                                    allowfullscreen>
                                            </iframe>
                                        </div>
                                        
                                        <?php if (!empty($webinar_password)) : ?>
                                            <div class="alert alert-info mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-lock me-2"></i>
                                                    <div>
                                                        <strong>Recording Password:</strong> 
                                                        <span id="recording-password"><?php echo esc_html($webinar_password); ?></span>
                                                        <button id="copy-password-btn" class="btn btn-sm btn-outline-primary ms-2" onclick="copyPassword()">
                                                            <i class="fas fa-copy"></i> Copy
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted mt-1 d-block">You'll need this password to access the recording</small>
                                            </div>
                                            
                                            <script>
                                            function copyPassword() {
                                                const password = document.getElementById('recording-password').textContent;
                                                const copyBtn = document.getElementById('copy-password-btn');
                                                const originalHtml = copyBtn.innerHTML;
                                                
                                                navigator.clipboard.writeText(password)
                                                    .then(() => {
                                                        // Change button text temporarily to show success
                                                        copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                                                        copyBtn.classList.remove('btn-outline-primary');
                                                        copyBtn.classList.add('btn-success');
                                                        
                                                        setTimeout(() => {
                                                            copyBtn.innerHTML = originalHtml;
                                                            copyBtn.classList.remove('btn-success');
                                                            copyBtn.classList.add('btn-outline-primary');
                                                        }, 2000);
                                                    })
                                                    .catch(err => {
                                                        console.error('Failed to copy password: ', err);
                                                        // Fallback for browsers that don't support clipboard API
                                                        const tempInput = document.createElement('input');
                                                        document.body.appendChild(tempInput);
                                                        tempInput.value = password;
                                                        tempInput.select();
                                                        document.execCommand('copy');
                                                        document.body.removeChild(tempInput);
                                                        
                                                        copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                                                        copyBtn.classList.remove('btn-outline-primary');
                                                        copyBtn.classList.add('btn-success');
                                                        
                                                        setTimeout(() => {
                                                            copyBtn.innerHTML = originalHtml;
                                                            copyBtn.classList.remove('btn-success');
                                                            copyBtn.classList.add('btn-outline-primary');
                                                        }, 2000);
                                                    });
                                            }
                                            </script>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">Recorded on <?php echo date('F j, Y', strtotime($webinar_date)); ?></span>
                                            
                                            <a href="<?php echo esc_url($webinar_recording); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                                <i class="fas fa-external-link-alt me-2"></i> Open in New Window
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($is_upcoming) : ?>
                                <!-- Questions Form Card -->
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <h3 class="fs-4 fw-bold text-primary mb-3">Have Questions for the Presenter?</h3>
                                        <p class="mb-4">Submit your questions in advance for this webinar:</p>
                                        <?php 
                                        // Display WPForms webinar question form if available
                                        if (function_exists('wpforms')) {
                                            $form_id = get_option('webinar_question_form_id');
                                            if ($form_id) {
                                                echo do_shortcode('[wpforms id="' . $form_id . '" title="false"]');
                                            } else {
                                                // Fallback to hardcoded form ID
                                                echo do_shortcode('[wpforms id="122" title="false"]');
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Related Webinars Card -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <h3 class="fs-4 fw-bold text-primary mb-3">Related Webinars</h3>
                                    <?php
                                    // Get terms for the current webinar
                                    $terms = get_the_terms(get_the_ID(), 'topic');
                                    $term_ids = array();
                                    if ($terms && !is_wp_error($terms)) {
                                        foreach ($terms as $term) {
                                            $term_ids[] = $term->term_id;
                                        }
                                    }
                                    
                                    // Get related webinars
                                    $related_webinars = new WP_Query(array(
                                        'post_type' => 'webinar',
                                        'posts_per_page' => 3,
                                        'post__not_in' => array(get_the_ID()),
                                        'tax_query' => array(
                                            array(
                                                'taxonomy' => 'topic',
                                                'field' => 'term_id',
                                                'terms' => $term_ids,
                                            ),
                                        ),
                                    ));
                                    
                                    if ($related_webinars->have_posts()) {
                                        echo '<div class="list-group list-group-flush">';
                                        while ($related_webinars->have_posts()) {
                                            $related_webinars->the_post();
                                            $related_date = '';
                                            if (function_exists('get_field') && get_field('webinar_date')) {
                                                $related_date = date('M j, Y', strtotime(get_field('webinar_date')));
                                            }
                                            echo '<a href="' . get_permalink() . '" class="list-group-item list-group-item-action d-flex align-items-center border-0 py-3 px-0">
                                                <div class="me-3 text-center" style="min-width: 45px;">
                                                    <i class="fas fa-video fs-4 text-primary"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-1 fs-6 fw-semibold">' . get_the_title() . '</h5>
                                                    <div class="small text-muted">' . $related_date . '</div>
                                                </div>
                                            </a>';
                                        }
                                        echo '</div>';
                                    } else {
                                        echo '<p class="text-muted">No related webinars found.</p>';
                                    }
                                    wp_reset_postdata();
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="sticky-lg-top pt-lg-2" style="top:80px;">
                                <!-- Presenter Card -->
                                <?php if ($webinar_presenter) : ?>
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-primary text-white p-3">
                                            <h3 class="fs-5 mb-0"><i class="fas fa-user-tie me-2"></i> About the Presenter</h3>
                                        </div>
                                        <div class="card-body p-4">
                                            <?php
                                            // If using ACF for presenter details
                                            $presenter_image = '';
                                            $presenter_bio = '';
                                            $presenter_title = '';
                                            
                                            if (function_exists('get_field')) {
                                                $presenter_image = get_field('presenter_image');
                                                $presenter_bio = get_field('presenter_bio');
                                                $presenter_title = get_field('presenter_title');
                                            }
                                            ?>
                                            
                                            <div class="d-flex flex-column align-items-center text-center mb-3">
                                                <?php if ($presenter_image) : ?>
                                                    <img src="<?php echo esc_url($presenter_image['url']); ?>" alt="<?php echo esc_attr($webinar_presenter); ?>" class="rounded-circle mb-3" width="80" height="80">
                                                <?php else : ?>
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                                                        <i class="fas fa-user fs-3 text-primary"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <h4 class="fs-5 fw-bold mb-1"><?php echo $webinar_presenter; ?></h4>
                                                
                                                <?php if ($presenter_title) : ?>
                                                    <div class="text-muted mb-3"><?php echo $presenter_title; ?></div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($presenter_bio) : ?>
                                                <div class="presenter-bio">
                                                    <?php echo $presenter_bio; ?>
                                                </div>
                                            <?php else : ?>
                                                <p class="text-muted">Join this expert presenter for valuable insights and analysis on current market trends and investment strategies.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Webinar Details Card -->
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-primary text-white p-3">
                                        <h3 class="fs-5 mb-0"><i class="fas fa-info-circle me-2"></i> Webinar Details</h3>
                                    </div>
                                    <div class="card-body p-4">
                                        <ul class="list-group list-group-flush">
                                            <?php if ($display_date) : ?>
                                                <li class="list-group-item px-0 py-3 d-flex border-0 border-bottom">
                                                    <div class="text-primary me-3"><i class="fas fa-calendar-alt"></i></div>
                                                    <div>
                                                        <strong class="d-block">Date & Time</strong>
                                                        <span class="text-muted"><?php echo $display_date; ?></span>
                                                    </div>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php if ($webinar_duration) : ?>
                                                <li class="list-group-item px-0 py-3 d-flex border-0 border-bottom">
                                                    <div class="text-primary me-3"><i class="fas fa-clock"></i></div>
                                                    <div>
                                                        <strong class="d-block">Duration</strong>
                                                        <span class="text-muted"><?php echo $webinar_duration; ?></span>
                                                    </div>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <li class="list-group-item px-0 py-3 d-flex border-0 border-bottom">
                                                <div class="text-primary me-3"><i class="fas fa-video"></i></div>
                                                <div>
                                                    <strong class="d-block">Format</strong>
                                                    <span class="text-muted">Online Zoom Webinar</span>
                                                </div>
                                            </li>
                                            
                                            <?php 
                                            $categories = get_the_terms(get_the_ID(), 'topic');
                                            if ($categories && !is_wp_error($categories)) : 
                                            ?>
                                                <li class="list-group-item px-0 py-3 d-flex border-0">
                                                    <div class="text-primary me-3"><i class="fas fa-tags"></i></div>
                                                    <div>
                                                        <strong class="d-block">Topics</strong>
                                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                                            <?php foreach ($categories as $category) : ?>
                                                                <a href="<?php echo get_term_link($category); ?>" class="badge bg-light text-primary text-decoration-none">
                                                                    <?php echo $category->name; ?>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                        
                                        <?php if ($is_upcoming && !empty($zoom_meeting_id)) : ?>
                                            <div class="mt-4 text-center">
                                                <a href="<?php echo esc_url($zoom_link); ?>" class="btn btn-primary w-100" target="_blank">
                                                    <i class="fas fa-user-plus me-2"></i> Join Meeting
                                                </a>
                                                <div class="text-muted small mt-2">Free for subscribers</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- More Webinars Card -->
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white p-3">
                                        <h3 class="fs-5 mb-0"><i class="fas fa-list me-2"></i> More Webinars</h3>
                                    </div>
                                    <div class="card-body p-4">
                                        <?php
                                        // Get upcoming webinars
                                        $upcoming_webinars = new WP_Query(array(
                                            'post_type' => 'webinar',
                                            'posts_per_page' => 3,
                                            'post__not_in' => array(get_the_ID()),
                                            'meta_key' => 'webinar_date',
                                            'meta_value' => date('Y-m-d'),
                                            'meta_compare' => '>=',
                                            'orderby' => 'meta_value',
                                            'order' => 'ASC'
                                        ));
                                        
                                        if ($upcoming_webinars->have_posts()) {
                                            echo '<h5 class="fs-6 fw-bold mb-3">Upcoming Events</h5>';
                                            while ($upcoming_webinars->have_posts()) {
                                                $upcoming_webinars->the_post();
                                                $upcoming_date = '';
                                                
                                                // Get date from standard meta first, then ACF if available
                                                $date_value = get_post_meta(get_the_ID(), 'webinar_date', true);
                                                if (empty($date_value) && function_exists('get_field')) {
                                                    $date_value = get_field('webinar_date');
                                                }
                                                
                                                if ($date_value) {
                                                    $timestamp = strtotime($date_value);
                                                    if ($timestamp !== false) {
                                                        $upcoming_date = date('M j', $timestamp);
                                                    }
                                                }
                                                ?>
                                                <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                                    <?php if ($upcoming_date) : ?>
                                                        <div class="bg-primary text-white text-center rounded p-2 me-3" style="min-width:48px;">
                                                            <div class="fw-bold"><?php echo $upcoming_date; ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold"><a href="<?php the_permalink(); ?>" class="text-decoration-none"><?php the_title(); ?></a></h6>
                                                        <div class="small text-muted"><?php echo wp_trim_words(get_the_excerpt(), 10); ?></div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            echo '<a href="' . get_post_type_archive_link('webinar') . '" class="btn btn-outline-primary btn-sm w-100 mt-2">View All Webinars</a>';
                                        } else {
                                            echo '<p class="text-muted">Stay tuned for upcoming webinars.</p>';
                                            echo '<a href="' . get_post_type_archive_link('webinar') . '" class="btn btn-outline-primary btn-sm w-100 mt-2">View Past Webinars</a>';
                                        }
                                        wp_reset_postdata();
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
                <?php
            }
        endwhile;
        ?>
    </div>
</div>

<?php
get_footer();