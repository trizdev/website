<?php

namespace SmartCrawl\Controllers;

use SmartCrawl\Settings;
use SmartCrawl\Simple_Renderer;
use SmartCrawl\Singleton;

class Onboard extends Controller {

	use Singleton;

	const ONBOARDING_DONE_OPTION = 'wds-onboarding-done';

	/**
	 * Dispatches action listeners for admin pages
	 *
	 * @return void
	 */
	public function dispatch_actions() {
		add_action( 'wds-dshboard-after_settings', array( $this, 'add_onboarding' ) );

		add_action( 'wp_ajax_wds-boarding-toggle', array( $this, 'process_boarding_action' ) );
		add_action( 'wp_ajax_wds-boarding-skip', array( $this, 'process_boarding_skip' ) );
		add_action( 'wp_ajax_wds-boarding-done', array( $this, 'process_boarding_done' ) );
	}

	public function process_boarding_skip() {
		$this->mark_onboarding_done();

		/**
		 * Action hook to trigger after onboarding is skipped.
		 *
		 * @since 3.7.0
		 */
		do_action( 'smartcrawl_after_onboarding_skip' );

		wp_send_json_success();
	}

	/**
	 * Process onboarding completion.
	 *
	 * @return void
	 */
	public function process_boarding_done() {
		$this->mark_onboarding_done();

		/**
		 * Action hook to trigger after onboarding is done.
		 *
		 * @since 3.7.0
		 */
		do_action( 'smartcrawl_after_onboarding_done' );

		wp_send_json_success();
	}

	public function process_boarding_action() {
		$data   = $this->get_request_data();
		$target = ! empty( $data['target'] ) ? sanitize_key( $data['target'] ) : false;
		$enable = empty( $data['enable'] ) ? false : true;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();

			return;
		}

		switch ( $target ) {
			case 'analysis-enable':
				$opts                         = Settings::get_specific_options( 'wds_settings_options' );
				$opts['analysis-seo']         = $enable;
				$opts['analysis-readability'] = $enable;
				Settings::update_specific_options( 'wds_settings_options', $opts );
				wp_send_json_success();

				return;
			case 'opengraph-twitter-enable':
				$opts                        = Settings::get_component_options( Settings::COMP_SOCIAL );
				$opts['og-enable']           = $enable;
				$opts['twitter-card-enable'] = $enable;
				Settings::update_component_options( Settings::COMP_SOCIAL, $opts );
				wp_send_json_success();

				return;
			case 'sitemaps-enable':
				$opts            = Settings::get_specific_options( 'wds_settings_options' );
				$opts['sitemap'] = $enable;
				Settings::update_specific_options( 'wds_settings_options', $opts );
				wp_send_json_success();

				return;

			case 'robots-txt-enable':
				$controller               = \SmartCrawl\Modules\Advanced\Controller::get();
				$opts                     = $controller->get_options();
				$opts['robots']['active'] = $enable;
				$controller->update_option( 'robots', $opts['robots'] );
				wp_send_json_success();
				return;

			case 'usage-tracking-enable':
				$opts                   = Settings::get_specific_options( 'wds_settings_options' );
				$opts['usage_tracking'] = $enable;
				Settings::update_specific_options( 'wds_settings_options', $opts );
				wp_send_json_success();
				return;

			default:
				wp_send_json_error();
				return;
		}
	}

	public function add_onboarding() {
		if ( $this->onboarding_done() ) {
			return;
		}

		Simple_Renderer::render( 'dashboard/onboarding' );
	}

	/**
	 * Bind listening actions
	 *
	 * @return bool
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'dispatch_actions' ) );

		return true;
	}

	/**
	 * Unbinds listening actions
	 *
	 * @return bool
	 */
	protected function terminate() {
		remove_action( 'admin_init', array( $this, 'dispatch_actions' ) );

		return true;
	}

	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wds_nonce'] ), 'wds-onboard-nonce' ) ? stripslashes_deep( $_POST ) : array(); // phpcs:ignore
	}

	public function get_onboarding_done_version() {
		return Settings::get_specific_options( self::ONBOARDING_DONE_OPTION );
	}

	public function onboarding_done() {
		$version = $this->get_onboarding_done_version();
		return ! empty( $version );
	}

	public function mark_onboarding_done() {
		Settings::update_specific_options( self::ONBOARDING_DONE_OPTION, SMARTCRAWL_VERSION );
	}
}