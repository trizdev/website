<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Integration\Woocommerce\Data;
use SmartCrawl\Schema\Utils;

class Woo_Shop extends Fragment {
	/**
	 * @var
	 */
	private $url;
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
	 * @var Data
	 */
	private $data;
	/**
	 * @var Utils
	 */
	private $utils;

	/**
	 * @param $url
	 * @param $posts
	 * @param $title
	 * @param $description
	 */
	public function __construct( $url, $posts, $title, $description ) {
		$this->url         = $url;
		$this->posts       = $posts;
		$this->title       = $title;
		$this->description = $description;
		$this->data        = new Data();
		$this->utils       = Utils::get();
	}

	/**
	 * @return array
	 */
	private function get_options() {
		return $this->data->get_options();
	}

	/**
	 * @return array|mixed|Archive
	 */
	protected function get_raw() {
		$woo_enabled = (bool) \smartcrawl_get_array_value( $this->get_options(), 'active' );
		$shop_schema = (bool) \smartcrawl_get_array_value( $this->get_options(), 'shop_schema' );

		if ( $woo_enabled && $shop_schema ) {
			return new Archive(
				'CollectionPage',
				$this->url,
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
					$this->utils->get_webpage_id( $this->url )
				);
			} else {
				return array();
			}
		}
	}
}