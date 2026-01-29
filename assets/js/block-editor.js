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
            repeatCount: { type: 'number', default: 0 }
        },
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            return el('div', { className: props.className, style: { border: '2px dashed #2271b1', padding: '20px', backgroundColor: '#f0f5ff', borderRadius: '4px' } },
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Animation Type', 'syntekpro-animations'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Select Animation', 'syntekpro-animations'),
                            value: attributes.type,
                            options: [
                                { label: '-- Fade Animations --', value: '' },
                                { label: 'Fade In', value: 'fadeIn' },
                                { label: 'Fade In Up', value: 'fadeInUp' },
                                { label: 'Fade In Down', value: 'fadeInDown' },
                                { label: 'Fade In Left', value: 'fadeInLeft' },
                                { label: 'Fade In Right', value: 'fadeInRight' },
                                { label: '-- Slide Animations --', value: '' },
                                { label: 'Slide Left', value: 'slideLeft' },
                                { label: 'Slide Right', value: 'slideRight' },
                                { label: 'Slide Up', value: 'slideUp' },
                                { label: 'Slide Down', value: 'slideDown' },
                                { label: '-- Scale Animations --', value: '' },
                                { label: 'Scale Up', value: 'scaleUp' },
                                { label: 'Scale Down', value: 'scaleDown' },
                                { label: '-- Rotation Animations --', value: '' },
                                { label: 'Rotate In', value: 'rotateIn' },
                                { label: 'Flip X', value: 'flipX' },
                                { label: 'Flip Y', value: 'flipY' },
                                { label: '-- Bounce Animations --', value: '' },
                                { label: 'Bounce In', value: 'bounceIn' },
                                { label: 'Elastic In', value: 'elasticIn' }
                            ],
                            onChange: function(val) { setAttributes({ type: val }); }
                        })
                    ),
                    el(PanelBody, { title: __('Timing', 'syntekpro-animations'), initialOpen: false },
                        el(RangeControl, {
                            label: __('Duration (seconds)', 'syntekpro-animations'),
                            value: attributes.duration,
                            min: 0.1,
                            max: 5,
                            step: 0.1,
                            help: __('How long the animation lasts', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ duration: val }); }
                        }),
                        el(RangeControl, {
                            label: __('Delay (seconds)', 'syntekpro-animations'),
                            value: attributes.delay,
                            min: 0,
                            max: 3,
                            step: 0.1,
                            help: __('Wait time before animation starts', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ delay: val }); }
                        }),
                        el(TextControl, {
                            label: __('Ease Function', 'syntekpro-animations'),
                            value: attributes.ease,
                            help: __('e.g. power2.out, power3.inOut, bounce.out', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ ease: val }); }
                        })
                    ),
                    el(PanelBody, { title: __('Advanced', 'syntekpro-animations'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Trigger Type', 'syntekpro-animations'),
                            value: attributes.trigger,
                            options: [
                                { label: 'Load (on page load)', value: 'load' },
                                { label: 'Scroll (on scroll into view)', value: 'scroll' }
                            ],
                            help: __('Choose when the animation should start', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ trigger: val }); }
                        }),
                        el(RangeControl, {
                            label: __('Stagger (seconds)', 'syntekpro-animations'),
                            value: attributes.stagger,
                            min: 0,
                            max: 1,
                            step: 0.05,
                            help: __('Delay between child elements', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ stagger: val }); }
                        }),
                        el(RangeControl, {
                            label: __('Repeat Count', 'syntekpro-animations'),
                            value: attributes.repeatCount,
                            min: 0,
                            max: 5,
                            step: 1,
                            help: __('0 = no repeat (plays once)', 'syntekpro-animations'),
                            onChange: function(val) { setAttributes({ repeatCount: val }); }
                        })
                    )
                ),
                el('div', { className: 'syntekpro-animate-preview', style: { backgroundColor: '#fff', border: '1px solid #e0e0e0', padding: '15px', borderRadius: '4px', marginTop: '10px' } },
                    el('p', { style: { marginTop: 0, fontSize: '12px', color: '#666', fontWeight: 'bold' } },
                        __('Animation Preview', 'syntekpro-animations')
                    ),
                    el('div', { style: { fontSize: '13px', color: '#333', lineHeight: '1.6' } },
                        el('strong', {}, attributes.type || 'fadeInUp'),
                        ' | ' + attributes.duration.toFixed(1) + 's | ',
                        attributes.useScrollTrigger ? __('On Scroll', 'syntekpro-animations') : __('On Load', 'syntekpro-animations')
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