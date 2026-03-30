/**
 * Syntekpro Animations - Get+ Features JavaScript
 */

(function() {
    'use strict';
    
    // Wait for DOM and GSAP to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Syntekpro Animations Get+ loaded');
        
        // Register Get+ plugins
        registerPlusPlugins();
        
        // Initialize Get+ features
        initTextAnimations();
        initSVGAnimations();
        initTimelineAnimations();
        initScrollSmoother();
    });
    
    /**
     * Register Get+ GSAP plugins
     */
    function registerPlusPlugins() {
        const features = syntekproAnimPlus.features;
        
        if (features.splitText && typeof SplitText !== 'undefined') {
            gsap.registerPlugin(SplitText);
        }
        
        if (features.morphSVG && typeof MorphSVGPlugin !== 'undefined') {
            gsap.registerPlugin(MorphSVGPlugin);
        }
        
        if (features.drawSVG && typeof DrawSVGPlugin !== 'undefined') {
            gsap.registerPlugin(DrawSVGPlugin);
        }
        
        if (features.scrollSmoother && typeof ScrollSmoother !== 'undefined') {
            gsap.registerPlugin(ScrollSmoother);
        }
    }
    
    /**
     * Initialize Text Animations (SplitText)
     */
    function initTextAnimations() {
        if (typeof SplitText === 'undefined') {
            return;
        }
        
        const textElements = document.querySelectorAll('.sp-text-animate');
        
        textElements.forEach(function(element) {
            const splitType = element.getAttribute('data-split-type') || 'chars';
            const effect = element.getAttribute('data-effect') || 'fadeIn';
            const duration = parseFloat(element.getAttribute('data-duration')) || 0.05;
            const stagger = parseFloat(element.getAttribute('data-stagger')) || 0.03;
            const ease = element.getAttribute('data-ease') || 'power2.out';
            
            // Split the text
            const split = new SplitText(element, { type: splitType });
            
            // Get animation config
            const animConfig = getTextAnimationConfig(effect, duration, stagger, ease);
            
            // Set initial state
            gsap.set(split[splitType], animConfig.from);
            
            // Animate with ScrollTrigger
            gsap.to(split[splitType], {
                ...animConfig.to,
                scrollTrigger: {
                    trigger: element,
                    start: 'top 80%',
                    toggleActions: 'play none none none',
                    once: true
                }
            });
        });
    }
    
    /**
     * Get text animation configuration
     */
    function getTextAnimationConfig(effect, duration, stagger, ease) {
        const configs = {
            fadeIn: {
                from: { opacity: 0 },
                to: { opacity: 1, duration: duration, stagger: stagger, ease: ease }
            },
            fadeInUp: {
                from: { opacity: 0, y: 20 },
                to: { opacity: 1, y: 0, duration: duration, stagger: stagger, ease: ease }
            },
            fadeInDown: {
                from: { opacity: 0, y: -20 },
                to: { opacity: 1, y: 0, duration: duration, stagger: stagger, ease: ease }
            },
            slideLeft: {
                from: { opacity: 0, x: 50 },
                to: { opacity: 1, x: 0, duration: duration, stagger: stagger, ease: ease }
            },
            slideRight: {
                from: { opacity: 0, x: -50 },
                to: { opacity: 1, x: 0, duration: duration, stagger: stagger, ease: ease }
            },
            scaleUp: {
                from: { opacity: 0, scale: 0 },
                to: { opacity: 1, scale: 1, duration: duration, stagger: stagger, ease: ease }
            },
            rotateIn: {
                from: { opacity: 0, rotation: -90 },
                to: { opacity: 1, rotation: 0, duration: duration, stagger: stagger, ease: ease }
            },
            blur: {
                from: { opacity: 0, filter: 'blur(10px)' },
                to: { opacity: 1, filter: 'blur(0px)', duration: duration, stagger: stagger, ease: ease }
            }
        };
        
        return configs[effect] || configs.fadeIn;
    }
    
    /**
     * Initialize SVG Animations
     */
    function initSVGAnimations() {
        const svgElements = document.querySelectorAll('.sp-svg-animate');
        
        svgElements.forEach(function(element) {
            const svgType = element.getAttribute('data-svg-type') || 'draw';
            const duration = parseFloat(element.getAttribute('data-duration')) || 2;
            const delay = parseFloat(element.getAttribute('data-delay')) || 0;
            const ease = element.getAttribute('data-ease') || 'power2.inOut';
            
            if (svgType === 'draw' && typeof DrawSVGPlugin !== 'undefined') {
                animateDrawSVG(element, duration, delay, ease);
            } else if (svgType === 'morph' && typeof MorphSVGPlugin !== 'undefined') {
                animateMorphSVG(element, duration, delay, ease);
            }
        });
    }
    
    /**
     * Animate DrawSVG
     */
    function animateDrawSVG(element, duration, delay, ease) {
        const paths = element.querySelectorAll('path, line, circle, rect, polygon');
        
        gsap.set(paths, { drawSVG: '0%' });
        
        gsap.to(paths, {
            drawSVG: '100%',
            duration: duration,
            delay: delay,
            ease: ease,
            stagger: 0.1,
            scrollTrigger: {
                trigger: element,
                start: 'top 80%',
                toggleActions: 'play none none none',
                once: true
            }
        });
    }
    
    /**
     * Animate MorphSVG
     */
    function animateMorphSVG(element, duration, delay, ease) {
        const fromShape = element.getAttribute('data-morph-from');
        const toShape = element.getAttribute('data-morph-to');
        
        if (!fromShape || !toShape) {
            console.warn('MorphSVG requires data-morph-from and data-morph-to attributes');
            return;
        }
        
        const paths = element.querySelectorAll('path');
        
        gsap.to(paths, {
            morphSVG: toShape,
            duration: duration,
            delay: delay,
            ease: ease,
            scrollTrigger: {
                trigger: element,
                start: 'top 80%',
                toggleActions: 'play none none reverse',
            }
        });
    }
    
    /**
     * Initialize Timeline Animations
     */
    function initTimelineAnimations() {
        const timelines = document.querySelectorAll('.sp-timeline');
        
        timelines.forEach(function(timelineElement) {
            const repeat = parseInt(timelineElement.getAttribute('data-repeat')) || 0;
            const yoyo = timelineElement.getAttribute('data-yoyo') === 'true';
            
            // Create timeline
            const tl = gsap.timeline({
                repeat: repeat,
                yoyo: yoyo,
                scrollTrigger: {
                    trigger: timelineElement,
                    start: 'top 80%',
                    toggleActions: 'play none none none',
                    once: true
                }
            });
            
            // Add animations from child elements
            const children = timelineElement.querySelectorAll('.sp-animate');
            
            children.forEach(function(child, index) {
                const animationType = child.getAttribute('data-animation') || 'fadeIn';
                const duration = parseFloat(child.getAttribute('data-duration')) || 1;
                const delay = parseFloat(child.getAttribute('data-delay')) || 0;
                const ease = child.getAttribute('data-ease') || 'power2.out';
                
                // Get animation from presets
                const animation = window.SyntekproAnimations.getPreset(animationType);
                
                if (animation) {
                    gsap.set(child, animation.from);
                    
                    const animConfig = Object.assign({}, animation.to, {
                        duration: duration,
                        ease: ease
                    });
                    
                    if (index === 0) {
                        tl.to(child, animConfig, delay);
                    } else {
                        tl.to(child, animConfig, '-=' + (duration * 0.3)); // Overlap slightly
                    }
                }
            });
        });
    }
    
    /**
     * Initialize ScrollSmoother
     */
    function initScrollSmoother() {
        if (!syntekproAnimPlus.features.scrollSmoother || typeof ScrollSmoother === 'undefined') {
            return;
        }
        
        // Check if wrapper and content exist
        let wrapper = document.getElementById('smooth-wrapper');
        let content = document.getElementById('smooth-content');
        
        // Create wrappers if they don't exist
        if (!wrapper || !content) {
            const body = document.body;
            const bodyContent = body.innerHTML;
            
            wrapper = document.createElement('div');
            wrapper.id = 'smooth-wrapper';
            
            content = document.createElement('div');
            content.id = 'smooth-content';
            content.innerHTML = bodyContent;
            
            body.innerHTML = '';
            wrapper.appendChild(content);
            body.appendChild(wrapper);
        }
        
        // Create ScrollSmoother
        ScrollSmoother.create({
            wrapper: '#smooth-wrapper',
            content: '#smooth-content',
            smooth: 1.5,
            effects: true,
            smoothTouch: 0.1,
            normalizeScroll: true
        });
    }
    
    /**
     * Extended Public API for Get+ features
     */
    window.SyntekproAnimationsPlus = {
        splitText: function(selector, options) {
            if (typeof SplitText === 'undefined') {
                console.error('SplitText plugin not loaded');
                return null;
            }
            return new SplitText(selector, options);
        },
        
        morphSVG: function(selector, target, options) {
            if (typeof MorphSVGPlugin === 'undefined') {
                console.error('MorphSVG plugin not loaded');
                return null;
            }
            return gsap.to(selector, Object.assign({ morphSVG: target }, options));
        },
        
        drawSVG: function(selector, options) {
            if (typeof DrawSVGPlugin === 'undefined') {
                console.error('DrawSVG plugin not loaded');
                return null;
            }
            return gsap.to(selector, Object.assign({ drawSVG: '100%' }, options));
        },
        
        scrambleText: function(selector, options) {
            if (typeof ScrambleTextPlugin === 'undefined') {
                console.error('ScrambleText plugin not loaded');
                return null;
            }
            return gsap.to(selector, Object.assign({ scrambleText: {} }, options));
        },
        
        timeline: function(options) {
            return gsap.timeline(options);
        }
    };
    
})();