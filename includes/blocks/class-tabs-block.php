<?php
/**
 * Tabs Block
 * Add tabs to any kind of content on your site
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Tabs_Block extends Syntekpro_Base_Block {
    
    protected $block_name = 'tabs';
    protected $block_title = 'Tabs for anything';
    protected $block_description = 'Add tabs to any kind of content on your site';
    protected $block_icon = 'layout';
    protected $block_category = 'layout';
    protected $block_keywords = array('tabs', 'tabbed', 'content');
    
    protected $block_attributes = array(
        'tabs' => array('type' => 'array', 'default' => array()),
        'activeTab' => array('type' => 'number', 'default' => 0),
        'tabStyle' => array('type' => 'string', 'default' => 'default'),
        'tabAlignment' => array('type' => 'string', 'default' => 'left'),
        'backgroundColor' => array('type' => 'string', 'default' => '#f5f5f5'),
        'activeColor' => array('type' => 'string', 'default' => '#0073aa'),
        'textColor' => array('type' => 'string', 'default' => '#000000'),
        'animation' => array('type' => 'string', 'default' => 'fade')
    );

    public function render_block($attributes, $content) {
        $unique_id = 'tabs-' . uniqid();
        
        $output = sprintf(
            '<div id="%s" class="syntekpro-tabs" data-tab-style="%s" data-animation="%s">
                <div class="syntekpro-tabs-nav">%s</div>
                <div class="syntekpro-tabs-content">%s</div>
            </div>',
            esc_attr($unique_id),
            esc_attr($attributes['tabStyle']),
            esc_attr($attributes['animation']),
            '<button class="tab-button active">Tab 1</button><button class="tab-button">Tab 2</button>',
            do_blocks($content)
        );
        
        return $output;
    }
}
