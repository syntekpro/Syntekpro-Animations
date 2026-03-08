<?php
/**
 * Shortcodes for Syntekpro Animations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Animations_Shortcodes {
    
    public function __construct() {
        // Basic animation shortcode
        add_shortcode('sp_animate', array($this, 'animate_shortcode'));
        
        // Text animation shortcode
        add_shortcode('sp_text_animate', array($this, 'text_animate_shortcode'));
        
        // SVG animation shortcode
        add_shortcode('sp_svg_animate', array($this, 'svg_animate_shortcode'));
        
        // Timeline shortcode
        add_shortcode('sp_timeline', array($this, 'timeline_shortcode'));
    }
    
    /**
     * Basic animation shortcode
     * Usage: [sp_animate type="fadeIn" duration="1" delay="0" trigger="scroll" stagger="0" repeat="0"]Content[/sp_animate]
     */
    public function animate_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'type' => 'fadeIn',
            'duration' => '1',
            'delay' => '0',
            'trigger' => 'scroll',
            'start' => 'top 80%',
            'ease' => 'power2.out',
            'stagger' => '0',
            'repeat' => '0',
            'class' => '',
            'id' => ''
        ), $atts, 'sp_animate');
        
        $unique_id = $atts['id'] ? $atts['id'] : 'sp-anim-' . uniqid();
        $classes = 'sp-animate ' . esc_attr($atts['class']);
        
        $html = sprintf(
            '<div id="%s" class="%s" data-animation="%s" data-duration="%s" data-delay="%s" data-trigger="%s" data-start="%s" data-ease="%s" data-stagger="%s" data-repeat="%s">%s</div>',
            esc_attr($unique_id),
            $classes,
            esc_attr($atts['type']),
            esc_attr($atts['duration']),
            esc_attr($atts['delay']),
            esc_attr($atts['trigger']),
            esc_attr($atts['start']),
            esc_attr($atts['ease']),
            esc_attr($atts['stagger']),
            esc_attr($atts['repeat']),
            do_shortcode($content)
        );
        
        return $html;
    }
    
    /**
     * Text animation shortcode
     * Usage: [sp_text_animate type="chars" effect="fadeIn"]Your Text[/sp_text_animate]
     */
    public function text_animate_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'type' => 'chars', // chars, words, lines
            'effect' => 'fadeIn',
            'duration' => '0.05',
            'stagger' => '0.03',
            'ease' => 'power2.out',
            'class' => ''
        ), $atts, 'sp_text_animate');
        
        $unique_id = 'sp-text-' . uniqid();
        $classes = 'sp-text-animate ' . esc_attr($atts['class']);
        
        $html = sprintf(
            '<div id="%s" class="%s" data-split-type="%s" data-effect="%s" data-duration="%s" data-stagger="%s" data-ease="%s">%s</div>',
            esc_attr($unique_id),
            $classes,
            esc_attr($atts['type']),
            esc_attr($atts['effect']),
            esc_attr($atts['duration']),
            esc_attr($atts['stagger']),
            esc_attr($atts['ease']),
            do_shortcode($content)
        );
        
        return $html;
    }
    
    /**
     * SVG animation shortcode
     * Usage: [sp_svg_animate type="draw" duration="2"]<svg>...</svg>[/sp_svg_animate]
     */
    public function svg_animate_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'type' => 'draw', // draw, morph
            'duration' => '2',
            'delay' => '0',
            'ease' => 'power2.inOut',
            'class' => ''
        ), $atts, 'sp_svg_animate');
        
        $unique_id = 'sp-svg-' . uniqid();
        $classes = 'sp-svg-animate ' . esc_attr($atts['class']);
        
        $html = sprintf(
            '<div id="%s" class="%s" data-svg-type="%s" data-duration="%s" data-delay="%s" data-ease="%s">%s</div>',
            esc_attr($unique_id),
            $classes,
            esc_attr($atts['type']),
            esc_attr($atts['duration']),
            esc_attr($atts['delay']),
            esc_attr($atts['ease']),
            do_shortcode($content)
        );
        
        return $html;
    }
    
    /**
     * Timeline shortcode
     * Usage: [sp_timeline][sp_animate]...[/sp_animate][sp_animate]...[/sp_animate][/sp_timeline]
     */
    public function timeline_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'repeat' => '0',
            'yoyo' => 'false',
            'class' => ''
        ), $atts, 'sp_timeline');
        
        $unique_id = 'sp-timeline-' . uniqid();
        $classes = 'sp-timeline ' . esc_attr($atts['class']);
        
        $html = sprintf(
            '<div id="%s" class="%s" data-repeat="%s" data-yoyo="%s">%s</div>',
            esc_attr($unique_id),
            $classes,
            esc_attr($atts['repeat']),
            esc_attr($atts['yoyo']),
            do_shortcode($content)
        );
        
        return $html;
    }
    
}

// Initialize
new Syntekpro_Animations_Shortcodes();