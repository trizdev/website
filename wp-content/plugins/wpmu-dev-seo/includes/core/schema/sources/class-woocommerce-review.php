<?php

namespace SmartCrawl\Schema\Sources;

class Woocommerce_Review extends Property {
	const ID = 'woocommerce_review';

	/**
	 * @var
	 */
	private $comment;
	/**
	 * @var
	 */
	private $field;

	/**
	 * @param $comment
	 * @param $field
	 */
	public function __construct( $comment, $field ) {
		parent::__construct();

		$this->comment = $comment;
		$this->field   = $field;
	}

	/**
	 * @return mixed|string
	 */
	public function get_value() {
		if ( empty( $this->comment ) ) {
			return '';
		}

		switch ( $this->field ) {
			case 'comment_date':
				return get_comment_date( 'c', $this->comment );

			case 'comment_author_name':
				return get_comment_author( $this->comment );

			case 'rating_value':
				return get_comment_meta( $this->comment->comment_ID, 'rating', true );

			case 'comment_text':
				return get_comment_text( $this->comment );

			default:
				return '';
		}
	}
}