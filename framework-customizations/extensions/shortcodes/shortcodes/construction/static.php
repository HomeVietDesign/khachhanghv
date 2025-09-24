<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-construction',
	$shortcodes_extension->locate_URI( '/shortcodes/construction/static/css/styles.css' ),
	[],
	'1.0'
);

wp_enqueue_script(
	'fw-shortcode-construction',
	$shortcodes_extension->locate_URI('/shortcodes/construction/static/js/scripts.js'),
	array('jquery'),
	'1.0',
	true
);
