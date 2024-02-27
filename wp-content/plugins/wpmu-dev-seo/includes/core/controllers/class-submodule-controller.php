<?php
/**
 * Controller for Advanced module.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Controllers;

use SmartCrawl\Services\Service;
use SmartCrawl\Settings;

/**
 * Redirects Controller.
 */
abstract class Submodule_Controller extends Controller {

	/**
	 * Parent module.
	 *
	 * @var Module_Controller
	 */
	public $parent;

	/**
	 * Submodule ID.
	 *
	 * @var string
	 */
	public $module_id;

	/**
	 * Submodule name.
	 *
	 * @var string
	 */
	public $module_name;

	/**
	 * Submodule title.
	 *
	 * @var string
	 */
	public $module_title = '';

	/**
	 * Includes methods that runs always.
	 *
	 * @return void
	 */
	protected function always() {
		$this->options = wp_parse_args(
			$this->options,
			is_callable( array( $this, 'defaults' ) ) ?
				call_user_func( array( $this, 'defaults' ) ) :
				array()
		);

		if ( is_callable( array( $this, 'localize_script' ) ) ) {
			add_action( "smartcrawl_{$this->parent->module_id}_after_output_page", array( $this, 'localize_script' ) );
		}

		if ( \smartcrawl_get_array_value( $this->parent->settings_opts, 'hide_disables', true ) ) {
			return;
		}

		if ( is_callable( array( $this, 'render_dashboard_content' ) ) ) {
			add_action( "smartcrawl_widget_{$this->parent->module_id}_submodules", array( $this, 'render_dashboard_content' ) );
		}

		add_action( "wp_ajax_smartcrawl_activate_{$this->parent->module_id}_{$this->module_id}", array( $this, 'activate_submodule' ) );
	}

	/**
	 * Should this module run?.
	 *
	 * @return bool
	 */
	public function should_run() {
		return $this->parent->should_run() && is_array( $this->options ) && ! empty( $this->options['active'] );
	}

	/**
	 * Initiailization method.
	 *
	 * @return void
	 */
	protected function init() {
		if ( ! \smartcrawl_get_array_value( $this->parent->settings_opts, 'hide_disables', true ) ) {
			return;
		}

		if ( is_callable( array( $this, 'render_dashboard_content' ) ) ) {
			add_action( "smartcrawl_widget_{$this->parent->module_id}_submodules", array( $this, 'render_dashboard_content' ) );
		}

		add_action( "wp_ajax_smartcrawl_activate_{$this->parent->module_id}_{$this->module_id}", array( $this, 'activate_submodule' ) );
	}

	/**
	 * Includes methods when the controller stops running.
	 *
	 * @return void
	 */
	protected function terminate() {
		$this->options['active'] = false;
	}

	/**
	 * Ajax handler to activate module.
	 *
	 * @return void
	 */
	public function activate_submodule() {
		if ( ! isset( $_POST['_wds_nonce'] ) || wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) ), 'wds-dashboard-nonce' ) ) {
			wp_send_json_error();
		}

		$this->options['active'] = true;

		$this->parent->update_option( $this->module_name, $this->options );

		wp_send_json_success();
	}
}