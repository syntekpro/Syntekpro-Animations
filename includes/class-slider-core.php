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
            'show_in_menu' => 'syntekpro-animations',
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
            'loop' => true,
            'navigation' => true,
            'pagination' => true,
            'transition' => 'slide',
            'heightDesktop' => 460,
        );

        $settings = array_merge($defaults, $settings);

        if (empty($slides)) {
            $slides = array(
                array(
                    'title' => __('Sample Slide', 'syntekpro-animations'),
                    'description' => __('Edit this slider and replace sample content from the visual slide editor.', 'syntekpro-animations'),
                    'buttonText' => __('Learn More', 'syntekpro-animations'),
                    'buttonUrl' => '#',
                    'backgroundImage' => '',
                )
            );
        }

        wp_enqueue_style('syntekpro-slider-runtime');
        wp_enqueue_script('syntekpro-slider-runtime');

        $instance_id = 'sp-slider-' . $slider_id . '-' . wp_rand(100, 9999);

        $wrapper_attrs = array(
            'id' => $instance_id,
            'class' => 'sp-slider-runtime',
            'data-autoplay' => !empty($settings['autoplay']) ? 'true' : 'false',
            'data-autoplay-delay' => (string) absint($settings['autoplayDelay']),
            'data-loop' => !empty($settings['loop']) ? 'true' : 'false',
            'data-pagination' => !empty($settings['pagination']) ? 'true' : 'false',
            'data-navigation' => !empty($settings['navigation']) ? 'true' : 'false',
            'data-transition' => sanitize_text_field($settings['transition']),
            'style' => 'min-height:' . absint($settings['heightDesktop']) . 'px;',
        );

        $attrs_html = '';
        foreach ($wrapper_attrs as $k => $v) {
            $attrs_html .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        }

        $html = '<div' . $attrs_html . '>';
        $html .= '<div class="sp-slider-track">';

        foreach ($slides as $slide) {
            $title = isset($slide['title']) ? $slide['title'] : '';
            $desc = isset($slide['description']) ? $slide['description'] : '';
            $btn_text = isset($slide['buttonText']) ? $slide['buttonText'] : '';
            $btn_url = isset($slide['buttonUrl']) ? $slide['buttonUrl'] : '#';
            $bg = isset($slide['backgroundImage']) ? $slide['backgroundImage'] : '';

            $bg_style = $bg ? ' style="background-image:url(' . esc_url($bg) . ');"' : '';
            $html .= '<article class="sp-slide"' . $bg_style . '>';
            $html .= '<div class="sp-slide-overlay"></div>';
            $html .= '<div class="sp-slide-content">';
            if ($title !== '') {
                $html .= '<h3>' . esc_html($title) . '</h3>';
            }
            if ($desc !== '') {
                $html .= '<p>' . esc_html($desc) . '</p>';
            }
            if ($btn_text !== '') {
                $html .= '<a class="sp-slide-btn" href="' . esc_url($btn_url) . '">' . esc_html($btn_text) . '</a>';
            }
            $html .= '</div>';
            $html .= '</article>';
        }

        $html .= '</div>';

        if (!empty($settings['navigation'])) {
            $html .= '<button class="sp-slider-prev" type="button" aria-label="' . esc_attr__('Previous slide', 'syntekpro-animations') . '">&#10094;</button>';
            $html .= '<button class="sp-slider-next" type="button" aria-label="' . esc_attr__('Next slide', 'syntekpro-animations') . '">&#10095;</button>';
        }

        if (!empty($settings['pagination'])) {
            $html .= '<div class="sp-slider-dots" aria-hidden="true"></div>';
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
                'loop' => true,
                'navigation' => true,
                'pagination' => true,
                'transition' => 'slide',
                'heightDesktop' => 460,
            );
        }

        if (!is_array($slides)) {
            $slides = array(
                array(
                    'title' => 'Slide One',
                    'description' => 'Describe your offer or feature here.',
                    'buttonText' => 'Learn More',
                    'buttonUrl' => '#',
                    'backgroundImage' => '',
                ),
                array(
                    'title' => 'Slide Two',
                    'description' => 'Add another message for your audience.',
                    'buttonText' => 'Get Started',
                    'buttonUrl' => '#',
                    'backgroundImage' => '',
                ),
            );
        }

        $autoplay = !empty($settings['autoplay']);
        $autoplay_delay = isset($settings['autoplayDelay']) ? absint($settings['autoplayDelay']) : 5000;
        $loop = !empty($settings['loop']);
        $navigation = !empty($settings['navigation']);
        $pagination = !empty($settings['pagination']);
        $transition = isset($settings['transition']) ? sanitize_text_field($settings['transition']) : 'slide';
        $height_desktop = isset($settings['heightDesktop']) ? absint($settings['heightDesktop']) : 460;

        echo '<p><strong>' . esc_html__('Shortcode:', 'syntekpro-animations') . '</strong> <code>[sp_slider id="' . esc_html((string) $post->ID) . '"]</code></p>';
        echo '<p>' . esc_html__('Use the visual controls below to build your slider. JSON fallback remains available for advanced editing.', 'syntekpro-animations') . '</p>';

        echo '<div class="sp-slider-editor">';

        echo '<div class="sp-slider-settings">';
        echo '<h4>' . esc_html__('Slider Settings', 'syntekpro-animations') . '</h4>';
        echo '<div class="sp-slider-grid">';

        echo '<label><span>' . esc_html__('Transition', 'syntekpro-animations') . '</span>';
        echo '<select name="sp_slider_settings[transition]">';
        $transition_options = array('slide', 'fade');
        foreach ($transition_options as $option) {
            echo '<option value="' . esc_attr($option) . '" ' . selected($transition, $option, false) . '>' . esc_html(ucfirst($option)) . '</option>';
        }
        echo '</select></label>';

        echo '<label><span>' . esc_html__('Height (Desktop px)', 'syntekpro-animations') . '</span><input type="number" min="220" max="1200" name="sp_slider_settings[heightDesktop]" value="' . esc_attr((string) $height_desktop) . '"></label>';
        echo '<label><span>' . esc_html__('Autoplay Delay (ms)', 'syntekpro-animations') . '</span><input type="number" min="1000" max="20000" step="100" name="sp_slider_settings[autoplayDelay]" value="' . esc_attr((string) $autoplay_delay) . '"></label>';

        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[autoplay]" value="1" ' . checked($autoplay, true, false) . '><span>' . esc_html__('Enable Autoplay', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[loop]" value="1" ' . checked($loop, true, false) . '><span>' . esc_html__('Enable Loop', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[navigation]" value="1" ' . checked($navigation, true, false) . '><span>' . esc_html__('Show Arrows', 'syntekpro-animations') . '</span></label>';
        echo '<label class="sp-check"><input type="checkbox" name="sp_slider_settings[pagination]" value="1" ' . checked($pagination, true, false) . '><span>' . esc_html__('Show Dots', 'syntekpro-animations') . '</span></label>';

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
            .sp-image-row { display: flex; align-items: center; gap: 8px; }
            .sp-image-preview { width: 64px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #d1d5db; background: #fff; }
            .sp-slide-actions { margin-top: 8px; text-align: right; }
        </style>
        <script>
            (function() {
                const list = document.getElementById('sp-slides-list');
                const addBtn = document.getElementById('sp-add-slide');
                if (!list || !addBtn) return;

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
                }

                function addSlide() {
                    const index = list.querySelectorAll('.sp-slide-card').length;
                    const html = `
                        <div class="sp-slide-card" data-index="${index}">
                            <h5 class="sp-slide-title">Slide ${index + 1}</h5>
                            <div class="sp-slide-grid">
                                <label class="full"><span>Title</span><input type="text" name="sp_slider_slides[${index}][title]" value=""></label>
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
        $button_text = isset($slide['buttonText']) ? $slide['buttonText'] : '';
        $button_url = isset($slide['buttonUrl']) ? $slide['buttonUrl'] : '#';
        $background = isset($slide['backgroundImage']) ? $slide['backgroundImage'] : '';

        echo '<div class="sp-slide-card" data-index="' . esc_attr((string) $index) . '">';
        echo '<h5 class="sp-slide-title">' . esc_html(sprintf(__('Slide %d', 'syntekpro-animations'), $index + 1)) . '</h5>';
        echo '<div class="sp-slide-grid">';

        echo '<label class="full"><span>' . esc_html__('Title', 'syntekpro-animations') . '</span><input type="text" name="sp_slider_slides[' . esc_attr((string) $index) . '][title]" value="' . esc_attr($title) . '"></label>';
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
            'loop' => !empty($settings['loop']),
            'navigation' => !empty($settings['navigation']),
            'pagination' => !empty($settings['pagination']),
            'transition' => isset($settings['transition']) ? sanitize_text_field($settings['transition']) : 'slide',
            'heightDesktop' => isset($settings['heightDesktop']) ? absint($settings['heightDesktop']) : 460,
        );

        $safe_slides = array();
        foreach ($slides as $slide) {
            if (!is_array($slide)) {
                continue;
            }
            $safe_slide = array(
                'title' => isset($slide['title']) ? sanitize_text_field($slide['title']) : '',
                'description' => isset($slide['description']) ? sanitize_textarea_field($slide['description']) : '',
                'buttonText' => isset($slide['buttonText']) ? sanitize_text_field($slide['buttonText']) : '',
                'buttonUrl' => isset($slide['buttonUrl']) ? esc_url_raw($slide['buttonUrl']) : '#',
                'backgroundImage' => isset($slide['backgroundImage']) ? esc_url_raw($slide['backgroundImage']) : '',
            );

            if ($safe_slide['title'] === '' && $safe_slide['description'] === '' && $safe_slide['buttonText'] === '' && $safe_slide['backgroundImage'] === '') {
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
