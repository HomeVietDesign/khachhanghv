<?php
namespace HomeViet;

class Admin_Gzalo {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			add_action( 'manage_gzalo_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			add_filter( 'manage_gzalo_posts_columns', [ $this, 'add_custom_columns_header' ] );

		}

	}

	public function custom_columns_value($column, $post_id) {
		switch ($column) {
			case 'gurl':
				echo esc_html(fw_get_db_post_option($post_id, 'gzalo_zalo'));
				break;
		}
	}

	public function add_custom_columns_header($columns) {
		
		$columns['gurl'] = 'Link nhóm zalo';
		
		return $columns;
	}

	public function disable_months_dropdown($disabled, $post_type) {
		if($post_type=='gzalo') {
			$disabled = true;
		}
		return $disabled;
	}


	public function switch_boxes() {

		remove_meta_box(
			'slugdiv',
			'gzalo',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'gzalo',
			'side'
		);

		remove_meta_box(
			'passwordsdiv',
			'gzalo',
			'side'
		);

	}



	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Gzalo::instance();