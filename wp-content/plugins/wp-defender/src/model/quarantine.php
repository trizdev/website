<?php

namespace WP_Defender\Model;

use WP_Defender\DB;
use WP_Defender\Model\Scan_Item;

/**
 * Model class Quarantine.
 *
 * Handles quarantined files DB table.
 *
 * @since 4.0.0
 */
class Quarantine extends DB {

	public const WP_UNCATEGORIZED = 0;
	public const WP_CORE = 1;
	public const WP_PLUGIN = 2;
	public const WP_THEME = 3;
	public const WP_DROPINS = 4;

	protected $table = 'defender_quarantine';

	/**
	 * @var int
	 * @defender_property
	 */
	public $id;

	/**
	 * @var int
	 * @defender_property
	 */
	public $defender_scan_item_id;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_hash;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_full_path;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_original_name;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_extension;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_mime_type;

	/**
	 * @var int
	 * @defender_property
	 */
	public $file_rw_permission;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_owner;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_group;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_version;

	/**
	 * @var int
	 * @defender_property
	 */
	public $file_category;

	/**
	 * @var string
	 * @defender_property
	 */
	public $file_modified_time;

	/**
	 * @var string
	 * @defender_property
	 */
	public $source_slug;

	/**
	 * @var string
	 * @defender_property
	 */
	public $created_time;

	/**
	 * @var int
	 * @defender_property
	 */
	public $created_by;

	public function is_quarantined( Scan_Item $scan_item ): bool {
		global $wpdb;

		$scan_item_id = $scan_item->id;
		$file_path = $scan_item->raw_data['file'];
		$table_name = $wpdb->prefix . $this->table;

			$sql = <<<SQL
		SELECT EXISTS(
			SELECT
				1
			FROM $table_name
			WHERE
				`defender_scan_item_id` = %d
				OR `file_full_path` = %s
			ORDER BY
				`created_time` DESC
			LIMIT 0,
			1
		)
SQL;

		$records = (int) $wpdb->get_var(
			$wpdb->prepare(
				$sql,
				$scan_item_id,
				$file_path
			)
		);

		return $records === 1;
	}

	public function delete( int $id ): bool {
		$delete = self::get_orm()->get_repository( self::class )
		->delete( [ 'id' => $id ] );

		return is_int( $delete );
	}

	public function select_by_scan_item_id( int $scan_item_id ): array {
		return self::get_orm()->get_repository( self::class )->select( '' )
			->where( 'defender_scan_item_id', $scan_item_id )->get();
	}

	public function select_by_file_full_path( string $file_full_path ): array {
		return self::get_orm()->get_repository( self::class )->select( '' )
			->where( 'file_full_path', $file_full_path )->get();
	}

	/**
	 * Select restoring file metadata.
	 *
	 * @param Scan_Item $scan_item
	 *
	 * @return array|object|null|void SQL record for file metadata.
	 */
	public function select_restore_detail( Scan_Item $scan_item ) {
		global $wpdb;

		$scan_item_id = $scan_item->id;
		$file_path = $scan_item->raw_data['file'];
		$table_name = $wpdb->prefix . $this->table;

		$sql = <<<SQL
		SELECT
			*
		FROM $table_name
		WHERE
			`defender_scan_item_id` = %d
			OR `file_full_path` = %s
		ORDER BY
			`created_time` DESC
		LIMIT 0,
		1
SQL;

		$records = $wpdb->get_row(
			$wpdb->prepare(
				$sql,
				$scan_item_id,
				$file_path
			)
		);

		return $records;
	}

	public function quarantine_collection(): array {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$sql = <<<SQL
		SELECT
			$table_name.id,
			file_hash,
			file_original_name,
			file_extension,
			source_slug,
			created_by,
			file_full_path,
			file_modified_time,
			created_time,
			display_name as user_display_name
		FROM $table_name
		LEFT JOIN $wpdb->users
		ON $table_name.created_by = $wpdb->users.id
		ORDER BY $table_name.id
		DESC
SQL;

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get record by primary key.
	 *
	 * @param int $id Primary key of the record.
	 *
	 * @return Quarantine|null Return Quarantine model on fetched else null.
	 */
	public function find_by_id( int $id ) {
		$orm = self::get_orm();

		$record = $orm->get_repository( self::class )->find_by_id( $id )->get();

		return isset( $record[0] ) ? $record[0] : null;
	}

	/**
	 * Get records which are created older than the $expiry_limit
	 *
	 * @param string $expiry_limit Expiry limit date time in mysql format.
	 *
	 * @return array Array of quarantined files primary key if exists else empty array.
	 */
	public function get_old_records( string $expiry_limit ): array {
		$orm = self::get_orm();
		$builder = $orm->get_repository( self::class );

		$records = $builder->select( 'id' )
			->where( 'created_time', '<=', $expiry_limit )->get_results();

		return $records;
	}

	/**
	 * Drop quarantine table.
	 */
	public function drop_table(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$wpdb->query(
			"DROP TABLE IF EXISTS $table_name"
		);
	}

	/**
	 * SQL to fetch array of last 5 recently quarantined files.
	 */
	public function hub_list(): array {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$sql = <<<SQL
		SELECT
			$table_name.id,
			file_original_name,
			file_extension,
			created_time as quarantined_time,
			file_hash as quarantined_path,
			file_full_path as source_path
		FROM $table_name
		ORDER BY quarantined_time DESC
		LIMIT 0, 5
SQL;

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}