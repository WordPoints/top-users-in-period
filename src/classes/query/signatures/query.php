<?php

/**
 * Query signatures query class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.1
 */

/**
 * Queries the query signatures.
 *
 * The queries on for user totals are matched against the blocks tables based on the
 * query signature. The signature of a query is derived from the query's significant
 * arguments, and is then hashed for easy comparison. If two queries have the same
 * signature, they can use the same blocks to calculate the user totals from; queries
 * with other signatures will use other sets of blocks, respectively. To keep track
 * of the query signatures, and store a list of the args behind them, we store them
 * in a table, which this class is used to query.
 *
 * @since 1.0.1
 */
class WordPoints_Top_Users_In_Period_Query_Signatures_Query
	extends WordPoints_DB_Query {

	/**
	 * @since 1.0.1
	 */
	protected $columns = array(
		'id'         => array( 'format' => '%d', 'unsigned' => true ),
		'signature'  => array( 'format' => '%s' ),
		'query_args' => array( 'format' => '%s' ),
	);

	/**
	 * @since 1.0.1
	 *
	 * @param array $args {
	 *        The arguments for the query.
	 *
	 *        The arguments are the same as the parent class with the following
	 *        additions and modifications.
	 *
	 *        @type string       $order_by            The field to use to order the results. Default: 'id'.
	 *        @type int          $id                  The ID of the query signature to retrieve.
	 *        @type string       $id__compare         The comparison operator to use with the above value.
	 *        @type int[]        $id__in              Limit results to these query signature IDs.
	 *        @type int[]        $id__not_in          Exclude all query signatures with these IDs.
	 *        @type string       $signature           Include only results for this query signature.
	 *        @type string       $signature__compare  The comparison operator to use with the above value.
	 *        @type string[]     $signature__in       Limit results to these query signatures.
	 *        @type string[]     $signature__not_in   Exclude blocks for these query signatures from the results.
	 *        @type string       $query_args          Include only results for queries with this set of query args.
	 *        @type string       $query_args__compare The comparison operator to use with the above value.
	 *        @type string[]     $query_args__in      Limit results to queries with these sets of query args.
	 *        @type string[]     $query_args__not_in  Exclude queries with these sets of args from the results.
	 */
	public function __construct( $args = array() ) {

		global $wpdb;

		$this->table_name = $wpdb->base_prefix . 'wordpoints_top_users_in_period_query_signatures';

		$this->defaults['order_by'] = 'id';

		parent::__construct( $args );
	}
}

// EOF
