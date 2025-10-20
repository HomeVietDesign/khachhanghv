<?php
/**
 * Template Name: Đầu tư
 * 
 */
get_header();

if(current_user_can('edit_investments')) {
	global $current_investor;

	while (have_posts()) {
		the_post();
		if($current_investor) {
		?>
		<div id="filter-form">
			<div class="client-heading text-center py-3 text-yellow m-0 position-sticky">
				<div class="container">
					<div class="d-flex justify-content-between align-items-center">
						<div class="client-name text-uppercase d-flex align-items-center">
							<div><?=esc_html($current_investor->name)?></div>
							<?php if($current_investor->description!='') { ?>
							<div class="fs-6 ms-3">( <?=esc_html($current_investor->description)?> )</div>
							<?php } ?>
						</div>
						<div id="ancho">
							<div class="dropdown">
								<button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Nhảy tới mục</button>
								<ul class="dropdown-menu"></ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="site-content">
				<?php the_content(); ?>
			</div>
		</div>
		<?php
		}
	}
}
get_footer();