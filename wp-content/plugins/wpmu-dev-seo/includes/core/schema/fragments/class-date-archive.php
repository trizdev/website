<?php

namespace SmartCrawl\Schema\Fragments;

use SmartCrawl\Schema\Utils;

class Date_Archive extends Fragment {
	/**
	 * @var
	 */
	private $year;
	/**
	 * @var
	 */
	private $month;
	/**
	 * @var
	 */
	private $posts;
	/**
	 * @var Utils
	 */
	private $utils;
	/**
	 * @var
	 */
	private $title;
	/**
	 * @var
	 */
	private $description;

	/**
	 * @param $year
	 * @param $month
	 * @param $posts
	 * @param $title
	 * @param $description
	 */
	public function __construct( $year, $month, $posts, $title, $description ) {
		$this->year        = $year;
		$this->month       = $month;
		$this->posts       = $posts;
		$this->title       = $title;
		$this->description = $description;
		$this->utils       = Utils::get();
	}

	/**
	 * @return array|mixed|Archive
	 */
	protected function get_raw() {
		$enabled          = (bool) $this->utils->get_schema_option( 'schema_enable_date_archives' );
		$requested_year   = $this->year;
		$requested_month  = $this->month;
		$date_callback    = ! empty( $requested_year ) && empty( $requested_month )
			? 'get_year_link'
			: 'get_month_link';
		$date_archive_url = $date_callback( $requested_year, $requested_month );

		if ( $enabled ) {
			return new Archive(
				'CollectionPage',
				$date_archive_url,
				$this->posts,
				$this->title,
				$this->description
			);
		} else {
			$custom_schema_types = $this->utils->get_custom_schema_types();
			if ( $custom_schema_types ) {
				return $this->utils->add_custom_schema_types(
					array(),
					$custom_schema_types,
					$this->utils->get_webpage_id( $date_archive_url )
				);
			} else {
				return array();
			}
		}
	}
}