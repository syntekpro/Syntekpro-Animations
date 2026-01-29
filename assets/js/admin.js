/**
 * Syntekpro Animations - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Initialize admin features
        initTabNavigation();
        initLicenseValidation();
        initAnimationPreviews();
        initCopyShortcodes();
        initPluginToggle();
        initProFeatureNotices();
        
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
     * License Validation
     */
    function initLicenseValidation() {
        const $licenseInput = $('input[name="syntekpro_anim_license_key"]');
        const $submitButton = $('#submit');
        
        if ($licenseInput.length === 0) {
            return;
        }
        
        // Add validation on input
        $licenseInput.on('input', function() {
            const value = $(this).val().trim();
            const isValid = validateLicenseFormat(value);
            
            if (isValid) {
                $(this).css('border-color', '#46b450');
            } else if (value.length > 0) {
                $(this).css('border-color', '#d63638');
            } else {
                $(this).css('border-color', '');
            }
        });
        
        // Add loading state on submit
        $submitButton.on('click', function() {
            if ($licenseInput.val().trim().length > 0) {
                $(this).prop('disabled', true);
                $(this).after('<span class="sp-loading" style="margin-left: 10px;"></span>');
            }
        });
    }
    
    /**
     * Validate license key format
     */
    function validateLicenseFormat(key) {
        // Format: XXXX-XXXX-XXXX-XXXX
        const pattern = /^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/;
        return pattern.test(key.toUpperCase());
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
     * Pro Feature Notices
     */
    function initProFeatureNotices() {
        // Add lock icons to disabled pro features
        $('input[disabled][name*="splittext"], input[disabled][name*="morphsvg"], input[disabled][name*="drawsvg"]').each(function() {
            const $input = $(this);
            const $label = $input.closest('td').find('label');
            
            if (!$label.find('.sp-pro-badge').length) {
                $label.append(' <span class="sp-pro-badge" style="background:#d63638;color:white;padding:2px 8px;border-radius:3px;font-size:11px;margin-left:5px;">PRO</span>');
            }
        });
        
        // Add upgrade prompt on disabled checkbox click
        $('input[disabled]').on('click', function(e) {
            if ($(this).attr('name') && $(this).attr('name').includes('syntekpro_anim_load_')) {
                e.preventDefault();
                
                const featureName = $(this).attr('name').replace('syntekpro_anim_load_', '').replace(/_/g, ' ');
                
                showUpgradeModal(featureName);
            }
        });
    }
    
    /**
     * Show upgrade modal
     */
    function showUpgradeModal(featureName) {
        const modalHTML = `
            <div class="sp-upgrade-modal" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:999999;display:flex;align-items:center;justify-content:center;">
                <div style="background:white;padding:40px;border-radius:8px;max-width:500px;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.3);">
                    <h2 style="margin-top:0;color:#1d2327;">🔒 Premium Feature</h2>
                    <p style="font-size:16px;color:#646970;margin:20px 0;">
                        <strong>${capitalizeWords(featureName)}</strong> is a premium feature available in Syntekpro Animations Pro.
                    </p>
                    <p style="margin:20px 0;">Unlock this and many more powerful features:</p>
                    <ul style="text-align:left;margin:20px 0;padding-left:40px;color:#646970;">
                        <li>SplitText - Animate text by characters</li>
                        <li>MorphSVG - Morph SVG shapes</li>
                        <li>DrawSVG - Animate SVG strokes</li>
                        <li>ScrollSmoother - Buttery smooth scrolling</li>
                        <li>And 5+ more premium plugins!</li>
                    </ul>
                    <div style="margin-top:30px;">
                        <a href="https://syntekpro.com/animations-pro" target="_blank" class="button button-primary button-large" style="margin-right:10px;">Upgrade to Pro</a>
                        <button class="button button-large sp-close-modal">Maybe Later</button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        
        $('.sp-close-modal, .sp-upgrade-modal').on('click', function(e) {
            if (e.target === this) {
                $('.sp-upgrade-modal').fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }
    
    /**
     * Capitalize words
     */
    function capitalizeWords(str) {
        return str.replace(/\b\w/g, function(char) {
            return char.toUpperCase();
        });
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