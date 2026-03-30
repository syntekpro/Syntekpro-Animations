/**
 * Syntekpro Animations - Admin Preview & Interactive Controls
 */

(function($) {
    'use strict';

    // Animation Preview Manager
    const AnimationPreview = {
        init: function() {
            this.removeAllPreviewAreas(); // Clean first
            this.setupPreviewBoxes();
            this.setupAnimationBuilder();
            this.setupLivePreview();
            this.setupTimelineBuilder();
            this.setupCodeGenerator();
        },

        removeAllPreviewAreas: function() {
            // Aggressively remove ALL preview areas immediately
            $('.syntekplus-preset-card .preview-area').remove();
            $('.syntekplus-preset-card .preview-btn').remove();
            $('.syntekplus-preset-card .preview-box').not('.preset-preview-box').remove();
        },

        setupPreviewBoxes: function() {
            // Use the built-in preview tile on each preset card
            $('.syntekplus-preset-card').each(function() {
                const $card = $(this);
                const animationType = $card.data('preset-key') || $card.data('animation');
                const $previewBox = $card.find('.preset-preview-element');

                // Remove any legacy injected preview UI (extra button/area) to avoid duplicate tiles
                $card.find('.preview-area, .preview-btn').remove();
                $card.find('.preview-box').not('.preset-preview-box').remove();

                if (!$previewBox.length) {
                    return;
                }

                $previewBox.off('click.preview').on('click.preview', function(e) {
                    e.stopPropagation();
                    AnimationPreview.playAnimation($previewBox, animationType);
                });
            });
        },

        playAnimation: function($element, animationType) {
            // Reset element
            gsap.set($element[0], { clearProps: 'all' });
            
            // Get animation preset data
            const presets = {
                fadeIn: { from: { opacity: 0 }, to: { opacity: 1 } },
                fadeInUp: { from: { opacity: 0, y: 50 }, to: { opacity: 1, y: 0 } },
                fadeInDown: { from: { opacity: 0, y: -50 }, to: { opacity: 1, y: 0 } },
                slideLeft: { from: { x: 100 }, to: { x: 0 } },
                slideRight: { from: { x: -100 }, to: { x: 0 } },
                scaleUp: { from: { scale: 0, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                rotateIn: { from: { rotation: -180, opacity: 0 }, to: { rotation: 0, opacity: 1 } },
                zoomIn: { from: { scale: 0.5, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                bounceIn: { from: { scale: 0 }, to: { scale: 1, ease: 'back.out(1.7)' } },
                pulse: { from: { scale: 1 }, to: { scale: 1.2, repeat: 1, yoyo: true } }
            };

            const animation = presets[animationType] || presets.fadeIn;
            
            // Set initial state
            gsap.set($element[0], animation.from);
            
            // Animate
            gsap.to($element[0], {
                ...animation.to,
                duration: 1,
                onComplete: function() {
                    setTimeout(() => {
                        gsap.set($element[0], { clearProps: 'all' });
                    }, 500);
                }
            });
        },

        setupAnimationBuilder: function() {
            if (!$('#animation-builder').length) return;

            const builder = $('#animation-builder');
            const preview = builder.find('#builder-preview-box');
            
            // Update preview on any change
            builder.find('input, select').on('change input', function() {
                AnimationPreview.updateBuilderPreview();
            });

            // Play button
            $('#play-builder-animation').on('click', function() {
                AnimationPreview.updateBuilderPreview();
            });
        },

        updateBuilderPreview: function() {
            const preview = $('#builder-preview-box');
            if (!preview.length) return;

            // Get values
            const animType = $('#builder-animation-type').val() || 'fadeIn';
            const duration = parseFloat($('#builder-duration').val()) || 1;
            const delay = parseFloat($('#builder-delay').val()) || 0;
            const ease = $('#builder-ease').val() || 'power2.out';
            const stagger = parseFloat($('#builder-stagger').val()) || 0;

            // Reset
            gsap.set(preview[0], { clearProps: 'all' });

            // Apply animation with custom settings
            setTimeout(() => {
                this.playCustomAnimation(preview, {
                    type: animType,
                    duration: duration,
                    delay: delay,
                    ease: ease
                });
            }, 100);

            // Update code output
            this.updateCodeOutput({
                type: animType,
                duration: duration,
                delay: delay,
                ease: ease,
                stagger: stagger
            });
        },

        playCustomAnimation: function($element, settings) {
            const animations = {
                fadeIn: { from: { opacity: 0 }, to: { opacity: 1 } },
                fadeInUp: { from: { opacity: 0, y: 50 }, to: { opacity: 1, y: 0 } },
                slideLeft: { from: { x: 100 }, to: { x: 0 } },
                scaleUp: { from: { scale: 0 }, to: { scale: 1 } },
                rotateIn: { from: { rotation: -180, opacity: 0 }, to: { rotation: 0, opacity: 1 } }
            };

            const anim = animations[settings.type] || animations.fadeIn;
            
            gsap.set($element[0], anim.from);
            gsap.to($element[0], {
                ...anim.to,
                duration: settings.duration,
                delay: settings.delay,
                ease: settings.ease
            });
        },

        setupLivePreview: function() {
            // Create live preview panel if it doesn't exist
            if (!$('#live-preview-panel').length) {
                this.createLivePreviewPanel();
            }

            // Toggle live preview
            $('#toggle-live-preview').on('click', function() {
                $('#live-preview-panel').toggleClass('active');
            });
        },

        createLivePreviewPanel: function() {
            const panel = $('<div id="live-preview-panel" class="syntekpro-live-preview"><div class="preview-header"><h3>Live Preview</h3><button class="close-preview">×</button></div><div class="preview-content"><div class="preview-element">Preview Element</div></div><div class="preview-controls"><button class="replay-btn">🔄 Replay</button></div></div>');
            
            $('body').append(panel);

            // Close button
            panel.find('.close-preview').on('click', function() {
                panel.removeClass('active');
            });

            // Replay button
            panel.find('.replay-btn').on('click', function() {
                AnimationPreview.replayCurrentAnimation();
            });
        },

        setupTimelineBuilder: function() {
            if (!$('#timeline-builder').length) return;

            // Initialize sortable timeline
            if (typeof $.fn.sortable !== 'undefined') {
                $('#timeline-steps').sortable({
                    handle: '.timeline-handle',
                    update: function() {
                        AnimationPreview.updateTimelinePreview();
                    }
                });
            }

            // Add step button
            $('#add-timeline-step').on('click', function() {
                AnimationPreview.addTimelineStep();
            });

            // Remove step
            $(document).on('click', '.remove-timeline-step', function() {
                $(this).closest('.timeline-step').remove();
                AnimationPreview.updateTimelinePreview();
            });

            // Play timeline
            $('#play-timeline').on('click', function() {
                AnimationPreview.playTimeline();
            });
        },

        addTimelineStep: function() {
            const stepCount = $('#timeline-steps .timeline-step').length + 1;
            const step = $(`
                <div class="timeline-step" data-step="${stepCount}">
                    <div class="timeline-handle">⋮⋮</div>
                    <div class="step-content">
                        <h4>Step ${stepCount}</h4>
                        <label>Animation: <select class="step-animation">
                            <option value="fadeIn">Fade In</option>
                            <option value="slideLeft">Slide Left</option>
                            <option value="scaleUp">Scale Up</option>
                            <option value="rotateIn">Rotate In</option>
                        </select></label>
                        <label>Duration: <input type="number" class="step-duration" value="1" step="0.1" min="0"></label>
                        <label>Delay: <input type="number" class="step-delay" value="0" step="0.1" min="0"></label>
                        <button class="remove-timeline-step">Remove</button>
                    </div>
                </div>
            `);

            $('#timeline-steps').append(step);
        },

        playTimeline: function() {
            const tl = gsap.timeline();
            const preview = $('#timeline-preview-box');

            gsap.set(preview[0], { clearProps: 'all' });

            $('#timeline-steps .timeline-step').each(function(index) {
                const $step = $(this);
                const animation = $step.find('.step-animation').val();
                const duration = parseFloat($step.find('.step-duration').val()) || 1;
                const delay = parseFloat($step.find('.step-delay').val()) || 0;

                const anims = {
                    fadeIn: { opacity: 1 },
                    slideLeft: { x: 0 },
                    scaleUp: { scale: 1 },
                    rotateIn: { rotation: 0, opacity: 1 }
                };

                if (index === 0) {
                    gsap.set(preview[0], { opacity: 0, x: 100, scale: 0.5, rotation: -180 });
                }

                tl.to(preview[0], {
                    ...anims[animation],
                    duration: duration,
                    delay: delay
                });
            });
        },

        setupCodeGenerator: function() {
            // Copy shortcode button
            $(document).on('click', '.copy-shortcode', function() {
                const code = $(this).closest('.code-output').find('code').text();
                AnimationPreview.copyToClipboard(code);
                
                const $btn = $(this);
                $btn.text('✓ Copied!');
                setTimeout(() => $btn.text('Copy'), 2000);
            });
        },

        updateCodeOutput: function(settings) {
            const shortcode = `[sp_animate type="${settings.type}" duration="${settings.duration}" delay="${settings.delay}" ease="${settings.ease}"]Your Content[/sp_animate]`;
            
            $('#generated-shortcode').text(shortcode);
        },

        copyToClipboard: function(text) {
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
        },

        replayCurrentAnimation: function() {
            const $preview = $('#live-preview-panel .preview-element');
            this.playAnimation($preview, 'fadeIn');
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if (typeof gsap !== 'undefined') {
            AnimationPreview.init();
            
            // Set up a mutation observer to continuously remove preview-areas if they appear
            const observer = new MutationObserver(function() {
                $('.syntekplus-preset-card .preview-area, .syntekplus-preset-card .preview-btn').remove();
                $('.syntekplus-preset-card .preview-box').not('.preset-preview-box').remove();
            });
            
            const container = document.querySelector('#presets-container');
            if (container) {
                observer.observe(container, { childList: true, subtree: true });
            }
        }
    });

})(jQuery);
