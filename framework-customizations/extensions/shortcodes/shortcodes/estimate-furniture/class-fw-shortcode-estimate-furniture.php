<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Estimate_Furniture extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_estimate_furniture_form', [$this, 'ajax_get_edit_estimate_furniture_form']);
      add_action( 'wp_ajax_update_estimate_furniture', [$this, 'ajax_update_estimate_furniture']);
      add_action( 'wp_ajax_get_estimate_furniture_info', [$this, 'ajax_get_estimate_furniture_info']);
      add_action( 'wp_ajax_estimate_contractor_furniture_hide', [$this, 'ajax_estimate_contractor_furniture_hide']);
	}

	public function ajax_estimate_contractor_furniture_hide() {
		global $current_client;
		$contractor_id = isset($_POST['contractor']) ? absint($_POST['contractor']) : 0;
		$response = false;
		if(current_user_can('estimate_furniture_edit') && $current_client && $contractor_id && check_ajax_referer( 'global', 'nonce', false )) {
			$contractor_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'contractor_furniture_hide', []);
			$contractor_hide[] = $contractor_id;
			fw_set_db_term_option($current_client->term_id, 'passwords', 'contractor_furniture_hide', $contractor_hide);
			$response = true;
		}
		wp_send_json($response);
	}

	public function ajax_get_estimate_furniture_info() {
		global $current_client;
		
		$contractor_id = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		$response = [
			//'require_content' => '',
			'info' => '',
			'zalo' => '',
			'attachment' => '',
			'required' => '',
			'received' => '',
			'completed' => '',
			'sent' => '',
			'quote' => '',
		];
		
		if($current_client && $contractor_id) {
			$default_estimate_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
			$default_estimate = [
				'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
				'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
				'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
				'attachment_id' => (!empty($default_estimate_attachment))?$default_estimate_attachment['attachment_id']:'',
			];

			$estimates = get_post_meta($contractor_id, '_estimate_furniture', true);
			if(empty($estimates)) $estimates = [];
			
			$estimate = isset($estimates[$current_client->term_id])?$estimates[$current_client->term_id]:['required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'link'=>'', 'draw_id'=>'', 'slideshow_id'=>'', 'attachment_id'=>'', 'quote'=>''];

			if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
			if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
			if(empty($estimate['zalo'])) $estimate['zalo'] = $default_estimate['zalo'];
			if(empty($estimate['attachment_id'])) $estimate['attachment_id'] = $default_estimate['attachment_id'];

			$phone_number = get_post_meta($contractor_id, '_phone_number', true);
			$phone_number_label = fw_get_db_post_option($contractor_id, 'phone_number_label', '');
			//$external_url = get_post_meta($contractor_id, '_external_url', true);
			//$external_url = ($external_url!='')?esc_url($external_url):'#';

			$cats = get_the_terms( $contractor_id, 'contractor_cat' );

			$response['zalo'] = ($estimate['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($estimate['zalo']).'" target="_blank">Zalo</a>':'';
			$response['attachment'] = ($estimate['attachment_id'])?'<a class="btn-shadow btn btn-sm btn-primary" href="'.esc_url(wp_get_attachment_url($estimate['attachment_id'])).'" target="_blank">Tải</a>':'';

			$response['required'] = (isset($estimate['required']) && $estimate['required']!='')?'<div class="bg-danger" title="Ngày gửi yêu cầu">'.esc_html(date('d/m', strtotime($estimate['required']))).'</div>':'';
			$response['received'] = (isset($estimate['received']) && $estimate['received']!='')?'<div class="bg-danger" title="Ngày nhận dự toán nhà thầu">'.esc_html(date('d/m', strtotime($estimate['received']))).'</div>':'';
			$response['completed'] = (isset($estimate['completed']) && $estimate['completed']!='')?'<div class="bg-danger" title="Ngày làm xong dự toán">'.esc_html(date('d/m', strtotime($estimate['completed']))).'</div>':'';
			$response['sent'] = (isset($estimate['sent']) && $estimate['sent']!='')?'<div class="bg-danger" title="Ngày gửi cho khách">'.esc_html(date('d/m', strtotime($estimate['sent']))).'</div>':'';

			$response['quote'] = (isset($estimate['quote']) && $estimate['quote']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Dự toán được khách hàng chọn"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
		?>
			<div class="contractor-title pt-3 mb-1">
				<a class="d-block fs-5" href="#" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
				<?php
				if($phone_number) {
				?>
				<div class="d-flex flex-wrap justify-content-center contractor-links">
					<span class="bg-primary px-2 py-1 rounded-1 my-1 mx-2 fs-sm"><?=esc_html($phone_number_label.' '.$phone_number)?></span>
				</div>
				<?php
				}
				?>
				<div class="fs-6 text-yellow">
					<?php
					if($cats) {
						foreach ($cats as $key => $cat) {
							$page = absint(get_term_meta($cat->term_id, '_page', true));
							if($page) {
								echo '<a class="text-yellow" href="'.esc_url(get_permalink( $page )).'" target="_blank">'.(($key>0)?', ':' ').esc_html($cat->name).'</a>';
							} else {
								echo '<span>'.(($key>0)?', ':' ').esc_html($cat->name).'</span>';
							} 
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
			<div class="d-flex flex-wrap justify-content-center contractor-links">
				<?php
			
				if(!empty($estimate['info'])) {
					?>
					<span class="bg-danger px-2 py-1 rounded-1 my-1 mx-2 fs-sm"><?=esc_html($estimate['info'])?></span>
					<?php
				}

				if($estimate['link']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($estimate['link'])?>" target="_blank">Xem chi tiết</a>
					<?php
				}

				if(isset($estimate['draw_id']) && $estimate['draw_id']) {
					?>
					<a class="btn btn-sm btn-warning my-1 mx-2" href="<?=esc_url(wp_get_attachment_url($estimate['draw_id']))?>" target="_blank">Bản vẽ</a>
					<?php
				}

				if(isset($estimate['slideshow_id']) && $estimate['slideshow_id']) {
					?>
					<a class="btn btn-sm btn-info my-1 mx-2" href="<?=esc_url(wp_get_attachment_url($estimate['slideshow_id']))?>" target="_blank">Bản thuyết trình</a>
					<?php
				}
				?>
			</div>
			
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_estimate_furniture() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can( 'estimate_furniture_edit' ) && check_ajax_referer( 'edit-estimate-furniture', 'nonce', false )) {
			$estimate_furniture_client = isset($_POST['estimate_furniture_client'])?absint($_POST['estimate_furniture_client']):0;
			$estimate_furniture_contractor = isset($_POST['estimate_furniture_contractor'])?absint($_POST['estimate_furniture_contractor']):0;
			$estimate_furniture_value = isset($_POST['estimate_furniture_value'])?sanitize_text_field($_POST['estimate_furniture_value']):'';
			$estimate_furniture_unit = isset($_POST['estimate_furniture_unit'])?sanitize_text_field($_POST['estimate_furniture_unit']):'';
			$estimate_furniture_zalo = isset($_POST['estimate_furniture_zalo'])?sanitize_text_field($_POST['estimate_furniture_zalo']):'';
			$estimate_furniture_info = isset($_POST['estimate_furniture_info'])?sanitize_text_field($_POST['estimate_furniture_info']):'';
			$estimate_furniture_link = isset($_POST['estimate_furniture_link'])?sanitize_text_field($_POST['estimate_furniture_link']):'';
			
			$estimate_furniture_draw_id = isset($_POST['estimate_furniture_draw_id'])?absint($_POST['estimate_furniture_draw_id']):0;
			$estimate_furniture_draw = isset($_FILES['estimate_furniture_draw']) ? $_FILES['estimate_furniture_draw'] : null;

			$estimate_furniture_slideshow_id = isset($_POST['estimate_furniture_slideshow_id'])?absint($_POST['estimate_furniture_slideshow_id']):0;
			$estimate_furniture_slideshow = isset($_FILES['estimate_furniture_slideshow']) ? $_FILES['estimate_furniture_slideshow'] : null;

			$estimate_furniture_attachment_id = isset($_POST['estimate_furniture_attachment_id'])?absint($_POST['estimate_furniture_attachment_id']):0;
			$estimate_furniture_attachment = isset($_FILES['estimate_furniture_attachment']) ? $_FILES['estimate_furniture_attachment'] : null;

			//$estimate_furniture_require_content = isset($_POST['estimate_furniture_require_content']) ? sanitize_textarea_field($_POST['estimate_furniture_require_content']) : '';
			$estimate_furniture_required = isset($_POST['estimate_furniture_required']) ? $_POST['estimate_furniture_required'] : '';
			$estimate_furniture_received = isset($_POST['estimate_furniture_received']) ? $_POST['estimate_furniture_received'] : '';
			$estimate_furniture_completed = isset($_POST['estimate_furniture_completed']) ? $_POST['estimate_furniture_completed'] : '';
			$estimate_furniture_sent = isset($_POST['estimate_furniture_sent']) ? $_POST['estimate_furniture_sent'] : '';
			$estimate_furniture_quote = isset($_POST['estimate_furniture_quote']) ? $_POST['estimate_furniture_quote'] : '';

			//debug_log($_POST);

			if($estimate_furniture_client && $estimate_furniture_contractor) {
				$estimates = get_post_meta($estimate_furniture_contractor, '_estimate_furniture', true);
				if(empty($estimates)) $estimates = [];
				$estimate = isset($estimates[$estimate_furniture_client])?$estimates[$estimate_furniture_client]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'link'=>'', 'draw_id'=>'', 'slideshow_id'=>'', 'attachment_id'=>'', 'quote'=>''];

				$new_estimate = [
					'required' => $estimate_furniture_required,
					'received' => $estimate_furniture_received,
					'completed' => $estimate_furniture_completed,
					'sent' => $estimate_furniture_sent,
					'value' => $estimate_furniture_value,
					'unit' => $estimate_furniture_unit,
					'zalo' => $estimate_furniture_zalo,
					'info' => $estimate_furniture_info,
					'link' => $estimate_furniture_link,
					'draw_id' => ($estimate_furniture_draw_id!=0)?$estimate_furniture_draw_id:'',
					'slideshow_id' => ($estimate_furniture_slideshow_id!=0)?$estimate_furniture_slideshow_id:'',
					'attachment_id' => ($estimate_furniture_attachment_id!=0)?$estimate_furniture_attachment_id:'',
					'quote' => $estimate_furniture_quote,
				];

				// tải lên file dự toán
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					require_once(ABSPATH . "wp-admin" . '/includes/file.php');
					require_once(ABSPATH . "wp-admin" . '/includes/media.php');
				}

				$estimate_furniture_draw_upload = media_handle_upload( 'estimate_furniture_draw', $estimate_furniture_contractor );
				if ($estimate_furniture_draw['error']==0 && !($estimate_furniture_draw_upload instanceof \WP_Error) ) {
					$new_estimate['draw_id'] = $estimate_furniture_draw_upload;
					if($estimate_furniture_draw_id) wp_delete_attachment($estimate_furniture_draw_id, true);
				}
				if($new_estimate['draw_id']=='' || $new_estimate['draw_id']==0) {
					if(isset($estimate['draw_id']) && $estimate['draw_id']) wp_delete_attachment($estimate['draw_id'], true);
				}

				$estimate_furniture_slideshow_upload = media_handle_upload( 'estimate_furniture_slideshow', $estimate_furniture_contractor );
				if ($estimate_furniture_slideshow['error']==0 && !($estimate_furniture_slideshow_upload instanceof \WP_Error)) {
					$new_estimate['slideshow_id'] = $estimate_furniture_slideshow_upload;
					if($estimate_furniture_slideshow_id) wp_delete_attachment($estimate_furniture_slideshow_id, true);
				}
				if($new_estimate['slideshow_id']=='' || $new_estimate['slideshow_id']==0) {
					if(isset($estimate['slideshow_id']) && $estimate['slideshow_id']) wp_delete_attachment($estimate['slideshow_id'], true);
				}

				$estimate_furniture_attachment_upload = media_handle_upload( 'estimate_furniture_attachment', $estimate_furniture_contractor );
				if ($estimate_furniture_attachment['error']==0 && !($estimate_furniture_attachment_upload instanceof \WP_Error)) {
					$new_estimate['attachment_id'] = $estimate_furniture_attachment_upload;
					if($estimate_furniture_attachment_id) wp_delete_attachment($estimate_furniture_attachment_id, true);
				}
				if($new_estimate['attachment_id']=='' || $new_estimate['attachment_id']==0) {
					if(isset($estimate['attachment_id']) && $estimate['attachment_id']) wp_delete_attachment($estimate['attachment_id'], true);
				}

				$estimates[$estimate_furniture_client] = $new_estimate;

				update_post_meta( $estimate_furniture_contractor, '_estimate_furniture', $estimates );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_estimate_furniture_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$contractor = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		if($client && $contractor) {
			$estimates = get_post_meta($contractor, '_estimate_furniture', true);
			if(empty($estimates)) $estimates = [];
			
			$default = [
				//'require_content'=>'',
				'required'=>'',
				'received'=>'',
				'completed'=>'',
				'value'=>'',
				'unit'=>'',
				'zalo'=>'',
				'link'=>'',
				'draw_id'=>'',
				'slideshow_id'=>'',
				'attachment_id'=>'',
				'quote'=>''
			];
			$estimate = isset($estimates[$client])?$estimates[$client]:$default;

			$draw_url = (isset($estimate['draw_id']) && $estimate['draw_id']!='')?wp_get_attachment_url($estimate['draw_id']):'';
			$slideshow_url = (isset($estimate['slideshow_id']) && $estimate['slideshow_id']!='')?wp_get_attachment_url($estimate['slideshow_id']):'';
			$attachment_url = (isset($estimate['attachment_id']) && $estimate['attachment_id']!='')?wp_get_attachment_url($estimate['attachment_id']):'';
			?>
			<form id="frm-edit-estimate-furniture" method="POST" action="" >
				<input type="hidden" id="estimate_furniture_client" name="estimate_furniture_client" value="<?=$client?>">
				<input type="hidden" id="estimate_furniture_contractor" name="estimate_furniture_contractor" value="<?=$contractor?>">
				<?php wp_nonce_field( 'edit-estimate-furniture', 'nonce' ); ?>
				<div id="edit-estimate-furniture-response"></div>
				<div class="mb-3<?php echo (!current_user_can('edit_contractors'))?' hidden':''; ?>">
					Gửi yêu cầu
					<input class="form-control" type="date" value="<?php echo (isset($estimate['required'])&&$estimate['required']!='')?esc_html(date('Y-m-d', strtotime($estimate['required']))):''; ?>" name="estimate_furniture_required" id="estimate_furniture_required">
				</div>
				<div class="mb-3">
					Dự toán nhà thầu
					<input class="form-control" type="date" value="<?php echo (isset($estimate['received'])&&$estimate['received']!='')?esc_html(date('Y-m-d', strtotime($estimate['received']))):''; ?>" name="estimate_furniture_received" id="estimate_furniture_received">
				</div>
				<div class="mb-3">
					Xong dự toán
					<input class="form-control" type="date" value="<?php echo (isset($estimate['completed'])&&$estimate['completed']!='')?esc_html(date('Y-m-d', strtotime($estimate['completed']))):''; ?>" name="estimate_furniture_completed" id="estimate_furniture_completed">
				</div>                                  
				<div class="mb-3">
					Ngày gửi khách
					<input class="form-control" type="date" value="<?php echo (isset($estimate['sent'])&&$estimate['sent']!='')?esc_html(date('Y-m-d', strtotime($estimate['sent']))):''; ?>" name="estimate_furniture_sent" id="estimate_furniture_sent">
				</div>
				<div class="col mb-3">
					<input type="text" id="estimate_furniture_value" name="estimate_furniture_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($estimate['value']); ?>">
				</div>
				<div class="col mb-3">
					<input type="text" id="estimate_furniture_unit" name="estimate_furniture_unit" placeholder="Ghi chú" class="form-control" value="<?php echo esc_attr($estimate['unit']); ?>">
				</div>
				<div class="col mb-3">
					<input type="text" id="estimate_furniture_zalo" name="estimate_furniture_zalo" placeholder="Link nhóm zalo" class="form-control" value="<?php echo esc_attr($estimate['zalo']); ?>">
				</div>
				<div class="col mb-3">
					<input type="text" id="estimate_furniture_info" name="estimate_furniture_info" placeholder="Thông tin nhà thầu" class="form-control" value="<?php echo esc_attr($estimate['info']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="estimate_furniture_link" name="estimate_furniture_link" placeholder="Link dự toán" class="form-control" value="<?php echo esc_attr($estimate['link']); ?>">
				</div>

				<!-- <div class="mb-3">
					<div class="form-label mb-1">File bản vẽ</div>
					<div class="row row-cols-2 g-0 p-2 border rounded-2">
						<div class="col attachment-uploaded">
							<input type="hidden" id="estimate_furniture_draw_id" name="estimate_furniture_draw_id" value="<?=esc_attr((isset($estimate['draw_id']))?$estimate['draw_id']:'')?>">
							<?php if($draw_url) { ?>
							<div class="input-group input-group-sm">
								<div class="form-control text-truncate"><?=esc_html(basename($draw_url))?></div>
								<button class="btn btn-warning" id="estimate_furniture_remove_draw" type="button">Xóa file</button>
							</div>
							<?php } ?>
						</div>
						<label class="col d-block ps-5" for="estimate_furniture_draw">
							<div class="input-group input-group-sm">
								<div class="form-control text-nowrap">Chọn file cần tải lên</div>
								<span class="btn btn-primary">Bấm tải lên</span>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<input type="file" id="estimate_furniture_draw" name="estimate_furniture_draw" class="form-control">
							</div>
						</label>
					</div>
				</div>

				<div class="mb-3">
					<div class="form-label mb-1">File thuyết trình</div>
					<div class="row row-cols-2 g-0 p-2 border rounded-2">
						<div class="col attachment-uploaded">
							<input type="hidden" id="estimate_furniture_slideshow_id" name="estimate_furniture_slideshow_id" value="<?=esc_attr((isset($estimate['slideshow_id']))?$estimate['slideshow_id']:'')?>">
							<?php if($slideshow_url) { ?>
							<div class="input-group input-group-sm">
								<div class="form-control text-truncate"><?=esc_html(basename($slideshow_url))?></div>
								<button class="btn btn-warning" id="estimate_furniture_remove_slideshow" type="button">Xóa file</button>
							</div>
							<?php } ?>
						</div>
						<label class="col d-block ps-5" for="estimate_furniture_slideshow">
							<div class="input-group input-group-sm">
								<div class="form-control text-nowrap">Chọn file cần tải lên</div>
								<span class="btn btn-primary">Bấm tải lên</span>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<input type="file" id="estimate_furniture_slideshow" name="estimate_furniture_slideshow" class="form-control">
							</div>
						</label>
					</div>
				</div> -->

				<div class="mb-3">
					<div class="form-label mb-1">File dữ liệu</div>
					<div class="row row-cols-2 g-0 p-2 border rounded-2">
						<div class="col attachment-uploaded">
							<input type="hidden" id="estimate_furniture_attachment_id" name="estimate_furniture_attachment_id" value="<?=esc_attr((isset($estimate['attachment_id']))?$estimate['attachment_id']:'')?>">
							<?php if($attachment_url) { ?>
							<div class="input-group input-group-sm">
								<div class="form-control text-truncate"><?=esc_html(basename($attachment_url))?></div>
								<button class="btn btn-warning" id="estimate_furniture_remove_attachment" type="button">Xóa file</button>
							</div>
							<?php } ?>
						</div>
						<label class="col d-block ps-5" for="estimate_furniture_attachment">
							<div class="input-group input-group-sm">
								<div class="form-control text-nowrap">Chọn file cần tải lên</div>
								<span class="btn btn-primary">Bấm tải lên</span>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<!-- <input type="file" id="estimate_furniture_attachment" name="estimate_furniture_attachment" accept=".doc,.docx,.xls,.xlsx,.pdf" class="form-control"> -->
								<input type="file" id="estimate_furniture_attachment" name="estimate_furniture_attachment" class="form-control">
							</div>
						</label>
					</div>
				</div>
				<div class="mb-3<?php echo (!current_user_can('edit_contractors'))?' hidden':''; ?>">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="estimate_furniture_quote" id="estimate_furniture_quote" <?php checked( (isset($estimate['quote']) && $estimate['quote']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="estimate_furniture_quote">Được khách hàng lựa chọn?</label>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-estimate-furniture-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-estimate-furniture" tabindex="-1" role="dialog" aria-labelledby="edit-estimate-furniture-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-estimate-furniture-label">Sửa dự toán</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function display_contractor($contractor_id, $client, $progress='', $contractor_hide=[]) {
		$default_estimate_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
		$default_estimate = [
			'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
			'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
			'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
			'attachment_id' => (!empty($default_estimate_attachment))?$default_estimate_attachment['attachment_id']:'',
		];

		$default_link = fw_get_db_post_option($contractor_id, 'estimate_default_link');
		$estimate_content = fw_get_db_post_option($contractor_id, 'estimate_content');

		$estimates = get_post_meta($contractor_id, '_estimate_furniture', true); // dữ liệu dự toán của mỗi chủ đầu tư lưu chung
		$estimate = isset($estimates[$client->term_id])?$estimates[$client->term_id]:[ 'require_content'=>'', 'required'=>'', 'received'=>'', 'completed'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'default_link'=>'',  'link'=>'', 'draw_id'=>'', 'slideshow_id'=>'', 'attachment_id'=>'', 'quote'=>''];
		
		if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
		if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
		if(empty($estimate['zalo'])) $estimate['zalo'] = $default_estimate['zalo'];
		if(empty($estimate['attachment_id'])) $estimate['attachment_id'] = $default_estimate['attachment_id'];

		$phone_number = get_post_meta($contractor_id, '_phone_number', true);
		$phone_number_label = fw_get_db_post_option($contractor_id, 'phone_number_label', '');

		$cats = get_the_terms( $contractor_id, 'contractor_cat' );

		$display = empty($progress) ? true : false;

		if(!$display) {
			$display_none = true;
			if('none'==$progress) {
				$display_none = false;
				if(empty($estimate['required']) && empty($estimate['received']) && empty($estimate['completed']) && empty($estimate['sent']) && empty($estimate['quote'])) {
					$display_none = true;
				}
			}

			$display_required = true;
			if('required'==$progress) {
				$display_required = false;
				if(isset($estimate['required']) && $estimate['required']!='' ) {
					$display_required = true;
				}
			}

			$display_received = true;
			if('received'==$progress) {
				$display_received = false;
				if(isset($estimate['received']) && $estimate['received']!='' ) {
					$display_received = true;
				}
			}

			$display_completed = true;
			if('completed'==$progress) {
				$display_completed = false;
				if(isset($estimate['completed']) && $estimate['completed']!='' ) {
					$display_completed = true;
				}
			}

			$display_sent = true;
			if('sent'==$progress) {
				$display_sent = false;
				if(isset($estimate['sent']) && $estimate['sent']!='' ) {
					$display_sent = true;
				}
			}

			$display_quote = true;
			if('quote'==$progress) {
				$display_quote = false;
				if(isset($estimate['quote']) && $estimate['quote']!='' ) {
					$display_quote = true;
				}
			}

		}

		$item_class = '';

		if(!$display) {
			if( !($display_none && $display_required && $display_received && $display_completed && $display_sent && $display_quote )) {
				$item_class .= ' hidden';
			}
		}

		if(in_array($contractor_id, $contractor_hide)) {
			$item_class .= ' hide';
		}

		$texture_images = fw_get_db_post_option($contractor_id, 'texture_images');
		$project_images = fw_get_db_post_option($contractor_id, 'project_images');
		?>
		<div class="col-lg-3 col-md-6 estimate-item mb-4<?=$item_class?>">
			<div class="estimate estimate-<?=$contractor_id?> border border-dark h-100 bg-black">
				<div class="row g-0 progressing-bar estimate-progress text-center text-yellow">
					<div class="col estimate-required<?php echo (isset($estimate['required']) && $estimate['required']!='')?' on':''; ?>">
					<?php
					if(isset($estimate['required']) && $estimate['required']!='') {
						?>
						<div class="bg-danger" title="Ngày gửi yêu cầu">
							<?php echo esc_html(date('d/m', strtotime($estimate['required']))); ?>
						</div>
						<?php
					}
					?>
					</div>
					<div class="col estimate-received<?php echo (isset($estimate['received']) && $estimate['received']!='')?' on':''; ?>">
						<?php
						if(isset($estimate['received']) && $estimate['received']!='') {
							?>
							<div class="bg-danger" title="Ngày nhận dự toán nhà thầu">
								<?php echo esc_html(date('d/m', strtotime($estimate['received']))); ?>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col estimate-completed<?php echo (isset($estimate['completed']) && $estimate['completed']!='')?' on':''; ?>">
						<?php
						if(isset($estimate['completed']) && $estimate['completed']!='') {
							?>
							<div class="bg-danger" title="Ngày làm xong dự toán">
								<?php echo esc_html(date('d/m', strtotime($estimate['completed']))); ?>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col estimate-sent<?php echo (isset($estimate['sent']) && $estimate['sent']!='')?' on':''; ?>">
						<?php
						if(isset($estimate['sent']) && $estimate['sent']!='') {
							?>
							<div class="bg-danger" title="Ngày gửi cho khách">
								<?php echo esc_html(date('d/m', strtotime($estimate['sent']))); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<div class="contractor-thumbnail position-relative">
					<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
						<div class="estimate-require-content">
						<?php
						if(isset($estimate_content) && $estimate_content!='') {
							?>
							<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($estimate_content))?>" data-bs-html="true">Đề bài</button>
							<?php
						}
						?>
						</div>
						<div class="attachment-download">
						<?php
						if(isset($estimate['attachment_id']) && $estimate['attachment_id']!='') {
							$attachment_url = wp_get_attachment_url($estimate['attachment_id']);
							if($attachment_url) {
							?>
							<a class="btn-shadow btn btn-sm btn-primary fw-bold me-2" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
							<?php
							}
						}
						?>
						</div>
					</div>
					<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-top border-bottom border-dark<?php echo (!empty($project_images))?' pswp-gallery':''?>">
						<?php if(!empty($project_images)) { ?>
							<?php foreach ($project_images as $key => $value) {
							$src_full = wp_get_attachment_image_src( $value['attachment_id'], 'full' );
							?>
							<a class="<?php echo ($key>0)?'hidden':''; ?>" href="<?=esc_url($src_full[0])?>" data-pswp-width="<?=$src_full[1]?>" data-pswp-height="<?=$src_full[2]?>"><?php echo ($key==0)? get_the_post_thumbnail( $contractor_id, 'full' ):''; ?></a>
							<?php
							}
						} else {
							echo get_the_post_thumbnail( $contractor_id, 'full' );
						} ?>
					</div>
					<div class="position-absolute bottom-0 end-0 m-1 d-flex">
						<div class="estimate-quote<?php echo (isset($estimate['quote']) && $estimate['quote']=='yes')?' on':''; ?>">
							<?php
							if(isset($estimate['quote']) && $estimate['quote']=='yes') {
								?>
								<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Dự toán được khách hàng chọn"><span class="dashicons dashicons-yes"></span></span>
								<?php
							}
							?>
						</div>
						
						<?php if(current_user_can('edit_contractors')) { ?>
						<button class="estimate-contractor-furniture-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><span class="dashicons dashicons-visibility"></span></button>
						
						<a href="<?php echo get_edit_post_link( $contractor_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
						<?php } ?>
						<?php if(current_user_can('estimate_furniture_edit')) { ?>
						<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-estimate-furniture" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
						<?php } ?>
					</div>
					<div class="position-absolute top-0 end-0 p-1 d-flex">
						<div class="zalo-link">
						<?php if($estimate['zalo']) { ?>
							<a class="btn btn-sm btn-shadow fw-bold ms-2" href="<?=esc_url($estimate['zalo'])?>" target="_blank">Zalo</a>
						<?php } ?>
						</div>
					</div>
					<div class="position-absolute start-0 bottom-0 p-1 z-3 d-flex">
						<?php if($default_link) { ?>
						<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_link)?>" target="_blank">Gốc</a>
						<?php } ?>
						<?php if(!empty($texture_images)) { ?>
						<div class="position-relative texture-images pswp-gallery me-2">
							<?php foreach ($texture_images as $key => $value) {
							$src_full = wp_get_attachment_image_src( $value['attachment_id'], 'full' );
							?>
							<a class="btn btn-sm btn-info btn-shadow<?php echo ($key>0)?' hidden':''; ?> text-nowrap" href="<?=esc_url($src_full[0])?>" data-pswp-width="<?=$src_full[1]?>" data-pswp-height="<?=$src_full[2]?>"><?php echo ($key==0)?'Map vật liệu':''; ?></a>
							<?php } ?>
						</div>
						<?php } ?>
					</div>
				</div>
				<div class="contractor-info text-center px-1 pb-3">
					<div class="contractor-title pt-3 mb-1">
						<a class="d-block fs-5" href="#" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
						<?php
					
						if($phone_number) {
						?>
						<div class="d-flex flex-wrap justify-content-center contractor-links">
							<span class="bg-primary px-2 py-1 rounded-1 my-1 mx-2 fs-sm"><?=esc_html($phone_number_label.' '.$phone_number)?></span>
						</div>
						<?php
						}

						?>
						<div class="fs-6 text-yellow d-flex flex-wrap justify-content-center">
							<?php
							if($cats) {
								foreach ($cats as $k => $cat) {
									$page = absint(get_term_meta($cat->term_id, '_page', true));
									if($page) {
										echo '<a class="text-yellow" href="'.esc_url(get_permalink( $page )).'" target="_blank">'.(($k>0)?', ':' ').esc_html($cat->name).'</a>';
									} else {
										echo '<span>'.(($k>0)?', ':' ').esc_html($cat->name).'</span>';
									} 
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
					<div class="d-flex flex-wrap justify-content-center contractor-links">
						<?php

						if(!empty($estimate['info'])) {
							?>
							<span class="bg-danger px-2 py-1 rounded-1 my-1 mx-2 fs-sm"><?=esc_html($estimate['info'])?></span>
							<?php
						}

						if(isset($estimate['link']) && $estimate['link']!='') {
							?>
							<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($estimate['link'])?>" target="_blank">Xem chi tiết</a>
							<?php
						}

						if(isset($estimate['draw_id']) && $estimate['draw_id']) {
							?>
							<a class="btn btn-sm btn-warning my-1 mx-2" href="<?=esc_url(wp_get_attachment_url($estimate['draw_id']))?>" target="_blank">Bản vẽ</a>
							<?php
						}

						if(isset($estimate['slideshow_id']) && $estimate['slideshow_id']) {
							?>
							<a class="btn btn-sm btn-info my-1 mx-2" href="<?=esc_url(wp_get_attachment_url($estimate['slideshow_id']))?>" target="_blank">Bản thuyết trình</a>
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
