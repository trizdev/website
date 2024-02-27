<div class="wds-metabox-preview">
	<label class="sui-label"><?php esc_html_e( 'Google Preview', 'wds' ); ?></label>
	<?php
	if ( apply_filters( 'wds-metabox-visible_parts-preview_area', true ) ) : // phpcs:ignore
		$this->render_view( 'onpage/onpage-preview' );
	endif;
	?>
</div>