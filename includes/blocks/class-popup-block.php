<?php
/**
 * Popup/Sliding Panel Block
 * Popups, sliding panels, mega menus, tooltips
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Popup_Block extends Syntekpro_Base_Block {
    
    protected $block_name = 'popup';
    protected $block_title = 'Sliding Panel/Popup';
    protected $block_description = 'Popups, sliding panels, mega menus, tooltips';
    protected $block_icon = 'layout';
    protected $block_category = 'interactive';
    protected $block_keywords = array('popup', 'modal', 'panel', 'sidebar');
    
    protected $block_attributes = array(
        'triggerType' => array('type' => 'string', 'default' => 'button'),
        'popupType' => array('type' => 'string', 'default' => 'modal'),
        'position' => array('type' => 'string', 'default' => 'center'),
        'animation' => array('type' => 'string', 'default' => 'fadeIn'),
        'triggerText' => array('type' => 'string', 'default' => 'Open Popup'),
        'closeButton' => array('type' => 'boolean', 'default' => true),
        'clickOutsideClose' => array('type' => 'boolean', 'default' => true),
        'width' => array('type' => 'string', 'default' => '500px'),
        'height' => array('type' => 'string', 'default' => 'auto'),
        'backgroundColor' => array('type' => 'string', 'default' => '#ffffff')
    );

    public function render_block($attributes, $content) {
        $unique_id = 'popup-' . uniqid();
        
        $output = sprintf(
            '<div id="%s" class="syntekpro-popup" data-popup-type="%s" data-position="%s" data-animation="%s">
                <button class="syntekpro-popup-trigger">%s</button>
                <div class="syntekpro-popup-content" style="background-color: %s; width: %s; height: %s;">
                    %s
                    %s
                </div>
            </div>',
            esc_attr($unique_id),
            esc_attr($attributes['popupType']),
            esc_attr($attributes['position']),
            esc_attr($attributes['animation']),
            esc_html($attributes['triggerText']),
            sanitize_hex_color($attributes['backgroundColor']),
            sanitize_text_field($attributes['width']),
            sanitize_text_field($attributes['height']),
            $attributes['closeButton'] ? '<button class="syntekpro-popup-close">&times;</button>' : '',
            do_blocks($content)
        );
        
        return $output;
    }
}
