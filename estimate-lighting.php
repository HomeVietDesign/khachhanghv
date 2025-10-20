<?php
/**
 * Template Name: Dự toán Đèn
 * 
 */
get_header();

if(has_role('administrator')) {
	$wp_users = get_users( ['role'=>'subscriber', 'capability__in' => ['read_elightings', 'edit_elightings']] );
	if($wp_users) {
	?>
	<div class="d-flex justify-content-center align-items-center flex-wrap mt-3">
		<?php
		foreach($wp_users as $wp_user) {
			?>
			<div class="mx-1 px-2 text-bg-secondary rounded">
				<?php
				echo esc_html($wp_user->display_name);
				echo ($wp_user->has_cap( 'edit_elightings' )) ? esc_html(' (Chỉnh sửa)') : esc_html(' (Chỉ xem)');
				?>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	}
}

if(current_user_can('read_elightings')||current_user_can('edit_elightings')) {
	global $current_client;

	while (have_posts()) {
		the_post();
		if($current_client) {
		?>
		<form id="estimate-filter-form" action="<?=esc_url(fw_current_url())?>" method="GET">
			<div class="client-heading text-center py-3 text-yellow m-0 position-sticky">
				<div class="container">
					<input type="hidden" name="client" value="<?=$current_client->term_id?>">
					<div class="d-flex justify-content-between align-items-center">
						<div class="client-name text-uppercase d-flex align-items-center">
							<div><?=esc_html($current_client->name)?></div>
							<?php if($current_client->description!='') { ?>
							<div class="fs-6 ms-3">( <?=esc_html($current_client->description)?> )</div>
							<?php } ?>
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
		</form>
		<?php
		}
	}
}

get_footer();