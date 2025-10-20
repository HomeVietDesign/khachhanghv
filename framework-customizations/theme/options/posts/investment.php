<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Frameinvestment options
 *
 * @var array $options Fill this array with options to generate frameinvestment settings form in backend
 */

$options = array(
	'investment' => array(
		'context' => 'advanced',
		'title'   => 'Thông mặc định',
		'type'    => 'box',
        'options' => array(
			'investment_note' => array(
				'label' => 'Ghi chú',
				'type' => 'text'
			),
			'investment_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'investment_url' => array(
				'label' => 'Link gốc',
				'type' => 'text'
			),
		),
	),
	'investment_content' => array(
		'context' => 'advanced',
		'title'   => 'Đề bài',
		'type'    => 'box',
        'options' => array(
        	'investment_content' => array(
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