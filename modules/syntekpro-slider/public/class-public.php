<?php
defined( 'ABSPATH' ) || exit;

/**
 * Frontend asset enqueue and per-page slider tracking.
 */
class SPSLIDER_Public {

    /** Slider IDs used on current page request */
    private static $used_sliders = [];

    public static function mark_slider_used( $id ) {
        self::$used_sliders[] = (int) $id;
    }

    public function enqueue_scripts() {
        // Always register scripts; only enqueue when a slider is on the page
        wp_register_style(
            'spslider-public',
            SPSLIDER_URL . 'public/css/public.css',
            [],
            SPSLIDER_VERSION
        );
        wp_register_script(
            'spslider-public',
            SPSLIDER_URL . 'public/js/public.js',
            [],
            SPSLIDER_VERSION,
            true
        );

        // If in editor context always enqueue
        if ( is_admin() ) return;

        // Enqueue on every page; the JS will self-initialise only if containers exist.
        // Selective enqueueing can be toggled via global settings.
        $global = get_option( 'spslider_global_settings', [] );
        if ( ! empty( $global['optimize_assets'] ) ) {
            // With optimise_assets, we rely on mark_slider_used() called during shortcode
            // rendering and then late-enqueue in wp_footer.
            add_action( 'wp_footer', [ $this, 'maybe_enqueue_footer' ], 1 );
        } else {
            wp_enqueue_style(  'spslider-public' );
            wp_enqueue_script( 'spslider-public' );
            wp_localize_script( 'spslider-public', 'SPSLIDER_PUBLIC', $this->script_data() );
        }
    }

    public function maybe_enqueue_footer() {
        if ( empty( self::$used_sliders ) ) return;
        wp_enqueue_style(  'spslider-public' );
        wp_enqueue_script( 'spslider-public' );
        wp_localize_script( 'spslider-public', 'SPSLIDER_PUBLIC', $this->script_data() );
    }

    private function script_data() {
        return [
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'rest_url'    => esc_url_raw( rest_url( 'syntekpro-slider/v1' ) ),
            'nonce'       => wp_create_nonce( 'spslider_public_nonce' ),
            'analytics'   => ! empty( get_option( 'spslider_global_settings', [] )['analytics_enabled'] ),
            'lazy_load'   => ! empty( get_option( 'spslider_global_settings', [] )['lazy_load'] ),
        ];
    }
}
