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
				'wordpoints_top_users_in_period_blocks' => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					block_type VARCHAR(32) NOT NULL,
					start_date DATETIME NOT NULL,
					end_date DATETIME NOT NULL,
					query_signature CHAR(64) NOT NULL,
					status VARCHAR(10) NOT NULL,
					PRIMARY KEY  (id),
					UNIQUE KEY block_signature (block_type,query_signature,start_date)',
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
	 * Updates the network to 1.0.1.
	 *
	 * @since 1.0.1
	 */
	protected function update_network_to_1_0_1() {
		$this->update_cache_index_to_1_0_1();
	}

	/**
	 * Updates a site on the network to 1.0.1.
	 *
	 * @since 1.0.1
	 */
	protected function update_site_to_1_0_1() {
		$this->update_cache_index_to_1_0_1();
	}

	/**
	 * Updates a single site to 1.0.1.
	 *
	 * @since 1.0.1
	 */
	protected function update_single_to_1_0_1() {
		$this->update_cache_index_to_1_0_1();
	}

	/**
	 * Updates the cache index for 1.0.1.
	 *
	 * @since 1.0.1
	 */
	protected function update_cache_index_to_1_0_1() {

		$caches = wordpoints_get_maybe_network_array_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, 'network' === $this->context
		);

		// Each start date now holds an array that is a list of end dates.
		foreach ( $caches as $key => $query ) {
			foreach ( $query['caches'] as $type => $dates ) {
				foreach ( $dates as $start_date => $unused ) {
					$caches[ $key ]['caches'][ $type ][ $start_date ] = array( 'none' => true );
				}
			}
		}

		wordpoints_update_maybe_network_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, $caches
			, 'network' === $this->context
		);
	}
}

return 'WordPoints_Top_Users_In_Period_Un_Installer';

// EOF
