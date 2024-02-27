<?php

namespace SmartCrawl\Schema\Sources;

class Site_Settings extends Property {
	const ID          = 'site_settings';
	const NAME        = 'site_name';
	const DESCRIPTION = 'site_description';
	const URL         = 'site_url';
	const ADMIN_EMAIL = 'site_admin_email';

	/**
	 * @var
	 */
	private $setting;

	/**
	 * @param $setting
	 */
	public function __construct( $setting ) {
		parent::__construct();

		$this->setting = $setting;
	}

	/**
	 * @return mixed|string|void
	 */
	public function get_value() {
		$setting = str_replace( 'site_', '', $this->setting );
		return get_bloginfo( $setting );
	}
}