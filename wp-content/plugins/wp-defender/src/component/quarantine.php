<?php

namespace WP_Defender\Component;

use Calotes\Base\Component;
use WP_Defender\Component\Scheduler\Scheduler;
use WP_Defender\Model\Scan_Item;
use WP_Defender\Model\Quarantine as Quarantine_Model;
use WP_Defender\Model\Setting\Scan as Scan_Setting;
use WP_Defender\Model\Setting\Main_Setting;
use WP_Defender\Traits\IO;
use WP_Defender\Traits\Plugin;
use WP_Defender\Traits\Formats;
use WP_Defender\Behavior\WPMUDEV;
use WP_Defender\Helper\File as File_Helper;

/**
 * Component class Quarantine.
 *
 * Service layer for quarantine files functionality.
 *
 * @since 4.0.0
 */
class Quarantine extends Component {

	use Plugin, Formats, IO;

	/**
	 * @var ?int Quarantine file expiry limit.
	 */
	private $file_expiry_timestamp;

	/**
	* @var Quarantine_Model
	*/
	private $quarantine_model;

	/**
	 * @var Scheduler
	 */
	private $scheduler;

	/**
	 * @var Scan_Setting
	 */
	private $scan_setting;

	/**
	 * @var Main_Setting
	 */
	private $main_setting;

	/**
	 * @var WPMUDEV
	 */
	private $wpmudev;

	/**
	 * Hook tag to delete expired files.
	 */
	private const CRON_HOOK = 'wpdef_quarantine_delete_expired';

	/**
	 * Quarantine directory name.
	 */
	private const QUARANTINE_DIRECTORY = '.defender-security-quarantine';

	/**
	 * Quarantine directory permission.
	 */
	public const QUARANTINE_DIRECTORY_PERMISSION = 0755;

	public function __construct() {
		// Dependencies.
		$this->quarantine_model = wd_di()->get( Quarantine_Model::class );
		$this->scheduler = wd_di()->get( Scheduler::class );
		$this->scan_setting = wd_di()->get( Scan_Setting::class );
		$this->main_setting = wd_di()->get( Main_Setting::class );
		$this->wpmudev = wd_di()->get( WPMUDEV::class );

		$this->file_expiry_timestamp = $this->set_expiry_timestamp();

		$this->init();
	}

	/**
	 * Get file permission in octal number.
	 *
	 * @param string $file File path.
	 *
	 * @return int File permission octal notation.
	 */
	private function get_octet_fileperms( $file ): int {
		clearstatcache();

		return (int) decoct( fileperms( $file ) & 0777 );
	}

	/**
	 * Prepare value for all columns of quarantine table.
	 *
	 * @param Scan_Item $scan_item Scan item model object.
	 * @return Quarantine_Model Quarantine model object.
	 */
	private function prepare_file_metadata( Scan_Item $scan_item ): Quarantine_Model {
		$file = $scan_item->raw_data['file'];

		$file_hash = (string) sha1_file( $file ) . uniqid();

		$path_info = pathinfo( $file );

		$mime = (string) mime_content_type( $file );

		$perms = $this->get_octet_fileperms( $file );

		$file_owner = (string) fileowner( $file );

		$file_group = (string) filegroup( $file );

		$plugin_headers = $this->get_plugin_headers( $file );
		$plugin_headers = reset( $plugin_headers );

		$file_version = isset( $plugin_headers['Version'] ) ? $plugin_headers['Version'] : '';

		$mtime = gmdate( 'Y-m-d H:i:s', (int) filemtime( $file ) );

		$source_slug = $this->get_plugin_directory_name( $file );

		$created_time = gmdate( 'Y-m-d H:i:s' );

		$created_by = get_current_user_id();

		$file_category = $this->quarantine_model::WP_PLUGIN;

		$this->quarantine_model->defender_scan_item_id = $scan_item->id;
		$this->quarantine_model->file_hash = $file_hash;
		$this->quarantine_model->file_full_path = $file;
		$this->quarantine_model->file_original_name = $path_info['filename'];
		$this->quarantine_model->file_extension = isset( $path_info['extension'] ) ? $path_info['extension'] : '';
		$this->quarantine_model->file_mime_type = $mime;
		$this->quarantine_model->file_rw_permission = $perms;
		$this->quarantine_model->file_owner = $file_owner;
		$this->quarantine_model->file_group = $file_group;
		$this->quarantine_model->file_version = $file_version;
		$this->quarantine_model->file_category = $file_category;
		$this->quarantine_model->file_modified_time = $mtime;
		$this->quarantine_model->source_slug = $source_slug;
		$this->quarantine_model->created_time = $created_time;
		$this->quarantine_model->created_by = $created_by;

		return $this->quarantine_model;
	}

	/**
	 * Logics to process before file manipulation.
	 *
	 * @param string $file_path File path of the plugin.
	 *
	 * @return array Array of plugin data. Keys plugin_basename, is_active & is_network_active.
	 */
	private function before_file_transaction( string $file_path ): array {
		// Plugin active state.
		$plugin_basename = plugin_basename( $file_path );
		$is_active = is_plugin_active( $plugin_basename );
		$is_network_active = is_plugin_active_for_network( $plugin_basename );

		// Deactivate plugin which are already activated. No effect on already deactivated plugin.
		if ( $is_active ) {
			deactivate_plugins( $plugin_basename, true, $is_network_active );
		}

		$result = [
			'plugin_basename' => $plugin_basename,
			'is_active' => $is_active,
			'is_network_active' => $is_network_active,
		];

		$this->log_wrapper( $result );

		return $result;
	}

	/**
	 * Logics to process before file manipulation.
	 *
	 * @param array $data Data needs to activate the plugin.
	 */
	private function after_file_transaction( array $data ): void {
		$this->log_wrapper( $data );

		// Activate plugin if already activates.
		if ( $data['is_active'] ) {
			activate_plugins( $data['plugin_basename'], '', $data['is_network_active'], true );
		}
	}

	/**
	 * Wrapper method which quarantine the file.
	 *
	 * Track plugin activation/deactivation state.
	 * Decide to deactivate plugin based on the state.
	 * Do core quarantine process.
	 * Reinstate previous state of plugin activation/deactivation.
	 *
	 * @param Scan_Item $scan_item Scan item model object.
	 *
	 * @param string $parent_action Parent action.
	 *
	 * @return array Index message: describes what happened.
	 *               Index success: true if file quarantined and quarantine record
	 *               created else false.
	 */
	public function quarantine_file( Scan_Item $scan_item, string $parent_action ): array {

		$before_file_transaction = $this->before_file_transaction( $scan_item->raw_data['file'] );

		// Do quarantine file processing.
		$action = $this->do_quarantine( $scan_item, $parent_action );

		$this->after_file_transaction( $before_file_transaction );

		$this->wpmudev->schedule_hub_sync();

		return $action;
	}

	/**
	 * Transfer file from source to destination.
	 *
	 * Depends on the parent action if action is repair then copy file to destination.
	 * Else move file to destination.
	 *
	 * @param string $from Source file path.
	 * @param string $to Destination file path.
	 * @param string $parent_action Parent action. As of now only two actions repair or delete.
	 *
	 * @return bool True on operation succeeded or False on operation failed.
	 */
	private function file_transfer( string $from, string $to, string $parent_action ): bool {
		if ( $parent_action === 'repair' ) {
			return copy( $from, $to );
		}

		return rename( $from, $to );
	}

	/**
	 * Core method which Quarantine the file.
	 *
	 * Do only file processing and DB handling.
	 *
	 * @param Scan_Item $scan_item Scan item model object.
	 *
	 * @param string $parent_action Parent action.
	 *
	 * @return array Index message: describes what happened.
	 *               Index success: true if file quarantined and quarantine record
	 *               created else false.
	 */
	private function do_quarantine( Scan_Item $scan_item, string $parent_action ): array {
		$quarantine_model = $this->prepare_file_metadata( $scan_item );

		$this->log_wrapper( $quarantine_model, 'Do quarantine: Prepare file metadata' );
		$this->log_wrapper( $parent_action, 'Do quarantine: Parent action name' );

		$file_name_with_extension = $quarantine_model->file_original_name . '.' . $quarantine_model->file_extension;

		$is_renamed = $this->file_transfer(
			$quarantine_model->file_full_path,
			$this->get_quarantined_file_path( $quarantine_model->file_hash ),
			$parent_action
		);

		if ( ! $is_renamed ) {
			$result = [
				/* translators: 1: Filename with extension */
				'message' => sprintf( __( 'Failed to quarantine the file %1$s.', 'wpdef' ), '<strong>' . $file_name_with_extension . '</strong>' ),
				'success' => false,
			];

			$this->log_wrapper( $result );

			return $result;
		}

		$saved = $quarantine_model->save();

		if ( ! is_int( $saved ) ) {
			$result = [
				/* translators: 1: Filename with extension */
				'message' => sprintf( __( 'Failed to add quarantine record in DB for the file %1$s.', 'wpdef' ), '<strong>' . $file_name_with_extension . '</strong>' ),
				'success' => false,
			];

			$this->log_wrapper( $result );

			return $result;
		}

		/* translators: 1: Filename with extension */
		$message = sprintf( __( 'Quarantined the file %1$s and deleted the source file successfully.', 'wpdef' ), '<strong>' . $file_name_with_extension . '</strong>' );

		if ( $parent_action === 'repair' ) {
			$plugin_write_handler = $this->plugin_write_handler( $quarantine_model );

			if ( $plugin_write_handler['success'] === false ) {
				$this->log_wrapper( $plugin_write_handler, 'Plugin write failed' );

				$this->restore_file( $scan_item );

				return $plugin_write_handler;
			}

			/* translators: 1: Filename with extension */
			$message = sprintf( __( 'Quarantined the file %1$s and repaired the source file successfully.', 'wpdef' ), '<strong>' . $file_name_with_extension . '</strong>' );
		}

		$result = [
			'message' => $message,
			'success' => true,
		];

		$scan_item->delete_by_id( $scan_item->id );

		$this->log_wrapper( $result );

		return $result;
	}

	/**
	 * Restore the quarantined file either using Scan_Item object or Quarantine record primary key.
	 *
	 * @param Scan_Item|int $entity To determine which file to restore.
	 *
	 * @return array Index message: describes what happened.
	 *               Index success: true if file moved and quarantine record
	 *               removed else false.
	 */
	public function restore_file( $entity ): array {

		if ( $entity instanceof Scan_Item ) {
			$file_metadata = $this->get_record_by_scan_item( $entity );
		} elseif ( is_int( $entity ) ) {
			$file_metadata = $this->quarantine_model->find_by_id( $entity );
		}

		$this->log_wrapper( $file_metadata );

		if (
			! isset(
				$file_metadata,
				$file_metadata->file_hash,
				$file_metadata->file_full_path,
				$file_metadata->id,
				$file_metadata->file_original_name,
				$file_metadata->file_extension
			)
		) {
			$result = [
				'message' => __( 'Record not exists in DB.', 'wpdef' ),
				'success' => false,
			];

			$this->log_wrapper( $result );

			return $result;
		}

		$file_name_with_extension = $file_metadata->file_original_name . '.' . $file_metadata->file_extension;

		if ( ! $this->is_quarantine_file_exists( $file_metadata->file_hash ) ) {
			$result = [
				'message' => __( 'Quarantined file doesn\'t exist in the quarantine directory.', 'wpdef' ),
				'success' => false,
			];

			$this->log_wrapper( $result, 'External error: File System Error or Quarantined file deleted by someone using OS CMD' );

			return $result;
		}

		$plugin_headers = $this->get_plugin_headers( $file_metadata->file_full_path );

		if ( empty( $plugin_headers ) ) {
			$result = [
				/* translators: 1: Filename with extension */
				'message' => sprintf( __( 'Plugin main file missing for %1$s file.', 'wpdef' ), '<strong>' . $file_name_with_extension . '</strong>' ),
				'success' => false,
			];

			$this->log_wrapper( $result );
		}

		$quarantined_file_path = $this->get_quarantined_file_path( $file_metadata->file_hash );

		$before_file_transaction = $this->before_file_transaction( $file_metadata->file_full_path );

		$is_renamed = rename( $quarantined_file_path, $file_metadata->file_full_path );

		$this->after_file_transaction( $before_file_transaction );

		if ( ! $is_renamed ) {
			$result = [
				'message' => __( 'Failed to restore quarantined file.', 'wpdef' ),
				'success' => false,
			];

			$this->log_wrapper( $result );

			return $result;
		}

		$is_delete_success = $this->quarantine_model->delete( (int) $file_metadata->id );

		if ( ! $is_delete_success ) {
			$result = [
				'message' => __( 'Failed to remove quarantine record from DB.', 'wpdef' ),
				'success' => false,
			];

			$this->log_wrapper( $result );

			return $result;
		}

		$result = [
			/* translators: 1: Filename with extension */
			'message' => sprintf( __( 'Restored %1$s', 'wpdef' ), '<strong>' . $file_name_with_extension . '</strong>' ),
			'success' => true,
		];

		$this->log_wrapper( $result );

		$this->wpmudev->schedule_hub_sync();

		return $result;
	}

	/**
	 * Return Quarantine model.
	 *
	 * @param Scan_Item $scan_item Scan_Item object which individual record of scan data.
	 *
	 * @return mixed Return Quarantine model if exists.
	 */
	private function get_record_by_scan_item( Scan_Item $scan_item ) {
		return $this->quarantine_model->select_restore_detail( $scan_item );
	}

	private function is_quarantined( Scan_Item $scan_item ): bool {
		$quarantined_record = $this->quarantine_model->select_by_file_full_path( $scan_item->raw_data['file'] );

		$is_file_exists = false;

		if ( isset( $quarantined_record[0] ) ) {
			$is_file_exists = $this->is_quarantine_file_exists( $quarantined_record[0]->file_hash );
		}

		return $is_file_exists;
	}

	private function get_quarantine_directory(): string {
		$quarantine_dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . self::QUARANTINE_DIRECTORY;

		if ( ! is_dir( $quarantine_dir ) ) {
			mkdir( $quarantine_dir, self::QUARANTINE_DIRECTORY_PERMISSION, true );
		}

		/**
		 * @var File_Helper
		 */
		$file_helper = wd_di()->get( File_Helper::class );
		$file_helper->maybe_dir_access_deny( $quarantine_dir );

		return $quarantine_dir;
	}

	private function get_quarantined_file_path( string $file_hash ): string {
		return $this->get_quarantine_directory() . DIRECTORY_SEPARATOR . $file_hash . '.restore';
	}

	/**
	 * Writes plugin file by fetching from wp.org repo.
	 *
	 * @param Quarantine_Model $quarantine_model SQL ORM object.
	 *
	 * @return array Index message: describes what happened.
	 *               Index success: true if writes successfully
	 *               else false on failed to write or WP plugin repository errors.
	 */
	private function plugin_write_handler( Quarantine_Model $quarantine_model ): array {
		$plugin_directory_name = $this->get_plugin_directory_name( $quarantine_model->file_full_path );

		if ( ! $this->is_likely_wporg_slug( $plugin_directory_name ) ) {
			return [
				'message' => __( 'Make sure plugin exists in WordPress plugin repository.', 'wpdef' ),
				'success' => false,
			];
		}

		$plugin_relative_path = $this->get_plugin_relative_path( $quarantine_model->file_full_path );
		$is_plugin_in_wp_org = $this->check_plugin_on_wp_org( $plugin_directory_name );

		$file_name_with_extension = $quarantine_model->file_original_name . '.' . $quarantine_model->file_extension;
		$generic_error = [
			/* translators: 1: Filename with extension */
			'message' => sprintf( __( 'Failed to write the file %1$s.', 'wpdef' ), '<strong>' . $file_name_with_extension . '</strong>' ),
			'success' => false,
		];

		if (
			isset( $is_plugin_in_wp_org['success'] ) &&
			$is_plugin_in_wp_org['success'] === true
		) {
			$file_url = $this->get_file_url( $plugin_directory_name, $quarantine_model->file_version, $plugin_relative_path );

			$file_content = $this->get_url_content( $file_url );

			$is_written = file_put_contents( $quarantine_model->file_full_path, $file_content );

			if ( is_int( $is_written ) ) {
				return [
					/* translators: 1: Filename with extension */
					'message' => sprintf( __( 'Writes the file %1$s successfully.', 'wpdef' ), '<strong>' . $file_name_with_extension . '</strong>' ),
					'success' => true,
				];
			}

			return $generic_error;
		} elseif (
			isset( $is_plugin_in_wp_org['success'], $is_plugin_in_wp_org['message'] ) &&
			$is_plugin_in_wp_org['success'] === false
		) {
			$error = $is_plugin_in_wp_org['message'];

			return [
				/* translators: 1: WordPress API error message */
				'message' => sprintf( __( 'WordPress remote error: %1$s', 'wpdef' ), '<strong>' . $error . '</strong>' ),
				'success' => false,
			];
		}

		return $generic_error;
	}

	private function is_quarantine_file_exists( string $file_hash ): bool {
		return file_exists( $this->get_quarantined_file_path( $file_hash ) );
	}

	public function quarantine_collection(): array {
		$collections = $this->quarantine_model->quarantine_collection();

		foreach ( $collections as $index => $collection ) {
			$name = '';

			$plugin_headers = $this->get_plugin_headers( $collection['file_full_path'] );

			if ( is_array( $plugin_headers ) ) {
				$plugin_headers = reset( $plugin_headers );

				$name = isset( $plugin_headers['Name'] ) ? $plugin_headers['Name'] : '';
			}

			$collections[ $index ]['name'] = $name;

			$collections[ $index ]['file_modified_time'] =
				$this->format_date_time( $collection['file_modified_time'] );
			$collections[ $index ]['created_time'] =
				$this->format_date_time( $collection['created_time'] );
		}

		return $collections;
	}

	/**
	 * Hard delete the quarantined file and remove the DB record.
	 *
	 * @param int $id Primary key of the record need to be deleted with associated file.
	 *
	 * @return bool True on success else false.
	 */
	public function delete_quarantined_file( int $id ): bool {
		$file_metadata = $this->quarantine_model->find_by_id( $id );

		$this->log_wrapper( $file_metadata, 'On deletion: File metadata' );

		$is_file_exists = $this->is_quarantine_file_exists( $file_metadata->file_hash );

		$this->log_wrapper( 'File exists? ' . $is_file_exists );

		if (
			! is_null( $file_metadata ) &&
			$is_file_exists
		) {
			$is_deleted = unlink(
				$this->get_quarantined_file_path(
					$file_metadata->file_hash
				)
			);

			$this->log_wrapper( 'File deleted? ' . $is_deleted );

			if ( $is_deleted ) {
				$is_model_deleted = $this->quarantine_model->delete( $id );

				$this->log_wrapper( 'Model deleted? ' . $is_model_deleted );

				$this->wpmudev->schedule_hub_sync();

				return $is_model_deleted;
			}
		}

		$this->log_wrapper( 'Check SQL row & quarantined file exists for PK: ' . $id );

		return false;
	}

	/**
	 * Delete files which are older the expiry time limit.
	 */
	private function delete_old_file(): void {
		if ( is_int( $this->file_expiry_timestamp ) ) {
			$expiry_limit = gmdate( 'Y-m-d H:i:s', $this->file_expiry_timestamp );

			$old_records = $this->quarantine_model->get_old_records( $expiry_limit );

			foreach ( $old_records as $file_id ) {
				$this->delete_quarantined_file( (int) $file_id['id'] );
			}
		}
	}

	/**
	 * Cron schedule delete old files.
	 */
	public function cron_process(): void {
		$this->delete_old_file();
	}

	/**
	 * Invoke all init methods.
	 */
	public function init(): void {
		/**
		 * Delete old quarantined files.
		 */
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event(
				time(),
				$this->scan_setting->quarantine_expire_schedule,
				self::CRON_HOOK
			);
		}

		add_action( self::CRON_HOOK, [ &$this, 'cron_process' ] );

		$this->get_quarantine_directory();
	}

	/**
	 * List of cron schedules utilized by quarantine expiry settings.
	 *
	 * @return array List of WP cron schedules relevant to quarantine period.
	 */
	public function cron_schedules(): array {
		return $this->scheduler->filter_cron_schedules(
			[
				'thirty_days',
				'sixty_days',
				'ninety_days',
				'six_months',
				'one_year',
			]
		);
	}

	/**
	 * Calculate threshold timestamp.
	 *
	 * This timestamp is used to delete sql record created before threshold
	 * timestamp and it's associated file.
	 *
	 * @return ?int Threshold timestamp.
	 */
	private function set_expiry_timestamp(): ?int {
		$schedule_name = $this->scan_setting->quarantine_expire_schedule;

		$get_schedule = $this->scheduler->filter_cron_schedules(
			[ $schedule_name ]
		);

		if ( isset( $get_schedule[ $schedule_name ] ) ) {
			$quarantined_time_threshold =
				(int) strtotime( 'now' ) -
				$get_schedule[ $schedule_name ]['interval'];

			return $quarantined_time_threshold;
		}

		return null;
	}

	/**
	 * Reschedule the cron.
	 *
	 * This method helps to change schedule when expiry period changed and delete
	 * quarantine sql records and associated file if the quarantined time is expired.
	 *
	 * @param string $prev Previously selected cron interval.
	 * @param string $current Currently selected cron interval.
	 */
	public function reschedule_file_expiry_cron( string $prev, string $current ): void {
		// If prev cron interval and current cron interval same then early return.
		if ( strcmp( $prev, $current ) === 0 ) {
			return;
		}

		// Else if prev cron interval not equal to current cron interval.
		// Replace the previous scheduler with new.
		$this->scheduler->override_schedule( self::CRON_HOOK, $current );

		// Invoke cron process immediately to delete expired file related to current interval.
		$this->cron_process();
	}

	/**
	 * Get quarantine directory URL.
	 *
	 * @return string Quarantine directory URL.
	 */
	public function quarantine_directory_url(): string {
		return content_url( self::QUARANTINE_DIRECTORY );
	}

	/**
	 * Actions to accomplish before plugin uninstallation.
	 *
	 * If Remove settings chosen then directly remove data i.e. without archiving the quarantined file and table data.
	 */
	public function on_uninstall(): void {
		$method_suffix = $this->main_setting->uninstall_quarantine;

		if ( method_exists( $this, 'data_' . $method_suffix ) ) {
			$this->{'data_' . $method_suffix}();
		}
	}

	/**
	 * Remove quarantine file system & table data.
	 */
	private function data_remove(): void {
		$this->delete_dir( $this->get_quarantine_directory() );
		$this->quarantine_model->drop_table();
	}

	/**
	 * Log wrapper method.
	 *
	 * @param mixed $message Details need to be write accepts string or array or object.
	 * @param string $custom_trace_title Description about custom trace event.
	 * @param bool $in_depth_debug If true in depth trace else if false then simple trace.
	 * @param int $in_depth_limit How much deep to trace. Works only for true assigned for $in_depth_debug.
	 * @param string $category Log filename.
	 */
	private function log_wrapper(
		$message,
		string $custom_trace_title = '',
		bool $in_depth_debug = false,
		int $in_depth_limit = 1,
		string $category = 'quarantine'
	): void {
		$this->log( 'Backtrace Summary:', $category );
		$this->log( wp_debug_backtrace_summary( null, 1, false ), $category );

		$this->log( 'Custom Trace:', $category );

		if ( ! empty( $custom_trace_title ) ) {
			$this->log( $custom_trace_title, $category );
		}

		$this->log( $message, $category );

		if ( $in_depth_debug === true ) {
			$this->log( 'Detailed Debug:', $category );
			$this->log( debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, $in_depth_limit ), $category );
		}
	}

	/**
	 * Data required from quarantine on scan item.
	 *
	 * @param Scan_Item $scan_item Scan item model object.
	 *
	 * @return array Array of data required from quarantine on scan item.
	 */
	public function scan_item_data( Scan_Item $scan_item ): array {
		return [
			'is_quarantined' => $this->is_quarantined( $scan_item ),
			'is_quarantinable' => $this->is_quarantinable( $scan_item->raw_data['file'] ),
		];
	}

	/**
	 * Array of quarantined files and it's source file details for HUB widget listing.
	 *
	 * @return array Quarantined files details.
	 */
	public function hub_list(): array {
		$model_list = $this->quarantine_model->hub_list();

		$hub_api_list = [];

		foreach ( $model_list as $quarantine_item ) {
			$id = $quarantine_item['id'];
			$file_name = $quarantine_item['file_original_name'] . '.' . $quarantine_item['file_extension'];
			$quarantined_time = strtotime( $quarantine_item['quarantined_time'] );
			$quarantined_path = $this->get_quarantined_file_path( $quarantine_item['quarantined_path'] );
			$source_path = $quarantine_item['source_path'];

			array_push(
				$hub_api_list,
				compact(
					'id',
					'file_name',
					'quarantined_time',
					'quarantined_path',
					'source_path'
				)
			);
		}

		return $hub_api_list;
	}

	/**
	 * Check the quarantine directory is forbidden.
	 *
	 * When user visits the URL of the quarantine directory it should not be available.
	 * This function returns false if we get the 200 status code and true for any other status codes.
	 *
	 * @return bool
	 */
	public function is_quarantine_directory_url_forbidden(): bool {
		$response = wp_remote_head(
			$this->quarantine_directory_url(),
			[ 'timeout' => 5 ]
		);

		$is_200 = (int) wp_remote_retrieve_response_code( $response ) === 200;

		return ! $is_200;
	}
}