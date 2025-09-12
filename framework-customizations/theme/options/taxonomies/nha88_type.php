<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	
	'nha88_hide' => array(
		'label' => 'Hồ sơ đã ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'nha88',
		'limit' => 1000
	),

);
