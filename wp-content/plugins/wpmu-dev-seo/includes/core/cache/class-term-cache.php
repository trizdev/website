<?php

namespace SmartCrawl\Cache;

use SmartCrawl\Singleton;
use SmartCrawl\Entities\Taxonomy_Term;

class Term_Cache {

	use Singleton;

	private $cache = array();

	public function get_term( $term_id ) {
		if ( empty( $this->cache[ $term_id ] ) ) {
			$term = new Taxonomy_Term( $term_id );
			if ( ! $term->get_wp_term() ) {
				return null;
			}
			$this->cache[ $term_id ] = $term;
		}

		return $this->cache[ $term_id ];
	}

	public function purge( $term_id ) {
		unset( $this->cache[ $term_id ] );
	}

	public function purge_all() {
		$this->cache = array();
	}
}