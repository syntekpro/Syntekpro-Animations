<?php
defined( 'ABSPATH' ) || exit;

/**
 * REST API (syntekpro-slider/v1) and developer hooks / PHP SDK.
 *
 * JS API: window.SPSlider.get(id).play() / .pause() / .goTo(n) / .destroy()
 * PHP hooks:
 *   Actions:  spslider_loaded, spslider_before_render, spslider_after_render
 *   Filters:  spslider_slider_settings, spslider_layer_output, spslider_slide_output
 */
class SPSLIDER_API {

    const NS = 'syntekpro-slider/v1';

    public function register_routes() {
        // Sliders
        register_rest_route( self::NS, '/sliders', [
            [ 'methods' => 'GET',  'callback' => [ $this, 'get_sliders' ],  'permission_callback' => [ $this, 'auth_admin' ] ],
            [ 'methods' => 'POST', 'callback' => [ $this, 'create_slider' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
        ] );
        register_rest_route( self::NS, '/sliders/(?P<id>\d+)', [
            [ 'methods' => 'GET',    'callback' => [ $this, 'get_slider' ],    'permission_callback' => [ $this, 'auth_admin' ] ],
            [ 'methods' => 'PUT',    'callback' => [ $this, 'update_slider' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
            [ 'methods' => 'DELETE', 'callback' => [ $this, 'delete_slider' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
        ] );

        // Full slider with slides+layers for editor
        register_rest_route( self::NS, '/sliders/(?P<id>\d+)/full', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_full_slider' ],
            'permission_callback' => [ $this, 'auth_admin' ],
        ] );

        register_rest_route( self::NS, '/sliders/(?P<id>\d+)/save', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'save_full_slider' ],
            'permission_callback' => [ $this, 'auth_admin' ],
        ] );

        // Analytics
        register_rest_route( self::NS, '/sliders/(?P<id>\d+)/analytics', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_analytics' ],
            'permission_callback' => [ $this, 'auth_admin' ],
        ] );

        // Templates
        register_rest_route( self::NS, '/templates', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_templates' ],
            'permission_callback' => [ $this, 'auth_admin' ],
        ] );

        // Slides
        register_rest_route( self::NS, '/slides', [
            [ 'methods' => 'POST',   'callback' => [ $this, 'create_slide' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
        ] );
        register_rest_route( self::NS, '/slides/(?P<id>\d+)', [
            [ 'methods' => 'PUT',    'callback' => [ $this, 'update_slide' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
            [ 'methods' => 'DELETE', 'callback' => [ $this, 'delete_slide' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
        ] );

        // Layers
        register_rest_route( self::NS, '/layers', [
            [ 'methods' => 'POST',   'callback' => [ $this, 'create_layer' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
        ] );
        register_rest_route( self::NS, '/layers/(?P<id>\d+)', [
            [ 'methods' => 'PUT',    'callback' => [ $this, 'update_layer' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
            [ 'methods' => 'DELETE', 'callback' => [ $this, 'delete_layer' ], 'permission_callback' => [ $this, 'auth_admin' ] ],
        ] );

        // Track event (no auth – public)
        register_rest_route( self::NS, '/track', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'track_event' ],
            'permission_callback' => '__return_true',
        ] );
    }

    // ── Permission callbacks ───────────────────────────────────────────────────

    public function auth_admin( WP_REST_Request $req ) {
        return current_user_can( 'manage_options' );
    }

    // ── Slider endpoints ──────────────────────────────────────────────────────

    public function get_sliders( WP_REST_Request $req ) {
        $sliders = SPSLIDER_Database::get_sliders();
        return rest_ensure_response( $sliders );
    }

    public function get_slider( WP_REST_Request $req ) {
        $slider = SPSLIDER_Database::get_slider( $req['id'] );
        if ( ! $slider ) return new WP_Error( 'not_found', 'Slider not found', [ 'status' => 404 ] );
        return rest_ensure_response( $slider );
    }

    public function get_full_slider( WP_REST_Request $req ) {
        $data = SPSLIDER_Database::load_full_slider( $req['id'] );
        if ( ! $data ) return new WP_Error( 'not_found', 'Slider not found', [ 'status' => 404 ] );
        return rest_ensure_response( $data );
    }

    public function create_slider( WP_REST_Request $req ) {
        $params = $req->get_json_params();
        $id     = SPSLIDER_Database::create_slider( $params );
        if ( ! $id ) return new WP_Error( 'create_failed', 'Could not create slider', [ 'status' => 500 ] );
        return rest_ensure_response( SPSLIDER_Database::get_slider( $id ) );
    }

    public function update_slider( WP_REST_Request $req ) {
        $params = $req->get_json_params();
        SPSLIDER_Database::update_slider( $req['id'], $params );
        return rest_ensure_response( SPSLIDER_Database::get_slider( $req['id'] ) );
    }

    public function delete_slider( WP_REST_Request $req ) {
        SPSLIDER_Database::delete_slider( $req['id'] );
        return rest_ensure_response( [ 'deleted' => true ] );
    }

    public function save_full_slider( WP_REST_Request $req ) {
        $body = $req->get_json_params();
        SPSLIDER_Database::save_full_slider(
            $req['id'],
            $body['settings'] ?? [],
            $body['slides']   ?? []
        );
        return rest_ensure_response( SPSLIDER_Database::load_full_slider( $req['id'] ) );
    }

    // ── Slide endpoints ───────────────────────────────────────────────────────

    public function create_slide( WP_REST_Request $req ) {
        $id = SPSLIDER_Database::create_slide( $req->get_json_params() );
        if ( ! $id ) return new WP_Error( 'create_failed', 'Could not create slide', [ 'status' => 500 ] );
        return rest_ensure_response( SPSLIDER_Database::get_slide( $id ) );
    }

    public function update_slide( WP_REST_Request $req ) {
        SPSLIDER_Database::update_slide( $req['id'], $req->get_json_params() );
        return rest_ensure_response( SPSLIDER_Database::get_slide( $req['id'] ) );
    }

    public function delete_slide( WP_REST_Request $req ) {
        SPSLIDER_Database::delete_slide( $req['id'] );
        return rest_ensure_response( [ 'deleted' => true ] );
    }

    // ── Layer endpoints ───────────────────────────────────────────────────────

    public function create_layer( WP_REST_Request $req ) {
        $id = SPSLIDER_Database::create_layer( $req->get_json_params() );
        if ( ! $id ) return new WP_Error( 'create_failed', 'Could not create layer', [ 'status' => 500 ] );
        return rest_ensure_response( [ 'id' => $id ] );
    }

    public function update_layer( WP_REST_Request $req ) {
        SPSLIDER_Database::update_layer( $req['id'], $req->get_json_params() );
        return rest_ensure_response( [ 'updated' => true ] );
    }

    public function delete_layer( WP_REST_Request $req ) {
        SPSLIDER_Database::delete_layer( $req['id'] );
        return rest_ensure_response( [ 'deleted' => true ] );
    }

    // ── Analytics endpoint ────────────────────────────────────────────────────

    public function get_analytics( WP_REST_Request $req ) {
        $days  = (int) ( $req->get_param( 'days' ) ?? 30 );
        $stats = SPSLIDER_Analytics::get_stats( $req['id'], $days );
        return rest_ensure_response( $stats );
    }

    // ── Templates endpoint ────────────────────────────────────────────────────

    public function get_templates( WP_REST_Request $req ) {
        return rest_ensure_response( SPSLIDER_Templates::get_all() );
    }

    // ── Track endpoint ────────────────────────────────────────────────────────

    public function track_event( WP_REST_Request $req ) {
        $body = $req->get_json_params();
        SPSLIDER_Analytics::track(
            $body['slider_id']  ?? 0,
            $body['event_type'] ?? '',
            $body['slide_id']   ?? null,
            $body['layer_id']   ?? null,
            $body['meta']       ?? []
        );
        return rest_ensure_response( [ 'ok' => true ] );
    }
}

// ── Developer PHP SDK (static helpers that fire hooks) ────────────────────────

/**
 * Register a custom layer type.
 *
 * @param string   $type     Unique key (e.g. 'countdown').
 * @param callable $renderer Callable( $layer_settings ) => HTML string.
 */
function spslider_register_layer_type( $type, $renderer ) {
    add_filter( 'spslider_layer_output', function ( $html, $layer_type, $settings ) use ( $type, $renderer ) {
        if ( $layer_type === $type ) {
            return call_user_func( $renderer, $settings );
        }
        return $html;
    }, 10, 3 );
}

/**
 * Register a custom slide transition.
 *
 * @param string $key   Unique transition key.
 * @param array  $args  { css_class, label }
 */
function spslider_register_transition( $key, $args ) {
    add_filter( 'spslider_transitions', function ( $transitions ) use ( $key, $args ) {
        $transitions[ sanitize_key( $key ) ] = $args;
        return $transitions;
    } );
}
