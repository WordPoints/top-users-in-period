/**
 * Adds date-picker support to the admin screen.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

jQuery( function( $ ) {

	var $form = $( '.wordpoints-top-users-in-period-form' );

	function getDate( element ) {

		var date;

		try {
			date = $.datepicker.parseDate( 'yy-mm-dd', element.value );
		} catch ( error ) {
			date = null;
		}

		return date;
	}

	function initDatePicker ( el ) {

		var $el = $( el );

		var $from = $el.find( '.wordpoints-top-users-in-period-from' )
			.datepicker( {
				showOtherMonths:   true,
				selectOtherMonths: true,
				dateFormat:        'yy-mm-dd',
				maxDate:           0
			} )
			.on( 'change', function () {
				$to.datepicker( 'option', 'minDate', getDate( this ) );
			} );

		var $to = $el.find( '.wordpoints-top-users-in-period-to' )
			.datepicker( {
				showOtherMonths:   true,
				selectOtherMonths: true,
				dateFormat:        'yy-mm-dd'
			} )
			.on( 'change', function () {
				$from.datepicker( 'option', 'maxDate', getDate( this ) );
			} );

		$from.change();
		$to.change();
	}

	$form.each( initDatePicker );

	$( '.wrap' ).on(
		'focus'
		, '.wordpoints-top-users-in-period-from, .wordpoints-top-users-in-period-to'
		, function() {

			var $this = $( this );

			if ( ! $this.hasClass( 'hasDatepicker' ) ) {
				initDatePicker( $this.closest( '.wordpoints-top-users-in-period-form' ) );
			}
		}
	);

} );

// EOF
