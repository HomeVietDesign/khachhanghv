<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Rebate extends FW_Shortcode
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
      add_action( 'wp_ajax_get_edit_rebate_form', [$this, 'ajax_get_edit_rebate_form']);
      add_action( 'wp_ajax_update_rebate', [$this, 'ajax_update_rebate']);
      add_action( 'wp_ajax_get_rebate_info', [$this, 'ajax_get_rebate_info']);
      add_action( 'wp_ajax_rebate_show', [$this, 'ajax_rebate_show']);
      add_action( 'wp_ajax_rebate_toggle', [$this, 'ajax_rebate_toggle']);

      add_action( 'wp_ajax_rebate_chunk_upload', [$this, 'handle_rebate_chunk_upload'] );
      add_action( 'wp_ajax_rebate_check_chunks', [$this, 'check_uploaded_chunks'] );
      //add_action( 'wp_ajax_rebate_file_upload', [$this, 'ajax_rebate_file_upload']);
	}

	public function ajax_rebate_toggle() {
		global $current_product;
		$rebate_id = isset($_POST['rebate']) ? absint($_POST['rebate']) : 0;
		$response = 0;
		
		//debug_log($current_product);

		if(current_user_can('edit_rebates') && $current_product && $rebate_id && check_ajax_referer( 'global', 'nonce', false )) {

			$rebate_removed = get_term_meta($current_product->term_id, 'rebate_removed', true);
			if(empty($rebate_removed)) $rebate_removed = [];

			if(in_array($rebate_id, $rebate_removed)) {
				unset($rebate_removed[array_search($rebate_id, $rebate_removed)]);
				$response = -1;
			} else {
				$rebate_removed[] = $rebate_id;
				$response = 1;
			}

			update_term_meta($current_product->term_id, 'rebate_removed', $rebate_removed);

		}
		wp_send_json($response);
	}

	public function ajax_rebate_show() {
		global $current_product;
		$rebate_id = isset($_POST['rebate']) ? absint($_POST['rebate']) : 0;
		$response = 0;
		if(current_user_can('edit_rebates') && $current_product && $rebate_id && check_ajax_referer( 'global', 'nonce', false )) {

			$rebate_show = get_term_meta($current_product->term_id, 'rebate_show', true);
			if(empty($rebate_show)) $rebate_show = [];

			if(in_array($rebate_id, $rebate_show)) {
				unset($rebate_show[array_search($rebate_id, $rebate_show)]);
				$response = -1;
			} else {
				$rebate_show[] = $rebate_id;
				$response = 1;
			}

			update_term_meta($current_product->term_id, 'rebate_show', $rebate_show);

		}
		wp_send_json($response);
	}

	public function ajax_get_rebate_info() {

		$product = isset($_GET['product'])?absint($_GET['product']):0;
		$rebate = isset($_GET['rebate'])?absint($_GET['rebate']):0;

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
		
		if($product && $rebate) {
			$default_zalo = fw_get_db_post_option($rebate,'rebate_zalo');
			$default_data = [
				'note' => fw_get_db_post_option($rebate,'rebate_note'),
			];

			$data = get_term_meta($product, 'rebate', true);
			if(empty($data)) $data = [];
			$rebate_data = isset($data[$rebate])?$data[$rebate]:$this->default;
			$rebate_data += $this->default;

			if(empty($rebate_data['note'])) $rebate_data['note'] = $default_data['note'];
		
			$response['zalo'] = ($rebate_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($rebate_data['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['zalo'] .= ($default_zalo)?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($default_zalo).'" target="_blank">Zalo</a>':'';

			$response['file'] = ($rebate_data['file_id'])?'<a class="btn-shadow btn btn-sm btn-primary fw-bold me-1" href="'.esc_url(wp_get_attachment_url($rebate_data['file_id'])).'" target="_blank">Tải</a>':'';
			
			$response['proc1'] = (isset($rebate_data['proc1']) && $rebate_data['proc1']!='')?'<div class="bg-danger" title="'.esc_attr($rebate_data['proc1_label']).'">'.esc_html(date('d/m', strtotime($rebate_data['proc1']))).'</div>':'';
			$response['proc2'] = (isset($rebate_data['proc2']) && $rebate_data['proc2']!='')?'<div class="bg-danger" title="'.esc_attr($rebate_data['proc2_label']).'">'.esc_html(date('d/m', strtotime($rebate_data['proc2']))).'</div>':'';
			$response['proc3'] = (isset($rebate_data['proc3']) && $rebate_data['proc3']!='')?'<div class="bg-danger" title="'.esc_attr($rebate_data['proc3_label']).'">'.esc_html(date('d/m', strtotime($rebate_data['proc3']))).'</div>':'';
			$response['proc4'] = (isset($rebate_data['proc4']) && $rebate_data['proc4']!='')?'<div class="bg-danger" title="'.esc_attr($rebate_data['proc4_label']).'">'.esc_html(date('d/m', strtotime($rebate_data['proc4']))).'</div>':'';

			ob_start();
			
			if($rebate_data['content']!='') {
				$content = '<div class="copy-text">'.wp_get_the_content($rebate_data['content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
				?>
				<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($content)?>" data-bs-html="true">ÁP DỤNG</button>
				<?php
			}

			$response['content'] = ob_get_clean();

			ob_start();
		?>
			<div class="rebate-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $rebate )); ?>
			</div>
			<?php if($rebate_data['note']) { ?>
			<div class="rebate-note mb-1">
				<div class="text-red fw-bold"><?php echo esc_html($rebate_data['note']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center rebate-url1 mb-3">
				<?php
				if(isset($rebate_data['url1']) && $rebate_data['url1']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($rebate_data['url1'])?>" target="_blank">Link 1</a>
					<?php
				}

				if(isset($rebate_data['url2']) && $rebate_data['url2']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($rebate_data['url2'])?>" target="_blank">Link 2</a>
					<?php
				}

				if(isset($rebate_data['url3']) && $rebate_data['url3']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($rebate_data['url3'])?>" target="_blank">Link 3</a>
					<?php
				}

				if(isset($rebate_data['url4']) && $rebate_data['url4']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($rebate_data['url4'])?>" target="_blank">Link 4</a>
					<?php
				}

				if(isset($rebate_data['url5']) && $rebate_data['url5']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($rebate_data['url5'])?>" target="_blank">Link 5</a>
					<?php
				}

				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_rebate() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(current_user_can('edit_rebates') && check_ajax_referer( 'edit-rebate', 'nonce', false )) {
			$product = isset($_POST['product'])?absint($_POST['product']):0;
			$rebate_id = isset($_POST['rebate'])?absint($_POST['rebate']):0;
			$rebate_content = isset($_POST['rebate_content'])?wp_kses_post($_POST['rebate_content']):'';
			$rebate_note = isset($_POST['rebate_note'])?sanitize_text_field($_POST['rebate_note']):'';
			$rebate_zalo = isset($_POST['rebate_zalo'])?sanitize_text_field($_POST['rebate_zalo']):'';
			$rebate_url1 = isset($_POST['rebate_url1'])?sanitize_url($_POST['rebate_url1']):'';
			$rebate_url2 = isset($_POST['rebate_url2'])?sanitize_url($_POST['rebate_url2']):'';
			$rebate_url3 = isset($_POST['rebate_url3'])?sanitize_url($_POST['rebate_url3']):'';
			$rebate_url4 = isset($_POST['rebate_url4'])?sanitize_url($_POST['rebate_url4']):'';
			$rebate_url5 = isset($_POST['rebate_url5'])?sanitize_url($_POST['rebate_url5']):'';
			
			$rebate_file_id = isset($_POST['rebate_file_id'])?absint($_POST['rebate_file_id']):0;
			
			$rebate_proc1_label = isset($_POST['rebate_proc1_label']) ? $_POST['rebate_proc1_label'] : '';
			$rebate_proc1 = isset($_POST['rebate_proc1']) ? $_POST['rebate_proc1'] : '';
			$rebate_proc2_label = isset($_POST['rebate_proc2_label']) ? $_POST['rebate_proc2_label'] : '';
			$rebate_proc2 = isset($_POST['rebate_proc2']) ? $_POST['rebate_proc2'] : '';
			$rebate_proc3_label = isset($_POST['rebate_proc3_label']) ? $_POST['rebate_proc3_label'] : '';
			$rebate_proc3 = isset($_POST['rebate_proc3']) ? $_POST['rebate_proc3'] : '';
			$rebate_proc4_label = isset($_POST['rebate_proc4_label']) ? $_POST['rebate_proc4_label'] : '';
			$rebate_proc4 = isset($_POST['rebate_proc4']) ? $_POST['rebate_proc4'] : '';
			
			if($product && $rebate_id) {
				$data = get_term_meta($product, 'rebate', true);
				if(empty($data)) $data = [];
				$rebate_data = isset($data[$rebate_id])?$data[$rebate_id]:$this->default;
				$rebate_data += $this->default;

				$new_rebate_data = [
					'content' => $rebate_content,
					'proc1_label' => $rebate_proc1_label,
					'proc1' => $rebate_proc1,
					'proc2_label' => $rebate_proc2_label,
					'proc2' => $rebate_proc2,
					'proc3_label' => $rebate_proc3_label,
					'proc3' => $rebate_proc3,
					'proc4_label' => $rebate_proc4_label,
					'proc4' => $rebate_proc4,
					'note' => $rebate_note,
					'zalo' => $rebate_zalo,
					'url1' => $rebate_url1,
					'url2' => $rebate_url2,
					'url3' => $rebate_url3,
					'url4' => $rebate_url4,
					'url5' => $rebate_url5,
					'file_id' => ($rebate_file_id!=0)?$rebate_file_id:'',
				];

				if($new_rebate_data['file_id']=='' || $new_rebate_data['file_id']==0) {
					if(isset($rebate_data['file_id']) && $rebate_data['file_id']) wp_delete_attachment($rebate_data['file_id'], true);
				}

				$data[$rebate_id] = $new_rebate_data;

				update_term_meta($product, 'rebate', $data);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_rebate_file_upload() {
		$response = [
			'status' => 500,
			'message' => '',
			'data' => []
		];

		// debug_log($_POST);
		// debug_log($_FILES);
		// wp_send_json( $response );

		if(!current_user_can('edit_rebates') || !check_ajax_referer( 'edit-rebate', 'nonce', false )) {
			$response['status'] = 403;
			$response['message'] = "Forbiden.";
			wp_send_json( $response, 403 );
		}

		$product = isset($_POST['product'])?absint($_POST['product']):0;
		$rebate_id = isset($_POST['rebate'])?absint($_POST['rebate']):0;

		wp_send_json( $response, $response['status'] );
	}

	public function handle_rebate_chunk_upload() {
		$response = ['success' => false, 'attachment_id' => 0, 'url' => '', 'filename' => '', 'msg' => ''];

		if(current_user_can('edit_rebates') && check_ajax_referer( 'edit-rebate', 'nonce', false )) {
			$product = isset($_POST['product'])?absint($_POST['product']):0;
			$rebate_id = isset($_POST['rebate'])?absint($_POST['rebate']):0;

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
					$data = get_term_meta($product, 'rebate', true);
					if(empty($data)) $data = [];
					$rebate_data = isset($data[$rebate_id])?$data[$rebate_id]:$this->default;
					$rebate_data += $this->default;

					if($rebate_data['file_id']) wp_delete_attachment($rebate_data['file_id'], true);

					$rebate_data['file_id'] = $attach_id;

					$data[$rebate_id] = $rebate_data;

					update_term_meta($product, 'rebate', $data);

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

	public function ajax_get_edit_rebate_form() {
		$product = isset($_GET['product'])?absint($_GET['product']):0;
		$rebate = isset($_GET['rebate'])?absint($_GET['rebate']):0;

		if($product && $rebate) {
			$data = get_term_meta($product, 'rebate', true);
			if(empty($data)) $data = [];
			$rebate_data = isset($data[$rebate])?$data[$rebate]:$this->default;
			$rebate_data += $this->default;

			$file_url = (isset($rebate_data['file_id']) && $rebate_data['file_id']!='')?wp_get_attachment_url($rebate_data['file_id']):'';
			?>
			<form id="frm-edit-rebate" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="product" name="product" value="<?=$product?>">
				<input type="hidden" id="rebate" name="rebate" value="<?=$rebate?>">
				<?php wp_nonce_field( 'edit-rebate', 'nonce' ); ?>
				<div id="edit-rebate-response"></div>
				<div class="row">
					<div class="col-lg-7<?php echo (!current_user_can('edit_rebates'))?' hidden':''; ?>">
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
							wp_editor( $rebate_data['content'], 'rebate_content', $settings);
							?>
							<input type="hidden" id="rebate_content_settings" value="<?=esc_attr(json_encode($settings))?>">
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
					<div class="<?php echo (!current_user_can('edit_rebates'))?' col-lg-12':'col-lg-5'; ?>">
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($rebate_data['proc1_label']!='')?esc_html($rebate_data['proc1_label']):''; ?>" name="rebate_proc1_label" id="rebate_proc1_label" placeholder="Ghi chú ngày 1">
							<input class="form-control" type="date" value="<?php echo ($rebate_data['proc1']!='')?esc_html(date('Y-m-d', strtotime($rebate_data['proc1']))):''; ?>" name="rebate_proc1" id="rebate_proc1">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($rebate_data['proc2_label']!='')?esc_html($rebate_data['proc2_label']):''; ?>" name="rebate_proc2_label" id="rebate_proc2_label" placeholder="Ghi chú ngày 2">
							<input class="form-control" type="date" value="<?php echo ($rebate_data['proc2']!='')?esc_html(date('Y-m-d', strtotime($rebate_data['proc2']))):''; ?>" name="rebate_proc2" id="rebate_proc2">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($rebate_data['proc3_label']!='')?esc_html($rebate_data['proc3_label']):''; ?>" name="rebate_proc3_label" id="rebate_proc3_label" placeholder="Ghi chú ngày 3">
							<input class="form-control" type="date" value="<?php echo ($rebate_data['proc3']!='')?esc_html(date('Y-m-d', strtotime($rebate_data['proc3']))):''; ?>" name="rebate_proc3" id="rebate_proc3">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($rebate_data['proc4_label']!='')?esc_html($rebate_data['proc4_label']):''; ?>" name="rebate_proc4_label" id="rebate_proc4_label" placeholder="Ghi chú ngày 4">
							<input class="form-control" type="date" value="<?php echo ($rebate_data['proc4']!='')?esc_html(date('Y-m-d', strtotime($rebate_data['proc4']))):''; ?>" name="rebate_proc4" id="rebate_proc4">
						</div>
						<div class="mb-3">
							Ghi chú
							<input type="text" id="rebate_note" name="rebate_note" class="form-control" value="<?php echo esc_attr($rebate_data['note']); ?>">
						</div>
						<div class="mb-3">
							Link nhóm zalo
							<input type="text" id="rebate_zalo" name="rebate_zalo" class="form-control" value="<?php echo esc_attr($rebate_data['zalo']); ?>">
						</div>
						<div class="mb-3">
							Link công việc 1
							<input type="text" id="rebate_url1" name="rebate_url1" class="form-control" value="<?php echo ($rebate_data['url1'])?esc_url($rebate_data['url1']):''; ?>">
						</div>
						<div class="mb-3">
							Link công việc 2
							<input type="text" id="rebate_url2" name="rebate_url2" class="form-control" value="<?php echo ($rebate_data['url2'])?esc_url($rebate_data['url2']):''; ?>">
						</div>
						<div class="mb-3">
							Link công việc 3
							<input type="text" id="rebate_url3" name="rebate_url3" class="form-control" value="<?php echo ($rebate_data['url3'])?esc_url($rebate_data['url3']):''; ?>">
						</div>
						<div class="mb-3">
							Link công việc 4
							<input type="text" id="rebate_url4" name="rebate_url4" class="form-control" value="<?php echo ($rebate_data['url4'])?esc_url($rebate_data['url4']):''; ?>">
						</div>
						<div class="mb-3">
							Link công việc 5
							<input type="text" id="rebate_url5" name="rebate_url5" class="form-control" value="<?php echo ($rebate_data['url5'])?esc_url($rebate_data['url5']):''; ?>">
						</div>
					</div>
					<div class="col-lg-12">
						<div class="mb-3">
							<div class="form-label mb-1">File công việc</div>
							<div class="row row-cols-2 g-0 p-2 border rounded-2">
								<div class="col">
									<div id="attachment-uploaded">
										<input type="hidden" id="rebate_file_id" name="rebate_file_id" value="<?=esc_attr($rebate_data['file_id'])?>">
										<div class="input-group input-group-sm">
											<div class="form-control text-truncate">
												<?php
												if($file_url) {
													echo esc_html(basename($file_url));
												}
												?>	
											</div>
											<button class="btn btn-sm btn-warning" id="rebate_remove_file" type="button" <?php disabled( '', $file_url, true ); ?>>Xóa file</button>
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
								<label class="col d-block ps-5" for="rebate_file">
									<div class="input-group input-group-sm">
										<div class="form-control text-nowrap text-truncate">Chọn file cần tải lên</div>
										<span class="btn btn-primary">Bấm tải lên</span>
									</div>
									<div style="width: 0;height: 0;overflow: hidden;">
										<input type="file" id="rebate_file" name="rebate_file" class="form-control">
									</div>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-rebate-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-rebate" tabindex="-1" role="dialog" aria-labelledby="edit-rebate-label">
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-rebate-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
