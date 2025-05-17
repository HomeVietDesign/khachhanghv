<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
global $current_password;
$default_term_password = get_option( 'default_term_passwords', -1 );

$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;
$partner_cats = get_terms(['taxonomy' => 'partner_cat','parent'=>0]);

if($partner_cats && $client) {
	?>
	<div class="fw-shortcode-partners">
		<div class="accordion">
		<?php
		foreach ($partner_cats as $key => $value) {
		?>
		<section class="accordion-item mb-3">
			<h2 class="accordion-header">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
			</h2>
			<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
  				<div class="accordion-body">
					<div class="row justify-content-center">
					<?php
					$partners = get_posts([
						'post_type' => 'partner',
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'fields' => 'ids',
						'tax_query' => [
							'cat' => [
								'taxonomy' => 'partner_cat',
								'field' => 'id',
								'terms' => [$value->term_id]
							]
						]
					]);
					if($partners) {
						foreach($partners as $partner_id) {
							$default_attachment = fw_get_db_post_option($partner_id,'estimate_attachment');
							$default_data = [
								'value' => fw_get_db_post_option($partner_id,'estimate_value'),
								'unit' => fw_get_db_post_option($partner_id,'estimate_unit'),
								'zalo' => fw_get_db_post_option($partner_id,'estimate_zalo'),
								'attachment_id' => ($default_attachment) ? $default_attachment['attachment_id']:''
							];

							$data = get_post_meta($partner_id, '_data', true);
							$partner_data = isset($data[$client->term_id])?$data[$client->term_id]:[ 'value'=>'', 'unit'=>'', 'zalo'=>'', 'attachment_id'=>''];

							if(empty($partner_data['value'])) $partner_data['value'] = $default_data['value'];
							if(empty($partner_data['unit'])) $partner_data['unit'] = $default_data['unit'];
							if(empty($partner_data['zalo'])) $partner_data['zalo'] = $default_data['zalo'];
							if(empty($partner_data['attachment_id'])) $partner_data['attachment_id'] = $default_data['attachment_id'];

							$phone_number = get_post_field( 'post_excerpt', $partner_id );
							
							?>
							<div class="col-lg-3 col-md-6 partner-item mb-4">
								<div class="partner partner-<?=$partner_id?> border border-dark h-100">
									<div class="partner-thumbnail position-relative">
										<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0"><?php echo get_the_post_thumbnail( $partner_id, 'full' ); ?></span>
										<?php if(has_role('administrator')) { ?>
										<div class="position-absolute bottom-0 end-0 m-1 d-flex">
											<a href="<?php echo get_edit_post_link( $partner_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
											<button type="button" class="btn btn-sm btn-danger btn-shadow text-yellow fw-bold ms-2" data-bs-toggle="modal" data-bs-target="#edit-partner" data-client="<?=$client->term_id?>" data-partner="<?=$partner_id?>" data-partner-title="<?php echo esc_attr(get_the_title( $partner_id )); ?>"><span class="dashicons dashicons-edit" title="Sửa nhanh"></span></button>
										</div>
										<?php } ?>
										<div class="zalo-link position-absolute top-0 end-0 p-2">
										<?php if($partner_data['zalo']) { ?>
											<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($partner_data['zalo'])?>" target="_blank">Zalo</a>
										<?php } ?>
										</div>
									</div>
									<div class="partner-info text-center px-1">
										<div class="partner-title pt-3 mb-1 fs-5">
											<span class="d-block text-truncate" title="<?php echo esc_attr(get_the_title( $partner_id )); ?>"><?php echo esc_html(get_the_title( $partner_id )); ?></span>
										</div>
										<?php if($partner_data['value']!='') { ?>
										<div class="partner-value mb-1">
											<span>Tổng giá trị: </span>
											<span class="text-red fw-bold"><?php echo esc_html($partner_data['value']); ?></span>
											<span class="text-red"> <?php echo esc_html($partner_data['unit']); ?></span>
										</div>
										<?php } ?>
										<div class="d-flex flex-wrap justify-content-center partner-links mb-3">
											<?php
											if($phone_number) {
												?>
												<a class="btn btn-sm btn-danger my-1 mx-2" href="tel:<?=esc_attr($phone_number)?>"><?=esc_html($phone_number)?></a>
												<?php
											}

											if($partner_data['attachment_id']>0) {
												$attachment_url = wp_get_attachment_url($partner_data['attachment_id']);
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