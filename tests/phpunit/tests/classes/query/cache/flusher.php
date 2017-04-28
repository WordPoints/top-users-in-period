<?php

/**
 * Test case for WordPoints_Top_Users_In_Period_Query_Cache_Flusher.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests WordPoints_Top_Users_In_Period_Query_Cache_Flusher.
 *
 * @since 1.0.0
 *
 * @covers WordPoints_Top_Users_In_Period_Query_Cache_Flusher
 */
class WordPoints_Top_Users_In_Period_Query_Cache_Flusher_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 1.0.0
	 */
	public function tearDown() {

		parent::tearDown();

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = false;
	}

	/**
	 * Tests flushing the cache.
	 *
	 * @since 1.0.0
	 */
	public function test_flush() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'test'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$cache = array( 'test' );

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = $cache;

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array(), 5 );

		$this->assertNotEmpty( $index->get() );

		$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher();
		$flusher->flush();

		$this->assertFalse(
			WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}

	/**
	 * Tests flushing the cache when the cache type isn't registered.
	 *
	 * @since 1.0.0
	 */
	public function test_flush_cache_type_not_registered() {

		$this->mock_apps();

		$cache = array( 'test' );

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = $cache;

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array(), 5 );

		$this->assertNotEmpty( $index->get() );

		$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher();
		$flusher->flush();

		$this->assertSame(
			$cache
			, WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}

	/**
	 * Tests flushing the cache on multisite also flushes the network cache.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_flush_multisite() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'test'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$cache = array( 'test' );

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = $cache;

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index( true );

		$index->add( 'test', array(), 5 );

		$this->assertNotEmpty( $index->get() );

		$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher();
		$flusher->flush();

		$this->assertFalse(
			WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}
}

// EOF
