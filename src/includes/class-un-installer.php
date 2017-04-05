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
					KEY block_signature (block_type,query_signature(8))',
				'wordpoints_top_users_in_period_block_logs' => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					block_id BIGINT(20) UNSIGNED NOT NULL,
					user_id BIGINT(20) UNSIGNED NOT NULL,
					points BIGINT(20) UNSIGNED NOT NULL,
					PRIMARY KEY  (id),
					KEY block_user_id (user_id,block_id)',
			),
		),
	);
}

return 'WordPoints_Top_Users_In_Period_Un_Installer';

// EOF
