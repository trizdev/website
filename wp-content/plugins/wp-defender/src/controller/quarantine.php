<?php

namespace WP_Defender\Controller;

use Calotes\Component\Request;
use Calotes\Component\Response;
use WP_Defender\Controller;
use WP_Defender\Component\Quarantine as Quarantine_Component;

class Quarantine extends Controller {

	/**
	 * @var Quarantine_Component
	 */
	private $quarantine_component;

	public function __construct() {
		$this->register_routes();
		$this->quarantine_component = wd_di()->get( Quarantine_Component::class );
	}

	/**
	 * @return array
	 */
	public function data_frontend(): array {

		$quarantine_directory = [
			'url' => $this->quarantine_component->quarantine_directory_url(),
			'permission' => $this->quarantine_component::QUARANTINE_DIRECTORY_PERMISSION,
			'is_quarantine_directory_url_forbidden' => $this->quarantine_component->is_quarantine_directory_url_forbidden(),
		];

		return array_merge(
			[
				'list' => $this->quarantine_component->quarantine_collection(),
				'cron_schedules' => $this->quarantine_component->cron_schedules(),
				'quarantine_directory' => $quarantine_directory,
			],
			$this->dump_routes_and_nonces()
		);
	}

	/**
	 * @return array[]
	 */
	public function to_array(): array {
		return [];
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function import_data( $data ): void {}

	public function remove_settings() {
	}

	public function remove_data() {
		$this->quarantine_component->on_uninstall();
	}

	/**
	 * @return array
	 */
	public function export_strings(): array {
		return [];
	}

	/**
	 * Restore the quarantined file.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 * @defender_route
	 */
	public function restore_file( Request $request ) {
		$data = $request->get_data(
			[
				'id' => [
					'type' => 'int',
				],
			]
		);

		$action = $this->quarantine_component->restore_file( $data['id'] );

		if ( isset( $action['success'] ) && $action['success'] === true ) {
			return new Response(
				true,
				[
					'message' => $action['message'],
					'file_id' => $data['id'],
					'success' => true,
					'quarantine_collection' => $this->quarantine_component->quarantine_collection(),
				]
			);
		}

		return new Response(
			false,
			[
				'message' => $action['message'],
				'file_id' => $data['id'],
				'success' => false,
			]
		);
	}

	/**
	 * Get quarantine collection.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 * @defender_route
	 */
	public function quarantine_collection( Request $request ) {
		$data = $this->quarantine_component->quarantine_collection();

		return new Response(
			true,
			[
				'list' => $data,
			]
		);
	}

	/**
	 * Delete quarantined file.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 * @defender_route
	 */
	public function delete_file( Request $request ): Response {
		$data = $request->get_data(
			[
				'id' => [
					'type' => 'int',
				],
				'file_name' => [
					'type' => 'string',
				],
			]
		);

		$action = $this->quarantine_component->delete_quarantined_file( $data['id'] );

		if ( $action ) {
			return new Response(
				true,
				[
					/* translators: 1: Filename with extension */
					'message' => sprintf( __( 'Deleted %1$s permanently.', 'wpdef' ), '<strong>' . $data['file_name'] . '</strong>' ),
					'file_id' => $data['id'],
					'success' => true,
					'quarantine_collection' => $this->quarantine_component->quarantine_collection(),
				]
			);
		}

		return new Response(
			false,
			[
				'message' =>
					sprintf(
						__(
							'Deleting <strong>%s</strong> failed.',
							'wpdef'
						), $data['file_name']
					),
				'file_id' => $data['id'],
				'success' => false,
			]
		);
	}
}