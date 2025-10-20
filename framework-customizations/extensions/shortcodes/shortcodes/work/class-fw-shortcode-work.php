<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Work extends FW_Shortcode
{
	public $default = [
		'content'=>'',
		'proc1_label'=>'',
		'proc1'=>'',
		'proc2_label'=>'',
		'proc2'=>'',
		'proc3_label'=>'',
		'proc3'=>'',
		'proc4_label'=>'',
		'proc4'=>'',
		'note'=>'',
		'zalo'=>'',
		'url1'=>'',
		'url2'=>'',
		'url3'=>'',
		'url4'=>'',
		'url5'=>'',
		'file_id'=>'',
	];

	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_work_form', [$this, 'ajax_get_edit_work_form']);
      add_action( 'wp_ajax_update_work', [$this, 'ajax_update_work']);
      add_action( 'wp_ajax_get_work_info', [$this, 'ajax_get_work_info']);
      add_action( 'wp_ajax_work_show', [$this, 'ajax_work_show']);
      add_action( 'wp_ajax_work_toggle', [$this, 'ajax_work_toggle']);

      add_action( 'wp_ajax_work_chunk_upload', [$this, 'handle_work_chunk_upload'] );
      add_action( 'wp_ajax_work_check_chunks', [$this, 'check_uploaded_chunks'] );
      //add_action( 'wp_ajax_work_file_upload', [$this, 'ajax_work_file_upload']);
	}

	public function ajax_work_toggle() {
		global $current_employee;
		$work_id = isset($_POST['work']) ? absint($_POST['work']) : 0;
		$response = 0;
		
		//debug_log($current_employee);

		if(current_user_can('edit_works') && $current_employee && $work_id && check_ajax_referer( 'global', 'nonce', false )) {

			$work_removed = get_term_meta($current_employee->term_id, 'work_removed', true);
			if(empty($work_removed)) $work_removed = [];

			if(in_array($work_id, $work_removed)) {
				unset($work_removed[array_search($work_id, $work_removed)]);
				$response = -1;
			} else {
				$work_removed[] = $work_id;
				$response = 1;
			}

			update_term_meta($current_employee->term_id, 'work_removed', $work_removed);

		}
		wp_send_json($response);
	}

	public function ajax_work_show() {
		global $current_employee;
		$work_id = isset($_POST['work']) ? absint($_POST['work']) : 0;
		$response = 0;
		if(current_user_can('edit_works') && $current_employee && $work_id && check_ajax_referer( 'global', 'nonce', false )) {

			$work_show = get_term_meta($current_employee->term_id, 'work_show', true);
			if(empty($work_show)) $work_show = [];

			if(in_array($work_id, $work_show)) {
				unset($work_show[array_search($work_id, $work_show)]);
				$response = -1;
			} else {
				$work_show[] = $work_id;
				$response = 1;
			}

			update_term_meta($current_employee->term_id, 'work_show', $work_show);

		}
		wp_send_json($response);
	}

	public function ajax_get_work_info() {

		$employee = isset($_GET['employee'])?absint($_GET['employee']):0;
		$work = isset($_GET['work'])?absint($_GET['work']):0;

		$response = [
			'content' => '',
			'info' => '',
			'zalo' => '',
			'proc1' => '',
			'proc2' => '',
			'proc3' => '',
			'proc4' => '',
			'file_id' => '',
		];
		
		if($employee && $work) {
			$default_zalo = fw_get_db_post_option($work,'work_zalo');
			$default_data = [
				'note' => fw_get_db_post_option($work,'work_note'),
			];

			$data = get_term_meta($employee, 'work', true);
			if(empty($data)) $data = [];
			$work_data = isset($data[$work])?$data[$work]:$this->default;
			$work_data += $this->default;

			if(empty($work_data['note'])) $work_data['note'] = $default_data['note'];

			$response['zalo'] = ($work_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($work_data['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['zalo'] .= ($default_zalo)?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($default_zalo).'" target="_blank">Zalo</a>':'';

			$response['file'] = ($work_data['file_id'])?'<a class="btn-shadow btn btn-sm btn-primary fw-bold me-1" href="'.esc_url(wp_get_attachment_url($work_data['file_id'])).'" target="_blank">Tải</a>':'';
			
			$response['proc1'] = (isset($work_data['proc1']) && $work_data['proc1']!='')?'<div class="bg-danger" title="'.esc_attr($work_data['proc1_label']).'">'.esc_html(date('d/m', strtotime($work_data['proc1']))).'</div>':'';
			$response['proc2'] = (isset($work_data['proc2']) && $work_data['proc2']!='')?'<div class="bg-danger" title="'.esc_attr($work_data['proc2_label']).'">'.esc_html(date('d/m', strtotime($work_data['proc2']))).'</div>':'';
			$response['proc3'] = (isset($work_data['proc3']) && $work_data['proc3']!='')?'<div class="bg-danger" title="'.esc_attr($work_data['proc3_label']).'">'.esc_html(date('d/m', strtotime($work_data['proc3']))).'</div>':'';
			$response['proc4'] = (isset($work_data['proc4']) && $work_data['proc4']!='')?'<div class="bg-danger" title="'.esc_attr($work_data['proc4_label']).'">'.esc_html(date('d/m', strtotime($work_data['proc4']))).'</div>':'';

			ob_start();
			
			if($work_data['content']!='') {
				$content = '<div class="copy-text">'.wp_get_the_content($work_data['content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
				?>
				<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($content)?>" data-bs-html="true">ÁP DỤNG</button>
				<?php
			}

			$response['content'] = ob_get_clean();

			ob_start();
		?>
			<div class="work-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $work )); ?>
			</div>
			<?php if($work_data['note']) { ?>
			<div class="work-note mb-1">
				<div class="text-red fw-bold"><?php echo esc_html($work_data['note']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center work-url1 mb-3">
				<?php
				if(isset($work_data['url1']) && $work_data['url1']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url1'])?>" target="_blank">Link 1</a>
					<?php
				}

				if(isset($work_data['url2']) && $work_data['url2']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url2'])?>" target="_blank">Link 2</a>
					<?php
				}

				if(isset($work_data['url3']) && $work_data['url3']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url3'])?>" target="_blank">Link 3</a>
					<?php
				}

				if(isset($work_data['url4']) && $work_data['url4']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url4'])?>" target="_blank">Link 4</a>
					<?php
				}

				if(isset($work_data['url5']) && $work_data['url5']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url5'])?>" target="_blank">Link 5</a>
					<?php
				}

				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_work() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(current_user_can('edit_works') && check_ajax_referer( 'edit-work', 'nonce', false )) {
			$employee = isset($_POST['employee'])?absint($_POST['employee']):0;
			$work_id = isset($_POST['work'])?absint($_POST['work']):0;
			$work_content = isset($_POST['work_content'])?wp_kses_post($_POST['work_content']):'';
			$work_note = isset($_POST['work_note'])?sanitize_text_field($_POST['work_note']):'';
			$work_zalo = isset($_POST['work_zalo'])?sanitize_text_field($_POST['work_zalo']):'';
			$work_url1 = isset($_POST['work_url1'])?sanitize_url($_POST['work_url1']):'';
			$work_url2 = isset($_POST['work_url2'])?sanitize_url($_POST['work_url2']):'';
			$work_url3 = isset($_POST['work_url3'])?sanitize_url($_POST['work_url3']):'';
			$work_url4 = isset($_POST['work_url4'])?sanitize_url($_POST['work_url4']):'';
			$work_url5 = isset($_POST['work_url5'])?sanitize_url($_POST['work_url5']):'';
			
			$work_file_id = isset($_POST['work_file_id'])?absint($_POST['work_file_id']):0;
			
			$work_proc1_label = isset($_POST['work_proc1_label']) ? $_POST['work_proc1_label'] : '';
			$work_proc1 = isset($_POST['work_proc1']) ? $_POST['work_proc1'] : '';
			$work_proc2_label = isset($_POST['work_proc2_label']) ? $_POST['work_proc2_label'] : '';
			$work_proc2 = isset($_POST['work_proc2']) ? $_POST['work_proc2'] : '';
			$work_proc3_label = isset($_POST['work_proc3_label']) ? $_POST['work_proc3_label'] : '';
			$work_proc3 = isset($_POST['work_proc3']) ? $_POST['work_proc3'] : '';
			$work_proc4_label = isset($_POST['work_proc4_label']) ? $_POST['work_proc4_label'] : '';
			$work_proc4 = isset($_POST['work_proc4']) ? $_POST['work_proc4'] : '';
			
			if($employee && $work_id) {
				$data = get_term_meta($employee, 'work', true);
				if(empty($data)) $data = [];
				$work_data = isset($data[$work_id])?$data[$work_id]:$this->default;
				$work_data += $this->default;

				$new_work_data = [
					'content' => $work_content,
					'proc1_label' => $work_proc1_label,
					'proc1' => $work_proc1,
					'proc2_label' => $work_proc2_label,
					'proc2' => $work_proc2,
					'proc3_label' => $work_proc3_label,
					'proc3' => $work_proc3,
					'proc4_label' => $work_proc4_label,
					'proc4' => $work_proc4,
					'note' => $work_note,
					'zalo' => $work_zalo,
					'url1' => $work_url1,
					'url2' => $work_url2,
					'url3' => $work_url3,
					'url4' => $work_url4,
					'url5' => $work_url5,
					'file_id' => ($work_file_id!=0)?$work_file_id:'',
				];

				if($new_work_data['file_id']=='' || $new_work_data['file_id']==0) {
					if(isset($work_data['file_id']) && $work_data['file_id']) wp_delete_attachment($work_data['file_id'], true);
				}

				$data[$work_id] = $new_work_data;

				update_term_meta($employee, 'work', $data);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_work_file_upload() {
		$response = [
			'status' => 500,
			'message' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(!current_user_can('edit_works') || !check_ajax_referer( 'edit-work', 'nonce', false )) {
			$response['status'] = 403;
			$response['message'] = "Forbiden.";
			wp_send_json( $response, 403 );
		}

		$employee = isset($_POST['employee'])?absint($_POST['employee']):0;
		$work_id = isset($_POST['work'])?absint($_POST['work']):0;

		wp_send_json( $response, $response['status'] );
	}

	public function handle_work_chunk_upload() {
		$response = ['success' => false, 'attachment_id' => 0, 'url' => '', 'filename' => '', 'msg' => ''];

		if(current_user_can('edit_works') && check_ajax_referer( 'edit-work', 'nonce', false )) {
			$employee = isset($_POST['employee'])?absint($_POST['employee']):0;
			$work_id = isset($_POST['work'])?absint($_POST['work']):0;

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
					$data = get_term_meta($employee, 'work', true);
					if(empty($data)) $data = [];
					$work_data = isset($data[$work_id])?$data[$work_id]:$this->default;
					$work_data += $this->default;

					if($work_data['file_id']) wp_delete_attachment($work_data['file_id'], true);

					$work_data['file_id'] = $attach_id;

					$data[$work_id] = $work_data;

					update_term_meta($employee, 'work', $data);

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

	public function ajax_get_edit_work_form() {
		$employee = isset($_GET['employee'])?absint($_GET['employee']):0;
		$work = isset($_GET['work'])?absint($_GET['work']):0;

		if($employee && $work) {
			$data = get_term_meta($employee, 'work', true);
			if(empty($data)) $data = [];
			$work_data = isset($data[$work])?$data[$work]:$this->default;
			$work_data += $this->default;

			$file_url = (isset($work_data['file_id']) && $work_data['file_id']!='')?wp_get_attachment_url($work_data['file_id']):'';
			?>
			<form id="frm-edit-work" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="employee" name="employee" value="<?=$employee?>">
				<input type="hidden" id="work" name="work" value="<?=$work?>">
				<?php wp_nonce_field( 'edit-work', 'nonce' ); ?>
				<div id="edit-work-response"></div>
				<div class="row">
					<div class="col-lg-7<?php echo (!current_user_can('edit_works'))?' hidden':''; ?>">
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
							wp_editor( $work_data['content'], 'work_content', $settings);
							?>
							<input type="hidden" id="work_content_settings" value="<?=esc_attr(json_encode($settings))?>">
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
					<div class="<?php echo (!current_user_can('edit_works'))?' col-lg-12':'col-lg-5'; ?>">
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($work_data['proc1_label']!='')?esc_html($work_data['proc1_label']):''; ?>" name="work_proc1_label" id="work_proc1_label" placeholder="Ghi chú ngày 1">
							<input class="form-control" type="date" value="<?php echo ($work_data['proc1']!='')?esc_html(date('Y-m-d', strtotime($work_data['proc1']))):''; ?>" name="work_proc1" id="work_proc1">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($work_data['proc2_label']!='')?esc_html($work_data['proc2_label']):''; ?>" name="work_proc2_label" id="work_proc2_label" placeholder="Ghi chú ngày 2">
							<input class="form-control" type="date" value="<?php echo ($work_data['proc2']!='')?esc_html(date('Y-m-d', strtotime($work_data['proc2']))):''; ?>" name="work_proc2" id="work_proc2">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($work_data['proc3_label']!='')?esc_html($work_data['proc3_label']):''; ?>" name="work_proc3_label" id="work_proc3_label" placeholder="Ghi chú ngày 3">
							<input class="form-control" type="date" value="<?php echo ($work_data['proc3']!='')?esc_html(date('Y-m-d', strtotime($work_data['proc3']))):''; ?>" name="work_proc3" id="work_proc3">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($work_data['proc4_label']!='')?esc_html($work_data['proc4_label']):''; ?>" name="work_proc4_label" id="work_proc4_label" placeholder="Ghi chú ngày 4">
							<input class="form-control" type="date" value="<?php echo ($work_data['proc4']!='')?esc_html(date('Y-m-d', strtotime($work_data['proc4']))):''; ?>" name="work_proc4" id="work_proc4">
						</div>
						<div class="mb-3">
							Ghi chú
							<input type="text" id="work_note" name="work_note" class="form-control" value="<?php echo esc_attr($work_data['note']); ?>">
						</div>
						<div class="mb-3">
							Link nhóm zalo
							<input type="text" id="work_zalo" name="work_zalo" class="form-control" value="<?php echo esc_attr($work_data['zalo']); ?>">
						</div>
						<div class="mb-3">
							Link công việc 1
							<input type="text" id="work_url1" name="work_url1" class="form-control" value="<?php echo ($work_data['url1'])?esc_url($work_data['url1']):''; ?>">
						</div>
						<div class="mb-3">
							Link công việc 2
							<input type="text" id="work_url2" name="work_url2" class="form-control" value="<?php echo ($work_data['url2'])?esc_url($work_data['url2']):''; ?>">
						</div>
						<div class="mb-3">
							Link công việc 3
							<input type="text" id="work_url3" name="work_url3" class="form-control" value="<?php echo ($work_data['url3'])?esc_url($work_data['url3']):''; ?>">
						</div>
						<div class="mb-3">
							Link công việc 4
							<input type="text" id="work_url4" name="work_url4" class="form-control" value="<?php echo ($work_data['url4'])?esc_url($work_data['url4']):''; ?>">
						</div>
						<div class="mb-3">
							Link công việc 5
							<input type="text" id="work_url5" name="work_url5" class="form-control" value="<?php echo ($work_data['url5'])?esc_url($work_data['url5']):''; ?>">
						</div>
					</div>
					<div class="col-lg-12">
						<div class="mb-3">
							<div class="form-label mb-1">File công việc</div>
							<div class="row row-cols-2 g-0 p-2 border rounded-2">
								<div class="col">
									<div id="attachment-uploaded">
										<input type="hidden" id="work_file_id" name="work_file_id" value="<?=esc_attr($work_data['file_id'])?>">
										<div class="input-group input-group-sm">
											<div class="form-control text-truncate">
												<?php
												if($file_url) {
													echo esc_html(basename($file_url));
												}
												?>	
											</div>
											<button class="btn btn-sm btn-warning" id="work_remove_file" type="button" <?php disabled( '', $file_url, true ); ?>>Xóa file</button>
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
								<label class="col d-block ps-5" for="work_file">
									<div class="input-group input-group-sm">
										<div class="form-control text-nowrap text-truncate">Chọn file cần tải lên</div>
										<span class="btn btn-primary">Bấm tải lên</span>
									</div>
									<div style="width: 0;height: 0;overflow: hidden;">
										<input type="file" id="work_file" name="work_file" class="form-control">
									</div>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-work-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-work" tabindex="-1" role="dialog" aria-labelledby="edit-work-label">
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-work-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
