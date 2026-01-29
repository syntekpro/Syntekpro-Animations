/**
 * Element Block Editor
 * 
 * Flexible container element with design and layout options
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, InnerBlocks, useBlockProps } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, ColorPalette, RangeControl, TextareaControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('syntekpro-animations/element', {
    title: 'Element',
    description: 'Flexible container element with design and layout options',
    category: 'syntekpro',
    icon: 'admin-generic',
    keywords: ['container', 'wrapper', 'div'],
    
    attributes: {
        element: {
            type: 'string',
            default: 'div'
        },
        elementClass: {
            type: 'string',
            default: ''
        },
        elementId: {
            type: 'string',
            default: ''
        },
        backgroundColor: {
            type: 'string',
            default: ''
        },
        textColor: {
            type: 'string',
            default: ''
        },
        padding: {
            type: 'string',
            default: ''
        },
        margin: {
            type: 'string',
            default: ''
        },
        borderRadius: {
            type: 'string',
            default: '0'
        },
        borderColor: {
            type: 'string',
            default: ''
        },
        borderWidth: {
            type: 'string',
            default: '0'
        },
        minHeight: {
            type: 'string',
            default: ''
        },
        display: {
            type: 'string',
            default: 'block'
        },
        flexDirection: {
            type: 'string',
            default: 'row'
        },
        alignItems: {
            type: 'string',
            default: 'stretch'
        },
        justifyContent: {
            type: 'string',
            default: 'flex-start'
        },
        opacity: {
            type: 'number',
            default: 1
        },
        boxShadow: {
            type: 'string',
            default: ''
        },
        transition: {
            type: 'string',
            default: ''
        }
    },
    
    supports: {
        align: true,
        alignWide: true,
        customClassName: true
    },

    edit: function( { attributes, setAttributes } ) {
        const {
            element,
            elementClass,
            elementId,
            backgroundColor,
            textColor,
            padding,
            margin,
            borderRadius,
            borderColor,
            borderWidth,
            minHeight,
            display,
            flexDirection,
            alignItems,
            justifyContent,
            opacity,
            boxShadow,
            transition
        } = attributes;

        const blockProps = useBlockProps({
            style: {
                backgroundColor: backgroundColor || undefined,
                color: textColor || undefined,
                padding: padding || undefined,
                margin: margin || undefined,
                borderRadius: borderRadius || undefined,
                border: borderColor && borderWidth ? `${borderWidth} solid ${borderColor}` : undefined,
                minHeight: minHeight || undefined,
                display: display,
                flexDirection: display === 'flex' ? flexDirection : undefined,
                alignItems: display === 'flex' ? alignItems : undefined,
                justifyContent: display === 'flex' ? justifyContent : undefined,
                opacity: opacity,
                boxShadow: boxShadow || undefined,
                transition: transition || undefined
            }
        });

        return (
            <Fragment>
                <InspectorControls>
                    {/* Basic Settings */}
                    <PanelBody title="Element Settings" initialOpen={true}>
                        <SelectControl
                            label="HTML Element"
                            value={element}
                            options={[
                                { label: 'Div', value: 'div' },
                                { label: 'Section', value: 'section' },
                                { label: 'Article', value: 'article' },
                                { label: 'Aside', value: 'aside' },
                                { label: 'Header', value: 'header' },
                                { label: 'Footer', value: 'footer' },
                                { label: 'Nav', value: 'nav' }
                            ]}
                            onChange={(value) => setAttributes({ element: value })}
                        />
                        <TextControl
                            label="CSS Class"
                            value={elementClass}
                            onChange={(value) => setAttributes({ elementClass: value })}
                            placeholder="my-custom-class"
                        />
                        <TextControl
                            label="Element ID"
                            value={elementId}
                            onChange={(value) => setAttributes({ elementId: value })}
                            placeholder="my-element-id"
                        />
                    </PanelBody>

                    {/* Colors */}
                    <PanelBody title="Colors" initialOpen={false}>
                        <p className="components-base-control__label">Background Color</p>
                        <ColorPalette
                            value={backgroundColor}
                            onChange={(value) => setAttributes({ backgroundColor: value })}
                            clearable={true}
                        />
                        <p className="components-base-control__label" style={{ marginTop: '16px' }}>Text Color</p>
                        <ColorPalette
                            value={textColor}
                            onChange={(value) => setAttributes({ textColor: value })}
                            clearable={true}
                        />
                    </PanelBody>

                    {/* Spacing */}
                    <PanelBody title="Spacing" initialOpen={false}>
                        <TextControl
                            label="Padding (CSS format)"
                            value={padding}
                            onChange={(value) => setAttributes({ padding: value })}
                            placeholder="10px 20px"
                        />
                        <TextControl
                            label="Margin (CSS format)"
                            value={margin}
                            onChange={(value) => setAttributes({ margin: value })}
                            placeholder="10px 20px"
                        />
                        <TextControl
                            label="Min Height"
                            value={minHeight}
                            onChange={(value) => setAttributes({ minHeight: value })}
                            placeholder="200px"
                        />
                    </PanelBody>

                    {/* Border */}
                    <PanelBody title="Border" initialOpen={false}>
                        <TextControl
                            label="Border Width"
                            value={borderWidth}
                            onChange={(value) => setAttributes({ borderWidth: value })}
                            placeholder="1px"
                        />
                        <p className="components-base-control__label">Border Color</p>
                        <ColorPalette
                            value={borderColor}
                            onChange={(value) => setAttributes({ borderColor: value })}
                            clearable={true}
                        />
                        <TextControl
                            label="Border Radius"
                            value={borderRadius}
                            onChange={(value) => setAttributes({ borderRadius: value })}
                            placeholder="0px"
                        />
                    </PanelBody>

                    {/* Layout */}
                    <PanelBody title="Layout" initialOpen={false}>
                        <SelectControl
                            label="Display"
                            value={display}
                            options={[
                                { label: 'Block', value: 'block' },
                                { label: 'Flex', value: 'flex' },
                                { label: 'Grid', value: 'grid' },
                                { label: 'Inline', value: 'inline' },
                                { label: 'Inline Block', value: 'inline-block' }
                            ]}
                            onChange={(value) => setAttributes({ display: value })}
                        />
                        {display === 'flex' && (
                            <Fragment>
                                <SelectControl
                                    label="Flex Direction"
                                    value={flexDirection}
                                    options={[
                                        { label: 'Row', value: 'row' },
                                        { label: 'Column', value: 'column' },
                                        { label: 'Row Reverse', value: 'row-reverse' },
                                        { label: 'Column Reverse', value: 'column-reverse' }
                                    ]}
                                    onChange={(value) => setAttributes({ flexDirection: value })}
                                />
                                <SelectControl
                                    label="Align Items"
                                    value={alignItems}
                                    options={[
                                        { label: 'Stretch', value: 'stretch' },
                                        { label: 'Flex Start', value: 'flex-start' },
                                        { label: 'Center', value: 'center' },
                                        { label: 'Flex End', value: 'flex-end' }
                                    ]}
                                    onChange={(value) => setAttributes({ alignItems: value })}
                                />
                                <SelectControl
                                    label="Justify Content"
                                    value={justifyContent}
                                    options={[
                                        { label: 'Flex Start', value: 'flex-start' },
                                        { label: 'Center', value: 'center' },
                                        { label: 'Flex End', value: 'flex-end' },
                                        { label: 'Space Between', value: 'space-between' },
                                        { label: 'Space Around', value: 'space-around' },
                                        { label: 'Space Evenly', value: 'space-evenly' }
                                    ]}
                                    onChange={(value) => setAttributes({ justifyContent: value })}
                                />
                            </Fragment>
                        )}
                    </PanelBody>

                    {/* Effects */}
                    <PanelBody title="Effects" initialOpen={false}>
                        <RangeControl
                            label="Opacity"
                            value={opacity}
                            onChange={(value) => setAttributes({ opacity: value })}
                            min={0}
                            max={1}
                            step={0.1}
                        />
                        <TextControl
                            label="Box Shadow"
                            value={boxShadow}
                            onChange={(value) => setAttributes({ boxShadow: value })}
                            placeholder="0 2px 8px rgba(0,0,0,0.1)"
                        />
                        <TextControl
                            label="Transition"
                            value={transition}
                            onChange={(value) => setAttributes({ transition: value })}
                            placeholder="all 0.3s ease"
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <InnerBlocks />
                </div>
            </Fragment>
        );
    },

    save: function() {
        return <InnerBlocks.Content />;
    }
});
