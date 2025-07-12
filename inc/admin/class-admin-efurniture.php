<?php
namespace HomeViet;

class Admin_Efurniture {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			// add_action( 'manage_efurniture_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			// add_filter( 'manage_efurniture_posts_columns', [ $this, 'add_custom_columns_header' ] );

		}

	}

	public function custom_columns_value($column, $post_id) {
		switch ($column) {
			case 'phone_number':
				$post = get_post($post_id);
				echo esc_html($post->post_excerpt);
				break;
		}
	}

	public function add_custom_columns_header($columns) {
		
		$columns['phone_number'] = 'Số điện thoại';
		
		return $columns;
	}

	public function disable_months_dropdown($disabled, $post_type) {
		if($post_type=='efurniture') {
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
			'efurniture',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'efurniture',
			'side'
		);

		remove_meta_box(
			'passwordsdiv',
			'efurniture',
			'side'
		);
		/*
		remove_meta_box(
            'postexcerpt' // ID
        ,   'efurniture'            // Screen, empty to support all post types
        ,   'normal'      // Context
        );

        add_meta_box(
            'postexcerpt2'     // Reusing just 'postexcerpt' doesn't work.
        ,   'Số điện thoại'    // Title
        ,   array ( $this, 'postexcerpt2' ) // Display function
        ,   'efurniture'              // Screen, we use all screens with meta boxes.
        ,   'normal'          // Context
        ,   'core'            // Priority
        );
        */
	}

	public function postexcerpt2( $post ) {
    ?>
        <label class="screen-reader-text" for="excerpt">Số điện thoại</label>
        <input type="text" name="excerpt" value="<?php echo esc_attr( $post->post_excerpt ); ?>">
        <?php
    }


	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Efurniture::instance();