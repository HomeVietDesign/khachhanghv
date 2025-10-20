<?php
namespace HomeViet;

class Custom_Types {

	private static $instance = null;

	private function __construct() {

		add_action( 'admin_menu', [$this, '_admin_action_rename_menu'], 9999 );

		add_action( 'admin_head', [$this, 'admin_menu_highlight'] );
		
		add_action( 'after_setup_theme', [$this, '_theme_action_register_taxonomy'], 8 );

		// đặt thứ tự hook là 10 để các plugin có thể nhận được các type tùy biến
		add_action( 'after_setup_theme', [$this, '_theme_action_register_custom_type_10'], 8 );

		// đặt thứ tự hook là 9999 để có thể đảm bảo lần chỉnh cuối nhất
		add_action( 'init', [$this, '_theme_action_change_object_content_labels'], 9999 );
	
	}

	/**
	 * Changes the labels value od the posts type: post from Post to Blog Post
	 * @internal
	 */
	public function _theme_action_change_object_content_labels() {
		global $wp_post_types, $wp_taxonomies;
		
		if( isset($wp_post_types['post']) && is_object( $wp_post_types['post']) && !empty($wp_post_types['post']->labels)) {
			$wp_post_types['post']->labels->name               = 'Sản phẩm';
			$wp_post_types['post']->labels->singular_name      = 'Sản phẩm';
			$wp_post_types['post']->labels->add_new            = 'Thêm Sản phẩm';
			$wp_post_types['post']->labels->add_new_item       = 'Thêm Sản phẩm mới';
			$wp_post_types['post']->labels->all_items          = 'Tất cả các Sản phẩm';
			$wp_post_types['post']->labels->edit_item          = 'Sửa Sản phẩm';
			$wp_post_types['post']->labels->name_admin_bar     = 'Sản phẩm';
			$wp_post_types['post']->labels->menu_name          = 'Sản phẩm';
			$wp_post_types['post']->labels->new_item           = 'Sản phẩm mới';
			$wp_post_types['post']->labels->not_found          = 'Không có Sản phẩm nào';
			$wp_post_types['post']->labels->not_found_in_trash = 'Không có Sản phẩm nào trong thùng rác';
			$wp_post_types['post']->labels->search_items       = 'Tìm Sản phẩm';
			$wp_post_types['post']->labels->view_item          = 'Xem Sản phẩm';
		}

		if( isset($wp_taxonomies['category']) && is_object( $wp_taxonomies['category']) && !empty($wp_taxonomies['category']->labels) ) {
			$wp_taxonomies['category']->label = 'Phân loại';
			$wp_taxonomies['category']->labels->name = 'Phân loại';
			$wp_taxonomies['category']->labels->singular_name = 'Phân loại';
			$wp_taxonomies['category']->labels->add_new = 'Thêm phân loại';
			$wp_taxonomies['category']->labels->add_new_item = 'Thêm phân loại';
			$wp_taxonomies['category']->labels->edit_item = 'Sửa phân loại';
			$wp_taxonomies['category']->labels->new_item = 'Phân loại';
			$wp_taxonomies['category']->labels->view_item = 'Xem phân loại';
			$wp_taxonomies['category']->labels->search_items = 'Tìm phân loại';
			$wp_taxonomies['category']->labels->not_found = 'Không có phân loại nào được tìm thấy';
			$wp_taxonomies['category']->labels->not_found_in_trash = 'Không có phân loại nào trong thùng rác';
			$wp_taxonomies['category']->labels->all_items = 'Tất cả phân loại';
			$wp_taxonomies['category']->labels->menu_name = 'Phân loại';
			$wp_taxonomies['category']->labels->name_admin_bar = 'Phân loại';
		}

		if( isset($wp_taxonomies['post_tag']) && is_object( $wp_taxonomies['post_tag']) && !empty($wp_taxonomies['post_tag']->labels) ) {
			$wp_taxonomies['post_tag']->label = 'Đặc điểm';
			$wp_taxonomies['post_tag']->labels->name = 'Đặc điểm';
			$wp_taxonomies['post_tag']->labels->singular_name = 'Đặc điểm';
			$wp_taxonomies['post_tag']->labels->add_new = 'Thêm đặc điểm';
			$wp_taxonomies['post_tag']->labels->add_new_item = 'Thêm đặc điểm';
			$wp_taxonomies['post_tag']->labels->edit_item = 'Sửa đặc điểm';
			$wp_taxonomies['post_tag']->labels->new_item = 'Loại nhà';
			$wp_taxonomies['post_tag']->labels->view_item = 'Xem đặc điểm';
			$wp_taxonomies['post_tag']->labels->search_items = 'Tìm đặc điểm';
			$wp_taxonomies['post_tag']->labels->not_found = 'Không có đặc điểm nào được tìm thấy';
			$wp_taxonomies['post_tag']->labels->not_found_in_trash = 'Không có đặc điểm nào trong thùng rác';
			$wp_taxonomies['post_tag']->labels->all_items = 'Tất cả đặc điểm';
			$wp_taxonomies['post_tag']->labels->menu_name = 'Đặc điểm';
			$wp_taxonomies['post_tag']->labels->name_admin_bar = 'Đặc điểm';
		}
	}

	public function _theme_action_register_custom_type_10() {

		// nhà thầu
		$labels = array(
			'name'               => 'Nhà thầu',
			'singular_name'      => 'Nhà thầu',
			'add_new'            => 'Thêm mới Nhà thầu',
			'add_new_item'       => 'Thêm mới Nhà thầu',
			'edit_item'          => 'Sửa Nhà thầu',
			'new_item'           => 'Nhà thầu mới',
			'view_item'          => 'Xem Nhà thầu',
			'search_items'       => 'Tìm Nhà thầu',
			'not_found'          => 'Không có Nhà thầu nào',
			'not_found_in_trash' => 'Không có Nhà thầu nào trong Thùng rác',
			'parent_item_colon'  => 'Nhà thầu cấp trên:',
			'menu_name'          => 'Nhà thầu',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 3,
			'menu_icon'           => 'dashicons-groups',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			//'rewrite'             => ['slug'=>'nha-thau'],
			'rewrite'             => false,
			'capability_type'     => 'contractor',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'contractor', $args );

		$labels = array(
			'name'               => 'Trang nhà thầu',
			'singular_name'      => 'Trang nhà thầu',
			'add_new'            => 'Thêm mới Trang nhà thầu',
			'add_new_item'       => 'Thêm mới Trang nhà thầu',
			'edit_item'          => 'Sửa Trang nhà thầu',
			'new_item'           => 'Trang nhà thầu mới',
			'view_item'          => 'Xem Trang nhà thầu',
			'search_items'       => 'Tìm Trang nhà thầu',
			'not_found'          => 'Không có Trang nhà thầu nào',
			'not_found_in_trash' => 'Không có Trang nhà thầu nào trong Thùng rác',
			'parent_item_colon'  => 'Trang nhà thầu cha:',
			'menu_name'          => 'Trang nhà thầu',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			//'description'         => 'description',
			//'taxonomies'          => array(),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-groups',
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => ['slug'=>'trang-nha-thau'],
			'capability_type'     => 'contractor_page',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'editor',
				//'author',
				//'thumbnail',
				//'excerpt',
				//'custom-fields',
				//'trackbacks',
				//'comments',
				'revisions',
				'page-attributes',
				//'post-formats',
			),
		);
		register_post_type( 'contractor_page', $args );

		$labels = array(
			'name'               => 'Dự toán Xây dựng',
			'singular_name'      => 'Dự toán Xây dựng',
			'add_new'            => 'Thêm mới Dự toán Xây dựng',
			'add_new_item'       => 'Thêm mới Dự toán Xây dựng',
			'edit_item'          => 'Sửa Dự toán Xây dựng',
			'new_item'           => 'Dự toán Xây dựng mới',
			'view_item'          => 'Xem Dự toán Xây dựng',
			'search_items'       => 'Tìm Dự toán Xây dựng',
			'not_found'          => 'Không có Dự toán Xây dựng nào',
			'not_found_in_trash' => 'Không có Dự toán Xây dựng nào trong Thùng rác',
			'parent_item_colon'  => 'Dự toán Xây dựng cấp trên:',
			'menu_name'          => 'Dự toán Xây dựng',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-calculator',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'econstruction',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'econstruction', $args );

		$labels = array(
			'name'               => 'Dự toán Đồ gỗ',
			'singular_name'      => 'Dự toán Đồ gỗ',
			'add_new'            => 'Thêm mới Dự toán Đồ gỗ',
			'add_new_item'       => 'Thêm mới Dự toán Đồ gỗ',
			'edit_item'          => 'Sửa Dự toán Đồ gỗ',
			'new_item'           => 'Dự toán Đồ gỗ mới',
			'view_item'          => 'Xem Dự toán Đồ gỗ',
			'search_items'       => 'Tìm Dự toán Đồ gỗ',
			'not_found'          => 'Không có Dự toán Đồ gỗ nào',
			'not_found_in_trash' => 'Không có Dự toán Đồ gỗ nào trong Thùng rác',
			'parent_item_colon'  => 'Dự toán Đồ gỗ cấp trên:',
			'menu_name'          => 'Dự toán Đồ gỗ',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-calculator',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'efurniture',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'efurniture', $args );

		$labels = array(
			'name'               => 'Dự toán Đèn',
			'singular_name'      => 'Dự toán Đèn',
			'add_new'            => 'Thêm mới Dự toán Đèn',
			'add_new_item'       => 'Thêm mới Dự toán Đèn',
			'edit_item'          => 'Sửa Dự toán Đèn',
			'new_item'           => 'Dự toán Đèn mới',
			'view_item'          => 'Xem Dự toán Đèn',
			'search_items'       => 'Tìm Dự toán Đèn',
			'not_found'          => 'Không có Dự toán Đèn nào',
			'not_found_in_trash' => 'Không có Dự toán Đèn nào trong Thùng rác',
			'parent_item_colon'  => 'Dự toán Đèn cấp trên:',
			'menu_name'          => 'Dự toán Đèn',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-calculator',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'elighting',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'elighting', $args );

		$labels = array(
			'name'               => 'Hồ sơ thiết kế',
			'singular_name'      => 'Hồ sơ thiết kế',
			'add_new'            => 'Thêm mới Hồ sơ thiết kế',
			'add_new_item'       => 'Thêm mới Hồ sơ thiết kế',
			'edit_item'          => 'Sửa Hồ sơ thiết kế',
			'new_item'           => 'Hồ sơ thiết kế mới',
			'view_item'          => 'Xem Hồ sơ thiết kế',
			'search_items'       => 'Tìm Hồ sơ thiết kế',
			'not_found'          => 'Không có Hồ sơ thiết kế nào',
			'not_found_in_trash' => 'Không có Hồ sơ thiết kế nào trong Thùng rác',
			'parent_item_colon'  => 'Hồ sơ thiết kế cấp trên:',
			'menu_name'          => 'Hồ sơ thiết kế',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-book',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'document',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'document', $args );

		$labels = array(
			'name'               => 'Hợp đồng',
			'singular_name'      => 'Hợp đồng',
			'add_new'            => 'Thêm mới Hợp đồng',
			'add_new_item'       => 'Thêm mới Hợp đồng',
			'edit_item'          => 'Sửa Hợp đồng',
			'new_item'           => 'Hợp đồng mới',
			'view_item'          => 'Xem Hợp đồng',
			'search_items'       => 'Tìm Hợp đồng',
			'not_found'          => 'Không có Hợp đồng nào',
			'not_found_in_trash' => 'Không có Hợp đồng nào trong Thùng rác',
			'parent_item_colon'  => 'Hợp đồng cấp trên:',
			'menu_name'          => 'Hợp đồng',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-media-text',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'contract',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'contract', $args );

		$labels = array(
			'name'               => 'Nhóm zalo',
			'singular_name'      => 'Nhóm zalo',
			'add_new'            => 'Thêm mới Nhóm zalo',
			'add_new_item'       => 'Thêm mới Nhóm zalo',
			'edit_item'          => 'Sửa Nhóm zalo',
			'new_item'           => 'Nhóm zalo mới',
			'view_item'          => 'Xem Nhóm zalo',
			'search_items'       => 'Tìm Nhóm zalo',
			'not_found'          => 'Không có Nhóm zalo nào',
			'not_found_in_trash' => 'Không có Nhóm zalo nào trong Thùng rác',
			'parent_item_colon'  => 'Nhóm zalo cấp trên:',
			'menu_name'          => 'Nhóm zalo',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-format-chat',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'gzalo',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'gzalo', $args );

		$labels = array(
			'name'               => 'Save ảnh',
			'singular_name'      => 'Save ảnh',
			'add_new'            => 'Thêm mới Save ảnh',
			'add_new_item'       => 'Thêm mới Save ảnh',
			'edit_item'          => 'Sửa Save ảnh',
			'new_item'           => 'Save ảnh mới',
			'view_item'          => 'Xem Save ảnh',
			'search_items'       => 'Tìm Save ảnh',
			'not_found'          => 'Không có Save ảnh nào',
			'not_found_in_trash' => 'Không có Save ảnh nào trong Thùng rác',
			'parent_item_colon'  => 'Save ảnh cấp trên:',
			'menu_name'          => 'Save ảnh',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-format-gallery',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'media',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'media', $args );

		$labels = array(
			'name'               => 'Nha88',
			'singular_name'      => 'Nha88',
			'add_new'            => 'Thêm mới Nha88',
			'add_new_item'       => 'Thêm mới Nha88',
			'edit_item'          => 'Sửa Nha88',
			'new_item'           => 'Nha88 mới',
			'view_item'          => 'Xem Nha88',
			'search_items'       => 'Tìm Nha88',
			'not_found'          => 'Không có Nha88 nào',
			'not_found_in_trash' => 'Không có Nha88 nào trong Thùng rác',
			'parent_item_colon'  => 'Nha88 cấp trên:',
			'menu_name'          => 'Nha88',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-bank',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'nha88',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'nha88', $args );

		$labels = array(
			'name'               => 'Thiết kế',
			'singular_name'      => 'Thiết kế',
			'add_new'            => 'Thêm mới Thiết kế',
			'add_new_item'       => 'Thêm mới Thiết kế',
			'edit_item'          => 'Sửa Thiết kế',
			'new_item'           => 'Thiết kế mới',
			'view_item'          => 'Xem Thiết kế',
			'search_items'       => 'Tìm Thiết kế',
			'not_found'          => 'Không có Thiết kế nào',
			'not_found_in_trash' => 'Không có Thiết kế nào trong Thùng rác',
			'parent_item_colon'  => 'Thiết kế cấp trên:',
			'menu_name'          => 'Thiết kế',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-art',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'design',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'design', $args );

		$labels = array(
			'name'               => 'Thi công',
			'singular_name'      => 'Thi công',
			'add_new'            => 'Thêm mới Thi công',
			'add_new_item'       => 'Thêm mới Thi công',
			'edit_item'          => 'Sửa Thi công',
			'new_item'           => 'Thi công mới',
			'view_item'          => 'Xem Thi công',
			'search_items'       => 'Tìm Thi công',
			'not_found'          => 'Không có Thi công nào',
			'not_found_in_trash' => 'Không có Thi công nào trong Thùng rác',
			'parent_item_colon'  => 'Thi công cấp trên:',
			'menu_name'          => 'Thi công',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-hammer',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'construction',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'construction', $args );

		$labels = array(
			'name'               => 'Công việc',
			'singular_name'      => 'Công việc',
			'add_new'            => 'Thêm mới Công việc',
			'add_new_item'       => 'Thêm mới Công việc',
			'edit_item'          => 'Sửa Công việc',
			'new_item'           => 'Công việc mới',
			'view_item'          => 'Xem Công việc',
			'search_items'       => 'Tìm Công việc',
			'not_found'          => 'Không có Công việc nào',
			'not_found_in_trash' => 'Không có Công việc nào trong Thùng rác',
			'parent_item_colon'  => 'Công việc cấp trên:',
			'menu_name'          => 'Công việc',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-admin-generic',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'work',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
				//'editor',
				//'excerpt',
				//'revisions',
				//'page-attributes',
			),
		);
		register_post_type( 'work', $args );

		$labels = array(
			'name'               => 'Đầu tư',
			'singular_name'      => 'Đầu tư',
			'add_new'            => 'Thêm mới Đầu tư',
			'add_new_item'       => 'Thêm mới Đầu tư',
			'edit_item'          => 'Sửa Đầu tư',
			'new_item'           => 'Đầu tư mới',
			'view_item'          => 'Xem Đầu tư',
			'search_items'       => 'Tìm Đầu tư',
			'not_found'          => 'Không có Đầu tư nào',
			'not_found_in_trash' => 'Không có Đầu tư nào trong Thùng rác',
			'parent_item_colon'  => 'Đầu tư cấp trên:',
			'menu_name'          => 'Đầu tư',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-share-alt',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'investment',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
			),
		);
		register_post_type( 'investment', $args );

		$labels = array(
			'name'               => 'Chiết tính',
			'singular_name'      => 'Chiết tính',
			'add_new'            => 'Thêm mới Chiết tính',
			'add_new_item'       => 'Thêm mới Chiết tính',
			'edit_item'          => 'Sửa Chiết tính',
			'new_item'           => 'Chiết tính mới',
			'view_item'          => 'Xem Chiết tính',
			'search_items'       => 'Tìm Chiết tính',
			'not_found'          => 'Không có Chiết tính nào',
			'not_found_in_trash' => 'Không có Chiết tính nào trong Thùng rác',
			'parent_item_colon'  => 'Chiết tính cấp trên:',
			'menu_name'          => 'Chiết tính',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-money-alt',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'rebate',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'thumbnail',
			),
		);
		register_post_type( 'rebate', $args );
	}

	public function customtaxorder() {

	}

	/**
	 * Changes the name in admin menu from Post to Blog Post
	 * @internal
	 */
	public function _admin_action_rename_menu() {
		global $menu, $submenu;

		//debug_log($menu);

		remove_menu_page( 'edit-comments.php' ); // ẩn menu Comments
		remove_menu_page( 'edit.php' ); // ẩn menu Blog posts
		remove_menu_page( 'fw-extensions' ); // ẩn menu Unyson
		remove_menu_page( 'separator1' ); // ẩn menu divider
		remove_menu_page( 'upload.php' );
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'filebird-settings' );
		remove_menu_page( 'wp-mail-smtp' );
		remove_menu_page( 'media-cloud' );

		remove_submenu_page( 'tools.php', 'customtaxorder' );
		
		//debug_log($submenu);

		add_menu_page( 'Chủ đầu tư', 'Chủ đầu tư', 'manage_passwordss', 'edit-tags.php?taxonomy=passwords', null, 'dashicons-businessperson', 2 );
		
		if(function_exists('customtaxorder_menu')) {
			global $title;
			$title = 'Custom Taxonomy Order';
			$custom_cap = apply_filters( 'customtaxorder_custom_cap', 'manage_categories' );
			add_menu_page( 'Sắp thứ tự', 'Sắp thứ tự', $custom_cap, 'tools.php?page=customtaxorder', '', 'dashicons-list-view', 50 );
		}
		
		add_menu_page( 'Manager', 'Manager', 'manage_options', 'manager', [$this, 'manage_page'], 'dashicons-sos', 100 );

		add_submenu_page( 'manager', 'Media', 'Media', 'upload_files', 'upload.php' );

		if(class_exists('\FileBird\Admin\Settings')) {
			add_submenu_page( 'manager', __( 'FileBird', 'filebird' ), __( 'FileBird', 'filebird' ), 'manage_options', \FileBird\Admin\Settings::SETTING_PAGE_SLUG );
		}
		if(class_exists('\WPMailSMTP\Admin\Area')) {
			add_submenu_page( 'manager', esc_html__( 'WP Mail SMTP', 'wp-mail-smtp' ), esc_html__( 'WP Mail SMTP', 'wp-mail-smtp' ), wp_mail_smtp()->get_capability_manage_options(), \WPMailSMTP\Admin\Area::SLUG );
		}
		if(class_exists('\MediaCloud\Plugin\Tools\ToolsManager')) {
			add_submenu_page( 'manager', 'Media Cloud', 'Media Cloud', 'manage_options', 'admin.php?page=media-cloud-settings' );
		}

		add_submenu_page( 'manager', 'Trang nhà thầu', 'Trang nhà thầu', 'manage_options', 'edit.php?post_type=contractor_page' );
	}

	public function admin_menu_highlight() {
		global $pagenow, $taxonomy, $parent_file, $submenu_file, $current_screen, $plugin_page, $menu, $submenu;

		if(($pagenow=='edit-tags.php' || $pagenow=='term.php') && $taxonomy=='passwords') {
			$parent_file = 'edit-tags.php?taxonomy=passwords';
		}

		if(isset($submenu[''])) {
			unset($submenu['']);
		}

		//debug_log($current_screen->id);

		if($current_screen->id=='upload') {
			if(isset($submenu['upload.php'])) {
				unset($submenu['upload.php']);
			}
			$parent_file = 'manager'; // highlight menu cha
			$submenu_file = 'upload.php'; // highlight submenu
			$plugin_page  = 'upload.php';   // 🔥 giúp menu cha mở ra
		}

		if(class_exists('\WPMailSMTP\Admin\Area') && isset($_GET['page']) && $_GET['page'] === \WPMailSMTP\Admin\Area::SLUG) {
			if(isset($submenu['wp-mail-smtp'])) {
				unset($submenu['wp-mail-smtp']);
			}
			$parent_file = 'manager'; // highlight menu cha
        	$submenu_file = \WPMailSMTP\Admin\Area::SLUG; // highlight submenu
        	$plugin_page  = \WPMailSMTP\Admin\Area::SLUG;   // 🔥 giúp menu cha mở ra
		}

		if($current_screen->id=='media-cloud_page_media-cloud-settings') {
			if(isset($submenu['media-cloud'])) {
				unset($submenu['media-cloud']);
			}
			$parent_file = 'manager'; // highlight menu cha
			$submenu_file = 'admin.php?page=media-cloud-settings'; // highlight submenu
			$plugin_page  = 'media-cloud-settings';   // 🔥 giúp menu cha mở ra
		}

		if( $current_screen->id=='tools_page_customtaxorder' || preg_match('/admin_page_customtaxorder-(.+)/', $current_screen->id) ) {
			$parent_file = 'tools.php?page=customtaxorder'; // highlight menu cha
			// $submenu_file = 'tools.php?page=customtaxorder'; // highlight submenu
			$plugin_page  = 'tools.php?page=customtaxorder';   // 🔥 giúp menu cha mở ra
		}

		
	}

	public function manage_page() {
		?>
		Manager page
		<?php
	}

	public function _theme_action_register_taxonomy() {
		//global $wp_taxonomies;

		// if ( taxonomy_exists( 'post_tag'))
		// 	unset( $wp_taxonomies['post_tag']);
		// unregister_taxonomy('post_tag');

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Địa điểm',
			'singular_name'     => 'Địa điểm',
			'search_items'      => 'Tìm Địa điểm',
			'all_items'         => 'Tất cả Địa điểm',
			'edit_item'         => 'Sửa Địa điểm',
			'update_item'       => 'Cập nhật Địa điểm',
			'add_new_item'      => 'Thêm Địa điểm mới',
			'new_item_name'     => 'Địa điểm mới',
			'menu_name'         => 'Địa điểm',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => ['slug'=>'dia-diem'],
			'public' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud' => false,
		);
		//register_taxonomy( 'location', 'post', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Hạng mục',
			'singular_name'     => 'Hạng mục',
			'search_items'      => 'Tìm Hạng mục',
			'all_items'         => 'Tất cả Hạng mục',
			'edit_item'         => 'Sửa Hạng mục',
			'update_item'       => 'Cập nhật Hạng mục',
			'add_new_item'      => 'Thêm Hạng mục mới',
			'new_item_name'     => 'Hạng mục mới',
			'menu_name'         => 'Hạng mục',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_contractor_cats',
				'edit_terms'   => 'edit_contractor_cats',
				'delete_terms' => 'delete_contractor_cats',
				'assign_terms' => 'edit_contractors',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		//update_option( 'default_term_contractor_cat', 1937 );
		register_taxonomy( 'contractor_cat', 'contractor', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm đánh giá',
			'singular_name'     => 'Nhóm đánh giá',
			'search_items'      => 'Tìm Nhóm đánh giá',
			'all_items'         => 'Tất cả Nhóm đánh giá',
			'edit_item'         => 'Sửa Nhóm đánh giá',
			'update_item'       => 'Cập nhật Nhóm đánh giá',
			'add_new_item'      => 'Thêm Nhóm đánh giá mới',
			'new_item_name'     => 'Nhóm đánh giá mới',
			'menu_name'         => 'Nhóm đánh giá',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			//'rewrite'           => ['slug'=>'hang-muc'],
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_contractor_ratings',
				'edit_terms'   => 'edit_contractor_ratings',
				'delete_terms' => 'delete_contractor_ratings',
				'assign_terms' => 'edit_contractors',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'contractor_rating', 'contractor', $args ); // our new 'format' taxonomy

		$labels = array(
			'name'              => 'Phân khúc',
			'singular_name'     => 'Phân khúc',
			'search_items'      => 'Tìm Phân khúc',
			'all_items'         => 'Tất cả Phân khúc',
			'edit_item'         => 'Sửa Phân khúc',
			'update_item'       => 'Cập nhật Phân khúc',
			'add_new_item'      => 'Thêm Phân khúc mới',
			'new_item_name'     => 'Phân khúc mới',
			'menu_name'         => 'Phân khúc',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_contractor_classs',
				'edit_terms'   => 'edit_contractor_classs',
				'delete_terms' => 'edit_contractor_classs',
				'assign_terms' => 'edit_contractors',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'contractor_class', 'contractor', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Chủ đầu tư',
			'singular_name'     => 'Chủ đầu tư',
			'search_items'      => 'Tìm Chủ đầu tư',
			'all_items'         => 'Tất cả Chủ đầu tư',
			'edit_item'         => 'Sửa Chủ đầu tư',
			'update_item'       => 'Cập nhật Chủ đầu tư',
			'add_new_item'      => 'Thêm Chủ đầu tư mới',
			'new_item_name'     => 'Chủ đầu tư mới',
			'menu_name'         => 'Chủ đầu tư',
		);

		$default_password = [
			'name' => 'HV@5011',
			'slug' => 'hv-5011',
			'description' => 'Mặc định'
		];
		$default = (int) get_option( 'default_term_passwords', -1 );
		// delete_option( 'default_term_passwords' );
		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_passwordss',
				'edit_terms'   => 'edit_passwordss',
				'delete_terms' => 'delete_passwordss',
				'assign_terms' => 'edit_contractor_pages',
			],
			'public' => false,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
			'default_term' => ($default>0)?$default:$default_password
			
		);
		register_taxonomy( 'passwords', 'contractor_page', $args );
		
		// our new 'format' taxonomy
		$labels = array(
			'name'              => 'Tỉnh thành',
			'singular_name'     => 'Tỉnh thành',
			'search_items'      => 'Tìm Tỉnh thành',
			'all_items'         => 'Tất cả Tỉnh thành',
			'edit_item'         => 'Sửa Tỉnh thành',
			'update_item'       => 'Cập nhật Tỉnh thành',
			'add_new_item'      => 'Thêm Tỉnh thành mới',
			'new_item_name'     => 'Tỉnh thành mới',
			'menu_name'         => 'Tỉnh thành',
		);

		$default_province = [
			'name' => 'Toàn quốc',
			'slug' => 'toan-quoc',
			'description' => 'Mặc định'
		];
		$default = (int) get_option( 'default_term_province', -1 );

		//delete_option( 'default_term_province' );

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_provinces',
				'edit_terms'   => 'edit_provinces',
				'delete_terms' => 'delete_provinces',
				'assign_terms' => 'edit_contractors',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
			'default_term' => ($default>0)?$default:$default_province
			
		);		
		register_taxonomy( 'province', 'contractor', $args );


		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm Dự toán Xây dựng',
			'singular_name'     => 'Nhóm Dự toán Xây dựng',
			'search_items'      => 'Tìm Nhóm Dự toán Xây dựng',
			'all_items'         => 'Tất cả Nhóm Dự toán Xây dựng',
			'edit_item'         => 'Sửa Nhóm Dự toán Xây dựng',
			'update_item'       => 'Cập nhật Nhóm Dự toán Xây dựng',
			'add_new_item'      => 'Thêm Nhóm Dự toán Xây dựng mới',
			'new_item_name'     => 'Nhóm Dự toán Xây dựng mới',
			'menu_name'         => 'Nhóm Dự toán Xây dựng',
			'parent_item'         => 'Nhóm cấp trên',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_econstruction_cats',
				'edit_terms'   => 'edit_econstruction_cats',
				'delete_terms' => 'delete_econstruction_cats',
				'assign_terms' => 'edit_econstructions',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'econstruction_cat', 'econstruction', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm Dự toán Đồ gỗ',
			'singular_name'     => 'Nhóm Dự toán Đồ gỗ',
			'search_items'      => 'Tìm Nhóm Dự toán Đồ gỗ',
			'all_items'         => 'Tất cả Nhóm Dự toán Đồ gỗ',
			'edit_item'         => 'Sửa Nhóm Dự toán Đồ gỗ',
			'update_item'       => 'Cập nhật Nhóm Dự toán Đồ gỗ',
			'add_new_item'      => 'Thêm Nhóm Dự toán Đồ gỗ mới',
			'new_item_name'     => 'Nhóm Dự toán Đồ gỗ mới',
			'menu_name'         => 'Nhóm Dự toán Đồ gỗ',
			'parent_item'         => 'Nhóm cấp trên',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_efurniture_cats',
				'edit_terms'   => 'edit_efurniture_cats',
				'delete_terms' => 'delete_efurniture_cats',
				'assign_terms' => 'edit_efurnitures',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'efurniture_cat', 'efurniture', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm Dự toán Đèn',
			'singular_name'     => 'Nhóm Dự toán Đèn',
			'search_items'      => 'Tìm Nhóm Dự toán Đèn',
			'all_items'         => 'Tất cả Nhóm Dự toán Đèn',
			'edit_item'         => 'Sửa Nhóm Dự toán Đèn',
			'update_item'       => 'Cập nhật Nhóm Dự toán Đèn',
			'add_new_item'      => 'Thêm Nhóm Dự toán Đèn mới',
			'new_item_name'     => 'Nhóm Dự toán Đèn mới',
			'menu_name'         => 'Nhóm Dự toán Đèn',
			'parent_item'         => 'Nhóm cấp trên',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_elighting_cats',
				'edit_terms'   => 'edit_elighting_cats',
				'delete_terms' => 'delete_elighting_cats',
				'assign_terms' => 'edit_elightings',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'elighting_cat', 'elighting', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm hồ sơ thiết kế',
			'singular_name'     => 'Nhóm hồ sơ thiết kế',
			'search_items'      => 'Tìm Nhóm hồ sơ thiết kế',
			'all_items'         => 'Tất cả Nhóm hồ sơ thiết kế',
			'edit_item'         => 'Sửa Nhóm hồ sơ thiết kế',
			'update_item'       => 'Cập nhật Nhóm hồ sơ thiết kế',
			'add_new_item'      => 'Thêm Nhóm hồ sơ thiết kế mới',
			'new_item_name'     => 'Nhóm hồ sơ thiết kế mới',
			'menu_name'         => 'Nhóm hồ sơ thiết kế',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_document_cats',
				'edit_terms'   => 'edit_document_cats',
				'delete_terms' => 'delete_document_cats',
				'assign_terms' => 'edit_documents',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'document_cat', 'document', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm hợp đồng',
			'singular_name'     => 'Nhóm hợp đồng',
			'search_items'      => 'Tìm Nhóm hợp đồng',
			'all_items'         => 'Tất cả Nhóm hợp đồng',
			'edit_item'         => 'Sửa Nhóm hợp đồng',
			'update_item'       => 'Cập nhật Nhóm hợp đồng',
			'add_new_item'      => 'Thêm Nhóm hợp đồng mới',
			'new_item_name'     => 'Nhóm hợp đồng mới',
			'menu_name'         => 'Nhóm hợp đồng',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_contract_cats',
				'edit_terms'   => 'edit_contract_cats',
				'delete_terms' => 'delete_contract_cats',
				'assign_terms' => 'edit_contracts',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'contract_cat', 'contract', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Phân nhóm zalo',
			'singular_name'     => 'Phân nhóm zalo',
			'search_items'      => 'Tìm Phân nhóm zalo',
			'all_items'         => 'Tất cả Phân nhóm zalo',
			'edit_item'         => 'Sửa Phân nhóm zalo',
			'update_item'       => 'Cập nhật Phân nhóm zalo',
			'add_new_item'      => 'Thêm Phân nhóm zalo mới',
			'new_item_name'     => 'Phân nhóm zalo mới',
			'menu_name'         => 'Phân nhóm zalo',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_gzalo_cats',
				'edit_terms'   => 'edit_gzalo_cats',
				'delete_terms' => 'delete_gzalo_cats',
				'assign_terms' => 'edit_gzalos',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'gzalo_cat', 'gzalo', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm save ảnh',
			'singular_name'     => 'Nhóm save ảnh',
			'search_items'      => 'Tìm Nhóm save ảnh',
			'all_items'         => 'Tất cả Nhóm save ảnh',
			'edit_item'         => 'Sửa Nhóm save ảnh',
			'update_item'       => 'Cập nhật Nhóm save ảnh',
			'add_new_item'      => 'Thêm Nhóm save ảnh mới',
			'new_item_name'     => 'Nhóm save ảnh mới',
			'menu_name'         => 'Nhóm save ảnh',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_media_cats',
				'edit_terms'   => 'edit_media_cats',
				'delete_terms' => 'delete_media_cats',
				'assign_terms' => 'edit_medias',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'media_cat', 'media', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Loại Nha88',
			'singular_name'     => 'Loại Nha88',
			'search_items'      => 'Tìm Loại Nha88',
			'all_items'         => 'Tất cả Loại Nha88',
			'edit_item'         => 'Sửa Loại Nha88',
			'update_item'       => 'Cập nhật Loại Nha88',
			'add_new_item'      => 'Thêm Loại Nha88 mới',
			'new_item_name'     => 'Loại Nha88 mới',
			'menu_name'         => 'Loại Nha88',
		);
		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'public' => false,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
			
		);
		register_taxonomy( 'nha88_type', 'nha88', $args );

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm Nha88',
			'singular_name'     => 'Nhóm Nha88',
			'search_items'      => 'Tìm Nhóm Nha88',
			'all_items'         => 'Tất cả Nhóm Nha88',
			'edit_item'         => 'Sửa Nhóm Nha88',
			'update_item'       => 'Cập nhật Nhóm Nha88',
			'add_new_item'      => 'Thêm Nhóm Nha88 mới',
			'new_item_name'     => 'Nhóm Nha88 mới',
			'menu_name'         => 'Nhóm Nha88',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'nha88_cat', 'nha88', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm thiết kế',
			'singular_name'     => 'Nhóm thiết kế',
			'search_items'      => 'Tìm Nhóm thiết kế',
			'all_items'         => 'Tất cả Nhóm thiết kế',
			'edit_item'         => 'Sửa Nhóm thiết kế',
			'update_item'       => 'Cập nhật Nhóm thiết kế',
			'add_new_item'      => 'Thêm Nhóm thiết kế mới',
			'new_item_name'     => 'Nhóm thiết kế mới',
			'menu_name'         => 'Nhóm thiết kế',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_design_cats',
				'edit_terms'   => 'edit_design_cats',
				'delete_terms' => 'delete_design_cats',
				'assign_terms' => 'edit_designs',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'design_cat', 'design', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm thi công',
			'singular_name'     => 'Nhóm thi công',
			'search_items'      => 'Tìm Nhóm thi công',
			'all_items'         => 'Tất cả Nhóm thi công',
			'edit_item'         => 'Sửa Nhóm thi công',
			'update_item'       => 'Cập nhật Nhóm thi công',
			'add_new_item'      => 'Thêm Nhóm thi công mới',
			'new_item_name'     => 'Nhóm thi công mới',
			'menu_name'         => 'Nhóm thi công',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_construction_cats',
				'edit_terms'   => 'edit_construction_cats',
				'delete_terms' => 'delete_construction_cats',
				'assign_terms' => 'edit_constructions',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'construction_cat', 'construction', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm công việc',
			'singular_name'     => 'Nhóm công việc',
			'search_items'      => 'Tìm Nhóm công việc',
			'all_items'         => 'Tất cả Nhóm công việc',
			'edit_item'         => 'Sửa Nhóm công việc',
			'update_item'       => 'Cập nhật Nhóm công việc',
			'add_new_item'      => 'Thêm Nhóm công việc mới',
			'new_item_name'     => 'Nhóm công việc mới',
			'menu_name'         => 'Nhóm công việc',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_work_cats',
				'edit_terms'   => 'edit_work_cats',
				'delete_terms' => 'delete_work_cats',
				'assign_terms' => 'edit_works',
			],
			'public' => false,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'work_cat', 'work', $args );

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhân sự',
			'singular_name'     => 'Nhân sự',
			'search_items'      => 'Tìm Nhân sự',
			'all_items'         => 'Tất cả Nhân sự',
			'edit_item'         => 'Sửa Nhân sự',
			'update_item'       => 'Cập nhật Nhân sự',
			'add_new_item'      => 'Thêm Nhân sự mới',
			'new_item_name'     => 'Nhân sự mới',
			'menu_name'         => 'Nhân sự',
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_employees',
				'edit_terms'   => 'edit_employees',
				'delete_terms' => 'delete_employees',
				'assign_terms' => 'edit_works',
			],
			'public' => false,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'employee', 'work', $args );

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm đầu tư',
			'singular_name'     => 'Nhóm đầu tư',
			'search_items'      => 'Tìm Nhóm đầu tư',
			'all_items'         => 'Tất cả Nhóm đầu tư',
			'edit_item'         => 'Sửa Nhóm đầu tư',
			'update_item'       => 'Cập nhật Nhóm đầu tư',
			'add_new_item'      => 'Thêm Nhóm đầu tư mới',
			'new_item_name'     => 'Nhóm đầu tư mới',
			'menu_name'         => 'Nhóm đầu tư',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_investment_cats',
				'edit_terms'   => 'edit_investment_cats',
				'delete_terms' => 'delete_investment_cats',
				'assign_terms' => 'edit_investments',
			],
			'public' => false,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'investment_cat', 'investment', $args );

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhà đầu tư',
			'singular_name'     => 'Nhà đầu tư',
			'search_items'      => 'Tìm Nhà đầu tư',
			'all_items'         => 'Tất cả Nhà đầu tư',
			'edit_item'         => 'Sửa Nhà đầu tư',
			'update_item'       => 'Cập nhật Nhà đầu tư',
			'add_new_item'      => 'Thêm Nhà đầu tư mới',
			'new_item_name'     => 'Nhà đầu tư mới',
			'menu_name'         => 'Nhà đầu tư',
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_investors',
				'edit_terms'   => 'edit_investors',
				'delete_terms' => 'delete_investors',
				'assign_terms' => 'edit_investments',
			],
			'public' => false,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'investor', 'investment', $args );

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm chiết tính',
			'singular_name'     => 'Nhóm chiết tính',
			'search_items'      => 'Tìm Nhóm chiết tính',
			'all_items'         => 'Tất cả Nhóm chiết tính',
			'edit_item'         => 'Sửa Nhóm chiết tính',
			'update_item'       => 'Cập nhật Nhóm chiết tính',
			'add_new_item'      => 'Thêm Nhóm chiết tính mới',
			'new_item_name'     => 'Nhóm chiết tính mới',
			'menu_name'         => 'Nhóm chiết tính',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_rebate_cats',
				'edit_terms'   => 'edit_rebate_cats',
				'delete_terms' => 'delete_rebate_cats',
				'assign_terms' => 'edit_rebates',
			],
			'public' => false,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'rebate_cat', 'rebate', $args );

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Sản phẩm',
			'singular_name'     => 'Sản phẩm',
			'search_items'      => 'Tìm Sản phẩm',
			'all_items'         => 'Tất cả Sản phẩm',
			'edit_item'         => 'Sửa Sản phẩm',
			'update_item'       => 'Cập nhật Sản phẩm',
			'add_new_item'      => 'Thêm Sản phẩm mới',
			'new_item_name'     => 'Sản phẩm mới',
			'menu_name'         => 'Sản phẩm',
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_products',
				'edit_terms'   => 'edit_products',
				'delete_terms' => 'delete_products',
				'assign_terms' => 'edit_rebates',
			],
			'public' => false,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'product', 'rebate', $args );
	}
	
	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}

Custom_Types::instance();