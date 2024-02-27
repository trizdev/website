<?php
/**
 * Controller for Advanced module.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Controllers;

use SmartCrawl\Services\Service;
use SmartCrawl\Settings;
use SmartCrawl\Admin\Settings\Settings as Admin_Settings;

/**
 * Redirects Controller.
 */
abstract class Module_Controller extends Controller {

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	public $module_id;

	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $module_name;

	/**
	 * Module title.
	 *
	 * @var string
	 */
	public $module_title;

	/**
	 * Capability required for this page
	 *
	 * @var string
	 */
	public $capability = 'manage_options';

	/**
	 * Page title.
	 *
	 * @var string
	 */
	public $page_title;

	/**
	 * The position in the menu order this item should appear.
	 *
	 * @var float|int
	 */
	public $position = null;

	/**
	 * Action message
	 *
	 * @var string
	 */
	public $msg = '';

	/**
	 * Module page's hook_suffix, or false if not existing.
	 *
	 * @var string
	 */
	public $hook_suffix = '';

	/**
	 * Submodule handlers.
	 *
	 * @var Submodule_Controller[]
	 */
	public $submodules = array();

	/**
	 * Settings options.
	 *
	 * @var array
	 */
	public $settings_opts = array();

	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct();

		$this->options = wp_parse_args(
			get_option( $this->module_name, array() ),
			array_merge(
				array( 'active' => true ),
				array_map(
					function() {
						return array(); // phpcs:ignore Universal.CodeAnalysis.ConstructorDestructorReturn.ReturnValueFound
					},
					$this->submodules
				)
			)
		);

		foreach ( $this->submodules as $submodule_name => $handler ) {
			$handler->parent      = $this;
			$handler->module_name = $submodule_name;
			$handler->module_id   = str_replace( '-', '_', $submodule_name );
			$handler->set_options( empty( $this->options[ $submodule_name ] ) ? array() : $this->options[ $submodule_name ] );
		}

		/* translators: %s: menu title. */
		$this->page_title = sprintf( __( 'SmartCrawl Wizard: %s', 'wds' ), $this->module_title );
		$this->module_id  = str_replace( array( 'wds-', '-' ), array( '', '_' ), $this->module_name );

		$this->settings_opts = Settings::get_specific_options( Settings::SETTINGS_MODULE . '_options' );
	}

	/**
	 * Checks if current module is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return array_key_exists( $this->module_name, Admin_Settings::get_blog_tabs() );
	}

	/**
	 * Includes methods that runs always.
	 *
	 * @return void
	 */
	protected function always() {
		foreach ( $this->submodules as $submodule_name => $handler ) {
			$handler->set_options( empty( $this->options[ $submodule_name ] ) ? array() : $this->options[ $submodule_name ] );
			$handler->run();
		}

		add_action( 'admin_init', array( $this, 'register_setting' ) );

		if ( ! \smartcrawl_get_array_value( $this->settings_opts, 'hide_disables', true ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 98 );
			add_filter( 'smartcrawl_admin_bar_menu', array( $this, 'admin_bar_menu' ), 98 );
		}

		add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_settings_scripts' ) );

		add_action( "wp_ajax_smartcrawl_activate_$this->module_id", array( $this, 'activate_module' ) );
	}

	/**
	 * Should this module run?
	 *
	 * @return bool
	 */
	public function should_run() {
		return ! empty( $this->options['active'] );
	}

	/**
	 * Initiailization method.
	 *
	 * @return void
	 */
	protected function init() {
		if ( \smartcrawl_get_array_value( $this->settings_opts, 'hide_disables', true ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 98 );
			add_filter( 'smartcrawl_admin_bar_menu', array( $this, 'admin_bar_menu' ), 99 );
		}
	}

	/**
	 * Terminates submodules.
	 *
	 * @return bool
	 */
	public function stop() {
		foreach ( $this->submodules as $submodule ) {
			$submodule->stop();
		}

		return parent::stop();
	}

	/**
	 * Includes methods when the controller stops running.
	 *
	 * @return void
	 */
	protected function terminate() {
		$this->options['active'] = false;

		update_option( $this->module_name, $this->options );
	}

	/**
	 * Registers submodule setting and its data.
	 */
	public function register_setting() {
		register_setting(
			$this->module_name,
			$this->module_name,
			array( $this, 'sanitize_callback' )
		);
	}

	/**
	 * Adds admin bar menu item.
	 *
	 * @param array $nodes Admin bar nodes.
	 *
	 * @return array
	 */
	public function admin_bar_menu( $nodes ) {
		$settings_index = array_search( Settings::TAB_SETTINGS, array_column( $nodes, 'id' ), true );

		array_splice(
			$nodes,
			$settings_index,
			0,
			array(
				array(
					'id'     => $this->module_name,
					'title'  => $this->module_title,
					'href'   => sprintf(
						'%s?page=%s',
						admin_url( 'admin.php' ),
						$this->module_name
					),
					'parent' => Settings::TAB_DASHBOARD,
				),
			)
		);

		return $nodes;
	}

	/**
	 * Adds a submenu page for module.
	 *
	 * @return mixed
	 */
	public function admin_menu() {
		$menu_title = apply_filters( 'smartcrawl_admin_settings_submenu_title', $this->module_title, $this->module_name );
		$menu_title = wp_kses( $menu_title, array( 'span' => array( 'class' => array() ) ) );

		$this->hook_suffix = add_submenu_page(
			'wds_wizard',
			$this->page_title,
			$menu_title,
			$this->capability,
			$this->module_name,
			array( $this, 'output_page' ),
			$this->position
		);
	}

	/**
	 * Enqueues admin scripts for this module.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( ! $this->is_current_screen() ) {
			return;
		}

		\smartcrawl_register_js(
			$this->module_name,
			"js/build/wds-admin-$this->module_id.min.js",
			array(
				'underscore',
				'jquery',
				Assets::ADMIN_JS,
			)
		);
	}

		/**
		 * Enqueues admin scripts for this module.
		 *
		 * @return void
		 */
	public function admin_enqueue_settings_scripts() {
		if ( ! \SmartCrawl\Admin\Conflict_Detector::get()->is_settings_page() ) {
			return;
		}

		$submodules = array();

		foreach ( $this->submodules as $submodule => $handler ) {
			if ( $handler->is_active() ) {
				$submodules[ $submodule ] = array(
					'active' => ! empty( $handler->get_options()['active'] ),
					'title'  => $handler->module_title,
				);
			}
		}

		wp_localize_script(
			Assets::SETTINGS_PAGE_JS,
			"_wds_{$this->module_id}",
			array(
				'active'     => $this->should_run(),
				'title'      => $this->module_title,
				'submodules' => apply_filters( "smartcrawl_settings_{$this->module_id}_submodules", $submodules ),
			)
		);
	}

	/**
	 * Unified admin tab URL getter
	 *
	 * Also takes into account whether the tab is allowed or not
	 *
	 * @param string $tab Tab to check.
	 *
	 * @return string Unescaped admin URL, or tab anchor on failure
	 */
	public static function admin_url( $tab ) {
		$admin_url = esc_url_raw( add_query_arg( 'page', $tab, admin_url( 'admin.php' ) ) );

		if ( class_exists( '\WP_Defender\Model\Setting\Mask_Login' ) && ! is_user_logged_in() ) {
			$mask_login_model = new \WP_Defender\Model\Setting\Mask_Login();

			if ( $mask_login_model->is_active() ) {
				$admin_url = add_query_arg(
					'redirect_to',
					urlencode( $admin_url ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.urlencode_urlencode
					$mask_login_model->get_new_login_url()
				);
			}
		}

		return $admin_url;
	}

	/**
	 * Outputs the content for this module's page.
	 */
	public function output_page() {
		$this->msg = '';

		if ( ! empty( $_GET['updated'] ) || ! empty( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->msg = __( 'Settings updated', 'wds' );

			if ( function_exists( '\w3tc_pgcache_flush' ) ) {
				\w3tc_pgcache_flush();
				$this->msg .= __( ' &amp; W3 Total Cache Page Cache flushed', 'wds' );
			} elseif ( function_exists( '\wp_cache_clear_cache' ) ) {
				\wp_cache_clear_cache();
				$this->msg .= __( ' &amp; WP Super Cache flushed', 'wds' );
			}
		}

		$errors = get_settings_errors( $this->module_name );

		if ( $errors ) {
			set_transient( 'wds-settings-save-errors', $errors, 3 );
		}

		$prefix = str_replace( '-', '_', $this->module_id );

		do_action( "smartcrawl_{$prefix}_after_output_page" );
	}

	/**
	 * Adds body class
	 *
	 * @param string $classes Class that's being processed.
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		if ( ! $this->is_current_screen() ) {
			return $classes;
		}

		$classes = explode( ' ', $classes );

		$sui_class = \smartcrawl_sui_class();

		if ( ! in_array( $sui_class, $classes, true ) ) {
			$classes[] = $sui_class;
		}

		$service = Service::get( Service::SERVICE_SITE );

		if ( $service->is_member() ) {
			$classes[] = 'wds-is-member';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Sanitizes submitted options
	 *
	 * @param array $input Raw input.
	 *
	 * @return array Sanitized options.
	 */
	public function sanitize_callback( $input ) {
		$old_options = $this->options;

		if ( isset( $input['active'] ) ) {
			$active = boolval( $input['active'] );

			if ( empty( $this->options['active'] ) || $active !== $this->options['active'] ) {
				$this->options['active'] = $active;
			}

			unset( $input['active'] );
		}

		foreach ( $this->submodules as $submodule_name => $handler ) {
			if ( isset( $input[ $submodule_name ] ) && is_callable( array( $handler, 'sanitize_callback' ) ) ) {
				$this->options[ $submodule_name ] = $handler->sanitize_callback( $input[ $submodule_name ] );
			}
		}

		do_action_deprecated(
			'smartcrawl_before_save_tools',
			array( $old_options, $this->options ),
			'6.4.2',
			"smartcrawl_after_sanitize_{$this->module_id}",
			/* translators: %s: Module ID. */
			sprintf( __( 'Please use our new hook `smartcrawl_after_sanitize_%s` in SmartCrawl.' ), $this->module_id )
		);

		do_action( "smartcrawl_after_sanitize_{$this->module_id}", $old_options, $this->options );

		return $this->options;
	}

	/**
	 * Updates module option and saves to db.
	 *
	 * @param string $option Name of the option to update.
	 * @param mixed  $value Option value.
	 *
	 * @return void
	 */
	public function update_option( $option = '', $value = false ) {
		if ( $option ) {
			$this->options[ $option ] = $value;
		} else {
			$this->options = array_merge( $this->options, $value );
		}

		update_option( $this->module_name, $this->options );

		if ( ! empty( $this->options['active'] ) ) {
			$this->run();
		} else {
			$this->stop();
		}
	}

	/**
	 * Ajax handler to activate module.
	 *
	 * @return void
	 */
	public function activate_module() {
		$this->update_option( 'active', true );

		wp_send_json_success();
	}

	/**
	 * Is current screen SmartCrawl.
	 *
	 * @return bool
	 */
	private function is_current_screen() {
		$screen = get_current_screen();

		return (
			! empty( $screen->id ) &&
			! empty( $this->hook_suffix ) &&
			strpos( $screen->id, $this->hook_suffix ) === 0
		);
	}

	/**
	 * Checks if the last active tab is stored in the transient and returns its value. If nothing is available then it returns the default value.
	 *
	 * @param string $default_tab Fallback value.
	 *
	 * @return string The last active tab.
	 */
	protected function get_active_tab( $default_tab = '' ) {
		return empty( $_GET['tab'] ) ? $default_tab : sanitize_text_field( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}