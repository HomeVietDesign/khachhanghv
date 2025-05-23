<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Documents extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_document_form', [$this, 'ajax_get_edit_document_form']);
      add_action( 'wp_ajax_update_document', [$this, 'ajax_update_document']);
      add_action( 'wp_ajax_get_document_info', [$this, 'ajax_get_document_info']);
	}

	public function ajax_get_document_info() {
		global $current_password;
		$default_term_password = get_option( 'default_term_passwords', -1 );

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$document_id = isset($_GET['document'])?absint($_GET['document']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];
		
		if($client && $document_id) {

			$data = get_post_meta($document_id, '_data', true);
			$document_data = isset($data[$client])?$data[$client]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

			$phone_number = get_post_field( 'post_excerpt', $document_id );

			$response['zalo'] = ($document_data['zalo'])?'<a class="btn btn-sm btn-shadow" href="'.esc_url($document_data['zalo']).'" target="_blank">Zalo</a>':'';

			ob_start();
		?>
			<div class="document-title pt-3 mb-1 fs-5">
				<span class="d-block text-truncate text-uppercase" title="<?php echo esc_attr(get_the_title( $document_id )); ?>"><?php echo esc_html(get_the_title( $document_id )); ?></span>
			</div>
			<?php if($document_data['value']!='') { ?>
			<div class="document-value mb-1">
				<span>Tổng giá trị: </span>
				<span class="text-red fw-bold"><?php echo esc_html(number_format($document_data['value'],0,'.',',')); ?></span><span class="text-red"> <?php echo esc_html($document_data['unit']); ?></span>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center document-links mb-3">
				<?php
				if($phone_number) {
					?>
					<a class="btn btn-sm btn-danger my-1 mx-2" href="tel:<?=esc_attr($phone_number)?>"><?=esc_html($phone_number)?></a>
					<?php
				}

				if($document_data['attachment_id']) {
					$attachment_url = wp_get_attachment_url($document_data['attachment_id']);
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($attachment_url)?>" target="_blank">Xem chi tiết</a>
					<?php
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

		if(has_role('administrator') && check_ajax_referer( 'edit-document', 'nonce', false )) {
			$document_client = isset($_POST['document_client'])?absint($_POST['document_client']):0;
			$document_id = isset($_POST['document_id'])?absint($_POST['document_id']):0;
			$document_attachment_id = isset($_POST['document_attachment_id'])?absint($_POST['document_attachment_id']):'';
			$document_value = isset($_POST['document_value'])?absint(str_replace(',', '', $_POST['document_value'])):0;
			$document_unit = isset($_POST['document_unit'])?sanitize_text_field($_POST['document_unit']):'';
			$document_zalo = isset($_POST['document_zalo'])?sanitize_text_field($_POST['document_zalo']):'';
			$document_attachment = isset($_FILES['document_attachment']) ? $_FILES['document_attachment'] : null;

			if($document_client && $document_id) {
				$data = get_post_meta($document_id, '_data', true);
				if(empty($data)) $data = [];
				$document_data = isset($data[$document_client])?$data[$document_client]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

				$new_document_data = [
					'value' => ($document_value!=0)?$document_value:'',
					'unit' => $document_unit,
					'zalo' => $document_zalo,
					'attachment_id' => $document_attachment_id
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
			$document_data = isset($data[$client])?$data[$client]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

			$attachment_url = ($document_data['attachment_id'])?wp_get_attachment_url($document_data['attachment_id']):'';
			?>
			<form id="frm-edit-document" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="document_client" name="document_client" value="<?=$client?>">
				<input type="hidden" id="document_id" name="document_id" value="<?=$document?>">
				<?php wp_nonce_field( 'edit-document', 'nonce' ); ?>
				<div id="edit-document-response"></div>
				<div class="mb-3">
					<input type="text" id="document_value" name="document_value" placeholder="Giá trị" class="form-control" value="<?php echo ($document_data['value'])?esc_attr(number_format(absint($document_data['value']),0,'.',',')):''; ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="document_unit" name="document_unit" placeholder="Đơn vị" class="form-control" value="<?php echo esc_attr($document_data['unit']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="document_zalo" name="document_zalo" placeholder="URL nhóm zalo" class="form-control" value="<?php echo esc_attr($document_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dữ liệu</div>
					<input type="hidden" id="document_attachment_id" name="document_attachment_id" value="<?=esc_attr($document_data['attachment_id'])?>">
					<?php if($attachment_url) { ?>
					<div class="mb-2">
						<span class="overflow-hidden"><?=esc_html(basename($attachment_url))?></span>
						<button class="btn btn-sm btn-danger" id="document_remove_attachment">Xóa file</button>
					</div>
					<?php } ?>
					<label class="d-block" for="document_attachment">
						<span class="input-group">
							<span class="form-control overflow-hidden"></span>
							<span class="input-group-text">Bấm tải lên</span>
						</span>
						<div style="width: 0;height: 0;overflow: hidden;">
							<input type="file" id="document_attachment" name="document_attachment" accept="image/*,.pdf" class="form-control">
						</div>
					</label>
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
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
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
