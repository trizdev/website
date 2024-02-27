<?php

namespace SmartCrawl\Lighthouse;

class Group {
	/**
	 * @var Checks\Check[]
	 */
	private $checks = array();
	/**
	 * @var
	 */
	private $label;
	/**
	 * @var
	 */
	private $description;
	/**
	 * @var
	 */
	private $id;

	/**
	 * @param $id
	 * @param $label
	 * @param $description
	 * @param $report
	 * @param $checks
	 */
	public function __construct( $id, $label, $description, $report, $checks ) {
		$this->id          = $id;
		$this->label       = $label;
		$this->description = $description;

		foreach ( $checks as $check_id ) {
			$check                     = Checks\Check::create( $check_id, $report );
			$this->checks[ $check_id ] = $check;
		}
	}

	/**
	 * @return Checks\Check[]
	 */
	public function get_checks() {
		return $this->checks;
	}

	/**
	 * @param $check_id
	 *
	 * @return Checks\Check
	 */
	public function get_check( $check_id ) {
		return \smartcrawl_get_array_value( $this->checks, $check_id );
	}

	/**
	 * @return mixed
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * @return mixed
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function get_failing_count() {
		$failing_count = 0;
		foreach ( $this->checks as $check ) {
			if ( ! $check->is_passed() ) {
				++$failing_count;
			}
		}
		return $failing_count;
	}

	/**
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}
}