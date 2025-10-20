<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcode = fw_ext( 'shortcodes' )->get_shortcode('efurniture');

/**
 * @var array $atts
 */
global $current_client;

$efurniture_cats = get_terms(['taxonomy' => 'efurniture_cat','parent'=>0]);

if($efurniture_cats && $current_client) {

	$efurniture_hide = get_term_meta($current_client->term_id, 'efurniture_hide', true);
	if(empty($efurniture_hide)) $efurniture_hide = [];

	$efurniture_removed = get_term_meta($current_client->term_id, 'efurniture_removed', true);
	if(empty($efurniture_removed)) $efurniture_removed = [];

	// $data = fw_get_db_term_option($current_client->term_id, 'passwords', 'efurniture', []); // bỏ vì gây mất dữ liệu
	$data = get_term_meta($current_client->term_id, 'efurniture', true);
	if(empty($data)) $data = [];
	?>
	<div class="fw-shortcode-efurnitures">
		<div class="accordion">
		<?php
		foreach ($efurniture_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header" id="accordion-header-<?=$value->term_id?>">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$value->term_id?>" aria-expanded="true" aria-controls="panels-<?=$value->term_id?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$value->term_id?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$efurnitures = get_posts([
						'post_type' => 'efurniture',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'efurniture_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($efurnitures) {
						foreach($efurnitures as $efurniture_id) {
							$efurniture_content = fw_get_db_post_option($efurniture_id, 'efurniture_content');
							$default_efurniture_file = fw_get_db_post_option($efurniture_id,'efurniture_file');
							$default_url = fw_get_db_post_option($efurniture_id,'efurniture_url');
							$default_zalo = fw_get_db_post_option($efurniture_id,'efurniture_zalo');
							$default_data = [
								'value' => fw_get_db_post_option($efurniture_id,'efurniture_value'),
								'unit' => fw_get_db_post_option($efurniture_id,'efurniture_unit'),
								'file_id' => (!empty($default_efurniture_file))?$default_efurniture_file['attachment_id']:'',
							];

							$efurniture_data = isset($data[$efurniture_id])?$data[$efurniture_id]:$shortcode->default;
							$efurniture_data += $shortcode->default;

							if(empty($efurniture_data['value'])) $efurniture_data['value'] = $default_data['value'];
							if(empty($efurniture_data['unit'])) $efurniture_data['unit'] = $default_data['unit'];
							if(empty($efurniture_data['file_id'])) $efurniture_data['file_id'] = $default_data['file_id'];

							$item_class = '';

							if(in_array($efurniture_id, $efurniture_hide)) {
								$item_class .= ' active';
							}

							if(in_array($efurniture_id, $efurniture_removed)) {
								$item_class .= ' removed';
							}
							?>
							<div class="col-lg-3 col-md-6 estimate-item efurniture-item mb-4<?=$item_class?>">
								<div class="efurniture efurniture-<?=$efurniture_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar efurniture-progress text-center text-yellow">
										<div class="col estimate-required efurniture-required">
										<?php
										if($efurniture_data['required']!='') {
											?>
											<div class="bg-danger" title="<?=esc_attr($efurniture_data['required_label'])?>">
												<?php echo esc_html(date('d/m', strtotime($efurniture_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col estimate-received efurniture-received">
											<?php
											if($efurniture_data['received']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($efurniture_data['received_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($efurniture_data['received']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col estimate-completed efurniture-completed">
											<?php
											if($efurniture_data['completed']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($efurniture_data['completed_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($efurniture_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col estimate-sent efurniture-sent">
											<?php
											if($efurniture_data['sent']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($efurniture_data['sent_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($efurniture_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>	
									</div>
									<div class="efurniture-thumbnail position-relative">
										<div class="efurniture-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="efurniture-require-content">
											<?php
											if($efurniture_content!='') {
												$efurniture_content = '<div class="copy-text">'.wp_get_the_content($efurniture_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($efurniture_content)?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="required-content">
											<?php
										
											if($efurniture_data['required_content']!='') {
												$required_content = '<div class="copy-text">'.wp_get_the_content($efurniture_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
												<?php
											}
											?>
											</div>
											<div class="file-download">
											<?php
											if($efurniture_data['file_id']!='') {
												$attachment_url = wp_get_attachment_url($efurniture_data['file_id']);
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
											<?php
											if(has_post_thumbnail( $efurniture_id )) {
												echo get_the_post_thumbnail( $efurniture_id, 'full' );
											} else {
												?>
												<div class="thumbnail-title d-flex w-100 h-100 align-items-center text-center justify-content-center">
													<?=nl2br(esc_textarea(get_post_meta($efurniture_id, '_thumbnail_title', true)))?>
												</div>
												<?php
											}
											?>
										</div>

										<div class="efurniture-control position-absolute bottom-0 end-0 m-1 d-flex">

											<?php if(current_user_can('edit_estimate_furnitures')) { ?>

											<button class="efurniture-toggle btn btn-sm btn-warning ms-2" type="button" data-client="<?=$current_client->term_id?>" data-efurniture="<?=$efurniture_id?>" data-efurniture-title="<?php echo esc_attr(get_the_title( $efurniture_id )); ?>" title="<?php echo (in_array($efurniture_id, $efurniture_removed))?'Sử dụng':'Loại bỏ'; ?>"></button>

											<button class="efurniture-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$current_client->term_id?>" data-efurniture="<?=$efurniture_id?>" data-efurniture-title="<?php echo esc_attr(get_the_title( $efurniture_id )); ?>" title="Ẩn/Hiện"></button>

											<a href="<?php echo get_edit_post_link( $efurniture_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-efurniture" data-client="<?=$current_client->term_id?>" data-efurniture="<?=$efurniture_id?>" data-efurniture-title="<?php echo esc_attr(get_the_title( $efurniture_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="efurniture-control zalo-link position-absolute top-0 end-0 p-1 d-flex">
										<?php if($efurniture_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($efurniture_data['zalo'])?>" target="_blank">RIÊNG</a>
										<?php } ?>
										<?php if($default_zalo) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($default_zalo)?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>

										<div class="efurniture-control position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>
									</div>
									<div class="efurniture-info text-center px-1">
										<div class="efurniture-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $efurniture_id )); ?>
										</div>
										<?php if($efurniture_data['value']) { ?>
										<div class="efurniture-value mb-1">
											<span class="text-red fw-bold"><?php echo esc_html($efurniture_data['value']); ?></span>
										</div>
										<?php } ?>
										<?php if($efurniture_data['unit']) { ?>
										<div class="efurniture-unit mb-1">
											<div class="text-red fw-bold"><?php echo esc_html($efurniture_data['unit']); ?></div>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center efurniture-url mb-3">
											<?php
											if(isset($efurniture_data['url']) && $efurniture_data['url']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($efurniture_data['url'])?>" target="_blank">Dự toán 1</a>
												<?php
											}

											if(isset($efurniture_data['url2']) && $efurniture_data['url2']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($efurniture_data['url2'])?>" target="_blank">Dự toán 2</a>
												<?php
											}

											if(isset($efurniture_data['url3']) && $efurniture_data['url3']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($efurniture_data['url3'])?>" target="_blank">Dự toán 3</a>
												<?php
											}

											if(isset($efurniture_data['url4']) && $efurniture_data['url4']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($efurniture_data['url4'])?>" target="_blank">Dự toán 4</a>
												<?php
											}

											if(isset($efurniture_data['url5']) && $efurniture_data['url5']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($efurniture_data['url5'])?>" target="_blank">Dự toán 5</a>
												<?php
											}
											?>
										</div>
									</div>
								</div>
							</div>
					<?php }
					}
					?>
					</div>
				</div>
			</div>
		</section>
		<?php
		}
		?>
		</div>
	</div>
	<?php
}