<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_client;

$econstruction_cats = get_terms(['taxonomy' => 'econstruction_cat','parent'=>0]);
$progress = isset($_GET['progress']) ? $_GET['progress'] : '';

if($econstruction_cats && $current_client) {

	$econstruction_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'econstruction_hide', []);

	$data = fw_get_db_term_option($current_client->term_id, 'passwords', 'econstruction', []);

	?>
	<div class="fw-shortcode-econstructions">
		<div class="accordion">
		<?php
		foreach ($econstruction_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$econstructions = get_posts([
						'post_type' => 'econstruction',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'econstruction_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($econstructions) {
						foreach($econstructions as $econstruction_id) {
							$econstruction_content = fw_get_db_post_option($econstruction_id, 'econstruction_content');
							$default_econstruction_file = fw_get_db_post_option($econstruction_id,'econstruction_file');
							$default_url = fw_get_db_post_option($econstruction_id,'econstruction_url');
							$default_data = [
								'value' => fw_get_db_post_option($econstruction_id,'econstruction_value'),
								'unit' => fw_get_db_post_option($econstruction_id,'econstruction_unit'),
								'zalo' => fw_get_db_post_option($econstruction_id,'econstruction_zalo'),
								'file_id' => (!empty($default_econstruction_file))?$default_econstruction_file['attachment_id']:'',
							];

							$econstruction_data = isset($data[$econstruction_id])?$data[$econstruction_id]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

							if(empty($econstruction_data['value'])) $econstruction_data['value'] = $default_data['value'];
							if(empty($econstruction_data['unit'])) $econstruction_data['unit'] = $default_data['unit'];
							if(empty($econstruction_data['zalo'])) $econstruction_data['zalo'] = $default_data['zalo'];
							if(empty($econstruction_data['file_id'])) $econstruction_data['file_id'] = $default_data['file_id'];

							$display = empty($progress) ? true : false;

							if(!$display) {
								$display_none = true;
								if('none'==$progress) {
									$display_none = false;
									if(empty($econstruction_data['required']) && empty($econstruction_data['received']) && empty($econstruction_data['completed']) && empty($econstruction_data['sent']) && empty($econstruction_data['quote'])) {
										$display_none = true;
									}
								}

								$display_required = true;
								if('required'==$progress) {
									$display_required = false;
									if(isset($econstruction_data['required']) && $econstruction_data['required']!='' ) {
										$display_required = true;
									}
								}

								$display_received = true;
								if('received'==$progress) {
									$display_received = false;
									if(isset($econstruction_data['received']) && $econstruction_data['received']!='' ) {
										$display_received = true;
									}
								}

								$display_completed = true;
								if('completed'==$progress) {
									$display_completed = false;
									if(isset($econstruction_data['completed']) && $econstruction_data['completed']!='' ) {
										$display_completed = true;
									}
								}

								$display_sent = true;
								if('sent'==$progress) {
									$display_sent = false;
									if(isset($econstruction_data['sent']) && $econstruction_data['sent']!='' ) {
										$display_sent = true;
									}
								}

								$display_quote = true;
								if('quote'==$progress) {
									$display_quote = false;
									if(isset($econstruction_data['quote']) && $econstruction_data['quote']!='' ) {
										$display_quote = true;
									}
								}

							}

							$item_class = '';

							if(!$display && !($display_none && $display_required && $display_received && $display_completed && $display_sent && $display_quote)) {
								$item_class = ' hidden';
							}

							if(in_array($econstruction_id, $econstruction_hide)) {
								$item_class .= ' hide';
							}
							?>
							<div class="col-lg-3 col-md-6 econstruction-item mb-4<?=$item_class?>">
								<div class="econstruction econstruction-<?=$econstruction_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar econstruction-progress text-center text-yellow">
										<div class="col econstruction-required<?php echo (isset($econstruction_data['required']) && $econstruction_data['required']!='')?' on':''; ?>">
										<?php
										if(isset($econstruction_data['required']) && $econstruction_data['required']!='') {
											?>
											<div class="bg-danger" title="Ngày gửi yêu cầu">
												<?php echo esc_html(date('d/m', strtotime($econstruction_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col econstruction-received<?php echo (isset($econstruction_data['received']) && $econstruction_data['received']!='')?' on':''; ?>">
											<?php
											if(isset($econstruction_data['received']) && $econstruction_data['received']!='') {
												?>
												<div class="bg-danger" title="Ngày nhận dự toán nhà thầu">
													<?php echo esc_html(date('d/m', strtotime($econstruction_data['received']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col econstruction-completed<?php echo (isset($econstruction_data['completed']) && $econstruction_data['completed']!='')?' on':''; ?>">
											<?php
											if(isset($econstruction_data['completed']) && $econstruction_data['completed']!='') {
												?>
												<div class="bg-danger" title="Ngày làm xong dự toán">
													<?php echo esc_html(date('d/m', strtotime($econstruction_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col econstruction-sent<?php echo (isset($econstruction_data['sent']) && $econstruction_data['sent']!='')?' on':''; ?>">
											<?php
											if(isset($econstruction_data['sent']) && $econstruction_data['sent']!='') {
												?>
												<div class="bg-danger" title="Ngày gửi khách">
													<?php echo esc_html(date('d/m', strtotime($econstruction_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>	
									</div>
									<div class="econstruction-thumbnail position-relative">
										<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="econstruction-require-content">
											<?php
											if(isset($econstruction_content) && $econstruction_content!='') {
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($econstruction_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
											<div class="file-download">
											<?php
											if(isset($econstruction_data['file_id']) && $econstruction_data['file_id']!='') {
												$attachment_url = wp_get_attachment_url($econstruction_data['file_id']);
												if($attachment_url) {
												?>
												<a class="btn-shadow btn btn-sm btn-primary fw-bold" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
												<?php
												}
											}
											?>
											</div>
										</div>
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-top border-bottom border-dark"><?php echo get_the_post_thumbnail( $econstruction_id, 'full' ); ?></span>

										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<div class="econstruction-quote<?php echo (isset($econstruction_data['quote']) && $econstruction_data['quote']=='yes')?' on':''; ?>">
												<?php
												if(isset($econstruction_data['quote']) && $econstruction_data['quote']=='yes') {
													?>
													<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã chọn"><span class="dashicons dashicons-yes"></span></span>
													<?php
												}
												?>
											</div>
											<?php if(current_user_can('edit_econstructions')) { ?>
											<button class="econstruction-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$current_client->term_id?>" data-econstruction="<?=$econstruction_id?>" data-econstruction-title="Ẩn <?php echo esc_attr('"'.get_the_title( $econstruction_id ).'" ?'); ?>"><span class="dashicons dashicons-visibility"></span></button>

											<a href="<?php echo get_edit_post_link( $econstruction_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<?php } ?>
											<?php if(current_user_can('econstruction_edit')) { ?>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-econstruction" data-client="<?=$current_client->term_id?>" data-econstruction="<?=$econstruction_id?>" data-econstruction-title="<?php echo esc_attr(get_the_title( $econstruction_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="zalo-link position-absolute top-0 end-0 p-1">
										<?php if($econstruction_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($econstruction_data['zalo'])?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>

										<div class="position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>
									</div>
									<div class="econstruction-info text-center px-1">
										<div class="econstruction-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $econstruction_id )); ?>
										</div>
										<?php if($econstruction_data['value']) { ?>
										<div class="econstruction-value mb-1">
											<span>Tổng giá trị:</span>
											<span class="text-red fw-bold"><?php echo esc_html($econstruction_data['value']); ?></span>
										</div>
										<?php } ?>
										<?php if($econstruction_data['unit']) { ?>
										<div class="econstruction-unit mb-1">
											<div class="text-red"><?php echo esc_html($econstruction_data['unit']); ?></div>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center econstruction-url mb-3">
											<?php
											if($econstruction_data['url']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($econstruction_data['url'])?>" target="_blank">Xem chi tiết</a>
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