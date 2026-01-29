/**
 * Tabs Block Editor
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, InnerBlocks, useBlockProps, ColorPalette } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl } = wp.components;
const { Fragment, useState } = wp.element;

registerBlockType('syntekpro-animations/tabs', {
    title: 'Tabs',
    description: 'Create tabbed content containers',
    category: 'syntekpro',
    icon: 'playlist-video',
    keywords: ['tabs', 'tabbed', 'accordion', 'content'],
    
    attributes: {
        activeTab: { type: 'number', default: 0 },
        tabStyle: { type: 'string', default: 'default' },
        tabAlignment: { type: 'string', default: 'left' },
        backgroundColor: { type: 'string', default: '#ffffff' },
        activeColor: { type: 'string', default: '#1565c0' },
        textColor: { type: 'string', default: '#333' },
        animation: { type: 'string', default: 'fade' }
    },
    
    supports: { customClassName: true },

    edit: function( { attributes, setAttributes } ) {
        const {
            activeTab,
            tabStyle,
            tabAlignment,
            backgroundColor,
            activeColor,
            textColor,
            animation
        } = attributes;

        const blockProps = useBlockProps({ style: { padding: '20px', backgroundColor: '#f9f9f9' } });

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="Tab Settings" initialOpen={true}>
                        <SelectControl
                            label="Tab Style"
                            value={tabStyle}
                            options={[
                                { label: 'Default', value: 'default' },
                                { label: 'Pills', value: 'pills' },
                                { label: 'Underline', value: 'underline' },
                                { label: 'Vertical', value: 'vertical' }
                            ]}
                            onChange={(value) => setAttributes({ tabStyle: value })}
                        />
                        <SelectControl
                            label="Tab Alignment"
                            value={tabAlignment}
                            options={[
                                { label: 'Left', value: 'left' },
                                { label: 'Center', value: 'center' },
                                { label: 'Right', value: 'right' }
                            ]}
                            onChange={(value) => setAttributes({ tabAlignment: value })}
                        />
                        <SelectControl
                            label="Animation"
                            value={animation}
                            options={[
                                { label: 'Fade', value: 'fade' },
                                { label: 'Slide', value: 'slide' },
                                { label: 'Zoom', value: 'zoom' },
                                { label: 'None', value: 'none' }
                            ]}
                            onChange={(value) => setAttributes({ animation: value })}
                        />
                    </PanelBody>
                    <PanelBody title="Colors" initialOpen={false}>
                        <p className="components-base-control__label">Active Tab Color</p>
                        <ColorPalette
                            value={activeColor}
                            onChange={(value) => setAttributes({ activeColor: value })}
                        />
                        <p className="components-base-control__label" style={{ marginTop: '16px' }}>Text Color</p>
                        <ColorPalette
                            value={textColor}
                            onChange={(value) => setAttributes({ textColor: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <p><strong>Tabs Container</strong></p>
                    <p style={{ fontSize: '12px', color: '#666' }}>Create tabbed content sections</p>
                </div>
            </Fragment>
        );
    },

    save: function() {
        return null;
    }
});
