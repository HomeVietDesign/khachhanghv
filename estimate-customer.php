<?php
/**
 * Template Name: Dự toán Khách hàng
 * 
 */


get_header();

if(current_user_can('estimate_customer_view')) {
	global $current_client;

	while (have_posts()) {
		the_post();
		if($current_client) {
		?>
		<div class="client-heading container-fluid text-center py-3 text-yellow text-uppercase m-0 position-sticky">
			<div><?=esc_html($current_client->description)?></div>
			<div class="fs-6">( <?=esc_html($current_client->name)?> )</div>
		</div>
		<div id="site-content">
			<?php the_content(); ?>
		</div>
		<?php
		}
	}
}
get_footer();