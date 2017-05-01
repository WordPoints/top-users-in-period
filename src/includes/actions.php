<?php

/**
 * Hooks up the actions and filters for the module.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

add_action( 'wordpoints_init_app-modules', 'wordpoints_top_users_in_period_modules_app_init' );
add_action( 'wordpoints_init_app-modules-top_users_in_period', 'wordpoints_top_users_in_period_apps_init' );

add_action( 'wordpoints_init_app_registry-modules-top_users_in_period-block_types', 'wordpoints_top_users_in_period_block_types_init' );
add_action( 'wordpoints_init_app_registry-modules-top_users_in_period-query_caches', 'wordpoints_top_users_in_period_query_caches_init' );

add_action( 'wp_enqueue_scripts', 'wordpoints_top_users_in_period_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_top_users_in_period_register_scripts', 5 );

add_action( 'widgets_init', 'wordpoints_top_users_in_period_register_widgets' );

add_action( 'wordpoints_points_altered', 'wordpoints_top_users_in_period_query_caches_flush_for_log', 10, 4 );

// EOF
