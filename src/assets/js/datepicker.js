/**
 * Adds date-picker support to the admin screen.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

jQuery( function( $ ) {

	var $form = $( '#wordpoints-top-users-in-period-admin-form' );

	var $from = $form.find( '[name=from]' )
		.datepicker( {
			showOtherMonths: true,
			selectOtherMonths: true,
			dateFormat: 'yy-mm-dd',
			maxDate: 0
		} )
		.on( 'change', function() {
			$to.datepicker( 'option', 'minDate', getDate( this ) );
		});

	var $to = $form.find( '[name=to]' )
		.datepicker( {
			showOtherMonths: true,
			selectOtherMonths: true,
			dateFormat: 'yy-mm-dd'
		} )
		.on( 'change', function() {
			$from.datepicker( 'option', 'maxDate', getDate( this ) );
		});

	function getDate( element ) {

		var date;

		try {
			date = $.datepicker.parseDate( 'yy-mm-dd', element.value );
		} catch ( error ) {
			date = null;
		}

		return date;
	}

	$from.change();
	$to.change();
} );

// EOF
