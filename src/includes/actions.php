<?php

/**
 * Hooks up the actions and filters for the extension.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

add_action( 'wordpoints_init_app-extensions', 'wordpoints_top_users_in_period_extensions_app_init' );
add_action( 'wordpoints_init_app-extensions-top_users_in_period', 'wordpoints_top_users_in_period_apps_init' );

// Back-compat.
add_action( 'wordpoints_init_app-modules', 'wordpoints_top_users_in_period_extensions_app_init' );
add_action( 'wordpoints_init_app-modules-top_users_in_period', 'wordpoints_top_users_in_period_apps_init' );

add_action( 'wordpoints_init_app_registry-extensions-top_users_in_period-block_types', 'wordpoints_top_users_in_period_block_types_init' );
add_action( 'wordpoints_init_app_registry-extensions-top_users_in_period-query_caches', 'wordpoints_top_users_in_period_query_caches_init' );

// Back-compat.
add_action( 'wordpoints_init_app_registry-modules-top_users_in_period-block_types', 'wordpoints_top_users_in_period_block_types_init' );
add_action( 'wordpoints_init_app_registry-modules-top_users_in_period-query_caches', 'wordpoints_top_users_in_period_query_caches_init' );

add_action( 'wp_enqueue_scripts', 'wordpoints_top_users_in_period_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_top_users_in_period_register_scripts', 5 );

add_action( 'widgets_init', 'wordpoints_top_users_in_period_register_widgets' );

add_action( 'wordpoints_points_altered', 'wordpoints_top_users_in_period_query_caches_flush_for_log', 10, 4 );

add_action( 'deleted_user', 'wordpoints_top_users_in_period_delete_block_logs_for_user' );

add_action( 'wordpoints_delete_points_type', 'wordpoints_top_users_in_period_delete_blocks_for_points_type' );

WordPoints_Shortcodes::register(
	'wordpoints_top_users_in_period'
	, 'WordPoints_Top_Users_In_Period_Shortcode_Dynamic'
);

WordPoints_Shortcodes::register(
	'wordpoints_top_users_in_fixed_period'
	, 'WordPoints_Top_Users_In_Period_Shortcode_Fixed'
);

// EOF
