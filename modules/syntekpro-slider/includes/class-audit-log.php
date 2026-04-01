<?php
defined( 'ABSPATH' ) || exit;

/**
 * Audit log: record every admin action on sliders for accountability.
 */
class SPSLIDER_Audit_Log {

    /**
     * Create audit log table.
     */
    public static function create_table() {
        global $wpdb;
        $c = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE {$wpdb->prefix}spslider_audit_log (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
            user_login VARCHAR(100)    NOT NULL DEFAULT '',
            action     VARCHAR(100)    NOT NULL,
            object_type VARCHAR(50)    NOT NULL DEFAULT 'slider',
            object_id  BIGINT UNSIGNED NOT NULL DEFAULT 0,
            details    TEXT,
            ip_address VARCHAR(45)     NOT NULL DEFAULT '',
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY object_id (object_id),
            KEY created_at (created_at)
        ) $c;" );
    }

    /**
     * Log an action.
     */
    public static function log( $action, $object_type = 'slider', $object_id = 0, $details = '' ) {
        global $wpdb;

        $global = get_option( 'spslider_global_settings', [] );
        if ( empty( $global['enable_audit_log'] ) ) return;

        $user = wp_get_current_user();

        $wpdb->insert(
            $wpdb->prefix . 'spslider_audit_log',
            [
                'user_id'     => (int) $user->ID,
                'user_login'  => sanitize_user( $user->user_login ?: 'system' ),
                'action'      => sanitize_key( $action ),
                'object_type' => sanitize_key( $object_type ),
                'object_id'   => (int) $object_id,
                'details'     => sanitize_textarea_field( $details ),
                'ip_address'  => self::get_client_ip(),
                'created_at'  => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s' ]
        );
    }

    /**
     * Get audit log entries.
     */
    public static function get_entries( $args = [] ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spslider_audit_log';

        $defaults = [
            'object_type' => '',
            'object_id'   => 0,
            'user_id'     => 0,
            'action'      => '',
            'limit'       => 50,
            'offset'      => 0,
            'days'        => 90,
        ];
        $args = wp_parse_args( $args, $defaults );

        $sql    = "SELECT * FROM {$table} WHERE 1=1";
        $params = [];

        if ( $args['object_type'] ) {
            $sql .= ' AND object_type = %s';
            $params[] = sanitize_key( $args['object_type'] );
        }
        if ( $args['object_id'] ) {
            $sql .= ' AND object_id = %d';
            $params[] = (int) $args['object_id'];
        }
        if ( $args['user_id'] ) {
            $sql .= ' AND user_id = %d';
            $params[] = (int) $args['user_id'];
        }
        if ( $args['action'] ) {
            $sql .= ' AND action = %s';
            $params[] = sanitize_key( $args['action'] );
        }
        if ( $args['days'] ) {
            $sql .= ' AND created_at >= %s';
            $params[] = gmdate( 'Y-m-d H:i:s', strtotime( "-{$args['days']} days" ) );
        }

        $sql .= ' ORDER BY created_at DESC LIMIT %d OFFSET %d';
        $params[] = max( 1, (int) $args['limit'] );
        $params[] = max( 0, (int) $args['offset'] );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    /**
     * Prune old entries.
     */
    public static function prune( $days = 365 ) {
        global $wpdb;
        $before = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}spslider_audit_log WHERE created_at < %s",
            $before
        ) );
    }

    /**
     * Get client IP address safely.
     */
    private static function get_client_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            // Take only the first IP in the chain
            $ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
            $ip  = trim( $ips[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
    }

    /**
     * Hook into slider lifecycle to auto-log.
     */
    public static function init() {
        add_action( 'spslider_after_save',  function ( $id ) { self::log( 'save',   'slider', $id ); } );
        add_action( 'spslider_published',   function ( $id ) { self::log( 'publish', 'slider', $id ); } );
        add_action( 'spslider_unpublished', function ( $id ) { self::log( 'unpublish', 'slider', $id ); } );
        add_action( 'spslider_deleted',     function ( $id ) { self::log( 'delete', 'slider', $id ); } );
    }
}
