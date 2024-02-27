<?php

namespace SmartCrawl\Sitemaps;

use SmartCrawl\Admin\Settings\Admin_Settings;
use SmartCrawl\Settings;

abstract class Sitemap {
	const SITEMAP_TYPE_INDEX = 'index';

	/**
	 * @return mixed
	 */
	abstract public function add_rewrites();

	/**
	 * @return mixed
	 */
	abstract public function can_handle_request();

	/**
	 * @return mixed
	 */
	abstract public function do_fallback();

	/**
	 * @return mixed
	 */
	abstract public function serve();

	/**
	 * @return bool
	 */
	public function is_enabled() {
		return Settings::get_setting( 'sitemap' )
			&& Admin_Settings::is_tab_allowed( Settings::TAB_SITEMAP );
	}

	/**
	 * @param $xml
	 * @param $gzip
	 *
	 * @return void
	 */
	protected function output_xml( $xml, $gzip ) {
		if ( ! headers_sent() ) {
			status_header( 200 );
			// Prevent the search engines from indexing the XML Sitemap.
			header( 'X-Robots-Tag: noindex, follow' );
			header( 'Content-Type: text/xml; charset=UTF-8' );

			if (
				$this->is_gzip_supported()
				&& function_exists( 'gzencode' )
				&& $gzip
			) {
				header( 'Content-Encoding: gzip' );
				$xml = gzencode( $xml );
			}
			die( $xml );
		}
	}

	/**
	 * @return bool
	 */
	private function is_gzip_supported() {
		$accepted = (string) \smartcrawl_get_array_value( $_SERVER, 'HTTP_ACCEPT_ENCODING' );

		return stripos( $accepted, 'gzip' ) !== false;
	}

	/**
	 * @return void
	 */
	protected function do_404() {
		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
	}
}