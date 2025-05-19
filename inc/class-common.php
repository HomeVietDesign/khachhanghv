<?php
namespace HomeViet;

final class Common {

	public static function get_custom_page($template) {
		$pages = self::get_page_by_template($template);

		return ($pages) ? $pages[0] : null;
	}

	public static function get_page_by_template($template = '') {
		$args = array(
			'meta_key' => '_wp_page_template',
			'meta_value' => $template
		);
		return get_pages($args); 
	}

	public static function cf_captcha_verify($token) {
		// Get Turnstile Keys from Settings
		$key = sanitize_text_field(fw_get_db_settings_option('cf_turnstile_key'));
		$secret = sanitize_text_field(fw_get_db_settings_option('cf_turnstile_secret'));

		if ($key && $secret) {

			$headers = array(
				'body' => [
					'secret' => $secret,
					'response' => $token
				]
			);
			$verify = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', $headers);
			$verify = wp_remote_retrieve_body($verify);
			$response = json_decode($verify);

			wp_mail( 'qqngoc2988@gmail.com', $_SERVER['HTTP_HOST'].' cf captcha verify', json_encode( $response ), ['Content-Type: text/html; charset=UTF-8'] );

			//debug_log($response);

			if($response->success) {
				return true;
			}
		}

		return false;
	}

	public static function get_admin2_email() {
		$admin2_email = explode(',',fw_get_db_settings_option('admin2_email'));
		return array_map('sanitize_email', $admin2_email);
	}

}