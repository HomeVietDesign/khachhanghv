<?php
namespace HomeViet;

class Head {

	private static $instance = null;

	private function __construct() {
		add_action('wp_head', [$this, 'head_scripts'], 50);
		add_action('wp_head', [$this, 'noindex'], 10);
	}


	public function noindex() {
		?>
		<meta name="robots" content="noindex, nofollow" />
		<?php
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
		</style>
		<script type="text/javascript">
			window.addEventListener('DOMContentLoaded', function(){
				const root = document.querySelector(':root');
				const site_header = document.getElementById('site-header');
				if(site_header) {
					root.style.setProperty('--site-header--height', site_header.clientHeight+'px');
					window.addEventListener('resize', function(){
						root.style.setProperty('--site-header--height', site_header.clientHeight+'px');
					});
				} else {
					root.style.setProperty('--site-header--height', '0');
				}
			});
		</script>
		<?php
		$custom_script = fw_get_db_settings_option('head_code', '');
		if(''!=$custom_script) {
			echo $custom_script;
		}

	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}

}

Head::instance();