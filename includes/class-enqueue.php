<?php
/**
 * Enqueue GSAP Scripts and Styles
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Animations_Enqueue {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        $options = $this->get_options();
        
        // Core GSAP (always load if enabled)
        if ($options['load_gsap'] === 'yes') {
            wp_enqueue_script(
                'syntekpro-gsap',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/gsap/minified/gsap.min.js',
                array(),
                SYNTEKPRO_ANIM_VERSION,
                true
            );
        }
        
        // ScrollTrigger (FREE)
        if ($options['load_scrolltrigger'] === 'yes') {
            wp_enqueue_script(
                'syntekpro-scrolltrigger',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/gsap/minified/ScrollTrigger.min.js',
                array('syntekpro-gsap'),
                SYNTEKPRO_ANIM_VERSION,
                true
            );
        }
        
        // Free plugins
        $this->load_free_plugins();
        
        // Pro plugins (only if license is active)
        if (syntekpro_animations()->is_pro_active()) {
            $this->load_pro_plugins();
        }
        
        // NOTE: animations-frontend script is registered and enqueued by the Gutenberg block
        // Only on pages with the block will animations.js be enqueued
        // This is handled by the 'script' parameter in register_block_type()
        
        // Pass data to JavaScript (for animations script when it loads)
        wp_localize_script('syntekpro-gsap', 'syntekproAnim', array(
            'isPro' => syntekpro_animations()->is_pro_active(),
            'smoothScroll' => $options['smooth_scroll'] === 'yes',
            'developerMode' => $options['enable_developer_mode'] === 'yes'
        ));
        
        // Frontend styles
        wp_enqueue_style(
            'syntekpro-animations-style',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/style.css',
            array(),
            SYNTEKPRO_ANIM_VERSION
        );
    }
    
    /**
     * Load free GSAP plugins
     */
    private function load_free_plugins() {
        $free_plugins = array(
            'Flip' => 'Flip.min.js',
            'Observer' => 'Observer.min.js',
            'ScrollToPlugin' => 'ScrollToPlugin.min.js',
            'TextPlugin' => 'TextPlugin.min.js',
            'Draggable' => 'Draggable.min.js',
            'MotionPathPlugin' => 'MotionPathPlugin.min.js',
            'EasePack' => 'EasePack.min.js',
            'CustomEase' => 'CustomEase.min.js'
        );
        
        foreach ($free_plugins as $name => $file) {
            $option_key = 'load_' . strtolower($name);
            if (get_option('syntekpro_anim_' . $option_key) === 'yes') {
                wp_enqueue_script(
                    'syntekpro-' . strtolower($name),
                    SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/gsap/minified/' . $file,
                    array('syntekpro-gsap'),
                    SYNTEKPRO_ANIM_VERSION,
                    true
                );
            }
        }
    }
    
    /**
     * Load premium GSAP plugins
     */
    private function load_pro_plugins() {
        $pro_plugins = array(
            'SplitText' => 'SplitText.min.js',
            'MorphSVGPlugin' => 'MorphSVGPlugin.min.js',
            'DrawSVGPlugin' => 'DrawSVGPlugin.min.js',
            'ScrollSmoother' => 'ScrollSmoother.min.js',
            'GSDevTools' => 'GSDevTools.min.js',
            'InertiaPlugin' => 'InertiaPlugin.min.js',
            'ScrambleTextPlugin' => 'ScrambleTextPlugin.min.js',
            'CustomBounce' => 'CustomBounce.min.js',
            'CustomWiggle' => 'CustomWiggle.min.js',
            'MotionPathHelper' => 'MotionPathHelper.min.js',
            'Physics2DPlugin' => 'Physics2DPlugin.min.js',
            'PhysicsPropsPlugin' => 'PhysicsPropsPlugin.min.js'
        );
        
        foreach ($pro_plugins as $name => $file) {
            $option_key = 'load_' . strtolower($name);
            if (get_option('syntekpro_anim_' . $option_key) === 'yes') {
                wp_enqueue_script(
                    'syntekpro-' . strtolower($name),
                    SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/gsap/minified/' . $file,
                    array('syntekpro-gsap'),
                    SYNTEKPRO_ANIM_VERSION,
                    true
                );
            }
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Load on all Syntekpro admin pages
        if (strpos($hook, 'syntekpro-animations') === false) {
            return;
        }
        
        // Core GSAP for admin preview
        wp_enqueue_script(
            'syntekpro-gsap-admin',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/gsap/minified/gsap.min.js',
            array(),
            SYNTEKPRO_ANIM_VERSION,
            true
        );
        
        wp_enqueue_style(
            'syntekpro-admin-style',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            SYNTEKPRO_ANIM_VERSION
        );
        
        wp_enqueue_script(
            'syntekpro-admin-script',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SYNTEKPRO_ANIM_VERSION,
            true
        );
        
        // Load interactive preview on builder and timeline pages
        if (strpos($hook, 'syntekpro-animations-builder') !== false || 
            strpos($hook, 'syntekpro-animations-timeline') !== false ||
            strpos($hook, 'syntekpro-animations-presets') !== false) {
            
            // jQuery UI for sortable
            wp_enqueue_script('jquery-ui-sortable');
            
            // Admin preview script
            wp_enqueue_script(
                'syntekpro-admin-preview',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/admin-preview.js',
                array('jquery', 'syntekpro-gsap-admin'),
                SYNTEKPRO_ANIM_VERSION,
                true
            );
        }
    }
    
    /**
     * Get plugin options
     */
    private function get_options() {
        return array(
            'load_gsap' => get_option('syntekpro_anim_load_gsap', 'yes'),
            'load_scrolltrigger' => get_option('syntekpro_anim_load_scrolltrigger', 'yes'),
            'smooth_scroll' => get_option('syntekpro_anim_smooth_scroll', 'no'),
            'enable_developer_mode' => get_option('syntekpro_anim_enable_developer_mode', 'no')
        );
    }
}

// Initialize
new Syntekpro_Animations_Enqueue();