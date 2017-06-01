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
			'block_type'         => 'weekly',
			'start_date'         => '2017-03-21 10:25:32',
			'end_date'           => '2017-03-28 10:25:32',
			'query_signature_id' => '1',
			'status'             => 'draft',
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

	/**
	 * Test that converts a query signature to a signature ID.
	 *
	 * @since 1.0.1
	 *
	 * @expectedDeprecated WordPoints_Top_Users_In_Period_Blocks_Query::__construct
	 */
	public function test_query_signature_converted_to_id() {

		global $wpdb;

		$query_signature = '1111111111111111';

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures'
			, array(
				'signature' => $query_signature,
				'query_args' => '',
			)
		);

		$query_signature_id = $wpdb->insert_id;

		$block = array(
			'block_type'         => 'weekly',
			'start_date'         => '2017-03-21 10:25:32',
			'end_date'           => '2017-03-28 10:25:32',
			'query_signature_id' => (string) $query_signature_id,
			'status'             => 'draft',
		);

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, $block
		);

		$block['id'] = (string) $wpdb->insert_id;

		// Add a block for a different query signature.
		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, array(
				'block_type'         => 'weekly',
				'start_date'         => '2017-03-21 10:25:32',
				'end_date'           => '2017-03-28 10:25:32',
				'query_signature_id' => $query_signature_id + 1,
				'status'             => 'draft',
			)
		);

		$query = new WordPoints_Top_Users_In_Period_Blocks_Query(
			array( 'query_signature' => $query_signature )
		);

		$results = $query->get();

		$this->assertCount( 1, $results );
		$this->assertSameProperties( (object) $block, $results[0] );
	}
}

// EOF
