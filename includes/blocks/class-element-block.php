<?php
/**
 * Element Block
 * Add fluent HTML elements with design and interactions
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Element_Block extends Syntekpro_Base_Block {
    
    protected $block_name = 'element';
    protected $block_title = 'Element Block';
    protected $block_description = 'Add fluent HTML elements with design and interactions';
    protected $block_icon = 'editor-code';
    protected $block_category = 'design';
    protected $block_keywords = array('element', 'html', 'div', 'container');
    
    protected $block_attributes = array(
        'element' => array(
            'type' => 'string',
            'default' => 'div'
        ),
        'elementClass' => array(
            'type' => 'string',
            'default' => ''
        ),
        'elementId' => array(
            'type' => 'string',
            'default' => ''
        ),
        'backgroundColor' => array(
            'type' => 'string',
            'default' => ''
        ),
        'textColor' => array(
            'type' => 'string',
            'default' => ''
        ),
        'padding' => array(
            'type' => 'string',
            'default' => '0'
        ),
        'margin' => array(
            'type' => 'string',
            'default' => '0'
        ),
        'borderRadius' => array(
            'type' => 'string',
            'default' => '0'
        ),
        'borderColor' => array(
            'type' => 'string',
            'default' => ''
        ),
        'borderWidth' => array(
            'type' => 'string',
            'default' => '0'
        ),
        'minHeight' => array(
            'type' => 'string',
            'default' => ''
        ),
        'display' => array(
            'type' => 'string',
            'default' => 'block'
        ),
        'flexDirection' => array(
            'type' => 'string',
            'default' => 'row'
        ),
        'alignItems' => array(
            'type' => 'string',
            'default' => 'stretch'
        ),
        'justifyContent' => array(
            'type' => 'string',
            'default' => 'flex-start'
        ),
        'opacity' => array(
            'type' => 'number',
            'default' => 1
        ),
        'boxShadow' => array(
            'type' => 'boolean',
            'default' => false
        ),
        'transition' => array(
            'type' => 'boolean',
            'default' => false
        )
    );

    /**
     * Render element block
     */
    public function render_block($attributes, $content) {
        $element = isset($attributes['element']) ? sanitize_text_field($attributes['element']) : 'div';
        $class = isset($attributes['elementClass']) ? sanitize_text_field($attributes['elementClass']) : '';
        $id = isset($attributes['elementId']) ? sanitize_text_field($attributes['elementId']) : '';
        
        // Build inline styles
        $styles = array();
        
        if (!empty($attributes['backgroundColor'])) {
            $styles[] = 'background-color: ' . sanitize_hex_color($attributes['backgroundColor']);
        }
        
        if (!empty($attributes['textColor'])) {
            $styles[] = 'color: ' . sanitize_hex_color($attributes['textColor']);
        }
        
        if (!empty($attributes['padding'])) {
            $styles[] = 'padding: ' . sanitize_text_field($attributes['padding']);
        }
        
        if (!empty($attributes['margin'])) {
            $styles[] = 'margin: ' . sanitize_text_field($attributes['margin']);
        }
        
        if (!empty($attributes['borderRadius'])) {
            $styles[] = 'border-radius: ' . sanitize_text_field($attributes['borderRadius']);
        }
        
        if (!empty($attributes['borderColor']) && !empty($attributes['borderWidth'])) {
            $styles[] = 'border: ' . sanitize_text_field($attributes['borderWidth']) . ' solid ' . sanitize_hex_color($attributes['borderColor']);
        }
        
        if (!empty($attributes['minHeight'])) {
            $styles[] = 'min-height: ' . sanitize_text_field($attributes['minHeight']);
        }
        
        if (!empty($attributes['display'])) {
            $styles[] = 'display: ' . sanitize_text_field($attributes['display']);
        }
        
        if ($attributes['display'] === 'flex') {
            $styles[] = 'flex-direction: ' . sanitize_text_field($attributes['flexDirection']);
            $styles[] = 'align-items: ' . sanitize_text_field($attributes['alignItems']);
            $styles[] = 'justify-content: ' . sanitize_text_field($attributes['justifyContent']);
        }
        
        if ($attributes['opacity'] !== 1) {
            $styles[] = 'opacity: ' . floatval($attributes['opacity']);
        }
        
        if ($attributes['boxShadow']) {
            $styles[] = 'box-shadow: 0 2px 8px rgba(0,0,0,0.1)';
        }
        
        if ($attributes['transition']) {
            $styles[] = 'transition: all 0.3s ease';
        }
        
        $style_attr = !empty($styles) ? 'style="' . esc_attr(implode('; ', $styles)) . '"' : '';
        $class_attr = !empty($class) ? 'class="' . esc_attr($class) . '"' : '';
        $id_attr = !empty($id) ? 'id="' . esc_attr($id) . '"' : '';
        
        $output = sprintf(
            '<%1$s %2$s %3$s %4$s>%5$s</%1$s>',
            esc_html($element),
            $style_attr,
            $class_attr,
            $id_attr,
            do_blocks($content)
        );
        
        return $output;
    }
}
