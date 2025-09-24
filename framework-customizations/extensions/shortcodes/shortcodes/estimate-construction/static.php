<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-estimate-construction',
	$shortcodes_extension->locate_URI( '/shortcodes/estimate-construction/static/css/styles.css' ),
	[],
	'1.1'
);

wp_enqueue_script(
	'fw-shortcode-estimate-construction',
	$shortcodes_extension->locate_URI('/shortcodes/estimate-construction/static/js/scripts.js'),
	array('jquery'),
	'1.1',
	true
);
