<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_nha88_type;

$nha88_cats = get_terms(['taxonomy' => 'nha88_cat','parent'=>0]);
$progress = isset($_GET['progress']) ? $_GET['progress'] : '';

if($nha88_cats && $current_nha88_type) {

	$nha88_hide = fw_get_db_term_option($current_nha88_type->term_id, 'nha88_type', 'nha88_hide', []);

	?>
	<div class="fw-shortcode-nha88s">
		<div class="accordion">
		<?php
		foreach ($nha88_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
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
							$default_url = fw_get_db_post_option($nha88_id,'nha88_url');
							$default_data = [
								'value' => fw_get_db_post_option($nha88_id,'nha88_value'),
								'note' => fw_get_db_post_option($nha88_id,'nha88_note'),
								'zalo' => fw_get_db_post_option($nha88_id,'nha88_zalo'),
							];

							$data = get_post_meta($nha88_id, '_data', true);
							$nha88_data = isset($data[$current_nha88_type->term_id])?$data[$current_nha88_type->term_id]:[ 'required'=>'', 'created'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'note'=>'', 'zalo'=>'', 'url'=>'', 'sold'=>''];
							
							if(empty($nha88_data['value'])) $nha88_data['value'] = $default_data['value'];
							if(empty($nha88_data['note'])) $nha88_data['note'] = $default_data['note'];
							if(empty($nha88_data['zalo'])) $nha88_data['zalo'] = $default_data['zalo'];

							$display = empty($progress) ? true : false;

							if(!$display) {
								$display_none = true;
								if('none'==$progress) {
									$display_none = false;
									if(empty($nha88_data['required']) && empty($nha88_data['created']) && empty($nha88_data['completed']) && empty($nha88_data['sent']) && empty($nha88_data['sold'])) {
										$display_none = true;
									}
								}

								$display_required = true;
								if('required'==$progress) {
									$display_required = false;
									if(isset($nha88_data['required']) && $nha88_data['required']!='' ) {
										$display_required = true;
									}
								}

								$display_created = true;
								if('created'==$progress) {
									$display_created = false;
									if(isset($nha88_data['created']) && $nha88_data['created']!='' ) {
										$display_created = true;
									}
								}

								$display_completed = true;
								if('completed'==$progress) {
									$display_completed = false;
									if(isset($nha88_data['completed']) && $nha88_data['completed']!='' ) {
										$display_completed = true;
									}
								}

								$display_sent = true;
								if('sent'==$progress) {
									$display_sent = false;
									if(isset($nha88_data['sent']) && $nha88_data['sent']!='' ) {
										$display_sent = true;
									}
								}

								$display_sold = true;
								if('sold'==$progress) {
									$display_sold = false;
									if(isset($nha88_data['sold']) && $nha88_data['sold']!='' ) {
										$display_sold = true;
									}
								}

							}
							
							$item_class = '';

							if(!$display) {
								if( !($display_none && $display_required && $display_created && $display_completed && $display_sent && $display_sold )) {
									$item_class .= ' hidden';
								}
							}

							if(in_array($nha88_id, $nha88_hide)) {
								$item_class .= ' hide';
							}
							?>
							<div class="col-lg-3 col-md-6 nha88-item mb-4<?=$item_class?>">
								<div class="nha88 nha88-<?=$nha88_id?> border border-dark h-100 bg-black">
									<div class="row g-0 progressing-bar nha88-progress text-center text-yellow">
										<div class="col nha88-required<?php echo (isset($nha88_data['required']) && $nha88_data['required']!='')?' on':''; ?>">
										<?php
										if(isset($nha88_data['required']) && $nha88_data['required']!='') {
											?>
											<div class="bg-danger" title="Ngày gửi yêu cầu">
												<?php echo esc_html(date('d/m', strtotime($nha88_data['required']))); ?>
											</div>
											<?php
										}
										?>
										</div>
										<div class="col nha88-created<?php echo (isset($nha88_data['created']) && $nha88_data['created']!='')?' on':''; ?>">
											<?php
											if(isset($nha88_data['created']) && $nha88_data['created']!='') {
												?>
												<div class="bg-danger" title="Ngày làm">
													<?php echo esc_html(date('d/m', strtotime($nha88_data['created']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col nha88-completed<?php echo (isset($nha88_data['completed']) && $nha88_data['completed']!='')?' on':''; ?>">
											<?php
											if(isset($nha88_data['completed']) && $nha88_data['completed']!='') {
												?>
												<div class="bg-danger" title="Ngày làm xong">
													<?php echo esc_html(date('d/m', strtotime($nha88_data['completed']))); ?>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col nha88-sent<?php echo (isset($nha88_data['sent']) && $nha88_data['sent']!='')?' on':''; ?>">
											<?php
											if(isset($nha88_data['sent']) && $nha88_data['sent']!='') {
												?>
												<div class="bg-danger" title="Ngày gửi cho khách">
													<?php echo esc_html(date('d/m', strtotime($nha88_data['sent']))); ?>
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<div class="nha88-thumbnail position-relative">
										<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
											<div class="nha88-require-content">
											<?php
											if(isset($nha88_content) && $nha88_content!='') {
												$nha88_content = '<div class="copy-text mb-3">'.wp_get_the_content($nha88_content).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
												?>
												<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($nha88_content))?>" data-bs-html="true">Đề bài</button>
												<?php
											}
											?>
											</div>
										</div>
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $nha88_id, 'full' ); ?></span>

										<div class="position-absolute start-0 bottom-0 p-1 z-3 d-flex">
											<?php if($default_url) { ?>
											<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
											<?php } ?>
										</div>

										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<div class="nha88-sold<?php echo (isset($nha88_data['sold']) && $nha88_data['sold']=='yes')?' on':''; ?>">
												<?php
												if(isset($nha88_data['sold']) && $nha88_data['sold']=='yes') {
													?>
													<span class="btn-shadow btn btn-sm btn-warning border-0 bg-green text-dark fw-bold ms-2" title="Khách hàng đã mua"><span class="dashicons dashicons-yes"></span></span>
													<?php
												}
												?>
											</div>
											<?php if(current_user_can('edit_nha88s')) { ?>
											<button class="nha88-hide btn btn-sm btn-danger text-yellow ms-2" type="button" data-nha88_type="<?=$current_nha88_type->term_id?>" data-nha88="<?=$nha88_id?>" data-nha88-title="Ẩn hợp đồng <?php echo esc_attr('"'.get_the_title( $nha88_id ).'" ?'); ?>"><span class="dashicons dashicons-visibility"></span></button>

											<a href="<?php echo get_edit_post_link( $nha88_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<?php } ?>
											<?php if(current_user_can('nha88_edit')) { ?>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-nha88" data-nha88_type="<?=$current_nha88_type->term_id?>" data-nha88="<?=$nha88_id?>" data-nha88-title="<?php echo esc_attr(get_the_title( $nha88_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
											<?php } ?>
										</div>
										
										<div class="zalo-link position-absolute top-0 end-0 p-1">
										<?php if($nha88_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($nha88_data['zalo'])?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>
									</div>
									<div class="nha88-info text-center px-1">
										<div class="nha88-title pt-3 mb-1 fs-5 text-green text-uppercase">
											<?php echo esc_html(get_the_title( $nha88_id )); ?>
										</div>
										<?php if($nha88_data['value']!='' || $nha88_data['note']!='') { ?>
										<div class="nha88-value mb-1">
											<?php if($nha88_data['value']!='') { ?>
											<div>
												<span>Tổng giá trị: </span>
												<span class="text-red fw-bold"><?php echo esc_html($nha88_data['value']); ?></span>
											</div>
											<?php } ?>
											<?php if($nha88_data['note']!='') { ?>
											<div class="text-red"><?php echo esc_html($nha88_data['note']); ?></div>
											<?php } ?>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center nha88-links mb-3">
											<?php
											if($nha88_data['url']) {
												?>
												<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($nha88_data['url'])?>" target="_blank">Xem chi tiết</a>
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