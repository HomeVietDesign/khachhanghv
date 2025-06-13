<?php
/**
 * Template Name: Dự toán Khách hàng
 * 
 */
global $current_client;

get_header();
while (have_posts()) {
	the_post();
	if($current_client && current_user_can('estimate_customer_view')) {
		?>
		<div class="client-heading container-fluid text-center py-3 text-yellow text-uppercase m-0 position-sticky">
			<div><?=esc_html($current_client->description)?></div>
			<div class="fs-6">( <?=esc_html($current_client->name)?> )</div>
		</div>
		<?php
	
		the_content();
	}
}
get_footer();