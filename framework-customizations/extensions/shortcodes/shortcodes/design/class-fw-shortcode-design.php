<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Design extends FW_Shortcode
{
	
	public function _init()
	{
		add_action( 'wp_footer', [$this, 'edit_modal'] );
		add_action( 'wp_ajax_get_edit_design_form', [$this, 'ajax_get_edit_design_form']);
		add_action( 'wp_ajax_update_design', [$this, 'ajax_update_design']);
		add_action( 'wp_ajax_get_design_info', [$this, 'ajax_get_design_info']);
	}

	public function ajax_get_design_info() {

		$design = isset($_GET['design'])?absint($_GET['design']):0;

		$response = [
			'design_estimate_link' => '',
			'design_dates' => '',
		];
		
		if($design) {
			$design_estimate_link = fw_get_db_post_option($design, 'design_estimate_link');
			$_design_dates = fw_get_db_post_option($design, 'design_dates');
			
			$design_dates = [
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

			if($_design_dates) {
				foreach ($_design_dates as $key => $value) {
					$design_dates['date'.($key+1)] = [
						'title' => $value['title'],
						'date' => $value['date']
					];
				}
			}

			$response['design_estimate_link'] = ($design_estimate_link!='')?'<a class="btn btn-sm btn-primary" href="'.esc_url($design_estimate_link).'" target="_blank">Dự toán</a>':'';
			
			ob_start();
			$i = 0;
			foreach ($design_dates as $key => $value) {
				$i++;
				?>
				<div class="col design-date-<?=$i?> position-relative">
				<?php
				if($design_dates[$key]['date']!='') {
					$date = date('d/m/y', strtotime($design_dates[$key]['date']));
					?>
					<span class="w-100 d-flex justify-content-center align-items-center text-yellow" title="<?=esc_attr($design_dates[$key]['title'])?>"><?=esc_html($date)?></span>
					<?php
				}
				?>
				</div>
				<?php
			}
			$response['design_dates'] = ob_get_clean();
			
		}
		
		wp_send_json($response);
	}

	public function ajax_update_design() {
		$response = [
			'code' => 0,
			'msg' => '',
			'data' => []
		];

		if(current_user_can('edit_designs') && check_ajax_referer( 'edit-design', 'nonce', false )) {
			$design_id = isset($_POST['design'])?absint($_POST['design']):0;

			$design_estimate_link = isset($_POST['design_estimate_link'])?sanitize_url($_POST['design_estimate_link']):'';
			
			$design_dates = isset($_POST['design_dates']) ? $_POST['design_dates'] : [];
			
			if($design_id) {

				fw_set_db_post_option($design_id, 'design_estimate_link', $design_estimate_link);
				fw_set_db_post_option($design_id, 'design_dates', $design_dates);

				$response['code'] = 1;
				$response['msg'] = '<p class="text-success">Đã lưu</p>';
			}

		}

		wp_send_json( $response );
	}

	public function ajax_get_edit_design_form() {

		$design = isset($_GET['design'])?absint($_GET['design']):0;

		if($design) {
			//$design_content = fw_get_db_post_option($design, 'design_content');
			//$design_stage_options = fw_get_db_post_option($design, 'design_stage_options');
			$design_estimate_link = fw_get_db_post_option($design, 'design_estimate_link');
			$_design_dates = fw_get_db_post_option($design, 'design_dates');
			
			$design_dates = [
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

			if($_design_dates) {
				foreach ($_design_dates as $key => $value) {
					$design_dates['date'.($key+1)] = [
						'title' => $value['title'],
						'date' => $value['date']
					];
				}
			}

			?>
			<form id="frm-edit-design" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" id="design" name="design" value="<?=$design?>">
				<?php wp_nonce_field( 'edit-design', 'nonce' ); ?>
				<div id="edit-design-response"></div>
				<?php
				$i=0;
				foreach ($design_dates as $key => $value) {
					?>
					<div class="mb-3">
						<input class="form-control mb-2" type="text" value="<?=esc_attr($value['title'])?>" name="design_dates[<?=$i?>][title]" id="design_<?=esc_attr($key)?>_label" placeholder="Ghi chú ngày <?=($i+1)?>">
						<input class="form-control" type="date" value="<?php echo ($value['date']!='')?esc_html(date('Y-m-d', strtotime($value['date']))):''; ?>" name="design_dates[<?=$i?>][date]" id="design_<?=esc_attr($key)?>">
					</div>
					<?php
					$i++;
				}
				?>
				<div class="mb-3">
					Link dự toán
					<input type="text" id="design_estimate_link" name="design_estimate_link" placeholder="" class="form-control" value="<?php echo ($design_estimate_link!='')?esc_url($design_estimate_link):''; ?>">
				</div>
				<div class="mb-3">
					<button type="submit" class="btn btn-lg btn-danger text-uppercase fw-bold text-yellow text-nowrap d-block w-100" id="edit-design-submit">Lưu lại</button>
				</div>
				
			</form>
			<?php
		}
		exit;
	}

	public function edit_modal() {
		?>
		<div class="modal fade" id="edit-designs" tabindex="-1" role="dialog" aria-labelledby="edit-designs-label">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="edit-designs-label"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
