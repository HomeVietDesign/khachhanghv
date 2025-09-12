<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * Framework options
 *
 * @var array $options Fill this array with options to generate framework settings form in backend
 */

$default_contractor_cat = get_option( 'default_term_contractor_cat', -1 );
$contractor_cats = get_terms(['taxonomy' => 'contractor_cat', 'fields' => 'id=>name','parent'=>0,'exclude' => [$default_contractor_cat]]);
if(empty($contractor_cats)) $contractor_cats = [];

$options = array(
	'province' => array(
		'label' => 'Tỉnh thành',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'taxonomy',
		'source' => 'province',
		'limit' => 1,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'province',
		),
	),

	'contractor_hide' => array(
		'label' => 'Dự toán nhà thầu Hiện',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'contractor',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'contractor_hide',
		),
	),

	// 'contractor_hide' => array(
	// 	'label' => 'Dự toán đã ký',
	// 	'desc'  => '',
	// 	'type'  => 'multi-select',
	// 	'population' => 'posts',
	// 	'source' => 'contractor',
	// 	'limit' => 1000
	// ),

	'contractor_customer_hide' => array(
		'label' => 'Dự toán khách hàng Hiện',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'contractor',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'contractor_customer_hide',
		),
	),

	'contractor_construction_hide' => array(
		'label' => 'Dự toán Xây dựng ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'contractor',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'contractor_construction_hide',
		),
	),

	'econstruction_hide' => array(
		'label' => 'Xây dựng ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'econstruction',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'econstruction_hide',
		),
	),

	'contractor_furniture_hide' => array(
		'label' => 'Dự toán Đồ gỗ ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'contractor',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'contractor_furniture_hide',
		),
	),

	'efurniture_hide' => array(
		'label' => 'Đồ gỗ ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'efurniture',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'efurniture_hide',
		),
	),

	// 'estimate_hide' => array(
	// 	'label' => 'Dự toán Xây dựng, Đồ gỗ ẩn',
	// 	'desc'  => '',
	// 	'type'  => 'multi-select',
	// 	'population' => 'posts',
	// 	'source' => 'estimate',
	// 	'limit' => 1000
	// ),
	
	'document_hide' => array(
		'label' => 'Hồ sơ ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'document',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'document_hide',
		),
	),

	'contract_hide' => array(
		'label' => 'Hợp đồng ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'contract',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'contract_hide',
		),
	),

	'gzalo_hide' => array(
		'label' => 'Nhóm zalo ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'gzalo',
		'limit' => 1000,
		'fw-storage' => array(
			'type' => 'term-meta',
			'term-meta' => 'gzalo_hide',
		),
	),
);
