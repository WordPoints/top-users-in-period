<?php

/**
 * Query cache index class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Manages an index of the caches for queries.
 *
 * Only the queries that are open-ended are currently managed by this index, since
 * they are the only ones that need to be invalidated when new transactions occur.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Query_Cache_Index {

	/**
	 * Whether this is the index for network-wide queries.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $network_wide;

	/**
	 * @since 1.0.0
	 *
	 * @param bool $network_wide Whether the index should be for network-wide
	 *                           queries, or just the current site.
	 */
	public function __construct( $network_wide = false ) {

		$this->network_wide = $network_wide;
	}

	/**
	 * Gets all of the query caches in the index.
	 *
	 * @since 1.0.0
	 *
	 * @return array The query caches.
	 */
	public function get() {

		return wordpoints_get_maybe_network_array_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, $this->network_wide
		);
	}

	/**
	 * Adds a query to the index.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type            The type of cache used for the query.
	 * @param array  $query_args      The args for the query.
	 * @param int    $start_timestamp The start time of the query.
	 */
	public function add( $type, $query_args, $start_timestamp ) {

		ksort( $query_args );

		$query_signature = wordpoints_hash( wp_json_encode( $query_args ) );

		$queries = $this->get();
		$queries[ $query_signature ]['args'] = $query_args;
		$queries[ $query_signature ]['caches'][ $type ][ $start_timestamp ] = true;

		$this->save( $queries );
	}

	/**
	 * Saves the query index.
	 *
	 * @since 1.0.0
	 *
	 * @param array $queries The cached queries.
	 */
	protected function save( array $queries ) {

		wordpoints_update_maybe_network_option(
			'wordpoints_top_users_in_period_query_cache_index'
			, $queries
			, $this->network_wide
		);
	}

}

// EOF
