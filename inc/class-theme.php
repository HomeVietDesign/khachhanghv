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

		add_action('after_switch_theme', [$this, 'theme_activation']);
		add_action('switch_theme', [$this, 'theme_deactivation']);
	}

	public function theme_activation() {
		add_role( 'viewer', 'Xem một phần', array( 'read' => true, 'level_0' => true ) );

		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap('contractor_view');
		$admin_role->add_cap('contractor_edit');
		$admin_role->add_cap('estimate_contractor_view');
		$admin_role->add_cap('estimate_contractor_edit');
		$admin_role->add_cap('estimate_customer_view');
		$admin_role->add_cap('estimate_customer_edit');
		$admin_role->add_cap('estimate_manage_view');
		$admin_role->add_cap('estimate_manage_edit');
		$admin_role->add_cap('partner_view');
		$admin_role->add_cap('partner_edit');
		$admin_role->add_cap('document_view');
		$admin_role->add_cap('document_edit');
		
		$sangtran = get_user_by( 'login', 'sangtran' );
		$sangtran->add_cap('estimate_contractor_view');
		$sangtran->add_cap('estimate_contractor_edit');

		$sangtran = get_user_by( 'login', 'tanhv' );
		$sangtran->add_cap('estimate_contractor_view');
		$sangtran->add_cap('estimate_contractor_edit');

		$sangtran = get_user_by( 'login', 'thaotde' );
		$sangtran->add_cap('estimate_contractor_view');
		$sangtran->add_cap('estimate_contractor_edit');
	}

	public function theme_deactivation() {
		remove_role( 'viewer' );

		$admin_role = get_role( 'administrator' );
		$admin_role->remove_cap('contractor_view');
		$admin_role->remove_cap('contractor_edit');
		$admin_role->remove_cap('estimate_contractor_view');
		$admin_role->remove_cap('estimate_contractor_edit');
		$admin_role->remove_cap('estimate_customer_view');
		$admin_role->remove_cap('estimate_customer_edit');
		$admin_role->remove_cap('estimate_manage_view');
		$admin_role->remove_cap('estimate_manage_edit');
		$admin_role->remove_cap('partner_view');
		$admin_role->remove_cap('partner_edit');
		$admin_role->remove_cap('document_view');
		$admin_role->remove_cap('document_edit');

		$sangtran = get_user_by( 'login', 'sangtran' );
		$sangtran->remove_cap('estimate_contractor_view');
		$sangtran->remove_cap('estimate_contractor_edit');

		$sangtran = get_user_by( 'login', 'tanhv' );
		$sangtran->remove_cap('estimate_contractor_view');
		$sangtran->remove_cap('estimate_contractor_edit');

		$sangtran = get_user_by( 'login', 'thaotde' );
		$sangtran->remove_cap('estimate_contractor_view');
		$sangtran->remove_cap('estimate_contractor_edit');
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}

