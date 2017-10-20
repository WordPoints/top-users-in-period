<?php

/**
 * Test case for the update to 1.0.1.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.1
 */

/**
 * Tests updating to 1.0.1.
 *
 * @since 1.0.1
 *
 * @covers WordPoints_Top_Users_In_Period_Installable::get_update_routine_factories()
 */
class WordPoints_Top_Users_In_Period_Update_1_0_1_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 1.0.1
	 */
	protected $previous_version = '1.0.0';

	/**
	 * @since 1.0.1
	 */
	protected $wordpoints_extension = 'top-users-in-period';

	/**
	 * @since 1.0.1
	 */
	protected $backup_globals = array( '_wp_using_ext_object_cache' );

	/**
	 * Tests that the cache index is deleted.
	 *
	 * @since 1.0.1
	 */
	public function test_cache_index_deleted() {

		update_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => true ),
					),
				),
			)
		);

		$this->update_extension();

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index();

		$this->assertSame( array(), $index->get() );
	}

	/**
	 * Tests that the cache index is deleted.
	 *
	 * @since 1.0.1
	 *
	 * @requires WordPoints network-active
	 */
	public function test_cache_index_deleted_network() {

		update_site_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, array(
				'4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945' => array(
					'args'   => array(),
					'caches' => array(
						'test' => array( 5 => true ),
					),
				),
			)
		);

		$this->update_extension();

		$index = new WordPoints_Top_Users_In_Period_Query_Cache_Index( true );

		$this->assertSame( array(), $index->get() );
	}

	/**
	 * Tests that the caches are flushed.
	 *
	 * @since 1.0.1
	 */
	public function test_caches_flushed() {

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients( 'test' );
		$cache->set( array( 'test' ) );

		$this->update_extension();

		$this->assertFalse( $cache->get() );
	}

	/**
	 * Tests that the caches are flushed.
	 *
	 * @since 1.0.1
	 */
	public function test_caches_flushed_external_object_cache() {

		wp_using_ext_object_cache( true );

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients( 'test' );
		$cache->set( array( 'test' ) );

		$this->update_extension();

		$this->assertFalse( $cache->get() );
	}

	/**
	 * Tests that the network caches are flushed.
	 *
	 * @since 1.0.1
	 *
	 * @requires WordPress multisite
	 */
	public function test_network_caches_flushed() {

		$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
			'test'
			, array()
			, true
		);

		$cache->set( array( 'test' ) );

		$this->update_extension();

		$this->assertFalse( $cache->get() );
	}

	/**
	 * Tests that the database tables are updated.
	 *
	 * @since 1.0.1
	 */
	public function test_db_tables_updated() {

		global $wpdb;

		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );

		$wpdb->query( "DROP TABLE `{$wpdb->base_prefix}wordpoints_top_users_in_period_query_signatures`" );
		$wpdb->query( "DROP TABLE `{$wpdb->base_prefix}wordpoints_top_users_in_period_blocks`" );
		$wpdb->query( "DROP TABLE `{$wpdb->base_prefix}wordpoints_top_users_in_period_block_logs`" );

		$wpdb->query(
			"
				CREATE TABLE `{$wpdb->base_prefix}wordpoints_top_users_in_period_blocks` (
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					block_type VARCHAR(32) NOT NULL,
					start_date DATETIME NOT NULL,
					end_date DATETIME NOT NULL,
					query_signature CHAR(64) NOT NULL,
					status VARCHAR(10) NOT NULL,
					PRIMARY KEY  (id),
					UNIQUE KEY block_signature (block_type,query_signature,start_date)
				)
			"
		);

		$this->update_extension();

		$this->assertTableHasColumn(
			'query_signature_id'
			, "{$wpdb->base_prefix}wordpoints_top_users_in_period_blocks"
		);

		$this->assertTableHasNotColumn(
			'query_signature'
			, "{$wpdb->base_prefix}wordpoints_top_users_in_period_blocks"
		);

		$this->assertDBTableExists(
			"{$wpdb->base_prefix}wordpoints_top_users_in_period_query_signatures"
		);
	}
}

// EOF
