<?php

/**
 * Admin-side action and filter hooks of the module.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

add_action( 'admin_menu', 'wordpoints_top_users_in_period_admin_menu' );

if ( is_wordpoints_module_active_for_network( __FILE__ ) ) {
	add_action( 'network_admin_menu', 'wordpoints_top_users_in_period_admin_menu' );
}

// EOF
