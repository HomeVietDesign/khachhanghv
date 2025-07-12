<?php
get_header();

global $current_province;

while (have_posts()) {
	the_post();

	$cat = absint(get_post_meta( get_the_ID(), '_cat', true ));

    if(current_user_can('contractor_view')) {
	?>
	<div class="contractor-search-wrap">
		<div class="d-flex justify-content-center py-3">
			<div class="d-flex align-items-center">
					<div class="btn btn-primary d-none d-lg-block text-nowrap rounded-0">Tra cứu nhà thầu</div>
					<input type="hidden" id="contractor-search-province" value="<?php echo ($current_province)?$current_province->term_id:0; ?>">
				<input type="search" id="contractor-search-input" class="form-control rounded-0" placeholder="Nhập từ khóa tìm kiếm..." title="Nhập số điện thoại, tên, hoặc mô tả dịch vụ.">
			</div>
		</div>
		<div id="contractor-search-result-wrap">
			<div class="contractor-search-result invisible mb-3"></div>
			<div class="loading text-center invisible mb-3"></div>
		</div>
	</div>
	<?php } ?>
	<div class="page-header bg-black py-3 mb-4">
		<div class="container-xxl">
			<h2 class="page-title text-center text-uppercase fw-bold p-0 m-0 d-flex justify-content-center align-items-center">
				<span class="d-inline-block mx-1 text-yellow"><?php if($cat) { ?>Nhà thầu <?php } ?><?php the_title(); ?></span>
			</h2>
		</div>
	</div>
	<?php
	if($cat) {
		echo wp_do_shortcode('ratings_contractors', ['number'=>4, 'contractor_cat'=>[$cat]]);
	}
	the_content();

	wp_do_shortcode('contractors', ['number'=>0, 'contractor_cat'=>[0]]);
}

get_footer();