<?php

namespace SmartCrawl\Sitemaps\General\Queries;

use SmartCrawl\Settings;
use SmartCrawl\Sitemaps\Query;
use SmartCrawl\Singleton;
use SmartCrawl\Sitemaps\Utils;
use SmartCrawl\Sitemaps\General\Item;

class BP_Groups extends Query {

	use Singleton;

	const TYPE = 'bp_groups';

	/**
	 * @return string[]
	 */
	public function get_supported_types() {
		return array( self::TYPE );
	}

	/**
	 * @return array|Item[]
	 */
	public function get_items( $type = '', $page_number = 0 ) {
		if ( ! $this->can_return_items() ) {
			return array();
		}

		$groups = $this->get_groups( $page_number );
		$items  = array();

		foreach ( $groups as $group ) {
			$url = bp_get_group_permalink( $group );
			if ( $this->is_group_excluded( $group ) || Utils::is_url_ignored( $url ) ) {
				continue;
			}

			$item = new Item();
			$item->set_location( $url )
				->set_last_modified( strtotime( $group->last_activity ) )
				->set_images( $this->get_group_images( $group ) );

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * @return array Images
	 */
	private function get_group_images( $group ) {
		if ( ! Utils::sitemap_images_enabled() ) {
			return array();
		}

		$markup  = $group->description;
		$markup .= $this->get_group_avatar( $group );
		$images  = $this->find_images( $markup );

		$cover = $this->get_group_cover_url( $group );
		if ( $cover ) {
			$images[] = array(
				'src'   => $cover,
				'title' => '',
				'alt'   => '',
			);
		}

		return $images;
	}

	/**
	 * @return string
	 */
	private function get_group_avatar( $group ) {
		return function_exists( '\bp_core_fetch_avatar' )
			? \bp_core_fetch_avatar(
				array(
					'item_id' => $group->id,
					'object'  => 'group',
					'type'    => 'full',
					'html'    => true,
				)
			)
			: '';
	}

	/**
	 * @return string|void
	 */
	private function get_group_cover_url( $group ) {
		return function_exists( '\bp_attachments_get_attachment' )
			? \bp_attachments_get_attachment(
				'url',
				array(
					'object_dir' => 'groups',
					'item_id'    => $group->id,
				)
			)
			: '';
	}

	/**
	 * @return bool
	 */
	public function can_handle_type( $type ) {
		return parent::can_handle_type( $type )
			&& $this->can_return_items();
	}

	/**
	 * @return bool
	 */
	private function can_return_items() {
		return defined( '\BP_VERSION' )
			&& \smartcrawl_is_main_bp_site()
			&& function_exists( '\groups_get_groups' )
			&& function_exists( '\bp_get_group_permalink' )
			&& $this->bp_groups_enabled();
	}

	/**
	 * @return string
	 */
	public function get_filter_prefix() {
		return 'wds-sitemap-bp_groups';
	}

	/**
	 * @return bool
	 */
	private function bp_groups_enabled() {
		$options = $this->get_options();

		return ! empty( $options['sitemap-buddypress-groups'] );
	}

	/**
	 * @return \BP_Groups_Group[]
	 */
	private function get_groups( $page_number ) {
		$per_page = $this->get_limit( $page_number );

		$groups = groups_get_groups(
			array(
				'per_page' => $per_page,
				'page'     => $page_number,
				'orderby'  => 'last_activity',
				'order'    => 'ASC',
			)
		);

		return ! empty( $groups['groups'] ) ? $groups['groups'] : array();
	}

	/**
	 * @return boolean
	 */
	private function is_group_excluded( $group ) {
		$options = $this->get_options();

		return ! empty( $options[ "sitemap-buddypress-exclude-buddypress-group-$group->slug" ] );
	}

	/**
	 * @return array
	 */
	private function get_options() {
		return Settings::get_options();
	}
}