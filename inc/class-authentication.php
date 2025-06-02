<?php
namespace HomeViet;

class Authentication {

	private static $instance = null;

	private function __construct() {

		add_filter( 'the_password_form', [$this, 'the_password_form'], 10, 2 );
		add_filter( 'post_password_required', [$this, 'post_password_required'], 10, 2 );

		add_filter( 'nonce_life', [$this, 'nonce_life'] );
	}

	public function nonce_life() {
		return 8 * DAY_IN_SECONDS;
	}

	public function post_password_required($required, $post) {
		global $current_password;

		$post = get_post( $post );
		
		$client = isset($_GET['client'])?get_term_by( 'id', absint($_GET['client']), 'passwords' ):null;

		if($post->post_type=="contractor_page") {
			$required = true;

			if($current_password || has_role('administrator') || has_role('viewer')) {
				$required = false;
			}
		} elseif(is_page_template( 'estimates.php' ) || is_page_template( 'estimate.php' ) || is_page_template( 'estimate-customer.php' )) {
			$required = true;

			if( has_role('administrator') || has_role('viewer') || ($current_password && ( ($client && $current_password->term_id == $client->term_id) || $current_password->term_id == get_option( 'default_term_passwords', -1 ))) ) {
				$required = false;
			}
		} elseif ( is_page_template( 'estimate-manage.php' ) || is_page_template( 'partner.php' ) || is_page_template( 'document.php' ) ) {
			$required = true;

			if( has_role('administrator') || ($current_password && ( ($client && $current_password->term_id == $client->term_id) || $current_password->term_id == get_option( 'default_term_passwords', -1 ))) ) {
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