/**
 * acklabs/clients-carousel — Block Editor Script (no-build)
 * Uses MediaUpload to let users choose logo images from the Media Library.
 */
(function () {
	var registerBlockType = wp.blocks.registerBlockType;
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var useBlockProps     = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload       = wp.blockEditor.MediaUpload;
	var MediaUploadCheck  = wp.blockEditor.MediaUploadCheck;
	var PanelBody         = wp.components.PanelBody;
	var Button            = wp.components.Button;
	var TextControl       = wp.components.TextControl;
	var __                = wp.i18n.__;

	registerBlockType( 'acklabs/clients-carousel', {

		edit: function ( props ) {
			var attributes    = props.attributes;
			var setAttributes = props.setAttributes;
			var items         = attributes.items || [];
			var label         = attributes.label || '';

			function addItem( media ) {
				setAttributes( {
					items: items.concat( [ {
						imageId:  media.id,
						imageUrl: media.url,
						altText:  media.alt || media.title || '',
						linkUrl:  ''
					} ] )
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

			function replaceImage( index, media ) {
				updateItem( index, 'imageId',  media.id );
				updateItem( index, 'imageUrl', media.url );
				updateItem( index, 'altText',  media.alt || media.title || '' );
			}

			function removeItem( index ) {
				setAttributes( {
					items: items.filter( function ( _, i ) { return i !== index; } )
				} );
			}

			var blockProps = useBlockProps( { className: 'clients-carousel-editor' } );

			return el( Fragment, null,

				el( InspectorControls, null,
					el( PanelBody, { title: __( 'Carousel Settings', 'acklabs' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Section Label', 'acklabs' ),
							placeholder: 'Trusted by…',
							value: label,
							onChange: function ( v ) { setAttributes( { label: v } ); }
						} )
					),
					el( PanelBody, { title: __( 'Logos', 'acklabs' ), initialOpen: true },
						items.map( function ( item, index ) {
							return el( 'div', {
								key: index,
								style: { borderBottom: '1px solid #3a3a3a', paddingBottom: '16px', marginBottom: '16px' }
							},
								item.imageUrl && el( 'img', {
									src: item.imageUrl,
									alt: item.altText,
									style: { maxWidth: '100%', maxHeight: '48px', objectFit: 'contain', marginBottom: '8px', display: 'block' }
								} ),
								el( MediaUploadCheck, null,
									el( MediaUpload, {
										onSelect: function ( media ) { replaceImage( index, media ); },
										allowedTypes: [ 'image' ],
										value: item.imageId,
										render: function ( obj ) {
											return el( Button, {
												variant: 'secondary',
												onClick: obj.open,
												style: { marginBottom: '8px', display: 'block' }
											}, __( 'Replace Image', 'acklabs' ) );
										}
									} )
								),
								el( TextControl, {
									label: __( 'Alt Text', 'acklabs' ),
									value: item.altText || '',
									onChange: function ( v ) { updateItem( index, 'altText', v ); }
								} ),
								el( TextControl, {
									label: __( 'Link URL (optional)', 'acklabs' ),
									value: item.linkUrl || '',
									type: 'url',
									onChange: function ( v ) { updateItem( index, 'linkUrl', v ); }
								} ),
								el( Button, {
									variant: 'secondary',
									isDestructive: true,
									onClick: function () { removeItem( index ); }
								}, __( 'Remove', 'acklabs' ) )
							);
						} ),
						el( MediaUploadCheck, null,
							el( MediaUpload, {
								onSelect: addItem,
								allowedTypes: [ 'image' ],
								multiple: false,
								render: function ( obj ) {
									return el( Button, {
										variant: 'primary',
										onClick: obj.open,
										style: { marginTop: '8px' }
									}, __( '+ Add Logo', 'acklabs' ) );
								}
							} )
						)
					)
				),

				el( 'div', blockProps,
					el( 'div', { className: 'clients-section' },
						label && el( 'p', { className: 'clients-label' }, label ),
						items.length === 0
							? el( 'p', { style: { color: '#7a8fa6', padding: '16px', textAlign: 'center', border: '1px dashed rgba(255,255,255,0.15)', borderRadius: '8px' } },
									__( 'Add logos using the block settings panel →', 'acklabs' )
								)
							: el( 'div', { className: 'carousel-track-wrap' },
									el( 'div', { className: 'carousel-track', style: { animationPlayState: 'paused' } },
										items.map( function ( item, i ) {
											return el( 'img', {
												key: i,
												className: 'carousel-logo',
												src: item.imageUrl,
												alt: item.altText || ''
											} );
										} )
									)
								)
					)
				)
			);
		},

		save: function () { return null; }
	} );
}() );
