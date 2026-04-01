<?php
defined( 'ABSPATH' ) || exit;

/**
 * A/B testing: run split tests on slider variations and track winner by conversion rate.
 * Creates variant copies of sliders and rotates between them.
 */
class SPSLIDER_AB_Test {

    const OPTION_KEY = 'spslider_ab_tests';

    /**
     * Get all active A/B tests.
     */
    public static function get_tests() {
        return get_option( self::OPTION_KEY, [] );
    }

    /**
     * Create a new A/B test.
     *
     * @param int    $slider_id_a  Control slider ID.
     * @param int    $slider_id_b  Variant slider ID.
     * @param string $name         Test name.
     * @param int    $traffic_pct  Traffic % to variant B (0-100).
     * @return array The test config.
     */
    public static function create_test( $slider_id_a, $slider_id_b, $name = '', $traffic_pct = 50 ) {
        $tests = self::get_tests();

        $test = [
            'id'           => 'abtest_' . wp_generate_uuid4(),
            'name'         => sanitize_text_field( $name ?: "A/B Test #{$slider_id_a}" ),
            'slider_a'     => (int) $slider_id_a,
            'slider_b'     => (int) $slider_id_b,
            'traffic_b'    => max( 0, min( 100, (int) $traffic_pct ) ),
            'active'       => true,
            'created_at'   => gmdate( 'c' ),
            'views_a'      => 0,
            'views_b'      => 0,
            'conversions_a'=> 0,
            'conversions_b'=> 0,
        ];

        $tests[ $test['id'] ] = $test;
        update_option( self::OPTION_KEY, $tests );
        return $test;
    }

    /**
     * Delete a test.
     */
    public static function delete_test( $test_id ) {
        $tests = self::get_tests();
        unset( $tests[ sanitize_key( $test_id ) ] );
        update_option( self::OPTION_KEY, $tests );
    }

    /**
     * Get the slider ID to show for an A/B test (randomly by traffic split).
     * Uses a session cookie to keep the experience consistent per user.
     */
    public static function resolve_slider( $slider_id ) {
        $tests = self::get_tests();

        foreach ( $tests as $test ) {
            if ( ! $test['active'] ) continue;
            if ( (int) $test['slider_a'] !== (int) $slider_id ) continue;

            // Check session cookie for existing assignment
            $cookie_key = 'spslider_ab_' . $test['id'];
            if ( isset( $_COOKIE[ $cookie_key ] ) ) {
                $variant = sanitize_key( $_COOKIE[ $cookie_key ] );
                return $variant === 'b' ? (int) $test['slider_b'] : (int) $test['slider_a'];
            }

            // Assign variant based on traffic split
            $rand    = wp_rand( 1, 100 );
            $variant = $rand <= $test['traffic_b'] ? 'b' : 'a';

            // Set cookie for 30 days (on frontend only)
            if ( ! headers_sent() ) {
                setcookie( $cookie_key, $variant, time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
            }

            return $variant === 'b' ? (int) $test['slider_b'] : (int) $test['slider_a'];
        }

        return (int) $slider_id;
    }

    /**
     * Record a view for A/B tracking.
     */
    public static function record_view( $slider_id ) {
        $tests = self::get_tests();

        foreach ( $tests as &$test ) {
            if ( ! $test['active'] ) continue;
            if ( (int) $test['slider_a'] === (int) $slider_id ) {
                $test['views_a']++;
                update_option( self::OPTION_KEY, $tests );
                return;
            }
            if ( (int) $test['slider_b'] === (int) $slider_id ) {
                $test['views_b']++;
                update_option( self::OPTION_KEY, $tests );
                return;
            }
        }
    }

    /**
     * Record a conversion for A/B tracking.
     */
    public static function record_conversion( $slider_id ) {
        $tests = self::get_tests();

        foreach ( $tests as &$test ) {
            if ( ! $test['active'] ) continue;
            if ( (int) $test['slider_a'] === (int) $slider_id ) {
                $test['conversions_a']++;
                update_option( self::OPTION_KEY, $tests );
                return;
            }
            if ( (int) $test['slider_b'] === (int) $slider_id ) {
                $test['conversions_b']++;
                update_option( self::OPTION_KEY, $tests );
                return;
            }
        }
    }

    /**
     * Get test results with conversion rates and statistical significance.
     */
    public static function get_results( $test_id ) {
        $tests = self::get_tests();
        $test  = $tests[ sanitize_key( $test_id ) ] ?? null;
        if ( ! $test ) return null;

        $rate_a = $test['views_a'] > 0 ? round( ( $test['conversions_a'] / $test['views_a'] ) * 100, 2 ) : 0;
        $rate_b = $test['views_b'] > 0 ? round( ( $test['conversions_b'] / $test['views_b'] ) * 100, 2 ) : 0;

        $winner = null;
        if ( $test['views_a'] >= 100 && $test['views_b'] >= 100 ) {
            // Simple significance check (Z-score > 1.96 for 95% confidence)
            $p_a  = $test['conversions_a'] / max( 1, $test['views_a'] );
            $p_b  = $test['conversions_b'] / max( 1, $test['views_b'] );
            $se_a = sqrt( $p_a * ( 1 - $p_a ) / max( 1, $test['views_a'] ) );
            $se_b = sqrt( $p_b * ( 1 - $p_b ) / max( 1, $test['views_b'] ) );
            $se   = sqrt( $se_a * $se_a + $se_b * $se_b );

            if ( $se > 0 ) {
                $z = abs( $p_b - $p_a ) / $se;
                if ( $z >= 1.96 ) {
                    $winner = $rate_b > $rate_a ? 'b' : 'a';
                }
            }
        }

        return [
            'test'     => $test,
            'rate_a'   => $rate_a,
            'rate_b'   => $rate_b,
            'winner'   => $winner,
            'lift'     => $rate_a > 0 ? round( ( ( $rate_b - $rate_a ) / $rate_a ) * 100, 1 ) : 0,
        ];
    }
}
