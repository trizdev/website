<?php

namespace SmartCrawl\Sitemaps\General;

use SmartCrawl\Sitemaps\Index_Item;

class Item extends Index_Item {
	/**
	 * @var array
	 */
	private $images = array();

	/**
	 * @return array
	 */
	public function get_images() {
		return $this->images;
	}

	/**
	 * @return $this
	 */
	public function set_images( $images ) {
		$this->images = $images;

		return $this;
	}

	/**
	 * @return string
	 */
	private function images_xml() {
		$images = array();
		foreach ( $this->get_images() as $image ) {
			$images[] = $this->image_xml( $image );
		}
		return join( "\n", $images );
	}

	/**
	 * @return string
	 */
	private function image_xml( $image ) {
		$text = ! empty( $image['title'] )
			? $image['title']
			: (string) \smartcrawl_get_array_value( $image, 'alt' );
		$src  = (string) \smartcrawl_get_array_value( $image, 'src' );

		$image_tag  = '<image:image>';
		$image_tag .= '<image:loc>' . esc_url( $src ) . '</image:loc>';
		$image_tag .= '<image:title>' . esc_xml( $text ) . '</image:title>';
		$image_tag .= '</image:image>';

		return $image_tag;
	}

	/**
	 * @return string
	 */
	public function to_xml() {
		$tags = array();

		$location = $this->get_location();
		if ( empty( $location ) ) {
			\SmartCrawl\Logger::error( 'Sitemap item with empty location found' );
			return '';
		}

		$tags[] = sprintf( '<loc>%s</loc>', esc_url( $location ) );

		// Last modified date.
		$tags[] = sprintf( '<lastmod>%s</lastmod>', $this->format_timestamp( $this->get_last_modified() ) );

		// Images.
		$images = $this->images_xml();
		if ( ! empty( $images ) ) {
			$tags[] = $images;
		}

		return sprintf( "<url>\n%s\n</url>", implode( "\n", $tags ) );
	}
}