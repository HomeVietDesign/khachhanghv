<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcode = fw_ext( 'shortcodes' )->get_shortcode('gzalo');

/**
 * @var array $atts
 */

$nha88_cats = get_terms(['taxonomy' => 'nha88_cat','parent'=>0]);

if($nha88_cats) {

	?>
	<div class="fw-shortcode-nha88s">
		<div class="accordion">
		<?php
		foreach ($nha88_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header" id="accordion-header-<?=$value->term_id?>">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$nha88s = get_posts([
						'post_type' => 'nha88',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'nha88_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($nha88s) {
						foreach($nha88s as $nha88_id) {
							$nha88_content = fw_get_db_post_option($nha88_id, 'nha88_content');
							$nha88_value = fw_get_db_post_option($nha88_id,'nha88_value');
							$nha88_note = fw_get_db_post_option($nha88_id,'nha88_note');
							$nha88_zalo = fw_get_db_post_option($nha88_id,'nha88_zalo');
							$nha88_url = fw_get_db_post_option($nha88_id,'nha88_url');
							
							$_nha88_dates = fw_get_db_post_option($nha88_id, 'nha88_dates');
							
							$nha88_dates = [
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

							if($_nha88_dates) {
								foreach ($_nha88_dates as $key => $value) {
									$nha88_dates['date'.($key+1)] = [
										'title' => $value['title'],
										'date' => $value['date']
									];
								}
							}

							$nha88_data = get_post_meta($nha88_id, '_data', true);
							if(empty($nha88_data)) $nha88_data = $shortcode->default;
							$nha88_data += $shortcode->default;

							?>
							<div class="col-lg-3 col-md-6 nha88-item mb-4">
								<div class="nha88 nha88-<?=$nha88_id?> border border-dark h-100 bg-black">
									<div class="row g-0 dates">
										<div class="col nha88-date-1 position-relative">
										<?php
										if($nha88_dates['date1']['date']!='') {
											$date = date('d/m/y', strtotime($nha88_dates['date1']['date']));
											?>
											<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($nha88_dates['date1']['title'])?>"><?=esc_html($date)?></span>
											<?php
										}
										?>
										</div>
										<div class="col nha88-date-2 position-relative">
										<?php
										if($nha88_dates['date2']['date']!='') {
											$date = date('d/m/y', strtotime($nha88_dates['date2']['date']));
											?>
											<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($nha88_dates['date2']['title'])?>"><?=esc_html($date)?></span>
											<?php
										}
										?>
										</div>
										<div class="col nha88-date-3 position-relative">
										<?php
										if($nha88_dates['date3']['date']!='') {
											$date = date('d/m/y', strtotime($nha88_dates['date3']['date']));
											?>
											<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($nha88_dates['date3']['title'])?>"><?=esc_html($date)?></span>
											<?php
										}
										?>
										</div>
										<div class="col nha88-date-4 position-relative">
										<?php
										if($nha88_dates['date4']['date']!='') {
											$date = date('d/m/y', strtotime($nha88_dates['date4']['date']));
											?>
											<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($nha88_dates['date4']['title'])?>"><?=esc_html($date)?></span>
											<?php
										}
										?>
										</div>
									</div>
									<div class="nha88-thumbnail position-relative">
										<div class="nha88-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="nha88-require-content">
											<?php
											if(isset($nha88_content) && $nha88_content!='') {
												$nha88_content = '<div class="copy-text mb-3">'.wp_get_the_content($nha88_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($nha88_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="required-content">
											<?php
											if($nha88_data['required_content']!='') {
												$required_content = '<div class="copy-text">'.wp_get_the_content($nha88_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
												<?php
											}
											?>
											</div>
										</div>
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $nha88_id, 'full' ); ?></span>

										<div class="nha88-control nha88-url position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($nha88_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($nha88_url)?>" target="_blank">Chi tiết</a>
											<?php } ?>
										</div>

										<div class="nha88-control position-absolute bottom-0 end-0 m-1 d-flex">
											
											<?php if(current_user_can('edit_nha88s')) { ?>

											<a href="<?php echo get_edit_post_link( $nha88_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
										
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-nha88" data-nha88="<?=$nha88_id?>" data-nha88-title="<?php echo esc_attr(get_the_title( $nha88_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>

											<?php } ?>

										</div>
										
										<div class="nha88-control nha88-zalo zalo-link position-absolute top-0 end-0 p-1 d-flex">
										<?php if($nha88_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($nha88_data['zalo'])?>" target="_blank">RIÊNG</a>
										<?php } ?>
										<?php if($nha88_zalo) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($nha88_zalo)?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>
									</div>
									<div class="nha88-info text-center px-1 pb-2">
										<div class="nha88-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $nha88_id )); ?>
										</div>
										<?php if($nha88_value!='') { ?>
										<div class="nha88-value mb-1">
											<span class="text-red fw-bold"><?php echo esc_html($nha88_value); ?></span>
										</div>
										<?php } ?>

										<?php if($nha88_note!='') { ?>
										<div class="nha88-note mb-1">
											<span class="text-red fw-bold"><?php echo esc_html($nha88_note); ?></span>
										</div>
										<?php } ?>
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