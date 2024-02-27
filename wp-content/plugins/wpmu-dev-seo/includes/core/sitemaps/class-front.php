<?php

namespace SmartCrawl\Sitemaps;

use SmartCrawl\Singleton;
use SmartCrawl\Controllers;

class Front extends Controllers\Controller {

	use Singleton;

	const SITEMAP_TYPE_INDEX            = 'index';
	const SITEMAP_REWRITE_RULES_FLUSHED = 'wds-sitemap-rewrite-rules-flushed';

	/**
	 * @var Sitemap[]
	 */
	private $sitemaps;

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->sitemaps = array(
			new \SmartCrawl\Sitemaps\News\Sitemap(),
			new \SmartCrawl\Sitemaps\General\Sitemap(),
		);
	}

	/**
	 * @return bool
	 */
	protected function init() {
		add_action( 'init', array( $this, 'add_rewrites' ) );
		add_action( 'wp', array( $this, 'serve_sitemaps' ), 999 );
		add_action( 'wp_sitemaps_enabled', array( $this, 'maybe_disable_native_sitemap' ) );

		return true;
	}

	/**
	 * @param $is_enabled
	 *
	 * @return false
	 */
	public function maybe_disable_native_sitemap( $is_enabled ) {
		if ( Utils::override_native() ) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * @return void
	 */
	public function add_rewrites() {
		$this->add_styling_rewrites();

		foreach ( $this->sitemaps as $sitemap ) {
			$sitemap->add_rewrites();
		}

		$this->maybe_flush_rewrite_rules();
	}

	/**
	 * @return void
	 */
	public function serve_sitemaps() {
		$this->maybe_serve_xsl_stylesheet();

		foreach ( $this->sitemaps as $sitemap ) {
			$this->serve_sitemap( $sitemap );
		}
	}

	/**
	 * @param $sitemap Sitemap
	 */
	private function serve_sitemap( $sitemap ) {
		if ( $sitemap->can_handle_request() ) {
			if ( $sitemap->is_enabled() ) {
				$sitemap->serve();
			} else {
				$sitemap->do_fallback();
			}
		}
	}

	/**
	 * @return void
	 */
	protected function maybe_flush_rewrite_rules() {
		$flushed = get_option( self::SITEMAP_REWRITE_RULES_FLUSHED, false );
		if ( SMARTCRAWL_VERSION !== $flushed ) {
			flush_rewrite_rules();
			update_option( self::SITEMAP_REWRITE_RULES_FLUSHED, SMARTCRAWL_VERSION );
		}
	}

	/**
	 * @return void
	 */
	private function add_styling_rewrites() {
		/**
		 * @var $wp \WP
		 */
		global $wp;
		$wp->add_query_var( 'wds_sitemap_styling' );
	}

	/**
	 * @return void
	 */
	private function maybe_serve_xsl_stylesheet() {
		if ( $this->is_styling_request() ) {
			$this->output_xsl();
		}
	}

	/**
	 * @return string
	 */
	private function is_styling_request() {
		return (string) get_query_var( 'wds_sitemap_styling' );
	}

	/**
	 * @return void
	 */
	private function output_xsl() {
		if ( ! headers_sent() ) {
			$whitelabel = \smartcrawl_get_array_value( $_GET, 'whitelabel' );
			$template   = \smartcrawl_get_array_value( $_GET, 'template' );
			$xsl        = \SmartCrawl\Simple_Renderer::load(
				'sitemap/sitemap-xsl',
				array(
					'whitelabel' => $whitelabel,
					'template'   => $template,
				)
			);

			status_header( 200 );
			header( 'Content-Type: text/xsl; charset=UTF-8' );

			die( $xsl );
		}
	}
}