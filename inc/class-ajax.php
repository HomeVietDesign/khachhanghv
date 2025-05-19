<?php
namespace HomeViet;

class Ajax {

	private static $instance = null;

	private function __construct() {

		add_action('wp_ajax_url_delete_cache', [$this, 'ajax_url_delete_cache']);
		add_action('wp_ajax_nopriv_url_delete_cache', [$this, 'ajax_url_delete_cache']);

		add_action('wp_ajax_contractor_search', [$this, 'ajax_contractor_search']);
		add_action('wp_ajax_nopriv_contractor_search', [$this, 'ajax_contractor_search']);

		// add_action('wp_ajax_statistic', [$this, 'ajax_statistic']);
		// add_action('wp_ajax_nopriv_statistic', [$this, 'ajax_statistic']);
	}

	public function ajax_statistic() {
		debug_log($_REQUEST);
		die;
	}

	public function ajax_contractor_search() {
		global $current_password;
		?>
		<div class="text-center text-uppercase fw-bold fs-2 text-yellow py-3 bg-black mb-3">Kết quả tìm kiếm</div>
		<div class="contractor-search-response py-3 container-xxl">
		<?php
		if(has_role('administrator') || $current_password) {
			$kw = isset($_REQUEST['kw'])?sanitize_text_field($_REQUEST['kw']):'';
			if($kw) {
				$search_result = wp_do_shortcode('contractors', ['number'=>12]);
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
		exit;
	}

	public function ajax_url_delete_cache() {
		$url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
		if($url) wp_remote_request($url, ['method'=>'PURGE']);
		exit;
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Ajax::instance();