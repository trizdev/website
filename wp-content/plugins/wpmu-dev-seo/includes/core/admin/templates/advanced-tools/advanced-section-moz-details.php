<?php

use SmartCrawl\Modules\Advanced\Seomoz\Renderer;
use SmartCrawl\Settings;

$option_name = empty( $option_name ) ? false : $option_name;
$options     = empty( $options ) ? array() : $options;
$access_id   = \smartcrawl_get_array_value( $options, 'access_id' );
$secret_key  = \smartcrawl_get_array_value( $options, 'secret_key' );

$image_url = sprintf( '%s/assets/images/empty-box.svg', SMARTCRAWL_PLUGIN_URL );
$image_url = \SmartCrawl\Controllers\White_Label::get()->get_wpmudev_hero_image( $image_url );
?>

<?php if ( empty( $access_id ) || empty( $secret_key ) ) : ?>
	<div class="wds-disabled-component">
		<?php if ( ! empty( $image_url ) ) : ?>
			<p>
				<img
					src="<?php echo esc_attr( $image_url ); ?>"
					alt="<?php esc_attr_e( 'MOZ Disabled', 'wds' ); ?>"
					class="wds-disabled-image"
				/>
			</p>
		<?php endif; ?>
		<p>
			<?php esc_html_e( 'Moz provides reports that tell you how your site stacks up against the competition with all of', 'wds' ); ?>
			<br/><?php esc_html_e( 'the important SEO measurement tools - ranking, links, and much more.', 'wds' ); ?>
		</p>
	</div>
	<div class="wds-moz-api-credentials">
		<form method="POST" class="wds-form">
			<div class="wds-moz-fields">
				<div class="wds-moz-fields-inner">
					<p class="sui-p-small">
						<?php
						printf(
						/* translators: %s: Url to get the Moz account API credentials */
							esc_html__( 'Connect your Moz account. You can get the API credentials %s.', 'wds' ),
							sprintf( '<a href="https://moz.com/products/mozscape/access" target="_blank">%s</a>', esc_html__( 'here', 'wds' ) )
						);
						?>
					</p>

					<div class="sui-form-field">
						<label
							class="sui-label"
							for="wds-moz-access-id"><?php esc_html_e( 'Access ID', 'wds' ); ?></label>
						<input
							type="text"
							name="<?php echo esc_attr( $option_name . '[access_id]' ); ?>"
							class="sui-form-control"
							placeholder="<?php esc_attr_e( 'Enter your Moz Access ID', 'wds' ); ?>"
							value="<?php echo esc_attr( $access_id ); ?>"/>
						<span class="sui-error-message"><?php esc_html_e( 'Please enter a valid Moz Access ID', 'wds' ); ?></span>
					</div>

					<div class="sui-form-field">
						<label
							class="sui-label"
							for="wds-moz-secret-key"><?php esc_html_e( 'Secret Key', 'wds' ); ?></label>
						<input
							type="text"
							name="<?php echo esc_attr( $option_name . '[secret_key]' ); ?>"
							class="sui-form-control"
							placeholder="<?php esc_attr_e( 'Enter your Moz Secret Key', 'wds' ); ?>"
							value="<?php echo esc_attr( $secret_key ); ?>"/>
						<span class="sui-error-message"><?php esc_html_e( 'Please enter a valid Moz Secret Key', 'wds' ); ?></span>
					</div>
					<button
						type="submit"
						class="sui-button sui-button-blue">
						<span class="sui-loading-text"><?php esc_html_e( 'Connect', 'wds' ); ?></span>
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				</div>
			</div>

			<p class="wds-moz-signup-notice">
				<small>
					<?php
					printf(
					/* translators: %s: Url to signup Moz account */
						esc_html__( "Don't have an account yet? %s.", 'wds' ),
						sprintf( '<a href="https://moz.com/community/join" target="_blank">%s</a>', esc_html__( 'Sign up free', 'wds' ) )
					);
					?>
				</small>
			</p>
		</form>
	</div>
<?php else : ?>
	<p><?php esc_html_e( 'Here’s how your site stacks up against the competition as defined by Moz. You can also see individual stats per post in the post editor under the Moz module.', 'wds' ); ?></p>

	<button
		type="submit" class="sui-button"
		name="<?php echo esc_attr( $option_name . '[reset]' ); ?>">
		<?php esc_html_e( 'Reset API Credentials', 'wds' ); ?>
	</button>

	<?php
	\SmartCrawl\Modules\Advanced\Seomoz\Controller::get()->render_metrics(
		get_bloginfo( 'url' ),
		'seomoz-dashboard-widget'
	);
	?>
<?php endif; ?>