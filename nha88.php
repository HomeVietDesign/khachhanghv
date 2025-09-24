<?php
/**
 * Template Name: Nha88
 * 
 */
get_header();

if(current_user_can( 'nha88_view' )) {

	while (have_posts()) {
		the_post();
		
			?>
			<form id="design-filter-form" action="<?=esc_url(fw_current_url())?>" method="GET">
				<div id="site-content">
					<?php the_content(); ?>
				</div>
			</form>
			<?php
	}

}

get_footer();