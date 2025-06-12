<?php
namespace HomeViet;

class Authentication {

	private static $instance = null;

	private function __construct() {

		add_action( 'parse_request', [$this, 'require_login_use'] );

		//add_filter( 'the_password_form', [$this, 'the_password_form'], 10, 2 );
		//add_filter( 'post_password_required', [$this, 'post_password_required'], 10, 2 );

		add_filter( 'nonce_life', [$this, 'nonce_life'] );

		//add_action( 'init', [$this, 'custom_capability'] );
	}

	public function require_login_use($wp) {
		if(!is_user_logged_in()) { // bắt buộc đăng nhập để truy cập hệ thống
			// chuyển hướng sang trang đăng nhập
			wp_redirect(wp_login_url(fw_current_url()));
			exit;
		}
	}

	public function custom_capability() {
		// $nha88 = get_user_by( 'login', 'nha88' );
		// $nha88->remove_cap('contractor_view');
		// $nha88->remove_cap('estimate_contractor_view');
		// $nha88->remove_cap('estimate_contractor_edit');
		// $nha88->remove_cap('estimate_customer_view');
		
		// $ngochv = get_user_by( 'login', 'ngochv' );
		// $ngochv->remove_cap('contractor_view');
		// $ngochv->remove_cap('estimate_contractor_view');
		// $ngochv->remove_cap('estimate_customer_view');
		// $ngochv->remove_cap('estimate_customer_edit');
		// $ngochv->remove_cap('estimate_manage_view');
		// $ngochv->remove_cap('estimate_manage_edit');
		// $ngochv->remove_cap('partner_view');
		// $ngochv->remove_cap('document_view');

		// $admin_role = get_role( 'administrator' );
		// $admin_role->add_cap('contractor_view');
		// $admin_role->add_cap('contractor_edit');
		// $admin_role->add_cap('estimate_contractor_view');
		// $admin_role->add_cap('estimate_contractor_edit');
		// $admin_role->add_cap('estimate_customer_view');
		// $admin_role->add_cap('estimate_customer_edit');
		// $admin_role->add_cap('estimate_manage_view');
		// $admin_role->add_cap('estimate_manage_edit');
		// $admin_role->add_cap('partner_view');
		// $admin_role->add_cap('partner_edit');
		// $admin_role->add_cap('document_view');
		// $admin_role->add_cap('document_edit');
		
	}

	public function nonce_life() {
		return 2 * DAY_IN_SECONDS;
	}

	public function post_password_required($required, $post) {
		global $current_password;

		$post = get_post( $post );
		
		$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;
		$default_term_password = get_option( 'default_term_passwords', -1 );

		if( $post->post_type=="contractor_page" || is_singular('contractor_page') ) { // trang danh sách nhà thầu theo hạng mục
			$required = true;

			//if( $current_password || current_user_can( 'contractor_view' ) ) { // khách hàng và nhà quản lý có quyền
			if( current_user_can( 'contractor_view' ) ) { // Nhà quản lý có quyền
				$required = false;
			}

		} elseif( is_page_template( 'estimates.php' ) ) { // trang chủ dự toán
			$required = true;
			if( has_role('administrator') || has_role('viewer') || ($current_password &&  $current_password->term_id == $default_term_password) ) { // nhà quản lý | khách hàng mặc định
				$required = false;
			}
		} elseif( is_page_template( 'estimate.php' ) ) { // trang dự toán nhà thầu
			$required = true;
			if( current_user_can('estimate_contractor_view') 
				|| ( $current_password && $current_password->term_id == $default_term_password ) 
			) {  // nhà quản lý có quyền | khách hàng mặc định
				$required = false;
			}
		} elseif( is_page_template( 'estimate-customer.php' ) ) { // trang dự toán khách hàng
			$required = true;
			if( current_user_can('estimate_customer_view') || ( $current_password && ( ($client && $current_password->term_id == $client->term_id) || $current_password->term_id == $default_term_password ) ) ) { // nhà quản lý có quyền | khách hàng | khách hàng mặc định
				$required = false;
			}
		} elseif ( is_page_template( 'estimate-manage.php' ) ) {
			$required = true;
			if( current_user_can('estimate_manage_view') || ( $current_password && $current_password->term_id == $default_term_password ) ) { // nhà quản lý có quyền | khách hàng mặc định
				$required = false;
			}
		} elseif ( is_page_template( 'partner.php' ) ) {
			$required = true;
			if( current_user_can('partner_view') || ( $current_password && $current_password->term_id == $default_term_password ) ) { // nhà quản lý có quyền | khách hàng mặc định
				$required = false;
			}
		} elseif ( is_page_template( 'document.php' ) ) {
			$required = true;
			if( current_user_can('document_view') || ( $current_password && $current_password->term_id == $default_term_password ) ) { // nhà quản lý có quyền | khách hàng mặc định
				$required = false;
			}
		}

		return $required;
	}

	public static function check($contractor_page) {
		global $current_password;
		if($current_password) {
			return true;
		}
		return false;
	}

	public function the_password_form($output, $post) {
		
		ob_start();
		?>
		<form action="<?=home_url( 'wp-login.php?action=postpass' )?>" class="post-password-form text-center py-4 px-3" method="post">
			<div class="d-inline-block">
				<div class="my-3">Nội dung này được bảo mật. Hãy nhập mật khẩu để xem tiếp:</div>
				<div class="input-group mb-3">
				  <input name="post_password" type="password" class="form-control" spellcheck="false">
				  <button class="btn btn-primary" type="submit">Nhập</button>
				</div>
				<?php if(!is_user_logged_in()) { ?>
				<p>Hoặc - <a href="<?php echo esc_url(wp_login_url( fw_current_url() )); ?>">Đăng nhập quản lý</a></p>
				<?php } ?>
			</div>
		</form>
		<?php
		$output = ob_get_clean();
		return $output;
	}


	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}
Authentication::instance();