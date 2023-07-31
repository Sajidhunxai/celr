( function( editor, components, i18n, element, $ ) {
	"use strict";

	const __ = i18n.__;
	const el = element.createElement;
	const compose = wp.compose.compose;
	const registerPlugin = wp.plugins.registerPlugin;

	const {
		Fragment,
		Component 
	} = element;


	const {
		TextareaControl,
		PanelBody
	} = components;

	const {
		dispatch,
		withSelect,
		withDispatch 
	} = wp.data;

	const {
		PluginSidebar,
		PluginSidebarMoreMenuItem 
	} = wp.editPost;

	const Icon = el( 'svg', {
			height: '20px',
			width: '20px',
			viewBox: '0 0 17.39 17.39'
		}, el ( 'polygon', {
			points: '14.77 11.19 17.3 8.65 14.77 6.12 14.77 2.53 11.19 2.53 8.65 0 6.12 2.53 2.53 2.53 2.53 6.12 0 8.65 2.53 11.19 2.53 14.77 6.12 14.77 8.65 17.3 11.19 14.77 14.77 14.77 14.77 11.19'
		} )
	);

	function LoftLoaderPlugin( props ) {
		return el( Fragment, {},
			el( PluginSidebarMoreMenuItem, { target: 'loftloader-any-page' }, __( 'LoftLoader Any Page Shortcode' ) ),
			el( PluginSidebar, { name: 'loftloader-any-page', title: __( 'LoftLoader Any Page Shortcode' ) },
				el( PanelBody, { 
						className: 'loftloader-any-page-sidebar',
						initialOpen: true
					},
					el( TextareaControl, {
						label: __( 'Paste LoftLoader shortcode into the box below' ),
						value: props.meta.loftloader_page_shortcode,
						onChange: ( value ) => {
							props.updateValue( { loftloader_page_shortcode: value } );
						}
					} )
				),
				el( 'input', {
					type: 'hidden',
					name: 'loftloader_gutenberg_enabled', 
					value: 'on'
				} )
			)
		);
	}

	// Fetch the post meta.
	const applyWithSelect = withSelect( ( select ) => { 
		const { getEditedPostAttribute } = select( 'core/editor' );
		return { meta: getEditedPostAttribute( 'meta' ) };
	} );

	const applyWithDispatch = withDispatch( ( dispatch ) => {
		const { editPost } = dispatch( 'core/editor' );
		return {
			updateValue: function( value ) {
				editPost( { meta: { ...value } } );
			}
		}
	} );

	const render = compose( [ applyWithSelect, applyWithDispatch ] )( LoftLoaderPlugin );

	registerPlugin( 'loftloader-any-page', {
		icon: Icon,
		render
	} );
} )(
	window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element,
	jQuery
);