<?php

/**
 * Admin screen class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Displays the extension's admin screen.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Admin_Screen extends WordPoints_Admin_Screen {

	/**
	 * @since 1.0.0
	 */
	protected function get_title() {
		return __( 'Top Users', 'wordpoints-top-users-in-period' );
	}

	/**
	 * @since 1.0.0
	 */
	protected function display_content() {

		wp_enqueue_style( 'wordpoints-top-users-in-period-datepicker' );
		wp_enqueue_script( 'wordpoints-top-users-in-period-datepicker' );

		$args = $this->get_search_args();

		?>

		<form class="wordpoints-top-users-in-period-form">
			<label>
				<span class="screen-reader-text">
					<?php esc_html_e( 'From date (inclusive):', 'wordpoints-top-users-in-period' ); ?>
				</span>
				<span aria-hidden="true">
					<?php esc_html_e( 'From (inclusive):', 'wordpoints-top-users-in-period' ); ?>
				</span>
				<input
					class="wordpoints-top-users-in-period-from"
					name="from"
					type="date"
					placeholder="<?php esc_attr_e( 'Date', 'wordpoints-top-users-in-period' ); ?>"
					value="<?php echo esc_attr( $args['from'] ); ?>"
				>
			</label>
			<label>
				<span class="screen-reader-text">
					<?php esc_html_e( 'From time (inclusive):', 'wordpoints-top-users-in-period' ); ?>
				</span>
				<input
					name="from_time"
					type="time"
					placeholder="<?php esc_attr_e( 'Time', 'wordpoints-top-users-in-period' ); ?>"
					value="<?php echo esc_attr( $args['from_time'] ); ?>"
				>
			</label>
			<label>
				<span class="screen-reader-text">
					<?php esc_html_e( 'To date (inclusive) (default: now):', 'wordpoints-top-users-in-period' ); ?>
				</span>
				<span aria-hidden="true">
					<?php esc_html_e( 'To (inclusive):', 'wordpoints-top-users-in-period' ); ?>
				</span>
				<input
					class="wordpoints-top-users-in-period-to"
					name="to"
					type="date"
					placeholder="<?php esc_attr_e( 'Date (default: now)', 'wordpoints-top-users-in-period' ); ?>"
					value="<?php echo esc_attr( $args['to'] ); ?>"
				>
			</label>
			<label>
				<span class="screen-reader-text">
					<?php esc_html_e( 'To time (inclusive):', 'wordpoints-top-users-in-period' ); ?>
				</span>
				<input
					name="to_time"
					type="time"
					placeholder="<?php esc_attr_e( 'Time', 'wordpoints-top-users-in-period' ); ?>"
					value="<?php echo esc_attr( $args['to_time'] ); ?>"
				>
			</label>
			<label>
				<?php esc_html_e( 'Points Type:', 'wordpoints-top-users-in-period' ); ?>
				<?php

				wordpoints_points_types_dropdown(
					array(
						'name'             => 'points_type',
						'selected'         => $args['points_type'],
						'show_option_none' => _x( 'All', 'points types', 'wordpoints-top-users-in-period' ),
					)
				);

				?>
			</label>
			<input type="hidden" name="page" value="<?php echo ( isset( $_GET['page'] ) ? esc_html( sanitize_key( $_GET['page'] ) ) : '' ); // WPCS: CSRF OK ?>" />
			<?php wp_nonce_field( 'wordpoints_top_users_in_period_admin_query', 'nonce', false ); ?>
			<?php submit_button( __( 'Show Users', 'wordpoints-top-users-in-period' ), 'primary', 'submit', false ); ?>
		</form>

		<?php

		$this->display_query_results( $args );
	}

	/**
	 * Displays the results of the query submitted by the user.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The user-submitted data.
	 */
	protected function display_query_results( $data ) {

		if ( ! wordpoints_verify_nonce( 'nonce', 'wordpoints_top_users_in_period_admin_query' ) ) {
			return;
		}

		if ( empty( $data['from'] ) ) {
			return;
		}

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$from = new DateTime( "{$data['from']} {$data['from_time']}:00", $timezone );

		$to = null;

		if ( ! empty( $data['to'] ) ) {
			$to = new DateTime( "{$data['to']} {$data['to_time']}:59", $timezone );
		}

		$args = array();

		if ( ! empty( $data['points_type'] ) ) {
			$args['points_type'] = $data['points_type'];
		}

		$query = new WordPoints_Top_Users_In_Period_Query( $from, $to, $args );

		$table = new WordPoints_Top_Users_In_Period_Table( $query, 'admin_screen' );
		$table->display();
	}

	/**
	 * Gets the search args submitted by the user.
	 *
	 * @since 1.0.0
	 *
	 * @return array The search args submitted by the user.
	 */
	protected function get_search_args() {

		$args = array(
			'from'        => '',
			'from_time'   => '00:00',
			'to'          => '',
			'to_time'     => '23:59',
			'points_type' => '-1',
		);

		if ( ! empty( $_GET['from'] ) ) { // WPCS: CSRF OK.
			$args['from'] = sanitize_text_field( wp_unslash( $_GET['from'] ) ); // WPCS: CSRF OK.
		}

		if ( ! empty( $_GET['from_time'] ) ) { // WPCS: CSRF OK.
			$args['from_time'] = sanitize_text_field( wp_unslash( $_GET['from_time'] ) ); // WPCS: CSRF OK.
		}

		if ( ! empty( $_GET['to'] ) ) { // WPCS: CSRF OK.
			$args['to'] = sanitize_text_field( wp_unslash( $_GET['to'] ) ); // WPCS: CSRF OK.
		}

		if ( ! empty( $_GET['to_time'] ) ) { // WPCS: CSRF OK.
			$args['to_time'] = sanitize_text_field( wp_unslash( $_GET['to_time'] ) ); // WPCS: CSRF OK.
		}

		if ( ! empty( $_GET['points_type'] ) && '-1' !== $_GET['points_type'] ) { // WPCS: CSRF OK.
			$args['points_type'] = sanitize_key( $_GET['points_type'] ); // WPCS: CSRF OK.
		}

		return $args;
	}
}

// EOF
