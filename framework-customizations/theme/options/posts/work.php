<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'work' => array(
		'context' => 'advanced',
		'title'   => 'Thông mặc định',
		'type'    => 'box',
        'options' => array(
			'work_note' => array(
				'label' => 'Ghi chú',
				'type' => 'text'
			),
			'work_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'work_url' => array(
				'label' => 'Link gốc',
				'type' => 'text'
			),
		),
	),
	'work_content' => array(
		'context' => 'advanced',
		'title'   => 'Đề bài',
		'type'    => 'box',
        'options' => array(
        	'work_content' => array(
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