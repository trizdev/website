<?php

namespace SmartCrawl\Schema\Sources;

class Media extends Property {
	const OBJECT = 'image';
	const URL    = 'image_url';

	/**
	 * @var
	 */
	private $media_id;
	/**
	 * @var
	 */
	private $field;

	/**
	 * @param $media_id
	 * @param $field
	 */
	public function __construct( $media_id, $field ) {
		parent::__construct();

		$this->media_id = $media_id;
		$this->field    = $field;
	}

	/**
	 * @return array|mixed|string
	 */
	public function get_value() {
		if ( self::URL === $this->field ) {
			$image_source = $this->utils->get_attachment_image_source( $this->media_id );

			return $image_source
				? $image_source[0]
				: '';
		} else {
			return $this->utils->get_media_item_image_schema(
				$this->media_id,
				home_url( "/#image-$this->media_id" )
			);
		}
	}
}