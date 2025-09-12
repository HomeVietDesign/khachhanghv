<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-media',
	$shortcodes_extension->locate_URI( '/shortcodes/media/static/css/styles.css' ),
	[],
	'1.0'
);

wp_enqueue_script(
	'fw-shortcode-media',
	$shortcodes_extension->locate_URI('/shortcodes/media/static/js/scripts.js'),
	array('jquery'),
	'1.0',
	true
);
