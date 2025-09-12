<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'media' => array(
		'context' => 'advanced',
		'title'   => 'Thông tin',
		'type'    => 'box',
        'options' => array(
        	// 'phone_number' => array(
			// 	'label' => 'Số điện thoại',
			// 	'type' => 'text'
			// ),
			// 'zalo' => array(
			// 	'label' => 'Số zalo',
			// 	'type' => 'text'
			// ),
			
			'web' => array(
				'label' => 'URL web',
				'type' => 'text'
			),
			'fb' => array(
				'label' => 'URL facebook',
				'type' => 'text'
			),
			'last_date' => array(
				'type'  => 'date-picker',
				'label' => 'Ngày save cũ nhất',
				'monday-first' => true,
				'min-date' => date('d-m-Y', mktime(0, 0, 0, 1, 1, 2022)),
				//'max-date' => null,
			),
			'end_date' => array(
				'type'  => 'date-picker',
				'label' => 'Ngày save mới nhất',
				'monday-first' => true,
				'min-date' => date('d-m-Y', mktime(0, 0, 0, 1, 1, 2022)),
				//'max-date' => null,
			),
		),
	),
);