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
				'label' => 'Nhóm được chọn',
				'fw-storage' => array(
					'type' => 'wp-option',
					'wp-option' => 'contractor_rating_top',
				),
			],
			'contractor_rating_customer' => [
				'type'  => 'multi-select',
				'population' => 'taxonomy',
				'source' => 'contractor_rating',
				'limit' => 1,
				'label' => 'Nhóm gửi khách hàng',
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
				'label' => 'Nhóm xây dựng',
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
				'label' => 'Nhóm đồ gỗ',
				'fw-storage' => array(
					'type' => 'wp-option',
					'wp-option' => 'contractor_furniture',
				),
			],
		),
	),
);
