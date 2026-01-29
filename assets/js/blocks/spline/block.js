/**
 * Spline 3D Viewer Block Editor
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, TextControl, CheckboxControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('syntekpro-animations/spline', {
    title: 'Spline 3D Viewer',
    description: 'Embed interactive 3D scenes from Spline App',
    category: 'syntekpro',
    icon: 'format-video',
    keywords: ['spline', '3d', 'viewer', 'embed'],
    
    attributes: {
        splineUrl: { type: 'string', default: '' },
        width: { type: 'string', default: '100%' },
        height: { type: 'string', default: '600px' },
        fullScreen: { type: 'boolean', default: false },
        allowInteraction: { type: 'boolean', default: true }
    },
    
    supports: { align: ['full', 'wide'], customClassName: true },

    edit: function( { attributes, setAttributes } ) {
        const { splineUrl, width, height, fullScreen, allowInteraction } = attributes;
        const blockProps = useBlockProps({ style: { padding: '20px', backgroundColor: '#f5f5f5' } });

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="Spline Settings" initialOpen={true}>
                        <TextControl
                            label="Spline URL"
                            value={splineUrl}
                            onChange={(value) => setAttributes({ splineUrl: value })}
                            placeholder="https://prod.spline.design/..."
                        />
                        <TextControl
                            label="Width"
                            value={width}
                            onChange={(value) => setAttributes({ width: value })}
                            placeholder="100%"
                        />
                        <TextControl
                            label="Height"
                            value={height}
                            onChange={(value) => setAttributes({ height: value })}
                            placeholder="600px"
                        />
                        <CheckboxControl
                            label="Full Screen Mode"
                            checked={fullScreen}
                            onChange={(checked) => setAttributes({ fullScreen: checked })}
                        />
                        <CheckboxControl
                            label="Allow Interaction"
                            checked={allowInteraction}
                            onChange={(checked) => setAttributes({ allowInteraction: checked })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <p><strong>Spline 3D Viewer</strong></p>
                    <p style={{ fontSize: '12px', color: '#666' }}>{splineUrl ? 'Spline loaded' : 'No Spline URL set'}</p>
                </div>
            </Fragment>
        );
    },

    save: function() {
        return null;
    }
});
