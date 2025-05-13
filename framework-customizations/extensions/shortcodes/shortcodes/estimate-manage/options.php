<?php if (!defined('FW')) {
	die('Forbidden');
}

$options = array(
	'estimate_cat' => array(
		'label' => 'Nhóm dự toán',
		'desc' => '',
		'type' => 'multi-select',
		'population' => 'taxonomy',
		'source' => 'estimate_cat',
		'limit' => 1,
	),
	
);
