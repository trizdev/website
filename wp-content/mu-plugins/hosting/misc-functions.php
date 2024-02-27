<?php
/**
 * WPMU DEV Hosting Miscellaneous Functions
 */

/**
 * Disable the Delete button from Plugins list for WPMU DEV Dashboard.
 */
function wpmudev_hosting_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
	// Remove deactivate link for important plugins.
	if ( array_key_exists( 'deactivate', $actions ) && in_array( $plugin_file, array( 'wpmudev-updates/update-notifications.php' ), true ) ) {
		unset( $actions['deactivate'] );
	}

	// Remove delete links for important plugins.
	if ( array_key_exists( 'delete', $actions ) && in_array( $plugin_file, array( 'wpmudev-updates/update-notifications.php' ), true ) ) {
		unset( $actions['delete'] );
	}

	return $actions;
}
add_filter( 'plugin_action_links', 'wpmudev_hosting_plugin_actions', 30, 4 );
add_filter( 'network_admin_plugin_action_links', 'wpmudev_hosting_plugin_actions', 30, 4 );

add_action(
	'delete_plugin',
	function( $plugin ) {
		if ( 'wpmudev-updates/update-notifications.php' === $plugin ) {
			if ( defined( 'DOING_AJAX' ) ) {
				$status = array(
					'delete'       => 'plugin',
					'slug'         => 'wpmu-dev-dashboard',
					'errorMessage' => __( 'Sorry, you are not allowed to delete this plugin.' ),
				);
				wp_send_json_error( $status );
			} elseif ( ( isset( $_POST['action'] ) && 'delete-selected' === $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'delete-selected' === $_POST['action2'] ) ) {
				wp_redirect( self_admin_url( 'plugins.php' ) );
				exit;
			}
		}
	}
);

/**
 * Disable the Deactivate button from Plugins list for WPMU DEV Dashboard.
 */
add_action(
	'deactivate_plugin',
	function( $plugin ) {
		if ( ( ( isset( $_POST['action'] ) && 'deactivate-selected' === $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'deactivate-selected' === $_POST['action2'] ) ) && 'wpmudev-updates/update-notifications.php' === $plugin ) {
			wp_redirect( self_admin_url( 'plugins.php' ) );
			exit;
		}
	}
);

function wpmudev_hosting_plugin_cap_filter( $allcaps, $cap, $args ) {
	// Bail out if we're not asking about deactivating a plugin.
	if ( 'deactivate_plugin' !== $args[0] ) {
		return $allcaps;
	}

	if ( 'wpmudev-updates/update-notifications.php' !== $args[2] ) {
		return $allcaps;
	}

	$allcaps[ $cap[0] ] = false;

	return $allcaps;
}
add_filter( 'user_has_cap', 'wpmudev_hosting_plugin_cap_filter', 10, 3 );

function wpmudev_hosting_plugin_cap_filter_super( $caps, $cap, $user_id, $args ) {
	if ( 'deactivate_plugin' === $cap && 'wpmudev-updates/update-notifications.php' === $args[0] ) {
		$caps[] = 'do_not_allow';
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'wpmudev_hosting_plugin_cap_filter_super', 10, 4 );

/**
 * Customize the upgrade PHP link in Site Health.
 */
add_filter(
	'wp_update_php_url',
	function( $url ) {
		return 'https://premium.wpmudev.org/docs/hosting/tools-features/#php-versions';
	}
);

/**
 * Customize the direct upgrade PHP button in nag message.
 */
add_filter(
	'wp_direct_php_update_url',
	function( $url ) {
		return sprintf( 'https://premium.wpmudev.org/hub/hosting/?view=site&site_id=%s&tab=tools', WPMUDEV_HOSTING_SITE_ID );
	}
);

/**
 * Redirect to primary domain on single sites.
 * Fixes the cases of both temporary domain & primary domain being accessed directly.
 */
/**
 add_action(
	'plugins_loaded',
	function() {
		// Get the home url if exists.
		$home_url = get_home_url();
		if (
			defined( 'WP_CLI' ) && WP_CLI ||
			defined( 'WP_INSTALLING' ) && WP_INSTALLING ||
			'staging' === $_SERVER['WPMUDEV_HOSTING_ENV'] ||
			is_multisite() ||
			empty( $home_url )
		) {
			return;
		}

		// Set the temp domain regex.
		$temp_domain = "/\bwpmudev.host\b/i";

		// Find the primary domain.
		$primary_domain = parse_url( $home_url, PHP_URL_HOST );

		// Find the domain the request was made from.
		$request_domain = $_SERVER['HTTP_HOST'];

		// Find the URI of the request.
		$request_uri = $_SERVER['REQUEST_URI'];

		// If our primary domain is not a temporary domain.
		if ( ! preg_match( $temp_domain, $primary_domain ) ) {
			// If the request domain is a temporary domain or if it's not the same as the primary then redirect.
			if ( preg_match( $temp_domain, $request_domain ) || $primary_domain !== $request_domain ) {
				wp_safe_redirect( 'https://' . $primary_domain . $request_uri, 301, 'WordPress' );
				exit;
			}
		}
	}
);
*/

/**
 * WPMU DEV Hosting function for plugins to hook in and clear Static Server Cache on demand.
 */
function wpmudev_hosting_purge_static_cache( $path = '' ) {
	// Setup.
	$domain   = untrailingslashit( get_site_url( null, '', 'https' ) );
	$resolver = str_replace( array( 'http://', 'https://' ), '', $domain ) . ':443:127.0.0.1';

	// Default to purging all.
	$url = $domain . '/*';

	// Adjust the PURGE URI.
	if ( empty( $path ) ) {
		// Purge all.
		$url = $domain . '/*';
	} elseif ( '/' === $path ) {
		// Purge homepage.
		$url = $domain . '/$';
	} else {
		// Purge specific URI.
		$url = $domain . $path;
	}

	// Curl.
	$ch = curl_init();
	curl_setopt_array(
		$ch,
		array(
			CURLOPT_URL                  => $url,
			CURLOPT_RETURNTRANSFER       => true,
			CURLOPT_NOBODY               => false,
			CURLOPT_HEADER               => true,
			CURLOPT_CUSTOMREQUEST        => 'PURGE',
			CURLOPT_FOLLOWLOCATION       => true,
			CURLOPT_DNS_USE_GLOBAL_CACHE => false,
			CURLOPT_RESOLVE              => array(
				$resolver,
			),
		)
	);

	// Response.
	$response    = curl_exec( $ch );
	$header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
	$header      = substr( $response, 0, $header_size );
	$body        = substr( $response, $header_size );
	curl_close( $ch );

	if ( preg_match( '/^OK/', $body ) ) {
		return true;
	} else {
		return false;
	}
}
