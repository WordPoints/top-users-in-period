<?php

/**
 * The extension's main functions.
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

	$assets_url = wordpoints_extensions_url( '/assets', dirname( __FILE__ ) );
	$suffix     = SCRIPT_DEBUG ? '' : '.min';
	$version    = wordpoints_get_extension_version( __FILE__ );

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

	$styles     = wp_styles();
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

	// A direct offset may be used instead of a timezone identifier.
	if ( empty( $timezone_string ) ) {

		$offset = get_option( 'gmt_offset' );

		if ( empty( $offset ) ) {

			$timezone_string = 'UTC';

		} else {

			$hours           = (int) $offset;
			$minutes         = ( $offset - floor( $offset ) ) * 60;
			$timezone_string = sprintf( '%+03d:%02d', $hours, $minutes );
		}
	}

	// The offsets in particular do not work prior to PHP 5.5.
	try {
		$timezone = new DateTimeZone( $timezone_string );
	} catch ( Exception $e ) {
		$timezone = new DateTimeZone( 'UTC' );
	}

	return $timezone;
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
function wordpoints_top_users_in_period_validate_datetime( $datetime, $timezone ) {

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
 * Register Top Users In Period extension app when the Extensions registry is initialized.
 *
 * @since 1.0.0 As wordpoints_top_users_in_period_modules_app_init().
 * @since 1.0.2
 *
 * @WordPress\action wordpoints_init_app-extensions
 * @WordPress\action wordpoints_init_app-modules For back-compat.
 *
 * @param WordPoints_App $extensions The extensions app.
 */
function wordpoints_top_users_in_period_extensions_app_init( $extensions ) {

	$apps = $extensions->sub_apps();

	$apps->register( 'top_users_in_period', 'WordPoints_App' );
}

/**
 * Register Top Users In Period extension app when the Extensions registry is initialized.
 *
 * @since 1.0.0
 * @deprecated 1.0.2
 *
 * @param WordPoints_App $modules The extensions app.
 */
function wordpoints_top_users_in_period_modules_app_init( $modules ) {

	_deprecated_function(
		__FUNCTION__
		, '1.0.2'
		, 'wordpoints_top_users_in_period_extensions_app_init'
	);

	wordpoints_top_users_in_period_extensions_app_init( $modules );
}

/**
 * Register sub apps when the Top Users In Period app is initialized.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_init_app-extensions-top_users_in_period
 * @WordPress\action wordpoints_init_app-modules-top_users_in_period For back-compat.
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
 * @WordPress\action wordpoints_init_app_registry-extensions-top_users_in_period-block_types
 * @WordPress\action wordpoints_init_app_registry-modules-top_users_in_period-block_types
 *                   For back-compat.
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
 * @WordPress\action wordpoints_init_app_registry-extensions-top_users_in_period-query_caches
 * @WordPress\action wordpoints_init_app_registry-modules-top_users_in_period-query_caches
 *                   For back-compat.
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

/**
 * Deletes the block logs for a user and flushes the appropriate caches.
 *
 * @since 1.0.1
 *
 * @WordPress\action deleted_user
 *
 * @param int $user_id The ID of the user.
 */
function wordpoints_top_users_in_period_delete_block_logs_for_user( $user_id ) {

	global $wpdb;

	$wpdb->delete(
		$wpdb->base_prefix . 'wordpoints_top_users_in_period_block_logs'
		, array( 'user_id' => $user_id )
		, '%d'
	); // WPCS: cache OK.

	$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher(
		array( 'user_id' => $user_id )
	);

	$flusher->flush( true, true );
}

/**
 * Deletes all blocks, block logs, and query signatures relating to a points type.
 *
 * @since 1.0.1
 *
 * @param string $points_type The points type slug.
 */
function wordpoints_top_users_in_period_delete_blocks_for_points_type( $points_type ) {

	$query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query();

	foreach ( $query->get() as $signature ) {

		$args = json_decode( $signature->query_args, true );

		// If this query is for a specific points type other than this one, skip it.
		if (
			isset( $args['points_type'] )
			&& $points_type !== $args['points_type']
			&& (
				! isset( $args['points_type__compare'] )
				|| '=' === $args['points_type__compare']
			)
		) {
			continue;
		}

		// Or if it is for a particular set of types, but not this one, skip it.
		if (
			isset( $args['points_type__in'] )
			&& ! in_array( $points_type, $args['points_type__in'], true )
		) {
			continue;
		}

		// We could check not_in too, but any query that specified the points type in
		// not_in is unlikely to be run again. Same for !=, etc.

		wordpoints_top_users_in_period_delete_query_signature( $signature->id );
	}

	$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher(
		array( 'points_type' => $points_type )
	);

	$flusher->flush( true );
}

/**
 * Deletes a query signature and all blocks and block logs relating to it.
 *
 * Note that it does not flush the caches, that is currently expected to be done
 * outside of the function as necessary.
 *
 * @since 1.0.1
 *
 * @param int $id The ID of the query signature to delete.
 */
function wordpoints_top_users_in_period_delete_query_signature( $id ) {

	// Delete the blocks.
	$blocks_query = new WordPoints_Top_Users_In_Period_Blocks_Query(
		array( 'fields' => 'id', 'query_signature_id' => $id )
	);

	foreach ( $blocks_query->get( 'col' ) as $block_id ) {
		wordpoints_top_users_in_period_delete_block( $block_id );
	}

	// Delete the query signature itself.
	global $wpdb;

	$wpdb->delete(
		$wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures'
		, array( 'id' => $id )
		, '%d'
	); // WPCS: cache OK.
}

/**
 * Deletes a block and all block logs relating to it.
 *
 * Note that it does not flush any affected caches, you are expected to take care of
 * that yourself depending on the situation.
 *
 * @since 1.0.1
 *
 * @param int $id The ID of the block to delete.
 */
function wordpoints_top_users_in_period_delete_block( $id ) {

	global $wpdb;

	// Delete the logs.
	$wpdb->delete(
		$wpdb->base_prefix . 'wordpoints_top_users_in_period_block_logs'
		, array( 'block_id' => $id )
		, '%d'
	); // WPCS: cache OK.

	// Delete the block itself.
	$wpdb->delete(
		$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
		, array( 'id' => $id )
		, '%d'
	); // WPCS: cache OK.
}

// EOF
