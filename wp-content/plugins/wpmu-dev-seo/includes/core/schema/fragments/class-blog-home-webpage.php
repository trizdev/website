<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Schema\Utils;
use SmartCrawl\Cache\Object_Cache;

class Blog_Home_Webpage extends Fragment {
	/**
	 * @var Utils
	 */
	private $utils;
	/**
	 * @var
	 */
	private $publisher_id;
	/**
	 * @var
	 */
	private $title;
	/**
	 * @var
	 */
	private $description;

	/**
	 * @param $title
	 * @param $description
	 * @param $publisher_id
	 */
	public function __construct( $title, $description, $publisher_id ) {
		$this->title        = $title;
		$this->description  = $description;
		$this->publisher_id = $publisher_id;
		$this->utils        = Utils::get();
	}

	/**
	 * @return array
	 */
	protected function get_raw() {
		$site_url = get_site_url();
		$schema   = array(
			'@type'      => 'WebPage',
			'@id'        => $this->utils->get_webpage_id( $site_url ),
			'url'        => $site_url,
			'name'       => $this->title,
			'inLanguage' => get_bloginfo( 'language' ),
			'isPartOf'   => array(
				'@id' => $this->utils->get_website_id(),
			),
			'publisher'  => array(
				'@id' => $this->publisher_id,
			),
		);

		if ( $this->description ) {
			$schema['description'] = $this->utils->apply_filters( 'site-data-description', $this->description );
		}

		$last_post_date = get_lastpostmodified( 'blog' );
		if ( $last_post_date ) {
			$schema['dateModified'] = $last_post_date;
		}

		$schema['hasPart'] = new Menu( $site_url );

		return $schema;
	}
}