<?php

namespace SmartCrawl;

$active_tab  = empty( $active_tab ) ? false : $active_tab;
$option_name = empty( $option_name ) ? false : $option_name;
$options     = empty( $options ) ? array() : $options;

if ( ! empty( $options['active'] ) ) {

	$this->render_view(
		'vertical-tab',
		array(
			'tab_id'       => 'tab_moz_main',
			'tab_name'     => __( 'Moz', 'wds' ),
			'is_active'    => 'tab_moz' === $active_tab,
			'button_text'  => false,
			'tab_sections' => array(
				array(
					'section_template' => 'advanced-tools/advanced-section-moz-details',
					'section_args'     => array(
						'option_name' => $option_name,
						'options'     => $options,
					),
				),
			),
		)
	);

	$this->render_view(
		'vertical-tab',
		array(
			'tab_id'       => 'tab_moz_settings',
			'tab_name'     => esc_html__( 'Settings', 'wds' ),
			'is_active'    => 'tab_moz' === $active_tab,
			'button_text'  => false,
			'tab_sections' => array(
				array(
					'section_template' => 'advanced-tools/advanced-section-moz-settings',
					'section_args'     => array(
						'option_name' => $option_name,
					),
				),
			),
		)
	);

	return;
}

$this->render_view(
	'vertical-tab',
	array(
		'tab_id'       => 'tab_moz_disabled',
		'tab_name'     => __( 'Moz', 'wds' ),
		'is_active'    => 'tab_moz' === $active_tab,
		'button_text'  => false,
		'tab_sections' => array(
			array(
				'section_template' => 'advanced-tools/advanced-section-moz-disabled',
				'section_args'     => array(
					'option_name' => $option_name,
				),
			),
		),
	)
);