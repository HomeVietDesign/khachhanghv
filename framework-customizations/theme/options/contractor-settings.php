<?php
if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}


$options = array(
	'contractor' => array(
		'type' => 'tab',
		'title' => 'Nhà thầu',
		'options' => array(
			'contractor_display_provinces' => array(
				'label' => 'Tỉnh hiển thị',
				'desc'  => '',
				'type'  => 'multi-select',
				'population' => 'taxonomy',
				'source' => 'province',
				'limit' => 36
			),
			'contractor_rating_top' => [
				'type'  => 'multi-select',
				'population' => 'taxonomy',
				'source' => 'contractor_rating',
				'limit' => 1,
				'label' => 'Nhóm DỰ TOÁN NHÀ THẦU',
				'fw-storage' => array(
					'type' => 'wp-option',
					'wp-option' => 'contractor_rating_top',
				),
			],
			// 'contractor_rating_procedure' => [
			// 	'type'  => 'multi-select',
			// 	'population' => 'taxonomy',
			// 	'source' => 'contractor_rating',
			// 	'limit' => 1,
			// 	'label' => 'Nhóm quy trình',
			// 	'fw-storage' => array(
			// 		'type' => 'wp-option',
			// 		'wp-option' => 'contractor_rating_procedure',
			// 	),
			// ],
			'contractor_rating_customer' => [
				'type'  => 'multi-select',
				'population' => 'taxonomy',
				'source' => 'contractor_rating',
				'limit' => 1,
				'label' => 'Nhóm KHÁCH CHỌN',
				'fw-storage' => array(
					'type' => 'wp-option',
					'wp-option' => 'contractor_rating_customer',
				),
			],
			'contractor_construction' => [
				'type'  => 'multi-select',
				'population' => 'taxonomy',
				'source' => 'contractor_rating',
				'limit' => 1,
				'label' => 'Nhóm DỰ TOÁN XÂY DỰNG',
				'fw-storage' => array(
					'type' => 'wp-option',
					'wp-option' => 'contractor_construction',
				),
			],
			'contractor_furniture' => [
				'type'  => 'multi-select',
				'population' => 'taxonomy',
				'source' => 'contractor_rating',
				'limit' => 1,
				'label' => 'Nhóm DỰ TOÁN ĐỒ GỖ',
				'fw-storage' => array(
					'type' => 'wp-option',
					'wp-option' => 'contractor_furniture',
				),
			],
			'contractor_lighting' => [
				'type'  => 'multi-select',
				'population' => 'taxonomy',
				'source' => 'contractor_rating',
				'limit' => 1,
				'label' => 'Nhóm ĐÈN',
				'fw-storage' => array(
					'type' => 'wp-option',
					'wp-option' => 'contractor_lighting',
				),
			],
		),
	),
);
