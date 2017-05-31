<?php

/**
 * Test case for the update to 1.0.1.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.1
 */

/**
 * Tests updating to 1.0.1.
 *
 * @since 1.0.1
 *
 * @covers WordPoints_Top_Users_In_Period_Un_Installer::update_single_to_1_0_1()
 * @covers WordPoints_Top_Users_In_Period_Un_Installer::update_site_to_1_0_1()
 * @covers WordPoints_Top_Users_In_Period_Un_Installer::update_network_to_1_0_1()
 * @covers WordPoints_Top_Users_In_Period_Un_Installer::update_cache_index_to_1_0_1()
 */
class WordPoints_Top_Users_In_Period_Update_1_0_1_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 1.0.1
	 */
	protected $previous_version = '1.0.0';

	/**
	 * @since 1.0.1
	 */
	protected $wordpoints_module = 'top-users-in-period';

	/**
	 * @since 1.0.1
	 */
	protected $backup_globals = array( '_wp_using_ext_object_cache' );

	/**
	 * Tests that the cache index is updated.
	 *
	 * @since 1.0.1
	 */
	public function test_cache_index_updated() {

		update_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => true ),
					),
				),
			)
		);

		$this->update_module();

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$this->assertSame(
			array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => array( 'none' => true ) ),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests that the cache index is updated.
	 *
	 * @since 1.0.1
	 *
	 * @requires WordPoints network-active
	 */
	public function test_cache_index_updated_network() {

		update_site_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => true ),
					),
				),
			)
		);

		$this->update_module();

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index( true );

		$this->assertSame(
			array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => array( 'none' => true ) ),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests that the caches are flushed.
	 *
	 * @since 1.0.1
	 */
	public function test_caches_flushed() {

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients( 'test' );
		$cache->set( array( 'test' ) );

		$this->update_module();

		$this->assertFalse( $cache->get() );
	}

	/**
	 * Tests that the caches are flushed.
	 *
	 * @since 1.0.1
	 */
	public function test_caches_flushed_external_object_cache() {

		wp_using_ext_object_cache( true );

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients( 'test' );
		$cache->set( array( 'test' ) );

		$this->update_module();

		$this->assertFalse( $cache->get() );
	}

	/**
	 * Tests that the network caches are flushed.
	 *
	 * @since 1.0.1
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_caches_flushed() {

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
			'test'
			, array()
			, true
		);

		$cache->set( array( 'test' ) );

		$this->update_module();

		$this->assertFalse( $cache->get() );
	}
}

// EOF
