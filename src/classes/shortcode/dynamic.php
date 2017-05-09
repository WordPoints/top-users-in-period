<?php

/**
 * Dynamic period shortcode class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * A shortcode that displays the top users over a dynamic period.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Shortcode_Dynamic
	extends WordPoints_Points_Shortcode_Top_Users {

	/**
	 * @since 1.0.0
	 */
	protected $pairs = array(
		'users'       => 10,
		'points_type' => '',
		'length'      => 1,
		'units'       => 'days',
		'relative_to' => 'present',
	);

	/**
	 * @since 1.0.0
	 */
	protected function verify_atts() {

		if ( ! wordpoints_posint( $this->atts['length'] ) ) {
			$this->atts['length'] = $this->pairs['length'];
		}

		$options = array( 'present' => true, 'calendar' => true );

		if ( ! isset( $this->atts['relative_to'], $options[ $this->atts['relative_to'] ] ) ) {
			$this->atts['relative_to'] = $this->pairs['relative_to'];
		}

		$options = array(
			'seconds' => true,
			'minutes' => true,
			'hours'   => true,
			'days'    => true,
			'weeks'   => true,
			'months'  => true,
		);

		if ( ! isset( $this->atts['units'], $options[ $this->atts['units'] ] ) ) {
			$this->atts['units'] = $this->pairs['units'];
		}

		return parent::verify_atts();
	}

	/**
	 * @since 1.0.0
	 */
	protected function generate() {

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		if ( 'present' === $this->atts['relative_to'] ) {

			$from = new DateTime(
				"-{$this->atts['length']} {$this->atts['units']}"
				, $timezone
			);

			if ( 'minutes' === $this->atts['units'] || 'hours' === $this->atts['units'] ) {
				$from->setTime( $from->format( 'H' ), $from->format( 'i' ) );
			}

			if ( 'days' === $this->atts['units'] ) {
				$from->setTime( $from->format( 'H' ), 0 );
			}

			if ( 'months' === $this->atts['units'] || 'weeks' === $this->atts['units'] ) {
				$from->setTime( 0, 0 );
			}

		} else {

			$from = new DateTime( null, $timezone );

			if ( 'seconds' !== $this->atts['units'] ) {
				$this->atts['length'] -= 1;
			}

			$array = array(
				'seconds' => true,
				'minutes' => true,
				'hours'   => true,
				'days'    => true,
			);

			if (
				0 !== $this->atts['length']
				&& isset( $array[ $this->atts['units'] ] )
			) {
				$from->modify( "-{$this->atts['length']} {$this->atts['units']}" );
			}

			if ( 'minutes' === $this->atts['units'] ) {
				$from->setTime( $from->format( 'H' ), $from->format( 'i' ) );
			}

			if ( 'hours' === $this->atts['units'] ) {
				$from->setTime( $from->format( 'H' ), 0 );
			}

			if ( 'days' === $this->atts['units'] ) {
				$from->setTime( 0, 0 );
			}

			if ( 'months' === $this->atts['units'] ) {
				$from->setTime( 0, 0 );
				$from->setDate(
					$from->format( 'Y' )
					, $from->format( 'm' ) - $this->atts['length']
					, 0
				);
			}

			if ( 'weeks' === $this->atts['units'] ) {

				$from->setTime( 0, 0 );

				$start_of_week = (int) get_option( 'start_of_week' );
				$day_of_week   = (int) $from->format( 'w' );

				// ISO assumes weeks start on Monday (1). For other days, we have to
				// check if a new (non-ISO) week has already started, and take that
				// into account.
				if ( 1 !== $start_of_week && $day_of_week === $start_of_week ) {
					$this->atts['length'] -= 1;
				}

				$from->setISODate(
					$from->format( 'Y' )
					, $from->format( 'W' ) - $this->atts['length']
					, $start_of_week
				);
			}

		} // End if ( relative to present ) else {}.

		$args = array(
			'points_type'     => $this->atts['points_type'],
			'limit'           => $this->atts['users'],
			'user_id__not_in' => wordpoints_get_excluded_users(
				'top_users_in_period_widget'
			),
		);

		$query = new WordPoints_Top_Users_In_Period_Query( $from, null, $args );
		$table = new WordPoints_Top_Users_In_Period_Table( $query, 'shortcode' );

		ob_start();
		$table->display();
		return ob_get_clean();
	}
}

// EOF
