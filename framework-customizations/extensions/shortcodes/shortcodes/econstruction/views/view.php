<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
$shortcode = fw_ext( 'shortcodes' )->get_shortcode('econstruction');

/**
 * @var array $atts
 */
global $current_client;

$econstruction_cats = get_terms(['taxonomy' => 'econstruction_cat','parent'=>0]);

if($econstruction_cats && $current_client) {

	$econstruction_hide = get_term_meta($current_client->term_id, 'econstruction_hide', true);
	if(empty($econstruction_hide)) $econstruction_hide = [];

	$data = get_term_meta($current_client->term_id, 'econstruction', true);
	if(empty($data)) $data = [];

	?>
	<div class="fw-shortcode-econstructions">
		<div class="accordion">
		<?php
		foreach ($econstruction_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header" id="accordion-header-<?=$value->term_id?>">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$value->term_id?>" aria-expanded="true" aria-controls="panels-<?=$value->term_id?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$value->term_id?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$econstructions = get_posts([
						'post_type' => 'econstruction',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'econstruction_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($econstructions) {
						foreach($econstructions as $econstruction_id) {
							$econstruction_content = fw_get_db_post_option($econstruction_id, 'econstruction_content');
							$default_econstruction_file = fw_get_db_post_option($econstruction_id,'econstruction_file');
							$default_url = fw_get_db_post_option($econstruction_id,'econstruction_url');
							$default_data = [
								'value' => fw_get_db_post_option($econstruction_id,'econstruction_value'),
								'unit' => fw_get_db_post_option($econstruction_id,'econstruction_unit'),
								'zalo' => fw_get_db_post_option($econstruction_id,'econstruction_zalo'),
								'file_id' => (!empty($default_econstruction_file))?$default_econstruction_file['attachment_id']:'',
							];

							$econstruction_data = isset($data[$econstruction_id])?$data[$econstruction_id]:$shortcode->default;
							$econstruction_data += $shortcode->default;

							if(empty($econstruction_data['value'])) $econstruction_data['value'] = $default_data['value'];
							if(empty($econstruction_data['unit'])) $econstruction_data['unit'] = $default_data['unit'];
							if(empty($econstruction_data['zalo'])) $econstruction_data['zalo'] = $default_data['zalo'];
							if(empty($econstruction_data['file_id'])) $econstruction_data['file_id'] = $default_data['file_id'];

							$item_class = '';

							if(in_array($econstruction_id, $econstruction_hide)) {
								$item_class .= ' active';
							}
							?>
							<div class="col-lg-3 col-md-6 estimate-item econstruction-item mb-4<?=$item_class?>">
								<div class="econstruction econstruction-<?=$econstruction_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar econstruction-progress text-center text-yellow">
										<div class="col estimate-required econstruction-required">
										<?php
										if($econstruction_data['required']!='') {
											?>
											<div class="bg-danger" title="<?=esc_attr($econstruction_data['required_label'])?>">
												<?php echo esc_html(date('d/m', strtotime($econstruction_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col estimate-received econstruction-received">
											<?php
											if($econstruction_data['received']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($econstruction_data['received_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($econstruction_data['received']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col estimate-completed econstruction-completed">
											<?php
											if($econstruction_data['completed']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($econstruction_data['completed_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($econstruction_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col estimate-sent econstruction-sent">
											<?php
											if($econstruction_data['sent']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($econstruction_data['sent_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($econstruction_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>	
									</div>
									<div class="econstruction-thumbnail position-relative">
										<div class="econstruction-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="econstruction-require-content">
											<?php
											if(isset($econstruction_content) && $econstruction_content!='') {
												$econstruction_content = '<div class="copy-text">'.wp_get_the_content($econstruction_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($econstruction_content)?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="file-download">
											<?php
											if(isset($econstruction_data['file_id']) && $econstruction_data['file_id']!='') {
												$attachment_url = wp_get_attachment_url($econstruction_data['file_id']);
												if($attachment_url) {
												?>
												<a class="btn-shadow btn btn-sm btn-primary fw-bold" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
												<?php
												}
											}
											?>
											</div>
										</div>
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-top border-bottom border-dark"><?php echo get_the_post_thumbnail( $econstruction_id, 'full' ); ?></span>

										<div class="econstruction-control position-absolute bottom-0 end-0 m-1 d-flex">
											<div class="estimate-quote econstruction-quote<?php echo (isset($econstruction_data['quote']) && $econstruction_data['quote']=='yes')?' on':''; ?>">
												<?php
												if(isset($econstruction_data['quote']) && $econstruction_data['quote']=='yes') {
													?>
													<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã chọn"><span class="dashicons dashicons-yes"></span></span>
													<?php
												}
												?>
											</div>
											<?php if(current_user_can('edit_econstructions')) { ?>
											
											<button class="econstruction-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$current_client->term_id?>" data-econstruction="<?=$econstruction_id?>" data-econstruction-title="<?php echo esc_attr(get_the_title( $econstruction_id )); ?>" title="Ẩn/Hiện"></button>

											<a href="<?php echo get_edit_post_link( $econstruction_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<?php } ?>

											<?php if(current_user_can('econstruction_edit')) { ?>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-econstruction" data-client="<?=$current_client->term_id?>" data-econstruction="<?=$econstruction_id?>" data-econstruction-title="<?php echo esc_attr(get_the_title( $econstruction_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="econstruction-control zalo-link position-absolute top-0 end-0 p-1">
										<?php if($econstruction_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($econstruction_data['zalo'])?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>

										<div class="econstruction-control position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>
									</div>
									<div class="econstruction-info text-center px-1">
										<div class="econstruction-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $econstruction_id )); ?>
										</div>
										<?php if($econstruction_data['value']) { ?>
										<div class="econstruction-value mb-1">
											<span>Tổng giá trị:</span>
											<span class="text-red fw-bold"><?php echo esc_html($econstruction_data['value']); ?></span>
										</div>
										<?php } ?>
										<?php if($econstruction_data['unit']) { ?>
										<div class="econstruction-unit mb-1">
											<div class="text-red"><?php echo esc_html($econstruction_data['unit']); ?></div>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center econstruction-url mb-3">
											<?php
											if($econstruction_data['url']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($econstruction_data['url'])?>" target="_blank">Dự toán 1</a>
												<?php
											}
											if($econstruction_data['url2']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($econstruction_data['url2'])?>" target="_blank">Dự toán 2</a>
												<?php
											}
											if($econstruction_data['url3']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($econstruction_data['url3'])?>" target="_blank">Dự toán 3</a>
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