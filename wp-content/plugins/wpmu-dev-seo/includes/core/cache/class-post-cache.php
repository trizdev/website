<?php

namespace SmartCrawl\Cache;

use SmartCrawl\Singleton;
use SmartCrawl\Entities\Post;

class Post_Cache {

	use Singleton;

	private $cache = array();

	public function get_post( $post_id ) {
		if ( ! is_numeric( $post_id ) ) {
			return null;
		}

		if ( empty( $this->cache[ $post_id ] ) ) {
			$post = new Post( $post_id );
			if ( ! $post->get_wp_post() ) {
				return null;
			}
			$this->cache[ $post_id ] = $post;
		}

		return $this->cache[ $post_id ];
	}

	public function purge( $post_id ) {
		unset( $this->cache[ $post_id ] );
	}

	public function purge_all() {
		$this->cache = array();
	}
}