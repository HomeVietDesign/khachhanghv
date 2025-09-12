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
			'document_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'document_default_url' => array(
				'label' => 'Link dữ liệu gốc',
				'type' => 'text'
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