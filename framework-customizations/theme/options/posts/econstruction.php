<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'econstruction' => array(
		'context' => 'advanced',
		'title'   => 'Thông mặc định',
		'type'    => 'box',
        'options' => array(
        	'econstruction_value' => array(
				'label' => 'Giá trị',
				'type' => 'text'
			),
			'econstruction_unit' => array(
				'label' => 'Ghi chú',
				'type' => 'text'
			),
			'econstruction_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'econstruction_url' => array(
				'label' => 'Link dự toán gốc',
				'type' => 'text'
			),
			'econstruction_file' => array(
				'label' => 'File pdf dự toán',
				'type' => 'upload',
				'images_only' => false,
				'files_ext' => array( 'pdf' ),
			),
		),
	),
	'econstruction_content' => array(
		'context' => 'advanced',
		'title'   => 'Đề bài yêu cầu',
		'type'    => 'box',
        'options' => array(
        	'econstruction_content' => array(
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