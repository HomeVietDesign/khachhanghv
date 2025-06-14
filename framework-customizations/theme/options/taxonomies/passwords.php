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
		'label' => 'Dự toán nhà thầu ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'contractor',
		'limit' => 1000
	),

	'contractor_customer_hide' => array(
		'label' => 'Dự toán khách hàng ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'contractor',
		'limit' => 1000
	),

	'estimate_hide' => array(
		'label' => 'Dự toán Xây dựng, Đồ gỗ ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'estimate',
		'limit' => 1000
	),

	'econstruction_hide' => array(
		'label' => 'Xây dựng ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'econstruction',
		'limit' => 1000
	),

	'efurniture_hide' => array(
		'label' => 'Đồ gỗ ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'efurniture',
		'limit' => 1000
	),
	
	'document_hide' => array(
		'label' => 'Hồ sơ ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'document',
		'limit' => 1000
	),

	'contract_hide' => array(
		'label' => 'Hợp đồng ẩn',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'posts',
		'source' => 'contract',
		'limit' => 1000
	),

);
