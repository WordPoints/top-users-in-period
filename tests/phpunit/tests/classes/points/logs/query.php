<?php

/**
 * Test case for WordPoints_Top_Users_In_Period_Points_Logs_Query.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests WordPoints_Top_Users_In_Period_Points_Logs_Query.
 *
 * @since 1.0.0
 *
 * @covers WordPoints_Top_Users_In_Period_Points_Logs_Query
 */
class WordPoints_Top_Users_In_Period_Points_Logs_Query_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test the query arg defaults.
	 *
	 * @since 1.0.0
	 */
	public function test_defaults() {

		$query = new WordPoints_Top_Users_In_Period_Points_Logs_Query();

		$this->assertSame( 'total', $query->get_arg( 'order_by' ) );
	}

	/**
	 * Test that the fields query arg is not supported.
	 *
	 * @since 1.0.0
	 *
	 * @expectedIncorrectUsage WordPoints_Top_Users_In_Period_Points_Logs_Query::prepare_select
	 */
	public function test_fields_not_supported() {

		$query = new WordPoints_Top_Users_In_Period_Points_Logs_Query(
			array( 'fields' => 'total' )
		);

		$query->get_sql();
	}

	/**
	 * Test that the fields query arg is not supported.
	 *
	 * @since 1.0.0
	 *
	 * @expectedIncorrectUsage WordPoints_Top_Users_In_Period_Points_Logs_Query::prepare_select
	 */
	public function test_fields_not_supported_set_args() {

		$query = new WordPoints_Top_Users_In_Period_Points_Logs_Query();
		$query->set_args( array( 'fields' => 'total' ) );

		$query->get_sql();
	}

	/**
	 * Test that the results are grouped by user.
	 *
	 * @since 1.0.0
	 */
	public function test_results() {

		$user_id_1 = $this->factory->user->create();
		$this->factory->wordpoints->points_log->create(
			array( 'user_id' => $user_id_1, 'points' => 50 )
		);

		$user_id_2 = $this->factory->user->create();
		$this->factory->wordpoints->points_log->create(
			array( 'user_id' => $user_id_2, 'points' => 6 )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'user_id' => $user_id_1, 'points' => 4 )
		);

		$query = new WordPoints_Top_Users_In_Period_Points_Logs_Query();

		$results = $query->get();

		$this->assertCount( 2, $results );
		$this->assertSameProperties(
			(object) array( 'total' => '54', 'user_id' => (string) $user_id_1 )
			, $results[0]
		);
		$this->assertSameProperties(
			(object) array( 'total' => '6', 'user_id' => (string) $user_id_2 )
			, $results[1]
		);
	}
}

// EOF
