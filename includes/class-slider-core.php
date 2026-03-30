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
            'data-align' => sanitize_text_field($settings['contentAlign']),
            'style' => '--sp-height-desktop:' . absint($settings['heightDesktop']) . 'px;--sp-height-tablet:' . absint($settings['heightTablet']) . 'px;--sp-height-mobile:' . absint($settings['heightMobile']) . 'px;--sp-overlay-alpha:' . max(0, min(100, absint($settings['overlayStrength']))) / 100 . ';',
            'tabindex' => '0',
        );

        $attrs_html = '';
        foreach ($wrapper_attrs as $k => $v) {
            $attrs_html .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        }

        $html = '<div' . $attrs_html . '>';
        $html .= '<div class="sp-slider-track">';

        foreach ($slides as $slide) {
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
            $ken_burns = !empty($slide['kenBurns']);
            $ken_burns_scale_start = isset($slide['kenBurnsScaleStart']) ? max(1, min(1.8, (float) $slide['kenBurnsScaleStart'])) : 1.06;
            $ken_burns_scale_end = isset($slide['kenBurnsScaleEnd']) ? max(1, min(2.2, (float) $slide['kenBurnsScaleEnd'])) : 1.16;
            $ken_burns_duration = isset($slide['kenBurnsDuration']) ? absint($slide['kenBurnsDuration']) : 9000;
            $ken_burns_direction = isset($slide['kenBurnsDirection']) ? sanitize_key($slide['kenBurnsDirection']) : 'left-to-right';

            $title_anim = $this->sanitize_layer_animation($title_anim, 'fade-up');
            $desc_anim = $this->sanitize_layer_animation($desc_anim, 'fade-up');
            $button_anim = $this->sanitize_layer_animation($button_anim, 'zoom-in');
            $badge_anim = $this->sanitize_layer_animation($badge_anim, 'fade-down');
            $caption_anim = $this->sanitize_layer_animation($caption_anim, 'fade-up');

            $layer_fragments = array();
            if ($badge !== '') {
                $layer_fragments['badge'] = '<span class="sp-slide-badge sp-layer sp-layer-in-' . esc_attr($badge_anim) . ' sp-layer-out-' . esc_attr($badge_anim_out) . '" data-layer="badge" data-delay="' . esc_attr((string) $badge_delay) . '">' . esc_html($badge) . '</span>';
            }
            if ($title !== '') {
                $layer_fragments['title'] = '<h3 class="sp-layer sp-layer-in-' . esc_attr($title_anim) . ' sp-layer-out-' . esc_attr($title_anim_out) . '" data-layer="title" data-delay="' . esc_attr((string) $title_delay) . '">' . esc_html($title) . '</h3>';
            }
            if ($desc !== '') {
                $layer_fragments['description'] = '<p class="sp-layer sp-layer-in-' . esc_attr($desc_anim) . ' sp-layer-out-' . esc_attr($desc_anim_out) . '" data-layer="description" data-delay="' . esc_attr((string) $desc_delay) . '">' . esc_html($desc) . '</p>';
            }
            if ($btn_text !== '') {
                $layer_fragments['button'] = '<a class="sp-slide-btn sp-layer sp-layer-in-' . esc_attr($button_anim) . ' sp-layer-out-' . esc_attr($button_anim_out) . '" data-layer="button" data-delay="' . esc_attr((string) $button_delay) . '" href="' . esc_url($btn_url) . '">' . esc_html($btn_text) . '</a>';
            }
            if ($caption !== '') {
                $layer_fragments['caption'] = '<span class="sp-slide-caption sp-layer sp-layer-in-' . esc_attr($caption_anim) . ' sp-layer-out-' . esc_attr($caption_anim_out) . '" data-layer="caption" data-delay="' . esc_attr((string) $caption_delay) . '">' . esc_html($caption) . '</span>';
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
            foreach ($slides as $thumb_index => $slide) {
                $thumb_title = isset($slide['title']) ? $slide['title'] : '';
                $thumb_bg = isset($slide['backgroundImage']) ? $slide['backgroundImage'] : '';
                $thumb_style = $thumb_bg ? ' style="background-image:url(' . esc_url($thumb_bg) . ');"' : '';
                $html .= '<button type="button" class="sp-slider-thumb" data-index="' . esc_attr((string) $thumb_index) . '" aria-label="' . esc_attr(sprintf(__('Open slide %d', 'syntekpro-animations'), $thumb_index + 1)) . '">';
                $html .= '<span class="sp-slider-thumb-bg"' . $thumb_style . '></span>';
                $html .= '<span class="sp-slider-thumb-title">' . esc_html($thumb_title !== '' ? $thumb_title : sprintf(__('Slide %d', 'syntekpro-animations'), $thumb_index + 1)) . '</span>';
                $html .= '</button>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

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
        $height_desktop = isset($settings['heightDesktop']) ? absint($settings['heightDesktop']) : 460;
        $height_tablet = isset($settings['heightTablet']) ? absint($settings['heightTablet']) : 400;
        $height_mobile = isset($settings['heightMobile']) ? absint($settings['heightMobile']) : 320;
        $content_align = isset($settings['contentAlign']) ? sanitize_text_field($settings['contentAlign']) : 'center';
        $overlay_strength = isset($settings['overlayStrength']) ? absint($settings['overlayStrength']) : 55;

        echo '<p><strong>' . esc_html__('Shortcode:', 'syntekpro-animations') . '</strong> <code>[sp_slider id="' . esc_html((string) $post->ID) . '"]</code></p>';
        echo '<p>' . esc_html__('Use the visual controls below to build your slider. JSON fallback remains available for advanced editing.', 'syntekpro-animations') . '</p>';

        echo '<div class="sp-slider-editor">';

        echo '<div class="sp-slider-settings">';
        echo '<h4>' . esc_html__('Slider Settings', 'syntekpro-animations') . '</h4>';
        echo '<div class="sp-slider-grid">';

        echo '<label><span>' . esc_html__('Transition', 'syntekpro-animations') . '</span>';
        echo '<select name="sp_slider_settings[transition]">';
        $transition_options = array('slide', 'fade', 'zoom');
        foreach ($transition_options as $option) {
            echo '<option value="' . esc_attr($option) . '" ' . selected($transition, $option, false) . '>' . esc_html(ucfirst($option)) . '</option>';
        }
        echo '</select></label>';

        echo '<label><span>' . esc_html__('Height (Desktop px)', 'syntekpro-animations') . '</span><input type="number" min="220" max="1200" name="sp_slider_settings[heightDesktop]" value="' . esc_attr((string) $height_desktop) . '"></label>';
        echo '<label><span>' . esc_html__('Height (Tablet px)', 'syntekpro-animations') . '</span><input type="number" min="180" max="1000" name="sp_slider_settings[heightTablet]" value="' . esc_attr((string) $height_tablet) . '"></label>';
        echo '<label><span>' . esc_html__('Height (Mobile px)', 'syntekpro-animations') . '</span><input type="number" min="160" max="900" name="sp_slider_settings[heightMobile]" value="' . esc_attr((string) $height_mobile) . '"></label>';
        echo '<label><span>' . esc_html__('Transition Speed (ms)', 'syntekpro-animations') . '</span><input type="number" min="100" max="3000" step="50" name="sp_slider_settings[transitionSpeed]" value="' . esc_attr((string) $transition_speed) . '"></label>';
        echo '<label><span>' . esc_html__('Autoplay Delay (ms)', 'syntekpro-animations') . '</span><input type="number" min="1000" max="20000" step="100" name="sp_slider_settings[autoplayDelay]" value="' . esc_attr((string) $autoplay_delay) . '"></label>';
        echo '<label><span>' . esc_html__('Content Align', 'syntekpro-animations') . '</span><select name="sp_slider_settings[contentAlign]"><option value="left" ' . selected($content_align, 'left', false) . '>' . esc_html__('Left', 'syntekpro-animations') . '</option><option value="center" ' . selected($content_align, 'center', false) . '>' . esc_html__('Center', 'syntekpro-animations') . '</option><option value="right" ' . selected($content_align, 'right', false) . '>' . esc_html__('Right', 'syntekpro-animations') . '</option></select></label>';
        echo '<label><span>' . esc_html__('Overlay Strength (%)', 'syntekpro-animations') . '</span><input type="number" min="0" max="90" step="1" name="sp_slider_settings[overlayStrength]" value="' . esc_attr((string) $overlay_strength) . '"></label>';

        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[autoplay]" value="1" ' . checked($autoplay, true, false) . '><span>' . esc_html__('Enable Autoplay', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[autoplayPauseOnHover]" value="1" ' . checked($autoplay_pause_hover, true, false) . '><span>' . esc_html__('Pause Autoplay on Hover', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[pauseOnInteraction]" value="1" ' . checked($pause_on_interaction, true, false) . '><span>' . esc_html__('Pause on Manual Interaction', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[loop]" value="1" ' . checked($loop, true, false) . '><span>' . esc_html__('Enable Loop', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[navigation]" value="1" ' . checked($navigation, true, false) . '><span>' . esc_html__('Show Arrows', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[pagination]" value="1" ' . checked($pagination, true, false) . '><span>' . esc_html__('Show Dots', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[keyboardNav]" value="1" ' . checked($keyboard_nav, true, false) . '><span>' . esc_html__('Keyboard Navigation', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[swipeNav]" value="1" ' . checked($swipe_nav, true, false) . '><span>' . esc_html__('Touch Swipe Navigation', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[progressBar]" value="1" ' . checked($progress_bar, true, false) . '><span>' . esc_html__('Show Progress Bar', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[showCounter]" value="1" ' . checked($show_counter, true, false) . '><span>' . esc_html__('Show Slide Counter', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[thumbnails]" value="1" ' . checked($thumbnails, true, false) . '><span>' . esc_html__('Show Thumbnails', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[lazyLoad]" value="1" ' . checked($lazy_load, true, false) . '><span>' . esc_html__('Lazy Load Slide Backgrounds', 'syntekpro-animations') . '</span></label>';

        echo '</div>';
        echo '</div>';

        echo '<div class="sp-slides-wrap">';
        echo '<div class="sp-slides-head"><h4>' . esc_html__('Slides', 'syntekpro-animations') . '</h4><button type="button" class="button button-secondary" id="sp-add-slide">' . esc_html__('Add Slide', 'syntekpro-animations') . '</button></div>';
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

        echo '</div>';

        ?>
        <style>
            .sp-slider-editor { display: grid; gap: 14px; }
            .sp-slider-settings, .sp-slides-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
            .sp-slider-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
            .sp-slider-grid label span { display: block; font-weight: 600; margin-bottom: 4px; }
            .sp-slider-grid input[type="number"], .sp-slider-grid select, .sp-slide-card input[type="text"], .sp-slide-card input[type="url"], .sp-slide-card textarea { width: 100%; }
            .sp-check { display: flex; align-items: center; gap: 8px; padding-top: 24px; }
            .sp-slides-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
            .sp-slide-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; margin-bottom: 10px; background: #f8fafc; }
            .sp-slide-card h5 { margin: 0 0 8px 0; }
            .sp-slide-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
            .sp-slide-grid .full { grid-column: 1 / -1; }
            .sp-layer-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; margin-top: 6px; }
            .sp-layer-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 4px; }
            .sp-layer-head span { font-weight: 600; }
            .sp-layer-help { margin: 4px 0 0; color: #475569; font-size: 12px; }
            .sp-layer-order-list { list-style: none; margin: 8px 0 0; padding: 0; display: grid; gap: 6px; }
            .sp-layer-order-item { background: #fff; border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 10px; cursor: move; display: flex; align-items: center; gap: 8px; }
            .sp-layer-order-item.dragging { opacity: 0.55; }
            .sp-layer-order-handle { color: #64748b; font-size: 12px; }
            .sp-image-row { display: flex; align-items: center; gap: 8px; }
            .sp-image-preview { width: 64px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #d1d5db; background: #fff; }
            .sp-slide-actions { margin-top: 8px; text-align: right; }
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
        </style>
        <script>
            (function() {
                const list = document.getElementById('sp-slides-list');
                const addBtn = document.getElementById('sp-add-slide');
                if (!list || !addBtn) return;

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
                        const title = card.querySelector('.sp-slide-title');
                        if (title) {
                            title.textContent = 'Slide ' + (index + 1);
                        }
                        card.querySelectorAll('[name]').forEach((input) => {
                            input.name = input.name.replace(/sp_slider_slides\[\d+\]/, 'sp_slider_slides[' + index + ']');
                        });
                    });
                }

                function bindCardEvents(card) {
                    const removeBtn = card.querySelector('.sp-remove-slide');
                    const pickBtn = card.querySelector('.sp-select-image');
                    const clearBtn = card.querySelector('.sp-clear-image');
                    const imageInput = card.querySelector('.sp-image-input');
                    const imagePreview = card.querySelector('.sp-image-preview');
                    const layerList = card.querySelector('.sp-layer-order-list');
                    const layerOrderInput = card.querySelector('.sp-layer-order-input');
                    const previewWrap = ensureLivePreview(card);
                    const previewCanvas = previewWrap ? previewWrap.querySelector('.sp-live-preview-canvas') : null;
                    const replayBtn = previewWrap ? previewWrap.querySelector('.sp-preview-replay') : null;
                    const resetBtn = card.querySelector('.sp-reset-layer-timings');

                    card.querySelectorAll('input[type="text"][name*="Anim"], input[type="text"][name*="AnimOut"]').forEach(toSelectInput);

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

                    if (resetBtn) {
                        resetBtn.addEventListener('click', resetLayerTimings);
                    }

                    card.querySelectorAll('input, textarea, select').forEach((el) => {
                        el.addEventListener('input', function() {
                            updatePreviewText();
                            runPreview();
                        });
                        el.addEventListener('change', function() {
                            updatePreviewText();
                            runPreview();
                        });
                    });

                    bindLayerDnD();
                    syncLayerOrder();
                    updatePreviewText();
                    runPreview();
                }

                function addSlide() {
                    const index = list.querySelectorAll('.sp-slide-card').length;
                    const html = `
                        <div class="sp-slide-card" data-index="${index}">
                            <h5 class="sp-slide-title">Slide ${index + 1}</h5>
                            <div class="sp-slide-grid">
                                <label class="full"><span>Title</span><input type="text" name="sp_slider_slides[${index}][title]" value=""></label>
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
                                        <button type="button" class="button button-small sp-reset-layer-timings">Reset Timings</button>
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
                            <div class="sp-slide-actions"><button type="button" class="button button-link-delete sp-remove-slide">Remove Slide</button></div>
                        </div>
                    `;
                    list.insertAdjacentHTML('beforeend', html);
                    bindCardEvents(list.lastElementChild);
                }

                list.querySelectorAll('.sp-slide-card').forEach(bindCardEvents);
                addBtn.addEventListener('click', addSlide);
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

        echo '<div class="sp-slide-card" data-index="' . esc_attr((string) $index) . '">';
        echo '<h5 class="sp-slide-title">' . esc_html(sprintf(__('Slide %d', 'syntekpro-animations'), $index + 1)) . '</h5>';
        echo '<div class="sp-slide-grid">';

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

        echo '<label><span>' . esc_html__('Layer Duration (ms)', 'syntekpro-animations') . '</span><input type="number" min="100" max="3000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][layerDuration]" value="' . esc_attr((string) $layer_duration) . '"></label>';
        echo '<label><span>' . esc_html__('Layer Stagger (ms)', 'syntekpro-animations') . '</span><input type="number" min="0" max="1000" step="10" name="sp_slider_slides[' . esc_attr((string) $index) . '][layerStagger]" value="' . esc_attr((string) $layer_stagger) . '"></label>';
        echo '<label><span>' . esc_html__('Ken Burns Duration (ms)', 'syntekpro-animations') . '</span><input type="number" min="2000" max="30000" step="100" name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurnsDuration]" value="' . esc_attr((string) $ken_burns_duration) . '"></label>';
        echo '<label><span>' . esc_html__('Ken Burns Scale Start', 'syntekpro-animations') . '</span><input type="number" min="1" max="1.8" step="0.01" name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurnsScaleStart]" value="' . esc_attr((string) $ken_burns_scale_start) . '"></label>';
        echo '<label><span>' . esc_html__('Ken Burns Scale End', 'syntekpro-animations') . '</span><input type="number" min="1" max="2.2" step="0.01" name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurnsScaleEnd]" value="' . esc_attr((string) $ken_burns_scale_end) . '"></label>';
        echo '<label><span>' . esc_html__('Ken Burns Direction', 'syntekpro-animations') . '</span><select name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurnsDirection]"><option value="left-to-right" ' . selected($ken_burns_direction, 'left-to-right', false) . '>Left to Right</option><option value="right-to-left" ' . selected($ken_burns_direction, 'right-to-left', false) . '>Right to Left</option><option value="top-to-bottom" ' . selected($ken_burns_direction, 'top-to-bottom', false) . '>Top to Bottom</option><option value="bottom-to-top" ' . selected($ken_burns_direction, 'bottom-to-top', false) . '>Bottom to Top</option><option value="center" ' . selected($ken_burns_direction, 'center', false) . '>Center</option></select></label>';
        echo '<label class="full"><span><input type="checkbox" name="sp_slider_slides[' . esc_attr((string) $index) . '][kenBurns]" value="1" ' . checked($ken_burns, true, false) . '> ' . esc_html__('Enable Ken Burns on this slide', 'syntekpro-animations') . '</span></label>';

        echo '<div class="full">';
        echo '<div class="sp-layer-head"><span>' . esc_html__('Layer Entrances', 'syntekpro-animations') . '</span><button type="button" class="button button-small sp-reset-layer-timings">' . esc_html__('Reset Timings', 'syntekpro-animations') . '</button></div>';
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
        echo '<div class="sp-slide-actions"><button type="button" class="button button-link-delete sp-remove-slide">' . esc_html__('Remove Slide', 'syntekpro-animations') . '</button></div>';
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
            'heightDesktop' => isset($settings['heightDesktop']) ? absint($settings['heightDesktop']) : 460,
            'heightTablet' => isset($settings['heightTablet']) ? absint($settings['heightTablet']) : 400,
            'heightMobile' => isset($settings['heightMobile']) ? absint($settings['heightMobile']) : 320,
            'contentAlign' => isset($settings['contentAlign']) ? sanitize_text_field($settings['contentAlign']) : 'center',
            'overlayStrength' => isset($settings['overlayStrength']) ? absint($settings['overlayStrength']) : 55,
        );

        if (!in_array($safe_settings['transition'], array('slide', 'fade', 'zoom'), true)) {
            $safe_settings['transition'] = 'slide';
        }
        if (!in_array($safe_settings['contentAlign'], array('left', 'center', 'right'), true)) {
            $safe_settings['contentAlign'] = 'center';
        }
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
                'layerDuration' => isset($slide['layerDuration']) ? absint($slide['layerDuration']) : 720,
                'layerStagger' => isset($slide['layerStagger']) ? absint($slide['layerStagger']) : 70,
                'layerOrder' => isset($slide['layerOrder']) ? sanitize_text_field($slide['layerOrder']) : 'badge,title,description,button,caption',
                'kenBurns' => !empty($slide['kenBurns']),
                'kenBurnsScaleStart' => isset($slide['kenBurnsScaleStart']) ? (float) $slide['kenBurnsScaleStart'] : 1.06,
                'kenBurnsScaleEnd' => isset($slide['kenBurnsScaleEnd']) ? (float) $slide['kenBurnsScaleEnd'] : 1.16,
                'kenBurnsDuration' => isset($slide['kenBurnsDuration']) ? absint($slide['kenBurnsDuration']) : 9000,
                'kenBurnsDirection' => isset($slide['kenBurnsDirection']) ? sanitize_key($slide['kenBurnsDirection']) : 'left-to-right',
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

            $safe_slides[] = $safe_slide;
        }

        update_post_meta($post_id, '_sp_slider_settings', $safe_settings);
        update_post_meta($post_id, '_sp_slider_slides', $safe_slides);
        update_post_meta($post_id, '_sp_slider_version', '1');
    }
}

new Syntekpro_Slider_Core();
