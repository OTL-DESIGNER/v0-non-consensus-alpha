<?php get_header(); ?>

<div class="bg-light py-5">
    <div class="container">
        <!-- Main Header -->
        <header class="text-center mb-5">
            <h1 class="display-5 fw-bold text-primary mb-3">Investment Webinars</h1>
            <p class="lead text-secondary mx-auto" style="max-width: 700px;">
                Join our financial experts for in-depth market analysis, investment strategies, and wealth management insights through our exclusive webinar series.
            </p>
            
            <?php if (!is_user_logged_in() || (function_exists('pms_is_member') && !pms_is_member(get_current_user_id()))) : ?>
                <div class="mt-4">
                    <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary">
                        <i class="fas fa-unlock-alt me-2"></i> Subscribe for Full Access
                    </a>
                </div>
            <?php endif; ?>
        </header>
        
        <!-- Webinar Filter Tabs -->
        <div class="card border-0 shadow-sm mb-5">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill border-0" id="webinarTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3 border-0" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="true">
                            <i class="fas fa-calendar-alt me-2"></i> Upcoming Webinars
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 border-0" id="recordings-tab" data-bs-toggle="tab" data-bs-target="#recordings" type="button" role="tab" aria-controls="recordings" aria-selected="false">
                            <i class="fas fa-play-circle me-2"></i> Past Recordings
                        </button>
                    </li>
                    <?php if (is_user_logged_in() && (function_exists('pms_is_member') && pms_is_member(get_current_user_id()))) : ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3 border-0" id="registered-tab" data-bs-toggle="tab" data-bs-target="#registered" type="button" role="tab" aria-controls="registered" aria-selected="false">
                                <i class="fas fa-user-check me-2"></i> My Webinars
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="tab-content" id="webinarTabContent">
            <!-- Upcoming Webinars Tab -->
            <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                <div class="row">
                    <?php
                    // Debug: Print all webinar posts to see what's available for admin users
                    if (current_user_can('manage_options') && empty($_GET['hide_debug'])) {
                        $all_webinars = get_posts(array(
                            'post_type' => 'webinar',
                            'numberposts' => -1,
                        ));
                        
                        if (!empty($all_webinars)) {
                            echo '<div class="col-12 mb-4">';
                            echo '<div class="alert alert-info">';
                            echo '<h5>Debug Info (visible only to admins)</h5>';
                            echo '<p>Found ' . count($all_webinars) . ' total webinar posts</p>';
                            echo '<ul>';
                            foreach ($all_webinars as $webinar) {
                                $webinar_date = get_post_meta($webinar->ID, 'webinar_date', true);
                                echo '<li>' . $webinar->post_title . ' (ID: ' . $webinar->ID . ') - Date: ' . ($webinar_date ?: 'No date set') . '</li>';
                            }
                            echo '</ul>';
                            echo '<a href="' . add_query_arg('hide_debug', '1') . '" class="btn btn-sm btn-secondary">Hide Debug Info</a>';
                            if (current_user_can('manage_options')) {
                                echo ' <a href="' . admin_url('edit.php?post_type=webinar&fix_all_webinars=1') . '" class="btn btn-sm btn-primary">Fix All Webinar Dates</a>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    
                    // Query upcoming webinars - using timestamp and multiple date formats
                    $args = array(
                        'post_type' => 'webinar',
                        'posts_per_page' => 6,
                        'post_status' => 'publish',
                        'meta_query' => array(
                            'relation' => 'OR',
                            // For webinar_timestamp (most reliable)
                            array(
                                'key' => 'webinar_timestamp',
                                'value' => time(),
                                'compare' => '>=',
                                'type' => 'NUMERIC'
                            ),
                            // For MySQL datetime format
                            array(
                                'key' => 'webinar_date',
                                'value' => date('Y-m-d H:i:s'),
                                'compare' => '>=',
                                'type' => 'DATETIME'
                            ),
                            // For ACF date format (YYYYMMDD)
                            array(
                                'key' => 'webinar_date',
                                'value' => date('Ymd'),
                                'compare' => '>=',
                                'type' => 'NUMERIC'
                            ),
                            // For stm_date field (millisecond timestamp)
                            array(
                                'key' => 'stm_date',
                                'value' => time() * 1000, // Convert current time to milliseconds
                                'compare' => '>=',
                                'type' => 'NUMERIC'
                            )
                        ),
                        'orderby' => 'meta_value_num',
                        'meta_key' => 'webinar_timestamp',
                        'order' => 'ASC'
                    );
                    
                    $upcoming_webinars = new WP_Query($args);
                    
                    if ($upcoming_webinars->have_posts()) :
                        while ($upcoming_webinars->have_posts()) : $upcoming_webinars->the_post();
                            // Get webinar details
                            $webinar_date = get_post_meta(get_the_ID(), 'webinar_date', true);
                            $webinar_timestamp = get_post_meta(get_the_ID(), 'webinar_timestamp', true);
                            $webinar_time = get_post_meta(get_the_ID(), 'webinar_time', true);
                            $webinar_presenter = get_post_meta(get_the_ID(), 'webinar_presenter_name', true);
                            if (empty($webinar_presenter)) {
                                $webinar_presenter = get_post_meta(get_the_ID(), 'webinar_presenter', true);
                            }
                            $webinar_duration = get_post_meta(get_the_ID(), 'webinar_duration', true);
                            
                            // Default values if ACF fields aren't being used
                            if (empty($webinar_presenter) && function_exists('get_field')) {
                                $webinar_presenter = get_field('webinar_presenter');
                            }
                            
                            if (empty($webinar_duration) && function_exists('get_field')) {
                                $webinar_duration = get_field('webinar_duration');
                            }
                            
                            // Initialize format variables
                            $display_date = '';
                            $display_time = '';
                            $month = '';
                            $day = '';
                            $year = '';
                            $days_until = '';
                            
                            // Format date for display - prioritize webinar_timestamp
                            if (!empty($webinar_timestamp)) {
                                $date_timestamp = intval($webinar_timestamp);
                            } else if ($webinar_date) {
                                // Check if it's in ACF format (YYYYMMDD)
                                if (preg_match('/^\d{8}$/', $webinar_date)) {
                                    // Extract year, month, day
                                    $year = substr($webinar_date, 0, 4);
                                    $month = substr($webinar_date, 4, 2);
                                    $day = substr($webinar_date, 6, 2);
                                    
                                    // Create a properly formatted date string
                                    $formatted_date = "$year-$month-$day 00:00:00";
                                    $date_timestamp = strtotime($formatted_date);
                                } else {
                                    // Handle regular MySQL datetime format
                                    $date_timestamp = strtotime($webinar_date);
                                }
                                
                                // Get the time from stm_date if available (for more accurate timing)
                                $stm_date = get_post_meta(get_the_ID(), 'stm_date', true);
                                if (!empty($stm_date) && is_numeric($stm_date) && strlen($stm_date) > 10) {
                                    $date_timestamp = intval($stm_date) / 1000; // Convert from milliseconds to seconds
                                }
                            } else {
                                // Fallback to current time if no date is set
                                $date_timestamp = time();
                            }
                            
                            // Now format the display values
                            $month = date_i18n('M', $date_timestamp);
                            $day = date_i18n('d', $date_timestamp);
                            $year = date_i18n('Y', $date_timestamp);
                            $display_date = date_i18n('F j, Y', $date_timestamp);
                            
                            // For time display, try multiple sources
                            $stm_time = get_post_meta(get_the_ID(), 'stm_time', true);
                            if (!empty($stm_time)) {
                                // Format with AM/PM if needed
                                if (strpos($stm_time, ':') !== false && strlen($stm_time) <= 5) {
                                    $time_parts = explode(':', $stm_time);
                                    $hours = isset($time_parts[0]) ? intval($time_parts[0]) : 0;
                                    $minutes = isset($time_parts[1]) ? intval($time_parts[1]) : 0;
                                    $display_time = date('g:i A', mktime($hours, $minutes, 0));
                                } else {
                                    $display_time = $stm_time;
                                }
                            } elseif (strlen($webinar_date) > 10) {
                                // If webinar_date has time component
                                $display_time = date('g:i A', $date_timestamp);
                            } elseif ($webinar_time) {
                                // If there's a separate webinar_time field
                                $display_time = $webinar_time;
                            } else {
                                // No time available
                                $display_time = '';
                            }
                            
                            // Calculate days until webinar using timezone-aware calculation
                            $now = new DateTime('now', new DateTimeZone(wp_timezone_string()));
                            $webinar_datetime = new DateTime('@' . $date_timestamp);
                            $webinar_datetime->setTimezone(new DateTimeZone(wp_timezone_string()));
                            
                            // Calculate full days between now and the webinar
                            $now_day_start = new DateTime($now->format('Y-m-d') . ' 00:00:00', new DateTimeZone(wp_timezone_string()));
                            $webinar_day_start = new DateTime($webinar_datetime->format('Y-m-d') . ' 00:00:00', new DateTimeZone(wp_timezone_string()));
                            
                            // Calculate the number of days (as integer)
                            $date_diff = (int)$webinar_day_start->diff($now_day_start)->format('%r%a');
                            
                            // Set display text based on days difference
                            if ($date_diff == 0) {
                                $days_until = 'Today';
                            } elseif ($date_diff == -1) {
                                $days_until = 'Tomorrow';
                            } elseif ($date_diff < 0) {
                                $days_until = 'In ' . abs($date_diff) . ' days';
                            } else {
                                $days_until = abs($date_diff) . ' days ago';
                            }
                            
                            // Debug information for admin users
                            if (current_user_can('manage_options')) {
                                $current_time = time();
                                $current_date = date('Y-m-d H:i:s', $current_time);
                                $wp_timezone = wp_timezone_string();
                                
                                echo '<div class="col-12 mb-4">';
                                echo '<div class="alert alert-info">';
                                echo '<h5>Debug Info for Webinar: ' . get_the_title() . '</h5>';
                                echo '<p><strong>WP Date/Time:</strong> ' . $current_date . '<br>';
                                echo '<strong>WP Timezone:</strong> ' . $wp_timezone . '<br>';
                                echo '<strong>Server Time:</strong> ' . date('Y-m-d H:i:s') . '<br>';
                                echo '<strong>Webinar Date in DB:</strong> ' . $webinar_date . '<br>';
                                echo '<strong>Webinar Timestamp:</strong> ' . $webinar_timestamp . ' (' . date('Y-m-d H:i:s', $webinar_timestamp) . ')<br>';
                                echo '<strong>Calculated Timestamp:</strong> ' . $date_timestamp . ' (' . date('Y-m-d H:i:s', $date_timestamp) . ')<br>';
                                echo '<strong>STM Time:</strong> ' . $stm_time . '<br>';
                                echo '<strong>Display Time:</strong> ' . $display_time . '<br>';
                                echo '<strong>Date Diff Calculation:</strong> ' . $date_diff . ' days<br>';
                                
                                // Check all meta for this post
                                $all_meta = get_post_meta(get_the_ID());
                                echo '<strong>Raw Metadata:</strong><br>';
                                echo '<code style="display:block;white-space:pre-wrap;margin:10px 0;padding:10px;background:#f5f5f5;">';
                                print_r($all_meta);
                                echo '</code>';
                                
                                echo '</p></div>';
                                echo '</div>';
                            }
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="row g-0">
                                <?php if ($display_date) : ?>
                                <div class="col-3 p-3 text-center d-flex flex-column justify-content-center align-items-center bg-primary text-white border-end">
                                    <div class="display-4 fw-bold"><?php echo esc_html($day); ?></div>
                                    <div class="text-uppercase"><?php echo esc_html($month); ?></div>
                                    <div class="mt-1 small"><?php echo esc_html($year); ?></div>
                                    <?php if ($days_until) : ?>
                                        <div class="mt-2 badge bg-white text-primary"><?php echo esc_html($days_until); ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <div class="col-3 p-3 text-center d-flex flex-column justify-content-center align-items-center bg-primary text-white border-end">
                                    <i class="fas fa-calendar-alt display-5"></i>
                                    <div class="mt-2 small">Date TBD</div>
                                </div>
                                <?php endif; ?>
                                <div class="col-9">
                                    <div class="card-body p-4">
                                        <h3 class="card-title fs-5 fw-bold mb-2">
                                            <a href="<?php the_permalink(); ?>" class="text-decoration-none text-primary stretched-link"><?php the_title(); ?></a>
                                        </h3>
                                        
                                        <div class="d-flex align-items-center text-muted small mb-2">
                                            <?php if ($display_time) : ?>
                                            <div class="me-3">
                                                <i class="far fa-clock me-1"></i> <?php echo esc_html($display_time); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($webinar_duration) : ?>
                                                <div>
                                                    <i class="fas fa-hourglass-half me-1"></i> <?php echo esc_html($webinar_duration); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($webinar_presenter) : ?>
                                            <div class="text-muted small mb-3">
                                                <i class="far fa-user me-1"></i> Presented by: <?php echo esc_html($webinar_presenter); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-text small mb-3">
                                            <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                        </div>
                                        
                                        <div class="mt-auto pt-2">
                                            <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else : 
                    ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-5 text-center">
                                    <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h3 class="fs-4 fw-bold text-primary mb-3">No Upcoming Webinars</h3>
                                    <p class="text-muted mb-4">Stay tuned for our upcoming webinar schedule. We're constantly adding new events.</p>
                                    <?php if (current_user_can('manage_options')) : ?>
                                        <div class="alert alert-warning d-inline-block">
                                            <h5>Admin Message</h5>
                                            <p>No upcoming webinars found. Please create webinar posts with future dates using the webinar_date field.</p>
                                            <a href="<?php echo admin_url('post-new.php?post_type=webinar'); ?>" class="btn btn-primary btn-sm">Create Webinar</a>
                                        </div>
                                    <?php endif; ?>
                                    <a href="#recordings" class="btn btn-primary mt-3" data-bs-toggle="tab" data-bs-target="#recordings" role="tab" aria-controls="recordings" aria-selected="false">
                                        <i class="fas fa-play-circle me-2"></i> View Past Recordings
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination for upcoming webinars if needed -->
                <?php if (isset($upcoming_webinars) && $upcoming_webinars->max_num_pages > 1) : ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php 
                                echo paginate_links(array(
                                    'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                                    'format' => '?paged=%#%',
                                    'current' => max(1, get_query_var('paged')),
                                    'total' => $upcoming_webinars->max_num_pages,
                                    'type' => 'list',
                                    'prev_text' => '<i class="fas fa-chevron-left"></i>',
                                    'next_text' => '<i class="fas fa-chevron-right"></i>',
                                ));
                                ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Past Recordings Tab -->
            <div class="tab-pane fade" id="recordings" role="tabpanel" aria-labelledby="recordings-tab">
                <div class="row">
                    <?php
                    // Query past webinars with recordings - using timestamp for more reliable date comparison
                    $args = array(
                        'post_type' => 'webinar',
                        'posts_per_page' => 6,
                        'post_status' => 'publish',
                        'meta_query' => array(
                            'relation' => 'AND',
                            array(
                                'relation' => 'OR',
                                // For webinar_timestamp (most reliable)
                                array(
                                    'key' => 'webinar_timestamp',
                                    'value' => time(),
                                    'compare' => '<',
                                    'type' => 'NUMERIC'
                                ),
                                // For MySQL datetime format
                                array(
                                    'key' => 'webinar_date',
                                    'value' => date('Y-m-d H:i:s'),
                                    'compare' => '<',
                                    'type' => 'DATETIME'
                                ),
                                // For ACF date format (YYYYMMDD)
                                array(
                                    'key' => 'webinar_date',
                                    'value' => date('Ymd'),
                                    'compare' => '<',
                                    'type' => 'NUMERIC'
                                ),
                                // For stm_date field (millisecond timestamp)
                                array(
                                    'key' => 'stm_date',
                                    'value' => time() * 1000, // Convert current time to milliseconds
                                    'compare' => '<',
                                    'type' => 'NUMERIC'
                                )
                            ),
                            array(
                                'key' => 'webinar_recording',
                                'compare' => 'EXISTS'
                            )
                        ),
                        'orderby' => 'meta_value_num',
                        'meta_key' => 'webinar_timestamp',
                        'order' => 'DESC'
                    );
                    
                    $past_webinars = new WP_Query($args);
                    
                    if ($past_webinars->have_posts()) :
                        while ($past_webinars->have_posts()) : $past_webinars->the_post();
                            // Get webinar details
                            $webinar_timestamp = get_post_meta(get_the_ID(), 'webinar_timestamp', true);
                            $webinar_date = get_post_meta(get_the_ID(), 'webinar_date', true);
                            $webinar_recording = get_post_meta(get_the_ID(), 'webinar_recording', true);
                            $webinar_presenter = get_post_meta(get_the_ID(), 'webinar_presenter_name', true);
                            if (empty($webinar_presenter)) {
                                $webinar_presenter = get_post_meta(get_the_ID(), 'webinar_presenter', true);
                            }
                            
                            // Default values if ACF fields aren't being used
                            if (empty($webinar_recording) && function_exists('get_field')) {
                                $webinar_recording = get_field('webinar_recording');
                            }
                            
                            if (empty($webinar_presenter) && function_exists('get_field')) {
                                $webinar_presenter = get_field('webinar_presenter');
                            }
                            
                            // Format date for display - prioritize webinar_timestamp
                            if (!empty($webinar_timestamp)) {
                                $display_date = date_i18n('F j, Y', intval($webinar_timestamp));
                            } elseif ($webinar_date) {
                                if (preg_match('/^\d{8}$/', $webinar_date)) {
                                    // ACF format
                                    $year = substr($webinar_date, 0, 4);
                                    $month = substr($webinar_date, 4, 2);
                                    $day = substr($webinar_date, 6, 2);
                                    $display_date = date_i18n('F j, Y', strtotime("$year-$month-$day"));
                                } else {
                                    // MySQL format or other
                                    $display_date = date_i18n('F j, Y', strtotime($webinar_date));
                                }
                            } else {
                                $display_date = '';
                            }
                            
                            // Get thumbnail or placeholder
                            $thumbnail = '';
                            if (has_post_thumbnail()) {
                                $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                            } else {
                                $thumbnail = 'https://via.placeholder.com/300x200?text=Webinar';
                            }
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-img-top position-relative">
                                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>" class="w-100" style="height: 200px; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-danger">
                                        <i class="fas fa-play-circle me-1"></i> Recording
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h3 class="card-title fs-5 fw-bold mb-2">
                                    <a href="<?php the_permalink(); ?>" class="text-decoration-none text-primary stretched-link"><?php the_title(); ?></a>
                                </h3>
                                
                                <div class="d-flex align-items-center text-muted small mb-3">
                                    <?php if ($display_date) : ?>
                                    <div class="me-3">
                                        <i class="far fa-calendar-alt me-1"></i> <?php echo esc_html($display_date); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($webinar_presenter) : ?>
                                    <div>
                                        <i class="far fa-user me-1"></i> <?php echo esc_html($webinar_presenter); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-text small mb-3">
                                    <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                </div>
                                
                                <div class="mt-auto pt-2">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i> Watch Recording
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else : 
                    ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-5 text-center">
                                    <i class="fas fa-film text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h3 class="fs-4 fw-bold text-primary mb-3">No Recordings Available</h3>
                                    <p class="text-muted mb-4">We haven't uploaded any webinar recordings yet. Check back soon or register for an upcoming webinar.</p>
                                    <a href="#upcoming" class="btn btn-primary" data-bs-toggle="tab" data-bs-target="#upcoming" role="tab" aria-controls="upcoming" aria-selected="false">
                                        <i class="fas fa-calendar-alt me-2"></i> View Upcoming Webinars
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination for past webinars if needed -->
                <?php if (isset($past_webinars) && $past_webinars->max_num_pages > 1) : ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php 
                                echo paginate_links(array(
                                    'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                                    'format' => '?paged=%#%',
                                    'current' => max(1, get_query_var('paged')),
                                    'total' => $past_webinars->max_num_pages,
                                    'type' => 'list',
                                    'prev_text' => '<i class="fas fa-chevron-left"></i>',
                                    'next_text' => '<i class="fas fa-chevron-right"></i>',
                                ));
                                ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (is_user_logged_in() && (function_exists('pms_is_member') && pms_is_member(get_current_user_id()))) : ?>
            <!-- My Webinars Tab (for logged in subscribers) -->
            <div class="tab-pane fade" id="registered" role="tabpanel" aria-labelledby="registered-tab">
                <div class="row">
                    <?php
                    // In a real implementation, you would get webinars the user has registered for
                    // For now, showing a placeholder with upcoming webinars
                    $args = array(
                        'post_type' => 'webinar',
                        'posts_per_page' => 6,
                        'post_status' => 'publish',
                        'meta_query' => array(
                            'relation' => 'OR',
                            // For webinar_timestamp (most reliable)
                            array(
                                'key' => 'webinar_timestamp',
                                'value' => time(),
                                'compare' => '>=',
                                'type' => 'NUMERIC'
                            ),
                            // For MySQL datetime format
                            array(
                                'key' => 'webinar_date',
                                'value' => date('Y-m-d H:i:s'),
                                'compare' => '>=',
                                'type' => 'DATETIME'
                            ),
                            // For ACF date format (YYYYMMDD)
                            array(
                                'key' => 'webinar_date',
                                'value' => date('Ymd'),
                                'compare' => '>=',
                                'type' => 'NUMERIC'
                            )
                        ),
                        'orderby' => 'meta_value_num',
                        'meta_key' => 'webinar_timestamp',
                        'order' => 'ASC'
                    );
                    
                    $registered_webinars = new WP_Query($args);
                    
                    if ($registered_webinars->have_posts()) :
                    ?>
                        <div class="col-12 mb-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> As a subscriber, you have access to all upcoming webinars and recordings.
                            </div>
                        </div>
                        
                        <?php
                        while ($registered_webinars->have_posts()) : $registered_webinars->the_post();
                            // Get webinar details using the timestamp for reliability
                            $webinar_timestamp = get_post_meta(get_the_ID(), 'webinar_timestamp', true);
                            $webinar_date = get_post_meta(get_the_ID(), 'webinar_date', true);
                            $webinar_time = get_post_meta(get_the_ID(), 'webinar_time', true);
                            $stm_time = get_post_meta(get_the_ID(), 'stm_time', true);
                            
                            // Format date for display - prioritize webinar_timestamp
                            if (!empty($webinar_timestamp)) {
                                $display_date = date_i18n('F j, Y', intval($webinar_timestamp));
                                $display_time = date_i18n('g:i A', intval($webinar_timestamp));
                            } elseif ($webinar_date) {
                                // Check if it's in ACF format (YYYYMMDD)
                                if (preg_match('/^\d{8}$/', $webinar_date)) {
                                    // Extract year, month, day
                                    $year = substr($webinar_date, 0, 4);
                                    $month = substr($webinar_date, 4, 2);
                                    $day = substr($webinar_date, 6, 2);
                                    
                                    // Format for display
                                    $display_date = date_i18n('F j, Y', strtotime("$year-$month-$day"));
                                } else {
                                    // Handle regular MySQL datetime format
                                    $display_date = date_i18n('F j, Y', strtotime($webinar_date));
                                }
                                
                                // Get time from various sources
                                if (!empty($stm_time)) {
                                    $display_time = $stm_time;
                                } elseif (strlen($webinar_date) > 10) {
                                    $display_time = date_i18n('g:i A', strtotime($webinar_date));
                                } elseif ($webinar_time) {
                                    $display_time = $webinar_time;
                                } else {
                                    $display_time = '';
                                }
                            } else {
                                $display_date = '';
                                $display_time = '';
                            }
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <h3 class="card-title fs-5 fw-bold mb-2">
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-primary stretched-link"><?php the_title(); ?></a>
                                    </h3>
                                    
                                    <div class="d-flex align-items-center text-muted small mb-3">
                                        <?php if ($display_date) : ?>
                                        <div class="me-3">
                                            <i class="far fa-calendar-alt me-1"></i> <?php echo esc_html($display_date); ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($display_time) : ?>
                                        <div>
                                            <i class="far fa-clock me-1"></i> <?php echo esc_html($display_time); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-text small mb-3">
                                        <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                    </div>
                                    
                                    <div class="mt-auto pt-2">
                                        <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-user-check me-1"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        endwhile;
                        wp_reset_postdata();
                    else : 
                    ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-5 text-center">
                                    <i class="fas fa-user-check text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h3 class="fs-4 fw-bold text-primary mb-3">My Webinars</h3>
                                    <p class="text-muted mb-4">As a subscriber, you have access to all webinars. Check out upcoming webinars or watch past recordings.</p>
                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="#upcoming" class="btn btn-primary" data-bs-toggle="tab" data-bs-target="#upcoming" role="tab" aria-controls="upcoming" aria-selected="false">
                                            <i class="fas fa-calendar-alt me-2"></i> Upcoming Webinars
                                        </a>
                                        <a href="#recordings" class="btn btn-outline-primary" data-bs-toggle="tab" data-bs-target="#recordings" role="tab" aria-controls="recordings" aria-selected="false">
                                            <i class="fas fa-play-circle me-2"></i> Past Recordings
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Topic Filter Section -->
        <div class="mt-5 pt-3">
            <h3 class="text-center mb-4">Browse Webinars by Topic</h3>
            
            <div class="text-center">
                <?php
                $topics = get_terms(array(
                    'taxonomy' => 'topic',
                    'hide_empty' => true,
                ));
                
                if (!empty($topics) && !is_wp_error($topics)) :
                    echo '<div class="d-flex flex-wrap justify-content-center gap-2 mb-4">';
                    foreach ($topics as $topic) {
                        echo '<a href="' . get_term_link($topic) . '" class="btn btn-outline-primary btn-sm mb-2">';
                        echo '<i class="fas fa-tag me-1"></i> ' . $topic->name;
                        echo '</a>';
                    }
                    echo '</div>';
                else:
                    echo '<div class="alert alert-info d-inline-block">No topics available yet. Topics will appear here when webinars are categorized.</div>';
                endif;
                ?>
            </div>
        </div>
        
        <!-- Subscribe CTA Section -->
        <?php if (!is_user_logged_in() || (function_exists('pms_is_member') && !pms_is_member(get_current_user_id()))) : ?>
            <div class="mt-5">
                <div class="card border-0 shadow bg-primary text-white">
                    <div class="card-body p-5 text-center">
                        <h3 class="mb-3">Get Full Access to All Webinars</h3>
                        <p class="lead mb-4">Subscribe today to unlock all webinar recordings and register for upcoming live sessions.</p>
                        <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-light btn-lg">
                            <i class="fas fa-crown me-2"></i> Subscribe Now
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle tab changes via URL hash
    let hash = window.location.hash;
    if(hash) {
        $('#webinarTabs button[data-bs-target="' + hash + '"]').tab('show');
    }
    
    // Update URL hash when tab changes
    $('#webinarTabs button').on('shown.bs.tab', function (e) {
        history.pushState(null, null, $(this).data('bs-target'));
    });
    
    // Function to update tab display based on URL hash
    function handleHashChange() {
        let hash = window.location.hash;
        if(hash) {
            $('#webinarTabs button[data-bs-target="' + hash + '"]').tab('show');
        }
    }
    
    // Handle back/forward button navigation
    window.addEventListener('popstate', handleHashChange);
    
    // Handle links from other tabs
    $('a[data-bs-toggle="tab"]').on('click', function(e) {
        e.preventDefault();
        let target = $(this).attr('data-bs-target');
        $('#webinarTabs button[data-bs-target="' + target + '"]').tab('show');
    });
});
</script>

<?php get_footer(); ?>