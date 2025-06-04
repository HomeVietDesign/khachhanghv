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
		global $current_password, $current_client;
		$default_term_password = get_option( 'default_term_passwords', -1 );

		$contractor_id = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		$response = [
			'info' => '',
			'zalo' => '',
			'attachment' => '',
			'required' => '',
			'received' => '',
			'completed' => '',
			'quote' => '',
		];
		
		if($current_client && $contractor_id) {
			$default_estimate_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
			$default_estimate = [
				'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
				'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
				'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
			];

			$estimates = get_post_meta($contractor_id, '_estimates', true);
			if(empty($estimates)) $estimates = [];
			
			$estimate = isset($estimates[$current_client->term_id])?$estimates[$current_client->term_id]:['required'=>'', 'received'=>'', 'completed'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'link'=>'', 'attachment_id'=>'', 'quote'=>''];

			if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
			if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
			if(empty($estimate['zalo'])) $estimate['zalo'] = $default_estimate['zalo'];

			$phone_number = get_post_meta($contractor_id, '_phone_number', true);
			//$external_url = get_post_meta($contractor_id, '_external_url', true);
			//$external_url = ($external_url!='')?esc_url($external_url):'#';

			$cats = get_the_terms( $contractor_id, 'contractor_cat' );

			$response['zalo'] = ($estimate['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($estimate['zalo']).'" target="_blank">Zalo</a>':'';
			$response['attachment'] = ($estimate['attachment_id'])?'<a class="btn-shadow btn btn-sm btn-primary" href="'.esc_url(wp_get_attachment_url($estimate['attachment_id'])).'" target="_blank">Tải</a>':'';

			$response['required'] = (isset($estimate['required']) && $estimate['required']!='')?'<div title="Ngày gửi đề bài yêu cầu">'.esc_html(date('d/m/Y', strtotime($estimate['required']))).'</div>':'';
			$response['received'] = (isset($estimate['received']) && $estimate['received']!='')?'<div title="Ngày nhận dự toán nhà thầu">'.esc_html(date('d/m/Y', strtotime($estimate['received']))).'</div>':'';
			$response['completed'] = (isset($estimate['completed']) && $estimate['completed']!='')?'<div title="Ngày nhận dự toán khách hàng">'.esc_html(date('d/m/Y', strtotime($estimate['completed']))).'</div>':'';

			$response['quote'] = (isset($estimate['quote']) && $estimate['quote']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Đã gửi dự toán khách hàng"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
		?>
			<div class="contractor-title pt-3 mb-1 fs-5">
				<a class="d-block" href="#" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
				<div class="fs-6 text-yellow">
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
				<span class="text-red fw-bold"><?php echo  esc_html($estimate['value']); ?></span>
				
			</div>
			<?php } ?>
			<?php if($estimate['unit']) { ?>
			<div class="contractor-unit mb-1">
				<div class="text-red"><?php echo esc_html($estimate['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center contractor-links mb-3">
				<?php
				if(client_can_view()) {
					if(empty($estimate['info']) && $phone_number) {
					?>
					<a class="btn btn-sm btn-danger my-1 mx-2" href="tel:<?=esc_attr($phone_number)?>"><?=esc_html($phone_number)?></a>
					<?php
					} else if(!empty($estimate['info'])) {
						?>
						<span class="bg-danger px-2 py-1 rounded-1 my-1 mx-2"><?=esc_html($estimate['info'])?></span>
						<?php
					}
				}

				if($estimate['link']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($estimate['link'])?>" target="_blank">Xem chi tiết</a>
					<?php
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
			$estimate_info = isset($_POST['estimate_info'])?sanitize_text_field($_POST['estimate_info']):'';
			$estimate_link = isset($_POST['estimate_link'])?sanitize_text_field($_POST['estimate_link']):'';
			$estimate_attachment = isset($_FILES['estimate_attachment']) ? $_FILES['estimate_attachment'] : null;

			$estimate_required = isset($_POST['estimate_required']) ? $_POST['estimate_required'] : '';
			$estimate_received = isset($_POST['estimate_received']) ? $_POST['estimate_received'] : '';
			$estimate_completed = isset($_POST['estimate_completed']) ? $_POST['estimate_completed'] : '';
			$estimate_quote = isset($_POST['estimate_quote']) ? $_POST['estimate_quote'] : '';

			//debug_log($_POST);

			if($estimate_client && $estimate_contractor) {
				$estimates = get_post_meta($estimate_contractor, '_estimates', true);
				if(empty($estimates)) $estimates = [];
				$estimate = isset($estimates[$estimate_client])?$estimates[$estimate_client]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'link'=>'', 'attachment_id'=>''];

				$new_estimate = [
					'required' => $estimate_required,
					'received' => $estimate_received,
					'completed' => $estimate_completed,
					'value' => $estimate_value,
					'unit' => $estimate_unit,
					'zalo' => $estimate_zalo,
					'info' => $estimate_info,
					'link' => $estimate_link,
					'attachment_id' => ($estimate_attachment_id!=0)?$estimate_attachment_id:'',
					'quote' => $estimate_quote,
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
					if(isset($estimate['attachment_id']) && $estimate['attachment_id']) wp_delete_attachment($estimate['attachment_id'], true);
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
			if(empty($estimates)) $estimates = [];
			
			$estimate = isset($estimates[$client])?$estimates[$client]:['required'=>'', 'received'=>'', 'completed'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'link'=>'', 'attachment_id'=>'', 'quote'=>''];

			$attachment_url = (isset($estimate['attachment_id']) && $estimate['attachment_id']!='')?wp_get_attachment_url($estimate['attachment_id']):'';
			?>
			<form id="frm-edit-estimate" method="POST" action="" >
				<input type="hidden" id="estimate_client" name="estimate_client" value="<?=$client?>">
				<input type="hidden" id="estimate_contractor" name="estimate_contractor" value="<?=$contractor?>">
				<?php wp_nonce_field( 'edit-estimate', 'nonce' ); ?>
				<div id="edit-estimate-response"></div>
				<div class="row gx-3">
					<div class="col mb-3">
						Ngày gửi đề bài yêu cầu
						<input class="form-control" type="date" value="<?php echo (isset($estimate['required'])&&$estimate['required']!='')?esc_html(date('Y-m-d', strtotime($estimate['required']))):''; ?>" name="estimate_required" id="estimate_required">
					</div>
					<div class="col mb-3">
						Ngày nhận dự toán nhà thầu
						<input class="form-control" type="date" value="<?php echo (isset($estimate['received'])&&$estimate['received']!='')?esc_html(date('Y-m-d', strtotime($estimate['received']))):''; ?>" name="estimate_received" id="estimate_received">
					</div>
					<div class="col mb-3">
						Ngày nhận dự toán khách hàng
						<input class="form-control" type="date" value="<?php echo (isset($estimate['completed'])&&$estimate['completed']!='')?esc_html(date('Y-m-d', strtotime($estimate['completed']))):''; ?>" name="estimate_completed" id="estimate_completed">
					</div>
				</div>
				<div class="row gx-3">
					<div class="col mb-3">
						<input type="text" id="estimate_value" name="estimate_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($estimate['value']); ?>">
					</div>
					<div class="col mb-3">
						<input type="text" id="estimate_unit" name="estimate_unit" placeholder="Ghi chú" class="form-control" value="<?php echo esc_attr($estimate['unit']); ?>">
					</div>
				</div>
				
				<div class="row gx-3">
					<div class="col mb-3">
						<input type="text" id="estimate_zalo" name="estimate_zalo" placeholder="Link nhóm zalo" class="form-control" value="<?php echo esc_attr($estimate['zalo']); ?>">
					</div>
					<div class="col mb-3">
						<input type="text" id="estimate_info" name="estimate_info" placeholder="Thông tin nhà thầu" class="form-control" value="<?php echo esc_attr($estimate['info']); ?>">
					</div>
					
				</div>
				<div class="mb-3">
					<input type="text" id="estimate_link" name="estimate_link" placeholder="Link dự toán" class="form-control" value="<?php echo esc_attr($estimate['link']); ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dự toán</div>
					<div class="d-flex justify-content-between p-2 border rounded-2">
						<div class="attachment-uploaded">
							<input type="hidden" id="estimate_attachment_id" name="estimate_attachment_id" value="<?=esc_attr((isset($estimate['attachment_id']))?$estimate['attachment_id']:'')?>">
							<?php if($attachment_url) { ?>
							<div class="input-group input-group-sm">
								<div class="form-control overflow-hidden"><?=esc_html(basename($attachment_url))?></div>
								<button class="btn btn-warning" id="estimate_remove_attachment">Xóa file</button>
							</div>
							<?php } ?>
						</div>
						<label class="d-block" for="estimate_attachment">
							<div class="input-group input-group-sm">
								<div class="form-control overflow-hidden">Chọn file dự toán cần tải lên</div>
								<button class="btn btn-primary">Bấm tải lên</button>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<input type="file" id="estimate_attachment" name="estimate_attachment" accept=".xls,.xlsx" class="form-control">
							</div>
						</label>
					</div>
				</div>
				<div class="mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="estimate_quote" id="estimate_quote" <?php checked( (isset($estimate['quote']) && $estimate['quote']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="estimate_quote">Đã gửi dự toán khách hàng?</label>
					</div>
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
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
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

	public static function display_contractor($contractor_id, $client) {
		$default_estimate_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
		$default_estimate = [
			'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
			'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
			'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
		];

		$default_link = fw_get_db_post_option($contractor_id, 'estimate_default_link');

		$estimates = get_post_meta($contractor_id, '_estimates', true);
		$estimate = isset($estimates[$client->term_id])?$estimates[$client->term_id]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'default_link'=>'',  'link'=>'', 'attachment_id'=>''];
		
		if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
		if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
		if(empty($estimate['zalo'])) $estimate['zalo'] = $default_estimate['zalo'];

		$phone_number = get_post_meta($contractor_id, '_phone_number', true);
		// $external_url = get_post_meta($contractor_id, '_external_url', true);
		// $external_url = ($external_url!='')?esc_url($external_url):'#';

		$cats = get_the_terms( $contractor_id, 'contractor_cat' );
		?>
		<div class="col-lg-3 col-md-6 estimate-item mb-4">
			<div class="estimate estimate-<?=$contractor_id?> border border-dark h-100 bg-black">
				<div class="row g-0 estimate-progress text-center text-yellow">
					<div class="col estimate-required">
					<?php
					if(isset($estimate['required']) && $estimate['required']!='') {
						?>
						<div title="Ngày gửi đề bài yêu cầu">
							<?php echo esc_html(date('d/m/Y', strtotime($estimate['required']))); ?>
						</div>
						<?php
					}
					?>
					</div>
					<div class="col estimate-received">
						<?php
						if(isset($estimate['received']) && $estimate['received']!='') {
							?>
							<div title="Ngày nhận dự toán nhà thầu">
								<?php echo esc_html(date('d/m/Y', strtotime($estimate['received']))); ?>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col estimate-completed">
						<?php
						if(isset($estimate['completed']) && $estimate['completed']!='') {
							?>
							<div title="Ngày nhận dự toán khách hàng">
								<?php echo esc_html(date('d/m/Y', strtotime($estimate['completed']))); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<div class="contractor-thumbnail position-relative">
					<div class="attachment-download position-absolute top-0 start-0 p-1 z-3">
					<?php if(client_can_view()) {
						if(isset($estimate['attachment_id']) && $estimate['attachment_id']!='') {
							$attachment_url = wp_get_attachment_url($estimate['attachment_id']);
							if($attachment_url) {
							?>
							<a class="btn-shadow btn btn-sm btn-primary fw-bold me-2" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
							<?php
							}
						}
					} ?>
					</div>
					<a class="thumbnail-image position-absolute w-100 h-100 start-0 top-0" href="#"><?php echo get_the_post_thumbnail( $contractor_id, 'full' ); ?></a>
					<div class="position-absolute bottom-0 end-0 m-1 d-flex">
						<div class="estimate-quote">
						<?php
						if(isset($estimate['quote']) && $estimate['quote']=='yes') {
							?>
							<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Đã gửi dự toán khách hàng"><span class="dashicons dashicons-yes"></span></span>
							<?php
						}
						?>
						</div>
						<?php if(has_role('administrator')) { ?>
						<a href="<?php echo get_edit_post_link( $contractor_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
						<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-estimate" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
						<?php } ?>
					</div>
					<div class="zalo-link position-absolute top-0 end-0 p-2">
					<?php if($estimate['zalo']) { ?>
						<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($estimate['zalo'])?>" target="_blank">Zalo</a>
					<?php } ?>
					</div>
					<?php if($default_link) { ?>
						<a class="btn btn-sm btn-primary btn-shadow fw-bold position-absolute start-0 bottom-0 m-1 z-3" href="<?=esc_url($default_link)?>" target="_blank">Gốc</a>
					<?php } ?>
				</div>
				<div class="contractor-info text-center px-1">
					<div class="contractor-title pt-3 mb-1 fs-5">
						<a class="d-block" href="#" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
						<div class="fs-6 text-yellow d-flex flex-wrap justify-content-center">
							<?php
							if($cats) {
								foreach ($cats as $k => $cat) {
									echo '<div>'.(($k>0)?', ':' ').esc_html($cat->name).'</div>';
								}
							}
							?>
						</div>
					</div>
					<?php if($estimate['value']!='') { ?>
					<div class="contractor-value mb-1">
						<span>Tổng giá trị: </span>
						<span class="text-red fw-bold"><?php echo  esc_html($estimate['value']); ?></span>
					</div>
					<?php } ?>
					<?php if($estimate['unit']) { ?>
					<div class="contractor-unit mb-1">
						<div class="text-red"><?php echo esc_html($estimate['unit']); ?></div>
					</div>
					<?php } ?>
					<div class="d-flex flex-wrap justify-content-center contractor-links mb-3">
						<?php
						if(client_can_view()) {
							if(empty($estimate['info']) && $phone_number) {
							?>
							<a class="btn btn-sm btn-danger my-1 mx-2" href="tel:<?=esc_attr($phone_number)?>"><?=esc_html($phone_number)?></a>
							<?php
							} else if(!empty($estimate['info'])) {
								?>
								<span class="bg-danger px-2 py-1 rounded-1 my-1 mx-2"><?=esc_html($estimate['info'])?></span>
								<?php
							}
						}

						if(isset($estimate['link']) && $estimate['link']!='') {
							?>
							<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($estimate['link'])?>" target="_blank">Xem chi tiết</a>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
