<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-contract',
	$shortcodes_extension->locate_URI( '/shortcodes/contract/static/css/styles.css' ),
	[],
	'1.2'
);

wp_enqueue_script(
	'fw-shortcode-contract',
	$shortcodes_extension->locate_URI('/shortcodes/contract/static/js/scripts.js'),
	array('jquery'),
	'1.2',
	true
);
