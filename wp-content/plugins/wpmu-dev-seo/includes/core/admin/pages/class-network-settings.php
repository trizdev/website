<?php
/**
 * Class Network_Settings
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Admin\Pages;

use SmartCrawl\Admin\Settings\Admin_Settings;
use SmartCrawl\Controllers\Assets;
use SmartCrawl\Settings;
use SmartCrawl\Simple_Renderer;
use SmartCrawl\Singleton;

class Network_Settings extends Page {

	use Singleton;

	const MENU_SLUG = 'wds_network_settings';

	public function should_run() {
		return is_multisite() && is_network_admin();
	}

	/**
	 * Get the capability.
	 *
	 * @return string
	 */
	public function capability() {
		return 'manage_network_options';
	}

	protected function init() {
		parent::init();

		add_action( 'network_admin_menu', array( $this, 'add_page' ), 20 );
		add_action( 'admin_head', array( $this, 'add_css' ) );
		add_action( 'init', array( $this, 'save_settings' ) );

		return true;
	}

	private function url() {
		return esc_url_raw( add_query_arg( 'page', self::MENU_SLUG, network_admin_url( 'admin.php' ) ) );
	}

	public function save_settings() {
		$data  = $this->get_request_data();
		$input = \smartcrawl_get_array_value( $data, 'wds_settings_options' );
		if (
			! empty( $input['save_blog_tabs'] )
			&& current_user_can( $this->capability() )
		) {
			$raw  = ! empty( $input['wds_blog_tabs'] ) && is_array( $input['wds_blog_tabs'] )
				? $input['wds_blog_tabs']
				: array();
			$tabs = array();
			foreach ( $raw as $key => $tab ) {
				if ( ! empty( $tab ) ) {
					$tabs[ $key ] = true;
				}
			}

			update_site_option( 'wds_blog_tabs', $tabs );

			$manager_role = \smartcrawl_get_array_value( $input, 'wds_subsite_manager_role' );
			update_site_option( 'wds_subsite_manager_role', sanitize_text_field( $manager_role ) );

			$config_id = \smartcrawl_get_array_value( $input, 'wds_subsite_config_id' );
			update_site_option( 'wds_subsite_config_id', sanitize_text_field( $config_id ) );

			wp_safe_redirect(
				esc_url_raw( add_query_arg( 'settings-updated', 'true', $this->url() ) )
			);
			exit();
		}
	}

	public function add_page() {
		$dashboard = \SmartCrawl\Admin\Settings\Dashboard::get();
		add_menu_page(
			'',
			$dashboard->get_title(),
			$this->capability(),
			self::MENU_SLUG,
			'',
			$dashboard->get_icon()
		);
		$this->add_network_settings_page( self::MENU_SLUG );
		add_submenu_page(
			self::MENU_SLUG,
			'',
			'',
			$this->capability(),
			'wds_dummy'
		);
	}

	public function add_css() {
		?>
		<style>
			#adminmenu a[href="wds_dummy"] {
				display: none !important;
			}
		</style>
		<?php
	}

	private function add_network_settings_page( $parent ) {
		$submenu_page = add_submenu_page(
			$parent,
			esc_html__( 'SmartCrawl Network Settings', 'wds' ),
			esc_html__( 'Network Settings', 'wds' ),
			$this->capability(),
			self::MENU_SLUG,
			array( $this, 'options_page' )
		);

		add_action( "admin_print_styles-{$submenu_page}", array( $this, 'admin_styles' ) );
	}

	public function options_page() {
		$arguments['slugs']                = array(
			Settings::TAB_ONPAGE      => __( 'Title & Meta', 'wds' ),
			Settings::TAB_SCHEMA      => __( 'Schema', 'wds' ),
			Settings::TAB_SOCIAL      => __( 'Social', 'wds' ),
			Settings::TAB_SITEMAP     => __( 'Sitemaps', 'wds' ),
			Settings::ADVANCED_MODULE => __( 'Advanced Tools', 'wds' ),
			Settings::TAB_SETTINGS    => __( 'Settings', 'wds' ),
		);
		$arguments['blog_tabs']            = \SmartCrawl\Admin\Settings\Settings::get_blog_tabs();
		$arguments['subsite_manager_role'] = get_site_option( 'wds_subsite_manager_role' );
		$arguments['subsite_config_id']    = get_site_option( 'wds_subsite_config_id' );
		$arguments['option_name']          = 'wds_settings_options';
		$arguments['per_site_notice']      = $this->per_site_notice();

		wp_enqueue_script( Assets::NETWORK_SETTINGS_PAGE_JS );

		Simple_Renderer::render( 'network-settings', $arguments );
	}

	private function per_site_notice() {
		$dashboard_url = Admin_Settings::admin_url( Admin_Settings::TAB_DASHBOARD );
		ob_start();
		?>
		<?php esc_html_e( 'You are currently in Per Site mode which means each site on your network has different settings.', 'wds' ); ?>
		<br/><br/>
		<a
			type="button"
			href="<?php echo esc_attr( $dashboard_url ); ?>"
			class="sui-button"
		>
			<?php esc_html_e( 'Configure Main Site', 'wds' ); ?>
		</a>
		<?php
		return Simple_Renderer::load(
			'notice',
			array(
				'message' => ob_get_clean(),
				'class'   => 'sui-notice-warning',
			)
		);
	}

	public function admin_styles() {
		wp_enqueue_style( Assets::APP_CSS );
	}

	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wds_nonce'] ), 'wds-network-settings-nonce' ) ? $_POST : array(); // phpcs:ignore -- Sanitized when used.
	}

	public function get_menu_slug() {
		return self::MENU_SLUG;
	}
}