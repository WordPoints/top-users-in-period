<?php

/**
 * Main file of the extension.
 *
 * ---------------------------------------------------------------------------------|
 * Copyright 2017  J.D. Grimes  (email : jdg@codesymphony.co)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or later, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * ---------------------------------------------------------------------------------|
 *
 * @package WordPoints_Top_Users_In_Period
 * @version 1.0.2
 * @author  J.D. Grimes <jdg@codesymphony.co>
 * @license GPLv2+
 */

wordpoints_register_extension(
	'
		Extension Name: Top Users In Period
		Author:         J.D. Grimes
		Author URI:     https://wordpoints.org/
		Extension URI:  https://wordpoints.org/extensions/top-users-in-period/
		Version:        1.0.2
		License:        GPLv2+
		Description:    Display the top points earners within a given period of time.
		Text Domain:    wordpoints-top-users-in-period
		Domain Path:    /languages
		Server:         wordpoints.org
		ID:             1058
		Namespace:      Top_Users_In_Period
	'
	, __FILE__
);

if ( ! wordpoints_component_is_active( 'points' ) ) {
	return;
}

WordPoints_Class_Autoloader::register_dir( dirname( __FILE__ ) . '/classes/' );

/**
 * Contains the extension's main functions.
 *
 * @since 1.0.0
 */
require_once dirname( __FILE__ ) . '/includes/functions.php';

/**
 * Hooks up the actions and filters for the extension.
 *
 * @since 1.0.0
 */
require_once dirname( __FILE__ ) . '/includes/actions.php';

if ( is_admin() ) {
	/**
	 * Admin-side functions.
	 *
	 * @since 1.0.0
	 */
	require_once dirname( __FILE__ ) . '/includes/admin/functions.php';

	/**
	 * Admin-side actions and filters.
	 *
	 * @since 1.0.0
	 */
	require_once dirname( __FILE__ ) . '/includes/admin/actions.php';
}

// EOF
