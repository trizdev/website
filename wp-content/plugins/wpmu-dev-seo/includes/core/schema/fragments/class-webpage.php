<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Entities;
use SmartCrawl\Schema\Utils;

class Webpage extends Fragment {
	/**
	 * @var Entities\Post
	 */
	private $post;
	/**
	 * @var
	 */
	private $type;
	/**
	 * @var
	 */
	private $author_id;
	/**
	 * @var
	 */
	private $publisher_id;
	/**
	 * @var Utils
	 */
	private $utils;

	/**
	 * @param $post
	 * @param $type
	 * @param $author_id
	 * @param $publisher_id
	 */
	public function __construct( $post, $type, $author_id, $publisher_id ) {
		$this->post         = $post;
		$this->type         = $type;
		$this->author_id    = $author_id;
		$this->publisher_id = $publisher_id;
		$this->utils        = Utils::get();
	}

	/**
	 * @return array|mixed|null
	 */
	protected function get_raw() {
		$post_permalink = $this->post->get_permalink();
		$post_fragment  = new Post(
			$this->post,
			$this->author_id,
			$this->publisher_id,
			true
		);

		return array(
			'@type'    => $this->type,
			'@id'      => $this->utils->get_webpage_id( $post_permalink ),
			'isPartOf' => $this->utils->get_website_id(),
			'hasPart'  => new Menu( $post_permalink ),
			'url'      => $post_permalink,
		) + $post_fragment->get_schema();
	}
}