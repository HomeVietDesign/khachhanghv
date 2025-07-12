<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_client;

$gzalo_cats = get_terms(['taxonomy' => 'gzalo_cat','parent'=>0]);
$progress = isset($_GET['progress']) ? $_GET['progress'] : '';

if($gzalo_cats && $current_client) {

	$gzalo_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'gzalo_hide', []);

	?>
	<div class="fw-shortcode-gzalos">
		<div class="accordion">
		<?php
		foreach ($gzalo_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
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
							$default_url = fw_get_db_post_option($gzalo_id,'gzalo_url');
							$default_data = [
								'value' => fw_get_db_post_option($gzalo_id,'gzalo_value'),
								'unit' => fw_get_db_post_option($gzalo_id,'gzalo_unit'),
								'zalo' => fw_get_db_post_option($gzalo_id,'gzalo_zalo'),
							];

							$data = get_post_meta($gzalo_id, '_data', true);
							$gzalo_data = isset($data[$current_client->term_id])?$data[$current_client->term_id]:[ 'required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'signed'=>''];
							
							if(empty($gzalo_data['value'])) $gzalo_data['value'] = $default_data['value'];
							if(empty($gzalo_data['unit'])) $gzalo_data['unit'] = $default_data['unit'];
							if(empty($gzalo_data['zalo'])) $gzalo_data['zalo'] = $default_data['zalo'];

							$display = empty($progress) ? true : false;

							if(!$display) {
								$display_none = true;
								if('none'==$progress) {
									$display_none = false;
									if(empty($gzalo_data['required']) && empty($gzalo_data['created']) && empty($gzalo_data['completed']) && empty($gzalo_data['sent']) && empty($gzalo_data['signed'])) {
										$display_none = true;
									}
								}

								$display_required = true;
								if('required'==$progress) {
									$display_required = false;
									if(isset($gzalo_data['required']) && $gzalo_data['required']!='' ) {
										$display_required = true;
									}
								}

								$display_created = true;
								if('created'==$progress) {
									$display_created = false;
									if(isset($gzalo_data['created']) && $gzalo_data['created']!='' ) {
										$display_created = true;
									}
								}

								$display_completed = true;
								if('completed'==$progress) {
									$display_completed = false;
									if(isset($gzalo_data['completed']) && $gzalo_data['completed']!='' ) {
										$display_completed = true;
									}
								}

								$display_sent = true;
								if('sent'==$progress) {
									$display_sent = false;
									if(isset($gzalo_data['sent']) && $gzalo_data['sent']!='' ) {
										$display_sent = true;
									}
								}

								$display_signed = true;
								if('signed'==$progress) {
									$display_signed = false;
									if(isset($gzalo_data['signed']) && $gzalo_data['signed']!='' ) {
										$display_signed = true;
									}
								}

							}
							
							$item_class = '';

							if(!$display) {
								if( !($display_none && $display_required && $display_created && $display_completed && $display_sent && $display_signed )) {
									$item_class .= ' hidden';
								}
							}

							if(in_array($gzalo_id, $gzalo_hide)) {
								$item_class .= ' hide';
							}
							?>
							<div class="col-lg-3 col-md-6 gzalo-item mb-4<?=$item_class?>">
								<div class="gzalo gzalo-<?=$gzalo_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar gzalo-progress text-center text-yellow">
										<div class="col gzalo-required<?php echo (isset($gzalo_data['required']) && $gzalo_data['required']!='')?' on':''; ?>">
										<?php
										if(isset($gzalo_data['required']) && $gzalo_data['required']!='') {
											?>
											<div class="bg-danger" title="Ngày gửi yêu cầu">
												<?php echo esc_html(date('d/m', strtotime($gzalo_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col gzalo-created<?php echo (isset($gzalo_data['created']) && $gzalo_data['created']!='')?' on':''; ?>">
											<?php
											if(isset($gzalo_data['created']) && $gzalo_data['created']!='') {
												?>
												<div class="bg-danger" title="Ngày tạo">
													<?php echo esc_html(date('d/m', strtotime($gzalo_data['created']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col gzalo-completed<?php echo (isset($gzalo_data['completed']) && $gzalo_data['completed']!='')?' on':''; ?>">
											<?php
											if(isset($gzalo_data['completed']) && $gzalo_data['completed']!='') {
												?>
												<div class="bg-danger" title="Ngày làm xong">
													<?php echo esc_html(date('d/m', strtotime($gzalo_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col gzalo-sent<?php echo (isset($gzalo_data['sent']) && $gzalo_data['sent']!='')?' on':''; ?>">
											<?php
											if(isset($gzalo_data['sent']) && $gzalo_data['sent']!='') {
												?>
												<div class="bg-danger" title="Ngày gửi cho khách">
													<?php echo esc_html(date('d/m', strtotime($gzalo_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<div class="gzalo-thumbnail position-relative">
										<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="gzalo-require-content">
											<?php
											if(isset($gzalo_content) && $gzalo_content!='') {
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($gzalo_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
										</div>
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $gzalo_id, 'full' ); ?></span>

										<div class="position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>

										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<div class="gzalo-signed<?php echo (isset($gzalo_data['signed']) && $gzalo_data['signed']=='yes')?' on':''; ?>">
												<?php
												if(isset($gzalo_data['signed']) && $gzalo_data['signed']=='yes') {
													?>
													<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã ký"><span class="dashicons dashicons-yes"></span></span>
													<?php
												}
												?>
											</div>
											<?php if(current_user_can('edit_gzalos')) { ?>
											<button class="gzalo-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-client="<?=$current_client->term_id?>" data-gzalo="<?=$gzalo_id?>" data-gzalo-title="Ẩn hợp đồng <?php echo esc_attr('"'.get_the_title( $gzalo_id ).'" ?'); ?>"><span class="dashicons dashicons-visibility"></span></button>

											<a href="<?php echo get_edit_post_link( $gzalo_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<?php } ?>
											<?php if(current_user_can('gzalo_edit')) { ?>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-gzalo" data-client="<?=$current_client->term_id?>" data-gzalo="<?=$gzalo_id?>" data-gzalo-title="<?php echo esc_attr(get_the_title( $gzalo_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="zalo-link position-absolute top-0 end-0 p-1">
										<?php if($gzalo_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($gzalo_data['zalo'])?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>
									</div>
									<div class="gzalo-info text-center px-1">
										<div class="gzalo-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $gzalo_id )); ?>
										</div>
										<?php if($gzalo_data['value']!='' || $gzalo_data['unit']!='') { ?>
										<div class="gzalo-value mb-1">
											<?php if($gzalo_data['value']!='') { ?>
											<div>
												<span>Tổng giá trị: </span>
												<span class="text-red fw-bold"><?php echo esc_html($gzalo_data['value']); ?></span>
											</div>
											<?php } ?>
											<?php if($gzalo_data['unit']!='') { ?>
											<div class="text-red"><?php echo esc_html($gzalo_data['unit']); ?></div>
											<?php } ?>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center gzalo-links mb-3">
											<?php
											if($gzalo_data['url']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($gzalo_data['url'])?>" target="_blank">Xem chi tiết</a>
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