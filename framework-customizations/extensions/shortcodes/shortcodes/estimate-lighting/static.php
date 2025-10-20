<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-estimate-lighting',
	$shortcodes_extension->locate_URI( '/shortcodes/estimate-lighting/static/css/styles.css' ),
	[],
	'1.2'
);

wp_enqueue_script(
	'fw-shortcode-estimate-lighting',
	$shortcodes_extension->locate_URI('/shortcodes/estimate-lighting/static/js/scripts.js'),
	array('jquery'),
	'1.3',
	true
);
