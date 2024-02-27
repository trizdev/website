<?php
/**
 * @var $index_items \SmartCrawl\Sitemaps\Index_Item[]
 */

namespace SmartCrawl;

use SmartCrawl\Controllers\White_Label;
use SmartCrawl\Sitemaps\Utils;

$index_items        = empty( $index_items ) ? array() : $index_items;
$hide_branding      = White_Label::get()->is_hide_wpmudev_branding();
$stylesheet_enabled = Utils::stylesheet_enabled();
$plugin_dir_url     = SMARTCRAWL_PLUGIN_URL;

echo "<?xml version='1.0' encoding='UTF-8'?>";

if ( $stylesheet_enabled ) {
	$xsl_url = home_url( '?wds_sitemap_styling=1&template=sitemapIndexBody' );
	$xsl_url = str_replace( array( 'http:', 'https:' ), '', $xsl_url );

	if ( $hide_branding ) {
		$xsl_url .= '&whitelabel=1';
	}

	$xsl_url = esc_url( $xsl_url );

	echo "<?xml-stylesheet type='text/xml' href='{$xsl_url}'?>";
}
?>
<!-- <?php echo Utils::SITEMAP_VERIFICATION_TOKEN; ?> -->
<sitemapindex
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<?php foreach ( $index_items as $index_item ) : ?>
		<?php echo $index_item->to_xml(); ?>
	<?php endforeach; ?>
</sitemapindex>