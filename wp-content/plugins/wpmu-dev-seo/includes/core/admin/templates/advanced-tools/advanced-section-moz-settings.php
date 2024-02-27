<?php
/**
 * Moz API deactivation settings.
 *
 * @package SmartCrawl
 */

$option_name  = empty( $option_name ) ? false : $option_name;
$option_group = explode( '[', $option_name )[0];
?>

<form action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>"
		method="post" class="wds-form">
	<?php $this->settings_fields( $option_group ); ?>

	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<label class="sui-settings-label" for="wds-default-redirection-type">
				<?php esc_html_e( 'Deactivate', 'wds' ); ?>
			</label>
			<p class="sui-description">
				<?php esc_html_e( 'No longer need MOZ? Deactivate MOZ here. This will also reset your MOZ credentials.', 'wds' ); ?>
			</p>
		</div>

		<div class="sui-box-settings-col-2">
			<button type="submit"
					name="<?php echo esc_attr( $option_name . '[active]' ); ?>"
					value="0"
					class="sui-button-ghost sui-button">
				<span>
					<span class="sui-icon-power-on-off" aria-hidden="true"></span>
					<?php esc_html_e( 'Deactivate', 'wds' ); ?>
				</span>
			</button>
		</div>
	</div>

</form>