<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$options = array(
	'gzalo' => array(
		'context' => 'advanced',
		'title'   => 'Dữ liệu mặc định',
		'type'    => 'box',
        'options' => array(
        	// 'gzalo_value' => array(
			// 	'label' => 'Giá trị',
			// 	'type' => 'text'
			// ),
			// 'gzalo_unit' => array(
			// 	'label' => 'Đơn vị',
			// 	'type' => 'text'
			// ),
			'gzalo_zalo' => array(
				'label' => 'Link nhóm zalo',
				'type' => 'text'
			),
			// 'gzalo_url' => array(
			// 	'label' => 'Link hợp đồng',
			// 	'type' => 'text'
			// ),
		),
	),
	// 'gzalo_content' => array(
	// 	'context' => 'advanced',
	// 	'title'   => 'Nội dung yêu cầu',
	// 	'type'    => 'box',
    //     'options' => array(
    //     	'gzalo_content' => array(
	// 			'label' => '',
	// 			'desc'  => '',
	// 			'type'  => 'wp-editor',
	// 			'value' => '',
	// 			'size' => 'large',
	// 			'editor_height' => '400',
	// 			'media_buttons' => false
	// 		),
	// 	),
	// ),
);