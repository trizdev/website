<?php

namespace SmartCrawl\Configs;

use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Controllers;
use SmartCrawl\Controllers\Onboard;

class Controller extends Controllers\Controller {

	use Singleton;

	private $service;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->service = new Service();

		parent::__construct();
	}

	protected function init() {
		add_action( 'wp_ajax_wds_apply_config', array( $this, 'apply_config_handler' ) );
		add_action( 'wp_ajax_wds_sync_hub_configs', array( $this, 'sync_hub_configs' ) );
		add_action( 'wp_ajax_wds_create_new_config', array( $this, 'create_new_config' ) );
		add_action( 'wp_ajax_wds_update_config', array( $this, 'update_config' ) );
		add_action( 'wp_ajax_wds_delete_config', array( $this, 'delete_config' ) );
		add_action( 'wp_ajax_wds_upload_config', array( $this, 'upload_config' ) );
	}

	public function apply_config_handler() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}
		$config_id = \smartcrawl_get_array_value( $data, 'config_id' );
		if ( ! $config_id ) {
			wp_send_json_error();
		}
		$collection = Collection::get();
		$config     = $collection->get_by_id( $config_id );
		if ( ! $config ) {
			wp_send_json_error();
		}
		$configs = $config->get_configs();
		$this->apply_config( $configs );
		wp_send_json_success();
	}

	public function sync_hub_configs() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}
		$collection = Collection::get();
		if ( $this->service->is_member() ) {
			$synced = $collection->sync_with_hub();
			if ( ! $synced ) {
				wp_send_json_error();
			}
		}
		wp_send_json_success(
			array(
				'configs' => $collection->get_deflated_configs(),
			)
		);
	}

	public function create_new_config() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$name        = sanitize_text_field( \smartcrawl_get_array_value( $data, 'name' ) );
		$description = sanitize_text_field( \smartcrawl_get_array_value( $data, 'description' ) );
		if ( empty( $name ) ) {
			wp_send_json_error();
		}

		$config = Model::create_from_plugin_snapshot( $name, $description );
		if ( $this->service->is_member() ) {
			$response = $this->service->publish_config( $config );
			if ( empty( $response['id'] ) ) {
				wp_send_json_error();
			} else {
				$config->set_hub_id( $response['id'] );
			}
		}
		$collection = Collection::get();
		$collection->add( $config );
		$collection->save();
		wp_send_json_success(
			array(
				'config_id' => $config->get_id(),
				'configs'   => $collection->get_deflated_configs(),
			)
		);
	}

	public function update_config() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$config_id   = \smartcrawl_get_array_value( $data, 'config_id' );
		$name        = \smartcrawl_get_array_value( $data, 'name' );
		$description = \smartcrawl_get_array_value( $data, 'description' );
		if ( ! $config_id || ! $name ) {
			wp_send_json_error();
		}

		$collection = Collection::get();
		$config     = $collection->get_by_id( $config_id );
		if ( ! $config ) {
			wp_send_json_error();
		}
		$config->set_name( sanitize_text_field( $name ) );
		$config->set_description( sanitize_text_field( $description ) );
		if ( $this->service->is_member() ) {
			if ( $config->get_hub_id() ) {
				$response = $this->service->update_config( $config );
			} else {
				$response = $this->service->publish_config( $config );
				if ( ! empty( $response['id'] ) ) {
					$config->set_hub_id( $response['id'] );
				}
			}
			if ( ! $response ) {
				wp_send_json_error();
			}
		}
		$collection->save();
		wp_send_json_success(
			array(
				'configs' => $collection->get_deflated_configs(),
			)
		);
	}

	public function delete_config() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$config_id = \smartcrawl_get_array_value( $data, 'config_id' );
		if ( ! $config_id ) {
			wp_send_json_error();
		}

		$collection = Collection::get();
		$config     = $collection->get_by_id( $config_id );
		if ( ! $config ) {
			wp_send_json_error();
		}
		if ( $this->service->is_member() ) {
			$response = $this->service->delete_config( $config );
			if ( ! $response ) {
				wp_send_json_error();
			}
		}
		$collection->remove( $config );
		$collection->save();
		wp_send_json_success(
			array(
				'configs' => $collection->get_deflated_configs(),
			)
		);
	}

	public function upload_config() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$config_json = file_get_contents( $_FILES['file']['tmp_name'] ); // phpcs:ignore
		if ( ! $config_json ) {
			wp_send_json_error();
		}

		$config = Model::inflate( json_decode( $config_json, true ) );
		if ( ! $config->get_id() ) {
			wp_send_json_error();
		}
		$config->refresh_id();
		$config->set_timestamp( time() );
		$collection = Collection::get();
		if ( $this->service->is_member() ) {
			$response = $this->service->publish_config( $config );
			if ( empty( $response['id'] ) ) {
				wp_send_json_error();
			} else {
				$config->set_hub_id( $response['id'] );
			}
		}
		$collection->add( $config );
		$collection->save();
		wp_send_json_success(
			array(
				'config_id' => $config->get_id(),
				'configs'   => $collection->get_deflated_configs(),
			)
		);
	}

	private function get_request_data() {
		// Only admins should have access.
		if ( ! $this->has_permission() ) {
			return array();
		}

		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wds_nonce'] ), 'wds-configs-nonce' ) ? stripslashes_deep( $_POST ) : array(); // phpcs:ignore
	}

	private function apply_basic_config() {
		// Reset everything else so defaults can be applied.
		foreach ( Settings::get_all_components() as $component ) {
			if ( Settings::COMP_HEALTH !== $component ) {
				Settings::delete_component_options( $component );
			}
		}
		Settings::delete_specific_options( 'wds_settings_options' );
	}

	public function apply_config( $configs ) {
		$is_basic_config = empty( $configs['options'] );
		if ( $is_basic_config ) {
			$this->apply_basic_config();
		} else {
			Import::load( wp_json_encode( $configs ) )->save();
		}

		Onboard::get()->mark_onboarding_done();
	}

	/**
	 * Check if current user has permission to perform actions.
	 *
	 * @since 3.3.1
	 *
	 * @return bool
	 */
	private function has_permission() {
		// Site admins only.
		$cap = 'manage_options';
		// If only super admins should access.
		if ( is_multisite() && \smartcrawl_subsite_manager_role() === 'superadmin' ) {
			$cap = 'manage_network_options';
		}

		// Only admins should have access.
		return current_user_can( $cap );
	}
}