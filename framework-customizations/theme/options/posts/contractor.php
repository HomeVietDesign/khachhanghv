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
		'title'   => 'Dự toán mặc định',
		'type'    => 'box',
        'options' => array(
        	'estimate_value' => array(
				'label' => 'Giá trị',
				'type' => 'text',
				'value' => ''
			),
			'estimate_unit' => array(
				'label' => 'Ghi chú',
				'type' => 'text'
			),
			'estimate_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'estimate_attachment' => array(
				'label' => 'File pdf dự toán',
				'type' => 'upload',
				'images_only' => false,
				'files_ext' => array( 'pdf' ),
			),
		),
	),
	
	'info'=>array(
		'context' => 'advanced',
		'title'   => 'Cài đặt nâng cao',
		'type'    => 'box',
        'options' => array(
			'_phone_number' => array(
				'label' => 'Số điện thoại',
				'type' => 'text',
				'fw-storage' => array(
					'type' => 'post-meta',
					'post-meta' => '_phone_number',
				),
			),
			'_external_url' => array(
				'label' => 'Link nút Xem',
				'type' => 'text',
				'fw-storage' => array(
					'type' => 'post-meta',
					'post-meta' => '_external_url',
				),
			),
			'_best' => array(
				'label' => 'Là nhà thầu tốt?',
				'desc'  => '',
				'value'  => 'false',
				'type'  => 'switch',
				'left-choice' => array(
			        'value' => 'true',
			        'label' => 'Đúng',
			    ),
			    'right-choice' => array(
			        'value' => 'false',
			        'label' => 'Không',
			    ),
			    'fw-storage' => array(
					'type' => 'post-meta',
					'post-meta' => '_best',
				),
			),
		),
    ),

);
