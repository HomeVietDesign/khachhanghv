<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'design' => array(
		'context' => 'advanced',
		'title'   => 'Thông tin',
		'type'    => 'box',
		'options' => array(
			'design_stage_options' => [
				'type' => 'addable-popup',
				'value' => [],
				'label' => 'Các giai đoạn',
				'desc'  => '',
				'template' => '{{=name}}',
				'popup-title' => 'Thêm hình ảnh',
				'size' => 'small', // small, medium, large
				'limit' => 0, // limit the number of popup`s that can be added
				'add-button-text' => 'Thêm',
				'sortable' => true,
				'popup-options' => array(
					'name' => array(
						'label' => 'Giai đoạn',
						'type' => 'text',
						'value' => '',
					),
					'images' => [
						'type' => 'multi-upload',
						'label' => 'Hình ảnh',
						'images_only' => true,
						'files_ext' => ['png', 'jpg', 'jpeg'],
					],
				),
			],
			'design_estimate_link' => array(
				'label' => 'Link dự toán',
				'type' => 'text'
			),
		),
	),
	'design_content' => array(
		'context' => 'advanced',
		'title'   => 'Đề bài yêu cầu',
		'type'    => 'box',
        'options' => array(
        	'design_content' => array(
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
	'design_proccessing' => array(
		'context' => 'advanced',
		'title'   => 'Các ngày ghi chú',
		'type'    => 'box',
		'options' => array(
			'design_dates' => [
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