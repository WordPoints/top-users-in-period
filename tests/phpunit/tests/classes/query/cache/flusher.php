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
	 * Tests flushing the cache when the end date is set.
	 *
	 * @since 1.0.1
	 */
	public function test_flush_end_date_set() {

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

		$index->add( 'test', array(), 5, time() + 10 );

		$this->assertNotEmpty( $index->get() );

		$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher();
		$flusher->flush();

		$this->assertFalse(
			WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}

	/**
	 * Tests flushing the cache when the end date is set.
	 *
	 * @since 1.0.1
	 */
	public function test_flush_end_date_past() {

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

		$index->add( 'test', array(), 5, 7 );

		$this->assertNotEmpty( $index->get() );

		$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher();
		$flusher->flush();

		$this->assertSame(
			$cache
			, WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
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

	/**
	 * Tests flushing the cache when the query does not match the specified args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_non_matching_queries
	 *
	 * @param array $query_args The args for the query.
	 * @param array $args       The args to flush against.
	 */
	public function test_flush_not_matching_query( $query_args, $args ) {

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

		$index->add( 'test', $query_args, 5 );

		$this->assertNotEmpty( $index->get() );

		$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher( $args );
		$flusher->flush();

		$this->assertSame(
			$cache
			, WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}

	/**
	 * Provides sets of query args and flusher args that do not match.
	 *
	 * @since 1.0.0
	 *
	 * @return array[][] The sets of args.
	 */
	public function data_provider_non_matching_queries() {
		return array(
			'points_type_different' => array(
				array( 'points_type' => 'points' ),
				array( 'points_type' => 'other' ),
			),
			'points_type_different_compare_equals' => array(
				array(
					'points_type' => 'points',
					'points_type__compare' => '=',
				),
				array( 'points_type' => 'other' ),
			),
			'points_type_different_in' => array(
				array( 'points_type__in' => array( 'one', 'two', 'points' ) ),
				array( 'points_type' => 'other' ),
			),
			'points_type_same_not_in' => array(
				array( 'points_type__not_in' => array( 'one', 'two', 'points' ) ),
				array( 'points_type' => 'points' ),
			),
			'log_type_different' => array(
				array( 'log_type' => 'test' ),
				array( 'log_type' => 'other' ),
			),
			'log_type_different_compare_equals' => array(
				array(
					'log_type' => 'test',
					'log_type__compare' => '=',
				),
				array( 'log_type' => 'other' ),
			),
			'log_type_different_in' => array(
				array( 'log_type__in' => array( 'one', 'two', 'test' ) ),
				array( 'log_type' => 'other' ),
			),
			'log_type_same_not_in' => array(
				array( 'log_type__not_in' => array( 'one', 'two', 'test' ) ),
				array( 'log_type' => 'test' ),
			),
			'user_id_different' => array(
				array( 'user_id' => 1 ),
				array( 'user_id' => 2 ),
			),
			'user_id_different_compare_equals' => array(
				array(
					'user_id' => 1,
					'user_id__compare' => '=',
				),
				array( 'user_id' => 2 ),
			),
			'user_id_different_in' => array(
				array( 'user_id__in' => array( 3, 4, 1 ) ),
				array( 'user_id' => 2 ),
			),
			'user_id_same_not_in' => array(
				array( 'user_id__not_in' => array( 3, 4, 1 ) ),
				array( 'user_id' => 1 ),
			),
			'blog_id_different' => array(
				array( 'blog_id' => 1 ),
				array( 'blog_id' => 2 ),
			),
			'blog_id_different_compare_equals' => array(
				array(
					'blog_id' => 1,
					'blog_id__compare' => '=',
				),
				array( 'blog_id' => 2 ),
			),
			'blog_id_different_in' => array(
				array( 'blog_id__in' => array( 3, 4, 1 ) ),
				array( 'blog_id' => 2 ),
			),
			'blog_id_same_not_in' => array(
				array( 'blog_id__not_in' => array( 3, 4, 1 ) ),
				array( 'blog_id' => 1 ),
			),
			'site_id_different' => array(
				array( 'site_id' => 1 ),
				array( 'site_id' => 2 ),
			),
			'site_id_different_compare_equals' => array(
				array(
					'site_id' => 1,
					'site_id__compare' => '=',
				),
				array( 'site_id' => 2 ),
			),
			'site_id_different_in' => array(
				array( 'site_id__in' => array( 3, 4, 1 ) ),
				array( 'site_id' => 2 ),
			),
			'site_id_same_not_in' => array(
				array( 'site_id__not_in' => array( 3, 4, 1 ) ),
				array( 'site_id' => 1 ),
			),
		);
	}

	/**
	 * Tests flushing the cache when the query matches the specified args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_matching_queries
	 *
	 * @param array $query_args The args for the query.
	 * @param array $args       The args to flush against.
	 */
	public function test_flush_matching_query( $query_args, $args ) {

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

		$index->add( 'test', $query_args, 5 );

		$this->assertNotEmpty( $index->get() );

		$flusher = new WordPoints_Top_Users_In_Period_Query_Cache_Flusher( $args );
		$flusher->flush();

		$this->assertFalse(
			WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}

	/**
	 * Provides sets of query args and flusher args that match.
	 *
	 * @since 1.0.0
	 *
	 * @return array[][] The sets of args.
	 */
	public function data_provider_matching_queries() {
		return array(
			'points_type_not_specified' => array(
				array(),
				array( 'points_type' => 'points' ),
			),
			'points_type_same' => array(
				array( 'points_type' => 'points' ),
				array( 'points_type' => 'points' ),
			),
			'points_type_different_compare_not_equals' => array(
				array(
					'points_type' => 'points',
					'points_type__compare' => '!=',
				),
				array( 'points_type' => 'other' ),
			),
			'points_type_same_compare_not_equals' => array(
				array(
					'points_type' => 'points',
					'points_type__compare' => '!=',
				),
				array( 'points_type' => 'points' ),
			),
			'points_type_same_compare_equals' => array(
				array(
					'points_type' => 'points',
					'points_type__compare' => '=',
				),
				array( 'points_type' => 'points' ),
			),
			'points_type_same_in' => array(
				array( 'points_type__in' => array( 'one', 'two', 'points' ) ),
				array( 'points_type' => 'points' ),
			),
			'points_type_different_not_in' => array(
				array( 'points_type__not_in' => array( 'one', 'two', 'points' ) ),
				array( 'points_type' => 'other' ),
			),
			'log_type_not_specified' => array(
				array(),
				array( 'log_type' => 'test' ),
			),
			'log_type_same' => array(
				array( 'log_type' => 'test' ),
				array( 'log_type' => 'test' ),
			),
			'log_type_different_compare_not_equals' => array(
				array(
					'log_type' => 'test',
					'log_type__compare' => '!=',
				),
				array( 'log_type' => 'other' ),
			),
			'log_type_same_compare_not_equals' => array(
				array(
					'log_type' => 'test',
					'log_type__compare' => '!=',
				),
				array( 'log_type' => 'test' ),
			),
			'log_type_same_compare_equals' => array(
				array(
					'log_type' => 'test',
					'log_type__compare' => '=',
				),
				array( 'log_type' => 'test' ),
			),
			'log_type_same_in' => array(
				array( 'log_type__in' => array( 'one', 'two', 'test' ) ),
				array( 'log_type' => 'test' ),
			),
			'log_type_different_not_in' => array(
				array( 'log_type__not_in' => array( 'one', 'two', 'test' ) ),
				array( 'log_type' => 'other' ),
			),
			'user_id_not_specified' => array(
				array(),
				array( 'user_id' => 1 ),
			),
			'user_id_same' => array(
				array( 'user_id' => 1 ),
				array( 'user_id' => 1 ),
			),
			'user_id_different_compare_not_equals' => array(
				array(
					'user_id' => 1,
					'user_id__compare' => '!=',
				),
				array( 'user_id' => 2 ),
			),
			'user_id_same_compare_not_equals' => array(
				array(
					'user_id' => 1,
					'user_id__compare' => '!=',
				),
				array( 'user_id' => 1 ),
			),
			'user_id_same_compare_equals' => array(
				array(
					'user_id' => 1,
					'user_id__compare' => '=',
				),
				array( 'user_id' => 1 ),
			),
			'user_id_same_in' => array(
				array( 'user_id__in' => array( 3, 4, 1 ) ),
				array( 'user_id' => 1 ),
			),
			'user_id_different_not_in' => array(
				array( 'user_id__not_in' => array( 3, 4, 1 ) ),
				array( 'user_id' => 2 ),
			),
			'blog_id_not_specified' => array(
				array(),
				array( 'blog_id' => 1 ),
			),
			'blog_id_same' => array(
				array( 'blog_id' => 1 ),
				array( 'blog_id' => 1 ),
			),
			'blog_id_different_compare_not_equals' => array(
				array(
					'blog_id' => 1,
					'blog_id__compare' => '!=',
				),
				array( 'blog_id' => 2 ),
			),
			'blog_id_same_compare_not_equals' => array(
				array(
					'blog_id' => 1,
					'blog_id__compare' => '!=',
				),
				array( 'blog_id' => 1 ),
			),
			'blog_id_same_compare_equals' => array(
				array(
					'blog_id' => 1,
					'blog_id__compare' => '=',
				),
				array( 'blog_id' => 1 ),
			),
			'blog_id_same_in' => array(
				array( 'blog_id__in' => array( 3, 4, 1 ) ),
				array( 'blog_id' => 1 ),
			),
			'blog_id_different_not_in' => array(
				array( 'blog_id__not_in' => array( 3, 4, 1 ) ),
				array( 'blog_id' => 2 ),
			),
			'site_id_not_specified' => array(
				array(),
				array( 'site_id' => 1 ),
			),
			'site_id_same' => array(
				array( 'site_id' => 1 ),
				array( 'site_id' => 1 ),
			),
			'site_id_different_compare_not_equals' => array(
				array(
					'site_id' => 1,
					'site_id__compare' => '!=',
				),
				array( 'site_id' => 2 ),
			),
			'site_id_same_compare_not_equals' => array(
				array(
					'site_id' => 1,
					'site_id__compare' => '!=',
				),
				array( 'site_id' => 1 ),
			),
			'site_id_same_compare_equals' => array(
				array(
					'site_id' => 1,
					'site_id__compare' => '=',
				),
				array( 'site_id' => 1 ),
			),
			'site_id_same_in' => array(
				array( 'site_id__in' => array( 3, 4, 1 ) ),
				array( 'site_id' => 1 ),
			),
			'site_id_different_not_in' => array(
				array( 'site_id__not_in' => array( 3, 4, 1 ) ),
				array( 'site_id' => 2 ),
			),
		);
	}
}

// EOF
