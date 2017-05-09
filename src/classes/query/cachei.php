<?php

/**
 * Query cache interface.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Defines the interface for a query cache object.
 *
 * @since 1.0.0
 */
interface WordPoints_Top_Users_In_Period_Query_CacheI {

	/**
	 * Gets the value from the cache.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed The cached value, or false if none.
	 */
	public function get();

	/**
	 * Sets the value of the cache.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The value to cache.
	 *
	 * @return bool Whether the cache was set successfully.
	 */
	public function set( $value );

	/**
	 * Deletes the current value from the cache.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the cache was deleted successfully.
	 */
	public function delete();
}

// EOF
