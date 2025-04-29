<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
//debug($atts);
$contractor_id = (isset($atts['contractor']) && !empty($atts['contractor'])) ? absint($atts['contractor'][0]) : 0;

if($contractor_id==0) return;

$pdf = (isset($atts['pdf']) && !empty($atts['pdf']))?wp_get_attachment_url($atts['pdf']['attachment_id']):'';

$phone_number = get_post_meta($contractor_id, '_phone_number', true);
$external_url = get_post_meta($contractor_id, '_external_url', true);
$external_url = ($external_url!='')?esc_url($external_url):'javascript:void(0);';
?>
<div class="fw-shortcode-estimate border border-dark">
	<div class="contractor-thumbnail position-relative">
		<a class="thumbnail-image position-absolute w-100 h-100 start-0 top-0" href="<?=$external_url?>" target="_blank"><?php echo get_the_post_thumbnail( $contractor_id, 'full' ); ?></a>
	</div>
	<div class="contractor-info text-center px-1">
		<div class="contractor-title py-3 fs-5">
			<a class="d-block text-truncate" href="<?=$external_url?>" target="_blank" title="<?php echo esc_attr(get_the_title( $contractor_id )); ?>"><?php echo esc_html(get_the_title( $contractor_id )); ?></a>
			<div class="text-truncate fs-6 text-yellow"><?=esc_html($atts['category'])?></div>
		</div>
		<?php if($atts['value']) { ?>
		<div class="contractor-value mb-3">
			<span>Tổng giá trị:</span>
			<span class="text-red fw-bold"><?php echo esc_html(number_format($atts['value'],0,'.',',')); ?></span>
		</div>
		<?php } ?>
		<div class="d-flex flex-wrap justify-content-center contractor-links mb-3">
			<?php
			if($phone_number) {
				?>
				<a class="btn btn-sm btn-danger my-1 mx-2" href="tel:<?=esc_attr($phone_number)?>"><?=esc_html($phone_number)?></a>
				<?php
			}

			if($pdf) {
				?>
				<a class="btn btn-sm btn-primary my-1 mx-2" href="<?=esc_url($pdf)?>" target="_blank">Xem dự toán</a>
				<?php
			}
			?>
		</div>
	</div>
</div>
<?php

