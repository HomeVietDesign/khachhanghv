<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Nha88 extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_nha88_form', [$this, 'ajax_get_edit_nha88_form']);
      add_action( 'wp_ajax_update_nha88', [$this, 'ajax_update_nha88']);
      add_action( 'wp_ajax_get_nha88_info', [$this, 'ajax_get_nha88_info']);
      add_action( 'wp_ajax_nha88_hide', [$this, 'ajax_nha88_hide']);
	}

	public function ajax_nha88_hide() {
		global $current_nha88_type;
		$nha88_id = isset($_POST['nha88']) ? absint($_POST['nha88']) : 0;
		$response = 0;
		if(current_user_can('nha88_edit') && $current_nha88_type && $nha88_id && check_ajax_referer( 'global', 'nonce', false )) {
			
			$nha88_hide = get_term_meta($current_nha88_type->term_id, 'nha88_hide', true);
			if(empty($nha88_hide)) $nha88_hide = [];

			if(in_array($nha88_id, $nha88_hide)) {
				unset($nha88_hide[array_search($nha88_id, $nha88_hide)]);
				$response = -1;
			} else {
				$nha88_hide[] = $nha88_id;
				$response = 1;
			}

			update_term_meta($current_nha88_type->term_id, 'nha88_hide', $nha88_hide);
		}
		wp_send_json($response);
	}

	public function ajax_get_nha88_info() {

		$nha88_type = isset($_GET['nha88_type'])?absint($_GET['nha88_type']):0;
		$nha88_id = isset($_GET['nha88'])?absint($_GET['nha88']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];
		
		if($nha88_type && $nha88_id) {
			//$default_url = fw_get_db_post_option($nha88_id,'nha88_url');
			$default_data = [
				'value' => fw_get_db_post_option($nha88_id,'nha88_value'),
				'note' => fw_get_db_post_option($nha88_id,'nha88_note'),
				'zalo' => fw_get_db_post_option($nha88_id,'nha88_zalo'),
			];

			$data = get_post_meta($nha88_id, '_data', true);
			$nha88_data = isset($data[$nha88_type])?$data[$nha88_type]:['required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'note'=>'', 'zalo'=>'', 'url'=>'', 'sold'=>''];

			if(empty($nha88_data['value'])) $nha88_data['value'] = $default_data['value'];
			if(empty($nha88_data['note'])) $nha88_data['note'] = $default_data['note'];
			if(empty($nha88_data['zalo'])) $nha88_data['zalo'] = $default_data['zalo'];

			$response['zalo'] = ($nha88_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($nha88_data['zalo']).'" target="_blank">Zalo</a>':'';
			$response['required'] = (isset($nha88_data['required']) && $nha88_data['required']!='')?'<div class="bg-danger" title="Ngày gửi yêu cầu">'.esc_html(date('d/m', strtotime($nha88_data['required']))).'</div>':'';
			$response['created'] = (isset($nha88_data['created']) && $nha88_data['created']!='')?'<div class="bg-danger" title="Ngày làm">'.esc_html(date('d/m', strtotime($nha88_data['created']))).'</div>':'';
			$response['completed'] = (isset($nha88_data['completed']) && $nha88_data['completed']!='')?'<div class="bg-danger" title="Ngày làm xong">'.esc_html(date('d/m', strtotime($nha88_data['completed']))).'</div>':'';
			$response['sent'] = (isset($nha88_data['sent']) && $nha88_data['sent']!='')?'<div class="bg-danger" title="Ngày gửi cho khách">'.esc_html(date('d/m', strtotime($nha88_data['sent']))).'</div>':'';

			$response['sold'] = (isset($nha88_data['sold']) && $nha88_data['sold']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã mua"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
		?>
			<div class="nha88-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title( $nha88_id )); ?>
			</div>
			<?php if($nha88_data['value']!='' || $nha88_data['note']!='') { ?>
			<div class="nha88-value mb-1">
				<?php if($nha88_data['value']!='') { ?>
				<div>
					<span>Tổng giá trị: </span>
					<span class="text-red fw-bold"><?php echo esc_html($nha88_data['value']); ?></span>
				</div>
				<?php } ?>
				<?php if($nha88_data['note']!='') { ?>
				<div class="text-red"><?php echo esc_html($nha88_data['note']); ?></div>
				<?php } ?>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center nha88-links mb-3">
				<?php
				if($nha88_data['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($nha88_data['url'])?>" target="_blank">Xem chi tiết</a>
					<?php
				}
				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_nha88() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('nha88_edit') && check_ajax_referer( 'edit-nha88', 'nonce', false )) {
			$nha88_type = isset($_POST['nha88_type'])?absint($_POST['nha88_type']):0;
			$nha88_id = isset($_POST['nha88_id'])?absint($_POST['nha88_id']):0;
			$nha88_value = isset($_POST['nha88_value'])?sanitize_text_field($_POST['nha88_value']):'';
			$nha88_note = isset($_POST['nha88_note'])?sanitize_text_field($_POST['nha88_note']):'';
			$nha88_zalo = isset($_POST['nha88_zalo'])?sanitize_text_field($_POST['nha88_zalo']):'';
			$nha88_url = isset($_POST['nha88_url'])?sanitize_url($_POST['nha88_url']):'';

			$nha88_required = isset($_POST['nha88_required']) ? $_POST['nha88_required'] : '';
			$nha88_created = isset($_POST['nha88_created']) ? $_POST['nha88_created'] : '';
			$nha88_completed = isset($_POST['nha88_completed']) ? $_POST['nha88_completed'] : '';
			$nha88_sent = isset($_POST['nha88_sent']) ? $_POST['nha88_sent'] : '';
			$nha88_sold = isset($_POST['nha88_sold']) ? $_POST['nha88_sold'] : '';

			if($nha88_type && $nha88_id) {
				$data = get_post_meta($nha88_id, '_data', true);
				if(empty($data)) $data = [];
				$nha88_data = isset($data[$nha88_type])?$data[$nha88_type]:[ 'value'=>'', 'note'=>'', 'zalo'=>'', 'url'=>''];

				$new_nha88_data = [
					'required' => $nha88_required,
					'created' => $nha88_created,
					'completed' => $nha88_completed,
					'sent' => $nha88_sent,
					'value' => $nha88_value,
					'note' => $nha88_note,
					'zalo' => $nha88_zalo,
					'url' => $nha88_url,
					'sold' => $nha88_sold,
				];

				$data[$nha88_type] = $new_nha88_data;

				update_post_meta( $nha88_id, '_data', $data );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_nha88_form() {
		$nha88_type = isset($_GET['nha88_type'])?absint($_GET['nha88_type']):0;
		$nha88 = isset($_GET['nha88'])?absint($_GET['nha88']):0;

		if($nha88_type && $nha88) {
			$data = get_post_meta($nha88, '_data', true);
			$nha88_data = isset($data[$nha88_type])?$data[$nha88_type]:['required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'note'=>'', 'zalo'=>'', 'url'=>'', 'sold'=>''];

			?>
			<form id="frm-edit-nha88" method="POST" action="">
				<input type="hidden" id="nha88_type" name="nha88_type" value="<?=$nha88_type?>">
				<input type="hidden" id="nha88_id" name="nha88_id" value="<?=$nha88?>">
				<?php wp_nonce_field( 'edit-nha88', 'nonce' ); ?>
				<div id="edit-nha88-response"></div>
				<div class="mb-3<?php echo (!current_user_can('edit_nha88s'))?' hidden':''; ?>">
					Gửi yêu cầu
					<input class="form-control" type="date" value="<?php echo (isset($nha88_data['required'])&&$nha88_data['required']!='')?esc_html(date('Y-m-d', strtotime($nha88_data['required']))):''; ?>" name="nha88_required" id="nha88_required">
				</div>
				<div class="mb-3">
					Tạo hợp đồng
					<input class="form-control" type="date" value="<?php echo (isset($nha88_data['created'])&&$nha88_data['created']!='')?esc_html(date('Y-m-d', strtotime($nha88_data['created']))):''; ?>" name="nha88_created" id="nha88_created">
				</div>
				<div class="mb-3">
					Xong hợp đồng
					<input class="form-control" type="date" value="<?php echo (isset($nha88_data['completed'])&&$nha88_data['completed']!='')?esc_html(date('Y-m-d', strtotime($nha88_data['completed']))):''; ?>" name="nha88_completed" id="nha88_completed">
				</div>
				<div class="mb-3">
					Ngày gửi khách
					<input class="form-control" type="date" value="<?php echo (isset($nha88_data['sent'])&&$nha88_data['sent']!='')?esc_html(date('Y-m-d', strtotime($nha88_data['sent']))):''; ?>" name="nha88_sent" id="nha88_sent">
				</div>
				<div class="mb-3">
					<input type="text" id="nha88_value" name="nha88_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($nha88_data['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="nha88_note" name="nha88_note" placeholder="Ghi chú" class="form-control" value="<?php echo esc_attr($nha88_data['note']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="nha88_zalo" name="nha88_zalo" placeholder="URL nhóm zalo" class="form-control" value="<?php echo esc_attr($nha88_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="nha88_url" name="nha88_url" placeholder="Link hợp đồng" class="form-control" value="<?php echo ($nha88_data['url'])?esc_url($nha88_data['url']):''; ?>">
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="nha88_sold" id="nha88_sold" <?php checked( (isset($nha88_data['sold']) && $nha88_data['sold']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="nha88_sold">Đã bán?</label>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-nha88-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-nha88" tabindex="-1" role="dialog" aria-labelledby="edit-nha88-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-nha88-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
