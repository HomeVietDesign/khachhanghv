<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Elighting extends FW_Shortcode
{
	public $default = [
		'required_content'=>'',
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
	];

	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_elighting_form', [$this, 'ajax_get_edit_elighting_form']);
      add_action( 'wp_ajax_update_elighting', [$this, 'ajax_update_elighting']);
      add_action( 'wp_ajax_get_elighting_info', [$this, 'ajax_get_elighting_info']);
      add_action( 'wp_ajax_elighting_hide', [$this, 'ajax_elighting_hide']);
      add_action( 'wp_ajax_elighting_toggle', [$this, 'ajax_elighting_toggle']);

      add_action( 'wp_ajax_elighting_chunk_upload', [$this, 'handle_elighting_chunk_upload'] );
      add_action( 'wp_ajax_elighting_check_chunks', [$this, 'check_uploaded_chunks'] );
      //add_action( 'wp_ajax_elighting_file_upload', [$this, 'ajax_elighting_file_upload']);
	}

	public function ajax_elighting_toggle() {
		global $current_client;
		$elighting_id = isset($_POST['elighting']) ? absint($_POST['elighting']) : 0;
		$response = 0;
		if(current_user_can('edit_elightings') && $current_client && $elighting_id && check_ajax_referer( 'global', 'nonce', false )) {

			$elighting_removed = get_term_meta($current_client->term_id, 'elighting_removed', true);
			if(empty($elighting_removed)) $elighting_removed = [];

			if(in_array($elighting_id, $elighting_removed)) {
				unset($elighting_removed[array_search($elighting_id, $elighting_removed)]);
				$response = -1;
			} else {
				$elighting_removed[] = $elighting_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'elighting_removed', $elighting_removed);

		}
		wp_send_json($response);
	}

	public function ajax_elighting_hide() {
		global $current_client;
		$elighting_id = isset($_POST['elighting']) ? absint($_POST['elighting']) : 0;
		$response = 0;
		if(current_user_can('edit_elightings') && $current_client && $elighting_id && check_ajax_referer( 'global', 'nonce', false )) {

			$elighting_hide = get_term_meta($current_client->term_id, 'elighting_hide', true);
			if(empty($elighting_hide)) $elighting_hide = [];

			if(in_array($elighting_id, $elighting_hide)) {
				unset($elighting_hide[array_search($elighting_id, $elighting_hide)]);
				$response = -1;
			} else {
				$elighting_hide[] = $elighting_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'elighting_hide', $elighting_hide);

		}
		wp_send_json($response);
	}

	public function ajax_get_elighting_info() {

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$elighting = isset($_GET['elighting'])?absint($_GET['elighting']):0;

		$response = [
			'required_content' => '',
			'info' => '',
			'zalo' => '',
			'required' => '',
			'received' => '',
			'completed' => '',
			'sent' => '',
			'file_id' => '',
		];
		
		if($client && $elighting) {
			$default_elighting_file = fw_get_db_post_option($elighting,'elighting_file');
			$default_zalo = fw_get_db_post_option($elighting,'elighting_zalo');
			$default_data = [
				'value' => fw_get_db_post_option($elighting,'elighting_value'),
				'unit' => fw_get_db_post_option($elighting,'elighting_unit'),
				'file_id' => (!empty($default_elighting_file))?$default_elighting_file['attachment_id']:'',
			];

			$data = get_term_meta($client, 'elighting', true);
			if(empty($data)) $data = [];
			$elighting_data = isset($data[$elighting])?$data[$elighting]:$this->default;
			$elighting_data += $this->default;

			if(empty($elighting_data['value'])) $elighting_data['value'] = $default_data['value'];
			if(empty($elighting_data['unit'])) $elighting_data['unit'] = $default_data['unit'];
			if(empty($elighting_data['file_id'])) $elighting_data['file_id'] = $default_data['file_id'];

			$response['zalo'] = ($elighting_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($elighting_data['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['zalo'] .= ($default_zalo)?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($default_zalo).'" target="_blank">Zalo</a>':'';

			$response['file'] = ($elighting_data['file_id'])?'<a class="btn-shadow btn btn-sm btn-primary fw-bold me-1" href="'.esc_url(wp_get_attachment_url($elighting_data['file_id'])).'" target="_blank">Tải</a>':'';
			
			$response['required'] = (isset($elighting_data['required']) && $elighting_data['required']!='')?'<div class="bg-danger" title="'.esc_attr($elighting_data['required_label']).'">'.esc_html(date('d/m', strtotime($elighting_data['required']))).'</div>':'';
			$response['received'] = (isset($elighting_data['received']) && $elighting_data['received']!='')?'<div class="bg-danger" title="'.esc_attr($elighting_data['received_label']).'">'.esc_html(date('d/m', strtotime($elighting_data['received']))).'</div>':'';
			$response['completed'] = (isset($elighting_data['completed']) && $elighting_data['completed']!='')?'<div class="bg-danger" title="'.esc_attr($elighting_data['completed_label']).'">'.esc_html(date('d/m', strtotime($elighting_data['completed']))).'</div>':'';
			$response['sent'] = (isset($elighting_data['sent']) && $elighting_data['sent']!='')?'<div class="bg-danger" title="'.esc_attr($elighting_data['sent_label']).'">'.esc_html(date('d/m', strtotime($elighting_data['sent']))).'</div>':'';

			ob_start();
			
			if($elighting_data['required_content']!='') {
				$required_content = '<div class="copy-text">'.wp_get_the_content($elighting_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
				?>
				<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
				<?php
			}

			$response['required_content'] = ob_get_clean();

			ob_start();
		?>
			<div class="elighting-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $elighting )); ?>
			</div>
			<?php if($elighting_data['value']) { ?>
			<div class="elighting-value mb-1">
				<span class="text-red fw-bold"><?php echo esc_html($elighting_data['value']); ?></span>
			</div>
			<?php } ?>
			<?php if($elighting_data['unit']) { ?>
			<div class="elighting-unit mb-1">
				<div class="text-red fw-bold"><?php echo esc_html($elighting_data['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center elighting-url mb-3">
				<?php
				if(isset($elighting_data['url']) && $elighting_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($elighting_data['url'])?>" target="_blank">Dự toán 1</a>
					<?php
				}

				if(isset($elighting_data['url2']) && $elighting_data['url2']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($elighting_data['url2'])?>" target="_blank">Dự toán 2</a>
					<?php
				}

				if(isset($elighting_data['url3']) && $elighting_data['url3']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($elighting_data['url3'])?>" target="_blank">Dự toán 3</a>
					<?php
				}

				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_elighting() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(current_user_can('edit_elightings') && check_ajax_referer( 'edit-elighting', 'nonce', false )) {
			$client = isset($_POST['client'])?absint($_POST['client']):0;
			$elighting_id = isset($_POST['elighting'])?absint($_POST['elighting']):0;
			$required_content = isset($_POST['required_content'])?wp_kses_post($_POST['required_content']):'';
			$elighting_value = isset($_POST['elighting_value'])?sanitize_text_field($_POST['elighting_value']):'';
			$elighting_unit = isset($_POST['elighting_unit'])?sanitize_text_field($_POST['elighting_unit']):'';
			$elighting_zalo = isset($_POST['elighting_zalo'])?sanitize_text_field($_POST['elighting_zalo']):'';
			$elighting_url = isset($_POST['elighting_url'])?sanitize_url($_POST['elighting_url']):'';
			$elighting_url2 = isset($_POST['elighting_url2'])?sanitize_url($_POST['elighting_url2']):'';
			$elighting_url3 = isset($_POST['elighting_url3'])?sanitize_url($_POST['elighting_url3']):'';
			
			$elighting_file_id = isset($_POST['elighting_file_id'])?absint($_POST['elighting_file_id']):0;
			
			$elighting_required_label = isset($_POST['elighting_required_label']) ? $_POST['elighting_required_label'] : '';
			$elighting_required = isset($_POST['elighting_required']) ? $_POST['elighting_required'] : '';
			$elighting_received_label = isset($_POST['elighting_received_label']) ? $_POST['elighting_received_label'] : '';
			$elighting_received = isset($_POST['elighting_received']) ? $_POST['elighting_received'] : '';
			$elighting_completed_label = isset($_POST['elighting_completed_label']) ? $_POST['elighting_completed_label'] : '';
			$elighting_completed = isset($_POST['elighting_completed']) ? $_POST['elighting_completed'] : '';
			$elighting_sent_label = isset($_POST['elighting_sent_label']) ? $_POST['elighting_sent_label'] : '';
			$elighting_sent = isset($_POST['elighting_sent']) ? $_POST['elighting_sent'] : '';
			
			if($client && $elighting_id) {
				$data = get_term_meta($client, 'elighting', true);
				if(empty($data)) $data = [];
				$elighting_data = isset($data[$elighting_id])?$data[$elighting_id]:$this->default;
				$elighting_data += $this->default;

				$new_elighting_data = [
					'required_content' => $required_content,
					'required_label' => $elighting_required_label,
					'required' => $elighting_required,
					'received_label' => $elighting_received_label,
					'received' => $elighting_received,
					'completed_label' => $elighting_completed_label,
					'completed' => $elighting_completed,
					'sent_label' => $elighting_sent_label,
					'sent' => $elighting_sent,
					'value' => $elighting_value,
					'unit' => $elighting_unit,
					'zalo' => $elighting_zalo,
					'url' => $elighting_url,
					'url2' => $elighting_url2,
					'url3' => $elighting_url3,
					'file_id' => ($elighting_file_id!=0)?$elighting_file_id:'',
				];

				if($new_elighting_data['file_id']=='' || $new_elighting_data['file_id']==0) {
					if(isset($elighting_data['file_id']) && $elighting_data['file_id']) wp_delete_attachment($elighting_data['file_id'], true);
				}

				$data[$elighting_id] = $new_elighting_data;

				update_term_meta($client, 'elighting', $data);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_elighting_file_upload() {
		$response = [
			'status' => 500,
			'message' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(!current_user_can('edit_elightings') || !check_ajax_referer( 'edit-elighting', 'nonce', false )) {
			$response['status'] = 403;
			$response['message'] = "Forbiden.";
			wp_send_json( $response, 403 );
		}

		$client = isset($_POST['client'])?absint($_POST['client']):0;
		$elighting_id = isset($_POST['elighting'])?absint($_POST['elighting']):0;

		wp_send_json( $response, $response['status'] );
	}

	public function handle_elighting_chunk_upload() {
		$response = ['success' => false, 'attachment_id' => 0, 'url' => '', 'filename' => '', 'msg' => ''];

		if(current_user_can('edit_elightings') && check_ajax_referer( 'edit-elighting', 'nonce', false )) {
			$client = isset($_POST['client'])?absint($_POST['client']):0;
			$elighting_id = isset($_POST['elighting'])?absint($_POST['elighting']):0;

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
					$data = get_term_meta($client, 'elighting', true);
					if(empty($data)) $data = [];
					$elighting_data = isset($data[$elighting_id])?$data[$elighting_id]:$this->default;
					$elighting_data += $this->default;

					if($elighting_data['file_id']) wp_delete_attachment($elighting_data['file_id'], true);

					$elighting_data['file_id'] = $attach_id;

					$data[$elighting_id] = $elighting_data;

					update_term_meta($client, 'elighting', $data);

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

	public function ajax_get_edit_elighting_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$elighting = isset($_GET['elighting'])?absint($_GET['elighting']):0;

		if($client && $elighting) {
			$data = get_term_meta($client, 'elighting', true);
			if(empty($data)) $data = [];
			$elighting_data = isset($data[$elighting])?$data[$elighting]:$this->default;
			$elighting_data += $this->default;


			$file_url = (isset($elighting_data['file_id']) && $elighting_data['file_id']!='')?wp_get_attachment_url($elighting_data['file_id']):'';
			?>
			<form id="frm-edit-elighting" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="client" name="client" value="<?=$client?>">
				<input type="hidden" id="elighting" name="elighting" value="<?=$elighting?>">
				<?php wp_nonce_field( 'edit-elighting', 'nonce' ); ?>
				<div id="edit-elighting-response"></div>
				<div class="row">
					<div class="col-lg-7<?php echo (!current_user_can('edit_contractors'))?' hidden':''; ?>">
						<div class="mb-3">
							Nội dung áp dụng
							<?php
							$settings = [
								'media_buttons' => false,
								'teeny'         => false,
								'quicktags'     => false,
								'editor_height' => '730',
								'tinymce'       => [
									'toolbar1' => 'bold,italic,underline,alignleft,aligncenter,alignright,alignjustify,link,unlink,bullist,numlist,undo,redo,fullscreen',
									'toolbar2' => 'forecolor,pastetext,removeformat,charmap',
									'content_style' => 'body { font-family: Arial, Helvetica, sans-serif; font-size: 16px; }'
								]
							];
							wp_editor( $elighting_data['required_content'], 'required_content', $settings);
							?>
							<input type="hidden" id="required_content_settings" value="<?=esc_attr(json_encode($settings))?>">
						</div>
						<style>
							.mce-container, .mce-container *, .mce-widget, .mce-widget * {
								color: #333;
							}
							.mce-menu .mce-menu-item.mce-active.mce-menu-item-normal, .mce-menu .mce-menu-item.mce-active.mce-menu-item-preview, .mce-menu .mce-menu-item.mce-selected, .mce-menu .mce-menu-item:focus, .mce-menu .mce-menu-item:hover {
								color: #fff;
							}
							.mce-widget.mce-tooltip {
								color: #fff;
							}
						</style>
					</div>
					<div class="<?php echo (!current_user_can('edit_contractors'))?' col-lg-12':'col-lg-5'; ?>">
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($elighting_data['required_label']!='')?esc_html($elighting_data['required_label']):''; ?>" name="elighting_required_label" id="elighting_required_label" placeholder="Ghi chú ngày 1">
							<input class="form-control" type="date" value="<?php echo ($elighting_data['required']!='')?esc_html(date('Y-m-d', strtotime($elighting_data['required']))):''; ?>" name="elighting_required" id="elighting_required">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($elighting_data['received_label']!='')?esc_html($elighting_data['received_label']):''; ?>" name="elighting_received_label" id="elighting_received_label" placeholder="Ghi chú ngày 2">
							<input class="form-control" type="date" value="<?php echo ($elighting_data['received']!='')?esc_html(date('Y-m-d', strtotime($elighting_data['received']))):''; ?>" name="elighting_received" id="elighting_received">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($elighting_data['completed_label']!='')?esc_html($elighting_data['completed_label']):''; ?>" name="elighting_completed_label" id="elighting_completed_label" placeholder="Ghi chú ngày 3">
							<input class="form-control" type="date" value="<?php echo ($elighting_data['completed']!='')?esc_html(date('Y-m-d', strtotime($elighting_data['completed']))):''; ?>" name="elighting_completed" id="elighting_completed">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($elighting_data['sent_label']!='')?esc_html($elighting_data['sent_label']):''; ?>" name="elighting_sent_label" id="elighting_sent_label" placeholder="Ghi chú ngày 4">
							<input class="form-control" type="date" value="<?php echo ($elighting_data['sent']!='')?esc_html(date('Y-m-d', strtotime($elighting_data['sent']))):''; ?>" name="elighting_sent" id="elighting_sent">
						</div>
						<div class="mb-3">
							Tên - Số điện thoại
							<input type="text" id="elighting_value" name="elighting_value" class="form-control" value="<?php echo esc_attr($elighting_data['value']); ?>">
						</div>
						<div class="mb-3">
							Ghi chú
							<input type="text" id="elighting_unit" name="elighting_unit" class="form-control" value="<?php echo esc_attr($elighting_data['unit']); ?>">
						</div>
						<div class="mb-3">
							Link nhóm zalo
							<input type="text" id="elighting_zalo" name="elighting_zalo" class="form-control" value="<?php echo esc_attr($elighting_data['zalo']); ?>">
						</div>
						<div class="mb-3">
							Link dự toán 1
							<input type="text" id="elighting_url" name="elighting_url" class="form-control" value="<?php echo ($elighting_data['url'])?esc_url($elighting_data['url']):''; ?>">
						</div>
						<div class="mb-3">
							Link dự toán 2
							<input type="text" id="elighting_url2" name="elighting_url2" class="form-control" value="<?php echo ($elighting_data['url2'])?esc_url($elighting_data['url2']):''; ?>">
						</div>
						<div class="mb-3">
							Link dự toán 3
							<input type="text" id="elighting_url3" name="elighting_url3" class="form-control" value="<?php echo ($elighting_data['url3'])?esc_url($elighting_data['url3']):''; ?>">
						</div>
					</div>
					<div class="col-lg-12">
						<div class="mb-3">
							<div class="form-label mb-1">File dữ liệu</div>
							<div class="row row-cols-2 g-0 p-2 border rounded-2">
								<div class="col">
									<div id="attachment-uploaded">
										<input type="hidden" id="elighting_file_id" name="elighting_file_id" value="<?=esc_attr($elighting_data['file_id'])?>">
										<!-- <input type="hidden" id="elighting_file_id_new" name="elighting_file_id_new" value=""> -->
										<div class="input-group input-group-sm">
											<div class="form-control text-truncate">
												<?php
												if($file_url) {
													echo esc_html(basename($file_url));
												}
												?>	
											</div>
											<button class="btn btn-sm btn-warning" id="elighting_remove_file" type="button" <?php disabled( '', $file_url, true ); ?>>Xóa file</button>
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
								<label class="col d-block ps-5" for="elighting_file">
									<div class="input-group input-group-sm">
										<div class="form-control text-nowrap text-truncate">Chọn file cần tải lên</div>
										<span class="btn btn-primary">Bấm tải lên</span>
									</div>
									<div style="width: 0;height: 0;overflow: hidden;">
										<input type="file" id="elighting_file" name="elighting_file" class="form-control">
									</div>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-elighting-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-elighting" tabindex="-1" role="dialog" aria-labelledby="edit-elighting-label">
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-elighting-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
