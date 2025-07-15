<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

?>
<div class="fw-shortcode-gzalos">
	<section class="mb-3">
		<h2 class="text-center text-uppercase text-yellow m-0 p-3 fw-bold">
			<?php the_title(); ?>
		</h2>
		<div class="row justify-content-center">
		<?php
		$gzalos = get_posts([
			'post_type' => 'gzalo',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'fields' => 'ids'
		]);
		if($gzalos) {
			foreach($gzalos as $gzalo_id) {
				$gzalo_url = fw_get_db_post_option($gzalo_id, 'gzalo_zalo');
				
				?>
				<div class="col-lg-3 col-md-6 gzalo-item mb-4">
					<div class="gzalo gzalo-<?=$gzalo_id?> h-100 bg-black">
						<div class="gzalo-thumbnail position-relative">
							<span class="thumbnail-image position-absolute w-100 h-100 start-0 top-0 border-bottom border-top border-dark"><?php echo get_the_post_thumbnail( $gzalo_id, 'full' ); ?></span>
							<div class="position-absolute bottom-0 end-0 m-1 d-flex">
								<?php if(current_user_can('edit_gzalos')) { ?>
								<a href="<?php echo get_edit_post_link( $gzalo_id ); ?>" class="btn btn-sm btn-primary btn-shadow fw-bold ms-2" target="blank" title="Sửa chi tiết"><span class="dashicons dashicons-edit-page"></span></a>
								<?php } ?>
							</div>
							<div class="zalo-link position-absolute top-0 end-0 p-2">
							<?php if($gzalo_url) { ?>
								<a class="btn btn-sm btn-shadow fw-bold" href="<?=esc_url($gzalo_url)?>" target="_blank">Zalo</a>
							<?php } ?>
							</div>
						</div>
						<div class="gzalo-info text-center px-1">
							<div class="gzalo-title pt-3 mb-3 fs-5 text-green text-uppercase">
								<?php echo esc_html(get_the_title( $gzalo_id )); ?>
							</div>
						</div>
					</div>
				</div>
		<?php }
		}
		?>
		</div>
	</section>
</div>
<?php
