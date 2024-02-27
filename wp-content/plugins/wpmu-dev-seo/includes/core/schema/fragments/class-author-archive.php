<?php
/**
 * Manages Schema Author Archive fragment.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Schema\Utils;

/**
 * Schema Author Archive Fragment class.
 */
class Author_Archive extends Fragment {
	/**
	 * Schema Utils.
	 *
	 * @var Utils
	 */
	private $utils;
	/**
	 * Author as WP_User object.
	 *
	 * @var \WP_User
	 */
	private $author;
	/**
	 * Posts created by this author.
	 *
	 * @var \WP_Post[]
	 */
	private $posts;
	/**
	 * Author archive title.
	 *
	 * @var string
	 */
	private $title;
	/**
	 * Author archive description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Constructor.
	 *
	 * @param \WP_User   $author Author as WP_User object.
	 * @param \WP_Post[] $posts WP Posts.
	 * @param string     $title Author archive title.
	 * @param string     $description Author archive description.
	 */
	public function __construct( $author, $posts, $title, $description ) {
		$this->author      = $author;
		$this->posts       = $posts;
		$this->title       = $title;
		$this->description = $description;
		$this->utils       = Utils::get();
	}

	/**
	 * Retrieves schema raw data.
	 *
	 * @return array
	 */
	protected function get_raw() {
		$author_url = get_author_posts_url( $this->author->ID );

		$enabled = (bool) $this->utils->get_schema_option( 'schema_enable_author_archives' );
		if ( $enabled ) {
			return new Archive(
				'ProfilePage',
				$author_url,
				$this->posts,
				$this->title,
				$this->description
			);
		} else {
			$custom_schema_types = $this->utils->get_custom_schema_types();
			if ( $custom_schema_types ) {
				return $this->utils->add_custom_schema_types(
					array(),
					$custom_schema_types,
					$this->utils->get_webpage_id( $author_url )
				);
			} else {
				return array();
			}
		}
	}
}