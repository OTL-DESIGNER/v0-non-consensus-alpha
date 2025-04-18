/**
 * Non-Consensus Alpha - Optimized Webinar Countdown
 * Uses RAF instead of setInterval for smoother performance
 */
(function($) {
    'use strict';
    
    // Store elements and state to avoid repeated DOM lookups
    var countdownElements = [];
    var rafId = null;
    var lastTime = 0;
    
    // Initialize countdown elements once at the start
    function initCountdowns() {
        $('.countdown').each(function(index) {
            var $countdown = $(this);
            // Get timestamp and ensure it's treated consistently
            var rawTimestamp = parseInt($countdown.data('time'));
            
            // Ensure the timestamp is in milliseconds (some systems provide seconds)
            var targetTime = rawTimestamp > 9999999999 ? rawTimestamp : rawTimestamp * 1000;
            
            console.log('Countdown initialized with timestamp:', rawTimestamp, 'Target time:', new Date(targetTime).toISOString());
            
            // We'll keep the existing structure if it's already properly formatted
            // This way the HTML structure is consistent whether created by server or JS
            if (!$countdown.find('.countdown-row').length) {
                $countdown.html(
                    '<div class="row justify-content-center countdown-row">' +
                        '<div class="col-3 countdown-item">' +
                            '<div class="countdown-value bg-primary text-white fw-bold fs-4 p-2 rounded" id="countdown-days-' + index + '">00</div>' +
                            '<div class="countdown-label small mt-1">Days</div>' +
                        '</div>' +
                        '<div class="col-3 countdown-item">' +
                            '<div class="countdown-value bg-primary text-white fw-bold fs-4 p-2 rounded" id="countdown-hours-' + index + '">00</div>' +
                            '<div class="countdown-label small mt-1">Hours</div>' +
                        '</div>' +
                        '<div class="col-3 countdown-item">' +
                            '<div class="countdown-value bg-primary text-white fw-bold fs-4 p-2 rounded" id="countdown-minutes-' + index + '">00</div>' +
                            '<div class="countdown-label small mt-1">Mins</div>' +
                        '</div>' +
                        '<div class="col-3 countdown-item">' +
                            '<div class="countdown-value bg-primary text-white fw-bold fs-4 p-2 rounded" id="countdown-seconds-' + index + '">00</div>' +
                            '<div class="countdown-label small mt-1">Secs</div>' +
                        '</div>' +
                    '</div>'
                );
            } else {
                // If structure exists, just make sure we have IDs for direct DOM access
                $countdown.find('.countdown-value:eq(0)').attr('id', 'countdown-days-' + index);
                $countdown.find('.countdown-value:eq(1)').attr('id', 'countdown-hours-' + index);
                $countdown.find('.countdown-value:eq(2)').attr('id', 'countdown-minutes-' + index);
                $countdown.find('.countdown-value:eq(3)').attr('id', 'countdown-seconds-' + index);
            }
            
            // Store references to DOM elements to avoid jQuery lookups in the animation loop
            countdownElements.push({
                countdown: $countdown,
                container: $countdown.closest('.webinar-countdown-container'),
                targetTime: targetTime,
                daysElement: document.getElementById('countdown-days-' + index),
                hoursElement: document.getElementById('countdown-hours-' + index),
                minutesElement: document.getElementById('countdown-minutes-' + index),
                secondsElement: document.getElementById('countdown-seconds-' + index),
                liveMode: false
            });
        });
    }
    
    // Update countdown - only runs when browser is ready to render a frame
    function updateCountdown(timestamp) {
        // Throttle updates to once per second
        if (timestamp - lastTime < 1000) {
            rafId = requestAnimationFrame(updateCountdown);
            return;
        }
        
        lastTime = timestamp;
        
        // Get current time in milliseconds since epoch
        var now = Date.now();
        
        for (var i = 0; i < countdownElements.length; i++) {
            var elem = countdownElements[i];
            // Simple subtraction of timestamps - no timezone conversion
            var difference = elem.targetTime - now;
            
            if (difference <= 0) {
                // Webinar has started - only update DOM if not already in live mode
                if (!elem.liveMode) {
                    elem.countdown.html('<div class="live-now"><i class="fas fa-circle text-danger me-2 blink"></i> Live Now!</div>');
                    elem.liveMode = true;
                    
                    // If we have a live container, show it
                    var $liveContainer = $('#webinar-live-container');
                    if ($liveContainer.length) {
                        $liveContainer.removeClass('d-none');
                        $('#webinar-countdown-container').addClass('d-none');
                    }
                }
            } else {
                // Calculate time units
                var days = Math.floor(difference / (1000 * 60 * 60 * 24));
                var hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((difference % (1000 * 60)) / 1000);
                
                // Format values
                var daysText = days < 10 ? '0' + days : days;
                var hoursText = hours < 10 ? '0' + hours : hours;
                var minutesText = minutes < 10 ? '0' + minutes : minutes;
                var secondsText = seconds < 10 ? '0' + seconds : seconds;
                
                // Only update the DOM if values have changed - using textContent instead of innerHTML
                if (elem.daysElement.textContent !== daysText) {
                    elem.daysElement.textContent = daysText;
                }
                if (elem.hoursElement.textContent !== hoursText) {
                    elem.hoursElement.textContent = hoursText;
                }
                if (elem.minutesElement.textContent !== minutesText) {
                    elem.minutesElement.textContent = minutesText;
                }
                if (elem.secondsElement.textContent !== secondsText) {
                    elem.secondsElement.textContent = secondsText;
                }
                
                // Add classes for styling based on time remaining (only when needed)
                if (difference <= 86400000) { // 24 hours
                    if (!elem.container.hasClass('countdown-urgent')) {
                        elem.container.addClass('countdown-urgent').removeClass('countdown-warning');
                    }
                } else if (difference <= 259200000) { // 3 days
                    if (!elem.container.hasClass('countdown-warning')) {
                        elem.container.addClass('countdown-warning').removeClass('countdown-urgent');
                    }
                }
            }
        }
        
        // Schedule next update
        rafId = requestAnimationFrame(updateCountdown);
    }
    
    // Initialize and start countdown if needed
    function startCountdown() {
        if ($('.countdown').length) {
            // Initialize only once
            if (countdownElements.length === 0) {
                initCountdowns();
            }
            
            // Start animation frame if not already running
            if (!rafId) {
                rafId = requestAnimationFrame(updateCountdown);
            }
        }
    }
    
    // Cleanup on page unload
    function stopCountdown() {
        if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
    }
    
    // Start the countdown when document is ready
    $(document).ready(function() {
        startCountdown();
        
        // Registration button animation
        $('.btn-register').hover(
            function() { $(this).addClass('pulse'); },
            function() { $(this).removeClass('pulse'); }
        );
        
        // Cleanup on page unload
        $(window).on('unload', stopCountdown);
    });
    
})(jQuery);