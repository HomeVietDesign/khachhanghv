<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcode = fw_ext( 'shortcodes' )->get_shortcode('gzalo');

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
			<h2 class="accordion-header" id="accordion-header-<?=$value->term_id?>">
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
							$gzalo_content = fw_get_db_post_option($gzalo_id, 'gzalo_content');
							$default_zalo = fw_get_db_post_option($gzalo_id, 'gzalo_zalo');

							$gzalo_data = get_post_meta($gzalo_id, '_data', true);
							if(empty($gzalo_data)) $gzalo_data = $shortcode->default;
							$gzalo_data += $shortcode->default;
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
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($gzalo_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="required-content">
											<?php
											if($gzalo_data['required_content']!='') {
												$required_content = '<div class="copy-text">'.wp_get_the_content($gzalo_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
												<?php
											}
											?>
											</div>
										</div>
										<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark">
											<?php
											if(has_post_thumbnail( $gzalo_id )) {
												echo get_the_post_thumbnail( $gzalo_id, 'full' );
											} else {
												?>
												<div class="thumbnail-title d-flex w-100 h-100 align-items-center text-center justify-content-center">
													<?=nl2br(esc_textarea(get_post_meta($gzalo_id, '_thumbnail_title', true)))?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<?php if(current_user_can('edit_gzalos')) { ?>
											<a href="<?php echo get_edit_post_link( $gzalo_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-gzalo" data-gzalo="<?=$gzalo_id?>" data-gzalo-title="<?php echo esc_attr(get_the_title( $gzalo_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										<div class="zalo-link position-absolute top-0 end-0 p-2 d-flex justify-content-end">
										<?php if($gzalo_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($gzalo_data['zalo'])?>" target="_blank">RIÊNG</a>
										<?php } ?>
										<?php if($default_zalo) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($default_zalo)?>" target="_blank">Zalo</a>
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
