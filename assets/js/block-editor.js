(function(wp) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var InnerBlocks = wp.blockEditor.InnerBlocks;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var TextControl = wp.components.TextControl;
    var PanelRow = wp.components.PanelRow;
    var ToggleControl = wp.components.ToggleControl;
    var RangeControl = wp.components.RangeControl;
    var Notice = wp.components.Notice;
    var Button = wp.components.Button;
    var __ = wp.i18n.__;

    registerBlockType('syntekpro/animate', {
        title: __('Syntekpro Animate', 'syntekpro-animations'),
        description: __('Wrap content in a GSAP animation powered by ScrollTrigger.', 'syntekpro-animations'),
        icon: 'controls-play',
        category: 'design',
        keywords: ['animation', 'gsap', 'motion', 'scroll trigger'],
        attributes: {
            type: { type: 'string', default: 'fadeInUp' },
            duration: { type: 'number', default: 1 },
            delay: { type: 'number', default: 0 },
            ease: { type: 'string', default: 'power2.out' },
            trigger: { type: 'string', default: 'scroll' },
            useScrollTrigger: { type: 'boolean', default: true },
            stagger: { type: 'number', default: 0 },
            repeatCount: { type: 'number', default: 0 },
            startPosition: { type: 'string', default: 'top 80%' },
            scrub: { type: 'boolean', default: false },
            markers: { type: 'boolean', default: false },
            onceOnly: { type: 'boolean', default: true }
        },
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return el('div', { className: props.className, style: { border: '2px dashed #2271b1', padding: '20px', backgroundColor: '#f0f5ff', borderRadius: '4px' } },
                el(InspectorControls, {},
                    el(PanelBody, { title: __('🎨 Animation Type', 'syntekpro-animations'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Select Animation Style', 'syntekpro-animations'),
                            value: attributes.type,
                            help: __('Choose how elements enter the screen', 'syntekpro-animations'),
                            options: [
                                { label: '━━━ Fade Animations ━━━', value: '', disabled: true },
                                { label: 'Fade In', value: 'fadeIn' },
                                { label: 'Fade In Up ↑', value: 'fadeInUp' },
                                { label: 'Fade In Down ↓', value: 'fadeInDown' },
                                { label: 'Fade In Left ←', value: 'fadeInLeft' },
                                { label: 'Fade In Right →', value: 'fadeInRight' },
                                { label: '━━━ Slide Animations ━━━', value: '', disabled: true },
                                { label: 'Slide Left ←', value: 'slideLeft' },
                                { label: 'Slide Right →', value: 'slideRight' },
                                { label: 'Slide Up ↑', value: 'slideUp' },
                                { label: 'Slide Down ↓', value: 'slideDown' },
                                { label: '━━━ Zoom Animations ━━━', value: '', disabled: true },
                                { label: 'Zoom In', value: 'zoomIn' },
                                { label: 'Zoom In Up ↑', value: 'zoomInUp' },
                                { label: 'Zoom In Down ↓', value: 'zoomInDown' },
                                { label: 'Zoom In Left ←', value: 'zoomInLeft' },
                                { label: 'Zoom In Right →', value: 'zoomInRight' },
                                { label: '━━━ Scale Animations ━━━', value: '', disabled: true },
                                { label: 'Scale Up', value: 'scaleUp' },
                                { label: 'Scale Down', value: 'scaleDown' },
                                { label: 'Scale X (Horizontal)', value: 'scaleX' },
                                { label: 'Scale Y (Vertical)', value: 'scaleY' },
                                { label: '━━━ Rotation Animations ━━━', value: '', disabled: true },
                                { label: 'Rotate In', value: 'rotateIn' },
                                { label: 'Rotate 360°', value: 'rotate360' },
                                { label: 'Flip X (Horizontal)', value: 'flipX' },
                                { label: 'Flip Y (Vertical)', value: 'flipY' },
                                { label: '━━━ Reveal Animations ━━━', value: '', disabled: true },
                                { label: 'Reveal Left ←', value: 'revealLeft' },
                                { label: 'Reveal Right →', value: 'revealRight' },
                                { label: 'Reveal Up ↑', value: 'revealUp' },
                                { label: 'Reveal Down ↓', value: 'revealDown' },
                                { label: '━━━ Attention Seekers ━━━', value: '', disabled: true },
                                { label: 'Pulse', value: 'pulse' },
                                { label: 'Shake', value: 'shake' },
                                { label: 'Wobble', value: 'wobble' },
                                { label: 'Heartbeat', value: 'heartbeat' },
                                { label: '━━━ Bounce & Elastic ━━━', value: '', disabled: true },
                                { label: 'Bounce In', value: 'bounceIn' },
                                { label: 'Bounce In Up', value: 'bounceInUp' },
                                { label: 'Elastic In', value: 'elasticIn' },
                                { label: 'Elastic Scale', value: 'elasticScale' }
                            ],
                            onChange: function(val) { if(val) setAttributes({ type: val }); }
                        }),
                        el('p', { style: { fontSize: '12px', color: '#666', marginTop: '8px', fontStyle: 'italic' } },
                            __('💡 Tip: Fade and Slide animations work great for content blocks', 'syntekpro-animations')
                        )
                    ),
                    el(PanelBody, { title: __('⏱️ Timing & Easing', 'syntekpro-animations'), initialOpen: false },
                        el(RangeControl, {
                            label: __('Duration', 'syntekpro-animations'),
                            value: attributes.duration,
                            min: 0.1,
                            max: 5,
                            step: 0.1,
                            help: attributes.duration.toFixed(1) + 's - ' + (attributes.duration < 0.5 ? 'Very Fast' : attributes.duration < 1 ? 'Fast' : attributes.duration < 2 ? 'Normal' : attributes.duration < 3 ? 'Slow' : 'Very Slow'),
                            onChange: function(val) { setAttributes({ duration: val }); }
                        }),
                        el(RangeControl, {
                            label: __('Delay', 'syntekpro-animations'),
                            value: attributes.delay,
                            min: 0,
                            max: 3,
                            step: 0.1,
                            help: attributes.delay > 0 ? 'Starts after ' + attributes.delay.toFixed(1) + 's' : 'Starts immediately',
                            onChange: function(val) { setAttributes({ delay: val }); }
                        }),
                        el(SelectControl, {
                            label: __('Easing Function', 'syntekpro-animations'),
                            value: attributes.ease,
                            help: __('Controls the animation timing curve', 'syntekpro-animations'),
                            options: [
                                { label: 'Linear (No Easing)', value: 'none' },
                                { label: '─── Smooth ───', value: '', disabled: true },
                                { label: 'Power 1 (Gentle)', value: 'power1.out' },
                                { label: 'Power 2 (Standard) ⭐', value: 'power2.out' },
                                { label: 'Power 3 (Strong)', value: 'power3.out' },
                                { label: 'Power 4 (Very Strong)', value: 'power4.out' },
                                { label: '─── Special ───', value: '', disabled: true },
                                { label: 'Back (Bouncy)', value: 'back.out(1.7)' },
                                { label: 'Elastic (Very Bouncy)', value: 'elastic.out(1,0.3)' },
                                { label: 'Bounce', value: 'bounce.out' },
                                { label: 'Sine (Soft)', value: 'sine.inOut' },
                                { label: 'Expo (Fast then Slow)', value: 'expo.out' },
                                { label: 'Circ (Circular)', value: 'circ.out' }
                            ],
                            onChange: function(val) { if(val) setAttributes({ ease: val }); }
                        }),
                        el('p', { style: { fontSize: '11px', color: '#666', marginTop: '8px', padding: '8px', background: '#f0f9ff', borderRadius: '4px', borderLeft: '3px solid #1565c0' } },
                            __('⭐ Power 2 is recommended for most animations', 'syntekpro-animations')
                        )
                    ),
                    el(PanelBody, { title: __('🎯 Trigger Settings', 'syntekpro-animations'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Animation Trigger', 'syntekpro-animations'),
                            value: attributes.trigger,
                            options: [
                                { label: '🔄 On Page Load', value: 'load' },
                                { label: '👁️ On Scroll Into View', value: 'scroll' }
                            ],
                            help: attributes.trigger === 'scroll' ? __('✓ Animation plays when element scrolls into view', 'syntekpro-animations') : __('✓ Animation plays immediately when page loads', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ trigger: val }); }
                        }),
                        attributes.trigger === 'scroll' && el('div', { style: { marginTop: '16px', padding: '12px', background: '#f0f9f4', borderRadius: '6px', borderLeft: '4px solid #2e7d32' } },
                            el('h4', { style: { margin: '0 0 12px 0', fontSize: '13px', color: '#2e7d32' } }, __('📍 Scroll Behavior', 'syntekpro-animations')),
                            el(SelectControl, {
                                label: __('Start Position', 'syntekpro-animations'),
                                value: attributes.startPosition,
                                options: [
                                    { label: 'Top 80% (Standard)', value: 'top 80%' },
                                    { label: 'Top 90% (Earlier)', value: 'top 90%' },
                                    { label: 'Top 70% (Later)', value: 'top 70%' },
                                    { label: 'Top 50% (Center)', value: 'top 50%' },
                                    { label: 'Top 100% (Bottom)', value: 'top 100%' },
                                    { label: 'Bottom 80%', value: 'bottom 80%' },
                                    { label: 'Center Center', value: 'center center' }
                                ],
                                help: __('When element enters viewport', 'syntekpro-animations'),
                                onChange: function(val) { setAttributes({ startPosition: val }); }
                            }),
                            el(ToggleControl, {
                                label: __('Play Once Only', 'syntekpro-animations'),
                                checked: attributes.onceOnly,
                                help: attributes.onceOnly ? __('Animation plays only first time', 'syntekpro-animations') : __('Animation repeats on scroll', 'syntekpro-animations'),
                                onChange: function(val) { setAttributes({ onceOnly: val }); }
                            }),
                            el(ToggleControl, {
                                label: __('Show Debug Markers', 'syntekpro-animations'),
                                checked: attributes.markers,
                                help: __('Show ScrollTrigger markers for debugging', 'syntekpro-animations'),
                                onChange: function(val) { setAttributes({ markers: val }); }
                            })
                        )
                    ),
                    el(PanelBody, { title: __('🎭 Advanced Options', 'syntekpro-animations'), initialOpen: false },
                        el(RangeControl, {
                            label: __('Stagger Delay', 'syntekpro-animations'),
                            value: attributes.stagger,
                            min: 0,
                            max: 1,
                            step: 0.05,
                            help: attributes.stagger > 0 ? __('Child elements animate with ' + attributes.stagger.toFixed(2) + 's delay', 'syntekpro-animations') : __('All elements animate together', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ stagger: val }); }
                        }),
                        el(RangeControl, {
                            label: __('Repeat Count', 'syntekpro-animations'),
                            value: attributes.repeatCount,
                            min: 0,
                            max: 10,
                            step: 1,
                            help: attributes.repeatCount === 0 ? __('Plays once', 'syntekpro-animations') : attributes.repeatCount === -1 ? __('Loops forever', 'syntekpro-animations') : __('Repeats ' + attributes.repeatCount + ' times', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ repeatCount: val }); }
                        }),
                        el('div', { style: { marginTop: '16px', padding: '10px', background: '#fff3cd', borderRadius: '4px', borderLeft: '3px solid #ffc107' } },
                            el('p', { style: { margin: 0, fontSize: '11px', color: '#856404' } },
                                __('💡 For multiple child elements, use Stagger to create sequential animations', 'syntekpro-animations')
                            )
                        )
                    )
                ),
                el('div', { className: 'syntekpro-animate-preview', style: { background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', border: 'none', padding: '20px', borderRadius: '8px', marginTop: '10px', boxShadow: '0 4px 12px rgba(102,126,234,0.2)' } },
                    el('div', { style: { display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '12px' } },
                        el('p', { style: { margin: 0, fontSize: '13px', color: '#fff', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '6px' } },
                            el('span', {}, '🎬'),
                            __('Animation Preview', 'syntekpro-animations')
                        )
                    ),
                    el('div', { style: { background: 'rgba(255,255,255,0.95)', padding: '14px', borderRadius: '6px' } },
                        el('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', fontSize: '12px', color: '#333' } },
                            el('div', {},
                                el('div', { style: { color: '#666', fontSize: '10px', textTransform: 'uppercase', letterSpacing: '0.5px', marginBottom: '4px' } }, __('Type', 'syntekpro-animations')),
                                el('div', { style: { fontWeight: 'bold', color: '#667eea' } }, attributes.type || 'fadeInUp')
                            ),
                            el('div', {},
                                el('div', { style: { color: '#666', fontSize: '10px', textTransform: 'uppercase', letterSpacing: '0.5px', marginBottom: '4px' } }, __('Duration', 'syntekpro-animations')),
                                el('div', { style: { fontWeight: 'bold', color: '#667eea' } }, attributes.duration.toFixed(1) + 's')
                            ),
                            el('div', {},
                                el('div', { style: { color: '#666', fontSize: '10px', textTransform: 'uppercase', letterSpacing: '0.5px', marginBottom: '4px' } }, __('Trigger', 'syntekpro-animations')),
                                el('div', { style: { fontWeight: 'bold', color: attributes.trigger === 'scroll' ? '#2e7d32' : '#1565c0' } },
                                    attributes.trigger === 'scroll' ? '👁️ On Scroll' : '🔄 On Load'
                                )
                            ),
                            el('div', {},
                                el('div', { style: { color: '#666', fontSize: '10px', textTransform: 'uppercase', letterSpacing: '0.5px', marginBottom: '4px' } }, __('Easing', 'syntekpro-animations')),
                                el('div', { style: { fontWeight: 'bold', color: '#667eea', fontSize: '11px' } }, attributes.ease)
                            )
                        ),
                        attributes.delay > 0 && el('div', { style: { marginTop: '10px', padding: '8px', background: '#fff3cd', borderRadius: '4px', fontSize: '11px', color: '#856404' } },
                            '⏱️ Delay: ' + attributes.delay.toFixed(1) + 's'
                        ),
                        attributes.stagger > 0 && el('div', { style: { marginTop: '8px', padding: '8px', background: '#e3f2fd', borderRadius: '4px', fontSize: '11px', color: '#1565c0' } },
                            '🎭 Stagger: ' + attributes.stagger.toFixed(2) + 's'
                        )
                    )
                ),
                el('div', { className: 'syntekpro-animate-inner-blocks', style: { marginTop: '15px' } },
                    el('p', { style: { fontSize: '12px', color: '#666', fontWeight: 'bold', marginBottom: '10px' } },
                        __('Content to Animate', 'syntekpro-animations')
                    ),
                    el(InnerBlocks, {})
                )
            );
        },
        save: function(props) {
            // Save the inner blocks content
            // WordPress will preserve our attributes automatically
            // The render_callback will wrap the content in the animation div
            return el(InnerBlocks.Content);
        }
    });
})(window.wp);