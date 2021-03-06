<?php

/**
 * Test case for the apps functions.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests the apps functions.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Apps_Functions_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests the extensions app init function.
	 *
	 * @since 1.0.2
	 *
	 * @covers ::wordpoints_top_users_in_period_extensions_app_init
	 */
	public function test_extensions_app_init() {

		$this->mock_apps();

		$app = new WordPoints_App( 'test' );

		wordpoints_top_users_in_period_extensions_app_init( $app );

		$this->assertTrue(
			$app->sub_apps()->is_registered( 'top_users_in_period' )
		);
	}

	/**
	 * Tests the modules app init function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_top_users_in_period_modules_app_init
	 *
	 * @expectedDeprecated wordpoints_top_users_in_period_modules_app_init
	 */
	public function test_modules_app_init() {

		$this->mock_apps();

		$app = new WordPoints_App( 'test' );

		wordpoints_top_users_in_period_modules_app_init( $app );

		$this->assertTrue(
			$app->sub_apps()->is_registered( 'top_users_in_period' )
		);
	}

	/**
	 * Tests the extension's apps init function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_top_users_in_period_apps_init
	 */
	public function test_apps_init() {

		$this->mock_apps();

		$app = new WordPoints_App( 'test' );

		wordpoints_top_users_in_period_apps_init( $app );

		$sub_apps = $app->sub_apps();

		$this->assertTrue( $sub_apps->is_registered( 'block_types' ) );
		$this->assertTrue( $sub_apps->is_registered( 'query_caches' ) );
	}

	/**
	 * Tests the block types registry init function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_top_users_in_period_block_types_init
	 */
	public function test_block_types_init() {

		$this->mock_apps();

		$registry = new WordPoints_Class_Registry();

		wordpoints_top_users_in_period_block_types_init( $registry );

		$this->assertTrue( $registry->is_registered( 'week_in_seconds' ) );
	}

	/**
	 * Tests the query caches registry init function.
	 *
	 * @since 1.0.0
	 *
	 * @covers ::wordpoints_top_users_in_period_query_caches_init
	 */
	public function test_query_caches_init() {

		$this->mock_apps();

		$registry = new WordPoints_Class_Registry();

		wordpoints_top_users_in_period_query_caches_init( $registry );

		$this->assertTrue( $registry->is_registered( 'transients' ) );
	}
}

// EOF
