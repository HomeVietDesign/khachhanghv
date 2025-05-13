<?php
namespace HomeViet;

class Head {

	private static $instance = null;

	private function __construct() {
		add_action('wp_head', [$this, 'head_scripts'], 50);
		add_action('wp_head', [$this, 'noindex'], 10);
	}


	public function noindex() {
		if(is_singular( 'contractor' ) || is_singular( 'contractor_page' ) || is_tax('contractor_cat')) {
		?>
		<meta name="robots" content="noindex, nofollow" />
		<?php
		}
	}

	public static function head_scripts() {

		$footer_bg_color = fw_get_db_settings_option('footer_bg_color', '#000');
		$footer_color = fw_get_db_settings_option('footer_color', '#fff');
		if($footer_bg_color) {
			?>
			<style type="text/css">
			#site-footer {
				background-color: <?=$footer_bg_color?>;
				color: <?=$footer_color?>;
			}
			</style>
			<?php
		}

		?>
		<style type="text/css">
			.grecaptcha-badge {
				right: -999999px!important;
			}

			/*@media (min-width: 576px) {
				
			}*/
		</style>
		<script type="text/javascript">
			window.addEventListener('DOMContentLoaded', function(){
				const root = document.querySelector(':root');
				root.style.setProperty('--site-header--height', document.getElementById('site-header').clientHeight+'px');
				window.addEventListener('resize', function(){
					root.style.setProperty('--site-header--height', document.getElementById('site-header').clientHeight+'px');
				});
			});
		</script>
		<?php
		$custom_script = fw_get_db_settings_option('head_code', '');
		if(''!=$custom_script) {
			echo $custom_script;
		}
		?>
		<!-- <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script> -->
		<?php

	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}

Head::instance();