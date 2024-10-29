const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;

registerBlockType('fs/shortcode-block', {
    title: __('FS Shortcode', 'fs'),
    icon: 'shortcode',
    category: 'widgets',
    attributes: {
        style: {
            type: 'string',
            default: 'light',
        },
    },

    edit({ attributes, setAttributes }) {
        const { style } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Settings', 'fs')}>
                        <SelectControl
                            label={__('Style', 'fs')}
                            value={style}
                            options={[
                                { label: 'Light', value: 'light' },
                                { label: 'Dark', value: 'dark' },
                            ]}
                            onChange={(newStyle) => setAttributes({ style: newStyle })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className={`fs-shortcode ${style}`}>
                    {'[fs-shortcode style=' + style + ']'}
                </div>
            </>
        );
    },

    save({ attributes }) {
        const { style } = attributes;
        return `[fs-shortcode style="${style}"]`;
    },
});