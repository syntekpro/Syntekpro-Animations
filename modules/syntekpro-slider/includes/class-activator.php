<?php
defined( 'ABSPATH' ) || exit;

/**
 * Runs on plugin activation: creates DB tables and sets default options.
 */
class SPSLIDER_Activator {

    public static function activate() {
        self::create_tables();
        self::set_defaults();
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;
        $c = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE {$wpdb->prefix}spslider_sliders (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name       VARCHAR(255)    NOT NULL DEFAULT '',
            slug       VARCHAR(255)    NOT NULL DEFAULT '',
            settings   LONGTEXT        NOT NULL DEFAULT '{}',
            status     TINYINT(1)      NOT NULL DEFAULT 1,
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $c;" );

        dbDelta( "CREATE TABLE {$wpdb->prefix}spslider_slides (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            slider_id  BIGINT UNSIGNED NOT NULL,
            title      VARCHAR(255)    NOT NULL DEFAULT '',
            sort_order INT             NOT NULL DEFAULT 0,
            settings   LONGTEXT        NOT NULL DEFAULT '{}',
            status     TINYINT(1)      NOT NULL DEFAULT 1,
            publish_at DATETIME        DEFAULT NULL,
            expire_at  DATETIME        DEFAULT NULL,
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY slider_id (slider_id)
        ) $c;" );

        dbDelta( "CREATE TABLE {$wpdb->prefix}spslider_layers (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            slide_id   BIGINT UNSIGNED NOT NULL,
            type       VARCHAR(50)     NOT NULL DEFAULT 'text',
            sort_order INT             NOT NULL DEFAULT 0,
            settings   LONGTEXT        NOT NULL DEFAULT '{}',
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY slide_id (slide_id)
        ) $c;" );

        dbDelta( "CREATE TABLE {$wpdb->prefix}spslider_analytics (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            slider_id  BIGINT UNSIGNED NOT NULL,
            slide_id   BIGINT UNSIGNED,
            layer_id   BIGINT UNSIGNED,
            event_type VARCHAR(50)     NOT NULL,
            session_id VARCHAR(64)     NOT NULL DEFAULT '',
            meta       TEXT,
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY slider_id (slider_id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $c;" );

        // New tables for v1.1.0
        SPSLIDER_Revisions::create_table();
        SPSLIDER_Conversions::create_table();
        SPSLIDER_Audit_Log::create_table();

        update_option( 'spslider_db_version', SPSLIDER_VERSION );
    }

    private static function set_defaults() {
        $options = [
            'spslider_global_settings' => [
                'analytics_enabled'  => true,
                'lazy_load'          => true,
                'optimize_assets'    => false,
                'generate_webp'      => false,
                'generate_avif'      => false,
                'enable_cache'       => false,
                'enable_revisions'   => true,
                'enable_audit_log'   => true,
            ],
        ];
        foreach ( $options as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }
}
