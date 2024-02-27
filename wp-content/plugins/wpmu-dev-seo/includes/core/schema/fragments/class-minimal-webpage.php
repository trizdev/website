<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Schema\Utils;

class Minimal_Webpage extends Fragment {
	/**
	 * @var
	 */
	private $url;
	/**
	 * @var Utils
	 */
	private $utils;
	/**
	 * @var
	 */
	private $publisher_id;

	/**
	 * @param $url
	 * @param $publisher_id
	 */
	public function __construct( $url, $publisher_id ) {
		$this->url          = $url;
		$this->publisher_id = $publisher_id;
		$this->utils        = Utils::get();
	}

	/**
	 * @return array
	 */
	protected function get_raw() {
		return array(
			'@type'     => 'WebPage',
			'@id'       => $this->utils->get_webpage_id( $this->url ),
			'isPartOf'  => array(
				'@id' => $this->utils->get_website_id(),
			),
			'publisher' => array(
				'@id' => $this->publisher_id,
			),
			'url'       => $this->url,
			'hasPart'   => new Menu( $this->url ),
		);
	}
}