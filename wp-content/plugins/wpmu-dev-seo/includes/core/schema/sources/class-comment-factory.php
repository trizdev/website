<?php

namespace SmartCrawl\Schema\Sources;

class Comment_Factory extends Factory {
	/**
	 * @var
	 */
	private $comment;

	/**
	 * @param $post
	 * @param $comment
	 */
	public function __construct( $post, $comment ) {
		parent::__construct( $post );
		$this->comment = $comment;
	}

	/**
	 * @param $source
	 * @param $field
	 * @param $type
	 *
	 * @return Comment|Text
	 */
	public function create( $source, $field, $type ) {
		if ( empty( $this->comment ) ) {
			return $this->create_default_source();
		}

		if ( Comment::ID === $source ) {
			return new Comment( $this->comment, $field );
		}

		return parent::create( $source, $field, $type );
	}
}