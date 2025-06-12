<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_password, $current_client;
//$default_term_password = get_option( 'default_term_passwords', -1 );

$progress = isset($_GET['progress']) ? $_GET['progress'] : '';

$estimate_cat = isset($atts['estimate_cat']) ? get_term_by( 'term_id', $atts['estimate_cat'][0], 'estimate_cat' ) : null;

if( $estimate_cat instanceof \WP_Term && $current_client ) {

	$client_estimates = get_term_meta($current_client->term_id, '_estimates', true);
	?>
	<div class="fw-shortcode-estimate-manage">
		<section class="mb-3">
			<div class="row justify-content-center">
			<?php
			$estimates = get_posts([
				'post_type' => 'estimate',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'fields' => 'ids',
				'tax_query' => [
					'cat' => [
						'taxonomy' => 'estimate_cat',
						'field' => 'term_id',
						'terms' => [$estimate_cat->term_id]
					]
				]
			]);

			if($estimates) {

				$display = empty($progress) ? true : false;

				foreach($estimates as $estimate_id) {
					$default_estimate_file = fw_get_db_post_option($estimate_id,'estimate_file');
					$default_estimate = [
						'value' => fw_get_db_post_option($estimate_id,'estimate_value'),
						'unit' => fw_get_db_post_option($estimate_id,'estimate_unit'),
						'zalo' => fw_get_db_post_option($estimate_id,'estimate_zalo'),
						'file_id' => (!empty($default_estimate_file))?$default_estimate_file['attachment_id']:'',
					];

					$default_url = fw_get_db_post_option($estimate_id,'estimate_url');
					$estimate_content = fw_get_db_post_option($estimate_id,'estimate_content');

					$client_estimate = isset($client_estimates[$estimate_id])?$client_estimates[$estimate_id]:[ 'required'=>'', 'received'=>'', 'completed'=>'', 'sent'=>'', 'value'=>'', 'unit'=>'', 'zalo'=>'', 'url'=>'', 'file_id'=>'', 'quote'=>''];

					if(empty($client_estimate['value'])) $client_estimate['value'] = $default_estimate['value'];
					if(empty($client_estimate['unit'])) $client_estimate['unit'] = $default_estimate['unit'];
					if(empty($client_estimate['zalo'])) $client_estimate['zalo'] = $default_estimate['zalo'];
					if(empty($client_estimate['file_id'])) $client_estimate['file_id'] = $default_estimate['file_id'];

					//debug($client_estimate);
					if(!$display) {
						$display_required = true;
						if('required'==$progress) {
							$display_required = false;
							if(isset($client_estimate['required']) && $client_estimate['required']!='' ) {
								$display_required = true;
							}
						}

						$display_received = true;
						if('received'==$progress) {
							$display_received = false;
							if(isset($client_estimate['received']) && $client_estimate['received']!='' ) {
								$display_received = true;
							}
						}

						$display_completed = true;
						if('completed'==$progress) {
							$display_completed = false;
							if(isset($client_estimate['completed']) && $client_estimate['completed']!='' ) {
								$display_completed = true;
							}
						}

						$display_sent = true;
						if('sent'==$progress) {
							$display_sent = false;
							if(isset($client_estimate['sent']) && $client_estimate['sent']!='' ) {
								$display_sent = true;
							}
						}

						$display_quote = true;
						if('quote'==$progress) {
							$display_quote = false;
							if(isset($client_estimate['quote']) && $client_estimate['quote']!='' ) {
								$display_quote = true;
							}
						}

					}

					$item_class = '';

					if(!$display && !($display_required && $display_received && $display_completed && $display_sent && $display_quote)) {
						$item_class = 'hidden';
					}
					
					?>
					<div class="col-lg-3 col-md-6 estimate-item mb-4 <?=$item_class?>">
						<div class="estimate estimate-<?=$estimate_id?> border border-dark bg-black h-100">
							<div class="row g-0 estimate-progress text-center text-yellow">
								<div class="col estimate-required<?php echo (isset($client_estimate['required']) && $client_estimate['required']!='')?' on':''; ?>">
								<?php
								if(isset($client_estimate['required']) && $client_estimate['required']!='') {
									?>
									<div title="Ngày gửi yêu cầu">
										<?php echo esc_html(date('d/m', strtotime($client_estimate['required']))); ?>
									</div>
									<?php
								}
								?>
								</div>
								<div class="col estimate-received<?php echo (isset($client_estimate['received']) && $client_estimate['received']!='')?' on':''; ?>">
									<?php
									if(isset($client_estimate['received']) && $client_estimate['received']!='') {
										?>
										<div title="Ngày nhận dự toán nhà thầu">
											<?php echo esc_html(date('d/m', strtotime($client_estimate['received']))); ?>
										</div>
										<?php
									}
									?>
								</div>
								<div class="col estimate-completed<?php echo (isset($client_estimate['completed']) && $client_estimate['completed']!='')?' on':''; ?>">
									<?php
									if(isset($client_estimate['completed']) && $client_estimate['completed']!='') {
										?>
										<div title="Ngày làm xong dự toán">
											<?php echo esc_html(date('d/m', strtotime($client_estimate['completed']))); ?>
										</div>
										<?php
									}
									?>
								</div>
								<div class="col estimate-sent<?php echo (isset($client_estimate['sent']) && $client_estimate['sent']!='')?' on':''; ?>">
									<?php
									if(isset($client_estimate['sent']) && $client_estimate['sent']!='') {
										?>
										<div title="Ngày gửi khách">
											<?php echo esc_html(date('d/m', strtotime($client_estimate['sent']))); ?>
										</div>
										<?php
									}
									?>
								</div>
							</div>
							<div class="estimate-thumbnail position-relative">
								<div class="position-absolute top-0 start-0 p-1 z-3 d-flex">
									<div class="estimate-require-content">
									<?php
									if(isset($estimate_content) && $estimate_content!='') {
										?>
										<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-2" data-bs-toggle="popover" data-bs-title="Nội dung yêu cầu" data-bs-content="<?=esc_attr(wp_get_the_content($estimate_content))?>" data-bs-html="true" data-bs-trigger="click">Đề bài</button>
										<?php
									}
									?>
									</div>
									<div class="file-download">
									<?php
									if(isset($client_estimate['file_id']) && $client_estimate['file_id']!='') {
										$attachment_url = wp_get_attachment_url($client_estimate['file_id']);
										if($attachment_url) {
										?>
										<a class="btn-shadow btn btn-sm btn-primary fw-bold" href="<?=esc_url($attachment_url)?>" target="_blank">Tải</a>
										<?php
										}
									}
									?>
									</div>
								</div>
								<div class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-top border-bottom border-dark"><?php echo get_the_post_thumbnail( $estimate_id, 'full' ); ?></div>
								
								<div class="position-absolute bottom-0 end-0 m-1 d-flex">
									<div class="estimate-quote<?php echo (isset($client_estimate['quote']) && $client_estimate['quote']=='yes')?' on':''; ?>">
									<?php
									if(isset($client_estimate['quote']) && $client_estimate['quote']=='yes') {
										?>
										<span class="btn-shadow btn btn-sm btn-warning border-secondary bg-green text-dark fw-bold ms-2" title="Đã gửi cho khách hàng"><span class="dashicons dashicons-yes"></span></span>
										<?php
									}
									?>
									</div>
									<?php if(has_role('administrator')) { ?>
										<a href="<?php echo get_edit_post_link( $estimate_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
									<?php } ?>
									<?php if(current_user_can('estimate_manage_edit')) { ?>
										<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-estimate-manage" data-client="<?=$current_client->term_id?>" data-estimate="<?=$estimate_id?>" data-estimate-title="<?php echo esc_attr(get_the_title( $estimate_id )); ?>"><span class="dashicons dashicons-edit"></span></button>
									<?php } ?>
								</div>
								
								<div class="zalo-link position-absolute top-0 end-0 p-2">
								<?php if($client_estimate['zalo']) { ?>
									<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($client_estimate['zalo'])?>" target="_blank">Zalo</a>
								<?php } ?>
								</div>
								<?php if($default_url) { ?>
									<a class="btn btn-sm btn-primary btn-shadow fw-bold position-absolute start-0 bottom-0 m-1 z-3" href="<?=esc_url($default_url)?>" target="_blank">Gốc</a>
								<?php } ?>
							</div>
							<div class="estimate-info text-center px-1">
								<div class="estimate-title pt-3 mb-1 fs-5 text-green">
									<?php echo esc_html(get_the_title( $estimate_id )); ?>
								</div>
								<?php if($client_estimate['value']) { ?>
								<div class="estimate-value mb-1">
									<span>Tổng giá trị:</span>
									<span class="text-red fw-bold"><?php echo esc_html($client_estimate['value']); ?></span>
								</div>
								<?php } ?>
								<?php if($client_estimate['unit']) { ?>
								<div class="estimate-unit mb-1">
									<div class="text-red"><?php echo esc_html($client_estimate['unit']); ?></div>
								</div>
								<?php } ?>
								<div class="d-flex flex-wrap justify-content-center estimate-url mb-3">
									<?php
									if($client_estimate['url']) {
										?>
										<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($client_estimate['url'])?>" target="_blank">Xem chi tiết</a>
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
		</section>
	</div>
	<?php
}