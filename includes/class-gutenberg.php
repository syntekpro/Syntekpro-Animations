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
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
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
            array('syntekpro-gsap', 'syntekpro-scrolltrigger'),  // Depends on both GSAP and ScrollTrigger
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
                'onceOnly' => array('type' => 'boolean', 'default' => true)
            ),
            'supports' => array(
                'align' => true,
                'className' => true,
                'html' => false,
                'innerBlocks' => true
            ),
            'apiVersion' => 2
        ));
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
            '<div id="%s" class="sp-animate" data-animation="%s" data-duration="%f" data-delay="%f" data-trigger="%s" data-ease="%s" data-stagger="%f" data-repeat="%d" data-start="%s" data-markers="%s" data-once="%s">%s</div>',
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