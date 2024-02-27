<?php
$tweet_url = empty( $tweet_url ) ? '' : $tweet_url;
$large     = empty( $large ) ? false : $large;

if ( ! $tweet_url ) {
	return;
}
?>
<div class="wds-twitter-embed <?php echo $large ? 'wds-twitter-embed-large' : ''; ?>">
	<?php
	global $wp_embed;
	/**
	 * Embed.
	 *
	 * @var WP_Embed $wp_embed
	 */
	echo $wp_embed->autoembed( $tweet_url ); // phpcs:ignore -- The embed won't work if escaped
	?>
</div>