<?php
/**
 * Pro Features Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Animations_Pro {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_pro_scripts'));
        add_filter('syntekpro_animation_presets', array($this, 'add_pro_presets'));
        
        // Developer hooks
        add_action('syntekpro_before_animation', array($this, 'before_animation_hook'));
        add_action('syntekpro_after_animation', array($this, 'after_animation_hook'));
    }
    
    /**
     * Enqueue Pro scripts
     */
    public function enqueue_pro_scripts() {
        // Pro custom animations
        wp_enqueue_script(
            'syntekpro-pro-animations',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/pro-animations.js',
            array('syntekpro-gsap', 'syntekpro-animations'),
            SYNTEKPRO_ANIM_VERSION,
            true
        );
        
        wp_localize_script('syntekpro-pro-animations', 'syntekproAnimPro', array(
            'version' => SYNTEKPRO_ANIM_VERSION,
            'features' => array(
                'splitText' => get_option('syntekpro_anim_load_splittext') === 'yes',
                'morphSVG' => get_option('syntekpro_anim_load_morphsvgplugin') === 'yes',
                'drawSVG' => get_option('syntekpro_anim_load_drawsvgplugin') === 'yes',
                'scrollSmoother' => get_option('syntekpro_anim_load_scrollsmoother') === 'yes'
            )
        ));
    }
    
    /**
     * Add Pro presets
     */
    public function add_pro_presets($presets) {
        $pro_presets = array(
            // Advanced bounce animations
            'bounceInLeft' => array(
                'name' => 'Bounce In Left',
                'category' => 'bounce',
                'free' => true,
                'from' => array('x' => -100, 'opacity' => 0),
                'to' => array('x' => 0, 'opacity' => 1, 'ease' => 'back.out(1.7)')
            ),
            'bounceInRight' => array(
                'name' => 'Bounce In Right',
                'category' => 'bounce',
                'free' => true,
                'from' => array('x' => 100, 'opacity' => 0),
                'to' => array('x' => 0, 'opacity' => 1, 'ease' => 'back.out(1.7)')
            ),
            
            // Wiggle animations
            'wiggle' => array(
                'name' => 'Wiggle',
                'category' => 'wiggle',
                'free' => true,
                'from' => array(),
                'to' => array('x' => 10, 'ease' => 'wiggle')
            ),
            
            // 3D animations
            'flip3D' => array(
                'name' => 'Flip 3D',
                'category' => '3d',
                'free' => true,
                'from' => array('rotationY' => -180, 'opacity' => 0, 'transformPerspective' => 1000),
                'to' => array('rotationY' => 0, 'opacity' => 1)
            ),
            'rotate3D' => array(
                'name' => 'Rotate 3D',
                'category' => '3d',
                'free' => true,
                'from' => array('rotationX' => -90, 'rotationY' => -90, 'opacity' => 0),
                'to' => array('rotationX' => 0, 'rotationY' => 0, 'opacity' => 1)
            ),
            
            // Glitch effects
            'glitch' => array(
                'name' => 'Glitch',
                'category' => 'glitch',
                'free' => true,
                'from' => array('x' => 0),
                'to' => array('x' => '+=20', 'yoyo' => true, 'repeat' => 3)
            ),
            
            // Liquid animations
            'liquidFill' => array(
                'name' => 'Liquid Fill',
                'category' => 'liquid',
                'free' => true,
                'from' => array('scaleY' => 0, 'transformOrigin' => 'bottom'),
                'to' => array('scaleY' => 1, 'ease' => 'elastic.out(1, 0.3)')
            )
        );
        
        return array_merge($presets, $pro_presets);
    }
    
    /**
     * Before animation hook
     */
    public function before_animation_hook($element) {
        do_action('syntekpro_pro_before_animation', $element);
    }
    
    /**
     * After animation hook
     */
    public function after_animation_hook($element) {
        do_action('syntekpro_pro_after_animation', $element);
    }
    
    /**
     * Get Pro status
     */
    public static function get_pro_info() {
        return array(
            'active' => true,
            'features' => array(
                'splitText' => true,
                'morphSVG' => true,
                'drawSVG' => true,
                'scrollSmoother' => true,
                'gsDevTools' => true,
                'customBounce' => true,
                'customWiggle' => true,
                'inertia' => true,
                'scrambleText' => true
            )
        );
    }
}

// Initialize
new Syntekpro_Animations_Pro();
