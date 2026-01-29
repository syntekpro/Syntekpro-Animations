<?php
/**
 * Block Registry
 * Central registry for all blocks in the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Block_Registry {
    private static $instance = null;
    private $blocks = array();

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_blocks();
    }

    /**
     * Load all block classes
     */
    private function load_blocks() {
        $blocks_dir = SYNTEKPRO_ANIM_PLUGIN_DIR . 'includes/blocks/';
        
        // List of block class files to load
        $block_files = array(
            'class-element-block.php',
            'class-heading-block.php',
            'class-carousel-block.php',
            'class-popup-block.php',
            'class-toc-block.php',
            'class-navigation-block.php',
            'class-3d-model-block.php',
            'class-spline-block.php',
            'class-icon-list-block.php',
            'class-tabs-block.php',
        );

        foreach ($block_files as $file) {
            $file_path = $blocks_dir . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
                // Instantiate the block class
                // Block names derived from file names: class-element-block.php -> Syntekpro_Element_Block
                $class_name = $this->get_class_name_from_file($file);
                if (class_exists($class_name)) {
                    new $class_name();
                }
            }
        }
    }

    /**
     * Convert filename to class name
     * class-element-block.php -> Syntekpro_Element_Block
     */
    private function get_class_name_from_file($filename) {
        $name = str_replace('class-', '', $filename);
        $name = str_replace('.php', '', $name);
        $parts = explode('-', $name);
        $parts = array_map('ucfirst', $parts);
        return 'Syntekpro_' . implode('_', $parts);
    }

    /**
     * Register a block
     */
    public function register_block($block_name, $block_class) {
        $this->blocks[$block_name] = $block_class;
    }

    /**
     * Get all registered blocks
     */
    public function get_blocks() {
        return $this->blocks;
    }
}

// Note: Registry is initialized from main plugin file via plugins_loaded hook
