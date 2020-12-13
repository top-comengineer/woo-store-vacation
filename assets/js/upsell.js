/* global ajaxurl, wsvVars */

/**
 * WordPress admin enhancements, specific to plugin's up-sell admin notice.
 *
 * @since       1.4.0
 */
( function ( wp, $ ) {
	'use strict';

	if ( ! wp ) {
		return;
	} // End If Statement

	$( function () {
		// Dismiss up-sell banner notice on user click!
		$( document ).on( 'click', '#woo-store-vacation-dismiss-upsell .notice-dismiss', function () {
			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					_ajax_nonce: wsvVars.dismiss_nonce,
					action: 'woo_store_vacation_dismiss_upsell',
				},
				dataType: 'json',
			} );
		} );
	} );
} )( window.wp, jQuery );
