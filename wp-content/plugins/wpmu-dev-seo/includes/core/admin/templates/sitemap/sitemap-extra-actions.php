<?php

namespace SmartCrawl;

use SmartCrawl\Services\Service;

$is_member = ! empty( $_view['is_member'] );
if ( ! $is_member ) {
	return;
}
$service = Service::get( Service::SERVICE_SEO );
/**
 * Report.
 *
 * @var Seo_Report $crawl_report
 */
$crawl_report = empty( $_view['crawl_report'] ) ? null : $_view['crawl_report'];
if ( ! $crawl_report ) {
	return;
}
$crawl_url       = \SmartCrawl\Admin\Settings\Sitemap::crawl_url();
$sitemap_enabled = Settings::get_setting( 'sitemap' );
if ( ! $sitemap_enabled ) {
	return;
}

$function_name = function_exists( '\wp_date' ) ? 'wp_date' : 'date_i18n';

$end = $service->get_last_run_timestamp();
$end = ! empty( $end ) && is_numeric( $end )
	? call_user_func( $function_name, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $end )
	: __( 'Never', 'wds' );
?>

<span>
	<?php
	printf(
		/* translators: %s: Last crawl date */
		esc_html__( 'Last crawl: %s', 'wds' ),
		esc_html( $end )
	);
	?>
</span>

<a
	href="<?php echo esc_attr( $crawl_url ); ?>" class="sui-button sui-button-blue wds-new-crawl-button"
	style="<?php echo $crawl_report->is_in_progress() ? 'display:none;' : ''; ?>"
>
	<?php esc_html_e( 'New crawl', 'wds' ); ?>
</a>