<?php
namespace HomeViet;

class Background_Process {

	private static $instance = null;

	private function __construct() {

		

	}

	

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Background_Process::instance();