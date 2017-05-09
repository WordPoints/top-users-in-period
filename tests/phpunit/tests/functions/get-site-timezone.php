<?php

/**
 * Test case for the wordpoints_top_user_in_period_get_site_timezone() function.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests the wordpoints_top_user_in_period_get_site_timezone() function.
 *
 * @since 1.0.0
 *
 * @covers ::wordpoints_top_user_in_period_get_site_timezone
 */
class WordPoints_Top_Users_In_Period_Get_Site_Timezone_Function_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting the timezone when the 'timezone_string' option is set.
	 *
	 * @since 1.0.0
	 */
	public function test_timezone_string_set() {

		update_option( 'timezone_string', 'America/New_York' );

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$this->assertInstanceOf( 'DateTimeZone', $timezone );
		$this->assertSame( 'America/New_York', $timezone->getName() );
	}

	/**
	 * Tests getting the timezone when the 'timezone_string' option is invalid.
	 *
	 * @since 1.0.0
	 */
	public function test_timezone_string_invalid() {

		update_option( 'timezone_string', 'invalid' );

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$this->assertInstanceOf( 'DateTimeZone', $timezone );
		$this->assertSame( 'UTC', $timezone->getName() );
	}

	/**
	 * Tests getting the timezone when the 'gmt_offset' option is negative.
	 *
	 * @since 1.0.0
	 */
	public function test_using_offset_negative() {

		delete_option( 'timezone_string' );
		update_option( 'gmt_offset', -5 );

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$this->assertInstanceOf( 'DateTimeZone', $timezone );

		if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
			$this->assertSame( '-05:00', $timezone->getName() );
		} else {
			$this->assertSame( 'UTC', $timezone->getName() );
		}
	}

	/**
	 * Tests getting the timezone when the 'gmt_offset' option is positive.
	 *
	 * @since 1.0.0
	 */
	public function test_using_offset_positive() {

		delete_option( 'timezone_string' );
		update_option( 'gmt_offset', 7 );

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$this->assertInstanceOf( 'DateTimeZone', $timezone );

		if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
			$this->assertSame( '+07:00', $timezone->getName() );
		} else {
			$this->assertSame( 'UTC', $timezone->getName() );
		}
	}

	/**
	 * Tests getting the timezone when the 'gmt_offset' option is not an integer.
	 *
	 * @since 1.0.0
	 */
	public function test_using_offset_decimal() {

		delete_option( 'timezone_string' );
		update_option( 'gmt_offset', 7.25 );

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$this->assertInstanceOf( 'DateTimeZone', $timezone );

		if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
			$this->assertSame( '+07:15', $timezone->getName() );
		} else {
			$this->assertSame( 'UTC', $timezone->getName() );
		}
	}

	/**
	 * Tests getting the timezone when the 'gmt_offset' option is zero.
	 *
	 * @since 1.0.0
	 */
	public function test_using_offset_0() {

		delete_option( 'timezone_string' );
		update_option( 'gmt_offset', 0 );

		$timezone = wordpoints_top_users_in_period_get_site_timezone();

		$this->assertInstanceOf( 'DateTimeZone', $timezone );
		$this->assertSame( 'UTC', $timezone->getName() );
	}
}

// EOF
