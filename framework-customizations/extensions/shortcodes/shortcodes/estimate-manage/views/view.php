<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
$estimate_cat = isset($atts['estimate_cat']) ? array_map('absint',$atts['estimate_cat']) : [];

global $current_password;
$default_term_password = get_option( 'default_term_passwords', -1 );

$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;

$estimates = get_posts([
					'post_type' => 'estimate',
					'posts_per_page' => -1,
					'post_status' => 'publish',
					'fields' => 'ids',
					'tax_query' => [
						'cat' => [
							'taxonomy' => 'estimate_cat',
							'field' => 'term_id',
							'terms' => $estimate_cat
						]
					]
				]);

if( $estimates && $client ) {

	$client_estimates = get_term_meta($client->term_id, '_estimates', true);
	?>
	<div class="fw-shortcode-estimate-manage">
		<div class="row justify-content-center">
		<?php
		if($estimates) {
			foreach($estimates as $estimate_id) {
				$client_estimate = isset($client_estimates[$estimate_id])?$client_estimates[$estimate_id]:[ 'value'=>'', 'url'=>''];
				?>
				<div class="col-lg-3 col-md-6 estimate-item mb-4">
					<div class="estimate border border-dark h-100">
						<div class="estimate-thumbnail position-relative">
							<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0"><?php echo get_the_post_thumbnail( $estimate_id, 'full' ); ?></div>
							<?php if(has_role('administrator')) { ?>
							<button type="button" class="btn btn-sm btn-danger text-yellow fw-bold m-1 position-absolute bottom-0 end-0" data-bs-toggle="modal" data-bs-target="#edit-estimate-manage" data-client="<?=$client->term_id?>" data-estimate="<?=$estimate_id?>" data-estimate-title="<?php echo esc_attr(get_the_title( $estimate_id )); ?>"><span class="dashicons dashicons-edit"></span></button>
							<?php } ?>
						</div>
						<div class="estimate-info estimate-info-<?=$estimate_id?> text-center px-1">
							<div class="estimate-title pt-3 mb-1 fs-5 text-uppercase">
								<?php echo esc_html(get_the_title( $estimate_id )); ?>
							</div>
							<?php if($client_estimate['value']) { ?>
							<div class="estimate-value mb-1">
								<span>Tổng giá trị:</span>
								<span class="text-red fw-bold"><?php echo esc_html(number_format($client_estimate['value'],0,'.',',')); ?></span>
							</div>
							<?php } ?>
							<div class="d-flex flex-wrap justify-content-center estimate-url mb-3">
								<?php
								if($client_estimate['url']) {
									?>
									<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($client_estimate['url'])?>" target="_blank">Dự toán</a>
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
	<?php
}