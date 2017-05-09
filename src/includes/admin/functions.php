<?php

/**
 * Admin-side functions.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @WordPress\action admin_menu
 * @WordPress\action network_admin_menu Only when the module is network-active.
 */
function wordpoints_top_users_in_period_admin_menu() {

	$wordpoints_menu = wordpoints_get_main_admin_menu();

	/** @var WordPoints_Admin_Screens $admin_screens */
	$admin_screens = wordpoints_apps()
		->get_sub_app( 'admin' )
		->get_sub_app( 'screen' );

	// Hooks page.
	$id = add_submenu_page(
		$wordpoints_menu
		, __( 'WordPoints — Top Users', 'wordpoints-top-users-in-period' )
		, __( 'Top Users', 'wordpoints-top-users-in-period' )
		, 'manage_options'
		, 'wordpoints_top_users_in_period'
		, array( $admin_screens, 'display' )
	);

	if ( $id ) {
		$admin_screens->register( $id, 'WordPoints_Top_Users_In_Period_Admin_Screen' );
	}
}

// EOF
