<?php

namespace SmartCrawl\Sitemaps;

class Provider extends \WP_Sitemaps_Provider {
	/**
	 * @var \SmartCrawl\Sitemaps\Query
	 */
	private $query;

	/**
	 * @param $name
	 * @param $query
	 */
	public function __construct( $name, $query ) {
		$this->name        = $name;
		$this->object_type = $name;

		$this->query = $query;
	}

	/**
	 * @param $page_num
	 * @param $object_subtype
	 *
	 * @return array|array[]
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		$sitemap_items = $this->query->get_items( $object_subtype, $page_num );

		return array_map( array( $this, 'convert_to_array' ), $sitemap_items );
	}

	/**
	 * @param $object_subtype
	 *
	 * @return int
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		$index_items = $this->query->get_index_items();

		return count( $index_items );
	}

	/**
	 * @param $sitemap_item \SmartCrawl\Sitemaps\General\Item
	 *
	 * @return array
	 */
	private function convert_to_array( $sitemap_item ) {
		return array(
			'loc' => $sitemap_item->get_location(),
		);
	}
}