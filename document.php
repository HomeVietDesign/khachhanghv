<?php
/**
 * Template Name: Hồ sơ
 * 
 */
global $current_client;

get_header();
while (have_posts()) {
	the_post();
	if($current_client) {
		?>
		<div class="client-heading container-fluid text-center py-3 text-yellow text-uppercase h3 m-0 position-sticky"><?=esc_html($current_client->description)?></div>
		<?php
	}
	the_content();
}
get_footer();