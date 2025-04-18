/**
 * RTS Capital Management main JavaScript file
 */
(function($) {
    'use strict';
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Add .active class to current menu item
    $('#primary-menu .current-menu-item, #primary-menu .current-menu-parent').addClass('active');
    
    // Add special class to dropdown items
    $('.dropdown-menu .nav-link').removeClass('nav-link').addClass('dropdown-item');
    
    // Handle scroll behavior
    $(window).scroll(function() {
        var scroll = $(window).scrollTop();
        if (scroll >= 100) {
            $('.site-header').addClass('navbar-scrolled');
        } else {
            $('.site-header').removeClass('navbar-scrolled');
        }
        
        // Performance chart animation
        var chartSection = $('.performance-section');
        if (chartSection.length) {
            var chartPosition = chartSection.offset().top;
            var windowPosition = $(window).scrollTop() + $(window).height();
            
            if (windowPosition > chartPosition + 200) {
                $('.performance-chart').addClass('animated');
            }
        }
    });
    
    // Back to top button
    var btnTop = $('#back-to-top');
    
    $(window).scroll(function() {
        if ($(window).scrollTop() > 300) {
            btnTop.addClass('show');
        } else {
            btnTop.removeClass('show');
        }
    });
    
    btnTop.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop:0}, '300');
    });
    
    // Form validation with Bootstrap
    if ($('.needs-validation').length) {
        // Fetch all forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation');
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }
    
    // HOME PAGE SPECIFIC CODE
    if ($('.hero-section').length) {
        // Smooth scrolling for anchor links
        $('a[href*="#"]:not([href="#"])').click(function() {
            if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 800);
                    return false;
                }
            }
        });
        
        // Animated counter for statistics
        $('.counter-value').each(function() {
            $(this).prop('Counter', 0).animate({
                Counter: $(this).text()
            }, {
                duration: 2000,
                easing: 'swing',
                step: function(now) {
                    $(this).text(Math.ceil(now));
                }
            });
        });
        
        // Newsletter form submission
        $('.newsletter-form').submit(function(e) {
            e.preventDefault();
            
            var email = $(this).find('input[type="email"]').val();
            
            // Add your AJAX form submission here
            // For now, just show a success message
            $(this).html('<div class="alert alert-success mb-0">Thank you for subscribing! Check your email to confirm.</div>');
        });
        
        // Testimonial carousel
        var testimonialsSlider = $('.testimonials-slider');
        if (testimonialsSlider.length) {
            testimonialsSlider.slick({
                dots: true,
                arrows: false,
                infinite: true,
                speed: 500,
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 5000,
                responsive: [
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 2,
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1,
                        }
                    }
                ]
            });
        }
    }
    
    // Mobile menu toggle
    $('.mobile-menu-toggle').on('click', function() {
        $('.site-header').toggleClass('menu-open');
        $(this).toggleClass('active');
    });
    
})(jQuery);