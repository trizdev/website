<?php
namespace SmartCrawl;

$is_member       = empty( $_view['is_member'] ) ? false : true;
$already_exists  = empty( $already_exists ) ? false : true;
$rootdir_install = empty( $rootdir_install ) ? false : true;

$is_active = ! empty( \SmartCrawl\Modules\Advanced\Controller::get()->should_run() );
?>

<?php $this->render_view( 'before-page-container' ); ?>
<div id="container"
	class="<?php \smartcrawl_wrap_class( 'wds-advanced' ); ?>">

	<?php
	$this->render_view(
		'page-header',
		array(
			'title'                 => esc_html__( 'Advanced Tools', 'wds' ),
			'documentation_chapter' => 'advanced-tools',
			'utm_campaign'          => 'smartcrawl_advanced-tools_docs',
		)
	);
	?>

	<?php
	$this->render_view(
		'floating-notices',
		array(
			'keys' => array(
				'wds-redirect-notice',
			),
		)
	);
	?>

	<?php if ( ! $is_active ) : ?>

		<form action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>"
				method="post" class="wds-form">
			<?php $this->settings_fields( $_view['option_name'] ); ?>

			<div class="sui-box">
				<div class="sui-box-header">
					<h2 class="sui-box-title"><?php esc_html_e( 'Get Started', 'wds' ); ?></h2>
				</div>
				<div class="sui-box-body">
					<?php
					$this->render_view(
						'disabled-component-inner',
						array(
							'content'     => esc_html__( 'Enhance website SEO with advanced tools. Access SmartCrawl\'s impressive features including automatic linking, URL redirection, robots.txt editor, Moz reporting, and Breadcrumbs.', 'wds' ),
							'button_text' => esc_html__( 'Activate', 'wds' ),
							'button_name' => $_view['option_name'] . '[active]',
						)
					);
					?>
				</div>
			</div>
		</form>

	<?php else : ?>
		<div class="wds-vertical-tabs-container sui-row-with-sidenav">
			<?php
			$this->render_view(
				'advanced-tools/advanced-side-nav',
				array(
					'active_tab' => $active_tab,
				)
			);
			?>

			<form action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>"
					method="post" class="wds-form">
				<?php $this->settings_fields( $_view['option_name'] ); ?>

				<div id="wds-autolinks"></div>
			</form>

			<form action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>"
					method="post" class="wds-form">
				<?php $this->settings_fields( $_view['option_name'] ); ?>

				<?php
				if ( \smartcrawl_woocommerce_active() ) {
					$this->render_view(
						'advanced-tools/advanced-section-woo-settings',
						array(
							'is_active' => 'tab_woo' === $active_tab,
						)
					);
				}
				?>
			</form>

			<form action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>"
					method="post" class="wds-form">
				<?php $this->settings_fields( $_view['option_name'] ); ?>

				<div id="tab_url_redirection"></div>
			</form>

			<form action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>"
					method="post" class="wds-moz-form wds-form">
				<?php $this->settings_fields( $_view['option_name'] ); ?>

				<div id="tab_moz">
					<?php
					$this->render_view(
						'advanced-tools/advanced-section-moz',
						array(
							'active_tab'  => $active_tab,
							'option_name' => $_view['option_name'] . '[seomoz]',
							'options'     => \SmartCrawl\Modules\Advanced\Seomoz\Controller::get()->get_options(),
						)
					);
					?>
				</div>
			</form>

			<form action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>"
					method="post" class="wds-form">
				<?php $this->settings_fields( $_view['option_name'] ); ?>

				<?php
				$this->render_view(
					'advanced-tools/advanced-tab-robots',
					array(
						'active_tab'      => $active_tab,
						'option_name'     => $_view['option_name'] . '[robots]',
						'already_exists'  => $already_exists,
						'rootdir_install' => $rootdir_install,
						'options'         => \SmartCrawl\Modules\Advanced\Robots\Controller::get()->get_options(),
					)
				);
				?>
			</form>

			<form action="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>"
					method="post" class="wds-form">
				<?php $this->settings_fields( $_view['option_name'] ); ?>

				<div id="wds-breadcrumbs"></div>
			</form>
		</div>
	<?php endif; ?>

	<?php $this->render_view( 'footer' ); ?>
	<?php $this->render_view( 'upsell-modal' ); ?>

</div><!-- end wds-advanced -->