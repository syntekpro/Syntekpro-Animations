<?php
/**
 * Icon List Block
 * Make advanced extra lists with icon and text
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Icon_List_Block extends Syntekpro_Base_Block {
    
    protected $block_name = 'icon-list';
    protected $block_title = 'Icon Lists';
    protected $block_description = 'Make advanced extra lists with icon and text';
    protected $block_icon = 'list-view';
    protected $block_category = 'text';
    protected $block_keywords = array('list', 'icon', 'items');
    
    protected $block_attributes = array(
        'items' => array('type' => 'array', 'default' => array()),
        'layout' => array('type' => 'string', 'default' => 'vertical'),
        'iconSize' => array('type' => 'string', 'default' => '24px'),
        'iconColor' => array('type' => 'string', 'default' => '#000000'),
        'textColor' => array('type' => 'string', 'default' => '#000000'),
        'spacing' => array('type' => 'string', 'default' => '16px'),
        'alignment' => array('type' => 'string', 'default' => 'left')
    );

    public function render_block($attributes, $content) {
        $unique_id = 'icon-list-' . uniqid();
        
        $output = sprintf(
            '<ul id="%s" class="syntekpro-icon-list" data-layout="%s">%s</ul>',
            esc_attr($unique_id),
            esc_attr($attributes['layout']),
            do_blocks($content)
        );
        
        return $output;
    }
}
