<?php

namespace SmartCrawl\Sitemaps;

use SmartCrawl\Models\User;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Services\Service;
use SmartCrawl\Controllers;
use SmartCrawl\Admin\Settings\Admin_Settings;

class Troubleshooting extends Controllers\Controller {

	use Singleton;

	const SITEMAP_INVALID              = 0;
	const SITEMAP_VALID                = 1;
	const SITEMAP_FOREIGN              = 2;
	const SITEMAP_PHYSICAL             = 3;
	const SITEMAP_FAULTY_PERMALINK     = 4;
	const PERMALINKS_SETTING_PLAIN     = 5;
	const SITEMAP_HAS_WHITESPACE       = 6;
	const SITEMAP_UNAUTHORIZED_REQUEST = 7;
	const SITEMAP_REQUEST_ERROR        = 8;
	const EVENT_HOOK                   = 'wds_sitemap_validity_check';
	const ERRORS_FOUND_OPTION_ID       = 'wds_sitemap_errors_found';

	/**
	 * @var
	 */
	private $sub_sitemap_url;

	/**
	 * @var \WP_Error
	 */
	private $wp_error = null;

	/**
	 * @return bool
	 */
	public function should_run() {
		return Settings::get_setting( 'sitemap' )
			&& Admin_Settings::is_tab_allowed( Settings::TAB_SITEMAP );
	}

	/**
	 * @return void
	 */
	protected function init() {
		add_action(
			'wp_ajax_wds_troubleshoot_sitemap',
			array(
				$this,
				'troubleshoot_sitemap',
			)
		);
		add_action(
			'wp_ajax_wds_recheck_sitemaps',
			array(
				$this,
				'recheck_sitemaps',
			)
		);
		add_action( 'init', array( $this, 'schedule_cron' ) );
		add_action(
			self::EVENT_HOOK,
			array(
				$this,
				'do_sitemap_validity_check_cron',
			)
		);
		add_action( 'all_admin_notices', array( $this, 'show_notice' ) );
	}

	/**
	 * Terminates submodules.
	 *
	 * @return bool
	 */
	public function stop() {
		wp_clear_scheduled_hook( self::EVENT_HOOK );

		return parent::stop();
	}

	/**
	 * Removes action hooks when the controller stops running.
	 *
	 * @return void
	 */
	protected function terminate() {
		remove_action(
			'wp_ajax_wds_troubleshoot_sitemap',
			array(
				$this,
				'troubleshoot_sitemap',
			)
		);
		remove_action(
			'wp_ajax_wds_recheck_sitemaps',
			array(
				$this,
				'recheck_sitemaps',
			)
		);
		remove_action( 'init', array( $this, 'schedule_cron' ) );

		remove_action(
			self::EVENT_HOOK,
			array(
				$this,
				'do_sitemap_validity_check_cron',
			)
		);
		remove_action( 'all_admin_notices', array( $this, 'show_notice' ) );
	}

	/**
	 * @return void
	 */
	public function show_notice() {
		$key                  = self::ERRORS_FOUND_OPTION_ID;
		$dismissed_messages   = get_user_meta( get_current_user_id(), 'wds_dismissed_messages', true );
		$is_message_dismissed = \smartcrawl_get_array_value( $dismissed_messages, $key ) === true;
		$errors_found         = get_option( $key );
		if (
			$is_message_dismissed
			|| ! current_user_can( 'manage_options' )
			|| ! $errors_found
		) {
			return;
		}

		$message = sprintf(
			/* translators: %s: User display name */
			esc_html__( 'Hey, %s! A problem on your site is preventing sitemaps from functioning properly. Identify and resolve any issues with SmartCrawl’s Sitemap Troubleshooting feature.', 'wds' ),
			User::current()->get_display_name()
		);
		$action_url = Admin_Settings::admin_url( Settings::TAB_SITEMAP ) . '&tab=tab_settings#wds-troubleshooting-sitemap-placeholder';

		\smartcrawl_print_admin_notice( $key, false, $message, $action_url, esc_html__( 'Troubleshoot Sitemap', 'wds' ) );
	}

	/**
	 * @return void
	 */
	public function schedule_cron() {
		if ( ! wp_next_scheduled( self::EVENT_HOOK ) ) {
			wp_schedule_event( time(), 'twicedaily', self::EVENT_HOOK );
		}
	}

	/**
	 * @return void
	 */
	public function do_sitemap_validity_check_cron() {
		$result = $this->check_all_sitemaps();
		$status = (int) \smartcrawl_get_array_value( $result, 'status' );
		if ( self::SITEMAP_VALID === $status ) {
			$this->clear_errors_found_option();
		} else {
			update_option( self::ERRORS_FOUND_OPTION_ID, 1 );
		}
	}

	/**
	 * @return void
	 */
	public function troubleshoot_sitemap() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Session expired. Please reload the page and try again.', 'wds' ),
				)
			);
		}

		// Check all sitemaps.
		$result = $this->check_all_sitemaps();
		$status = (int) \smartcrawl_get_array_value( $result, 'status' );

		// Let's do whatever we can to make the sitemap work.
		Cache::get()->invalidate();
		flush_rewrite_rules();

		// Prime the caches but don't block the current thread.
		$this->prime_all_sitemap_caches();

		$this->increase_troubleshoot_count();

		wp_send_json_success(
			array(
				'status' => $status,
			)
		);
	}

	/**
	 * @return bool
	 */
	public function recheck_sitemaps() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Session expired. Please reload the page and try again.', 'wds' ),
				)
			);
		}

		$status      = ! isset( $data['status'] ) ? self::SITEMAP_VALID : (int) $data['status'];
		$sitemap_url = empty( $data['sitemap'] ) ? '' : $data['sitemap'];

		// Let's check again, did we make a difference?
		$recheck_result    = $this->check_all_sitemaps();
		$recheck_status    = (int) \smartcrawl_get_array_value( $recheck_result, 'status' );
		$rechecked_sitemap = (string) \smartcrawl_get_array_value( $recheck_result, 'sitemap', $sitemap_url );

		if ( self::SITEMAP_VALID === $recheck_status ) {
			$this->clear_errors_found_option();

			if ( self::SITEMAP_VALID === $status ) {
				// There was no error, there is no error, hurray!
				return $this->send_response( true );
			} else {
				// We fixed something without user intervention, hurray!
				list( $fixed_issue, , $fixed_message ) = $this->get_issue_details( $status );

				return $this->send_response(
					true,
					$fixed_issue,
					$this->include_sitemap_name( $fixed_message, $rechecked_sitemap )
				);
			}
		}

		// Our fix didn't make any difference. Let's ask the user to intervene.
		list( $issue, $message, , $action_text, $action_url ) = $this->get_issue_details( $recheck_status );

		return $this->send_response(
			false,
			$issue,
			$this->include_sitemap_name( $message, $rechecked_sitemap ),
			$action_text,
			$action_url
		);
	}

	/**
	 * @param $fixed
	 * @param $issue
	 * @param $message
	 * @param $action_text
	 * @param $action_url
	 *
	 * @return bool
	 */
	private function send_response( $fixed, $issue = '', $message = '', $action_text = '', $action_url = '' ) {
		$data = array(
			'fixed'       => $fixed,
			'issue'       => $issue,
			'message'     => $message,
			'action_text' => $action_text,
			'action_url'  => $action_url,
		);

		do_action( 'smartcrawl_before_recheck_sitemaps', $data );

		wp_send_json_success( $data );

		return true;
	}

	/**
	 * @return bool
	 */
	private function is_nginx_server() {
		$server_software = \smartcrawl_get_array_value( $_SERVER, 'SERVER_SOFTWARE' );
		if ( empty( $server_software ) || ! is_array( $server_software ) ) {
			return false;
		}

		return in_array( 'nginx', array_map( 'strtolower', $server_software ), true );
	}

	/**
	 * @param $status
	 *
	 * @return array
	 */
	private function get_issue_details( $status ) {
		$service     = Service::get( Service::SERVICE_SITE );
		$support_url = $service->is_member()
			? 'https://wpmudev.com/hub2/support'
			: 'https://wordpress.org/support/plugin/smartcrawl-seo/';

		$permalink_problem_description = $this->is_nginx_server()
			/* translators: %s: Sitemap */
			? __( "Pretty permalinks are not working for your sitemap %s. Since you are hosting your website on an Nginx server, you may have to manually include some rewrite rules to your server's configuration files. Check our documentation for details on how to fix this issue.", 'wds' )
			/* translators: %s: Sitemap */
			: __( "Pretty permalinks are not working for your sitemap %s. You may have to manually include some rewrite rules to your server's configuration files. Visit SmartCrawl's documentation for details on how to fix this issue.", 'wds' );

		$wp_error_message = $this->wp_error ? $this->wp_error->get_error_message() : '';
		$request_error    = $wp_error_message
			/* translators: %s: Error message */
			? __( 'Our troubleshooter was not able to access your sitemap for testing. We received the following error: %s', 'wds' )
			: __( 'Our troubleshooter was not able to access your sitemap for testing.', 'wds' );
		$request_error = sprintf( $request_error, "<br/><code>$wp_error_message</code>" );

		switch ( $status ) {
			case self::SITEMAP_VALID:
				return array();

			case self::SITEMAP_FOREIGN:
				return array(
					__( 'Plugin Conflict', 'wds' ),
					/* translators: %s: Sitemap */
					__( "You have another sitemap plugin conflicting with SmartCrawl's sitemap %s. Please deactivate the conflicting plugin and try again.", 'wds' ),
					'',
					__( 'Go to the Plugins Screen', 'wds' ),
					admin_url( 'plugins.php' ),
				);

			case self::SITEMAP_PHYSICAL:
				return array(
					__( 'File Conflict', 'wds' ),
					/* translators: %s: Sitemap */
					esc_html__( 'You have a physical file named %s on your server that is conflicting with SmartCrawl. Please delete the file and try again.', 'wds' ),
					'',
					'',
					'',
				);

			case self::SITEMAP_FAULTY_PERMALINK:
				return array(
					__( 'Permalink Problem', 'wds' ),
					$permalink_problem_description,
					__( 'Pretty permalinks were not working for your sitemap. Flushing the rewrite rules fixed the issue.', 'wds' ),
					__( 'Visit Documentation', 'wds' ),
					'https://wpmudev.com/docs/wpmu-dev-plugins/smartcrawl/#additional-troubleshooting-options-sitemap',
				);

			case self::PERMALINKS_SETTING_PLAIN:
				return array(
					__( 'Incorrect Permalink Settings', 'wds' ),
					__( 'You are using <code>plain</code> permalinks on this site. Change your permalink structure to anything else for the SmartCrawl sitemap to work.', 'wds' ),
					'',
					__( 'Go to Permalink Settings', 'wds' ),
					admin_url( 'options-permalink.php' ),
				);

			case self::SITEMAP_HAS_WHITESPACE:
				return array(
					__( 'Unwanted Whitespace Character', 'wds' ),
					__( 'Your XML sitemap is invalid because it has an empty whitespace at the beginning. The cause most often is an empty line at the beginning (before the <?php line) or end of the <code>wp-config.php</code> or <code>functions.php</code> file. If there is no empty line or space in these files, we highly recommend running a conflict check to identify what outputs the empty whitespace.', 'wds' ),
					'',
					'',
					'',
				);

			case self::SITEMAP_UNAUTHORIZED_REQUEST:
				return array(
					__( 'Authorization Error', 'wds' ),
					__( "Our troubleshooter was not able to access your sitemap for testing. Your server's security software might be blocking requests sent by the troubleshooter. If this is the case, whitelisting your server's IP might resolve the issue.", 'wds' ),
					'',
					'',
					'',
				);

			case self::SITEMAP_REQUEST_ERROR:
				return array(
					__( 'Request Failed', 'wds' ),
					$request_error,
					'',
					'',
					'',
				);

			default:
			case self::SITEMAP_INVALID:
				return array(
					__( 'Unknown Error', 'wds' ),
					/* translators: %s: Sitemap */
					__( "We found an issue with your sitemap %s, but unfortunately, we couldn't fix it. Please contact our support.", 'wds' ),
					/* translators: %s: Sitemap */
					__( 'We found an unknown issue with your sitemap %s, but clearing the cache seems to have fixed it.', 'wds' ),
					__( 'Contact Support' ),
					$support_url,
				);
		}
	}

	/**
	 * @param $message
	 * @param $sitemap_name
	 *
	 * @return string
	 */
	private function include_sitemap_name( $message, $sitemap_name ) {
		return empty( $sitemap_name )
			? $message
			: sprintf( $message, "<code>$sitemap_name</code>" );
	}

	/**
	 * @param $response
	 *
	 * @return string
	 */
	private function get_sitemap_xml( $response ) {
		return wp_remote_retrieve_body( $response );
	}

	/**
	 * @param $sitemap_url
	 *
	 * @return array|\WP_Error
	 */
	private function get_sitemap_response( $sitemap_url ) {
		return wp_remote_get(
			$sitemap_url,
			array(
				'timeout' => 300,
			)
		);
	}

	/**
	 * @param $sitemap_xml
	 *
	 * @return bool
	 */
	private function xml_has_whitespace( $sitemap_xml ) {
		return $this->is_xml_valid( trim( $sitemap_xml ) );
	}

	/**
	 * @param $sitemap_xml
	 *
	 * @return bool
	 */
	private function is_xml_valid( $sitemap_xml ) {
		return \SmartCrawl\String_Utils::starts_with( $sitemap_xml, '<?xml' );
	}

	/**
	 * @return void
	 */
	private function prime_all_sitemap_caches() {
		$sitemap_urls = $this->get_sitemap_urls();

		foreach ( $sitemap_urls as $sitemap_url ) {
			wp_remote_get(
				$sitemap_url['pretty'],
				array(
					'blocking' => false,
					'timeout'  => 1,
				)
			);
		}
	}

	/**
	 * @return array|int[]
	 */
	private function check_all_sitemaps() {
		return $this->check_sitemaps( $this->get_sitemap_urls() );
	}

	/**
	 * @return array[]
	 */
	private function get_sitemap_urls() {
		$sitemap_urls = array(
			array(
				'pretty' => \smartcrawl_get_sitemap_url(),
				'plain'  => \smartcrawl_get_plain_sitemap_url(),
			),
			array(
				'pretty' => home_url( 'post-sitemap1.xml' ),
				'plain'  => \smartcrawl_get_plain_sitemap_url( 'post' ),
			),
		);

		$news_sitemap_enabled = Utils::get_sitemap_option( 'enable-news-sitemap' );
		if ( $news_sitemap_enabled ) {
			$sitemap_urls[] = array(
				'pretty' => \smartcrawl_get_news_sitemap_url(),
				'plain'  => \smartcrawl_get_plain_news_sitemap_url(),
			);
		}

		return $sitemap_urls;
	}

	/**
	 * @param $sitemaps
	 *
	 * @return array|int[]
	 */
	private function check_sitemaps( $sitemaps ) {
		foreach ( $sitemaps as $sitemap_urls ) {
			$pretty_permalink = $sitemap_urls['pretty'];
			$plain_permalink  = $sitemap_urls['plain'];

			$sitemap_name = $this->get_sitemap_name( $pretty_permalink );

			$status = $this->check_sitemap(
				$pretty_permalink,
				$plain_permalink
			);
			if ( self::SITEMAP_VALID !== $status ) {
				return array(
					'sitemap' => $sitemap_name,
					'status'  => $status,
				);
			}
		}

		return array(
			'status' => self::SITEMAP_VALID,
		);
	}

	/**
	 * @param $sitemap_response
	 *
	 * @return bool|int
	 */
	private function validate_sitemap_response( $sitemap_response ) {
		if ( empty( $sitemap_response ) ) {
			return self::SITEMAP_INVALID;
		}

		if ( is_wp_error( $sitemap_response ) ) {
			$this->wp_error = $sitemap_response;

			return self::SITEMAP_REQUEST_ERROR;
		}

		if ( $this->is_unauthorized_response( $sitemap_response ) ) {
			return self::SITEMAP_UNAUTHORIZED_REQUEST;
		}

		if (
			wp_remote_retrieve_response_code( $sitemap_response ) !== 200
			|| empty( wp_remote_retrieve_body( $sitemap_response ) )
		) {
			return self::SITEMAP_INVALID;
		}

		return true;
	}

	/**
	 * @param $pretty_url
	 * @param $plain_url
	 *
	 * @return bool|int
	 */
	private function check_sitemap( $pretty_url, $plain_url ) {
		$sitemap_name = $this->get_sitemap_name( $pretty_url );
		if ( $this->physical_sitemap_file_exists( $sitemap_name ) ) {
			return self::SITEMAP_PHYSICAL;
		}

		$sitemap_response          = $this->get_sitemap_response( $pretty_url );
		$is_valid_sitemap_response = $this->validate_sitemap_response( $sitemap_response );
		if ( true !== $is_valid_sitemap_response ) {
			return $is_valid_sitemap_response;
		}

		$sitemap_xml = $this->get_sitemap_xml( $sitemap_response );
		$xml_valid   = $this->is_xml_valid( $sitemap_xml );
		if ( $xml_valid ) {
			return $this->is_foreign_sitemap( $sitemap_xml )
				? self::SITEMAP_FOREIGN
				: self::SITEMAP_VALID;
		} elseif ( $this->xml_has_whitespace( $sitemap_xml ) ) {
			return self::SITEMAP_HAS_WHITESPACE;
		} else {
			$sitemap_plain_response  = $this->get_sitemap_response( $plain_url );
			$is_valid_plain_response = $this->validate_sitemap_response( $sitemap_plain_response );
			if ( true !== $is_valid_plain_response ) {
				return $is_valid_plain_response;
			}

			$sitemap_plain_xml = $this->get_sitemap_xml( $sitemap_plain_response );
			$plain_xml_valid   = $this->is_xml_valid( $sitemap_plain_xml );
			if ( $plain_xml_valid ) {
				if ( empty( get_option( 'permalink_structure' ) ) ) {
					return self::PERMALINKS_SETTING_PLAIN;
				} else {
					return self::SITEMAP_FAULTY_PERMALINK;
				}
			} else {
				return self::SITEMAP_INVALID;
			}
		}
	}

	/**
	 * @param $response
	 *
	 * @return bool
	 */
	private function is_unauthorized_response( $response ) {
		$response_code = wp_remote_retrieve_response_code( $response );

		return in_array( $response_code, array( 401, 403 ), true );
	}

	/**
	 * @param $sitemap_url
	 *
	 * @return mixed|string|null
	 */
	private function get_sitemap_name( $sitemap_url ) {
		$url_parts = explode( '/', $sitemap_url );

		return array_pop( $url_parts );
	}

	/**
	 * @param $sitemap_name
	 *
	 * @return bool
	 */
	private function physical_sitemap_file_exists( $sitemap_name ) {
		return file_exists( trailingslashit( ABSPATH ) . $sitemap_name );
	}

	/**
	 * @param $sitemap_xml
	 *
	 * @return bool
	 */
	private function is_foreign_sitemap( $sitemap_xml ) {
		return strpos( $sitemap_xml, Utils::SITEMAP_VERIFICATION_TOKEN ) === false;
	}

	/**
	 * @return array|mixed
	 */
	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( $_POST['_wds_nonce'], 'wds-nonce' ) ? stripslashes_deep( $_POST ) : array();
	}

	/**
	 * @return void
	 */
	private function clear_errors_found_option() {
		delete_option( self::ERRORS_FOUND_OPTION_ID );
	}

	/**
	 * Increase troubleshoot count.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	private function increase_troubleshoot_count() {
		$troubleshoot_count = Utils::get_sitemap_option( 'troubleshoot-count' );

		if ( empty( $troubleshoot_count ) ) {
			$troubleshoot_count = 0;
		}

		Utils::set_sitemap_option( 'troubleshoot-count', intval( $troubleshoot_count ) + 1 );
	}
}