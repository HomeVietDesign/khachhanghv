<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_password;
$default_term_password = get_option( 'default_term_passwords', -1 );

$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;

$interiors = get_posts([
					'post_type' => 'interior',
					'posts_per_page' => -1,
					'post_status' => 'publish',
					'fields' => 'ids'
				]);

if($interiors && $client && has_role('administrator')) {

	$estimates = get_term_meta($client->term_id, '_estimates', true);
	?>
	<div class="fw-shortcode-estimates-interior">
		<div class="row">
		<?php
		if($interiors) {
			foreach($interiors as $interior_id) {
				$estimate = isset($estimates[$interior_id])?$estimates[$interior_id]:[ 'value'=>'', 'url'=>''];
				?>
				<div class="col-lg-3 col-md-6 estimate-item mb-4">
					<div class="estimate border border-dark h-100">
						<div class="interior-thumbnail position-relative">
							<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0"><?php echo get_the_post_thumbnail( $interior_id, 'full' ); ?></div>
							<button type="button" class="btn btn-sm btn-danger text-yellow fw-bold m-1 position-absolute bottom-0 end-0" data-bs-toggle="modal" data-bs-target="#edit-estimate-interior" data-client="<?=$client->term_id?>" data-interior="<?=$interior_id?>" data-interior-title="<?php echo esc_attr(get_the_title( $interior_id )); ?>"><span class="dashicons dashicons-edit"></span></button>
						</div>
						<div class="interior-info interior-info-<?=$interior_id?> text-center px-1">
							<div class="interior-title pt-3 mb-1 fs-5 text-uppercase">
								<?php echo esc_html(get_the_title( $interior_id )); ?>
							</div>
							
							<div class="interior-value mb-1">
								<span>Tổng giá trị:</span>
								<span class="text-red fw-bold"><?php echo ($estimate['value']) ? esc_html(number_format($estimate['value'],0,'.',',')) : ''; ?></span>
							</div>
							
							<div class="d-flex flex-wrap justify-content-center interior-links mb-3">
								<?php
								if($estimate['url']) {
									?>
									<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($estimate['url'])?>" target="_blank">Dự toán</a>
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