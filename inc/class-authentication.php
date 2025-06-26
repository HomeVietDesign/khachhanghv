<?php
namespace HomeViet;

class Authentication {

	private static $instance = null;

	private function __construct() {

		add_action( 'parse_request', [$this, 'require_login_use'] );

		//add_filter( 'the_password_form', [$this, 'the_password_form'], 10, 2 );
		//add_filter( 'post_password_required', [$this, 'post_password_required'], 10, 2 );

		add_filter( 'nonce_life', [$this, 'nonce_life'] );
	}

	public function require_login_use($wp) {
		if(!is_user_logged_in()) { // bắt buộc đăng nhập để truy cập hệ thống
			// chuyển hướng sang trang đăng nhập
			wp_redirect(wp_login_url(fw_current_url()));
			exit;
		}
	}

	public function nonce_life() {
		return 2 * DAY_IN_SECONDS;
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