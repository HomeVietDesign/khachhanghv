<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
$shortcode = fw_ext( 'shortcodes' )->get_shortcode('contract');

/**
 * @var array $atts
 */
global $current_client;

$contract_cats = get_terms(['taxonomy' => 'contract_cat','parent'=>0]);

if($contract_cats && $current_client) {

	$contract_hide = get_term_meta($current_client->term_id, 'contract_hide', true);
	if(empty($contract_hide)) $contract_hide = [];

	?>
	<div class="fw-shortcode-contracts">
		<div class="accordion">
		<?php
		foreach ($contract_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$contracts = get_posts([
						'post_type' => 'contract',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'contract_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($contracts) {
						foreach($contracts as $contract_id) {
							$contract_content = fw_get_db_post_option($contract_id, 'contract_content');
							$default_url = fw_get_db_post_option($contract_id,'contract_url');
							$default_data = [
								'value' => fw_get_db_post_option($contract_id,'contract_value'),
								'unit' => fw_get_db_post_option($contract_id,'contract_unit'),
								'zalo' => fw_get_db_post_option($contract_id,'contract_zalo'),
							];

							$data = get_post_meta($contract_id, '_data', true);
							$contract_data = isset($data[$current_client->term_id])?$data[$current_client->term_id]:$shortcode->default;
							$contract_data += $shortcode->default;
							
							if(empty($contract_data['value'])) $contract_data['value'] = $default_data['value'];
							if(empty($contract_data['unit'])) $contract_data['unit'] = $default_data['unit'];
							if(empty($contract_data['zalo'])) $contract_data['zalo'] = $default_data['zalo'];
							
							$item_class = '';

							if(in_array($contract_id, $contract_hide)) {
								$item_class .= ' active';
							}
							?>
							<div class="col-lg-3 col-md-6 contract-item mb-4<?=$item_class?>">
								<div class="contract contract-<?=$contract_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar contract-progress text-center text-yellow">
										<div class="col contract-required">
										<?php
										if($contract_data['required']!='') {
											?>
											<div class="bg-danger" title="<?=esc_attr($contract_data['required_label'])?>">
												<?php echo esc_html(date('d/m', strtotime($contract_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col contract-created">
											<?php
											if($contract_data['created']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($contract_data['created_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($contract_data['created']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col contract-completed">
											<?php
											if($contract_data['completed']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($contract_data['completed_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($contract_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col contract-sent">
											<?php
											if($contract_data['sent']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($contract_data['sent_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($contract_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<div class="contract-thumbnail position-relative">
										<div class="contract-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="contract-require-content">
											<?php
											if($contract_content!='') {
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($contract_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
										</div>
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $contract_id, 'full' ); ?></span>

										<div class="contract-control position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>

										<div class="contract-control position-absolute bottom-0 end-0 m-1 d-flex">
											<div class="contract-signed">
												<?php
												if($contract_data['signed']=='yes') {
													?>
													<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã ký"><span class="dashicons dashicons-yes"></span></span>
													<?php
												}
												?>
											</div>
											<?php if(current_user_can('edit_contracts')) { ?>

											<button class="contract-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$current_client->term_id?>" data-contract="<?=$contract_id?>" data-contract-title="<?php echo esc_attr(get_the_title( $contract_id )); ?>" title="Ẩn/Hiện"></button>

											<a href="<?php echo get_edit_post_link( $contract_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<?php } ?>
											<?php if(current_user_can('contract_edit')) { ?>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-contract" data-client="<?=$current_client->term_id?>" data-contract="<?=$contract_id?>" data-contract-title="<?php echo esc_attr(get_the_title( $contract_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="contract-control zalo-link position-absolute top-0 end-0 p-1">
										<?php if($contract_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($contract_data['zalo'])?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>
									</div>
									<div class="contract-info text-center px-1">
										<div class="contract-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $contract_id )); ?>
										</div>
										<?php if($contract_data['value']!='' || $contract_data['unit']!='') { ?>
										<div class="contract-value mb-1">
											<?php if($contract_data['value']!='') { ?>
											<div>
												<span>Tổng giá trị: </span>
												<span class="text-red fw-bold"><?php echo esc_html($contract_data['value']); ?></span>
											</div>
											<?php } ?>
											<?php if($contract_data['unit']!='') { ?>
											<div class="text-red"><?php echo esc_html($contract_data['unit']); ?></div>
											<?php } ?>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center contract-links mb-3">
											<?php
											if($contract_data['url']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($contract_data['url'])?>" target="_blank">Hợp đồng bản 1</a>
												<?php
											}
											if($contract_data['url2']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($contract_data['url2'])?>" target="_blank">Hợp đồng bản 2</a>
												<?php
											}
											if($contract_data['url3']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($contract_data['url3'])?>" target="_blank">Hợp đồng bản 3</a>
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