<?php

/**
 * Transients query cache class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since 1.0.0
 */

/**
 * Query cache class that utilizes the transients API.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Query_Cache_Transients
	implements WordPoints_Top_Users_In_Period_Query_CacheI {

	/**
	 * The name of the transient to use for this query.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $transient_name;

	/**
	 * Whether this is a network query.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $is_network_query;

	/**
	 * @since 1.0.0
	 *
	 * @param string $slug       The slug of this query cache object.
	 * @param array  $query_args The args of the query this cache object is for.
	 */
	public function __construct( $slug, array $query_args = array() ) {

		ksort( $query_args );

		$query_signature = wordpoints_hash( wp_json_encode( $query_args ) );

		$this->transient_name = "wordpoints_top_users_in_period_query_{$query_signature}";

		$this->is_network_query = $this->is_network_query( $query_args );
	}

	/**
	 * Checks if this is a network query.
	 *
	 * A query is considered a network-scope query if it affects other sites on the
	 * network beside the current one.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_args The query args.
	 *
	 * @return bool Whether this is a network query.
	 */
	protected function is_network_query( $query_args ) {

		if ( ! is_multisite() ) {
			return false;
		}

		return (
			empty( $query_args['blog_id'] )
			|| (
				isset( $query_args['blog_id__compare'] )
				&& '=' !== $query_args['blog_id__compare']
			)
			|| get_current_blog_id() !== $query_args['blog_id']
		);
	}

	/**
	 * @since 1.0.0
	 */
	public function get() {

		if ( $this->is_network_query ) {
			return get_site_transient( $this->transient_name );
		} else {
			return get_transient( $this->transient_name );
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function set( $value ) {

		if ( $this->is_network_query ) {
			return set_site_transient( $this->transient_name, $value );
		} else {
			return set_transient( $this->transient_name, $value );
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function delete() {

		if ( $this->is_network_query ) {
			return delete_site_transient( $this->transient_name );
		} else {
			return delete_transient( $this->transient_name );
		}
	}
}

// EOF
