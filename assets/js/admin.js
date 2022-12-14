/* global jQuery */

( function ( wp, $ ) {
	'use strict';

	if ( ! wp ) {
		return;
	}

	const { __ } = wp.i18n;
	const wsvAdmin = {
		cache() {
			this.vars = {};
			this.els = {};
			this.els.$startDate = $( '.woo-store-vacation-start-datepicker' );
			this.els.$endDate = $( '.woo-store-vacation-end-datepicker' );
			this.els.$txtColor = $( '.woo-store-vacation-text-color-field' );
			this.els.$bgColor = $( '.woo-store-vacation-background-color-field' );
		},

		init() {
			this.cache();
			this.colorPickers();
			this.datePickers();
			this.events();
		},

		events() {
			$( document.body ).on( 'click', this.vars.upsell, this.handleOnDismiss );
		},

		colorPickers() {
			this.els.$txtColor.wpColorPicker();
			this.els.$bgColor.wpColorPicker();
		},

		datePickers() {
			this.addDeleteButtonDatepicker();

			this.els.$startDate.datepicker( {
				changeMonth: true,
				showButtonPanel: true,
				dateFormat: 'yy-mm-dd',
				onClose( selectedDate ) {
					const minDate = new Date( Date.parse( selectedDate ) );
					minDate.setDate( minDate.getDate() + 1 );
					wsvAdmin.els.$endDate.datepicker( 'option', 'minDate', minDate );
				},
			} );

			this.els.$endDate.datepicker( {
				minDate: '+1D',
				changeMonth: true,
				showButtonPanel: true,
				dateFormat: 'yy-mm-dd',
				onClose( selectedDate ) {
					const maxDate = new Date( Date.parse( selectedDate ) );
					maxDate.setDate( maxDate.getDate() - 1 );
					wsvAdmin.els.$startDate.datepicker( 'option', 'maxDate', maxDate );
				},
			} );

			// Override the _goToToday method outside of the library itself.
			const oldGoToToday = $.datepicker._gotoToday;
			$.datepicker._gotoToday = function ( id ) {
				oldGoToToday.call( this, id );
				this._selectDate( id );
			};
		},

		addDeleteButtonDatepicker() {
			const oldFn = $.datepicker._updateDatepicker;

			$.datepicker._updateDatepicker = function ( inst ) {
				oldFn.call( this, inst );
				const buttonPane = $( this ).datepicker( 'widget' ).find( '.ui-datepicker-buttonpane' );
				$(
					`<button type="button" class="ui-datepicker-clean ui-state-default ui-priority-primary ui-corner-all">
						${ __( 'Delete', 'woo-store-vacation' ) }
					</button>`
				)
					.appendTo( buttonPane )
					.on( 'click', function () {
						$.datepicker._clearDate( inst.input );
					} );
			};
		},
	};

	wsvAdmin.init();
} )( window.wp, jQuery );
