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
	 * Args to flush against.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * Whether to only flush the caches with results matching the user ID being
	 * flushed against.
	 *
	 * @since 1.0.1
	 *
	 * @var bool
	 */
	protected $only_matching_cache;

	/**
	 * @since 1.0.0
	 *
	 * @param array $args Query arg values to flush against. Only caches for queries
	 *                    whose args would match these values will be flushed.
	 */
	public function __construct( array $args = array() ) {

		$this->args = $args;
	}

	/**
	 * Flushes the query caches.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 - The $flush_ended arg was added.
	 *              - The $only_matching_cache arg was added.
	 *
	 * @param bool $flush_ended         Whether to flush caches for ended periods.
	 * @param bool $only_matching_cache Whether to only flush caches if the results
	 *                                  match the user that is being flushed against.
	 */
	public function flush( $flush_ended = false, $only_matching_cache = false ) {

		$this->query_caches = wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' );

		$this->only_matching_cache = $only_matching_cache;

		$this->flush_caches( false, $flush_ended );

		if ( is_multisite() ) {
			$this->flush_caches( true, $flush_ended );
		}
	}

	/**
	 * Flushes a given set of queries' caches.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 The $flush_ended arg was added.
	 *
	 * @param bool $network_wide Whether to flush the network-wide caches or not.
	 * @param bool $flush_ended  Whether to flush caches for ended periods.
	 */
	protected function flush_caches( $network_wide = false, $flush_ended = false ) {

		$now = time();

		$caches = new WordPoints_Top_Users_In_Period_Query_Cache_Index( $network_wide );

		foreach ( $caches->get() as $query ) {

			if ( ! $this->should_flush_query( $query['args'] ) ) {
				continue;
			}

			foreach ( $query['caches'] as $type => $dates ) {

				if ( ! $this->query_caches->is_registered( $type ) ) {
					continue;
				}

				foreach ( $dates as $start_date => $end_dates ) {

					$args = $query['args'];
					$args['start_timestamp'] = $start_date;

					foreach ( $end_dates as $end_date => $unused ) {

						if ( 'none' !== $end_date ) {

							$args['end_timestamp'] = $end_date;

							// If this query's period has ended, and we're not
							// flushing caches for past periods, skip it.
							if ( ! $flush_ended && $end_date < $now ) {
								continue;
							}
						}

						/** @var WordPoints_Top_Users_In_Period_Query_CacheI $cache */
						$cache = $this->query_caches->get( $type, array( $args, $network_wide ) );

						if ( $this->should_flush_cache( $cache ) ) {
							$cache->delete();
						}
					}
				}
			}

		} // End foreach ( query cache ).
	}

	/**
	 * Checks if a given query's cache should be flushed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The query args to check based on.
	 *
	 * @return bool Whether this query's cache should be flushed.
	 */
	protected function should_flush_query( $args ) {

		$arg_slugs = array(
			'points_type',
			'log_type',
			'user_id',
			'blog_id',
			'site_id',
		);

		foreach ( $arg_slugs as $arg ) {

			if ( isset( $this->args[ $arg ] ) ) {
				if ( ! $this->query_arg_matches_value( $args, $arg ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks if a query arg matches a value that we are flushing relative to.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args The query args.
	 * @param string $arg  The slug of the arg to check.
	 *
	 * @return bool Whether the arg matches the value this flusher was passed.
	 */
	protected function query_arg_matches_value( $args, $arg ) {

		if ( isset( $args[ $arg ] ) ) {

			if (
				! isset( $args[ "{$arg}__compare" ] )
				|| '=' === $args[ "{$arg}__compare" ]
			) {

				if ( $args[ $arg ] === $this->args[ $arg ] ) {
					return true;
				}

			} else {
				return true;
			}

		} elseif ( isset( $args[ "{$arg}__in" ] ) ) {

			if ( in_array( $this->args[ $arg ], $args[ "{$arg}__in" ], true ) ) {
				return true;
			}

		} elseif ( isset( $args[ "{$arg}__not_in" ] ) ) {

			if ( ! in_array( $this->args[ $arg ], $args[ "{$arg}__not_in" ], true ) ) {
				return true;
			}

		} else {

			// If the arg isn't referenced in the query at all, then there are no
			// restrictions, so it matches.
			return true;
		}

		return false;
	}

	/**
	 * Checks if the cache should be flushed.
	 *
	 * @since 1.0.1
	 *
	 * @param WordPoints_Top_Users_In_Period_Query_CacheI $cache The cache.
	 *
	 * @return bool Whether the cache should be flushed.
	 */
	protected function should_flush_cache(
		WordPoints_Top_Users_In_Period_Query_CacheI $cache
	) {

		if ( $this->only_matching_cache && isset( $this->args['user_id'] ) ) {
			if ( ! $this->is_user_in_cache( $cache, (int) $this->args['user_id'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if a user is in the cache.
	 *
	 * @since 1.0.1
	 *
	 * @param WordPoints_Top_Users_In_Period_Query_CacheI $cache   The cache.
	 * @param int                                         $user_id The user ID.
	 *
	 * @return bool Whether the user is in the cache.
	 */
	protected function is_user_in_cache(
		WordPoints_Top_Users_In_Period_Query_CacheI $cache,
		$user_id
	) {

		foreach ( $cache->get() as $result ) {
			if ( (int) $result->user_id === $user_id ) {
				return true;
			}
		}

		return false;
	}
}

// EOF
