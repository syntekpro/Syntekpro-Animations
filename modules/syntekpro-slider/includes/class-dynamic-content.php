<?php
defined( 'ABSPATH' ) || exit;

/**
 * Dynamic content integration — pull slides from WordPress posts,
 * WooCommerce products, ACF fields, and custom post types.
 */
class SPSLIDER_Dynamic_Content {

    /**
     * Generate slides array from a dynamic source config.
     *
     * @param array $config {
     *   source:      'posts'|'woocommerce'|'acf'|'cpt'
     *   post_type:   string
     *   taxonomy:    string
     *   term:        string
     *   posts_per_page: int
     *   field_map:   array  [layer_field => post_field]
     *   image_size:  string
     * }
     * @return array  Slide data arrays ready for SPSLIDER_Database::create_slide().
     */
    public static function build_slides( array $config ) {
        $source = sanitize_key( $config['source'] ?? 'posts' );

        switch ( $source ) {
            case 'woocommerce':
                return self::from_woocommerce( $config );
            case 'acf':
                return self::from_acf( $config );
            case 'cpt':
            case 'posts':
            default:
                return self::from_posts( $config );
        }
    }

    // ── WordPress posts ───────────────────────────────────────────────────────

    private static function from_posts( array $config ) {
        $args = [
            'post_type'      => sanitize_key( $config['post_type'] ?? 'post' ),
            'posts_per_page' => min( 50, max( 1, (int) ( $config['posts_per_page'] ?? 10 ) ) ),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        ];

        if ( ! empty( $config['taxonomy'] ) && ! empty( $config['term'] ) ) {
            $args['tax_query'] = [ [
                'taxonomy' => sanitize_key( $config['taxonomy'] ),
                'field'    => 'slug',
                'terms'    => sanitize_key( $config['term'] ),
            ] ];
        }

        $query   = new WP_Query( $args );
        $slides  = [];
        $order   = 0;

        foreach ( $query->posts as $post ) {
            $thumb_id  = get_post_thumbnail_id( $post->ID );
            $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, $config['image_size'] ?? 'large' ) : '';

            $slides[] = [
                'title'      => $post->post_title,
                'sort_order' => $order++,
                'settings'   => [
                    'bg_image'           => $thumb_url,
                    'bg_size'            => 'cover',
                    'bg_overlay'         => '#000000',
                    'bg_overlay_opacity' => 0.4,
                    'link'               => get_permalink( $post->ID ),
                ],
                'layers' => self::map_layers( $post, [
                    'title'   => $post->post_title,
                    'excerpt' => wp_strip_all_tags( get_the_excerpt( $post ) ),
                    'url'     => get_permalink( $post->ID ),
                ], $config['field_map'] ?? [] ),
            ];
        }
        wp_reset_postdata();
        return $slides;
    }

    // ── WooCommerce products ──────────────────────────────────────────────────

    private static function from_woocommerce( array $config ) {
        if ( ! class_exists( 'WooCommerce' ) ) return [];

        $args = [
            'post_type'      => 'product',
            'posts_per_page' => min( 50, max( 1, (int) ( $config['posts_per_page'] ?? 10 ) ) ),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        ];

        if ( ! empty( $config['term'] ) ) {
            $args['tax_query'] = [ [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => sanitize_key( $config['term'] ),
            ] ];
        }

        $query  = new WP_Query( $args );
        $slides = [];
        $order  = 0;

        foreach ( $query->posts as $post ) {
            $product   = wc_get_product( $post->ID );
            if ( ! $product ) continue;

            $thumb_url = get_the_post_thumbnail_url( $post->ID, $config['image_size'] ?? 'large' );
            $price     = wp_strip_all_tags( $product->get_price_html() );

            $slides[] = [
                'title'      => $product->get_name(),
                'sort_order' => $order++,
                'settings'   => [
                    'bg_color'           => '#111',
                    'bg_image'           => $thumb_url ?: '',
                    'bg_size'            => 'cover',
                    'bg_overlay'         => '#000',
                    'bg_overlay_opacity' => 0.45,
                    'link'               => get_permalink( $post->ID ),
                ],
                'layers' => self::map_layers( $post, [
                    'title'   => $product->get_name(),
                    'excerpt' => wp_strip_all_tags( $product->get_short_description() ),
                    'price'   => $price,
                    'url'     => get_permalink( $post->ID ),
                ], $config['field_map'] ?? [] ),
            ];
        }
        wp_reset_postdata();
        return $slides;
    }

    // ── ACF fields ────────────────────────────────────────────────────────────

    private static function from_acf( array $config ) {
        if ( ! function_exists( 'get_fields' ) ) return [];

        $post_id   = (int) ( $config['post_id'] ?? get_the_ID() );
        $group_key = sanitize_text_field( $config['acf_group'] ?? '' );

        $fields = $group_key ? get_field( $group_key, $post_id ) : get_fields( $post_id );
        if ( ! is_array( $fields ) ) return [];

        $slides = [];
        $order  = 0;

        // Treat each repeater row or each array group as a slide
        foreach ( $fields as $row ) {
            if ( ! is_array( $row ) ) continue;
            $slides[] = [
                'title'      => sanitize_text_field( $row[ $config['title_field'] ?? 'title' ] ?? 'Slide' ),
                'sort_order' => $order++,
                'settings'   => [
                    'bg_image' => esc_url_raw( $row[ $config['image_field'] ?? 'image' ] ?? '' ),
                    'bg_size'  => 'cover',
                ],
                'layers' => self::map_layers( null, $row, $config['field_map'] ?? [] ),
            ];
        }
        return $slides;
    }

    // ── Layer mapper ──────────────────────────────────────────────────────────

    /**
     * Build minimal layer list from field data, applying a field_map.
     * field_map: [ 'title_layer' => 'title', 'meta_layer' => 'excerpt' ]
     */
    private static function map_layers( $post, array $fields, array $field_map ) {
        $layers = [];
        $y      = 120;

        // Default: title + excerpt + button
        $title_text   = $fields['title']   ?? ( $post ? $post->post_title : '' );
        $excerpt_text = $fields['excerpt'] ?? '';
        $url          = $fields['url']     ?? '';

        if ( isset( $field_map['title'] ) )   $title_text   = $fields[ $field_map['title'] ]   ?? $title_text;
        if ( isset( $field_map['excerpt'] ) ) $excerpt_text = $fields[ $field_map['excerpt'] ] ?? $excerpt_text;

        $layers[] = [
            'type' => 'text',
            'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                'content'    => wp_kses_post( $title_text ),
                'x'          => 80,
                'y'          => $y,
                'width'      => 600,
                'font_size'  => 40,
                'font_weight'=> '700',
                'color'      => '#ffffff',
                'tag'        => 'h2',
                'animation_in' => [ 'effect' => 'slide-left', 'delay' => 200, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 60 ],
            ] ),
        ];
        $y += 100;

        if ( $excerpt_text ) {
            $layers[] = [
                'type' => 'text',
                'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                    'content'   => wp_kses_post( wp_trim_words( $excerpt_text, 18 ) ),
                    'x'         => 80,
                    'y'         => $y,
                    'width'     => 560,
                    'font_size' => 18,
                    'color'     => '#eeeeee',
                    'animation_in' => [ 'effect' => 'fade', 'delay' => 400, 'duration' => 700, 'easing' => 'ease-out', 'distance' => 0 ],
                ] ),
            ];
            $y += 80;
        }

        if ( $url ) {
            $layers[] = [
                'type' => 'button',
                'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                    'x'   => 80,
                    'y'   => $y,
                    'url' => esc_url_raw( $url ),
                    'animation_in' => [ 'effect' => 'fade', 'delay' => 600, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 0 ],
                ] ),
            ];
        }

        return $layers;
    }

    /**
     * Returns available dynamic sources for the editor UI.
     */
    public static function get_sources() {
        $sources = [
            [ 'key' => 'posts', 'label' => 'WordPress Posts', 'available' => true ],
            [ 'key' => 'cpt',   'label' => 'Custom Post Type', 'available' => true ],
            [ 'key' => 'woocommerce', 'label' => 'WooCommerce Products', 'available' => class_exists( 'WooCommerce' ) ],
            [ 'key' => 'acf',   'label' => 'ACF Fields', 'available' => function_exists( 'get_fields' ) ],
        ];
        return $sources;
    }

    /**
     * Returns all registered public post types for the editor dropdown.
     */
    public static function get_post_types() {
        $types = get_post_types( [ 'public' => true ], 'objects' );
        $result = [];
        foreach ( $types as $type ) {
            $result[] = [ 'key' => $type->name, 'label' => $type->labels->name ];
        }
        return $result;
    }
}
