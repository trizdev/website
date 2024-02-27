<?php
$this->render_view(
	'onpage/onpage-preview',
	array(
		'link'        => '{{- link }}',
		'title'       => '{{- title }}',
		'description' => '{{- description }}',
	)
);