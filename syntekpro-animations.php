<?php
/**
 * Plugin Name: Syntekpro Animations
 * Plugin URI: https://syntekpro.com/animations
 * Description: Professional high-performance animation engine for WordPress. Create stunning scroll-triggered animations, timeline sequences, and advanced visual effects with a complete feature set.
 * Version: 2.2.4
 * Author: Syntekpro
 * Author URI: https://syntekpro.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: syntekpro-animations
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SYNTEKPRO_ANIM_VERSION', '2.2.4');
define('SYNTEKPRO_ANIM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SYNTEKPRO_ANIM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SYNTEKPRO_ANIM_PLUGIN_FILE', __FILE__);

/**
 * Main Plugin Class
 */
class Syntekpro_Animations {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/class-enqueue.php';
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/class-animation-presets.php';
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/class-admin.php';
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/class-gutenberg.php';
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/class-advanced-features.php';
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/class-help-system.php';
        
        // Load block system for new modular blocks
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/blocks/class-base-block.php';
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/blocks/class-block-registry.php';
        
        require_once SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/class-pro-features.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Declare compatibility with the WordPress Consent API
        add_action('plugins_loaded', array($this, 'declare_consent_api_compatibility'), 5);
        
        // Initialize block system
        add_action('plugins_loaded', array($this, 'init_block_system'));
        
        // Add settings link
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links'));
    }
    
    /**
     * Initialize block system
     */
    public function init_block_system() {
        // The block registry will load and instantiate all block classes
        // which will hook themselves to the 'init' action for registration
        if (class_exists('Syntekpro_Block_Registry')) {
            Syntekpro_Block_Registry::get_instance();
        }
    }
    
    /**
     * Backward-compatible capability flag.
     */
    public function is_pro_active() {
        // Keep legacy integrations functional while all features are available.
        return true;
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $defaults = array(
            'load_gsap' => 'yes',
            'load_scrolltrigger' => 'yes',
            'smooth_scroll' => 'no',
            'enable_developer_mode' => 'no'
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option('syntekpro_anim_' . $key) === false) {
                add_option('syntekpro_anim_' . $key, $value);
            }
        }
        
        // Create upload directory for custom animations
        $upload_dir = wp_upload_dir();
        $custom_dir = $upload_dir['basedir'] . '/syntekpro-animations';
        if (!file_exists($custom_dir)) {
            wp_mkdir_p($custom_dir);
        }
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'syntekpro-animations',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Declare WordPress Consent API compatibility in a backward-safe way
     */
    public function declare_consent_api_compatibility() {
        if (!function_exists('wp_set_consent_type')) {
            return;
        }

        if (function_exists('wp_get_consent_type') && !empty(wp_get_consent_type())) {
            return;
        }

        wp_set_consent_type('optin');
    }
    
    /**
     * Add settings link to plugins page
     */
    public function add_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=syntekpro-animations'),
            __('Settings', 'syntekpro-animations')
        );
        
        array_unshift($links, $settings_link);
        
        return $links;
    }
}

/**
 * Initialize the plugin
 */
function syntekpro_animations() {
    return Syntekpro_Animations::get_instance();
}

// Start the plugin
syntekpro_animations();