<?php

namespace SmartCrawl\Schema\Sources;

class Comment extends Property {
	const ID = 'comment';
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
	 * @return string
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

			case 'comment_text':
				return get_comment_text( $this->comment );

			case 'comment_url':
				return get_comment_link( $this->comment );

			default:
				return '';
		}
	}
}