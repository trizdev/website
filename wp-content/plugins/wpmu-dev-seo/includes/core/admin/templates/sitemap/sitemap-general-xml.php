<?php
/**
 * @var $items \SmartCrawl\Sitemaps\General\Item[]
 */

namespace SmartCrawl;

use SmartCrawl\Controllers\White_Label;
use SmartCrawl\Sitemaps\Utils;

$items = empty( $items ) ? array() : $items;
if ( ! $items ) {
	return;
}
$stylesheet_enabled     = Utils::stylesheet_enabled();
$sitemap_images_enabled = Utils::sitemap_images_enabled();
$hide_branding          = White_Label::get()->is_hide_wpmudev_branding();
$plugin_dir_url         = SMARTCRAWL_PLUGIN_URL;

echo '<?xml version="1.0" encoding="UTF-8"?>';

if ( $stylesheet_enabled ) {
	$xsl_url = home_url( '?wds_sitemap_styling=1&template=sitemapBody' );
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
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 https://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd"
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	<?php if ( $sitemap_images_enabled ) : ?>
		xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
	<?php endif; ?>
>
	<?php foreach ( $items as $item ) : ?>
		<?php echo $item->to_xml(); ?>
	<?php endforeach; ?>
</urlset>