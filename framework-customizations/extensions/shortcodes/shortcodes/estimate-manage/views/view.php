<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_password, $current_client;
$default_term_password = get_option( 'default_term_passwords', -1 );

//$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;

$estimate_cat = isset($atts['estimate_cat']) ? get_term_by( 'term_id', $atts['estimate_cat'][0], 'estimate_cat' ) : null;

if( $estimate_cat instanceof \WP_Term && $current_client ) {

	$client_estimates = get_term_meta($current_client->term_id, '_estimates', true);
	?>
	<div class="fw-shortcode-estimate-manage">
		<section class="mb-3">
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
						'terms' => [$estimate_cat->term_id]
					]
				]
			]);
			if($estimates) {
				foreach($estimates as $estimate_id) {
					$default_estimate = [
						'value' => fw_get_db_post_option($estimate_id,'estimate_value'),
						'unit' => fw_get_db_post_option($estimate_id,'estimate_unit'),
						'zalo' => fw_get_db_post_option($estimate_id,'estimate_zalo'),
					];

					$default_url = fw_get_db_post_option($estimate_id,'estimate_url');

					$client_estimate = isset($client_estimates[$estimate_id])?$client_estimates[$estimate_id]:[ 'value'=>'', 'unit'=>'', 'required'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

					if(empty($client_estimate['value'])) $client_estimate['value'] = $default_estimate['value'];
					if(empty($client_estimate['unit'])) $client_estimate['unit'] = $default_estimate['unit'];
					if(empty($client_estimate['zalo'])) $client_estimate['zalo'] = $default_estimate['zalo'];

					//debug($client_estimate);
					?>
					<div class="col-lg-3 col-md-6 estimate-item mb-4">
						<div class="estimate estimate-<?=$estimate_id?> border border-dark h-100">
							<div class="estimate-thumbnail position-relative">
								<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
									<div class="file-download">
									<?php
									if(isset($client_estimate['file_id']) && $client_estimate['file_id']!='') {
										$attachment_url = wp_get_attachment_url($client_estimate['file_id']);
										if($attachment_url) {
										?>
										<a class="btn-shadow btn btn-sm btn-primary fw-bold" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
										<?php
										}
									}
									?>
									</div>
									<div class="estimate-required">
									<?php
									if(isset($client_estimate['required']) && $client_estimate['required']!='') {
										?>
										<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark me-2" title="Ngày gửi đề bài yêu cầu"><?php echo esc_html(date('d/m/Y', strtotime($client_estimate['required']))); ?></span>
										<?php
									}
									?>
									</div>
								</div>
								<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0"><?php echo get_the_post_thumbnail( $estimate_id, 'full' ); ?></div>
								
								<div class="position-absolute bottom-0 end-0 m-1 d-flex">
									<div class="estimate-quote">
									<?php
									if(isset($client_estimate['quote']) && $client_estimate['quote']=='yes') {
										?>
										<span class="btn-shadow btn btn-sm btn-warning border-secondary bg-green text-dark fw-bold ms-2" title="Đã gửi cho khách hàng"><span class="dashicons dashicons-yes"></span></span>
										<?php
									}
									?>
									</div>
									<?php if(has_role('administrator')) { ?>
										<a href="<?php echo get_edit_post_link( $estimate_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
										<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-estimate-manage" data-client="<?=$current_client->term_id?>" data-estimate="<?=$estimate_id?>" data-estimate-title="<?php echo esc_attr(get_the_title( $estimate_id )); ?>"><span class="dashicons dashicons-edit"></span></button>
									<?php } ?>
								</div>
								
								<div class="zalo-link position-absolute top-0 end-0 p-2">
								<?php if($client_estimate['zalo']) { ?>
									<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($client_estimate['zalo'])?>" target="_blank">Zalo</a>
								<?php } ?>
								</div>
								<?php if($default_url) { ?>
									<a class="btn btn-sm btn-primary btn-shadow fw-bold position-absolute start-0 bottom-0 m-1 z-3" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
								<?php } ?>
							</div>
							<div class="estimate-info text-center px-1">
								<div class="estimate-title pt-3 mb-1 fs-5 text-green">
									<?php echo esc_html(get_the_title( $estimate_id )); ?>
								</div>
								<?php if($client_estimate['value']) { ?>
								<div class="estimate-value mb-1">
									<span>Tổng giá trị:</span>
									<span class="text-red fw-bold"><?php echo esc_html($client_estimate['value']); ?></span>
								</div>
								<?php } ?>
								<?php if($client_estimate['unit']) { ?>
								<div class="estimate-unit mb-1">
									<div class="text-red"><?php echo esc_html($client_estimate['unit']); ?></div>
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
		</section>
	</div>
	<?php
}