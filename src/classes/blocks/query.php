<?php

/**
 * Blocks query class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Queries the blocks.
 *
 * The historical totals for the users from points logs are split into blocks of time
 * and the total for each block for each user is stored. This class queries the table
 * that contains a list of the blocks, including what periods each block covers, what
 * exact points log query they are for, etc. The actual totals for each block for
 * each user are stored in a different table, indexed by block IDs from this table.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Blocks_Query
	extends WordPoints_DB_Query {

	/**
	 * @since 1.0.0
	 */
	protected $columns = array(
		'id' => array( 'format' => '%d', 'unsigned' => true ),
		'block_type' => array( 'format' => '%s' ),
		'start_date' => array( 'format' => '%s', 'is_date' => true ),
		'end_date' => array( 'format' => '%s', 'is_date' => true ),
		'query_signature' => array( 'format' => '%s' ),
	);

	/**
	 * @since 1.0.0
	 *
	 * @see WP_Date_Query for the proper arguments for the 'start_date_query' and
	 *                    'end_date_query' args.
	 *
	 * @param array $args {
	 *        The arguments for the query.
	 *
	 *        The arguments are the same as the parent class with the following
	 *        additions and modifications.
	 *
	 *        @type string       $order_by                 The field to use to order the results. Default: 'start_date'.
	 *        @type int          $id                       The ID of the log to retrieve.
	 *        @type string       $id__compare              The comparison operator to use with the above value.
	 *        @type int[]        $id__in                   Limit results to these log IDs.
	 *        @type int[]        $id__not_in               Exclude all logs with these IDs.
	 *        @type string       $block_type               Include only results for this block type.
	 *        @type string       $block_type__compare      The comparison operator to use with the above value.
	 *        @type string[]     $block_type__in           Limit results to these block types.
	 *        @type string[]     $block_type__not_in       Exclude logs for these block types from the results.
	 *        @type string       $query_signature          Include only results for this query signature.
	 *        @type string       $query_signature__compare The comparison operator to use with the above value.
	 *        @type string[]     $query_signature__in      Limit results to these query signatures.
	 *        @type string[]     $query_signature__not_in  Exclude logs for these query signatures from the results.
	 *        @type array        $start_date_query         Arguments for a WP_Date_Query with the 'start_date' as teh default column.
	 *        @type array        $end_date_query           Arguments for a WP_Date_Query with the 'end_date' as teh default column.
	 * }
	 */
	public function __construct( $args = array() ) {

		global $wpdb;

		$this->table_name = $wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks';

		$this->defaults['order_by'] = 'start_date';

		parent::__construct( $args );
	}
}

// EOF
