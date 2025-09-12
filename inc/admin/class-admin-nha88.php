<?php
namespace HomeViet;

class Admin_Nha88 {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			add_action( 'manage_nha88_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			add_filter( 'manage_nha88_posts_columns', [ $this, 'add_custom_columns_header' ] );

		}

	}

	public function custom_columns_value($column, $post_id) {
		switch ($column) {
			case 'gurl':
				echo esc_html(fw_get_db_post_option($post_id, 'nha88_zalo'));
				break;
		}
	}

	public function add_custom_columns_header($columns) {
		
		$columns['gurl'] = 'Link nhóm zalo';
		
		return $columns;
	}

	public function disable_months_dropdown($disabled, $post_type) {
		if($post_type=='nha88') {
			$disabled = true;
		}
		return $disabled;
	}


	public function switch_boxes() {

		remove_meta_box(
			'slugdiv',
			'nha88',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'nha88',
			'side'
		);

		remove_meta_box(
			'nha88_typediv',
			'nha88',
			'side'
		);

	}



	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Nha88::instance();