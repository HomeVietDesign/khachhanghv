<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Estimates extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_estimate_form', [$this, 'ajax_get_edit_estimate_form']);
      add_action( 'wp_ajax_update_estimate', [$this, 'ajax_update_estimate']);
      add_action( 'wp_ajax_get_estimate_info', [$this, 'ajax_get_estimate_info']);
	}

	public function ajax_get_estimate_info() {
		global $current_password;
		$default_term_password = get_option( 'default_term_passwords', -1 );

		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$contractor_id = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];
		
		if($client && $contractor_id) {
			$default_estimate_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
			$default_estimate = [
				'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
				'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
				'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
				'attachment_id' => ($default_estimate_attachment) ? $default_estimate_attachment['attachment_id']:''
			];

			$estimates = get_post_meta($contractor_id, '_estimates', true);
			$estimate = isset($estimates[$client])?$estimates[$client]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

			if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
			if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
			if(empty($estimate['zalo'])) $estimate['zalo'] = $default_estimate['zalo'];
			if(empty($estimate['attachment_id'])) $estimate['attachment_id'] = $default_estimate['attachment_id'];

			$phone_number = get_post_meta($contractor_id, '_phone_number', true);
			$external_url = get_post_meta($contractor_id, '_external_url', true);
			$external_url = ($external_url!='')?esc_url($external_url):'#';

			$cats = get_the_terms( $contractor_id, 'contractor_cat' );

			$response['zalo'] = ($estimate['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($estimate['zalo']).'" target="_blank">Zalo</a>':'';

			ob_start();
		?>
			<div class="contractor-title pt-3 mb-1 fs-5">
				<a class="d-block text-truncate" href="<?=$external_url?>" target="_blank" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
				<div class="text-truncate fs-6 text-yellow">
					<?php
					if($cats) {
						foreach ($cats as $key => $cat) {
							echo '<div>'.(($key>0)?', ':' ').esc_html($cat->name).'</div>';
						}
					}
					?>
				</div>
			</div>
			<?php if($estimate['value']) { ?>
			<div class="contractor-value mb-1">
				<span>Tổng giá trị:</span>
				<span class="text-red fw-bold"><?php echo  esc_html($estimate['value']); ?></span><span class="text-red"> <?php echo esc_html($estimate['unit']); ?></span>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center contractor-links mb-3">
				<?php
				if($phone_number) {
					?>
					<a class="btn btn-sm btn-danger my-1 mx-2" href="tel:<?=esc_attr($phone_number)?>"><?=esc_html($phone_number)?></a>
					<?php
				}

				if($estimate['attachment_id']) {
					$attachment_url = wp_get_attachment_url($estimate['attachment_id']);
					if($attachment_url){
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($attachment_url)?>" target="_blank">Xem chi tiết</a>
					<?php
					}
				}

				?>
			</div>
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_estimate() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(has_role('administrator') && check_ajax_referer( 'edit-estimate', 'nonce', false )) {
			$estimate_client = isset($_POST['estimate_client'])?absint($_POST['estimate_client']):0;
			$estimate_contractor = isset($_POST['estimate_contractor'])?absint($_POST['estimate_contractor']):0;
			$estimate_attachment_id = isset($_POST['estimate_attachment_id'])?absint($_POST['estimate_attachment_id']):0;
			$estimate_value = isset($_POST['estimate_value'])?sanitize_text_field($_POST['estimate_value']):'';
			$estimate_unit = isset($_POST['estimate_unit'])?sanitize_text_field($_POST['estimate_unit']):'';
			$estimate_zalo = isset($_POST['estimate_zalo'])?sanitize_text_field($_POST['estimate_zalo']):'';
			$estimate_attachment = isset($_FILES['estimate_attachment']) ? $_FILES['estimate_attachment'] : null;

			if($estimate_client && $estimate_contractor) {
				$estimates = get_post_meta($estimate_contractor, '_estimates', true);
				if(empty($estimates)) $estimates = [];
				$estimate = isset($estimates[$estimate_client])?$estimates[$estimate_client]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

				$new_estimate = [
					'value' => $estimate_value,
					'unit' => $estimate_unit,
					'zalo' => $estimate_zalo,
					'attachment_id' => ($estimate_attachment_id!=0)?$estimate_attachment_id:''
				];

				// tải lên file dự toán
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					require_once(ABSPATH . "wp-admin" . '/includes/file.php');
					require_once(ABSPATH . "wp-admin" . '/includes/media.php');
				}

				$estimate_attachment_upload = media_handle_upload( 'estimate_attachment', $estimate_contractor );

				if ($estimate_attachment['error']==0 && $estimate_attachment_upload && ! is_array( $estimate_attachment_upload ) ) {
					$new_estimate['attachment_id'] = $estimate_attachment_upload;
					if($estimate_attachment_id) wp_delete_attachment($estimate_attachment_id, true);
				}

				if($new_estimate['attachment_id']=='' || $new_estimate['attachment_id']==0) {
					if($estimate['attachment_id']) wp_delete_attachment($estimate['attachment_id'], true);
				}

				$estimates[$estimate_client] = $new_estimate;

				update_post_meta( $estimate_contractor, '_estimates', $estimates );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_estimate_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$contractor = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		if($client && $contractor) {
			$estimates = get_post_meta($contractor, '_estimates', true);
			$estimate = isset($estimates[$client])?$estimates[$client]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

			$attachment_url = ($estimate['attachment_id'])?wp_get_attachment_url($estimate['attachment_id']):'';
			?>
			<form id="frm-edit-estimate" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="estimate_client" name="estimate_client" value="<?=$client?>">
				<input type="hidden" id="estimate_contractor" name="estimate_contractor" value="<?=$contractor?>">
				<?php wp_nonce_field( 'edit-estimate', 'nonce' ); ?>
				<div id="edit-estimate-response"></div>
				<div class="mb-3">
					<input type="text" id="estimate_value" name="estimate_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($estimate['value']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="estimate_unit" name="estimate_unit" placeholder="Đơn vị" class="form-control" value="<?php echo esc_attr($estimate['unit']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="estimate_zalo" name="estimate_zalo" placeholder="Link nhóm zalo" class="form-control" value="<?php echo esc_attr($estimate['zalo']); ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dự toán</div>
					<input type="hidden" id="estimate_attachment_id" name="estimate_attachment_id" value="<?=esc_attr($estimate['attachment_id'])?>">
					<?php if($attachment_url) { ?>
					<div class="mb-2">
						<span class="overflow-hidden"><?=esc_html(basename($attachment_url))?></span>
						<button class="btn btn-sm btn-danger" id="estimate_remove_attachment">Xóa file</button>
					</div>
					<?php } ?>
					<label class="d-block" for="estimate_attachment">
						<span class="input-group">
							<span class="form-control overflow-hidden"></span>
							<span class="input-group-text">Bấm tải lên</span>
						</span>
						<div style="width: 0;height: 0;overflow: hidden;">
							<input type="file" id="estimate_attachment" name="estimate_attachment" accept=".pdf" class="form-control">
						</div>
					</label>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-estimate-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-estimate" tabindex="-1" role="dialog" aria-labelledby="edit-estimate-label">
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-estimate-label">Sửa dự toán</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
