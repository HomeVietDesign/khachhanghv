<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Estimate_Customer extends FW_Shortcode
{
	public $default = [
		'required_content' => '',
		'value'=>'',
		'unit'=>'',
		'zalo'=>'',
		'attachment_id'=>''
	];

	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_estimate_customer_form', [$this, 'ajax_get_edit_estimate_customer_form']);
      add_action( 'wp_ajax_update_estimate_customer', [$this, 'ajax_update_estimate_customer']);
      add_action( 'wp_ajax_get_estimate_customer_info', [$this, 'ajax_get_estimate_customer_info']);
      add_action( 'wp_ajax_estimate_customer_hide', [$this, 'ajax_estimate_customer_hide']);
      add_action( 'wp_ajax_estimate_customer_toggle', [$this, 'ajax_estimate_customer_toggle']);
	}

	public function ajax_estimate_customer_toggle() {
		global $current_client;
		$contractor_id = isset($_POST['contractor']) ? absint($_POST['contractor']) : 0;
		$response = 0;
		if(current_user_can('edit_estimate_customers') && $current_client && $contractor_id && check_ajax_referer( 'global', 'nonce', false )) {
			$contractor_customer_removed = get_term_meta($current_client->term_id, 'contractor_customer_removed', true);
			if(empty($contractor_customer_removed)) $contractor_customer_removed = [];

			if(in_array($contractor_id, $contractor_customer_removed)) {
				unset($contractor_customer_removed[array_search($contractor_id, $contractor_customer_removed)]);
				$response = -1;
			} else {
				$contractor_customer_removed[] = $contractor_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'contractor_customer_removed', $contractor_customer_removed);
		}
		
		wp_send_json($response);
	}

	public function ajax_estimate_customer_hide() {
		global $current_client;
		$contractor_id = isset($_POST['contractor']) ? absint($_POST['contractor']) : 0;
		$response = 0;
		if(current_user_can('edit_estimate_customers') && $current_client && $contractor_id && check_ajax_referer( 'global', 'nonce', false )) {
			$contractor_customer_hide = get_term_meta($current_client->term_id, 'contractor_customer_hide', true);
			if(empty($contractor_customer_hide)) $contractor_customer_hide = [];

			if(in_array($contractor_id, $contractor_customer_hide)) {
				unset($contractor_customer_hide[array_search($contractor_id, $contractor_customer_hide)]);
				$response = -1;
			} else {
				$contractor_customer_hide[] = $contractor_id;
				$response = 1;
			}

			update_term_meta($current_client->term_id, 'contractor_customer_hide', $contractor_customer_hide);
		}
		
		wp_send_json($response);
	}

	public function ajax_get_estimate_customer_info() {
		global $current_client;

		$contractor_id = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		$response = [
			'required_content' => '',
			'info' => '',
			'zalo' => ''
		];
		
		if($current_client && $contractor_id) {
			$default_estimate_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
			$default_zalo = fw_get_db_post_option($contractor_id,'estimate_zalo');
			$default_estimate = [
				'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
				'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
				'attachment_id' => ($default_estimate_attachment) ? $default_estimate_attachment['attachment_id']:''
			];

			$estimates = get_post_meta($contractor_id, '_estimate_customer', true);
			$estimate = isset($estimates[$current_client->term_id])?$estimates[$current_client->term_id]:$this->default;
			$estimate += $this->default;

			if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
			if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
			if(empty($estimate['zalo'])) $estimate['zalo'] = $default_estimate['zalo'];
			if(empty($estimate['attachment_id'])) $estimate['attachment_id'] = $default_estimate['attachment_id'];

			$phone_number = get_post_meta($contractor_id, '_phone_number', true);
			$external_url = get_post_meta($contractor_id, '_external_url', true);
			$external_url = ($external_url!='')?esc_url($external_url):'#';

			$cats = get_the_terms( $contractor_id, 'contractor_cat' );

			$response['zalo'] = ($estimate['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($estimate['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['zalo'] .= ($default_zalo)?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($default_zalo).'" target="_blank">Zalo</a>':'';

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
			<div class="contractor-title pt-3 mb-1 fs-5">
				<a class="d-block" href="<?=$external_url?>" target="_blank" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
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
			<?php if($estimate['value']!='') { ?>
			<div class="contractor-value mb-1">
				<span class="text-red fw-bold"><?php echo esc_html($estimate['value']); ?></span>
			</div>
			<?php } ?>
			<?php if($estimate['unit']!='') { ?>
			<div class="contractor-unit mb-1">
				<div class="text-red fw-bold"><?php echo esc_html($estimate['unit']); ?></div>
			</div>
			<?php } ?>
			<div class="d-flex flex-wrap justify-content-center contractor-links mb-3">
				<?php
				if($phone_number && current_user_can('estimate_customer_view')) {
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

	public function ajax_update_estimate_customer() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if((current_user_can('edit_estimate_customers')) && check_ajax_referer( 'edit-estimate-customer', 'nonce', false )) {
			$estimate_client = isset($_POST['estimate_client'])?absint($_POST['estimate_client']):0;
			$estimate_contractor = isset($_POST['estimate_contractor'])?absint($_POST['estimate_contractor']):0;
			$estimate_attachment_id = isset($_POST['estimate_attachment_id'])?absint($_POST['estimate_attachment_id']):0;
			$required_content = isset($_POST['required_content'])?wp_kses_post($_POST['required_content']):'';
			$estimate_value = isset($_POST['estimate_value'])?sanitize_text_field($_POST['estimate_value']):'';
			$estimate_unit = isset($_POST['estimate_unit'])?sanitize_text_field($_POST['estimate_unit']):'';
			$estimate_zalo = isset($_POST['estimate_zalo'])?sanitize_text_field($_POST['estimate_zalo']):'';
			$estimate_attachment = isset($_FILES['estimate_attachment']) ? $_FILES['estimate_attachment'] : null;

			if($estimate_client && $estimate_contractor) {
				$estimates = get_post_meta($estimate_contractor, '_estimate_customer', true);
				if(empty($estimates)) $estimates = [];
				$estimate = isset($estimates[$estimate_client])?$estimates[$estimate_client]:$this->default;
				$estimate += $this->default;

				$new_estimate = [
					'required_content' => $required_content,
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

				update_post_meta( $estimate_contractor, '_estimate_customer', $estimates );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_estimate_customer_form() {
		$client = isset($_GET['client'])?absint($_GET['client']):0;
		$contractor = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		if($client && $contractor) {
			$estimates = get_post_meta($contractor, '_estimate_customer', true);
			$estimate = isset($estimates[$client])?$estimates[$client]:$this->default;
			$estimate += $this->default;

			$attachment_url = ($estimate['attachment_id'])?wp_get_attachment_url($estimate['attachment_id']):'';
			?>
			<form id="frm-edit-estimate-customer" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="estimate_client" name="estimate_client" value="<?=$client?>">
				<input type="hidden" id="estimate_contractor" name="estimate_contractor" value="<?=$contractor?>">
				<?php wp_nonce_field( 'edit-estimate-customer', 'nonce' ); ?>
				<div id="edit-estimate-customer-response"></div>
				<div class="row">
					<div class="col-lg-7<?php echo (!current_user_can('edit_contractors'))?' hidden':''; ?>">
						<div class="mb-3">
							Nội dung áp dụng
							<?php
							$settings = [
								'media_buttons' => false,
								'teeny'         => false,
								'quicktags'     => false,
								'editor_height' => '400',
								'tinymce'       => [
									'toolbar1' => 'bold,italic,underline,alignleft,aligncenter,alignright,alignjustify,link,unlink,bullist,numlist,undo,redo,fullscreen',
									'toolbar2' => 'forecolor,pastetext,removeformat,charmap',
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
						<div class="col mb-3">
							Tên - Số điện thoại
							<input type="text" id="estimate_value" name="estimate_value" class="form-control" value="<?php echo esc_attr($estimate['value']); ?>">
						</div>
						<div class="col mb-3">
							Ghi chú
							<input type="text" id="estimate_unit" name="estimate_unit" class="form-control" value="<?php echo esc_attr($estimate['unit']); ?>">
						</div>
						<div class="mb-3">
							Link nhóm zalo
							<input type="text" id="estimate_zalo" name="estimate_zalo" class="form-control" value="<?php echo esc_attr($estimate['zalo']); ?>">
						</div>
					</div>
					<div class="col-lg-12">
						<div class="mb-3">
							<div class="form-label mb-1">File dự toán</div>
							<div class="row row-cols-2 g-0 p-2 border rounded-2">
								<div class="col attachment-uploaded">
									<input type="hidden" id="estimate_attachment_id" name="estimate_attachment_id" value="<?=esc_attr($estimate['attachment_id'])?>">
									<?php if($attachment_url) { ?>
										<div class="input-group input-group-sm">
											<div class="form-control text-truncate"><?=esc_html(basename($attachment_url))?></div>
											<button class="btn btn-warning" id="estimate_customer_remove_attachment" type="button">Xóa file</button>
										</div>
									<?php } ?>
								</div>
								<label class="col d-block ps-5" for="estimate_attachment">
									<div class="input-group input-group-sm">
										<div class="form-control text-nowrap">Chọn file dự toán cần tải lên</div>
										<span class="btn btn-primary">Bấm tải lên</span>
									</div>
									<div style="width: 0;height: 0;overflow: hidden;">
										<input type="file" id="estimate_attachment" name="estimate_attachment" class="form-control">
									</div>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-estimate-customer-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-estimate-customer" tabindex="-1" role="dialog" aria-labelledby="edit-estimate-customer-label">
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-estimate-customer-label">Sửa dự toán</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}

	public function display_contractor($contractor_id, $client, $contractor_customer_hide=[], $contractor_customer_removed=[]) {
		
		$default_estimate_attachment = fw_get_db_post_option($contractor_id, 'estimate_attachment');
		$default_zalo = fw_get_db_post_option($contractor_id,'estimate_zalo');
		$default_estimate = [
			'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
			'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
			'attachment_id' => ($default_estimate_attachment) ? $default_estimate_attachment['attachment_id']:''
		];

		$estimate_content = fw_get_db_post_option($contractor_id, 'estimate_content');

		$estimates = get_post_meta($contractor_id, '_estimate_customer', true);
		$estimate = isset($estimates[$client->term_id])?$estimates[$client->term_id]:$this->default;
		$estimate += $this->default;
		
		if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
		if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
		
		if(empty($estimate['attachment_id'])) $estimate['attachment_id'] = $default_estimate['attachment_id'];

		$phone_number = get_post_meta($contractor_id, '_phone_number', true);

		$item_class = '';
		
		$cats = get_the_terms( $contractor_id, 'contractor_cat' );

		$project_images = fw_get_db_post_option($contractor_id, 'project_images');

		if(in_array($contractor_id, $contractor_customer_hide)) {
			$item_class .= ' active';
		}

		if(in_array($contractor_id, $contractor_customer_removed)) {
			$item_class .= ' removed';
		}
		?>
		<div class="col-lg-3 col-md-6 estimate-item mb-4<?=$item_class?>">
			<div class="estimate estimate-<?=$contractor_id?> border border-dark h-100">
				<div class="contractor-thumbnail position-relative">
					<div class="position-absolute contractor-control top-0 start-0 p-1 z-3 d-flex">
						<?php
						if(isset($estimate_content) && $estimate_content!='') {
							$estimate_content = '<div class="copy-text">'.wp_get_the_content($estimate_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
							?>
							<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($estimate_content)?>" data-bs-html="true">Đề bài</button>
							<?php
						}
						?>
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
					</div>

					<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0">
						<?php
						if(has_post_thumbnail( $contractor_id )) {
							echo get_the_post_thumbnail( $contractor_id, 'full' );
						} else {
							?>
							<div class="thumbnail-title d-flex w-100 h-100 align-items-center text-center justify-content-center">
								<?=nl2br(esc_textarea(get_post_meta($contractor_id, '_thumbnail_title', true)))?>
							</div>
							<?php
						}
						?>
					</div>
					
					<div class="position-absolute contractor-control bottom-0 end-0 m-1 d-flex">
						
						<?php if(current_user_can('edit_estimate_customers')) { ?>

						<button class="estimate-customer-toggle btn btn-sm btn-warning ms-2" type="button" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>" title="<?php echo (in_array($contractor_id, $contractor_customer_removed))?'Sử dụng':'Loại bỏ'; ?>"></button>

						<button class="estimate-customer-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>" title="Ẩn/Hiện"></button>
						
						<a href="<?php echo get_edit_post_link( $contractor_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
						
						<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-estimate-customer" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
						<?php } // if(current_user_can('edit_estimate_customers')) ?>

					</div>

					<div class="zalo-link position-absolute contractor-control top-0 end-0 p-1">
					<?php if($estimate['zalo']) { ?>
						<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($estimate['zalo'])?>" target="_blank">RIÊNG</a>
					<?php } ?>
					<?php if($default_zalo) { ?>
						<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($default_zalo)?>" target="_blank">Zalo</a>
					<?php } ?>
					</div>
					<div class="position-absolute contractor-control start-0 bottom-0 p-1 z-3 d-flex">
						
					</div>
				</div>
				<div class="contractor-info text-center px-1">
					<div class="contractor-title pt-3 mb-1 fs-5">
						<div class="text-green" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></div>
						<div class="fs-6 text-yellow d-flex flex-wrap justify-content-center">
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
					<?php if($estimate['value']!='') { ?>
					<div class="contractor-value mb-1">
						<span class="text-red fw-bold"><?php echo  esc_html($estimate['value']); ?></span>
					</div>
					<?php } ?>
					<?php if($estimate['unit']!='') { ?>
					<div class="contractor-unit mb-1">
						<div class="text-red fw-bold"><?php echo esc_html($estimate['unit']); ?></div>
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
							if($attachment_url) {
							?>
							<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($attachment_url)?>" target="_blank">Xem chi tiết</a>
							<?php
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
