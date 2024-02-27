<?php

namespace SmartCrawl\Readability\Formulas;

use SmartCrawl\SmartCrawl_String;

abstract class Formula {

	abstract public function __construct( SmartCrawl_String $string, $language_code );

	/**
	 * @return int
	 */
	abstract public function get_score();
}