<?php
defined( 'ABSPATH' ) || exit;

/**
 * WP-CLI commands for SyntekPro Slider (requires WP-CLI environment).
 * Provides bulk operations, export/import, cache management from the terminal.
 *
 * Usage: wp spslider <command> [args]
 */
class SPSLIDER_CLI {

    /**
     * Register CLI commands when WP-CLI is available.
     */
    public static function register() {
        if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) return;

        WP_CLI::add_command( 'spslider list',       [ __CLASS__, 'cmd_list' ] );
        WP_CLI::add_command( 'spslider get',        [ __CLASS__, 'cmd_get' ] );
        WP_CLI::add_command( 'spslider create',     [ __CLASS__, 'cmd_create' ] );
        WP_CLI::add_command( 'spslider delete',     [ __CLASS__, 'cmd_delete' ] );
        WP_CLI::add_command( 'spslider duplicate',  [ __CLASS__, 'cmd_duplicate' ] );
        WP_CLI::add_command( 'spslider export',     [ __CLASS__, 'cmd_export' ] );
        WP_CLI::add_command( 'spslider import',     [ __CLASS__, 'cmd_import' ] );
        WP_CLI::add_command( 'spslider cache-flush',[ __CLASS__, 'cmd_cache_flush' ] );
        WP_CLI::add_command( 'spslider stats',      [ __CLASS__, 'cmd_stats' ] );
        WP_CLI::add_command( 'spslider prune',      [ __CLASS__, 'cmd_prune' ] );
    }

    /**
     * List all sliders.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format (table, json, csv).
     * ---
     * default: table
     * ---
     *
     * ## EXAMPLES
     *   wp spslider list
     *   wp spslider list --format=json
     *
     * @subcommand list
     */
    public static function cmd_list( $args, $assoc_args ) {
        $format  = $assoc_args['format'] ?? 'table';
        $sliders = SPSLIDER_Database::get_sliders( [ 'status' => '', 'limit' => 9999 ] );

        $rows = [];
        foreach ( $sliders as $s ) {
            $slides = SPSLIDER_Database::get_slides( $s->id );
            $rows[] = [
                'ID'      => $s->id,
                'Name'    => $s->name,
                'Slug'    => $s->slug,
                'Status'  => $s->status ? 'active' : 'draft',
                'Slides'  => count( $slides ),
                'Created' => $s->created_at,
            ];
        }

        WP_CLI\Utils\format_items( $format, $rows, [ 'ID', 'Name', 'Slug', 'Status', 'Slides', 'Created' ] );
    }

    /**
     * Get slider details.
     *
     * <id>
     * : Slider ID.
     *
     * ## EXAMPLES
     *   wp spslider get 1
     */
    public static function cmd_get( $args ) {
        $id   = (int) $args[0];
        $data = SPSLIDER_Database::load_full_slider( $id );
        if ( ! $data ) WP_CLI::error( "Slider #{$id} not found." );

        WP_CLI::log( "Slider: {$data['name']} (ID: {$data['id']})" );
        WP_CLI::log( "Slides: " . count( $data['slides'] ) );
        WP_CLI::log( "Settings: " . wp_json_encode( $data['settings'], JSON_PRETTY_PRINT ) );
    }

    /**
     * Create a new slider.
     *
     * [--name=<name>]
     * : Slider name.
     * ---
     * default: CLI Slider
     * ---
     *
     * ## EXAMPLES
     *   wp spslider create --name="Hero Banner"
     */
    public static function cmd_create( $args, $assoc_args ) {
        $name = $assoc_args['name'] ?? 'CLI Slider';
        $id   = SPSLIDER_Database::create_slider( [ 'name' => $name ] );
        if ( ! $id ) WP_CLI::error( 'Failed to create slider.' );
        WP_CLI::success( "Created slider #{$id}: {$name}" );
    }

    /**
     * Delete a slider.
     *
     * <id>
     * : Slider ID to delete.
     *
     * [--yes]
     * : Skip confirmation.
     *
     * ## EXAMPLES
     *   wp spslider delete 3 --yes
     */
    public static function cmd_delete( $args, $assoc_args ) {
        $id = (int) $args[0];
        if ( empty( $assoc_args['yes'] ) ) {
            WP_CLI::confirm( "Delete slider #{$id}?" );
        }
        SPSLIDER_Database::delete_slider( $id );
        WP_CLI::success( "Deleted slider #{$id}." );
    }

    /**
     * Duplicate a slider.
     *
     * <id>
     * : Slider ID to duplicate.
     *
     * ## EXAMPLES
     *   wp spslider duplicate 1
     */
    public static function cmd_duplicate( $args ) {
        $id     = (int) $args[0];
        $new_id = SPSLIDER_Database::duplicate_slider( $id );
        if ( ! $new_id ) WP_CLI::error( "Failed to duplicate slider #{$id}." );
        WP_CLI::success( "Duplicated slider #{$id} → #{$new_id}" );
    }

    /**
     * Export a slider to JSON file.
     *
     * <id>
     * : Slider ID (or 'all' for bulk export).
     *
     * [--output=<path>]
     * : Output file path. Default: current directory.
     *
     * ## EXAMPLES
     *   wp spslider export 1 --output=./slider-backup.json
     *   wp spslider export all
     */
    public static function cmd_export( $args, $assoc_args ) {
        $id = $args[0] ?? '';

        if ( $id === 'all' ) {
            $sliders = SPSLIDER_Database::get_sliders( [ 'status' => '', 'limit' => 9999 ] );
            $out = [];
            foreach ( $sliders as $s ) {
                $out[] = SPSLIDER_Database::load_full_slider( $s->id );
            }
            $export = [
                'format'  => 'syntekpro-slider-bulk',
                'version' => SPSLIDER_VERSION,
                'count'   => count( $out ),
                'sliders' => $out,
            ];
            $filename = $assoc_args['output'] ?? 'spslider-backup-' . gmdate( 'Y-m-d' ) . '.json';
        } else {
            $data = SPSLIDER_Database::load_full_slider( (int) $id );
            if ( ! $data ) WP_CLI::error( "Slider #{$id} not found." );
            $export = [
                'format'  => 'syntekpro-slider',
                'version' => SPSLIDER_VERSION,
                'slider'  => $data,
            ];
            $filename = $assoc_args['output'] ?? 'spslider-' . $data['name'] . '.json';
        }

        $json = wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        file_put_contents( $filename, $json ); // phpcs:ignore WordPress.WP.AlternativeFunctions
        WP_CLI::success( "Exported to {$filename}" );
    }

    /**
     * Import a slider from a JSON file.
     *
     * <file>
     * : Path to JSON file.
     *
     * ## EXAMPLES
     *   wp spslider import ./slider-backup.json
     */
    public static function cmd_import( $args ) {
        $file = $args[0] ?? '';
        if ( ! file_exists( $file ) ) WP_CLI::error( "File not found: {$file}" );

        $result = SPSLIDER_Export::import_json( $file );
        if ( is_wp_error( $result ) ) {
            WP_CLI::error( $result->get_error_message() );
        }

        if ( is_array( $result ) ) {
            WP_CLI::success( 'Imported ' . count( $result ) . ' sliders.' );
        } else {
            WP_CLI::success( "Imported slider #{$result}" );
        }
    }

    /**
     * Flush all slider caches.
     *
     * ## EXAMPLES
     *   wp spslider cache-flush
     */
    public static function cmd_cache_flush() {
        SPSLIDER_Cache::flush_all();
        WP_CLI::success( 'All slider caches flushed.' );
    }

    /**
     * Show analytics stats for a slider.
     *
     * <id>
     * : Slider ID.
     *
     * [--days=<days>]
     * : Number of days to show.
     * ---
     * default: 30
     * ---
     */
    public static function cmd_stats( $args, $assoc_args ) {
        $id   = (int) $args[0];
        $days = (int) ( $assoc_args['days'] ?? 30 );
        $stats = SPSLIDER_Analytics::get_stats( $id, $days );

        WP_CLI::log( "Stats for slider #{$id} (last {$days} days):" );
        WP_CLI::log( "  Views:    {$stats['total_views']}" );
        WP_CLI::log( "  Sessions: {$stats['unique_sessions']}" );
        WP_CLI::log( "  Swipes:   {$stats['nav_swipe']}" );
        WP_CLI::log( "  Clicks:   {$stats['nav_click']}" );
    }

    /**
     * Prune old analytics and audit log data.
     *
     * [--days=<days>]
     * : Keep data newer than this many days.
     * ---
     * default: 365
     * ---
     */
    public static function cmd_prune( $args, $assoc_args ) {
        $days = (int) ( $assoc_args['days'] ?? 365 );
        SPSLIDER_Analytics::prune( $days );
        SPSLIDER_Audit_Log::prune( $days );
        WP_CLI::success( "Pruned data older than {$days} days." );
    }
}
