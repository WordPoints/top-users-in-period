<?php

/**
 * Points logs query class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Queries for the total number of points for each user from a set of points logs.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Points_Logs_Query
	extends WordPoints_Points_Logs_Query {

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
	 *        Supports all of the args that WordPoints_Points_Logs_Query::__construct()
	 *        does, with the following additions/modifications.
	 *
	 *        @type string|array $fields         Fields to include in the results. Defaults to 'total'
	 *                                           and 'user_id', and *cannot be changed*.
	 *        @type string       $order_by       The field to use to order the results. Default: 'total'.
	 *        @type string       $total          Include only results for this total.
	 *        @type string       $total__compare The comparison operator to use with the above value.
	 *        @type string[]     $total__in      Limit results to these totals.
	 *        @type string[]     $total__not_in  Exclude users with these totals from the results.
	 * }
	 */
	public function __construct( array $args = array() ) {

		// We can't use $this->defaults because the value would be overwritten by the
		// parent constructor.
		if ( ! isset( $args['order_by'] ) ) {
			$args['order_by'] = 'total';
		}

		$this->columns['total'] = array( 'format' => '%d' );

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
		$this->where = '';

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
