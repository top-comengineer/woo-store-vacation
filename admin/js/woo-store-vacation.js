/**
 * Initialize jQuery datepicker, colorpicker
 *
 * @author      Mahdi Yazdani
 * @package     Woo Store Vacation
 * @since       1.0.4
 */
(function($) {
    $(function() {
    	'use strict';
    	$('.woo-store-vacation-start-datepicker').datepicker({
                dateFormat : 'dd-mm-yy',
                minDate: 0,
        });
		$('.woo-store-vacation-end-datepicker').datepicker({
                dateFormat : 'dd-mm-yy',
                minDate: 1,
        });
        $('.woo-store-vacation-text-color-field').wpColorPicker({
            'defaultColor': '#ffffff'
        });
	    $('.woo-store-vacation-background-color-field').wpColorPicker({
            'defaultColor': '#e2401c'
        });
    }); // end of document ready
})(jQuery); // end of jQuery name space