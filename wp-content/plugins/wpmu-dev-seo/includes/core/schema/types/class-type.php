<?php

namespace SmartCrawl\Schema\Types;

class Type {
	/**
	 * @var \SmartCrawl\Schema\Utils
	 */
	protected $utils;
	/**
	 * @var \WP_Post
	 */
	protected $post;
	/**
	 * @var array
	 */
	protected $type;

	private $is_front_page;

	/**
	 * Type constructor.
	 *
	 * @param $type array
	 * @param $post \WP_Post
	 * @param $is_front_page
	 */
	private function __construct( $type, $post, $is_front_page ) {
		$this->utils         = \SmartCrawl\Schema\Utils::get();
		$this->type          = $type;
		$this->post          = $post;
		$this->is_front_page = $is_front_page;
	}

	/**
	 * @return bool
	 */
	public function conditions_met() {
		$conditions = \smartcrawl_get_array_value( $this->type, 'conditions' );
		if ( is_null( $conditions ) ) {
			return false;
		}

		$conditions_helper = new \SmartCrawl\Schema\Type_Conditions( $conditions, $this->post, $this->is_front_page );

		return $conditions_helper->met();
	}

	/**
	 * @return mixed|null
	 */
	public function get_type() {
		return \smartcrawl_get_array_value( $this->type, 'type' );
	}

	/**
	 * @return array
	 */
	public function get_schema() {
		$type       = $this->get_type();
		$properties = \smartcrawl_get_array_value( $this->type, 'properties' );

		if ( is_null( $type ) || is_null( $properties ) ) {
			return array();
		}

		$factory               = new \SmartCrawl\Schema\Sources\Factory( $this->post );
		$property_value_helper = new \SmartCrawl\Schema\Property_Values( $factory, $this->post );
		$property_values       = $property_value_helper->get_property_values( $properties );

		if ( empty( $property_values ) ) {
			return array();
		}

		return array_merge(
			array( '@type' => $type ),
			$property_values
		);
	}

	/**
	 * @return bool
	 */
	public function is_active() {
		return ! \smartcrawl_get_array_value( $this->type, 'disabled' );
	}

	/**
	 * @param $data
	 * @param $post
	 * @param $is_front_page
	 *
	 * @return Type|Woo_Product
	 */
	public static function create( $data, $post, $is_front_page ) {
		$type = \smartcrawl_get_array_value( $data, 'type' );

		switch ( $type ) {
			case Woo_Product::TYPE:
			case Woo_Product::TYPE_SIMPLE:
			case Woo_Product::TYPE_VARIABLE:
				return new Woo_Product( $data, $post, false );

			default:
				return new self( $data, $post, $is_front_page );
		}
	}
}