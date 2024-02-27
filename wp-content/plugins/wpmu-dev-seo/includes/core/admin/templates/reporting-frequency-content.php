<?php
/**
 * Frequency content template.
 *
 * @var string $component Component.
 * @var string $frequency Frequency.
 * @var string $dow_value Value.
 * @var string $tod_value Value.
 *
 * @package SmartCrawl
 */

if ( empty( $component ) ) {
	return;
}

if ( 'daily' === $frequency ) : ?>
	<div class="sui-form-field">
		<?php
		$this->render_view(
			'reporting-tod-select',
			array(
				'component' => $component,
				'tod_value' => $tod_value,
			)
		);
		?>
	</div>
<?php elseif ( 'weekly' === $frequency || 'monthly' === $frequency ) : ?>
	<div class="sui-form-field">
		<?php
		$this->render_view(
			'reporting-dow-select',
			array(
				'component' => $component,
				'dow_value' => $dow_value,
				'monthly'   => 'monthly' === $frequency,
			)
		);
		?>
	</div>
<?php
endif;