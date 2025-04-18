<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'rts-capital'); ?></a>

    <!-- Top bar with contact info -->
    <div class="top-bar bg-dark text-white py-2 d-none d-lg-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="top-contact">
                        <span class="me-3"><i class="fas fa-envelope me-1"></i> info@nonconsensusalpha.com</span>
                        <span><i class="fas fa-phone-alt me-1"></i> (555) 123-4567</span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="top-socials">
                        <a href="#" class="text-white me-2" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <header id="masthead" class="site-header">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (has_custom_logo()) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <span class="site-title"><?php bloginfo('name'); ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- Off-canvas toggler for mobile -->
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavOffcanvas" aria-controls="mobileNavOffcanvas" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Desktop navigation -->
                <div class="collapse navbar-collapse" id="primaryNavigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'container'      => false,
                        'menu_class'     => 'navbar-nav me-auto mb-2 mb-lg-0',
                        'fallback_cb'    => '__return_false',
                        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'depth'          => 2,
                        'walker'         => new Bootstrap_5_Nav_Walker(),
                    ));
                    ?>
                    
                    <?php if (is_user_logged_in()) : ?>
                        <div class="nav-buttons">
                            <a href="<?php echo esc_url(site_url('/member-dashboard/')); ?>" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-user-circle me-1"></i> My Account
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="nav-buttons">
                            <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-sm btn-outline-light me-2">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                            <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-sm btn-light">
                                <i class="fas fa-user-plus me-1"></i> Sign Up
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header><!-- #masthead -->

    <!-- Off-canvas Mobile Menu -->
    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileNavOffcanvas" aria-labelledby="mobileNavOffcanvasLabel">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title" id="mobileNavOffcanvasLabel">
                <?php bloginfo('name'); ?>
            </h5>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-menu-container mb-4">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_id'        => 'mobile-menu',
                    'container'      => false,
                    'menu_class'     => 'mobile-nav list-unstyled',
                    'fallback_cb'    => '__return_false',
                    'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'depth'          => 2,
                    'walker'         => new Bootstrap_5_Mobile_Nav_Walker(),
                ));
                ?>
            </div>
            
            <div class="mobile-contact-info mb-4">
                <h6 class="text-uppercase fw-bold mb-3 text-primary">Contact Us</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i> info@nonconsensusalpha.com</li>
                    <li class="mb-2"><i class="fas fa-phone-alt me-2 text-primary"></i> (555) 123-4567</li>
                </ul>
            </div>
            
            <div class="mobile-social-links">
                <h6 class="text-uppercase fw-bold mb-3 text-primary">Follow Us</h6>
                <div class="d-flex">
                    <a href="#" class="text-white me-3" aria-label="Twitter"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white me-3" aria-label="LinkedIn"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    <a href="#" class="text-white" aria-label="YouTube"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-top border-secondary">
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(site_url('/member-dashboard/')); ?>" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-user-circle me-2"></i> My Account
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </a>
                    <a href="<?php echo esc_url(site_url('/register/')); ?>" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-2"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="content" class="site-content">