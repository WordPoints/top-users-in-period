<?php

/**
 * Fixed widget class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Displays the top users in a fixed period.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Widget_Fixed
	extends WordPoints_Points_Widget_Top_Users {

	/**
	 * @since 1.0.0
	 */
	public function __construct() {

		WordPoints_Points_Widget::__construct(
			__CLASS__
			, _x( 'Top Users In Fixed Period', 'widget name', 'wordpoints-top-users-in-period' )
			, array(
				'description' => __( 'Showcase the users who earned the most points between two fixed dates.', 'wordpoints-top-users-in-period' ),
				'wordpoints_hook_slug' => 'top_users_in_period_fixed',
			)
		);

		$this->defaults = array(
			'title'       => _x( 'Top Users', 'widget title', 'wordpoints-top-users-in-period' ),
			'points_type' => wordpoints_get_default_points_type(),
			'num_users'   => 3,
			'from'        => current_time( 'Y-m-d' ),
			'from_time'   => '00:00',
			'to'          => '',
			'to_time'     => '23:59',
		);
	}

	/**
	 * @since 1.0.0
	 */
	protected function verify_settings( $instance ) {

		if ( empty( $instance['from'] ) ) {
			$instance['from'] = $this->defaults['from'];
		}

		if ( empty( $instance['from_time'] ) ) {
			$instance['from_time'] = $this->defaults['from_time'];
		}

		$timezone = wordpoints_top_users_in_period_get_site_timezone();
		$datetime = "{$instance['from']} {$instance['from_time']}:00";

		if ( ! $this->validate_datetime( $datetime, $timezone ) ) {
			return new WP_Error(
				'wordpoints_top_users_in_period_widget_invalid_from'
				, esc_html__( 'Please enter a valid From date and time.', 'wordpoints-top-users-in-period' )
			);
		}

		if ( ! empty( $instance['to'] ) ) {

			if ( empty( $instance['to_time'] ) ) {
				$instance['to_time'] = $this->defaults['to_time'];
			}

			$datetime = "{$instance['to']} {$instance['to_time']}:59";

			if ( ! $this->validate_datetime( $datetime, $timezone ) ) {
				return new WP_Error(
					'wordpoints_top_users_in_period_widget_invalid_from'
					, esc_html__( 'Please enter a valid To date and time.', 'wordpoints-top-users-in-period' )
				);
			}
		}

		return parent::verify_settings( $instance );
	}

	/**
	 * Validate a datetime string.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $datetime The datetime string.
	 * @param DateTimeZone $timezone The timezone to use.
	 *
	 * @return bool Whether the datetime is valid.
	 */
	protected function validate_datetime( $datetime, $timezone ) {

		try {
			new DateTime( $datetime, $timezone );
		} catch ( Exception $e ) {
			return false;
		}

		// Requires PHP 5.3+.
		if ( ! function_exists( 'DateTime::getLastErrors' ) ) {
			return true;
		}

		$errors = DateTime::getLastErrors();

		if ( 0 !== $errors['error_count'] || 0 !== $errors['warning_count'] ) {
			return false;
		}

		return true;
	}

	/**
	 * @since 1.0.0
	 */
	protected function widget_body( $instance ) {

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$from = new DateTime(
			"{$instance['from']} {$instance['from_time']}:00"
			, $timezone
		);

		$to = null;

		if ( ! empty( $instance['to'] ) ) {
			$to = new DateTime(
				"{$instance['to']} {$instance['to_time']}:59"
				, $timezone
			);
		}

		$args = array(
			'points_type'     => $instance['points_type'],
			'limit'           => $instance['num_users'],
			'user_id__not_in' => wordpoints_get_excluded_users(
				'top_users_in_period_widget'
			),
		);

		$query = new WordPoints_Top_Users_In_Period_Query( $from, $to, $args );
		$table = new WordPoints_Top_Users_In_Period_Table( $query, 'widget' );
		$table->display();
	}

	/**
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {

		parent::update( $new_instance, $old_instance );

		$this->instance['from']      = sanitize_text_field( $this->instance['from'] );
		$this->instance['from_time'] = sanitize_text_field( $this->instance['from_time'] );
		$this->instance['to']        = sanitize_text_field( $this->instance['to'] );
		$this->instance['to_time']   = sanitize_text_field( $this->instance['to_time'] );

		return $this->instance;
	}

	/**
	 * @since 1.0.0
	 */
	public function form( $instance ) {

		wp_enqueue_style( 'wordpoints-top-users-in-period-widget-settings' );
		wp_enqueue_style( 'wordpoints-top-users-in-period-datepicker' );
		wp_enqueue_script( 'wordpoints-top-users-in-period-datepicker' );

		parent::form( $instance );

		?>

		<div class="wordpoints-top-users-in-period-form">
			<p>
				<label>
					<span class="screen-reader-text">
						<?php esc_html_e( 'From date (inclusive)', 'wordpoints-top-users-in-period' ); ?>
					</span>
					<span aria-hidden="true">
						<?php esc_html_e( 'From (inclusive)', 'wordpoints-top-users-in-period' ); ?>
					</span>
					<input
						class="wordpoints-top-users-in-period-from widefat"
						name="<?php echo esc_attr( $this->get_field_name( 'from' ) ); ?>"
						type="date"
						placeholder="<?php esc_attr_e( 'Date', 'wordpoints-top-users-in-period' ); ?>"
						value="<?php echo esc_attr( $this->instance['from'] ); ?>"
					>
				</label>
				<label>
					<span class="screen-reader-text">
						<?php esc_html_e( 'From time (inclusive)', 'wordpoints-top-users-in-period' ); ?>
					</span>
					<input
						class="widefat"
						name="<?php echo esc_attr( $this->get_field_name( 'from_time' ) ); ?>"
						type="time"
						placeholder="<?php esc_attr_e( 'Time', 'wordpoints-top-users-in-period' ); ?>"
						value="<?php echo esc_attr( $this->instance['from_time'] ); ?>"
					>
				</label>
			</p>

			<p>
				<label>
					<span class="screen-reader-text">
						<?php esc_html_e( 'To date (inclusive) (default: now)', 'wordpoints-top-users-in-period' ); ?>
					</span>
					<span aria-hidden="true">
						<?php esc_html_e( 'To (inclusive)', 'wordpoints-top-users-in-period' ); ?>
					</span>
					<input
						class="wordpoints-top-users-in-period-to widefat"
						name="<?php echo esc_attr( $this->get_field_name( 'to' ) ); ?>"
						type="date"
						placeholder="<?php esc_attr_e( 'Date (default: now)', 'wordpoints-top-users-in-period' ); ?>"
						value="<?php echo esc_attr( $this->instance['to'] ); ?>"
					>
				</label>
				<label>
					<span class="screen-reader-text">
						<?php esc_html_e( 'To time (inclusive)', 'wordpoints-top-users-in-period' ); ?>
					</span>
					<input
						class="widefat"
						name="<?php echo esc_attr( $this->get_field_name( 'to_time' ) ); ?>"
						type="time"
						placeholder="<?php esc_attr_e( 'Time', 'wordpoints-top-users-in-period' ); ?>"
						value="<?php echo esc_attr( $this->instance['to_time'] ); ?>"
					>
				</label>
			</p>
		</div>

		<?php

		return true;
	}
}

// EOF
