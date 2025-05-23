<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
$per = isset($atts['per'])?absint($atts['per']):0;

global $current_password;
$default_term_password = get_option( 'default_term_passwords', -1 );

$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;
$contractor_cats = get_terms(['taxonomy' => 'contractor_cat','parent'=>0]);

if($contractor_cats && $client) {
	?>
	<div class="fw-shortcode-estimates">
		<div class="accordion">
		<?php
		foreach ($contractor_cats as $key => $value) {
			if($key>0) {
				$childrent = get_terms(['taxonomy' => 'contractor_cat','parent'=>$value->term_id]);
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
									'terms' => fw_get_db_settings_option('contractor_rating_top')
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
					<section class="accordion-item mb-3">
						<h2 class="accordion-header">
							<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panels-<?=$key?>" aria-expanded="true" aria-controls="panels-<?=$key?>"><?=esc_html($value->name)?></button>
						</h2>
						<div id="panels-<?=$key?>" class="accordion-collapse collapse show">
		      				<div class="accordion-body">
								<div class="items row justify-content-center">
								<?php
								foreach($contractors as $i => $contractor_id) {
									if($per<=0 || $i<$per) {
										\FW_Shortcode_Estimates::display_contractor($contractor_id, $client);
									} else {
										break;
									}
								}
								?>
								</div>
								<div class="pagination-link d-flex justify-content-center" data-ids="<?=esc_attr(json_encode($contractors))?>" data-per="<?=$per?>" data-total="<?=$total?>" data-client="<?=$client->term_id?>"></div>
							</div>
						</div>
					</section>
					<?php
					}
				}
			}
		}
		?>
		</div>
	</div>
	<?php
}