<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Efurniture extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_efurniture_form', [$this, 'ajax_get_edit_efurniture_form']);
      add_action( 'wp_ajax_update_efurniture', [$this, 'ajax_update_efurniture']);
      add_action( 'wp_ajax_get_efurniture_info', [$this, 'ajax_get_efurniture_info']);
      add_action( 'wp_ajax_efurniture_hide', [$this, 'ajax_efurniture_hide']);
	}

	public function ajax_efurniture_hide() {
		global $current_client;
		$efurniture_id = isset($_POST['efurniture']) ? absint($_POST['efurniture']) : 0;
		$response = false;
		if(current_user_can('efurniture_edit') && $current_client && $efurniture_id && check_ajax_referer( 'global', 'nonce', false )) {
			$efurniture_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'efurniture_hide', []);
			$efurniture_hide[] = $efurniture_id;
			fw_set_db_term_option($current_client->term_id, 'passwords', 'efurniture_hide', $efurniture_hide);
			$response = true;
		}
		wp_send_json($response);
	}

	public function ajax_get_efurniture_info() {

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$efurniture = isset($_GET['efurniture'])?absint($_GET['efurniture']):0;

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
		
		if($client && $efurniture) {
			$default_efurniture_file = fw_get_db_post_option($efurniture,'efurniture_file');
			$default_data = [
				'value' => fw_get_db_post_option($efurniture,'efurniture_value'),
				'unit' => fw_get_db_post_option($efurniture,'efurniture_unit'),
				'zalo' => fw_get_db_post_option($efurniture,'efurniture_zalo'),
				'file_id' => (!empty($default_efurniture_file))?$default_efurniture_file['attachment_id']:'',
			];

			$data = fw_get_db_term_option($client, 'passwords', 'efurniture', []);
			$efurniture_data = isset($data[$efurniture])?$data[$efurniture]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

			if(empty($efurniture_data['value'])) $efurniture_data['value'] = $default_data['value'];
			if(empty($efurniture_data['unit'])) $efurniture_data['unit'] = $default_data['unit'];
			if(empty($efurniture_data['zalo'])) $efurniture_data['zalo'] = $default_data['zalo'];
			if(empty($efurniture_data['file_id'])) $efurniture_data['file_id'] = $default_data['file_id'];

			$response['zalo'] = ($efurniture_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($efurniture_data['zalo']).'" target="_blank">Zalo</a>':'';
			$response['file'] = ($efurniture_data['file_id'])?'<a class="btn-shadow btn btn-sm btn-primary" href="'.esc_url(wp_get_attachment_url($efurniture_data['file_id'])).'" target="_blank">Tải</a>':'';
			
			$response['required'] = (isset($efurniture_data['required']) && $efurniture_data['required']!='')?'<div class="bg-danger" title="Ngày gửi yêu cầu">'.esc_html(date('d/m', strtotime($efurniture_data['required']))).'</div>':'';
			$response['received'] = (isset($efurniture_data['received']) && $efurniture_data['received']!='')?'<div class="bg-danger" title="Ngày nhận dự toán nhà thầu">'.esc_html(date('d/m', strtotime($efurniture_data['received']))).'</div>':'';
			$response['completed'] = (isset($efurniture_data['completed']) && $efurniture_data['completed']!='')?'<div class="bg-danger" title="Ngày làm xong dự toán">'.esc_html(date('d/m', strtotime($efurniture_data['completed']))).'</div>':'';
			$response['sent'] = (isset($efurniture_data['sent']) && $efurniture_data['sent']!='')?'<div class="bg-danger" title="Ngày gửi khách">'.esc_html(date('d/m', strtotime($efurniture_data['sent']))).'</div>':'';
			
			$response['quote'] = (isset($efurniture_data['quote']) && $efurniture_data['quote']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-secondary bg-green text-dark fw-bold ms-2" title="Đã gửi cho khách hàng"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
		?>
			<div class="efurniture-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $efurniture )); ?>
			</div>
			<?php if($efurniture_data['value']) { ?>
			<div class="efurniture-value mb-1">
				<span>Tổng giá trị:</span>
				<span class="text-red fw-bold"><?php echo esc_html($efurniture_data['value']); ?></span>
			</div>
			<?php } ?>
			<?php if($efurniture_data['unit']) { ?>
			<div class="efurniture-unit mb-1">
				<div class="text-red"><?php echo esc_html($efurniture_data['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center efurniture-url mb-3">
				<?php
				if($efurniture_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($efurniture_data['url'])?>" target="_blank">Xem chi tiết</a>
					<?php
				}
				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_efurniture() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(current_user_can('efurniture_edit') && check_ajax_referer( 'edit-efurniture', 'nonce', false )) {
			$client = isset($_POST['client'])?absint($_POST['client']):0;
			$efurniture_id = isset($_POST['efurniture'])?absint($_POST['efurniture']):0;

			$efurniture_value = isset($_POST['efurniture_value'])?sanitize_text_field($_POST['efurniture_value']):'';
			$efurniture_unit = isset($_POST['efurniture_unit'])?sanitize_text_field($_POST['efurniture_unit']):'';
			$efurniture_zalo = isset($_POST['efurniture_zalo'])?sanitize_text_field($_POST['efurniture_zalo']):'';
			$efurniture_url = isset($_POST['efurniture_url'])?sanitize_url($_POST['efurniture_url']):'';
			$efurniture_file_id = isset($_POST['efurniture_file_id'])?absint($_POST['efurniture_file_id']):0;
			$efurniture_file = isset($_FILES['efurniture_file']) ? $_FILES['efurniture_file'] : null;

			$efurniture_required = isset($_POST['efurniture_required']) ? $_POST['efurniture_required'] : '';
			$efurniture_received = isset($_POST['efurniture_received']) ? $_POST['efurniture_received'] : '';
			$efurniture_completed = isset($_POST['efurniture_completed']) ? $_POST['efurniture_completed'] : '';
			$efurniture_sent = isset($_POST['efurniture_sent']) ? $_POST['efurniture_sent'] : '';
			$efurniture_quote = isset($_POST['efurniture_quote']) ? $_POST['efurniture_quote'] : '';
			
			if($client && $efurniture_id) {
				$data = fw_get_db_term_option($client, 'passwords', 'efurniture', []);
				$efurniture_data = isset($data[$efurniture_id])?$data[$efurniture_id]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

				$new_efurniture_data = [
					'required' => $efurniture_required,
					'received' => $efurniture_received,
					'completed' => $efurniture_completed,
					'sent' => $efurniture_sent,
					'value' => $efurniture_value,
					'unit' => $efurniture_unit,
					'zalo' => $efurniture_zalo,
					'url' => $efurniture_url,
					'file_id' => ($efurniture_file_id!=0)?$efurniture_file_id:'',
					'quote' => $efurniture_quote,
				];

				// tải lên file dự toán
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					require_once(ABSPATH . "wp-admin" . '/includes/file.php');
					require_once(ABSPATH . "wp-admin" . '/includes/media.php');
				}

				$efurniture_file_upload = media_handle_upload( 'efurniture_file', 0 );

				//debug_log($efurniture_file_upload);

				if ($efurniture_file['error']==0 && $efurniture_file_upload && ! is_array( $efurniture_file_upload ) ) {
					$new_efurniture_data['file_id'] = $efurniture_file_upload;
					if($efurniture_file_id) wp_delete_attachment($efurniture_file_id, true);
				}

				if($new_efurniture_data['file_id']=='' || $new_efurniture_data['file_id']==0) {
					if($efurniture_data['file_id']) wp_delete_attachment($efurniture_data['file_id'], true);
				}

				$efurniture_data[$efurniture_id] = $new_efurniture_data;

				fw_set_db_term_option($client, 'passwords', 'efurniture', $efurniture_data);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_efurniture_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$efurniture = isset($_GET['efurniture'])?absint($_GET['efurniture']):0;

		if($client && $efurniture) {
			$data = fw_get_db_term_option($client, 'passwords', 'efurniture', []);
			$efurniture_data = isset($data[$efurniture])?$data[$efurniture]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

			$file_url = (isset($efurniture_data['file_id']) && $efurniture_data['file_id']!='')?wp_get_attachment_url($efurniture_data['file_id']):'';
			?>
			<form id="frm-edit-efurniture" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="client" name="client" value="<?=$client?>">
				<input type="hidden" id="efurniture" name="efurniture" value="<?=$efurniture?>">
				<?php wp_nonce_field( 'edit-efurniture', 'nonce' ); ?>
				<div id="edit-efurniture-response"></div>
				<div class="mb-3">
					Gửi yêu cầu
					<input class="form-control" type="date" value="<?php echo (isset($efurniture_data['required'])&&$efurniture_data['required']!='')?esc_html(date('Y-m-d', strtotime($efurniture_data['required']))):''; ?>" name="efurniture_required" id="efurniture_required">
				</div>
				<div class="mb-3">
					Dự toán nhà thầu
					<input class="form-control" type="date" value="<?php echo (isset($efurniture_data['received'])&&$efurniture_data['received']!='')?esc_html(date('Y-m-d', strtotime($efurniture_data['received']))):''; ?>" name="efurniture_received" id="efurniture_received">
				</div>
				<div class="mb-3">
					Xong dự toán
					<input class="form-control" type="date" value="<?php echo (isset($efurniture_data['completed'])&&$efurniture_data['completed']!='')?esc_html(date('Y-m-d', strtotime($efurniture_data['completed']))):''; ?>" name="efurniture_completed" id="efurniture_completed">
				</div>
				<div class="mb-3">
					Ngày gửi khách
					<input class="form-control" type="date" value="<?php echo (isset($efurniture_data['sent'])&&$efurniture_data['sent']!='')?esc_html(date('Y-m-d', strtotime($efurniture_data['sent']))):''; ?>" name="efurniture_sent" id="efurniture_sent">
				</div>
				<div class="mb-3">
					<input type="text" id="efurniture_value" name="efurniture_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($efurniture_data['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="efurniture_unit" name="efurniture_unit" placeholder="Ghi chú" class="form-control" value="<?php echo esc_attr($efurniture_data['unit']); ?>">
				</div>

				<div class="mb-3">
					<input type="text" id="efurniture_zalo" name="efurniture_zalo" placeholder="Link nhóm zalo" class="form-control" value="<?php echo esc_attr($efurniture_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="efurniture_url" name="efurniture_url" placeholder="Link dự toán" class="form-control" value="<?php echo ($efurniture_data['url'])?esc_url($efurniture_data['url']):''; ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dự toán</div>
					<div class="row row-cols-2 g-0 p-2 border rounded-2">
						<div class="col attachment-uploaded">
							<input type="hidden" id="efurniture_file_id" name="efurniture_file_id" value="<?=esc_attr($efurniture_data['file_id'])?>">
							<?php if($file_url) { ?>
							<div class="input-group input-group-sm">
								<div class="form-control text-truncate"><?=esc_html(basename($file_url))?></div>
								<button class="btn btn-sm btn-warning" id="efurniture_remove_file" type="button">Xóa file</button>
							</div>
							<?php } ?>
						</div>
						<label class="col d-block ps-5" for="efurniture_file">
							<div class="input-group input-group-sm">
								<div class="form-control text-nowrap">Chọn file dự toán cần tải lên</div>
								<span class="btn btn-primary">Bấm tải lên</span>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<input type="file" id="efurniture_file" name="efurniture_file" accept=".doc,.docx,.xls,.xlsx,.pdf" class="form-control">
							</div>
						</label>
					</div>
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="efurniture_quote" id="efurniture_quote" <?php checked( (isset($efurniture_data['quote']) && $efurniture_data['quote']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="efurniture_quote">Được khách hàng lựa chọn?</label>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-efurniture-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-efurniture" tabindex="-1" role="dialog" aria-labelledby="edit-efurniture-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-efurniture-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
