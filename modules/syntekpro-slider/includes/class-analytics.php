<?php
defined( 'ABSPATH' ) || exit;

/**
 * Analytics: track events, aggregate stats, export CSV, push to GA4.
 */
class SPSLIDER_Analytics {

    /**
     * Record an analytics event.
     * Called via AJAX from the frontend slider JS.
     */
    public static function track( $slider_id, $event_type, $slide_id = null, $layer_id = null, $meta = [] ) {
        global $wpdb;

        $global = get_option( 'spslider_global_settings', [] );
        if ( empty( $global['analytics_enabled'] ) ) return;

        $session_id = sanitize_text_field(
            wp_unslash( $_COOKIE['spslider_session'] ?? '' )
        );
        if ( ! $session_id ) {
            $session_id = wp_generate_uuid4();
            // Session cookie is set on the frontend via JS — this is a fallback
        }

        $wpdb->insert(
            $wpdb->prefix . 'spslider_analytics',
            [
                'slider_id'  => (int) $slider_id,
                'slide_id'   => $slide_id ? (int) $slide_id : null,
                'layer_id'   => $layer_id ? (int) $layer_id : null,
                'event_type' => sanitize_key( $event_type ),
                'session_id' => substr( $session_id, 0, 64 ),
                'meta'       => ! empty( $meta ) ? wp_json_encode( $meta ) : null,
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%d', '%s', '%s', '%s', '%s' ]
        );
    }

    /**
     * Get aggregated stats for a slider.
     */
    public static function get_stats( $slider_id, $days = 30 ) {
        global $wpdb;
        $table     = $wpdb->prefix . 'spslider_analytics';
        $slider_id = (int) $slider_id;
        $from      = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        // Total impressions (slider_view events)
        $total_views = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE slider_id=%d AND event_type='slider_view' AND created_at>=%s",
            $slider_id, $from
        ) );

        // Unique sessions
        $unique_sessions = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id) FROM {$table} WHERE slider_id=%d AND event_type='slider_view' AND created_at>=%s",
            $slider_id, $from
        ) );

        // Per-slide views
        $slide_views = $wpdb->get_results( $wpdb->prepare(
            "SELECT slide_id, COUNT(*) as views FROM {$table}
             WHERE slider_id=%d AND event_type='slide_view' AND created_at>=%s
             GROUP BY slide_id ORDER BY views DESC",
            $slider_id, $from
        ) );

        // Layer click-through
        $layer_clicks = $wpdb->get_results( $wpdb->prepare(
            "SELECT layer_id, COUNT(*) as clicks FROM {$table}
             WHERE slider_id=%d AND event_type='layer_click' AND created_at>=%s
             GROUP BY layer_id ORDER BY clicks DESC",
            $slider_id, $from
        ) );

        // Navigation method (swipe vs click)
        $nav_swipe = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE slider_id=%d AND event_type='swipe_nav' AND created_at>=%s",
            $slider_id, $from
        ) );
        $nav_click = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE slider_id=%d AND event_type='click_nav' AND created_at>=%s",
            $slider_id, $from
        ) );

        // Daily views chart (last 30 days)
        $daily = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(created_at) as day, COUNT(*) as views
             FROM {$table}
             WHERE slider_id=%d AND event_type='slider_view' AND created_at>=%s
             GROUP BY DATE(created_at) ORDER BY day ASC",
            $slider_id, $from
        ) );

        return compact( 'total_views', 'unique_sessions', 'slide_views', 'layer_clicks', 'nav_swipe', 'nav_click', 'daily' );
    }

    /**
     * Export analytics to CSV.
     */
    public static function export_csv( $slider_id, $days = 30 ) {
        global $wpdb;
        $table     = $wpdb->prefix . 'spslider_analytics';
        $slider_id = (int) $slider_id;
        $from      = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, slider_id, slide_id, layer_id, event_type, session_id, meta, created_at
             FROM {$table} WHERE slider_id=%d AND created_at>=%s ORDER BY created_at DESC",
            $slider_id, $from
        ), ARRAY_A );

        if ( ob_get_length() ) ob_clean();

        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="spslider-analytics-' . $slider_id . '.csv"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, [ 'ID', 'Slider ID', 'Slide ID', 'Layer ID', 'Event', 'Session', 'Meta', 'Date/Time' ] );
        foreach ( $rows as $row ) {
            fputcsv( $out, array_values( $row ) );
        }
        fclose( $out );
        exit;
    }

    /**
     * Push event to GA4 Measurement Protocol.
     * Called server-side when analytics_ga4_id is configured.
     */
    public static function push_ga4( $event_name, $params ) {
        $settings = get_option( 'spslider_global_settings', [] );
        $api_secret  = sanitize_text_field( $settings['ga4_api_secret'] ?? '' );
        $measurement_id = sanitize_text_field( $settings['ga4_measurement_id'] ?? '' );

        if ( ! $api_secret || ! $measurement_id ) return;

        $body = wp_json_encode( [
            'client_id' => 'spslider.' . time(),
            'events'    => [ [ 'name' => sanitize_key( $event_name ), 'params' => $params ] ],
        ] );

        wp_remote_post(
            "https://www.google-analytics.com/mp/collect?measurement_id={$measurement_id}&api_secret={$api_secret}",
            [
                'body'    => $body,
                'headers' => [ 'Content-Type' => 'application/json' ],
                'timeout' => 5,
            ]
        );
    }

    /**
     * Prune old analytics data (runs on a weekly cron).
     */
    public static function prune( $days = 365 ) {
        global $wpdb;
        $before = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}spslider_analytics WHERE created_at < %s",
            $before
        ) );
    }
}
