<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Gzalo extends FW_Shortcode
{
	public $default = [
		'required_content' => '',
		'zalo' => '',
	];

	public function _init() {
		add_action( 'wp_footer', [$this, 'edit_modal'] );
		add_action( 'wp_ajax_get_edit_gzalo_form', [$this, 'ajax_get_edit_gzalo_form']);
		add_action( 'wp_ajax_update_gzalo', [$this, 'ajax_update_gzalo']);
		add_action( 'wp_ajax_get_gzalo_info', [$this, 'ajax_get_gzalo_info']);
	}

	public function ajax_get_gzalo_info() {
		$gzalo = isset($_GET['gzalo'])?absint($_GET['gzalo']):0;

		$response = [
			'required_content' => '',
			'zalo' => ''
		];
		
		if($gzalo) {
			$default_zalo = fw_get_db_post_option($gzalo, 'gzalo_zalo');

			$gzalo_data = get_post_meta($gzalo, '_data', true);
			if(empty($gzalo_data)) $gzalo_data = $this->default;
			$gzalo_data += $this->default;

			$response['zalo'] = ($gzalo_data['zalo'])?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($gzalo_data['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['zalo'] .= ($default_zalo)?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($default_zalo).'" target="_blank">Zalo</a>':'';

			ob_start();
			
			if($gzalo_data['required_content']!='') {
				$required_content = '<div class="copy-text">'.wp_get_the_content($gzalo_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
				?>
				<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
				<?php
			}

			$response['required_content'] = ob_get_clean();
			
		}
		
		wp_send_json($response);
	}

	public function ajax_update_gzalo() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('edit_gzalos') && check_ajax_referer( 'edit-gzalo', 'nonce', false )) {
			$gzalo = isset($_POST['gzalo'])?absint($_POST['gzalo']):0;
			$required_content = isset($_POST['required_content'])?wp_kses_post($_POST['required_content']):'';
			$gzalo_zalo = isset($_POST['gzalo_zalo'])?sanitize_text_field($_POST['gzalo_zalo']):'';
			
			if($gzalo) {
				$gzalo_data = get_post_meta($gzalo, '_data', true);
				if(empty($gzalo_data)) $gzalo_data = $this->default;
				$gzalo_data += $this->default;

				$new_gzalo_data = [
					'required_content' => $required_content,
					'zalo' => $gzalo_zalo,
				];

				update_post_meta( $gzalo, '_data', $new_gzalo_data );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_gzalo_form() {
		$gzalo = isset($_GET['gzalo'])?absint($_GET['gzalo']):0;

		if($gzalo) {
			$gzalo_data = get_post_meta($gzalo, '_data', true);
			if(empty($gzalo_data)) $gzalo_data = $this->default;
			$gzalo_data += $this->default;

			?>
			<form id="frm-edit-gzalo" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="gzalo" name="gzalo" value="<?=$gzalo?>">
				<?php wp_nonce_field( 'edit-gzalo', 'nonce' ); ?>
				<div id="edit-gzalo-response"></div>
			
				<div class="mb-3">
					Nội dung áp dụng
					<?php
					$settings = [
						'media_buttons' => false,
						'teeny'         => false,
						'quicktags'     => false,
						'editor_height' => '400',
						'tinymce'       => [
							'toolbar1' => 'bold,italic,underline,alignleft,aligncenter,alignright,alignjustify,link,unlink,bullist,numlist,forecolor,pastetext,removeformat,charmap,fullscreen',
							'toolbar2' => '',
							'content_style' => 'body { font-family: Arial, Helvetica, sans-serif; font-size: 16px; }'
						]
					];
					wp_editor( $gzalo_data['required_content'], 'required_content', $settings);
					?>
					<input type="hidden" id="required_content_settings" value="<?=esc_attr(json_encode($settings))?>">
				</div>
				<style>
					.mce-container, .mce-container *, .mce-widget, .mce-widget * {
						color: #333;
					}
					.mce-menu .mce-menu-item.mce-active.mce-menu-item-normal, .mce-menu .mce-menu-item.mce-active.mce-menu-item-preview, .mce-menu .mce-menu-item.mce-selected, .mce-menu .mce-menu-item:focus, .mce-menu .mce-menu-item:hover {
						color: #fff;
					}
					.mce-widget.mce-tooltip {
						color: #fff;
					}
				</style>
				
				<div class="mb-3">
					Nhóm zalo riêng
					<input type="text" id="gzalo_zalo" name="gzalo_zalo" class="form-control" value="<?php echo esc_attr($gzalo_data['zalo']); ?>">
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-gzalo-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-gzalo" tabindex="-1" role="dialog" aria-labelledby="edit-gzalo-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-gzalo-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}

}
