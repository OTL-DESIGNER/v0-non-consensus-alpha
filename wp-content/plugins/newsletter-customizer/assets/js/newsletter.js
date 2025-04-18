/**
 * RTS Newsletter Enhancer - Frontend JavaScript
 * Handles interactive elements and layout adjustments for newsletter display
 */

(function($) {
    'use strict';

    // Main newsletter functionality object
    var RTSNewsletter = {
        /**
         * Initialize the newsletter functionality
         */
        init: function() {
            this.setupImageGallery();
            this.setupResponsiveLayout();
            this.setupPrintFunctionality();
            this.setupSocialSharing();
            this.setupReadingProgressBar();
            this.setupScrollToTopButton();
            this.setupLazyLoading();
        },

        /**
         * Setup responsive image gallery functionality
         */
        setupImageGallery: function() {
            // Check if the newsletter has any galleries
            if ($('.newsletter-gallery').length) {
                $('.newsletter-gallery').each(function() {
                    var $gallery = $(this);
                    
                    // Handle gallery image clicks for lightbox effect
                    $gallery.find('img').on('click', function() {
                        var imgSrc = $(this).attr('src');
                        var imgAlt = $(this).attr('alt') || 'Newsletter Image';
                        
                        // Create lightbox overlay
                        var $lightbox = $('<div class="newsletter-lightbox"></div>');
                        var $lightboxContent = $('<div class="newsletter-lightbox-content"></div>');
                        var $lightboxImage = $('<img src="' + imgSrc + '" alt="' + imgAlt + '" />');
                        var $lightboxClose = $('<button class="newsletter-lightbox-close">&times;</button>');
                        var $lightboxCaption = $('<div class="newsletter-lightbox-caption">' + imgAlt + '</div>');
                        
                        // Assemble and append lightbox
                        $lightboxContent.append($lightboxImage, $lightboxCaption, $lightboxClose);
                        $lightbox.append($lightboxContent);
                        $('body').append($lightbox);
                        
                        // Prevent body scrolling
                        $('body').addClass('newsletter-lightbox-open');
                        
                        // Handle close button click
                        $lightboxClose.on('click', function() {
                            $lightbox.remove();
                            $('body').removeClass('newsletter-lightbox-open');
                        });
                        
                        // Also close on clicking outside the image
                        $lightbox.on('click', function(e) {
                            if ($(e.target).hasClass('newsletter-lightbox')) {
                                $lightbox.remove();
                                $('body').removeClass('newsletter-lightbox-open');
                            }
                        });
                        
                        // Handle ESC key to close
                        $(document).on('keydown.newsletter', function(e) {
                            if (e.keyCode === 27) { // ESC key
                                $lightbox.remove();
                                $('body').removeClass('newsletter-lightbox-open');
                                $(document).off('keydown.newsletter');
                            }
                        });
                    });
                });
            }
        },

        /**
         * Setup responsive layout adjustments
         */
        setupResponsiveLayout: function() {
            // Responsive table handling
            $('.newsletter-content table').each(function() {
                if (!$(this).parent().hasClass('table-responsive')) {
                    $(this).wrap('<div class="table-responsive"></div>');
                }
            });
            
            // Handle responsive sidebar
            if ($('.newsletter-layout.with-sidebar').length) {
                $(window).on('resize', function() {
                    if ($(window).width() < 768) {
                        $('.newsletter-main-content').css('padding-right', '0');
                        $('.newsletter-sidebar').css('width', '100%');
                    } else {
                        $('.newsletter-main-content').css('padding-right', '30px');
                        $('.newsletter-sidebar').css('width', '30%');
                    }
                }).trigger('resize');
            }
            
            // Make images responsive
            $('.newsletter-content img').each(function() {
                $(this).addClass('img-fluid');
            });
        },

        /**
         * Setup print functionality
         */
        setupPrintFunctionality: function() {
            // Add print button if not already present
            if ($('.newsletter-content').length && !$('.newsletter-print-button').length) {
                var $printButton = $('<button class="newsletter-print-button">Print Newsletter</button>');
                $('.newsletter-content').prepend($printButton);
                
                // Handle print button click
                $printButton.on('click', function(e) {
                    e.preventDefault();
                    window.print();
                });
            }
        },

        /**
         * Setup social sharing buttons
         */
        setupSocialSharing: function() {
            // Check if social sharing container exists
            if (!$('.newsletter-social-sharing').length) {
                // Create social sharing container
                var $sharingContainer = $('<div class="newsletter-social-sharing"></div>');
                var pageUrl = encodeURIComponent(window.location.href);
                var pageTitle = encodeURIComponent(document.title);
                
                // Add sharing buttons
                var shareButtons = [
                    '<a href="https://www.facebook.com/sharer/sharer.php?u=' + pageUrl + '" target="_blank" class="newsletter-share-facebook">Facebook</a>',
                    '<a href="https://twitter.com/intent/tweet?url=' + pageUrl + '&text=' + pageTitle + '" target="_blank" class="newsletter-share-twitter">Twitter</a>',
                    '<a href="https://www.linkedin.com/shareArticle?mini=true&url=' + pageUrl + '&title=' + pageTitle + '" target="_blank" class="newsletter-share-linkedin">LinkedIn</a>',
                    '<a href="mailto:?subject=' + pageTitle + '&body=Check out this newsletter: ' + pageUrl + '" class="newsletter-share-email">Email</a>'
                ];
                
                $sharingContainer.html('<span>Share: </span>' + shareButtons.join(''));
                $('.newsletter-content').append($sharingContainer);
                
                // Open share links in popup
                $('.newsletter-social-sharing a').on('click', function(e) {
                    if (!$(this).hasClass('newsletter-share-email')) {
                        e.preventDefault();
                        window.open($(this).attr('href'), 'share', 'width=600,height=400,location=no,menubar=no,toolbar=no');
                    }
                });
            }
        },

        /**
         * Setup reading progress bar
         */
        setupReadingProgressBar: function() {
            // Only add progress bar on single newsletter view
            if ($('.newsletter-content').length && !$('.newsletter-progress').length) {
                var $progressBar = $('<div class="newsletter-progress"><div class="newsletter-progress-bar"></div></div>');
                $('body').append($progressBar);
                
                // Update progress bar on scroll
                $(window).on('scroll', function() {
                    var windowHeight = $(window).height();
                    var documentHeight = $(document).height();
                    var scrollTop = $(window).scrollTop();
                    var progress = (scrollTop / (documentHeight - windowHeight)) * 100;
                    
                    $('.newsletter-progress-bar').width(progress + '%');
                    
                    // Show/hide progress bar
                    if (scrollTop > 100) {
                        $('.newsletter-progress').addClass('active');
                    } else {
                        $('.newsletter-progress').removeClass('active');
                    }
                });
            }
        },

        /**
         * Setup scroll to top button
         */
        setupScrollToTopButton: function() {
            // Only add button if not already present
            if (!$('.newsletter-scroll-top').length) {
                var $scrollButton = $('<button class="newsletter-scroll-top" aria-label="Scroll to top"><span>↑</span></button>');
                $('body').append($scrollButton);
                
                // Show/hide scroll button
                $(window).on('scroll', function() {
                    if ($(window).scrollTop() > 300) {
                        $scrollButton.addClass('visible');
                    } else {
                        $scrollButton.removeClass('visible');
                    }
                });
                
                // Handle scroll button click
                $scrollButton.on('click', function() {
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500);
                });
            }
        },

        /**
         * Setup lazy loading for images
         */
        setupLazyLoading: function() {
            // Check for browser support of IntersectionObserver
            if ('IntersectionObserver' in window) {
                var lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var lazyImage = entry.target;
                            lazyImage.src = lazyImage.dataset.src;
                            
                            if (lazyImage.dataset.srcset) {
                                lazyImage.srcset = lazyImage.dataset.srcset;
                            }
                            
                            lazyImage.classList.remove('lazy');
                            lazyImageObserver.unobserve(lazyImage);
                        }
                    });
                });
                
                // Find all lazy load images and observe them
                document.querySelectorAll('img.lazy').forEach(function(lazyImage) {
                    lazyImageObserver.observe(lazyImage);
                });
            } else {
                // Fallback for browsers that don't support IntersectionObserver
                $('.newsletter-content img.lazy').each(function() {
                    $(this).attr('src', $(this).data('src'));
                    
                    if ($(this).data('srcset')) {
                        $(this).attr('srcset', $(this).data('srcset'));
                    }
                    
                    $(this).removeClass('lazy');
                });
            }
        },

        /**
         * Helper function to check if element is in viewport
         */
        isInViewport: function(element) {
            var rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        RTSNewsletter.init();
        
        // Additional initialization for specific templates
        var templateClass = $('.newsletter-content').attr('class');
        
        if (templateClass && templateClass.indexOf('template-modern') !== -1) {
            // Modern template specific enhancements
            $('.newsletter-content blockquote').each(function() {
                $(this).prepend('<span class="newsletter-quote-mark">"</span>');
            });
            
            // Add smooth scrolling for anchor links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                var target = $(this.hash);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 50
                    }, 500);
                }
            });
        } else if (templateClass && templateClass.indexOf('template-minimal') !== -1) {
            // Minimal template specific enhancements
            $('.newsletter-content h2, .newsletter-content h3').each(function() {
                var $heading = $(this);
                var headingId = $heading.text().toLowerCase().replace(/[^a-z0-9]+/g, '-');
                $heading.attr('id', headingId);
                
                // Add heading anchors
                $heading.append('<a class="heading-anchor" href="#' + headingId + '">#</a>');
            });
        }
        
        // Check if there's a PDF version available
        if ($('.newsletter-pdf-link a').length) {
            // Track PDF downloads with analytics if available
            $('.newsletter-pdf-link a').on('click', function() {
                if (typeof ga !== 'undefined') {
                    ga('send', 'event', 'Newsletter', 'PDF Download', $(this).attr('href'));
                }
            });
        }
    });

})(jQuery);