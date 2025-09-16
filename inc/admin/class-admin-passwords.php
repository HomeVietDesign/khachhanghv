<?php
namespace HomeViet;

class Admin_Passwords {

	private static $instance = null;

	private function __construct() {

		if(is_admin()) {
			
			//require_once THEME_DIR.'/inc/admin/class-walker-passwords-checklist.php';

			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );

			add_action( 'created_passwords', [$this, 'auto_slug'] );

			add_filter('manage_edit-passwords_columns', [$this, 'custom_column_header']);
			add_filter('manage_passwords_custom_column', [$this, 'custom_column_value'], 10, 3);

			// add_filter( 'wp_terms_checklist_args', [$this, 'change_passwords_check_list'], 10, 1 );
			// add_filter( 'post_column_taxonomy_links', [$this, 'change_passwords_columns_links'], 10, 3 );

			add_action( 'delete_passwords', [$this, 'delete_contractor_order'], 10, 4 );
			add_action( 'created_passwords', [$this, 'create_default_contractor_order_term'] );

			//add_action( 'passwords_add_form_fields', [$this, 'passwords_add_form_fields'] );
			//add_action( 'passwords_edit_form_fields', [$this, 'passwords_edit_form_fields'] );

			add_action( 'edited_passwords', [$this, 'save_passwords_meta'] );

			
		}
		
		add_action( 'delete_contractor_order', [$this, 'async_delete_contractor_order'] );
		add_action( 'create_default_contractor_order_term', [$this, 'async_create_default_contractor_order_term'] );
	}

	public function save_passwords_meta($term_id) {
		if(isset($_POST['econstruction'])) {
			$econstruction =  $_POST['econstruction'];
			update_term_meta( $term_id, 'econstruction', $econstruction );
		}
		
		if(isset($_POST['efurniture'])) {
			$efurniture =  $_POST['efurniture'];
			update_term_meta( $term_id, 'efurniture', $efurniture );
		}
	}

	public function passwords_edit_form_fields($term) {
		//$data = get_term_meta( $term->term_id, 'econstruction', true );
		$econstruction = fw_get_db_term_option($term->term_id, 'passwords', 'econstruction', []);
		$efurniture = fw_get_db_term_option($term->term_id, 'passwords', 'efurniture', []);

		// $econstruction = get_term_meta( $term->term_id, 'econstruction', true );
		// $efurniture = get_term_meta( $term->term_id, 'efurniture', true );
		
		//debug($econstruction);
		//debug($efurniture);
		?>
		<tr class="form-field">
			<th scope="row"><label for="econstruction">econstruction</label></th>
			<td>
				<?php
				if($econstruction) {
					foreach ($econstruction as $key => $value) {
						if(!empty($value)) {
							foreach ($value as $key2 => $value2) {
							?>
							<input type="text" name="econstruction[<?=$key?>][<?=$key2?>]" value="<?=esc_attr($value2)?>">
							<?php
							}
						}
					}
				}
				?>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="econstruction">efurniture</label></th>
			<td>
				<?php
				if($efurniture) {
					foreach ($efurniture as $key => $value) {
						if(!empty($value)) {
							foreach ($value as $key2 => $value2) {
							?>
							<input type="text" name="efurniture[<?=$key?>][<?=$key2?>]" value="<?=esc_attr($value2)?>">
							<?php
							}
						}
					}
				}
				?>
			</td>
		</tr>
		<?php
	}

	public function passwords_add_form_fields() {
		?>
		<div class="form-field">
			<label for="term_econstruction">Econstruction</label>
			<textarea name="term_econstruction" id="term_econstruction"></textarea>
			<p class="description"></p>
		</div>
		<?php
	}

	public function async_create_default_contractor_order_term($term_id) {
		$contractors = get_posts([
			'post_type' => 'contractor',
			'numberposts' => -1,
			'nopaging' => true,
			'no_found_rows' => true,
			'post_status' => 'any',
			'fields' => 'ids',
		]);
		if($contractors) {
			foreach ($contractors as $key => $post_id) {
				update_post_meta($post_id, 'term_orderby_'.$term_id, 0);
			}
			
		}
	}

	public function create_default_contractor_order_term($term_id) {
		if(function_exists('as_enqueue_async_action')) {
			as_enqueue_async_action( 'create_default_contractor_order_term', ['term_id'=>$term_id], 'passwords', false );
		}
	}

	public function async_delete_contractor_order($term_id) {
		global $wpdb;
		$table = _get_meta_table( 'post' );
		if ( $table ) {
			$wpdb->query("DELETE FROM {$table} WHERE meta_key='term_orderby_{$term_id}'");
		}
	}

	public function delete_contractor_order($term_id, $tt_id, $deleted_term, $object_ids) {
		/*
		global $wpdb;
		$table = _get_meta_table( 'post' );
		if ( $table ) {
			$wpdb->query("DELETE FROM {$table} WHERE meta_key='term_orderby_{$term_id}'");
		}
		*/
		if(function_exists('as_enqueue_async_action')) {
			// sử dụng tiến trình nền để xử lý, không cần phải sử lý luôn
			as_enqueue_async_action( 'delete_contractor_order', ['term_id'=>$term_id], 'passwords', false );
		}
	}

	public function change_passwords_columns_links($term_links, $taxonomy, $terms) {

		if($taxonomy=='passwords') {

			$term_links = array();
			$max = count($terms)-1;
			foreach ( $terms as $key => $t ) {
				if($key==0) {
					$term_links[] = '<span class="term-pass">'.esc_html($t->description.' ('.$t->name.')').'</span><span class="sep">';
				} elseif($key==$max) {
					$term_links[] = '</span><span class="term-pass">'.esc_html($t->description.' ('.$t->name.')').'</span>';
				} else {
					$term_links[] = '</span><span class="term-pass">'.esc_html($t->description.' ('.$t->name.')').'</span><span class="sep">';
				}
				
			}

		}

		return $term_links;	
	}

	public function change_passwords_check_list($args) {
		//debug_log($args);
		if($args['taxonomy']=='passwords') {
			$args['walker'] = new \Walker_Passwords_Checklist();
		}

		return $args;
	}

	public function custom_column_value($value, $column_name, $term_id) {
		switch ($column_name) {
			case 'external_url':

				$value .= '<input type="text" value="'.esc_attr(get_term_meta($term_id,'external_url', true)).'" class="external_url" data-id="'.absint($term_id).'" data-nonce="'.esc_attr(wp_create_nonce('change_external_url_'.$term_id)).'">';
				break;
			case 'province':
				$province = get_term_meta($term_id, 'province', true);
				if(!empty($province)) {
					$value .= esc_html(get_term_field( 'name', absint($province[0]), 'province' ));
				}
				break;
			default:
				// code...
				break;
		}
		return $value;
	}

	public function custom_column_header($columns) {
		if(isset($columns['name'])) {
			$columns['name'] = 'Tên gọi';
		}
		if(isset($columns['description'])) {
			$columns['description'] = 'Số điện thoại';
		}
		if(isset($columns['slug'])) {
			unset($columns['slug']);
		}
		if(isset($columns['posts'])) {
			unset($columns['posts']);
		}
		$columns['province'] = 'Tỉnh thành';
		
		return $columns;
	}

	public function auto_slug($term_id) {
		global $wpdb;
		$wpdb->update( $wpdb->terms, ['slug' => current_time( 'U' )], ['term_id' => $term_id] );
		wp_cache_delete( $term_id, 'terms' );
	}

	public function enqueue_scripts($hook) {
		global $taxonomy;
		// debug_log($hook);
		// debug_log($taxonomy);
		if(($hook=='edit-tags.php' || $hook=='term.php') && $taxonomy=='passwords') {
			wp_enqueue_style( 'manage-passwords', THEME_URI.'/assets/css/manage-passwords.css', [], '' );
			wp_enqueue_script('manage-passwords', THEME_URI.'/assets/js/manage-passwords.js', array('jquery'), '');
		}
	}

	public static function instance() {
		if(empty(self::$instance))
			self::$instance = new self;

		return self::$instance;
	}
}

Admin_Passwords::instance();