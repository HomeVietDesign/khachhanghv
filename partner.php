<?php
/**
 * Template Name: Đối tác
 * 
 */
global $current_client;

get_header();
while (have_posts()) {
	the_post();
	if($current_client) {
		?>
		<div class="client-heading container-fluid text-center py-3 text-yellow text-uppercase h3 m-0 position-sticky">
			<div><?=esc_html($current_client->description)?></div>
			<div class="fs-6">( <?=esc_html($current_client->name)?> )</div>
		</div>
		<?php
	}
	the_content();
}
get_footer();