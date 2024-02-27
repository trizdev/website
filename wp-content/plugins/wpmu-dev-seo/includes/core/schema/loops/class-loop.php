<?php

namespace SmartCrawl\Schema\Loops;

abstract class Loop {
	/**
	 * @param $id
	 * @param $post
	 *
	 * @return Loop
	 */
	public static function create( $id, $post ) {
		switch ( $id ) {
			case Woocommerce_Reviews::ID:
				return new Woocommerce_Reviews( $post );

			case Comments::ID:
				return new Comments( $post );

			default:
				return null;
		}
	}

	/**
	 * @param $property
	 *
	 * @return mixed
	 */
	abstract public function get_property_value( $property );
}