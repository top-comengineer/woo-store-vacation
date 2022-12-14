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
			this.vars.upsell = '#woo-store-vacation-dismiss-upsell .notice-dismiss';
		},

		init() {
			this.cache();
			this.events();
		},

		events() {
			$( document.body ).on( 'click', this.vars.upsell, this.handleOnDismiss );
		},

		handleOnDismiss() {
			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					_ajax_nonce: wsvVars.dismiss_nonce,
					action: 'woo_store_vacation_dismiss_upsell',
				},
				dataType: 'json',
			} );
		},
	};

	wsvUpsell.init();
} )( window.wp, jQuery );
