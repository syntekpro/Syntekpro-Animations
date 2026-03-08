/**
 * Syntekpro Animations - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Initialize admin features
        initTabNavigation();
        initAnimationPreviews();
        initCopyShortcodes();
        initPluginToggle();
        
    });
    
    /**
     * Tab Navigation
     */
    function initTabNavigation() {
        $('.nav-tab').on('click', function(e) {
            const url = $(this).attr('href');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
        });
    }
    
    /**
     * Animation Previews
     */
    function initAnimationPreviews() {
        $('.sp-animation-preview').on('click', function() {
            const $element = $(this);
            const animation = $element.data('animation') || 'fadeIn';
            
            // Reset element
            $element.css({
                opacity: 1,
                transform: 'none'
            });
            
            // Trigger animation based on type
            setTimeout(function() {
                switch(animation) {
                    case 'fadeIn':
                        animateFadeIn($element);
                        break;
                    case 'slideLeft':
                        animateSlideLeft($element);
                        break;
                    case 'scaleUp':
                        animateScaleUp($element);
                        break;
                    case 'rotateIn':
                        animateRotateIn($element);
                        break;
                    default:
                        animateFadeIn($element);
                }
            }, 100);
        });
    }
    
    // Animation functions
    function animateFadeIn($el) {
        $el.css('opacity', 0);
        $el.animate({ opacity: 1 }, 800);
    }
    
    function animateSlideLeft($el) {
        $el.css({ opacity: 0, transform: 'translateX(100px)' });
        $el.animate({ opacity: 1 }, 800);
        setTimeout(() => $el.css('transform', 'translateX(0)'), 50);
    }
    
    function animateScaleUp($el) {
        $el.css({ opacity: 0, transform: 'scale(0)' });
        $el.animate({ opacity: 1 }, 800);
        setTimeout(() => $el.css('transform', 'scale(1)'), 50);
    }
    
    function animateRotateIn($el) {
        $el.css({ opacity: 0, transform: 'rotate(-180deg)' });
        $el.animate({ opacity: 1 }, 800);
        setTimeout(() => $el.css('transform', 'rotate(0deg)'), 50);
    }
    
    /**
     * Copy Shortcode to Clipboard
     */
    function initCopyShortcodes() {
        // Add copy buttons to code blocks
        $('table code').each(function() {
            const $code = $(this);
            const code = $code.text();
            
            const $copyBtn = $('<button class="button button-small" style="margin-left: 10px;">Copy</button>');
            
            $copyBtn.on('click', function(e) {
                e.preventDefault();
                copyToClipboard(code);
                
                // Show feedback
                $(this).text('Copied!').css('color', '#46b450');
                setTimeout(() => {
                    $(this).text('Copy').css('color', '');
                }, 2000);
            });
            
            $code.after($copyBtn);
        });
    }
    
    /**
     * Copy text to clipboard
     */
    function copyToClipboard(text) {
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
    }
    
    /**
     * Plugin Toggle Dependencies
     */
    function initPluginToggle() {
        // GSAP Core dependency
        const $gsapCore = $('input[name="syntekpro_anim_load_gsap"]');
        const $allPlugins = $('input[name^="syntekpro_anim_load_"]').not($gsapCore);
        
        $gsapCore.on('change', function() {
            if (!$(this).is(':checked')) {
                const confirmed = confirm('Disabling GSAP Core will disable all animations. Are you sure?');
                if (confirmed) {
                    $allPlugins.prop('checked', false).prop('disabled', true);
                } else {
                    $(this).prop('checked', true);
                }
            } else {
                $allPlugins.prop('disabled', false);
            }
        });
        
        // Initial state
        if (!$gsapCore.is(':checked')) {
            $allPlugins.prop('disabled', true);
        }
    }
    
    /**
     * Settings save notification
     */
    $(document).on('submit', 'form', function() {
        const $form = $(this);
        const $submitBtn = $form.find('#submit');
        
        if ($submitBtn.length) {
            $submitBtn.prop('disabled', true).val('Saving...');
        }
    });
    
    /**
     * Add helpful tooltips
     */
    $('.form-table .description').each(function() {
        const $description = $(this);
        const $th = $description.closest('tr').find('th');
        
        if ($th.length && !$th.find('.dashicons-info').length) {
            $th.append(' <span class="dashicons dashicons-info" style="color:#2271b1;cursor:help;" title="' + $description.text() + '"></span>');
        }
    });
    
})(jQuery);