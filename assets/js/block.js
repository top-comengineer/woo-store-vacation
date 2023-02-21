( function ( wp ) {
	'use strict';

	if ( ! wp ) {
		return;
	}

	const el = wp.element.createElement;
	const { registerBlockType } = wp.blocks;
	const { Notice } = wp.components;
	const { __ } = wp.i18n;

	registerBlockType( 'mypreview/woo-store-vacation', {
		title: __( 'Store Vacation Notice', 'woo-store-vacation' ),
		description: __( 'Placeholder block for displaying store vacation notice.', 'woo-store-vacation' ),
		icon: { foreground: '#7f54b3', src: 'palmtree' },
		category: 'woocommerce',
		supports: { html: false },
		edit: () => {
			return el(
				Notice,
				{
					className: 'wc-blocks-sidebar-compatibility-notice is-dismissible',
					isDismissible: false,
					status: 'warning',
				},
				__(
					'⚠ This alert-box is a placeholder that is displayed in place of the actual vacation notice message.',
					'woo-store-vacation'
				)
			);
		},
		save: () => {
			return '[woo_store_vacation]';
		},
	} );
} )( window.wp );
