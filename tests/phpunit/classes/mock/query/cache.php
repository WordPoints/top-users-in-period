<?php

/**
 * Mock query cache class for use in the PHPUnit tests.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Mock query cache used in the PHPUnit tests.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_PHPUnit_Mock_Query_Cache
	implements WordPoints_Top_Users_In_Period_Query_CacheI {

	/**
	 * The cached value.
	 *
	 * @since 1.0.0
	 *
	 * @var mixed
	 */
	public static $value = false;

	/**
	 * @since 1.0.0
	 */
	public function get() {
		return self::$value;
	}

	/**
	 * @since 1.0.0
	 */
	public function set( $value ) {
		self::$value = $value;
	}

	/**
	 * @since 1.0.0
	 */
	public function delete() {
		self::$value = false;
	}
}

// EOF
