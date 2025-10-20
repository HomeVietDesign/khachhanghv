<?php
$shortcodes_extension = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-estimate-customer',
	$shortcodes_extension->locate_URI( '/shortcodes/estimate-customer/static/css/styles.css' ),
	[],
	'1.3'
);

wp_enqueue_script(
	'fw-shortcode-estimate-customer',
	$shortcodes_extension->locate_URI('/shortcodes/estimate-customer/static/js/scripts.js'),
	array('jquery'),
	'1.3',
	true
);
