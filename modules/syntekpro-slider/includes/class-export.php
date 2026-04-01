<?php
defined( 'ABSPATH' ) || exit;

/**
 * Import / Export: JSON-based local backup, cross-site portability, and ZIP bundles.
 */
class SPSLIDER_Export {

    /**
     * Export a slider as a JSON file (downloaded by the browser).
     */
    public static function export_json( $slider_id ) {
        $data = SPSLIDER_Database::load_full_slider( (int) $slider_id );
        if ( ! $data ) wp_die( 'Slider not found.' );

        $export = [
            'format'    => 'syntekpro-slider',
            'version'   => SPSLIDER_VERSION,
            'exported'  => gmdate( 'c' ),
            'slider'    => $data,
        ];

        $filename = sanitize_file_name( 'spslider-' . ( $data['name'] ?? $slider_id ) . '.json' );

        if ( ob_get_length() ) ob_clean();
        header( 'Content-Type: application/json; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        exit;
    }

    /**
     * Export ALL sliders as a JSON array (bulk backup).
     */
    public static function export_all_json() {
        $sliders = SPSLIDER_Database::get_sliders( [ 'status' => '', 'limit' => 9999 ] );
        $out     = [];

        foreach ( $sliders as $slider ) {
            $out[] = SPSLIDER_Database::load_full_slider( $slider->id );
        }

        $export = [
            'format'   => 'syntekpro-slider-bulk',
            'version'  => SPSLIDER_VERSION,
            'exported' => gmdate( 'c' ),
            'count'    => count( $out ),
            'sliders'  => $out,
        ];

        if ( ob_get_length() ) ob_clean();
        header( 'Content-Type: application/json; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="spslider-backup-' . gmdate( 'Y-m-d' ) . '.json"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        exit;
    }

    /**
     * Import a slider from an uploaded JSON file.
     *
     * @param string $file_path  Path to the uploaded JSON file.
     * @return int|WP_Error      New slider ID, or WP_Error on failure.
     */
    public static function import_json( $file_path ) {
        $json = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        if ( ! $json ) {
            return new WP_Error( 'read_error', __( 'Could not read the import file.', 'syntekpro-slider' ) );
        }

        $data = json_decode( $json, true );
        if ( json_last_error() !== JSON_ERROR_NONE || empty( $data ) ) {
            return new WP_Error( 'parse_error', __( 'Invalid JSON in import file.', 'syntekpro-slider' ) );
        }

        // Validate format
        $format = $data['format'] ?? '';
        if ( ! in_array( $format, [ 'syntekpro-slider', 'syntekpro-slider-bulk' ], true ) ) {
            return new WP_Error( 'format_error', __( 'Unsupported import format.', 'syntekpro-slider' ) );
        }

        if ( $format === 'syntekpro-slider-bulk' ) {
            return self::import_bulk( $data['sliders'] ?? [] );
        }

        return self::import_single( $data['slider'] ?? [] );
    }

    /**
     * Import a single slider from parsed data.
     */
    private static function import_single( $slider_data ) {
        if ( empty( $slider_data ) ) {
            return new WP_Error( 'empty', __( 'No slider data found in import.', 'syntekpro-slider' ) );
        }

        $name = sanitize_text_field( ( $slider_data['name'] ?? 'Imported Slider' ) . ' (Import)' );

        // Create new slider
        $new_id = SPSLIDER_Database::create_slider( [
            'name'     => $name,
            'settings' => $slider_data['settings'] ?? [],
        ] );

        if ( ! $new_id ) {
            return new WP_Error( 'create_failed', __( 'Could not create slider.', 'syntekpro-slider' ) );
        }

        // Create slides + layers
        foreach ( ( $slider_data['slides'] ?? [] ) as $slide ) {
            $layers = [];
            if ( is_object( $slide ) ) {
                $layers   = $slide->layers ?? [];
                $settings = $slide->settings ?? [];
                $title    = $slide->title ?? 'Slide';
                $order    = $slide->sort_order ?? 0;
            } else {
                $layers   = $slide['layers'] ?? [];
                $settings = $slide['settings'] ?? [];
                $title    = $slide['title'] ?? 'Slide';
                $order    = $slide['sort_order'] ?? 0;
            }

            $new_slide_id = SPSLIDER_Database::create_slide( [
                'slider_id'  => $new_id,
                'title'      => $title,
                'sort_order' => $order,
                'settings'   => $settings,
            ] );

            if ( ! $new_slide_id ) continue;

            foreach ( $layers as $layer ) {
                if ( is_object( $layer ) ) {
                    $layer = (array) $layer;
                }
                SPSLIDER_Database::create_layer( [
                    'slide_id'   => $new_slide_id,
                    'type'       => $layer['type'] ?? 'text',
                    'sort_order' => $layer['sort_order'] ?? 0,
                    'settings'   => $layer['settings'] ?? [],
                ] );
            }
        }

        return $new_id;
    }

    /**
     * Import multiple sliders (bulk restore).
     */
    private static function import_bulk( $sliders_data ) {
        $results = [];
        foreach ( $sliders_data as $slider_data ) {
            $result = self::import_single( $slider_data );
            $results[] = is_wp_error( $result ) ? $result->get_error_message() : $result;
        }
        return $results;
    }

    /**
     * Import from Smart Slider 3 JSON export (basic compatibility).
     */
    public static function import_smartslider3( $file_path ) {
        $json = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        if ( ! $json ) {
            return new WP_Error( 'read_error', __( 'Could not read file.', 'syntekpro-slider' ) );
        }

        $data = json_decode( $json, true );
        if ( ! $data ) {
            return new WP_Error( 'parse_error', __( 'Invalid JSON.', 'syntekpro-slider' ) );
        }

        // Map SS3 structure to our format
        $ss3_slider   = $data['slider'] ?? $data;
        $ss3_settings = $ss3_slider['params'] ?? [];
        $ss3_slides   = $ss3_slider['slides'] ?? [];

        $mapped = [
            'name'     => sanitize_text_field( $ss3_slider['title'] ?? 'SS3 Import' ),
            'settings' => [
                'width'       => (int) ( $ss3_settings['width'] ?? 1200 ),
                'height'      => (int) ( $ss3_settings['height'] ?? 500 ),
                'autoplay'    => ! empty( $ss3_settings['autoplay'] ),
                'transition'  => sanitize_key( $ss3_settings['animation'] ?? 'slide' ),
            ],
            'slides'   => [],
        ];

        foreach ( $ss3_slides as $ss3_slide ) {
            $slide = [
                'title'    => sanitize_text_field( $ss3_slide['title'] ?? 'Slide' ),
                'settings' => [
                    'bg_image' => esc_url_raw( $ss3_slide['backgroundImage']['image'] ?? '' ),
                    'bg_color' => sanitize_hex_color( $ss3_slide['backgroundColor'] ?? '' ) ?: '#1a1a2e',
                ],
                'layers'   => [],
            ];

            foreach ( ( $ss3_slide['layers'] ?? [] ) as $ss3_layer ) {
                $type = 'text';
                if ( ! empty( $ss3_layer['type'] ) ) {
                    $t = strtolower( $ss3_layer['type'] );
                    if ( in_array( $t, [ 'image', 'button', 'video', 'shape' ], true ) ) $type = $t;
                }

                $slide['layers'][] = [
                    'type'     => $type,
                    'settings' => [
                        'content' => wp_kses_post( $ss3_layer['subtitle'] ?? $ss3_layer['title'] ?? '' ),
                        'x'       => (int) ( $ss3_layer['desktopportraitx'] ?? 60 ),
                        'y'       => (int) ( $ss3_layer['desktopportraity'] ?? 60 ),
                    ],
                ];
            }

            $mapped['slides'][] = $slide;
        }

        return self::import_single( $mapped );
    }
}
