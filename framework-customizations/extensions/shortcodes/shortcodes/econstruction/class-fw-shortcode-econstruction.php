<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Econstruction extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_econstruction_form', [$this, 'ajax_get_edit_econstruction_form']);
      add_action( 'wp_ajax_update_econstruction', [$this, 'ajax_update_econstruction']);
      add_action( 'wp_ajax_get_econstruction_info', [$this, 'ajax_get_econstruction_info']);
      add_action( 'wp_ajax_econstruction_hide', [$this, 'ajax_econstruction_hide']);
	}

	public function ajax_econstruction_hide() {
		global $current_client;
		$econstruction_id = isset($_POST['econstruction']) ? absint($_POST['econstruction']) : 0;
		$response = false;
		if(current_user_can('econstruction_edit') && $current_client && $econstruction_id && check_ajax_referer( 'global', 'nonce', false )) {
			$econstruction_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'econstruction_hide', []);
			$econstruction_hide[] = $econstruction_id;
			fw_set_db_term_option($current_client->term_id, 'passwords', 'econstruction_hide', $econstruction_hide);
			$response = true;
		}
		wp_send_json($response);
	}

	public function ajax_get_econstruction_info() {

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$econstruction = isset($_GET['econstruction'])?absint($_GET['econstruction']):0;

		$response = [
			'info' => '',
			'zalo' => '',
			'required' => '',
			'received' => '',
			'completed' => '',
			'sent' => '',
			'file_id' => '',
			'quote' => '',
		];
		
		if($client && $econstruction) {
			$default_econstruction_file = fw_get_db_post_option($econstruction,'econstruction_file');
			$default_data = [
				'value' => fw_get_db_post_option($econstruction,'econstruction_value'),
				'unit' => fw_get_db_post_option($econstruction,'econstruction_unit'),
				'zalo' => fw_get_db_post_option($econstruction,'econstruction_zalo'),
				'file_id' => (!empty($default_econstruction_file))?$default_econstruction_file['attachment_id']:'',
			];

			$data = fw_get_db_term_option($client, 'passwords', 'econstruction', []);
			$econstruction_data = isset($data[$econstruction])?$data[$econstruction]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

			if(empty($econstruction_data['value'])) $econstruction_data['value'] = $default_data['value'];
			if(empty($econstruction_data['unit'])) $econstruction_data['unit'] = $default_data['unit'];
			if(empty($econstruction_data['zalo'])) $econstruction_data['zalo'] = $default_data['zalo'];
			if(empty($econstruction_data['file_id'])) $econstruction_data['file_id'] = $default_data['file_id'];

			$response['zalo'] = ($econstruction_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($econstruction_data['zalo']).'" target="_blank">Zalo</a>':'';
			$response['file'] = ($econstruction_data['file_id'])?'<a class="btn-shadow btn btn-sm btn-primary" href="'.esc_url(wp_get_attachment_url($econstruction_data['file_id'])).'" target="_blank">Tải</a>':'';
			
			$response['required'] = (isset($econstruction_data['required']) && $econstruction_data['required']!='')?'<div class="bg-danger" title="Ngày gửi yêu cầu">'.esc_html(date('d/m', strtotime($econstruction_data['required']))).'</div>':'';
			$response['received'] = (isset($econstruction_data['received']) && $econstruction_data['received']!='')?'<div class="bg-danger" title="Ngày nhận dự toán nhà thầu">'.esc_html(date('d/m', strtotime($econstruction_data['received']))).'</div>':'';
			$response['completed'] = (isset($econstruction_data['completed']) && $econstruction_data['completed']!='')?'<div class="bg-danger" title="Ngày làm xong dự toán">'.esc_html(date('d/m', strtotime($econstruction_data['completed']))).'</div>':'';
			$response['sent'] = (isset($econstruction_data['sent']) && $econstruction_data['sent']!='')?'<div class="bg-danger" title="Ngày gửi khách">'.esc_html(date('d/m', strtotime($econstruction_data['sent']))).'</div>':'';
			
			$response['quote'] = (isset($econstruction_data['quote']) && $econstruction_data['quote']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-secondary bg-green text-dark fw-bold ms-2" title="Đã gửi cho khách hàng"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
		?>
			<div class="econstruction-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $econstruction )); ?>
			</div>
			<?php if($econstruction_data['value']) { ?>
			<div class="econstruction-value mb-1">
				<span>Tổng giá trị:</span>
				<span class="text-red fw-bold"><?php echo esc_html($econstruction_data['value']); ?></span>
			</div>
			<?php } ?>
			<?php if($econstruction_data['unit']) { ?>
			<div class="econstruction-unit mb-1">
				<div class="text-red"><?php echo esc_html($econstruction_data['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center econstruction-url mb-3">
				<?php
				if($econstruction_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($econstruction_data['url'])?>" target="_blank">Xem chi tiết</a>
					<?php
				}
				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_econstruction() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(current_user_can('econstruction_edit') && check_ajax_referer( 'edit-econstruction', 'nonce', false )) {
			$client = isset($_POST['client'])?absint($_POST['client']):0;
			$econstruction_id = isset($_POST['econstruction'])?absint($_POST['econstruction']):0;

			$econstruction_value = isset($_POST['econstruction_value'])?sanitize_text_field($_POST['econstruction_value']):'';
			$econstruction_unit = isset($_POST['econstruction_unit'])?sanitize_text_field($_POST['econstruction_unit']):'';
			$econstruction_zalo = isset($_POST['econstruction_zalo'])?sanitize_text_field($_POST['econstruction_zalo']):'';
			$econstruction_url = isset($_POST['econstruction_url'])?sanitize_url($_POST['econstruction_url']):'';
			$econstruction_file_id = isset($_POST['econstruction_file_id'])?absint($_POST['econstruction_file_id']):0;
			$econstruction_file = isset($_FILES['econstruction_file']) ? $_FILES['econstruction_file'] : null;

			$econstruction_required = isset($_POST['econstruction_required']) ? $_POST['econstruction_required'] : '';
			$econstruction_received = isset($_POST['econstruction_received']) ? $_POST['econstruction_received'] : '';
			$econstruction_completed = isset($_POST['econstruction_completed']) ? $_POST['econstruction_completed'] : '';
			$econstruction_sent = isset($_POST['econstruction_sent']) ? $_POST['econstruction_sent'] : '';
			$econstruction_quote = isset($_POST['econstruction_quote']) ? $_POST['econstruction_quote'] : '';
			
			if($client && $econstruction_id) {
				$data = fw_get_db_term_option($client, 'passwords', 'econstruction', []);
				$econstruction_data = isset($data[$econstruction_id])?$data[$econstruction_id]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

				$new_econstruction_data = [
					'required' => $econstruction_required,
					'received' => $econstruction_received,
					'completed' => $econstruction_completed,
					'sent' => $econstruction_sent,
					'value' => $econstruction_value,
					'unit' => $econstruction_unit,
					'zalo' => $econstruction_zalo,
					'url' => $econstruction_url,
					'file_id' => ($econstruction_file_id!=0)?$econstruction_file_id:'',
					'quote' => $econstruction_quote,
				];

				// tải lên file dự toán
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					require_once(ABSPATH . "wp-admin" . '/includes/file.php');
					require_once(ABSPATH . "wp-admin" . '/includes/media.php');
				}

				$econstruction_file_upload = media_handle_upload( 'econstruction_file', 0 );

				//debug_log($econstruction_file_upload);

				if ($econstruction_file['error']==0 && $econstruction_file_upload && ! is_array( $econstruction_file_upload ) ) {
					$new_econstruction_data['file_id'] = $econstruction_file_upload;
					if($econstruction_file_id) wp_delete_attachment($econstruction_file_id, true);
				}

				if($new_econstruction_data['file_id']=='' || $new_econstruction_data['file_id']==0) {
					if($econstruction_data['file_id']) wp_delete_attachment($econstruction_data['file_id'], true);
				}

				$econstruction_data[$econstruction_id] = $new_econstruction_data;

				fw_set_db_term_option($client, 'passwords', 'econstruction', $econstruction_data);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_econstruction_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$econstruction = isset($_GET['econstruction'])?absint($_GET['econstruction']):0;

		if($client && $econstruction) {
			$data = fw_get_db_term_option($client, 'passwords', 'econstruction', []);
			$econstruction_data = isset($data[$econstruction])?$data[$econstruction]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

			$file_url = (isset($econstruction_data['file_id']) && $econstruction_data['file_id']!='')?wp_get_attachment_url($econstruction_data['file_id']):'';
			?>
			<form id="frm-edit-econstruction" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="client" name="client" value="<?=$client?>">
				<input type="hidden" id="econstruction" name="econstruction" value="<?=$econstruction?>">
				<?php wp_nonce_field( 'edit-econstruction', 'nonce' ); ?>
				<div id="edit-econstruction-response"></div>
				<div class="mb-3">
					Gửi yêu cầu
					<input class="form-control" type="date" value="<?php echo (isset($econstruction_data['required'])&&$econstruction_data['required']!='')?esc_html(date('Y-m-d', strtotime($econstruction_data['required']))):''; ?>" name="econstruction_required" id="econstruction_required">
				</div>
				<div class="mb-3">
					Dự toán nhà thầu
					<input class="form-control" type="date" value="<?php echo (isset($econstruction_data['received'])&&$econstruction_data['received']!='')?esc_html(date('Y-m-d', strtotime($econstruction_data['received']))):''; ?>" name="econstruction_received" id="econstruction_received">
				</div>
				<div class="mb-3">
					Xong dự toán
					<input class="form-control" type="date" value="<?php echo (isset($econstruction_data['completed'])&&$econstruction_data['completed']!='')?esc_html(date('Y-m-d', strtotime($econstruction_data['completed']))):''; ?>" name="econstruction_completed" id="econstruction_completed">
				</div>
				<div class="mb-3">
					Ngày gửi khách
					<input class="form-control" type="date" value="<?php echo (isset($econstruction_data['sent'])&&$econstruction_data['sent']!='')?esc_html(date('Y-m-d', strtotime($econstruction_data['sent']))):''; ?>" name="econstruction_sent" id="econstruction_sent">
				</div>
				<div class="mb-3">
					<input type="text" id="econstruction_value" name="econstruction_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($econstruction_data['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="econstruction_unit" name="econstruction_unit" placeholder="Ghi chú" class="form-control" value="<?php echo esc_attr($econstruction_data['unit']); ?>">
				</div>

				<div class="mb-3">
					<input type="text" id="econstruction_zalo" name="econstruction_zalo" placeholder="Link nhóm zalo" class="form-control" value="<?php echo esc_attr($econstruction_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="econstruction_url" name="econstruction_url" placeholder="Link dự toán" class="form-control" value="<?php echo ($econstruction_data['url'])?esc_url($econstruction_data['url']):''; ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dự toán</div>
					<div class="row row-cols-2 g-0 p-2 border rounded-2">
						<div class="col attachment-uploaded">
							<input type="hidden" id="econstruction_file_id" name="econstruction_file_id" value="<?=esc_attr($econstruction_data['file_id'])?>">
							<?php if($file_url) { ?>
							<div class="input-group input-group-sm">
								<div class="form-control text-truncate"><?=esc_html(basename($file_url))?></div>
								<button class="btn btn-sm btn-warning" id="econstruction_remove_file" type="button">Xóa file</button>
							</div>
							<?php } ?>
						</div>
						<label class="col d-block ps-5" for="econstruction_file">
							<div class="input-group input-group-sm">
								<div class="form-control text-nowrap">Chọn file dự toán cần tải lên</div>
								<span class="btn btn-primary">Bấm tải lên</span>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<input type="file" id="econstruction_file" name="econstruction_file" accept=".doc,.docx,.xls,.xlsx,.pdf" class="form-control">
							</div>
						</label>
					</div>
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="econstruction_quote" id="econstruction_quote" <?php checked( (isset($econstruction_data['quote']) && $econstruction_data['quote']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="econstruction_quote">Được khách hàng lựa chọn?</label>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-econstruction-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-econstruction" tabindex="-1" role="dialog" aria-labelledby="edit-econstruction-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-econstruction-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
