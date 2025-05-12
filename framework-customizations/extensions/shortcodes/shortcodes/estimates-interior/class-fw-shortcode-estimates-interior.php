<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Estimates_Interior extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_estimate_interior_form', [$this, 'ajax_get_edit_estimate_interior_form']);
      add_action( 'wp_ajax_update_estimate_interior', [$this, 'ajax_update_estimate_interior']);
      add_action( 'wp_ajax_get_estimate_interior_info', [$this, 'ajax_get_estimate_interior_info']);
	}

	public function ajax_get_estimate_interior_info() {
		global $current_password;
		$default_term_password = get_option( 'default_term_passwords', -1 );

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$interior_id = isset($_GET['interior'])?absint($_GET['interior']):0;

		$estimates = get_post_meta($interior_id, '_estimates', true);
		$estimate = isset($estimates[$client])?$estimates[$client]:['value'=>'', 'url'=>''];

		if($client && $interior_id) {
		?>
			<div class="interior-title pt-3 mb-1 fs-5 text-uppercase">
				<?php echo esc_html(get_the_title( $interior_id )); ?>
			</div>
			
			<div class="interior-value mb-1">
				<span>Tổng giá trị:</span>
				<span class="text-red fw-bold"><?php echo ($estimate['value']) ? esc_html(number_format($estimate['value'],0,'.',',')) : ''; ?></span>
			</div>
			
			<div class="d-flex flex-wrap justify-content-center interior-links mb-3">
				<?php
				if($estimate['url']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($estimate['url'])?>" target="_blank">Dự toán</a>
					<?php
				}
				?>
			</div>
		<?php
		}
		exit;
	}

	public function ajax_update_estimate_interior() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(has_role('administrator') && check_ajax_referer( 'edit-estimate-interior', 'nonce', false )) {
			$estimate_client = isset($_POST['estimate_client'])?absint($_POST['estimate_client']):0;
			$estimate_interior = isset($_POST['estimate_interior'])?absint($_POST['estimate_interior']):0;
			$estimate_interior_value = isset($_POST['estimate_interior_value'])?absint(str_replace(',', '', $_POST['estimate_interior_value'])):0;
			$estimate_interior_url = isset($_POST['estimate_interior_url'])?sanitize_url($_POST['estimate_interior_url']):'';
			
			if($estimate_client && $estimate_interior) {
				$estimates = get_post_meta($estimate_interior, '_estimates', true);
				if(empty($estimates)) $estimates = [];
				$estimate = isset($estimates[$estimate_client])?$estimates[$estimate_client]:[ 'value'=>'', 'url'=>''];

				$new_estimate = [
					'value' => $estimate_interior_value,
					'url' => $estimate_interior_url
				];

				$estimates[$estimate_client] = $new_estimate;

				update_post_meta( $estimate_interior, '_estimates', $estimates );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_estimate_interior_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$interior = isset($_GET['interior'])?absint($_GET['interior']):0;

		if($client && $interior) {
			$estimates = get_post_meta($interior, '_estimates', true);
			$estimate = isset($estimates[$client])?$estimates[$client]:['value'=>'', 'url'=>''];

			?>
			<form id="frm-edit-estimate-interior" method="POST" action="" >
				<input type="hidden" id="estimate_client" name="estimate_client" value="<?=$client?>">
				<input type="hidden" id="estimate_interior" name="estimate_interior" value="<?=$interior?>">
				<?php wp_nonce_field( 'edit-estimate-interior', 'nonce' ); ?>
				<div id="edit-estimate-interior-response"></div>
				<div class="mb-3">
					<input type="text" id="estimate_interior_value" name="estimate_interior_value" placeholder="Giá trị" class="form-control" value="<?php echo ($estimate['value'])?esc_attr(number_format(absint($estimate['value']),0,'.',',')):''; ?>">
				</div>
				<div class="mb-3">
					<textarea id="estimate_interior_url" name="estimate_interior_url" placeholder="URL dự toán" class="form-control" rows="2"><?php echo ($estimate['url'])?esc_textarea($estimate['url']):''; ?></textarea>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-estimate-interior-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-estimate-interior" tabindex="-1" role="dialog" aria-labelledby="edit-estimate-interior-label">
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-estimate-interior-label">Sửa dự toán</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
