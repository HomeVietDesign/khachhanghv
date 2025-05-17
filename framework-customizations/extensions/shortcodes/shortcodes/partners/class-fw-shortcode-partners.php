<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Partners extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_partner_form', [$this, 'ajax_get_edit_partner_form']);
      add_action( 'wp_ajax_update_partner', [$this, 'ajax_update_partner']);
      add_action( 'wp_ajax_get_partner_info', [$this, 'ajax_get_partner_info']);
	}

	public function ajax_get_partner_info() {
		global $current_password;
		$default_term_password = get_option( 'default_term_passwords', -1 );

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$partner_id = isset($_GET['partner'])?absint($_GET['partner']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];
		
		if($client && $partner_id) {
			$default_attachment = fw_get_db_post_option($partner_id,'estimate_attachment');
			$default_data = [
				'value' => fw_get_db_post_option($partner_id,'estimate_value'),
				'unit' => fw_get_db_post_option($partner_id,'estimate_unit'),
				'zalo' => fw_get_db_post_option($partner_id,'estimate_zalo'),
				'attachment_id' => ($default_attachment) ? $default_attachment['attachment_id']:''
			];

			$data = get_post_meta($partner_id, '_data', true);
			$partner_data = isset($data[$client])?$data[$client]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

			if(empty($partner_data['value'])) $partner_data['value'] = $default_data['value'];
			if(empty($partner_data['unit'])) $partner_data['unit'] = $default_data['unit'];
			if(empty($partner_data['zalo'])) $partner_data['zalo'] = $default_data['zalo'];
			if(empty($partner_data['attachment_id'])) $partner_data['attachment_id'] = $default_data['attachment_id'];

			$phone_number = get_post_field( 'post_excerpt', $partner_id );

			$response['zalo'] = ($partner_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($partner_data['zalo']).'" target="_blank">Zalo</a>':'';

			ob_start();
		?>
			<div class="partner-title pt-3 mb-1 fs-5">
				<span class="d-block text-truncate" title="<?php echo esc_attr(get_the_title( $partner_id )); ?>"><?php echo esc_html(get_the_title( $partner_id )); ?></span>
			</div>
			<?php if($partner_data['value']!='') { ?>
			<div class="partner-value mb-1">
				<span>Tổng giá trị: </span>
				<span class="text-red fw-bold"><?php echo esc_html($partner_data['value']); ?></span><span class="text-red"> <?php echo esc_html($partner_data['unit']); ?></span>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center partner-links mb-3">
				<?php
				if($phone_number) {
					?>
					<a class="btn btn-sm btn-danger my-1 mx-2" href="tel:<?=esc_attr($phone_number)?>"><?=esc_html($phone_number)?></a>
					<?php
				}

				if($partner_data['attachment_id']>0) {
					$attachment_url = wp_get_attachment_url($partner_data['attachment_id']);
					if($attachment_url) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($attachment_url)?>" target="_blank">Xem chi tiết</a>
					<?php
					}
				}
				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_partner() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(has_role('administrator') && check_ajax_referer( 'edit-partner', 'nonce', false )) {
			$partner_client = isset($_POST['partner_client'])?absint($_POST['partner_client']):0;
			$partner_id = isset($_POST['partner_id'])?absint($_POST['partner_id']):0;
			$partner_attachment_id = isset($_POST['partner_attachment_id'])?absint($_POST['partner_attachment_id']):'';
			$partner_value = isset($_POST['partner_value'])?sanitize_text_field($_POST['partner_value']):'';
			$partner_unit = isset($_POST['partner_unit'])?sanitize_text_field($_POST['partner_unit']):'';
			$partner_zalo = isset($_POST['partner_zalo'])?sanitize_text_field($_POST['partner_zalo']):'';
			$partner_attachment = isset($_FILES['partner_attachment']) ? $_FILES['partner_attachment'] : null;

			if($partner_client && $partner_id) {
				$data = get_post_meta($partner_id, '_data', true);
				if(empty($data)) $data = [];
				$partner_data = isset($data[$partner_client])?$data[$partner_client]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

				$new_partner_data = [
					'value' => $partner_value,
					'unit' => $partner_unit,
					'zalo' => $partner_zalo,
					'attachment_id' => $partner_attachment_id
				];

				// tải lên file dự toán
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					require_once(ABSPATH . "wp-admin" . '/includes/file.php');
					require_once(ABSPATH . "wp-admin" . '/includes/media.php');
				}

				$partner_attachment_upload = media_handle_upload( 'partner_attachment', $partner_id );

				if ($partner_attachment['error']==0 && $partner_attachment_upload && ! is_array( $partner_attachment_upload ) ) {
					$new_partner_data['attachment_id'] = $partner_attachment_upload;
					if($partner_attachment_id) wp_delete_attachment($partner_attachment_id, true);
				}

				if($new_partner_data['attachment_id']=='' || $new_partner_data['attachment_id']==0) {
					if($partner_data['attachment_id']) wp_delete_attachment($partner_data['attachment_id'], true);
				}

				$data[$partner_client] = $new_partner_data;

				update_post_meta( $partner_id, '_data', $data );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_partner_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$partner = isset($_GET['partner'])?absint($_GET['partner']):0;

		if($client && $partner) {
			$data = get_post_meta($partner, '_data', true);
			$partner_data = isset($data[$client])?$data[$client]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

			$attachment_url = ($partner_data['attachment_id'])?wp_get_attachment_url($partner_data['attachment_id']):'';
			?>
			<form id="frm-edit-partner" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="partner_client" name="partner_client" value="<?=$client?>">
				<input type="hidden" id="partner_id" name="partner_id" value="<?=$partner?>">
				<?php wp_nonce_field( 'edit-partner', 'nonce' ); ?>
				<div id="edit-partner-response"></div>
				<div class="mb-3">
					<input type="text" id="partner_value" name="partner_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($partner_data['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="partner_unit" name="partner_unit" placeholder="Đơn vị" class="form-control" value="<?php echo esc_attr($partner_data['unit']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="partner_zalo" name="partner_zalo" placeholder="URL nhóm zalo" class="form-control" value="<?php echo esc_attr($partner_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dữ liệu</div>
					<input type="hidden" id="partner_attachment_id" name="partner_attachment_id" value="<?=esc_attr($partner_data['attachment_id'])?>">
					<?php if($attachment_url) { ?>
					<div class="mb-2">
						<span class="overflow-hidden"><?=esc_html(basename($attachment_url))?></span>
						<button class="btn btn-sm btn-danger" id="partner_remove_attachment">Xóa file</button>
					</div>
					<?php } ?>
					<label class="d-block" for="partner_attachment">
						<span class="input-group">
							<span class="form-control overflow-hidden"></span>
							<span class="input-group-text">Bấm tải lên</span>
						</span>
						<div style="width: 0;height: 0;overflow: hidden;">
							<input type="file" id="partner_attachment" name="partner_attachment" accept=".pdf" class="form-control">
						</div>
					</label>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-partner-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-partner" tabindex="-1" role="dialog" aria-labelledby="edit-partner-label">
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-partner-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
