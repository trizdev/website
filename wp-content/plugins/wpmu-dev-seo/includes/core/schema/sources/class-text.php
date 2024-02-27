<?php

namespace SmartCrawl\Schema\Sources;

class Text extends Property {
	const ID = 'custom_text';

	/**
	 * @var
	 */
	private $text;

	/**
	 * @param $text
	 */
	public function __construct( $text ) {
		parent::__construct();
		$this->text = $text;
	}

	/**
	 * @return mixed
	 */
	public function get_value() {
		return $this->text;
	}
}