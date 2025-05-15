<?php
/**
 * Template Name: Đối tác
 * 
 */
$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;

get_header();
while (have_posts()) {
	the_post();
	if($client) {
		?>
		<div class="client-heading container-fluid text-center py-3 text-yellow text-uppercase h3 m-0 position-sticky"><?=esc_html($client->description)?></div>
		<?php
	}
	the_content();
}
get_footer();