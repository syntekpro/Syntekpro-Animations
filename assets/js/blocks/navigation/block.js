/**
 * Navigation Block Editor
 */

const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, SelectControl, CheckboxControl } = wp.components;
const { Fragment, useState } = wp.element;

registerBlockType('syntekpro-animations/navigation', {
    title: 'Navigation',
    description: 'Custom navigation menu with styling options',
    category: 'syntekpro',
    icon: 'menu',
    keywords: ['navigation', 'menu', 'nav'],
    
    attributes: {
        menuLocation: { type: 'string', default: 'primary' },
        style: { type: 'string', default: 'horizontal' },
        alignment: { type: 'string', default: 'left' },
        backgroundColor: { type: 'string', default: '' },
        textColor: { type: 'string', default: '' },
        hoverEffect: { type: 'string', default: 'underline' },
        stickyNav: { type: 'boolean', default: false },
        mobileMenu: { type: 'boolean', default: true }
    },
    
    supports: { align: ['full'], customClassName: true },

    edit: function( { attributes, setAttributes } ) {
        const { menuLocation, style, alignment, hoverEffect, stickyNav, mobileMenu } = attributes;
        const blockProps = useBlockProps({ style: { padding: '10px', backgroundColor: '#f5f5f5' } });

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        <SelectControl
                            label="Menu Location"
                            value={menuLocation}
                            options={[
                                { label: 'Primary Menu', value: 'primary' },
                                { label: 'Secondary Menu', value: 'secondary' },
                                { label: 'Footer Menu', value: 'footer' }
                            ]}
                            onChange={(value) => setAttributes({ menuLocation: value })}
                        />
                        <SelectControl
                            label="Style"
                            value={style}
                            options={[
                                { label: 'Horizontal', value: 'horizontal' },
                                { label: 'Vertical', value: 'vertical' },
                                { label: 'Mega Menu', value: 'mega' }
                            ]}
                            onChange={(value) => setAttributes({ style: value })}
                        />
                        <CheckboxControl
                            label="Sticky Navigation"
                            checked={stickyNav}
                            onChange={(checked) => setAttributes({ stickyNav: checked })}
                        />
                        <CheckboxControl
                            label="Mobile Menu"
                            checked={mobileMenu}
                            onChange={(checked) => setAttributes({ mobileMenu: checked })}
                        />
                    </PanelBody>
                </InspectorControls>
                <nav {...blockProps}>
                    <p><strong>Navigation Menu</strong></p>
                </nav>
            </Fragment>
        );
    },

    save: function() {
        return null;
    }
});
