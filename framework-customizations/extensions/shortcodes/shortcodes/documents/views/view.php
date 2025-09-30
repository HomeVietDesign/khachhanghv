<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcode = fw_ext( 'shortcodes' )->get_shortcode('documents');

/**
 * @var array $atts
 */
global $current_client;

$document_cats = get_terms(['taxonomy' => 'document_cat','parent'=>0]);

//debug($shortcode);

if($document_cats && $current_client) {
	$document_hide = get_term_meta($current_client->term_id, 'document_hide', true);
	if(empty($document_hide)) $document_hide = [];
	?>
	<div class="fw-shortcode-documents">
		<div class="accordion">
		<?php
		foreach ($document_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header" id="accordion-header-<?=$value->term_id?>">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$documents = get_posts([
						'post_type' => 'document',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'document_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($documents) {
						foreach($documents as $document_id) {
							$document_content = fw_get_db_post_option($document_id, 'document_content');
							$default_zalo = fw_get_db_post_option($document_id, 'document_zalo');

							// url dự toán gốc
							$default_url = fw_get_db_post_option($document_id, 'document_default_url');

							$data = get_post_meta($document_id, '_data', true);
							$document_data = isset($data[$current_client->term_id])?$data[$current_client->term_id]:$shortcode->default;
							$document_data += $shortcode->default;
							
							$item_class = '';

							if(in_array($document_id, $document_hide)) {
								$item_class .= ' active';
							}
							?>
							<div class="col-lg-3 col-md-6 document-item mb-4<?=$item_class?>">
								<div class="document document-<?=$document_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar document-progress text-center text-yellow">
										<div class="col document-required">
										<?php
										if($document_data['required']!='') {
											?>
											<div class="bg-danger" title="<?=esc_attr($document_data['required_label'])?>">
												<?php echo esc_html(date('d/m', strtotime($document_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col document-created">
											<?php
											if($document_data['created']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($document_data['created_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($document_data['created']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col document-completed">
											<?php
											if($document_data['completed']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($document_data['completed_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($document_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col document-sent">
											<?php
											if($document_data['sent']!='') {
												?>
												<div class="bg-danger" title="<?=esc_attr($document_data['sent_label'])?>">
													<?php echo esc_html(date('d/m', strtotime($document_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<div class="document-thumbnail position-relative">
										<div class="document-control position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="document-require-content">
											<?php
											if($document_content!='') {
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($document_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="required-content">
											<?php
											if($document_data['required_content']!='') {
												$required_content = '<div class="copy-text">'.wp_get_the_content($document_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
												<?php
											}
											?>
											</div>
											<div class="attachment-download">
											<?php
											if($document_data['attachment_id']!='') {
												$attachment_url = wp_get_attachment_url($document_data['attachment_id']);
												if($attachment_url) {
												?>
												<a class="btn-shadow btn btn-sm btn-primary fw-bold me-1" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
												<?php
												}
											}
											?>
											</div>
										</div>

										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $document_id, 'full' ); ?></span>
										
										<div class="document-control position-absolute bottom-0 start-0 m-1 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>

										<div class="document-control position-absolute bottom-0 end-0 m-1 d-flex">
											<div class="document-selected">
												<?php
												if($document_data['selected']=='yes') {
													?>
													<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã ký"><span class="dashicons dashicons-yes"></span></span>
													<?php
												}
												?>
											</div>
											<?php if(current_user_can('edit_documents')) { ?>

											<button class="document-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$current_client->term_id?>" data-document="<?=$document_id?>" data-document-title="<?php echo esc_attr(get_the_title( $document_id )); ?>" title="Ẩn/Hiện"></button>
											
											<a href="<?php echo get_edit_post_link( $document_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>

											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-document" data-client="<?=$current_client->term_id?>" data-document="<?=$document_id?>" data-document-title="<?php echo esc_attr(get_the_title( $document_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>

										</div>
										
										<div class="document-control zalo-link position-absolute top-0 end-0 p-1 d-flex">
										<?php if($document_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($document_data['zalo'])?>" target="_blank">RIÊNG</a>
										<?php } ?>
										<?php if($default_zalo) { ?>
											<a class="btn btn-sm btn-shadow fw-bold ms-1" href="<?=esc_url($default_zalo)?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>
									</div>
									<div class="document-info text-center px-1">
										<div class="document-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $document_id )); ?>
										</div>
										
										<div class="d-flex flex-wrap justify-content-center mb-3">
											<?php
											if($document_data['link']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($document_data['link'])?>" target="_blank">Xem chi tiết</a>
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