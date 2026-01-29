/**
 * Icon List Block Editor
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps, ColorPalette } = wp.blockEditor;
const { PanelBody, SelectControl, RangeControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('syntekpro-animations/icon-list', {
    title: 'Icon List',
    description: 'Create beautiful lists with icons and custom styling',
    category: 'syntekpro',
    icon: 'list-view',
    keywords: ['icon', 'list', 'items'],
    
    attributes: {
        layout: { type: 'string', default: 'vertical' },
        iconSize: { type: 'string', default: '24px' },
        iconColor: { type: 'string', default: '#1565c0' },
        textColor: { type: 'string', default: '#333' },
        spacing: { type: 'string', default: '16px' },
        alignment: { type: 'string', default: 'left' }
    },
    
    supports: { customClassName: true },

    edit: function( { attributes, setAttributes } ) {
        const { layout, iconSize, iconColor, textColor, spacing, alignment } = attributes;
        const blockProps = useBlockProps({ style: { padding: '20px', backgroundColor: '#f9f9f9' } });

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        <SelectControl
                            label="Layout"
                            value={layout}
                            options={[
                                { label: 'Vertical', value: 'vertical' },
                                { label: 'Horizontal', value: 'horizontal' },
                                { label: 'Grid', value: 'grid' }
                            ]}
                            onChange={(value) => setAttributes({ layout: value })}
                        />
                        <SelectControl
                            label="Alignment"
                            value={alignment}
                            options={[
                                { label: 'Left', value: 'left' },
                                { label: 'Center', value: 'center' },
                                { label: 'Right', value: 'right' }
                            ]}
                            onChange={(value) => setAttributes({ alignment: value })}
                        />
                    </PanelBody>
                    <PanelBody title="Styling" initialOpen={false}>
                        <p className="components-base-control__label">Icon Color</p>
                        <ColorPalette
                            value={iconColor}
                            onChange={(value) => setAttributes({ iconColor: value })}
                        />
                        <p className="components-base-control__label" style={{ marginTop: '16px' }}>Text Color</p>
                        <ColorPalette
                            value={textColor}
                            onChange={(value) => setAttributes({ textColor: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <p><strong>Icon List</strong></p>
                    <p style={{ fontSize: '12px', color: '#666' }}>Add list items with icons</p>
                </div>
            </Fragment>
        );
    },

    save: function() {
        return null;
    }
});
