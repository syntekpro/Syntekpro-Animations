/**
 * Carousel/Slider Block Editor
 * 
 * Turn any content into an animated slider/carousel
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, InnerBlocks, useBlockProps } = wp.blockEditor;
const { PanelBody, SelectControl, RangeControl, CheckboxControl, TextControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('syntekpro-animations/carousel', {
    title: 'Carousel / Slider',
    description: 'Turn any content into an animated slider with navigation and pagination',
    category: 'syntekpro',
    icon: 'slides',
    keywords: ['carousel', 'slider', 'swiper', 'gallery'],
    
    attributes: {
        slidesPerView: {
            type: 'number',
            default: 1
        },
        spaceBetween: {
            type: 'number',
            default: 10
        },
        autoplay: {
            type: 'boolean',
            default: false
        },
        autoplayDelay: {
            type: 'number',
            default: 5000
        },
        navigation: {
            type: 'boolean',
            default: true
        },
        pagination: {
            type: 'boolean',
            default: true
        },
        effect: {
            type: 'string',
            default: 'slide'
        },
        loop: {
            type: 'boolean',
            default: true
        },
        speed: {
            type: 'number',
            default: 300
        }
    },
    
    supports: {
        align: ['full', 'wide'],
        customClassName: true
    },

    edit: function( { attributes, setAttributes } ) {
        const {
            slidesPerView,
            spaceBetween,
            autoplay,
            autoplayDelay,
            navigation,
            pagination,
            effect,
            loop,
            speed
        } = attributes;

        const blockProps = useBlockProps({
            style: {
                padding: '20px',
                backgroundColor: '#f5f5f5',
                borderRadius: '4px'
            }
        });

        return (
            <Fragment>
                <InspectorControls>
                    {/* Carousel Settings */}
                    <PanelBody title="Carousel Settings" initialOpen={true}>
                        <RangeControl
                            label="Slides Per View"
                            value={slidesPerView}
                            onChange={(value) => setAttributes({ slidesPerView: value })}
                            min={1}
                            max={6}
                        />
                        <RangeControl
                            label="Space Between Slides (px)"
                            value={spaceBetween}
                            onChange={(value) => setAttributes({ spaceBetween: value })}
                            min={0}
                            max={100}
                        />
                        <SelectControl
                            label="Transition Effect"
                            value={effect}
                            options={[
                                { label: 'Slide', value: 'slide' },
                                { label: 'Fade', value: 'fade' },
                                { label: 'Cube', value: 'cube' },
                                { label: 'Coverflow', value: 'coverflow' },
                                { label: 'Flip', value: 'flip' }
                            ]}
                            onChange={(value) => setAttributes({ effect: value })}
                        />
                        <RangeControl
                            label="Transition Speed (ms)"
                            value={speed}
                            onChange={(value) => setAttributes({ speed: value })}
                            min={100}
                            max={2000}
                            step={100}
                        />
                    </PanelBody>

                    {/* Navigation */}
                    <PanelBody title="Navigation" initialOpen={false}>
                        <CheckboxControl
                            label="Show Previous/Next Buttons"
                            checked={navigation}
                            onChange={(checked) => setAttributes({ navigation: checked })}
                        />
                        <CheckboxControl
                            label="Show Dots/Pagination"
                            checked={pagination}
                            onChange={(checked) => setAttributes({ pagination: checked })}
                        />
                        <CheckboxControl
                            label="Enable Loop (Infinite)"
                            checked={loop}
                            onChange={(checked) => setAttributes({ loop: checked })}
                        />
                    </PanelBody>

                    {/* Autoplay */}
                    <PanelBody title="Autoplay" initialOpen={false}>
                        <CheckboxControl
                            label="Enable Autoplay"
                            checked={autoplay}
                            onChange={(checked) => setAttributes({ autoplay: checked })}
                        />
                        {autoplay && (
                            <RangeControl
                                label="Autoplay Delay (ms)"
                                value={autoplayDelay}
                                onChange={(value) => setAttributes({ autoplayDelay: value })}
                                min={1000}
                                max={10000}
                                step={500}
                            />
                        )}
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <p style={{ marginTop: 0, color: '#666' }}>
                        <strong>Carousel Block</strong><br/>
                        Add slides below. Each child block will become a slide.
                    </p>
                    <InnerBlocks
                        allowedBlocks={['core/paragraph', 'core/image', 'core/heading', 'core/button', 'core/group']}
                        renderAppender={InnerBlocks.DefaultBlockAppender}
                    />
                </div>
            </Fragment>
        );
    },

    save: function() {
        return <InnerBlocks.Content />;
    }
});
