<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_password;
$default_term_password = get_option( 'default_term_passwords', -1 );

$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;
$estimate_cats = get_terms(['taxonomy' => 'estimate_cat','parent'=>0]);

if( $estimate_cats && $client ) {

	$client_estimates = get_term_meta($client->term_id, '_estimates', true);
	?>
	<div class="fw-shortcode-estimate-manage">
		<div class="accordion">
		<?php foreach ($estimate_cats as $key => $value) { ?>
			<section class="accordion-item mb-3">
				<h2 class="accordion-header">
					<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
				</h2>
				<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
					<div class="accordion-body">
						<div class="row justify-content-center">
						<?php
						$estimates = get_posts([
							'post_type' => 'estimate',
							'posts_per_page' => -1,
							'post_status' => 'publish',
							'fields' => 'ids',
							'tax_query' => [
								'cat' => [
									'taxonomy' => 'estimate_cat',
									'field' => 'term_id',
									'terms' => [$value->term_id]
								]
							]
						]);
						if($estimates) {
							foreach($estimates as $estimate_id) {
								$default_estimate = [
									'value' => fw_get_db_post_option($estimate_id,'estimate_value'),
									'unit' => fw_get_db_post_option($estimate_id,'estimate_unit'),
									'zalo' => fw_get_db_post_option($estimate_id,'estimate_zalo'),
									'url' => fw_get_db_post_option($estimate_id,'estimate_url')
								];

								$client_estimate = isset($client_estimates[$estimate_id])?$client_estimates[$estimate_id]:[ 'value'=>'', 'zalo'=>'', 'url'=>''];

								if(empty($client_estimate['value'])) $client_estimate['value'] = $default_estimate['value'];
								if(empty($client_estimate['unit'])) $client_estimate['unit'] = $default_estimate['unit'];
								if(empty($client_estimate['zalo'])) $client_estimate['zalo'] = $default_estimate['zalo'];
								if(empty($client_estimate['url'])) $client_estimate['url'] = $default_estimate['url'];

								?>
								<div class="col-lg-3 col-md-6 estimate-item mb-4">
									<div class="estimate estimate-<?=$estimate_id?> border border-dark h-100">
										<div class="estimate-thumbnail position-relative">
											<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0"><?php echo get_the_post_thumbnail( $estimate_id, 'full' ); ?></div>
											
											<?php if(has_role('administrator')) { ?>
											<div class="position-absolute bottom-0 end-0 m-1 d-flex">
												<a href="<?php echo get_edit_post_link( $estimate_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
												<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-1" data-bs-toggle="modal" data-bs-target="#edit-estimate-manage" data-client="<?=$client->term_id?>" data-estimate="<?=$estimate_id?>" data-estimate-title="<?php echo esc_attr(get_the_title( $estimate_id )); ?>"><span class="dashicons dashicons-edit"></span></button>
											</div>
											<?php } ?>

											<div class="zalo-link position-absolute top-0 end-0 p-2">
											<?php if($client_estimate['zalo']) { ?>
												<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($client_estimate['zalo'])?>" target="_blank">Zalo</a>
											<?php } ?>
											</div>
										</div>
										<div class="estimate-info text-center px-1">
											<div class="estimate-title pt-3 mb-1 fs-5">
												<?php echo esc_html(get_the_title( $estimate_id )); ?>
											</div>
											<?php if($client_estimate['value']) { ?>
											<div class="estimate-value mb-1">
												<span>Tổng giá trị:</span>
												<span class="text-red fw-bold"><?php echo esc_html($client_estimate['value']); ?></span>
												<div class="text-red"> <?php echo esc_html($client_estimate['unit']); ?></div>
											</div>
											<?php } ?>
											<div class="d-flex flex-wrap justify-content-center estimate-url mb-3">
												<?php
												if($client_estimate['url']) {
													?>
													<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($client_estimate['url'])?>" target="_blank">Xem chi tiết</a>
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
		<?php } ?>
		</div>
	</div>
	<?php
}