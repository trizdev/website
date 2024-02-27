<?php

namespace SmartCrawl\Schema\Fragments;

abstract class Fragment {
	/**
	 * @var null
	 */
	private $schema = null;

	/**
	 * @return array|mixed|null
	 */
	public function get_schema() {
		if ( is_null( $this->schema ) ) {
			$this->schema = $this->make_schema();
		}

		return $this->schema;
	}

	/**
	 * @return mixed
	 */
	abstract protected function get_raw();

	/**
	 * @return array|mixed|null
	 */
	private function make_schema() {
		/**
		 * Action hook to run before making schema.
		 *
		 * @since 3.8.0
		 */
		do_action( 'smartcrawl_before_make_schema' );

		$schema = $this->process_schema_item( $this->get_raw() );

		/**
		 * Action hook to run after making schema.
		 *
		 * @since 3.8.0
		 *
		 * @param array $schema Schema data.
		 */
		do_action( 'smartcrawl_after_make_schema' );

		return $schema;
	}

	/**
	 * @param $schema_item
	 *
	 * @return array|mixed|null
	 */
	private function process_schema_item( $schema_item ) {
		if ( is_a( $schema_item, self::class ) ) {
			return $schema_item->get_schema();
		} elseif ( is_array( $schema_item ) ) {
			return $this->traverse_schema_array( $schema_item );
		} else {
			return $schema_item;
		}
	}

	/**
	 * @param $schema
	 *
	 * @return array
	 */
	private function traverse_schema_array( $schema ) {
		$new_schema   = array();
		$keys_numeric = true;

		foreach ( $schema as $schema_item_id => $schema_item ) {
			$keys_numeric = $keys_numeric && is_numeric( $schema_item_id );

			$processed_schema_item = $this->process_schema_item( $schema_item );

			if ( false !== $processed_schema_item ) {
				$new_schema[ $schema_item_id ] = $processed_schema_item;
			}
		}

		return $keys_numeric
			? array_values( $new_schema )
			: $new_schema;
	}
}