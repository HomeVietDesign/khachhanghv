<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-procedures',
	$shortcodes_extension->locate_URI( '/shortcodes/procedure/static/css/styles.css' ),
	[],
	'1.0'
);

wp_enqueue_script(
	'fw-shortcode-procedure',
	$shortcodes_extension->locate_URI('/shortcodes/procedure/static/js/scripts.js'),
	array('jquery'),
	'1.0',
	true
);
