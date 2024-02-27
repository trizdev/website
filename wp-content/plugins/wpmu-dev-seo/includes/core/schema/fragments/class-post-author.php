<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Models\User;
use SmartCrawl\Schema\Utils;

class Post_Author extends Fragment {
	/**
	 * @var Utils
	 */
	private $utils;
	/**
	 * @var User
	 */
	private $user;

	/**
	 * Post_Author constructor.
	 *
	 * @param $user User
	 */
	public function __construct( $user ) {
		$this->user  = $user;
		$this->utils = Utils::get();
	}

	/**
	 * @return string
	 */
	public function get_post_author_id() {
		return $this->get_author_id( $this->user );
	}

	/**
	 * @return mixed|void
	 */
	protected function get_raw() {
		$name = $this->utils->get_user_full_name( $this->user );

		$schema = array(
			'@type' => 'Person',
			'@id'   => $this->get_post_author_id(),
			'name'  => $name,
		);

		$url = $this->get_user_url( $this->user );
		if ( (bool) $this->utils->get_schema_option( 'schema_enable_author_url' ) ) {
			$schema['url'] = $url;
		}

		$description = $this->user->get_description();
		if ( $description ) {
			$schema['description'] = $description;
		}

		if ( $this->utils->is_author_gravatar_enabled() ) {
			$schema['image'] = $this->utils->get_image_schema(
				$this->utils->url_to_id( $url, '#schema-author-gravatar' ),
				$this->user->get_avatar_url( 100 ),
				100,
				100,
				$name
			);
		}

		$urls = $this->get_user_urls( $this->user );
		if ( $urls ) {
			$schema['sameAs'] = $urls;
		}

		return $this->utils->apply_filters( 'user-data', $schema, $this->user );
	}

	/**
	 * @param $user User
	 *
	 * @return mixed
	 */
	private function get_user_url( $user ) {
		return $this->utils->apply_filters( 'user-url', $user->get_user_url(), $user );
	}

	/**
	 * @param $user User
	 *
	 * @return mixed
	 */
	public function get_user_urls( $user ) {
		return $this->utils->apply_filters( 'user-urls', $user->get_user_urls(), $user );
	}

	/**
	 * @param $user
	 *
	 * @return string
	 */
	private function get_author_id( $user ) {
		$url = get_author_posts_url( $user->get_id() );

		return $this->utils->url_to_id( $url, '#schema-author' );
	}
}