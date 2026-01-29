/**
 * 3D Model Viewer Block Editor
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, TextControl, CheckboxControl, SelectControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('syntekpro-animations/3d-model', {
    title: '3D Model Viewer',
    description: 'Display and interact with 3D models (glTF, GLTF)',
    category: 'syntekpro',
    icon: 'media-video',
    keywords: ['3d', 'model', 'viewer', 'gltf'],
    
    attributes: {
        modelUrl: { type: 'string', default: '' },
        modelFormat: { type: 'string', default: 'gltf' },
        width: { type: 'string', default: '100%' },
        height: { type: 'string', default: '500px' },
        autoRotate: { type: 'boolean', default: true },
        cameraControl: { type: 'boolean', default: true },
        arSupport: { type: 'boolean', default: false },
        backgroundColor: { type: 'string', default: '#ffffff' },
        lighting: { type: 'string', default: 'default' }
    },
    
    supports: { align: ['full', 'wide'], customClassName: true },

    edit: function( { attributes, setAttributes } ) {
        const { modelUrl, modelFormat, width, height, autoRotate, cameraControl, arSupport } = attributes;
        const blockProps = useBlockProps({ style: { padding: '20px', backgroundColor: '#f5f5f5', height: height } });

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="Model Settings" initialOpen={true}>
                        <TextControl
                            label="Model URL"
                            value={modelUrl}
                            onChange={(value) => setAttributes({ modelUrl: value })}
                            placeholder="https://example.com/model.gltf"
                        />
                        <SelectControl
                            label="Model Format"
                            value={modelFormat}
                            options={[
                                { label: 'glTF', value: 'gltf' },
                                { label: 'GLB', value: 'glb' },
                                { label: 'OBJ', value: 'obj' }
                            ]}
                            onChange={(value) => setAttributes({ modelFormat: value })}
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
                            placeholder="500px"
                        />
                        <CheckboxControl
                            label="Auto Rotate"
                            checked={autoRotate}
                            onChange={(checked) => setAttributes({ autoRotate: checked })}
                        />
                        <CheckboxControl
                            label="Camera Control"
                            checked={cameraControl}
                            onChange={(checked) => setAttributes({ cameraControl: checked })}
                        />
                        <CheckboxControl
                            label="AR Support"
                            checked={arSupport}
                            onChange={(checked) => setAttributes({ arSupport: checked })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <p><strong>3D Model Viewer</strong></p>
                    <p style={{ fontSize: '12px', color: '#666' }}>{modelUrl ? modelUrl : 'No model loaded'}</p>
                </div>
            </Fragment>
        );
    },

    save: function() {
        return null;
    }
});
