/**
 * Initialize jQuery datepicker, colorpicker
 *
 * @since       1.3.9
 */
( function ( wp, $ ) {
	'use strict';

	if ( ! wp ) {
		return;
	} // End If Statement

	const $startDate = $( '.woo-store-vacation-start-datepicker' ),
		$endDate = $( '.woo-store-vacation-end-datepicker' );

	// In order to work, we have to call this before any call to datepicker.
	cleanDatepicker();

	$startDate.datepicker( {
		changeMonth: true,
		showButtonPanel: true,
		dateFormat: 'yy-mm-dd',
		onClose( selectedDate ) {
			const minDate = new Date( Date.parse( selectedDate ) );
			minDate.setDate( minDate.getDate() + 1 );
			$endDate.datepicker( 'option', 'minDate', minDate );
		},
	} );

	$endDate.datepicker( {
		minDate: '+1D',
		changeMonth: true,
		showButtonPanel: true,
		dateFormat: 'yy-mm-dd',
		onClose( selectedDate ) {
			const maxDate = new Date( Date.parse( selectedDate ) );
			maxDate.setDate( maxDate.getDate() - 1 );
			$startDate.datepicker( 'option', 'maxDate', maxDate );
		},
	} );

	// Override the _goToToday method outside of the library itself.
	const oldGoToToday = $.datepicker._gotoToday;
	$.datepicker._gotoToday = function ( id ) {
		oldGoToToday.call( this, id );
		this._selectDate( id );
	};

	$( '.woo-store-vacation-text-color-field, .woo-store-vacation-background-color-field' ).wpColorPicker();

	// Adds a "Reset" control to the datepicker at the bottom
	function cleanDatepicker() {
		const oldFn = $.datepicker._updateDatepicker;

		$.datepicker._updateDatepicker = function ( inst ) {
			oldFn.call( this, inst );
			const buttonPane = $( this ).datepicker( 'widget' ).find( '.ui-datepicker-buttonpane' );
			$(
				"<button type='button' class='ui-datepicker-clean ui-state-default ui-priority-primary ui-corner-all'>Delete</button>"
			)
				.appendTo( buttonPane )
				.on( 'click', function () {
					$.datepicker._clearDate( inst.input );
				} );
		};
	}
} )( window.wp, jQuery );
