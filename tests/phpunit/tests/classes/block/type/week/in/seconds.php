<?php

/**
 * Test case for WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds.
 *
 * @since 1.0.0
 *
 * @covers WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds
 */
class WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting the block type slug.
	 *
	 * @since 1.0.0
	 */
	public function test_get_slug() {

		$block_type = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$this->assertSame( 'test', $block_type->get_slug() );
	}

	/**
	 * Tests getting the start and end times for a particular block.
	 *
	 * @since 1.0.0
	 */
	public function test_get_block_info() {

		$block_type = new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
			'test'
		);

		$timestamp = 1490908830;

		$info = $block_type->get_block_info( $timestamp );

		$this->assertSame( 1490832000, $info['start'] );
		$this->assertSame( 1491436800, $info['end'] );
	}
}

// EOF
