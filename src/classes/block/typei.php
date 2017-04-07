<?php

/**
 * Block type interface.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * Defines the interface for representing a block type.
 *
 * @since 1.0.0
 */
interface WordPoints_Top_Users_In_Period_Block_TypeI {

	/**
	 * Gets the slug of this block type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The block type's slug.
	 */
	public function get_slug();

	/**
	 * Gets the start and end times for a block.
	 *
	 * Both the start and end times are inclusive.
	 *
	 * @since 1.0.0
	 *
	 * @param int $timestamp A time within the block to get the info for.
	 *
	 * @return array {
	 *         The block info.
	 *
	 *         @type int $start The start time of the block.
	 *         @type int $end   The end time of the block.
	 * }
	 */
	public function get_block_info( $timestamp );
}

// EOF
