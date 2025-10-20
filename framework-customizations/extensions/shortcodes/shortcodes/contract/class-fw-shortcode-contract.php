<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Contract extends FW_Shortcode
{
	public $default = [
		'required_content'=>'',
		'required_label'=>'',
		'required'=>'',
		'created_label'=>'',
		'created'=>'',
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
	];

	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_contract_form', [$this, 'ajax_get_edit_contract_form']);
      add_action( 'wp_ajax_update_contract', [$this, 'ajax_update_contract']);
      add_action( 'wp_ajax_get_contract_info', [$this, 'ajax_get_contract_info']);
      add_action( 'wp_ajax_contract_hide', [$this, 'ajax_contract_hide']);
      add_action( 'wp_ajax_contract_toggle', [$this, 'ajax_contract_toggle']);
	}

	public function ajax_contract_toggle() {
		global $current_client;
		$contract_id = isset($_POST['contract']) ? absint($_POST['contract']) : 0;
		$response = 0;
		if(current_user_can('contract_edit') && $current_client && $contract_id && check_ajax_referer( 'global', 'nonce', false )) {

			$contract_removed = get_term_meta($current_client->term_id, 'contract_removed', true);
			if(empty($contract_removed)) $contract_removed = [];

			if(in_array($contract_id, $contract_removed)) {
				unset($contract_removed[array_search($contract_id, $contract_removed)]);
				$response = -1;
			} else {
				$contract_removed[] = $contract_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'contract_removed', $contract_removed);
		}
		wp_send_json($response);
	}

	public function ajax_contract_hide() {
		global $current_client;
		$contract_id = isset($_POST['contract']) ? absint($_POST['contract']) : 0;
		$response = 0;
		if(current_user_can('contract_edit') && $current_client && $contract_id && check_ajax_referer( 'global', 'nonce', false )) {

			$contract_hide = get_term_meta($current_client->term_id, 'contract_hide', true);
			if(empty($contract_hide)) $contract_hide = [];

			if(in_array($contract_id, $contract_hide)) {
				unset($contract_hide[array_search($contract_id, $contract_hide)]);
				$response = -1;
			} else {
				$contract_hide[] = $contract_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'contract_hide', $contract_hide);
		}
		wp_send_json($response);
	}

	public function ajax_get_contract_info() {

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$contract_id = isset($_GET['contract'])?absint($_GET['contract']):0;

		$response = [
			'required_content' => '',
			'info' => '',
			'zalo' => ''
		];
		
		if($client && $contract_id) {
			$default_zalo = fw_get_db_post_option($contract_id,'contract_zalo');
			$default_data = [
				'value' => fw_get_db_post_option($contract_id,'contract_value'),
				'unit' => fw_get_db_post_option($contract_id,'contract_unit'),
			];

			$data = get_post_meta($contract_id, '_data', true);
			$contract_data = isset($data[$client])?$data[$client]:$this->default;
			$contract_data += $this->default;

			if(empty($contract_data['value'])) $contract_data['value'] = $default_data['value'];
			if(empty($contract_data['unit'])) $contract_data['unit'] = $default_data['unit'];

			$response['zalo'] = ($contract_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($contract_data['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['zalo'] .= ($default_zalo)?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($default_zalo).'" target="_blank">Zalo</a>':'';

			$response['required'] = ($contract_data['required']!='')?'<div class="bg-danger" title="'.esc_attr($contract_data['required_label']).'">'.esc_html(date('d/m', strtotime($contract_data['required']))).'</div>':'';
			$response['created'] = ($contract_data['created']!='')?'<div class="bg-danger" title="'.esc_attr($contract_data['created_label']).'">'.esc_html(date('d/m', strtotime($contract_data['created']))).'</div>':'';
			$response['completed'] = ($contract_data['completed']!='')?'<div class="bg-danger" title="'.esc_attr($contract_data['completed_label']).'">'.esc_html(date('d/m', strtotime($contract_data['completed']))).'</div>':'';
			$response['sent'] = ($contract_data['sent']!='')?'<div class="bg-danger" title="'.esc_attr($contract_data['sent_label']).'">'.esc_html(date('d/m', strtotime($contract_data['sent']))).'</div>':'';

			ob_start();
			
			if($contract_data['required_content']!='') {
				$required_content = '<div class="copy-text">'.wp_get_the_content($contract_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
				?>
				<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
				<?php
			}

			$response['required_content'] = ob_get_clean();

			ob_start();
		?>
			<div class="contract-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $contract_id )); ?>
			</div>
			<?php if($contract_data['value']!='' || $contract_data['unit']!='') { ?>
			<div class="contract-value mb-1">
				<?php if($contract_data['value']!='') { ?>
				<div>
					<span class="text-red fw-bold"><?php echo esc_html($contract_data['value']); ?></span>
				</div>
				<?php } ?>
				<?php if($contract_data['unit']!='') { ?>
				<div class="text-red fw-bold"><?php echo esc_html($contract_data['unit']); ?></div>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center contract-links mb-3">
				<?php
				if($contract_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($contract_data['url'])?>" target="_blank">Hợp đồng bản 1</a>
					<?php
				}
				if($contract_data['url2']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($contract_data['url2'])?>" target="_blank">Hợp đồng bản 2</a>
					<?php
				}
				if($contract_data['url3']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($contract_data['url3'])?>" target="_blank">Hợp đồng bản 3</a>
					<?php
				}
				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_contract() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('contract_edit') && check_ajax_referer( 'edit-contract', 'nonce', false )) {
			$contract_client = isset($_POST['contract_client'])?absint($_POST['contract_client']):0;
			$contract_id = isset($_POST['contract_id'])?absint($_POST['contract_id']):0;
			$required_content = isset($_POST['required_content'])?wp_kses_post($_POST['required_content']):'';
			$contract_value = isset($_POST['contract_value'])?sanitize_text_field($_POST['contract_value']):'';
			$contract_unit = isset($_POST['contract_unit'])?sanitize_text_field($_POST['contract_unit']):'';
			$contract_zalo = isset($_POST['contract_zalo'])?sanitize_text_field($_POST['contract_zalo']):'';
			$contract_url = isset($_POST['contract_url'])?sanitize_url($_POST['contract_url']):'';
			$contract_url2 = isset($_POST['contract_url2'])?sanitize_url($_POST['contract_url2']):'';
			$contract_url3 = isset($_POST['contract_url3'])?sanitize_url($_POST['contract_url3']):'';

			$contract_required_label = isset($_POST['contract_required_label']) ? $_POST['contract_required_label'] : '';
			$contract_required = isset($_POST['contract_required']) ? $_POST['contract_required'] : '';
			$contract_created_label = isset($_POST['contract_created_label']) ? $_POST['contract_created_label'] : '';
			$contract_created = isset($_POST['contract_created']) ? $_POST['contract_created'] : '';
			$contract_completed_label = isset($_POST['contract_completed_label']) ? $_POST['contract_completed_label'] : '';
			$contract_completed = isset($_POST['contract_completed']) ? $_POST['contract_completed'] : '';
			$contract_sent_label = isset($_POST['contract_sent_label']) ? $_POST['contract_sent_label'] : '';
			$contract_sent = isset($_POST['contract_sent']) ? $_POST['contract_sent'] : '';

			if($contract_client && $contract_id) {
				$data = get_post_meta($contract_id, '_data', true);
				if(empty($data)) $data = [];
				$contract_data = isset($data[$contract_client])?$data[$contract_client]:$this->default;
				$contract_data += $this->default;

				$new_contract_data = [
					'required_content' => $required_content,
					'required_label' => $contract_required_label,
					'required' => $contract_required,
					'created_label' => $contract_created_label,
					'created' => $contract_created,
					'completed_label' => $contract_completed_label,
					'completed' => $contract_completed,
					'sent_label' => $contract_sent_label,
					'sent' => $contract_sent,
					'value' => $contract_value,
					'unit' => $contract_unit,
					'zalo' => $contract_zalo,
					'url' => $contract_url,
					'url2' => $contract_url2,
					'url3' => $contract_url3,
				];

				$data[$contract_client] = $new_contract_data;

				update_post_meta( $contract_id, '_data', $data );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_contract_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$contract = isset($_GET['contract'])?absint($_GET['contract']):0;

		if($client && $contract) {
			$data = get_post_meta($contract, '_data', true);
			$contract_data = isset($data[$client])?$data[$client]:$this->default;
			$contract_data += $this->default;
			?>
			<form id="frm-edit-contract" method="POST" action="">
				<input type="hidden" id="contract_client" name="contract_client" value="<?=$client?>">
				<input type="hidden" id="contract_id" name="contract_id" value="<?=$contract?>">
				<?php wp_nonce_field( 'edit-contract', 'nonce' ); ?>
				<div id="edit-contract-response"></div>
				<div class="row">
					<div class="col-lg-7<?php echo (!current_user_can('edit_contracts'))?' hidden':''; ?>">
						<div class="mb-3">
							Nội dung áp dụng
							<?php
							$settings = [
								'media_buttons' => false,
								'teeny'         => false,
								'quicktags'     => false,
								'editor_height' => '765',
								'tinymce'       => [
									'toolbar1' => 'bold,italic,underline,alignleft,aligncenter,alignright,alignjustify,link,unlink,bullist,numlist,forecolor,pastetext,removeformat,charmap,fullscreen',
									'toolbar2' => '',
									'content_style' => 'body { font-family: Arial, Helvetica, sans-serif; font-size: 16px; }'
								]
							];
							wp_editor( $contract_data['required_content'], 'required_content', $settings);
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
					<div class="<?php echo (!current_user_can('edit_contracts'))?' col-lg-12':'col-lg-5'; ?>">
						<div class="mb-3<?php echo (!current_user_can('edit_contracts'))?' hidden':''; ?>">
							<input class="form-control mb-2" type="text" value="<?php echo ($contract_data['required_label']!='')?esc_html($contract_data['required_label']):''; ?>" name="contract_required_label" id="contract_required_label" placeholder="Ghi chú ngày 1">
							<input class="form-control" type="date" value="<?php echo ($contract_data['required']!='')?esc_html(date('Y-m-d', strtotime($contract_data['required']))):''; ?>" name="contract_required" id="contract_required">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($contract_data['created_label']!='')?esc_html($contract_data['created_label']):''; ?>" name="contract_created_label" id="contract_created_label" placeholder="Ghi chú ngày 2">
							<input class="form-control" type="date" value="<?php echo ($contract_data['created']!='')?esc_html(date('Y-m-d', strtotime($contract_data['created']))):''; ?>" name="contract_created" id="contract_created">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($contract_data['completed_label']!='')?esc_html($contract_data['completed_label']):''; ?>" name="contract_completed_label" id="contract_completed_label" placeholder="Ghi chú ngày 3">
							<input class="form-control" type="date" value="<?php echo ($contract_data['completed']!='')?esc_html(date('Y-m-d', strtotime($contract_data['completed']))):''; ?>" name="contract_completed" id="contract_completed">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($contract_data['sent_label']!='')?esc_html($contract_data['sent_label']):''; ?>" name="contract_sent_label" id="contract_sent_label" placeholder="Ghi chú ngày 4">
							<input class="form-control" type="date" value="<?php echo ($contract_data['sent']!='')?esc_html(date('Y-m-d', strtotime($contract_data['sent']))):''; ?>" name="contract_sent" id="contract_sent">
						</div>
						<div class="mb-3">
							Tên - Số điện thoại
							<input type="text" id="contract_value" name="contract_value" class="form-control" value="<?php echo esc_attr($contract_data['value']); ?>">
						</div>
						<div class="mb-3">
							Đơn vị
							<input type="text" id="contract_unit" name="contract_unit" class="form-control" value="<?php echo esc_attr($contract_data['unit']); ?>">
						</div>
						<div class="mb-3">
							URL nhóm zalo
							<input type="text" id="contract_zalo" name="contract_zalo" class="form-control" value="<?php echo esc_attr($contract_data['zalo']); ?>">
						</div>
						<div class="mb-3">
							Hợp đồng bản 1
							<input type="text" id="contract_url" name="contract_url" class="form-control" value="<?php echo ($contract_data['url'])?esc_url($contract_data['url']):''; ?>">
						</div>
						<div class="mb-3">
							Hợp đồng bản 2
							<input type="text" id="contract_url2" name="contract_url2" class="form-control" value="<?php echo ($contract_data['url2'])?esc_url($contract_data['url2']):''; ?>">
						</div>
						<div class="mb-3">
							Hợp đồng bản 3
							<input type="text" id="contract_url3" name="contract_url3" class="form-control" value="<?php echo ($contract_data['url3'])?esc_url($contract_data['url3']):''; ?>">
						</div>
						
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-contract-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-contract" tabindex="-1" role="dialog" aria-labelledby="edit-contract-label">
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-contract-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
