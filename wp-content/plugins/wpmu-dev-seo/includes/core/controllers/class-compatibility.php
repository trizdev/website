<?php

namespace SmartCrawl\Controllers;

use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Sitemaps;

/**
 * Class Compatibility
 *
 * Fixes third-party compatibility issues
 */
class Compatibility extends Controller {

	use Singleton;

	/**
	 * Flags for checks.
	 *
	 * @var bool[]
	 */
	private $flags = array(
		'forminator_forms' => false,
	);

	protected function init() {
		add_filter( 'wds-omitted-shortcodes', array( $this, 'avada_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'divi_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'wpbakery_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'swift_omitted_shortcodes' ) );
		add_filter( 'bbp_register_topic_taxonomy', array( $this, 'allow_sitemap_access' ) );
		add_filter( 'bbp_register_forum_post_type', array( $this, 'allow_sitemap_access' ) );
		add_filter( 'bbp_register_topic_post_type', array( $this, 'allow_sitemap_access' ) );
		add_filter( 'bbp_register_reply_post_type', array( $this, 'allow_sitemap_access' ) );
		add_filter( 'wds-sitemaps-sitemap_url', array( $this, 'change_sitemap_url_for_domain_map' ) );
		// Forminator forms compatibility.
		add_filter( 'the_content', array( $this, 'forminator_shortcode_check' ), -1 );
		add_filter( 'wds_autolinks_can_cache_content', array( $this, 'skip_form_cache' ) );
		// Disable defender login redirect because we are not entirely sure about its security implications
		// add_filter( 'wds-report-admin-url', array( $this, 'ensure_defender_login_redirect' ) );.
		add_action( 'wu_domain_post_save', array( $this, 'wp_ultimo_clear_sitemap_cache' ) );

		return true;
	}

	/**
	 * Clear sitemap cache when WP Ultimo domain is updated.
	 *
	 * @since 3.6.3
	 *
	 * @param array $data Domain data.
	 *
	 * @return void
	 */
	public function wp_ultimo_clear_sitemap_cache( $data ) {
		// Only if current blog.
		if ( ! empty( $data['blog_id'] ) && function_exists( '\switch_to_blog' ) ) {
			// Make sure to switch to relevant blog.
			\switch_to_blog( $data['blog_id'] );
			// Invalidate sitemaps.
			Sitemaps\Controller::get()->invalidate_sitemap_cache();
			// Restore previous blog.
			\restore_current_blog();
		}
	}

	/**
	 * Set a flag for Forminator form shortcode.
	 *
	 * If a form shortcode is found on the page, we need to skip
	 * cache. Otherwise some form scripts may not work.
	 *
	 * @since 3.4.2
	 * @todo  See if we can check for editor field https://incsub.atlassian.net/browse/SMA-1272
	 *
	 * @param string $content Post content.
	 *
	 * @return string
	 */
	public function forminator_shortcode_check( $content ) {
		if ( ! empty( $content ) && class_exists( '\Forminator' ) && function_exists( '\has_shortcode' ) ) {
			// Check if current content has forms shortcode.
			$this->flags['forminator_forms'] = has_shortcode( $content, 'forminator_form' );
		}

		return $content;
	}

	/**
	 * Skip auto link object cache if forms found.
	 *
	 * If Forminator forms found on the page, skip the object cache
	 * for the auto linking.
	 *
	 * @since 3.4.2
	 *
	 * @param bool $can_cache Can we use cache?.
	 *
	 * @return bool
	 */
	public function skip_form_cache( $can_cache ) {
		if ( $can_cache && ! empty( $this->flags['forminator_forms'] ) ) {
			$can_cache = false;
		}

		return $can_cache;
	}

	public function allow_sitemap_access( $args ) {
		$request            = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		$is_sitemap_request = strpos( $request, '/sitemap.xml' ) === strlen( $request ) - strlen( '/sitemap.xml' );

		// Strip numbers from request
		$sitemap = preg_replace( '/[0-9]+/', '', $request );

		// Check if one of bbp sitemaps
		if ( in_array(
			$sitemap,
			array(
				'/forum-sitemap.xml',
				'/topic-sitemap.xml',
				'/reply-sitemap.xml',
				'/topic-tag-sitemap.xml',
			)
		) ) {
			$is_sitemap_request = true;
		}

		$sc_sitemap_active = Settings::get_setting( 'sitemap' );
		if ( $sc_sitemap_active && $is_sitemap_request ) {
			$args['show_ui'] = true;
		}

		return $args;
	}

	public function avada_omitted_shortcodes( $omitted ) {
		return array_merge(
			$omitted,
			array(
				'fusion_code',
				'fusion_imageframe',
				'fusion_slide',
				'fusion_syntax_highlighter',
			)
		);
	}

	public function divi_omitted_shortcodes( $omitted ) {
		return array_merge(
			$omitted,
			array(
				'et_pb_code',
				'et_pb_fullwidth_code',
			)
		);
	}

	public function wpbakery_omitted_shortcodes( $omitted ) {
		return array_merge(
			$omitted,
			array(
				'vc_raw_js',
				'vc_raw_html',
			)
		);
	}

	public function swift_omitted_shortcodes( $omitted ) {
		return array_merge(
			$omitted,
			array(
				'spb_raw_js',
				'spb_raw_html',
			)
		);
	}

	public function ensure_defender_login_redirect( $url ) {
		if (
			is_user_logged_in()
			|| ! method_exists( '\WP_Defender\Module\Advanced_Tools\Component\Mask_Api', 'maybeAppendTicketToUrl' )
		) {
			return $url;
		}

		return \WP_Defender\Module\Advanced_Tools\Component\Mask_Api::maybeAppendTicketToUrl( $url );
	}

	public function change_sitemap_url_for_domain_map( $sitemap_url ) {
		if (
			is_multisite()
			&& class_exists( '\domain_map' )
			&& \smartcrawl_is_switch_active( '\SMARTCRAWL_SITEMAP_DM_SIMPLE_DISCOVERY_FALLBACK' )
		) {
			$sitemap_url = ( is_network_admin() ? '../../' : ( is_admin() ? '../' : '/' ) ) . 'sitemap.xml'; // Simplest possible logic.
		}

		return $sitemap_url;
	}
}