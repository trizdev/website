<?php
/**
 * Controller for Advanced module.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Modules\Advanced;

use SmartCrawl\Admin\Module_Settings;
use SmartCrawl\Controllers;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;

/**
 * Redirects Controller.
 */
class Controller extends Controllers\Module_Controller {

	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->module_name  = Settings::ADVANCED_MODULE;
		$this->module_title = __( 'Advanced Tools', 'wds' );
		$this->position     = 6;

		$this->submodules = array(
			Settings::AUTOLINKS_SUBMODULE   => Autolinks\Controller::get(),
			Settings::REDIRECTS_SUBMODULE   => Redirects\Controller::get(),
			Settings::WOOCOMMERCE_SUBMODULE => WooCommerce\Controller::get(),
			Settings::SEOMOZ_SUBMODULE      => Seomoz\Controller::get(),
			Settings::ROBOTS_SUBMODULE      => Robots\Controller::get(),
			Settings::BREADCRUMB_SUBMODULE  => Breadcrumbs\Controller::get(),
		);

		parent::__construct();
	}

	/**
	 * Outputs the content for this module's page.
	 */
	public function output_page() {
		wp_enqueue_script( $this->module_name );

		$args = array(
			'active_tab'      => $this->get_active_tab( 'tab_automatic_linking' ),
			'already_exists'  => Robots\Controller::get()->file_exists(),
			'rootdir_install' => Robots\Controller::get()->rootdir_exists(),
			'options'         => $this->options,
			'_view'           => array(
				'option_name' => $this->module_name,
			),
		);

		Module_Settings::get()->output_view( 'advanced-tools/advanced-tools-settings', $args );

		parent::output_page();
	}
}