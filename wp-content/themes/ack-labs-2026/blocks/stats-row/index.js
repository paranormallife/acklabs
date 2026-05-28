/**
 * acklabs/stats-row — Block Editor Script (no-build)
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
	var __                = wp.i18n.__;

	registerBlockType( 'acklabs/stats-row', {

		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var items         = attributes.items || [];

			function addItem() {
				setAttributes( {
					items: items.concat( [ { statNumber: '', statLabel: '' } ] )
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

			var blockProps = useBlockProps( { className: 'stats-row-editor' } );

			return el( Fragment, null,

				el( InspectorControls, null,
					el( PanelBody, { title: __( 'Stats', 'acklabs' ), initialOpen: true },
						items.map( function ( item, index ) {
							return el( 'div', {
								key: index,
								style: { borderBottom: '1px solid #3a3a3a', paddingBottom: '16px', marginBottom: '16px' }
							},
								el( 'p', { style: { fontWeight: 600, marginBottom: '8px', color: '#e8a045' } },
									__( 'Stat', 'acklabs' ) + ' ' + ( index + 1 )
								),
								el( TextControl, {
									label: __( 'Number / Value', 'acklabs' ),
									placeholder: '12+',
									value: item.statNumber || '',
									onChange: function ( v ) { updateItem( index, 'statNumber', v ); }
								} ),
								el( TextControl, {
									label: __( 'Label', 'acklabs' ),
									placeholder: 'Years of experience',
									value: item.statLabel || '',
									onChange: function ( v ) { updateItem( index, 'statLabel', v ); }
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
						}, __( '+ Add Stat', 'acklabs' ) )
					)
				),

				el( 'div', blockProps,
					items.length === 0
						? el( 'p', { style: { color: '#7a8fa6', padding: '16px', border: '1px dashed rgba(255,255,255,0.15)', borderRadius: '8px' } },
								__( 'Add stats using the block settings panel →', 'acklabs' )
							)
						: el( 'div', { className: 'stats-row' },
								items.map( function ( item, i ) {
									return el( 'div', { key: i, className: 'stat' },
										el( 'div', { className: 'stat-number' },
											item.statNumber || el( 'span', { style: { opacity: 0.3 } }, '—' )
										),
										el( 'div', { className: 'stat-label' },
											item.statLabel || el( 'span', { style: { opacity: 0.3 } }, __( 'Label', 'acklabs' ) )
										)
									);
								} )
							)
				)
			);
		},

		save: function () { return null; }
	} );
}() );
