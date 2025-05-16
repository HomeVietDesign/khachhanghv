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
				'type' => 'numeric'
			),
			'estimate_unit' => array(
				'label' => 'Đơn vị',
				'type' => 'text'
			),
			'estimate_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'estimate_url' => array(
				'label' => 'Link dự toán',
				'type' => 'text'
			),
		),
	),
);