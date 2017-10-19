<?php

/**
 * Dynamic widget class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Displays the top users in a dynamic period.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Widget_Dynamic
	extends WordPoints_Points_Widget_Top_Users {

	/**
	 * @since 1.0.0
	 */
	public function __construct() {

		WordPoints_Points_Widget::__construct(
			__CLASS__
			, _x( 'Top Users In Dynamic Period', 'widget name', 'wordpoints-top-users-in-period' )
			, array(
				'description'          => __( 'Showcase the users who earned the most points in the last month/day/week/etc.', 'wordpoints-top-users-in-period' ),
				'wordpoints_hook_slug' => 'top_users_in_period_dynamic',
			)
		);

		$this->defaults = array(
			'title'           => _x(
				'Top Users In The Last Day'
				, 'widget title'
				, 'wordpoints-top-users-in-period'
			),
			'points_type'     => wordpoints_get_default_points_type(),
			'num_users'       => 3,
			'length_in_units' => 1,
			'relative'        => 'present',
			'units'           => 'days',
		);
	}

	/**
	 * @since 1.0.0
	 */
	protected function verify_settings( $instance ) {

		$instance = $this->validate_settings( $instance );

		return parent::verify_settings( $instance );
	}

	/**
	 * Validate the settings of an instance.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance The instance to validate.
	 *
	 * @return array The validated settings.
	 */
	protected function validate_settings( $instance ) {

		if ( ! wordpoints_posint( $instance['length_in_units'] ) ) {
			$instance['length_in_units'] = $this->defaults['length_in_units'];
		}

		$options = array( 'present' => true, 'calendar' => true );

		if ( ! isset( $instance['relative'], $options[ $instance['relative'] ] ) ) {
			$instance['relative'] = $this->defaults['relative'];
		}

		$options = array(
			'seconds' => true,
			'minutes' => true,
			'hours'   => true,
			'days'    => true,
			'weeks'   => true,
			'months'  => true,
		);

		if ( ! isset( $instance['units'], $options[ $instance['units'] ] ) ) {
			$instance['units'] = $this->defaults['units'];
		}

		return $instance;
	}

	/**
	 * @since 1.0.0
	 */
	protected function widget_body( $instance ) {

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		if ( 'present' === $instance['relative'] ) {

			$from = new DateTime(
				"-{$instance['length_in_units']} {$instance['units']}"
				, $timezone
			);

			if ( 'minutes' === $instance['units'] || 'hours' === $instance['units'] ) {
				$from->setTime( $from->format( 'H' ), $from->format( 'i' ) );
			}

			if ( 'days' === $instance['units'] ) {
				$from->setTime( $from->format( 'H' ), 0 );
			}

			if ( 'months' === $instance['units'] || 'weeks' === $instance['units'] ) {
				$from->setTime( 0, 0 );
			}

		} else {

			$from = new DateTime( null, $timezone );

			if ( 'seconds' !== $instance['units'] ) {
				$instance['length_in_units'] -= 1;
			}

			$array = array(
				'seconds' => true,
				'minutes' => true,
				'hours'   => true,
				'days'    => true,
			);

			if (
				0 !== $instance['length_in_units']
				&& isset( $array[ $instance['units'] ] )
			) {
				$from->modify( "-{$instance['length_in_units']} {$instance['units']}" );
			}

			if ( 'minutes' === $instance['units'] ) {
				$from->setTime( $from->format( 'H' ), $from->format( 'i' ) );
			}

			if ( 'hours' === $instance['units'] ) {
				$from->setTime( $from->format( 'H' ), 0 );
			}

			if ( 'days' === $instance['units'] ) {
				$from->setTime( 0, 0 );
			}

			if ( 'months' === $instance['units'] ) {
				$from->setTime( 0, 0 );
				$from->setDate(
					$from->format( 'Y' )
					, $from->format( 'm' ) - $instance['length_in_units']
					, 0
				);
			}

			if ( 'weeks' === $instance['units'] ) {

				$from->setTime( 0, 0 );

				$start_of_week = (int) get_option( 'start_of_week' );
				$day_of_week   = (int) $from->format( 'w' );

				// ISO assumes weeks start on Monday (1). For other days, we have to
				// check if a new (non-ISO) week has already started, and take that
				// into account.
				if ( 1 !== $start_of_week && $day_of_week === $start_of_week ) {
					$instance['length_in_units'] -= 1;
				}

				$from->setISODate(
					$from->format( 'Y' )
					, $from->format( 'W' ) - $instance['length_in_units']
					, $start_of_week
				);
			}

		} // End if ( relative to present ) else {}.

		$args = array(
			'points_type'     => $instance['points_type'],
			'limit'           => $instance['num_users'],
			'user_id__not_in' => wordpoints_get_excluded_users(
				'top_users_in_period_widget'
			),
		);

		$query = new WordPoints_Top_Users_In_Period_Query( $from, null, $args );
		$table = new WordPoints_Top_Users_In_Period_Table( $query, 'widget' );
		$table->display();
	}

	/**
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {

		parent::update( $new_instance, $old_instance );

		$this->instance = $this->validate_settings( $this->instance );

		return $this->instance;
	}

	/**
	 * @since 1.0.0
	 */
	public function form( $instance ) {

		wp_enqueue_style( 'wordpoints-top-users-in-period-widget-settings' );

		parent::form( $instance );

		?>

		<div>
			<fieldset>
				<legend>
					<?php esc_html_e( 'Show top users based on the last', 'wordpoints-top-users-in-period' ); ?>
				</legend>
				<label>
					<span class="screen-reader-text">
						<?php esc_html_e( 'Time Units', 'wordpoints-top-users-in-period' ); ?>
					</span>
					<select
						name="<?php echo esc_attr( $this->get_field_name( 'units' ) ); ?>"
						class="widefat wordpoints-top-users-in-period-units"
					>
						<option value="seconds"<?php selected( 'seconds', $this->instance['units'] ); ?>>
							<?php esc_html_e( 'Seconds', 'wordpoints-top-users-in-period' ); ?>
						</option>
						<option value="minutes"<?php selected( 'minutes', $this->instance['units'] ); ?>>
							<?php esc_html_e( 'Minutes', 'wordpoints-top-users-in-period' ); ?>
						</option>
						<option value="hours"<?php selected( 'hours', $this->instance['units'] ); ?>>
							<?php esc_html_e( 'Hours', 'wordpoints-top-users-in-period' ); ?>
						</option>
						<option value="days"<?php selected( 'days', $this->instance['units'] ); ?>>
							<?php esc_html_e( 'Days', 'wordpoints-top-users-in-period' ); ?>
						</option>
						<option value="weeks"<?php selected( 'weeks', $this->instance['units'] ); ?>>
							<?php esc_html_e( 'Weeks', 'wordpoints-top-users-in-period' ); ?>
						</option>
						<option value="months"<?php selected( 'months', $this->instance['units'] ); ?>>
							<?php esc_html_e( 'Months', 'wordpoints-top-users-in-period' ); ?>
						</option>
					</select>
				</label>
				<label>
					<span class="screen-reader-text">
						<?php esc_html_e( 'Number of units', 'wordpoints-top-users-in-period' ); ?>
					</span>
					<input
						name="<?php echo esc_attr( $this->get_field_name( 'length_in_units' ) ); ?>"
						type="number"
						value="<?php echo esc_attr( $this->instance['length_in_units'] ); ?>"
						min="1"
						class="widefat wordpoints-top-users-in-period-length-in-units"
					/>
				</label>
				<p>
					<label>
						<input
							name="<?php echo esc_attr( $this->get_field_name( 'relative' ) ); ?>"
							type="radio"
							value="present"
							<?php checked( $this->instance['relative'], 'present' ); ?>
							class="widefat"
						/>
						<?php esc_html_e( 'Count back from the present (for example, one week would mean during the last seven days).', 'wordpoints-top-users-in-period' ); ?>
					</label>
				</p>
				<p>
					<label>
						<input
							name="<?php echo esc_attr( $this->get_field_name( 'relative' ) ); ?>"
							type="radio"
							value="calendar"
							<?php checked( $this->instance['relative'], 'calendar' ); ?>
							class="widefat"
						/>
						<?php esc_html_e( 'Calculate relative to the calendar (for example, one week would mean during the current calendar week).', 'wordpoints-top-users-in-period' ); ?>
					</label>
				</p>
			</fieldset>
		</div>

		<?php

		return true;
	}
}

// EOF
