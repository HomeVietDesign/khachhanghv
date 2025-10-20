<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'elighting' => array(
		'context' => 'advanced',
		'title'   => 'Thông mặc định',
		'type'    => 'box',
        'options' => array(
        	'elighting_value' => array(
				'label' => 'Giá trị',
				'type' => 'text'
			),
			'elighting_unit' => array(
				'label' => 'Ghi chú',
				'type' => 'text'
			),
			'elighting_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'elighting_url' => array(
				'label' => 'Link dự toán gốc',
				'type' => 'text'
			),
			'elighting_file' => array(
				'label' => 'File pdf dự toán',
				'type' => 'upload',
				'images_only' => false,
				'files_ext' => array( 'pdf' ),
			),
		),
	),
	'elighting_content' => array(
		'context' => 'advanced',
		'title'   => 'Đề bài yêu cầu',
		'type'    => 'box',
        'options' => array(
        	'elighting_content' => array(
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