<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
$media_cats = get_terms(['taxonomy' => 'media_cat','parent'=>0]);

if($media_cats) {
?>
<div class="fw-shortcode-medias">
	<div class="accordion">
		<?php
		foreach ($media_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
				<button class="accordion-button text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$medias = get_posts([
						'post_type' => 'media',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'media_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($medias) {
						foreach($medias as $media_id) {
							$phone_number = fw_get_db_post_option($media_id, 'phone_number');
							$zalo = fw_get_db_post_option($media_id, 'zalo');
							$web = fw_get_db_post_option($media_id, 'web');
							$fb = fw_get_db_post_option($media_id, 'fb');
							
							if($zalo=='') $zalo = $phone_number;

							$_last_date = fw_get_db_post_option($media_id, 'last_date');
							$_end_date = fw_get_db_post_option($media_id, 'end_date');
							?>
							<div class="col-lg-3 col-md-6 media-item mb-4">
								<div class="media media-<?=$media_id?> h-100 bg-black border border-dark">
									<div class="links text-uppercase">
										<div class="row g-0">
											<div class="col media-web position-relative">
											<?php
											if($web!='') {
												?>
												<a href="<?=esc_url($web)?>" class="d-block w-100 py-2 d-flex justify-content-center align-items-center text-yellow" target="_blank">Web</a>
												<?php
											}
											?>
											</div>
											<div class="col media-fb position-relative">
											<?php
											if($fb!='') {
												?>
												<a href="<?=esc_url($fb)?>" class="d-block w-100 py-2 d-flex justify-content-center align-items-center text-yellow" target="_blank">Face</a>
												<?php
											}
											?>
											</div>
											<div class="col media-last_date position-relative">
											<?php
											if($_last_date!='') {
												$last_date = date('d/m/y', strtotime($_last_date));
												?>
												<span class="d-block w-100 py-2 d-flex justify-content-center align-items-center text-yellow"><?=esc_html($last_date)?></span>
												<?php
											}
											?>
											</div>
											<div class="col media-end_date position-relative">
											<?php
											if($_end_date!='') {
												$end_date = date('d/m/y', strtotime($_end_date));
												?>
												<span class="d-block w-100 py-2 d-flex justify-content-center align-items-center text-yellow"><?=esc_html($end_date)?></span>
												<?php
											}
											?>
											</div>
										</div>
									</div>
									<div class="media-thumbnail position-relative">
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $media_id, 'full' ); ?></span>
										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<?php if(current_user_can('edit_medias')) { ?>
											<a href="<?php echo get_edit_post_link( $media_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>

											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-medias" data-media="<?=$media_id?>" data-medias-title="<?php echo esc_attr(get_the_title( $media_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
									</div>
									<div class="media-info text-center px-1">
										<div class="media-title pt-3 mb-3 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $media_id )); ?>
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
