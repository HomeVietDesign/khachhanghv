<?php
namespace HomeViet;

class Setup {

	private static $instance = null;

	private function __construct() {
		add_action( 'after_setup_theme', [$this, 'after_setup_theme'] );
		add_filter( 'use_widgets_block_editor', '__return_false' );
		add_filter( 'use_block_editor_for_post_type', '__return_false', 10 );

		add_filter( 'image_size_names_choose', [$this, 'image_sizes_choose'] );

		add_filter( 'edit_post_link', [$this, 'edit_post_link_target'] );

		add_action( 'init', [$this, 'add_rewrite_rule'] );
		add_filter( 'query_vars', [$this, 'add_query_vars'] );
		add_filter( 'template_include', [$this, 'template_include'], 9999, 1 );

		add_filter( 'posts_search', [$this, 'seo_post_search_by_title'], 10, 2 );

		add_filter( 'mime_types', [$this, 'fix_rar_mime_type'] );

		add_action( 'wp_loaded', [$this, 'wp_loaded'], 10 );
		add_action( 'admin_init', [$this, 'ajax_set_global_vars'], 10 );
		add_action( 'template_redirect', [$this, 'set_global_vars'], 5 );
		
	}


	public function fix_rar_mime_type($mime_types) {
		if(isset($mime_types['rar'])) {
			//$mime_types['rar'] = 'application/x-rar-compressed';
			$mime_types['rar'] = 'application/x-rar';
		}

		return $mime_types;
	}

	public function seo_post_search_by_title($search, $wp_query) {
	
		if ( ! empty( $search ) && ! empty( $wp_query->query_vars['s'] ) && isset( $wp_query->query_vars['custom_search'] ) ) {
			global $wpdb;

			$q = $wp_query->query_vars;
			$n = ! empty( $q['exact'] ) ? '' : '%';

			$search = array();

			foreach ( ( array ) $q['s'] as $term )
				$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );

			if ( ! is_user_logged_in() )
				$search[] = "$wpdb->posts.post_password = ''";

			$search = ' AND ' . implode( ' AND ', $search );

		}

		return $search;
	}

	public function template_include($template) {
		if ( get_query_var( 'contractor_search_page' ) == false || get_query_var( 'contractor_search_page' ) == '' ) {
			return $template;
		}
		return THEME_DIR . '/contractor-search-page.php';
	}

	public function add_query_vars($query_vars) {
		$query_vars[] = 'contractor_search_page';
    	return $query_vars;
	}

	public function add_rewrite_rule() {
	    add_rewrite_rule( '^contractor-search$', 'index.php?contractor_search_page=1', 'top' );
	}

	public function edit_post_link_target($link) {

		$link = str_replace('<a', '<a target="_blank"', $link);

		return $link;
	}

	public function image_sizes_choose( $size_names ) {

		$full = $size_names['full'];
		unset($size_names['full']);
		
		$new_sizes = array(
			'medium_large' => 'Medium large',
			'extra_large' => 'Extra large',
			'full' => $full,
		);

		return array_merge( $size_names, $new_sizes );
	}

	public function set_global_vars() {
		global $view;
		$view_id = isset($_REQUEST['view'])?absint($_REQUEST['view']):((is_singular()||is_page())?get_the_ID():0);
		if($view_id) {
			$view = get_post($view_id);
		}
	}

	public function ajax_set_global_vars() {
		if(defined('DOING_AJAX') && DOING_AJAX) {
			global $current_client;
			$current_client = isset($_REQUEST['client'])?get_term_by( 'id', absint($_REQUEST['client']), 'passwords' ):null;
		}
	}

	public function wp_loaded() {
		global $current_province, $current_client;
		
		$province = isset($_REQUEST['province'])?absint($_REQUEST['province']):0;
		$current_province = get_term_by( 'term_id', $province, 'province' );
		
		if(current_user_can('contractor_view')) {
			add_filter('post_type_link', [$this, 'contractor_page_link'], 10, 2);
		}

		$current_client = isset($_REQUEST['client'])?get_term_by( 'id', absint($_REQUEST['client']), 'passwords' ):null;
	}

	public function contractor_page_link($post_link, $post) {
		if($post->post_type=='contractor_page') {
			global $current_province;
			if($current_province) {
				$post_link = add_query_arg('province', $current_province->term_id, $post_link);
			}

		}

		return $post_link;
	}


	public function after_setup_theme() {
		global $popup;
		$popup = isset($_REQUEST['popup']) ? true : false;

		if($popup):
			show_admin_bar( false );
		endif;

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * This theme does not use a hard-coded <title> tag in the document head,
		 * WordPress will provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		remove_image_size( '1536x1536' );
		remove_image_size( '2048x2048' );

		add_image_size( 'extra_large', get_option('extra_large_size_w', 1600), get_option('extra_large_size_h', 0) );

		//add_theme_support( 'menus' );

		
		register_nav_menus(
			array(
				'primary' => 'Vị trí chính',
				'secondary_left' => 'Vị trí phụ trái',
				'secondary_right' => 'Vị trí phụ phải',
			)
		);

		add_theme_support('custom-background');

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'navigation-widgets',
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for Block Styles.
		//add_theme_support( 'wp-block-styles' );

		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Add support for responsive embedded content.
		add_theme_support( 'responsive-embeds' );

		add_filter('get_the_archive_title_prefix', '__return_empty_string');

	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}
Setup::instance();