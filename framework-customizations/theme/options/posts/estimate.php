<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'estimate' => array(
		'context' => 'advanced',
		'title'   => 'Thông mặc định',
		'type'    => 'box',
        'options' => array(
        	'estimate_value' => array(
				'label' => 'Giá trị',
				'type' => 'text'
			),
			'estimate_unit' => array(
				'label' => 'Ghi chú',
				'type' => 'text'
			),
			'estimate_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'estimate_url' => array(
				'label' => 'Link dự toán gốc',
				'type' => 'text'
			),
			'estimate_file' => array(
				'label' => 'File pdf dự toán',
				'type' => 'upload',
				'images_only' => false,
				'files_ext' => array( 'pdf' ),
			),
		),
	),
	'estimate_content' => array(
		'context' => 'advanced',
		'title'   => 'Đề bài yêu cầu',
		'type'    => 'box',
        'options' => array(
        	'estimate_content' => array(
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