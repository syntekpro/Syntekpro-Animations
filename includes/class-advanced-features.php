<?php
/**
 * Advanced Animation Features
 * Includes timeline builder, custom easing, and animation sequences
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Advanced_Features {
    
    public function __construct() {
        add_shortcode('sp_timeline', array($this, 'timeline_shortcode'));
        add_shortcode('sp_sequence', array($this, 'sequence_shortcode'));
        add_shortcode('sp_hover_effect', array($this, 'hover_effect_shortcode'));
        add_shortcode('sp_scroll_scene', array($this, 'scroll_scene_shortcode'));
        add_action('wp_footer', array($this, 'output_advanced_animations'));
    }
    
    private $timeline_animations = array();
    private $sequence_animations = array();
    private $hover_effects = array();
    private $scroll_scenes = array();
    
    /**
     * Timeline Shortcode - Create animation sequences
     * [sp_timeline id="my-timeline" auto="yes" scrub="yes"]
     *   [sp_animate type="fadeIn"]Element 1[/sp_animate]
     *   [sp_animate type="slideLeft" delay="0.5"]Element 2[/sp_animate]
     * [/sp_timeline]
     */
    public function timeline_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'id' => 'timeline-' . rand(1000, 9999),
            'auto' => 'yes',
            'scrub' => 'no',
            'trigger' => '',
            'start' => 'top 80%',
            'end' => 'bottom 20%'
        ), $atts);
        
        $timeline_id = sanitize_key($atts['id']);
        
        // Store timeline data for footer output
        $this->timeline_animations[$timeline_id] = array(
            'auto' => $atts['auto'],
            'scrub' => $atts['scrub'],
            'trigger' => $atts['trigger'],
            'start' => $atts['start'],
            'end' => $atts['end']
        );
        
        $output = '<div class="sp-timeline" data-timeline-id="' . esc_attr($timeline_id) . '">';
        $output .= do_shortcode($content);
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Sequence Shortcode - Stagger animations on child elements
     * [sp_sequence type="fadeIn" stagger="0.1" trigger="scroll"]
     *   <div>Item 1</div>
     *   <div>Item 2</div>
     *   <div>Item 3</div>
     * [/sp_sequence]
     */
    public function sequence_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'type' => 'fadeIn',
            'duration' => '1',
            'stagger' => '0.1',
            'delay' => '0',
            'trigger' => 'scroll',
            'ease' => 'power2.out',
            'from' => 'start'
        ), $atts);
        
        $sequence_id = 'sequence-' . rand(1000, 9999);
        
        $this->sequence_animations[$sequence_id] = $atts;
        
        $output = '<div class="sp-sequence" data-sequence-id="' . esc_attr($sequence_id) . '" ';
        $output .= 'data-animation="' . esc_attr($atts['type']) . '" ';
        $output .= 'data-duration="' . esc_attr($atts['duration']) . '" ';
        $output .= 'data-stagger="' . esc_attr($atts['stagger']) . '" ';
        $output .= 'data-delay="' . esc_attr($atts['delay']) . '" ';
        $output .= 'data-ease="' . esc_attr($atts['ease']) . '" ';
        $output .= 'data-trigger="' . esc_attr($atts['trigger']) . '">';
        $output .= do_shortcode($content);
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Hover Effect Shortcode - Add interactive hover animations
     * [sp_hover_effect type="scale" scale="1.1" duration="0.3"]
     *   <img src="image.jpg" />
     * [/sp_hover_effect]
     */
    public function hover_effect_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'type' => 'scale',
            'scale' => '1.1',
            'x' => '0',
            'y' => '0',
            'rotation' => '0',
            'duration' => '0.3',
            'ease' => 'power2.out'
        ), $atts);
        
        $hover_id = 'hover-' . rand(1000, 9999);
        
        $this->hover_effects[$hover_id] = $atts;
        
        $output = '<div class="sp-hover-effect" data-hover-id="' . esc_attr($hover_id) . '" ';
        $output .= 'data-type="' . esc_attr($atts['type']) . '" ';
        $output .= 'data-scale="' . esc_attr($atts['scale']) . '" ';
        $output .= 'data-x="' . esc_attr($atts['x']) . '" ';
        $output .= 'data-y="' . esc_attr($atts['y']) . '" ';
        $output .= 'data-rotation="' . esc_attr($atts['rotation']) . '" ';
        $output .= 'data-duration="' . esc_attr($atts['duration']) . '" ';
        $output .= 'data-ease="' . esc_attr($atts['ease']) . '">';
        $output .= do_shortcode($content);
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Scroll Scene Shortcode - Advanced scroll-based animations
     * [sp_scroll_scene trigger="my-element" pin="yes" scrub="1"]
     *   [sp_animate type="fadeIn"]Content[/sp_animate]
     * [/sp_scroll_scene]
     */
    public function scroll_scene_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'id' => 'scene-' . rand(1000, 9999),
            'trigger' => '',
            'pin' => 'no',
            'scrub' => '0',
            'start' => 'top 80%',
            'end' => 'bottom 20%',
            'markers' => 'no'
        ), $atts);
        
        $scene_id = sanitize_key($atts['id']);
        
        $this->scroll_scenes[$scene_id] = $atts;
        
        $output = '<div class="sp-scroll-scene" data-scene-id="' . esc_attr($scene_id) . '" ';
        $output .= 'data-pin="' . esc_attr($atts['pin']) . '" ';
        $output .= 'data-scrub="' . esc_attr($atts['scrub']) . '" ';
        $output .= 'data-start="' . esc_attr($atts['start']) . '" ';
        $output .= 'data-end="' . esc_attr($atts['end']) . '" ';
        $output .= 'data-markers="' . esc_attr($atts['markers']) . '">';
        $output .= do_shortcode($content);
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Output JavaScript for advanced animations
     */
    public function output_advanced_animations() {
        if (empty($this->timeline_animations) && 
            empty($this->sequence_animations) && 
            empty($this->hover_effects) && 
            empty($this->scroll_scenes)) {
            return;
        }
        ?>
        <script type="text/javascript">
        (function() {
            if (typeof gsap === 'undefined') return;
            
            // Initialize on DOM ready
            document.addEventListener('DOMContentLoaded', function() {
                
                // SEQUENCE ANIMATIONS
                document.querySelectorAll('.sp-sequence').forEach(function(sequence) {
                    const children = sequence.children;
                    if (children.length === 0) return;
                    
                    const animation = sequence.dataset.animation || 'fadeIn';
                    const duration = parseFloat(sequence.dataset.duration) || 1;
                    const stagger = parseFloat(sequence.dataset.stagger) || 0.1;
                    const delay = parseFloat(sequence.dataset.delay) || 0;
                    const ease = sequence.dataset.ease || 'power2.out';
                    const trigger = sequence.dataset.trigger || 'scroll';
                    
                    // Animation presets
                    const animations = {
                        fadeIn: { from: { opacity: 0 }, to: { opacity: 1 } },
                        fadeInUp: { from: { opacity: 0, y: 30 }, to: { opacity: 1, y: 0 } },
                        fadeInDown: { from: { opacity: 0, y: -30 }, to: { opacity: 1, y: 0 } },
                        slideLeft: { from: { x: 50, opacity: 0 }, to: { x: 0, opacity: 1 } },
                        slideRight: { from: { x: -50, opacity: 0 }, to: { x: 0, opacity: 1 } },
                        scaleUp: { from: { scale: 0, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                        zoomIn: { from: { scale: 0.5, opacity: 0 }, to: { scale: 1, opacity: 1 } }
                    };
                    
                    const anim = animations[animation] || animations.fadeIn;
                    
                    // Set initial state
                    gsap.set(children, anim.from);
                    
                    if (trigger === 'scroll' && typeof ScrollTrigger !== 'undefined') {
                        gsap.to(children, {
                            ...anim.to,
                            duration: duration,
                            delay: delay,
                            stagger: stagger,
                            ease: ease,
                            scrollTrigger: {
                                trigger: sequence,
                                start: 'top 80%',
                                toggleActions: 'play none none none'
                            }
                        });
                    } else {
                        gsap.to(children, {
                            ...anim.to,
                            duration: duration,
                            delay: delay,
                            stagger: stagger,
                            ease: ease
                        });
                    }
                });
                
                // HOVER EFFECTS
                document.querySelectorAll('.sp-hover-effect').forEach(function(element) {
                    const type = element.dataset.type || 'scale';
                    const scale = parseFloat(element.dataset.scale) || 1.1;
                    const x = parseFloat(element.dataset.x) || 0;
                    const y = parseFloat(element.dataset.y) || 0;
                    const rotation = parseFloat(element.dataset.rotation) || 0;
                    const duration = parseFloat(element.dataset.duration) || 0.3;
                    const ease = element.dataset.ease || 'power2.out';
                    
                    const effects = {
                        scale: { scale: scale },
                        lift: { y: y || -10, boxShadow: '0 10px 30px rgba(0,0,0,0.2)' },
                        rotate: { rotation: rotation || 5 },
                        glow: { filter: 'brightness(1.2)' }
                    };
                    
                    const effect = effects[type] || effects.scale;
                    
                    element.addEventListener('mouseenter', function() {
                        gsap.to(element, { ...effect, duration: duration, ease: ease });
                    });
                    
                    element.addEventListener('mouseleave', function() {
                        gsap.to(element, { 
                            scale: 1, 
                            y: 0, 
                            rotation: 0, 
                            boxShadow: 'none',
                            filter: 'none',
                            duration: duration, 
                            ease: ease 
                        });
                    });
                });
                
                // SCROLL SCENES
                if (typeof ScrollTrigger !== 'undefined') {
                    document.querySelectorAll('.sp-scroll-scene').forEach(function(scene) {
                        const pin = scene.dataset.pin === 'yes';
                        const scrub = parseFloat(scene.dataset.scrub) || false;
                        const start = scene.dataset.start || 'top 80%';
                        const end = scene.dataset.end || 'bottom 20%';
                        const markers = scene.dataset.markers === 'yes';
                        
                        const timeline = gsap.timeline({
                            scrollTrigger: {
                                trigger: scene,
                                pin: pin,
                                scrub: scrub,
                                start: start,
                                end: end,
                                markers: markers
                            }
                        });
                        
                        // Animate children
                        const children = scene.querySelectorAll('.sp-animate');
                        children.forEach(function(child) {
                            const animation = child.dataset.animation || 'fadeIn';
                            const duration = parseFloat(child.dataset.duration) || 1;
                            
                            const animations = {
                                fadeIn: { opacity: 1 },
                                slideLeft: { x: 0, opacity: 1 },
                                scaleUp: { scale: 1, opacity: 1 }
                            };
                            
                            gsap.set(child, { opacity: 0, x: 50, scale: 0.5 });
                            timeline.to(child, { ...animations[animation], duration: duration });
                        });
                    });
                }
            });
        })();
        </script>
        <?php
    }
}

// Initialize
new Syntekpro_Advanced_Features();
