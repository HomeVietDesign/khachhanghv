<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-nha88',
	$shortcodes_extension->locate_URI( '/shortcodes/nha88/static/css/styles.css' ),
	[],
	'1.0'
);

wp_enqueue_script(
	'fw-shortcode-nha88',
	$shortcodes_extension->locate_URI('/shortcodes/nha88/static/js/scripts.js'),
	array('jquery'),
	'1.1',
	true
);
