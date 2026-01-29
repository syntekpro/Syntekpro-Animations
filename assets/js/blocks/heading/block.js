/**
 * Heading Block Editor
 * 
 * Advanced heading with text gradients and numbered styling
 */

const { registerBlockType } = wp.blocks;
const { RichText, InspectorControls, useBlockProps, ColorPalette } = wp.blockEditor;
const { PanelBody, SelectControl, TextControl, RangeControl, CheckboxControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('syntekpro-animations/heading', {
    title: 'Heading',
    description: 'Advanced heading with text gradients and numbered styling',
    category: 'syntekpro',
    icon: 'heading',
    keywords: ['heading', 'title', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
    
    attributes: {
        content: {
            type: 'string',
            source: 'html',
            selector: 'h1, h2, h3, h4, h5, h6',
            default: ''
        },
        level: {
            type: 'number',
            default: 2
        },
        textAlign: {
            type: 'string',
            default: 'left'
        },
        textColor: {
            type: 'string',
            default: ''
        },
        fontSize: {
            type: 'string',
            default: '32px'
        },
        fontWeight: {
            type: 'string',
            default: 'bold'
        },
        lineHeight: {
            type: 'string',
            default: '1.2'
        },
        useGradient: {
            type: 'boolean',
            default: false
        },
        gradientColor1: {
            type: 'string',
            default: '#ff0000'
        },
        gradientColor2: {
            type: 'string',
            default: '#00ff00'
        },
        gradientAngle: {
            type: 'number',
            default: 90
        },
        useNumbering: {
            type: 'boolean',
            default: false
        },
        numberStyle: {
            type: 'string',
            default: 'circle'
        },
        numberColor: {
            type: 'string',
            default: '#1565c0'
        },
        numberBgColor: {
            type: 'string',
            default: '#f5f5f5'
        },
        numberSize: {
            type: 'string',
            default: '32px'
        },
        letterSpacing: {
            type: 'string',
            default: '0'
        },
        textTransform: {
            type: 'string',
            default: 'none'
        },
        textShadow: {
            type: 'string',
            default: ''
        }
    },
    
    supports: {
        align: ['left', 'center', 'right'],
        customClassName: true
    },

    edit: function( { attributes, setAttributes } ) {
        const {
            content,
            level,
            textAlign,
            textColor,
            fontSize,
            fontWeight,
            lineHeight,
            useGradient,
            gradientColor1,
            gradientColor2,
            gradientAngle,
            useNumbering,
            numberStyle,
            numberColor,
            numberBgColor,
            numberSize,
            letterSpacing,
            textTransform,
            textShadow
        } = attributes;

        const TagName = 'h' + level;

        // Build style for heading
        let headingStyle = {
            textAlign: textAlign,
            fontSize: fontSize,
            fontWeight: fontWeight,
            lineHeight: lineHeight,
            letterSpacing: letterSpacing,
            textTransform: textTransform,
            textShadow: textShadow || undefined
        };

        if (useGradient) {
            headingStyle.backgroundImage = `linear-gradient(${gradientAngle}deg, ${gradientColor1}, ${gradientColor2})`;
            headingStyle.WebkitBackgroundClip = 'text';
            headingStyle.WebkitTextFillColor = 'transparent';
            headingStyle.backgroundClip = 'text';
        } else if (textColor) {
            headingStyle.color = textColor;
        }

        const blockProps = useBlockProps({ style: headingStyle });

        let numberPrefix = '';
        if (useNumbering) {
            const numberStyle_ = numberStyle === 'circle' ? 
                `style="display: inline-flex; align-items: center; justify-content: center; width: ${numberSize}; height: ${numberSize}; border-radius: 50%; background-color: ${numberBgColor}; color: ${numberColor}; margin-right: 8px; flex-shrink: 0;"` :
                `style="display: inline-flex; align-items: center; justify-content: center; width: ${numberSize}; height: ${numberSize}; background-color: ${numberBgColor}; color: ${numberColor}; margin-right: 8px; flex-shrink: 0;"`;
            numberPrefix = `<span ${numberStyle_}>${level}</span>`;
        }

        return (
            <Fragment>
                <InspectorControls>
                    {/* Heading Settings */}
                    <PanelBody title="Heading Settings" initialOpen={true}>
                        <SelectControl
                            label="Heading Level"
                            value={level}
                            options={[
                                { label: 'H1', value: 1 },
                                { label: 'H2', value: 2 },
                                { label: 'H3', value: 3 },
                                { label: 'H4', value: 4 },
                                { label: 'H5', value: 5 },
                                { label: 'H6', value: 6 }
                            ]}
                            onChange={(value) => setAttributes({ level: value })}
                        />
                        <SelectControl
                            label="Text Alignment"
                            value={textAlign}
                            options={[
                                { label: 'Left', value: 'left' },
                                { label: 'Center', value: 'center' },
                                { label: 'Right', value: 'right' }
                            ]}
                            onChange={(value) => setAttributes({ textAlign: value })}
                        />
                    </PanelBody>

                    {/* Typography */}
                    <PanelBody title="Typography" initialOpen={false}>
                        <TextControl
                            label="Font Size"
                            value={fontSize}
                            onChange={(value) => setAttributes({ fontSize: value })}
                            placeholder="32px"
                        />
                        <SelectControl
                            label="Font Weight"
                            value={fontWeight}
                            options={[
                                { label: 'Normal', value: 'normal' },
                                { label: 'Bold', value: 'bold' },
                                { label: '100', value: '100' },
                                { label: '300', value: '300' },
                                { label: '600', value: '600' },
                                { label: '700', value: '700' },
                                { label: '900', value: '900' }
                            ]}
                            onChange={(value) => setAttributes({ fontWeight: value })}
                        />
                        <TextControl
                            label="Line Height"
                            value={lineHeight}
                            onChange={(value) => setAttributes({ lineHeight: value })}
                            placeholder="1.2"
                        />
                        <TextControl
                            label="Letter Spacing"
                            value={letterSpacing}
                            onChange={(value) => setAttributes({ letterSpacing: value })}
                            placeholder="0"
                        />
                        <SelectControl
                            label="Text Transform"
                            value={textTransform}
                            options={[
                                { label: 'None', value: 'none' },
                                { label: 'Uppercase', value: 'uppercase' },
                                { label: 'Lowercase', value: 'lowercase' },
                                { label: 'Capitalize', value: 'capitalize' }
                            ]}
                            onChange={(value) => setAttributes({ textTransform: value })}
                        />
                        <TextControl
                            label="Text Shadow"
                            value={textShadow}
                            onChange={(value) => setAttributes({ textShadow: value })}
                            placeholder="2px 2px 4px rgba(0,0,0,0.3)"
                        />
                    </PanelBody>

                    {/* Gradient */}
                    <PanelBody title="Text Gradient" initialOpen={false}>
                        <CheckboxControl
                            label="Enable Gradient"
                            checked={useGradient}
                            onChange={(checked) => setAttributes({ useGradient: checked })}
                        />
                        {useGradient && (
                            <Fragment>
                                <p className="components-base-control__label">Gradient Color 1</p>
                                <ColorPalette
                                    value={gradientColor1}
                                    onChange={(value) => setAttributes({ gradientColor1: value })}
                                />
                                <p className="components-base-control__label" style={{ marginTop: '16px' }}>Gradient Color 2</p>
                                <ColorPalette
                                    value={gradientColor2}
                                    onChange={(value) => setAttributes({ gradientColor2: value })}
                                />
                                <RangeControl
                                    label="Gradient Angle"
                                    value={gradientAngle}
                                    onChange={(value) => setAttributes({ gradientAngle: value })}
                                    min={0}
                                    max={360}
                                />
                            </Fragment>
                        )}
                        {!useGradient && (
                            <Fragment>
                                <p className="components-base-control__label">Text Color</p>
                                <ColorPalette
                                    value={textColor}
                                    onChange={(value) => setAttributes({ textColor: value })}
                                    clearable={true}
                                />
                            </Fragment>
                        )}
                    </PanelBody>

                    {/* Numbering */}
                    <PanelBody title="Numbering" initialOpen={false}>
                        <CheckboxControl
                            label="Enable Numbering"
                            checked={useNumbering}
                            onChange={(checked) => setAttributes({ useNumbering: checked })}
                        />
                        {useNumbering && (
                            <Fragment>
                                <SelectControl
                                    label="Number Style"
                                    value={numberStyle}
                                    options={[
                                        { label: 'Circle', value: 'circle' },
                                        { label: 'Square', value: 'square' }
                                    ]}
                                    onChange={(value) => setAttributes({ numberStyle: value })}
                                />
                                <p className="components-base-control__label">Number Color</p>
                                <ColorPalette
                                    value={numberColor}
                                    onChange={(value) => setAttributes({ numberColor: value })}
                                />
                                <p className="components-base-control__label" style={{ marginTop: '16px' }}>Number Background</p>
                                <ColorPalette
                                    value={numberBgColor}
                                    onChange={(value) => setAttributes({ numberBgColor: value })}
                                />
                                <TextControl
                                    label="Number Size"
                                    value={numberSize}
                                    onChange={(value) => setAttributes({ numberSize: value })}
                                    placeholder="32px"
                                />
                            </Fragment>
                        )}
                    </PanelBody>
                </InspectorControls>

                <TagName {...blockProps}>
                    {useNumbering && (
                        <span 
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                width: numberSize,
                                height: numberSize,
                                borderRadius: numberStyle === 'circle' ? '50%' : '0',
                                backgroundColor: numberBgColor,
                                color: numberColor,
                                marginRight: '8px',
                                flexShrink: 0,
                                fontSize: '0.8em'
                            }}
                        >
                            {level}
                        </span>
                    )}
                    <RichText
                        placeholder="Enter heading text..."
                        value={content}
                        onChange={(value) => setAttributes({ content: value })}
                        allowedFormats={['bold', 'italic', 'strikethrough']}
                    />
                </TagName>
            </Fragment>
        );
    },

    save: function( { attributes } ) {
        const {
            content,
            level,
            textAlign,
            textColor,
            fontSize,
            fontWeight,
            lineHeight,
            useGradient,
            gradientColor1,
            gradientColor2,
            gradientAngle,
            useNumbering,
            numberStyle,
            numberColor,
            numberBgColor,
            numberSize,
            letterSpacing,
            textTransform,
            textShadow
        } = attributes;

        const TagName = 'h' + level;

        let headingStyle = {
            textAlign: textAlign,
            fontSize: fontSize,
            fontWeight: fontWeight,
            lineHeight: lineHeight,
            letterSpacing: letterSpacing,
            textTransform: textTransform,
            textShadow: textShadow || undefined
        };

        if (useGradient) {
            headingStyle.backgroundImage = `linear-gradient(${gradientAngle}deg, ${gradientColor1}, ${gradientColor2})`;
            headingStyle.WebkitBackgroundClip = 'text';
            headingStyle.WebkitTextFillColor = 'transparent';
            headingStyle.backgroundClip = 'text';
        } else if (textColor) {
            headingStyle.color = textColor;
        }

        const blockProps = useBlockProps.save({ style: headingStyle });

        return (
            <TagName {...blockProps}>
                {useNumbering && (
                    <span 
                        style={{
                            display: 'inline-flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: numberSize,
                            height: numberSize,
                            borderRadius: numberStyle === 'circle' ? '50%' : '0',
                            backgroundColor: numberBgColor,
                            color: numberColor,
                            marginRight: '8px',
                            flexShrink: 0,
                            fontSize: '0.8em'
                        }}
                    >
                        {level}
                    </span>
                )}
                <RichText.Content value={content} />
            </TagName>
        );
    }
});
