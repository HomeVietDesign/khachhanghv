<?php
namespace HomeViet;

class Admin_Efurniture_Cat {
	
	private static $instance = null;

	protected function __construct() {
		if(is_admin()) {
			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
			add_filter( 'manage_edit-efurniture_cat_columns', [$this, 'manage_edit_column_header'] );

			add_action( 'created_efurniture_cat', [$this, 'auto_slug'] );
		}
	}

	public function auto_slug($term_id) {
		global $wpdb;
		$wpdb->update( $wpdb->terms, ['slug' => current_time( 'U' )], ['term_id' => $term_id] );
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

		if(($hook=='edit-tags.php' || $hook=='term.php') && $taxonomy=='efurniture_cat') {
			wp_enqueue_style( 'manage-efurniture_cat', THEME_URI.'/assets/css/manage-efurniture_cat.css', [], '' );
			//wp_enqueue_script('manage-efurniture_cat', THEME_URI.'/assets/js/manage-efurniture_cat.js', array('jquery'), '');
		}
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}
Admin_Efurniture_Cat::instance();