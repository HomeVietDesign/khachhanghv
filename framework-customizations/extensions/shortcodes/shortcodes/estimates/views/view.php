<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;
$contractor_cats = get_terms(['taxonomy' => 'contractor_cat','parent'=>0]);

if($contractor_cats && $client) {
	?>
	<div class="fw-shortcode-estimates">
		<div class="accordion">
	<?php
	foreach ($contractor_cats as $key => $value) {
		if($key>0) {
			$contractors = get_posts([
				'post_type' => 'contractor',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'fields' => 'ids',
				'tax_query' => [
					'cat' => [
						'taxonomy' => 'contractor_cat',
						'field' => 'id',
						'terms' => [$value->term_id]
					],
					'rating' => [
						'taxonomy' => 'contractor_rating',
						'field' => 'id',
						'terms' => fw_get_db_settings_option('contractor_rating_top')
					]
				]
			]);
			if($contractors) {
			?>
			<section class="accordion-item mb-3">
				<h2 class="accordion-header">
					<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
				</h2>
				<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
      				<div class="accordion-body">
						<div class="row">
						<?php
						foreach($contractors as $contractor_id) {
							$estimates = get_post_meta($contractor_id, '_estimates', true);
							$estimate = isset($estimates[$client->term_id])?$estimates[$client->term_id]:[ 'value'=>'', 'attachment_id'=>''];
							//debug($estimate);
							$phone_number = get_post_meta($contractor_id, '_phone_number', true);
							$external_url = get_post_meta($contractor_id, '_external_url', true);
							$external_url = ($external_url!='')?esc_url($external_url):'#';

							$cats = get_the_terms( $contractor_id, 'contractor_cat' );
							?>
							<div class="col-lg-3 col-md-6 estimate-item mb-4">
								<div class="estimate border border-dark">
									<div class="contractor-thumbnail position-relative">
										<a class="thumbnail-image position-absolute w-100 h-100 start-0 top-0" href="<?=$external_url?>" target="_blank"><?php echo get_the_post_thumbnail( $contractor_id, 'full' ); ?></a>
										<?php if(has_role('administrator')) { ?>
										<button type="button" class="btn btn-sm btn-danger text-yellow fw-bold m-1 position-absolute bottom-0 end-0" data-bs-toggle="modal" data-bs-target="#edit-estimate" data-client="<?=$client->term_id?>" data-contractor="<?=$contractor_id?>" data-contractor-title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>">Sửa</button>
										<?php } ?>
									</div>
									<div class="contractor-info contractor-info-<?=$contractor_id?> text-center px-1">
										<div class="contractor-title py-3 fs-5">
											<a class="d-block text-truncate" href="<?=$external_url?>" target="_blank" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
											<div class="text-truncate fs-6 text-yellow d-flex flex-wrap justify-content-center">
												<?php
												if($cats) {
													foreach ($cats as $key => $cat) {
														echo '<div>'.(($key>0)?', ':' ').esc_html($cat->name).'</div>';
													}
												}
												?>
											</div>
										</div>
										
										<div class="contractor-value mb-3">
											<span>Tổng giá trị:</span>
											<span class="text-red fw-bold"><?php echo ($estimate['value']) ? esc_html(number_format($estimate['value'],0,'.',',')) : ''; ?></span>
										</div>
										
										<div class="d-flex flex-wrap justify-content-center contractor-links mb-3">
											<?php
											if($phone_number) {
												?>
												<a class="btn btn-sm btn-danger my-1 mx-2" href="tel:<?=esc_attr($phone_number)?>"><?=esc_html($phone_number)?></a>
												<?php
											}

											if($estimate['attachment_id']) {
												$attachment_url = wp_get_attachment_url($estimate['attachment_id']);
												?>
												<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($attachment_url)?>" target="_blank">Xem dự toán</a>
												<?php
											}
											?>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
						</div>
					</div>
				</div>
			</section>
			<?php
			}
		}
	}
	?>
		</div>
	</div>
	<?php
}