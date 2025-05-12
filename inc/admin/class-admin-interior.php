<?php
namespace HomeViet;

class Admin_Interior {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {

			//add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			// if(unyson_exists()) {
			// 	add_action( 'fw_save_post_options', [$this, 'save_interior_15'], 15, 3 );
			// } else {
			// 	add_action( 'save_post_interior', [$this, 'save_interior_15'], 15, 3 );
			// }

			add_action( 'manage_interior_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			add_filter( 'manage_interior_posts_columns', [ $this, 'add_custom_columns_header' ] );

		}

	}

	public function custom_columns_value($column, $post_id) {
		switch ($column) {
			case 'link':
				$post = get_post($post_id);
				echo esc_url($post->post_excerpt);
				break;
		}
	}

	public function add_custom_columns_header($columns) {
		
		$columns['link'] = 'Link dự toán';
		
		return $columns;
	}

	public static function check_interior_exists($phone_number, $id=0) {
		$args = [
			'post_type'=>'interior',
			'posts_per_page'=>1,
			'fields'=>'ids',
			'meta_key'=>'_phone_number',
			'meta_value'=>$phone_number,
			'post_status'=>['publish']
		];
		if($id>0) {
			$args['post__not_in'] = [$id];
		}

		$check = new \WP_Query( $args );

		if($check->have_posts()) {
			return true;
		}

		return false;
	}

	public function save_interior_15($post_id, $post, $update) {
		if ($post->post_type!='interior') return;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

	}

	public function enqueue_scripts($hook) {
		global $post_type;
		if($hook=='post.php' && $post_type=='interior') {
			wp_enqueue_style( 'interior', THEME_URI.'/assets/css/admin-interior.css', [], '' );
			wp_enqueue_script( 'interior', THEME_URI.'/assets/js/admin-interior.js', array('jquery'), '', false );
			
		}
	}

	public function disable_months_dropdown($disabled, $post_type) {
		if($post_type=='interior') {
			$disabled = true;
		}
		return $disabled;
	}


	public function post_content_editor($post) {
		wp_editor( self::unescape($post->post_content), 'content', [
			'tinymce' => true,
			'textarea_rows' => 15,
		] );
	}

	public function switch_boxes() {

		remove_meta_box(
			'slugdiv',
			'interior',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'interior',
			'side'
		);

		remove_meta_box(
			'passwordsdiv',
			'interior',
			'side'
		);

		remove_meta_box(
            'postexcerpt' // ID
        ,   'interior'            // Screen, empty to support all post types
        ,   'normal'      // Context
        );

        add_meta_box(
            'postexcerpt2'     // Reusing just 'postexcerpt' doesn't work.
        ,   __( 'Excerpt' )    // Title
        ,   array ( $this, 'postexcerpt2' ) // Display function
        ,   'interior'              // Screen, we use all screens with meta boxes.
        ,   'normal'          // Context
        ,   'core'            // Priority
        );
	}

	public function postexcerpt2( $post ) {
    ?>
        <label class="screen-reader-text" for="excerpt"><?php
        _e( 'Excerpt' )
        ?></label>
        <textarea id="excerpt" name="excerpt"><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>
        <?php
    }


	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Interior::instance();