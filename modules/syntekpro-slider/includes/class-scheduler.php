<?php
defined( 'ABSPATH' ) || exit;

/**
 * Scheduled slide publishing: publish/expire slides at specific dates.
 * Uses WP Cron to transition slide visibility at the scheduled time.
 */
class SPSLIDER_Scheduler {

    const CRON_HOOK = 'spslider_scheduled_check';

    /**
     * Register hooks.
     */
    public static function init() {
        add_action( self::CRON_HOOK, [ __CLASS__, 'process_scheduled' ] );

        // Schedule the cron event if not already
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'five_minutes', self::CRON_HOOK );
        }

        // Register a 5-minute interval
        add_filter( 'cron_schedules', [ __CLASS__, 'add_cron_interval' ] );
    }

    /**
     * Add custom cron interval.
     */
    public static function add_cron_interval( $schedules ) {
        $schedules['five_minutes'] = [
            'interval' => 300,
            'display'  => __( 'Every 5 minutes', 'syntekpro-slider' ),
        ];
        return $schedules;
    }

    /**
     * Process scheduled slides: publish pending and expire past-due.
     */
    public static function process_scheduled() {
        global $wpdb;
        $table = $wpdb->prefix . 'spslider_slides';
        $now   = current_time( 'mysql' );

        // Publish slides whose publish_at has passed and are currently disabled
        $to_publish = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, slider_id FROM {$table} WHERE publish_at IS NOT NULL AND publish_at <= %s AND status = 0",
            $now
        ) );

        foreach ( $to_publish as $slide ) {
            $wpdb->update(
                $table,
                [ 'status' => 1, 'publish_at' => null ],
                [ 'id' => (int) $slide->id ],
                [ '%d', '%s' ],
                [ '%d' ]
            );
            SPSLIDER_Cache::invalidate( (int) $slide->slider_id );
            do_action( 'spslider_slide_published', (int) $slide->id, (int) $slide->slider_id );
        }

        // Expire slides whose expire_at has passed
        $to_expire = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, slider_id FROM {$table} WHERE expire_at IS NOT NULL AND expire_at <= %s AND status = 1",
            $now
        ) );

        foreach ( $to_expire as $slide ) {
            $wpdb->update(
                $table,
                [ 'status' => 0, 'expire_at' => null ],
                [ 'id' => (int) $slide->id ],
                [ '%d', '%s' ],
                [ '%d' ]
            );
            SPSLIDER_Cache::invalidate( (int) $slide->slider_id );
            do_action( 'spslider_slide_expired', (int) $slide->id, (int) $slide->slider_id );
        }
    }

    /**
     * Schedule a slide to publish at a future date.
     */
    public static function schedule_publish( $slide_id, $datetime ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'spslider_slides',
            [ 'publish_at' => sanitize_text_field( $datetime ), 'status' => 0 ],
            [ 'id' => (int) $slide_id ],
            [ '%s', '%d' ],
            [ '%d' ]
        );
    }

    /**
     * Schedule a slide to expire (unpublish) at a future date.
     */
    public static function schedule_expire( $slide_id, $datetime ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'spslider_slides',
            [ 'expire_at' => sanitize_text_field( $datetime ) ],
            [ 'id' => (int) $slide_id ],
            [ '%s' ],
            [ '%d' ]
        );
    }

    /**
     * Clear scheduling for a slide.
     */
    public static function clear_schedule( $slide_id ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'spslider_slides',
            [ 'publish_at' => null, 'expire_at' => null ],
            [ 'id' => (int) $slide_id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );
    }

    /**
     * Get scheduling info for a slide.
     */
    public static function get_schedule( $slide_id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT publish_at, expire_at FROM {$wpdb->prefix}spslider_slides WHERE id = %d",
            (int) $slide_id
        ) );
        return $row ? [ 'publish_at' => $row->publish_at, 'expire_at' => $row->expire_at ] : null;
    }

    /**
     * Cleanup on deactivation — remove the cron event.
     */
    public static function deactivate() {
        wp_clear_scheduled_hook( self::CRON_HOOK );
    }
}
