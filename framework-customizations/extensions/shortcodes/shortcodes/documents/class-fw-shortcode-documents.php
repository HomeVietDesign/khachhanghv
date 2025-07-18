<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Documents extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_document_form', [$this, 'ajax_get_edit_document_form']);
      add_action( 'wp_ajax_update_document', [$this, 'ajax_update_document']);
      add_action( 'wp_ajax_get_document_info', [$this, 'ajax_get_document_info']);
      add_action( 'wp_ajax_document_hide', [$this, 'ajax_document_hide']);
	}

	public function ajax_document_hide() {
		global $current_client;
		$doc_id = isset($_POST['doc']) ? absint($_POST['doc']) : 0;
		$response = false;
		if(current_user_can('document_edit') && $current_client && $doc_id && check_ajax_referer( 'global', 'nonce', false )) {
			$document_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'document_hide', []);
			$document_hide[] = $doc_id;
			fw_set_db_term_option($current_client->term_id, 'passwords', 'document_hide', $document_hide);
			$response = true;
		}
		wp_send_json($response);
	}

	public function ajax_get_document_info() {
		
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$document_id = isset($_GET['document'])?absint($_GET['document']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];
		
		if($client && $document_id) {
			$default_attachment = fw_get_db_post_option($document_id,'document_attachment');
			$default_data = [
				'value' => fw_get_db_post_option($document_id,'document_value'),
				'unit' => fw_get_db_post_option($document_id,'document_unit'),
				'zalo' => fw_get_db_post_option($document_id,'document_zalo'),
				'attachment_id' => ($default_attachment) ? $default_attachment['attachment_id']:''
			];

			$data = get_post_meta($document_id, '_data', true);
			$document_data = isset($data[$client])?$data[$client]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

			if(empty($document_data['value'])) $document_data['value'] = $default_data['value'];
			if(empty($document_data['unit'])) $document_data['unit'] = $default_data['unit'];
			if(empty($document_data['zalo'])) $document_data['zalo'] = $default_data['zalo'];
			if(empty($document_data['attachment_id'])) $document_data['attachment_id'] = $default_data['attachment_id'];

			$response['zalo'] = ($document_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($document_data['zalo']).'" target="_blank">Zalo</a>':'';
			$response['required'] = (isset($document_data['required']) && $document_data['required']!='')?'<div class="bg-danger" title="Ngày gửi yêu cầu">'.esc_html(date('d/m', strtotime($document_data['required']))).'</div>':'';
			$response['created'] = (isset($document_data['created']) && $document_data['created']!='')?'<div class="bg-danger" title="Ngày tạo hợp đồng">'.esc_html(date('d/m', strtotime($document_data['created']))).'</div>':'';
			$response['completed'] = (isset($document_data['completed']) && $document_data['completed']!='')?'<div class="bg-danger" title="Ngày làm xong hợp đồng">'.esc_html(date('d/m', strtotime($document_data['completed']))).'</div>':'';
			$response['sent'] = (isset($document_data['sent']) && $document_data['sent']!='')?'<div class="bg-danger" title="Ngày gửi cho khách">'.esc_html(date('d/m', strtotime($document_data['sent']))).'</div>':'';

			$response['selected'] = (isset($document_data['selected']) && $document_data['selected']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã ký"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
		?>
			<div class="document-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $document_id )); ?>
			</div>
			<?php if($document_data['value']!='' || $document_data['unit']!='') { ?>
			<div class="document-value mb-1">
				<?php if($document_data['value']!='') { ?>
				<div>
					<span>Tổng giá trị: </span>
					<span class="text-red fw-bold"><?php echo esc_html($document_data['value']); ?></span>
				</div>
				<?php } ?>
				<?php if($document_data['unit']!='') { ?>
				<div class="text-red"><?php echo esc_html($document_data['unit']); ?></div>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center document-links mb-3">
				<?php
				if($document_data['attachment_id']) {
					$attachment_url = wp_get_attachment_url($document_data['attachment_id']);
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

	public function ajax_update_document() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('document_edit') && check_ajax_referer( 'edit-document', 'nonce', false )) {
			$document_client = isset($_POST['document_client'])?absint($_POST['document_client']):0;
			$document_id = isset($_POST['document_id'])?absint($_POST['document_id']):0;
			$document_attachment_id = isset($_POST['document_attachment_id'])?absint($_POST['document_attachment_id']):'';
			$document_value = isset($_POST['document_value'])?sanitize_text_field($_POST['document_value']):'';
			$document_unit = isset($_POST['document_unit'])?sanitize_text_field($_POST['document_unit']):'';
			$document_zalo = isset($_POST['document_zalo'])?sanitize_text_field($_POST['document_zalo']):'';
			$document_attachment = isset($_FILES['document_attachment']) ? $_FILES['document_attachment'] : null;

			$document_required = isset($_POST['document_required']) ? $_POST['document_required'] : '';
			$document_created = isset($_POST['document_created']) ? $_POST['document_created'] : '';
			$document_completed = isset($_POST['document_completed']) ? $_POST['document_completed'] : '';
			$document_sent = isset($_POST['document_sent']) ? $_POST['document_sent'] : '';
			$document_selected = isset($_POST['document_selected']) ? $_POST['document_selected'] : '';

			if($document_client && $document_id) {
				$data = get_post_meta($document_id, '_data', true);
				if(empty($data)) $data = [];
				$document_data = isset($data[$document_client])?$data[$document_client]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

				$new_document_data = [
					'required' => $document_required,
					'created' => $document_created,
					'completed' => $document_completed,
					'sent' => $document_sent,
					'value' => $document_value,
					'unit' => $document_unit,
					'zalo' => $document_zalo,
					'attachment_id' => $document_attachment_id,
					'selected' => $document_selected,
				];

				// tải lên file dự toán
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					require_once(ABSPATH . "wp-admin" . '/includes/file.php');
					require_once(ABSPATH . "wp-admin" . '/includes/media.php');
				}

				$document_attachment_upload = media_handle_upload( 'document_attachment', $document_id );

				if ($document_attachment['error']==0 && $document_attachment_upload && ! is_array( $document_attachment_upload ) ) {
					$new_document_data['attachment_id'] = $document_attachment_upload;
					if($document_attachment_id) wp_delete_attachment($document_attachment_id, true);
				}

				if($new_document_data['attachment_id']=='' || $new_document_data['attachment_id']==0) {
					if($document_data['attachment_id']) wp_delete_attachment($document_data['attachment_id'], true);
				}

				$data[$document_client] = $new_document_data;

				update_post_meta( $document_id, '_data', $data );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_document_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$document = isset($_GET['document'])?absint($_GET['document']):0;

		if($client && $document) {
			$data = get_post_meta($document, '_data', true);
			$document_data = isset($data[$client])?$data[$client]:['required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>'', 'selected'=>''];

			$attachment_url = ($document_data['attachment_id'])?wp_get_attachment_url($document_data['attachment_id']):'';
			?>
			<form id="frm-edit-document" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="document_client" name="document_client" value="<?=$client?>">
				<input type="hidden" id="document_id" name="document_id" value="<?=$document?>">
				<?php wp_nonce_field( 'edit-document', 'nonce' ); ?>
				<div id="edit-document-response"></div>
				<div class="mb-3<?php echo (!current_user_can('edit_documents'))?' hidden':''; ?>">
					Gửi yêu cầu
					<input class="form-control" type="date" value="<?php echo (isset($document_data['required'])&&$document_data['required']!='')?esc_html(date('Y-m-d', strtotime($document_data['required']))):''; ?>" name="document_required" id="document_required">
				</div>
				<div class="mb-3">
					Ngày bắt đầu
					<input class="form-control" type="date" value="<?php echo (isset($document_data['created'])&&$document_data['created']!='')?esc_html(date('Y-m-d', strtotime($document_data['created']))):''; ?>" name="document_created" id="document_created">
				</div>
				<div class="mb-3">
					Ngày làm xong
					<input class="form-control" type="date" value="<?php echo (isset($document_data['completed'])&&$document_data['completed']!='')?esc_html(date('Y-m-d', strtotime($document_data['completed']))):''; ?>" name="document_completed" id="document_completed">
				</div>
				<div class="mb-3">
					Ngày gửi khách
					<input class="form-control" type="date" value="<?php echo (isset($document_data['sent'])&&$document_data['sent']!='')?esc_html(date('Y-m-d', strtotime($document_data['sent']))):''; ?>" name="document_sent" id="document_sent">
				</div>
				<div class="mb-3">
					<input type="text" id="document_value" name="document_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($document_data['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="document_unit" name="document_unit" placeholder="Đơn vị" class="form-control" value="<?php echo esc_attr($document_data['unit']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="document_zalo" name="document_zalo" placeholder="URL nhóm zalo" class="form-control" value="<?php echo esc_attr($document_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dữ liệu</div>
					<div class="row row-cols-2 g-0 p-2 border rounded-2">
						<div class="col attachment-uploaded">
							<input type="hidden" id="document_attachment_id" name="document_attachment_id" value="<?=esc_attr($document_data['attachment_id'])?>">
							<?php if($attachment_url) { ?>
							<div class="input-group input-group-sm">
								<div class="form-control text-truncate"><?=esc_html(basename($attachment_url))?></div>
								<button class="btn btn-warning" id="document_remove_attachment" type="button">Xóa file</button>
							</div>
							<?php } ?>
						</div>
						<label class="col d-block ps-5" for="document_attachment">
							<div class="input-group input-group-sm">
								<div class="form-control text-nowrap">Chọn file dự toán cần tải lên</div>
								<span class="btn btn-primary">Bấm tải lên</span>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<input type="file" id="document_attachment" name="document_attachment" accept=".doc,.docx,.xls,.xlsx,.pdf,.rar,.zip" class="form-control">
							</div>
						</label>
					</div>
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="document_selected" id="document_selected" <?php checked( (isset($document_data['selected']) && $document_data['selected']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="document_selected">Được chọn?</label>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-document-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-document" tabindex="-1" role="dialog" aria-labelledby="edit-document-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-document-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
