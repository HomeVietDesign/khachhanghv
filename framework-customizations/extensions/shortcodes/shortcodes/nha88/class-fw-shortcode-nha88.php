<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Nha88 extends FW_Shortcode
{
	public $default = [
		'required_content' => '',
		'zalo' => '',
	];

	public function _init()
	{
      add_action( 'wp_footer', [$this, 'edit_modal'] );
      add_action( 'wp_ajax_get_edit_nha88_form', [$this, 'ajax_get_edit_nha88_form']);
      add_action( 'wp_ajax_update_nha88', [$this, 'ajax_update_nha88']);
      add_action( 'wp_ajax_get_nha88_info', [$this, 'ajax_get_nha88_info']);
      add_action( 'wp_ajax_nha88_hide', [$this, 'ajax_nha88_hide']);
	}

	public function ajax_nha88_hide() {
		global $current_nha88_type;
		$nha88_id = isset($_POST['nha88']) ? absint($_POST['nha88']) : 0;
		$response = 0;
		if(current_user_can('edit_nha88s') && $current_nha88_type && $nha88_id && check_ajax_referer( 'global', 'nonce', false )) {
			
			$nha88_hide = get_term_meta($current_nha88_type->term_id, 'nha88_hide', true);
			if(empty($nha88_hide)) $nha88_hide = [];

			if(in_array($nha88_id, $nha88_hide)) {
				unset($nha88_hide[array_search($nha88_id, $nha88_hide)]);
				$response = -1;
			} else {
				$nha88_hide[] = $nha88_id;
				$response = 1;
			}

			update_term_meta($current_nha88_type->term_id, 'nha88_hide', $nha88_hide);
		}
		wp_send_json($response);
	}

	public function ajax_get_nha88_info() {

		$nha88 = isset($_GET['nha88'])?absint($_GET['nha88']):0;

		$response = [
			'required_content' => '',
			'nha88_info' => '',
			'nha88_zalo' => '',
			'nha88_url' => '',
			'nha88_dates' => '',
		];
		
		if($nha88) {
			$nha88_value = fw_get_db_post_option($nha88,'nha88_value');
			$nha88_note = fw_get_db_post_option($nha88,'nha88_note');
			$nha88_zalo = fw_get_db_post_option($nha88,'nha88_zalo');
			$nha88_url = fw_get_db_post_option($nha88,'nha88_url');
			
			$_nha88_dates = fw_get_db_post_option($nha88, 'nha88_dates');
			
			$nha88_dates = [
				'date1' => [
					'title' => '',
					'date' => ''
				],
				'date2' => [
					'title' => '',
					'date' => ''
				],
				'date3' => [
					'title' => '',
					'date' => ''
				],
				'date4' => [
					'title' => '',
					'date' => ''
				]
			];

			if($_nha88_dates) {
				foreach ($_nha88_dates as $key => $value) {
					$nha88_dates['date'.($key+1)] = [
						'title' => $value['title'],
						'date' => $value['date']
					];
				}
			}

			$nha88_data = get_post_meta($nha88, '_data', true);
			if(empty($nha88_data)) $nha88_data = $this->default;
			$nha88_data += $this->default;

			ob_start();
			$i = 0;
			foreach ($nha88_dates as $key => $value) {
				$i++;
				?>
				<div class="col nha88-date-<?=$i?> position-relative">
				<?php
				if($nha88_dates[$key]['date']!='') {
					$date = date('d/m/y', strtotime($nha88_dates[$key]['date']));
					?>
					<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($nha88_dates[$key]['title'])?>"><?=esc_html($date)?></span>
					<?php
				}
				?>
				</div>
				<?php
			}
			$response['nha88_dates'] = ob_get_clean();

			ob_start();
			?>
			<div class="nha88-title pt-3 mb-1 fs-5 text-green text-uppercase">
				<?php echo esc_html(get_the_title($nha88)); ?>
			</div>
			<?php if($nha88_value!='') { ?>
			<div class="nha88-value mb-1">
				<span class="text-red fw-bold"><?php echo esc_html($nha88_value); ?></span>
			</div>
			<?php } ?>

			<?php if($nha88_note!='') { ?>
			<div class="nha88-note mb-1">
				<span class="text-red fw-bold"><?php echo esc_html($nha88_note); ?></span>
			</div>
			<?php } ?>
			<?php
			$response['nha88_info'] = ob_get_clean();

			$response['nha88_zalo'] = ($nha88_data['zalo']!='')?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($nha88_data['zalo']).'" target="_blank">RIÊNG</a>':'';
			$response['nha88_zalo'] .= ($nha88_zalo!='')?'<a class="btn btn-sm btn-shadow fw-bold ms-1" href="'.esc_url($nha88_zalo).'" target="_blank">Zalo</a>':'';

			$response['nha88_url'] = ($nha88_url!='')?'<a class="btn btn-sm btn-primary btn-shadow fw-bold me-2" href="'.esc_url($nha88_url).'" target="_blank">Chi tiết</a>':'';

			ob_start();
			
			if($nha88_data['required_content']!='') {
				$required_content = '<div class="copy-text">'.wp_get_the_content($nha88_data['required_content']).'</div><div class="text-end mb-3"><a class="zalo-copy btn btn-sm btn-primary" href="#">Copy</a></div>';
				?>
				<button type="button" class="btn-shadow btn btn-sm btn-primary fw-bold me-1" data-bs-toggle="popover" data-bs-title="Nội dung áp dụng" data-bs-content="<?=esc_attr($required_content)?>" data-bs-html="true">ÁP DỤNG</button>
				<?php
			}

			$response['required_content'] = ob_get_clean();
		}
		
		wp_send_json($response);
	}

	public function ajax_update_nha88() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('edit_nha88s') && check_ajax_referer( 'edit-nha88', 'nonce', false )) {
			$nha88 = isset($_POST['nha88'])?absint($_POST['nha88']):0;
			$required_content = isset($_POST['required_content'])?wp_kses_post($_POST['required_content']):'';
			$nha88_value = isset($_POST['nha88_value'])?sanitize_text_field($_POST['nha88_value']):'';
			$nha88_note = isset($_POST['nha88_note'])?sanitize_text_field($_POST['nha88_note']):'';
			$nha88_zalo = isset($_POST['nha88_zalo'])?sanitize_url($_POST['nha88_zalo']):'';
			$nha88_url = isset($_POST['nha88_url'])?sanitize_url($_POST['nha88_url']):'';

			$nha88_dates = isset($_POST['nha88_dates']) ? $_POST['nha88_dates'] : '';
			

			if($nha88) {
				
				fw_set_db_post_option($nha88, 'nha88_value', $nha88_value);
				fw_set_db_post_option($nha88, 'nha88_note', $nha88_note);
				fw_set_db_post_option($nha88, 'nha88_url', $nha88_url);
				fw_set_db_post_option($nha88, 'nha88_dates', $nha88_dates);

				$nha88_data = get_post_meta($nha88, '_data', true);
				if(empty($nha88_data)) $nha88_data = $this->default;
				$nha88_data += $this->default;

				$new_nha88_data = [
					'required_content' => $required_content,
					'zalo' => $nha88_zalo,
				];

				update_post_meta( $nha88, '_data', $new_nha88_data );

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_nha88_form() {
		$nha88 = isset($_GET['nha88'])?absint($_GET['nha88']):0;
		$nha88_value = fw_get_db_post_option($nha88,'nha88_value');
		$nha88_note = fw_get_db_post_option($nha88,'nha88_note');
		$nha88_url = fw_get_db_post_option($nha88,'nha88_url');
		
		$_nha88_dates = fw_get_db_post_option($nha88, 'nha88_dates');
			
		$nha88_dates = [
			'date1' => [
				'title' => '',
				'date' => ''
			],
			'date2' => [
				'title' => '',
				'date' => ''
			],
			'date3' => [
				'title' => '',
				'date' => ''
			],
			'date4' => [
				'title' => '',
				'date' => ''
			]
		];

		if($_nha88_dates) {
			foreach ($_nha88_dates as $key => $value) {
				$nha88_dates['date'.($key+1)] = [
					'title' => $value['title'],
					'date' => $value['date']
				];
			}
		}

		$nha88_data = get_post_meta($nha88, '_data', true);
		if(empty($nha88_data)) $nha88_data = $this->default;
		$nha88_data += $this->default;

		?>
		<form id="frm-edit-nha88" method="POST" action="">
			<input type="hidden" id="nha88" name="nha88" value="<?=$nha88?>">
			<?php wp_nonce_field( 'edit-nha88', 'nonce' ); ?>
			<div id="edit-nha88-response"></div>
			<div class="row">
				<div class="col-lg-7<?php echo (!current_user_can('edit_nha88s'))?' hidden':''; ?>">
					<div class="mb-3">
						Nội dung áp dụng
						<?php
						$settings = [
							'media_buttons' => false,
							'teeny'         => false,
							'quicktags'     => false,
							'editor_height' => '610',
							'tinymce'       => [
								'toolbar1' => 'bold,italic,underline,alignleft,aligncenter,alignright,alignjustify,link,unlink,bullist,numlist,forecolor,pastetext,removeformat,charmap,fullscreen',
								'toolbar2' => '',
								'content_style' => 'body { font-family: Arial, Helvetica, sans-serif; font-size: 16px; }'
							]
						];
						wp_editor( $nha88_data['required_content'], 'required_content', $settings);
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
				</div>
				<div class="<?php echo (!current_user_can('edit_nha88s'))?' col-lg-12':'col-lg-5'; ?>">
					<?php
					$i=0;
					foreach ($nha88_dates as $key => $value) {
						?>
						<div class="mb-3">
							<input class="form-control mb-2" type="text" value="<?=esc_attr($value['title'])?>" name="nha88_dates[<?=$i?>][title]" id="nha88_<?=esc_attr($key)?>_label" placeholder="Ghi chú ngày <?=($i+1)?>">
							<input class="form-control" type="date" value="<?php echo ($value['date']!='')?esc_html(date('Y-m-d', strtotime($value['date']))):''; ?>" name="nha88_dates[<?=$i?>][date]" id="nha88_<?=esc_attr($key)?>">
						</div>
						<?php
						$i++;
					}
					?>
					<div class="mb-3">
						Tên - Số điện thoại
						<input type="text" id="nha88_value" name="nha88_value" class="form-control" value="<?php echo esc_attr($nha88_value); ?>">
					</div>
					<div class="mb-3">
						Ghi chú
						<input type="text" id="nha88_note" name="nha88_note" class="form-control" value="<?php echo esc_attr($nha88_note); ?>">
					</div>
					<div class="mb-3">
						Nhóm zalo riêng
						<input type="text" id="nha88_zalo" name="nha88_zalo" class="form-control" value="<?php echo esc_attr($nha88_data['zalo']); ?>">
					</div>
					<div class="mb-3">
						Link chi tiết
						<input type="text" id="nha88_url" name="nha88_url" class="form-control" value="<?php echo ($nha88_url)?esc_url($nha88_url):''; ?>">
					</div>
				</div>
			</div>

			<div class="mb-3">
				<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-nha88-submit">Lưu lại</button>
			</div>
			
		</form>
		<?php

		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-nha88" tabindex="-1" role="dialog" aria-labelledby="edit-nha88-label">
			<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-nha88-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
