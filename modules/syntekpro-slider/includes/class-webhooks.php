<?php
defined( 'ABSPATH' ) || exit;

/**
 * Webhooks: fire HTTP callbacks on slider events (publish, unpublish, save, delete).
 */
class SPSLIDER_Webhooks {

    const OPTION_KEY = 'spslider_webhooks';

    /**
     * Register webhook endpoints.
     */
    public static function get_webhooks() {
        return get_option( self::OPTION_KEY, [] );
    }

    /**
     * Save webhook configuration.
     *
     * @param array $webhooks [ [ 'url' => '', 'events' => ['save','publish','delete'], 'active' => true ] ]
     */
    public static function save_webhooks( $webhooks ) {
        $clean = [];
        foreach ( $webhooks as $wh ) {
            $url = esc_url_raw( $wh['url'] ?? '' );
            if ( ! $url ) continue;
            $clean[] = [
                'url'    => $url,
                'events' => array_map( 'sanitize_key', (array) ( $wh['events'] ?? [] ) ),
                'active' => ! empty( $wh['active'] ),
                'secret' => sanitize_text_field( $wh['secret'] ?? '' ),
            ];
        }
        update_option( self::OPTION_KEY, $clean );
    }

    /**
     * Fire webhooks for a given event.
     *
     * @param string $event   Event name: save, publish, unpublish, delete.
     * @param array  $payload Data to send.
     */
    public static function fire( $event, $payload = [] ) {
        $webhooks = self::get_webhooks();
        if ( empty( $webhooks ) ) return;

        $payload['event']     = $event;
        $payload['timestamp'] = gmdate( 'c' );
        $payload['site_url']  = home_url();

        $body = wp_json_encode( $payload );

        foreach ( $webhooks as $wh ) {
            if ( ! $wh['active'] ) continue;
            if ( ! in_array( $event, $wh['events'], true ) ) continue;

            $headers = [ 'Content-Type' => 'application/json' ];

            // HMAC signature for payload verification
            if ( ! empty( $wh['secret'] ) ) {
                $headers['X-SPSlider-Signature'] = hash_hmac( 'sha256', $body, $wh['secret'] );
            }

            wp_remote_post( $wh['url'], [
                'body'      => $body,
                'headers'   => $headers,
                'timeout'   => 10,
                'blocking'  => false,
                'sslverify' => true,
            ] );
        }
    }

    /**
     * Hook into slider lifecycle events.
     */
    public static function init() {
        add_action( 'spslider_after_save',   function ( $id ) { self::fire( 'save',      [ 'slider_id' => $id ] ); } );
        add_action( 'spslider_published',    function ( $id ) { self::fire( 'publish',   [ 'slider_id' => $id ] ); } );
        add_action( 'spslider_unpublished',  function ( $id ) { self::fire( 'unpublish', [ 'slider_id' => $id ] ); } );
        add_action( 'spslider_deleted',      function ( $id ) { self::fire( 'delete',    [ 'slider_id' => $id ] ); } );
    }
}
