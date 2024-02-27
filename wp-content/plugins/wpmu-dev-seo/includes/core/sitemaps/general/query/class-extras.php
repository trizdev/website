<?php

namespace SmartCrawl\Sitemaps\General\Queries;

use SmartCrawl\Singleton;
use SmartCrawl\Sitemaps\General\Item;
use SmartCrawl\Sitemaps\Query;

class Extras extends Query {

	use Singleton;

	const EXTRAS         = 'extras';
	const EXTRAS_STORAGE = 'wds-sitemap-extras';

	/**
	 * @return string[]
	 */
	public function get_supported_types() {
		return array( self::EXTRAS );
	}

	/**
	 * @return array|Item[]
	 */
	public function get_items( $type = '', $page_number = 0 ) {
		$extras = get_option( self::EXTRAS_STORAGE );
		$extras = empty( $extras ) || ! is_array( $extras )
			? array()
			: $extras;

		if ( ! empty( $page_number ) ) {
			$limit  = $this->get_limit( $page_number );
			$offset = $this->get_offset( $page_number );
			$extras = array_slice( $extras, $offset, $limit );
		}

		$items = array();
		foreach ( $extras as $extra_url ) {
			if ( \SmartCrawl\Sitemaps\Utils::is_url_ignored( $extra_url ) ) {
				continue;
			}

			$item = new Item();
			$item->set_location( $extra_url )
				->set_last_modified( time() );

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * @return string
	 */
	public function get_filter_prefix() {
		return 'wds-sitemap-extras';
	}
}