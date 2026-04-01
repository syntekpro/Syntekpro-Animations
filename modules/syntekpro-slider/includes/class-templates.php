<?php
defined( 'ABSPATH' ) || exit;

/**
 * Starter template library.
 * Provides pre-built slide configurations for the Template Library modal.
 */
class SPSLIDER_Templates {

    /**
     * Returns all template categories with their templates.
     */
    public static function get_all() {
        return [
            'hero'        => self::hero_templates(),
            'testimonial' => self::testimonial_templates(),
            'product'     => self::product_templates(),
            'portfolio'   => self::portfolio_templates(),
            'fullscreen'  => self::fullscreen_templates(),
        ];
    }

    public static function get_by_category( $category ) {
        $all = self::get_all();
        return $all[ sanitize_key( $category ) ] ?? [];
    }

    /**
     * Import a template as a new slide (with layers) into a slider.
     */
    public static function import( $slider_id, $template_id ) {
        $all = [];
        foreach ( self::get_all() as $cats ) {
            foreach ( $cats as $tpl ) {
                $all[ $tpl['id'] ] = $tpl;
            }
        }

        if ( ! isset( $all[ $template_id ] ) ) return false;
        $tpl = $all[ $template_id ];

        $slide_id = SPSLIDER_Database::create_slide( [
            'slider_id' => (int) $slider_id,
            'title'     => $tpl['name'],
            'settings'  => $tpl['slide_settings'],
        ] );
        if ( ! $slide_id ) return false;

        foreach ( ( $tpl['layers'] ?? [] ) as $layer ) {
            SPSLIDER_Database::create_layer( array_merge( $layer, [ 'slide_id' => $slide_id ] ) );
        }

        return $slide_id;
    }

    // ── Hero Templates ────────────────────────────────────────────────────────

    private static function hero_templates() {
        return [
            self::agency_landing_template(),
            [
                'id'       => 'hero-dark',
                'name'     => 'Hero Dark',
                'category' => 'hero',
                'thumb'    => SPSLIDER_URL . 'assets/img/tpl-hero-dark.jpg',
                'slide_settings' => [
                    'bg_color'           => '#0d0d1a',
                    'bg_overlay'         => '#000000',
                    'bg_overlay_opacity' => 0.5,
                ],
                'layers' => [
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content'   => 'Welcome to the Future',
                            'x' => 80, 'y' => 140, 'width' => 700, 'height' => 80,
                            'font_size' => 52, 'font_weight' => '800', 'color' => '#ffffff',
                            'tag'       => 'h1',
                            'animation_in' => [ 'effect' => 'slide-left', 'delay' => 200, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 60 ],
                        ] ),
                    ],
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content'   => 'Create stunning, high-performance sliders effortlessly.',
                            'x' => 80, 'y' => 240, 'width' => 600, 'height' => 60,
                            'font_size' => 20, 'color' => '#cccccc',
                            'animation_in' => [ 'effect' => 'slide-left', 'delay' => 400, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 60 ],
                        ] ),
                    ],
                    [
                        'type' => 'button',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                            'x' => 80, 'y' => 330,
                            'animation_in' => [ 'effect' => 'fade', 'delay' => 600, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 0 ],
                        ] ),
                    ],
                ],
            ],
            [
                'id'             => 'hero-gradient',
                'name'           => 'Hero Gradient',
                'category'       => 'hero',
                'thumb'          => SPSLIDER_URL . 'assets/img/tpl-hero-gradient.jpg',
                'slide_settings' => [
                    'bg_color' => 'linear-gradient(135deg,#667eea 0%,#764ba2 100%)',
                ],
                'layers' => [
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content' => 'Grow Your Business', 'x' => 100, 'y' => 150,
                            'width' => 600, 'font_size' => 48, 'font_weight' => '800', 'color' => '#fff',
                            'animation_in' => [ 'effect' => 'zoom', 'delay' => 200, 'duration' => 700, 'easing' => 'ease-out', 'distance' => 0 ],
                        ] ),
                    ],
                    [
                        'type' => 'button',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                            'x' => 100, 'y' => 320, 'bg_color' => '#ffffff', 'text_color' => '#764ba2',
                            'label' => 'Start Free Trial',
                            'animation_in' => [ 'effect' => 'fade', 'delay' => 500, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 0 ],
                        ] ),
                    ],
                ],
            ],
        ];
    }

    // ── Testimonial Templates ─────────────────────────────────────────────────

    private static function testimonial_templates() {
        return [
            self::client_review_template(),
            [
                'id'       => 'testimonial-clean',
                'name'     => 'Testimonial Clean',
                'category' => 'testimonial',
                'thumb'    => SPSLIDER_URL . 'assets/img/tpl-testimonial.jpg',
                'slide_settings' => [ 'bg_color' => '#f9f9f9' ],
                'layers' => [
                    [
                        'type' => 'shape',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                            'x' => 300, 'y' => 60, 'width' => 600, 'height' => 340,
                            'fill' => '#ffffff', 'border_radius' => 16,
                        ] ),
                    ],
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content'   => '"SyntekPro Slider transformed our landing pages. Conversions went up 40%!"',
                            'x' => 340, 'y' => 120, 'width' => 520, 'height' => 140,
                            'font_size' => 18, 'color' => '#333', 'text_align' => 'center', 'line_height' => 1.7,
                            'animation_in' => [ 'effect' => 'fade', 'delay' => 200, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 0 ],
                        ] ),
                    ],
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content' => '— Jane Doe, CEO at Acme Corp', 'x' => 340, 'y' => 300,
                            'width'   => 520, 'font_size' => 14, 'color' => '#888', 'text_align' => 'center',
                        ] ),
                    ],
                ],
            ],
        ];
    }

    // ── Product Templates ─────────────────────────────────────────────────────

    private static function product_templates() {
        return [
            self::fashion_store_template(),
            [
                'id'       => 'product-showcase',
                'name'     => 'Product Showcase',
                'category' => 'product',
                'thumb'    => SPSLIDER_URL . 'assets/img/tpl-product.jpg',
                'slide_settings' => [ 'bg_color' => '#111827' ],
                'layers' => [
                    [
                        'type' => 'image',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'image' ), [
                            'x' => 600, 'y' => 60, 'width' => 480, 'height' => 380,
                            'src' => '',
                            'animation_in' => [ 'effect' => 'slide-right', 'delay' => 200, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 80 ],
                        ] ),
                    ],
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content' => 'New Arrival', 'x' => 80, 'y' => 120, 'width' => 400,
                            'font_size' => 14, 'font_weight' => '600', 'color' => '#e94560', 'letter_spacing' => 3,
                        ] ),
                    ],
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content' => 'Premium Product Name', 'x' => 80, 'y' => 160, 'width' => 480,
                            'font_size' => 42, 'font_weight' => '800', 'color' => '#ffffff',
                            'animation_in' => [ 'effect' => 'slide-left', 'delay' => 300, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 60 ],
                        ] ),
                    ],
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content' => '$299.00', 'x' => 80, 'y' => 280, 'width' => 200,
                            'font_size' => 32, 'font_weight' => '700', 'color' => '#e94560',
                        ] ),
                    ],
                    [
                        'type' => 'button',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                            'x' => 80, 'y' => 355, 'label' => 'Shop Now',
                            'bg_color' => '#e94560', 'hover_bg' => '#c73652',
                            'animation_in' => [ 'effect' => 'fade', 'delay' => 600, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 0 ],
                        ] ),
                    ],
                ],
            ],
        ];
    }

    // ── Portfolio Templates ───────────────────────────────────────────────────

    private static function portfolio_templates() {
        return [
            [
                'id'       => 'portfolio-overlay',
                'name'     => 'Portfolio Overlay',
                'category' => 'portfolio',
                'thumb'    => SPSLIDER_URL . 'assets/img/tpl-portfolio.jpg',
                'slide_settings' => [
                    'bg_color' => '#000000',
                    'bg_overlay'         => '#000000',
                    'bg_overlay_opacity' => 0.55,
                ],
                'layers' => [
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content'   => 'Portfolio Project',
                            'x' => 80, 'y' => 180, 'width' => 700,
                            'font_size' => 46, 'font_weight' => '700', 'color' => '#ffffff',
                            'animation_in' => [ 'effect' => 'slide-up', 'delay' => 200, 'duration' => 700, 'easing' => 'ease-out', 'distance' => 40 ],
                        ] ),
                    ],
                    [
                        'type' => 'button',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                            'x' => 80, 'y' => 290, 'label' => 'View Project',
                            'bg_color' => 'transparent', 'text_color' => '#ffffff',
                            'border' => '2px solid #ffffff',
                        ] ),
                    ],
                ],
            ],
        ];
    }

    // ── Fullscreen Templates ──────────────────────────────────────────────────

    private static function fullscreen_templates() {
        return [
            [
                'id'       => 'fullscreen-split',
                'name'     => 'Fullscreen Split',
                'category' => 'fullscreen',
                'thumb'    => SPSLIDER_URL . 'assets/img/tpl-fullscreen.jpg',
                'slide_settings' => [ 'bg_color' => '#16213e' ],
                'layers' => [
                    [
                        'type' => 'shape',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                            'x' => 0, 'y' => 0, 'width' => 580, 'height' => 600,
                            'fill' => 'rgba(233,69,96,0.12)', 'border_radius' => 0,
                        ] ),
                    ],
                    [
                        'type' => 'text',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                            'content'   => 'Bold Fullscreen Experience',
                            'x' => 60, 'y' => 160, 'width' => 500,
                            'font_size' => 50, 'font_weight' => '800', 'color' => '#ffffff',
                            'animation_in' => [ 'effect' => 'slide-left', 'delay' => 200, 'duration' => 900, 'easing' => 'ease-out', 'distance' => 80 ],
                        ] ),
                    ],
                    [
                        'type' => 'button',
                        'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                            'x' => 60, 'y' => 360, 'label' => 'Explore Now',
                            'bg_color' => '#e94560',
                        ] ),
                    ],
                ],
            ],
        ];
    }

    // ── Agency Landing (Hero) ─────────────────────────────────────────────────

    private static function agency_landing_template() {
        return [
            'id'       => 'hero-agency-landing',
            'name'     => 'Agency Landing',
            'category' => 'hero',
            'thumb'    => SPSLIDER_URL . 'assets/img/tpl-hero-agency.svg',
            'slide_settings' => [
                'bg_color'            => 'linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%)',
                'bg_overlay'          => '#000000',
                'bg_overlay_opacity'  => 0.25,
                'ken_burns'           => false,
            ],
            'layers' => [
                // ── Decorative accent shape (top-right glow) ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 820, 'y' => -60, 'width' => 480, 'height' => 480,
                        'fill'          => 'radial-gradient(circle, rgba(124,58,237,0.35) 0%, transparent 70%)',
                        'border_radius' => 9999,
                        'opacity'       => 0.8,
                        'z_index'       => 1,
                        'name'          => 'Accent Glow',
                        'animation_in'  => [ 'effect' => 'zoom', 'delay' => 0, 'duration' => 1200, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Decorative line accent ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 80, 'y' => 115, 'width' => 50, 'height' => 4,
                        'fill'          => '#7c3aed',
                        'border_radius' => 2,
                        'z_index'       => 5,
                        'name'          => 'Accent Line',
                        'animation_in'  => [ 'effect' => 'slide-left', 'delay' => 100, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 30 ],
                    ] ),
                ],
                // ── Badge / label ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'        => 'AWARD-WINNING DIGITAL AGENCY',
                        'x' => 140, 'y' => 100, 'width' => 350, 'height' => 30,
                        'font_size'      => 12,
                        'font_weight'    => '700',
                        'color'          => '#a78bfa',
                        'letter_spacing' => 4,
                        'tag'            => 'span',
                        'z_index'        => 5,
                        'name'           => 'Badge',
                        'animation_in'   => [ 'effect' => 'slide-left', 'delay' => 150, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 30 ],
                    ] ),
                ],
                // ── Main headline ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => 'We Craft Digital Experiences That Matter',
                        'x' => 80, 'y' => 145, 'width' => 660, 'height' => 130,
                        'font_size'    => 48,
                        'font_weight'  => '800',
                        'color'        => '#ffffff',
                        'line_height'  => 1.15,
                        'tag'          => 'h1',
                        'z_index'      => 5,
                        'name'         => 'Headline',
                        'animation_in' => [ 'effect' => 'slide-up', 'delay' => 300, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 50 ],
                    ] ),
                ],
                // ── Subtitle ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => 'From brand strategy to full-stack development — we build products that users love and businesses grow with.',
                        'x' => 80, 'y' => 290, 'width' => 560, 'height' => 60,
                        'font_size'    => 16,
                        'font_weight'  => '400',
                        'color'        => '#c4b5fd',
                        'line_height'  => 1.65,
                        'tag'          => 'p',
                        'z_index'      => 5,
                        'name'         => 'Subtitle',
                        'animation_in' => [ 'effect' => 'slide-up', 'delay' => 500, 'duration' => 700, 'easing' => 'ease-out', 'distance' => 40 ],
                    ] ),
                ],
                // ── Primary CTA ──
                [
                    'type'     => 'button',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                        'label'         => 'Start a Project',
                        'url'           => '#contact',
                        'x' => 80, 'y' => 385, 'width' => 190, 'height' => 52,
                        'bg_color'      => '#7c3aed',
                        'text_color'    => '#ffffff',
                        'hover_bg'      => '#6d28d9',
                        'border_radius' => 8,
                        'font_size'     => 15,
                        'font_weight'   => '600',
                        'padding'       => '14px 28px',
                        'border'        => 'none',
                        'z_index'       => 5,
                        'name'          => 'Primary CTA',
                        'animation_in'  => [ 'effect' => 'slide-up', 'delay' => 700, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 30 ],
                    ] ),
                ],
                // ── Secondary CTA (outline) ──
                [
                    'type'     => 'button',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                        'label'         => 'View Our Work',
                        'url'           => '#portfolio',
                        'x' => 290, 'y' => 385, 'width' => 190, 'height' => 52,
                        'bg_color'      => 'transparent',
                        'text_color'    => '#c4b5fd',
                        'hover_bg'      => 'rgba(124,58,237,0.15)',
                        'border_radius' => 8,
                        'font_size'     => 15,
                        'font_weight'   => '600',
                        'padding'       => '14px 28px',
                        'border'        => '2px solid rgba(167,139,250,0.4)',
                        'z_index'       => 5,
                        'name'          => 'Secondary CTA',
                        'animation_in'  => [ 'effect' => 'slide-up', 'delay' => 800, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 30 ],
                    ] ),
                ],
                // ── Floating stat badge (right side) ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 900, 'y' => 320, 'width' => 170, 'height' => 80,
                        'fill'          => 'rgba(255,255,255,0.08)',
                        'border_radius' => 12,
                        'z_index'       => 4,
                        'name'          => 'Stat Card BG',
                        'animation_in'  => [ 'effect' => 'fade', 'delay' => 900, 'duration' => 700, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => '250+ Projects Delivered',
                        'x' => 916, 'y' => 340, 'width' => 140, 'height' => 40,
                        'font_size'    => 13,
                        'font_weight'  => '500',
                        'color'        => 'rgba(255,255,255,0.7)',
                        'text_align'   => 'center',
                        'line_height'  => 1.4,
                        'z_index'      => 5,
                        'name'         => 'Stat Text',
                        'animation_in' => [ 'effect' => 'fade', 'delay' => 1000, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
            ],
        ];
    }

    // ── Fashion Store (Product) ───────────────────────────────────────────────

    private static function fashion_store_template() {
        return [
            'id'       => 'product-fashion-store',
            'name'     => 'Fashion Store',
            'category' => 'product',
            'thumb'    => SPSLIDER_URL . 'assets/img/tpl-product-fashion.svg',
            'slide_settings' => [
                'bg_color'            => '#0a0a0a',
                'bg_overlay'          => '#000000',
                'bg_overlay_opacity'  => 0.3,
            ],
            'layers' => [
                // ── Right-side decorative panel ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 660, 'y' => 0, 'width' => 540, 'height' => 500,
                        'fill'          => 'linear-gradient(180deg, rgba(217,119,87,0.12) 0%, rgba(217,119,87,0.03) 100%)',
                        'border_radius' => 0,
                        'z_index'       => 1,
                        'name'          => 'Right Panel',
                        'animation_in'  => [ 'effect' => 'fade', 'delay' => 0, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Product image placeholder ──
                [
                    'type'     => 'image',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'image' ), [
                        'x' => 720, 'y' => 40, 'width' => 420, 'height' => 420,
                        'src'           => '',
                        'object_fit'    => 'contain',
                        'border_radius' => 0,
                        'z_index'       => 3,
                        'name'          => 'Product Image',
                        'animation_in'  => [ 'effect' => 'zoom', 'delay' => 200, 'duration' => 900, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── "SALE" badge ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 80, 'y' => 52, 'width' => 70, 'height' => 28,
                        'fill'          => '#d97757',
                        'border_radius' => 4,
                        'z_index'       => 6,
                        'name'          => 'Sale Badge BG',
                        'animation_in'  => [ 'effect' => 'zoom', 'delay' => 100, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'        => 'SALE',
                        'x' => 85, 'y' => 53, 'width' => 60, 'height' => 26,
                        'font_size'      => 11,
                        'font_weight'    => '800',
                        'color'          => '#ffffff',
                        'letter_spacing' => 2,
                        'text_align'     => 'center',
                        'tag'            => 'span',
                        'z_index'        => 7,
                        'name'           => 'Sale Badge Text',
                        'animation_in'   => [ 'effect' => 'zoom', 'delay' => 100, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Category label ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'        => 'SPRING COLLECTION 2026',
                        'x' => 80, 'y' => 105, 'width' => 300, 'height' => 24,
                        'font_size'      => 11,
                        'font_weight'    => '600',
                        'color'          => '#d97757',
                        'letter_spacing' => 3,
                        'tag'            => 'span',
                        'z_index'        => 5,
                        'name'           => 'Category',
                        'animation_in'   => [ 'effect' => 'slide-left', 'delay' => 200, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 30 ],
                    ] ),
                ],
                // ── Product title ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => 'Italian Leather Crossbody Bag',
                        'x' => 80, 'y' => 140, 'width' => 520, 'height' => 90,
                        'font_size'    => 40,
                        'font_weight'  => '800',
                        'color'        => '#ffffff',
                        'line_height'  => 1.15,
                        'tag'          => 'h2',
                        'z_index'      => 5,
                        'name'         => 'Product Title',
                        'animation_in' => [ 'effect' => 'slide-up', 'delay' => 350, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 50 ],
                    ] ),
                ],
                // ── Product description ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => 'Hand-stitched full-grain leather with brass hardware. Adjustable strap, lined interior with dual compartments.',
                        'x' => 80, 'y' => 248, 'width' => 480, 'height' => 50,
                        'font_size'    => 14,
                        'font_weight'  => '400',
                        'color'        => 'rgba(255,255,255,0.55)',
                        'line_height'  => 1.6,
                        'tag'          => 'p',
                        'z_index'      => 5,
                        'name'         => 'Description',
                        'animation_in' => [ 'effect' => 'slide-up', 'delay' => 500, 'duration' => 700, 'easing' => 'ease-out', 'distance' => 30 ],
                    ] ),
                ],
                // ── Original price (strikethrough effect via text) ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => '$489.00',
                        'x' => 80, 'y' => 330, 'width' => 100, 'height' => 30,
                        'font_size'    => 16,
                        'font_weight'  => '400',
                        'color'        => 'rgba(255,255,255,0.35)',
                        'tag'          => 'span',
                        'z_index'      => 5,
                        'name'         => 'Original Price',
                        'style'        => [ 'text-decoration' => 'line-through' ],
                        'animation_in' => [ 'effect' => 'fade', 'delay' => 650, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Sale price ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => '$349.00',
                        'x' => 190, 'y' => 320, 'width' => 140, 'height' => 45,
                        'font_size'    => 34,
                        'font_weight'  => '800',
                        'color'        => '#d97757',
                        'tag'          => 'span',
                        'z_index'      => 5,
                        'name'         => 'Sale Price',
                        'animation_in' => [ 'effect' => 'fade', 'delay' => 650, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Shop Now CTA ──
                [
                    'type'     => 'button',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                        'label'         => 'Shop Now',
                        'url'           => '#',
                        'x' => 80, 'y' => 395, 'width' => 170, 'height' => 52,
                        'bg_color'      => '#d97757',
                        'text_color'    => '#ffffff',
                        'hover_bg'      => '#c2684a',
                        'border_radius' => 6,
                        'font_size'     => 15,
                        'font_weight'   => '700',
                        'padding'       => '14px 32px',
                        'border'        => 'none',
                        'z_index'       => 5,
                        'name'          => 'Shop CTA',
                        'animation_in'  => [ 'effect' => 'slide-up', 'delay' => 800, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 25 ],
                    ] ),
                ],
                // ── Wishlist button (outline) ──
                [
                    'type'     => 'button',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'button' ), [
                        'label'         => '♥  Wishlist',
                        'url'           => '#',
                        'x' => 270, 'y' => 395, 'width' => 150, 'height' => 52,
                        'bg_color'      => 'transparent',
                        'text_color'    => 'rgba(255,255,255,0.6)',
                        'hover_bg'      => 'rgba(255,255,255,0.06)',
                        'border_radius' => 6,
                        'font_size'     => 14,
                        'font_weight'   => '500',
                        'padding'       => '14px 24px',
                        'border'        => '1px solid rgba(255,255,255,0.15)',
                        'z_index'       => 5,
                        'name'          => 'Wishlist CTA',
                        'animation_in'  => [ 'effect' => 'slide-up', 'delay' => 900, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 25 ],
                    ] ),
                ],
            ],
        ];
    }

    // ── Client Review (Testimonial) ───────────────────────────────────────────

    private static function client_review_template() {
        return [
            'id'       => 'testimonial-client-review',
            'name'     => 'Client Review',
            'category' => 'testimonial',
            'thumb'    => SPSLIDER_URL . 'assets/img/tpl-testimonial-review.svg',
            'slide_settings' => [
                'bg_color' => 'linear-gradient(160deg, #fdfbfb 0%, #ebedee 100%)',
            ],
            'layers' => [
                // ── Card background ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 200, 'y' => 40, 'width' => 800, 'height' => 420,
                        'fill'          => '#ffffff',
                        'border_radius' => 20,
                        'z_index'       => 2,
                        'name'          => 'Card Background',
                        'style'         => [ 'box-shadow' => '0 20px 60px rgba(0,0,0,0.08)' ],
                        'animation_in'  => [ 'effect' => 'zoom', 'delay' => 0, 'duration' => 700, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Left accent stripe ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 200, 'y' => 40, 'width' => 6, 'height' => 420,
                        'fill'          => 'linear-gradient(180deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%)',
                        'border_radius' => 20,
                        'z_index'       => 3,
                        'name'          => 'Accent Stripe',
                        'animation_in'  => [ 'effect' => 'slide-up', 'delay' => 200, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 60 ],
                    ] ),
                ],
                // ── Large quotation mark ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => '"',
                        'x' => 260, 'y' => 50, 'width' => 100, 'height' => 100,
                        'font_size'    => 120,
                        'font_weight'  => '800',
                        'color'        => 'rgba(99,102,241,0.12)',
                        'line_height'  => 1,
                        'tag'          => 'span',
                        'z_index'      => 3,
                        'name'         => 'Quote Mark',
                        'animation_in' => [ 'effect' => 'fade', 'delay' => 100, 'duration' => 600, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Star rating ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => '★★★★★',
                        'x' => 260, 'y' => 120, 'width' => 150, 'height' => 30,
                        'font_size'    => 20,
                        'color'        => '#f59e0b',
                        'z_index'      => 4,
                        'name'         => 'Stars',
                        'animation_in' => [ 'effect' => 'fade', 'delay' => 250, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Testimonial quote ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => 'SyntekPro Slider completely changed our website. The animations are buttery smooth, the builder is intuitive, and our bounce rate dropped by 35%. This is hands-down the best slider plugin we\'ve ever used.',
                        'x' => 260, 'y' => 165, 'width' => 690, 'height' => 100,
                        'font_size'    => 18,
                        'font_weight'  => '400',
                        'color'        => '#374151',
                        'line_height'  => 1.75,
                        'tag'          => 'p',
                        'z_index'      => 4,
                        'name'         => 'Quote Text',
                        'animation_in' => [ 'effect' => 'slide-up', 'delay' => 350, 'duration' => 800, 'easing' => 'ease-out', 'distance' => 40 ],
                    ] ),
                ],
                // ── Divider line ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 260, 'y' => 310, 'width' => 690, 'height' => 1,
                        'fill'          => '#e5e7eb',
                        'border_radius' => 0,
                        'z_index'       => 4,
                        'name'          => 'Divider',
                        'animation_in'  => [ 'effect' => 'fade', 'delay' => 500, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Avatar placeholder (circle) ──
                [
                    'type'     => 'shape',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'shape' ), [
                        'x' => 260, 'y' => 335, 'width' => 56, 'height' => 56,
                        'fill'          => 'linear-gradient(135deg, #6366f1 0%, #a78bfa 100%)',
                        'border_radius' => 9999,
                        'z_index'       => 4,
                        'name'          => 'Avatar Circle',
                        'animation_in'  => [ 'effect' => 'zoom', 'delay' => 550, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Avatar initials ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => 'SM',
                        'x' => 260, 'y' => 347, 'width' => 56, 'height' => 30,
                        'font_size'    => 18,
                        'font_weight'  => '700',
                        'color'        => '#ffffff',
                        'text_align'   => 'center',
                        'tag'          => 'span',
                        'z_index'      => 5,
                        'name'         => 'Avatar Initials',
                        'animation_in' => [ 'effect' => 'zoom', 'delay' => 550, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
                // ── Author name ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => 'Sarah Mitchell',
                        'x' => 330, 'y' => 338, 'width' => 250, 'height' => 26,
                        'font_size'    => 17,
                        'font_weight'  => '700',
                        'color'        => '#111827',
                        'tag'          => 'span',
                        'z_index'      => 4,
                        'name'         => 'Author Name',
                        'animation_in' => [ 'effect' => 'slide-left', 'delay' => 650, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 20 ],
                    ] ),
                ],
                // ── Author role / company ──
                [
                    'type'     => 'text',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'text' ), [
                        'content'      => 'Head of Marketing, TechVenture Inc.',
                        'x' => 330, 'y' => 367, 'width' => 300, 'height' => 22,
                        'font_size'    => 13,
                        'font_weight'  => '400',
                        'color'        => '#9ca3af',
                        'tag'          => 'span',
                        'z_index'      => 4,
                        'name'         => 'Author Role',
                        'animation_in' => [ 'effect' => 'slide-left', 'delay' => 700, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 20 ],
                    ] ),
                ],
                // ── Company logo placeholder ──
                [
                    'type'     => 'image',
                    'settings' => array_merge( SPSLIDER_Database::default_layer_settings( 'image' ), [
                        'x' => 850, 'y' => 345, 'width' => 100, 'height' => 40,
                        'src'           => '',
                        'object_fit'    => 'contain',
                        'opacity'       => 0.4,
                        'border_radius' => 0,
                        'z_index'       => 4,
                        'name'          => 'Company Logo',
                        'animation_in'  => [ 'effect' => 'fade', 'delay' => 800, 'duration' => 500, 'easing' => 'ease-out', 'distance' => 0 ],
                    ] ),
                ],
            ],
        ];
    }
}
