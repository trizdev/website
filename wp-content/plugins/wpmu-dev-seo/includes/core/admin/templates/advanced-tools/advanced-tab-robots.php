<?php

namespace SmartCrawl;

$active_tab      = empty( $active_tab ) ? false : $active_tab;
$option_name     = empty( $option_name ) ? '' : $option_name;
$optons          = empty( $options ) ? array() : $options;
$already_exists  = ! empty( $already_exists );
$rootdir_install = ! empty( $rootdir_install );

$section_template = \SmartCrawl\Modules\Advanced\Robots\Controller::get()->should_run()
	? 'advanced-tools/advanced-robots-settings'
	: 'advanced-tools/advanced-robots-disabled';
$section_args     = array(
	'already_exists'  => $already_exists,
	'rootdir_install' => $rootdir_install,
	'option_name'     => $option_name,
	'options'         => $options,
);


$tab_args = array(
	'tab_id'       => 'tab_robots_editor',
	'tab_name'     => esc_html__( 'Robots.txt Editor', 'wds' ),
	'is_active'    => 'tab_robots_editor' === $active_tab,
	'button_text'  => false,
	'tab_sections' => array(
		array(
			'section_template' => $section_template,
			'section_args'     => $section_args,
		),
	),
);
$this->render_view( 'vertical-tab', $tab_args );