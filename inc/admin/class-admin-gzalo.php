<?php
namespace HomeViet;

class Admin_Gzalo {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			add_action( 'manage_gzalo_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			add_filter( 'manage_gzalo_posts_columns', [ $this, 'add_custom_columns_header' ] );

			add_action( 'edit_form_before_permalink', [$this, 'thumbnail_title_field'] );

			if(unyson_exists()) {
				add_action( 'fw_save_post_options', [$this, 'save_gzalo_15'], 15, 2 );
			} else {
				add_action( 'save_post_gzalo', [$this, 'save_gzalo_15'], 15, 2 );
			}

		}

	}

	public function save_gzalo_15($post_id, $post) {
		if ($post->post_type!='gzalo') return;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_gzalo', $post_id ) ) {
			return;
		}

		if(isset($_POST['thumbnail_title'])) {
			$thumbnail_title = sanitize_textarea_field($_POST['thumbnail_title']);
			update_post_meta( $post_id, '_thumbnail_title', $thumbnail_title );
		}

	}

	public function thumbnail_title_field($post) {
		if($post->post_type=='gzalo') {
			$thumbnail_title = get_post_meta( $post->ID, '_thumbnail_title', true );
			?>
			<p><strong>TIÊU ĐỀ ẢNH THUMBNAIL</strong><textarea name="thumbnail_title" rows="3" class="large-text code" style="width: 100%;"><?=esc_attr($thumbnail_title)?></textarea><br><i>Sẽ hiển thị thay thế khi không có ảnh thumbnail</i></p>
			<?php
		}
	}

	public function custom_columns_value($column, $post_id) {
		switch ($column) {
			case 'gurl':
				echo esc_html(fw_get_db_post_option($post_id, 'gzalo_zalo'));
				break;
		}
	}

	public function add_custom_columns_header($columns) {
		
		$columns['gurl'] = 'Link nhóm zalo';
		
		return $columns;
	}

	public function disable_months_dropdown($disabled, $post_type) {
		if($post_type=='gzalo') {
			$disabled = true;
		}
		return $disabled;
	}


	public function switch_boxes() {

		remove_meta_box(
			'slugdiv',
			'gzalo',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'gzalo',
			'side'
		);

		remove_meta_box(
			'passwordsdiv',
			'gzalo',
			'side'
		);

	}



	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Gzalo::instance();