<?php if (!defined('FW')) {
	die('Forbidden');
}

$options = array(
	'contractor' => array(
		'label' => 'Nhà thầu',
		'desc' => '',
		'type' => 'multi-select',
		'population' => 'posts',
		'source' => 'contractor',
		'limit' => 1,
	),
	'category' => array(
		'label' => 'Hạng mục',
		'desc' => '',
		'value' => '',
		'type' => 'text',
	),
	'value' => array(
		'label' => 'Tổng giá trị dự toán',
		'desc' => '',
		'value' => '',
		'type' => 'numeric',
		'width' => 'full',
	),
	'pdf' => array(
		'label' => 'File báo giá',
		'desc' => '',
		'value' => '',
		'type' => 'upload',
		'files_ext' => array( 'pdf' ),
	),
);
