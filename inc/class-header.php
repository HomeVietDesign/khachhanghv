<?php
namespace HomeViet;

class Header {

	private static $instance = null;

	private function __construct() {
		add_action( 'template_redirect', [$this, 'display_header_html'] );
		
	}

	public function site_header() {
		global $current_password;

		if( has_role('administrator') || has_role('viewer') || ( $current_password && $current_password->term_id == get_option( 'default_term_passwords', -1 ) ) ) {
		?>
		<header id="site-header" class="position-sticky">
			<?php self::primary_menu(); ?>
		</header>
		<?php
		}
	}

	public static function contractor_menu($menu_html='') {
		global $view;

		$parent = null;
		if($view->post_parent>0) {
			$parent = get_post($view->post_parent);
		}

		$contractor_cats = get_terms([
			'taxonomy' => 'contractor_cat',
			'hide_empty' => false,
			//'hierarchical' => true,
			'parent' => 0
		]);
		
		ob_start();

		if($contractor_cats) {
		?>
		<nav id="main-nav">
			<div class="main-nav-inner">
				<ul class="menu d-flex flex-wrap justify-content-center">
				<?php
				foreach ($contractor_cats as $key => $value) {
					$children = get_terms([
						'taxonomy' => 'contractor_cat',
						'hide_empty' => false,
						'parent' => $value->term_id
					]);
					//debug($children);

					if($children) {
						$page_id = get_term_meta( $value->term_id, '_page', true );
					?>
					<li class="menu-item menu-item-has-children d-flex position-relative align-items-center<?php
					if($parent && $parent->ID==$page_id) {
						echo ' current-menu-ancestor current-menu-parent';
					} elseif ($view->ID==$page_id) {
						echo ' current-menu-item';
					}
					?>">
						<a href="<?php echo esc_url(get_permalink($page_id)); ?>"><?=esc_html($value->name)?></a>
						<a href="javascript:void(0);" class="toggle-sub-menu d-flex align-items-center"><span class="dashicons dashicons-arrow-down-alt2"></span></a>
						<ul class="sub-menu position-absolute">
						<?php
						foreach ($children as $child) {
							$page_id = get_term_meta( $child->term_id, '_page', true );
							?>
							<li class="menu-item<?php
							if ($view->ID==$page_id) {
								echo ' current-menu-item';
							}
							?>">
								<a href="<?php echo esc_url(get_permalink($page_id)); ?>"><?=esc_html($child->name)?></a>
							</li>
							<?php
						}
						?>
						</ul>
					</li>
					<?php
					}
				}

				echo $menu_html;
				?>
				</ul>
			</div>
		</nav>
		<?php
		}

		return ob_get_clean();
	}

	public static function primary_menu() {
		global $current_password, $current_client;

		$object = get_queried_object();
		$display_menu = 'yes';
		$menu = false;
		$nav_menu = '';

		$default_term_passwords = (int) get_option( 'default_term_passwords', -1 );
		$passwords = get_terms([
			'taxonomy' => 'passwords',
			'hide_empty' => false,
			'exclude' => [$default_term_passwords],
		]);

		$menu_html = '';

		if($passwords) {
		//if($passwords && !(is_singular('contractor_page') || is_home() || is_front_page())) {
			$estimate_page = Common::get_custom_page('estimate.php');
			if($estimate_page) {
				$estimate_page_url = get_permalink($estimate_page);
				$this_template = is_page_template('estimate.php') ? true : false;
				$menu_html .= '<li class="menu-item menu-item-has-children d-flex position-relative align-items-center';
				if($this_template) {
					$menu_html .= ' current-menu-ancestor current-menu-parent';
				}
				$menu_html .= '">';
				$menu_html .= '<a href="#">'.esc_html($estimate_page->post_title).'</a>';
				$menu_html .= '<a href="javascript:void(0)" class="toggle-sub-menu d-flex align-items-center"><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
				$menu_html .= '<ul class="sub-menu position-absolute">';
				foreach ($passwords as $key => $value) {
					$menu_html .= '<li class="menu-item';
					$menu_html .= ($this_template && $current_client && $value->term_id==$current_client->term_id)?' current-menu-item':'';
					$menu_html .= '">';
					$menu_html .= '<a href="'.esc_url($estimate_page_url).'?client='.absint($value->term_id).'">'.esc_html($value->description).'</a>';
					$menu_html .= '</li>';
				}
				$menu_html .= '</ul>';
				$menu_html .= '</li>';
			}

			$estimate_customer_page = Common::get_custom_page('estimate-customer.php');
			if($estimate_customer_page) {
				$estimate_customer_page_url = get_permalink($estimate_customer_page);
				$this_template = is_page_template('estimate-customer.php') ? true : false;
				$menu_html .= '<li class="menu-item menu-item-has-children d-flex position-relative align-items-center';
				if($this_template) {
					$menu_html .= ' current-menu-ancestor current-menu-parent';
				}
				$menu_html .= '">';
				$menu_html .= '<a href="#">'.esc_html($estimate_customer_page->post_title).'</a>';
				$menu_html .= '<a href="javascript:void(0)" class="toggle-sub-menu d-flex align-items-center"><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
				$menu_html .= '<ul class="sub-menu position-absolute">';
				foreach ($passwords as $key => $value) {
					$menu_html .= '<li class="menu-item';
					$menu_html .= ($this_template && $current_client && $value->term_id==$current_client->term_id)?' current-menu-item':'';
					$menu_html .= '">';
					$menu_html .= '<a href="'.esc_url($estimate_customer_page_url).'?client='.absint($value->term_id).'">'.esc_html($value->description).'</a>';
					$menu_html .= '</li>';
				}
				$menu_html .= '</ul>';
				$menu_html .= '</li>';
			}

			if(has_role('administrator') || ($current_password && $current_password->term_id == get_option( 'default_term_passwords', -1 )) ) {
				$estimate_manage_page = Common::get_custom_page('estimate-manage.php');
				if($estimate_manage_page) {
					$estimate_manage_page_url = get_permalink($estimate_manage_page);
					$this_template = is_page_template('estimate-manage.php') ? true : false;
					$menu_html .= '<li class="menu-item menu-item-has-children d-flex position-relative align-items-center';
					if($this_template) {
						$menu_html .= ' current-menu-ancestor current-menu-parent';
					}
					$menu_html .= '">';
					$menu_html .= '<a href="#">'.esc_html($estimate_manage_page->post_title).'</a>';
					$menu_html .= '<a href="javascript:void(0)" class="toggle-sub-menu d-flex align-items-center"><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
					$menu_html .= '<ul class="sub-menu position-absolute">';
					foreach ($passwords as $key => $value) {
						$menu_html .= '<li class="menu-item';
						$menu_html .= ($this_template && $current_client && $value->term_id==$current_client->term_id)?' current-menu-item':'';
						$menu_html .= '">';
						$menu_html .= '<a href="'.esc_url($estimate_manage_page_url).'?client='.absint($value->term_id).'">'.esc_html($value->description).'</a>';
						$menu_html .= '</li>';
					}
					$menu_html .= '</ul>';
					$menu_html .= '</li>';
				}

				$partner_page = Common::get_custom_page('partner.php');
				if($partner_page) {
					$partner_page_url = get_permalink($partner_page);
					$this_template = is_page_template('partner.php') ? true : false;
					$menu_html .= '<li class="menu-item menu-item-has-children d-flex position-relative align-items-center';
					if($this_template) {
						$menu_html .= ' current-menu-ancestor current-menu-parent';
					}
					$menu_html .= '">';
					$menu_html .= '<a href="#">'.esc_html($partner_page->post_title).'</a>';
					$menu_html .= '<a href="javascript:void(0)" class="toggle-sub-menu d-flex align-items-center"><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
					$menu_html .= '<ul class="sub-menu position-absolute">';
					foreach ($passwords as $key => $value) {
						$menu_html .= '<li class="menu-item';
						$menu_html .= ($this_template && $current_client && $value->term_id==$current_client->term_id)?' current-menu-item':'';
						$menu_html .= '">';
						$menu_html .= '<a href="'.esc_url($partner_page_url).'?client='.absint($value->term_id).'">'.esc_html($value->description).'</a>';
						$menu_html .= '</li>';
					}
					$menu_html .= '</ul>';
					$menu_html .= '</li>';
				}

				$document_page = Common::get_custom_page('document.php');
				if($document_page) {
					$document_page_url = get_permalink($document_page);
					$this_template = is_page_template('document.php') ? true : false;
					$menu_html .= '<li class="menu-item menu-item-has-children d-flex position-relative align-items-center';
					if($this_template) {
						$menu_html .= ' current-menu-ancestor current-menu-parent';
					}
					$menu_html .= '">';
					$menu_html .= '<a href="#">'.esc_html($document_page->post_title).'</a>';
					$menu_html .= '<a href="javascript:void(0)" class="toggle-sub-menu d-flex align-items-center"><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
					$menu_html .= '<ul class="sub-menu position-absolute">';
					foreach ($passwords as $key => $value) {
						$menu_html .= '<li class="menu-item';
						$menu_html .= ($this_template && $current_client && $value->term_id==$current_client->term_id)?' current-menu-item':'';
						$menu_html .= '">';
						$menu_html .= '<a href="'.esc_url($document_page_url).'?client='.absint($value->term_id).'">'.esc_html($value->description).'</a>';
						$menu_html .= '</li>';
					}
					$menu_html .= '</ul>';
					$menu_html .= '</li>';
				}
			}

		}

		if( is_singular( 'contractor' ) || is_singular( 'contractor_page' ) ) {
			$estimates_page = Common::get_custom_page('estimates.php');
			$estimates_menu = '';
			if($estimates_page) {
				$estimates_page_url = get_permalink($estimates_page);
					$this_template = is_page_template('estimates.php') ? true : false;
					$estimates_menu .= '<li class="menu-item menu-item-has-children d-flex position-relative align-items-center';
					if($this_template) {
						$estimates_menu .= ' current-menu-ancestor current-menu-parent';
					}
					$estimates_menu .= '">';
					$estimates_menu .= '<a href="'.esc_url($estimates_page_url).'">'.esc_html($estimates_page->post_title).'</a>';
					$estimates_menu .= '</li>';
			}
			
			$contractor_menu = self::contractor_menu(($estimates_menu!='')?$estimates_menu:$menu_html);
			echo $contractor_menu;

			return;
		} elseif(is_page() || is_single()) {
			$display_menu = fw_get_db_post_option($object->ID, 'display_menu', 'yes');
			$menu = fw_get_db_post_option($object->ID, 'apply_menu');
		} else if(is_category() || is_tax()) {
			$display_menu = fw_get_db_term_option($object->term_id, $object->taxonomy, 'display_menu', 'yes');
			$menu = fw_get_db_term_option($object->term_id, $object->taxonomy, 'apply_menu');
		}

		if($display_menu=='yes') {
			$obj_menu = ($menu) ? wp_get_nav_menu_object( $menu[0] ): false;
			if($obj_menu) {
				$nav_menu = wp_nav_menu([
					'menu' => $obj_menu,
					'container' => false,
					'echo' => false,
					'fallback_cb' => '',
					'depth' => 2,
					'walker' => new \HomeViet\Walker_Primary_Menu(),
					'items_wrap' => '<ul class="%2$s d-flex flex-wrap justify-content-center">%3$s'.$menu_html.'</ul>',
				]);

			} else if(has_nav_menu('primary')) {
				$nav_menu = wp_nav_menu([
					'theme_location' => 'primary',
					'container' => false,
					'echo' => false,
					'fallback_cb' => '',
					'depth' => 2,
					'walker' => new \HomeViet\Walker_Primary_Menu(),
					'items_wrap' => '<ul class="%2$s d-flex flex-wrap justify-content-center">%3$s'.$menu_html.'</ul>',
				]);
			}

			
			if($nav_menu!='') {
				?>
				<nav id="main-nav">
					<div class="main-nav-inner"><?php echo $nav_menu; ?></div>
				</nav>
				<?php
			}
			
		}
	
	}

	public function display_header_html() {
		global $popup;
		if( !$popup ) {
			add_action('wp_body_open', [$this, 'site_header'], 10);
		}
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Header::instance();