<?php

namespace SmartCrawl\Multisite;

use SmartCrawl\Configs;
use SmartCrawl\Singleton;
use SmartCrawl\Controllers;

class Network_Configs extends Controllers\Controller {

	use Singleton;

	public function should_run() {
		return is_multisite();
	}

	protected function init() {
		add_action( 'wp_initialize_site', array( $this, 'apply_config' ), 99 );
		add_action( 'activate_blog', array( $this, 'apply_config' ) );
	}

	private function get_subsite_config_id() {
		return get_site_option( 'wds_subsite_config_id', '' );
	}

	/**
	 * Actually apply the config to the current site
	 *
	 * @param mixed $blog Blog.
	 */
	public function apply_config( $blog ) {
		$config_id = $this->get_subsite_config_id();
		if ( empty( $config_id ) ) {
			return;
		}

		$config_collection = Configs\Collection::get();
		$config            = $config_collection->get_by_id( $config_id );
		if ( ! $config ) {
			return;
		}

		if ( is_numeric( $blog ) ) {
			$blog_id = (int) $blog;
		} elseif ( is_a( $blog, '\WP_Site' ) ) {
			$blog_id = $blog->blog_id;
		}
		if ( empty( $blog_id ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		Configs\Controller::get()->apply_config( $config->get_configs() );
		restore_current_blog();
	}
}