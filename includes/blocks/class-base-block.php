<?php
/**
 * Base Block Class
 * All blocks extend this class for consistent functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class Syntekpro_Base_Block {
    /**
     * Block name/slug
     * Must be overridden in child class
     */
    protected $block_name = '';
    
    /**
     * Block title
     * Must be overridden in child class
     */
    protected $block_title = '';
    
    /**
     * Block description
     */
    protected $block_description = '';
    
    /**
     * Block icon
     */
    protected $block_icon = 'controls-play';
    
    /**
     * Block category
     */
    protected $block_category = 'design';
    
    /**
     * Block keywords
     */
    protected $block_keywords = array();
    
    /**
     * Block attributes
     */
    protected $block_attributes = array();
    
    /**
     * Block supports
     */
    protected $block_supports = array(
        'align' => true,
        'className' => true,
        'html' => false,
        'innerBlocks' => true
    );

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_block'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
    }

    /**
     * Register the block
     */
    public function register_block() {
        if (empty($this->block_name)) {
            return;
        }

        // Register block script
        wp_register_script(
            $this->get_editor_script_handle(),
            $this->get_editor_script_url(),
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'),
            SYNTEKPRO_ANIM_VERSION,
            true
        );

        // Register block style
        wp_register_style(
            $this->get_style_handle(),
            $this->get_style_url(),
            array(),
            SYNTEKPRO_ANIM_VERSION
        );

        // Register block type
        register_block_type('syntekpro-animations/' . $this->block_name, array(
            'editor_script' => $this->get_editor_script_handle(),
            'style' => $this->get_style_handle(),
            'render_callback' => array($this, 'render_block'),
            'attributes' => $this->get_block_attributes(),
            'supports' => $this->block_supports,
            'apiVersion' => 2
        ));
    }

    /**
     * Get block attributes
     */
    protected function get_block_attributes() {
        return $this->block_attributes;
    }

    /**
     * Get editor script handle
     */
    protected function get_editor_script_handle() {
        return 'syntekpro-' . $this->block_name . '-editor';
    }

    /**
     * Get editor script URL
     */
    protected function get_editor_script_url() {
        return SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/js/blocks/' . $this->block_name . '/block.js';
    }

    /**
     * Get style handle
     */
    protected function get_style_handle() {
        return 'syntekpro-' . $this->block_name . '-style';
    }

    /**
     * Get style URL
     */
    protected function get_style_url() {
        return SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/blocks/' . $this->block_name . '/style.css';
    }

    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets() {
        wp_enqueue_style('syntekpro-block-editor-style', SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/block-editor.css', array(), SYNTEKPRO_ANIM_VERSION);
    }

    /**
     * Render block on frontend
     * Must be overridden in child class
     */
    abstract public function render_block($attributes, $content);
}
