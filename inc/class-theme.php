<?php
namespace HomeViet;

class Theme {

	private static $instance = null;

	private function __construct() {
	
		include_once THEME_DIR.'/inc/global-functions.php';
		include_once THEME_DIR.'/inc/unyson/class-unyson.php';
		include_once THEME_DIR.'/inc/class-api.php';
		include_once THEME_DIR.'/inc/admin/class-admin.php';

		include_once THEME_DIR.'/inc/class-custom-types.php';
		//include_once THEME_DIR.'/inc/class-background-process.php';

		if(class_exists('\\FileBird\\Plugin')) {
			include_once THEME_DIR.'/inc/filebird/class-filebird.php';
		}

		if(class_exists('\\FacebookPixelPlugin\\FacebookForWordpress')) {
			include_once THEME_DIR.'/inc/official-facebook-pixel/class-official-facebook-pixel.php';
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

		add_action('after_setup_theme', [$this, 'theme_activation']);
		//add_action('after_setup_theme', [$this, 'theme_deactivation']);
	}

	public function theme_activation() {
		
		//add_role( 'viewer', 'Xem một phần', array( 'read' => true, 'level_0' => true ) );

		/*
		$admin_role = get_role( 'administrator' );

		$admin_role->add_cap('edit_content_builder');
		$admin_role->add_cap('read_content_builder');
		$admin_role->add_cap('delete_content_builder');
		$admin_role->add_cap('edit_content_builders');
		$admin_role->add_cap('edit_others_content_builders');
		$admin_role->add_cap('delete_content_builders');
		$admin_role->add_cap('publish_content_builders');
		$admin_role->add_cap('read_private_content_builders');
		$admin_role->add_cap('delete_private_content_builders');
		$admin_role->add_cap('delete_published_content_builders');
		$admin_role->add_cap('delete_others_content_builders');
		$admin_role->add_cap('edit_private_content_builders');
		$admin_role->add_cap('edit_published_content_builders');

		$admin_role->add_cap('edit_contractor');
		$admin_role->add_cap('read_contractor');
		$admin_role->add_cap('delete_contractor');
		$admin_role->add_cap('edit_contractors');
		$admin_role->add_cap('edit_others_contractors');
		$admin_role->add_cap('delete_contractors');
		$admin_role->add_cap('publish_contractors');
		$admin_role->add_cap('read_private_contractors');
		$admin_role->add_cap('delete_private_contractors');
		$admin_role->add_cap('delete_published_contractors');
		$admin_role->add_cap('delete_others_contractors');
		$admin_role->add_cap('edit_private_contractors');
		$admin_role->add_cap('edit_published_contractors');

		$admin_role->add_cap('edit_contractor_page');
		$admin_role->add_cap('read_contractor_page');
		$admin_role->add_cap('delete_contractor_page');
		$admin_role->add_cap('edit_contractor_pages');
		$admin_role->add_cap('edit_others_contractor_pages');
		$admin_role->add_cap('delete_contractor_pages');
		$admin_role->add_cap('publish_contractor_pages');
		$admin_role->add_cap('read_private_contractor_pages');
		$admin_role->add_cap('delete_private_contractor_pages');
		$admin_role->add_cap('delete_published_contractor_pages');
		$admin_role->add_cap('delete_others_contractor_pages');
		$admin_role->add_cap('edit_private_contractor_pages');
		$admin_role->add_cap('edit_published_contractor_pages');

		$admin_role->add_cap('edit_estimate');
		$admin_role->add_cap('read_estimate');
		$admin_role->add_cap('delete_estimate');
		$admin_role->add_cap('edit_estimates');
		$admin_role->add_cap('edit_others_estimates');
		$admin_role->add_cap('delete_estimates');
		$admin_role->add_cap('publish_estimates');
		$admin_role->add_cap('read_private_estimates');
		$admin_role->add_cap('delete_private_estimates');
		$admin_role->add_cap('delete_published_estimates');
		$admin_role->add_cap('delete_others_estimates');
		$admin_role->add_cap('edit_private_estimates');
		$admin_role->add_cap('edit_published_estimates');

		$admin_role->add_cap('edit_partner');
		$admin_role->add_cap('read_partner');
		$admin_role->add_cap('delete_partner');
		$admin_role->add_cap('edit_partners');
		$admin_role->add_cap('edit_others_partners');
		$admin_role->add_cap('delete_partners');
		$admin_role->add_cap('publish_partners');
		$admin_role->add_cap('read_private_partners');
		$admin_role->add_cap('delete_private_partners');
		$admin_role->add_cap('delete_published_partners');
		$admin_role->add_cap('delete_others_partners');
		$admin_role->add_cap('edit_private_partners');
		$admin_role->add_cap('edit_published_partners');

		$admin_role->add_cap('edit_document');
		$admin_role->add_cap('read_document');
		$admin_role->add_cap('delete_document');
		$admin_role->add_cap('edit_documents');
		$admin_role->add_cap('edit_others_documents');
		$admin_role->add_cap('delete_documents');
		$admin_role->add_cap('publish_documents');
		$admin_role->add_cap('read_private_documents');
		$admin_role->add_cap('delete_private_documents');
		$admin_role->add_cap('delete_published_documents');
		$admin_role->add_cap('delete_others_documents');
		$admin_role->add_cap('edit_private_documents');
		$admin_role->add_cap('edit_published_documents');

		$admin_role->add_cap('edit_contract');
		$admin_role->add_cap('read_contract');
		$admin_role->add_cap('delete_contract');
		$admin_role->add_cap('edit_contracts');
		$admin_role->add_cap('edit_others_contracts');
		$admin_role->add_cap('delete_contracts');
		$admin_role->add_cap('publish_contracts');
		$admin_role->add_cap('read_private_contracts');
		$admin_role->add_cap('delete_private_contracts');
		$admin_role->add_cap('delete_published_contracts');
		$admin_role->add_cap('delete_others_contracts');
		$admin_role->add_cap('edit_private_contracts');
		$admin_role->add_cap('edit_published_contracts');

		$admin_role->add_cap('manage_contractor_cats');
		$admin_role->add_cap('edit_contractor_cats');
		$admin_role->add_cap('delete_contractor_cats');

		$admin_role->add_cap('manage_contractor_ratings');
		$admin_role->add_cap('edit_contractor_ratings');
		$admin_role->add_cap('delete_contractor_ratings');

		$admin_role->add_cap('manage_contractor_classs');
		$admin_role->add_cap('edit_contractor_classs');
		$admin_role->add_cap('delete_contractor_classs');

		$admin_role->add_cap('manage_passwordss');
		$admin_role->add_cap('edit_passwordss');
		$admin_role->add_cap('delete_passwordss');

		$admin_role->add_cap('manage_provinces');
		$admin_role->add_cap('edit_provinces');
		$admin_role->add_cap('delete_provinces');

		$admin_role->add_cap('manage_estimate_cats');
		$admin_role->add_cap('edit_estimate_cats');
		$admin_role->add_cap('delete_estimate_cats');

		$admin_role->add_cap('manage_partner_cats');
		$admin_role->add_cap('edit_partner_cats');
		$admin_role->add_cap('delete_partner_cats');

		$admin_role->add_cap('manage_document_cats');
		$admin_role->add_cap('edit_document_cats');
		$admin_role->add_cap('delete_document_cats');

		$admin_role->add_cap('manage_contract_cats');
		$admin_role->add_cap('edit_contract_cats');
		$admin_role->add_cap('delete_contract_cats');
		
		*/

		// $admin_role->add_cap('contractor_view');
		// $admin_role->add_cap('contractor_edit');

		// $admin_role->add_cap('estimate_contractor_view');
		// $admin_role->add_cap('estimate_contractor_edit');

		// $admin_role->add_cap('estimate_customer_view');
		// $admin_role->add_cap('estimate_customer_edit');

		// $admin_role->add_cap('estimate_manage_view');
		// $admin_role->add_cap('estimate_manage_edit');
		// $admin_role->add_cap('estimate_manage_du-toan-xay-dung_view'); // Xây dựng
		// $admin_role->add_cap('estimate_manage_du-toan-xay-dung_edit'); // Xây dựng
		// $admin_role->add_cap('estimate_manage_du-toan-do-go_view'); // đồ gỗ
		// $admin_role->add_cap('estimate_manage_du-toan-do-go_edit'); // đồ gỗ

		// $admin_role->add_cap('partner_view');
		// $admin_role->add_cap('partner_edit');

		// $admin_role->add_cap('document_view');
		// $admin_role->add_cap('document_edit');

		// $admin_role->add_cap('contract_view');
		// $admin_role->add_cap('contract_edit');
		
		/*-------------------------------------------*/
		// $ngochv = get_user_by( 'login', 'ngochv' );
		// $ngochv->add_cap('edit_contractors');
		// $ngochv->add_cap('edit_others_contractors');
		// $ngochv->add_cap('estimate_manage_view');
		// $ngochv->add_cap('estimate_manage_edit');
		// $ngochv->add_cap('estimate_manage_du-toan-do-go_view');
		// $ngochv->add_cap('estimate_manage_du-toan-do-go_edit');

		$tanhv = get_user_by( 'login', 'tanhv' );
		// $tanhv->add_cap('edit_contractor');
		// $tanhv->add_cap('read_contractor');
		// $tanhv->add_cap('delete_contractor');
		// $tanhv->add_cap('edit_contractors');
		// $tanhv->add_cap('edit_others_contractors');
		// $tanhv->add_cap('delete_contractors');
		// $tanhv->add_cap('publish_contractors');
		// $tanhv->add_cap('read_private_contractors');
		// $tanhv->add_cap('delete_private_contractors');
		// $tanhv->add_cap('delete_published_contractors');
		// $tanhv->add_cap('delete_others_contractors');
		// $tanhv->add_cap('edit_private_contractors');
		// $tanhv->add_cap('edit_published_contractors');

		// $tanhv->add_cap('estimate_contractor_view');
		// $tanhv->add_cap('estimate_contractor_edit');
		// $tanhv->add_cap('edit_posts');
		// $tanhv->add_cap('edit_others_posts');
		// $tanhv->add_cap('edit_published_posts');
		// $tanhv->add_cap('edit_pages');
		// $tanhv->add_cap('edit_others_pages');
		// $tanhv->add_cap('edit_published_pages');
		// $tanhv->add_cap('manage_categories');
		// $tanhv->add_cap('contractor_view');
		// $tanhv->add_cap('contractor_edit');

		// $thaotde = get_user_by( 'login', 'thaotde' );
		// $thaotde->add_cap('estimate_contractor_view');
		// $thaotde->add_cap('estimate_contractor_edit');
		
	}

	public function theme_deactivation() {
		/*
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

		$tanhv = get_user_by( 'login', 'tanhv' );
		$tanhv->remove_cap('estimate_contractor_view');
		$tanhv->remove_cap('estimate_contractor_edit');

		$thaotde = get_user_by( 'login', 'thaotde' );
		$thaotde->remove_cap('estimate_contractor_view');
		$thaotde->remove_cap('estimate_contractor_edit');
		*/

		$ngochv = get_user_by( 'login', 'ngochv' );debug_log($ngochv);
		// $ngochv->remove_cap('contractor_view');
		// $ngochv->remove_cap('contractor_edit');
		// $ngochv->remove_cap('estimate_manage_edit');
		// $ngochv->remove_cap('estimate_manage_du-toan-do-go_view');
		// $ngochv->remove_cap('estimate_manage_du-toan-do-go_edit');
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}

