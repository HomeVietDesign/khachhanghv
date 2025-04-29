<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}


$shortcodes_extension = fw_ext( 'shortcodes' );


wp_enqueue_style(
	'fw-shortcode-estimate',
	$shortcodes_extension->locate_URI( '/shortcodes/estimate/static/css/styles.css' ),
	['dashicons', 'bootstrap'],
	date('YmdHis', filemtime($shortcodes_extension->locate_path('/shortcodes/estimate/static/css/styles.css'))),
);

/*
wp_enqueue_script(
	'fw-shortcode-contractors',
	$shortcodes_extension->locate_URI( '/shortcodes/contractors/static/js/scripts.js' ),
	['jquery', 'bootstrap', 'imagesloaded', 'masonry', 'select2'],
	date('YmdHis', filemtime($shortcodes_extension->locate_path('/shortcodes/contractors/static/js/scripts.js'))),
	true
);

*/