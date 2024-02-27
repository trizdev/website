<?php
/**
 * @var $news_items \SmartCrawl\Sitemaps\News\Item[]
 */

namespace SmartCrawl;

use SmartCrawl\Controllers\White_Label;
use SmartCrawl\Sitemaps\Utils;

$news_items         = empty( $news_items ) ? array() : $news_items;
$hide_branding      = White_Label::get()->is_hide_wpmudev_branding();
$stylesheet_enabled = Utils::stylesheet_enabled();
$plugin_dir_url     = SMARTCRAWL_PLUGIN_URL;

echo "<?xml version='1.0' encoding='UTF-8'?>";

if ( $stylesheet_enabled ) {
	$xsl_url = home_url( '?wds_sitemap_styling=1&template=newsSitemapBody' );
	$xsl_url = str_replace( array( 'http:', 'https:' ), '', $xsl_url );

	if ( $hide_branding ) {
		$xsl_url .= '&whitelabel=1';
	}

	$xsl_url = esc_url( $xsl_url );

	echo "<?xml-stylesheet type='text/xml' href='{$xsl_url}'?>";
}
?>
<!-- <?php echo Utils::SITEMAP_VERIFICATION_TOKEN; ?> -->
<urlset
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
>
	<?php foreach ( $news_items as $news_item ) : ?>
		<?php echo $news_item->to_xml(); ?>
	<?php endforeach; ?>
</urlset>