<?php

namespace SmartCrawl\Schema\Types;

class Woo_Product extends Type {
	const TYPE          = 'WooProduct';
	const TYPE_SIMPLE   = 'WooSimpleProduct';
	const TYPE_VARIABLE = 'WooVariableProduct';

	/**
	 * @return string
	 */
	public function get_type() {
		return 'Product';
	}

	/**
	 * @return bool
	 */
	public function is_active() {
		return parent::is_active() && \smartcrawl_woocommerce_active();
	}
}