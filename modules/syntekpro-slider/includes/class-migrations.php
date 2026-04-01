<?php
defined( 'ABSPATH' ) || exit;

/**
 * Schema migration runner: automatically converts old slider data when the plugin version changes.
 * Each migration is a versioned function that runs once.
 */
class SPSLIDER_Migrations {

    const OPTION_KEY = 'spslider_db_version';

    /**
     * Run any pending migrations.
     */
    public static function run() {
        $current = get_option( self::OPTION_KEY, '1.0.0' );
        $target  = SPSLIDER_VERSION;

        if ( version_compare( $current, $target, '>=' ) ) return;

        $migrations = self::get_migrations();

        foreach ( $migrations as $version => $callback ) {
            if ( version_compare( $current, $version, '<' ) ) {
                call_user_func( $callback );
            }
        }

        update_option( self::OPTION_KEY, $target );
    }

    /**
     * Migration registry: version => callable.
     * Add new migrations here when schema changes.
     */
    private static function get_migrations() {
        return [
            '1.1.0' => [ __CLASS__, 'migrate_1_1_0' ],
            '1.2.0' => [ __CLASS__, 'migrate_1_2_0' ],
        ];
    }

    /**
     * v1.1.0: Add revision, conversion, audit, scheduling tables.
     */
    public static function migrate_1_1_0() {
        SPSLIDER_Revisions::create_table();
        SPSLIDER_Conversions::create_table();
        SPSLIDER_Audit_Log::create_table();

        // Add schedule columns to slides table
        global $wpdb;
        $table = $wpdb->prefix . 'spslider_slides';

        $col_exists = $wpdb->get_var( "SHOW COLUMNS FROM {$table} LIKE 'publish_at'" );
        if ( ! $col_exists ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->query( "ALTER TABLE {$table} ADD COLUMN publish_at DATETIME DEFAULT NULL AFTER status" );
            $wpdb->query( "ALTER TABLE {$table} ADD COLUMN expire_at DATETIME DEFAULT NULL AFTER publish_at" );
        }

        // Add new global settings defaults
        $global = get_option( 'spslider_global_settings', [] );
        $global = wp_parse_args( $global, [
            'enable_cache'     => false,
            'generate_avif'    => false,
            'enable_revisions' => true,
            'enable_audit_log' => true,
        ] );
        update_option( 'spslider_global_settings', $global );
    }

    /**
     * v1.2.0: placeholder for future migrations.
     */
    public static function migrate_1_2_0() {
        // Future schema changes go here
    }
}
