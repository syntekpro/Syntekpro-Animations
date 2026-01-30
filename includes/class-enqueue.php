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
        add_action('enqueue_block_assets', array($this, 'enqueue_block_frontend_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        $options = $this->get_options();
        $debug_overlay_enabled = $this->is_debug_overlay_enabled($options);
        $current_role = $this->get_current_user_role();
        
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
        
        // Ensure animations.js loads for blocks and shortcodes (CSS mode friendly)
        $script_deps = array();
        if ($options['load_gsap'] === 'yes') {
            $script_deps[] = 'syntekpro-gsap';
            if ($options['load_scrolltrigger'] === 'yes') {
                $script_deps[] = 'syntekpro-scrolltrigger';
            }
        }

        // Register with conditional deps; enqueue unconditionally for shortcode and block output
        wp_register_script(
            'syntekpro-animations-frontend',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/animations.js',
            $script_deps,
            SYNTEKPRO_ANIM_VERSION,
            true
        );

        wp_enqueue_script('syntekpro-animations-frontend');

        // Pass data to JavaScript (for animations script when it loads)
        wp_localize_script('syntekpro-animations-frontend', 'syntekproAnim', array(
            'isPro' => syntekpro_animations()->is_pro_active(),
            'smoothScroll' => $options['smooth_scroll'] === 'yes',
            'developerMode' => $options['enable_developer_mode'] === 'yes',
            'engine' => $options['engine'],
            'debugOverlay' => $debug_overlay_enabled,
            'debugOverlayPersistRole' => $options['debug_overlay_persist_role'] === 'yes',
            'debugOverlayRole' => $current_role,
            'silenceConsole' => $options['silence_console'] === 'yes'
        ));

        if ($debug_overlay_enabled) {
            wp_enqueue_style(
                'syntekpro-debug-overlay',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'analytics/css/debug-overlay.css',
                array(),
                SYNTEKPRO_ANIM_VERSION
            );

            wp_enqueue_script(
                'syntekpro-debug-overlay',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'analytics/js/debug-overlay.js',
                array(),
                SYNTEKPRO_ANIM_VERSION,
                true
            );
        }
        
        // Frontend styles
        wp_enqueue_style(
            'syntekpro-animations-style',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/style.css',
            array(),
            SYNTEKPRO_ANIM_VERSION
        );
    }

    /**
     * Ensure block-rendered animations enqueue on frontend even without shortcodes
     */
    public function enqueue_block_frontend_assets() {
        if (is_admin()) {
            return;
        }
        // Reuse same handles; registration happens in enqueue_frontend_assets on wp_enqueue_scripts
        if (!wp_script_is('syntekpro-animations-frontend', 'enqueued')) {
            wp_enqueue_script('syntekpro-animations-frontend');
        }
        if (!wp_style_is('syntekpro-animations-style', 'enqueued')) {
            wp_enqueue_style('syntekpro-animations-style');
        }
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
        $is_builder_page = strpos($hook, 'syntekpro-animations-builder') !== false;
        $is_timeline_page = strpos($hook, 'syntekpro-animations-timeline') !== false;
        $is_presets_page = strpos($hook, 'syntekpro-animations-presets') !== false;

        if ($is_builder_page || $is_timeline_page || $is_presets_page) {
            
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

            // Builder timeline stubs (Phase 2 scaffold)
            wp_enqueue_style(
                'syntekpro-builder-timeline-style',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'builder/css/timeline.css',
                array(),
                SYNTEKPRO_ANIM_VERSION
            );

            wp_enqueue_script(
                'syntekpro-builder-timeline-script',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'builder/js/timeline.js',
                array(),
                SYNTEKPRO_ANIM_VERSION,
                true
            );

            if ($is_presets_page) {
                wp_enqueue_script(
                    'syntekpro-admin-presets',
                    SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/admin-presets.js',
                    array('jquery'),
                    SYNTEKPRO_ANIM_VERSION,
                    true
                );

                $all_presets = apply_filters('syntekpro_animation_presets', array());
                wp_localize_script('syntekpro-admin-presets', 'syntekproAdminPresets', array(
                    'presets' => $all_presets,
                    'nonce' => wp_create_nonce('syntekpro_presets_import')
                ));
            }
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
            'enable_developer_mode' => get_option('syntekpro_anim_enable_developer_mode', 'no'),
            'engine' => get_option('syntekpro_anim_engine', 'auto'),
            'debug_overlay' => get_option('syntekpro_anim_debug_overlay', 'no'),
            'debug_overlay_persist_role' => get_option('syntekpro_anim_debug_overlay_persist_role', 'no'),
            'silence_console' => get_option('syntekpro_anim_silence_console', 'no')
        );
    }

    /**
     * Determine if debug overlay assets should load
     */
    private function is_debug_overlay_enabled($options) {
        $developer_mode = isset($options['enable_developer_mode']) && $options['enable_developer_mode'] === 'yes';
        $explicit_opt_in = isset($options['debug_overlay']) && $options['debug_overlay'] === 'yes';
        $query_flag = isset($_GET['syntekpro_debug']) && $_GET['syntekpro_debug'] !== '0';
        return $developer_mode || $explicit_opt_in || $query_flag;
    }

    /**
     * Get current user role (first role or guest)
     */
    private function get_current_user_role() {
        if (!is_user_logged_in()) {
            return 'guest';
        }
        $user = wp_get_current_user();
        if (!empty($user->roles)) {
            return $user->roles[0];
        }
        return 'guest';
    }
}

// Initialize
new Syntekpro_Animations_Enqueue();