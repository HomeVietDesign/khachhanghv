<?php
namespace HomeViet;

class Admin_Rebate {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {

			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
			
			add_action( 'add_meta_boxes', [$this, 'switch_boxes'] );
			
			add_filter( 'disable_months_dropdown', [$this, 'disable_months_dropdown'], 10, 2 );

			add_action( 'manage_rebate_posts_custom_column', [ $this, 'custom_columns_value' ], 2, 2 );
			add_filter( 'manage_rebate_posts_columns', [ $this, 'add_custom_columns_header' ] );

			add_action( 'edit_form_before_permalink', [$this, 'thumbnail_title_field'] );

			if(unyson_exists()) {
				add_action( 'fw_save_post_options', [$this, 'save_rebate_15'], 15, 2 );
			} else {
				add_action( 'save_post_rebate', [$this, 'save_rebate_15'], 15, 2 );
			}

		}

	}

	public function save_rebate_15($post_id, $post) {
		if ($post->post_type!='rebate') return;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_rebate', $post_id ) ) {
			return;
		}

		if(isset($_POST['thumbnail_title'])) {
			$thumbnail_title = sanitize_textarea_field($_POST['thumbnail_title']);
			update_post_meta( $post_id, '_thumbnail_title', $thumbnail_title );
		}

	}

	public function thumbnail_title_field($post) {
		if($post->post_type=='rebate') {
			$thumbnail_title = get_post_meta( $post->ID, '_thumbnail_title', true );
			?>
			<p><strong>TIÊU ĐỀ ẢNH THUMBNAIL</strong><textarea name="thumbnail_title" rows="3" class="large-text code" style="width: 100%;"><?=esc_attr($thumbnail_title)?></textarea><br><i>Sẽ hiển thị thay thế khi không có ảnh thumbnail</i></p>
			<?php
		}
	}

	public function custom_columns_value($column, $post_id) {
		switch ($column) {
			case 'gurl':
				echo esc_html(fw_get_db_post_option($post_id, 'rebate_zalo'));
				break;

			case 'thumbnail_title':
				echo esc_html(get_post_meta($post_id, '_thumbnail_title', true));
				break;
		}
	}

	public function add_custom_columns_header($columns) {

		if(isset($columns['date'])) unset($columns['date']);
		
		//$columns['gurl'] = 'Link nhóm zalo';
		$columns['thumbnail_title'] = 'Tiêu đề thumbnail';
		
		return $columns;
	}

	public function disable_months_dropdown($disabled, $post_type) {
		if($post_type=='rebate') {
			$disabled = true;
		}
		return $disabled;
	}


	public function switch_boxes() {

		remove_meta_box(
			'slugdiv',
			'rebate',
			'normal'
		);

		remove_meta_box(
			'pageparentdiv',
			'rebate',
			'side'
		);

		remove_meta_box(
			'tagsdiv-product',
			'rebate',
			'side'
		);

	}

	public function enqueue_scripts($hook) {
		global $post_type;
		if(($hook=='post.php' || $hook=='edit.php') && $post_type=='rebate') {
			wp_enqueue_style( 'rebate', THEME_URI.'/assets/css/admin-rebate.css', [], '' );
			//wp_enqueue_script( 'rebate', THEME_URI.'/assets/js/admin-rebate.js', array('jquery'), '', false );
		}
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Rebate::instance();