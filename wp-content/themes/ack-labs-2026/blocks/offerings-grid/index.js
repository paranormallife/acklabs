/**
 * acklabs/offerings-grid — Block Editor Script (no-build)
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

	registerBlockType( 'acklabs/offerings-grid', {

		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var items         = attributes.items || [];

			function addItem() {
				setAttributes( {
					items: items.concat( [ { stepLabel: '', title: '', description: '', url: '' } ] )
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

			var blockProps = useBlockProps( { className: 'offerings-section-editor' } );

			return el( Fragment, null,

				el( InspectorControls, null,
					el( PanelBody, { title: __( 'Offerings', 'acklabs' ), initialOpen: true },
						items.map( function ( item, index ) {
							return el( 'div', {
								key: index,
								style: { borderBottom: '1px solid #3a3a3a', paddingBottom: '16px', marginBottom: '16px' }
							},
								el( 'p', { style: { fontWeight: 600, marginBottom: '8px', color: '#e8a045' } },
									__( 'Card', 'acklabs' ) + ' ' + ( index + 1 )
								),
								el( TextControl, {
									label: __( 'Step Label', 'acklabs' ),
									placeholder: 'Step 01',
									value: item.stepLabel || '',
									onChange: function ( v ) { updateItem( index, 'stepLabel', v ); }
								} ),
								el( TextControl, {
									label: __( 'Title', 'acklabs' ),
									value: item.title || '',
									onChange: function ( v ) { updateItem( index, 'title', v ); }
								} ),
								el( TextareaControl, {
									label: __( 'Description', 'acklabs' ),
									value: item.description || '',
									onChange: function ( v ) { updateItem( index, 'description', v ); }
								} ),
								el( TextControl, {
									label: __( 'Link URL (optional)', 'acklabs' ),
									value: item.url || '',
									type: 'url',
									onChange: function ( v ) { updateItem( index, 'url', v ); }
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
						}, __( '+ Add Offering', 'acklabs' ) )
					)
				),

				el( 'div', blockProps,
					items.length === 0
						? el( 'p', { style: { color: '#7a8fa6', padding: '16px', border: '1px dashed rgba(255,255,255,0.15)', borderRadius: '8px' } },
								__( 'Add offering cards using the block settings panel →', 'acklabs' )
							)
						: el( 'div', { className: 'offerings-grid' },
								items.map( function ( item, i ) {
									return el( 'div', { key: i, className: 'offering-card' },
										item.stepLabel && el( 'div', { className: 'offering-step' }, item.stepLabel ),
										el( 'div', { className: 'offering-title' },
											item.title || el( 'em', { style: { opacity: 0.4 } }, __( 'Title…', 'acklabs' ) )
										),
										item.description && el( 'div', { className: 'offering-desc' }, item.description )
									);
								} )
							)
				)
			);
		},

		save: function () { return null; }
	} );
}() );
