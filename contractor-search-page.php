<?php
get_header();
?>
<div class="contractor-search-response pb-3 container-xxl">
<?php
if(current_user_can('contractor_view')) {
	$kw = isset($_GET['kw'])?sanitize_text_field($_GET['kw']):'';
	if($kw) {
		$search_result = wp_do_shortcode('ratings_contractors', ['number'=>12]);
		if($search_result) {
			echo $search_result;
		} else {
			echo '<div class="py-4 text-center">Không có kết quả nào được tìm thấy.</div>';
		}
	} else {
		echo '<div class="py-4 text-center">Nhập từ khóa để tra cứu.</div>';
	}
} else {
	echo '<div class="py-4 text-center">Forbidden.</div>';
}
?>
</div>
<?php
get_footer()
?>