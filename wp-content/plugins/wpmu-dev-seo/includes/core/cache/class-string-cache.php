<?php

namespace SmartCrawl\Cache;

use SmartCrawl\Singleton;
use SmartCrawl\SmartCrawl_String;

class String_Cache {

	use Singleton;

	private $cache = array();

	/**
	 * Get string.
	 *
	 * @param string $string   String.
	 * @param string $language Language.
	 *
	 * @return SmartCrawl_String
	 */
	public function get_string( $string, $language ) {
		$key = $this->make_key( $string, $language );
		if ( empty( $this->cache[ $key ] ) ) {
			$this->cache[ $key ] = new SmartCrawl_String( $string, $language );
		}

		return $this->cache[ $key ];
	}

	public function purge( $string, $language ) {
		$key = $this->make_key( $string, $language );

		unset( $this->cache[ $key ] );
	}

	public function purge_all() {
		$this->cache = array();
	}

	private function make_key( $string, $language ) {
		return md5( "$string-$language" );
	}
}