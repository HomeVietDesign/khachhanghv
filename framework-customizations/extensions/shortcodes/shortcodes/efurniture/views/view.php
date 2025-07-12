<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_client;

$efurniture_cats = get_terms(['taxonomy' => 'efurniture_cat','parent'=>0]);
$progress = isset($_GET['progress']) ? $_GET['progress'] : '';

if($efurniture_cats && $current_client) {

	$efurniture_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'efurniture_hide', []);

	$data = fw_get_db_term_option($current_client->term_id, 'passwords', 'efurniture', []);

	?>
	<div class="fw-shortcode-efurnitures">
		<div class="accordion">
		<?php
		foreach ($efurniture_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$value->term_id?>" aria-expanded="true" aria-controls="panels-<?=$value->term_id?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$value->term_id?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$efurnitures = get_posts([
						'post_type' => 'efurniture',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'efurniture_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($efurnitures) {
						foreach($efurnitures as $efurniture_id) {
							$efurniture_content = fw_get_db_post_option($efurniture_id, 'efurniture_content');
							$default_efurniture_file = fw_get_db_post_option($efurniture_id,'efurniture_file');
							$default_url = fw_get_db_post_option($efurniture_id,'efurniture_url');
							$default_data = [
								'value' => fw_get_db_post_option($efurniture_id,'efurniture_value'),
								'unit' => fw_get_db_post_option($efurniture_id,'efurniture_unit'),
								'zalo' => fw_get_db_post_option($efurniture_id,'efurniture_zalo'),
								'file_id' => (!empty($default_efurniture_file))?$default_efurniture_file['attachment_id']:'',
							];

							$efurniture_data = isset($data[$efurniture_id])?$data[$efurniture_id]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'draw_id'=>'', 'slideshow_id'=>'', 'file_id'=>'', 'quote'=>''];

							if(empty($efurniture_data['value'])) $efurniture_data['value'] = $default_data['value'];
							if(empty($efurniture_data['unit'])) $efurniture_data['unit'] = $default_data['unit'];
							if(empty($efurniture_data['zalo'])) $efurniture_data['zalo'] = $default_data['zalo'];
							if(empty($efurniture_data['file_id'])) $efurniture_data['file_id'] = $default_data['file_id'];

							$display = empty($progress) ? true : false;

							if(!$display) {
								$display_none = true;
								if('none'==$progress) {
									$display_none = false;
									if(empty($efurniture_data['required']) && empty($efurniture_data['received']) && empty($efurniture_data['completed']) && empty($efurniture_data['sent']) && empty($efurniture_data['quote'])) {
										$display_none = true;
									}
								}

								$display_required = true;
								if('required'==$progress) {
									$display_required = false;
									if(isset($efurniture_data['required']) && $efurniture_data['required']!='' ) {
										$display_required = true;
									}
								}

								$display_received = true;
								if('received'==$progress) {
									$display_received = false;
									if(isset($efurniture_data['received']) && $efurniture_data['received']!='' ) {
										$display_received = true;
									}
								}

								$display_completed = true;
								if('completed'==$progress) {
									$display_completed = false;
									if(isset($efurniture_data['completed']) && $efurniture_data['completed']!='' ) {
										$display_completed = true;
									}
								}

								$display_sent = true;
								if('sent'==$progress) {
									$display_sent = false;
									if(isset($efurniture_data['sent']) && $efurniture_data['sent']!='' ) {
										$display_sent = true;
									}
								}

								$display_quote = true;
								if('quote'==$progress) {
									$display_quote = false;
									if(isset($efurniture_data['quote']) && $efurniture_data['quote']!='' ) {
										$display_quote = true;
									}
								}

							}

							$item_class = '';

							if(!$display && !($display_none && $display_required && $display_received && $display_completed && $display_sent && $display_quote)) {
								$item_class = ' hidden';
							}

							if(in_array($efurniture_id, $efurniture_hide)) {
								$item_class .= ' hide';
							}
							?>
							<div class="col-lg-3 col-md-6 estimate-item efurniture-item mb-4<?=$item_class?>">
								<div class="efurniture efurniture-<?=$efurniture_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar efurniture-progress text-center text-yellow">
										<div class="col estimate-required efurniture-required<?php echo (isset($efurniture_data['required']) && $efurniture_data['required']!='')?' on':''; ?>">
										<?php
										if(isset($efurniture_data['required']) && $efurniture_data['required']!='') {
											?>
											<div class="bg-danger" title="Ngày gửi yêu cầu">
												<?php echo esc_html(date('d/m', strtotime($efurniture_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col estimate-received efurniture-received<?php echo (isset($efurniture_data['received']) && $efurniture_data['received']!='')?' on':''; ?>">
											<?php
											if(isset($efurniture_data['received']) && $efurniture_data['received']!='') {
												?>
												<div class="bg-danger" title="Ngày nhận dự toán nhà thầu">
													<?php echo esc_html(date('d/m', strtotime($efurniture_data['received']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col estimate-completed efurniture-completed<?php echo (isset($efurniture_data['completed']) && $efurniture_data['completed']!='')?' on':''; ?>">
											<?php
											if(isset($efurniture_data['completed']) && $efurniture_data['completed']!='') {
												?>
												<div class="bg-danger" title="Ngày làm xong dự toán">
													<?php echo esc_html(date('d/m', strtotime($efurniture_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col estimate-sent efurniture-sent<?php echo (isset($efurniture_data['sent']) && $efurniture_data['sent']!='')?' on':''; ?>">
											<?php
											if(isset($efurniture_data['sent']) && $efurniture_data['sent']!='') {
												?>
												<div class="bg-danger" title="Ngày gửi khách">
													<?php echo esc_html(date('d/m', strtotime($efurniture_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>	
									</div>
									<div class="efurniture-thumbnail position-relative">
										<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="efurniture-require-content">
											<?php
											if(isset($efurniture_content) && $efurniture_content!='') {
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($efurniture_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="file-download">
											<?php
											if(isset($efurniture_data['file_id']) && $efurniture_data['file_id']!='') {
												$attachment_url = wp_get_attachment_url($efurniture_data['file_id']);
												if($attachment_url) {
												?>
												<a class="btn-shadow btn btn-sm btn-primary fw-bold" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
												<?php
												}
											}
											?>
											</div>
										</div>
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-top border-bottom border-dark"><?php echo get_the_post_thumbnail( $efurniture_id, 'full' ); ?></span>

										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<div class="estimate-quote efurniture-quote<?php echo (isset($efurniture_data['quote']) && $efurniture_data['quote']=='yes')?' on':''; ?>">
												<?php
												if(isset($efurniture_data['quote']) && $efurniture_data['quote']=='yes') {
													?>
													<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã chọn"><span class="dashicons dashicons-yes"></span></span>
													<?php
												}
												?>
											</div>
											<?php if(current_user_can('edit_efurnitures')) { ?>
											<button class="efurniture-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$current_client->term_id?>" data-efurniture="<?=$efurniture_id?>" data-efurniture-title="Ẩn <?php echo esc_attr('"'.get_the_title( $efurniture_id ).'" ?'); ?>"><span class="dashicons dashicons-visibility"></span></button>

											<a href="<?php echo get_edit_post_link( $efurniture_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<?php } ?>
											<?php if(current_user_can('efurniture_edit')) { ?>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-efurniture" data-client="<?=$current_client->term_id?>" data-efurniture="<?=$efurniture_id?>" data-efurniture-title="<?php echo esc_attr(get_the_title( $efurniture_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="zalo-link position-absolute top-0 end-0 p-1">
										<?php if($efurniture_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($efurniture_data['zalo'])?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>

										<div class="position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>
									</div>
									<div class="efurniture-info text-center px-1">
										<div class="efurniture-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $efurniture_id )); ?>
										</div>
										<?php if($efurniture_data['value']) { ?>
										<div class="efurniture-value mb-1">
											<span>Tổng giá trị:</span>
											<span class="text-red fw-bold"><?php echo esc_html($efurniture_data['value']); ?></span>
										</div>
										<?php } ?>
										<?php if($efurniture_data['unit']) { ?>
										<div class="efurniture-unit mb-1">
											<div class="text-red"><?php echo esc_html($efurniture_data['unit']); ?></div>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center efurniture-url mb-3">
											<?php
											if(isset($efurniture_data['url']) && $efurniture_data['url']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($efurniture_data['url'])?>" target="_blank">Xem chi tiết</a>
												<?php
											}

											if(isset($efurniture_data['draw_id']) && $efurniture_data['draw_id']) {
												?>
												<a class="btn btn-sm btn-warning my-1 mx-2" href="<?=esc_url(wp_get_attachment_url($efurniture_data['draw_id']))?>" target="_blank">Bản vẽ</a>
												<?php
											}

											if(isset($efurniture_data['slideshow_id']) && $efurniture_data['slideshow_id']) {
												?>
												<a class="btn btn-sm btn-info my-1 mx-2" href="<?=esc_url(wp_get_attachment_url($efurniture_data['slideshow_id']))?>" target="_blank">Bản thuyết trình</a>
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