<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Media extends FW_Shortcode
{
	
	public function _init()
	{
		add_action( 'wp_footer', [$this, 'edit_modal'] );
		add_action( 'wp_ajax_get_edit_media_form', [$this, 'ajax_get_edit_media_form']);
		add_action( 'wp_ajax_update_media', [$this, 'ajax_update_media']);
		add_action( 'wp_ajax_get_media_info', [$this, 'ajax_get_media_info']);
	}

	public function ajax_get_media_info() {

		$media = isset($_GET['media'])?absint($_GET['media']):0;

		$response = [
			'web' => '',
			'fb' => '',
			'last_date' => '',
			'end_date' => '',
		];
		
		if($media) {
			$media_web = fw_get_db_post_option($media,'web');
			$media_fb = fw_get_db_post_option($media,'fb');
			$media_last_date = fw_get_db_post_option($media,'last_date');
			$media_end_date = fw_get_db_post_option($media,'end_date');

			$response['web'] = ($media_web)?'<a href="'.esc_url($media_web).'" class="d-block w-100 py-2 d-flex justify-content-center align-items-center text-yellow" target="_blank">Web</a>':'';
			$response['fb'] = ($media_fb)?'<a href="'.esc_url($media_fb).'" class="d-block w-100 py-2 d-flex justify-content-center align-items-center text-yellow" target="_blank">Face</a>':'';
			
			$response['last_date'] = ($media_last_date!='')?'<span class="d-block w-100 py-2 d-flex justify-content-center align-items-center text-yellow">'.esc_html(date('d/m/y', strtotime($media_last_date))).'</span>':'';
			$response['end_date'] = ($media_end_date!='')?'<span class="d-block w-100 py-2 d-flex justify-content-center align-items-center text-yellow">'.esc_html(date('d/m/y', strtotime($media_end_date))).'</span>':'';
			
		}
		
		wp_send_json($response);
	}

	public function ajax_update_media() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('media_edit') && check_ajax_referer( 'edit-media', 'nonce', false )) {
			$media_id = isset($_POST['media'])?absint($_POST['media']):0;

			$media_web = isset($_POST['media_web'])?sanitize_url($_POST['media_web']):'';
			$media_fb = isset($_POST['media_fb'])?sanitize_url($_POST['media_fb']):'';
			$media_last_date = isset($_POST['media_last_date']) ? $_POST['media_last_date'] : '';
			$media_end_date = isset($_POST['media_end_date']) ? $_POST['media_end_date'] : '';
			
			if($media_id) {
				
				fw_set_db_post_option($media_id, 'web', $media_web);
				fw_set_db_post_option($media_id, 'fb', $media_fb);
				fw_set_db_post_option($media_id, 'last_date', $media_last_date);
				fw_set_db_post_option($media_id, 'end_date', $media_end_date);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_media_form() {

		$media = isset($_GET['media'])?absint($_GET['media']):0;

		if($media) {
			$web = fw_get_db_post_option($media, 'web', '');
			$fb = fw_get_db_post_option($media, 'fb', '');
			$last_date = fw_get_db_post_option($media, 'last_date', '');
			$end_date = fw_get_db_post_option($media, 'end_date', '');
			
			?>
			<form id="frm-edit-media" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="media" name="media" value="<?=$media?>">
				<?php wp_nonce_field( 'edit-media', 'nonce' ); ?>
				<div id="edit-media-response"></div>
				
				<div class="mb-3">
					<input type="text" id="media_web" name="media_web" placeholder="Link Web" class="form-control" value="<?php echo esc_url($web); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="media_fb" name="media_fb" placeholder="Link facebook" class="form-control" value="<?php echo esc_url($fb); ?>">
				</div>
				<div class="mb-3">
					Ngày save cũ nhất
					<input class="form-control" type="date" value="<?php echo (isset($last_date)&&$last_date!='')?esc_html(date('Y-m-d', strtotime($last_date))):''; ?>" name="media_last_date" id="media_last_date">
				</div>
				<div class="mb-3">
					Ngày save mới nhất
					<input class="form-control" type="date" value="<?php echo (isset($end_date)&&$end_date!='')?esc_html(date('Y-m-d', strtotime($end_date))):''; ?>" name="media_end_date" id="media_end_date">
				</div>
				
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-media-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-medias" tabindex="-1" role="dialog" aria-labelledby="edit-medias-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-medias-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
