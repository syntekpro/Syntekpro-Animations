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
        
        // Text animation shortcode (Get+)
        add_shortcode('sp_text_animate', array($this, 'text_animate_shortcode'));
        
        // SVG animation shortcode (Get+)
        add_shortcode('sp_svg_animate', array($this, 'svg_animate_shortcode'));
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
     * Text animation shortcode (Get+ FEATURE)
     * Usage: [sp_text_animate type="chars" effect="fadeIn"]Your Text[/sp_text_animate]
     */
    public function text_animate_shortcode($atts, $content = null) {
        if (!syntekpro_animations()->is_plus_active()) {
            return $this->plus_placeholder('Text Animation');
        }
        
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
     * SVG animation shortcode (Get+ FEATURE)
     * Usage: [sp_svg_animate type="draw" duration="2"]<svg>...</svg>[/sp_svg_animate]
     */
    public function svg_animate_shortcode($atts, $content = null) {
        if (!syntekpro_animations()->is_plus_active()) {
            return $this->plus_placeholder('SVG Animation');
        }
        
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
     * Timeline shortcode (Get+ FEATURE)
     * Usage: [sp_timeline][sp_animate]...[/sp_animate][sp_animate]...[/sp_animate][/sp_timeline]
     */
    public function timeline_shortcode($atts, $content = null) {
        if (!syntekpro_animations()->is_plus_active()) {
            return $this->plus_placeholder('Timeline Animation');
        }
        
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
    
    /**
     * Get+ feature placeholder
     */
    private function plus_placeholder($feature_name) {
        $is_admin = current_user_can('manage_options');
        
        if (!$is_admin) {
            return '';
        }
        
        return sprintf(
            '<div class="sp-plus-notice" style="background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:10px 0;border-radius:4px;">
                <strong>🔒 Get+ Feature: %s</strong>
                <p>This feature requires Syntekpro Animations Get+. <a href="%s" target="_blank">Get+</a></p>
            </div>',
            esc_html($feature_name),
            esc_url('https://syntekpro.com/animations-plus')
        );
    }
}

// Initialize
new Syntekpro_Animations_Shortcodes();