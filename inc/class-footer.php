<?php
namespace HomeViet;

class Footer {
	private static $instance = null;

	private function __construct() {
		add_action( 'template_redirect', [$this, 'display_footer_html'] );
		add_action( 'wp_footer', [$this, 'custom_scripts'], 100 );

		// remove_action( 'wp_footer', 'snow_fall_print_web_component', 100 );
		// add_action( 'wp_footer', [$this, 'snow_fall_print_web_component'], 100 );
	}

	public function snow_fall_print_web_component() {
		?>
		<is-land on:media="(prefers-reduced-motion: no-preference)" on:idle>
			<snow-fall text="❄︎" style="--snow-fall-color: rebeccapurple"></snow-fall>
		</is-land>
		<?php
	}

	public function site_footer() {
	
		?>
		<footer id="site-footer" class="py-5">
			<?php \HomeViet\Footer::display_widgets(); ?>
		</footer>
		<?php
	}


	public function custom_scripts() {
		$custom_script = fw_get_db_settings_option('footer_code', '');
		if(''!=$custom_script) {
			echo $custom_script;
		}
		?>
		
		<?php
	}


	public static function display_widgets() {
		?>
		<div class="site-footer-inner container-xl">
			<div class="row">
				<?php if(is_active_sidebar( 'footer-1' )) { ?>
				<div class="site-footer-col col-lg-4 py-3">
					<div class="col-inner"><?php dynamic_sidebar('footer-1'); ?></div>
				</div>
				<?php } ?>
				<?php if(is_active_sidebar( 'footer-2' )) { ?>
				<div class="site-footer-col col-lg-4 py-3">
					<div class="col-inner"><?php dynamic_sidebar('footer-2'); ?></div>
				</div>
				<?php } ?>
				<?php if(is_active_sidebar( 'footer-3' )) { ?>
				<div class="site-footer-col col-lg-4 py-3">
					<div class="col-inner"><?php dynamic_sidebar('footer-3'); ?></div>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	public function display_footer_html() {
		global $popup;
		if( !$popup ) {
			add_action('wp_footer', [$this, 'site_footer'], 10);
			//add_action('wp_footer', [$this, 'logout_post_password'], 15);
		}
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Footer::instance();