/**
 * acklabs/eyebrow — Block Editor Script (no-build)
 */
(function () {
	var registerBlockType = wp.blocks.registerBlockType;
	var el                = wp.element.createElement;
	var useBlockProps     = wp.blockEditor.useBlockProps;
	var PlainText         = wp.blockEditor.PlainText;
	var __                = wp.i18n.__;

	registerBlockType( 'acklabs/eyebrow', {

		edit: function ( props ) {
			var text         = props.attributes.text;
			var setAttributes = props.setAttributes;
			var blockProps   = useBlockProps( { className: 'eyebrow' } );

			return el(
				'div',
				blockProps,
				el( PlainText, {
					value: text,
					onChange: function ( val ) { setAttributes( { text: val } ); },
					placeholder: __( 'Eyebrow text…', 'acklabs' ),
					style: {
						background: 'transparent',
						border: 'none',
						padding: 0,
						font: 'inherit',
						color: 'inherit',
						letterSpacing: 'inherit',
						textTransform: 'inherit',
						width: '100%',
					},
				} )
			);
		},

		save: function () { return null; },
	} );
}() );
