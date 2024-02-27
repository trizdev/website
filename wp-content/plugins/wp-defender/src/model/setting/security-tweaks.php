<?php

namespace WP_Defender\Model\Setting;

use Calotes\Model\Setting;

class Security_Tweaks extends Setting {
	protected $table = 'hardener_settings';

	/**
	 * Store a list of issues tweaks, as slug.
	 * @var array
	 */
	public $issues = [];

	/**
	 * Store a list of fixed tweaks, as slug.
	 * @var array
	 */
	public $fixed = [];

	/**
	 * Store a list of ignored tweaks, as slug.
	 * @var array
	 */
	public $ignore = [];

	/**
	 * Contains all the data generated by rules.
	 * @var array
	 */
	public $data = [];

	/**
	 * Last time visit into the hardener page.
	 * @var integer
	 */
	public $last_seen;

	/**
	 * Last notification sent out.
	 * @var integer
	 */
	public $last_sent;

	public $automate = false;

	/**
	 * @param $slug
	 *
	 * @return bool
	 */
	public function is_tweak_ignore( $slug ): bool {
		// Empty ignored tweak is string on old versions, so change it to array.
		if ( is_string( $this->ignore ) ) {
			$this->ignore = empty( $this->ignore ) ? [] : array( $this->ignore );
			$this->save();
		}

		return in_array( $slug, $this->ignore, true );
	}

	/**
	 * @param $as
	 * @param $slug
	 */
	public function mark( $as, $slug ) {
		foreach ( [ 'issues', 'fixed', 'ignore' ] as $list ) {
			$arr = $this->$list;
			$index = array_search( $slug, $arr, true );
			if ( $index !== false ) {
				unset( $arr[ $index ] );
			}
			$this->$list = $arr;
		}
		if ( \WP_Defender\Controller\Security_Tweaks::STATUS_RESTORE === $as ) {
			$as = \WP_Defender\Controller\Security_Tweaks::STATUS_ISSUES;
		}
		$list = $this->{$as};
		$list[] = $slug;
		$this->{$as} = $list;
		$this->save();
	}

	/**
	 * Define settings labels.
	 *
	 * @return array
	 */
	public function labels(): array {
		return [
			'data' => 'data',
			'fixed' => __( 'Actioned', 'wpdef' ),
			'issues' => __( 'Recommendations', 'wpdef' ),
			'ignore' => __( 'Ignored', 'wpdef' ),
			'automate' => 'automate',
		];
	}

	/**
	 * Todo: Find a less expensive way.
	 *
	 * @return array
	 */
	public function get_tweak_types(): array {
		return [
			'count_fixed' => count( $this->fixed ),
			'count_ignored' => count( $this->ignore ),
			'count_issues' => count( $this->issues ),
		];
	}
}