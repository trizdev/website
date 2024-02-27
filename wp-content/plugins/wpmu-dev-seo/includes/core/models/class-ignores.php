<?php

namespace SmartCrawl\Models;

class Ignores extends Model {

	const IGNORES_SEO_STORAGE = 'wds-ignores';

	private $ignores = array();

	private $ignores_storage;

	public function __construct() {
		$this->ignores_storage = self::IGNORES_SEO_STORAGE;

		$this->load();
	}

	/**
	 * Loads the ignores list
	 *
	 * @return bool Status
	 */
	public function load() {
		$this->ignores = array();

		$ignores = get_option( $this->get_ignores_storage() );

		if ( ! empty( $ignores ) && is_array( $ignores ) ) {
			$this->ignores = array_filter( array_unique( $ignores ) );

			return true;
		}

		return false;
	}

	public function get_type() {
		return 'ignores';
	}

	/**
	 * Clears the persisted ignores list
	 *
	 * @return bool Status
	 */
	public function clear() {
		return update_option( $this->get_ignores_storage(), array() );
	}

	/**
	 * Adds ignored item to ignores list
	 *
	 * @param string $key Item key to ignore.
	 *
	 * @return bool Status
	 */
	public function set_ignore( $key ) {
		if ( empty( $key ) ) {
			return false;
		}
		if ( ! $this->is_valid_ignore_key( $key ) ) {
			return false;
		}

		$this->ignores[] = $key;

		return $this->set_ignores( $this->ignores );
	}

	public function set_ignores( $keys ) {
		$this->ignores = array_filter( array_unique( $keys ) );

		return update_option( $this->get_ignores_storage(), $this->ignores );
	}

	/**
	 * Check if a string is valid ignored issue identifier
	 *
	 * @param string $key String to check.
	 *
	 * @return bool Valid state
	 */
	public function is_valid_ignore_key( $key ) {
		if ( ! is_string( $key ) ) {
			return false;
		}

		return ! ! preg_match( '/^[a-z0-9-_]+$/i', $key );
	}

	/**
	 * Removes ignored item from ignores list
	 *
	 * @param string $key Item key to remove from ignores.
	 *
	 * @return bool Status
	 */
	public function unset_ignore( $key ) {
		if ( empty( $key ) ) {
			return false;
		}
		if ( ! $this->is_valid_ignore_key( $key ) ) {
			return false;
		}

		$index = array_search( $key, $this->ignores, true );
		if ( false !== $index ) {
			unset( $this->ignores[ $index ] );
		}

		$this->ignores = array_filter( array_unique( $this->ignores ) );

		return update_option( $this->get_ignores_storage(), $this->ignores );
	}

	/**
	 * Checks if an issue is to be ignored.
	 *
	 * @param string $key Key.
	 *
	 * @return bool
	 */
	public function is_ignored( $key ) {
		return (bool) in_array( $key, $this->get_all(), true );
	}

	public function is_not_ignored( $key ) {
		return ! $this->is_ignored( $key );
	}

	/**
	 * Gets a list of ignored items
	 *
	 * @return array List of ignored items unique IDs
	 */
	public function get_all() {
		return array_unique( $this->ignores );
	}

	private function get_ignores_storage() {
		return $this->ignores_storage;
	}
}