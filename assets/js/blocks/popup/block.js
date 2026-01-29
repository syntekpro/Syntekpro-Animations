/**
 * Popup/Modal Block Editor
 * 
 * Create modal, sliding panel, or popup overlay content
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, InnerBlocks, useBlockProps, ColorPalette } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, CheckboxControl, RangeControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('syntekpro-animations/popup', {
    title: 'Popup / Modal',
    description: 'Create modal dialogs, sliding panels, and popup overlays',
    category: 'syntekpro',
    icon: 'admin-comments',
    keywords: ['popup', 'modal', 'dialog', 'overlay'],
    
    attributes: {
        triggerType: {
            type: 'string',
            default: 'button'
        },
        popupType: {
            type: 'string',
            default: 'modal'
        },
        position: {
            type: 'string',
            default: 'center'
        },
        animation: {
            type: 'string',
            default: 'fadeIn'
        },
        triggerText: {
            type: 'string',
            default: 'Open Popup'
        },
        closeButton: {
            type: 'boolean',
            default: true
        },
        clickOutsideClose: {
            type: 'boolean',
            default: true
        },
        width: {
            type: 'string',
            default: '500px'
        },
        height: {
            type: 'string',
            default: 'auto'
        },
        backgroundColor: {
            type: 'string',
            default: '#ffffff'
        }
    },
    
    supports: {
        customClassName: true
    },

    edit: function( { attributes, setAttributes } ) {
        const {
            triggerType,
            popupType,
            position,
            animation,
            triggerText,
            closeButton,
            clickOutsideClose,
            width,
            height,
            backgroundColor
        } = attributes;

        const blockProps = useBlockProps({
            style: {
                padding: '20px',
                backgroundColor: '#f9f9f9',
                border: '2px dashed #ccc',
                borderRadius: '4px'
            }
        });

        return (
            <Fragment>
                <InspectorControls>
                    {/* Trigger Settings */}
                    <PanelBody title="Trigger Settings" initialOpen={true}>
                        <SelectControl
                            label="Trigger Type"
                            value={triggerType}
                            options={[
                                { label: 'Button Click', value: 'button' },
                                { label: 'Link Click', value: 'link' },
                                { label: 'Hover', value: 'hover' },
                                { label: 'Auto Show (On Load)', value: 'auto' }
                            ]}
                            onChange={(value) => setAttributes({ triggerType: value })}
                        />
                        {(triggerType === 'button' || triggerType === 'link') && (
                            <TextControl
                                label="Trigger Text"
                                value={triggerText}
                                onChange={(value) => setAttributes({ triggerText: value })}
                                placeholder="Open Popup"
                            />
                        )}
                    </PanelBody>

                    {/* Popup Settings */}
                    <PanelBody title="Popup Settings" initialOpen={false}>
                        <SelectControl
                            label="Popup Type"
                            value={popupType}
                            options={[
                                { label: 'Modal (Centered)', value: 'modal' },
                                { label: 'Slide From Left', value: 'slide-left' },
                                { label: 'Slide From Right', value: 'slide-right' },
                                { label: 'Slide From Top', value: 'slide-top' },
                                { label: 'Slide From Bottom', value: 'slide-bottom' }
                            ]}
                            onChange={(value) => setAttributes({ popupType: value })}
                        />
                        <SelectControl
                            label="Animation"
                            value={animation}
                            options={[
                                { label: 'Fade In', value: 'fadeIn' },
                                { label: 'Zoom In', value: 'zoomIn' },
                                { label: 'Bounce In', value: 'bounceIn' },
                                { label: 'Slide In', value: 'slideIn' }
                            ]}
                            onChange={(value) => setAttributes({ animation: value })}
                        />
                        <TextControl
                            label="Width"
                            value={width}
                            onChange={(value) => setAttributes({ width: value })}
                            placeholder="500px"
                        />
                        <TextControl
                            label="Height"
                            value={height}
                            onChange={(value) => setAttributes({ height: value })}
                            placeholder="auto"
                        />
                        <p className="components-base-control__label">Background Color</p>
                        <ColorPalette
                            value={backgroundColor}
                            onChange={(value) => setAttributes({ backgroundColor: value })}
                        />
                    </PanelBody>

                    {/* Behavior */}
                    <PanelBody title="Behavior" initialOpen={false}>
                        <CheckboxControl
                            label="Show Close Button"
                            checked={closeButton}
                            onChange={(checked) => setAttributes({ closeButton: checked })}
                        />
                        <CheckboxControl
                            label="Close on Outside Click"
                            checked={clickOutsideClose}
                            onChange={(checked) => setAttributes({ clickOutsideClose: checked })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <p style={{ marginTop: 0, marginBottom: '10px' }}>
                        <strong>Popup Content</strong>
                    </p>
                    <button style={{
                        padding: '10px 20px',
                        backgroundColor: '#1565c0',
                        color: 'white',
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer',
                        marginBottom: '10px'
                    }}>
                        {triggerText}
                    </button>
                    <div style={{
                        padding: '20px',
                        backgroundColor: backgroundColor,
                        border: '1px solid #ddd',
                        borderRadius: '4px',
                        minHeight: '200px'
                    }}>
                        <InnerBlocks
                            allowedBlocks={['core/paragraph', 'core/heading', 'core/button', 'core/image']}
                        />
                    </div>
                </div>
            </Fragment>
        );
    },

    save: function( { attributes } ) {
        return <InnerBlocks.Content />;
    }
});
