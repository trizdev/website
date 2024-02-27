<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Entities;
use SmartCrawl\Schema\Utils;

class Comments extends Fragment {
	/**
	 * @var Entities\Post
	 */
	private $post;
	/**
	 * @var Utils
	 */
	private $utils;

	/**
	 * @param $post
	 */
	public function __construct( $post ) {
		$this->post  = $post;
		$this->utils = Utils::get();
	}

	/**
	 * @return array
	 */
	protected function get_raw() {
		/**
		 * @var $comments \WP_Comment[]
		 */
		$comments = get_comments(
			array(
				'post_id'      => $this->post->get_post_id(),
				'status'       => 'approve',
				'hierarchical' => 'threaded',
			)
		);

		return $this->comments_to_schema( $comments, $this->post->get_permalink() );
	}

	/**
	 * @param $comments \WP_Comment[]
	 * @param $post_url
	 *
	 * @return array
	 */
	private function comments_to_schema( $comments, $post_url ) {
		$schema = array();
		foreach ( $comments as $comment ) {
			$author_id      = '#comment-author-' . md5( $comment->comment_author_email );
			$comment_schema = array(
				'@type'       => 'Comment',
				'@id'         => $this->utils->url_to_id( $post_url, '#schema-comment-' . $comment->comment_ID ),
				'text'        => $comment->comment_content,
				'dateCreated' => $comment->comment_date,
				'url'         => get_comment_link( $comment ),
				'author'      => array(
					'@type' => 'Person',
					'@id'   => $this->utils->url_to_id( $post_url, $author_id ),
					'name'  => $comment->comment_author,
				),
			);

			$children = $comment->get_children();
			if ( ! empty( $children ) ) {
				$comment_schema['comment'] = $this->comments_to_schema( $children, $post_url );
			}

			if ( ! empty( $comment->comment_author_url ) ) {
				$comment_schema['author']['url'] = $comment->comment_author_url;
			}

			$schema[] = $comment_schema;
		}

		return $schema;
	}
}