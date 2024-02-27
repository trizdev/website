<?php

namespace SmartCrawl\Schema\Sources;

class Options extends Property {
	const ID = 'options';

	/**
	 * @var
	 */
	private $option;
	/**
	 * @var
	 */
	private $type;

	/**
	 * @param $option
	 * @param $type
	 */
	public function __construct( $option, $type ) {
		parent::__construct();

		$this->option = $option;
		$this->type   = $type;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		if ( 'Array' !== $this->type && is_array( $this->option ) ) {
			return join( ',', $this->option );
		}

		return $this->option;
	}
}