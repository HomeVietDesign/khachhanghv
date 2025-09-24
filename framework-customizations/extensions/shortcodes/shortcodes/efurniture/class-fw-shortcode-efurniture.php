<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Efurniture extends FW_Shortcode
{
	public $default = [
		'required_label'=>'',
		'required'=>'',
		'received_label'=>'',
		'received'=>'',
		'completed_label'=>'',
		'completed'=>'',
		'sent_label'=>'',
		'sent'=>'',
		'value'=>'',
		'unit'=>'',
		'zalo'=>'',
		'url'=>'',
		'url2'=>'',
		'url3'=>'',
		'file_id'=>'',
		'quote'=>''
	];

	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_efurniture_form', [$this, 'ajax_get_edit_efurniture_form']);
      add_action( 'wp_ajax_update_efurniture', [$this, 'ajax_update_efurniture']);
      add_action( 'wp_ajax_get_efurniture_info', [$this, 'ajax_get_efurniture_info']);
      add_action( 'wp_ajax_efurniture_hide', [$this, 'ajax_efurniture_hide']);

      add_action( 'wp_ajax_efurniture_chunk_upload', [$this, 'handle_efurniture_chunk_upload'] );
      add_action( 'wp_ajax_efurniture_check_chunks', [$this, 'check_uploaded_chunks'] );
      //add_action( 'wp_ajax_efurniture_file_upload', [$this, 'ajax_efurniture_file_upload']);
	}

	public function ajax_efurniture_hide() {
		global $current_client;
		$efurniture_id = isset($_POST['efurniture']) ? absint($_POST['efurniture']) : 0;
		$response = 0;
		if(current_user_can('efurniture_edit') && $current_client && $efurniture_id && check_ajax_referer( 'global', 'nonce', false )) {

			$efurniture_hide = get_term_meta($current_client->term_id, 'efurniture_hide', true);
			if(empty($efurniture_hide)) $efurniture_hide = [];

			if(in_array($efurniture_id, $efurniture_hide)) {
				unset($efurniture_hide[array_search($efurniture_id, $efurniture_hide)]);
				$response = -1;
			} else {
				$efurniture_hide[] = $efurniture_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'efurniture_hide', $efurniture_hide);

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

			$data = get_term_meta($client, 'efurniture', true);
			if(empty($data)) $data = [];
			$efurniture_data = isset($data[$efurniture])?$data[$efurniture]:$this->default;
			$efurniture_data += $this->default;

			if(empty($efurniture_data['value'])) $efurniture_data['value'] = $default_data['value'];
			if(empty($efurniture_data['unit'])) $efurniture_data['unit'] = $default_data['unit'];
			if(empty($efurniture_data['zalo'])) $efurniture_data['zalo'] = $default_data['zalo'];
			if(empty($efurniture_data['file_id'])) $efurniture_data['file_id'] = $default_data['file_id'];

			$response['zalo'] = ($efurniture_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($efurniture_data['zalo']).'" target="_blank">Zalo</a>':'';
			$response['file'] = ($efurniture_data['file_id'])?'<a class="btn-shadow btn btn-sm btn-primary" href="'.esc_url(wp_get_attachment_url($efurniture_data['file_id'])).'" target="_blank">Tải</a>':'';
			
			$response['required'] = (isset($efurniture_data['required']) && $efurniture_data['required']!='')?'<div class="bg-danger" title="'.esc_attr($efurniture_data['required_label']).'">'.esc_html(date('d/m', strtotime($efurniture_data['required']))).'</div>':'';
			$response['received'] = (isset($efurniture_data['received']) && $efurniture_data['received']!='')?'<div class="bg-danger" title="'.esc_attr($efurniture_data['received_label']).'">'.esc_html(date('d/m', strtotime($efurniture_data['received']))).'</div>':'';
			$response['completed'] = (isset($efurniture_data['completed']) && $efurniture_data['completed']!='')?'<div class="bg-danger" title="'.esc_attr($efurniture_data['completed_label']).'">'.esc_html(date('d/m', strtotime($efurniture_data['completed']))).'</div>':'';
			$response['sent'] = (isset($efurniture_data['sent']) && $efurniture_data['sent']!='')?'<div class="bg-danger" title="'.esc_attr($efurniture_data['sent_label']).'">'.esc_html(date('d/m', strtotime($efurniture_data['sent']))).'</div>':'';
			
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
				if(isset($efurniture_data['url']) && $efurniture_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($efurniture_data['url'])?>" target="_blank">Dự toán 1</a>
					<?php
				}

				if(isset($efurniture_data['url2']) && $efurniture_data['url2']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($efurniture_data['url2'])?>" target="_blank">Dự toán 2</a>
					<?php
				}

				if(isset($efurniture_data['url3']) && $efurniture_data['url3']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($efurniture_data['url3'])?>" target="_blank">Dự toán 3</a>
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
			$efurniture_url2 = isset($_POST['efurniture_url2'])?sanitize_url($_POST['efurniture_url2']):'';
			$efurniture_url3 = isset($_POST['efurniture_url3'])?sanitize_url($_POST['efurniture_url3']):'';
			
			$efurniture_file_id = isset($_POST['efurniture_file_id'])?absint($_POST['efurniture_file_id']):0;
			
			$efurniture_required_label = isset($_POST['efurniture_required_label']) ? $_POST['efurniture_required_label'] : '';
			$efurniture_required = isset($_POST['efurniture_required']) ? $_POST['efurniture_required'] : '';
			$efurniture_received_label = isset($_POST['efurniture_received_label']) ? $_POST['efurniture_received_label'] : '';
			$efurniture_received = isset($_POST['efurniture_received']) ? $_POST['efurniture_received'] : '';
			$efurniture_completed_label = isset($_POST['efurniture_completed_label']) ? $_POST['efurniture_completed_label'] : '';
			$efurniture_completed = isset($_POST['efurniture_completed']) ? $_POST['efurniture_completed'] : '';
			$efurniture_sent_label = isset($_POST['efurniture_sent_label']) ? $_POST['efurniture_sent_label'] : '';
			$efurniture_sent = isset($_POST['efurniture_sent']) ? $_POST['efurniture_sent'] : '';
			$efurniture_quote = isset($_POST['efurniture_quote']) ? $_POST['efurniture_quote'] : '';
			
			if($client && $efurniture_id) {
				$data = get_term_meta($client, 'efurniture', true);
				if(empty($data)) $data = [];
				$efurniture_data = isset($data[$efurniture_id])?$data[$efurniture_id]:$this->default;
				$efurniture_data += $this->default;

				$new_efurniture_data = [
					'required_label' => $efurniture_required_label,
					'required' => $efurniture_required,
					'received_label' => $efurniture_received_label,
					'received' => $efurniture_received,
					'completed_label' => $efurniture_completed_label,
					'completed' => $efurniture_completed,
					'sent_label' => $efurniture_sent_label,
					'sent' => $efurniture_sent,
					'value' => $efurniture_value,
					'unit' => $efurniture_unit,
					'zalo' => $efurniture_zalo,
					'url' => $efurniture_url,
					'url2' => $efurniture_url2,
					'url3' => $efurniture_url3,
					'file_id' => ($efurniture_file_id!=0)?$efurniture_file_id:'',
					'quote' => $efurniture_quote,
				];

				if($new_efurniture_data['file_id']=='' || $new_efurniture_data['file_id']==0) {
					if(isset($efurniture_data['file_id']) && $efurniture_data['file_id']) wp_delete_attachment($efurniture_data['file_id'], true);
				}

				$data[$efurniture_id] = $new_efurniture_data;

				update_term_meta($client, 'efurniture', $data);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_efurniture_file_upload() {
		$response = [
			'status' => 500,
			'message' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(!current_user_can('efurniture_edit') || !check_ajax_referer( 'edit-efurniture', 'nonce', false )) {
			$response['status'] = 403;
			$response['message'] = "Forbiden.";
			wp_send_json( $response, 403 );
		}

		$client = isset($_POST['client'])?absint($_POST['client']):0;
		$efurniture_id = isset($_POST['efurniture'])?absint($_POST['efurniture']):0;

		wp_send_json( $response, $response['status'] );
	}

	public function handle_efurniture_chunk_upload() {
		$response = ['success' => false, 'attachment_id' => 0, 'url' => '', 'filename' => '', 'msg' => ''];

		if(current_user_can('efurniture_edit') && check_ajax_referer( 'edit-efurniture', 'nonce', false )) {
			$client = isset($_POST['client'])?absint($_POST['client']):0;
			$efurniture_id = isset($_POST['efurniture'])?absint($_POST['efurniture']):0;

			$chunk_temp_dir = wp_upload_dir()['basedir'] . '/chunk_temp/';
			$combine_dir = wp_upload_dir()['basedir'] . '/combine/';
			if (!is_dir($chunk_temp_dir)) mkdir($chunk_temp_dir);
			if (!is_dir($combine_dir)) mkdir($combine_dir);

			$uploadId = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['uploadId']);
			$chunkIndex = intval($_POST['chunkIndex']);
			$totalChunks = intval($_POST['totalChunks']);
			$fileName = sanitize_file_name($_POST['fileName']);

			$target = "{$chunk_temp_dir}{$uploadId}.part{$chunkIndex}";
			@move_uploaded_file($_FILES['file']['tmp_name'], $target);

			// Ghép nếu là chunk cuối
			if ($chunkIndex + 1 == $totalChunks) {
				$final_path = $combine_dir.$fileName;
				$out = fopen($final_path, 'wb');
				for ($i = 0; $i < $totalChunks; $i++) {
					$chunk_path = "{$chunk_temp_dir}{$uploadId}.part{$i}";
					fwrite($out, file_get_contents($chunk_path));
					unlink($chunk_path);
				}
				fclose($out);

				// ĐĂNG KÝ FILE VÀO MEDIA LIBRARY
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';

				$filetype = wp_check_filetype($fileName, null);

				$file = [
					'name'     => $fileName, // ex: wp-header-logo.png
					'type'     => $filetype['type'],
					'tmp_name' => $final_path,
					'error'    => 0,
					'size'     => filesize( $final_path ),
				];

				// Move the temporary file into the uploads directory.
				$attach_id = media_handle_sideload( $file, 0 );
				//debug_log($attach_id);

				if(!($attach_id instanceof \WP_Error)) {
					$data = get_term_meta($client, 'efurniture', true);
					if(empty($data)) $data = [];
					$efurniture_data = isset($data[$efurniture_id])?$data[$efurniture_id]:$this->default;
					$efurniture_data += $this->default;

					if($efurniture_data['file_id']) wp_delete_attachment($efurniture_data['file_id'], true);

					$efurniture_data['file_id'] = $attach_id;

					$data[$efurniture_id] = $efurniture_data;

					update_term_meta($client, 'efurniture', $data);

					// Trả về ID hoặc URL nếu bạn cần xử lý tiếp
					$file_url = wp_get_attachment_url($attach_id);
					$response['success'] = true;
					$response['attachment_id'] = $attach_id;
					$response['url'] = $file_url;
					$response['filename'] = $fileName;
				} else {
					$response['msg'] = $attach_id->get_error_message();
				}
			}
		}

		wp_send_json($response);

	}

	public function check_uploaded_chunks() {
		$upload_dir = wp_upload_dir()['basedir'] . '/chunk_temp/';
		$uploadId = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['uploadId']);
		$pattern = $upload_dir . $uploadId . '.part*';

		$chunks = [];
		foreach (glob($pattern) as $file) {
			if (preg_match('/\.part(\d+)$/', $file, $m)) {
				$chunks[] = intval($m[1]);
			}
		}

		wp_send_json($chunks);
	}

	public function ajax_get_edit_efurniture_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$efurniture = isset($_GET['efurniture'])?absint($_GET['efurniture']):0;

		if($client && $efurniture) {
			$data = get_term_meta($client, 'efurniture', true);
			if(empty($data)) $data = [];
			$efurniture_data = isset($data[$efurniture])?$data[$efurniture]:$this->default;
			$efurniture_data += $this->default;


			$file_url = (isset($efurniture_data['file_id']) && $efurniture_data['file_id']!='')?wp_get_attachment_url($efurniture_data['file_id']):'';
			?>
			<form id="frm-edit-efurniture" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="client" name="client" value="<?=$client?>">
				<input type="hidden" id="efurniture" name="efurniture" value="<?=$efurniture?>">
				<?php wp_nonce_field( 'edit-efurniture', 'nonce' ); ?>
				<div id="edit-efurniture-response"></div>
				<div class="mb-3">
					<input class="form-control mb-2" type="text" value="<?php echo ($efurniture_data['required_label']!='')?esc_html($efurniture_data['required_label']):''; ?>" name="efurniture_required_label" id="efurniture_required_label" placeholder="Ghi chú ngày 1">
					<input class="form-control" type="date" value="<?php echo ($efurniture_data['required']!='')?esc_html(date('Y-m-d', strtotime($efurniture_data['required']))):''; ?>" name="efurniture_required" id="efurniture_required">
				</div>
				<div class="mb-3">
					<input class="form-control mb-2" type="text" value="<?php echo ($efurniture_data['received_label']!='')?esc_html($efurniture_data['received_label']):''; ?>" name="efurniture_received_label" id="efurniture_received_label" placeholder="Ghi chú ngày 2">
					<input class="form-control" type="date" value="<?php echo ($efurniture_data['received']!='')?esc_html(date('Y-m-d', strtotime($efurniture_data['received']))):''; ?>" name="efurniture_received" id="efurniture_received">
				</div>
				<div class="mb-3">
					<input class="form-control mb-2" type="text" value="<?php echo ($efurniture_data['completed_label']!='')?esc_html($efurniture_data['completed_label']):''; ?>" name="efurniture_completed_label" id="efurniture_completed_label" placeholder="Ghi chú ngày 3">
					<input class="form-control" type="date" value="<?php echo ($efurniture_data['completed']!='')?esc_html(date('Y-m-d', strtotime($efurniture_data['completed']))):''; ?>" name="efurniture_completed" id="efurniture_completed">
				</div>
				<div class="mb-3">
					<input class="form-control mb-2" type="text" value="<?php echo ($efurniture_data['sent_label']!='')?esc_html($efurniture_data['sent_label']):''; ?>" name="efurniture_sent_label" id="efurniture_sent_label" placeholder="Ghi chú ngày 4">
					<input class="form-control" type="date" value="<?php echo ($efurniture_data['sent']!='')?esc_html(date('Y-m-d', strtotime($efurniture_data['sent']))):''; ?>" name="efurniture_sent" id="efurniture_sent">
				</div>
				<div class="mb-3">
					Giá trị
					<input type="text" id="efurniture_value" name="efurniture_value" class="form-control" value="<?php echo esc_attr($efurniture_data['value']); ?>">
				</div>
				<div class="mb-3">
					Ghi chú
					<input type="text" id="efurniture_unit" name="efurniture_unit" class="form-control" value="<?php echo esc_attr($efurniture_data['unit']); ?>">
				</div>
				<div class="mb-3">
					Link nhóm zalo
					<input type="text" id="efurniture_zalo" name="efurniture_zalo" class="form-control" value="<?php echo esc_attr($efurniture_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					Link dự toán 1
					<input type="text" id="efurniture_url" name="efurniture_url" class="form-control" value="<?php echo ($efurniture_data['url'])?esc_url($efurniture_data['url']):''; ?>">
				</div>
				<div class="mb-3">
					Link dự toán 2
					<input type="text" id="efurniture_url2" name="efurniture_url2" class="form-control" value="<?php echo ($efurniture_data['url2'])?esc_url($efurniture_data['url2']):''; ?>">
				</div>
				<div class="mb-3">
					Link dự toán 3
					<input type="text" id="efurniture_url3" name="efurniture_url3" class="form-control" value="<?php echo ($efurniture_data['url3'])?esc_url($efurniture_data['url3']):''; ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dữ liệu</div>
					<div class="row row-cols-2 g-0 p-2 border rounded-2">
						<div class="col">
							<div id="attachment-uploaded">
								<input type="hidden" id="efurniture_file_id" name="efurniture_file_id" value="<?=esc_attr($efurniture_data['file_id'])?>">
								<!-- <input type="hidden" id="efurniture_file_id_new" name="efurniture_file_id_new" value=""> -->
								<div class="input-group input-group-sm">
									<div class="form-control text-truncate">
										<?php
										if($file_url) {
											echo esc_html(basename($file_url));
										}
										?>	
									</div>
									<button class="btn btn-sm btn-warning" id="efurniture_remove_file" type="button" <?php disabled( '', $file_url, true ); ?>>Xóa file</button>
								</div>
								
							</div>
							<div id="attachment-uploaded-error" class="d-none"></div>
							<div id="attachment-upload-bar" class="d-none">
								<div class="progress flex-grow-1 position-relative" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="height: 31px;">
									<div class="progress-bar"></div>
									<div class="percent position-absolute start-50 top-50 translate-middle text-danger"></div>
								</div>
								<button type="button" class="abort btn btn-sm btn-danger ms-2">Hủy</button>
							</div>
						</div>
						<label class="col d-block ps-5" for="efurniture_file">
							<div class="input-group input-group-sm">
								<div class="form-control text-nowrap text-truncate">Chọn file cần tải lên</div>
								<span class="btn btn-primary">Bấm tải lên</span>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<input type="file" id="efurniture_file" name="efurniture_file" class="form-control">
							</div>
						</label>
					</div>
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="efurniture_quote" id="efurniture_quote" <?php checked( ($efurniture_data['quote']=='yes'), true, true ); ?>>
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
