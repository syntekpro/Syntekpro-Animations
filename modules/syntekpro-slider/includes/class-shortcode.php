<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shortcode [syntekpro_slider id="X"] renderer.
 * Outputs semantic, accessible HTML for the frontend slider.
 */
class SPSLIDER_Shortcode {

    public function render( $atts ) {
        $atts = shortcode_atts( [
            'id'    => 0,
            'class' => '',
        ], $atts, 'syntekpro_slider' );

        $slider_id = (int) $atts['id'];
        if ( ! $slider_id ) return '<!-- SyntekPro Slider: missing id -->';

        // A/B testing: resolve which slider variant to show
        if ( class_exists( 'SPSLIDER_AB_Test' ) ) {
            $slider_id = SPSLIDER_AB_Test::resolve_slider( $slider_id );
        }

        $self = $this;
        $render = function () use ( $slider_id, $atts, $self ) {
            return $self->render_slider( $slider_id, $atts );
        };

        // Edge caching
        if ( class_exists( 'SPSLIDER_Cache' ) ) {
            return SPSLIDER_Cache::get_cached_html( $slider_id, $render );
        }

        return $render();
    }

    /**
     * Internal renderer (separated for caching).
     */
    public function render_slider( $slider_id, $atts ) {
        $data = SPSLIDER_Database::load_full_slider( $slider_id );
        if ( ! $data ) return '<!-- SyntekPro Slider: slider not found -->';

        $settings = $data['settings'];

        // Block-level overrides
        if ( ! empty( $atts['overrides'] ) && is_array( $atts['overrides'] ) ) {
            $settings = array_merge( $settings, $atts['overrides'] );
            $data['settings'] = $settings;
        }
        $slides   = $data['slides'];

        // Personalisation: filter slides based on user context
        if ( class_exists( 'SPSLIDER_Personalisation' ) ) {
            $slides = SPSLIDER_Personalisation::filter_slides( $slides, $settings );
        }

        if ( empty( $slides ) ) return '<!-- SyntekPro Slider: no slides -->';

        // Mark page as using a slider (so assets can be selectively enqueued)
        SPSLIDER_Public::mark_slider_used( $slider_id );

        $extra_class = sanitize_html_class( $atts['class'] );
        $config_json = esc_attr( wp_json_encode( $settings ) );

        $width_style  = $settings['full_width']   ? 'width:100%;' : 'max-width:' . (int) $settings['width'] . 'px;';
        $height_style = $settings['full_screen']  ? 'height:100vh;' : 'height:' . (int) $settings['height'] . 'px;';
        $scaling_mode = sanitize_key( $settings['scaling_mode'] ?? 'auto' );

        ob_start();
        ?>
        <div
            class="spslider-wrapper spslider-scale-<?php echo esc_attr( $scaling_mode ); ?> <?php echo esc_attr( $extra_class ); ?>"
            style="<?php echo esc_attr( $width_style ); ?>"
            aria-label="<?php echo esc_attr( $data['name'] ); ?>"
            role="region"
        >
            <div
                id="spslider-<?php echo esc_attr( $slider_id ); ?>"
                class="spslider-container"
                data-slider-id="<?php echo esc_attr( $slider_id ); ?>"
                data-config="<?php echo $config_json; ?>"
                style="<?php echo esc_attr( $height_style ); ?>"
                tabindex="0"
                aria-roledescription="carousel"
                aria-label="<?php echo esc_attr( $data['name'] ); ?>"
            >
                <div class="spslider-track">
                    <?php foreach ( $slides as $index => $slide ) : ?>
                        <?php echo $this->render_slide( $slide, $index, count( $slides ), $settings ); ?>
                    <?php endforeach; ?>
                </div>

                <?php if ( ! empty( $settings['arrows'] ) ) : ?>
                <button class="spslider-arrow spslider-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'syntekpro-slider' ); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button class="spslider-arrow spslider-next" aria-label="<?php esc_attr_e( 'Next slide', 'syntekpro-slider' ); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
                <?php endif; ?>

                <?php if ( ! empty( $settings['dots'] ) ) : ?>
                <div class="spslider-dots" role="tablist" aria-label="<?php esc_attr_e( 'Slide navigation', 'syntekpro-slider' ); ?>">
                    <?php foreach ( $slides as $i => $s ) : ?>
                    <button class="spslider-dot<?php echo $i === 0 ? ' active' : ''; ?>"
                        role="tab"
                        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                        aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'syntekpro-slider' ), $i + 1 ) ); ?>"
                        data-index="<?php echo esc_attr( $i ); ?>"
                    ></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_slide( $slide, $index, $total, $slider_settings ) {
        $s        = $slide->settings;
        $is_first = $index === 0;
        $bg_style = $this->build_bg_style( $s, $is_first );
        $slide_config = esc_attr( wp_json_encode( $s ) );

        ob_start();
        ?>
        <div class="spslider-slide<?php echo $is_first ? ' active' : ''; ?>"
            data-slide-id="<?php echo esc_attr( $slide->id ); ?>"
            data-index="<?php echo esc_attr( $index ); ?>"
            data-config="<?php echo $slide_config; ?>"
            style="<?php echo esc_attr( $bg_style ); ?>"
            role="tabpanel"
            aria-label="<?php echo esc_attr( sprintf( __( 'Slide %1$d of %2$d: %3$s', 'syntekpro-slider' ), $index + 1, $total, $slide->title ) ); ?>"
            aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"
        >
            <?php if ( ! empty( $s['bg_overlay'] ) && $s['bg_overlay_opacity'] > 0 ) : ?>
            <div class="spslider-overlay"
                style="background:<?php echo esc_attr( $s['bg_overlay'] ); ?>;opacity:<?php echo esc_attr( $s['bg_overlay_opacity'] ); ?>;"
                aria-hidden="true"
            ></div>
            <?php endif; ?>

            <?php if ( ! empty( $s['bg_video'] ) ) : ?>
            <video class="spslider-bg-video" autoplay muted loop playsinline aria-hidden="true"
                src="<?php echo esc_url( $s['bg_video'] ); ?>"></video>
            <?php endif; ?>

            <div class="spslider-layers-wrap">
                <?php foreach ( ( $slide->layers ?? [] ) as $layer ) : ?>
                    <?php echo $this->render_layer( $layer, $slider_settings ); ?>
                <?php endforeach; ?>
            </div>

            <?php if ( ! empty( $s['link'] ) ) : ?>
            <a class="spslider-slide-link"
               href="<?php echo esc_url( $s['link'] ); ?>"
               target="<?php echo esc_attr( $s['link_target'] ?? '_self' ); ?>"
               aria-label="<?php echo esc_attr( $slide->title ); ?>"
            ></a>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_layer( $layer, $slider_settings ) {
        $s   = $layer->settings;
        $cfg = esc_attr( wp_json_encode( $s ) );

        $x        = (int) ( $s['x'] ?? 0 );
        $y        = (int) ( $s['y'] ?? 0 );
        $w        = (int) ( $s['width'] ?? 200 );
        $h        = (int) ( $s['height'] ?? 80 );
        $z        = (int) ( $s['z_index'] ?? 10 );
        $opacity  = (float) ( $s['opacity'] ?? 1 );
        $rotation = (float) ( $s['rotation'] ?? 0 );
        $visible  = ! empty( $s['visible'] );

        $style = sprintf(
            'left:%dpx;top:%dpx;width:%dpx;height:%dpx;z-index:%d;opacity:%s;transform:rotate(%sdeg);%s',
            $x, $y, $w, $h, $z,
            esc_attr( $opacity ),
            esc_attr( $rotation ),
            $visible ? '' : 'display:none;'
        );

        // Hover CSS class
        $hover_class = '';
        if ( ! empty( $s['hover']['effect'] ) && $s['hover']['effect'] !== 'none' ) {
            $hover_class = 'spslider-hover-' . sanitize_html_class( $s['hover']['effect'] );
        }

        ob_start();
        ?>
        <div class="spslider-layer spslider-layer-type-<?php echo esc_attr( $layer->type ); ?> <?php echo esc_attr( $hover_class ); ?>"
            data-layer-id="<?php echo esc_attr( $layer->id ); ?>"
            data-layer-config="<?php echo $cfg; ?>"
            data-parallax="<?php echo esc_attr( $s['parallax_depth'] ?? 0 ); ?>"
            style="position:absolute;<?php echo esc_attr( $style ); ?>"
            <?php if ( ! empty( $s['alt'] ) ) echo 'aria-label="' . esc_attr( $s['alt'] ) . '"'; ?>
        >
            <?php echo $this->render_layer_content( $layer->type, $s ); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_layer_content( $type, $s ) {
        switch ( $type ) {
            case 'text':
                $tag     = in_array( $s['tag'] ?? 'div', [ 'h1','h2','h3','h4','h5','h6','p','div','span' ], true ) ? $s['tag'] : 'div';
                $style   = sprintf(
                    'font-size:%dpx;font-weight:%s;font-family:%s;color:%s;text-align:%s;line-height:%s;letter-spacing:%spx;',
                    (int) ($s['font_size'] ?? 16),
                    esc_attr( $s['font_weight'] ?? '400' ),
                    esc_attr( $s['font_family'] ?? 'inherit' ),
                    esc_attr( $s['color'] ?? '#333' ),
                    esc_attr( $s['text_align'] ?? 'left' ),
                    esc_attr( $s['line_height'] ?? 1.5 ),
                    (float) ($s['letter_spacing'] ?? 0)
                );
                return "<{$tag} class=\"spslider-text-layer\" style=\"{$style}\">" . wp_kses_post( $s['content'] ?? '' ) . "</{$tag}>";

            case 'image':
                $src = esc_url( $s['src'] ?? '' );
                $alt = esc_attr( $s['alt'] ?? '' );
                $fit = esc_attr( $s['object_fit'] ?? 'cover' );
                $br  = (int) ( $s['border_radius'] ?? 0 );
                return "<img src=\"{$src}\" alt=\"{$alt}\" class=\"spslider-image-layer\" loading=\"lazy\" decoding=\"async\" style=\"width:100%;height:100%;object-fit:{$fit};border-radius:{$br}px;\">";

            case 'button':
                $url    = esc_url( $s['url'] ?? '#' );
                $target = esc_attr( $s['target'] ?? '_self' );
                $label  = esc_html( $s['label'] ?? 'Click Me' );
                $style  = sprintf(
                    'background:%s;color:%s;border-radius:%dpx;padding:%s;font-size:%dpx;font-weight:%s;border:%s;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;cursor:pointer;width:100%%;height:100%%;box-sizing:border-box;',
                    esc_attr( $s['bg_color'] ?? '#0073aa' ),
                    esc_attr( $s['text_color'] ?? '#ffffff' ),
                    (int)($s['border_radius'] ?? 4),
                    esc_attr( $s['padding'] ?? '12px 24px' ),
                    (int)($s['font_size'] ?? 16),
                    esc_attr( $s['font_weight'] ?? '600' ),
                    esc_attr( $s['border'] ?? 'none' )
                );
                $hover_bg = esc_attr( $s['hover_bg'] ?? '' );
                return "<a href=\"{$url}\" target=\"{$target}\" class=\"spslider-button-layer\" style=\"{$style}\" data-hover-bg=\"{$hover_bg}\">{$label}</a>";

            case 'video':
                $src      = esc_url( $s['src'] ?? '' );
                $poster   = esc_url( $s['poster'] ?? '' );
                $autoplay = ! empty( $s['autoplay'] ) ? 'autoplay' : '';
                $loop     = ! empty( $s['loop'] )     ? 'loop' : '';
                $muted    = ! empty( $s['muted'] )    ? 'muted' : '';
                $controls = ! empty( $s['controls'] ) ? 'controls' : '';
                return "<video class=\"spslider-video-layer\" src=\"{$src}\" poster=\"{$poster}\" {$autoplay} {$loop} {$muted} {$controls} playsinline style=\"width:100%;height:100%;object-fit:cover;\"></video>";

            case 'shape':
                $fill   = esc_attr( $s['fill'] ?? '#cccccc' );
                $stroke = esc_attr( $s['stroke'] ?? 'none' );
                $sw     = (int) ( $s['stroke_width'] ?? 0 );
                $br     = (int) ( $s['border_radius'] ?? 0 );
                return "<div class=\"spslider-shape-layer\" style=\"width:100%;height:100%;background:{$fill};border:{$sw}px solid {$stroke};border-radius:{$br}px;\"></div>";

            case 'countdown':
                $target = esc_attr( $s['countdown_target'] ?? ( $s['target_date'] ?? gmdate( 'Y-m-d\TH:i:s', strtotime( '+1 day' ) ) ) );
                $label  = esc_html( $s['countdown_label'] ?? ( $s['label'] ?? '' ) );
                $style  = sprintf(
                    'font-size:%dpx;color:%s;font-weight:%s;text-align:center;',
                    (int) ( $s['font_size'] ?? 32 ),
                    esc_attr( $s['color'] ?? '#ffffff' ),
                    esc_attr( $s['font_weight'] ?? '700' )
                );
                $expired_text = esc_attr( $s['countdown_expired'] ?? ( $s['expired_text'] ?? 'Expired!' ) );
                return "<div class=\"spslider-countdown-layer\" data-target=\"{$target}\" data-expired=\"{$expired_text}\" style=\"{$style}\"><span class=\"sp-cd-days\">00</span>d <span class=\"sp-cd-hours\">00</span>h <span class=\"sp-cd-mins\">00</span>m <span class=\"sp-cd-secs\">00</span>s</div>" . ( $label ? "<div class=\"spslider-countdown-label\" style=\"text-align:center;color:" . esc_attr( $s['color'] ?? '#fff' ) . ";font-size:" . (int) ( ( $s['font_size'] ?? 32 ) * 0.5 ) . "px;\">{$label}</div>" : '' );

            case 'icon':
                $icon_class = esc_attr( $s['icon_class'] ?? 'dashicons dashicons-star-filled' );
                $icon_size  = (int) ( $s['icon_size'] ?? 48 );
                $icon_color = esc_attr( $s['color'] ?? '#ffffff' );
                return "<span class=\"spslider-icon-layer {$icon_class}\" style=\"font-size:{$icon_size}px;color:{$icon_color};display:inline-flex;align-items:center;justify-content:center;width:100%;height:100%;\"></span>";

            case 'lottie':
                $lottie_src = esc_url( $s['lottie_src'] ?? ( $s['src'] ?? '' ) );
                $lottie_loop = ! empty( $s['lottie_loop'] ) || ! empty( $s['loop'] ) ? 'true' : 'false';
                $lottie_auto = ! empty( $s['lottie_autoplay'] ) || ! empty( $s['autoplay'] ) ? 'true' : 'false';
                return "<lottie-player class=\"spslider-lottie-layer\" src=\"{$lottie_src}\" background=\"transparent\" speed=\"1\" style=\"width:100%;height:100%;\"" . ( $lottie_loop === 'true' ? ' loop' : '' ) . ( $lottie_auto === 'true' ? ' autoplay' : '' ) . "></lottie-player>";

            case 'html':
                return "<div class=\"spslider-html-layer\" style=\"width:100%;height:100%;\">" . wp_kses_post( $s['html_content'] ?? ( $s['content'] ?? '' ) ) . "</div>";

            default:
                return '';
        }
    }

    private function build_bg_style( $s, $is_first ) {
        $style = '';
        if ( ! empty( $s['bg_image'] ) ) {
            $bg  = esc_attr( $s['bg_image'] );
            $pos = esc_attr( $s['bg_position'] ?? 'center center' );
            $sz  = esc_attr( $s['bg_size']     ?? 'cover' );
            $rep = esc_attr( $s['bg_repeat']   ?? 'no-repeat' );
            // Lazy-load background images (only first slide loads eagerly)
            if ( $is_first ) {
                $style .= "background-image:url('{$bg}');background-position:{$pos};background-size:{$sz};background-repeat:{$rep};";
            } else {
                $style .= "background-position:{$pos};background-size:{$sz};background-repeat:{$rep};";
                // data-bg-lazy is picked up by the JS lazy loader
            }
        } elseif ( ! empty( $s['bg_color'] ) ) {
            $style .= 'background:' . esc_attr( $s['bg_color'] ) . ';';
        }
        return $style;
    }

    /** Gutenberg block render callback */
    public function render_block( $attrs ) {
        $overrides = [];
        $map = [
            'autoplay'      => 'autoplay',
            'autoplaySpeed' => 'autoplay_speed',
            'loop'          => 'loop',
            'arrows'        => 'arrows',
            'dots'          => 'dots',
            'transition'    => 'transition',
            'speed'         => 'speed',
            'pauseOnHover'  => 'pause_on_hover',
            'touch'         => 'touch',
            'keyboardNav'   => 'keyboard_nav',
            'lazyLoad'      => 'lazy_load',
            'parallax'      => 'parallax',
            'scalingMode'   => 'scaling_mode',
            'width'         => 'width',
            'height'        => 'height',
        ];
        foreach ( $map as $block_key => $setting_key ) {
            if ( ! isset( $attrs[ $block_key ] ) ) continue;
            $v = $attrs[ $block_key ];
            if ( $v === '' || $v === 0 ) continue; // empty = use slider default
            if ( $v === 'true' )  { $overrides[ $setting_key ] = true;  continue; }
            if ( $v === 'false' ) { $overrides[ $setting_key ] = false; continue; }
            if ( is_numeric( $v ) ) { $overrides[ $setting_key ] = (int) $v; continue; }
            $overrides[ $setting_key ] = $v;
        }
        return $this->render( [
            'id'        => $attrs['sliderId'] ?? 0,
            'class'     => $attrs['cssClass'] ?? '',
            'overrides' => $overrides,
        ] );
    }
}
