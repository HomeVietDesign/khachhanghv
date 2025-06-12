<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'contract' => array(
		'context' => 'advanced',
		'title'   => 'Dữ liệu mặc định',
		'type'    => 'box',
        'options' => array(
        	'contract_value' => array(
				'label' => 'Giá trị',
				'type' => 'text'
			),
			'contract_unit' => array(
				'label' => 'Đơn vị',
				'type' => 'text'
			),
			'contract_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			'contract_attachment' => array(
				'label' => 'File pdf dữ liệu',
				'type' => 'upload',
				'images_only' => false,
				'files_ext' => array( 'pdf','rar','zip' ),
			),
		),
	),
);