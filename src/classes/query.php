<?php

/**
 * Query class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Queries for the top users in a given period.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Query
	extends WordPoints_Top_Users_In_Period_Points_Logs_Query {

	/**
	 * The date for the start of the period.
	 *
	 * @since 1.0.0
	 *
	 * @var DateTime
	 */
	protected $start_date;

	/**
	 * The date for the end of the period.
	 *
	 * @since 1.0.0
	 *
	 * @var DateTime
	 */
	protected $end_date;

	/**
	 * The Unix timestamp of the start date for the period this query is for.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $start_timestamp;

	/**
	 * The Unix timestamp of the end date for the period this query is for.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $end_timestamp;

	/**
	 * Start and end data info for the block during which the start date occurs.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $start_block_info;

	/**
	 * Start and end data info for the block during which the end date occurs.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $end_block_info;

	/**
	 * Whether an explicit end date was specified for this query.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $open_ended = false;

	/**
	 * The block type being used in the query.
	 *
	 * @since 1.0.0
	 *
	 * @var WordPoints_Top_Users_In_Period_Block_TypeI
	 */
	protected $block_type;

	/**
	 * The signature for this query to match against in the blocks table.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $blocks_query_signature;

	//
	// Public Methods.
	//

	/**
	 * Constructs a query for a period.
	 *
	 * Both the start and end of the period are treated as inclusive.
	 *
	 * @since 1.0.0
	 *
	 * @param DateTime $start_date DateTime object for the start of the period.
	 * @param DateTime $end_date   DateTime object for the end of the period.
	 *                             Defaults to now.
	 * @param array    $args       Other optional args for the query. Supports all of
	 *                             the same args as `WordPoints_Top_Users_In_Period_Points_Logs_Query::__construct()`.
	 */
	public function __construct(
		DateTime $start_date,
		DateTime $end_date = null,
		array $args = array()
	) {

		if ( null === $end_date ) {

			$this->open_ended = true;

			$end_date = new DateTime(
				'@' . current_time( 'timestamp', true )
				, new DateTimeZone( 'UTC' )
			);
		}

		$this->start_date = $start_date;
		$this->end_date = $end_date;

		$this->start_timestamp = (int) $this->start_date->format( 'U' );
		$this->end_timestamp   = (int) $this->end_date->format( 'U' );

		// If the dates are invalid we return an error message below.
		if ( $this->end_timestamp < $this->start_timestamp ) {
			return;
		}

		parent::__construct( $args );

		$this->table_name = null;
	}

	/**
	 * Returns the start date for the query.
	 *
	 * The returned object is a clone, so modifications have no effect. It is
	 * essentially read-only.
	 *
	 * @since 1.0.0
	 *
	 * @return DateTime The start date object.
	 */
	public function get_start_date() {
		return clone $this->start_date;
	}

	/**
	 * Returns the end date for the query.
	 *
	 * The returned object is a clone, so modifications have no effect. It is
	 * essentially read-only.
	 *
	 * @since 1.0.0
	 *
	 * @return DateTime The end date object.
	 */
	public function get_end_date() {
		return clone $this->end_date;
	}

	/**
	 * Returns the args for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return array The query args.
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * Checks if this is a network query.
	 *
	 * A query is considered a network-scope query if it affects other sites on the
	 * network beside the current one.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether this is a network query.
	 */
	public function is_network_scope() {

		if ( ! is_multisite() ) {
			return false;
		}

		return (
			empty( $this->args['blog_id'] )
			|| (
				isset( $this->args['blog_id__compare'] )
				&& '=' !== $this->args['blog_id__compare']
			)
			|| get_current_blog_id() !== $this->args['blog_id']
		);
	}

	/**
	 * @since 1.0.0
	 */
	public function count() {

		_doing_it_wrong(
			__METHOD__
			, 'The top users query does not support counting.'
			, '1.0.0'
		);

		return false;
	}

	/**
	 * Gets the query results.
	 *
	 * @since 1.0.0
	 *
	 * @return object[]|WP_Error The results, or an error object on failure.
	 */
	public function get( $unused = null ) {

		if ( isset( $unused ) ) {
			_doing_it_wrong( __METHOD__, 'The top users query doesn\'t use the $method parameter.', '1.0.0' );
		}

		if ( $this->end_timestamp < $this->start_timestamp ) {
			return new WP_Error(
				'wordpoints_top_users_in_period_query_end_before_start'
				, __( 'End date cannot come before start date.', 'wordpoints-top-users-in-period' )
			);
		}

		$this->args = $this->clean_query_args( $this->args );

		$cache = $this->get_cache();
		$cached_results = $cache->get();

		if ( false !== $cached_results ) {
			return $cached_results;
		}

		$sql = $this->get_sql();

		if ( is_wp_error( $sql ) ) {
			return $sql;
		}

		global $wpdb;

		$results = $wpdb->get_results( $sql ); // WPCS: unprepared SQL, cache OK.

		$cache->set( $results );

		return $results;
	}

	/**
	 * @since 1.0.0
	 */
	public function get_sql( $unused = null ) {

		if ( isset( $unused ) ) {
			_doing_it_wrong( __METHOD__, 'The top users query doesn\'t use the $select_type parameter.', '1.0.0' );
		}

		if ( ! $this->is_query_ready ) {
			$this->blocks_query_signature = $this->generate_blocks_query_signature();
		}

		$this->block_type = $this->get_block_type();
		$this->start_block_info = $this->block_type->get_block_info( $this->start_timestamp );
		$this->end_block_info = $this->block_type->get_block_info( $this->end_timestamp );

		if ( ! $this->should_use_block_logs() ) {

			return $this->get_sql_for_points_logs();

		} elseif (
			$this->start_timestamp === $this->start_block_info['start']
			&& $this->end_timestamp === $this->end_block_info['end']
		) {

			return $this->get_sql_for_block_logs();

		} else {

			return $this->get_sql_for_both();
		}
	}

	//
	// Protected Methods.
	//

	/**
	 * @since 1.0.0
	 */
	protected function prepare_query() {

		if ( ! $this->is_query_ready ) {
			$this->prepare_having();
		}

		parent::prepare_query();
	}

	/**
	 * @since 1.0.0
	 */
	protected function prepare_select() {}

	/**
	 * @since 1.0.0
	 */
	protected function prepare_where() {}

	/**
	 * Prepares the SQL for the `HAVING` clause for the query.
	 *
	 * @since 1.0.0
	 */
	protected function prepare_having() {

		$this->having = '';

		$this->prepare_column_where( 'total', array( 'format' => '%d' ) );

		if ( $this->wheres ) {
			$this->having = 'HAVING ' . implode( ' AND ', $this->wheres ) . "\n";
		}
	}

	/**
	 * Gets the block type to be used by this query.
	 *
	 * @since 1.0.0
	 *
	 * @return WordPoints_Top_Users_In_Period_Block_TypeI The block type.
	 */
	protected function get_block_type() {

		$slug = 'week_in_seconds';

		/**
		 * Filter the block type to use for a query.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug  The slug of the block type to use.
		 * @param object $query The query.
		 */
		$slug = apply_filters(
			'wordpoints_top_user_in_period_query_block_type'
			, $slug
			, $this
		);

		$block_type = wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'block_types' )
			->get( $slug );

		if ( ! $block_type instanceof WordPoints_Top_Users_In_Period_Block_TypeI ) {
			return new WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds(
				'week_in_seconds'
			);
		}

		return $block_type;
	}

	/**
	 * Gets the cache object that should be used by this query.
	 *
	 * @since 1.0.0
	 *
	 * @return WordPoints_Top_Users_In_Period_Query_CacheI The cache object.
	 */
	protected function get_cache() {

		$slug = 'transients';

		/**
		 * Filter the cache object to use for a query.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug  The slug of the cache object to use.
		 * @param object $query The query.
		 */
		$slug = apply_filters(
			'wordpoints_top_user_in_period_query_cache'
			, $slug
			, $this
		);

		$args = $this->args;
		$args['start_timestamp'] = $this->start_timestamp;

		if ( ! $this->open_ended ) {
			$args['end_timestamp'] = $this->end_timestamp;
		}

		$is_network_query = $this->is_network_scope();

		$cache = wordpoints_module( 'top_users_in_period' )
			->get_sub_app( 'query_caches' )
			->get( $slug, array( $args, $is_network_query ) );

		if ( ! $cache instanceof WordPoints_Top_Users_In_Period_Query_CacheI ) {

			$slug = 'transients';

			$cache = new WordPoints_Top_Users_In_Period_Query_Cache_Transients(
				$slug
				, $args
				, $is_network_query
			);
		}

		// We need to keep track of the cache for the purpose of flushing it as
		// needed, if there is no end date or the end date is still in the future.
		if ( $this->open_ended || $this->end_timestamp > time() ) {
			$cache_index = new WordPoints_Top_Users_In_Period_Query_Cache_Index(
				$is_network_query
			);

			$cache_index->add(
				$slug
				, $this->args
				, $args['start_timestamp']
				, isset( $args['end_timestamp'] ) ? $args['end_timestamp'] : null
			);
		}

		return $cache;
	}

	/**
	 * Cleans query args to standardize them for creating a query signature.
	 *
	 * Query signatures are utilized both for traditional caching and for the blocks.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The query args to clean.
	 *
	 * @return array The cleaned args.
	 */
	protected function clean_query_args( array $args ) {

		$not_cols = array(
			'start' => true,
			'limit' => true,
			'order' => true,
			'order_by' => true,
			'fields' => true,
			'date_query' => true,
			'meta_query' => true,
			'meta_key' => true,
			'meta_value' => true,
			'meta_compare' => true,
		);

		foreach ( $args as $arg => $value ) {

			if ( isset( $not_cols[ $arg ] ) ) {
				continue;
			}

			$column = $arg;
			$operator = null;

			if ( strpos( $column, '__' ) ) {
				list( $column, $operator ) = explode( '__', $arg );
			}

			if ( ! isset( $this->columns[ $column ] ) ) {
				unset( $args[ $arg ] );
				continue;
			}

			// The compare operator is ignored if the value isn't set.
			if (
				'compare' === $operator
				&& 'NOT EXISTS' !== $value
				&& ! isset( $args[ $column ] )
			) {
				unset( $args[ $arg ] );
			}

			if ( 'in' === $operator || 'not_in' === $operator ) {

				// In and not in are ignored when a direct value is set.
				if ( isset( $args[ $column ] ) ) {
					unset( $args[ $arg ] );
					continue;
				}

				if ( '%d' === $this->columns[ $column ]['format'] ) {
					$value = array_map( 'wordpoints_int', $value );
				}

				$value = array_unique( $value );

				$count = count( $value );

				if ( 1 === $count ) {

					unset( $args[ $arg ] );

					$args[ $column ] = reset( $value );

					if ( 'not_in' === $operator ) {
						$args[ "{$column}__compare" ] = '!=';
					}

				} elseif ( 0 === $count ) {

					unset( $args[ $arg ] );

				} else {

					sort( $value );

					$args[ $arg ] = $value;
				}

			} elseif ( ! isset( $operator ) ) {

				if ( '%d' === $this->columns[ $column ]['format'] ) {
					$args[ $arg ] = wordpoints_int( $value );
				}

			} // End if ( in or not in ) elseif ( just column ).

		} // End foreach ( args ).

		return $args;
	}

	/**
	 * Generates the query signature to use for the blocks table.
	 *
	 * @since 1.0.0
	 *
	 * @return string The query signature.
	 */
	protected function generate_blocks_query_signature() {

		$args = $this->get_block_signature_args();

		ksort( $args );

		return wordpoints_hash( wp_json_encode( $args ) );
	}

	/**
	 * Gets just the args that are included in the block signature.
	 *
	 * @since 1.0.0
	 *
	 * @return array The args that are included in the block signature.
	 */
	protected function get_block_signature_args() {

		$args = $this->args;

		// The user and total args aren't needed in the signature since the blocks
		// table includes a `user_id` column, and the total is calculated across
		// blocks.
		unset(
			$args['limit'],
			$args['start'],
			$args['order'],
			$args['order_by'],
			$args['fields'],
			$args['user_id'],
			$args['user_id__in'],
			$args['user_id__not_in'],
			$args['user_id__compare'],
			$args['total'],
			$args['total__in'],
			$args['total__not_in'],
			$args['total__compare']
		);

		return $args;
	}

	/**
	 * Checks whether the block logs should be used, or just the regular logs only.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether to use the block logs or not.
	 */
	protected function should_use_block_logs() {

		$use_blocks = false;

		$one_block_only = $this->start_block_info === $this->end_block_info;

		// Only use the blocks if an entire block is going to fall within the period.
		if (
			// That only happens when more than two blocks are involved.
			(
				$this->start_block_info['end'] + 1 !== $this->end_block_info['start']
				&& ! $one_block_only
			)

			// Or, when there are only two blocks involved, but one or both fit the
			// period exactly.
			|| (
				$this->start_block_info['end'] + 1 === $this->end_block_info['start']
				&& (
					$this->start_block_info['start'] === $this->start_timestamp
					|| $this->end_block_info['end'] === $this->end_timestamp
				)
			)

			// Or, when there is only one block involved, but it fits exactly.
			|| (
				$one_block_only
				&& $this->start_block_info['start'] === $this->start_timestamp
				&& $this->end_block_info['end'] === $this->end_timestamp
			)
		) {
			$use_blocks = true;
		}

		/**
		 * Filters whether to use the block logs for a query.
		 *
		 * If the block logs aren't used, just the regular points logs will be
		 * queried to get the result.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $use_blocks Whether to use the block logs.
		 * @param object $query      The query.
		 */
		$use_blocks = apply_filters(
			'wordpoints_top_users_in_period_query_use_blocks'
			, $use_blocks
			, $this
		);

		return $use_blocks;
	}

	/**
	 * Gets the SQL for a query using only the regular points logs table.
	 *
	 * @since 1.0.0
	 *
	 * @return string The SQL.
	 */
	protected function get_sql_for_points_logs() {

		$args = $this->args;

		$args['date_query'][] = array(
			'inclusive' => true,
			'after'     => '@' . $this->start_timestamp,
			'before'    => '@' . $this->end_timestamp,
		);

		$query = new WordPoints_Top_Users_In_Period_Points_Logs_Query( $args );

		return $query->get_sql();
	}

	/**
	 * Gets the SQL for the query using only the block logs table.
	 *
	 * @since 1.0.0
	 *
	 * @return string|WP_Error The SQL, or an error on failure.
	 */
	protected function get_sql_for_block_logs() {

		$blocks = $this->get_and_verify_blocks();

		if ( is_wp_error( $blocks ) ) {
			return $blocks;
		}

		return $this->get_block_logs_query( $blocks )->get_sql();
	}

	/**
	 * Gets the SQL using a hybrid query involving both the regular and block tables.
	 *
	 * @since 1.0.0
	 *
	 * @return string|WP_Error The SQL, or an error object on failure.
	 */
	protected function get_sql_for_both() {

		$blocks = $this->get_and_verify_blocks();

		if ( is_wp_error( $blocks ) ) {
			return $blocks;
		}

		$this->prepare_query();

		$blocks_query = $this->get_block_logs_query( $blocks, false );
		$logs_query = $this->get_points_logs_query( $blocks );

		return "
			SELECT SUM(`total`) AS `total`, `user_id`
			FROM (
					{$blocks_query->get_sql()}
				UNION ALL
					{$logs_query->get_sql()}
			) AS `t`
			{$this->order}
			{$this->limit}
		";
	}

	/**
	 * Gets the query for the block logs table.
	 *
	 * @since 1.0.0
	 *
	 * @param object[] $blocks              The blocks to query.
	 * @param bool     $inherit_limit_order Whether to use the limit and order
	 *                                      related args from the main query or not.
	 *
	 * @return WordPoints_Top_Users_In_Period_Block_Logs_Query The block logs query.
	 */
	protected function get_block_logs_query( $blocks, $inherit_limit_order = true ) {

		$args = array( 'block_id__in' => wp_list_pluck( $blocks, 'id' ) );

		$inherited_args = array(
			'user_id',
			'user_id__in',
			'user_id__not_in',
			'user_id__compare',
		);

		if ( $inherit_limit_order ) {
			$inherited_args[] = 'limit';
			$inherited_args[] = 'start';
			$inherited_args[] = 'order';
			$inherited_args[] = 'order_by';
			$inherited_args[] = 'total';
			$inherited_args[] = 'total__in';
			$inherited_args[] = 'total__not_in';
			$inherited_args[] = 'total__compare';
		} else {
			$args['order_by'] = false;
		}

		foreach ( $inherited_args as $arg ) {
			if ( isset( $this->args[ $arg ] ) ) {
				$args[ $arg ] = $this->args[ $arg ];
			}
		}

		return new WordPoints_Top_Users_In_Period_Block_Logs_Query( $args );
	}

	/**
	 * Gets the points logs query for use along with a block logs query.
	 *
	 * @since 1.0.0
	 *
	 * @param object[] $blocks The blocks being queried for the period.
	 *
	 * @return WordPoints_Top_Users_In_Period_Points_Logs_Query The points logs query.
	 */
	protected function get_points_logs_query( $blocks ) {

		$args = $this->args;

		// The conditions for the total need to be set only for the combined query.
		unset(
			$args['limit'],
			$args['start'],
			$args['total'],
			$args['total__in'],
			$args['total__not_in'],
			$args['total__compare']
		);

		$args['order_by'] = false;

		$first_block = $blocks[0];
		$last_block  = end( $blocks );

		$first_block_start = strtotime( $first_block->start_date . '+0000' );
		$last_block_end = strtotime( $last_block->end_date . '+0000' );

		$date_query = array( 'relation' => 'OR' );

		if ( $this->start_timestamp !== $first_block_start ) {
			$date_query[] = array(
				'inclusive' => true,
				'after'     => '@' . $this->start_timestamp,
				'before'    => '@' . ( $first_block_start - 1 ), // Because inclusive.
			);
		}

		if ( $this->end_timestamp !== $last_block_end ) {
			$date_query[] = array(
				'inclusive' => true,
				'after'     => '@' . ( $last_block_end + 1 ), // Because inclusive.
				'before'    => '@' . $this->end_timestamp,
			);
		}

		$args['date_query'][] = $date_query;

		return new WordPoints_Top_Users_In_Period_Points_Logs_Query( $args );
	}

	/**
	 * Gets the blocks involved in this query and verifies that they can be used.
	 *
	 * @since 1.0.0
	 *
	 * @return object[]|WP_Error The blocks, or an error object on failure.
	 */
	protected function get_and_verify_blocks() {

		// See if any blocks exist that fall into this period.
		$blocks = $this->get_blocks();

		// If any of these blocks are currently in the process of being filled by
		// another query, we can't proceed.
		$draft_blocks = $this->check_for_draft_blocks( $blocks );

		if ( $draft_blocks ) {
			return new WP_Error(
				'wordpoints_top_users_in_period_query_draft_blocks'
				, ''
				, $draft_blocks
			);
		}

		// If any of the blocks are not filled yet, we need to fill them.
		$missing_blocks = $this->check_for_missing_blocks( $blocks );

		if ( ! empty( $missing_blocks ) ) {

			foreach ( $missing_blocks as $block ) {
				if ( ! $this->fill_block_logs( $block ) ) {
					return new WP_Error(
						'wordpoints_top_users_in_period_query_failed_filling_block'
						, ''
						, $block
					);
				}
			}

			return $this->get_and_verify_blocks();
		}

		return $blocks;
	}

	/**
	 * Gets the list of blocks that fall within this query's period.
	 *
	 * @since 1.0.0
	 *
	 * @return object[] The blocks.
	 */
	protected function get_blocks() {

		$block_query = new WordPoints_Top_Users_In_Period_Blocks_Query(
			array(
				'block_type'       => $this->block_type->get_slug(),
				'start_date_query' => array(
					'inclusive' => true,
					'after'     => '@' . $this->start_timestamp,
				),
				'end_date_query'   => array(
					'inclusive' => true,
					'before'    => '@' . $this->end_timestamp,
				),
				'query_signature'  => $this->blocks_query_signature,
				'order_by'         => 'start_date',
				'order'            => 'ASC',
			)
		);

		return $block_query->get();
	}

	/**
	 * Checks for any blocks that are still drafts.
	 *
	 * @since 1.0.0
	 *
	 * @param object[] $blocks The blocks.
	 *
	 * @return object[] The draft blocks, if any.
	 */
	protected function check_for_draft_blocks( $blocks ) {

		return wp_list_filter( $blocks, array( 'status' => 'draft' ) );
	}

	/**
	 * Checks for any missing blocks for the period covered by the query.
	 *
	 * @since 1.0.0
	 *
	 * @param object[] $blocks The blocks that are already in the database.
	 *
	 * @return array[] Start and end dates for any missing blocks.
	 */
	protected function check_for_missing_blocks( $blocks ) {

		$missing_blocks = array();

		$start = $this->start_block_info['start'];

		if ( $start !== $this->start_timestamp ) {
			$start = $this->start_block_info['end'] + 1;
		}

		$end_block = $this->end_block_info;

		if ( $end_block['end'] !== $this->end_timestamp ) {
			$end_block = $this->block_type->get_block_info( $end_block['start'] - 1 );
		}

		if ( empty( $blocks ) ) {
			return $this->note_missing_blocks( $end_block['end'] + 1, $start );
		}

		$first_block = array_shift( $blocks );
		$first_block_start = strtotime( $first_block->start_date . '+0000' );

		// Check if there are any blocks missing at the start.
		if ( $first_block_start !== $start ) {

			$missing_blocks = array_merge(
				$missing_blocks
				, $this->note_missing_blocks( $first_block_start, $start )
			);
		}

		$previous_block = $first_block;

		// Check if there are any holes between blocks.
		foreach ( $blocks as $index => $block ) {

			$missing_blocks = array_merge(
				$missing_blocks
				, $this->note_missing_blocks(
					strtotime( $block->start_date . '+0000' )
					, strtotime( $previous_block->end_date . '+0000' ) + 1
				)
			);

			$previous_block = $block;
		}

		$last_block = $previous_block;
		$last_block_start = strtotime( $last_block->start_date . '+0000' );

		// Check if there are any blocks missing at the end.
		if ( $last_block_start !== $end_block['start'] ) {

			$missing_blocks = array_merge(
				$missing_blocks
				, $this->note_missing_blocks(
					$end_block['end'] + 1
					, strtotime( $last_block->end_date . '+0000' ) + 1
				)
			);
		}

		return $missing_blocks;
	}

	/**
	 * Makes a note of all blocks that are missing between two timestamps.
	 *
	 * Each missing block is then saved to the database as a "draft", to have the
	 * data for it in the block logs table filled momentarily.
	 *
	 * @since 1.0.0
	 *
	 * @param int $actual_start   The timestamp for when the next detected block
	 *                            actually starts.
	 * @param int $expected_start The timestamp for when the next block was expected
	 *                            to start.
	 *
	 * @return array[] Start and end dates for any missing blocks.
	 */
	protected function note_missing_blocks( $actual_start, $expected_start ) {

		$missing_blocks = array();

		// If we start when expected, we're OK.
		if ( $actual_start <= $expected_start ) {
			return $missing_blocks;
		}

		$block_end = $expected_start - 1;

		// Otherwise note the blocks that would be expected between the expected
		// starting point and the actual starting point.
		while ( $block_end + 1 < $actual_start ) {

			$block_info = $this->block_type->get_block_info( $block_end + 1 );

			$block_end = $block_info['end'];

			// Save the draft block here to reduce chance of race conditions where
			// another query is trying to fill the same block.
			$block_info['id'] = $this->save_draft_block( $block_info );

			$missing_blocks[] = $block_info;
		}

		return $missing_blocks;
	}

	/**
	 * Saves a block as a draft.
	 *
	 * @since 1.0.0
	 *
	 * @param array $block The block data.
	 *
	 * @return int|false The block ID, or false on failure.
	 */
	protected function save_draft_block( $block ) {

		global $wpdb;

		$rows = $wpdb->insert(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, array(
				'start_date'      => date( 'Y-m-d H:i:s', $block['start'] ),
				'end_date'        => date( 'Y-m-d H:i:s', $block['end'] ),
				'block_type'      => $this->block_type->get_slug(),
				'query_signature' => $this->blocks_query_signature,
				'status'          => 'draft',
			)
		);

		$id = (int) $wpdb->insert_id;

		if ( 1 !== (int) $rows || $id < 1 ) {
			return false;
		}

		return $id;
	}

	/**
	 * Fills in all of the data in the block logs table for a block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $block The block to fill in the logs the data for.
	 *
	 * @return bool Whether the block was filled successfully.
	 */
	protected function fill_block_logs( $block ) {

		global $wpdb;

		if ( ! $block['id'] ) {
			return false;
		}

		$this->no_interruptions();

		$args = $this->get_block_signature_args();

		// Ordering isn't necessary here, so explicitly set it to false.
		$args['order_by'] = false;

		$args['date_query'][] = array(
			'inclusive' => true,
			'after'     => '@' . $block['start'],
			'before'    => '@' . $block['end'],
		);

		$query = new WordPoints_Top_Users_In_Period_Points_Logs_Query( $args );

		$result = $wpdb->query( // WPCS: unprepared SQL OK.
			"
				INSERT INTO `{$wpdb->base_prefix}wordpoints_top_users_in_period_block_logs` 
					( `block_id`, `points`, `user_id` )
				SELECT " . (int) $block['id'] . ',' . substr( trim( $query->get_sql() ), 6 )
		); // WPCS: cache OK.

		if ( false === $result ) {
			return false;
		}

		$this->publish_block( $block['id'] );

		return true;
	}

	/**
	 * Prevents any interruptions from occurring during the query.
	 *
	 * @since 1.0.0
	 */
	protected function no_interruptions() {

		static $done = false;

		if ( $done ) {
			return;
		}

		ignore_user_abort( true );

		if ( ! wordpoints_is_function_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		$done = true;
	}

	/**
	 * Publishes a block.
	 *
	 * Blocks are initially inserted into the database with the "draft" status, until
	 * they are filled. Then we can "publish" them by giving them a status of
	 * "filled".
	 *
	 * @since 1.0.0
	 *
	 * @param int $block_id The ID of the block to mark as published.
	 *
	 * @return bool Whether the block was published successfully.
	 */
	protected function publish_block( $block_id ) {

		global $wpdb;

		$rows = $wpdb->update(
			$wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks'
			, array( 'status' => 'filled' )
			, array( 'id' => $block_id )
		); // WPCS: cache OK.

		if ( 1 !== (int) $rows ) {
			return false;
		}

		return true;
	}
}

// EOF
