<?php
/**
 * Animation Presets
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Animation_Presets {
    
    public function __construct() {
        add_filter('syntekpro_animation_presets', array($this, 'get_presets'));
    }
    
    /**
     * Get all animation presets
     */
    public function get_presets($presets = array()) {
        $all_presets = array_merge($presets, array(
            // FADE ANIMATIONS
            'fadeIn' => array(
                'name' => 'Fade In',
                'category' => 'fade',
                'free' => true,
                'from' => array('opacity' => 0),
                'to' => array('opacity' => 1)
            ),
            'fadeInUp' => array(
                'name' => 'Fade In Up',
                'category' => 'fade',
                'free' => true,
                'from' => array('opacity' => 0, 'y' => 50),
                'to' => array('opacity' => 1, 'y' => 0)
            ),
            'fadeInDown' => array(
                'name' => 'Fade In Down',
                'category' => 'fade',
                'free' => true,
                'from' => array('opacity' => 0, 'y' => -50),
                'to' => array('opacity' => 1, 'y' => 0)
            ),
            'fadeInLeft' => array(
                'name' => 'Fade In Left',
                'category' => 'fade',
                'free' => true,
                'from' => array('opacity' => 0, 'x' => -50),
                'to' => array('opacity' => 1, 'x' => 0)
            ),
            'fadeInRight' => array(
                'name' => 'Fade In Right',
                'category' => 'fade',
                'free' => true,
                'from' => array('opacity' => 0, 'x' => 50),
                'to' => array('opacity' => 1, 'x' => 0)
            ),
            
            // SLIDE ANIMATIONS
            'slideLeft' => array(
                'name' => 'Slide Left',
                'category' => 'slide',
                'free' => true,
                'from' => array('x' => 100),
                'to' => array('x' => 0)
            ),
            'slideRight' => array(
                'name' => 'Slide Right',
                'category' => 'slide',
                'free' => true,
                'from' => array('x' => -100),
                'to' => array('x' => 0)
            ),
            'slideUp' => array(
                'name' => 'Slide Up',
                'category' => 'slide',
                'free' => true,
                'from' => array('y' => 100),
                'to' => array('y' => 0)
            ),
            'slideDown' => array(
                'name' => 'Slide Down',
                'category' => 'slide',
                'free' => true,
                'from' => array('y' => -100),
                'to' => array('y' => 0)
            ),
            
            // SCALE ANIMATIONS
            'scaleUp' => array(
                'name' => 'Scale Up',
                'category' => 'scale',
                'free' => true,
                'from' => array('scale' => 0, 'opacity' => 0),
                'to' => array('scale' => 1, 'opacity' => 1)
            ),
            'scaleDown' => array(
                'name' => 'Scale Down',
                'category' => 'scale',
                'free' => true,
                'from' => array('scale' => 2, 'opacity' => 0),
                'to' => array('scale' => 1, 'opacity' => 1)
            ),
            'scaleX' => array(
                'name' => 'Scale X',
                'category' => 'scale',
                'free' => true,
                'from' => array('scaleX' => 0),
                'to' => array('scaleX' => 1)
            ),
            'scaleY' => array(
                'name' => 'Scale Y',
                'category' => 'scale',
                'free' => true,
                'from' => array('scaleY' => 0),
                'to' => array('scaleY' => 1)
            ),
            
            // ROTATE ANIMATIONS
            'rotateIn' => array(
                'name' => 'Rotate In',
                'category' => 'rotate',
                'free' => true,
                'from' => array('rotation' => -180, 'opacity' => 0),
                'to' => array('rotation' => 0, 'opacity' => 1)
            ),
            'rotate360' => array(
                'name' => 'Rotate 360',
                'category' => 'rotate',
                'free' => true,
                'from' => array('rotation' => 0),
                'to' => array('rotation' => 360)
            ),
            'flipX' => array(
                'name' => 'Flip X',
                'category' => 'rotate',
                'free' => true,
                'from' => array('rotationY' => 90, 'opacity' => 0),
                'to' => array('rotationY' => 0, 'opacity' => 1)
            ),
            'flipY' => array(
                'name' => 'Flip Y',
                'category' => 'rotate',
                'free' => true,
                'from' => array('rotationX' => 90, 'opacity' => 0),
                'to' => array('rotationX' => 0, 'opacity' => 1)
            ),
            
            // BOUNCE ANIMATIONS (PRO)
            'bounceIn' => array(
                'name' => 'Bounce In',
                'category' => 'bounce',
                'free' => true,
                'from' => array('scale' => 0, 'opacity' => 0),
                'to' => array('scale' => 1, 'opacity' => 1, 'ease' => 'back.out(1.7)')
            ),
            'bounceInUp' => array(
                'name' => 'Bounce In Up',
                'category' => 'bounce',
                'free' => true,
                'from' => array('y' => 100, 'opacity' => 0),
                'to' => array('y' => 0, 'opacity' => 1, 'ease' => 'back.out(1.7)')
            ),
            
            // ELASTIC ANIMATIONS (PRO)
            'elasticIn' => array(
                'name' => 'Elastic In',
                'category' => 'elastic',
                'free' => true,
                'from' => array('scale' => 0),
                'to' => array('scale' => 1, 'ease' => 'elastic.out(1, 0.3)')
            ),
            
            // BLUR ANIMATIONS (PRO)
            'blurIn' => array(
                'name' => 'Blur In',
                'category' => 'blur',
                'free' => true,
                'from' => array('opacity' => 0, 'filter' => 'blur(10px)'),
                'to' => array('opacity' => 1, 'filter' => 'blur(0px)')
            ),
            
            // ZOOM ANIMATIONS
            'zoomIn' => array(
                'name' => 'Zoom In',
                'category' => 'zoom',
                'free' => true,
                'from' => array('scale' => 0.5, 'opacity' => 0),
                'to' => array('scale' => 1, 'opacity' => 1)
            ),
            'zoomInUp' => array(
                'name' => 'Zoom In Up',
                'category' => 'zoom',
                'free' => true,
                'from' => array('scale' => 0.5, 'y' => 50, 'opacity' => 0),
                'to' => array('scale' => 1, 'y' => 0, 'opacity' => 1)
            ),
            'zoomInDown' => array(
                'name' => 'Zoom In Down',
                'category' => 'zoom',
                'free' => true,
                'from' => array('scale' => 0.5, 'y' => -50, 'opacity' => 0),
                'to' => array('scale' => 1, 'y' => 0, 'opacity' => 1)
            ),
            'zoomInLeft' => array(
                'name' => 'Zoom In Left',
                'category' => 'zoom',
                'free' => true,
                'from' => array('scale' => 0.5, 'x' => -50, 'opacity' => 0),
                'to' => array('scale' => 1, 'x' => 0, 'opacity' => 1)
            ),
            'zoomInRight' => array(
                'name' => 'Zoom In Right',
                'category' => 'zoom',
                'free' => true,
                'from' => array('scale' => 0.5, 'x' => 50, 'opacity' => 0),
                'to' => array('scale' => 1, 'x' => 0, 'opacity' => 1)
            ),
            
            // REVEAL ANIMATIONS
            'revealLeft' => array(
                'name' => 'Reveal Left',
                'category' => 'reveal',
                'free' => true,
                'from' => array('clipPath' => 'inset(0 100% 0 0)'),
                'to' => array('clipPath' => 'inset(0 0 0 0)')
            ),
            'revealRight' => array(
                'name' => 'Reveal Right',
                'category' => 'reveal',
                'free' => true,
                'from' => array('clipPath' => 'inset(0 0 0 100%)'),
                'to' => array('clipPath' => 'inset(0 0 0 0)')
            ),
            'revealUp' => array(
                'name' => 'Reveal Up',
                'category' => 'reveal',
                'free' => true,
                'from' => array('clipPath' => 'inset(100% 0 0 0)'),
                'to' => array('clipPath' => 'inset(0 0 0 0)')
            ),
            'revealDown' => array(
                'name' => 'Reveal Down',
                'category' => 'reveal',
                'free' => true,
                'from' => array('clipPath' => 'inset(0 0 100% 0)'),
                'to' => array('clipPath' => 'inset(0 0 0 0)')
            ),
            
            // PERSPECTIVE ANIMATIONS (PRO)
            'perspective3D' => array(
                'name' => '3D Perspective',
                'category' => 'perspective',
                'free' => true,
                'from' => array('rotationX' => -90, 'transformOrigin' => '50% 50% -200', 'opacity' => 0),
                'to' => array('rotationX' => 0, 'opacity' => 1)
            ),
            'flipInX' => array(
                'name' => 'Flip In X',
                'category' => 'perspective',
                'free' => true,
                'from' => array('rotationX' => -90, 'opacity' => 0),
                'to' => array('rotationX' => 0, 'opacity' => 1, 'ease' => 'back.out(1.7)')
            ),
            'flipInY' => array(
                'name' => 'Flip In Y',
                'category' => 'perspective',
                'free' => true,
                'from' => array('rotationY' => -90, 'opacity' => 0),
                'to' => array('rotationY' => 0, 'opacity' => 1, 'ease' => 'back.out(1.7)')
            ),
            'cardFlip' => array(
                'name' => 'Card Flip',
                'category' => 'perspective',
                'free' => true,
                'from' => array('rotationY' => 180, 'opacity' => 0),
                'to' => array('rotationY' => 0, 'opacity' => 1)
            ),
            
            // GLITCH ANIMATIONS (PRO)
            'glitchIn' => array(
                'name' => 'Glitch In',
                'category' => 'glitch',
                'free' => true,
                'from' => array('x' => -20, 'opacity' => 0),
                'to' => array('x' => 0, 'opacity' => 1, 'ease' => 'rough({clamp:true, points:20})'),
                'scramble' => true
            ),
            'digitalReveal' => array(
                'name' => 'Digital Reveal',
                'category' => 'glitch',
                'free' => true,
                'from' => array('scale' => 0.95, 'opacity' => 0),
                'to' => array('scale' => 1, 'opacity' => 1, 'ease' => 'steps(5)'),
                'glitch' => true
            ),
            
            // WAVE ANIMATIONS
            'waveIn' => array(
                'name' => 'Wave In',
                'category' => 'wave',
                'free' => true,
                'from' => array('y' => 50, 'opacity' => 0),
                'to' => array('y' => 0, 'opacity' => 1, 'ease' => 'sine.inOut'),
                'wave' => true
            ),
            'ripple' => array(
                'name' => 'Ripple',
                'category' => 'wave',
                'free' => true,
                'from' => array('scale' => 0.8, 'opacity' => 0),
                'to' => array('scale' => 1, 'opacity' => 1, 'ease' => 'elastic.out(1, 0.5)')
            ),
            
            // PEEL ANIMATIONS (PRO)
            'peelLeft' => array(
                'name' => 'Peel Left',
                'category' => 'peel',
                'free' => true,
                'from' => array('rotationY' => 180, 'x' => -100, 'transformOrigin' => 'left center'),
                'to' => array('rotationY' => 0, 'x' => 0)
            ),
            'peelRight' => array(
                'name' => 'Peel Right',
                'category' => 'peel',
                'free' => true,
                'from' => array('rotationY' => -180, 'x' => 100, 'transformOrigin' => 'right center'),
                'to' => array('rotationY' => 0, 'x' => 0)
            ),
            
            // SWING ANIMATIONS
            'swingIn' => array(
                'name' => 'Swing In',
                'category' => 'swing',
                'free' => true,
                'from' => array('rotation' => -15, 'transformOrigin' => 'top center', 'opacity' => 0),
                'to' => array('rotation' => 0, 'opacity' => 1, 'ease' => 'back.out(3)')
            ),
            'pendulum' => array(
                'name' => 'Pendulum',
                'category' => 'swing',
                'free' => true,
                'from' => array('rotation' => -45, 'transformOrigin' => 'top center'),
                'to' => array('rotation' => 0, 'ease' => 'elastic.out(1, 0.3)')
            ),
            
            // FOLD ANIMATIONS (PRO)
            'unfoldHorizontal' => array(
                'name' => 'Unfold Horizontal',
                'category' => 'fold',
                'free' => true,
                'from' => array('scaleX' => 0, 'transformOrigin' => 'center center'),
                'to' => array('scaleX' => 1, 'ease' => 'back.out(1.7)')
            ),
            'unfoldVertical' => array(
                'name' => 'Unfold Vertical',
                'category' => 'fold',
                'free' => true,
                'from' => array('scaleY' => 0, 'transformOrigin' => 'center center'),
                'to' => array('scaleY' => 1, 'ease' => 'back.out(1.7)')
            ),
            
            // ATTENTION SEEKERS
            'pulse' => array(
                'name' => 'Pulse',
                'category' => 'attention',
                'free' => true,
                'from' => array('scale' => 1),
                'to' => array('scale' => 1.1, 'repeat' => 2, 'yoyo' => true, 'ease' => 'sine.inOut')
            ),
            'shake' => array(
                'name' => 'Shake',
                'category' => 'attention',
                'free' => true,
                'from' => array('x' => 0),
                'to' => array('x' => -10, 'repeat' => 5, 'yoyo' => true, 'ease' => 'sine.inOut')
            ),
            'wobble' => array(
                'name' => 'Wobble',
                'category' => 'attention',
                'free' => true,
                'from' => array('rotation' => 0),
                'to' => array('rotation' => 5, 'repeat' => 5, 'yoyo' => true, 'ease' => 'sine.inOut')
            ),
            'heartbeat' => array(
                'name' => 'Heartbeat',
                'category' => 'attention',
                'free' => true,
                'from' => array('scale' => 1),
                'to' => array('scale' => 1.3, 'repeat' => 2, 'yoyo' => true, 'ease' => 'power2.inOut')
            ),
            
            // ADVANCED EASING (PRO)
            'smoothBounce' => array(
                'name' => 'Smooth Bounce',
                'category' => 'advanced',
                'free' => true,
                'from' => array('y' => -100, 'opacity' => 0),
                'to' => array('y' => 0, 'opacity' => 1, 'ease' => 'bounce.out')
            ),
            'elasticScale' => array(
                'name' => 'Elastic Scale',
                'category' => 'advanced',
                'free' => true,
                'from' => array('scale' => 0),
                'to' => array('scale' => 1, 'ease' => 'elastic.out(1.5, 0.5)')
            ),
            'backSlide' => array(
                'name' => 'Back Slide',
                'category' => 'advanced',
                'free' => true,
                'from' => array('x' => -100, 'opacity' => 0),
                'to' => array('x' => 0, 'opacity' => 1, 'ease' => 'back.out(2)')
            ),
            
            // TEXT ANIMATIONS (PRO)
            'typewriter' => array(
                'name' => 'Typewriter',
                'category' => 'text',
                'free' => true,
                'textEffect' => true,
                'effect' => 'chars',
                'from' => array('opacity' => 0),
                'to' => array('opacity' => 1, 'stagger' => 0.03)
            ),
            'textWave' => array(
                'name' => 'Text Wave',
                'category' => 'text',
                'free' => true,
                'textEffect' => true,
                'effect' => 'chars',
                'from' => array('y' => 20, 'opacity' => 0),
                'to' => array('y' => 0, 'opacity' => 1, 'stagger' => 0.05, 'ease' => 'back.out(1.7)')
            ),
            'textRotate' => array(
                'name' => 'Text Rotate',
                'category' => 'text',
                'free' => true,
                'textEffect' => true,
                'effect' => 'chars',
                'from' => array('rotation' => -90, 'opacity' => 0),
                'to' => array('rotation' => 0, 'opacity' => 1, 'stagger' => 0.03)
            ),

            // LAYOUT / HERO / GRID PRESETS (PRO)
            'heroLift' => array(
                'name' => 'Hero Lift',
                'category' => 'layout',
                'free' => true,
                'from' => array('opacity' => 0, 'y' => 40, 'scale' => 0.98),
                'to' => array('opacity' => 1, 'y' => 0, 'scale' => 1, 'ease' => 'power2.out', 'duration' => 1.05)
            ),
            'cardStagger' => array(
                'name' => 'Card Stagger',
                'category' => 'layout',
                'free' => true,
                'from' => array('opacity' => 0, 'y' => 30),
                'to' => array('opacity' => 1, 'y' => 0, 'stagger' => 0.12, 'ease' => 'power2.out')
            ),
            'ctaPop' => array(
                'name' => 'CTA Pop',
                'category' => 'layout',
                'free' => true,
                'from' => array('opacity' => 0, 'scale' => 0.92, 'y' => 12),
                'to' => array('opacity' => 1, 'scale' => 1, 'y' => 0, 'ease' => 'back.out(1.4)', 'duration' => 0.9)
            ),
            'sectionDrift' => array(
                'name' => 'Section Drift',
                'category' => 'layout',
                'free' => true,
                'from' => array('opacity' => 0, 'y' => 60, 'filter' => 'blur(6px)'),
                'to' => array('opacity' => 1, 'y' => 0, 'filter' => 'blur(0px)', 'duration' => 1.1)
            )
        ));

        // Animations+ model: first N presets are free, remaining are paid.
        $free_limit = (int) get_option('syntekpro_anim_free_preset_limit', 15);
        if ($free_limit < 1) {
            $free_limit = 15;
        }
        $index = 0;
        foreach ($all_presets as $key => $preset) {
            $all_presets[$key]['free'] = $index < $free_limit;
            $index++;
        }

        return $all_presets;
    }
    
    /**
     * Get presets by category
     */
    public static function get_by_category($category = null) {
        $all_presets = apply_filters('syntekpro_animation_presets', array());
        
        if ($category === null) {
            return $all_presets;
        }
        
        return array_filter($all_presets, function($preset) use ($category) {
            return isset($preset['category']) && $preset['category'] === $category;
        });
    }
    
    /**
     * Get free presets only
     */
    public static function get_free_presets() {
        $all_presets = apply_filters('syntekpro_animation_presets', array());
        
        return array_filter($all_presets, function($preset) {
            return isset($preset['free']) && $preset['free'] === true;
        });
    }

    /**
     * Get free preset keys for frontend runtime checks.
     */
    public static function get_free_preset_keys() {
        return array_keys(self::get_free_presets());
    }
    
    /**
     * Get categories
     */
    public static function get_categories() {
        return array(
            'fade' => 'Fade Animations',
            'slide' => 'Slide Animations',
            'scale' => 'Scale Animations',
            'rotate' => 'Rotate Animations',
            'zoom' => 'Zoom Animations',
            'reveal' => 'Reveal Animations',
            'wave' => 'Wave Animations',
            'swing' => 'Swing Animations',
            'attention' => 'Attention Seekers',
            'bounce' => 'Bounce Animations (PRO)',
            'elastic' => 'Elastic Animations (PRO)',
            'blur' => 'Blur Animations (PRO)',
            'perspective' => '3D Perspective (PRO)',
            'glitch' => 'Glitch Effects (PRO)',
            'peel' => 'Peel Animations (PRO)',
            'fold' => 'Fold Animations (PRO)',
            'advanced' => 'Advanced Easing (PRO)',
            'text' => 'Text Effects (PRO)',
            'layout' => 'Layout & Sections (PRO)'
        );
    }
}

// Initialize
new Syntekpro_Animation_Presets();
