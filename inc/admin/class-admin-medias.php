<?php
namespace HomeViet;

class Admin_Medias {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			add_action( 'manage_media_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			add_filter( 'manage_media_posts_columns', [ $this, 'add_custom_columns_header' ] );

		}

	}

	public function custom_columns_value($column, $post_id) {
		switch ($column) {
			case 'gurl':
				echo esc_html(fw_get_db_post_option($post_id, 'media_zalo'));
				break;
			case 'last_date':
				echo esc_html(fw_get_db_post_option($post_id, 'last_date'));
				break;
			case 'end_date':
				echo esc_html(fw_get_db_post_option($post_id, 'end_date'));
				break;
		}
	}

	public function add_custom_columns_header($columns) {
		
		//$columns['gurl'] = 'Link nhóm zalo';
		$columns['last_date'] = 'Ngày save cũ nhất';
		$columns['end_date'] = 'Ngày save mới nhất';
		
		return $columns;
	}

	public function disable_months_dropdown($disabled, $post_type) {
		if($post_type=='media') {
			$disabled = true;
		}
		return $disabled;
	}


	public function switch_boxes() {

		remove_meta_box(
			'slugdiv',
			'media',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'media',
			'side'
		);

		remove_meta_box(
			'passwordsdiv',
			'media',
			'side'
		);

	}



	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Medias::instance();