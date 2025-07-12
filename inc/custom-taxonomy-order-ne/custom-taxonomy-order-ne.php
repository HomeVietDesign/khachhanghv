<?php
namespace HomeViet;

final class Custome_Taxonomy_Order_Ne {
	
	private static $instance = null;

	private function __construct() {
		add_filter( 'customtaxorder_get_taxonomies', [$this, 'customtaxorder_get_taxonomies'] );

		add_action('admin_print_styles', [$this, 'customtaxorder_css'] );

		if (is_admin() && isset($_GET['page']) ) {
			$pos_page = sanitize_text_field( $_GET['page'] );
			if ( $pos_page == 'customtaxorder-passwords' ) {
				add_filter('get_terms', [$this, 'get_terms'], 10);
			}
		}

		
	}

	public function get_terms($_terms) {
		remove_filter('get_terms', [$this, 'get_terms'], 10);
		//debug_log($terms);
		$terms = [];

		if(!empty($_terms)) {
			foreach ($_terms as $key => $value) {
				$term = $value;
				$term->name = strip_tags($value->description).' ('.$value->name.')';
				$terms[] = $term;
			}
		}
		
		return $terms;
	}

	public function customtaxorder_css() {
		if ( isset($_GET['page']) ) {
			$pos_page = sanitize_text_field( $_GET['page'] );
			if ( $pos_page == 'customtaxorder' ) {
				?>
				<style>
					.customtaxorder .order-widget ul li.lineitem:first-child {
						display: none;
					}
				</style>
				<?PHP
			}
		}
	}

	public function customtaxorder_get_taxonomies($taxonomies) {
		//debug_log(array_keys($taxonomies));

		unset($taxonomies['category']);
		unset($taxonomies['post_tag']);
		unset($taxonomies['nav_menu']);
		unset($taxonomies['link_category']);
		unset($taxonomies['post_format']);
		unset($taxonomies['wp_theme']);
		unset($taxonomies['wp_template_part_area']);
		unset($taxonomies['wp_pattern_category']);
		unset($taxonomies['wp_theme']);

		return $taxonomies;
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Custome_Taxonomy_Order_Ne::instance();