<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-efurniture',
	$shortcodes_extension->locate_URI( '/shortcodes/efurniture/static/css/styles.css' ),
	[],
	'1.0'
);

wp_enqueue_script(
	'fw-shortcode-efurniture',
	$shortcodes_extension->locate_URI('/shortcodes/efurniture/static/js/scripts.js'),
	array('jquery'),
	'1.0',
	true
);
