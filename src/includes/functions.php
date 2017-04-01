<?php

/**
 * The module's main functions.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

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

// EOF
