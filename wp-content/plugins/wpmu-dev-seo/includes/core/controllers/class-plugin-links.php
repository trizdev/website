<?php

namespace SmartCrawl\Controllers;

use SmartCrawl\Singleton;
use SmartCrawl\Services\Service;
use SmartCrawl\Settings;

class Plugin_Links extends Controller {

	use Singleton;

	protected function init() {
		add_filter( 'plugin_action_links_' . SMARTCRAWL_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		return true;
	}

	public function add_settings_link( $links ) {
		if ( ! is_array( $links ) ) {
			return $links;
		}

		$service = Service::get( Service::SERVICE_SITE );
		if ( ! $service->is_member() ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s" style="color: #8D00B1;">%s</a>',
					'https://wpmudev.com/project/smartcrawl-wordpress-seo/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=smartcrawl_pluginlist_renew',
					esc_html( __( 'Renew Membership', 'wds' ) )
				)
			);
		}

		array_unshift(
			$links,
			sprintf(
				'<a href="%s">%s</a>',
				'https://wpmudev.com/docs/wpmu-dev-plugins/smartcrawl/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=smartcrawl_pluginlist_docs',
				esc_html( __( 'Docs', 'wds' ) )
			)
		);

		array_unshift(
			$links,
			sprintf(
				'<a href="%s">%s</a>',
				\SmartCrawl\Admin\Settings\Admin_Settings::admin_url( Settings::TAB_DASHBOARD ),
				esc_html( __( 'Dashboard', 'wds' ) )
			)
		);

		return $links;
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( SMARTCRAWL_PLUGIN_BASENAME === $plugin_file ) {
			if ( isset( $plugin_meta[2] ) ) {
				$plugin_meta[2] = '<a href="https://wpmudev.com/project/smartcrawl-wordpress-seo/" target="_blank">' . esc_html__( 'View Details', 'wds' ) . '</a>';
			}

			$row_meta = array(
				'support' => '<a href="https://wpmudev.com/get-support/" target="_blank">' . esc_html__( 'Support', 'wds' ) . '</a>',
				'roadmap' => '<a href="https://wpmudev.com/roadmap/" target="_blank">' . esc_html__( 'Roadmap', 'wds' ) . '</a>',
			);

			$plugin_meta = array_merge( $plugin_meta, $row_meta );
		}

		return $plugin_meta;
	}
}