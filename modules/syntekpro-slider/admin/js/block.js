(function (wp) {
    var el              = wp.element.createElement;
    var Fragment        = wp.element.Fragment;
    var registerBlock   = wp.blocks.registerBlockType;
    var useBlockProps   = wp.blockEditor.useBlockProps;
    var InspectorCtrls  = wp.blockEditor.InspectorControls;
    var BlockControls   = wp.blockEditor.BlockControls;
    var PanelBody       = wp.components.PanelBody;
    var PanelRow        = wp.components.PanelRow;
    var SelectControl   = wp.components.SelectControl;
    var ToggleControl   = wp.components.ToggleControl;
    var RangeControl    = wp.components.RangeControl;
    var TextControl     = wp.components.TextControl;
    var Placeholder     = wp.components.Placeholder;
    var Spinner         = wp.components.Spinner;
    var Button          = wp.components.Button;
    var ToolbarGroup    = wp.components.ToolbarGroup;
    var ToolbarButton   = wp.components.ToolbarButton;
    var ExternalLink    = wp.components.ExternalLink;
    var Notice          = wp.components.Notice;
    var Disabled        = wp.components.Disabled;
    var ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;
    var useState        = wp.element.useState;
    var useEffect       = wp.element.useEffect;
    var useCallback     = wp.element.useCallback;
    var __              = wp.i18n.__;

    var iconSVG = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24', fill: 'none' },
        el('rect', { x: 2, y: 5, width: 20, height: 14, rx: 2, stroke: 'currentColor', strokeWidth: 2 }),
        el('path', { d: 'M7 19l3-4 2.5 3L16 13l5 6', stroke: 'currentColor', strokeWidth: 1.5, strokeLinecap: 'round', strokeLinejoin: 'round' }),
        el('circle', { cx: 8, cy: 10, r: 1.5, fill: 'currentColor' })
    );

    /* Tri-state helper: '' = inherit from slider, 'true', 'false' */
    var triOptions = [
        { label: __('Default (use slider setting)', 'syntekpro-slider'), value: '' },
        { label: __('On',  'syntekpro-slider'), value: 'true' },
        { label: __('Off', 'syntekpro-slider'), value: 'false' },
    ];

    var transitionOptions = [
        { label: __('Default', 'syntekpro-slider'), value: '' },
        { label: 'Slide',     value: 'slide' },
        { label: 'Fade',      value: 'fade' },
        { label: 'Zoom',      value: 'zoom' },
        { label: 'Crossfade', value: 'crossfade' },
        { label: 'Parallax',  value: 'parallax' },
        { label: 'Ken Burns', value: 'kenburns' },
        { label: '3D Cube',   value: 'cube3d' },
        { label: 'Flip',      value: 'flip' },
    ];

    var scalingOptions = [
        { label: __('Default', 'syntekpro-slider'), value: '' },
        { label: 'Auto',        value: 'auto' },
        { label: 'Fixed',       value: 'fixed' },
        { label: 'Full Width',  value: 'fullwidth' },
        { label: 'Full Screen', value: 'fullscreen' },
    ];

    registerBlock('syntekpro/slider', {
        title:       __('SyntekPro Slider', 'syntekpro-slider'),
        description: __('Display a SyntekPro Slider with full control over layout, transitions, autoplay, navigation, and more.', 'syntekpro-slider'),
        category:    'media',
        icon:        iconSVG,
        keywords:    [__('slider', 'syntekpro-slider'), __('carousel', 'syntekpro-slider'), __('slideshow', 'syntekpro-slider'), 'syntekpro'],
        supports:    { html: false, align: ['wide', 'full'], className: true },
        example:     { attributes: { sliderId: 0 } },

        attributes: {
            sliderId:      { type: 'number',  default: 0 },
            autoplay:      { type: 'string',  default: '' },
            autoplaySpeed: { type: 'number',  default: 0 },
            loop:          { type: 'string',  default: '' },
            arrows:        { type: 'string',  default: '' },
            dots:          { type: 'string',  default: '' },
            transition:    { type: 'string',  default: '' },
            speed:         { type: 'number',  default: 0 },
            pauseOnHover:  { type: 'string',  default: '' },
            touch:         { type: 'string',  default: '' },
            keyboardNav:   { type: 'string',  default: '' },
            lazyLoad:      { type: 'string',  default: '' },
            parallax:      { type: 'string',  default: '' },
            scalingMode:   { type: 'string',  default: '' },
            width:         { type: 'number',  default: 0 },
            height:        { type: 'number',  default: 0 },
            cssClass:      { type: 'string',  default: '' },
        },

        edit: function (props) {
            var attrs      = props.attributes;
            var setAttrs   = props.setAttributes;
            var blockProps = useBlockProps();

            var _sliders   = useState(null);
            var _loading   = useState(true);
            var _preview   = useState(false);
            var sliderList = _sliders[0], setSliders = _sliders[1];
            var isLoading  = _loading[0],  setLoading = _loading[1];
            var showPreview = _preview[0], setPreview = _preview[1];

            /* Fetch slider list on mount */
            useEffect(function () {
                wp.apiFetch({ path: '/syntekpro-slider/v1/sliders' }).then(function (data) {
                    setSliders(data || []);
                    setLoading(false);
                }).catch(function () {
                    setSliders([]);
                    setLoading(false);
                });
            }, []);

            /* Build options for SelectControl */
            var sliderOptions = [{ label: __('— Select a slider —', 'syntekpro-slider'), value: 0 }];
            if (sliderList) {
                sliderList.forEach(function (s) {
                    sliderOptions.push({ label: s.name + ' (ID: ' + s.id + ')', value: s.id });
                });
            }

            var hasSlider  = attrs.sliderId > 0;
            var editUrl    = (window.SPSLIDER_BLOCK && window.SPSLIDER_BLOCK.edit_url || '') + attrs.sliderId;
            var sliderInfo = hasSlider && sliderList ? (sliderList.filter(function (x) { return x.id === attrs.sliderId; })[0] || null) : null;

            /* ── Toolbar ────────────────────────────────────── */
            var toolbar = hasSlider && el(BlockControls, { group: 'block' },
                el(ToolbarGroup, {},
                    el(ToolbarButton, {
                        icon: 'visibility',
                        label: showPreview ? __('Hide Preview', 'syntekpro-slider') : __('Show Preview', 'syntekpro-slider'),
                        onClick: function () { setPreview(!showPreview); },
                        isPressed: showPreview,
                    }),
                    el(ToolbarButton, {
                        icon: 'edit',
                        label: __('Open Slider Editor', 'syntekpro-slider'),
                        onClick: function () { window.open(editUrl, '_blank'); },
                    })
                )
            );

            /* ── Inspector: Slider Selection ────────────────── */
            var panelSelect = el(PanelBody, {
                title: __('Slider', 'syntekpro-slider'),
                initialOpen: true,
                icon: 'images-alt2',
            },
                isLoading
                    ? el(Spinner)
                    : el(SelectControl, {
                        label:    __('Choose Slider', 'syntekpro-slider'),
                        value:    attrs.sliderId,
                        options:  sliderOptions,
                        onChange: function (val) { setAttrs({ sliderId: parseInt(val, 10) || 0 }); },
                    }),
                hasSlider && el(PanelRow, {},
                    el(ExternalLink, { href: editUrl }, __('Open in Slider Editor', 'syntekpro-slider'))
                ),
                sliderInfo && el('div', { className: 'spslider-block-meta' },
                    el('small', {}, __('Shortcode:', 'syntekpro-slider') + ' '),
                    el('code', {}, '[syntekpro_slider id="' + attrs.sliderId + '"]')
                )
            );

            /* ── Inspector: Layout & Dimensions ─────────────── */
            var panelLayout = hasSlider && el(PanelBody, {
                title: __('Layout & Dimensions', 'syntekpro-slider'),
                initialOpen: false,
                icon: 'admin-generic',
            },
                el(SelectControl, {
                    label: __('Scaling Mode', 'syntekpro-slider'),
                    value: attrs.scalingMode,
                    options: scalingOptions,
                    onChange: function (v) { setAttrs({ scalingMode: v }); },
                    help: __('Controls how the slider scales on different screen sizes.', 'syntekpro-slider'),
                }),
                el(RangeControl, {
                    label: __('Width (px)', 'syntekpro-slider'),
                    value: attrs.width || 0,
                    onChange: function (v) { setAttrs({ width: v || 0 }); },
                    min: 0,
                    max: 2560,
                    step: 10,
                    allowReset: true,
                    resetFallbackValue: 0,
                    help: attrs.width ? '' : __('0 = use slider default', 'syntekpro-slider'),
                }),
                el(RangeControl, {
                    label: __('Height (px)', 'syntekpro-slider'),
                    value: attrs.height || 0,
                    onChange: function (v) { setAttrs({ height: v || 0 }); },
                    min: 0,
                    max: 1200,
                    step: 10,
                    allowReset: true,
                    resetFallbackValue: 0,
                    help: attrs.height ? '' : __('0 = use slider default', 'syntekpro-slider'),
                }),
                el(TextControl, {
                    label: __('CSS Class', 'syntekpro-slider'),
                    value: attrs.cssClass,
                    onChange: function (v) { setAttrs({ cssClass: v }); },
                    help: __('Add custom CSS class(es) to the slider wrapper.', 'syntekpro-slider'),
                })
            );

            /* ── Inspector: Autoplay ────────────────────────── */
            var panelAutoplay = hasSlider && el(PanelBody, {
                title: __('Autoplay', 'syntekpro-slider'),
                initialOpen: false,
                icon: 'controls-play',
            },
                el(SelectControl, {
                    label: __('Autoplay', 'syntekpro-slider'),
                    value: attrs.autoplay,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ autoplay: v }); },
                }),
                el(RangeControl, {
                    label: __('Autoplay Speed (ms)', 'syntekpro-slider'),
                    value: attrs.autoplaySpeed || 0,
                    onChange: function (v) { setAttrs({ autoplaySpeed: v || 0 }); },
                    min: 0,
                    max: 15000,
                    step: 250,
                    allowReset: true,
                    resetFallbackValue: 0,
                    help: attrs.autoplaySpeed ? '' : __('0 = use slider default', 'syntekpro-slider'),
                }),
                el(SelectControl, {
                    label: __('Pause on Hover', 'syntekpro-slider'),
                    value: attrs.pauseOnHover,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ pauseOnHover: v }); },
                }),
                el(SelectControl, {
                    label: __('Loop', 'syntekpro-slider'),
                    value: attrs.loop,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ loop: v }); },
                })
            );

            /* ── Inspector: Transition ──────────────────────── */
            var panelTransition = hasSlider && el(PanelBody, {
                title: __('Transition', 'syntekpro-slider'),
                initialOpen: false,
                icon: 'slides',
            },
                el(SelectControl, {
                    label: __('Transition Effect', 'syntekpro-slider'),
                    value: attrs.transition,
                    options: transitionOptions,
                    onChange: function (v) { setAttrs({ transition: v }); },
                }),
                el(RangeControl, {
                    label: __('Transition Speed (ms)', 'syntekpro-slider'),
                    value: attrs.speed || 0,
                    onChange: function (v) { setAttrs({ speed: v || 0 }); },
                    min: 0,
                    max: 3000,
                    step: 50,
                    allowReset: true,
                    resetFallbackValue: 0,
                    help: attrs.speed ? '' : __('0 = use slider default', 'syntekpro-slider'),
                })
            );

            /* ── Inspector: Navigation ──────────────────────── */
            var panelNavigation = hasSlider && el(PanelBody, {
                title: __('Navigation', 'syntekpro-slider'),
                initialOpen: false,
                icon: 'arrow-left-alt2',
            },
                el(SelectControl, {
                    label: __('Arrows', 'syntekpro-slider'),
                    value: attrs.arrows,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ arrows: v }); },
                }),
                el(SelectControl, {
                    label: __('Dots', 'syntekpro-slider'),
                    value: attrs.dots,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ dots: v }); },
                }),
                el(SelectControl, {
                    label: __('Touch / Swipe', 'syntekpro-slider'),
                    value: attrs.touch,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ touch: v }); },
                }),
                el(SelectControl, {
                    label: __('Keyboard Navigation', 'syntekpro-slider'),
                    value: attrs.keyboardNav,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ keyboardNav: v }); },
                })
            );

            /* ── Inspector: Performance ─────────────────────── */
            var panelPerformance = hasSlider && el(PanelBody, {
                title: __('Performance', 'syntekpro-slider'),
                initialOpen: false,
                icon: 'performance',
            },
                el(SelectControl, {
                    label: __('Lazy Load Images', 'syntekpro-slider'),
                    value: attrs.lazyLoad,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ lazyLoad: v }); },
                }),
                el(SelectControl, {
                    label: __('Parallax', 'syntekpro-slider'),
                    value: attrs.parallax,
                    options: triOptions,
                    onChange: function (v) { setAttrs({ parallax: v }); },
                })
            );

            /* ── Inspector: Reset ───────────────────────────── */
            var panelReset = hasSlider && el(PanelBody, {
                title: __('Reset Overrides', 'syntekpro-slider'),
                initialOpen: false,
            },
                el('p', { style: { fontSize: '12px', color: '#757575', margin: '0 0 12px' } },
                    __('Reset all block overrides to use the slider\'s saved settings.', 'syntekpro-slider')
                ),
                el(Button, {
                    variant: 'secondary',
                    isDestructive: true,
                    onClick: function () {
                        setAttrs({
                            autoplay: '', autoplaySpeed: 0, loop: '', arrows: '', dots: '',
                            transition: '', speed: 0, pauseOnHover: '', touch: '', keyboardNav: '',
                            lazyLoad: '', parallax: '', scalingMode: '', width: 0, height: 0, cssClass: '',
                        });
                    },
                }, __('Reset All to Defaults', 'syntekpro-slider'))
            );

            var inspector = el(InspectorCtrls, {},
                panelSelect,
                panelLayout,
                panelAutoplay,
                panelTransition,
                panelNavigation,
                panelPerformance,
                panelReset
            );

            /* ── Main block content ─────────────────────────── */
            var content;
            if (isLoading) {
                content = el(Placeholder, {
                    icon:  iconSVG,
                    label: __('SyntekPro Slider', 'syntekpro-slider'),
                }, el(Spinner));
            } else if (!hasSlider) {
                content = el(Placeholder, {
                    icon:         iconSVG,
                    label:        __('SyntekPro Slider', 'syntekpro-slider'),
                    instructions: __('Choose a slider to display from the dropdown, or create one in the Slider Editor.', 'syntekpro-slider'),
                },
                    el(SelectControl, {
                        value:    attrs.sliderId,
                        options:  sliderOptions,
                        onChange: function (val) { setAttrs({ sliderId: parseInt(val, 10) || 0 }); },
                    }),
                    el(Button, {
                        variant: 'secondary',
                        href: window.SPSLIDER_BLOCK && window.SPSLIDER_BLOCK.edit_url ? window.SPSLIDER_BLOCK.edit_url.replace(/&slider_id=$/, '') : '#',
                        target: '_blank',
                        style: { marginTop: '4px' },
                    }, __('Create New Slider', 'syntekpro-slider'))
                );
            } else if (showPreview) {
                content = el(Disabled, {},
                    el(ServerSideRender, {
                        block: 'syntekpro/slider',
                        attributes: attrs,
                    })
                );
            } else {
                /* Static preview card */
                var overrideCount = 0;
                ['autoplay','loop','arrows','dots','transition','pauseOnHover','touch','keyboardNav','lazyLoad','parallax','scalingMode'].forEach(function (k) {
                    if (attrs[k] !== '') overrideCount++;
                });
                ['autoplaySpeed','speed','width','height'].forEach(function (k) {
                    if (attrs[k] > 0) overrideCount++;
                });
                if (attrs.cssClass) overrideCount++;

                content = el('div', { className: 'spslider-block-preview' },
                    el('div', { className: 'spslider-block-preview-header' },
                        el('span', { className: 'spslider-block-preview-icon' }, '▶'),
                        el('span', { className: 'spslider-block-preview-label' },
                            sliderInfo ? sliderInfo.name : (__('Slider', 'syntekpro-slider') + ' #' + attrs.sliderId)
                        ),
                        el('span', { className: 'spslider-block-preview-id' }, 'ID: ' + attrs.sliderId)
                    ),
                    el('div', { className: 'spslider-block-preview-body' },
                        el('div', { className: 'spslider-block-preview-stats' },
                            el('div', { className: 'spslider-stat' },
                                el('span', { className: 'spslider-stat-label' }, __('Transition', 'syntekpro-slider')),
                                el('span', { className: 'spslider-stat-value' }, attrs.transition || __('Default', 'syntekpro-slider'))
                            ),
                            el('div', { className: 'spslider-stat' },
                                el('span', { className: 'spslider-stat-label' }, __('Autoplay', 'syntekpro-slider')),
                                el('span', { className: 'spslider-stat-value' }, attrs.autoplay === 'true' ? __('On', 'syntekpro-slider') : attrs.autoplay === 'false' ? __('Off', 'syntekpro-slider') : __('Default', 'syntekpro-slider'))
                            ),
                            el('div', { className: 'spslider-stat' },
                                el('span', { className: 'spslider-stat-label' }, __('Arrows / Dots', 'syntekpro-slider')),
                                el('span', { className: 'spslider-stat-value' },
                                    (attrs.arrows === 'true' ? '✓' : attrs.arrows === 'false' ? '✗' : '—') +
                                    ' / ' +
                                    (attrs.dots === 'true' ? '✓' : attrs.dots === 'false' ? '✗' : '—')
                                )
                            ),
                            el('div', { className: 'spslider-stat' },
                                el('span', { className: 'spslider-stat-label' }, __('Overrides', 'syntekpro-slider')),
                                el('span', { className: 'spslider-stat-value' }, overrideCount > 0 ? overrideCount + ' active' : __('None', 'syntekpro-slider'))
                            )
                        )
                    ),
                    el('div', { className: 'spslider-block-preview-footer' },
                        el(Button, {
                            variant: 'secondary',
                            isSmall: true,
                            onClick: function () { setPreview(true); },
                        }, __('Show Live Preview', 'syntekpro-slider')),
                        el(Button, {
                            variant: 'link',
                            isSmall: true,
                            href: editUrl,
                            target: '_blank',
                        }, __('Edit Slides', 'syntekpro-slider'))
                    )
                );
            }

            return el(Fragment, {}, toolbar, el('div', blockProps, inspector, content));
        },

        save: function () {
            /* Dynamic block — rendered server-side */
            return null;
        },
    });
})(window.wp);
