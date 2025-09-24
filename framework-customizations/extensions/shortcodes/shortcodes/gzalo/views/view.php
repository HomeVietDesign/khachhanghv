<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
$gzalo_cats = get_terms(['taxonomy' => 'gzalo_cat','parent'=>0]);

if($gzalo_cats) {
?>
<div class="fw-shortcode-gzalos">
	<div class="accordion">
		<?php
		foreach ($gzalo_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
				<button class="accordion-button text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$gzalos = get_posts([
						'post_type' => 'gzalo',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'gzalo_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($gzalos) {
						foreach($gzalos as $gzalo_id) {
							$gzalo_url = fw_get_db_post_option($gzalo_id, 'gzalo_zalo');
							$gzalo_content = fw_get_db_post_option($gzalo_id, 'gzalo_content');
							
							?>
							<div class="col-lg-3 col-md-6 gzalo-item mb-4">
								<div class="gzalo gzalo-<?=$gzalo_id?> h-100 bg-black">
									<div class="gzalo-thumbnail position-relative">
										<div class="position-absolute top-0 start-0 p-2 z-3 d-flex">
											<div class="nha88-require-content">
											<?php
											if($gzalo_content!='') {
												$gzalo_content = '<div class="copy-text mb-3">'.wp_get_the_content($gzalo_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($gzalo_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
										</div>
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $gzalo_id, 'full' ); ?></span>
										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<?php if(current_user_can('edit_gzalos')) { ?>
											<a href="<?php echo get_edit_post_link( $gzalo_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<?php } ?>
										</div>
										<div class="zalo-link position-absolute top-0 end-0 p-2">
										<?php if($gzalo_url) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($gzalo_url)?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>
									</div>
									<div class="gzalo-info text-center px-1">
										<div class="gzalo-title pt-3 mb-3 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $gzalo_id )); ?>
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
