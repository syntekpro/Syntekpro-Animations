<?php
defined( 'ABSPATH' ) || exit;

/**
 * Edge caching: generate static HTML snapshots of sliders and serve from cache.
 * Hydrates JS controls asynchronously for instant first paint.
 */
class SPSLIDER_Cache {

    const CACHE_GROUP = 'spslider';

    /**
     * Get or build a cached slider HTML snapshot.
     */
    public static function get_cached_html( $slider_id, $render_callback ) {
        $global = get_option( 'spslider_global_settings', [] );
        if ( empty( $global['enable_cache'] ) ) {
            return call_user_func( $render_callback );
        }

        $cache_key = 'spslider_html_' . (int) $slider_id;
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached . '<!-- spslider:cached -->';
        }

        $html = call_user_func( $render_callback );
        set_transient( $cache_key, $html, HOUR_IN_SECONDS );
        return $html;
    }

    /**
     * Invalidate cache for a specific slider.
     */
    public static function invalidate( $slider_id ) {
        delete_transient( 'spslider_html_' . (int) $slider_id );
    }

    /**
     * Invalidate all slider caches.
     */
    public static function flush_all() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_spslider_html_%' OR option_name LIKE '_transient_timeout_spslider_html_%'"
        );
    }

    /**
     * Hook into slider save to auto-invalidate cache.
     */
    public static function init() {
        add_action( 'spslider_after_save', [ __CLASS__, 'invalidate' ] );
    }
}
