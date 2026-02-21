<?php
/**
 * Uninstall handler for Syntekpro Animations.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$option_names = array(
    'syntekpro_anim_load_gsap',
    'syntekpro_anim_load_scrolltrigger',
    'syntekpro_anim_smooth_scroll',
    'syntekpro_anim_enable_developer_mode',
    'syntekpro_anim_disable_mobile',
    'syntekpro_anim_lazy_load',
    'syntekpro_anim_debug_overlay',
    'syntekpro_anim_debug_overlay_persist_role',
    'syntekpro_anim_silence_console',
    'syntekpro_anim_engine',
    'syntekpro_anim_reduced_motion',
    'syntekpro_anim_debug_mode',
    'syntekpro_anim_default_duration',
    'syntekpro_anim_default_ease',
    'syntekpro_anim_load_flip',
    'syntekpro_anim_load_observer',
    'syntekpro_anim_load_scrolltoplugin',
    'syntekpro_anim_load_textplugin',
    'syntekpro_anim_load_draggable',
    'syntekpro_anim_load_motionpathplugin',
    'syntekpro_anim_load_easepack',
    'syntekpro_anim_load_customease',
    'syntekpro_anim_load_splittext',
    'syntekpro_anim_load_morphsvgplugin',
    'syntekpro_anim_load_drawsvgplugin',
    'syntekpro_anim_load_scrollsmoother',
    'syntekpro_anim_load_gsdevtools',
    'syntekpro_anim_load_inertiaplugin',
    'syntekpro_anim_load_scrambletextplugin',
    'syntekpro_anim_load_custombounce',
    'syntekpro_anim_load_customwiggle',
    'syntekpro_anim_license_key',
    'syntekpro_anim_license_status',
    'syntekpro_anim_license_expires',
);

foreach ($option_names as $option_name) {
    delete_option($option_name);
    if (function_exists('is_multisite') && is_multisite()) {
        delete_site_option($option_name);
    }
}

if (!empty($upload_dir = wp_upload_dir()) && !empty($upload_dir['basedir'])) {
    $custom_dir = trailingslashit($upload_dir['basedir']) . 'syntekpro-animations';
    if (is_dir($custom_dir)) {
    $iterator = new RecursiveDirectoryIterator($custom_dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $fileinfo) {
        if ($fileinfo->isDir()) {
            rmdir($fileinfo->getRealPath());
        } else {
            unlink($fileinfo->getRealPath());
        }
    }

    rmdir($custom_dir);
}
