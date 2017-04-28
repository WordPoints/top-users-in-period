<?php

/**
 * Query cache flusher class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Flushes the query caches.
 *
 * The queries that have a start date but no specified end date (i.e., they continue
 * to the present), are kept track of so that the cache can be invalidated when a new
 * points transaction takes place. The job of this class is to flush those caches as
 * needed based on a transaction.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Query_Cache_Flusher {

	/**
	 * The query caches class registry.
	 *
	 * @since 1.0.0
	 *
	 * @var WordPoints_Class_Registry
	 */
	protected $query_caches;

	/**
	 * Flushes the query caches.
	 *
	 * @since 1.0.0
	 */
	public function flush() {

		$this->query_caches = wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' );

		$this->flush_caches();

		if ( is_multisite() ) {
			$this->flush_caches( true );
		}
	}

	/**
	 * Flushes a given set of queries' caches.
	 *
	 * @since  1.0.0
	 *
	 * @param bool $network_wide Whether to flush the network-wide caches or not.
	 */
	protected function flush_caches( $network_wide = false ) {

		$caches = new WordPoints_Top_Users_In_Period_Query_Cache_Index( $network_wide );

		foreach ( $caches->get() as $query ) {
			foreach ( $query['caches'] as $type => $dates ) {

				if ( ! $this->query_caches->is_registered( $type ) ) {
					continue;
				}

				foreach ( $dates as $date ) {

					$args = $query['args'];
					$args['start_timestamp'] = $date;

					/** @var WordPoints_Top_Users_In_Period_Query_CacheI $cache */
					$cache = $this->query_caches->get( $type, array( $args, $network_wide ) );
					$cache->delete();
				}
			}
		}
	}
}

// EOF
