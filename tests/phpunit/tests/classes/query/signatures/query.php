<?php

/**
 * Test case for WordPoints_Top_Users_In_Period_Query_Signatures_Query.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.1
 */

/**
 * Tests WordPoints_Top_Users_In_Period_Query_Signatures_Query.
 *
 * @since 1.0.1
 *
 * @covers WordPoints_Top_Users_In_Period_Query_Signatures_Query
 */
class WordPoints_Top_Users_In_Period_Query_Signatures_Query_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test the query arg defaults.
	 *
	 * @since 1.0.1
	 */
	public function test_defaults() {

		$query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query();

		$this->assertSame( 'id', $query->get_arg( 'order_by' ) );
	}

	/**
	 * Test that it retrieves the results correctly.
	 *
	 * @since 1.0.1
	 */
	public function test_results() {

		global $wpdb;

		$query_signature = array(
			'signature'  => '1111111111111111',
			'query_args' => '[]',
		);

		$wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures'
			, $query_signature
		);

		$query_signature['id'] = (string) $wpdb->insert_id;

		$query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query();

		$results = $query->get();

		$this->assertCount( 1, $results );
		$this->assertSameProperties( (object) $query_signature, $results[0] );
	}
}

// EOF
