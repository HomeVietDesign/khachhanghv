<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Contract extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_contract_form', [$this, 'ajax_get_edit_contract_form']);
      add_action( 'wp_ajax_update_contract', [$this, 'ajax_update_contract']);
      add_action( 'wp_ajax_get_contract_info', [$this, 'ajax_get_contract_info']);
      add_action( 'wp_ajax_contract_hide', [$this, 'ajax_contract_hide']);
	}

	public function ajax_contract_hide() {
		global $current_client;
		$contract_id = isset($_POST['contract']) ? absint($_POST['contract']) : 0;
		$response = false;
		if(current_user_can('contract_edit') && $current_client && $contract_id && check_ajax_referer( 'global', 'nonce', false )) {
			$contract_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'contract_hide', []);
			$contract_hide[] = $contract_id;
			fw_set_db_term_option($current_client->term_id, 'passwords', 'contract_hide', $contract_hide);
			$response = true;
		}
		wp_send_json($response);
	}

	public function ajax_get_contract_info() {

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$contract_id = isset($_GET['contract'])?absint($_GET['contract']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];
		
		if($client && $contract_id) {
			//$default_url = fw_get_db_post_option($contract_id,'contract_url');
			$default_data = [
				'value' => fw_get_db_post_option($contract_id,'contract_value'),
				'unit' => fw_get_db_post_option($contract_id,'contract_unit'),
				'zalo' => fw_get_db_post_option($contract_id,'contract_zalo'),
			];

			$data = get_post_meta($contract_id, '_data', true);
			$contract_data = isset($data[$client])?$data[$client]:['required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'signed'=>''];

			if(empty($contract_data['value'])) $contract_data['value'] = $default_data['value'];
			if(empty($contract_data['unit'])) $contract_data['unit'] = $default_data['unit'];
			if(empty($contract_data['zalo'])) $contract_data['zalo'] = $default_data['zalo'];

			$response['zalo'] = ($contract_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($contract_data['zalo']).'" target="_blank">Zalo</a>':'';
			$response['required'] = (isset($contract_data['required']) && $contract_data['required']!='')?'<div class="bg-danger" title="Ngày gửi yêu cầu">'.esc_html(date('d/m', strtotime($contract_data['required']))).'</div>':'';
			$response['created'] = (isset($contract_data['created']) && $contract_data['created']!='')?'<div class="bg-danger" title="Ngày tạo hợp đồng">'.esc_html(date('d/m', strtotime($contract_data['created']))).'</div>':'';
			$response['completed'] = (isset($contract_data['completed']) && $contract_data['completed']!='')?'<div class="bg-danger" title="Ngày làm xong hợp đồng">'.esc_html(date('d/m', strtotime($contract_data['completed']))).'</div>':'';
			$response['sent'] = (isset($contract_data['sent']) && $contract_data['sent']!='')?'<div class="bg-danger" title="Ngày gửi cho khách">'.esc_html(date('d/m', strtotime($contract_data['sent']))).'</div>':'';

			$response['signed'] = (isset($contract_data['signed']) && $contract_data['signed']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã ký"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
		?>
			<div class="contract-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $contract_id )); ?>
			</div>
			<?php if($contract_data['value']!='' || $contract_data['unit']!='') { ?>
			<div class="contract-value mb-1">
				<?php if($contract_data['value']!='') { ?>
				<div>
					<span>Tổng giá trị: </span>
					<span class="text-red fw-bold"><?php echo esc_html($contract_data['value']); ?></span>
				</div>
				<?php } ?>
				<?php if($contract_data['unit']!='') { ?>
				<div class="text-red"><?php echo esc_html($contract_data['unit']); ?></div>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center contract-links mb-3">
				<?php
				if($contract_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($contract_data['url'])?>" target="_blank">Xem chi tiết</a>
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
			$contract_value = isset($_POST['contract_value'])?sanitize_text_field($_POST['contract_value']):'';
			$contract_unit = isset($_POST['contract_unit'])?sanitize_text_field($_POST['contract_unit']):'';
			$contract_zalo = isset($_POST['contract_zalo'])?sanitize_text_field($_POST['contract_zalo']):'';
			$contract_url = isset($_POST['contract_url'])?sanitize_url($_POST['contract_url']):'';

			$contract_required = isset($_POST['contract_required']) ? $_POST['contract_required'] : '';
			$contract_created = isset($_POST['contract_created']) ? $_POST['contract_created'] : '';
			$contract_completed = isset($_POST['contract_completed']) ? $_POST['contract_completed'] : '';
			$contract_sent = isset($_POST['contract_sent']) ? $_POST['contract_sent'] : '';
			$contract_signed = isset($_POST['contract_signed']) ? $_POST['contract_signed'] : '';

			if($contract_client && $contract_id) {
				$data = get_post_meta($contract_id, '_data', true);
				if(empty($data)) $data = [];
				$contract_data = isset($data[$contract_client])?$data[$contract_client]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>''];

				$new_contract_data = [
					'required' => $contract_required,
					'created' => $contract_created,
					'completed' => $contract_completed,
					'sent' => $contract_sent,
					'value' => $contract_value,
					'unit' => $contract_unit,
					'zalo' => $contract_zalo,
					'url' => $contract_url,
					'signed' => $contract_signed,
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
			$contract_data = isset($data[$client])?$data[$client]:['required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'signed'=>''];

			?>
			<form id="frm-edit-contract" method="POST" action="">
				<input type="hidden" id="contract_client" name="contract_client" value="<?=$client?>">
				<input type="hidden" id="contract_id" name="contract_id" value="<?=$contract?>">
				<?php wp_nonce_field( 'edit-contract', 'nonce' ); ?>
				<div id="edit-contract-response"></div>
				<div class="mb-3<?php echo (!current_user_can('edit_contracts'))?' hidden':''; ?>">
					Gửi yêu cầu
					<input class="form-control" type="date" value="<?php echo (isset($contract_data['required'])&&$contract_data['required']!='')?esc_html(date('Y-m-d', strtotime($contract_data['required']))):''; ?>" name="contract_required" id="contract_required">
				</div>
				<div class="mb-3">
					Tạo hợp đồng
					<input class="form-control" type="date" value="<?php echo (isset($contract_data['created'])&&$contract_data['created']!='')?esc_html(date('Y-m-d', strtotime($contract_data['created']))):''; ?>" name="contract_created" id="contract_created">
				</div>
				<div class="mb-3">
					Xong hợp đồng
					<input class="form-control" type="date" value="<?php echo (isset($contract_data['completed'])&&$contract_data['completed']!='')?esc_html(date('Y-m-d', strtotime($contract_data['completed']))):''; ?>" name="contract_completed" id="contract_completed">
				</div>
				<div class="mb-3">
					Ngày gửi khách
					<input class="form-control" type="date" value="<?php echo (isset($contract_data['sent'])&&$contract_data['sent']!='')?esc_html(date('Y-m-d', strtotime($contract_data['sent']))):''; ?>" name="contract_sent" id="contract_sent">
				</div>
				<div class="mb-3">
					<input type="text" id="contract_value" name="contract_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($contract_data['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="contract_unit" name="contract_unit" placeholder="Đơn vị" class="form-control" value="<?php echo esc_attr($contract_data['unit']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="contract_zalo" name="contract_zalo" placeholder="URL nhóm zalo" class="form-control" value="<?php echo esc_attr($contract_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="contract_url" name="contract_url" placeholder="Link hợp đồng" class="form-control" value="<?php echo ($contract_data['url'])?esc_url($contract_data['url']):''; ?>">
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="contract_signed" id="contract_signed" <?php checked( (isset($contract_data['signed']) && $contract_data['signed']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="contract_signed">Đã ký?</label>
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
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
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
