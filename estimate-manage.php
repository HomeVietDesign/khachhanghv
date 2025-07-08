<?php
/**
 * Template Name: Dự toán Xây dựng - Đồ gỗ (bỏ)
 * 
 */
global $current_client;

get_header();

while (have_posts()) {
	the_post();
	global $post;
	if($current_client && current_user_can('estimate_manage_'.$post->post_name.'_view')) {
		?>
		<form id="estimate-filter-form" action="<?=esc_url(fw_current_url())?>" method="GET">
			<div class="client-heading text-center py-3 text-yellow m-0 position-sticky">
				<div class="container">
					<?php
					$progress = isset($_GET['progress']) ? $_GET['progress'] : '';
					?>
					<input type="hidden" name="client" value="<?=$current_client->term_id?>">
					<div class="d-flex justify-content-between align-items-center">
						<div class="client-name text-uppercase d-flex align-items-center">
							<div><?=esc_html($current_client->description)?></div>
							<div class="fs-6 ms-3">( <?=esc_html($current_client->name)?> )</div>
						</div>
						<div class="filters d-flex justify-content-end align-items-center">
							<div class="filter-progress d-flex justify-content-end align-items-center">
								<div class="filter-progress-item m-1 d-flex">
									<input type="checkbox" class="btn-check progress-checker" name="progress" value="none" id="progress-none" <?php checked( 'none', $progress, true ); ?>>
									<label class="btn btn-sm btn-outline-green fw-bold" for="progress-none">Chưa có: <span>0</span></label>
								</div>
								<div class="filter-progress-item m-1 d-flex">
									<input type="checkbox" class="btn-check progress-checker" name="progress" value="required" id="progress-required" <?php checked( 'required', $progress, true ); ?>>
									<label class="btn btn-sm btn-outline-yellow fw-bold" for="progress-required">Yêu cầu: <span>0</span></label>
								</div>
								<div class="filter-progress-item m-1 d-flex">
									<input type="checkbox" class="btn-check progress-checker" name="progress" value="received" id="progress-received" <?php checked( 'received', $progress, true ); ?>>
									<label class="btn btn-sm btn-outline-yellow fw-bold" for="progress-received">Nhận: <span>0</span></label>
								</div>
								<div class="filter-progress-item m-1 d-flex">
									<input type="checkbox" class="btn-check progress-checker" name="progress" value="completed" id="progress-completed" <?php checked( 'completed', $progress, true ); ?>>
									<label class="btn btn-sm btn-outline-yellow fw-bold" for="progress-completed">Xong: <span>0</span></label>
								</div>
								<div class="filter-progress-item m-1 d-flex">
									<input type="checkbox" class="btn-check progress-checker" name="progress" value="sent" id="progress-sent" <?php checked( 'sent', $progress, true ); ?>>
									<label class="btn btn-sm btn-outline-yellow fw-bold" for="progress-sent">Gửi: <span>0</span></label>
								</div>
								<div class="filter-progress-item m-1 d-flex">
									<input type="checkbox" class="btn-check progress-checker" name="progress" value="quote" id="progress-quote" <?php checked( 'quote', $progress, true ); ?>>
									<label class="btn btn-sm btn-outline-green fw-bold" for="progress-quote">Khách chọn: <span>0</span></label>
								</div>
							</div>
						</div>
					</div>
				
				</div>
			</div>

			<div id="site-content">
				<?php the_content(); ?>
			</div>
		</form>
		<?php
	}
	
}


get_footer();