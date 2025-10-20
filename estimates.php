<?php
/**
 * Template Name: Các dự toán
 * 
 */

get_header();

if(has_role('administrator')) {
	$wp_users = get_users( ['role'=>'subscriber'] );
	?>
	<div class="d-flex justify-content-center align-items-center flex-wrap my-3">
	<?php
	foreach($wp_users as $wp_user) {
		if( $wp_user->has_cap( 'read_estimates' ) ) {
		?>
		<div class="mx-1 px-2 text-bg-secondary rounded"><?=esc_html($wp_user->display_name)?></div>
		<?php
		}
	}
	?>
	</div>
	<?php
}

while (have_posts()) {
	the_post();
	the_content();
}
get_footer();