<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Gzalo extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_gzalo_form', [$this, 'ajax_get_edit_gzalo_form']);
      add_action( 'wp_ajax_update_gzalo', [$this, 'ajax_update_gzalo']);
      add_action( 'wp_ajax_get_gzalo_info', [$this, 'ajax_get_gzalo_info']);
      add_action( 'wp_ajax_gzalo_hide', [$this, 'ajax_gzalo_hide']);
	}

	public function ajax_gzalo_hide() {
		global $current_client;
		$gzalo_id = isset($_POST['gzalo']) ? absint($_POST['gzalo']) : 0;
		$response = false;
		if(current_user_can('gzalo_edit') && $current_client && $gzalo_id && check_ajax_referer( 'global', 'nonce', false )) {
			$gzalo_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'gzalo_hide', []);
			$gzalo_hide[] = $gzalo_id;
			fw_set_db_term_option($current_client->term_id, 'passwords', 'gzalo_hide', $gzalo_hide);
			$response = true;
		}
		wp_send_json($response);
	}

	public function ajax_get_gzalo_info() {

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$gzalo_id = isset($_GET['gzalo'])?absint($_GET['gzalo']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];
		
		if($client && $gzalo_id) {
			//$default_url = fw_get_db_post_option($gzalo_id,'gzalo_url');
			$default_data = [
				'value' => fw_get_db_post_option($gzalo_id,'gzalo_value'),
				'unit' => fw_get_db_post_option($gzalo_id,'gzalo_unit'),
				'zalo' => fw_get_db_post_option($gzalo_id,'gzalo_zalo'),
			];

			$data = get_post_meta($gzalo_id, '_data', true);
			$gzalo_data = isset($data[$client])?$data[$client]:['required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'signed'=>''];

			if(empty($gzalo_data['value'])) $gzalo_data['value'] = $default_data['value'];
			if(empty($gzalo_data['unit'])) $gzalo_data['unit'] = $default_data['unit'];
			if(empty($gzalo_data['zalo'])) $gzalo_data['zalo'] = $default_data['zalo'];

			$response['zalo'] = ($gzalo_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($gzalo_data['zalo']).'" target="_blank">Zalo</a>':'';
			$response['required'] = (isset($gzalo_data['required']) && $gzalo_data['required']!='')?'<div class="bg-danger" title="Ngày gửi yêu cầu">'.esc_html(date('d/m', strtotime($gzalo_data['required']))).'</div>':'';
			$response['created'] = (isset($gzalo_data['created']) && $gzalo_data['created']!='')?'<div class="bg-danger" title="Ngày tạo hợp đồng">'.esc_html(date('d/m', strtotime($gzalo_data['created']))).'</div>':'';
			$response['completed'] = (isset($gzalo_data['completed']) && $gzalo_data['completed']!='')?'<div class="bg-danger" title="Ngày làm xong hợp đồng">'.esc_html(date('d/m', strtotime($gzalo_data['completed']))).'</div>':'';
			$response['sent'] = (isset($gzalo_data['sent']) && $gzalo_data['sent']!='')?'<div class="bg-danger" title="Ngày gửi cho khách">'.esc_html(date('d/m', strtotime($gzalo_data['sent']))).'</div>':'';

			$response['signed'] = (isset($gzalo_data['signed']) && $gzalo_data['signed']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã ký"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
		?>
			<div class="gzalo-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $gzalo_id )); ?>
			</div>
			<?php if($gzalo_data['value']!='' || $gzalo_data['unit']!='') { ?>
			<div class="gzalo-value mb-1">
				<?php if($gzalo_data['value']!='') { ?>
				<div>
					<span>Tổng giá trị: </span>
					<span class="text-red fw-bold"><?php echo esc_html($gzalo_data['value']); ?></span>
				</div>
				<?php } ?>
				<?php if($gzalo_data['unit']!='') { ?>
				<div class="text-red"><?php echo esc_html($gzalo_data['unit']); ?></div>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center gzalo-links mb-3">
				<?php
				if($gzalo_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($gzalo_data['url'])?>" target="_blank">Xem chi tiết</a>
					<?php
				}
				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_gzalo() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('gzalo_edit') && check_ajax_referer( 'edit-gzalo', 'nonce', false )) {
			$gzalo_client = isset($_POST['gzalo_client'])?absint($_POST['gzalo_client']):0;
			$gzalo_id = isset($_POST['gzalo_id'])?absint($_POST['gzalo_id']):0;
			$gzalo_value = isset($_POST['gzalo_value'])?sanitize_text_field($_POST['gzalo_value']):'';
			$gzalo_unit = isset($_POST['gzalo_unit'])?sanitize_text_field($_POST['gzalo_unit']):'';
			$gzalo_zalo = isset($_POST['gzalo_zalo'])?sanitize_text_field($_POST['gzalo_zalo']):'';
			$gzalo_url = isset($_POST['gzalo_url'])?sanitize_url($_POST['gzalo_url']):'';

			$gzalo_required = isset($_POST['gzalo_required']) ? $_POST['gzalo_required'] : '';
			$gzalo_created = isset($_POST['gzalo_created']) ? $_POST['gzalo_created'] : '';
			$gzalo_completed = isset($_POST['gzalo_completed']) ? $_POST['gzalo_completed'] : '';
			$gzalo_sent = isset($_POST['gzalo_sent']) ? $_POST['gzalo_sent'] : '';
			$gzalo_signed = isset($_POST['gzalo_signed']) ? $_POST['gzalo_signed'] : '';

			if($gzalo_client && $gzalo_id) {
				$data = get_post_meta($gzalo_id, '_data', true);
				if(empty($data)) $data = [];
				$gzalo_data = isset($data[$gzalo_client])?$data[$gzalo_client]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>''];

				$new_gzalo_data = [
					'required' => $gzalo_required,
					'created' => $gzalo_created,
					'completed' => $gzalo_completed,
					'sent' => $gzalo_sent,
					'value' => $gzalo_value,
					'unit' => $gzalo_unit,
					'zalo' => $gzalo_zalo,
					'url' => $gzalo_url,
					'signed' => $gzalo_signed,
				];

				$data[$gzalo_client] = $new_gzalo_data;

				update_post_meta( $gzalo_id, '_data', $data );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_gzalo_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$gzalo = isset($_GET['gzalo'])?absint($_GET['gzalo']):0;

		if($client && $gzalo) {
			$data = get_post_meta($gzalo, '_data', true);
			$gzalo_data = isset($data[$client])?$data[$client]:['required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'signed'=>''];

			?>
			<form id="frm-edit-gzalo" method="POST" action="">
				<input type="hidden" id="gzalo_client" name="gzalo_client" value="<?=$client?>">
				<input type="hidden" id="gzalo_id" name="gzalo_id" value="<?=$gzalo?>">
				<?php wp_nonce_field( 'edit-gzalo', 'nonce' ); ?>
				<div id="edit-gzalo-response"></div>
				<div class="mb-3<?php echo (!current_user_can('edit_gzalos'))?' hidden':''; ?>">
					Gửi yêu cầu
					<input class="form-control" type="date" value="<?php echo (isset($gzalo_data['required'])&&$gzalo_data['required']!='')?esc_html(date('Y-m-d', strtotime($gzalo_data['required']))):''; ?>" name="gzalo_required" id="gzalo_required">
				</div>
				<div class="mb-3">
					Tạo hợp đồng
					<input class="form-control" type="date" value="<?php echo (isset($gzalo_data['created'])&&$gzalo_data['created']!='')?esc_html(date('Y-m-d', strtotime($gzalo_data['created']))):''; ?>" name="gzalo_created" id="gzalo_created">
				</div>
				<div class="mb-3">
					Xong hợp đồng
					<input class="form-control" type="date" value="<?php echo (isset($gzalo_data['completed'])&&$gzalo_data['completed']!='')?esc_html(date('Y-m-d', strtotime($gzalo_data['completed']))):''; ?>" name="gzalo_completed" id="gzalo_completed">
				</div>
				<div class="mb-3">
					Ngày gửi khách
					<input class="form-control" type="date" value="<?php echo (isset($gzalo_data['sent'])&&$gzalo_data['sent']!='')?esc_html(date('Y-m-d', strtotime($gzalo_data['sent']))):''; ?>" name="gzalo_sent" id="gzalo_sent">
				</div>
				<div class="mb-3">
					<input type="text" id="gzalo_value" name="gzalo_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($gzalo_data['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="gzalo_unit" name="gzalo_unit" placeholder="Đơn vị" class="form-control" value="<?php echo esc_attr($gzalo_data['unit']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="gzalo_zalo" name="gzalo_zalo" placeholder="URL nhóm zalo" class="form-control" value="<?php echo esc_attr($gzalo_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="gzalo_url" name="gzalo_url" placeholder="Link hợp đồng" class="form-control" value="<?php echo ($gzalo_data['url'])?esc_url($gzalo_data['url']):''; ?>">
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="gzalo_signed" id="gzalo_signed" <?php checked( (isset($gzalo_data['signed']) && $gzalo_data['signed']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="gzalo_signed">Đã ký?</label>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-gzalo-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-gzalo" tabindex="-1" role="dialog" aria-labelledby="edit-gzalo-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-gzalo-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
