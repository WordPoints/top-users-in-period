<?php

/**
 * A test case for the widget.
 *
 * @package WordPoints_Top_Users_In_Period\PHPUnit\Tests
 * @since 1.0.0
 */

/**
 * Test the widget.
 *
 * @since 1.0.0
 *
 * @group widgets
 *
 * @covers WordPoints_Top_Users_In_Period_Widget
 */
class WordPoints_Top_Users_In_Period_Widget_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 1.0.0
	 */
	protected $widget_class = 'WordPoints_Top_Users_In_Period_Widget';

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
	 * Test that and invalid from date results in an error.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_from() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertWordPointsWidgetError(
			$this->get_widget_html( array( 'from' => 'invalid' ) )
		);

		// It should not error when the from date is empty.
		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'from' => '' ) )
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

		$xpath = $this->get_widget_xpath( array( 'from' => '2017-04-20' ) );

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that and invalid from time results in an error.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_from_time() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertWordPointsWidgetError(
			$this->get_widget_html( array( 'from_time' => 'invalid' ) )
		);

		$this->assertWordPointsWidgetError(
			$this->get_widget_html( array( 'from_time' => '2017-04-31' ) )
		);

		// It should not error when the from date is empty.
		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'from_time' => '' ) )
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

		$xpath = $this->get_widget_xpath( array( 'from_time' => '05:00' ) );

		$this->assertSame( 2, $xpath->query( '//tbody/tr' )->length );
	}

	/**
	 * Test that and invalid to date results in an error.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_to() {

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertWordPointsWidgetError(
			$this->get_widget_html( array( 'to' => 'invalid' ) )
		);

		// It should not error when the to date is empty.
		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'to' => '' ) )
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

		$xpath = $this->get_widget_xpath(
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

		$this->give_current_user_caps( 'edit_theme_options' );

		$this->assertWordPointsWidgetError(
			$this->get_widget_html(
				array( 'to' => '2017-04-21', 'to_time' => 'invalid' )
			)
		);

		// It should not error when the to date is empty.
		$this->assertNotWordPointsWidgetError(
			$this->get_widget_html( array( 'to_time' => '' ) )
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

		$xpath = $this->get_widget_xpath( array( 'to_time' => '05:59:59' ) );

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
