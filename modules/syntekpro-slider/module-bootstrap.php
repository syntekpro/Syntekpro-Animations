<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('SPSLIDER_VERSION')) {
    define('SPSLIDER_VERSION', '1.5.0');
}
if (!defined('SPSLIDER_DIR')) {
    define('SPSLIDER_DIR', trailingslashit(__DIR__));
}
if (!defined('SPSLIDER_URL')) {
    define('SPSLIDER_URL', trailingslashit(plugins_url('modules/syntekpro-slider', SYNTEKPRO_ANIM_PLUGIN_FILE)));
}
if (!defined('SPSLIDER_FILE')) {
    define('SPSLIDER_FILE', __FILE__);
}
if (!defined('SPSLIDER_BASENAME')) {
    define('SPSLIDER_BASENAME', plugin_basename(SPSLIDER_FILE));
}

foreach (array(
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
) as $file) {
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

if (class_exists('SPSLIDER_Migrations', false)) {
    SPSLIDER_Migrations::run();
}

if (function_exists('load_plugin_textdomain')) {
    load_plugin_textdomain('syntekpro-slider', false, dirname(plugin_basename(SYNTEKPRO_ANIM_PLUGIN_FILE)) . '/modules/syntekpro-slider/languages');
}

if (class_exists('SPSLIDER_Core', false)) {
    (new SPSLIDER_Core())->run();
}
