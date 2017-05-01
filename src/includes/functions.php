<?php

/**
 * The module's main functions.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Register scripts and styles for the component.
 *
 * @since 1.0.0
 *
 * @WordPress\action wp_enqueue_scripts    5 So they are ready on enqueue (10).
 * @WordPress\action admin_enqueue_scripts 5 So they are ready on enqueue (10).
 */
function wordpoints_top_users_in_period_register_scripts() {

	$assets_url = wordpoints_modules_url( '/assets', dirname( __FILE__ ) );
	$suffix = SCRIPT_DEBUG ? '' : '.min';

	$version = WordPoints_Modules::get_data( __FILE__, 'version' );

	// JS.
	wp_register_script(
		'wordpoints-top-users-in-period-datepicker'
		, "{$assets_url}/js/datepicker{$suffix}.js"
		, array( 'jquery-ui-datepicker' )
		, $version
	);

	// CSS.
	wp_register_style(
		'wordpoints-top-users-in-period-table'
		, "{$assets_url}/css/table{$suffix}.css"
		, null
		, $version
	);

	wp_register_style(
		'wordpoints-top-users-in-period-widget-settings'
		, "{$assets_url}/css/widget-settings{$suffix}.css"
		, null
		, $version
	);

	wp_register_style(
		'wordpoints-top-users-in-period-datepicker'
		, "{$assets_url}/css/jquery-ui-datepicker{$suffix}.css"
		, null
		, $version
	);

	$styles = wp_styles();
	$rtl_styles = array( 'table', 'widget-settings' );

	foreach ( $rtl_styles as $rtl_style ) {

		$rtl_style = "wordpoints-top-users-in-period-{$rtl_style}";

		$styles->add_data( $rtl_style, 'rtl', 'replace' );

		if ( $suffix ) {
			$styles->add_data( $rtl_style, 'suffix', $suffix );
		}
	}
}

/**
 * Register the widgets.
 *
 * @since 1.0.0
 *
 * @action widgets_init
 */
function wordpoints_top_users_in_period_register_widgets() {

	register_widget( 'WordPoints_Top_Users_In_Period_Widget_Dynamic' );
	register_widget( 'WordPoints_Top_Users_In_Period_Widget_Fixed' );
}

/**
 * Creates a `DateTimeZone` object for the site's timezone.
 *
 * This function determines the site timezone as follows:
 *
 * - If the site uses a timezone identifier (i.e., 'timezone_string' option is set),
 *   that is used.
 * - If that's not set, we make up an identifier based on the 'gmt_offset'.
 * - If the GMT offset is 0, or the identifier is invalid, UTC is used.
 *
 * @see https://wordpress.stackexchange.com/a/198453/27757
 * @see https://us.php.net/manual/en/timezones.others.php
 *
 * @return DateTimeZone The site's timezone.
 */
function wordpoints_top_users_in_period_get_site_timezone() {

	$timezone_string = get_option( 'timezone_string' );

	// A direct offset is being used instead of a timezone identifier.
	if ( empty( $timezone_string ) ) {

		$offset = (int) get_option( 'gmt_offset' );

		if ( 0 === $offset ) {

			$timezone_string = 'UTC';

		} else {

			// IANA timezone database that provides PHP's timezone support uses POSIX
			// -style (i.e. reversed) signs.
			$sign = $offset > 0 ? '-' : '+';

			$timezone_string = 'Etc/GMT' . $sign . abs( $offset );
		}
	}

	return new DateTimeZone( $timezone_string );
}

/**
 * Register Top Users In Period module app when the Modules registry is initialized.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_init_app-modules
 *
 * @param WordPoints_App $modules The modules app.
 */
function wordpoints_top_users_in_period_modules_app_init( $modules ) {

	$apps = $modules->sub_apps();

	$apps->register( 'top_users_in_period', 'WordPoints_App' );
}

/**
 * Register sub apps when the Top Users In Period app is initialized.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_init_app-modules-top_users_in_period
 *
 * @param WordPoints_App $app The Top Users In Period app.
 */
function wordpoints_top_users_in_period_apps_init( $app ) {

	$apps = $app->sub_apps();

	$apps->register( 'block_types', 'WordPoints_Class_Registry' );
	$apps->register( 'query_caches', 'WordPoints_Class_Registry' );
}

/**
 * Register block types when the registry is initialized.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_init_app_registry-modules-top_users_in_period-block_types
 *
 * @param WordPoints_Class_RegistryI $block_types The block types registry.
 */
function wordpoints_top_users_in_period_block_types_init( $block_types ) {

	$block_types->register( 'week_in_seconds', 'WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds' );
}

/**
 * Register query caches when the registry is initialized.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_init_app_registry-modules-top_users_in_period-query_caches
 *
 * @param WordPoints_Class_RegistryI $query_caches The query caches registry.
 */
function wordpoints_top_users_in_period_query_caches_init( $query_caches ) {

	$query_caches->register( 'transients', 'WordPoints_Top_Users_In_Period_Query_Cache_Transients' );
}

/**
 * Flushes the query caches in reference to a particular points log.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_points_altered Only really needed when the points are
 *                   actually logged, but we can't hook into that action due to a
 *                   bug: https://github.com/WordPoints/wordpoints/issues/651
 */
function wordpoints_top_users_in_period_query_caches_flush_for_log(
	$user_id,
	$points,
	$points_type,
	$log_type
) {

	$args = array(
		'user_id'     => $user_id,
		'points_type' => $points_type,
		'log_type'    => $log_type,
	);

	if ( is_multisite() ) {
		$args['blog_id'] = get_current_blog_id();
		$args['site_id'] = get_current_network_id();
	}

	$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher( $args );
	$flusher->flush();
}

// EOF
