<?php
/**
 * Template Name: Thi công
 * 
 */
get_header();

if(current_user_can( 'construction_view' )) {


	while (have_posts()) {
		the_post();
		?>
		<form id="construction-filter-form" action="<?=esc_url(fw_current_url())?>" method="GET">
			<div id="site-content">
				<?php the_content(); ?>
			</div>
		</form>
		<?php
	}


}

get_footer();