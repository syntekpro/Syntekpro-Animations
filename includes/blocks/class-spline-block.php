<?php
/**
 * Spline 3D Viewer Block
 * Add Interactive 3d on your site with Spline App
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Spline_Block extends Syntekpro_Base_Block {
    
    protected $block_name = 'spline';
    protected $block_title = 'Spline 3D Viewer';
    protected $block_description = 'Add Interactive 3d on your site with Spline App';
    protected $block_icon = 'format-video';
    protected $block_category = 'media';
    protected $block_keywords = array('spline', '3d', 'interactive', 'viewer');
    
    protected $block_attributes = array(
        'splineUrl' => array('type' => 'string', 'default' => ''),
        'width' => array('type' => 'string', 'default' => '100%'),
        'height' => array('type' => 'string', 'default' => '600px'),
        'fullScreen' => array('type' => 'boolean', 'default' => false),
        'allowInteraction' => array('type' => 'boolean', 'default' => true)
    );

    public function render_block($attributes, $content) {
        $unique_id = 'spline-' . uniqid();
        
        $output = sprintf(
            '<div id="%s" class="syntekpro-spline" style="width: %s; height: %s;">
                <iframe 
                    src="%s" 
                    style="width: 100%%; height: 100%%; border: none; border-radius: 8px;">
                </iframe>
            </div>',
            esc_attr($unique_id),
            sanitize_text_field($attributes['width']),
            sanitize_text_field($attributes['height']),
            esc_url($attributes['splineUrl'])
        );
        
        return $output;
    }
}
