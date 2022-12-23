/* global jQuery, ajaxurl, wsvVars */

( function ( wp, $ ) {
	'use strict';

	if ( ! wp ) {
		return;
	}

	const wsvUpsell = {
		cache() {
			this.vars = {};
			this.els = {};
			this.vars.rate = '#woo-store-vacation-dismiss-rate .notice-dismiss';
			this.vars.upsell = '#woo-store-vacation-dismiss-upsell .notice-dismiss';
		},

		init() {
			this.cache();
			this.events();
		},

		events() {
			$( document.body ).on( 'click', this.vars.rate, ( event ) => this.handleOnDismiss( event, 'rate' ) );
			$( document.body ).on( 'click', this.vars.upsell, ( event ) => this.handleOnDismiss( event, 'upsell' ) );
		},

		handleOnDismiss( event, action ) {
			event.preventDefault();

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					_ajax_nonce: wsvVars.dismiss_nonce,
					action: `woo_store_vacation_dismiss_${ action }`,
				},
				dataType: 'json',
			} );
		},
	};

	wsvUpsell.init();
} )( window.wp, jQuery );
