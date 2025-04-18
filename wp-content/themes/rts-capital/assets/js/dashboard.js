/**
 * RTS Capital Management - Dashboard JavaScript
 */
(function($) {
    'use strict';
    
    // Initialize charts if they exist on the page
    if ($('#newsletterStatsChart').length) {
        var ctx = document.getElementById('newsletterStatsChart').getContext('2d');
        var newsletterStatsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Newsletter Views',
                    data: [12, 19, 14, 15, 22, 25],
                    backgroundColor: 'rgba(26, 188, 156, 0.2)',
                    borderColor: 'rgba(26, 188, 156, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    // Tab functionality for dashboard sections
    $('.dashboard-tabs .nav-link').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Notification handling
    $('.notification-dismiss').on('click', function() {
        $(this).closest('.alert').fadeOut();
    });
    
    /**
     * Add accordion functionality to dashboard sections
     */
    $('.dashboard-section h2').on('click', function() {
        const $heading = $(this);
        const $section = $heading.closest('.dashboard-section');
        
        // Only collapse if it has the collapsible class
        if ($section.hasClass('collapsible')) {
            const $content = $section.find('.dashboard-section-content');
            $content.slideToggle(300);
            $section.toggleClass('collapsed');
            
            // Update toggle icon if present
            const $icon = $heading.find('.toggle-icon');
            if ($icon.length) {
                $icon.toggleClass('fa-chevron-down fa-chevron-up');
            }
        }
    });
    
    /**
     * Make subscription cards clickable
     */
    $('.subscription-item').on('click', function(e) {
        // Don't trigger if clicking on a button/link inside
        if ($(e.target).closest('a').length === 0) {
            const $manageLink = $(this).find('.subscription-actions a').first();
            if ($manageLink.length) {
                window.location.href = $manageLink.attr('href');
            }
        }
    });
    
    // Subscription toggle details
    $('.subscription-toggle').on('click', function() {
        $(this).closest('.subscription-item').find('.subscription-details').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
    });
    
    /**
     * Add smooth scrolling for anchor links
     */
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        
        if (target.length) {
            e.preventDefault();
            
            $('html, body').animate({
                scrollTop: target.offset().top - 100 // Offset for fixed header
            }, 500);
        }
    });
    
    /**
     * Handle responsive behavior
     */
    function handleResponsiveLayout() {
        const windowWidth = $(window).width();
        
        if (windowWidth < 768) {
            // On mobile, stack action buttons
            $('.content-actions, .webinar-actions, .recording-actions').addClass('stacked');
        } else {
            $('.content-actions, .webinar-actions, .recording-actions').removeClass('stacked');
        }
    }
    
    // Run on load and resize
    handleResponsiveLayout();
    $(window).on('resize', handleResponsiveLayout);
    
    // Upgrade option hover effect
    $('.upgrade-option').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
    
})(jQuery);