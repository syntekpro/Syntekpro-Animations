<?php
/**
 * Advanced Heading Block
 * Add headings with text gradients, numbered headings
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Heading_Block extends Syntekpro_Base_Block {
    
    protected $block_name = 'heading';
    protected $block_title = 'Advanced Heading';
    protected $block_description = 'Add headings with text gradients and numbered headings';
    protected $block_icon = 'heading';
    protected $block_category = 'text';
    protected $block_keywords = array('heading', 'title', 'h1', 'h2', 'h3');
    
    protected $block_attributes = array(
        'content' => array(
            'type' => 'string',
            'default' => 'Your Heading Here'
        ),
        'level' => array(
            'type' => 'number',
            'default' => 2
        ),
        'textAlign' => array(
            'type' => 'string',
            'default' => 'left'
        ),
        'textColor' => array(
            'type' => 'string',
            'default' => '#000000'
        ),
        'fontSize' => array(
            'type' => 'string',
            'default' => '32px'
        ),
        'fontWeight' => array(
            'type' => 'string',
            'default' => 'bold'
        ),
        'lineHeight' => array(
            'type' => 'string',
            'default' => '1.2'
        ),
        'useGradient' => array(
            'type' => 'boolean',
            'default' => false
        ),
        'gradientColor1' => array(
            'type' => 'string',
            'default' => '#ff0000'
        ),
        'gradientColor2' => array(
            'type' => 'string',
            'default' => '#0000ff'
        ),
        'gradientAngle' => array(
            'type' => 'number',
            'default' => 90
        ),
        'useNumbering' => array(
            'type' => 'boolean',
            'default' => false
        ),
        'numberStyle' => array(
            'type' => 'string',
            'default' => 'circle'
        ),
        'numberColor' => array(
            'type' => 'string',
            'default' => '#000000'
        ),
        'numberSize' => array(
            'type' => 'string',
            'default' => '40px'
        ),
        'numberBgColor' => array(
            'type' => 'string',
            'default' => 'transparent'
        ),
        'letterSpacing' => array(
            'type' => 'string',
            'default' => 'normal'
        ),
        'textTransform' => array(
            'type' => 'string',
            'default' => 'none'
        ),
        'textShadow' => array(
            'type' => 'boolean',
            'default' => false
        )
    );

    protected $block_supports = array(
        'align' => array('left', 'center', 'right'),
        'className' => true,
        'html' => false
    );

    /**
     * Render heading block
     */
    public function render_block($attributes, $content) {
        $level = intval($attributes['level']) ?: 2;
        $tag = 'h' . $level;
        $text = isset($attributes['content']) ? sanitize_text_field($attributes['content']) : '';
        
        // Build styles
        $styles = array();
        $text_color = sanitize_hex_color($attributes['textColor']);
        
        if ($attributes['useGradient']) {
            $angle = intval($attributes['gradientAngle']);
            $color1 = sanitize_hex_color($attributes['gradientColor1']);
            $color2 = sanitize_hex_color($attributes['gradientColor2']);
            $styles[] = sprintf(
                'background: linear-gradient(%ddeg, %s, %s)',
                $angle,
                $color1,
                $color2
            );
            $styles[] = '-webkit-background-clip: text';
            $styles[] = '-webkit-text-fill-color: transparent';
            $styles[] = 'background-clip: text';
        } else {
            $styles[] = 'color: ' . $text_color;
        }
        
        $styles[] = 'font-size: ' . sanitize_text_field($attributes['fontSize']);
        $styles[] = 'font-weight: ' . sanitize_text_field($attributes['fontWeight']);
        $styles[] = 'line-height: ' . sanitize_text_field($attributes['lineHeight']);
        $styles[] = 'text-align: ' . sanitize_text_field($attributes['textAlign']);
        $styles[] = 'letter-spacing: ' . sanitize_text_field($attributes['letterSpacing']);
        $styles[] = 'text-transform: ' . sanitize_text_field($attributes['textTransform']);
        
        if ($attributes['textShadow']) {
            $styles[] = 'text-shadow: 2px 2px 4px rgba(0,0,0,0.2)';
        }
        
        $style_attr = 'style="' . esc_attr(implode('; ', $styles)) . '"';
        
        // Handle numbering
        $html = '';
        if ($attributes['useNumbering']) {
            $number_styles = array();
            $number_styles[] = 'display: inline-flex';
            $number_styles[] = 'align-items: center';
            $number_styles[] = 'justify-content: center';
            $number_styles[] = 'width: ' . sanitize_text_field($attributes['numberSize']);
            $number_styles[] = 'height: ' . sanitize_text_field($attributes['numberSize']);
            $number_styles[] = 'margin-right: 12px';
            $number_styles[] = 'color: ' . sanitize_hex_color($attributes['numberColor']);
            $number_styles[] = 'background: ' . sanitize_hex_color($attributes['numberBgColor']);
            $number_styles[] = 'font-weight: bold';
            
            if ($attributes['numberStyle'] === 'circle') {
                $number_styles[] = 'border-radius: 50%';
            } elseif ($attributes['numberStyle'] === 'square') {
                $number_styles[] = 'border-radius: 4px';
            }
            
            $number_style_attr = 'style="' . esc_attr(implode('; ', $number_styles)) . '"';
            $number = isset($attributes['number']) ? intval($attributes['number']) : 1;
            
            $html = sprintf(
                '<div style="display: flex; align-items: center;">
                    <span %s>%d</span>
                    <%s %s>%s</%s>
                </div>',
                $number_style_attr,
                $number,
                esc_html($tag),
                $style_attr,
                esc_html($text),
                esc_html($tag)
            );
        } else {
            $html = sprintf(
                '<%s %s>%s</%s>',
                esc_html($tag),
                $style_attr,
                esc_html($text),
                esc_html($tag)
            );
        }
        
        return $html;
    }
}
