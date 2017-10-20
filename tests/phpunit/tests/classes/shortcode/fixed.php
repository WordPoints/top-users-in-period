<?php

/**
 * A test case for the fixed period shortcode.
 *
 * @package WordPoints_Top_Users_In_Period\PHPUnit\Tests
 * @since 1.0.0
 */

/**
 * Tests the fixed period shortcode.
 *
 * @since 1.0.0
 *
 * @group shortcodes
 *
 * @covers WordPoints_Top_Users_In_Period_Shortcode_Fixed
 */
class WordPoints_Top_Users_In_Period_Shortcode_Fixed_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 1.0.0
	 */
	protected $shortcode = 'wordpoints_top_users_in_fixed_period';

	/**
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		wordpoints_update_maybe_network_option(
			'wordpoints_default_points_type'
			, 'points'
		);
	}

	/**
	 * Tests that the excluded users are excluded.
	 *
	 * @since 1.0.0
	 */
	public function test_excludes_excluded_users() {

		$user_id = $this->factory->user->create();

		wordpoints_update_maybe_network_option(
			'wordpoints_excluded_users'
			, array( $user_id )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'user_id' => $user_id )
		);

		$this->factory->wordpoints->points_log->create();

		$xpath = $this->get_shortcode_xpath( array( 'points_type' => 'points' ) );

		$this->assertSame( 1, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test the num_users setting.
	 *
	 * @since 1.0.0
	 */
	public function test_users_setting() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		$xpath = $this->get_shortcode_xpath(
			array( 'points_type' => 'points', 'users' => 2 )
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that and invalid from date results in an error.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_from() {

		$this->give_current_user_caps( 'manage_options' );

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'from' => 'invalid' ) )
		);

		// It should not error when the from date is empty.
		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'from' => '' ) )
		);
	}

	/**
	 * Tests the from date.
	 *
	 * @since 1.0.0
	 */
	public function test_from() {

		$this->factory->wordpoints->points_log->create(
			array( 'date' => '2017-04-19 00:00:00' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => '2017-04-20 00:00:00' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => '2017-04-21 00:00:00' )
		);

		$xpath = $this->get_shortcode_xpath( array( 'from' => '2017-04-20' ) );

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that and invalid from time results in an error.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_from_time() {

		$this->give_current_user_caps( 'manage_options' );

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'from_time' => 'invalid' ) )
		);

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'from_time' => '2017-04-31' ) )
		);
	}

	/**
	 * Tests the from time.
	 *
	 * @since 1.0.0
	 */
	public function test_from_time() {

		$date = current_time( 'Y-m-d' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date . ' 00:00:00' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date . ' 05:00:00' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date . ' 06:00:00' )
		);

		$xpath = $this->get_shortcode_xpath( array( 'from_time' => '05:00' ) );

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that and invalid to date results in an error.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_to() {

		$this->give_current_user_caps( 'manage_options' );

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'to' => 'invalid' ) )
		);

		// It should not error when the to date is empty.
		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'to' => '' ) )
		);
	}

	/**
	 * Tests the to date.
	 *
	 * @since 1.0.0
	 */
	public function test_to() {

		$this->factory->wordpoints->points_log->create(
			array( 'date' => '2017-04-19 00:00:00' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => '2017-04-20 00:00:00' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => '2017-04-21 00:00:00' )
		);

		$xpath = $this->get_shortcode_xpath(
			array( 'from' => '2017-03-20', 'to' => '2017-04-20' )
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that and invalid to time results in an error.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_to_time() {

		$this->give_current_user_caps( 'manage_options' );

		$this->assertWordPointsShortcodeError(
			$this->do_shortcode(
				$this->shortcode
				, array( 'to' => '2017-04-21', 'to_time' => 'invalid' )
			)
		);

		// It should not error when the to date is empty.
		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'to_time' => '' ) )
		);
	}

	/**
	 * Tests the to time.
	 *
	 * @since 1.0.0
	 */
	public function test_to_time() {

		$date = current_time( 'Y-m-d' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date . ' 23:59:59' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date . ' 05:59:59' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date . ' 06:59:59' )
		);

		$xpath = $this->get_shortcode_xpath( array( 'to_time' => '05:59:59' ) );

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}
}

// EOF
