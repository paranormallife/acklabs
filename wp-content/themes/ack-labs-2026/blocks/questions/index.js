/**
 * acklabs/questions — Block Editor Script (no-build)
 * Uses wp.element.createElement instead of JSX.
 */
(function () {
	var registerBlockType = wp.blocks.registerBlockType;
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var useBlockProps     = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody         = wp.components.PanelBody;
	var Button            = wp.components.Button;
	var TextControl       = wp.components.TextControl;
	var TextareaControl   = wp.components.TextareaControl;
	var __                = wp.i18n.__;

	registerBlockType( 'acklabs/questions', {

		edit: function ( props ) {
			var attributes   = props.attributes;
			var setAttributes = props.setAttributes;
			var items        = attributes.items || [];

			function addItem() {
				setAttributes( {
					items: items.concat( [ { dimLabel: '', questionText: '', answer: '' } ] )
				} );
			}

			function updateItem( index, field, value ) {
				var newItems = items.map( function ( item, i ) {
					if ( i !== index ) return item;
					var updated = Object.assign( {}, item );
					updated[ field ] = value;
					return updated;
				} );
				setAttributes( { items: newItems } );
			}

			function removeItem( index ) {
				setAttributes( {
					items: items.filter( function ( _, i ) { return i !== index; } )
				} );
			}

			var blockProps = useBlockProps( { className: 'questions-editor-wrap' } );

			return el( Fragment, null,

				// ── Inspector Controls ──────────────────────────────────
				el( InspectorControls, null,
					el( PanelBody, { title: __( 'Questions', 'acklabs' ), initialOpen: true },
						items.map( function ( item, index ) {
							return el( 'div', {
								key: index,
								style: { borderBottom: '1px solid #3a3a3a', paddingBottom: '16px', marginBottom: '16px' }
							},
								el( 'p', { style: { fontWeight: 600, marginBottom: '8px', color: '#e8a045' } },
									__( 'Question', 'acklabs' ) + ' ' + ( index + 1 )
								),
								el( TextControl, {
									label: __( 'Dim Label', 'acklabs' ),
									value: item.dimLabel || '',
									onChange: function ( v ) { updateItem( index, 'dimLabel', v ); }
								} ),
								el( TextControl, {
									label: __( 'Question Text', 'acklabs' ),
									value: item.questionText || '',
									onChange: function ( v ) { updateItem( index, 'questionText', v ); }
								} ),
								el( TextareaControl, {
									label: __( 'Answer', 'acklabs' ),
									value: item.answer || '',
									onChange: function ( v ) { updateItem( index, 'answer', v ); }
								} ),
								el( Button, {
									variant: 'secondary',
									isDestructive: true,
									onClick: function () { removeItem( index ); }
								}, __( 'Remove', 'acklabs' ) )
							);
						} ),
						el( Button, {
							variant: 'primary',
							onClick: addItem,
							style: { marginTop: '8px' }
						}, __( '+ Add Question', 'acklabs' ) )
					)
				),

				// ── Block Preview ───────────────────────────────────────
				el( 'div', blockProps,
					items.length === 0
						? el( 'p', { style: { color: '#7a8fa6', padding: '16px', border: '1px dashed rgba(255,255,255,0.15)', borderRadius: '8px' } },
								__( 'Add questions using the block settings panel →', 'acklabs' )
							)
						: el( 'div', { className: 'questions' },
								items.map( function ( item, i ) {
									return el( 'div', { key: i, className: 'question' },
										el( 'div', { className: 'question-trigger' },
											el( 'div', { className: 'question-left' },
												item.dimLabel && el( 'span', { className: 'dim-label' }, item.dimLabel ),
												el( 'div', { className: 'question-text' },
													item.questionText || el( 'em', { style: { opacity: 0.4 } }, __( 'Question text…', 'acklabs' ) )
												)
											),
											el( 'svg', { className: 'question-chevron', viewBox: '0 0 20 20', fill: 'none' },
												el( 'path', { d: 'M5 8l5 5 5-5', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round' } )
											)
										),
										item.answer && el( 'div', { className: 'question-expand', style: { display: 'block', maxHeight: 'none', padding: '0 0 16px' } },
											item.answer
										)
									);
								} )
							)
				)
			);
		},

		save: function () {
			return null; // Dynamic block — rendered via render.php
		}
	} );
}() );
