/**
 * Syntekpro Animations - Frontend JavaScript
 */

(function() {
    'use strict';

    var config = (typeof syntekproAnim !== 'undefined') ? syntekproAnim : {};
    var developerMode = config.developerMode === true;
    var silenceLogs = config.silenceConsole === true;
    var debugLogsEnabled = developerMode && !silenceLogs;

    if (!debugLogsEnabled && typeof console !== 'undefined') {
        var noop = function() {};
        console.log = noop;
        console.info = noop;
        console.debug = noop;
    }

    if (silenceLogs && typeof console !== 'undefined') {
        console.warn = function() {};
    }

    function revealAllAnimatedElements() {
        var animatedElements = document.querySelectorAll('.sp-animate');
        animatedElements.forEach(function(element) {
            element.style.visibility = 'visible';
            element.style.opacity = '1';
            element.classList.add('animated');
        });
    }
    
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
        
        if (config.disableMobile && window.matchMedia('(max-width: 767px)').matches) {
            revealAllAnimatedElements();
            return;
        }

        if (config.reducedMotion && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            revealAllAnimatedElements();
            return;
        }

        const gsapAvailable = typeof gsap !== 'undefined';
        if (!gsapAvailable) {
            console.warn('GSAP is not loaded. CSS light-mode animations will still run if enabled.');
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
            var runAnimationInit = function() {
                initAnimations();
            };

            if (config.lazyLoad && 'requestIdleCallback' in window) {
                requestIdleCallback(runAnimationInit, { timeout: 1200 });
            } else if (config.lazyLoad) {
                setTimeout(runAnimationInit, 200);
            } else {
                runAnimationInit();
            }
            
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
    
    // Map of CSS-capable presets to their CSS class
    const cssPresetMap = {
        fadeIn: 'sp-css-fadeIn',
        fadeInUp: 'sp-css-fadeInUp',
        fadeInDown: 'sp-css-fadeInDown',
        fadeInLeft: 'sp-css-fadeInLeft',
        fadeInRight: 'sp-css-fadeInRight',
        slideLeft: 'sp-css-slideLeft',
        slideRight: 'sp-css-slideRight',
        slideUp: 'sp-css-slideUp',
        slideDown: 'sp-css-slideDown',
        zoomIn: 'sp-css-zoomIn',
        zoomInUp: 'sp-css-zoomInUp',
        zoomInDown: 'sp-css-zoomInDown',
        zoomInLeft: 'sp-css-zoomInLeft',
        zoomInRight: 'sp-css-zoomInRight',
        scaleUp: 'sp-css-scaleUp',
        scaleDown: 'sp-css-scaleDown',
        scaleX: 'sp-css-scaleX',
        scaleY: 'sp-css-scaleY',
        rotateIn: 'sp-css-rotateIn',
        pulse: 'sp-css-pulse',
        revealLeft: 'sp-css-revealLeft',
        revealRight: 'sp-css-revealRight',
        revealUp: 'sp-css-revealUp',
        revealDown: 'sp-css-revealDown'
    };

    function isCssPreset(type) {
        return !!cssPresetMap[type];
    }

    function mapEaseToCss(ease) {
        if (!ease) return 'ease';
        if (ease.indexOf('power1') === 0) return 'cubic-bezier(0.11, 0.6, 0.31, 0.99)';
        if (ease.indexOf('power2') === 0) return 'cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        if (ease.indexOf('power3') === 0) return 'cubic-bezier(0.22, 0.61, 0.36, 1)';
        if (ease.indexOf('power4') === 0) return 'cubic-bezier(0.1, 0.7, 0.1, 1)';
        if (ease.indexOf('back') === 0) return 'cubic-bezier(0.36, 0, 0.66, -0.56)';
        if (ease.indexOf('elastic') === 0) return 'cubic-bezier(0.5, -0.5, 0.5, 1.5)';
        if (ease.indexOf('bounce') === 0) return 'cubic-bezier(0.34, 1.56, 0.64, 1)';
        return 'ease';
    }

    function applyCssAnimation(element, opts) {
        const { animationType, duration, delay, ease, trigger, startPosition, onceOnly } = opts;
        const cssClass = cssPresetMap[animationType] || cssPresetMap.fadeIn;
        const cssEase = mapEaseToCss(ease);

        element.classList.add('sp-css-anim', cssClass);
        element.style.setProperty('--sp-duration', duration + 's');
        element.style.setProperty('--sp-delay', delay + 's');
        element.style.setProperty('--sp-ease', cssEase);

        const play = () => {
            element.style.visibility = 'visible';
            element.classList.add('animated', 'sp-css-play');
        };

        const reset = () => {
            element.classList.remove('sp-css-play');
        };

        if (trigger === 'load') {
            play();
            return;
        }

        if (trigger === 'scroll') {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        play();
                        if (onceOnly) {
                            observer.unobserve(element);
                        }
                    } else if (!onceOnly) {
                        reset();
                    }
                });
            }, { threshold: 0.2 });
            observer.observe(element);
            return;
        }

        if (trigger === 'hover' || trigger === 'pointer') {
            element.addEventListener('mouseenter', function() {
                play();
            });
            element.addEventListener('mouseleave', function() {
                if (!onceOnly) {
                    reset();
                }
            });

            if (trigger === 'pointer') {
                const followStrength = 12;
                element.addEventListener('mousemove', function(ev) {
                    const rect = element.getBoundingClientRect();
                    const relX = (ev.clientX - rect.left) / rect.width - 0.5;
                    const relY = (ev.clientY - rect.top) / rect.height - 0.5;
                    element.style.transform = 'translate(' + (relX * followStrength) + 'px, ' + (relY * followStrength) + 'px)';
                });
                element.addEventListener('mouseleave', function() {
                    element.style.transform = 'translate(0, 0)';
                });
            }
            return;
        }

        if (trigger === 'click') {
            element.addEventListener('click', function() {
                if (element.classList.contains('sp-css-play')) {
                    if (!onceOnly) {
                        reset();
                    }
                } else {
                    play();
                }
            });
            return;
        }

        // Fallback
        play();
    }

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
        
        const globalEngine = (typeof syntekproAnim !== 'undefined' && syntekproAnim.engine) ? syntekproAnim.engine : 'auto';
        const defaultDuration = (typeof syntekproAnim !== 'undefined' && syntekproAnim.defaultDuration) ? parseFloat(syntekproAnim.defaultDuration) : 1;
        const defaultEase = (typeof syntekproAnim !== 'undefined' && syntekproAnim.defaultEase) ? syntekproAnim.defaultEase : 'power2.out';
        const isPro = (typeof syntekproAnim !== 'undefined' && syntekproAnim.isPro === true);
        const freePresetKeys = (typeof syntekproAnim !== 'undefined' && Array.isArray(syntekproAnim.freePresetKeys)) ? syntekproAnim.freePresetKeys : [];
        const debugItems = [];

        animatedElements.forEach(function(element) {
            try {
                console.log('Processing animation element:', element.id);
                
                let animationType = element.getAttribute('data-animation') || 'fadeIn';
                if (!isPro && freePresetKeys.length > 0 && freePresetKeys.indexOf(animationType) === -1) {
                    animationType = 'fadeIn';
                    element.dataset.spLocked = 'true';
                }

                const durationValue = parseFloat(element.getAttribute('data-duration'));
                const duration = !isNaN(durationValue) ? durationValue : defaultDuration;
                const delay = parseFloat(element.getAttribute('data-delay')) || 0;
                const trigger = element.getAttribute('data-trigger') || 'scroll';
                const start = element.getAttribute('data-start') || 'top 80%';
                const ease = element.getAttribute('data-ease') || defaultEase;
                const stagger = parseFloat(element.getAttribute('data-stagger')) || 0;
                const repeat = parseInt(element.getAttribute('data-repeat')) || 0;
                const startPosition = element.getAttribute('data-start') || 'top 80%';
                const onceOnly = element.getAttribute('data-once') !== 'false';
                const markers = element.getAttribute('data-markers') === 'true';
                
                const engineAttr = element.getAttribute('data-engine') || 'auto';
                const effectiveEngine = engineAttr !== 'auto' ? engineAttr : globalEngine;
                const cssCapable = isCssPreset(animationType);
                const useCss = (effectiveEngine === 'css' && cssCapable) || (effectiveEngine === 'auto' && cssCapable);
                const resolvedEngine = useCss ? 'css' : (typeof gsap !== 'undefined' ? 'gsap' : 'none');

                console.log('Animation config:', { animationType, duration, delay, trigger, ease, startPosition, engine: effectiveEngine, useCss });

                                // Tag runtime data for the debug overlay
                                element.dataset.spEngineRequested = effectiveEngine;
                                element.dataset.spEngineResolved = resolvedEngine;
                                element.dataset.spTrigger = trigger;
                                element.dataset.spAnimation = animationType;
                                element.dataset.spMarkers = markers ? 'true' : 'false';
                                element.dataset.spOnce = onceOnly ? 'true' : 'false';

                                debugItems.push({
                                    id: element.id || null,
                                    tag: element.tagName,
                                    animation: animationType,
                                    trigger: trigger,
                                    engineRequested: effectiveEngine,
                                    engineResolved: resolvedEngine,
                                    cssCapable: cssCapable,
                                    markers: markers,
                                    onceOnly: onceOnly,
                                    delay: delay,
                                    duration: duration
                                });
                
                // Get animation preset
                const animation = getAnimationPreset(animationType);
                
                if (!animation) {
                    console.warn('Animation type not found:', animationType);
                    return;
                }
                
                // CSS-only path for supported presets
                if (useCss) {
                    applyCssAnimation(element, { animationType, duration, delay, ease, trigger, startPosition, onceOnly });
                    return;
                }

                if (typeof gsap === 'undefined') {
                    console.warn('GSAP unavailable; falling back to visibility for', animationType, 'trigger', trigger);
                    element.style.visibility = 'visible';
                    element.classList.add('animated');
                    return;
                }

                // Base config
                const baseConfig = Object.assign({}, animation.to, {
                    duration: duration,
                    delay: delay,
                    ease: animation.to.ease || ease,
                    repeat: repeat,
                    stagger: stagger
                });

                // Set initial state
                gsap.set(element, animation.from);

                // Determine how to trigger the animation
                if (trigger === 'scroll') {
                    if (typeof ScrollTrigger !== 'undefined') {
                        const animConfig = Object.assign({}, baseConfig, {
                            scrollTrigger: {
                                trigger: element,
                                start: startPosition,
                                toggleActions: onceOnly ? 'play none none none' : 'play none none reset',
                                once: onceOnly,
                                markers: markers,
                                onEnter: function() {
                                    element.style.visibility = 'visible';
                                    element.classList.add('animated');
                                    console.log('ScrollTrigger entered:', element.id);
                                },
                                onEnterBack: function() {
                                    element.style.visibility = 'visible';
                                    element.classList.add('animated');
                                    console.log('ScrollTrigger re-entered:', element.id);
                                }
                            }
                        });

                        gsap.to(element, animConfig);
                        console.log('Scroll animation created with ScrollTrigger for element:', element.id);
                    } else {
                        console.warn('ScrollTrigger not available, using Intersection Observer fallback');
                        const observer = new IntersectionObserver(function(entries) {
                            entries.forEach(function(entry) {
                                if (entry.isIntersecting) {
                                    element.style.visibility = 'visible';
                                    element.classList.add('animated');
                                    gsap.to(element, baseConfig);
                                    observer.unobserve(element);
                                    console.log('Intersection Observer triggered animation');
                                }
                            });
                        });
                        observer.observe(element);
                    }
                } else if (trigger === 'hover') {
                    element.style.visibility = 'visible';
                    const hoverTimeline = gsap.timeline({ paused: true });
                    hoverTimeline.fromTo(element, animation.from, Object.assign({}, baseConfig));

                    element.addEventListener('mouseenter', function() {
                        element.classList.add('animated');
                        hoverTimeline.play(0);
                    });

                    element.addEventListener('mouseleave', function() {
                        if (!onceOnly) {
                            hoverTimeline.reverse();
                        }
                    });
                } else if (trigger === 'pointer') {
                    element.style.visibility = 'visible';
                    const pointerTimeline = gsap.timeline({ paused: true });
                    pointerTimeline.fromTo(element, animation.from, Object.assign({}, baseConfig));

                    let hasPlayed = false;
                    const followStrength = 16;
                    element.addEventListener('mousemove', function(ev) {
                        const rect = element.getBoundingClientRect();
                        const relX = (ev.clientX - rect.left) / rect.width - 0.5;
                        const relY = (ev.clientY - rect.top) / rect.height - 0.5;
                        gsap.to(element, { x: relX * followStrength, y: relY * followStrength, duration: 0.2, overwrite: true, ease: 'power2.out' });

                        if (!hasPlayed) {
                            element.classList.add('animated');
                            pointerTimeline.play(0);
                            hasPlayed = true;
                        }
                    });

                    element.addEventListener('mouseleave', function() {
                        gsap.to(element, { x: 0, y: 0, duration: 0.25, ease: 'power2.out' });
                        if (!onceOnly && hasPlayed) {
                            pointerTimeline.reverse();
                            hasPlayed = false;
                        }
                    });
                } else if (trigger === 'click') {
                    element.style.visibility = 'visible';
                    const clickTimeline = gsap.timeline({ paused: true });
                    clickTimeline.fromTo(element, animation.from, Object.assign({}, baseConfig));

                    element.addEventListener('click', function() {
                        element.classList.add('animated');
                        if (onceOnly && clickTimeline.progress() === 1) {
                            return;
                        }
                        if (clickTimeline.reversed() || clickTimeline.paused()) {
                            clickTimeline.play(0);
                        } else if (!onceOnly) {
                            clickTimeline.reverse();
                        }
                    });
                } else {
                    // Load trigger - animate immediately
                    element.style.visibility = 'visible';
                    element.classList.add('animated');
                    gsap.to(element, baseConfig);
                    console.log('Load animation started immediately');
                }
            } catch (e) {
                console.error('Error processing animation element:', element.id, e);
            }
        });

        try {
            window.dispatchEvent(new CustomEvent('syntekpro:animations-ready', {
                detail: {
                    items: debugItems,
                    globalEngine: globalEngine,
                    hasGsap: typeof gsap !== 'undefined'
                }
            }));
        } catch (dispatchError) {
            console.warn('Unable to dispatch syntekpro:animations-ready event', dispatchError);
        }
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