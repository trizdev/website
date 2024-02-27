<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Schema\Utils;

class Static_Home extends Fragment {
	/**
	 * @var Utils
	 */
	private $utils;
	/**
	 * @var
	 */
	private $posts;
	/**
	 * @var
	 */
	private $title;
	/**
	 * @var
	 */
	private $description;

	/**
	 * @param $posts
	 * @param $title
	 * @param $description
	 */
	public function __construct( $posts, $title, $description ) {
		$this->utils       = Utils::get();
		$this->posts       = $posts;
		$this->title       = $title;
		$this->description = $description;
	}

	/**
	 * @return Archive
	 */
	protected function get_raw() {
		$page_for_posts_id = get_option( 'page_for_posts' );
		$url               = get_permalink( $page_for_posts_id );

		return new Archive(
			'CollectionPage',
			$url,
			$this->posts,
			$this->title,
			$this->description
		);
	}
}