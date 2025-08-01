<?php
namespace HomeViet;

class Scheduler {

	private static $instance = null;

	private function __construct() {
		
		add_action('init', [$this, 'schedule'], 20);

		add_action('cleanup_temp_files', [$this, 'cleanup_temp_files']);
	}

	public function cleanup_temp_files() {
		$upload_dir = wp_upload_dir();
		$tmp_dir = $upload_dir['basedir'] . '/chunk_temp/';

		if (!is_dir($tmp_dir)) {
			return;
		}

		$files = glob($tmp_dir . '*');

		foreach ($files as $file) {
			// Xóa file nếu cũ hơn 24 giờ
			if (is_file($file) && filemtime($file) < time() - DAY_IN_SECONDS) {
				@unlink($file);
			}
		}
	}

	public function schedule() {
		if (!as_has_scheduled_action('cleanup_temp_files')) {
			as_schedule_cron_action(
				strtotime('tomorrow 02:00', current_time('timestamp')), // lúc 2h sáng giờ Việt Nam
            	'0 2 * * *',                                            // cron biểu thức: 2h sáng
				'cleanup_temp_files',    // Hook tên tác vụ
				[],                       // Không cần tham số
				'upload',
				true
			);
		}
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Scheduler::instance();