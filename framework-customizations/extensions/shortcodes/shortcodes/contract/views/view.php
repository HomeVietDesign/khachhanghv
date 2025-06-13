<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_client;

$contract_cats = get_terms(['taxonomy' => 'contract_cat','parent'=>0]);

if($contract_cats && $current_client) {
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
							$default_data = [
								'value' => fw_get_db_post_option($contract_id,'contract_value'),
								'unit' => fw_get_db_post_option($contract_id,'contract_unit'),
								'zalo' => fw_get_db_post_option($contract_id,'contract_zalo'),
								'url' => fw_get_db_post_option($contract_id,'contract_url'),
							];

							$data = get_post_meta($contract_id, '_data', true);
							$contract_data = isset($data[$current_client->term_id])?$data[$current_client->term_id]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>''];
							
							if(empty($contract_data['value'])) $contract_data['value'] = $default_data['value'];
							if(empty($contract_data['unit'])) $contract_data['unit'] = $default_data['unit'];
							if(empty($contract_data['zalo'])) $contract_data['zalo'] = $default_data['zalo'];
							if(empty($contract_data['url'])) $contract_data['url'] = $default_data['url'];
							
							?>
							<div class="col-lg-3 col-md-6 contract-item mb-4">
								<div class="contract contract-<?=$contract_id?> border border-dark h-100 bg-black">
									<div class="contract-thumbnail position-relative">
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-dark"><?php echo get_the_post_thumbnail( $contract_id, 'full' ); ?></span>
										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<?php if(has_role('administrator')) { ?>
											<a href="<?php echo get_edit_post_link( $contract_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<?php } ?>
											<?php if(current_user_can('contract_edit')) { ?>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-contract" data-client="<?=$current_client->term_id?>" data-contract="<?=$contract_id?>" data-contract-title="<?php echo esc_attr(get_the_title( $contract_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="zalo-link position-absolute top-0 end-0 p-1">
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
												<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($contract_data['url'])?>" target="_blank">Xem chi tiết</a>
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