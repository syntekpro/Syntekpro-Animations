<?php
/**
 * Navigation Block
 * Make custom headers and complex menus
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Navigation_Block extends Syntekpro_Base_Block {
    
    protected $block_name = 'navigation';
    protected $block_title = 'Navigation Element';
    protected $block_description = 'Make custom headers and complex menus';
    protected $block_icon = 'menu';
    protected $block_category = 'common';
    protected $block_keywords = array('navigation', 'menu', 'header');
    
    protected $block_attributes = array(
        'menuLocation' => array('type' => 'string', 'default' => ''),
        'style' => array('type' => 'string', 'default' => 'horizontal'),
        'alignment' => array('type' => 'string', 'default' => 'left'),
        'backgroundColor' => array('type' => 'string', 'default' => ''),
        'textColor' => array('type' => 'string', 'default' => '#000000'),
        'hoverEffect' => array('type' => 'string', 'default' => 'underline'),
        'stickyNav' => array('type' => 'boolean', 'default' => false),
        'mobileMenu' => array('type' => 'boolean', 'default' => true)
    );

    public function render_block($attributes, $content) {
        $unique_id = 'nav-' . uniqid();
        
        $styles = array('text-align: ' . esc_attr($attributes['alignment']));
        if (!empty($attributes['backgroundColor'])) {
            $styles[] = 'background-color: ' . sanitize_hex_color($attributes['backgroundColor']);
        }
        $styles[] = 'color: ' . sanitize_hex_color($attributes['textColor']);
        
        $output = sprintf(
            '<nav id="%s" class="syntekpro-navigation" style="%s">%s</nav>',
            esc_attr($unique_id),
            esc_attr(implode('; ', $styles)),
            do_blocks($content)
        );
        
        return $output;
    }
}
