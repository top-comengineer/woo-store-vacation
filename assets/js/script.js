/**
 * Initialize jQuery datepicker, colorpicker
 *
 * @since       1.3.0
 * @package     Woo Store Vacation
 */
( function( wp, $ ) {
        'use strict';

        if ( ! wp ) {
            return;
        } // End If Statement

        var getDate = new Date(),
            targetTimeOffset = 60,
            startDate = $( '.woo-store-vacation-start-datepicker' ),
            endDate = $( '.woo-store-vacation-end-datepicker' );
        
        getDate.setMinutes( getDate.getMinutes() + getDate.getTimezoneOffset() + targetTimeOffset );

        // In order to work, we have to call this before any call to datepicker.
        cleanDatepicker();

        startDate.datepicker( {
            minDate: getDate,
            changeMonth: true,
            showButtonPanel: true,
            dateFormat: 'yy-mm-dd',
            onClose: function( selectedDate, inst ) {
                var minDate = new Date( Date.parse( selectedDate ) );
                minDate.setDate( minDate.getDate() + 1 );
                endDate.datepicker( 'option', 'minDate', minDate );
            }
        } );

        endDate.datepicker( {
            minDate: '+1D',
            changeMonth: true,
            showButtonPanel: true,
            dateFormat: 'yy-mm-dd',
            onClose: function( selectedDate, inst ) {
                var maxDate = new Date( Date.parse( selectedDate ) );
                maxDate.setDate( maxDate.getDate() - 1 );            
                startDate.datepicker( 'option', 'maxDate', maxDate );
            }
        } );

        $( '.woo-store-vacation-text-color-field, .woo-store-vacation-background-color-field' ).wpColorPicker();

        // Adds a "Reset" control to the datepicker at the bottom
        function cleanDatepicker() {
           var old_fn = $.datepicker._updateDatepicker;

           $.datepicker._updateDatepicker = function( inst ) {
              old_fn.call( this, inst );
              var buttonPane = $( this ).datepicker( 'widget' ).find( '.ui-datepicker-buttonpane' );
              $( "<button type='button' class='ui-datepicker-clean ui-state-default ui-priority-primary ui-corner-all'>Delete</button>" ).appendTo( buttonPane ).on( 'click', function( ev ) {
                  $.datepicker._clearDate( inst.input );
              } ) ;
           }
        }

} )( window.wp, jQuery );