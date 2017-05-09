<?php

/**
 * A test case for the dynamic shortcode.
 *
 * @package WordPoints_Top_Users_In_Period\PHPUnit\Tests
 * @since 1.0.0
 */

/**
 * Test the dynamic shortcode.
 *
 * @since 1.0.0
 *
 * @group shortcodes
 *
 * @covers WordPoints_Top_Users_In_Period_Shortcode_Dynamic
 */
class WordPoints_Top_Users_In_Period_Shortcode_Dynamic_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 1.0.0
	 */
	protected $shortcode = 'wordpoints_top_users_in_period';

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
	 * Test the users setting.
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
	 * Test that and invalid period length results in the default being used.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_length() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'length' => 'invalid' ) )
		);

		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'length' => '' ) )
		);
	}

	/**
	 * Test that and invalid period units results in the default being used.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_units() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'units' => 'invalid' ) )
		);

		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'units' => '' ) )
		);
	}

	/**
	 * Test that and invalid period relative_to results in the default being used.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_relative_to() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'relative_to' => 'invalid' ) )
		);

		$this->assertNotWordPointsShortcodeError(
			$this->do_shortcode( $this->shortcode, array( 'relative_to' => '' ) )
		);
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_seconds() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->modify( '-29 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array( 'length' => 30, 'units' => 'seconds' )
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_minutes() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->modify( '-5 minutes' );
		$date->setTime( $date->format( 'H' ), $date->format( 'i' ), 1 );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array( 'length' => 5, 'units' => 'minutes' )
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_hours() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( $date->format( 'H' ), $date->format( 'i' ), 1 );
		$date->modify( '-2 hours' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array( 'length' => 2, 'units' => 'hours' )
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_days() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( $date->format( 'H' ), 0, 1 );
		$date->modify( '-2 days' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath( array( 'length' => 2 ) );

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_weeks() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( 0, 0, 1 );
		$date->modify( '-2 weeks' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array( 'length' => 2, 'units' => 'weeks' )
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_months() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( 0, 0, 1 );
		$date->setDate(
			$date->format( 'Y' )
			, (int) $date->format( 'm' ) - 2
			, $date->format( 'd' )
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array( 'length' => 2, 'units' => 'months' )
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_seconds_calendar() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->modify( '-29 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array(
				'length'      => 30,
				'units'       => 'seconds',
				'relative_to' => 'calendar',
			)
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_minutes_calendar() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( $date->format( 'H' ), $date->format( 'i' ), 1 );
		$date->modify( '-4 minutes' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array(
				'length'      => 5,
				'units'       => 'minutes',
				'relative_to' => 'calendar',
			)
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_hours_calendar() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( $date->format( 'H' ), 0, 1 );
		$date->modify( '-1 hours' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array(
				'length'      => 2,
				'units'       => 'hours',
				'relative_to' => 'calendar',
			)
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_days_calendar() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->modify( '-1 days' );
		$date->setTime( 0, 0, 1 );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array(
				'length'      => 2,
				'units'       => 'days',
				'relative_to' => 'calendar',
			)
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_weeks_calendar() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( 0, 0, 1 );
		$date->setISODate(
			$date->format( 'Y' )
			, (int) $date->format( 'W' ) - 1
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array(
				'length'      => 2,
				'units'       => 'weeks',
				'relative_to' => 'calendar',
			)
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_weeks_calendar_starts_on_sunday() {

		update_option( 'start_of_week', 0 );

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( 0, 0, 1 );

		$weeks = 2;

		if ( '7' === $date->format( 'w' ) ) {
			$weeks = 1;
		}

		$date->setISODate(
			$date->format( 'Y' )
			, (int) $date->format( 'W' ) - $weeks
			, 7
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array(
				'length'      => 2,
				'units'       => 'weeks',
				'relative_to' => 'calendar',
			)
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Tests the period length.
	 *
	 * @since 1.0.0
	 */
	public function test_length_months_calendar() {

		$this->factory->wordpoints->points_log->create();

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$date = new DateTime( null, $timezone );
		$date->setTime( 0, 0, 1 );
		$date->setDate(
			$date->format( 'Y' )
			, (int) $date->format( 'm' ) - 1
			, 0
		);

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$date->modify( '-3 seconds' );

		$this->factory->wordpoints->points_log->create(
			array( 'date' => $date->format( 'Y-m-d H:i:s' ) )
		);

		$xpath = $this->get_shortcode_xpath(
			array(
				'length'      => 2,
				'units'       => 'months',
				'relative_to' => 'calendar',
			)
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}
}

// EOF
