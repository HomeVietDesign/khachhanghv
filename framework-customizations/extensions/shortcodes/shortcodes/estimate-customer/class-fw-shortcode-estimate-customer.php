<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Estimate_Customer extends FW_Shortcode
{
	
	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_estimate_customer_form', [$this, 'ajax_get_edit_estimate_customer_form']);
      add_action( 'wp_ajax_update_estimate_customer', [$this, 'ajax_update_estimate_customer']);
      add_action( 'wp_ajax_get_estimate_customer_info', [$this, 'ajax_get_estimate_customer_info']);
	}

	public function ajax_get_estimate_customer_info() {
		global $current_password, $current_client;
		$default_term_password = get_option( 'default_term_passwords', -1 );

		$contractor_id = isset($_GET['contractor'])?absint($_GET['contractor']):0;

		$response = [
			'info' => '',
			'zalo' => ''
		];
		
		if($current_client && $contractor_id) {
			$default_estimate_attachment = fw_get_db_post_option($contractor_id,'estimate_attachment');
			$default_estimate = [
				'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
				'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
				'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
				'attachment_id' => ($default_estimate_attachment) ? $default_estimate_attachment['attachment_id']:''
			];

			$estimates = get_post_meta($contractor_id, '_estimate_customer', true);
			$estimate = isset($estimates[$current_client->term_id])?$estimates[$current_client->term_id]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

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
				<a class="d-block" href="<?=$external_url?>" target="_blank" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
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
				if($phone_number && (current_user_can('estimate_customer_view') || ( $current_password && $current_password->term_id == $default_term_password ))) {
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

		if((current_user_can('estimate_customer_edit')) && check_ajax_referer( 'edit-estimate-customer', 'nonce', false )) {
			$estimate_client = isset($_POST['estimate_client'])?absint($_POST['estimate_client']):0;
			$estimate_contractor = isset($_POST['estimate_contractor'])?absint($_POST['estimate_contractor']):0;
			//$estimate_drawing_id = isset($_POST['estimate_drawing_id'])?absint($_POST['estimate_drawing_id']):0;
			$estimate_attachment_id = isset($_POST['estimate_attachment_id'])?absint($_POST['estimate_attachment_id']):0;
			$estimate_value = isset($_POST['estimate_value'])?sanitize_text_field($_POST['estimate_value']):'';
			$estimate_unit = isset($_POST['estimate_unit'])?sanitize_text_field($_POST['estimate_unit']):'';
			$estimate_zalo = isset($_POST['estimate_zalo'])?sanitize_text_field($_POST['estimate_zalo']):'';
			$estimate_attachment = isset($_FILES['estimate_attachment']) ? $_FILES['estimate_attachment'] : null;

			if($estimate_client && $estimate_contractor) {
				$estimates = get_post_meta($estimate_contractor, '_estimate_customer', true);
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
			$estimate = isset($estimates[$client])?$estimates[$client]:['value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

			$attachment_url = ($estimate['attachment_id'])?wp_get_attachment_url($estimate['attachment_id']):'';
			?>
			<form id="frm-edit-estimate-customer" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="estimate_client" name="estimate_client" value="<?=$client?>">
				<input type="hidden" id="estimate_contractor" name="estimate_contractor" value="<?=$contractor?>">
				<?php wp_nonce_field( 'edit-estimate-customer', 'nonce' ); ?>
				<div id="edit-estimate-customer-response"></div>
				<div class="col mb-3">
					<input type="text" id="estimate_value" name="estimate_value" placeholder="Giá trị" class="form-control" value="<?php echo esc_attr($estimate['value']); ?>">
				</div>
				<div class="col mb-3">
					<input type="text" id="estimate_unit" name="estimate_unit" placeholder="Ghi chú" class="form-control" value="<?php echo esc_attr($estimate['unit']); ?>">
				</div>
				<div class="mb-3">
					<input type="text" id="estimate_zalo" name="estimate_zalo" placeholder="Link nhóm zalo" class="form-control" value="<?php echo esc_attr($estimate['zalo']); ?>">
				</div>
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
								<input type="file" id="estimate_attachment" name="estimate_attachment" accept=".doc,.docx,.xls,.xlsx,.pdf" class="form-control">
							</div>
						</label>
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
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
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

	public static function display_contractor($contractor_id, $client) {
		global $current_password;
		$default_term_password = get_option('default_term_passwords', -1);
		
		$default_estimate_attachment = fw_get_db_post_option($contractor_id, 'estimate_attachment');
		$default_estimate = [
			'value' => fw_get_db_post_option($contractor_id,'estimate_value'),
			'unit' => fw_get_db_post_option($contractor_id,'estimate_unit'),
			'zalo' => fw_get_db_post_option($contractor_id,'estimate_zalo'),
			'attachment_id' => ($default_estimate_attachment) ? $default_estimate_attachment['attachment_id']:''
		];

		$estimates = get_post_meta($contractor_id, '_estimate_customer', true);
		$estimate = isset($estimates[$client->term_id])?$estimates[$client->term_id]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];
		
		if(empty($estimate['value'])) $estimate['value'] = $default_estimate['value'];
		if(empty($estimate['unit'])) $estimate['unit'] = $default_estimate['unit'];
		if(empty($estimate['zalo'])) $estimate['zalo'] = $default_estimate['zalo'];
		if(empty($estimate['attachment_id'])) $estimate['attachment_id'] = $default_estimate['attachment_id'];

		$phone_number = get_post_meta($contractor_id, '_phone_number', true);
		$external_url = get_post_meta($contractor_id, '_external_url', true);
		$external_url = ($external_url!='')?esc_url($external_url):'#';

		$cats = get_the_terms( $contractor_id, 'contractor_cat' );

		if( !(current_user_can('estimate_customer_view')) && empty($estimate['attachment_id']) ) {
			return;
		}

		$project_images = fw_get_db_post_option($contractor_id, 'project_images');
		?>
		<div class="col-lg-3 col-md-6 estimate-item mb-4">
			<div class="estimate estimate-<?=$contractor_id?> border border-dark h-100">
				<div class="contractor-thumbnail position-relative">

					<a class="thumbnail-image position-absolute w-100 h-100 start-0 top-0" href="<?=$external_url?>"<?php echo ($external_url!='#')?' target="_blank"':''; ?>><?php echo get_the_post_thumbnail( $contractor_id, 'full' ); ?></a>
					
					<?php if(current_user_can('estimate_customer_view')): ?>

					<div class="position-absolute bottom-0 end-0 m-1 d-flex">
						
						<?php if(has_role('administrator')) { ?>
						<a href="<?php echo get_edit_post_link( $contractor_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
						<?php } // if(has_role('administrator')) ?>
						
						<?php if(current_user_can('estimate_customer_edit')) { ?>
						<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-estimate-customer" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
						<?php } // if(current_user_can('estimate_customer_edit')) ?>

					</div>

					<?php endif; // if(current_user_can('estimate_customer_view')) ?>

					<div class="zalo-link position-absolute top-0 end-0 p-1">
					<?php if($estimate['zalo']) { ?>
						<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($estimate['zalo'])?>" target="_blank">Zalo</a>
					<?php } ?>
					</div>
					<div class="position-absolute start-0 bottom-0 p-1 z-3 d-flex">
						<?php if(!empty($project_images)) { ?>
						<div class="position-relative project-images pswp-gallery me-2">
							<?php foreach ($project_images as $key => $value) {
							$src_full = wp_get_attachment_image_src( $value['attachment_id'], 'full' );
							?>
							<a class="btn btn-sm btn-primary btn-shadow<?php echo ($key>0)?' hidden':''; ?> text-nowrap" href="<?=esc_url($src_full[0])?>" data-pswp-width="<?=$src_full[1]?>" data-pswp-height="<?=$src_full[2]?>"><?php echo ($key==0)?'Hình ảnh':''; ?></a>
							<?php } ?>
						</div>
						<?php } ?>
					</div>
				</div>
				<div class="contractor-info text-center px-1">
					<div class="contractor-title pt-3 mb-1 fs-5">
						<a class="d-block" href="<?=$external_url?>" target="_blank" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
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
					<?php if($estimate['unit']!='') { ?>
					<div class="contractor-unit mb-1">
						<div class="text-red"><?php echo esc_html($estimate['unit']); ?></div>
					</div>
					<?php } ?>
					<div class="d-flex flex-wrap justify-content-center contractor-links mb-3">
						<?php
						if($phone_number && (current_user_can('estimate_customer_view') || ( $current_password && $current_password->term_id == $default_term_password ))) {
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
