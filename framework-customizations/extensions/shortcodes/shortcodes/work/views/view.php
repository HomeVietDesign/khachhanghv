<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcode = fw_ext( 'shortcodes' )->get_shortcode('work');

/**
 * @var array $atts
 */
global $current_employee;

$work_cats = get_terms(['taxonomy' => 'work_cat','parent'=>0]);

if($work_cats && $current_employee) {

	$work_show = get_term_meta($current_employee->term_id, 'work_show', true);
	if(empty($work_show)) $work_show = [];

	$work_removed = get_term_meta($current_employee->term_id, 'work_removed', true);
	if(empty($work_removed)) $work_removed = [];

	// $data = fw_get_db_term_option($current_employee->term_id, 'passwords', 'work', []); // bỏ vì gây mất dữ liệu
	$data = get_term_meta($current_employee->term_id, 'work', true);
	if(empty($data)) $data = [];

	?>
	<div class="fw-shortcode-works">
		<div class="accordion">
		<?php
		foreach ($work_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header" id="accordion-header-<?=$value->term_id?>">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$value->term_id?>" aria-expanded="true" aria-controls="panels-<?=$value->term_id?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$value->term_id?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$works = get_posts([
						'post_type' => 'work',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'work_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($works) {
						foreach($works as $work_id) {
							$default_content = fw_get_db_post_option($work_id, 'work_content');
							$default_url = fw_get_db_post_option($work_id,'work_url');
							$default_zalo = fw_get_db_post_option($work_id,'work_zalo');
							$default_data = [
								'note' => fw_get_db_post_option($work_id,'work_note'),
							];

							$work_data = isset($data[$work_id])?$data[$work_id]:$shortcode->default;
							$work_data += $shortcode->default;

							if(empty($work_data['note'])) $work_data['note'] = $default_data['note'];

							$item_class = '';

							if(in_array($work_id, $work_show)) {
								$item_class .= ' active';
							}

							if(in_array($work_id, $work_removed)) {
								$item_class .= ' removed';
							}
							?>
							<div class="col-lg-3 col-md-6 work-item work-item-<?=$work_id?> mb-4<?=$item_class?>">
								<div class="work work-<?=$work_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar work-progress text-center text-yellow">
										<div class="col work-proc1">
										<?php
										if($work_data['proc1']!='') {
											?>
											<div class="bg-danger" title="<?=esc_attr($work_data['proc1_label'])?>">
												<?php echo esc_html(date('d/m', strtotime($work_data['proc1']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col work-proc2 work-proc2">
											<?php
											if($work_data['proc2']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($work_data['proc2_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($work_data['proc2']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col work-proc3">
											<?php
											if($work_data['proc3']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($work_data['proc3_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($work_data['proc3']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col work-proc4">
											<?php
											if($work_data['proc4']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($work_data['proc4_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($work_data['proc4']))); ?>
												</div>
												<?php
											}
											?>
										</div>	
									</div>
									<div class="work-thumbnail position-relative">
										<div class="work-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="work-default-content">
											<?php
											if($default_content!='') {
												$default_content = '<div class="copy-text">'.wp_get_the_content($default_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($default_content)?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="work-content">
											<?php
										
											if($work_data['content']!='') {
												$content = '<div class="copy-text">'.wp_get_the_content($work_data['content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($content)?>" data-bs-html="true">ÁP DỤNG</button>
												<?php
											}
											?>
											</div>
											<div class="file-download">
											<?php
											if($work_data['file_id']!='') {
												$attachment_url = wp_get_attachment_url($work_data['file_id']);
												if($attachment_url) {
												?>
												<a class="btn-shadow btn btn-sm btn-primary fw-bold me-1" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
												<?php
												}
											}
											?>
											</div>
										</div>
										<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-top border-bottom border-dark">
											<?php
											if(has_post_thumbnail( $work_id )) {
												echo get_the_post_thumbnail( $work_id, 'full' );
											} else {
												?>
												<div class="thumbnail-title d-flex w-100 h-100 align-items-center text-center justify-content-center">
													<?=nl2br(esc_textarea(get_post_meta($work_id, '_thumbnail_title', true)))?>
												</div>
												<?php
											}
											?>
										</div>

										<div class="work-control position-absolute bottom-0 end-0 m-1 d-flex">

											<?php if(current_user_can('edit_works')) { ?>

											<button class="work-toggle btn btn-sm btn-warning ms-2" type="button" data-employee="<?=$current_employee->term_id?>" data-work="<?=$work_id?>" data-work-title="<?php echo esc_attr(get_the_title( $work_id )); ?>" title="<?php echo (in_array($work_id, $work_removed))?'Sử dụng':'Loại bỏ'; ?>"></button>

											<button class="work-show btn btn-sm btn-danger text-yellow ms-2" type="button" data-employee="<?=$current_employee->term_id?>" data-work="<?=$work_id?>" data-work-title="<?php echo esc_attr(get_the_title( $work_id )); ?>" title="Ẩn/Hiện"></button>

											<a href="<?php echo get_edit_post_link( $work_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
										
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-work" data-employee="<?=$current_employee->term_id?>" data-work="<?=$work_id?>" data-work-title="<?php echo esc_attr(get_the_title( $work_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="work-control zalo-link position-absolute top-0 end-0 p-1 d-flex">
										<?php if($work_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($work_data['zalo'])?>" target="_blank">RIÊNG</a>
										<?php } ?>
										<?php if($default_zalo) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($default_zalo)?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>

										<div class="work-control position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>
									</div>
									<div class="work-info text-center px-1">
										<div class="work-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $work_id )); ?>
										</div>
										<?php if($work_data['note']) { ?>
										<div class="work-note mb-1">
											<div class="text-red fw-bold"><?php echo esc_html($work_data['note']); ?></div>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center work-url1 mb-3">
											<?php
											if(isset($work_data['url1']) && $work_data['url1']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url1'])?>" target="_blank">Link 1</a>
												<?php
											}

											if(isset($work_data['url2']) && $work_data['url2']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url2'])?>" target="_blank">Link 2</a>
												<?php
											}

											if(isset($work_data['url3']) && $work_data['url3']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url3'])?>" target="_blank">Link 3</a>
												<?php
											}

											if(isset($work_data['url4']) && $work_data['url4']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url4'])?>" target="_blank">Link 4</a>
												<?php
											}

											if(isset($work_data['url5']) && $work_data['url5']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($work_data['url5'])?>" target="_blank">Link 5</a>
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
			</div>
		</section>
		<?php
		}
		?>
		</div>
	</div>
	<?php
}