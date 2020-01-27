/**
 * WordPress admin enhancements, specific to plugin's up-sell admin notice.
 *
 * @since       1.3.1
 * @package     Woo Store Vacation
 * @author      MyPreview (Github: @mahdiyazdani, @mypreview)
 */
( function( wp, $ ) {
	'use strict';

	if ( ! wp ) {
		return;
	} // End If Statement

	$( function() {
		// Dismiss up-sell banner notice on user click!
		$( document ).on( 'click', '.notice-error.woocommerce-message.is-dismissible .notice-dismiss', function() {
			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: { 
					_ajax_nonce: wsv_vars.dismiss_nonce, 
					action: 'woo_store_vacation_dismiss_upsell' 
				},
				dataType: 'json'
			} );
		} );
	} );

} )( window.wp, jQuery );