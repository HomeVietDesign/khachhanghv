<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-gzalo',
	$shortcodes_extension->locate_URI( '/shortcodes/gzalo/static/css/styles.css' ),
	[],
	'1.0'
);

wp_enqueue_script(
	'fw-shortcode-gzalo',
	$shortcodes_extension->locate_URI('/shortcodes/gzalo/static/js/scripts.js'),
	array('jquery'),
	'1.0',
	true
);
