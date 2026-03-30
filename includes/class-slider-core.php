<?php
/**
 * Slider Core Module
 * MVP foundation for Smart Slider-style workflows.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Slider_Core {

    public function __construct() {
        add_action('init', array($this, 'register_slider_cpt'));
        add_action('init', array($this, 'register_slider_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'register_runtime_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_slider_admin_assets'));
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        add_action('save_post_syntekpro_slider', array($this, 'save_slider_meta'));
    }

    private function get_default_layer_order() {
        return array('badge', 'title', 'description', 'button', 'caption');
    }

    private function normalize_layer_order($value) {
        $allowed = $this->get_default_layer_order();

        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = array_filter(array_map('trim', explode(',', (string) $value)));
        }

        $normalized = array();
        foreach ($parts as $part) {
            $key = sanitize_key($part);
            if (in_array($key, $allowed, true) && !in_array($key, $normalized, true)) {
                $normalized[] = $key;
            }
        }

        foreach ($allowed as $fallback) {
            if (!in_array($fallback, $normalized, true)) {
                $normalized[] = $fallback;
            }
        }

        return $normalized;
    }

    private function sanitize_layer_animation($value, $default = 'fade-up') {
        $allowed = array('none', 'fade-up', 'fade-down', 'fade-left', 'fade-right', 'zoom-in', 'zoom-out');
        $safe = sanitize_key((string) $value);
        if (!in_array($safe, $allowed, true)) {
            return $default;
        }
        return $safe;
    }

    /**
     * Register slider post type under the plugin menu.
     */
    public function register_slider_cpt() {
        $labels = array(
            'name' => __('Sliders', 'syntekpro-animations'),
            'singular_name' => __('Slider', 'syntekpro-animations'),
            'menu_name' => __('🎞 Sliders', 'syntekpro-animations'),
            'add_new' => __('Add New Slider', 'syntekpro-animations'),
            'add_new_item' => __('Add New Slider', 'syntekpro-animations'),
            'edit_item' => __('Edit Slider', 'syntekpro-animations'),
            'new_item' => __('New Slider', 'syntekpro-animations'),
            'view_item' => __('View Slider', 'syntekpro-animations'),
            'search_items' => __('Search Sliders', 'syntekpro-animations'),
            'not_found' => __('No sliders found.', 'syntekpro-animations'),
        );

        register_post_type('syntekpro_slider', array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'menu_position' => 30,
            'supports' => array('title'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
    }

    /**
     * Register shortcode.
     */
    public function register_slider_shortcode() {
        add_shortcode('sp_slider', array($this, 'render_slider_shortcode'));
    }

    /**
     * Register frontend assets (enqueue on demand in shortcode render).
     */
    public function register_runtime_assets() {
        wp_register_style(
            'syntekpro-slider-runtime',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/slider-runtime.css',
            array(),
            SYNTEKPRO_ANIM_VERSION
        );

        wp_register_script(
            'syntekpro-slider-runtime',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/slider-runtime.js',
            array(),
            SYNTEKPRO_ANIM_VERSION,
            true
        );
    }

    /**
     * Enqueue media library only on slider edit screens.
     */
    public function enqueue_slider_admin_assets($hook) {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->post_type !== 'syntekpro_slider') {
            return;
        }

        wp_enqueue_media();
    }

    /**
     * Render [sp_slider id="123"]
     */
    public function render_slider_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'sp_slider');

        $slider_id = absint($atts['id']);
        if ($slider_id <= 0) {
            return '';
        }

        $slider_post = get_post($slider_id);
        if (!$slider_post || $slider_post->post_type !== 'syntekpro_slider') {
            return '';
        }

        $settings = get_post_meta($slider_id, '_sp_slider_settings', true);
        $slides = get_post_meta($slider_id, '_sp_slider_slides', true);

        if (!is_array($settings)) {
            $settings = array();
        }
        if (!is_array($slides)) {
            $slides = array();
        }

        $defaults = array(
            'autoplay' => false,
            'autoplayDelay' => 5000,
            'autoplayPauseOnHover' => true,
            'pauseOnInteraction' => true,
            'loop' => true,
            'navigation' => true,
            'pagination' => true,
            'keyboardNav' => true,
            'swipeNav' => true,
            'progressBar' => true,
            'showCounter' => false,
            'thumbnails' => false,
            'lazyLoad' => true,
            'transition' => 'slide',
            'transitionSpeed' => 600,
            'heightDesktop' => 460,
            'heightTablet' => 400,
            'heightMobile' => 320,
            'contentAlign' => 'center',
            'overlayStrength' => 55,
        );

        $settings = array_merge($defaults, $settings);

        // 2.4.3: expose richer feature toggles/config for advanced runtime/editor behaviors.
        $advanced_defaults = array(
            'sliderWidth' => 1200,
            'easing' => 'ease',
            'pauseOnFocus' => true,
            'fluidMode' => 'auto-scale',
            'swipeSensitivity' => 35,
            'swipeDirection' => 'horizontal',
            'dynamicSource' => 'manual',
            'dynamicPostType' => 'post',
            'dynamicLimit' => 5,
            'analyticsEnabled' => true,
            'ga4Enabled' => false,
            'ga4EventPrefix' => 'syntekpro_slider',
            'customTransitionCss' => '',
        );
        $settings = array_merge($advanced_defaults, $settings);
        $settings = apply_filters('syntekpro_slider_settings', $settings, $slider_id, $slider_post);

        $slides = apply_filters('syntekpro_slider_slides', $slides, $slider_id, $settings);

        if (empty($slides)) {
            $slides = array(
                array(
                    'title' => __('Sample Slide', 'syntekpro-animations'),
                    'badge' => __('Featured', 'syntekpro-animations'),
                    'caption' => __('Built with Syntekpro Slider', 'syntekpro-animations'),
                    'description' => __('Edit this slider and replace sample content from the visual slide editor.', 'syntekpro-animations'),
                    'buttonText' => __('Learn More', 'syntekpro-animations'),
                    'buttonUrl' => '#',
                    'backgroundImage' => '',
                    'titleAnim' => 'fade-up',
                    'titleDelay' => 80,
                    'descAnim' => 'fade-up',
                    'descDelay' => 180,
                    'buttonAnim' => 'zoom-in',
                    'buttonDelay' => 300,
                    'badgeAnim' => 'fade-down',
                    'badgeDelay' => 0,
                    'captionAnim' => 'fade-up',
                    'captionDelay' => 360,
                    'titleAnimOut' => 'fade-down',
                    'descAnimOut' => 'fade-down',
                    'buttonAnimOut' => 'zoom-out',
                    'badgeAnimOut' => 'fade-up',
                    'captionAnimOut' => 'fade-down',
                    'layerDuration' => 720,
                    'layerStagger' => 70,
                    'layerOrder' => 'badge,title,description,button,caption',
                    'kenBurns' => false,
                    'kenBurnsScaleStart' => 1.06,
                    'kenBurnsScaleEnd' => 1.16,
                    'kenBurnsDuration' => 9000,
                    'kenBurnsDirection' => 'left-to-right',
                )
            );
        }

        wp_enqueue_style('syntekpro-slider-runtime');
        wp_enqueue_script('syntekpro-slider-runtime');

        $instance_id = 'sp-slider-' . $slider_id . '-' . wp_rand(100, 9999);

        $wrapper_attrs = array(
            'id' => $instance_id,
            'class' => 'sp-slider-runtime sp-transition-' . sanitize_html_class($settings['transition']),
            'data-autoplay' => !empty($settings['autoplay']) ? 'true' : 'false',
            'data-autoplay-delay' => (string) absint($settings['autoplayDelay']),
            'data-autoplay-pause-hover' => !empty($settings['autoplayPauseOnHover']) ? 'true' : 'false',
            'data-pause-on-interaction' => !empty($settings['pauseOnInteraction']) ? 'true' : 'false',
            'data-loop' => !empty($settings['loop']) ? 'true' : 'false',
            'data-pagination' => !empty($settings['pagination']) ? 'true' : 'false',
            'data-navigation' => !empty($settings['navigation']) ? 'true' : 'false',
            'data-keyboard' => !empty($settings['keyboardNav']) ? 'true' : 'false',
            'data-swipe' => !empty($settings['swipeNav']) ? 'true' : 'false',
            'data-progress' => !empty($settings['progressBar']) ? 'true' : 'false',
            'data-thumbnails' => !empty($settings['thumbnails']) ? 'true' : 'false',
            'data-counter' => !empty($settings['showCounter']) ? 'true' : 'false',
            'data-lazy' => !empty($settings['lazyLoad']) ? 'true' : 'false',
            'data-transition' => sanitize_text_field($settings['transition']),
            'data-speed' => (string) absint($settings['transitionSpeed']),
            'data-easing' => sanitize_text_field($settings['easing']),
            'data-align' => sanitize_text_field($settings['contentAlign']),
            'data-pause-on-focus' => !empty($settings['pauseOnFocus']) ? 'true' : 'false',
            'data-fluid-mode' => sanitize_text_field($settings['fluidMode']),
            'data-swipe-sensitivity' => (string) absint($settings['swipeSensitivity']),
            'data-swipe-direction' => sanitize_text_field($settings['swipeDirection']),
            'data-analytics' => !empty($settings['analyticsEnabled']) ? 'true' : 'false',
            'data-ga4' => !empty($settings['ga4Enabled']) ? 'true' : 'false',
            'data-ga4-prefix' => sanitize_text_field($settings['ga4EventPrefix']),
            'data-slider-id' => (string) $slider_id,
            'data-reduced-motion' => !empty($settings['reducedMotionMode']) ? 'true' : 'false',
            'data-focus-trap' => !empty($settings['focusTrapManagement']) ? 'true' : 'false',
            'data-gdpr-consent' => !empty($settings['gdprConsentLayer']) ? 'true' : 'false',
            'data-live-refresh' => (string) absint(isset($settings['liveDataRefresh']) ? $settings['liveDataRefresh'] : 30),
            'data-config' => wp_json_encode($settings),
            'style' => '--sp-height-desktop:' . absint($settings['heightDesktop']) . 'px;--sp-height-tablet:' . absint($settings['heightTablet']) . 'px;--sp-height-mobile:' . absint($settings['heightMobile']) . 'px;--sp-overlay-alpha:' . max(0, min(100, absint($settings['overlayStrength']))) / 100 . ';--sp-slider-width:' . absint($settings['sliderWidth']) . 'px;',
            'tabindex' => '0',
        );

        $attrs_html = '';
        foreach ($wrapper_attrs as $k => $v) {
            $attrs_html .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        }

        $html = '<div' . $attrs_html . '>';
        $html .= '<div class="sp-slider-track">';

        $transcript_items = array();
        foreach ($slides as $slide) {
            if (isset($slide['enabled']) && empty($slide['enabled'])) {
                continue;
            }
            $title = isset($slide['title']) ? $slide['title'] : '';
            $badge = isset($slide['badge']) ? $slide['badge'] : '';
            $caption = isset($slide['caption']) ? $slide['caption'] : '';
            $desc = isset($slide['description']) ? $slide['description'] : '';
            $btn_text = isset($slide['buttonText']) ? $slide['buttonText'] : '';
            $btn_url = isset($slide['buttonUrl']) ? $slide['buttonUrl'] : '#';
            $bg = isset($slide['backgroundImage']) ? $slide['backgroundImage'] : '';
            $title_anim = isset($slide['titleAnim']) ? sanitize_key($slide['titleAnim']) : 'fade-up';
            $title_delay = isset($slide['titleDelay']) ? absint($slide['titleDelay']) : 80;
            $desc_anim = isset($slide['descAnim']) ? sanitize_key($slide['descAnim']) : 'fade-up';
            $desc_delay = isset($slide['descDelay']) ? absint($slide['descDelay']) : 180;
            $button_anim = isset($slide['buttonAnim']) ? sanitize_key($slide['buttonAnim']) : 'zoom-in';
            $button_delay = isset($slide['buttonDelay']) ? absint($slide['buttonDelay']) : 300;
            $badge_anim = isset($slide['badgeAnim']) ? sanitize_key($slide['badgeAnim']) : 'fade-down';
            $badge_delay = isset($slide['badgeDelay']) ? absint($slide['badgeDelay']) : 0;
            $caption_anim = isset($slide['captionAnim']) ? sanitize_key($slide['captionAnim']) : 'fade-up';
            $caption_delay = isset($slide['captionDelay']) ? absint($slide['captionDelay']) : 360;
            $title_anim_out = isset($slide['titleAnimOut']) ? $this->sanitize_layer_animation($slide['titleAnimOut'], 'fade-down') : 'fade-down';
            $desc_anim_out = isset($slide['descAnimOut']) ? $this->sanitize_layer_animation($slide['descAnimOut'], 'fade-down') : 'fade-down';
            $button_anim_out = isset($slide['buttonAnimOut']) ? $this->sanitize_layer_animation($slide['buttonAnimOut'], 'zoom-out') : 'zoom-out';
            $badge_anim_out = isset($slide['badgeAnimOut']) ? $this->sanitize_layer_animation($slide['badgeAnimOut'], 'fade-up') : 'fade-up';
            $caption_anim_out = isset($slide['captionAnimOut']) ? $this->sanitize_layer_animation($slide['captionAnimOut'], 'fade-down') : 'fade-down';
            $layer_duration = isset($slide['layerDuration']) ? absint($slide['layerDuration']) : 720;
            $layer_stagger = isset($slide['layerStagger']) ? absint($slide['layerStagger']) : 70;
            $layer_order = $this->normalize_layer_order(isset($slide['layerOrder']) ? $slide['layerOrder'] : '');
            $title_motion_path = isset($slide['titleMotionPath']) ? sanitize_text_field($slide['titleMotionPath']) : '';
            $desc_motion_path = isset($slide['descMotionPath']) ? sanitize_text_field($slide['descMotionPath']) : '';
            $button_motion_path = isset($slide['buttonMotionPath']) ? sanitize_text_field($slide['buttonMotionPath']) : '';
            $badge_motion_path = isset($slide['badgeMotionPath']) ? sanitize_text_field($slide['badgeMotionPath']) : '';
            $caption_motion_path = isset($slide['captionMotionPath']) ? sanitize_text_field($slide['captionMotionPath']) : '';
            $ken_burns = !empty($slide['kenBurns']);
            $ken_burns_scale_start = isset($slide['kenBurnsScaleStart']) ? max(1, min(1.8, (float) $slide['kenBurnsScaleStart'])) : 1.06;
            $ken_burns_scale_end = isset($slide['kenBurnsScaleEnd']) ? max(1, min(2.2, (float) $slide['kenBurnsScaleEnd'])) : 1.16;
            $ken_burns_duration = isset($slide['kenBurnsDuration']) ? absint($slide['kenBurnsDuration']) : 9000;
            $ken_burns_direction = isset($slide['kenBurnsDirection']) ? sanitize_key($slide['kenBurnsDirection']) : 'left-to-right';
            $countdown_end = isset($slide['countdownEnd']) ? sanitize_text_field((string) $slide['countdownEnd']) : '';
            $live_endpoint = isset($slide['liveEndpoint']) ? esc_url_raw((string) $slide['liveEndpoint']) : '';
            $live_key = isset($slide['liveKey']) ? sanitize_key((string) $slide['liveKey']) : 'value';

            $title_anim = $this->sanitize_layer_animation($title_anim, 'fade-up');
            $desc_anim = $this->sanitize_layer_animation($desc_anim, 'fade-up');
            $button_anim = $this->sanitize_layer_animation($button_anim, 'zoom-in');
            $badge_anim = $this->sanitize_layer_animation($badge_anim, 'fade-down');
            $caption_anim = $this->sanitize_layer_animation($caption_anim, 'fade-up');

            $layer_fragments = array();
            if ($badge !== '') {
                $motion_attr = $badge_motion_path !== '' ? ' data-motion-path="' . esc_attr($badge_motion_path) . '"' : '';
                $layer_fragments['badge'] = '<span class="sp-slide-badge sp-layer sp-layer-in-' . esc_attr($badge_anim) . ' sp-layer-out-' . esc_attr($badge_anim_out) . '" data-layer="badge" data-delay="' . esc_attr((string) $badge_delay) . '"' . $motion_attr . '>' . esc_html($badge) . '</span>';
            }
            if ($title !== '') {
                $motion_attr = $title_motion_path !== '' ? ' data-motion-path="' . esc_attr($title_motion_path) . '"' : '';
                $layer_fragments['title'] = '<h3 class="sp-layer sp-layer-in-' . esc_attr($title_anim) . ' sp-layer-out-' . esc_attr($title_anim_out) . '" data-layer="title" data-delay="' . esc_attr((string) $title_delay) . '"' . $motion_attr . '>' . esc_html($title) . '</h3>';
            }
            if ($desc !== '') {
                $motion_attr = $desc_motion_path !== '' ? ' data-motion-path="' . esc_attr($desc_motion_path) . '"' : '';
                $layer_fragments['description'] = '<p class="sp-layer sp-layer-in-' . esc_attr($desc_anim) . ' sp-layer-out-' . esc_attr($desc_anim_out) . '" data-layer="description" data-delay="' . esc_attr((string) $desc_delay) . '"' . $motion_attr . '>' . esc_html($desc) . '</p>';
            }
            if ($btn_text !== '') {
                $motion_attr = $button_motion_path !== '' ? ' data-motion-path="' . esc_attr($button_motion_path) . '"' : '';
                $layer_fragments['button'] = '<a class="sp-slide-btn sp-layer sp-layer-in-' . esc_attr($button_anim) . ' sp-layer-out-' . esc_attr($button_anim_out) . '" data-layer="button" data-delay="' . esc_attr((string) $button_delay) . '" href="' . esc_url($btn_url) . '"' . $motion_attr . '>' . esc_html($btn_text) . '</a>';
            }
            if ($caption !== '') {
                $motion_attr = $caption_motion_path !== '' ? ' data-motion-path="' . esc_attr($caption_motion_path) . '"' : '';
                $layer_fragments['caption'] = '<span class="sp-slide-caption sp-layer sp-layer-in-' . esc_attr($caption_anim) . ' sp-layer-out-' . esc_attr($caption_anim_out) . '" data-layer="caption" data-delay="' . esc_attr((string) $caption_delay) . '"' . $motion_attr . '>' . esc_html($caption) . '</span>';
            }
            if (!empty($settings['countdownLiveDataLayers']) && $countdown_end !== '') {
                $layer_fragments['countdown'] = '<span class="sp-slide-countdown sp-layer" data-layer="countdown" data-delay="0" data-countdown-end="' . esc_attr($countdown_end) . '"></span>';
            }
            if (!empty($settings['countdownLiveDataLayers']) && $live_endpoint !== '') {
                $layer_fragments['live'] = '<span class="sp-slide-live sp-layer" data-layer="live" data-delay="0" data-live-endpoint="' . esc_url($live_endpoint) . '" data-live-key="' . esc_attr($live_key) . '"></span>';
            }

            $transcript = trim(implode(' ', array_filter(array($badge, $title, $desc, $caption, $btn_text))));
            if ($transcript !== '') {
                $transcript_items[] = $transcript;
            }

            $bg_style = (!empty($bg) && empty($settings['lazyLoad'])) ? ' style="background-image:url(' . esc_url($bg) . ');"' : '';
            $bg_data = (!empty($bg) && !empty($settings['lazyLoad'])) ? ' data-bg="' . esc_url($bg) . '"' : '';
            $html .= '<article class="sp-slide" data-layer-duration="' . esc_attr((string) $layer_duration) . '" data-layer-stagger="' . esc_attr((string) $layer_stagger) . '" data-kb-enabled="' . ($ken_burns ? 'true' : 'false') . '" data-kb-scale-start="' . esc_attr((string) $ken_burns_scale_start) . '" data-kb-scale-end="' . esc_attr((string) $ken_burns_scale_end) . '" data-kb-duration="' . esc_attr((string) $ken_burns_duration) . '" data-kb-direction="' . esc_attr($ken_burns_direction) . '">';
            $html .= '<div class="sp-slide-bg"' . $bg_style . $bg_data . '></div>';
            $html .= '<div class="sp-slide-overlay"></div>';
            $html .= '<div class="sp-slide-content">';
            foreach ($layer_order as $order_index => $layer_key) {
                if (!isset($layer_fragments[$layer_key])) {
                    continue;
                }
                $html .= str_replace(' data-layer="', ' data-order="' . esc_attr((string) $order_index) . '" data-layer="', $layer_fragments[$layer_key]);
            }
            foreach (array('countdown', 'live') as $utility_layer) {
                if (isset($layer_fragments[$utility_layer])) {
                    $html .= $layer_fragments[$utility_layer];
                }
            }
            $html .= '</div>';
            $html .= '</article>';
        }

        $html .= '</div>';

        if (!empty($settings['progressBar'])) {
            $html .= '<div class="sp-slider-progress" aria-hidden="true"><span class="sp-slider-progress-fill"></span></div>';
        }

        if (!empty($settings['showCounter'])) {
            $html .= '<div class="sp-slider-counter" aria-live="polite"></div>';
        }

        if (!empty($settings['navigation'])) {
            $html .= '<button class="sp-slider-prev" type="button" aria-label="' . esc_attr__('Previous slide', 'syntekpro-animations') . '">&#10094;</button>';
            $html .= '<button class="sp-slider-next" type="button" aria-label="' . esc_attr__('Next slide', 'syntekpro-animations') . '">&#10095;</button>';
        }

        if (!empty($settings['pagination'])) {
            $html .= '<div class="sp-slider-dots" aria-hidden="true"></div>';
        }

        if (!empty($settings['thumbnails'])) {
            $html .= '<div class="sp-slider-thumbs" aria-label="' . esc_attr__('Slide thumbnails', 'syntekpro-animations') . '">';
            $thumb_visible_index = 0;
            foreach ($slides as $thumb_index => $slide) {
                if (isset($slide['enabled']) && empty($slide['enabled'])) {
                    continue;
                }
                $thumb_title = isset($slide['title']) ? $slide['title'] : '';
                $thumb_bg = isset($slide['backgroundImage']) ? $slide['backgroundImage'] : '';
                $thumb_style = $thumb_bg ? ' style="background-image:url(' . esc_url($thumb_bg) . ');"' : '';
                $html .= '<button type="button" class="sp-slider-thumb" data-index="' . esc_attr((string) $thumb_visible_index) . '" aria-label="' . esc_attr(sprintf(__('Open slide %d', 'syntekpro-animations'), $thumb_visible_index + 1)) . '">';
                $html .= '<span class="sp-slider-thumb-bg"' . $thumb_style . '></span>';
                $html .= '<span class="sp-slider-thumb-title">' . esc_html($thumb_title !== '' ? $thumb_title : sprintf(__('Slide %d', 'syntekpro-animations'), $thumb_visible_index + 1)) . '</span>';
                $html .= '</button>';
                $thumb_visible_index++;
            }
            $html .= '</div>';
        }

        if (!empty($settings['screenReaderSlideTranscript']) && !empty($transcript_items)) {
            $html .= '<ol class="sp-slider-transcript" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">';
            foreach ($transcript_items as $item) {
                $html .= '<li>' . esc_html($item) . '</li>';
            }
            $html .= '</ol>';
        }

        $html .= '</div>';

        $critical_css = get_post_meta($slider_id, '_sp_slider_critical_css', true);
        if (!empty($settings['criticalCssExtraction']) && is_string($critical_css) && $critical_css !== '') {
            $html = '<style class="sp-slider-critical-css">' . wp_strip_all_tags($critical_css) . '</style>' . $html;
        }

        if (!empty($settings['edgeCachingLayer'])) {
            $snapshot_html = get_post_meta($slider_id, '_sp_slider_snapshot_html', true);
            if (is_string($snapshot_html) && $snapshot_html !== '') {
                $html = '<div class="sp-slider-edge-snapshot" aria-hidden="true" style="display:none;">' . $snapshot_html . '</div>' . $html;
            }
        }

        if (!empty($settings['webComponentsOutputMode'])) {
            $html = '<slider-pro data-slider-id="' . esc_attr((string) $slider_id) . '">' . $html . '</slider-pro>';
        }

        do_action('syntekpro_slider_rendered', $slider_id, $settings, $slides, $instance_id);

        return $html;
    }

    /**
     * Add simple JSON-based settings/slides editor for MVP.
     */
    public function register_meta_boxes() {
        add_meta_box(
            'sp_slider_data',
            __('Slider Data (MVP)', 'syntekpro-animations'),
            array($this, 'render_slider_meta_box'),
            'syntekpro_slider',
            'normal',
            'high'
        );
    }

    public function render_slider_meta_box($post) {
        wp_nonce_field('sp_slider_meta_save', 'sp_slider_meta_nonce');

        $settings = get_post_meta($post->ID, '_sp_slider_settings', true);
        $slides = get_post_meta($post->ID, '_sp_slider_slides', true);

        if (!is_array($settings)) {
            $settings = array(
                'autoplay' => false,
                'autoplayDelay' => 5000,
                'autoplayPauseOnHover' => true,
                'pauseOnInteraction' => true,
                'loop' => true,
                'navigation' => true,
                'pagination' => true,
                'keyboardNav' => true,
                'swipeNav' => true,
                'progressBar' => true,
                'showCounter' => false,
                'thumbnails' => false,
                'lazyLoad' => true,
                'transition' => 'slide',
                'transitionSpeed' => 600,
                'heightDesktop' => 460,
                'heightTablet' => 400,
                'heightMobile' => 320,
                'contentAlign' => 'center',
                'overlayStrength' => 55,
            );
        }

        if (!is_array($slides)) {
            $slides = array(
                array(
                    'title' => 'Slide One',
                    'badge' => 'Featured',
                    'caption' => 'Built with Syntekpro Slider',
                    'description' => 'Describe your offer or feature here.',
                    'buttonText' => 'Learn More',
                    'buttonUrl' => '#',
                    'backgroundImage' => '',
                    'titleAnim' => 'fade-up',
                    'titleDelay' => 80,
                    'descAnim' => 'fade-up',
                    'descDelay' => 180,
                    'buttonAnim' => 'zoom-in',
                    'buttonDelay' => 300,
                    'badgeAnim' => 'fade-down',
                    'badgeDelay' => 0,
                    'captionAnim' => 'fade-up',
                    'captionDelay' => 360,
                    'titleAnimOut' => 'fade-down',
                    'descAnimOut' => 'fade-down',
                    'buttonAnimOut' => 'zoom-out',
                    'badgeAnimOut' => 'fade-up',
                    'captionAnimOut' => 'fade-down',
                    'layerDuration' => 720,
                    'layerStagger' => 70,
                    'layerOrder' => 'badge,title,description,button,caption',
                    'kenBurns' => false,
                    'kenBurnsScaleStart' => 1.06,
                    'kenBurnsScaleEnd' => 1.16,
                    'kenBurnsDuration' => 9000,
                    'kenBurnsDirection' => 'left-to-right',
                ),
                array(
                    'title' => 'Slide Two',
                    'badge' => 'New',
                    'caption' => 'Add your marketing caption here',
                    'description' => 'Add another message for your audience.',
                    'buttonText' => 'Get Started',
                    'buttonUrl' => '#',
                    'backgroundImage' => '',
                    'titleAnim' => 'fade-up',
                    'titleDelay' => 80,
                    'descAnim' => 'fade-right',
                    'descDelay' => 180,
                    'buttonAnim' => 'zoom-in',
                    'buttonDelay' => 300,
                    'badgeAnim' => 'fade-down',
                    'badgeDelay' => 0,
                    'captionAnim' => 'fade-up',
                    'captionDelay' => 360,
                    'titleAnimOut' => 'fade-down',
                    'descAnimOut' => 'fade-left',
                    'buttonAnimOut' => 'zoom-out',
                    'badgeAnimOut' => 'fade-up',
                    'captionAnimOut' => 'fade-down',
                    'layerDuration' => 720,
                    'layerStagger' => 70,
                    'layerOrder' => 'badge,title,description,button,caption',
                    'kenBurns' => false,
                    'kenBurnsScaleStart' => 1.06,
                    'kenBurnsScaleEnd' => 1.16,
                    'kenBurnsDuration' => 9000,
                    'kenBurnsDirection' => 'left-to-right',
                ),
            );
        }

        $autoplay = !empty($settings['autoplay']);
        $autoplay_delay = isset($settings['autoplayDelay']) ? absint($settings['autoplayDelay']) : 5000;
        $loop = !empty($settings['loop']);
        $navigation = !empty($settings['navigation']);
        $pagination = !empty($settings['pagination']);
        $keyboard_nav = !empty($settings['keyboardNav']);
        $swipe_nav = !empty($settings['swipeNav']);
        $progress_bar = !empty($settings['progressBar']);
        $show_counter = !empty($settings['showCounter']);
        $thumbnails = !empty($settings['thumbnails']);
        $lazy_load = !empty($settings['lazyLoad']);
        $autoplay_pause_hover = !empty($settings['autoplayPauseOnHover']);
        $pause_on_interaction = !empty($settings['pauseOnInteraction']);
        $transition = isset($settings['transition']) ? sanitize_text_field($settings['transition']) : 'slide';
        $transition_speed = isset($settings['transitionSpeed']) ? absint($settings['transitionSpeed']) : 600;
        $easing = isset($settings['easing']) ? sanitize_text_field($settings['easing']) : 'ease';
        $height_desktop = isset($settings['heightDesktop']) ? absint($settings['heightDesktop']) : 460;
        $height_tablet = isset($settings['heightTablet']) ? absint($settings['heightTablet']) : 400;
        $height_mobile = isset($settings['heightMobile']) ? absint($settings['heightMobile']) : 320;
        $content_align = isset($settings['contentAlign']) ? sanitize_text_field($settings['contentAlign']) : 'center';
        $overlay_strength = isset($settings['overlayStrength']) ? absint($settings['overlayStrength']) : 55;
        $slider_width = isset($settings['sliderWidth']) ? absint($settings['sliderWidth']) : 1200;
        $pause_on_focus = !empty($settings['pauseOnFocus']);
        $fluid_mode = isset($settings['fluidMode']) ? sanitize_text_field($settings['fluidMode']) : 'auto-scale';
        $swipe_sensitivity = isset($settings['swipeSensitivity']) ? absint($settings['swipeSensitivity']) : 35;
        $swipe_direction = isset($settings['swipeDirection']) ? sanitize_text_field($settings['swipeDirection']) : 'horizontal';
        $dynamic_source = isset($settings['dynamicSource']) ? sanitize_text_field($settings['dynamicSource']) : 'manual';
        $dynamic_post_type = isset($settings['dynamicPostType']) ? sanitize_text_field($settings['dynamicPostType']) : 'post';
        $dynamic_limit = isset($settings['dynamicLimit']) ? absint($settings['dynamicLimit']) : 5;
        $analytics_enabled = !empty($settings['analyticsEnabled']);
        $ga4_enabled = !empty($settings['ga4Enabled']);
        $ga4_event_prefix = isset($settings['ga4EventPrefix']) ? sanitize_text_field($settings['ga4EventPrefix']) : 'syntekpro_slider';
        $custom_transition_css = isset($settings['customTransitionCss']) ? sanitize_textarea_field($settings['customTransitionCss']) : '';
        $ab_traffic_split = isset($settings['abTrafficSplit']) ? sanitize_text_field($settings['abTrafficSplit']) : '50:50';
        $event_webhook_url = isset($settings['eventWebhookUrl']) ? esc_url($settings['eventWebhookUrl']) : '';
        $conversion_goal_url = isset($settings['conversionGoalUrl']) ? esc_url($settings['conversionGoalUrl']) : '';

        echo '<p><strong>' . esc_html__('Shortcode:', 'syntekpro-animations') . '</strong> <code>[sp_slider id="' . esc_html((string) $post->ID) . '"]</code></p>';
        echo '<p>' . esc_html__('Use the visual controls below to build your slider. JSON fallback remains available for advanced editing.', 'syntekpro-animations') . '</p>';

        echo '<div class="sp-slider-editor">';
        echo '<section class="sp-slider-hero">';
        echo '<div class="sp-slider-hero-copy">';
        echo '<h3>' . esc_html__('SyntekPro Slider Studio', 'syntekpro-animations') . '</h3>';
        echo '<p>' . esc_html__('Build polished, high-converting sliders with AI tools, performance tuning, and advanced motion controls.', 'syntekpro-animations') . '</p>';
        echo '</div>';
        echo '<div class="sp-slider-hero-actions">';
        echo '<button type="button" class="button sp-btn-features" id="sp-open-features">' . esc_html__('View Slider Features', 'syntekpro-animations') . '</button>';
        echo '</div>';
        echo '</section>';

        echo '<div class="sp-slider-settings">';
        echo '<h4>' . esc_html__('Slider Settings', 'syntekpro-animations') . '</h4>';
        echo '<div class="sp-slider-grid">';

        echo '<label><span>' . esc_html__('Transition', 'syntekpro-animations') . '</span>';
        echo '<select name="sp_slider_settings[transition]">';
        $transition_options = array('slide', 'fade', 'zoom', 'crossfade', 'parallax', 'ken-burns', 'cube', 'flip', 'custom-css');
        foreach ($transition_options as $option) {
            echo '<option value="' . esc_attr($option) . '" ' . selected($transition, $option, false) . '>' . esc_html(ucfirst($option)) . '</option>';
        }
        echo '</select></label>';

        echo '<label><span>' . esc_html__('Height (Desktop px)', 'syntekpro-animations') . '</span><input type="number" min="220" max="1200" name="sp_slider_settings[heightDesktop]" value="' . esc_attr((string) $height_desktop) . '"></label>';
        echo '<label><span>' . esc_html__('Height (Tablet px)', 'syntekpro-animations') . '</span><input type="number" min="180" max="1000" name="sp_slider_settings[heightTablet]" value="' . esc_attr((string) $height_tablet) . '"></label>';
        echo '<label><span>' . esc_html__('Height (Mobile px)', 'syntekpro-animations') . '</span><input type="number" min="160" max="900" name="sp_slider_settings[heightMobile]" value="' . esc_attr((string) $height_mobile) . '"></label>';
        echo '<label><span>' . esc_html__('Transition Speed (ms)', 'syntekpro-animations') . '</span><input type="number" min="100" max="3000" step="50" name="sp_slider_settings[transitionSpeed]" value="' . esc_attr((string) $transition_speed) . '"></label>';
        echo '<label><span>' . esc_html__('Easing', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_settings[easing]" value="' . esc_attr($easing) . '" placeholder="ease"></label>';
        echo '<label><span>' . esc_html__('Autoplay Delay (ms)', 'syntekpro-animations') . '</span><input type="number" min="1000" max="20000" step="100" name="sp_slider_settings[autoplayDelay]" value="' . esc_attr((string) $autoplay_delay) . '"></label>';
        echo '<label><span>' . esc_html__('Slider Width (px)', 'syntekpro-animations') . '</span><input type="number" min="320" max="2560" step="10" name="sp_slider_settings[sliderWidth]" value="' . esc_attr((string) $slider_width) . '"></label>';
        echo '<label><span>' . esc_html__('Content Align', 'syntekpro-animations') . '</span><select name="sp_slider_settings[contentAlign]"><option value="left" ' . selected($content_align, 'left', false) . '>' . esc_html__('Left', 'syntekpro-animations') . '</option><option value="center" ' . selected($content_align, 'center', false) . '>' . esc_html__('Center', 'syntekpro-animations') . '</option><option value="right" ' . selected($content_align, 'right', false) . '>' . esc_html__('Right', 'syntekpro-animations') . '</option></select></label>';
        echo '<label><span>' . esc_html__('Fluid Mode', 'syntekpro-animations') . '</span><select name="sp_slider_settings[fluidMode]"><option value="auto-scale" ' . selected($fluid_mode, 'auto-scale', false) . '>Auto Scale</option><option value="fixed" ' . selected($fluid_mode, 'fixed', false) . '>Fixed</option><option value="full-width" ' . selected($fluid_mode, 'full-width', false) . '>Full Width</option><option value="full-screen" ' . selected($fluid_mode, 'full-screen', false) . '>Full Screen</option><option value="aspect-lock" ' . selected($fluid_mode, 'aspect-lock', false) . '>Aspect Lock</option></select></label>';
        echo '<label><span>' . esc_html__('Swipe Sensitivity', 'syntekpro-animations') . '</span><input type="number" min="10" max="160" step="1" name="sp_slider_settings[swipeSensitivity]" value="' . esc_attr((string) $swipe_sensitivity) . '"></label>';
        echo '<label><span>' . esc_html__('Swipe Direction', 'syntekpro-animations') . '</span><select name="sp_slider_settings[swipeDirection]"><option value="horizontal" ' . selected($swipe_direction, 'horizontal', false) . '>Horizontal</option><option value="vertical" ' . selected($swipe_direction, 'vertical', false) . '>Vertical</option><option value="both" ' . selected($swipe_direction, 'both', false) . '>Both</option></select></label>';
        echo '<label><span>' . esc_html__('Dynamic Source', 'syntekpro-animations') . '</span><select name="sp_slider_settings[dynamicSource]"><option value="manual" ' . selected($dynamic_source, 'manual', false) . '>Manual</option><option value="posts" ' . selected($dynamic_source, 'posts', false) . '>Posts</option><option value="products" ' . selected($dynamic_source, 'products', false) . '>Products</option><option value="cpt" ' . selected($dynamic_source, 'cpt', false) . '>Custom Post Type</option><option value="acf" ' . selected($dynamic_source, 'acf', false) . '>ACF Mapping</option></select></label>';
        echo '<label><span>' . esc_html__('Dynamic Post Type', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_settings[dynamicPostType]" value="' . esc_attr($dynamic_post_type) . '" placeholder="post"></label>';
        echo '<label><span>' . esc_html__('Dynamic Limit', 'syntekpro-animations') . '</span><input type="number" min="1" max="30" step="1" name="sp_slider_settings[dynamicLimit]" value="' . esc_attr((string) $dynamic_limit) . '"></label>';
        echo '<label><span>' . esc_html__('Overlay Strength (%)', 'syntekpro-animations') . '</span><input type="number" min="0" max="90" step="1" name="sp_slider_settings[overlayStrength]" value="' . esc_attr((string) $overlay_strength) . '"></label>';
        echo '<label class="full"><span>' . esc_html__('Custom Transition CSS', 'syntekpro-animations') . '</span><textarea rows="3" name="sp_slider_settings[customTransitionCss]" placeholder=".sp-slider-runtime.sp-transition-custom-css .sp-slide{}">' . esc_textarea($custom_transition_css) . '</textarea></label>';

        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[autoplay]" value="1" ' . checked($autoplay, true, false) . '><span>' . esc_html__('Enable Autoplay', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[autoplayPauseOnHover]" value="1" ' . checked($autoplay_pause_hover, true, false) . '><span>' . esc_html__('Pause Autoplay on Hover', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[pauseOnInteraction]" value="1" ' . checked($pause_on_interaction, true, false) . '><span>' . esc_html__('Pause on Manual Interaction', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[pauseOnFocus]" value="1" ' . checked($pause_on_focus, true, false) . '><span>' . esc_html__('Pause on Focus', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[loop]" value="1" ' . checked($loop, true, false) . '><span>' . esc_html__('Enable Loop', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[navigation]" value="1" ' . checked($navigation, true, false) . '><span>' . esc_html__('Show Arrows', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[pagination]" value="1" ' . checked($pagination, true, false) . '><span>' . esc_html__('Show Dots', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[keyboardNav]" value="1" ' . checked($keyboard_nav, true, false) . '><span>' . esc_html__('Keyboard Navigation', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[swipeNav]" value="1" ' . checked($swipe_nav, true, false) . '><span>' . esc_html__('Touch Swipe Navigation', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[progressBar]" value="1" ' . checked($progress_bar, true, false) . '><span>' . esc_html__('Show Progress Bar', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[showCounter]" value="1" ' . checked($show_counter, true, false) . '><span>' . esc_html__('Show Slide Counter', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[thumbnails]" value="1" ' . checked($thumbnails, true, false) . '><span>' . esc_html__('Show Thumbnails', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[lazyLoad]" value="1" ' . checked($lazy_load, true, false) . '><span>' . esc_html__('Lazy Load Slide Backgrounds', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[analyticsEnabled]" value="1" ' . checked($analytics_enabled, true, false) . '><span>' . esc_html__('Enable Slider Analytics', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[ga4Enabled]" value="1" ' . checked($ga4_enabled, true, false) . '><span>' . esc_html__('Enable GA4 Event Bridge', 'syntekpro-animations') . '</span></label>';
        echo '<label><span>' . esc_html__('GA4 Event Prefix', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_settings[ga4EventPrefix]" value="' . esc_attr($ga4_event_prefix) . '"></label>';
        echo '<label class="full"><span>' . esc_html__('Event Webhook URL (Zapier/Make)', 'syntekpro-animations') . '</span><input type="url" name="sp_slider_settings[eventWebhookUrl]" value="' . esc_attr($event_webhook_url) . '" placeholder="https://hooks.zapier.com/..." /></label>';
        echo '<label><span>' . esc_html__('A/B Traffic Split', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_settings[abTrafficSplit]" value="' . esc_attr($ab_traffic_split) . '" placeholder="50:50" /></label>';
        echo '<label><span>' . esc_html__('Conversion Goal URL', 'syntekpro-animations') . '</span><input type="url" name="sp_slider_settings[conversionGoalUrl]" value="' . esc_attr($conversion_goal_url) . '" placeholder="https://example.com/checkout" /></label>';

        echo '<details class="full" style="background:#f8fafc;border:1px solid #dbe3ee;border-radius:8px;padding:10px;">';
        echo '<summary style="cursor:pointer;font-weight:600;">' . esc_html__('Advanced Feature Matrix (25-slider update)', 'syntekpro-animations') . '</summary>';
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:8px;margin-top:10px;">';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[aiSlideGenerator]" value="1" ' . checked(!empty($settings['aiSlideGenerator']), true, false) . '><span>AI Slide Generator</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[aiSmartCrop]" value="1" ' . checked(!empty($settings['aiSmartCrop']), true, false) . '><span>AI Smart Image Cropping</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[aiAutoContrast]" value="1" ' . checked(!empty($settings['aiAutoContrast']), true, false) . '><span>AI Auto Contrast & Readability</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[aiCopySuggestions]" value="1" ' . checked(!empty($settings['aiCopySuggestions']), true, false) . '><span>AI Copy Suggestions</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[aiTimingPredictor]" value="1" ' . checked(!empty($settings['aiTimingPredictor']), true, false) . '><span>AI Timing Predictor</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[criticalCssExtraction]" value="1" ' . checked(!empty($settings['criticalCssExtraction']), true, false) . '><span>Critical CSS Extraction</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[adaptiveVideoLoading]" value="1" ' . checked(!empty($settings['adaptiveVideoLoading']), true, false) . '><span>Adaptive Video Loading</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[edgeCachingLayer]" value="1" ' . checked(!empty($settings['edgeCachingLayer']), true, false) . '><span>Edge Caching Snapshot</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[coreWebVitalsDashboard]" value="1" ' . checked(!empty($settings['coreWebVitalsDashboard']), true, false) . '><span>Core Web Vitals Dashboard</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[autoConvertImages]" value="1" ' . checked(!empty($settings['autoConvertImages']), true, false) . '><span>AVIF/WebP Auto Convert</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[bulkEditor]" value="1" ' . checked(!empty($settings['bulkEditor']), true, false) . '><span>Multi-slide Bulk Editor</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[globalDesignTokens]" value="1" ' . checked(!empty($settings['globalDesignTokens']), true, false) . '><span>Global Design Tokens</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[revisionHistoryDiff]" value="1" ' . checked(!empty($settings['revisionHistoryDiff']), true, false) . '><span>Revision History with Diff</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[collaborativeEditing]" value="1" ' . checked(!empty($settings['collaborativeEditing']), true, false) . '><span>Collaborative Editing</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[motionPathEditor]" value="1" ' . checked(!empty($settings['motionPathEditor']), true, false) . '><span>Motion Path Editor</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[abTestingBuilder]" value="1" ' . checked(!empty($settings['abTestingBuilder']), true, false) . '><span>A/B Testing Builder</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[restApiEnabled]" value="1" ' . checked(!empty($settings['restApiEnabled']), true, false) . '><span>REST API & Webhooks</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[blockHeadlessOutput]" value="1" ' . checked(!empty($settings['blockHeadlessOutput']), true, false) . '><span>Headless / Block Output</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[figmaImport]" value="1" ' . checked(!empty($settings['figmaImport']), true, false) . '><span>Figma Import</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[gsapLottieLayer]" value="1" ' . checked(!empty($settings['gsapLottieLayer']), true, false) . '><span>GSAP & Lottie Layer Type</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[zapierMakeTriggers]" value="1" ' . checked(!empty($settings['zapierMakeTriggers']), true, false) . '><span>Zapier/Make Triggers</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[whiteLabelMode]" value="1" ' . checked(!empty($settings['whiteLabelMode']), true, false) . '><span>White Label Mode</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[cloudTemplateMarketplace]" value="1" ' . checked(!empty($settings['cloudTemplateMarketplace']), true, false) . '><span>Cloud Template Marketplace</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[usageCloudSync]" value="1" ' . checked(!empty($settings['usageCloudSync']), true, false) . '><span>Usage-based Cloud Sync</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[conversionGoalTracking]" value="1" ' . checked(!empty($settings['conversionGoalTracking']), true, false) . '><span>Conversion Goal Tracking</span></label>';
        echo '</div>';
        echo '</details>';

        echo '<details class="full" style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:10px;margin-top:8px;">';
        echo '<summary style="cursor:pointer;font-weight:600;">' . esc_html__('Enhancement Matrix (30-request pack)', 'syntekpro-animations') . '</summary>';
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:8px;margin-top:10px;">';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[cliScaffoldTool]" value="1" ' . checked(!empty($settings['cliScaffoldTool']), true, false) . '><span>CLI Scaffold Tool</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[typeScriptDefinitions]" value="1" ' . checked(!empty($settings['typeScriptDefinitions']), true, false) . '><span>TypeScript Definitions</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[storybookComponentLibrary]" value="1" ' . checked(!empty($settings['storybookComponentLibrary']), true, false) . '><span>Storybook Layer Library</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[automatedUpgradeMigrations]" value="1" ' . checked(!empty($settings['automatedUpgradeMigrations']), true, false) . '><span>Automated Upgrade Migrations</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[localImportExport]" value="1" ' . checked(!empty($settings['localImportExport']), true, false) . '><span>Local Import / Export</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[scheduledSlidePublishing]" value="1" ' . checked(!empty($settings['scheduledSlidePublishing']), true, false) . '><span>Scheduled Slide Publishing</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[personalisationEngine]" value="1" ' . checked(!empty($settings['personalisationEngine']), true, false) . '><span>Personalisation Engine</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[multilingualLayerSupport]" value="1" ' . checked(!empty($settings['multilingualLayerSupport']), true, false) . '><span>Multilingual Layer Support</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[csvGoogleSheetsDataSource]" value="1" ' . checked(!empty($settings['csvGoogleSheetsDataSource']), true, false) . '><span>CSV / Google Sheets Source</span></label>';
        echo '<label><span>' . esc_html__('CSV/Sheets URL', 'syntekpro-animations') . '</span><input type="url" name="sp_slider_settings[csvDataUrl]" value="' . esc_attr(isset($settings['csvDataUrl']) ? $settings['csvDataUrl'] : '') . '" placeholder="https://..."></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[countdownLiveDataLayers]" value="1" ' . checked(!empty($settings['countdownLiveDataLayers']), true, false) . '><span>Countdown & Live Data Layers</span></label>';
        echo '<label><span>' . esc_html__('Live Data Refresh (sec)', 'syntekpro-animations') . '</span><input type="number" min="5" max="300" step="1" name="sp_slider_settings[liveDataRefresh]" value="' . esc_attr((string) (isset($settings['liveDataRefresh']) ? absint($settings['liveDataRefresh']) : 30)) . '"></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[contentSecurityPolicyHeaders]" value="1" ' . checked(!empty($settings['contentSecurityPolicyHeaders']), true, false) . '><span>CSP Headers</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[signedAssetIntegrityChecks]" value="1" ' . checked(!empty($settings['signedAssetIntegrityChecks']), true, false) . '><span>Signed Asset Integrity Checks</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[gdprConsentLayer]" value="1" ' . checked(!empty($settings['gdprConsentLayer']), true, false) . '><span>GDPR Consent Layer</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[roleBasedEditorPermissions]" value="1" ' . checked(!empty($settings['roleBasedEditorPermissions']), true, false) . '><span>Role-based Editor Permissions</span></label>';
        echo '<label><span>' . esc_html__('Allowed Roles (csv)', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_settings[allowedEditorRoles]" value="' . esc_attr(isset($settings['allowedEditorRoles']) ? $settings['allowedEditorRoles'] : 'administrator,editor') . '"></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[officialAddonSdk]" value="1" ' . checked(!empty($settings['officialAddonSdk']), true, false) . '><span>Official Add-on SDK</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[pageBuilderDeepIntegration]" value="1" ' . checked(!empty($settings['pageBuilderDeepIntegration']), true, false) . '><span>Page Builder Deep Integration</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[wpCliCommands]" value="1" ' . checked(!empty($settings['wpCliCommands']), true, false) . '><span>WP CLI Commands</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[multisiteNetworkManagement]" value="1" ' . checked(!empty($settings['multisiteNetworkManagement']), true, false) . '><span>Multisite Network Management</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[wcagAuditMode]" value="1" ' . checked(!empty($settings['wcagAuditMode']), true, false) . '><span>WCAG 2.2 AA Audit Mode</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[reducedMotionMode]" value="1" ' . checked(!empty($settings['reducedMotionMode']), true, false) . '><span>Reduced Motion Mode</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[screenReaderSlideTranscript]" value="1" ' . checked(!empty($settings['screenReaderSlideTranscript']), true, false) . '><span>Screen Reader Transcript</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[focusTrapManagement]" value="1" ' . checked(!empty($settings['focusTrapManagement']), true, false) . '><span>Focus Trap Management</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[webComponentsOutputMode]" value="1" ' . checked(!empty($settings['webComponentsOutputMode']), true, false) . '><span>Web Components Output Mode</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[moduleFederationRuntime]" value="1" ' . checked(!empty($settings['moduleFederationRuntime']), true, false) . '><span>Module Federation Runtime</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[e2eTestSuite]" value="1" ' . checked(!empty($settings['e2eTestSuite']), true, false) . '><span>E2E Test Suite</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[pluginHealthMonitor]" value="1" ' . checked(!empty($settings['pluginHealthMonitor']), true, false) . '><span>Plugin Health Monitor</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[stagingEnvironmentSync]" value="1" ' . checked(!empty($settings['stagingEnvironmentSync']), true, false) . '><span>Staging Environment Sync</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[changeApprovalWorkflow]" value="1" ' . checked(!empty($settings['changeApprovalWorkflow']), true, false) . '><span>Change Approval Workflow</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[auditLog]" value="1" ' . checked(!empty($settings['auditLog']), true, false) . '><span>Audit Log</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[maintenanceModePerSlider]" value="1" ' . checked(!empty($settings['maintenanceModePerSlider']), true, false) . '><span>Maintenance Mode per Slider</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[maintenanceActive]" value="1" ' . checked(!empty($settings['maintenanceActive']), true, false) . '><span>Maintenance Active</span></label>';
        echo '<label class="full"><span>' . esc_html__('Maintenance Message', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_settings[maintenanceMessage]" value="' . esc_attr(isset($settings['maintenanceMessage']) ? $settings['maintenanceMessage'] : '') . '" placeholder="Slider is under maintenance"></label>';
        echo '</div>';
        echo '</details>';

        echo '</div>';
        echo '</div>';

        echo '<div class="sp-slides-wrap">';
        echo '<div class="sp-slides-head"><h4>' . esc_html__('Slides', 'syntekpro-animations') . '</h4><div class="sp-slide-toolbar"><button type="button" class="button" id="sp-bulk-edit">' . esc_html__('Bulk Edit Selected', 'syntekpro-animations') . '</button><button type="button" class="button" id="sp-undo-slide">' . esc_html__('Undo', 'syntekpro-animations') . '</button><button type="button" class="button" id="sp-redo-slide">' . esc_html__('Redo', 'syntekpro-animations') . '</button><button type="button" class="button sp-btn-features" id="sp-open-features-toolbar">' . esc_html__('Features', 'syntekpro-animations') . '</button><button type="button" class="button button-secondary sp-btn-add-slide" id="sp-add-slide">' . esc_html__('Add New Slide', 'syntekpro-animations') . '</button></div></div>';
        echo '<div id="sp-slides-list">';

        foreach ($slides as $index => $slide) {
            $this->render_slide_card($slide, $index);
        }

        echo '</div>';
        echo '</div>';

        // Optional JSON fallback for advanced users
        echo '<details style="margin-top:14px;"><summary><strong>' . esc_html__('Advanced JSON Fallback', 'syntekpro-animations') . '</strong></summary>';
        echo '<p style="margin-top:8px;">' . esc_html__('If visual fields are empty, JSON values can still be saved and parsed.', 'syntekpro-animations') . '</p>';
        echo '<h4>' . esc_html__('Slider Settings JSON', 'syntekpro-animations') . '</h4>';
        echo '<textarea name="sp_slider_settings_json" rows="8" style="width:100%;font-family:monospace;">' . esc_textarea(wp_json_encode($settings, JSON_PRETTY_PRINT)) . '</textarea>';
        echo '<h4 style="margin-top:10px;">' . esc_html__('Slides JSON', 'syntekpro-animations') . '</h4>';
        echo '<textarea name="sp_slider_slides_json" rows="10" style="width:100%;font-family:monospace;">' . esc_textarea(wp_json_encode($slides, JSON_PRETTY_PRINT)) . '</textarea>';
        echo '</details>';

        $revision_history = get_post_meta($post->ID, '_sp_slider_revision_history', true);
        $revision_history = is_array($revision_history) ? $revision_history : array();
        if (!empty($revision_history)) {
            echo '<details style="margin-top:14px;"><summary><strong>' . esc_html__('Revision History with Diff View', 'syntekpro-animations') . '</strong></summary>';
            echo '<p style="margin-top:8px;">' . esc_html__('Compare two revisions and restore manually by copying JSON if needed.', 'syntekpro-animations') . '</p>';
            echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">';
            echo '<label><span>' . esc_html__('Revision A', 'syntekpro-animations') . '</span><select id="sp-rev-a">';
            foreach ($revision_history as $index => $revision) {
                $label = '#' . ($index + 1) . ' - ' . (isset($revision['savedAt']) ? $revision['savedAt'] : 'n/a');
                echo '<option value="' . esc_attr((string) $index) . '">' . esc_html($label) . '</option>';
            }
            echo '</select></label>';
            echo '<label><span>' . esc_html__('Revision B', 'syntekpro-animations') . '</span><select id="sp-rev-b">';
            foreach ($revision_history as $index => $revision) {
                $label = '#' . ($index + 1) . ' - ' . (isset($revision['savedAt']) ? $revision['savedAt'] : 'n/a');
                echo '<option value="' . esc_attr((string) $index) . '">' . esc_html($label) . '</option>';
            }
            echo '</select></label>';
            echo '</div>';
            echo '<button type="button" class="button" id="sp-rev-diff" style="margin-top:8px;">' . esc_html__('Compare Revisions', 'syntekpro-animations') . '</button>';
            echo '<pre id="sp-rev-diff-out" style="margin-top:8px;background:#0f172a;color:#dbeafe;padding:10px;max-height:240px;overflow:auto;">' . esc_html__('Choose revisions and click compare.', 'syntekpro-animations') . '</pre>';
            echo '<script>window.SP_REVISION_HISTORY = ' . wp_json_encode($revision_history) . ';</script>';
            echo '</details>';
        }

        echo '<div class="sp-features-modal" id="sp-features-modal" aria-hidden="true">';
        echo '<div class="sp-features-backdrop" data-close="1"></div>';
        echo '<div class="sp-features-dialog" role="dialog" aria-modal="true" aria-labelledby="sp-features-title">';
        echo '<div class="sp-features-head">';
        echo '<div class="sp-features-head-title"><h4 id="sp-features-title">' . esc_html__('Slider Features Document', 'syntekpro-animations') . '</h4><p>' . esc_html__('Everything available in Slider Studio, organized by capability.', 'syntekpro-animations') . '</p></div>';
        echo '<button type="button" class="sp-features-close" id="sp-close-features" aria-label="' . esc_attr__('Close features modal', 'syntekpro-animations') . '">Close</button>';
        echo '</div>';
        echo '<div class="sp-features-body">';
        echo '<div class="sp-features-card">';
        echo '<div class="sp-features-card-head"><span class="sp-feature-pill">AI</span><h5>' . esc_html__('AI-Powered Features', 'syntekpro-animations') . '</h5></div>';
        echo '<ul><li>AI slide generation from prompts</li><li>Smart crop focal suggestions by breakpoint</li><li>Auto-contrast readability guidance</li><li>Copy alternatives by tone</li><li>Animation timing prediction</li></ul>';
        echo '</div>';
        echo '<div class="sp-features-card">';
        echo '<div class="sp-features-card-head"><span class="sp-feature-pill">Performance</span><h5>' . esc_html__('Performance Features', 'syntekpro-animations') . '</h5></div>';
        echo '<ul><li>Critical CSS extraction path</li><li>Adaptive video loading by connection quality</li><li>Edge cache snapshot generation</li><li>Core Web Vitals dashboard and beacons</li><li>WebP and AVIF conversion hooks</li></ul>';
        echo '</div>';
        echo '<div class="sp-features-card">';
        echo '<div class="sp-features-card-head"><span class="sp-feature-pill">Editor</span><h5>' . esc_html__('Editor and UX Features', 'syntekpro-animations') . '</h5></div>';
        echo '<ul><li>Bulk edit selected slides</li><li>Global design tokens</li><li>Revision history with diff view</li><li>Collaborative presence endpoints</li><li>Motion path data support</li><li>A/B foundations and traffic split</li></ul>';
        echo '</div>';
        echo '<div class="sp-features-card">';
        echo '<div class="sp-features-card-head"><span class="sp-feature-pill">Integrations</span><h5>' . esc_html__('Integrations and Monetization', 'syntekpro-animations') . '</h5></div>';
        echo '<ul><li>REST API and webhook automation</li><li>Headless JSON output</li><li>Figma import adapter endpoint</li><li>GSAP and Lottie-ready layer architecture</li><li>Zapier and Make webhook flow</li><li>White label, marketplace, cloud sync, and conversion goal settings</li></ul>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '</div>';

        ?>
        <style>
            .sp-slider-editor { display: grid; gap: 14px; }
            .sp-slider-hero { background: radial-gradient(circle at top right,#0ea5e9 0%,#1d4ed8 50%,#0f172a 100%); border-radius: 12px; color: #fff; border: 1px solid #1e3a8a; padding: 16px 18px; display: flex; justify-content: space-between; gap: 14px; align-items: center; }
            .sp-slider-hero h3 { margin: 0 0 6px 0; color: #fff; font-size: 20px; }
            .sp-slider-hero p { margin: 0; color: rgba(255,255,255,.9); font-size: 13px; max-width: 760px; }
            .sp-slider-hero-actions { display: flex; gap: 8px; align-items: center; }
            .sp-slider-settings, .sp-slides-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
            .sp-slider-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
            .sp-slider-grid label span { display: block; font-weight: 600; margin-bottom: 4px; }
            .sp-slider-grid input[type="number"], .sp-slider-grid select, .sp-slide-card input[type="text"], .sp-slide-card input[type="url"], .sp-slide-card textarea { width: 100%; }
            .sp-check { display: flex; align-items: center; gap: 8px; padding-top: 24px; }
            .sp-slides-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
            .sp-slide-toolbar { display: flex; align-items: center; gap: 6px; }
            .sp-btn-add-slide { background: linear-gradient(135deg,#0ea5e9,#2563eb) !important; color: #fff !important; border: none !important; border-radius: 8px !important; font-weight: 700 !important; padding: 0 14px !important; box-shadow: 0 8px 20px rgba(37,99,235,.24); }
            .sp-btn-add-slide:hover { filter: brightness(1.03); transform: translateY(-1px); }
            .sp-btn-features { border-radius: 8px !important; border-color: #93c5fd !important; color: #1d4ed8 !important; background: #eff6ff !important; font-weight: 600 !important; }
            .sp-slide-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; margin-bottom: 10px; background: #f8fafc; }
            .sp-slide-card.is-disabled { opacity: .72; border-style: dashed; }
            .sp-slide-card h5 { margin: 0 0 8px 0; }
            .sp-slide-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
            .sp-slide-grid .full { grid-column: 1 / -1; }
            .sp-layer-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; margin-top: 6px; }
            .sp-layer-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 4px; }
            .sp-layer-head span { font-weight: 600; }
            .sp-layer-actions { display: flex; align-items: center; gap: 6px; }
            .sp-layer-help { margin: 4px 0 0; color: #475569; font-size: 12px; }
            .sp-layer-order-list { list-style: none; margin: 8px 0 0; padding: 0; display: grid; gap: 6px; }
            .sp-layer-order-item { background: #fff; border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 10px; cursor: move; display: flex; align-items: center; gap: 8px; }
            .sp-layer-order-item.dragging { opacity: 0.55; }
            .sp-layer-order-handle { color: #64748b; font-size: 12px; }
            .sp-image-row { display: flex; align-items: center; gap: 8px; }
            .sp-image-preview { width: 64px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #d1d5db; background: #fff; }
            .sp-slide-actions { margin-top: 8px; text-align: right; }
            .sp-slide-actions .button { margin-left: 6px; }
            .sp-live-preview { margin-top: 10px; border: 1px solid #dbeafe; border-radius: 8px; background: linear-gradient(135deg,#0f172a,#1f2937); color:#fff; padding: 10px; overflow: hidden; }
            .sp-live-preview-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
            .sp-live-preview-head strong { font-size: 12px; letter-spacing: .3px; }
            .sp-live-preview-canvas { min-height: 150px; border-radius: 6px; position: relative; padding: 14px; background: rgba(255,255,255,0.04); }
            .sp-preview-layer { opacity: 0; transition-property: opacity, transform, filter; transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1); will-change: transform, opacity; }
            .sp-preview-layer.is-active { opacity: 1; transform: translate3d(0,0,0) scale(1); filter: blur(0); }
            .sp-preview-layer-badge { display:inline-block; background: rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.28); border-radius:999px; padding:3px 8px; font-size:11px; font-weight:700; margin-bottom:8px; }
            .sp-preview-layer-title { font-size: 20px; font-weight: 700; margin: 0 0 8px 0; }
            .sp-preview-layer-description { margin: 0 0 8px 0; opacity: .9; }
            .sp-preview-layer-button { display:inline-block; background:#fff; color:#0f172a; font-weight:600; border-radius:6px; padding:6px 10px; text-decoration:none; }
            .sp-preview-layer-caption { display:block; margin-top:8px; font-size:12px; opacity:.9; }
            .sp-preview-in-none { opacity: 1; }
            .sp-preview-in-fade-up { transform: translate3d(0,16px,0); }
            .sp-preview-in-fade-down { transform: translate3d(0,-16px,0); }
            .sp-preview-in-fade-left { transform: translate3d(18px,0,0); }
            .sp-preview-in-fade-right { transform: translate3d(-18px,0,0); }
            .sp-preview-in-zoom-in { transform: scale(.92); filter: blur(2px); }
            .sp-preview-in-zoom-out { transform: scale(1.08); filter: blur(2px); }
            .sp-features-modal { position: fixed; inset: 0; z-index: 100000; display: none; }
            .sp-features-modal.is-open { display: block; }
            .sp-features-backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.5); }
            .sp-features-dialog { position: relative; max-width: 860px; margin: 7vh auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #dbeafe; box-shadow: 0 18px 48px rgba(15,23,42,.28); }
            .sp-features-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 14px 16px; background: linear-gradient(135deg,#dbeafe,#eff6ff); border-bottom: 1px solid #bfdbfe; }
            .sp-features-head-title h4 { margin: 0; color: #0f172a; }
            .sp-features-head-title p { margin: 3px 0 0; color: #475569; font-size: 12px; }
            .sp-features-close { border: 1px solid #93c5fd; background: #fff; color: #1d4ed8; border-radius: 8px; padding: 6px 10px; font-weight: 600; cursor: pointer; }
            .sp-features-close:hover { background: #eff6ff; }
            .sp-features-body { padding: 14px 16px; max-height: 68vh; overflow-y: auto; display: grid; gap: 10px; background: #f8fafc; }
            .sp-features-card { border: 1px solid #dbeafe; background: #fff; border-radius: 10px; padding: 10px 12px; box-shadow: 0 3px 12px rgba(15,23,42,.05); }
            .sp-features-card-head { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 6px; }
            .sp-features-card-head h5 { margin: 0; color: #1e293b; }
            .sp-feature-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: 2px 8px; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase; }
            .sp-features-card ul { margin: 0; padding-left: 18px; color: #334155; line-height: 1.5; }
            @media (max-width: 920px) { .sp-slider-hero { flex-direction: column; align-items: flex-start; } .sp-features-dialog { margin: 3vh 12px; } }
        </style>
        <script>
            (function() {
                const list = document.getElementById('sp-slides-list');
                const addBtn = document.getElementById('sp-add-slide');
                const featuresBtn = document.getElementById('sp-open-features');
                const featuresBtnToolbar = document.getElementById('sp-open-features-toolbar');
                const featuresModal = document.getElementById('sp-features-modal');
                const closeFeaturesBtn = document.getElementById('sp-close-features');
                const bulkBtn = document.getElementById('sp-bulk-edit');
                const undoBtn = document.getElementById('sp-undo-slide');
                const redoBtn = document.getElementById('sp-redo-slide');
                const revDiffBtn = document.getElementById('sp-rev-diff');
                const revA = document.getElementById('sp-rev-a');
                const revB = document.getElementById('sp-rev-b');
                const revOut = document.getElementById('sp-rev-diff-out');
                if (!list || !addBtn) return;

                const history = [];
                const redoStack = [];
                const MAX_HISTORY = 40;

                function updateUndoRedoButtons() {
                    if (undoBtn) undoBtn.disabled = history.length <= 1;
                    if (redoBtn) redoBtn.disabled = redoStack.length === 0;
                }

                function snapshot() {
                    return list.innerHTML;
                }

                function restoreState(markup) {
                    if (!markup) return;
                    list.innerHTML = markup;
                    list.querySelectorAll('.sp-slide-card').forEach(bindCardEvents);
                    reindex();
                    updateUndoRedoButtons();
                }

                function pushHistory() {
                    const state = snapshot();
                    if (history.length && history[history.length - 1] === state) {
                        return;
                    }
                    history.push(state);
                    if (history.length > MAX_HISTORY) {
                        history.shift();
                    }
                    redoStack.length = 0;
                    updateUndoRedoButtons();
                }

                const animationOptions = [
                    { value: 'none', label: 'No Animation', hint: 'Layer appears instantly with no movement.' },
                    { value: 'fade-up', label: 'Fade Up', hint: 'Layer rises upward while fading in.' },
                    { value: 'fade-down', label: 'Fade Down', hint: 'Layer drops downward while fading in.' },
                    { value: 'fade-left', label: 'Fade Left', hint: 'Layer moves left while fading in.' },
                    { value: 'fade-right', label: 'Fade Right', hint: 'Layer moves right while fading in.' },
                    { value: 'zoom-in', label: 'Zoom In', hint: 'Layer scales up into view.' },
                    { value: 'zoom-out', label: 'Zoom Out', hint: 'Layer scales down into view.' }
                ];

                function getOptionValueList() {
                    return animationOptions.map((opt) => opt.value);
                }

                function toSelectInput(input) {
                    if (!input || input.tagName !== 'INPUT') return input;
                    const name = input.getAttribute('name') || '';
                    if (!name.includes('Anim]') && !name.includes('AnimOut]')) return input;

                    const value = (input.value || '').trim() || 'fade-up';
                    const select = document.createElement('select');
                    select.name = input.name;
                    select.className = input.className;

                    animationOptions.forEach((opt) => {
                        const option = document.createElement('option');
                        option.value = opt.value;
                        option.textContent = opt.label;
                        option.title = opt.hint;
                        if (opt.value === value) option.selected = true;
                        select.appendChild(option);
                    });

                    if (!getOptionValueList().includes(value)) {
                        const custom = document.createElement('option');
                        custom.value = value;
                        custom.textContent = value + ' (Custom)';
                        custom.selected = true;
                        select.appendChild(custom);
                    }

                    input.replaceWith(select);
                    return select;
                }

                function ensureLivePreview(card) {
                    let wrap = card.querySelector('.sp-live-preview');
                    if (wrap) return wrap;

                    wrap = document.createElement('div');
                    wrap.className = 'sp-live-preview full';
                    wrap.innerHTML = `
                        <div class="sp-live-preview-head">
                            <strong>Live Layer Preview</strong>
                            <button type="button" class="button button-small sp-preview-replay">Replay</button>
                        </div>
                        <div class="sp-live-preview-canvas">
                            <span class="sp-preview-layer sp-preview-layer-badge" data-preview-layer="badge">Badge</span>
                            <h4 class="sp-preview-layer sp-preview-layer-title" data-preview-layer="title">Slide Title</h4>
                            <p class="sp-preview-layer sp-preview-layer-description" data-preview-layer="description">Slide description text for animation timing preview.</p>
                            <a href="#" class="sp-preview-layer sp-preview-layer-button" data-preview-layer="button">Button</a>
                            <span class="sp-preview-layer sp-preview-layer-caption" data-preview-layer="caption">Caption text</span>
                        </div>
                    `;

                    const slideGrid = card.querySelector('.sp-slide-grid');
                    if (slideGrid) {
                        slideGrid.appendChild(wrap);
                    }
                    return wrap;
                }

                function reindex() {
                    const cards = list.querySelectorAll('.sp-slide-card');
                    cards.forEach((card, index) => {
                        card.dataset.index = index;
                        const titleText = card.querySelector('.sp-slide-title-text');
                        if (titleText) {
                            titleText.textContent = 'Slide ' + (index + 1);
                        }
                        card.querySelectorAll('[name]').forEach((input) => {
                            input.name = input.name.replace(/sp_slider_slides\[\d+\]/, 'sp_slider_slides[' + index + ']');
                        });
                    });
                }

                function openFeaturesModal() {
                    if (!featuresModal) return;
                    featuresModal.classList.add('is-open');
                    featuresModal.setAttribute('aria-hidden', 'false');
                }

                function closeFeaturesModal() {
                    if (!featuresModal) return;
                    featuresModal.classList.remove('is-open');
                    featuresModal.setAttribute('aria-hidden', 'true');
                }

                function bindCardEvents(card) {
                    const removeBtn = card.querySelector('.sp-remove-slide');
                    const duplicateBtn = card.querySelector('.sp-duplicate-slide');
                    const moveUpBtn = card.querySelector('.sp-move-slide-up');
                    const moveDownBtn = card.querySelector('.sp-move-slide-down');
                    const toggleEnabledBtn = card.querySelector('.sp-toggle-slide-enabled');
                    const enabledInput = card.querySelector('.sp-slide-enabled-input');
                    const pickBtn = card.querySelector('.sp-select-image');
                    const clearBtn = card.querySelector('.sp-clear-image');
                    const imageInput = card.querySelector('.sp-image-input');
                    const imagePreview = card.querySelector('.sp-image-preview');
                    const layerList = card.querySelector('.sp-layer-order-list');
                    const layerOrderInput = card.querySelector('.sp-layer-order-input');
                    const previewWrap = ensureLivePreview(card);
                    const previewCanvas = previewWrap ? previewWrap.querySelector('.sp-live-preview-canvas') : null;
                    const replayBtn = previewWrap ? previewWrap.querySelector('.sp-preview-replay') : null;
                    const resetTimingsBtn = card.querySelector('.sp-reset-layer-timings');
                    const resetAnimationsBtn = card.querySelector('.sp-reset-layer-animations');

                    card.querySelectorAll('input[type="text"][name*="Anim"], input[type="text"][name*="AnimOut"]').forEach(toSelectInput);

                    function syncEnabledVisual() {
                        const enabled = !enabledInput || enabledInput.value !== '0';
                        card.classList.toggle('is-disabled', !enabled);
                        if (toggleEnabledBtn) {
                            toggleEnabledBtn.textContent = enabled ? 'Hide Slide' : 'Show Slide';
                        }
                    }

                    function getField(namePart) {
                        return card.querySelector('[name*="[' + namePart + ']"]');
                    }

                    function updatePreviewText() {
                        if (!previewCanvas) return;
                        const mapping = {
                            badge: (getField('badge') && getField('badge').value) || 'Badge',
                            title: (getField('title') && getField('title').value) || 'Slide Title',
                            description: (getField('description') && getField('description').value) || 'Slide description text for animation timing preview.',
                            button: (getField('buttonText') && getField('buttonText').value) || 'Button',
                            caption: (getField('caption') && getField('caption').value) || 'Caption text'
                        };

                        Object.keys(mapping).forEach((key) => {
                            const el = previewCanvas.querySelector('[data-preview-layer="' + key + '"]');
                            if (el) {
                                el.textContent = mapping[key];
                            }
                        });
                    }

                    function setFieldValue(namePart, value) {
                        const field = getField(namePart);
                        if (field) {
                            field.value = value;
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                            field.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }

                    function resetLayerTimings() {
                        setFieldValue('layerDuration', '720');
                        setFieldValue('layerStagger', '70');
                        setFieldValue('badgeDelay', '0');
                        setFieldValue('titleDelay', '80');
                        setFieldValue('descDelay', '180');
                        setFieldValue('buttonDelay', '300');
                        setFieldValue('captionDelay', '360');
                        runPreview();
                    }

                    function resetLayerAnimations() {
                        setFieldValue('badgeAnim', 'fade-down');
                        setFieldValue('badgeAnimOut', 'fade-up');
                        setFieldValue('titleAnim', 'fade-up');
                        setFieldValue('titleAnimOut', 'fade-down');
                        setFieldValue('descAnim', 'fade-up');
                        setFieldValue('descAnimOut', 'fade-down');
                        setFieldValue('buttonAnim', 'zoom-in');
                        setFieldValue('buttonAnimOut', 'zoom-out');
                        setFieldValue('captionAnim', 'fade-up');
                        setFieldValue('captionAnimOut', 'fade-down');
                        runPreview();
                    }

                    function runPreview() {
                        if (!previewCanvas) return;

                        const layerOrder = (layerOrderInput && layerOrderInput.value ? layerOrderInput.value : 'badge,title,description,button,caption')
                            .split(',')
                            .map((s) => s.trim())
                            .filter(Boolean);
                        const duration = parseInt((getField('layerDuration') && getField('layerDuration').value) || '720', 10) || 720;
                        const stagger = parseInt((getField('layerStagger') && getField('layerStagger').value) || '70', 10) || 70;

                        layerOrder.forEach((key, idx) => {
                            const el = previewCanvas.querySelector('[data-preview-layer="' + key + '"]');
                            if (!el) return;
                            const animField = getField(key + 'Anim');
                            const delayField = getField(key + 'Delay');
                            const anim = (animField && animField.value) ? animField.value : 'fade-up';
                            const delay = parseInt((delayField && delayField.value) || '0', 10) || 0;
                            const totalDelay = Math.max(0, delay + (idx * stagger));

                            el.className = el.className
                                .replace(/\bsp-preview-in-[^\s]+/g, '')
                                .replace(/\bis-active\b/g, '')
                                .trim();
                            el.classList.add('sp-preview-in-' + anim);
                            el.style.transitionDuration = duration + 'ms';
                            el.style.transitionDelay = totalDelay + 'ms';

                            requestAnimationFrame(() => {
                                el.classList.add('is-active');
                            });
                        });
                    }

                    function syncLayerOrder() {
                        if (!layerList || !layerOrderInput) return;
                        const keys = Array.from(layerList.querySelectorAll('.sp-layer-order-item')).map((item) => item.dataset.layerKey).filter(Boolean);
                        layerOrderInput.value = keys.join(',');
                        runPreview();
                    }

                    function bindLayerDnD() {
                        if (!layerList) return;
                        let dragEl = null;
                        layerList.querySelectorAll('.sp-layer-order-item').forEach((item) => {
                            item.addEventListener('dragstart', () => {
                                dragEl = item;
                                item.classList.add('dragging');
                            });
                            item.addEventListener('dragend', () => {
                                item.classList.remove('dragging');
                                dragEl = null;
                                syncLayerOrder();
                            });
                            item.addEventListener('dragover', (event) => {
                                event.preventDefault();
                            });
                            item.addEventListener('drop', (event) => {
                                event.preventDefault();
                                if (!dragEl || dragEl === item) return;
                                const rect = item.getBoundingClientRect();
                                const before = event.clientY < rect.top + (rect.height / 2);
                                if (before) {
                                    layerList.insertBefore(dragEl, item);
                                } else {
                                    layerList.insertBefore(dragEl, item.nextSibling);
                                }
                                syncLayerOrder();
                            });
                        });
                    }

                    if (removeBtn) {
                        removeBtn.addEventListener('click', function() {
                            card.remove();
                            reindex();
                            pushHistory();
                        });
                    }

                    if (duplicateBtn) {
                        duplicateBtn.addEventListener('click', function() {
                            const clone = card.cloneNode(true);
                            card.insertAdjacentElement('afterend', clone);
                            bindCardEvents(clone);
                            reindex();
                            pushHistory();
                        });
                    }

                    if (moveUpBtn) {
                        moveUpBtn.addEventListener('click', function() {
                            const prev = card.previousElementSibling;
                            if (!prev) return;
                            list.insertBefore(card, prev);
                            reindex();
                            pushHistory();
                        });
                    }

                    if (moveDownBtn) {
                        moveDownBtn.addEventListener('click', function() {
                            const next = card.nextElementSibling;
                            if (!next) return;
                            list.insertBefore(next, card);
                            reindex();
                            pushHistory();
                        });
                    }

                    if (toggleEnabledBtn && enabledInput) {
                        toggleEnabledBtn.addEventListener('click', function() {
                            enabledInput.value = enabledInput.value === '0' ? '1' : '0';
                            syncEnabledVisual();
                            pushHistory();
                        });
                    }

                    if (pickBtn && typeof wp !== 'undefined' && wp.media) {
                        pickBtn.addEventListener('click', function() {
                            const frame = wp.media({
                                title: 'Select slide background',
                                button: { text: 'Use Image' },
                                multiple: false
                            });
                            frame.on('select', function() {
                                const attachment = frame.state().get('selection').first().toJSON();
                                imageInput.value = attachment.url || '';
                                imagePreview.src = attachment.url || '';
                            });
                            frame.open();
                        });
                    }

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function() {
                            imageInput.value = '';
                            imagePreview.src = '';
                        });
                    }

                    if (replayBtn) {
                        replayBtn.addEventListener('click', runPreview);
                    }

                    if (resetTimingsBtn) {
                        resetTimingsBtn.addEventListener('click', resetLayerTimings);
                    }

                    if (resetAnimationsBtn) {
                        resetAnimationsBtn.addEventListener('click', resetLayerAnimations);
                    }

                    card.querySelectorAll('input, textarea, select').forEach((el) => {
                        el.addEventListener('input', function() {
                            updatePreviewText();
                            runPreview();
                        });
                        el.addEventListener('change', function() {
                            updatePreviewText();
                            runPreview();
                            pushHistory();
                        });
                    });

                    bindLayerDnD();
                    syncLayerOrder();
                    updatePreviewText();
                    runPreview();
                    syncEnabledVisual();
                }

                function addSlide() {
                    const index = list.querySelectorAll('.sp-slide-card').length;
                    const html = `
                        <div class="sp-slide-card" data-index="${index}">
                            <h5 class="sp-slide-title"><span class="sp-slide-title-text">Slide ${index + 1}</span> <label style="margin-left:8px;font-weight:400;font-size:12px;"><input type="checkbox" class="sp-slide-select"> Select</label></h5>
                            <div class="sp-slide-grid">
                                <label class="full"><span>Title</span><input type="text" name="sp_slider_slides[${index}][title]" value=""></label>
                                <input type="hidden" class="sp-slide-enabled-input" name="sp_slider_slides[${index}][enabled]" value="1">
                                <label><span>Badge</span><input type="text" name="sp_slider_slides[${index}][badge]" value=""></label>
                                <label><span>Caption</span><input type="text" name="sp_slider_slides[${index}][caption]" value=""></label>
                                <label class="full"><span>Description</span><textarea rows="3" name="sp_slider_slides[${index}][description]"></textarea></label>
                                <label><span>Button Text</span><input type="text" name="sp_slider_slides[${index}][buttonText]" value=""></label>
                                <label><span>Button URL</span><input type="url" name="sp_slider_slides[${index}][buttonUrl]" value="#"></label>
                                <label class="full"><span>Background Image</span>
                                    <div class="sp-image-row">
                                        <img class="sp-image-preview" src="" alt="">
                                        <input class="sp-image-input" type="url" name="sp_slider_slides[${index}][backgroundImage]" value="" placeholder="https://...">
                                        <button type="button" class="button sp-select-image">Select</button>
                                        <button type="button" class="button sp-clear-image">Clear</button>
                                    </div>
                                </label>
                                <label><span>Publish Start</span><input type="datetime-local" name="sp_slider_slides[${index}][publishStart]" value=""></label>
                                <label><span>Publish End</span><input type="datetime-local" name="sp_slider_slides[${index}][publishEnd]" value=""></label>
                                <label><span>Audience</span><select name="sp_slider_slides[${index}][audience]"><option value="all">All</option><option value="guest">Guest Only</option><option value="logged-in">Logged-in Only</option></select></label>
                                <label><span>Roles (csv)</span><input type="text" name="sp_slider_slides[${index}][roles]" value="" placeholder="subscriber,customer"></label>
                                <label><span>Geo Allow (csv)</span><input type="text" name="sp_slider_slides[${index}][geoAllow]" value="" placeholder="US,CA,GB"></label>
                                <label><span>Countdown End</span><input type="datetime-local" name="sp_slider_slides[${index}][countdownEnd]" value=""></label>
                                <label><span>Live Endpoint</span><input type="url" name="sp_slider_slides[${index}][liveEndpoint]" value="" placeholder="https://api.example.com/metrics"></label>
                                <label><span>Live Key</span><input type="text" name="sp_slider_slides[${index}][liveKey]" value="value"></label>
                                <label><span>Layer Duration (ms)</span><input type="number" min="100" max="3000" step="10" name="sp_slider_slides[${index}][layerDuration]" value="720"></label>
                                <label><span>Layer Stagger (ms)</span><input type="number" min="0" max="1000" step="10" name="sp_slider_slides[${index}][layerStagger]" value="70"></label>
                                <label><span>Ken Burns Duration (ms)</span><input type="number" min="2000" max="30000" step="100" name="sp_slider_slides[${index}][kenBurnsDuration]" value="9000"></label>
                                <label><span>Ken Burns Scale Start</span><input type="number" min="1" max="1.8" step="0.01" name="sp_slider_slides[${index}][kenBurnsScaleStart]" value="1.06"></label>
                                <label><span>Ken Burns Scale End</span><input type="number" min="1" max="2.2" step="0.01" name="sp_slider_slides[${index}][kenBurnsScaleEnd]" value="1.16"></label>
                                <label><span>Ken Burns Direction</span><select name="sp_slider_slides[${index}][kenBurnsDirection]"><option value="left-to-right">Left to Right</option><option value="right-to-left">Right to Left</option><option value="top-to-bottom">Top to Bottom</option><option value="bottom-to-top">Bottom to Top</option><option value="center">Center</option></select></label>
                                <label class="full"><span><input type="checkbox" name="sp_slider_slides[${index}][kenBurns]" value="1"> Enable Ken Burns on this slide</span></label>
                                <div class="full">
                                    <div class="sp-layer-head">
                                        <span>Layer Entrances</span>
                                        <div class="sp-layer-actions">
                                            <button type="button" class="button button-small sp-reset-layer-animations">Reset Animations</button>
                                            <button type="button" class="button button-small sp-reset-layer-timings">Reset Timings</button>
                                        </div>
                                    </div>
                                    <p class="sp-layer-help">Friendly animation labels are shown in dropdowns. Hover each option to see motion intent.</p>
                                    <div class="sp-layer-grid">
                                        <strong>Layer</strong><strong>In</strong><strong>Out</strong><strong>Delay (ms)</strong>
                                        <span>Badge</span><input type="text" name="sp_slider_slides[${index}][badgeAnim]" value="fade-down"><input type="text" name="sp_slider_slides[${index}][badgeAnimOut]" value="fade-up"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[${index}][badgeDelay]" value="0">
                                        <span>Title</span><input type="text" name="sp_slider_slides[${index}][titleAnim]" value="fade-up"><input type="text" name="sp_slider_slides[${index}][titleAnimOut]" value="fade-down"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[${index}][titleDelay]" value="80">
                                        <span>Description</span><input type="text" name="sp_slider_slides[${index}][descAnim]" value="fade-up"><input type="text" name="sp_slider_slides[${index}][descAnimOut]" value="fade-down"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[${index}][descDelay]" value="180">
                                        <span>Button</span><input type="text" name="sp_slider_slides[${index}][buttonAnim]" value="zoom-in"><input type="text" name="sp_slider_slides[${index}][buttonAnimOut]" value="zoom-out"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[${index}][buttonDelay]" value="300">
                                        <span>Caption</span><input type="text" name="sp_slider_slides[${index}][captionAnim]" value="fade-up"><input type="text" name="sp_slider_slides[${index}][captionAnimOut]" value="fade-down"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[${index}][captionDelay]" value="360">
                                    </div>
                                </div>
                                <div class="full">
                                    <span style="display:block;font-weight:600;margin-bottom:4px;">Layer Order (Drag to Reorder)</span>
                                    <input type="hidden" class="sp-layer-order-input" name="sp_slider_slides[${index}][layerOrder]" value="badge,title,description,button,caption">
                                    <ul class="sp-layer-order-list">
                                        <li class="sp-layer-order-item" draggable="true" data-layer-key="badge"><span class="sp-layer-order-handle">↕</span>Badge</li>
                                        <li class="sp-layer-order-item" draggable="true" data-layer-key="title"><span class="sp-layer-order-handle">↕</span>Title</li>
                                        <li class="sp-layer-order-item" draggable="true" data-layer-key="description"><span class="sp-layer-order-handle">↕</span>Description</li>
                                        <li class="sp-layer-order-item" draggable="true" data-layer-key="button"><span class="sp-layer-order-handle">↕</span>Button</li>
                                        <li class="sp-layer-order-item" draggable="true" data-layer-key="caption"><span class="sp-layer-order-handle">↕</span>Caption</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="sp-slide-actions"><button type="button" class="button sp-move-slide-up">Move Up</button><button type="button" class="button sp-move-slide-down">Move Down</button><button type="button" class="button sp-duplicate-slide">Duplicate</button><button type="button" class="button sp-toggle-slide-enabled">Hide Slide</button><button type="button" class="button button-link-delete sp-remove-slide">Remove Slide</button></div>
                        </div>
                    `;
                    list.insertAdjacentHTML('beforeend', html);
                    bindCardEvents(list.lastElementChild);
                    pushHistory();
                }

                list.querySelectorAll('.sp-slide-card').forEach(bindCardEvents);
                addBtn.addEventListener('click', addSlide);
                if (featuresBtn) featuresBtn.addEventListener('click', openFeaturesModal);
                if (featuresBtnToolbar) featuresBtnToolbar.addEventListener('click', openFeaturesModal);
                if (closeFeaturesBtn) closeFeaturesBtn.addEventListener('click', closeFeaturesModal);
                if (featuresModal) {
                    featuresModal.addEventListener('click', function(event) {
                        if (event.target && event.target.hasAttribute('data-close')) {
                            closeFeaturesModal();
                        }
                    });
                }
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') closeFeaturesModal();
                });
                if (bulkBtn) {
                    bulkBtn.addEventListener('click', function() {
                        const selectedCards = Array.from(list.querySelectorAll('.sp-slide-card')).filter((card) => {
                            const checkbox = card.querySelector('.sp-slide-select');
                            return checkbox && checkbox.checked;
                        });

                        if (!selectedCards.length) {
                            window.alert('Select at least one slide for bulk edit.');
                            return;
                        }

                        const field = window.prompt('Field key to update (e.g. titleAnim, titleAnimOut, layerDuration, buttonText):', 'titleAnim');
                        if (!field) return;
                        const value = window.prompt('Set value for ' + field + ':', 'fade-up');
                        if (value === null) return;

                        selectedCards.forEach((card) => {
                            const input = card.querySelector('[name*="[' + field + ']"]');
                            if (!input) return;
                            input.value = value;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        });

                        pushHistory();
                    });
                }
                if (undoBtn) {
                    undoBtn.addEventListener('click', function() {
                        if (history.length <= 1) return;
                        const current = history.pop();
                        redoStack.push(current);
                        restoreState(history[history.length - 1]);
                    });
                }
                if (redoBtn) {
                    redoBtn.addEventListener('click', function() {
                        if (!redoStack.length) return;
                        const state = redoStack.pop();
                        history.push(state);
                        restoreState(state);
                    });
                }
                if (revDiffBtn && revA && revB && revOut && Array.isArray(window.SP_REVISION_HISTORY)) {
                    revDiffBtn.addEventListener('click', function() {
                        const aIndex = parseInt(revA.value || '0', 10);
                        const bIndex = parseInt(revB.value || '0', 10);
                        const a = window.SP_REVISION_HISTORY[aIndex] || {};
                        const b = window.SP_REVISION_HISTORY[bIndex] || {};
                        const aJson = JSON.stringify({ settings: a.settings || {}, slides: a.slides || [] }, null, 2);
                        const bJson = JSON.stringify({ settings: b.settings || {}, slides: b.slides || [] }, null, 2);
                        if (aJson === bJson) {
                            revOut.textContent = 'No differences found.';
                            return;
                        }
                        revOut.textContent = 'Revision A:\n' + aJson + '\n\n---\n\nRevision B:\n' + bJson;
                    });
                }
                pushHistory();
            })();
        </script>
        <?php
    }

    private function render_slide_card($slide, $index) {
        $title = isset($slide['title']) ? $slide['title'] : '';
        $description = isset($slide['description']) ? $slide['description'] : '';
        $badge = isset($slide['badge']) ? $slide['badge'] : '';
        $caption = isset($slide['caption']) ? $slide['caption'] : '';
        $button_text = isset($slide['buttonText']) ? $slide['buttonText'] : '';
        $button_url = isset($slide['buttonUrl']) ? $slide['buttonUrl'] : '#';
        $background = isset($slide['backgroundImage']) ? $slide['backgroundImage'] : '';
        $title_anim = isset($slide['titleAnim']) ? $slide['titleAnim'] : 'fade-up';
        $title_delay = isset($slide['titleDelay']) ? absint($slide['titleDelay']) : 80;
        $desc_anim = isset($slide['descAnim']) ? $slide['descAnim'] : 'fade-up';
        $desc_delay = isset($slide['descDelay']) ? absint($slide['descDelay']) : 180;
        $button_anim = isset($slide['buttonAnim']) ? $slide['buttonAnim'] : 'zoom-in';
        $button_delay = isset($slide['buttonDelay']) ? absint($slide['buttonDelay']) : 300;
        $badge_anim = isset($slide['badgeAnim']) ? $slide['badgeAnim'] : 'fade-down';
        $badge_delay = isset($slide['badgeDelay']) ? absint($slide['badgeDelay']) : 0;
        $caption_anim = isset($slide['captionAnim']) ? $slide['captionAnim'] : 'fade-up';
        $caption_delay = isset($slide['captionDelay']) ? absint($slide['captionDelay']) : 360;
        $title_anim_out = isset($slide['titleAnimOut']) ? $slide['titleAnimOut'] : 'fade-down';
        $desc_anim_out = isset($slide['descAnimOut']) ? $slide['descAnimOut'] : 'fade-down';
        $button_anim_out = isset($slide['buttonAnimOut']) ? $slide['buttonAnimOut'] : 'zoom-out';
        $badge_anim_out = isset($slide['badgeAnimOut']) ? $slide['badgeAnimOut'] : 'fade-up';
        $caption_anim_out = isset($slide['captionAnimOut']) ? $slide['captionAnimOut'] : 'fade-down';
        $layer_duration = isset($slide['layerDuration']) ? absint($slide['layerDuration']) : 720;
        $layer_stagger = isset($slide['layerStagger']) ? absint($slide['layerStagger']) : 70;
        $layer_order = implode(',', $this->normalize_layer_order(isset($slide['layerOrder']) ? $slide['layerOrder'] : ''));
        $ken_burns = !empty($slide['kenBurns']);
        $ken_burns_scale_start = isset($slide['kenBurnsScaleStart']) ? (float) $slide['kenBurnsScaleStart'] : 1.06;
        $ken_burns_scale_end = isset($slide['kenBurnsScaleEnd']) ? (float) $slide['kenBurnsScaleEnd'] : 1.16;
        $ken_burns_duration = isset($slide['kenBurnsDuration']) ? absint($slide['kenBurnsDuration']) : 9000;
        $ken_burns_direction = isset($slide['kenBurnsDirection']) ? $slide['kenBurnsDirection'] : 'left-to-right';
        $enabled = !isset($slide['enabled']) || !empty($slide['enabled']);
        $publish_start = isset($slide['publishStart']) ? sanitize_text_field($slide['publishStart']) : '';
        $publish_end = isset($slide['publishEnd']) ? sanitize_text_field($slide['publishEnd']) : '';
        $publish_start_attr = $publish_start !== '' ? str_replace(' ', 'T', substr($publish_start, 0, 16)) : '';
        $publish_end_attr = $publish_end !== '' ? str_replace(' ', 'T', substr($publish_end, 0, 16)) : '';
        $audience = isset($slide['audience']) ? sanitize_key($slide['audience']) : 'all';
        $roles = isset($slide['roles']) ? sanitize_text_field($slide['roles']) : '';
        $geo_allow = isset($slide['geoAllow']) ? sanitize_text_field($slide['geoAllow']) : '';
        $countdown_end = isset($slide['countdownEnd']) ? sanitize_text_field($slide['countdownEnd']) : '';
        $live_endpoint = isset($slide['liveEndpoint']) ? esc_url($slide['liveEndpoint']) : '';
        $live_key = isset($slide['liveKey']) ? sanitize_key($slide['liveKey']) : 'value';

        echo '<div class="sp-slide-card' . ($enabled ? '' : ' is-disabled') . '" data-index="' . esc_attr((string) $index) . '">';
        echo '<h5 class="sp-slide-title"><span class="sp-slide-title-text">' . esc_html(sprintf(__('Slide %d', 'syntekpro-animations'), $index + 1)) . '</span> <label style="margin-left:8px;font-weight:400;font-size:12px;"><input type="checkbox" class="sp-slide-select"> ' . esc_html__('Select', 'syntekpro-animations') . '</label></h5>';
        echo '<div class="sp-slide-grid">';
        echo '<input type="hidden" class="sp-slide-enabled-input" name="sp_slider_slides[' . esc_attr((string) $index) . '][enabled]" value="' . ($enabled ? '1' : '0') . '">';

        echo '<label class="full"><span>' . esc_html__('Title', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][title]" value="' . esc_attr($title) . '"></label>';
        echo '<label><span>' . esc_html__('Badge', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][badge]" value="' . esc_attr($badge) . '"></label>';
        echo '<label><span>' . esc_html__('Caption', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][caption]" value="' . esc_attr($caption) . '"></label>';
        echo '<label class="full"><span>' . esc_html__('Description', 'syntekpro-animations') . '</span><textarea rows="3" name="sp_slider_slides[' . esc_attr((string) $index) . '][description]">' . esc_textarea($description) . '</textarea></label>';
        echo '<label><span>' . esc_html__('Button Text', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][buttonText]" value="' . esc_attr($button_text) . '"></label>';
        echo '<label><span>' . esc_html__('Button URL', 'syntekpro-animations') . '</span><input type="url" name="sp_slider_slides[' . esc_attr((string) $index) . '][buttonUrl]" value="' . esc_attr($button_url) . '"></label>';

        echo '<label class="full"><span>' . esc_html__('Background Image', 'syntekpro-animations') . '</span>';
        echo '<div class="sp-image-row">';
        echo '<img class="sp-image-preview" src="' . esc_url($background) . '" alt="">';
        echo '<input class="sp-image-input" type="url" name="sp_slider_slides[' . esc_attr((string) $index) . '][backgroundImage]" value="' . esc_attr($background) . '" placeholder="https://...">';
        echo '<button type="button" class="button sp-select-image">' . esc_html__('Select', 'syntekpro-animations') . '</button>';
        echo '<button type="button" class="button sp-clear-image">' . esc_html__('Clear', 'syntekpro-animations') . '</button>';
        echo '</div>';
        echo '</label>';

        echo '<label><span>' . esc_html__('Publish Start', 'syntekpro-animations') . '</span><input type="datetime-local" name="sp_slider_slides[' . esc_attr((string) $index) . '][publishStart]" value="' . esc_attr($publish_start_attr) . '"></label>';
        echo '<label><span>' . esc_html__('Publish End', 'syntekpro-animations') . '</span><input type="datetime-local" name="sp_slider_slides[' . esc_attr((string) $index) . '][publishEnd]" value="' . esc_attr($publish_end_attr) . '"></label>';
        echo '<label><span>' . esc_html__('Audience', 'syntekpro-animations') . '</span><select name="sp_slider_slides[' . esc_attr((string) $index) . '][audience]"><option value="all" ' . selected($audience, 'all', false) . '>All</option><option value="guest" ' . selected($audience, 'guest', false) . '>Guest Only</option><option value="logged-in" ' . selected($audience, 'logged-in', false) . '>Logged-in Only</option></select></label>';
        echo '<label><span>' . esc_html__('Roles (csv)', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][roles]" value="' . esc_attr($roles) . '" placeholder="subscriber,customer"></label>';
        echo '<label><span>' . esc_html__('Geo Allow (csv)', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][geoAllow]" value="' . esc_attr($geo_allow) . '" placeholder="US,CA,GB"></label>';
        echo '<label><span>' . esc_html__('Countdown End', 'syntekpro-animations') . '</span><input type="datetime-local" name="sp_slider_slides[' . esc_attr((string) $index) . '][countdownEnd]" value="' . esc_attr($countdown_end) . '"></label>';
        echo '<label><span>' . esc_html__('Live Endpoint', 'syntekpro-animations') . '</span><input type="url" name="sp_slider_slides[' . esc_attr((string) $index) . '][liveEndpoint]" value="' . esc_attr($live_endpoint) . '" placeholder="https://api.example.com/metrics"></label>';
        echo '<label><span>' . esc_html__('Live Key', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][liveKey]" value="' . esc_attr($live_key) . '"></label>';

        echo '<label><span>' . esc_html__('Layer Duration (ms)', 'syntekpro-animations') . '</span><input type="number" min="100" max="3000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][layerDuration]" value="' . esc_attr((string) $layer_duration) . '"></label>';
        echo '<label><span>' . esc_html__('Layer Stagger (ms)', 'syntekpro-animations') . '</span><input type="number" min="0" max="1000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][layerStagger]" value="' . esc_attr((string) $layer_stagger) . '"></label>';
        echo '<label><span>' . esc_html__('Ken Burns Duration (ms)', 'syntekpro-animations') . '</span><input type="number" min="2000" max="30000" step="100" name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurnsDuration]" value="' . esc_attr((string) $ken_burns_duration) . '"></label>';
        echo '<label><span>' . esc_html__('Ken Burns Scale Start', 'syntekpro-animations') . '</span><input type="number" min="1" max="1.8" step="0.01" name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurnsScaleStart]" value="' . esc_attr((string) $ken_burns_scale_start) . '"></label>';
        echo '<label><span>' . esc_html__('Ken Burns Scale End', 'syntekpro-animations') . '</span><input type="number" min="1" max="2.2" step="0.01" name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurnsScaleEnd]" value="' . esc_attr((string) $ken_burns_scale_end) . '"></label>';
        echo '<label><span>' . esc_html__('Ken Burns Direction', 'syntekpro-animations') . '</span><select name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurnsDirection]"><option value="left-to-right" ' . selected($ken_burns_direction, 'left-to-right', false) . '>Left to Right</option><option value="right-to-left" ' . selected($ken_burns_direction, 'right-to-left', false) . '>Right to Left</option><option value="top-to-bottom" ' . selected($ken_burns_direction, 'top-to-bottom', false) . '>Top to Bottom</option><option value="bottom-to-top" ' . selected($ken_burns_direction, 'bottom-to-top', false) . '>Bottom to Top</option><option value="center" ' . selected($ken_burns_direction, 'center', false) . '>Center</option></select></label>';
        echo '<label class="full"><span><input type="checkbox" name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurns]" value="1" ' . checked($ken_burns, true, false) . '> ' . esc_html__('Enable Ken Burns on this slide', 'syntekpro-animations') . '</span></label>';

        echo '<div class="full">';
        echo '<div class="sp-layer-head"><span>' . esc_html__('Layer Entrances', 'syntekpro-animations') . '</span><div class="sp-layer-actions"><button type="button" class="button button-small sp-reset-layer-animations">' . esc_html__('Reset Animations', 'syntekpro-animations') . '</button><button type="button" class="button button-small sp-reset-layer-timings">' . esc_html__('Reset Timings', 'syntekpro-animations') . '</button></div></div>';
        echo '<p class="sp-layer-help">' . esc_html__('Friendly animation labels appear in dropdowns. Hover each option to see the motion intent.', 'syntekpro-animations') . '</p>';
        echo '<div class="sp-layer-grid">';
        echo '<strong>' . esc_html__('Layer', 'syntekpro-animations') . '</strong><strong>' . esc_html__('In', 'syntekpro-animations') . '</strong><strong>' . esc_html__('Out', 'syntekpro-animations') . '</strong><strong>' . esc_html__('Delay (ms)', 'syntekpro-animations') . '</strong>';
        echo '<span>' . esc_html__('Badge', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][badgeAnim]" value="' . esc_attr($badge_anim) . '"><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][badgeAnimOut]" value="' . esc_attr($badge_anim_out) . '"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][badgeDelay]" value="' . esc_attr((string) $badge_delay) . '">';
        echo '<span>' . esc_html__('Title', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][titleAnim]" value="' . esc_attr($title_anim) . '"><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][titleAnimOut]" value="' . esc_attr($title_anim_out) . '"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][titleDelay]" value="' . esc_attr((string) $title_delay) . '">';
        echo '<span>' . esc_html__('Description', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][descAnim]" value="' . esc_attr($desc_anim) . '"><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][descAnimOut]" value="' . esc_attr($desc_anim_out) . '"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][descDelay]" value="' . esc_attr((string) $desc_delay) . '">';
        echo '<span>' . esc_html__('Button', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][buttonAnim]" value="' . esc_attr($button_anim) . '"><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][buttonAnimOut]" value="' . esc_attr($button_anim_out) . '"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][buttonDelay]" value="' . esc_attr((string) $button_delay) . '">';
        echo '<span>' . esc_html__('Caption', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][captionAnim]" value="' . esc_attr($caption_anim) . '"><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][captionAnimOut]" value="' . esc_attr($caption_anim_out) . '"><input type="number" min="0" max="4000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][captionDelay]" value="' . esc_attr((string) $caption_delay) . '">';
        echo '</div>';
        echo '</div>';

        echo '<div class="full">';
        echo '<span style="display:block;font-weight:600;margin-bottom:4px;">' . esc_html__('Layer Order (Drag to Reorder)', 'syntekpro-animations') . '</span>';
        echo '<input type="hidden" class="sp-layer-order-input" name="sp_slider_slides[' . esc_attr((string) $index) . '][layerOrder]" value="' . esc_attr($layer_order) . '">';
        echo '<ul class="sp-layer-order-list">';
        foreach ($this->normalize_layer_order($layer_order) as $layer_key) {
            echo '<li class="sp-layer-order-item" draggable="true" data-layer-key="' . esc_attr($layer_key) . '"><span class="sp-layer-order-handle">↕</span>' . esc_html(ucfirst($layer_key)) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '<div class="sp-slide-actions"><button type="button" class="button sp-move-slide-up">' . esc_html__('Move Up', 'syntekpro-animations') . '</button><button type="button" class="button sp-move-slide-down">' . esc_html__('Move Down', 'syntekpro-animations') . '</button><button type="button" class="button sp-duplicate-slide">' . esc_html__('Duplicate', 'syntekpro-animations') . '</button><button type="button" class="button sp-toggle-slide-enabled">' . esc_html($enabled ? __('Hide Slide', 'syntekpro-animations') : __('Show Slide', 'syntekpro-animations')) . '</button><button type="button" class="button button-link-delete sp-remove-slide">' . esc_html__('Remove Slide', 'syntekpro-animations') . '</button></div>';
        echo '</div>';
    }

    /**
     * Save slider JSON from meta box.
     */
    public function save_slider_meta($post_id) {
        if (!isset($_POST['sp_slider_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sp_slider_meta_nonce'])), 'sp_slider_meta_save')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $settings = array();
        $slides = array();

        if (isset($_POST['sp_slider_settings']) && is_array($_POST['sp_slider_settings'])) {
            $settings = wp_unslash($_POST['sp_slider_settings']);
        } else {
            $settings_json = isset($_POST['sp_slider_settings_json']) ? wp_unslash($_POST['sp_slider_settings_json']) : '{}';
            $decoded_settings = json_decode($settings_json, true);
            if (is_array($decoded_settings)) {
                $settings = $decoded_settings;
            }
        }

        if (isset($_POST['sp_slider_slides']) && is_array($_POST['sp_slider_slides'])) {
            $slides = wp_unslash($_POST['sp_slider_slides']);
        } else {
            $slides_json = isset($_POST['sp_slider_slides_json']) ? wp_unslash($_POST['sp_slider_slides_json']) : '[]';
            $decoded_slides = json_decode($slides_json, true);
            if (is_array($decoded_slides)) {
                $slides = $decoded_slides;
            }
        }

        $safe_settings = array(
            'autoplay' => !empty($settings['autoplay']),
            'autoplayDelay' => isset($settings['autoplayDelay']) ? absint($settings['autoplayDelay']) : 5000,
            'autoplayPauseOnHover' => !empty($settings['autoplayPauseOnHover']),
            'pauseOnInteraction' => !empty($settings['pauseOnInteraction']),
            'loop' => !empty($settings['loop']),
            'navigation' => !empty($settings['navigation']),
            'pagination' => !empty($settings['pagination']),
            'keyboardNav' => !empty($settings['keyboardNav']),
            'swipeNav' => !empty($settings['swipeNav']),
            'progressBar' => !empty($settings['progressBar']),
            'showCounter' => !empty($settings['showCounter']),
            'thumbnails' => !empty($settings['thumbnails']),
            'lazyLoad' => !empty($settings['lazyLoad']),
            'transition' => isset($settings['transition']) ? sanitize_text_field($settings['transition']) : 'slide',
            'transitionSpeed' => isset($settings['transitionSpeed']) ? absint($settings['transitionSpeed']) : 600,
            'easing' => isset($settings['easing']) ? sanitize_text_field($settings['easing']) : 'ease',
            'heightDesktop' => isset($settings['heightDesktop']) ? absint($settings['heightDesktop']) : 460,
            'heightTablet' => isset($settings['heightTablet']) ? absint($settings['heightTablet']) : 400,
            'heightMobile' => isset($settings['heightMobile']) ? absint($settings['heightMobile']) : 320,
            'contentAlign' => isset($settings['contentAlign']) ? sanitize_text_field($settings['contentAlign']) : 'center',
            'overlayStrength' => isset($settings['overlayStrength']) ? absint($settings['overlayStrength']) : 55,
            'sliderWidth' => isset($settings['sliderWidth']) ? absint($settings['sliderWidth']) : 1200,
            'pauseOnFocus' => !empty($settings['pauseOnFocus']),
            'fluidMode' => isset($settings['fluidMode']) ? sanitize_text_field($settings['fluidMode']) : 'auto-scale',
            'swipeSensitivity' => isset($settings['swipeSensitivity']) ? absint($settings['swipeSensitivity']) : 35,
            'swipeDirection' => isset($settings['swipeDirection']) ? sanitize_text_field($settings['swipeDirection']) : 'horizontal',
            'dynamicSource' => isset($settings['dynamicSource']) ? sanitize_text_field($settings['dynamicSource']) : 'manual',
            'dynamicPostType' => isset($settings['dynamicPostType']) ? sanitize_text_field($settings['dynamicPostType']) : 'post',
            'dynamicLimit' => isset($settings['dynamicLimit']) ? absint($settings['dynamicLimit']) : 5,
            'analyticsEnabled' => !empty($settings['analyticsEnabled']),
            'ga4Enabled' => !empty($settings['ga4Enabled']),
            'ga4EventPrefix' => isset($settings['ga4EventPrefix']) ? sanitize_text_field($settings['ga4EventPrefix']) : 'syntekpro_slider',
            'customTransitionCss' => isset($settings['customTransitionCss']) ? sanitize_textarea_field($settings['customTransitionCss']) : '',
        );

        if (!in_array($safe_settings['transition'], array('slide', 'fade', 'zoom', 'crossfade', 'parallax', 'ken-burns', 'cube', 'flip', 'custom-css'), true)) {
            $safe_settings['transition'] = 'slide';
        }
        if (!in_array($safe_settings['contentAlign'], array('left', 'center', 'right'), true)) {
            $safe_settings['contentAlign'] = 'center';
        }
        if (!in_array($safe_settings['fluidMode'], array('auto-scale', 'fixed', 'full-width', 'full-screen', 'aspect-lock'), true)) {
            $safe_settings['fluidMode'] = 'auto-scale';
        }
        if (!in_array($safe_settings['swipeDirection'], array('horizontal', 'vertical', 'both'), true)) {
            $safe_settings['swipeDirection'] = 'horizontal';
        }
        if (!in_array($safe_settings['dynamicSource'], array('manual', 'posts', 'products', 'cpt', 'acf'), true)) {
            $safe_settings['dynamicSource'] = 'manual';
        }
        $safe_settings['swipeSensitivity'] = max(10, min(160, $safe_settings['swipeSensitivity']));
        $safe_settings['dynamicLimit'] = max(1, min(30, $safe_settings['dynamicLimit']));
        $safe_settings['sliderWidth'] = max(320, min(2560, $safe_settings['sliderWidth']));
        $safe_settings['overlayStrength'] = max(0, min(90, $safe_settings['overlayStrength']));

        $safe_slides = array();
        foreach ($slides as $slide) {
            if (!is_array($slide)) {
                continue;
            }
            $safe_slide = array(
                'title' => isset($slide['title']) ? sanitize_text_field($slide['title']) : '',
                'badge' => isset($slide['badge']) ? sanitize_text_field($slide['badge']) : '',
                'caption' => isset($slide['caption']) ? sanitize_text_field($slide['caption']) : '',
                'description' => isset($slide['description']) ? sanitize_textarea_field($slide['description']) : '',
                'buttonText' => isset($slide['buttonText']) ? sanitize_text_field($slide['buttonText']) : '',
                'buttonUrl' => isset($slide['buttonUrl']) ? esc_url_raw($slide['buttonUrl']) : '#',
                'backgroundImage' => isset($slide['backgroundImage']) ? esc_url_raw($slide['backgroundImage']) : '',
                'titleAnim' => isset($slide['titleAnim']) ? sanitize_key($slide['titleAnim']) : 'fade-up',
                'titleDelay' => isset($slide['titleDelay']) ? absint($slide['titleDelay']) : 80,
                'descAnim' => isset($slide['descAnim']) ? sanitize_key($slide['descAnim']) : 'fade-up',
                'descDelay' => isset($slide['descDelay']) ? absint($slide['descDelay']) : 180,
                'buttonAnim' => isset($slide['buttonAnim']) ? sanitize_key($slide['buttonAnim']) : 'zoom-in',
                'buttonDelay' => isset($slide['buttonDelay']) ? absint($slide['buttonDelay']) : 300,
                'badgeAnim' => isset($slide['badgeAnim']) ? sanitize_key($slide['badgeAnim']) : 'fade-down',
                'badgeDelay' => isset($slide['badgeDelay']) ? absint($slide['badgeDelay']) : 0,
                'captionAnim' => isset($slide['captionAnim']) ? sanitize_key($slide['captionAnim']) : 'fade-up',
                'captionDelay' => isset($slide['captionDelay']) ? absint($slide['captionDelay']) : 360,
                'titleAnimOut' => isset($slide['titleAnimOut']) ? sanitize_key($slide['titleAnimOut']) : 'fade-down',
                'descAnimOut' => isset($slide['descAnimOut']) ? sanitize_key($slide['descAnimOut']) : 'fade-down',
                'buttonAnimOut' => isset($slide['buttonAnimOut']) ? sanitize_key($slide['buttonAnimOut']) : 'zoom-out',
                'badgeAnimOut' => isset($slide['badgeAnimOut']) ? sanitize_key($slide['badgeAnimOut']) : 'fade-up',
                'captionAnimOut' => isset($slide['captionAnimOut']) ? sanitize_key($slide['captionAnimOut']) : 'fade-down',
                'titleMotionPath' => isset($slide['titleMotionPath']) ? sanitize_text_field($slide['titleMotionPath']) : '',
                'descMotionPath' => isset($slide['descMotionPath']) ? sanitize_text_field($slide['descMotionPath']) : '',
                'buttonMotionPath' => isset($slide['buttonMotionPath']) ? sanitize_text_field($slide['buttonMotionPath']) : '',
                'badgeMotionPath' => isset($slide['badgeMotionPath']) ? sanitize_text_field($slide['badgeMotionPath']) : '',
                'captionMotionPath' => isset($slide['captionMotionPath']) ? sanitize_text_field($slide['captionMotionPath']) : '',
                'layerDuration' => isset($slide['layerDuration']) ? absint($slide['layerDuration']) : 720,
                'layerStagger' => isset($slide['layerStagger']) ? absint($slide['layerStagger']) : 70,
                'layerOrder' => isset($slide['layerOrder']) ? sanitize_text_field($slide['layerOrder']) : 'badge,title,description,button,caption',
                'kenBurns' => !empty($slide['kenBurns']),
                'kenBurnsScaleStart' => isset($slide['kenBurnsScaleStart']) ? (float) $slide['kenBurnsScaleStart'] : 1.06,
                'kenBurnsScaleEnd' => isset($slide['kenBurnsScaleEnd']) ? (float) $slide['kenBurnsScaleEnd'] : 1.16,
                'kenBurnsDuration' => isset($slide['kenBurnsDuration']) ? absint($slide['kenBurnsDuration']) : 9000,
                'kenBurnsDirection' => isset($slide['kenBurnsDirection']) ? sanitize_key($slide['kenBurnsDirection']) : 'left-to-right',
                'enabled' => !isset($slide['enabled']) || !empty($slide['enabled']),
                'publishStart' => isset($slide['publishStart']) ? sanitize_text_field($slide['publishStart']) : '',
                'publishEnd' => isset($slide['publishEnd']) ? sanitize_text_field($slide['publishEnd']) : '',
                'audience' => isset($slide['audience']) ? sanitize_key($slide['audience']) : 'all',
                'roles' => isset($slide['roles']) ? sanitize_text_field($slide['roles']) : '',
                'geoAllow' => isset($slide['geoAllow']) ? sanitize_text_field($slide['geoAllow']) : '',
                'countdownEnd' => isset($slide['countdownEnd']) ? sanitize_text_field($slide['countdownEnd']) : '',
                'liveEndpoint' => isset($slide['liveEndpoint']) ? esc_url_raw($slide['liveEndpoint']) : '',
                'liveKey' => isset($slide['liveKey']) ? sanitize_key($slide['liveKey']) : 'value',
                'translations' => isset($slide['translations']) && is_array($slide['translations']) ? $slide['translations'] : array(),
            );

            $allowed_layer_anims = array('none', 'fade-up', 'fade-down', 'fade-left', 'fade-right', 'zoom-in', 'zoom-out');
            foreach (array('titleAnim', 'descAnim', 'buttonAnim', 'badgeAnim', 'captionAnim', 'titleAnimOut', 'descAnimOut', 'buttonAnimOut', 'badgeAnimOut', 'captionAnimOut') as $layer_key) {
                if (!in_array($safe_slide[$layer_key], $allowed_layer_anims, true)) {
                    $safe_slide[$layer_key] = 'fade-up';
                }
            }

            $safe_slide['layerDuration'] = max(100, min(3000, $safe_slide['layerDuration']));
            $safe_slide['layerStagger'] = max(0, min(1000, $safe_slide['layerStagger']));
            $safe_slide['titleDelay'] = min(4000, $safe_slide['titleDelay']);
            $safe_slide['descDelay'] = min(4000, $safe_slide['descDelay']);
            $safe_slide['buttonDelay'] = min(4000, $safe_slide['buttonDelay']);
            $safe_slide['badgeDelay'] = min(4000, $safe_slide['badgeDelay']);
            $safe_slide['captionDelay'] = min(4000, $safe_slide['captionDelay']);
            $safe_slide['layerOrder'] = implode(',', $this->normalize_layer_order($safe_slide['layerOrder']));
            $safe_slide['kenBurnsScaleStart'] = max(1, min(1.8, $safe_slide['kenBurnsScaleStart']));
            $safe_slide['kenBurnsScaleEnd'] = max(1, min(2.2, $safe_slide['kenBurnsScaleEnd']));
            $safe_slide['kenBurnsDuration'] = max(2000, min(30000, $safe_slide['kenBurnsDuration']));
            if (!in_array($safe_slide['kenBurnsDirection'], array('left-to-right', 'right-to-left', 'top-to-bottom', 'bottom-to-top', 'center'), true)) {
                $safe_slide['kenBurnsDirection'] = 'left-to-right';
            }

            if ($safe_slide['title'] === '' && $safe_slide['badge'] === '' && $safe_slide['caption'] === '' && $safe_slide['description'] === '' && $safe_slide['buttonText'] === '' && $safe_slide['backgroundImage'] === '') {
                continue;
            }

            if (!empty($safe_slide['translations']) && is_array($safe_slide['translations'])) {
                $clean_translations = array();
                foreach ($safe_slide['translations'] as $locale => $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $locale_key = sanitize_text_field((string) $locale);
                    $clean_translations[$locale_key] = array(
                        'title' => isset($row['title']) ? sanitize_text_field($row['title']) : '',
                        'badge' => isset($row['badge']) ? sanitize_text_field($row['badge']) : '',
                        'caption' => isset($row['caption']) ? sanitize_text_field($row['caption']) : '',
                        'description' => isset($row['description']) ? sanitize_textarea_field($row['description']) : '',
                        'buttonText' => isset($row['buttonText']) ? sanitize_text_field($row['buttonText']) : '',
                    );
                }
                $safe_slide['translations'] = $clean_translations;
            }

            $safe_slides[] = $safe_slide;
        }

        update_post_meta($post_id, '_sp_slider_settings', $safe_settings);
        update_post_meta($post_id, '_sp_slider_slides', $safe_slides);
        update_post_meta($post_id, '_sp_slider_version', '1');
    }
}

new Syntekpro_Slider_Core();
