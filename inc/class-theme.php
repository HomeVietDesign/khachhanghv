<?php
namespace HomeViet;

class Theme {

	private static $instance = null;

	private function __construct() {
	
		include_once THEME_DIR.'/inc/global-functions.php';
		include_once THEME_DIR.'/inc/unyson/class-unyson.php';
		include_once THEME_DIR.'/inc/admin/class-admin.php';

		include_once THEME_DIR.'/inc/class-custom-types.php';
		//include_once THEME_DIR.'/inc/class-background-process.php';

		if(class_exists('\\FileBird\\Plugin')) {
			include_once THEME_DIR.'/inc/filebird/class-filebird.php';
		}

		if(class_exists('\\FacebookPixelPlugin\\FacebookForWordpress')) {
			include_once THEME_DIR.'/inc/official-facebook-pixel/class-official-facebook-pixel.php';
		}

		if(class_exists('WPCF7_ContactForm')) {
			include_once THEME_DIR.'/inc/wpcf7/class-wpcf7.php';
		}

		if(class_exists('WP_Statistics')) {
			include_once THEME_DIR.'/inc/wp-statistics/class-wp-statistics.php';
		}

		include_once THEME_DIR.'/inc/class-authentication.php';
		
		if(unyson_exists()) {

			include_once THEME_DIR.'/inc/class-common.php';
			include_once THEME_DIR.'/inc/class-template-tags.php';
			include_once THEME_DIR.'/inc/class-setup.php';
			include_once THEME_DIR.'/inc/class-query.php';
			include_once THEME_DIR.'/inc/class-assets.php';
			include_once THEME_DIR.'/inc/class-ajax.php';
			include_once THEME_DIR.'/inc/class-head.php';
			include_once THEME_DIR.'/inc/class-body.php';
			include_once THEME_DIR.'/inc/class-header.php';
			include_once THEME_DIR.'/inc/class-footer.php';

			//include_once THEME_DIR.'/inc/class-shortcode.php';

			include_once THEME_DIR.'/inc/class-walker-primary-menu.php';
			include_once THEME_DIR.'/inc/class-walker-secondary-menu.php';

			// widgets
			include_once THEME_DIR.'/inc/class-widgets.php';

		}

		// add_action('after_switch_theme', [$this, 'theme_activation']);
		// add_action('switch_theme', [$this, 'theme_deactivation']);
	}

	public function theme_activation() {

	}

	public function theme_deactivation() {
		
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}

