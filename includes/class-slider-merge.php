<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Loads SyntekPro Slider as an embedded module inside SyntekPro Animations.
 */
class Syntekpro_Slider_Merge {

    /** @var string */
    private $slider_dir = '';

    /** @var string */
    private $slider_file = '';

    /** @var bool */
    private $uses_internal_module = false;

    /**
     * Bootstrap merged slider module.
     */
    public function bootstrap() {
        if (defined('SPSLIDER_VERSION') || class_exists('SPSLIDER_Core', false)) {
            return;
        }

        $this->resolve_slider_path();
        if ($this->slider_dir === '' || !is_dir($this->slider_dir)) {
            return;
        }

        if ($this->is_standalone_slider_active()) {
            return;
        }

        if ($this->uses_internal_module) {
            require_once $this->slider_file;
            return;
        }

        $this->define_slider_constants();
        $this->load_slider_dependencies();
        $this->boot_slider_runtime();
    }

    /**
     * Resolve sibling slider plugin path robustly for different folder casing.
     */
    private function resolve_slider_path() {
        $internal_bootstrap = SYNTEKPRO_ANIM_PLUGIN_DIR . 'modules/syntekpro-slider/module-bootstrap.php';
        if (file_exists($internal_bootstrap)) {
            $this->uses_internal_module = true;
            $this->slider_file = $internal_bootstrap;
            $this->slider_dir = trailingslashit(dirname($internal_bootstrap));
            return;
        }

        $plugins_dir = dirname(rtrim(SYNTEKPRO_ANIM_PLUGIN_DIR, '/\\'));

        $candidates = array(
            $plugins_dir . DIRECTORY_SEPARATOR . 'SyntekPro-Slider',
            $plugins_dir . DIRECTORY_SEPARATOR . 'syntekpro-slider',
        );

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                $this->slider_dir = trailingslashit($candidate);
                $main_file = $candidate . DIRECTORY_SEPARATOR . 'syntekpro-slider.php';
                if (file_exists($main_file)) {
                    $this->slider_file = $main_file;
                }
                return;
            }
        }
    }

    /**
     * Skip merged runtime when standalone slider plugin is already active.
     *
     * @return bool
     */
    private function is_standalone_slider_active() {
        if ($this->slider_file === '' || !function_exists('get_option')) {
            return false;
        }

        $basename = plugin_basename($this->slider_file);
        $active_plugins = (array) get_option('active_plugins', array());
        if (in_array($basename, $active_plugins, true)) {
            return true;
        }

        if (function_exists('is_multisite') && is_multisite()) {
            $sitewide = (array) get_site_option('active_sitewide_plugins', array());
            if (isset($sitewide[$basename])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Define slider constants when running as embedded module.
     */
    private function define_slider_constants() {
        if (!defined('SPSLIDER_VERSION')) {
            define('SPSLIDER_VERSION', '1.5.0');
        }
        if (!defined('SPSLIDER_DIR')) {
            define('SPSLIDER_DIR', $this->slider_dir);
        }
        if (!defined('SPSLIDER_URL')) {
            define('SPSLIDER_URL', trailingslashit(plugins_url(basename(rtrim($this->slider_dir, '/\\')))));
        }
        if (!defined('SPSLIDER_FILE')) {
            define('SPSLIDER_FILE', $this->slider_file !== '' ? $this->slider_file : $this->slider_dir . 'syntekpro-slider.php');
        }
        if (!defined('SPSLIDER_BASENAME')) {
            define('SPSLIDER_BASENAME', plugin_basename(SPSLIDER_FILE));
        }
    }

    /**
     * Load all slider classes required by SPSLIDER_Core.
     */
    private function load_slider_dependencies() {
        $files = array(
            'class-activator',
            'class-deactivator',
            'class-database',
            'class-templates',
            'class-dynamic-content',
            'class-analytics',
            'class-image-optimizer',
            'class-shortcode',
            'class-ajax',
            'class-api',
            'class-updater',
            'class-cache',
            'class-revisions',
            'class-webhooks',
            'class-white-label',
            'class-conversions',
            'class-export',
            'class-scheduler',
            'class-audit-log',
            'class-permissions',
            'class-personalisation',
            'class-ab-test',
            'class-migrations',
            'class-syntekpro-slider',
        );

        foreach ($files as $file) {
            $path = SPSLIDER_DIR . 'includes/' . $file . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }

        if (defined('WP_CLI') && WP_CLI) {
            $cli_path = SPSLIDER_DIR . 'includes/class-cli.php';
            if (file_exists($cli_path)) {
                require_once $cli_path;
                if (class_exists('SPSLIDER_CLI', false)) {
                    SPSLIDER_CLI::register();
                }
            }
        }
    }

    /**
     * Start slider runtime when classes are loaded.
     */
    private function boot_slider_runtime() {
        if (!class_exists('SPSLIDER_Core', false)) {
            return;
        }

        if (class_exists('SPSLIDER_Migrations', false)) {
            SPSLIDER_Migrations::run();
        }

        if (function_exists('load_plugin_textdomain')) {
            load_plugin_textdomain('syntekpro-slider', false, dirname(SPSLIDER_BASENAME) . '/languages');
        }

        $core = new SPSLIDER_Core();
        $core->run();
    }
}
