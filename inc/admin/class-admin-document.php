<?php
namespace HomeViet;

class Admin_Document {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			// add_action( 'manage_document_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			// add_filter( 'manage_document_posts_columns', [ $this, 'add_custom_columns_header' ] );

			add_action( 'edit_form_before_permalink', [$this, 'thumbnail_title_field'] );

			if(unyson_exists()) {
				add_action( 'fw_save_post_options', [$this, 'save_document_15'], 15, 2 );
			} else {
				add_action( 'save_post_document', [$this, 'save_document_15'], 15, 2 );
			}

		}

	}

	public function save_document_15($post_id, $post) {
		if ($post->post_type!='document') return;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_document', $post_id ) ) {
			return;
		}

		if(isset($_POST['thumbnail_title'])) {
			$thumbnail_title = sanitize_textarea_field($_POST['thumbnail_title']);
			update_post_meta( $post_id, '_thumbnail_title', $thumbnail_title );
		}

	}

	public function thumbnail_title_field($post) {
		if($post->post_type=='document') {
			$thumbnail_title = get_post_meta( $post->ID, '_thumbnail_title', true );
			?>
			<p><strong>TIÊU ĐỀ ẢNH THUMBNAIL</strong><textarea name="thumbnail_title" rows="3" class="large-text code" style="width: 100%;"><?=esc_attr($thumbnail_title)?></textarea><br><i>Sẽ hiển thị thay thế khi không có ảnh thumbnail</i></p>
			<?php
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
		if($post_type=='document') {
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
			'document',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'document',
			'side'
		);

		remove_meta_box(
			'passwordsdiv',
			'document',
			'side'
		);
		/*
		remove_meta_box(
            'postexcerpt' // ID
        ,   'document'            // Screen, empty to support all post types
        ,   'normal'      // Context
        );

        add_meta_box(
            'postexcerpt2'     // Reusing just 'postexcerpt' doesn't work.
        ,   'Số điện thoại'    // Title
        ,   array ( $this, 'postexcerpt2' ) // Display function
        ,   'document'              // Screen, we use all screens with meta boxes.
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

Admin_Document::instance();