<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'document' => array(
		'context' => 'advanced',
		'title'   => 'Dữ liệu mặc định',
		'type'    => 'box',
        'options' => array(
        	'document_value' => array(
				'label' => 'Giá trị',
				'type' => 'text'
			),
			'document_unit' => array(
				'label' => 'Đơn vị',
				'type' => 'text'
			),
			'document_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'document_attachment' => array(
				'label' => 'File pdf dữ liệu',
				'type' => 'upload',
				'images_only' => false,
				'files_ext' => array( 'pdf','rar','zip' ),
			),
		),
	),
	'document_content' => array(
		'context' => 'advanced',
		'title'   => 'Nội dung yêu cầu',
		'type'    => 'box',
        'options' => array(
        	'document_content' => array(
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