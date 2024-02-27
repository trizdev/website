<?php

namespace SmartCrawl\Schema\Sources;

class Woocommerce_Review_Factory extends Factory {
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
	 * @return Author|Media|Options|Post|Post_Meta|Schema_Settings|SEO_Meta|Site_Settings|Text|Woocommerce|Woocommerce_Review
	 */
	public function create( $source, $field, $type ) {
		if ( empty( $this->comment ) ) {
			return $this->create_default_source();
		}

		if ( Woocommerce_Review::ID === $source ) {
			return new Woocommerce_Review( $this->comment, $field );
		}

		return parent::create( $source, $field, $type );
	}
}