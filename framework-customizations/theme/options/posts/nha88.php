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
		'title'   => 'Dữ liệu',
		'type'    => 'box',
        'options' => array(
        	'nha88_value' => array(
				'label' => 'Giá trị',
				'type' => 'text'
			),
			'nha88_unit' => array(
				'label' => 'Đơn vị',
				'type' => 'text'
			),
			'nha88_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'nha88_url' => array(
				'label' => 'Link chi tiết',
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
	'nha88_proccessing' => array(
		'context' => 'advanced',
		'title'   => 'Các ngày ghi chú',
		'type'    => 'box',
		'options' => array(
			'nha88_dates' => [
				'type' => 'addable-popup',
				'value' => [],
				'label' => '',
				'desc'  => '',
				'template' => '{{=title}}',
				'popup-title' => 'Thêm ngày',
				'size' => 'small', // small, medium, large
				'limit' => 4, // limit the number of popup`s that can be added
				'add-button-text' => 'Thêm',
				'sortable' => true,
				'popup-options' => array(
					'title' => array(
						'label' => 'Tiêu đề',
						'type' => 'text',
						'value' => '',
					),
					'date' => array(
						'type'  => 'date-picker',
						'label' => 'Ngày tháng năm',
						'value' => '',
						'monday-first' => true,
						'min-date' => date('d-m-Y', mktime(0, 0, 0, 1, 1, 2022)),
						//'max-date' => null,
					),
				),
			]
			
		),
	),
);