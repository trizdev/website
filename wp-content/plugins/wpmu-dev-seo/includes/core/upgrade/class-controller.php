<?php
/**
 * Controller to handle redirects and database upgrade.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Upgrade;

use SmartCrawl\Controllers;
use SmartCrawl\Singleton;
use SmartCrawl\Settings;
use SmartCrawl\Admin\Settings as Admin_Settings;

/**
 * Upgrader Controller.
 */
class Controller extends Controllers\Controller {

	use Singleton;

	/**
	 * Should this module run?
	 *
	 * @return true
	 */
	public function should_run() {
		return true;
	}

	/**
	 * Initialization method.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'init', array( $this, 'redirect_admin_pages' ) );
		add_action( 'wds_plugin_update', array( $this, 'upgrade_post_types_to_343' ), 10, 2 );
		add_action( 'wds_plugin_update', array( $this, 'upgrade_advanced_options' ), 10, 2 );
	}

	/**
	 * Redirects old admin page urls to new ones.
	 *
	 * @return void
	 */
	public function redirect_admin_pages() {
		if ( ! is_admin() ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'wds_autolinks' === $_GET['page'] ) {
			$redirect_url = 'page=' . Settings::ADVANCED_MODULE;

			if ( $redirect_url && isset( $_GET['tab'] ) ) {
				$redirect_url .= '&tab=' . sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			}

			wp_safe_redirect( admin_url( "admin.php?$redirect_url" ) );
			exit();
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Upgrades pre v3.4.3 settings to latest structure.
	 *
	 * We have moved insert and link_to settings to array.
	 *
	 * @param string $new_version New version.
	 * @param string $old_version Old version.
	 *
	 * @return void
	 */
	public function upgrade_post_types_to_343( $new_version, $old_version ) {
		// Only if old version is less than 3.4.3.
		if ( version_compare( $old_version, '3.4.3', '>=' ) ) {
			return;
		}

		$insert          = array();
		$link_to         = array();
		$options         = get_option( 'wds_autolinks_options', array() );
		$post_type_names = array_keys( \smartcrawl_get_post_types() );

		// Check each post types for enabled status.
		foreach ( array_merge( $post_type_names, array( 'comment', 'product_cat' ) ) as $insert_key ) {
			if ( ! empty( $options[ $insert_key ] ) ) {
				// Remove old value.
				unset( $options[ $insert_key ] );
				$insert[] = $insert_key;
			}
		}
		// Set link to option.
		foreach ( $post_type_names as $post_type ) {
			if ( ! empty( $options[ 'l' . $post_type ] ) ) {
				// Remove old value.
				unset( $options[ 'l' . $post_type ] );
				$link_to[] = 'l' . $post_type;
			}
		}
		foreach ( get_taxonomies() as $taxonomy ) {
			$tax = get_taxonomy( $taxonomy );
			$key = strtolower( $tax->labels->name );
			if ( ! empty( $options[ 'l' . $key ] ) ) {
				// Remove old value.
				unset( $options[ 'l' . $key ] );
				$link_to[] = 'l' . $key;
			}
		}

		$options['insert']  = $insert;
		$options['link_to'] = $link_to;

		update_option( 'wds_autolinks_options', $options );
	}

	/**
	 * Upgrades pre v3.9.1 advanced module db settings to latest structure.
	 *
	 * @since 3.8.0
	 *
	 * @param string       $new_version New version.
	 * @param string|false $old_version Old version.
	 *
	 * @return void
	 */
	public function upgrade_advanced_options( $new_version, $old_version ) {
		if ( ! $old_version || version_compare( $old_version, '3.10.0', '>=' ) ) {
			return;
		}

		$options         = get_option( Settings::ADVANCED_MODULE, array() );
		$autolinks_opts  = get_option( 'wds_autolinks_options', array() );
		$woo_opts        = get_option( 'wds_woocommerce_options', array() );
		$settings_opts   = get_option( 'wds_settings_options', array() );
		$breadcrumb_opts = get_option( 'wds_breadcrumb_options', array() );
		$robots_opts     = get_option( 'wds_robots_options', array() );

		$options['active'] = empty( $autolinks_opts['disable-adv-tools'] );

		$this->set_option( $options, 'autolinks', 'active', ! empty( $settings_opts['autolinks'] ) );

		$keys = array(
			'ignorepost',
			'ignore',
			'customkey',
			'cpt_char_limit',
			'tax_char_limit',
			'link_limit',
			'single_link_limit',
			'insert',
			'link_to',
			'comment',
			'onlysingle',
			'allowfeed',
			'casesens',
			'customkey_preventduplicatelink',
			'target_blank',
			'rel_nofollow',
			'allow_empty_tax',
			'excludeheading',
			'exclude_no_index',
			'exclude_image_captions',
			'disable_content_cache',
		);

		foreach ( $keys as $key ) {
			$this->set_option( $options, 'autolinks', $key, $autolinks_opts, $key );
		}

		$this->set_option( $options, 'redirects', 'active', ! isset( $settings_opts['redirects'] ) || ! empty( $settings_opts['redirects'] ) );
		$this->set_option( $options, 'redirects', 'attachments', $autolinks_opts, 'redirect-attachments' );
		$this->set_option( $options, 'redirects', 'images_only', $autolinks_opts, 'redirect-attachments-images-only' );
		$this->set_option( $options, 'redirects', 'default_type', $settings_opts, 'redirections-code' );

		$keys = array(
			'active'              => 'woocommerce_enabled',
			'rm_gen_tag'          => 'remove_generator_tag',
			'enable_og'           => 'enable_open_graph',
			'add_robots'          => 'add_robots',
			'shop_schema'         => 'enable_shop_page_schema',
			'noindex_hidden_prod' => 'noindex_hidden_products',
			'brand'               => 'brand',
			'global_id'           => 'global_identifier',
		);

		foreach ( $keys as $new_key => $old_key ) {
			$this->set_option( $options, 'woocommerce', $new_key, $woo_opts, $old_key );
		}

		$this->set_option( $options, 'breadcrumbs', 'active', ! empty( $settings_opts['breadcrumb'] ) );

		$keys = array(
			'separator',
			'custom_sep',
			'prefix',
			'home_label',
			'home_trail',
			'hide_post_title',
			'add_prefix',
			'disable_woo',
			'labels',
		);

		foreach ( $keys as $key ) {
			$this->set_option( $options, 'breadcrumbs', $key, $breadcrumb_opts, $key );

		}

		$this->set_option( $options, 'seomoz', 'active', ! empty( $settings_opts['moz'] ) );

		$this->set_option( $options, 'robots', 'active', ! empty( $settings_opts['robots-txt'] ) );

		$keys = array(
			'sitemap_directive_disabled',
			'custom_sitemap_url',
			'custom_directives',
		);

		foreach ( $keys as $key ) {
			$this->set_option( $options, 'robots', $key, $robots_opts, $key );
		}

		update_option( Settings::ADVANCED_MODULE, $options );

		$modules = get_site_option( 'wds_blog_tabs' );

		if ( isset( $modules['wds_autolinks'] ) ) {
			$modules[ Settings::ADVANCED_MODULE ] = $modules['wds_autolinks'];

			update_site_option( 'wds_blog_tabs', $modules );
		}
	}

	/**
	 * Sets submodule option value.
	 *
	 * @param array        $options Options where the option to be stored.
	 * @param string       $submodule Module name.
	 * @param string       $option Submodule name.
	 * @param array|string $value Options where to get value if $old_option param exists. If not, old option value.
	 * @param string       $old_option Optional. Old option value.
	 *
	 * @return void
	 */
	private function set_option( &$options, $submodule, $option, $value, $old_option = false ) {
		if ( func_num_args() > 4 ) {

			if ( ! isset( $value[ $old_option ] ) ) {
				return;
			}

			$value = $value[ $old_option ];
		}

		if ( ! isset( $options[ $submodule ] ) ) {
			$options[ $submodule ] = array();
		}

		if ( isset( $options[ $submodule ][ $option ] ) ) {
			return;
		}

		$options[ $submodule ][ $option ] = $value;
	}
}