<?php
/**
 * Class to handle mixpanel advanced tools events functionality.
 *
 * @since   3.10.0
 * @package SmartCrawl
 */

namespace SmartCrawl\Mixpanel;

use SmartCrawl\Admin\Settings\Settings;
use SmartCrawl\Redirects;
use SmartCrawl\Singleton;

/**
 * Mixpanel Tools Event class
 */
class Advanced extends Events {

	use Singleton;

	/**
	 * Initialize class.
	 *
	 * @since 3.10.0
	 */
	protected function init() {
		add_action( 'update_option_' . Settings::ADVANCED_MODULE, array( $this, 'intercept_advanced_update' ), 10, 2 );
		add_action( 'smartcrawl_after_sanitize_autolinks', array( $this, 'intercept_autolinks_update' ), 10, 2 );
		add_action( 'smartcrawl_after_sanitize_redirects', array( $this, 'intercept_redirects_update' ), 10, 2 );
		add_action( 'smartcrawl_after_save_redirect', array( $this, 'intercept_redirect_save' ), 10, 2 );
		add_action( 'smartcrawl_after_sanitize_woocommerce', array( $this, 'intercept_woo_seo_update' ), 10, 2 );
		add_action( 'smartcrawl_after_sanitize_seomoz', array( $this, 'intercept_seomoz_update' ), 10, 2 );
		add_action( 'smartcrawl_after_sanitize_robots', array( $this, 'intercept_robots_txt_update' ), 10, 2 );
		add_action( 'smartcrawl_after_sanitize_breadcrumbs', array( $this, 'intercept_breadcrumbs_update' ), 10, 2 );
	}

	/**
	 * Handles Advanced Tools update.
	 *
	 * @param array $old_values The old option value.
	 * @param array $new_values The new option value.
	 *
	 * @return void
	 *
	 * @since 3.7.0
	 */
	public function intercept_advanced_update( $old_values, $new_values ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), Settings::SETTINGS_MODULE . '_options-options' ) ) {
			return;
		}

		$old_status = $this->get_value( 'active', $old_values );
		$new_status = $this->get_value( 'active', $new_values );

		$controller = \SmartCrawl\Modules\Advanced\Controller::get();

		foreach ( $controller->submodules as $sm_name => $sm_controller ) {
			if ( $sm_controller->is_active() ) {
				$sm_old_status = $this->get_value( 'active', $old_values[ $sm_name ] );
				$sm_new_status = $this->get_value( 'active', $new_values[ $sm_name ] );

				if ( $new_status ) {
					if (
						( ! $old_status && $sm_new_status ) ||
						( $old_status && $sm_old_status !== $sm_new_status )
					) {
						$this->tracker()->track(
							$sm_new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
							array(
								'advanced_tool'  => $sm_controller->module_title,
								'triggered_from' => 'General Settings',
							)
						);
					}
				} elseif ( $old_status && $sm_old_status ) {
						$this->tracker()->track(
							'SMA - Advanced Tool Deactivated',
							array(
								'advanced_tool'  => $sm_controller->module_title,
								'triggered_from' => 'General Settings',
							)
						);
				}
			}
		}
	}

	/**
	 * Handles Automatic Links settings update.
	 *
	 * @param array $old_values The old option value.
	 * @param array $new_values The new option value.
	 *
	 * @return void
	 *
	 * @since 3.10.0
	 */
	public function intercept_autolinks_update( $old_values, $new_values ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		if ( ! \smartcrawl_is_build_type_full() ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), Settings::SETTINGS_MODULE . '_options-options' ) ) {
			return;
		}

		$old_status = $this->get_value( 'active', $old_values );
		$new_status = $this->get_value( 'active', $new_values );

		if ( $old_status === $new_status ) {
			return;
		}

		$this->tracker()->track(
			$new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'Automatic Links',
				'triggered_from' => 'Automatic Links',
			)
		);

		$old_fields = array();
		$new_fields = array();

		foreach (
			array(
				'insert',
				'link_to',
				'customkey',
			)
			as $field
		) {
			$old_fields[ $field ] = $this->get_value( $field, $old_values );
			$new_fields[ $field ] = $this->get_value( $field, $new_values );
		}

		if ( $old_fields === $new_fields ) {
			return;
		}

		$this->tracker()->track(
			'SMA - Automatic Links',
			array(
				'insert_links_count' => count( $new_fields['insert'] ),
				'link_to_count'      => count( $new_fields['link_to'] ),
				'custom_links_count' => count( array_filter( explode( "\n", $new_fields['customkey'] ) ) ),
			)
		);
	}


	/**
	 * Handles redirects update.
	 *
	 * @param array $old_values The old option value.
	 * @param array $new_values The new option value.
	 *
	 * @return void
	 *
	 * @since 3.10.0
	 */
	public function intercept_redirects_update( $old_values, $new_values ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), Settings::SETTINGS_MODULE . '_options-options' ) ) {
			return;
		}

		$old_status = $this->get_value( 'active', $old_values );
		$new_status = $this->get_value( 'active', $new_values );

		if ( $old_status === $new_status ) {
			return;
		}

		$this->tracker()->track(
			$new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'URL Redirection',
				'triggered_from' => 'URL Redirection',
			)
		);
	}

	/**
	 * Handles saving redirect.
	 *
	 * @param array $old_values The old option value.
	 * @param array $new_values The new option value.
	 *
	 * @return void
	 *
	 * @since 3.10.0
	 */
	public function intercept_redirect_save( $old_values, $new_values ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), Settings::SETTINGS_MODULE . '_options-options' ) ) {
			return;
		}

		if ( ! \smartcrawl_array_diff( $old_values, $new_values ) ) {
			return;
		}

		$redirect_table = \SmartCrawl\Modules\Advanced\Redirects\Database_Table::get();

		$rules          = ! empty( $new_values['rules'] ) ? json_decode( $new_values['rules'], true ) : array();
		$from_countries = array();
		$to_countries   = array();

		foreach ( $rules as $rule ) {
			if ( empty( $rule['indicate'] ) ) {
				$from_countries = array_merge( $from_countries, $rule['countries'] );
			} else {
				$to_countries = array_merge( $to_countries, $rule['countries'] );
			}
		}

		$this->tracker()->track(
			'SMA - Redirection',
			array(
				'number_redirects'   => $redirect_table->get_redirect_count(),
				'redirect_type'      => $new_values['type'],
				'regex'              => in_array( 'regex', explode( ',', $new_values['options'] ), true ) ? 'Yes' : 'No',
				'countries_from'     => implode( ', ', $from_countries ),
				'countries_not_from' => implode( ', ', $to_countries ),
				'is_location_based'  => ! empty( $rules ) ? 'Yes' : 'No',
			)
		);
	}

	/**
	 * Handles Woocommerce SEO update.
	 *
	 * @param array $old_values The old option value.
	 * @param array $new_values The new option value.
	 *
	 * @return void
	 *
	 * @since 3.10.0
	 */
	public function intercept_woo_seo_update( $old_values, $new_values ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), Settings::SETTINGS_MODULE . '_options-options' ) ) {
			return;
		}

		$old_status = $this->get_value( 'active', $old_values );
		$new_status = $this->get_value( 'active', $new_values );

		if ( $old_status === $new_status ) {
			return;
		}

		$this->tracker()->track(
			$new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'WooCommerce SEO',
				'triggered_from' => 'WooCommerce SEO',
			)
		);
	}

	/**
	 * Handles Moz api settings update.
	 *
	 * @param array $old_values The old option value.
	 * @param array $new_values The new option value.
	 *
	 * @since 3.10.0
	 *
	 * @return void
	 */
	public function intercept_seomoz_update( $old_values, $new_values ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), Settings::SETTINGS_MODULE . '_options-options' ) ) {
			return;
		}

		$old_status = $this->get_value( 'active', $old_values );
		$new_status = $this->get_value( 'active', $new_values );

		$old_access_id = $this->get_value( 'access_id', $old_values );
		$new_access_id = $this->get_value( 'access_id', $new_values );

		$old_secret_key = $this->get_value( 'secret_key', $old_values );
		$new_secret_key = $this->get_value( 'secret_key', $new_values );

		if ( empty( $old_access_id ) || empty( $old_secret_key ) ) {
			$old_status = false;
		}

		if ( empty( $new_access_id ) || empty( $new_secret_key ) ) {
			$new_status = false;
		}

		if ( $old_status === $new_status && $old_access_id === $new_access_id && $old_secret_key === $new_secret_key ) {
			return;
		}

		$this->tracker()->track(
			$new_status ?
				'SMA - Advanced Tool Activated' :
				'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'MOZ',
				'triggered_from' => 'MOZ',
			)
		);
	}

	/**
	 * Handles Robots.txt settings update.
	 *
	 * @param array $old_values The old option value.
	 * @param array $new_values The new option value.
	 *
	 * @return void
	 *
	 * @since 3.10.0
	 */
	public function intercept_robots_txt_update( $old_values, $new_values ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), Settings::SETTINGS_MODULE . '_options-options' ) ) {
			return;
		}

		$old_status = $this->get_value( 'active', $old_values );
		$new_status = $this->get_value( 'active', $new_values );

		if ( $old_status === $new_status ) {
			return;
		}

		$this->tracker()->track(
			$new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'Robots.txt Editor',
				'triggered_from' => 'Robots.txt Editor',
			)
		);
	}

	/**
	 * Handles Breadcrumbs settings update.
	 *
	 * @param array $old_values The old option value.
	 * @param array $new_values The new option value.
	 *
	 * @return void
	 *
	 * @since 3.10.0
	 */
	public function intercept_breadcrumbs_update( $old_values, $new_values ) {
		if ( ! $this->is_tracking_active() ) {
			return;
		}

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), Settings::SETTINGS_MODULE . '_options-options' ) ) {
			return;
		}

		$old_status = $this->get_value( 'active', $old_values );
		$new_status = $this->get_value( 'active', $new_values );

		if ( $old_status === $new_status ) {
			return;
		}

		$this->tracker()->track(
			$new_status ? 'SMA - Advanced Tool Activated' : 'SMA - Advanced Tool Deactivated',
			array(
				'advanced_tool'  => 'Breadcrumbs',
				'triggered_from' => 'Breadcrumbs',
			)
		);
	}
}