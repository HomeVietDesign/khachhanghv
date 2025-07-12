<?php
namespace HomeViet;

class Ajax {

	private static $instance = null;

	private function __construct() {
		add_action('wp_ajax_contractor_search', [$this, 'ajax_contractor_search']);
		add_action('wp_ajax_nopriv_contractor_search', [$this, 'ajax_contractor_search']);

		add_action('wp_ajax_estimate_paginate', [$this, 'ajax_estimate_paginate']);
		add_action('wp_ajax_nopriv_estimate_paginate', [$this, 'ajax_estimate_paginate']);

	}

	public function ajax_estimate_paginate() {
		global $current_client;
		$contractors = $_GET['ids'];
		if( has_role('administrator') || has_role('viewer') ) {
			foreach($contractors as $contractor_id) {
				\FW_Shortcode_Estimates::display_contractor($contractor_id, $current_client);
			}
		}
		exit;
	}

	public function ajax_contractor_search() {
		?>
		<div class="text-center text-uppercase fw-bold fs-2 text-yellow py-3 bg-black">Kết quả tìm kiếm</div>
		<div class="contractor-search-response pb-3 container-xxl">
		<?php
		if(current_user_can('contractor_view')) {
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

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Ajax::instance();