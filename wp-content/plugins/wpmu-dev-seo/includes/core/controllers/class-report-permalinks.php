<?php

namespace SmartCrawl\Controllers;

use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Admin\Settings\Admin_Settings;

class Report_Permalinks extends Controller {

	use Singleton;

	const ACTION_QV = 'load-report';

	const ACTION_AUDIT_REPORT = 'seo-audit';

	const ACTION_CRAWL_REPORT = 'sitemap-crawler';

	/**
	 * Child controllers can use this method to initialize.
	 *
	 * @return bool
	 */
	protected function init() {
		add_action( 'wp', array( $this, 'intercept' ) );

		return true;
	}

	public function intercept() {
		if ( ! is_front_page() || ! isset( $_GET[ self::ACTION_QV ] ) ) { // phpcs:ignore
			return;
		}

		$url = false;

		if ( $_GET[ self::ACTION_QV ] === self::ACTION_AUDIT_REPORT ) { // phpcs:ignore
			$url = Admin_Settings::admin_url( Settings::TAB_HEALTH );
		} elseif ( $_GET[ self::ACTION_QV ] === self::ACTION_CRAWL_REPORT ) { // phpcs:ignore
			$url = Admin_Settings::admin_url( Settings::TAB_SITEMAP );
		}

		if ( $url ) {
			wp_safe_redirect( apply_filters( 'wds-report-admin-url', $url ) ); // phpcs:ignore
			exit;
		}
	}
}