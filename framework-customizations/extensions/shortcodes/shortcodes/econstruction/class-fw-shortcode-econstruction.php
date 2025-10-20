<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Econstruction extends FW_Shortcode
{
	public $default = [
		'required_content' => '',
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
      add_action( 'wp_ajax_get_edit_econstruction_form', [$this, 'ajax_get_edit_econstruction_form']);
      add_action( 'wp_ajax_update_econstruction', [$this, 'ajax_update_econstruction']);
      add_action( 'wp_ajax_get_econstruction_info', [$this, 'ajax_get_econstruction_info']);
      add_action( 'wp_ajax_econstruction_hide', [$this, 'ajax_econstruction_hide']);
      add_action( 'wp_ajax_econstruction_toggle', [$this, 'ajax_econstruction_toggle']);
	}

	public function ajax_econstruction_toggle() {
		global $current_client;
		$econstruction_id = isset($_POST['econstruction']) ? absint($_POST['econstruction']) : 0;
		$response = 0;
		if(current_user_can('edit_estimate_constructions') && $current_client && $econstruction_id && check_ajax_referer( 'global', 'nonce', false )) {

			$econstruction_removed = get_term_meta($current_client->term_id, 'econstruction_removed', true);
			if(empty($econstruction_removed)) $econstruction_removed = [];

			if(in_array($econstruction_id, $econstruction_removed)) {
				unset($econstruction_removed[array_search($econstruction_id, $econstruction_removed)]);
				$response = -1;
			} else {
				$econstruction_removed[] = $econstruction_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'econstruction_removed', $econstruction_removed);
		}
		wp_send_json($response);
	}

	public function ajax_econstruction_hide() {
		global $current_client;
		$econstruction_id = isset($_POST['econstruction']) ? absint($_POST['econstruction']) : 0;
		$response = 0;
		if(current_user_can('edit_estimate_constructions') && $current_client && $econstruction_id && check_ajax_referer( 'global', 'nonce', false )) {

			$econstruction_hide = get_term_meta($current_client->term_id, 'econstruction_hide', true);
			if(empty($econstruction_hide)) $econstruction_hide = [];

			if(in_array($econstruction_id, $econstruction_hide)) {
				unset($econstruction_hide[array_search($econstruction_id, $econstruction_hide)]);
				$response = -1;
			} else {
				$econstruction_hide[] = $econstruction_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'econstruction_hide', $econstruction_hide);
		}
		wp_send_json($response);
	}

	public function ajax_get_econstruction_info() {

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$econstruction = isset($_GET['econstruction'])?absint($_GET['econstruction']):0;

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
		
		if($client && $econstruction) {
			$default_econstruction_file = fw_get_db_post_option($econstruction,'econstruction_file');
			$default_zalo = fw_get_db_post_option($econstruction,'econstruction_zalo');
			$default_data = [
				'value' => fw_get_db_post_option($econstruction,'econstruction_value'),
				'unit' => fw_get_db_post_option($econstruction,'econstruction_unit'),
				'file_id' => (!empty($default_econstruction_file))?$default_econstruction_file['attachment_id']:'',
			];

			$data = get_term_meta($client, 'econstruction', true);
			if(empty($data)) $data = [];
			
			$econstruction_data = isset($data[$econstruction])?$data[$econstruction]:$this->default;
			$econstruction_data += $this->default;

			if(empty($econstruction_data['value'])) $econstruction_data['value'] = $default_data['value'];
			if(empty($econstruction_data['unit'])) $econstruction_data['unit'] = $default_data['unit'];
			if(empty($econstruction_data['file_id'])) $econstruction_data['file_id'] = $default_data['file_id'];

			$response['zalo'] = ($econstruction_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($econstruction_data['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['zalo'] .= ($default_zalo)?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($default_zalo).'" target="_blank">Zalo</a>':'';

			$response['file'] = ($econstruction_data['file_id'])?'<a class="btn-shadow btn btn-sm btn-primary fw-bold me-1" href="'.esc_url(wp_get_attachment_url($econstruction_data['file_id'])).'" target="_blank">Tải</a>':'';
			
			$response['required'] = (isset($econstruction_data['required']) && $econstruction_data['required']!='')?'<div class="bg-danger" title="'.esc_attr($econstruction_data['required_label']).'">'.esc_html(date('d/m', strtotime($econstruction_data['required']))).'</div>':'';
			$response['received'] = (isset($econstruction_data['received']) && $econstruction_data['received']!='')?'<div class="bg-danger" title="'.esc_attr($econstruction_data['received_label']).'">'.esc_html(date('d/m', strtotime($econstruction_data['received']))).'</div>':'';
			$response['completed'] = (isset($econstruction_data['completed']) && $econstruction_data['completed']!='')?'<div class="bg-danger" title="'.esc_attr($econstruction_data['completed_label']).'">'.esc_html(date('d/m', strtotime($econstruction_data['completed']))).'</div>':'';
			$response['sent'] = (isset($econstruction_data['sent']) && $econstruction_data['sent']!='')?'<div class="bg-danger" title="'.esc_attr($econstruction_data['sent_label']).'">'.esc_html(date('d/m', strtotime($econstruction_data['sent']))).'</div>':'';

			ob_start();
			
			if($econstruction_data['required_content']!='') {
				$required_content = '<div class="copy-text">'.wp_get_the_content($econstruction_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
				?>
				<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
				<?php
			}

			$response['required_content'] = ob_get_clean();

			ob_start();
		?>
			<div class="econstruction-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $econstruction )); ?>
			</div>
			<?php if($econstruction_data['value']) { ?>
			<div class="econstruction-value mb-1">
				<span class="text-red fw-bold"><?php echo esc_html($econstruction_data['value']); ?></span>
			</div>
			<?php } ?>
			<?php if($econstruction_data['unit']) { ?>
			<div class="econstruction-unit mb-1">
				<div class="text-red fw-bold"><?php echo esc_html($econstruction_data['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center econstruction-url mb-3">
				<?php
				if($econstruction_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($econstruction_data['url'])?>" target="_blank">Dự toán 1</a>
					<?php
				}
				if($econstruction_data['url2']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($econstruction_data['url2'])?>" target="_blank">Dự toán 2</a>
					<?php
				}
				if($econstruction_data['url3']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($econstruction_data['url3'])?>" target="_blank">Dự toán 3</a>
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

		if(current_user_can('edit_estimate_constructions') && check_ajax_referer( 'edit-econstruction', 'nonce', false )) {
			$client = isset($_POST['client'])?absint($_POST['client']):0;
			$econstruction_id = isset($_POST['econstruction'])?absint($_POST['econstruction']):0;

			$required_content = isset($_POST['required_content'])?wp_kses_post($_POST['required_content']):'';
			$econstruction_value = isset($_POST['econstruction_value'])?sanitize_text_field($_POST['econstruction_value']):'';
			$econstruction_unit = isset($_POST['econstruction_unit'])?sanitize_text_field($_POST['econstruction_unit']):'';
			$econstruction_zalo = isset($_POST['econstruction_zalo'])?sanitize_text_field($_POST['econstruction_zalo']):'';
			$econstruction_url = isset($_POST['econstruction_url'])?sanitize_url($_POST['econstruction_url']):'';
			$econstruction_url2 = isset($_POST['econstruction_url2'])?sanitize_url($_POST['econstruction_url2']):'';
			$econstruction_url3 = isset($_POST['econstruction_url3'])?sanitize_url($_POST['econstruction_url3']):'';
			$econstruction_file_id = isset($_POST['econstruction_file_id'])?absint($_POST['econstruction_file_id']):0;
			$econstruction_file = isset($_FILES['econstruction_file']) ? $_FILES['econstruction_file'] : null;

			$econstruction_required_label = isset($_POST['econstruction_required_label']) ? $_POST['econstruction_required_label'] : '';
			$econstruction_required = isset($_POST['econstruction_required']) ? $_POST['econstruction_required'] : '';
			$econstruction_received_label = isset($_POST['econstruction_received_label']) ? $_POST['econstruction_received_label'] : '';
			$econstruction_received = isset($_POST['econstruction_received']) ? $_POST['econstruction_received'] : '';
			$econstruction_completed_label = isset($_POST['econstruction_completed_label']) ? $_POST['econstruction_completed_label'] : '';
			$econstruction_completed = isset($_POST['econstruction_completed']) ? $_POST['econstruction_completed'] : '';
			$econstruction_sent_label = isset($_POST['econstruction_sent_label']) ? $_POST['econstruction_sent_label'] : '';
			$econstruction_sent = isset($_POST['econstruction_sent']) ? $_POST['econstruction_sent'] : '';
			
			if($client && $econstruction_id) {
	
				$data = get_term_meta($client, 'econstruction', true);
				if(empty($data)) $data = [];
				
				$econstruction_data = isset($data[$econstruction_id])?$data[$econstruction_id]:$this->default;
				$econstruction_data += $this->default;

				$new_econstruction_data = [
					'required_content' => $required_content,
					'required_label' => $econstruction_required_label,
					'required' => $econstruction_required,
					'received_label' => $econstruction_received_label,
					'received' => $econstruction_received,
					'completed_label' => $econstruction_completed_label,
					'completed' => $econstruction_completed,
					'sent_label' => $econstruction_sent_label,
					'sent' => $econstruction_sent,
					'value' => $econstruction_value,
					'unit' => $econstruction_unit,
					'zalo' => $econstruction_zalo,
					'url' => $econstruction_url,
					'url2' => $econstruction_url2,
					'url3' => $econstruction_url3,
					'file_id' => ($econstruction_file_id!=0)?$econstruction_file_id:'',
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

				$data[$econstruction_id] = $new_econstruction_data;

				//fw_set_db_term_option($client, 'passwords', 'econstruction', $data);
				update_term_meta($client, 'econstruction', $data);

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
			
			$data = get_term_meta($client, 'econstruction', true);
			if(empty($data)) $data = [];
			
			$econstruction_data = isset($data[$econstruction])?$data[$econstruction]:$this->default;
			$econstruction_data += $this->default;

			$file_url = (isset($econstruction_data['file_id']) && $econstruction_data['file_id']!='')?wp_get_attachment_url($econstruction_data['file_id']):'';
			?>
			<form id="frm-edit-econstruction" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="client" name="client" value="<?=$client?>">
				<input type="hidden" id="econstruction" name="econstruction" value="<?=$econstruction?>">
				<?php wp_nonce_field( 'edit-econstruction', 'nonce' ); ?>
				<div id="edit-econstruction-response"></div>
				<div class="row">
					<div class="col-lg-7<?php echo (!current_user_can('edit_estimate_constructions'))?' hidden':''; ?>">
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
							wp_editor( $econstruction_data['required_content'], 'required_content', $settings);
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
					<div class="<?php echo (!current_user_can('edit_estimate_constructions'))?' col-lg-12':'col-lg-5'; ?>">
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo (isset($econstruction_data['required_label'])&&$econstruction_data['required_label']!='')?esc_html($econstruction_data['required_label']):''; ?>" name="econstruction_required_label" id="econstruction_required_label" placeholder="Ghi chú ngày 1">
							<input class="form-control" type="date" value="<?php echo (isset($econstruction_data['required'])&&$econstruction_data['required']!='')?esc_html(date('Y-m-d', strtotime($econstruction_data['required']))):''; ?>" name="econstruction_required" id="econstruction_required">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo (isset($econstruction_data['received_label'])&&$econstruction_data['received_label']!='')?esc_html($econstruction_data['received_label']):''; ?>" name="econstruction_received_label" id="econstruction_received_label" placeholder="Ghi chú ngày 2">
							<input class="form-control" type="date" value="<?php echo (isset($econstruction_data['received'])&&$econstruction_data['received']!='')?esc_html(date('Y-m-d', strtotime($econstruction_data['received']))):''; ?>" name="econstruction_received" id="econstruction_received">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo (isset($econstruction_data['completed_label'])&&$econstruction_data['completed_label']!='')?esc_html($econstruction_data['completed_label']):''; ?>" name="econstruction_completed_label" id="econstruction_completed_label" placeholder="Ghi chú ngày 3">
							<input class="form-control" type="date" value="<?php echo (isset($econstruction_data['completed'])&&$econstruction_data['completed']!='')?esc_html(date('Y-m-d', strtotime($econstruction_data['completed']))):''; ?>" name="econstruction_completed" id="econstruction_completed">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo (isset($econstruction_data['sent_label'])&&$econstruction_data['sent_label']!='')?esc_html($econstruction_data['sent_label']):''; ?>" name="econstruction_sent_label" id="econstruction_sent_label" placeholder="Ghi chú ngày 4">
							<input class="form-control" type="date" value="<?php echo (isset($econstruction_data['sent'])&&$econstruction_data['sent']!='')?esc_html(date('Y-m-d', strtotime($econstruction_data['sent']))):''; ?>" name="econstruction_sent" id="econstruction_sent">
						</div>
						<div class="mb-3">
							Tên - Số điện thoại
							<input type="text" id="econstruction_value" name="econstruction_value" class="form-control" value="<?php echo esc_attr($econstruction_data['value']); ?>">
						</div>
						<div class="mb-3">
							Ghi chú
							<input type="text" id="econstruction_unit" name="econstruction_unit" class="form-control" value="<?php echo esc_attr($econstruction_data['unit']); ?>">
						</div>

						<div class="mb-3">
							Link nhóm zalo
							<input type="text" id="econstruction_zalo" name="econstruction_zalo" class="form-control" value="<?php echo esc_attr($econstruction_data['zalo']); ?>">
						</div>
						<div class="mb-3">
							Dự toán phiên bản 1
							<input type="text" id="econstruction_url" name="econstruction_url" placeholder="" class="form-control" value="<?php echo ($econstruction_data['url'])?esc_url($econstruction_data['url']):''; ?>">
						</div>
						<div class="mb-3">
							Dự toán phiên bản 2
							<input type="text" id="econstruction_url" name="econstruction_url2" placeholder="" class="form-control" value="<?php echo ($econstruction_data['url'])?esc_url($econstruction_data['url2']):''; ?>">
						</div>
						<div class="mb-3">
							Dự toán phiên bản 3
							<input type="text" id="econstruction_url" name="econstruction_url3" placeholder="" class="form-control" value="<?php echo ($econstruction_data['url'])?esc_url($econstruction_data['url3']):''; ?>">
						</div>
					</div>
					<div class="col-lg-12">
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
									<input type="file" id="econstruction_file" name="econstruction_file" class="form-control">
								</div>
							</label>
						</div>
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
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
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
