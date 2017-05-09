<?php

/**
 * Weekly block type class.
 *
 * @package WordPoints_Top_Users_In_Period
 * @since   1.0.0
 */

/**
 * A block type where the blocks are one week's worth of seconds in length.
 *
 * The block edges will not align with actual calendar weeks, they will just be the
 * same number of seconds in length as a perfect week is. The start and end of each
 * block is calculated relative to the Unix Epoch, using the 0 second as zero.
 *
 * @since 1.0.0
 */
class WordPoints_Top_Users_In_Period_Block_Type_Week_In_Seconds
	implements WordPoints_Top_Users_In_Period_Block_TypeI {

	/**
	 * The slug of this block type.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * @since 1.0.0
	 *
	 * @param string $slug The slug of this block type.
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * @since 1.0.0
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @since 1.0.0
	 */
	public function get_block_info( $timestamp ) {

		$start = $timestamp - ( $timestamp % WEEK_IN_SECONDS );

		// We subtract one from the end because the start and end are inclusive.
		return array( 'start' => $start, 'end' => $start + WEEK_IN_SECONDS - 1 );
	}
}

// EOF
