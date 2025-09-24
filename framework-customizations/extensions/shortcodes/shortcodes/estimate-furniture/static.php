<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-estimate-furniture',
	$shortcodes_extension->locate_URI( '/shortcodes/estimate-furniture/static/css/styles.css' ),
	[],
	'1.1'
);

wp_enqueue_script(
	'fw-shortcode-estimate-furniture',
	$shortcodes_extension->locate_URI('/shortcodes/estimate-furniture/static/js/scripts.js'),
	array('jquery'),
	'1.1',
	true
);
