<?php
/**
 * Meta description keywords check.
 *
 * @since   3.4.0
 * @package SmartCrawl
 */

namespace SmartCrawl\Checks;

use SmartCrawl\Html;
use SmartCrawl\Cache\Post_Cache;

/**
 * Class instance to check Metadesc Keywords
 */
class Metadesc_Keywords extends Post_Check {

	/**
	 * Holds state reference
	 *
	 * @var bool
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
		if ( - 1 === $this->state ) {
			return __( "We couldn't find a description to check for keywords", 'wds' );
		}

		return false === $this->state
			? __( "The SEO description doesn't contain your focus keywords", 'wds' )
			: __( 'The SEO description contains your focus keywords', 'wds' );
	}

	/**
	 * Apply check to the subject.
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	public function apply() {
		$post = $this->get_subject();

		if ( ! is_object( $post ) || empty( $post->ID ) ) {
			$subject = $this->get_markup();
		} else {
			$smartcrawl_post = Post_Cache::get()->get_post( $post->ID );
			$subject         = $smartcrawl_post
				? $smartcrawl_post->get_meta_description()
				: '';
		}

		$this->state = $this->has_focus( $subject );

		return ! ! $this->state;
	}

	/**
	 * Get check result.
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	public function get_result() {
		return array( 'state' => $this->state );
	}
}