<?php
defined( 'ABSPATH' ) || exit;

/**
 * Conversion-goal tracking: define a goal URL or event per slider, track conversions.
 * Integrates with built-in analytics and optionally pushes to GA4.
 */
class SPSLIDER_Conversions {

    /**
     * Create the conversion goals table.
     */
    public static function create_table() {
        global $wpdb;
        $c = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE {$wpdb->prefix}spslider_conversions (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            slider_id   BIGINT UNSIGNED NOT NULL,
            session_id  VARCHAR(64)     NOT NULL DEFAULT '',
            goal_type   VARCHAR(50)     NOT NULL DEFAULT 'url',
            goal_value  VARCHAR(500)    NOT NULL DEFAULT '',
            converted   TINYINT(1)      NOT NULL DEFAULT 0,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY slider_id (slider_id),
            KEY session_id (session_id)
        ) $c;" );
    }

    /**
     * Get goal config for a slider (stored in slider settings as 'conversion_goal').
     *
     * Goal structure: [ 'type' => 'url|event|click', 'value' => '/thank-you', 'label' => 'Purchase' ]
     */
    public static function get_goal( $slider_id ) {
        $slider = SPSLIDER_Database::get_slider( $slider_id );
        if ( ! $slider ) return null;
        return $slider->settings['conversion_goal'] ?? null;
    }

    /**
     * Record a conversion for a slider (called from frontend).
     */
    public static function record( $slider_id, $session_id, $goal_type = 'url', $goal_value = '' ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'spslider_conversions',
            [
                'slider_id'  => (int) $slider_id,
                'session_id' => sanitize_text_field( $session_id ),
                'goal_type'  => sanitize_key( $goal_type ),
                'goal_value' => sanitize_text_field( $goal_value ),
                'converted'  => 1,
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s', '%d', '%s' ]
        );

        // Also push to GA4 if configured
        SPSLIDER_Analytics::push_ga4( 'spslider_conversion', [
            'slider_id' => $slider_id,
            'goal_type' => $goal_type,
            'goal_value'=> $goal_value,
        ] );
    }

    /**
     * Get conversion stats for a slider.
     */
    public static function get_stats( $slider_id, $days = 30 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spslider_conversions';
        $from  = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE slider_id = %d AND created_at >= %s",
            (int) $slider_id, $from
        ) );

        $unique = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id) FROM {$table} WHERE slider_id = %d AND created_at >= %s",
            (int) $slider_id, $from
        ) );

        // Get slider views for conversion rate calculation
        $views_stats  = SPSLIDER_Analytics::get_stats( $slider_id, $days );
        $total_views  = $views_stats['total_views'] ?? 0;
        $rate         = $total_views > 0 ? round( ( $unique / $total_views ) * 100, 2 ) : 0;

        $daily = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(created_at) as day, COUNT(*) as conversions
             FROM {$table}
             WHERE slider_id = %d AND created_at >= %s
             GROUP BY DATE(created_at) ORDER BY day ASC",
            (int) $slider_id, $from
        ) );

        return compact( 'total', 'unique', 'rate', 'daily' );
    }

    /**
     * Frontend: check if current page URL matches a slider's goal URL.
     * Outputs a script tag that fires the conversion if matched.
     */
    public static function check_page_goals() {
        global $wpdb;
        $settings = get_option( 'spslider_global_settings', [] );
        if ( empty( $settings['analytics_enabled'] ) ) return;

        // Get all sliders with URL-type conversion goals
        $sliders = $wpdb->get_results(
            "SELECT id, settings FROM {$wpdb->prefix}spslider_sliders WHERE status = 1"
        );

        $goals = [];
        foreach ( $sliders as $slider ) {
            $s = json_decode( $slider->settings, true ) ?: [];
            $goal = $s['conversion_goal'] ?? null;
            if ( $goal && ( $goal['type'] ?? '' ) === 'url' && ! empty( $goal['value'] ) ) {
                $goals[] = [
                    'slider_id' => (int) $slider->id,
                    'url'       => $goal['value'],
                ];
            }
        }

        if ( empty( $goals ) ) return;

        // Output inline script that checks current URL against goals
        add_action( 'wp_footer', function () use ( $goals ) {
            $json = wp_json_encode( $goals );
            echo "<script>!function(){var g={$json},p=location.pathname;g.forEach(function(o){if(p.indexOf(o.url)!==-1){var s=document.cookie.match(/spslider_session=([^;]+)/);if(s){fetch('" . esc_url( admin_url( 'admin-ajax.php' ) ) . "',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=spslider_convert&slider_id='+o.slider_id+'&session_id='+s[1]+'&nonce=" . esc_js( wp_create_nonce( 'spslider_public_nonce' ) ) . "'})}}});}();</script>\n";
        }, 99 );
    }
}
