<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcode = fw_ext( 'shortcodes' )->get_shortcode('investment');

/**
 * @var array $atts
 */
global $current_investor;

$investment_cats = get_terms(['taxonomy' => 'investment_cat','parent'=>0]);

if($investment_cats && $current_investor) {

	$investment_show = get_term_meta($current_investor->term_id, 'investment_show', true);
	if(empty($investment_show)) $investment_show = [];

	$investment_removed = get_term_meta($current_investor->term_id, 'investment_removed', true);
	if(empty($investment_removed)) $investment_removed = [];

	// $data = fw_get_db_term_option($current_investor->term_id, 'passwords', 'investment', []); // bỏ vì gây mất dữ liệu
	$data = get_term_meta($current_investor->term_id, 'investment', true);
	if(empty($data)) $data = [];

	?>
	<div class="fw-shortcode-investments">
		<div class="accordion">
		<?php
		foreach ($investment_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header" id="accordion-header-<?=$value->term_id?>">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$value->term_id?>" aria-expanded="true" aria-controls="panels-<?=$value->term_id?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$value->term_id?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$investments = get_posts([
						'post_type' => 'investment',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'investment_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($investments) {
						foreach($investments as $investment_id) {
							$default_content = fw_get_db_post_option($investment_id, 'investment_content');
							$default_url = fw_get_db_post_option($investment_id,'investment_url');
							$default_zalo = fw_get_db_post_option($investment_id,'investment_zalo');
							$default_data = [
								'note' => fw_get_db_post_option($investment_id,'investment_note'),
							];

							$investment_data = isset($data[$investment_id])?$data[$investment_id]:$shortcode->default;
							$investment_data += $shortcode->default;

							if(empty($investment_data['note'])) $investment_data['note'] = $default_data['note'];

							$item_class = '';

							if(in_array($investment_id, $investment_show)) {
								$item_class .= ' active';
							}

							if(in_array($investment_id, $investment_removed)) {
								$item_class .= ' removed';
							}
							?>
							<div class="col-lg-3 col-md-6 investment-item investment-item-<?=$investment_id?> mb-4<?=$item_class?>">
								<div class="investment investment-<?=$investment_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar investment-progress text-center text-yellow">
										<div class="col investment-proc1">
										<?php
										if($investment_data['proc1']!='') {
											?>
											<div class="bg-danger" title="<?=esc_attr($investment_data['proc1_label'])?>">
												<?php echo esc_html(date('d/m', strtotime($investment_data['proc1']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col investment-proc2 investment-proc2">
											<?php
											if($investment_data['proc2']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($investment_data['proc2_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($investment_data['proc2']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col investment-proc3">
											<?php
											if($investment_data['proc3']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($investment_data['proc3_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($investment_data['proc3']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col investment-proc4">
											<?php
											if($investment_data['proc4']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($investment_data['proc4_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($investment_data['proc4']))); ?>
												</div>
												<?php
											}
											?>
										</div>	
									</div>
									<div class="investment-thumbnail position-relative">
										<div class="investment-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="investment-default-content">
											<?php
											if($default_content!='') {
												$default_content = '<div class="copy-text">'.wp_get_the_content($default_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr($default_content)?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="investment-content">
											<?php
										
											if($investment_data['content']!='') {
												$content = '<div class="copy-text">'.wp_get_the_content($investment_data['content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($content)?>" data-bs-html="true">ÁP DỤNG</button>
												<?php
											}
											?>
											</div>
											<div class="file-download">
											<?php
											if($investment_data['file_id']!='') {
												$attachment_url = wp_get_attachment_url($investment_data['file_id']);
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
											if(has_post_thumbnail( $investment_id )) {
												echo get_the_post_thumbnail( $investment_id, 'full' );
											} else {
												?>
												<div class="thumbnail-title d-flex w-100 h-100 align-items-center text-center justify-content-center">
													<?=nl2br(esc_textarea(get_post_meta($investment_id, '_thumbnail_title', true)))?>
												</div>
												<?php
											}
											?>
										</div>

										<div class="investment-control position-absolute bottom-0 end-0 m-1 d-flex">

											<?php if(current_user_can('edit_investments')) { ?>

											<button class="investment-toggle btn btn-sm btn-warning ms-2" type="button" data-investor="<?=$current_investor->term_id?>" data-investment="<?=$investment_id?>" data-investment-title="<?php echo esc_attr(get_the_title( $investment_id )); ?>" title="<?php echo (in_array($investment_id, $investment_removed))?'Sử dụng':'Loại bỏ'; ?>"></button>

											<button class="investment-show btn btn-sm btn-danger text-yellow ms-2" type="button" data-investor="<?=$current_investor->term_id?>" data-investment="<?=$investment_id?>" data-investment-title="<?php echo esc_attr(get_the_title( $investment_id )); ?>" title="Ẩn/Hiện"></button>

											<a href="<?php echo get_edit_post_link( $investment_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
										
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-investment" data-investor="<?=$current_investor->term_id?>" data-investment="<?=$investment_id?>" data-investment-title="<?php echo esc_attr(get_the_title( $investment_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="investment-control zalo-link position-absolute top-0 end-0 p-1 d-flex">
										<?php if($investment_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($investment_data['zalo'])?>" target="_blank">RIÊNG</a>
										<?php } ?>
										<?php if($default_zalo) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($default_zalo)?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>

										<div class="investment-control position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>
									</div>
									<div class="investment-info text-center px-1">
										<div class="investment-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $investment_id )); ?>
										</div>
										<?php if($investment_data['note']) { ?>
										<div class="investment-note mb-1">
											<div class="text-red fw-bold"><?php echo esc_html($investment_data['note']); ?></div>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center investment-url1 mb-3">
											<?php
											if(isset($investment_data['url1']) && $investment_data['url1']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($investment_data['url1'])?>" target="_blank">Link 1</a>
												<?php
											}

											if(isset($investment_data['url2']) && $investment_data['url2']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($investment_data['url2'])?>" target="_blank">Link 2</a>
												<?php
											}

											if(isset($investment_data['url3']) && $investment_data['url3']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($investment_data['url3'])?>" target="_blank">Link 3</a>
												<?php
											}

											if(isset($investment_data['url4']) && $investment_data['url4']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($investment_data['url4'])?>" target="_blank">Link 4</a>
												<?php
											}

											if(isset($investment_data['url5']) && $investment_data['url5']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-1" href="<?=esc_url($investment_data['url5'])?>" target="_blank">Link 5</a>
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