<?php
/**
 * Template Name: Thiết kế
 * 
 */
get_header();

if(current_user_can( 'design_view' )) {

	while (have_posts()) {
		the_post();
		?>
		<div class="client-heading text-center py-3 text-yellow m-0 position-sticky">
			<div class="container">
				<div class="d-flex justify-content-between align-items-center">
					<div class="client-name text-uppercase">
						<div><?php the_title(); ?></div>
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
		<?php
	}


}

get_footer();