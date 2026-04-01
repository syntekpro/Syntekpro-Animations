<?php
defined( 'ABSPATH' ) || exit;

/**
 * All CRUD operations for sliders, slides, and layers.
 */
class SPSLIDER_Database {

    // ── Slider CRUD ───────────────────────────────────────────────────────────

    public static function get_sliders( $args = [] ) {
        global $wpdb;
        $table    = $wpdb->prefix . 'spslider_sliders';
        $defaults = [ 'status' => 1, 'orderby' => 'id', 'order' => 'DESC', 'limit' => 100, 'offset' => 0 ];
        $args     = wp_parse_args( $args, $defaults );

        $sql    = "SELECT * FROM {$table} WHERE 1=1";
        $params = [];

        if ( $args['status'] !== '' ) {
            $sql .= ' AND status = %d';
            $params[] = (int) $args['status'];
        }

        $orderby   = in_array( strtoupper( $args['order'] ), [ 'ASC', 'DESC' ], true ) ? strtoupper( $args['order'] ) : 'DESC';
        $order_col = sanitize_key( $args['orderby'] );
        $sql      .= " ORDER BY {$order_col} {$orderby} LIMIT %d OFFSET %d";
        $params[]  = max( 1, (int) $args['limit'] );
        $params[]  = max( 0, (int) $args['offset'] );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

        foreach ( $results as &$row ) {
            $row->settings = json_decode( $row->settings, true ) ?: [];
        }
        return $results;
    }

    public static function get_slider( $id ) {
        global $wpdb;
        $slider = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}spslider_sliders WHERE id = %d",
            (int) $id
        ) );
        if ( $slider ) {
            $slider->settings = json_decode( $slider->settings, true ) ?: [];
        }
        return $slider;
    }

    public static function create_slider( $data ) {
        global $wpdb;
        $table    = $wpdb->prefix . 'spslider_sliders';
        $name     = sanitize_text_field( $data['name'] ?? 'Untitled Slider' );
        $slug     = self::unique_slug( $name, $table );
        $settings = wp_json_encode( self::default_slider_settings( $data['settings'] ?? [] ) );

        $ok = $wpdb->insert( $table,
            [ 'name' => $name, 'slug' => $slug, 'settings' => $settings, 'status' => 1 ],
            [ '%s', '%s', '%s', '%d' ]
        );
        return $ok ? $wpdb->insert_id : false;
    }

    public static function update_slider( $id, $data ) {
        global $wpdb;
        $update = []; $formats = [];
        if ( isset( $data['name'] ) )     { $update['name']     = sanitize_text_field( $data['name'] ); $formats[] = '%s'; }
        if ( isset( $data['settings'] ) ) { $update['settings'] = wp_json_encode( $data['settings'] );  $formats[] = '%s'; }
        if ( isset( $data['status'] ) )   { $update['status']   = (int) $data['status'];                $formats[] = '%d'; }
        if ( empty( $update ) ) return false;
        return $wpdb->update( $wpdb->prefix . 'spslider_sliders', $update, [ 'id' => (int) $id ], $formats, [ '%d' ] );
    }

    public static function delete_slider( $id ) {
        global $wpdb;
        $id = (int) $id;

        $slide_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}spslider_slides WHERE slider_id = %d", $id
        ) );
        if ( ! empty( $slide_ids ) ) {
            $in = implode( ',', array_map( 'intval', $slide_ids ) );
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->query( "DELETE FROM {$wpdb->prefix}spslider_layers WHERE slide_id IN ({$in})" );
        }

        $wpdb->delete( $wpdb->prefix . 'spslider_slides',    [ 'slider_id' => $id ], [ '%d' ] );
        $wpdb->delete( $wpdb->prefix . 'spslider_analytics', [ 'slider_id' => $id ], [ '%d' ] );
        return $wpdb->delete( $wpdb->prefix . 'spslider_sliders', [ 'id' => $id ], [ '%d' ] );
    }

    public static function duplicate_slider( $id ) {
        $slider = self::get_slider( $id );
        if ( ! $slider ) return false;

        $new_id = self::create_slider( [
            'name'     => $slider->name . ' (Copy)',
            'settings' => $slider->settings,
        ] );
        if ( ! $new_id ) return false;

        foreach ( self::get_slides( $id ) as $slide ) {
            $new_slide_id = self::create_slide( [
                'slider_id'  => $new_id,
                'title'      => $slide->title,
                'sort_order' => $slide->sort_order,
                'settings'   => $slide->settings,
            ] );
            if ( $new_slide_id ) {
                foreach ( self::get_layers( $slide->id ) as $layer ) {
                    self::create_layer( [
                        'slide_id'   => $new_slide_id,
                        'type'       => $layer->type,
                        'sort_order' => $layer->sort_order,
                        'settings'   => $layer->settings,
                    ] );
                }
            }
        }
        return $new_id;
    }

    // ── Slide CRUD ────────────────────────────────────────────────────────────

    public static function get_slides( $slider_id ) {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}spslider_slides WHERE slider_id = %d AND status = 1 ORDER BY sort_order ASC",
            (int) $slider_id
        ) );
        foreach ( $results as &$row ) {
            $row->settings = json_decode( $row->settings, true ) ?: [];
        }
        return $results;
    }

    public static function get_slide( $id ) {
        global $wpdb;
        $slide = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}spslider_slides WHERE id = %d", (int) $id
        ) );
        if ( $slide ) {
            $slide->settings = json_decode( $slide->settings, true ) ?: [];
        }
        return $slide;
    }

    public static function create_slide( $data ) {
        global $wpdb;
        $table     = $wpdb->prefix . 'spslider_slides';
        $slider_id = (int) ( $data['slider_id'] ?? 0 );

        if ( ! isset( $data['sort_order'] ) ) {
            $data['sort_order'] = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COALESCE(MAX(sort_order),0)+1 FROM {$table} WHERE slider_id = %d", $slider_id
            ) );
        }

        $ok = $wpdb->insert( $table, [
            'slider_id'  => $slider_id,
            'title'      => sanitize_text_field( $data['title'] ?? 'Slide' ),
            'sort_order' => (int) $data['sort_order'],
            'settings'   => wp_json_encode( self::default_slide_settings( $data['settings'] ?? [] ) ),
            'status'     => 1,
        ], [ '%d', '%s', '%d', '%s', '%d' ] );

        return $ok ? $wpdb->insert_id : false;
    }

    public static function update_slide( $id, $data ) {
        global $wpdb;
        $update = []; $formats = [];
        if ( isset( $data['title'] ) )      { $update['title']      = sanitize_text_field( $data['title'] ); $formats[] = '%s'; }
        if ( isset( $data['sort_order'] ) ) { $update['sort_order'] = (int) $data['sort_order'];             $formats[] = '%d'; }
        if ( isset( $data['settings'] ) )   { $update['settings']   = wp_json_encode( $data['settings'] );   $formats[] = '%s'; }
        if ( isset( $data['status'] ) )     { $update['status']     = (int) $data['status'];                  $formats[] = '%d'; }
        if ( empty( $update ) ) return false;
        return $wpdb->update( $wpdb->prefix . 'spslider_slides', $update, [ 'id' => (int) $id ], $formats, [ '%d' ] );
    }

    public static function delete_slide( $id ) {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'spslider_layers', [ 'slide_id' => (int) $id ], [ '%d' ] );
        return $wpdb->delete( $wpdb->prefix . 'spslider_slides', [ 'id' => (int) $id ], [ '%d' ] );
    }

    public static function reorder_slides( $slider_id, $order ) {
        global $wpdb;
        foreach ( $order as $sort => $slide_id ) {
            $wpdb->update(
                $wpdb->prefix . 'spslider_slides',
                [ 'sort_order' => (int) $sort ],
                [ 'id' => (int) $slide_id, 'slider_id' => (int) $slider_id ],
                [ '%d' ], [ '%d', '%d' ]
            );
        }
    }

    // ── Layer CRUD ────────────────────────────────────────────────────────────

    public static function get_layers( $slide_id ) {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}spslider_layers WHERE slide_id = %d ORDER BY sort_order ASC",
            (int) $slide_id
        ) );
        foreach ( $results as &$row ) {
            $row->settings = json_decode( $row->settings, true ) ?: [];
        }
        return $results;
    }

    public static function create_layer( $data ) {
        global $wpdb;
        $table    = $wpdb->prefix . 'spslider_layers';
        $slide_id = (int) ( $data['slide_id'] ?? 0 );

        if ( ! isset( $data['sort_order'] ) ) {
            $data['sort_order'] = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COALESCE(MAX(sort_order),0)+1 FROM {$table} WHERE slide_id = %d", $slide_id
            ) );
        }

        $type     = sanitize_key( $data['type'] ?? 'text' );
        $settings = self::default_layer_settings( $type, $data['settings'] ?? [] );

        $ok = $wpdb->insert( $table, [
            'slide_id'   => $slide_id,
            'type'       => $type,
            'sort_order' => (int) $data['sort_order'],
            'settings'   => wp_json_encode( $settings ),
        ], [ '%d', '%s', '%d', '%s' ] );

        return $ok ? $wpdb->insert_id : false;
    }

    public static function update_layer( $id, $data ) {
        global $wpdb;
        $update = []; $formats = [];
        if ( isset( $data['sort_order'] ) ) { $update['sort_order'] = (int) $data['sort_order'];           $formats[] = '%d'; }
        if ( isset( $data['settings'] ) )   { $update['settings']   = wp_json_encode( $data['settings'] ); $formats[] = '%s'; }
        if ( isset( $data['type'] ) )       { $update['type']       = sanitize_key( $data['type'] );        $formats[] = '%s'; }
        if ( empty( $update ) ) return false;
        return $wpdb->update( $wpdb->prefix . 'spslider_layers', $update, [ 'id' => (int) $id ], $formats, [ '%d' ] );
    }

    public static function delete_layer( $id ) {
        global $wpdb;
        return $wpdb->delete( $wpdb->prefix . 'spslider_layers', [ 'id' => (int) $id ], [ '%d' ] );
    }

    public static function reorder_layers( $slide_id, $order ) {
        global $wpdb;
        foreach ( $order as $sort => $layer_id ) {
            $wpdb->update(
                $wpdb->prefix . 'spslider_layers',
                [ 'sort_order' => (int) $sort ],
                [ 'id' => (int) $layer_id, 'slide_id' => (int) $slide_id ],
                [ '%d' ], [ '%d', '%d' ]
            );
        }
    }

    // ── Batch save (full slider save from editor) ─────────────────────────────

    public static function save_full_slider( $slider_id, $slider_settings, $slides_data ) {
        global $wpdb;

        self::update_slider( $slider_id, [ 'settings' => $slider_settings ] );

        $committed_slide_ids = [];

        foreach ( $slides_data as $slide_data ) {
            $sid = isset( $slide_data['id'] ) && $slide_data['id'] ? (int) $slide_data['id'] : 0;

            if ( $sid ) {
                self::update_slide( $sid, $slide_data );
                $committed_slide_ids[] = $sid;
            } else {
                $sid = self::create_slide( array_merge( $slide_data, [ 'slider_id' => $slider_id ] ) );
                if ( $sid ) $committed_slide_ids[] = $sid;
            }
            if ( ! $sid ) continue;

            $committed_layer_ids = [];
            foreach ( ( $slide_data['layers'] ?? [] ) as $layer_data ) {
                $lid = isset( $layer_data['id'] ) && $layer_data['id'] ? (int) $layer_data['id'] : 0;
                if ( $lid ) {
                    self::update_layer( $lid, $layer_data );
                    $committed_layer_ids[] = $lid;
                } else {
                    $lid = self::create_layer( array_merge( $layer_data, [ 'slide_id' => $sid ] ) );
                    if ( $lid ) $committed_layer_ids[] = $lid;
                }
            }

            // Remove layers that were deleted in editor
            $db_layer_ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}spslider_layers WHERE slide_id = %d", $sid
            ) );
            foreach ( $db_layer_ids as $lid ) {
                if ( ! in_array( (int) $lid, $committed_layer_ids, true ) ) {
                    self::delete_layer( (int) $lid );
                }
            }
        }

        // Remove slides that were deleted in editor
        $db_slide_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}spslider_slides WHERE slider_id = %d", $slider_id
        ) );
        foreach ( $db_slide_ids as $sid ) {
            if ( ! in_array( (int) $sid, $committed_slide_ids, true ) ) {
                self::delete_slide( (int) $sid );
            }
        }

        return true;
    }

    // ── Load full slider for editor ───────────────────────────────────────────

    public static function load_full_slider( $slider_id ) {
        $slider = self::get_slider( $slider_id );
        if ( ! $slider ) return null;

        $slides = self::get_slides( $slider_id );
        foreach ( $slides as &$slide ) {
            $slide->layers = self::get_layers( $slide->id );
        }

        return [
            'id'       => $slider->id,
            'name'     => $slider->name,
            'settings' => $slider->settings,
            'slides'   => $slides,
        ];
    }

    // ── Default settings ──────────────────────────────────────────────────────

    public static function default_slider_settings( $override = [] ) {
        return array_merge( [
            'width'             => 1200,
            'height'            => 500,
            'autoplay'          => true,
            'autoplay_speed'    => 5000,
            'loop'              => true,
            'arrows'            => true,
            'dots'              => true,
            'speed'             => 700,
            'easing'            => 'ease-in-out',
            'pause_on_hover'    => true,
            'transition'        => 'slide',
            'touch'             => true,
            'swipe_sensitivity' => 50,
            'swipe_direction'   => 'horizontal',
            'scaling_mode'      => 'auto',
            'lazy_load'         => true,
            'preload_next'      => true,
            'keyboard_nav'      => true,
            'parallax'          => false,
            'arrows_style'      => 'default',
            'dots_style'        => 'default',
            'full_width'        => false,
            'full_screen'       => false,
            'aspect_ratio'      => '16:9',
        ], $override );
    }

    public static function default_slide_settings( $override = [] ) {
        return array_merge( [
            'bg_color'            => '#1a1a2e',
            'bg_image'            => '',
            'bg_repeat'           => 'no-repeat',
            'bg_position'         => 'center center',
            'bg_size'             => 'cover',
            'bg_video'            => '',
            'bg_overlay'          => '#000000',
            'bg_overlay_opacity'  => 0,
            'link'                => '',
            'link_target'         => '_self',
            'ken_burns'           => false,
            'ken_burns_zoom'      => 120,
            'ken_burns_direction' => 'in',
            'thumbnail'           => '',
        ], $override );
    }

    public static function default_layer_settings( $type, $override = [] ) {
        $base = [
            'x'              => 60,
            'y'              => 60,
            'width'          => 320,
            'height'         => 80,
            'z_index'        => 10,
            'visible'        => true,
            'locked'         => false,
            'opacity'        => 1,
            'rotation'       => 0,
            'parallax_depth' => 0,
            'alt'            => '',
            'name'           => ucfirst( $type ) . ' Layer',
            'animation_in'   => [ 'effect' => 'fade', 'delay' => 300, 'duration' => 700, 'easing' => 'ease-out', 'distance' => 40 ],
            'animation_out'  => [ 'effect' => 'fade', 'delay' => 0,   'duration' => 500, 'easing' => 'ease-in',  'distance' => 40 ],
            'breakpoints'    => [
                'tablet' => [ 'visible' => true, 'font_size_scale' => 0.85 ],
                'mobile' => [ 'visible' => true, 'font_size_scale' => 0.7 ],
            ],
            'hover'  => [ 'effect' => 'none', 'scale' => 1.05, 'glow' => false ],
            'click'  => [ 'action' => 'none', 'url' => '', 'target' => '_self', 'slide' => 0 ],
            'style'  => [],
        ];

        switch ( $type ) {
            case 'text':
                $base = array_merge( $base, [
                    'content'        => 'Your Amazing Headline',
                    'font_size'      => 36,
                    'font_weight'    => '700',
                    'font_family'    => 'inherit',
                    'color'          => '#ffffff',
                    'text_align'     => 'left',
                    'line_height'    => 1.3,
                    'letter_spacing' => 0,
                    'tag'            => 'h2',
                    'text_shadow'    => '',
                ] );
                break;
            case 'image':
                $base = array_merge( $base, [
                    'src'           => '',
                    'srcset'        => '',
                    'object_fit'    => 'cover',
                    'border_radius' => 0,
                    'width'         => 400,
                    'height'        => 250,
                ] );
                break;
            case 'button':
                $base = array_merge( $base, [
                    'label'         => 'Get Started',
                    'url'           => '#',
                    'target'        => '_self',
                    'bg_color'      => '#e94560',
                    'text_color'    => '#ffffff',
                    'border_radius' => 6,
                    'padding'       => '14px 28px',
                    'font_size'     => 16,
                    'font_weight'   => '600',
                    'hover_bg'      => '#c73652',
                    'border'        => 'none',
                    'width'         => 180,
                    'height'        => 52,
                ] );
                break;
            case 'video':
                $base = array_merge( $base, [
                    'src'      => '',
                    'type'     => 'mp4',
                    'autoplay' => false,
                    'loop'     => false,
                    'muted'    => true,
                    'controls' => true,
                    'poster'   => '',
                    'width'    => 560,
                    'height'   => 315,
                ] );
                break;
            case 'shape':
                $base = array_merge( $base, [
                    'shape'        => 'rectangle',
                    'fill'         => 'rgba(255,255,255,0.15)',
                    'stroke'       => '',
                    'stroke_width' => 0,
                    'border_radius'=> 8,
                    'width'        => 200,
                    'height'       => 120,
                ] );
                break;
            case 'countdown':
                $base = array_merge( $base, [
                    'countdown_target'  => '',
                    'countdown_label'   => '',
                    'countdown_expired' => 'Expired!',
                    'target_date'       => '',
                    'font_size'         => 28,
                    'font_weight'       => '700',
                    'color'             => '#ffffff',
                    'width'             => 400,
                    'height'            => 80,
                ] );
                break;
            case 'icon':
                $base = array_merge( $base, [
                    'icon_class'  => 'dashicons dashicons-star-filled',
                    'icon_size'   => 48,
                    'color'       => '#ffffff',
                    'width'       => 60,
                    'height'      => 60,
                ] );
                break;
            case 'lottie':
                $base = array_merge( $base, [
                    'lottie_src'      => '',
                    'lottie_autoplay' => true,
                    'lottie_loop'     => true,
                    'src'             => '',
                    'autoplay'        => true,
                    'loop'            => true,
                    'speed'           => 1,
                    'width'           => 300,
                    'height'          => 300,
                ] );
                break;
            case 'html':
                $base = array_merge( $base, [
                    'html_content' => '<div>Custom HTML</div>',
                    'content'      => '<div>Custom HTML</div>',
                    'width'        => 400,
                    'height'       => 200,
                ] );
                break;
        }

        return array_merge( $base, $override );
    }

    // ── Utilities ─────────────────────────────────────────────────────────────

    private static function unique_slug( $name, $table ) {
        global $wpdb;
        $slug = sanitize_title( $name );
        $base = $slug;
        $i    = 2;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        while ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $slug ) ) ) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
