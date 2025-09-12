<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Procedure extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_procedure_form', [$this, 'ajax_get_edit_procedure_form']);
      add_action( 'wp_ajax_update_procedure', [$this, 'ajax_update_procedure']);
      add_action( 'wp_ajax_get_procedure_info', [$this, 'ajax_get_procedure_info']);
      add_action( 'wp_ajax_procedure_contractor_hide', [$this, 'ajax_procedure_contractor_hide']);
	}

	public function ajax_procedure_contractor_hide() {
		global $current_client;
		$contractor_id = isset($_POST['contractor']) ? absint($_POST['contractor']) : 0;
		$response = false;
		if(current_user_can('procedure_contractor_edit') && $current_client && $contractor_id && check_ajax_referer( 'global', 'nonce', false )) {
			$contractor_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'procedure_contractor_hide', []);
			$contractor_hide[] = $contractor_id;
			fw_set_db_term_option($current_client->term_id, 'passwords', 'procedure_contractor_hide', $contractor_hide);
			$response = true;
		}
		wp_send_json($response);
	}

	public function ajax_get_procedure_info() {
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
			$default_procedure_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
			$default_procedure = [
				'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
				'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
				'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
				'attachment_id' => (!empty($default_procedure_attachment))?$default_procedure_attachment['attachment_id']:'',
			];

			$procedures = get_post_meta($contractor_id, '_procedures', true);
			if(empty($procedures)) $procedures = [];
			
			$procedure = isset($procedures[$current_client->term_id])?$procedures[$current_client->term_id]:['required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'link'=>'', 'attachment_id'=>'', 'quote'=>''];

			if(empty($procedure['value'])) $procedure['value'] = $default_procedure['value'];
			if(empty($procedure['unit'])) $procedure['unit'] = $default_procedure['unit'];
			if(empty($procedure['zalo'])) $procedure['zalo'] = $default_procedure['zalo'];
			if(empty($procedure['attachment_id'])) $procedure['attachment_id'] = $default_procedure['attachment_id'];

			$phone_number = get_post_meta($contractor_id, '_phone_number', true);
			$phone_number_label = fw_get_db_post_option($contractor_id, 'phone_number_label', '');
			//$external_url = get_post_meta($contractor_id, '_external_url', true);
			//$external_url = ($external_url!='')?esc_url($external_url):'#';

			$cats = get_the_terms( $contractor_id, 'contractor_cat' );

			$response['zalo'] = ($procedure['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold" href="'.esc_url($procedure['zalo']).'" target="_blank">Zalo</a>':'';
			$response['attachment'] = ($procedure['attachment_id'])?'<a class="btn-shadow btn btn-sm btn-primary" href="'.esc_url(wp_get_attachment_url($procedure['attachment_id'])).'" target="_blank">Tải</a>':'';

			$response['required'] = (isset($procedure['required']) && $procedure['required']!='')?'<div class="bg-danger" title="Ngày gửi yêu cầu">'.esc_html(date('d/m', strtotime($procedure['required']))).'</div>':'';
			$response['received'] = (isset($procedure['received']) && $procedure['received']!='')?'<div class="bg-danger" title="Ngày nhận dự toán nhà thầu">'.esc_html(date('d/m', strtotime($procedure['received']))).'</div>':'';
			$response['completed'] = (isset($procedure['completed']) && $procedure['completed']!='')?'<div class="bg-danger" title="Ngày làm xong dự toán">'.esc_html(date('d/m', strtotime($procedure['completed']))).'</div>':'';
			$response['sent'] = (isset($procedure['sent']) && $procedure['sent']!='')?'<div class="bg-danger" title="Ngày gửi cho khách">'.esc_html(date('d/m', strtotime($procedure['sent']))).'</div>':'';

			$response['quote'] = (isset($procedure['quote']) && $procedure['quote']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Dự toán được khách hàng chọn"><span class="dashicons dashicons-yes"></span></span>':'';

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
			<?php if($procedure['value']) { ?>
			<div class="contractor-value mb-1">
				<span>Tổng giá trị:</span>
				<span class="text-red fw-bold"><?php echo  esc_html($procedure['value']); ?></span>
				
			</div>
			<?php } ?>
			<?php if($procedure['unit']) { ?>
			<div class="contractor-unit mb-1">
				<div class="text-red"><?php echo esc_html($procedure['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center contractor-links">
				<?php
			
				if(!empty($procedure['info'])) {
					?>
					<span class="bg-danger px-2 py-1 rounded-1 my-1 mx-2 fs-sm"><?=esc_html($procedure['info'])?></span>
					<?php
				}

				if($procedure['link']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($procedure['link'])?>" target="_blank">Xem chi tiết</a>
					<?php
				}

				?>
			</div>
			
		<?php
			$response['info'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_procedure() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can( 'procedure_contractor_edit' ) && check_ajax_referer( 'edit-procedure', 'nonce', false )) {
			$procedure_client = isset($_POST['procedure_client'])?absint($_POST['procedure_client']):0;
			$procedure_contractor = isset($_POST['procedure_contractor'])?absint($_POST['procedure_contractor']):0;
			$procedure_attachment_id = isset($_POST['procedure_attachment_id'])?absint($_POST['procedure_attachment_id']):0;
			$procedure_value = isset($_POST['procedure_value'])?sanitize_text_field($_POST['procedure_value']):'';
			$procedure_unit = isset($_POST['procedure_unit'])?sanitize_text_field($_POST['procedure_unit']):'';
			$procedure_zalo = isset($_POST['procedure_zalo'])?sanitize_text_field($_POST['procedure_zalo']):'';
			$procedure_info = isset($_POST['procedure_info'])?sanitize_text_field($_POST['procedure_info']):'';
			$procedure_link = isset($_POST['procedure_link'])?sanitize_text_field($_POST['procedure_link']):'';
			$procedure_attachment = isset($_FILES['procedure_attachment']) ? $_FILES['procedure_attachment'] : null;

			//$procedure_require_content = isset($_POST['procedure_require_content']) ? sanitize_textarea_field($_POST['procedure_require_content']) : '';
			$procedure_required = isset($_POST['procedure_required']) ? $_POST['procedure_required'] : '';
			$procedure_received = isset($_POST['procedure_received']) ? $_POST['procedure_received'] : '';
			$procedure_completed = isset($_POST['procedure_completed']) ? $_POST['procedure_completed'] : '';
			$procedure_sent = isset($_POST['procedure_sent']) ? $_POST['procedure_sent'] : '';
			$procedure_quote = isset($_POST['procedure_quote']) ? $_POST['procedure_quote'] : '';

			//debug_log($_POST);

			if($procedure_client && $procedure_contractor) {
				$procedures = get_post_meta($procedure_contractor, '_procedures', true);
				if(empty($procedures)) $procedures = [];
				$procedure = isset($procedures[$procedure_client])?$procedures[$procedure_client]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'link'=>'', 'attachment_id'=>''];

				$new_procedure = [
					//'require_content' => $procedure_require_content,
					'required' => $procedure_required,
					'received' => $procedure_received,
					'completed' => $procedure_completed,
					'sent' => $procedure_sent,
					'value' => $procedure_value,
					'unit' => $procedure_unit,
					'zalo' => $procedure_zalo,
					'info' => $procedure_info,
					'link' => $procedure_link,
					'attachment_id' => ($procedure_attachment_id!=0)?$procedure_attachment_id:'',
					'quote' => $procedure_quote,
				];

				// tải lên file dự toán
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					require_once(ABSPATH . "wp-admin" . '/includes/file.php');
					require_once(ABSPATH . "wp-admin" . '/includes/media.php');
				}

				$procedure_attachment_upload = media_handle_upload( 'procedure_attachment', $procedure_contractor );

				if ($procedure_attachment['error']==0 && $procedure_attachment_upload && ! is_array( $procedure_attachment_upload ) ) {
					$new_procedure['attachment_id'] = $procedure_attachment_upload;
					if($procedure_attachment_id) wp_delete_attachment($procedure_attachment_id, true);
				}

				if($new_procedure['attachment_id']=='' || $new_procedure['attachment_id']==0) {
					if(isset($procedure['attachment_id']) && $procedure['attachment_id']) wp_delete_attachment($procedure['attachment_id'], true);
				}

				$procedures[$procedure_client] = $new_procedure;

				update_post_meta( $procedure_contractor, '_procedures', $procedures );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_procedure_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$contractor = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		if($client && $contractor) {
			$procedures = get_post_meta($contractor, '_procedures', true);
			if(empty($procedures)) $procedures = [];
			
			$default = [
				//'require_content'=>'',
				'required'=>'',
				'received'=>'',
				'completed'=>'',
				'value'=>'',
				'unit'=>'',
				'zalo'=>'',
				'info'=>'',
				'link'=>'',
				'attachment_id'=>'',
				'quote'=>''
			];
			$procedure = isset($procedures[$client])?$procedures[$client]:$default;

			$attachment_url = (isset($procedure['attachment_id']) && $procedure['attachment_id']!='')?wp_get_attachment_url($procedure['attachment_id']):'';
			?>
			<form id="frm-edit-procedure" method="POST" action="" >
				<input type="hidden" id="procedure_client" name="procedure_client" value="<?=$client?>">
				<input type="hidden" id="procedure_contractor" name="procedure_contractor" value="<?=$contractor?>">
				<?php wp_nonce_field( 'edit-procedure', 'nonce' ); ?>
				<div id="edit-procedure-response"></div>
				<div class="mb-3<?php echo (!current_user_can('edit_contractors'))?' hidden':''; ?>">
					Gửi yêu cầu
					<input class="form-control" type="date" value="<?php echo (isset($procedure['required'])&&$procedure['required']!='')?esc_html(date('Y-m-d', strtotime($procedure['required']))):''; ?>" name="procedure_required" id="procedure_required">
				</div>
				<div class="mb-3">
					Dự toán nhà thầu
					<input class="form-control" type="date" value="<?php echo (isset($procedure['received'])&&$procedure['received']!='')?esc_html(date('Y-m-d', strtotime($procedure['received']))):''; ?>" name="procedure_received" id="procedure_received">
				</div>
				<div class="mb-3">
					Xong dự toán
					<input class="form-control" type="date" value="<?php echo (isset($procedure['completed'])&&$procedure['completed']!='')?esc_html(date('Y-m-d', strtotime($procedure['completed']))):''; ?>" name="procedure_completed" id="procedure_completed">
				</div>                                  
				<div class="mb-3">
					Ngày gửi khách
					<input class="form-control" type="date" value="<?php echo (isset($procedure['sent'])&&$procedure['sent']!='')?esc_html(date('Y-m-d', strtotime($procedure['sent']))):''; ?>" name="procedure_sent" id="procedure_sent">
				</div>
				<div class="col mb-3">
					<input type="text" id="procedure_value" name="procedure_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($procedure['value']); ?>">
				</div>
				<div class="col mb-3">
					<input type="text" id="procedure_unit" name="procedure_unit" placeholder="Ghi chú" class="form-control" value="<?php echo esc_attr($procedure['unit']); ?>">
				</div>
				<div class="col mb-3">
					<input type="text" id="procedure_zalo" name="procedure_zalo" placeholder="Link nhóm zalo" class="form-control" value="<?php echo esc_attr($procedure['zalo']); ?>">
				</div>
				<div class="col mb-3">
					<input type="text" id="procedure_info" name="procedure_info" placeholder="Thông tin nhà thầu" class="form-control" value="<?php echo esc_attr($procedure['info']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="procedure_link" name="procedure_link" placeholder="Link dự toán" class="form-control" value="<?php echo esc_attr($procedure['link']); ?>">
				</div>
				<div class="mb-3">
					<div class="form-label mb-1">File dự toán</div>
					<div class="row row-cols-2 g-0 p-2 border rounded-2">
						<div class="col attachment-uploaded">
							<input type="hidden" id="procedure_attachment_id" name="procedure_attachment_id" value="<?=esc_attr((isset($procedure['attachment_id']))?$procedure['attachment_id']:'')?>">
							<?php if($attachment_url) { ?>
							<div class="input-group input-group-sm">
								<div class="form-control text-truncate"><?=esc_html(basename($attachment_url))?></div>
								<button class="btn btn-warning" id="procedure_remove_attachment" type="button">Xóa file</button>
							</div>
							<?php } ?>
						</div>
						<label class="col d-block ps-5" for="procedure_attachment">
							<div class="input-group input-group-sm">
								<div class="form-control text-nowrap">Chọn file dự toán cần tải lên</div>
								<span class="btn btn-primary">Bấm tải lên</span>
							</div>
							<div style="width: 0;height: 0;overflow: hidden;">
								<!-- <input type="file" id="procedure_attachment" name="procedure_attachment" accept=".doc,.docx,.xls,.xlsx,.pdf" class="form-control"> -->
								<input type="file" id="procedure_attachment" name="procedure_attachment" class="form-control">
							</div>
						</label>
					</div>
				</div>
				<div class="mb-3<?php echo (!current_user_can('edit_contractors'))?' hidden':''; ?>">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="yes" name="procedure_quote" id="procedure_quote" <?php checked( (isset($procedure['quote']) && $procedure['quote']=='yes'), true, true ); ?>>
						<label class="form-check-label" for="procedure_quote">Được khách hàng lựa chọn?</label>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-procedure-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-procedure" tabindex="-1" role="dialog" aria-labelledby="edit-procedure-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-procedure-label">Sửa dự toán</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function display_contractor($contractor_id, $client, $progress='', $contractor_hide=[]) {
		$default_procedure_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
		$default_procedure = [
			'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
			'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
			'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
			'attachment_id' => (!empty($default_procedure_attachment))?$default_procedure_attachment['attachment_id']:'',
		];

		$default_link = fw_get_db_post_option($contractor_id, 'estimate_default_link');
		$procedure_content = fw_get_db_post_option($contractor_id, 'estimate_content');

		$procedures = get_post_meta($contractor_id, '_procedures', true);
		$procedure = isset($procedures[$client->term_id])?$procedures[$client->term_id]:[ 'require_content'=>'', 'required'=>'', 'received'=>'', 'completed'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'info'=>'', 'link'=>'', 'attachment_id'=>''];
		
		if(empty($procedure['value'])) $procedure['value'] = $default_procedure['value'];
		if(empty($procedure['unit'])) $procedure['unit'] = $default_procedure['unit'];
		if(empty($procedure['zalo'])) $procedure['zalo'] = $default_procedure['zalo'];
		if(empty($procedure['attachment_id'])) $procedure['attachment_id'] = $default_procedure['attachment_id'];

		$phone_number = get_post_meta($contractor_id, '_phone_number', true);
		$phone_number_label = fw_get_db_post_option($contractor_id, 'phone_number_label', '');

		$cats = get_the_terms( $contractor_id, 'contractor_cat' );

		$display = empty($progress) ? true : false;

		if(!$display) {
			$display_none = true;
			if('none'==$progress) {
				$display_none = false;
				if(empty($procedure['required']) && empty($procedure['received']) && empty($procedure['completed']) && empty($procedure['sent']) && empty($procedure['quote'])) {
					$display_none = true;
				}
			}

			$display_required = true;
			if('required'==$progress) {
				$display_required = false;
				if(isset($procedure['required']) && $procedure['required']!='' ) {
					$display_required = true;
				}
			}

			$display_received = true;
			if('received'==$progress) {
				$display_received = false;
				if(isset($procedure['received']) && $procedure['received']!='' ) {
					$display_received = true;
				}
			}

			$display_completed = true;
			if('completed'==$progress) {
				$display_completed = false;
				if(isset($procedure['completed']) && $procedure['completed']!='' ) {
					$display_completed = true;
				}
			}

			$display_sent = true;
			if('sent'==$progress) {
				$display_sent = false;
				if(isset($procedure['sent']) && $procedure['sent']!='' ) {
					$display_sent = true;
				}
			}

			$display_quote = true;
			if('quote'==$progress) {
				$display_quote = false;
				if(isset($procedure['quote']) && $procedure['quote']!='' ) {
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
		<div class="col-lg-3 col-md-6 procedure-item mb-4<?=$item_class?>">
			<div class="procedure procedure-<?=$contractor_id?> border border-dark h-100 bg-black">
				<div class="row g-0 progressing-bar procedure-progress text-center text-yellow">
					<div class="col procedure-required<?php echo (isset($procedure['required']) && $procedure['required']!='')?' on':''; ?>">
					<?php
					if(isset($procedure['required']) && $procedure['required']!='') {
						?>
						<div class="bg-danger" title="Ngày gửi yêu cầu">
							<?php echo esc_html(date('d/m', strtotime($procedure['required']))); ?>
						</div>
						<?php
					}
					?>
					</div>
					<div class="col procedure-received<?php echo (isset($procedure['received']) && $procedure['received']!='')?' on':''; ?>">
						<?php
						if(isset($procedure['received']) && $procedure['received']!='') {
							?>
							<div class="bg-danger" title="Ngày nhận dự toán nhà thầu">
								<?php echo esc_html(date('d/m', strtotime($procedure['received']))); ?>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col procedure-completed<?php echo (isset($procedure['completed']) && $procedure['completed']!='')?' on':''; ?>">
						<?php
						if(isset($procedure['completed']) && $procedure['completed']!='') {
							?>
							<div class="bg-danger" title="Ngày làm xong dự toán">
								<?php echo esc_html(date('d/m', strtotime($procedure['completed']))); ?>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col procedure-sent<?php echo (isset($procedure['sent']) && $procedure['sent']!='')?' on':''; ?>">
						<?php
						if(isset($procedure['sent']) && $procedure['sent']!='') {
							?>
							<div class="bg-danger" title="Ngày gửi cho khách">
								<?php echo esc_html(date('d/m', strtotime($procedure['sent']))); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<div class="contractor-thumbnail position-relative">
					<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
						<div class="procedure-require-content">
						<?php
						if(isset($procedure_content) && $procedure_content!='') {
							$procedure_content = '<div class="copy-text">'.wp_get_the_content($procedure_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
							?>
							<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($procedure_content)?>" data-bs-html="true">Đề bài</button>
							<?php
						}
						?>
						</div>
						<div class="attachment-download">
						<?php
						if(isset($procedure['attachment_id']) && $procedure['attachment_id']!='') {
							$attachment_url = wp_get_attachment_url($procedure['attachment_id']);
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
						<div class="procedure-quote<?php echo (isset($procedure['quote']) && $procedure['quote']=='yes')?' on':''; ?>">
							<?php
							if(isset($procedure['quote']) && $procedure['quote']=='yes') {
								?>
								<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Dự toán được khách hàng chọn"><span class="dashicons dashicons-yes"></span></span>
								<?php
							}
							?>
						</div>
						
						<?php if(current_user_can('edit_contractors')) { ?>
						<button class="procedure-contractor-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><span class="dashicons dashicons-visibility"></span></button>
						
						<a href="<?php echo get_edit_post_link( $contractor_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
						<?php } ?>
						<?php if(current_user_can('procedure_contractor_edit')) { ?>
						<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-procedure" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
						<?php } ?>
					</div>
					<div class="position-absolute top-0 end-0 p-1 d-flex">
						<div class="zalo-link">
						<?php if($procedure['zalo']) { ?>
							<a class="btn btn-sm btn-shadow fw-bold ms-2" href="<?=esc_url($procedure['zalo'])?>" target="_blank">Zalo</a>
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
					<?php if($procedure['value']!='') { ?>
					<div class="contractor-value mb-1">
						<span>Tổng giá trị: </span>
						<span class="text-red fw-bold"><?php echo  esc_html($procedure['value']); ?></span>
					</div>
					<?php } ?>
					<?php if($procedure['unit']) { ?>
					<div class="contractor-unit mb-1">
						<div class="text-red"><?php echo esc_html($procedure['unit']); ?></div>
					</div>
					<?php } ?>
					<div class="d-flex flex-wrap justify-content-center contractor-links">
						<?php

						if(!empty($procedure['info'])) {
							?>
							<span class="bg-danger px-2 py-1 rounded-1 my-1 mx-2 fs-sm"><?=esc_html($procedure['info'])?></span>
							<?php
						}

						if(isset($procedure['link']) && $procedure['link']!='') {
							?>
							<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($procedure['link'])?>" target="_blank">Xem chi tiết</a>
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
