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

		add_action('wp_ajax_estimate_paginate', [$this, 'ajax_estimate_paginate']);
		add_action('wp_ajax_nopriv_estimate_paginate', [$this, 'ajax_estimate_paginate']);

		add_action('wp_ajax_contractor_cat_hide_toggle', [$this, 'ajax_contractor_cat_hide_toggle']);
	}

	public function ajax_contractor_cat_hide_toggle() {
		global $current_client;

		$response = false;

		$cat = isset($_POST['cat']) ? absint($_POST['cat']) : 0;
		$checked = isset($_POST['checked']) ? absint($_POST['checked']) : 0;

		if($current_client && $cat && check_ajax_referer( 'global', 'nonce', false )) {
			$contractor_cat_hide = fw_get_db_term_option($current_client->term_id, 'passwords', 'contractor_cat_hide', []);
			if(empty($contractor_cat_hide)) {
				$contractor_cat_hide = [];
			}

			if($checked==1) {
				if(in_array($cat, $contractor_cat_hide)) {
					unset($contractor_cat_hide[array_search($cat,$contractor_cat_hide)]);
				}
			} else {
				if(!in_array($cat, $contractor_cat_hide)) {
					$contractor_cat_hide[] = $cat;
				}
			}
			fw_set_db_term_option($current_client->term_id, 'passwords', 'contractor_cat_hide', $contractor_cat_hide);

			$response = true;
		}

		wp_send_json($response);
	}

	public function ajax_estimate_paginate() {
		global $current_password;
		$contractors = $_GET['ids'];
		$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;
		if( has_role('administrator') || has_role('viewer') || ($current_password && ( ($client && $current_password->term_id == $client->term_id) || $current_password->term_id == get_option( 'default_term_passwords', -1 ))) ) {
			foreach($contractors as $contractor_id) {
				\FW_Shortcode_Estimates::display_contractor($contractor_id, $client);
			}
		}
		exit;
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