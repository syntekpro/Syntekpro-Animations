/**
 * Table of Contents Block Editor
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, TextControl, CheckboxControl, FormTokenField } = wp.components;
const { Fragment } = wp.element;

registerBlockType('syntekpro-animations/toc', {
    title: 'Table of Contents',
    description: 'Auto-generate table of contents from page headings',
    category: 'syntekpro',
    icon: 'list-view',
    keywords: ['table', 'contents', 'toc', 'navigation'],
    
    attributes: {
        title: { type: 'string', default: 'Table of Contents' },
        headingLevels: { type: 'array', default: [2, 3] },
        showTitle: { type: 'boolean', default: true },
        numbered: { type: 'boolean', default: false },
        linkColor: { type: 'string', default: '#1565c0' }
    },
    
    supports: { customClassName: true },

    edit: function( { attributes, setAttributes } ) {
        const { title, headingLevels, showTitle, numbered, linkColor } = attributes;
        const blockProps = useBlockProps({
            style: {
                padding: '20px',
                backgroundColor: '#f9f9f9',
                border: '2px solid #ddd',
                borderRadius: '4px'
            }
        });

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        <TextControl
                            label="Title"
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                        />
                        <CheckboxControl
                            label="Show Title"
                            checked={showTitle}
                            onChange={(checked) => setAttributes({ showTitle: checked })}
                        />
                        <CheckboxControl
                            label="Numbered List"
                            checked={numbered}
                            onChange={(checked) => setAttributes({ numbered: checked })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <p><strong>Table of Contents Block</strong></p>
                    <p style={{ fontSize: '12px', color: '#666' }}>Will auto-generate from page headings</p>
                </div>
            </Fragment>
        );
    },

    save: function() {
        return null;
    }
});
