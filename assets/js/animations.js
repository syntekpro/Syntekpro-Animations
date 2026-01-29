/**
 * Syntekpro Animations - Frontend JavaScript
 */

(function() {
    'use strict';
    
    // Log immediately on script load
    console.log('animations.js loaded');
    console.log('typeof gsap:', typeof gsap);
    console.log('typeof ScrollTrigger:', typeof ScrollTrigger);
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded fired');
        console.log('Syntekpro Animations initialization starting');
        console.log('GSAP available:', typeof gsap !== 'undefined');
        console.log('ScrollTrigger available:', typeof ScrollTrigger !== 'undefined');
        
        // Check if GSAP is loaded
        if (typeof gsap === 'undefined') {
            console.error('GSAP is not loaded! Check network tab to see if gsap.min.js loaded.');
            console.error('Script dependencies may not be set up correctly.');
            return;
        }
        
        // Register ScrollTrigger if available
        if (typeof ScrollTrigger !== 'undefined') {
            try {
                gsap.registerPlugin(ScrollTrigger);
                console.log('ScrollTrigger registered with GSAP');
            } catch (e) {
                console.error('Error registering ScrollTrigger:', e);
            }
        } else {
            console.warn('ScrollTrigger not available, scroll animations will not work');
        }
        
        // Initialize animations
        console.log('Calling initAnimations()');
        try {
            initAnimations();
            
            // Refresh ScrollTrigger after all animations are set up
            if (typeof ScrollTrigger !== 'undefined') {
                console.log('Refreshing ScrollTrigger');
                // Use setTimeout to ensure DOM is fully rendered
                setTimeout(function() {
                    ScrollTrigger.refresh();
                    console.log('ScrollTrigger.refresh() completed');
                }, 100);
                
                // Safety fallback: if ScrollTrigger doesn't trigger after 3 seconds, show all hidden elements
                setTimeout(function() {
                    var hiddenElements = document.querySelectorAll('.sp-animate[style*="opacity: 0"]');
                    if (hiddenElements.length > 0) {
                        console.warn('Fallback: Found ' + hiddenElements.length + ' hidden elements after 3 seconds, forcing visibility');
                        hiddenElements.forEach(function(el) {
                            gsap.to(el, { opacity: 1, duration: 0.5 });
                        });
                    }
                }, 3000);
            }
        } catch (e) {
            console.error('Error in initAnimations:', e);
        }
        
        // Initialize smooth scroll if enabled
        if (typeof syntekproAnim !== 'undefined' && syntekproAnim.smoothScroll && typeof ScrollSmoother !== 'undefined') {
            console.log('Initializing ScrollSmoother');
            try {
                initSmoothScroll();
            } catch (e) {
                console.error('Error in initSmoothScroll:', e);
            }
        }
    });
    
    /**
     * Initialize all animations
     */
    function initAnimations() {
        const animatedElements = document.querySelectorAll('.sp-animate');
        console.log('Found .sp-animate elements:', animatedElements.length);
        
        if (animatedElements.length === 0) {
            console.warn('No elements with class .sp-animate found on page');
            return;
        }
        
        animatedElements.forEach(function(element) {
            try {
                console.log('Processing animation element:', element.id);
                
                const animationType = element.getAttribute('data-animation') || 'fadeIn';
                const duration = parseFloat(element.getAttribute('data-duration')) || 1;
                const delay = parseFloat(element.getAttribute('data-delay')) || 0;
                const trigger = element.getAttribute('data-trigger') || 'scroll';
                const start = element.getAttribute('data-start') || 'top 80%';
                const ease = element.getAttribute('data-ease') || 'power2.out';
                const stagger = parseFloat(element.getAttribute('data-stagger')) || 0;
                const repeat = parseInt(element.getAttribute('data-repeat')) || 0;
                
                console.log('Animation config:', { animationType, duration, delay, trigger, ease });
                
                // Get animation preset
                const animation = getAnimationPreset(animationType);
                
                if (!animation) {
                    console.warn('Animation type not found:', animationType);
                    return;
                }
                
                // Set initial state based on trigger type
                // For LOAD trigger: hide element initially, then animate on load
                // For SCROLL trigger: keep element visible, animate on scroll
                if (trigger === 'load') {
                    // Hide element initially for load animations
                    gsap.set(element, animation.from);
                    console.log('Load trigger: element hidden initially, will animate on load');
                } else if (trigger === 'scroll') {
                    // For scroll animations, set to FROM state so we know where animation starts
                    // Then ScrollTrigger will animate it to TO state on scroll
                    gsap.set(element, animation.from);
                    console.log('Scroll trigger: element set to initial state, will animate on scroll');
                }
                
                // Create animation config
                const animConfig = Object.assign({}, animation.to, {
                    duration: duration,
                    delay: delay,
                    ease: animation.to.ease || ease,
                    repeat: repeat,
                    stagger: stagger
                });
                
                // Determine how to trigger the animation
                if (trigger === 'scroll') {
                    if (typeof ScrollTrigger !== 'undefined') {
                        // Use ScrollTrigger plugin
                        animConfig.scrollTrigger = {
                            trigger: element,
                            start: start,
                            toggleActions: 'play none none none',
                            once: true,
                            markers: false
                        };
                        console.log('Using ScrollTrigger for scroll animation');
                        
                        // Animate with ScrollTrigger
                        gsap.to(element, animConfig);
                        console.log('Scroll animation created with ScrollTrigger');
                    } else {
                        // ScrollTrigger not available - use Intersection Observer as fallback
                        console.warn('ScrollTrigger not available, using Intersection Observer fallback');
                        const observer = new IntersectionObserver(function(entries) {
                            entries.forEach(function(entry) {
                                if (entry.isIntersecting) {
                                    gsap.to(element, animConfig);
                                    observer.unobserve(element);
                                    console.log('Intersection Observer triggered animation');
                                }
                            });
                        });
                        observer.observe(element);
                    }
                } else {
                    // Load trigger - animate immediately
                    gsap.to(element, animConfig);
                    console.log('Load animation started immediately');
                }
            } catch (e) {
                console.error('Error processing animation element:', element.id, e);
            }
        });
    }
    
    /**
     * Get animation preset
     */
    function getAnimationPreset(type) {
        const presets = {
            // Fade animations
            'fadeIn': {
                from: { opacity: 0 },
                to: { opacity: 1 }
            },
            'fadeInUp': {
                from: { opacity: 0, y: 50 },
                to: { opacity: 1, y: 0 }
            },
            'fadeInDown': {
                from: { opacity: 0, y: -50 },
                to: { opacity: 1, y: 0 }
            },
            'fadeInLeft': {
                from: { opacity: 0, x: -50 },
                to: { opacity: 1, x: 0 }
            },
            'fadeInRight': {
                from: { opacity: 0, x: 50 },
                to: { opacity: 1, x: 0 }
            },
            
            // Slide animations
            'slideLeft': {
                from: { x: 100 },
                to: { x: 0 }
            },
            'slideRight': {
                from: { x: -100 },
                to: { x: 0 }
            },
            'slideUp': {
                from: { y: 100 },
                to: { y: 0 }
            },
            'slideDown': {
                from: { y: -100 },
                to: { y: 0 }
            },
            
            // Scale animations
            'scaleUp': {
                from: { scale: 0, opacity: 0 },
                to: { scale: 1, opacity: 1 }
            },
            'scaleDown': {
                from: { scale: 2, opacity: 0 },
                to: { scale: 1, opacity: 1 }
            },
            'scaleX': {
                from: { scaleX: 0 },
                to: { scaleX: 1 }
            },
            'scaleY': {
                from: { scaleY: 0 },
                to: { scaleY: 1 }
            },
            
            // Rotate animations
            'rotateIn': {
                from: { rotation: -180, opacity: 0 },
                to: { rotation: 0, opacity: 1 }
            },
            'rotate360': {
                from: { rotation: 0 },
                to: { rotation: 360 }
            },
            'flipX': {
                from: { rotationY: 90, opacity: 0 },
                to: { rotationY: 0, opacity: 1 }
            },
            'flipY': {
                from: { rotationX: 90, opacity: 0 },
                to: { rotationX: 0, opacity: 1 }
            },
            
            // Bounce animations (Pro)
            'bounceIn': {
                from: { scale: 0, opacity: 0 },
                to: { scale: 1, opacity: 1, ease: 'back.out(1.7)' }
            },
            'bounceInUp': {
                from: { y: 100, opacity: 0 },
                to: { y: 0, opacity: 1, ease: 'back.out(1.7)' }
            },
            'bounceInLeft': {
                from: { x: -100, opacity: 0 },
                to: { x: 0, opacity: 1, ease: 'back.out(1.7)' }
            },
            'bounceInRight': {
                from: { x: 100, opacity: 0 },
                to: { x: 0, opacity: 1, ease: 'back.out(1.7)' }
            },
            
            // Elastic animations (Pro)
            'elasticIn': {
                from: { scale: 0 },
                to: { scale: 1, ease: 'elastic.out(1, 0.3)' }
            },
            
            // Blur animations (Pro)
            'blurIn': {
                from: { opacity: 0, filter: 'blur(10px)' },
                to: { opacity: 1, filter: 'blur(0px)' }
            },
            
            // Zoom animations
            'zoomIn': {
                from: { scale: 0.5, opacity: 0 },
                to: { scale: 1, opacity: 1 }
            },
            'zoomInUp': {
                from: { scale: 0.5, y: 50, opacity: 0 },
                to: { scale: 1, y: 0, opacity: 1 }
            },
            'zoomInDown': {
                from: { scale: 0.5, y: -50, opacity: 0 },
                to: { scale: 1, y: 0, opacity: 1 }
            },
            'zoomInLeft': {
                from: { scale: 0.5, x: -50, opacity: 0 },
                to: { scale: 1, x: 0, opacity: 1 }
            },
            'zoomInRight': {
                from: { scale: 0.5, x: 50, opacity: 0 },
                to: { scale: 1, x: 0, opacity: 1 }
            },
            
            // Reveal animations
            'revealLeft': {
                from: { clipPath: 'inset(0 100% 0 0)' },
                to: { clipPath: 'inset(0 0 0 0)' }
            },
            'revealRight': {
                from: { clipPath: 'inset(0 0 0 100%)' },
                to: { clipPath: 'inset(0 0 0 0)' }
            },
            'revealUp': {
                from: { clipPath: 'inset(100% 0 0 0)' },
                to: { clipPath: 'inset(0 0 0 0)' }
            },
            'revealDown': {
                from: { clipPath: 'inset(0 0 100% 0)' },
                to: { clipPath: 'inset(0 0 0 0)' }
            },
            
            // 3D Perspective animations (Pro)
            'perspective3D': {
                from: { rotationX: -90, transformOrigin: '50% 50% -200', opacity: 0 },
                to: { rotationX: 0, opacity: 1 }
            },
            'flipInX': {
                from: { rotationX: -90, opacity: 0 },
                to: { rotationX: 0, opacity: 1, ease: 'back.out(1.7)' }
            },
            'flipInY': {
                from: { rotationY: -90, opacity: 0 },
                to: { rotationY: 0, opacity: 1, ease: 'back.out(1.7)' }
            },
            'cardFlip': {
                from: { rotationY: 180, opacity: 0 },
                to: { rotationY: 0, opacity: 1 }
            },
            
            // Wave animations
            'waveIn': {
                from: { y: 50, opacity: 0 },
                to: { y: 0, opacity: 1, ease: 'sine.inOut' }
            },
            'ripple': {
                from: { scale: 0.8, opacity: 0 },
                to: { scale: 1, opacity: 1, ease: 'elastic.out(1, 0.5)' }
            },
            
            // Swing animations
            'swingIn': {
                from: { rotation: -15, transformOrigin: 'top center', opacity: 0 },
                to: { rotation: 0, opacity: 1, ease: 'back.out(3)' }
            },
            'pendulum': {
                from: { rotation: -45, transformOrigin: 'top center' },
                to: { rotation: 0, ease: 'elastic.out(1, 0.3)' }
            },
            
            // Attention seekers
            'pulse': {
                from: { scale: 1 },
                to: { scale: 1.1, repeat: 2, yoyo: true, ease: 'sine.inOut' }
            },
            'shake': {
                from: { x: 0 },
                to: { x: -10, repeat: 5, yoyo: true, ease: 'sine.inOut' }
            },
            'wobble': {
                from: { rotation: 0 },
                to: { rotation: 5, repeat: 5, yoyo: true, ease: 'sine.inOut' }
            },
            'heartbeat': {
                from: { scale: 1 },
                to: { scale: 1.3, repeat: 2, yoyo: true, ease: 'power2.inOut' }
            },
            
            // Advanced easing (Pro)
            'smoothBounce': {
                from: { y: -100, opacity: 0 },
                to: { y: 0, opacity: 1, ease: 'bounce.out' }
            },
            'elasticScale': {
                from: { scale: 0 },
                to: { scale: 1, ease: 'elastic.out(1.5, 0.5)' }
            },
            'backSlide': {
                from: { x: -100, opacity: 0 },
                to: { x: 0, opacity: 1, ease: 'back.out(2)' }
            }
        };
        
        return presets[type] || null;
    }
    
    /**
     * Initialize smooth scrolling
     */
    function initSmoothScroll() {
        if (typeof ScrollSmoother === 'undefined') {
            console.warn('ScrollSmoother not loaded');
            return;
        }
        
        gsap.registerPlugin(ScrollSmoother);
        
        ScrollSmoother.create({
            wrapper: '#smooth-wrapper',
            content: '#smooth-content',
            smooth: 1.5,
            effects: true,
            smoothTouch: 0.1
        });
    }
    
    /**
     * Public API for developers
     */
    window.SyntekproAnimations = {
        animate: function(selector, options) {
            return gsap.to(selector, options);
        },
        
        animateFrom: function(selector, from, to) {
            gsap.set(selector, from);
            return gsap.to(selector, to);
        },
        
        timeline: function() {
            return gsap.timeline();
        },
        
        scrollTrigger: function(options) {
            if (typeof ScrollTrigger === 'undefined') {
                console.error('ScrollTrigger not loaded');
                return null;
            }
            return ScrollTrigger.create(options);
        },
        
        getPreset: function(type) {
            return getAnimationPreset(type);
        }
    };
    
})();