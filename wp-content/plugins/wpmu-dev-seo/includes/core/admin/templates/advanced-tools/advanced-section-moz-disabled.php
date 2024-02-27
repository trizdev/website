<?php
/**
 * Moz deactivation template.
 *
 * @package SmartCrawl
 */

$option_name = empty( $option_name ) ? false : $option_name;

$this->render_view(
	'disabled-component-inner',
	array(
		'content'     => esc_html__(
			'Moz provides reports that tell you how your site stacks up against the competition with all of
the important SEO measurement tools - ranking, links, and much more.',
			'wds'
		),
		'button_text' => esc_html__( 'Activate', 'wds' ),
		'button_name' => $option_name . '[active]',
	)
);