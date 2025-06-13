
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
if(!empty($wp_users)) {
	$option = [
		'label' => 'Khách hàng được quản lý',
		'desc'  => '',
		'type'  => 'multi-select',
		'population' => 'taxonomy',
		'source' => 'passwords',
		'limit' => 1000,
	];
	foreach ($wp_users as $user) {
		$picker_choices[$user->user_login] = $user->user_nicename;
		$choices[$user->user_login]['passwords'] = $option;
	}
	$options['user_manager']['options']['user_passwords']['picker']['gadget']['choices'] = $picker_choices;
	$options['user_manager']['options']['user_passwords']['choices'] = $choices;
}
