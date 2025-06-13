
<?php
if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'user_manager' => array(
    	'type' => 'tab',
		'title' => __('Cài đặt nhà quản lý'),
		'options' => array(
			'user_passwords'  => [
				'type'         => 'multi-picker',
				'label'        => false,
				'desc'         => false,
				'picker'       => array(
					'gadget' => array(
						'label'   => 'Tài khoản',
						'type'    => 'select',
						'choices' => array(
							'phone'  => __( 'Phone', 'unyson' ),
							'laptop' => __( 'Laptop', 'unyson' )
						),
						
					)
				),
				'choices'      => array(
					// 'phone'  => array(
					// 	'passwords'  => array(
					// 		'label' => 'Khách hàng được quản lý',
					// 		'desc'  => '',
					// 		'type'  => 'multi-select',
					// 		'population' => 'taxonomy',
					// 		'source' => 'passwords',
					// 		'limit' => 1000,
					// 	),
					// ),
					// 'laptop' => array(
					// 	'passwords'  => array(
					// 		'label' => 'Khách hàng được quản lý',
					// 		'desc'  => '',
					// 		'type'  => 'multi-select',
					// 		'population' => 'taxonomy',
					// 		'source' => 'passwords',
					// 		'limit' => 1000,
					// 	),
					// ),
				),
				'show_borders' => false,
			]
		),
	),
);

$wp_users = get_users(['role__in'=>['viewer']]);
$picker_choices = [];
$choices = [];
$_passwords = get_terms(['taxonomy'=>'passwords', 'hide_empty'=>false]);
//debug_log($_passwords);
if(!empty($wp_users) && !empty($_passwords)) {
	$passwords = [];
	foreach ($_passwords as $key => $value) {
		$passwords[$value->term_id] = $value->description.' ( '.$value->name.' )';
	}

	$option = [
		'label' => 'Khách hàng được quản lý',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'array',
		'source' => '',
		'choices' => $passwords,
		'limit' => 1000,
	];
	foreach ($wp_users as $user) {
		$picker_choices[$user->user_login] = $user->user_nicename;
		$choices[$user->user_login]['passwords'] = $option;
	}
	$options['user_manager']['options']['user_passwords']['picker']['gadget']['choices'] = $picker_choices;
	$options['user_manager']['options']['user_passwords']['choices'] = $choices;
}
