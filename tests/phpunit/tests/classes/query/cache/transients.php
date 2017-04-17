<?php

/**
 * Test case for WordPoints_Top_Users_In_Period_Query_Cache_Transients.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Tests WordPoints_Top_Users_In_Period_Query_Cache_Transients.
 *
 * @since 1.0.0
 *
 * @covers WordPoints_Top_Users_In_Period_Query_Cache_Transients
 */
class WordPoints_Top_Users_In_Period_Query_Cache_Transients_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Tests getting and setting the cache value.
	 *
	 * @since 1.0.0
	 */
	public function test_get() {

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
			'test'
		);

		$value = array( 'test' );

		$this->assertTrue( $cache->set( $value ) );

		$this->assertSame( $value, $cache->get() );

		$this->assertTrue( $cache->delete() );

		$this->assertFalse( $cache->get() );
	}

	/**
	 * Tests that order of the query args doesn't affect the query signature.
	 *
	 * @since 1.0.0
	 */
	public function test_query_arg_order() {

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
			'test'
			, array( 'a' => 1, 'b' => 2 )
		);

		$value = array( 'test' );

		$this->assertTrue( $cache->set( $value ) );

		$this->assertSame( $value, $cache->get() );

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
			'test'
			, array( 'b' => 2, 'a' => 1 )
		);

		$this->assertSame( $value, $cache->get() );
	}

	/**
	 * Tests that network transients are used when appropriate.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 *
	 * @dataProvider data_provider_network_queries
	 */
	public function test_network_transients( $args, $signature ) {

		$transient_name = "wordpoints_top_users_in_period_query_{$signature}";

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
			'test'
			, $args
		);

		$value = array( 'test' );

		$this->assertTrue( $cache->set( $value ) );

		$this->assertSame( $value, $cache->get() );
		$this->assertSame( $value, get_site_transient( $transient_name ) );
		$this->assertFalse( get_transient( $transient_name ) );

		$cache->delete();

		$this->assertFalse( get_site_transient( $transient_name ) );
	}

	/**
	 * Data provider for network queries.
	 *
	 * @since 1.0.0
	 *
	 * @return array Network queries.
	 */
	public function data_provider_network_queries() {
		return array(
			'no_blog_id' => array(
				array(),
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945',
			),
			'different_blog_id' => array(
				array( 'blog_id' => 2 ),
				'32359a140e2c18c16533860d2a29715eb0af87b4af25f25477420ed30bd3b36a',
			),
			'blog_id_not_=' => array(
				array( 'blog_id' => 1, 'blog_id__compare' => '!=' ),
				'17c9321bb65e3af0f8439ac01aabd5043ea0e6e986a80660b198e420c10202c7',
			),
		);
	}

	/**
	 * Tests that network transients are used when appropriate.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress multisite
	 *
	 * @dataProvider data_provider_per_site_queries
	 */
	public function test_non_network_transients( $args, $signature ) {

		$transient_name = "wordpoints_top_users_in_period_query_{$signature}";

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
			'test'
			, $args
		);

		$value = array( 'test' );

		$this->assertTrue( $cache->set( $value ) );

		$this->assertSame( $value, $cache->get() );
		$this->assertSame( $value, get_transient( $transient_name ) );
		$this->assertFalse( get_site_transient( $transient_name ) );

		$cache->delete();

		$this->assertFalse( get_transient( $transient_name ) );
	}

	/**
	 * Data provider for per-site queries.
	 *
	 * @since 1.0.0
	 *
	 * @return array Per-site queries.
	 */
	public function data_provider_per_site_queries() {
		return array(
			'blog_id' => array(
				array( 'blog_id' => 1 ),
				'9db104c00a85d8cd2216f898a44a7f45a67b94de60dfe56eba7bef5a77f2ef67',
			),
			'blog_id_=' => array(
				array( 'blog_id' => 1, 'blog_id__compare' => '=' ),
				'5afa76bba28da6831d7b824b55e3bf511269804d567d1770c1a0646ce229599b',
			),
		);
	}

	/**
	 * Tests that network transients are used when appropriate.
	 *
	 * @since 1.0.0
	 *
	 * @requires WordPress !multisite
	 *
	 * @dataProvider data_provider_network_queries
	 */
	public function test_non_network_transients_not_multisite( $args, $signature ) {

		$transient_name = "wordpoints_top_users_in_period_query_{$signature}";

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
			'test'
			, $args
		);

		$value = array( 'test' );

		$this->assertTrue( $cache->set( $value ) );

		$this->assertSame( $value, $cache->get() );
		$this->assertSame( $value, get_transient( $transient_name ) );
		$this->assertFalse( get_site_transient( $transient_name ) );

		$cache->delete();

		$this->assertFalse( get_transient( $transient_name ) );
	}
}

// EOF
