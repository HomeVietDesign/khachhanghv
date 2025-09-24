<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
$design_cats = get_terms(['taxonomy' => 'design_cat','parent'=>0]);

if($design_cats) {
?>
<div class="fw-shortcode-designs">
	<div class="accordion">
		<?php
		foreach ($design_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
				<button class="accordion-button text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$designs = get_posts([
						'post_type' => 'design',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'design_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($designs) {
						foreach($designs as $design_id) {
							$design_content = fw_get_db_post_option($design_id, 'design_content');
							$design_stage_options = fw_get_db_post_option($design_id, 'design_stage_options');
							$design_estimate_link = fw_get_db_post_option($design_id, 'design_estimate_link');
							$_design_dates = fw_get_db_post_option($design_id, 'design_dates');
							
							$design_dates = [
								'date1' => [
									'title' => '',
									'date' => ''
								],
								'date2' => [
									'title' => '',
									'date' => ''
								],
								'date3' => [
									'title' => '',
									'date' => ''
								],
								'date4' => [
									'title' => '',
									'date' => ''
								]
							];

							if($_design_dates) {
								foreach ($_design_dates as $key => $value) {
									$design_dates['date'.($key+1)] = [
										'title' => $value['title'],
										'date' => $value['date']
									];
								}
							}

							?>
							<div class="col-lg-3 col-md-6 design-item mb-4">
								<div class="design design-<?=$design_id?> h-100 bg-black border border-dark">
									<div class="text-uppercase">
										<div class="row g-0 dates">
											<div class="col design-date-1 position-relative">
											<?php
											if($design_dates['date1']['date']!='') {
												$date = date('d/m/y', strtotime($design_dates['date1']['date']));
												?>
												<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($design_dates['date1']['title'])?>"><?=esc_html($date)?></span>
												<?php
											}
											?>
											</div>
											<div class="col design-date-2 position-relative">
											<?php
											if($design_dates['date2']['date']!='') {
												$date = date('d/m/y', strtotime($design_dates['date2']['date']));
												?>
												<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($design_dates['date2']['title'])?>"><?=esc_html($date)?></span>
												<?php
											}
											?>
											</div>
											<div class="col design-date-3 position-relative">
											<?php
											if($design_dates['date3']['date']!='') {
												$date = date('d/m/y', strtotime($design_dates['date3']['date']));
												?>
												<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($design_dates['date3']['title'])?>"><?=esc_html($date)?></span>
												<?php
											}
											?>
											</div>
											<div class="col design-date-4 position-relative">
											<?php
											if($design_dates['date4']['date']!='') {
												$date = date('d/m/y', strtotime($design_dates['date4']['date']));
												?>
												<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($design_dates['date4']['title'])?>"><?=esc_html($date)?></span>
												<?php
											}
											?>
											</div>
										</div>
									</div>
									<div class="design-thumbnail position-relative">
										<div class="design-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="design-require-content">
											<?php
											if(isset($design_content) && $design_content!='') {
												$design_content = '<div class="copy-text">'.wp_get_the_content($design_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($design_content)?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
										</div>

										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $design_id, 'full' ); ?></span>

										<div class="design-control position-absolute bottom-0 start-0 p-1 z-3 d-flex">
											<div class="estimate-link">
											<?php if($design_estimate_link!='') { ?>
												<a class="btn btn-sm btn-primary" href="<?=esc_url($design_estimate_link)?>" target="_blank">Dự toán</a>
											<?php } ?>
											</div>
										</div>

										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<?php if(current_user_can('edit_designs')) { ?>
											<a href="<?php echo get_edit_post_link( $design_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>

											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-designs" data-design="<?=$design_id?>" data-designs-title="<?php echo esc_attr(get_the_title( $design_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>

											<?php } ?>
										</div>
									</div>
									<div class="design-info text-center px-1">
										<?php
										if($design_stage_options) {
											?>
											<div class="design-state-options mt-3 d-flex justify-content-center align-items-center flex-wrap">
											<?php
											foreach ($design_stage_options as $op) {
												if($op['images']) {
												?>
												<div class="m-1 pswp-gallery">
													<?php
													foreach ($op['images'] as $key => $value) {
														$src_full = wp_get_attachment_image_src( $value['attachment_id'], 'full' );
														if($key==0) {
														?>
														<a class="btn btn-sm btn-primary" href="<?=esc_url($src_full[0])?>" data-pswp-width="<?=$src_full[1]?>" data-pswp-height="<?=$src_full[2]?>"><?=esc_html($op['name'])?></a>
														<?php
														} else {
														?>
														<a class="d-none" href="<?=esc_url($src_full[0])?>" data-pswp-width="<?=$src_full[1]?>" data-pswp-height="<?=$src_full[2]?>"></a>
														<?php	
														}
													}
													?>		
												</div>
												<?php
												}
											}
											?>	
											</div>
											<?php
										}
										?>
										<div class="design-title pt-3 mb-3 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $design_id )); ?>
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
