<?php

/**
 * Test case for the wordpoints_top_users_in_period_query_caches_flush_for_log() function.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests the wordpoints_top_users_in_period_query_caches_flush_for_log() function.
 *
 * @since 1.0.0
 *
 * @covers ::wordpoints_top_users_in_period_query_caches_flush_for_log
 */
class WordPoints_Top_Users_In_Period_Query_Caches_Flush_For_Log_Function_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it flushes the cache when a new log is added.
	 *
	 * @since 1.0.0
	 */
	public function test_flushes_cache_for_log() {

		$this->factory->wordpoints->points_log->create();

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create();

		$query->get();

		$this->assertSame( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Tests that it flushes the cache based on the points type of a new log.
	 *
	 * @since 1.0.0
	 */
	public function test_flushes_cache_based_on_log_points_type() {

		$this->factory->wordpoints->points_log->create();

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
			, null
			, array( 'points_type' => 'points' )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create(
			array( 'points_type' => 'test' )
		);

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create();

		$query->get();

		$this->assertSame( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Tests that it flushes the cache based on the log type of a new log.
	 *
	 * @since 1.0.0
	 */
	public function test_flushes_cache_based_on_log_type() {

		$this->factory->wordpoints->points_log->create();

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
			, null
			, array( 'log_type' => 'test' )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create(
			array( 'log_type' => 'other' )
		);

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create();

		$query->get();

		$this->assertSame( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Tests that it flushes the cache based on the user ID for a new log.
	 *
	 * @since 1.0.0
	 */
	public function test_flushes_cache_based_on_log_user_id() {

		$user_id = $this->factory->user->create();

		$this->factory->wordpoints->points_log->create(
			array( 'user_id' => $user_id )
		);

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
			, null
			, array( 'user_id' => $user_id )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create();

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create(
			array( 'user_id' => $user_id )
		);

		$query->get();

		$this->assertSame( 2, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Tests that it flushes the cache based on the blog ID for a new log.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 */
	public function test_flushes_cache_based_on_log_blog_id() {

		$this->factory->wordpoints->points_log->create();

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create(
			array( 'blog_id' => $this->factory->blog->create() )
		);

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$this->factory->wordpoints->points_log->create();

		$query->get();

		$this->assertSame( 2, $this->filter_was_called( 'query' ) );
	}
}

// EOF
