<?php

namespace SmartCrawl\Schema;

use SmartCrawl\Admin\Settings\Admin_Settings;
use SmartCrawl\Entities\Entity;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Work_Unit;

/**
 * Outputs JSON+LD schema.org data to the page
 */
class Printer extends Work_Unit {

	use Singleton;

	/**
	 * @var bool
	 */
	private $is_running = false;

	/**
	 * @var bool
	 */
	private $is_done = false;

	/**
	 * Boot the hooking part
	 */
	public static function run() {
		self::get()->add_hooks();
	}

	/**
	 * First-line dispatching of schema tags injection
	 */
	public function dispatch_schema_injection() {
		if ( ! ! $this->is_done ) {
			return false;
		}

		if ( $this->is_schema_disabled() ) {
			$this->is_done = true;

			return false; // Disabled.
		}

		$entity = \SmartCrawl\Endpoint_Resolver::resolve()->get_queried_entity();
		if ( ! $entity ) {
			return false;
		}

		$data = $entity->get_schema();
		if ( empty( $data ) ) {
			return false;
		}

		/**
		 * Filter to modify final schema data.
		 *
		 * @param array  $data   Schema data.
		 * @param Entity $entity Entity.
		 */
		$data = apply_filters( 'wds_schema_printer_schema_data', $data, $entity );

		$this->is_done = true;

		echo '<script type="application/ld+json">' .
			wp_json_encode(
				array(
					'@context' => 'https://schema.org',
					'@graph'   => $data,
				)
			) . "</script>\n";
	}

	/**
	 * @return string
	 */
	public function get_filter_prefix() {
		return 'wds-schema';
	}

	/**
	 * @return mixed
	 */
	public function admin_bar_menu_items( $admin_bar ) {
		$schema_options = Settings::get_component_options( Settings::COMP_SCHEMA );
		if (
			is_admin()
			|| ! current_user_can( 'manage_options' )
			|| $this->is_schema_disabled()
			|| empty( $schema_options['schema_enable_test_button'] )
		) {
			return $admin_bar;
		}

		// Do not show if only superadmin can view settings and the current user is not super admin.
		if (
			is_multisite()
			&& \smartcrawl_subsite_manager_role() === 'superadmin'
			&& ! current_user_can( 'manage_network_options' )
		) {
			return $admin_bar;
		}

		$url = esc_url_raw( 'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" ); // phpcs:ignore
		$admin_bar->add_menu(
			array(
				'id'    => 'smartcrawl-test-item',
				'title' => __( 'Test Schema', 'wds' ),
				'href'  => sprintf( 'https://search.google.com/test/rich-results?url=%s&user_agent=2', urlencode( $url ) ), // phpcs:ignore
				'meta'  => array(
					'title'  => __( 'Test Schema', 'wds' ),
					'target' => '_blank',
				),
			)
		);

		return $admin_bar;
	}

	/**
	 * @return bool|void
	 */
	private function add_hooks() {
		// Do not double-bind.
		if ( $this->apply_filters( 'is_running', $this->is_running ) ) {
			return true;
		}

		add_action(
			'wp_head',
			array(
				$this,
				'dispatch_schema_injection',
			),
			50
		);
		add_action(
			'wds_head-after_output',
			array(
				$this,
				'dispatch_schema_injection',
			)
		);
		add_action(
			'admin_bar_menu',
			array(
				$this,
				'admin_bar_menu_items',
			),
			99
		);

		$this->is_running = true;
	}

	/**
	 * @return bool
	 */
	private function is_schema_disabled() {
		$social = Settings::get_component_options( Settings::COMP_SOCIAL );

		return ! empty( $social['disable-schema'] )
			|| ! Admin_Settings::is_tab_allowed( Settings::TAB_SCHEMA );
	}
}