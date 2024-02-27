<?php

namespace SmartCrawl\Services;

/**
 * Pass through service class
 *
 * Used to check membership info throughout the non-service code
 */
class Site extends Service {

	public function get_service_base_url() {
	}

	public function get_request_url( $verb ) {
	}

	public function get_request_arguments( $verb ) {
	}

	public function get_known_verbs() {
	}

	public function is_cacheable_verb( $verb ) {
	}

	public function handle_error_response( $response, $verb ) {
	}
}