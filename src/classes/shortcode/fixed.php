<?php

/**
 * Fixed period shortcode class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * A shortcode that displays the top users over a fixed period.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Shortcode_Fixed
	extends WordPoints_Points_Shortcode_Top_Users {

	/**
	 * @since 1.0.0
	 */
	protected $pairs = array(
		'users'       => 10,
		'points_type' => '',
		'from'        => '',
		'from_time'   => '00:00',
		'to'          => '',
		'to_time'     => '23:59',
	);

	/**
	 * @since 1.0.0
	 */
	protected function verify_atts() {

		if ( empty( $this->atts['from'] ) ) {
			$instance['from'] = current_time( 'Y-m-d' );
		}

		$timezone = wordpoints_top_users_in_period_get_site_timezone();
		$datetime = "{$this->atts['from']} {$this->atts['from_time']}:00";

		if ( ! wordpoints_top_users_in_period_validate_datetime( $datetime, $timezone ) ) {
			return new WP_Error(
				'wordpoints_top_users_in_period_widget_invalid_from'
				, esc_html__( 'Please enter a valid From date and time.', 'wordpoints-top-users-in-period' )
			);
		}

		if ( ! empty( $this->atts['to'] ) ) {

			$datetime = "{$this->atts['to']} {$this->atts['to_time']}:59";

			if ( ! wordpoints_top_users_in_period_validate_datetime( $datetime, $timezone ) ) {
				return new WP_Error(
					'wordpoints_top_users_in_period_widget_invalid_from'
					, esc_html__( 'Please enter a valid To date and time.', 'wordpoints-top-users-in-period' )
				);
			}
		}

		return parent::verify_atts();
	}

	/**
	 * @since 1.0.0
	 */
	protected function generate() {

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$from = new DateTime(
			"{$this->atts['from']} {$this->atts['from_time']}:00"
			, $timezone
		);

		$to = null;

		if ( ! empty( $this->atts['to'] ) ) {
			$to = new DateTime(
				"{$this->atts['to']} {$this->atts['to_time']}:59"
				, $timezone
			);
		}

		$args = array(
			'points_type'     => $this->atts['points_type'],
			'limit'           => $this->atts['users'],
			'user_id__not_in' => wordpoints_get_excluded_users(
				'top_users_in_period_widget'
			),
		);

		$query = new WordPoints_Top_Users_In_Period_Query( $from, $to, $args );
		$table = new WordPoints_Top_Users_In_Period_Table( $query, 'shortcode' );

		ob_start();
		$table->display();
		return ob_get_clean();
	}
}

// EOF
