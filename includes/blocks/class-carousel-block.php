<?php
/**
 * Carousel Block
 * Turn any content into Slider or Carousel
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Carousel_Block extends Syntekpro_Base_Block {
    
    protected $block_name = 'carousel';
    protected $block_title = 'Slider/Carousel';
    protected $block_description = 'Turn any content into Slider or Carousel';
    protected $block_icon = 'images-alt2';
    protected $block_category = 'media';
    protected $block_keywords = array('carousel', 'slider', 'gallery');
    
    protected $block_attributes = array(
        'slidesPerView' => array('type' => 'number', 'default' => 1),
        'spaceBetween' => array('type' => 'number', 'default' => 20),
        'autoplay' => array('type' => 'boolean', 'default' => false),
        'autoplayDelay' => array('type' => 'number', 'default' => 5000),
        'navigation' => array('type' => 'boolean', 'default' => true),
        'pagination' => array('type' => 'boolean', 'default' => true),
        'effect' => array('type' => 'string', 'default' => 'slide'),
        'loop' => array('type' => 'boolean', 'default' => true),
        'speed' => array('type' => 'number', 'default' => 500)
    );

    public function render_block($attributes, $content) {
        $unique_id = 'carousel-' . uniqid();
        
        $output = sprintf(
            '<div id="%s" class="syntekpro-carousel" data-slides-per-view="%d" data-space-between="%d">%s</div>',
            esc_attr($unique_id),
            intval($attributes['slidesPerView']),
            intval($attributes['spaceBetween']),
            do_blocks($content)
        );
        
        return $output;
    }
}
