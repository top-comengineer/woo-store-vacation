/* global ajaxurl, wsvVars */

/**
 * WordPress admin enhancements, specific to plugin's up-sell admin notice.
 *
 * @since       1.3.8
 */
( function ( wp, $ ) {
	'use strict';

	if ( ! wp ) {
		return;
	} // End If Statement

	$( function () {
		// Dismiss up-sell banner notice on user click!
		$( document ).on( 'click', '.notice-info.woocommerce-message.is-dismissible .notice-dismiss', function () {
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
