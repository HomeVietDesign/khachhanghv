<?php
/**
 * Template Name: Dự toán nhà thầu
 * 
 */
global $current_client;

get_header();
while (have_posts()) {
	the_post();
	if($current_client) {
		?>
		<div class="client-heading container-fluid text-center py-3 text-yellow text-uppercase m-0 position-sticky">
			<span><?=esc_html($current_client->description)?></span>
			<?php if(has_role('administrator')) {
				$contractor_cat_hide = fw_get_db_term_option($current_client->term_id, 'contractor_cat', 'contractor_cat_hide', []);
				if(empty($contractor_cat_hide)) {
					$contractor_cat_hide = [];
				}

				$contractor_cats = get_terms(['taxonomy' => 'contractor_cat','parent'=>0,'fields'=>'id=>name','exclude' => [get_option( 'default_term_contractor_cat', -1 )]]);
				?>
				<div class="contractor-cat-hide position-absolute top-50 end-0 translate-middle-y">
					<div class="dropdown lh-1">
						<button class="btn btn-sm btn-warning me-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><span class="dashicons dashicons-visibility"></span></button>
						<ul class="dropdown-menu dropdown-menu-dark">
							<?php if($contractor_cats) {
								foreach ($contractor_cats as $id => $name) {
									?>
									<li>
										<div class="dropdown-item">
										<div class="form-check">
											<input class="form-check-input contractor-cat-hide-toggle" type="checkbox" value="<?=$id?>" id="contractor-cat-hide-<?=$id?>" <?php checked( false, in_array($id, $contractor_cat_hide) ); ?> data-client="<?=$current_client->term_id?>">
											<label class="form-check-label" for="contractor-cat-hide-<?=$id?>"><?=esc_html($name)?></label>
										</div>
										</div>
									</li>
									<?php
								}
							} ?>
						</ul>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}
	the_content();
}
get_footer();