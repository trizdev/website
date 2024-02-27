<?php
/**
 * Focus check.
 *
 * @since   3.4.0
 * @package SmartCrawl
 */

namespace SmartCrawl\Checks;

/**
 * Class Smartcrawl_Check_Focus
 */
class Focus extends Check {

	/**
	 * Holds check state
	 *
	 * @var int
	 */
	private $state;

	/**
	 * Get the message for the check.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_status_msg() {
		return false === $this->state
			? __( 'There are no focus keywords', 'wds' )
			: __( 'There are some focus keywords', 'wds' );
	}

	/**
	 * Apply check to the subject.
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	public function apply() {
		$focus       = $this->get_focus();
		$this->state = ! empty( $focus );

		return ! ! $this->state;
	}

	/**
	 * Get check result.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 */
	public function get_result() {
		return array( 'state' => $this->state );
	}
}