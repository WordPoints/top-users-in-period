<?php

/**
 * A test case for the dynamic widget.
 *
 * @package WordPoints_Top_Users_In_Period\PHPUnit\Tests
 * @since 1.0.0
 */

/**
 * Test the dynamic widget.
 *
 * @since 1.0.0
 *
 * @group widgets
 *
 * @covers WordPoints_Top_Users_In_Period_Widget_Dynamic
 */
class WordPoints_Top_Users_In_Period_Widget_Dynamic_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 1.0.0
	 */
	protected $widget_class = 'WordPoints_Top_Users_In_Period_Widget_Dynamic';

	/**
	 * Test that and invalid points_type setting results in an error.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_points_type_setting() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertWordPointsWidgetError(
			$this->get_widget_html( array( 'points_type' => 'invalid' ) )
		);

		// It should not error when the points type is empty.
		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'points_type' => '' ) )
		);
	}

	/**
	 * Test the default behaviour of the widget.
	 *
	 * @since 1.0.0
	 */
	public function test_defaults() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		$xpath = $this->get_widget_xpath( array( 'points_type' => 'points' ) );

		$this->assertSame( 3, $xpath->query( '//tbody/tr' )->length );
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

		$xpath = $this->get_widget_xpath( array( 'points_type' => 'points' ) );

		$this->assertSame( 1, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test the num_users setting.
	 *
	 * @since 1.0.0
	 */
	public function test_num_users_setting() {

		$this->factory->wordpoints->points_log->create_many( 4 );

		$xpath = $this->get_widget_xpath(
			array( 'points_type' => 'points', 'num_users' => 2 )
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

		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'length' => 'invalid' ) )
		);

		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'length' => '' ) )
		);
	}

	/**
	 * Test that and invalid period units results in the default being used.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_units() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'units' => 'invalid' ) )
		);

		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'units' => '' ) )
		);
	}

	/**
	 * Test that and invalid period relative results in the default being used.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_relative() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'relative' => 'invalid' ) )
		);

		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'relative' => '' ) )
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

		$xpath = $this->get_widget_xpath(
			array( 'length_in_units' => 30, 'units' => 'seconds' )
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

		$xpath = $this->get_widget_xpath(
			array( 'length_in_units' => 5, 'units' => 'minutes' )
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

		$xpath = $this->get_widget_xpath(
			array( 'length_in_units' => 2, 'units' => 'hours' )
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

		$xpath = $this->get_widget_xpath( array( 'length_in_units' => 2 ) );

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

		$xpath = $this->get_widget_xpath(
			array( 'length_in_units' => 2, 'units' => 'weeks' )
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

		$xpath = $this->get_widget_xpath(
			array( 'length_in_units' => 2, 'units' => 'months' )
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

		$xpath = $this->get_widget_xpath(
			array(
				'length_in_units' => 30,
				'units'           => 'seconds',
				'relative'        => 'calendar',
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

		$xpath = $this->get_widget_xpath(
			array(
				'length_in_units' => 5,
				'units'           => 'minutes',
				'relative'        => 'calendar',
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

		$xpath = $this->get_widget_xpath(
			array(
				'length_in_units' => 2,
				'units'           => 'hours',
				'relative'        => 'calendar',
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

		$xpath = $this->get_widget_xpath(
			array(
				'length_in_units' => 2,
				'units'           => 'days',
				'relative'        => 'calendar',
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

		$xpath = $this->get_widget_xpath(
			array(
				'length_in_units' => 2,
				'units'           => 'weeks',
				'relative'        => 'calendar',
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

		$xpath = $this->get_widget_xpath(
			array(
				'length_in_units' => 2,
				'units'           => 'weeks',
				'relative'        => 'calendar',
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

		$xpath = $this->get_widget_xpath(
			array(
				'length_in_units' => 2,
				'units'           => 'months',
				'relative'        => 'calendar',
			)
		);

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test the update() method.
	 *
	 * @since 1.0.0
	 */
	public function test_update_method() {

		/** @var WP_Widget $widget */
		$widget = new $this->widget_class;

		$sanitized = $widget->update(
			array(
				'title'       => '<p>Title</p>',
				'num_users'   => '5dd',
				'points_type' => 'invalid',
			)
			, array()
		);

		$this->assertSame( 'Title', $sanitized['title'] );
		$this->assertSame( 3, $sanitized['num_users'] );
		$this->assertSame( 'points', $sanitized['points_type'] );
	}
}

// EOF
