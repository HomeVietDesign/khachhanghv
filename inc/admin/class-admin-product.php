<?php
namespace HomeViet;

class Admin_Product {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );

			add_action( 'created_product', [$this, 'auto_slug'] );

			add_filter('manage_edit-product_columns', [$this, 'custom_column_header']);
			//add_filter('manage_product_custom_column', [$this, 'custom_column_value'], 10, 3);
			
		}

	}

	public function custom_column_value($value, $column_name, $term_id) {
		switch ($column_name) {
			case 'external_url':

				$value .= '<input type="text" value="'.esc_attr(get_term_meta($term_id,'external_url', true)).'" class="external_url" data-id="'.absint($term_id).'" data-nonce="'.esc_attr(wp_create_nonce('change_external_url_'.$term_id)).'">';
				break;
			case 'province':
				$province = get_term_meta($term_id, 'province', true);
				if(!empty($province)) {
					$value .= esc_html(get_term_field( 'name', absint($province[0]), 'province' ));
				}
				break;
			default:
				// code...
				break;
		}
		return $value;
	}

	public function custom_column_header($columns) {
		if(isset($columns['name'])) {
			$columns['name'] = 'Tên gọi';
		}
		if(isset($columns['description'])) {
			$columns['description'] = 'Số điện thoại';
		}
		if(isset($columns['slug'])) {
			unset($columns['slug']);
		}
		if(isset($columns['posts'])) {
			unset($columns['posts']);
		}
		
		return $columns;
	}

	public function auto_slug($term_id) {
		global $wpdb;
		$wpdb->update( $wpdb->terms, ['slug' => current_time( 'U' )], ['term_id' => $term_id] );
		wp_cache_delete( $term_id, 'terms' );
	}

	public function enqueue_scripts($hook) {
		global $taxonomy;
	
		if(($hook=='edit-tags.php' || $hook=='term.php') && $taxonomy=='product') {
			wp_enqueue_style( 'manage-product', THEME_URI.'/assets/css/manage-product.css', [], '' );
			wp_enqueue_script('manage-product', THEME_URI.'/assets/js/manage-product.js', array('jquery'), '');
		}
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Product::instance();