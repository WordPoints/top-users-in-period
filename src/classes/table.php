<?php

/**
 * Table class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Displays the results of a top users in period query in a table.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Table {

	/**
	 * The query being displayed.
	 *
	 * @since 1.0.0
	 *
	 * @var WordPoints_Top_Users_In_Period_Query
	 */
	protected $query;

	/**
	 * The context in which the table will be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * The points type this query is for.
	 *
	 * If the query isn't for a particular points type, then this is null.
	 *
	 * @since 1.0.0
	 *
	 * @var string|null
	 */
	protected $points_type;

	/**
	 * The titles for the table headers.
	 *
	 * @since 1.0.0
	 *
	 * @var string[]
	 */
	protected $column_headers;

	/**
	 * The size of the user avatars used in the table, in pixels.
	 *
	 * @since 1.0.2
	 *
	 * @var int
	 */
	protected $avatar_size = 32;

	/**
	 * @since 1.0.0
	 *
	 * @param WordPoints_Top_Users_In_Period_Query $query   The query to display.
	 * @param string                               $context The context.
	 */
	public function __construct(
		WordPoints_Top_Users_In_Period_Query $query,
		$context
	) {

		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Displays the table.
	 *
	 * @since 1.0.0
	 */
	public function display() {

		wp_enqueue_style( 'wordpoints-top-users-in-period-table' );

		$top_users = $this->query->get();

		if ( is_wp_error( $top_users ) ) {
			$this->display_message( __( 'There was an error running the query, please try again in a few moments.', 'wordpoints-top-users-in-period' ), 'error' );
			return;
		}

		if ( empty( $top_users ) ) {
			$this->display_message( __( 'No users received points during this period.', 'wordpoints-top-users-in-period' ), 'info' );
			return;
		}

		/**
		 * Filters the size of the user avatars displayed in the top users in period table.
		 *
		 * @since 1.0.2
		 *
		 * @param int $avatar_size The size of the avatars, in pixels.
		 */
		$this->avatar_size = apply_filters( 'wordpoints_top_users_in_period_table_avatar_size', $this->avatar_size );

		$this->points_type    = $this->get_points_type();
		$this->column_headers = $this->get_column_headers();

		$extra_classes = $this->get_extra_classes( $top_users );

		?>

		<table class="wordpoints-top-users-in-period <?php echo esc_attr( implode( ' ', $extra_classes ) ); ?>">
			<thead>
				<?php $this->display_headers(); ?>
			</thead>
			<tbody>
			<?php

			$position = 1;

			foreach ( $top_users as $data ) {

				$user_id = $data->user_id;
				$points  = $data->total;

				$user = get_userdata( $user_id );

				?>

				<tr>
					<td>
						<?php echo esc_html( number_format_i18n( $position ) ); ?>
					</td>
					<td>
						<?php echo get_avatar( $user_id, $this->avatar_size ); ?>
						<?php

						$name = sanitize_user_field(
							'display_name'
							, $user->display_name
							, $user_id
							, 'display'
						);

						/**
						 * Filters a user's name in the top users in period table.
						 *
						 * @since 1.0.0
						 *
						 * @param string                               $name        The name of the user.
						 * @param int                                  $user_id     The user ID.
						 * @param string|null                          $points_type The points type the query is for.
						 * @param WordPoints_Top_Users_In_Period_Query $query       The query.
						 * @param string                               $context     The context in which the table is being displayed.
						 */
						$name = apply_filters(
							'wordpoints_top_users_in_period_table_username'
							, $name
							, $user_id
							, $this->points_type
							, $this->query
							, $this->context
						);

						echo wp_kses(
							$name
							, 'wordpoints_top_users_in_period_table_username'
						);

						?>
					</td>
					<td>
						<?php

						if ( $this->points_type ) {

							echo wordpoints_format_points(
								$points
								, $this->points_type
								, "top_users_in_period_table_{$this->context}"
							);

						} else {

							echo (int) $points;
						}

						?>
					</td>
				</tr>

				<?php

				$position++;

			} // End foreach ( $top_users ).

			?>
			</tbody>
			<tfoot>
				<?php $this->display_headers(); ?>
			</tfoot>
		</table>

		<?php
	}

	/**
	 * Displays a message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The message.
	 * @param string $type    The type of message.
	 */
	protected function display_message( $message, $type ) {

		if ( is_admin() ) {
			wordpoints_show_admin_message( $message, $type );
		} else {
			echo esc_html( $message );
		}
	}

	/**
	 * Gets the column headers for the table.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] The column headers for the table.
	 */
	protected function get_column_headers() {

		$column_headers = array(
			'#'        => _x( '#', 'top users table heading', 'wordpoints-top-users-in-period' ),
			'position' => _x( 'Position', 'top users table heading', 'wordpoints-top-users-in-period' ),
			'user'     => _x( 'User', 'top users table heading', 'wordpoints-top-users-in-period' ),
			'points'   => _x( 'Points', 'top users table heading', 'wordpoints-top-users-in-period' ),
		);

		if ( $this->points_type ) {

			$points_type_name = wordpoints_get_points_type_setting(
				$this->points_type
				, 'name'
			);

			if ( ! empty( $points_type_name ) ) {
				$column_headers['points'] = $points_type_name;
			}
		}

		return $column_headers;
	}

	/**
	 * Displays the column headers for a table.
	 *
	 * @since 1.0.0
	 */
	protected function display_headers() {

		?>

		<tr>
			<th scope="col">
				<span aria-hidden="true"><?php echo esc_html( $this->column_headers['#'] ); ?></span>
				<span class="screen-reader-text"><?php echo esc_html( $this->column_headers['position'] ); ?></span>
			</th>
			<th scope="col"><?php echo esc_html( $this->column_headers['user'] ); ?></th>
			<th scope="col"><?php echo esc_html( $this->column_headers['points'] ); ?></th>
		</tr>

		<?php
	}

	/**
	 * Gets the list of extra classes to give the table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $results The query results.
	 *
	 * @return string[] The extra classes.
	 */
	protected function get_extra_classes( $results ) {

		$extra_classes = array();

		if ( is_admin() ) {
			$extra_classes[] = 'widefat';
			$extra_classes[] = 'striped';
		}

		/**
		 * Filter the extra HTML classes for the top in period users table element.
		 *
		 * @since 1.0.0
		 *
		 * @param string[]                             $extra_classes The extra classes for the table element.
		 * @param WordPoints_Top_Users_In_Period_Query $query         The query being displayed.
		 * @param array                                $results       The query results.
		 */
		$extra_classes = apply_filters(
			'wordpoints_top_users_in_period_table_extra_classes'
			, $extra_classes
			, $this->query
			, $results
		);

		return $extra_classes;
	}

	/**
	 * Gets the points type this query is for.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null The points type, or null if not for a particular type.
	 */
	protected function get_points_type() {

		$points_type = $this->query->get_arg( 'points_type' );

		if ( $this->query->get_arg( 'points_type__compare' ) ) {
			$points_type = null;
		}

		return $points_type;
	}
}

// EOF
