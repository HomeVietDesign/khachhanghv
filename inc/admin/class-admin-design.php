<?php
namespace HomeViet;

class Admin_Design {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			// add_action( 'manage_design_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			// add_filter( 'manage_design_posts_columns', [ $this, 'add_custom_columns_header' ] );

		}

	}

	public function custom_columns_value($column, $post_id) {
		switch ($column) {
			case 'phone_number':
				$post = get_post($post_id);
				echo esc_html($post->post_excerpt);
				break;
		}
	}

	public function add_custom_columns_header($columns) {
		
		$columns['phone_number'] = 'Số điện thoại';
		
		return $columns;
	}

	public function disable_months_dropdown($disabled, $post_type) {
		if($post_type=='design') {
			$disabled = true;
		}
		return $disabled;
	}

	public function switch_boxes() {

		remove_meta_box(
			'slugdiv',
			'design',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'design',
			'side'
		);

	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Design::instance();