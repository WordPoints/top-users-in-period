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
		'id'                 => array( 'format' => '%d', 'unsigned' => true ),
		'block_type'         => array( 'format' => '%s' ),
		'start_date'         => array( 'format' => '%s', 'is_date' => true ),
		'end_date'           => array( 'format' => '%s', 'is_date' => true ),
		'query_signature_id' => array( 'format' => '%d', 'unsigned' => true ),
		'status'             => array( 'format' => '%s', 'values' => array( 'draft', 'filled' ) ),
	);

	/**
	 * @since 1.0.1
	 */
	protected $deprecated_args = array(
		'query_signature' => array(
			'replacement' => 'query_signature_id',
			'version'     => '1.0.1',
			'class'       => __CLASS__,
		),
		'query_signature__compare' => array(
			'replacement' => 'query_signature_id__compare',
			'version'     => '1.0.1',
			'class'       => __CLASS__,
		),
		'query_signature__in' => array(
			'replacement' => 'query_signature_id__in',
			'version'     => '1.0.1',
			'class'       => __CLASS__,
		),
		'query_signature__not_in' => array(
			'replacement' => 'query_signature_id__not_in',
			'version'     => '1.0.1',
			'class'       => __CLASS__,
		),
	);

	/**
	 * @since 1.0.0
	 * @since 1.0.1 $query_signature* args are deprecated in favor of
	 *              $query_signature_id*.
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
	 *        @type string       $order_by                    The field to use to order the results. Default: 'start_date'.
	 *        @type int          $id                          The ID of the block to retrieve.
	 *        @type string       $id__compare                 The comparison operator to use with the above value.
	 *        @type int[]        $id__in                      Limit results to these block IDs.
	 *        @type int[]        $id__not_in                  Exclude all blocks with these IDs.
	 *        @type string       $block_type                  Include only results for this block type.
	 *        @type string       $block_type__compare         The comparison operator to use with the above value.
	 *        @type string[]     $block_type__in              Limit results to these block types.
	 *        @type string[]     $block_type__not_in          Exclude blocks of these types from the results.
	 *        @type string       $query_signature_id          Include only results for this query signature ID.
	 *        @type string       $query_signature_id__compare The comparison operator to use with the above value.
	 *        @type string[]     $query_signature_id__in      Limit results to these query signature IDs.
	 *        @type string[]     $query_signature_id__not_in  Exclude blocks for these query signature IDs from the results.
	 *        @type string       $status                      Include only results for blocks with this status.
	 *        @type string       $status__compare             The comparison operator to use with the above value.
	 *        @type string[]     $status__in                  Limit results to blocks with these statuses.
	 *        @type string[]     $status__not_in              Exclude blocks with these statuses from the results.
	 *        @type array        $start_date_query            Arguments for a WP_Date_Query with the 'start_date' as the default column.
	 *        @type array        $end_date_query              Arguments for a WP_Date_Query with the 'end_date' as the default column.
	 * }
	 */
	public function __construct( $args = array() ) {

		global $wpdb;

		$this->table_name = $wpdb->base_prefix . 'wordpoints_top_users_in_period_blocks';

		$this->defaults['order_by'] = 'start_date';

		// Back-compat for 1.0.0.
		if ( isset( $args['query_signature'] ) ) {

			$query = new WordPoints_Top_Users_In_Period_Query_Signatures_Query(
				array( 'fields' => 'id', 'signature' => $args['query_signature'] )
			);

			$args['query_signature'] = $query->get( 'var' );
		}

		parent::__construct( $args );
	}
}

// EOF
