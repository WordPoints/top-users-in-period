<?php

/**
 * Block logs query class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Queries the block logs.
 *
 * The historical totals for the users from points logs are split into blocks of time
 * and the total for each block for each user is stored. This class queries the table
 * that contains a list of the totals for each block for each user. It groups the
 * results for each user and returns the sum total of points values for each for all
 * block logs matched by the query.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Block_Logs_Query
	extends WordPoints_DB_Query {

	/**
	 * @since 1.0.0
	 */
	protected $columns = array(
		'id'       => array( 'format' => '%d', 'unsigned' => true ),
		'block_id' => array( 'format' => '%d', 'unsigned' => true ),
		'user_id'  => array( 'format' => '%d', 'unsigned' => true ),
		'points'   => array( 'format' => '%d', 'unsigned' => true ),
		// Pseudo-column of the sum of the results.
		'total'    => array( 'format' => '%d' ),
	);

	/**
	 * @since 1.0.1
	 */
	protected $select_count = 'SELECT COUNT(DISTINCT `user_id`)';

	/**
	 * The HAVING clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $having = '';

	/**
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *        The arguments for the query.
	 *
	 *        Supports all of the args that WordPoints_DB_Query::__construct() does,
	 *        with the following additions/modifications.
	 *
	 *        @type string|array $fields            Fields to include in the results. Defaults to 'total'
	 *                                              and 'user_id', and *cannot be changed*.
	 *        @type string       $order_by          The field to use to order the results. Default: 'total'.
	 *        @type int          $id                The ID of the block log to retrieve.
	 *        @type string       $id__compare       The comparison operator to use with the above value.
	 *        @type int[]        $id__in            Limit results to these block log IDs.
	 *        @type int[]        $id__not_in        Exclude all block logs with these IDs.
	 *        @type int          $user_id           Limit results to block logs for this user.
	 *        @type string       $user_id__compare  The comparison operator to use with the above value.
	 *        @type int[]        $user_id__in       Limit results to block logs for these users.
	 *        @type int[]        $user_id__not_in   Exclude all block logs for these users from the results.
	 *        @type int          $block_id          Limit results to logs for this block.
	 *        @type string       $block_id__compare The comparison operator to use with the above value.
	 *        @type int[]        $block_id__in      Limit results to logs for these blocks.
	 *        @type int[]        $block_id__not_in  Exclude all logs for these blocks from the results.
	 *        @type int          $points            Limit results to blocks in which the user earned this number of points. More uses when used with $points__compare.
	 *        @type string       $points__compare   Comparison operator for logs comparison with $points. May be any of these: '=', '<', '>', '<>', '!=', '<=', '>='. Default is '='.
	 *        @type int[]        $points__in        Return only block logs for these points amounts.
	 *        @type int[]        $points__not_in    Exclude block logs for these points amounts from the results.
	 *        @type int          $total             Include only results for this total.
	 *        @type string       $total__compare    The comparison operator to use with the above value.
	 *        @type int[]        $total__in         Limit results to these totals.
	 *        @type int[]        $total__not_in     Exclude users with these totals from the results.
	 * }
	 */
	public function __construct( array $args = array() ) {

		global $wpdb;

		$this->table_name = $wpdb->base_prefix . 'wordpoints_top_users_in_period_block_logs';

		$this->defaults['order_by'] = 'total';

		parent::__construct( $args );
	}

	/**
	 * @since 1.0.1
	 */
	public function get_sql( $select_type = 'SELECT' ) {

		$this->prepare_query();

		$order = $this->order;

		if ( 'SELECT COUNT' === $select_type ) {
			$this->order = '';
		}

		$sql = parent::get_sql( $select_type );

		$this->order = $order;

		return $sql;
	}

	/**
	 * @since 1.0.0
	 */
	protected function prepare_select() {

		// We check this here instead of in the constructor, because of set_args().
		if ( isset( $this->args['fields'] ) ) {
			_doing_it_wrong(
				__METHOD__
				, "The 'fields' argument is not supported and has no effect."
				, '1.0.0'
			);
		}

		$this->select = 'SELECT SUM(`points`) AS `total`, `user_id`';
	}

	/**
	 * @since 1.0.0
	 */
	protected function prepare_where() {

		$this->wheres = array();

		// First do just the total column.
		$this->prepare_column_where( 'total', $this->columns['total'] );

		if ( $this->wheres ) {
			$this->having = 'HAVING ' . implode( ' AND ', $this->wheres ) . "\n";
		}

		$this->wheres = array();
		$this->where  = '';

		// Then do everything else.
		$all_columns = $this->columns;

		unset( $this->columns['total'] );

		parent::prepare_where();

		$this->columns = $all_columns;
	}

	/**
	 * @since 1.0.0
	 */
	protected function prepare_order_by() {

		parent::prepare_order_by();

		$this->order = "GROUP BY `user_id`\n{$this->having}\n{$this->order}";
	}
}

// EOF
