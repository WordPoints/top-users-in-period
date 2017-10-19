<?php

/**
 * Uninstall test case.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests uninstalling the module.
 *
 * @since 1.0.0
 *
 * @covers WordPoints_Top_Users_In_Period_Un_Installer
 */
class Top_Users_In_Period_Uninstall_Test
	extends WordPoints_PHPUnit_TestCase_Extension_Uninstall {

	/**
	 * Test installation and uninstallation.
	 *
	 * @since 1.0.0
	 */
	public function test_uninstall() {

		global $wpdb;

		/*
		 * First test that the module installed itself properly.
		 */

		$this->assertTableExists( $wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks' );
		$this->assertTableExists( $wpdb->base_prefix . 'wordpoints_top_users_in_period_block_logs' );

		/*
		 * Now, test that it uninstalls itself properly.
		 */

		$this->uninstall();

		// Check that everything with this module's prefix has been uninstalled.
		$this->assertUninstalledPrefix( 'wordpoints_top_users_in_period' );
	}
}

// EOF
