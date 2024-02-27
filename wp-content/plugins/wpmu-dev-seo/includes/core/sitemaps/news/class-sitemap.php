<?php

namespace SmartCrawl\Sitemaps\News;

use SmartCrawl\Simple_Renderer;
use SmartCrawl\Sitemaps;

class Sitemap extends Sitemaps\Sitemap {
	/**
	 * @return void
	 */
	public function add_rewrites() {
		/**
		 * WP.
		 *
		 * @var $wp \WP
		 */
		global $wp;

		$wp->add_query_var( 'wds_news_sitemap' );
		$wp->add_query_var( 'wds_news_sitemap_type' );
		$wp->add_query_var( 'wds_news_sitemap_page' );
		$wp->add_query_var( 'wds_news_sitemap_gzip' );

		add_rewrite_rule( '^news-sitemap\.xml(\.gz)?$', 'index.php?wds_news_sitemap=1&wds_news_sitemap_type=index&wds_news_sitemap_gzip=$matches[1]', 'top' );
		add_rewrite_rule( '^news-([^/]+?)-sitemap([0-9]+)?\.xml(\.gz)?$', 'index.php?wds_news_sitemap=1&wds_news_sitemap_type=$matches[1]&wds_news_sitemap_page=$matches[2]&wds_news_sitemap_gzip=$matches[3]', 'top' );
	}

	/**
	 * @return bool
	 */
	public function is_enabled() {
		return parent::is_enabled()
			&& Sitemaps\Utils::get_sitemap_option( 'enable-news-sitemap' );
	}

	/**
	 * @return bool
	 */
	public function can_handle_request() {
		return (bool) get_query_var( 'wds_news_sitemap' );
	}

	/**
	 * @return void
	 */
	public function do_fallback() {
		$this->do_404();
	}

	/**
	 * @return string
	 */
	private function cache_type( $type ) {
		return "news-{$type}";
	}

	/**
	 * @return void
	 */
	public function serve() {
		$sitemap_type = $this->get_sitemap_type_var();
		$sitemap_page = $this->get_sitemap_page_var();

		$sitemap_cache = Sitemaps\Cache::get();
		$cached        = $sitemap_cache->get_cached(
			$this->cache_type( $sitemap_type ),
			$sitemap_page
		);
		$gzip          = $this->is_gzip_request();

		if ( ! empty( $cached ) ) {
			$this->output_xml( $cached, $gzip );

			return;
		}

		do_action( 'wds_before_news_sitemap_rebuild' );

		if ( self::SITEMAP_TYPE_INDEX === $sitemap_type ) {
			$xml = $this->build_index();
		} else {
			$xml = $this->build_partial_sitemap( $sitemap_type, $sitemap_page );
		}

		$sitemap_cache->set_cached(
			$this->cache_type( $sitemap_type ),
			$sitemap_page,
			$xml
		);
		$this->output_xml( $xml, $gzip );
	}

	/**
	 * @return false|string
	 */
	private function build_partial_sitemap( $type, $page ) {
		$items = array();
		foreach ( $this->get_queries() as $query ) {
			if ( $query->can_handle_type( $type ) ) {
				$items = array_merge(
					$items,
					$query->get_items( $type, $page )
				);
				break;
			}
		}

		$items = apply_filters( 'wds_partial_news_sitemap_items', $items, $type, $page );

		if ( empty( $items ) ) {
			return false;
		}

		return $this->build_xml( $items );
	}

	/**
	 * @return void
	 */
	private function post_process() {
		do_action( 'wds_news_sitemap_created' );
	}

	/**
	 * @return string
	 */
	private function build_index() {
		$index_items = array();

		foreach ( $this->get_queries() as $query ) {
			$index_items = array_merge(
				$index_items,
				$query->get_index_items()
			);
		}

		$this->post_process();

		return $this->build_index_xml( $index_items );
	}

	/**
	 * @return string
	 */
	private function get_sitemap_type_var() {
		return (string) get_query_var( 'wds_news_sitemap_type' );
	}

	/**
	 * @return int
	 */
	private function get_sitemap_page_var() {
		return (int) get_query_var( 'wds_news_sitemap_page' );
	}

	/**
	 * @return bool
	 */
	private function is_gzip_request() {
		$query_var = get_query_var( 'wds_news_sitemap_gzip' );

		return ! empty( $query_var );
	}

	/**
	 * @return Query[]
	 */
	private function get_queries() {
		return array(
			new Query(),
		);
	}

	/**
	 * @return string
	 */
	public function build_xml( $items ) {
		return Simple_Renderer::load(
			'sitemap/sitemap-news-xml',
			array(
				'news_items' => $items,
			)
		);
	}

	/**
	 * @return string
	 */
	private function build_index_xml( $index_items ) {
		return Simple_Renderer::load(
			'sitemap/sitemap-index-xml',
			array(
				'index_items' => $index_items,
			)
		);
	}
}