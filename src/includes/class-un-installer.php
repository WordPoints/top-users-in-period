<?php

/**
 * Un/installer class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Installs, uninstalls, and updates the module.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Un_Installer
	extends WordPoints_Un_Installer_Base {

	/**
	 * @since 1.0.0
	 */
	protected $type = 'module';

	/**
	 * @since 1.0.0
	 */
	protected $schema = array(
		'global' => array(
			'tables' => array(
				'wordpoints_top_users_in_period_query_signatures' => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					signature CHAR(64) NOT NULL,
					query_args TEXT NOT NULL,
					PRIMARY KEY  (id),
					UNIQUE KEY (signature)',
				'wordpoints_top_users_in_period_blocks' => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					block_type VARCHAR(32) NOT NULL,
					start_date DATETIME NOT NULL,
					end_date DATETIME NOT NULL,
					query_signature_id BIGINT(20) UNSIGNED NOT NULL,
					status VARCHAR(10) NOT NULL,
					PRIMARY KEY  (id),
					UNIQUE KEY block_signature (block_type,query_signature_id,start_date)',
				'wordpoints_top_users_in_period_block_logs' => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					block_id BIGINT(20) UNSIGNED NOT NULL,
					user_id BIGINT(20) UNSIGNED NOT NULL,
					points BIGINT(20) NOT NULL,
					PRIMARY KEY  (id),
					UNIQUE KEY block_user_id (user_id,block_id)',
			),
		),
	);

	/**
	 * @since 1.0.1
	 */
	protected $updates = array(
		'1.0.1' => array( 'single' => true, 'site' => true, 'network' => true ),
	);

	/**
	 * @since 1.0.1
	 */
	protected function before_update() {

		parent::before_update();

		if ( '1.0.0' === $this->updating_from ) {
			$this->map_shortcuts( 'schema' );
		}
	}

	/**
	 * Updates the network to 1.0.1.
	 *
	 * @since 1.0.1
	 */
	protected function update_network_to_1_0_1() {
		$this->flush_caches_for_1_0_1();
		$this->update_db_tables_to_1_0_1();
	}

	/**
	 * Updates a site on the network to 1.0.1.
	 *
	 * @since 1.0.1
	 */
	protected function update_site_to_1_0_1() {
		$this->flush_caches_for_1_0_1();
	}

	/**
	 * Updates a single site to 1.0.1.
	 *
	 * @since 1.0.1
	 */
	protected function update_single_to_1_0_1() {
		$this->flush_caches_for_1_0_1();
		$this->update_db_tables_to_1_0_1();
	}

	/**
	 * Flushes the caches for 1.0.1, in case they contain deleted users.
	 *
	 * @since 1.0.1
	 */
	protected function flush_caches_for_1_0_1() {

		if ( 'network' === $this->context ) {

			$this->uninstall_network_option(
				'_site_transient_wordpoints_top_users_in_period_query_%'
			);

		} else {

			$this->uninstall_option(
				'_transient_wordpoints_top_users_in_period_query_%'
			);

			if ( wp_using_ext_object_cache() ) {
				wp_cache_flush();
			}
		}

		wordpoints_delete_maybe_network_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, 'network' === $this->context
		);
	}

	/**
	 * Updates the database tables to 1.0.1.
	 *
	 * @since 1.0.1
	 */
	protected function update_db_tables_to_1_0_1() {

		$this->uninstall_table( 'wordpoints_top_users_in_period_blocks' );
		$this->uninstall_table( 'wordpoints_top_users_in_period_block_logs' );

		$this->install_db_schema();
	}
}

return 'WordPoints_Top_Users_In_Period_Un_Installer';

// EOF
