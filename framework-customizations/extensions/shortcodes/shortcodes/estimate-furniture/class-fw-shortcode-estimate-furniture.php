<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Estimate_Furniture extends FW_Shortcode
{
	public $default = [
		'required_content'=>'',
		'required_label'=>'',
		'required'=>'',
		'received_label'=>'',
		'received'=>'',
		'completed_label'=>'',
		'completed'=>'',
		'sent_label'=>'',
		'sent'=>'',
		'value'=>'',
		'unit'=>'',
		'zalo'=>'',
		'info'=>'',
		'link'=>'',
		'link2'=>'',
		'link3'=>'',
		'attachment_id'=>'',
		'quote'=>''
	];

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
		$response = 0;
		if(current_user_can('estimate_furniture_edit') && $current_client && $contractor_id && check_ajax_referer( 'global', 'nonce', false )) {

			$contractor_furniture_hide = get_term_meta($current_client->term_id, 'contractor_furniture_hide', true);
			if(empty($contractor_furniture_hide)) $contractor_furniture_hide = [];

			if(in_array($contractor_id, $contractor_furniture_hide)) {
				unset($contractor_furniture_hide[array_search($contractor_id, $contractor_furniture_hide)]);
				$response = -1;
			} else {
				$contractor_furniture_hide[] = $contractor_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'contractor_furniture_hide', $contractor_furniture_hide);
		}
		wp_send_json($response);
	}

	public function ajax_get_estimate_furniture_info() {
		global $current_client;
		
		$contractor_id = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		$response = [
			'required_content' => '',
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
			$default_zalo = fw_get_db_post_option($contractor_id,'estimate_zalo');
			$default_estimate = [
				'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
				'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
				'attachment_id' => (!empty($default_estimate_attachment))?$default_estimate_attachment['attachment_id']:'',
			];

			$estimates = get_post_meta($contractor_id, '_estimate_furniture', true);
			if(empty($estimates)) $estimates = [];
			
			$estimate = isset($estimates[$current_client->term_id])?$estimates[$current_client->term_id]:$this->default;
			$estimate += $this->default;

			if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
			if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
			if(empty($estimate['attachment_id'])) $estimate['attachment_id'] = $default_estimate['attachment_id'];

			$phone_number = get_post_meta($contractor_id, '_phone_number', true);
			$phone_number_label = fw_get_db_post_option($contractor_id, 'phone_number_label', '');

			$cats = get_the_terms( $contractor_id, 'contractor_cat' );

			$response['zalo'] = ($estimate['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($estimate['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['zalo'] .= ($default_zalo)?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($default_zalo).'" target="_blank">Zalo</a>':'';

			$response['attachment'] = ($estimate['attachment_id'])?'<a class="btn-shadow btn btn-sm btn-primary me-1" href="'.esc_url(wp_get_attachment_url($estimate['attachment_id'])).'" target="_blank">Tải</a>':'';

			$response['required'] = ($estimate['required']!='')?'<div class="bg-danger" title="'.esc_attr($estimate['required_label']).'">'.esc_html(date('d/m', strtotime($estimate['required']))).'</div>':'';
			$response['received'] = ($estimate['received']!='')?'<div class="bg-danger" title="'.esc_attr($estimate['received_label']).'">'.esc_html(date('d/m', strtotime($estimate['received']))).'</div>':'';
			$response['completed'] = ($estimate['completed']!='')?'<div class="bg-danger" title="'.esc_attr($estimate['completed_label']).'">'.esc_html(date('d/m', strtotime($estimate['completed']))).'</div>':'';
			$response['sent'] = ($estimate['sent']!='')?'<div class="bg-danger" title="'.esc_attr($estimate['sent_label']).'">'.esc_html(date('d/m', strtotime($estimate['sent']))).'</div>':'';

			$response['quote'] = ($estimate['quote']=='yes')?'<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Dự toán được khách hàng chọn"><span class="dashicons dashicons-yes"></span></span>':'';

			ob_start();
			
			if($estimate['required_content']!='') {
				$required_content = '<div class="copy-text">'.wp_get_the_content($estimate['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
				?>
				<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
				<?php
			}

			$response['required_content'] = ob_get_clean();

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
				<span class="text-red fw-bold"><?php echo  esc_html($estimate['value']); ?></span>
				
			</div>
			<?php } ?>
			<?php if($estimate['unit']) { ?>
			<div class="contractor-unit mb-1">
				<div class="text-red fw-bold"><?php echo esc_html($estimate['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center contractor-links">
				<?php
			
				if(!empty($estimate['info'])) {
					?>
					<span class="bg-danger px-2 py-1 rounded-1 fs-sm my-1 mx-1"><?=esc_html($estimate['info'])?></span>
					<?php
				}

				if($estimate['link']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($estimate['link'])?>" target="_blank">Dự toán 1</a>
					<?php
				}
				if($estimate['link2']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($estimate['link2'])?>" target="_blank">Dự toán 2</a>
					<?php
				}
				if($estimate['link3']) {
					?>
					<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($estimate['link3'])?>" target="_blank">Dự toán 3</a>
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
			$required_content = isset($_POST['required_content'])?wp_kses_post($_POST['required_content']):'';
			$estimate_furniture_value = isset($_POST['estimate_furniture_value'])?sanitize_text_field($_POST['estimate_furniture_value']):'';
			$estimate_furniture_unit = isset($_POST['estimate_furniture_unit'])?sanitize_text_field($_POST['estimate_furniture_unit']):'';
			$estimate_furniture_zalo = isset($_POST['estimate_furniture_zalo'])?sanitize_text_field($_POST['estimate_furniture_zalo']):'';
			$estimate_furniture_info = isset($_POST['estimate_furniture_info'])?sanitize_text_field($_POST['estimate_furniture_info']):'';
			$estimate_furniture_link = isset($_POST['estimate_furniture_link'])?sanitize_text_field($_POST['estimate_furniture_link']):'';
			$estimate_furniture_link2 = isset($_POST['estimate_furniture_link2'])?sanitize_text_field($_POST['estimate_furniture_link2']):'';
			$estimate_furniture_link3 = isset($_POST['estimate_furniture_link3'])?sanitize_text_field($_POST['estimate_furniture_link3']):'';

			$estimate_furniture_attachment_id = isset($_POST['estimate_furniture_attachment_id'])?absint($_POST['estimate_furniture_attachment_id']):0;
			$estimate_furniture_attachment = isset($_FILES['estimate_furniture_attachment']) ? $_FILES['estimate_furniture_attachment'] : null;

			$estimate_furniture_required_label = isset($_POST['estimate_furniture_required_label']) ? $_POST['estimate_furniture_required_label'] : '';
			$estimate_furniture_required = isset($_POST['estimate_furniture_required']) ? $_POST['estimate_furniture_required'] : '';
			$estimate_furniture_received_label = isset($_POST['estimate_furniture_received_label']) ? $_POST['estimate_furniture_received_label'] : '';
			$estimate_furniture_received = isset($_POST['estimate_furniture_received']) ? $_POST['estimate_furniture_received'] : '';
			$estimate_furniture_completed_label = isset($_POST['estimate_furniture_completed_label']) ? $_POST['estimate_furniture_completed_label'] : '';
			$estimate_furniture_completed = isset($_POST['estimate_furniture_completed']) ? $_POST['estimate_furniture_completed'] : '';
			$estimate_furniture_sent_label = isset($_POST['estimate_furniture_sent_label']) ? $_POST['estimate_furniture_sent_label'] : '';
			$estimate_furniture_sent = isset($_POST['estimate_furniture_sent']) ? $_POST['estimate_furniture_sent'] : '';
			$estimate_furniture_quote = isset($_POST['estimate_furniture_quote']) ? $_POST['estimate_furniture_quote'] : '';

			//debug_log($_POST);

			if($estimate_furniture_client && $estimate_furniture_contractor) {
				$estimates = get_post_meta($estimate_furniture_contractor, '_estimate_furniture', true);
				if(empty($estimates)) $estimates = [];
				$estimate = isset($estimates[$estimate_furniture_client])?$estimates[$estimate_furniture_client]:$this->default;
				$estimate += $this->default;

				$new_estimate = [
					'required_content' => $required_content,
					'required_label' => $estimate_furniture_required_label,
					'required' => $estimate_furniture_required,
					'received_label' => $estimate_furniture_received_label,
					'received' => $estimate_furniture_received,
					'completed_label' => $estimate_furniture_completed_label,
					'completed' => $estimate_furniture_completed,
					'sent_label' => $estimate_furniture_sent_label,
					'sent' => $estimate_furniture_sent,
					'value' => $estimate_furniture_value,
					'unit' => $estimate_furniture_unit,
					'zalo' => $estimate_furniture_zalo,
					'info' => $estimate_furniture_info,
					'link' => $estimate_furniture_link,
					'link2' => $estimate_furniture_link2,
					'link3' => $estimate_furniture_link3,
					'attachment_id' => ($estimate_furniture_attachment_id!=0)?$estimate_furniture_attachment_id:'',
					'quote' => $estimate_furniture_quote,
				];

				// tải lên file dự toán
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					require_once(ABSPATH . "wp-admin" . '/includes/file.php');
					require_once(ABSPATH . "wp-admin" . '/includes/media.php');
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
			
			$estimate = isset($estimates[$client])?$estimates[$client]:$this->default;
			$estimate += $this->default;

			$attachment_url = (isset($estimate['attachment_id']) && $estimate['attachment_id']!='')?wp_get_attachment_url($estimate['attachment_id']):'';
			?>
			<form id="frm-edit-estimate-furniture" method="POST" action="" >
				<input type="hidden" id="estimate_furniture_client" name="estimate_furniture_client" value="<?=$client?>">
				<input type="hidden" id="estimate_furniture_contractor" name="estimate_furniture_contractor" value="<?=$contractor?>">
				<?php wp_nonce_field( 'edit-estimate-furniture', 'nonce' ); ?>
				<div id="edit-estimate-furniture-response"></div>
				<div class="row">
					<div class="col-lg-7<?php echo (!current_user_can('edit_contractors'))?' hidden':''; ?>">
						<div class="mb-3">
							Nội dung áp dụng
							<?php
							$settings = [
								'media_buttons' => false,
								'teeny'         => false,
								'quicktags'     => false,
								'editor_height' => '840',
								'tinymce'       => [
									'toolbar1' => 'bold,italic,underline,alignleft,aligncenter,alignright,alignjustify,link,unlink,bullist,numlist,forecolor,pastetext,removeformat,charmap,fullscreen',
									'toolbar2' => '',
									'content_style' => 'body { font-family: Arial, Helvetica, sans-serif; font-size: 16px; }'
								]
							];
							wp_editor( $estimate['required_content'], 'required_content', $settings);
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
					<div class="<?php echo (!current_user_can('edit_contractors'))?' col-lg-12':'col-lg-5'; ?>">
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($estimate['required_label']!='')?esc_html($estimate['required_label']):''; ?>" name="estimate_furniture_required_label" id="estimate_furniture_required_label" placeholder="Ghi chú ngày 1">
							<input class="form-control" type="date" value="<?php echo ($estimate['required']!='')?esc_html(date('Y-m-d', strtotime($estimate['required']))):''; ?>" name="estimate_furniture_required" id="estimate_furniture_required">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($estimate['received_label']!='')?esc_html($estimate['received_label']):''; ?>" name="estimate_furniture_received_label" id="estimate_furniture_received_label" placeholder="Ghi chú ngày 2">
							<input class="form-control" type="date" value="<?php echo ($estimate['received']!='')?esc_html(date('Y-m-d', strtotime($estimate['received']))):''; ?>" name="estimate_furniture_received" id="estimate_furniture_received">
						</div>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($estimate['completed_label']!='')?esc_html($estimate['completed_label']):''; ?>" name="estimate_furniture_completed_label" id="estimate_furniture_completed_label" placeholder="Ghi chú ngày 3">
							<input class="form-control" type="date" value="<?php echo ($estimate['completed']!='')?esc_html(date('Y-m-d', strtotime($estimate['completed']))):''; ?>" name="estimate_furniture_completed" id="estimate_furniture_completed">
						</div>                                  
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?php echo ($estimate['sent_label']!='')?esc_html($estimate['sent_label']):''; ?>" name="estimate_furniture_sent_label" id="estimate_furniture_sent_label" placeholder="Ghi chú ngày 4">
							<input class="form-control" type="date" value="<?php echo ($estimate['sent']!='')?esc_html(date('Y-m-d', strtotime($estimate['sent']))):''; ?>" name="estimate_furniture_sent" id="estimate_furniture_sent">
						</div>
						<div class="col mb-3">
							Tên - Số điện thoại
							<input type="text" id="estimate_furniture_value" name="estimate_furniture_value" class="form-control" value="<?php echo esc_attr($estimate['value']); ?>">
						</div>
						<div class="col mb-3">
							Ghi chú
							<input type="text" id="estimate_furniture_unit" name="estimate_furniture_unit" class="form-control" value="<?php echo esc_attr($estimate['unit']); ?>">
						</div>
						<div class="col mb-3">
							Link nhóm zalo
							<input type="text" id="estimate_furniture_zalo" name="estimate_furniture_zalo" class="form-control" value="<?php echo esc_attr($estimate['zalo']); ?>">
						</div>
						<div class="col mb-3">
							Thông tin nhà thầu
							<input type="text" id="estimate_furniture_info" name="estimate_furniture_info" class="form-control" value="<?php echo esc_attr($estimate['info']); ?>">
						</div>
						<div class="mb-3">
							Link dự toán 1
							<input type="text" id="estimate_furniture_link" name="estimate_furniture_link" class="form-control" value="<?php echo esc_attr($estimate['link']); ?>">
						</div>
						<div class="mb-3">
							Link dự toán 2
							<input type="text" id="estimate_furniture_link2" name="estimate_furniture_link2" class="form-control" value="<?php echo esc_attr($estimate['link2']); ?>">
						</div>
						<div class="mb-3">
							Link dự toán 3
							<input type="text" id="estimate_furniture_link3" name="estimate_furniture_link3" class="form-control" value="<?php echo esc_attr($estimate['link3']); ?>">
						</div>
					</div>
					<div class="col-lg-12">
						<div class="mb-3">
							<div class="form-label mb-1">File dữ liệu</div>
							<div class="row row-cols-2 g-0 p-2 border rounded-2">
								<div class="col attachment-uploaded">
									<input type="hidden" id="estimate_furniture_attachment_id" name="estimate_furniture_attachment_id" value="<?=esc_attr($estimate['attachment_id'])?>">
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
										<input type="file" id="estimate_furniture_attachment" name="estimate_furniture_attachment" class="form-control">
									</div>
								</label>
							</div>
						</div>
						<div class="mb-3<?php echo (!current_user_can('edit_contractors'))?' hidden':''; ?> text-center">
							<div class="d-inline-block">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" value="yes" name="estimate_furniture_quote" id="estimate_furniture_quote" <?php checked( $estimate['quote']=='yes', true, true ); ?>>
									<label class="form-check-label" for="estimate_furniture_quote">Được khách hàng lựa chọn?</label>
								</div>
							</div>
						</div>
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
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
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

	public function display_contractor($contractor_id, $client, $contractor_hide=[]) {
		$default_estimate_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
		$default_zalo = fw_get_db_post_option($contractor_id,'estimate_zalo');
		$default_estimate = [
			'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
			'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
			'attachment_id' => (!empty($default_estimate_attachment))?$default_estimate_attachment['attachment_id']:'',
		];

		$default_link = fw_get_db_post_option($contractor_id, 'estimate_default_link');
		$estimate_content = fw_get_db_post_option($contractor_id, 'estimate_content');

		$estimates = get_post_meta($contractor_id, '_estimate_furniture', true);
		$estimate = isset($estimates[$client->term_id])?$estimates[$client->term_id]:$this->default;
		$estimate += $this->default;
		
		if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
		if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
		if(empty($estimate['attachment_id'])) $estimate['attachment_id'] = $default_estimate['attachment_id'];

		$phone_number = get_post_meta($contractor_id, '_phone_number', true);
		$phone_number_label = fw_get_db_post_option($contractor_id, 'phone_number_label', '');

		$cats = get_the_terms( $contractor_id, 'contractor_cat' );

		$item_class = '';

		if(in_array($contractor_id, $contractor_hide)) {
			$item_class .= ' active';
		}

		?>
		<div class="col-lg-3 col-md-6 estimate-item mb-4<?=$item_class?>">
			<div class="estimate estimate-<?=$contractor_id?> border border-dark h-100 bg-black">
				<div class="row g-0 progressing-bar estimate-progress text-center text-yellow">
					<div class="col estimate-required">
					<?php
					if($estimate['required']!='') {
						?>
						<div class="bg-danger" title="<?=esc_attr($estimate['required_label'])?>">
							<?php echo esc_html(date('d/m', strtotime($estimate['required']))); ?>
						</div>
						<?php
					}
					?>
					</div>
					<div class="col estimate-received">
						<?php
						if($estimate['received']!='') {
							?>
							<div class="bg-danger" title="<?=esc_attr($estimate['received_label'])?>">
								<?php echo esc_html(date('d/m', strtotime($estimate['received']))); ?>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col estimate-completed">
						<?php
						if($estimate['completed']!='') {
							?>
							<div class="bg-danger" title="<?=esc_attr($estimate['completed_label'])?>n">
								<?php echo esc_html(date('d/m', strtotime($estimate['completed']))); ?>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col estimate-sent">
						<?php
						if($estimate['sent']!='') {
							?>
							<div class="bg-danger" title="<?=esc_attr($estimate['sent_label'])?>">
								<?php echo esc_html(date('d/m', strtotime($estimate['sent']))); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<div class="contractor-thumbnail position-relative">
					<div class="contractor-control position-absolute top-0 start-0 p-1 z-3 d-flex">
						<div class="estimate-require-content">
						<?php
						if($estimate_content!='') {
							$estimate_content = '<div class="copy-text">'.wp_get_the_content($estimate_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
							?>
							<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($estimate_content)?>" data-bs-html="true">Đề bài</button>
							<?php
						}
						?>
						</div>
						<div class="required-content">
						<?php
						if($estimate['required_content']!='') {
							$required_content = '<div class="copy-text">'.wp_get_the_content($estimate['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
							?>
							<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
							<?php
						}
						?>
						</div>
						<div class="attachment-download">
						<?php
						if($estimate['attachment_id']!='') {
							$attachment_url = wp_get_attachment_url($estimate['attachment_id']);
							if($attachment_url) {
							?>
							<a class="btn-shadow btn btn-sm btn-primary fw-bold me-1" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
							<?php
							}
						}
						?>
						</div>
					</div>
					<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-top border-bottom border-dark">
						<?php echo get_the_post_thumbnail( $contractor_id, 'full' ); ?>
					</div>
					<div class="contractor-control position-absolute bottom-0 end-0 m-1 d-flex">
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

						<button class="estimate-contractor-furniture-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>" title="Ẩn/Hiện"></button>
						
						<a href="<?php echo get_edit_post_link( $contractor_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
						<?php } ?>
						<?php if(current_user_can('estimate_furniture_edit')) { ?>
						<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-estimate-furniture" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
						<?php } ?>
					</div>
					<div class="contractor-control position-absolute top-0 end-0 p-1 d-flex">
						<div class="zalo-link d-flex">
						<?php if($estimate['zalo']) { ?>
							<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($estimate['zalo'])?>" target="_blank">RIÊNG</a>
						<?php } ?>
						<?php if($default_zalo) { ?>
							<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($default_zalo)?>" target="_blank">Zalo</a>
						<?php } ?>
						</div>
					</div>
					<div class="contractor-control position-absolute start-0 bottom-0 p-1 z-3 d-flex">
						<?php if($default_link) { ?>
						<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_link)?>" target="_blank">Gốc</a>
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
						<span class="text-red fw-bold"><?php echo  esc_html($estimate['value']); ?></span>
					</div>
					<?php } ?>
					<?php if($estimate['unit']) { ?>
					<div class="contractor-unit mb-1">
						<div class="text-red fw-bold"><?php echo esc_html($estimate['unit']); ?></div>
					</div>
					<?php } ?>
					<div class="d-flex flex-wrap justify-content-center contractor-links">
						<?php

						if(!empty($estimate['info'])) {
							?>
							<span class="bg-danger px-2 py-1 rounded-1 fs-sm my-1 mx-1"><?=esc_html($estimate['info'])?></span>
							<?php
						}

						if($estimate['link']!='') {
							?>
							<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($estimate['link'])?>" target="_blank">Dự toán 1</a>
							<?php
						}
						if($estimate['link2']!='') {
							?>
							<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($estimate['link2'])?>" target="_blank">Dự toán 2</a>
							<?php
						}
						if($estimate['link3']!='') {
							?>
							<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($estimate['link3'])?>" target="_blank">Dự toán 3</a>
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
