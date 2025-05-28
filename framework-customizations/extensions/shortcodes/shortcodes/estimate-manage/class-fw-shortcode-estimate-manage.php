<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Estimate_Manage extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_estimate_manage_form', [$this, 'ajax_get_edit_estimate_manage_form']);
      add_action( 'wp_ajax_update_estimate_manage', [$this, 'ajax_update_estimate_manage']);
      add_action( 'wp_ajax_get_estimate_manage_info', [$this, 'ajax_get_estimate_manage_info']);
	}

	public function ajax_get_estimate_manage_info() {
		global $current_password;
		$default_term_password = get_option( 'default_term_passwords', -1 );

		$estimate_client = isset($_GET['estimate_client'])?absint($_GET['estimate_client']):0;
		$estimate_id = isset($_GET['estimate_id'])?absint($_GET['estimate_id']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];

		if($estimate_client && $estimate_id) {
			$default_estimate = [
				'value' => fw_get_db_post_option($estimate_id,'estimate_value'),
				'unit' => fw_get_db_post_option($estimate_id,'estimate_unit'),
				'zalo' => fw_get_db_post_option($estimate_id,'estimate_zalo'),
				'url' => fw_get_db_post_option($estimate_id,'estimate_url')
			];

			$client_estimates = get_term_meta($estimate_client, '_estimates', true);
			$client_estimate = isset($client_estimates[$estimate_id])?$client_estimates[$estimate_id]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>''];

			if(empty($client_estimate['value'])) $client_estimate['value'] = $default_estimate['value'];
			if(empty($client_estimate['unit'])) $client_estimate['unit'] = $default_estimate['unit'];
			if(empty($client_estimate['zalo'])) $client_estimate['zalo'] = $default_estimate['zalo'];
			if(empty($client_estimate['url'])) $client_estimate['url'] = $default_estimate['url'];

			$response['zalo'] = ($client_estimate['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($client_estimate['zalo']).'" target="_blank">Zalo</a>':'';

			ob_start();
		?>
			<div class="estimate-title pt-3 mb-1 fs-5 text-uppercase">
				<?php echo esc_html(get_the_title( $estimate_id )); ?>
			</div>
			<?php if($client_estimate['value']) { ?>
			<div class="estimate-value mb-1">
				<span>Tổng giá trị:</span>
				<span class="text-red fw-bold"><?php echo esc_html($client_estimate['value']); ?></span>
				<div class="text-red"> <?php echo esc_html($client_estimate['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center estimate-url mb-3">
				<?php
				if($client_estimate['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($client_estimate['url'])?>" target="_blank">Xem chi tiết</a>
					<?php
				}
				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		wp_send_json($response);
	}

	public function ajax_update_estimate_manage() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(has_role('administrator') && check_ajax_referer( 'edit-estimate-manage', 'nonce', false )) {
			$estimate_client = isset($_POST['estimate_client'])?absint($_POST['estimate_client']):0;
			$estimate_id = isset($_POST['estimate_id'])?absint($_POST['estimate_id']):0;
			$estimate_client_value = isset($_POST['estimate_client_value'])?sanitize_text_field($_POST['estimate_client_value']):'';
			$estimate_client_unit = isset($_POST['estimate_client_unit'])?sanitize_text_field($_POST['estimate_client_unit']):'';
			$estimate_client_zalo = isset($_POST['estimate_client_zalo'])?sanitize_text_field($_POST['estimate_client_zalo']):'';
			$estimate_client_url = isset($_POST['estimate_client_url'])?sanitize_url($_POST['estimate_client_url']):'';
			
			if($estimate_client && $estimate_id) {
				$client_estimates = get_term_meta($estimate_client, '_estimates', true);
				if(empty($client_estimates)) $client_estimates = [];
				$client_estimate = isset($client_estimates[$estimate_id])?$client_estimates[$estimate_id]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>''];

				$new_client_estimate = [
					'value' => $estimate_client_value,
					'unit' => $estimate_client_unit,
					'zalo' => $estimate_client_zalo,
					'url' => $estimate_client_url
				];

				$client_estimates[$estimate_id] = $new_client_estimate;

				update_term_meta( $estimate_client, '_estimates', $client_estimates );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_estimate_manage_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$estimate = isset($_GET['estimate'])?absint($_GET['estimate']):0;

		if($client && $estimate) {
			$client_estimates = get_term_meta($client, '_estimates', true);
			$client_estimate = isset($client_estimates[$estimate])?$client_estimates[$estimate]:['value'=>'', 'zalo'=>'', 'url'=>''];

			?>
			<form id="frm-edit-estimate-manage" method="POST" action="">
				<input type="hidden" id="estimate_client" name="estimate_client" value="<?=$client?>">
				<input type="hidden" id="estimate_id" name="estimate_id" value="<?=$estimate?>">
				<?php wp_nonce_field( 'edit-estimate-manage', 'nonce' ); ?>
				<div id="edit-estimate-manage-response"></div>
				<div class="mb-3">
					<input type="text" id="estimate_client_value" name="estimate_client_value" placeholder="Giá trị" class="form-control text-center" value="<?php echo esc_attr($client_estimate['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="estimate_client_unit" name="estimate_client_unit" placeholder="Ghi chú" class="form-control text-center" value="<?php echo esc_attr($client_estimate['unit']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="estimate_client_zalo" name="estimate_client_zalo" placeholder="Link nhóm zalo" class="form-control text-center" value="<?php echo esc_attr($client_estimate['zalo']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="estimate_client_url" name="estimate_client_url" placeholder="Link dự toán" class="form-control text-center" value="<?php echo ($client_estimate['url'])?esc_url($client_estimate['url']):''; ?>">
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-estimate-manage-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-estimate-manage" tabindex="-1" role="dialog" aria-labelledby="edit-estimate-manage-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-estimate-manage-label">Sửa dự toán</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
