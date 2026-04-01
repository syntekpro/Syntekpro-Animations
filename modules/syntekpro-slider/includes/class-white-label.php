<?php
defined( 'ABSPATH' ) || exit;

/**
 * White-label mode: remove all SyntekPro branding from admin and frontend.
 * Agencies can inject their own logo, name, colours, and support URL.
 */
class SPSLIDER_White_Label {

    const OPTION_KEY = 'spslider_white_label';

    /**
     * Get white-label settings with defaults.
     */
    public static function get_settings() {
        return wp_parse_args( get_option( self::OPTION_KEY, [] ), [
            'enabled'      => false,
            'plugin_name'  => 'SyntekPro Slider',
            'author_name'  => 'SyntekPro',
            'author_url'   => 'https://syntekpro.com',
            'support_url'  => 'https://plugins.syntekpro.com/support',
            'logo_url'     => '',
            'icon_url'     => '',
            'primary_color'=> '#991b1b',
            'hide_about'   => false,
        ] );
    }

    /**
     * Save white-label settings.
     */
    public static function save( $data ) {
        $settings = [
            'enabled'       => ! empty( $data['enabled'] ),
            'plugin_name'   => sanitize_text_field( $data['plugin_name'] ?? '' ),
            'author_name'   => sanitize_text_field( $data['author_name'] ?? '' ),
            'author_url'    => esc_url_raw( $data['author_url'] ?? '' ),
            'support_url'   => esc_url_raw( $data['support_url'] ?? '' ),
            'logo_url'      => esc_url_raw( $data['logo_url'] ?? '' ),
            'icon_url'      => esc_url_raw( $data['icon_url'] ?? '' ),
            'primary_color' => sanitize_hex_color( $data['primary_color'] ?? '' ) ?: '#991b1b',
            'hide_about'    => ! empty( $data['hide_about'] ),
        ];
        update_option( self::OPTION_KEY, $settings );
    }

    /**
     * Get the display name for the plugin (respecting white-label).
     */
    public static function name() {
        $s = self::get_settings();
        return $s['enabled'] && $s['plugin_name'] ? $s['plugin_name'] : 'SyntekPro Slider';
    }

    /**
     * Get the logo URL (respecting white-label).
     */
    public static function logo_url() {
        $s = self::get_settings();
        return $s['enabled'] && $s['logo_url'] ? $s['logo_url'] : SPSLIDER_URL . 'assets/img/SyntekPro Slider Logo.png';
    }

    /**
     * Get the support URL.
     */
    public static function support_url() {
        $s = self::get_settings();
        return $s['enabled'] && $s['support_url'] ? $s['support_url'] : 'https://plugins.syntekpro.com/support';
    }

    /**
     * Filter plugin row meta in the plugins list to reflect white-label.
     */
    public static function init() {
        $s = self::get_settings();
        if ( ! $s['enabled'] ) return;

        // Override plugin header info in plugins list
        add_filter( 'all_plugins', [ __CLASS__, 'filter_plugin_meta' ] );
    }

    /**
     * Modify plugin metadata shown in the plugins list.
     */
    public static function filter_plugin_meta( $plugins ) {
        $s = self::get_settings();
        if ( ! $s['enabled'] ) return $plugins;

        $key = SPSLIDER_BASENAME;
        if ( isset( $plugins[ $key ] ) ) {
            if ( $s['plugin_name'] )  $plugins[ $key ]['Name']      = $s['plugin_name'];
            if ( $s['plugin_name'] )  $plugins[ $key ]['Title']     = $s['plugin_name'];
            if ( $s['author_name'] )  $plugins[ $key ]['Author']    = $s['author_name'];
            if ( $s['author_name'] )  $plugins[ $key ]['AuthorName']= $s['author_name'];
            if ( $s['author_url'] )   $plugins[ $key ]['AuthorURI'] = $s['author_url'];
            if ( $s['author_url'] )   $plugins[ $key ]['PluginURI'] = $s['author_url'];
        }
        return $plugins;
    }
}
