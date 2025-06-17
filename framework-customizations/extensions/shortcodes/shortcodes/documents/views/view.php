<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_client;

$document_cats = get_terms(['taxonomy' => 'document_cat','parent'=>0]);
$progress = isset($_GET['progress']) ? $_GET['progress'] : '';

if($document_cats && $current_client) {
	$document_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'document_hide', []);
	?>
	<div class="fw-shortcode-documents">
		<div class="accordion">
		<?php
		foreach ($document_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
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
							$default_attachment = fw_get_db_post_option($document_id,'document_attachment');
							$default_data = [
								'value' => fw_get_db_post_option($document_id,'document_value'),
								'unit' => fw_get_db_post_option($document_id,'document_unit'),
								'zalo' => fw_get_db_post_option($document_id,'document_zalo'),
								'attachment_id' => ($default_attachment) ? $default_attachment['attachment_id']:''
							];

							$data = get_post_meta($document_id, '_data', true);
							$document_data = isset($data[$current_client->term_id])?$data[$current_client->term_id]:[ 'required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>'', 'selected' => '' ];
							
							if(empty($document_data['value'])) $document_data['value'] = $default_data['value'];
							if(empty($document_data['unit'])) $document_data['unit'] = $default_data['unit'];
							if(empty($document_data['zalo'])) $document_data['zalo'] = $default_data['zalo'];
							if(empty($document_data['attachment_id'])) $document_data['attachment_id'] = $default_data['attachment_id'];
							
							$item_class = '';

							$display = empty($progress) ? true : false;

							if(!$display) {
								$display_none = true;
								if('none'==$progress) {
									$display_none = false;
									if(empty($document_data['required']) && empty($document_data['created']) && empty($document_data['completed']) && empty($document_data['sent']) && empty($document_data['selected'])) {
										$display_none = true;
									}
								}

								$display_required = true;
								if('required'==$progress) {
									$display_required = false;
									if(isset($document_data['required']) && $document_data['required']!='' ) {
										$display_required = true;
									}
								}

								$display_created = true;
								if('created'==$progress) {
									$display_created = false;
									if(isset($document_data['created']) && $document_data['created']!='' ) {
										$display_created = true;
									}
								}

								$display_completed = true;
								if('completed'==$progress) {
									$display_completed = false;
									if(isset($document_data['completed']) && $document_data['completed']!='' ) {
										$display_completed = true;
									}
								}

								$display_sent = true;
								if('sent'==$progress) {
									$display_sent = false;
									if(isset($document_data['sent']) && $document_data['sent']!='' ) {
										$display_sent = true;
									}
								}

								$display_selected = true;
								if('selected'==$progress) {
									$display_selected = false;
									if(isset($document_data['selected']) && $document_data['selected']!='' ) {
										$display_selected = true;
									}
								}

							}
							
							$item_class = '';

							if(!$display) {
								if( !($display_none && $display_required && $display_created && $display_completed && $display_sent && $display_selected )) {
									$item_class .= ' hidden';
								}
							}

							if(in_array($document_id, $document_hide)) {
								$item_class .= ' hide';
							}
							?>
							<div class="col-lg-3 col-md-6 document-item mb-4<?=$item_class?>">
								<div class="document document-<?=$document_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar document-progress text-center text-yellow">
										<div class="col document-required<?php echo (isset($document_data['required']) && $document_data['required']!='')?' on':''; ?>">
										<?php
										if(isset($document_data['required']) && $document_data['required']!='') {
											?>
											<div class="bg-danger" title="Ngày gửi yêu cầu">
												<?php echo esc_html(date('d/m', strtotime($document_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col document-created<?php echo (isset($document_data['created']) && $document_data['created']!='')?' on':''; ?>">
											<?php
											if(isset($document_data['created']) && $document_data['created']!='') {
												?>
												<div class="bg-danger" title="Ngày bắt đầu">
													<?php echo esc_html(date('d/m', strtotime($document_data['created']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col document-completed<?php echo (isset($document_data['completed']) && $document_data['completed']!='')?' on':''; ?>">
											<?php
											if(isset($document_data['completed']) && $document_data['completed']!='') {
												?>
												<div class="bg-danger" title="Ngày làm xong">
													<?php echo esc_html(date('d/m', strtotime($document_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col document-sent<?php echo (isset($document_data['sent']) && $document_data['sent']!='')?' on':''; ?>">
											<?php
											if(isset($document_data['sent']) && $document_data['sent']!='') {
												?>
												<div class="bg-danger" title="Ngày gửi cho khách">
													<?php echo esc_html(date('d/m', strtotime($document_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<div class="document-thumbnail position-relative">
										<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="document-require-content">
											<?php
											if(isset($document_content) && $document_content!='') {
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($document_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
										</div>

										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $document_id, 'full' ); ?></span>
										
										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<div class="document-selected<?php echo (isset($document_data['selected']) && $document_data['selected']=='yes')?' on':''; ?>">
												<?php
												if(isset($document_data['selected']) && $document_data['selected']=='yes') {
													?>
													<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã ký"><span class="dashicons dashicons-yes"></span></span>
													<?php
												}
												?>
											</div>
											<?php if(current_user_can('edit_documents')) { ?>
											
											<button class="document-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$current_client->term_id?>" data-document="<?=$document_id?>" data-document-title="Ẩn hồ sơ <?php echo esc_attr('"'.get_the_title( $document_id ).'" ?'); ?>"><span class="dashicons dashicons-visibility"></span></button>
											
											<a href="<?php echo get_edit_post_link( $document_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>

											<?php } ?>

											<?php if(current_user_can('document_edit')) { ?>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-document" data-client="<?=$current_client->term_id?>" data-document="<?=$document_id?>" data-document-title="<?php echo esc_attr(get_the_title( $document_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>

										</div>
										
										<div class="zalo-link position-absolute top-0 end-0 p-1">
										<?php if($document_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($document_data['zalo'])?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>
									</div>
									<div class="document-info text-center px-1">
										<div class="document-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $document_id )); ?>
										</div>
										<?php if($document_data['value']!='' || $document_data['unit']!='') { ?>
										<div class="document-value mb-1">
											<?php if($document_data['value']!='') { ?>
											<div>
												<span>Tổng giá trị: </span>
												<span class="text-red fw-bold"><?php echo esc_html($document_data['value']); ?></span>
											</div>
											<?php } ?>
											<?php if($document_data['unit']!='') { ?>
											<div class="text-red"><?php echo esc_html($document_data['unit']); ?></div>
											<?php } ?>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center document-links mb-3">
											<?php
											if($document_data['attachment_id']) {
												$attachment_url = wp_get_attachment_url($document_data['attachment_id']);
												if($attachment_url) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($attachment_url)?>" target="_blank">Xem chi tiết</a>
												<?php
												}
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