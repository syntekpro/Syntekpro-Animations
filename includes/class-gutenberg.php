<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gutenberg Block Integration
 */
class Syntekpro_Animations_Gutenberg {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_block_category'));
        add_action('init', array($this, 'register_blocks'));
        add_action('init', array($this, 'register_block_patterns'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
    }

    /**
     * Default shared data for dynamic patterns.
     */
    private function get_pattern_defaults() {
        return array(
            'pricing' => array(
                array('title' => __('Starter', 'syntekpro-animations'), 'price' => '$12', 'items' => array(__('Core animations', 'syntekpro-animations'), __('Scroll triggers', 'syntekpro-animations'), __('Email support', 'syntekpro-animations'))),
                array('title' => __('Get+', 'syntekpro-animations'), 'price' => '$29', 'items' => array(__('All animations unlocked', 'syntekpro-animations'), __('ScrollSmoother + timelines', 'syntekpro-animations'), __('Priority chat support', 'syntekpro-animations'))),
                array('title' => __('Agency', 'syntekpro-animations'), 'price' => '$59', 'items' => array(__('Unlimited sites', 'syntekpro-animations'), __('Team seats included', 'syntekpro-animations'), __('Dedicated success manager', 'syntekpro-animations'))),
            ),
            'faq' => array(
                array('q' => __('Will animations respect reduced motion?', 'syntekpro-animations'), 'a' => __('Yes. We detect prefers-reduced-motion and fall back to minimal motion.', 'syntekpro-animations')),
                array('q' => __('Do these patterns work with any theme?', 'syntekpro-animations'), 'a' => __('They rely on core blocks and inherit your typography and colors.', 'syntekpro-animations')),
                array('q' => __('Can I adjust timing after inserting?', 'syntekpro-animations'), 'a' => __('Yes—select the animation block and tweak duration, delay, or stagger.', 'syntekpro-animations')),
            ),
            'testimonials' => array(
                array('quote' => __('“We shipped a refreshed homepage in a day—animations just worked.”', 'syntekpro-animations'), 'name' => __('Amira Lopez, Product Marketing Lead', 'syntekpro-animations')),
                array('quote' => __('“The presets respect reduced motion out of the box. Huge win.”', 'syntekpro-animations'), 'name' => __('Jamal Everett, Accessibility Lead', 'syntekpro-animations')),
                array('quote' => __('“Our agency can reuse these patterns across clients and stay on-brand.”', 'syntekpro-animations'), 'name' => __('Priya Menon, Creative Director', 'syntekpro-animations')),
            ),
        );
    }

    /**
     * Fetch shared data with defaults for dynamic patterns.
     */
    private function get_pattern_data($key) {
        $defaults = $this->get_pattern_defaults();
        $default_value = isset($defaults[$key]) ? $defaults[$key] : array();
        $option_value = get_option('syntekpro_pattern_' . $key, $default_value);

        if (!is_array($option_value) || empty($option_value)) {
            return $default_value;
        }

        return $option_value;
    }

    /**
     * Normalize align class for dynamic blocks.
     */
    private function get_align_class($attributes, $default = 'full') {
        $align = isset($attributes['align']) ? sanitize_text_field($attributes['align']) : $default;
        if ($align === 'wide' || $align === 'full') {
            return ' align' . $align;
        }
        return '';
    }

    /**
     * Build a lightweight animation wrapper to mirror the animate block output.
     */
    private function build_animation_wrapper($inner_html, $attrs = array()) {
        $defaults = array(
            'type' => 'fadeInUp',
            'duration' => 1,
            'delay' => 0,
            'ease' => 'power2.out',
            'trigger' => 'scroll',
            'stagger' => 0,
            'repeat' => 0,
            'startPosition' => 'top 80%',
            'markers' => false,
            'onceOnly' => true,
            'engine' => 'auto',
        );

        $settings = array_merge($defaults, $attrs);
        $unique_id = 'sp-anim-' . uniqid();

        return '<div id="' . esc_attr($unique_id) . '" class="sp-animate" data-animation="' . esc_attr($settings['type']) . '" data-duration="' . esc_attr($settings['duration']) . '" data-delay="' . esc_attr($settings['delay']) . '" data-trigger="' . esc_attr($settings['trigger']) . '" data-ease="' . esc_attr($settings['ease']) . '" data-stagger="' . esc_attr($settings['stagger']) . '" data-repeat="' . esc_attr($settings['repeat']) . '" data-start="' . esc_attr($settings['startPosition']) . '" data-markers="' . ($settings['markers'] ? 'true' : 'false') . '" data-once="' . ($settings['onceOnly'] ? 'true' : 'false') . '" data-engine="' . esc_attr($settings['engine']) . '">' . $inner_html . '</div>';
    }

    /**
     * Render pricing pattern block from shared data.
     */
    public function render_pattern_pricing_block($attributes) {
        $pricing_data = $this->get_pattern_data('pricing');
        if (empty($pricing_data)) {
            return '';
        }

        $pricing_columns = '';
        foreach ($pricing_data as $index => $plan) {
            $title = isset($plan['title']) ? $plan['title'] : '';
            $price = isset($plan['price']) ? $plan['price'] : '';
            $items = isset($plan['items']) && is_array($plan['items']) ? $plan['items'] : array();
            $list_items = '';
            foreach ($items as $item) {
                $list_items .= '<li>' . esc_html($item) . '</li>';
            }

            $is_accent = ($index === 1);
            $border_width = $is_accent ? '2px' : '1px';
            $border_color = $is_accent ? 'gray-800' : 'gray-300';
            $background = $is_accent ? 'var(--sp-color-surface-strong)' : 'var(--sp-color-surface)';
            $heading_color = $is_accent ? ' class="has-text-color" style="color:var(--sp-color-accent-contrast)"' : '';
            $price_color = $is_accent ? ' class="has-text-color" style="color:var(--sp-color-accent-contrast);font-size:28px;font-weight:700"' : ' style="font-size:28px;font-weight:700"';
            $list_color = $is_accent ? ' class="has-text-color" style="color:var(--sp-color-text-soft)"' : '';
            $button_class = $is_accent ? ' has-text-color has-background' : '';
            $button_style = $is_accent ? ';color:var(--sp-color-text);background-color:var(--sp-color-surface)' : '';
            $button_label = ($index === 1) ? __('Get+', 'syntekpro-animations') : (($index === 2) ? __('Talk to sales', 'syntekpro-animations') : __('Choose Starter', 'syntekpro-animations'));

            $pricing_columns .= '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"14px","width":"' . $border_width . '","style":"solid"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}},"color":{"background":"' . $background . '"}},"borderColor":"' . $border_color . '","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-border-color has-' . $border_color . '-border-color has-background" style="border-style:solid;border-width:' . $border_width . ';border-radius:14px;background-color:' . $background . ';padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><!-- wp:heading {"level":4' . ($is_accent ? ',"style":{"color":{"text":"var(--sp-color-accent-contrast)"}}' : '') . '} -->
<h4' . $heading_color . '>' . esc_html($title) . '</h4>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"28px","fontWeight":"700"}' . ($is_accent ? ',"color":{"text":"var(--sp-color-accent-contrast)"}' : '') . '}} -->
<p' . $price_color . '>' . esc_html($price) . '</p>
<!-- /wp:paragraph -->

<!-- wp:list' . ($is_accent ? ' {"style":{"color":{"text":"var(--sp-color-text-soft)"}}}' : '') . ' -->
<ul' . $list_color . '>' . $list_items . '</ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"10px"}' . ($is_accent ? ',"color":{"background":"var(--sp-color-surface)","text":"var(--sp-color-text)"}' : '') . '}} -->
<div class="wp-block-button"><a class="wp-block-button__link' . $button_class . ' wp-element-button" style="border-radius:10px' . $button_style . '">' . esc_html($button_label) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->';
        }

        $align_class = $this->get_align_class($attributes, 'full');

        $section_inner = '<div class="wp-block-group" style="display:grid;gap:18px;">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__('Pricing that scales with you.', 'syntekpro-animations') . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__('Choose the right plan and keep motion-first pages flowing.', 'syntekpro-animations') . '</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":"16px"}}} -->
<div class="wp-block-columns">' . $pricing_columns . '</div>
<!-- /wp:columns -->
</div>';

        $animated_section = $this->build_animation_wrapper($section_inner, array('stagger' => 0.08, 'duration' => 1));

        return '<div class="wp-block-group' . $align_class . ' has-background" style="background-color:var(--sp-color-bg);padding-top:40px;padding-right:20px;padding-bottom:40px;padding-left:20px">' . $animated_section . '</div>';
    }

    /**
     * Render FAQ pattern block from shared data.
     */
    public function render_pattern_faq_block($attributes) {
        $faq_data = $this->get_pattern_data('faq');
        if (empty($faq_data)) {
            return '';
        }

        $faq_details = '';
        foreach ($faq_data as $faq_item) {
            $q = isset($faq_item['q']) ? $faq_item['q'] : '';
            $a = isset($faq_item['a']) ? $faq_item['a'] : '';
            $faq_details .= '<!-- wp:details -->
<details>
<summary>' . esc_html($q) . '</summary>
<!-- wp:paragraph -->
<p>' . esc_html($a) . '</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->';
        }

        $align_class = $this->get_align_class($attributes, '');

        $section_inner = '<div class="wp-block-group" style="display:grid;gap:14px;">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__('Answers before you ship.', 'syntekpro-animations') . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__('Common questions about motion, performance, and rollout.', 'syntekpro-animations') . '</p>
<!-- /wp:paragraph -->

' . $faq_details . '
</div>';

        $animated_section = $this->build_animation_wrapper($section_inner, array('stagger' => 0.06, 'duration' => 0.95));

        return '<div class="wp-block-group' . $align_class . '" style="padding-top:32px;padding-right:20px;padding-bottom:32px;padding-left:20px;background-color:var(--sp-color-surface);border:1px solid var(--sp-color-border);border-radius:var(--sp-radius-lg)">' . $animated_section . '</div>';
    }

    /**
     * Render testimonial pattern block from shared data.
     */
    public function render_pattern_testimonials_block($attributes) {
        $testimonial_data = $this->get_pattern_data('testimonials');
        if (empty($testimonial_data)) {
            return '';
        }

        $testimonial_columns = '';
        foreach ($testimonial_data as $testimonial) {
            $quote = isset($testimonial['quote']) ? $testimonial['quote'] : '';
            $name = isset($testimonial['name']) ? $testimonial['name'] : '';
            $testimonial_columns .= '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"14px","width":"1px"},"spacing":{"padding":{"top":"18px","right":"18px","bottom":"18px","left":"18px"}}},"borderColor":"gray-200","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-border-color has-gray-200-border-color" style="border-width:1px;border-radius:14px;padding-top:18px;padding-right:18px;padding-bottom:18px;padding-left:18px"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"}}} -->
<p style="font-style:normal;font-weight:600">' . esc_html($quote) . '</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>' . esc_html($name) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->';
        }

        $align_class = $this->get_align_class($attributes, 'full');

        $section_inner = '<div class="wp-block-group" style="display:grid;gap:18px;">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">' . esc_html__('Trusted by teams that care about motion.', 'syntekpro-animations') . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__('Staggered testimonials to show outcomes and credibility.', 'syntekpro-animations') . '</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":"16px"}}} -->
<div class="wp-block-columns">' . $testimonial_columns . '</div>
<!-- /wp:columns -->
</div>';

        $animated_section = $this->build_animation_wrapper($section_inner, array('stagger' => 0.07, 'duration' => 1));

        return '<div class="wp-block-group' . $align_class . ' has-background" style="border-color:var(--sp-color-border);border-width:1px;background-color:var(--sp-color-surface);padding-top:36px;padding-right:20px;padding-bottom:36px;padding-left:20px;border-radius:var(--sp-radius-lg)">' . $animated_section . '</div>';
    }

    /**
     * Register custom block category
     */
    public function register_block_category() {
        if (function_exists('register_block_category')) {
            register_block_category(array(
                'slug'  => 'syntekpro',
                'title' => 'Syntekpro Animations',
                'icon'  => 'star-filled'
            ));
        }
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_style('syntekpro-design-system', SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/design-system.css', array(), SYNTEKPRO_ANIM_VERSION);
        wp_enqueue_style('syntekpro-block-editor-style', SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/block-editor.css', array(), SYNTEKPRO_ANIM_VERSION);
    }

    /**
     * Register blocks and assets
     */
    public function register_blocks() {
        // Register GSAP and ScrollTrigger scripts so other scripts can depend on them
        // These are registered (not enqueued) so they load when a page has the block
        wp_register_script(
            'syntekpro-gsap',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/gsap/minified/gsap.min.js',
            array(),
            SYNTEKPRO_ANIM_VERSION,
            true
        );
        
        wp_register_script(
            'syntekpro-scrolltrigger',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/gsap/minified/ScrollTrigger.min.js',
            array('syntekpro-gsap'),
            SYNTEKPRO_ANIM_VERSION,
            true
        );

        // Register the editor script
        wp_register_script(
            'syntekpro-animations-block-editor',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/block-editor.js',
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'),
            SYNTEKPRO_ANIM_VERSION,
            true
        );

        // Register the frontend script (will be enqueued only on pages with the block)
        wp_register_script(
            'syntekpro-animations-frontend',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/animations.js',
            array(),
            SYNTEKPRO_ANIM_VERSION,
            true
        );

        // Register the frontend style
        wp_register_style(
            'syntekpro-animations-style',
            SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/style.css',
            array(),
            SYNTEKPRO_ANIM_VERSION
        );

        // Register all new block scripts and styles
        $this->register_block_assets();

        // Register the block
        register_block_type('syntekpro/animate', array(
            'editor_script' => 'syntekpro-animations-block-editor',
            'script' => 'syntekpro-animations-frontend',
            'style' => 'syntekpro-animations-style',
            'render_callback' => array($this, 'render_block'),
            'attributes' => array(
                'type' => array('type' => 'string', 'default' => 'fadeInUp'),
                'duration' => array('type' => 'number', 'default' => 1),
                'delay' => array('type' => 'number', 'default' => 0),
                'ease' => array('type' => 'string', 'default' => 'power2.out'),
                'trigger' => array('type' => 'string', 'default' => 'scroll'),
                'useScrollTrigger' => array('type' => 'boolean', 'default' => true),
                'stagger' => array('type' => 'number', 'default' => 0),
                'repeatCount' => array('type' => 'number', 'default' => 0),
                'startPosition' => array('type' => 'string', 'default' => 'top 80%'),
                'scrub' => array('type' => 'boolean', 'default' => false),
                'markers' => array('type' => 'boolean', 'default' => false),
                'onceOnly' => array('type' => 'boolean', 'default' => true),
                // Engine: auto (smart choose CSS/GSAP), css (light), gsap (force GSAP)
                'engine' => array('type' => 'string', 'default' => 'auto')
            ),
            'supports' => array(
                'align' => true,
                'className' => true,
                'html' => false,
                'innerBlocks' => true
            ),
            'apiVersion' => 2
        ));

        // Dynamic pattern blocks that pull shared option data at render time
        register_block_type('syntekpro/pattern-pricing', array(
            'render_callback' => array($this, 'render_pattern_pricing_block'),
            'attributes' => array(
                'align' => array('type' => 'string'),
            ),
            'supports' => array(
                'align' => array('wide', 'full'),
            ),
        ));

        register_block_type('syntekpro/pattern-faq', array(
            'render_callback' => array($this, 'render_pattern_faq_block'),
            'attributes' => array(
                'align' => array('type' => 'string'),
            ),
            'supports' => array(
                'align' => array('wide', 'full'),
            ),
        ));

        register_block_type('syntekpro/pattern-testimonials', array(
            'render_callback' => array($this, 'render_pattern_testimonials_block'),
            'attributes' => array(
                'align' => array('type' => 'string'),
            ),
            'supports' => array(
                'align' => array('wide', 'full'),
            ),
        ));
    }

    /**
     * Register block patterns for common animated layouts
     */
    public function register_block_patterns() {
        if (!function_exists('register_block_pattern')) {
            return;
        }

        if (function_exists('register_block_pattern_category')) {
            register_block_pattern_category('syntekpro', array('label' => __('Syntekpro Animations', 'syntekpro-animations')));
        }

        // Hero spotlight pattern
        $hero_content = '<!-- wp:cover {"overlayColor":"white","minHeight":520,"isDark":false,"align":"full","style":{"color":{"background":"var(--sp-color-surface-subtle)"}}} -->
    <div class="wp-block-cover alignfull is-light" style="min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background" style="background-color:var(--sp-color-surface-subtle)"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"layout":{"type":"constrained","contentSize":"960px"}} --><div class="wp-block-group"><!-- wp:syntekpro/animate {"type":"fadeInUp","duration":1,"stagger":0.08,"onceOnly":true} -->
    <div class="wp-block-group" style="text-align:center;display:grid;gap:16px;max-width:820px;margin:0 auto;color:var(--sp-color-text)">
<!-- wp:heading {"level":1} -->
<h1>' . esc_html__('Animate hero sections with one click.', 'syntekpro-animations') . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__('Pair smooth entrance with clear CTAs and keep motion subtle.', 'syntekpro-animations') . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"8px"}},"className":"is-style-fill"} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" style="border-radius:8px;background:var(--sp-color-accent);color:var(--sp-color-accent-contrast)">' . esc_html__('Get Started', 'syntekpro-animations') . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"style":{"border":{"radius":"8px"}},"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" style="border-radius:8px;border-color:var(--sp-color-accent);color:var(--sp-color-accent)">' . esc_html__('View Showcase', 'syntekpro-animations') . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:syntekpro/animate --></div><!-- /wp:group --></div></div>
<!-- /wp:cover -->';

        register_block_pattern('syntekpro/hero-spotlight', array(
            'title' => __('Syntekpro Hero Spotlight', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $hero_content,
            'viewportWidth' => 1200,
        ));

        // Card stagger grid
        $cards_content = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}},"color":{"background":"var(--sp-color-bg)"}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
    <div class="wp-block-group has-background" style="padding-top:20px;padding-bottom:20px;background-color:var(--sp-color-bg)"><!-- wp:syntekpro/animate {"type":"fadeInUp","stagger":0.12,"duration":0.9,"onceOnly":true} -->
    <div class="wp-block-group"><!-- wp:heading {"textAlign":"center"} -->
    <h2 class="has-text-align-center">' . esc_html__('Feature highlights', 'syntekpro-animations') . '</h2>
    <!-- /wp:heading -->

    <!-- wp:columns {"style":{"spacing":{"blockGap":"16px"}}} -->
    <div class="wp-block-columns"><!-- wp:column -->
    <div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"var(--sp-radius-lg)","width":"1px"},"spacing":{"padding":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"color":{"background":"var(--sp-color-surface)"}},"borderColor":"gray-300","layout":{"type":"constrained"}} -->
    <div class="wp-block-group has-border-color has-gray-300-border-color has-background" style="border-width:1px;border-radius:var(--sp-radius-lg);background-color:var(--sp-color-surface);padding-top:16px;padding-right:16px;padding-bottom:16px;padding-left:16px"><!-- wp:heading {"level":4} -->
    <h4>' . esc_html__('Speedy setup', 'syntekpro-animations') . '</h4>
    <!-- /wp:heading --><!-- wp:paragraph -->
    <p>' . esc_html__('Drop in presets and tweak timing without code.', 'syntekpro-animations') . '</p>
    <!-- /wp:paragraph --></div>
    <!-- /wp:group --></div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"var(--sp-radius-lg)","width":"1px"},"spacing":{"padding":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"color":{"background":"var(--sp-color-surface)"}},"borderColor":"gray-300","layout":{"type":"constrained"}} -->
    <div class="wp-block-group has-border-color has-gray-300-border-color has-background" style="border-width:1px;border-radius:var(--sp-radius-lg);background-color:var(--sp-color-surface);padding-top:16px;padding-right:16px;padding-bottom:16px;padding-left:16px"><!-- wp:heading {"level":4} -->
    <h4>' . esc_html__('GSAP power', 'syntekpro-animations') . '</h4>
    <!-- /wp:heading --><!-- wp:paragraph -->
    <p>' . esc_html__('Use ScrollTrigger, timelines, and advanced easing.', 'syntekpro-animations') . '</p>
    <!-- /wp:paragraph --></div>
    <!-- /wp:group --></div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column"><!-- wp:group {"style":{"border":{"radius":"var(--sp-radius-lg)","width":"1px"},"spacing":{"padding":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"color":{"background":"var(--sp-color-surface)"}},"borderColor":"gray-300","layout":{"type":"constrained"}} -->
    <div class="wp-block-group has-border-color has-gray-300-border-color has-background" style="border-width:1px;border-radius:var(--sp-radius-lg);background-color:var(--sp-color-surface);padding-top:16px;padding-right:16px;padding-bottom:16px;padding-left:16px"><!-- wp:heading {"level":4} -->
    <h4>' . esc_html__('Design-safe motion', 'syntekpro-animations') . '</h4>
    <!-- /wp:heading --><!-- wp:paragraph -->
    <p>' . esc_html__('Smooth defaults that respect reduced motion.', 'syntekpro-animations') . '</p>
    <!-- /wp:paragraph --></div>
    <!-- /wp:group --></div>
    <!-- /wp:column --></div>
    <!-- /wp:columns --></div>
    <!-- /wp:syntekpro/animate --></div>
    <!-- /wp:group -->';

        register_block_pattern('syntekpro/card-stagger', array(
            'title' => __('Syntekpro Card Stagger', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $cards_content,
            'viewportWidth' => 1100,
        ));

        // CTA banner
        $cta_content = '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"30px","right":"20px","bottom":"30px","left":"20px"}},"color":{"background":"var(--sp-color-surface-strong)"},"border":{"radius":"0px"}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
    <div class="wp-block-group alignfull has-background" style="background-color:var(--sp-color-surface-strong);border-radius:0px;padding-top:30px;padding-right:20px;padding-bottom:30px;padding-left:20px"><!-- wp:syntekpro/animate {"type":"fadeInUp","duration":0.9,"onceOnly":true} -->
    <div class="wp-block-group" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
    <!-- wp:heading {"level":3,"style":{"color":{"text":"var(--sp-color-accent-contrast)"},"typography":{"lineHeight":"1.2"}}} -->
    <h3 class="has-text-color" style="color:var(--sp-color-accent-contrast);line-height:1.2">' . esc_html__('Ready to ship smoother pages?', 'syntekpro-animations') . '</h3>
<!-- /wp:heading -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
    <div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"8px"},"color":{"background":"var(--sp-color-surface)","text":"var(--sp-color-text)"}},"className":"is-style-fill"} -->
    <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" style="border-radius:8px;color:var(--sp-color-text);background-color:var(--sp-color-surface)">' . esc_html__('Explore Animations', 'syntekpro-animations') . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/cta-banner', array(
            'title' => __('Syntekpro CTA Banner', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $cta_content,
            'viewportWidth' => 1200,
        ));

        // Pricing grid (dynamic block)
        $pricing_content = '<!-- wp:syntekpro/pattern-pricing {"align":"full"} /-->';

        register_block_pattern('syntekpro/pricing-grid', array(
            'title' => __('Syntekpro Pricing Grid', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $pricing_content,
            'viewportWidth' => 1200,
        ));

        // FAQ accordion (dynamic block)
        $faq_content = '<!-- wp:syntekpro/pattern-faq /-->';

        register_block_pattern('syntekpro/faq-accordion', array(
            'title' => __('Syntekpro FAQ Accordion', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $faq_content,
            'viewportWidth' => 960,
        ));

        // Testimonial stack (dynamic block)
        $testimonial_content = '<!-- wp:syntekpro/pattern-testimonials {"align":"full"} /-->';

        register_block_pattern('syntekpro/testimonial-stack', array(
            'title' => __('Syntekpro Testimonial Stack', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $testimonial_content,
            'viewportWidth' => 1100,
        ));

        // Hero split (media + copy)
        $hero_split_content = '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"40px","right":"20px","bottom":"40px","left":"20px"}},"color":{"background":"var(--sp-color-surface-subtle)"}}} -->
<div class="wp-block-group alignfull has-background" style="background-color:var(--sp-color-surface-subtle);padding-top:40px;padding-right:20px;padding-bottom:40px;padding-left:20px"><!-- wp:columns {"style":{"spacing":{"blockGap":"24px"}},"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:syntekpro/animate {"type":"slideLeft","duration":0.9,"onceOnly":true} -->
<div class="wp-block-group" style="display:grid;gap:14px;max-width:520px;"> <!-- wp:heading {"level":1} --><h1>' . esc_html__('Launch with confident motion.', 'syntekpro-animations') . '</h1><!-- /wp:heading --> <!-- wp:paragraph --><p>' . esc_html__('Pair clear messaging with a supportive image beside it.', 'syntekpro-animations') . '</p><!-- /wp:paragraph --> <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"left"}} --><div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"10px"}},"className":"is-style-fill"} --><div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" style="border-radius:10px;background:var(--sp-color-accent);color:var(--sp-color-accent-contrast)">' . esc_html__('Get started', 'syntekpro-animations') . '</a></div><!-- /wp:button --><!-- wp:button {"style":{"border":{"radius":"10px"}},"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" style="border-radius:10px;border-color:var(--sp-color-accent);color:var(--sp-color-accent)">' . esc_html__('View demo', 'syntekpro-animations') . '</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:column --><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:syntekpro/animate {"type":"fadeIn","duration":0.9,"delay":0.1,"onceOnly":true} -->
<div class="wp-block-group" style="height:260px;border-radius:14px;background:linear-gradient(135deg,rgba(15,23,42,0.08),rgba(15,23,42,0.02));border:1px solid rgba(15,23,42,0.08);"></div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/hero-split', array(
            'title' => __('Syntekpro Hero Split', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $hero_split_content,
            'viewportWidth' => 1200,
        ));

        // Stats row
        $stats_content = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}},"color":{"background":"var(--sp-color-surface)"}},"layout":{"type":"constrained","contentSize":"1080px"}} -->
<div class="wp-block-group has-background" style="background-color:var(--sp-color-surface);padding-top:24px;padding-bottom:24px"><!-- wp:syntekpro/animate {"type":"fadeInUp","stagger":0.1,"duration":0.8,"onceOnly":true} -->
<div class="wp-block-columns" style="gap:18px"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3>' . esc_html__('4.8/5', 'syntekpro-animations') . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__('Average satisfaction', 'syntekpro-animations') . '</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3>' . esc_html__('120+', 'syntekpro-animations') . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__('Animated pages shipped', 'syntekpro-animations') . '</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3>' . esc_html__('35%', 'syntekpro-animations') . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__('Bounce rate drop', 'syntekpro-animations') . '</p><!-- /wp:paragraph --></div><!-- /wp:column --></div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/stats-row', array(
            'title' => __('Syntekpro Stats Row', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $stats_content,
            'viewportWidth' => 1080,
        ));

        // Logo strip
        $logo_strip_content = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}},"color":{"background":"var(--sp-color-surface-subtle)"}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group has-background" style="background-color:var(--sp-color-surface-subtle);padding-top:20px;padding-bottom:20px"><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size">' . esc_html__('Trusted by teams shipping polished motion:', 'syntekpro-animations') . '</p><!-- /wp:paragraph --><!-- wp:syntekpro/animate {"type":"fadeIn","stagger":0.08,"duration":0.8,"onceOnly":true} -->
<div class="wp-block-group" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;align-items:center;"> <div style="height:44px;border-radius:10px;background:rgba(15,23,42,0.06);"></div><div style="height:44px;border-radius:10px;background:rgba(15,23,42,0.06);"></div><div style="height:44px;border-radius:10px;background:rgba(15,23,42,0.06);"></div><div style="height:44px;border-radius:10px;background:rgba(15,23,42,0.06);"></div><div style="height:44px;border-radius:10px;background:rgba(15,23,42,0.06);"></div> </div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/logo-strip', array(
            'title' => __('Syntekpro Logo Strip', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $logo_strip_content,
            'viewportWidth' => 1100,
        ));

        // Steps walkthrough
        $steps_content = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}}},"layout":{"type":"constrained","contentSize":"1080px"}} -->
<div class="wp-block-group" style="padding-top:24px;padding-bottom:24px"><!-- wp:heading {"textAlign":"center"} --><h2 class="has-text-align-center">' . esc_html__('How it works', 'syntekpro-animations') . '</h2><!-- /wp:heading --><!-- wp:syntekpro/animate {"type":"fadeInUp","stagger":0.12,"duration":0.85,"onceOnly":true} -->
<div class="wp-block-columns" style="gap:18px"><!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"700"}}} --><p style="font-weight:700">' . esc_html__('1. Choose motion', 'syntekpro-animations') . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>' . esc_html__('Pick a preset or builder recipe.', 'syntekpro-animations') . '</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"700"}}} --><p style="font-weight:700">' . esc_html__('2. Apply & tweak', 'syntekpro-animations') . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>' . esc_html__('Adjust duration, delay, and easing.', 'syntekpro-animations') . '</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"700"}}} --><p style="font-weight:700">' . esc_html__('3. Publish', 'syntekpro-animations') . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>' . esc_html__('Ship with accessible defaults.', 'syntekpro-animations') . '</p><!-- /wp:paragraph --></div><!-- /wp:column --></div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/steps-walkthrough', array(
            'title' => __('Syntekpro Steps Walkthrough', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $steps_content,
            'viewportWidth' => 1080,
        ));

        // Feature checklist
        $checklist_content = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}},"color":{"background":"var(--sp-color-surface)"}},"layout":{"type":"constrained","contentSize":"1040px"}} -->
<div class="wp-block-group has-background" style="background-color:var(--sp-color-surface);padding-top:24px;padding-bottom:24px"><!-- wp:syntekpro/animate {"type":"fadeIn","stagger":0.1,"duration":0.85,"onceOnly":true} -->
<div class="wp-block-group" style="display:grid;gap:12px;max-width:720px;"> <!-- wp:heading {"level":3} --><h3>' . esc_html__('What you get', 'syntekpro-animations') . '</h3><!-- /wp:heading --> <!-- wp:list {"className":"is-style-checklist"} --><ul class="is-style-checklist"><li>' . esc_html__('GSAP-ready motion blocks', 'syntekpro-animations') . '</li><li>' . esc_html__('Reduced-motion aware defaults', 'syntekpro-animations') . '</li><li>' . esc_html__('Timeline and builder options', 'syntekpro-animations') . '</li></ul><!-- /wp:list --> </div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/feature-checklist', array(
            'title' => __('Syntekpro Feature Checklist', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $checklist_content,
            'viewportWidth' => 1040,
        ));

        // Comparison table
        $comparison_content = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"26px","bottom":"26px"}},"color":{"background":"var(--sp-color-surface-subtle)"}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group has-background" style="background-color:var(--sp-color-surface-subtle);padding-top:26px;padding-bottom:26px"><!-- wp:heading {"textAlign":"center"} --><h2 class="has-text-align-center">' . esc_html__('Compare plans', 'syntekpro-animations') . '</h2><!-- /wp:heading --><!-- wp:syntekpro/animate {"type":"fadeInUp","duration":0.85,"onceOnly":true} -->
<figure class="wp-block-table"><table><thead><tr><th></th><th>' . esc_html__('Essential', 'syntekpro-animations') . '</th><th>' . esc_html__('Get+', 'syntekpro-animations') . '</th></tr></thead><tbody><tr><td>' . esc_html__('Animation presets', 'syntekpro-animations') . '</td><td>' . esc_html__('30', 'syntekpro-animations') . '</td><td>' . esc_html__('60+', 'syntekpro-animations') . '</td></tr><tr><td>' . esc_html__('Timeline builder', 'syntekpro-animations') . '</td><td>—</td><td>✔</td></tr><tr><td>' . esc_html__('Support', 'syntekpro-animations') . '</td><td>' . esc_html__('Email', 'syntekpro-animations') . '</td><td>' . esc_html__('Priority', 'syntekpro-animations') . '</td></tr></tbody></table></figure>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/comparison-table', array(
            'title' => __('Syntekpro Comparison Table', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $comparison_content,
            'viewportWidth' => 1100,
        ));

        // Newsletter band
        $newsletter_content = '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"26px","right":"20px","bottom":"26px","left":"20px"}},"color":{"background":"var(--sp-color-surface-strong)"}},"layout":{"type":"constrained","contentSize":"1040px"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:var(--sp-color-surface-strong);padding-top:26px;padding-right:20px;padding-bottom:26px;padding-left:20px"><!-- wp:syntekpro/animate {"type":"fadeInUp","duration":0.9,"onceOnly":true} -->
<div class="wp-block-group" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;"> <!-- wp:heading {"level":3,"style":{"color":{"text":"var(--sp-color-accent-contrast)"}}} --><h3 class="has-text-color" style="color:var(--sp-color-accent-contrast)">' . esc_html__('Stay ahead on motion.', 'syntekpro-animations') . '</h3><!-- /wp:heading --> <!-- wp:paragraph {"style":{"color":{"text":"var(--sp-color-accent-contrast)"}}} --><p class="has-text-color" style="color:var(--sp-color-accent-contrast);opacity:0.9">' . esc_html__('Monthly tips, no spam.', 'syntekpro-animations') . '</p><!-- /wp:paragraph --> <!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"10px"}},"className":"is-style-fill"} --><div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" style="border-radius:10px;background:#ffffff;color:var(--sp-color-text)">' . esc_html__('Subscribe', 'syntekpro-animations') . '</a></div><!-- /wp:button --></div><!-- /wp:buttons --> </div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/newsletter-band', array(
            'title' => __('Syntekpro Newsletter Band', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $newsletter_content,
            'viewportWidth' => 1040,
        ));

        // Gallery tiles
        $gallery_tiles_content = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:24px;padding-bottom:24px"><!-- wp:heading {"textAlign":"center"} --><h2 class="has-text-align-center">' . esc_html__('Recent work', 'syntekpro-animations') . '</h2><!-- /wp:heading --><!-- wp:syntekpro/animate {"type":"fadeIn","stagger":0.08,"duration":0.85,"onceOnly":true} -->
<div class="wp-block-group" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;"> <div style="height:140px;border-radius:12px;background:linear-gradient(135deg,rgba(15,23,42,0.08),rgba(15,23,42,0.02));"></div><div style="height:140px;border-radius:12px;background:linear-gradient(135deg,rgba(15,23,42,0.08),rgba(15,23,42,0.02));"></div><div style="height:140px;border-radius:12px;background:linear-gradient(135deg,rgba(15,23,42,0.08),rgba(15,23,42,0.02));"></div><div style="height:140px;border-radius:12px;background:linear-gradient(135deg,rgba(15,23,42,0.08),rgba(15,23,42,0.02));"></div><div style="height:140px;border-radius:12px;background:linear-gradient(135deg,rgba(15,23,42,0.08),rgba(15,23,42,0.02));"></div><div style="height:140px;border-radius:12px;background:linear-gradient(135deg,rgba(15,23,42,0.08),rgba(15,23,42,0.02));"></div> </div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/gallery-tiles', array(
            'title' => __('Syntekpro Gallery Tiles', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $gallery_tiles_content,
            'viewportWidth' => 1100,
        ));

        // Testimonial highlight
        $testimonial_highlight_content = '<!-- wp:group {"style":{"spacing":{"padding":{"top":"24px","bottom":"24px"}},"color":{"background":"var(--sp-color-surface)"}},"layout":{"type":"constrained","contentSize":"860px"}} -->
<div class="wp-block-group has-background" style="background-color:var(--sp-color-surface);padding-top:24px;padding-bottom:24px"><!-- wp:syntekpro/animate {"type":"fadeInUp","duration":0.9,"onceOnly":true} -->
<div class="wp-block-group" style="display:grid;gap:10px;"> <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","fontWeight":"700"}}} --><p style="font-size:18px;font-weight:700">' . esc_html__('“Motion finally feels consistent across our site.”', 'syntekpro-animations') . '</p><!-- /wp:paragraph --> <!-- wp:paragraph {"style":{"color":{"text":"var(--sp-color-text)"}}} --><p class="has-text-color" style="color:var(--sp-color-text);opacity:0.8">' . esc_html__('Casey Morgan, Product Design Lead', 'syntekpro-animations') . '</p><!-- /wp:paragraph --> <div style="display:flex;gap:8px;align-items:center;"><span style="display:inline-block;padding:6px 10px;border-radius:999px;background:var(--sp-color-surface-subtle);border:1px solid rgba(15,23,42,0.08);">' . esc_html__('Customer story', 'syntekpro-animations') . '</span></div> </div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/testimonial-highlight', array(
            'title' => __('Syntekpro Testimonial Highlight', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $testimonial_highlight_content,
            'viewportWidth' => 860,
        ));

        // CTA minimal
        $cta_minimal_content = '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"22px","right":"20px","bottom":"22px","left":"20px"}},"color":{"background":"var(--sp-color-surface-subtle)"}},"layout":{"type":"constrained","contentSize":"980px"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:var(--sp-color-surface-subtle);padding-top:22px;padding-right:20px;padding-bottom:22px;padding-left:20px"><!-- wp:syntekpro/animate {"type":"fadeIn","duration":0.8,"onceOnly":true} -->
<div class="wp-block-group" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;"> <!-- wp:heading {"level":4} --><h4>' . esc_html__('Ready when you are.', 'syntekpro-animations') . '</h4><!-- /wp:heading --> <!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"10px"}},"className":"is-style-fill"} --><div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" style="border-radius:10px;background:var(--sp-color-accent);color:var(--sp-color-accent-contrast)">' . esc_html__('Start a project', 'syntekpro-animations') . '</a></div><!-- /wp:button --></div><!-- /wp:buttons --> </div>
<!-- /wp:syntekpro/animate --></div>
<!-- /wp:group -->';

        register_block_pattern('syntekpro/cta-minimal', array(
            'title' => __('Syntekpro CTA Minimal', 'syntekpro-animations'),
            'categories' => array('syntekpro'),
            'content' => $cta_minimal_content,
            'viewportWidth' => 980,
        ));

        // Register custom patterns from backend JSON
        $custom_patterns = get_option('syntekpro_pattern_custom', array());
        if (is_array($custom_patterns)) {
            foreach ($custom_patterns as $custom) {
                $slug = isset($custom['slug']) ? sanitize_title($custom['slug']) : '';
                $title = isset($custom['title']) ? $custom['title'] : '';
                $desc = isset($custom['description']) ? $custom['description'] : '';
                $content = isset($custom['content']) ? $custom['content'] : '';
                if (empty($slug) || empty($title) || empty($content)) {
                    continue;
                }
                // Ensure slug is namespaced
                if (strpos($slug, 'syntekpro/') !== 0) {
                    $slug = 'syntekpro/' . $slug;
                }
                register_block_pattern($slug, array(
                    'title' => wp_strip_all_tags($title),
                    'categories' => array('syntekpro'),
                    'description' => wp_strip_all_tags($desc),
                    'content' => $content,
                    'viewportWidth' => 1100,
                ));
            }
        }
    }

    /**
     * Register editor and frontend scripts/styles for all blocks
     */
    private function register_block_assets() {
        $blocks = array(
            'element',
            'heading',
            'carousel',
            'popup',
            'toc',
            'navigation',
            '3d-model',
            'spline',
            'icon-list',
            'tabs'
        );

        foreach ($blocks as $block_name) {
            // Register block editor script
            wp_register_script(
                'syntekpro-' . $block_name . '-block-editor',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/blocks/' . $block_name . '/block.js',
                array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-rich-text'),
                SYNTEKPRO_ANIM_VERSION,
                true
            );

            // Register block editor style
            wp_register_style(
                'syntekpro-' . $block_name . '-block-editor-style',
                SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/blocks/' . $block_name . '/editor.css',
                array(),
                SYNTEKPRO_ANIM_VERSION
            );

            // Register block frontend style (fallback if block defines it)
            if (file_exists(SYNTEKPRO_ANIM_PLUGIN_DIR . 'assets/css/blocks/' . $block_name . '/style.css')) {
                wp_register_style(
                    'syntekpro-' . $block_name . '-block-style',
                    SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/blocks/' . $block_name . '/style.css',
                    array(),
                    SYNTEKPRO_ANIM_VERSION
                );
            }

            // Register block frontend script (if exists)
            if (file_exists(SYNTEKPRO_ANIM_PLUGIN_DIR . 'assets/js/blocks/' . $block_name . '/script.js')) {
                wp_register_script(
                    'syntekpro-' . $block_name . '-block-script',
                    SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/blocks/' . $block_name . '/script.js',
                    array(),
                    SYNTEKPRO_ANIM_VERSION,
                    true
                );
            }
        }
    }

    /**
     * Render callback for the dynamic block on frontend
     */
    public function render_block($attributes, $content) {
        // Debug logging
        error_log('=== Syntekpro Render Block Called ===');
        error_log('Attributes: ' . print_r($attributes, true));
        error_log('Content length: ' . strlen($content));
        error_log('Content preview: ' . substr($content, 0, 300));
        
        // Extract and sanitize attributes
        $type = isset($attributes['type']) ? sanitize_text_field($attributes['type']) : 'fadeInUp';
        $duration = isset($attributes['duration']) ? floatval($attributes['duration']) : 1;
        $delay = isset($attributes['delay']) ? floatval($attributes['delay']) : 0;
        $ease = isset($attributes['ease']) ? sanitize_text_field($attributes['ease']) : 'power2.out';
        $trigger = isset($attributes['trigger']) ? sanitize_text_field($attributes['trigger']) : 'scroll';
        $use_scroll_trigger = isset($attributes['useScrollTrigger']) ? $attributes['useScrollTrigger'] : true;
        $stagger = isset($attributes['stagger']) ? floatval($attributes['stagger']) : 0;
        $repeat = isset($attributes['repeatCount']) ? intval($attributes['repeatCount']) : 0;
        $start_position = isset($attributes['startPosition']) ? sanitize_text_field($attributes['startPosition']) : 'top 80%';
        $markers = isset($attributes['markers']) ? ($attributes['markers'] ? 'true' : 'false') : 'false';
        $once_only = isset($attributes['onceOnly']) ? ($attributes['onceOnly'] ? 'true' : 'false') : 'true';
        $engine = isset($attributes['engine']) ? sanitize_text_field($attributes['engine']) : 'auto';
        $global_engine = get_option('syntekpro_anim_engine', 'auto');
        $effective_engine = ($engine && $engine !== 'auto') ? $engine : $global_engine;

        // Process inner blocks content if it exists
        $inner_content = '';
        if (!empty($content)) {
            // The content contains the inner blocks HTML
            $inner_content = do_blocks($content);
            error_log('Processed inner content length: ' . strlen($inner_content));
        } else {
            error_log('WARNING: Block has no content');
        }

        // Build the animation wrapper div with data attributes
        $unique_id = 'sp-anim-' . uniqid();
        $output = sprintf(
            '<div id="%s" class="sp-animate" data-animation="%s" data-duration="%f" data-delay="%f" data-trigger="%s" data-ease="%s" data-stagger="%f" data-repeat="%d" data-start="%s" data-markers="%s" data-once="%s" data-engine="%s">%s</div>',
            esc_attr($unique_id),
            esc_attr($type),
            $duration,
            $delay,
            esc_attr($trigger),
            esc_attr($ease),
            $stagger,
            $repeat,
            esc_attr($start_position),
            esc_attr($markers),
            esc_attr($once_only),
            esc_attr($effective_engine),
            $inner_content
        );

        // Add HTML comment for debugging
        $output = '<!-- Syntekpro Animation Block START id=' . $unique_id . ' -->' . "\n" . $output . "\n" . '<!-- Syntekpro Animation Block END -->';

        error_log('Final block output length: ' . strlen($output));
        error_log('Block output preview: ' . substr($output, 0, 300));
        return $output;
    }
}

new Syntekpro_Animations_Gutenberg();