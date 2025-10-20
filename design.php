<?php
/**
 * Template Name: Thiết kế
 * 
 */
get_header();

if(has_role('administrator')) {
	$wp_users = get_users( ['role'=>'subscriber', 'capability__in' => ['read_designs', 'edit_designs']] );
	if($wp_users) {
	?>
	<div class="d-flex justify-content-center align-items-center flex-wrap mt-3">
		<?php
		foreach($wp_users as $wp_user) {
			?>
			<div class="mx-1 px-2 text-bg-secondary rounded">
				<?php
				echo esc_html($wp_user->display_name);
				echo ($wp_user->has_cap( 'edit_designs' )) ? esc_html(' (Chỉnh sửa)') : esc_html(' (Chỉ xem)');
				?>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	}
}

if(current_user_can('read_designs') || current_user_can('edit_designs')) {

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