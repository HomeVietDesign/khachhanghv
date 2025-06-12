<?php
namespace HomeViet;

final class Official_Facebook_Pixel {

	private static $instance = null;

	private function __construct() {
	
		include_once THEME_DIR.'/inc/official-facebook-pixel/class-facebook-timer.php';
	}


	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}

Official_Facebook_Pixel::instance();