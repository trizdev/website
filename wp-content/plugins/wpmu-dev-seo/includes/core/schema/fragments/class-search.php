<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Schema\Utils;

class Search extends Fragment {
	/**
	 * @var
	 */
	private $search_term;
	/**
	 * @var \WP_Post[]
	 */
	private $posts;
	/**
	 * @var Utils
	 */
	private $utils;
	/**
	 * @var
	 */
	private $title;
	/**
	 * @var
	 */
	private $description;

	/**
	 * @param $search_term
	 * @param $posts
	 * @param $title
	 * @param $description
	 */
	public function __construct( $search_term, $posts, $title, $description ) {
		$this->search_term = $search_term;
		$this->posts       = $posts;
		$this->title       = $title;
		$this->description = $description;
		$this->utils       = Utils::get();
	}

	/**
	 * @return array|mixed|Archive
	 */
	protected function get_raw() {
		$enabled    = (bool) $this->utils->get_schema_option( 'schema_enable_search' );
		$search_url = get_search_link( $this->search_term );

		if ( $enabled ) {
			return new Archive(
				'SearchResultsPage',
				$search_url,
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
					$this->utils->get_webpage_id( $search_url )
				);
			} else {
				return array();
			}
		}
	}
}