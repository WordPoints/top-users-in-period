<?php

/**
 * Test case for WordPoints_Top_Users_In_Period_Query.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests WordPoints_Top_Users_In_Period_Query.
 *
 * @since 1.0.0
 *
 * @covers WordPoints_Top_Users_In_Period_Query
 */
class WordPoints_Top_Users_In_Period_Query_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * The default blocks signature.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $default_signature = '4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945';

	/**
	 * @since 1.0.0
	 */
	public static function setUpBeforeClass() {

		if ( is_multisite() ) {
			self::$default_signature = 'bb599c1a16d4e4773e6eb9cdce883097003a919322af50aad8f6046902fb5fe5';
		}

		return parent::setUpBeforeClass();
	}

	/**
	 * @since 1.0.0
	 */
	public function tearDown() {

		parent::tearDown();

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = false;
	}

	/**
	 * Tests getting the start date.
	 *
	 * @since 1.0.0
	 */
	public function test_get_start_date() {

		$start_date = new DateTime( '-1 month' );

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date );

		$query_start_date = $query->get_start_date();

		$this->assertNotSame( $start_date, $query_start_date );
		$this->assertSame(
			$start_date->format( 'U' )
			, $query_start_date->format( 'U' )
		);
	}

	/**
	 * Tests getting the end date.
	 *
	 * @since 1.0.0
	 */
	public function test_get_end_date() {

		$start_date = new DateTime( '-2 months' );
		$end_date = new DateTime( '-1 month' );

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date, $end_date );

		$query_end_date = $query->get_end_date();

		$this->assertNotSame( $end_date, $query_end_date );
		$this->assertSame(
			$end_date->format( 'U' )
			, $query_end_date->format( 'U' )
		);
	}

	/**
	 * Tests getting the default end date.
	 *
	 * @since 1.0.0
	 */
	public function test_default_end_date() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-2 months' )
		);

		$this->assertSame(
			current_time( 'timestamp', true )
			, (int) $query->get_end_date()->format( 'U' )
		);
	}

	/**
	 * Tests that an error is returned when the start is after the end.
	 *
	 * @since 1.0.0
	 */
	public function test_start_after_end_date() {

		$start_date = new DateTime( '-1 months' );
		$end_date = new DateTime( '-2 month' );

		$this->listen_for_filter( 'wordpoints_top_user_in_period_query_cache' );

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date, $end_date );

		$this->assertWPError( $query->get() );

		$this->assertSame(
			0
			, $this->filter_was_called( 'wordpoints_top_user_in_period_query_cache' )
		);
	}

	/**
	 * Tests getting the default args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_args_defaults() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSameSetsWithIndex(
			array(
				'order_by'      => 'total',
				'order'         => 'DESC',
				'start'         => 0,
				'text__compare' => 'LIKE',
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests getting the default args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_get_args_defaults_multisite() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSameSetsWithIndex(
			array(
				'start'         => 0,
				'order'         => 'DESC',
				'order_by'      => 'total',
				'text__compare' => 'LIKE',
				'blog_id'       => '1',
				'site_id'       => '1',
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests getting the args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_get_args() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array( 'start' => 10, 'limit' => 10 )
		);

		$this->assertSameSetsWithIndex(
			array(
				'order_by'      => 'total',
				'order'         => 'DESC',
				'text__compare' => 'LIKE',
				'start'         => 10,
				'limit'         => 10,
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests that the args are cleaned, removing unnecessary '*__compare' args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_cleans_args_removes_unused_compare() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array(
				'user_id'          => 1,
				'user_id__compare' => '=', // Kept.
				'text__compare'    => 'NOT EXISTS', // Kept.
				'total__compare'   => '!=', // Removed.
			)
		);

		$query->get();

		$this->assertSameSetsWithIndex(
			array(
				'order_by'         => 'total',
				'order'            => 'DESC',
				'start'            => 0,
				'user_id'          => 1,
				'user_id__compare' => '=',
				'text__compare'    => 'NOT EXISTS',
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests that the args are cleaned, removing unnecessary '*__in' args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_cleans_args_removes_unused_in() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array(
				'user_id'       => 1,
				'user_id__in'   => array( 2, 3, 4 ),
				'total'         => 100,
				'total__not_in' => array( 50, 10 ),
			)
		);

		$query->get();

		$this->assertSameSetsWithIndex(
			array(
				'order_by' => 'total',
				'order'    => 'DESC',
				'start'    => 0,
				'user_id'  => 1,
				'total'    => 100,
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests that the args are cleaned, removing empty '*__in' args.
	 *
	 * @since 1.0.1
	 *
	 * @requires WordPress !multisite
	 */
	public function test_cleans_args_removes_empty_in() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array(
				'user_id__in'   => array(),
				'total__not_in' => array(),
			)
		);

		$query->get();

		$this->assertSameSetsWithIndex(
			array(
				'order_by' => 'total',
				'order'    => 'DESC',
				'start'    => 0,
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests that the args are cleaned, sorting '*__in' and '*__not_in' args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_cleans_args_sorts_in() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array(
				'user_id__in'   => array( 2, 4, 3 ),
				'total__not_in' => array( 50, 10 ),
			)
		);

		$query->get();

		$this->assertSameSetsWithIndex(
			array(
				'order_by'      => 'total',
				'order'         => 'DESC',
				'start'         => 0,
				'user_id__in'   => array( 2, 3, 4 ),
				'total__not_in' => array( 10, 50 ),
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests that the args are cleaned, unique '*__in' and '*__not_in' args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_cleans_args_unique_in() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array(
				'user_id__in'   => array( 2, 4, 3, 2, 3 ),
				'total__not_in' => array( 50, 10, 10 ),
			)
		);

		$query->get();

		$this->assertSameSetsWithIndex(
			array(
				'order_by'      => 'total',
				'order'         => 'DESC',
				'start'         => 0,
				'user_id__in'   => array( 2, 3, 4 ),
				'total__not_in' => array( 10, 50 ),
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests that the args are cleaned, single '*__in' and '*__not_in' args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_cleans_args_once_in() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array(
				'user_id__in'   => array( 2 ),
				'total__not_in' => array( 10, 10 ),
			)
		);

		$query->get();

		$this->assertSameSetsWithIndex(
			array(
				'order_by'       => 'total',
				'order'          => 'DESC',
				'start'          => 0,
				'user_id'        => 2,
				'total'          => 10,
				'total__compare' => '!=',
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests that the args are cleaned.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_cleans_args_type() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array(
				'user_id'       => '5',
				'total__not_in' => array( '10', 10, '5' ),
			)
		);

		$query->get();

		$this->assertSameSetsWithIndex(
			array(
				'order_by'       => 'total',
				'order'          => 'DESC',
				'start'          => 0,
				'user_id'        => 5,
				'total__not_in'  => array( 5, 10 ),
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests that the args are cleaned.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_cleans_args_type_multisite() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, array(
				'site_id'     => '5',
				'blog_id__in' => array( '10', 10, '5' ),
			)
		);

		$query->get();

		$this->assertSameSetsWithIndex(
			array(
				'order_by'    => 'total',
				'order'       => 'DESC',
				'start'       => 0,
				'site_id'     => 5,
				'blog_id__in' => array( 5, 10 ),
			)
			, $query->get_args()
		);
	}

	/**
	 * Tests checking if a query is network scope.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 *
	 * @dataProvider data_provider_network_queries
	 *
	 * @param array $args The query args.
	 */
	public function test_is_network_scope( $args ) {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, $args
		);

		$this->assertTrue( $query->is_network_scope() );
	}

	/**
	 * Data provider for network queries.
	 *
	 * @since 1.0.0
	 *
	 * @return array Network queries.
	 */
	public function data_provider_network_queries() {
		return array(
			'no_blog_id'        => array( array() ),
			'different_blog_id' => array( array( 'blog_id' => 2 ) ),
			'blog_id_not_='     => array(
				array( 'blog_id' => 1, 'blog_id__compare' => '!=' ),
			),
		);
	}

	/**
	 * Tests checking if a query is network scope.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 *
	 * @dataProvider data_provider_per_site_queries
	 *
	 * @param array $args The query args.
	 */
	public function test_is_network_scope_per_site( $args ) {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, $args
		);

		$this->assertFalse( $query->is_network_scope() );
	}

	/**
	 * Data provider for per-site queries.
	 *
	 * @since 1.0.0
	 *
	 * @return array Per-site queries.
	 */
	public function data_provider_per_site_queries() {
		return array(
			'blog_id' => array( array( 'blog_id' => 1 ) ),
			'blog_id_=' => array(
				array( 'blog_id' => 1, 'blog_id__compare' => '=' ),
			),
		);
	}

	/**
	 * Tests checking if a query is network scope.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 *
	 * @dataProvider data_provider_per_site_queries
	 *
	 * @param array $args The query args.
	 */
	public function test_is_network_scope_not_multisite( $args ) {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
			, null
			, $args
		);

		$this->assertFalse( $query->is_network_scope() );
	}

	/**
	 * Tests that the count function is not supported.
	 *
	 * @since 1.0.0
	 *
	 * @expectedIncorrectUsage WordPoints_Top_Users_In_Period_Query::count
	 */
	public function test_count() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertFalse( $query->count() );
	}

	/**
	 * Tests that the method parameter is not supported.
	 *
	 * @since 1.0.0
	 *
	 * @expectedIncorrectUsage WordPoints_Top_Users_In_Period_Query::get
	 */
	public function test_get_method() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSame( array(), $query->get( 'results' ) );
	}

	/**
	 * Tests that the query type parameter is not supported.
	 *
	 * @since 1.0.0
	 *
	 * @expectedIncorrectUsage WordPoints_Top_Users_In_Period_Query::get_sql
	 */
	public function test_get_sql_query_type() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertInternalType( 'string', $query->get_sql( 'SELECT' ) );
	}

	/**
	 * Tests that the cache is checked.
	 *
	 * @since 1.0.0
	 */
	public function test_checks_cache() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'mock'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$cache = array( 'test' );

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = $cache;

		$mock = new WordPoints_PHPUnit_Mock_Filter( 'mock' );
		$mock->add_filter( 'wordpoints_top_user_in_period_query_cache' );

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSame( $cache, $query->get() );

		$this->assertSame( 1, $mock->call_count );
	}

	/**
	 * Tests that the cache is set with the query results.
	 *
	 * @since 1.0.0
	 */
	public function test_checks_cache_open_ended() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'transients'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSame( array(), $query->get() );
		$this->assertSame(
			array()
			, WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}

	/**
	 * Tests that the cache is added to the index.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_adds_to_cache_index() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'mock'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$cache = array( 'test' );

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = $cache;

		$mock = new WordPoints_PHPUnit_Mock_Filter( 'mock' );
		$mock->add_filter( 'wordpoints_top_user_in_period_query_cache' );

		$start_date = new DateTime( '-1 months' );

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date );

		$this->assertSame( $cache, $query->get() );

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$this->assertSame(
			array(
				'3d13afbe3e05f625ab72cc2cb1619af40921a833c545520b31c550d39a90aab4' => array(
					'args'   => array(
						'order'    => 'DESC',
						'order_by' => 'total',
						'start'    => 0,
					),
					'caches' => array(
						'mock' => array(
							$start_date->format( 'U' ) => array(
								'none' => true,
							),
						),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests that the cache is not added to the index when an end date is set.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 */
	public function test_adds_to_cache_index_has_end_date() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'mock'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$cache = array( 'test' );

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = $cache;

		$mock = new WordPoints_PHPUnit_Mock_Filter( 'mock' );
		$mock->add_filter( 'wordpoints_top_user_in_period_query_cache' );

		$start_date = new DateTime( '-1 months' );
		$end_date   = new DateTime();

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date, $end_date );

		$this->assertSame( $cache, $query->get() );

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$this->assertSame(
			array(
				'3d13afbe3e05f625ab72cc2cb1619af40921a833c545520b31c550d39a90aab4' => array(
					'args'   => array(
						'order'    => 'DESC',
						'order_by' => 'total',
						'start'    => 0,
					),
					'caches' => array(
						'mock' => array(
							$start_date->format( 'U' ) => array(
								$end_date->format( 'U' ) => true,
							),
						),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests that the cache is added to the network index for network queries.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_adds_to_cache_index_network() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'mock'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$cache = array( 'test' );

		WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value = $cache;

		$mock = new WordPoints_PHPUnit_Mock_Filter( 'mock' );
		$mock->add_filter( 'wordpoints_top_user_in_period_query_cache' );

		$start_date = new DateTime( '-1 months' );

		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, null
			, array( 'blog_id' => 5 )
		);

		$this->assertSame( $cache, $query->get() );

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$this->assertSame( array(), $index->get() );

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index( true );

		$this->assertSame(
			array(
				'd2b248f5c56d245046870436eb4815aa7d4ab15f87f2af364cb12866b15f6381' => array(
					'args'   => array(
						'blog_id'  => 5,
						'order'    => 'DESC',
						'order_by' => 'total',
						'site_id'  => 1,
						'start'    => 0,
					),
					'caches' => array(
						'mock' => array(
							$start_date->format( 'U' ) => array(
								'none' => true,
							),
						),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests that the cache is not used if it null.
	 *
	 * @since 1.0.0
	 */
	public function test_cache_null() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'transients'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSame( array(), $query->get() );
	}

	/**
	 * Tests that the cache is set with the query results.
	 *
	 * @since 1.0.0
	 */
	public function test_cache_set() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'transients'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSame( array(), $query->get() );
		$this->assertSame(
			array()
			, WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}

	/**
	 * Tests that the cache is not set if there is an error.
	 *
	 * @since 1.0.0
	 */
	public function test_cache_not_set_if_error() {

		$this->mock_apps();

		wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->register(
				'transients'
				, 'WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache'
			);

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_both' )
			, array( new DateTime( '-1 months' ) )
		);

		$stub->method( 'get_sql_for_both' )
			->willReturn( new WP_Error() );

		$this->assertWPError( $stub->get() );
		$this->assertFalse(
			WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache::$value
		);
	}

	/**
	 * Tests that it doesn't use the block logs when the period is too short.
	 *
	 * @since 1.0.0
	 */
	public function test_should_not_use_block_logs() {

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_points_logs' )
			, array( new DateTime( '-1 day' ) )
		);

		$stub->expects( $this->once() )
			->method( 'get_sql_for_points_logs' )
			->willReturn( 'test' );

		$this->assertSame( 'test', $stub->get_sql() );
	}

	/**
	 * Tests that it uses the block logs when the period is longer.
	 *
	 * @since 1.0.0
	 */
	public function test_should_use_block_logs() {

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_both' )
			, array( new DateTime( '-1 month' ) )
		);

		$stub->expects( $this->once() )
			->method( 'get_sql_for_both' )
			->willReturn( 'test' );

		$this->assertSame( 'test', $stub->get_sql() );
	}

	/**
	 * Tests that the use of block logs can be enabled via the filter.
	 *
	 * @since 1.0.0
	 */
	public function test_should_use_block_logs_filter() {

		add_filter(
			'wordpoints_top_users_in_period_query_use_blocks'
			, '__return_true'
		);

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_both' )
			, array( new DateTime( '-1 day' ) )
		);

		$stub->expects( $this->once() )
			->method( 'get_sql_for_both' )
			->willReturn( 'test' );

		$this->assertSame( 'test', $stub->get_sql() );
	}

	/**
	 * Tests that the use of block logs can be disabled via the filter.
	 *
	 * @since 1.0.0
	 */
	public function test_should_use_block_logs_filter_disable() {

		add_filter(
			'wordpoints_top_users_in_period_query_use_blocks'
			, '__return_false'
		);

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_points_logs' )
			, array( new DateTime( '-1 month' ) )
		);

		$stub->expects( $this->once() )
			->method( 'get_sql_for_points_logs' )
			->willReturn( 'test' );

		$this->assertSame( 'test', $stub->get_sql() );
	}

	/**
	 * Tests that it uses the block logs when the period fits one block exactly.
	 *
	 * @since 1.0.0
	 */
	public function test_should_use_block_logs_two_blocks() {

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$second_info = $block->get_block_info( current_time( 'timestamp' ) );
		$first_info = $block->get_block_info( $second_info['start'] - 1 );

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_both' )
			, array(
				new DateTime( '@' . $first_info['start'] ),
				new DateTime( '@' . ( $second_info['end'] - 50 ) ),
			)
		);

		$stub->expects( $this->once() )
			->method( 'get_sql_for_both' )
			->willReturn( 'test' );

		$this->assertSame( 'test', $stub->get_sql() );
	}

	/**
	 * Tests that it uses the block logs when the period fits one block exactly.
	 *
	 * @since 1.0.0
	 */
	public function test_should_use_block_logs_two_blocks_end() {

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$second_info = $block->get_block_info( current_time( 'timestamp' ) );
		$first_info = $block->get_block_info( $second_info['start'] - 1 );

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_both' )
			, array(
				new DateTime( '@' . ( $first_info['start'] + 50 ) ),
				new DateTime( '@' . $second_info['end'] ),
			)
		);

		$stub->expects( $this->once() )
			->method( 'get_sql_for_both' )
			->willReturn( 'test' );

		$this->assertSame( 'test', $stub->get_sql() );
	}

	/**
	 * Tests that it uses the block logs when the period fits the blocks exactly.
	 *
	 * @since 1.0.0
	 */
	public function test_should_use_block_logs_only_one_block() {

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$info = $block->get_block_info( current_time( 'timestamp' ) );

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_block_logs' )
			, array(
				new DateTime( '@' . $info['start'] ),
				new DateTime( '@' . $info['end'] ),
			)
		);

		$stub->expects( $this->once() )
			->method( 'get_sql_for_block_logs' )
			->willReturn( 'test' );

		$this->assertSame( 'test', $stub->get_sql() );
	}

	/**
	 * Tests that it uses the block logs when the period fits the blocks exactly.
	 *
	 * @since 1.0.0
	 */
	public function test_should_use_block_logs_only_multiple_blocks() {

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$end_info = $block->get_block_info( current_time( 'timestamp' ) );
		$start_info = $block->get_block_info(
			$end_info['start'] - 2 * WEEK_IN_SECONDS
		);

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_sql_for_block_logs' )
			, array(
				new DateTime( '@' . $start_info['start'] ),
				new DateTime( '@' . $end_info['end'] ),
			)
		);

		$stub->expects( $this->once() )
			->method( 'get_sql_for_block_logs' )
			->willReturn( 'test' );

		$this->assertSame( 'test', $stub->get_sql() );
	}

	/**
	 * Tests getting the results.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_get_results(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date, $end_date );

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);
	}

	/**
	 * Data provider for start and end dates to test getting results for.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] The sets of start and end dates.
	 */
	public function data_provider_start_end_dates() {

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$end_info = $block->get_block_info( current_time( 'timestamp' ) );
		$start_info = $block->get_block_info(
			$end_info['start'] - 2 * WEEK_IN_SECONDS
		);

		$now = new DateTime();

		return array(
			'points_logs' => array( new DateTime( '-1 day' ), $now ),
			'both' => array( new DateTime( '-1 month' ), $now, 2 * WEEK_IN_SECONDS ),
			'both_start_block_exact' => array(
				new DateTime( '@' . $start_info['start'] ),
				new DateTime( '@' . ( $end_info['end'] - 50 ) ),
			),
			'both_end_block_exact' => array(
				new DateTime( '@' . ( $start_info['start'] + 50 ) ),
				new DateTime( '@' . $end_info['end'] ),
			),
			'one_block_exactly' => array(
				new DateTime( '@' . $start_info['start'] ),
				new DateTime( '@' . $start_info['end'] ),
			),
			'multiple_blocks_exactly' => array(
				new DateTime( '@' . $start_info['start'] ),
				new DateTime( '@' . $end_info['end'] ),
			),
		);
	}

	/**
	 * Tests getting the results.
	 *
	 * @since 1.0.0
	 *
	 * @expectedIncorrectUsage WordPoints_Top_Users_In_Period_Points_Logs_Query::prepare_select
	 */
	public function test_arg_fields() {

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 month' )
			, null
			, array( 'fields' => 'total' )
		);

		$this->assertSame( array(), $query->get() );
	}

	/**
	 * Tests the 'limit' arg.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_limit(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'limit' => 1 )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'start' arg.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_start(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'start' => 1, 'limit' => 1 )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'order' arg.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_order(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'order' => 'ASC' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[1]
		);
	}

	/**
	 * Tests the 'order_by' arg.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_order_by(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'order_by' => 'user_id' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[1]
		);
	}

	/**
	 * Tests the 'id*' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_id(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		// A transaction that took place right at the start.
		$log_id = $this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_ids[0],
				'points'  => 32,
				'date'    => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// A transaction that took place right at the end.
		$log_id_2 = $this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_ids[1],
				'points'  => 64,
				'date'    => $end_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// 'id' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'id' => $log_id, 'id__compare' => '!=' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '66', 'user_id' => (string) $user_ids[1] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[1]
		);

		// 'id__in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'id__in' => array( $log_id, $log_id_2 ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '64', 'user_id' => (string) $user_ids[1] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '32', 'user_id' => (string) $user_ids[0] )
			, $results[1]
		);

		// 'id__not_in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'id__not_in' => array( $log_id, $log_id_2 ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);
	}

	/**
	 * Tests the 'user_id*' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_user_id(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );
		$user_ids[2] = $this->factory->user->create();

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_ids[2],
				'points'  => 32,
				'date'    => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// 'user_id' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'user_id' => $user_ids[2] )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '32', 'user_id' => (string) $user_ids[2] )
			, $results[0]
		);

		// 'user_id__compare' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'user_id' => $user_ids[2], 'user_id__compare' => '!=' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'user_id__in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'user_id__in' => array( $user_ids[0], $user_ids[2] ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '32', 'user_id' => (string) $user_ids[2] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[1]
		);

		// 'user_id__not_in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'user_id__not_in' => array( $user_ids[0], $user_ids[2] ) )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'points_type*' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_points_type(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id'     => $user_ids[0],
				'points_type' => 'other',
				'points'      => 32,
				'date'        => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id'     => $user_ids[0],
				'points_type' => 'third',
				'points'      => 64,
				'date'        => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// 'points_type' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'points_type' => 'points' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'points_type__compare' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'points_type' => 'points', 'points_type__compare' => '!=' )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '96', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		// 'points_type__in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'points_type__in' => array( 'points', 'other' ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '37', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'points_type__not_in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'points_type__not_in' => array( 'points', 'other' ) )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '64', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'log_type*' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_log_type(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id'  => $user_ids[0],
				'log_type' => 'other',
				'points'   => 32,
				'date'     => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id'  => $user_ids[0],
				'log_type' => 'third',
				'points'   => 64,
				'date'     => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// 'log_type' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'log_type' => 'test' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'log_type__compare' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'log_type' => 'test', 'log_type__compare' => '!=' )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '96', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		// 'log_type__in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'log_type__in' => array( 'test', 'other' ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '37', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'log_type__not_in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'log_type__not_in' => array( 'test', 'other' ) )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '64', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'text*' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_text(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_ids[0],
				'text'    => 'other',
				'points'  => 32,
				'date'    => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_ids[0],
				'text'    => 'third',
				'points'  => 64,
				'date'    => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// 'text' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'text' => 'Log text%' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'text__compare' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'text' => 'other', 'text__compare' => '!=' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '69', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'text__in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'text__in' => array( 'third', 'other' ) )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '96', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		// 'text__not_in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'text__not_in' => array( 'third', 'other' ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);
	}

	/**
	 * Tests the 'points*' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_points(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		// 'points' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'points' => 4 )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '4', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		// 'points__compare' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'points' => 4, 'points__compare' => '!=' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '1', 'user_id' => (string) $user_ids[0] )
			, $results[1]
		);

		// 'points__in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'points__in' => array( 2, 4 ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '4', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'points__not_in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'points__not_in' => array( 2, 4 ) )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '1', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'total*' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_total(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );
		$user_ids[2] = $this->factory->user->create();

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id'  => $user_ids[2],
				'points'   => 32,
				'date'     => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// 'total' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'total' => 5 )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		// 'total__compare' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'total' => 5, 'total__compare' => '!=' )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '32', 'user_id' => (string) $user_ids[2] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'total__in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'total__in' => array( 2, 5 ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'total__not_in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'total__not_in' => array( 2, 5 ) )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '32', 'user_id' => (string) $user_ids[2] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'meta*' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_meta(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id'  => $user_ids[0],
				'points'   => 32,
				'date'     => $start_date->format( 'Y-m-d H:i:s' ),
				'meta'     => array( 'test_1' => 'a' ),
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'meta_query' => array( array( 'key' => 'test_1' ) ) )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '32', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'date_query' args.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_date(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		$date = clone $start_date;

		// Add 45 minutes to the start time.
		$date->modify( '+45 minutes' );

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id'  => $user_ids[0],
				'points'   => 32,
				'date'     => $date->format( 'Y-m-d H:i:s' ),
			)
		);

		// Subtract an hour.
		$date->modify( '-1 hour' );

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id'  => $user_ids[0],
				'points'   => 64,
				'date'     => $date->format( 'Y-m-d H:i:s' ),
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array(
				'date_query' => array(
					array( 'minute' => $date->format( 'i' ), 'compare' => '=' ),
				),
			)
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '32', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);
	}

	/**
	 * Tests the 'blog_id*' args.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 *
	 * @dataProvider data_provider_start_end_dates
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 */
	public function test_arg_blog_id(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle = null
	) {

		$user_ids = $this->create_logs( $start_date, $end_date, $to_middle );

		$current_blog_id = get_current_blog_id();

		$blog_id = $this->factory->blog->create();

		switch_to_blog( $blog_id );

		if ( ! is_wordpoints_network_active() ) {
			wordpoints_add_points_type( array( 'name' => 'points' ) );
		}

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_ids[0],
				'points'  => 32,
				'date'    => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		restore_current_blog();

		switch_to_blog( $this->factory->blog->create() );

		if ( ! is_wordpoints_network_active() ) {
			wordpoints_add_points_type( array( 'name' => 'points' ) );
		}

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_ids[0],
				'points'  => 64,
				'date'    => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		restore_current_blog();

		// 'blog_id' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'blog_id' => $current_blog_id )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '5', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'blog_id__compare' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'blog_id' => $current_blog_id, 'blog_id__compare' => '!=' )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '96', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		// 'blog_id__in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'blog_id__in' => array( $current_blog_id, $blog_id ) )
		);

		$results = $query->get();

		$this->assertCount( 2, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '37', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);

		$this->assertSameProperties(
			(object) array( 'total' => '2', 'user_id' => (string) $user_ids[1] )
			, $results[1]
		);

		// 'blog_id__not_in' arg.
		$query = new WordPoints_Top_Users_In_Period_Query(
			$start_date
			, $end_date
			, array( 'blog_id__not_in' => array( $current_blog_id, $blog_id ) )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );

		$this->assertSameProperties(
			(object) array( 'total' => '64', 'user_id' => (string) $user_ids[0] )
			, $results[0]
		);
	}

	/**
	 * Tests that it returns an error if there was one while verifying the blocks.
	 *
	 * @since 1.0.0
	 */
	public function test_get_sql_for_block_logs_error() {

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_and_verify_blocks' )
			, array( new DateTime( '-1 months' ) )
		);

		$error = new WP_Error();

		$stub->method( 'get_and_verify_blocks' )
			->willReturn( $error );

		$this->assertSame( $error, $stub->get() );
	}

	/**
	 * Tests that it returns an error if there was one while getting the blocks.
	 *
	 * @since 1.0.1
	 */
	public function test_get_and_verify_blocks_get_blocks_error() {

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_blocks' )
			, array( new DateTime( '-1 months' ) )
		);

		$error = new WP_Error();

		$stub->method( 'get_blocks' )
			->willReturn( $error );

		$this->assertSame( $error, $stub->get() );
	}

	/**
	 * Tests that it returns an error if there were any draft blocks.
	 *
	 * @since 1.0.0
	 */
	public function test_get_and_verify_blocks_draft_blocks() {

		global $wpdb;

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures'
			, array(
				'signature'  => self::$default_signature,
				'query_args' => '',
			)
		);

		$signature_id = $wpdb->insert_id;

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$info = $block->get_block_info(
			current_time( 'timestamp' ) - WEEK_IN_SECONDS
		);

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, array(
				'block_type'         => 'week_in_seconds',
				'start_date'         => date( 'Y-m-d H:i:s', $info['start'] ),
				'end_date'           => date( 'Y-m-d H:i:s', $info['end'] ),
				'query_signature_id' => $signature_id,
				'status'             => 'draft',
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertWPError( $query->get() );
	}

	/**
	 * Tests that it returns an error if there was one while getting the signature.
	 *
	 * @since 1.0.1
	 */
	public function test_get_blocks_error() {

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'get_query_signature_id' )
			, array( new DateTime( '-1 months' ) )
		);

		$stub->method( 'get_query_signature_id' )
			->willReturn( false );

		$results = $stub->get();

		$this->assertWPError( $results );
		$this->assertSame(
			'wordpoints_top_users_in_period_query_failed_inserting_signature'
			, $results->get_error_code()
		);
	}

	/**
	 * Tests that it only inserts the query signature if not found.
	 *
	 * @since 1.0.1
	 */
	public function test_get_query_signature_id() {

		$signature_query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query(
			array( 'signature' => self::$default_signature )
		);

		$this->assertSame( 0, $signature_query->count() );

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSame( array(), $query->get() );

		$this->assertSame( 1, $signature_query->count() );

		$signature = $signature_query->get( 'row' );

		$this->assertSame(
			is_multisite() ? '{"blog_id":1,"site_id":1}' : '[]'
			, $signature->query_args
		);
	}

	/**
	 * Tests that it only gets blocks of the correct type.
	 *
	 * @since 1.0.0
	 */
	public function test_get_blocks_other_type() {

		global $wpdb;

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures'
			, array(
				'signature'  => self::$default_signature,
				'query_args' => '',
			)
		);

		$signature_id = $wpdb->insert_id;

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$info = $block->get_block_info(
			current_time( 'timestamp' ) - WEEK_IN_SECONDS
		);

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, array(
				'block_type'         => 'other',
				'start_date'         => date( 'Y-m-d H:i:s', $info['start'] ),
				'end_date'           => date( 'Y-m-d H:i:s', $info['end'] ),
				'query_signature_id' => $signature_id,
				'status'             => 'draft',
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSame( array(), $query->get() );
	}

	/**
	 * Tests that it only gets blocks for the same query signature.
	 *
	 * @since 1.0.0
	 */
	public function test_get_blocks_other_signature() {

		global $wpdb;

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures'
			, array(
				'signature'  => 'other',
				'query_args' => '',
			)
		);

		$signature_id = $wpdb->insert_id;

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$info = $block->get_block_info(
			current_time( 'timestamp' ) - WEEK_IN_SECONDS
		);

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, array(
				'block_type'         => 'week_in_seconds',
				'start_date'         => date( 'Y-m-d H:i:s', $info['start'] ),
				'end_date'           => date( 'Y-m-d H:i:s', $info['end'] ),
				'query_signature_id' => $signature_id,
				'status'             => 'draft',
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 months' )
		);

		$this->assertSame( array(), $query->get() );
	}

	/**
	 * Tests that it only gets blocks for the period.
	 *
	 * @since 1.0.0
	 */
	public function test_get_blocks_outside_period() {

		global $wpdb;

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures'
			, array(
				'signature'  => self::$default_signature,
				'query_args' => '',
			)
		);

		$signature_id = $wpdb->insert_id;

		$start_date = new DateTime( '-1 months' );
		$end_date   = new DateTime();

		$start = (int) $start_date->format( 'U' );
		$end = (int) $end_date->format( 'U' );

		// Before the period.
		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, array(
				'block_type'         => 'week_in_seconds',
				'start_date'         => date( 'Y-m-d H:i:s', $start - WEEK_IN_SECONDS ),
				'end_date'           => date( 'Y-m-d H:i:s', $start - 1 ),
				'query_signature_id' => $signature_id,
				'status'             => 'draft',
			)
		);

		// After the period.
		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, array(
				'block_type'         => 'week_in_seconds',
				'start_date'         => date( 'Y-m-d H:i:s', $end + 1 ),
				'end_date'           => date( 'Y-m-d H:i:s', $end + WEEK_IN_SECONDS ),
				'query_signature_id' => $signature_id,
				'status'             => 'draft',
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date, $end_date );

		$this->assertSame( array(), $query->get() );
	}

	/**
	 * Tests that it returns an error if there were any draft blocks.
	 *
	 * @since 1.0.0
	 */
	public function test_get_and_verify_blocks_failed_filling_block_logs() {

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'fill_block_logs' )
			, array( new DateTime( '-1 months' ) )
		);

		$stub->method( 'fill_block_logs' )->willReturn( false );

		$this->assertWPError( $stub->get() );
	}

	/**
	 * Tests that it creates the missing blocks correctly when all are missing.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_provider_blocks
	 *
	 * @param int[][] $expected_blocks Start and end timestamps for expected blocks.
	 * @param int     $period_start    Timestamp of the start of the period.
	 * @param int     $period_end      Timestamp of the end of the period.
	 * @param int[][] $pre_filled      Start and end timestamps for blocks to pre-
	 *                                 fill before running the query.
	 */
	public function test_check_for_missing_blocks(
		$expected_blocks,
		$period_start = null,
		$period_end = null,
		$pre_filled = array()
	) {

		global $wpdb;

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures'
			, array(
				'signature'  => self::$default_signature,
				'query_args' => '',
			)
		);

		$signature_id = $wpdb->insert_id;

		// Pre-fill any blocks requested to be pre-filled.
		foreach ( $pre_filled as $block ) {

			$wpdb->insert(
				$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
				, array(
					'block_type'         => 'week_in_seconds',
					'start_date'         => date( 'Y-m-d H:i:s', $block['start'] ),
					'end_date'           => date( 'Y-m-d H:i:s', $block['end'] ),
					'query_signature_id' => $signature_id,
					'status'             => 'filled',
				)
			);
		}

		// Construct the query.
		$count = count( $expected_blocks );

		if ( ! isset( $period_start ) ) {
			$period_start = $expected_blocks[0]['start'];
		}

		if ( ! isset( $period_end ) ) {
			$period_end = $expected_blocks[ $count - 1 ]['end'];
		}

		$start_date = new DateTime( '@' . $period_start );
		$end_date   = new DateTime( '@' . $period_end );

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date, $end_date );

		$this->assertSame( array(), $query->get() );

		// Check that the expected blocks now exist in the database.
		$blocks = $wpdb->get_results(
			"
				SELECT * 
				FROM {$wpdb->base_prefix}wordpoints_top_users_in_period_blocks
				ORDER BY `start_date` ASC
			"
		);

		$this->assertCount( $count, $blocks );

		foreach ( $expected_blocks as $index => $block ) {

			$this->assertSame(
				date( 'Y-m-d H:i:s', $block['start'] )
				, $blocks[ $index ]->start_date
			);

			$this->assertSame(
				date( 'Y-m-d H:i:s', $block['end'] )
				, $blocks[ $index ]->end_date
			);
		}
	}

	/**
	 * Data provider for sets of blocks.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sets of blocks.
	 */
	public function data_provider_blocks() {

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$first_info = $block->get_block_info(
			current_time( 'timestamp' ) - MONTH_IN_SECONDS
		);

		$second_info = $block->get_block_info( $first_info['end'] + 1 );
		$third_info = $block->get_block_info( $second_info['end'] + 1 );
		$fourth_info = $block->get_block_info( $third_info['end'] + 1 );
		$fifth_info = $block->get_block_info( $fourth_info['end'] + 1 );

		return array(
			'all_one_exactly' => array( array( $first_info ) ),
			'all_one_fits_start' => array(
				array( $first_info ),
				$first_info['start'],
				$first_info['end'] + HOUR_IN_SECONDS,
			),
			'all_one_fits_end' => array(
				array( $first_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$first_info['end'],
			),
			'all_one' => array(
				array( $first_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$first_info['end'] + HOUR_IN_SECONDS,
			),
			'all_multiple_exactly' => array(
				array( $first_info, $second_info, $third_info ),
			),
			'all_multiple_fits_start' => array(
				array( $first_info, $second_info, $third_info ),
				$first_info['start'],
				$third_info['end'] + HOUR_IN_SECONDS,
			),
			'all_multiple_fits_end' => array(
				array( $first_info, $second_info, $third_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$third_info['end'],
			),
			'all_multiple' => array(
				array( $first_info, $second_info, $third_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$third_info['end'] + HOUR_IN_SECONDS,
			),
			'start_multiple_exactly' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				null,
				null,
				array( $third_info, $fourth_info ),
			),
			'start_multiple_fits_start' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'],
				$fourth_info['end'] + HOUR_IN_SECONDS,
				array( $third_info, $fourth_info ),
			),
			'start_multiple_fits_end' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fourth_info['end'],
				array( $third_info, $fourth_info ),
			),
			'start_multiple' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fourth_info['end'] + HOUR_IN_SECONDS,
				array( $third_info, $fourth_info ),
			),
			'end_multiple_exactly' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				null,
				null,
				array( $first_info, $second_info ),
			),
			'end_multiple_fits_start' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'],
				$fourth_info['end'] + HOUR_IN_SECONDS,
				array( $first_info, $second_info ),
			),
			'end_multiple_fits_end' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fourth_info['end'],
				array( $first_info, $second_info ),
			),
			'end_multiple' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fourth_info['end'] + HOUR_IN_SECONDS,
				array( $first_info, $second_info ),
			),
			'middle_multiple_exactly' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				null,
				null,
				array( $first_info, $fourth_info ),
			),
			'middle_multiple_fits_start' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'],
				$fourth_info['end'] + HOUR_IN_SECONDS,
				array( $first_info, $fourth_info ),
			),
			'middle_multiple_fits_end' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fourth_info['end'],
				array( $first_info, $fourth_info ),
			),
			'middle_multiple' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fourth_info['end'] + HOUR_IN_SECONDS,
				array( $first_info, $fourth_info ),
			),
			'both_ends_multiple_exactly' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				null,
				null,
				array( $second_info, $third_info ),
			),
			'both_ends_multiple_fits_start' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'],
				$fourth_info['end'] + HOUR_IN_SECONDS,
				array( $second_info, $third_info ),
			),
			'both_ends_multiple_fits_end' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fourth_info['end'],
				array( $second_info, $third_info ),
			),
			'both_ends_multiple' => array(
				array( $first_info, $second_info, $third_info, $fourth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fourth_info['end'] + HOUR_IN_SECONDS,
				array( $second_info, $third_info ),
			),
			'all_three_multiple_exactly' => array(
				array( $first_info, $second_info, $third_info, $fourth_info, $fifth_info ),
				null,
				null,
				array( $second_info, $fourth_info ),
			),
			'all_three_multiple_fits_start' => array(
				array( $first_info, $second_info, $third_info, $fourth_info, $fifth_info ),
				$first_info['start'],
				$fifth_info['end'] + HOUR_IN_SECONDS,
				array( $second_info, $fourth_info ),
			),
			'all_three_multiple_fits_end' => array(
				array( $first_info, $second_info, $third_info, $fourth_info, $fifth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fifth_info['end'],
				array( $second_info, $fourth_info ),
			),
			'all_three_multiple' => array(
				array( $first_info, $second_info, $third_info, $fourth_info, $fifth_info ),
				$first_info['start'] - HOUR_IN_SECONDS,
				$fifth_info['end'] + HOUR_IN_SECONDS,
				array( $second_info, $fourth_info ),
			),
		);
	}

	/**
	 * Tests that it returns an error if a draft block couldn't be saved (or filled).
	 *
	 * @since 1.0.0
	 */
	public function test_fill_block_logs_error_saving_draft_block() {

		$stub = $this->getMock(
			'WordPoints_Top_Users_In_Period_Query'
			, array( 'save_draft_block' )
			, array( new DateTime( '-1 month' ) )
		);

		$stub->method( 'save_draft_block' )->willReturn( false );

		$this->assertWPError( $stub->get() );
	}

	/**
	 * Tests filling the block logs from the points logs.
	 *
	 * @since 1.0.0
	 */
	public function test_fill_block_logs() {

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$start_info = $block->get_block_info( current_time( 'timestamp' ) );

		$start_date = new DateTime( '@' . $start_info['start'] );
		$end_date = new DateTime( '@' . $start_info['end'] );

		$user_id_1 = $this->factory->user->create();
		$user_id_2 = $this->factory->user->create();

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_1,
				'points' => 1,
				'date' => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// A transaction that took place right at the end.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_2,
				'points' => 2,
				'date' => $end_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// A transaction that took place in the middle.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_1,
				'points' => 3,
				'date' => date(
					'Y-m-d H:i:s'
					, (int) $start_date->format( 'U' ) + HOUR_IN_SECONDS
				),
			)
		);

		// A transaction that took place just before the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_1,
				'points' => 5,
				'date' => date(
					'Y-m-d H:i:s'
					, (int) $start_date->format( 'U' ) - 1
				),
			)
		);

		// A transaction that took place just after the end.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_2,
				'points' => 7,
				'date' => date(
					'Y-m-d H:i:s'
					, (int) $end_date->format( 'U' ) + 1
				),
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date, $end_date );
		$query->get();

		global $wpdb;

		$results = $wpdb->get_results(
			"
				SELECT * 
				FROM {$wpdb->base_prefix}wordpoints_top_users_in_period_block_logs
				ORDER BY `user_id` ASC
			"
		);

		$this->assertCount( 2, $results );

		$this->assertSame( '4', $results[0]->points );
		$this->assertSame( (string) $user_id_1, $results[0]->user_id );

		$this->assertSame( '2', $results[1]->points );
		$this->assertSame( (string) $user_id_2, $results[1]->user_id );
	}

	/**
	 * Tests publishing a block.
	 *
	 * @since 1.0.0
	 */
	public function test_publish_block() {

		$block = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$start_info = $block->get_block_info( current_time( 'timestamp' ) );

		$start_date = new DateTime( '@' . $start_info['start'] );
		$end_date = new DateTime( '@' . $start_info['end'] );

		$query = new WordPoints_Top_Users_In_Period_Query( $start_date, $end_date );

		$this->assertSame( array(), $query->get() );

		global $wpdb;

		$results = $wpdb->get_results(
			"
				SELECT * 
				FROM {$wpdb->base_prefix}wordpoints_top_users_in_period_blocks
			"
		);

		$this->assertCount( 1, $results );

		$this->assertSame( 'filled', $results[0]->status );
	}

	/**
	 * Create some points logs around a period.
	 *
	 * @since 1.0.0
	 *
	 * @param DateTime $start_date The start date.
	 * @param DateTime $end_date   The end date.
	 * @param int      $to_middle  The distance to somewhere in the middle.
	 *
	 * @return int[] The IDs of the 2 users created.
	 */
	protected function create_logs(
		DateTime $start_date,
		DateTime $end_date,
		$to_middle
	) {

		if ( ! isset( $to_middle ) ) {
			$to_middle = HOUR_IN_SECONDS;
		}

		$user_id_1 = $this->factory->user->create();
		$user_id_2 = $this->factory->user->create();

		// A transaction that took place right at the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_1,
				'points'  => 1,
				'date'    => $start_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// A transaction that took place right at the end.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_2,
				'points'  => 2,
				'date'    => $end_date->format( 'Y-m-d H:i:s' ),
			)
		);

		// A transaction that took place in the middle.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_1,
				'points'  => 4,
				'date'    => date(
					'Y-m-d H:i:s'
					, (int) $start_date->format( 'U' ) + $to_middle
				),
			)
		);

		// A transaction that took place just before the start.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_1,
				'points'  => 8,
				'date'    => date(
					'Y-m-d H:i:s'
					, (int) $start_date->format( 'U' ) - 1
				),
			)
		);

		// A transaction that took place just after the end.
		$this->factory->wordpoints->points_log->create(
			array(
				'user_id' => $user_id_2,
				'points'  => 16,
				'date'    => date(
					'Y-m-d H:i:s'
					, (int) $end_date->format( 'U' ) + 1
				),
			)
		);

		return array( $user_id_1, $user_id_2 );
	}
}

// EOF
