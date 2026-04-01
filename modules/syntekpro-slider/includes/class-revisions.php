<?php
defined( 'ABSPATH' ) || exit;

/**
 * Revision history: snapshot every save, compare revisions, restore with one click.
 */
class SPSLIDER_Revisions {

    /**
     * Create DB table for revisions on activation.
     */
    public static function create_table() {
        global $wpdb;
        $c = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE {$wpdb->prefix}spslider_revisions (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            slider_id   BIGINT UNSIGNED NOT NULL,
            user_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
            snapshot    LONGTEXT        NOT NULL,
            label       VARCHAR(255)    NOT NULL DEFAULT '',
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY slider_id (slider_id),
            KEY created_at (created_at)
        ) $c;" );
    }

    /**
     * Save a revision snapshot (called after every editor save).
     */
    public static function save_revision( $slider_id ) {
        global $wpdb;

        $data = SPSLIDER_Database::load_full_slider( $slider_id );
        if ( ! $data ) return;

        $wpdb->insert(
            $wpdb->prefix . 'spslider_revisions',
            [
                'slider_id'  => (int) $slider_id,
                'user_id'    => get_current_user_id(),
                'snapshot'   => wp_json_encode( $data ),
                'label'      => sprintf( 'Revision by %s', wp_get_current_user()->display_name ?: 'System' ),
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%s', '%s', '%s' ]
        );

        // Keep only last 50 revisions per slider
        self::prune( $slider_id, 50 );
    }

    /**
     * List revisions for a slider.
     */
    public static function get_revisions( $slider_id, $limit = 50 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT id, slider_id, user_id, label, created_at
             FROM {$wpdb->prefix}spslider_revisions
             WHERE slider_id = %d
             ORDER BY created_at DESC
             LIMIT %d",
            (int) $slider_id,
            (int) $limit
        ) );
    }

    /**
     * Get a single revision's full snapshot.
     */
    public static function get_revision( $revision_id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}spslider_revisions WHERE id = %d",
            (int) $revision_id
        ) );
        if ( $row ) {
            $row->snapshot = json_decode( $row->snapshot, true );
        }
        return $row;
    }

    /**
     * Restore a slider from a revision snapshot.
     */
    public static function restore( $revision_id ) {
        $rev = self::get_revision( $revision_id );
        if ( ! $rev || empty( $rev->snapshot ) ) return false;

        $snapshot  = $rev->snapshot;
        $slider_id = (int) $rev->slider_id;

        // Save current state as a new revision first (safety net)
        self::save_revision( $slider_id );

        // Restore slider settings and all slides+layers
        SPSLIDER_Database::save_full_slider(
            $slider_id,
            $snapshot['settings'] ?? [],
            $snapshot['slides']   ?? []
        );

        SPSLIDER_Cache::invalidate( $slider_id );

        return true;
    }

    /**
     * Diff two revisions — returns changed keys at the settings level.
     */
    public static function diff( $rev_id_a, $rev_id_b ) {
        $a = self::get_revision( $rev_id_a );
        $b = self::get_revision( $rev_id_b );
        if ( ! $a || ! $b ) return null;

        $diff = [
            'settings_changed' => [],
            'slides_added'     => 0,
            'slides_removed'   => 0,
            'layers_changed'   => 0,
        ];

        $sa = $a->snapshot['settings'] ?? [];
        $sb = $b->snapshot['settings'] ?? [];

        foreach ( array_unique( array_merge( array_keys( $sa ), array_keys( $sb ) ) ) as $key ) {
            if ( ( $sa[ $key ] ?? null ) !== ( $sb[ $key ] ?? null ) ) {
                $diff['settings_changed'][] = $key;
            }
        }

        $slides_a = count( $a->snapshot['slides'] ?? [] );
        $slides_b = count( $b->snapshot['slides'] ?? [] );
        $diff['slides_added']   = max( 0, $slides_b - $slides_a );
        $diff['slides_removed'] = max( 0, $slides_a - $slides_b );

        return $diff;
    }

    /**
     * Prune old revisions, keeping only the latest $keep per slider.
     */
    private static function prune( $slider_id, $keep = 50 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spslider_revisions';

        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE slider_id = %d",
            (int) $slider_id
        ) );

        if ( $count <= $keep ) return;

        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table} WHERE slider_id = %d AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM {$table} WHERE slider_id = %d ORDER BY created_at DESC LIMIT %d
                ) AS keep_ids
            )",
            (int) $slider_id,
            (int) $slider_id,
            (int) $keep
        ) );
    }

    /**
     * Delete all revisions for a slider (called on slider deletion).
     */
    public static function delete_for_slider( $slider_id ) {
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix . 'spslider_revisions',
            [ 'slider_id' => (int) $slider_id ],
            [ '%d' ]
        );
    }
}
