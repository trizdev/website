<?php
/**
 * Plugin Name: SmartCrawl Pro
 * Plugin URI: https://wpmudev.com/project/smartcrawl-wordpress-seo/
 * Description: Every SEO option that a site requires, in one easy bundle.
 * Version: 3.10.1
 * Network: true
 * Text Domain: wds
 * Author: WPMU DEV
 * Author URI: https://wpmudev.com
 * WDP ID: 167
 *
 * Copyright 2010-2011 Incsub (http://incsub.com/)
 * Author - Ulrich Sossou (Incsub)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package SmartCrawl
 */

namespace SmartCrawl;

use SmartCrawl\Admin\Settings;

if ( ! class_exists( '\SmartCrawl\SmartCrawl' ) ) {
	/**
	 * Class SmartCrawl
	 */
	class SmartCrawl {

		const LAST_VERSION_OPTION_ID = 'wds_last_version';

		const VERSION_OPTION_ID = 'wds_version';

		/**
		 * Construct the Plugin object
		 */
		public function __construct() {
		}

		/**
		 * Init Plugin
		 */
		public function plugin_init() {
			require_once plugin_dir_path( __FILE__ ) . 'constants.php';

			// Init plugin.
			new Init();

			add_action( 'plugins_loaded', array( $this, 'set_version_options' ) );
			add_action( 'plugins_loaded', array( $this, 'maybe_deactivate_free' ) );
		}

		/**
		 * Activate the plugin
		 *
		 * @return void
		 */
		public static function activate() {
			require_once plugin_dir_path( __FILE__ ) . 'constants.php';

			// Init plugin.
			new Init();

			Settings\Dashboard::get()->defaults();
			Settings\Health::get()->defaults();
			Settings\Onpage::get()->defaults();
			Settings\Schema::get()->defaults();
			Settings\Social::get()->defaults();
			Settings\Sitemap::get()->defaults();
			Settings\Settings::get()->defaults();

			self::save_free_installation_timestamp();
		}

		/**
		 * Save timestamp for free version.
		 *
		 * @return void
		 */
		private static function save_free_installation_timestamp() {
			$service = self::get_service();
			if ( $service->is_member() ) {
				return;
			}

			$free_install_date = get_site_option( 'wds-free-install-date' );
			if ( empty( $free_install_date ) ) {
				update_site_option( 'wds-free-install-date', current_time( 'timestamp' ) ); // phpcs:ignore
			}
		}

		/**
		 * Set plugin version details.
		 *
		 * @return void
		 */
		public function set_version_options() {
			$version = get_option( self::VERSION_OPTION_ID, false );
			if ( ! $version || version_compare( $version, SMARTCRAWL_VERSION, '!=' ) ) {
				update_option( self::LAST_VERSION_OPTION_ID, $version );
				update_option( self::VERSION_OPTION_ID, SMARTCRAWL_VERSION );

				do_action( 'wds_plugin_update', SMARTCRAWL_VERSION, $version );
			}
		}

		/**
		 * Get service instance.
		 *
		 * @return Services\Site
		 */
		private static function get_service() {
			return Services\Service::get( Services\Service::SERVICE_SITE );
		}

		/**
		 * Deactivate the plugin
		 *
		 * @return void
		 */
		public static function deactivate() {
			Sitemaps\Troubleshooting::get()->stop();
			Modules\Advanced\Seomoz\Cron::get()->stop();
			Integration\Maxmind\Controller::get()->stop();
			Integration\Maxmind\Cron::get()->stop();
		}

		/**
		 * Get the last version number.
		 *
		 * @return false|mixed|void
		 */
		public static function get_last_version() {
			return get_option( self::LAST_VERSION_OPTION_ID, false );
		}

		/**
		 * Gets the version number string
		 *
		 * @return string Version number info
		 */
		public static function get_version() {
			static $version;
			if ( empty( $version ) ) {
				$version = defined( 'SMARTCRAWL_VERSION' ) && SMARTCRAWL_VERSION ? SMARTCRAWL_VERSION : null;
			}

			return $version;
		}

		/**
		 * Make sure both Pro and Free versions are not active at a time.
		 *
		 * If Pro version is already active, deactivate the free version if
		 * it is activated.
		 *
		 * @since 3.6.3
		 */
		public function maybe_deactivate_free() {
			// Make sure the function exist.
			if ( ! function_exists( '\is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Check if the Pro version exists and is activated along with free.
			if ( \is_plugin_active( 'smartcrawl-seo/wpmu-dev-seo.php' ) && \is_plugin_active( 'wpmu-dev-seo/wpmu-dev-seo.php' ) ) {
				// Pro is activated, so deactivate the free one.
				\deactivate_plugins( 'smartcrawl-seo/wpmu-dev-seo.php' );
			}
		}
	}

	require_once __DIR__ . '/vendor/autoload.php';
	require_once __DIR__ . '/deprecated-aliases.php';

	if ( ! defined( '\SMARTCRAWL_PLUGIN_BASENAME' ) ) {
		define( 'SMARTCRAWL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	}

	// Plugin Activation and Deactivation hooks.
	register_activation_hook( __FILE__, array( '\SmartCrawl\SmartCrawl', 'activate' ) );
	register_deactivation_hook( __FILE__, array( '\SmartCrawl\SmartCrawl', 'deactivate' ) );

	if ( defined( 'SMARTCRAWL_CONDITIONAL_EXECUTION' ) && \SMARTCRAWL_CONDITIONAL_EXECUTION ) {
		add_action(
			'plugins_loaded',
			array( new SmartCrawl(), 'plugin_init' )
		);
	} else {
		( new SmartCrawl() )->plugin_init();
	}
}