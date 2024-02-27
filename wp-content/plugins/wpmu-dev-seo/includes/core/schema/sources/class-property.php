<?php

namespace SmartCrawl\Schema\Sources;

use SmartCrawl\Schema\Utils;

abstract class Property {
	/**
	 * @var Utils
	 */
	protected $utils;

	/**
	 *
	 */
	public function __construct() {
		$this->utils = Utils::get();
	}

	/**
	 * @return mixed
	 */
	abstract public function get_value();
}