<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Schema\Utils;

class Tax_Archive extends Fragment {
	/**
	 * @var
	 */
	private $term;
	/**
	 * @var
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
	 * @param $term
	 * @param $posts
	 * @param $title
	 * @param $description
	 */
	public function __construct( $term, $posts, $title, $description ) {
		$this->term        = $term;
		$this->posts       = $posts;
		$this->title       = $title;
		$this->description = $description;
		$this->utils       = Utils::get();
	}

	/**
	 * @return array|mixed|Archive
	 */
	protected function get_raw() {
		$enabled  = (bool) $this->utils->get_schema_option( 'schema_enable_taxonomy_archives' );
		$disabled = (bool) $this->utils->get_schema_option(
			array(
				'schema_disabled_taxonomy_archives',
				$this->term->taxonomy,
			)
		);
		$term_url = get_term_link( $this->term, $this->term->taxonomy );

		if ( $enabled && ! $disabled ) {
			return new Archive(
				'CollectionPage',
				$term_url,
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
					$this->utils->get_webpage_id( $term_url )
				);
			} else {
				return array();
			}
		}
	}
}