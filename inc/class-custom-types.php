<?php
namespace HomeViet;

class Custom_Types {

	private static $instance = null;

	private function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_menu', [$this, '_admin_action_rename_menu'], 99 );

			add_filter( 'parent_file', [$this, 'admin_menu_highlight'] );

		}
		
		add_action( 'init', [$this, '_theme_action_register_taxonomy'], 10 );

		// đặt thứ tự hook là 10 để các plugin có thể nhận được các type tùy biến
		add_action( 'init', [$this, '_theme_action_register_custom_type_10'], 10 );

		// đặt thứ tự hook là 9999 để có thể đảm bảo lần chỉnh cuối nhất
		add_action( 'init', [$this, '_theme_action_change_object_content_labels'], 9999 );
	
	}

	public function admin_menu_highlight($parent_file) {
		global $pagenow, $taxonomy;

		//debug_log($taxonomy);

		if(($pagenow=='edit-tags.php' || $pagenow=='term.php') && $taxonomy=='passwords') {
			$parent_file = 'edit-tags.php?taxonomy=passwords';
		}

		// if ( $pagenow == 'post.php')
		// 	$parent_file = "post.php?post={$_REQUEST['post']}&action=edit";
		// elseif($pagenow == 'post-new.php')
		// 	$parent_file = "post-new.php?post_type={$_REQUEST['post_type']}";

		return $parent_file;
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

		$labels = array(
			'name'               => 'Nội dung',
			'singular_name'      => 'Nội dung',
			'add_new'            => 'Thêm mới Nội dung',
			'add_new_item'       => 'Thêm mới Nội dung',
			'edit_item'          => 'Sửa Nội dung',
			'new_item'           => 'Nội dung mới',
			'view_item'          => 'Xem Nội dung',
			'search_items'       => 'Tìm Nội dung',
			'not_found'          => 'Không có Nội dung nào',
			'not_found_in_trash' => 'Không có Nội dung nào trong Thùng rác',
			'parent_item_colon'  => 'Nội dung cha:',
			'menu_name'          => 'Nội dung',
		);
	
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array(),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => false,
			'menu_position'       => 15,
			'menu_icon'           => 'dashicons-media-text',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => false,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'content_builder',
			'map_meta_cap'     => true,
			'supports'            => array(
				'title',
				'editor',
				'revisions',
			),
		);
		//register_post_type( 'content_builder', $args );


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
			'menu_icon'           => 'dashicons-admin-post',
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
				'editor',
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
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 11,
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
			'name'               => 'Dự toán',
			'singular_name'      => 'Dự toán',
			'add_new'            => 'Thêm mới Dự toán',
			'add_new_item'       => 'Thêm mới Dự toán',
			'edit_item'          => 'Sửa Dự toán',
			'new_item'           => 'Dự toán mới',
			'view_item'          => 'Xem Dự toán',
			'search_items'       => 'Tìm Dự toán',
			'not_found'          => 'Không có Dự toán nào',
			'not_found_in_trash' => 'Không có Dự toán nào trong Thùng rác',
			'parent_item_colon'  => 'Dự toán cấp trên:',
			'menu_name'          => 'Dự toán (bỏ)',
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
			'menu_icon'           => 'dashicons-admin-post',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			//'rewrite'             => ['slug'=>'noi-that'],
			'rewrite'             => false,
			'capability_type'     => 'estimate',
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
		//register_post_type( 'estimate', $args );

		$labels = array(
			'name'               => 'Xây dựng',
			'singular_name'      => 'Xây dựng',
			'add_new'            => 'Thêm mới Xây dựng',
			'add_new_item'       => 'Thêm mới Xây dựng',
			'edit_item'          => 'Sửa Xây dựng',
			'new_item'           => 'Xây dựng mới',
			'view_item'          => 'Xem Xây dựng',
			'search_items'       => 'Tìm Xây dựng',
			'not_found'          => 'Không có Xây dựng nào',
			'not_found_in_trash' => 'Không có Xây dựng nào trong Thùng rác',
			'parent_item_colon'  => 'Xây dựng cấp trên:',
			'menu_name'          => 'Xây dựng (bỏ)',
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
			'menu_icon'           => 'dashicons-admin-post',
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
			'name'               => 'Đồ gỗ',
			'singular_name'      => 'Đồ gỗ',
			'add_new'            => 'Thêm mới Đồ gỗ',
			'add_new_item'       => 'Thêm mới Đồ gỗ',
			'edit_item'          => 'Sửa Đồ gỗ',
			'new_item'           => 'Đồ gỗ mới',
			'view_item'          => 'Xem Đồ gỗ',
			'search_items'       => 'Tìm Đồ gỗ',
			'not_found'          => 'Không có Đồ gỗ nào',
			'not_found_in_trash' => 'Không có Đồ gỗ nào trong Thùng rác',
			'parent_item_colon'  => 'Đồ gỗ cấp trên:',
			'menu_name'          => 'Đồ gỗ (bỏ)',
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
			'menu_icon'           => 'dashicons-admin-post',
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
			'name'               => 'Đối tác',
			'singular_name'      => 'Đối tác',
			'add_new'            => 'Thêm mới Đối tác',
			'add_new_item'       => 'Thêm mới Đối tác',
			'edit_item'          => 'Sửa Đối tác',
			'new_item'           => 'Đối tác mới',
			'view_item'          => 'Xem Đối tác',
			'search_items'       => 'Tìm Đối tác',
			'not_found'          => 'Không có Đối tác nào',
			'not_found_in_trash' => 'Không có Đối tác nào trong Thùng rác',
			'parent_item_colon'  => 'Đối tác cấp trên:',
			'menu_name'          => 'Đối tác',
		);
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			//'description'         => 'description',
			//'taxonomies'          => array('contractor_cat'),
			'public'              => false,
			'show_ui'             => false,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-admin-post',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false, // ẩn bài viết ở front-end
			'exclude_from_search' => true, // loại khỏi kết quả tìm kiếm
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'partner',
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
		//register_post_type( 'partner', $args );

		$labels = array(
			'name'               => 'Hồ sơ',
			'singular_name'      => 'Hồ sơ',
			'add_new'            => 'Thêm mới Hồ sơ',
			'add_new_item'       => 'Thêm mới Hồ sơ',
			'edit_item'          => 'Sửa Hồ sơ',
			'new_item'           => 'Hồ sơ mới',
			'view_item'          => 'Xem Hồ sơ',
			'search_items'       => 'Tìm Hồ sơ',
			'not_found'          => 'Không có Hồ sơ nào',
			'not_found_in_trash' => 'Không có Hồ sơ nào trong Thùng rác',
			'parent_item_colon'  => 'Hồ sơ cấp trên:',
			'menu_name'          => 'Hồ sơ',
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
			'menu_icon'           => 'dashicons-admin-post',
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
			'menu_icon'           => 'dashicons-admin-post',
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

		//debug_log($submenu);

		// if ( isset( $submenu['edit.php'] ) ) {
			
			// $submenu['edit.php'][5][0] = 'Xem tất cả';
			// $submenu['edit.php'][10][0] = 'Tạo Sản phẩm mới';
		// }

		add_menu_page( 'Chủ đầu tư', 'Chủ đầu tư', 'manage_passwordss', 'edit-tags.php?taxonomy=passwords', null, 'dashicons-businessperson', 2 );
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
			'name'              => 'Nhóm dự toán',
			'singular_name'     => 'Nhóm dự toán',
			'search_items'      => 'Tìm Nhóm dự toán',
			'all_items'         => 'Tất cả Nhóm dự toán',
			'edit_item'         => 'Sửa Nhóm dự toán',
			'update_item'       => 'Cập nhật Nhóm dự toán',
			'add_new_item'      => 'Thêm Nhóm dự toán mới',
			'new_item_name'     => 'Nhóm dự toán mới',
			'menu_name'         => 'Nhóm dự toán',
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
				'manage_terms' => 'manage_estimate_cats',
				'edit_terms'   => 'edit_estimate_cats',
				'delete_terms' => 'delete_estimate_cats',
				'assign_terms' => 'edit_estimates',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'estimate_cat', 'estimate', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm Xây dựng',
			'singular_name'     => 'Nhóm Xây dựng',
			'search_items'      => 'Tìm Nhóm Xây dựng',
			'all_items'         => 'Tất cả Nhóm Xây dựng',
			'edit_item'         => 'Sửa Nhóm Xây dựng',
			'update_item'       => 'Cập nhật Nhóm Xây dựng',
			'add_new_item'      => 'Thêm Nhóm Xây dựng mới',
			'new_item_name'     => 'Nhóm Xây dựng mới',
			'menu_name'         => 'Nhóm Xây dựng',
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
			'name'              => 'Nhóm Đồ gỗ',
			'singular_name'     => 'Nhóm Đồ gỗ',
			'search_items'      => 'Tìm Nhóm Đồ gỗ',
			'all_items'         => 'Tất cả Nhóm Đồ gỗ',
			'edit_item'         => 'Sửa Nhóm Đồ gỗ',
			'update_item'       => 'Cập nhật Nhóm Đồ gỗ',
			'add_new_item'      => 'Thêm Nhóm Đồ gỗ mới',
			'new_item_name'     => 'Nhóm Đồ gỗ mới',
			'menu_name'         => 'Nhóm Đồ gỗ',
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
			'name'              => 'Nhóm đối tác',
			'singular_name'     => 'Nhóm đối tác',
			'search_items'      => 'Tìm Nhóm đối tác',
			'all_items'         => 'Tất cả Nhóm đối tác',
			'edit_item'         => 'Sửa Nhóm đối tác',
			'update_item'       => 'Cập nhật Nhóm đối tác',
			'add_new_item'      => 'Thêm Nhóm đối tác mới',
			'new_item_name'     => 'Nhóm đối tác mới',
			'menu_name'         => 'Nhóm đối tác',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => [
				'manage_terms' => 'manage_partner_cats',
				'edit_terms'   => 'edit_partner_cats',
				'delete_terms' => 'delete_partner_cats',
				'assign_terms' => 'edit_partners',
			],
			'public' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
		);
		register_taxonomy( 'partner_cat', 'partner', $args ); // our new 'format' taxonomy

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Nhóm tài liệu',
			'singular_name'     => 'Nhóm tài liệu',
			'search_items'      => 'Tìm Nhóm tài liệu',
			'all_items'         => 'Tất cả Nhóm tài liệu',
			'edit_item'         => 'Sửa Nhóm tài liệu',
			'update_item'       => 'Cập nhật Nhóm tài liệu',
			'add_new_item'      => 'Thêm Nhóm tài liệu mới',
			'new_item_name'     => 'Nhóm tài liệu mới',
			'menu_name'         => 'Nhóm tài liệu',
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
			'name'              => 'Loại hợp đồng',
			'singular_name'     => 'Loại hợp đồng',
			'search_items'      => 'Tìm Loại hợp đồng',
			'all_items'         => 'Tất cả Loại hợp đồng',
			'edit_item'         => 'Sửa Loại hợp đồng',
			'update_item'       => 'Cập nhật Loại hợp đồng',
			'add_new_item'      => 'Thêm Loại hợp đồng mới',
			'new_item_name'     => 'Loại hợp đồng mới',
			'menu_name'         => 'Loại hợp đồng',
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
	}
	
	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}

Custom_Types::instance();