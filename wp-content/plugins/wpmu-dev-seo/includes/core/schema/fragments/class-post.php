<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Entities;
use SmartCrawl\Schema\Utils;

class Post extends Fragment {
	/**
	 * @var Utils
	 */
	private $utils;
	/**
	 * @var Entities\Post
	 */
	private $post;
	/**
	 * @var
	 */
	private $author_id;
	/**
	 * @var
	 */
	private $include_comments;
	/**
	 * @var
	 */
	private $publisher_id;

	/**
	 * @param $post
	 * @param $author_id
	 * @param $publisher_id
	 * @param $include_comments
	 */
	public function __construct( $post, $author_id, $publisher_id, $include_comments ) {
		$this->post             = $post;
		$this->publisher_id     = $publisher_id;
		$this->author_id        = $author_id;
		$this->include_comments = $include_comments;
		$this->utils            = Utils::get();
	}

	/**
	 * @return mixed
	 */
	protected function get_raw() {
		$wp_post = $this->utils->apply_filters(
			'post',
			$this->post->get_wp_post()
		);

		$headline    = $this->post->get_meta_title();
		$description = $this->post->get_meta_description();

		$author_schema = $this->author_id
			? array( '@id' => $this->author_id ) // An author has already been added, just link to it.
			: new Post_Author( $this->post->get_post_author() );

		$schema = array(
			'author'        => $author_schema,
			'publisher'     => array( '@id' => $this->publisher_id ),
			'dateModified'  => get_the_modified_date( 'Y-m-d\TH:i:s', $wp_post ),
			'datePublished' => get_the_date( 'Y-m-d\TH:i:s', $wp_post ),
			'headline'      => $this->utils->apply_filters( 'post-data-headline', $headline, $wp_post ),
			'description'   => $description,
			'name'          => $this->utils->apply_filters( 'post-data-name', get_the_title( $wp_post ), $wp_post ),
		);

		$enable_comments = (bool) $this->utils->get_schema_option( 'schema_enable_comments' );
		if ( $this->include_comments && $enable_comments ) {
			$schema['commentCount'] = get_comments_number( $this->post->get_post_id() );
			$schema['comment']      = new Comments( $this->post );
		}

		return $this->add_article_image( $schema );
	}

	/**
	 * @param $schema
	 *
	 * @return mixed
	 */
	private function add_article_image( $schema ) {
		$thumbnail_id = $this->post->get_thumbnail_id();

		if ( $thumbnail_id ) {
			$image_id = $thumbnail_id;
		} else {
			$image_id = (int) $this->utils->get_schema_option( 'schema_default_image' );
		}

		if ( $image_id ) {
			$schema['image']        = $this->filter_post_data_image(
				$this->utils->get_media_item_image_schema(
					$image_id,
					$this->utils->url_to_id( $this->post->get_permalink(), '#schema-article-image' )
				)
			);
			$schema['thumbnailUrl'] = (string) $this->utils->apply_filters(
				'post-data-thumbnailUrl',
				\smartcrawl_get_array_value( $schema, array( 'image', 'url' ) )
			);
		}
		return $schema;
	}

	/**
	 * @param $schema_image
	 *
	 * @return mixed|void
	 */
	private function filter_post_data_image( $schema_image ) {
		return $this->utils->apply_filters( 'post-data-image', $schema_image );
	}
}