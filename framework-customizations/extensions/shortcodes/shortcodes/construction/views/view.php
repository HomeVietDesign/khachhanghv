<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
$construction_cats = get_terms(['taxonomy' => 'construction_cat','parent'=>0]);

if($construction_cats) {
?>
<div class="fw-shortcode-constructions">
	<div class="accordion">
		<?php
		foreach ($construction_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header" id="accordion-header-<?=$value->term_id?>">
				<button class="accordion-button text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$constructions = get_posts([
						'post_type' => 'construction',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'construction_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($constructions) {
						foreach($constructions as $construction_id) {
							$construction_content = fw_get_db_post_option($construction_id, 'construction_content');
							$construction_stage_options = fw_get_db_post_option($construction_id, 'construction_stage_options');
							$construction_estimate_link = fw_get_db_post_option($construction_id, 'construction_estimate_link');
							$_construction_dates = fw_get_db_post_option($construction_id, 'construction_dates');
							
							$construction_dates = [
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

							if($_construction_dates) {
								foreach ($_construction_dates as $key => $value) {
									$construction_dates['date'.($key+1)] = [
										'title' => $value['title'],
										'date' => $value['date']
									];
								}
							}

							?>
							<div class="col-lg-3 col-md-6 construction-item mb-4">
								<div class="construction construction-<?=$construction_id?> h-100 bg-black border border-dark">
									<div class="text-uppercase">
										<div class="row g-0 dates">
											<div class="col construction-date-1 position-relative">
											<?php
											if($construction_dates['date1']['date']!='') {
												$date = date('d/m/y', strtotime($construction_dates['date1']['date']));
												?>
												<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($construction_dates['date1']['title'])?>"><?=esc_html($date)?></span>
												<?php
											}
											?>
											</div>
											<div class="col construction-date-2 position-relative">
											<?php
											if($construction_dates['date2']['date']!='') {
												$date = date('d/m/y', strtotime($construction_dates['date2']['date']));
												?>
												<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($construction_dates['date2']['title'])?>"><?=esc_html($date)?></span>
												<?php
											}
											?>
											</div>
											<div class="col construction-date-3 position-relative">
											<?php
											if($construction_dates['date3']['date']!='') {
												$date = date('d/m/y', strtotime($construction_dates['date3']['date']));
												?>
												<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($construction_dates['date3']['title'])?>"><?=esc_html($date)?></span>
												<?php
											}
											?>
											</div>
											<div class="col construction-date-4 position-relative">
											<?php
											if($construction_dates['date4']['date']!='') {
												$date = date('d/m/y', strtotime($construction_dates['date4']['date']));
												?>
												<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($construction_dates['date4']['title'])?>"><?=esc_html($date)?></span>
												<?php
											}
											?>
											</div>
										</div>
									</div>
									<div class="construction-thumbnail position-relative">
										<div class="construction-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="construction-require-content">
											<?php
											if(isset($construction_content) && $construction_content!='') {
												$construction_content = '<div class="copy-text">'.wp_get_the_content($construction_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($construction_content)?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
										</div>

										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $construction_id, 'full' ); ?></span>

										<div class="construction-control position-absolute bottom-0 start-0 p-1 z-3 d-flex">
											<div class="estimate-link">
											<?php if($construction_estimate_link!='') { ?>
												<a class="btn btn-sm btn-primary" href="<?=esc_url($construction_estimate_link)?>" target="_blank">Dự toán</a>
											<?php } ?>
											</div>
										</div>

										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<?php if(current_user_can('edit_constructions')) { ?>
											<a href="<?php echo get_edit_post_link( $construction_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>

											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-constructions" data-construction="<?=$construction_id?>" data-constructions-title="<?php echo esc_attr(get_the_title( $construction_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>

											<?php } ?>
										</div>
									</div>
									<div class="construction-info text-center px-1">
										<?php
										if($construction_stage_options) {
											?>
											<div class="construction-state-options mt-3 d-flex justify-content-center align-items-center flex-wrap">
											<?php
											foreach ($construction_stage_options as $op) {
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
										<div class="construction-title pt-3 mb-3 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $construction_id )); ?>
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
