<?php

namespace SmartCrawl\Lighthouse;

use SmartCrawl\Singleton;
use SmartCrawl\Renderable;
use SmartCrawl\Services\Service;

class Dashboard_Renderer extends Renderable {

	use Singleton;

	/**
	 * @return void
	 */
	public static function render( $view, $args = array() ) {
		$instance = self::get();
		$instance->render_view( $view, $args );
	}

	/**
	 * @return false|mixed
	 */
	public static function load( $view, $args = array() ) {
		$instance = self::get();

		return $instance->load_view( $view, $args );
	}

	/**
	 * @return array
	 */
	protected function get_view_defaults() {
		/**
		 * @var \SmartCrawl\Services\Lighthouse $lighthouse Service
		 */
		$lighthouse = Service::get( Service::SERVICE_LIGHTHOUSE );
		$device     = Options::dashboard_widget_device();

		if ( ! in_array( $device, array( 'desktop', 'mobile' ), true ) ) {
			$device = 'desktop';
		}

		return array(
			'lighthouse_start_time' => $lighthouse->get_start_time(),
			'lighthouse_report'     => $lighthouse->get_last_report( $device ),
		);
	}
}