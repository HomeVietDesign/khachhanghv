<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-documents',
	$shortcodes_extension->locate_URI( '/shortcodes/documents/static/css/styles.css' ),
	[],
	'1.1'
);

wp_enqueue_script(
	'fw-shortcode-documents',
	$shortcodes_extension->locate_URI('/shortcodes/documents/static/js/scripts.js'),
	array('jquery'),
	'1.0',
	true
);
