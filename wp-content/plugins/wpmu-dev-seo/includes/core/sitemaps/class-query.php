<?php

namespace SmartCrawl\Sitemaps;

use SmartCrawl\Work_Unit;

abstract class Query extends Work_Unit {

	const NO_LIMIT = PHP_INT_MAX;

	/**
	 * @param string $type
	 * @param int    $page_number
	 *
	 * @return General\Item[] Array of sitemap items
	 */
	abstract public function get_items( $type = '', $page_number = 0 );

	public function get_item_count( $type = '' ) {
		return count( $this->get_items( $type ) );
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	public function can_handle_type( $type ) {
		$allowed = $this->get_supported_types();

		return in_array( $type, $allowed, true );
	}

	/**
	 * @return mixed
	 */
	abstract public function get_supported_types();

	/**
	 * @param $page_number
	 *
	 * @return int
	 */
	protected function get_limit( $page_number ) {
		if ( 0 === $page_number ) { // 0 means all items are requested.
			return self::NO_LIMIT;
		}

		// Otherwise return the limit based on page number.
		return Utils::get_items_per_sitemap();
	}

	/**
	 * @param $page_number
	 *
	 * @return float|int
	 */
	protected function get_offset( $page_number ) {
		return $page_number > 1
			? ( $page_number - 1 ) * Utils::get_items_per_sitemap()
			: 0;
	}

	/**
	 * @param $haystack string
	 *
	 * @return array
	 */
	protected function find_images( $haystack ) {
		preg_match_all( '|(<img [^>]+?>)|', $haystack, $matches, PREG_SET_ORDER );
		if ( ! $matches ) {
			return array();
		}

		$images = array();
		foreach ( $matches as $tmp ) {
			$img = $tmp[0];

			$res = preg_match( '/src=(["\'])([^"\']+)(["\'])/', $img, $match );
			$src = $res ? $match[2] : '';
			if ( strpos( $src, 'http' ) !== 0 ) {
				$src = site_url( $src );
			}

			$res   = preg_match( '/title=(["\'])([^"\']+)(["\'])/', $img, $match );
			$title = $res ? str_replace( '-', ' ', str_replace( '_', ' ', $match[2] ) ) : '';

			$res = preg_match( '/alt=(["\'])([^"\']+)(["\'])/', $img, $match );
			$alt = $res ? str_replace( '-', ' ', str_replace( '_', ' ', $match[2] ) ) : '';

			$images[] = array(
				'src'   => $src,
				'title' => $title,
				'alt'   => $alt,
			);
		}

		return $images;
	}

	/**
	 * @return Index_Item[]
	 */
	public function get_index_items() {
		$types       = $this->get_supported_types();
		$index_items = array();
		foreach ( $types as $type ) {
			$index_items_for_type = $this->get_index_items_for_type( $type );

			$index_items = array_merge(
				$index_items,
				$index_items_for_type
			);
		}

		return $index_items;
	}

	/**
	 * @param $type
	 *
	 * @return array
	 */
	protected function get_index_items_for_type( $type ) {
		return $this->make_index_items( $type );
	}

	/**
	 * @param $type
	 * @param $sitemap_num
	 *
	 * @return string|void
	 */
	protected function get_index_item_url( $type, $sitemap_num ) {
		return home_url( "/$type-sitemap$sitemap_num.xml" );
	}

	/**
	 * @param $type string
	 *
	 * @return array
	 */
	protected function make_index_items( $type ) {
		$per_sitemap = Utils::get_items_per_sitemap();
		$item_count  = $this->get_item_count( $type );
		if ( empty( $per_sitemap ) ) {
			return array();
		}

		$sitemap_count = (int) ceil( $item_count / $per_sitemap );
		$index_items   = array();

		for ( $sitemap_num = 1; $sitemap_num <= $sitemap_count; $sitemap_num++ ) {
			$location = $this->get_index_item_url( $type, $sitemap_num );

			$index_item = new Index_Item();
			$index_item->set_location( $location );

			$index_items[] = $index_item;
		}

		return $index_items;
	}
}