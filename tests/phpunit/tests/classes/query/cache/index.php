<?php

/**
 * Test case for WordPoints_Top_Users_In_Period_Query_Cache_Index.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests WordPoints_Top_Users_In_Period_Query_Cache_Index.
 *
 * @since 1.0.0
 *
 * @covers WordPoints_Top_Users_In_Period_Query_Cache_Index
 */
class WordPoints_Top_Users_In_Period_Query_Cache_Index_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting the index.
	 *
	 * @since 1.0.0
	 */
	public function test_get() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array(), 5 );

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
	 * Tests getting the index when it is empty.
	 *
	 * @since 1.0.0
	 */
	public function test_get_empty() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$this->assertSame( array(), $index->get() );
	}

	/**
	 * Tests that order of the query args doesn't affect the query signature.
	 *
	 * @since 1.0.0
	 */
	public function test_query_arg_order() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array( 'a' => 1, 'b' => 2 ), 5 );
		$index->add( 'test', array( 'b' => 2, 'a' => 1 ), 5 );

		$this->assertSame(
			array(
				'43258cff783fe7036d8a43033f830adfc60ec037382473548ac742b888292777' => array(
					'args'   => array( 'a' => 1, 'b' => 2 ),
					'caches' => array(
						'test' => array( 5 => array( 'none' => true ) ),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests adding a query with an end date.
	 *
	 * @since 1.0.1
	 */
	public function test_add_end_timestamp() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array(), 5, 7 );
		$index->add( 'test', array(), 5, 8 );

		$this->assertSame(
			array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array(
							5 => array( 7 => true, 8 => true ),
						),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests adding two queries with the same args but different timestamps.
	 *
	 * @since 1.0.0
	 */
	public function test_add_different_timestamp() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array(), 5 );
		$index->add( 'test', array(), 4 );

		$this->assertSame(
			array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array(
							5 => array( 'none' => true ),
							4 => array( 'none' => true ),
						),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests adding two queries with the same args but of different types.
	 *
	 * @since 1.0.0
	 */
	public function test_add_different_type() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array(), 5 );
		$index->add( 'other', array(), 5 );

		$this->assertSame(
			array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test'  => array( 5 => array( 'none' => true ) ),
						'other' => array( 5 => array( 'none' => true ) ),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests adding two queries with the same timestamps but different args.
	 *
	 * @since 1.0.0
	 */
	public function test_add_different_args() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array(), 5 );
		$index->add( 'test', array( 'a' => 1, 'b' => 2 ), 5 );

		$this->assertSame(
			array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => array( 'none' => true ) ),
					),
				),
				'43258cff783fe7036d8a43033f830adfc60ec037382473548ac742b888292777' => array(
					'args'   => array( 'a' => 1, 'b' => 2 ),
					'caches' => array(
						'test' => array( 5 => array( 'none' => true ) ),
					),
				),
			)
			, $index->get()
		);
	}

	/**
	 * Tests that network options are used when when network wide is true.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_network() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index( true );

		$index->add( 'test', array(), 5 );

		$this->assertSame(
			array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => array( 'none' => true ) ),
					),
				),
			)
			, get_site_option(
				'wordpoints_top_users_in_period_query_cache_index'
			)
		);
	}

	/**
	 * Tests that regular options are used when when network wide is not true.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_not() {

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$index->add( 'test', array(), 5 );

		$this->assertSame(
			array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => array( 'none' => true ) ),
					),
				),
			)
			, get_option(
				'wordpoints_top_users_in_period_query_cache_index'
			)
		);
	}
}

// EOF
