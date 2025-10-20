<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
$shortcode = fw_ext( 'shortcodes' )->get_shortcode('estimate_lighting');

/**
 * @var array $atts
 */
$per = isset($atts['per'])?absint($atts['per']):0;

global $current_client;

if($current_client) {
	$exclude = [get_option( 'default_term_contractor_cat', -1 )];

	$contractor_cats = get_terms(['taxonomy' => 'contractor_cat','parent'=>0, 'exclude' => $exclude]);

	if($contractor_cats) {
		?>
		<div class="fw-shortcode-estimates-lighting">
			<div class="accordion">
			<?php
			foreach ($contractor_cats as $key => $value) {
				$childrent = get_terms(['taxonomy' => 'contractor_cat', 'parent'=>$value->term_id]);
				if($childrent) {
					$contractors = [];
					foreach ($childrent as $child) {
						$_contractors = get_posts([
							'post_type' => 'contractor',
							'posts_per_page' => -1,
							'post_status' => 'publish',
							'fields' => 'ids',
							'tax_query' => [
								'cat' => [
									'taxonomy' => 'contractor_cat',
									'field' => 'id',
									'terms' => [$child->term_id]
								],
								'rating' => [
									'taxonomy' => 'contractor_rating',
									'field' => 'id',
									'terms' => fw_get_db_settings_option('contractor_lighting')
								]
							]
						]);
						if($_contractors) {
							$contractors = array_merge($contractors, $_contractors);
						}
					}

					$contractors = array_unique($contractors);

					if($contractors) {
						$total = ($per>0)?ceil(count($contractors)/$per):0;

						?>
						<section class="accordion-item contractor-cat-section contractor-cat-section-<?=$value->term_id?> mb-3">
							<h2 class="accordion-header position-relative" id="accordion-header-<?=$value->term_id?>">
								<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$value->term_id?>" aria-expanded="true" aria-controls="panels-<?=$value->term_id?>"><?=esc_html($value->name)?></button>
							</h2>
							<div id="panels-<?=$value->term_id?>" class="accordion-collapse collapse show">
			      				<div class="accordion-body">
									<div class="items row justify-content-center">
									<?php
									$contractor_lighting_hide = get_term_meta($current_client->term_id, 'contractor_lighting_hide', true);
									if(empty($contractor_lighting_hide)) $contractor_lighting_hide = [];

									$contractor_lighting_removed = get_term_meta($current_client->term_id, 'contractor_lighting_removed', true);
									if(empty($contractor_lighting_removed)) $contractor_lighting_removed = [];

									foreach($contractors as $i => $contractor_id) {
										if($per<=0 || $i<$per) {
											$shortcode->display_contractor($contractor_id, $current_client, $contractor_lighting_hide, $contractor_lighting_removed);
										} else {
											break;
										}
									}
									?>
									</div>
									<div class="pagination-link d-flex justify-content-center" data-ids="<?=esc_attr(json_encode($contractors))?>" data-per="<?=$per?>" data-total="<?=$total?>" data-client="<?=$current_client->term_id?>"></div>
								</div>
							</div>
						</section>
						<?php
					}
				}
			}
			?>
			</div>
		</div>
		<?php
	}
}