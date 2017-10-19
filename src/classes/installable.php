<?php

/**
 * Installable class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.2
 */

/**
 * The installable object for this extension.
 *
 * @since 1.0.2
 */
class WordPoints_Top_Users_In_Period_Installable
	extends WordPoints_Installable_Extension {

	/**
	 * @since 1.0.2
	 */
	protected function get_db_tables() {

		return array(
			'global' => array(
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
		);
	}

	/**
	 * @since 1.0.2
	 */
	public function get_update_routine_factories() {

		$factories = parent::get_update_routine_factories();

		// v1.0.1.
		$updates = array();

		$options = new WordPoints_Uninstaller_Factory_Options(
			array(
				'local' => array(
					'_transient_wordpoints_top_users_in_period_query_%',
				),
				'network' => array(
					'_site_transient_wordpoints_top_users_in_period_query_%',
				),
				'universal' => array(
					'wordpoints_top_users_in_period_query_cache_index',
				),
			)
		);

		$updates['single']  = $options->get_for_single();
		$updates['site']    = $options->get_for_site();
		$updates['network'] = $options->get_for_network();

		if ( wp_using_ext_object_cache() ) {
			$updates['local'][] = array(
				'class' => 'WordPoints_Uninstaller_Callback',
				'args'  => array( 'wp_cache_flush', array( null ) ),
			);
		}

		$updates['global'][] = array(
			'class' => 'WordPoints_Uninstaller_DB_Tables',
			'args'  => array(
				array(
					'wordpoints_top_users_in_period_blocks',
					'wordpoints_top_users_in_period_block_logs',
				),
			),
		);

		$tables = $this->get_db_tables_install_routines();

		$updates['single'][]  = $tables['single'][0];
		$updates['site'][]    = $tables['site'][0];
		$updates['network'][] = $tables['network'][0];

		$factories[] = new WordPoints_Updater_Factory( '1.0.1', $updates );

		return $factories;
	}
}

// EOF
