<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'nha88' => array(
		'context' => 'advanced',
		'title'   => 'Dữ liệu mặc định',
		'type'    => 'box',
        'options' => array(
        	// 'nha88_value' => array(
			// 	'label' => 'Giá trị',
			// 	'type' => 'text'
			// ),
			// 'nha88_unit' => array(
			// 	'label' => 'Đơn vị',
			// 	'type' => 'text'
			// ),
			'nha88_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'nha88_url' => array(
				'label' => 'Link dữ liệu gốc',
				'type' => 'text'
			),
		),
	),
	'nha88_content' => array(
		'context' => 'advanced',
		'title'   => 'Nội dung đề bài',
		'type'    => 'box',
		'options' => array(
			'nha88_content' => array(
				'label' => '',
				'desc'  => '',
				'type'  => 'wp-editor',
				'value' => '',
				'size' => 'large',
				'editor_height' => '400',
				'media_buttons' => false
			),
		),
	),
);