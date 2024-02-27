<?php

namespace SmartCrawl\Sitemaps;

use SmartCrawl\Logger;
use SmartCrawl\Singleton;

class Cache {
	const CACHE_FILE_NAME_FORMAT = '%s-sitemap%d.xml';
	const CACHE_PRISTINE_OPTION  = 'wds_sitemap_cache_pristine';

	use Singleton;

	/**
	 * @param $type
	 * @param $page
	 * @param $sitemap
	 *
	 * @return bool
	 */
	public function set_cached( $type, $page, $sitemap ) {
		return $this->write_to_cache_file(
			$this->cache_file_name( $type, $page ),
			$sitemap
		);
	}

	/**
	 * @param $type
	 * @param $page
	 *
	 * @return false|string
	 */
	public function get_cached( $type, $page ) {
		if ( $this->is_cache_pristine() ) {
			return $this->get_from_cache_file( $this->cache_file_name( $type, $page ) );
		}

		$this->drop_cache();
		return false;
	}

	/**
	 * @return bool
	 */
	public function drop_cache() {
		$file_system = $this->fs_direct();
		$cache_dir   = $this->get_cache_dir();
		if ( empty( $cache_dir ) ) {
			Logger::error( 'Sitemap cache could not be dropped because it does not exist' );
			return false;
		}

		$removed = $file_system->rmdir( $cache_dir, true );
		if ( ! $removed ) {
			Logger::error( 'Sitemap cache directory could not be removed' );
			return false;
		}

		$this->set_cache_pristine( true ); // An empty cache is a pristine cache.
		Logger::info( 'Sitemap cache dropped' );
		return true;
	}

	/**
	 * @return \WP_Filesystem_Direct
	 */
	private function fs_direct() {
		if ( ! class_exists( '\WP_Filesystem_Direct', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		}
		return new \WP_Filesystem_Direct( null );
	}

	/**
	 * @param $type
	 * @param $page
	 *
	 * @return mixed|void
	 */
	private function cache_file_name( $type, $page ) {
		$file_name = sprintf( self::CACHE_FILE_NAME_FORMAT, $type, $page );

		return apply_filters( 'wds_sitemap_cache_file_name', $file_name, $type, $page );
	}

	/**
	 * @param $filename
	 * @param $contents
	 *
	 * @return bool
	 */
	private function write_to_cache_file( $filename, $contents ) {
		$path = $this->get_cache_dir( $filename );
		if (
			empty( $path )
			|| ! \smartcrawl_file_put_contents( $path, $contents, LOCK_EX )
		) {
			Logger::error( "Failed writing sitemap cache file to [$path]" );
			return false;
		}

		Logger::info( "Added file to sitemap cache: [$path]" );
		return true;
	}

	/**
	 * @param $filename
	 *
	 * @return false|string
	 */
	private function get_from_cache_file( $filename ) {
		$path = $this->get_cache_dir( $filename );

		if ( ! empty( $path ) && file_exists( $path ) ) {
			Logger::info( "Sitemap file read from cache: [$path]" );
			return \smartcrawl_file_get_contents( $path );
		}

		Logger::info( "Sitemap file not found in cache: [$path]" );
		return false;
	}

	/**
	 * @param $postfix
	 *
	 * @return false|string
	 */
	public function get_cache_dir( $postfix = '' ) {
		$path = \smartcrawl_uploads_dir();
		$path = "{$path}sitemap/";

		// Attempt to create the dir in case it doesn't already exist.
		$dir_exists = wp_mkdir_p( $path );
		if ( ! $dir_exists ) {
			Logger::error( "Sitemap cache directory could not be created at [$path]" );
			return false;
		}

		return "$path$postfix";
	}

	/**
	 * @return bool
	 */
	public function is_cache_pristine() {
		return in_array(
			get_current_blog_id(),
			$this->get_sitemap_pristine_option(),
			true
		);
	}

	/**
	 * @return void
	 */
	public function invalidate() {
		$this->set_cache_pristine( false );
	}

	/**
	 * @param $value
	 *
	 * @return void
	 */
	private function set_cache_pristine( $value ) {
		$pristine        = $this->get_sitemap_pristine_option();
		$current_site_id = get_current_blog_id();

		if ( $value ) {
			if ( ! in_array( $current_site_id, $pristine, true ) ) {
				$pristine[] = $current_site_id;
				$this->update_sitemap_pristine_option( $pristine );
			}
		} elseif ( ! is_multisite() ) {
				// The single site is out of date now so drop everything.
				$this->delete_sitemap_pristine_option();
		} else {
			$this->update_sitemap_pristine_option(
				array_diff( $pristine, array( $current_site_id ) )
			);
		}
	}

	/**
	 * @return array
	 */
	private function get_sitemap_pristine_option() {
		$value = get_site_option( self::CACHE_PRISTINE_OPTION, array() );
		return is_array( $value )
			? $value
			: array();
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	private function update_sitemap_pristine_option( $value ) {
		return update_site_option( self::CACHE_PRISTINE_OPTION, $value );
	}

	/**
	 * @return bool
	 */
	private function delete_sitemap_pristine_option() {
		return delete_site_option( self::CACHE_PRISTINE_OPTION );
	}

	/**
	 * @return bool
	 */
	public function is_writable() {
		return is_writeable( $this->get_cache_dir() );
	}

	/**
	 * @return bool
	 */
	public function is_index_cached() {
		if ( ! $this->is_cache_pristine() ) {
			// If cache is not pristine, we don't care if the file exists or not.
			return false;
		}

		$file_name = $this->cache_file_name( Front::SITEMAP_TYPE_INDEX, 0 );
		$path      = $this->get_cache_dir( $file_name );

		return ! empty( $path ) && file_exists( $path );
	}
}