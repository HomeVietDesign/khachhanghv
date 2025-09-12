<?php
namespace HomeViet;

class Admin_Nha88_Type {
	
	private static $instance = null;

	protected function __construct() {
		if(is_admin()) {
			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
			add_filter( 'manage_edit-nha88_type_columns', [$this, 'manage_edit_column_header'] );

			add_action( 'created_nha88_type', [$this, 'auto_slug'] );
		}
	}

	public function auto_slug($term_id) {
		global $wpdb;
		$wpdb->update( $wpdb->terms, ['slug' => 't'.current_time( 'U' )], ['term_id' => $term_id] );
		wp_cache_delete( $term_id, 'terms' );
	}

	public function manage_edit_column_header($columns) {
		if(isset($columns['description'])) {
			unset($columns['description']);
		}
		if(isset($columns['slug'])) {
			unset($columns['slug']);
		}
		if(isset($columns['posts'])) {
			$columns['posts'] = 'Đếm';
		}
		return $columns;
	}

	public function enqueue_scripts($hook) {
		global $taxonomy;

		if(($hook=='edit-tags.php' || $hook=='term.php') && $taxonomy=='nha88_type') {
			wp_enqueue_style( 'manage-nha88_type', THEME_URI.'/assets/css/manage-nha88_type.css', [], '' );
			//wp_enqueue_script('manage-nha88_type', THEME_URI.'/assets/js/manage-nha88_type.js', array('jquery'), '');
		}
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}
Admin_Nha88_Type::instance();