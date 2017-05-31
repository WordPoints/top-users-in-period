<?php

/**
 * Test case for the wordpoints_top_users_in_period_delete_block_logs_for_user() function.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.1
 */

/**
 * Tests the wordpoints_top_users_in_period_delete_block_logs_for_user() function.
 *
 * @since 1.0.1
 *
 * @covers ::wordpoints_top_users_in_period_delete_block_logs_for_user
 */
class WordPoints_Top_Users_In_Period_Delete_Block_Logs_For_User_Function_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests that it flushes the cache when a user is deleted.
	 *
	 * @since 1.0.1
	 */
	public function test_flushes_cache_on_user_delete() {

		$log = $this->factory->wordpoints->points_log->create_and_get();

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		wp_delete_user( $log->user_id );

		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 4, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Tests that it flushes the cache only when a matching user is deleted.
	 *
	 * @since 1.0.1
	 */
	public function test_flushes_cache_only_for_matching_user() {

		$user_id = $this->factory->user->create();

		$this->factory->wordpoints->points_log->create();

		$query = new WordPoints_Top_Users_In_Period_Query(
			new DateTime( '-1 day' )
		);

		$this->listen_for_filter( 'query', array( $this, 'is_points_logs_query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		wp_delete_user( $user_id );

		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		$query->get();

		$this->assertSame( 3, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Tests that it deletes the block logs for a user when the user is deleted.
	 *
	 * @since 1.0.1
	 */
	public function test_deletes_block_logs_on_user_delete() {

		global $wpdb;

		$user_id = $this->factory->user->create();

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_block_logs'
			, array(
				'block_id' => 1,
				'points'   => 5,
				'user_id'  => $user_id,
			)
		);

		$user_id_2 = $this->factory->user->create();

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_block_logs'
			, array(
				'block_id' => 1,
				'points'   => 5,
				'user_id'  => $user_id_2,
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Block_Logs_Query();

		$this->assertCount( 2, $query->get() );

		wp_delete_user( $user_id );

		$this->assertCount( 1, $query->get() );
		$this->assertSame( (string) $user_id_2, $query->get( 'row' )->user_id );
	}
}

// EOF
