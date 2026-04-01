<?php
defined( 'ABSPATH' ) || exit;

/**
 * Role-based editor permissions: control who can create, edit, publish, and delete sliders.
 * Allows mapping WordPress roles to slider capabilities.
 */
class SPSLIDER_Permissions {

    const OPTION_KEY = 'spslider_permissions';

    /**
     * Default role-capability mapping.
     */
    public static function defaults() {
        return [
            'administrator' => [ 'create', 'edit', 'publish', 'delete', 'settings', 'export', 'import' ],
            'editor'        => [ 'create', 'edit', 'publish' ],
            'author'        => [ 'edit' ],
            'contributor'   => [],
            'subscriber'    => [],
        ];
    }

    /**
     * Get current permissions map.
     */
    public static function get_map() {
        return wp_parse_args( get_option( self::OPTION_KEY, [] ), self::defaults() );
    }

    /**
     * Save permissions map.
     */
    public static function save( $map ) {
        $clean = [];
        $valid_caps = [ 'create', 'edit', 'publish', 'delete', 'settings', 'export', 'import' ];
        foreach ( $map as $role => $caps ) {
            $clean[ sanitize_key( $role ) ] = array_values( array_intersect(
                array_map( 'sanitize_key', (array) $caps ),
                $valid_caps
            ) );
        }
        update_option( self::OPTION_KEY, $clean );
    }

    /**
     * Check if the current user has a specific slider capability.
     */
    public static function current_user_can( $capability ) {
        if ( current_user_can( 'manage_options' ) ) return true; // admins always can

        $user = wp_get_current_user();
        $map  = self::get_map();

        foreach ( $user->roles as $role ) {
            $role_caps = $map[ $role ] ?? [];
            if ( in_array( $capability, $role_caps, true ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check permission and wp_send_json_error if denied.
     */
    public static function require_cap( $capability ) {
        if ( ! self::current_user_can( $capability ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to perform this action.', 'syntekpro-slider' ),
            ], 403 );
        }
    }

    /**
     * Filter admin menu visibility based on permissions.
     */
    public static function init() {
        add_filter( 'spslider_admin_capability', [ __CLASS__, 'filter_capability' ], 10, 2 );
    }

    /**
     * Maps page contexts to required capabilities.
     */
    public static function filter_capability( $default_cap, $context = '' ) {
        $user = wp_get_current_user();
        $map  = self::get_map();

        foreach ( $user->roles as $role ) {
            $role_caps = $map[ $role ] ?? [];
            if ( ! empty( $role_caps ) ) {
                // User has at least one slider cap — allow menu access
                return $default_cap;
            }
        }

        // No slider caps at all — use default (manage_options)
        return 'manage_options';
    }
}
