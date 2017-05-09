<?php

/**
 * Test case for WordPoints_Top_Users_In_Period_Blocks_Query.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests WordPoints_Top_Users_In_Period_Blocks_Query.
 *
 * @since 1.0.0
 *
 * @covers WordPoints_Top_Users_In_Period_Blocks_Query
 */
class WordPoints_Top_Users_In_Period_Blocks_Query_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test the query arg defaults.
	 *
	 * @since 1.0.0
	 */
	public function test_defaults() {

		$query = new WordPoints_Top_Users_In_Period_Blocks_Query();

		$this->assertSame( 'start_date', $query->get_arg( 'order_by' ) );
	}

	/**
	 * Test that query actually works.
	 *
	 * @since 1.0.0
	 */
	public function test_results() {

		global $wpdb;

		$block = array(
			'block_type'      => 'weekly',
			'start_date'      => '2017-03-21 10:25:32',
			'end_date'        => '2017-03-28 10:25:32',
			'query_signature' => '111111111111',
			'status'          => 'draft',
		);

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, $block
		);

		$block['id'] = (string) $wpdb->insert_id;

		$query = new WordPoints_Top_Users_In_Period_Blocks_Query();

		$results = $query->get();

		$this->assertCount( 1, $results );
		$this->assertSameProperties( (object) $block, $results[0] );
	}
}

// EOF
