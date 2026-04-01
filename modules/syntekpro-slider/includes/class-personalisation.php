<?php
defined( 'ABSPATH' ) || exit;

/**
 * Personalisation engine: show different slider content based on user context.
 * Supports: logged-in status, user role, device type, referrer source, geolocation (via IP),
 * return visitor detection, WooCommerce cart/purchase history.
 */
class SPSLIDER_Personalisation {

    /**
     * Evaluate personalisation rules for a slider and filter slides.
     *
     * @param array $slides  Array of slide objects.
     * @param array $settings Slider settings containing personalisation rules.
     * @return array Filtered slides to display.
     */
    public static function filter_slides( $slides, $settings ) {
        if ( empty( $settings['personalisation_enabled'] ) ) return $slides;

        $context = self::get_context();

        return array_values( array_filter( $slides, function ( $slide ) use ( $context ) {
            $s     = is_object( $slide ) ? ( $slide->settings ?? [] ) : ( $slide['settings'] ?? [] );
            $rules = $s['visibility_rules'] ?? [];

            if ( empty( $rules ) ) return true;

            return self::evaluate_rules( $rules, $context );
        } ) );
    }

    /**
     * Gather current page/user context for rule evaluation.
     */
    public static function get_context() {
        $context = [
            'is_logged_in'   => is_user_logged_in(),
            'user_role'      => '',
            'device'         => self::detect_device(),
            'referrer'       => wp_get_referer() ?: ( isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '' ),
            'is_return'      => ! empty( $_COOKIE['spslider_visited'] ),
            'page_url'       => sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ),
            'day_of_week'    => (int) gmdate( 'N' ), // 1=Mon, 7=Sun
            'hour'           => (int) current_time( 'G' ), // 0-23
            'woo_has_cart'   => false,
            'woo_purchased'  => false,
        ];

        if ( $context['is_logged_in'] ) {
            $user = wp_get_current_user();
            $context['user_role'] = $user->roles[0] ?? '';
        }

        // WooCommerce integration
        if ( function_exists( 'WC' ) ) {
            $cart = WC()->cart;
            $context['woo_has_cart'] = $cart && $cart->get_cart_contents_count() > 0;
            if ( $context['is_logged_in'] ) {
                $context['woo_purchased'] = wc_get_customer_order_count( get_current_user_id() ) > 0;
            }
        }

        // Set return visitor cookie
        if ( ! $context['is_return'] && ! headers_sent() ) {
            setcookie( 'spslider_visited', '1', time() + ( 365 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
        }

        return $context;
    }

    /**
     * Detect device type from user agent.
     */
    private static function detect_device() {
        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        if ( preg_match( '/Mobile|Android.*Mobile|iPhone|iPod/i', $ua ) ) return 'mobile';
        if ( preg_match( '/iPad|Android(?!.*Mobile)|Tablet/i', $ua ) ) return 'tablet';
        return 'desktop';
    }

    /**
     * Evaluate visibility rules against context.
     *
     * Rules format: [ [ 'field' => 'is_logged_in', 'operator' => 'equals', 'value' => true ], ... ]
     * Multiple rules are ANDed.
     */
    private static function evaluate_rules( $rules, $context ) {
        foreach ( $rules as $rule ) {
            $field    = $rule['field'] ?? '';
            $operator = $rule['operator'] ?? 'equals';
            $value    = $rule['value'] ?? '';
            $actual   = $context[ $field ] ?? null;

            if ( $actual === null ) continue;

            $match = false;
            switch ( $operator ) {
                case 'equals':
                    $match = ( $actual == $value ); // phpcs:ignore Universal.Operators.StrictComparisons
                    break;
                case 'not_equals':
                    $match = ( $actual != $value ); // phpcs:ignore Universal.Operators.StrictComparisons
                    break;
                case 'contains':
                    $match = is_string( $actual ) && stripos( $actual, (string) $value ) !== false;
                    break;
                case 'not_contains':
                    $match = is_string( $actual ) && stripos( $actual, (string) $value ) === false;
                    break;
                case 'greater_than':
                    $match = is_numeric( $actual ) && $actual > $value;
                    break;
                case 'less_than':
                    $match = is_numeric( $actual ) && $actual < $value;
                    break;
                case 'in':
                    $list = is_array( $value ) ? $value : explode( ',', (string) $value );
                    $match = in_array( $actual, array_map( 'trim', $list ), false ); // phpcs:ignore Universal.Operators.StrictComparisons
                    break;
            }

            if ( ! $match ) return false; // AND logic — any failure hides slide
        }

        return true;
    }
}
