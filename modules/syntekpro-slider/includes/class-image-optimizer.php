<?php
defined( 'ABSPATH' ) || exit;

/**
 * Image optimization: WebP conversion and on-the-fly optimisation helpers.
 */
class SPSLIDER_Image_Optimizer {

    /**
     * Get or generate a WebP version of an attachment.
     * Returns the WebP URL if available/generated, otherwise the original URL.
     */
    public static function get_webp_url( $attachment_id, $size = 'large' ) {
        if ( ! $attachment_id ) return '';

        $settings = get_option( 'spslider_global_settings', [] );
        $generate = ! empty( $settings['generate_webp'] );

        $original_url  = wp_get_attachment_image_url( $attachment_id, $size );
        if ( ! $original_url ) return '';

        if ( ! $generate ) return $original_url;

        // Check if WebP already exists as attachment meta
        $webp_url = get_post_meta( $attachment_id, '_spslider_webp_' . $size, true );
        if ( $webp_url ) return $webp_url;

        // Attempt to generate WebP
        $generated = self::generate_webp( $attachment_id, $size );
        return $generated ?: $original_url;
    }

    /**
     * Get or generate an AVIF version of an attachment.
     * Returns the AVIF URL if available/generated, otherwise falls back to WebP then original.
     */
    public static function get_avif_url( $attachment_id, $size = 'large' ) {
        if ( ! $attachment_id ) return '';

        $settings = get_option( 'spslider_global_settings', [] );
        if ( empty( $settings['generate_avif'] ) ) return self::get_webp_url( $attachment_id, $size );

        $original_url = wp_get_attachment_image_url( $attachment_id, $size );
        if ( ! $original_url ) return '';

        $avif_url = get_post_meta( $attachment_id, '_spslider_avif_' . $size, true );
        if ( $avif_url ) return $avif_url;

        $generated = self::generate_avif( $attachment_id, $size );
        return $generated ?: self::get_webp_url( $attachment_id, $size );
    }

    /**
     * Generate AVIF variant for an attachment.
     */
    private static function generate_avif( $attachment_id, $size ) {
        if ( ! function_exists( 'imageavif' ) ) return null;

        $meta     = wp_get_attachment_metadata( $attachment_id );
        $upload   = wp_upload_dir();
        $base_dir = $upload['basedir'];
        $file_path = $base_dir . '/' . ( $meta['file'] ?? '' );
        if ( ! file_exists( $file_path ) ) return null;

        $ext  = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
        $avif = preg_replace( '/\.' . preg_quote( $ext, '/' ) . '$/', '.avif', $file_path );

        if ( file_exists( $avif ) ) {
            $avif_url = str_replace( $base_dir, $upload['baseurl'], $avif );
            update_post_meta( $attachment_id, '_spslider_avif_' . $size, $avif_url );
            return $avif_url;
        }

        $image = self::create_image_resource( $file_path, $ext );
        if ( ! $image ) return null;

        imageavif( $image, $avif, 50 ); // quality 50 (AVIF compresses well)
        imagedestroy( $image );

        if ( ! file_exists( $avif ) ) return null;

        $avif_url = str_replace( $base_dir, $upload['baseurl'], $avif );
        update_post_meta( $attachment_id, '_spslider_avif_' . $size, $avif_url );
        return $avif_url;
    }

    /**
     * Generate WebP for a given attachment and size.
     */
    private static function generate_webp( $attachment_id, $size ) {
        if ( ! function_exists( 'imagewebp' ) ) return null;

        $meta      = wp_get_attachment_metadata( $attachment_id );
        $upload    = wp_upload_dir();
        $base_dir  = $upload['basedir'];

        $file_path = $base_dir . '/' . ( $meta['file'] ?? '' );
        if ( ! file_exists( $file_path ) ) return null;

        $ext  = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
        $webp = preg_replace( '/\.' . preg_quote( $ext, '/' ) . '$/', '.webp', $file_path );

        if ( file_exists( $webp ) ) {
            $webp_url = str_replace( $base_dir, $upload['baseurl'], $webp );
            update_post_meta( $attachment_id, '_spslider_webp_' . $size, $webp_url );
            return $webp_url;
        }

        $image = self::create_image_resource( $file_path, $ext );
        if ( ! $image ) return null;

        imagewebp( $image, $webp, 82 ); // quality 82
        imagedestroy( $image );

        if ( ! file_exists( $webp ) ) return null;

        $webp_url = str_replace( $base_dir, $upload['baseurl'], $webp );
        update_post_meta( $attachment_id, '_spslider_webp_' . $size, $webp_url );
        return $webp_url;
    }

    /**
     * Create GD image resource from file.
     */
    private static function create_image_resource( $file_path, $ext ) {
        switch ( $ext ) {
            case 'jpeg':
            case 'jpg':  return imagecreatefromjpeg( $file_path );
            case 'png':  return imagecreatefrompng( $file_path );
            case 'gif':  return imagecreatefromgif( $file_path );
            default:     return null;
        }
    }

    /**
     * Build a srcset string for responsive images.
     */
    public static function get_srcset( $attachment_id ) {
        if ( ! $attachment_id ) return '';
        $srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
        return $srcset ?: '';
    }

    /**
     * Enqueue per-slider optimised CSS (inline critical styles).
     * Extracts only the CSS rules used by this specific slider and inlines them.
     */
    public static function enqueue_slider_styles( $slider_id, $settings ) {
        $global = get_option( 'spslider_global_settings', [] );
        if ( empty( $global['optimize_assets'] ) ) return;

        $data = SPSLIDER_Database::load_full_slider( $slider_id );
        if ( ! $data ) return;

        add_action( 'wp_head', function () use ( $slider_id, $data ) {
            $css = self::extract_critical_css( $slider_id, $data );
            if ( $css ) {
                echo "\n<style id=\"spslider-critical-{$slider_id}\">{$css}</style>\n";
            }
        }, 7 );
    }

    /**
     * Build critical CSS for a specific slider based on its actual settings.
     */
    private static function extract_critical_css( $slider_id, $data ) {
        $s     = $data['settings'];
        $sid   = (int) $slider_id;
        $rules = [];

        // Container dimensions
        $w = $s['full_width'] ? '100%' : (int) $s['width'] . 'px';
        $h = $s['full_screen'] ? '100vh' : (int) $s['height'] . 'px';
        $rules[] = "#spslider-{$sid}{height:{$h}}";
        $rules[] = "#spslider-{$sid} .spslider-wrapper{max-width:{$w}}";

        // Transition speed
        $speed = (int) ( $s['speed'] ?? 700 );
        $rules[] = "#spslider-{$sid}{--sp-speed:{$speed}ms;--sp-easing:" . esc_attr( $s['easing'] ?? 'ease-in-out' ) . "}";

        // Slide backgrounds (first slide eager, others lazy)
        foreach ( $data['slides'] as $i => $slide ) {
            $ss = $slide->settings ?? (array) $slide['settings'] ?? [];
            if ( ! empty( $ss['bg_image'] ) && $i === 0 ) {
                $bg = esc_url( $ss['bg_image'] );
                $rules[] = "#spslider-{$sid} .spslider-slide[data-index=\"0\"]{background-image:url('{$bg}')}";
            }
            if ( ! empty( $ss['bg_color'] ) && $i === 0 ) {
                $rules[] = "#spslider-{$sid} .spslider-slide[data-index=\"0\"]{background:" . esc_attr( $ss['bg_color'] ) . "}";
            }
        }

        // Hide arrows/dots if disabled
        if ( empty( $s['arrows'] ) ) $rules[] = "#spslider-{$sid} .spslider-arrow{display:none}";
        if ( empty( $s['dots'] ) )   $rules[] = "#spslider-{$sid} .spslider-dots{display:none}";

        return implode( '', $rules );
    }

    /**
     * Returns the browser-native <picture> tag markup for a layer image.
     * Includes AVIF and WebP sources when available.
     */
    public static function picture_tag( $attachment_id, $size = 'large', $alt = '', $css_class = '' ) {
        $webp = self::get_webp_url( $attachment_id, $size );
        $avif = self::get_avif_url( $attachment_id, $size );
        $orig = wp_get_attachment_image_url( $attachment_id, $size );
        if ( ! $orig ) return '';

        $srcset  = self::get_srcset( $attachment_id );
        $alt_esc = esc_attr( $alt );
        $cls_esc = esc_attr( $css_class );
        $src_esc = esc_url( $orig );
        $set_esc = esc_attr( $srcset );

        $avif_source = '';
        if ( $avif && $avif !== $orig && $avif !== $webp ) {
            $avif_esc    = esc_url( $avif );
            $avif_source = "<source srcset=\"{$avif_esc}\" type=\"image/avif\">";
        }

        $webp_source = '';
        if ( $webp && $webp !== $orig ) {
            $webp_esc    = esc_url( $webp );
            $webp_source = "<source srcset=\"{$webp_esc}\" type=\"image/webp\">";
        }

        return "<picture class=\"{$cls_esc}\">{$avif_source}{$webp_source}<img src=\"{$src_esc}\" srcset=\"{$set_esc}\" alt=\"{$alt_esc}\" loading=\"lazy\" decoding=\"async\"></picture>";
    }
}
