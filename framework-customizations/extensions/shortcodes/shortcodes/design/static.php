<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-design',
	$shortcodes_extension->locate_URI( '/shortcodes/design/static/css/styles.css' ),
	[],
	'1.0'
);

wp_enqueue_script(
	'fw-shortcode-design',
	$shortcodes_extension->locate_URI('/shortcodes/design/static/js/scripts.js'),
	array('jquery'),
	'1.0',
	true
);
