<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-investment',
	$shortcodes_extension->locate_URI( '/shortcodes/investment/static/css/styles.css' ),
	[],
	'0.1'
);

wp_enqueue_script(
	'fw-shortcode-investment',
	$shortcodes_extension->locate_URI('/shortcodes/investment/static/js/scripts.js'),
	array('jquery'),
	'0.1',
	true
);
