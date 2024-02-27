<?php

namespace SmartCrawl;

class Simple_Renderer extends Renderable {

	use Singleton;

	public static function render( $view, $args = array() ) {
		$instance = self::get();
		$instance->render_view( $view, $args );
	}

	public static function load( $view, $args = array() ) {
		$instance = self::get();

		return $instance->load_view( $view, $args );
	}

	protected function get_view_defaults() {
		return array();
	}
}