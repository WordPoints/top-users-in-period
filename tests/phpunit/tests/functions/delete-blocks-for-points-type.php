<?php

/**
 * Test case for the wordpoints_top_users_in_period_delete_blocks_for_points_type() function.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.1
 */

/**
 * Tests the wordpoints_top_users_in_period_delete_blocks_for_points_type() function.
 *
 * @since 1.0.1
 *
 * @covers ::wordpoints_top_users_in_period_delete_blocks_for_points_type
 */
class WordPoints_Top_Users_In_Period_Delete_Blocks_For_Points_Type_Function_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it flushes the cache when a points type is deleted.
	 *
	 * @since 1.0.1
	 */
	public function test_flushes_cache_on_points_type_delete() {

		$this->factory->wordpoints->points_log->create_and_get();

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
			, null
			, array( 'points_type' => 'points' )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		wordpoints_delete_points_type( 'points' );

		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 4, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Tests that it flushes the cache only when a matching points type is deleted.
	 *
	 * @since 1.0.1
	 */
	public function test_flushes_cache_only_for_matching_points_type() {

		$slug = $this->factory->wordpoints->points_type->create();

		$this->factory->wordpoints->points_log->create();

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
			, null
			, array( 'points_type' => 'points' )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		wordpoints_delete_points_type( $slug );

		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 3, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Tests that it deletes the block info for a points type when the it is deleted.
	 *
	 * @since 1.0.1
	 *
	 * @dataProvider data_provider_matching_queries
	 *
	 * @param array $query_args The args for the query.
	 */
	public function test_deletes_block_info_on_points_type_delete( $query_args ) {

		$this->factory->wordpoints->points_type->create(
			array( 'name' => 'other' )
		);

		$this->factory->wordpoints->points_type->create(
			array( 'name' => 'third' )
		);

		// Make sure that the log falls within a block, so a block log will be added.
		$this->factory->wordpoints->points_log->create(
			array( 'date' => date( 'Y-m-d H:i:s', strtotime( '-2 weeks' ) ) )
		);

		$this->factory->wordpoints->points_log->create(
			array(
				'points_type' => 'other',
				'date'        => date( 'Y-m-d H:i:s', strtotime( '-2 weeks' ) ),
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 month' )
			, null
			, $query_args
		);

		$query->get();

		$signatures_query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query();

		$this->assertCount( 1, $signatures_query->get() );

		$blocks_query = new WordPoints_Top_Users_In_Period_Blocks_Query();

		$this->assertGreaterThan( 0, count( $blocks_query->get() ) );

		$block_logs_query = new WordPoints_Top_Users_In_Period_Block_Logs_Query();

		$this->assertGreaterThan( 0, count( $block_logs_query->get() ) );

		wordpoints_delete_points_type( 'points' );

		$signatures_query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query();

		$this->assertCount( 0, $signatures_query->get() );

		$blocks_query = new WordPoints_Top_Users_In_Period_Blocks_Query();

		$this->assertCount( 0, $blocks_query->get() );

		$block_logs_query = new WordPoints_Top_Users_In_Period_Block_Logs_Query();

		$this->assertCount( 0, $block_logs_query->get() );
	}

	/**
	 * Provides sets of query args that should match the points type deletion.
	 *
	 * @since 1.0.1
	 *
	 * @return array[][] The sets of args.
	 */
	public function data_provider_matching_queries() {
		return array(
			'points_type_not_specified' => array(
				array(),
			),
			'points_type_same' => array(
				array( 'points_type' => 'points' ),
			),
			'points_type_different_compare_not_equals' => array(
				array(
					'points_type'          => 'other',
					'points_type__compare' => '!=',
				),
			),
			'points_type_same_compare_not_equals' => array(
				array(
					'points_type'          => 'points',
					'points_type__compare' => '!=',
				),
			),
			'points_type_same_compare_equals' => array(
				array(
					'points_type'          => 'points',
					'points_type__compare' => '=',
				),
			),
			'points_type_same_in' => array(
				array( 'points_type__in' => array( 'third', 'points' ) ),
			),
			'points_type_different_not_in' => array(
				array( 'points_type__not_in' => array( 'third', 'other' ) ),
			),
			'points_type_same_not_in' => array(
				array( 'points_type__not_in' => array( 'third', 'points' ) ),
			),
		);
	}

	/**
	 * Tests that it deletes the block info for a points type when the it is deleted.
	 *
	 * @since 1.0.1
	 *
	 * @dataProvider data_provider_non_matching_queries
	 *
	 * @param array $query_args The args for the query.
	 */
	public function test_deletes_block_logs_only_for_matching_points_type( $query_args ) {

		$slug = $this->factory->wordpoints->points_type->create(
			array( 'name' => 'other' )
		);

		$this->factory->wordpoints->points_type->create(
			array( 'name' => 'third' )
		);

		// Make sure that the log falls within a block, so a block log will be added.
		$this->factory->wordpoints->points_log->create(
			array( 'date' => date( 'Y-m-d H:i:s', strtotime( '-2 weeks' ) ) )
		);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 month' )
			, null
			, $query_args
		);

		$query->get();

		$signatures_query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query();

		$this->assertCount( 1, $signatures_query->get() );

		$blocks_query = new WordPoints_Top_Users_In_Period_Blocks_Query();

		$this->assertGreaterThan( 0, count( $blocks_query->get() ) );

		$block_logs_query = new WordPoints_Top_Users_In_Period_Block_Logs_Query();

		$this->assertCount( 1, $block_logs_query->get() );

		wordpoints_delete_points_type( $slug );

		$signatures_query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query();

		$this->assertCount( 1, $signatures_query->get() );

		$blocks_query = new WordPoints_Top_Users_In_Period_Blocks_Query();

		$this->assertGreaterThan( 0, count( $blocks_query->get() ) );

		$block_logs_query = new WordPoints_Top_Users_In_Period_Block_Logs_Query();

		$this->assertCount( 1, $block_logs_query->get() );
	}

	/**
	 * Provides sets of query args that should not match the points type deletion.
	 *
	 * @since 1.0.0
	 *
	 * @return array[][] The sets of args.
	 */
	public function data_provider_non_matching_queries() {
		return array(
			'points_type_different' => array(
				array( 'points_type' => 'points' ),
			),
			'points_type_different_compare_equals' => array(
				array(
					'points_type'          => 'points',
					'points_type__compare' => '=',
				),
			),
			'points_type_different_in' => array(
				array( 'points_type__in' => array( 'third', 'points' ) ),
			),
		);
	}
}

// EOF
