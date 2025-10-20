<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-estimates',
	$shortcodes_extension->locate_URI( '/shortcodes/estimates/static/css/styles.css' ),
	[],
	'1.4'
);

wp_enqueue_script(
	'fw-shortcode-estimates',
	$shortcodes_extension->locate_URI('/shortcodes/estimates/static/js/scripts.js'),
	array('jquery'),
	'1.3',
	true
);
