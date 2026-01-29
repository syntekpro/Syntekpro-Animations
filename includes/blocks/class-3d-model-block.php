<?php
/**
 * 3D Model Viewer Block
 * Add real 3d on your site with AR and camera
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_3d_Model_Block extends Syntekpro_Base_Block {
    
    protected $block_name = '3d-model';
    protected $block_title = '3D Model Viewer';
    protected $block_description = 'Add real 3d on your site with AR and camera';
    protected $block_icon = 'format-video';
    protected $block_category = 'media';
    protected $block_keywords = array('3d', 'model', 'viewer', 'ar');
    
    protected $block_attributes = array(
        'modelUrl' => array('type' => 'string', 'default' => ''),
        'modelFormat' => array('type' => 'string', 'default' => 'gltf'),
        'width' => array('type' => 'string', 'default' => '100%'),
        'height' => array('type' => 'string', 'default' => '500px'),
        'autoRotate' => array('type' => 'boolean', 'default' => true),
        'cameraControl' => array('type' => 'boolean', 'default' => true),
        'arSupport' => array('type' => 'boolean', 'default' => false),
        'backgroundColor' => array('type' => 'string', 'default' => '#ffffff'),
        'lighting' => array('type' => 'string', 'default' => 'default')
    );

    public function render_block($attributes, $content) {
        $unique_id = '3d-model-' . uniqid();
        
        $output = sprintf(
            '<div id="%s" class="syntekpro-3d-model" 
                data-model-url="%s" 
                data-format="%s"
                style="width: %s; height: %s; background-color: %s;">
                <p>3D Model loading...</p>
            </div>',
            esc_attr($unique_id),
            esc_url($attributes['modelUrl']),
            esc_attr($attributes['modelFormat']),
            sanitize_text_field($attributes['width']),
            sanitize_text_field($attributes['height']),
            sanitize_hex_color($attributes['backgroundColor'])
        );
        
        return $output;
    }
}
